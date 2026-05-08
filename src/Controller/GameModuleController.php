<?php
declare(strict_types=1);

namespace Pokemon8\Controller;

use Pokemon8\Game\GameRoutes;
use Pokemon8\Http\Request;
use Pokemon8\Http\Response;
use Pokemon8\Repository\ItemMarketRepository;
use Pokemon8\Repository\MessageRepository;
use Pokemon8\Repository\TrainingRepository;
use Pokemon8\Repository\TransportRepository;
use Pokemon8\Security\Csrf;
use Pokemon8\Security\Session;
use Pokemon8\View\View;

final class GameModuleController
{
    public function __construct(
        private Session $session,
        private Csrf $csrf,
        private ?TransportRepository $transport = null,
        private ?MessageRepository $messages = null,
        private ?ItemMarketRepository $itemMarket = null,
    ) {
    }

    public function show(Request $request, string $slug): Response
    {
        if (!$this->session->get('id')) {
            return Response::redirect('/');
        }

        $module = GameRoutes::module($slug);
        if ($module === null) {
            return new Response(View::render('error', ['message' => 'Игровой раздел не найден.']), 404);
        }

        if ($slug === 'messages' && $this->messages !== null) {
            $userId = (int) $this->session->get('id');
            return new Response(View::render('game-messages', [
                'module' => $module,
                'slug' => $slug,
                'csrf' => $this->csrf->token(),
                'messages' => $this->messages->inboxForUser($userId),
                'sent' => $this->messages->sentForUser($userId),
                'archive' => $this->messages->archiveForUser($userId),
                'unread' => $this->messages->unreadCount($userId),
                'prefillRecipient' => $request->input('mail_to', $request->input('to', '')),
                'modules' => GameRoutes::MODULES,
            ]));
        }

        if ($slug === 'market/items' && $this->itemMarket !== null) {
            $userId = (int) $this->session->get('id');
            return new Response(View::render('game-market-items', [
                'module' => $module,
                'slug' => $slug,
                'csrf' => $this->csrf->token(),
                'catalog' => $this->itemMarket->catalog($userId),
                'lots' => $this->itemMarket->lots($userId),
                'wallet' => $this->itemMarket->wallet($userId),
                'modules' => GameRoutes::MODULES,
            ]));
        }

        if ($slug === 'diamond-shop') {
            return new Response(View::render('game-training-shop', [
                'module' => $module,
                'slug' => $slug,
                'shop' => 'diamond',
                'csrf' => $this->csrf->token(),
                'items' => [
                    [
                        'id' => TrainingRepository::TRAINING_ITEM_ID,
                        'name' => 'Набор тренировки',
                        'description' => 'Повышает стадию тренировки монстра. Стат выбирается случайно, HP не участвует.',
                        'image' => '/public/img/items/330.png',
                    ],
                    [
                        'id' => TrainingRepository::WEAKENING_ITEM_ID,
                        'name' => 'Набор ослабления',
                        'description' => 'Понижает стадию на 1, сохраняет текущий стат и делает монстра прирученным.',
                        'image' => '/public/img/items/678.png',
                    ],
                ],
                'currency' => ['name' => 'алмазов', 'itemId' => 2, 'price' => 10],
                'modules' => GameRoutes::MODULES,
            ]));
        }

        if ($slug === 'transport' && $this->transport !== null) {
            return new Response(View::render('game-transport', [
                'module' => $module,
                'slug' => $slug,
                'csrf' => $this->csrf->token(),
                'routes' => $this->transport->routesForUser((int) $this->session->get('id')),
                'modules' => GameRoutes::MODULES,
            ]));
        }

        return new Response(View::render('game-module', [
            'module' => $module,
            'slug' => $slug,
            'modules' => GameRoutes::MODULES,
        ]));
    }
}
