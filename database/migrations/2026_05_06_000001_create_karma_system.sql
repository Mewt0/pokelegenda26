SET @has_karma_score := (
  SELECT COUNT(*)
    FROM information_schema.columns
   WHERE table_schema = DATABASE()
     AND table_name = 'users'
     AND column_name = 'karma_score'
);
SET @karma_sql := IF(
  @has_karma_score = 0,
  'ALTER TABLE users ADD COLUMN karma_score INT NOT NULL DEFAULT 0 AFTER rang_c',
  'SELECT 1'
);
PREPARE karma_stmt FROM @karma_sql;
EXECUTE karma_stmt;
DEALLOCATE PREPARE karma_stmt;

CREATE TABLE IF NOT EXISTS karma_events (
  id INT NOT NULL AUTO_INCREMENT,
  user_id INT NOT NULL,
  target_user_id INT NOT NULL DEFAULT 0,
  battle_id INT NOT NULL DEFAULT 0,
  delta INT NOT NULL,
  score_after INT NOT NULL,
  reason VARCHAR(64) NOT NULL,
  created_at INT NOT NULL,
  PRIMARY KEY (id),
  KEY idx_karma_events_user (user_id, id),
  KEY idx_karma_events_battle (battle_id),
  KEY idx_karma_events_target (target_user_id, id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

UPDATE users
   SET karma_score = -100
 WHERE groups IN (7, 10)
   AND karma_score = 0;

INSERT INTO items (id, cools, name, tittle, category, uses, dress, delet, torg, elementary, timesnapoke, times, battleuse, dopolnen)
SELECT 90001, 0, 'Ордер Команды R I', 'Позволяет напасть в опасной локации на тренера выше ранга Начинающий, если его популярность не ниже 30% от вашей.', 0, 1, 0, 1, 1, 0, 0, '0', 0, 'karma_warrant_1'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM items WHERE id = 90001);

INSERT INTO items (id, cools, name, tittle, category, uses, dress, delet, torg, elementary, timesnapoke, times, battleuse, dopolnen)
SELECT 90002, 0, 'Ордер Команды R II', 'Позволяет напасть в опасной локации на любого тренера выше ранга Начинающий.', 0, 1, 0, 1, 1, 0, 0, '0', 0, 'karma_warrant_2'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM items WHERE id = 90002);

INSERT INTO items (id, cools, name, tittle, category, uses, dress, delet, torg, elementary, timesnapoke, times, battleuse, dopolnen)
SELECT 90003, 0, 'Ордер протекции', 'Позволяет защитнику временно защищать локацию от нападений. Эффект будет подключен отдельным блоком.', 0, 1, 0, 1, 1, 0, 0, '0', 0, 'karma_protection_order'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM items WHERE id = 90003);
