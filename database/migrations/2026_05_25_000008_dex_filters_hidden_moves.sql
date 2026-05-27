CREATE TABLE IF NOT EXISTS pokemon_special_moves (
    id INT NOT NULL AUTO_INCREMENT,
    poke_base_id INT NOT NULL,
    atac_id INT NOT NULL,
    method_type VARCHAR(32) NOT NULL DEFAULT 'hidden',
    condition_text VARCHAR(255) NOT NULL DEFAULT '',
    enabled TINYINT NOT NULL DEFAULT 1,
    created_at INT NOT NULL DEFAULT 0,
    updated_at INT NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_pokemon_special_move (poke_base_id, atac_id, method_type),
    KEY idx_pokemon_special_moves_pokemon (poke_base_id, enabled),
    KEY idx_pokemon_special_moves_attack (atac_id, enabled)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO pokemon_special_moves
    (poke_base_id, atac_id, method_type, condition_text, enabled, created_at, updated_at)
SELECT seed.poke_base_id,
       ap.atac_id,
       'hidden',
       'Скрытая атака / условие эволюции',
       1,
       UNIX_TIMESTAMP(),
       UNIX_TIMESTAMP()
  FROM (
        SELECT 114 AS poke_base_id UNION ALL
        SELECT 193 UNION ALL
        SELECT 221
       ) seed
 INNER JOIN pokemon p ON p.id = seed.poke_base_id
 INNER JOIN attac_power ap ON LOWER(REPLACE(REPLACE(ap.atac_name, ' ', ''), '-', '')) = 'ancientpower'
ON DUPLICATE KEY UPDATE
    condition_text = VALUES(condition_text),
    enabled = VALUES(enabled),
    updated_at = UNIX_TIMESTAMP();

INSERT INTO pokemon_evolution_rules
    (from_base_id, to_base_id, trigger_type, level_required, item_id, condition_text, priority, enabled, created_at, updated_at)
SELECT seed.from_base_id,
       seed.to_base_id,
       'condition',
       0,
       0,
       'Нужно знать Ancient Power',
       80,
       1,
       UNIX_TIMESTAMP(),
       UNIX_TIMESTAMP()
  FROM (
        SELECT 114 AS from_base_id, 465 AS to_base_id UNION ALL
        SELECT 193, 469 UNION ALL
        SELECT 221, 473
       ) seed
 INNER JOIN pokemon source ON source.id = seed.from_base_id
 INNER JOIN pokemon target ON target.id = seed.to_base_id
ON DUPLICATE KEY UPDATE
    condition_text = VALUES(condition_text),
    priority = VALUES(priority),
    enabled = VALUES(enabled),
    updated_at = UNIX_TIMESTAMP();
