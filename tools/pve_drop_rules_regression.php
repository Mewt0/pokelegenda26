<?php
declare(strict_types=1);

/**
 * D2 regression: one roll per wild-drop rule.
 *
 * Calls the real BattleRepository::rollAdminDropRewards() repeatedly inside a
 * transaction and rolls everything back. A JOIN multiplication bug (pokebuild.id=0)
 * returns hundreds of entries for one rule, so duplicate ruleId values or a drop
 * count above the number of matched rules fail this check.
 *
 * Usage: php tools/pve_drop_rules_regression.php [--user=1] [--location=4] [--base=13] [--iterations=200] [--json]
 */

use Pokemon8\Database\Connection;
use Pokemon8\Repository\BattleRepository;
use Pokemon8\Support\Env;

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Run from CLI.\n");
    exit(2);
}

define('APP_ROOT', dirname(__DIR__));
require APP_ROOT . '/src/Support/Autoload.php';
Env::load(APP_ROOT . '/.env');

$options = getopt('', ['user::', 'location::', 'base::', 'iterations::', 'json']);
$userId = max(1, (int) ($options['user'] ?? 1));
$locationId = max(1, (int) ($options['location'] ?? 4));
$baseId = max(1, (int) ($options['base'] ?? 13));
$iterations = max(20, min(1000, (int) ($options['iterations'] ?? 200)));
$json = array_key_exists('json', $options);

$db = Connection::make(require APP_ROOT . '/config/database.php');
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
$repo = new BattleRepository($db);

$stmt = $db->prepare(
    'SELECT r.id, r.item_id, r.chance_percent, i.name AS item_name
       FROM admin_drop_rules r
       INNER JOIN items i ON i.id = r.item_id
  LEFT JOIN pokebuild pb ON r.pokebuild_id > 0 AND pb.id = r.pokebuild_id
      WHERE r.enabled = 1 AND r.source_type = "wild"
        AND (r.location_id = 0 OR r.location_id = :location)
        AND (r.pokemon_base_id = 0 OR r.pokemon_base_id = :base)
        AND (r.pokebuild_id = 0 OR (pb.building = :location_pb AND pb.baseid = :base_pb))'
);
$stmt->execute([
    'location' => $locationId,
    'base' => $baseId,
    'location_pb' => $locationId,
    'base_pb' => $baseId,
]);
$rules = $stmt->fetchAll();
$ruleIds = array_map(static fn(array $r): int => (int) $r['id'], $rules);

$db->beginTransaction();
$failures = [];
$seenRuleIds = [];
$maxDropsInOneRoll = 0;
try {
    $db->prepare('UPDATE users SET buildmy = ? WHERE id = ?')->execute([$locationId, $userId]);
    for ($i = 0; $i < $iterations; $i++) {
        $drops = $repo->rollAdminDropRewards($userId, ['basenum' => $baseId], 1.0);
        $ids = array_map(static fn(array $d): int => (int) ($d['ruleId'] ?? 0), $drops);
        $maxDropsInOneRoll = max($maxDropsInOneRoll, count($ids));
        if (count($ids) !== count(array_unique($ids))) {
            $failures[] = 'duplicate ruleId in roll #' . $i . ': ' . implode(',', $ids);
            break;
        }
        if (count($ids) > count($ruleIds)) {
            $failures[] = 'drop count ' . count($ids) . ' exceeds matched rules ' . count($ruleIds);
            break;
        }
        foreach ($ids as $id) {
            $seenRuleIds[$id] = true;
        }
    }
} finally {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
}

$ok = $failures === [] && $ruleIds !== [];
$payload = [
    'ok' => $ok,
    'userId' => $userId,
    'locationId' => $locationId,
    'baseId' => $baseId,
    'iterations' => $iterations,
    'matchedRules' => count($ruleIds),
    'ruleIds' => $ruleIds,
    'seenRuleIds' => array_keys($seenRuleIds),
    'maxDropsInOneRoll' => $maxDropsInOneRoll,
    'failures' => $failures,
];

if ($json) {
    echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
} else {
    echo sprintf(
        "Drop rules regression: %s matched=%d iterations=%d maxDropsPerRoll=%d seen=%d\n",
        $ok ? 'OK' : 'FAIL',
        count($ruleIds),
        $iterations,
        $maxDropsInOneRoll,
        count($seenRuleIds)
    );
    foreach ($failures as $failure) {
        echo ' - ' . $failure . PHP_EOL;
    }
    if ($ruleIds === []) {
        echo ' - no enabled wild drop rules matched fixture' . PHP_EOL;
    }
}

exit($ok ? 0 : 1);
