-- Gym badges reward-flow metadata.
-- Idempotent: keeps existing badges and grants, adds only missing columns/indexes.

CREATE TABLE IF NOT EXISTS gym_badges (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  badge_key VARCHAR(64) NOT NULL,
  title VARCHAR(120) NOT NULL,
  leader_name VARCHAR(120) NOT NULL DEFAULT '',
  location_id INT NOT NULL DEFAULT 0,
  icon_item_id INT NOT NULL DEFAULT 0,
  created_at INT NOT NULL DEFAULT 0,
  updated_at INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY uq_gym_badges_key (badge_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS user_gym_badges (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT NOT NULL,
  badge_id BIGINT UNSIGNED NOT NULL,
  source_type VARCHAR(32) NOT NULL DEFAULT 'manual',
  source_id INT NOT NULL DEFAULT 0,
  awarded_by INT NOT NULL DEFAULT 0,
  awarded_at INT NOT NULL DEFAULT 0,
  created_at INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY uq_user_gym_badges (user_id, badge_id),
  KEY idx_user_gym_badges_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'user_gym_badges' AND column_name = 'reward_type') = 0,
  'ALTER TABLE user_gym_badges ADD COLUMN reward_type VARCHAR(32) NOT NULL DEFAULT ''gym_badge'' AFTER badge_id',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'user_gym_badges' AND column_name = 'issued_at') = 0,
  'ALTER TABLE user_gym_badges ADD COLUMN issued_at INT NOT NULL DEFAULT 0 AFTER awarded_at',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'user_gym_badges' AND column_name = 'source_battle_id') = 0,
  'ALTER TABLE user_gym_badges ADD COLUMN source_battle_id INT NOT NULL DEFAULT 0 AFTER source_id',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'user_gym_badges' AND column_name = 'source_quest_id') = 0,
  'ALTER TABLE user_gym_badges ADD COLUMN source_quest_id INT NOT NULL DEFAULT 0 AFTER source_battle_id',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'user_gym_badges' AND index_name = 'idx_user_gym_badges_reward_type') = 0,
  'ALTER TABLE user_gym_badges ADD KEY idx_user_gym_badges_reward_type (reward_type, issued_at)',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

UPDATE user_gym_badges
   SET reward_type = 'gym_badge'
 WHERE reward_type = '';

UPDATE user_gym_badges
   SET issued_at = awarded_at
 WHERE issued_at = 0 AND awarded_at > 0;

UPDATE user_gym_badges
   SET issued_at = created_at
 WHERE issued_at = 0 AND created_at > 0;

UPDATE user_gym_badges
   SET issued_at = UNIX_TIMESTAMP()
 WHERE issued_at = 0;

UPDATE user_gym_badges
   SET source_battle_id = source_id
 WHERE source_battle_id = 0
   AND source_id > 0
   AND source_type IN ('battle', 'gym_battle', 'pve', 'pvp');

UPDATE user_gym_badges
   SET source_quest_id = source_id
 WHERE source_quest_id = 0
   AND source_id > 0
   AND source_type = 'quest';
