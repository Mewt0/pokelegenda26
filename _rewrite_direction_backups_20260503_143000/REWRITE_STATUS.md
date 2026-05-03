# Pokemon 8.0 Rewrite Status

Этот файл фиксирует, что уже написано с нуля, а что еще остается legacy/reference.

Статусы:

- `DONE_NEW` - написано с нуля под новую архитектуру 2026.
- `PARTIAL_NEW` - есть новый каркас, но бизнес-логика еще не перенесена полностью.
- `LEGACY_REFERENCE` - старый файл, используется только как источник логики.
- `TODO_REWRITE` - нужно переписать с нуля.
- `DO_NOT_PORT` - не переносить в новую версию.

## Новое ядро

| Файл | Статус | Комментарий |
|---|---:|---|
| `public/index.php` | `DONE_NEW` | Новый front controller. Это новая замена старому root `index.php` как точке входа. |
| `public/.htaccess` | `DONE_NEW` | Rewrite всех запросов в `public/index.php`. |
| `composer.json` | `DONE_NEW` | Описание PHP 8.3+ проекта и PSR-4. |
| `.editorconfig` | `DONE_NEW` | Единый стиль файлов, UTF-8, LF. |
| `config/app.php` | `DONE_NEW` | Новый конфиг приложения из `.env`. |
| `config/database.php` | `DONE_NEW` | Новый конфиг БД из `.env`; понимает `DB_*` и legacy `server/user/pass/db`. |
| `src/Support/Env.php` | `DONE_NEW` | Загрузка `.env`. |
| `src/Support/Autoload.php` | `DONE_NEW` | Временный PSR-4 autoload без Composer. |
| `src/Support/ClientIp.php` | `DONE_NEW` | Определение IP клиента. |
| `src/Security/Csrf.php` | `DONE_NEW` | CSRF-токены для POST-форм. |
| `src/Security/BanGuard.php` | `DONE_NEW` | Проверка IP-банов перед роутингом. |
| `src/Repository/BanRepository.php` | `DONE_NEW` | Чтение таблицы `banip`. |
| `src/Http/Request.php` | `DONE_NEW` | Объект запроса. |
| `src/Http/Response.php` | `DONE_NEW` | Объект ответа. |
| `src/Http/Router.php` | `DONE_NEW` | Мини-роутер нового ядра. |
| `src/Database/Connection.php` | `DONE_NEW` | PDO-подключение к БД. |
| `src/Security/Session.php` | `DONE_NEW` | Безопасная работа с сессиями. |
| `src/Security/PasswordHasher.php` | `DONE_NEW` | Проверяет старый хэш и современный `password_hash`, умеет rehash. |
| `src/Repository/UserRepository.php` | `PARTIAL_NEW` | Логин и online update готовы. Нужно расширять под профиль/регистрацию. |
| `src/Repository/RankingRepository.php` | `PARTIAL_NEW` | Топ бойцов и покедекс готовы. Остальные рейтинги не перенесены. |
| `src/Controller/HomeController.php` | `DONE_NEW` | Новая главная через `views/home.php`. |
| `src/Controller/AuthController.php` | `PARTIAL_NEW` | Новый логин, CSRF и rehash готовы. Регистрация и throttling еще нет. |
| `src/Controller/GameController.php` | `PARTIAL_NEW` | Стартовая заглушка игры. Карта/чат/бой еще не перенесены. |
| `src/View/View.php` | `DONE_NEW` | Рендер шаблонов и escaping. |
| `views/home.php` | `DONE_NEW` | Новая главная страница. |
| `views/error.php` | `DONE_NEW` | Шаблон ошибки. |
| `views/game-start.php` | `PARTIAL_NEW` | Стартовая страница игры, пока без игровых экранов. |
| `STANDARD_2026.md` | `DONE_NEW` | Стандарт разработки. |
| `ROADMAP.md` | `DONE_NEW` | Карта миграции. |
| `README_RUN.md` | `DONE_NEW` | Запуск нового ядра. |
| `.env` | `DONE_NEW` | Локальные доступы к БД для запуска на этой машине. |
| `database/migrations/2026_05_03_000001_expand_user_password.sql` | `DONE_NEW` | Миграция поля пароля до `varchar(255)`. |
| `CHANGELOG_RU.md` | `DONE_NEW` | Русский журнал изменений. |

## Старые ключевые файлы и их статус

| Legacy файл | Новый файл/модуль | Статус | Что сделать |
|---|---|---:|---|
| `index.php` | `public/index.php`, `HomeController`, `views/home.php` | `PARTIAL_NEW` | Новая главная готова, старую верстку/новости переносить выборочно. |
| `autoriz.php` | `AuthController`, `UserRepository`, `PasswordHasher` | `PARTIAL_NEW` | Логин готов. Нужна миграция паролей на `password_hash`, CSRF и throttling. |
| `ban.php` | `Security/BanGuard`, `BanRepository` | `DONE_NEW` | IP-ban перенесен в guard нового роутера. |
| `game.php` | будущие `MapController`, `CharacterController`, `BattleController`, `InventoryController` | `TODO_REWRITE` | Главный большой файл. Переносить маршрутами, не целиком. |
| `events.php` | будущий `EventStreamController` | `TODO_REWRITE` | SSE/уведомления переписать отдельно. |
| `attak_pokes.php` | будущий `Admin/AttackController` | `TODO_REWRITE` | Перенести только админскую бизнес-логику. |
| `mailTo.php` | будущий `MailController` или service | `TODO_REWRITE` | Проверить назначение, переписать через сервис. |
| `robotsms.php` | будущий payment/sms service | `TODO_REWRITE` | Разобрать платежную/смс логику, убрать секреты. |
| `pInf.php` | будущий `PokemonController` | `TODO_REWRITE` | Перенести вывод информации о покемоне. |
| `style.php` | статические CSS или asset pipeline | `TODO_REWRITE` | Убрать динамическую отдачу CSS, если не нужна. |
| `itemsinpage.class3.php` | future pagination component | `TODO_REWRITE` | Переписать как нормальный paginator. |

## Legacy include/function

| Legacy файл | Новый модуль | Статус | Приоритет |
|---|---|---:|---:|
| `include/function/db3.php` | `Database/Connection`, repositories | `PARTIAL_NEW` | 1 |
| `include/function/config.php` | `config/*.php`, `.env` | `DONE_NEW` | 1 |
| `include/function/globfanction.php` | services/helpers by domain | `TODO_REWRITE` | 1 |
| `include/function/functionusers.php` | `UserRepository`, user view helpers | `TODO_REWRITE` | 1 |
| `include/function/fanction.games.php` | game domain services | `TODO_REWRITE` | 1 |
| `include/function/fanction.games.post.php` | action handlers/controllers | `TODO_REWRITE` | 2 |
| `include/function/get.map.php` | `MapController` JSON endpoints | `TODO_REWRITE` | 1 |
| `include/function/post.map.php` | `MapController` commands | `TODO_REWRITE` | 1 |
| `include/function/battle.functions.php` | `BattleService` | `TODO_REWRITE` | 1 |
| `include/function/battle.functions.tip.php` | `BattleEffectService` | `TODO_REWRITE` | 1 |
| `include/function/atk.php` | `AttackResolver` | `TODO_REWRITE` | 1 |
| `include/function/function.items.php` | `InventoryService` | `TODO_REWRITE` | 2 |
| `include/function/function.post.items.php` | `InventoryController` actions | `TODO_REWRITE` | 2 |
| `include/function/function.events.php` | `DailyRewardService` / events | `TODO_REWRITE` | 2 |
| `include/function/pdx.php` | `PokedexService` | `TODO_REWRITE` | 2 |
| `include/function/poke_anim.php` | asset helper/service | `TODO_REWRITE` | 3 |

## Legacy include/files

| Legacy файл | Новый модуль | Статус | Приоритет |
|---|---|---:|---:|
| `include/files/shapka.php` | layout templates | `TODO_REWRITE` | 1 |
| `include/files/bottom.php` | layout templates | `TODO_REWRITE` | 1 |
| `include/files/start.php` | `GameController::start` | `PARTIAL_NEW` | 1 |
| `include/files/map.world.php` | `MapController` + view/API | `TODO_REWRITE` | 1 |
| `include/files/char.world.php` | `CharacterController` | `TODO_REWRITE` | 1 |
| `include/files/chat.world.php` | `ChatController` | `TODO_REWRITE` | 1 |
| `include/files/buttons.world.php` | action panel component | `TODO_REWRITE` | 1 |
| `include/files/mapusers.world.php` | `MapUsersController` | `TODO_REWRITE` | 2 |
| `include/files/fight_pve.world.php` | `BattleController::pve` | `TODO_REWRITE` | 1 |
| `include/files/fight_pvp.world.php` | `BattleController::pvp` | `TODO_REWRITE` | 1 |
| `include/files/pokemon.php` | `PokemonController::index` | `TODO_REWRITE` | 2 |
| `include/files/items.users.php` | `InventoryController::index` | `TODO_REWRITE` | 2 |
| `include/files/pokedex.php` | `PokedexController` | `TODO_REWRITE` | 2 |
| `include/files/profile.users.php` | `ProfileController` | `TODO_REWRITE` | 2 |
| `include/files/registration.php` | `RegistrationController` | `TODO_REWRITE` | 1 |
| `include/files/registrationseve.php` | `RegistrationController::store` | `TODO_REWRITE` | 1 |
| `include/files/sends.php` | `MessageController` | `TODO_REWRITE` | 3 |
| `include/files/friends.game.php` | `FriendController` | `TODO_REWRITE` | 3 |
| `include/files/clans.users.php` | `ClanController` | `TODO_REWRITE` | 3 |

## Legacy admin

| Legacy файл | Новый модуль | Статус | Приоритет |
|---|---|---:|---:|
| `admin/admin.php` | `Admin/DashboardController` | `TODO_REWRITE` | 1 |
| `admin/gitem.php` | `Admin/GrantItemController` | `TODO_REWRITE` | 1 |
| `admin/poke.php` | `Admin/PokemonGrantController` | `TODO_REWRITE` | 1 |
| `admin/attak_pokes.php` | `Admin/AttackController` | `TODO_REWRITE` | 1 |
| `admin/info_pokes.php` | `Admin/PokemonInfoController` | `TODO_REWRITE` | 2 |
| `admin/alm.php` | `Admin/CurrencyController` | `TODO_REWRITE` | 2 |
| `admin/bb_news_admin.php` | `Admin/NewsController` | `TODO_REWRITE` | 3 |

## Не переносить

| Файл/папка | Статус | Причина |
|---|---:|---|
| `cache/` | `DO_NOT_PORT` | Runtime/cache. |
| `templates_c/` | `DO_NOT_PORT` | Compiled templates. |
| `log/` | `DO_NOT_PORT` | Runtime logs. |
| `webgrind/` | `DO_NOT_PORT` | Старый профайлер. |
| `test/` | `DO_NOT_PORT` | Тестовый мусор старого проекта. |
| `index_old.php` | `DO_NOT_PORT` | Старый дубль. |
| `1index.html` | `DO_NOT_PORT` | Старый дубль/статика. |
| `testx.php`, `testind.php`, `text.php` | `DO_NOT_PORT` | Отладочные файлы. |

## Последнее обновление

2026-05-03:

- Создано новое ядро `public/src/config/views`.
- Новая главная: `public/index.php` + `HomeController` + `views/home.php`.
- Новый логин: `AuthController` + `UserRepository`.
- CSRF для логина: `Security/Csrf`.
- IP-ban: `Security/BanGuard` + `BanRepository`.
- Миграция паролей: `PasswordHasher` + SQL migration для `users.password`.
- Новый старт игры: `GameController` + `views/game-start.php`.
- Старые файлы помечены как legacy/reference.
