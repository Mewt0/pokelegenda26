# План: освежение дизайна и охота на баги (PokemonChic)

Составлен: 2026-10-07. Основание: живой код, живые данные БД, прогон регрессии, инвентаризация markdown-документов проекта.

Область: новый слой (`public/index.php`, `src/**`, `views/**`, `public/js/**`, `public/css/**`, `/game`), без переписывания legacy (`include/**`, `game.php`, корневые `*.php`) — legacy остаётся справочником поведения, как и требует `PROJECT_CONTEXT.md`.

---

## 0. Что уже проверено (ground truth на момент составления)

| Проверка | Результат |
|---|---|
| `tools/open_test_regression.php --profile=quick` | **24/24 блока, 0 failed**, 39 с |
| `tools/http_smoke.php` | **52 проверки, 0 падений** |
| Миграции | 79/79, pending=0, dirty=0, failed=0 |
| DB integrity | P0=0, P1=0, WARN=0, ACCEPTED=4 |
| Сайт | `http://pokemonchic.com/` → HTTP 200, Apache, PHP 8.1 |
| Таблиц в БД | 150 |
| `item_gameplay_metadata.effect_status` | implemented 42, **visual_only 17, todo 11** |

Вывод: бэкенд в хорошей форме, «сломанного» ядра нет. Баг-хантинг надо вести там, где smoke не смотрит: фронтенд, дизайн-система, UX-состояния, edge cases, legacy-хвосты, документация.

---

## 1. Цель и критерии готовности

**Цель:** игра выглядит собранной и современной, каждый экран предсказуем на 360…1920 px, ни один функциональный блок не сломан, известные баги закрыты или задокументированы.

Критерии приёмки (Definition of Done):

1. Дизайн-токены — единый слой, ни один компонент не задаёт цвет/радиус/тень «руками» вне токенов.
2. Уникальных цветов в новом слое ≤ 60 (сейчас 655 hex + 232 rgba).
3. Шкала z-index — 1 документированная лестница (сейчас 25 произвольных значений до 150000).
4. Брейкпоинты — 6 фиксированных (сейчас 14 разных).
5. Типографика реально загружается (сейчас шрифт Inter/Manrope запрошен, но не подключён ни одним `@font-face` и не подгружается извне → рендерится Tahoma/Arial).
6. `quick`-регрессия остаётся 24/24 после каждого этапа, `full` — перед релизом.
7. Все экраны пройдены скриншот-QA на 390 / 768 / 1280 / 1920 px без horizontal overflow.
8. Баг-лист P0/P1 закрыт, P2 — с решением «чиним / откладываем осознанно».

---

## 2. Текущее состояние: факты

### 2.1. Фронтенд

| Метрика | Значение |
|---|---|
| CSS-файлов | 15 (≈185 КБ, ~9 000 строк) |
| JS-файлов | 15 (≈490 КБ, ~10 000 строк) |
| Крупнейшие | `game-start-runtime.js` 2 465 строк, `admin-panel.js` 2 116, `game-shell.css` 1 207, `game-start.css` 1 015, `game-battle-dock.css` 1 024 |
| CSS-переменных | **37**, при этом в трёх несовместимых системах: `--ui-*`, `--bd-*`, «голые» `--bg/--panel/--text/--muted/--line/--accent` |
| Уникальных hex-цветов | **655** |
| Уникальных `rgba()` | **232** |
| `!important` | 19 (в 8 файлах) |
| z-index значений | **25**: 0,1,2,3,4,5,80,82,90,120,140,150,260,1200,2400,70000…150000 |
| Брейкпоинтов | **14**: 520,560,620,640,720,760,840,900,920,980,1100,1180,1240,1320 |
| `@font-face` / ссылок на шрифты | **0** (шрифтовая пачка `Inter, Manrope, Nunito Sans` не подключена) |
| Инлайновых `style="` во views | 14 |
| Инлайновых `<script>` во views | 10 |
| `<img>` без `alt` | 10 из 49 |
| `<button>` без `type` | 4 из 147 |
| `aria-*` во views | 68 вхождений (частично есть) |
| Мёртвые файлы | `public/css/battle-dock-prototype.css` (539 строк), `public/js/battle-dock-prototype.js` (520), `public/prototypes/battle-dock-prototype.php` — **не подключены нигде** |
| Ассеты из `VISUAL_ASSETS_TODO.md` | на диске **8 из 42**, отсутствуют **34** |
| Лендинг `/` (`views/home.php`) | 21 КБ HTML, **0 внешних CSS/JS**, один инлайновый `<style>` со своей четвёртой системой токенов (`--bg/--surface/--line/--text/--muted/--blue #1d66c2/--green/--amber/--red/--shadow`) |
| Гость на `/game` | получает ту же страницу, что и `/` (HTTP 200), без явного приглашения войти |

Наблюдения из скриншота текущего окна комиссионной лавки (JS/Chrome, 499×1085): вложенные скроллбары внутри панели; кнопки в одном ряду с разным регистром («ВЕРНУТЬСЯ» vs «Сброс», «Обновить»); игроку показывается техническое `Статус эффекта: unknown` и пустые значения «Не указана».

### 2.2. Бэкенд

| Метрика | Значение |
|---|---|
| Таблиц | 150 |
| Миграций | 79 (все применены) |
| Контроллеров / репозиториев / сервисов | 33 / 36 / 16 |
| Маршрутов в `public/index.php` | 178 |
| Предметов без реального эффекта | **28** (11 `todo` + 17 `visual_only`) |
| Пустые рабочие таблицы (dev) | `admin_tournaments`, `economy_guard_alerts`, `safe_storage_entries`, `mail_delivery_logs` |

---

## 3. Что в игре НЕ реализовано (по markdown-файлам проекта)

Сводка по `PROJECT_CONTEXT.md`, `PROJECT_MAP_RU.md`, `PROJECT_STRUCTURE_RU.md`, `BETA_RELEASE_TASKS.md`, `plan.md`, `REWRITE.md`, `REWRITE_STATUS.md`, `MODERNIZATION_REPORT_2026.md`, `VISUAL_ASSETS_TODO.md`, `ITEM_LOGIC_AUDIT.md`, `GAME_EVENTS_MIGRATION_PLAN.md`, QA-отчётам.

### 3.1. Крупные незакрытые системы

| Система | Что именно не сделано | Источник |
|---|---|---|
| Кланы | Не реализованы вообще, в `GameRoutes::MODULES` остаётся `todo` (4 модуля из 27) | `OPEN_TEST_REGRESSION_2026-05-27.md:101`, `REWRITE_STATUS.md:227,522`, `BETA_RELEASE_TASKS.md:289` |
| Турниры | Нет сетки, matchmaking, bracket UI, показа победителя, replay внутри турнира | `REWRITE_STATUS.md:196,228`, `OPEN_TEST_REGRESSION_2026-05-27.md:100,115` |
| Battle replay | Есть v1 (запись), нет визуального пошагового плеера, фильтров, экспорта, retention-политики | `REWRITE_STATUS.md:229`, `BETA_RELEASE_TASKS.md:291` |
| События | Нет таблиц `game_event_scopes`, `game_event_rewards`; личные бусты не сведены в общий resolver; `quest_rewards` не проверены end-to-end | `GAME_EVENTS_MIGRATION_PLAN.md:71-80,90,130` |
| Почта | Не открывается как игровой overlay, нет тредов и восстановления из архива | `BETA_RELEASE_TASKS.md:226`, `REWRITE_STATUS.md:217,352` |
| Админка | Нет role matrix (admin / game master / moderator / player), нет части safe-actions, «Мастер контента» не даёт все кнопки | `BETA_RELEASE_TASKS.md:285`, `REWRITE_STATUS.md:233,509`, `PROJECT_MAP_RU.md:401` |
| Инфраструктура | Нет CI (API-smoke, browser-regression, asset registry), нет production telemetry, нет прод-чеклиста дампа, не пройден прод SMTP/OSP smoke | `REWRITE_STATUS.md:220,234,236,237,238`, `BETA_RELEASE_TASKS.md:295-297` |
| Мониторинг | Health Dashboard как отдельная система (500/очереди/cron) — только частично в GM Center | `REWRITE_STATUS.md:230`, `README.md:122` |

### 3.2. Бой

- Редкие атаки, не все предметы и долгие edge cases — `REWRITE_STATUS.md:184`.
- Нет `item_effects_json` для уникальных эффектов boss-предметов — `REWRITE_STATUS.md:127`.
- Боевой rollback-path не подтверждён вручную — `REWRITE_STATUS.md:59`.
- Логирование боёв не доведено для PvP/истории — `REWRITE_STATUS.md:311`.
- `stuck_battles` и `transport_flights` — только warning/scan, авто-финиш не реализован — `PROJECT_CONTEXT.md:56`, `REWRITE_STATUS.md:57,231`.
- Master Ball / Premium Ball: шанс ловли явно не подключён — `ITEM_LOGIC_AUDIT.md:121-122`.
- PvP: не проведены долгие реальные 6×6 двумя сессиями — `REWRITE_STATUS.md:185`, `BETA_RELEASE_TASKS.md:73-82`.

### 3.3. Квесты, NPC, локации

- Полные сюжетные цепочки не расширены — `OPEN_TEST_REGRESSION_2026-05-27.md:106`, `REWRITE_STATUS.md:226`.
- Browser-pass цепочки «Рассказ Спайка» не сделан — `BETA_RELEASE_TASKS.md:146`.
- Редкие event/hidden/stadium/craft NPC — нужна отдельная матрица — `OPEN_TEST_REGRESSION_2026-05-27.md:102`, `REWRITE_STATUS.md:226,511`.
- Тонкие legacy-условия проходов и сюжетных веток не перенесены — `REWRITE_STATUS.md:256,257`.
- Story-модуль `NpcDialogService` не вынесен — `PROJECT_STRUCTURE_RU.md:214-216`.
- Нет редактора маршрутов и закрытых локаций, нет полного регионального QA — `REWRITE_STATUS.md:195`.

### 3.4. Предметы и экономика

- `effect_key=pending`/`TODO` у предметов 10, 11, 17, 40-44, 64, 66, 74, 76, 78, 217, 234, 235, 325, 371, 654, 655, 861, 1401, 90003-90005 — `ITEM_LOGIC_AUDIT.md:56-122,139`.
- Не перенесены эффекты: конфеты, камни эволюции, TM-обучение, PP-витамины, покеболы 90004/90005, ордер 90003, крафт — `ITEM_LOGIC_AUDIT.md:144-153`.
- Неполные held items экипируются без эффекта — `REWRITE_STATUS.md:93`.
- Дубли камней 70/71 — «NEEDS DECISION» — `ITEM_LOGIC_AUDIT.md:100-101`.
- Валютные объекты в Комиссионке не поддержаны — `REWRITE_STATUS.md:197,224`.
- Safe storage: нет admin UI просмотра/resolve и auto-restore policy — `REWRITE_STATUS.md:235,306`.
- Балансировка economy/drop/exp отложена до данных beta — `BETA_RELEASE_TASKS.md:298`.

### 3.5. Покемоны, Dex, Trainer Card

- UX-полиш питомника — `REWRITE_STATUS.md:187`, `QA_GAMEPLAY_REPORT_2026-05-25.md:28`.
- Разведение через player popup/invite-flow — в процессе — `PROJECT_CONTEXT.md:85`.
- Не проверены 937 атак, эволюции, hidden/egg moves, missing assets — `REWRITE_STATUS.md:189,275`.
- `gym_badge_battle_rules` заполнены не для всех лидеров — `REWRITE_STATUS.md:302`.

### 3.6. Квесты/Dex/UI — долги

- Декомпозиция крупных файлов не завершена (5 кандидатов) — `PROJECT_STRUCTURE_RU.md:210-226`, `REWRITE_STATUS.md:516`.
- Нет финального ручного прохода по popup/tooltip/scroll — `OPEN_TEST_REGRESSION_2026-05-27.md:99`.
- Мобильные touch/keyboard на реальных устройствах не проверены — там же `:107`.
- Long-session тест 1-3 часа не выполнен — там же `:104,116`.
- Bug Reporter: нет скриншотов, авто-линка replay, SLA — `REWRITE_STATUS.md:202`.
- Dismissible баннеры событий/бонусов — `BETA_RELEASE_TASKS.md:237`.

### 3.7. Известные риски (принятые или открытые)

- Принятый integrity-мусор: `items.orphan_user=38`, `pokemon.invalid_owner=63`, `eggs.invalid_owner=1`, `battle.active_unfinished=208` — `BETA_RELEASE_TASKS.md:13-17`, `PROJECT_CONTEXT.md:64` (нужен отдельный cleanup).
- «API/PHP errors: 12» в GM Center, tail логов не разобран — `OPEN_TEST_REGRESSION_2026-05-27.md:113`.
- Риск потери/дюпа предметов, покемонов и валюты в комиссионке — `BETA_RELEASE_TASKS.md:26-33`.
- Мутирующие smoke нельзя запускать параллельно (lock / duplicate key) — `REWRITE_STATUS.md:43,45`.
- QA-пробелы: живые клик-прогоны в браузере, headless mobile (нет `playwright-core`), двухоконный PvP — `REWRITE_STATUS.md:101,520`, `QA_BATTLE_TRANSFORMATIONS_REPORT_2026-05-26.md:65-67`.

### 3.8. Противоречия в документации (нужно развести)

1. Готовность: `README.md:15-18` (84/74/62/37) vs `REWRITE_STATUS.md:169-174` (87/76/64/38).
2. `README.md:115-122` перечисляет как риски то, что уже сделано (replay, jobs, migration status, health dashboard).
3. `QA_GAMEPLAY_REPORT_2026-05-25.md:47,48` держит OPEN-пункты, которые в `REWRITE_STATUS.md:19,109` закрыты (журнал квестов overlay; питомник до 24 покемонов).
4. `ITEM_LOGIC_AUDIT.md` (десятки `pending`) vs `REWRITE_STATUS.md:129` (эффекты переведены в рабочие) — аудит без даты, вероятно устарел.
5. `PROJECT_STRUCTURE_RU.md:36` называет `GameModuleController` «временными заглушками», хотя модули рабочие.
6. `MODERNIZATION_REPORT_2026.md:4` «✅ Завершено» при чек-листе `:164-177`, где все пункты `[ ]`; файл `MIGRATION_GUIDE_2026.md` отсутствует.
7. `BETA_RELEASE_TASKS.md:12` (WARN=0) vs `OPEN_TEST_REGRESSION_2026-05-27.md:9,112` (WARN=4); внутри `REWRITE_STATUS.md` — `:55` (900 незавершённых боёв) vs `:3,200` (208).

**Решение:** в фазе Ф7 привести документы к одному состоянию, устаревшие отчёты помечать `HISTORICAL`, а не удалять (`PROJECT_CONTEXT.md:211`).

---

## 4. Найденные баги

### 4.1. Мои находки (дизайн-система и ассеты)

| # | Файл | Проблема | Влияние | Фикс |
|---|---|---|---|---|
| D-01 | `public/css/game-shell.css:40` | Запрошен шрифт `Inter, Manrope, Nunito Sans`, но ни одного `@font-face` и внешней ссылки в проекте нет | Игрок видит Tahoma/Arial; весь типографический замысел не работает | Самохостинг Inter (OFL) + `--font-sans`, `font-display: swap` |
| D-02 | все 15 CSS | 655 hex + 232 rgba, три системы токенов | Невозможно поддерживать, цвета «плывут» | Единый слой токенов (§5.1) |
| D-03 | все 15 CSS | 25 значений z-index до 150000 | Модалки/док/тултипы перекрывают друг друга непредсказуемо | Лестница `--z-*` (§5.1) |
| D-04 | все 15 CSS | 14 брейкпоинтов | Адаптив не проверяем | 6 брейкпоинтов (§5.1) |
| D-05 | `public/css/battle-dock-prototype.*`, `public/js/battle-dock-prototype.js`, `public/prototypes/battle-dock-prototype.php` | Прототип лежит в runtime, не подключён нигде | Мёртвый вес, путаница при правках | Удалить (или перенести в `tmp/`) |
| D-06 | `views/components/commission-market-panel.php` | Игроку показывается `Статус эффекта: unknown` и «Не указана» | Утечка технических данных в UI | Скрывать неизвестные поля, показывать человеческое «Эффект не описан» |
| D-07 | `VISUAL_ASSETS_TODO.md` + `public/img/ui` | 34 из 42 запланированных иконок отсутствуют | UI собирается из чего попало | Фаза Ф5 (§5.6) |
| D-08 | `views/home.php` | У лендинга своя, четвёртая система токенов и синий `#1d66c2`, у игрового шелла — `#3f7fff`; всё в одном инлайновом `<style>` | Лендинг и игра выглядят как два разных продукта | Свести к `tokens.css`, вынести стили в `public/css/landing.css` |
| D-09 | `views/home.php:5-8` | Бренд расходится: `<title>League of Pokemons</title>` / «League of Pokemons - браузерная онлайн-игра», при том что проект и игра — PokemonChic / Покелегенда | Путаница в бренде и SEO | Выбрать одно имя, поправить title/description/og |
| D-10 | `public/index.php`, гостевой доступ | `/game`, `/game/quests`, `/game/*` для неавторизованного отдают лендинг (200) вместо формы входа с понятным сообщением | Игрок не понимает, что нужно войти | Редирект на `/login?next=/game/...` + сообщение |

### 4.3. Бэкенд (детальный скан выполнен 2026-10-07)

Проверено: `src/**` (145 файлов), `public/index.php`, `config/**`, `app/**`, `tools/**`, миграции. `php -l` — **все файлы чистые**; SQL-инъекций в новом слое нет (PDO, `EMULATE_PREPARES=false`); CSRF и права проверены во всех POST-экшенах; `declare(strict_types=1)` есть везде; пустых `catch {}` нет.

| # | Проблема | Где | Задача |
|---|---|---|---|
| B-01 | **Подделка прогресса квестов**: `action=quest_update:<id>:<process>:<completed>` из тела POST уходит в БД без валидации → игрок «завершает» любой квест и забирает награду | `src/Game/NpcDialogService.php:150,992` → `src/Repository/QuestRepository.php:50` | TASK-010 |
| B-02 | **IP-бан не работает вообще**: строка `1.2.3.4` сравнивается с `int`-колонкой `banip.ip` (MySQL приводит к `1`) | `src/Repository/BanRepository.php:19`, `src/Security/BanGuard.php:23` | ✅ **исправлено DSH и проверено на боевой БД**: 5 из 5 реальных строк `banip` находятся по точечной форме, незабаненный IP → `false`, пустая строка → `false`; docblock в `BanGuard` перекодирован. Транзакция «бан + audit» и доверие к `X-Forwarded-For` остались в TASK-011 |
| B-03 | **Двойная трата валюты**: `FOR UPDATE` вне транзакции + запись абсолютного остатка из PHP | `src/Repository/InventoryRepository.php:850-881` | TASK-012 |
| B-04 | **Покупка в магазине тренировок без транзакции**: списание и выдача двумя автокоммитами | `src/Repository/TrainingRepository.php:142-163` | TASK-012 |
| B-05 | **Подмена IP через `X-Forwarded-For`** без проверки доверенного прокси (влияет на бан, `users.ip`, аудит) | `src/Support/ClientIp.php:11-21` | TASK-011 |
| B-06 | **Host header injection** в ссылке сброса пароля (`HTTP_HOST`) | `src/Controller/AuthController.php:349` | TASK-013 |
| B-07 | `canStart()` возвращает `true` для несуществующего квеста → мусор в `quest` | `src/Repository/QuestRepository.php:186` | TASK-010 |
| B-08 | Многошаговые NPC-операции без транзакции, результат `removeItem()` игнорируется | `src/Game/NpcDialogService.php:389,1080,1232` | TASK-012 |
| B-09 | Хеш пароля в сессии; `browse`-отпечаток пишется и не читается; секрет по умолчанию `change-me-local-secret`; CSRF-токен не ротируется; `GET /logout` без CSRF | `src/Controller/AuthController.php:78,80,147,359`, `src/Security/Csrf.php:15` | TASK-013 |
| B-10 | **Новые локации недостижимы**: граф переходов строится из legacy-файла, админские локации в него не попадают | `public/index.php:148`, `src/Game/MapMoveService.php:45` | TASK-014 (позже) |
| B-11 | Не работает автоидемпотентность наград: ключ операции содержит `random_bytes(4)` + `time()` | `src/Repository/RewardRepository.php:50-56` | TASK-014 (позже) |
| B-12 | `['ok' => true] + $repo->...` маскирует ошибку репозитория (`+` не перезаписывает ключ) | `src/Controller/AdminApiController.php:595` | TASK-014 (позже) |
| B-13 | Мёртвый код в `src/`: `LegacyRoomDataExtractor` (169 строк), `newPathForLegacy()`, `evolvePokemon()`, `evolutionTargetFor()`, `groupsOverlap()`, неиспользуемый `$state` в `GameController::start` | перечислено в отчёте скана | TASK-015 (позже) |
| B-14 | Пропущены номера миграций 000017, 000020–000023; двойной источник данных `pokemon_forms` (таблица vs `PokemonFormCatalog`); `techwork` читается из `site_settings` дважды за запрос | `database/migrations/**`, `src/Game/PokemonFormCatalog.php`, `public/index.php:127,386` | TASK-015 (позже) |

Полные детали скана — в отчёте исполнителя (сессия сканирования от 2026-10-07); каждая задача выше содержит свои критерии приёмки и обязательный прогон регрессии.

### 4.2. Фронтенд (детальный скан выполнен 2026-10-07)

Проверено машинно: 125 ID из JS ↔ 311 ID во вью, 154 `[data-*]`-селектора, 95 API-путей из JS + 29 из вью ↔ 148 маршрутов, 56 литеральных ссылок на ассеты. **Ни одного «JS зовёт несуществующий маршрут» и ни одной битой ссылки на файл не найдено**; дублей `<script>`/`id`/функций нет; `console.log` нет.

| # | Проблема | Где | Задача |
|---|---|---|---|
| F-01 | **Почта навсегда зависает**: `state.loading` не сбрасывается при ошибке fetch → «Загружаю почту…» до перезагрузки | `public/js/mail-overlay.js:239-246` | TASK-020 |
| F-02 | **Журнал квестов навсегда зависает** по той же причине | `public/js/quest-journal.js:290-295` | TASK-020 |
| F-03 | **Лавка «залипает»**: нет try/finally → кнопки «Купить/Снять/Выставить» остаются `disabled` после сбоя | `public/js/commission-market.js:118,504-525,550-561` | TASK-020 |
| F-04 | `loadState()` без catch: статус залипает на «Загрузка...», каждые 5 с необработанный реджект, локация не обновляется молча | `public/js/game-start-runtime.js:2427-2430` | TASK-020 |
| F-05 | Инвентарь грузится/меняется без catch — тихие отказы | `views/game-items.php:262-268,465` | TASK-020 |
| F-06 | **DOM-XSS**: имя предмета подставляется в `innerHTML` без экранирования | `public/js/game-start-runtime.js:2222` | ✅ исправлено (DSH, 2026-10-07) |
| F-07 | **Повтор боя недостижим**: кнопка создаётся только в недостижимой ветке (гард `:46-51` всегда уходит в `return`) | `public/js/game-start-runtime.js:53-111,135-208` | ✅ исправлено (DSH, 2026-10-07): кнопка `#battleReplayBtn` в шапке боя + привязка в живом пути |
| F-08 | Покемаркет не показывает пагинацию и слоты: `[data-market-page-info]`/`[data-market-slots]` нет ни в одной вью | `public/js/game-market-overlay.js:15-16`, `views/game-start.php:223-228` | ✅ исправлено (DSH, 2026-10-07): оба маркера добавлены в футер магазина |
| F-09 | Нет UI для `/api/friends/decline` и `/api/battle/pvp/accept|decline` — заявку в друзья и PvP-вызов нельзя отклонить | `public/index.php:235,236,376` | ✅ исправлено (DSH, 2026-10-07): кнопка «Отклонить» для входящей заявки в друзья + действие «Отклонить» в уведомлении о PvP-вызове; контракты проверены живыми запросами |
| F-10 | Рынок покемонов недостижим из UI: view не рендерится, `/game/market/pokemon` редиректится в лавку | `views/game-pokemon-market.php`, `src/Controller/GameModuleController.php:125-127` | TASK-021 |
| F-11 | Мёртвый код: `battle-dock-prototype.{js,css}`, `market-items.{js,css}`, `views/game-market-items.php`, `views/game-pokemon-market.php`, `views/example-modern-layout.html`, `script/{autGo,draganddrop,event,jquery2,poke_spa}.js`; в `game-start-runtime.js` — 59 строк дубля разметки боя и `openBugReportModal()` без вызовов | см. отчёт скана | TASK-021 |
| F-12 | Доступность: иконочные кнопки без `aria-label` (`game-start.php:147,150-152,165`, `chat.js:159`, `player-menu.js:838`), диалоги без `aria-modal`, поля логина без label, `row()` в настройках профиля не связывает label и input, смешанный язык «Fight/Pokémon/Items/Balls» | см. отчёт скана | ✅ закрыто (DSH): 6 иконочных кнопок и 5 диалогов в `/game`, подписи боя по-русски, **14 полей и селектов `/game` получили `aria-label`** (не осталось ни одного без имени), поля логина лендинга, декоративные глифы помечены `aria-hidden`, кнопки закрытия в чате и меню игрока, `row()` в настройках профиля теперь связывает `label[for]` ↔ `input[id]` — проверено живьём: 8 подписей, 8 связанных |
| F-13 | Настройки профиля (анимации, звук, размер интерфейса, тема, уведомления) сохраняются, но нигде не применяются | `public/js/profile-settings-modal.js:185-216` | **частично** (DSH, 2026-10-07): новый `public/js/ui-preferences.js` применяет `animations` (`html[data-motion="off"]` + правила в `tokens.css`), реагирует на `profile-settings:saved`; для темы/размера/звука добавлена честная подсказка, что они пока не влияют. Тема, `ui_size`, звук и уведомления — не реализованы |
| F-14 | 7 копий POST-хелпера (`admin-panel.js:942`, `commission-market.js:112`, `mail-overlay.js:264`, `quest-journal.js:114`, `views/game-items.php:257`, `game-pokemon-market.php:110`, `game-tournaments.php:128`); в `admin-panel.js:933-940` спред `...options` после `headers` теряет `Accept`, нет проверки `response.ok` | см. отчёт скана | **частично** (DSH): в `admin-panel.js` заголовки собираются последними, ответ не-2xx превращается в `{ok:false, message}` вместо падения `response.json()` на HTML (проверено: `/api/admin/nonexistent-endpoint` → 404 + HTML). Объединение оставшихся копий в общий хелпер — отдельная задача |
| F-15 | `JSON.parse` без try/catch на серверном конфиге (`game-start-runtime.js:1`, `views/game-tournaments.php:118`) | там же | TASK-020 |
| F-16 | `console.debug('[SCROLL] …')` на каждое событие скролла — спам | `public/js/game-start-runtime.js:1612` | ✅ исправлено (DSH): слушатель с одним `console.debug` удалён (остальные 8 `console.debug` в проекте — информационные трассы QA, оставлены) |
| F-28 | Логотип в шапке лендинга (`img/lop.png`, 300×48 wordmark) вписывался в квадрат 54×54 → надпись сжималась, а рядом дублировалась текстом «League of Pokemons» | `index.php` (шапка) | ✅ исправлено (DSH): логотип 200×32 (своя пропорция), дублирующий HTML-заголовок убран, остался текст «PokemonChic»; полю логина/пароля лендинга добавлены `aria-label` |
| F-17 | **JS-кэш не сбрасывался после деплоя**: версии у `<script src>` были вписаны руками (`?v=20260603-quest-guide`) и не менялись при правке кода — игрок оставался на старом JS | `views/game-start.php` (12 тегов) | ✅ исправлено (DSH): хелпер `asset_url()` в `views/components/head-styles.php` подставляет `filemtime` |
| F-18 | **Нижняя панель уезжала в горизонтальный скролл**: у админа на «Дороге 1» появлялась QA-кнопка, главное меню из 11 пунктов не влезало (202 px скрытой прокрутки на 1440 px) | `views/game-start.php`, `public/css/game-shell.css` | ✅ исправлено (DSH): QA-кнопка переехала в системную группу и стала компактной, плотность панели поджата — осталось 5 px. У игрока переполнения нет вообще |
| F-19 | Тосты уведомлений закрывали треть экрана: 6 накопленных уведомлений выводились разом | `public/js/game-start-runtime.js:523` | ✅ исправлено (DSH): дедупликация + максимум 3 тоста + сводка «И ещё N» |
| F-20 | **Экран инвентаря жил в другой палитре и типографике**: серо-градиентный «windows-98» стиль, `--accent:#2a6fbd`, Tahoma, заголовок «Инвентарь - League of Pokemons» | `views/game-items.php` | ✅ исправлено (DSH): локальные переменные переведены на токены, шрифт `var(--font-sans)`, заголовок выровнен, подключён партиал и `ui-preferences.js` |
| F-21 | **`ui-preferences.js` не подключался на страницах модулей** — настройка «Анимации» работала только на `/game` | `views/game-{quests,commission,messages,items}.php` | ✅ исправлено (DSH) |
| F-22 | ~~`/game/training-shop` → 404~~ — **ложное срабатывание**: `views/game-training-shop.php` рендерится по слагу `diamond-shop` (`src/Controller/GameModuleController.php:129`), страница «Алмазный магазин» работает. В `page_health_check.php` URL исправлен | `views/game-training-shop.php` | снято |
| F-23 | **`/game/items` переполнялся по горизонтали на телефоне** (390 px): тулбар и поиск 280 px не переносились, сетка слотов 12 колонок | `views/game-items.php` | ✅ исправлено (DSH): медиазапрос ≤640 px — перенос тулбара, поиск на всю строку, 6 колонок слотов |
| F-24 | **Шесть экранов оставались на Tahoma** (`eggs`, `events`, `tournaments`, `transport`, `diamond-shop`, `profile`): свои inline-стили с чужим шрифтовым стеком | `views/game-{eggs,events,tournaments,transport,training-shop,profile}.php` | ✅ исправлено (DSH): подключён базовый партиал, шрифт → `var(--font-sans)`, добавлен `ui-preferences.js`. Теперь Inter на всех 13 страницах |
| F-25 | **Игроку показывали внутреннюю документацию**: панель «QA-проверки» на `/game/tournaments` (4 карточки с описанием backend-поведения) и «QA-чеклист» на `/game/events` — видны обычному игроку, не только админу | `views/game-tournaments.php:109`, `views/game-events.php:194` | ✅ исправлено (DSH): блоки убраны с игровых страниц, данные остаются в GM Center. Проверено под обычным аккаунтом: `qaRows=0` |
| F-26 | Подзаголовок турниров объяснял игроку «работают через новый backend/API без legacy iframe»; строка состояния была тяжёлым тёмным блоком `#10213d` | `views/game-tournaments.php:62,89` | ✅ исправлено (DSH): текст для игрока, `.log` переведён на токены (спокойный по умолчанию, цвет только при событии) |
| F-27 | **Современный лендинг недостижим**: `views/home.php` + `HomeController` привязаны к маршруту `/` фронт-контроллера, но на корень Apache отдаёт legacy `index.php` напрямую — реально показывается он (20 КБ, «League of Pokemons»), а `views/home.php` («Pokemon 8.0 - Главная») не отдаётся никогда | `index.php` (корень) vs `public/index.php:194`, `views/home.php` | решение владельца: перевести `/` на фронт-контроллер или оставить legacy-лендинг. Визуальный слой legacy-лендинга уже обновлён (токены + Inter) |
| F-28 | Логотип в шапке лендинга (`img/lop.png`) отрисовывается крошечным, и текст «League of Pokemons PokemonChic» накладывается на него | `index.php` (шапка лендинга) | косметика: увеличить логотип или убрать его, оставить текстовый бренд |
| F-29 | Бренд расходится: лендинг «League of Pokemons», игровые экраны «Pokemon 8.0», проект — PokemonChic / Покелегенда | `index.php:104`, `views/*.php` | ✅ **закрыто 2026-10-09.** Решение владельца — `League of Pokemons`. Применено: **18 живых вью** переведены с `Pokemon 8.0` на `League of Pokemons`, порядок заголовков унифицирован в `<Страница> - League of Pokemons` (правило: на главной бренд первым, на внутренних — последним); в шапке лендинга убран подзаголовок `PokemonChic`, дублировавший логотип-вордмарк, и осиротевшее правило `.brand-subtitle` удалено из `landing.css`. Мёртвые `home.php`, `game-market-items.php`, `game-pokemon-market.php` не трогались. Проверено: `php -l` по всем 22 вью и корню, `/` → `League of Pokemons - Главная`, `/register` → `Регистрация - League of Pokemons`, `PokemonChic` в отдаваемом HTML нет |

Закрыто сканом как «претензий нет»: XSS в чате (`textContent`), мобильный overflow (единственный спорный случай — `quest-journal.css:879-884` `100vw/100vh`, нужна проверка в браузере), все литеральные ассеты на диске, CSRF-токен отправляется во всех POST-хелперах.

**Важно:** сканирование выполнялось до подключения `fonts.css`/`tokens.css` — пункты отчёта «CSS не подключён» уже неактуальны (исправлено в Ф1).

---

## 5. Дизайн-рефреш: целевая система

Принцип: не «перекрасить», а сначала ввести систему, потом на неё перевести экраны. Стек — vanilla CSS (Tailwind/React-правила из `baseline-ui` применяем как принципы, не как требования к стеку).

### 5.1. Токены (один файл `public/css/tokens.css`)

```css
:root {
  /* Цвет: поверхности и текст */
  --surface-0:#f4f8ff; --surface-1:#ffffff; --surface-2:#eaf2ff; --surface-3:#dce6f6;
  --text-1:#23324d; --text-2:#65748f; --text-3:#8b98ad; --text-inverse:#ffffff;
  --border-1:#dce6f6; --border-2:#cfe0ff;

  /* Бренд и семантика — по одному значению на смысл */
  --accent:#3f7fff; --accent-hover:#2f6ff0; --accent-soft:#eaf2ff;
  --success:#2fc76f; --danger:#ff6b6b; --warning:#f5a524; --info:#3f7fff;

  /* Типографика */
  --font-sans:'Inter', system-ui, 'Segoe UI', Tahoma, sans-serif;
  --fs-100:12px; --fs-200:13px; --fs-300:14px; --fs-400:16px; --fs-500:18px; --fs-600:22px; --fs-700:28px;
  --lh-tight:1.2; --lh-normal:1.45;

  /* Спейсинг — кратность 4 */
  --space-1:4px; --space-2:8px; --space-3:12px; --space-4:16px; --space-5:20px; --space-6:24px; --space-7:32px; --space-8:40px;

  /* Радиусы и тени */
  --radius-sm:6px; --radius-md:8px; --radius-lg:12px; --radius-xl:16px; --radius-pill:999px;
  --shadow-1:0 2px 8px rgba(74,112,180,.08);
  --shadow-2:0 8px 24px rgba(74,112,180,.10);
  --shadow-3:0 16px 40px rgba(74,112,180,.16);

  /* Слои */
  --z-base:0; --z-sticky:10; --z-panel:20; --z-overlay:30; --z-modal:40; --z-toast:50; --z-debug:60;

  /* Моушен */
  --dur-fast:120ms; --dur-base:180ms; --dur-slow:240ms; --ease-out:cubic-bezier(.16,1,.3,1);
}

@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after { animation-duration:.01ms !important; transition-duration:.01ms !important; }
}
```

Брейкпоинты (6): `480 / 640 / 840 / 1024 / 1280 / 1600`. Всё, что вне них, — удаляется или округляется.

Миграция `--ui-*` и `--bd-*` — алиасы на новые токены на один релиз, затем удаление.

#### Состояние палитры (2026-10-07, после 7 раунда)

Уникальных цветов в `public/css` (без `tokens.css`): **hex 695** (было 812 после выноса `game-pokemon.css`), **rgba 265**.

Сделано: `--ui-*` и локальные переменные `game-start.css` (`--accent`, `--text`, `--bg`…) сведены к токенам; 34 точных палитровых литерала и **174 близких к токенам** (Δ ≤ 8 по каналу, визуально неотличимы) заменены на `var()`; старый синий `#1d66c2` (15 мест в 4 файлах) убран — экран и шелл теперь в одном акценте.

Концентрация оставшихся цветов (файл — уникальных цветов):

| Файл | Уникальных | Размер |
|---|---|---|
| `game-pokemon.css` | 229 | 25 КБ |
| `game-battle-dock.css` | 142 | 22.7 КБ |
| `quest-journal.css` | 118 | 17.4 КБ |
| `trainer-profile-window.css` | 112 | 16.9 КБ |
| `game-start.css` | 104 | 22.8 КБ |

Это карта для следующих проходов: сокращение делаем по одному файлу с визуальной проверкой экрана, а не сплошняком. Автоматический коллапс уже один раз ошибся (зелёная подложка входа в админку `#f3fff8` уехала в нейтральный `--surface-0`) — поймано ревью diff и исправлено добавлением токена `--success-soft`.

#### Радиусы и типографика (2026-10-07, 8 раунд)

Магические числа заменены на шкалы: **147 `border-radius`** (8px → `--radius-md` 65 раз, 6px → `--radius-sm` 25, 999px → `--radius-pill` 25, плюс ±1px: 7px → `--radius-md`, 5px → `--radius-sm`) и **128 `font-size`** (12/13/14/15/18px → `--fs-100…--fs-500`). Итог: `var(--radius-*)` — 151 использование, `var(--fs-*)` — 132.

Что осталось осознанно: 5 многосоставных `border-radius: 8px 8px 0 0` (шорткаты, не трогаем), радиусы 3/4/10px и размеры 10/11/16/17/20/22px — их нормализация меняет вид, делаем в пофайловом проходе с проверкой экрана.

**Ловушка, которую поймали:** после замены `admin-panel.css` и `admin-content-wizard.css` начали использовать токены, а `views/game-admin.php` подключал CSS напрямую, без `tokens.css` — переменные не были бы определены, и радиусы/размеры шрифта на админке отвалились бы. Вью переведён на партиал; на `/game/admin` теперь есть `tokens.css` и `base.css`.

### 5.2. Консолидация CSS

Порядок подключения (жёсткий, в одном месте — [views/components/head-styles.php](views/components/head-styles.php), версия считается по `filemtime`):

1. `tokens.css` — переменные.
2. `base.css` — reset, типографика, фокус-стили, скроллбары, `.visually-hidden`, `[hidden]`.
3. `fonts.css` — self-hosted Inter.
4. Компонентные слои (по одному на систему: `chat`, `battle-dock`, `commission`, `quest-journal`, `trainer-card`, `mail`, `admin`, `game-pokemon`).
5. `utilities.css` — когда появится (`.truncate`, `.stack`, `.row`).

Правила: один компонент — один файл; никаких `!important` вне утилит; никаких инлайновых `style="` (14 сейчас); `?v=` руками больше не пишется.

Замечание по `base.css`: глобальное `[hidden] { display: none !important }` покрывает 20 существующих точечных правил вида `.chat-user-menu[hidden] { display: none }` — все они делают то же самое, так что конфликтов нет, а сами правила можно удалить при следующей уборке.

### 5.3. Компоненты и состояния

Минимальный набор, который обязан быть единым:

- Кнопки: primary / secondary / ghost / danger × default / hover / active / focus-visible / disabled / loading. Регистр — только предложениями (сейчас «ВЕРНУТЬСЯ» и «Сброс» в одном ряду).
- Поля: input / select / textarea × default / focus / error / disabled, подпись и текст ошибки рядом с полем.
- Карточки: предмет, покемон, лот, квест, игрок.
- Оверлеи: единый каркас (заголовок, тело со скроллом, футер с действиями), один скролл-контейнер вместо вложенных, закрытие по `Esc` и клику вне, фокус-трап.
- Пустые состояния: одна понятная кнопка действия.
- Загрузка: скелетон на структуру (не спиннер на весь экран).
- Уведомления: тост (успех/ошибка/инфо) с автоскрытием ≥ 4 с.

### 5.4. Доступность

- Контраст текста и иконок ≥ 4.5:1 (крупный текст ≥ 3:1).
- Видимый `:focus-visible` на всех интерактивных элементах.
- `aria-label` для иконочных кнопок; `aria-modal`/`role="dialog"` для оверлеев.
- `alt` для всех картинок (сейчас 10 из 49 без него).
- `type="button"` для кнопок внутри форм (4 без него).
- Полное управление с клавиатуры в бою, лавке, квестах, инвентаре.

### 5.5. Мобильное

- 360 px — минимальная ширина без horizontal overflow.
- `env(safe-area-inset-*)` для док-панелей и нижнего меню.
- Тач-цели ≥ 44×44 px.
- Нет вложенных скроллов (сейчас есть в лавке).

### 5.6. Ассеты

34 отсутствующих иконки из `VISUAL_ASSETS_TODO.md` (чат, локации, игроки, бой, нижняя панель). Решение по источнику — за владельцем (§9, вопрос 3). Технически: строгий `public/img/ui/` + `index.json`, проверка наличия в CI (asset registry checker из `REWRITE_STATUS.md:237`).

---

## 6. Фазы работ

Оценки — в часах чистой работы, без учёта ревью владельцем.

| Фаза | Что делаем | Выход | Оценка | Статус на 2026-10-07 |
|---|---|---|---|---|
| **Ф0. Инвентаризация и баг-лист** | Свести найденные баги (фронт, бэк, дизайн) в один трекаемый список с приоритетами; зафиксировать противоречия документов | Этот файл + баг-лист P0/P1/P2 | 2–3 ч | ✅ выполнено: две инвентаризации, баг-листы B-01…B-14 (бэк) и F-01…F-29 (фронт/дизайн) |
| **Ф1. Токены и типографика** | `tokens.css`, самохостинг Inter, слияние `--ui-*`/`--bd-*`, шкалы spacing/radius/shadow/z-index, сокращение палитры | Одинаковый вид в 4 размерах, шрифт загружается | 6–10 ч | ✅ выполнено: Inter на 13 страницах и лендинге, палитра 812 → 695 hex, 208 + 275 замен на токены |
| **Ф2. Консолидация CSS** | Единый head-партиал, базовый слой, вынос инлайновых стилей, авто-версии | Меньше дублей, предсказуемое кэширование | 10–16 ч | ⏳ почти: `head-styles.php` + `base.css` + авто-`?v` для CSS и JS, вынесены 25 КБ из `/game/pokemon`. **2026-10-09: 11 из 14 вью закрыты, 1 исключено как мёртвый код.** auth → `auth-panel.css`, «Алмазный магазин» + Транспорт → `game-shop.css`, `game-module` → `game-module.css`, `game-eggs` → `game-eggs.css`, `game-events` → `game-events.css`, `game-tournaments` → `game-tournaments.css`, `game-profile` → `game-profile.css`; добавлены контрастные текстовые токены (`--accent-text`, `--success-text`, `--danger-text`, `--danger-soft`, `--warning-soft`, `--warning-text`); по ходу закрыт предсуществующий обрез контента в профиле на узких экранах. `views/game-pokemon-market.php` — мёртвый код (TASK-021), не трогаем. **Ф2 ЗАКРЫТ:** добито `game-items` → `game-items.css` и **лендинг** — 334 строки инлайнового CSS из корневого `index.php` (он и обслуживает `/`) → `public/css/landing.css`. Итого 12 вью + лендинг; `<style>` не осталось ни в одном живом шаблоне. `views/home.php` исключён как **недостижимый (F-27 подтверждён)**: голый `/` отдаёт DirectoryIndex, то есть legacy корневой `index.php`. Вне зоны — legacy `game.php` и `attak_pokes.php` |
| **Ф3. Компоненты и состояния** | Кнопки, поля, карточки, оверлеи, пустые состояния, скелетоны, тосты | Новые экраны из готовых блоков | 14–20 ч | ⏳ идёт: **заложен единый слой `public/css/components.css`** (подключается `head-styles.php` после `base.css`): `.ui-btn` (primary/secondary/ghost/danger × hover/active/focus-visible/disabled/loading), `.ui-field` (label/control/error/hint), `.ui-empty`, `.ui-toast` (success/error/info), `.ui-skeleton`, тач-цель 44 px на ≤640. **Решён системный контраст кнопок:** добавлены `--accent-strong` (#2563eb, с белым ≈5.1:1) и `--accent-strong-hover`; раньше заливка была `--accent` (#3f7fff, 3.65:1). Перенесены auth-экраны. Осталось: перенести остальные экраны, карточки и оверлеи  **Дополнение 2026-10-09:** системный контраст закрыт по всем экранам, а не только в новом слое — заливок на `--accent` было **9** (стало 5.17:1 вместо 3.69:1), акцентного текста **11** в 5 файлах (6.35:1 вместо 3.69:1), посчитано из токенов. Тост сведён в один набор: правила переехали из `player-menu.css` (там были литералы `#bdd1e8`/`#193150`/`#fecaca`) в `components.css`, добавлен вариант `info`, кнопка действия переведена на `--accent-strong`, дублирующий `.ui-toast` удалён. Осталось: перенести на слой сами экраны, карточки и скелетоны ||
| **Ф4. Экраны (рефреш)** | `/game` → модули → админка → лендинг | Скриншот-QA на 4 размерах | 24–40 ч | ✅ выполнено: все 13 страниц + лендинг на токенах и Inter, мобильный фикс инвентаря, аудит 13/13 чисто |
| **Ф5. Ассеты** | 34 отсутствующие иконки, asset registry | Нет битых ссылок | 4–8 ч | ⏳ **сделаны заглушки:** `public/img/ui/icons.svg` — спрайт с 34 `<symbol id="i-NAME">` и 34 `<mask id="mask-NAME">`, все на `viewBox 0 0 24 24`, только `currentColor`, обводка 1.75, без текста и растров; `public/css/icons.css` — базовый `.icon` + 34 класса `.icon--NAME` через SVG-маску (есть и `mask-*`, и `-webkit-mask-*`). Пары состояний (`ui-pve-on/off`, `chat-autoscroll-on/off`, `player-online/offline`) различаются геометрией, а не цветом. **Проверено в Chrome:** все 34 рендерятся, масштабируются 16/24/32 и красятся через `currentColor`. Осталось: проверить Firefox/Safari, подключить к экранам и завести `public/img/ui/index.json` (asset registry — `TASK-001`) |
| **Ф6. Багфикс P0/P1** | Правки из баг-листа, бой — последним | P0=0, P1=0 | 8–24 ч | ⏳ фронт закрыт (F-07…F-29), бэкенд-P0 (B-01…B-04) в очереди Codex |
| **Ф7. Документация и QA** | Актуализировать `PROJECT_CONTEXT.md`, карты, пометить исторические отчёты; полный `full`-прогон; long-session и мобильный прогон | Документы не противоречат друг другу | 4–6 ч | ⏳ частично: в `PROJECT_CONTEXT.md` добавлен раздел о дизайн-системе и фронт-инструментах; полная регрессия 28/28; сверка README оставлена Codex (TASK-003) |

Правило безопасности между фазами: **после каждой фазы** — `php -l` изменённых файлов + `tools/open_test_regression.php --profile=quick` (24/24) + скриншот-проверка затронутых экранов. Мутирующие smoke запускать только последовательно.

---

## 7. Приоритеты багфикса

- **P0 (ломает игрока или данные):** потеря/дюп предметов, валюты, покемонов; повторная выдача награды; зависший бой; ошибка, после которой экран не восстанавливается.
- **P1 (мешает играть):** неработающая кнопка/ссылка, пустой экран, отсутствующий ассет в критичном месте, вложенный скролл в лавке, неверные значения в UI (`unknown`, «Не указана»), несоответствие JS↔API.
- **P2 (полировка):** регистр кнопок, отступы, тени, анимации, консистентность иконок, доступность.

Бой (`BattleEngineService`, `BattleRepository`) и экономика (`CommissionMarketRepository`, `RewardRepository`) — трогаем **последними**, только с прогоном полного профиля регрессии.

---

## 8. Риски

| Риск | Смягчение |
|---|---|
| Рефреш CSS ломает вёрстку боя/дока | Батл-слой переводится на токены отдельным шагом, с визуальной проверкой каждого состояния (PvE, PvP, Mega/Primal, погода) |
| Правка `game-start-runtime.js` (2 465 строк) ломает игровой цикл | Никаких «попутных» рефакторингов в этом файле: только точечные правки с прогоном `pve_action_lock_smoke` и `http_smoke` |
| Грязные legacy-данные дают ложные баги | Перед фазой Ф6 — свежий `beta_backup.php` + `db_integrity_smoke.php` |
| Ассеты не найдутся | Если исходников нет — сначала иконки-заглушки в едином стиле, затем замена |
| Документы противоречат друг другу | Ф7 с явным правилом: `PROJECT_CONTEXT.md` — источник правды |

---

## 9. Решения владельца (получены 2026-10-07)

1. **Направление дизайна:** светлый «Pokedex-clean» — сохраняем светлую основу, сводим к одной палитре, сетке и типографике.
2. **Шрифт:** самохостинг Inter (OFL), без внешних CDN.
3. **Ассеты:** 34 отсутствующие иконки — рисуем заглушки в едином стиле, подключаем позже финальные.
4. **Глубина захода:** **полный рефреш всех экранов** (Ф1–Ф5), включая лендинг.
5. **Кланы:** остаются после beta (по `BETA_RELEASE_TASKS.md:289`), в текущий объём не входят.

### Открытые вопросы (нужны по ходу работ)

1. Финальный набор иконок: когда появятся исходники — заменить заглушки (нужен контакт/набор).
2. ~~Бренд: `League of Pokemons` (лендинг) vs PokemonChic / Покелегенда — какое имя оставляем в title/description/og~~ — **РЕШЕНО 2026-10-09 владельцем: `League of Pokemons`.** Этим закрывается **F-29**; в title/description/og ставим это имя, `Pokemon 8.0` в `<title>` auth-экранов и в шапке движка подлежит замене в рамках Ф4.

---

## 10. Приложение: команды проверки

### ⚠ Состояние QA-канала на 2026-10-09 — читать до запуска проверок

Штатный путь (`visual-shot.js`, `audit-pages.js` через CDP на `127.0.0.1:9222`)
**сейчас не работает**, и две ловушки легко принимаются за баги вёрстки:

1. **Зависший браузер.** Экземпляр от 07.10 отвечает на HTTP `/json/version`, но
   `Page.enable` не возвращается никогда — ни через `Target.attachToTarget`, ни через
   page-level WebSocket. В нём накопилось ~12 брошенных `about:blank`-таргетов.
   Чистый перезапуск не помогает: у нового headless DevTools HTTP не отвечает вообще.
2. **`--window-size` клампится до 500 px.** Снимок «390 px» — это **обрезка
   500-пиксельного лейаута**, а не лейаут на 390. На таком снимке карточка выглядит
   вылезшей за вьюпорт, хотя переполнения нет. Проверять реальную узкую ширину
   обязательно через `Emulation.setDeviceMetricsOverride` или через iframe нужной
   ширины — не через `--window-size`.

Рабочий обход без CDP (использован для auth-экранов):

```powershell
# скриншот без CDP вообще
& 'C:\Program Files\Google\Chrome\Application\chrome.exe' --headless=new --disable-gpu `
  --user-data-dir="$env:TEMP\chrome-dsh-qa" --window-size=900,1050 `
  --screenshot=out.png --virtual-time-budget=8000 `
  'http://pokemonchic.com/_qa-viewport.php?url=%2Fregister&w=390&h=900'
```

Временный харнесс `_qa-viewport.php` рендерит страницу в iframe точной ширины и
печатает в углу `vw / docW / scrollW / OVERFLOW / font / styleTags / css` плюс список
элементов, выходящих за вьюпорт. `_qa-view.php` рендерит один шаблон с заданными
переменными, без боевого маршрута и без БД — нужен для состояний вроде `auth-reset`
(требует действующий токен). **Оба файла — временные, лежат в корне сайта, удалить до
деплоя.**

### Визуальная проверка в браузере (появилась в 9 раунде)

Проект годами жил без browser-pass: `playwright-core` локально отсутствует, и все отчёты помечали живой UI как непроверенный. Теперь проверка есть и не требует зависимостей: headless Chrome + CDP.

```powershell
# 1. поднять headless Chrome с отладочным портом (один раз за сессию)
Start-Process 'C:\Program Files\Google\Chrome\Application\chrome.exe' -ArgumentList `
  '--headless=new','--disable-gpu','--no-first-run','--remote-debugging-port=9222',`
  "--user-data-dir=$env:TEMP\chrome-dsh",'about:blank'

# 2. получить cookie сессии игрока (PHP-скрипт логина выводит PHPSESSID)
# 3. снять скриншот и заодно получить метрики страницы
node docs/agent-tasks/visual-shot.js 'http://pokemonchic.com/game' "$env:TEMP\game.png" 1440 900 $sid
```

`visual-shot.js` печатает JSON с `title`, `scrollW/clientW` (переполнение по горизонтали), `bodyFont`, `bodySize`, `motion` и `interLoaded` — то есть проверяет не только «как выглядит», но и «применились ли токены, шрифт и настройки игрока». `visual-probe.js` позволяет выполнить произвольное выражение на странице (замеры элементов, поиск горизонтальных скроллеров). `audit-pages.js` обходит 13 страниц игры и собирает **консольные ошибки, JS-исключения, битые картинки, переполнение и шрифт** — это тот browser-regression, которого проекту не хватало.

Результат прогона 2026-10-07 (13 страниц): **0 консольных ошибок, 0 исключений, 0 битых картинок, 0 переполнений**; Inter загружен на всех страницах (админка была последней, кто жил на Arial — `admin-panel.css`, исправлено).

Аудит поддерживает произвольный вьюпорт: `node docs/agent-tasks/audit-pages.js <sid> http://pokemonchic.com 390x844`.

| Вьюпорт | Результат |
|---|---|
| 1440×900 | 13/13 чисто, включая ноль внутренних горизонтальных скроллов |
| 390×844 | страничного переполнения нет ни на одной странице; внутренние горизонтальные прокрутки остались у лент, которым это свойственно: нижняя панель, вкладки чата, таблица админки, панель комиссионки (`/game/items` был единственным исключением — исправлено медиазапросом) |

Инструмент различает три разных случая и не путает их: **страничное переполнение** (`documentElement.scrollWidth`), **видимые внутренние скроллбары** (`overflow-x: auto|scroll`), и **обрезанный текст** (`overflow: hidden` без многоточия). Осознанное усечение с `text-overflow: ellipsis` (например, имена покемонов в тренер-карте) багом не считается.

Оба скрипта **отключают кэш браузера** (`Network.setCacheDisabled`) — иначе проверка идёт по устаревшим файлам. Именно на этом я сначала попался: правка JS не появлялась в браузере, потому что версии скриптов были вписаны руками (F-17).

Первые замеры (2026-10-07):

| Страница | Размер | Горизонтальное переполнение | Шрифт | `motion` | Inter загружен |
|---|---|---|---|---|---|
| `/game` | 1440×900 | нет | Inter | `on` | да |
| `/game` | 390×844 | нет | Inter | `on` | да |
| `/game/pokemon` | 1440×900 | нет | Inter | `on` | да |

До правок 9 раунда `/game/pokemon` показывал `Trebuchet MS`, `interLoaded: false`, `motion: null` — то есть на этом экране ни токены типографики, ни настройка анимаций не работали. Исправлено: подключён `ui-preferences.js`, шрифт переведён на `var(--font-sans)`.

### Прочие проверки

```powershell
# Синтаксис
E:\OSPanel\modules\php\PHP_8.1\php.exe -l src\Game\BattleEngineService.php

# Регрессия (последовательно, не параллельно)
E:\OSPanel\modules\php\PHP_8.1\php.exe tools\open_test_regression.php --profile=quick --login=Tacos --password=...
E:\OSPanel\modules\php\PHP_8.1\php.exe tools\open_test_regression.php --profile=full  --login=Tacos --password=...

# Целостность данных
E:\OSPanel\modules\php\PHP_8.1\php.exe tools\db_integrity_smoke.php --json
E:\OSPanel\modules\php\PHP_8.1\php.exe tools\migration_status.php --record-status

# Бэкап перед мутирующими правками
E:\OSPanel\modules\php\PHP_8.1\php.exe tools\beta_backup.php
```

Скриншот-QA: `/game`, `/game/admin`, `/game/pokemon`, `/game/items`, `/game/quests`, `/game/commission`, `/game/tournaments` на 390 / 768 / 1280 / 1920 px; проверять отсутствие horizontal overflow и вложенных скроллов.
