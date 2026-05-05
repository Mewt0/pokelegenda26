SET @db_name := DATABASE();

SET @has_training_stage := (
  SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @db_name
     AND TABLE_NAME = 'pok_user'
     AND COLUMN_NAME = 'training_stage'
);

SET @sql := IF(
  @has_training_stage = 0,
  'ALTER TABLE pok_user
     ADD COLUMN training_stage TINYINT NOT NULL DEFAULT 0 AFTER ability_key,
     ADD COLUMN training_stat VARCHAR(16) NOT NULL DEFAULT "" AFTER training_stage,
     ADD COLUMN training_named_effect VARCHAR(24) NOT NULL DEFAULT "" AFTER training_stat,
     ADD COLUMN training_tamed TINYINT NOT NULL DEFAULT 0 AFTER training_named_effect,
     ADD COLUMN training_updated_at INT NOT NULL DEFAULT 0 AFTER training_tamed',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

DELETE FROM items WHERE id IN (330, 678);

INSERT INTO items (id, cools, name, tittle, category, uses, dress, delet, torg, elementary, timesnapoke, times, battleuse, dopolnen)
VALUES
  (330, 0, 'Набор тренировки', 'Чемодан для тренировки монстра. Повышает стадию тренировки и случайно выбирает стат, кроме HP.', 0, 1, 0, 0, 1, 1, 0, '0', 0, 'training'),
  (678, 0, 'Набор ослабления', 'Скоба ослабления тренировки. Понижает стадию на 1, оставляет текущий стат и приручает монстра.', 0, 1, 0, 0, 1, 1, 0, '0', 0, 'training');
