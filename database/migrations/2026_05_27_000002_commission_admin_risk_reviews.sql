CREATE TABLE IF NOT EXISTS `market_deal_reviews` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `lot_id` BIGINT UNSIGNED NOT NULL,
  `status` VARCHAR(16) NOT NULL DEFAULT 'flagged',
  `risk_score` INT NOT NULL DEFAULT 0,
  `risk_flags_json` LONGTEXT NULL,
  `reviewed_by` INT NOT NULL DEFAULT 0,
  `reviewed_at` INT NOT NULL DEFAULT 0,
  `note` VARCHAR(255) NOT NULL DEFAULT '',
  `created_at` INT NOT NULL DEFAULT 0,
  `updated_at` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_market_deal_reviews_lot` (`lot_id`),
  KEY `idx_market_deal_reviews_status` (`status`, `reviewed_at`),
  KEY `idx_market_deal_reviews_reviewer` (`reviewed_by`, `reviewed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
