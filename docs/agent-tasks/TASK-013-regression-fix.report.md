Регрессии исправлены.

- CSRF совместим с legacy-токенами без `_csrf_issued_at`; неявная ротация устранена, TTL действует только для токенов класса.
- Восстановлен обработчик `quest_update:*` в `NpcDialogService`. `canStart()` ослаблять не потребовалось.
- Создан backup БД: `pokemonchic_beta_20261009_070703.sql`, 9.09 MB.
- `php -l` и `git diff --check` — без ошибок.
- Полный quick-gate: **24/24**.
  - HTTP: 52/52
  - Quest chains: 53/53
  - Integrity: P0=0, P1=0

Отчёт дополнен: [TASK-013-auth-session-hygiene.report.md](E:/OSPanel/domains/pokemonchic.com/docs/agent-tasks/TASK-013-auth-session-hygiene.report.md).

Коммиты и push не выполнялись.