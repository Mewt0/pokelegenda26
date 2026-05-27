<?php
declare(strict_types=1);

namespace Pokemon8\Repository;

use PDO;

final class GameEventRepository
{
    private const BOOST_LABELS = [
        'exp' => 'Опыт',
        'coins' => 'Монеты',
        'money' => 'Монеты',
        'drop' => 'Шанс дропа',
        'catch' => 'Шанс ловли',
        'happiness' => 'Счастье покемонов',
        'quest_rewards' => 'Квестовые награды',
    ];

    private const SCOPE_LABELS = [
        'global' => 'Везде',
        'pve' => 'PvE',
        'pvp' => 'PvP',
        'quest' => 'Квесты',
        'market' => 'Магазин',
        'all' => 'Все режимы',
    ];

    public function __construct(
        private PDO $db,
        private RewardRepository $rewards,
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function dashboardForUser(int $userId): array
    {
        return [
            'serverTime' => time(),
            'activeEvents' => $this->globalEvents('active'),
            'upcomingEvents' => $this->globalEvents('upcoming'),
            'personalBoosts' => $this->personalBoosts($userId),
            'effectiveMultipliers' => $this->effectiveMultipliers($userId),
            'qaChecklist' => $this->qaChecklist(),
        ];
    }

    /**
     * @return array<string,mixed>
     */
    public function activePayload(int $userId): array
    {
        return [
            'serverTime' => time(),
            'activeEvents' => $this->globalEvents('active'),
            'personalBoosts' => $this->personalBoosts($userId),
            'effectiveMultipliers' => $this->effectiveMultipliers($userId),
        ];
    }

    /**
     * @return list<array<string,mixed>>
     */
    private function globalEvents(string $mode): array
    {
        if (!$this->tableExists('game_event_boosts')) {
            return [];
        }

        $now = time();
        if ($mode === 'upcoming') {
            $where = 'enabled = 1 AND starts_at > :now';
            $order = 'starts_at ASC, id DESC';
        } else {
            $where = 'enabled = 1
                AND (starts_at = 0 OR starts_at <= :now)
                AND (ends_at = 0 OR ends_at >= :now_end)';
            $order = 'ends_at = 0 DESC, ends_at ASC, id DESC';
        }

        $stmt = $this->db->prepare(
            'SELECT id, title, boost_key, multiplier, scope, starts_at, ends_at, enabled, note
               FROM game_event_boosts
              WHERE ' . $where . '
              ORDER BY ' . $order . '
              LIMIT 30'
        );
        $params = ['now' => $now];
        if ($mode !== 'upcoming') {
            $params['now_end'] = $now;
        }
        $stmt->execute($params);

        return array_map(fn (array $row): array => $this->normalizeEventRow($row, $now), $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
    }

    /**
     * @return list<array<string,mixed>>
     */
    private function personalBoosts(int $userId): array
    {
        if ($userId <= 0 || !$this->tableExists('player_boosts')) {
            return [];
        }

        $now = time();
        $stmt = $this->db->prepare(
            'SELECT id, item_id, boost_key, multiplier, starts_at, expires_at, active, source, created_at
               FROM player_boosts
              WHERE user_id = :user
                AND active = 1
                AND (starts_at = 0 OR starts_at <= :now_start)
                AND (expires_at = 0 OR expires_at >= :now_end)
              ORDER BY expires_at = 0 DESC, expires_at ASC, id DESC
              LIMIT 30'
        );
        $stmt->execute(['user' => $userId, 'now_start' => $now, 'now_end' => $now]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return array_map(function (array $row) use ($now): array {
            $boostKey = $this->boostKey((string) ($row['boost_key'] ?? ''));
            $expiresAt = (int) ($row['expires_at'] ?? 0);

            return [
                'id' => (int) ($row['id'] ?? 0),
                'item_id' => (int) ($row['item_id'] ?? 0),
                'boost_key' => $boostKey,
                'boost_label' => $this->boostLabel($boostKey),
                'multiplier' => (float) ($row['multiplier'] ?? 1),
                'starts_at' => (int) ($row['starts_at'] ?? 0),
                'ends_at' => $expiresAt,
                'remaining_seconds' => $expiresAt > 0 ? max(0, $expiresAt - $now) : 0,
                'source' => (string) ($row['source'] ?? ''),
            ];
        }, $rows);
    }

    /**
     * @return array<string,array<string,array<string,mixed>>>
     */
    private function effectiveMultipliers(int $userId): array
    {
        $matrix = [
            'pve' => ['exp', 'coins', 'drop', 'catch', 'happiness'],
            'pvp' => ['exp', 'coins'],
            'quest' => ['quest_rewards'],
        ];

        $result = [];
        foreach ($matrix as $scope => $boosts) {
            foreach ($boosts as $boostKey) {
                $multiplier = $this->rewards->activeMultiplier($userId, $boostKey, $scope);
                $result[$scope][$boostKey] = [
                    'boost_key' => $boostKey,
                    'boost_label' => $this->boostLabel($boostKey),
                    'scope' => $scope,
                    'scope_label' => $this->scopeLabel($scope),
                    'multiplier' => $multiplier,
                    'active' => $multiplier > 1.0,
                ];
            }
        }

        return $result;
    }

    /**
     * @param array<string,mixed> $row
     * @return array<string,mixed>
     */
    private function normalizeEventRow(array $row, int $now): array
    {
        $boostKey = $this->boostKey((string) ($row['boost_key'] ?? ''));
        $scope = $this->scope((string) ($row['scope'] ?? 'global'));
        $startsAt = (int) ($row['starts_at'] ?? 0);
        $endsAt = (int) ($row['ends_at'] ?? 0);

        return [
            'id' => (int) ($row['id'] ?? 0),
            'title' => (string) ($row['title'] ?? ''),
            'boost_key' => $boostKey,
            'boost_label' => $this->boostLabel($boostKey),
            'multiplier' => (float) ($row['multiplier'] ?? 1),
            'scope' => $scope,
            'scope_label' => $this->scopeLabel($scope),
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'remaining_seconds' => $endsAt > 0 ? max(0, $endsAt - $now) : 0,
            'starts_in_seconds' => $startsAt > $now ? $startsAt - $now : 0,
            'status_label' => $this->eventStatus($startsAt, $endsAt, (int) ($row['enabled'] ?? 0), $now),
            'note' => (string) ($row['note'] ?? ''),
        ];
    }

    /**
     * @return list<array<string,string>>
     */
    private function qaChecklist(): array
    {
        return [
            ['key' => 'exp', 'title' => 'Опыт', 'expected' => 'После PvE-победы опыт умножается на активный множитель.'],
            ['key' => 'coins', 'title' => 'Монеты', 'expected' => 'Монеты за бой приходят с множителем события.'],
            ['key' => 'happiness', 'title' => 'Счастье', 'expected' => 'Прирост счастья после победы увеличивается.'],
            ['key' => 'drop', 'title' => 'Дроп', 'expected' => 'Шанс дропа умножается, но не превышает 100%.'],
            ['key' => 'catch', 'title' => 'Ловля', 'expected' => 'Шанс ловли на одинаковом HP выше при активном событии.'],
            ['key' => 'quest_rewards', 'title' => 'Квестовые награды', 'expected' => 'Награда квестов умножается через RewardRepository.'],
        ];
    }

    private function boostKey(string $value): string
    {
        $value = trim($value);
        return $value === 'money' ? 'coins' : $value;
    }

    private function scope(string $value): string
    {
        $value = trim($value);
        return $value !== '' ? $value : 'global';
    }

    private function boostLabel(string $boostKey): string
    {
        return self::BOOST_LABELS[$boostKey] ?? $boostKey;
    }

    private function scopeLabel(string $scope): string
    {
        return self::SCOPE_LABELS[$scope] ?? $scope;
    }

    private function eventStatus(int $startsAt, int $endsAt, int $enabled, int $now): string
    {
        if ($enabled !== 1) {
            return 'выключен';
        }
        if ($startsAt > 0 && $startsAt > $now) {
            return 'запланирован';
        }
        if ($endsAt > 0 && $endsAt < $now) {
            return 'завершён';
        }

        return 'активен';
    }

    private function tableExists(string $table): bool
    {
        static $cache = [];
        if (array_key_exists($table, $cache)) {
            return $cache[$table];
        }

        $stmt = $this->db->query('SHOW TABLES LIKE ' . $this->db->quote($table));
        $cache[$table] = (bool) $stmt->fetchColumn();
        return $cache[$table];
    }
}
