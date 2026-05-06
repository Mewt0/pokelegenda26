<?php
declare(strict_types=1);

namespace Pokemon8\Controller;

use Pokemon8\Http\Request;
use Pokemon8\Http\Response;
use Pokemon8\Repository\AdminRepository;
use Pokemon8\Security\Csrf;
use Pokemon8\Security\Session;
use Pokemon8\View\View;

final class AdminController
{
    public function __construct(
        private Session $session,
        private Csrf $csrf,
        private AdminRepository $admin,
    ) {
    }

    public function index(Request $request): Response
    {
        $userId = (int) $this->session->get('id', 0);
        if ($userId <= 0) {
            return Response::redirect('/');
        }
        if (!$this->admin->canAccess($userId)) {
            return new Response(View::render('error', ['message' => 'Админка доступна только администраторам.']), 403);
        }

        return new Response(View::render('game-admin', [
            'csrf' => $this->csrf->token(),
            'overview' => $this->admin->overview(),
            'legacyModules' => $this->admin->legacyModules(),
        ]));
    }
}
