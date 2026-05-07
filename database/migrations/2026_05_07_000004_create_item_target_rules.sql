-- Rules for inventory items that need a concrete target before use.
-- This keeps UI behavior DB-driven: item_id decides whether the Pokemon target menu is rendered.

CREATE TABLE IF NOT EXISTS item_target_rules (
    item_id INT NOT NULL,
    enabled TINYINT NOT NULL DEFAULT 1,
    target_type VARCHAR(32) NOT NULL DEFAULT 'pokemon',
    allow_quantity TINYINT NOT NULL DEFAULT 1,
    min_count INT NOT NULL DEFAULT 1,
    max_count INT NOT NULL DEFAULT 99,
    effect_key VARCHAR(64) NOT NULL DEFAULT 'pending',
    consume_on_success TINYINT NOT NULL DEFAULT 1,
    ui_title VARCHAR(120) NOT NULL DEFAULT 'Применить предмет',
    ui_hint VARCHAR(255) NOT NULL DEFAULT '',
    created_at INT NOT NULL DEFAULT 0,
    updated_at INT NOT NULL DEFAULT 0,
    PRIMARY KEY (item_id),
    KEY idx_item_target_rules_enabled (enabled, target_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO item_target_rules (
    item_id, enabled, target_type, allow_quantity, min_count, max_count,
    effect_key, consume_on_success, ui_title, ui_hint, created_at, updated_at
)
SELECT seed.item_id, 1, 'pokemon', seed.allow_quantity, 1, seed.max_count,
       'pending', 1, seed.ui_title, seed.ui_hint, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
FROM (
    SELECT 64 AS item_id, 0 AS allow_quantity, 1 AS max_count, 'Эволюция покемона' AS ui_title, 'Выберите покемона, к которому применить Камень рассвета.' AS ui_hint UNION ALL
    SELECT 66, 0, 1, 'Эволюция покемона', 'Выберите покемона, к которому применить Сумрачный камень.' UNION ALL
    SELECT 67, 0, 1, 'Эволюция покемона', 'Выберите покемона, к которому применить Огненный камень.' UNION ALL
    SELECT 68, 0, 1, 'Эволюция покемона', 'Выберите покемона, к которому применить Листовой камень.' UNION ALL
    SELECT 74, 0, 1, 'Эволюция покемона', 'Выберите покемона, к которому применить Солнечный камень.' UNION ALL
    SELECT 75, 0, 1, 'Эволюция покемона', 'Выберите покемона, к которому применить Громовой камень.' UNION ALL
    SELECT 76, 0, 1, 'Эволюция покемона', 'Выберите покемона, к которому применить Ледяной камень.' UNION ALL
    SELECT 77, 0, 1, 'Эволюция покемона', 'Выберите покемона, к которому применить Водяной камень.' UNION ALL
    SELECT 78, 0, 1, 'Обучение атаке', 'Выберите покемона для случайной TM-атаки.' UNION ALL
    SELECT 217, 1, 99, 'Витамины PP', 'Выберите покемона и количество витаминов PP.' UNION ALL
    SELECT 325, 1, 99, 'Лист разума', 'Выберите покемона и количество листьев разума.' UNION ALL
    SELECT 371, 0, 1, 'Удерживаемый эффект', 'Выберите покемона для увеличителя линзы.' UNION ALL
    SELECT 651, 1, 99, 'Конфета покемону', 'Выберите покемона и количество красных конфет.' UNION ALL
    SELECT 652, 1, 99, 'Конфета покемону', 'Выберите покемона и количество розовых конфет.' UNION ALL
    SELECT 653, 1, 99, 'Конфета покемону', 'Выберите покемона и количество шоколадных конфет.' UNION ALL
    SELECT 654, 1, 99, 'Конфета покемону', 'Выберите покемона и количество серых конфет.' UNION ALL
    SELECT 655, 1, 99, 'Конфета покемону', 'Выберите покемона и количество необычных конфет.' UNION ALL
    SELECT 861, 1, 99, 'Корм покемону', 'Выберите покемона и количество кексов с ягодами Церуго.' UNION ALL
    SELECT 1401, 1, 99, 'Золотая конфета', 'Выберите покемона и количество золотых конфет.'
) AS seed
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
