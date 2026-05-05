# Pokemonchic Rewrite Notes

Обновлено: 2026-05-05.

## Текущее ядро

- Front controller: `public/index.php`.
- Основной экран: `GET /game`.
- Игровые API: `/api/game/*`, `/api/map/*`, `/api/battle/pve/*`, `/api/inventory/*`, `/api/pokemon/*`, `/api/dex/*`, `/api/location/npc*`, `/api/chat/messages`, `/api/friends/*`.
- БД: MySQL/MariaDB `pokelege_fh7904el_base`, локально проверено на `127.0.0.1:3308`, пользователь `root` без пароля.

## Блок 2026-05-05: друзья и меню игроков

Что было сломано:

- В меню игрока была кнопка дружбы, но не было полноценного API заявок.
- Таблицы `friends` и `friends_zayv` не имели нормальных первичных ключей, уникальности пар и индексов под частые проверки.
- Клиент не понимал состояния отношений: нет заявки, входящая заявка, исходящая заявка, уже друзья.
- Часть сообщений в социалке и чате была битой из-за старой кодировки.

Что сделано:

- Добавлен `FriendRepository`.
- Добавлен `FriendApiController`.
- Добавлены API:
  - `GET /api/friends/status`
  - `GET /api/friends/requests`
  - `POST /api/friends/request`
  - `POST /api/friends/accept`
  - `POST /api/friends/decline`
  - `POST /api/friends/remove`
- Меню игрока теперь меняет кнопку по статусу:
  - `Добавить в друзья`
  - `Принять заявку`
  - `Заявка отправлена`
  - `Удалить из друзей`
- Клиент опрашивает входящие заявки и показывает игровое toast-уведомление, когда приходит новая заявка.
- Кнопки социалки больше не используют `alert`, уведомления выводятся внутри игры.
- Добавлена миграция `database/migrations/2026_05_05_000001_harden_friends_schema.sql`.
- Исправлены видимые UTF-8 строки в `public/js/chat.js`, `public/js/player-menu.js`, `src/Game/ChatService.php`, `src/Controller/ChatApiController.php`, `src/Controller/FriendApiController.php`.

Изменения БД:

- `friends.id_fr` переведён в `AUTO_INCREMENT PRIMARY KEY`.
- Добавлен `UNIQUE KEY uniq_friends_pair (id_user, id_my_friend)`.
- Добавлен `KEY idx_friends_reverse_pair (id_my_friend, id_user)`.
- `friends_zayv.id_fz` переведён в `AUTO_INCREMENT PRIMARY KEY`.
- Добавлен `UNIQUE KEY uniq_friend_request_pair (id_user, id_user_to)`.
- Добавлен `KEY idx_friend_request_to (id_user_to, id_user)`.

Проверки:

- PHP lint: `public/index.php`, `src/Repository/FriendRepository.php`, `src/Controller/FriendApiController.php`, `src/Controller/ChatApiController.php`, `src/Game/ChatService.php`.
- JS syntax: `public/js/chat.js`, `public/js/player-menu.js`.
- Проверена схема индексов в реальной БД.
- Прогнан сценарий на реальной БД с очисткой после себя: создать заявку, проверить `outgoing/incoming`, принять, проверить `friends`, удалить, проверить `none`.

## Ближайшие долги

- Добить входящие заявки отдельным списком/центром уведомлений, а не только toast и пунктом меню.
- Перевести оставшиеся старые строки с mojibake в `views/game-start.php`, `public/index.php`, старых комментариях и legacy-файлах.
- Сделать полноценный smoke-test API с авторизованной сессией и CSRF.
- Довести PvE бой: размер overlay, все 4 атаки, картинки активного покемона, компактные подсказки атак.
- Спроектировать и реализовать PvP бой на базе нового UI боя, с логированием ходов и защитой от повторных кликов.
- Переписать админку с нуля, используя старую только как справочник правил.
- Разобрать оставшиеся большие CSS/JS файлы на модули без возврата к старому CSS.
