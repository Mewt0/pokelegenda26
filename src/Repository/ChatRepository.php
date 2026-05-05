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
}
