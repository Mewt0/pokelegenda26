# Индекс документации PokemonChic

Источник правды по текущей архитектуре: `PROJECT_CONTEXT.md`. Старые планы и QA-отчёты не удаляются, но считаются историческими, если противоречат `PROJECT_CONTEXT.md` или живому коду.

Факты gate на 2026-10-07: сайт отвечает 200; `open_test_regression --profile=quick` = 24/24; `http_smoke` = 52/52; миграции 79/79; integrity `P0=0`, `P1=0`, `WARN=0`, `ACCEPTED=4`; `item_gameplay_metadata`: implemented 42, visual_only 17, todo 11.

QA-отчёты до текущего gate оставлены как архив багов. В частности, OPEN-пункты `QA_GAMEPLAY_REPORT_2026-05-25.md` по журналу квестов (`/game/quests` overlay) и NPC-питомнику до 24 покемонов закрыты в текущем слое и не должны читаться как актуальные блокеры.

| Файл | Назначение | Статус | Последняя актуализация |
| --- | --- | --- | --- |
| `AGENTS.md` | Правила совместной работы агентов и границы владения | актуальный | 2026-10-09 |
| `BETA_RELEASE_TASKS.md` | Старый список beta-задач | исторический | 2026-05 |
| `DESIGN_REFRESH_AND_BUGFIX_PLAN.md` | План дизайн-рефреша и багфикса DSH-агента | план | 2026-10-09 |
| `docs/README.md` | Этот индекс документации | актуальный | 2026-10-09 |
| `docs/agent-tasks/BOARD.md` | Очередь задач агентов | актуальный | 2026-10 |
| `docs/agent-tasks/README.md` | Протокол очереди задач | актуальный | 2026-10 |
| `docs/agent-tasks/TASK-000-codex-design-skills.md` | Спека задачи Codex | план | 2026-10 |
| `docs/agent-tasks/TASK-001-asset-registry-check.md` | Спека задачи Codex | план | 2026-10 |
| `docs/agent-tasks/TASK-001-asset-registry-check.report.md` | Отчёт Codex по asset registry | актуальный | 2026-10 |
| `docs/agent-tasks/TASK-002-php-health-report.md` | Спека задачи Codex | план | 2026-10 |
| `docs/agent-tasks/TASK-003-reconcile-docs.md` | Спека этой сверки документации | план | 2026-10 |
| `docs/agent-tasks/TASK-003-reconcile-docs.report.md` | Отчёт Codex по этой сверке документации | актуальный | 2026-10 |
| `docs/agent-tasks/TASK-010-quest-integrity.md` | Спека задачи Codex | план | 2026-10 |
| `docs/agent-tasks/TASK-010-quest-integrity.report.md` | Отчёт Codex по задаче | актуальный | 2026-10 |
| `docs/agent-tasks/TASK-011-ban-and-ip-trust.md` | Спека задачи Codex | план | 2026-10 |
| `docs/agent-tasks/TASK-011-ban-and-ip-trust.report.md` | Отчёт Codex по ban/ip trust | актуальный | 2026-10 |
| `docs/agent-tasks/TASK-012-economy-atomicity.md` | Спека задачи Codex | план | 2026-10 |
| `docs/agent-tasks/TASK-013-auth-session-hygiene.md` | Спека задачи Codex | план | 2026-10 |
| `docs/agent-tasks/TASK-013-auth-session-hygiene.report.md` | Отчёт Codex по задаче | актуальный | 2026-10 |
| `docs/agent-tasks/TASK-013-regression-fix.report.md` | Отчёт Codex по regression fix | актуальный | 2026-10 |
| `docs/agent-tasks/TASK-020-frontend-error-handling.md` | Спека задачи DSH/Codex | план | 2026-10 |
| `docs/agent-tasks/TASK-020-frontend-error-handling.report.md` | Отчёт Codex по frontend error handling | актуальный | 2026-10 |
| `docs/agent-tasks/TASK-021-dead-code-frontend.md` | Спека задачи DSH/Codex | план | 2026-10 |
| `docs/agent-tasks/TASK-021-dead-code-frontend.report.md` | Отчёт Codex по dead-code frontend | актуальный | 2026-10 |
| `docs/agent-tasks/TASK-030-legacy-include-compat.md` | Спека задачи Codex | план | 2026-10 |
| `docs/agent-tasks/TASK-030-legacy-include-compat.report.md` | Отчёт Codex по legacy include compat | актуальный | 2026-10 |
| `docs/agent-tasks/TASK-031-events-php-410.md` | Спека задачи Codex | план | 2026-10 |
| `docs/agent-tasks/TASK-031-events-php-410.report.md` | Отчёт Codex по задаче | актуальный | 2026-10 |
| `docs/agent-tasks/TASK-032-pve-stale-battle-reaper.md` | Спека задачи Codex | план | 2026-10 |
| `docs/agent-tasks/TASK-032-pve-stale-battle-reaper.report.md` | Отчёт Codex по задаче | актуальный | 2026-10 |
| `docs/agent-tasks/TASK-033-http-smoke-battle-leak.md` | Спека задачи Codex | план | 2026-10 |
| `docs/agent-tasks/TASK-033-http-smoke-battle-leak.report.md` | Отчёт Codex по задаче | актуальный | 2026-10 |
| `docs/agent-tasks/TASK-034-smoke-fix.report.md` | Отчёт Codex по smoke fix | актуальный | 2026-10 |
| `docs/agent-tasks/TASK-034-tools-backup-and-smoke.md` | Спека задачи Codex | план | 2026-10 |
| `docs/agent-tasks/TASK-034-tools-backup-and-smoke.report.md` | Отчёт Codex по задаче | актуальный | 2026-10 |
| `docs/agent-tasks/TASK-035-empty-states-mail-market.md` | Спека задачи Codex | план | 2026-10 |
| `docs/agent-tasks/TASK-035-empty-states-mail-market.report.md` | Отчёт Codex по задаче | актуальный | 2026-10 |
| `docs/agent-tasks/TASK-036-icon-integration.md` | Спека задачи Codex | план | 2026-10 |
| `docs/agent-tasks/TASK-036-icon-integration.report.md` | Отчёт Codex по задаче | актуальный | 2026-10 |
| `docs/agent-tasks/bus/inbox/20261007-212005-msg.md` | Сообщение мосту агентов | исторический | 2026-10 |
| `docs/reports/dead-code-frontend-2026-10-07.md` | Отчёт по dead-code frontend | исторический | 2026-10-07 |
| `docs/reports/php-health-2026-10-07.md` | Отчёт по PHP health | исторический | 2026-10-07 |
| `GAME_EVENTS_MIGRATION_PLAN.md` | План миграции игровых событий | план | 2026-05 |
| `ITEM_LOGIC_AUDIT.md` | Аудит логики предметов до `item_gameplay_metadata` | устаревший | 2026-05 |
| `MODERNIZATION_REPORT_2026.md` | Ранний отчёт по модернизации лендинга | исторический | 2026-10-09 |
| `OPEN_TEST_REGRESSION_2026-05-27.md` | Старый отчёт регрессии open-test | исторический | 2026-05-27 |
| `PHP85_MIGRATION_PLAN.md` | План будущей миграции PHP | план | 2026-10 |
| `plan.md` | Рабочий черновик/план | исторический | 2026-05 |
| `PROJECT_CONTEXT.md` | Текущая архитектура и источник правды | актуальный | 2026-06-05 |
| `PROJECT_MAP_RU.md` | Практическая карта проекта для владельца | актуальный | 2026-06 |
| `PROJECT_STRUCTURE_RU.md` | Техническая карта файлов и слоёв | актуальный | 2026-10-09 |
| `QA_BACKEND_BATTLE_CAPTURE_TASK.md` | QA-задача по backend battle/capture | исторический | 2026-05 |
| `QA_BATTLE_TRANSFORMATIONS_REPORT_2026-05-26.md` | QA-отчёт по боевым трансформациям | исторический | 2026-05-26 |
| `QA_GAMEPLAY_REPORT_2026-05-25.md` | QA-отчёт по gameplay; OPEN-пункты читать через текущий контекст | исторический | 2026-05-25 |
| `README.md` | Английское краткое описание проекта | актуальный | 2026-10-09 |
| `README_RU.md` | Русское краткое описание проекта | актуальный | 2026-10-09 |
| `REWRITE.md` | Старый черновик переписывания | исторический | 2026-05 |
| `REWRITE_STATUS.md` | Подробный журнал переноса; не главный источник архитектуры | актуальный | 2026-10 |
| `VISUAL_ASSETS_TODO.md` | Список задач по визуальным ассетам | план | 2026-05 |
