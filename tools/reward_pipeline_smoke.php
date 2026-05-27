<?php
declare(strict_types=1);

use Pokemon8\Database\Connection;
use Pokemon8\Repository\InventoryRepository;
use Pokemon8\Repository\PokemonEvolutionRepository;
use Pokemon8\Repository\RewardRepository;
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
$safe = new SafeStorageRepository($db);
$rewards = new RewardRepository($db, $safe);
$inventory = new InventoryRepository($db, new PokemonEvolutionRepository($db));
$inventory->setSafeStorageRepository($safe);
$inventory->setRewardRepository($rewards);
$results = [];

$db->beginTransaction();
try {
    $userId = userId($db, 'Tacos');
    assertTrue($results, 'user.tacos', $userId > 0, 'id=' . $userId);

    $operationKey = 'smoke:reward_pipeline:' . time();
    $beforeCoins = itemCount($db, $userId, 1);
    $beforeSpoon = itemCount($db, $userId, 90);
    $beforeNotifications = countRowsWhere($db, 'game_notifications', 'user_id = ' . $userId);

    $pipeline = $rewards->grantPipeline($userId, ['items' => [1 => 7, 90 => 1]], 'smoke', 'direct', [
        'operation_key' => $operationKey,
        'title' => 'Smoke reward pipeline',
    ]);
    assertTrue($results, 'pipeline.direct.ok', ($pipeline['ok'] ?? false) === true, (string) ($pipeline['message'] ?? ''));
    assertTrue($results, 'pipeline.direct.items', itemCount($db, $userId, 1) === $beforeCoins + 7 && itemCount($db, $userId, 90) === $beforeSpoon + 1, 'coins/spoon granted');
    assertTrue($results, 'pipeline.direct.transaction', transactionStatus($db, $operationKey) === 'completed', 'status=' . transactionStatus($db, $operationKey));
    assertTrue($results, 'pipeline.direct.entries', rewardEntryCount($db, (int) ($pipeline['transaction_id'] ?? 0), 'item') >= 2, 'entries=' . rewardEntryCount($db, (int) ($pipeline['transaction_id'] ?? 0), 'item'));
    assertTrue($results, 'pipeline.direct.notification', countRowsWhere($db, 'game_notifications', 'user_id = ' . $userId) >= $beforeNotifications + 1, 'notifications=' . countRowsWhere($db, 'game_notifications', 'user_id = ' . $userId));

    $coinsAfterFirst = itemCount($db, $userId, 1);
    $idempotent = $rewards->grantPipeline($userId, ['items' => [1 => 7, 90 => 1]], 'smoke', 'direct', [
        'operation_key' => $operationKey,
        'title' => 'Smoke reward pipeline',
    ]);
    assertTrue($results, 'pipeline.idempotent', ($idempotent['idempotent'] ?? false) === true && itemCount($db, $userId, 1) === $coinsAfterFirst, 'coins=' . itemCount($db, $userId, 1));

    $giftRow = grantGiftRow($db, $userId, 90021);
    $beforeGift90 = itemCount($db, $userId, 90);
    $beforeGift91 = itemCount($db, $userId, 91);
    $beforeGift93 = itemCount($db, $userId, 93);
    $gift = $inventory->openGiftBox($userId, $giftRow);
    assertTrue($results, 'gift.pipeline.ok', ($gift['ok'] ?? false) === true && count($gift['rewards'] ?? []) >= 3, (string) ($gift['message'] ?? ''));
    assertTrue($results, 'gift.pipeline.items', itemCount($db, $userId, 90) >= $beforeGift90 + 1 && itemCount($db, $userId, 91) >= $beforeGift91 + 1 && itemCount($db, $userId, 93) >= $beforeGift93 + 1, 'gift guaranteed items granted');
    assertTrue($results, 'gift.pipeline.spent', inventoryRowExists($db, $giftRow) === false, 'row=' . $giftRow);
    $giftOperation = 'gift_open:' . $userId . ':' . $giftRow . ':90021';
    assertTrue($results, 'gift.pipeline.transaction', transactionStatus($db, $giftOperation) === 'completed', 'status=' . transactionStatus($db, $giftOperation));

    $failureKey = 'smoke:reward_pipeline_failure:' . time();
    $rewards->recordPipelineFailure($userId, $failureKey, 'smoke', 'failure', 'Smoke failed reward', [[
        'type' => 'item',
        'object_id' => 90,
        'quantity' => 1,
        'title' => 'Twisted Spoon',
        'message' => 'Twisted Spoon x1',
        'data' => ['item_id' => 90, 'count' => 1],
    ]], 'forced smoke failure');
    assertTrue($results, 'pipeline.failure.logged', transactionStatus($db, $failureKey) === 'failed', 'status=' . transactionStatus($db, $failureKey));
    assertTrue($results, 'pipeline.failure.rollback', countRowsWhere($db, 'safe_operation_rollbacks', 'operation_key = ' . $db->quote('reward_pipeline_failed:' . $failureKey)) >= 1, 'rollback recorded');

    $db->rollBack();
} catch (Throwable $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    $results[] = ['name' => 'exception', 'ok' => false, 'details' => $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine()];
}

printResults($results);
exit(count(array_filter($results, static fn (array $row): bool => !$row['ok'])) === 0 ? 0 : 1);

function userId(PDO $db, string $login): int
{
    $stmt = $db->prepare('SELECT id FROM users WHERE login = :login LIMIT 1');
    $stmt->execute(['login' => $login]);
    return (int) ($stmt->fetchColumn() ?: 0);
}

function itemCount(PDO $db, int $userId, int $itemId): int
{
    $stmt = $db->prepare('SELECT COALESCE(SUM(count), 0) FROM items_users WHERE user_id = :user AND item_id = :item');
    $stmt->execute(['user' => $userId, 'item' => $itemId]);
    return (int) ($stmt->fetchColumn() ?: 0);
}

function grantGiftRow(PDO $db, int $userId, int $itemId): int
{
    $id = (int) ($db->query('SELECT COALESCE(MAX(id), 0) + 1 FROM items_users')->fetchColumn() ?: 1);
    $stmt = $db->prepare(
        'INSERT INTO items_users (id, item_id, user_id, count, dattimer, timers)
         VALUES (:id, :item, :user, 1, "not", "not")'
    );
    $stmt->execute(['id' => $id, 'item' => $itemId, 'user' => $userId]);
    return $id;
}

function transactionStatus(PDO $db, string $operationKey): string
{
    $stmt = $db->prepare('SELECT status FROM reward_transactions WHERE operation_key = :key LIMIT 1');
    $stmt->execute(['key' => $operationKey]);
    return (string) ($stmt->fetchColumn() ?: '');
}

function rewardEntryCount(PDO $db, int $transactionId, string $type): int
{
    $stmt = $db->prepare('SELECT COUNT(*) FROM reward_transaction_entries WHERE transaction_id = :id AND reward_type = :type');
    $stmt->execute(['id' => $transactionId, 'type' => $type]);
    return (int) ($stmt->fetchColumn() ?: 0);
}

function inventoryRowExists(PDO $db, int $rowId): bool
{
    $stmt = $db->prepare('SELECT 1 FROM items_users WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $rowId]);
    return (bool) $stmt->fetchColumn();
}

function countRowsWhere(PDO $db, string $table, string $where): int
{
    return (int) ($db->query('SELECT COUNT(*) FROM ' . $table . ' WHERE ' . $where)->fetchColumn() ?: 0);
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
    echo sprintf("Reward pipeline smoke: %d/%d passed.\n", $passed, count($results));
}
