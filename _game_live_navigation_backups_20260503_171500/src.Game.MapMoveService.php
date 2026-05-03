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
            return ['ok' => false, 'error' => 'bad_target', 'message' => 'РќРµРєРѕСЂСЂРµРєС‚РЅР°СЏ Р»РѕРєР°С†РёСЏ.'];
        }

        $user = $this->locations->findUserState($userId);
        if ($user === null) {
            return ['ok' => false, 'error' => 'auth', 'message' => 'РќСѓР¶РЅРѕ РІРѕР№С‚Рё РІ РёРіСЂСѓ.'];
        }

        if ((int) $user['pvp'] === 1) {
            return ['ok' => false, 'error' => 'in_pvp', 'message' => 'РЎРµР№С‡Р°СЃ РЅРµР»СЊР·СЏ РїРµСЂРµР№С‚Рё: РёРґРµС‚ PvP-Р±РѕР№.'];
        }

        if ((int) $user['pve'] === 1) {
            return ['ok' => false, 'error' => 'in_pve', 'message' => 'РЎРµР№С‡Р°СЃ РЅРµР»СЊР·СЏ РїРµСЂРµР№С‚Рё: РёРґРµС‚ PvE-Р±РѕР№.'];
        }

        if ((int) $user['trade'] > 0) {
            return ['ok' => false, 'error' => 'in_trade', 'message' => 'РЎРµР№С‡Р°СЃ РЅРµР»СЊР·СЏ РїРµСЂРµР№С‚Рё: РёРґРµС‚ РѕР±РјРµРЅ.'];
        }

        $currentLocationId = (int) $user['buildmy'];
        if (!$this->graph->canMove($currentLocationId, $targetLocationId)) {
            return ['ok' => false, 'error' => 'forbidden', 'message' => 'Р’ СЌС‚Сѓ Р»РѕРєР°С†РёСЋ РЅРµР»СЊР·СЏ РїРµСЂРµР№С‚Рё РѕС‚СЃСЋРґР°.'];
        }

        $target = $this->locations->findLocation($targetLocationId);
        if ($target === null) {
            return ['ok' => false, 'error' => 'not_found', 'message' => 'Р›РѕРєР°С†РёСЏ РЅРµ РЅР°Р№РґРµРЅР°.'];
        }

        $this->locations->updateUserLocation($userId, $targetLocationId);
        $state = $this->state->currentStateForUser($userId);
        $state['chatEvent'] = [
            'type' => 'move',
            'text' => '->' . ($state['location']['title'] ?? 'Р›РѕРєР°С†РёСЏ'),
        ];

        return $state;
    }
}
