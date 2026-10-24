Готово. Реализовал квесты 161/162 в [NpcDialogService.php](E:/OSPanel/domains/pokemonchic.com/src/Game/NpcDialogService.php:163) и завёл отчёт [TASK-quest161-162.report.md](E:/OSPanel/domains/pokemonchic.com/docs/agent-tasks/TASK-quest161-162.report.md).

Ключевое: для «смелого» использован `har = 7` по живому lookup в `AdminRepository`; победы Амиры считаются по готовому архиву `battle_history_archive`, не по `users.pve/pvp`. Списания и награды сделаны транзакционно, кнопки у Амиры и Билли зависят от текущего шага и пропадают после завершения.

Проверки прошли:

```text
php -l src\Game\NpcDialogService.php
No syntax errors detected

open_test_regression --profile=quick
Passed: 25, failed: 0, skipped: 0

migration_status.php --apply
pending=0 dirty=0 failed=0
```

Live-проверку через Chrome/CDP сделал. На текущем Tacos старт 161/162 не создаёт запись, потому что в БД есть зависимости: 161 требует завершённый 160, 162 требует завершённый 161, а у Tacos сейчас `quest_id=160` ещё `gotov=0`. Я добавил понятный отказ вместо ложного «квест принят». Локацию Tacos восстановил на `4`. Коммит не делал.