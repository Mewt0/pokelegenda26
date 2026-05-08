<?php
declare(strict_types=1);

namespace Pokemon8\Controller;

use Pokemon8\Game\BattleEngineService;
use Pokemon8\Http\Request;
use Pokemon8\Http\Response;
use Pokemon8\Security\Csrf;
use Pokemon8\Security\Session;

final class PvpBattleApiController
{
    public function __construct(
        private Session $session,
        private Csrf $csrf,
        private BattleEngineService $battleEngine,
    ) {
    }

    public function status(Request $request): Response
    {
        $userId = $this->userId();
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'error' => 'auth'], 401);
        }

        return $this->json($this->battleEngine->pvpStatus($userId, (int) $request->input('user_id', '0')));
    }

    public function requests(Request $request): Response
    {
        $userId = $this->userId();
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'error' => 'auth'], 401);
        }

        return $this->json($this->battleEngine->pvpRequests($userId));
    }

    public function pokemonOptions(Request $request): Response
    {
        $userId = $this->userId();
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'error' => 'auth'], 401);
        }

        return $this->json($this->battleEngine->pvpPokemonOptions($userId));
    }

    public function history(Request $request): Response
    {
        $userId = $this->userId();
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'error' => 'auth'], 401);
        }

        return $this->json($this->battleEngine->battleHistory($userId));
    }

    public function request(Request $request): Response
    {
        $userId = $this->userId();
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'error' => 'auth'], 401);
        }
        if (!$this->csrf->validate($request->input('_csrf'))) {
            return $this->json(['ok' => false, 'error' => 'csrf', 'message' => 'Сессия устарела. Обнови страницу.'], 419);
        }

        return $this->json($this->battleEngine->requestPvp(
            $userId,
            (int) $request->input('user_id', '0'),
            (int) $request->input('pokemon_id', '0')
        ));
    }

    public function force(Request $request): Response
    {
        $userId = $this->userId();
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'error' => 'auth'], 401);
        }
        if (!$this->csrf->validate($request->input('_csrf'))) {
            return $this->json(['ok' => false, 'error' => 'csrf', 'message' => 'Сессия устарела. Обнови страницу.'], 419);
        }

        return $this->json($this->battleEngine->forcePvp(
            $userId,
            (int) $request->input('user_id', '0'),
            (int) $request->input('pokemon_id', '0')
        ));
    }

    public function accept(Request $request): Response
    {
        $userId = $this->userId();
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'error' => 'auth'], 401);
        }
        if (!$this->csrf->validate($request->input('_csrf'))) {
            return $this->json(['ok' => false, 'error' => 'csrf', 'message' => 'Сессия устарела. Обнови страницу.'], 419);
        }

        return $this->json($this->battleEngine->acceptPvp(
            $userId,
            (int) $request->input('request_id', '0'),
            (int) $request->input('pokemon_id', '0')
        ));
    }

    public function decline(Request $request): Response
    {
        $userId = $this->userId();
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'error' => 'auth'], 401);
        }
        if (!$this->csrf->validate($request->input('_csrf'))) {
            return $this->json(['ok' => false, 'error' => 'csrf', 'message' => 'Сессия устарела. Обнови страницу.'], 419);
        }

        return $this->json($this->battleEngine->declinePvp($userId, (int) $request->input('request_id', '0')));
    }

    private function userId(): int
    {
        return (int) $this->session->get('id', 0);
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
