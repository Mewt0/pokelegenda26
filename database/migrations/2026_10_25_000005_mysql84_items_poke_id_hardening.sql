-- MySQL 8.4 compatibility: make items_poke.id_itp a real AUTO_INCREMENT key.
--
-- The legacy dump declares `id_itp INT NOT NULL` with no default, no primary key
-- and no AUTO_INCREMENT. Every INSERT that omits the column therefore failed on
-- MySQL 8.4 strict mode (error 1364); on permissive MySQL 5.5 the same INSERTs
-- silently wrote id_itp=0, which is why the live dump contains duplicate zero ids.
--
-- This migration:
--   1) renumbers existing id_itp=0 rows to max+1, max+2, ... (the column is a pure
--      surrogate: no source file, foreign key or archive references id_itp);
--   2) proves uniqueness by copying the ids into a temporary PRIMARY KEY table —
--      if non-zero duplicates exist the migration fails loudly and nothing is
--      altered;
--   3) adds a PRIMARY KEY and AUTO_INCREMENT, so both new code and remaining
--      legacy inserts (`insert('items_poke', ...)`) receive a valid id.
--
-- No rows are deleted. Existing non-zero ids keep their values.
--
-- Rollback:
--   ALTER TABLE `items_poke` MODIFY `id_itp` INT(11) NOT NULL;
--   ALTER TABLE `items_poke` DROP PRIMARY KEY;
-- (Only safe while no MySQL 8.4 server relies on generated ids.)

SET NAMES utf8mb4;

SET @max_items_poke_id := (SELECT COALESCE(MAX(`id_itp`), 0) FROM `items_poke` WHERE `id_itp` <> 0);
UPDATE `items_poke`
   SET `id_itp` = (@max_items_poke_id := @max_items_poke_id + 1)
 WHERE `id_itp` = 0
 ORDER BY `id_poke`, `id_items`;

DROP TEMPORARY TABLE IF EXISTS `mysql84_items_poke_id_guard`;
CREATE TEMPORARY TABLE `mysql84_items_poke_id_guard` (
  `id_itp` INT(11) NOT NULL,
  PRIMARY KEY (`id_itp`)
) ENGINE=InnoDB;
INSERT INTO `mysql84_items_poke_id_guard` (`id_itp`) SELECT `id_itp` FROM `items_poke`;

SET @items_poke_has_pk := (
  SELECT COUNT(*)
    FROM information_schema.TABLE_CONSTRAINTS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'items_poke'
     AND CONSTRAINT_TYPE = 'PRIMARY KEY'
);
SET @sql := IF(
  @items_poke_has_pk = 0,
  'ALTER TABLE `items_poke` ADD PRIMARY KEY (`id_itp`)',
  'SELECT "items_poke primary key already present"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @items_poke_is_auto := (
  SELECT COUNT(*)
    FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'items_poke'
     AND COLUMN_NAME = 'id_itp'
     AND EXTRA LIKE '%auto_increment%'
);
SET @next_items_poke_auto := (SELECT COALESCE(MAX(`id_itp`), 0) + 1 FROM `items_poke`);
SET @sql := IF(
  @items_poke_is_auto = 0,
  'ALTER TABLE `items_poke` MODIFY `id_itp` INT(11) NOT NULL AUTO_INCREMENT',
  'SELECT "items_poke id_itp is already AUTO_INCREMENT"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := CONCAT('ALTER TABLE `items_poke` AUTO_INCREMENT = ', @next_items_poke_auto);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

DROP TEMPORARY TABLE IF EXISTS `mysql84_items_poke_id_guard`;
