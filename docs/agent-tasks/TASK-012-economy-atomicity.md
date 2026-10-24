# TASK-012 — P0: атомарность экономики (двойная трата, покупка без транзакции)

**Исполнитель:** Codex-агент · **Зона:** `src/Repository/InventoryRepository.php`, `src/Repository/TrainingRepository.php`, `src/Repository/BackgroundJobRepository.php`, `src/Game/NpcDialogService.php` (только блоки выдачи наград/списания) · **Статус:** todo

## Проблемы (подтверждены аудитом)

1. **Двойная трата валюты (lost update).** `src/Repository/InventoryRepository.php:850-881`: стоит `SELECT ... FOR UPDATE`, но метод вызывается и **вне** транзакции (`src/Repository/TrainingRepository.php:57,123`, `src/Game/NpcDialogService.php:1214`). В autocommit `FOR UPDATE` не блокирует, остаток считается в PHP (`$newCount = $rowCount - $take;`) и пишется абсолютным значением → два параллельных списания читают один `count` и оба записывают одинаковый остаток.
2. **Покупка в магазине тренировок без транзакции.** `src/Repository/TrainingRepository.php:142-163 buy()`: `removeItem($userId, $currency, $total)` (`:152`), затем `addItem($userId, $itemId, $count)` (`:156`) — два автокоммита. Сбой между ними = списанная валюта без товара.
3. **Многошаговые NPC-операции без транзакции, результат `removeItem()` игнорируется**: `src/Game/NpcDialogService.php:389-391` (квест Билли), `:1080-1091` (Кэрол), `:1232-1244` (секретарь репутации).
4. **`jobTemporaryItems()` без транзакции**: `src/Repository/BackgroundJobRepository.php:189` — три записи (DELETE `items_users`, DELETE `items_poke`, UPDATE `player_boosts`) без обёртки.
5. Немые `catch`: `src/Repository/InventoryRepository.php:296-299` и `src/Game/NpcDialogService.php:1221` — `rollBack()` без логирования причины.

## Что сделать

1. Списание количества сделать атомарным на уровне SQL: `UPDATE items_users SET count = count - :take WHERE id = :id AND count >= :take` + проверка `rowCount() === 1`; при 0 — вернуть «недостаточно предметов», а не писать абсолютное значение. Сохранить `FOR UPDATE` как дополнительную защиту внутри транзакций.
2. `TrainingRepository::buy()` обернуть в существующий `withTransaction()` (`:225`), внутри проверять результат списания.
3. NPC-блоки (`:389`, `:1080`, `:1232`) обернуть в транзакции по образцу уже существующих в этом файле; проверять bool-результат `removeItem()` и прерывать операцию при неудаче.
4. `jobTemporaryItems()` — в транзакцию.
5. Логировать причину в двух «немых» `catch` (в проекте уже есть лог-хелперы/таблицы; использовать существующий стиль, не изобретать новый).

## Границы

- Нельзя: менять схему БД; трогать комиссионный рынок (`CommissionMarketRepository` уже корректен); трогать `views/**`, `public/**`, `public/js/**`, `public/css/**`.
- Не менять поведение успешных сценариев: суммы, порядок начислений, тексты ответов.

## Критерии приёмки

1. `php -l` на все изменённые файлы — чисто.
2. Демонстрация атомарности: параллельный/повторный вызов списания при остатке 1 не уводит `count` в минус и не выдаёт предмет дважды (в отчёте — команда и результат, состояние строки в БД).
3. `TrainingRepository::buy` при ошибке выдачи не оставляет списанной валюты (проверить имитацией: показать, что при исключении транзакция откатывается).
4. `tools/open_test_regression.php --profile=quick` → 24/24 (обязательно: `inventory_held_items` 16/16, `reward_pipeline` 13/13, `background_jobs` 10/10 внутри прогона).
5. `tools/db_integrity_smoke.php --json` → `P0=0, P1=0`.

## Доказательства

- Diff по сути (ключевые SQL/транзакции).
- Команды проверок и их вывод.
- Описание, как именно проверялась гонка (какой сценарий воспроизводился).
