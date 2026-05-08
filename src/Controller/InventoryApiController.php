<?php
declare(strict_types=1);

namespace Pokemon8\Controller;

use Pokemon8\Http\Request;
use Pokemon8\Http\Response;
use Pokemon8\Repository\InventoryRepository;
use Pokemon8\Repository\TrainingRepository;
use Pokemon8\Security\Csrf;
use Pokemon8\Security\Session;

final class InventoryApiController
{
    public function __construct(
        private Session $session,
        private InventoryRepository $inventory,
        private Csrf $csrf,
        private ?TrainingRepository $training = null,
    ) {
    }

    public function page(Request $request): Response
    {
        $userId = (int) $this->session->get('id', 0);
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'error' => 'auth', 'message' => 'Нужно войти в игру.'], 401);
        }

        $category = (string) $request->input('category', '');
        $query = (string) $request->input('q', (string) $request->input('query', ''));
        $perPage = 60;
        $total = $this->inventory->countForUser($userId, $category, $query);
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
            'category' => $category,
            'query' => $query,
            'categories' => [
                ['key' => '', 'label' => 'Все'],
                ['key' => 'balls', 'label' => 'Покеболы'],
                ['key' => 'tm', 'label' => 'ТМ'],
                ['key' => 'eggs', 'label' => 'Яйца'],
                ['key' => 'evolution', 'label' => 'Эволюция'],
                ['key' => 'consumables', 'label' => 'Расходники'],
                ['key' => 'quest', 'label' => 'Квестовые'],
                ['key' => 'drop', 'label' => 'Дроп'],
                ['key' => 'other', 'label' => 'Прочее'],
            ],
            'items' => $this->inventory->listForUser($userId, $perPage, $offset, $category, $query),
            'pokemon' => $this->inventory->listActivePokemonForUser($userId),
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

    public function equip(Request $request): Response
    {
        $userId = (int) $this->session->get('id', 0);
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'error' => 'auth', 'message' => 'Нужно войти в игру.'], 401);
        }

        if (!$this->csrf->validate($request->input('_csrf'))) {
            return $this->json(['ok' => false, 'error' => 'csrf', 'message' => 'Сессия устарела. Обновите страницу.'], 419);
        }

        return $this->json($this->inventory->equipItemToPokemon(
            $userId,
            (int) $request->input('item_user_id', '0'),
            (int) $request->input('pokemon_id', '0')
        ));
    }

    public function unequip(Request $request): Response
    {
        $userId = (int) $this->session->get('id', 0);
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'error' => 'auth', 'message' => 'Нужно войти в игру.'], 401);
        }

        if (!$this->csrf->validate($request->input('_csrf'))) {
            return $this->json(['ok' => false, 'error' => 'csrf', 'message' => 'Сессия устарела. Обновите страницу.'], 419);
        }

        return $this->json($this->inventory->unequipPokemonItem(
            $userId,
            (int) $request->input('pokemon_id', '0')
        ));
    }

    public function useTarget(Request $request): Response
    {
        $userId = (int) $this->session->get('id', 0);
        if ($userId <= 0) {
            return $this->json(['ok' => false, 'error' => 'auth', 'message' => 'Нужно войти в игру.'], 401);
        }

        if (!$this->csrf->validate($request->input('_csrf'))) {
            return $this->json(['ok' => false, 'error' => 'csrf', 'message' => 'Сессия устарела. Обновите страницу.'], 419);
        }

        $itemUserId = (int) $request->input('item_user_id', '0');
        $pokemonId = (int) $request->input('pokemon_id', '0');
        $itemId = $this->inventory->itemIdForInventoryRow($userId, $itemUserId);

        if ($this->training !== null && $itemId === TrainingRepository::TRAINING_ITEM_ID) {
            return $this->json($this->training->train($userId, $pokemonId));
        }
        if ($this->training !== null && $itemId === TrainingRepository::WEAKENING_ITEM_ID) {
            return $this->json($this->training->weaken($userId, $pokemonId));
        }

        return $this->json($this->inventory->useTargetedItem(
            $userId,
            $itemUserId,
            $pokemonId,
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
