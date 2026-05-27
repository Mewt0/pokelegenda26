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

    public function startIfAvailable(int $userId, int $questId, int $process): bool
    {
        if (!$this->canStart($userId, $questId)) {
            return false;
        }

        $existing = $this->findForUser($userId, $questId);
        if ($existing === null) {
            return $this->createIfMissing($userId, $questId, $process);
        }

        if ((int) ($existing['gotov'] ?? 0) === 1) {
            return false;
        }

        if ((int) ($existing['process'] ?? 0) < $process) {
            $this->updateState($userId, $questId, $process, 0);
            return true;
        }

        return false;
    }

    public function completeIfActive(int $userId, int $questId, int $process): bool
    {
        if (!$this->canStart($userId, $questId)) {
            return false;
        }

        $existing = $this->findForUser($userId, $questId);
        if ($existing === null) {
            $steps = $this->steps($questId);
            $firstProcess = $steps !== [] ? max(1, (int) ($steps[0]['required_process'] ?? 1)) : 1;
            $this->createIfMissing($userId, $questId, $firstProcess);
            $existing = $this->findForUser($userId, $questId);
        }

        if ($existing === null || (int) ($existing['gotov'] ?? 0) === 1) {
            return false;
        }

        $this->updateState($userId, $questId, $process, 1);
        return true;
    }

    public function isCompleted(int $userId, int $questId): bool
    {
        $state = $this->findForUser($userId, $questId);
        return $state !== null && (int) ($state['gotov'] ?? 0) === 1;
    }

    public function addQuestRank(int $userId, int $points): void
    {
        if ($userId <= 0 || $points === 0) {
            return;
        }

        $stmt = $this->db->prepare('UPDATE users SET rang_c = rang_c + :points WHERE id = :user LIMIT 1');
        $stmt->execute(['points' => $points, 'user' => $userId]);
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

    public function journalForUser(int $userId): array
    {
        if (!$this->tableExists('quest_definitions')) {
            return ['ok' => false, 'message' => 'Таблицы квестов не готовы.', 'quests' => []];
        }

        $stmt = $this->db->query(
            'SELECT id, title, description, depends_on_quest_id, repeatable, reward_json, enabled, updated_at
               FROM quest_definitions
              WHERE enabled = 1
              ORDER BY id ASC'
        );
        $definitions = $stmt ? ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []) : [];

        $quests = [];
        $summary = [
            'available' => 0,
            'active' => 0,
            'completed' => 0,
            'locked' => 0,
            'cooldown' => 0,
        ];

        foreach ($definitions as $definition) {
            $questId = (int) ($definition['id'] ?? 0);
            $state = $this->findForUser($userId, $questId);
            $steps = $this->steps($questId);
            $completed = $state !== null && (int) ($state['gotov'] ?? 0) === 1;
            $process = $state !== null ? (int) ($state['process'] ?? 0) : 0;
            $cooldownUntil = $state !== null ? max(0, (int) ($state['time'] ?? 0)) : 0;
            $onCooldown = (int) ($definition['repeatable'] ?? 0) === 1 && $cooldownUntil > time();
            $canStart = $this->canStart($userId, $questId);
            $status = 'available';
            if (!$canStart) {
                $status = 'locked';
            }
            if ($state !== null && !$completed) {
                $status = $onCooldown ? 'cooldown' : 'active';
            }
            if ($completed) {
                $status = 'completed';
            }

            $summary[$status] = ($summary[$status] ?? 0) + 1;
            $quests[] = [
                'id' => $questId,
                'title' => (string) ($definition['title'] ?? ('Квест #' . $questId)),
                'description' => (string) ($definition['description'] ?? ''),
                'status' => $status,
                'can_start' => $canStart && ($state === null || ((int) ($definition['repeatable'] ?? 0) === 1 && !$onCooldown)),
                'repeatable' => (int) ($definition['repeatable'] ?? 0) === 1,
                'depends_on_quest_id' => (int) ($definition['depends_on_quest_id'] ?? 0),
                'process' => $process,
                'completed' => $completed,
                'cooldown_until' => $cooldownUntil,
                'cooldown_remaining_seconds' => max(0, $cooldownUntil - time()),
                'reward' => $this->decodeJson((string) ($definition['reward_json'] ?? '')),
                'state' => $state ? [
                    'pers' => (int) ($state['pers'] ?? 0),
                    'time' => (int) ($state['time'] ?? 0),
                ] : null,
                'steps' => $this->formatSteps($steps, $process, $completed),
                'current_step' => $this->currentStep($steps, $process, $completed),
            ];
        }

        return [
            'ok' => true,
            'quests' => $quests,
            'summary' => $summary,
        ];
    }

    public function startFromDefinition(int $userId, int $questId): array
    {
        $definition = $this->definition($questId);
        if ($definition === [] || (int) ($definition['enabled'] ?? 0) !== 1) {
            return ['ok' => false, 'message' => 'Квест не найден или выключен.'];
        }
        if (!$this->canStart($userId, $questId)) {
            return ['ok' => false, 'message' => 'Сначала нужно завершить связанный квест.'];
        }

        $state = $this->findForUser($userId, $questId);
        if ($state !== null) {
            $completed = (int) ($state['gotov'] ?? 0) === 1;
            $repeatable = (int) ($definition['repeatable'] ?? 0) === 1;
            $cooldownUntil = (int) ($state['time'] ?? 0);
            if (!$repeatable || !$completed && $cooldownUntil <= time()) {
                return ['ok' => false, 'message' => 'Квест уже есть в журнале.'];
            }
            if ($cooldownUntil > time()) {
                return ['ok' => false, 'message' => 'Квест будет доступен позже.'];
            }
        }

        $steps = $this->steps($questId);
        $firstProcess = $steps !== [] ? max(1, (int) ($steps[0]['required_process'] ?? 1)) : 1;
        if ($state !== null) {
            $stmt = $this->db->prepare(
                'UPDATE quest SET process = :process, gotov = 0, pers = 0, time = 0 WHERE user_id = :user AND quest_id = :quest LIMIT 1'
            );
            $stmt->execute(['process' => $firstProcess, 'user' => $userId, 'quest' => $questId]);
            return ['ok' => true, 'message' => 'Повторный квест начат.', 'quest_id' => $questId];
        }

        $created = $this->createIfMissing($userId, $questId, $firstProcess);
        return [
            'ok' => $created,
            'message' => $created ? 'Квест принят.' : 'Квест уже есть в журнале.',
            'quest_id' => $questId,
        ];
    }

    private function nextId(): int
    {
        return (int) ($this->db->query('SELECT COALESCE(MAX(id), 0) + 1 FROM quest')->fetchColumn() ?: 1);
    }

    private function decodeJson(string $json): array
    {
        $json = trim($json);
        if ($json === '') {
            return [];
        }

        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function currentStep(array $steps, int $process, bool $completed): ?array
    {
        if ($steps === []) {
            return null;
        }
        if ($completed) {
            return $this->formatStep($steps[array_key_last($steps)], 'done');
        }

        foreach ($steps as $step) {
            if ($process <= (int) ($step['required_process'] ?? 0)) {
                return $this->formatStep($step, 'active');
            }
        }

        return $this->formatStep($steps[array_key_last($steps)], 'active');
    }

    private function formatSteps(array $steps, int $process, bool $completed): array
    {
        $formatted = [];
        foreach ($steps as $step) {
            $required = (int) ($step['required_process'] ?? 0);
            $status = $completed || ($process > 0 && $process >= $required) ? 'done' : 'locked';
            if (!$completed && $process <= $required && !in_array('active', array_column($formatted, 'status'), true)) {
                $status = 'active';
            }
            $formatted[] = $this->formatStep($step, $status);
        }

        return $formatted;
    }

    private function formatStep(array $step, string $status): array
    {
        return [
            'step_no' => (int) ($step['step_no'] ?? 0),
            'title' => (string) ($step['title'] ?? ''),
            'description' => (string) ($step['description'] ?? ''),
            'action_key' => (string) ($step['action_key'] ?? ''),
            'required_process' => (int) ($step['required_process'] ?? 0),
            'reward' => $this->decodeJson((string) ($step['reward_json'] ?? '')),
            'status' => $status,
        ];
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
