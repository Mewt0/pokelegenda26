<?php
declare(strict_types=1);

use Pokemon8\Database\Connection;
use Pokemon8\Game\BattleEngineService;
use Pokemon8\Repository\BattleRepository;
use Pokemon8\Repository\RewardRepository;
use Pokemon8\Support\Env;

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Run from CLI.\n");
    exit(2);
}

define('APP_ROOT', dirname(__DIR__));
require APP_ROOT . '/src/Support/Autoload.php';

Env::load(APP_ROOT . '/.env');
$config = require APP_ROOT . '/config/database.php';
$db = Connection::make($config);
$db2 = Connection::make($config);
$results = [];
$battleId = 0;
$enemyId = 0;
$userId = 0;
$pokemonId = 0;
$originalUser = null;
$originalPokemonHp = null;
$lockHeld = false;

try {
    $userId = userId($db, 'Tacos');
    assertTrue($results, 'user.tacos', $userId > 0, 'id=' . $userId);
    $pokemon = playerPokemon($db, $userId);
    assertTrue($results, 'pokemon.ready', $pokemon !== null, json_encode($pokemon, JSON_UNESCAPED_UNICODE));
    $pokemonId = (int) ($pokemon['id'] ?? 0);
    $originalUser = userBattleState($db, $userId);
    $originalPokemonHp = pokemonHpState($db, $pokemonId);

    $enemyId = nextId($db, 'pok_pve', 'id');
    $battleId = nextId($db, 'battles', 'id');
    insertEnemy($db, $enemyId, $battleId);
    insertBattle($db, $battleId, $userId, $pokemonId, $enemyId);
    $db->prepare('UPDATE pok_user SET hp_my = hp_max, active = 1 WHERE id = :id LIMIT 1')->execute(['id' => $pokemonId]);
    $db->prepare('UPDATE users SET pve = 1, pvp = 0, battleid = :battle WHERE id = :user LIMIT 1')
        ->execute(['battle' => $battleId, 'user' => $userId]);

    $lockRepo = new BattleRepository($db);
    $lockHeld = $lockRepo->acquirePveBattleActionLock($battleId, 0);
    assertTrue($results, 'lock.acquired', $lockHeld, 'battle=' . $battleId);

    $engine = new BattleEngineService(new BattleRepository($db2), new RewardRepository($db2));
    $blocked = $engine->action($userId, 'escape', []);
    assertTrue($results, 'escape.blocked.by-lock', ($blocked['ok'] ?? true) === false && !empty($blocked['active']), json_encode($blocked, JSON_UNESCAPED_UNICODE));
    assertTrue($results, 'battle.not.finished.while-locked', battleWinner($db, $battleId) === 0, 'winner=' . battleWinner($db, $battleId));

    $lockRepo->releasePveBattleActionLock($battleId);
    $lockHeld = false;

    $escaped = $engine->action($userId, 'escape', []);
    assertTrue($results, 'escape.after-release', ($escaped['finished'] ?? false) === true && ($escaped['result'] ?? '') === 'escape', json_encode($escaped, JSON_UNESCAPED_UNICODE));
    assertTrue($results, 'battle.finished.once', battleWinner($db, $battleId) === -1, 'winner=' . battleWinner($db, $battleId));
} catch (Throwable $e) {
    $results[] = ['name' => 'exception', 'ok' => false, 'details' => $e->getMessage()];
} finally {
    if ($lockHeld && $battleId > 0) {
        (new BattleRepository($db))->releasePveBattleActionLock($battleId);
    }
    cleanup($db, $userId, $battleId, $enemyId, $pokemonId, $originalUser, $originalPokemonHp);
}

printResults($results);
exit(count(array_filter($results, static fn (array $row): bool => !$row['ok'])) === 0 ? 0 : 1);

function userId(PDO $db, string $login): int
{
    $stmt = $db->prepare('SELECT id FROM users WHERE login = :login LIMIT 1');
    $stmt->execute(['login' => $login]);
    return (int) ($stmt->fetchColumn() ?: 0);
}

function playerPokemon(PDO $db, int $userId): ?array
{
    $stmt = $db->prepare(
        'SELECT id, basenum, names, lvl
           FROM pok_user
          WHERE users = :user AND hp_max > 0
          ORDER BY active DESC, lvl DESC, id DESC
          LIMIT 1'
    );
    $stmt->execute(['user' => $userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return is_array($row) ? $row : null;
}

function userBattleState(PDO $db, int $userId): ?array
{
    $stmt = $db->prepare('SELECT pve, pvp, battleid, atack_poke FROM users WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return is_array($row) ? $row : null;
}

function pokemonHpState(PDO $db, int $pokemonId): ?array
{
    $stmt = $db->prepare('SELECT hp_my, hp_max, active FROM pok_user WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $pokemonId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return is_array($row) ? $row : null;
}

function insertEnemy(PDO $db, int $enemyId, int $battleId): void
{
    $stmt = $db->prepare(
        'INSERT INTO pok_pve
            (id, users, basenum, names, evcount, lvl, sex, har, hp_my, hp_max, exp, exp_b,
             atk, def, satk, sdef, speed,
             hp_ev, atk_ev, def_ev, satk_ev, sdef_ev, speed_ev,
             hp_iv, atk_iv, def_iv, satk_iv, sdef_iv, speed_iv,
             tips, poimka, startepoke, reproduction, happy, battleid, sprz, ability_key)
         VALUES
            (:id, 0, 16, "Smoke Lock Pidgey", 0, 1, 1, 1, 10, 10, 0, 0,
             1, 1, 1, 1, 1,
             0, 0, 0, 0, 0, 0,
             1, 1, 1, 1, 1, 1,
             "normal", 1, 0, 0, 0, :battle, 0, NULL)'
    );
    $stmt->execute(['id' => $enemyId, 'battle' => $battleId]);
}

function insertBattle(PDO $db, int $battleId, int $userId, int $pokemonId, int $enemyId): void
{
    $now = time();
    $stmt = $db->prepare(
        'INSERT INTO battles
            (id, user_1, user_2, poke_1, poke_2, attac_1, attac_2, item_1, item_2,
             to_p, to_p2, batl_tip, time, pobeda, raund, effect_go, effect_go2,
             effect, effect2, id_pogodi, times, time_1, time_2, to_it, to_it2,
             hod_user_id, tips_battle, zamtru_1, zamtru_2, room, dates)
         VALUES
            (:id, :user, :enemy_user, :poke_1, :poke_2, 0, 0, 0, 0,
             "0", "0", "pve", :now, 0, 1, 0, 0,
             0, 0, 1, :expires, 0, 0, 0, 0,
             0, 0, 0, 0, 0, "")'
    );
    $stmt->execute([
        'id' => $battleId,
        'user' => $userId,
        'enemy_user' => $enemyId,
        'poke_1' => 'pvp_' . $pokemonId,
        'poke_2' => 'pve_' . $enemyId,
        'now' => $now,
        'expires' => $now + 3600,
    ]);
}

function battleWinner(PDO $db, int $battleId): int
{
    $stmt = $db->prepare('SELECT pobeda FROM battles WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $battleId]);
    return (int) ($stmt->fetchColumn() ?: 0);
}

function nextId(PDO $db, string $table, string $column): int
{
    $stmt = $db->query(sprintf('SELECT COALESCE(MAX(%s), 0) + 1 FROM %s', $column, $table));
    return max(1, (int) ($stmt ? $stmt->fetchColumn() : 1));
}

function cleanup(PDO $db, int $userId, int $battleId, int $enemyId, int $pokemonId, ?array $userState, ?array $pokemonHp): void
{
    if ($battleId > 0) {
        $db->prepare('DELETE FROM statpokemonbatle WHERE battleid = :id')->execute(['id' => $battleId]);
        $db->prepare('DELETE FROM battle_dop WHERE battleid = :id')->execute(['id' => $battleId]);
        $db->prepare('DELETE FROM battle_log WHERE battle_id = :id')->execute(['id' => $battleId]);
        $db->prepare('DELETE FROM bttle_status WHERE buttleid = :id')->execute(['id' => $battleId]);
        $db->prepare('DELETE FROM battles WHERE id = :id LIMIT 1')->execute(['id' => $battleId]);
    }
    if ($enemyId > 0) {
        $db->prepare('DELETE FROM pok_pve WHERE id = :id LIMIT 1')->execute(['id' => $enemyId]);
    }
    if ($userId > 0 && $userState !== null) {
        $db->prepare('UPDATE users SET pve = :pve, pvp = :pvp, battleid = :battle, atack_poke = :attack WHERE id = :id LIMIT 1')
            ->execute([
                'pve' => (int) ($userState['pve'] ?? 0),
                'pvp' => (int) ($userState['pvp'] ?? 0),
                'battle' => (int) ($userState['battleid'] ?? 0),
                'attack' => (int) ($userState['atack_poke'] ?? 0),
                'id' => $userId,
            ]);
    }
    if ($pokemonId > 0 && $pokemonHp !== null) {
        $db->prepare('UPDATE pok_user SET hp_my = :hp, hp_max = :max_hp, active = :active WHERE id = :id LIMIT 1')
            ->execute([
                'hp' => (int) ($pokemonHp['hp_my'] ?? 1),
                'max_hp' => (int) ($pokemonHp['hp_max'] ?? 1),
                'active' => (int) ($pokemonHp['active'] ?? 1),
                'id' => $pokemonId,
            ]);
    }
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
    echo sprintf("PvE action lock smoke: %d/%d passed.\n", $passed, count($results));
}
