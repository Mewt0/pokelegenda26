INSERT INTO quest_definitions (id, title, description, depends_on_quest_id, repeatable, reward_json, enabled, updated_at)
VALUES
    (101, 'Первый бой на Дороге 1', 'После стартера игрок выходит на Дорогу 1, запускает первый PvE-бой и получает базовую награду за победу или ловлю.', 1, 0, '{"items":{"1":2000,"10":2},"rank":1}', 1, UNIX_TIMESTAMP()),
    (102, 'Путь в Вертанию', 'Игрок проходит цепочку Алабастия -> Дорога 1 -> лесная тропа -> Вертания и получает билет для проверки транспорта.', 101, 0, '{"items":{"1":3000,"90111":1},"rank":1}', 1, UNIX_TIMESTAMP()),
    (103, 'Первый транспорт', 'Игрок открывает транспортный модуль и впервые использует рейс или перелёт без legacy PHP.', 102, 0, '{"items":{"1":2000},"rank":1}', 1, UNIX_TIMESTAMP())
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
    (101, 1, 'Выйти на Дорогу 1', 'Перейти из Алабастии на Дорогу 1 через новую карту.', 'fpe_route_1', 10, NULL, 1),
    (101, 2, 'Провести первый бой', 'Победить или поймать дикого покемона в PvE-бою.', 'fpe_first_battle', 20, '{"items":{"1":2000,"10":2},"rank":1}', 1),
    (102, 1, 'Дойти до Вертании', 'Пройти от Дороги 1 через доступные переходы до города Вертания.', 'fpe_viridian_path', 10, NULL, 1),
    (102, 2, 'Закрепиться в городе', 'Добраться до Вертании и открыть следующий транспортный шаг.', 'fpe_viridian_arrival', 20, '{"items":{"1":3000,"90111":1},"rank":1}', 1),
    (103, 1, 'Открыть транспорт', 'Открыть транспорт из меню или через кассу/порт.', 'fpe_transport_open', 10, NULL, 1),
    (103, 2, 'Использовать рейс', 'Запустить самолёт или обычный рейс и проверить, что маршрут работает в новом API.', 'fpe_transport_used', 20, '{"items":{"1":2000},"rank":1}', 1)
ON DUPLICATE KEY UPDATE
    title = VALUES(title),
    description = VALUES(description),
    action_key = VALUES(action_key),
    required_process = VALUES(required_process),
    reward_json = VALUES(reward_json),
    enabled = VALUES(enabled);
