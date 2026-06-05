CREATE TABLE IF NOT EXISTS `reward_transactions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `operation_key` VARCHAR(128) NOT NULL DEFAULT '',
  `user_id` INT NOT NULL DEFAULT 0,
  `source_type` VARCHAR(64) NOT NULL DEFAULT '',
  `source_id` VARCHAR(96) NOT NULL DEFAULT '',
  `title` VARCHAR(160) NOT NULL DEFAULT '',
  `status` VARCHAR(24) NOT NULL DEFAULT 'pending',
  `payload_json` LONGTEXT NULL,
  `result_json` LONGTEXT NULL,
  `error_message` VARCHAR(255) NOT NULL DEFAULT '',
  `created_at` INT NOT NULL DEFAULT 0,
  `updated_at` INT NOT NULL DEFAULT 0,
  `completed_at` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_reward_operation_key` (`operation_key`),
  KEY `idx_reward_transactions_user` (`user_id`, `status`, `created_at`),
  KEY `idx_reward_transactions_source` (`source_type`, `source_id`),
  KEY `idx_reward_transactions_status` (`status`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `reward_transaction_entries` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `transaction_id` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `reward_type` VARCHAR(32) NOT NULL DEFAULT '',
  `object_id` INT NOT NULL DEFAULT 0,
  `quantity` INT NOT NULL DEFAULT 0,
  `title` VARCHAR(160) NOT NULL DEFAULT '',
  `status` VARCHAR(24) NOT NULL DEFAULT 'pending',
  `data_json` LONGTEXT NULL,
  `created_at` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_reward_entries_transaction` (`transaction_id`, `reward_type`),
  KEY `idx_reward_entries_object` (`reward_type`, `object_id`, `created_at`),
  KEY `idx_reward_entries_status` (`status`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `site_settings` (`name`, `value`, `updated_by`, `updated_at`)
VALUES
  ('reward_pipeline.enabled', '1', 0, UNIX_TIMESTAMP()),
  ('reward_pipeline.notifications', '1', 0, UNIX_TIMESTAMP()),
  ('reward_pipeline.safe_storage_on_failure', '1', 0, UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);
