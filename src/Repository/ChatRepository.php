<?php
declare(strict_types=1);

namespace Pokemon8\Repository;

use PDO;

final class ChatRepository
{
    public function __construct(private PDO $db)
    {
    }

    /**
     * @return list<array>
     */
    public function findMessages(int $userId, int $roomId, int $afterId = 0, int $limit = 50): array
    {
        // Мы берем:
        // 1. Публичные сообщения (private = 0) текущей комнаты.
        // 2. Личные сообщения, где пользователь - отправитель или получатель (независимо от комнаты).
        // 3. Системные сообщения (author_id = 0), которые либо глобальные (room = 0), либо текущей комнаты.

        $order = $afterId > 0 ? 'ASC' : 'DESC';

        $stmt = $this->db->prepare("
            SELECT c.*, u.login as author_name, ut.login as to_name
            FROM chats c
            LEFT JOIN users u ON u.id = c.author_id
            LEFT JOIN users ut ON ut.id = c.userto
            WHERE c.id > :after_id
              AND (
                (c.private = 0 AND (c.room = :room OR c.room = 0))
                OR (c.private = 1 AND (c.author_id = :user_id OR c.userto = :user_id))
              )
            ORDER BY c.id $order
            LIMIT :limit
        ");

        $stmt->bindValue(':after_id', $afterId, PDO::PARAM_INT);
        $stmt->bindValue(':room', $roomId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
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
        $stmt = $this->db->prepare('
            INSERT INTO chats (author_id, author, userto, private, text, tipe, room, time)
            VALUES (:author_id, :author, :userto, :private, :text, :tipe, :room, :time)
        ');

        return $stmt->execute([
            'author_id' => $data['author_id'] ?? 0,
            'author'    => $data['author'] ?? 'System',
            'userto'    => $data['userto'] ?? 0,
            'private'   => $data['private'] ?? 0,
            'text'      => $data['text'],
            'tipe'      => $data['tipe'] ?? 1,
            'room'      => $data['room'] ?? 0,
            'time'      => time(),
        ]);
    }
}
