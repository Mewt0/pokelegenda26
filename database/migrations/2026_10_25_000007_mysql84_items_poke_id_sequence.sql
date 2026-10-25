-- MySQL 8.4 compatibility: transaction-scoped id allocator for legacy items_poke.
--
-- The legacy dump declares `items_poke.id_itp INT NOT NULL` with no default, no
-- primary key and no AUTO_INCREMENT. Until migration 000005 converts that column
-- to AUTO_INCREMENT, application code must allocate the id itself.
--
-- Allocating `MAX(id_itp) + 1` while holding GET_LOCK is not transaction-safe:
-- the named lock is released before the caller commits, so a second transaction
-- cannot see the first uncommitted row and picks the same id. This table replaces
-- that fallback with an InnoDB row lock that is held until the surrounding
-- transaction commits or rolls back:
--
--   UPDATE `items_poke_id_sequence`
--      SET `next_id` = LAST_INSERT_ID(`next_id` + 1)
--    WHERE `id` = 1;
--   SELECT LAST_INSERT_ID();
--
-- Overlapping transactions serialise on the single row, including through the
-- commit boundary. A rollback rolls the counter back together with the matching
-- `items_poke` insert, so a reused id cannot collide with a row that was never
-- committed. `next_id` stores the last handed-out id; 0 means the next id is 1.
--
-- After migration 000005 the column is AUTO_INCREMENT and this table is unused
-- (kept for rollback/legacy mode). The migration is additive, idempotent and safe
-- on MySQL 5.5 and 8.4: it does not read or rewrite items_poke rows.
--
-- Deployment order: apply this migration (with 000004-000006) before deploying the
-- R2 code path to a database whose items_poke.id_itp is still not AUTO_INCREMENT.
-- The code fails with an explicit deployment instruction instead of silently
-- writing duplicate ids when the table is missing.
--
-- Rollback:
--   DROP TABLE `items_poke_id_sequence`;

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `items_poke_id_sequence` (
  `id` TINYINT NOT NULL PRIMARY KEY,
  `next_id` INT(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `items_poke_id_sequence` (`id`, `next_id`)
SELECT 1, COALESCE(MAX(`id_itp`), 0) FROM `items_poke`
ON DUPLICATE KEY UPDATE `next_id` = GREATEST(`next_id`, VALUES(`next_id`));
