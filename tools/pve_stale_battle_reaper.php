<?php
declare(strict_types=1);

use Pokemon8\Database\Connection;
use Pokemon8\Game\StalePveBattleReaper;
use Pokemon8\Support\Env;

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Run from CLI.\n");
    exit(2);
}

define('APP_ROOT', dirname(__DIR__));
require APP_ROOT . '/src/Support/Autoload.php';
Env::load(APP_ROOT . '/.env');
$db = Connection::make(require APP_ROOT . '/config/database.php');
$options = getopt('', ['apply', 'dry-run', 'limit::', 'json']);
$dryRun = !array_key_exists('apply', $options);
$limit = isset($options['limit']) && is_numeric((string) $options['limit']) ? (int) $options['limit'] : 0;
$result = (new StalePveBattleReaper($db))->reap($dryRun, $limit);

if (array_key_exists('json', $options)) {
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
} else {
    echo sprintf(
        "Stale PvE battle reaper: %s candidates=%d changed=%d fallback_ttl=%ds battles=%s users=%s\n",
        $dryRun ? 'dry-run' : 'applied',
        $result['candidates'],
        $result['changed'],
        $result['fallbackTtlSeconds'],
        implode(',', $result['battleIds']),
        implode(',', $result['userIds'])
    );
}
