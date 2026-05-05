<?php
declare(strict_types=1);

namespace Pokemon8\Controller;

use Pokemon8\Game\GameRoutes;
use Pokemon8\Http\Request;
use Pokemon8\Http\Response;
use Pokemon8\Repository\MessageRepository;
use Pokemon8\Security\Session;
use Pokemon8\View\View;

final class GameModuleController
{
    public function __construct(private Session $session, private ?MessageRepository $messages = null)
    {
    }

    public function show(Request $request, string $slug): Response
    {
        if (!$this->session->get('id')) {
            return Response::redirect('/');
        }

        $module = GameRoutes::module($slug);
        if ($module === null) {
            return new Response(View::render('error', ['message' => 'Игровой раздел не найден.']), 404);
        }

        if ($slug === 'messages' && $this->messages !== null) {
            return new Response(View::render('game-messages', [
                'module' => $module,
                'slug' => $slug,
                'messages' => $this->messages->inboxForUser((int) $this->session->get('id')),
                'modules' => GameRoutes::MODULES,
            ]));
        }

        return new Response(View::render('game-module', [
            'module' => $module,
            'slug' => $slug,
            'modules' => GameRoutes::MODULES,
        ]));
    }
}
