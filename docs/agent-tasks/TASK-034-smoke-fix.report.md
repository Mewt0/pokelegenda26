Готово.

Переделал smoke так, чтобы он проверял настоящий `StalePveBattleReaper::reap(false, 0, false)`, а не локальную копию SQL. Выбрал вариант 2: reaper теперь умеет работать внутри уже открытой транзакции, а smoke после проверок откатывает всё через rollback. `reapSyntheticBattle()` удалён полностью.

Заодно smoke поймал реальную ошибку продукта: повторный PDO-параметр `:pvp_user` давал `SQLSTATE[HY093]`; исправил на два отдельных параметра.

Проверки:
- backup создан: `pokemonchic_beta_20261009_071428.sql (9.2 MB)`
- `php -l` по изменённым PHP-файлам: OK
- `REAPER_SMOKE_ALLOW_LIVE=1 php tools/pve_stale_battle_reaper_smoke.php`: `10/10 passed`
- красная проверка с временно сломанным UPDATE: smoke упал `7/10`, затем код возвращён
- `open_test_regression --profile=quick`: `24/24`
- `git diff --check` падает только на уже существующем `plan.md:41` trailing whitespace, вне зоны задачи

Отчёт переписан в [TASK-034-tools-backup-and-smoke.report.md](/E:/OSPanel/domains/pokemonchic.com/docs/agent-tasks/TASK-034-tools-backup-and-smoke.report.md), секцию `Review` DSH не удалял. Коммита и push не делал.