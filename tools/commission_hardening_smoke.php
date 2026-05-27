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
$options = parseArgs($argv);
$iterations = max(1, min(600, (int) ($options['iterations'] ?? 60)));

$results = [];
$tacos = userId($db, 'Tacos');
$niga = userId($db, 'NIGA');
assertTrue($results, 'users.tacos', $tacos > 0, 'id=' . $tacos);
assertTrue($results, 'users.niga', $niga > 0, 'id=' . $niga);

if ($tacos <= 0 || $niga <= 0) {
    printResults($results);
    exit(1);
}

try {
    transactionalHardening($db, $results, $market, $inventory, $tacos, $niga, $iterations);
    expireCronHardening($db, $results, $market, $inventory, $tacos);
} catch (Throwable $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    $results[] = ['name' => 'exception', 'ok' => false, 'details' => $e->getMessage()];
}

printResults($results);
exit(count(array_filter($results, static fn (array $row): bool => !$row['ok'])) === 0 ? 0 : 1);

function transactionalHardening(PDO $db, array &$results, CommissionMarketRepository $market, InventoryRepository $inventory, int $seller, int $buyer, int $iterations): void
{
    $db->beginTransaction();
    try {
        $db->prepare('UPDATE users SET pve = 0, pvp = 0, trade = 0 WHERE id IN (:a, :b)')
            ->execute(['a' => $seller, 'b' => $buyer]);
        $inventory->addItem($seller, 1, 5_000_000);
        $inventory->addItem($buyer, 1, 5_000_000);

        smokeSettings($db, $results);
        smokeFreezeAndDuplicateBuy($db, $results, $market, $inventory, $seller, $buyer);
        smokeCancelFreeze($db, $results, $market, $inventory, $seller);
        smokeLimits($db, $results, $market, $seller);
        smokeRiskReview($db, $results, $market, $seller);
        smokeReservedObjects($db, $results, $market, $seller);
        smokeSafeReturnReflection($db, $results, $market, $seller);
        smokeTradeLoop($results, $market, $inventory, $seller, $buyer, $iterations);

        $db->rollBack();
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        throw $e;
    }
}

function smokeSettings(PDO $db, array &$results): void
{
    foreach ([
        'commission.max_quantity_per_lot',
        'commission.max_total_price',
        'commission.freeze_timeout_seconds',
        'commission.risk_easy_item_unit',
    ] as $key) {
        $stmt = $db->prepare('SELECT value FROM site_settings WHERE name = :name LIMIT 1');
        $stmt->execute(['name' => $key]);
        assertTrue($results, 'setting.' . $key, $stmt->fetchColumn() !== false);
    }
    assertTrue($results, 'table.market_reserved_objects', tableExists($db, 'market_reserved_objects'));
}

function smokeFreezeAndDuplicateBuy(PDO $db, array &$results, CommissionMarketRepository $market, InventoryRepository $inventory, int $seller, int $buyer): void
{
    $item = firstSellableItem($market, $seller);
    $buyerBefore = $inventory->countItem($buyer, (int) $item['id']);
    $created = $market->createLot($seller, [
        'object_type' => 'item',
        'object_id' => (int) $item['id'],
        'quantity' => 1,
        'price_per_unit' => 123,
        'duration_hours' => 24,
    ]);
    $lotId = (int) ($created['lot_id'] ?? 0);
    assertTrue($results, 'freeze.buy.create', ($created['ok'] ?? false) === true, $created['message'] ?? '');

    $buy = $market->buy($buyer, $lotId);
    assertTrue($results, 'freeze.buy.ok', ($buy['ok'] ?? false) === true, $buy['message'] ?? '');
    assertTrue($results, 'freeze.buy.locked', lockedReason($db, $lotId) === 'buy', 'reason=' . lockedReason($db, $lotId));
    assertTrue($results, 'freeze.buy.log', logExists($db, $lotId, 'lot.freeze'), 'lot=' . $lotId);
    assertTrue($results, 'freeze.buy.received.once', $inventory->countItem($buyer, (int) $item['id']) === $buyerBefore + 1);

    $repeat = $market->buy($buyer, $lotId);
    assertTrue($results, 'anti_dup.repeat_buy.blocked', ($repeat['ok'] ?? true) === false, $repeat['message'] ?? '');
    assertTrue($results, 'anti_dup.repeat_buy.no_extra_item', $inventory->countItem($buyer, (int) $item['id']) === $buyerBefore + 1);
}

function smokeCancelFreeze(PDO $db, array &$results, CommissionMarketRepository $market, InventoryRepository $inventory, int $seller): void
{
    $item = firstSellableItem($market, $seller);
    $before = $inventory->countItem($seller, (int) $item['id']);
    $created = $market->createLot($seller, [
        'object_type' => 'item',
        'object_id' => (int) $item['id'],
        'quantity' => 1,
        'price_per_unit' => 222,
        'duration_hours' => 24,
    ]);
    $lotId = (int) ($created['lot_id'] ?? 0);
    $cancel = $market->cancel($seller, $lotId);
    assertTrue($results, 'freeze.cancel.ok', ($cancel['ok'] ?? false) === true, $cancel['message'] ?? '');
    assertTrue($results, 'freeze.cancel.locked', lockedReason($db, $lotId) === 'cancel', 'reason=' . lockedReason($db, $lotId));
    assertTrue($results, 'freeze.cancel.returned', $inventory->countItem($seller, (int) $item['id']) === $before);
}

function smokeLimits(PDO $db, array &$results, CommissionMarketRepository $market, int $seller): void
{
    $item = firstSellableItem($market, $seller);
    $db->prepare(
        'INSERT INTO site_settings (name, value, updated_by, updated_at)
         VALUES ("commission.max_quantity_per_lot", "1", 0, UNIX_TIMESTAMP())
         ON DUPLICATE KEY UPDATE value = VALUES(value), updated_at = VALUES(updated_at)'
    )->execute();
    $tooMany = $market->createLot($seller, [
        'object_type' => 'item',
        'object_id' => (int) $item['id'],
        'quantity' => 2,
        'price_per_unit' => 1,
        'duration_hours' => 24,
    ]);
    assertTrue($results, 'limit.quantity.blocked', ($tooMany['ok'] ?? true) === false, $tooMany['message'] ?? '');

    $db->prepare(
        'INSERT INTO site_settings (name, value, updated_by, updated_at)
         VALUES ("commission.max_total_price", "10", 0, UNIX_TIMESTAMP())
         ON DUPLICATE KEY UPDATE value = VALUES(value), updated_at = VALUES(updated_at)'
    )->execute();
    $tooExpensive = $market->createLot($seller, [
        'object_type' => 'item',
        'object_id' => (int) $item['id'],
        'quantity' => 1,
        'price_per_unit' => 11,
        'duration_hours' => 24,
    ]);
    assertTrue($results, 'limit.total.blocked', ($tooExpensive['ok'] ?? true) === false, $tooExpensive['message'] ?? '');

    $db->prepare(
        'INSERT INTO site_settings (name, value, updated_by, updated_at)
         VALUES
           ("commission.max_quantity_per_lot", "9999", 0, UNIX_TIMESTAMP()),
           ("commission.max_total_price", "999999999", 0, UNIX_TIMESTAMP())
         ON DUPLICATE KEY UPDATE value = VALUES(value), updated_at = VALUES(updated_at)'
    )->execute();
}

function smokeRiskReview(PDO $db, array &$results, CommissionMarketRepository $market, int $seller): void
{
    $item = firstSellableItem($market, $seller);
    $created = $market->createLot($seller, [
        'object_type' => 'item',
        'object_id' => (int) $item['id'],
        'quantity' => 1,
        'price_per_unit' => 50_000_000,
        'duration_hours' => 24,
    ]);
    $lotId = (int) ($created['lot_id'] ?? 0);
    assertTrue($results, 'risk.create', ($created['ok'] ?? false) === true, $created['message'] ?? '');
    assertTrue($results, 'risk.log.flagged', logExists($db, $lotId, 'risk.flagged'), 'lot=' . $lotId);
    assertTrue($results, 'risk.review.flagged', reviewStatus($db, $lotId) === 'flagged', 'status=' . reviewStatus($db, $lotId));
}

function smokeReservedObjects(PDO $db, array &$results, CommissionMarketRepository $market, int $seller): void
{
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
        assertTrue($results, 'reserve.pokemon.create', ($created['ok'] ?? false) === true, $created['message'] ?? '');
        assertTrue($results, 'reserve.pokemon.ledger', reservedExists($db, $lotId, 'pokemon', (int) $pokemon['id']));
        $market->cancel($seller, $lotId);
        assertTrue($results, 'reserve.pokemon.release', !reservedExists($db, $lotId, 'pokemon', (int) $pokemon['id']));
    } else {
        assertTrue($results, 'reserve.pokemon.skip', true, 'no sellable pokemon');
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
        assertTrue($results, 'reserve.egg.create', ($created['ok'] ?? false) === true, $created['message'] ?? '');
        assertTrue($results, 'reserve.egg.ledger', reservedExists($db, $lotId, 'egg', (int) $egg['id']));
        $market->cancel($seller, $lotId);
        assertTrue($results, 'reserve.egg.release', !reservedExists($db, $lotId, 'egg', (int) $egg['id']));
    } else {
        assertTrue($results, 'reserve.egg.skip', true, 'no sellable eggs');
    }
}

function smokeSafeReturnReflection(PDO $db, array &$results, CommissionMarketRepository $market, int $seller): void
{
    $before = (int) ($db->query('SELECT COUNT(*) FROM market_return_storage')->fetchColumn() ?: 0);
    $method = new ReflectionMethod($market, 'returnLotToSeller');
    $method->setAccessible(true);
    $method->invoke($market, [
        'id' => 987654321,
        'seller_id' => $seller,
        'object_type' => 'pokemon',
        'object_id' => 987654321,
        'quantity' => 1,
        'object_name' => 'Missing QA Pokemon',
    ], 'qa_missing_object');
    $after = (int) ($db->query('SELECT COUNT(*) FROM market_return_storage')->fetchColumn() ?: 0);
    assertTrue($results, 'safe_return.pending.created', $after === $before + 1, 'before=' . $before . ' after=' . $after);
    assertTrue($results, 'safe_return.log.created', logExists($db, 987654321, 'return.pending'));
}

function smokeTradeLoop(array &$results, CommissionMarketRepository $market, InventoryRepository $inventory, int $userA, int $userB, int $iterations): void
{
    $seller = $userA;
    $buyer = $userB;
    $item = firstSellableItem($market, $seller);
    $itemId = (int) $item['id'];
    $inventory->addItem($seller, $itemId, 1);
    $ok = true;
    $details = '';

    for ($i = 0; $i < $iterations; $i++) {
        $created = $market->createLot($seller, [
            'object_type' => 'item',
            'object_id' => $itemId,
            'quantity' => 1,
            'price_per_unit' => 10 + ($i % 7),
            'duration_hours' => 24,
        ]);
        if (($created['ok'] ?? false) !== true) {
            $ok = false;
            $details = 'create #' . $i . ': ' . ($created['message'] ?? '');
            break;
        }
        $buy = $market->buy($buyer, (int) $created['lot_id']);
        if (($buy['ok'] ?? false) !== true) {
            $ok = false;
            $details = 'buy #' . $i . ': ' . ($buy['message'] ?? '');
            break;
        }
        [$seller, $buyer] = [$buyer, $seller];
    }
    assertTrue($results, 'trade_loop.' . $iterations . '_iterations', $ok, $details);
}

function expireCronHardening(PDO $db, array &$results, CommissionMarketRepository $market, InventoryRepository $inventory, int $seller): void
{
    $item = firstSellableItem($market, $seller);
    $before = $inventory->countItem($seller, (int) $item['id']);
    $created = $market->createLot($seller, [
        'object_type' => 'item',
        'object_id' => (int) $item['id'],
        'quantity' => 1,
        'price_per_unit' => 333,
        'duration_hours' => 24,
    ]);
    $lotId = (int) ($created['lot_id'] ?? 0);
    assertTrue($results, 'expire.create', ($created['ok'] ?? false) === true, $created['message'] ?? '');
    if ($lotId <= 0) {
        return;
    }

    $db->prepare('UPDATE market_lots SET expires_at = :time WHERE id = :id LIMIT 1')->execute(['time' => time() - 5, 'id' => $lotId]);
    $job = $market->expireDueLotsJob(10, false);
    assertTrue($results, 'expire.job.expired', (int) ($job['expired'] ?? 0) >= 1, json_encode($job, JSON_UNESCAPED_UNICODE));
    assertTrue($results, 'expire.status', lotStatus($db, $lotId) === 'expired', 'status=' . lotStatus($db, $lotId));
    assertTrue($results, 'expire.freeze', lockedReason($db, $lotId) === 'expire', 'reason=' . lockedReason($db, $lotId));
    assertTrue($results, 'expire.returned', $inventory->countItem($seller, (int) $item['id']) === $before, 'count=' . $inventory->countItem($seller, (int) $item['id']));

    if (lotStatus($db, $lotId) === 'active') {
        $market->cancel($seller, $lotId);
    }
    $db->prepare('DELETE FROM market_logs WHERE lot_id = :lot')->execute(['lot' => $lotId]);
    $db->prepare('DELETE FROM market_deal_reviews WHERE lot_id = :lot')->execute(['lot' => $lotId]);
    $db->prepare('DELETE FROM market_reserved_objects WHERE lot_id = :lot')->execute(['lot' => $lotId]);
    $db->prepare('DELETE FROM market_lots WHERE id = :lot LIMIT 1')->execute(['lot' => $lotId]);
}

function firstSellableItem(CommissionMarketRepository $market, int $userId): array
{
    $sellable = $market->sellable($userId);
    foreach ($sellable['items'] ?? [] as $item) {
        if ((int) ($item['count'] ?? 0) > 0) {
            return $item;
        }
    }
    throw new RuntimeException('No sellable item found for user #' . $userId);
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

function tableExists(PDO $db, string $table): bool
{
    $stmt = $db->prepare('SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table');
    $stmt->execute(['table' => $table]);
    return (int) ($stmt->fetchColumn() ?: 0) > 0;
}

function logExists(PDO $db, int $lotId, string $action): bool
{
    $stmt = $db->prepare('SELECT 1 FROM market_logs WHERE lot_id = :lot AND action = :action LIMIT 1');
    $stmt->execute(['lot' => $lotId, 'action' => $action]);
    return (bool) $stmt->fetchColumn();
}

function reviewStatus(PDO $db, int $lotId): string
{
    $stmt = $db->prepare('SELECT status FROM market_deal_reviews WHERE lot_id = :lot LIMIT 1');
    $stmt->execute(['lot' => $lotId]);
    return (string) ($stmt->fetchColumn() ?: '');
}

function reservedExists(PDO $db, int $lotId, string $type, int $objectId): bool
{
    $stmt = $db->prepare(
        'SELECT 1
           FROM market_reserved_objects
          WHERE lot_id = :lot
            AND object_type = :type
            AND object_id = :object
          LIMIT 1'
    );
    $stmt->execute(['lot' => $lotId, 'type' => $type, 'object' => $objectId]);
    return (bool) $stmt->fetchColumn();
}

function lockedReason(PDO $db, int $lotId): string
{
    $stmt = $db->prepare('SELECT lock_reason FROM market_lots WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $lotId]);
    return (string) ($stmt->fetchColumn() ?: '');
}

function lotStatus(PDO $db, int $lotId): string
{
    $stmt = $db->prepare('SELECT status FROM market_lots WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $lotId]);
    return (string) ($stmt->fetchColumn() ?: '');
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

function parseArgs(array $argv): array
{
    $options = [];
    foreach (array_slice($argv, 1) as $arg) {
        if (!str_starts_with($arg, '--')) {
            continue;
        }
        $pair = explode('=', substr($arg, 2), 2);
        $options[$pair[0]] = $pair[1] ?? true;
    }
    return $options;
}
