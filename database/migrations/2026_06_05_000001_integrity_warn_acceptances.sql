CREATE TABLE IF NOT EXISTS `data_integrity_acceptances` (
  `check_key` VARCHAR(80) NOT NULL,
  `severity` VARCHAR(16) NOT NULL DEFAULT 'warn',
  `status` VARCHAR(24) NOT NULL DEFAULT 'accepted',
  `reason` TEXT NULL,
  `cleanup_policy` TEXT NULL,
  `accepted_until` INT NOT NULL DEFAULT 0,
  `accepted_by` VARCHAR(80) NOT NULL DEFAULT 'system',
  `created_at` INT NOT NULL DEFAULT 0,
  `updated_at` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`check_key`),
  KEY `idx_data_integrity_acceptances_status` (`status`, `accepted_until`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `data_integrity_acceptances`
  (`check_key`, `severity`, `status`, `reason`, `cleanup_policy`, `accepted_until`, `accepted_by`, `created_at`, `updated_at`)
VALUES
  (
    'items.orphan_user',
    'warn',
    'accepted',
    'Legacy inventory rows belong to deleted or missing users. They are not attached to a live session and must not be auto-deleted during beta hardening.',
    'Keep visible in integrity reports. After a verified backup/restore pass, archive or migrate these rows to Safe Storage with a dedicated owner-cleanup migration.',
    0,
    'codex/beta-foundation',
    UNIX_TIMESTAMP(),
    UNIX_TIMESTAMP()
  ),
  (
    'pokemon.invalid_owner',
    'warn',
    'accepted',
    'Legacy pokemon rows belong to deleted or missing users outside the commission reserve. Some rows may be old imported data, so automatic deletion is unsafe.',
    'Keep visible in integrity reports. Build an owner-cleanup report, then archive, reassign to System, or move to Safe Storage only after object-level review.',
    0,
    'codex/beta-foundation',
    UNIX_TIMESTAMP(),
    UNIX_TIMESTAMP()
  ),
  (
    'eggs.invalid_owner',
    'warn',
    'accepted',
    'A legacy egg row belongs to a deleted or missing user outside the commission reserve. It is not a P0/P1 runtime blocker but needs owner cleanup.',
    'Keep visible in integrity reports. Move to Safe Storage or archive after backup and object-level review.',
    0,
    'codex/beta-foundation',
    UNIX_TIMESTAMP(),
    UNIX_TIMESTAMP()
  ),
  (
    'battle.active_unfinished',
    'warn',
    'accepted',
    'Historical unfinished PvE battle rows remain in the legacy battles table. Current active-user consistency and duplicate-id checks are clean.',
    'Keep visible in integrity reports. Add a battle archival/expiration migration only after manual PvE/PvP regression confirms no active player relies on these rows.',
    0,
    'codex/beta-foundation',
    UNIX_TIMESTAMP(),
    UNIX_TIMESTAMP()
  )
ON DUPLICATE KEY UPDATE
  `severity` = VALUES(`severity`),
  `status` = VALUES(`status`),
  `reason` = VALUES(`reason`),
  `cleanup_policy` = VALUES(`cleanup_policy`),
  `accepted_until` = VALUES(`accepted_until`),
  `accepted_by` = VALUES(`accepted_by`),
  `updated_at` = UNIX_TIMESTAMP();
