# PokemonChic Current Project Context

Обновлено: 2026-06-05.

Этот файл - короткий источник правды для дальнейшей разработки. Если чат или старые ТЗ противоречат этому файлу, использовать этот файл и живой код.

## Что Больше Не Используем Как Рабочий Контекст

- Старые черновики, повторяющиеся промпты и ранние варианты механик из чата.
- `REWRITE.md` - исторический документ, не текущая карта работ.
- Старые QA-отчёты - только как архив багов, не как актуальную архитектуру.
- Legacy PHP-файлы - только как справочник поведения при переносе, не как основной слой.
- Новые функции нельзя строить на legacy как на runtime-источнике. Если модуль ещё читает legacy-конфиг, это считается долгом переноса, а не финальной архитектурой.
- Серые legacy UI-макеты - только визуальная референция, новый UI должен жить в текущем игровом стиле.
- `tmp/`, локальные скриншоты, прототипы, debug/log txt - не считать частью продукта.

## Текущая Архитектура

- Front controller: `public/index.php`.
- Страницы: `views/*`.
- API/controllers: `src/Controller/*`.
- Данные: `src/Repository/*`.
- Игровая логика: `src/Game/*`.
- Основной игровой shell: `/game`, `views/game-start.php`, `public/js/*`, `public/css/*`.
- `/game` view больше не держит основной inline-runtime: игровая логика вынесена в `public/js/game-start-runtime.js`, PHP передаёт только JSON-конфиг `gameRuntimeConfig`; чатовые стили вынесены в `public/css/chat.css`.
- Админка/GM Center: `/game/admin`, `views/game-admin.php`, `public/js/admin-panel.js`, `public/css/admin-panel.css`, `/api/admin/gm-center`, `/api/admin/qa-seed-tools`.
- Простая рабочая админка: вкладка `Мастер контента` в `/game/admin`, отдельные файлы `public/js/admin-content-wizard.js` и `public/css/admin-content-wizard.css`. Это верхний слой для владельца игры: создание предметов, подготовка квестов/NPC и карта ассетов без ручного поиска таблиц.
- `AdminRepository` уже частично разнесён на traits: commission, GM Center, QA Seed Tools, events/tournaments/medals; следующие безопасные кандидаты - pokemon grant, moderation/users, legacy-map helpers.
- Карта legacy -> новый слой: `src/Game/GameRoutes.php`.
- Миграции: `database/migrations/*.sql`; новые изменения применять в живую БД и оставлять идемпотентными.

## Beta Foundation / Защита Данных

- Рабочая beta-ветка: `codex/beta-foundation`.
- Реестр миграций: `schema_migrations`; снимки статуса: `migration_status`.
- CLI:
  - `tools/migration_status.php --record-status` - статус миграций;
  - `tools/migration_status.php --baseline` - только для фиксации уже существующей живой базы;
  - `tools/migration_status.php --apply` - применить pending миграции;
  - `tools/beta_backup.php` - дамп БД в `storage/backups/`;
  - `tools/beta_data_audit.php` - аудит P0/P1/WARN;
  - `tools/beta_data_audit.php --fix-safe` - только безопасные исправления: merge одинаковых item stacks, expire due commission/PvP.
  - `tools/background_jobs.php --status|--dry-run|--job=<name>` - ручной запуск фоновых задач;
  - `tools/db_integrity_smoke.php [--fix-safe]` - integrity/anti-dupe проверки.
- Последний статус миграций: `78/78`, `pending=0`, `dirty=0`, `failed=0`.
- Последний beta audit: `P0=0`, `P1=0`; `WARN` остаётся по историческим незавершённым rows в `battles`.
- Комиссионная лавка резервирует покемонов/яйца через `commission.reserve_user_id`, сейчас это аккаунт `Система`, а не живой игрок `id=3`; дополнительно ведётся ledger `market_reserved_objects`.
- Safe Storage: `safe_storage_entries`, `safe_operation_rollbacks`, `safe_storage_logs`, `SafeStorageRepository`, `tools/safe_storage_smoke.php`.
- Safe Storage используется для возврата/резерва при сбоях commission, gift/inventory, rewards, breeding egg create и battle reward rollback-plan. Нормальные успешные операции пишут rollback-plan со статусом `recorded`, аварийные - `failed/open`.
- Reward pipeline: `reward_transactions`, `reward_transaction_entries`, `RewardRepository::grantPipeline()`, `tools/reward_pipeline_smoke.php`. Подарки и `grantItems()` идут через единый лог начислений, push-уведомления и rollback/safe-storage failure record.
- Notifications + Mail: `game_notifications` хранит `sender_id/source_type/source_id/email_status/email_sent_at`, системный отправитель берётся из аккаунта `Система`, `MessageRepository::sendSystem()` пишет внутренние письма в `sends`, `Mailer` поддерживает `mail/smtp/log` transport и delivery-log в `mail_delivery_logs`.
- Event notifications: `GameEventRepository` создаёт одноразовые push-уведомления по активным событиям через `game_event_notification_receipts`, без дублей при повторных `/api/events/active`.
- Background jobs: `background_job_runs`, `background_job_logs`, `BackgroundJobRepository`, `tools/background_jobs.php`, `tools/background_jobs_smoke.php`.
- Текущие jobs: `expire_market`, `pvp_timeouts`, `stuck_battles` (warning-only, считает stale по `COALESCE(NULLIF(times,0), NULLIF(time,0), 0)`), `temporary_items`, `transport_flights` (scan), `event_cleanup`, `safe_storage_status`, `economy_guard`.
- Economy Guard: `economy_guard_alerts`, `EconomyGuardRepository`, `tools/economy_guard_smoke.php`, background job `economy_guard`, admin API `/api/admin/economy-guard/alerts|scan|review`; ловит suspicious trades, massive money gain, transfer abuse и fake market prices.
- Battle IDs: `battle_id_sequence` резервирует уникальные положительные `battles.id` для PvE/PvP/Boss на старой схеме без `AUTO_INCREMENT`; duplicate positive ids считаются P1.
- Battle Replay: `battle_replays`, `battle_replay_events`, `BattleReplayRepository`, `/api/battle/replay`, `/api/admin/battle-replays`; пишет snapshots, round logs, actions, random rolls и damage audit для QA/спорных боёв.
- Admin/GM Center health: `/api/admin/gm-center`, `AdminGmCenterRepositoryTrait`, `/game/admin` dashboard; единый payload для health cards, active/stuck battles, market moderation, replay tools, background jobs, migration status, safe storage, moderation summary и unified logs.
- QA Seed Tools: `AdminQaSeedRepositoryTrait`, `/api/admin/qa-seed-tools`, `/api/admin/qa-seed-tools/run`, блок в GM Center для `setup test accounts`, `give teams`, `give items`, `reset market`, `run smokes`; действия пишутся в `admin_audit_log` как `qa_seed.*`.
- Bug Reporter: `bug_reports`, `bug_report_events`, `BugReportRepository`, `/api/bug-reports`, `/api/admin/bug-reports`, кнопка `Report bug` в `/game`; report прикладывает page/game state, battle id/battle snapshot, client logs и tail server logs. GM Center показывает open/critical reports, вкладка `Bug Reports` даёт фильтры, inspector и смену статуса.
- DB Integrity: `data_integrity_logs`, `IntegrityRepository`, `tools/db_integrity_smoke.php`. `--fix-safe` чинит только очевидно безопасное: `items_users.count<=0`, orphan held rows, finished active transformations, expired PvP requests, `battles.id<=0`, stale user battle flags, `hp_my > hp_max` и реальные `pok_user` с невозможным level/stat через пересчёт по `poke_base + IV/EV + har`.
- Текущий integrity smoke после safe-fix: `P0=0`, `P1=0`, `WARN=4` (`items.orphan_user=38`, `pokemon.invalid_owner=63`, `eggs.invalid_owner=1`, `battle.active_unfinished=208`).
- Дампы и backup-файлы не коммитить: `storage/backups/` в `.gitignore`.

## Основные Рабочие Системы

- Auth: регистрация без обязательной почты, password reset, techwork, роли через users/admin repository.
- Game state/location: `/api/game/state`, `/api/map/move`, `LocationStateService`, `MapMoveService`.
- NPC/quests: `/api/location/npc`, `/api/quests`, `/api/quests/track`, `NpcDialogService`, `QuestRepository`, `quest_definitions`, `quest_steps`, `user_quest_tracking`.
- NPC migration rule: all quest NPC currently listed in `config/location_content.php` are handled by the new NPC engine without direct legacy PHP execution. Story NPC coverage includes Алабастия, Оук, Билли, Стив, Кэрол, Старая женщина, Цветочный прилавок, Амира, Айрен, Articuno, Гарен and праздничный зал. Event/tournament NPC helpers already started moving into `NpcDialogEventTrait`. Legacy `include/rooms/npc/*.php` remains reference-only.
- Owner-facing project map: `PROJECT_MAP_RU.md` is the short practical guide for where to add items, pictures, NPC, quests, UI and code. `PROJECT_STRUCTURE_RU.md` remains the deeper technical map and refactor reference.
- First Player Experience: квесты `1 -> 101 -> 102 -> 103` ведут игрока через Оука, стартера, Дорогу 1, первый PvE бой, Вертанию и первый транспорт; smoke `tools/fpe_quest_smoke.php`.
- Quests minimum: стартовые/battle/reward/cooldown/repeatable сценарии покрыты `tools/quests_minimum_smoke.php`; ежедневный Metapod-квест нельзя перезапустить во время cooldown, после истечения он снова становится `available`; цепочка `Рассказ Спайка` покрыта полным NPC-flow `Спайк -> Старая женщина -> Амира -> Айрен -> Articuno -> Айрен` с reward-flow; журнал квестов отдаёт progress/reward_view/tracked/navigation state.
- NPC + Locations: routes/map transitions/wild encounters/blocked routes/transport NPC/ship/flight smoke покрыты `tools/location_npc_transport_smoke.php`.
- PvE/PvP battle: `/api/battle/pve/*`, `/api/battle/pvp/*`, `BattleEngineService`, `BattleRepository`; активный бой выбирается строго по флагу `pve/pvp` и `batl_tip`, чтобы PvE catch не попадал в PvP-row при legacy-дублях id. PvE mutating actions защищены per-battle mutex от fast double-click, а `BattleRepository::findPokemon()` нормализует невозможные player stats в runtime, чтобы старые грязные `pok_user` не ломали damage/Primal/Mega displays.
- Battle replay viewer: игроки открывают свой replay через `/api/battle/replay`; админы смотрят список и детали через вкладку `Повторы боёв` в GM Center.
- Bug Reporter UI: игрок отправляет report прямо из нижней панели `/game`; mobile actionbar должен быть высотой `auto`, чтобы quick controls, главное меню и system-status не перекрывали друг друга.
- GM Center dashboard: админы видят health/status rows, QA Seed Tools, активные и зависшие бои, market moderation, replay summary, jobs/migrations/safe storage и последние логи на первой вкладке `/game/admin`; smoke `tools/admin_gm_center_smoke.php`.
- Battle transformations: Mega/Primal через `BattleTransformationCatalog`; формы боевые, не постоянные в `pok_user.basenum`. Проверка `tools/battle_transformation_stats_smoke.php` подтверждает sane stats: Primal Kyogre `342 HP / 354 Atk / 431 SAtk`, Primal Groudon `342 HP / 479 Atk`, Mega Rayquaza `352 HP / 479 Atk`.
- Inventory/items: `/api/inventory/page`, `/battle`, `/equip`, `/unequip`, `/use-target`, `/open-gift`, `InventoryRepository`.
- Held items metadata: `item_gameplay_metadata` is source of truth for `item_target_rules`; `equip_held` replacement returns old held item to inventory. Effects can be `implemented`, `visual_only`, `todo`.
- Gifts/rewards: `/api/inventory/open-gift` блокирует gift row через `FOR UPDATE`, считает loot table, начисляет награды через `RewardRepository::grantPipeline()` и списывает подарок только после успешного начисления.
- Pokemon/team/daycare: `/game/pokemon`, `/api/pokemon/*`, active team отдельно от питомника.
- Breeding/eggs: `/api/pokemon/breeding/*`, `/api/eggs`, `BreedingRepository`, `EggRepository`.
- Markets:
  - `ItemMarketRepository` - старый совместимый item market.
  - `PokemonMarketRepository` - совместимый рынок покемонов поверх legacy.
  - `CommissionMarketRepository` - текущий единый рынок item/pokemon/egg.
- Commission admin: `/api/admin/commission/*`, `market_lots`, `market_logs`, `market_return_storage`, `market_deal_reviews`; отдельная вкладка `Economy Guard` показывает автоалерты экономики и даёт ручной review.
- Trainer Card: `/api/profile/card`, модальное окно на текущей странице, social viewing/actions, held items и gym badges через reward-flow.
- Trainer Card gym flow: `gym_badge_battle_rules` связывает реальный PvE gym-бой с `gym_badges`; `BattleEngineService` выдаёт значок через `RewardRepository::grantGymBadge(..., sourceType=gym_battle)` после победы, пишет battle log/replay action и не дублирует уже выданный badge.
- Events/buffs: `/api/events`, `/api/events/active`, `GameEventRepository`.
- Notifications/mail: `/api/notifications`, `RewardRepository::notify()`, `MessageRepository::sendSystem()`, `Mailer`; SMTP задаётся через `.env`, smoke использует `MAIL_TRANSPORT=log`.
- Bug reports: `/api/bug-reports` принимает игровые отчёты с state/battle/log attachments; `/api/admin/bug-reports` и `/status` доступны только админам.
- Transport: `/api/transport/*`, самолет/пароход/рейсы.
- Bosses: `/api/bosses/start`, `/api/admin/bosses`, `BossRepository`.
- Tournaments: `/game/tournaments`, `/api/tournaments`, `TournamentRepository`; player-flow покрывает расписание, дедлайн регистрации, куратора, взнос, лимиты, вход/выход с арены, одноразовые награды и медали. NPC-куратор и нижнее меню `/game` ведут в новый модуль, admin CRUD хранит `registration_deadline_at`, `arena_exit_location_id`, `reward_json`.
- Dex: `/api/dex/pokemon`, `/api/dex/pokemon/show`, `/api/dex/attacks`, `/api/dex/attack/show`, `DexRepository`, `public/js/dex-overlay.js`; battle forms показывают свои статы/способности/спрайты, но learnset/egg/hidden moves/ареалы наследуют от базового dex-id. Smoke: `tools/dex_attackdex_smoke.php`.
- Chat/friends/messages/notifications: новые JSON API, legacy только источник данных там, где ещё не перенесено.

## Используемые Модели Данных

- Users/session: `users`, `site_settings`, `game_notifications`, `game_event_notification_receipts`.
- Pokemon: `pok_user`, `attac_my_poke`, base pokedex tables, form metadata.
- Items: `items`, `items_users`, `item_gameplay_metadata`.
- Rewards/notifications: `reward_transactions`, `reward_transaction_entries`, `game_notifications`, `mail_delivery_logs`.
- Battles: `battles`, battle state/log/archive tables, PvP request tables.
- Battle Replay: `battle_replays`, `battle_replay_events`.
- Quests: `quest`, `quest_definitions`, `quest_steps`, `user_quest_tracking`.
- Eggs/breeding: `eggs`, `pokemon_breeding_rules`, `pokemon_breeding_requests`.
- Markets: `market_lots`, `market_logs`, `market_return_storage`, `market_reserved_objects`, `market_deal_reviews`, `economy_guard_alerts`; legacy `auction_items`/`rinok_poke` только compat/import.
- Safe storage: `safe_storage_entries`, `safe_operation_rollbacks`, `safe_storage_logs`.
- Trainer Card rewards: `user_gym_badges`, `gym_badge_battle_rules`, medals/reward tables.
- Tournaments/medals: `admin_tournaments`, `admin_tournament_participants`, `admin_tournament_logs`, `admin_medals`, `admin_user_medals`.
- Events/bosses/transport: current migration tables in `database/migrations`.
- Background jobs: `background_job_runs`, `background_job_logs`.
- Integrity logs: `data_integrity_logs`.
- Bug reporter: `bug_reports`, `bug_report_events`.

## Актуальные UI-Системы

- `/game` - основной экран и overlays.
- `/game/admin` - рабочий GM Center + `Мастер контента`; legacy-карта должна быть только отдельной вкладкой, а обычная работа с контентом должна начинаться с мастера.
- `/game` Bug Reporter - кнопка `Report bug` открывает модалку, прикладывает state/battle/client logs/server logs и отправляет report без перезагрузки.
- `/game/commission` и overlay на `/game` - новая комиссионная лавка.
- `/game/items` - новый инвентарь.
- `/game/pokemon` - команда, питомник, breeding UI.
- `/game/quests` - overlay-журнал на `/game` плюс standalone fallback; список, поиск/фильтры, цели с progress bar, награды, tracked quest и mini-tracker.
- `/game/eggs`, `/game/events`, `/game/market/pokemon` - новые страницы модулей.
- `/game/tournaments` - игроковая страница турниров с регистрацией, взносом, ареной и наградами.
- Trainer Card - модальное окно на `/game`, не отдельная legacy-страница; клики по профилю/друзьям открывают overlay, API отдаёт `social` и `badgeSummary`; mobile open/profile-switch сбрасывает внутренний scroll, чтобы окно не открывалось с середины карточки.

## Комиссионная Лавка: Финальная V1

- Разрешено: предметы, held/evolution/ticket/TM/mega-primal/gift/craft items, покемоны, яйца.
- Запрещено: зелья, ягоды, quest/bound/temporary/blocked/equipped/in-use objects.
- Резерв:
  - items через списание/резерв в `market_lots`;
  - pokemon через `pok_user.users = commission.reserve_user_id`;
  - eggs через `eggs.users_egg = commission.reserve_user_id`.
- Покупка/отмена/истечение должны быть транзакционными.
- Safe return: `SafeStorageRepository` + совместимое зеркало `market_return_storage`.
- Logs: `market_logs`.
- Hardening: `locked_by/locked_at/lock_reason/lock_token` на `market_lots`, `lot.freeze` audit, `market_reserved_objects` для pokemon/egg, лимиты `commission.max_quantity_per_lot` и `commission.max_total_price`.
- Risk deals:
  - overpriced easy items/pokeballs, total 50m+, unit 10m+, big expensive stacks;
  - лог `risk.flagged`;
  - admin review в `market_deal_reviews`, кнопка “Проверено, сделка норм”.

## Тестовые Аккаунты

- `Tacos` - основной QA/admin пользователь.
- `NIGA` - второй игрок для PvP/two-session QA.
- `Система` - служебный аккаунт для системных/рыночных операций и будущих системных начислений.

## Smoke/QA Скрипты

- `tools/http_smoke.php`
- `tools/pvp_qa_smoke.php`
- `tools/legacy_core_qa_smoke.php`
- `tools/breeding_qa_smoke.php`
- `tools/commission_market_smoke.php`
- `tools/commission_hardening_smoke.php`
- `tools/economy_guard_smoke.php`
- `tools/safe_storage_smoke.php`
- `tools/background_jobs_smoke.php`
- `tools/db_integrity_smoke.php`
- `tools/battle_replay_smoke.php`
- `tools/inventory_held_items_smoke.php`
- `tools/reward_pipeline_smoke.php`
- `tools/fpe_quest_smoke.php`
- `tools/quests_minimum_smoke.php`
- `tools/location_npc_transport_smoke.php`
- `tools/admin_gm_center_smoke.php`
- `tools/bug_reporter_smoke.php`
- `tools/tournament_qa_smoke.php`
- `tools/open_test_regression.php` - sequential quick/full runner for open-test smoke matrix; runs mutating PvE/PvP/breeding checks in order, not in parallel.
- `tools/prepare_qa_teams.php`

Последняя Phase 7 / Release Candidate проверка: 2026-05-28 heartbeat `phase-7-full-manual-regression`, ветка `codex/beta-foundation`. `tools/open_test_regression.php --profile=quick --login=Tacos --password=...` прошёл `20/20`; targeted PvE: `pve_reward_idempotency_smoke.php` `9/9`, `battle_transformation_stats_smoke.php` OK, HTTP catch `56/56`, HTTP finish `58/58`; PvP: `pvp_qa_smoke.php` `126/126`, `battle_replay_smoke.php` `6/6`; economy/content/admin: commission `24/24`, hardening `36/36` на 120 итераций, breeding `55/55`, quests `29/29`, tournament `33/33`, Dex `27/27`, locations/NPC/transport `24/24`, Admin/GM `26/26`. Browser QA `/game`: главный экран без horizontal overflow, `Лавка` открывает overlay, категории без `Зелья/Ягоды`, `Мои лоты` переключается, `Квесты` открывают новый журнал overlay. Final smoke: `tools/open_test_regression.php --profile=full --login=Tacos --password=...` прошёл `24/24`, `failed=0`, `skipped=0`; миграции `74/74`, integrity `P0=0/P1=0/WARN=4`. Known issues без RC-blocker: WARN по старым orphan `items_users`/`pok_user`/`eggs` и 208 unfinished legacy battle rows; отдельный headless mobile Playwright-прогон не выполнен из-за отсутствующего локального `playwright-core`, но browser DOM QA desktop/current viewport зелёный.
Последняя Open Test Regression проверка: `tools/open_test_regression.php --profile=full --password=...` прошёл `24/24` блоков, `failed=0`, `skipped=0`; миграции `73/73`, integrity `P0=0/P1=0/WARN=4`, PvE catch `58/58`, PvE finish `59/59`, breeding `55/55`, PvP Tacos/NIGA `126/126`. Во время прогона исправлены race-safety хвосты: commission notifications и reward/items smoke больше не считают `MAX(id)+1` для auto-increment таблиц, а админская legacy-карта теперь скрыта вне вкладки `Legacy-карта`. Отчёт: `OPEN_TEST_REGRESSION_2026-05-27.md`; screenshots в `tmp/open-test-*.png` локальные и не считаются продуктом.
Последняя Trainer Card/social проверка: миграции `74/74`, `tools/trainer_card_gym_badge_flow_smoke.php` `8/8`, HTTP smoke `52/52`; browser QA `/game` mobile-width подтвердил: профиль Tacos открывается overlay без URL-перехода, клик по другу `NIGA` открывает `UID: 29` на текущей странице, `game.php` iframe/legacy popup отсутствуют, horizontal overflow и overlap секций отсутствуют.
Последняя декомпозиция крупных файлов: `views/game-start.php` сокращён до view + JSON config, runtime вынесен в `public/js/game-start-runtime.js`, чатовые стили вынесены в `public/css/chat.css`, admin events/tournaments/medals вынесены в `AdminEventsTournamentRepositoryTrait`. Проверено lint/syntax, HTTP smoke `52/52`, Admin GM Center `26/26`, Trainer Card gym flow `8/8`, browser QA `/game` inventory overlay после split.
Последняя Quest Journal UI проверка: миграции `73/73`, `tools/quests_minimum_smoke.php` `29/29`, `tools/fpe_quest_smoke.php` `25/25`; PHP lint `QuestRepository.php`, `QuestApiController.php`, `views/game-start.php`, `views/game-quests.php`, `views/components/quest-journal-panel.php`, `public/index.php`; JS syntax `public/js/quest-journal.js`. Browser QA: `/game` открывает квесты как overlay без перехода со страницы, `/game/quests` работает как standalone fallback, карточки/цели/награды/tracked quest/mini-tracker рендерятся, horizontal overflow на ширине `399px` отсутствует; актуальных console errors по quest UI нет.
Последняя Tournament QA проверка: миграции `72/72`, `tools/tournament_qa_smoke.php --password=...` `33/33`; покрыты `/game/tournaments`, `/api/tournaments`, CSRF-negative, расписание/timezone payload, куратор, взнос, списание/возврат, дедлайн, лимит участников, запрет дубля, нехватка средств, вход/выход с арены через `buildmy`, одноразовая награда, медаль и `admin_tournament_logs`; browser QA подтвердил рендер карточки турнира, кнопку регистрации, дедлайн, куратора, арену и отсутствие пустых состояний.
Последняя Phase 6 Bug Reporter проверка: миграции `71/71`, `tools/bug_reporter_smoke.php` `9/9`, `tools/admin_gm_center_smoke.php` `26/26`; browser QA `/game` подтвердил кнопку `Report bug`, открытие overlay, прикрепление state/battle id/client logs/server logs и отправку тестового report. Дополнительно исправлен mobile actionbar overlap: quick controls больше не перекрываются ссылкой `Лавка`.
Последняя Phase 6 QA Seed Tools проверка: `tools/admin_gm_center_smoke.php` `23/23`; `/api/admin/qa-seed-tools` отдаёт состояние `Tacos/NIGA/Система`, команд, item stacks, QA market lots и последних `qa_seed.*` audit logs; `/run` умеет `setup_accounts`, `give_teams`, `give_items`, `reset_market`, `run_smokes`; NIGA получает `403`, POST без CSRF даёт `419`; прямой запуск `give_items/reset_market/run_smokes` OK, `reset_market` снял 8 QA-лотов и вернул 8 объектов без pending returns; browser QA `/game/admin` подтвердил рендер блока, кнопку `setup test accounts`, обновление `#qaSeedResult` и отсутствие horizontal overflow.
Последняя Phase 4 NPC/Locations проверка: `tools/location_npc_transport_smoke.php` `24/24`, FPE `25/25`, Legacy Core `30/30`, HTTP `51/51`, migration status `69/69`, integrity `P0=0/P1=0/WARN=4`; покрыты routes, map transitions, blocked routes, wild encounter, NPC dialogs, ship travel и airplane boarding/early-exit block.
Последняя Phase 6 Admin/GM проверка: `tools/admin_gm_center_smoke.php` `23/23`, HTTP `52/52`, integrity `P0=0/P1=0/WARN=4`; `/api/admin/gm-center` отдаёт health cards, active/stuck battles, market moderation, replay tools, jobs, migrations, safe storage, moderation summary, QA Seed Tools и логи, NIGA получает `403`, CSRF-negative даёт `419`; browser QA `/game/admin` подтвердил GM rows, inspector detail, no console errors и отсутствие horizontal overflow на desktop/mobile.
Последняя Phase 4 quests проверка: `tools/quests_minimum_smoke.php` `23/23`, FPE `25/25`, Legacy Core `30/30`, HTTP `51/51`, migration status `69/69`, integrity `P0=0/P1=0/WARN=4`; исправлен repeatable/cooldown state machine в `QuestRepository` и NPC-start для ежедневного Metapod-квеста.
Последняя Phase 3 Economy Guard проверка: миграции `68/68`, Economy Guard `11/11`, Background Jobs `10/10`, Commission Hardening `36/36`, HTTP `51/51`, integrity `P0=0/P1=0/WARN=4`; добавлены `economy_guard_alerts`, background job `economy_guard`, admin API/GM вкладка и автообнаружение suspicious trades/massive money gain/transfer abuse/fake market prices.
Последняя Phase 3 commission hardening проверка: миграции `67/67`, `tools/commission_hardening_smoke.php --iterations=600` `36/36`, Commission `24/24`, Background Jobs `9/9`, Reward Pipeline `13/13`, HTTP `51/51`, integrity `P0=0/P1=0/WARN=4`; добавлены freeze audit, object reserve ledger, stricter lot limits, risk review auto-flag и return.pending logs.
Последняя Phase 3 gifts/reward проверка: Reward Pipeline `13/13`, Inventory/Held `16/16`, Legacy Core `30/30`, HTTP `51/51`, Commission `24/24`, Safe Storage `7/7`, integrity `P0=0/P1=0/WARN=4`; gift-box теперь пишет `reward_transactions/reward_transaction_entries`, отправляет push и имеет rollback/safe-storage failure record.
Последняя Phase 3 inventory/economy проверка: Inventory/Held `16/16`, HTTP `51/51`, Commission `24/24`, PvP `126/126`, Safe Storage `7/7`, integrity `P0=0/P1=0/WARN=4`; Browser QA подтвердил `/game` inventory overlay, targetable items, категории и held-item icons на `/game/pokemon`.
Последняя Phase 2 проверка: PvE catch `57/57`, PvE finish/rewards/ack `58/58`, PvP Tacos/NIGA `126/126`, PvP NIGA/Tacos `126/126`, Mega Rayquaza smoke `126/126`, Battle Replay `6/6`, Commission `24/24`, background jobs `9/9`, safe storage `7/7`, integrity `P0=0/P1=0/WARN=4`.
Последний Primal/Mega/weather факт: battle logs подтвердили `[TRANSFORM]` и `[WEATHER]` для Primal Kyogre/Primordial Sea, Primal Groudon/Desolate Land и Mega Rayquaza/Delta Stream; `battle_transformations.active=0` после ack, `pok_user.basenum` не хранит форму навсегда.

## Правило Для Следующих Задач

1. Сначала читать этот файл.
2. Затем смотреть живой код и БД.
3. `REWRITE_STATUS.md` использовать как журнал статусов.
4. Не тащить старые чаты и дублирующиеся ТЗ в рабочий контекст.
5. Не удалять legacy/архивные файлы без явной причины; помечать как historical/compat.
