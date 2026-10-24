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
    use NpcDialogEventTrait;

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

        if (($params['npc'] ?? '') === 'flight_status') {
            return $this->flightStatusDialog();
        }

        if (($params['npc'] ?? '') === 'flight_exit') {
            return $this->flightExitDialog();
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

        // Квест 160 «Сон о ледяном крыле»: шаги обслуживает Старая женщина
        // (локация 7, quest_npc = 1) — она же знахарка из лора Айрена.
        if ($locationId === 7
            && (int) ($params['quest_npc'] ?? 0) === 1
            && in_array($action, ['airen_ice_dream_start', 'airen_ice_catch', 'airen_ice_turnin'], true)
        ) {
            if ($action === 'airen_ice_dream_start') {
                return $this->airenIceDreamStart($userId);
            }
            if ($action === 'airen_ice_catch') {
                return $this->airenIceCatch($userId);
            }
            return $this->airenIceTurnIn($userId);
        }

        if ($locationId === 11
            && (int) ($params['quest_npc'] ?? 0) === 1
            && in_array($action, ['amira_lake_start', 'amira_storm_wins', 'amira_lake_turnin'], true)
        ) {
            if ($action === 'amira_lake_start') {
                return $this->amiraLakeStart($userId);
            }
            if ($action === 'amira_storm_wins') {
                return $this->amiraStormWins($userId);
            }
            return $this->amiraLakeTurnIn($userId);
        }

        if ($locationId === 4
            && (int) ($params['quest_npc'] ?? 0) === 7
            && in_array($action, ['billy_feather_start', 'billy_brave_catch', 'billy_feather_turnin'], true)
        ) {
            if ($action === 'billy_feather_start') {
                return $this->billyFeatherStart($userId);
            }
            if ($action === 'billy_brave_catch') {
                return $this->billyBraveCatch($userId);
            }
            return $this->billyFeatherTurnIn($userId);
        }

        if (str_starts_with($action, 'quest_start:')) {
            return $this->questStartAction($userId, $locationId, $params, $action);
        }

        if (str_starts_with($action, 'quest_update:')) {
            return $this->questUpdateAction($userId, $locationId, $params, $action);
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

        if ($action === 'quest_old_woman_turnin') {
            return $this->turnInOldWomanQuest($userId);
        }

        if ($action === 'quest_airen_complete') {
            return $this->completeAirenQuest($userId);
        }

        if (str_starts_with($action, 'quest_jafit_turnin:')) {
            return $this->turnInJafitQuest($userId, (int) substr($action, strlen('quest_jafit_turnin:')));
        }

        if (str_starts_with($action, 'hippodrome_register:')) {
            return $this->hippodromeRegisterAction($userId, $action);
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
            return $this->dialog('Секретарь', 'Клановые захваты временно готовятся к новому сезону. Когда регистрация откроется, заявки и правила появятся в отдельном игровом окне.', [
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
            return $this->dialog('NPC', 'Отмечено. Встреча записана, персонаж останется доступен на этой локации.', [
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
        $featherQuest = $this->quests->findForUser($userId, 162);
        $featherDone = $featherQuest !== null && (int) ($featherQuest['gotov'] ?? 0) === 1;
        $featherButtons = [];
        if (!$featherDone) {
            $featherProcess = (int) ($featherQuest['process'] ?? 0);
            if ($featherProcess >= 7) {
                $featherButtons[] = ['label' => 'Сдать клевер и конфету', 'action' => 'billy_feather_turnin'];
            } elseif ($featherProcess >= 6) {
                $featherButtons[] = ['label' => 'Показать смелого покемона', 'action' => 'billy_brave_catch'];
            } else {
                $featherButtons[] = ['label' => 'Спросить о пере', 'action' => 'billy_feather_start'];
            }
        }

        $quest = $this->quests->findForUser($userId, 7);
        $completed = $quest !== null && (int) ($quest['gotov'] ?? 0) === 1;
        $inProgress = $quest !== null && !$completed;

        if ($completed) {
            return $this->dialog('Коллекционер Билли', 'Спасибо за помощь с коллекцией перьев. Твой вклад уже занесен в каталог.', array_merge(
                $featherButtons,
                [['label' => 'Удачи', 'close' => true]]
            ));
        }

        if ($inProgress) {
            $pidgeottoFeathers = $this->inventory->countItem($userId, 13);
            $spearowFeathers = $this->inventory->countItem($userId, 14);
            $ready = $pidgeottoFeathers >= 10 && $spearowFeathers >= 10;

            return $this->dialog('Коллекционер Билли', 'Нужно 10 перьев Pidgeotto и 10 перьев Spearow.', array_merge([
                ['label' => 'Пока нет', 'close' => true],
                [
                    'label' => 'Сдать перья',
                    'action' => 'turn_in_billy_quest',
                    'disabled' => !$ready,
                    'hint' => 'Pidgeotto: ' . $pidgeottoFeathers . '/10, Spearow: ' . $spearowFeathers . '/10',
                ],
            ], $featherButtons));
        }

        if ($step === 2) {
            return $this->dialog('Коллекционер Билли', 'Я собираю редкие перья летающих покемонов. Поможешь собрать образцы?', [
                ['label' => 'Я согласен', 'action' => 'accept_billy_quest'],
                ['label' => 'Мне некогда', 'close' => true],
            ]);
        }

        return $this->dialog('Коллекционер Билли', 'Привет. Я наблюдаю за птицами на этой дороге и собираю коллекцию перьев.', array_merge([
            ['label' => 'Почему ты смотришь вдаль?', 'params' => ['quest_npc' => '7', 'do' => '2']],
        ], $featherButtons, [
            ['label' => 'Просто осматриваюсь', 'close' => true],
        ]));
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

        $this->db->beginTransaction();
        try {
        if (!$this->inventory->removeItem($userId, 13, 10) || !$this->inventory->removeItem($userId, 14, 10)) {
            $this->db->rollBack();
            return $this->error('Not enough quest items.');
        }
        $this->grantRewardItems($userId, [1 => 5000, 9 => 5, 10 => 3], 'Квест: Перья для Билли');
        $this->quests->updateState($userId, 7, 20, 1);
        $this->addQuestRank($userId, 2);
        $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            error_log('[npc_dialog] billy quest failed: ' . $e->getMessage());
            return $this->error('Quest reward failed.');
        }

        return $this->dialog('Коллекционер Билли', 'Отличные экземпляры. Награда: 5000 монет, красные и фиолетовые конфеты.', [
            ['label' => 'Спасибо', 'close' => true],
        ]);
    }

    /**
     * Квест 160 «Сон о ледяном крыле» — начало.
     * NPC: Старая женщина (знахарка), локация 7, quest_npc = 1.
     */
    private function airenIceDreamStart(int $userId): array
    {
        $quest = $this->quests->findForUser($userId, 160);

        if ($quest !== null && (int) ($quest['gotov'] ?? 0) === 1) {
            return $this->dialog('Старая женщина', 'Сон Айрена уже разгадан. Ты выполнил это поручение.', [
                ['label' => 'Уйти', 'close' => true],
            ]);
        }

        if ($quest === null) {
            $this->quests->createIfMissing($userId, 160, 2);
        } elseif ((int) ($quest['process'] ?? 0) < 2) {
            $this->quests->updateState($userId, 160, 2, 0);
        }

        return $this->dialog('Старая женщина', 'Айрену снится странный сон. Огромная птица с ледяными крыльями парит над тёмным озером. Каждую ночь она зовёт его, но он не понимает почему. Возможно, ледяной покемон поможет раскрыть смысл этого сна. Поймай покемона ледяного типа не ниже 15 уровня и возвращайся ко мне.', [
            ['label' => 'Проверить покемона', 'action' => 'airen_ice_catch'],
            ['label' => 'Отправиться на поиски', 'close' => true],
        ]);
    }

    /**
     * Шаг 2 квеста 160: у игрока должен быть покемон ледяного типа 15+.
     * Тип берётся из pokemon.Element (текстовое значение 'Ice'), потому что
     * числовые tip_a/tip_b не сопоставлены со справочником типов.
     */
    private function airenIceCatch(int $userId): array
    {
        $quest = $this->quests->findForUser($userId, 160);

        if ($quest === null) {
            return $this->error('Сначала нужно взять задание у Старой женщины.');
        }

        if ((int) ($quest['gotov'] ?? 0) === 1) {
            return $this->dialog('Старая женщина', 'Ты уже помог разгадать сон Айрена. Задание завершено.', [
                ['label' => 'Уйти', 'close' => true],
            ]);
        }

        if ((int) ($quest['process'] ?? 0) < 2) {
            return $this->error('Сначала нужно выслушать рассказ о сне Айрена.');
        }

        if ((int) ($quest['process'] ?? 0) >= 3) {
            return $this->dialog('Старая женщина', 'Ледяной покемон уже найден. Теперь принеси 3 энергетика и 2 пера Pidgeotto или Spearow. Покемон ледяного типа 15 уровня или выше должен оставаться у тебя.', [
                ['label' => 'Сдать задание', 'action' => 'airen_ice_turnin'],
                ['label' => 'Вернусь позже', 'close' => true],
            ]);
        }

        $stmt = $this->db->prepare(
            'SELECT pu.id
               FROM pok_user pu
               INNER JOIN pokemon p ON p.id = pu.basenum
              WHERE pu.users = :user
                AND pu.lvl >= :min_level
                AND FIND_IN_SET("Ice", REPLACE(p.Element, " ", "")) > 0
              LIMIT 1'
        );
        $stmt->execute([
            'user' => $userId,
            'min_level' => 15,
        ]);

        if (!$stmt->fetchColumn()) {
            return $this->dialog('Старая женщина', 'Я пока не вижу у тебя подходящего покемона. Нужно поймать покемона ледяного типа не ниже 15 уровня. Возвращайся, когда он появится у тебя.', [
                ['label' => 'Проверить снова', 'action' => 'airen_ice_catch'],
                ['label' => 'Продолжить поиски', 'close' => true],
            ]);
        }

        $this->quests->updateState($userId, 160, 3, 0);

        return $this->dialog('Старая женщина', 'Вот он, холод, похожий на дыхание птицы из сна Айрена! Ты нашёл подходящего ледяного покемона. Для следующего шага мне понадобятся 3 энергетика и 2 пера Pidgeotto или Spearow. Принеси их, не теряя своего ледяного спутника.', [
            ['label' => 'Сдать предметы', 'action' => 'airen_ice_turnin'],
            ['label' => 'Собрать необходимое', 'close' => true],
        ]);
    }

    /**
     * Шаг 3 квеста 160: ледяной покемон 15+ (остаётся у игрока) и предметы —
     * 3 энергетика (#15) и 2 пера (#13/#14 в любой комбинации).
     */
    private function airenIceTurnIn(int $userId): array
    {
        $quest = $this->quests->findForUser($userId, 160);

        if ($quest === null || (int) ($quest['gotov'] ?? 0) === 1) {
            return $this->error('Сначала нужно взять задание у Старой женщины.');
        }

        if ((int) ($quest['process'] ?? 0) < 3) {
            return $this->dialog('Старая женщина', 'Сначала нужно найти ледяного покемона не ниже 15 уровня и показать мне его.', [
                ['label' => 'Проверить покемона', 'action' => 'airen_ice_catch'],
                ['label' => 'Вернусь позже', 'close' => true],
            ]);
        }

        // Повторная проверка: покемон остаётся у игрока и не списывается.
        $stmt = $this->db->prepare(
            'SELECT pu.id
               FROM pok_user pu
               INNER JOIN pokemon p ON p.id = pu.basenum
              WHERE pu.users = :user
                AND pu.lvl >= :min_level
                AND FIND_IN_SET("Ice", REPLACE(p.Element, " ", "")) > 0
              LIMIT 1'
        );
        $stmt->execute([
            'user' => $userId,
            'min_level' => 15,
        ]);

        if (!$stmt->fetchColumn()) {
            return $this->dialog('Старая женщина', 'Где твой ледяной покемон? Для завершения задания у тебя должен быть хотя бы один покемон ледяного типа не ниже 15 уровня.', [
                ['label' => 'Вернусь с покемоном', 'close' => true],
            ]);
        }

        $energyCount = $this->inventory->countItem($userId, 15);
        $feather13Count = $this->inventory->countItem($userId, 13);
        $feather14Count = $this->inventory->countItem($userId, 14);

        if ($energyCount < 3 || ($feather13Count + $feather14Count) < 2) {
            return $this->dialog('Старая женщина', 'Для ритуала не хватает предметов. Мне нужны 3 энергетика и 2 пера Pidgeotto или Spearow. Перья можно смешивать: главное, чтобы их было две штуки суммарно.', [
                ['label' => 'Вернусь позже', 'close' => true],
            ]);
        }

        // Списываем ровно два пера в любой доступной комбинации.
        $remove13 = min(2, $feather13Count);
        $remove14 = 2 - $remove13;

        $this->db->beginTransaction();
        try {
            if (
                !$this->inventory->removeItem($userId, 15, 3)
                || ($remove13 > 0 && !$this->inventory->removeItem($userId, 13, $remove13))
                || ($remove14 > 0 && !$this->inventory->removeItem($userId, 14, $remove14))
            ) {
                $this->db->rollBack();
                return $this->error('Not enough quest items.');
            }

            $this->grantRewardItems($userId, [1 => 8000, 3 => 10, 5 => 5], 'Квест: Сон о ледяном крыле');
            $this->quests->updateState($userId, 160, 20, 1);
            $this->addQuestRank($userId, 2);
            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('[npc_dialog] airen ice dream quest failed: ' . $e->getMessage());
            return $this->error('Quest reward failed.');
        }

        return $this->dialog('Старая женщина', 'Всё готово. Ледяная сила твоего покемона и собранные предметы помогли мне разгадать сон Айрена. Птица с ледяными крыльями была знаком Артикуно. Теперь Айрен сможет понять её зов. Ты отлично справился! Награда: 8000 монет, 10 покеболов и 5 энергетиков, а также +2 к квестовому рангу.', [
            ['label' => 'Спасибо', 'close' => true],
        ]);
    }

    private function amiraLakeStart(int $userId): array
    {
        $quest = $this->quests->findForUser($userId, 161);
        if ($quest !== null && (int) ($quest['gotov'] ?? 0) === 1) {
            return $this->dialog('Художница Амира', 'Озеро уже услышало твой ответ. Спасибо, тренер.', [
                ['label' => 'Уйти', 'close' => true],
            ]);
        }

        if ($quest === null) {
            if (!$this->quests->createIfMissing($userId, 161, 4)) {
                return $this->dialog('Художница Амира', 'Озеро пока молчит. Сначала заверши историю ледяного сна, а потом возвращайся ко мне.', [
                    ['label' => 'Вернусь позже', 'close' => true],
                ]);
            }
        } elseif ((int) ($quest['process'] ?? 0) < 4) {
            $this->quests->updateState($userId, 161, 4, 0);
        }

        return $this->dialog('Художница Амира', 'Вода шепчет перед грозой. Победи пять диких покемонов, чтобы буря услышала тебя, а потом возвращайся к озеру.', [
            ['label' => 'Рассказать о победах', 'action' => 'amira_storm_wins'],
            ['label' => 'Я вернусь', 'close' => true],
        ]);
    }

    private function amiraStormWins(int $userId): array
    {
        $quest = $this->quests->findForUser($userId, 161);
        if ($quest === null) {
            return $this->error('Сначала нужно взять задание у Амиры.');
        }
        if ((int) ($quest['gotov'] ?? 0) === 1) {
            return $this->dialog('Художница Амира', 'Ты уже помог озеру пережить бурю.', [
                ['label' => 'Уйти', 'close' => true],
            ]);
        }
        if ((int) ($quest['process'] ?? 0) < 4) {
            return $this->error('Сначала выслушай просьбу Амиры.');
        }
        if ((int) ($quest['process'] ?? 0) >= 5) {
            return $this->dialog('Художница Амира', 'Гроза уже стихла. Осталось принести 2 энергетика для последнего мазка.', [
                ['label' => 'Сдать энергетики', 'action' => 'amira_lake_turnin'],
                ['label' => 'Позже', 'close' => true],
            ]);
        }

        $wins = $this->countArchivedPveWins($userId);
        if ($wins < 5) {
            return $this->dialog('Художница Амира', 'Я пока не чувствую в твоем следе пяти побед над дикими покемонами. Возвращайся, когда буря признает твою силу.', [
                ['label' => 'Проверить снова', 'action' => 'amira_storm_wins'],
                ['label' => 'Вернусь позже', 'close' => true],
            ]);
        }

        $this->quests->updateState($userId, 161, 5, 0);

        return $this->dialog('Художница Амира', 'Теперь вода стала тише. Для завершения картины нужны 2 энергетика - они удержат свет над озером.', [
            ['label' => 'Сдать энергетики', 'action' => 'amira_lake_turnin'],
            ['label' => 'Собрать предметы', 'close' => true],
        ]);
    }

    private function amiraLakeTurnIn(int $userId): array
    {
        $quest = $this->quests->findForUser($userId, 161);
        if ($quest === null || (int) ($quest['gotov'] ?? 0) === 1) {
            return $this->error('Сначала нужно взять задание у Амиры.');
        }
        if ((int) ($quest['process'] ?? 0) < 5) {
            return $this->dialog('Художница Амира', 'Сначала победи пять диких покемонов и вернись к озеру.', [
                ['label' => 'Проверить победы', 'action' => 'amira_storm_wins'],
                ['label' => 'Позже', 'close' => true],
            ]);
        }
        if ($this->inventory->countItem($userId, 15) < 2) {
            return $this->dialog('Художница Амира', 'Для обряда у озера нужны 2 энергетика. Пока их не хватает.', [
                ['label' => 'Вернусь с энергетиками', 'close' => true],
            ]);
        }

        $this->db->beginTransaction();
        try {
            if (!$this->inventory->removeItem($userId, 15, 2)) {
                $this->db->rollBack();
                return $this->error('Not enough quest items.');
            }
            $this->grantRewardItems($userId, [1 => 12000, 5 => 8, 9 => 2], 'Квест: Шепот озера');
            $this->quests->updateState($userId, 161, 20, 1);
            $this->addQuestRank($userId, 3);
            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('[npc_dialog] amira lake quest failed: ' . $e->getMessage());
            return $this->error('Quest reward failed.');
        }

        return $this->dialog('Художница Амира', 'Озеро успокоилось, а картина наконец задышала. Прими награду.', [
            ['label' => 'Спасибо', 'close' => true],
        ]);
    }

    private function billyFeatherStart(int $userId): array
    {
        $quest = $this->quests->findForUser($userId, 162);
        if ($quest !== null && (int) ($quest['gotov'] ?? 0) === 1) {
            return $this->dialog('Коллекционер Билли', 'Перо уже лежит в коллекции. Хорошая работа.', [
                ['label' => 'Уйти', 'close' => true],
            ]);
        }

        if ($quest === null) {
            if (!$this->quests->createIfMissing($userId, 162, 6)) {
                return $this->dialog('Коллекционер Билли', 'Пока рано искать это перо. Сначала помоги Амире у озера, потом возвращайся на дорогу.', [
                    ['label' => 'Вернусь позже', 'close' => true],
                ]);
            }
        } elseif ((int) ($quest['process'] ?? 0) < 6) {
            $this->quests->updateState($userId, 162, 6, 0);
        }

        return $this->dialog('Коллекционер Билли', 'Мне нужно перо, которое не дрогнет на ветру. Покажи мне покемона со смелым характером, и я пойму, что тебе можно доверить поиск.', [
            ['label' => 'Показать смелого покемона', 'action' => 'billy_brave_catch'],
            ['label' => 'Пойду искать', 'close' => true],
        ]);
    }

    private function billyBraveCatch(int $userId): array
    {
        $quest = $this->quests->findForUser($userId, 162);
        if ($quest === null) {
            return $this->error('Сначала нужно взять задание у Билли.');
        }
        if ((int) ($quest['gotov'] ?? 0) === 1) {
            return $this->dialog('Коллекционер Билли', 'Коллекция уже пополнена твоей находкой.', [
                ['label' => 'Уйти', 'close' => true],
            ]);
        }
        if ((int) ($quest['process'] ?? 0) < 6) {
            return $this->error('Сначала выслушай просьбу Билли.');
        }
        if ((int) ($quest['process'] ?? 0) >= 7) {
            return $this->dialog('Коллекционер Билли', 'Смелость я уже увидел. Теперь нужны 10 сушеных клеверов и 1 конфета.', [
                ['label' => 'Сдать предметы', 'action' => 'billy_feather_turnin'],
                ['label' => 'Позже', 'close' => true],
            ]);
        }

        $stmt = $this->db->prepare(
            'SELECT id
               FROM pok_user
              WHERE users = :user
                AND active = 1
                AND har = :nature
              LIMIT 1'
        );
        $stmt->execute([
            'user' => $userId,
            'nature' => 7,
        ]);

        if (!$stmt->fetchColumn()) {
            return $this->dialog('Коллекционер Билли', 'Я пока не вижу у тебя покемона со смелым характером. Поймай такого и возвращайся.', [
                ['label' => 'Проверить снова', 'action' => 'billy_brave_catch'],
                ['label' => 'Пойду искать', 'close' => true],
            ]);
        }

        $this->quests->updateState($userId, 162, 7, 0);

        return $this->dialog('Коллекционер Билли', 'Вот это взгляд. Теперь принеси 10 сушеных клеверов и 1 конфету - перо нужно закрепить правильно.', [
            ['label' => 'Сдать предметы', 'action' => 'billy_feather_turnin'],
            ['label' => 'Собрать предметы', 'close' => true],
        ]);
    }

    private function billyFeatherTurnIn(int $userId): array
    {
        $quest = $this->quests->findForUser($userId, 162);
        if ($quest === null || (int) ($quest['gotov'] ?? 0) === 1) {
            return $this->error('Сначала нужно взять задание у Билли.');
        }
        if ((int) ($quest['process'] ?? 0) < 7) {
            return $this->dialog('Коллекционер Билли', 'Сначала покажи мне покемона со смелым характером.', [
                ['label' => 'Показать покемона', 'action' => 'billy_brave_catch'],
                ['label' => 'Позже', 'close' => true],
            ]);
        }

        $clovers = $this->inventory->countItem($userId, 18);
        $candies = $this->inventory->countItem($userId, 9);
        if ($clovers < 10 || $candies < 1) {
            return $this->dialog('Коллекционер Билли', 'Предметов пока не хватает. Нужно 10 сушеных клеверов и 1 конфета.', [
                ['label' => 'Вернусь позже', 'close' => true],
            ]);
        }

        $this->db->beginTransaction();
        try {
            if (!$this->inventory->removeItem($userId, 18, 10) || !$this->inventory->removeItem($userId, 9, 1)) {
                $this->db->rollBack();
                return $this->error('Not enough quest items.');
            }
            $this->grantRewardItems($userId, [1 => 15000, 3 => 15, 9 => 3, 10 => 20], 'Квест: Перо для Билли');
            $this->quests->updateState($userId, 162, 20, 1);
            $this->addQuestRank($userId, 4);
            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('[npc_dialog] billy feather quest failed: ' . $e->getMessage());
            return $this->error('Quest reward failed.');
        }

        return $this->dialog('Коллекционер Билли', 'Отлично. Перо выдержит любую дорогу, а коллекция запомнит твое имя.', [
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

        if ($locationId === 11 && $questNpcId === 1) {
            return $this->artistAmira($userId, $step);
        }

        if ($locationId === 11 && $questNpcId === 2) {
            return $this->airen($userId, $step);
        }

        if ($locationId === 7 && $questNpcId === 1) {
            return $this->oldWoman($userId, $step);
        }

        if ($locationId === 13 && $questNpcId === 1) {
            return $this->articuno($userId, $step);
        }

        if ($locationId === 18 && $questNpcId === 1) {
            return $this->hippodromeGaren($userId, $step);
        }

        if ($locationId === 43 && in_array($questNpcId, [1, 2, 3, 4], true)) {
            return $this->eventHallNpc($questNpcId);
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
            return $this->flowerStall($userId);
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

    private function artistAmira(int $userId, int $step): array
    {
        $lakeQuest = $this->quests->findForUser($userId, 161);
        $lakeDone = $lakeQuest !== null && (int) ($lakeQuest['gotov'] ?? 0) === 1;
        $lakeButtons = [];
        if (!$lakeDone) {
            $lakeProcess = (int) ($lakeQuest['process'] ?? 0);
            if ($lakeProcess >= 5) {
                $lakeButtons[] = ['label' => 'Сдать энергетики', 'action' => 'amira_lake_turnin'];
            } elseif ($lakeProcess >= 4) {
                $lakeButtons[] = ['label' => 'Рассказать о победах', 'action' => 'amira_storm_wins'];
            } else {
                $lakeButtons[] = ['label' => 'Спросить о шепоте озера', 'action' => 'amira_lake_start'];
            }
        }

        $quest = $this->quests->findForUser($userId, 2);
        if ($quest !== null && (int) ($quest['gotov'] ?? 0) === 1) {
            return $this->dialog('Художница Амира', 'Озеро снова спокойно. Я все еще пишу его по ночам, но теперь в отражении воды нет тревоги.', array_merge(
                $lakeButtons,
                [['label' => 'Уйти', 'close' => true]]
            ));
        }

        if ($quest !== null && (int) ($quest['process'] ?? 0) >= 5 && $step <= 1) {
            return $this->dialog('Художница Амира', 'Я уже рассказала тебе главное: Айрен появляется у озера ночью и долго смотрит в свое отражение. Если хочешь понять эту историю, поговори с ним осторожно.', [
                ['label' => 'Найти Айрена', 'params' => ['quest_npc' => '2', 'do' => '1']],
                ['label' => 'Уйти', 'close' => true],
            ]);
        }

        if ($step === 2) {
            return $this->dialog(
                'Художница Амира',
                'Айрен? Да, я видела его не раз. Каждую ночь он приходит к воде и смотрит в отражение так, будто ищет там не лицо, а потерянную душу. Однажды его глаза вспыхнули холодным голубым светом, и я услышала странное шипение вместо человеческого голоса.',
                [
                    ['label' => 'Он приходит каждую ночь?', 'params' => ['quest_npc' => '1', 'do' => '3']],
                    ['label' => 'Спасибо, я пойду', 'close' => true],
                ]
            );
        }

        if ($step === 3) {
            return $this->dialog(
                'Художница Амира',
                'В полнолуние лунная дорожка ложится прямо на середину озера. В такие ночи Айрен особенно заметен: вокруг него мерцает аура, похожая на крылья Артикуно. Будь осторожен. Это не просто слух, а чья-то беда.',
                [
                    ['label' => 'Записать рассказ', 'action' => 'quest_update:2:5:0'],
                    ['label' => 'К Айрену', 'params' => ['quest_npc' => '2', 'do' => '1']],
                    ['label' => 'Уйти', 'close' => true],
                ]
            );
        }

        if ($step === 4) {
            return $this->dialog('Художница Амира', 'Красиво, правда? Это озеро кажется небольшим, но ночью оно отражает небо целиком. Иногда именно маленькие места прячут самые большие истории.', [
                ['label' => 'Спросить про Айрена', 'params' => ['quest_npc' => '1', 'do' => '2']],
                ['label' => 'Уйти', 'close' => true],
            ]);
        }

        return $this->dialog('Художница Амира', 'Тише... У озера звук разносится далеко. Я пишу воду и стараюсь не спугнуть то, что приходит сюда по ночам.', array_merge([
            ['label' => 'Спросить про Айрена', 'params' => ['quest_npc' => '1', 'do' => '2']],
            ['label' => 'Полюбоваться озером', 'params' => ['quest_npc' => '1', 'do' => '4']],
        ], $lakeButtons, [
            ['label' => 'Уйти', 'close' => true],
        ]));
    }

    private function airen(int $userId, int $step): array
    {
        $quest = $this->quests->findForUser($userId, 2);
        $process = (int) ($quest['process'] ?? 0);
        if ($quest !== null && (int) ($quest['gotov'] ?? 0) === 1) {
            return $this->dialog('Айрен', 'Холодная рябь на воде постепенно исчезает. История Айрена уже завершена, а озеро хранит ее как тихую легенду.', [
                ['label' => 'Уйти', 'close' => true],
            ]);
        }

        if ($process >= 7 && $step <= 1) {
            return $this->dialog('Айрен', 'Ты нашел след Артикуно на скалах. Этого достаточно, чтобы озеро снова открыло память. Спасибо, тренер: теперь я знаю, где искать тело легенды и как вернуть наши души на места.', [
                ['label' => 'Завершить историю', 'action' => 'quest_airen_complete'],
                ['label' => 'Уйти', 'close' => true],
            ]);
        }

        if ($step === 2) {
            return $this->dialog('Айрен', 'Что тебе нужно, человек? Говори тише. Это место и так помнит слишком много чужих голосов.', [
                ['label' => 'Я хочу помочь', 'params' => ['quest_npc' => '2', 'do' => '3']],
                ['label' => 'Отступить', 'close' => true],
            ]);
        }

        if ($step === 3) {
            return $this->dialog(
                'Айрен',
                'Помочь? Мое тело далеко, а в нем чужая душа. Я вижу его только в отражениях и слышу, как моя сила делает то, чего я не выбирал.',
                [
                    ['label' => 'Как это произошло?', 'params' => ['quest_npc' => '2', 'do' => '4']],
                    ['label' => 'Уйти', 'close' => true],
                ]
            );
        }

        if ($step === 4) {
            return $this->dialog(
                'Айрен',
                'Здесь столкнулись две ауры. Человек и легенда коснулись одной и той же силы, и озеро стало зеркалом между ними. Если встретишь Артикуно в моем теле, не подходи бездумно: он напуган и может ударить первым.',
                [
                    ['label' => 'Как вернуть все назад?', 'params' => ['quest_npc' => '2', 'do' => '5']],
                    ['label' => 'Уйти', 'close' => true],
                ]
            );
        }

        if ($step === 5) {
            return $this->dialog(
                'Айрен',
                'Приведи его сюда, к воде, в полночь. Тогда отражение снова станет дверью, и я попробую вернуть души на свои места. Это опасно, но другого пути я не знаю.',
                [
                    ['label' => 'Я запомню', 'action' => 'quest_update:2:6:0'],
                    ['label' => 'Спросить Амиру', 'params' => ['quest_npc' => '1', 'do' => '1']],
                    ['label' => 'Уйти', 'close' => true],
                ]
            );
        }

        return $this->dialog(
            'Айрен',
            'На противоположном берегу стоит невысокий парень. Лунный свет цепляется за его силуэт, а вокруг плеч едва заметно мерцает голубая аура.',
            [
                ['label' => 'Подойти осторожно', 'params' => ['quest_npc' => '2', 'do' => '2']],
                ['label' => 'Не тревожить', 'close' => true],
            ]
        );
    }

    private function oldWoman(int $userId, int $step): array
    {
        $quest = $this->quests->findForUser($userId, 2);
        $process = (int) ($quest['process'] ?? 0);

        if ($quest !== null && (int) ($quest['gotov'] ?? 0) === 1) {
            return $this->dialog('Старая женщина', 'История Айрена уже вышла из тени. Не ходи в этот лес просто ради любопытства.', [
                ['label' => 'Уйти', 'close' => true],
            ]);
        }

        if ($process >= 4) {
            return $this->dialog('Старая женщина', 'Я сказала всё, что знала: искать нужно не только Айрена, но и самого Артикуно. Дальше дорога твоя.', [
                ['label' => 'К озеру', 'params' => ['quest_npc' => '1', 'do' => '1']],
                ['label' => 'Уйти', 'close' => true],
            ]);
        }

        if ($process >= 3) {
            $ids = $this->findActivePokemonBatch($userId, 48, 25, 3, 9);

            return $this->dialog(
                'Старая женщина',
                'Вернулся? Для рассказа об Айрене мне нужны три Venonat 25+ уровня с нахальным характером. Я вижу подходящих: ' . count($ids) . '/3.',
                [
                    ['label' => 'Сдать Venonat', 'action' => 'quest_old_woman_turnin', 'disabled' => count($ids) < 3],
                    ['label' => 'Уйти', 'close' => true],
                ]
            );
        }

        if ($step === 2) {
            return $this->dialog('Старая женщина', 'Еще один наивный тренер... Никто из вас не понимает, что творится в этом лесу. Только бы Айрен не увидел тебя первым.', [
                ['label' => 'Кто такой Айрен?', 'params' => ['quest_npc' => '1', 'do' => '3']],
                ['label' => 'Уйти', 'close' => true],
            ]);
        }

        if ($step === 3) {
            return $this->dialog('Старая женщина', 'Говорят, он похож на человека с крыльями, хвостом и голубым сиянием. Но это не сказка для детей, а след легенды, которая поменялась с человеком местами.', [
                ['label' => 'Что мне сделать?', 'params' => ['quest_npc' => '1', 'do' => '4']],
                ['label' => 'Уйти', 'close' => true],
            ]);
        }

        if ($step === 4) {
            return $this->dialog('Старая женщина', 'Принеси три Venonat 25+ уровня с нахальным характером. Тогда расскажу больше. Пустые слова в этом лесу ничего не стоят.', [
                ['label' => 'Записать поручение', 'action' => 'quest_update:2:3:0'],
                ['label' => 'Уйти', 'close' => true],
            ]);
        }

        // Квест 160 идёт поверх старой линии Айрена: знахарка остаётся тем же NPC,
        // но у неё появляется шаг ледяного сна. Кнопка зависит от того, на каком
        // шаге игрок: не начал / ищет покемона / несёт предметы.
        $iceQuest = $this->quests->findForUser($userId, 160);
        $iceDone = $iceQuest !== null && (int) ($iceQuest['gotov'] ?? 0) === 1;

        $buttons = [
            ['label' => 'Спросить про слухи', 'params' => ['quest_npc' => '1', 'do' => '2']],
        ];
        if (!$iceDone) {
            $iceProcess = (int) ($iceQuest['process'] ?? 0);
            if ($iceProcess >= 3) {
                $buttons[] = ['label' => 'Сдать предметы для обряда', 'action' => 'airen_ice_turnin'];
            } elseif ($iceProcess >= 2) {
                $buttons[] = ['label' => 'Показать ледяного покемона', 'action' => 'airen_ice_catch'];
            } else {
                $buttons[] = ['label' => 'Спросить о сне Айрена', 'action' => 'airen_ice_dream_start'];
            }
        }
        $buttons[] = ['label' => 'Уйти', 'close' => true];

        return $this->dialog('Старая женщина', 'Что ты тут делаешь? Сюда опасно заходить. Тёмный лес не любит шумных гостей.', $buttons);
    }

    private function articuno(int $userId, int $step): array
    {
        $quest = $this->quests->findForUser($userId, 2);
        $process = (int) ($quest['process'] ?? 0);

        if ($quest !== null && (int) ($quest['gotov'] ?? 0) === 1) {
            return $this->dialog('#144 Articuno', 'Высокие скалы пусты. Легенда уже покинула это место, оставив после себя только холодный ветер.', [
                ['label' => 'Уйти', 'close' => true],
            ]);
        }

        if ($step === 2) {
            $nextProcess = max($process, 7);

            return $this->dialog(
                '#144 Articuno',
                'Ты отметил место встречи в журнале. Легенда пока не подпускает тебя ближе: нужен особый способ привести Артикуно к озеру, не нарушив связь с Айреном.',
                [
                    ['label' => 'Записать в журнал', 'action' => 'quest_update:2:' . $nextProcess . ':0'],
                    ['label' => 'Уйти', 'close' => true],
                ]
            );
        }

        if ($process < 6) {
            return $this->dialog('#144 Articuno', 'С вершины скалы тянет ледяным ветром. Кажется, здесь бывает легендарный покемон, но пока ты не знаешь, зачем искать его.', [
                ['label' => 'Уйти', 'close' => true],
            ]);
        }

        return $this->dialog(
            '#144 Articuno',
            'Огромная голубая птица сидит на высокой скале и смотрит вдаль. Простым покеболом такую легенду не удержать; если история Айрена правдива, тебе понадобится особый способ привести Артикуно к озеру.',
            [
                ['label' => 'Не тревожить пока', 'params' => ['quest_npc' => '1', 'do' => '2']],
                ['label' => 'Уйти', 'close' => true],
            ]
        );
    }

    private function strangeSpike(int $userId, int $step): array
    {
        $quest = $this->quests->findForUser($userId, 2);
        if ($quest !== null) {
            $process = (int) ($quest['process'] ?? 0);
            if ((int) ($quest['gotov'] ?? 0) === 1) {
                return $this->dialog('Странный Спайк', 'История Айрена теперь звучит иначе. Я рад, что ты не оставил этот след без внимания.', [
                    ['label' => 'Крафт камней', 'params' => ['quest_npc' => '3', 'do' => '3']],
                    ['label' => 'Уйти', 'close' => true],
                ]);
            }

            $hint = 'Я уже рассказал тебе всё, что видел на рассвете.';
            $choices = [['label' => 'Крафт камней', 'params' => ['quest_npc' => '3', 'do' => '3']]];
            if ($process < 3) {
                $hint = 'Если хочешь проверить слухи, начни с Тёмного леса. Там есть старая женщина, которая знает такие истории лучше всех.';
                $choices[] = ['label' => 'Идти в Тёмный лес', 'close' => true];
            } elseif ($process < 4) {
                $hint = 'Старуха не станет говорить просто так. Ей нужны три Venonat 25+ уровня с нахальным характером.';
            } elseif ($process < 5) {
                $hint = 'Теперь дорога ведёт к озеру. Художница Амира видела Айрена в полнолуние.';
            } elseif ($process < 6) {
                $hint = 'Поговори с Айреном у озера. Только без резких движений, ладно?';
            } elseif ($process < 7) {
                $hint = 'Айрен сказал искать Articuno. Скалы за Дорогой 3 — лучшее место для такого следа.';
            } else {
                $hint = 'Ты нашёл след легенды. Вернись к Айрену у озера и закрой эту историю.';
            }
            $choices[] = ['label' => 'Уйти', 'close' => true];

            return $this->dialog('Странный Спайк', $hint, $choices);
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

    private function flowerStall(int $userId): array
    {
        $quest = $this->quests->findForUser($userId, 8);
        if ($quest === null) {
            return $this->dialog('Миссис Джафит', 'Мой цветочный прилавок разгромлен, а покемонов-помощников похитили. Если поможешь вернуть помощников, магазин снова сможет радовать посетителей.', [
                ['label' => 'Помочь с прилавком', 'action' => 'quest_start:8:3'],
                ['label' => 'Открыть Покемаркет', 'route' => '/game/market/items'],
                ['label' => 'Уйти', 'close' => true],
            ]);
        }

        if ((int) ($quest['gotov'] ?? 0) === 1) {
            return $this->dialog('Миссис Джафит', 'Прилавок снова ожил. Спасибо за всех помощников: без тебя я бы не решилась открыть его заново.', [
                ['label' => 'Открыть Покемаркет', 'route' => '/game/market/items'],
                ['label' => 'Уйти', 'close' => true],
            ]);
        }

        $process = (int) ($quest['process'] ?? 0);
        if ($process < 6) {
            $ready = $this->findActivePokemonForQuest($userId, 43, 1, 16) > 0;
            return $this->dialog('Миссис Джафит', 'Для начала нужен Oddish с осторожным характером. Он поможет перебрать уцелевшие растения.', [
                ['label' => 'Сдать Oddish', 'action' => 'quest_jafit_turnin:1', 'disabled' => !$ready, 'hint' => $ready ? null : 'В активной команде нет подходящего Oddish.'],
                ['label' => 'Уйти', 'close' => true],
            ]);
        }

        if ($process < 12) {
            $ready = $this->findActivePokemonForQuest($userId, 12, 50, 0, 2) > 0;
            return $this->dialog('Миссис Джафит', 'Теперь нужна Butterfree 50 уровня женского пола. Ее крылья помогут аккуратно рассеять пыльцу.', [
                ['label' => 'Сдать Butterfree', 'action' => 'quest_jafit_turnin:2', 'disabled' => !$ready, 'hint' => $ready ? null : 'В активной команде нет подходящей Butterfree.'],
                ['label' => 'Уйти', 'close' => true],
            ]);
        }

        if ($process < 18) {
            $ready = $this->findActivePokemonForQuest($userId, 61, 1) > 0;
            return $this->dialog('Миссис Джафит', 'Полки уже почти разобраны. Принеси Poliwhirl: без воды цветы не восстановятся.', [
                ['label' => 'Сдать Poliwhirl', 'action' => 'quest_jafit_turnin:3', 'disabled' => !$ready, 'hint' => $ready ? null : 'В активной команде нет Poliwhirl.'],
                ['label' => 'Уйти', 'close' => true],
            ]);
        }

        $ready = $this->findActivePokemonForQuest($userId, 70, 45, 5) > 0;
        return $this->dialog('Миссис Джафит', 'Последний помощник - Weepinbell 45 уровня со спокойным характером. Тогда прилавок можно будет открыть снова.', [
            ['label' => 'Сдать Weepinbell', 'action' => 'quest_jafit_turnin:4', 'disabled' => !$ready, 'hint' => $ready ? null : 'В активной команде нет подходящего Weepinbell.'],
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

        return $this->dialog($title !== '' ? $title : 'Куратор', 'Я слежу за правилами этой зоны. Турниры и официальные бои теперь открываются через новый турнирный модуль.', [
            ['label' => 'Открыть турниры', 'route' => '/game/tournaments'],
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
        return $this->dialog($title !== '' ? $title : 'Стадион', 'Стадион готовит официальные матчи. Турнирные сценарии запускаются через раздел турниров, а обычные вызовы идут из меню игрока.', [
            ['label' => 'Понятно', 'close' => true],
        ]);
    }

    private function flightStatusDialog(): array
    {
        return $this->dialog('Проводник', 'Рейс выполняется. Подробности о времени прибытия и доступных действиях показаны в транспортном окне.', [
            ['label' => 'Открыть транспорт', 'route' => '/game/transport'],
            ['label' => 'Уйти', 'close' => true],
        ]);
    }

    private function flightExitDialog(): array
    {
        return $this->dialog('Выход', 'Покинуть самолёт можно после прибытия. Если рейс уже завершён, вернись через транспортное окно.', [
            ['label' => 'Открыть транспорт', 'route' => '/game/transport'],
            ['label' => 'Закрыть', 'close' => true],
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
        if (str_contains($lower, 'выход')) {
            return $this->dialog($name, 'Выход из этой зоны проходит через новую карту и транспорт. Если рейс уже завершён, вернуться можно через транспортное окно.', [
                ['label' => 'Открыть транспорт', 'route' => '/game/transport'],
                ['label' => 'Закрыть', 'close' => true],
            ]);
        }

        $text = 'Вы осмотрелись: здесь пока нет отдельной сцены, но точка уже отмечена на новой карте и не запускает старые окна.';

        return $this->dialog($name, $text, [['label' => 'Закрыть', 'close' => true]]);
    }

    private function genericDialog(string $title, int $locationId): array
    {
        return $this->dialog($title !== '' ? $title : 'NPC', 'Персонаж уже подключен к новой карте. Сейчас у него нет отдельного задания, но он может получить сцену или сервис в следующем обновлении. Локация #' . $locationId . '.', [
            ['label' => 'Закрыть', 'close' => true],
        ]);
    }

    private function genericQuestNpc(string $title, int $locationId): array
    {
        return $this->dialog($title !== '' ? $title : 'Сюжетный NPC', 'Этот персонаж уже стоит на новой карте. Пока его история ждёт продолжения, можно отметить встречу и вернуться позже. Локация #' . $locationId . '.', [
            ['label' => 'Отметить как просмотренное', 'action' => 'npc_ack'],
            ['label' => 'Уйти', 'close' => true],
        ]);
    }

    private function questStartAction(int $userId, int $locationId, array $params, string $action): array
    {
        // The wire strings are retained for existing dialogs, but are opaque commands.
        // Never interpret a client-supplied quest number or initial process.
        $rule = match ($action) {
            'quest_start:1:2' => [3, 1, 1, 2],
            'quest_start:2:2' => [1, 3, 2, 2],
            'quest_start:3:2' => [5, 4, 3, 2],
            'quest_start:4:10' => [6, 5, 4, 10],
            'quest_start:8:3' => [10, 8, 8, 3],
            default => null,
        };
        if ($rule === null || $locationId !== $rule[0] || (int) ($params['quest_npc'] ?? 0) !== $rule[1]) {
            return $this->error('Это действие недоступно у этого NPC.');
        }
        [, , $questId, $process] = $rule;
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

    private function questUpdateAction(int $userId, int $locationId, array $params, string $action): array
    {
        // Only five narrative acknowledgements exist. Completion and turn-in steps
        // must go through their server-side requirement/reward handlers.
        $rule = match ($action) {
            'quest_update:1:3:0' => [1, 2, 1, 2, 3],
            'quest_update:2:3:0' => [7, 1, 2, 2, 3],
            'quest_update:2:5:0' => [11, 1, 2, 4, 5],
            'quest_update:2:6:0' => [11, 2, 2, 5, 6],
            'quest_update:2:7:0' => [13, 1, 2, 6, 7],
            default => null,
        };
        if ($rule === null || $locationId !== $rule[0] || (int) ($params['quest_npc'] ?? 0) !== $rule[1]) {
            return $this->error('Это действие недоступно у этого NPC.');
        }
        [, , $questId, $expected, $next] = $rule;
        if (!$this->quests->advanceNarrativeStep($userId, $questId, $expected, $next)) {
            return $this->error('Сначала выполни предыдущий этап задания.');
        }

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
        $this->grantRewardItems($userId, [1 => 50000, 3 => 20], 'Квест: Цирковая команда');
        $this->quests->updateState($userId, 3, 10, 1);
        $this->addQuestRank($userId, 2);

        return $this->dialog('Циркач Стив', 'Огромное спасибо! Награда: 50 000 монет и 20 покеболов.', [
            ['label' => 'Уйти', 'close' => true],
        ]);
    }

    private function turnInJafitQuest(int $userId, int $stage): array
    {
        $requirements = [
            1 => ['base' => 43, 'level' => 1, 'nature' => 16, 'sex' => 0, 'next' => 6, 'completed' => 0, 'reward' => [18 => 1], 'rank' => 0, 'missing' => 'Нужен Oddish с осторожным характером.'],
            2 => ['base' => 12, 'level' => 50, 'nature' => 0, 'sex' => 2, 'next' => 12, 'completed' => 0, 'reward' => [9 => 5], 'rank' => 0, 'missing' => 'Нужна Butterfree 50 уровня женского пола.'],
            3 => ['base' => 61, 'level' => 1, 'nature' => 0, 'sex' => 0, 'next' => 18, 'completed' => 0, 'reward' => [15 => 20], 'rank' => 0, 'missing' => 'Нужен Poliwhirl.'],
            4 => ['base' => 70, 'level' => 45, 'nature' => 5, 'sex' => 0, 'next' => 20, 'completed' => 1, 'reward' => [56 => 1], 'rank' => 4, 'missing' => 'Нужен Weepinbell 45 уровня со спокойным характером.'],
        ];
        if (!isset($requirements[$stage])) {
            return $this->error('Неизвестный этап квеста.');
        }

        $quest = $this->quests->findForUser($userId, 8);
        if ($quest === null) {
            return $this->dialog('Миссис Джафит', 'Сначала нужно принять просьбу о восстановлении прилавка.', [
                ['label' => 'Принять задание', 'action' => 'quest_start:8:3'],
                ['label' => 'Уйти', 'close' => true],
            ]);
        }
        if ((int) ($quest['gotov'] ?? 0) === 1) {
            return $this->dialog('Миссис Джафит', 'Прилавок уже восстановлен. Спасибо за помощь.', [
                ['label' => 'Уйти', 'close' => true],
            ]);
        }

        $requiredProcess = [1 => 3, 2 => 6, 3 => 12, 4 => 18][$stage];
        if ((int) ($quest['process'] ?? 0) < $requiredProcess) {
            return $this->dialog('Миссис Джафит', 'Этот этап еще не открыт.', [
                ['label' => 'Уйти', 'close' => true],
            ]);
        }

        $req = $requirements[$stage];
        $pokemonId = $this->findActivePokemonForQuest($userId, $req['base'], $req['level'], $req['nature'], $req['sex']);
        if ($pokemonId <= 0) {
            return $this->dialog('Миссис Джафит', $req['missing'], [
                ['label' => 'Вернусь позже', 'close' => true],
            ]);
        }

        if ($this->activePokemonCount($userId) <= 1) {
            return $this->dialog('Миссис Джафит', 'Я не могу забрать последнего покемона в твоей команде. Оставь с собой хотя бы одного помощника.', [
                ['label' => 'Вернусь позже', 'close' => true],
            ]);
        }

        $this->db->beginTransaction();
        try {
            $this->deleteQuestPokemon($userId, [$pokemonId]);
            $this->grantRewardItems($userId, $req['reward'], 'Квест: Прилавок Миссис Джафит');
            if ($req['rank'] > 0) {
                $this->addQuestRank($userId, $req['rank']);
            }
            $this->quests->updateState($userId, 8, $req['next'], $req['completed']);
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            error_log('[npc_dialog] jafit quest failed: ' . $e->getMessage());
            return $this->error('Не удалось сдать этап квеста.');
        }

        $messages = [
            1 => 'Oddish устроился среди уцелевших горшков. Награда: сушеный клевер.',
            2 => 'Butterfree осторожно очистила прилавок от пыльцы и пыли. Награда: 5 фиолетовых конфет.',
            3 => 'Poliwhirl наполнил резервуары водой. Награда: 20 энергетиков.',
            4 => 'Weepinbell спокойно занял место последнего помощника. Прилавок снова открыт. Награда: таинственное яйцо и +4 к рейтингу квестов.',
        ];

        return $this->dialog('Миссис Джафит', $messages[$stage], [
            ['label' => $stage < 4 ? 'Продолжить' : 'Спасибо', 'close' => true],
        ]);
    }

    private function turnInCarolQuest(int $userId): array
    {
        if (!$this->inventory->hasItem($userId, 4, 1)) {
            return $this->dialog('Кэрол', 'Игрушки нет. Пожалуйста, найди ее.', [
                ['label' => 'Пойду искать', 'close' => true],
            ]);
        }

        $this->db->beginTransaction();
        try {
        if (!$this->inventory->removeItem($userId, 4, 1)) {
            $this->db->rollBack();
            return $this->error('Not enough quest items.');
        }
        $this->grantRewardItems($userId, [1 => 10000, 3 => 5], 'Квест: Игрушка Кэрол');
        $this->quests->updateState($userId, 4, 11, 1);
        $this->addQuestRank($userId, 3);
        $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            error_log('[npc_dialog] carol quest failed: ' . $e->getMessage());
            return $this->error('Quest reward failed.');
        }

        return $this->dialog('Кэрол', 'Ура! Спасибо, что вернул игрушку. Награда: 10 000 монет и 5 покеболов.', [
            ['label' => 'Будь осторожна', 'close' => true],
        ]);
    }

    private function turnInOldWomanQuest(int $userId): array
    {
        $ids = $this->findActivePokemonBatch($userId, 48, 25, 3, 9);
        if (count($ids) < 3) {
            return $this->dialog('Старая женщина', 'Нет. Мне нужны именно три Venonat 25+ уровня с нахальным характером. Возвращайся, когда подготовишь их.', [
                ['label' => 'Уйти', 'close' => true],
            ]);
        }

        if ($this->activePokemonCount($userId) <= 3) {
            return $this->dialog('Старая женщина', 'Если я заберу этих Venonat, у тебя не останется боевой команды. Оставь с собой хотя бы ещё одного покемона.', [
                ['label' => 'Уйти', 'close' => true],
            ]);
        }

        $this->deleteQuestPokemon($userId, $ids);
        $this->quests->createOrUpdate($userId, 2, 4, 0);
        $this->addQuestRank($userId, 1);

        return $this->dialog('Старая женщина', 'Так-то лучше. Слушай внимательно: Айрен связан с Артикуно. Ищи не только странного парня, но и тело легенды. Эта история началась не в лесу, а у воды.', [
            ['label' => 'К озеру', 'params' => ['quest_npc' => '1', 'do' => '1']],
            ['label' => 'Уйти', 'close' => true],
        ]);
    }

    private function completeAirenQuest(int $userId): array
    {
        $quest = $this->quests->findForUser($userId, 2);
        if ($quest === null || (int) ($quest['process'] ?? 0) < 7) {
            return $this->dialog('Айрен', 'Сначала найди след Артикуно на скалах. Без этого озеро не откроет обратный путь.', [
                ['label' => 'Уйти', 'close' => true],
            ]);
        }

        if ((int) ($quest['gotov'] ?? 0) === 1) {
            return $this->dialog('Айрен', 'Эта история уже завершена. Спасибо, что помог.', [
                ['label' => 'Уйти', 'close' => true],
            ]);
        }

        $this->grantRewardItems($userId, [1 => 15000, 44 => 1], 'Квест: Рассказ Спайка');
        $this->quests->updateState($userId, 2, 20, 1);
        $this->addQuestRank($userId, 3);

        return $this->dialog('Айрен', 'Лунная дорожка на озере вспыхивает и гаснет. История Айрена записана в журнал как завершённая. Награда: 15 000 монет, Лунный камень и +3 к рейтингу квестов.', [
            ['label' => 'Спасибо', 'close' => true],
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
                if (!$this->inventory->removeItem($userId, (int) $itemId, (int) $count)) {
                    $this->db->rollBack();
                    return $this->error('Not enough craft resources.');
                }
            }
            $success = random_int(0, 10) > 5;
            if ($success) {
                $this->grantRewardItems($userId, [(int) $recipe['item'] => 1], 'Крафт предмета');
            }
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            error_log('[npc_dialog] craft failed: ' . $e->getMessage());
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

        $this->db->beginTransaction();
        try {
        if (!$this->inventory->removeItem($userId, 1, 10000)) {
            $this->db->rollBack();
            return $this->error('Not enough coins.');
        }
        $stmt = $this->db->prepare('SELECT rang_a, rang_b, rang_c FROM users WHERE id = :user LIMIT 1');
        $stmt->execute(['user' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['rang_a' => 0, 'rang_b' => 0, 'rang_c' => 0];
        $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            error_log('[npc_dialog] reputation check failed: ' . $e->getMessage());
            return $this->error('Reputation check failed.');
        }

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

    private function findActivePokemonForQuest(int $userId, int $baseId, int $level, int $natureId = 0, int $sex = 0): int
    {
        $sql = 'SELECT id FROM pok_user
              WHERE users = :user AND active = 1 AND basenum = :base AND lvl >= :level';
        if ($natureId > 0) {
            $sql .= ' AND har = :nature';
        }
        if ($sex > 0) {
            $sql .= ' AND sex = :sex';
        }
        $sql .= ' ORDER BY lvl ASC, id ASC LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':base', $baseId, PDO::PARAM_INT);
        $stmt->bindValue(':level', $level, PDO::PARAM_INT);
        if ($natureId > 0) {
            $stmt->bindValue(':nature', $natureId, PDO::PARAM_INT);
        }
        if ($sex > 0) {
            $stmt->bindValue(':sex', $sex, PDO::PARAM_INT);
        }
        $stmt->execute();

        return (int) ($stmt->fetchColumn() ?: 0);
    }

    private function findActivePokemonBatch(int $userId, int $baseId, int $level, int $limit, int $natureId = 0): array
    {
        $sql = 'SELECT id FROM pok_user
              WHERE users = :user AND active = 1 AND basenum = :base AND lvl >= :level';
        if ($natureId > 0) {
            $sql .= ' AND har = :nature';
        }
        $sql .= ' ORDER BY lvl ASC, id ASC LIMIT :limit';

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':base', $baseId, PDO::PARAM_INT);
        $stmt->bindValue(':level', $level, PDO::PARAM_INT);
        if ($natureId > 0) {
            $stmt->bindValue(':nature', $natureId, PDO::PARAM_INT);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN) ?: []);
    }

    private function activePokemonCount(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM pok_user WHERE users = :user AND active = 1');
        $stmt->execute(['user' => $userId]);

        return (int) $stmt->fetchColumn();
    }

    private function countArchivedPveWins(int $userId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*)
               FROM battle_history_archive
              WHERE user_id = :user
                AND mode = "pve"
                AND result = "win"'
        );
        $stmt->execute(['user' => $userId]);

        return (int) $stmt->fetchColumn();
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
        if ($locationId === 11 && $questNpcId === 2) {
            return 'Айрен';
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
