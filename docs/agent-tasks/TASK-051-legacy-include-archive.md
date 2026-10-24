# TASK-051 — legacy `include/` в архив вне проекта

**Исполнитель:** DeepSeek (веб-канал) · **Зона (явно разрешена):** `include/**` — только чтение; ссылки в `src/**`, корневых `*.php`, `admin/**`, `script/**`, `views/**`, `config/**`, `.htaccess` — только чтение · **Приоритет:** P2 — расчистка legacy без срочности

**Формат сдачи:** веб-канал в репозиторий не пишет. Верни **unified diff или пофайловую замену целиком**, готовый PowerShell-скрипт переноса и возврата текстом, и отдельно перечисли проверки. Перемещение файлов выполняет владелец или DSH-агент — не ты.
**База:** гейт `quick` на момент выдачи — **25/25** (`Passed: 25, failed: 0, skipped: 0`, `docs/agent-tasks/TASK-quest161-162.report.md`). Не занижай.

---

## Причина

`include/**` — legacy-каталог, который не развивается и запрещён как основа для новой функциональности (`AGENTS.md:84`). При этом он лежит внутри рабочего дерева и в индексе Git целиком, то есть участвует в диффах, поиске и ревью, хотя в runtime нужен ровно один файл. Держать 240 файлов в проекте ради одного — цена без выгоды.

## Что замерено сейчас

* `include/**` — **240** файлов, из них **238** PHP, суммарно **27 560** строк.
* Реально читается в runtime **один** файл — `include/data.world.php`. Единственные ссылки в `src/**`:
  * `src/Repository/AdminRepository.php:118` — строка описания в UI (не чтение файла);
  * `src/Repository/QuestRepository.php:781` — реальное чтение файла.
* `include/rooms/**` упоминается в `src/**` **один раз** и **только в комментарии**.
* Весь каталог `include/**` отслеживается Git: `git ls-files include` → **240** путей. Поэтому перенос за пределы репозитория появится в `git status --short` как 240 удалений — это ожидаемо и требует решения владельца (см. «Границы»).
* В рабочем дереве уже есть незакоммиченные правки внутри `include/**` (среди них `include/data.world.php`, `include/files/chat.world.php`, `include/rooms/101.php`, `include/rooms/18.php`, `include/rooms/44.php`, `include/rooms/80.php`, `include/rooms/104.php`). По `AGENTS.md:37` такие файлы трогать нельзя.

## Что сделать

1. **Полный список ссылок на `include/**` по всему репозиторию** — сгенерировать скриптом, а не глазами. Область: корневые `*.php`, `admin/**`, `script/**`, `views/**`, `config/**`, `.htaccess`. Ищутся `include`, `include_once`, `require`, `require_once` и прямые URL. Скрипт:
   ```powershell
   Set-Location 'E:\OSPanel\domains\pokemonchic.com'
   $files = @(Get-ChildItem -Path . -File -Filter *.php) +
            @(Get-ChildItem -Path admin,script,views,config -Recurse -File -ErrorAction SilentlyContinue) +
            @(Get-Item .htaccess -ErrorAction SilentlyContinue)
   $files | Select-String -Pattern '(include|require)(_once)?\s*[\( ]' |
     ForEach-Object { '{0}:{1}: {2}' -f $_.Path.Replace((Get-Location).Path + '\', ''), $_.LineNumber, $_.Line.Trim() }
   ```
   Отдельно — прямые URL в ассетах и вью:
   ```powershell
   Set-Location 'E:\OSPanel\domains\pokemonchic.com'
   Get-ChildItem public,views -Recurse -File -Include *.js,*.css,*.php | Select-String -Pattern '/include/'
   ```
   Скрипт — отправная точка: если он пропускает форму записи (например `include $dir . '/file.php'`), дополни его и в отчёте приведи финальную версию и её вывод. Результат — таблица «файл:строка — что подключается — живое или мёртвое». Число найденных ссылок приведи итогом.
2. **Отделить рабочий `include/data.world.php`.** Он остаётся на месте, не перемещается и не правится. Все ссылки на него — только из двух мест выше; подтверди это своим прогоном.
3. **Подготовить перенос остального** (239 файлов) в каталог-архив **вне проекта**: `E:\OSPanel\_archive\pokemonchic-include-<YYYY-MM-DD>` (дата подставляется в момент выполнения, не в момент написания спеки). Требования к переносу:
   * предварительный бэкап `php tools/beta_backup.php` (БД), путь к дампу — в отчёт;
   * сначала **dry-run**, затем перенос: `robocopy <include> <archive> /E /XF data.world.php` — сначала с `/L` (только список), потом без `/L`;
   * имя файла `data.world.php` исключается (`/XF data.world.php`), структура подкаталогов сохраняется;
   * готовый скрипт возврата **одной командой**: `robocopy <archive> <include> /E /MOVE` — тоже с dry-run до применения;
   * никакой пофайловой выборки и никакого удаления: только перемещение, полностью обратимое.
4. **Перемещение выполнять только после явного «да» владельца.** Молчание, отсутствие ответа и «вроде всё проверили» согласием не считаются. В отчёте — дословная цитата решения и дата. До этого момента задача остаётся в статусе `todo`/`blocked`.
5. Перед перемещением — **предварительное условие по незакоммиченным правкам**:
   ```powershell
   Set-Location 'E:\OSPanel\domains\pokemonchic.com'
   git status --short -- include
   ```
   Если вывод непустой — перенос **не выполнять**: зафиксировать список файлов и эскалировать владельцу. Чужие незакоммиченные изменения не перемещать и не терять.

## Границы и запреты

* Не удалять файлы. Перемещение — только перемещение, и только с возможностью вернуть всё назад одной командой.
* Не трогать `include/data.world.php` — ни правкой, ни переносом.
* Не менять legacy-код: перемещаемые файлы переносятся байт-в-байт, содержимое не редактируется.
* Не менять `src/**`, `public/**`, `views/**`, `tools/**`, `config/**`, `database/**`, `game.php`, корневые `*.php`, `admin/**`.
* Не коммитить, не пушить, не выполнять `git checkout -- .`, `git reset --hard`, `git clean -fd`.
* Перенос за пределы репозитория даёт 240 удалений в `git status --short` — как этот дифф закрывается (коммит, `.gitignore`, возврат в архив) решает владелец. Это в отчёт отдельным вопросом.
* Не запускать мутирующие смоуки параллельно с другими агентами (`AGENTS.md:81`).

## Приёмка

1. `php -l` по каждому изменённому PHP-файлу. Если правок в PHP нет — так и напиши; для переносимого дерева вместо этого приведи сверку хешей до и после (`Get-FileHash -Algorithm SHA256`) по всем перемещаемым файлам.
2. **Полный гейт, вывод дословно** (нужно **25/25**, не ниже):
   ```powershell
   Set-Location 'E:\OSPanel\domains\pokemonchic.com'
   & 'E:\OSPanel\modules\php\PHP_8.1\php.exe' tools\open_test_regression.php --profile=quick --login=Tacos --password=REDACTED_USE_SMOKE_PASSWORD
   ```
   Если время позволяет — `--profile=full` (историческая норма **28/28**) и приведи код возврата, а не только текст.
3. Релевантные смоуки (до и после переноса, оба вывода):
   ```powershell
   Set-Location 'E:\OSPanel\domains\pokemonchic.com'
   & 'E:\OSPanel\modules\php\PHP_8.1\php.exe' tools\http_smoke.php --login=Tacos --password=REDACTED_USE_SMOKE_PASSWORD
   & 'E:\OSPanel\modules\php\PHP_8.1\php.exe' tools\legacy_core_qa_smoke.php
   & 'E:\OSPanel\modules\php\PHP_8.1\php.exe' tools\page_health_check.php
   ```
   Норма: `http_smoke` — 52 проверки, 0 падений; `page_health_check` — 15/15.
4. Legacy-маршруты не изменились: `game.php?go=fight_pve` и `?go=fight_pvp` отдают **410** до и после.
   ```powershell
   Set-Location 'E:\OSPanel\domains\pokemonchic.com'
   curl.exe -s -o NUL -w "%{http_code}`n" 'http://pokemonchic.com/game.php?go=fight_pve'
   ```
5. Итоговый список ссылок: живых ссылок на перемещённое — **ноль**; оставшиеся — только на `include/data.world.php` из двух известных мест. Список приложить.
6. `git status --short -- include` до и после; `git diff --stat` по проекту.
7. Возврат проверен: dry-run `robocopy <archive> <include> /E /MOVE /L` показывает полное восстановление 239 файлов.

## Отчёт

`docs/agent-tasks/TASK-051-legacy-include-archive.report.md`: таблица ссылок, финальный скрипт поиска, скрипты переноса и возврата, путь бэкапа, дословные выводы всех проверок, вывод `git status --short -- include`, `git diff --stat`, цитата решения владельца, риски и то, что осталось.

Требование к каналу: **верни unified diff или пофайловую замену целиком, отдельно перечисли проверки.** Плюс обязательное условие: **решение владельца требуется до любого перемещения файлов** — без него задача не выполняется. Не коммить, не пушить.
