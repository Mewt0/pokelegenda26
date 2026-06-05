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
        $currentLocationId = $this->currentLocationId($userId);

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
            $formattedSteps = $this->formatSteps($steps, $process, $completed, $currentLocationId);
            $progress = $this->questProgress($formattedSteps, $process, $completed);
            $currentStep = $this->currentStep($steps, $process, $completed, $currentLocationId);
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
                'current_step' => $currentStep,
                'navigation' => $currentStep['navigation'] ?? null,
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

    private function currentStep(array $steps, int $process, bool $completed, int $currentLocationId = 0): ?array
    {
        if ($steps === []) {
            return null;
        }
        if ($completed) {
            $formatted = $this->formatSteps($steps, $process, $completed, $currentLocationId);
            return $formatted[array_key_last($formatted)] ?? null;
        }

        $formatted = $this->formatSteps($steps, $process, $completed, $currentLocationId);
        foreach ($formatted as $step) {
            if (($step['status'] ?? '') === 'active') {
                return $step;
            }
        }

        return $formatted[array_key_last($formatted)] ?? null;
    }

    private function formatSteps(array $steps, int $process, bool $completed, int $currentLocationId = 0): array
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

            $formatted[] = $this->formatStep($step, $status, $current, $target, $currentLocationId);
            $previousRequired = max($previousRequired, $required);
        }

        return $formatted;
    }

    private function formatStep(array $step, string $status, int $current = 0, ?int $target = null, int $currentLocationId = 0): array
    {
        $target = max(1, $target ?? (int) ($step['required_process'] ?? 1));
        $current = $status === 'done' ? $target : max(0, min($target, $current));
        $actionKey = (string) ($step['action_key'] ?? '');

        return [
            'step_no' => (int) ($step['step_no'] ?? 0),
            'title' => (string) ($step['title'] ?? ''),
            'description' => (string) ($step['description'] ?? ''),
            'action_key' => $actionKey,
            'required_process' => (int) ($step['required_process'] ?? 0),
            'reward' => $this->decodeJson((string) ($step['reward_json'] ?? '')),
            'reward_view' => $this->formatRewardView($this->decodeJson((string) ($step['reward_json'] ?? ''))),
            'status' => $status,
            'navigation' => $status !== 'locked' ? $this->navigationForAction($actionKey, $currentLocationId) : null,
            'progress' => [
                'current' => $current,
                'target' => $target,
                'percent' => (int) min(100, round(($current / $target) * 100)),
            ],
        ];
    }

    private function currentLocationId(int $userId): int
    {
        if ($userId <= 0 || !$this->tableExists('users')) {
            return 0;
        }

        $stmt = $this->db->prepare('SELECT buildmy FROM users WHERE id = :user LIMIT 1');
        $stmt->execute(['user' => $userId]);
        return max(0, (int) ($stmt->fetchColumn() ?: 0));
    }

    private function navigationForAction(string $actionKey, int $currentLocationId): ?array
    {
        $actionKey = trim($actionKey);
        if ($actionKey === '') {
            return null;
        }

        $targets = $this->questNavigationTargets();
        if (!isset($targets[$actionKey])) {
            return null;
        }

        $target = $targets[$actionKey];
        $targetLocationId = max(0, (int) ($target['target_location_id'] ?? 0));
        $pathIds = $targetLocationId > 0 ? $this->shortestLocationPath($currentLocationId, $targetLocationId) : [];
        $path = $this->formatLocationPath($pathIds);
        $nextLocationId = $this->nextLocationInPath($pathIds, $currentLocationId);
        $nextLocationTitle = $nextLocationId > 0 ? $this->locationTitle($nextLocationId) : '';

        return [
            'action_key' => $actionKey,
            'kind' => (string) ($target['kind'] ?? 'route'),
            'label' => (string) ($target['label'] ?? ''),
            'hint' => (string) ($target['hint'] ?? ''),
            'currentLocationId' => $currentLocationId,
            'targetLocationId' => $targetLocationId,
            'targetLocationTitle' => $targetLocationId > 0 ? $this->locationTitle($targetLocationId) : '',
            'targetNpcTitle' => (string) ($target['target_npc_title'] ?? ''),
            'targetControl' => (string) ($target['target_control'] ?? ''),
            'onTarget' => $targetLocationId > 0 && $currentLocationId === $targetLocationId,
            'nextLocationId' => $nextLocationId,
            'nextLocationTitle' => $nextLocationTitle,
            'path' => $path,
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function questNavigationTargets(): array
    {
        return [
            'talk_oak' => [
                'kind' => 'npc',
                'label' => 'Профессор Оук',
                'target_location_id' => 3,
                'target_npc_title' => 'Профессор Оук',
                'hint' => 'Перейдите в лабораторию и поговорите с профессором Оуком.',
            ],
            'oak_intro' => [
                'kind' => 'npc',
                'label' => 'Профессор Оук',
                'target_location_id' => 3,
                'target_npc_title' => 'Профессор Оук',
                'hint' => 'Перейдите в лабораторию и поговорите с профессором Оуком.',
            ],
            'starter_choice' => [
                'kind' => 'npc',
                'label' => 'Профессор Оук',
                'target_location_id' => 3,
                'target_npc_title' => 'Профессор Оук',
                'hint' => 'Выберите первого покемона у профессора Оука.',
            ],
            'fpe_route_1' => [
                'kind' => 'route',
                'label' => 'Дорога 1',
                'target_location_id' => 4,
                'hint' => 'Выйдите из Алабастии на Дорогу 1.',
            ],
            'fpe_first_battle' => [
                'kind' => 'battle',
                'label' => 'Первый PvE-бой',
                'target_location_id' => 4,
                'target_control' => 'pve',
                'hint' => 'На Дороге 1 включите нападение или начните PvE-бой.',
            ],
            'fpe_viridian_path' => [
                'kind' => 'route',
                'label' => 'Вертания',
                'target_location_id' => 16,
                'hint' => 'Идите через Дорогу 1 и Лес Вертании до города Вертания.',
            ],
            'fpe_viridian_arrival' => [
                'kind' => 'npc',
                'label' => 'Покецентр Вертании',
                'target_location_id' => 16,
                'target_npc_title' => 'Покецентр',
                'hint' => 'Вы в Вертании. Зайдите к Покецентру или осмотритесь в городе.',
            ],
            'fpe_transport_open' => [
                'kind' => 'npc',
                'label' => 'Касса',
                'target_location_id' => 23,
                'target_npc_title' => 'Касса',
                'hint' => 'Найдите кассу транспорта и откройте рейсы.',
            ],
            'fpe_transport_used' => [
                'kind' => 'transport',
                'label' => 'Использовать рейс',
                'target_location_id' => 23,
                'target_npc_title' => 'Касса',
                'hint' => 'Выберите доступный рейс в кассе и отправьтесь в путь.',
            ],
            'spike_story' => [
                'kind' => 'npc',
                'label' => 'Странный Спайк',
                'target_location_id' => 1,
                'target_npc_title' => 'Странный Спайк',
                'hint' => 'Вернитесь в Алабастию и поговорите со Странным Спайком.',
            ],
            'old_woman_story' => [
                'kind' => 'npc',
                'label' => 'Старая женщина',
                'target_location_id' => 7,
                'target_npc_title' => 'Старая женщина',
                'hint' => 'Пройдите в Тёмный лес и расспросите старую женщину про Айрена.',
            ],
            'old_woman_turnin' => [
                'kind' => 'npc',
                'label' => 'Старая женщина',
                'target_location_id' => 7,
                'target_npc_title' => 'Старая женщина',
                'hint' => 'Принесите старой женщине трёх Venonat 25+ уровня с нахальным характером.',
            ],
            'amira_lake' => [
                'kind' => 'npc',
                'label' => 'Художница Амира',
                'target_location_id' => 11,
                'target_npc_title' => 'Художница Амира',
                'hint' => 'Доберитесь до Небольшого озера и поговорите с Амирой.',
            ],
            'airen_lake' => [
                'kind' => 'npc',
                'label' => 'Айрен',
                'target_location_id' => 11,
                'target_npc_title' => 'Айрен',
                'hint' => 'Найдите Айрена на берегу Небольшого озера.',
            ],
            'articuno_cliffs' => [
                'kind' => 'npc',
                'label' => '#144 Articuno',
                'target_location_id' => 13,
                'target_npc_title' => '#144 Articuno',
                'hint' => 'Идите к Скалам и проверьте след Артикуно.',
            ],
            'airen_finish' => [
                'kind' => 'npc',
                'label' => 'Айрен',
                'target_location_id' => 11,
                'target_npc_title' => 'Айрен',
                'hint' => 'Вернитесь к Айрену у озера и завершите историю.',
            ],
        ];
    }

    /**
     * @return list<int>
     */
    private function shortestLocationPath(int $from, int $to): array
    {
        if ($from <= 0 || $to <= 0) {
            return [];
        }
        if ($from === $to) {
            return [$from];
        }

        $edges = $this->legacyLocationEdges();
        $queue = [[$from]];
        $seen = [$from => true];
        while ($queue !== []) {
            $path = array_shift($queue);
            $last = (int) end($path);
            foreach (($edges[$last] ?? []) as $next) {
                $next = (int) $next;
                if ($next <= 0 || isset($seen[$next])) {
                    continue;
                }
                $nextPath = [...$path, $next];
                if ($next === $to) {
                    return $nextPath;
                }
                $seen[$next] = true;
                $queue[] = $nextPath;
            }
        }

        return [];
    }

    /**
     * @return array<int, list<int>>
     */
    private function legacyLocationEdges(): array
    {
        static $edges = null;
        if (is_array($edges)) {
            return $edges;
        }

        $dataLoc = [];
        $path = defined('APP_ROOT') ? APP_ROOT . '/include/data.world.php' : dirname(__DIR__, 2) . '/include/data.world.php';
        if (is_file($path)) {
            require $path;
        }

        $edges = [];
        foreach ((array) $dataLoc as $from => $targets) {
            $from = (int) $from;
            $edges[$from] = array_values(array_filter(array_unique(array_map('intval', (array) $targets)), static fn (int $id): bool => $id > 0));
        }

        return $edges;
    }

    /**
     * @param list<int> $pathIds
     */
    private function nextLocationInPath(array $pathIds, int $currentLocationId): int
    {
        if ($pathIds === []) {
            return 0;
        }
        foreach ($pathIds as $index => $id) {
            if ((int) $id === $currentLocationId) {
                return (int) ($pathIds[$index + 1] ?? 0);
            }
        }

        return (int) ($pathIds[0] ?? 0);
    }

    /**
     * @param list<int> $pathIds
     * @return list<array{id:int,title:string}>
     */
    private function formatLocationPath(array $pathIds): array
    {
        $path = [];
        foreach ($pathIds as $id) {
            $id = (int) $id;
            if ($id <= 0) {
                continue;
            }
            $path[] = [
                'id' => $id,
                'title' => $this->locationTitle($id),
            ];
        }

        return $path;
    }

    private function locationTitle(int $locationId): string
    {
        static $cache = [];
        if ($locationId <= 0) {
            return '';
        }
        if (array_key_exists($locationId, $cache)) {
            return $cache[$locationId];
        }

        $content = $this->locationContent();
        if (isset($content[$locationId]['name']) && (string) $content[$locationId]['name'] !== '') {
            return $cache[$locationId] = (string) $content[$locationId]['name'];
        }

        $stmt = $this->db->prepare('SELECT title FROM build WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $locationId]);
        $title = (string) ($stmt->fetchColumn() ?: ('Локация #' . $locationId));
        return $cache[$locationId] = $this->toUtf8($title);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function locationContent(): array
    {
        static $content = null;
        if (is_array($content)) {
            return $content;
        }

        $path = defined('APP_ROOT') ? APP_ROOT . '/config/location_content.php' : dirname(__DIR__, 2) . '/config/location_content.php';
        $data = is_file($path) ? require $path : [];
        $content = is_array($data) ? $data : [];
        return $content;
    }

    private function toUtf8(string $value): string
    {
        if (mb_check_encoding($value, 'UTF-8')) {
            return $value;
        }

        $converted = @iconv('Windows-1251', 'UTF-8//IGNORE', $value);
        return $converted !== false ? $converted : $value;
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
