<?php
declare(strict_types=1);

use Pokemon8\Support\Env;

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Run from CLI.\n");
    exit(2);
}

define('APP_ROOT', dirname(__DIR__));
require APP_ROOT . '/src/Support/Autoload.php';

Env::load(APP_ROOT . '/.env');
$options = parseOptions($argv);
$config = require APP_ROOT . '/config/database.php';

try {
    $dump = findMysqlDump((string) ($options['mysqldump'] ?? getenv('MYSQLDUMP_BIN') ?: ''));
    $output = (string) ($options['output'] ?? defaultOutputPath());
    $dir = dirname($output);
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        throw new RuntimeException('Unable to create backup directory: ' . $dir);
    }

    $command = [
        $dump,
        '--host=' . $config['host'],
        '--port=' . (string) $config['port'],
        '--user=' . $config['username'],
        '--default-character-set=' . ($config['charset'] ?? 'utf8mb4'),
        '--single-transaction',
        '--quick',
        '--routines',
        '--events',
        '--result-file=' . $output,
    ];
    if ((string) $config['password'] !== '') {
        $command[] = '--password=' . $config['password'];
    }
    if (isset($options['schema-only'])) {
        $command[] = '--no-data';
    }
    $command[] = $config['database'];

    [$exitCode, $stderr] = runProcess($command);
    if ($exitCode !== 0) {
        throw new RuntimeException("mysqldump failed with code {$exitCode}: " . trim($stderr));
    }
    if (!is_file($output) || filesize($output) === 0) {
        throw new RuntimeException('Backup file was not created or is empty.');
    }

    printf("Backup created: %s (%s)\n", $output, formatBytes((int) filesize($output)));
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, '[beta_backup] ' . $e->getMessage() . PHP_EOL);
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

function defaultOutputPath(): string
{
    return APP_ROOT . '/storage/backups/pokemonchic_beta_' . date('Ymd_His') . '.sql';
}

function findMysqlDump(string $explicit): string
{
    $candidates = [];
    $searched = [];
    if ($explicit !== '') {
        if (is_file($explicit)) {
            return $explicit;
        }
        throw new RuntimeException('Configured mysqldump path does not exist: ' . $explicit);
    }
    $candidates[] = 'mysqldump';

    $roots = [];
    foreach (['OSPANEL_ROOT', 'OSPanel_ROOT', 'OSPANEL_HOME'] as $envName) {
        $root = getenv($envName);
        if (is_string($root) && trim($root) !== '') {
            $roots[] = rtrim(trim($root), '\\\\/');
        }
    }
    foreach (['D:/OSPanel', 'E:/OSPanel'] as $root) {
        $roots[] = $root;
    }
    $roots = array_values(array_unique($roots));

    foreach ($roots as $root) {
        $pattern = $root . '/modules/database/*/bin/mysqldump.exe';
        $matches = glob($pattern) ?: [];
        $searched[] = $pattern;
        foreach ($matches as $path) {
            $candidates[] = $path;
        }
    }
    usort($candidates, static function (string $a, string $b): int {
        $aTime = is_file($a) ? (int) filemtime($a) : 0;
        $bTime = is_file($b) ? (int) filemtime($b) : 0;
        return $bTime <=> $aTime;
    });

    foreach ($candidates as $candidate) {
        if ($candidate === 'mysqldump') {
            $searched[] = 'PATH (mysqldump)';
            return $candidate;
        }
        $searched[] = $candidate;
        if (is_file($candidate)) {
            return $candidate;
        }
    }

    throw new RuntimeException(
        'mysqldump.exe not found. Searched: ' . implode('; ', array_values(array_unique($searched))
        ) . '. Pass --mysqldump=PATH or set MYSQLDUMP_BIN.'
    );
}

function runProcess(array $command): array
{
    $descriptor = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];
    $process = proc_open($command, $descriptor, $pipes, APP_ROOT);
    if (!is_resource($process)) {
        throw new RuntimeException('Unable to start mysqldump.');
    }
    fclose($pipes[0]);
    stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[2]);
    $exitCode = proc_close($process);
    return [$exitCode, $stderr];
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
