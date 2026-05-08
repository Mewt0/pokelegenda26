<?php
declare(strict_types=1);

namespace Pokemon8\Controller;

use Pokemon8\Http\Request;
use Pokemon8\Http\Response;
use Pokemon8\Repository\AdminRepository;
use Pokemon8\Repository\UserRepository;
use Pokemon8\Security\Csrf;
use Pokemon8\Security\Session;
use Pokemon8\View\View;

final class GameController
{
    public function __construct(
        private Session $session,
        private Csrf $csrf,
        private UserRepository $users,
        private AdminRepository $admin,
    ) {
    }

    public function start(Request $request): Response
    {
        // Пока это безопасная стартовая заглушка. Карта, чат и бой будут отдельными контроллерами.
        if (!$this->session->get('id')) {
            return Response::redirect('/');
        }

        $userId = (int) $this->session->get('id', 0);
        $state = $this->users->findStateById($userId);
        $isAdmin = $this->admin->canAccess($userId);

        return new Response(View::render('game-start', [
            'login' => $this->session->get('login'),
            'userId' => $userId,
            'csrf' => $this->csrf->token(),
            'isAdmin' => $isAdmin,
        ]));
    }
}
