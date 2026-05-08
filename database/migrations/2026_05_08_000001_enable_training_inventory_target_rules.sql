INSERT INTO item_target_rules (
    item_id, enabled, target_type, allow_quantity, min_count, max_count,
    effect_key, consume_on_success, ui_title, ui_hint, created_at, updated_at
)
VALUES
    (
        330, 1, 'pokemon', 0, 1, 1,
        'training_train', 1,
        'Провести тренировку',
        'Выберите покемона. Будет потрачен один набор тренировки.',
        UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
    ),
    (
        678, 1, 'pokemon', 0, 1, 1,
        'training_weaken', 1,
        'Ослабить тренировку',
        'Выберите покемона. Будет потрачен один набор ослабления.',
        UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
    )
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
    updated_at = VALUES(updated_at);
