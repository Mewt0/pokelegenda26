<?php
declare(strict_types=1);

namespace Pokemon8\Controller;

use Pokemon8\Http\Request;
use Pokemon8\Http\Response;
use Pokemon8\Repository\DexRepository;
use Pokemon8\Security\Session;

final class DexApiController
{
    public function __construct(private Session $session, private DexRepository $dex)
    {
    }

    public function pokemonList(Request $request): Response
    {
        if (!$this->session->get('id')) {
            return $this->json(['ok' => false, 'error' => 'auth'], 401);
        }
        $limit = (int) $request->input('limit', '1000');
        $filters = [
            'type' => (string) $request->input('type', ''),
            'generation' => (string) $request->input('generation', ''),
            'form' => (string) $request->input('form', ''),
        ];
        return $this->json(['ok' => true, 'items' => $this->dex->searchPokemon((string) $request->input('q', ''), $limit, $filters)]);
    }

    public function pokemon(Request $request): Response
    {
        if (!$this->session->get('id')) {
            return $this->json(['ok' => false, 'error' => 'auth'], 401);
        }
        $id = (int) $request->input('id', '0');
        $pokemon = $this->dex->pokemon($id);
        return $pokemon ? $this->json(['ok' => true, 'pokemon' => $pokemon]) : $this->json(['ok' => false, 'message' => 'Покемон не найден.'], 404);
    }

    public function attackList(Request $request): Response
    {
        if (!$this->session->get('id')) {
            return $this->json(['ok' => false, 'error' => 'auth'], 401);
        }
        $limit = (int) $request->input('limit', '1200');
        $filters = [
            'type' => (string) $request->input('type', ''),
            'category' => (string) $request->input('category', ''),
            'power_min' => (string) $request->input('power_min', ''),
            'power_max' => (string) $request->input('power_max', ''),
            'accuracy_min' => (string) $request->input('accuracy_min', ''),
            'accuracy_max' => (string) $request->input('accuracy_max', ''),
            'pokemon' => (string) $request->input('pokemon', ''),
            'tm' => (string) $request->input('tm', ''),
        ];
        return $this->json(['ok' => true, 'items' => $this->dex->searchAttacks((string) $request->input('q', ''), $limit, $filters)]);
    }

    public function attack(Request $request): Response
    {
        if (!$this->session->get('id')) {
            return $this->json(['ok' => false, 'error' => 'auth'], 401);
        }
        $id = (int) $request->input('id', '0');
        $attack = $this->dex->attack($id);
        return $attack ? $this->json(['ok' => true, 'attack' => $attack]) : $this->json(['ok' => false, 'message' => 'Атака не найдена.'], 404);
    }

    private function json(array $payload, int $status = 200): Response
    {
        return new Response(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}', $status, ['Content-Type' => 'application/json; charset=UTF-8']);
    }
}
