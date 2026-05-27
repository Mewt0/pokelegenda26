<?php
declare(strict_types=1);

namespace Pokemon8\Controller;

use Pokemon8\Http\Request;
use Pokemon8\Http\Response;
use Pokemon8\Repository\PokemonMarketRepository;
use Pokemon8\Security\Csrf;
use Pokemon8\Security\Session;

final class PokemonMarketApiController
{
    public function __construct(
        private Session $session,
        private Csrf $csrf,
        private PokemonMarketRepository $market,
    ) {
    }

    public function index(Request $request): Response
    {
        $userId = (int) $this->session->get('id', 0);
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'error' => 'auth', 'message' => 'Нужно войти в игру.'], 401);
        }

        return $this->json($this->market->index(
            $userId,
            (string) $request->input('q', ''),
            max(1, (int) $request->input('page', '1')),
            max(1, (int) $request->input('per_page', '30'))
        ));
    }

    public function createLot(Request $request): Response
    {
        $userId = (int) $this->session->get('id', 0);
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'error' => 'auth', 'message' => 'Нужно войти в игру.'], 401);
        }
        if (!$this->csrf->validate($request->input('_csrf'))) {
            return $this->json(['ok' => false, 'error' => 'csrf', 'message' => 'Сессия устарела. Обновите страницу.'], 419);
        }

        return $this->json($this->market->listPokemon(
            $userId,
            (int) $request->input('pokemon_id', '0'),
            (int) $request->input('price', '0'),
            (string) $request->input('private_to', '')
        ));
    }

    public function buy(Request $request): Response
    {
        $userId = (int) $this->session->get('id', 0);
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'error' => 'auth', 'message' => 'Нужно войти в игру.'], 401);
        }
        if (!$this->csrf->validate($request->input('_csrf'))) {
            return $this->json(['ok' => false, 'error' => 'csrf', 'message' => 'Сессия устарела. Обновите страницу.'], 419);
        }

        return $this->json($this->market->buy($userId, (int) $request->input('lot_id', '0')));
    }

    public function cancel(Request $request): Response
    {
        $userId = (int) $this->session->get('id', 0);
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'error' => 'auth', 'message' => 'Нужно войти в игру.'], 401);
        }
        if (!$this->csrf->validate($request->input('_csrf'))) {
            return $this->json(['ok' => false, 'error' => 'csrf', 'message' => 'Сессия устарела. Обновите страницу.'], 419);
        }

        return $this->json($this->market->cancel($userId, (int) $request->input('lot_id', '0')));
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
