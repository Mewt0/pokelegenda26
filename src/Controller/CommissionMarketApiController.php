<?php
declare(strict_types=1);

namespace Pokemon8\Controller;

use Pokemon8\Http\Request;
use Pokemon8\Http\Response;
use Pokemon8\Repository\AdminRepository;
use Pokemon8\Repository\CommissionMarketRepository;
use Pokemon8\Security\Csrf;
use Pokemon8\Security\Session;

final class CommissionMarketApiController
{
    public function __construct(
        private Session $session,
        private Csrf $csrf,
        private CommissionMarketRepository $market,
        private ?AdminRepository $admin = null,
    ) {
    }

    public function lots(Request $request): Response
    {
        $userId = $this->userId();
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'error' => 'auth', 'message' => 'Нужно войти в игру.'], 401);
        }

        return $this->json($this->market->lots($userId, [
            'category' => (string) $request->input('category', 'all'),
            'q' => (string) $request->input('q', ''),
            'sort' => (string) $request->input('sort', 'new'),
            'page' => (int) $request->input('page', '1'),
            'per_page' => (int) $request->input('per_page', '40'),
        ]));
    }

    public function sellable(Request $request): Response
    {
        $userId = $this->userId();
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'error' => 'auth', 'message' => 'Нужно войти в игру.'], 401);
        }

        return $this->json($this->market->sellable($userId));
    }

    public function my(Request $request): Response
    {
        $userId = $this->userId();
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'error' => 'auth', 'message' => 'Нужно войти в игру.'], 401);
        }

        return $this->json($this->market->myLots($userId));
    }

    public function create(Request $request): Response
    {
        $userId = $this->userId();
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'error' => 'auth', 'message' => 'Нужно войти в игру.'], 401);
        }
        if (!$this->csrf->validate($request->input('_csrf'))) {
            return $this->json(['ok' => false, 'error' => 'csrf', 'message' => 'Сессия устарела. Обновите страницу.'], 419);
        }

        return $this->json($this->market->createLot($userId, $request->post));
    }

    public function buy(Request $request): Response
    {
        $userId = $this->userId();
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'error' => 'auth', 'message' => 'Нужно войти в игру.'], 401);
        }
        if (!$this->csrf->validate($request->input('_csrf'))) {
            return $this->json(['ok' => false, 'error' => 'csrf', 'message' => 'Сессия устарела. Обновите страницу.'], 419);
        }

        return $this->json($this->market->buy($userId, (int) $request->input('lot_id', '0')));
    }

    public function cancel(Request $request): Response
    {
        $userId = $this->userId();
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'error' => 'auth', 'message' => 'Нужно войти в игру.'], 401);
        }
        if (!$this->csrf->validate($request->input('_csrf'))) {
            return $this->json(['ok' => false, 'error' => 'csrf', 'message' => 'Сессия устарела. Обновите страницу.'], 419);
        }

        return $this->json($this->market->cancel($userId, (int) $request->input('lot_id', '0')));
    }

    public function adminSettings(Request $request): Response
    {
        if (!$this->isAdmin()) {
            return $this->json(['ok' => false, 'error' => 'forbidden'], 403);
        }

        return $this->json($this->market->adminSettings());
    }

    public function saveAdminSettings(Request $request): Response
    {
        $adminId = $this->isAdmin() ? $this->userId() : 0;
        if ($adminId <= 0) {
            return $this->json(['ok' => false, 'error' => 'forbidden'], 403);
        }
        if (!$this->csrf->validate($request->input('_csrf'))) {
            return $this->json(['ok' => false, 'error' => 'csrf', 'message' => 'Сессия устарела. Обновите страницу.'], 419);
        }

        return $this->json($this->market->saveAdminSettings($adminId, $request->post));
    }

    private function userId(): int
    {
        return (int) $this->session->get('id', 0);
    }

    private function isAdmin(): bool
    {
        $userId = $this->userId();
        return $userId > 0 && $this->admin !== null && $this->admin->canAccess($userId);
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
