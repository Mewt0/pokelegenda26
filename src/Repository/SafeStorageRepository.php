<?php
declare(strict_types=1);

namespace Pokemon8\Repository;

use PDO;
use Throwable;

final class SafeStorageRepository
{
    public function __construct(private PDO $db)
    {
    }

    public function storeItem(
        int $userId,
        int $itemId,
        int $quantity,
        string $sourceType,
        string|int $sourceId = '',
        array $payload = [],
        string $reason = 'safe_return',
        string $error = ''
    ): int {
        return $this->storeObject($userId, 'item', $itemId, $quantity, $sourceType, $sourceId, $payload, $reason, $error);
    }

    public function storePokemon(
        int $userId,
        int $pokemonId,
        string $sourceType,
        string|int $sourceId = '',
        array $payload = [],
        string $reason = 'safe_return',
        string $error = ''
    ): int {
        return $this->storeObject($userId, 'pokemon', $pokemonId, 1, $sourceType, $sourceId, $payload, $reason, $error);
    }

    public function storeEgg(
        int $userId,
        int $eggId,
        string $sourceType,
        string|int $sourceId = '',
        array $payload = [],
        string $reason = 'safe_return',
        string $error = ''
    ): int {
        return $this->storeObject($userId, 'egg', $eggId, 1, $sourceType, $sourceId, $payload, $reason, $error);
    }

    public function storeObject(
        int $userId,
        string $objectType,
        int $objectId,
        int $quantity,
        string $sourceType,
        string|int $sourceId = '',
        array $payload = [],
        string $reason = 'safe_return',
        string $error = ''
    ): int {
        if (!$this->tableExists('safe_storage_entries')) {
            return 0;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO safe_storage_entries
                (user_id, source_type, source_id, object_type, object_id, quantity, payload_json, reason, error_message, status, created_at, resolved_at, resolved_by)
             VALUES
                (:user, :source_type, :source_id, :object_type, :object_id, :quantity, :payload, :reason, :error, "pending", :time, 0, 0)'
        );
        $stmt->execute([
            'user' => max(0, $userId),
            'source_type' => mb_substr($sourceType, 0, 48),
            'source_id' => mb_substr((string) $sourceId, 0, 64),
            'object_type' => mb_substr($objectType, 0, 24),
            'object_id' => max(0, $objectId),
            'quantity' => max(1, $quantity),
            'payload' => $this->json($payload),
            'reason' => mb_substr($reason, 0, 80),
            'error' => mb_substr($error, 0, 255),
            'time' => time(),
        ]);
        $entryId = (int) $this->db->lastInsertId();
        $this->log($entryId, 0, 'safe_storage.store', 0, [
            'object_type' => $objectType,
            'object_id' => $objectId,
            'quantity' => $quantity,
            'source_type' => $sourceType,
            'source_id' => (string) $sourceId,
            'reason' => $reason,
        ]);

        return $entryId;
    }

    public function recordRollback(
        string $operationKey,
        string $operationType,
        int $userId,
        string $sourceType,
        string|int $sourceId,
        array $before,
        array $after,
        array $rollback,
        string $status = 'open',
        string $error = ''
    ): int {
        if (!$this->tableExists('safe_operation_rollbacks')) {
            return 0;
        }

        $now = time();
        $stmt = $this->db->prepare(
            'INSERT INTO safe_operation_rollbacks
                (operation_key, operation_type, user_id, source_type, source_id, before_json, after_json, rollback_json,
                 status, error_message, created_at, updated_at, applied_at, applied_by)
             VALUES
                (:operation_key, :operation_type, :user, :source_type, :source_id, :before_json, :after_json, :rollback_json,
                 :status, :error, :created_at, :updated_at, 0, 0)
             ON DUPLICATE KEY UPDATE
                 before_json = VALUES(before_json),
                 after_json = VALUES(after_json),
                 rollback_json = VALUES(rollback_json),
                 status = VALUES(status),
                 error_message = VALUES(error_message),
                 updated_at = VALUES(updated_at)'
        );
        $stmt->execute([
            'operation_key' => mb_substr($operationKey, 0, 96),
            'operation_type' => mb_substr($operationType, 0, 48),
            'user' => max(0, $userId),
            'source_type' => mb_substr($sourceType, 0, 48),
            'source_id' => mb_substr((string) $sourceId, 0, 64),
            'before_json' => $this->json($before),
            'after_json' => $this->json($after),
            'rollback_json' => $this->json($rollback),
            'status' => mb_substr($status, 0, 24),
            'error' => mb_substr($error, 0, 255),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $id = $this->rollbackIdByKey($operationKey);
        $this->log(0, $id, 'safe_rollback.record', 0, [
            'operation_key' => $operationKey,
            'operation_type' => $operationType,
            'source_type' => $sourceType,
            'source_id' => (string) $sourceId,
            'status' => $status,
        ]);

        return $id;
    }

    public function markStorageResolved(int $entryId, int $actorId, string $status = 'resolved', array $data = []): void
    {
        if ($entryId <= 0 || !$this->tableExists('safe_storage_entries')) {
            return;
        }
        $this->db->prepare(
            'UPDATE safe_storage_entries
                SET status = :status, resolved_at = :time, resolved_by = :actor
              WHERE id = :id
              LIMIT 1'
        )->execute([
            'status' => mb_substr($status, 0, 24),
            'time' => time(),
            'actor' => max(0, $actorId),
            'id' => $entryId,
        ]);
        $this->log($entryId, 0, 'safe_storage.' . $status, $actorId, $data);
    }

    public function pendingCounts(): array
    {
        if (!$this->tableExists('safe_storage_entries')) {
            return ['storage' => 0, 'rollbacks' => 0];
        }
        $storage = (int) ($this->db->query('SELECT COUNT(*) FROM safe_storage_entries WHERE status = "pending"')->fetchColumn() ?: 0);
        $rollbacks = $this->tableExists('safe_operation_rollbacks')
            ? (int) ($this->db->query('SELECT COUNT(*) FROM safe_operation_rollbacks WHERE status IN ("open", "failed")')->fetchColumn() ?: 0)
            : 0;
        return ['storage' => $storage, 'rollbacks' => $rollbacks];
    }

    private function rollbackIdByKey(string $operationKey): int
    {
        $stmt = $this->db->prepare('SELECT id FROM safe_operation_rollbacks WHERE operation_key = :key LIMIT 1');
        $stmt->execute(['key' => mb_substr($operationKey, 0, 96)]);
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    private function log(int $entryId, int $rollbackId, string $action, int $actorId, array $data): void
    {
        if (!$this->tableExists('safe_storage_logs')) {
            return;
        }
        try {
            $this->db->prepare(
                'INSERT INTO safe_storage_logs (entry_id, rollback_id, action, actor_id, data_json, created_at)
                 VALUES (:entry, :rollback, :action, :actor, :data, :time)'
            )->execute([
                'entry' => max(0, $entryId),
                'rollback' => max(0, $rollbackId),
                'action' => mb_substr($action, 0, 48),
                'actor' => max(0, $actorId),
                'data' => $this->json($data),
                'time' => time(),
            ]);
        } catch (Throwable) {
            // Safe storage logging must never break the gameplay flow.
        }
    }

    private function json(array $data): string
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
    }

    private function tableExists(string $table): bool
    {
        static $cache = [];
        if (isset($cache[$table])) {
            return $cache[$table];
        }
        try {
            $stmt = $this->db->prepare(
                'SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table LIMIT 1'
            );
            $stmt->execute(['table' => $table]);
            $cache[$table] = (bool) $stmt->fetchColumn();
        } catch (Throwable) {
            $cache[$table] = false;
        }
        return $cache[$table];
    }
}
