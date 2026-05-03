<?php
declare(strict_types=1);

namespace Pokemon8\Controller;

use Pokemon8\Http\Request;
use Pokemon8\Http\Response;
use Pokemon8\Security\Session;
use Pokemon8\View\View;

final readonly class GameController
{
    public function __construct(private Session $session)
    {
    }

    public function start(Request $request): Response
    {
        // Пока это безопасная стартовая заглушка. Карта, чат и бой будут отдельными контроллерами.
        if (!$this->session->get('id')) {
            return Response::redirect('/');
        }

        return new Response(View::render('game-start', [
            'login' => $this->session->get('login'),
        ]));
    }
}
