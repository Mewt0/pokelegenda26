<?php
declare(strict_types=1);

namespace Pokemon8\Controller;

use Pokemon8\Http\Request;
use Pokemon8\Http\Response;
use Pokemon8\Repository\ProfileRepository;
use Pokemon8\Security\Session;
use Pokemon8\View\View;

final class ProfileController
{
    public function __construct(
        private Session $session,
        private ProfileRepository $profiles,
    ) {
    }

    public function show(Request $request): Response
    {
        $viewerId = (int) $this->session->get('id', 0);
        if ($viewerId <= 0) {
            return Response::redirect('/');
        }

        $profileLogin = $request->input('user');
        if ($profileLogin !== '') {
            $profileId = $this->profiles->idByLogin($profileLogin);
        } else {
            $profileId = (int) $request->input('id', (string) $viewerId);
            if ($profileId <= 0) {
                $profileId = $viewerId;
            }
        }

        $profile = $this->profiles->profile($viewerId, $profileId);
        if ($profile === null) {
            return new Response(View::render('error', ['message' => 'Тренер не найден.']), 404);
        }

        return new Response(View::render('game-profile', $profile));
    }
}
