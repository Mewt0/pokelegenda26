# Pokemon 8.0: карта проекта и порядок переписывания

## Решение по архитектуре

Новая версия пишется с нуля. Рабочее новое ядро лежит в:

1. `public/`
   Единственная публичная точка входа.

2. `src/`
   Новый PHP-код с namespace `Pokemon8`.

3. `config/`
   Конфиги приложения и базы.

4. `views/`
   Шаблоны нового интерфейса.

Старые root PHP, `include/` и `admin/` остаются как legacy/reference до переноса бизнес-логики. Их нельзя считать целевой архитектурой и нельзя продолжать расширять костылями.

Подробный стандарт: `STANDARD_2026.md`.

Текущий статус по каждому файлу: `REWRITE_STATUS.md`.

## Что это за папка

`pokemon8.0` - рабочая папка для миграции старого проекта. Внутри сохранена старая структура директорий, но скопированы только критические PHP/CSS/JS-файлы, которые нужны для понимания и переписывания ядра.

Текстовые файлы при переносе приведены к UTF-8. Большие ассеты покемонов, логи, кеши, старые тестовые копии и сторонний мусор сюда не перетаскивались.

Дополнительный источник `D:\ospanel\domains\pokemonchic.com` перенесен в `_incoming/pokemonchic.com`. Файлы с реально совпадающими путями и админка лежат в `_incoming/mapped`, а плоские корневые кандидаты - в `_incoming/mapped/root_flat`. Не подменяй `include/*` файлами из root по похожему имени: сначала смотри `_incoming/README.md`.

## Главные точки входа

1. `index.php`
   Главная страница, форма входа, рейтинги, переходы в регистрацию и игру.

2. `autoriz.php`
   Авторизация пользователя. Критично переписать хранение паролей: сейчас используется старый `md5` + реверс + соль.

3. `game.php`
   Главный игровой роутер. Он решает, какой файл из `include/files` подключать по `?go=...`.

4. `events.php`
   Server-Sent Events / уведомления. Нужен для live-обновлений.

5. `ban.php`
   Проверка банов и технических ограничений.

6. `router.php`
   Новый локальный роутер для запуска через `php -S`.

## Конфиг и база

1. `app/bootstrap.php`
   Новый bootstrap: `.env`, timezone, сессии, общий `$config`.

2. `app/env.php`
   Мини-загрузчик `.env`.

3. `include/function/config.php`
   Старый путь, теперь прокидывает проект в `app/bootstrap.php`.

4. `include/function/db3.php`
   Совместимый слой базы. Сейчас это PDO + временные polyfill-функции для старого `mysql_*`.

Цель переписывания: убрать `mysql_*` полностью, затем заменить строковые SQL-сборки на подготовленные запросы.

## Игровое ядро

1. `include/class/index.class.php`
   Рейтинги, онлайн, сервисные игровые обновления.

2. `include/function/globfanction.php`
   Общие функции проекта. Название старое, но файл критичный.

3. `include/function/functionusers.php`
   Отображение пользователей, группы, цвета и похожая логика.

4. `include/function/fanction.games.php`
   Большой игровой helper. Один из главных кандидатов на разбиение.

5. `include/function/fanction.games.post.php`
   POST-действия игры.

6. `include/function/get.map.php` и `include/function/post.map.php`
   AJAX-логика карты.

7. `include/function/battle.functions.php`
   Боевая система.

8. `include/function/battle.functions.tip.php`
   Дополнительная боевая логика и статусы.

9. `include/function/atk.php`
   Применение атаки в бою.

## Игровые экраны

Все основные экраны лежат в `include/files`.

Критический минимум:

1. `include/files/shapka.php` и `include/files/bottom.php`
   Общая оболочка игрового интерфейса.

2. `include/files/start.php`
   Стартовый экран после входа.

3. `include/files/map.world.php`
   Карта мира.

4. `include/files/char.world.php`
   Блок персонажа и текущей локации.

5. `include/files/chat.world.php`
   Чат.

6. `include/files/buttons.world.php`
   Кнопки действий.

7. `include/files/mapusers.world.php`
   Игроки на карте.

8. `include/files/fight_pve.world.php`
   PvE-бой.

9. `include/files/fight_pvp.world.php`
   PvP-бой.

10. `include/files/pokemon.php`
    Мои покемоны.

11. `include/files/items.users.php`
    Инвентарь.

12. `include/files/pokedex.php`
    Покедекс.

13. `include/files/profile.users.php`
    Профиль.

14. `include/files/registration.php` и `include/files/registrationseve.php`
    Регистрация.

## Локации и NPC

1. `include/rooms/*.php`
   Старые файлы локаций.

2. `include/rooms/npc/*.php`
   NPC, магазины, квесты, стартовый выбор покемона.

Цель переписывания: сначала оставить как совместимый слой, потом вынести локации и NPC в таблицы/JSON-конфиги или нормальные классы.

## Админка

Главный вход:

1. `admin/admin.php`
   Роутер админки.

Критические модули:

1. `admin/poke.php`
   Управление/выдача покемонов.

2. `admin/gitem.php`
   Выдача предметов.

3. `admin/attak_pokes.php`
   Правка атак.

4. `admin/info_pokes.php`
   Информация по покемонам.

5. `admin/alm.php`
   Алмазы/платежная часть.

6. `admin/bb_news_admin.php`
   Новости.

Цель переписывания: сделать отдельный защищенный `AdminController`, CSRF, роли, аудит действий.

## Фронт и ассеты

Код:

1. `css/style0.css`
2. `css/skin.css`
3. `css/superfish.css`
4. `script/jquery.js`
5. `script/poke_spa.js`
6. `script/map.js`
7. `script/pkmnBattle.js`

Ассеты:

1. `img/lop.png`
2. `img/prize/1nagrada.png`
3. `pok/normal`, `pok/shine`, `pok/anim`

В `pokemon8.0` большие папки спрайтов созданы как места под будущий перенос, но сами тысячи картинок не копировались.

## Что не тащить в новую версию

1. `cache`, `templates_c`, `log`
   Runtime-данные, не исходники.

2. `webgrind`
   Старый профайлер, не нужен для запуска игры.

3. `test`, `cgi-bin`, `.idea`
   Локальная среда и старые эксперименты.

4. `index_old.php`, `1index.html`, `testx.php`, `text.php`, `lalka.php`
   Старые копии и одноразовые файлы.

5. `admin/* old.php`, `admin/* work100%.php`, `admin/* (1).php`
   Старые дубли. Использовать только как справку, не как основу.

## Порядок работ

1. Стабилизировать новый запуск
   Проверить PHP 8.3+, `.env`, импорт SQL, открытие `/` через `public/index.php`.

2. Авторизация и безопасность
   Новая авторизация уже начинается в `src/Controller/AuthController.php`. Следующий шаг - миграция старых MD5-хэшей на `password_hash/password_verify`.

3. База
   Новое ядро использует `src/Database/Connection.php` и репозитории. Legacy `include/function/db3.php` больше не расширять.

4. Роутинг
   Вместо старого `game.php` переносить экраны в контроллеры: `Map`, `Character`, `Battle`, `Inventory`, `Pokedex`, `Admin`.

5. Бои
   Вынести `battle.functions.php`, `battle.functions.tip.php`, `atk.php` в отдельный боевой модуль.

6. Карта и чат
   Переписать `map.world.php`, `get.map.php`, `post.map.php`, `chat.world.php` на JSON API + frontend.

7. Админка
   Переписать `admin/admin.php` и самые опасные действия: выдача предметов, покемонов, правка атак.

8. Ассеты
   После кода перенести нужные картинки из старого `pok`, `img`, `new_diz`, удалить дубли.

## Контрольный список запуска

1. `.env` заполнен.
2. SQL импортирован.
3. PHP видит `pdo_mysql`.
4. Главная `/` открывается без fatal error.
5. Авторизация проходит.
6. `/game.php?go=start` открывается.
7. `/game.php?go=map` открывается.
8. Чат не ломает страницу.
9. PvE-бой хотя бы загружается.
10. Админка доступна только админу.
