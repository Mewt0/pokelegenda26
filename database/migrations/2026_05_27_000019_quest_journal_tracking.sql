CREATE TABLE IF NOT EXISTS user_quest_tracking (
  user_id INT NOT NULL,
  quest_id INT NOT NULL DEFAULT 0,
  updated_at INT NOT NULL DEFAULT 0,
  PRIMARY KEY (user_id),
  KEY idx_user_quest_tracking_quest (quest_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
