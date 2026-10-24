Готово. Задача `TASK-048` выполнена и отчёт записан в [TASK-048-explicit-columns-battle-paths.report.md](E:/OSPanel/domains/pokemonchic.com/docs/agent-tasks/TASK-048-explicit-columns-battle-paths.report.md).

Что изменено:
- В [BattleRepository.php](E:/OSPanel/domains/pokemonchic.com/src/Repository/BattleRepository.php) заменил 5 боевых `SELECT *` на явные списки колонок.
- В [BugReportRepository.php](E:/OSPanel/domains/pokemonchic.com/src/Repository/BugReportRepository.php) заменил 2 battle/replay snapshot `SELECT *`.
- Добавил [select_star_audit.php](E:/OSPanel/domains/pokemonchic.com/tools/select_star_audit.php) с `--self-test`.

Проверки:
- `php -l` на 3 изменённых PHP-файла: OK.
- `php tools/select_star_audit.php --self-test`: `Total SELECT * in src: 49`, OK.
- `php tools/battle_replay_smoke.php`: `6/6 passed`.
- `open_test_regression quick` и `http_smoke` сейчас падают на auth/session окружении: после успешного login API/admin paths возвращают `401/403`. Это зафиксировано в отчёте.

Важно: в `BattleRepository.php` уже были чужие незакоммиченные изменения до моей работы; я их не откатывал и не чистил.