<?php
declare(strict_types=1);

/**
 * Open-test regression runner.
 *
 * Runs the existing QA smoke scripts in a safe sequential order. Mutating
 * checks that share Tacos/NIGA sessions are intentionally not parallelized.
 *
 * Usage:
 *   php tools/open_test_regression.php --password=...
 *   php tools/open_test_regression.php --password=... --profile=quick
 *   SMOKE_PASSWORD=... php tools/open_test_regression.php --profile=full --json
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Run from CLI.\n");
    exit(2);
}

$options = parseOptions($argv);
$profile = strtolower((string) ($options['profile'] ?? 'full'));
$json = isset($options['json']);
$password = (string) ($options['password'] ?? getenv('SMOKE_PASSWORD') ?: '');
$login = (string) ($options['login'] ?? getenv('SMOKE_LOGIN') ?: 'Tacos');
$forbiddenLogin = (string) ($options['forbidden-login'] ?? 'NIGA');
$php = PHP_BINARY;

if (!in_array($profile, ['quick', 'full'], true)) {
    fwrite(STDERR, "Unknown --profile. Use quick or full.\n");
    exit(2);
}

$checks = regressionChecks($profile, $login, $forbiddenLogin, $password);
$started = microtime(true);
$results = [];

foreach ($checks as $check) {
    if (($check['needsPassword'] ?? false) && $password === '') {
        $results[] = [
            'name' => $check['name'],
            'status' => 'skipped',
            'exitCode' => null,
            'durationMs' => 0,
            'summary' => 'Missing --password or SMOKE_PASSWORD.',
        ];
        continue;
    }

    $command = array_merge([$php], $check['args']);
    $run = runCommand($command);
    $results[] = [
        'name' => $check['name'],
        'status' => $run['exitCode'] === 0 ? 'passed' : 'failed',
        'exitCode' => $run['exitCode'],
        'durationMs' => $run['durationMs'],
        'summary' => summarizeOutput($run['output']),
    ];
}

$failed = array_values(array_filter($results, static fn (array $row): bool => $row['status'] === 'failed'));
$skipped = array_values(array_filter($results, static fn (array $row): bool => $row['status'] === 'skipped'));
$payload = [
    'profile' => $profile,
    'durationMs' => (int) round((microtime(true) - $started) * 1000),
    'passed' => count($results) - count($failed) - count($skipped),
    'failed' => count($failed),
    'skipped' => count($skipped),
    'results' => $results,
];

if ($json) {
    echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
} else {
    printHuman($payload);
}

exit(count($failed) === 0 ? 0 : 1);

/**
 * @return array<string,string|bool>
 */
function parseOptions(array $argv): array
{
    $out = [];
    foreach (array_slice($argv, 1) as $arg) {
        if (!str_starts_with($arg, '--')) {
            continue;
        }
        $arg = substr($arg, 2);
        if (str_contains($arg, '=')) {
            [$key, $value] = explode('=', $arg, 2);
            $out[$key] = $value;
        } else {
            $out[$arg] = true;
        }
    }
    return $out;
}

/**
 * @return list<array{name:string,args:list<string>,needsPassword?:bool}>
 */
function regressionChecks(string $profile, string $login, string $forbiddenLogin, string $password): array
{
    $auth = ['--login=' . $login, '--password=' . $password];
    $forbidden = ['--forbidden-login=' . $forbiddenLogin, '--forbidden-password=' . $password];

    $checks = [
        ['name' => 'Migrations', 'args' => ['tools/migration_status.php', '--record-status']],
        ['name' => 'DB integrity', 'args' => ['tools/db_integrity_smoke.php', '--json']],
        ['name' => 'HTTP API read smoke', 'args' => array_merge(['tools/http_smoke.php'], $auth), 'needsPassword' => true],
        ['name' => 'Legacy core migration smoke', 'args' => ['tools/legacy_core_qa_smoke.php']],
        ['name' => 'First player experience', 'args' => ['tools/fpe_quest_smoke.php']],
        ['name' => 'Quest chains minimum', 'args' => ['tools/quests_minimum_smoke.php']],
        ['name' => 'Locations/NPC/transport', 'args' => ['tools/location_npc_transport_smoke.php']],
        ['name' => 'Inventory/Held items', 'args' => ['tools/inventory_held_items_smoke.php']],
        ['name' => 'Reward pipeline', 'args' => ['tools/reward_pipeline_smoke.php']],
        ['name' => 'Safe storage', 'args' => ['tools/safe_storage_smoke.php']],
        ['name' => 'Background jobs', 'args' => ['tools/background_jobs_smoke.php']],
        ['name' => 'Commission market', 'args' => ['tools/commission_market_smoke.php']],
        ['name' => 'Commission hardening', 'args' => ['tools/commission_hardening_smoke.php', '--iterations=60']],
        ['name' => 'Economy guard', 'args' => ['tools/economy_guard_smoke.php']],
        ['name' => 'Battle replay', 'args' => ['tools/battle_replay_smoke.php']],
        ['name' => 'Notifications/mail', 'args' => ['tools/notifications_mail_smoke.php']],
        ['name' => 'Dex/AttackDex', 'args' => array_merge(['tools/dex_attackdex_smoke.php'], $auth), 'needsPassword' => true],
        ['name' => 'Tournament flow', 'args' => ['tools/tournament_qa_smoke.php', '--password=' . $password], 'needsPassword' => true],
        ['name' => 'Admin/GM center', 'args' => array_merge(['tools/admin_gm_center_smoke.php'], $auth, $forbidden), 'needsPassword' => true],
        ['name' => 'Bug reporter', 'args' => array_merge(['tools/bug_reporter_smoke.php'], $auth, $forbidden), 'needsPassword' => true],
    ];

    if ($profile === 'full') {
        $checks[] = ['name' => 'PvE catch cycle', 'args' => array_merge(['tools/http_smoke.php'], $auth, ['--battle=catch']), 'needsPassword' => true];
        $checks[] = ['name' => 'PvE finish cycle', 'args' => array_merge(['tools/http_smoke.php'], $auth, ['--battle=finish']), 'needsPassword' => true];
        $checks[] = ['name' => 'Breeding Tacos/NIGA', 'args' => ['tools/breeding_qa_smoke.php', '--password=' . $password], 'needsPassword' => true];
        $checks[] = ['name' => 'PvP Tacos/NIGA', 'args' => ['tools/pvp_qa_smoke.php', '--password=' . $password], 'needsPassword' => true];
    }

    return $checks;
}

/**
 * @param list<string> $command
 * @return array{exitCode:int,durationMs:int,output:string}
 */
function runCommand(array $command): array
{
    $started = microtime(true);
    $parts = array_map('escapeshellarg', $command);
    $cmd = implode(' ', $parts);
    $descriptor = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];
    $process = proc_open($cmd, $descriptor, $pipes, dirname(__DIR__));
    if (!is_resource($process)) {
        return ['exitCode' => 127, 'durationMs' => 0, 'output' => 'Unable to start process.'];
    }

    fclose($pipes[0]);
    $stdout = stream_get_contents($pipes[1]) ?: '';
    $stderr = stream_get_contents($pipes[2]) ?: '';
    fclose($pipes[1]);
    fclose($pipes[2]);
    $exitCode = proc_close($process);

    return [
        'exitCode' => (int) $exitCode,
        'durationMs' => (int) round((microtime(true) - $started) * 1000),
        'output' => trim($stdout . ($stderr !== '' ? PHP_EOL . $stderr : '')),
    ];
}

function summarizeOutput(string $output): string
{
    $decoded = json_decode($output, true);
    if (is_array($decoded) && isset($decoded['summary']) && is_array($decoded['summary'])) {
        $summary = $decoded['summary'];
        $parts = [];
        foreach (['p0', 'p1', 'warn', 'accepted', 'ok', 'fixed'] as $key) {
            if (array_key_exists($key, $summary)) {
                $parts[] = strtoupper($key) . '=' . (int) $summary[$key];
            }
        }
        if ($parts !== []) {
            return implode(', ', $parts);
        }
    }

    $lines = array_values(array_filter(array_map('trim', preg_split('/\R/', $output) ?: []), static fn (string $line): bool => $line !== ''));
    if ($lines === []) {
        return '';
    }

    $interesting = [];
    foreach ($lines as $line) {
        if (preg_match('/(Result:|passed|Smoke:|QA smoke:|Migration status:|P0|P1|FAIL|failed|warnings?|checks)/i', $line)) {
            $interesting[] = $line;
        }
    }
    if ($interesting === []) {
        $interesting = array_slice($lines, -3);
    }

    return implode(' | ', array_slice($interesting, -4));
}

/**
 * @param array{profile:string,durationMs:int,passed:int,failed:int,skipped:int,results:list<array<string,mixed>>} $payload
 */
function printHuman(array $payload): void
{
    echo "Open-test regression ({$payload['profile']})\n";
    echo "Passed: {$payload['passed']}, failed: {$payload['failed']}, skipped: {$payload['skipped']}, duration: {$payload['durationMs']} ms\n\n";
    foreach ($payload['results'] as $row) {
        $mark = match ($row['status']) {
            'passed' => '[OK]',
            'skipped' => '[SKIP]',
            default => '[FAIL]',
        };
        $seconds = number_format(((int) $row['durationMs']) / 1000, 2, '.', '');
        echo sprintf("%s %s (%ss) %s\n", $mark, $row['name'], $seconds, (string) $row['summary']);
    }
}
