<?php
declare(strict_types=1);

/**
 * One-time migration of pok_user.exp from the legacy single curve to the
 * per-growth-group curve.
 *
 * Default is a read-only dry run. --apply additionally requires the supported
 * maintenance mode (`site_settings.techwork=1`, enabled via the normal admin
 * settings flow), --maintenance, a fresh verified beta dump and an explicit
 * --confirm=APPLY_CURVE_V2. It also refuses while any battle row is still
 * active, so no game write can interleave with the one-time remap.
 * The migration preserves the current level and the relative position inside
 * that level; it never lowers a level. Candy debt is clamped to the floor of
 * the current level and overflow above the old border is clamped to the top of
 * the current level.
 *
 * Examples:
 *   php tools/pokemon_experience_migrate.php --json
 *   php tools/pokemon_experience_migrate.php --apply --maintenance \
 *       --backup=storage/backups/pokemonchic_beta_YYYYMMDD_HHMMSS.sql --confirm=APPLY_CURVE_V2
 */

use Pokemon8\Database\Connection;
use Pokemon8\Game\PokemonGrowthCurve as Curve;
use Pokemon8\Game\PokemonGrowthGroup as Group;
use Pokemon8\Support\Env;

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Run from CLI.\n");
    exit(2);
}

define('APP_ROOT', dirname(__DIR__));
require APP_ROOT . '/src/Support/Autoload.php';

$options = [];
foreach (array_slice($argv, 1) as $arg) {
    if (!str_starts_with($arg, '--')) {
        continue;
    }
    $arg = substr($arg, 2);
    if (str_contains($arg, '=')) {
        [$key, $value] = explode('=', $arg, 2);
        $options[$key] = $value;
    } else {
        $options[$arg] = true;
    }
}

$apply = isset($options['apply']);
$jsonOut = isset($options['json']);
$maintenance = isset($options['maintenance']);
$backup = (string) ($options['backup'] ?? '');
$confirm = (string) ($options['confirm'] ?? '');

Env::load(APP_ROOT . '/.env');
$db = Connection::make(require APP_ROOT . '/config/database.php');

$tableExists = static function (PDO $db, string $table): bool {
    return $db->query('SHOW TABLES LIKE ' . $db->quote($table))->fetchColumn() !== false;
};

$activeBattleCount = static function (PDO $db): int {
    return (int) $db->query('SELECT COUNT(*) FROM battles WHERE pobeda = 0')->fetchColumn();
};

if ($apply) {
    if (!$maintenance) {
        fwrite(STDERR, "Refusing to mutate: pass --maintenance (supported site_settings.techwork window).\n");
        exit(1);
    }
    if (!$tableExists($db, 'site_settings')) {
        fwrite(STDERR, "Refusing to mutate: site_settings is missing, cannot verify maintenance mode.\n");
        exit(1);
    }
    $techworkStmt = $db->prepare("SELECT value FROM site_settings WHERE name = 'techwork' LIMIT 1");
    $techworkStmt->execute();
    if ((string) $techworkStmt->fetchColumn() !== '1') {
        fwrite(STDERR, "Refusing to mutate: maintenance mode is off (site_settings.techwork != 1).\n");
        exit(1);
    }
    $activeBattles = $activeBattleCount($db);
    if ($activeBattles > 0) {
        fwrite(STDERR, 'Refusing to mutate: ' . $activeBattles . " active battle row(s) found.\n");
        exit(1);
    }
}

$markerTable = 'pokemon_experience_curve_migrations';
$alreadyApplied = false;
if ($tableExists($db, $markerTable)) {
    $stmt = $db->prepare('SELECT id FROM ' . $markerTable . ' WHERE curve_version = :version LIMIT 1');
    $stmt->execute(['version' => Curve::VERSION]);
    $alreadyApplied = $stmt->fetchColumn() !== false;
}

$rows = $db->query('SELECT id, users, basenum, names, lvl, exp, exp_b FROM pok_user ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
$plan = [];
$summary = [
    'rows' => count($rows),
    'levelsKept' => 0,
    'debtClamped' => 0,
    'overflowClamped' => 0,
    'landedOnCap' => 0,
    'unknownGroup' => 0,
    'groups' => [],
    'minNewExp' => null,
    'maxNewExp' => null,
];
foreach ($rows as $row) {
    $level = max(1, min(100, (int) $row['lvl']));
    $oldExp = max(0, (int) $row['exp']);
    $resolved = Group::resolve((int) $row['basenum']);
    if (!$resolved['known']) {
        $summary['unknownGroup']++;
    }
    if ($alreadyApplied) {
        // The marker exists, so rows are already on curve v2: verify them in
        // place instead of remapping from the legacy curve a second time.
        $current = Curve::cumulative($level, $resolved['group']);
        $next = $level >= 100 ? $current : Curve::cumulative($level + 1, $resolved['group']);
        $debt = $oldExp < $current;
        $overflow = $level < 100 && $oldExp >= $next;
        $mapped = [
            'level' => $level,
            'exp' => $oldExp,
            'fraction' => max(0.0, min(1.0, ($oldExp - $current) / max(1, $next - $current))),
            'legacyFraction' => 0.0,
            'debtClamped' => $debt,
            'overflowClamped' => $overflow,
        ];
    } else {
        $mapped = Curve::remapLegacyExperience($level, $oldExp, $resolved['group']);
    }
    $progress = Curve::progress($mapped['level'], $mapped['exp'], $resolved['group']);
    $plan[] = [
        'id' => (int) $row['id'],
        'user_id' => (int) $row['users'],
        'base_id' => (int) $row['basenum'],
        'name' => strip_tags((string) $row['names']),
        'group' => $resolved['group'],
        'group_label' => $resolved['label'],
        'group_known' => $resolved['known'],
        'old_level' => $level,
        'new_level' => $mapped['level'],
        'old_exp' => $oldExp,
        'new_exp' => $mapped['exp'],
        'new_exp_b' => $progress['expNextLevel'],
        'fraction' => $mapped['fraction'],
        'legacy_fraction' => $mapped['legacyFraction'],
        'debt_clamped' => $mapped['debtClamped'],
        'overflow_clamped' => $mapped['overflowClamped'],
    ];
    if ($mapped['level'] === $level) {
        $summary['levelsKept']++;
    }
    if ($mapped['debtClamped']) {
        $summary['debtClamped']++;
    }
    if ($mapped['overflowClamped']) {
        $summary['overflowClamped']++;
    }
    if ($mapped['level'] === 100) {
        $summary['landedOnCap']++;
    }
    $summary['groups'][$resolved['group']] = ($summary['groups'][$resolved['group']] ?? 0) + 1;
    $summary['minNewExp'] = $summary['minNewExp'] === null ? $mapped['exp'] : min($summary['minNewExp'], $mapped['exp']);
    $summary['maxNewExp'] = $summary['maxNewExp'] === null ? $mapped['exp'] : max($summary['maxNewExp'], $mapped['exp']);
}

$verification = [
    'allLevelsKept' => $summary['levelsKept'] === $summary['rows'],
    'noLevelDrops' => true,
    'allNoDebt' => true,
    'allWithinLevel' => true,
];
foreach ($plan as $item) {
    if ($item['new_level'] < $item['old_level']) {
        $verification['noLevelDrops'] = false;
    }
    $group = $item['group'];
    if ($item['new_exp'] < Curve::cumulative($item['new_level'], $group)) {
        $verification['allNoDebt'] = false;
    }
    if ($item['new_level'] < 100 && $item['new_exp'] >= Curve::cumulative($item['new_level'] + 1, $group)) {
        $verification['allWithinLevel'] = false;
    }
}

$backupCheck = static function (string $path) use ($db): array {
    $real = realpath($path) ?: $path;
    if (!is_file($real)) {
        return ['ok' => false, 'reason' => 'backup file not found'];
    }
    $size = (int) filesize($real);
    if ($size < 1_000_000) {
        return ['ok' => false, 'reason' => 'backup is too small: ' . $size];
    }
    $head = (string) file_get_contents($real);
    foreach (['users', 'pok_user', 'schema_migrations'] as $table) {
        if (!str_contains($head, '`' . $table . '`')) {
            return ['ok' => false, 'reason' => 'backup lacks table marker: ' . $table];
        }
    }
    return ['ok' => true, 'size' => $size, 'sha256' => hash_file('sha256', $real)];
};

$result = [
    'curveVersion' => Curve::VERSION,
    'anchorTotal' => Curve::ANCHOR_TOTAL,
    'targetTotals' => Curve::targetTotals(),
    'dryRun' => !$apply,
    'alreadyApplied' => $alreadyApplied,
    'summary' => $summary,
    'verification' => $verification,
    'plan' => $plan,
];

if (!$apply) {
    if ($jsonOut) {
        echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
    } else {
        echo "XP migration dry run: rows={$summary['rows']} levelsKept={$summary['levelsKept']} debt={$summary['debtClamped']} overflow={$summary['overflowClamped']}\n";
        echo 'Groups: ' . json_encode($summary['groups'], JSON_UNESCAPED_UNICODE) . "\n";
        echo 'Verification: ' . json_encode($verification, JSON_UNESCAPED_UNICODE) . "\n";
        foreach ($plan as $item) {
            printf(
                "#%d user=%d %-24s %-16s L%d exp %d -> %d (exp_b %d), fraction %.4f%s%s\n",
                $item['id'],
                $item['user_id'],
                $item['name'],
                $item['group_label'],
                $item['old_level'],
                $item['old_exp'],
                $item['new_exp'],
                $item['new_exp_b'],
                $item['fraction'],
                $item['debt_clamped'] ? ' [debt]' : '',
                $item['overflow_clamped'] ? ' [overflow]' : ''
            );
        }
    }
    exit(0);
}

if ($alreadyApplied) {
    fwrite(STDERR, "Curve v" . Curve::VERSION . " is already marked as applied.\n");
    exit(1);
}
if ($confirm !== 'APPLY_CURVE_V2') {
    fwrite(STDERR, "Refusing to mutate: pass --confirm=APPLY_CURVE_V2.\n");
    exit(1);
}
if ($backup === '') {
    fwrite(STDERR, "Refusing to mutate: pass --backup=<fresh beta dump>.\n");
    exit(1);
}
$backupResult = $backupCheck($backup);
if (!$backupResult['ok']) {
    fwrite(STDERR, 'Refusing to mutate: ' . $backupResult['reason'] . "\n");
    exit(1);
}
if (!$verification['allLevelsKept'] || !$verification['noLevelDrops'] || !$verification['allNoDebt'] || !$verification['allWithinLevel']) {
    fwrite(STDERR, "Refusing to mutate: preflight verification failed.\n");
    exit(1);
}

// Serialize the one-time data step. The advisory lock is released when the
// connection closes; it protects against a second operator/script applying the
// same snapshot concurrently.
$lockName = 'pokemonchic_xp_curve_v2_migration';
$lock = $db->prepare('SELECT GET_LOCK(:name, 15)');
$lock->execute(['name' => $lockName]);
if ((int) ($lock->fetchColumn() ?: 0) !== 1) {
    fwrite(STDERR, "Refusing to mutate: another XP curve migration holds the lock.\n");
    exit(1);
}
register_shutdown_function(static function () use ($db, $lockName): void {
    try {
        $release = $db->prepare('SELECT RELEASE_LOCK(:name)');
        $release->execute(['name' => $lockName]);
    } catch (Throwable) {
        // The connection close releases the advisory lock anyway.
    }
});
if ($activeBattleCount($db) > 0) {
    fwrite(STDERR, "Refusing to mutate: an active battle appeared while preparing the migration.\n");
    exit(1);
}

// DDL (CREATE TABLE) causes an implicit commit in MySQL, so the marker table is
// created before the data transaction starts. The row updates and the marker
// insert then commit or roll back together.
if (!$tableExists($db, $markerTable)) {
    $db->exec(
        'CREATE TABLE IF NOT EXISTS ' . $markerTable . ' (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            curve_version SMALLINT UNSIGNED NOT NULL,
            applied_at INT UNSIGNED NOT NULL,
            rows_count INT UNSIGNED NOT NULL,
            source_sha256 CHAR(64) NOT NULL,
            details_json LONGTEXT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY uniq_curve_version (curve_version)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
}

$db->beginTransaction();
try {
    $update = $db->prepare(
        'UPDATE pok_user SET exp = :exp, exp_b = :exp_b WHERE id = :id AND users = :user AND lvl = :lvl AND exp = :old_exp LIMIT 1'
    );
    $updated = 0;
    foreach ($plan as $item) {
        $update->execute([
            'exp' => $item['new_exp'],
            'exp_b' => $item['new_exp_b'],
            'id' => $item['id'],
            'user' => $item['user_id'],
            'lvl' => $item['old_level'],
            'old_exp' => $item['old_exp'],
        ]);
        if ($update->rowCount() !== 1) {
            // MySQL reports changed rows, not matched rows: an already-equal row
            // is a valid no-op, while a concurrent change must abort the batch.
            $check = $db->prepare('SELECT lvl, exp, exp_b FROM pok_user WHERE id = :id AND users = :user LIMIT 1');
            $check->execute(['id' => $item['id'], 'user' => $item['user_id']]);
            $current = $check->fetch(PDO::FETCH_ASSOC);
            if (
                !$current
                || (int) $current['lvl'] !== $item['old_level']
                || (int) $current['exp'] !== $item['new_exp']
                || (int) $current['exp_b'] !== $item['new_exp_b']
            ) {
                throw new RuntimeException('Row changed during migration: pok_user #' . $item['id']);
            }
        }
        $updated++;
    }

    $insert = $db->prepare(
        'INSERT INTO ' . $markerTable . ' (curve_version, applied_at, rows_count, source_sha256, details_json)
         VALUES (:version, :applied_at, :rows_count, :source_sha256, :details_json)'
    );
    $insert->execute([
        'version' => Curve::VERSION,
        'applied_at' => time(),
        'rows_count' => $updated,
        'source_sha256' => hash('sha256', json_encode($plan, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: ''),
        'details_json' => json_encode([
            'backup' => $backupResult,
            'summary' => $summary,
            'verification' => $verification,
            'targets' => Curve::targetTotals(),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    ]);

    $db->commit();
} catch (Throwable $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    fwrite(STDERR, '[pokemon_experience_migrate] ' . $e->getMessage() . PHP_EOL);
    exit(1);
}

echo 'Applied curve v' . Curve::VERSION . ' to ' . $updated . " pok_user rows; backup sha256=" . $backupResult['sha256'] . PHP_EOL;
