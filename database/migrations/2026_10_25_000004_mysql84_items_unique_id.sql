-- MySQL 8.4 compatibility: items.id must have a UNIQUE index for the existing
-- items_users.item_id foreign key.
--
-- MySQL 5.5 accepted a foreign key that referenced a non-unique index. MySQL 8.4
-- rejects it by default (`restrict_fk_on_non_standard_key=ON`) with error 6125
-- while importing a dump. This migration adds a unique index on items.id, so
-- future dumps are importable into 8.4 without weakening foreign-key validation.
--
-- Notes:
--   * The legacy non-unique `idx_items_id` is intentionally left in place: InnoDB
--     may be using it for the foreign key, and dropping a parent index used by a
--     foreign key is exactly the kind of avoidable risk this task must not add.
--     A unique index with a different name is enough for MySQL 8.4.
--   * Preflight: ids are copied into a temporary PRIMARY KEY table first. If
--     items.id contains duplicates the INSERT fails with "Duplicate entry" and the
--     migration is recorded as failed; no index is added and an operator must
--     resolve the duplicates. No rows are deleted or rewritten.
--
-- Rollback:
--   ALTER TABLE `items` DROP INDEX `uq_items_id`;
-- (Only safe while no MySQL 8.4 server depends on the unique key.)

SET NAMES utf8mb4;

DROP TEMPORARY TABLE IF EXISTS `mysql84_items_id_unique_guard`;
CREATE TEMPORARY TABLE `mysql84_items_id_unique_guard` (
  `id` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;
INSERT INTO `mysql84_items_id_unique_guard` (`id`) SELECT `id` FROM `items`;

SET @items_id_unique_index_exists := (
  SELECT COUNT(*)
    FROM (
      SELECT INDEX_NAME
        FROM information_schema.STATISTICS
       WHERE TABLE_SCHEMA = DATABASE()
         AND TABLE_NAME = 'items'
         AND NON_UNIQUE = 0
       GROUP BY INDEX_NAME
      HAVING COUNT(*) = 1 AND MAX(COLUMN_NAME) = 'id'
    ) single_column_unique_indexes
);

SET @sql := IF(
  @items_id_unique_index_exists = 0,
  'ALTER TABLE `items` ADD UNIQUE KEY `uq_items_id` (`id`)',
  'SELECT "items.id already has a unique index"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

DROP TEMPORARY TABLE IF EXISTS `mysql84_items_id_unique_guard`;
