<?php
declare(strict_types=1);

namespace Pokemon8\Controller;

use Pokemon8\Http\Request;
use Pokemon8\Http\Response;
use Pokemon8\Repository\TournamentRepository;
use Pokemon8\Security\Csrf;
use Pokemon8\Security\Session;

final class TournamentApiController
{
    public function __construct(
        private Session $session,
        private Csrf $csrf,
        private TournamentRepository $tournaments,
    ) {
    }

    public function index(Request $request): Response
    {
        $userId = $this->userId();
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'error' => 'auth', 'message' => 'Нужно войти в игру.'], 401);
        }

        return $this->json($this->tournaments->dashboardForUser($userId));
    }

    public function register(Request $request): Response
    {
        return $this->mutate($request, fn (int $userId): array => $this->tournaments->register(
            $userId,
            (int) $request->input('tournament_id', '0')
        ));
    }

    public function cancel(Request $request): Response
    {
        return $this->mutate($request, fn (int $userId): array => $this->tournaments->cancel(
            $userId,
            (int) $request->input('tournament_id', '0')
        ));
    }

    public function enterArena(Request $request): Response
    {
        return $this->mutate($request, fn (int $userId): array => $this->tournaments->enterArena(
            $userId,
            (int) $request->input('tournament_id', '0')
        ));
    }

    public function leaveArena(Request $request): Response
    {
        return $this->mutate($request, fn (int $userId): array => $this->tournaments->leaveArena(
            $userId,
            (int) $request->input('tournament_id', '0')
        ));
    }

    public function claimReward(Request $request): Response
    {
        return $this->mutate($request, fn (int $userId): array => $this->tournaments->claimReward(
            $userId,
            (int) $request->input('tournament_id', '0')
        ));
    }

    private function mutate(Request $request, callable $callback): Response
    {
        $userId = $this->userId();
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'error' => 'auth', 'message' => 'Нужно войти в игру.'], 401);
        }
        if (!$this->csrf->validate($request->input('_csrf'))) {
            return $this->json(['ok' => false, 'error' => 'csrf', 'message' => 'Сессия устарела. Обновите страницу.'], 419);
        }

        return $this->json($callback($userId));
    }

    private function userId(): int
    {
        return (int) $this->session->get('id', 0);
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
