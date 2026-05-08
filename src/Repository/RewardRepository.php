<?php
declare(strict_types=1);

namespace Pokemon8\Repository;

use PDO;

final class RewardRepository
{
    public function __construct(private PDO $db)
    {
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
        $messages = [];
        foreach ($items as $itemId => $count) {
            $itemId = (int) $itemId;
            $count = (int) round((int) $count * $multiplier);
            if ($userId <= 0 || $itemId <= 0 || $count <= 0) {
                continue;
            }

            $this->addItem($userId, $itemId, $count);
            $messages[] = $this->itemRewardText($itemId, $count);
        }

        if ($messages !== []) {
            $this->notify(
                $userId,
                $source,
                'Получено: ' . implode(', ', $messages) . '.',
                'reward',
                ['items' => $items]
            );
        }

        return $messages;
    }

    public function notify(int $userId, string $title, string $message, string $variant = 'info', array $payload = []): void
    {
        if ($userId <= 0 || trim($message) === '' || !$this->tableExists('game_notifications')) {
            return;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO game_notifications (user_id, title, message, variant, payload_json, source, created_at, read_at)
             VALUES (:user_id, :title, :message, :variant, :payload_json, :source, :created_at, 0)'
        );
        $stmt->execute([
            'user_id' => $userId,
            'title' => mb_substr($title, 0, 120),
            'message' => mb_substr($message, 0, 500),
            'variant' => mb_substr($variant, 0, 32),
            'payload_json' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'source' => mb_substr($title, 0, 64),
            'created_at' => time(),
        ]);
    }

    public function activeMultiplier(int $userId, string $boostKey, string $scope = 'global'): float
    {
        $now = time();
        $multiplier = 1.0;

        if ($this->tableExists('game_event_boosts')) {
            $stmt = $this->db->prepare(
                'SELECT multiplier
                   FROM game_event_boosts
                  WHERE boost_key = :boost
                    AND enabled = 1
                    AND (scope = "global" OR scope = :scope)
                    AND (starts_at = 0 OR starts_at <= :now_a)
                    AND (ends_at = 0 OR ends_at >= :now_b)'
            );
            $stmt->execute(['boost' => $boostKey, 'scope' => $scope, 'now_a' => $now, 'now_b' => $now]);
            foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) ?: [] as $value) {
                $multiplier *= max(1.0, (float) $value);
            }
        }

        if ($userId > 0 && $this->tableExists('player_boosts')) {
            $stmt = $this->db->prepare(
                'SELECT multiplier
                   FROM player_boosts
                  WHERE user_id = :user
                    AND boost_key = :boost
                    AND active = 1
                    AND (starts_at = 0 OR starts_at <= :now_a)
                    AND (expires_at = 0 OR expires_at >= :now_b)'
            );
            $stmt->execute(['user' => $userId, 'boost' => $boostKey, 'now_a' => $now, 'now_b' => $now]);
            foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) ?: [] as $value) {
                $multiplier *= max(1.0, (float) $value);
            }
        }

        return min(20.0, $multiplier);
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

    private function nextTableId(string $table, string $column): int
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table) || !preg_match('/^[a-zA-Z0-9_]+$/', $column)) {
            return 1;
        }
        return (int) ($this->db->query(sprintf('SELECT COALESCE(MAX(`%s`), 0) + 1 FROM `%s`', $column, $table))->fetchColumn() ?: 1);
    }
}
