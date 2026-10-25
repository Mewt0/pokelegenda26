-- Per-growth-group experience curve, version 2.
--
-- This migration only creates the one-time migration marker. It does NOT touch
-- pok_user rows: the data conversion is performed by
-- tools/pokemon_experience_migrate.php after a fresh verified backup and with
-- --confirm=APPLY_CURVE_V2. Keeping the marker in a separate table makes the
-- data step idempotent and auditable without adding a column to every row.
--
-- Rollout order (maintenance window):
--   1. tools/beta_backup.php + tools/beta_backup_verify.php --file=...
--   2. apply this migration (schema only)
--   3. deploy the group-aware PHP/UI code
--   4. tools/pokemon_experience_migrate.php --apply --backup=... --confirm=APPLY_CURVE_V2
--      (the tool creates the same table if the migration runner was not used)
--   5. run tools/pokemon_experience_migrate.php (dry run must report no debt)
--   6. run tools/pokemon_growth_curve_regression.php and the read smoke.

CREATE TABLE IF NOT EXISTS `pokemon_experience_curve_migrations` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `curve_version` SMALLINT UNSIGNED NOT NULL,
    `applied_at` INT UNSIGNED NOT NULL,
    `rows_count` INT UNSIGNED NOT NULL,
    `source_sha256` CHAR(64) NOT NULL,
    `details_json` LONGTEXT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_curve_version` (`curve_version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
