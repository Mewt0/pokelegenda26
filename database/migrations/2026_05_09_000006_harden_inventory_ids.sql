-- Harden inventory ids from legacy dumps.
-- Safe to run repeatedly: fixes id=0 rows, then adds keys only when the table is clean.

SET @max_items_users_id := (SELECT COALESCE(MAX(id), 0) FROM items_users WHERE id <> 0);
UPDATE items_users
   SET id = (@max_items_users_id := @max_items_users_id + 1)
 WHERE id = 0
 ORDER BY user_id, item_id, count;

SET @items_users_duplicate_ids := (
    SELECT COUNT(*)
      FROM (
            SELECT id
              FROM items_users
             GROUP BY id
            HAVING COUNT(*) > 1
           ) duplicated_ids
);

SET @items_users_has_pk := (
    SELECT COUNT(*)
      FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME = 'items_users'
       AND CONSTRAINT_TYPE = 'PRIMARY KEY'
);

SET @sql := IF(
    @items_users_duplicate_ids = 0 AND @items_users_has_pk = 0,
    'ALTER TABLE items_users ADD PRIMARY KEY (id)',
    'SELECT "items_users primary key already present or duplicate ids remain"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @items_users_is_auto := (
    SELECT COUNT(*)
      FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME = 'items_users'
       AND COLUMN_NAME = 'id'
       AND EXTRA LIKE '%auto_increment%'
);

SET @next_items_users_auto := (SELECT COALESCE(MAX(id), 0) + 1 FROM items_users);
SET @sql := IF(
    @items_users_duplicate_ids = 0 AND @items_users_is_auto = 0,
    'ALTER TABLE items_users MODIFY id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT',
    'SELECT "items_users id already AUTO_INCREMENT or duplicate ids remain"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := CONCAT('ALTER TABLE items_users AUTO_INCREMENT = ', @next_items_users_auto);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @idx := (
    SELECT COUNT(*)
      FROM INFORMATION_SCHEMA.STATISTICS
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME = 'items_users'
       AND INDEX_NAME = 'idx_items_users_user_item_timer'
);
SET @sql := IF(
    @idx = 0,
    'ALTER TABLE items_users ADD KEY idx_items_users_user_item_timer (user_id, item_id, dattimer)',
    'SELECT "idx_items_users_user_item_timer exists"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @idx := (
    SELECT COUNT(*)
      FROM INFORMATION_SCHEMA.STATISTICS
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME = 'items_users'
       AND INDEX_NAME = 'idx_items_users_item'
);
SET @sql := IF(
    @idx = 0,
    'ALTER TABLE items_users ADD KEY idx_items_users_item (item_id)',
    'SELECT "idx_items_users_item exists"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @pok_user_duplicate_ids := (
    SELECT COUNT(*)
      FROM (
            SELECT id
              FROM pok_user
             GROUP BY id
            HAVING COUNT(*) > 1
           ) duplicated_ids
);

SET @pok_user_is_auto := (
    SELECT COUNT(*)
      FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME = 'pok_user'
       AND COLUMN_NAME = 'id'
       AND EXTRA LIKE '%auto_increment%'
);

SET @next_pok_user_auto := (SELECT COALESCE(MAX(id), 0) + 1 FROM pok_user);
SET @sql := IF(
    @pok_user_duplicate_ids = 0 AND @pok_user_is_auto = 0,
    'ALTER TABLE pok_user MODIFY id INT(11) NOT NULL AUTO_INCREMENT',
    'SELECT "pok_user id already AUTO_INCREMENT or duplicate ids remain"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := CONCAT('ALTER TABLE pok_user AUTO_INCREMENT = ', @next_pok_user_auto);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
