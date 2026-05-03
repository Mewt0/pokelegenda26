<?php
declare(strict_types=1);

namespace Pokemon8\Game;

use Pokemon8\Repository\LocationRepository;

final class MapMoveService
{
    public function __construct(
        private LocationRepository $locations,
        private LocationGraph $graph,
        private LocationStateService $state,
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
        $state = $this->state->currentStateForUser($userId);
        $state['chatEvent'] = [
            'type' => 'move',
            'text' => '->' . ($state['location']['title'] ?? 'Локация'),
        ];

        return $state;
    }
}
