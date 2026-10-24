# Dead-code frontend check, 2026-10-07 (завершено 2026-10-10)

Status: **done** — 13 файлов удалены, ссылок в живом коде не осталось, гейт и smoke зелёные.

Проверку выполнил Codex по `docs/agent-tasks/TASK-021-dead-code-frontend.md`; удаление и приёмку — DSH-агент 2026-10-10. Каждое имя файла перепроверено поиском по `public/index.php`, `views/**`, `public/js/**`, `public/css/**`, `src/**`, `script/**` уже **после** удаления: совпадений нет.

| File | Evidence | Action |
|---|---|---|
| `public/css/battle-dock-prototype.css` | имя файла: 0 совпадений в живом коде | удалён |
| `public/js/battle-dock-prototype.js` | имя файла: 0; хост `battleDockPrototype` встречался только внутри самого прототипа | удалён |
| `public/prototypes/battle-dock-prototype.php` | имя файла: 0 | удалён |
| `public/css/market-items.css` | подключался только из удаляемого `views/game-market-items.php` | удалён |
| `public/js/market-items.js` | подключался только из удаляемого `views/game-market-items.php` | удалён |
| `views/game-market-items.php` | `View::render('game-market-items')` нет, `/game/market/items` редиректится | удалён |
| `views/game-pokemon-market.php` | рендера нет, `/game/market/pokemon` редиректится на лавку | удалён |
| `views/example-modern-layout.html` | демо-макет, ссылался на несуществующие маршруты | удалён |
| `script/autGo.js` | имя файла: 0 | удалён |
| `script/draganddrop.js` | имя файла: 0 | удалён |
| `script/event.js` | имя файла: 0 | удалён |
| `script/jquery2.js` | имя файла: 0 | удалён |
| `script/poke_spa.js` | имя файла: 0 | удалён |

Хосты-классы: `.battle-dock-prototype` — 0, `.market-items` — только внутри удалённых файлов, `#battleDockPrototype` — 0.

## Что осталось и почему

`script/jquery.js`, `script/md5.js`, `script/textJs.js`, `script/autr.js`, `script/map.js`, `script/pkmnBattle.js` — используются legacy-страницами (`include/files/shapka.php`, `admin/shapka.php`). **Важно:** `script/autr.js` определяет собственную функцию `autGo()` и остаётся подключённым, поэтому удаление `script/autGo.js` ничего не ломает — функция живёт в `autr.js`.

Живые совпадения по подстрокам проверены отдельно и к удалённым файлам не относятся: `/api/admin/market-items` в `public/index.php` и `public/js/admin-panel.js` — это маршрут API, не файл.

## Проверки

```
Passed: 25, failed: 0, skipped: 0, duration: 23603 ms
Result: 52 checks, 0 failures.
[OK] battle.cleanup.state - pve=0,pvp=0,trade=0,battleid=0
```

## git status (удаления)

```
D  public/css/market-items.css
D  public/js/market-items.js
D  script/autGo.js
D  script/draganddrop.js
D  script/event.js
D  script/jquery2.js
D  script/poke_spa.js
D  views/example-modern-layout.html
D  views/game-market-items.php
D  views/game-pokemon-market.php
```

Три untracked-файла (`public/css/battle-dock-prototype.css`, `public/js/battle-dock-prototype.js`, `public/prototypes/battle-dock-prototype.php`) удалены с диска, в `git status` не отражаются, потому что не были под версионным контролем. Коммит делает владелец.
