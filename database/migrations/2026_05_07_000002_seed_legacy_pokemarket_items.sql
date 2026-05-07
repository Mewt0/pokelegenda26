SET @has_market_max_owned := (
  SELECT COUNT(*)
    FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'market_shop_items'
     AND COLUMN_NAME = 'max_owned'
);

SET @market_max_owned_sql := IF(
  @has_market_max_owned = 0,
  'ALTER TABLE market_shop_items ADD COLUMN max_owned INT NOT NULL DEFAULT 0 AFTER max_count',
  'SELECT 1'
);

PREPARE market_max_owned_stmt FROM @market_max_owned_sql;
EXECUTE market_max_owned_stmt;
DEALLOCATE PREPARE market_max_owned_stmt;

UPDATE market_shop_items
   SET price = 250,
       min_count = 1,
       max_count = 99,
       max_owned = 0,
       sort_order = 10,
       note = 'Legacy Покемаркет: Покебол.',
       updated_at = UNIX_TIMESTAMP()
 WHERE item_id = 3;

UPDATE market_shop_items
   SET price = 500,
       min_count = 1,
       max_count = 99,
       max_owned = 0,
       sort_order = 20,
       note = 'Legacy Покемаркет: Энергетик.',
       updated_at = UNIX_TIMESTAMP()
 WHERE item_id = 15;

INSERT INTO market_shop_items
  (item_id, currency_item_id, price, min_count, max_count, max_owned, stock, enabled, sort_order, note, created_at, updated_at)
SELECT 22, 1, 500000, 1, 10, 0, -1, 1, 30, 'Legacy Покемаркет: Фонарик.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
FROM DUAL
WHERE EXISTS (SELECT 1 FROM items WHERE id = 22)
  AND NOT EXISTS (SELECT 1 FROM market_shop_items WHERE item_id = 22);

UPDATE market_shop_items
   SET price = 500000,
       min_count = 1,
       max_count = 10,
       max_owned = 0,
       sort_order = 30,
       note = 'Legacy Покемаркет: Фонарик.',
       enabled = 1,
       updated_at = UNIX_TIMESTAMP()
 WHERE item_id = 22;

INSERT INTO market_shop_items
  (item_id, currency_item_id, price, min_count, max_count, max_owned, stock, enabled, sort_order, note, created_at, updated_at)
SELECT 23, 1, 350000, 1, 1, 1, -1, 1, 40, 'Legacy Покемаркет: Каменная Кирка. В старом магазине нельзя было купить вторую, пока первая есть.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
FROM DUAL
WHERE EXISTS (SELECT 1 FROM items WHERE id = 23)
  AND NOT EXISTS (SELECT 1 FROM market_shop_items WHERE item_id = 23);

UPDATE market_shop_items
   SET price = 350000,
       min_count = 1,
       max_count = 1,
       max_owned = 1,
       sort_order = 40,
       note = 'Legacy Покемаркет: Каменная Кирка. В старом магазине нельзя было купить вторую, пока первая есть.',
       enabled = 1,
       updated_at = UNIX_TIMESTAMP()
 WHERE item_id = 23;

INSERT INTO market_shop_items
  (item_id, currency_item_id, price, min_count, max_count, max_owned, stock, enabled, sort_order, note, created_at, updated_at)
SELECT 69, 1, 125000, 1, 1, 1, -1, 1, 50, 'Legacy Покемаркет: Пропуск на Электростанцию. В старом магазине нельзя было купить второй, пока первый есть.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
FROM DUAL
WHERE EXISTS (SELECT 1 FROM items WHERE id = 69)
  AND NOT EXISTS (SELECT 1 FROM market_shop_items WHERE item_id = 69);

UPDATE market_shop_items
   SET price = 125000,
       min_count = 1,
       max_count = 1,
       max_owned = 1,
       sort_order = 50,
       note = 'Legacy Покемаркет: Пропуск на Электростанцию. В старом магазине нельзя было купить второй, пока первый есть.',
       enabled = 1,
       updated_at = UNIX_TIMESTAMP()
 WHERE item_id = 69;

UPDATE market_shop_items
   SET sort_order = 100, updated_at = UNIX_TIMESTAMP()
 WHERE item_id = 6;

UPDATE market_shop_items
   SET sort_order = 110, updated_at = UNIX_TIMESTAMP()
 WHERE item_id = 7;

UPDATE market_shop_items
   SET sort_order = 120, updated_at = UNIX_TIMESTAMP()
 WHERE item_id = 8;

UPDATE market_shop_items
   SET sort_order = 130, updated_at = UNIX_TIMESTAMP()
 WHERE item_id = 9;

UPDATE market_shop_items
   SET sort_order = 140, updated_at = UNIX_TIMESTAMP()
 WHERE item_id = 10;

UPDATE market_shop_items
   SET sort_order = 150, updated_at = UNIX_TIMESTAMP()
 WHERE item_id = 11;

UPDATE market_shop_items
   SET sort_order = 200, updated_at = UNIX_TIMESTAMP()
 WHERE item_id = 330;

UPDATE market_shop_items
   SET sort_order = 210, updated_at = UNIX_TIMESTAMP()
 WHERE item_id = 678;
