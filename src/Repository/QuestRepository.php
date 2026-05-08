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
            'SELECT * FROM quest WHERE user_id = :user AND quest_id = :quest LIMIT 1'
        );
        $stmt->execute([
            'user' => $userId,
            'quest' => $questId,
        ]);

        return $stmt->fetch() ?: null;
    }

    public function createIfMissing(int $userId, int $questId, int $process): bool
    {
        if (!$this->canStart($userId, $questId)) {
            return false;
        }

        if ($this->findForUser($userId, $questId) !== null) {
            return false;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO quest (id, quest_id, user_id, process, gotov, pers, time)
             VALUES (:id, :quest, :user, :process, 0, 0, 0)'
        );

        return $stmt->execute([
            'id' => $this->nextId(),
            'quest' => $questId,
            'user' => $userId,
            'process' => $process,
        ]);
    }

    public function createOrUpdate(int $userId, int $questId, int $process, int $completed = 0): void
    {
        if (!$this->canStart($userId, $questId)) {
            return;
        }

        $existing = $this->findForUser($userId, $questId);
        if ($existing === null) {
            $stmt = $this->db->prepare(
                'INSERT INTO quest (id, quest_id, user_id, process, gotov, pers, time)
                 VALUES (:id, :quest, :user, :process, :completed, 0, 0)'
            );
            $stmt->execute([
                'id' => $this->nextId(),
                'quest' => $questId,
                'user' => $userId,
                'process' => $process,
                'completed' => $completed,
            ]);
            return;
        }

        if ((int) ($existing['gotov'] ?? 0) === 1 && $completed === 0) {
            return;
        }

        $this->updateState($userId, $questId, $process, $completed);
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

    public function setStarterChoice(int $userId, int $baseId): void
    {
        $existing = $this->findForUser($userId, 1);
        if ($existing !== null && (int) ($existing['gotov'] ?? 0) === 1) {
            return;
        }

        $this->createOrUpdate($userId, 1, 4, 0);

        $stmt = $this->db->prepare(
            'UPDATE quest SET pers = :base, process = 4, gotov = 0 WHERE user_id = :user AND quest_id = 1 LIMIT 1'
        );
        $stmt->execute([
            'base' => $baseId,
            'user' => $userId,
        ]);
    }

    public function setCooldown(int $userId, int $questId, int $process, int $until): void
    {
        $this->createOrUpdate($userId, $questId, $process, 0);

        $stmt = $this->db->prepare(
            'UPDATE quest SET process = :process, time = :time WHERE user_id = :user AND quest_id = :quest LIMIT 1'
        );
        $stmt->execute([
            'process' => $process,
            'time' => $until,
            'user' => $userId,
            'quest' => $questId,
        ]);
    }

    public function canStart(int $userId, int $questId): bool
    {
        $definition = $this->definition($questId);
        if ($definition === []) {
            return true;
        }
        if ((int) ($definition['enabled'] ?? 1) !== 1) {
            return false;
        }

        $dependency = (int) ($definition['depends_on_quest_id'] ?? 0);
        if ($dependency <= 0) {
            return true;
        }

        $dependencyState = $this->findForUser($userId, $dependency);
        return $dependencyState !== null && (int) ($dependencyState['gotov'] ?? 0) === 1;
    }

    public function definition(int $questId): array
    {
        if (!$this->tableExists('quest_definitions')) {
            return [];
        }

        $stmt = $this->db->prepare('SELECT * FROM quest_definitions WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $questId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * @return list<array>
     */
    public function steps(int $questId): array
    {
        if (!$this->tableExists('quest_steps')) {
            return [];
        }

        $stmt = $this->db->prepare('SELECT * FROM quest_steps WHERE quest_id = :quest AND enabled = 1 ORDER BY step_no ASC');
        $stmt->execute(['quest' => $questId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function nextId(): int
    {
        return (int) ($this->db->query('SELECT COALESCE(MAX(id), 0) + 1 FROM quest')->fetchColumn() ?: 1);
    }

    private function tableExists(string $table): bool
    {
        static $cache = [];
        if (isset($cache[$table])) {
            return $cache[$table];
        }

        $stmt = $this->db->prepare(
            'SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table LIMIT 1'
        );
        $stmt->execute(['table' => $table]);
        $cache[$table] = (bool) $stmt->fetchColumn();
        return $cache[$table];
    }
}
