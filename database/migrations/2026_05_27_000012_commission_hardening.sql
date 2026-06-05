-- Commission market hardening before open-test economy QA.
-- Adds explicit lot freeze audit fields, object reserve ledger and stricter
-- configurable limits without breaking existing active lots.

SET @has_locked_by := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'market_lots'
     AND COLUMN_NAME = 'locked_by'
);
SET @sql := IF(@has_locked_by = 0,
  'ALTER TABLE market_lots ADD COLUMN locked_by INT NOT NULL DEFAULT 0 AFTER commission_amount',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_locked_at := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'market_lots'
     AND COLUMN_NAME = 'locked_at'
);
SET @sql := IF(@has_locked_at = 0,
  'ALTER TABLE market_lots ADD COLUMN locked_at INT NOT NULL DEFAULT 0 AFTER locked_by',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_lock_reason := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'market_lots'
     AND COLUMN_NAME = 'lock_reason'
);
SET @sql := IF(@has_lock_reason = 0,
  'ALTER TABLE market_lots ADD COLUMN lock_reason VARCHAR(32) NOT NULL DEFAULT "" AFTER locked_at',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_lock_token := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'market_lots'
     AND COLUMN_NAME = 'lock_token'
);
SET @sql := IF(@has_lock_token = 0,
  'ALTER TABLE market_lots ADD COLUMN lock_token VARCHAR(64) NOT NULL DEFAULT "" AFTER lock_reason',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_market_lock_idx := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'market_lots'
     AND INDEX_NAME = 'idx_market_lots_lock'
);
SET @sql := IF(@has_market_lock_idx = 0,
  'ALTER TABLE market_lots ADD INDEX idx_market_lots_lock (locked_at, locked_by)',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

CREATE TABLE IF NOT EXISTS `market_reserved_objects` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `lot_id` BIGINT UNSIGNED NOT NULL,
  `seller_id` INT NOT NULL DEFAULT 0,
  `object_type` VARCHAR(16) NOT NULL,
  `object_id` INT NOT NULL,
  `expires_at` INT NOT NULL DEFAULT 0,
  `created_at` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_market_reserved_object` (`object_type`, `object_id`),
  UNIQUE KEY `uniq_market_reserved_lot` (`lot_id`),
  KEY `idx_market_reserved_seller` (`seller_id`, `created_at`),
  KEY `idx_market_reserved_expiry` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `market_reserved_objects`
  (`lot_id`, `seller_id`, `object_type`, `object_id`, `expires_at`, `created_at`)
SELECT `id`, `seller_id`, `object_type`, `object_id`, `expires_at`, `created_at`
  FROM `market_lots`
 WHERE `status` = 'active'
   AND `object_type` IN ('pokemon', 'egg');

INSERT INTO `site_settings` (`name`, `value`, `updated_by`, `updated_at`)
VALUES
  ('commission.max_quantity_per_lot', '9999', 0, UNIX_TIMESTAMP()),
  ('commission.max_total_price', '999999999', 0, UNIX_TIMESTAMP()),
  ('commission.freeze_timeout_seconds', '300', 0, UNIX_TIMESTAMP()),
  ('commission.risk_total_price', '50000000', 0, UNIX_TIMESTAMP()),
  ('commission.risk_unit_price', '10000000', 0, UNIX_TIMESTAMP()),
  ('commission.risk_easy_item_total', '1000000', 0, UNIX_TIMESTAMP()),
  ('commission.risk_easy_item_unit', '500000', 0, UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);
