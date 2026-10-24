Готово. Добавил три канонических региона отдельной миграцией и подключил их к карте:

- [2026_10_12_000001_canonical_regions.sql](E:/OSPanel/domains/pokemonchic.com/database/migrations/2026_10_12_000001_canonical_regions.sql): 36 локаций, 216 спавнов, транспорт, flight routes, 114 правил дропа без монет.
- [data.world.php](E:/OSPanel/domains/pokemonchic.com/include/data.world.php): связные подграфы Хоэнна, Синно и Юновы, стыковка через Джотто/порт и самолётную точку.
- [TASK-045-canonical-regions.report.md](E:/OSPanel/domains/pokemonchic.com/docs/agent-tasks/TASK-045-canonical-regions.report.md): отчёт с командами, результатами и рисками.

Проверки прошли:

- Backup создан: `pokemonchic_beta_20261010_232616.sql`
- `php -l include\data.world.php` — OK
- `tools\migration_status.php --apply` — `pending=0 dirty=0 failed=0`, повторный прогон тоже OK
- `tools\spawns_validity.php` — неизвестные покемоны/локации/веса: `0`; старые 6 high-level записей остаются в `Игральная комната`, новые регионы валидны
- `tools\spawns_hygiene.php` — дублей нет
- `tools\drops_audit.php` — несуществующих предметов `0`, суммы <= 100%, монет в новом wild-дропе `0`
- Связность: все три региона `12/12`, deadends `0`
- Главный гейт: `open_test_regression --profile=quick` — `Passed: 25, failed: 0, skipped: 0`

Коммит не делал.