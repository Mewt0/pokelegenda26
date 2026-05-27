INSERT INTO pokemon_ability_descriptions (ability_key, name_ru, description_ru, created_at, updated_at)
VALUES
    (
        'primordial_sea',
        'Приморское море',
        'Вызывает сильнейший ливень. Усиливает Water-атаки на 50%, блокирует Fire-атаки и не может быть заменен обычной погодой.',
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    ),
    (
        'desolate_land',
        'Выжженная земля',
        'Вызывает экстремальное солнце. Усиливает Fire-атаки на 50%, блокирует Water-атаки и не может быть заменено обычной погодой.',
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    ),
    (
        'delta_stream',
        'Дельта-поток',
        'Вызывает сильные ветры. Блокирует обычную погоду и убирает супер-эффективность атак против Flying-типа.',
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    )
ON DUPLICATE KEY UPDATE
    name_ru = VALUES(name_ru),
    description_ru = VALUES(description_ru),
    updated_at = VALUES(updated_at);
