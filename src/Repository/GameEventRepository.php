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
        $activeEvents = $this->globalEvents('active');
        $this->notifyActiveEventsForUser($userId, $activeEvents);

        return [
            'serverTime' => time(),
            'activeEvents' => $activeEvents,
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
        $activeEvents = $this->globalEvents('active');
        $this->notifyActiveEventsForUser($userId, $activeEvents);

        return [
            'serverTime' => time(),
            'activeEvents' => $activeEvents,
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

    /**
     * @param list<array<string,mixed>> $events
     */
    private function notifyActiveEventsForUser(int $userId, array $events): void
    {
        if ($userId <= 0 || $events === [] || !$this->settingBool('notifications.event_enabled', true)) {
            return;
        }
        if (!$this->tableExists('game_event_notification_receipts')) {
            return;
        }

        foreach ($events as $event) {
            $eventId = (int) ($event['id'] ?? 0);
            if ($eventId <= 0 || $this->eventReceiptExists($userId, $eventId)) {
                continue;
            }

            $title = (string) ($event['title'] ?? 'Игровое событие');
            $boost = (string) ($event['boost_label'] ?? $event['boost_key'] ?? '');
            $multiplier = (float) ($event['multiplier'] ?? 1);
            $remaining = (int) ($event['remaining_seconds'] ?? 0);
            $message = trim(sprintf(
                'Активно событие "%s"%s%s.',
                $title,
                $boost !== '' ? ': ' . $boost . ' x' . rtrim(rtrim(number_format($multiplier, 2, '.', ''), '0'), '.') : '',
                $remaining > 0 ? ' до ' . date('d.m.Y H:i', time() + $remaining) : ''
            ));

            $this->rewards->notify($userId, 'Игровое событие активно', $message, 'event', [
                'source' => 'Система',
                'source_type' => 'event',
                'source_id' => (string) $eventId,
                'event' => $event,
                'mailbox' => $this->settingBool('notifications.event_mailbox_enabled', false),
            ]);
            $this->markEventReceipt($userId, $eventId);
        }
    }

    private function eventReceiptExists(int $userId, int $eventId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM game_event_notification_receipts WHERE user_id = :user AND event_id = :event LIMIT 1'
        );
        $stmt->execute(['user' => $userId, 'event' => $eventId]);
        return (bool) $stmt->fetchColumn();
    }

    private function markEventReceipt(int $userId, int $eventId): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO game_event_notification_receipts (event_id, user_id, notified_at, status)
             VALUES (:event, :user, :time, "sent")
             ON DUPLICATE KEY UPDATE notified_at = notified_at'
        );
        $stmt->execute(['event' => $eventId, 'user' => $userId, 'time' => time()]);
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
}
