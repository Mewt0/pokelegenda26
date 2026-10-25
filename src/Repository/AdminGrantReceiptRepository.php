<?php
declare(strict_types=1);
namespace Pokemon8\Repository;

use PDO;
use Pokemon8\Service\AdminGrantException;

final class AdminGrantReceiptRepository
{
    public function __construct(private PDO $db, private AdminRepository $admin, private ?\Closure $afterCredit = null)
    {
        if ($afterCredit !== null && !self::localQa()) {
            throw new \LogicException('Failpoints require local QA.');
        }
    }

    public static function localQa(): bool
    {
        return ($_ENV['APP_ENV'] ?? '') === 'local' && ($_ENV['QA_TOOLS_ENABLED'] ?? '') === '1';
    }

    public function preview(array $payload): array
    {
        $validated = $this->admin->validateItemGrant($this->legacyPayload($payload));
        if (empty($validated['ok'])) {
            throw new AdminGrantException('invalid_target_or_item', 422);
        }
        $target = $this->db->prepare('SELECT id, login FROM users WHERE id = :id');
        $target->execute(['id' => $payload['target_user_id']]);
        $item = $this->db->prepare('SELECT id, name FROM items WHERE id = :id LIMIT 1');
        $item->execute(['id' => $payload['item_id']]);
        $before = $this->quantity($payload);
        if ($before < 0 || $before + $payload['quantity'] > 2147483647) {
            throw new AdminGrantException('quantity_overflow', 422);
        }
        return ['normalized' => $payload, 'target' => $target->fetch(), 'item' => $item->fetch(),
            'before_qty' => $before, 'after_qty' => $before + $payload['quantity'], 'warnings' => []];
    }

    public function replay(int $adminId, string $key, string $hash): ?array
    {
        $stmt = $this->db->prepare('SELECT payload_hash, response_json FROM admin_grant_receipts WHERE admin_id = :admin AND idempotency_key = :key');
        $stmt->execute(['admin' => $adminId, 'key' => $key]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        if (!hash_equals($row['payload_hash'], $hash)) {
            throw new AdminGrantException('idempotency_conflict', 409);
        }
        return json_decode($row['response_json'], true, 512, JSON_THROW_ON_ERROR);
    }

    public function confirm(int $adminId, string $key, string $hash, array $payload, string $reason, array $ticket, string $ticketHash): array
    {
        if ($this->db->inTransaction()) {
            throw new \LogicException('Grant requires an independent transaction.');
        }
        $lock = $this->db->prepare('SELECT GET_LOCK(:name, 15)');
        $lock->execute(['name' => 'pokemon8_seq_items_users_id']);
        if ((int) $lock->fetchColumn() !== 1) {
            throw new AdminGrantException('inventory_busy', 409);
        }
        try {
            // This mutex is shared with the legacy allocator and held through commit.
            $this->db->beginTransaction();
            $replay = $this->replay($adminId, $key, $hash);
            if ($replay !== null) {
                $this->db->rollBack();
                return $replay;
            }
            $target = $this->db->prepare('SELECT id FROM users WHERE id = :id FOR UPDATE');
            $target->execute(['id' => $payload['target_user_id']]);
            if (!$target->fetchColumn()) {
                throw new AdminGrantException('target_missing', 409);
            }
            $item = $this->db->prepare('SELECT id FROM items WHERE id = :id LIMIT 1 LOCK IN SHARE MODE');
            $item->execute(['id' => $payload['item_id']]);
            if (!$item->fetchColumn()) {
                throw new AdminGrantException('item_missing', 409);
            }
            $before = $this->quantity($payload, true);
            if ($before !== $ticket['before_qty'] || $before + $payload['quantity'] > 2147483647) {
                throw new AdminGrantException('stale_inventory', 409);
            }
            $publicId = bin2hex(random_bytes(16));
            $now = time();
            $insert = $this->db->prepare('INSERT INTO admin_grant_receipts
                (public_id, admin_id, idempotency_key, payload_hash, preview_hash, correlation_id, target_user_id, item_id, quantity, before_qty, after_qty, reason, result, response_json, created_at, completed_at)
                VALUES (:public, :admin, :key, :hash, :preview, :correlation, :target, :item, :qty, :before, :after, :reason, "pending", "", :created, 0)');
            $insert->execute(['public' => $publicId, 'admin' => $adminId, 'key' => $key, 'hash' => $hash,
                'preview' => $ticketHash, 'correlation' => $ticket['correlation_id'], 'target' => $payload['target_user_id'],
                'item' => $payload['item_id'], 'qty' => $payload['quantity'], 'before' => $before,
                'after' => $before + $payload['quantity'], 'reason' => $reason, 'created' => $now]);
            $receiptId = (int) $this->db->lastInsertId();
            $grant = $this->admin->grantItemCore($this->legacyPayload($payload), true);
            if (empty($grant['ok'])) {
                throw new AdminGrantException('grant_validation_changed', 409);
            }
            if ($this->afterCredit !== null) {
                ($this->afterCredit)();
            }
            $response = ['ok' => true, 'data' => ['receipt_id' => $publicId, 'correlation_id' => $ticket['correlation_id'],
                'normalized' => $payload, 'before_qty' => $before, 'after_qty' => $before + $payload['quantity'],
                'result' => 'completed', 'created_at' => $now, 'completed_at' => $now]];
            $auditPayload = $response['data'] + ['actor_id' => $adminId, 'reason' => $reason];
            $audit = $this->db->prepare('INSERT INTO admin_audit_log (admin_id, action, entity, entity_id, payload, created_at)
                VALUES (:admin, "item.grant.v2", "items_users", :entity, :payload, :created)');
            $audit->execute(['admin' => $adminId, 'entity' => $grant['row_id'],
                'payload' => json_encode($auditPayload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), 'created' => $now]);
            $this->db->prepare('UPDATE admin_grant_receipts SET result = "completed", response_json = :response, completed_at = :completed WHERE id = :id')
                ->execute(['response' => json_encode($response, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), 'completed' => $now, 'id' => $receiptId]);
            $this->db->commit();
            return $response;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            if ($e instanceof AdminGrantException) {
                throw $e;
            }
            if ($e instanceof \PDOException && $e->getCode() === '23000') {
                throw new AdminGrantException('receipt_conflict', 409);
            }
            // No raw request, SQL, exception text, tickets or session IDs in failure logs.
            error_log('admin_grant_v2 operation_failed correlation=' . $ticket['correlation_id']);
            throw new AdminGrantException('operation_failed', 503);
        } finally {
            $this->db->prepare('SELECT RELEASE_LOCK(:name)')->execute(['name' => 'pokemon8_seq_items_users_id']);
        }
    }

    private function quantity(array $payload, bool $lock = false): int
    {
        $stmt = $this->db->prepare('SELECT count FROM items_users WHERE user_id = :user AND item_id = :item AND dattimer = "not"' . ($lock ? ' FOR UPDATE' : ''));
        $stmt->execute(['user' => $payload['target_user_id'], 'item' => $payload['item_id']]);
        return array_sum(array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN)));
    }

    private function legacyPayload(array $payload): array
    {
        return ['user_id' => $payload['target_user_id'], 'item_id' => $payload['item_id'], 'count' => $payload['quantity']];
    }
}
