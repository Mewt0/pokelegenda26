SET @has_quest_pk := (
  SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'quest'
     AND CONSTRAINT_TYPE = 'PRIMARY KEY'
);
SET @sql := IF(@has_quest_pk = 0, 'ALTER TABLE quest ADD PRIMARY KEY (id)', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

ALTER TABLE quest MODIFY id BIGINT NOT NULL AUTO_INCREMENT;

SET @has_quest_user_idx := (
  SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.STATISTICS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'quest'
     AND INDEX_NAME = 'idx_quest_user_quest'
);
SET @sql := IF(@has_quest_user_idx = 0, 'ALTER TABLE quest ADD INDEX idx_quest_user_quest (user_id, quest_id)', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_quest_state_idx := (
  SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.STATISTICS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'quest'
     AND INDEX_NAME = 'idx_quest_state'
);
SET @sql := IF(@has_quest_state_idx = 0, 'ALTER TABLE quest ADD INDEX idx_quest_state (quest_id, gotov, process)', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
