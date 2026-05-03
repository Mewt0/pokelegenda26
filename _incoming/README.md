# Incoming: pokemonchic.com

Источник: `D:\ospanel\domains\pokemonchic.com`

Эта папка содержит уже подготовленный PHP 8.x rewrite pack из другого домена. Я скопировал его сюда в двух видах:

1. `pokemonchic.com/`
   Оригинальная структура как в источнике.

2. `mapped/`
   Файлы, у которых путь действительно совпадает с текущим проектом, плюс админка.

3. `mapped/root_flat/`
   Плоские корневые файлы из источника. Они НЕ считаются заменой одноименных файлов из `include/`. Например исходный `db.php` не является `include/function/db3.php`, а исходный `config.php` не является `include/function/config.php`.

## Что можно брать почти сразу

1. Документы:
   - `pokemonchic.com/MIGRATION.md`
   - `pokemonchic.com/CLEANUP_NOTES.md`
   - `pokemonchic.com/LINT_RESULT.txt`
   - `pokemonchic.com/README_PHP8_FULL_REWRITE.txt`
   - `pokemonchic.com/README_NGINX_403_FIX.txt`

2. Админские модули как материал для переписывания:
   - `mapped/admin/gitem.php`
   - `mapped/admin/poke.php`
   - `mapped/admin/attak_pokes.php`
   - `mapped/admin/info_pokes.php`
   - `mapped/admin/alm.php`

3. Небольшие системные файлы, если путь совпадает:
   - `mapped/ban.php`
   - `mapped/attak_pokes.php`
   - `mapped/page.php`
   - `mapped/mailTo.php`
   - `mapped/style.php`

## Что брать осторожно

1. `mapped/root_flat/db.php`
   Файл переписан на `mysqli`, но содержит старые хардкоженные доступы к базе. Это НЕ замена `include/function/db3.php`. В текущем `pokemon8.0` лучше оставить PDO + `.env`.

2. `mapped/root_flat/config.php`
   Это корневой файл из плоского rewrite pack. Это НЕ замена `include/function/config.php`. Если брать идеи, их нужно адаптировать к `app/bootstrap.php`.

3. `mapped/autoriz.php`
   Полезный по идеям: `filter_input`, `mb_strlen`, нормальный IP helper. Но файл нужно поправить перед применением: там есть повторный `declare(strict_types=1);`, и логика паролей пока всё ещё совместимая со старым хэшем.

4. `mapped/index.php`
   Очень укороченная главная. Можно использовать как пример чистки, но она теряет старый контент/верстку.

5. `mapped/admin/admin.php`
   Роутер админки похож на старый. Нужен как материал, но лучше переписать с whitelist-массивом маршрутов и CSRF.

6. `mapped/root_flat/buttons_world.php`, `mapped/root_flat/loc.world.php`, `mapped/root_flat/config.room.php`
   Названия похожи на файлы из `include`, но источник лежал в корне. Сначала сравнить вручную, потом решать, куда переносить.

## Что не переносить в production

1. `pokemonchic.com/testx.php`
2. `pokemonchic.com/testind.php`
3. `pokemonchic.com/text.php`
4. `pokemonchic.com/pest.php`
5. `pokemonchic.com/log_php_errors.txt`
6. `pokemonchic.com/pokemonchic.com.zip`

## Рекомендуемый способ работы

1. Сравнить файл из `pokemon8.0/...` с `pokemon8.0/_incoming/mapped/...`.
2. Забрать только улучшения: типизацию, фильтрацию ввода, безопасный вывод, `session_status()`.
3. Не возвращать хардкоженные пароли и старую структуру конфигов.
4. После переноса запускать `php -l` по измененному файлу.
