<?php
declare(strict_types=1);

namespace Pokemon8\Repository;

use PDO;
use Pokemon8\Support\Mailer;

final class RewardRepository
{
    private ?Mailer $mailer = null;
    private ?MessageRepository $messages = null;

    public function __construct(private PDO $db, private ?SafeStorageRepository $safeStorage = null)
    {
    }

    public function setSafeStorageRepository(SafeStorageRepository $safeStorage): void
    {
        $this->safeStorage = $safeStorage;
    }

    public function setMailer(Mailer $mailer): void
    {
        $this->mailer = $mailer;
    }

    public function setMessageRepository(MessageRepository $messages): void
    {
        $this->messages = $messages;
    }

    public function grantPipeline(
        int $userId,
        array $rewards,
        string $sourceType = 'reward',
        string|int $sourceId = '',
        array $context = []
    ): array {
        $title = mb_substr(trim((string) ($context['title'] ?? 'Награда')), 0, 160);
        if ($title === '') {
            $title = 'Награда';
        }

        $entries = $this->normalizeRewardEntries($rewards);
        if ($userId <= 0 || $entries === []) {
            return ['ok' => false, 'message' => 'Нет наград для начисления.', 'entries' => []];
        }

        $operationKey = mb_substr(
            trim((string) ($context['operation_key'] ?? '')) !== ''
                ? trim((string) $context['operation_key'])
                : $this->newRewardOperationKey($userId, $sourceType, $sourceId),
            0,
            128
        );
        $throwOnFail = (bool) ($context['throw_on_fail'] ?? false);
        $sendNotification = (bool) ($context['notify'] ?? true);
        $payload = [
            'rewards' => $rewards,
            'entries' => $entries,
            'context' => $context,
        ];

        $existing = $this->rewardTransactionByKey($operationKey);
        if ($existing !== null && (string) ($existing['status'] ?? '') === 'completed') {
            $result = $this->decodeJson((string) ($existing['result_json'] ?? '{}'));
            $result['ok'] = true;
            $result['idempotent'] = true;
            $result['transaction_id'] = (int) ($existing['id'] ?? 0);
            return $result;
        }
        if ($existing !== null && !(bool) ($context['allow_retry'] ?? false)) {
            $message = 'Эта операция награды уже была зарегистрирована.';
            if ($throwOnFail) {
                throw new \RuntimeException($message);
            }
            return ['ok' => false, 'message' => $message, 'transaction_id' => (int) ($existing['id'] ?? 0)];
        }

        $transactionId = $existing !== null
            ? (int) ($existing['id'] ?? 0)
            : $this->createRewardTransaction($operationKey, $userId, $sourceType, (string) $sourceId, $title, $payload);

        $startedTransaction = !$this->db->inTransaction();
        try {
            if ($startedTransaction) {
                $this->db->beginTransaction();
            }

            $messages = [];
            foreach ($entries as $entry) {
                if ($entry['type'] === 'item') {
                    $this->addItem($userId, (int) $entry['object_id'], (int) $entry['quantity']);
                }

                $messages[] = $entry['message'];
                $this->insertRewardEntry($transactionId, $entry, 'completed');
            }

            if ($sendNotification && $messages !== []) {
                $this->notify(
                    $userId,
                    $title,
                    'Вам начислено: ' . implode(', ', $messages) . '.',
                    (string) ($context['variant'] ?? 'reward'),
                    [
                        'reward_transaction_id' => $transactionId,
                        'operation_key' => $operationKey,
                        'source_type' => $sourceType,
                        'source_id' => (string) $sourceId,
                        'entries' => $entries,
                        'email' => (bool) ($context['email'] ?? $this->settingBool('notifications.reward_email_enabled', false)),
                        'mailbox' => (bool) ($context['mailbox'] ?? $this->settingBool('notifications.reward_mailbox_enabled', false)),
                    ]
                );
                $this->insertRewardEntry($transactionId, [
                    'type' => 'notification',
                    'object_id' => 0,
                    'quantity' => 1,
                    'title' => $title,
                    'message' => 'Пуш-уведомление отправлено.',
                    'data' => ['messages' => $messages],
                ], 'completed');
            }

            $result = [
                'ok' => true,
                'message' => 'Награды начислены.',
                'transaction_id' => $transactionId,
                'operation_key' => $operationKey,
                'entries' => $entries,
                'messages' => $messages,
            ];
            $this->markRewardTransaction($transactionId, 'completed', $result);

            if ($startedTransaction) {
                $this->db->commit();
            }

            return $result;
        } catch (\Throwable $e) {
            if ($startedTransaction && $this->db->inTransaction()) {
                $this->db->rollBack();
            }

            $this->recordPipelineFailure(
                $userId,
                $operationKey,
                $sourceType,
                (string) $sourceId,
                $title,
                $entries,
                $e->getMessage(),
                $transactionId
            );

            if ($throwOnFail) {
                throw $e;
            }

            return [
                'ok' => false,
                'message' => 'Не удалось начислить награды.',
                'transaction_id' => $transactionId,
                'operation_key' => $operationKey,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function recordPipelineFailure(
        int $userId,
        string $operationKey,
        string $sourceType,
        string|int $sourceId,
        string $title,
        array $entries,
        string $error,
        int $transactionId = 0
    ): void {
        if ($transactionId <= 0) {
            $transactionId = $this->createRewardTransaction($operationKey, $userId, $sourceType, (string) $sourceId, $title, [
                'entries' => $entries,
                'failure_record' => true,
            ]);
        }

        foreach ($entries as $entry) {
            $this->insertRewardEntry($transactionId, $entry, 'failed');
            if (($entry['type'] ?? '') === 'item') {
                $this->safeStorage?->storeItem(
                    $userId,
                    (int) ($entry['object_id'] ?? 0),
                    max(1, (int) ($entry['quantity'] ?? 1)),
                    'reward.pipeline',
                    $operationKey,
                    ['source_type' => $sourceType, 'source_id' => (string) $sourceId, 'entry' => $entry],
                    'reward_pipeline_failed',
                    $error
                );
            }
        }

        $this->safeStorage?->recordRollback(
            'reward_pipeline_failed:' . $operationKey,
            'reward_pipeline',
            $userId,
            $sourceType,
            $sourceId,
            ['entries' => $entries],
            ['error' => $error],
            ['safe_storage' => true, 'operation_key' => $operationKey],
            'failed',
            $error
        );
        $this->markRewardTransaction($transactionId, 'failed', ['ok' => false, 'entries' => $entries], $error);
    }

    /**
     * @param array<int,int> $items item_id => count
     * @return list<string>
     */
    public function grantItems(int $userId, array $items, string $source = 'Награда'): array
    {
        $multiplier = str_starts_with($source, 'Квест:')
            ? $this->activeMultiplier($userId, 'quest_rewards', 'quest')
            : 1.0;
        $normalizedItems = [];
        foreach ($items as $itemId => $count) {
            $itemId = (int) $itemId;
            $count = (int) round((int) $count * $multiplier);
            if ($userId <= 0 || $itemId <= 0 || $count <= 0) {
                continue;
            }

            $normalizedItems[$itemId] = ($normalizedItems[$itemId] ?? 0) + $count;
        }

        if ($normalizedItems === []) {
            return [];
        }

        $pipeline = $this->grantPipeline($userId, ['items' => $normalizedItems], 'reward_items', $source, [
            'title' => $source,
            'original_items' => $items,
            'multiplier' => $multiplier,
        ]);

        return array_values(array_map('strval', $pipeline['messages'] ?? []));
    }

    public function grantReward(
        int $userId,
        string $type,
        array $payload,
        string $source = 'Награда',
        int $sourceId = 0,
        int $awardedBy = 0
    ): array {
        $type = strtolower(trim(str_replace('-', '_', $type)));
        if ($type === 'gym_badge' || $type === 'badge') {
            $badge = $payload['badge_id']
                ?? $payload['badgeId']
                ?? $payload['badge_key']
                ?? $payload['badgeKey']
                ?? $payload['key']
                ?? $payload['id']
                ?? '';
            $sourceType = (string) ($payload['source_type'] ?? $payload['sourceType'] ?? $source);
            $resolvedSourceId = (int) ($payload['source_id'] ?? $payload['sourceId'] ?? $sourceId);

            return $this->grantGymBadge($userId, $badge, $sourceType, $resolvedSourceId, $awardedBy);
        }

        throw new \InvalidArgumentException('Unsupported reward type: ' . $type);
    }

    public function grantGymBadge(
        int $userId,
        string|int $badge,
        string $sourceType = 'manual',
        int $sourceId = 0,
        int $awardedBy = 0
    ): array {
        if ($userId <= 0 || !$this->tableExists('gym_badges') || !$this->tableExists('user_gym_badges')) {
            return ['ok' => false, 'granted' => false, 'message' => 'Таблицы значков гим-лидеров не готовы.'];
        }

        $badgeRow = $this->findGymBadge($badge);
        if ($badgeRow === null) {
            return ['ok' => false, 'granted' => false, 'message' => 'Значок гим-лидера не найден.'];
        }

        $badgeId = (int) $badgeRow['id'];
        $existing = $this->db->prepare(
            'SELECT id, awarded_at FROM user_gym_badges WHERE user_id = :user AND badge_id = :badge LIMIT 1'
        );
        $existing->execute(['user' => $userId, 'badge' => $badgeId]);
        $existingRow = $existing->fetch(PDO::FETCH_ASSOC);
        if ($existingRow) {
            return [
                'ok' => true,
                'granted' => false,
                'message' => 'У игрока уже есть этот значок.',
                'badge' => $this->formatGymBadgeReward($badgeRow, (int) ($existingRow['awarded_at'] ?? 0), $sourceType, $sourceId),
            ];
        }

        $now = time();
        $sourceType = mb_substr(trim($sourceType) !== '' ? trim($sourceType) : 'manual', 0, 32);
        $sourceBattleId = in_array($sourceType, ['battle', 'gym_battle', 'pve', 'pvp'], true) ? max(0, $sourceId) : 0;
        $sourceQuestId = $sourceType === 'quest' ? max(0, $sourceId) : 0;
        $columns = [
            'user_id' => $userId,
            'badge_id' => $badgeId,
            'source_type' => $sourceType,
            'source_id' => max(0, $sourceId),
            'awarded_by' => max(0, $awardedBy),
            'awarded_at' => $now,
            'created_at' => $now,
        ];
        if ($this->columnExists('user_gym_badges', 'reward_type')) {
            $columns['reward_type'] = 'gym_badge';
        }
        if ($this->columnExists('user_gym_badges', 'issued_at')) {
            $columns['issued_at'] = $now;
        }
        if ($this->columnExists('user_gym_badges', 'source_battle_id')) {
            $columns['source_battle_id'] = $sourceBattleId;
        }
        if ($this->columnExists('user_gym_badges', 'source_quest_id')) {
            $columns['source_quest_id'] = $sourceQuestId;
        }

        $names = array_keys($columns);
        $placeholders = array_map(static fn (string $name): string => ':' . $name, $names);
        $stmt = $this->db->prepare(
            'INSERT INTO user_gym_badges (' . implode(', ', $names) . ')
             VALUES (' . implode(', ', $placeholders) . ')'
        );

        try {
            $stmt->execute($columns);
        } catch (\PDOException $e) {
            if ($e->getCode() !== '23000') {
                throw $e;
            }

            return [
                'ok' => true,
                'granted' => false,
                'message' => 'У игрока уже есть этот значок.',
                'badge' => $this->formatGymBadgeReward($badgeRow, $now, $sourceType, $sourceId),
            ];
        }

        $badgeData = $this->formatGymBadgeReward($badgeRow, $now, $sourceType, $sourceId);
        $this->notify(
            $userId,
            'Получен значок гим-лидера',
            'Вам начислен значок "' . $badgeData['title'] . '"' . ($badgeData['leader'] !== '' ? ' от ' . $badgeData['leader'] : '') . '.',
            'success',
            ['reward_type' => 'gym_badge', 'badge' => $badgeData]
        );

        return [
            'ok' => true,
            'granted' => true,
            'message' => 'Значок гим-лидера выдан.',
            'badge' => $badgeData,
        ];
    }

    public function notify(int $userId, string $title, string $message, string $variant = 'info', array $payload = []): void
    {
        if ($userId <= 0 || trim($message) === '' || !$this->tableExists('game_notifications')) {
            return;
        }

        $senderId = (int) ($payload['sender_id'] ?? 0);
        if ($senderId <= 0 && $this->settingBool('notifications.system_sender_enabled', true)) {
            $senderId = $this->systemAccountId();
        }
        $source = mb_substr((string) ($payload['source'] ?? 'Система'), 0, 64);
        $sourceType = mb_substr((string) ($payload['source_type'] ?? 'system'), 0, 64);
        $sourceId = mb_substr((string) ($payload['source_id'] ?? ''), 0, 96);
        $emailRequested = (bool) ($payload['email'] ?? false);
        $mailboxRequested = (bool) ($payload['mailbox'] ?? false);
        $emailStatus = $emailRequested ? 'pending' : 'not_requested';
        $emailSentAt = 0;
        $mailboxId = 0;

        if ($mailboxRequested && $this->messages !== null) {
            $mail = $this->messages->sendSystem(
                $userId,
                (string) ($payload['mail_subject'] ?? $title),
                (string) ($payload['mail_text'] ?? $message),
                ['source_type' => $sourceType, 'source_id' => $sourceId]
            );
            $mailboxId = (int) ($mail['id'] ?? 0);
        }

        if ($emailRequested && $this->mailer !== null) {
            $email = $this->userEmail($userId);
            if ($email !== '') {
                $sent = $this->mailer->send($email, (string) ($payload['email_subject'] ?? $title), (string) ($payload['email_text'] ?? $message));
                $emailStatus = $sent ? 'sent' : 'failed';
                $emailSentAt = $sent ? time() : 0;
            } else {
                $emailStatus = 'missing_email';
            }
        }

        if ($mailboxId > 0) {
            $payload['mailbox_message_id'] = $mailboxId;
        }

        $columns = [
            'user_id' => $userId,
            'title' => mb_substr($title, 0, 120),
            'message' => mb_substr($message, 0, 500),
            'variant' => mb_substr($variant, 0, 32),
            'payload_json' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'source' => $source,
            'created_at' => time(),
            'read_at' => 0,
        ];
        if ($this->columnExists('game_notifications', 'sender_id')) {
            $columns['sender_id'] = $senderId;
        }
        if ($this->columnExists('game_notifications', 'source_type')) {
            $columns['source_type'] = $sourceType;
        }
        if ($this->columnExists('game_notifications', 'source_id')) {
            $columns['source_id'] = $sourceId;
        }
        if ($this->columnExists('game_notifications', 'email_status')) {
            $columns['email_status'] = $emailStatus;
        }
        if ($this->columnExists('game_notifications', 'email_sent_at')) {
            $columns['email_sent_at'] = $emailSentAt;
        }

        $names = array_keys($columns);
        $placeholders = array_map(static fn (string $name): string => ':' . $name, $names);
        $stmt = $this->db->prepare(
            'INSERT INTO game_notifications (' . implode(', ', $names) . ')
             VALUES (' . implode(', ', $placeholders) . ')'
        );
        $stmt->execute($columns);
    }

    private function userEmail(int $userId): string
    {
        $stmt = $this->db->prepare(
            'SELECT email FROM users WHERE id = :id AND activation = 1 LIMIT 1'
        );
        $stmt->execute(['id' => $userId]);
        $email = trim((string) ($stmt->fetchColumn() ?: ''));
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : '';
    }

    public function systemAccountId(): int
    {
        if ($this->tableExists('site_settings')) {
            $stmt = $this->db->prepare('SELECT value FROM site_settings WHERE name = "system.account_id" LIMIT 1');
            $stmt->execute();
            $id = (int) ($stmt->fetchColumn() ?: 0);
            if ($id > 0) {
                return $id;
            }
        }

        $stmt = $this->db->prepare('SELECT id FROM users WHERE login = "Система" ORDER BY id ASC LIMIT 1');
        $stmt->execute();
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    private function settingBool(string $name, bool $default): bool
    {
        if (!$this->tableExists('site_settings')) {
            return $default;
        }

        $stmt = $this->db->prepare('SELECT value FROM site_settings WHERE name = :name LIMIT 1');
        $stmt->execute(['name' => $name]);
        $value = $stmt->fetchColumn();
        if ($value === false || $value === null || $value === '') {
            return $default;
        }

        return filter_var((string) $value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? $default;
    }

    /**
     * @return list<array{type:string,object_id:int,quantity:int,title:string,message:string,data:array}>
     */
    private function normalizeRewardEntries(array $rewards): array
    {
        $items = [];
        if (isset($rewards['items']) && is_array($rewards['items'])) {
            $items = $rewards['items'];
        } elseif ($this->looksLikeItemMap($rewards)) {
            $items = $rewards;
        }

        if (isset($rewards['coins'])) {
            $items[1] = ($items[1] ?? 0) + (int) $rewards['coins'];
        }
        if (isset($rewards['diamonds'])) {
            $items[2] = ($items[2] ?? 0) + (int) $rewards['diamonds'];
        }

        $entries = [];
        foreach ($items as $itemId => $count) {
            $itemId = (int) $itemId;
            $count = (int) $count;
            if ($itemId <= 0 || $count <= 0) {
                continue;
            }

            $entries[] = [
                'type' => 'item',
                'object_id' => $itemId,
                'quantity' => $count,
                'title' => $this->itemName($itemId),
                'message' => $this->itemRewardText($itemId, $count),
                'data' => ['item_id' => $itemId, 'count' => $count],
            ];
        }

        if (isset($rewards['badges']) && is_array($rewards['badges'])) {
            foreach ($rewards['badges'] as $badge) {
                $entries[] = [
                    'type' => 'badge',
                    'object_id' => (int) ($badge['id'] ?? $badge['badge_id'] ?? 0),
                    'quantity' => 1,
                    'title' => (string) ($badge['title'] ?? $badge['name'] ?? 'Значок'),
                    'message' => (string) ($badge['title'] ?? $badge['name'] ?? 'Значок'),
                    'data' => is_array($badge) ? $badge : ['badge' => $badge],
                ];
            }
        }

        return $entries;
    }

    private function looksLikeItemMap(array $value): bool
    {
        if ($value === []) {
            return false;
        }

        foreach ($value as $key => $itemValue) {
            if (!is_int($key) && !ctype_digit((string) $key)) {
                return false;
            }
            if (!is_scalar($itemValue)) {
                return false;
            }
        }

        return true;
    }

    private function newRewardOperationKey(int $userId, string $sourceType, string|int $sourceId): string
    {
        return sprintf(
            'reward:%d:%s:%s:%s:%d',
            $userId,
            preg_replace('/[^a-zA-Z0-9_.:-]+/', '_', $sourceType) ?: 'reward',
            preg_replace('/[^a-zA-Z0-9_.:-]+/', '_', (string) $sourceId) ?: '0',
            bin2hex(random_bytes(4)),
            time()
        );
    }

    private function rewardTransactionByKey(string $operationKey): ?array
    {
        if ($operationKey === '' || !$this->tableExists('reward_transactions')) {
            return null;
        }

        $stmt = $this->db->prepare('SELECT * FROM reward_transactions WHERE operation_key = :key LIMIT 1');
        $stmt->execute(['key' => $operationKey]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : null;
    }

    private function createRewardTransaction(
        string $operationKey,
        int $userId,
        string $sourceType,
        string $sourceId,
        string $title,
        array $payload
    ): int {
        if (!$this->tableExists('reward_transactions')) {
            return 0;
        }

        $now = time();
        $stmt = $this->db->prepare(
            'INSERT INTO reward_transactions
                (operation_key, user_id, source_type, source_id, title, status, payload_json, result_json, error_message, created_at, updated_at, completed_at)
             VALUES
                (:operation_key, :user, :source_type, :source_id, :title, "pending", :payload, NULL, "", :created_at, :updated_at, 0)
             ON DUPLICATE KEY UPDATE
                status = IF(status = "failed", "pending", status),
                payload_json = VALUES(payload_json),
                updated_at = VALUES(updated_at)'
        );
        $stmt->execute([
            'operation_key' => $operationKey,
            'user' => $userId,
            'source_type' => mb_substr($sourceType, 0, 64),
            'source_id' => mb_substr($sourceId, 0, 96),
            'title' => mb_substr($title, 0, 160),
            'payload' => $this->json($payload),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $stmt = $this->db->prepare('SELECT id FROM reward_transactions WHERE operation_key = :key LIMIT 1');
        $stmt->execute(['key' => $operationKey]);
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    private function insertRewardEntry(int $transactionId, array $entry, string $status): void
    {
        if ($transactionId <= 0 || !$this->tableExists('reward_transaction_entries')) {
            return;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO reward_transaction_entries
                (transaction_id, reward_type, object_id, quantity, title, status, data_json, created_at)
             VALUES
                (:transaction, :type, :object, :quantity, :title, :status, :data, :created_at)'
        );
        $stmt->execute([
            'transaction' => $transactionId,
            'type' => mb_substr((string) ($entry['type'] ?? ''), 0, 32),
            'object' => max(0, (int) ($entry['object_id'] ?? 0)),
            'quantity' => max(0, (int) ($entry['quantity'] ?? 0)),
            'title' => mb_substr((string) ($entry['title'] ?? ''), 0, 160),
            'status' => mb_substr($status, 0, 24),
            'data' => $this->json((array) ($entry['data'] ?? [])),
            'created_at' => time(),
        ]);
    }

    private function markRewardTransaction(int $transactionId, string $status, array $result = [], string $error = ''): void
    {
        if ($transactionId <= 0 || !$this->tableExists('reward_transactions')) {
            return;
        }

        $now = time();
        $stmt = $this->db->prepare(
            'UPDATE reward_transactions
                SET status = :status,
                    result_json = :result,
                    error_message = :error,
                    updated_at = :updated,
                    completed_at = CASE WHEN :completed_status IN ("completed", "failed") THEN :completed ELSE completed_at END
              WHERE id = :id
              LIMIT 1'
        );
        $stmt->execute([
            'status' => mb_substr($status, 0, 24),
            'result' => $this->json($result),
            'error' => mb_substr($error, 0, 255),
            'updated' => $now,
            'completed_status' => $status,
            'completed' => $now,
            'id' => $transactionId,
        ]);
    }

    private function decodeJson(string $value): array
    {
        $decoded = json_decode($value !== '' ? $value : '{}', true);
        return is_array($decoded) ? $decoded : [];
    }

    private function json(array $data): string
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE) ?: '{}';
    }

    public function activeMultiplier(int $userId, string $boostKey, string $scope = 'global'): float
    {
        $now = time();
        $multiplier = 1.0;
        $boostKeys = $this->boostAliases($boostKey);

        if ($this->tableExists('game_event_boosts')) {
            [$boostWhere, $boostParams] = $this->boostWhereParams($boostKeys);
            $stmt = $this->db->prepare(
                'SELECT multiplier
                   FROM game_event_boosts
                  WHERE boost_key IN (' . $boostWhere . ')
                    AND enabled = 1
                    AND (scope = "global" OR scope = :scope)
                    AND (starts_at = 0 OR starts_at <= :now_a)
                    AND (ends_at = 0 OR ends_at >= :now_b)'
            );
            $stmt->execute($boostParams + ['scope' => $scope, 'now_a' => $now, 'now_b' => $now]);
            foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) ?: [] as $value) {
                $multiplier *= max(1.0, (float) $value);
            }
        }

        if ($userId > 0 && $this->tableExists('player_boosts')) {
            [$boostWhere, $boostParams] = $this->boostWhereParams($boostKeys);
            $stmt = $this->db->prepare(
                'SELECT multiplier
                   FROM player_boosts
                  WHERE user_id = :user
                    AND boost_key IN (' . $boostWhere . ')
                    AND active = 1
                    AND (starts_at = 0 OR starts_at <= :now_a)
                    AND (expires_at = 0 OR expires_at >= :now_b)'
            );
            $stmt->execute(['user' => $userId] + $boostParams + ['now_a' => $now, 'now_b' => $now]);
            foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) ?: [] as $value) {
                $multiplier *= max(1.0, (float) $value);
            }
        }

        return min(20.0, $multiplier);
    }

    /**
     * @return list<string>
     */
    private function boostAliases(string $boostKey): array
    {
        return match ($boostKey) {
            'coins', 'money' => ['coins', 'money'],
            default => [$boostKey],
        };
    }

    /**
     * @param list<string> $boostKeys
     * @return array{0:string,1:array<string,string>}
     */
    private function boostWhereParams(array $boostKeys): array
    {
        $params = [];
        $placeholders = [];
        foreach (array_values($boostKeys) as $index => $boost) {
            $key = 'boost_' . $index;
            $placeholders[] = ':' . $key;
            $params[$key] = $boost;
        }

        return [implode(',', $placeholders), $params];
    }

    /**
     * @return list<array>
     */
    public function pullUnread(int $userId, int $limit = 8): array
    {
        if ($userId <= 0 || !$this->tableExists('game_notifications')) {
            return [];
        }

        $limit = max(1, min(20, $limit));
        $stmt = $this->db->prepare(
            'SELECT id, title, message, variant, payload_json, source, created_at
               FROM game_notifications
              WHERE user_id = :user AND read_at = 0
              ORDER BY id ASC
              LIMIT :limit'
        );
        $stmt->bindValue(':user', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        if ($rows !== []) {
            $ids = array_map(static fn (array $row): int => (int) $row['id'], $rows);
            $this->markRead($userId, $ids);
        }

        return $rows;
    }

    /**
     * @param list<int> $ids
     */
    private function markRead(int $userId, array $ids): void
    {
        $ids = array_values(array_filter(array_map('intval', $ids)));
        if ($ids === []) {
            return;
        }

        $in = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare("UPDATE game_notifications SET read_at = ? WHERE user_id = ? AND id IN ($in)");
        $stmt->execute([time(), $userId, ...$ids]);
    }

    private function addItem(int $userId, int $itemId, int $count): void
    {
        $this->withItemsUsersLock(function () use ($userId, $itemId, $count): void {
            $existing = $this->db->prepare('SELECT id FROM items_users WHERE user_id = :user AND item_id = :item AND dattimer = "not" LIMIT 1');
            $existing->execute(['user' => $userId, 'item' => $itemId]);
            $rowId = (int) ($existing->fetchColumn() ?: 0);
            if ($rowId > 0) {
                $this->db->prepare('UPDATE items_users SET count = count + :count WHERE id = :id LIMIT 1')
                    ->execute(['count' => $count, 'id' => $rowId]);
                return;
            }

            $this->db->prepare(
                'INSERT INTO items_users (id, item_id, user_id, count, dattimer, timers)
                 VALUES (:id, :item, :user, :count, "not", "not")'
            )->execute([
                'id' => $this->nextTableId('items_users', 'id'),
                'item' => $itemId,
                'user' => $userId,
                'count' => $count,
            ]);
        });
    }

    private function itemRewardText(int $itemId, int $count): string
    {
        if ($itemId === 1) {
            return number_format($count, 0, ',', ' ') . ' монет';
        }
        if ($itemId === 2) {
            return number_format($count, 0, ',', ' ') . ' алмазов';
        }

        $stmt = $this->db->prepare('SELECT name FROM items WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $itemId]);
        $name = (string) ($stmt->fetchColumn() ?: ('Предмет #' . $itemId));
        return $name . ' x' . $count;
    }

    private function itemName(int $itemId): string
    {
        $stmt = $this->db->prepare('SELECT name FROM items WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $itemId]);
        return (string) ($stmt->fetchColumn() ?: ('Предмет #' . $itemId));
    }

    private function findGymBadge(string|int $badge): ?array
    {
        if (!$this->tableExists('gym_badges')) {
            return null;
        }

        if (is_int($badge) || ctype_digit((string) $badge)) {
            $stmt = $this->db->prepare(
                'SELECT id, badge_key, title, leader_name, location_id, icon_item_id
                   FROM gym_badges
                  WHERE id = :id
                  LIMIT 1'
            );
            $stmt->execute(['id' => (int) $badge]);
        } else {
            $stmt = $this->db->prepare(
                'SELECT id, badge_key, title, leader_name, location_id, icon_item_id
                   FROM gym_badges
                  WHERE badge_key = :key
                  LIMIT 1'
            );
            $stmt->execute(['key' => trim((string) $badge)]);
        }

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : null;
    }

    private function formatGymBadgeReward(array $badge, int $issuedAt, string $sourceType, int $sourceId): array
    {
        return [
            'id' => (int) $badge['id'],
            'key' => (string) $badge['badge_key'],
            'title' => (string) $badge['title'],
            'leader' => (string) ($badge['leader_name'] ?? ''),
            'locationId' => (int) ($badge['location_id'] ?? 0),
            'iconItemId' => (int) ($badge['icon_item_id'] ?? 0),
            'rewardType' => 'gym_badge',
            'issuedAt' => $issuedAt,
            'source' => [
                'type' => $sourceType,
                'id' => max(0, $sourceId),
                'battleId' => in_array($sourceType, ['battle', 'gym_battle', 'pve', 'pvp'], true) ? max(0, $sourceId) : 0,
                'questId' => $sourceType === 'quest' ? max(0, $sourceId) : 0,
            ],
        ];
    }

    private function tableExists(string $table): bool
    {
        static $cache = [];
        if (isset($cache[$table])) {
            return $cache[$table];
        }
        $stmt = $this->db->prepare(
            'SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table LIMIT 1'
        );
        $stmt->execute(['table' => $table]);
        $cache[$table] = (bool) $stmt->fetchColumn();
        return $cache[$table];
    }

    private function columnExists(string $table, string $column): bool
    {
        static $cache = [];
        $key = $table . '.' . $column;
        if (isset($cache[$key])) {
            return $cache[$key];
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table) || !preg_match('/^[a-zA-Z0-9_]+$/', $column)) {
            return false;
        }

        $stmt = $this->db->prepare(
            'SELECT 1
               FROM INFORMATION_SCHEMA.COLUMNS
              WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table AND COLUMN_NAME = :column
              LIMIT 1'
        );
        $stmt->execute(['table' => $table, 'column' => $column]);
        $cache[$key] = (bool) $stmt->fetchColumn();
        return $cache[$key];
    }

    private function nextTableId(string $table, string $column): int
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table) || !preg_match('/^[a-zA-Z0-9_]+$/', $column)) {
            return 1;
        }
        return (int) ($this->db->query(sprintf('SELECT COALESCE(MAX(`%s`), 0) + 1 FROM `%s`', $column, $table))->fetchColumn() ?: 1);
    }

    private function withItemsUsersLock(callable $callback): void
    {
        $lock = $this->db->prepare('SELECT GET_LOCK(:name, 15)');
        $lock->execute(['name' => 'pokemon8_seq_items_users_id']);
        if ((int) ($lock->fetchColumn() ?: 0) !== 1) {
            throw new \RuntimeException('Unable to acquire items_users lock.');
        }

        try {
            $callback();
        } finally {
            $release = $this->db->prepare('SELECT RELEASE_LOCK(:name)');
            $release->execute(['name' => 'pokemon8_seq_items_users_id']);
        }
    }
}
