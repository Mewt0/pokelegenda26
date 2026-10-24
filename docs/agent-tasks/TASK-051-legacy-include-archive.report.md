# TASK-051 — отчёт: перенос `include/` в архив **не выполняется**, посылка задачи опровергнута

**Исполнитель проверки:** DSH-агент · **Дата:** 2026-10-11
**Статус:** закрыто как неприменимое (`done` с обратным выводом), файлы не перемещались

## Что утверждала задача

«`include/**` — 27 560 строк мёртвого веса; новый движок NPC читает из легаси один файл
`include/data.world.php`; остальное в runtime не участвует — вынести в архив вне проекта».

## Что показала проверка

Посылка неверна. `include/**` — живой легаси-код, на котором стоят работающие страницы.

**1. Легаси-входы отвечают по HTTP (проверено запросами):**

| URL | Ответ |
|---|---|
| `/` (корневой `index.php`) | 200, 12 656 байт |
| `/game.php` | 200 |
| `/ban.php` | 200 |
| `/mailTo.php` | 200, 364 байта |
| `/pInf.php` | 200 |
| `/robotsms.php` | 200 |
| `/attak_pokes.php` | 200, 178 байт |
| `/events.php` | 410 (намеренно закрыт, `TASK-031`) |

**2. Корневой `index.php` — это страница `/`** (`AGENTS.md`, запись в `BOARD.md`), и он
подключает `include/function/config.php`, `include/function/db3.php`,
`include/function/globfanction.php`, `include/function/functionusers.php`,
`include/class/index.class.php`. `game.php` подключает ещё 30+ файлов `include/files/**`,
включая `char.world.php`, `chat.world.php`, `fight_pve.world.php`, `fight_pvp.world.php`.

**3. Комнаты и NPC подключаются динамически:**

* `include/files/char.world.php:64` и `char.work.php:73` строят путь
  `"include/rooms/".$buildmy.".php"` из данных игрока — то есть весь каталог
  `include/rooms/**` живой;
* `include/rooms/*.php` подключают NPC **относительным** путём
  (`include ("npc/npc_13.php")`), поэтому весь каталог `include/rooms/npc/**` тоже живой.

**4. Часть файлов, на которые нет ссылок в коде, отдаётся напрямую по URL** — то есть
«не упомянут в коде» ≠ «мёртвый»:

| URL | Ответ |
|---|---|
| `/include/files/registration.php` | 200, 15 271 байт (страница регистрации) |
| `/include/files/rulles.php` | 200, 8 813 байт (правила) |
| `/include/files/sendpass.php` | 200 (восстановление пароля) |
| `/include/files/fight_pvp.view.php` | 200 |
| `/include/rooms/npc/npc_15.php` | 200 |
| `/include/function/poke_anim.php` | 403 (закрыт конфигом) |

Перенос `include/**` в архив отдал бы 404 на регистрацию, правила, восстановление пароля
и часть страниц NPC. Риск для живого сайта — прямой, выгода — только в поиске по коду.

## Что сделано вместо переноса

1. **`tools/include_reachability.php`** — инструмент достижимости легаси-дерева. Учитывает
   все три способа подключения: литеральный путь (`__DIR__ . '/include/...'`),
   относительный (`include ("npc/...")` внутри `include/rooms/`) и динамически
   собираемый путь (`"include/rooms/".$buildmy.".php"`). Консервативен: файл считается
   мёртвым только если его вообще нигде не упоминают.
   Текущий вывод: **240 файлов, 229 достижимо, 11 без упоминаний** (и они всё равно
   отдаются по URL, см. выше).
2. Список 11 файлов без упоминаний (`include/class/game.class.php`,
   `include/files/fight_pvp.view.php`, `include/files/pokedex - копия.php`,
   `include/files/registration.php`, `include/files/rulles.php`,
   `include/files/sendpass.php`, `include/function/poke_anim.php`,
   `include/rooms/npc/npc_15.php`, `npc_event.php`, `npc_konditer.php`, `shop_events.php`)
   оставлен на месте: удаление каждого — решение владельца, а не следствие статического
   анализа.

## Что нужно от владельца

Если освобождать поиск всё же нужно, единственный безопасный путь — не переносить каталог,
а **закрыть легаси сам по себе**: убрать `game.php`/корневой `index.php` из обслуживания
(перевести `/` и игровые URL на `public/index.php`), после чего `include/**` действительно
станет мёртвым и его можно будет вынести одним движением. Это отдельная задача уровня
`TASK-030`/`TASK-031`, и начинать её надо с переписывания входа, а не с переноса файлов.

## Проверки

* `php -l tools/include_reachability.php` — чисто;
* `php tools/include_reachability.php` — 240 / 229 / 11 (см. выше);
* HTTP-пробы легаси-входов и файлов `include/**` — таблицы выше;
* файлы проекта не перемещались и не удалялись, `include/data.world.php` на месте.
