TASK-031 выполнена.

- `events.php` теперь немедленно возвращает `410 Gone` со ссылкой на `/game/events`.
- `PHP85_MIGRATION_PLAN.md` обновлён.
- `php -l events.php`: успешно.
- HTTP-проверка: `410 Gone`, фатальной ошибки нет.
- Живых потребителей `/events.php` не найдено; остаточный `EventSource` в `game.php` недостижим из-за раннего 410.
- Мутирующие smoke-тесты не запускались.
- `page_health_check.php` не завершён: отсутствуют login/password и переменные `SMOKE_*`.
- Коммиты и push не выполнялись.

Полный отчёт: [TASK-031-events-php-410.report.md](E:\OSPanel\domains\pokemonchic.com\docs\agent-tasks\TASK-031-events-php-410.report.md).