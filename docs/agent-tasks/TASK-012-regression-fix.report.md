Готово.

Исправил регрессию в [InventoryRepository.php](E:/OSPanel/domains/pokemonchic.com/src/Repository/InventoryRepository.php:642):

- `:take` в атомарном списании разнесён на `:take_sub` / `:take_cmp`.
- Дополнительно нашёл такой же PDO-паттерн в запросе PP атаки: `:pp` разнесён на `:pp_min` / `:pp_max`.

Проверки прошли:

```text
php -l InventoryRepository.php — OK
php -l TrainingRepository.php — OK
php -l NpcDialogService.php — OK
```

Quick gate вернулся:

```text
Open-test regression (quick)
Passed: 24, failed: 0, skipped: 0
```

Отчёт дописан в [TASK-012-economy-atomicity.report.md](E:/OSPanel/domains/pokemonchic.com/docs/agent-tasks/TASK-012-economy-atomicity.report.md) разделом `Исправление регрессии`, включая дословный вывод gate и список проверенных `prepare(...)` с количеством placeholders. Коммит/пуш не делал.