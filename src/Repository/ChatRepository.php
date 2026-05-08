<?php
declare(strict_types=1);

namespace Pokemon8\Repository;

use PDO;

final class ChatRepository
{
    private ?bool $hasAuthorId = null;
    private ?bool $idAutoIncrement = null;

    public function __construct(private PDO $db)
    {
    }

    /**
     * @return list<array>
     */
    public function findMessages(int $userId, int $roomId, int $afterId = 0, int $limit = 50): array
    {
        $order = $afterId > 0 ? 'ASC' : 'DESC';
        $hasAuthorId = $this->hasAuthorId();
        $authorIdSelect = $hasAuthorId ? 'c.author_id' : 'COALESCE(u.id, 0)';
        $authorJoin = $hasAuthorId ? 'u.id = c.author_id' : 'u.login = c.author';
        $privateFromCondition = $hasAuthorId ? 'c.author_id = :user_id_from' : 'u.id = :user_id_from';

        $stmt = $this->db->prepare("
            SELECT c.*, $authorIdSelect as resolved_author_id, u.login as author_name, ut.login as to_name
            FROM chats c
            LEFT JOIN users u ON $authorJoin
            LEFT JOIN users ut ON ut.id = c.userto
            WHERE c.id > :after_id
              AND (
                (c.private = 0 AND (c.room = :room OR c.room = 0))
                OR (c.private = 1 AND ($privateFromCondition OR c.userto = :user_id_to))
              )
            ORDER BY c.id $order
            LIMIT :limit
        ");

        $stmt->bindValue(':after_id', $afterId, PDO::PARAM_INT);
        $stmt->bindValue(':room', $roomId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id_from', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id_to', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        if ($afterId <= 0) {
            $rows = array_reverse($rows);
        }

        return $rows;
    }

    public function addMessage(array $data): bool
    {
        $columns = [
            'author'  => ':author',
            'userto'  => ':userto',
            'private' => ':private',
            'text'    => ':text',
            'tipe'    => ':tipe',
            'room'    => ':room',
            'time'    => ':time',
        ];
        $params = [
            'author'  => $data['author'] ?? 'System',
            'userto'  => $data['userto'] ?? 0,
            'private' => $data['private'] ?? 0,
            'text'    => $data['text'],
            'tipe'    => $data['tipe'] ?? 1,
            'room'    => $data['room'] ?? 0,
            'time'    => time(),
        ];

        if ($this->hasAuthorId()) {
            $columns = ['author_id' => ':author_id'] + $columns;
            $params['author_id'] = $data['author_id'] ?? 0;
        }

        if (!$this->idAutoIncrement()) {
            $columns = ['id' => ':id'] + $columns;
            $params['id'] = $this->nextId();
        }

        $stmt = $this->db->prepare(sprintf(
            'INSERT INTO chats (%s) VALUES (%s)',
            implode(', ', array_keys($columns)),
            implode(', ', array_values($columns))
        ));

        return $stmt->execute($params);
    }

    public function latestVisibleMessageId(int $userId, int $roomId): int
    {
        $hasAuthorId = $this->hasAuthorId();
        $authorJoin = $hasAuthorId ? 'u.id = c.author_id' : 'u.login = c.author';
        $privateFromCondition = $hasAuthorId ? 'c.author_id = :user_id_from' : 'u.id = :user_id_from';

        $stmt = $this->db->prepare("
            SELECT COALESCE(MAX(c.id), 0)
            FROM chats c
            LEFT JOIN users u ON $authorJoin
            WHERE (c.private = 0 AND (c.room = :room OR c.room = 0))
               OR (c.private = 1 AND ($privateFromCondition OR c.userto = :user_id_to))
        ");
        $stmt->bindValue(':room', $roomId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id_from', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id_to', $userId, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function activeMuteForUser(int $userId): array
    {
        if ($userId <= 0 || !$this->tableExists('moderation_punishments')) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT *
               FROM moderation_punishments
              WHERE target_user_id = :user
                AND action = "mute"
                AND active = 1
                AND (expires_at = 0 OR expires_at > :now)
              ORDER BY id DESC
              LIMIT 1'
        );
        $stmt->execute(['user' => $userId, 'now' => time()]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function canModerate(int $userId): bool
    {
        if ($userId <= 0) {
            return false;
        }

        $stmt = $this->db->prepare(
            'SELECT u.groups, u.moderation, u.police, COALESCE(i.admins_panels, 0) AS admins_panels
               FROM users u
          LEFT JOIN information_users i ON i.users_id = u.id
              WHERE u.id = :id
              LIMIT 1'
        );
        $stmt->execute(['id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        return (int) ($row['groups'] ?? 0) === 1
            || (int) ($row['moderation'] ?? 0) === 1
            || (int) ($row['police'] ?? 0) === 1
            || (int) ($row['admins_panels'] ?? 0) === 1;
    }

    public function findUserForModeration(string $loginOrId): array
    {
        $loginOrId = trim($loginOrId);
        if ($loginOrId === '') {
            return [];
        }

        if (ctype_digit($loginOrId)) {
            $stmt = $this->db->prepare('SELECT id, login, ip FROM users WHERE id = :id LIMIT 1');
            $stmt->execute(['id' => (int) $loginOrId]);
        } else {
            $stmt = $this->db->prepare('SELECT id, login, ip FROM users WHERE LOWER(login) = LOWER(:login) LIMIT 1');
            $stmt->execute(['login' => $loginOrId]);
        }

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function createPunishment(
        int $moderatorId,
        array $target,
        string $action,
        int $expiresAt,
        string $reason,
        string $scope = 'chat',
        int $active = 1
    ): int
    {
        if (!$this->tableExists('moderation_punishments')) {
            return 0;
        }

        $now = time();
        $stmt = $this->db->prepare(
            'INSERT INTO moderation_punishments
                (target_user_id, target_login, moderator_user_id, action, scope, reason, starts_at, expires_at, active, created_at)
             VALUES
                (:target_user_id, :target_login, :moderator_user_id, :action, :scope, :reason, :starts_at, :expires_at, :active, :created_at)'
        );
        $stmt->execute([
            'target_user_id' => (int) $target['id'],
            'target_login' => (string) $target['login'],
            'moderator_user_id' => $moderatorId,
            'action' => $action,
            'scope' => $scope,
            'reason' => $reason,
            'starts_at' => $now,
            'expires_at' => $expiresAt,
            'active' => $active,
            'created_at' => $now,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function revokePunishments(int $moderatorId, int $targetUserId, string $action, string $reason): int
    {
        if ($targetUserId <= 0 || !$this->tableExists('moderation_punishments')) {
            return 0;
        }

        $stmt = $this->db->prepare(
            'UPDATE moderation_punishments
                SET active = 0, revoked_at = :revoked_at, revoked_by = :revoked_by, revoke_reason = :reason
              WHERE target_user_id = :target
                AND action = :action
                AND active = 1'
        );
        $stmt->execute([
            'revoked_at' => time(),
            'revoked_by' => $moderatorId,
            'reason' => $reason,
            'target' => $targetUserId,
            'action' => $action,
        ]);

        return $stmt->rowCount();
    }

    public function banIpForUser(array $target): void
    {
        $ip = (int) ($target['ip'] ?? 0);
        if ($ip <= 0 || !$this->tableExists('banip')) {
            return;
        }

        $exists = $this->db->prepare('SELECT 1 FROM banip WHERE ip = :ip LIMIT 1');
        $exists->execute(['ip' => $ip]);
        if ($exists->fetchColumn()) {
            return;
        }

        $this->db->prepare('INSERT INTO banip (id, ip, date) VALUES (:id, :ip, NOW())')
            ->execute(['id' => $this->nextTableId('banip', 'id'), 'ip' => $ip]);
    }

    public function unbanIpForUser(array $target): int
    {
        $ip = (int) ($target['ip'] ?? 0);
        if ($ip <= 0 || !$this->tableExists('banip')) {
            return 0;
        }

        $stmt = $this->db->prepare('DELETE FROM banip WHERE ip = :ip');
        $stmt->execute(['ip' => $ip]);
        return $stmt->rowCount();
    }

    private function hasAuthorId(): bool
    {
        if ($this->hasAuthorId !== null) {
            return $this->hasAuthorId;
        }

        $stmt = $this->db->query("SHOW COLUMNS FROM chats LIKE 'author_id'");
        $this->hasAuthorId = (bool) $stmt->fetch();

        return $this->hasAuthorId;
    }

    private function idAutoIncrement(): bool
    {
        if ($this->idAutoIncrement !== null) {
            return $this->idAutoIncrement;
        }

        $stmt = $this->db->query("SHOW COLUMNS FROM chats LIKE 'id'");
        $column = $stmt->fetch();
        $this->idAutoIncrement = $column && str_contains((string) ($column['Extra'] ?? ''), 'auto_increment');

        return $this->idAutoIncrement;
    }

    private function nextId(): int
    {
        $stmt = $this->db->query('SELECT COALESCE(MAX(id), 0) + 1 FROM chats');
        return (int) $stmt->fetchColumn();
    }

    private function tableExists(string $table): bool
    {
        static $cache = [];
        if (isset($cache[$table])) {
            return $cache[$table];
        }
        $stmt = $this->db->prepare(
            'SELECT 1
               FROM INFORMATION_SCHEMA.TABLES
              WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table
              LIMIT 1'
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
