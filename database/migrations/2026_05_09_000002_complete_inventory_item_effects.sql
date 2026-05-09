-- Complete beta item target-use rules for inventory and battle usage.
-- Idempotent: updates existing rows and inserts missing rules without deleting player data.

INSERT INTO item_target_rules
    (item_id, enabled, target_type, allow_quantity, min_count, max_count, effect_key, consume_on_success, ui_title, ui_hint, created_at, updated_at)
VALUES
    (6, 1, 'pokemon', 1, 1, 99, 'exp_candy', 1, 'Дать конфету', 'Повышает уровень выбранного покемона.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    (7, 1, 'pokemon', 1, 1, 99, 'exp_candy', 1, 'Дать конфету', 'Повышает уровень выбранного покемона.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    (8, 1, 'pokemon', 1, 1, 99, 'exp_candy', 1, 'Дать конфету', 'Повышает уровень выбранного покемона.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    (9, 1, 'pokemon', 1, 1, 99, 'exp_candy', 1, 'Дать конфету', 'Повышает уровень выбранного покемона.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    (10, 1, 'pokemon', 1, 1, 99, 'exp_candy', 1, 'Дать конфету', 'Повышает уровень выбранного покемона.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    (11, 1, 'pokemon', 1, 1, 99, 'exp_candy', 1, 'Дать конфету', 'Повышает уровень выбранного покемона.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    (12, 1, 'pokemon', 0, 1, 1, 'nature_neutral', 1, 'Стабилизировать характер', 'Меняет характер покемона на обычный и пересчитывает характеристики.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    (17, 1, 'pokemon', 1, 1, 99, 'exp_candy', 1, 'Дать конфету', 'Повышает уровень выбранного покемона.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    (18, 1, 'pokemon', 1, 1, 24, 'boost_exp', 1, 'Активировать буст опыта', 'Включает персональный бонус опыта.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    (19, 1, 'pokemon', 1, 1, 24, 'boost_exp', 1, 'Активировать буст опыта', 'Включает персональный бонус опыта.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    (20, 1, 'pokemon', 1, 1, 24, 'boost_exp', 1, 'Активировать буст опыта', 'Включает персональный бонус опыта.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    (40, 1, 'pokemon', 0, 1, 1, 'evolution_item', 1, 'Эволюция камнем', 'Подходит только покемонам с этой эволюцией.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    (41, 1, 'pokemon', 0, 1, 1, 'evolution_item', 1, 'Эволюция камнем', 'Подходит только покемонам с этой эволюцией.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    (42, 1, 'pokemon', 0, 1, 1, 'evolution_item', 1, 'Эволюция камнем', 'Подходит только покемонам с этой эволюцией.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    (43, 1, 'pokemon', 0, 1, 1, 'evolution_item', 1, 'Эволюция камнем', 'Подходит только покемонам с этой эволюцией.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    (44, 1, 'pokemon', 0, 1, 1, 'evolution_item', 1, 'Эволюция камнем', 'Подходит только покемонам с этой эволюцией.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    (61, 1, 'pokemon', 1, 1, 24, 'boost_drop', 1, 'Активировать буст дропа', 'Включает персональный бонус дропа.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    (62, 1, 'pokemon', 1, 1, 24, 'boost_drop', 1, 'Активировать буст дропа', 'Включает персональный бонус дропа.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    (63, 1, 'pokemon', 1, 1, 24, 'boost_money', 1, 'Активировать буст монет', 'Включает персональный бонус монет.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    (64, 1, 'pokemon', 0, 1, 1, 'evolution_item', 1, 'Эволюция камнем', 'Подходит только покемонам с этой эволюцией.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    (66, 1, 'pokemon', 0, 1, 1, 'evolution_item', 1, 'Эволюция камнем', 'Подходит только покемонам с этой эволюцией.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    (67, 1, 'pokemon', 0, 1, 1, 'evolution_item', 1, 'Эволюция камнем', 'Подходит только покемонам с этой эволюцией.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    (68, 1, 'pokemon', 0, 1, 1, 'evolution_item', 1, 'Эволюция камнем', 'Подходит только покемонам с этой эволюцией.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    (74, 1, 'pokemon', 0, 1, 1, 'evolution_item', 1, 'Эволюция камнем', 'Подходит только покемонам с этой эволюцией.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    (75, 1, 'pokemon', 0, 1, 1, 'evolution_item', 1, 'Эволюция камнем', 'Подходит только покемонам с этой эволюцией.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    (76, 1, 'pokemon', 0, 1, 1, 'evolution_item', 1, 'Эволюция камнем', 'Подходит только покемонам с этой эволюцией.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    (77, 1, 'pokemon', 0, 1, 1, 'evolution_item', 1, 'Эволюция камнем', 'Подходит только покемонам с этой эволюцией.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    (78, 1, 'pokemon', 0, 1, 1, 'tm_learn', 1, 'Изучить TM', 'Покемон изучит одну доступную атаку из своей таблицы обучения.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    (217, 1, 'pokemon', 0, 1, 1, 'pp_vitamin', 1, 'Восстановить PP', 'Полностью восстанавливает PP атак выбранного покемона.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    (325, 1, 'pokemon', 1, 1, 24, 'boost_exp', 1, 'Активировать буст опыта', 'Включает персональный бонус опыта.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    (330, 1, 'pokemon', 0, 1, 1, 'training_train', 1, 'Тренировать покемона', 'Пытается повысить стадию тренировки.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    (333, 1, 'pokemon', 0, 1, 1, 'equip_held', 1, 'Дать предмет', 'Закрепляет предмет за выбранным покемоном.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    (371, 1, 'pokemon', 0, 1, 1, 'equip_held', 1, 'Дать предмет', 'Закрепляет предмет за выбранным покемоном.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    (651, 1, 'pokemon', 1, 1, 99, 'exp_candy', 1, 'Дать конфету', 'Повышает уровень выбранного покемона.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    (652, 1, 'pokemon', 1, 1, 99, 'exp_candy', 1, 'Дать конфету', 'Повышает уровень выбранного покемона.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    (653, 1, 'pokemon', 1, 1, 99, 'exp_candy', 1, 'Дать конфету', 'Повышает уровень выбранного покемона.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    (654, 1, 'pokemon', 1, 1, 99, 'exp_candy', 1, 'Дать конфету', 'Повышает уровень выбранного покемона.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    (655, 1, 'pokemon', 1, 1, 99, 'exp_candy', 1, 'Дать конфету', 'Повышает уровень выбранного покемона.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    (678, 1, 'pokemon', 0, 1, 1, 'training_weaken', 1, 'Ослабить тренировку', 'Понижает стадию тренировки для реролла.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    (861, 1, 'pokemon', 1, 1, 99, 'exp_candy', 1, 'Дать кекс', 'Повышает уровень выбранного покемона.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    (1401, 1, 'pokemon', 1, 1, 99, 'exp_candy', 1, 'Дать золотую конфету', 'Сильно повышает уровень выбранного покемона.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
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

UPDATE items
   SET battleuse = 1
 WHERE id IN (15, 217, 861)
   AND battleuse <> 1;
