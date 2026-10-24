# TASK-031 — Живой баг: `/events.php` падает с фатальной ошибкой

**Исполнитель:** Codex-агент · **Зона:** корневой `events.php` (legacy) · **Статус:** todo
**Основание:** найдено при проверке реально доступных legacy-маршрутов (2026-07-10, прогон DSH-агента). К переезду на PHP 8.5 отношения не имеет — файл падает **уже на текущем PHP 8.1**.

## Проблема

```
GET http://pokemonchic.com/events.php   (авторизованный)
HTTP 200, 241 байт
Fatal error: Uncaught Error: Call to undefined function all()
  in E:\OSPanel\domains\pokemonchic.com\events.php:59
```

`all()` не определена **нигде** в проекте (0 определений по всему дереву). Файл запускает long-poll цикл на 60 секунд (`events.php:56-57`) и падает на первой же итерации.

Современный аналог — страница `/game/events`.

## Что сделать

Отдать `410 Gone` со ссылкой на новый маршрут — тем же приёмом, что уже применён в `game.php:45-58`:

```php
if (function_exists('http_response_code')) http_response_code(410);
header('Content-Type: text/html; charset=UTF-8');
echo '<!doctype html><meta charset="utf-8"><title>Legacy route removed</title>'
   . '<h1>Старый маршрут отключён</h1>'
   . '<p><code>events.php</code> больше не используется. События доступны на '
   . '<a href="/game/events">/game/events</a>.</p>';
exit;
```

Перед правкой проверить:
1. Никто не подключает `events.php` как include (поиск по дереву).
2. Никто не ждёт от него long-poll данных (поиск обращений к `/events.php` в JS/PHP).
3. `all()` не должна была существовать «где-то в другой ветке» — подтвердить поиском.

Если обнаружится живой потребитель `/events.php` (например, старый JS с EventSource) — **не удалять молча**: зафиксировать в отчёте и перевести потребителя на `/api/events/active` (этот API уже используется современной страницей).

## Границы

- **Нельзя:** менять схему БД; трогать `views/**`, `public/**`; переносить логику событий из legacy в `src/**` (это отдельная программа работ).
- Правка локальная: файл в корне, не `include/**`.

## Критерии приёмки

1. `php -l events.php` — чисто.
2. `GET /events.php` авторизованным запросом → **HTTP 410**, в теле нет `Fatal error`.
3. Подтверждено, что потребителей не осталось (поиск + результат в отчёте).
4. `tools/page_health_check.php` → **15/15**.
5. `tools/open_test_regression.php --profile=quick` → **24/24**.
6. Строка `/events.php` в таблице §9 плана `PHP85_MIGRATION_PLAN.md` обновлена: было «Fatal error», стало «410».

## Отчёт

`docs/agent-tasks/TASK-031-events-php-410.report.md`: файлы, команды, фактические коды ответов до/после, найденные потребители (или их отсутствие), риски.
