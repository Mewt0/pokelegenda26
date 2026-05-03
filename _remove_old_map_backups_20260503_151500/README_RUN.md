# Pokemon 8.0 Rewrite Status

Этот файл фиксирует направление переписывания проекта. Цель: не чинить старую игру слоями костылей, а постепенно вынести рабочую бизнес-логику в нормальную архитектуру 2026 года.

## Решение

Старые фреймы, скрытые iframe (`_chat_two`, `_location_work`) и PHP-страницы, которые возвращают `<script>parent...`, больше не считаются целевой архитектурой.

Они могут временно оставаться только как совместимый слой, пока переносится логика. Новая игра должна работать так:

- один игровой экран без frameset;
- PHP 8.3+;
- front controller `public/index.php`;
- роутинг через контроллеры;
- JSON API для действий карты, чата, NPC, боев и инвентаря;
- PDO и prepared statements вместо `mysql_*`;
- UTF-8 в коде, шаблонах и API;
- CSRF для POST/command-запросов;
- отдельные сервисы домена: карта, локации, квесты, NPC, чат, бой, инвентарь;
- frontend обновляет только нужные области экрана через `fetch`, без перезагрузки всей страницы.

## Статусы

- `DONE_NEW` - написано с нуля под новую архитектуру.
- `PARTIAL_NEW` - новый каркас есть, но бизнес-логика перенесена не полностью.
- `LEGACY_COMPAT` - временный совместимый слой старой игры.
- `TODO_REWRITE` - нужно переписать с нуля.
- `DO_NOT_PORT` - не переносить.

## Новое ядро

| Файл | Статус | Комментарий |
|---|---:|---|
| `public/index.php` | `DONE_NEW` | Новый front controller. |
| `public/.htaccess` | `DONE_NEW` | Rewrite всех новых запросов в `public/index.php`. |
| `composer.json` | `DONE_NEW` | Описание PHP 8.3+ проекта и PSR-4. |
| `.editorconfig` | `DONE_NEW` | Единый стиль файлов: UTF-8, LF. |
| `config/app.php` | `DONE_NEW` | Конфиг приложения из `.env`. |
| `config/database.php` | `DONE_NEW` | Конфиг базы из `.env`. |
| `src/Support/Env.php` | `DONE_NEW` | Загрузка `.env`. |
| `src/Support/Autoload.php` | `DONE_NEW` | Временный PSR-4 autoload. |
| `src/Http/Request.php` | `DONE_NEW` | Объект запроса. |
| `src/Http/Response.php` | `DONE_NEW` | Объект ответа. |
| `src/Http/Router.php` | `DONE_NEW` | Мини-роутер нового ядра. |
| `src/Database/Connection.php` | `DONE_NEW` | PDO-подключение к базе. |
| `src/Security/Session.php` | `DONE_NEW` | Работа с сессиями. |
| `src/Security/Csrf.php` | `DONE_NEW` | CSRF-токены. |
| `src/Security/BanGuard.php` | `DONE_NEW` | Проверка IP-банов. |
| `src/Security/PasswordHasher.php` | `DONE_NEW` | Поддержка старого хэша и нового `password_hash`. |
| `src/Repository/BanRepository.php` | `DONE_NEW` | Репозиторий банов. |
| `src/Repository/UserRepository.php` | `PARTIAL_NEW` | Логин и online-данные есть, нужно расширить. |
| `src/Repository/RankingRepository.php` | `PARTIAL_NEW` | Рейтинги частично перенесены. |
| `src/Controller/HomeController.php` | `DONE_NEW` | Главная страница. |
| `src/Controller/AuthController.php` | `PARTIAL_NEW` | Логин есть, регистрация и throttling еще нужны. |
| `src/Controller/GameController.php` | `PARTIAL_NEW` | Заглушка игры есть, игровой мир еще не перенесен. |
| `src/View/View.php` | `DONE_NEW` | Рендер шаблонов и escaping. |
| `views/home.php` | `DONE_NEW` | Новая главная. |
| `views/error.php` | `DONE_NEW` | Шаблон ошибки. |
| `views/game-start.php` | `PARTIAL_NEW` | Стартовая страница игры без полноценного мира. |

## Игровой Мир

Текущий `game.php?go=map` все еще работает через legacy-слой. Это временно.

| Legacy файл | Новый модуль | Статус | Что сделать |
|---|---|---:|---|
| `game.php` | `Game/*Controller` | `TODO_REWRITE` | Разобрать по маршрутам, не расширять как главный роутер. |
| `include/files/map.world.php` | `MapController`, `views/game/map.php` | `TODO_REWRITE` | Убрать frameset, сделать один shell экрана. |
| `include/files/char.world.php` | `LocationController` | `TODO_REWRITE` | Возвращать HTML partial/JSON состояния локации. |
| `include/files/char.work.php` | `MapMoveController::move` | `TODO_REWRITE` | Команда перехода должна возвращать JSON: `ok`, `location`, `chatEvent`, `users`. |
| `include/files/chat.world.php` | `ChatController` | `TODO_REWRITE` | Разделить API чата и frontend-компонент. |
| `include/files/buttons.world.php` | `ActionPanel` | `TODO_REWRITE` | Переписать как компонент без самостоятельных редиректов. |
| `include/files/mapusers.world.php` | `LocationUsersController` | `TODO_REWRITE` | Отдавать список игроков JSON/partial. |
| `include/data.world.php` | `LocationGraphRepository` | `TODO_REWRITE` | Перенести граф переходов в БД или конфиг. |
| `include/loc.world.php` | `LocationRuleService` | `TODO_REWRITE` | Правила прохода вынести в сервис. |
| `include/rooms/*.php` | `LocationView/LocationData` | `TODO_REWRITE` | Локации вынести из PHP-скриптов в данные + шаблоны. |
| `include/rooms/npc/*.php` | `NpcController`, `QuestService` | `TODO_REWRITE` | NPC и квесты переписать как сценарии/сервисы. |

## Ближайший План

1. Стабилизировать текущий legacy-экран только настолько, чтобы он не блокировал игру.
2. Начать новый игровой shell: `views/game/world.php` + JS-компоненты `location`, `chat`, `users`, `actions`.
3. Сделать первый API:
   - `GET /api/game/state`
   - `POST /api/map/move`
   - `GET /api/location/current`
   - `GET /api/location/users`
   - `GET /api/chat/messages`
   - `POST /api/chat/messages`
4. Перенести переходы локаций из `char.work.php` в `MapMoveService`.
5. Перенести текущую локацию из `char.world.php` в `LocationService`.
6. После этого отключить frameset для `go=map`.

## Legacy Правило

Если файл лежит в `include/`, `admin/` или root старого проекта, его нельзя считать новым кодом. Его можно:

- читать для понимания бизнес-логики;
- временно чинить, если игра полностью сломана;
- помечать как `LEGACY_COMPAT`.

Его нельзя:

- расширять новой архитектурой;
- превращать в источник истины;
- смешивать с новым frontend/API как постоянное решение.

## Что Не Переносить

| Файл/папка | Статус | Причина |
|---|---:|---|
| `cache/` | `DO_NOT_PORT` | Runtime/cache. |
| `templates_c/` | `DO_NOT_PORT` | Compiled templates. |
| `log/` | `DO_NOT_PORT` | Runtime logs. |
| `webgrind/` | `DO_NOT_PORT` | Старый profiler. |
| `test/` | `DO_NOT_PORT` | Старые тестовые файлы и мусор. |
| `index_old.php` | `DO_NOT_PORT` | Старый дубль. |
| `1index.html` | `DO_NOT_PORT` | Старый статический дубль. |
| `testx.php`, `testind.php`, `text.php` | `DO_NOT_PORT` | Отладочные файлы. |

## Последнее Обновление

2026-05-03:

- Зафиксировано решение: целевая архитектура игры - новый shell + JSON API, не frameset.
- Legacy-переход `charWork` временно стабилизирован: после смены `buildmy` он принудительно обновляет фрейм локации и список игроков.
- `game.php`, `include/files/*`, `include/rooms/*` остаются `LEGACY_COMPAT/TODO_REWRITE`, а не новым кодом.
- Следующая инженерная задача: вынести переход по карте в `MapMoveService` и сделать `POST /api/map/move`.
