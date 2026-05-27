<?php
declare(strict_types=1);

namespace Pokemon8\Repository;

use PDO;

final class RewardRepository
{
    public function __construct(private PDO $db, private ?SafeStorageRepository $safeStorage = null)
    {
    }

    public function setSafeStorageRepository(SafeStorageRepository $safeStorage): void
    {
        $this->safeStorage = $safeStorage;
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

            try {
                $this->addItem($userId, $itemId, $count);
                $messages[] = $this->itemRewardText($itemId, $count);
            } catch (\Throwable $e) {
                $this->safeStorage?->storeItem($userId, $itemId, $count, 'reward.grantItems', $source, [
                    'source' => $source,
                    'item_id' => $itemId,
                    'count' => $count,
                ], 'reward_grant_failed', $e->getMessage());
                $this->safeStorage?->recordRollback(
                    'reward_grant_failed:' . $userId . ':' . $itemId . ':' . time(),
                    'reward_grant_items',
                    $userId,
                    'reward',
                    $source,
                    [],
                    ['failed_item_id' => $itemId, 'failed_count' => $count],
                    ['safe_storage' => true, 'item_id' => $itemId, 'count' => $count],
                    'failed',
                    $e->getMessage()
                );
            }
        }

        if ($messages !== []) {
            $this->notify(
                $userId,
                $source,
                'Вам начислено: ' . implode(', ', $messages) . '.',
                'reward',
                ['items' => $items]
            );
        }

        return $messages;
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
