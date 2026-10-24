# TASK-010 — P0: подделка прогресса квестов

**Исполнитель:** Codex-агент · **Зона:** `src/Game/NpcDialogService.php`, `src/Repository/QuestRepository.php` · **Статус:** todo

## Проблема (подтверждена аудитом)

`src/Game/NpcDialogService.php:150-152` → `:992 questUpdateAction()`: строка `action` целиком приходит из тела POST `/api/location/npc/action` (`src/Controller/NpcApiController.php:50`) и уходит в `QuestRepository::createOrUpdate()` (`src/Repository/QuestRepository.php:50`) **без валидации**. Игрок отправляет `action=quest_update:<questId>:<process>:<completed>` и получает `gotov = 1` для любого квеста, после чего забирает награду у NPC.

Дополнительно: `src/Repository/QuestRepository.php:186` — `canStart()` возвращает `true`, если определения квеста нет (`$definition === []`), то есть принимается любой `questId` и в таблицу `quest` попадает мусор.

## Что сделать

1. Убрать возможность менять прогресс квеста из клиентского запроса. Прогресс обновляется только серверными путями (бой, предмет, NPC-скрипт, событие).
   - Минимальный вариант: удалить ветку `quest_update:` из обработчика клиентских действий NPC.
   - Если ветка нужна для отладки/QA — оставить её только при `running under QA flag` (например `$_ENV['QA_MODE'] === '1'`), и обязательно валидировать `questId` по `quest_definitions` и шаг по `quest_steps`.
2. `QuestRepository::canStart()`: для неизвестного квеста возвращать `false`, а не `true`.
3. Проверить, что все остальные серверные вызовы `createOrUpdate()` передают значения из серверных источников, а не из запроса (проверить `grep -rn "createOrUpdate(" src/`).

## Границы

- Нельзя: менять схему БД, трогать `views/**`, `public/js/**`, `public/css/**` (зона DSH-агента), другие репозитории.
- Если правка требует изменения контракта API — описать это в отчёте, не меняя фронтенд.

## Критерии приёмки

1. `php -l` на оба файла — чисто.
2. POST с `action=quest_update:...` больше не меняет прогресс: в отчёте показать проверку (curl/`Invoke-WebRequest` с сессией игрока Tacos) — до и после, с состоянием строки в `quest` для этого квеста.
3. `tools/quests_minimum_smoke.php` → 53/53 (или больше), `tools/fpe_quest_smoke.php` → 25/25.
4. `tools/open_test_regression.php --profile=quick` → 24/24.
5. `tools/db_integrity_smoke.php --json` → `P0=0, P1=0`.

## Доказательства

- Diff по двум файлам (суть, не целиком).
- Команда атаки до/после и её результат (значение `quest.gotov` / `quest.process`).
- Вывод всех четырёх проверок.
