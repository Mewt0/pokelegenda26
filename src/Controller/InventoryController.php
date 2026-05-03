<?php
declare(strict_types=1);

namespace Pokemon8\Controller;

use Pokemon8\Http\Request;
use Pokemon8\Http\Response;
use Pokemon8\Repository\InventoryRepository;
use Pokemon8\Security\Csrf;
use Pokemon8\Security\Session;
use Pokemon8\View\View;

final class InventoryController
{
    public function __construct(
        private Session $session,
        private InventoryRepository $inventory,
        private Csrf $csrf,
    ) {
    }

    public function index(Request $request): Response
    {
        $userId = (int) $this->session->get('id', 0);
        if ($userId <= 0) {
            return Response::redirect('/');
        }

        $total = $this->inventory->countForUser($userId);
        $pokemons = $this->inventory->listActivePokemonForUser($userId);

        return new Response(View::render('game-items', [
            'total' => $total,
            'pokemons' => $pokemons,
            'csrf' => $this->csrf->token(),
        ]));
    }
}
