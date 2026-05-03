<?php
declare(strict_types=1);

namespace Pokemon8\Repository;

use PDO;

final class QuestRepository
{
    public function __construct(private PDO $db)
    {
    }

    public function findForUser(int $userId, int $questId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT quest_id, user_id, process, gotov FROM quest WHERE user_id = :user AND quest_id = :quest LIMIT 1'
        );
        $stmt->execute([
            'user' => $userId,
            'quest' => $questId,
        ]);

        return $stmt->fetch() ?: null;
    }

    public function createIfMissing(int $userId, int $questId, int $process): bool
    {
        if ($this->findForUser($userId, $questId) !== null) {
            return false;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO quest (quest_id, user_id, process) VALUES (:quest, :user, :process)'
        );

        return $stmt->execute([
            'quest' => $questId,
            'user' => $userId,
            'process' => $process,
        ]);
    }

    public function updateState(int $userId, int $questId, int $process, int $completed): void
    {
        $stmt = $this->db->prepare(
            'UPDATE quest SET process = :process, gotov = :completed WHERE user_id = :user AND quest_id = :quest LIMIT 1'
        );
        $stmt->execute([
            'process' => $process,
            'completed' => $completed,
            'user' => $userId,
            'quest' => $questId,
        ]);
    }
}
