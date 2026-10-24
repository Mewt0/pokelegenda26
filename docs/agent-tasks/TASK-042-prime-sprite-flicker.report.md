# TASK-042 — prime sprite flicker

Статус: done

## Изменённые файлы

| Файл | Что изменено |
|---|---|
| `public/js/game-start-runtime.js` | `setSpriteWithFallback()` кеширует выбранный рабочий `src` на самом `img` через `data-sprite-key`, `data-sprite-resolved-src`, `data-sprite-pending` и не переприсваивает GIF при повторной перерисовке того же покемона/стороны/цепочки кандидатов. |

## Доказательство причины

Причина мерцания была не в отсутствии основных GIF-ассетов, а в повторном присваивании `img.src` при каждом `renderBattleSprites()`: браузер заново перезапускал GIF, даже когда показывался тот же праймал на той же стороне боя.

Для `enemyImg` обычного не-shiny праймала `renderBattleSprites()` строит такую цепочку кандидатов:

| basenum | Последовательность `src`, присваиваемых одному `img` за один ход до успешной загрузки |
|---:|---|
| `5017` | `/Pok/spriteanim/5017.gif` → `/Pok/pok/5017.gif` → `/Pok/anim/5017.gif` → `/Pok/normal/5017.png` |
| `5018` | `/Pok/spriteanim/5018.gif` → `/Pok/pok/5018.gif` → `/Pok/anim/5018.gif` → `/Pok/normal/5018.png` |
| `5019` | `/Pok/spriteanim/5019.gif` → `/Pok/pok/5019.gif` → `/Pok/anim/5019.gif` → `/Pok/normal/5019.png` |

Для `playerImg` обычного не-shiny праймала цепочка другая, потому что сначала берётся back sprite:

| basenum | Последовательность `src`, присваиваемых одному `img` за один ход до успешной загрузки |
|---:|---|
| `5017` | `/Pok/back/5017.gif` → `/Pok/anim/5017.gif` → `/Pok/pok/5017.gif` → `/Pok/normal/5017.gif` → `/Pok/normal/5017.png` → `/Pok/normal/5017.jpg` |
| `5018` | `/Pok/back/5018.gif` → `/Pok/anim/5018.gif` → `/Pok/pok/5018.gif` → `/Pok/normal/5018.gif` → `/Pok/normal/5018.png` → `/Pok/normal/5018.jpg` |
| `5019` | `/Pok/back/5019.gif` → `/Pok/anim/5019.gif` → `/Pok/pok/5019.gif` → `/Pok/normal/5019.gif` → `/Pok/normal/5019.png` → `/Pok/normal/5019.jpg` |

Физическая проверка файлов показала: каталога `public/Pok` в рабочем дереве нет; браузерные URL `/Pok/**` соответствуют корневому каталогу `pok/**`. Реально существуют:

```text
pok\anim\5017.gif
pok\anim\5018.gif
pok\anim\5019.gif
pok\back\5017.gif
pok\back\5018.gif
pok\back\5019.gif
pok\pok\5017.gif
pok\pok\5018.gif
pok\pok\5019.gif
pok\sback\5017.gif
pok\sback\5018.gif
pok\sback\5019.gif
pok\shiny\5017.gif
pok\shiny\5018.gif
pok\shiny\5019.gif
pok\spriteanim\5017.gif
pok\spriteanim\5018.gif
pok\spriteanim\5019.gif
```

PNG/JPG fallback для `pok\normal\5017`, `5018`, `5019` не найден. Это не ломает основной сценарий, потому что GIF-кандидаты выше существуют.

Живой PvE-бой глазами не проверялся. Это честный статус проверки: причина подтверждена цепочкой `src`, наличием ассетов и зелёными автоматическими проверками, но визуальный ручной прогон боя не выполнялся, чтобы не делать лишних мутаций боевого состояния.

## Команды

```powershell
node --check public/js/game-start-runtime.js
php tools\open_test_regression.php --profile=quick --login=Tacos --password=...
php tools\http_smoke.php --login=Tacos --password=...
```

## Вывод

```text
node --check public/js/game-start-runtime.js
```

```text
Passed: 25, failed: 0, skipped: 0
```

```text
Result: 52 checks, 0 failures.
```

## Риски

- Ручной визуальный PvE-прогон не выполнялся.
- Если серверный alias `/Pok/**` будет изменён, нужно отдельно проверить отдачу корневого `pok/**`.

## Что осталось

- По желанию владельца: ручная визуальная проверка живого PvE-боя с 5017/5018/5019 после подготовки безопасного QA-состояния.
