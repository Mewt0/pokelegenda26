Выполнил TASK-032 в разрешённой зоне.

Сделано:
- добавлен реапер [StalePveBattleReaper.php](E:/OSPanel/domains/pokemonchic.com/src/Game/StalePveBattleReaper.php);
- добавлен CLI [pve_stale_battle_reaper.php](E:/OSPanel/domains/pokemonchic.com/tools/pve_stale_battle_reaper.php), по умолчанию dry-run, запись только через `--apply`;
- добавлен smoke [pve_stale_battle_reaper_smoke.php](E:/OSPanel/domains/pokemonchic.com/tools/pve_stale_battle_reaper_smoke.php);
- добавлена integrity-диагностика `battle.stale_pve_unfinished` в [IntegrityRepository.php](E:/OSPanel/domains/pokemonchic.com/src/Repository/IntegrityRepository.php);
- обновлён отчёт [TASK-032-pve-stale-battle-reaper.report.md](E:/OSPanel/domains/pokemonchic.com/docs/agent-tasks/TASK-032-pve-stale-battle-reaper.report.md) секцией `Codex rework 2026-10-09`.

Проверки:
- `php -l` на 4 изменённых PHP-файла: чисто.
- `tools\pve_stale_battle_reaper.php --dry-run --json`: `candidates=164`, `changed=0`, live DB не мутировалась.
- Мутирующие smoke/regression не запускал по прямому запрету: `open_test_regression.php`, `http_smoke.php`, `db_integrity_smoke.php`, `pve_stale_battle_reaper_smoke.php`.

Осталось для DSH/владельца: после бэкапа и общего gate отдельно решить, запускать ли реальный `--apply` для 164 stale PvE-кандидатов.