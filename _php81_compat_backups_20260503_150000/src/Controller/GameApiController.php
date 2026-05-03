<?php
declare(strict_types=1);

namespace Pokemon8\Controller;

use Pokemon8\Game\LocationStateService;
use Pokemon8\Game\MapMoveService;
use Pokemon8\Http\Request;
use Pokemon8\Http\Response;
use Pokemon8\Security\Csrf;
use Pokemon8\Security\Session;

final readonly class GameApiController
{
    public function __construct(
        private Session $session,
        private Csrf $csrf,
        private LocationStateService $state,
        private MapMoveService $moves,
    ) {
    }

    public function state(Request $request): Response
    {
        $userId = (int) $this->session->get('id', 0);
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'error' => 'auth'], 401);
        }

        return $this->json($this->state->currentStateForUser($userId));
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

    private function json(array $payload, int $status = 200): Response
    {
        return new Response(
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}',
            $status,
            ['Content-Type' => 'application/json; charset=UTF-8']
        );
    }
}
