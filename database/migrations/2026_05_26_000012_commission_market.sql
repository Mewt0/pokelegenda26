CREATE TABLE IF NOT EXISTS `market_lots` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `seller_id` INT NOT NULL,
  `seller_name` VARCHAR(64) NOT NULL DEFAULT '',
  `object_type` VARCHAR(16) NOT NULL,
  `object_id` INT NOT NULL,
  `object_name` VARCHAR(160) NOT NULL DEFAULT '',
  `object_icon` VARCHAR(255) NOT NULL DEFAULT '',
  `category` VARCHAR(32) NOT NULL DEFAULT 'other',
  `object_snapshot_json` LONGTEXT NULL,
  `reserve_payload_json` LONGTEXT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `price_per_unit` INT NOT NULL DEFAULT 0,
  `total_price` INT NOT NULL DEFAULT 0,
  `status` VARCHAR(16) NOT NULL DEFAULT 'active',
  `created_at` INT NOT NULL DEFAULT 0,
  `expires_at` INT NOT NULL DEFAULT 0,
  `sold_at` INT NOT NULL DEFAULT 0,
  `buyer_id` INT NOT NULL DEFAULT 0,
  `private_buyer_id` INT NOT NULL DEFAULT 0,
  `commission_amount` INT NOT NULL DEFAULT 0,
  `legacy_source_type` VARCHAR(32) NULL,
  `legacy_source_id` INT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_market_lots_legacy` (`legacy_source_type`, `legacy_source_id`),
  KEY `idx_market_lots_status_expires` (`status`, `expires_at`),
  KEY `idx_market_lots_seller_status` (`seller_id`, `status`),
  KEY `idx_market_lots_buyer` (`buyer_id`),
  KEY `idx_market_lots_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `market_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `action` VARCHAR(40) NOT NULL,
  `actor_id` INT NOT NULL DEFAULT 0,
  `lot_id` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `data_json` LONGTEXT NULL,
  `created_at` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_market_logs_lot` (`lot_id`),
  KEY `idx_market_logs_actor` (`actor_id`, `created_at`),
  KEY `idx_market_logs_action` (`action`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `market_return_storage` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `lot_id` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `object_type` VARCHAR(16) NOT NULL,
  `object_id` INT NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `payload_json` LONGTEXT NULL,
  `status` VARCHAR(16) NOT NULL DEFAULT 'pending',
  `created_at` INT NOT NULL DEFAULT 0,
  `resolved_at` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_market_return_user_status` (`user_id`, `status`),
  KEY `idx_market_return_lot` (`lot_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `site_settings` (`name`, `value`, `updated_by`, `updated_at`)
VALUES
  ('commission.enabled', '1', 0, UNIX_TIMESTAMP()),
  ('commission.commission_percent', '5', 0, UNIX_TIMESTAMP()),
  ('commission.min_price', '1', 0, UNIX_TIMESTAMP()),
  ('commission.max_price', '999999999', 0, UNIX_TIMESTAMP()),
  ('commission.min_hours', '24', 0, UNIX_TIMESTAMP()),
  ('commission.max_hours', '72', 0, UNIX_TIMESTAMP()),
  ('commission.max_active_lots', '20', 0, UNIX_TIMESTAMP()),
  ('commission.allow_pokemon', '1', 0, UNIX_TIMESTAMP()),
  ('commission.allow_eggs', '1', 0, UNIX_TIMESTAMP()),
  ('commission.allow_currency', '0', 0, UNIX_TIMESTAMP()),
  ('commission.hide_egg_species', '0', 0, UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);
