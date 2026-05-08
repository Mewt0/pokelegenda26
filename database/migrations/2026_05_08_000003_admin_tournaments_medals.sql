CREATE TABLE IF NOT EXISTS admin_tournaments (
  id INT NOT NULL AUTO_INCREMENT,
  legacy_id INT NOT NULL DEFAULT 0,
  title VARCHAR(160) NOT NULL,
  status VARCHAR(24) NOT NULL DEFAULT 'draft',
  starts_at INT NOT NULL DEFAULT 0,
  ends_at INT NOT NULL DEFAULT 0,
  entry_fee_item_id INT NOT NULL DEFAULT 1,
  entry_fee_amount INT NOT NULL DEFAULT 0,
  location_id INT NOT NULL DEFAULT 40,
  curator_user_id INT NOT NULL DEFAULT 0,
  min_level INT NOT NULL DEFAULT 1,
  max_level INT NOT NULL DEFAULT 100,
  max_participants INT NOT NULL DEFAULT 0,
  rules TEXT NOT NULL,
  reward_note TEXT NOT NULL,
  created_by INT NOT NULL DEFAULT 0,
  updated_by INT NOT NULL DEFAULT 0,
  created_at INT NOT NULL DEFAULT 0,
  updated_at INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_admin_tournaments_status (status, starts_at),
  KEY idx_admin_tournaments_curator (curator_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_tournament_participants (
  id INT NOT NULL AUTO_INCREMENT,
  tournament_id INT NOT NULL,
  user_id INT NOT NULL,
  pokemon_id INT NOT NULL DEFAULT 0,
  status VARCHAR(24) NOT NULL DEFAULT 'registered',
  score INT NOT NULL DEFAULT 0,
  place_num INT NOT NULL DEFAULT 0,
  joined_at INT NOT NULL DEFAULT 0,
  updated_at INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY uq_admin_tournament_user (tournament_id, user_id),
  KEY idx_admin_tournament_participants_user (user_id),
  KEY idx_admin_tournament_participants_status (tournament_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_medals (
  id INT NOT NULL AUTO_INCREMENT,
  title VARCHAR(160) NOT NULL,
  description TEXT NOT NULL,
  icon_file VARCHAR(160) NOT NULL DEFAULT '',
  medal_type VARCHAR(32) NOT NULL DEFAULT 'tournament',
  tournament_id INT NOT NULL DEFAULT 0,
  sort_order INT NOT NULL DEFAULT 0,
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  created_by INT NOT NULL DEFAULT 0,
  updated_by INT NOT NULL DEFAULT 0,
  created_at INT NOT NULL DEFAULT 0,
  updated_at INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_admin_medals_enabled_sort (enabled, sort_order),
  KEY idx_admin_medals_tournament (tournament_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_user_medals (
  id INT NOT NULL AUTO_INCREMENT,
  medal_id INT NOT NULL,
  user_id INT NOT NULL,
  tournament_id INT NOT NULL DEFAULT 0,
  comment VARCHAR(255) NOT NULL DEFAULT '',
  awarded_by INT NOT NULL DEFAULT 0,
  awarded_at INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY uq_admin_user_medal (medal_id, user_id, tournament_id),
  KEY idx_admin_user_medals_user (user_id),
  KEY idx_admin_user_medals_tournament (tournament_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
