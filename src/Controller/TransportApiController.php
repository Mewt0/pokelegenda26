<?php
declare(strict_types=1);

namespace Pokemon8\Controller;

use Pokemon8\Http\Request;
use Pokemon8\Http\Response;
use Pokemon8\Repository\TransportRepository;
use Pokemon8\Security\Csrf;
use Pokemon8\Security\Session;

final class TransportApiController
{
    public function __construct(
        private Session $session,
        private Csrf $csrf,
        private TransportRepository $transport,
    ) {
    }

    public function routes(Request $request): Response
    {
        $userId = (int) $this->session->get('id', 0);
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'error' => 'auth'], 401);
        }

        return $this->json(['ok' => true, 'routes' => $this->transport->routesForUser($userId)]);
    }

    public function flightRoutes(Request $request): Response
    {
        $userId = (int) $this->session->get('id', 0);
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'error' => 'auth'], 401);
        }

        return $this->json(['ok' => true, 'routes' => $this->transport->planeDestinationsForUser($userId)]);
    }

    public function flightStatus(Request $request): Response
    {
        $userId = (int) $this->session->get('id', 0);
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'error' => 'auth'], 401);
        }

        return $this->json($this->transport->flightStatus($userId));
    }

    public function travel(Request $request): Response
    {
        $userId = (int) $this->session->get('id', 0);
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'error' => 'auth'], 401);
        }
        if (!$this->csrf->validate($request->input('_csrf'))) {
            return $this->json(['ok' => false, 'error' => 'csrf', 'message' => 'Сессия устарела. Обновите страницу.'], 419);
        }

        return $this->json($this->transport->travel($userId, (int) $request->input('route_id', '0')));
    }

    public function flightStart(Request $request): Response
    {
        $userId = (int) $this->session->get('id', 0);
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'error' => 'auth'], 401);
        }
        if (!$this->csrf->validate($request->input('_csrf'))) {
            return $this->json(['ok' => false, 'error' => 'csrf', 'message' => 'Сессия устарела. Обновите страницу.'], 419);
        }

        return $this->json($this->transport->startFlight(
            $userId,
            (int) $request->input('item_user_id', '0'),
            (int) $request->input('route_id', '0')
        ));
    }

    public function flightExit(Request $request): Response
    {
        $userId = (int) $this->session->get('id', 0);
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'error' => 'auth'], 401);
        }
        if (!$this->csrf->validate($request->input('_csrf'))) {
            return $this->json(['ok' => false, 'error' => 'csrf', 'message' => 'Сессия устарела. Обновите страницу.'], 419);
        }

        return $this->json($this->transport->exitFlight($userId));
    }

    private function json(array $payload, int $status = 200): Response
    {
        return new Response(
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}',
            $status,
            ['Content-Type' => 'application/json; charset=UTF-8']
        );
    }
}
