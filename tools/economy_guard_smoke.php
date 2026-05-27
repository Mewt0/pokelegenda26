<?php
declare(strict_types=1);

use Pokemon8\Database\Connection;
use Pokemon8\Repository\EconomyGuardRepository;
use Pokemon8\Support\Env;

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Run from CLI.\n");
    exit(2);
}

define('APP_ROOT', dirname(__DIR__));
require APP_ROOT . '/src/Support/Autoload.php';

Env::load(APP_ROOT . '/.env');
$db = Connection::make(require APP_ROOT . '/config/database.php');
$guard = new EconomyGuardRepository($db);
$results = [];

$tacos = userId($db, 'Tacos');
$niga = userId($db, 'NIGA');
assertTrue($results, 'users.ready', $tacos > 0 && $niga > 0, 'Tacos=' . $tacos . ', NIGA=' . $niga);

if ($tacos <= 0 || $niga <= 0) {
    printResults($results);
    exit(1);
}

$db->beginTransaction();
try {
    assertTrue($results, 'table.alerts', tableExists($db, 'economy_guard_alerts'));
    seedGuardFixtures($db, $tacos, $niga);
    $beforeAlerts = alertCount($db);

    $dry = $guard->scan(true, 200);
    assertTrue($results, 'scan.dry.ok', ($dry['enabled'] ?? false) === true && ($dry['dryRun'] ?? false) === true, json_encode($dry, JSON_UNESCAPED_UNICODE));
    assertTrue($results, 'scan.dry.no_mutation', alertCount($db) === $beforeAlerts, 'alerts=' . alertCount($db) . ', before=' . $beforeAlerts);

    $scan = $guard->scan(false, 200);
    assertTrue($results, 'scan.run.ok', ($scan['enabled'] ?? false) === true && alertCount($db) >= $beforeAlerts + 4, json_encode($scan, JSON_UNESCAPED_UNICODE));
    assertTrue($results, 'alert.suspicious_trade', alertTypeCount($db, 'suspicious_trade') >= 1);
    assertTrue($results, 'alert.massive_money_gain', alertTypeCount($db, 'massive_money_gain') >= 1);
    assertTrue($results, 'alert.transfer_abuse', alertTypeCount($db, 'transfer_abuse') >= 1);
    assertTrue($results, 'alert.fake_market_price', alertTypeCount($db, 'fake_market_price') >= 1);

    $alerts = $guard->alerts(['status' => 'open'], 20, 0);
    assertTrue($results, 'alerts.api.shape', ($alerts['total'] ?? 0) >= 4 && count($alerts['rows'] ?? []) > 0, 'total=' . ($alerts['total'] ?? 0));

    $firstId = (int) (($alerts['rows'][0]['id'] ?? 0));
    $review = $guard->review($tacos, $firstId, 'reviewed', 'QA smoke reviewed');
    assertTrue($results, 'alert.review', ($review['ok'] ?? false) === true, $review['message'] ?? '');

    $db->rollBack();
} catch (Throwable $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    $results[] = ['name' => 'exception', 'ok' => false, 'details' => $e->getMessage()];
}

printResults($results);
exit(count(array_filter($results, static fn (array $row): bool => !$row['ok'])) === 0 ? 0 : 1);

function seedGuardFixtures(PDO $db, int $seller, int $buyer): void
{
    $now = time();
    setSetting($db, 'economy_guard.massive_money_gain_threshold', '1000000');
    setSetting($db, 'economy_guard.transfer_pair_threshold', '1000000');
    setSetting($db, 'economy_guard.transfer_pair_count_threshold', '3');
    setSetting($db, 'economy_guard.fake_price_multiplier', '10');
    setSetting($db, 'economy_guard.fake_price_min_sales', '3');
    setSetting($db, 'economy_guard.fake_price_min_unit', '100000');
    setSetting($db, 'economy_guard.coin_balance_threshold', '1000000');

    $flaggedLot = insertLot($db, $seller, $buyer, 'item', 3, 'QA Pokeball', 1, 50_000_000, 'sold', $now - 300);
    $db->prepare(
        'INSERT INTO market_deal_reviews (lot_id, status, risk_score, risk_flags_json, reviewed_by, reviewed_at, note, created_at, updated_at)
         VALUES (:lot, "flagged", 95, :flags, 0, 0, "", :created_at, :updated_at)'
    )->execute([
        'lot' => $flaggedLot,
        'flags' => json_encode(['QA suspicious trade'], JSON_UNESCAPED_UNICODE),
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    for ($i = 0; $i < 4; $i++) {
        insertLot($db, $seller, $buyer, 'item', 80 + $i, 'QA Transfer #' . $i, 1, 400_000 + ($i * 10_000), 'sold', $now - 240 + $i);
    }

    $itemId = 880001;
    for ($i = 0; $i < 3; $i++) {
        insertLot($db, $buyer, $seller, 'item', $itemId, 'QA Baseline Item', 1, 10_000, 'sold', $now - 1200 + $i);
    }
    insertLot($db, $seller, 0, 'item', $itemId, 'QA Baseline Item', 1, 200_000, 'active', $now - 60);

    $transactionId = nextAutoId($db, 'reward_transactions');
    $db->prepare(
        'INSERT INTO reward_transactions
            (id, operation_key, user_id, source_type, source_id, title, status, payload_json, result_json, error_message, created_at, updated_at, completed_at)
         VALUES
            (:id, :key, :user, "qa_smoke", "economy_guard", "QA massive coins", "completed", "{}", "{}", "", :created_at, :updated_at, :completed_at)'
    )->execute([
        'id' => $transactionId,
        'key' => 'qa_economy_guard_' . $transactionId,
        'user' => $buyer,
        'created_at' => $now,
        'updated_at' => $now,
        'completed_at' => $now,
    ]);
    $db->prepare(
        'INSERT INTO reward_transaction_entries
            (transaction_id, reward_type, object_id, quantity, title, status, data_json, created_at)
         VALUES
            (:transaction, "item", 1, 2000000, "Coins", "completed", "{}", :time)'
    )->execute(['transaction' => $transactionId, 'time' => $now]);
}

function insertLot(PDO $db, int $seller, int $buyer, string $type, int $objectId, string $name, int $quantity, int $unit, string $status, int $time): int
{
    $sellerName = login($db, $seller);
    $lotId = nextAutoId($db, 'market_lots');
    $soldAt = $status === 'sold' ? $time : 0;
    $db->prepare(
        'INSERT INTO market_lots
            (id, seller_id, seller_name, object_type, object_id, object_name, object_icon, category, object_snapshot_json,
             reserve_payload_json, quantity, price_per_unit, total_price, status, created_at, expires_at, sold_at, buyer_id,
             private_buyer_id, commission_amount, locked_by, locked_at, lock_reason, lock_token)
         VALUES
            (:id, :seller, :seller_name, :type, :object, :name, "", "other", "{}", "{}", :qty, :unit, :total,
             :status, :created, :expires, :sold, :buyer, 0, 0, 0, 0, "", "")'
    )->execute([
        'id' => $lotId,
        'seller' => $seller,
        'seller_name' => $sellerName,
        'type' => $type,
        'object' => $objectId,
        'name' => $name,
        'qty' => $quantity,
        'unit' => $unit,
        'total' => $quantity * $unit,
        'status' => $status,
        'created' => $time,
        'expires' => $time + 86400,
        'sold' => $soldAt,
        'buyer' => $buyer,
    ]);
    return $lotId;
}

function setSetting(PDO $db, string $name, string $value): void
{
    $db->prepare(
        'INSERT INTO site_settings (name, value, updated_by, updated_at)
         VALUES (:name, :value, 0, UNIX_TIMESTAMP())
         ON DUPLICATE KEY UPDATE value = VALUES(value), updated_at = VALUES(updated_at)'
    )->execute(['name' => $name, 'value' => $value]);
}

function alertCount(PDO $db): int
{
    return (int) ($db->query('SELECT COUNT(*) FROM economy_guard_alerts')->fetchColumn() ?: 0);
}

function alertTypeCount(PDO $db, string $type): int
{
    $stmt = $db->prepare('SELECT COUNT(*) FROM economy_guard_alerts WHERE alert_type = :type');
    $stmt->execute(['type' => $type]);
    return (int) ($stmt->fetchColumn() ?: 0);
}

function nextAutoId(PDO $db, string $table): int
{
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
        return 1;
    }
    return (int) ($db->query('SELECT COALESCE(MAX(id), 0) + 1 FROM `' . $table . '`')->fetchColumn() ?: 1);
}

function userId(PDO $db, string $login): int
{
    $stmt = $db->prepare('SELECT id FROM users WHERE login = :login LIMIT 1');
    $stmt->execute(['login' => $login]);
    return (int) ($stmt->fetchColumn() ?: 0);
}

function login(PDO $db, int $userId): string
{
    $stmt = $db->prepare('SELECT login FROM users WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $userId]);
    return (string) ($stmt->fetchColumn() ?: ('#' . $userId));
}

function tableExists(PDO $db, string $table): bool
{
    $stmt = $db->prepare('SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table');
    $stmt->execute(['table' => $table]);
    return (int) ($stmt->fetchColumn() ?: 0) > 0;
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
    echo sprintf("Economy Guard smoke: %d/%d passed.\n", $passed, count($results));
}
