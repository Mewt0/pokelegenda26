CREATE TABLE IF NOT EXISTS `safe_storage_entries` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL DEFAULT 0,
  `source_type` VARCHAR(48) NOT NULL DEFAULT '',
  `source_id` VARCHAR(64) NOT NULL DEFAULT '',
  `object_type` VARCHAR(24) NOT NULL DEFAULT '',
  `object_id` INT NOT NULL DEFAULT 0,
  `quantity` INT NOT NULL DEFAULT 1,
  `payload_json` LONGTEXT NULL,
  `reason` VARCHAR(80) NOT NULL DEFAULT '',
  `error_message` VARCHAR(255) NOT NULL DEFAULT '',
  `status` VARCHAR(24) NOT NULL DEFAULT 'pending',
  `created_at` INT NOT NULL DEFAULT 0,
  `resolved_at` INT NOT NULL DEFAULT 0,
  `resolved_by` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_safe_storage_user_status` (`user_id`, `status`, `created_at`),
  KEY `idx_safe_storage_source` (`source_type`, `source_id`),
  KEY `idx_safe_storage_object` (`object_type`, `object_id`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `safe_operation_rollbacks` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `operation_key` VARCHAR(96) NOT NULL DEFAULT '',
  `operation_type` VARCHAR(48) NOT NULL DEFAULT '',
  `user_id` INT NOT NULL DEFAULT 0,
  `source_type` VARCHAR(48) NOT NULL DEFAULT '',
  `source_id` VARCHAR(64) NOT NULL DEFAULT '',
  `before_json` LONGTEXT NULL,
  `after_json` LONGTEXT NULL,
  `rollback_json` LONGTEXT NULL,
  `status` VARCHAR(24) NOT NULL DEFAULT 'open',
  `error_message` VARCHAR(255) NOT NULL DEFAULT '',
  `created_at` INT NOT NULL DEFAULT 0,
  `updated_at` INT NOT NULL DEFAULT 0,
  `applied_at` INT NOT NULL DEFAULT 0,
  `applied_by` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_safe_operation_key` (`operation_key`),
  KEY `idx_safe_operation_user_status` (`user_id`, `status`, `created_at`),
  KEY `idx_safe_operation_source` (`source_type`, `source_id`),
  KEY `idx_safe_operation_type` (`operation_type`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `safe_storage_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `entry_id` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `rollback_id` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `action` VARCHAR(48) NOT NULL DEFAULT '',
  `actor_id` INT NOT NULL DEFAULT 0,
  `data_json` LONGTEXT NULL,
  `created_at` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_safe_storage_logs_entry` (`entry_id`, `created_at`),
  KEY `idx_safe_storage_logs_rollback` (`rollback_id`, `created_at`),
  KEY `idx_safe_storage_logs_action` (`action`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `site_settings` (`name`, `value`, `updated_by`, `updated_at`)
VALUES
  ('safe_storage.enabled', '1', 0, UNIX_TIMESTAMP()),
  ('safe_storage.auto_restore', '0', 0, UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);
