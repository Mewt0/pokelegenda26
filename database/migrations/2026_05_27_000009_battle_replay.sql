CREATE TABLE IF NOT EXISTS `battle_replays` (
  `battle_id` BIGINT UNSIGNED NOT NULL,
  `battle_type` VARCHAR(16) NOT NULL DEFAULT '',
  `user_1` INT NOT NULL DEFAULT 0,
  `user_2` INT NOT NULL DEFAULT 0,
  `winner_id` INT NOT NULL DEFAULT 0,
  `status` VARCHAR(24) NOT NULL DEFAULT 'active',
  `rounds` INT NOT NULL DEFAULT 0,
  `started_at` INT NOT NULL DEFAULT 0,
  `finished_at` INT NOT NULL DEFAULT 0,
  `meta_json` LONGTEXT NULL,
  `created_at` INT NOT NULL DEFAULT 0,
  `updated_at` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`battle_id`),
  KEY `idx_battle_replays_type_status` (`battle_type`, `status`),
  KEY `idx_battle_replays_users` (`user_1`, `user_2`),
  KEY `idx_battle_replays_updated` (`updated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `battle_replay_events` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `battle_id` BIGINT UNSIGNED NOT NULL,
  `event_key` VARCHAR(190) NULL,
  `round_no` INT NOT NULL DEFAULT 0,
  `event_type` VARCHAR(32) NOT NULL DEFAULT '',
  `actor_key` VARCHAR(64) NOT NULL DEFAULT '',
  `target_key` VARCHAR(64) NOT NULL DEFAULT '',
  `move_id` INT NOT NULL DEFAULT 0,
  `move_name` VARCHAR(190) NOT NULL DEFAULT '',
  `data_json` LONGTEXT NULL,
  `created_at` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_battle_replay_event_key` (`battle_id`, `event_key`),
  KEY `idx_battle_replay_events_battle_round` (`battle_id`, `round_no`, `id`),
  KEY `idx_battle_replay_events_type` (`event_type`),
  KEY `idx_battle_replay_events_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `site_settings` (`name`, `value`, `updated_by`, `updated_at`)
VALUES
  ('battle_replay.enabled', '1', 0, UNIX_TIMESTAMP()),
  ('battle_replay.max_events_per_battle', '1200', 0, UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `updated_at` = VALUES(`updated_at`);
