-- Keep legacy crafting/evolution item ids as the source of truth when names already exist.
-- New catalogue ids from plan.md stay in the DB, but shop/use rules point to the old working ids.

UPDATE items
   SET name = 'Маленький осколок Огненного камня',
       tittle = 'Осколок красно-желтого окраса. Требуется для изготовления предметов.<br> Категория: <font color=gold>Крафт->Обычные</font>.',
       uses = 0,
       dress = 0,
       delet = 1,
       torg = 1,
       elementary = 0,
       battleuse = 0
 WHERE id = 25
 LIMIT 1;

INSERT INTO items (
    id, cools, name, tittle, category, uses, dress, delet, torg,
    elementary, timesnapoke, times, battleuse, dopolnen
)
SELECT 90005, 0, 'Премиум бол', 'Улучшенный покебол с повышенным шансом ловли.', 0, 1, 0, 1, 1, 0, 1, '0', 0, 'premium'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM items WHERE id = 90005);

UPDATE items
   SET name = 'Премиум бол',
       tittle = 'Улучшенный покебол с повышенным шансом ловли.',
       uses = 1,
       delet = 1,
       torg = 1
 WHERE id = 90005
 LIMIT 1;

UPDATE market_shop_items
   SET enabled = 0,
       note = 'disabled:legacy_name_alias',
       updated_at = UNIX_TIMESTAMP()
 WHERE item_id IN (25, 67, 68, 75, 77, 651, 652, 653);

INSERT INTO market_shop_items (
    item_id, currency_item_id, price, min_count, max_count, max_owned,
    stock, enabled, sort_order, note, created_at, updated_at
)
SELECT seed.item_id, 1, seed.price, 1, seed.max_count, 0, -1, 1, seed.sort_order, seed.note, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
FROM (
    SELECT 90005 AS item_id, 1200 AS price, 99 AS max_count, 300 AS sort_order, 'plan:pokeballs:legacy_safe' AS note UNION ALL
    SELECT 40, 80000, 99, 370, 'plan:evolution_stones:legacy_id' UNION ALL
    SELECT 41, 80000, 99, 340, 'plan:evolution_stones:legacy_id' UNION ALL
    SELECT 42, 80000, 99, 390, 'plan:evolution_stones:legacy_id' UNION ALL
    SELECT 43, 80000, 99, 350, 'plan:evolution_stones:legacy_id' UNION ALL
    SELECT 10, 100000, 99, 450, 'plan:candies:legacy_id' UNION ALL
    SELECT 11, 100000, 99, 440, 'plan:candies:legacy_id' UNION ALL
    SELECT 17, 150000, 99, 460, 'plan:candies:legacy_id'
) AS seed
ON DUPLICATE KEY UPDATE
    currency_item_id = VALUES(currency_item_id),
    price = VALUES(price),
    min_count = VALUES(min_count),
    max_count = VALUES(max_count),
    max_owned = VALUES(max_owned),
    stock = VALUES(stock),
    enabled = VALUES(enabled),
    sort_order = VALUES(sort_order),
    note = VALUES(note),
    updated_at = UNIX_TIMESTAMP();

UPDATE item_target_rules
   SET enabled = 0,
       updated_at = UNIX_TIMESTAMP()
 WHERE item_id IN (67, 68, 75, 77, 651, 652, 653);

INSERT INTO item_target_rules (
    item_id, enabled, target_type, allow_quantity, min_count, max_count,
    effect_key, consume_on_success, ui_title, ui_hint, created_at, updated_at
)
SELECT seed.item_id, 1, 'pokemon', seed.allow_quantity, 1, seed.max_count,
       'pending', 1, seed.ui_title, seed.ui_hint, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
FROM (
    SELECT 40 AS item_id, 0 AS allow_quantity, 1 AS max_count, 'Эволюция покемона' AS ui_title, 'Выберите покемона, к которому применить Громовой камень.' AS ui_hint UNION ALL
    SELECT 41, 0, 1, 'Эволюция покемона', 'Выберите покемона, к которому применить Огненный камень.' UNION ALL
    SELECT 42, 0, 1, 'Эволюция покемона', 'Выберите покемона, к которому применить Водный камень.' UNION ALL
    SELECT 43, 0, 1, 'Эволюция покемона', 'Выберите покемона, к которому применить Лиственный камень.' UNION ALL
    SELECT 44, 0, 1, 'Эволюция покемона', 'Выберите покемона, к которому применить Лунный камень.' UNION ALL
    SELECT 10, 1, 99, 'Конфета покемону', 'Выберите покемона и количество розовых конфет.' UNION ALL
    SELECT 11, 1, 99, 'Конфета покемону', 'Выберите покемона и количество красных конфет.' UNION ALL
    SELECT 17, 1, 99, 'Конфета покемону', 'Выберите покемона и количество шоколадных конфет.'
) AS seed
ON DUPLICATE KEY UPDATE
    enabled = VALUES(enabled),
    target_type = VALUES(target_type),
    allow_quantity = VALUES(allow_quantity),
    min_count = VALUES(min_count),
    max_count = VALUES(max_count),
    effect_key = VALUES(effect_key),
    consume_on_success = VALUES(consume_on_success),
    ui_title = VALUES(ui_title),
    ui_hint = VALUES(ui_hint),
    updated_at = UNIX_TIMESTAMP();
