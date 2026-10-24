<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Run from CLI.\n");
    exit(2);
}

define('APP_ROOT', dirname(__DIR__));
require APP_ROOT . '/src/Support/Autoload.php';

$options = parseOptions($argv);
$file = (string) ($options['file'] ?? latestBackupPath());

try {
    $result = verifyBackup($file);
    if (isset($options['json'])) {
        echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
    } else {
        echo sprintf(
            "Backup verify: %s size=%s tables=%d/%d\n",
            $result['ok'] ? 'OK' : 'FAIL',
            formatBytes((int) $result['size']),
            (int) $result['presentTables'],
            count($result['requiredTables'])
        );
        foreach ($result['missingTables'] as $table) {
            echo "- missing table marker: {$table}\n";
        }
    }
    exit($result['ok'] ? 0 : 1);
} catch (Throwable $e) {
    fwrite(STDERR, '[beta_backup_verify] ' . $e->getMessage() . PHP_EOL);
    exit(1);
}

function parseOptions(array $argv): array
{
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
    return $options;
}

function latestBackupPath(): string
{
    $files = glob(APP_ROOT . '/storage/backups/pokemonchic_beta_*.sql') ?: [];
    usort($files, static fn (string $a, string $b): int => filemtime($b) <=> filemtime($a));
    if ($files === []) {
        throw new RuntimeException('No beta backup dump found in storage/backups.');
    }
    return $files[0];
}

/**
 * @return array<string,mixed>
 */
function verifyBackup(string $file): array
{
    $path = realpath($file) ?: $file;
    if (!is_file($path)) {
        throw new RuntimeException('Backup file not found: ' . $file);
    }

    $size = (int) filesize($path);
    $requiredTables = [
        'users',
        'site_settings',
        'schema_migrations',
        'pok_user',
        'attac_my_poke',
        'items',
        'items_users',
        'battles',
        'quest_definitions',
        'quest_steps',
        'market_lots',
        'safe_storage_entries',
        'reward_transactions',
        'game_notifications',
    ];

    $contents = file_get_contents($path);
    if (!is_string($contents) || $contents === '') {
        throw new RuntimeException('Unable to read backup file: ' . $path);
    }

    $missing = [];
    foreach ($requiredTables as $table) {
        if (preg_match('/CREATE TABLE `' . preg_quote($table, '/') . '`/i', $contents) !== 1) {
            $missing[] = $table;
        }
    }

    return [
        'ok' => $size >= 1024 * 1024 && $missing === [],
        'file' => $path,
        'size' => $size,
        'requiredTables' => $requiredTables,
        'presentTables' => count($requiredTables) - count($missing),
        'missingTables' => $missing,
    ];
}

function formatBytes(int $bytes): string
{
    if ($bytes >= 1024 * 1024) {
        return round($bytes / 1024 / 1024, 2) . ' MB';
    }
    if ($bytes >= 1024) {
        return round($bytes / 1024, 2) . ' KB';
    }
    return $bytes . ' B';
}
