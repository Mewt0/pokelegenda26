<?php
declare(strict_types=1);

use Pokemon8\Database\Connection;
use Pokemon8\Repository\BattleReplayRepository;
use Pokemon8\Support\Env;

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Run from CLI.\n");
    exit(2);
}

define('APP_ROOT', dirname(__DIR__));
require APP_ROOT . '/src/Support/Autoload.php';

Env::load(APP_ROOT . '/.env');
$db = Connection::make(require APP_ROOT . '/config/database.php');
$replays = new BattleReplayRepository($db);
$results = [];

$db->beginTransaction();
try {
    $battleId = 900000000 + random_int(10000, 99999);
    $battle = [
        'id' => $battleId,
        'batl_tip' => 'smoke',
        'user_1' => userId($db, 'Tacos'),
        'user_2' => userId($db, 'NIGA'),
        'poke_1' => 'smoke_1',
        'poke_2' => 'smoke_2',
        'raund' => 1,
        'hod_user_id' => 0,
        'pobeda' => 0,
        'time' => time(),
    ];
    assertTrue($results, 'users.ready', (int) $battle['user_1'] > 0 && (int) $battle['user_2'] > 0, 'Tacos=' . $battle['user_1'] . ', NIGA=' . $battle['user_2']);

    $replays->recordBattle($battle, ['smoke' => true]);
    $replays->recordSnapshot($battleId, 1, 'smoke_state', $battle, [
        'id' => 1,
        'battle_pokemon' => 'smoke_1',
        'names' => 'Replaymon',
        'lvl' => 100,
        'hp_my' => 300,
        'hp_max' => 300,
    ], [
        'id' => 2,
        'battle_pokemon' => 'smoke_2',
        'names' => 'Targetmon',
        'lvl' => 100,
        'hp_my' => 250,
        'hp_max' => 250,
    ], ['weather' => 'smoke'], ['viewer_id' => (int) $battle['user_1']]);
    $replays->recordAction($battleId, 1, 'smoke_attack', (int) $battle['user_1'], ['move_id' => 1]);
    $replays->recordRoundLog($battleId, 1, 'Replaymon использует Smoke Hit.', 123456);
    $replays->recordRandomRoll($battleId, 1, 'accuracy', 88, 1, 100, 95, true, ['actor_key' => 'smoke_1', 'target_key' => 'smoke_2', 'move_id' => 1]);
    $replays->recordDamage($battleId, 1, [
        'actor_key' => 'smoke_1',
        'target_key' => 'smoke_2',
        'move_id' => 1,
        'move_name' => 'Smoke Hit',
        'damage' => 77,
        'hp_before' => 250,
        'hp_after' => 173,
    ]);
    $battle['pobeda'] = (int) $battle['user_1'];
    $replays->markFinished($battleId, (int) $battle['user_1'], $battle);

    $payload = $replays->replayForBattle($battleId, (int) $battle['user_1'], false);
    assertTrue($results, 'player.view', (bool) ($payload['ok'] ?? false), $payload['message'] ?? '');
    assertTrue($results, 'events.recorded', (int) ($payload['replay']['summary']['events'] ?? 0) >= 6, 'events=' . (int) ($payload['replay']['summary']['events'] ?? 0));
    assertTrue($results, 'rounds.grouped', count($payload['replay']['rounds'] ?? []) >= 1, 'rounds=' . count($payload['replay']['rounds'] ?? []));

    $adminPayload = $replays->replayForBattle($battleId, 0, true);
    assertTrue($results, 'admin.view', (bool) ($adminPayload['ok'] ?? false), $adminPayload['message'] ?? '');
    $list = $replays->adminList((string) $battleId, 10, 0);
    assertTrue($results, 'admin.list', (int) ($list['total'] ?? 0) >= 1, 'total=' . (int) ($list['total'] ?? 0));

    $db->rollBack();
} catch (Throwable $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    $results[] = ['name' => 'exception', 'ok' => false, 'details' => $e->getMessage()];
}

printResults($results);
exit(count(array_filter($results, static fn (array $row): bool => !$row['ok'])) === 0 ? 0 : 1);

function userId(PDO $db, string $login): int
{
    $stmt = $db->prepare('SELECT id FROM users WHERE login = :login LIMIT 1');
    $stmt->execute(['login' => $login]);
    return (int) ($stmt->fetchColumn() ?: 0);
}

function assertTrue(array &$results, string $name, bool $ok, string $details = ''): void
{
    $results[] = ['name' => $name, 'ok' => $ok, 'details' => $details];
}

function printResults(array $results): void
{
    $passed = 0;
    foreach ($results as $row) {
        if ($row['ok']) {
            $passed++;
        }
        echo sprintf("[%s] %s%s\n", $row['ok'] ? 'OK ' : 'FAIL', $row['name'], $row['details'] !== '' ? ' - ' . $row['details'] : '');
    }
    echo sprintf("Battle replay smoke: %d/%d passed.\n", $passed, count($results));
}
