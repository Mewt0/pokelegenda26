-- Starter story polish and safe Hippodrome v1.

INSERT INTO quest_definitions (id, title, description, depends_on_quest_id, repeatable, reward_json, enabled, updated_at)
VALUES
    (2, 'Рассказ Спайка', 'Странный Спайк видел тренера с холодной аурой. Цепочка ведёт через Тёмный лес, озеро, Айрена и след Артикуно.', 1, 0, '{"items":{"1":15000,"44":1},"rank":3}', 1, UNIX_TIMESTAMP())
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
    (2, 1, 'Выслушать Спайка', 'Спайк рассказывает о странном тренере, голубом сиянии и древних камнях.', 'spike_story', 2, NULL, 1),
    (2, 2, 'Расспросить старую женщину', 'В Тёмном лесу старуха знает больше, но просит принести трёх Venonat.', 'old_woman_story', 3, NULL, 1),
    (2, 3, 'Принести Venonat', 'Сдать три Venonat 25+ уровня с нахальным характером.', 'old_woman_turnin', 4, '{"rank":1}', 1),
    (2, 4, 'Поговорить с Амирой', 'Художница у озера видела Айрена в полнолуние.', 'amira_lake', 5, NULL, 1),
    (2, 5, 'Поговорить с Айреном', 'Айрен просит найти Артикуно и вернуть души на свои места.', 'airen_lake', 6, NULL, 1),
    (2, 6, 'Найти Articuno', 'На скалах отмечается след легендарного покемона.', 'articuno_cliffs', 7, NULL, 1),
    (2, 7, 'Вернуться к Айрену', 'Завершить историю у озера и получить награду.', 'airen_finish', 20, '{"items":{"1":15000,"44":1},"rank":3}', 1)
ON DUPLICATE KEY UPDATE
    title = VALUES(title),
    description = VALUES(description),
    action_key = VALUES(action_key),
    required_process = VALUES(required_process),
    reward_json = VALUES(reward_json),
    enabled = VALUES(enabled);

CREATE TABLE IF NOT EXISTS hippodrome_races (
    id INT NOT NULL AUTO_INCREMENT,
    title VARCHAR(160) NOT NULL,
    status VARCHAR(24) NOT NULL DEFAULT 'registration',
    level_required INT NOT NULL DEFAULT 100,
    entry_fee_item_id INT NOT NULL DEFAULT 1,
    entry_fee_amount INT NOT NULL DEFAULT 20000,
    registration_starts_at INT NOT NULL DEFAULT 0,
    registration_ends_at INT NOT NULL DEFAULT 0,
    starts_at INT NOT NULL DEFAULT 0,
    ends_at INT NOT NULL DEFAULT 0,
    min_participants INT NOT NULL DEFAULT 6,
    max_participants INT NOT NULL DEFAULT 0,
    prize_pool INT NOT NULL DEFAULT 0,
    created_by INT NOT NULL DEFAULT 0,
    created_at INT NOT NULL DEFAULT 0,
    updated_at INT NOT NULL DEFAULT 0,
    settled_at INT NOT NULL DEFAULT 0,
    note VARCHAR(255) NOT NULL DEFAULT '',
    PRIMARY KEY (id),
    KEY idx_hippodrome_status_time (status, registration_ends_at, ends_at),
    KEY idx_hippodrome_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS hippodrome_participants (
    id INT NOT NULL AUTO_INCREMENT,
    race_id INT NOT NULL,
    user_id INT NOT NULL,
    pokemon_id INT NOT NULL,
    pokemon_base_id INT NOT NULL DEFAULT 0,
    pokemon_name VARCHAR(160) NOT NULL DEFAULT '',
    pokemon_level INT NOT NULL DEFAULT 0,
    speed_score INT NOT NULL DEFAULT 0,
    status VARCHAR(24) NOT NULL DEFAULT 'registered',
    place_num INT NOT NULL DEFAULT 0,
    prize_amount INT NOT NULL DEFAULT 0,
    joined_at INT NOT NULL DEFAULT 0,
    reward_claimed_at INT NOT NULL DEFAULT 0,
    updated_at INT NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE KEY uq_hippodrome_race_user (race_id, user_id),
    UNIQUE KEY uq_hippodrome_race_pokemon (race_id, pokemon_id),
    KEY idx_hippodrome_participants_user (user_id, joined_at),
    KEY idx_hippodrome_participants_race_score (race_id, speed_score, id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS hippodrome_logs (
    id INT NOT NULL AUTO_INCREMENT,
    race_id INT NOT NULL DEFAULT 0,
    user_id INT NOT NULL DEFAULT 0,
    action VARCHAR(64) NOT NULL DEFAULT '',
    data_json TEXT NULL,
    created_at INT NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY idx_hippodrome_logs_race (race_id, created_at),
    KEY idx_hippodrome_logs_user (user_id, created_at),
    KEY idx_hippodrome_logs_action (action, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
