Готово.

Изменил [public/css/tokens.css](E:/OSPanel/domains/pokemonchic.com/public/css/tokens.css:51): `--fs-400` теперь `16px`, `--dur-fast` теперь `150ms`. `base.css` не трогал: тело уже берёт размер через `var(--fs-400)`.

Отчёт создан: [TASK-040-type-scale-and-duration.report.md](E:/OSPanel/domains/pokemonchic.com/docs/agent-tasks/TASK-040-type-scale-and-duration.report.md).

Проверки:
- шкала кеглей: `12, 13, 14, 16, 18, 22, 28`;
- durations: `150 < 180 < 240`;
- баланс `{}` в `tokens.css` и `base.css` зелёный;
- overflow проверил в браузере на `1366x768` и `390x844` по основным страницам, горизонтального переполнения документа нет;
- `open_test_regression` не запускал по ограничению.