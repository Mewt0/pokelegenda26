<?php
declare(strict_types=1);

use Pokemon8\Database\Connection;
use Pokemon8\Repository\BackgroundJobRepository;
use Pokemon8\Repository\CommissionMarketRepository;
use Pokemon8\Repository\EconomyGuardRepository;
use Pokemon8\Repository\InventoryRepository;
use Pokemon8\Repository\PokemonEvolutionRepository;
use Pokemon8\Repository\SafeStorageRepository;
use Pokemon8\Support\Env;

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Run from CLI.\n");
    exit(2);
}

define('APP_ROOT', dirname(__DIR__));
require APP_ROOT . '/src/Support/Autoload.php';

Env::load(APP_ROOT . '/.env');
$db = Connection::make(require APP_ROOT . '/config/database.php');
$evolutions = new PokemonEvolutionRepository($db);
$inventory = new InventoryRepository($db, $evolutions);
$safeStorage = new SafeStorageRepository($db);
$inventory->setSafeStorageRepository($safeStorage);
$commission = new CommissionMarketRepository($db, $inventory, $safeStorage);
$economyGuard = new EconomyGuardRepository($db);
$jobs = new BackgroundJobRepository($db, $commission, $economyGuard);
$results = [];

$db->beginTransaction();
try {
    $tacos = userId($db, 'Tacos');
    $niga = userId($db, 'NIGA');
    assertTrue($results, 'users.ready', $tacos > 0 && $niga > 0, 'Tacos=' . $tacos . ', NIGA=' . $niga);

    $status = $jobs->status();
    assertTrue($results, 'status.jobs', isset($status['jobs']['expire_market'], $status['jobs']['pvp_timeouts'], $status['jobs']['economy_guard']), 'jobs=' . count($status['jobs'] ?? []));

    $dry = $jobs->runAll(true, 10);
    assertTrue($results, 'dry_run.all', (bool) ($dry['ok'] ?? false), 'jobs=' . count($dry['jobs'] ?? []));

    $requestId = nextId($db, 'pvp_requests', 'id');
    $now = time();
    $db->prepare(
        'INSERT INTO pvp_requests
            (id, from_user_id, to_user_id, from_pokemon_id, to_pokemon_id, status, battle_id, expires_at, responded_at, created_at, updated_at)
         VALUES
            (:id, :from, :to, 0, 0, "pending", 0, :expires, 0, :created, :updated)'
    )->execute([
        'id' => $requestId,
        'from' => $tacos,
        'to' => $niga,
        'expires' => $now - 10,
        'created' => $now - 130,
        'updated' => $now - 130,
    ]);
    $pvp = $jobs->runJob('pvp_timeouts', false, 10);
    assertTrue($results, 'pvp_timeout.run', (bool) ($pvp['ok'] ?? false), json_encode($pvp['summary'] ?? []));
    assertTrue($results, 'pvp_timeout.expired', scalar($db, 'SELECT status FROM pvp_requests WHERE id = :id', ['id' => $requestId]) === 'expired');

    $boostId = nextId($db, 'player_boosts', 'id');
    $db->prepare(
        'INSERT INTO player_boosts (id, user_id, item_id, boost_key, multiplier, starts_at, expires_at, active, source, created_at)
         VALUES (:id, :user, 0, "exp", 2.00, :start, :expires, 1, "smoke", :created)'
    )->execute([
        'id' => $boostId,
        'user' => $tacos,
        'start' => $now - 3600,
        'expires' => $now - 60,
        'created' => $now - 3600,
    ]);
    $db->prepare(
        'INSERT INTO items_users (item_id, user_id, count, dattimer, timers)
         VALUES (1, :user, 1, :expires, "not")'
    )->execute([
        'user' => $tacos,
        'expires' => (string) ($now - 60),
    ]);
    $itemRowId = (int) $db->lastInsertId();
    $tmp = $jobs->runJob('temporary_items', false, 10);
    assertTrue($results, 'temporary_items.run', (bool) ($tmp['ok'] ?? false), json_encode($tmp['summary'] ?? []));
    assertTrue($results, 'temporary_items.boost_disabled', (int) scalar($db, 'SELECT active FROM player_boosts WHERE id = :id', ['id' => $boostId]) === 0);
    assertTrue($results, 'temporary_items.item_deleted', (int) scalar($db, 'SELECT COUNT(*) FROM items_users WHERE id = :id', ['id' => $itemRowId]) === 0);

    $safe = $jobs->runJob('safe_storage_status', true, 10);
    assertTrue($results, 'safe_storage_status.dry', (bool) ($safe['ok'] ?? false), json_encode($safe['summary'] ?? []));

    $economy = $jobs->runJob('economy_guard', true, 10);
    assertTrue($results, 'economy_guard.dry', (bool) ($economy['ok'] ?? false), json_encode($economy['summary'] ?? []));

    $db->rollBack();
} catch (Throwable $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    $results[] = ['name' => 'exception', 'ok' => false, 'details' => $e->getMessage()];
}

printResults($results);
exit(count(array_filter($results, static fn (array $row): bool => !$row['ok'])) === 0 ? 0 : 1);

function userId(PDO $db, string $login): int
{
    $stmt = $db->prepare('SELECT id FROM users WHERE login = :login LIMIT 1');
    $stmt->execute(['login' => $login]);
    return (int) ($stmt->fetchColumn() ?: 0);
}

function nextId(PDO $db, string $table, string $column): int
{
    $stmt = $db->query('SELECT COALESCE(MAX(' . $column . '), 0) + 1 FROM ' . $table);
    return (int) ($stmt->fetchColumn() ?: 1);
}

/**
 * @param array<string,mixed> $params
 */
function scalar(PDO $db, string $sql, array $params = []): mixed
{
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn();
}

function assertTrue(array &$results, string $name, bool $ok, string $details = ''): void
{
    $results[] = ['name' => $name, 'ok' => $ok, 'details' => $details];
}

function printResults(array $results): void
{
    $passed = 0;
    foreach ($results as $row) {
        if ($row['ok']) {
            $passed++;
        }
        echo sprintf("[%s] %s%s\n", $row['ok'] ? 'OK ' : 'FAIL', $row['name'], $row['details'] !== '' ? ' - ' . $row['details'] : '');
    }
    echo sprintf("Background jobs smoke: %d/%d passed.\n", $passed, count($results));
}
