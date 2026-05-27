# PokemonChic Current Project Context

Обновлено: 2026-05-27.

Этот файл - короткий источник правды для дальнейшей разработки. Если чат или старые ТЗ противоречат этому файлу, использовать этот файл и живой код.

## Что Больше Не Используем Как Рабочий Контекст

- Старые черновики, повторяющиеся промпты и ранние варианты механик из чата.
- `REWRITE.md` - исторический документ, не текущая карта работ.
- Старые QA-отчёты - только как архив багов, не как актуальную архитектуру.
- Legacy PHP-файлы - только как справочник поведения при переносе, не как основной слой.
- Серые legacy UI-макеты - только визуальная референция, новый UI должен жить в текущем игровом стиле.
- `tmp/`, локальные скриншоты, прототипы, debug/log txt - не считать частью продукта.

## Текущая Архитектура

- Front controller: `public/index.php`.
- Страницы: `views/*`.
- API/controllers: `src/Controller/*`.
- Данные: `src/Repository/*`.
- Игровая логика: `src/Game/*`.
- Основной игровой shell: `/game`, `views/game-start.php`, `public/js/*`, `public/css/*`.
- Админка/GM Center: `/game/admin`, `views/game-admin.php`, `public/js/admin-panel.js`, `public/css/admin-panel.css`.
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
- Последний статус миграций: `63/63`, `pending=0`, `dirty=0`, `failed=0`.
- Последний beta audit: `P0=0`, `P1=0`; `WARN` остаётся по историческим незавершённым rows в `battles`.
- Комиссионная лавка резервирует покемонов/яйца через `commission.reserve_user_id`, сейчас это аккаунт `Система`, а не живой игрок `id=3`.
- Safe Storage: `safe_storage_entries`, `safe_operation_rollbacks`, `safe_storage_logs`, `SafeStorageRepository`, `tools/safe_storage_smoke.php`.
- Safe Storage используется для возврата/резерва при сбоях commission, gift/inventory, rewards, breeding egg create и battle reward rollback-plan. Нормальные успешные операции пишут rollback-plan со статусом `recorded`, аварийные - `failed/open`.
- Background jobs: `background_job_runs`, `background_job_logs`, `BackgroundJobRepository`, `tools/background_jobs.php`, `tools/background_jobs_smoke.php`.
- Текущие jobs: `expire_market`, `pvp_timeouts`, `stuck_battles` (warning-only), `temporary_items`, `transport_flights` (scan), `event_cleanup`, `safe_storage_status`.
- Battle IDs: `battle_id_sequence` резервирует уникальные положительные `battles.id` для PvE/PvP/Boss на старой схеме без `AUTO_INCREMENT`; duplicate positive ids считаются P1.
- DB Integrity: `data_integrity_logs`, `IntegrityRepository`, `tools/db_integrity_smoke.php`. `--fix-safe` чинит только очевидно безопасное: `items_users.count<=0`, orphan held rows, finished active transformations, expired PvP requests, `battles.id<=0` и stale user battle flags.
- Текущие integrity WARN: orphan legacy owners и исторические unfinished battles; P0/P1 после safe-fix нет.
- Дампы и backup-файлы не коммитить: `storage/backups/` в `.gitignore`.

## Основные Рабочие Системы

- Auth: регистрация без обязательной почты, password reset, techwork, роли через users/admin repository.
- Game state/location: `/api/game/state`, `/api/map/move`, `LocationStateService`, `MapMoveService`.
- NPC/quests: `/api/location/npc`, `/api/quests`, `NpcDialogService`, `QuestRepository`, `quest_definitions`, `quest_steps`.
- PvE/PvP battle: `/api/battle/pve/*`, `/api/battle/pvp/*`, `BattleEngineService`, `BattleRepository`; активный бой выбирается строго по флагу `pve/pvp` и `batl_tip`, чтобы PvE catch не попадал в PvP-row при legacy-дублях id.
- Battle transformations: Mega/Primal через `BattleTransformationCatalog`; формы боевые, не постоянные в `pok_user.basenum`.
- Inventory/items: `/api/inventory/page`, `/battle`, `/equip`, `/unequip`, `/use-target`, `/open-gift`, `InventoryRepository`.
- Held items metadata: `item_gameplay_metadata`; эффекты могут быть `implemented`, `visual_only`, `todo`.
- Pokemon/team/daycare: `/game/pokemon`, `/api/pokemon/*`, active team отдельно от питомника.
- Breeding/eggs: `/api/pokemon/breeding/*`, `/api/eggs`, `BreedingRepository`, `EggRepository`.
- Markets:
  - `ItemMarketRepository` - старый совместимый item market.
  - `PokemonMarketRepository` - совместимый рынок покемонов поверх legacy.
  - `CommissionMarketRepository` - текущий единый рынок item/pokemon/egg.
- Commission admin: `/api/admin/commission/*`, `market_lots`, `market_logs`, `market_return_storage`, `market_deal_reviews`.
- Trainer Card: `/api/profile/card`, модальное окно на текущей странице, gym badges через reward-flow.
- Events/buffs: `/api/events`, `/api/events/active`, `GameEventRepository`.
- Transport: `/api/transport/*`, самолет/пароход/рейсы.
- Bosses: `/api/bosses/start`, `/api/admin/bosses`, `BossRepository`.
- Dex: `/api/dex/pokemon`, `/api/dex/attacks`, `DexRepository`.
- Chat/friends/messages/notifications: новые JSON API, legacy только источник данных там, где ещё не перенесено.

## Используемые Модели Данных

- Users/session: `users`, `site_settings`, `game_notifications`.
- Pokemon: `pok_user`, `attac_my_poke`, base pokedex tables, form metadata.
- Items: `items`, `items_users`, `item_gameplay_metadata`.
- Battles: `battles`, battle state/log/archive tables, PvP request tables.
- Quests: `quest`, `quest_definitions`, `quest_steps`.
- Eggs/breeding: `eggs`, `pokemon_breeding_rules`, `pokemon_breeding_requests`.
- Markets: `market_lots`, `market_logs`, `market_return_storage`, `market_deal_reviews`; legacy `auction_items`/`rinok_poke` только compat/import.
- Safe storage: `safe_storage_entries`, `safe_operation_rollbacks`, `safe_storage_logs`.
- Trainer Card rewards: `user_gym_badges`, medals/reward tables.
- Events/bosses/transport: current migration tables in `database/migrations`.
- Background jobs: `background_job_runs`, `background_job_logs`.
- Integrity logs: `data_integrity_logs`.

## Актуальные UI-Системы

- `/game` - основной экран и overlays.
- `/game/admin` - рабочий GM Center; legacy-карта должна быть только отдельной вкладкой.
- `/game/commission` и overlay на `/game` - новая комиссионная лавка.
- `/game/items` - новый инвентарь.
- `/game/pokemon` - команда, питомник, breeding UI.
- `/game/eggs`, `/game/quests`, `/game/events`, `/game/market/pokemon` - новые страницы модулей.
- Trainer Card - модальное окно, не отдельная legacy-страница.

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
- `tools/safe_storage_smoke.php`
- `tools/background_jobs_smoke.php`
- `tools/db_integrity_smoke.php`
- `tools/prepare_qa_teams.php`

Последняя Phase 2 проверка: PvE catch `57/57`, PvE finish/rewards/ack `58/58`, PvP Tacos/NIGA `126/126`, Commission `24/24`, background jobs `9/9`, safe storage `7/7`, integrity `P0=0/P1=0/WARN=4`.

## Правило Для Следующих Задач

1. Сначала читать этот файл.
2. Затем смотреть живой код и БД.
3. `REWRITE_STATUS.md` использовать как журнал статусов.
4. Не тащить старые чаты и дублирующиеся ТЗ в рабочий контекст.
5. Не удалять legacy/архивные файлы без явной причины; помечать как historical/compat.
