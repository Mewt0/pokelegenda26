-- Phase 5.17: SMTP/mail logs, system sender metadata and event notifications.

CREATE TABLE IF NOT EXISTS `mail_delivery_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `recipient` VARCHAR(190) NOT NULL DEFAULT '',
  `subject` VARCHAR(190) NOT NULL DEFAULT '',
  `transport` VARCHAR(24) NOT NULL DEFAULT '',
  `status` VARCHAR(24) NOT NULL DEFAULT '',
  `error_message` VARCHAR(255) NOT NULL DEFAULT '',
  `meta_json` LONGTEXT NULL,
  `created_at` INT NOT NULL DEFAULT 0,
  `sent_at` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_mail_delivery_status` (`status`, `created_at`),
  KEY `idx_mail_delivery_recipient` (`recipient`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `game_event_notification_receipts` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_id` INT NOT NULL DEFAULT 0,
  `user_id` INT NOT NULL DEFAULT 0,
  `notified_at` INT NOT NULL DEFAULT 0,
  `status` VARCHAR(24) NOT NULL DEFAULT 'sent',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_event_notification_user` (`event_id`, `user_id`),
  KEY `idx_event_notification_user` (`user_id`, `notified_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @has_sender_id := (
  SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'game_notifications'
     AND COLUMN_NAME = 'sender_id'
);
SET @sql := IF(@has_sender_id = 0,
  'ALTER TABLE `game_notifications` ADD COLUMN `sender_id` INT NOT NULL DEFAULT 0 AFTER `user_id`',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_source_type := (
  SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'game_notifications'
     AND COLUMN_NAME = 'source_type'
);
SET @sql := IF(@has_source_type = 0,
  'ALTER TABLE `game_notifications` ADD COLUMN `source_type` VARCHAR(64) NOT NULL DEFAULT ''system'' AFTER `source`',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_source_id := (
  SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'game_notifications'
     AND COLUMN_NAME = 'source_id'
);
SET @sql := IF(@has_source_id = 0,
  'ALTER TABLE `game_notifications` ADD COLUMN `source_id` VARCHAR(96) NOT NULL DEFAULT '''' AFTER `source_type`',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_email_status := (
  SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'game_notifications'
     AND COLUMN_NAME = 'email_status'
);
SET @sql := IF(@has_email_status = 0,
  'ALTER TABLE `game_notifications` ADD COLUMN `email_status` VARCHAR(24) NOT NULL DEFAULT ''not_requested'' AFTER `source_id`',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_email_sent_at := (
  SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'game_notifications'
     AND COLUMN_NAME = 'email_sent_at'
);
SET @sql := IF(@has_email_sent_at = 0,
  'ALTER TABLE `game_notifications` ADD COLUMN `email_sent_at` INT NOT NULL DEFAULT 0 AFTER `email_status`',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

INSERT INTO `site_settings` (`name`, `value`, `updated_by`, `updated_at`)
VALUES
  ('notifications.system_sender_enabled', '1', 0, UNIX_TIMESTAMP()),
  ('notifications.reward_email_enabled', '0', 0, UNIX_TIMESTAMP()),
  ('notifications.reward_mailbox_enabled', '0', 0, UNIX_TIMESTAMP()),
  ('notifications.event_enabled', '1', 0, UNIX_TIMESTAMP()),
  ('notifications.event_mailbox_enabled', '0', 0, UNIX_TIMESTAMP()),
  ('mail.delivery_log_enabled', '1', 0, UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

UPDATE `game_notifications`
   SET `sender_id` = COALESCE((SELECT CAST(`value` AS UNSIGNED) FROM `site_settings` WHERE `name` = 'system.account_id' LIMIT 1), 0),
       `source` = IF(`source` = '' OR `source` IS NULL, 'Система', `source`),
       `source_type` = IF(`source_type` = '' OR `source_type` IS NULL, 'system', `source_type`)
 WHERE `sender_id` = 0;
