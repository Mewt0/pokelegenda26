<?php
declare(strict_types=1);

namespace Pokemon8\Game;

use Pokemon8\Repository\LocationRepository;
use Pokemon8\Repository\InventoryRepository;
use Pokemon8\Repository\QuestRepository;

final class NpcDialogService
{
    public function __construct(
        private LocationRepository $locations,
        private QuestRepository $quests,
        private InventoryRepository $inventory,
        private LocationContentRepository $content,
    ) {
    }

    public function open(int $userId, int $locationId, array $params): array
    {
        if (!$this->userIsAtLocation($userId, $locationId)) {
            return $this->error('Этот NPC находится в другой локации.');
        }

        if ((int) ($params['quest_npc'] ?? 0) === 7) {
            return $this->billy($userId, (int) ($params['do'] ?? 1));
        }

        return $this->genericDialog($locationId, $params);
    }

    public function action(int $userId, int $locationId, array $params, string $action): array
    {
        if (!$this->userIsAtLocation($userId, $locationId)) {
            return $this->error('Это действие доступно только на текущей локации.');
        }

        if ((int) ($params['quest_npc'] ?? 0) === 7 && $action === 'accept_billy_quest') {
            $created = $this->quests->createIfMissing($userId, 7, 10);

            return [
                'ok' => true,
                'npc' => [
                    'title' => 'Билли',
                    'text' => $created
                        ? 'Отлично! Тогда задание такое: найди Спироу и Пиджеотто. Собери с них по 10 перьев каждого вида. Я обязательно отблагодарю тебя.'
                        : 'Я уже дал тебе это задание. Возвращайся, когда соберёшь перья.',
                    'choices' => [
                        ['label' => 'Хорошо, я вернусь позже', 'close' => true],
                    ],
                ],
            ];
        }

        if ((int) ($params['quest_npc'] ?? 0) === 7 && $action === 'turn_in_billy_quest') {
            return $this->turnInBilly($userId);
        }

        return $this->error('Действие NPC пока не перенесено.');
    }

    private function billy(int $userId, int $step): array
    {
        $quest = $this->quests->findForUser($userId, 7);
        $completed = $quest !== null && (int) ($quest['gotov'] ?? 0) === 1;
        $inProgress = $quest !== null && !$completed;

        if ($completed) {
            return $this->dialog('Билли', 'Спасибо за помощь с коллекцией перьев. Если появится новая находка, я обязательно обращусь к тебе.', [
                ['label' => 'Удачи с коллекцией', 'close' => true],
            ]);
        }

        if ($inProgress) {
            $pidgeottoFeathers = $this->inventory->countItem($userId, 13);
            $spearowFeathers = $this->inventory->countItem($userId, 14);
            $ready = $pidgeottoFeathers >= 10 && $spearowFeathers >= 10;

            return $this->dialog('Билли', 'Ты уже принёс мне перья? Мне нужны 10 перьев Пиджеотто и 10 перьев Спироу.', [
                ['label' => 'Пока что нет', 'close' => true],
                [
                    'label' => 'Сдать перья',
                    'action' => 'turn_in_billy_quest',
                    'disabled' => !$ready,
                    'hint' => 'Пиджеотто: ' . $pidgeottoFeathers . '/10, Спироу: ' . $spearowFeathers . '/10',
                ],
            ]);
        }

        if ($step === 2) {
            return $this->dialog(
                'Билли',
                'Я не просто так смотрю вдаль. Я коллекционер перьев разного вида и оттенка. Для полной коллекции мне осталось собрать перья Пиджеотто и Спироу, но мои покемоны уже выдохлись. Может быть, ты поможешь мне?',
                [
                    ['label' => 'Я согласен', 'action' => 'accept_billy_quest'],
                    ['label' => 'Мне некогда', 'close' => true],
                ]
            );
        }

        return $this->dialog('Билли', 'Привет! Чего пожаловал? Я наблюдаю за летающими покемонами и собираю редкие перья.', [
            ['label' => 'Почему ты так внимательно смотришь вдаль?', 'params' => ['quest_npc' => '7', 'do' => '2']],
            ['label' => 'Просто осматриваюсь', 'close' => true],
        ]);
    }

    private function turnInBilly(int $userId): array
    {
        $quest = $this->quests->findForUser($userId, 7);
        if ($quest === null || (int) ($quest['gotov'] ?? 0) === 1) {
            return $this->error('Сначала нужно взять задание у Билли.');
        }

        if (!$this->inventory->hasItem($userId, 13, 10) || !$this->inventory->hasItem($userId, 14, 10)) {
            return $this->dialog('Билли', 'Ты собрал недостаточно перьев. Мне нужно по 10 перьев Пиджеотто и Спироу.', [
                ['label' => 'Я вернусь позже', 'close' => true],
            ]);
        }

        $this->inventory->removeItem($userId, 13, 10);
        $this->inventory->removeItem($userId, 14, 10);
        $this->inventory->addItem($userId, 1, 5000);
        $this->inventory->addItem($userId, 9, 5);
        $this->inventory->addItem($userId, 10, 3);
        $this->quests->updateState($userId, 7, 20, 1);

        return $this->dialog('Билли', 'Замечательно! Перья отличные. Спасибо тебе! Забирай награду и иди по своим делам.', [
            ['label' => 'Спасибо', 'close' => true],
        ]);
    }

    private function genericDialog(int $locationId, array $params): array
    {
        $npc = $this->findNpc($locationId, $params);
        $title = $npc['title'] ?? 'NPC';

        return $this->dialog((string) $title, 'Этот персонаж уже найден в новой карте. Его диалог будет перенесён отдельным сценарием NPC.', [
            ['label' => 'Закрыть', 'close' => true],
        ]);
    }

    private function userIsAtLocation(int $userId, int $locationId): bool
    {
        $user = $this->locations->findUserState($userId);
        return $user !== null && (int) $user['buildmy'] === $locationId;
    }

    private function findNpc(int $locationId, array $params): ?array
    {
        foreach ($this->content->find($locationId)['npcs'] as $npc) {
            if (($npc['params'] ?? []) == $params) {
                return $npc;
            }
        }

        return null;
    }

    private function dialog(string $title, string $text, array $choices): array
    {
        return [
            'ok' => true,
            'npc' => [
                'title' => $title,
                'text' => $text,
                'choices' => $choices,
            ],
        ];
    }

    private function error(string $message): array
    {
        return ['ok' => false, 'error' => 'npc', 'message' => $message];
    }
}
