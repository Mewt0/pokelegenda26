INSERT INTO quest_definitions (id, title, description, depends_on_quest_id, repeatable, reward_json, enabled, updated_at)
VALUES
    (1, 'Первый покемон', 'Стартовый квест профессора Оука с выбором первого покемона и регистрацией команды.', 0, 0, '{"items":{"1":5000,"10":3},"rank":1}', 1, UNIX_TIMESTAMP()),
    (2, 'Рассказ Спайка', 'Странный Спайк видел необычного тренера и открывает ветку крафта камней.', 1, 0, '{"rank":1}', 1, UNIX_TIMESTAMP()),
    (3, 'Цирковая команда', 'Принести Стиву набор цирковых покемонов нужного уровня.', 0, 0, '{"items":{"1":50000,"20":3},"rank":2}', 1, UNIX_TIMESTAMP()),
    (4, 'Игрушка Кэрол', 'Найти и вернуть потерянную игрушку Кэрол.', 0, 0, '{"items":{"1":10000,"5":3},"rank":3}', 1, UNIX_TIMESTAMP()),
    (5, 'Исследование Horsea', 'Сдать десять Horsea 40 уровня исследователю.', 0, 0, '{"items":{"1":25000},"rank":2}', 1, UNIX_TIMESTAMP()),
    (6, 'Metapod на сегодня', 'Ежедневная сдача пяти Metapod 9 уровня.', 0, 1, '{"items":{"1":10000,"3":10},"cooldown":86400}', 1, UNIX_TIMESTAMP()),
    (7, 'Перья для Билли', 'Собрать перья Pidgeotto и Spearow для коллекционера Билли.', 1, 0, '{"items":{"1":5000,"9":5,"10":3},"rank":1}', 1, UNIX_TIMESTAMP())
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
    (1, 2, 'Расспросить прохожего', 'Узнать, куда убежали стартовые покемоны.', 'oak_hint', 3, NULL, 1),
    (1, 3, 'Выбрать стартера', 'Игрок выбирает одного из стартовых покемонов.', 'oak_choose', 4, NULL, 1),
    (1, 4, 'Зарегистрировать покемона', 'Оук выдаёт стартового покемона и набор припасов.', 'oak_finish', 10, '{"items":{"1":5000,"10":3},"rank":1}', 1),
    (2, 1, 'Выслушать Спайка', 'Спайк рассказывает о странном тренере и древних камнях.', 'spike_story', 2, '{"rank":1}', 1),
    (3, 1, 'Принять задание Стива', 'Циркачу нужны пять покемонов 35 уровня.', 'circus_start', 2, NULL, 1),
    (3, 2, 'Сдать команду цирку', 'Сдать Butterfree, Arbok, Venomoth, Beedrill и Primeape 35 уровня.', 'circus_turnin', 10, '{"items":{"1":50000,"20":3},"rank":2}', 1),
    (4, 1, 'Принять просьбу Кэрол', 'Кэрол просит вернуть украденную игрушку.', 'carol_start', 10, NULL, 1),
    (4, 2, 'Вернуть игрушку', 'Сдать предмет #4 и получить награду.', 'carol_turnin', 11, '{"items":{"1":10000,"5":3},"rank":3}', 1),
    (5, 1, 'Принять исследование', 'Исследователь просит собрать десять Horsea 40 уровня.', 'horsea_start', 10, NULL, 1),
    (5, 2, 'Сдать Horsea', 'Сдать десять Horsea 40 уровня.', 'horsea_turnin', 20, '{"items":{"1":25000},"rank":2}', 1),
    (6, 1, 'Взять ежедневное задание', 'Принести пять Metapod 9 уровня.', 'metapod_start', 10, NULL, 1),
    (6, 2, 'Сдать Metapod', 'Сдать пять Metapod 9 уровня и запустить суточный cooldown.', 'metapod_turnin', 1, '{"items":{"1":10000,"3":10},"cooldown":86400}', 1),
    (7, 1, 'Принять просьбу Билли', 'Коллекционер просит собрать перья.', 'billy_start', 10, NULL, 1),
    (7, 2, 'Сдать перья', 'Нужно 10 перьев Pidgeotto и 10 перьев Spearow.', 'billy_turnin', 20, '{"items":{"1":5000,"9":5,"10":3},"rank":1}', 1)
ON DUPLICATE KEY UPDATE
    title = VALUES(title),
    description = VALUES(description),
    action_key = VALUES(action_key),
    required_process = VALUES(required_process),
    reward_json = VALUES(reward_json),
    enabled = VALUES(enabled);
