<?php
declare(strict_types=1);

use Pokemon8\Database\Connection;
use Pokemon8\Support\Env;

require dirname(__DIR__) . '/src/Support/Autoload.php';

Env::load(dirname(__DIR__) . '/.env');
$config = require dirname(__DIR__) . '/config/database.php';
$db = Connection::make($config);

$apply = in_array('--apply', $argv, true);
$now = time();

$teams = [
    'Tacos' => [
        [
            'base' => 382,
            'level' => 100,
            'shiny' => true,
            'item' => 90200,
            'moves' => ['Water Spout', 'Origin Pulse', 'Ice Beam', 'Thunder'],
            'ev' => ['hp' => 4, 'atk' => 0, 'def' => 0, 'satk' => 252, 'sdef' => 0, 'speed' => 252],
            'ability' => 'drizzle',
        ],
        [
            'base' => 384,
            'level' => 100,
            'shiny' => false,
            'item' => 93,
            'moves' => ['Dragon Ascent', 'ExtremeSpeed', 'Earthquake', 'Dragon Dance'],
            'ev' => ['hp' => 4, 'atk' => 252, 'def' => 0, 'satk' => 0, 'sdef' => 0, 'speed' => 252],
            'ability' => 'air_lock',
        ],
        [
            'base' => 448,
            'level' => 100,
            'shiny' => false,
            'item' => 90244,
            'moves' => ['Close Combat', 'Meteor Mash', 'ExtremeSpeed', 'Swords Dance'],
            'ev' => ['hp' => 4, 'atk' => 252, 'def' => 0, 'satk' => 0, 'sdef' => 0, 'speed' => 252],
            'ability' => 'inner_focus',
        ],
        [
            'base' => 483,
            'level' => 100,
            'shiny' => false,
            'item' => 88,
            'moves' => ['Draco Meteor', 'Flash Cannon', 'Thunder', 'Stealth Rock'],
            'ev' => ['hp' => 252, 'atk' => 0, 'def' => 0, 'satk' => 252, 'sdef' => 4, 'speed' => 0],
            'ability' => 'pressure',
        ],
        [
            'base' => 644,
            'level' => 100,
            'shiny' => false,
            'item' => 87,
            'moves' => ['Bolt Strike', 'Dragon Claw', 'Fusion Bolt', 'Stone Edge'],
            'ev' => ['hp' => 4, 'atk' => 252, 'def' => 0, 'satk' => 0, 'sdef' => 0, 'speed' => 252],
            'ability' => 'teravolt',
        ],
        [
            'base' => 716,
            'level' => 100,
            'shiny' => false,
            'item' => 337,
            'moves' => ['Moonblast', 'Geomancy', 'Thunder', 'Focus Blast'],
            'ev' => ['hp' => 4, 'atk' => 0, 'def' => 0, 'satk' => 252, 'sdef' => 0, 'speed' => 252],
            'ability' => 'fairy_aura',
        ],
    ],
    'NIGA' => [
        [
            'base' => 383,
            'level' => 100,
            'shiny' => false,
            'item' => 90201,
            'moves' => ['Precipice Blades', 'Fire Punch', 'Stone Edge', 'Stealth Rock'],
            'ev' => ['hp' => 4, 'atk' => 252, 'def' => 252, 'satk' => 0, 'sdef' => 0, 'speed' => 0],
            'ability' => 'drought',
        ],
        [
            'base' => 6,
            'level' => 100,
            'shiny' => false,
            'item' => 90203,
            'moves' => ['Dragon Dance', 'Flare Blitz', 'Dragon Claw', 'Earthquake'],
            'ev' => ['hp' => 4, 'atk' => 252, 'def' => 0, 'satk' => 0, 'sdef' => 0, 'speed' => 252],
            'ability' => 'blaze',
        ],
        [
            'base' => 150,
            'level' => 100,
            'shiny' => false,
            'item' => 90216,
            'moves' => ['Psystrike', 'Recover', 'Aura Sphere', 'Fire Blast'],
            'ev' => ['hp' => 4, 'atk' => 0, 'def' => 0, 'satk' => 252, 'sdef' => 0, 'speed' => 252],
            'ability' => 'pressure',
        ],
        [
            'base' => 484,
            'level' => 100,
            'shiny' => false,
            'item' => 320,
            'moves' => ['Spacial Rend', 'Hydro Pump', 'Thunder', 'Fire Blast'],
            'ev' => ['hp' => 4, 'atk' => 0, 'def' => 0, 'satk' => 252, 'sdef' => 0, 'speed' => 252],
            'ability' => 'pressure',
        ],
        [
            'base' => 643,
            'level' => 100,
            'shiny' => false,
            'item' => 86,
            'moves' => ['Blue Flare', 'Draco Meteor', 'Fusion Flare', 'Earth Power'],
            'ev' => ['hp' => 4, 'atk' => 0, 'def' => 0, 'satk' => 252, 'sdef' => 0, 'speed' => 252],
            'ability' => 'turboblaze',
        ],
        [
            'base' => 717,
            'level' => 100,
            'shiny' => false,
            'item' => 181,
            'moves' => ['Oblivion Wing', 'Dark Pulse', 'Sucker Punch', 'Roost'],
            'ev' => ['hp' => 4, 'atk' => 0, 'def' => 0, 'satk' => 252, 'sdef' => 0, 'speed' => 252],
            'ability' => 'dark_aura',
        ],
    ],
];

$giftBoxes = [90020, 90021, 90022, 90023, 90024];
$extraItems = [90200, 90201, 90203, 90204, 90215, 90216, 90244, 210, 217, 219, 371, 86, 87, 88, 90, 91, 93, 181, 320, 337, 342, 344, 492];

function nextId(PDO $db, string $table, string $column): int
{
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $table) || !preg_match('/^[a-zA-Z0-9_]+$/', $column)) {
        throw new RuntimeException('Bad table name.');
    }
    return (int) ($db->query(sprintf('SELECT COALESCE(MAX(`%s`), 0) + 1 FROM `%s`', $column, $table))->fetchColumn() ?: 1);
}

function row(PDO $db, string $sql, array $params = []): ?array
{
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    return is_array($data) ? $data : null;
}

function scalar(PDO $db, string $sql, array $params = []): mixed
{
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn();
}

function plainPokemonName(array $base): string
{
    $id = (int) ($base['id'] ?? 0);
    $title = trim(strip_tags((string) ($base['title'] ?? '')));
    $title = preg_replace('/^#?0*' . $id . '\s*/u', '', $title) ?: $title;
    $title = preg_replace('/^#?0*\d+\s*/u', '', $title) ?: $title;
    return trim($title) !== '' ? trim($title) : ('Pokemon #' . $id);
}

function moveByName(PDO $db, string $name): array
{
    $stmt = $db->prepare(
        'SELECT atac_id, atac_name, COALESCE(atac_pp, 0) AS pp
           FROM attac_power
          WHERE LOWER(REPLACE(atac_name, " ", "")) = LOWER(REPLACE(:name, " ", ""))
          LIMIT 1'
    );
    $stmt->execute(['name' => $name]);
    $move = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$move) {
        throw new RuntimeException('Attack not found: ' . $name);
    }
    return $move;
}

function calcStats(array $base, int $level, array $iv, array $ev): array
{
    $stat = static function (int $baseValue, int $ivValue, int $evValue, int $level): int {
        return max(1, (int) floor((($ivValue + $baseValue * 2 + (int) floor($evValue / 4)) * $level / 100) + 5));
    };

    return [
        'hp' => max(1, (int) floor((($iv['hp'] + ((int) $base['hp'] * 2) + (int) floor($ev['hp'] / 4) + 100) * $level / 100) + 10)),
        'atk' => $stat((int) $base['atk'], $iv['atk'], $ev['atk'], $level),
        'def' => $stat((int) $base['def'], $iv['def'], $ev['def'], $level),
        'satk' => $stat((int) $base['satk'], $iv['satk'], $ev['satk'], $level),
        'sdef' => $stat((int) $base['sdef'], $iv['sdef'], $ev['sdef'], $level),
        'speed' => $stat((int) $base['speed'], $iv['speed'], $ev['speed'], $level),
    ];
}

function ensureItem(PDO $db, int $userId, int $itemId, int $count): int
{
    $existing = row($db, 'SELECT id, count FROM items_users WHERE user_id = :user AND item_id = :item AND dattimer = "not" LIMIT 1', [
        'user' => $userId,
        'item' => $itemId,
    ]);
    if ($existing) {
        $id = (int) $existing['id'];
        $current = (int) $existing['count'];
        if ($current < $count) {
            $stmt = $db->prepare('UPDATE items_users SET count = :count WHERE id = :id LIMIT 1');
            $stmt->execute(['count' => $count, 'id' => $id]);
        }
        return $id;
    }

    $id = nextId($db, 'items_users', 'id');
    $stmt = $db->prepare('INSERT INTO items_users (id, item_id, user_id, count, dattimer, timers) VALUES (:id, :item, :user, :count, "not", "not")');
    $stmt->execute(['id' => $id, 'item' => $itemId, 'user' => $userId, 'count' => $count]);
    return $id;
}

function ensureMoves(PDO $db, int $pokemonId, array $moves): void
{
    $slots = [];
    foreach (array_slice($moves, 0, 4) as $name) {
        $move = moveByName($db, $name);
        $slots[] = ['id' => (int) $move['atac_id'], 'pp' => max(0, (int) $move['pp']), 'name' => (string) $move['atac_name']];
    }
    while (count($slots) < 4) {
        $slots[] = ['id' => 0, 'pp' => 0, 'name' => ''];
    }

    $existing = row($db, 'SELECT id FROM attac_my_poke WHERE pok_id = :pokemon ORDER BY id ASC LIMIT 1', ['pokemon' => $pokemonId]);
    if ($existing) {
        $stmt = $db->prepare(
            'UPDATE attac_my_poke
                SET a_id = :a_id, a_pp_min = :a_pp_min, a_pp_max = :a_pp_max,
                    b_id = :b_id, b_pp_min = :b_pp_min, b_pp_max = :b_pp_max,
                    c_id = :c_id, c_pp_min = :c_pp_min, c_pp_max = :c_pp_max,
                    d_id = :d_id, d_pp_min = :d_pp_min, d_pp_max = :d_pp_max
              WHERE id = :id LIMIT 1'
        );
        $stmt->execute([
            'a_id' => $slots[0]['id'], 'a_pp_min' => $slots[0]['pp'], 'a_pp_max' => $slots[0]['pp'],
            'b_id' => $slots[1]['id'], 'b_pp_min' => $slots[1]['pp'], 'b_pp_max' => $slots[1]['pp'],
            'c_id' => $slots[2]['id'], 'c_pp_min' => $slots[2]['pp'], 'c_pp_max' => $slots[2]['pp'],
            'd_id' => $slots[3]['id'], 'd_pp_min' => $slots[3]['pp'], 'd_pp_max' => $slots[3]['pp'],
            'id' => (int) $existing['id'],
        ]);
        $db->prepare('DELETE FROM attac_my_poke WHERE pok_id = :pokemon AND id <> :id')->execute([
            'pokemon' => $pokemonId,
            'id' => (int) $existing['id'],
        ]);
        return;
    }

    $stmt = $db->prepare(
        'INSERT INTO attac_my_poke
            (id, pok_id, a_id, a_pp_min, a_pp_max, b_id, b_pp_min, b_pp_max, c_id, c_pp_min, c_pp_max, d_id, d_pp_min, d_pp_max)
         VALUES
            (:id, :pokemon, :a_id, :a_pp_min, :a_pp_max, :b_id, :b_pp_min, :b_pp_max, :c_id, :c_pp_min, :c_pp_max, :d_id, :d_pp_min, :d_pp_max)'
    );
    $stmt->execute([
        'id' => nextId($db, 'attac_my_poke', 'id'),
        'pokemon' => $pokemonId,
        'a_id' => $slots[0]['id'], 'a_pp_min' => $slots[0]['pp'], 'a_pp_max' => $slots[0]['pp'],
        'b_id' => $slots[1]['id'], 'b_pp_min' => $slots[1]['pp'], 'b_pp_max' => $slots[1]['pp'],
        'c_id' => $slots[2]['id'], 'c_pp_min' => $slots[2]['pp'], 'c_pp_max' => $slots[2]['pp'],
        'd_id' => $slots[3]['id'], 'd_pp_min' => $slots[3]['pp'], 'd_pp_max' => $slots[3]['pp'],
    ]);
}

function ensurePokemon(PDO $db, int $userId, array $spec, int $slot): int
{
    $baseId = (int) $spec['base'];
    $level = (int) $spec['level'];
    $tips = !empty($spec['shiny']) ? 'shine' : 'normal';
    $base = row($db, 'SELECT * FROM poke_base WHERE id = :id LIMIT 1', ['id' => $baseId]);
    if (!$base) {
        throw new RuntimeException('Base pokemon not found: #' . $baseId);
    }

    $iv = ['hp' => 31, 'atk' => 31, 'def' => 31, 'satk' => 31, 'sdef' => 31, 'speed' => 31];
    $ev = $spec['ev'];
    $stats = calcStats($base, $level, $iv, $ev);
    $name = plainPokemonName($base) . (!empty($spec['shiny']) ? ' - Shiny' : '');
    $pokemon = row(
        $db,
        'SELECT id FROM pok_user WHERE users = :user AND basenum = :base AND tips = :tips ORDER BY id ASC LIMIT 1',
        ['user' => $userId, 'base' => $baseId, 'tips' => $tips]
    );

    if ($pokemon) {
        $pokemonId = (int) $pokemon['id'];
        $stmt = $db->prepare(
            'UPDATE pok_user
                SET names = :name, active = 1, startepoke = :starter, lvl = :lvl, hp_my = :hp_my, hp_max = :hp_max,
                    atk = :atk, def = :def, satk = :satk, sdef = :sdef, speed = :speed,
                    hp_ev = :hp_ev, atk_ev = :atk_ev, def_ev = :def_ev, satk_ev = :satk_ev, sdef_ev = :sdef_ev, speed_ev = :speed_ev,
                    hp_iv = 31, atk_iv = 31, def_iv = 31, satk_iv = 31, sdef_iv = 31, speed_iv = 31,
                    evcount = :evcount, item = :item, happy = 100, ability_key = :ability
              WHERE id = :id LIMIT 1'
        );
        $stmt->execute([
            'name' => $name,
            'starter' => $slot === 0 ? 1 : 0,
            'lvl' => $level,
            'hp_my' => $stats['hp'],
            'hp_max' => $stats['hp'],
            'atk' => $stats['atk'],
            'def' => $stats['def'],
            'satk' => $stats['satk'],
            'sdef' => $stats['sdef'],
            'speed' => $stats['speed'],
            'hp_ev' => $ev['hp'],
            'atk_ev' => $ev['atk'],
            'def_ev' => $ev['def'],
            'satk_ev' => $ev['satk'],
            'sdef_ev' => $ev['sdef'],
            'speed_ev' => $ev['speed'],
            'evcount' => array_sum($ev),
            'item' => (int) $spec['item'],
            'ability' => (string) $spec['ability'],
            'id' => $pokemonId,
        ]);
    } else {
        $pokemonId = nextId($db, 'pok_user', 'id');
        $stmt = $db->prepare(
            'INSERT INTO pok_user
                (id, users, basenum, names, active, evcount, lvl, sex, har, hp_my, hp_max, exp, exp_b,
                 atk, def, satk, sdef, speed, hp_ev, atk_ev, def_ev, satk_ev, sdef_ev, speed_ev,
                 hp_iv, atk_iv, def_iv, satk_iv, sdef_iv, speed_iv, tips, startone, startepoke,
                 reproduction, happy, datemay, usersone, sprz, item, ability_key)
             VALUES
                (:id, :user, :base, :name, 1, :evcount, :lvl, 1, 1, :hp_my, :hp_max, 0, 100,
                 :atk, :def, :satk, :sdef, :speed, :hp_ev, :atk_ev, :def_ev, :satk_ev, :sdef_ev, :speed_ev,
                 31, 31, 31, 31, 31, 31, :tips, 0, :starter,
                 0, 100, NOW(), :usersone, 0, :item, :ability)'
        );
        $stmt->execute([
            'id' => $pokemonId,
            'user' => $userId,
            'base' => $baseId,
            'name' => $name,
            'evcount' => array_sum($ev),
            'lvl' => $level,
            'hp_my' => $stats['hp'],
            'hp_max' => $stats['hp'],
            'atk' => $stats['atk'],
            'def' => $stats['def'],
            'satk' => $stats['satk'],
            'sdef' => $stats['sdef'],
            'speed' => $stats['speed'],
            'hp_ev' => $ev['hp'],
            'atk_ev' => $ev['atk'],
            'def_ev' => $ev['def'],
            'satk_ev' => $ev['satk'],
            'sdef_ev' => $ev['sdef'],
            'speed_ev' => $ev['speed'],
            'tips' => $tips,
            'starter' => $slot === 0 ? 1 : 0,
            'usersone' => $userId,
            'item' => (int) $spec['item'],
            'ability' => (string) $spec['ability'],
        ]);
    }

    ensureMoves($db, $pokemonId, $spec['moves']);
    return $pokemonId;
}

function grantGymBadge(PDO $db, int $userId, string $badgeKey, int $adminId, int $now): void
{
    $badgeId = (int) scalar($db, 'SELECT id FROM gym_badges WHERE badge_key = :key LIMIT 1', ['key' => $badgeKey]);
    if ($badgeId <= 0) {
        return;
    }
    $exists = (int) scalar($db, 'SELECT id FROM user_gym_badges WHERE user_id = :user AND badge_id = :badge LIMIT 1', [
        'user' => $userId,
        'badge' => $badgeId,
    ]);
    if ($exists > 0) {
        return;
    }
    $stmt = $db->prepare(
        'INSERT INTO user_gym_badges (id, user_id, badge_id, source_type, source_id, awarded_by, awarded_at, created_at)
         VALUES (:id, :user, :badge, "qa", 0, :admin, :awarded_at, :created_at)'
    );
    $stmt->execute([
        'id' => nextId($db, 'user_gym_badges', 'id'),
        'user' => $userId,
        'badge' => $badgeId,
        'admin' => $adminId,
        'awarded_at' => $now,
        'created_at' => $now,
    ]);
}

try {
    $db->beginTransaction();
    $report = [];
    $adminId = (int) (scalar($db, 'SELECT id FROM users WHERE groups = 1 ORDER BY id ASC LIMIT 1') ?: 1);

    foreach ($teams as $login => $specs) {
        $user = row($db, 'SELECT id, login FROM users WHERE LOWER(login) = LOWER(:login) LIMIT 1', ['login' => $login]);
        if (!$user) {
            throw new RuntimeException('User not found: ' . $login);
        }

        $userId = (int) $user['id'];
        $teamIds = [];
        foreach (array_values($specs) as $slot => $spec) {
            $pokemonId = ensurePokemon($db, $userId, $spec, $slot);
            $teamIds[] = $pokemonId;
            if ((int) $spec['item'] > 0) {
                ensureItem($db, $userId, (int) $spec['item'], 2);
            }
            $report[] = sprintf('%s: #%d prepared for base #%d', $login, $pokemonId, (int) $spec['base']);
        }

        if ($teamIds !== []) {
            $placeholders = implode(',', array_fill(0, count($teamIds), '?'));
            $stmt = $db->prepare('UPDATE pok_user SET active = 0, startepoke = 0 WHERE users = ? AND id NOT IN (' . $placeholders . ')');
            $stmt->execute(array_merge([$userId], $teamIds));
        }

        foreach ($extraItems as $itemId) {
            ensureItem($db, $userId, $itemId, 2);
        }
        foreach ($giftBoxes as $giftId) {
            ensureItem($db, $userId, $giftId, 1);
        }
    }

    $tacosId = (int) (scalar($db, 'SELECT id FROM users WHERE LOWER(login) = "tacos" LIMIT 1') ?: 0);
    if ($tacosId > 0) {
        grantGymBadge($db, $tacosId, 'boulder', $adminId, $now);
        grantGymBadge($db, $tacosId, 'cascade', $adminId, $now);
    }

    if ($apply) {
        $db->commit();
        echo "QA teams applied.\n";
    } else {
        $db->rollBack();
        echo "Dry run only. Add --apply to write changes.\n";
    }
    foreach ($report as $line) {
        echo "- " . $line . "\n";
    }
} catch (Throwable $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    fwrite(STDERR, 'Failed: ' . $e->getMessage() . "\n");
    exit(1);
}
