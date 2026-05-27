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
        $trackedQuestId = $this->trackedQuestId($userId);

        foreach ($definitions as $definition) {
            $questId = (int) ($definition['id'] ?? 0);
            $state = $this->findForUser($userId, $questId);
            $steps = $this->steps($questId);
            $repeatable = (int) ($definition['repeatable'] ?? 0) === 1;
            $completed = $state !== null && (int) ($state['gotov'] ?? 0) === 1;
            $process = $state !== null ? (int) ($state['process'] ?? 0) : 0;
            $cooldownUntil = $state !== null ? max(0, (int) ($state['time'] ?? 0)) : 0;
            $now = time();
            $onCooldown = $repeatable && $cooldownUntil > $now;
            $repeatableReady = $repeatable
                && $state !== null
                && !$completed
                && $cooldownUntil > 0
                && $cooldownUntil <= $now;
            $canStart = $this->canStart($userId, $questId);
            $status = 'available';
            if (!$canStart) {
                $status = 'locked';
            }
            if ($state !== null && !$completed && !$repeatableReady) {
                $status = $onCooldown ? 'cooldown' : 'active';
            }
            if ($completed) {
                $status = 'completed';
            }

            $summary[$status] = ($summary[$status] ?? 0) + 1;
            $formattedSteps = $this->formatSteps($steps, $process, $completed);
            $progress = $this->questProgress($formattedSteps, $process, $completed);
            $quests[] = [
                'id' => $questId,
                'title' => (string) ($definition['title'] ?? ('Квест #' . $questId)),
                'description' => (string) ($definition['description'] ?? ''),
                'icon' => $this->questIcon($definition),
                'type' => $this->questType($definition),
                'status' => $status,
                'tracked' => $trackedQuestId === $questId,
                'can_start' => $canStart && (
                    $state === null
                    || ($repeatable && ($completed || $repeatableReady) && !$onCooldown)
                ),
                'repeatable' => $repeatable,
                'depends_on_quest_id' => (int) ($definition['depends_on_quest_id'] ?? 0),
                'process' => $process,
                'completed' => $completed,
                'cooldown_until' => $cooldownUntil,
                'cooldown_remaining_seconds' => max(0, $cooldownUntil - time()),
                'reward' => $this->decodeJson((string) ($definition['reward_json'] ?? '')),
                'reward_view' => $this->formatRewardView($this->decodeJson((string) ($definition['reward_json'] ?? ''))),
                'state' => $state ? [
                    'pers' => (int) ($state['pers'] ?? 0),
                    'time' => (int) ($state['time'] ?? 0),
                ] : null,
                'steps' => $formattedSteps,
                'current_step' => $this->currentStep($steps, $process, $completed),
                'progress' => $progress,
            ];
        }

        return [
            'ok' => true,
            'quests' => $quests,
            'summary' => $summary,
            'tracked_quest_id' => $trackedQuestId,
        ];
    }

    public function trackedQuestId(int $userId): int
    {
        if ($userId <= 0 || !$this->tableExists('user_quest_tracking')) {
            return 0;
        }

        $stmt = $this->db->prepare('SELECT quest_id FROM user_quest_tracking WHERE user_id = :user LIMIT 1');
        $stmt->execute(['user' => $userId]);
        return max(0, (int) ($stmt->fetchColumn() ?: 0));
    }

    public function trackQuest(int $userId, int $questId): array
    {
        if ($userId <= 0) {
            return ['ok' => false, 'message' => 'Нужно войти в игру.'];
        }
        if (!$this->tableExists('user_quest_tracking')) {
            return ['ok' => false, 'message' => 'Таблица отслеживания квестов не готова.'];
        }

        if ($questId > 0) {
            $definition = $this->definition($questId);
            if ($definition === [] || (int) ($definition['enabled'] ?? 0) !== 1) {
                return ['ok' => false, 'message' => 'Квест не найден.'];
            }
            if (!$this->canStart($userId, $questId)) {
                return ['ok' => false, 'message' => 'Этот квест пока заблокирован.'];
            }
        }

        $stmt = $this->db->prepare(
            'INSERT INTO user_quest_tracking (user_id, quest_id, updated_at)
             VALUES (:user, :quest, :time)
             ON DUPLICATE KEY UPDATE quest_id = VALUES(quest_id), updated_at = VALUES(updated_at)'
        );
        $stmt->execute([
            'user' => $userId,
            'quest' => max(0, $questId),
            'time' => time(),
        ]);

        return [
            'ok' => true,
            'quest_id' => max(0, $questId),
            'message' => $questId > 0 ? 'Квест добавлен в отслеживание.' : 'Отслеживание квеста снято.',
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
            $now = time();
            $repeatableReady = $repeatable && !$completed && $cooldownUntil > 0 && $cooldownUntil <= $now;
            if ($repeatable && $cooldownUntil > $now) {
                return ['ok' => false, 'message' => 'Квест будет доступен позже.'];
            }
            if (!$repeatable || (!$completed && !$repeatableReady)) {
                return ['ok' => false, 'message' => 'Квест уже есть в журнале.'];
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
            $formatted = $this->formatSteps($steps, $process, $completed);
            return $formatted[array_key_last($formatted)] ?? null;
        }

        $formatted = $this->formatSteps($steps, $process, $completed);
        foreach ($formatted as $step) {
            if (($step['status'] ?? '') === 'active') {
                return $step;
            }
        }

        return $formatted[array_key_last($formatted)] ?? null;
    }

    private function formatSteps(array $steps, int $process, bool $completed): array
    {
        $formatted = [];
        $previousRequired = 0;
        $activeAssigned = false;
        foreach ($steps as $step) {
            $required = (int) ($step['required_process'] ?? 0);
            $target = max(1, $required - $previousRequired);
            $current = 0;
            $status = 'locked';

            if ($completed || ($process > 0 && $process >= $required)) {
                $status = 'done';
                $current = $target;
            } elseif (!$activeAssigned) {
                $status = 'active';
                $current = max(0, min($target, $process - $previousRequired));
                $activeAssigned = true;
            }

            $formatted[] = $this->formatStep($step, $status, $current, $target);
            $previousRequired = max($previousRequired, $required);
        }

        return $formatted;
    }

    private function formatStep(array $step, string $status, int $current = 0, ?int $target = null): array
    {
        $target = max(1, $target ?? (int) ($step['required_process'] ?? 1));
        $current = $status === 'done' ? $target : max(0, min($target, $current));

        return [
            'step_no' => (int) ($step['step_no'] ?? 0),
            'title' => (string) ($step['title'] ?? ''),
            'description' => (string) ($step['description'] ?? ''),
            'action_key' => (string) ($step['action_key'] ?? ''),
            'required_process' => (int) ($step['required_process'] ?? 0),
            'reward' => $this->decodeJson((string) ($step['reward_json'] ?? '')),
            'reward_view' => $this->formatRewardView($this->decodeJson((string) ($step['reward_json'] ?? ''))),
            'status' => $status,
            'progress' => [
                'current' => $current,
                'target' => $target,
                'percent' => (int) min(100, round(($current / $target) * 100)),
            ],
        ];
    }

    private function questProgress(array $steps, int $process, bool $completed): array
    {
        if ($steps === []) {
            return [
                'current' => $completed ? 1 : 0,
                'target' => 1,
                'percent' => $completed ? 100 : 0,
            ];
        }

        $target = 0;
        foreach ($steps as $step) {
            $target += max(1, (int) ($step['progress']['target'] ?? 1));
        }
        $current = 0;
        foreach ($steps as $step) {
            $current += max(0, (int) ($step['progress']['current'] ?? 0));
        }
        if ($completed) {
            $current = $target;
        }

        return [
            'current' => $current,
            'target' => max(1, $target),
            'percent' => (int) min(100, round(($current / max(1, $target)) * 100)),
        ];
    }

    private function questType(array $definition): string
    {
        if ((int) ($definition['repeatable'] ?? 0) === 1) {
            return 'daily';
        }

        $title = mb_strtolower((string) ($definition['title'] ?? ''), 'UTF-8');
        $description = mb_strtolower((string) ($definition['description'] ?? ''), 'UTF-8');
        $text = $title . ' ' . $description;
        if (str_contains($text, 'турнир')) {
            return 'tournament';
        }
        if (str_contains($text, 'pvp') || str_contains($text, 'пвп')) {
            return 'pvp';
        }
        if (str_contains($text, 'ивент') || str_contains($text, 'событ')) {
            return 'event';
        }
        if ((int) ($definition['depends_on_quest_id'] ?? 0) > 0) {
            return 'chain';
        }

        return 'story';
    }

    private function questIcon(array $definition): string
    {
        return match ($this->questType($definition)) {
            'daily' => '/public/img/ui/menu-quests.png',
            'tournament' => '/public/img/ui/menu-battle.png',
            'pvp' => '/public/img/ui/menu-battle.png',
            'event' => '/public/img/ui/menu-quests.png',
            default => '/public/img/ui/menu-quests.png',
        };
    }

    private function formatRewardView(array $reward): array
    {
        $entries = [];
        foreach (($reward['items'] ?? []) as $itemId => $amount) {
            $id = (int) $itemId;
            $entries[] = [
                'type' => 'item',
                'id' => $id,
                'label' => $this->itemName($id),
                'amount' => (int) $amount,
                'icon' => '/public/img/items/' . $id . '.png',
            ];
        }
        if (isset($reward['rank'])) {
            $entries[] = [
                'type' => 'rank',
                'id' => 0,
                'label' => 'Рейтинг квестов',
                'amount' => (int) $reward['rank'],
                'icon' => '/public/img/ui/menu-profile.png',
            ];
        }
        if (isset($reward['exp'])) {
            $entries[] = [
                'type' => 'exp',
                'id' => 0,
                'label' => 'Опыт',
                'amount' => (int) $reward['exp'],
                'icon' => '/public/img/ui/menu-pokemon.png',
            ];
        }
        foreach (($reward['pokemon'] ?? []) as $pokemon) {
            $baseId = is_array($pokemon) ? (int) ($pokemon['base_id'] ?? $pokemon['id'] ?? 0) : (int) $pokemon;
            if ($baseId > 0) {
                $entries[] = [
                    'type' => 'pokemon',
                    'id' => $baseId,
                    'label' => $this->pokemonName($baseId),
                    'amount' => 1,
                    'icon' => '/img/pokemon/mini/' . $baseId . '.png',
                ];
            }
        }

        return $entries;
    }

    private function itemName(int $itemId): string
    {
        static $cache = [];
        if ($itemId <= 0) {
            return 'Предмет';
        }
        if (isset($cache[$itemId])) {
            return $cache[$itemId];
        }

        $stmt = $this->db->prepare('SELECT COALESCE(NULLIF(name, ""), NULLIF(tittle, ""), CONCAT("Предмет #", id)) FROM items WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $itemId]);
        $cache[$itemId] = (string) ($stmt->fetchColumn() ?: ('Предмет #' . $itemId));
        return $cache[$itemId];
    }

    private function pokemonName(int $baseId): string
    {
        static $cache = [];
        if ($baseId <= 0) {
            return 'Покемон';
        }
        if (isset($cache[$baseId])) {
            return $cache[$baseId];
        }

        $stmt = $this->db->prepare('SELECT title FROM poke_base WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $baseId]);
        $cache[$baseId] = (string) ($stmt->fetchColumn() ?: ('Pokemon #' . $baseId));
        return $cache[$baseId];
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
