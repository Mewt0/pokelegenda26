# Структура проекта PokemonChic

Этот файл — быстрый ориентир по проекту: какой файл/папка за что отвечает.

## 1) Точки входа

- `index.php`
  - Главная страница сайта (новая витрина/лендинг).
- `public/index.php`
  - Новый front controller приложения.
  - Здесь собираются зависимости и регистрируются новые маршруты (`/game`, `/api/...`).
- `router.php`
  - Роутер для встроенного PHP-сервера: если нет статического файла — отправляет в `index.php`.
- `game.php`
  - Legacy-вход старой игры (`?go=...`), частично отключен и используется как переходный слой.

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
  - Диалоговые сценарии NPC и квестовые действия.
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

Важно: legacy сейчас частично используется как источник правил и данных, но основной курс — перенос в `src/` + `/api/*`.

## 6) Где смотреть PvE сейчас

- Запуск диких: `src/Game/WildEncounterService.php`
- Боевое ядро: `src/Game/BattleEngineService.php`
- API боя: `src/Controller/PveBattleApiController.php`
- Хранилище боя: `src/Repository/BattleRepository.php`
- UI боя: `views/game-start.php` + `public/css/game-start.css`

## 7) Где смотреть инвентарь сейчас

- Страница: `src/Controller/InventoryController.php`
- API: `src/Controller/InventoryApiController.php`
- Репозиторий: `src/Repository/InventoryRepository.php`
- UI: `views/game-start.php` (overlay) и `views/game-items.php`

---

Если нужно, можно сделать вторую версию этого файла в виде:
- диаграммы потока (`request -> controller -> service -> repository -> view`),
- и таблицы маршрутов (`URL -> контроллер -> шаблон/API`).
