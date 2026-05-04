# Pokemon 8.0 battle math + dex fix

Заменяемые файлы:

```text
src/Game/BattleMathService.php              новый
src/Game/BattleEngineService.php            заменить
src/Repository/BattleRepository.php         заменить
src/Repository/DexRepository.php            новый
src/Controller/DexApiController.php         новый
public/index.php                            заменить, добавлены маршруты /api/dex/*
views/game-start.php                        заменить, добавлены кнопки Покедекс/Атакадекс и overlay
public/js/dex-overlay.js                    новый
public/css/game-start.css                   заменить/или перенести добавленный CSS в свой файл
```

Что сделано:

- Старт боя без фейковых `+6`.
- `+6/-6` теперь являются лимитами стадий, а не стартовыми значениями.
- Обычные статы считаются по формуле: `base * ((2 + plus) / (2 + minus))`.
- Точность/ловкость считаются отдельно: `accuracy * ((3 + accuracy+ + targetEvasion-) / (3 + accuracy- + targetEvasion+))`.
- Урон считает физ/спец атаку, физ/спец защиту, STAB, эффективность типов, крит и случайный множитель 85-100%.
- `stat_attak` используется как источник основных бафов/дебафов атак.
- `attac_dop` используется для вторичных эффектов атак.
- `bttle_status/status` подключены для яд/сон/ожог/заморозка/паралич/испуг/спутанность.
- Ожог режет физическую атаку и наносит периодический урон.
- Яд наносит периодический урон.
- Сон/заморозка/паралич/испуг/спутанность могут пропускать ход.
- Покебол списывается при каждой попытке, не только при успехе.
- Добавлены API:
  - `GET /api/dex/pokemon?q=`
  - `GET /api/dex/pokemon/show?id=`
  - `GET /api/dex/attacks?q=`
  - `GET /api/dex/attack/show?id=`
- Добавлено простое окно Покедекс/Атакадекс в `/game`.

Проверка математики:

```text
base 100 +6 / -0 = 400
base 100 +0 / -6 = 25
base 100 +2 / -1 = 133
accuracy 100 with accuracy -6 = 33%
Electric vs Water/Flying = x4
```

SQL из `sql/OPTIONAL_BATTLE_DEX_SCHEMA_FIXES.sql` не обязателен, но очень желательно прогнать после backup БД. Если MySQL скажет, что PRIMARY/INDEX уже существует — просто пропусти соответствующую строку.
