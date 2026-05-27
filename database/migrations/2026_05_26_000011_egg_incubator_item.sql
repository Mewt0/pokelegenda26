-- Idempotent: item used by /api/eggs/incubate to halve remaining hatch time.
INSERT INTO items (id, cools, name, tittle, category, uses, dress, delet, torg, elementary, timesnapoke, times, battleuse, dopolnen)
SELECT 90311, 0, 'Инкубатор', 'Ускоряет инкубацию выбранного яйца: каждое применение уменьшает оставшееся время до вылупления в 2 раза.', 12, 1, 0, 1, 1, 0, 0, '0', 0, 'egg_incubator'
FROM (SELECT 1) seed
WHERE NOT EXISTS (SELECT 1 FROM items WHERE id = 90311);

UPDATE items
   SET name = 'Инкубатор',
       tittle = 'Ускоряет инкубацию выбранного яйца: каждое применение уменьшает оставшееся время до вылупления в 2 раза.',
       category = 12,
       uses = 1,
       dress = 0,
       battleuse = 0,
       dopolnen = 'egg_incubator'
 WHERE id = 90311;

INSERT INTO item_gameplay_metadata
  (item_id, ru_name, en_alias, category_key, description, target_use_rule, battleuse_flag, equipuse_flag, effect_key, effect_status, compatibility_rule, compatibility_json, safe_to_equip, updated_at)
VALUES
  (90311, 'Инкубатор', 'Egg Incubator', 'utility', 'Ускоряет инкубацию яйца: каждое применение уменьшает оставшееся время до вылупления в 2 раза.', 'egg_incubator', 0, 0, 'egg_incubator:halve_remaining', 'implemented', 'none', NULL, 0, UNIX_TIMESTAMP())
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
