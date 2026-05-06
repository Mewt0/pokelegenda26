CREATE TABLE IF NOT EXISTS market_shop_items (
  id INT NOT NULL AUTO_INCREMENT,
  item_id INT NOT NULL,
  currency_item_id INT NOT NULL DEFAULT 1,
  price INT NOT NULL,
  min_count INT NOT NULL DEFAULT 1,
  max_count INT NOT NULL DEFAULT 99,
  stock INT NOT NULL DEFAULT -1,
  enabled TINYINT NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0,
  note VARCHAR(255) NOT NULL DEFAULT '',
  created_at INT NOT NULL DEFAULT 0,
  updated_at INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY uq_market_shop_item (item_id),
  KEY idx_market_shop_enabled (enabled, sort_order),
  KEY idx_market_shop_currency (currency_item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO market_shop_items
  (item_id, currency_item_id, price, min_count, max_count, stock, enabled, sort_order, note, created_at, updated_at)
SELECT 3, 1, 200, 1, 99, -1, 1, 10, 'Базовый покебол для ловли диких покемонов.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
FROM DUAL
WHERE EXISTS (SELECT 1 FROM items WHERE id = 3)
  AND NOT EXISTS (SELECT 1 FROM market_shop_items WHERE item_id = 3);

INSERT INTO market_shop_items
  (item_id, currency_item_id, price, min_count, max_count, stock, enabled, sort_order, note, created_at, updated_at)
SELECT 330, 1, 500000, 1, 20, -1, 1, 100, 'Набор тренировки за местную валюту.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
FROM DUAL
WHERE EXISTS (SELECT 1 FROM items WHERE id = 330)
  AND NOT EXISTS (SELECT 1 FROM market_shop_items WHERE item_id = 330);

INSERT INTO market_shop_items
  (item_id, currency_item_id, price, min_count, max_count, stock, enabled, sort_order, note, created_at, updated_at)
SELECT 678, 1, 500000, 1, 20, -1, 1, 110, 'Набор ослабления за местную валюту.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
FROM DUAL
WHERE EXISTS (SELECT 1 FROM items WHERE id = 678)
  AND NOT EXISTS (SELECT 1 FROM market_shop_items WHERE item_id = 678);
