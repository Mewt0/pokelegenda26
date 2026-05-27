CREATE TABLE IF NOT EXISTS `data_integrity_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `run_key` VARCHAR(64) NOT NULL DEFAULT '',
  `check_key` VARCHAR(80) NOT NULL DEFAULT '',
  `severity` VARCHAR(16) NOT NULL DEFAULT 'warn',
  `status` VARCHAR(16) NOT NULL DEFAULT 'ok',
  `count_found` INT NOT NULL DEFAULT 0,
  `count_fixed` INT NOT NULL DEFAULT 0,
  `details_json` LONGTEXT NULL,
  `created_at` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_data_integrity_logs_run` (`run_key`, `created_at`),
  KEY `idx_data_integrity_logs_check` (`check_key`, `status`, `created_at`),
  KEY `idx_data_integrity_logs_severity` (`severity`, `status`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `site_settings` (`name`, `value`, `updated_by`, `updated_at`)
VALUES
  ('integrity.safe_fix_enabled', '1', 0, UNIX_TIMESTAMP()),
  ('integrity.last_run_at', '0', 0, UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);
