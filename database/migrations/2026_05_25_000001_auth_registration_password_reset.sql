SET @db_name := DATABASE();

ALTER TABLE users
  MODIFY password VARCHAR(255) NOT NULL;

ALTER TABLE users
  MODIFY email VARCHAR(100) NULL DEFAULT NULL;

UPDATE users
   SET email = NULL
 WHERE email IN ('', 'none@mail.ru', 'none@example.test');

SET @has_email_verified := (
  SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @db_name
     AND TABLE_NAME = 'users'
     AND COLUMN_NAME = 'email_verified_at'
);
SET @sql := IF(@has_email_verified = 0, 'ALTER TABLE users ADD COLUMN email_verified_at INT NOT NULL DEFAULT 0 AFTER email', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx_users_email := (
  SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.STATISTICS
   WHERE TABLE_SCHEMA = @db_name
     AND TABLE_NAME = 'users'
     AND INDEX_NAME = 'idx_users_email'
);
SET @sql := IF(@idx_users_email = 0, 'ALTER TABLE users ADD INDEX idx_users_email (email)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

CREATE TABLE IF NOT EXISTS password_reset_tokens (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT NOT NULL,
  email VARCHAR(100) NOT NULL,
  token_hash CHAR(64) NOT NULL,
  expires_at INT NOT NULL,
  used_at INT NOT NULL DEFAULT 0,
  request_ip INT UNSIGNED NOT NULL DEFAULT 0,
  created_at INT NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_password_reset_token_hash (token_hash),
  KEY idx_password_reset_user (user_id, used_at, expires_at),
  KEY idx_password_reset_email (email, used_at, expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS email_verification_tokens (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT NOT NULL,
  email VARCHAR(100) NOT NULL,
  token_hash CHAR(64) NOT NULL,
  expires_at INT NOT NULL,
  used_at INT NOT NULL DEFAULT 0,
  request_ip INT UNSIGNED NOT NULL DEFAULT 0,
  created_at INT NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_email_verify_token_hash (token_hash),
  KEY idx_email_verify_user (user_id, used_at, expires_at),
  KEY idx_email_verify_email (email, used_at, expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
