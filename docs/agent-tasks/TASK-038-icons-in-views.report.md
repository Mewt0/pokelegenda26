# TASK-038 icons in views report

## Статус

done

## Что сделано

- Прочитан `docs/agent-tasks/VISUAL_REWRITE_BRIEF.md` полностью.
- Проверены доступные sprite-классы в `public/css/icons.css` и сопоставление в `VISUAL_ASSETS_TODO.md`.
- В `views/game-start.php` заменены очевидные символы-заглушки на `<span class="icon icon--NAME" aria-hidden="true"></span>`:
  - inventory overlay toolbar: `icon--ui-settings`, `icon--ui-mode-general`, `icon--ui-pve-on`;
  - attacks tab: `icon--chat-tab-battle`;
  - escape button: `icon--ui-pve-off`.
- В `views/game-items.php` заменены toolbar/search placeholders:
  - `icon--ui-settings`, `icon--ui-mode-general`, `icon--ui-pve-on`, `icon--chat-clear`.

## Изменённые файлы

- `views/game-start.php`
- `views/game-items.php`
- `docs/agent-tasks/TASK-038-icons-in-views.report.md`

`views/game-pokemon.php`, `views/game-events.php` и `views/game-tournaments.php` проверены по списку символов раздела A: подходящих замен из текущего спрайта не найдено, правок не вносил.

## Команды и результат

```powershell
Select-String -Path views/game-start.php,views/game-pokemon.php,views/game-items.php,views/game-events.php,views/game-tournaments.php -Pattern '[▣⚑⚔◈○↻▶★✦✕☻⇩✧✓☆⚙]'
```

После правок остались только символы без подходящего sprite-класса:

- `views/game-start.php`: `⚑` для Report bug, `▶` для повтора боя, `↻` для смены покемона, `▣` для предметов, `○` для покеболов.
- `views/game-items.php`: `✦`, `⚑` в toolbar без понятного назначения.
- `views/game-pokemon.php`: `⇩` download, `▣` lock, `✦`/`★` tile markers, `☻` meta/avatar marker.

```powershell
& 'E:\OSPanel\modules\php\PHP_8.1\php.exe' -l views\game-start.php
```

`No syntax errors detected in views\game-start.php`

```powershell
& 'E:\OSPanel\modules\php\PHP_8.1\php.exe' -l views\game-items.php
```

`No syntax errors detected in views\game-items.php`

```powershell
git diff --stat -- views/game-start.php views/game-pokemon.php views/game-items.php views/game-events.php views/game-tournaments.php
```

Рабочее дерево уже содержало незакоммиченные изменения в пяти view-файлах до начала задачи, поэтому общий stat включает чужие/предыдущие правки:

```text
views/game-events.php      |  67 +---
views/game-items.php       | 103 +++----
views/game-pokemon.php     | 746 +--------------------------------------------
views/game-start.php       | 148 ++++-----
views/game-tournaments.php |  86 ++----
5 files changed, 157 insertions(+), 993 deletions(-)
```

## Не запускалось

- `tools/open_test_regression.php` не запускался по прямому указанию задачи: gate прогонит DSH-агент.

## Риски и что осталось

- В текущем спрайте нет отдельных иконок для report bug, replay/play, download, lock, pokemon switch, inventory items, pokeballs, starter/star marker и meta/avatar marker. Эти символы оставлены без добавления новых sprite-классов.
- До начала работы все пять целевых view-файлов уже были modified в рабочем дереве; я не откатывал и не трогал чужие изменения вне точечных замен символов.
