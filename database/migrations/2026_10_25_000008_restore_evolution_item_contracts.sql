-- Restore historical evolution items without reusing current held-item IDs.
-- No player inventory, shop listing, price or reward is changed.
-- Parent must back up and apply serially. Allocation uses MAX(id)+1, never overwrites occupied IDs.
-- Re-running reuses the exact evolution-only name+marker and leaves held item metadata intact.

SET @evolution_item_id = COALESCE(
    (SELECT MIN(id) FROM items WHERE name = 'Острый клык' AND dopolnen = 'evolution_item'),
    (SELECT COALESCE(MAX(id), 0) + 1 FROM items)
);
INSERT INTO items
    (id, cools, name, tittle, category, uses, dress, delet, torg, elementary, timesnapoke, times, battleuse, dopolnen)
SELECT @evolution_item_id, 0, 'Острый клык', 'Предмет эволюции: Gligar превращается в Gliscor. При успешном применении расходуется одна штука.', 0, 1, 0, 1, 1, 0, 1, '0', 0, 'evolution_item'
WHERE NOT EXISTS (SELECT 1 FROM items WHERE id = @evolution_item_id);

INSERT INTO item_target_rules
    (item_id, enabled, target_type, allow_quantity, min_count, max_count, effect_key, consume_on_success, ui_title, ui_hint, created_at, updated_at)
VALUES (@evolution_item_id, 1, 'pokemon', 0, 1, 1, 'evolution_item', 1, 'Эволюция предметом', 'Подходит только покемонам с этой эволюцией. При ошибке предмет не списывается.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE enabled = 1, target_type = 'pokemon', allow_quantity = 0,
    min_count = 1, max_count = 1, effect_key = 'evolution_item', consume_on_success = 1,
    ui_title = VALUES(ui_title), ui_hint = VALUES(ui_hint), updated_at = UNIX_TIMESTAMP();

INSERT INTO pokemon_evolution_rules
    (from_base_id, to_base_id, trigger_type, level_required, item_id, condition_text, priority, enabled, created_at, updated_at)
SELECT from_base_id, to_base_id, trigger_type, level_required, @evolution_item_id,
       condition_text, priority, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
FROM pokemon_evolution_rules
WHERE trigger_type = 'item' AND item_id = 80 AND condition_text = 'Острый клык'
ON DUPLICATE KEY UPDATE condition_text = VALUES(condition_text), priority = VALUES(priority),
    enabled = 1, updated_at = UNIX_TIMESTAMP();

UPDATE pokemon_evolution_rules SET enabled = 0, updated_at = UNIX_TIMESTAMP()
WHERE trigger_type = 'item' AND item_id = 80 AND condition_text = 'Острый клык';

SET @evolution_item_id = COALESCE(
    (SELECT MIN(id) FROM items WHERE name = 'Острый коготь для эволюции' AND dopolnen = 'evolution_item'),
    (SELECT COALESCE(MAX(id), 0) + 1 FROM items)
);
INSERT INTO items
    (id, cools, name, tittle, category, uses, dress, delet, torg, elementary, timesnapoke, times, battleuse, dopolnen)
SELECT @evolution_item_id, 0, 'Острый коготь для эволюции', 'Предмет эволюции: Sneasel превращается в Weavile. При успешном применении расходуется одна штука. Это отдельный предмет, обычный Острый коготь остаётся надеваемым.', 0, 1, 0, 1, 1, 0, 1, '0', 0, 'evolution_item'
WHERE NOT EXISTS (SELECT 1 FROM items WHERE id = @evolution_item_id);

INSERT INTO item_target_rules
    (item_id, enabled, target_type, allow_quantity, min_count, max_count, effect_key, consume_on_success, ui_title, ui_hint, created_at, updated_at)
VALUES (@evolution_item_id, 1, 'pokemon', 0, 1, 1, 'evolution_item', 1, 'Эволюция предметом', 'Подходит только покемонам с этой эволюцией. При ошибке предмет не списывается.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE enabled = 1, target_type = 'pokemon', allow_quantity = 0,
    min_count = 1, max_count = 1, effect_key = 'evolution_item', consume_on_success = 1,
    ui_title = VALUES(ui_title), ui_hint = VALUES(ui_hint), updated_at = UNIX_TIMESTAMP();

INSERT INTO pokemon_evolution_rules
    (from_base_id, to_base_id, trigger_type, level_required, item_id, condition_text, priority, enabled, created_at, updated_at)
SELECT from_base_id, to_base_id, trigger_type, level_required, @evolution_item_id,
       condition_text, priority, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
FROM pokemon_evolution_rules
WHERE trigger_type = 'item' AND item_id = 81 AND condition_text = 'Острый коготь'
ON DUPLICATE KEY UPDATE condition_text = VALUES(condition_text), priority = VALUES(priority),
    enabled = 1, updated_at = UNIX_TIMESTAMP();

UPDATE pokemon_evolution_rules SET enabled = 0, updated_at = UNIX_TIMESTAMP()
WHERE trigger_type = 'item' AND item_id = 81 AND condition_text = 'Острый коготь';

SET @evolution_item_id = COALESCE(
    (SELECT MIN(id) FROM items WHERE name = 'Жуткая ткань' AND dopolnen = 'evolution_item'),
    (SELECT COALESCE(MAX(id), 0) + 1 FROM items)
);
INSERT INTO items
    (id, cools, name, tittle, category, uses, dress, delet, torg, elementary, timesnapoke, times, battleuse, dopolnen)
SELECT @evolution_item_id, 0, 'Жуткая ткань', 'Предмет эволюции: Dusclops превращается в Dusknoir. При успешном применении расходуется одна штука.', 0, 1, 0, 1, 1, 0, 1, '0', 0, 'evolution_item'
WHERE NOT EXISTS (SELECT 1 FROM items WHERE id = @evolution_item_id);

INSERT INTO item_target_rules
    (item_id, enabled, target_type, allow_quantity, min_count, max_count, effect_key, consume_on_success, ui_title, ui_hint, created_at, updated_at)
VALUES (@evolution_item_id, 1, 'pokemon', 0, 1, 1, 'evolution_item', 1, 'Эволюция предметом', 'Подходит только покемонам с этой эволюцией. При ошибке предмет не списывается.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE enabled = 1, target_type = 'pokemon', allow_quantity = 0,
    min_count = 1, max_count = 1, effect_key = 'evolution_item', consume_on_success = 1,
    ui_title = VALUES(ui_title), ui_hint = VALUES(ui_hint), updated_at = UNIX_TIMESTAMP();

INSERT INTO pokemon_evolution_rules
    (from_base_id, to_base_id, trigger_type, level_required, item_id, condition_text, priority, enabled, created_at, updated_at)
SELECT from_base_id, to_base_id, trigger_type, level_required, @evolution_item_id,
       condition_text, priority, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
FROM pokemon_evolution_rules
WHERE trigger_type = 'item' AND item_id = 82 AND condition_text = 'Жуткая ткань'
ON DUPLICATE KEY UPDATE condition_text = VALUES(condition_text), priority = VALUES(priority),
    enabled = 1, updated_at = UNIX_TIMESTAMP();

UPDATE pokemon_evolution_rules SET enabled = 0, updated_at = UNIX_TIMESTAMP()
WHERE trigger_type = 'item' AND item_id = 82 AND condition_text = 'Жуткая ткань';

SET @evolution_item_id = COALESCE(
    (SELECT MIN(id) FROM items WHERE name = 'Глубинная чешуя' AND dopolnen = 'evolution_item'),
    (SELECT COALESCE(MAX(id), 0) + 1 FROM items)
);
INSERT INTO items
    (id, cools, name, tittle, category, uses, dress, delet, torg, elementary, timesnapoke, times, battleuse, dopolnen)
SELECT @evolution_item_id, 0, 'Глубинная чешуя', 'Слабо светящаяся чешуйка. С ее помощью эволюционирует Clamperl.', 0, 1, 0, 1, 1, 0, 1, '0', 0, 'evolution_item'
WHERE NOT EXISTS (SELECT 1 FROM items WHERE id = @evolution_item_id);

INSERT INTO item_target_rules
    (item_id, enabled, target_type, allow_quantity, min_count, max_count, effect_key, consume_on_success, ui_title, ui_hint, created_at, updated_at)
VALUES (@evolution_item_id, 1, 'pokemon', 0, 1, 1, 'evolution_item', 1, 'Эволюция предметом', 'Подходит только покемонам с этой эволюцией. При ошибке предмет не списывается.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE enabled = 1, target_type = 'pokemon', allow_quantity = 0,
    min_count = 1, max_count = 1, effect_key = 'evolution_item', consume_on_success = 1,
    ui_title = VALUES(ui_title), ui_hint = VALUES(ui_hint), updated_at = UNIX_TIMESTAMP();

INSERT INTO pokemon_evolution_rules
    (from_base_id, to_base_id, trigger_type, level_required, item_id, condition_text, priority, enabled, created_at, updated_at)
SELECT from_base_id, to_base_id, trigger_type, level_required, @evolution_item_id,
       condition_text, priority, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
FROM pokemon_evolution_rules
WHERE trigger_type = 'item' AND item_id = 83 AND condition_text = 'Глубинная чешуя'
ON DUPLICATE KEY UPDATE condition_text = VALUES(condition_text), priority = VALUES(priority),
    enabled = 1, updated_at = UNIX_TIMESTAMP();

UPDATE pokemon_evolution_rules SET enabled = 0, updated_at = UNIX_TIMESTAMP()
WHERE trigger_type = 'item' AND item_id = 83 AND condition_text = 'Глубинная чешуя';

SET @evolution_item_id = COALESCE(
    (SELECT MIN(id) FROM items WHERE name = 'Магматор' AND dopolnen = 'evolution_item'),
    (SELECT COALESCE(MAX(id), 0) + 1 FROM items)
);
INSERT INTO items
    (id, cools, name, tittle, category, uses, dress, delet, torg, elementary, timesnapoke, times, battleuse, dopolnen)
SELECT @evolution_item_id, 0, 'Магматор', 'Коробка, наполненная магматической энергией. С ее помощью эволюционирует Magmar.', 0, 1, 0, 1, 1, 0, 1, '0', 0, 'evolution_item'
WHERE NOT EXISTS (SELECT 1 FROM items WHERE id = @evolution_item_id);

INSERT INTO item_target_rules
    (item_id, enabled, target_type, allow_quantity, min_count, max_count, effect_key, consume_on_success, ui_title, ui_hint, created_at, updated_at)
VALUES (@evolution_item_id, 1, 'pokemon', 0, 1, 1, 'evolution_item', 1, 'Эволюция предметом', 'Подходит только покемонам с этой эволюцией. При ошибке предмет не списывается.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE enabled = 1, target_type = 'pokemon', allow_quantity = 0,
    min_count = 1, max_count = 1, effect_key = 'evolution_item', consume_on_success = 1,
    ui_title = VALUES(ui_title), ui_hint = VALUES(ui_hint), updated_at = UNIX_TIMESTAMP();

INSERT INTO pokemon_evolution_rules
    (from_base_id, to_base_id, trigger_type, level_required, item_id, condition_text, priority, enabled, created_at, updated_at)
SELECT from_base_id, to_base_id, trigger_type, level_required, @evolution_item_id,
       condition_text, priority, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
FROM pokemon_evolution_rules
WHERE trigger_type = 'item' AND item_id = 86 AND condition_text = 'Магматор'
ON DUPLICATE KEY UPDATE condition_text = VALUES(condition_text), priority = VALUES(priority),
    enabled = 1, updated_at = UNIX_TIMESTAMP();

UPDATE pokemon_evolution_rules SET enabled = 0, updated_at = UNIX_TIMESTAMP()
WHERE trigger_type = 'item' AND item_id = 86 AND condition_text = 'Магматор';

SET @evolution_item_id = COALESCE(
    (SELECT MIN(id) FROM items WHERE name = 'Электрайзер' AND dopolnen = 'evolution_item'),
    (SELECT COALESCE(MAX(id), 0) + 1 FROM items)
);
INSERT INTO items
    (id, cools, name, tittle, category, uses, dress, delet, torg, elementary, timesnapoke, times, battleuse, dopolnen)
SELECT @evolution_item_id, 0, 'Электрайзер', 'Коробка, наполненная электроэнергией. С ее помощью эволюционирует Electabuzz.', 0, 1, 0, 1, 1, 0, 1, '0', 0, 'evolution_item'
WHERE NOT EXISTS (SELECT 1 FROM items WHERE id = @evolution_item_id);

INSERT INTO item_target_rules
    (item_id, enabled, target_type, allow_quantity, min_count, max_count, effect_key, consume_on_success, ui_title, ui_hint, created_at, updated_at)
VALUES (@evolution_item_id, 1, 'pokemon', 0, 1, 1, 'evolution_item', 1, 'Эволюция предметом', 'Подходит только покемонам с этой эволюцией. При ошибке предмет не списывается.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE enabled = 1, target_type = 'pokemon', allow_quantity = 0,
    min_count = 1, max_count = 1, effect_key = 'evolution_item', consume_on_success = 1,
    ui_title = VALUES(ui_title), ui_hint = VALUES(ui_hint), updated_at = UNIX_TIMESTAMP();

INSERT INTO pokemon_evolution_rules
    (from_base_id, to_base_id, trigger_type, level_required, item_id, condition_text, priority, enabled, created_at, updated_at)
SELECT from_base_id, to_base_id, trigger_type, level_required, @evolution_item_id,
       condition_text, priority, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
FROM pokemon_evolution_rules
WHERE trigger_type = 'item' AND item_id = 87 AND condition_text = 'Электрайзер'
ON DUPLICATE KEY UPDATE condition_text = VALUES(condition_text), priority = VALUES(priority),
    enabled = 1, updated_at = UNIX_TIMESTAMP();

UPDATE pokemon_evolution_rules SET enabled = 0, updated_at = UNIX_TIMESTAMP()
WHERE trigger_type = 'item' AND item_id = 87 AND condition_text = 'Электрайзер';

SET @evolution_item_id = COALESCE(
    (SELECT MIN(id) FROM items WHERE name = 'Протектор' AND dopolnen = 'evolution_item'),
    (SELECT COALESCE(MAX(id), 0) + 1 FROM items)
);
INSERT INTO items
    (id, cools, name, tittle, category, uses, dress, delet, torg, elementary, timesnapoke, times, battleuse, dopolnen)
SELECT @evolution_item_id, 0, 'Протектор', 'Тяжелый защитный модуль с усиленным ядром. С его помощью Rhydon эволюционирует в Rhyperior.', 0, 1, 0, 1, 1, 0, 1, '0', 0, 'evolution_item'
WHERE NOT EXISTS (SELECT 1 FROM items WHERE id = @evolution_item_id);

INSERT INTO item_target_rules
    (item_id, enabled, target_type, allow_quantity, min_count, max_count, effect_key, consume_on_success, ui_title, ui_hint, created_at, updated_at)
VALUES (@evolution_item_id, 1, 'pokemon', 0, 1, 1, 'evolution_item', 1, 'Эволюция предметом', 'Подходит только покемонам с этой эволюцией. При ошибке предмет не списывается.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE enabled = 1, target_type = 'pokemon', allow_quantity = 0,
    min_count = 1, max_count = 1, effect_key = 'evolution_item', consume_on_success = 1,
    ui_title = VALUES(ui_title), ui_hint = VALUES(ui_hint), updated_at = UNIX_TIMESTAMP();

INSERT INTO pokemon_evolution_rules
    (from_base_id, to_base_id, trigger_type, level_required, item_id, condition_text, priority, enabled, created_at, updated_at)
SELECT from_base_id, to_base_id, trigger_type, level_required, @evolution_item_id,
       condition_text, priority, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
FROM pokemon_evolution_rules
WHERE trigger_type = 'item' AND item_id = 89 AND condition_text = 'Протектор'
ON DUPLICATE KEY UPDATE condition_text = VALUES(condition_text), priority = VALUES(priority),
    enabled = 1, updated_at = UNIX_TIMESTAMP();

UPDATE pokemon_evolution_rules SET enabled = 0, updated_at = UNIX_TIMESTAMP()
WHERE trigger_type = 'item' AND item_id = 89 AND condition_text = 'Протектор';

-- ID 71 is an existing Dawn Stone alias: copy the current ID64 targeting contract.
INSERT INTO item_target_rules
    (item_id, enabled, target_type, allow_quantity, min_count, max_count, effect_key, consume_on_success, ui_title, ui_hint, created_at, updated_at)
SELECT 71, 1, target_type, allow_quantity, min_count, max_count, effect_key,
       consume_on_success, ui_title, ui_hint, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
FROM item_target_rules WHERE item_id = 64 AND effect_key = 'evolution_item'
ON DUPLICATE KEY UPDATE enabled = 1, target_type = VALUES(target_type), allow_quantity = VALUES(allow_quantity),
    min_count = VALUES(min_count), max_count = VALUES(max_count), effect_key = VALUES(effect_key),
    consume_on_success = VALUES(consume_on_success), ui_title = VALUES(ui_title),
    ui_hint = VALUES(ui_hint), updated_at = UNIX_TIMESTAMP();

-- ID332 is now Moon Stone. Remove only historical Oval Stone/Happiny mapping, if present.
UPDATE pokemon_evolution_rules SET enabled = 0, updated_at = UNIX_TIMESTAMP()
WHERE trigger_type = 'item' AND item_id = 332 AND from_base_id = 440 AND to_base_id = 113;

INSERT INTO pokemon_evolution_rules
    (from_base_id, to_base_id, trigger_type, level_required, item_id, condition_text, priority, enabled, created_at, updated_at)
SELECT from_base_id, to_base_id, trigger_type, level_required, 332,
       condition_text, priority, enabled, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
FROM pokemon_evolution_rules
WHERE trigger_type = 'item' AND item_id = 69 AND enabled = 1
ON DUPLICATE KEY UPDATE condition_text = VALUES(condition_text), priority = VALUES(priority),
    enabled = VALUES(enabled), updated_at = UNIX_TIMESTAMP();

-- Hours preserve unic_evol's existing game contract; this migration does not extend it to morning.
UPDATE pokemon_evolution_rules
SET condition_text = 'Дневное время (13:00–18:59) + 100% счастья', updated_at = UNIX_TIMESTAMP()
WHERE from_base_id = 133 AND to_base_id = 196 AND trigger_type = 'condition'
    AND condition_text = 'Дневное время + 100% счастья';

UPDATE pokemon_evolution_rules
SET condition_text = 'Ночное время (19:00–23:59) + 100% счастья', updated_at = UNIX_TIMESTAMP()
WHERE from_base_id = 133 AND to_base_id = 197 AND trigger_type = 'condition'
    AND condition_text = 'Ночное время + 100% счастья';

