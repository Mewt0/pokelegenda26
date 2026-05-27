CREATE TABLE IF NOT EXISTS `schema_migrations` (
  `migration` VARCHAR(190) NOT NULL,
  `checksum` CHAR(64) NOT NULL,
  `batch` INT NOT NULL DEFAULT 0,
  `applied_at` INT NOT NULL DEFAULT 0,
  `execution_ms` INT NOT NULL DEFAULT 0,
  `status` VARCHAR(24) NOT NULL DEFAULT 'applied',
  `note` VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`migration`),
  KEY `idx_schema_migrations_status` (`status`, `applied_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `migration_status` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `checked_at` INT NOT NULL DEFAULT 0,
  `total_migrations` INT NOT NULL DEFAULT 0,
  `applied_migrations` INT NOT NULL DEFAULT 0,
  `pending_migrations` INT NOT NULL DEFAULT 0,
  `dirty_migrations` INT NOT NULL DEFAULT 0,
  `failed_migrations` INT NOT NULL DEFAULT 0,
  `data_json` LONGTEXT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_migration_status_checked` (`checked_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
