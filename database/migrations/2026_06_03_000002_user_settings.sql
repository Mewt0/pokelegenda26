CREATE TABLE IF NOT EXISTS user_settings (
  user_id INT NOT NULL,
  show_online TINYINT(1) NOT NULL DEFAULT 1,
  allow_pm TINYINT(1) NOT NULL DEFAULT 1,
  allow_friend_requests TINYINT(1) NOT NULL DEFAULT 1,
  show_gifts TINYINT(1) NOT NULL DEFAULT 1,
  show_achievements TINYINT(1) NOT NULL DEFAULT 1,
  show_party_public TINYINT(1) NOT NULL DEFAULT 1,
  theme VARCHAR(24) NOT NULL DEFAULT 'auto',
  ui_size VARCHAR(24) NOT NULL DEFAULT 'normal',
  sounds TINYINT(1) NOT NULL DEFAULT 1,
  animations TINYINT(1) NOT NULL DEFAULT 1,
  notify_messages TINYINT(1) NOT NULL DEFAULT 1,
  notify_friends TINYINT(1) NOT NULL DEFAULT 1,
  notify_gifts TINYINT(1) NOT NULL DEFAULT 1,
  notify_clan TINYINT(1) NOT NULL DEFAULT 1,
  notify_system TINYINT(1) NOT NULL DEFAULT 1,
  profile_background VARCHAR(64) NOT NULL DEFAULT 'classic',
  profile_frame VARCHAR(64) NOT NULL DEFAULT 'classic',
  profile_title VARCHAR(64) NOT NULL DEFAULT '',
  updated_at INT NOT NULL DEFAULT 0,
  PRIMARY KEY (user_id),
  KEY idx_user_settings_privacy (show_online, allow_pm, allow_friend_requests),
  KEY idx_user_settings_profile (profile_background, profile_frame, profile_title)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO user_settings (user_id, show_party_public, updated_at)
SELECT ups.user_id, ups.show_party_public, UNIX_TIMESTAMP()
  FROM user_profile_settings ups
 WHERE EXISTS (
   SELECT 1
     FROM information_schema.tables
    WHERE table_schema = DATABASE()
      AND table_name = 'user_profile_settings'
 )
ON DUPLICATE KEY UPDATE
  show_party_public = VALUES(show_party_public),
  updated_at = VALUES(updated_at);
