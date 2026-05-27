# QA Report: Trainer Card, Primal/Mega, Weather

Дата: 2026-05-26

## Проверено

- Trainer Card через `ProfileRepository` для `Tacos` и `NIGA`: пользователь, UID, локация, друзья, награды/подарки и активная команда возвращаются без `null/undefined`-полей в обязательных блоках.
- Player menu: hover/click открывает новую trainer-card modal, старые `/game/profile` ссылки перехватываются фронтом.
- Blue Orb / Red Orb / Dragon Ascent правила трансформаций.
- Primal Kyogre, Primal Groudon, Mega Rayquaza в PvP battle-state.
- Сохранение moveset обычного покемона во временной форме.
- Сброс формы после завершения боя.
- Strong weather: Primordial Sea, Desolate Land, Delta Stream.
- AbilityDex-описания для трёх сильных погод.
- Read-only HTTP smoke основного `/game` API.

## Найдено и исправлено

| ID | Система | Баг | Исправление | Статус |
|---|---|---|---|---|
| BUG-001 | Battle end | `finishPvpBattle()` падал на `SQLSTATE[HY093]` при сбросе `battle_transformations`, потому что PDO с native prepares не принимает повторный `:time`. | Разделены placeholders на `:reverted_at` и `:updated_at`. | fixed |
| BUG-002 | Held items | Blue Orb / Red Orb можно было пытаться надеть на неподходящего покемона через target-use/equip flow. | Добавлена серверная проверка `BattleTransformationCatalog::matchRule()` в `InventoryRepository` перед экипировкой. | fixed |
| BUG-003 | Weather | Обычная погода от способностей могла перетереть `heavy_rain`, `harsh_sun`, `strong_winds`. | В `applySingleEntryWeatherAbility()` обычная погода блокируется, если уже активна сильная погода. | fixed |
| BUG-004 | Weather refresh | Strong weather могла не переактивироваться после смены активного покемона из-за volatile `weather_started`. | Для сильной погоды больше не используется одноразовый volatile-флаг. | fixed |
| BUG-005 | Battle logs | В логах не было технических маркеров для спорных ситуаций. | Добавлены `[TRANSFORM]`, `[WEATHER]`, `[DAMAGE_MODIFIER]`, `[DAMAGE_BLOCKED]`, `[REVERT]`. | fixed |
| BUG-006 | AbilityDex | Описание Delta Stream было неточным относительно сильных погод. | Добавлена и применена миграция `2026_05_26_000004_harden_battle_transformation_abilities.sql`. | fixed |

## Smoke Checks

Транзакционный DB-smoke:

- Blue Orb подходит Kyogre и не подходит Bulbasaur.
- Red Orb подходит Groudon и не подходит Kyogre.
- Rayquaza без Dragon Ascent не трансформируется.
- Rayquaza с Dragon Ascent трансформируется в `5019`.
- Kyogre + Blue Orb -> `5017`, weather `heavy_rain`, dex number `382`, moveset сохранён, `pok_user.basenum=382`, после `finishPvpBattle()` active transform = 0 и есть `[REVERT]`.
- Groudon + Red Orb -> `5018`, weather `harsh_sun`, dex number `383`, moveset сохранён, после боя сброс.
- Rayquaza + Dragon Ascent -> `5019`, weather `strong_winds`, dex number `384`, moveset сохранён, после боя сброс.
- Primordial Sea блокирует Fire.
- Desolate Land блокирует Water.
- Primordial Sea бустит Water x1.5.
- Desolate Land бустит Fire x1.5.
- Delta Stream нейтрализует Flying weakness для Ice/Rock/Electric.

HTTP smoke:

- `tools/http_smoke.php --base=http://pokemonchic.com --login=Tacos --password=jungheinrick --battle=state`
- Результат: 32 checks, 0 failures.

Lint:

- `php -l src/Game/BattleEngineService.php`
- `php -l src/Repository/BattleRepository.php`
- `php -l src/Repository/InventoryRepository.php`
- `php -l src/Repository/ProfileRepository.php`
- `php -l src/Repository/PokemonRepository.php`
- `php -l src/Repository/DexRepository.php`
- `php -l src/Game/BattleTransformationCatalog.php`
- `node --check public/js/trainer-profile-window.js`
- `node --check public/js/player-menu.js`
- `node --check public/js/chat.js`

## Осталось для ручного QA

- Двухоконный бой реальными аккаунтами: Kyogre/Groudon/Rayquaza против разных погодных способностей.
- Визуальный browser-regression trainer-card modal на мобильном/desktop. Codex in-app browser сейчас не дал активную browser pane, поэтому визуальный screenshot не снят.
- Полный PvE/PvP прогон всех боевых предметов и редких edge cases смены покемона.
