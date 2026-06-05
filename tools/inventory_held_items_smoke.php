<?php
declare(strict_types=1);

use Pokemon8\Database\Connection;
use Pokemon8\Repository\InventoryRepository;
use Pokemon8\Repository\PokemonEvolutionRepository;
use Pokemon8\Repository\SafeStorageRepository;
use Pokemon8\Support\Env;

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Run from CLI.\n");
    exit(2);
}

define('APP_ROOT', dirname(__DIR__));
require APP_ROOT . '/src/Support/Autoload.php';

Env::load(APP_ROOT . '/.env');
$db = Connection::make(require APP_ROOT . '/config/database.php');
$inventory = new InventoryRepository($db, new PokemonEvolutionRepository($db));
$inventory->setSafeStorageRepository(new SafeStorageRepository($db));
$results = [];

$userId = 0;
$pokemonIds = [];
try {
    $userId = cloneUser($db, 'Tacos');
    assertTrue($results, 'user.tacos', $userId > 0, 'id=' . $userId);
    $db->prepare('UPDATE users SET pve = 0, pvp = 0, trade = 0 WHERE id = :user LIMIT 1')->execute(['user' => $userId]);

    $generic = clonePokemon($db, $userId, 150, 'Smoke Mewtwo');
    $kyogre = clonePokemon($db, $userId, 382, 'Smoke Kyogre');
    $groudon = clonePokemon($db, $userId, 383, 'Smoke Groudon');
    $latias = clonePokemon($db, $userId, 380, 'Smoke Latias');
    $cubone = clonePokemon($db, $userId, 104, 'Smoke Cubone');
    $pokemonIds = [$generic, $kyogre, $groudon, $latias, $cubone];
    assertTrue($results, 'pokemon.fixtures', min($generic, $kyogre, $groudon, $latias, $cubone) > 0, 'ids=' . implode(',', [$generic, $kyogre, $groudon, $latias, $cubone]));

    $row90 = grantItemRow($db, $userId, 90, 1);
    $equip90 = $inventory->equipItemToPokemon($userId, $row90, $generic);
    assertTrue($results, 'equip.direct.twisted_spoon', ($equip90['ok'] ?? false) === true && heldItem($db, $generic) === 90, (string) ($equip90['message'] ?? ''));

    $before90 = $inventory->countItem($userId, 90);
    $row93 = grantItemRow($db, $userId, 93, 1);
    $target93 = $inventory->useTargetedItem($userId, $row93, $generic, 1);
    assertTrue($results, 'replace.use_target.dragon_fang', ($target93['ok'] ?? false) === true && heldItem($db, $generic) === 93, (string) ($target93['message'] ?? ''));
    assertTrue($results, 'replace.returns_old_item', $inventory->countItem($userId, 90) === $before90 + 1, 'count90=' . $inventory->countItem($userId, 90));

    $before93 = $inventory->countItem($userId, 93);
    $unequip = $inventory->unequipPokemonItem($userId, $generic);
    assertTrue($results, 'unequip.returns_item', ($unequip['ok'] ?? false) === true && heldItem($db, $generic) === 0 && $inventory->countItem($userId, 93) === $before93 + 1, (string) ($unequip['message'] ?? ''));

    $badBlue = grantItemRow($db, $userId, 90200, 1);
    $beforeBlue = $inventory->countItem($userId, 90200);
    $badBlueResult = $inventory->equipItemToPokemon($userId, $badBlue, $generic);
    assertTrue($results, 'compat.blue_orb.blocks_non_kyogre', ($badBlueResult['ok'] ?? false) === false && $inventory->countItem($userId, 90200) === $beforeBlue, (string) ($badBlueResult['message'] ?? ''));

    $rowBlue = grantItemRow($db, $userId, 90200, 1);
    $blue = $inventory->equipItemToPokemon($userId, $rowBlue, $kyogre);
    assertTrue($results, 'compat.blue_orb.kyogre', ($blue['ok'] ?? false) === true && heldItem($db, $kyogre) === 90200, (string) ($blue['message'] ?? ''));

    $rowRed = grantItemRow($db, $userId, 90201, 1);
    $red = $inventory->equipItemToPokemon($userId, $rowRed, $groudon);
    assertTrue($results, 'compat.red_orb.groudon', ($red['ok'] ?? false) === true && heldItem($db, $groudon) === 90201, (string) ($red['message'] ?? ''));

    $badSoul = grantItemRow($db, $userId, 344, 1);
    $beforeSoul = $inventory->countItem($userId, 344);
    $badSoulResult = $inventory->equipItemToPokemon($userId, $badSoul, $generic);
    assertTrue($results, 'compat.soul_dew.blocks_non_lati', ($badSoulResult['ok'] ?? false) === false && $inventory->countItem($userId, 344) === $beforeSoul, (string) ($badSoulResult['message'] ?? ''));

    $rowSoul = grantItemRow($db, $userId, 344, 1);
    $soul = $inventory->equipItemToPokemon($userId, $rowSoul, $latias);
    assertTrue($results, 'compat.soul_dew.latias', ($soul['ok'] ?? false) === true && heldItem($db, $latias) === 344, (string) ($soul['message'] ?? ''));

    $badClub = grantItemRow($db, $userId, 358, 1);
    $beforeClub = $inventory->countItem($userId, 358);
    $badClubResult = $inventory->equipItemToPokemon($userId, $badClub, $generic);
    assertTrue($results, 'compat.thick_club.blocks_non_cubone', ($badClubResult['ok'] ?? false) === false && $inventory->countItem($userId, 358) === $beforeClub, (string) ($badClubResult['message'] ?? ''));

    $rowClub = grantItemRow($db, $userId, 358, 1);
    $club = $inventory->equipItemToPokemon($userId, $rowClub, $cubone);
    assertTrue($results, 'compat.thick_club.cubone', ($club['ok'] ?? false) === true && heldItem($db, $cubone) === 358, (string) ($club['message'] ?? ''));

    $rowTm = grantItemRow($db, $userId, 78, 1);
    $tmBefore = moveCount($db, $kyogre);
    $tm = $inventory->useTargetedItem($userId, $rowTm, $kyogre, 1);
    assertTrue($results, 'tm.learn.rollback_safe', ($tm['ok'] ?? false) === true && moveCount($db, $kyogre) >= $tmBefore, (string) ($tm['message'] ?? ''));

    $rowGift = grantItemRow($db, $userId, 90021, 1);
    $gift = $inventory->openGiftBox($userId, $rowGift);
    assertTrue($results, 'gift.open.server_rewards', ($gift['ok'] ?? false) === true && count($gift['rewards'] ?? []) > 0, (string) ($gift['message'] ?? ''));

    $mismatches = targetRuleMismatches($db);
    assertTrue($results, 'metadata.target_rules.normalized', $mismatches === [], json_encode($mismatches, JSON_UNESCAPED_UNICODE));

} catch (Throwable $e) {
    $results[] = ['name' => 'exception', 'ok' => false, 'details' => $e->getMessage()];
} finally {
    cleanupSmokeData($db, $userId, $pokemonIds);
}

printResults($results);
exit(count(array_filter($results, static fn (array $row): bool => !$row['ok'])) === 0 ? 0 : 1);

function cloneUser(PDO $db, string $login): int
{
    $stmt = $db->prepare('SELECT id FROM users WHERE LOWER(login) = LOWER(:login) LIMIT 1');
    $stmt->execute(['login' => $login]);
    $sourceId = (int) ($stmt->fetchColumn() ?: 0);
    if ($sourceId <= 0) {
        throw new RuntimeException('Source user not found: ' . $login);
    }

    $stmt = $db->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $sourceId]);
    $source = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!is_array($source) || $source === []) {
        throw new RuntimeException('Source user row not found.');
    }

    $id = nextId($db, 'users', 'id');
    $unique = 'CodexInventorySmoke_' . time() . '_' . random_int(1000, 9999);
    $source['id'] = $id;
    foreach (['login' => $unique, 'name' => $unique, 'email' => $unique . '@example.test', 'mail' => $unique . '@example.test', 'pve' => 0, 'pvp' => 0, 'trade' => 0, 'battleid' => 0] as $key => $value) {
        if (array_key_exists($key, $source)) {
            $source[$key] = $value;
        }
    }

    $columns = array_keys($source);
    $placeholders = array_map(static fn (string $column): string => ':' . $column, $columns);
    $insert = $db->prepare('INSERT INTO users (`' . implode('`,`', $columns) . '`) VALUES (' . implode(',', $placeholders) . ')');
    foreach ($source as $column => $value) {
        $insert->bindValue(':' . $column, $value);
    }
    $insert->execute();

    return $id;
}

function clonePokemon(PDO $db, int $userId, int $baseId, string $name): int
{
    $stmt = $db->prepare('SELECT * FROM pok_user WHERE users = 1 ORDER BY active DESC, id ASC LIMIT 1');
    $stmt->execute();
    $source = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!is_array($source) || $source === []) {
        throw new RuntimeException('No source pokemon for user ' . $userId);
    }

    $id = nextId($db, 'pok_user', 'id');
    $source['id'] = $id;
    foreach (['users' => $userId, 'basenum' => $baseId, 'names' => $name, 'active' => 1, 'startepoke' => 0, 'lvl' => 80, 'hp_my' => 250, 'hp_max' => 250] as $key => $value) {
        if (array_key_exists($key, $source)) {
            $source[$key] = $value;
        }
    }

    $columns = array_keys($source);
    $placeholders = array_map(static fn (string $column): string => ':' . $column, $columns);
    $insert = $db->prepare('INSERT INTO pok_user (`' . implode('`,`', $columns) . '`) VALUES (' . implode(',', $placeholders) . ')');
    foreach ($source as $column => $value) {
        $insert->bindValue(':' . $column, $value);
    }
    $insert->execute();

    return $id;
}

function cleanupSmokeData(PDO $db, int $userId, array $pokemonIds): void
{
    if ($pokemonIds !== []) {
        $ids = implode(',', array_map('intval', $pokemonIds));
        $db->exec('DELETE FROM items_poke WHERE id_poke IN (' . $ids . ')');
        $db->exec('DELETE FROM attac_my_poke WHERE pok_id IN (' . $ids . ')');
        $db->exec('DELETE FROM pok_user WHERE id IN (' . $ids . ')');
    }

    if ($userId > 0) {
        $stmt = $db->prepare('DELETE FROM items_users WHERE user_id = :user');
        $stmt->execute(['user' => $userId]);
        $stmt = $db->prepare('DELETE FROM users WHERE id = :user LIMIT 1');
        $stmt->execute(['user' => $userId]);
    }
}

function grantItemRow(PDO $db, int $userId, int $itemId, int $count): int
{
    $exists = $db->prepare('SELECT id FROM items WHERE id = :id LIMIT 1');
    $exists->execute(['id' => $itemId]);
    if (!$exists->fetchColumn()) {
        throw new RuntimeException('Missing item #' . $itemId);
    }

    $stmt = $db->prepare('INSERT INTO items_users (item_id, user_id, count, dattimer, timers) VALUES (:item, :user, :count, "not", "not")');
    $stmt->execute(['item' => $itemId, 'user' => $userId, 'count' => $count]);
    return (int) $db->lastInsertId();
}

function heldItem(PDO $db, int $pokemonId): int
{
    $stmt = $db->prepare('SELECT id_items FROM items_poke WHERE id_poke = :pokemon LIMIT 1');
    $stmt->execute(['pokemon' => $pokemonId]);
    return (int) ($stmt->fetchColumn() ?: 0);
}

function moveCount(PDO $db, int $pokemonId): int
{
    $stmt = $db->prepare('SELECT a_id, b_id, c_id, d_id FROM attac_my_poke WHERE pok_id = :pokemon LIMIT 1');
    $stmt->execute(['pokemon' => $pokemonId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    return count(array_filter(array_map('intval', [$row['a_id'] ?? 0, $row['b_id'] ?? 0, $row['c_id'] ?? 0, $row['d_id'] ?? 0])));
}

function targetRuleMismatches(PDO $db): array
{
    $stmt = $db->query(
        'SELECT m.item_id, m.target_use_rule, r.target_type, r.effect_key, r.enabled
           FROM item_gameplay_metadata m
           LEFT JOIN item_target_rules r ON r.item_id = m.item_id
          WHERE m.target_use_rule IN ("equip_held", "tm_learn", "pp_vitamin", "evolution_item", "open_gift")
          ORDER BY m.item_id'
    );
    $bad = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
        $expectedTarget = (string) $row['target_use_rule'] === 'open_gift' ? 'gift' : 'pokemon';
        $expectedEffect = (string) $row['target_use_rule'];
        if ((string) $row['target_use_rule'] === 'open_gift') {
            $expectedEffect = 'open_gift';
        }
        if ((int) ($row['enabled'] ?? 0) !== 1 || (string) ($row['target_type'] ?? '') !== $expectedTarget || (string) ($row['effect_key'] ?? '') !== $expectedEffect) {
            $bad[] = $row;
        }
    }
    return $bad;
}

function nextId(PDO $db, string $table, string $column): int
{
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $table) || !preg_match('/^[a-zA-Z0-9_]+$/', $column)) {
        throw new RuntimeException('Bad identifier.');
    }
    return (int) ($db->query(sprintf('SELECT COALESCE(MAX(`%s`), 0) + 1 FROM `%s`', $column, $table))->fetchColumn() ?: 1);
}

function assertTrue(array &$results, string $name, bool $ok, string $details = ''): void
{
    $results[] = ['name' => $name, 'ok' => $ok, 'details' => $details];
}

function printResults(array $results): void
{
    foreach ($results as $row) {
        printf("[%s] %s%s\n", $row['ok'] ? 'OK ' : 'FAIL', $row['name'], $row['details'] !== '' ? ' - ' . $row['details'] : '');
    }
    $passed = count(array_filter($results, static fn (array $row): bool => $row['ok']));
    printf("Inventory held-items smoke: %d/%d passed.\n", $passed, count($results));
}
