CREATE TABLE IF NOT EXISTS `background_job_runs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `job_name` VARCHAR(64) NOT NULL DEFAULT '',
  `status` VARCHAR(24) NOT NULL DEFAULT 'running',
  `dry_run` TINYINT NOT NULL DEFAULT 0,
  `started_at` INT NOT NULL DEFAULT 0,
  `finished_at` INT NOT NULL DEFAULT 0,
  `duration_ms` INT NOT NULL DEFAULT 0,
  `summary_json` LONGTEXT NULL,
  `error_message` VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_background_job_runs_name_time` (`job_name`, `started_at`),
  KEY `idx_background_job_runs_status` (`status`, `started_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `background_job_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `run_id` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `job_name` VARCHAR(64) NOT NULL DEFAULT '',
  `action` VARCHAR(64) NOT NULL DEFAULT '',
  `severity` VARCHAR(16) NOT NULL DEFAULT 'info',
  `entity_type` VARCHAR(32) NOT NULL DEFAULT '',
  `entity_id` VARCHAR(64) NOT NULL DEFAULT '',
  `data_json` LONGTEXT NULL,
  `created_at` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_background_job_logs_run` (`run_id`, `created_at`),
  KEY `idx_background_job_logs_job_action` (`job_name`, `action`, `created_at`),
  KEY `idx_background_job_logs_entity` (`entity_type`, `entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `site_settings` (`name`, `value`, `updated_by`, `updated_at`)
VALUES
  ('background_jobs.enabled', '1', 0, UNIX_TIMESTAMP()),
  ('background_jobs.market_expire_limit', '100', 0, UNIX_TIMESTAMP()),
  ('background_jobs.pvp_timeout_limit', '200', 0, UNIX_TIMESTAMP()),
  ('background_jobs.temporary_items_limit', '300', 0, UNIX_TIMESTAMP()),
  ('background_jobs.event_cleanup_limit', '100', 0, UNIX_TIMESTAMP()),
  ('background_jobs.stuck_pve_seconds', '86400', 0, UNIX_TIMESTAMP()),
  ('background_jobs.stuck_pvp_seconds', '14400', 0, UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);
