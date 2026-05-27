-- Plan items, gift boxes and core legendary learnsets.
-- Idempotent: preserves existing gameplay rules and only fills missing records/rules.

CREATE TABLE IF NOT EXISTS item_gift_loot (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  gift_item_id INT NOT NULL,
  reward_item_id INT NOT NULL,
  min_count INT NOT NULL DEFAULT 1,
  max_count INT NOT NULL DEFAULT 1,
  chance_bps INT NOT NULL DEFAULT 10000,
  guaranteed TINYINT NOT NULL DEFAULT 0,
  enabled TINYINT NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0,
  created_at INT NOT NULL DEFAULT 0,
  updated_at INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY uq_item_gift_loot (gift_item_id, reward_item_id),
  KEY idx_item_gift_loot_gift (gift_item_id, enabled, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS gym_badges (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  badge_key VARCHAR(64) NOT NULL,
  title VARCHAR(120) NOT NULL,
  leader_name VARCHAR(120) NOT NULL DEFAULT '',
  location_id INT NOT NULL DEFAULT 0,
  icon_item_id INT NOT NULL DEFAULT 0,
  created_at INT NOT NULL DEFAULT 0,
  updated_at INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY uq_gym_badges_key (badge_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS user_gym_badges (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT NOT NULL,
  badge_id BIGINT UNSIGNED NOT NULL,
  source_type VARCHAR(32) NOT NULL DEFAULT 'manual',
  source_id INT NOT NULL DEFAULT 0,
  awarded_by INT NOT NULL DEFAULT 0,
  awarded_at INT NOT NULL DEFAULT 0,
  created_at INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY uq_user_gym_badges (user_id, badge_id),
  KEY idx_user_gym_badges_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO items (id, cools, name, tittle, category, uses, dress, delet, torg, elementary, timesnapoke, times, battleuse, dopolnen)
SELECT 88, 0, 'Металлическое покрытие', 'Held item: усиливает атаки стального типа. Может использоваться в механиках эволюции, если правило задано отдельно.', 12, 1, 1, 1, 1, 0, 1, '0', 0, 'held_item:steel'
FROM (SELECT 1) seed
WHERE NOT EXISTS (SELECT 1 FROM items WHERE id = 88);
INSERT INTO items (id, cools, name, tittle, category, uses, dress, delet, torg, elementary, timesnapoke, times, battleuse, dopolnen)
SELECT 90, 0, 'Согнутая ложка', 'Held item: усиливает атаки психического типа.', 12, 1, 1, 1, 1, 0, 1, '0', 0, 'held_item:psychic'
FROM (SELECT 1) seed
WHERE NOT EXISTS (SELECT 1 FROM items WHERE id = 90);
INSERT INTO items (id, cools, name, tittle, category, uses, dress, delet, torg, elementary, timesnapoke, times, battleuse, dopolnen)
SELECT 91, 0, 'Ядовитый шип', 'Held item: усиливает атаки ядовитого типа.', 12, 1, 1, 1, 1, 0, 1, '0', 0, 'held_item:poison'
FROM (SELECT 1) seed
WHERE NOT EXISTS (SELECT 1 FROM items WHERE id = 91);
INSERT INTO items (id, cools, name, tittle, category, uses, dress, delet, torg, elementary, timesnapoke, times, battleuse, dopolnen)
SELECT 92, 0, 'Браслет силы', 'Held item: тренировочный предмет. В beta работает как визуальный held item.', 12, 1, 1, 1, 1, 0, 1, '0', 0, 'held_item:training'
FROM (SELECT 1) seed
WHERE NOT EXISTS (SELECT 1 FROM items WHERE id = 92);
INSERT INTO items (id, cools, name, tittle, category, uses, dress, delet, torg, elementary, timesnapoke, times, battleuse, dopolnen)
SELECT 93, 0, 'Клык дракона', 'Held item: усиливает атаки драконьего типа.', 12, 1, 1, 1, 1, 0, 1, '0', 0, 'held_item:dragon'
FROM (SELECT 1) seed
WHERE NOT EXISTS (SELECT 1 FROM items WHERE id = 93);
INSERT INTO items (id, cools, name, tittle, category, uses, dress, delet, torg, elementary, timesnapoke, times, battleuse, dopolnen)
SELECT 147, 0, 'Фото тренера', 'Памятный предмет для будущих наград тренер-карты.', 12, 0, 0, 1, 1, 0, 1, '0', 0, 'trainer_card'
FROM (SELECT 1) seed
WHERE NOT EXISTS (SELECT 1 FROM items WHERE id = 147);
INSERT INTO items (id, cools, name, tittle, category, uses, dress, delet, torg, elementary, timesnapoke, times, battleuse, dopolnen)
SELECT 152, 0, 'Покебилет', 'Билет для транспортных и ивентовых механик.', 12, 0, 0, 1, 1, 0, 1, '0', 0, 'ticket'
FROM (SELECT 1) seed
WHERE NOT EXISTS (SELECT 1 FROM items WHERE id = 152);
INSERT INTO items (id, cools, name, tittle, category, uses, dress, delet, torg, elementary, timesnapoke, times, battleuse, dopolnen)
SELECT 153, 0, 'Золотой билет', 'Редкий билет для боевых и турнирных событий.', 12, 0, 0, 1, 1, 0, 1, '0', 0, 'ticket'
FROM (SELECT 1) seed
WHERE NOT EXISTS (SELECT 1 FROM items WHERE id = 153);
INSERT INTO items (id, cools, name, tittle, category, uses, dress, delet, torg, elementary, timesnapoke, times, battleuse, dopolnen)
SELECT 154, 0, 'Подарок', 'Подарочная коробка. Открой, чтобы получить набор предметов.', 8, 1, 0, 1, 1, 0, 1, '0', 0, 'gift_box'
FROM (SELECT 1) seed
WHERE NOT EXISTS (SELECT 1 FROM items WHERE id = 154);
INSERT INTO items (id, cools, name, tittle, category, uses, dress, delet, torg, elementary, timesnapoke, times, battleuse, dopolnen)
SELECT 181, 0, 'Тёмная пластина', 'Held item: усиливает атаки тёмного типа.', 12, 1, 1, 1, 1, 0, 1, '0', 0, 'held_item:dark'
FROM (SELECT 1) seed
WHERE NOT EXISTS (SELECT 1 FROM items WHERE id = 181);
INSERT INTO items (id, cools, name, tittle, category, uses, dress, delet, torg, elementary, timesnapoke, times, battleuse, dopolnen)
SELECT 189, 0, 'Алмазная валюта', 'Ценный предмет-валюта для наград и подарков.', 12, 0, 0, 1, 1, 0, 1, '0', 0, 'currency'
FROM (SELECT 1) seed
WHERE NOT EXISTS (SELECT 1 FROM items WHERE id = 189);
INSERT INTO items (id, cools, name, tittle, category, uses, dress, delet, torg, elementary, timesnapoke, times, battleuse, dopolnen)
SELECT 210, 0, 'Муму-молоко', 'Восстанавливает здоровье покемона в бою.', 12, 1, 0, 1, 1, 0, 1, '0', 1, 'battle_heal_percent:50'
FROM (SELECT 1) seed
WHERE NOT EXISTS (SELECT 1 FROM items WHERE id = 210);
INSERT INTO items (id, cools, name, tittle, category, uses, dress, delet, torg, elementary, timesnapoke, times, battleuse, dopolnen)
SELECT 219, 0, 'Набор витаминов', 'Расходник для будущего развития статов. В beta безопасно восстанавливает PP.', 12, 1, 0, 1, 1, 0, 1, '0', 1, 'battle_pp_restore'
FROM (SELECT 1) seed
WHERE NOT EXISTS (SELECT 1 FROM items WHERE id = 219);

INSERT INTO items (id, cools, name, tittle, category, uses, dress, delet, torg, elementary, timesnapoke, times, battleuse, dopolnen)
SELECT v.id, 0, v.name, v.title, 12, 1, 1, 1, 1, 0, 1, '0', 0, v.meta
FROM (
  SELECT 320 id, 'Поглощающая луковица' name, 'Held item: реагирует на водные атаки. В beta работает как held item.' title, 'held_item:absorb_bulb' meta UNION ALL
  SELECT 321, 'Красивое перо', 'Коллекционный held item.', 'held_item:wing' UNION ALL
  SELECT 322, 'Метроном', 'Held item: усиливает повторяющиеся атаки, эффект будет расширен отдельно.', 'held_item:metronome' UNION ALL
  SELECT 323, 'Клык дракона', 'Held item: усиливает атаки драконьего типа.', 'held_item:dragon' UNION ALL
  SELECT 326, 'Связующая лента', 'Held item: усиливает удерживающие атаки.', 'held_item:binding' UNION ALL
  SELECT 328, 'Шоковый привод', 'Held item для электрических механик.', 'held_item:electric' UNION ALL
  SELECT 337, 'Волшебная пластина', 'Held item: усиливает атаки fairy-типа.', 'held_item:fairy' UNION ALL
  SELECT 338, 'Туман снов', 'Ивентовый предмет для будущих механик сна.', 'utility:dream' UNION ALL
  SELECT 339, 'Твёрдый камень', 'Held item: усиливает атаки каменного типа.', 'held_item:rock' UNION ALL
  SELECT 342, 'Чёрный пояс', 'Held item: усиливает атаки боевого типа.', 'held_item:fighting' UNION ALL
  SELECT 344, 'Капля души', 'Held item для Latios/Latias.', 'held_item:soul_dew' UNION ALL
  SELECT 349, 'Согнутая ложка', 'Held item: усиливает атаки психического типа.', 'held_item:psychic' UNION ALL
  SELECT 350, 'Древесный уголь', 'Held item: усиливает атаки огненного типа.', 'held_item:fire' UNION ALL
  SELECT 356, 'Механическая деталь', 'Технический предмет для будущих рецептов.', 'utility:craft' UNION ALL
  SELECT 358, 'Толстая кость', 'Held item для Cubone/Marowak.', 'held_item:thick_club' UNION ALL
  SELECT 360, 'Улучшение', 'Held/evolution item для Porygon-линейки.', 'held_item:upgrade' UNION ALL
  SELECT 361, 'Кольцевая цель', 'Held item: меняет взаимодействие типов, эффект будет расширен отдельно.', 'held_item:ring_target' UNION ALL
  SELECT 365, 'Сомнительный диск', 'Held/evolution item для Porygon-линейки.', 'held_item:dubious_disc' UNION ALL
  SELECT 366, 'Красный шарф', 'Held item для конкурсов/скорости.', 'held_item:scarf' UNION ALL
  SELECT 370, 'Фокус-бэнд', 'Held item: шанс пережить удар, эффект будет расширен отдельно.', 'held_item:focus_band' UNION ALL
  SELECT 372, 'Метроном', 'Held item: усиливает повторяющиеся атаки.', 'held_item:metronome' UNION ALL
  SELECT 373, 'Железный шар', 'Held item: снижает скорость, эффект будет расширен отдельно.', 'held_item:iron_ball' UNION ALL
  SELECT 374, 'Металлическая пудра', 'Held item для Ditto.', 'held_item:metal_powder' UNION ALL
  SELECT 375, 'Пушистый хвост', 'Расходный utility item.', 'utility:fluffy_tail' UNION ALL
  SELECT 376, 'Мягкий песок', 'Held item: усиливает земляные атаки.', 'held_item:ground' UNION ALL
  SELECT 377, 'Нетающий лёд', 'Held item: усиливает ледяные атаки.', 'held_item:ice' UNION ALL
  SELECT 378, 'Ледяная пластина', 'Held item: усиливает ледяные атаки.', 'held_item:ice' UNION ALL
  SELECT 380, 'Ледяной самоцвет', 'Held item: одноразовый ледяной усилитель, эффект будет расширен отдельно.', 'held_item:ice_gem' UNION ALL
  SELECT 386, 'Выборочные очки', 'Held item: усиливает спец. атаку с ограничением выбора атаки.', 'held_item:choice_specs' UNION ALL
  SELECT 489, 'Глина света', 'Held item: продлевает экраны.', 'held_item:light_clay' UNION ALL
  SELECT 491, 'Шёлковый шарф', 'Held item: усиливает Normal-атаки.', 'held_item:normal' UNION ALL
  SELECT 492, 'Широкая линза', 'Held item: повышает точность.', 'held_item:wide_lens' UNION ALL
  SELECT 496, 'Вечный камень', 'Held item: блокирует эволюцию.', 'held_item:everstone' UNION ALL
  SELECT 517, 'Штурмовой жилет', 'Held item: повышает спец. защиту, блокируя статусные атаки.', 'held_item:assault_vest' UNION ALL
  SELECT 520, 'Защитные очки', 'Held item: защищает от погоды/порошков.', 'held_item:safety_goggles'
) v
WHERE NOT EXISTS (SELECT 1 FROM items i WHERE i.id = v.id);

INSERT INTO items (id, cools, name, tittle, category, uses, dress, delet, torg, elementary, timesnapoke, times, battleuse, dopolnen)
SELECT v.id, 0, v.name, v.title, 8, 1, 0, 1, 1, 0, 1, '0', 0, 'gift_box'
FROM (
  SELECT 90020 id, 'Боевой набор' name, 'Тестовый подарок с расходниками для боёв.' title UNION ALL
  SELECT 90021, 'Набор held items', 'Тестовый подарок с предметами для покемонов.' UNION ALL
  SELECT 90022, 'Набор эволюции', 'Тестовый подарок с камнями и предметами эволюции.' UNION ALL
  SELECT 90023, 'Праймал/Мега набор', 'Тестовый подарок со сферами и мега-камнями.' UNION ALL
  SELECT 90024, 'Премиальный тестовый подарок', 'Большой подарок для проверки наград.'
) v
WHERE NOT EXISTS (SELECT 1 FROM items i WHERE i.id = v.id);

UPDATE items SET uses = 1, dopolnen = 'gift_box' WHERE id IN (45,154,90020,90021,90022,90023,90024);

INSERT INTO item_target_rules (item_id, enabled, target_type, allow_quantity, min_count, max_count, effect_key, consume_on_success, ui_title, ui_hint, created_at, updated_at)
SELECT v.item_id, 1, 'pokemon', 0, 1, 1, 'equip_held', 1, 'Дать предмет', 'Закрепляет предмет за выбранным покемоном.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
FROM (
  SELECT 88 item_id UNION ALL SELECT 90 UNION ALL SELECT 91 UNION ALL SELECT 92 UNION ALL SELECT 93 UNION ALL SELECT 181 UNION ALL
  SELECT 320 UNION ALL SELECT 321 UNION ALL SELECT 322 UNION ALL SELECT 323 UNION ALL SELECT 326 UNION ALL SELECT 328 UNION ALL
  SELECT 337 UNION ALL SELECT 339 UNION ALL SELECT 342 UNION ALL SELECT 344 UNION ALL SELECT 349 UNION ALL SELECT 350 UNION ALL
  SELECT 358 UNION ALL SELECT 360 UNION ALL SELECT 361 UNION ALL SELECT 365 UNION ALL SELECT 366 UNION ALL SELECT 370 UNION ALL
  SELECT 372 UNION ALL SELECT 373 UNION ALL SELECT 374 UNION ALL SELECT 376 UNION ALL SELECT 377 UNION ALL SELECT 378 UNION ALL
  SELECT 380 UNION ALL SELECT 386 UNION ALL SELECT 489 UNION ALL SELECT 491 UNION ALL SELECT 492 UNION ALL SELECT 496 UNION ALL
  SELECT 517 UNION ALL SELECT 520
) v
WHERE NOT EXISTS (SELECT 1 FROM item_target_rules r WHERE r.item_id = v.item_id);

INSERT INTO item_target_rules (item_id, enabled, target_type, allow_quantity, min_count, max_count, effect_key, consume_on_success, ui_title, ui_hint, created_at, updated_at)
SELECT v.item_id, 1, 'gift', 0, 1, 1, 'open_gift', 1, 'Открыть подарок', 'Открывает коробку и начисляет награды.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
FROM (SELECT 45 item_id UNION ALL SELECT 154 UNION ALL SELECT 90020 UNION ALL SELECT 90021 UNION ALL SELECT 90022 UNION ALL SELECT 90023 UNION ALL SELECT 90024) v
ON DUPLICATE KEY UPDATE enabled = VALUES(enabled), target_type = VALUES(target_type), effect_key = VALUES(effect_key), ui_title = VALUES(ui_title), ui_hint = VALUES(ui_hint), updated_at = VALUES(updated_at);

INSERT INTO item_gift_loot (gift_item_id, reward_item_id, min_count, max_count, chance_bps, guaranteed, enabled, sort_order, created_at, updated_at)
SELECT v.gift_item_id, v.reward_item_id, v.min_count, v.max_count, v.chance_bps, v.guaranteed, 1, v.sort_order, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
FROM (
  SELECT 45 gift_item_id, 653 reward_item_id, 1 min_count, 1 max_count, 2000 chance_bps, 0 guaranteed, 10 sort_order UNION ALL
  SELECT 45, 651, 5, 15, 10000, 1, 20 UNION ALL
  SELECT 45, 654, 1, 2, 2000, 0, 30 UNION ALL
  SELECT 45, 2, 5, 25, 1500, 0, 40 UNION ALL
  SELECT 45, 75, 1, 2, 1500, 0, 50 UNION ALL
  SELECT 45, 67, 1, 2, 1500, 0, 60 UNION ALL
  SELECT 45, 77, 1, 2, 1500, 0, 70 UNION ALL
  SELECT 45, 68, 1, 2, 1500, 0, 80 UNION ALL
  SELECT 45, 332, 1, 2, 1500, 0, 90 UNION ALL
  SELECT 154, 1, 1000, 5000, 10000, 1, 10 UNION ALL
  SELECT 154, 651, 1, 3, 6000, 0, 20 UNION ALL
  SELECT 90020, 217, 3, 5, 10000, 1, 10 UNION ALL
  SELECT 90020, 210, 5, 10, 10000, 1, 20 UNION ALL
  SELECT 90020, 371, 1, 1, 4000, 0, 30 UNION ALL
  SELECT 90021, 90, 1, 1, 10000, 1, 10 UNION ALL
  SELECT 90021, 91, 1, 1, 10000, 1, 20 UNION ALL
  SELECT 90021, 93, 1, 1, 10000, 1, 30 UNION ALL
  SELECT 90021, 342, 1, 1, 8000, 0, 40 UNION ALL
  SELECT 90021, 344, 1, 1, 8000, 0, 50 UNION ALL
  SELECT 90021, 492, 1, 1, 8000, 0, 60 UNION ALL
  SELECT 90022, 64, 1, 1, 10000, 1, 10 UNION ALL
  SELECT 90022, 66, 1, 1, 10000, 1, 20 UNION ALL
  SELECT 90022, 67, 1, 1, 10000, 1, 30 UNION ALL
  SELECT 90022, 68, 1, 1, 10000, 1, 40 UNION ALL
  SELECT 90022, 75, 1, 1, 10000, 1, 50 UNION ALL
  SELECT 90023, 90200, 1, 1, 10000, 1, 10 UNION ALL
  SELECT 90023, 90201, 1, 1, 10000, 1, 20 UNION ALL
  SELECT 90023, 90202, 1, 1, 5000, 0, 30 UNION ALL
  SELECT 90023, 90203, 1, 1, 5000, 0, 40 UNION ALL
  SELECT 90024, 2, 25, 50, 10000, 1, 10 UNION ALL
  SELECT 90024, 90004, 1, 1, 10000, 1, 20 UNION ALL
  SELECT 90024, 1401, 1, 2, 10000, 1, 30
) v
ON DUPLICATE KEY UPDATE min_count = VALUES(min_count), max_count = VALUES(max_count), chance_bps = VALUES(chance_bps), guaranteed = VALUES(guaranteed), enabled = VALUES(enabled), sort_order = VALUES(sort_order), updated_at = VALUES(updated_at);

SET @next_attac_poke_id := (SELECT COALESCE(MAX(id_structure), 0) FROM attac_poke);

INSERT INTO attac_poke (id_structure, atac_id, poke_base_id, atc_lvl)
SELECT @next_attac_poke_id := @next_attac_poke_id + 1, a.atac_id, v.base_id, v.learn_level
FROM (
  SELECT 382 base_id, 'Water Spout' move_name, 1 learn_level UNION ALL
  SELECT 382, 'Origin Pulse', 1 UNION ALL
  SELECT 382, 'Ice Beam', 1 UNION ALL
  SELECT 382, 'Thunder', 1 UNION ALL
  SELECT 383, 'Precipice Blades', 1 UNION ALL
  SELECT 383, 'Fire Punch', 1 UNION ALL
  SELECT 383, 'Stone Edge', 1 UNION ALL
  SELECT 383, 'Stealth Rock', 1 UNION ALL
  SELECT 384, 'Dragon Ascent', 1 UNION ALL
  SELECT 384, 'ExtremeSpeed', 1 UNION ALL
  SELECT 384, 'Earthquake', 1 UNION ALL
  SELECT 384, 'Dragon Dance', 1 UNION ALL
  SELECT 448, 'Close Combat', 1 UNION ALL
  SELECT 448, 'Meteor Mash', 1 UNION ALL
  SELECT 448, 'ExtremeSpeed', 1 UNION ALL
  SELECT 6, 'Flare Blitz', 1 UNION ALL
  SELECT 6, 'Dragon Claw', 1 UNION ALL
  SELECT 6, 'Earthquake', 1 UNION ALL
  SELECT 6, 'Solarbeam', 1 UNION ALL
  SELECT 150, 'Psystrike', 1 UNION ALL
  SELECT 150, 'Recover', 1 UNION ALL
  SELECT 150, 'Aura Sphere', 1
) v
INNER JOIN attac_power a ON a.atac_name = v.move_name
WHERE NOT EXISTS (
  SELECT 1 FROM attac_poke existing
   WHERE existing.poke_base_id = v.base_id AND existing.atac_id = a.atac_id
);
