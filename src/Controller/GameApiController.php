<?php
declare(strict_types=1);

namespace Pokemon8\Controller;

use Pokemon8\Game\LocationStateService;
use Pokemon8\Game\MapMoveService;
use Pokemon8\Game\WildEncounterService;
use Pokemon8\Game\BattleEngineService;
use Pokemon8\Http\Request;
use Pokemon8\Http\Response;
use Pokemon8\Repository\LocationRepository;
use Pokemon8\Security\Csrf;
use Pokemon8\Security\Session;

final class GameApiController
{
    public function __construct(
        private Session $session,
        private Csrf $csrf,
        private LocationStateService $state,
        private MapMoveService $moves,
        private WildEncounterService $wildEncounter,
        private BattleEngineService $battleEngine,
        private LocationRepository $locations,
    ) {
    }

    public function state(Request $request): Response
    {
        $userId = (int) $this->session->get('id', 0);
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'error' => 'auth'], 401);
        }

        $encounter = $this->wildEncounter->tryStartForUser($userId);
        $worldState = $this->state->currentStateForUser($userId);
        $battleState = $this->battleEngine->state($userId);
        $worldState['battle'] = [
            'active' => (bool) ($battleState['active'] ?? false),
            'id' => (int) ($battleState['battle']['id'] ?? 0),
        ];

        if ($encounter !== null && isset($encounter['wildEncounter'])) {
            $worldState['wildEncounter'] = $encounter['wildEncounter'];
            if (!empty($encounter['wildEncounter']['started'])) {
                $worldState['battle']['active'] = true;
                $worldState['battle']['id'] = (int) ($encounter['wildEncounter']['battleId'] ?? 0);
            }
        }

        return $this->json($worldState);
    }

    public function move(Request $request): Response
    {
        $userId = (int) $this->session->get('id', 0);
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'error' => 'auth', 'message' => 'Нужно войти в игру.'], 401);
        }

        if (!$this->csrf->validate($request->input('_csrf'))) {
            return $this->json(['ok' => false, 'error' => 'csrf', 'message' => 'Сессия устарела. Обнови страницу.'], 419);
        }

        return $this->json($this->moves->move($userId, (int) $request->input('location_id')));
    }

    public function setPveMode(Request $request): Response
    {
        $userId = (int) $this->session->get('id', 0);
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'error' => 'auth', 'message' => 'Нужно войти в игру.'], 401);
        }

        if (!$this->csrf->validate($request->input('_csrf'))) {
            return $this->json(['ok' => false, 'error' => 'csrf', 'message' => 'Сессия устарела. Обнови страницу.'], 419);
        }

        $mode = $request->input('mode');
        if ($mode !== 'on' && $mode !== 'off') {
            return $this->json(['ok' => false, 'error' => 'mode', 'message' => 'Некорректный режим.'], 422);
        }

        $this->locations->setPveButton($userId, $mode === 'on');
        $state = $this->state->currentStateForUser($userId);
        $state['message'] = $mode === 'on'
            ? 'Авто-нападение включено.'
            : 'Авто-нападение выключено.';

        return $this->json($state);
    }

    public function forcePveBattleRoad2(Request $request): Response
    {
        $userId = (int) $this->session->get('id', 0);
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'error' => 'auth', 'message' => 'Нужно войти в игру.'], 401);
        }

        if (!$this->csrf->validate($request->input('_csrf'))) {
            return $this->json(['ok' => false, 'error' => 'csrf', 'message' => 'Сессия устарела. Обнови страницу.'], 419);
        }

        $encounter = $this->wildEncounter->forceStartForUserAtLocation($userId, 4);
        $worldState = $this->state->currentStateForUser($userId);
        $battleState = $this->battleEngine->state($userId);

        $worldState['battle'] = [
            'active' => (bool) ($battleState['active'] ?? false),
            'id' => (int) ($battleState['battle']['id'] ?? 0),
        ];

        if (isset($encounter['wildEncounter'])) {
            $worldState['wildEncounter'] = $encounter['wildEncounter'];
            if (!empty($encounter['wildEncounter']['started'])) {
                $worldState['battle']['active'] = true;
                $worldState['battle']['id'] = (int) ($encounter['wildEncounter']['battleId'] ?? 0);
            }
        }

        $worldState['message'] = $encounter['wildEncounter']['message'] ?? 'Отладочный бой: нет сообщения.';
        return $this->json($worldState);
    }

    private function json(array $payload, int $status = 200): Response
    {
        return new Response(
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}',
            $status,
            ['Content-Type' => 'application/json; charset=UTF-8']
        );
    }
}
