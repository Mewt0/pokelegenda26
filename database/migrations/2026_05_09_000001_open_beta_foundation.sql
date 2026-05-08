SET @db_name := DATABASE();

SET @has_pvp_expires := (
  SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @db_name
     AND TABLE_NAME = 'pvp_requests'
     AND COLUMN_NAME = 'expires_at'
);
SET @sql := IF(@has_pvp_expires = 0, 'ALTER TABLE pvp_requests ADD COLUMN expires_at INT NOT NULL DEFAULT 0 AFTER battle_id', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_pvp_responded := (
  SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @db_name
     AND TABLE_NAME = 'pvp_requests'
     AND COLUMN_NAME = 'responded_at'
);
SET @sql := IF(@has_pvp_responded = 0, 'ALTER TABLE pvp_requests ADD COLUMN responded_at INT NOT NULL DEFAULT 0 AFTER expires_at', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx_pvp_expiry := (
  SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.STATISTICS
   WHERE TABLE_SCHEMA = @db_name
     AND TABLE_NAME = 'pvp_requests'
     AND INDEX_NAME = 'idx_pvp_requests_expiry'
);
SET @sql := IF(@idx_pvp_expiry = 0, 'ALTER TABLE pvp_requests ADD INDEX idx_pvp_requests_expiry (status, expires_at)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

UPDATE pvp_requests
   SET expires_at = created_at + 120
 WHERE status = 'pending'
   AND expires_at = 0;

CREATE TABLE IF NOT EXISTS mail_message_state (
  id INT NOT NULL AUTO_INCREMENT,
  message_id INT NOT NULL,
  user_id INT NOT NULL,
  folder VARCHAR(24) NOT NULL DEFAULT 'inbox',
  read_at INT NOT NULL DEFAULT 0,
  archived_at INT NOT NULL DEFAULT 0,
  deleted_at INT NOT NULL DEFAULT 0,
  created_at INT NOT NULL DEFAULT 0,
  updated_at INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY uq_mail_message_user_folder (message_id, user_id, folder),
  KEY idx_mail_state_user_folder (user_id, folder, deleted_at, archived_at),
  KEY idx_mail_state_message (message_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
