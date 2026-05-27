-- Player-facing tournament flow: registration deadline, arena state,
-- reward claims and audit logs. Kept additive so existing admin rows survive.

SET @has_registration_deadline_at := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'admin_tournaments'
     AND COLUMN_NAME = 'registration_deadline_at'
);
SET @sql := IF(@has_registration_deadline_at = 0,
  'ALTER TABLE admin_tournaments ADD COLUMN registration_deadline_at INT NOT NULL DEFAULT 0 AFTER ends_at',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_arena_exit_location_id := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'admin_tournaments'
     AND COLUMN_NAME = 'arena_exit_location_id'
);
SET @sql := IF(@has_arena_exit_location_id = 0,
  'ALTER TABLE admin_tournaments ADD COLUMN arena_exit_location_id INT NOT NULL DEFAULT 0 AFTER location_id',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_reward_json := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'admin_tournaments'
     AND COLUMN_NAME = 'reward_json'
);
SET @sql := IF(@has_reward_json = 0,
  'ALTER TABLE admin_tournaments ADD COLUMN reward_json LONGTEXT NULL AFTER reward_note',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @idx_tournament_deadline := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'admin_tournaments'
     AND INDEX_NAME = 'idx_admin_tournaments_registration_deadline'
);
SET @sql := IF(@idx_tournament_deadline = 0,
  'ALTER TABLE admin_tournaments ADD INDEX idx_admin_tournaments_registration_deadline (status, registration_deadline_at)',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_fee_paid_at := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'admin_tournament_participants'
     AND COLUMN_NAME = 'fee_paid_at'
);
SET @sql := IF(@has_fee_paid_at = 0,
  'ALTER TABLE admin_tournament_participants ADD COLUMN fee_paid_at INT NOT NULL DEFAULT 0 AFTER joined_at',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_fee_refunded_at := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'admin_tournament_participants'
     AND COLUMN_NAME = 'fee_refunded_at'
);
SET @sql := IF(@has_fee_refunded_at = 0,
  'ALTER TABLE admin_tournament_participants ADD COLUMN fee_refunded_at INT NOT NULL DEFAULT 0 AFTER fee_paid_at',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_checked_in_at := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'admin_tournament_participants'
     AND COLUMN_NAME = 'checked_in_at'
);
SET @sql := IF(@has_checked_in_at = 0,
  'ALTER TABLE admin_tournament_participants ADD COLUMN checked_in_at INT NOT NULL DEFAULT 0 AFTER fee_refunded_at',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_left_at := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'admin_tournament_participants'
     AND COLUMN_NAME = 'left_at'
);
SET @sql := IF(@has_left_at = 0,
  'ALTER TABLE admin_tournament_participants ADD COLUMN left_at INT NOT NULL DEFAULT 0 AFTER checked_in_at',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_return_location_id := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'admin_tournament_participants'
     AND COLUMN_NAME = 'return_location_id'
);
SET @sql := IF(@has_return_location_id = 0,
  'ALTER TABLE admin_tournament_participants ADD COLUMN return_location_id INT NOT NULL DEFAULT 0 AFTER left_at',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_reward_claimed_at := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'admin_tournament_participants'
     AND COLUMN_NAME = 'reward_claimed_at'
);
SET @sql := IF(@has_reward_claimed_at = 0,
  'ALTER TABLE admin_tournament_participants ADD COLUMN reward_claimed_at INT NOT NULL DEFAULT 0 AFTER return_location_id',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

CREATE TABLE IF NOT EXISTS `admin_tournament_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tournament_id` INT NOT NULL DEFAULT 0,
  `user_id` INT NOT NULL DEFAULT 0,
  `action` VARCHAR(48) NOT NULL,
  `data_json` LONGTEXT NULL,
  `created_at` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_admin_tournament_logs_tournament` (`tournament_id`, `created_at`),
  KEY `idx_admin_tournament_logs_user` (`user_id`, `created_at`),
  KEY `idx_admin_tournament_logs_action` (`action`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
