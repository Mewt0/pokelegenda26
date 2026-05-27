<?php
declare(strict_types=1);

namespace Pokemon8\Controller;

use Pokemon8\Http\Request;
use Pokemon8\Http\Response;
use Pokemon8\Repository\PokemonRepository;
use Pokemon8\Repository\TrainingRepository;
use Pokemon8\Security\Csrf;
use Pokemon8\Security\Session;

final class PokemonApiController
{
    public function __construct(
        private Session $session,
        private Csrf $csrf,
        private PokemonRepository $pokemon,
        private TrainingRepository $training,
    ) {
    }

    public function moves(Request $request): Response
    {
        $userId = (int) $this->session->get('id', 0);
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'error' => 'auth'], 401);
        }

        return $this->json(['ok' => true, 'pokemon' => $this->pokemon->moveEditorData($userId)]);
    }

    public function setMove(Request $request): Response
    {
        $userId = (int) $this->session->get('id', 0);
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'error' => 'auth'], 401);
        }

        if (!$this->csrf->validate($request->input('_csrf'))) {
            return $this->json(['ok' => false, 'error' => 'csrf', 'message' => 'Сессия устарела. Обновите страницу.'], 419);
        }

        $result = $this->pokemon->setMove(
            $userId,
            (int) $request->input('pokemon_id', '0'),
            strtolower($request->input('slot', '')),
            (int) $request->input('move_id', '0')
        );

        return $this->json($result);
    }

    public function training(Request $request): Response
    {
        $userId = (int) $this->session->get('id', 0);
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'error' => 'auth'], 401);
        }

        if (!$this->csrf->validate($request->input('_csrf'))) {
            return $this->json(['ok' => false, 'error' => 'csrf', 'message' => 'Сессия устарела. Обновите страницу.'], 419);
        }

        $action = (string) $request->input('action', 'train');
        $pokemonId = (int) $request->input('pokemon_id', '0');
        $result = $action === 'weaken'
            ? $this->training->weaken($userId, $pokemonId)
            : $this->training->train($userId, $pokemonId, (bool) (int) $request->input('boosted', '0'));

        return $this->json($result);
    }

    public function nursery(Request $request): Response
    {
        $userId = (int) $this->session->get('id', 0);
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'error' => 'auth'], 401);
        }

        if (!$this->csrf->validate($request->input('_csrf'))) {
            return $this->json(['ok' => false, 'error' => 'csrf', 'message' => 'Сессия устарела. Обновите страницу.'], 419);
        }

        return $this->json($this->pokemon->nurseryAction(
            $userId,
            (int) $request->input('pokemon_id', '0'),
            strtolower((string) $request->input('action', ''))
        ));
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
