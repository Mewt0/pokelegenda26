-- Economy Guard: automatic open-test economy anomaly detection.

CREATE TABLE IF NOT EXISTS `economy_guard_alerts` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `alert_key` VARCHAR(190) NOT NULL,
  `alert_type` VARCHAR(40) NOT NULL DEFAULT '',
  `severity` VARCHAR(16) NOT NULL DEFAULT 'warn',
  `status` VARCHAR(16) NOT NULL DEFAULT 'open',
  `user_id` INT NOT NULL DEFAULT 0,
  `related_user_id` INT NOT NULL DEFAULT 0,
  `entity_type` VARCHAR(32) NOT NULL DEFAULT '',
  `entity_id` VARCHAR(64) NOT NULL DEFAULT '',
  `amount` BIGINT NOT NULL DEFAULT 0,
  `score` INT NOT NULL DEFAULT 0,
  `title` VARCHAR(190) NOT NULL DEFAULT '',
  `details_json` LONGTEXT NULL,
  `first_seen_at` INT NOT NULL DEFAULT 0,
  `last_seen_at` INT NOT NULL DEFAULT 0,
  `reviewed_by` INT NOT NULL DEFAULT 0,
  `reviewed_at` INT NOT NULL DEFAULT 0,
  `note` VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_economy_guard_alert_key` (`alert_key`),
  KEY `idx_economy_guard_status` (`status`, `severity`, `last_seen_at`),
  KEY `idx_economy_guard_user` (`user_id`, `last_seen_at`),
  KEY `idx_economy_guard_type` (`alert_type`, `last_seen_at`),
  KEY `idx_economy_guard_entity` (`entity_type`, `entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `site_settings` (`name`, `value`, `updated_by`, `updated_at`)
VALUES
  ('economy_guard.enabled', '1', 0, UNIX_TIMESTAMP()),
  ('economy_guard.scan_window_seconds', '86400', 0, UNIX_TIMESTAMP()),
  ('economy_guard.massive_money_gain_threshold', '10000000', 0, UNIX_TIMESTAMP()),
  ('economy_guard.transfer_pair_threshold', '10000000', 0, UNIX_TIMESTAMP()),
  ('economy_guard.transfer_pair_count_threshold', '5', 0, UNIX_TIMESTAMP()),
  ('economy_guard.fake_price_multiplier', '10', 0, UNIX_TIMESTAMP()),
  ('economy_guard.fake_price_min_sales', '3', 0, UNIX_TIMESTAMP()),
  ('economy_guard.fake_price_min_unit', '100000', 0, UNIX_TIMESTAMP()),
  ('economy_guard.coin_balance_threshold', '100000000', 0, UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);
