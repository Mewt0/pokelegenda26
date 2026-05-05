<?php
declare(strict_types=1);

namespace Pokemon8\Repository;

use PDO;

final class MessageRepository
{
    public function __construct(private PDO $db)
    {
    }

    /** @return list<array<string,mixed>> */
    public function inboxForUser(int $userId, int $limit = 50): array
    {
        if ($userId <= 0) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT s.id, s.users, s.inputusers, s.tema, s.text, s.active, s.date,
                    u.login AS sender_login
               FROM sends s
               LEFT JOIN users u ON u.id = s.inputusers
              WHERE s.users = :user
              ORDER BY s.id DESC
              LIMIT :limit'
        );
        $stmt->bindValue(':user', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', max(1, min(200, $limit)), PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }
}
