# TASK-021 — Мёртвый код фронтенда и расчистка дублей

**Исполнитель:** Codex-агент · **Зона (явно разрешена):** `public/css/battle-dock-prototype.css`, `public/js/battle-dock-prototype.js`, `public/prototypes/battle-dock-prototype.php`, `public/css/market-items.css`, `public/js/market-items.js`, `views/game-market-items.php`, `views/game-pokemon-market.php`, `views/example-modern-layout.html`, `script/autGo.js`, `script/draganddrop.js`, `script/event.js`, `script/jquery2.js`, `script/poke_spa.js`, `docs/**` · **Статус:** todo

> Зона `views/**` и `public/js/**` принадлежит DSH-агенту. Эта задача — явное исключение строго на перечисленные файлы. **Не удаляй и не меняй ничего другого**, в том числе `public/css/fonts.css`, `public/css/tokens.css`, `public/js/game-start-runtime.js`, `views/game-start.php`.

## Что удалить (доказано сканом фронтенда)

| Файл | Доказательство мёртвости |
|---|---|
| `public/css/battle-dock-prototype.css`, `public/js/battle-dock-prototype.js`, `public/prototypes/battle-dock-prototype.php` | Ни один view/`public/index.php` не подключает файлы; хост-`id="battleDockPrototype"` отсутствует в проекте; переменные `--bd-*` используются только внутри самого прототипа |
| `public/css/market-items.css`, `public/js/market-items.js`, `views/game-market-items.php` | JS подключён только из этого view; view не рендерится: `/game/market/items` редиректится (`src/Controller/GameModuleController.php:59-61`), вызова `View::render('game-market-items')` нет |
| `views/game-pokemon-market.php` | `/game/market/pokemon` редиректится (`src/Controller/GameModuleController.php:125-127`), рендера нет |
| `views/example-modern-layout.html` | Демо-макет, ссылается на несуществующие маршруты (`/about`, `/privacy`, `/rules`, `/support`, `/contact`) |
| `script/autGo.js`, `script/draganddrop.js`, `script/event.js`, `script/jquery2.js`, `script/poke_spa.js` | 0 ссылок по всему репозиторию |

## Порядок работы

1. **Перед удалением перепроверь сам** (не верь отчёту на слово): `grep` по `public/index.php`, `views/**`, `public/js/**`, `src/**` на имя каждого файла. Если хоть одна живая ссылка найдётся — файл не удаляй, зафиксируй в отчёте.
2. Удаляй `git rm` (или обычным удалением, но не коммить — коммит делает владелец).
3. Убедись, что не осталось ссылок в CSS/JS на удалённые классы-хосты.
4. В `docs/reports/dead-code-frontend-2026-10-07.md` запиши: что удалено, чем доказано, что проверено и оставлено (например `script/jquery.js`, `script/md5.js`, `script/textJs.js`, `script/autr.js`, `script/map.js`, `script/pkmnBattle.js` используются legacy-страницами и не удаляются).

## Границы

- Нельзя: трогать `src/**`, `public/index.php`, `database/**`, живые CSS/JS игрового шелла, `include/**`, `game.php`, `admin/**`, `css/**`.
- Нельзя коммитить.

## Критерии приёмки

1. Перечисленные файлы удалены, `grep` по их именам в живом коде — 0 совпадений.
2. `php tools\open_test_regression.php --profile=quick --login=Tacos --password=...` → **24/24** (удаление не должно ничего сломать).
3. `php tools\http_smoke.php --login=Tacos --password=...` → 52/52.
4. Отчёт `docs/reports/dead-code-frontend-2026-10-07.md` создан и содержит таблицу «файл — доказательство — действие».
5. В отчёте задачи — `git status` (список удалённых путей).

## Доказательства

- Вывод `grep` до удаления.
- Вывод обеих проверок.
- `git status --short`.
