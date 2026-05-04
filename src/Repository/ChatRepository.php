<?php
declare(strict_types=1);

namespace Pokemon8\Repository;

use PDO;

final class ChatRepository
{
    private const CHAT_TABLE = 'chats';
    private const USERS_TABLE = 'users';

    public function __construct(private PDO $db)
    {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listMessages(int $userId, int $roomId, int $scope, int $limit = 80): array
    {
        // scope: 1 = all rooms, 2 = current room only
        $roomFilter = $scope === 2 ? ' AND c.room = :room ' : '';

        // We fetch public messages AND private messages where user is either sender or recipient
        // Note: The legacy schema uses 'author' (login) as sender.
        // We'll join users to get the sender's ID if possible, but rely on author login for display.

        $sql = "
            SELECT c.id, c.author, c.userto, c.private, c.time as ts, c.text, c.tipe, c.room,
                   u1.id AS from_user_id, u1.login AS from_name, u2.login AS to_name
              FROM " . self::CHAT_TABLE . " c
              LEFT JOIN " . self::USERS_TABLE . " u1 ON u1.login = c.author
              LEFT JOIN " . self::USERS_TABLE . " u2 ON u2.id = c.userto
             WHERE (c.private = 0 OR c.userto = :user OR u1.id = :user)
                   " . $roomFilter . "
             ORDER BY c.id DESC
             LIMIT :limit
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user', $userId, PDO::PARAM_INT);
        if ($scope === 2) {
            $stmt->bindValue(':room', $roomId, PDO::PARAM_INT);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        return is_array($rows) ? array_reverse($rows) : [];
    }

    public function saveMessage(string $authorLogin, int $recipientId, int $private, int $time, string $text, int $type, int $room): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO " . self::CHAT_TABLE . " (author, userto, private, time, text, tipe, room)
             VALUES (:author, :userto, :private, :time, :text, :tipe, :room)"
        );

        return $stmt->execute([
            'author' => $authorLogin,
            'userto' => $recipientId,
            'private' => $private,
            'time' => $time,
            'text' => $text,
            'tipe' => $type,
            'room' => $room,
        ]);
    }

    public function updateUserChatScope(int $userId, int $scope): bool
    {
        $stmt = $this->db->prepare("UPDATE " . self::USERS_TABLE . " SET mychat = :scope WHERE id = :id LIMIT 1");
        return $stmt->execute(['scope' => $scope, 'id' => $userId]);
    }

    public function findUserByLogin(string $login): ?array
    {
        $stmt = $this->db->prepare("SELECT id, login, online, onlinetime FROM " . self::USERS_TABLE . " WHERE login = :login LIMIT 1");
        $stmt->execute(['login' => $login]);
        return $stmt->fetch() ?: null;
    }

    public function getUserChatPreferences(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT id, login, mychat, buildmy, clans FROM " . self::USERS_TABLE . " WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch();

        return $user ?: [
            'mychat' => 1,
            'buildmy' => 1,
            'clans' => 0
        ];
    }
}
