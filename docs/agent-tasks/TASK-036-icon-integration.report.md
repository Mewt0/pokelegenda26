Готово: выполнил TASK-036 и написал отчёт в [TASK-036-icon-integration.report.md](E:/OSPanel/domains/pokemonchic.com/docs/agent-tasks/TASK-036-icon-integration.report.md).

Ключевое: `icons.css` подключён в shared head, иконки интегрированы в чат, нижнюю панель, локацию/переходы/NPC и игроков; `menu-*.png` не трогал. Доступность сохранена: иконки `aria-hidden="true"`, icon-only кнопки оставлены с `aria-label`.

Проверки:
- `php -l` по изменённым view: OK
- `node --check` по изменённым JS: OK
- quick gate через проектный PHP CLI: `Passed: 24, failed: 0, skipped: 0`
- bare `php ...` не стартует, потому что `php` не в PATH; это зафиксировано в отчёте вместе с дословным выводом
- полный `git diff --check` упирается в уже существующий `plan.md:41` вне разрешённой зоны; scoped check по моим файлам чистый.