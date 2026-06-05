# Структура проекта PokemonChic

Этот файл — быстрый ориентир по проекту: какой файл/папка за что отвечает и куда добавлять новый контент. Актуальная цель проекта — работать через новый слой `/game` + `/api/*`; legacy PHP используется только как справочник поведения при переносе.

## 1) Точки входа

- `index.php`
  - Главная страница сайта (новая витрина/лендинг).
- `public/index.php`
  - Новый front controller приложения.
  - Здесь собираются зависимости и регистрируются новые маршруты (`/game`, `/api/...`).
- `router.php`
  - Роутер для встроенного PHP-сервера: если нет статического файла — отправляет в `index.php`.
- `game.php`
  - Legacy-вход старой игры (`?go=...`). Не использовать для новых функций. Если старое поведение нужно вернуть, переносить его в `src/` и API.

## 2) Новый слой приложения (`src/`)

### `src/Controller/` — HTTP-контроллеры

- `GameApiController.php`
  - API игрового мира: состояние, перемещения, переключение режима нападения.
- `PveBattleApiController.php`
  - Новый API PvE-боя: состояние боя, действия, подтверждение завершения.
- `NpcApiController.php`
  - API диалогов и действий NPC.
- `InventoryController.php`
  - Страница инвентаря пользователя.
- `InventoryApiController.php`
  - API постраничной загрузки инвентаря.
- `AuthController.php`
  - Логин/логаут, обновление сессии.
- `GameController.php`
  - Рендер главного игрового экрана (`views/game-start.php`).
- `GameModuleController.php`
  - Временные заглушки модулей `/game/*`.
- `HomeController.php`
  - Рендер главной страницы/стартового экрана.

### `src/Game/` — бизнес-логика игры

- `LocationStateService.php`
  - Формирует state мира для клиента (`локация`, `игроки`, `переходы`, `NPC`).
- `MapMoveService.php`
  - Логика перемещения между локациями.
- `WildEncounterService.php`
  - Логика авто-нападений диких покемонов (создание PvE-боя).
- `BattleEngineService.php`
  - Новое ядро PvE-боя (ходы, урон, выбор атак, завершение боя).
- `NpcDialogService.php`
  - Роутинг диалогов NPC, безопасные действия NPC и квестовые действия.
  - Не запускать отсюда legacy `include/rooms/npc/*.php`; старые файлы только читаем как референс.
- `LocationGraph.php`
  - Граф доступных переходов между локациями.
- `GameRoutes.php`
  - Карта legacy `go=` -> новые маршруты `/game/...`, список модулей.
- `LocationContentRepository.php`
  - Чтение контента локаций из конфигов (описание, NPC и т.п.).

### `src/Repository/` — доступ к данным (БД)

- `LocationRepository.php`
  - Чтение/обновление данных по локациям и состоянию игрока.
- `BattleRepository.php`
  - Операции PvE-боя (`battles`, `pok_user`, `pok_pve`, `battle_log`, `users`).
- `InventoryRepository.php`
  - Работа с инвентарем пользователя.
- `UserRepository.php`
  - Данные аккаунта/логина.
- `QuestRepository.php`
  - Данные квестов.
- `RankingRepository.php`, `BanRepository.php`
  - Рейтинги и баны.

### `src/Http/` — HTTP-базис

- `Router.php`
  - Простой роутер по `METHOD + PATH`.
- `Request.php`
  - Обертка запроса (`query`, `post`, `input`).
- `Response.php`
  - Обертка ответа (тело, статус, заголовки, redirect).

### `src/Security/` — безопасность

- `Session.php`, `Csrf.php`, `PasswordHasher.php`, `BanGuard.php`
  - Сессии, CSRF, хэши паролей, проверка блокировок.

### `src/Support/` + `src/Database/` + `src/View/`

- `Support/Autoload.php`, `Support/Env.php`
  - Автозагрузка и env-настройки.
- `Database/Connection.php`
  - Создание PDO-подключения.
- `View/View.php`
  - Рендер PHP-шаблонов из `views/`.

## 3) Шаблоны и фронт

- `views/game-start.php`
  - Основной экран игры: локация, чат, список игроков, actionbar, overlay инвентаря, overlay PvE-боя.
- `views/game-items.php`
  - Экран/интерфейс инвентаря.
- `views/game-module.php`
  - Временный шаблон модульных страниц.
- `public/css/game-start.css`
  - Стили игрового экрана и overlay-окон.

## 4) Конфиги и инициализация

- `config/app.php`
  - Общие настройки приложения.
- `config/database.php`
  - Настройки БД.
- `config/location_content.php`
  - Контент локаций/NPC для нового мира.
- `app/bootstrap.php`
  - Базовые функции проекта (legacy-слой и общие хелперы).

## 5) Legacy-слой (старый движок)

- `include/files/*.php`
  - Старые игровые экраны и модули (`fight_pve.world.php`, `gameload.world.php`, `buttons.world.php` и др.).
- `include/function/*.php`
  - Старые функции игры/боя/инвентаря.
- `include/rooms/*`
  - Скрипты локаций/комнат/NPC старой системы.

Важно: legacy сейчас частично используется как источник правил и данных, но основной курс — перенос в `src/` + `/api/*`. Новый NPC, квест, предмет или экран не должен зависеть от прямого запуска `game.php?go=...`.

## 6) Как Добавлять Квесты И Миссии

Квест в новой архитектуре состоит из трёх частей:

1. Данные квеста в БД:
   - `quest_definitions` — название, описание, тип, зависимости, повторяемость, `reward_json`.
   - `quest_steps` — шаги, цели, проценты, подсказки, привязка к локации/NPC.
   - `quest` — состояние конкретного игрока: `process`, `gotov`, cooldown/time.
   - `user_quest_tracking` — какой квест игрок отслеживает на карте.
2. Игровые действия:
   - NPC-старт/сдача — `src/Game/NpcDialogService.php`.
   - Автопрогресс от боя/предмета/ивента — соответствующий service/repository, но через `QuestRepository`.
   - Награды — через `RewardRepository::grantPipeline()` или `grantItems()`, не прямым `INSERT`.
3. UI:
   - Журнал — `/game/quests`, `views/components/quest-journal-panel.php`, `public/js/quest-journal.js`.
   - Мини-трекер и кнопки “Показать на карте/Перейти к NPC” — игровой overlay на `/game`.

Минимальный порядок добавления:

1. Завести запись в `quest_definitions`.
2. Завести шаги в `quest_steps`.
3. Если квест выдаёт NPC — добавить NPC в `config/location_content.php` и обработку в `NpcDialogService`.
4. Проверить `/api/quests` и `/api/location/npc`.
5. Прогнать релевантный smoke: `tools/quests_minimum_smoke.php`, `tools/location_npc_transport_smoke.php`.

Черновики квестов можно собирать в админке: `/game/admin` -> `Мастер контента` -> `Квест`. Если там ещё нет кнопки сохранения нужного сценария, мастер всё равно показывает правильную структуру JSON/таблиц.

## 7) Как Добавлять NPC

Новый NPC добавляется так:

1. `config/location_content.php`:
   - `title` — имя в локации.
   - `type` — `npc` или `quest`.
   - `params` — например `npc=2` для Покемаркета или `quest_npc=1&do=1` для сюжетного NPC.
2. `src/Game/NpcDialogService.php`:
   - стандартные NPC уже покрыты общими методами: Покецентр, Покемаркет, транспорт, куратор, стадион.
   - сюжетные NPC должны иметь отдельный безопасный метод с `choices`, `action`, `params`, `route` или `close`.
3. Нельзя:
   - подключать `include/rooms/npc/*.php` напрямую;
   - делать `die("<script>...")`;
   - выдавать предметы/покемонов без reward pipeline;
   - запускать старые бои из NPC.

Текущая карта сюжетных NPC нового слоя:

- `#1 Алабастия`: Случайный прохожий, Странный Спайк.
- `#3 Лаборатория Оука`: Профессор Оук, Исследователи.
- `#4 Дорога 1`: Коллекционер Билли.
- `#5 Лес Вертании`: Циркач Стив.
- `#6`: Кэрол.
- `#7 Тёмный лес`: Старая женщина.
- `#10 Дорога 2`: Цветочный прилавок.
- `#11 Небольшое озеро`: Художница Амира, Айрен.
- `#13 Скалы`: Articuno.
- `#18 Пьютер`: Гарен, куратор ипподрома.
- `#43 Праздничный зал`: праздничные NPC.

Если NPC отсутствует в этом списке, но есть в локации, сначала проверить `config/location_content.php`, затем добавить обработчик в `NpcDialogService`.

## 8) Как Добавлять Предметы И Картинки

Предмет состоит из:

1. БД:
   - `items` — базовое имя/описание/иконка/цена.
   - `item_gameplay_metadata` — категория, target-use, equip-use, battle-use, compatibility, `effect_status`.
   - `items_users` — наличие у игрока.
2. Ассеты:
   - Runtime-иконки: `public/img/items/...`.
   - Индекс: `public/img/items/index.json`.
   - Сырьё/скачанные файлы не подключать напрямую к UI.
3. Логика:
   - Инвентарь: `InventoryRepository`, `InventoryApiController`.
   - Held items и ограничения: `item_gameplay_metadata`.
   - Награды/подарки: `RewardRepository`.

Правило: если предмет можно надевать/продавать/использовать, это должно быть описано в metadata, а не спрятано в одном случайном PHP-файле.

## 9) Кандидаты На Разделение Больших Файлов

Сначала делить то, где меньше риск сломать бой:

1. `src/Game/NpcDialogService.php`
   - вынести сюжетные NPC в отдельный story-модуль/trait;
   - оставить в основном сервисе роутинг, стандартных NPC и action dispatcher.
2. `public/js/game-start-runtime.js`
   - разнести на modules: game state, overlays, battle UI, player menu, notifications.
3. `public/js/admin-panel.js`
   - разнести по вкладкам: users/items/quests/commission/tournaments/gm-center.
4. `src\Repository\AdminRepository.php`
   - продолжать выносить traits: users/moderation, pokemon grant, legacy map.
5. `views/game-pokemon.php`
   - вынести карточку покемона, held item controls, training/vitamins, team/daycare sections в компоненты.

Боёвку (`BattleEngineService`, `BattleRepository`) делить только после smoke-фиксации, потому что это самый опасный слой для регрессий.

## 10) Где смотреть PvE сейчас

- Запуск диких: `src/Game/WildEncounterService.php`
- Боевое ядро: `src/Game/BattleEngineService.php`
- API боя: `src/Controller/PveBattleApiController.php`
- Хранилище боя: `src/Repository/BattleRepository.php`
- UI боя: `views/game-start.php` + `public/css/game-start.css`

## 11) Где смотреть инвентарь сейчас

- Страница: `src/Controller/InventoryController.php`
- API: `src/Controller/InventoryApiController.php`
- Репозиторий: `src/Repository/InventoryRepository.php`
- UI: `views/game-start.php` (overlay) и `views/game-items.php`

---

Если нужно, можно сделать вторую версию этого файла в виде:
- диаграммы потока (`request -> controller -> service -> repository -> view`),
- и таблицы маршрутов (`URL -> контроллер -> шаблон/API`).
