Готово.

Изменил только [include/files/chat.world.php](E:/OSPanel/domains/pokemonchic.com/include/files/chat.world.php): убрал `utf8_decode()` из `__u2c()` и заменил fallback на `windows-1251` через `iconv`, затем `mb_convert_encoding` при наличии `mbstring`.

Отчет создан: [TASK-030-legacy-include-compat.report.md](E:/OSPanel/domains/pokemonchic.com/docs/agent-tasks/TASK-030-legacy-include-compat.report.md).

Проверки:
- `php -l include\files\chat.world.php` — OK.
- `game.php?go=fight_pve` — 410.
- `game.php?go=fight_pvp` — 410.
- `Services_JSON.php` не портировал: найден только в недостижимой legacy-цепочке; `php -l` по нему ожидаемо падает на старом синтаксисе, это зафиксировано в отчете.
- Мутирующие smoke не запускал, как было запрещено.

Также заметил опечатку в спеке: указан `include/function/chat.world.php`, но реальный файл с `__u2c()` находится в `include/files/chat.world.php`; это тоже внесено в отчет.