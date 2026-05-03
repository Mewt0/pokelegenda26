<?php
declare(strict_types=1);

namespace Pokemon8\Controller;

use Pokemon8\Game\BattleEngineService;
use Pokemon8\Http\Request;
use Pokemon8\Http\Response;
use Pokemon8\Security\Csrf;
use Pokemon8\Security\Session;

final class PveBattleApiController
{
    public function __construct(
        private Session $session,
        private Csrf $csrf,
        private BattleEngineService $battleEngine,
    ) {
    }

    public function state(Request $request): Response
    {
        $userId = (int) $this->session->get('id', 0);
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'active' => false, 'error' => 'auth'], 401);
        }

        return $this->json($this->battleEngine->state($userId));
    }

    public function action(Request $request): Response
    {
        $userId = (int) $this->session->get('id', 0);
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'active' => false, 'error' => 'auth'], 401);
        }

        if (!$this->csrf->validate($request->input('_csrf'))) {
            return $this->json(['ok' => false, 'active' => true, 'error' => 'csrf'], 419);
        }

        $action = trim($request->input('action', ''));
        $payload = [
            'move_id' => (int) $request->input('move_id', '0'),
            'pokemon_id' => (int) $request->input('pokemon_id', '0'),
            'item_user_id' => (int) $request->input('item_user_id', '0'),
        ];

        return $this->json($this->battleEngine->action($userId, $action, $payload));
    }

    public function ackEnd(Request $request): Response
    {
        $userId = (int) $this->session->get('id', 0);
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'active' => false, 'error' => 'auth'], 401);
        }

        if (!$this->csrf->validate($request->input('_csrf'))) {
            return $this->json(['ok' => false, 'active' => false, 'error' => 'csrf'], 419);
        }

        return $this->json($this->battleEngine->acknowledgeEnd($userId));
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
