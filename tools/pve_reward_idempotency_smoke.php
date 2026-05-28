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
$db = Connection::make(require APP_ROOT . '/config/database.php');
$results = [];

$db->beginTransaction();
try {
    $userId = userId($db, 'Tacos');
    assertTrue($results, 'user.tacos', $userId > 0, 'id=' . $userId);

    $pokemon = playerPokemon($db, $userId);
    assertTrue($results, 'pokemon.ready', $pokemon !== null, json_encode($pokemon, JSON_UNESCAPED_UNICODE));
    $pokemonId = (int) ($pokemon['id'] ?? 0);

    $moveId = 323; // Water Spout: 150 power, 100 accuracy. Keeps the smoke deterministic.
    ensureMoveSlot($db, $pokemonId, $moveId);
    $beforePp = selectedMovePp($db, $pokemonId, $moveId);

    $enemyId = nextId($db, 'pok_pve', 'id');
    $battleId = nextId($db, 'battles', 'id');
    insertEnemy($db, $enemyId, $battleId);
    insertBattle($db, $battleId, $userId, $pokemonId, $enemyId);
    $db->prepare('UPDATE pok_user SET hp_my = hp_max, active = 1 WHERE id = :id LIMIT 1')->execute(['id' => $pokemonId]);
    $db->prepare('UPDATE users SET pve = 1, pvp = 0, battleid = :battle WHERE id = :user LIMIT 1')
        ->execute(['battle' => $battleId, 'user' => $userId]);

    $coinsBefore = itemCount($db, $userId, 1);
    $engine = new BattleEngineService(new BattleRepository($db), new RewardRepository($db));

    $first = $engine->action($userId, 'attack', ['move_id' => $moveId]);
    assertTrue($results, 'first.finished.win', ($first['finished'] ?? false) === true && ($first['result'] ?? '') === 'win', json_encode($first, JSON_UNESCAPED_UNICODE));
    $rewardCoins = (int) ($first['rewards']['coins'] ?? 0);
    assertTrue($results, 'first.reward.coins', $rewardCoins > 0, 'coins=' . $rewardCoins);

    $coinsAfterFirst = itemCount($db, $userId, 1);
    $second = $engine->action($userId, 'attack', ['move_id' => $moveId]);
    $coinsAfterSecond = itemCount($db, $userId, 1);
    $afterPp = selectedMovePp($db, $pokemonId, $moveId);

    assertTrue($results, 'first.coins.once', $coinsAfterFirst === $coinsBefore + $rewardCoins, 'before=' . $coinsBefore . ', after=' . $coinsAfterFirst . ', reward=' . $rewardCoins);
    assertTrue($results, 'second.no.extra.coins', $coinsAfterSecond === $coinsAfterFirst, 'first=' . $coinsAfterFirst . ', second=' . $coinsAfterSecond);
    assertTrue($results, 'second.no.reward.payload', (int) ($second['rewards']['coins'] ?? 0) === 0 && (int) ($second['rewards']['exp'] ?? 0) === 0, json_encode($second['rewards'] ?? [], JSON_UNESCAPED_UNICODE));
    assertTrue($results, 'pp.decrement.once', $afterPp === $beforePp - 1, 'before=' . $beforePp . ', after=' . $afterPp);
    assertTrue($results, 'battle.pobeda.once', battleWinner($db, $battleId) === $userId, 'winner=' . battleWinner($db, $battleId));

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

function ensureMoveSlot(PDO $db, int $pokemonId, int $moveId): void
{
    $rowId = (int) ($db->query('SELECT id FROM attac_my_poke WHERE pok_id = ' . (int) $pokemonId . ' LIMIT 1')->fetchColumn() ?: 0);
    if ($rowId > 0) {
        $stmt = $db->prepare(
            'UPDATE attac_my_poke
                SET a_id = :move, a_pp_min = 5, a_pp_max = 5,
                    b_id = 0, b_pp_min = 0, b_pp_max = 0,
                    c_id = 0, c_pp_min = 0, c_pp_max = 0,
                    d_id = 0, d_pp_min = 0, d_pp_max = 0
              WHERE id = :id LIMIT 1'
        );
        $stmt->execute(['move' => $moveId, 'id' => $rowId]);
        return;
    }

    $stmt = $db->prepare(
        'INSERT INTO attac_my_poke
            (id, pok_id, a_id, a_pp_min, a_pp_max, b_id, b_pp_min, b_pp_max, c_id, c_pp_min, c_pp_max, d_id, d_pp_min, d_pp_max)
         VALUES
            (:id, :pokemon, :move, 5, 5, 0, 0, 0, 0, 0, 0, 0, 0, 0)'
    );
    $stmt->execute([
        'id' => nextId($db, 'attac_my_poke', 'id'),
        'pokemon' => $pokemonId,
        'move' => $moveId,
    ]);
}

function selectedMovePp(PDO $db, int $pokemonId, int $moveId): int
{
    $stmt = $db->prepare('SELECT a_pp_min FROM attac_my_poke WHERE pok_id = :pokemon AND a_id = :move LIMIT 1');
    $stmt->execute(['pokemon' => $pokemonId, 'move' => $moveId]);
    return (int) ($stmt->fetchColumn() ?: 0);
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
            (:id, 0, 16, "Smoke Pidgey", 0, 1, 1, 1, 1, 1, 0, 0,
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

function itemCount(PDO $db, int $userId, int $itemId): int
{
    $stmt = $db->prepare('SELECT COALESCE(SUM(count), 0) FROM items_users WHERE user_id = :user AND item_id = :item');
    $stmt->execute(['user' => $userId, 'item' => $itemId]);
    return (int) ($stmt->fetchColumn() ?: 0);
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
    echo sprintf("PvE reward idempotency smoke: %d/%d passed.\n", $passed, count($results));
}
