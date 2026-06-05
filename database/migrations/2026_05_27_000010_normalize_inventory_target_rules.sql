-- Normalize inventory target rules for beta economy QA.
-- Older legacy rows reused several held item ids as evolution buttons; current
-- runtime must follow item_gameplay_metadata as the source of truth.

INSERT INTO item_target_rules
  (item_id, enabled, target_type, allow_quantity, min_count, max_count, effect_key, consume_on_success, ui_title, ui_hint, created_at, updated_at)
SELECT item_id, 1, 'pokemon', 0, 1, 1, 'equip_held', 1, 'Дать предмет', 'Закрепляет предмет за выбранным покемоном. Старый предмет безопасно возвращается в инвентарь.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
FROM item_gameplay_metadata
WHERE target_use_rule = 'equip_held'
ON DUPLICATE KEY UPDATE
  enabled = 1,
  target_type = 'pokemon',
  allow_quantity = 0,
  min_count = 1,
  max_count = 1,
  effect_key = 'equip_held',
  consume_on_success = 1,
  ui_title = 'Дать предмет',
  ui_hint = 'Закрепляет предмет за выбранным покемоном. Старый предмет безопасно возвращается в инвентарь.',
  updated_at = UNIX_TIMESTAMP();

INSERT INTO item_target_rules
  (item_id, enabled, target_type, allow_quantity, min_count, max_count, effect_key, consume_on_success, ui_title, ui_hint, created_at, updated_at)
SELECT item_id, 1, 'pokemon', 0, 1, 1, 'tm_learn', 1, 'Обучить ТМ', 'Покемон изучает доступную атаку. При ошибке предмет не списывается.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
FROM item_gameplay_metadata
WHERE target_use_rule = 'tm_learn'
ON DUPLICATE KEY UPDATE
  enabled = 1,
  target_type = 'pokemon',
  allow_quantity = 0,
  min_count = 1,
  max_count = 1,
  effect_key = 'tm_learn',
  consume_on_success = 1,
  ui_title = 'Обучить ТМ',
  ui_hint = 'Покемон изучает доступную атаку. При ошибке предмет не списывается.',
  updated_at = UNIX_TIMESTAMP();

INSERT INTO item_target_rules
  (item_id, enabled, target_type, allow_quantity, min_count, max_count, effect_key, consume_on_success, ui_title, ui_hint, created_at, updated_at)
SELECT item_id, 1, 'pokemon', 0, 1, 1, 'pp_vitamin', 1, 'Восстановить PP', 'Полностью восстанавливает PP атак выбранного покемона.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
FROM item_gameplay_metadata
WHERE target_use_rule = 'pp_vitamin'
ON DUPLICATE KEY UPDATE
  enabled = 1,
  target_type = 'pokemon',
  allow_quantity = 0,
  min_count = 1,
  max_count = 1,
  effect_key = 'pp_vitamin',
  consume_on_success = 1,
  ui_title = 'Восстановить PP',
  ui_hint = 'Полностью восстанавливает PP атак выбранного покемона.',
  updated_at = UNIX_TIMESTAMP();

INSERT INTO item_target_rules
  (item_id, enabled, target_type, allow_quantity, min_count, max_count, effect_key, consume_on_success, ui_title, ui_hint, created_at, updated_at)
SELECT item_id, 1, 'pokemon', 0, 1, 1, 'evolution_item', 1, 'Эволюция предметом', 'Подходит только покемонам с этой эволюцией. При ошибке предмет не списывается.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
FROM item_gameplay_metadata
WHERE target_use_rule = 'evolution_item'
ON DUPLICATE KEY UPDATE
  enabled = 1,
  target_type = 'pokemon',
  allow_quantity = 0,
  min_count = 1,
  max_count = 1,
  effect_key = 'evolution_item',
  consume_on_success = 1,
  ui_title = 'Эволюция предметом',
  ui_hint = 'Подходит только покемонам с этой эволюцией. При ошибке предмет не списывается.',
  updated_at = UNIX_TIMESTAMP();

INSERT INTO item_target_rules
  (item_id, enabled, target_type, allow_quantity, min_count, max_count, effect_key, consume_on_success, ui_title, ui_hint, created_at, updated_at)
SELECT item_id, 1, 'gift', 0, 1, 1, 'open_gift', 1, 'Открыть подарок', 'Открывает коробку и начисляет награды через сервер.', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
FROM item_gameplay_metadata
WHERE target_use_rule = 'open_gift'
ON DUPLICATE KEY UPDATE
  enabled = 1,
  target_type = 'gift',
  allow_quantity = 0,
  min_count = 1,
  max_count = 1,
  effect_key = 'open_gift',
  consume_on_success = 1,
  ui_title = 'Открыть подарок',
  ui_hint = 'Открывает коробку и начисляет награды через сервер.',
  updated_at = UNIX_TIMESTAMP();
