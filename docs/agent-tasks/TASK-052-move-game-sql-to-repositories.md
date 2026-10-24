# TASK-052 — вынести SQL из `src/Game` в репозитории

**Исполнитель:** ChatGPT (веб-канал) · **Зона (явно разрешена):** `src/Game/**`, `src/Repository/**` · **Приоритет:** P1 — латентные падения класса HY093 на живых путях

**Формат сдачи:** веб-канал в репозиторий не пишет. Верни **unified diff или пофайловую замену целиком** и отдельно перечисли проверки.
**База:** гейт `quick` на момент выдачи — **25/25** (`Passed: 25, failed: 0, skipped: 0`, `docs/agent-tasks/TASK-quest161-162.report.md`). Не занижай.

---

## Причина

Правило проекта (`PROJECT_CONTEXT.md`): данные — в `src/Repository`, логика — в `src/Game`. Сейчас оно соблюдено в одну сторону: в `src/Controller` SQL **ноль**, в `src/Repository` — сотни вызовов, а в `src/Game` осталось **69** прямых запросов. Именно в этих местах рождался класс дефектов HY093 (дублированный именованный параметр при `PDO::ATTR_EMULATE_PREPARES => false`), который уже дважды ломал проект: `src/Game/StalePveBattleReaper.php` (`:pvp_user`) и `src/Repository/InventoryRepository.php` (`:take`). Пока SQL живёт в сервисах, аудит `tools/pdo_param_audit.php` покрывает их хуже, а ревью запросов распылено по логике.

## Что замерено сейчас

Замер по `src/Game`: **69** вхождений `$this->db->prepare(` / `->query(` / `->exec(`.

| Файл | Вхождений |
|---|---|
| `src/Game/WildEncounterService.php` | 27 |
| `src/Game/NpcDialogEventTrait.php` | 22 |
| `src/Game/NpcDialogService.php` | 16 |
| `src/Game/StalePveBattleReaper.php` | 4 |

Что ещё важно и что нельзя потерять при переносе:

* `WildEncounterService` и `StalePveBattleReaper` держат **только** `PDO` (`private PDO $db`), `NpcDialogService` держит `PDO` плюс репозитории (`NpcDialogService.php:46-55`), а `NpcDialogEventTrait` работает с `$this->db` класса-хоста.
* `StalePveBattleReaper` использует не только SQL: `$this->db->inTransaction()` в `reap()` (`StalePveBattleReaper.php:23`). Это не запрос, но при переносе SQL проверка транзакции должна остаться рабочей — её поведение закреплено смоуком (`LogicException`, если транзакции нет и управление выключено).
* **DI-контейнера в проекте нет.** Зависимости собираются вручную в `public/index.php:107-191`; комментарий в строке 107 прямо это фиксирует: «Собираем зависимости вручную. Позже это можно заменить DI-контейнером». Новый контейнер не изобретать.
* Точки сборки этих трёх сервисов, которые обязаны продолжать работать без правок (иначе ты выйдешь из зоны):
  * `public/index.php:152` — `new WildEncounterService($db)`;
  * `public/index.php:154` — `new NpcDialogService($db, $locations, $quests, $inventory, $pokemonRepository, $locationContent, $rewardRepository)`;
  * `tools/fpe_quest_smoke.php:51-52`, `tools/legacy_core_qa_smoke.php:42`, `tools/location_npc_transport_smoke.php:48-49`, `tools/npc_runtime_audit_smoke.php:40`, `tools/quests_minimum_smoke.php:37`.

## Что сделать

1. Перенести запросы в репозитории **с сохранением SQL и семантики**:
   * ипподром (`src/Game/NpcDialogEventTrait.php`) → новый `src/Repository/HippodromeRepository.php`;
   * `WildEncounterService` → `src/Repository/WildEncounterRepository.php` (либо расширение `BattleRepository`, если так честнее — реши и обоснуй);
   * `StalePveBattleReaper` → `BattleRepository`;
   * оставшееся в `NpcDialogService` → профильные репозитории (`UserRepository`, `QuestRepository`, `PokemonRepository`).
2. Работа **механическая**: SQL не переписывать, поведение не менять, новые параметры не выдумывать, порядок запросов и условия сохранять буквально. Единственное разрешённое изменение SQL — разведение дублированного именованного параметра, если аудит найдёт такое в переносимом запросе (и это отдельным пунктом в отчёте).
3. Проводка зависимостей — **внутри зоны**. Так как `public/index.php` и `tools/**` в зону не входят, конструкторы обязаны остаться совместимыми с текущими вызовами: либо репозиторий создаётся внутри сервиса из уже имеющегося `$db`, либо принимается необязательным параметром со значением по умолчанию из этого же `$db`. Выбери один вариант, обоснуй в отчёте. Если считаешь, что без правки `public/index.php:152/154` или пяти вызовов в `tools/**` сделать честно нельзя — **не правь их**, а верни вопрос владельцу отдельным пунктом отчёта.
4. Для `StalePveBattleReaper` сохранить проверку `inTransaction()` и её исключение; перенос SQL не должен превращать смоук `tools/pve_stale_battle_reaper_smoke.php` в проверку копии SQL.
5. Итог: в `src/Game` не остаётся ни одного `$this->db->prepare/query/exec`. `src/Game` может продолжать держать `PDO` для транзакций и `inTransaction()`, но не для запросов.
6. Ничего не удалять: старые приватные методы, из которых ушёл SQL, либо удаляются вместе с телом запроса, либо остаются как обёртки. Мёртвых запросов-дублей после переноса быть не должно — проверь поиском по исходному SQL.

## Границы и запреты

* Менять можно только `src/Game/**` и `src/Repository/**`. Всё остальное — вне зоны, включая `public/index.php`, `tools/**`, `views/**`, `public/**`, `config/**`, `database/**`, `include/**`.
* Не менять сигнатуры публичных методов, которые уже вызывают контроллеры, и имена их параметров.
* Не менять схему БД, не добавлять и не удалять миграции.
* Не менять формат успешных ответов API.
* Не коммитить, не пушить, не удалять файлы, не выполнять `git checkout -- .`, `git reset --hard`, `git clean -fd`.
* Не запускать мутирующие смоуки параллельно с другими агентами (`AGENTS.md:81`).

## Приёмка

1. `php -l` по каждому изменённому и каждому новому PHP-файлу:
   ```powershell
   Set-Location 'E:\OSPanel\domains\pokemonchic.com'
   & 'E:\OSPanel\modules\php\PHP_8.1\php.exe' -l src\Repository\HippodromeRepository.php
   ```
2. **Полный гейт, вывод дословно** (нужно **25/25**, не ниже):
   ```powershell
   Set-Location 'E:\OSPanel\domains\pokemonchic.com'
   & 'E:\OSPanel\modules\php\PHP_8.1\php.exe' tools\open_test_regression.php --profile=quick --login=Tacos --password=REDACTED_USE_SMOKE_PASSWORD
   ```
   Если время позволяет — `--profile=full` (историческая норма **28/28**), с кодом возврата.
3. Аудит параметров — обе команды, вывод дословно:
   ```powershell
   Set-Location 'E:\OSPanel\domains\pokemonchic.com'
   & 'E:\OSPanel\modules\php\PHP_8.1\php.exe' tools\pdo_param_audit.php --self-test
   & 'E:\OSPanel\modules\php\PHP_8.1\php.exe' tools\pdo_param_audit.php --path=src
   ```
   Норма: `--self-test` → `prepare_checked=3, duplicates=1`, код 0; аудит `src/**` → `duplicates=0`, код 0 (историческая база: `prepare_checked=172`). **Не выдавай зелёный аудит за «проверено всё»**: запросы с динамически собираемым SQL в покрытие не попадают.
4. Замер остатка — должен быть нулём, вывод приложить по файлам:
   ```powershell
   Set-Location 'E:\OSPanel\domains\pokemonchic.com'
   Select-String -Path 'src\Game\*.php' -Pattern '\$this->db->(prepare|query|exec)\(' |
     Group-Object Filename | ForEach-Object { '{0}: {1}' -f $_.Name, $_.Count }
   ```
5. Смоуки путей, которые ты трогал (каждый — вывод дословно):
   ```powershell
   Set-Location 'E:\OSPanel\domains\pokemonchic.com'
   & 'E:\OSPanel\modules\php\PHP_8.1\php.exe' tools\fpe_quest_smoke.php
   & 'E:\OSPanel\modules\php\PHP_8.1\php.exe' tools\quests_minimum_smoke.php
   & 'E:\OSPanel\modules\php\PHP_8.1\php.exe' tools\location_npc_transport_smoke.php
   & 'E:\OSPanel\modules\php\PHP_8.1\php.exe' tools\npc_runtime_audit_smoke.php
   & 'E:\OSPanel\modules\php\PHP_8.1\php.exe' tools\pve_stale_battle_reaper_smoke.php
   & 'E:\OSPanel\modules\php\PHP_8.1\php.exe' tools\legacy_core_qa_smoke.php
   ```
   Историческая база: `fpe_quest_smoke` 25/0, `quests_minimum_smoke` 53/0, `pve_stale_battle_reaper_smoke` 10/10. Ни один результат не должен стать ниже.
6. Сверка «SQL не переписан»: для каждого перенесённого запроса покажи пару «файл:строка до — файл:строка после» с одинаковым телом. Отдельно перечисли запросы, где тело всё же изменилось, и почему.
7. `git diff --stat` и `git status --short` (только свои файлы, без лишних).

## Отчёт

`docs/agent-tasks/TASK-052-move-game-sql-to-repositories.report.md`: изменённые и новые файлы, таблица «запрос — куда переехал», команды, **дословный вывод** каждой проверки, `git diff --stat`, риски и то, что осталось.

Требование к каналу: **верни unified diff или пофайловую замену целиком, отдельно перечисли проверки.** Не коммить, не пушить.
