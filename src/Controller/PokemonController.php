<?php
declare(strict_types=1);

namespace Pokemon8\Controller;

use Pokemon8\Http\Request;
use Pokemon8\Http\Response;
use Pokemon8\Security\Csrf;
use Pokemon8\Security\Session;
use Pokemon8\View\View;

final class PokemonController
{
    public function __construct(
        private Session $session,
        private Csrf $csrf,
    ) {
    }

    public function index(Request $request): Response
    {
        if (!$this->session->get('id')) {
            return Response::redirect('/');
        }

        return new Response(View::render('game-pokemon', [
            'csrf' => $this->csrf->token(),
        ]));
    }
}
