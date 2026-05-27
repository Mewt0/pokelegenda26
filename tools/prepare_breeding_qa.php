<?php
declare(strict_types=1);

use Pokemon8\Database\Connection;
use Pokemon8\Repository\BreedingRepository;
use Pokemon8\Support\Env;

require dirname(__DIR__) . '/src/Support/Autoload.php';

Env::load(dirname(__DIR__) . '/.env');
$db = Connection::make(require dirname(__DIR__) . '/config/database.php');

function qaRow(PDO $db, string $sql, array $params = []): ?array
{
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return is_array($row) ? $row : null;
}

function qaNextId(PDO $db, string $table, string $column): int
{
    return (int) ($db->query('SELECT COALESCE(MAX(`' . $column . '`), 0) + 1 FROM `' . $table . '`')->fetchColumn() ?: 1);
}

function qaCleanPokemonName(array $base): string
{
    $id = (int) ($base['id'] ?? 0);
    $title = trim(strip_tags((string) ($base['title'] ?? '')));
    $title = preg_replace('/^#?0*' . $id . '\s*/u', '', $title) ?: $title;
    $title = preg_replace('/^#?0*\d+\s*/u', '', $title) ?: $title;
    return trim($title) !== '' ? trim($title) : ('Pokemon #' . $id);
}

function qaStats(array $base, int $level): array
{
    $iv = 20;
    $stat = static fn (int $baseValue): int => max(1, (int) floor((($iv + $baseValue * 2) * $level / 100) + 5));
    return [
        'hp' => max(1, (int) floor((($iv + ((int) $base['hp'] * 2) + 100) * $level / 100) + 10)),
        'atk' => $stat((int) $base['atk']),
        'def' => $stat((int) $base['def']),
        'satk' => $stat((int) $base['satk']),
        'sdef' => $stat((int) $base['sdef']),
        'speed' => $stat((int) $base['speed']),
    ];
}

function qaEnsureItem(PDO $db, int $userId, int $itemId, int $count): void
{
    $row = qaRow($db, 'SELECT id, count FROM items_users WHERE user_id = :user AND item_id = :item AND dattimer = "not" LIMIT 1', [
        'user' => $userId,
        'item' => $itemId,
    ]);
    if ($row) {
        if ((int) $row['count'] < $count) {
            $db->prepare('UPDATE items_users SET count = :count WHERE id = :id LIMIT 1')
                ->execute(['count' => $count, 'id' => (int) $row['id']]);
        }
        return;
    }

    $db->prepare('INSERT INTO items_users (id, item_id, user_id, count, dattimer, timers) VALUES (:id, :item, :user, :count, "not", "not")')
        ->execute([
            'id' => qaNextId($db, 'items_users', 'id'),
            'item' => $itemId,
            'user' => $userId,
            'count' => $count,
        ]);
}

function qaEquipItem(PDO $db, int $pokemonId, int $itemId): void
{
    $row = qaRow($db, 'SELECT id_poke FROM items_poke WHERE id_poke = :pokemon LIMIT 1', ['pokemon' => $pokemonId]);
    if ($row) {
        $db->prepare('UPDATE items_poke SET id_items = :item, datetime = "not" WHERE id_poke = :pokemon LIMIT 1')
            ->execute(['item' => $itemId, 'pokemon' => $pokemonId]);
        return;
    }

    $db->prepare('INSERT INTO items_poke (id_poke, id_items, datetime) VALUES (:pokemon, :item, "not")')
        ->execute(['pokemon' => $pokemonId, 'item' => $itemId]);
}

function qaEnsurePokemon(PDO $db, int $userId, int $baseId, string $nickname, int $sex, int $active = 0): int
{
    $base = qaRow($db, 'SELECT * FROM poke_base WHERE id = :id LIMIT 1', ['id' => $baseId]);
    if (!$base) {
        throw new RuntimeException('Base pokemon not found: #' . $baseId);
    }
    $level = 30;
    $stats = qaStats($base, $level);
    $name = $nickname . ' ' . qaCleanPokemonName($base);
    $existing = qaRow($db, 'SELECT id FROM pok_user WHERE users = :user AND names = :name LIMIT 1', [
        'user' => $userId,
        'name' => $name,
    ]);

    if ($existing) {
        $pokemonId = (int) $existing['id'];
        $db->prepare(
            'UPDATE pok_user
                SET basenum = :base, active = :active, lvl = :level, sex = :sex, har = 1,
                    hp_my = :hp_my, hp_max = :hp_max, atk = :atk, def = :def, satk = :satk, sdef = :sdef, speed = :speed,
                    hp_iv = 20, atk_iv = 20, def_iv = 20, satk_iv = 20, sdef_iv = 20, speed_iv = 20,
                    hp_ev = 0, atk_ev = 0, def_ev = 0, satk_ev = 0, sdef_ev = 0, speed_ev = 0,
                    evcount = 0, tips = "normal", startone = 0, startepoke = 0, reproduction = 0,
                    happy = 100, item = 0
              WHERE id = :id LIMIT 1'
        )->execute([
            'base' => $baseId,
            'active' => $active,
            'level' => $level,
            'sex' => $sex,
            'hp_my' => $stats['hp'],
            'hp_max' => $stats['hp'],
            'atk' => $stats['atk'],
            'def' => $stats['def'],
            'satk' => $stats['satk'],
            'sdef' => $stats['sdef'],
            'speed' => $stats['speed'],
            'id' => $pokemonId,
        ]);
        return $pokemonId;
    }

    $pokemonId = qaNextId($db, 'pok_user', 'id');
    $db->prepare(
        'INSERT INTO pok_user
            (id, users, basenum, names, active, evcount, lvl, sex, har, hp_my, hp_max, exp, exp_b,
             atk, def, satk, sdef, speed, hp_ev, atk_ev, def_ev, satk_ev, sdef_ev, speed_ev,
             hp_iv, atk_iv, def_iv, satk_iv, sdef_iv, speed_iv, tips, startone, startepoke,
             reproduction, happy, datemay, usersone, sprz, item)
         VALUES
            (:id, :user, :base, :name, :active, 0, :level, :sex, 1, :hp_my, :hp_max, 0, 0,
             :atk, :def, :satk, :sdef, :speed, 0, 0, 0, 0, 0, 0,
             20, 20, 20, 20, 20, 20, "normal", 0, 0,
             0, 100, NOW(), :usersone, 0, 0)'
    )->execute([
        'id' => $pokemonId,
        'user' => $userId,
        'base' => $baseId,
        'name' => $name,
        'active' => $active,
        'level' => $level,
        'sex' => $sex,
        'hp_my' => $stats['hp'],
        'hp_max' => $stats['hp'],
        'atk' => $stats['atk'],
        'def' => $stats['def'],
        'satk' => $stats['satk'],
        'sdef' => $stats['sdef'],
        'speed' => $stats['speed'],
        'usersone' => $userId,
    ]);

    return $pokemonId;
}

$users = [];
foreach (['Tacos', 'NIGA'] as $login) {
    $user = qaRow($db, 'SELECT id, login FROM users WHERE LOWER(login) = LOWER(:login) LIMIT 1', ['login' => $login]);
    if (!$user) {
        throw new RuntimeException('QA user not found: ' . $login);
    }
    $users[$login] = (int) $user['id'];
}

$created = [
    'Tacos' => [
        qaEnsurePokemon($db, $users['Tacos'], 1, 'QA Breed Male', 1, 0),
        qaEnsurePokemon($db, $users['Tacos'], 16, 'QA Breed Incompatible Male', 1, 0),
        qaEnsurePokemon($db, $users['Tacos'], 81, 'QA Breed Genderless', 0, 0),
        qaEnsurePokemon($db, $users['Tacos'], 382, 'QA Breed Legendary Blocked', 0, 0),
    ],
    'NIGA' => [
        qaEnsurePokemon($db, $users['NIGA'], 1, 'QA Breed Female', 2, 0),
        qaEnsurePokemon($db, $users['NIGA'], 132, 'QA Breed Ditto', 0, 0),
        qaEnsurePokemon($db, $users['NIGA'], 81, 'QA Breed Genderless Partner', 0, 0),
        qaEnsurePokemon($db, $users['NIGA'], 4, 'QA Breed Wrong Group Female', 2, 0),
    ],
];

qaEnsureItem($db, $users['Tacos'], BreedingRepository::DITTO_EXTRACT_ITEM_ID, 3);
qaEnsureItem($db, $users['NIGA'], BreedingRepository::DITTO_EXTRACT_ITEM_ID, 3);
qaEnsureItem($db, $users['Tacos'], Pokemon8\Repository\EggRepository::INCUBATOR_ITEM_ID, 3);
qaEnsureItem($db, $users['NIGA'], Pokemon8\Repository\EggRepository::INCUBATOR_ITEM_ID, 3);
qaEquipItem($db, $created['Tacos'][2], BreedingRepository::DITTO_EXTRACT_ITEM_ID);
qaEquipItem($db, $created['NIGA'][2], BreedingRepository::DITTO_EXTRACT_ITEM_ID);

echo json_encode([
    'ok' => true,
    'users' => $users,
    'pokemon' => $created,
    'ditto_extract_item_id' => BreedingRepository::DITTO_EXTRACT_ITEM_ID,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
