-- Phase 6.21: in-game bug reporter with state, battle id and logs.

CREATE TABLE IF NOT EXISTS `bug_reports` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL DEFAULT 0,
  `user_login` VARCHAR(64) NOT NULL DEFAULT '',
  `title` VARCHAR(190) NOT NULL DEFAULT '',
  `description` TEXT NULL,
  `severity` VARCHAR(24) NOT NULL DEFAULT 'bug',
  `status` VARCHAR(24) NOT NULL DEFAULT 'open',
  `page_url` VARCHAR(255) NOT NULL DEFAULT '',
  `route` VARCHAR(160) NOT NULL DEFAULT '',
  `location_id` INT NOT NULL DEFAULT 0,
  `battle_id` BIGINT NOT NULL DEFAULT 0,
  `battle_type` VARCHAR(24) NOT NULL DEFAULT '',
  `user_state_json` LONGTEXT NULL,
  `game_state_json` LONGTEXT NULL,
  `battle_state_json` LONGTEXT NULL,
  `client_logs_json` LONGTEXT NULL,
  `server_logs_json` LONGTEXT NULL,
  `attachments_json` LONGTEXT NULL,
  `created_at` INT NOT NULL DEFAULT 0,
  `updated_at` INT NOT NULL DEFAULT 0,
  `resolved_at` INT NOT NULL DEFAULT 0,
  `resolved_by` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_bug_reports_status` (`status`, `created_at`),
  KEY `idx_bug_reports_user` (`user_id`, `created_at`),
  KEY `idx_bug_reports_battle` (`battle_id`, `created_at`),
  KEY `idx_bug_reports_severity` (`severity`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `bug_report_events` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `report_id` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `actor_id` INT NOT NULL DEFAULT 0,
  `action` VARCHAR(64) NOT NULL DEFAULT '',
  `data_json` LONGTEXT NULL,
  `created_at` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_bug_report_events_report` (`report_id`, `created_at`),
  KEY `idx_bug_report_events_actor` (`actor_id`, `created_at`),
  KEY `idx_bug_report_events_action` (`action`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `site_settings` (`name`, `value`, `updated_by`, `updated_at`)
VALUES
  ('bug_reporter.enabled', '1', 0, UNIX_TIMESTAMP()),
  ('bug_reporter.attach_server_logs', '1', 0, UNIX_TIMESTAMP()),
  ('bug_reporter.server_log_lines', '20', 0, UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);
