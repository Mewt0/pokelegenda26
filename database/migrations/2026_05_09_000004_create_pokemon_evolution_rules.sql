CREATE TABLE IF NOT EXISTS pokemon_evolution_rules (
    id INT NOT NULL AUTO_INCREMENT,
    from_base_id INT NOT NULL,
    to_base_id INT NOT NULL,
    trigger_type VARCHAR(24) NOT NULL DEFAULT 'level',
    level_required INT NOT NULL DEFAULT 0,
    item_id INT NOT NULL DEFAULT 0,
    condition_text VARCHAR(255) NOT NULL DEFAULT '',
    priority INT NOT NULL DEFAULT 100,
    enabled TINYINT NOT NULL DEFAULT 1,
    created_at INT NOT NULL DEFAULT 0,
    updated_at INT NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_pokemon_evolution_rule (from_base_id, to_base_id, trigger_type, level_required, item_id),
    KEY idx_pokemon_evolution_from (from_base_id, enabled, trigger_type),
    KEY idx_pokemon_evolution_to (to_base_id, enabled),
    KEY idx_pokemon_evolution_item (item_id, enabled)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO pokemon_evolution_rules
    (from_base_id, to_base_id, trigger_type, level_required, item_id, condition_text, priority, enabled, created_at, updated_at)
SELECT pb.id,
       pb.evolution_type,
       'level',
       pb.evolution_lvl,
       0,
       CONCAT('Уровень ', pb.evolution_lvl),
       100,
       1,
       UNIX_TIMESTAMP(),
       UNIX_TIMESTAMP()
  FROM poke_base pb
 INNER JOIN poke_base target ON target.id = pb.evolution_type
 WHERE pb.evolution_type > 0
   AND pb.evolution_type <> pb.id
   AND pb.evolution_lvl > 0
ON DUPLICATE KEY UPDATE
    condition_text = VALUES(condition_text),
    enabled = VALUES(enabled),
    updated_at = UNIX_TIMESTAMP();

INSERT INTO pokemon_evolution_rules
    (from_base_id, to_base_id, trigger_type, level_required, item_id, condition_text, priority, enabled, created_at, updated_at)
SELECT seed.from_base_id,
       seed.to_base_id,
       'item',
       seed.level_required,
       seed.item_id,
       seed.condition_text,
       seed.priority,
       1,
       UNIX_TIMESTAMP(),
       UNIX_TIMESTAMP()
  FROM (
        SELECT 25 from_base_id, 26 to_base_id, 0 level_required, 40 item_id, 'Громовой камень' condition_text, 100 priority UNION ALL
        SELECT 25, 26, 0, 75, 'Громовой камень', 100 UNION ALL
        SELECT 30, 31, 0, 44, 'Лунный камень', 100 UNION ALL
        SELECT 33, 34, 0, 44, 'Лунный камень', 100 UNION ALL
        SELECT 35, 36, 0, 44, 'Лунный камень', 100 UNION ALL
        SELECT 37, 38, 0, 41, 'Огненный камень', 100 UNION ALL
        SELECT 37, 38, 0, 67, 'Огненный камень', 100 UNION ALL
        SELECT 39, 40, 0, 44, 'Лунный камень', 100 UNION ALL
        SELECT 44, 45, 0, 43, 'Лиственный камень', 100 UNION ALL
        SELECT 44, 45, 0, 68, 'Листовой камень', 100 UNION ALL
        SELECT 44, 182, 0, 74, 'Солнечный камень', 90 UNION ALL
        SELECT 58, 59, 0, 41, 'Огненный камень', 100 UNION ALL
        SELECT 58, 59, 0, 67, 'Огненный камень', 100 UNION ALL
        SELECT 61, 62, 0, 42, 'Водный камень', 100 UNION ALL
        SELECT 61, 62, 0, 77, 'Водяной камень', 100 UNION ALL
        SELECT 70, 71, 0, 43, 'Лиственный камень', 100 UNION ALL
        SELECT 70, 71, 0, 68, 'Листовой камень', 100 UNION ALL
        SELECT 90, 91, 0, 42, 'Водный камень', 100 UNION ALL
        SELECT 90, 91, 0, 77, 'Водяной камень', 100 UNION ALL
        SELECT 102, 103, 0, 43, 'Лиственный камень', 100 UNION ALL
        SELECT 102, 103, 0, 68, 'Листовой камень', 100 UNION ALL
        SELECT 120, 121, 0, 42, 'Водный камень', 100 UNION ALL
        SELECT 120, 121, 0, 77, 'Водяной камень', 100 UNION ALL
        SELECT 133, 134, 0, 42, 'Водный камень', 100 UNION ALL
        SELECT 133, 134, 0, 77, 'Водяной камень', 100 UNION ALL
        SELECT 133, 135, 0, 40, 'Громовой камень', 100 UNION ALL
        SELECT 133, 135, 0, 75, 'Громовой камень', 100 UNION ALL
        SELECT 133, 136, 0, 41, 'Огненный камень', 100 UNION ALL
        SELECT 133, 136, 0, 67, 'Огненный камень', 100 UNION ALL
        SELECT 133, 470, 0, 43, 'Лиственный камень', 100 UNION ALL
        SELECT 133, 470, 0, 68, 'Листовой камень', 100 UNION ALL
        SELECT 133, 471, 0, 76, 'Ледяной камень', 100 UNION ALL
        SELECT 191, 192, 0, 74, 'Солнечный камень', 100 UNION ALL
        SELECT 198, 430, 0, 66, 'Сумрачный камень', 100 UNION ALL
        SELECT 200, 429, 0, 66, 'Сумрачный камень', 100 UNION ALL
        SELECT 271, 272, 0, 42, 'Водный камень', 100 UNION ALL
        SELECT 271, 272, 0, 77, 'Водяной камень', 100 UNION ALL
        SELECT 274, 275, 0, 43, 'Лиственный камень', 100 UNION ALL
        SELECT 274, 275, 0, 68, 'Листовой камень', 100 UNION ALL
        SELECT 281, 475, 0, 64, 'Камень рассвета', 100 UNION ALL
        SELECT 281, 475, 0, 71, 'Камень рассвета', 100 UNION ALL
        SELECT 300, 301, 0, 44, 'Лунный камень', 100 UNION ALL
        SELECT 361, 478, 0, 64, 'Камень рассвета', 100 UNION ALL
        SELECT 361, 478, 0, 71, 'Камень рассвета', 100 UNION ALL
        SELECT 511, 512, 0, 43, 'Лиственный камень', 100 UNION ALL
        SELECT 511, 512, 0, 68, 'Листовой камень', 100 UNION ALL
        SELECT 513, 514, 0, 41, 'Огненный камень', 100 UNION ALL
        SELECT 513, 514, 0, 67, 'Огненный камень', 100 UNION ALL
        SELECT 515, 516, 0, 42, 'Водный камень', 100 UNION ALL
        SELECT 515, 516, 0, 77, 'Водяной камень', 100 UNION ALL
        SELECT 517, 518, 0, 44, 'Лунный камень', 100 UNION ALL
        SELECT 546, 547, 0, 74, 'Солнечный камень', 100 UNION ALL
        SELECT 548, 549, 0, 74, 'Солнечный камень', 100 UNION ALL
        SELECT 603, 604, 0, 40, 'Громовой камень', 100 UNION ALL
        SELECT 603, 604, 0, 75, 'Громовой камень', 100 UNION ALL
        SELECT 608, 609, 0, 66, 'Сумрачный камень', 100 UNION ALL
        SELECT 680, 681, 0, 66, 'Сумрачный камень', 100 UNION ALL
        SELECT 694, 695, 0, 74, 'Солнечный камень', 100
       ) seed
 INNER JOIN poke_base source ON source.id = seed.from_base_id
 INNER JOIN poke_base target ON target.id = seed.to_base_id
ON DUPLICATE KEY UPDATE
    condition_text = VALUES(condition_text),
    priority = VALUES(priority),
    enabled = VALUES(enabled),
    updated_at = UNIX_TIMESTAMP();

INSERT INTO pokemon_evolution_rules
    (from_base_id, to_base_id, trigger_type, level_required, item_id, condition_text, priority, enabled, created_at, updated_at)
SELECT seed.from_base_id,
       seed.to_base_id,
       'condition',
       0,
       0,
       seed.condition_text,
       seed.priority,
       1,
       UNIX_TIMESTAMP(),
       UNIX_TIMESTAMP()
  FROM (
        SELECT 133 from_base_id, 196 to_base_id, 'Дневное время + 100% счастья' condition_text, 100 priority UNION ALL
        SELECT 133, 197, 'Ночное время + 100% счастья', 100 UNION ALL
        SELECT 193, 469, '50% счастья + Ancient Power', 100 UNION ALL
        SELECT 221, 473, '50% счастья + Ancient Power', 100 UNION ALL
        SELECT 406, 315, '100% счастья', 100 UNION ALL
        SELECT 527, 528, '100% счастья', 100
       ) seed
 INNER JOIN poke_base source ON source.id = seed.from_base_id
 INNER JOIN poke_base target ON target.id = seed.to_base_id
ON DUPLICATE KEY UPDATE
    condition_text = VALUES(condition_text),
    priority = VALUES(priority),
    enabled = VALUES(enabled),
    updated_at = UNIX_TIMESTAMP();
