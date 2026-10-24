INSERT INTO quest_definitions (id, title, description, depends_on_quest_id, repeatable, reward_json, enabled, updated_at)
VALUES
    (3, 'Цирковая команда', 'Циркачу Стиву нужны Butterfree, Arbok, Venomoth, Beedrill и Primeape 35 уровня.', 0, 0, '{"items":{"1":50000,"3":20},"rank":2}', 1, UNIX_TIMESTAMP()),
    (4, 'Игрушка Кэрол', 'Вернуть малышке Кэрол игрушку, которую украли Beedrill.', 0, 0, '{"items":{"1":10000,"3":5},"rank":3}', 1, UNIX_TIMESTAMP()),
    (7, 'Перья для Билли', 'Собрать 10 перьев Pidgeotto и 10 перьев Spearow для коллекционера Билли.', 1, 0, '{"items":{"1":5000,"9":5,"10":3},"rank":2}', 1, UNIX_TIMESTAMP()),
    (8, 'Прилавок Миссис Джафит', 'У Миссис Джафит похитили всех покемонов-помощников и разгромили цветочный прилавок. Помогите ей восстановить магазин.', 0, 0, '{"items":{"18":1,"9":5,"15":20,"56":1},"rank":4}', 1, UNIX_TIMESTAMP())
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
    (3, 2, 'Сдать команду цирку', 'Сдать Butterfree, Arbok, Venomoth, Beedrill и Primeape 35 уровня.', 'circus_turnin', 10, '{"items":{"1":50000,"3":20},"rank":2}', 1),
    (4, 2, 'Вернуть игрушку', 'Атаковать Beedrill, пока не найдете игрушку, затем вернуть ее Кэрол.', 'carol_turnin', 11, '{"items":{"1":10000,"3":5},"rank":3}', 1),
    (7, 2, 'Сдать перья', 'Собрать и сдать 10 перьев Pidgeotto и 10 перьев Spearow.', 'billy_turnin', 20, '{"items":{"1":5000,"9":5,"10":3},"rank":2}', 1),
    (8, 1, 'Принести Oddish', 'Поймать Oddish с осторожным характером и отдать его Миссис Джафит.', 'jafit_oddish_turnin', 3, '{"items":{"18":1}}', 1),
    (8, 2, 'Принести Butterfree', 'Поймать Butterfree 50 уровня женского пола и отдать ее Миссис Джафит.', 'jafit_butterfree_turnin', 6, '{"items":{"9":5}}', 1),
    (8, 3, 'Принести Poliwhirl', 'Поймать Poliwhirl и отдать его Миссис Джафит.', 'jafit_poliwhirl_turnin', 12, '{"items":{"15":20}}', 1),
    (8, 4, 'Принести Weepinbell', 'Поймать Weepinbell 45 уровня со спокойным характером и отдать его Миссис Джафит.', 'jafit_weepinbell_turnin', 20, '{"items":{"56":1},"rank":4}', 1)
ON DUPLICATE KEY UPDATE
    title = VALUES(title),
    description = VALUES(description),
    action_key = VALUES(action_key),
    required_process = VALUES(required_process),
    reward_json = VALUES(reward_json),
    enabled = VALUES(enabled);
