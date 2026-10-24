Готово.

Изменил только разрешённую зону задачи:
- [src/Support/ClientIp.php](E:/OSPanel/domains/pokemonchic.com/src/Support/ClientIp.php): proxy-заголовки учитываются только при `REMOTE_ADDR` из `TRUSTED_PROXIES`.
- [src/Repository/AdminRepository.php](E:/OSPanel/domains/pokemonchic.com/src/Repository/AdminRepository.php): создание `banip` + `audit()` обёрнуто в транзакцию.
- Создал обязательный отчёт: [TASK-011-ban-and-ip-trust.report.md](E:/OSPanel/domains/pokemonchic.com/docs/agent-tasks/TASK-011-ban-and-ip-trust.report.md).

Проверки:
- `php -l src\Support\ClientIp.php` — OK
- `php -l src\Repository\AdminRepository.php` — OK
- read-only DB check: `banip.ip=630215025`, `37.144.81.113` → `BanRepository::isIpBanned(...) = true`
- spoof-check: без trusted proxy `X-Forwarded-For=8.8.8.8` больше не подменяет IP

Мутирующие smoke не запускал, как и было запрещено. Коммитов, пушей и удалений не делал.