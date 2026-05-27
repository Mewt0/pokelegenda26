CREATE TABLE IF NOT EXISTS battle_history_archive (
  id INT NOT NULL AUTO_INCREMENT,
  battle_id INT NOT NULL,
  user_id INT NOT NULL,
  opponent_id INT NOT NULL DEFAULT 0,
  mode VARCHAR(16) NOT NULL DEFAULT 'pve',
  result VARCHAR(16) NOT NULL DEFAULT '',
  winner_id INT NOT NULL DEFAULT 0,
  rounds INT NOT NULL DEFAULT 0,
  started_at INT NOT NULL DEFAULT 0,
  finished_at INT NOT NULL DEFAULT 0,
  log_json MEDIUMTEXT NULL,
  snapshot_json MEDIUMTEXT NULL,
  created_at INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_battle_user (battle_id, user_id),
  KEY idx_user_finished (user_id, finished_at),
  KEY idx_battle (battle_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
