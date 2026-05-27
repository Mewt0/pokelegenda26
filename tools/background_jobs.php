<?php
declare(strict_types=1);

use Pokemon8\Database\Connection;
use Pokemon8\Repository\BackgroundJobRepository;
use Pokemon8\Repository\CommissionMarketRepository;
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

$options = getopt('', ['job::', 'dry-run', 'status', 'limit::', 'json']);
$job = (string) ($options['job'] ?? 'all');
$dryRun = array_key_exists('dry-run', $options);
$statusOnly = array_key_exists('status', $options);
$jsonOutput = array_key_exists('json', $options);
$limit = isset($options['limit']) && is_numeric((string) $options['limit']) ? (int) $options['limit'] : 0;

$evolutions = new PokemonEvolutionRepository($db);
$inventory = new InventoryRepository($db, $evolutions);
$safeStorage = new SafeStorageRepository($db);
$inventory->setSafeStorageRepository($safeStorage);
$commission = new CommissionMarketRepository($db, $inventory, $safeStorage);
$jobs = new BackgroundJobRepository($db, $commission);

if ($statusOnly) {
    output($jobs->status(), $jsonOutput);
    exit(0);
}

$result = $job === 'all'
    ? $jobs->runAll($dryRun, $limit)
    : $jobs->runJob($job, $dryRun, $limit);

output($result, $jsonOutput);
$ok = (bool) ($result['ok'] ?? false);
exit($ok ? 0 : 1);

/**
 * @param array<string,mixed> $data
 */
function output(array $data, bool $jsonOutput): void
{
    if ($jsonOutput) {
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
        return;
    }

    if (isset($data['jobs']) && is_array($data['jobs'])) {
        echo sprintf("Background jobs: %s%s\n", ($data['ok'] ?? true) ? 'OK' : 'FAILED', ($data['dryRun'] ?? false) ? ' (dry-run)' : '');
        foreach ($data['jobs'] as $name => $row) {
            echo sprintf("- %s: %s %s\n", (string) $name, (string) ($row['status'] ?? 'unknown'), json_encode($row['summary'] ?? $row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }
        return;
    }

    if (isset($data['jobs'])) {
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
        return;
    }

    echo sprintf("Background job: %s %s\n", (string) ($data['status'] ?? 'unknown'), json_encode($data['summary'] ?? $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}
