Что исправлено для 403 Forbidden nginx:

1. В архиве не было index.php в корне. Добавлен index.php, который подключает testind.php.
2. Все текстовые PHP/HTML-файлы перекодированы в UTF-8.
3. Короткие PHP-теги <? заменены на <?php, потому что на PHP 8/nginx+php-fpm short_open_tag часто выключен.
4. session_start() защищён от повторного запуска.
5. sizeof() заменён на count().
6. Добавлен пример nginx-конфига: deploy/pokemonchic.nginx.conf
7. Добавлен скрипт прав: deploy/fix_permissions.sh

После загрузки на сервер:
- проверь root в nginx-конфиге;
- проверь сокет php-fpm: /run/php/php8.0-fpm.sock;
- выполни: nginx -t && systemctl reload nginx;
- права: bash deploy/fix_permissions.sh /var/www/pokemonchic.com www-data
