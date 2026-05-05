CREATE TABLE IF NOT EXISTS pvp_requests (
  id INT NOT NULL AUTO_INCREMENT,
  from_user_id INT NOT NULL,
  to_user_id INT NOT NULL,
  status VARCHAR(16) NOT NULL DEFAULT 'pending',
  battle_id INT NOT NULL DEFAULT 0,
  created_at INT NOT NULL,
  updated_at INT NOT NULL,
  PRIMARY KEY (id),
  KEY idx_pvp_requests_pair (from_user_id, to_user_id, status),
  KEY idx_pvp_requests_incoming (to_user_id, status, id),
  KEY idx_pvp_requests_outgoing (from_user_id, status, id),
  KEY idx_pvp_requests_battle (battle_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
