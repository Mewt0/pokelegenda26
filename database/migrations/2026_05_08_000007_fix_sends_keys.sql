SET @db_name := DATABASE();

SET @sql := IF(
    (
        SELECT COUNT(*)
        FROM information_schema.TABLE_CONSTRAINTS
        WHERE TABLE_SCHEMA = @db_name
          AND TABLE_NAME = 'sends'
          AND CONSTRAINT_TYPE = 'PRIMARY KEY'
    ) = 0,
    'ALTER TABLE sends ADD PRIMARY KEY (id)',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

ALTER TABLE sends MODIFY id INT(11) NOT NULL AUTO_INCREMENT;
SET @next_send_id := (SELECT COALESCE(MAX(id), 0) + 1 FROM sends);
SET @sql := CONCAT('ALTER TABLE sends AUTO_INCREMENT = ', @next_send_id);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    (
        SELECT COUNT(*)
        FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = @db_name
          AND TABLE_NAME = 'sends'
          AND INDEX_NAME = 'idx_sends_users_active_id'
    ) = 0,
    'ALTER TABLE sends ADD INDEX idx_sends_users_active_id (users, active, id)',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    (
        SELECT COUNT(*)
        FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = @db_name
          AND TABLE_NAME = 'sends'
          AND INDEX_NAME = 'idx_sends_inputusers_id'
    ) = 0,
    'ALTER TABLE sends ADD INDEX idx_sends_inputusers_id (inputusers, id)',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
