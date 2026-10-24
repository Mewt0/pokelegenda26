Готово.

Добавил [tools/asset_registry_check.php](E:/OSPanel/domains/pokemonchic.com/tools/asset_registry_check.php) и обязательный отчёт [TASK-001-asset-registry-check.report.md](E:/OSPanel/domains/pokemonchic.com/docs/agent-tasks/TASK-001-asset-registry-check.report.md).

Проверки:
- `php -l tools\asset_registry_check.php` → OK
- `--json` валидно парсится через `ConvertFrom-Json`
- запуск из `%TEMP%` работает
- мутирующие smoke/regression не запускал

Текущий результат сканера: `sources=85`, `assets=672`, `references=70`, `broken=3`, `orphans=627`. Найденные битые ссылки все в `script/map.js:1`: `img/other/inf.png`, `inf2.png`, `inf3.png`.

В рабочем дереве уже есть много чужих изменений вне зоны задачи; я их не трогал. Мои добавления: только новый инструмент и report-файл.