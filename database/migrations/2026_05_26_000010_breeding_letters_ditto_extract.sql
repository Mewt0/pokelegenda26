-- Idempotent: align breeding with compatibility letters, held Ditto Extract, and long egg incubation.
SET @has_breeding_letter := (
  SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'pokemon_breeding_rules'
     AND COLUMN_NAME = 'compatibility_letter'
);
SET @sql := IF(@has_breeding_letter = 0, 'ALTER TABLE pokemon_breeding_rules ADD COLUMN compatibility_letter CHAR(1) NOT NULL DEFAULT '''' AFTER egg_group_2', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx_breeding_letter := (
  SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.STATISTICS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'pokemon_breeding_rules'
     AND INDEX_NAME = 'idx_breeding_rules_letter'
);
SET @sql := IF(@idx_breeding_letter = 0, 'ALTER TABLE pokemon_breeding_rules ADD INDEX idx_breeding_rules_letter (compatibility_letter)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

INSERT INTO pokemon_breeding_rules
  (base_id, egg_base_id, egg_group_1, egg_group_2, compatibility_letter, gender_mode, breedable, notes, updated_at)
VALUES
  (1, 1, 'monster', 'grass', 'A', 'normal', 1, 'Bulbasaur line: compatibility A', UNIX_TIMESTAMP()),
  (2, 1, 'monster', 'grass', 'A', 'normal', 1, 'Bulbasaur line: compatibility A', UNIX_TIMESTAMP()),
  (3, 1, 'monster', 'grass', 'A', 'normal', 1, 'Bulbasaur line: compatibility A', UNIX_TIMESTAMP()),
  (4, 4, 'monster', 'dragon', 'B', 'normal', 1, 'Charmander line: compatibility B', UNIX_TIMESTAMP()),
  (5, 4, 'monster', 'dragon', 'B', 'normal', 1, 'Charmander line: compatibility B', UNIX_TIMESTAMP()),
  (6, 4, 'monster', 'dragon', 'B', 'normal', 1, 'Charmander line: compatibility B', UNIX_TIMESTAMP()),
  (16, 16, 'flying', '', 'C', 'normal', 1, 'Pidgey line: compatibility C', UNIX_TIMESTAMP()),
  (17, 16, 'flying', '', 'C', 'normal', 1, 'Pidgey line: compatibility C', UNIX_TIMESTAMP()),
  (18, 16, 'flying', '', 'C', 'normal', 1, 'Pidgey line: compatibility C', UNIX_TIMESTAMP()),
  (25, 172, 'field', 'fairy', 'D', 'normal', 1, 'Pikachu line eggs into Pichu: compatibility D', UNIX_TIMESTAMP()),
  (26, 172, 'field', 'fairy', 'D', 'normal', 1, 'Pikachu line eggs into Pichu: compatibility D', UNIX_TIMESTAMP()),
  (81, 81, 'mineral', '', 'M', 'genderless', 1, 'Genderless mineral line: Ditto or held Ditto Extract pair', UNIX_TIMESTAMP()),
  (82, 81, 'mineral', '', 'M', 'genderless', 1, 'Genderless mineral line: Ditto or held Ditto Extract pair', UNIX_TIMESTAMP()),
  (120, 120, 'water3', '', 'W', 'genderless', 1, 'Staryu line: genderless, Ditto or held Ditto Extract pair', UNIX_TIMESTAMP()),
  (121, 120, 'water3', '', 'W', 'genderless', 1, 'Staryu line: genderless, Ditto or held Ditto Extract pair', UNIX_TIMESTAMP()),
  (132, 132, 'ditto', '', 'X', 'genderless', 1, 'Ditto helper, cannot breed with Ditto', UNIX_TIMESTAMP()),
  (133, 133, 'field', '', 'D', 'normal', 1, 'Eevee line: compatibility D', UNIX_TIMESTAMP()),
  (137, 137, 'mineral', '', 'M', 'genderless', 1, 'Porygon line: genderless, Ditto or held Ditto Extract pair', UNIX_TIMESTAMP()),
  (374, 374, 'mineral', '', 'M', 'genderless', 1, 'Beldum line: genderless, Ditto or held Ditto Extract pair', UNIX_TIMESTAMP()),
  (375, 374, 'mineral', '', 'M', 'genderless', 1, 'Beldum line: genderless, Ditto or held Ditto Extract pair', UNIX_TIMESTAMP()),
  (376, 374, 'mineral', '', 'M', 'genderless', 1, 'Beldum line: genderless, Ditto or held Ditto Extract pair', UNIX_TIMESTAMP()),
  (382, 382, 'no_eggs', '', 'Z', 'genderless', 0, 'Legendary: breeding disabled', UNIX_TIMESTAMP()),
  (383, 383, 'no_eggs', '', 'Z', 'genderless', 0, 'Legendary: breeding disabled', UNIX_TIMESTAMP()),
  (384, 384, 'no_eggs', '', 'Z', 'genderless', 0, 'Legendary: breeding disabled', UNIX_TIMESTAMP()),
  (448, 447, 'field', 'humanlike', 'E', 'normal', 1, 'Lucario line eggs into Riolu: compatibility E', UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
  egg_base_id = VALUES(egg_base_id),
  egg_group_1 = VALUES(egg_group_1),
  egg_group_2 = VALUES(egg_group_2),
  compatibility_letter = VALUES(compatibility_letter),
  gender_mode = VALUES(gender_mode),
  breedable = VALUES(breedable),
  notes = VALUES(notes),
  updated_at = VALUES(updated_at);

UPDATE pokemon_breeding_rules
   SET compatibility_letter = UPPER(LEFT(egg_group_1, 1))
 WHERE compatibility_letter = ''
   AND egg_group_1 NOT IN ('', 'ditto', 'no_eggs');

INSERT INTO items (id, cools, name, tittle, category, uses, dress, delet, torg, elementary, timesnapoke, times, battleuse, dopolnen)
SELECT 90310, 0, 'Экстракт Дитто', 'Held utility item для разведения: должен быть надет на обоих бесполых breedable-покемонах с одинаковой буквой совместимости. Не работает с Ditto, No Eggs Discovered и легендарными.', 12, 1, 1, 1, 1, 0, 0, '0', 0, 'held_item:ditto_extract;breeding:ditto_extract'
FROM (SELECT 1) seed
WHERE NOT EXISTS (SELECT 1 FROM items WHERE id = 90310);

UPDATE items
   SET name = 'Экстракт Дитто',
       tittle = 'Held utility item для разведения: должен быть надет на обоих бесполых breedable-покемонах с одинаковой буквой совместимости. Не работает с Ditto, No Eggs Discovered и легендарными.',
       category = 12,
       uses = 1,
       dress = 1,
       battleuse = 0,
       dopolnen = 'held_item:ditto_extract;breeding:ditto_extract'
 WHERE id = 90310;

INSERT INTO item_gameplay_metadata
  (item_id, ru_name, en_alias, category_key, description, target_use_rule, battleuse_flag, equipuse_flag, effect_key, effect_status, compatibility_rule, compatibility_json, safe_to_equip, updated_at)
VALUES
  (90310, 'Экстракт Дитто', 'Ditto Extract', 'utility', 'Held helper для breeding: если предмет надет на обоих бесполых breedable-покемонах с одинаковой буквой совместимости, пара может создать яйцо. Не работает с Ditto, No Eggs Discovered и легендарными.', 'equip_held', 0, 1, 'held_item:ditto_extract;breeding:ditto_extract', 'implemented', 'universal', NULL, 1, UNIX_TIMESTAMP())
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

INSERT INTO item_target_rules (item_id, enabled, target_type, allow_quantity, min_count, max_count, effect_key, consume_on_success, ui_title, ui_hint, created_at, updated_at)
VALUES
  (90310, 1, 'pokemon', 0, 1, 1, 'equip_held', 1, 'Надеть Экстракт', 'Наденьте предмет на бесполого покемона. Для спарки через Extract предмет нужен на обоих родителях.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
  enabled = VALUES(enabled),
  target_type = VALUES(target_type),
  allow_quantity = VALUES(allow_quantity),
  min_count = VALUES(min_count),
  max_count = VALUES(max_count),
  effect_key = VALUES(effect_key),
  consume_on_success = VALUES(consume_on_success),
  ui_title = VALUES(ui_title),
  ui_hint = VALUES(ui_hint),
  updated_at = VALUES(updated_at);
