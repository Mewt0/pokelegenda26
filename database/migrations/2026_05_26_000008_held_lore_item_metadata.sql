-- Idempotent: normalizes held/lore item metadata without removing existing game logic.
ALTER TABLE items MODIFY dopolnen VARCHAR(128) NOT NULL DEFAULT '0';

CREATE TABLE IF NOT EXISTS item_gameplay_metadata (
  item_id INT NOT NULL,
  ru_name VARCHAR(120) NOT NULL,
  en_alias VARCHAR(160) NOT NULL DEFAULT '',
  category_key VARCHAR(32) NOT NULL DEFAULT 'utility',
  description TEXT NOT NULL,
  target_use_rule VARCHAR(80) NOT NULL DEFAULT '',
  battleuse_flag TINYINT NOT NULL DEFAULT 0,
  equipuse_flag TINYINT NOT NULL DEFAULT 0,
  effect_key VARCHAR(128) NOT NULL DEFAULT '',
  effect_status VARCHAR(32) NOT NULL DEFAULT 'todo',
  compatibility_rule VARCHAR(64) NOT NULL DEFAULT 'universal',
  compatibility_json TEXT NULL,
  safe_to_equip TINYINT NOT NULL DEFAULT 1,
  updated_at INT NOT NULL DEFAULT 0,
  PRIMARY KEY (item_id),
  KEY idx_item_gameplay_metadata_category (category_key),
  KEY idx_item_gameplay_metadata_status (effect_status),
  KEY idx_item_gameplay_metadata_compatibility (compatibility_rule)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO item_gameplay_metadata
  (item_id, ru_name, en_alias, category_key, description, target_use_rule, battleuse_flag, equipuse_flag, effect_key, effect_status, compatibility_rule, compatibility_json, safe_to_equip, updated_at)
VALUES
  (78, 'Случайная TM-атака', 'Pink Bow / Polkadot Bow asset; runtime Random TM', 'utility', 'В текущей игре ID 78 занят случайной TM-атакой. Иконка 78.png сохранена, но рабочая логика TM не переписывается на held item.', 'tm_learn', 0, 0, 'tm_learn', 'implemented', 'none', NULL, 0, UNIX_TIMESTAMP()),
  (80, 'Быстрый коготь', 'Quick Claw', 'held_item', 'Held item: даёт шанс ударить раньше соперника.', 'equip_held', 0, 1, 'held_item:quick_claw', 'implemented', 'universal', NULL, 1, UNIX_TIMESTAMP()),
  (81, 'Острый коготь', 'Razor Claw', 'held_item', 'Held item: повышает шанс критического удара. Может участвовать в эволюции по отдельному правилу.', 'equip_held', 0, 1, 'held_item:scope_lens;held_item:razor_claw', 'implemented', 'universal', NULL, 1, UNIX_TIMESTAMP()),
  (82, 'Метка заклятия', 'Spell Tag', 'held_item', 'Held item: усиливает атаки призрачного типа.', 'equip_held', 0, 1, 'held_item:ghost', 'implemented', 'universal', NULL, 1, UNIX_TIMESTAMP()),
  (83, 'Розовый шарф', 'Pink Scarf', 'held_item', 'Held item: безопасный декоративный шарф; в beta работает как normal-усилитель.', 'equip_held', 0, 1, 'held_item:normal', 'implemented', 'universal', NULL, 1, UNIX_TIMESTAMP()),
  (85, 'Шёлковый шарф', 'Silk Scarf', 'held_item', 'Held item: усиливает атаки normal-типа.', 'equip_held', 0, 1, 'held_item:normal', 'implemented', 'universal', NULL, 1, UNIX_TIMESTAMP()),
  (86, 'Древесный уголь', 'Charcoal', 'held_item', 'Held item: усиливает атаки огненного типа.', 'equip_held', 0, 1, 'held_item:fire', 'implemented', 'universal', NULL, 1, UNIX_TIMESTAMP()),
  (87, 'Электрическая пластина', 'Zap Plate / Electric item', 'held_item', 'Held item: усиливает атаки электрического типа.', 'equip_held', 0, 1, 'held_item:electric', 'implemented', 'universal', NULL, 1, UNIX_TIMESTAMP()),
  (88, 'Металлическое покрытие', 'Metal Coat', 'held_item', 'Held item: усиливает атаки стального типа. Может участвовать в эволюции по отдельному правилу.', 'equip_held', 0, 1, 'held_item:steel', 'implemented', 'universal', NULL, 1, UNIX_TIMESTAMP()),
  (89, 'Твёрдый камень', 'Hard Stone', 'held_item', 'Held item: усиливает атаки каменного типа.', 'equip_held', 0, 1, 'held_item:rock', 'implemented', 'universal', NULL, 1, UNIX_TIMESTAMP()),
  (90, 'Согнутая ложка', 'Twisted Spoon', 'held_item', 'Held item: усиливает атаки психического типа.', 'equip_held', 0, 1, 'held_item:psychic', 'implemented', 'universal', NULL, 1, UNIX_TIMESTAMP()),
  (91, 'Ядовитый шип', 'Poison Barb', 'held_item', 'Held item: усиливает атаки ядовитого типа.', 'equip_held', 0, 1, 'held_item:poison', 'implemented', 'universal', NULL, 1, UNIX_TIMESTAMP()),
  (92, 'Браслет силы', 'Macho Brace', 'held_item', 'Held item: тренировочный предмет. В beta безопасно отображается и экипируется.', 'equip_held', 0, 1, 'held_item:training', 'visual_only', 'universal', NULL, 1, UNIX_TIMESTAMP()),
  (93, 'Клык дракона', 'Dragon Fang', 'held_item', 'Held item: усиливает атаки драконьего типа.', 'equip_held', 0, 1, 'held_item:dragon', 'implemented', 'universal', NULL, 1, UNIX_TIMESTAMP()),
  (147, 'Фото тренера', 'Trainer Card / Trainer Photo', 'utility', 'Памятный предмет для тренер-карты и наград.', '', 0, 0, 'trainer_card', 'visual_only', 'none', NULL, 0, UNIX_TIMESTAMP()),
  (152, 'Покебилет', 'Poke Ticket', 'ticket', 'Билет для транспортных и ивентовых механик.', '', 0, 0, 'ticket', 'visual_only', 'none', NULL, 0, UNIX_TIMESTAMP()),
  (153, 'Золотой билет', 'Golden Ticket / Battle Ticket', 'ticket', 'Редкий билет для боевых и турнирных событий.', '', 0, 0, 'ticket', 'visual_only', 'none', NULL, 0, UNIX_TIMESTAMP()),
  (154, 'Подарок', 'Present', 'gift_box', 'Подарочная коробка. Открывается сервером и начисляет награды через reward-flow.', 'open_gift', 0, 0, 'gift_box', 'implemented', 'none', NULL, 0, UNIX_TIMESTAMP()),
  (181, 'Тёмная пластина', 'Dread Plate', 'held_item', 'Held item: усиливает атаки тёмного типа.', 'equip_held', 0, 1, 'held_item:dark', 'implemented', 'universal', NULL, 1, UNIX_TIMESTAMP()),
  (189, 'Алмазная валюта', 'Diamond / Nugget-style currency', 'utility', 'Ценный предмет-валюта для наград и подарков.', '', 0, 0, 'currency', 'visual_only', 'none', NULL, 0, UNIX_TIMESTAMP()),
  (210, 'Муму-молоко', 'MooMoo Milk', 'vitamin', 'Боевой расходник: восстанавливает часть HP покемона.', 'battle_heal', 1, 0, 'battle_heal_percent:50', 'implemented', 'none', NULL, 0, UNIX_TIMESTAMP()),
  (217, 'Витамины PP', 'HP Up / PP vitamin', 'vitamin', 'Расходник для PP/витаминной логики. В beta безопасно восстанавливает PP.', 'pp_vitamin', 1, 0, 'battle_pp_restore;pp_vitamin', 'implemented', 'none', NULL, 0, UNIX_TIMESTAMP()),
  (219, 'Набор витаминов', 'Protein / Zinc / Vitamin item', 'vitamin', 'Расходник для развития статов. В beta безопасно восстанавливает PP.', 'pp_vitamin', 1, 0, 'battle_pp_restore;pp_vitamin', 'implemented', 'none', NULL, 0, UNIX_TIMESTAMP()),
  (234, 'Искатель предметов', 'Itemfinder / Dowsing Machine', 'utility', 'Утилитарный предмет для поиска и будущих квестов.', '', 0, 0, 'utility:itemfinder', 'visual_only', 'none', NULL, 0, UNIX_TIMESTAMP()),
  (235, 'Суперудочка', 'Super Rod / Fishing Rod item', 'utility', 'Утилитарный предмет для рыбалки и будущих квестов.', '', 0, 0, 'utility:fishing_rod', 'visual_only', 'none', NULL, 0, UNIX_TIMESTAMP()),
  (320, 'Поглощающая луковица', 'Absorb Bulb / Energy Herb', 'held_item', 'Held item: реагирует на водные атаки; точный эффект будет расширен отдельно.', 'equip_held', 0, 1, 'held_item:absorb_bulb', 'todo', 'universal', NULL, 1, UNIX_TIMESTAMP()),
  (321, 'Красивое перо', 'Pretty Wing', 'held_item', 'Коллекционный held item.', 'equip_held', 0, 1, 'held_item:wing', 'visual_only', 'universal', NULL, 1, UNIX_TIMESTAMP()),
  (322, 'Метроном', 'Metronome', 'held_item', 'Held item: должен усиливать повторяющиеся атаки; эффект будет расширен отдельно.', 'equip_held', 0, 1, 'held_item:metronome', 'todo', 'universal', NULL, 1, UNIX_TIMESTAMP()),
  (323, 'Клык дракона', 'Dragon Fang', 'held_item', 'Held item: усиливает атаки драконьего типа.', 'equip_held', 0, 1, 'held_item:dragon', 'implemented', 'universal', NULL, 1, UNIX_TIMESTAMP()),
  (325, 'Лёгкое перо', 'Swift Wing', 'held_item', 'Held item/тренировочный предмет скорости; в beta безопасно отображается.', 'equip_held', 0, 1, 'held_item:wing', 'visual_only', 'universal', NULL, 1, UNIX_TIMESTAMP()),
  (326, 'Связующая лента', 'Binding Band', 'held_item', 'Held item: должен усиливать удерживающие атаки; эффект будет расширен отдельно.', 'equip_held', 0, 1, 'held_item:binding', 'todo', 'universal', NULL, 1, UNIX_TIMESTAMP()),
  (327, 'Звёздная пыль', 'Stardust', 'utility', 'Ценный предмет для продажи, наград и будущих квестов.', '', 0, 0, 'currency:stardust', 'visual_only', 'none', NULL, 0, UNIX_TIMESTAMP()),
  (328, 'Шоковый привод', 'Shock Drive', 'held_item', 'Held item: усиливает электрические механики.', 'equip_held', 0, 1, 'held_item:electric', 'implemented', 'universal', NULL, 1, UNIX_TIMESTAMP()),
  (330, 'Кейс тренера', 'Pokegear / Trainer Case', 'utility', 'Утилитарный предмет профиля тренера.', '', 0, 0, 'utility:trainer_case', 'visual_only', 'none', NULL, 0, UNIX_TIMESTAMP()),
  (332, 'Лунный камень', 'Moon Stone', 'evolution', 'Предмет эволюции. Может отображаться в инвентаре и участвовать в правилах evolve.', 'evolution_item', 0, 0, 'evolution_item:moon_stone', 'implemented', 'none', NULL, 0, UNIX_TIMESTAMP()),
  (333, 'Пламенная сфера', 'Flame Orb', 'held_item', 'Held item: должен накладывать ожог на владельца; эффект будет расширен отдельно.', 'equip_held', 0, 1, 'held_item:flame_orb', 'todo', 'universal', NULL, 1, UNIX_TIMESTAMP()),
  (337, 'Волшебная пластина', 'Pixie Plate / Fairy Gem', 'held_item', 'Held item: усиливает атаки fairy-типа.', 'equip_held', 0, 1, 'held_item:fairy', 'implemented', 'universal', NULL, 1, UNIX_TIMESTAMP()),
  (338, 'Туман снов', 'Dream Mist', 'utility', 'Ивентовый предмет для будущих механик сна.', '', 0, 0, 'utility:dream_mist', 'visual_only', 'none', NULL, 0, UNIX_TIMESTAMP()),
  (339, 'Твёрдый камень', 'Hard Stone', 'held_item', 'Held item: усиливает атаки каменного типа.', 'equip_held', 0, 1, 'held_item:rock', 'implemented', 'universal', NULL, 1, UNIX_TIMESTAMP()),
  (342, 'Чёрный пояс', 'Black Belt', 'held_item', 'Held item: усиливает атаки боевого типа.', 'equip_held', 0, 1, 'held_item:fighting', 'implemented', 'universal', NULL, 1, UNIX_TIMESTAMP()),
  (344, 'Капля души', 'Soul Dew', 'held_item', 'Held item только для Latios/Latias: усиливает dragon/psychic-атаки.', 'equip_held', 0, 1, 'held_item:soul_dew', 'implemented', 'latios_latias', '{"base_ids":[380,381]}', 1, UNIX_TIMESTAMP()),
  (349, 'Согнутая ложка', 'Twisted Spoon', 'held_item', 'Held item: усиливает атаки психического типа.', 'equip_held', 0, 1, 'held_item:psychic', 'implemented', 'universal', NULL, 1, UNIX_TIMESTAMP()),
  (350, 'Древесный уголь', 'Charcoal', 'held_item', 'Held item: усиливает атаки огненного типа.', 'equip_held', 0, 1, 'held_item:fire', 'implemented', 'universal', NULL, 1, UNIX_TIMESTAMP()),
  (356, 'Механическая деталь', 'Screw / Mechanical item', 'utility', 'Технический предмет для будущих рецептов и квестов.', '', 0, 0, 'utility:craft', 'visual_only', 'none', NULL, 0, UNIX_TIMESTAMP()),
  (358, 'Толстая кость', 'Thick Club', 'held_item', 'Held item только для Cubone/Marowak: удваивает Attack.', 'equip_held', 0, 1, 'held_item:thick_club', 'implemented', 'cubone_marowak', '{"base_ids":[104,105]}', 1, UNIX_TIMESTAMP()),
  (360, 'Улучшение', 'Up-Grade', 'evolution', 'Held/evolution item для Porygon-линейки.', 'equip_held', 0, 1, 'held_item:upgrade;evolution_item', 'visual_only', 'universal', NULL, 1, UNIX_TIMESTAMP()),
  (361, 'Кольцевая цель', 'Ring Target', 'held_item', 'Held item: меняет взаимодействие типов; эффект будет расширен отдельно.', 'equip_held', 0, 1, 'held_item:ring_target', 'todo', 'universal', NULL, 1, UNIX_TIMESTAMP()),
  (365, 'Сомнительный диск', 'Dubious Disc', 'evolution', 'Held/evolution item для Porygon-линейки.', 'equip_held', 0, 1, 'held_item:dubious_disc;evolution_item', 'visual_only', 'universal', NULL, 1, UNIX_TIMESTAMP()),
  (366, 'Красный шарф', 'Red Scarf', 'held_item', 'Held item для конкурсов/скорости; в beta визуальный.', 'equip_held', 0, 1, 'held_item:scarf', 'visual_only', 'universal', NULL, 1, UNIX_TIMESTAMP()),
  (370, 'Фокус-бэнд', 'Focus Band', 'held_item', 'Held item: шанс пережить удар; эффект будет расширен отдельно.', 'equip_held', 0, 1, 'held_item:focus_band', 'todo', 'universal', NULL, 1, UNIX_TIMESTAMP()),
  (371, 'Увеличитель линзы', 'Scope Lens', 'held_item', 'Held item: повышает шанс критического удара.', 'equip_held', 0, 1, 'held_item:scope_lens', 'implemented', 'universal', NULL, 1, UNIX_TIMESTAMP()),
  (372, 'Метроном', 'Metronome', 'held_item', 'Held item: должен усиливать повторяющиеся атаки; эффект будет расширен отдельно.', 'equip_held', 0, 1, 'held_item:metronome', 'todo', 'universal', NULL, 1, UNIX_TIMESTAMP()),
  (373, 'Железный шар', 'Iron Ball', 'held_item', 'Held item: снижает скорость владельца.', 'equip_held', 0, 1, 'held_item:iron_ball', 'implemented', 'universal', NULL, 1, UNIX_TIMESTAMP()),
  (374, 'Металлическая пудра', 'Metal Powder', 'held_item', 'Held item для Ditto; эффект будет расширен отдельно.', 'equip_held', 0, 1, 'held_item:metal_powder', 'todo', 'universal', NULL, 1, UNIX_TIMESTAMP()),
  (375, 'Пушистый хвост', 'Fluffy Tail / Yarn Ball', 'utility', 'Утилитарный предмет для побега/квестов; battle-эффект будет расширен отдельно.', '', 0, 0, 'utility:fluffy_tail', 'visual_only', 'none', NULL, 0, UNIX_TIMESTAMP()),
  (376, 'Мягкий песок', 'Soft Sand', 'held_item', 'Held item: усиливает земляные атаки.', 'equip_held', 0, 1, 'held_item:ground', 'implemented', 'universal', NULL, 1, UNIX_TIMESTAMP()),
  (377, 'Нетающий лёд', 'Never-Melt Ice', 'held_item', 'Held item: усиливает ледяные атаки.', 'equip_held', 0, 1, 'held_item:ice', 'implemented', 'universal', NULL, 1, UNIX_TIMESTAMP()),
  (378, 'Ледяная пластина', 'Icicle Plate', 'held_item', 'Held item: усиливает ледяные атаки.', 'equip_held', 0, 1, 'held_item:ice', 'implemented', 'universal', NULL, 1, UNIX_TIMESTAMP()),
  (380, 'Ледяной самоцвет', 'Ice Gem', 'held_item', 'Held item: усиливает ледяные атаки сильнее обычного усилителя.', 'equip_held', 0, 1, 'held_item:ice_gem', 'implemented', 'universal', NULL, 1, UNIX_TIMESTAMP()),
  (386, 'Выборочные очки', 'Choice Specs', 'held_item', 'Held item: усиливает special-атаки.', 'equip_held', 0, 1, 'held_item:choice_specs', 'implemented', 'universal', NULL, 1, UNIX_TIMESTAMP()),
  (489, 'Глина света', 'Light Clay', 'held_item', 'Held item: продлевает экраны; эффект будет расширен отдельно.', 'equip_held', 0, 1, 'held_item:light_clay', 'todo', 'universal', NULL, 1, UNIX_TIMESTAMP()),
  (491, 'Шёлковый шарф', 'Silk Scarf', 'held_item', 'Held item: усиливает normal-атаки.', 'equip_held', 0, 1, 'held_item:normal', 'implemented', 'universal', NULL, 1, UNIX_TIMESTAMP()),
  (492, 'Широкая линза', 'Wide Lens', 'held_item', 'Held item: повышает точность атак.', 'equip_held', 0, 1, 'held_item:wide_lens', 'implemented', 'universal', NULL, 1, UNIX_TIMESTAMP()),
  (496, 'Вечный камень', 'Everstone', 'held_item', 'Held item: должен блокировать эволюцию; эффект будет расширен отдельно.', 'equip_held', 0, 1, 'held_item:everstone', 'todo', 'universal', NULL, 1, UNIX_TIMESTAMP()),
  (517, 'Штурмовой жилет', 'Assault Vest', 'held_item', 'Held item: повышает спец. защиту и блокирует статусные атаки.', 'equip_held', 0, 1, 'held_item:assault_vest', 'implemented', 'universal', NULL, 1, UNIX_TIMESTAMP()),
  (520, 'Защитные очки', 'Safety Goggles', 'held_item', 'Held item: должен защищать от погоды и порошков; эффект будет расширен отдельно.', 'equip_held', 0, 1, 'held_item:safety_goggles', 'todo', 'universal', NULL, 1, UNIX_TIMESTAMP()),
  (90200, 'Синяя сфера', 'Blue Orb', 'held_item', 'Held item только для Kyogre: включает Primal Reversion в бою.', 'equip_held', 0, 1, 'held_item:blue_orb;battle_transform:primal_kyogre', 'implemented', 'kyogre', '{"base_ids":[382]}', 1, UNIX_TIMESTAMP()),
  (90201, 'Красная сфера', 'Red Orb', 'held_item', 'Held item только для Groudon: включает Primal Reversion в бою.', 'equip_held', 0, 1, 'held_item:red_orb;battle_transform:primal_groudon', 'implemented', 'groudon', '{"base_ids":[383]}', 1, UNIX_TIMESTAMP())
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

INSERT INTO items (id, cools, name, tittle, category, uses, dress, delet, torg, elementary, timesnapoke, times, battleuse, dopolnen)
SELECT
  m.item_id,
  0,
  m.ru_name,
  m.description,
  CASE m.category_key
    WHEN 'gift_box' THEN 8
    WHEN 'ticket' THEN 10
    ELSE 12
  END,
  CASE WHEN m.category_key IN ('held_item', 'gift_box', 'evolution', 'vitamin') THEN 1 ELSE 0 END,
  m.equipuse_flag,
  1,
  1,
  0,
  0,
  '0',
  m.battleuse_flag,
  COALESCE(NULLIF(m.effect_key, ''), '0')
FROM item_gameplay_metadata m
WHERE m.item_id IN (78,80,81,82,83,85,86,87,88,89,90,91,92,93,147,152,153,154,181,189,210,217,219,234,235,320,321,322,323,325,326,327,328,330,332,333,337,338,339,342,344,349,350,356,358,360,361,365,366,370,371,372,373,374,375,376,377,378,380,386,489,491,492,496,517,520)
  AND NOT EXISTS (SELECT 1 FROM items i WHERE i.id = m.item_id);

UPDATE items i
INNER JOIN item_gameplay_metadata m ON m.item_id = i.id
   SET i.name = m.ru_name,
       i.tittle = m.description,
       i.category = CASE m.category_key WHEN 'gift_box' THEN 8 WHEN 'ticket' THEN 10 ELSE 12 END,
       i.uses = CASE WHEN m.category_key IN ('held_item', 'gift_box', 'evolution', 'vitamin') THEN 1 ELSE 0 END,
       i.dress = m.equipuse_flag,
       i.delet = 1,
       i.torg = 1,
       i.timesnapoke = 0,
       i.battleuse = m.battleuse_flag,
       i.dopolnen = COALESCE(NULLIF(m.effect_key, ''), i.dopolnen)
 WHERE m.item_id IN (80,81,82,83,85,86,87,88,89,90,91,92,93,147,152,153,154,181,189,210,217,219,234,235,320,321,322,323,325,326,327,328,330,332,333,337,338,339,342,344,349,350,356,358,360,361,365,366,370,371,372,373,374,375,376,377,378,380,386,489,491,492,496,517,520);

-- ID 78 is deliberately left as Random TM in runtime because existing TM learning and market logic depends on it.
UPDATE items i
INNER JOIN item_gameplay_metadata m ON m.item_id = i.id
   SET i.tittle = m.description,
       i.battleuse = m.battleuse_flag,
       i.timesnapoke = 0
 WHERE m.item_id = 78;

INSERT INTO item_target_rules (item_id, enabled, target_type, allow_quantity, min_count, max_count, effect_key, consume_on_success, ui_title, ui_hint, created_at, updated_at)
SELECT m.item_id, 1, 'pokemon', 0, 1, 1, 'equip_held', 1, 'Дать предмет', 'Закрепляет предмет за выбранным покемоном.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
FROM item_gameplay_metadata m
WHERE m.target_use_rule = 'equip_held'
  AND NOT EXISTS (SELECT 1 FROM item_target_rules r WHERE r.item_id = m.item_id);

INSERT INTO item_target_rules (item_id, enabled, target_type, allow_quantity, min_count, max_count, effect_key, consume_on_success, ui_title, ui_hint, created_at, updated_at)
SELECT m.item_id, 1, 'gift', 0, 1, 1, 'open_gift', 1, 'Открыть подарок', 'Открывает коробку и начисляет награды.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
FROM item_gameplay_metadata m
WHERE m.target_use_rule = 'open_gift'
ON DUPLICATE KEY UPDATE
  enabled = VALUES(enabled),
  target_type = VALUES(target_type),
  effect_key = VALUES(effect_key),
  consume_on_success = VALUES(consume_on_success),
  ui_title = VALUES(ui_title),
  ui_hint = VALUES(ui_hint),
  updated_at = VALUES(updated_at);
