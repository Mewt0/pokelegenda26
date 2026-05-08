<?php
declare(strict_types=1);

namespace Pokemon8\Game;

use Pokemon8\Repository\InventoryRepository;
use Pokemon8\Repository\LocationRepository;
use Pokemon8\Repository\PokemonRepository;
use Pokemon8\Repository\QuestRepository;

final class NpcDialogService
{
    public function __construct(
        private LocationRepository $locations,
        private QuestRepository $quests,
        private InventoryRepository $inventory,
        private PokemonRepository $pokemon,
        private LocationContentRepository $content,
    ) {
    }

    public function open(int $userId, int $locationId, array $params): array
    {
        if (!$this->userIsAtLocation($userId, $locationId)) {
            return $this->error('Этот NPC находится в другой локации.');
        }

        $npc = $this->findNpc($locationId, $params);
        $title = $this->npcTitle($npc, $params);

        if ((int) ($params['quest_npc'] ?? 0) === 7) {
            return $this->billy($userId, (int) ($params['do'] ?? 1));
        }

        if ($this->isSisterJoy($locationId, $params, $title)) {
            return $this->sisterJoy($userId, (string) ($params['do_npc'] ?? 'pc'));
        }

        if ($this->isMarket($params, $title)) {
            return $this->marketDialog();
        }

        if ($this->isTransportNpc($params, $title)) {
            return $this->transportDialog($locationId, $title);
        }

        if ($this->isTicketOffice($params, $title)) {
            return $this->ticketOfficeDialog($locationId);
        }

        if ($this->isCurator($params, $title)) {
            return $this->curatorDialog($locationId);
        }

        if ($this->isStadiumNpc($params, $title)) {
            return $this->stadiumDialog($title);
        }

        if ($this->isSecretary($params, $title)) {
            return $this->secretaryDialog();
        }

        if ((int) ($params['quest_npc'] ?? 0) > 0) {
            return $this->questNpcDialog((int) $params['quest_npc'], $title, $locationId);
        }

        if (($params['do'] ?? '') === '1') {
            return $this->locationActionDialog($title, $locationId);
        }

        return $this->genericDialog($title, $locationId);
    }

    public function action(int $userId, int $locationId, array $params, string $action): array
    {
        if (!$this->userIsAtLocation($userId, $locationId)) {
            return $this->error('Это действие доступно только на текущей локации.');
        }

        if ((int) ($params['quest_npc'] ?? 0) === 7 && $action === 'accept_billy_quest') {
            $created = $this->quests->createIfMissing($userId, 7, 10);

            return $this->dialog('Коллекционер Билли', $created
                ? 'Отлично. Задание такое: найди Спироу и Пиджеотто, собери с них по 10 перьев каждого вида и принеси мне. Я отмечу это как первый настоящий полевой сбор.'
                : 'Я уже записал тебя в журнал помощников. Возвращайся, когда соберёшь перья.', [
                ['label' => 'Хорошо, вернусь позже', 'close' => true],
            ]);
        }

        if ((int) ($params['quest_npc'] ?? 0) === 7 && $action === 'turn_in_billy_quest') {
            return $this->turnInBilly($userId);
        }

        if ($this->isSisterJoy($locationId, $params, $this->npcTitle($this->findNpc($locationId, $params), $params))) {
            if (!$this->hasSisterJoy($locationId)) {
                return $this->error('Сестра Джой доступна только в покецентре.');
            }
            return $this->sisterJoyAction($userId, $action);
        }

        if ($action === 'curator_rules') {
            return $this->dialog('Куратор', 'Арена сейчас переносится на новый PvP-модуль. Добровольные вызовы доступны через меню игрока, а принудительные бои работают через ордера Команды R по новым правилам кармы.', [
                ['label' => 'Понятно', 'close' => true],
            ]);
        }

        if ($action === 'npc_ack') {
            return $this->dialog('NPC', 'Я записал это. Когда сценарий этого персонажа будет расширен, здесь появятся полноценные награды, условия и прогресс.', [
                ['label' => 'Закрыть', 'close' => true],
            ]);
        }

        return $this->error('Действие NPC пока не подключено к новому сценарию.');
    }

    private function sisterJoy(int $userId, string $mode): array
    {
        if ($mode === '2' || $mode === 'nursery') {
            return $this->nurseryDialog($userId);
        }

        return $this->dialog('Сестра Джой', 'Добро пожаловать в покецентр. Я могу восстановить здоровье и PP вашей команды или помочь с питомником.', [
            ['label' => 'Вылечить команду', 'action' => 'joy_heal'],
            ['label' => 'Питомник', 'params' => ['npc' => '1', 'do_npc' => 'nursery']],
            ['label' => 'Уйти', 'close' => true],
        ]);
    }

    private function sisterJoyAction(int $userId, string $action): array
    {
        if ($action === 'joy_heal') {
            $this->pokemon->healActivePokemon($userId);
            return $this->dialog('Сестра Джой', 'Готово. Все активные покемоны вылечены, PP атак восстановлены.', [
                ['label' => 'Спасибо', 'close' => true],
                ['label' => 'Питомник', 'params' => ['npc' => '1', 'do_npc' => 'nursery']],
            ]);
        }

        if (str_starts_with($action, 'nursery_take:')) {
            $pokemonId = (int) substr($action, strlen('nursery_take:'));
            if (!$this->pokemon->moveFromNursery($userId, $pokemonId)) {
                return $this->dialog('Сестра Джой', 'Не получилось забрать покемона: в команде должно быть меньше 6 покемонов.', [
                    ['label' => 'Питомник', 'params' => ['npc' => '1', 'do_npc' => 'nursery']],
                ]);
            }

            return $this->nurseryDialog($userId, 'Покемон добавлен в команду.');
        }

        if (str_starts_with($action, 'nursery_store:')) {
            $pokemonId = (int) substr($action, strlen('nursery_store:'));
            if (!$this->pokemon->moveToNursery($userId, $pokemonId)) {
                return $this->dialog('Сестра Джой', 'Не получилось отправить покемона: при себе должен остаться хотя бы один активный покемон.', [
                    ['label' => 'Питомник', 'params' => ['npc' => '1', 'do_npc' => 'nursery']],
                ]);
            }

            return $this->nurseryDialog($userId, 'Покемон отправлен в питомник.');
        }

        return $this->error('Действие Сестры Джой не найдено.');
    }

    private function nurseryDialog(int $userId, string $prefix = ''): array
    {
        $active = $this->pokemon->listActivePokemon($userId, 6);
        $stored = $this->pokemon->listNurseryPokemon($userId, 8);
        $activeCount = $this->pokemon->countActivePokemon($userId);

        $choices = [
            ['label' => 'Вылечить команду', 'action' => 'joy_heal'],
        ];

        foreach ($stored as $pokemon) {
            $choices[] = [
                'label' => 'Забрать ' . $pokemon['name'] . ' Lv.' . $pokemon['level'],
                'action' => 'nursery_take:' . $pokemon['id'],
                'disabled' => $activeCount >= 6,
                'hint' => $activeCount >= 6 ? 'В команде уже 6 покемонов.' : null,
            ];
        }

        foreach ($active as $pokemon) {
            $choices[] = [
                'label' => 'В питомник ' . $pokemon['name'] . ' Lv.' . $pokemon['level'],
                'action' => 'nursery_store:' . $pokemon['id'],
                'disabled' => $activeCount <= 1 || $pokemon['starter'],
                'hint' => $pokemon['starter'] ? 'Стартового покемона нельзя убрать.' : null,
            ];
        }

        $choices[] = ['label' => 'Назад', 'params' => ['npc' => '1', 'do_npc' => 'pc']];
        $text = trim(($prefix !== '' ? $prefix . ' ' : '') . 'В команде сейчас ' . $activeCount . '/6. В питомнике показаны первые ' . count($stored) . ' покемонов.');

        return $this->dialog('Питомник', $text, $choices);
    }

    private function billy(int $userId, int $step): array
    {
        $quest = $this->quests->findForUser($userId, 7);
        $completed = $quest !== null && (int) ($quest['gotov'] ?? 0) === 1;
        $inProgress = $quest !== null && !$completed;

        if ($completed) {
            return $this->dialog('Коллекционер Билли', 'Спасибо за помощь с коллекцией перьев. Я уже занёс твой вклад в каталог.', [
                ['label' => 'Удачи с коллекцией', 'close' => true],
            ]);
        }

        if ($inProgress) {
            $pidgeottoFeathers = $this->inventory->countItem($userId, 13);
            $spearowFeathers = $this->inventory->countItem($userId, 14);
            $ready = $pidgeottoFeathers >= 10 && $spearowFeathers >= 10;

            return $this->dialog('Коллекционер Билли', 'Ты уже принёс мне перья? Нужно 10 перьев Пиджеотто и 10 перьев Спироу.', [
                ['label' => 'Пока нет', 'close' => true],
                [
                    'label' => 'Сдать перья',
                    'action' => 'turn_in_billy_quest',
                    'disabled' => !$ready,
                    'hint' => 'Пиджеотто: ' . $pidgeottoFeathers . '/10, Спироу: ' . $spearowFeathers . '/10',
                ],
            ]);
        }

        if ($step === 2) {
            return $this->dialog('Коллекционер Билли', 'Я собираю редкие перья летающих покемонов. Для нового стенда нужны перья Пиджеотто и Спироу, но самому мне сейчас не выбраться в поле. Поможешь?', [
                ['label' => 'Я согласен', 'action' => 'accept_billy_quest'],
                ['label' => 'Мне некогда', 'close' => true],
            ]);
        }

        return $this->dialog('Коллекционер Билли', 'Привет. Я наблюдаю за птицами на этой дороге и собираю коллекцию перьев.', [
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
            return $this->dialog('Коллекционер Билли', 'Перьев пока недостаточно. Принеси по 10 перьев Пиджеотто и Спироу.', [
                ['label' => 'Вернусь позже', 'close' => true],
            ]);
        }

        $this->inventory->removeItem($userId, 13, 10);
        $this->inventory->removeItem($userId, 14, 10);
        $this->inventory->addItem($userId, 1, 5000);
        $this->inventory->addItem($userId, 9, 5);
        $this->inventory->addItem($userId, 10, 3);
        $this->quests->updateState($userId, 7, 20, 1);

        return $this->dialog('Коллекционер Билли', 'Отличные экземпляры. Забирай награду: 5000 монет, красные и фиолетовые конфеты.', [
            ['label' => 'Спасибо', 'close' => true],
        ]);
    }

    private function marketDialog(): array
    {
        return $this->dialog('Покемаркет', 'Магазин уже работает в новом интерфейсе: товары, поиск, корзина и покупка идут через JSON API.', [
            ['label' => 'Открыть Покемаркет', 'route' => '/game/market/items'],
            ['label' => 'Уйти', 'close' => true],
        ]);
    }

    private function curatorDialog(int $locationId): array
    {
        $text = $locationId === 1
            ? 'Я курирую регистрацию на арену и слежу за правилами официальных боёв. Старый турнирный вход больше не используется напрямую.'
            : 'Я слежу за правилами этой площадки. Все бои должны идти через новый PvP-модуль, чтобы лог, награды и состояние игроков не расходились.';

        return $this->dialog('Куратор', $text, [
            ['label' => 'Правила боёв', 'action' => 'curator_rules'],
            ['label' => 'Уйти', 'close' => true],
        ]);
    }

    private function transportDialog(int $locationId, string $title): array
    {
        $direction = $locationId === 23 ? 'из Канто в Джотто' : ($locationId === 25 ? 'из Джотто в Канто' : 'между регионами');

        return $this->dialog($title !== '' ? $title : 'Транспорт', 'Рейсы ' . $direction . ' перенесены в новый транспортный модуль. Там показываются доступные маршруты, цена и требуемый билет.', [
            ['label' => 'Открыть транспорт', 'route' => '/game/transport'],
            ['label' => 'Уйти', 'close' => true],
        ]);
    }

    private function ticketOfficeDialog(int $locationId): array
    {
        $region = $locationId === 23 ? 'Канто' : ($locationId === 25 ? 'Джотто' : 'регионов');

        return $this->dialog('Касса', 'Здесь оформляются билеты для рейсов ' . $region . '. Покупка билетов и сами маршруты теперь вынесены в транспорт и магазин предметов.', [
            ['label' => 'Открыть транспорт', 'route' => '/game/transport'],
            ['label' => 'Открыть Покемаркет', 'route' => '/game/market/items'],
            ['label' => 'Уйти', 'close' => true],
        ]);
    }

    private function stadiumDialog(string $title): array
    {
        $name = $title !== '' ? $title : 'Стадион';

        return $this->dialog($name, 'Стадион уже подключён к новой карте, но турнирные сценарии будут запускаться через отдельный PvP/ивент-модуль. Прямой legacy-вход отключён, чтобы не ломать бои и логи.', [
            ['label' => 'Понятно', 'close' => true],
        ]);
    }

    private function secretaryDialog(): array
    {
        return $this->dialog('Секретарь', 'Административные функции перенесены в новый Game Master Center. Если у тебя есть доступ, заходи через кнопку админки в игровом интерфейсе.', [
            ['label' => 'Открыть админку', 'route' => '/game/admin'],
            ['label' => 'Уйти', 'close' => true],
        ]);
    }

    private function questNpcDialog(int $questNpcId, string $title, int $locationId): array
    {
        $name = $title !== '' ? $title : $this->questNpcTitle($questNpcId);
        $texts = [
            1 => 'Я связан с основным сюжетом этой зоны. Старый сценарий уже разобран, теперь его нужно вести через новые квесты, награды и проверки состояния.',
            2 => 'Я могу выдать небольшую подсказку по текущей локации. Полноценная ветка будет подключена через новый quest API.',
            3 => 'У меня есть отдельная сюжетная линия. Она больше не будет выполняться старым PHP-файлом напрямую.',
            4 => 'Мой номер с заданиями и наградами будет перенесён как отдельный сценарий NPC.',
            5 => 'Эта зона опаснее, чем кажется. Сначала подготовь команду, затем возвращайся за полноценным заданием.',
            8 => 'Цветочный прилавок будет работать через новый магазин/крафт, чтобы покупки и ингредиенты учитывались в инвентаре корректно.',
        ];

        $text = $texts[$questNpcId] ?? 'Этот персонаж найден в старой карте и уже отображается в новом интерфейсе. Сценарий будет подключён через новый NPC engine.';
        $text .= ' Локация #' . $locationId . '.';

        return $this->dialog($name, $text, [
            ['label' => 'Отметить как просмотренное', 'action' => 'npc_ack'],
            ['label' => 'Уйти', 'close' => true],
        ]);
    }

    private function locationActionDialog(string $title, int $locationId): array
    {
        $name = $title !== '' ? $title : 'Осмотреться';
        $text = match (true) {
            str_contains(mb_strtolower($name, 'UTF-8'), 'выход') => 'Выход из этой зоны теперь должен проходить через новые переходы карты, чтобы состояние игрока и чат не расходились.',
            default => 'Вы внимательно осмотрели локацию #' . $locationId . '. Сейчас здесь нет отдельного события, но точка уже заведена в новый NPC engine.',
        };

        return $this->dialog($name, $text, [
            ['label' => 'Закрыть', 'close' => true],
        ]);
    }

    private function genericDialog(string $title, int $locationId): array
    {
        return $this->dialog($title !== '' ? $title : 'NPC', 'Этот персонаж перенесён в новую карту локаций. Для него включён безопасный новый диалог без выполнения legacy PHP. Локация #' . $locationId . '.', [
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

    private function hasSisterJoy(int $locationId): bool
    {
        return $this->findNpc($locationId, ['npc' => '1', 'do_npc' => 'pc']) !== null;
    }

    private function npcTitle(?array $npc, array $params): string
    {
        if ($npc !== null && isset($npc['title'])) {
            return (string) $npc['title'];
        }

        if (($params['npc'] ?? '') === '2') {
            return 'Покемаркет';
        }
        if (($params['npc'] ?? '') === '3') {
            return 'NPC';
        }
        if (($params['npc'] ?? '') === '1' && ($params['do_npc'] ?? '') === 'pc') {
            return 'Покецентр';
        }

        return '';
    }

    private function isSisterJoy(int $locationId, array $params, string $title): bool
    {
        $lower = mb_strtolower($title, 'UTF-8');
        return (($params['npc'] ?? '') === '1' && ($params['do_npc'] ?? '') !== '')
            || ($this->hasSisterJoy($locationId) && str_contains($lower, 'покецентр'));
    }

    private function isMarket(array $params, string $title): bool
    {
        $lower = mb_strtolower($title, 'UTF-8');
        return ($params['npc'] ?? '') === '2' || str_contains($lower, 'маркет') || str_contains($lower, 'магазин');
    }

    private function isCurator(array $params, string $title): bool
    {
        return ($params['npc'] ?? '') === '3' && str_contains(mb_strtolower($title, 'UTF-8'), 'куратор');
    }

    private function isTransportNpc(array $params, string $title): bool
    {
        $lower = mb_strtolower($title, 'UTF-8');
        return ($params['npc'] ?? '') === '3'
            && (($params['do'] ?? '') === '1' || str_contains($lower, 'теплоход') || str_contains($lower, 'бортпровод'));
    }

    private function isTicketOffice(array $params, string $title): bool
    {
        return ($params['npc'] ?? '') === '1' && str_contains(mb_strtolower($title, 'UTF-8'), 'касса');
    }

    private function isStadiumNpc(array $params, string $title): bool
    {
        $lower = mb_strtolower($title, 'UTF-8');
        return in_array((string) ($params['npc'] ?? ''), ['4', '5'], true)
            || str_contains($lower, 'тренер')
            || str_contains($lower, 'арен');
    }

    private function isSecretary(array $params, string $title): bool
    {
        return ($params['npc'] ?? '') === '1'
            && (($params['do'] ?? '') === '1')
            && str_contains(mb_strtolower($title, 'UTF-8'), 'секрет');
    }

    private function questNpcTitle(int $questNpcId): string
    {
        return [
            1 => 'Профессор Оук',
            2 => 'Случайный прохожий',
            3 => 'Странный Спайк',
            4 => 'Циркач Стив',
            5 => 'Кэрол',
            8 => 'Цветочный прилавок',
        ][$questNpcId] ?? 'Сюжетный NPC';
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
