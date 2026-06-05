# Beta Release Tasks

Обновлено: 2026-06-05.

Цель файла: один рабочий список задач для выхода в beta/open test. Если старые планы из чата конфликтуют с этим файлом, использовать этот файл, `PROJECT_CONTEXT.md`, `REWRITE_STATUS.md` и живой код.

## Текущий Срез Проекта

- Ветка: `codex/beta-foundation`.
- Миграции: `79/79`, `pending=0`, `dirty=0`, `failed=0`.
- Последний beta backup: `storage/backups/pokemonchic_beta_20260605_165616.sql`, проверен через `tools/beta_backup_verify.php` (`14/14` ключевых таблиц).
- Integrity smoke: `P0=0`, `P1=0`, `WARN=0`, `ACCEPTED=4`.
- Принятые исторические WARN до отдельного cleanup-этапа:
  - `items.orphan_user=38`;
  - `pokemon.invalid_owner=63`;
  - `eggs.invalid_owner=1`;
  - `battle.active_unfinished=208`.
- Последний широкий regression из статусов: `tools/open_test_regression.php --profile=full` проходил без падений, но после новых изменений 2026-06-05 нужен свежий полный прогон.
- Последние проверки 2026-06-05: `tools/open_test_regression.php --profile=quick --login=Tacos --password=...` прошёл `20/20`; `inventory_held_items` `16/16`, `breeding_qa` `55/55`, `http_smoke` `52/52`.
- UI-команда покемонов: старая dead breeding-панель удалена из `/game/pokemon`; browser QA desktop/direct route и mobile `390px` подтвердил отсутствие старого блока и horizontal overflow.

## Release Gates

Beta нельзя открывать, если есть хотя бы один пункт:

- P0/P1 integrity issue.
- 500/fatal на основном игровом пути.
- Потеря предметов, покемонов, яиц, валюты или наград.
- Дюп предметов, покемонов, яиц, валюты или наград.
- PvE/PvP desync, повторная выдача награды за один бой, stuck battle без выхода.
- Не применены миграции или есть dirty/failed migration.
- Комиссионка может потерять объект при покупке/отмене/истечении.
- Невозможно зайти новым игроком, получить стартера и начать первый бой.

## P0/P1 До Beta

### 1. Data Lock Перед Beta

- Сделать свежий backup через `tools/beta_backup.php`.
- Проверить restore на локальной копии или отдельной базе.
- Зафиксировать `schema_migrations` и `migration_status`.
- Прогнать `tools/migration_status.php --record-status`.
- Запретить ручные hotfix-изменения БД без миграции.
- Создать короткий список тестовых аккаунтов, которые остаются после cleanup: `Tacos`, `NIGA`, `Система`.

### 2. Cleanup Policy Для Integrity WARN

- `items.orphan_user=38`, `pokemon.invalid_owner=63`, `eggs.invalid_owner=1`, `battle.active_unfinished=208` формализованы в `data_integrity_acceptances` как accepted WARN, чтобы beta-gate отличал исторический мусор от новых P0/P1.
- Отдельный cleanup всё ещё нужен после beta-lock: перенос в safe storage, привязка к `Система`, архив или доказанное удаление.
- Для каждого типа выбрать политику:
  - перенести в safe storage;
  - привязать к `Система`;
  - архивировать;
  - удалить только если доказано, что это мусор.
- Текущий gate уже зелёный: `P0=0`, `P1=0`, `WARN=0`, `ACCEPTED=4`.

### 3. Battle Safety

- Свежий прогон PvE:
  - старт боя;
  - атаки;
  - PP;
  - crit/miss;
  - statuses;
  - weather;
  - catch;
  - rewards;
  - surrender/escape;
  - victory/lose;
  - final screen;
  - `ack-end`;
  - fast double-click по атаке/предмету/побегу.
- Свежий прогон PvP Tacos/NIGA:
  - invite;
  - accept/reject/timeout;
  - reconnect/refresh обоих клиентов;
  - 6x6 бой;
  - switch;
  - items;
  - anti-double-click;
  - surrender;
  - history/replay.
- Проверить, что ловля не работает в PvP.
- Проверить, что награда за бой начисляется ровно один раз.

### 4. Primal/Mega/Weather

- Primal Kyogre отдельно против обычных покемонов.
- Primal Groudon отдельно против обычных покемонов.
- Mega Rayquaza отдельно.
- Финальные strong-weather tests:
  - Kyogre после Groudon;
  - Groudon после Kyogre;
  - Rayquaza после Kyogre/Groudon;
  - Kyogre/Groudon после Rayquaza.
- Проверить:
  - формы не сохраняются в `pok_user.basenum`;
  - attacks/moves сохраняются от обычной формы;
  - stats sane;
  - Fire/Water block работает;
  - battle log/replay пишет transform/weather/revert.

### 5. Reward/Rollback/Safe Storage

- Все начисления должны идти через reward pipeline.
- Ошибка начисления не должна списывать предмет/валюту.
- Battle rewards должны иметь rollback-plan.
- Gift/open-gift должен списывать подарок только после успешного reward.
- Safe storage pending/open должен быть виден в GM Center.
- Нужен smoke после любого изменения экономики.

## Beta-Critical Systems

### First Player Experience

- Новый игрок заходит в игру без ручной помощи.
- Получает/видит стартовую локацию.
- Говорит с нужными NPC.
- Получает стартового покемона.
- Проходит Дорогу 1.
- Запускает первый PvE бой.
- Получает первую награду.
- Видит журнал квестов, подсказку к NPC и может отменить отслеживание.

### NPC / Locations

- Все NPC на стартовой карте должны иметь новый JSON/API flow.
- Legacy PHP не должен запускаться как runtime.
- Для каждого NPC минимум:
  - имя;
  - роль;
  - диалог;
  - действие или понятное пустое состояние;
  - quest/action handler, если NPC влияет на прогресс.
- Отдельно проверить:
  - blocked routes;
  - map transitions;
  - transport NPC;
  - shop/service NPC;
  - event/tournament NPC;
  - rare/hidden NPC.

### Quests

- Стартовая цепочка должна быть полностью playable.
- `Рассказ Спайка` уже покрыт smoke, но нужен browser pass в игре.
- Для beta минимум:
  - starter quest;
  - battle quest;
  - reward quest;
  - cooldown quest;
  - repeatable quest;
  - transport quest.
- Quest Journal:
  - progress bars;
  - rewards;
  - tracked quest;
  - show-on-map;
  - go-to-NPC;
  - cancel tracking;
  - no stuck overlay.

### Inventory / Held Items

- Equip/unequip/replace через UI.
- При клике по held item на покемоне показывать confirm снять/оставить.
- Blue Orb только Kyogre.
- Red Orb только Groudon.
- Soul Dew только Latios/Latias.
- Thick Club только Cubone/Marowak-line.
- TM/evolution/vitamin/gift items:
  - успешное применение;
  - ошибка без списания;
  - visual-only/todo effects явно обозначены;
  - battle-use не ломает ход.

### Commission / Market

- 20-60 минут торговли подряд:
  - выставить предмет;
  - выставить покемона;
  - выставить яйцо;
  - купить Tacos -> NIGA;
  - отменить;
  - истечение через job;
  - повторный POST покупки;
  - покупка своего лота;
  - недостаточно монет;
  - запрещённый предмет.
- Проверить:
  - freeze lot;
  - safe returns;
  - suspicious trade detection;
  - market logs;
  - admin review "сделка норм";
  - no potions/berries sellable;
  - old item/pokemon markets do not bypass safety.

### Trainer Card / Social

- Trainer Card open/close работает всегда.
- Свой профиль показывает `Настройки`, чужой профиль нет.
- Настройки сохраняют:
  - profile description;
  - privacy;
  - show/hide pokemon;
  - profile background;
  - profile frame;
  - title.
- Чужой профиль не раскрывает held items.
- Если пользователь скрывает покемонов, social viewing не показывает команду.
- Gym badges:
  - имеют короткие красивые названия;
  - tooltip не растягивает страницу;
  - выданы один раз;
  - появляются после real gym battle.
- Social buttons должны быть понятными:
  - сообщение;
  - бой;
  - обмен;
  - разведение.

### Mail / Notifications

- Почта должна открываться как игровой overlay, не отдельная web-страница.
- Проверить:
  - inbox;
  - sent;
  - archive;
  - send;
  - read/unread;
  - no mojibake in new messages.
- System notifications:
  - sender `Система`;
  - reward notifications;
  - event notifications;
  - dismissible event/bonus banners.
- SMTP/log transport настроить под beta.

### Breeding / Eggs

- Breeding должен идти через player action popup/invite flow, не через большую форму команды.
- Проверить:
  - male + female same compatibility;
  - incompatible pair;
  - genderless without Ditto/Extract blocked;
  - genderless + Ditto/Extract allowed only by rules;
  - item spent only on success;
  - egg created;
  - `/game/eggs` hatch/incubate refresh safe.

### Tournaments

- Если турниры включены в beta:
  - schedule/timezone;
  - curator;
  - entry fee;
  - registration deadline;
  - participant limits;
  - arena enter/leave;
  - rewards;
  - medals;
  - logs.
- Если сетка/матчмейкинг не готовы, оставить турниры как limited/beta-disabled или clearly marked.

### Admin / GM Center

- Владелец должен уметь без ручного SQL:
  - найти игрока;
  - выдать предмет;
  - выдать покемона;
  - запустить smoke;
  - посмотреть market logs;
  - посмотреть bug reports;
  - посмотреть active/stuck battles;
  - увидеть migrations/integrity/jobs.
- `Мастер контента` должен объяснять, куда добавлять:
  - предмет;
  - картинку;
  - NPC;
  - квест;
  - дроп;
  - событие.
- Legacy-карта только во вкладке `Legacy-карта`.
- Role matrix для опасных API: admin/game master/moderator/player.

## Important But Can Ship After Beta

- Полноценные кланы.
- Турнирная сетка, matchmaking и spectator/replay UI.
- Визуальный replay viewer с пошаговым проигрыванием.
- Полный перенос всех редких event NPC.
- Расширенные эффекты всех редких held items.
- Валютные объекты в Комиссионке.
- Полная production telemetry/metrics.
- CI с browser regression на desktop/mobile.
- Asset registry checker как обязательный CI gate.
- Большая балансировка economy/drop/exp после первых данных beta.

## Manual QA Plan Перед Beta

### Блок 1: Core Login/FPE, 30-60 минут

- Login/logout/refresh.
- Новый игрок.
- Стартовая карта.
- NPC.
- Starter quest.
- Первый бой.
- Quest Journal.

### Блок 2: PvE, 60 минут

- 10+ боёв подряд.
- Ловля.
- Побег.
- Предметы.
- Награды.
- Primal/Mega отдельно.
- Double-click/stress actions.

### Блок 3: PvP, 60 минут

- Две сессии Tacos/NIGA.
- Invite/accept/reject/timeout.
- 6x6.
- Switch/items/refresh/reconnect.
- Surrender/history/replay.

### Блок 4: Economy, 60 минут

- Inventory.
- Held items.
- Gift boxes.
- Commission listing/buy/cancel/expire.
- Market admin logs.
- Economy Guard alerts.

### Блок 5: Social/UI, 30-60 минут

- Trainer Card.
- Settings.
- Friends/social buttons.
- Mail.
- Notifications.
- Bug Reporter.

### Блок 6: Mobile, 30-60 минут

- `/game` on small width.
- Battle UI.
- Quest Journal.
- Inventory.
- Commission.
- Trainer Card.
- Mail.
- Dropdowns/popups/touch scroll.

### Блок 7: Admin/GM, 30-60 минут

- Health dashboard.
- QA Seed Tools.
- Commission admin.
- Economy Guard.
- Bug reports.
- Battle replays.
- Content wizard.

## Smoke Commands

Использовать PHP из OpenServer:

```powershell
D:\OSPanel\modules\php\PHP_8.1\php.exe tools\migration_status.php --record-status
D:\OSPanel\modules\php\PHP_8.1\php.exe tools\db_integrity_smoke.php --json
D:\OSPanel\modules\php\PHP_8.1\php.exe tools\open_test_regression.php --profile=quick --login=Tacos --password=...
D:\OSPanel\modules\php\PHP_8.1\php.exe tools\open_test_regression.php --profile=full --login=Tacos --password=...
D:\OSPanel\modules\php\PHP_8.1\php.exe tools\http_smoke.php --login=Tacos --password=...
D:\OSPanel\modules\php\PHP_8.1\php.exe tools\pvp_qa_smoke.php --password=...
D:\OSPanel\modules\php\PHP_8.1\php.exe tools\commission_market_smoke.php
D:\OSPanel\modules\php\PHP_8.1\php.exe tools\commission_hardening_smoke.php --iterations=600
D:\OSPanel\modules\php\PHP_8.1\php.exe tools\breeding_qa_smoke.php --password=...
D:\OSPanel\modules\php\PHP_8.1\php.exe tools\quests_minimum_smoke.php
D:\OSPanel\modules\php\PHP_8.1\php.exe tools\fpe_quest_smoke.php
D:\OSPanel\modules\php\PHP_8.1\php.exe tools\location_npc_transport_smoke.php
D:\OSPanel\modules\php\PHP_8.1\php.exe tools\tournament_qa_smoke.php --password=...
D:\OSPanel\modules\php\PHP_8.1\php.exe tools\admin_gm_center_smoke.php --login=Tacos --password=... --forbidden-login=NIGA --forbidden-password=...
```

## Browser QA Checklist

- Открыть `/game`.
- Проверить console errors.
- Проверить network 4xx/5xx.
- Открыть:
  - Pokemon;
  - Inventory;
  - Pokemarket;
  - Commission;
  - Profile/Trainer Card;
  - Pokedex;
  - AttackDex;
  - Quests;
  - Mail;
  - Admin.
- Проверить close buttons/Esc/overlay click.
- Проверить mobile width около `390px`.
- Проверить отсутствие horizontal overflow.
- Проверить, что popup не перекрывают друг друга.

## Release Candidate Criteria

RC можно объявлять только если:

- Миграции clean.
- Integrity `P0=0`, `P1=0`; `WARN=0` или accepted documented WARN.
- Full smoke green after latest changes.
- Browser QA desktop/mobile done after latest changes.
- Не осталось P0/P1 bug reports.
- Known issues list обновлён.
- Backup создан и restore проверен.
- OpenServer/beta config documented.
- `PROJECT_CONTEXT.md`, `REWRITE_STATUS.md` и этот файл синхронизированы.

## Suggested Order From Now

1. Закрыть/документировать integrity WARN.
2. Прогнать свежий `open_test_regression --profile=full` после изменений 2026-06-05.
3. Пройти manual FPE/PvE/PvP/economy/social/mobile/admin.
4. Исправить найденные P0/P1.
5. Доделать или выключить недоготовые публичные фичи: clans, advanced tournaments, rare event NPC.
6. Повторить full smoke и browser QA.
7. Создать beta backup и release candidate note.
