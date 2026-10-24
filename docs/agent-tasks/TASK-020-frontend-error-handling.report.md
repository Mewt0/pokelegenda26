Готово: TASK-020 выполнена в разрешённых файлах, отчёт создан: [TASK-020-frontend-error-handling.report.md](E:/OSPanel/domains/pokemonchic.com/docs/agent-tasks/TASK-020-frontend-error-handling.report.md).

Что изменено:
- Почта и журнал больше не залипают на `loading` при сетевой ошибке.
- Комиссия разблокирует кнопки в `finally` и показывает ошибку в status.
- Инвентарь проверяет `response.ok`, ловит сетевые ошибки и показывает toast.
- Smoke/regression не запускал по запрету.

Проверки прошли:
- `node --check` для трёх JS-файлов — OK.
- `php -l views\game-items.php` — OK.