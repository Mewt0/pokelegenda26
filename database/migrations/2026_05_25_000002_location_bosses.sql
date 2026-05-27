CREATE TABLE IF NOT EXISTS location_bosses (
  id INT NOT NULL AUTO_INCREMENT,
  location_id INT NOT NULL DEFAULT 0,
  title VARCHAR(120) NOT NULL,
  description TEXT NOT NULL,
  event_key VARCHAR(64) NOT NULL DEFAULT '',
  enabled TINYINT NOT NULL DEFAULT 1,
  starts_at INT NOT NULL DEFAULT 0,
  ends_at INT NOT NULL DEFAULT 0,
  conditions_json TEXT NOT NULL,
  created_by INT NOT NULL DEFAULT 0,
  created_at INT NOT NULL DEFAULT 0,
  updated_at INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS location_boss_pokemon (
  id INT NOT NULL AUTO_INCREMENT,
  boss_id INT NOT NULL,
  slot_no TINYINT NOT NULL DEFAULT 1,
  base_pokemon_id INT NOT NULL DEFAULT 0,
  level INT NOT NULL DEFAULT 50,
  gender TINYINT NOT NULL DEFAULT 1,
  nature_id INT NOT NULL DEFAULT 1,
  shiny TINYINT NOT NULL DEFAULT 0,
  held_item_id INT NOT NULL DEFAULT 0,
  ability_key VARCHAR(64) NOT NULL DEFAULT '',
  stats_json TEXT NOT NULL,
  moves_json TEXT NOT NULL,
  item_effects_json TEXT NOT NULL,
  enabled TINYINT NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_location_boss_slot (boss_id, slot_no)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS location_boss_drops (
  id INT NOT NULL AUTO_INCREMENT,
  boss_id INT NOT NULL,
  item_id INT NOT NULL DEFAULT 0,
  chance_percent DECIMAL(8,4) NOT NULL DEFAULT 0,
  min_count INT NOT NULL DEFAULT 1,
  max_count INT NOT NULL DEFAULT 1,
  guaranteed TINYINT NOT NULL DEFAULT 0,
  rare TINYINT NOT NULL DEFAULT 0,
  enabled TINYINT NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  KEY idx_location_boss_drops_boss (boss_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS boss_battle_sessions (
  id INT NOT NULL AUTO_INCREMENT,
  boss_id INT NOT NULL,
  user_id INT NOT NULL,
  battle_id INT NOT NULL DEFAULT 0,
  status VARCHAR(20) NOT NULL DEFAULT 'active',
  active_player_pokemon_id INT NOT NULL DEFAULT 0,
  active_boss_slot TINYINT NOT NULL DEFAULT 1,
  boss_team_json MEDIUMTEXT NOT NULL,
  rewards_json TEXT NOT NULL,
  started_at INT NOT NULL DEFAULT 0,
  finished_at INT NOT NULL DEFAULT 0,
  updated_at INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_boss_battle_user_status (user_id, status),
  KEY idx_boss_battle_battle (battle_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @db_name := DATABASE();

SET @idx_location_bosses_location := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'location_bosses' AND INDEX_NAME = 'idx_location_bosses_location'
);
SET @sql := IF(@idx_location_bosses_location = 0, 'ALTER TABLE location_bosses ADD INDEX idx_location_bosses_location (location_id, enabled, starts_at, ends_at)', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @idx_location_boss_pokemon_boss := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'location_boss_pokemon' AND INDEX_NAME = 'idx_location_boss_pokemon_boss'
);
SET @sql := IF(@idx_location_boss_pokemon_boss = 0, 'ALTER TABLE location_boss_pokemon ADD INDEX idx_location_boss_pokemon_boss (boss_id, enabled, slot_no)', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
