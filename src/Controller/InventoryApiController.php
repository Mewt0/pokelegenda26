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
        private InventoryRepository $inventory,
        private Session $session,
    ) {
    }

    public function list(Request $request): Response
    {
        $userId = $this->session->get('id');
        if (!$userId) {
            return new Response(json_encode(['ok' => false, 'message' => 'Unauthorized']), 401, ['Content-Type' => 'application/json']);
        }

        $items = $this->inventory->getUserInventory((int) $userId);

        return new Response(json_encode([
            'ok' => true,
            'items' => $items,
        ]), 200, ['Content-Type' => 'application/json']);
    }
}
