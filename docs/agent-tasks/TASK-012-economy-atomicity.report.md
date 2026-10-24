Готово. Выполнил TASK-012 в разрешённой зоне и создал отчёт: [TASK-012-economy-atomicity.report.md](E:/OSPanel/domains/pokemonchic.com/docs/agent-tasks/TASK-012-economy-atomicity.report.md).

Что изменено:
- `removeItem()` переведён на атомарное SQL-списание через `count = count - :take WHERE count >= :take`.
- `TrainingRepository::buy()` обёрнут в транзакцию.
- `jobTemporaryItems()` обёрнут в транзакцию.
- NPC-блоки списания/наград теперь проверяют результат `removeItem()` и работают транзакционно.
- Добавлено логирование для двух немых `catch`.

Проверки:
- `php -l` по 4 изменённым PHP-файлам — без ошибок.
- Мутирующие smoke/regression не запускал, как было указано.
## Исправление регрессии

Причина падения quick gate: в `src/Repository/InventoryRepository.php` запрос `UPDATE items_users SET count = count - :take WHERE id = :id AND count >= :take LIMIT 1` использовал named placeholder `:take` дважды при `PDO::ATTR_EMULATE_PREPARES=false`.

Сделано:
- `src/Repository/InventoryRepository.php:883`: `:take` разнесён на `:take_sub` и `:take_cmp`, оба значения привязаны в `execute()`.
- `src/Repository/InventoryRepository.php:642`: найден и исправлен такой же PDO-паттерн в запросе обновления PP атаки, `:pp` разнесён на `:pp_min` и `:pp_max`.
- `src/Repository/TrainingRepository.php` и `src/Game/NpcDialogService.php` проверены на повторяющиеся placeholders в `prepare(...)`; повторов не найдено.

Проверки:

```text
E:\OSPanel\modules\php\PHP_8.1\php.exe -l src\Repository\InventoryRepository.php
No syntax errors detected in src\Repository\InventoryRepository.php

E:\OSPanel\modules\php\PHP_8.1\php.exe -l src\Repository\TrainingRepository.php
No syntax errors detected in src\Repository\TrainingRepository.php

E:\OSPanel\modules\php\PHP_8.1\php.exe -l src\Game\NpcDialogService.php
No syntax errors detected in src\Game\NpcDialogService.php
```

Quick gate, вывод дословно:

```text
Open-test regression (quick)
Passed: 24, failed: 0, skipped: 0, duration: 22693 ms

[OK] Migrations (0.29s) Migration status: total=79 applied=79 pending=0 dirty=0 failed=0
[OK] DB integrity (0.54s) P0=0, P1=0, WARN=1, ACCEPTED=4, OK=18, FIXED=0
[OK] HTTP API read smoke (2.90s) Result: 52 checks, 0 failures.
[OK] Legacy core migration smoke (0.35s) Result: 30 checks, 0 failures.
[OK] First player experience (0.42s) Result: 25 checks, 0 failures.
[OK] Quest chains minimum (0.39s) Result: 53 checks, 0 failures.
[OK] Locations/NPC/transport (0.31s) Result: 24 checks, 0 failures.
[OK] Inventory/Held items (0.32s) Inventory held-items smoke: 16/16 passed.
[OK] Reward pipeline (0.32s) [OK ] pipeline.failure.logged - status=failed | [OK ] pipeline.failure.rollback - rollback recorded | Reward pipeline smoke: 13/13 passed.
[OK] Safe storage (0.23s) Safe storage smoke: 7/7 passed.
[OK] PvE reward idempotency (0.31s) PvE reward idempotency smoke: 9/9 passed.
[OK] PvE action lock safety (6.31s) PvE action lock smoke: 14/14 passed.
[OK] Background jobs (0.29s) [OK ] economy_guard.dry - {"enabled":true,"dryRun":true,"created":0,"updated":0,"checks":{"suspicious_trades":0,"massive_market_gain":0,"massive_reward_gain":0,"coin_balance":5,"transfer_abuse":0,"fake_market_prices":0}} | Background jobs smoke: 10/10 passed.
[OK] Commission market (0.48s) Passed 24/24 checks.
[OK] Commission hardening (1.25s) [OK ] expire.job.expired - {"due":1,"expired":1,"failed":0,"dryRun":false} | Passed 36/36 checks.
[OK] Economy guard (0.29s) [OK ] scan.dry.ok - {"enabled":true,"dryRun":true,"created":0,"updated":0,"checks":{"suspicious_trades":1,"massive_market_gain":1,"massive_reward_gain":1,"coin_balance":34,"transfer_abuse":2,"fake_market_prices":1}} | [OK ] scan.run.ok - {"enabled":true,"dryRun":false,"created":40,"updated":0,"checks":{"suspicious_trades":1,"massive_market_gain":1,"massive_reward_gain":1,"coin_balance":34,"transfer_abuse":2,"fake_market_prices":1}} | Economy Guard smoke: 11/11 passed.
[OK] Battle replay (0.23s) Battle replay smoke: 6/6 passed.
[OK] Battle transformation stats (0.23s) [OK ] primal_kyogre {"hp_max":342,"hp_my":342,"atk":354,"def":216,"satk":431,"sdef":361,"speed":307} | [OK ] primal_groudon {"hp_max":342,"hp_my":342,"atk":479,"def":433,"satk":354,"sdef":216,"speed":216} | [OK ] mega_rayquaza {"hp_max":352,"hp_my":352,"atk":479,"def":240,"satk":403,"sdef":240,"speed":350}
[OK] Battle transformation/weather (0.31s) Battle transformation/weather smoke: 26/26 passed.
[OK] Notifications/mail (0.26s) Notifications/mail smoke: 11/11 passed.
[OK] Dex/AttackDex (1.37s) Dex/AttackDex smoke: 27/27 passed.
[OK] Tournament flow (1.95s) Tournament QA smoke: 33/33 passed.
[OK] Admin/GM center (2.05s) Admin GM Center smoke: 26/26 passed.
[OK] Bug reporter (1.28s) Bug Reporter smoke: 9/9 passed.
```

Проверенные `prepare(...)` запросы и число вхождений placeholders:

```text
src/Repository/InventoryRepository.php:34: user=1, item=1, time=1
src/Repository/InventoryRepository.php:59: (none)
src/Repository/InventoryRepository.php:78: (none)
src/Repository/InventoryRepository.php:108: user=1
src/Repository/InventoryRepository.php:136: id=1, user=1, time=1
src/Repository/InventoryRepository.php:236: pokemon=1
src/Repository/InventoryRepository.php:279: pokemon=1, user=1
src/Repository/InventoryRepository.php:329: id=1, user=1, time=1
src/Repository/InventoryRepository.php:354: gift=1
src/Repository/InventoryRepository.php:495: levels=1, pokemon=1, user=1
src/Repository/InventoryRepository.php:513: pokemon=1, user=1
src/Repository/InventoryRepository.php:518: happy=1, pokemon=1, user=1
src/Repository/InventoryRepository.php:527: base=1, name=1, pokemon=1, user=1
src/Repository/InventoryRepository.php:544: pokemon=1, user=1
src/Repository/InventoryRepository.php:558: base=1
src/Repository/InventoryRepository.php:565: har=1
src/Repository/InventoryRepository.php:578: hp_my=1, hp_max=1, atk=1, def=1, satk=1, sdef=1, speed=1, pokemon=1, user=1
src/Repository/InventoryRepository.php:604: pokemon=1
src/Repository/InventoryRepository.php:615: base=1, level=1
src/Repository/InventoryRepository.php:642: move=1, pp_min=1, pp_max=1, pokemon=1
src/Repository/InventoryRepository.php:656: id=1, pokemon=1, move=1, pp_min=1, pp_max=1
src/Repository/InventoryRepository.php:696: item=1, time=1, pokemon=1
src/Repository/InventoryRepository.php:701: pokemon=1, item=1, time=1
src/Repository/InventoryRepository.php:707: id=1
src/Repository/InventoryRepository.php:715: id=1
src/Repository/InventoryRepository.php:772: user=1
src/Repository/InventoryRepository.php:796: count=1, id=1
src/Repository/InventoryRepository.php:804: id=1, item=1, user=1, count=1, dattimer=1, timers=1
src/Repository/InventoryRepository.php:831: name=1
src/Repository/InventoryRepository.php:840: name=1
src/Repository/InventoryRepository.php:858: user=1, item=1, time=1
src/Repository/InventoryRepository.php:883: take_sub=1, id=1, take_cmp=1
src/Repository/InventoryRepository.php:889: id=1
src/Repository/InventoryRepository.php:957: item=1, datetime=1, pokemon=1
src/Repository/InventoryRepository.php:966: pokemon=1, item=1, datetime=1
src/Repository/InventoryRepository.php:1017: pokemon=1
src/Repository/InventoryRepository.php:1044: user=1, item=1
src/Repository/InventoryRepository.php:1061: id=1, user=1, time=1
src/Repository/InventoryRepository.php:1105: item=1
src/Repository/InventoryRepository.php:1177: id=1, user=1, time=1
src/Repository/InventoryRepository.php:1411: user=1, boost_key=1, multiplier=1, starts_at=1, expires_at=1, created_at=1
src/Repository/InventoryRepository.php:1427: pokemon=1, user=1
src/Repository/InventoryRepository.php:1442: pokemon=1, user=1
src/Repository/InventoryRepository.php:1455: pokemon=1
src/Repository/InventoryRepository.php:1465: count=1, id=1, user=1
src/Repository/InventoryRepository.php:1474: id=1, user=1
src/Repository/InventoryRepository.php:1480: user=1
src/Repository/TrainingRepository.php:213: pokemon=1, user=1
src/Repository/TrainingRepository.php:251: stage=1, stat=1, effect=1, tamed=1, updated=1, pokemon=1
src/Repository/TrainingRepository.php:272: user=1
src/Game/NpcDialogService.php:1272: user=1
src/Game/NpcDialogService.php:1308: user=1, base=1, level=1
src/Game/NpcDialogService.php:1342: user=1
src/Game/NpcDialogService.php:1351: pokemon=1
src/Game/NpcDialogService.php:1352: pokemon=1, user=1
src/Game/NpcDialogService.php:1361: points=1, user=1
src/Game/NpcDialogService.php:1401: id=1, users=1, base=1, name=1, lvl=1, hp_my=1, hp_max=1, atk=1, def=1, satk=1, sdef=1, speed=1, startone=1, starter=1, usersone=1, ability=1
src/Game/NpcDialogService.php:1438: base=1, level=1
src/Game/NpcDialogService.php:1453: id=1, pokemon=1, a_id=1, a_pp_min=1, a_pp_max=1, b_id=1, b_pp_min=1, b_pp_max=1, c_id=1, c_pp_min=1, c_pp_max=1, d_id=1, d_pp_min=1, d_pp_max=1
```

Итог: повторяющихся named placeholders в проверенных `prepare(...)` запросах трёх разрешённых файлов не осталось; quick gate вернулся к `24/24`.
