<?php
declare(strict_types=1);

namespace Pokemon8\Controller;

use Pokemon8\Http\Request;
use Pokemon8\Http\Response;
use Pokemon8\Repository\ProfileRepository;
use Pokemon8\Security\Csrf;
use Pokemon8\Security\Session;
use Pokemon8\View\View;
use Throwable;

final class ProfileController
{
    public function __construct(
        private Session $session,
        private ProfileRepository $profiles,
        private Csrf $csrf,
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

        return new Response(View::render('game-profile', $profile + [
            'csrfToken' => $this->csrf->token(),
        ]));
    }

    public function card(Request $request): Response
    {
        try {
            $viewerId = (int) $this->session->get('id', 0);
            if ($viewerId <= 0) {
                return $this->json(['ok' => false, 'error' => 'auth', 'message' => 'Нужно войти в игру.'], 401);
            }

            $profileId = $this->resolveProfileId($request, $viewerId);
            if ($profileId <= 0) {
                return $this->json(['ok' => false, 'message' => 'Тренер не найден.'], 404);
            }

            $profile = $this->profiles->profile($viewerId, $profileId);
            if ($profile === null) {
                return $this->json(['ok' => false, 'message' => 'Тренер не найден.'], 404);
            }

            return $this->json([
                'ok' => true,
                'profile' => $profile,
            ]);
        } catch (Throwable $e) {
            error_log('[ProfileController] ' . $e::class . ': ' . $e->getMessage());
            return $this->json(['ok' => false, 'message' => 'Профиль временно недоступен.'], 500);
        }
    }

    public function settings(Request $request): Response
    {
        $userId = (int) $this->session->get('id', 0);
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'error' => 'auth', 'message' => 'Нужно войти в игру.'], 401);
        }

        return $this->json([
            'ok' => true,
            'settings' => $this->profiles->settingsForUser($userId),
        ]);
    }

    public function saveSettings(Request $request): Response
    {
        $userId = (int) $this->session->get('id', 0);
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'error' => 'auth', 'message' => 'Нужно войти в игру.'], 401);
        }
        if (!$this->csrf->validate($request->input('_csrf'))) {
            return $this->json(['ok' => false, 'error' => 'csrf', 'message' => 'Сессия устарела. Обновите страницу.'], 419);
        }

        return $this->json($this->profiles->saveSettings($userId, $request->post));
    }

    private function resolveProfileId(Request $request, int $viewerId): int
    {
        $profileLogin = $request->input('user');
        if ($profileLogin === '') {
            $profileLogin = $request->input('login');
        }

        if ($profileLogin !== '') {
            return $this->profiles->idByLogin($profileLogin);
        }

        $profileId = (int) $request->input('id', (string) $viewerId);
        return $profileId > 0 ? $profileId : $viewerId;
    }

    private function json(array $payload, int $status = 200): Response
    {
        return new Response(
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE) ?: '{}',
            $status,
            ['Content-Type' => 'application/json; charset=UTF-8']
        );
    }
}
