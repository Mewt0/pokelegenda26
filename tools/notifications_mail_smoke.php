<?php
declare(strict_types=1);

use Pokemon8\Database\Connection;
use Pokemon8\Repository\GameEventRepository;
use Pokemon8\Repository\MessageRepository;
use Pokemon8\Repository\RewardRepository;
use Pokemon8\Repository\SafeStorageRepository;
use Pokemon8\Support\Env;
use Pokemon8\Support\Mailer;

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Run from CLI.\n");
    exit(2);
}

define('APP_ROOT', dirname(__DIR__));
require APP_ROOT . '/src/Support/Autoload.php';

Env::load(APP_ROOT . '/.env');
$db = Connection::make(require APP_ROOT . '/config/database.php');
$mailConfig = require APP_ROOT . '/config/mail.php';
$mailConfig['enabled'] = true;
$mailConfig['transport'] = 'log';
$mailConfig['from_address'] = 'no-reply@pokemonchic.local';

$mailer = new Mailer($mailConfig, $db);
$messages = new MessageRepository($db);
$rewards = new RewardRepository($db, new SafeStorageRepository($db));
$rewards->setMailer($mailer);
$rewards->setMessageRepository($messages);
$events = new GameEventRepository($db, $rewards);
$results = [];

$db->beginTransaction();
try {
    $userId = userId($db, 'Tacos');
    $systemId = userId($db, 'Система');
    assertTrue($results, 'user.tacos', $userId > 0, 'id=' . $userId);
    assertTrue($results, 'user.system', $systemId > 0, 'id=' . $systemId);

    $mailBefore = countRowsWhere($db, 'mail_delivery_logs', '1=1');
    $mailSent = $mailer->send('qa@example.test', 'Smoke SMTP/log transport', 'SMTP/log smoke body.');
    $mailAfter = latestMailLog($db);
    assertTrue($results, 'mail.log_transport.send', $mailSent === true, 'sent=' . ($mailSent ? 'true' : 'false'));
    assertTrue(
        $results,
        'mail.delivery_log',
        countRowsWhere($db, 'mail_delivery_logs', '1=1') === $mailBefore + 1
            && ($mailAfter['status'] ?? '') === 'success'
            && ($mailAfter['transport'] ?? '') === 'log',
        'status=' . (string) ($mailAfter['status'] ?? '') . ', transport=' . (string) ($mailAfter['transport'] ?? '')
    );

    $noticeBefore = countRowsWhere($db, 'game_notifications', 'user_id = ' . $userId);
    $mailboxBefore = countRowsWhere($db, 'sends', 'users = ' . $userId . ' AND inputusers = ' . $systemId);
    $rewards->notify($userId, 'Smoke system notice', 'Системное уведомление smoke.', 'info', [
        'source_type' => 'smoke',
        'source_id' => 'notification',
        'mailbox' => true,
    ]);
    $notice = latestNotification($db, $userId);
    assertTrue(
        $results,
        'notification.system_sender',
        countRowsWhere($db, 'game_notifications', 'user_id = ' . $userId) === $noticeBefore + 1
            && (int) ($notice['sender_id'] ?? 0) === $systemId
            && ($notice['source'] ?? '') === 'Система'
            && ($notice['source_type'] ?? '') === 'smoke',
        'sender=' . (int) ($notice['sender_id'] ?? 0) . ', source=' . (string) ($notice['source_type'] ?? '')
    );
    assertTrue(
        $results,
        'mailbox.system_sender',
        countRowsWhere($db, 'sends', 'users = ' . $userId . ' AND inputusers = ' . $systemId) === $mailboxBefore + 1,
        'mailbox rows=' . countRowsWhere($db, 'sends', 'users = ' . $userId . ' AND inputusers = ' . $systemId)
    );

    $rewardKey = 'smoke:notifications_mail:' . time();
    $pipeline = $rewards->grantPipeline($userId, ['items' => [1 => 1]], 'smoke_reward', 'pipeline', [
        'operation_key' => $rewardKey,
        'title' => 'Smoke reward notification',
    ]);
    $rewardNotice = latestNotification($db, $userId);
    assertTrue($results, 'reward.pipeline.ok', ($pipeline['ok'] ?? false) === true, (string) ($pipeline['message'] ?? ''));
    assertTrue(
        $results,
        'reward.notification.metadata',
        ($rewardNotice['source_type'] ?? '') === 'smoke_reward'
            && (string) ($rewardNotice['source_id'] ?? '') === 'pipeline'
            && (int) ($rewardNotice['sender_id'] ?? 0) === $systemId,
        'source=' . (string) ($rewardNotice['source_type'] ?? '') . ', sender=' . (int) ($rewardNotice['sender_id'] ?? 0)
    );

    $eventId = createSmokeEvent($db);
    $eventNoticeBefore = countRowsWhere($db, 'game_notifications', 'user_id = ' . $userId . ' AND source_type = "event" AND source_id = ' . $db->quote((string) $eventId));
    $payload = $events->activePayload($userId);
    $found = false;
    foreach (($payload['activeEvents'] ?? []) as $event) {
        if ((int) ($event['id'] ?? 0) === $eventId) {
            $found = true;
            break;
        }
    }
    assertTrue($results, 'event.active.visible', $found, 'event_id=' . $eventId);
    assertTrue(
        $results,
        'event.notification.sent_once',
        countRowsWhere($db, 'game_event_notification_receipts', 'user_id = ' . $userId . ' AND event_id = ' . $eventId) === 1
            && countRowsWhere($db, 'game_notifications', 'user_id = ' . $userId . ' AND source_type = "event" AND source_id = ' . $db->quote((string) $eventId)) === $eventNoticeBefore + 1,
        'receipts=' . countRowsWhere($db, 'game_event_notification_receipts', 'user_id = ' . $userId . ' AND event_id = ' . $eventId)
    );
    $events->activePayload($userId);
    assertTrue(
        $results,
        'event.notification.no_duplicate',
        countRowsWhere($db, 'game_notifications', 'user_id = ' . $userId . ' AND source_type = "event" AND source_id = ' . $db->quote((string) $eventId)) === $eventNoticeBefore + 1,
        'notifications stable'
    );

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

function createSmokeEvent(PDO $db): int
{
    $stmt = $db->prepare(
        'INSERT INTO game_event_boosts (title, boost_key, multiplier, scope, starts_at, ends_at, enabled, note, created_by, created_at, updated_at)
         VALUES ("Smoke event notification", "exp", 2.00, "global", :start, :end, 1, "notifications smoke", 0, :created_at, :updated_at)'
    );
    $now = time();
    $stmt->execute(['start' => $now - 60, 'end' => $now + 3600, 'created_at' => $now, 'updated_at' => $now]);
    return (int) $db->lastInsertId();
}

function latestMailLog(PDO $db): array
{
    $row = $db->query('SELECT * FROM mail_delivery_logs ORDER BY id DESC LIMIT 1')->fetch(PDO::FETCH_ASSOC);
    return is_array($row) ? $row : [];
}

function latestNotification(PDO $db, int $userId): array
{
    $stmt = $db->prepare('SELECT * FROM game_notifications WHERE user_id = :user ORDER BY id DESC LIMIT 1');
    $stmt->execute(['user' => $userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return is_array($row) ? $row : [];
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
    echo sprintf("Notifications/mail smoke: %d/%d passed.\n", $passed, count($results));
}
