# Карта проекта PokemonChic для добавления контента

Обновлено: 2026-06-04.

Этот файл нужен как быстрая карта: куда добавлять предметы, картинки, NPC, квесты, игровые правила и UI. Если здесь и старые legacy-файлы противоречат друг другу, работать по этой карте и живому коду нового слоя.

## Главное правило

- Новый runtime игры идёт через `public/index.php`, маршруты `/game/...` и `/api/...`.
- Старые `game.php`, `include/*`, старые `admin/*.php` и legacy popup использовать только как справочник поведения. Новые функции на них не строить.
- Все изменения в базе, которые должны повторяться на сервере, делать через `database/migrations/*.sql`, а не только ручной правкой БД.
- Runtime-картинки брать из `public/img/...`. Папки вроде `downloaded_assets_sorted/...` — это сырьё, не источник для фронта.

## Быстрая таблица: куда добавлять что

| Что добавить | Основное место | Картинки | Логика |
| --- | --- | --- | --- |
| Предмет | `items`, `item_gameplay_metadata` | `public/img/items/<id>.png` | `src/Repository/InventoryRepository.php`, `src/Repository/RewardRepository.php`, для боевых эффектов `src/Game/BattleEngineService.php` |
| Held item | `item_gameplay_metadata` | `public/img/items/<id>.png` | `InventoryRepository` для equip/unequip, `BattleEngineService` для эффекта в бою |
| Подарок/ящик | `items` + loot/reward миграции | `public/img/items/<id>.png` | `InventoryRepository::openGift`, `RewardRepository::grantPipeline()` |
| Квест | `quest_definitions`, `quest_steps` | награды через item/pokemon icons | `src/Repository/QuestRepository.php`, NPC/события в `src/Game/NpcDialogService.php` |
| NPC | `config/location_content.php` | портреты/иконки по ситуации | `NpcDialogService`, `NpcDialogEventTrait`, API `/api/location/npc` |
| Локация/переход | `config/location_content.php`, таблицы/legacy world data | фон/иконки в `public/img/ui` или legacy room assets | `src/Repository/LocationRepository.php`, `src/Repository/TransportRepository.php` |
| Покемон игроку | `pok_user`, `attac_my_poke` | см. блок покемонов ниже | `src/Repository/PokemonRepository.php`, админка/QA seed |
| Атака | `attac_power`, learnset tables | иконка типа/атаки | `BattleEngineService`, Dex/AttackDex |
| Primal/Mega форма | form/transformation migrations | Pok/Dex sprites | `BattleTransformationCatalog`, `BattleEngineService`, `DexRepository` |
| Комиссионная лавка | `market_lots`, `market_logs`, `market_return_storage` | item/pokemon/egg icons | `src/Repository/CommissionMarketRepository.php`, `public/js/commission-market.js` |
| Trainer Card | profile/gym/user settings tables | badges/items/avatar | `ProfileRepository`, `ProfileController`, `trainer-profile-window.js` |
| Админка | `views/game-admin.php` | `public/img/ui` | `AdminApiController`, `AdminRepository` + traits, `admin-panel.js` |

## Куда класть картинки

### Предметы

Основной путь:

```text
public/img/items/<item_id>.png
```

Пример:

```text
public/img/items/349.png
```

Если файл имеет нестандартное имя, добавить alias в:

```text
public/img/items/index.json
```

Сырьевая папка:

```text
downloaded_assets_sorted/items_60x60_numeric
```

Её можно использовать только чтобы взять файл и скопировать в `public/img/items`. Фронт не должен ходить туда напрямую.

### Покемоны для Покедекса

Основные папки:

```text
public/img/pokemon/art
public/img/pokemon/art-shiny
public/img/pokemon/small
public/img/pokemon/small-shiny
```

Они используются для Покедекса, карточек и маленьких превью.

### Боевые спрайты

Showdown-style front/back спрайты сейчас тянутся отсюда:

```text
Pok/spriteanim   фронт обычный
Pok/back         спина обычная
Pok/shiny        фронт shiny
Pok/sback        спина shiny
```

Для PvE/PvP важно класть переднюю и заднюю сторону. Если есть только картинка для Покедекса, бой может показать fallback или старый sprite.

### UI-иконки

Кнопки, меню, системные значки:

```text
public/img/ui
```

## Как добавить новый предмет

1. Выбрать ID предмета.
2. Положить иконку в `public/img/items/<id>.png`.
3. Создать миграцию в `database/migrations/YYYY_MM_DD_XXXX_name.sql`.
4. В миграции добавить/обновить запись в `items`.
5. Если предмет используется, экипируется или должен иметь ограничения, добавить запись в `item_gameplay_metadata`.
6. Для подарка или ящика завести loot/reward-логику через `InventoryRepository` и `RewardRepository`.
7. Для held item с боевым эффектом добавить обработку в `BattleEngineService`.
8. Проверить через админку, инвентарь и smoke/API.

Минимальная логика для `item_gameplay_metadata`:

```text
category_key        held_item / gift_box / evolution / ticket / vitamin / utility
target_rule         куда можно применять или экипировать
battle_use          можно ли в бою
equip_use           можно ли держать на покемоне
compatibility_rule  кому можно, например kyogre_only
effect_status       implemented / visual_only / todo
```

Если эффект ещё не написан, ставить `visual_only` или `todo`, чтобы не обещать игроку рабочую механику.

## Как добавить квест

1. Создать миграцию в `database/migrations`.
2. Добавить запись в `quest_definitions`:
   - `id`
   - `title`
   - `description`
   - `depends_on_quest_id`
   - `repeatable`
   - `reward_json`
   - `enabled`
3. Добавить шаги в `quest_steps`:
   - `quest_id`
   - `step_no`
   - `title`
   - `description`
   - `action_key`
   - `required_process`
   - `reward_json`
4. Если квест стартует от NPC, добавить NPC/действие в `config/location_content.php` и обработчик в `NpcDialogService`.
5. Если нужен прогресс от боя/ловли/предмета, подключить событие в нужном сервисе через `QuestRepository`.
6. Проверить журнал квестов: `public/js/quest-journal.js`, `/api/quests`.

Награды лучше всегда вести через общий reward-flow, а не прямыми случайными insert в разные таблицы.

## Как добавить NPC

1. Найти локацию в `config/location_content.php`.
2. Добавить NPC в список этой локации: имя, роль, диалог, действия.
3. Если NPC просто говорит текст — достаточно конфига.
4. Если NPC выдаёт квест, предмет, телепортирует, запускает мини-игру или сервис — добавить action handler в:

```text
src/Game/NpcDialogService.php
src/Game/NpcDialogEventTrait.php
```

5. Проверить через `/api/location/npc` и прямо в игре.

Legacy NPC из `include/rooms/...` можно смотреть как источник текста и старых условий, но не подключать напрямую.

## Как добавить локацию или переход

Текущий новый слой читает карту и NPC через:

```text
config/location_content.php
src/Repository/LocationRepository.php
src/Repository/TransportRepository.php
```

Для простой локации нужны:

- название;
- id;
- фон/визуал, если есть;
- список NPC;
- переходы;
- wild encounters, если локация боевая;
- blocked routes, если переход должен быть закрыт условием.

Для транспортов и рейсов смотреть `TransportRepository` и API `/api/transport/*`.

## Как добавить покемона, форму или спрайт

### Данные покемона

- базовые данные: pokedex/base tables;
- покемон игрока: `pok_user`;
- атаки игрока: `attac_my_poke`;
- атаки/справочник: `attac_power`;
- learnset/скрытые атаки — через миграции и Dex/AttackDex.

### Primal/Mega

Не записывать Primal/Mega форму навсегда в `pok_user.basenum`. Это battle form.

Основные места:

```text
database/migrations/*battle_transformations*.sql
database/migrations/*pokemon_form_metadata*.sql
src/Game/BattleTransformationCatalog.php
src/Game/BattleEngineService.php
src/Repository/DexRepository.php
```

## Как добавить UI-окно

Обычно схема такая:

- markup: `views/components/<name>.php` или `views/game-*.php`;
- стили: `public/css/<name>.css`;
- поведение: `public/js/<name>.js`;
- API: `src/Controller/*ApiController.php`;
- данные: `src/Repository/*Repository.php`;
- маршрут: `public/index.php`.

Если окно открывается поверх `/game`, лучше делать overlay-компонент, как лавка, почта, профиль, квесты.

## Где писать код по слоям

```text
public/index.php
```

Роуты, сборка зависимостей, подключение контроллеров. Сюда добавлять только wiring, не игровую логику.

```text
src/Controller
```

HTTP/API слой: проверить CSRF, авторизацию, входные параметры, вернуть JSON/HTML.

```text
src/Repository
```

Запросы к БД, транзакции, блокировки, сохранение состояния.

```text
src/Game
```

Игровые правила: бой, NPC-логика, карты, трансформации, события.

```text
views
```

PHP-шаблоны страниц и overlay-компонентов.

```text
public/js
```

Клики, модалки, API-запросы, обновление UI.

```text
public/css
```

Визуал, адаптив, layout.

```text
database/migrations
```

Все изменения схемы и seed-данных, которые должны жить на сервере.

## Главные рабочие файлы

### Игра

```text
views/game-start.php
public/js/game-start-runtime.js
public/js/game-toolbar.js
public/css/game-shell.css
```

### Инвентарь и предметы

```text
src/Repository/InventoryRepository.php
src/Repository/RewardRepository.php
views/game-items.php
public/js/game-start-runtime.js
public/img/items
```

### Бой

```text
src/Game/BattleEngineService.php
src/Repository/BattleRepository.php
src/Controller/PveBattleApiController.php
src/Controller/PvpBattleApiController.php
```

### Квесты

```text
src/Repository/QuestRepository.php
src/Controller/QuestApiController.php
public/js/quest-journal.js
database/migrations/*quest*.sql
```

### NPC

```text
config/location_content.php
src/Game/NpcDialogService.php
src/Game/NpcDialogEventTrait.php
src/Controller/NpcApiController.php
```

### Комиссионная лавка

```text
src/Repository/CommissionMarketRepository.php
src/Controller/CommissionMarketApiController.php
views/components/commission-market-panel.php
public/js/commission-market.js
public/css/commission-market.css
```

### Админка

```text
views/game-admin.php
src/Controller/AdminApiController.php
src/Repository/AdminRepository.php
src/Repository/Admin*Trait.php
public/js/admin-panel.js
public/js/admin-content-wizard.js
public/css/admin-content-wizard.css
```

## Что лучше не трогать без причины

- `game.php` — старый вход, не строить на нём новые функции.
- `include/*` — legacy-справочник, не runtime для новых систем.
- старые root PHP-страницы — смотреть только как референс.
- `log_php_errors.txt` — не чистить и не править как часть задач.
- `plan.md` — не править без отдельной просьбы.

## Как проверять после изменений

PHP lint:

```powershell
php -l src\Repository\InventoryRepository.php
```

Статус миграций:

```powershell
php tools\migration_status.php --record-status
```

Применить pending миграции:

```powershell
php tools\migration_status.php --apply
```

Полезные smoke:

```powershell
php tools\quests_minimum_smoke.php
php tools\location_npc_transport_smoke.php
php tools\commission_market_smoke.php
php tools\db_integrity_smoke.php
php tools\background_jobs_smoke.php
```

После UI-изменений обязательно открыть `/game` в браузере и проверить кликами, а не только backend.

## Мини-схема запроса

```text
Игрок в браузере
  -> public/index.php
  -> Controller
  -> Repository / Game Service
  -> DB
  -> JSON или view
  -> public/js обновляет overlay/UI
```

## Что можно делать через админку

В `/game/admin` есть `Мастер контента`. Это самый понятный слой для владельца игры:

- создать предмет;
- посмотреть карту ассетов;
- подготовить квест/NPC;
- запускать QA seed/smoke-инструменты;
- смотреть GM Center.

Если админка пока не даёт нужную кнопку, делать через миграцию и код по карте выше.

## Коротко: где добавить предмет и картинку

1. Картинку: `public/img/items/<id>.png`.
2. Название/описание: таблица `items` через миграцию.
3. Категория/совместимость/можно ли экипировать: `item_gameplay_metadata`.
4. Выдача игроку: `RewardRepository` или админка/QA seed.
5. Эффект в бою: `BattleEngineService`.
6. Эффект в инвентаре: `InventoryRepository`.
7. Проверка: открыть инвентарь, экипировать/снять, проверить бой или лавку.
