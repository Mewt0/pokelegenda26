<?php
declare(strict_types=1);

namespace Pokemon8\Game;

use Pokemon8\Repository\LocationRepository;
use Pokemon8\Repository\QuestRepository;
use Pokemon8\Repository\RewardRepository;

final class MapMoveService
{
    public function __construct(
        private LocationRepository $locations,
        private LocationGraph $graph,
        private LocationStateService $state,
        private ?QuestRepository $quests = null,
        private ?RewardRepository $rewards = null,
    ) {
    }

    public function move(int $userId, int $targetLocationId): array
    {
        if ($targetLocationId <= 0) {
            return ['ok' => false, 'error' => 'bad_target', 'message' => 'Некорректная локация.'];
        }

        $user = $this->locations->findUserState($userId);
        if ($user === null) {
            return ['ok' => false, 'error' => 'auth', 'message' => 'Нужно войти в игру.'];
        }

        if ((int) $user['pvp'] === 1) {
            return ['ok' => false, 'error' => 'in_pvp', 'message' => 'Сейчас нельзя перейти: идет PvP-бой.'];
        }

        if ((int) $user['pve'] === 1) {
            return ['ok' => false, 'error' => 'in_pve', 'message' => 'Сейчас нельзя перейти: идет PvE-бой.'];
        }

        if ((int) $user['trade'] > 0) {
            return ['ok' => false, 'error' => 'in_trade', 'message' => 'Сейчас нельзя перейти: идет обмен.'];
        }

        $currentLocationId = (int) $user['buildmy'];
        if (!$this->graph->canMove($currentLocationId, $targetLocationId)) {
            return ['ok' => false, 'error' => 'forbidden', 'message' => 'В эту локацию нельзя перейти отсюда.'];
        }

        $target = $this->locations->findLocation($targetLocationId);
        if ($target === null) {
            return ['ok' => false, 'error' => 'not_found', 'message' => 'Локация не найдена.'];
        }

        $this->locations->updateUserLocation($userId, $targetLocationId);
        $questEvent = $this->recordFirstPlayerRouteProgress($userId, $targetLocationId);
        $state = $this->state->currentStateForUser($userId);
        $state['chatEvent'] = [
            'type' => 'move',
            'text' => '->' . ($state['location']['title'] ?? 'Локация'),
        ];
        if ($questEvent !== null) {
            $state['questEvent'] = $questEvent;
        }

        return $state;
    }

    private function recordFirstPlayerRouteProgress(int $userId, int $targetLocationId): ?array
    {
        if ($this->quests === null) {
            return null;
        }

        if ($targetLocationId === 4 && $this->quests->startIfAvailable($userId, 101, 10)) {
            return [
                'questId' => 101,
                'message' => 'Задание обновлено: Дорога 1 найдена. Проведи первый бой с диким покемоном.',
            ];
        }

        if ($targetLocationId === 16) {
            $this->quests->startIfAvailable($userId, 102, 10);
            if ($this->quests->completeIfActive($userId, 102, 20)) {
                $this->rewards?->grantItems($userId, [1 => 3000, 90111 => 1], 'Квест: Путь в Вертанию');
                $this->quests->addQuestRank($userId, 1);
                $this->quests->startIfAvailable($userId, 103, 10);
                $this->rewards?->notify(
                    $userId,
                    'Квест завершён',
                    'Путь в Вертанию завершён. В инвентарь добавлен билет на самолёт для проверки транспорта.',
                    'quest',
                    ['quest_id' => 102, 'next_quest_id' => 103]
                );

                return [
                    'questId' => 102,
                    'nextQuestId' => 103,
                    'message' => 'Квест завершён: Путь в Вертанию. Получен билет на самолёт.',
                ];
            }
        }

        return null;
    }
}
