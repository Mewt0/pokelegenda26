CREATE TABLE IF NOT EXISTS user_profile_settings (
  user_id INT NOT NULL,
  show_party_public TINYINT(1) NOT NULL DEFAULT 1,
  updated_at INT NOT NULL DEFAULT 0,
  PRIMARY KEY (user_id),
  KEY idx_user_profile_settings_party (show_party_public)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
