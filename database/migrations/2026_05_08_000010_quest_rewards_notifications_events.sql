CREATE TABLE IF NOT EXISTS game_notifications (
    id INT NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(120) NOT NULL DEFAULT '',
    message VARCHAR(500) NOT NULL DEFAULT '',
    variant VARCHAR(32) NOT NULL DEFAULT 'info',
    payload_json TEXT NULL,
    source VARCHAR(64) NOT NULL DEFAULT '',
    created_at INT NOT NULL DEFAULT 0,
    read_at INT NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY idx_game_notifications_user_read (user_id, read_at, id),
    KEY idx_game_notifications_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS quest_definitions (
    id INT NOT NULL,
    title VARCHAR(160) NOT NULL,
    description TEXT NULL,
    depends_on_quest_id INT NOT NULL DEFAULT 0,
    repeatable TINYINT NOT NULL DEFAULT 0,
    reward_json TEXT NULL,
    enabled TINYINT NOT NULL DEFAULT 1,
    updated_at INT NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY idx_quest_definitions_enabled (enabled),
    KEY idx_quest_definitions_depends (depends_on_quest_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS quest_steps (
    id INT NOT NULL AUTO_INCREMENT,
    quest_id INT NOT NULL,
    step_no INT NOT NULL,
    title VARCHAR(160) NOT NULL,
    description TEXT NULL,
    action_key VARCHAR(80) NOT NULL DEFAULT '',
    required_process INT NOT NULL DEFAULT 0,
    reward_json TEXT NULL,
    enabled TINYINT NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_quest_step (quest_id, step_no),
    KEY idx_quest_steps_action (action_key),
    KEY idx_quest_steps_enabled (quest_id, enabled)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS game_event_boosts (
    id INT NOT NULL AUTO_INCREMENT,
    title VARCHAR(160) NOT NULL,
    boost_key VARCHAR(40) NOT NULL,
    multiplier DECIMAL(6,2) NOT NULL DEFAULT 1.00,
    scope VARCHAR(40) NOT NULL DEFAULT 'global',
    starts_at INT NOT NULL DEFAULT 0,
    ends_at INT NOT NULL DEFAULT 0,
    enabled TINYINT NOT NULL DEFAULT 1,
    note VARCHAR(255) NOT NULL DEFAULT '',
    created_by INT NOT NULL DEFAULT 0,
    created_at INT NOT NULL DEFAULT 0,
    updated_at INT NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY idx_game_event_boosts_active (boost_key, enabled, starts_at, ends_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS player_boosts (
    id INT NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    item_id INT NOT NULL DEFAULT 0,
    boost_key VARCHAR(40) NOT NULL,
    multiplier DECIMAL(6,2) NOT NULL DEFAULT 1.00,
    starts_at INT NOT NULL DEFAULT 0,
    expires_at INT NOT NULL DEFAULT 0,
    active TINYINT NOT NULL DEFAULT 1,
    source VARCHAR(80) NOT NULL DEFAULT '',
    created_at INT NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY idx_player_boosts_active (user_id, boost_key, active, expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO quest_definitions (id, title, description, depends_on_quest_id, repeatable, reward_json, enabled, updated_at)
VALUES
    (1, 'Первый покемон', 'Стартовый квест профессора Оука с выбором первого покемона.', 0, 0, '{"items":{"1":5000,"10":3},"rank":1}', 1, UNIX_TIMESTAMP()),
    (7, 'Перья для Билли', 'Собрать перья Pidgeotto и Spearow для коллекционера.', 1, 0, '{"items":{"1":5000,"9":5,"10":3},"rank":1}', 1, UNIX_TIMESTAMP()),
    (3, 'Цирковая команда', 'Принести Стиву набор редких покемонов для цирка.', 0, 0, '{"items":{"1":50000,"20":3},"rank":2}', 1, UNIX_TIMESTAMP()),
    (4, 'Игрушка Кэрол', 'Найти и вернуть потерянную игрушку.', 0, 0, '{"items":{"1":10000,"5":3},"rank":3}', 1, UNIX_TIMESTAMP()),
    (5, 'Исследование Horsea', 'Сдать десять Horsea 40 уровня исследователю.', 0, 0, '{"items":{"1":25000},"rank":2}', 1, UNIX_TIMESTAMP()),
    (6, 'Metapod на сегодня', 'Ежедневная сдача пяти Metapod 9 уровня.', 0, 1, '{"items":{"1":10000,"3":10},"cooldown":86400}', 1, UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
    title = VALUES(title),
    description = VALUES(description),
    depends_on_quest_id = VALUES(depends_on_quest_id),
    repeatable = VALUES(repeatable),
    reward_json = VALUES(reward_json),
    enabled = VALUES(enabled),
    updated_at = VALUES(updated_at);

INSERT INTO quest_steps (quest_id, step_no, title, description, action_key, required_process, reward_json, enabled)
VALUES
    (1, 1, 'Поговорить с Оуком', 'Профессор просит разобраться со стартовыми покемонами.', 'oak_start', 2, NULL, 1),
    (1, 2, 'Выбрать стартера', 'Игрок выбирает одного из стартовых покемонов.', 'oak_choose', 4, NULL, 1),
    (1, 3, 'Зарегистрировать покемона', 'Оук выдает стартового покемона и набор покеболов.', 'oak_finish', 10, '{"items":{"1":5000,"10":3},"rank":1}', 1),
    (7, 1, 'Принять просьбу Билли', 'Коллекционер просит собрать перья.', 'billy_start', 10, NULL, 1),
    (7, 2, 'Сдать перья', 'Нужно 10 перьев Pidgeotto и 10 перьев Spearow.', 'billy_turnin', 20, '{"items":{"1":5000,"9":5,"10":3},"rank":1}', 1)
ON DUPLICATE KEY UPDATE
    title = VALUES(title),
    description = VALUES(description),
    action_key = VALUES(action_key),
    required_process = VALUES(required_process),
    reward_json = VALUES(reward_json),
    enabled = VALUES(enabled);
