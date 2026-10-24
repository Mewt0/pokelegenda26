# AGENTS.md — правила работы в проекте PokemonChic

Браузерная игра: PHP 8.1 + MySQL + vanilla JS/CSS. В репозитории одновременно работают **два ИИ-агента**:

- **DSH-агент** (`deepseek-harness`, GUI на `127.0.0.1:3080`) — владелец дизайн-рефреша и плана.
- **Codex-агент** (`codex exec`, `~/.codex`) — исполнитель задач из очереди.

Файл читается обоими агентами до начала работы. Нарушение границ владения = сломанная чужая работа.

## 1. Источник правды

1. `PROJECT_CONTEXT.md` — текущая архитектура и правило «сначала читать этот файл».
2. Живой код.
3. `DESIGN_REFRESH_AND_BUGFIX_PLAN.md` — план дизайн-рефреша и багфикса (владелец — DSH-агент).
4. `docs/agent-tasks/` — очередь задач и протокол обмена.

`REWRITE.md`, старые QA-отчёты, `MODERNIZATION_REPORT_2026.md` — исторические, не источник истины (`PROJECT_CONTEXT.md:7-15`).

## 2. Окружение и проверки

- Сайт: `http://pokemonchic.com` (OSPanel, Apache + PHP 8.1, MySQL-5.5 на порту 3308).
- PHP CLI: `E:\OSPanel\modules\php\PHP_8.1\php.exe`.
- Быстрая регрессия: `php tools\open_test_regression.php --profile=quick --login=Tacos --password=...` → ожидается **24/24**.
- Read-smoke: `php tools\http_smoke.php --login=Tacos --password=...` → **52/52**.
- Целостность: `php tools\db_integrity_smoke.php --json` → `P0=0, P1=0`.

## 3. Границы владения файлами

| Путь | Владелец | Комментарий |
|---|---|---|
| `public/css/**`, `public/fonts/**`, `public/js/**`, `views/**` | **DSH-агент** | Дизайн-рефреш. Codex правит только по явной задаче |
| `DESIGN_REFRESH_AND_BUGFIX_PLAN.md`, `docs/agent-tasks/TASK-*.md` (спеки) | **DSH-агент** | Codex пишет только `TASK-*.report.md` |
| `tools/**`, `docs/**` (кроме спек), `README*.md`, `PROJECT_STRUCTURE_RU.md`, `PROJECT_MAP_RU.md`, `MODERNIZATION_REPORT_2026.md`, `log_php_errors.txt` | **Codex-агент** | Документация и QA-инструменты |
| `src/**`, `public/index.php`, `database/migrations/**` | только по конкретной задаче | Вне своей задачи — не трогать |
| `include/**`, `game.php`, корневые `*.php`, `admin/**`, `css/**` | никто | Legacy, только чтение как справочник поведения |

Файлы, изменённые другим агентом и не закоммиченные, трогать нельзя.

## 4. Обмен задачами

1. DSH-агент кладёт спеку: `docs/agent-tasks/TASK-NNN-краткое-имя.md`.
2. Codex забирает задачу со статусом `todo` (см. `docs/agent-tasks/BOARD.md`), ставит `in-progress`, выполняет.
3. Codex пишет отчёт `docs/agent-tasks/TASK-NNN-краткое-имя.report.md`: что сделано, какие файлы, какие команды, какой результат, что осталось/риски.
4. DSH-агент проверяет (читает diff, запускает свои проверки), ставит `done` или `rework` с замечаниями в том же report-файле (секция `## Review`).

Запуск Codex-агента (выполняет владелец машины или DSH-агент):

```powershell
$codex = 'C:\Users\grut\AppData\Local\OpenAI\Codex\bin\5ea220ae823df3d7\codex.exe'
& $codex exec -C 'E:\OSPanel\domains\pokemonchic.com' -s workspace-write `
  -o 'E:\OSPanel\domains\pokemonchic.com\docs\agent-tasks\TASK-001-asset-registry-check.report.md' `
  'Выполни docs/agent-tasks/TASK-001-asset-registry-check.md'
```

Для продолжения той же сессии — `codex exec resume --last "<дополнение>"`; для работающей интерактивной сессии — `codex queue "<сообщение>"`.

### Прямой мост между агентами

`docs/agent-tasks/bridge.ps1` — постоянный канал: он держит одну сессию Codex и переписывается с ней без участия человека.

| Каталог / файл | Смысл |
|---|---|
| `bus/inbox/*.md` | сообщения DSH-агента для Codex |
| `bus/outbox/*.reply.md` | ответы Codex |
| `bus/state.json` | id сессии, обработанные сообщения, время сброса лимита |
| `bus/bridge.log` | журнал обмена |

Команды: `-Ask "<вопрос>"` (синхронный вопрос-ответ), `-Send "<текст>"`, `-Run` (обработать inbox), `-Watch` (цикл), `-Status`, `-ProcessQueue -Tasks 010,011,...` (прогнать задачи по порядку).

**Codex-агенту:** если по ходу задачи нужен ответ DSH-агента или требуется тронуть файл из чужой зоны — не гадай: допиши вопрос в свой отчёт `TASK-NNN-*.report.md` в раздел `## Вопросы` и заверши задачу со статусом `blocked`. DSH-агент читает отчёты и отвечает через `bus/inbox`.

## 5. Definition of Done

- `php -l` на каждый изменённый PHP-файл — без ошибок.
- Прогнана релевантная проверка (smoke/lint/скриншот), результат приведён в отчёте.
- Изменения только внутри разрешённой зоны задачи.
- Отчёт `TASK-*.report.md` заполнен: файлы, команды, результат, риски.

## 6. Жёсткие запреты

- Не запускать мутирующие smoke параллельно (локи и duplicate key, `REWRITE_STATUS.md:43,45`).
- Не делать мутирующих операций с БД без предварительного `tools\beta_backup.php`.
- Не коммитить и не откатывать чужие изменения; не выполнять `git checkout -- .`, `git reset --hard`, `git clean -fd`.
- Не строить новую функциональность на legacy (`include/**`, `game.php`) — только `src/**` + `/api/*`.
- Не удалять и не «чистить» `log_php_errors.txt`, `plan.md` без отдельной задачи.
- Не менять `public/css/**`, `views/**`, `public/js/**` без задачи от DSH-агента (идёт дизайн-рефреш).
