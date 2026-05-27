-- Trainer Card gym badges seed.
-- Keeps rewards public/gameplay data and does not grant badges to users by itself.

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

INSERT INTO gym_badges (badge_key, title, leader_name, location_id, icon_item_id, created_at, updated_at)
SELECT v.badge_key, v.title, v.leader_name, v.location_id, v.icon_item_id, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
FROM (
  SELECT 'boulder' badge_key, 'Каменный значок' title, 'Брок' leader_name, 0 location_id, 89 icon_item_id UNION ALL
  SELECT 'cascade', 'Каскадный значок', 'Мисти', 0, 77 UNION ALL
  SELECT 'thunder', 'Громовой значок', 'Лейтенант Сёрдж', 0, 87 UNION ALL
  SELECT 'rainbow', 'Радужный значок', 'Эрика', 0, 68 UNION ALL
  SELECT 'soul', 'Душевный значок', 'Кога', 0, 91 UNION ALL
  SELECT 'marsh', 'Болотный значок', 'Сабрина', 0, 90 UNION ALL
  SELECT 'volcano', 'Вулканический значок', 'Блейн', 0, 86 UNION ALL
  SELECT 'earth', 'Земляной значок', 'Джованни', 0, 376
) v
ON DUPLICATE KEY UPDATE
  title = VALUES(title),
  leader_name = VALUES(leader_name),
  location_id = VALUES(location_id),
  icon_item_id = VALUES(icon_item_id),
  updated_at = VALUES(updated_at);
