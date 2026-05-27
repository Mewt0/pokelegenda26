<?php
declare(strict_types=1);

use Pokemon8\Database\Connection;
use Pokemon8\Repository\CommissionMarketRepository;
use Pokemon8\Repository\InventoryRepository;
use Pokemon8\Repository\PokemonEvolutionRepository;
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
$market = new CommissionMarketRepository($db, $inventory);

$results = [];
$tacos = userId($db, 'Tacos');
$niga = userId($db, 'NIGA');
assertTrue($results, 'users.tacos', $tacos > 0, 'id=' . $tacos);
assertTrue($results, 'users.niga', $niga > 0, 'id=' . $niga);

if ($tacos <= 0 || $niga <= 0) {
    printResults($results);
    exit(1);
}

$db->beginTransaction();
try {
    $db->prepare('UPDATE users SET pve = 0, pvp = 0, trade = 0 WHERE id IN (:a, :b)')
        ->execute(['a' => $tacos, 'b' => $niga]);

    smokeLists($results, $market, $tacos);
    smokeItemSale($db, $results, $market, $inventory, $tacos, $niga);
    smokeCancel($db, $results, $market, $inventory, $tacos);
    smokePokemonAndEggReserve($db, $results, $market, $tacos);
    smokeNegativeCases($db, $results, $market, $inventory, $tacos, $niga);

    $db->rollBack();
} catch (Throwable $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    $results[] = ['name' => 'exception', 'ok' => false, 'details' => $e->getMessage()];
}

printResults($results);
exit(count(array_filter($results, static fn (array $row): bool => !$row['ok'])) === 0 ? 0 : 1);

function smokeLists(array &$results, CommissionMarketRepository $market, int $userId): void
{
    $lots = $market->lots($userId, ['category' => 'all', 'sort' => 'new']);
    assertTrue($results, 'lots.index', ($lots['ok'] ?? false) === true && isset($lots['categories']), 'total=' . ($lots['total'] ?? 0));
    $sellable = $market->sellable($userId);
    assertTrue($results, 'sellable.index', ($sellable['ok'] ?? false) === true && count($sellable['items'] ?? []) > 0, 'items=' . count($sellable['items'] ?? []));
    $my = $market->myLots($userId);
    assertTrue($results, 'my.index', ($my['ok'] ?? false) === true && isset($my['active'], $my['history']), 'active=' . count($my['active'] ?? []));
}

function smokeItemSale(PDO $db, array &$results, CommissionMarketRepository $market, InventoryRepository $inventory, int $seller, int $buyer): void
{
    $item = firstSellableItem($market, $seller);
    $beforeSellerCoins = $inventory->countItem($seller, 1);
    $beforeBuyerItem = $inventory->countItem($buyer, (int) $item['id']);
    $price = 1000;
    $created = $market->createLot($seller, [
        'object_type' => 'item',
        'object_id' => (int) $item['id'],
        'quantity' => 1,
        'price_per_unit' => $price,
        'duration_hours' => 24,
    ]);
    assertTrue($results, 'item.create', ($created['ok'] ?? false) === true, $created['message'] ?? '');
    $lotId = (int) ($created['lot_id'] ?? 0);
    assertTrue($results, 'item.reserve', lotStatus($db, $lotId) === 'active' && $inventory->countItem($seller, (int) $item['id']) === (int) $item['count'] - 1, 'lot=' . $lotId);

    $buy = $market->buy($buyer, $lotId);
    assertTrue($results, 'item.buy', ($buy['ok'] ?? false) === true, $buy['message'] ?? '');
    assertTrue($results, 'item.sold.status', lotStatus($db, $lotId) === 'sold', 'status=' . lotStatus($db, $lotId));
    assertTrue($results, 'item.buyer.received', $inventory->countItem($buyer, (int) $item['id']) === $beforeBuyerItem + 1, 'item=' . $item['id']);
    assertTrue($results, 'item.seller.income', $inventory->countItem($seller, 1) >= $beforeSellerCoins + 950, 'coins=' . $inventory->countItem($seller, 1));

    $repeat = $market->buy($buyer, $lotId);
    assertTrue($results, 'item.repeat.buy.blocked', ($repeat['ok'] ?? true) === false, $repeat['message'] ?? '');
}

function smokeCancel(PDO $db, array &$results, CommissionMarketRepository $market, InventoryRepository $inventory, int $seller): void
{
    $item = firstSellableItem($market, $seller);
    $before = $inventory->countItem($seller, (int) $item['id']);
    $created = $market->createLot($seller, [
        'object_type' => 'item',
        'object_id' => (int) $item['id'],
        'quantity' => 1,
        'price_per_unit' => 777,
        'duration_hours' => 24,
    ]);
    $lotId = (int) ($created['lot_id'] ?? 0);
    $cancel = $market->cancel($seller, $lotId);
    assertTrue($results, 'item.cancel', ($cancel['ok'] ?? false) === true, $cancel['message'] ?? '');
    assertTrue($results, 'item.cancel.status', lotStatus($db, $lotId) === 'cancelled', 'status=' . lotStatus($db, $lotId));
    assertTrue($results, 'item.cancel.return', $inventory->countItem($seller, (int) $item['id']) === $before, 'count=' . $inventory->countItem($seller, (int) $item['id']));
}

function smokePokemonAndEggReserve(PDO $db, array &$results, CommissionMarketRepository $market, int $seller): void
{
    $reserveUserId = commissionReserveUserId($db);
    $sellable = $market->sellable($seller);
    $pokemon = firstAvailable($sellable['pokemon'] ?? []);
    if ($pokemon !== null) {
        $created = $market->createLot($seller, [
            'object_type' => 'pokemon',
            'object_id' => (int) $pokemon['id'],
            'quantity' => 1,
            'price_per_unit' => 5000,
            'duration_hours' => 24,
        ]);
        $lotId = (int) ($created['lot_id'] ?? 0);
        assertTrue($results, 'pokemon.create', ($created['ok'] ?? false) === true, $created['message'] ?? '');
        assertTrue($results, 'pokemon.reserve', pokemonOwner($db, (int) $pokemon['id']) === $reserveUserId, 'owner=' . pokemonOwner($db, (int) $pokemon['id']));
        $market->cancel($seller, $lotId);
        assertTrue($results, 'pokemon.return', pokemonOwner($db, (int) $pokemon['id']) === $seller, 'owner=' . pokemonOwner($db, (int) $pokemon['id']));
    } else {
        assertTrue($results, 'pokemon.skip', true, 'no sellable pokemon');
    }

    $sellable = $market->sellable($seller);
    $egg = firstAvailable($sellable['eggs'] ?? []);
    if ($egg !== null) {
        $created = $market->createLot($seller, [
            'object_type' => 'egg',
            'object_id' => (int) $egg['id'],
            'quantity' => 1,
            'price_per_unit' => 3000,
            'duration_hours' => 24,
        ]);
        $lotId = (int) ($created['lot_id'] ?? 0);
        assertTrue($results, 'egg.create', ($created['ok'] ?? false) === true, $created['message'] ?? '');
        assertTrue($results, 'egg.reserve', eggOwner($db, (int) $egg['id']) === $reserveUserId, 'owner=' . eggOwner($db, (int) $egg['id']));
        $market->cancel($seller, $lotId);
        assertTrue($results, 'egg.return', eggOwner($db, (int) $egg['id']) === $seller, 'owner=' . eggOwner($db, (int) $egg['id']));
    } else {
        assertTrue($results, 'egg.skip', true, 'no sellable eggs');
    }
}

function smokeNegativeCases(PDO $db, array &$results, CommissionMarketRepository $market, InventoryRepository $inventory, int $seller, int $buyer): void
{
    $inventory->addItem($seller, 15, 1);
    $forbidden = $market->createLot($seller, [
        'object_type' => 'item',
        'object_id' => 15,
        'quantity' => 1,
        'price_per_unit' => 10,
        'duration_hours' => 24,
    ]);
    assertTrue($results, 'forbidden.item.blocked', ($forbidden['ok'] ?? true) === false, $forbidden['message'] ?? '');

    $item = firstSellableItem($market, $seller);
    $created = $market->createLot($seller, [
        'object_type' => 'item',
        'object_id' => (int) $item['id'],
        'quantity' => 1,
        'price_per_unit' => 999999999,
        'duration_hours' => 24,
    ]);
    $lotId = (int) ($created['lot_id'] ?? 0);
    $own = $market->buy($seller, $lotId);
    assertTrue($results, 'own.buy.blocked', ($own['ok'] ?? true) === false, $own['message'] ?? '');
    $poor = $market->buy($buyer, $lotId);
    assertTrue($results, 'insufficient.coins.blocked', ($poor['ok'] ?? true) === false, $poor['message'] ?? '');
}

function firstSellableItem(CommissionMarketRepository $market, int $userId): array
{
    $sellable = $market->sellable($userId);
    foreach ($sellable['items'] ?? [] as $item) {
        if ((int) ($item['count'] ?? 0) > 0) {
            return $item;
        }
    }
    throw new RuntimeException('No sellable item found.');
}

function firstAvailable(array $rows): ?array
{
    foreach ($rows as $row) {
        if (empty($row['blocked'])) {
            return $row;
        }
    }
    return null;
}

function userId(PDO $db, string $login): int
{
    $stmt = $db->prepare('SELECT id FROM users WHERE login = :login LIMIT 1');
    $stmt->execute(['login' => $login]);
    return (int) ($stmt->fetchColumn() ?: 0);
}

function commissionReserveUserId(PDO $db): int
{
    $stmt = $db->query('SELECT CAST(value AS UNSIGNED) FROM site_settings WHERE name = "commission.reserve_user_id" LIMIT 1');
    $id = (int) ($stmt->fetchColumn() ?: 0);
    if ($id <= 0) {
        $stmt = $db->query('SELECT id FROM users WHERE login = "Система" ORDER BY id ASC LIMIT 1');
        $id = (int) ($stmt->fetchColumn() ?: 3);
    }
    return max(1, $id);
}

function lotStatus(PDO $db, int $lotId): string
{
    $stmt = $db->prepare('SELECT status FROM market_lots WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $lotId]);
    return (string) ($stmt->fetchColumn() ?: '');
}

function pokemonOwner(PDO $db, int $pokemonId): int
{
    $stmt = $db->prepare('SELECT users FROM pok_user WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $pokemonId]);
    return (int) ($stmt->fetchColumn() ?: 0);
}

function eggOwner(PDO $db, int $eggId): int
{
    $stmt = $db->prepare('SELECT users_egg FROM eggs WHERE id_egg = :id LIMIT 1');
    $stmt->execute(['id' => $eggId]);
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
        $status = $row['ok'] ? 'OK ' : 'FAIL';
        if ($row['ok']) {
            $passed++;
        }
        echo sprintf("[%s] %s %s\n", $status, $row['name'], $row['details'] ? '- ' . $row['details'] : '');
    }
    echo sprintf("Passed %d/%d checks.\n", $passed, count($results));
}
