<?php
declare(strict_types=1);

namespace Pokemon8\Repository;

use PDO;

final class InventoryRepository
{
    public function __construct(private PDO $db)
    {
    }

    public function countItem(int $userId, int $itemId): int
    {
        $stmt = $this->db->prepare(
            'SELECT count, dattimer FROM items_users WHERE user_id = :user AND item_id = :item LIMIT 1'
        );
        $stmt->execute([
            'user' => $userId,
            'item' => $itemId,
        ]);
        $row = $stmt->fetch();

        if (!$row) {
            return 0;
        }

        if (($row['dattimer'] ?? 'not') !== 'not' && (int) $row['dattimer'] <= time()) {
            return 0;
        }

        return max(0, (int) $row['count']);
    }

    public function hasItem(int $userId, int $itemId, int $count): bool
    {
        return $this->countItem($userId, $itemId) >= $count;
    }

    public function addItem(int $userId, int $itemId, int $count): void
    {
        if ($count <= 0) {
            return;
        }

        $existing = $this->findRow($userId, $itemId);
        if ($existing !== null) {
            $stmt = $this->db->prepare('UPDATE items_users SET count = count + :count WHERE id = :id LIMIT 1');
            $stmt->execute([
                'count' => $count,
                'id' => (int) $existing['id'],
            ]);
            return;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO items_users (item_id, user_id, count, dattimer, timers) VALUES (:item, :user, :count, :dattimer, :timers)'
        );
        $stmt->execute([
            'item' => $itemId,
            'user' => $userId,
            'count' => $count,
            'dattimer' => 'not',
            'timers' => 'not',
        ]);
    }

    public function removeItem(int $userId, int $itemId, int $count): bool
    {
        if ($count <= 0 || !$this->hasItem($userId, $itemId, $count)) {
            return false;
        }

        $existing = $this->findRow($userId, $itemId);
        if ($existing === null) {
            return false;
        }

        $newCount = (int) $existing['count'] - $count;
        if ($newCount > 0) {
            $stmt = $this->db->prepare('UPDATE items_users SET count = :count WHERE id = :id LIMIT 1');
            $stmt->execute([
                'count' => $newCount,
                'id' => (int) $existing['id'],
            ]);
            return true;
        }

        $stmt = $this->db->prepare('DELETE FROM items_users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => (int) $existing['id']]);

        return true;
    }

    private function findRow(int $userId, int $itemId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, count FROM items_users WHERE user_id = :user AND item_id = :item LIMIT 1'
        );
        $stmt->execute([
            'user' => $userId,
            'item' => $itemId,
        ]);

        return $stmt->fetch() ?: null;
    }
}
