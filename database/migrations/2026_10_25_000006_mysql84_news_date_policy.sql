-- MySQL 8.4 compatibility: remove the zero-date default from news.date.
--
-- `news.date DATE NOT NULL DEFAULT '0000-00-00'` is accepted by MySQL 5.5 but
-- rejected by MySQL 8.4 strict mode when a table is cloned:
--   CREATE TABLE ... LIKE news  ->  ERROR 1067 (42000): Invalid default value
-- The value is still imported from the legacy dump because the dump temporarily
-- sets a legacy SQL mode, so this only breaks schema cloning and long-term safety.
--
-- Policy chosen here: an unknown/publish date is represented by NULL, not by a
-- fabricated 1970-01-01 and not by a zero date. The admin news form already writes
-- a real date (`date('Y-m-d')`) on create/update, so no application behaviour is
-- changed. Existing DATE values are not rewritten by this migration.
--
-- If historical zero dates exist in some environment, this ALTER may fail under
-- NO_ZERO_DATE; that is intentional — resolving them (NULL vs. a real date) is a
-- data-owner decision and must not be guessed by a migration.
--
-- Rollback (only if a 5.5-only consumer requires the old definition):
--   ALTER TABLE `news` MODIFY `date` DATE NOT NULL DEFAULT '0000-00-00';
--   (requires a relaxed SQL mode)

SET NAMES utf8mb4;

SET @news_date_needs_change := (
  SELECT COUNT(*)
    FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'news'
     AND COLUMN_NAME = 'date'
     AND (IS_NULLABLE <> 'YES' OR COLUMN_DEFAULT IS NOT NULL)
);

SET @sql := IF(
  @news_date_needs_change > 0,
  'ALTER TABLE `news` MODIFY `date` DATE NULL DEFAULT NULL',
  'SELECT "news.date already allows NULL and has no zero default"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
