<?php
declare(strict_types=1);

namespace Pokemon8\Repository;

use PDO;
use Pokemon8\Support\ClientIp;

final class BugReportRepository
{
    private const STATUSES = ['open', 'investigating', 'fixed', 'closed', 'duplicate'];
    private const SEVERITIES = ['bug', 'critical', 'visual', 'balance', 'ux', 'other'];

    public function __construct(private PDO $db)
    {
    }

    public function create(int $userId, array $payload, array $server = []): array
    {
        if ($userId <= 0) {
            return ['ok' => false, 'message' => 'Нужно войти в игру.'];
        }
        if (!$this->tableExists('bug_reports')) {
            return ['ok' => false, 'message' => 'Bug Reporter ещё не установлен в БД.'];
        }
        if ($this->setting('bug_reporter.enabled', '1') !== '1') {
            return ['ok' => false, 'message' => 'Bug Reporter временно выключен.'];
        }

        $user = $this->userSnapshot($userId);
        $login = (string) ($user['login'] ?? ('#' . $userId));
        $clientState = $this->jsonPayload($payload['client_state_json'] ?? $payload['state_json'] ?? '', 80000);
        $clientLogs = $this->jsonPayload($payload['client_logs_json'] ?? '', 30000);
        $battleId = $this->detectBattleId($payload, $clientState, $user);
        $serverBattle = $battleId > 0 ? $this->battleSnapshot($battleId) : [];
        $battleState = [
            'client' => $this->arrayFromJson($payload['battle_state_json'] ?? ''),
            'server' => $serverBattle,
            'replay' => $battleId > 0 ? $this->battleReplaySnapshot($battleId) : [],
        ];
        $battleType = mb_substr((string) ($payload['battle_type'] ?? $serverBattle['batl_tip'] ?? $serverBattle['battle_type'] ?? ''), 0, 24);
        $locationId = max(0, (int) ($payload['location_id'] ?? $clientState['locationId'] ?? $clientState['state']['locationId'] ?? $user['buildmy'] ?? 0));
        $serverState = [
            'user' => $user,
            'location' => $locationId > 0 ? $this->locationSnapshot($locationId) : [],
            'request' => [
                'ip' => ClientIp::fromServer($server),
                'user_agent' => mb_substr((string) ($server['HTTP_USER_AGENT'] ?? ''), 0, 500),
                'referer' => mb_substr((string) ($server['HTTP_REFERER'] ?? ''), 0, 500),
            ],
        ];

        $description = mb_substr(trim((string) ($payload['description'] ?? '')), 0, 4000);
        $title = mb_substr(trim((string) ($payload['title'] ?? '')), 0, 190);
        if ($title === '') {
            $title = mb_substr($description !== '' ? $description : 'Bug report', 0, 120);
        }
        $severity = in_array((string) ($payload['severity'] ?? ''), self::SEVERITIES, true)
            ? (string) $payload['severity']
            : 'bug';
        $pageUrl = mb_substr(trim((string) ($payload['page_url'] ?? $payload['url'] ?? '')), 0, 255);
        $route = mb_substr(trim((string) ($payload['route'] ?? parse_url($pageUrl, PHP_URL_PATH) ?: '')), 0, 160);
        $now = time();
        $attachments = [
            'attach_state' => true,
            'attach_battle_id' => $battleId,
            'attach_client_logs' => $clientLogs !== [],
            'attach_server_logs' => $this->setting('bug_reporter.attach_server_logs', '1') === '1',
            'viewport' => $this->arrayFromJson($payload['viewport_json'] ?? ''),
            'meta' => $this->arrayFromJson($payload['meta_json'] ?? ''),
        ];

        $serverLogs = $attachments['attach_server_logs']
            ? ['lines' => $this->recentServerLogs($this->settingInt('bug_reporter.server_log_lines', 20))]
            : ['lines' => []];

        $stmt = $this->db->prepare(
            'INSERT INTO bug_reports
                (user_id, user_login, title, description, severity, status, page_url, route, location_id,
                 battle_id, battle_type, user_state_json, game_state_json, battle_state_json, client_logs_json,
                 server_logs_json, attachments_json, created_at, updated_at)
             VALUES
                (:user_id, :user_login, :title, :description, :severity, "open", :page_url, :route, :location_id,
                 :battle_id, :battle_type, :user_state_json, :game_state_json, :battle_state_json, :client_logs_json,
                 :server_logs_json, :attachments_json, :created_at, :updated_at)'
        );
        $stmt->execute([
            'user_id' => $userId,
            'user_login' => $login,
            'title' => $title,
            'description' => $description,
            'severity' => $severity,
            'page_url' => $pageUrl,
            'route' => $route,
            'location_id' => $locationId,
            'battle_id' => $battleId,
            'battle_type' => $battleType,
            'user_state_json' => $this->jsonEncode($serverState),
            'game_state_json' => $this->jsonEncode($clientState),
            'battle_state_json' => $this->jsonEncode($battleState),
            'client_logs_json' => $this->jsonEncode($clientLogs),
            'server_logs_json' => $this->jsonEncode($serverLogs),
            'attachments_json' => $this->jsonEncode($attachments),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $reportId = (int) $this->db->lastInsertId();
        $this->event($reportId, $userId, 'created', [
            'severity' => $severity,
            'battle_id' => $battleId,
            'route' => $route,
        ]);
        $this->notifyUser($userId, 'Bug report #' . $reportId, 'Баг-репорт отправлен. Спасибо, это попадёт в GM Center.', [
            'bug_report_id' => $reportId,
            'source_type' => 'bug_report',
            'source_id' => (string) $reportId,
        ]);

        return [
            'ok' => true,
            'reportId' => $reportId,
            'message' => 'Bug report #' . $reportId . ' отправлен.',
        ];
    }

    public function adminList(string $query = '', int $limit = 80, int $offset = 0, array $filters = []): array
    {
        if (!$this->tableExists('bug_reports')) {
            return ['rows' => [], 'total' => 0];
        }

        [$where, $params] = $this->adminWhere($query, $filters);
        $limit = max(10, min(200, $limit));
        $offset = max(0, $offset);
        $sql = 'FROM bug_reports br
           LEFT JOIN users u ON u.id = br.user_id
           LEFT JOIN users a ON a.id = br.resolved_by
              WHERE ' . implode(' AND ', $where);
        $totalStmt = $this->db->prepare('SELECT COUNT(*) ' . $sql);
        $totalStmt->execute($params);
        $total = (int) ($totalStmt->fetchColumn() ?: 0);

        $rowsStmt = $this->db->prepare(
            'SELECT br.*, COALESCE(u.login, br.user_login) AS reporter_login, a.login AS resolved_login
               ' . $sql . '
              ORDER BY br.created_at DESC, br.id DESC
              LIMIT ' . $limit . ' OFFSET ' . $offset
        );
        $rowsStmt->execute($params);
        $rows = array_map(
            fn (array $row): array => $this->formatReport($row),
            $rowsStmt->fetchAll(PDO::FETCH_ASSOC) ?: []
        );

        return ['rows' => $rows, 'total' => $total];
    }

    public function adminSummary(): array
    {
        if (!$this->tableExists('bug_reports')) {
            return [
                'ready' => false,
                'open' => 0,
                'today' => 0,
                'criticalOpen' => 0,
                'withBattle' => 0,
                'lastCreatedAt' => 0,
                'recent' => [],
            ];
        }

        $todayStart = strtotime('today') ?: (time() - 86400);
        $recent = $this->adminList('', 8, 0, [])['rows'] ?? [];
        return [
            'ready' => true,
            'open' => $this->count('status IN ("open", "investigating")'),
            'today' => $this->count('created_at >= ' . (int) $todayStart),
            'criticalOpen' => $this->count('severity = "critical" AND status IN ("open", "investigating")'),
            'withBattle' => $this->count('battle_id > 0 AND status IN ("open", "investigating")'),
            'lastCreatedAt' => (int) ($this->db->query('SELECT COALESCE(MAX(created_at), 0) FROM bug_reports')->fetchColumn() ?: 0),
            'recent' => $recent,
        ];
    }

    public function updateStatus(int $adminId, array $payload): array
    {
        if ($adminId <= 0) {
            return ['ok' => false, 'message' => 'Нет доступа.'];
        }
        if (!$this->tableExists('bug_reports')) {
            return ['ok' => false, 'message' => 'Bug Reporter ещё не установлен в БД.'];
        }

        $reportId = max(0, (int) ($payload['report_id'] ?? $payload['id'] ?? 0));
        $status = (string) ($payload['status'] ?? '');
        if ($reportId <= 0 || !in_array($status, self::STATUSES, true)) {
            return ['ok' => false, 'message' => 'Некорректный статус баг-репорта.'];
        }
        $note = mb_substr(trim((string) ($payload['note'] ?? '')), 0, 1000);
        $resolvedAt = in_array($status, ['fixed', 'closed', 'duplicate'], true) ? time() : 0;
        $now = time();
        $stmt = $this->db->prepare(
            'UPDATE bug_reports
                SET status = :status, updated_at = :updated_at, resolved_at = :resolved_at, resolved_by = :resolved_by
              WHERE id = :id'
        );
        $stmt->execute([
            'status' => $status,
            'updated_at' => $now,
            'resolved_at' => $resolvedAt,
            'resolved_by' => $adminId,
            'id' => $reportId,
        ]);
        if ($stmt->rowCount() <= 0) {
            return ['ok' => false, 'message' => 'Баг-репорт не найден.'];
        }

        $this->event($reportId, $adminId, 'status.update', ['status' => $status, 'note' => $note]);
        $report = $this->reportById($reportId);
        $userId = (int) ($report['user_id'] ?? 0);
        if ($userId > 0 && in_array($status, ['fixed', 'closed'], true)) {
            $this->notifyUser($userId, 'Bug report #' . $reportId, 'Статус баг-репорта обновлён: ' . $status . '.', [
                'bug_report_id' => $reportId,
                'status' => $status,
            ]);
        }

        return [
            'ok' => true,
            'message' => 'Статус баг-репорта обновлён.',
            'report' => $this->formatReport($report ?: []),
        ];
    }

    private function adminWhere(string $query, array $filters): array
    {
        $where = ['1=1'];
        $params = [];
        $query = trim($query !== '' ? $query : (string) ($filters['q'] ?? ''));
        if ($query !== '') {
            $where[] = '(br.title LIKE :q_title OR br.description LIKE :q_description OR br.user_login LIKE :q_user OR br.route LIKE :q_route OR br.id = :q_id)';
            $params['q_title'] = '%' . $query . '%';
            $params['q_description'] = '%' . $query . '%';
            $params['q_user'] = '%' . $query . '%';
            $params['q_route'] = '%' . $query . '%';
            $params['q_id'] = ctype_digit($query) ? (int) $query : -1;
        }
        $status = (string) ($filters['status'] ?? '');
        if ($status !== '' && in_array($status, self::STATUSES, true)) {
            $where[] = 'br.status = :status';
            $params['status'] = $status;
        }
        $severity = (string) ($filters['severity'] ?? '');
        if ($severity !== '' && in_array($severity, self::SEVERITIES, true)) {
            $where[] = 'br.severity = :severity';
            $params['severity'] = $severity;
        }
        $user = trim((string) ($filters['user'] ?? ''));
        if ($user !== '') {
            $where[] = '(br.user_login LIKE :user OR br.user_id = :user_id)';
            $params['user'] = '%' . $user . '%';
            $params['user_id'] = ctype_digit($user) ? (int) $user : -1;
        }
        $battleId = max(0, (int) ($filters['battle_id'] ?? 0));
        if ($battleId > 0) {
            $where[] = 'br.battle_id = :battle_id';
            $params['battle_id'] = $battleId;
        }
        $from = $this->dateToUnix((string) ($filters['date_from'] ?? ''), false);
        if ($from > 0) {
            $where[] = 'br.created_at >= :date_from';
            $params['date_from'] = $from;
        }
        $to = $this->dateToUnix((string) ($filters['date_to'] ?? ''), true);
        if ($to > 0) {
            $where[] = 'br.created_at <= :date_to';
            $params['date_to'] = $to;
        }

        return [$where, $params];
    }

    private function detectBattleId(array $payload, array $clientState, array $user): int
    {
        foreach ([
            $payload['battle_id'] ?? null,
            $payload['battleId'] ?? null,
            $clientState['battleId'] ?? null,
            $clientState['battleState']['battleId'] ?? null,
            $user['battleid'] ?? null,
        ] as $value) {
            $id = (int) $value;
            if ($id > 0) {
                return $id;
            }
        }
        return 0;
    }

    private function userSnapshot(int $userId): array
    {
        if (!$this->tableExists('users')) {
            return ['id' => $userId];
        }
        $stmt = $this->db->prepare(
            'SELECT id, login, groups, activation, buildmy, battleid, pve, pvp, trade, online, onlinetime, karma_score
               FROM users
              WHERE id = :id
              LIMIT 1'
        );
        $stmt->execute(['id' => $userId]);
        return $this->trimSnapshot($stmt->fetch(PDO::FETCH_ASSOC) ?: ['id' => $userId]);
    }

    private function locationSnapshot(int $locationId): array
    {
        if (!$this->tableExists('build')) {
            return [];
        }
        $stmt = $this->db->prepare('SELECT * FROM build WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $locationId]);
        return $this->trimSnapshot($stmt->fetch(PDO::FETCH_ASSOC) ?: []);
    }

    private function battleSnapshot(int $battleId): array
    {
        if (!$this->tableExists('battles')) {
            return [];
        }
        $stmt = $this->db->prepare('SELECT * FROM battles WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $battleId]);
        return $this->trimSnapshot($stmt->fetch(PDO::FETCH_ASSOC) ?: []);
    }

    private function battleReplaySnapshot(int $battleId): array
    {
        if (!$this->tableExists('battle_replays')) {
            return [];
        }
        $stmt = $this->db->prepare('SELECT * FROM battle_replays WHERE battle_id = :id LIMIT 1');
        $stmt->execute(['id' => $battleId]);
        return $this->trimSnapshot($stmt->fetch(PDO::FETCH_ASSOC) ?: []);
    }

    private function reportById(int $reportId): array
    {
        $stmt = $this->db->prepare(
            'SELECT br.*, COALESCE(u.login, br.user_login) AS reporter_login, a.login AS resolved_login
               FROM bug_reports br
          LEFT JOIN users u ON u.id = br.user_id
          LEFT JOIN users a ON a.id = br.resolved_by
              WHERE br.id = :id
              LIMIT 1'
        );
        $stmt->execute(['id' => $reportId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    private function formatReport(array $row): array
    {
        $row['id'] = (int) ($row['id'] ?? 0);
        $row['user_id'] = (int) ($row['user_id'] ?? 0);
        $row['location_id'] = (int) ($row['location_id'] ?? 0);
        $row['battle_id'] = (int) ($row['battle_id'] ?? 0);
        $row['created_at'] = (int) ($row['created_at'] ?? 0);
        $row['updated_at'] = (int) ($row['updated_at'] ?? 0);
        $row['resolved_at'] = (int) ($row['resolved_at'] ?? 0);
        $row['created_at_text'] = $row['created_at'] > 0 ? date('Y-m-d H:i:s', $row['created_at']) : '';
        $row['updated_at_text'] = $row['updated_at'] > 0 ? date('Y-m-d H:i:s', $row['updated_at']) : '';
        $row['resolved_at_text'] = $row['resolved_at'] > 0 ? date('Y-m-d H:i:s', $row['resolved_at']) : '';
        $row['reporter_login'] = (string) ($row['reporter_login'] ?? $row['user_login'] ?? '');
        $row['user_state'] = $this->decodeJson((string) ($row['user_state_json'] ?? ''));
        $row['game_state'] = $this->decodeJson((string) ($row['game_state_json'] ?? ''));
        $row['battle_state'] = $this->decodeJson((string) ($row['battle_state_json'] ?? ''));
        $row['client_logs'] = $this->decodeJson((string) ($row['client_logs_json'] ?? ''));
        $row['server_logs'] = $this->decodeJson((string) ($row['server_logs_json'] ?? ''));
        $row['attachments'] = $this->decodeJson((string) ($row['attachments_json'] ?? ''));
        unset(
            $row['user_state_json'],
            $row['game_state_json'],
            $row['battle_state_json'],
            $row['client_logs_json'],
            $row['server_logs_json'],
            $row['attachments_json']
        );
        return $row;
    }

    private function event(int $reportId, int $actorId, string $action, array $data): void
    {
        if (!$this->tableExists('bug_report_events')) {
            return;
        }
        $stmt = $this->db->prepare(
            'INSERT INTO bug_report_events (report_id, actor_id, action, data_json, created_at)
             VALUES (:report_id, :actor_id, :action, :data_json, :created_at)'
        );
        $stmt->execute([
            'report_id' => $reportId,
            'actor_id' => $actorId,
            'action' => mb_substr($action, 0, 64),
            'data_json' => $this->jsonEncode($data),
            'created_at' => time(),
        ]);
    }

    private function notifyUser(int $userId, string $title, string $message, array $payload): void
    {
        if ($userId <= 0 || !$this->tableExists('game_notifications')) {
            return;
        }
        $columns = [
            'user_id' => $userId,
            'title' => mb_substr($title, 0, 120),
            'message' => mb_substr($message, 0, 500),
            'variant' => 'info',
            'payload_json' => $this->jsonEncode($payload),
            'source' => 'Bug Reporter',
            'created_at' => time(),
            'read_at' => 0,
        ];
        if ($this->columnExists('game_notifications', 'sender_id')) {
            $columns['sender_id'] = $this->systemUserId();
        }
        if ($this->columnExists('game_notifications', 'source_type')) {
            $columns['source_type'] = 'bug_report';
        }
        if ($this->columnExists('game_notifications', 'source_id')) {
            $columns['source_id'] = (string) ($payload['bug_report_id'] ?? '');
        }

        $names = array_keys($columns);
        $stmt = $this->db->prepare(
            'INSERT INTO game_notifications (' . implode(', ', $names) . ')
             VALUES (' . implode(', ', array_map(static fn (string $name): string => ':' . $name, $names)) . ')'
        );
        $stmt->execute($columns);
    }

    private function systemUserId(): int
    {
        if ($this->tableExists('site_settings')) {
            $stmt = $this->db->prepare('SELECT value FROM site_settings WHERE name = "system.account_id" LIMIT 1');
            $stmt->execute();
            $id = (int) ($stmt->fetchColumn() ?: 0);
            if ($id > 0) {
                return $id;
            }
        }
        if (!$this->tableExists('users')) {
            return 0;
        }
        $stmt = $this->db->prepare('SELECT id FROM users WHERE login = "Система" ORDER BY id ASC LIMIT 1');
        $stmt->execute();
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    private function jsonPayload(mixed $value, int $maxBytes): array
    {
        if (is_array($value)) {
            return $this->trimSnapshot($value);
        }
        $raw = mb_substr(trim((string) $value), 0, $maxBytes);
        if ($raw === '') {
            return [];
        }
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            return $this->trimSnapshot($decoded);
        }
        return ['raw' => $raw, 'decode_error' => json_last_error_msg()];
    }

    private function arrayFromJson(mixed $value): array
    {
        if (is_array($value)) {
            return $this->trimSnapshot($value);
        }
        $decoded = json_decode(trim((string) $value), true);
        return is_array($decoded) ? $this->trimSnapshot($decoded) : [];
    }

    private function trimSnapshot(mixed $value, int $depth = 0): mixed
    {
        if ($depth > 6) {
            return '[depth-limit]';
        }
        if (is_array($value)) {
            $result = [];
            $count = 0;
            foreach ($value as $key => $item) {
                if ($count++ >= 80) {
                    $result['_truncated'] = true;
                    break;
                }
                $result[$key] = $this->trimSnapshot($item, $depth + 1);
            }
            return $result;
        }
        if (is_string($value)) {
            return mb_substr($value, 0, 2000);
        }
        if (is_scalar($value) || $value === null) {
            return $value;
        }
        return (string) $value;
    }

    private function recentServerLogs(int $lines): array
    {
        $path = defined('APP_ROOT') ? APP_ROOT . '/log_php_errors.txt' : dirname(__DIR__, 2) . '/log_php_errors.txt';
        if (!is_file($path) || !is_readable($path)) {
            return [];
        }
        $all = @file($path, FILE_IGNORE_NEW_LINES);
        if (!is_array($all)) {
            return [];
        }
        $tail = array_slice($all, -max(1, min(80, $lines)));
        return array_values(array_map(static fn (string $line): string => mb_substr($line, 0, 1000), $tail));
    }

    private function dateToUnix(string $value, bool $endOfDay): int
    {
        $value = trim($value);
        if ($value === '') {
            return 0;
        }
        $time = strtotime($value . ($endOfDay && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? ' 23:59:59' : ''));
        return $time === false ? 0 : (int) $time;
    }

    private function count(string $where): int
    {
        if (!$this->tableExists('bug_reports')) {
            return 0;
        }
        return (int) ($this->db->query('SELECT COUNT(*) FROM bug_reports WHERE ' . $where)->fetchColumn() ?: 0);
    }

    private function settingInt(string $name, int $default): int
    {
        $value = $this->setting($name, (string) $default);
        return ctype_digit($value) ? max(0, (int) $value) : $default;
    }

    private function setting(string $name, string $default): string
    {
        if (!$this->tableExists('site_settings')) {
            return $default;
        }
        $stmt = $this->db->prepare('SELECT value FROM site_settings WHERE name = :name LIMIT 1');
        $stmt->execute(['name' => $name]);
        $value = $stmt->fetchColumn();
        return $value === false ? $default : (string) $value;
    }

    private function tableExists(string $table): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*)
               FROM information_schema.TABLES
              WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = :table'
        );
        $stmt->execute(['table' => $table]);
        return (int) ($stmt->fetchColumn() ?: 0) > 0;
    }

    private function columnExists(string $table, string $column): bool
    {
        if (!$this->tableExists($table)) {
            return false;
        }
        $stmt = $this->db->prepare(
            'SELECT COUNT(*)
               FROM information_schema.COLUMNS
              WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = :table
                AND COLUMN_NAME = :column'
        );
        $stmt->execute(['table' => $table, 'column' => $column]);
        return (int) ($stmt->fetchColumn() ?: 0) > 0;
    }

    private function decodeJson(string $json): array
    {
        if (trim($json) === '') {
            return [];
        }
        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : ['raw' => mb_substr($json, 0, 4000)];
    }

    private function jsonEncode(array $payload): string
    {
        return json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
    }
}
