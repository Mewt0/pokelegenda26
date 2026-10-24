Готово. TASK-044 выполнена и отчёт заполнен: [TASK-044-dex-and-broken-text.report.md](E:/OSPanel/domains/pokemonchic.com/docs/agent-tasks/TASK-044-dex-and-broken-text.report.md).

Изменил:
`public/css/game-start.css`, `public/js/dex-overlay.js`, `public/js/mail-overlay.js`, `public/index.php`.

Ключевое:
- Покедекс/Атакадекс приведены к схеме “список + детали”, статы переносятся.
- `/game/attackdex` теперь редиректит на `/game?dex=attacks`, и JS открывает вкладку Атакадекса.
- Почта чинит старый mojibake-текст из БД через восстановление CP1251 → UTF-8.
- Карточка тренера в живом DOM проверена: битого текста там не обнаружил.

Проверки прошли:
- `node --check` для изменённых JS: ok
- `php -l public/index.php`: ok
- quick regression: `Passed: 25, failed: 0, skipped: 0`
- `http_smoke`: `Result: 52 checks, 0 failures.`

Скриншоты лежат в [task-044-shots](E:/OSPanel/domains/pokemonchic.com/docs/agent-tasks/task-044-shots).