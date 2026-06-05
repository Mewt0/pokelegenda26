<?php
declare(strict_types=1);

use Pokemon8\Database\Connection;
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
    bootstrapMigrationTables($db);
    $files = migrationFiles(APP_ROOT . '/database/migrations');

    if (isset($options['baseline'])) {
        baselineMigrations($db, $files);
    }

    if (isset($options['apply'])) {
        applyPendingMigrations($db, $files);
    }

    $snapshot = buildSnapshot($db, $files);
    if (isset($options['record-status'])) {
        recordSnapshot($db, $snapshot);
    }

    if (isset($options['json'])) {
        echo json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
    } else {
        printSnapshot($snapshot);
    }

    exit(($snapshot['dirty_migrations'] ?? 0) > 0 || ($snapshot['failed_migrations'] ?? 0) > 0 ? 1 : 0);
} catch (Throwable $e) {
    fwrite(STDERR, '[migration_status] ' . $e->getMessage() . PHP_EOL);
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

function bootstrapMigrationTables(PDO $db): void
{
    $db->exec(
        'CREATE TABLE IF NOT EXISTS `schema_migrations` (
          `migration` VARCHAR(190) NOT NULL,
          `checksum` CHAR(64) NOT NULL,
          `batch` INT NOT NULL DEFAULT 0,
          `applied_at` INT NOT NULL DEFAULT 0,
          `execution_ms` INT NOT NULL DEFAULT 0,
          `status` VARCHAR(24) NOT NULL DEFAULT "applied",
          `note` VARCHAR(255) NOT NULL DEFAULT "",
          PRIMARY KEY (`migration`),
          KEY `idx_schema_migrations_status` (`status`, `applied_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
    $db->exec(
        'CREATE TABLE IF NOT EXISTS `migration_status` (
          `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
          `checked_at` INT NOT NULL DEFAULT 0,
          `total_migrations` INT NOT NULL DEFAULT 0,
          `applied_migrations` INT NOT NULL DEFAULT 0,
          `pending_migrations` INT NOT NULL DEFAULT 0,
          `dirty_migrations` INT NOT NULL DEFAULT 0,
          `failed_migrations` INT NOT NULL DEFAULT 0,
          `data_json` LONGTEXT NULL,
          PRIMARY KEY (`id`),
          KEY `idx_migration_status_checked` (`checked_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
}

/**
 * @return array<int, array{name:string,path:string,checksum:string}>
 */
function migrationFiles(string $dir): array
{
    $paths = glob($dir . '/*.sql') ?: [];
    sort($paths, SORT_STRING);
    $files = [];
    foreach ($paths as $path) {
        $files[] = [
            'name' => basename($path),
            'path' => $path,
            'checksum' => hash_file('sha256', $path) ?: '',
        ];
    }
    return $files;
}

function baselineMigrations(PDO $db, array $files): void
{
    $batch = nextBatch($db);
    $stmt = $db->prepare(
        'INSERT INTO schema_migrations (migration, checksum, batch, applied_at, execution_ms, status, note)
         VALUES (:migration, :checksum, :batch, :time, 0, "baseline", "Baseline captured before beta")
         ON DUPLICATE KEY UPDATE checksum = VALUES(checksum), status = IF(status = "failed", "baseline", status)'
    );
    foreach ($files as $file) {
        $stmt->execute([
            'migration' => $file['name'],
            'checksum' => $file['checksum'],
            'batch' => $batch,
            'time' => time(),
        ]);
    }
}

function applyPendingMigrations(PDO $db, array $files): void
{
    $existing = appliedMap($db);
    $batch = nextBatch($db);
    foreach ($files as $file) {
        $row = $existing[$file['name']] ?? null;
        if ($row !== null && in_array((string) $row['status'], ['applied', 'baseline'], true)) {
            continue;
        }

        $started = microtime(true);
        try {
            foreach (splitSqlStatements((string) file_get_contents($file['path'])) as $statement) {
                $db->exec($statement);
            }
            recordMigration($db, $file['name'], $file['checksum'], $batch, (int) round((microtime(true) - $started) * 1000), 'applied', '');
        } catch (Throwable $e) {
            recordMigration($db, $file['name'], $file['checksum'], $batch, (int) round((microtime(true) - $started) * 1000), 'failed', substr($e->getMessage(), 0, 255));
            throw $e;
        }
    }
}

function splitSqlStatements(string $sql): array
{
    $statements = [];
    $buffer = '';
    $quote = null;
    $len = strlen($sql);
    for ($i = 0; $i < $len; $i++) {
        $char = $sql[$i];
        $next = $i + 1 < $len ? $sql[$i + 1] : '';

        if ($quote === null && $char === '-' && $next === '-') {
            while ($i < $len && $sql[$i] !== "\n") {
                $i++;
            }
            $buffer .= "\n";
            continue;
        }
        if ($quote === null && $char === '#') {
            while ($i < $len && $sql[$i] !== "\n") {
                $i++;
            }
            $buffer .= "\n";
            continue;
        }

        if (($char === "'" || $char === '"') && ($i === 0 || $sql[$i - 1] !== '\\')) {
            $quote = $quote === $char ? null : ($quote ?? $char);
        }

        if ($char === ';' && $quote === null) {
            $statement = trim($buffer);
            if ($statement !== '') {
                $statements[] = $statement;
            }
            $buffer = '';
            continue;
        }

        $buffer .= $char;
    }

    $tail = trim($buffer);
    if ($tail !== '') {
        $statements[] = $tail;
    }
    return $statements;
}

function recordMigration(PDO $db, string $migration, string $checksum, int $batch, int $ms, string $status, string $note): void
{
    $stmt = $db->prepare(
        'INSERT INTO schema_migrations (migration, checksum, batch, applied_at, execution_ms, status, note)
         VALUES (:migration, :checksum, :batch, :time, :ms, :status, :note)
         ON DUPLICATE KEY UPDATE checksum = VALUES(checksum), batch = VALUES(batch), applied_at = VALUES(applied_at),
             execution_ms = VALUES(execution_ms), status = VALUES(status), note = VALUES(note)'
    );
    $stmt->execute([
        'migration' => $migration,
        'checksum' => $checksum,
        'batch' => $batch,
        'time' => time(),
        'ms' => $ms,
        'status' => $status,
        'note' => $note,
    ]);
}

function buildSnapshot(PDO $db, array $files): array
{
    $map = appliedMap($db);
    $items = [];
    $applied = 0;
    $pending = 0;
    $dirty = 0;
    $failed = 0;

    foreach ($files as $file) {
        $row = $map[$file['name']] ?? null;
        $status = $row['status'] ?? 'pending';
        if ($row === null) {
            $pending++;
        } elseif ((string) $row['checksum'] !== $file['checksum']) {
            $dirty++;
            $status = 'dirty';
        } elseif ($status === 'failed') {
            $failed++;
        } else {
            $applied++;
        }
        $items[] = [
            'migration' => $file['name'],
            'status' => $status,
            'checksum' => $file['checksum'],
            'applied_at' => isset($row['applied_at']) ? (int) $row['applied_at'] : 0,
            'note' => (string) ($row['note'] ?? ''),
        ];
    }

    return [
        'checked_at' => time(),
        'total_migrations' => count($files),
        'applied_migrations' => $applied,
        'pending_migrations' => $pending,
        'dirty_migrations' => $dirty,
        'failed_migrations' => $failed,
        'items' => $items,
    ];
}

function recordSnapshot(PDO $db, array $snapshot): void
{
    $stmt = $db->prepare(
        'INSERT INTO migration_status (checked_at, total_migrations, applied_migrations, pending_migrations, dirty_migrations, failed_migrations, data_json)
         VALUES (:checked, :total, :applied, :pending, :dirty, :failed, :data)'
    );
    $stmt->execute([
        'checked' => (int) $snapshot['checked_at'],
        'total' => (int) $snapshot['total_migrations'],
        'applied' => (int) $snapshot['applied_migrations'],
        'pending' => (int) $snapshot['pending_migrations'],
        'dirty' => (int) $snapshot['dirty_migrations'],
        'failed' => (int) $snapshot['failed_migrations'],
        'data' => json_encode($snapshot, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
    ]);
}

function appliedMap(PDO $db): array
{
    $rows = $db->query('SELECT migration, checksum, status, applied_at, note FROM schema_migrations')->fetchAll(PDO::FETCH_ASSOC) ?: [];
    $map = [];
    foreach ($rows as $row) {
        $map[(string) $row['migration']] = $row;
    }
    return $map;
}

function nextBatch(PDO $db): int
{
    return (int) ($db->query('SELECT COALESCE(MAX(batch), 0) + 1 FROM schema_migrations')->fetchColumn() ?: 1);
}

function printSnapshot(array $snapshot): void
{
    printf(
        "Migration status: total=%d applied=%d pending=%d dirty=%d failed=%d\n",
        $snapshot['total_migrations'],
        $snapshot['applied_migrations'],
        $snapshot['pending_migrations'],
        $snapshot['dirty_migrations'],
        $snapshot['failed_migrations']
    );
    foreach ($snapshot['items'] as $item) {
        if ($item['status'] !== 'applied' && $item['status'] !== 'baseline') {
            printf("- %s: %s\n", $item['migration'], $item['status']);
        }
    }
}
