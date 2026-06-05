-- Data-driven gym badge grants after real PvE gym-leader battles.

CREATE TABLE IF NOT EXISTS gym_badge_battle_rules (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  badge_key VARCHAR(64) NOT NULL,
  location_id INT NOT NULL DEFAULT 0,
  enemy_base_id INT NOT NULL DEFAULT 0,
  enemy_pokemon_id INT NOT NULL DEFAULT 0,
  enemy_name_like VARCHAR(120) NOT NULL DEFAULT '',
  min_level INT NOT NULL DEFAULT 0,
  source_note VARCHAR(160) NOT NULL DEFAULT '',
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  created_at INT NOT NULL DEFAULT 0,
  updated_at INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_gym_badge_rules_enabled (enabled, location_id, enemy_base_id),
  KEY idx_gym_badge_rules_badge (badge_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
