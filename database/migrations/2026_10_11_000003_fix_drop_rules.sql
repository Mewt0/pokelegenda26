-- Починка дропа: предмет №5 оказался «Почтовым подарком», а не лечебным, перья
-- висели глобально с шансом 85%, редких предметов не было ни одного.
-- Проверка после применения: tools/drops_audit.php

SET NAMES utf8mb4;

-- №5 «Почтовый подарок» не лечебный: заменяем на энергетик (№15), он и по смыслу
-- подходит как расходник из диких боёв.
UPDATE admin_drop_rules SET item_id = 15, note = 'energy' WHERE item_id = 5 AND source_type = 'wild';

-- Перья — квестовые предметы (квест #7 «Перья для Билли» просит 10+10). Глобальный
-- шанс 85% на всей карте ломал и квест, и экономику: привязываем к лесу, где
-- встречаются Pidgeotto и Spearow, и снижаем до разумного.
UPDATE admin_drop_rules SET location_id = 5, note = 'quest feather (forest)', chance_percent = 22.0000 WHERE item_id = 13 AND location_id = 0;
UPDATE admin_drop_rules SET location_id = 5, note = 'quest feather (forest)', chance_percent = 22.0000 WHERE item_id = 14 AND location_id = 0;

-- Редкие предметы (1–3%) и джекпот (<=0.5%): эволюционные камни и «Уникальный подарок».
INSERT IGNORE INTO admin_drop_rules (item_id, location_id, pokebuild_id, pokemon_base_id, source_type, chance_percent, min_count, max_count, time_start, time_end, quest_id, quest_process, quest_complete, enabled, note, created_by, created_at, updated_at)
VALUES
    (67, 13, 0, 0, 'wild', 1.5000, 1, 1, '00:00:00', '23:59:59', 0, 0, 0, 1, 'rare: fire stone (cliffs)', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    (68, 7, 0, 0, 'wild', 1.5000, 1, 1, '00:00:00', '23:59:59', 0, 0, 0, 1, 'rare: leaf stone (dark forest)', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    (66, 8, 0, 0, 'wild', 1.5000, 1, 1, '00:00:00', '23:59:59', 0, 0, 0, 1, 'rare: dusk stone (cave)', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    (64, 14, 0, 0, 'wild', 1.0000, 1, 1, '00:00:00', '23:59:59', 0, 0, 0, 1, 'rare: dawn stone (mountain pass)', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    (41, 33, 0, 0, 'wild', 1.0000, 1, 1, '00:00:00', '23:59:59', 0, 0, 0, 1, 'rare: fire stone (ecruteak cave)', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    (45, 8, 0, 0, 'wild', 0.4000, 1, 1, '20:00:00', '05:00:00', 0, 0, 0, 1, 'jackpot: unique gift (cave, night)', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    (45, 13, 0, 0, 'wild', 0.4000, 1, 1, '20:00:00', '05:00:00', 0, 0, 0, 1, 'jackpot: unique gift (cliffs, night)', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    (17, 27, 0, 0, 'wild', 0.5000, 1, 1, '00:00:00', '23:59:59', 0, 0, 0, 1, 'jackpot: chocolate candy (beach)', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());
