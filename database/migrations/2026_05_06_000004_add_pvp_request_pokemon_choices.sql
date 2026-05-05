SET @db_name := DATABASE();

SET @has_from_pokemon := (
  SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @db_name
     AND TABLE_NAME = 'pvp_requests'
     AND COLUMN_NAME = 'from_pokemon_id'
);

SET @sql := IF(
  @has_from_pokemon = 0,
  'ALTER TABLE pvp_requests ADD COLUMN from_pokemon_id INT NOT NULL DEFAULT 0 AFTER to_user_id',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_to_pokemon := (
  SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @db_name
     AND TABLE_NAME = 'pvp_requests'
     AND COLUMN_NAME = 'to_pokemon_id'
);

SET @sql := IF(
  @has_to_pokemon = 0,
  'ALTER TABLE pvp_requests ADD COLUMN to_pokemon_id INT NOT NULL DEFAULT 0 AFTER from_pokemon_id',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
