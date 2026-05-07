INSERT INTO market_shop_items
  (item_id, currency_item_id, price, min_count, max_count, stock, enabled, sort_order, note, created_at, updated_at)
SELECT 6, 1, 15000, 1, 50, -1, 1, 20, 'Желтая конфета для базовой прокачки.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
FROM DUAL
WHERE EXISTS (SELECT 1 FROM items WHERE id = 6)
  AND NOT EXISTS (SELECT 1 FROM market_shop_items WHERE item_id = 6);

INSERT INTO market_shop_items
  (item_id, currency_item_id, price, min_count, max_count, stock, enabled, sort_order, note, created_at, updated_at)
SELECT 7, 1, 30000, 1, 50, -1, 1, 30, 'Зеленая конфета с EV-бонусом.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
FROM DUAL
WHERE EXISTS (SELECT 1 FROM items WHERE id = 7)
  AND NOT EXISTS (SELECT 1 FROM market_shop_items WHERE item_id = 7);

INSERT INTO market_shop_items
  (item_id, currency_item_id, price, min_count, max_count, stock, enabled, sort_order, note, created_at, updated_at)
SELECT 8, 1, 45000, 1, 30, -1, 1, 40, 'Голубая конфета с усиленным EV-бонусом.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
FROM DUAL
WHERE EXISTS (SELECT 1 FROM items WHERE id = 8)
  AND NOT EXISTS (SELECT 1 FROM market_shop_items WHERE item_id = 8);

INSERT INTO market_shop_items
  (item_id, currency_item_id, price, min_count, max_count, stock, enabled, sort_order, note, created_at, updated_at)
SELECT 9, 1, 60000, 1, 25, -1, 1, 50, 'Фиолетовая конфета для продвинутой прокачки.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
FROM DUAL
WHERE EXISTS (SELECT 1 FROM items WHERE id = 9)
  AND NOT EXISTS (SELECT 1 FROM market_shop_items WHERE item_id = 9);

INSERT INTO market_shop_items
  (item_id, currency_item_id, price, min_count, max_count, stock, enabled, sort_order, note, created_at, updated_at)
SELECT 10, 1, 80000, 1, 20, -1, 1, 60, 'Розовая конфета для сильной прокачки.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
FROM DUAL
WHERE EXISTS (SELECT 1 FROM items WHERE id = 10)
  AND NOT EXISTS (SELECT 1 FROM market_shop_items WHERE item_id = 10);

INSERT INTO market_shop_items
  (item_id, currency_item_id, price, min_count, max_count, stock, enabled, sort_order, note, created_at, updated_at)
SELECT 11, 1, 100000, 1, 20, -1, 1, 70, 'Красная конфета для максимального EV-бонуса.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
FROM DUAL
WHERE EXISTS (SELECT 1 FROM items WHERE id = 11)
  AND NOT EXISTS (SELECT 1 FROM market_shop_items WHERE item_id = 11);

INSERT INTO market_shop_items
  (item_id, currency_item_id, price, min_count, max_count, stock, enabled, sort_order, note, created_at, updated_at)
SELECT 15, 1, 1000, 1, 99, -1, 1, 80, 'Энергетик для пробуждения покемона.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
FROM DUAL
WHERE EXISTS (SELECT 1 FROM items WHERE id = 15)
  AND NOT EXISTS (SELECT 1 FROM market_shop_items WHERE item_id = 15);
