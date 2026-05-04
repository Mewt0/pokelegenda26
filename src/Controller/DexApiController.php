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
        return $this->json(['ok' => true, 'items' => $this->dex->searchPokemon((string) $request->input('q', ''))]);
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
        return $this->json(['ok' => true, 'items' => $this->dex->searchAttacks((string) $request->input('q', ''))]);
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
