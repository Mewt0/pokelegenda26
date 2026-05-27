<?php
declare(strict_types=1);

namespace Pokemon8\Game;

use PDO;
use Pokemon8\Repository\InventoryRepository;
use Pokemon8\Repository\LocationRepository;
use Pokemon8\Repository\PokemonRepository;
use Pokemon8\Repository\QuestRepository;
use Pokemon8\Repository\RewardRepository;

final class NpcDialogService
{
    private const STARTERS = [
        1 => 'Bulbasaur',
        4 => 'Charmander',
        7 => 'Squirtle',
        152 => 'Chikorita',
        155 => 'Cyndaquil',
        158 => 'Totodile',
        252 => 'Treecko',
        255 => 'Torchic',
        258 => 'Mudkip',
        387 => 'Turtwig',
        390 => 'Chimchar',
        393 => 'Piplup',
        495 => 'Snivy',
        498 => 'Tepig',
        501 => 'Oshawott',
        650 => 'Chespin',
        653 => 'Fennekin',
        656 => 'Froakie',
    ];

    private const STONE_RECIPES = [
        'thunder' => ['item' => 40, 'name' => 'Громовой камень', 'parts' => [39 => 1, 24 => 5, 29 => 3, 34 => 1]],
        'fire' => ['item' => 41, 'name' => 'Огненный камень', 'parts' => [39 => 1, 25 => 5, 30 => 3, 35 => 1]],
        'water' => ['item' => 42, 'name' => 'Водный камень', 'parts' => [39 => 1, 26 => 5, 31 => 3, 36 => 1]],
        'leaf' => ['item' => 43, 'name' => 'Лиственный камень', 'parts' => [39 => 1, 27 => 5, 32 => 3, 37 => 1]],
        'moon' => ['item' => 44, 'name' => 'Лунный камень', 'parts' => [39 => 1, 28 => 5, 33 => 3, 38 => 1]],
    ];

    public function __construct(
        private PDO $db,
        private LocationRepository $locations,
        private QuestRepository $quests,
        private InventoryRepository $inventory,
        private PokemonRepository $pokemon,
        private LocationContentRepository $content,
        private ?RewardRepository $rewards = null,
    ) {
    }

    public function open(int $userId, int $locationId, array $params): array
    {
        if (!$this->userIsAtLocation($userId, $locationId)) {
            return $this->error('Этот NPC находится в другой локации.');
        }

        $npc = $this->findNpc($locationId, $params);
        $title = $this->npcTitle($npc, $params, $locationId);

        if ((int) ($params['quest_npc'] ?? 0) === 7) {
            return $this->billy($userId, (int) ($params['do'] ?? 1));
        }

        if ($this->isSisterJoy($locationId, $params)) {
            return $this->sisterJoy($userId, (string) ($params['do_npc'] ?? 'pc'));
        }

        if ($this->isMarket($params)) {
            return $this->marketDialog($title);
        }

        if ($this->isTicketOffice($locationId, $params)) {
            return $this->ticketOfficeDialog($locationId);
        }

        if ($this->isTransportNpc($locationId, $params)) {
            return $this->transportDialog($locationId, $title);
        }

        if ($this->isCurator($locationId, $params)) {
            return $this->curatorDialog($locationId, $title);
        }

        if ($this->isStadiumNpc($locationId, $params)) {
            return $this->stadiumDialog($title);
        }

        if ($this->isSecretary($locationId, $params)) {
            return $this->secretaryDialog();
        }

        if ((int) ($params['quest_npc'] ?? 0) > 0) {
            return $this->questNpcDialog($userId, (int) $params['quest_npc'], $title, $locationId, $params);
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

            return $this->dialog(
                'Коллекционер Билли',
                $created
                    ? 'Отлично. Найди Pidgeotto и Spearow, собери с них по 10 перьев каждого вида и принеси мне.'
                    : 'Я уже записал тебя в журнал помощников. Возвращайся, когда соберешь перья.',
                [['label' => 'Хорошо', 'close' => true]]
            );
        }

        if ((int) ($params['quest_npc'] ?? 0) === 7 && $action === 'turn_in_billy_quest') {
            return $this->turnInBilly($userId);
        }

        if ($this->isSisterJoy($locationId, $params)) {
            if (!$this->hasSisterJoy($locationId)) {
                return $this->error('Сестра Джой доступна только в покецентре.');
            }
            return $this->sisterJoyAction($userId, $action);
        }

        if (str_starts_with($action, 'quest_start:')) {
            return $this->questStartAction($userId, $action);
        }

        if (str_starts_with($action, 'quest_update:')) {
            return $this->questUpdateAction($userId, $action);
        }

        if (str_starts_with($action, 'starter_select:')) {
            return $this->selectStarter($userId, (int) substr($action, strlen('starter_select:')));
        }

        if ($action === 'oak_finish_starter') {
            return $this->finishOakStarter($userId);
        }

        if ($action === 'quest_circus_turnin') {
            return $this->turnInCircusQuest($userId);
        }

        if ($action === 'quest_carol_turnin') {
            return $this->turnInCarolQuest($userId);
        }

        if ($action === 'quest_research_start') {
            $this->quests->createOrUpdate($userId, 5, 10, 0);
            return $this->dialog('Исследователь', 'Я отметил задание. Нужны десять Horsea 40 уровня с маячками. Возвращайся, когда данные будут собраны.', [
                ['label' => 'Понятно', 'close' => true],
            ]);
        }

        if ($action === 'quest_research_turnin') {
            return $this->turnInResearchQuest($userId);
        }

        if ($action === 'quest_metapod_start') {
            $started = $this->quests->startFromDefinition($userId, 6);
            if (($started['ok'] ?? false) !== true) {
                return $this->dialog('Исследователь', (string) ($started['message'] ?? 'Ежедневная задача сейчас недоступна.'), [
                    ['label' => 'Уйти', 'close' => true],
                ]);
            }

            return $this->dialog('Исследователь', 'Ежедневная задача принята: принеси пять Metapod 9 уровня.', [
                ['label' => 'Пойду искать', 'close' => true],
            ]);
        }

        if ($action === 'quest_metapod_turnin') {
            return $this->turnInMetapodDaily($userId);
        }

        if (str_starts_with($action, 'craft_stone:')) {
            return $this->craftStone($userId, substr($action, strlen('craft_stone:')));
        }

        if ($action === 'secretary_reputation') {
            return $this->secretaryReputation($userId);
        }

        if ($action === 'secretary_clan') {
            return $this->dialog('Секретарь', 'Захваты кланов будут идти через новый модуль кланов и PvP. Старый вход не запускается, чтобы не ломать логи и состояния игроков.', [
                ['label' => 'Другой вопрос', 'params' => ['npc' => '1', 'do' => '1']],
                ['label' => 'Уйти', 'close' => true],
            ]);
        }

        if ($action === 'secretary_job') {
            return $this->dialog('Секретарь', 'Набор на игровые должности сейчас закрыт. Когда появится новый набор, он будет оформлен через новости и заявки.', [
                ['label' => 'Другой вопрос', 'params' => ['npc' => '1', 'do' => '1']],
                ['label' => 'Уйти', 'close' => true],
            ]);
        }

        if ($action === 'curator_rules') {
            return $this->dialog('Куратор', 'Добровольные PvP-вызовы идут через меню игрока. Принудительные нападения используют ордера Команды R и правила кармы. Все бои должны логироваться новым модулем боя.', [
                ['label' => 'Понятно', 'close' => true],
            ]);
        }

        if ($action === 'npc_ack') {
            return $this->dialog('NPC', 'Отмечено. Этот персонаж уже работает через новый безопасный NPC engine.', [
                ['label' => 'Закрыть', 'close' => true],
            ]);
        }

        return $this->error('Действие NPC не найдено.');
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
            return $this->dialog('Сестра Джой', 'Готово. Активная команда вылечена, PP атак восстановлены.', [
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
        $stored = $this->pokemon->listNurseryPokemon($userId, 24);
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

        $choices[] = [
            'label' => 'Открыть полный список покемонов',
            'route' => '/game/pokemon',
        ];
        $choices[] = ['label' => 'Назад', 'params' => ['npc' => '1', 'do_npc' => 'pc']];
        $text = trim(($prefix !== '' ? $prefix . ' ' : '')
            . 'В команде сейчас ' . $activeCount . '/6. '
            . 'Быстрый список показывает до ' . count($stored) . ' покемонов из питомника; полный список открыт в меню покемонов без перезагрузки боя/мира.');

        return $this->dialog('Питомник', $text, $choices);
    }

    private function billy(int $userId, int $step): array
    {
        $quest = $this->quests->findForUser($userId, 7);
        $completed = $quest !== null && (int) ($quest['gotov'] ?? 0) === 1;
        $inProgress = $quest !== null && !$completed;

        if ($completed) {
            return $this->dialog('Коллекционер Билли', 'Спасибо за помощь с коллекцией перьев. Твой вклад уже занесен в каталог.', [
                ['label' => 'Удачи', 'close' => true],
            ]);
        }

        if ($inProgress) {
            $pidgeottoFeathers = $this->inventory->countItem($userId, 13);
            $spearowFeathers = $this->inventory->countItem($userId, 14);
            $ready = $pidgeottoFeathers >= 10 && $spearowFeathers >= 10;

            return $this->dialog('Коллекционер Билли', 'Нужно 10 перьев Pidgeotto и 10 перьев Spearow.', [
                ['label' => 'Пока нет', 'close' => true],
                [
                    'label' => 'Сдать перья',
                    'action' => 'turn_in_billy_quest',
                    'disabled' => !$ready,
                    'hint' => 'Pidgeotto: ' . $pidgeottoFeathers . '/10, Spearow: ' . $spearowFeathers . '/10',
                ],
            ]);
        }

        if ($step === 2) {
            return $this->dialog('Коллекционер Билли', 'Я собираю редкие перья летающих покемонов. Поможешь собрать образцы?', [
                ['label' => 'Я согласен', 'action' => 'accept_billy_quest'],
                ['label' => 'Мне некогда', 'close' => true],
            ]);
        }

        return $this->dialog('Коллекционер Билли', 'Привет. Я наблюдаю за птицами на этой дороге и собираю коллекцию перьев.', [
            ['label' => 'Почему ты смотришь вдаль?', 'params' => ['quest_npc' => '7', 'do' => '2']],
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
            return $this->dialog('Коллекционер Билли', 'Перьев пока недостаточно. Принеси по 10 перьев Pidgeotto и Spearow.', [
                ['label' => 'Вернусь позже', 'close' => true],
            ]);
        }

        $this->inventory->removeItem($userId, 13, 10);
        $this->inventory->removeItem($userId, 14, 10);
        $this->grantRewardItems($userId, [1 => 5000, 9 => 5, 10 => 3], 'Квест: Перья для Билли');
        $this->quests->updateState($userId, 7, 20, 1);
        $this->addQuestRank($userId, 1);

        return $this->dialog('Коллекционер Билли', 'Отличные экземпляры. Награда: 5000 монет, красные и фиолетовые конфеты.', [
            ['label' => 'Спасибо', 'close' => true],
        ]);
    }

    private function questNpcDialog(int $userId, int $questNpcId, string $title, int $locationId, array $params): array
    {
        $step = (int) ($params['do'] ?? 1);

        if ($locationId === 3 && $questNpcId === 1) {
            return $this->professorOak($userId);
        }

        if ($locationId === 1 && $questNpcId === 2) {
            return $this->passerby();
        }

        if ($locationId === 1 && $questNpcId === 3) {
            return $this->strangeSpike($userId, $step);
        }

        if ($locationId === 5 && $questNpcId === 4) {
            return $this->circusSteve($userId);
        }

        if ($locationId === 6 && $questNpcId === 5) {
            return $this->carol($userId);
        }

        if ($locationId === 3 && in_array($questNpcId, [2, 3], true)) {
            return $this->researcher($userId);
        }

        if (($locationId === 28 && $questNpcId === 3) || ($questNpcId === 3 && $step === 3)) {
            return $this->stoneCraftDialog($userId);
        }

        if ($questNpcId === 8) {
            return $this->flowerStall();
        }

        return $this->genericQuestNpc($title !== '' ? $title : $this->questNpcTitle($questNpcId, $locationId), $locationId);
    }

    private function professorOak(int $userId): array
    {
        $quest = $this->quests->findForUser($userId, 1);
        if ($quest === null) {
            return $this->dialog('Профессор Оук', 'Добро пожаловать в мир покемонов. В лаборатории случилась беда: стартовые покемоны разбежались. Расспроси людей рядом с лабораторией и возвращайся ко мне.', [
                ['label' => 'Я помогу', 'action' => 'quest_start:1:2'],
                ['label' => 'Позже', 'close' => true],
            ]);
        }

        if ((int) ($quest['gotov'] ?? 0) === 1) {
            return $this->dialog('Профессор Оук', 'Удачи в путешествии, тренер. Следи за командой и не забывай лечиться в покецентрах.', [
                ['label' => 'Спасибо', 'close' => true],
            ]);
        }

        if ((int) ($quest['process'] ?? 0) >= 4) {
            return $this->dialog('Профессор Оук', 'Вижу, у тебя уже есть покебол со стартовым покемоном. Я зарегистрирую его и выдам припасы.', [
                ['label' => 'Получить стартера и припасы', 'action' => 'oak_finish_starter'],
                ['label' => 'Позже', 'close' => true],
            ]);
        }

        $choices = [];
        foreach (self::STARTERS as $baseId => $name) {
            $choices[] = ['label' => sprintf('#%03d %s', $baseId, $name), 'action' => 'starter_select:' . $baseId];
        }
        $choices[] = ['label' => 'Пока осмотрюсь', 'close' => true];

        return $this->dialog('Профессор Оук', 'Ты нашел следы сбежавших покемонов. Выбери, кого попробуешь поймать первым.', $choices);
    }

    private function passerby(): array
    {
        return $this->dialog('Случайный прохожий', 'Да, я видел, как несколько покемонов убежали на Дорогу 1. Кажется, они держались вместе.', [
            ['label' => 'Спасибо за информацию', 'action' => 'quest_update:1:3:0'],
            ['label' => 'Уйти', 'close' => true],
        ]);
    }

    private function strangeSpike(int $userId, int $step): array
    {
        $quest = $this->quests->findForUser($userId, 2);
        if ($quest !== null) {
            return $this->dialog('Странный Спайк', 'Я уже рассказал тебе всё, что видел на рассвете. Если узнаешь что-то новое, возвращайся.', [
                ['label' => 'Крафт камней', 'params' => ['quest_npc' => '3', 'do' => '3']],
                ['label' => 'Уйти', 'close' => true],
            ]);
        }

        $text = $step >= 4
            ? 'Я видел странного тренера на холме: крылья, хвост и голубоватое свечение. Может, это связано с легендами.'
            : 'Я видел кое-что странное на рассвете, но мне редко верят.';

        return $this->dialog('Странный Спайк', $text, [
            ['label' => 'Расскажи подробнее', 'params' => ['quest_npc' => '3', 'do' => (string) min(4, $step + 1)]],
            ['label' => 'Записать рассказ', 'action' => 'quest_start:2:2'],
            ['label' => 'Крафт камней', 'params' => ['quest_npc' => '3', 'do' => '3']],
            ['label' => 'Уйти', 'close' => true],
        ]);
    }

    private function circusSteve(int $userId): array
    {
        $quest = $this->quests->findForUser($userId, 3);
        if ($quest === null) {
            return $this->dialog('Циркач Стив', 'Нам нужны цирковые покемоны 35 уровня: Butterfree, Arbok, Venomoth, Beedrill и Primeape. Поможешь?', [
                ['label' => 'Принять задание', 'action' => 'quest_start:3:2'],
                ['label' => 'Позже', 'close' => true],
            ]);
        }

        if ((int) ($quest['gotov'] ?? 0) === 1) {
            return $this->dialog('Циркач Стив', 'Спасибо за помощь. Для тебя в цирке всегда найдется место.', [
                ['label' => 'Уйти', 'close' => true],
            ]);
        }

        return $this->dialog('Циркач Стив', 'Покемоны готовы? Я проверю активную команду и заберу нужных участников.', [
            ['label' => 'Сдать покемонов', 'action' => 'quest_circus_turnin'],
            ['label' => 'Пока нет', 'close' => true],
        ]);
    }

    private function carol(int $userId): array
    {
        $quest = $this->quests->findForUser($userId, 4);
        if ($quest === null) {
            return $this->dialog('Кэрол', 'Beedrill украли мою любимую игрушку. Если найдешь ее, пожалуйста, верни.', [
                ['label' => 'Я помогу', 'action' => 'quest_start:4:10'],
                ['label' => 'Позже', 'close' => true],
            ]);
        }

        if ((int) ($quest['gotov'] ?? 0) === 1) {
            return $this->dialog('Кэрол', 'Спасибо, что вернул игрушку. Я больше не плачу.', [
                ['label' => 'Уйти', 'close' => true],
            ]);
        }

        return $this->dialog('Кэрол', 'Ты принес игрушку? Нужен предмет #4.', [
            ['label' => 'Вернуть игрушку', 'action' => 'quest_carol_turnin', 'disabled' => !$this->inventory->hasItem($userId, 4, 1)],
            ['label' => 'Еще ищу', 'close' => true],
        ]);
    }

    private function researcher(int $userId): array
    {
        $quest5 = $this->quests->findForUser($userId, 5);
        if ($quest5 === null) {
            return $this->dialog('Исследователь', 'Лаборатория изучает озеро Вертании. Нужны данные с десяти Horsea 40 уровня.', [
                ['label' => 'Принять задание', 'action' => 'quest_research_start'],
                ['label' => 'Позже', 'close' => true],
            ]);
        }

        if ((int) ($quest5['gotov'] ?? 0) === 0) {
            return $this->dialog('Исследователь', 'Если данные готовы, я проверю активную команду.', [
                ['label' => 'Сдать Horsea', 'action' => 'quest_research_turnin'],
                ['label' => 'Пока нет', 'close' => true],
            ]);
        }

        $daily = $this->quests->findForUser($userId, 6);
        if ($daily !== null && (int) ($daily['time'] ?? 0) > time()) {
            return $this->dialog('Исследователь', 'Сегодня задач больше нет. Возвращайся позже.', [
                ['label' => 'Уйти', 'close' => true],
            ]);
        }

        return $this->dialog('Исследователь', 'Есть ежедневное задание: принеси пять Metapod 9 уровня.', [
            ['label' => 'Взять задание', 'action' => 'quest_metapod_start'],
            ['label' => 'Сдать Metapod', 'action' => 'quest_metapod_turnin'],
            ['label' => 'Уйти', 'close' => true],
        ]);
    }

    private function flowerStall(): array
    {
        return $this->dialog('Цветочный прилавок', 'Семена, цветы и сезонные ингредиенты перенесены в новый магазин предметов. Покупки больше не идут через старый PHP.', [
            ['label' => 'Открыть Покемаркет', 'route' => '/game/market/items'],
            ['label' => 'Уйти', 'close' => true],
        ]);
    }

    private function stoneCraftDialog(int $userId): array
    {
        $choices = [];
        foreach (self::STONE_RECIPES as $key => $recipe) {
            $missing = $this->missingParts($userId, $recipe['parts']);
            $choices[] = [
                'label' => 'Сделать ' . $recipe['name'],
                'action' => 'craft_stone:' . $key,
                'disabled' => $missing !== '',
                'hint' => $missing,
            ];
        }
        $choices[] = ['label' => 'Уйти', 'close' => true];

        return $this->dialog('Мастеровой', 'Я могу собрать эволюционные камни из осколков. Шанс успеха: около 50%, материалы тратятся при попытке.', $choices);
    }

    private function marketDialog(string $title): array
    {
        return $this->dialog($title !== '' ? $title : 'Покемаркет', 'Магазин работает в новом интерфейсе: товары, поиск, выбор количества и покупка идут через JSON API.', [
            ['label' => 'Открыть Покемаркет', 'route' => '/game/market/items'],
            ['label' => 'Уйти', 'close' => true],
        ]);
    }

    private function curatorDialog(int $locationId, string $title): array
    {
        if ($locationId === 16) {
            return $this->dialog('Продавец удочек', 'Удочки и расходники перенесены в Покемаркет. Там же работает поиск по предметам.', [
                ['label' => 'Открыть Покемаркет', 'route' => '/game/market/items'],
                ['label' => 'Уйти', 'close' => true],
            ]);
        }

        return $this->dialog($title !== '' ? $title : 'Куратор', 'Я слежу за правилами этой зоны. Турниры и официальные бои будут запускаться через новый PvP/ивент-модуль.', [
            ['label' => 'Правила боев', 'action' => 'curator_rules'],
            ['label' => 'Уйти', 'close' => true],
        ]);
    }

    private function transportDialog(int $locationId, string $title): array
    {
        $direction = $locationId === 23 ? 'из Канто в Джотто' : ($locationId === 25 ? 'из Джотто в Канто' : 'между регионами');

        return $this->dialog($title !== '' ? $title : 'Транспорт', 'Рейсы ' . $direction . ' перенесены в новый транспортный модуль.', [
            ['label' => 'Открыть транспорт', 'route' => '/game/transport'],
            ['label' => 'Уйти', 'close' => true],
        ]);
    }

    private function ticketOfficeDialog(int $locationId): array
    {
        $region = $locationId === 23 ? 'Канто' : ($locationId === 25 ? 'Джотто' : 'регионов');

        return $this->dialog('Касса', 'Здесь оформляются билеты для рейсов ' . $region . '. Маршруты и билеты вынесены в новый транспорт и магазин.', [
            ['label' => 'Открыть транспорт', 'route' => '/game/transport'],
            ['label' => 'Открыть Покемаркет', 'route' => '/game/market/items'],
            ['label' => 'Уйти', 'close' => true],
        ]);
    }

    private function stadiumDialog(string $title): array
    {
        return $this->dialog($title !== '' ? $title : 'Стадион', 'Стадион подключен к новой карте. Турнирные сценарии будут запускаться через модуль PvP и турниров, без legacy iframe.', [
            ['label' => 'Понятно', 'close' => true],
        ]);
    }

    private function secretaryDialog(): array
    {
        return $this->dialog('Секретарь', 'Добрый день. Я могу показать репутацию, рассказать о клановых захватах или о должностях в игре.', [
            ['label' => 'Узнать репутацию за 10 000 монет', 'action' => 'secretary_reputation'],
            ['label' => 'Клановые захваты', 'action' => 'secretary_clan'],
            ['label' => 'Должности в игре', 'action' => 'secretary_job'],
            ['label' => 'Уйти', 'close' => true],
        ]);
    }

    private function locationActionDialog(string $title, int $locationId): array
    {
        $name = $title !== '' ? $title : 'Осмотреться';
        $lower = mb_strtolower($name, 'UTF-8');
        $text = str_contains($lower, 'выход')
            ? 'Выход из этой зоны теперь проходит через переходы новой карты, чтобы состояние игрока и чат не расходились.'
            : 'Вы внимательно осмотрели локацию #' . $locationId . '. Отдельного события здесь пока нет, но точка уже заведена в новый NPC engine.';

        return $this->dialog($name, $text, [['label' => 'Закрыть', 'close' => true]]);
    }

    private function genericDialog(string $title, int $locationId): array
    {
        return $this->dialog($title !== '' ? $title : 'NPC', 'Этот персонаж перенесен в новую карту. Для него включен безопасный диалог без запуска legacy PHP. Локация #' . $locationId . '.', [
            ['label' => 'Закрыть', 'close' => true],
        ]);
    }

    private function genericQuestNpc(string $title, int $locationId): array
    {
        return $this->dialog($title !== '' ? $title : 'Сюжетный NPC', 'Персонаж найден в legacy-карте и отображается в новой версии. Старый PHP не запускается напрямую; сценарий готов к дальнейшему расширению через quest API. Локация #' . $locationId . '.', [
            ['label' => 'Отметить как просмотренное', 'action' => 'npc_ack'],
            ['label' => 'Уйти', 'close' => true],
        ]);
    }

    private function questStartAction(int $userId, string $action): array
    {
        [, $questId, $process] = array_pad(explode(':', $action), 3, 0);
        if (!$this->quests->canStart($userId, (int) $questId)) {
            return $this->dialog('Задание', 'Это задание пока недоступно: сначала нужно завершить предыдущий связанный квест.', [
                ['label' => 'Понятно', 'close' => true],
            ]);
        }

        $created = $this->quests->createIfMissing($userId, (int) $questId, (int) $process);

        return $this->dialog('Задание', $created ? 'Задание принято.' : 'Это задание уже есть в журнале.', [
            ['label' => 'Закрыть', 'close' => true],
        ]);
    }

    private function questUpdateAction(int $userId, string $action): array
    {
        [, $questId, $process, $completed] = array_pad(explode(':', $action), 4, 0);
        $this->quests->createOrUpdate($userId, (int) $questId, (int) $process, (int) $completed);

        return $this->dialog('Задание', 'Журнал обновлен.', [
            ['label' => 'Закрыть', 'close' => true],
        ]);
    }

    private function selectStarter(int $userId, int $baseId): array
    {
        if (!isset(self::STARTERS[$baseId])) {
            return $this->error('Такого стартового покемона выбрать нельзя.');
        }

        $this->quests->setStarterChoice($userId, $baseId);

        return $this->dialog('Пойман стартовый покемон', sprintf('Ты выбрал #%03d %s. Вернись к профессору Оуку, чтобы зарегистрировать покемона.', $baseId, self::STARTERS[$baseId]), [
            ['label' => 'К профессору Оуку', 'params' => ['quest_npc' => '1', 'do' => '1']],
            ['label' => 'Закрыть', 'close' => true],
        ]);
    }

    private function finishOakStarter(int $userId): array
    {
        $quest = $this->quests->findForUser($userId, 1);
        if ($quest === null || (int) ($quest['gotov'] ?? 0) === 1) {
            return $this->error('Нечего завершать.');
        }

        $baseId = (int) ($quest['pers'] ?? 0);
        if (!isset(self::STARTERS[$baseId])) {
            return $this->error('Сначала выбери стартового покемона.');
        }

        $pokemonId = $this->grantPokemon($userId, $baseId, 5, true);
        if ($pokemonId <= 0) {
            return $this->error('Не удалось выдать стартового покемона.');
        }

        $this->grantRewardItems($userId, [10 => 3, 1 => 5000], 'Квест: Первый покемон');
        $this->quests->updateState($userId, 1, 10, 1);
        $this->addQuestRank($userId, 1);
        $this->quests->startIfAvailable($userId, 101, 1);

        return $this->dialog('Профессор Оук', 'Готово. Стартовый покемон зарегистрирован, а в инвентарь добавлены покеболы и 5000 монет. Следующий шаг: выйди на Дорогу 1 и проведи первый бой.', [
            ['label' => 'Спасибо', 'close' => true],
        ]);
    }

    private function turnInCircusQuest(int $userId): array
    {
        $requirements = [
            12 => ['name' => 'Butterfree', 'level' => 35],
            24 => ['name' => 'Arbok', 'level' => 35],
            49 => ['name' => 'Venomoth', 'level' => 35],
            15 => ['name' => 'Beedrill', 'level' => 35],
            57 => ['name' => 'Primeape', 'level' => 35],
        ];
        $pokemonIds = [];
        $missing = [];

        foreach ($requirements as $baseId => $info) {
            $id = $this->findActivePokemonForQuest($userId, $baseId, $info['level']);
            if ($id <= 0) {
                $missing[] = sprintf('#%03d %s Lv.%d', $baseId, $info['name'], $info['level']);
            } else {
                $pokemonIds[] = $id;
            }
        }

        if ($missing !== []) {
            return $this->dialog('Циркач Стив', 'Не хватает: ' . implode(', ', $missing), [
                ['label' => 'Вернусь позже', 'close' => true],
            ]);
        }

        $this->deleteQuestPokemon($userId, $pokemonIds);
        $this->grantRewardItems($userId, [1 => 50000, 20 => 3], 'Квест: Цирковая команда');
        $this->quests->updateState($userId, 3, 10, 1);
        $this->addQuestRank($userId, 2);

        return $this->dialog('Циркач Стив', 'Огромное спасибо! Награда: 50 000 монет и 3 предмета #20.', [
            ['label' => 'Уйти', 'close' => true],
        ]);
    }

    private function turnInCarolQuest(int $userId): array
    {
        if (!$this->inventory->hasItem($userId, 4, 1)) {
            return $this->dialog('Кэрол', 'Игрушки нет. Пожалуйста, найди ее.', [
                ['label' => 'Пойду искать', 'close' => true],
            ]);
        }

        $this->inventory->removeItem($userId, 4, 1);
        $this->grantRewardItems($userId, [1 => 10000, 5 => 3], 'Квест: Игрушка Кэрол');
        $this->quests->updateState($userId, 4, 11, 1);
        $this->addQuestRank($userId, 3);

        return $this->dialog('Кэрол', 'Ура! Спасибо, что вернул игрушку. Награда: 10 000 монет и 3 предмета #5.', [
            ['label' => 'Будь осторожна', 'close' => true],
        ]);
    }

    private function turnInResearchQuest(int $userId): array
    {
        $ids = $this->findActivePokemonBatch($userId, 116, 40, 10);
        if (count($ids) < 10) {
            return $this->dialog('Исследователь', 'Я не вижу десяти Horsea 40 уровня в активной команде.', [
                ['label' => 'Вернусь позже', 'close' => true],
            ]);
        }

        $this->deleteQuestPokemon($userId, $ids);
        $this->grantRewardItems($userId, [1 => 25000], 'Квест: Исследование Horsea');
        $this->quests->updateState($userId, 5, 20, 1);
        $this->addQuestRank($userId, 2);

        return $this->dialog('Исследователь', 'Данные получены. Награда: 25 000 монет.', [
            ['label' => 'Спасибо', 'close' => true],
        ]);
    }

    private function turnInMetapodDaily(int $userId): array
    {
        $daily = $this->quests->findForUser($userId, 6);
        if ($daily !== null && (int) ($daily['time'] ?? 0) > time()) {
            return $this->dialog('Исследователь', 'Сегодня награда уже получена.', [
                ['label' => 'Уйти', 'close' => true],
            ]);
        }
        if ($daily === null || (int) ($daily['process'] ?? 0) < 10 || (int) ($daily['gotov'] ?? 0) === 1) {
            return $this->dialog('Исследователь', 'Сначала возьми ежедневное задание.', [
                ['label' => 'Взять задание', 'action' => 'quest_metapod_start'],
                ['label' => 'Уйти', 'close' => true],
            ]);
        }

        $ids = $this->findActivePokemonBatch($userId, 11, 9, 5);
        if (count($ids) < 5) {
            return $this->dialog('Исследователь', 'Нужно пять Metapod 9 уровня.', [
                ['label' => 'Вернусь позже', 'close' => true],
            ]);
        }

        $this->deleteQuestPokemon($userId, $ids);
        $this->grantRewardItems($userId, [1 => 10000, 3 => 10], 'Квест: Metapod на сегодня');
        $this->quests->setCooldown($userId, 6, 1, time() + 86400);

        return $this->dialog('Исследователь', 'Спасибо. Награда: 10 000 монет и 10 покеболов. Возвращайся завтра.', [
            ['label' => 'Уйти', 'close' => true],
        ]);
    }

    private function craftStone(int $userId, string $key): array
    {
        if (!isset(self::STONE_RECIPES[$key])) {
            return $this->error('Неизвестный рецепт.');
        }

        $recipe = self::STONE_RECIPES[$key];
        $missing = $this->missingParts($userId, $recipe['parts']);
        if ($missing !== '') {
            return $this->dialog('Мастеровой', 'Не хватает ресурсов: ' . $missing, [
                ['label' => 'Назад к крафту', 'params' => ['quest_npc' => '3', 'do' => '3']],
            ]);
        }

        $this->db->beginTransaction();
        try {
            foreach ($recipe['parts'] as $itemId => $count) {
                $this->inventory->removeItem($userId, (int) $itemId, (int) $count);
            }
            $success = random_int(0, 10) > 5;
            if ($success) {
                $this->grantRewardItems($userId, [(int) $recipe['item'] => 1], 'Крафт предмета');
            }
            $this->db->commit();
        } catch (\Throwable) {
            $this->db->rollBack();
            return $this->error('Не удалось выполнить крафт.');
        }

        return $this->dialog('Мастеровой', $success ? 'Успешно изготовлено: ' . $recipe['name'] . '.' : 'Крафт не удался. Материалы потрачены.', [
            ['label' => 'Еще крафт', 'params' => ['quest_npc' => '3', 'do' => '3']],
            ['label' => 'Уйти', 'close' => true],
        ]);
    }

    private function secretaryReputation(int $userId): array
    {
        if (!$this->inventory->hasItem($userId, 1, 10000)) {
            return $this->dialog('Секретарь', 'Для проверки репутации нужно 10 000 монет.', [
                ['label' => 'Другой вопрос', 'params' => ['npc' => '1', 'do' => '1']],
                ['label' => 'Уйти', 'close' => true],
            ]);
        }

        $this->inventory->removeItem($userId, 1, 10000);
        $stmt = $this->db->prepare('SELECT rang_a, rang_b, rang_c FROM users WHERE id = :user LIMIT 1');
        $stmt->execute(['user' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['rang_a' => 0, 'rang_b' => 0, 'rang_c' => 0];

        return $this->dialog('Секретарь', sprintf(
            'Ранг PvE: %d. Ранг PvP: %d. Квестовые очки: %d.',
            (int) $user['rang_a'],
            (int) $user['rang_b'],
            (int) $user['rang_c']
        ), [
            ['label' => 'Другой вопрос', 'params' => ['npc' => '1', 'do' => '1']],
            ['label' => 'Уйти', 'close' => true],
        ]);
    }

    private function missingParts(int $userId, array $parts): string
    {
        $missing = [];
        foreach ($parts as $itemId => $required) {
            $have = $this->inventory->countItem($userId, (int) $itemId);
            if ($have < (int) $required) {
                $missing[] = '#' . $itemId . ' ' . $have . '/' . $required;
            }
        }

        return implode(', ', $missing);
    }

    private function findActivePokemonForQuest(int $userId, int $baseId, int $level): int
    {
        $stmt = $this->db->prepare(
            'SELECT id FROM pok_user
              WHERE users = :user AND active = 1 AND basenum = :base AND lvl >= :level
              ORDER BY lvl ASC, id ASC LIMIT 1'
        );
        $stmt->execute(['user' => $userId, 'base' => $baseId, 'level' => $level]);

        return (int) ($stmt->fetchColumn() ?: 0);
    }

    private function findActivePokemonBatch(int $userId, int $baseId, int $level, int $limit): array
    {
        $stmt = $this->db->prepare(
            'SELECT id FROM pok_user
              WHERE users = :user AND active = 1 AND basenum = :base AND lvl >= :level
              ORDER BY lvl ASC, id ASC LIMIT :limit'
        );
        $stmt->bindValue(':user', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':base', $baseId, PDO::PARAM_INT);
        $stmt->bindValue(':level', $level, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN) ?: []);
    }

    private function deleteQuestPokemon(int $userId, array $pokemonIds): void
    {
        foreach ($pokemonIds as $pokemonId) {
            $this->db->prepare('DELETE FROM attac_my_poke WHERE pok_id = :pokemon')->execute(['pokemon' => $pokemonId]);
            $this->db->prepare('DELETE FROM pok_user WHERE id = :pokemon AND users = :user LIMIT 1')->execute([
                'pokemon' => $pokemonId,
                'user' => $userId,
            ]);
        }
    }

    private function addQuestRank(int $userId, int $points): void
    {
        $stmt = $this->db->prepare('UPDATE users SET rang_c = rang_c + :points WHERE id = :user LIMIT 1');
        $stmt->execute(['points' => $points, 'user' => $userId]);
    }

    /**
     * @param array<int,int> $items
     */
    private function grantRewardItems(int $userId, array $items, string $source): void
    {
        if ($this->rewards !== null) {
            $this->rewards->grantItems($userId, $items, $source);
            return;
        }

        foreach ($items as $itemId => $count) {
            $this->inventory->addItem($userId, (int) $itemId, (int) $count);
        }
    }

    private function grantPokemon(int $userId, int $baseId, int $level, bool $starter): int
    {
        $base = $this->rowById('poke_base', 'id', $baseId);
        if (!$base) {
            return 0;
        }

        $nature = $this->rowById('har', 'id_har', 16) ?: ['atk' => 1, 'def' => 1, 'satk' => 1, 'sdef' => 1, 'speed' => 1];
        $iv = 1;
        $ev = 0;
        $stats = [
            'hp' => (int) round((($iv + ((int) $base['hp'] * 2) + ($ev / 4) + 100) * ($level / 100)) + 10),
            'atk' => (int) round(((($iv + ((int) $base['atk'] * 2) + ($ev / 4)) * ($level / 100)) + 5) * (float) $nature['atk']),
            'def' => (int) round(((($iv + ((int) $base['def'] * 2) + ($ev / 4)) * ($level / 100)) + 5) * (float) $nature['def']),
            'satk' => (int) round(((($iv + ((int) $base['satk'] * 2) + ($ev / 4)) * ($level / 100)) + 5) * (float) $nature['satk']),
            'sdef' => (int) round(((($iv + ((int) $base['sdef'] * 2) + ($ev / 4)) * ($level / 100)) + 5) * (float) $nature['sdef']),
            'speed' => (int) round(((($iv + ((int) $base['speed'] * 2) + ($ev / 4)) * ($level / 100)) + 5) * (float) $nature['speed']),
        ];

        $pokemonId = $this->nextTableId('pok_user', 'id');
        $name = (string) ($base['title'] ?? ('Pokemon #' . $baseId));
        $stmt = $this->db->prepare(
            'INSERT INTO pok_user
                (id, users, basenum, names, active, evcount, lvl, sex, har, hp_my, hp_max, exp, exp_b,
                 atk, def, satk, sdef, speed, hp_ev, atk_ev, def_ev, satk_ev, sdef_ev, speed_ev,
                 hp_iv, atk_iv, def_iv, satk_iv, sdef_iv, speed_iv, tips, startone, startepoke,
                 reproduction, happy, datemay, usersone, sprz, item, ability_key)
             VALUES
                (:id, :users, :base, :name, 1, 0, :lvl, 1, 16, :hp_my, :hp_max, 0, 100,
                 :atk, :def, :satk, :sdef, :speed, 0, 0, 0, 0, 0, 0,
                 1, 1, 1, 1, 1, 1, "normal", :startone, :starter,
                 0, 0, NOW(), :usersone, 0, 0, :ability)'
        );
        $stmt->execute([
            'id' => $pokemonId,
            'users' => $userId,
            'base' => $baseId,
            'name' => $name,
            'lvl' => $level,
            'hp_my' => $stats['hp'],
            'hp_max' => $stats['hp'],
            'atk' => $stats['atk'],
            'def' => $stats['def'],
            'satk' => $stats['satk'],
            'sdef' => $stats['sdef'],
            'speed' => $stats['speed'],
            'startone' => $starter ? 1 : 0,
            'starter' => $starter ? 1 : 0,
            'usersone' => $userId,
            'ability' => $base['ability_key'] ?? null,
        ]);

        $this->seedPokemonMoves($pokemonId, $baseId, $level);
        return $pokemonId;
    }

    private function seedPokemonMoves(int $pokemonId, int $baseId, int $level): void
    {
        $stmt = $this->db->prepare(
            'SELECT ap.atac_id, COALESCE(power.atac_pp, 0) AS pp
               FROM attac_poke ap
          LEFT JOIN attac_power power ON power.atac_id = ap.atac_id
              WHERE ap.poke_base_id = :base AND ap.atc_lvl <= :level
              ORDER BY ap.atc_lvl DESC, ap.id_structure DESC
              LIMIT 4'
        );
        $stmt->execute(['base' => $baseId, 'level' => $level]);
        $moves = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $slots = array_fill(0, 4, ['id' => 0, 'pp' => 0]);
        foreach ($moves as $index => $move) {
            $slots[$index] = ['id' => (int) $move['atac_id'], 'pp' => max(0, (int) $move['pp'])];
        }

        $this->db->prepare(
            'INSERT INTO attac_my_poke
                (id, pok_id, a_id, a_pp_min, a_pp_max, b_id, b_pp_min, b_pp_max, c_id, c_pp_min, c_pp_max, d_id, d_pp_min, d_pp_max)
             VALUES
                (:id, :pokemon, :a_id, :a_pp_min, :a_pp_max, :b_id, :b_pp_min, :b_pp_max, :c_id, :c_pp_min, :c_pp_max, :d_id, :d_pp_min, :d_pp_max)'
        )->execute([
            'id' => $this->nextTableId('attac_my_poke', 'id'),
            'pokemon' => $pokemonId,
            'a_id' => $slots[0]['id'],
            'a_pp_min' => $slots[0]['pp'],
            'a_pp_max' => $slots[0]['pp'],
            'b_id' => $slots[1]['id'],
            'b_pp_min' => $slots[1]['pp'],
            'b_pp_max' => $slots[1]['pp'],
            'c_id' => $slots[2]['id'],
            'c_pp_min' => $slots[2]['pp'],
            'c_pp_max' => $slots[2]['pp'],
            'd_id' => $slots[3]['id'],
            'd_pp_min' => $slots[3]['pp'],
            'd_pp_max' => $slots[3]['pp'],
        ]);
    }

    private function rowById(string $table, string $column, int $id): ?array
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table) || !preg_match('/^[a-zA-Z0-9_]+$/', $column)) {
            return null;
        }

        $stmt = $this->db->prepare(sprintf('SELECT * FROM `%s` WHERE `%s` = :id LIMIT 1', $table, $column));
        $stmt->execute(['id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function nextTableId(string $table, string $column): int
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table) || !preg_match('/^[a-zA-Z0-9_]+$/', $column)) {
            return 1;
        }

        return (int) ($this->db->query(sprintf('SELECT COALESCE(MAX(`%s`), 0) + 1 FROM `%s`', $column, $table))->fetchColumn() ?: 1);
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

    private function npcTitle(?array $npc, array $params, int $locationId): string
    {
        if (($params['quest_npc'] ?? '') !== '') {
            return $this->questNpcTitle((int) $params['quest_npc'], $locationId);
        }
        if (($params['npc'] ?? '') === '1' && ($params['do_npc'] ?? '') === 'pc') {
            return 'Покецентр';
        }
        if (($params['npc'] ?? '') === '1' && in_array($locationId, [23, 25], true)) {
            return 'Касса';
        }
        if (($params['npc'] ?? '') === '1' && ($params['do'] ?? '') === '1' && $locationId === 17) {
            return 'Секретарь';
        }
        if (($params['npc'] ?? '') === '2') {
            return 'Покемаркет';
        }
        if (($params['npc'] ?? '') === '3' && in_array($locationId, [23, 25], true)) {
            return 'Теплоход';
        }
        if (($params['npc'] ?? '') === '3' && $locationId === 16) {
            return 'Продавец удочек';
        }
        if (($params['npc'] ?? '') === '3') {
            return 'Куратор';
        }
        if (($params['npc'] ?? '') === '4') {
            return 'Старший тренер';
        }
        if (($params['npc'] ?? '') === '5') {
            return 'Смотритель арены';
        }

        $title = (string) ($npc['title'] ?? '');
        return $this->looksMojibake($title) ? '' : $title;
    }

    private function looksMojibake(string $value): bool
    {
        foreach (['Рђ', 'Рџ', 'РЅ', 'Рµ', 'Рё', 'СЃ', 'С‚', 'СЏ', 'СЊ', '�'] as $marker) {
            if (str_contains($value, $marker)) {
                return true;
            }
        }

        return false;
    }

    private function isSisterJoy(int $locationId, array $params): bool
    {
        return (($params['npc'] ?? '') === '1' && ($params['do_npc'] ?? '') !== '') || $this->hasSisterJoy($locationId) && ($params['npc'] ?? '') === '1';
    }

    private function isMarket(array $params): bool
    {
        return ($params['npc'] ?? '') === '2';
    }

    private function isCurator(int $locationId, array $params): bool
    {
        return ($params['npc'] ?? '') === '3' && !in_array($locationId, [23, 25], true);
    }

    private function isTransportNpc(int $locationId, array $params): bool
    {
        return ($params['npc'] ?? '') === '3' && in_array($locationId, [23, 25], true);
    }

    private function isTicketOffice(int $locationId, array $params): bool
    {
        return ($params['npc'] ?? '') === '1' && in_array($locationId, [23, 25], true);
    }

    private function isStadiumNpc(int $locationId, array $params): bool
    {
        return $locationId === 81 || in_array((string) ($params['npc'] ?? ''), ['4', '5'], true);
    }

    private function isSecretary(int $locationId, array $params): bool
    {
        return $locationId === 17 && ($params['npc'] ?? '') === '1' && ($params['do'] ?? '') === '1';
    }

    private function questNpcTitle(int $questNpcId, int $locationId): string
    {
        if ($locationId === 3 && $questNpcId === 1) {
            return 'Профессор Оук';
        }
        if ($locationId === 7 && $questNpcId === 1) {
            return 'Старая женщина';
        }
        if ($locationId === 11 && $questNpcId === 1) {
            return 'Художница Амира';
        }
        if ($locationId === 13 && $questNpcId === 1) {
            return '#144 Articuno';
        }
        if ($locationId === 18 && $questNpcId === 1) {
            return 'Куратор ипподрома Гарен';
        }
        if ($locationId === 5 && $questNpcId === 4) {
            return 'Циркач Стив';
        }
        if ($locationId === 6 && $questNpcId === 5) {
            return 'Кэрол';
        }
        if ($locationId === 10 && $questNpcId === 8) {
            return 'Цветочный прилавок';
        }

        return [
            1 => 'Сюжетный NPC',
            2 => 'Случайный прохожий',
            3 => 'Странный Спайк',
            4 => 'Сюжетный NPC',
            5 => 'Сюжетный NPC',
            7 => 'Коллекционер Билли',
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
