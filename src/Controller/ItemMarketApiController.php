<?php
declare(strict_types=1);

namespace Pokemon8\Controller;

use Pokemon8\Http\Request;
use Pokemon8\Http\Response;
use Pokemon8\Repository\ItemMarketRepository;
use Pokemon8\Security\Csrf;
use Pokemon8\Security\Session;

final class ItemMarketApiController
{
    public function __construct(
        private Session $session,
        private Csrf $csrf,
        private ItemMarketRepository $market,
    ) {
    }

    public function index(Request $request): Response
    {
        $userId = (int) $this->session->get('id', 0);
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'error' => 'auth'], 401);
        }

        $search = $request->input('q');
        return $this->json([
            'ok' => true,
            'catalog' => $this->market->catalog($userId, $search),
            'lots' => $this->market->lots($userId, $search),
            'wallet' => $this->market->wallet($userId),
        ]);
    }

    public function buy(Request $request): Response
    {
        $userId = (int) $this->session->get('id', 0);
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'error' => 'auth'], 401);
        }
        if (!$this->csrf->validate($request->input('_csrf'))) {
            return $this->json(['ok' => false, 'error' => 'csrf', 'message' => 'Сессия устарела. Обновите страницу.'], 419);
        }

        $source = $request->input('source', 'catalog');
        if ($source === 'lot') {
            return $this->json($this->market->buyLot(
                $userId,
                (int) $request->input('lot_id', '0'),
                (int) $request->input('count', '1')
            ));
        }

        return $this->json($this->market->buyCatalogItem(
            $userId,
            (int) $request->input('shop_item_id', '0'),
            (int) $request->input('count', '1')
        ));
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
