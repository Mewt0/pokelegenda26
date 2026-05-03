<?php
declare(strict_types=1);

namespace Pokemon8\Controller;

use Pokemon8\Http\Request;
use Pokemon8\Http\Response;
use Pokemon8\Repository\InventoryRepository;
use Pokemon8\Security\Session;

final class InventoryApiController
{
    public function __construct(
        private Session $session,
        private InventoryRepository $inventory,
    ) {
    }

    public function page(Request $request): Response
    {
        $userId = (int) $this->session->get('id', 0);
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'error' => 'auth', 'message' => 'Нужно войти в игру.'], 401);
        }

        $perPage = 60;
        $total = $this->inventory->countForUser($userId);
        $pages = max(1, (int) ceil($total / $perPage));
        $page = max(1, (int) $request->input('page', '1'));
        $page = min($page, $pages);
        $offset = ($page - 1) * $perPage;

        return $this->json([
            'ok' => true,
            'page' => $page,
            'pages' => $pages,
            'perPage' => $perPage,
            'total' => $total,
            'items' => $this->inventory->listForUser($userId, $perPage, $offset),
        ]);
    }

    public function battle(Request $request): Response
    {
        $userId = (int) $this->session->get('id', 0);
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'error' => 'auth', 'message' => 'auth'], 401);
        }

        return $this->json([
            'ok' => true,
            'items' => $this->inventory->listBattleItemsForUser($userId),
        ]);
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
