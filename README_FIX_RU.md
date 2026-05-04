# Pokemon 8.0 — battle UI + dex fix

Заменить файлы:

```text
src/Game/BattleMathService.php
src/Game/BattleEngineService.php
src/Repository/BattleRepository.php
src/Repository/DexRepository.php
public/js/dex-overlay.js
public/css/game-start.css
views/game-start.php
```

Что исправлено:

1. Окно боя больше не растягивается от большого лога.
   Правая панель лога стала отдельным scroll-контейнером.
2. Арена больше не увеличивается от раундов/лога.
3. Спрайт игрока больше не пропадает:
   - добавлены fallback пути `/pok/sback`, `/pok/back`, `/pok/anim`, `/pok/pok`, `/pok/normal`;
   - shiny определяется не только по `tips`, но и по имени.
4. `/api/dex/*` оставлен, но DexRepository переписан под реальные таблицы сервера:
   - `pokemon`
   - `poke_base`
   - `attac_power`
   - `attac_poke`
   - `attac_egg`
   - `pokebuild`
   - `build`
   - `stat_attak`
   - `attac_dop`
5. Переходы на старые ссылки вида `game.php?go=pokedex&id=...` и `game.php?go=atk&id=...` теперь перехватываются JS и открывают новый overlay-декс.
6. Математика:
   - `atac_accuracy = 0` теперь считается как 100% попадание / never miss;
   - `attac_dop` с шансом 0 больше не становится 100% эффектом;
   - паралич режет скорость в 2 раза.
7. Finished-state боя теперь можно увидеть после refresh до `ack-end`.
8. `battle_log` получил fallback для БД без AUTO_INCREMENT.

Файл SQL внутри пакета необязательный. Код работает и без него, но SQL можно использовать после backup для улучшения структуры.
