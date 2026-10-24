# TASK-045 — канонические регионы Хоэнн, Синно, Юнова

## Статус

done

## Что сделано

- Добавлена миграция `database/migrations/2026_10_12_000001_canonical_regions.sql`.
- Добавлены 36 новых локаций в `build`:
  - Хоэнн: 12 локаций, id `30000..30011`, `town=5`.
  - Синно: 12 локаций, id `31000..31011`, `town=6`.
  - Юнова: 12 локаций, id `32000..32011`, `town=7`.
- Добавлены 216 региональных спавнов в `pokebuild`:
  - Хоэнн: 72 спавна, только `baseid 252..386`.
  - Синно: 72 спавна, только `baseid 387..493`.
  - Юнова: 72 спавна, только `baseid 494..649`.
- Легендарные покемоны не добавлены в спавны; в миграции оставлен комментарий-заготовка для событий:
  - Kyogre, Groudon, Rayquaza.
  - Dialga, Palkia, Giratina.
  - Reshiram, Zekrom, Kyurem.
- Добавлены 12 записей транспорта:
  - Паромы: Джотто <-> Хоэнн, Хоэнн <-> Синно, Синно <-> Юнова.
  - Самолёты: Канто <-> Хоэнн, Канто <-> Синно, Канто <-> Юнова.
- Добавлены 3 направления в `transport_flight_routes`: Хоэнн, Синно, Юнова.
- Добавлены 114 правил дропа в `admin_drop_rules`:
  - Расходники: Покебол 30%, Энергетик 20%, Фиолетовая конфета 5%.
  - Редкие камни/джекпоты: 6 правил по 1.5%.
  - `item_id=1` не добавлялся.
- Обновлён `include/data.world.php`:
  - Все новые регионы связны из стартового города.
  - Есть стыковка с существующим миром через `100` и порт Джотто `25`.
  - В новых регионах нет локаций с единственным выходом.

## Изменённые файлы

- `database/migrations/2026_10_12_000001_canonical_regions.sql`
- `include/data.world.php`
- `docs/agent-tasks/TASK-045-canonical-regions.report.md`

## Проверки

- Backup перед мутирующей операцией:
  - `php tools\beta_backup.php`
  - создан `storage/backups/pokemonchic_beta_20261010_232616.sql`
- PHP lint:
  - `php -l include\data.world.php` — OK.
- Миграции:
  - `php tools\migration_status.php --apply` — `total=86 applied=86 pending=0 dirty=0 failed=0`.
  - Повторный прогон `php tools\migration_status.php --apply` — `total=86 applied=86 pending=0 dirty=0 failed=0`.
- Спавны:
  - `php tools\spawns_validity.php` — неизвестные покемоны `0`, неизвестные локации `0`, неверные веса `0`.
  - Скрипт показывает `уровень вне 1..100 = 6`, но это старые строки `Игральная комната (20152)` с уровнями 300–400, не новые регионы.
  - Targeted-проверка новых регионов: `regional invalid level/weight=0`, `regional duplicate triples=0`.
  - `php tools\spawns_hygiene.php` — дублей `(building, baseid, lvl)` нет.
- Дроп:
  - `php tools\drops_audit.php` — несуществующих предметов `0`, суммы по локациям <= 100%; новые локации с редким дропом имеют сумму 56.5%.
  - Targeted-проверка: `regional coin drops=0`.
- Квестовые виды:
  - `php tools\quest_spawn_coverage.php` — `13/13` квестовых видов доступны.
- Региональная сводка:
  - `php tools\regions_recon.php` — появились `town=5/6/7` по 12 локаций; `transport_routes=16`, `transport_flight_routes=6`.
- Связность:
  - Хоэнн `30000`: `region reachable 12/12`, `deadends=0`.
  - Синно `31000`: `region reachable 12/12`, `deadends=0`.
  - Юнова `32000`: `region reachable 12/12`, `deadends=0`.
- Гейт:
  - `php tools\open_test_regression.php --profile=quick --login=Tacos --password=REDACTED_USE_SMOKE_PASSWORD`
  - `Passed: 25, failed: 0, skipped: 0`.

## Риски и спорное

- В живой БД уже были старые локации Хоэнна в диапазоне `101..158`. Новая работа не переиспользует их и добавляет канонический набор в отдельном диапазоне `30000+`, как просили в задаче.
- `tools\regions_recon.php` печатает предупреждение `Undefined variable $from–` на строке 37. Это существующий дефект инструмента вывода, не блокирует данные региона и не связан с новой миграцией.
- `tools\spawns_validity.php` показывает 6 старых уровней вне 1..100 в `Игральная комната (20152)`. Новые региональные спавны проверены отдельно и валидны.
