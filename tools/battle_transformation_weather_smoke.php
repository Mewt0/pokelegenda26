<?php
declare(strict_types=1);

use Pokemon8\Database\Connection;
use Pokemon8\Game\BattleEngineService;
use Pokemon8\Repository\BattleRepository;
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
$repo = new BattleRepository($db);
$replay = new BattleReplayRepository($db);
$repo->setBattleReplayRepository($replay);
$engine = new BattleEngineService($repo, null, null, null, $replay);
$results = [];

$applyWeather = privateMethod($engine, 'applySingleEntryWeatherAbility');
$weatherBlock = privateMethod($engine, 'weatherBlockedMoveText');
$damageMove = privateMethod($engine, 'damageMove');
$markReverted = privateMethod($repo, 'markBattleTransformationsReverted');

$db->beginTransaction();
try {
    $tacosId = userId($db, 'Tacos');
    $nigaId = userId($db, 'NIGA');
    assertTrue($results, 'users.ready', $tacosId > 0 && $nigaId > 0, 'Tacos=' . $tacosId . ', NIGA=' . $nigaId);

    $battleId = unusedId($db, 'battles', 'id', 790000000);
    $kyogreId = unusedId($db, 'pok_user', 'id', 790100000);
    $groudonId = unusedId($db, 'pok_user', 'id', 790200000);
    $rayquazaId = unusedId($db, 'pok_user', 'id', 790300000);
    $targetId = unusedId($db, 'pok_user', 'id', 790400000);

    createPokemon($db, $kyogreId, $tacosId, 382, 'Smoke Kyogre', 90200, [
        'hp_max' => 342, 'hp_my' => 342, 'atk' => 236, 'def' => 216, 'satk' => 359, 'sdef' => 316, 'speed' => 307,
    ]);
    createPokemon($db, $groudonId, $nigaId, 383, 'Smoke Groudon', 90201, [
        'hp_max' => 342, 'hp_my' => 342, 'atk' => 399, 'def' => 379, 'satk' => 236, 'sdef' => 216, 'speed' => 216,
    ]);
    createPokemon($db, $rayquazaId, $tacosId, 384, 'Smoke Rayquaza', 0, [
        'hp_max' => 352, 'hp_my' => 352, 'atk' => 399, 'def' => 216, 'satk' => 336, 'sdef' => 216, 'speed' => 289,
    ]);
    createPokemon($db, $targetId, $nigaId, 187, 'Smoke Hoppip', 0, [
        'hp_max' => 300, 'hp_my' => 300, 'atk' => 80, 'def' => 100, 'satk' => 80, 'sdef' => 100, 'speed' => 100,
    ]);
    createMoves($db, $kyogreId, [323, 58, 87, 57]);
    createMoves($db, $groudonId, [7, 444, 157, 53]);
    createMoves($db, $rayquazaId, [570, 58, 157, 85]);
    createMoves($db, $targetId, [58, 157, 85, 53]);
    createBattle($db, $battleId, $tacosId, $nigaId, 'pvp_' . $groudonId, 'pvp_' . $kyogreId);

    $groudon = $repo->findPokemon('pvp_' . $groudonId, $battleId);
    $kyogre = $repo->findPokemon('pvp_' . $kyogreId, $battleId);
    $rayquaza = $repo->findPokemon('pvp_' . $rayquazaId, $battleId);
    $target = $repo->findPokemon('pvp_' . $targetId, $battleId);

    assertSame($results, 'primal.groudon.form', 5018, (int) ($groudon['basenum'] ?? 0));
    assertSame($results, 'primal.kyogre.form', 5017, (int) ($kyogre['basenum'] ?? 0));
    assertSame($results, 'mega.rayquaza.form', 5019, (int) ($rayquaza['basenum'] ?? 0));
    assertSame($results, 'multi.transform.active', 3, activeTransformations($db, $battleId));
    assertSame($results, 'permanent.kyogre.basenum', 382, permanentBase($db, $kyogreId));
    assertSame($results, 'permanent.groudon.basenum', 383, permanentBase($db, $groudonId));
    assertSame($results, 'permanent.rayquaza.basenum', 384, permanentBase($db, $rayquazaId));

    $applyWeather->invoke($engine, $battleId, 1, $groudon);
    assertWeather($results, $repo, 'weather.groudon.starts', $battleId, 'harsh_sun');
    $applyWeather->invoke($engine, $battleId, 1, $kyogre);
    assertWeather($results, $repo, 'weather.kyogre.overrides.groudon', $battleId, 'heavy_rain');
    $applyWeather->invoke($engine, $battleId, 1, $groudon);
    assertWeather($results, $repo, 'weather.groudon.overrides.kyogre', $battleId, 'harsh_sun');
    $applyWeather->invoke($engine, $battleId, 1, $rayquaza);
    assertWeather($results, $repo, 'weather.rayquaza.overrides.strong', $battleId, 'strong_winds');

    $repo->setBattleWeather($battleId, 'heavy_rain', 999999);
    $fireBlocked = (string) $weatherBlock->invoke($engine, $battleId, 'Smoke Hoppip', 'Flamethrower', 'Fire');
    assertTrue($results, 'weather.fire.blocked', str_contains($fireBlocked, '[DAMAGE_BLOCKED] Fire move blocked by Primordial Sea'), $fireBlocked);

    $repo->setBattleWeather($battleId, 'harsh_sun', 999999);
    $waterBlocked = (string) $weatherBlock->invoke($engine, $battleId, 'Smoke Kyogre', 'Surf', 'Water');
    assertTrue($results, 'weather.water.blocked', str_contains($waterBlocked, '[DAMAGE_BLOCKED] Water move blocked by Desolate Land'), $waterBlocked);

    $repo->setBattleWeather($battleId, 'heavy_rain', 999999);
    $waterMove = moveById($db, 323);
    $targetForWater = $target;
    $waterText = $damageMove->invokeArgs($engine, [$battleId, 1, $kyogre, &$targetForWater, $waterMove]);
    $waterPayload = latestDamagePayload($db, $battleId, 'Water Spout');
    assertSameFloat($results, 'weather.water.boost', 1.5, (float) ($waterPayload['modifiers']['weather'] ?? 0.0), $waterText);
    assertTrue($results, 'weather.water.boost.log', hasModifierLog($waterPayload, 'Water move boosted by Primordial Sea'), json_encode($waterPayload['modifier_logs'] ?? [], JSON_UNESCAPED_UNICODE));

    $repo->setBattleWeather($battleId, 'harsh_sun', 999999);
    $fireMove = moveById($db, 7);
    $targetForFire = $target;
    $fireText = $damageMove->invokeArgs($engine, [$battleId, 1, $groudon, &$targetForFire, $fireMove]);
    $firePayload = latestDamagePayload($db, $battleId, 'Fire Punch');
    assertSameFloat($results, 'weather.fire.boost', 1.5, (float) ($firePayload['modifiers']['weather'] ?? 0.0), $fireText);
    assertTrue($results, 'weather.fire.boost.log', hasModifierLog($firePayload, 'Fire move boosted by Desolate Land'), json_encode($firePayload['modifier_logs'] ?? [], JSON_UNESCAPED_UNICODE));

    $repo->setBattleWeather($battleId, 'strong_winds', 999999);
    $iceMove = moveById($db, 58);
    $rayquazaForIce = $rayquaza;
    $iceText = $damageMove->invokeArgs($engine, [$battleId, 1, $kyogre, &$rayquazaForIce, $iceMove]);
    $icePayload = latestDamagePayload($db, $battleId, 'Ice Beam');
    assertSameFloat($results, 'weather.delta.ice.x2', 2.0, (float) ($icePayload['type_effectiveness'] ?? 0.0), $iceText);
    assertTrue($results, 'weather.delta.log', hasModifierLog($icePayload, 'Flying weakness neutralized by Delta Stream'), json_encode($icePayload['modifier_logs'] ?? [], JSON_UNESCAPED_UNICODE));

    $markReverted->invoke($repo, $battleId);
    assertSame($results, 'revert.active.zero', 0, activeTransformations($db, $battleId));
    assertTrue($results, 'log.transform', battleLogContains($db, $battleId, '[TRANSFORM] Smoke Kyogre -> Primal Kyogre by blue_orb.'), '');
    assertTrue($results, 'log.weather', battleLogContains($db, $battleId, '[WEATHER] Delta Stream activated by Mega Rayquaza.'), '');
    assertTrue($results, 'log.revert', battleLogContains($db, $battleId, '[REVERT] Mega Rayquaza -> Rayquaza after battle.'), '');
    assertTrue($results, 'replay.transform.log', replayRoundLogContains($db, $battleId, '[TRANSFORM] Smoke Groudon -> Primal Groudon by red_orb.'), '');
    assertTrue($results, 'replay.damage.delta', replayDamageContains($db, $battleId, 'Ice Beam', 'Flying weakness neutralized by Delta Stream'), '');

    $db->rollBack();
} catch (Throwable $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    $results[] = ['name' => 'exception', 'ok' => false, 'details' => $e->getMessage()];
}

printResults($results);
exit(count(array_filter($results, static fn (array $row): bool => !$row['ok'])) === 0 ? 0 : 1);

function privateMethod(object $object, string $name): object
{
    // Метод мог переехать из движка в один из его сервисов (TASK-047): ищем и там,
    // чтобы смоук не зависел от того, в каком классе живёт логика. Обёртка принимает
    // тот же первый аргумент-объект, что и ReflectionMethod::invoke(), но вызывает
    // метод на найденном владельце.
    $target = $object;
    if (!method_exists($target, $name)) {
        $reflection = new ReflectionObject($object);
        foreach ($reflection->getProperties() as $property) {
            $property->setAccessible(true);
            if (!$property->isInitialized($object)) {
                continue;
            }
            $value = $property->getValue($object);
            if (is_object($value) && method_exists($value, $name)) {
                $target = $value;
                break;
            }
        }
    }

    $method = new ReflectionMethod($target, $name);
    $method->setAccessible(true);

    return new class ($method, $target) {
        public function __construct(
            private ReflectionMethod $method,
            private object $target,
        ) {
        }

        public function invoke(?object $ignored = null, mixed ...$args): mixed
        {
            return $this->method->invoke($this->target, ...$args);
        }

        public function invokeArgs(?object $ignored = null, array $args = []): mixed
        {
            return $this->method->invokeArgs($this->target, $args);
        }
    };
}

function userId(PDO $db, string $login): int
{
    $stmt = $db->prepare('SELECT id FROM users WHERE login = :login LIMIT 1');
    $stmt->execute(['login' => $login]);
    return (int) ($stmt->fetchColumn() ?: 0);
}

function unusedId(PDO $db, string $table, string $column, int $base): int
{
    for ($i = 0; $i < 30; $i++) {
        $id = $base + random_int(1000, 999999);
        $stmt = $db->prepare(sprintf('SELECT 1 FROM `%s` WHERE `%s` = :id LIMIT 1', $table, $column));
        $stmt->execute(['id' => $id]);
        if ($stmt->fetchColumn() === false) {
            return $id;
        }
    }
    throw new RuntimeException('Cannot allocate temporary id for ' . $table);
}

/** @param array<string,int> $stats */
function createPokemon(PDO $db, int $id, int $userId, int $baseId, string $name, int $heldItem, array $stats): void
{
    $stmt = $db->prepare(
        'INSERT INTO pok_user
            (id, users, basenum, names, active, evcount, lvl, sex, har,
             hp_my, hp_max, exp, exp_b, atk, def, satk, sdef, speed,
             hp_ev, atk_ev, def_ev, satk_ev, sdef_ev, speed_ev,
             hp_iv, atk_iv, def_iv, satk_iv, sdef_iv, speed_iv,
             tips, startone, startepoke, reproduction, happy, datemay, usersone, sprz, item,
             ability_key, training_stage, training_stat, training_named_effect, training_tamed, training_updated_at)
         VALUES
            (:id, :user, :base, :name, 1, 0, 100, 1, 1,
             :hp_my, :hp_max, 0, 0, :atk, :def, :satk, :sdef, :speed,
             0, 0, 0, 0, 0, 0,
             31, 31, 31, 31, 31, 31,
             "normal", 0, 0, 0, 0, :datemay, 0, 0, :item,
             NULL, 0, "", "", 0, 0)'
    );
    $stmt->execute([
        'id' => $id,
        'user' => $userId,
        'base' => $baseId,
        'name' => $name,
        'hp_my' => $stats['hp_my'],
        'hp_max' => $stats['hp_max'],
        'atk' => $stats['atk'],
        'def' => $stats['def'],
        'satk' => $stats['satk'],
        'sdef' => $stats['sdef'],
        'speed' => $stats['speed'],
        'datemay' => date('Y-m-d H:i:s'),
        'item' => $heldItem,
    ]);
}

/** @param list<int> $moves */
function createMoves(PDO $db, int $pokemonId, array $moves): void
{
    $id = unusedId($db, 'attac_my_poke', 'id', 790500000);
    $stmt = $db->prepare(
        'INSERT INTO attac_my_poke
            (id, a_id, a_pp_min, a_pp_max, b_id, b_pp_min, b_pp_max, c_id, c_pp_min, c_pp_max, d_id, d_pp_min, d_pp_max, pok_id)
         VALUES
            (:id, :a, 10, 10, :b, 10, 10, :c, 10, 10, :d, 10, 10, :pokemon)'
    );
    $stmt->execute([
        'id' => $id,
        'a' => $moves[0] ?? 0,
        'b' => $moves[1] ?? 0,
        'c' => $moves[2] ?? 0,
        'd' => $moves[3] ?? 0,
        'pokemon' => $pokemonId,
    ]);
}

function createBattle(PDO $db, int $battleId, int $user1, int $user2, string $first, string $second): void
{
    $stmt = $db->prepare(
        'INSERT INTO battles
            (id, user_1, user_2, poke_1, poke_2, attac_1, attac_2, item_1, item_2,
             to_p, to_p2, batl_tip, time, pobeda, raund, effect_go, effect_go2,
             effect, effect2, id_pogodi, times, time_1, time_2, to_it, to_it2,
             hod_user_id, tips_battle, zamtru_1, zamtru_2, room, dates)
         VALUES
            (:id, :user1, :user2, :first, :second, 0, 0, 0, 0,
             "0", "0", "pvp", :created_time, 0, 1, 0, 0,
             0, 0, 1, :turn_time, 0, 0, 0, 0,
             0, 0, 0, 0, 0, "")'
    );
    $stmt->execute([
        'id' => $battleId,
        'user1' => $user1,
        'user2' => $user2,
        'first' => $first,
        'second' => $second,
        'created_time' => time(),
        'turn_time' => time(),
    ]);
}

function moveById(PDO $db, int $moveId): array
{
    $stmt = $db->prepare('SELECT atac_id AS id, atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy, critic FROM attac_power WHERE atac_id = :id LIMIT 1');
    $stmt->execute(['id' => $moveId]);
    $row = $stmt->fetch();
    if (!$row) {
        throw new RuntimeException('Missing move #' . $moveId);
    }
    return $row;
}

function activeTransformations(PDO $db, int $battleId): int
{
    $stmt = $db->prepare('SELECT COUNT(*) FROM battle_transformations WHERE battle_id = :battle AND active = 1');
    $stmt->execute(['battle' => $battleId]);
    return (int) $stmt->fetchColumn();
}

function permanentBase(PDO $db, int $pokemonId): int
{
    $stmt = $db->prepare('SELECT basenum FROM pok_user WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $pokemonId]);
    return (int) ($stmt->fetchColumn() ?: 0);
}

function assertWeather(array &$results, BattleRepository $repo, string $name, int $battleId, string $expected): void
{
    $actual = (string) (($repo->findBattleWeather($battleId)['kind'] ?? ''));
    assertSame($results, $name, $expected, $actual);
}

function latestDamagePayload(PDO $db, int $battleId, string $moveName): array
{
    $stmt = $db->prepare(
        'SELECT data_json
           FROM battle_replay_events
          WHERE battle_id = :battle AND event_type = "damage" AND move_name = :move
          ORDER BY id DESC
          LIMIT 1'
    );
    $stmt->execute(['battle' => $battleId, 'move' => $moveName]);
    $json = (string) ($stmt->fetchColumn() ?: '');
    $data = json_decode($json, true);
    return is_array($data) ? $data : [];
}

function hasModifierLog(array $payload, string $needle): bool
{
    foreach (($payload['modifier_logs'] ?? []) as $line) {
        if (is_string($line) && str_contains($line, $needle)) {
            return true;
        }
    }
    return false;
}

function battleLogContains(PDO $db, int $battleId, string $needle): bool
{
    $stmt = $db->prepare('SELECT demage FROM battle_log WHERE battle_id = :battle');
    $stmt->execute(['battle' => $battleId]);
    foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) ?: [] as $line) {
        if (is_string($line) && str_contains($line, $needle)) {
            return true;
        }
    }
    return false;
}

function replayRoundLogContains(PDO $db, int $battleId, string $needle): bool
{
    $stmt = $db->prepare('SELECT data_json FROM battle_replay_events WHERE battle_id = :battle AND event_type = "round_log"');
    $stmt->execute(['battle' => $battleId]);
    foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) ?: [] as $json) {
        $data = json_decode((string) $json, true);
        if (is_array($data) && str_contains((string) ($data['message'] ?? ''), $needle)) {
            return true;
        }
    }
    return false;
}

function replayDamageContains(PDO $db, int $battleId, string $moveName, string $needle): bool
{
    $payload = latestDamagePayload($db, $battleId, $moveName);
    return hasModifierLog($payload, $needle);
}

function assertTrue(array &$results, string $name, bool $ok, string $details = ''): void
{
    $results[] = ['name' => $name, 'ok' => $ok, 'details' => $details];
}

function assertSame(array &$results, string $name, int|string $expected, int|string $actual): void
{
    $results[] = [
        'name' => $name,
        'ok' => $expected === $actual,
        'details' => $expected === $actual ? (string) $actual : 'expected=' . $expected . ' actual=' . $actual,
    ];
}

function assertSameFloat(array &$results, string $name, float $expected, float $actual, string $details = ''): void
{
    $ok = abs($expected - $actual) < 0.0001;
    $results[] = [
        'name' => $name,
        'ok' => $ok,
        'details' => $ok ? (string) $actual : 'expected=' . $expected . ' actual=' . $actual . ($details !== '' ? ' text=' . $details : ''),
    ];
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
    echo sprintf("Battle transformation/weather smoke: %d/%d passed.\n", $passed, count($results));
}
