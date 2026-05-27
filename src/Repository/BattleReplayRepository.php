<?php
declare(strict_types=1);

namespace Pokemon8\Repository;

use PDO;
use Throwable;

final class BattleReplayRepository
{
    /** @var array<string,bool> */
    private array $tableCache = [];

    public function __construct(private PDO $db)
    {
    }

    public function recordBattle(array $battle, array $meta = []): void
    {
        if (!$this->ready()) {
            return;
        }

        $battleId = (int) ($battle['id'] ?? 0);
        if ($battleId <= 0) {
            return;
        }

        $now = time();
        $startedAt = (int) ($battle['time'] ?? $battle['times'] ?? $meta['started_at'] ?? $now);
        $winner = (int) ($battle['pobeda'] ?? 0);
        $status = $winner !== 0 ? 'finished' : 'active';
        $finishedAt = $winner !== 0 ? $now : 0;
        $payload = $this->json([
            'battle' => $this->compactBattle($battle),
            'meta' => $meta,
        ]);

        try {
            $stmt = $this->db->prepare(
                'INSERT INTO battle_replays
                    (battle_id, battle_type, user_1, user_2, winner_id, status, rounds, started_at, finished_at, meta_json, created_at, updated_at)
                 VALUES
                    (:battle_id, :battle_type, :user_1, :user_2, :winner_id, :status, :rounds, :started_at, :finished_at, :meta_json, :created_at, :updated_at)
                 ON DUPLICATE KEY UPDATE
                    battle_type = VALUES(battle_type),
                    user_1 = VALUES(user_1),
                    user_2 = VALUES(user_2),
                    winner_id = IF(VALUES(winner_id) <> 0, VALUES(winner_id), winner_id),
                    status = IF(VALUES(status) = "finished", "finished", status),
                    rounds = GREATEST(rounds, VALUES(rounds)),
                    started_at = IF(started_at = 0, VALUES(started_at), started_at),
                    finished_at = IF(VALUES(finished_at) <> 0, VALUES(finished_at), finished_at),
                    meta_json = VALUES(meta_json),
                    updated_at = VALUES(updated_at)'
            );
            $stmt->execute([
                'battle_id' => $battleId,
                'battle_type' => (string) ($battle['batl_tip'] ?? $meta['mode'] ?? ''),
                'user_1' => (int) ($battle['user_1'] ?? 0),
                'user_2' => (int) ($battle['user_2'] ?? 0),
                'winner_id' => $winner,
                'status' => $status,
                'rounds' => (int) ($battle['raund'] ?? 0),
                'started_at' => $startedAt,
                'finished_at' => $finishedAt,
                'meta_json' => $payload,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } catch (Throwable $e) {
            error_log('[battle_replay] recordBattle failed: ' . $e->getMessage());
        }
    }

    public function recordRoundLog(int $battleId, int $round, string $message, int $logId = 0): void
    {
        $eventKey = $logId > 0 ? 'log:' . $logId : 'log:' . sha1($round . ':' . $message);
        $this->recordEvent($battleId, $round, 'round_log', [
            'message' => $message,
            'battle_log_id' => $logId,
        ], [
            'event_key' => $eventKey,
        ]);
    }

    public function recordSnapshot(int $battleId, int $round, string $label, array $battle, ?array $player, ?array $enemy, array $environment = [], array $context = []): void
    {
        $this->recordBattle($battle, ['snapshot_label' => $label] + $context);
        $this->recordEvent($battleId, $round, 'snapshot', [
            'label' => $label,
            'battle' => $this->compactBattle($battle),
            'player' => $this->compactPokemon($player),
            'enemy' => $this->compactPokemon($enemy),
            'environment' => $environment,
            'context' => $context,
        ], [
            'event_key' => 'snapshot:' . $label . ':' . $round . ':' . (int) ($context['viewer_id'] ?? 0),
        ]);
    }

    public function recordAction(int $battleId, int $round, string $action, int $actorUserId, array $payload = []): void
    {
        $this->recordEvent($battleId, $round, 'action', [
            'action' => $action,
            'actor_user_id' => $actorUserId,
            'payload' => $payload,
        ], [
            'actor_key' => 'user:' . $actorUserId,
            'event_key' => 'action:' . $round . ':' . $actorUserId . ':' . $action . ':' . sha1($this->json($payload)),
        ]);
    }

    public function recordRandomRoll(int $battleId, int $round, string $label, int $roll, int $min, int $max, ?int $threshold = null, ?bool $success = null, array $context = []): void
    {
        $this->recordEvent($battleId, $round, 'random_roll', [
            'label' => $label,
            'roll' => $roll,
            'min' => $min,
            'max' => $max,
            'threshold' => $threshold,
            'success' => $success,
            'context' => $context,
        ], [
            'actor_key' => (string) ($context['actor_key'] ?? ''),
            'target_key' => (string) ($context['target_key'] ?? ''),
            'move_id' => (int) ($context['move_id'] ?? 0),
            'move_name' => (string) ($context['move_name'] ?? ''),
        ]);
    }

    public function recordDamage(int $battleId, int $round, array $data): void
    {
        $this->recordEvent($battleId, $round, 'damage', $data, [
            'actor_key' => (string) ($data['actor_key'] ?? ''),
            'target_key' => (string) ($data['target_key'] ?? ''),
            'move_id' => (int) ($data['move_id'] ?? 0),
            'move_name' => (string) ($data['move_name'] ?? ''),
        ]);
    }

    public function markFinished(int $battleId, int $winnerId, array $battle = []): void
    {
        if (!$this->ready() || $battleId <= 0) {
            return;
        }

        $now = time();
        try {
            $this->db->prepare(
                'INSERT INTO battle_replays
                    (battle_id, battle_type, user_1, user_2, winner_id, status, rounds, started_at, finished_at, meta_json, created_at, updated_at)
                 VALUES
                    (:battle_id, :battle_type, :user_1, :user_2, :winner_id, "finished", :rounds, :started_at, :finished_at, :meta_json, :created_at, :updated_at)
                 ON DUPLICATE KEY UPDATE
                    winner_id = VALUES(winner_id),
                    status = "finished",
                    rounds = GREATEST(rounds, VALUES(rounds)),
                    finished_at = VALUES(finished_at),
                    updated_at = VALUES(updated_at)'
            )->execute([
                'battle_id' => $battleId,
                'battle_type' => (string) ($battle['batl_tip'] ?? ''),
                'user_1' => (int) ($battle['user_1'] ?? 0),
                'user_2' => (int) ($battle['user_2'] ?? 0),
                'winner_id' => $winnerId,
                'rounds' => (int) ($battle['raund'] ?? 0),
                'started_at' => (int) ($battle['time'] ?? $battle['times'] ?? $now),
                'finished_at' => $now,
                'meta_json' => $this->json(['battle' => $this->compactBattle($battle)]),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $this->recordEvent($battleId, (int) ($battle['raund'] ?? 0), 'finish', [
                'winner_id' => $winnerId,
                'battle' => $this->compactBattle($battle),
            ], [
                'event_key' => 'finish:' . $winnerId,
            ]);
        } catch (Throwable $e) {
            error_log('[battle_replay] markFinished failed: ' . $e->getMessage());
        }
    }

    public function replayForBattle(int $battleId, int $viewerId, bool $admin = false): array
    {
        if (!$this->ready()) {
            return ['ok' => false, 'message' => 'Battle replay tables are not installed.'];
        }
        if ($battleId <= 0) {
            return ['ok' => false, 'message' => 'battle_id не задан.'];
        }
        if (!$admin && !$this->canView($battleId, $viewerId)) {
            return ['ok' => false, 'error' => 'forbidden', 'message' => 'Нет доступа к повтору боя.'];
        }

        $battle = $this->findReplayRow($battleId);
        if ($battle === null) {
            return ['ok' => false, 'message' => 'Повтор боя ещё не записан.'];
        }

        $events = $this->eventsForBattle($battleId);
        return [
            'ok' => true,
            'replay' => [
                'battle' => $battle,
                'events' => $events,
                'rounds' => $this->groupByRound($events),
                'summary' => $this->summary($events),
            ],
        ];
    }

    public function adminDashboard(): array
    {
        if (!$this->ready()) {
            return ['installed' => false];
        }

        $now = time();
        return [
            'installed' => true,
            'replays' => $this->scalarInt('SELECT COUNT(*) FROM battle_replays'),
            'active' => $this->scalarInt('SELECT COUNT(*) FROM battle_replays WHERE status = "active"'),
            'finished_today' => $this->scalarInt('SELECT COUNT(*) FROM battle_replays WHERE status = "finished" AND finished_at >= :since', ['since' => $now - 86400]),
            'events_today' => $this->scalarInt('SELECT COUNT(*) FROM battle_replay_events WHERE created_at >= :since', ['since' => $now - 86400]),
            'damage_events' => $this->scalarInt('SELECT COUNT(*) FROM battle_replay_events WHERE event_type = "damage"'),
            'random_rolls' => $this->scalarInt('SELECT COUNT(*) FROM battle_replay_events WHERE event_type = "random_roll"'),
        ];
    }

    public function adminList(string $search = '', int $limit = 80, int $offset = 0): array
    {
        if (!$this->ready()) {
            return ['rows' => [], 'total' => 0];
        }

        $where = [];
        $params = [];
        $search = trim($search);
        if ($search !== '') {
            if (ctype_digit($search)) {
                $where[] = 'br.battle_id = :battle_id';
                $params['battle_id'] = (int) $search;
            } else {
                $where[] = '(u1.login LIKE :q OR u2.login LIKE :q OR br.battle_type LIKE :q)';
                $params['q'] = '%' . $search . '%';
            }
        }
        $sqlWhere = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $totalStmt = $this->db->prepare("SELECT COUNT(*) FROM battle_replays br LEFT JOIN users u1 ON u1.id = br.user_1 LEFT JOIN users u2 ON u2.id = br.user_2 {$sqlWhere}");
        $totalStmt->execute($params);
        $total = (int) ($totalStmt->fetchColumn() ?: 0);

        $stmt = $this->db->prepare(
            "SELECT br.*, u1.login AS user_1_login, u2.login AS user_2_login,
                    (SELECT COUNT(*) FROM battle_replay_events e WHERE e.battle_id = br.battle_id) AS event_count,
                    (SELECT COUNT(*) FROM battle_replay_events e WHERE e.battle_id = br.battle_id AND e.event_type = 'damage') AS damage_count,
                    (SELECT COUNT(*) FROM battle_replay_events e WHERE e.battle_id = br.battle_id AND e.event_type = 'random_roll') AS roll_count
               FROM battle_replays br
          LEFT JOIN users u1 ON u1.id = br.user_1
          LEFT JOIN users u2 ON u2.id = br.user_2
              {$sqlWhere}
              ORDER BY br.updated_at DESC, br.battle_id DESC
              LIMIT :limit OFFSET :offset"
        );
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', max(1, min(200, $limit)), PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $offset), PDO::PARAM_INT);
        $stmt->execute();

        $rows = [];
        foreach ($stmt->fetchAll() ?: [] as $row) {
            $rows[] = $this->formatReplayRow($row);
        }
        return ['rows' => $rows, 'total' => $total];
    }

    private function recordEvent(int $battleId, int $round, string $type, array $data, array $context = []): void
    {
        if (!$this->ready() || $battleId <= 0) {
            return;
        }

        $now = time();
        try {
            $this->db->prepare(
                'INSERT INTO battle_replay_events
                    (battle_id, event_key, round_no, event_type, actor_key, target_key, move_id, move_name, data_json, created_at)
                 VALUES
                    (:battle_id, :event_key, :round_no, :event_type, :actor_key, :target_key, :move_id, :move_name, :data_json, :created_at)
                 ON DUPLICATE KEY UPDATE
                    round_no = VALUES(round_no),
                    event_type = VALUES(event_type),
                    actor_key = VALUES(actor_key),
                    target_key = VALUES(target_key),
                    move_id = VALUES(move_id),
                    move_name = VALUES(move_name),
                    data_json = VALUES(data_json)'
            )->execute([
                'battle_id' => $battleId,
                'event_key' => $context['event_key'] ?? null,
                'round_no' => max(0, $round),
                'event_type' => $type,
                'actor_key' => (string) ($context['actor_key'] ?? ''),
                'target_key' => (string) ($context['target_key'] ?? ''),
                'move_id' => (int) ($context['move_id'] ?? 0),
                'move_name' => (string) ($context['move_name'] ?? ''),
                'data_json' => $this->json($data),
                'created_at' => $now,
            ]);
        } catch (Throwable $e) {
            error_log('[battle_replay] recordEvent failed: ' . $e->getMessage());
        }
    }

    private function canView(int $battleId, int $viewerId): bool
    {
        if ($viewerId <= 0) {
            return false;
        }

        $row = $this->findReplayRow($battleId);
        if ($row !== null && ((int) ($row['user_1'] ?? 0) === $viewerId || (int) ($row['user_2'] ?? 0) === $viewerId)) {
            return true;
        }

        if ($this->tableExists('battle_history_archive')) {
            $stmt = $this->db->prepare('SELECT 1 FROM battle_history_archive WHERE battle_id = :battle AND user_id = :user LIMIT 1');
            $stmt->execute(['battle' => $battleId, 'user' => $viewerId]);
            if ($stmt->fetchColumn() !== false) {
                return true;
            }
        }

        $stmt = $this->db->prepare('SELECT 1 FROM battles WHERE id = :battle AND (user_1 = :user_a OR user_2 = :user_b) LIMIT 1');
        $stmt->execute(['battle' => $battleId, 'user_a' => $viewerId, 'user_b' => $viewerId]);
        return $stmt->fetchColumn() !== false;
    }

    private function findReplayRow(int $battleId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT br.*, u1.login AS user_1_login, u2.login AS user_2_login
               FROM battle_replays br
          LEFT JOIN users u1 ON u1.id = br.user_1
          LEFT JOIN users u2 ON u2.id = br.user_2
              WHERE br.battle_id = :battle
              LIMIT 1'
        );
        $stmt->execute(['battle' => $battleId]);
        $row = $stmt->fetch();
        return $row ? $this->formatReplayRow($row) : null;
    }

    /** @return list<array<string,mixed>> */
    private function eventsForBattle(int $battleId): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, battle_id, round_no, event_type, actor_key, target_key, move_id, move_name, data_json, created_at
               FROM battle_replay_events
              WHERE battle_id = :battle
              ORDER BY round_no ASC, id ASC'
        );
        $stmt->execute(['battle' => $battleId]);
        $rows = [];
        foreach ($stmt->fetchAll() ?: [] as $row) {
            $data = json_decode((string) ($row['data_json'] ?? ''), true);
            $rows[] = [
                'id' => (int) ($row['id'] ?? 0),
                'battleId' => (int) ($row['battle_id'] ?? 0),
                'round' => (int) ($row['round_no'] ?? 0),
                'type' => (string) ($row['event_type'] ?? ''),
                'actorKey' => (string) ($row['actor_key'] ?? ''),
                'targetKey' => (string) ($row['target_key'] ?? ''),
                'moveId' => (int) ($row['move_id'] ?? 0),
                'moveName' => (string) ($row['move_name'] ?? ''),
                'data' => is_array($data) ? $data : [],
                'createdAt' => (int) ($row['created_at'] ?? 0),
            ];
        }
        return $rows;
    }

    private function groupByRound(array $events): array
    {
        $rounds = [];
        foreach ($events as $event) {
            $round = (int) ($event['round'] ?? 0);
            $key = (string) $round;
            if (!isset($rounds[$key])) {
                $rounds[$key] = [
                    'round' => $round,
                    'logs' => [],
                    'snapshots' => [],
                    'randomRolls' => [],
                    'damage' => [],
                    'events' => [],
                ];
            }
            $rounds[$key]['events'][] = $event;
            if (($event['type'] ?? '') === 'round_log') {
                $rounds[$key]['logs'][] = $event['data']['message'] ?? '';
            } elseif (($event['type'] ?? '') === 'snapshot') {
                $rounds[$key]['snapshots'][] = $event['data'] ?? [];
            } elseif (($event['type'] ?? '') === 'random_roll') {
                $rounds[$key]['randomRolls'][] = $event['data'] ?? [];
            } elseif (($event['type'] ?? '') === 'damage') {
                $rounds[$key]['damage'][] = $event['data'] ?? [];
            }
        }
        return array_values($rounds);
    }

    private function summary(array $events): array
    {
        $types = [];
        foreach ($events as $event) {
            $type = (string) ($event['type'] ?? '');
            $types[$type] = ($types[$type] ?? 0) + 1;
        }
        return [
            'events' => count($events),
            'byType' => $types,
        ];
    }

    private function formatReplayRow(array $row): array
    {
        $meta = json_decode((string) ($row['meta_json'] ?? ''), true);
        return [
            'battle_id' => (int) ($row['battle_id'] ?? 0),
            'battle_type' => (string) ($row['battle_type'] ?? ''),
            'status' => (string) ($row['status'] ?? ''),
            'rounds' => (int) ($row['rounds'] ?? 0),
            'winner_id' => (int) ($row['winner_id'] ?? 0),
            'user_1' => (int) ($row['user_1'] ?? 0),
            'user_2' => (int) ($row['user_2'] ?? 0),
            'user_1_login' => (string) ($row['user_1_login'] ?? ''),
            'user_2_login' => (string) ($row['user_2_login'] ?? ''),
            'started_at' => (int) ($row['started_at'] ?? 0),
            'finished_at' => (int) ($row['finished_at'] ?? 0),
            'updated_at' => (int) ($row['updated_at'] ?? 0),
            'event_count' => (int) ($row['event_count'] ?? 0),
            'damage_count' => (int) ($row['damage_count'] ?? 0),
            'roll_count' => (int) ($row['roll_count'] ?? 0),
            'meta' => is_array($meta) ? $meta : [],
        ];
    }

    private function compactBattle(array $battle): array
    {
        $keys = ['id', 'batl_tip', 'user_1', 'user_2', 'poke_1', 'poke_2', 'attac_1', 'attac_2', 'raund', 'hod_user_id', 'pobeda', 'time', 'times'];
        return array_intersect_key($battle, array_flip($keys));
    }

    private function compactPokemon(?array $pokemon): ?array
    {
        if ($pokemon === null) {
            return null;
        }
        $keys = [
            'id', 'basenum', 'names', 'battle_pokemon', 'users', 'lvl', 'hp_my', 'hp_max',
            'atk', 'def', 'satk', 'sdef', 'speed', 'Element', 'SubElement',
            'gender', 'sparkaNumber', 'held_item_id', 'held_item_name', 'ability_key',
        ];
        return array_intersect_key($pokemon, array_flip($keys));
    }

    private function scalarInt(string $sql, array $params = []): int
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    private function ready(): bool
    {
        return $this->tableExists('battle_replays') && $this->tableExists('battle_replay_events');
    }

    private function tableExists(string $table): bool
    {
        if (array_key_exists($table, $this->tableCache)) {
            return $this->tableCache[$table];
        }
        try {
            $stmt = $this->db->prepare(
                'SELECT 1
                   FROM information_schema.TABLES
                  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table
                  LIMIT 1'
            );
            $stmt->execute(['table' => $table]);
            return $this->tableCache[$table] = $stmt->fetchColumn() !== false;
        } catch (Throwable) {
            return $this->tableCache[$table] = false;
        }
    }

    private function json(array $payload): string
    {
        return json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
    }
}
