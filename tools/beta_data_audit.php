<?php
declare(strict_types=1);

use Pokemon8\Database\Connection;
use Pokemon8\Repository\CommissionMarketRepository;
use Pokemon8\Repository\InventoryRepository;
use Pokemon8\Repository\PokemonEvolutionRepository;
use Pokemon8\Support\Env;

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Run from CLI.\n");
    exit(2);
}

define('APP_ROOT', dirname(__DIR__));
require APP_ROOT . '/src/Support/Autoload.php';

Env::load(APP_ROOT . '/.env');
$db = Connection::make(require APP_ROOT . '/config/database.php');
$options = parseOptions($argv);

try {
    $fixes = [];
    if (isset($options['fix-safe'])) {
        $fixes = applySafeFixes($db);
    }
    $report = buildAuditReport($db, $fixes);

    if (isset($options['json'])) {
        echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
    } else {
        printReport($report);
    }

    exit(($report['summary']['p0'] ?? 0) > 0 ? 1 : 0);
} catch (Throwable $e) {
    fwrite(STDERR, '[beta_data_audit] ' . $e->getMessage() . PHP_EOL);
    exit(1);
}

function parseOptions(array $argv): array
{
    $options = [];
    foreach (array_slice($argv, 1) as $arg) {
        if (str_starts_with($arg, '--')) {
            $options[substr($arg, 2)] = true;
        }
    }
    return $options;
}

function applySafeFixes(PDO $db): array
{
    $fixes = [];
    $mergedStacks = mergeDuplicateItemStacks($db);
    if ($mergedStacks > 0) {
        $fixes[] = 'items_users.merge_duplicate_stacks=' . $mergedStacks;
    }

    if (tableExists($db, 'market_lots')) {
        $inventory = new InventoryRepository($db, new PokemonEvolutionRepository($db));
        $market = new CommissionMarketRepository($db, $inventory);
        $market->lots(0, ['per_page' => 10]);
        $fixes[] = 'commission.expire_due_lots';
    }

    if (tableExists($db, 'pvp_requests') && columnExists($db, 'pvp_requests', 'expires_at')) {
        $stmt = $db->prepare(
            'UPDATE pvp_requests
                SET status = "expired",
                    responded_at = IF(responded_at = 0, :time_responded, responded_at),
                    updated_at = :time_updated
              WHERE status = "pending"
                AND expires_at > 0
                AND expires_at <= :time_expires'
        );
        $now = time();
        $stmt->execute(['time_responded' => $now, 'time_updated' => $now, 'time_expires' => $now]);
        $fixes[] = 'pvp_requests.expire_pending=' . $stmt->rowCount();
    }

    return $fixes;
}

function mergeDuplicateItemStacks(PDO $db): int
{
    if (!tableExists($db, 'items_users')) {
        return 0;
    }
    $groups = $db->query(
        'SELECT user_id, item_id, dattimer, timers, COUNT(*) AS rows_count, SUM(count) AS total_count, MIN(id) AS keep_id
           FROM items_users
       GROUP BY user_id, item_id, dattimer, timers
         HAVING rows_count > 1'
    )->fetchAll(PDO::FETCH_ASSOC) ?: [];

    $merged = 0;
    foreach ($groups as $group) {
        $db->beginTransaction();
        try {
            $lock = $db->prepare(
                'SELECT id, count
                   FROM items_users
                  WHERE user_id = :user
                    AND item_id = :item
                    AND dattimer = :dattimer
                    AND timers = :timers
               ORDER BY id ASC
                    FOR UPDATE'
            );
            $lock->execute([
                'user' => (int) $group['user_id'],
                'item' => (int) $group['item_id'],
                'dattimer' => (string) $group['dattimer'],
                'timers' => (string) $group['timers'],
            ]);
            $rows = $lock->fetchAll(PDO::FETCH_ASSOC) ?: [];
            if (count($rows) <= 1) {
                $db->commit();
                continue;
            }
            $keepId = (int) $rows[0]['id'];
            $total = array_sum(array_map(static fn (array $row): int => max(0, (int) $row['count']), $rows));
            $db->prepare('UPDATE items_users SET count = :count WHERE id = :id LIMIT 1')
                ->execute(['count' => $total, 'id' => $keepId]);
            $deleteIds = array_map(static fn (array $row): int => (int) $row['id'], array_slice($rows, 1));
            $db->exec('DELETE FROM items_users WHERE id IN (' . implode(',', $deleteIds) . ')');
            $db->commit();
            $merged++;
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    return $merged;
}

function buildAuditReport(PDO $db, array $fixes): array
{
    $checks = [];
    addCheck($checks, $db, 'items_users.zero_id', 'p0', 'items_users rows with id=0', 'SELECT COUNT(*) FROM items_users WHERE id = 0');
    addCheck($checks, $db, 'items_users.negative_count', 'p0', 'items_users rows with negative count', 'SELECT COUNT(*) FROM items_users WHERE count < 0');
    addCheck($checks, $db, 'items_users.duplicate_stack_rows', 'warn', 'duplicate item stacks by user/item/timer', 'SELECT COUNT(*) FROM (SELECT user_id, item_id, dattimer, COUNT(*) c FROM items_users GROUP BY user_id, item_id, dattimer HAVING c > 1) x');
    addCheck($checks, $db, 'users.qa_accounts', 'p1', 'missing QA/service accounts Tacos/NIGA/System', qaAccountsSql());
    addCheck($checks, $db, 'pokemon.permanent_battle_forms', 'p1', 'permanent Primal/Mega form ids in pok_user.basenum', 'SELECT COUNT(*) FROM pok_user WHERE basenum BETWEEN 5000 AND 5099');
    addCheck($checks, $db, 'pokemon.active_party_over_6', 'warn', 'users with more than 6 active pokemon', 'SELECT COUNT(*) FROM (SELECT users, COUNT(*) c FROM pok_user WHERE users > 0 AND users <> 3 AND active = 1 GROUP BY users HAVING c > 6) x');
    addCheck($checks, $db, 'battle.active_unfinished', 'warn', 'unfinished battles', tableExists($db, 'battles') ? 'SELECT COUNT(*) FROM battles WHERE pobeda = 0' : null);

    if (tableExists($db, 'market_lots')) {
        $reserveId = commissionReserveUserId($db);
        addCheck($checks, $db, 'commission.reserve_user_collision', 'p1', 'commission reserve user id points to a regular player', reserveCollisionSql($reserveId));
        addCheck($checks, $db, 'commission.expired_active_lots', 'warn', 'active market lots past expires_at', 'SELECT COUNT(*) FROM market_lots WHERE status = "active" AND expires_at > 0 AND expires_at <= UNIX_TIMESTAMP()');
        addCheck($checks, $db, 'commission.reserved_pokemon_without_lot', 'p1', 'reserved pokemon without active lot', 'SELECT COUNT(*) FROM pok_user pu WHERE pu.users = ' . $reserveId . ' AND NOT EXISTS (SELECT 1 FROM market_lots ml WHERE ml.object_type = "pokemon" AND ml.object_id = pu.id AND ml.status = "active")');
        addCheck($checks, $db, 'commission.reserved_eggs_without_lot', 'p1', 'reserved eggs without active lot', tableExists($db, 'eggs') ? 'SELECT COUNT(*) FROM eggs e WHERE e.users_egg = ' . $reserveId . ' AND NOT EXISTS (SELECT 1 FROM market_lots ml WHERE ml.object_type = "egg" AND ml.object_id = e.id_egg AND ml.status = "active")' : null);
        addCheck($checks, $db, 'commission.pending_returns', 'warn', 'pending market return storage rows', tableExists($db, 'market_return_storage') ? 'SELECT COUNT(*) FROM market_return_storage WHERE status = "pending"' : null);
    }

    if (tableExists($db, 'pvp_requests') && columnExists($db, 'pvp_requests', 'expires_at')) {
        addCheck($checks, $db, 'pvp.expired_pending_requests', 'warn', 'pending PvP requests past expires_at', 'SELECT COUNT(*) FROM pvp_requests WHERE status = "pending" AND expires_at > 0 AND expires_at <= UNIX_TIMESTAMP()');
    }

    $summary = ['p0' => 0, 'p1' => 0, 'warn' => 0, 'ok' => 0];
    foreach ($checks as $check) {
        if (($check['count'] ?? 0) > 0) {
            $summary[$check['severity']]++;
        } else {
            $summary['ok']++;
        }
    }

    return [
        'checked_at' => time(),
        'fixes' => $fixes,
        'summary' => $summary,
        'checks' => $checks,
    ];
}

function addCheck(array &$checks, PDO $db, string $key, string $severity, string $label, ?string $sql): void
{
    if ($sql === null) {
        $checks[] = [
            'key' => $key,
            'severity' => $severity,
            'label' => $label,
            'count' => 0,
            'status' => 'skipped',
        ];
        return;
    }
    try {
        $count = (int) ($db->query($sql)->fetchColumn() ?: 0);
        $checks[] = [
            'key' => $key,
            'severity' => $severity,
            'label' => $label,
            'count' => $count,
            'status' => $count > 0 ? 'attention' : 'ok',
        ];
    } catch (Throwable $e) {
        $checks[] = [
            'key' => $key,
            'severity' => $severity,
            'label' => $label,
            'count' => 1,
            'status' => 'error',
            'error' => $e->getMessage(),
        ];
    }
}

function qaAccountsSql(): string
{
    return 'SELECT 3 - COUNT(DISTINCT login)
              FROM users
             WHERE login IN ("Tacos", "NIGA", "Система")';
}

function commissionReserveUserId(PDO $db): int
{
    $stmt = $db->query('SELECT CAST(value AS UNSIGNED) FROM site_settings WHERE name = "commission.reserve_user_id" LIMIT 1');
    $id = (int) ($stmt->fetchColumn() ?: 0);
    if ($id <= 0) {
        $stmt = $db->query('SELECT id FROM users WHERE login = "Система" ORDER BY id ASC LIMIT 1');
        $id = (int) ($stmt->fetchColumn() ?: 3);
    }
    return max(1, $id);
}

function reserveCollisionSql(int $reserveId): string
{
    return 'SELECT COUNT(*) FROM users WHERE id = ' . $reserveId . ' AND login <> "Система"';
}

function tableExists(PDO $db, string $table): bool
{
    $stmt = $db->prepare(
        'SELECT COUNT(*)
           FROM information_schema.TABLES
          WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = :table'
    );
    $stmt->execute(['table' => $table]);
    return (int) $stmt->fetchColumn() > 0;
}

function columnExists(PDO $db, string $table, string $column): bool
{
    $stmt = $db->prepare(
        'SELECT COUNT(*)
           FROM information_schema.COLUMNS
          WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = :table
            AND COLUMN_NAME = :column'
    );
    $stmt->execute(['table' => $table, 'column' => $column]);
    return (int) $stmt->fetchColumn() > 0;
}

function printReport(array $report): void
{
    printf(
        "Beta data audit: p0=%d p1=%d warn=%d ok=%d\n",
        $report['summary']['p0'],
        $report['summary']['p1'],
        $report['summary']['warn'],
        $report['summary']['ok']
    );
    if ($report['fixes'] !== []) {
        echo "Safe fixes: " . implode(', ', $report['fixes']) . "\n";
    }
    foreach ($report['checks'] as $check) {
        if (($check['count'] ?? 0) > 0 || ($check['status'] ?? '') === 'error') {
            printf(
                "- [%s] %s: %s (%d)\n",
                strtoupper((string) $check['severity']),
                $check['key'],
                $check['label'],
                (int) $check['count']
            );
            if (isset($check['error'])) {
                echo "  error: " . $check['error'] . "\n";
            }
        }
    }
}
