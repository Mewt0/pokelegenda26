# Pokemon 8.0 Rewrite Status

Этот файл фиксирует направление переписывания проекта. Цель: не чинить старую игру слоями костылей, а постепенно вынести рабочую бизнес-логику в нормальную архитектуру 2026 года.

## Решение

Старые фреймы, скрытые iframe (`_chat_two`, `_location_work`) и PHP-страницы, которые возвращают `<script>parent...`, больше не считаются целевой архитектурой.

Они могут временно оставаться только как совместимый слой, пока переносится логика. Новая игра должна работать так:

- один игровой экран без frameset;
- PHP 8.1+ минимум, целевой стиль PHP 8.3+ без использования несовместимых фич там, где нужна совместимость;
- front controller `public/index.php`;
- роутинг через контроллеры;
- JSON API для действий карты, чата, NPC, боев, покедекса, атакадекса и инвентаря;
- PDO и prepared statements вместо `mysql_*`;
- UTF-8 в коде, шаблонах, БД и API;
- CSRF для POST/command-запросов;
- отдельные сервисы домена: карта, локации, квесты, NPC, чат, бой, инвентарь, покедекс, атакадекс;
- frontend обновляет только нужные области экрана через `fetch`, без перезагрузки всей страницы.

## Статусы

- `DONE_NEW` — написано с нуля под новую архитектуру и стабильно работает.
- `PARTIAL_NEW` — новый каркас есть, часть бизнес-логики перенесена, но модуль ещё требует доработки.
- `LEGACY_COMPAT` — временный совместимый слой старой игры.
- `TODO_REWRITE` — нужно переписать с нуля.
- `DO_NOT_PORT` — не переносить.

---

## Новое ядро

| Файл | Статус | Комментарий |
|---|---:|---|
| `public/index.php` | `DONE_NEW` | Новый front controller. |
| `public/.htaccess` | `DONE_NEW` | Rewrite новых запросов в `public/index.php`. |
| `composer.json` | `DONE_NEW` | Описание PHP-проекта и PSR-4. |
| `.editorconfig` | `DONE_NEW` | Единый стиль файлов: UTF-8, LF. |
| `config/app.php` | `DONE_NEW` | Конфиг приложения из `.env`. |
| `config/database.php` | `DONE_NEW` | Конфиг базы из `.env`. |
| `src/Support/Env.php` | `DONE_NEW` | Загрузка `.env`. |
| `src/Support/Autoload.php` | `DONE_NEW` | Временный PSR-4 autoload. |
| `src/Http/Request.php` | `DONE_NEW` | Объект запроса. |
| `src/Http/Response.php` | `DONE_NEW` | Объект ответа. |
| `src/Http/Router.php` | `DONE_NEW` | Мини-роутер нового ядра. |
| `src/Database/Connection.php` | `DONE_NEW` | PDO-подключение к базе. |
| `src/Security/Session.php` | `DONE_NEW` | Работа с сессиями. Требование: совместимость с PHP 8.1+. |
| `src/Security/Csrf.php` | `DONE_NEW` | CSRF-токены. |
| `src/Security/BanGuard.php` | `DONE_NEW` | Проверка IP-банов. |
| `src/Security/PasswordHasher.php` | `DONE_NEW` | Поддержка старого хэша и нового `password_hash`. |
| `src/Repository/BanRepository.php` | `DONE_NEW` | Репозиторий банов. |
| `src/Repository/UserRepository.php` | `PARTIAL_NEW` | Логин и online-данные есть, нужно расширить профиль/игровые поля. |
| `src/Repository/RankingRepository.php` | `PARTIAL_NEW` | Рейтинги частично перенесены. |
| `src/Controller/HomeController.php` | `DONE_NEW` | Главная страница. |
| `src/Controller/AuthController.php` | `PARTIAL_NEW` | Логин есть, регистрация и throttling ещё нужны. |
| `src/Controller/GameController.php` | `PARTIAL_NEW` | Новый `/game` shell есть, но не все модули перенесены. |
| `src/View/View.php` | `DONE_NEW` | Рендер шаблонов и escaping. |
| `views/home.php` | `DONE_NEW` | Новая главная. |
| `views/error.php` | `DONE_NEW` | Шаблон ошибки. |
| `views/game-start.php` | `PARTIAL_NEW` | Новый игровой экран без frameset, но UI и модули ещё дорабатываются. |

---

## Новый игровой экран `/game`

Текущий `/game` уже не является простой заглушкой. Он работает как новый shell, который получает данные через JSON API.

| Модуль | Статус | Комментарий |
|---|---:|---|
| Новый `/game` shell | `PARTIAL_NEW` | Работает без frameset, но UI ещё стабилизируется. |
| `views/game-start.php` | `PARTIAL_NEW` | Рендерит карту, локацию, игроков, NPC, нижнюю панель, PvE overlay, Dex overlay. |
| `public/css/game-start.css` | `PARTIAL_NEW` | Стили нового игрового экрана, боёвки и overlay. |
| `public/js/dex-overlay.js` | `PARTIAL_NEW` | Новый overlay для покедекса/атакадекса. Требует финальной проверки маршрутов. |

---

## API игрового мира

| API / Файл | Статус | Комментарий |
|---|---:|---|
| `GET /api/game/state` | `PARTIAL_NEW` | Возвращает пользователя, локацию, переходы, NPC, игроков, PvE-состояние. |
| `POST /api/map/move` | `PARTIAL_NEW` | Базовые переходы работают. Сложные legacy-правила прохода ещё не все перенесены. |
| `GET /api/location/npc` | `PARTIAL_NEW` | Загружает NPC-диалог без legacy `game.php?go=char`. |
| `POST /api/location/npc/action` | `PARTIAL_NEW` | Выполняет действие NPC через CSRF. |
| `GET /api/battle/pve/state` | `PARTIAL_NEW` | Возвращает состояние PvE-боя. |
| `POST /api/battle/pve/action` | `PARTIAL_NEW` | Выполняет действие игрока в PvE. |
| `POST /api/battle/pve/ack-end` | `PARTIAL_NEW` | Подтверждает конец боя и возвращает игрока в мир. |
| `GET /api/dex/pokemon` | `PARTIAL_NEW` | Новый API покедекса. Требует проверки данных и UI. |
| `GET /api/dex/attack` | `PARTIAL_NEW` | Новый API атакадекса. Требует проверки данных и UI. |
| `GET /api/chat/messages` | `TODO_REWRITE` | Полноценный новый чат ещё не перенесён. |
| `POST /api/chat/messages` | `TODO_REWRITE` | Отправка сообщений в новом чате ещё не готова. |

---

## Карта и локации

| Legacy файл | Новый модуль | Статус | Комментарий |
|---|---|---:|---|
| `game.php` | `GameController`, `GameApiController` | `LEGACY_COMPAT` / `PARTIAL_NEW` | Старый роутер не расширять. Новый `/game` уже работает отдельно. |
| `include/files/map.world.php` | `views/game-start.php`, `GameApiController` | `PARTIAL_NEW` | Новый экран есть, но старый файл остаётся как образец. |
| `include/files/char.world.php` | `LocationStateService` | `PARTIAL_NEW` | Текущая локация уже отдаётся через API. |
| `include/files/char.work.php` | `MapMoveService`, `POST /api/map/move` | `PARTIAL_NEW` | Базовый переход работает через JSON. |
| `include/files/mapusers.world.php` | `LocationUsersController` / `GameApiController` | `PARTIAL_NEW` | Список игроков на локации уже отдаётся в `state`, но отдельный модуль ещё можно улучшить. |
| `include/data.world.php` | `LocationGraph`, `LocationRepository` | `PARTIAL_NEW` | Граф переходов частично перенесён. |
| `include/loc.world.php` | `LocationRuleService` | `TODO_REWRITE` | Сложные правила прохода ещё нужно вынести в сервис. |
| `include/rooms/*.php` | `LegacyRoomDataExtractor`, `LocationRepository` | `PARTIAL_NEW` | Данные локаций читаются, но нужно постепенно вынести в нормальный слой данных. |

### Новые файлы карты/локаций

| Файл | Статус | Комментарий |
|---|---:|---|
| `src/Repository/LocationRepository.php` | `PARTIAL_NEW` | Получение данных локаций. |
| `src/Game/LocationGraph.php` | `PARTIAL_NEW` | Граф переходов. |
| `src/Game/LocationStateService.php` | `PARTIAL_NEW` | Сбор состояния текущей локации. |
| `src/Game/MapMoveService.php` | `PARTIAL_NEW` | Логика переходов. |
| `src/Game/LegacyRoomDataExtractor.php` | `PARTIAL_NEW` | Временно читает данные из legacy rooms без исполнения старых скриптов. |
| `src/Controller/GameApiController.php` | `PARTIAL_NEW` | API состояния мира и переходов. |

---

## NPC и квесты

| Legacy файл | Новый модуль | Статус | Комментарий |
|---|---|---:|---|
| `include/rooms/npc/*.php` | `NpcDialogService`, `NpcApiController`, `QuestService` | `PARTIAL_NEW` | Перенесён первый реальный NPC-flow, остальные ещё нужно переносить. |
| `Коллекционер Билли` | `NpcDialogService` + `QuestRepository` | `PARTIAL_NEW` | Диалог, старт/проверка/сдача квеста 7 работают через новый слой. |
| `items_users` для квестов | `InventoryRepository` | `PARTIAL_NEW` | Используется для проверки и списания предметов в квесте. |

### Новые файлы NPC/квестов

| Файл | Статус | Комментарий |
|---|---:|---|
| `src/Game/NpcDialogService.php` | `PARTIAL_NEW` | Новый сервис NPC-диалогов. |
| `src/Controller/NpcApiController.php` | `PARTIAL_NEW` | API NPC. |
| `src/Repository/QuestRepository.php` | `PARTIAL_NEW` | Работа с квестами. |
| `src/Repository/InventoryRepository.php` | `PARTIAL_NEW` | Работа с предметами игрока. Пока не полноценный инвентарь. |

---

## PvE бой

PvE-бой уже перенесён в новый слой, но ещё требует стабилизации математики, статусов, UI и наград.

| Модуль | Статус | Комментарий |
|---|---:|---|
| `src/Controller/PveBattleApiController.php` | `PARTIAL_NEW` | API PvE боя. |
| `src/Game/WildEncounterService.php` | `PARTIAL_NEW` | Создание дикого боя. Важно использовать `battle_id_sequence`, если `battles.id` не AUTO_INCREMENT. |
| `src/Game/BattleEngineService.php` | `PARTIAL_NEW` | Основная логика боя: атаки, раунды, статусы, конец боя. |
| `src/Game/BattleMathService.php` | `PARTIAL_NEW` | Расчёт статов, точности, урона, эффективности типов. Требуется финальное тестирование. |
| `src/Repository/BattleRepository.php` | `PARTIAL_NEW` | Работа с `battles`, `battle_log`, `statpokemonbatle`, `bttle_status`, `pok_user`, `pok_pve`. |
| Battle overlay в `/game` | `PARTIAL_NEW` | Работает, но UI ещё исправляется: лог, высота окна, спрайты, конец боя. |

### Что уже сделано по PvE

- бой запускается через новый overlay в `/game`;
- действия идут через JSON API;
- есть лог раундов;
- подключена таблица `statpokemonbatle`;
- подключаются бафы/дебафы через боевую математику;
- `+6` и `-6` должны считаться как стадии, а не как стартовые бафы;
- старт боя должен быть без активных бафов: `plus = 0`, `minus = 0`;
- `attac_dop` начал использоваться для вторичных эффектов;
- `status` / `bttle_status` начали использоваться для яд/сон/ожог/паралич;
- type effectiveness начал переноситься в новую математику;
- конец боя не должен мгновенно удалять лог до `ack-end`.

### Что ещё проверить по PvE

- корректность формулы статов:
  - обычные статы: `base * ((2 + plus) / (2 + minus))`;
  - `+6 = x4`;
  - `-6 = x0.25`;
  - одновременные plus/minus считаются ratio-формулой, а не простым `plus - minus`;
- точность и ловкость:
  - `atac_accuracy = 0` означает “всегда попадает”;
  - точность атакующего и ловкость защитника считаются отдельно;
- `attac_dop`:
  - шанс `0` не должен превращаться в `100%`;
  - вторичные эффекты должны срабатывать только по `chans_dop` / реальному шансу;
- статусы:
  - яд наносит урон по раундам;
  - ожог режет физическую атаку;
  - паралич режет скорость и может пропускать ход;
  - сон/заморозка/испуг/спутанность не должны ломать очередь хода;
- конец боя:
  - победа;
  - поражение;
  - побег;
  - `ack-end`;
  - refresh страницы до `ack-end`;
- награды:
  - опыт;
  - деньги;
  - дроп;
  - рейтинг;
  - счастье/энергия/ивенты;
- смена покемона;
- использование предметов в бою;
- поимка покемона.

---

## Пokedex / Attackdex

| Модуль | Статус | Комментарий |
|---|---:|---|
| `src/Repository/DexRepository.php` | `PARTIAL_NEW` | Читает данные из `pokemon`, `poke_base`, `attac_power`, `attac_poke`, `attac_egg`, локаций. |
| `src/Controller/DexApiController.php` | `PARTIAL_NEW` | API покедекса и атакадекса. |
| `public/js/dex-overlay.js` | `PARTIAL_NEW` | Frontend overlay. Нужно проверить маршруты и открытие из старых ссылок. |
| Pokedex overlay | `PARTIAL_NEW` | Каркас есть, надо довести до нормального UI. |
| Attackdex overlay | `PARTIAL_NEW` | Каркас есть, надо довести до нормального UI. |

### Что надо проверить по Dex

- переходы:
  - `game.php?go=pokedex&id=...` должны открывать новый overlay;
  - `game.php?go=atk&id=...` должны открывать новый overlay;
- поиск покемонов;
- поиск атак;
- отображение:
  - номер покемона;
  - имя;
  - типы;
  - базовые статы;
  - атаки по уровням;
  - egg moves;
  - локации появления;
  - описание атаки;
  - сила;
  - точность;
  - PP;
  - категория;
  - тип;
  - вторичные эффекты.

---

## Чат

| Legacy файл | Новый модуль | Статус | Комментарий |
|---|---|---:|---|
| `include/files/chat.world.php` | `ChatController`, `ChatRepository`, Chat API | `TODO_REWRITE` | Полноценный новый чат ещё не перенесён. |
| Старые вкладки чата | Новый frontend-компонент чата | `TODO_REWRITE` | Нужны вкладки общий/торговля/бои/помощь/приват/клан. |

### План по чату

- `GET /api/chat/messages`;
- `POST /api/chat/messages`;
- `ChatRepository`;
- `ChatController`;
- CSRF для отправки;
- защита от XSS;
- автообновление без iframe;
- вкладки каналов;
- приватные сообщения;
- системные сообщения боя/локации.

---

## Инвентарь

| Legacy файл | Новый модуль | Статус | Комментарий |
|---|---|---:|---|
| `include/files/items*`, `function.items.php`, `function.post.items.php` | `InventoryController`, `InventoryService`, `InventoryRepository` | `TODO_REWRITE` / `PARTIAL_NEW` | Репозиторий есть частично, полноценный UI/API ещё не готов. |
| `items_users` | `InventoryRepository` | `PARTIAL_NEW` | Используется для квеста Билли и базовых операций. |
| Использование предметов | `InventoryService` | `TODO_REWRITE` | Нужно переносить логику лечения, эволюции, конфет, подарков, статусов. |

### План по инвентарю

- `GET /api/inventory`;
- `POST /api/inventory/use`;
- `POST /api/inventory/drop`;
- `POST /api/inventory/give` только для админки;
- overlay инвентаря в `/game`;
- поддержка боевых предметов;
- поддержка предметов эволюции;
- поддержка лечения;
- поддержка подарков/рандомных предметов.

---

## Пользователь / профиль / команда покемонов

| Модуль | Статус | Комментарий |
|---|---:|---|
| `UserRepository` | `PARTIAL_NEW` | Логин/онлайн есть, игровые поля нужно расширить. |
| Команда покемонов | `TODO_REWRITE` | Нужно вынести из legacy-файлов. |
| Профиль тренера | `TODO_REWRITE` | Старый `trenInfo` ещё не перенесён. |
| Список покемонов игрока | `TODO_REWRITE` | Нужен новый API и overlay. |

---

## Админка

Админка пока не считается частью нового ядра.

| Legacy файл/папка | Статус | Комментарий |
|---|---:|---|
| `admin/*` | `LEGACY_COMPAT` | Временно может работать отдельно. |
| `poke.php` | `LEGACY_COMPAT` | Ранее правился визуально, но не считается новым модулем. |
| `gitem.php` | `LEGACY_COMPAT` | Выдача предметов пока legacy. |
| `attak_pokes.php` | `LEGACY_COMPAT` | Админка атак пока legacy. |
| `bb_news_admin.php` | `LEGACY_COMPAT` | Новости пока legacy. |

Правило: админку переносить отдельной задачей после стабилизации игрового ядра.

---

## Legacy Правило

Если файл лежит в `include/`, `admin/` или root старого проекта, его нельзя считать новым кодом.

Его можно:

- читать для понимания бизнес-логики;
- временно чинить, если игра полностью сломана;
- помечать как `LEGACY_COMPAT`.

Его нельзя:

- расширять новой архитектурой;
- превращать в источник истины;
- смешивать с новым frontend/API как постоянное решение.

---

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
| Старые кэши изображений | `DO_NOT_PORT` | Не являются бизнес-логикой. |

---

## Текущая оценка готовности

| Область | Готовность | Комментарий |
|---|---:|---|
| Новое ядро | 80–90% | Основа готова. |
| Новый `/game` shell | 55–65% | Работает, но UI и модули ещё стабилизируются. |
| Карта / переходы | 55–60% | Базовый JSON-flow есть. |
| Локации | 45–55% | Данные читаются, но нужно вынести из legacy в нормальный источник. |
| NPC / квесты | 20–30% | Есть первый рабочий slice с Билли. |
| PvE бой | 55–65% | Работает, но нужно добить математику, статусы, UI, награды. |
| BattleMath | 55–65% | Формулы есть, требуется тестирование на серверной БД. |
| Покедекс | 35–45% | Каркас есть, нужно довести маршруты и UI. |
| Атакадекс | 35–45% | Каркас есть, нужно довести маршруты и UI. |
| Чат | 10–15% | Почти весь новый чат ещё впереди. |
| Инвентарь | 20–25% | Репозиторий частично есть, UI/API нет. |
| Админка | legacy | Переносить отдельно. |

---

## Ближайший инженерный план

### Приоритет 1 — стабилизация PvE боя

1. Проверить и зафиксировать создание battle id через серверную схему:
   - если `battles.id` не AUTO_INCREMENT, использовать `battle_id_sequence`;
   - не использовать опасный `MAX(id)+1` для боёв.
2. Проверить старт боя:
   - нет фейковых `acc +6`;
   - `statpokemonbatle` стартует с нулями.
3. Проверить математику:
   - `+6 = x4`;
   - `-6 = x0.25`;
   - plus/minus считаются ratio-формулой;
   - `atac_accuracy = 0` означает “всегда попадает”.
4. Проверить `attac_dop`:
   - шанс 0 не равен 100%;
   - вторичные эффекты применяются по реальному шансу.
5. Проверить статусы:
   - poison;
   - burn;
   - paralysis;
   - sleep;
   - freeze;
   - confusion;
   - flinch.
6. Починить UI боя:
   - окно не растягивается от длинного лога;
   - лог скроллится;
   - фон арены не увеличивается;
   - спрайт игрока не пропадает;
   - кнопки не дают двойной action.
7. Проверить конец боя:
   - победа;
   - поражение;
   - побег;
   - refresh до `ack-end`;
   - очистка после `ack-end`.

### Приоритет 2 — Pokedex / Attackdex

1. Заменить заглушки на рабочие overlay.
2. Перехватить старые ссылки:
   - `game.php?go=pokedex&id=...`;
   - `game.php?go=atk&id=...`.
3. Сделать поиск покемонов и атак.
4. Подтянуть данные:
   - `pokemon`;
   - `poke_base`;
   - `attac_power`;
   - `attac_poke`;
   - `attac_egg`;
   - `attac_dop`;
   - `stat_attak`;
   - локации появления.

### Приоритет 3 — чат

1. `ChatRepository`.
2. `ChatController`.
3. `GET /api/chat/messages`.
4. `POST /api/chat/messages`.
5. Frontend-компонент чата без iframe.
6. Вкладки каналов.

### Приоритет 4 — инвентарь

1. Полноценный `InventoryService`.
2. `GET /api/inventory`.
3. `POST /api/inventory/use`.
4. Overlay инвентаря.
5. Перенос логики из `function.items.php` и `function.post.items.php`.

### Приоритет 5 — перенос остальных NPC/квестов

1. По одному NPC-flow.
2. Не переносить все скрипты сразу.
3. Каждый NPC — отдельный вертикальный slice:
   - диалог;
   - условия;
   - действия;
   - награда;
   - тест.

---

## Последнее обновление

2026-05-03:

- Зафиксировано решение: целевая архитектура игры — новый shell + JSON API, не frameset.
- Создано новое ядро: front controller, router, request/response, PDO, session, CSRF, repositories, controllers.
- Новый `/game` shell начал работать без frameset.
- Добавлены:
  - `GET /api/game/state`;
  - `POST /api/map/move`;
  - `GET /api/location/npc`;
  - `POST /api/location/npc/action`;
  - `GET /api/battle/pve/state`;
  - `POST /api/battle/pve/action`;
  - `POST /api/battle/pve/ack-end`.
- Создан вертикальный slice карты:
  - `LocationRepository`;
  - `LocationGraph`;
  - `LocationStateService`;
  - `MapMoveService`;
  - `GameApiController`.
- Создан временный extractor для legacy rooms:
  - `LegacyRoomDataExtractor`.
- `/api/game/state` отдаёт:
  - локацию;
  - описание;
  - картинку;
  - переходы;
  - NPC;
  - игроков;
  - признак PvE.
- Создан первый NPC API slice:
  - `NpcDialogService`;
  - `NpcApiController`;
  - `QuestRepository`.
- Перенесён первый реальный NPC-flow:
  - `Коллекционер Билли`;
  - старт/проверка/сдача квеста 7.
- Добавлен `InventoryRepository` для работы с предметами в квестах.
- Начат перенос PvE боя:
  - `WildEncounterService`;
  - `BattleRepository`;
  - `BattleEngineService`;
  - `BattleMathService`;
  - `PveBattleApiController`;
  - battle overlay в `views/game-start.php`.
- Начат перенос Dex:
  - `DexRepository`;
  - `DexApiController`;
  - `dex-overlay.js`.
- Выявлены текущие проблемы:
  - длинный лог растягивает окно боя;
  - нужен скролл лога;
  - иногда пропадает спрайт игрока;
  - заглушки покедекса/атакадекса нужно заменить рабочим overlay;
  - нужно финально проверить математику `+6/-6`, `attac_dop`, `status`, `type effectiveness`.
- `game.php`, `include/files/*`, `include/rooms/*`, `admin/*` остаются `LEGACY_COMPAT/TODO_REWRITE`, а не новым кодом.

Следующая инженерная задача:

1. стабилизировать PvE бой;
2. довести Pokedex / Attackdex overlay;
3. затем переносить чат и инвентарь.