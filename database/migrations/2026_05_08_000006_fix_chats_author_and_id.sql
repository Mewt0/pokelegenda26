SET @db_name := DATABASE();

SET @sql := IF(
    (
        SELECT COUNT(*)
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = @db_name
          AND TABLE_NAME = 'chats'
          AND COLUMN_NAME = 'author_id'
    ) = 0,
    'ALTER TABLE chats ADD COLUMN author_id INT(11) NOT NULL DEFAULT 0 AFTER id',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

UPDATE chats c
JOIN users u ON u.login = c.author
   SET c.author_id = u.id
 WHERE COALESCE(c.author_id, 0) = 0;

SET @sql := IF(
    (
        SELECT COUNT(*)
        FROM information_schema.TABLE_CONSTRAINTS
        WHERE TABLE_SCHEMA = @db_name
          AND TABLE_NAME = 'chats'
          AND CONSTRAINT_TYPE = 'PRIMARY KEY'
    ) = 0,
    'ALTER TABLE chats ADD PRIMARY KEY (id)',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    (
        SELECT EXTRA
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = @db_name
          AND TABLE_NAME = 'chats'
          AND COLUMN_NAME = 'id'
        LIMIT 1
    ) NOT LIKE '%auto_increment%',
    'ALTER TABLE chats MODIFY id INT(11) NOT NULL AUTO_INCREMENT',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @next_chat_id := (SELECT COALESCE(MAX(id), 0) + 1 FROM chats);
SET @sql := CONCAT('ALTER TABLE chats AUTO_INCREMENT = ', @next_chat_id);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    (
        SELECT COUNT(*)
        FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = @db_name
          AND TABLE_NAME = 'chats'
          AND INDEX_NAME = 'idx_chats_author_id'
    ) = 0,
    'ALTER TABLE chats ADD INDEX idx_chats_author_id (author_id)',
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
          AND TABLE_NAME = 'chats'
          AND INDEX_NAME = 'idx_chats_userto'
    ) = 0,
    'ALTER TABLE chats ADD INDEX idx_chats_userto (userto)',
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
          AND TABLE_NAME = 'chats'
          AND INDEX_NAME = 'idx_chats_room_private_id'
    ) = 0,
    'ALTER TABLE chats ADD INDEX idx_chats_room_private_id (room, private, id)',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
