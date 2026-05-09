-- Evolution items from plan.md.
-- Idempotent: updates existing items/rules and inserts missing evolution item rows without touching player inventory.

UPDATE items SET
    name = 'Камень рассвета',
    tittle = 'Необычный камень, наделенный особой энергией. С его помощью эволюционируют Kirlia, Snorunt и Rellor.'
WHERE id = 64;

UPDATE items SET
    name = 'Сумрачный камень',
    tittle = 'Необычный камень, наделенный особой энергией. С его помощью эволюционируют Cubone, Murkrow, Misdreavus, Lampent, Doublade и Sinistea.'
WHERE id = 66;

UPDATE items SET
    name = 'Огненный камень',
    tittle = 'Необычный камень, наделенный огненной энергией. С его помощью эволюционируют Vulpix, Growlithe, Eevee, Pansear и Capsakid.'
WHERE id = 67;

UPDATE items SET
    name = 'Листовой камень',
    tittle = 'Необычный камень, наделенный энергией растений. С его помощью эволюционируют Gloom, Weepinbell, Exeggcute, Nuzleaf, Pansage и Voltorb Hisui.'
WHERE id = 68;

UPDATE items SET
    name = 'Лунный камень',
    tittle = 'Необычный камень, наделенный лунной энергией. С его помощью эволюционируют Nidorina, Nidorino, Clefairy, Jigglypuff, Skitty и Munna.'
WHERE id = 69;

UPDATE items SET
    name = 'Солнечный камень',
    tittle = 'Необычный камень, наделенный солнечной энергией. С его помощью эволюционируют Gloom, Sunkern, Cottonee, Petilil и Helioptile.'
WHERE id = 74;

UPDATE items SET
    name = 'Громовой камень',
    tittle = 'Необычный камень, наделенный электрической энергией. С его помощью эволюционируют Pikachu, Eevee, Eelektrik, Charjabug и Tadbulb.'
WHERE id = 75;

UPDATE items SET
    name = 'Ледяной камень',
    tittle = 'Необычный камень, наделенный энергией льда. С его помощью эволюционируют Sandshrew, Vulpix, Mime Jr., Darumaka Galar, Crabrawler, Snom и Cetoddle.'
WHERE id = 76;

UPDATE items SET
    name = 'Водяной камень',
    tittle = 'Необычный камень, наделенный водной энергией. С его помощью эволюционируют Poliwhirl, Shellder, Staryu, Eevee, Lombre, Panpour и Basculin.'
WHERE id = 77;

INSERT INTO items (id, cools, name, tittle, category, uses, dress, delet, torg, elementary, timesnapoke, times, battleuse, dopolnen)
SELECT seed.id, 0, seed.name, seed.tittle, 0, 1, 0, 1, 1, 0, 1, '0', 0, 'evolution_item'
  FROM (
        SELECT 80 id, 'Острый клык' name, 'Имеет шанс вызвать испуг при атаке. С его помощью эволюционирует Gligar.' tittle UNION ALL
        SELECT 81, 'Острый коготь', 'Повышает шанс критического удара. С его помощью эволюционируют Sneasel и Sneasel Hisui.' UNION ALL
        SELECT 82, 'Жуткая ткань', 'Жуткая ткань с духовной энергией. С ее помощью эволюционируют Dusclops и Bisharp.' UNION ALL
        SELECT 83, 'Глубинная чешуя', 'Слабо светящаяся чешуйка. С ее помощью эволюционирует Clamperl.' UNION ALL
        SELECT 86, 'Магматор', 'Коробка, наполненная магматической энергией. С ее помощью эволюционирует Magmar.' UNION ALL
        SELECT 87, 'Электрайзер', 'Коробка, наполненная электроэнергией. С ее помощью эволюционирует Electabuzz.' UNION ALL
        SELECT 89, 'Протектор', 'Тяжелый защитный модуль с усиленным ядром. С его помощью Rhydon эволюционирует в Rhyperior.' UNION ALL
        SELECT 332, 'Овальный камень', 'Своеобразный камень, похожий на яйцо. С его помощью эволюционирует Happiny.'
       ) seed
 WHERE NOT EXISTS (SELECT 1 FROM items i WHERE i.id = seed.id);

UPDATE items SET
    category = 0,
    uses = 1,
    delet = 1,
    torg = 1,
    battleuse = 0,
    dopolnen = 'evolution_item'
WHERE id IN (64,66,67,68,69,74,75,76,77,80,81,82,83,86,87,89,332);

INSERT INTO item_target_rules
    (item_id, enabled, target_type, allow_quantity, min_count, max_count, effect_key, consume_on_success, ui_title, ui_hint, created_at, updated_at)
VALUES
    (69, 1, 'pokemon', 0, 1, 1, 'evolution_item', 1, 'Эволюция камнем', 'Подходит только покемонам с этой эволюцией.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    (80, 1, 'pokemon', 0, 1, 1, 'evolution_item', 1, 'Эволюция предметом', 'Подходит только покемонам с этой эволюцией.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    (81, 1, 'pokemon', 0, 1, 1, 'evolution_item', 1, 'Эволюция предметом', 'Подходит только покемонам с этой эволюцией.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    (82, 1, 'pokemon', 0, 1, 1, 'evolution_item', 1, 'Эволюция предметом', 'Подходит только покемонам с этой эволюцией.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    (83, 1, 'pokemon', 0, 1, 1, 'evolution_item', 1, 'Эволюция предметом', 'Подходит только покемонам с этой эволюцией.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    (86, 1, 'pokemon', 0, 1, 1, 'evolution_item', 1, 'Эволюция предметом', 'Подходит только покемонам с этой эволюцией.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    (87, 1, 'pokemon', 0, 1, 1, 'evolution_item', 1, 'Эволюция предметом', 'Подходит только покемонам с этой эволюцией.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    (89, 1, 'pokemon', 0, 1, 1, 'evolution_item', 1, 'Эволюция предметом', 'Подходит только покемонам с этой эволюцией.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    (332, 1, 'pokemon', 0, 1, 1, 'evolution_item', 1, 'Эволюция предметом', 'Подходит только покемонам с этой эволюцией.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
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
    updated_at = UNIX_TIMESTAMP();

INSERT INTO pokemon_evolution_rules
    (from_base_id, to_base_id, trigger_type, level_required, item_id, condition_text, priority, enabled, created_at, updated_at)
SELECT seed.from_base_id,
       seed.to_base_id,
       'item',
       0,
       seed.item_id,
       seed.condition_text,
       100,
       1,
       UNIX_TIMESTAMP(),
       UNIX_TIMESTAMP()
  FROM (
        SELECT 104 from_base_id, 105 to_base_id, 66 item_id, 'Сумрачный камень' condition_text UNION ALL
        SELECT 30, 31, 69, 'Лунный камень' UNION ALL
        SELECT 33, 34, 69, 'Лунный камень' UNION ALL
        SELECT 35, 36, 69, 'Лунный камень' UNION ALL
        SELECT 39, 40, 69, 'Лунный камень' UNION ALL
        SELECT 300, 301, 69, 'Лунный камень' UNION ALL
        SELECT 517, 518, 69, 'Лунный камень' UNION ALL
        SELECT 440, 113, 332, 'Овальный камень' UNION ALL
        SELECT 207, 472, 80, 'Острый клык' UNION ALL
        SELECT 215, 461, 81, 'Острый коготь' UNION ALL
        SELECT 356, 477, 82, 'Жуткая ткань' UNION ALL
        SELECT 625, 983, 82, 'Жуткая ткань' UNION ALL
        SELECT 366, 368, 83, 'Глубинная чешуя' UNION ALL
        SELECT 126, 467, 86, 'Магматор' UNION ALL
        SELECT 125, 466, 87, 'Электрайзер' UNION ALL
        SELECT 112, 464, 89, 'Протектор' UNION ALL
        SELECT 737, 738, 75, 'Громовой камень' UNION ALL
        SELECT 938, 939, 75, 'Громовой камень' UNION ALL
        SELECT 739, 740, 76, 'Ледяной камень' UNION ALL
        SELECT 872, 873, 76, 'Ледяной камень' UNION ALL
        SELECT 974, 975, 76, 'Ледяной камень' UNION ALL
        SELECT 550, 902, 77, 'Водяной камень' UNION ALL
        SELECT 951, 952, 67, 'Огненный камень' UNION ALL
        SELECT 953, 954, 64, 'Камень рассвета' UNION ALL
        SELECT 854, 855, 66, 'Сумрачный камень'
       ) seed
 INNER JOIN poke_base source ON source.id = seed.from_base_id
 INNER JOIN poke_base target ON target.id = seed.to_base_id
ON DUPLICATE KEY UPDATE
    condition_text = VALUES(condition_text),
    enabled = VALUES(enabled),
    updated_at = UNIX_TIMESTAMP();
