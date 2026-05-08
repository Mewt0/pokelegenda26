CREATE TABLE IF NOT EXISTS site_settings (
  name VARCHAR(64) NOT NULL,
  value TEXT NOT NULL,
  updated_by INT NOT NULL DEFAULT 0,
  updated_at INT NOT NULL DEFAULT 0,
  PRIMARY KEY (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO site_settings (name, value, updated_by, updated_at)
VALUES ('techwork', '0', 0, UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE name = VALUES(name);

SET @db_name := DATABASE();

SET @idx_users_login := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'users' AND INDEX_NAME = 'idx_users_login'
);
SET @sql := IF(@idx_users_login = 0, 'ALTER TABLE users ADD INDEX idx_users_login (login)', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @idx_items_name := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'items' AND INDEX_NAME = 'idx_items_name'
);
SET @sql := IF(@idx_items_name = 0, 'ALTER TABLE items ADD INDEX idx_items_name (name(64))', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @idx_market_enabled_sort := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'market_shop_items' AND INDEX_NAME = 'idx_market_enabled_sort'
);
SET @sql := IF(@idx_market_enabled_sort = 0, 'ALTER TABLE market_shop_items ADD INDEX idx_market_enabled_sort (enabled, sort_order)', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @idx_admin_audit_created := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'admin_audit_log' AND INDEX_NAME = 'idx_admin_audit_created'
);
SET @sql := IF(@idx_admin_audit_created = 0, 'ALTER TABLE admin_audit_log ADD INDEX idx_admin_audit_created (created_at)', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @idx_pok_user_admin_filters := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'pok_user' AND INDEX_NAME = 'idx_pok_user_admin_filters'
);
SET @sql := IF(@idx_pok_user_admin_filters = 0, 'ALTER TABLE pok_user ADD INDEX idx_pok_user_admin_filters (users, basenum, lvl, active, training_stage)', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @idx_items_poke_admin_lookup := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'items_poke' AND INDEX_NAME = 'idx_items_poke_admin_lookup'
);
SET @sql := IF(@idx_items_poke_admin_lookup = 0, 'ALTER TABLE items_poke ADD INDEX idx_items_poke_admin_lookup (id_poke, id_items)', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
