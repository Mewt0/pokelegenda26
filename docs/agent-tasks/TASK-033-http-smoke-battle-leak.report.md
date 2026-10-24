TASK-033 выполнена.

Изменено:

- [tools/http_smoke.php](E:/OSPanel/domains/pokemonchic.com/tools/http_smoke.php)
- [TASK-033 report](E:/OSPanel/domains/pokemonchic.com/docs/agent-tasks/TASK-033-http-smoke-battle-leak.report.md)

Исправлена утечка PvE-боя, создаваемого `GET /api/game/state`. Cleanup выполняется через штатные `escape` и `ack-end`; добавлена итоговая проверка чистого боевого состояния.

Проверки:

- `php -l tools/http_smoke.php` — успешно.
- `git diff --check` — ошибок нет.
- Мутирующие smoke и `open_test_regression.php` не запускались согласно запрету задачи.
- Коммиты и push не выполнялись.