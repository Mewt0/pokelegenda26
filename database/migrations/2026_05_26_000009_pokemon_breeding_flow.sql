-- Idempotent: new JSON breeding flow for Pokemon nursery and eggs.
CREATE TABLE IF NOT EXISTS pokemon_breeding_rules (
  base_id INT NOT NULL,
  egg_base_id INT NOT NULL DEFAULT 0,
  egg_group_1 VARCHAR(32) NOT NULL DEFAULT '',
  egg_group_2 VARCHAR(32) NOT NULL DEFAULT '',
  gender_mode VARCHAR(16) NOT NULL DEFAULT 'normal',
  breedable TINYINT NOT NULL DEFAULT 1,
  notes VARCHAR(255) NOT NULL DEFAULT '',
  updated_at INT NOT NULL DEFAULT 0,
  PRIMARY KEY (base_id),
  KEY idx_breeding_rules_group_1 (egg_group_1),
  KEY idx_breeding_rules_group_2 (egg_group_2),
  KEY idx_breeding_rules_breedable (breedable)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS pokemon_breeding_requests (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  requester_id INT NOT NULL,
  target_user_id INT NOT NULL,
  requester_pokemon_id INT NOT NULL,
  target_pokemon_id INT NOT NULL DEFAULT 0,
  status VARCHAR(16) NOT NULL DEFAULT 'pending',
  result_message TEXT NULL,
  egg_id INT NOT NULL DEFAULT 0,
  method VARCHAR(32) NOT NULL DEFAULT 'pair',
  created_at INT NOT NULL DEFAULT 0,
  expires_at INT NOT NULL DEFAULT 0,
  responded_at INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_breeding_requests_target (target_user_id, status, expires_at),
  KEY idx_breeding_requests_requester (requester_id, status, created_at),
  KEY idx_breeding_requests_egg (egg_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET @has_parent_one_id := (
  SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'eggs'
     AND COLUMN_NAME = 'parent_one_id'
);
SET @sql := IF(@has_parent_one_id = 0, 'ALTER TABLE eggs ADD COLUMN parent_one_id INT NOT NULL DEFAULT 0 AFTER spar', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_parent_two_id := (
  SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'eggs'
     AND COLUMN_NAME = 'parent_two_id'
);
SET @sql := IF(@has_parent_two_id = 0, 'ALTER TABLE eggs ADD COLUMN parent_two_id INT NOT NULL DEFAULT 0 AFTER parent_one_id', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_parent_one_user_id := (
  SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'eggs'
     AND COLUMN_NAME = 'parent_one_user_id'
);
SET @sql := IF(@has_parent_one_user_id = 0, 'ALTER TABLE eggs ADD COLUMN parent_one_user_id INT NOT NULL DEFAULT 0 AFTER parent_two_id', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_parent_two_user_id := (
  SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'eggs'
     AND COLUMN_NAME = 'parent_two_user_id'
);
SET @sql := IF(@has_parent_two_user_id = 0, 'ALTER TABLE eggs ADD COLUMN parent_two_user_id INT NOT NULL DEFAULT 0 AFTER parent_one_user_id', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_breeding_request_id := (
  SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'eggs'
     AND COLUMN_NAME = 'breeding_request_id'
);
SET @sql := IF(@has_breeding_request_id = 0, 'ALTER TABLE eggs ADD COLUMN breeding_request_id BIGINT UNSIGNED NOT NULL DEFAULT 0 AFTER parent_two_user_id', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_breeding_method := (
  SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'eggs'
     AND COLUMN_NAME = 'breeding_method'
);
SET @sql := IF(@has_breeding_method = 0, 'ALTER TABLE eggs ADD COLUMN breeding_method VARCHAR(32) NOT NULL DEFAULT '''' AFTER breeding_request_id', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx_eggs_breeding_request := (
  SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.STATISTICS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'eggs'
     AND INDEX_NAME = 'idx_eggs_breeding_request'
);
SET @sql := IF(@idx_eggs_breeding_request = 0, 'ALTER TABLE eggs ADD INDEX idx_eggs_breeding_request (breeding_request_id)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

INSERT INTO pokemon_breeding_rules
  (base_id, egg_base_id, egg_group_1, egg_group_2, gender_mode, breedable, notes, updated_at)
VALUES
  (1, 1, 'monster', 'grass', 'normal', 1, 'Bulbasaur line', UNIX_TIMESTAMP()),
  (2, 1, 'monster', 'grass', 'normal', 1, 'Bulbasaur line', UNIX_TIMESTAMP()),
  (3, 1, 'monster', 'grass', 'normal', 1, 'Bulbasaur line', UNIX_TIMESTAMP()),
  (4, 4, 'monster', 'dragon', 'normal', 1, 'Charmander line', UNIX_TIMESTAMP()),
  (5, 4, 'monster', 'dragon', 'normal', 1, 'Charmander line', UNIX_TIMESTAMP()),
  (6, 4, 'monster', 'dragon', 'normal', 1, 'Charmander line', UNIX_TIMESTAMP()),
  (16, 16, 'flying', '', 'normal', 1, 'Pidgey line', UNIX_TIMESTAMP()),
  (17, 16, 'flying', '', 'normal', 1, 'Pidgey line', UNIX_TIMESTAMP()),
  (18, 16, 'flying', '', 'normal', 1, 'Pidgey line', UNIX_TIMESTAMP()),
  (25, 172, 'field', 'fairy', 'normal', 1, 'Pikachu line eggs into Pichu', UNIX_TIMESTAMP()),
  (26, 172, 'field', 'fairy', 'normal', 1, 'Pikachu line eggs into Pichu', UNIX_TIMESTAMP()),
  (81, 81, 'mineral', '', 'genderless', 1, 'Genderless, Ditto required', UNIX_TIMESTAMP()),
  (82, 81, 'mineral', '', 'genderless', 1, 'Genderless, Ditto required', UNIX_TIMESTAMP()),
  (132, 132, 'ditto', '', 'genderless', 1, 'Ditto helper, cannot breed with Ditto', UNIX_TIMESTAMP()),
  (133, 133, 'field', '', 'normal', 1, 'Eevee line', UNIX_TIMESTAMP()),
  (137, 137, 'mineral', '', 'genderless', 1, 'Porygon line, Ditto required', UNIX_TIMESTAMP()),
  (374, 374, 'mineral', '', 'genderless', 1, 'Beldum line, Ditto required', UNIX_TIMESTAMP()),
  (375, 374, 'mineral', '', 'genderless', 1, 'Beldum line, Ditto required', UNIX_TIMESTAMP()),
  (376, 374, 'mineral', '', 'genderless', 1, 'Beldum line, Ditto required', UNIX_TIMESTAMP()),
  (382, 382, 'no_eggs', '', 'genderless', 0, 'Legendary: breeding disabled', UNIX_TIMESTAMP()),
  (383, 383, 'no_eggs', '', 'genderless', 0, 'Legendary: breeding disabled', UNIX_TIMESTAMP()),
  (384, 384, 'no_eggs', '', 'genderless', 0, 'Legendary: breeding disabled', UNIX_TIMESTAMP()),
  (448, 447, 'field', 'humanlike', 'normal', 1, 'Lucario line eggs into Riolu', UNIX_TIMESTAMP()),
  (483, 483, 'no_eggs', '', 'genderless', 0, 'Legendary: breeding disabled', UNIX_TIMESTAMP()),
  (484, 484, 'no_eggs', '', 'genderless', 0, 'Legendary: breeding disabled', UNIX_TIMESTAMP()),
  (643, 643, 'no_eggs', '', 'genderless', 0, 'Legendary: breeding disabled', UNIX_TIMESTAMP()),
  (644, 644, 'no_eggs', '', 'genderless', 0, 'Legendary: breeding disabled', UNIX_TIMESTAMP()),
  (716, 716, 'no_eggs', '', 'genderless', 0, 'Legendary: breeding disabled', UNIX_TIMESTAMP()),
  (717, 717, 'no_eggs', '', 'genderless', 0, 'Legendary: breeding disabled', UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
  egg_base_id = VALUES(egg_base_id),
  egg_group_1 = VALUES(egg_group_1),
  egg_group_2 = VALUES(egg_group_2),
  gender_mode = VALUES(gender_mode),
  breedable = VALUES(breedable),
  notes = VALUES(notes),
  updated_at = VALUES(updated_at);

INSERT INTO items (id, cools, name, tittle, category, uses, dress, delet, torg, elementary, timesnapoke, times, battleuse, dopolnen)
SELECT 90310, 0, 'Эссенция Дитто', 'Кастомный utility item для разведения: позволяет бесполому breedable-покемону получить яйцо так, будто второй родитель Ditto. Не работает с Ditto, No Eggs Discovered и легендарными.', 12, 1, 0, 1, 1, 0, 0, '0', 0, 'breeding:ditto_essence'
FROM (SELECT 1) seed
WHERE NOT EXISTS (SELECT 1 FROM items WHERE id = 90310);

UPDATE items
   SET name = 'Эссенция Дитто',
       tittle = 'Кастомный utility item для разведения: позволяет бесполому breedable-покемону получить яйцо так, будто второй родитель Ditto. Не работает с Ditto, No Eggs Discovered и легендарными.',
       category = 12,
       uses = 1,
       dress = 0,
       battleuse = 0,
       dopolnen = 'breeding:ditto_essence'
 WHERE id = 90310;

INSERT INTO item_gameplay_metadata
  (item_id, ru_name, en_alias, category_key, description, target_use_rule, battleuse_flag, equipuse_flag, effect_key, effect_status, compatibility_rule, compatibility_json, safe_to_equip, updated_at)
VALUES
  (90310, 'Эссенция Дитто', 'Ditto Essence', 'utility', 'Кастомный helper для breeding: genderless breedable-покемон получает яйцо как с Ditto. Не работает с Ditto, No Eggs Discovered и легендарными.', 'breeding_helper', 0, 0, 'breeding:ditto_essence', 'implemented', 'none', NULL, 0, UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
  ru_name = VALUES(ru_name),
  en_alias = VALUES(en_alias),
  category_key = VALUES(category_key),
  description = VALUES(description),
  target_use_rule = VALUES(target_use_rule),
  battleuse_flag = VALUES(battleuse_flag),
  equipuse_flag = VALUES(equipuse_flag),
  effect_key = VALUES(effect_key),
  effect_status = VALUES(effect_status),
  compatibility_rule = VALUES(compatibility_rule),
  compatibility_json = VALUES(compatibility_json),
  safe_to_equip = VALUES(safe_to_equip),
  updated_at = VALUES(updated_at);
