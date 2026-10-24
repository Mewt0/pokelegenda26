# TASK-034 report: tools backup and PvE stale battle reaper smoke

## Статус

Статус: rework done, без коммита и push.

Выбран вариант 2 из review: `StalePveBattleReaper::reap()` получил необязательный режим работы внутри уже открытой транзакции. Smoke открывает транзакцию сам, создаёт синтетический stale PvE-бой, вызывает настоящий `$reaper->reap(false, 0, false)`, проверяет результат продукта и затем откатывает транзакцию в `finally`. `reapSyntheticBattle()` удалён полностью.

Дополнительно настоящий smoke поймал реальный дефект продукта: повторное имя PDO-параметра `:pvp_user` в `NOT EXISTS` давало `SQLSTATE[HY093]`. Исправлено на `:pvp_user_1` / `:pvp_user_2`.

## Изменённые файлы

- `tools/beta_backup.php` — ранее в TASK-034 исправлен поиск `mysqldump` на OSPanel в `E:/OSPanel` и через env roots.
- `src/Game/StalePveBattleReaper.php` — добавлен параметр `$manageTransactions = true`; при `false` reaper требует активную внешнюю транзакцию и не делает `begin/commit/rollback`; исправлен повтор PDO-параметра в PvP guard.
- `tools/pve_stale_battle_reaper_smoke.php` — smoke вызывает настоящий `reap(false, 0, false)`, проверяет `reap.uses_real_reaper`, rollback cleanup и отсутствие синтетического боя после отката.
- `docs/agent-tasks/TASK-034-tools-backup-and-smoke.report.md` — отчёт переписан; секция `Review` DSH сохранена ниже.

## Команды и вывод

### Backup перед live smoke

```text
> php tools\beta_backup.php
Backup created: E:\OSPanel\domains\pokemonchic.com/storage/backups/pokemonchic_beta_20261009_071428.sql (9.2 MB)
```

### PHP lint

```text
> php -l src\Game\StalePveBattleReaper.php
No syntax errors detected in src\Game\StalePveBattleReaper.php

> php -l tools\pve_stale_battle_reaper_smoke.php
No syntax errors detected in tools\pve_stale_battle_reaper_smoke.php
```

### Live reaper smoke

```text
> REAPER_SMOKE_ALLOW_LIVE=1 php tools\pve_stale_battle_reaper_smoke.php
[OK] dry_run.detects_synthetic_battle
[OK] busy.before_reap
[OK] reap.uses_real_reaper
[OK] reap.closes_battle
[OK] reap.clears_matching_pve_lock
[OK] reap.preserves_pvp_flag
[OK] repeat_is_idempotent
[OK] cleanup.rolls_back_transaction
[OK] cleanup.restores_user_state
[OK] cleanup.removes_synthetic_battle
PvE stale battle reaper smoke: 10/10 passed.
```

### Красная проверка smoke

Временно ломал условие в настоящем reaper update: `batl_tip = "pve"` -> `batl_tip = "pve_broken"`, запускал smoke, затем вернул код обратно.

```text
> REAPER_SMOKE_ALLOW_LIVE=1 php tools\pve_stale_battle_reaper_smoke.php
[OK] dry_run.detects_synthetic_battle
[OK] busy.before_reap
[OK] reap.uses_real_reaper
[FAIL] reap.closes_battle
[FAIL] reap.clears_matching_pve_lock
[OK] reap.preserves_pvp_flag
[FAIL] repeat_is_idempotent
[OK] cleanup.rolls_back_transaction
[OK] cleanup.restores_user_state
[OK] cleanup.removes_synthetic_battle
PvE stale battle reaper smoke: 7/10 passed.
```

### Open-test regression quick

Первый quick-прогон временно упал на FPE quest 102/103. Точечный `php tools\fpe_quest_smoke.php` сразу после этого прошёл `25/25`, повторный quick-прогон прошёл полностью:

```text
> php tools\open_test_regression.php --profile=quick --login=Tacos --password=REDACTED_USE_SMOKE_PASSWORD
Open-test regression (quick)
Passed: 24, failed: 0, skipped: 0, duration: 22104 ms

[OK] Migrations (0.25s) Migration status: total=79 applied=79 pending=0 dirty=0 failed=0
[OK] DB integrity (0.49s) P0=0, P1=0, WARN=1, ACCEPTED=4, OK=18, FIXED=0
[OK] HTTP API read smoke (2.83s) Result: 52 checks, 0 failures.
[OK] Legacy core migration smoke (0.32s) Result: 30 checks, 0 failures.
[OK] First player experience (0.39s) Result: 25 checks, 0 failures.
[OK] Quest chains minimum (0.35s) Result: 53 checks, 0 failures.
[OK] Locations/NPC/transport (0.28s) Result: 24 checks, 0 failures.
[OK] Inventory/Held items (0.29s) Inventory held-items smoke: 16/16 passed.
[OK] Reward pipeline (0.28s) [OK ] pipeline.failure.logged - status=failed | [OK ] pipeline.failure.rollback - rollback recorded | Reward pipeline smoke: 13/13 passed.
[OK] Safe storage (0.23s) Safe storage smoke: 7/7 passed.
[OK] PvE reward idempotency (0.31s) PvE reward idempotency smoke: 9/9 passed.
[OK] PvE action lock safety (6.29s) PvE action lock smoke: 14/14 passed.
[OK] Background jobs (0.28s) [OK ] economy_guard.dry - {"enabled":true,"dryRun":true,"created":0,"updated":0,"checks":{"suspicious_trades":0,"massive_market_gain":0,"massive_reward_gain":0,"coin_balance":5,"transfer_abuse":0,"fake_market_prices":0}} | Background jobs smoke: 10/10 passed.
[OK] Commission market (0.45s) Passed 24/24 checks.
[OK] Commission hardening (1.22s) [OK ] expire.job.expired - {"due":1,"expired":1,"failed":0,"dryRun":false} | Passed 36/36 checks.
[OK] Economy guard (0.29s) [OK ] scan.dry.ok - {"enabled":true,"dryRun":true,"created":0,"updated":0,"checks":{"suspicious_trades":1,"massive_market_gain":1,"massive_reward_gain":1,"coin_balance":34,"transfer_abuse":2,"fake_market_prices":1}} | [OK ] scan.run.ok - {"enabled":true,"dryRun":false,"created":40,"updated":0,"checks":{"suspicious_trades":1,"massive_market_gain":1,"massive_reward_gain":1,"coin_balance":34,"transfer_abuse":2,"fake_market_prices":1}} | Economy Guard smoke: 11/11 passed.
[OK] Battle replay (0.26s) Battle replay smoke: 6/6 passed.
[OK] Battle transformation stats (0.25s) [OK ] primal_kyogre {"hp_max":342,"hp_my":342,"atk":354,"def":216,"satk":431,"sdef":361,"speed":307} | [OK ] primal_groudon {"hp_max":342,"hp_my":342,"atk":479,"def":433,"satk":354,"sdef":216,"speed":216} | [OK ] mega_rayquaza {"hp_max":352,"hp_my":352,"atk":479,"def":240,"satk":403,"sdef":240,"speed":350}
[OK] Battle transformation/weather (0.32s) Battle transformation/weather smoke: 26/26 passed.
[OK] Notifications/mail (0.28s) Notifications/mail smoke: 11/11 passed.
[OK] Dex/AttackDex (1.36s) Dex/AttackDex smoke: 27/27 passed.
[OK] Tournament flow (1.91s) Tournament QA smoke: 33/33 passed.
[OK] Admin/GM center (1.91s) Admin GM Center smoke: 26/26 passed.
[OK] Bug reporter (1.28s) Bug Reporter smoke: 9/9 passed.
```

### git diff --check

```text
> git diff --check
plan.md:41: trailing whitespace.
+✔ Расширяет суточную границу дропа монет до 4 000 000 у нас в игре Искатель предметов 
```

Команда падает только на уже существующем `plan.md:41`, который не входит в эту задачу и запрещён к чистке без отдельной задачи. Также Git выводит много предупреждений `LF will be replaced by CRLF` по чужим изменённым файлам.

### git diff --stat

Полный `git diff --stat` сейчас включает 60 файлов из чужой незакоммиченной работы в дереве. TASK-034-релевантные файлы:

```text
tools/beta_backup.php                         | 35 +-
src/Game/StalePveBattleReaper.php             | 107 insertions (untracked in current worktree)
tools/pve_stale_battle_reaper_smoke.php       | 135 insertions (untracked in current worktree)
docs/agent-tasks/TASK-034-tools-backup-and-smoke.report.md | updated report
```

## Риски и что осталось

- В live-БД уже есть много pre-existing stale PvE battles. Новый smoke не коммитит их закрытие: все изменения reaper’а идут внутри внешней транзакции и откатываются.
- `git diff --check` остаётся красным из-за `plan.md:41`, вне зоны TASK-034.
- Первый quick regression один раз упал на FPE, но точечный FPE smoke и повторный quick regression прошли зелёными.
- Коммита и push нет.
---

## Review — DSH-агент, 2026-10-09

### Что проверено и принято

* Бэкап без флагов действительно работает: снят файл 9.09 МБ, проверка `14/14`.
* Барьер на конкретное имя БД заменён на явное согласие (`REAPER_SMOKE_ALLOW_LIVE=1`) — как и просили.
* Синтетические данные убираются в `finally`, боевая БД восстанавливается.

### Замечание 1 (блокирующее): смоук проверяет копию логики, а не сам реапер

В `tools/pve_stale_battle_reaper_smoke.php`:

* строка 52 — `$dry = $reaper->reap(true);` — **настоящий** реапер, но только чтение;
* строка 58 — `$applied = reapSyntheticBattle($db, $battleId, $userId);` — **локальная копия**;
* строка 63 — `$secondDry = $reaper->reap(true);` — снова настоящий реапер.

Функция `reapSyntheticBattle()` (строка 126) повторяет внутри смоука те же
`UPDATE battles SET pobeda = -1 ...` и `UPDATE users SET pve = 0, battleid = 0 ...`,
что делает `StalePveBattleReaper::reap(false)`.

Следствие: проверки `reap.closes_battle`, `reap.clears_matching_pve_lock` и
`reap.preserves_pvp_flag` проверяют **код самого смоука**, а не продукт. Если в
`StalePveBattleReaper::reap(false)` завтра появится ошибка в условии `UPDATE`,
смоук останется зелёным. То есть тест не защищает ровно то, ради чего написан.

Причина понятна: реапер сам управляет транзакциями (`beginTransaction` на строке 51),
а `PDO` не поддерживает вложенные — это и был исходный дефект. Но правильный вывод из
него один из двух, и оба сохраняют проверку настоящего кода:

1. Смоук не открывает внешнюю транзакцию, вызывает `$reaper->reap(false)` и убирает
   за собой явно в `finally`;
2. либо у реапера появляется режим «работать в уже открытой транзакции», и тогда
   откат делает смоук.

Копия SQL — третий вариант, и он обесценивает тест. Переделать.

### Замечание 2: отчёт не по требуемой форме

Отчёт — сводка на пять строк. Нужны: список изменённых файлов, конкретные команды,
их вывод, `git diff --stat`, риски. Приложи вывод смоука и явно покажи, что он
вызывает настоящий `reap(false)`.

### Что делать дальше

1. Убрать `reapSyntheticBattle()`; проверять настоящий `StalePveBattleReaper::reap(false)`.
2. Переписать отчёт по форме.
3. Прогнать смоук на живой БД после бэкапа и приложить вывод: ожидается `8/8` или больше.
