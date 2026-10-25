<?php
declare(strict_types=1);

/**
 * D1 regression: wild battle links pok_pve.battleid, and a successful catch is
 * atomic (battle finish + ball decrement + pokemon insert), idempotent on repeat.
 * Runs inside a transaction and rolls back, so no live inventory/progress changes.
 *
 * Usage: php tools/pve_catch_regression.php [--user=10158] [--json]
 */

use Pokemon8\Database\Connection;
use Pokemon8\Game\WildEncounterService;
use Pokemon8\Repository\BattleRepository;
use Pokemon8\Support\Env;

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Run from CLI.\n");
    exit(2);
}

define('APP_ROOT', dirname(__DIR__));
require APP_ROOT . '/src/Support/Autoload.php';
Env::load(APP_ROOT . '/.env');

$options = getopt('', ['user::', 'json']);
$userId = max(1, (int) ($options['user'] ?? 10158));
$json = array_key_exists('json', $options);

$db = Connection::make(require APP_ROOT . '/config/database.php');
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
$battles = new BattleRepository($db);
$wild = new WildEncounterService($db);
$checks = [];
$fail = static function (string $name, bool $ok, string $evidence) use (&$checks): void {
    $checks[] = ['name' => $name, 'pass' => $ok, 'evidence' => $evidence];
};

$db->beginTransaction();
try {
    $db->prepare('UPDATE users SET buildmy=4, pve_button=1, pve=0, pvp=0, trade=0, battleid=0 WHERE id=?')->execute([$userId]);
    $db->prepare('UPDATE pok_user SET hp_my=hp_max WHERE users=? AND hp_my<=0')->execute([$userId]);
    $ball = $db->prepare('SELECT id,count FROM items_users WHERE user_id=? AND item_id=3 ORDER BY id LIMIT 1');
    $ball->execute([$userId]);
    $ballRow = $ball->fetch();
    if (!is_array($ballRow)) {
        throw new RuntimeException('QA user has no Pokeball row (item_id=3).');
    }
    $ballId = (int) $ballRow['id'];
    $ballBefore = (int) $ballRow['count'];
    if ($ballBefore < 2) {
        $db->prepare('UPDATE items_users SET count=10 WHERE id=?')->execute([$ballId]);
        $ballBefore = 10;
    }

    $activeBefore = (int) $db->query('SELECT COUNT(*) FROM pok_user WHERE users=' . $userId . ' AND active=1')->fetchColumn();
    $monsBefore = (int) $db->query('SELECT COUNT(*) FROM pok_user WHERE users=' . $userId)->fetchColumn();

    $encounter = $wild->forceStartForUserAtLocation($userId, 4);
    $battleId = (int) ($encounter['wildEncounter']['battleId'] ?? 0);
    $battle = $db->query('SELECT poke_2,pobeda FROM battles WHERE id=' . $battleId)->fetch();
    $poke2 = (string) ($battle['poke_2'] ?? '');
    $pveId = (int) preg_replace('/^pve_/', '', $poke2);
    $link = $db->query('SELECT battleid,users,poimka FROM pok_pve WHERE id=' . $pveId)->fetch();
    $fail('battle.started', $battleId > 0 && $pveId > 0, 'battleId=' . $battleId . ' poke_2=' . $poke2);
    $fail('pok_pve.battleid-linked', is_array($link) && (int) $link['battleid'] === $battleId && (int) $link['users'] === $userId,
        'pok_pve=' . json_encode($link));

    $active = $activeBefore >= 6 ? 0 : 1;
    $catch = $battles->completePveCatch($userId, $ballId, $poke2, $active, $battleId);
    $newId = (int) ($catch['pokemonId'] ?? 0);
    $new = $newId > 0 ? $db->query('SELECT id,users,active,basenum FROM pok_user WHERE id=' . $newId)->fetch() : null;
    $ballAfter = (int) $db->query('SELECT count FROM items_users WHERE id=' . $ballId)->fetchColumn();
    $pobeda = (int) $db->query('SELECT pobeda FROM battles WHERE id=' . $battleId)->fetchColumn();
    $monsAfter = (int) $db->query('SELECT COUNT(*) FROM pok_user WHERE users=' . $userId)->fetchColumn();
    $fail('catch.atomic-success', ($catch['ok'] ?? false) === true && $newId > 0 && is_array($new)
        && (int) $new['users'] === $userId && (int) $new['active'] === $active
        && $monsAfter === $monsBefore + 1 && $ballAfter === $ballBefore - 1 && $pobeda === $userId,
        'catch=' . json_encode($catch) . ' new=' . json_encode($new) . ' mons=' . $monsBefore . '->' . $monsAfter . ' ball=' . $ballBefore . '->' . $ballAfter . ' pobeda=' . $pobeda);

    $repeat = $battles->completePveCatch($userId, $ballId, $poke2, $active, $battleId);
    $ballRepeat = (int) $db->query('SELECT count FROM items_users WHERE id=' . $ballId)->fetchColumn();
    $monsRepeat = (int) $db->query('SELECT COUNT(*) FROM pok_user WHERE users=' . $userId)->fetchColumn();
    $fail('catch.repeat-idempotent', ($repeat['ok'] ?? true) === false && ($repeat['reason'] ?? '') === 'already_finished'
        && $ballRepeat === $ballAfter && $monsRepeat === $monsAfter,
        'repeat=' . json_encode($repeat) . ' ball=' . $ballRepeat . ' mons=' . $monsRepeat);
} catch (Throwable $e) {
    $fail('exception', false, $e->getMessage());
} finally {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
}

$ok = $checks !== [] && !in_array(false, array_column($checks, 'pass'), true);
if ($json) {
    echo json_encode(['ok' => $ok, 'checks' => $checks], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
} else {
    echo 'Catch regression: ' . ($ok ? 'OK' : 'FAIL') . PHP_EOL;
    foreach ($checks as $check) {
        echo ($check['pass'] ? 'PASS' : 'FAIL') . ' ' . $check['name'] . ' - ' . $check['evidence'] . PHP_EOL;
    }
}
exit($ok ? 0 : 1);
