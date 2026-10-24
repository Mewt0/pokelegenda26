<?php
declare(strict_types=1);

use Pokemon8\Database\Connection;
use Pokemon8\Repository\IntegrityRepository;
use Pokemon8\Support\Env;

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Run from CLI.\n");
    exit(2);
}

define('APP_ROOT', dirname(__DIR__));
require APP_ROOT . '/src/Support/Autoload.php';

Env::load(APP_ROOT . '/.env');
$db = Connection::make(require APP_ROOT . '/config/database.php');
$fixSafe = in_array('--fix-safe', $argv, true);
$json = in_array('--json', $argv, true);
$repo = new IntegrityRepository($db);
$result = $repo->run($fixSafe);

if ($json) {
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
} else {
    $summary = $result['summary'];
    echo sprintf(
        "DB integrity: p0=%d p1=%d warn=%d accepted=%d ok=%d fixed=%d%s\n",
        (int) ($summary['p0'] ?? 0),
        (int) ($summary['p1'] ?? 0),
        (int) ($summary['warn'] ?? 0),
        (int) ($summary['accepted'] ?? 0),
        (int) ($summary['ok'] ?? 0),
        (int) ($summary['fixed'] ?? 0),
        $fixSafe ? ' (fix-safe)' : ''
    );
    foreach ($result['checks'] as $check) {
        if (($check['status'] ?? 'ok') === 'ok') {
            continue;
        }
        echo sprintf(
            "- [%s] %s: %s count=%d fixed=%d\n",
            (($check['status'] ?? '') === 'accepted' ? 'ACCEPTED ' : '') . strtoupper((string) ($check['severity'] ?? 'warn')),
            (string) ($check['key'] ?? ''),
            (string) ($check['description'] ?? ''),
            (int) ($check['count'] ?? 0),
            (int) ($check['fixed'] ?? 0)
        );
    }
}

$summary = $result['summary'];
exit(((int) ($summary['p0'] ?? 0) === 0 && (int) ($summary['p1'] ?? 0) === 0) ? 0 : 1);
