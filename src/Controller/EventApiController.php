<?php
declare(strict_types=1);

namespace Pokemon8\Controller;

use Pokemon8\Http\Request;
use Pokemon8\Http\Response;
use Pokemon8\Repository\GameEventRepository;
use Pokemon8\Security\Session;

final class EventApiController
{
    public function __construct(
        private Session $session,
        private GameEventRepository $events,
    ) {
    }

    public function index(Request $request): Response
    {
        $userId = (int) $this->session->get('id', 0);
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'error' => 'auth', 'message' => 'Нужно войти в игру.'], 401);
        }

        return $this->json(['ok' => true] + $this->events->dashboardForUser($userId));
    }

    public function active(Request $request): Response
    {
        $userId = (int) $this->session->get('id', 0);
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'error' => 'auth', 'message' => 'Нужно войти в игру.'], 401);
        }

        return $this->json(['ok' => true] + $this->events->activePayload($userId));
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
