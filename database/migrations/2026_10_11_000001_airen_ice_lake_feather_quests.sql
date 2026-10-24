-- Легендарная линия Айрена: ледяное крыло, шёпот озера и перо Билли.
-- Контент: DeepSeek (окно браузера), внесён DSH-агентом 2026-10-11.
-- Идемпотентно: INSERT ... ON DUPLICATE KEY UPDATE, повтор безопасен.
-- action_key из этого файла реализуются отдельной задачей в NpcDialogService;
-- пока диалогов нет, квесты недостижимы для игрока (в журнал не попадают).

INSERT INTO quest_definitions (id, title, description, depends_on_quest_id, repeatable, reward_json, enabled, updated_at)
VALUES
    (160, 'Сон о ледяном крыле', 'Айрену снова снится птица, и на этот раз она роняет перо у хижины знахарки. Мальчик просит прийти ночью и помочь разобраться.', 0, 0, '{"items":{"1":8000,"3":10,"5":5},"rank":2}', 1, UNIX_TIMESTAMP()),
    (161, 'Шёпот озера', 'Амира клянётся, что в отражении озера видела второе крыло — грозовое. Она просит проверить, не отзывается ли вода на грозу.', 160, 0, '{"items":{"1":12000,"5":8,"9":2},"rank":3}', 1, UNIX_TIMESTAMP()),
    (162, 'Перо для Билли', 'Билли готов отдать своё собрание перьев, но только тому, кто принесёт ему доказательство смелости — покемона с храбрым сердцем.', 161, 0, '{"items":{"1":15000,"3":15,"9":3,"10":20},"rank":4}', 1, UNIX_TIMESTAMP())
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
    (160, 1, 'Ночной гость', 'Приди к хижине знахарки после захода солнца и выслушай сбивчивый рассказ Айрена.', 'airen_ice_dream_start', 2, NULL, 1),
    (160, 2, 'Поймать холод', 'Поймай ледяного покемона не ниже 15 уровня — птица отзывается только на живой холод.', 'airen_ice_catch', 3, NULL, 1),
    (160, 3, 'Сдать знахарке', 'Принеси знахарке пойманного покемона, 3 лечебных предмета и 2 пера.', 'airen_ice_turnin', 20, '{"items":{"1":8000,"3":10,"5":5},"rank":2}', 1),
    (161, 1, 'У озера', 'Поговори с Амирой у озера днём — она покажет набросок второго крыла.', 'amira_lake_start', 4, NULL, 1),
    (161, 2, 'Гроза на дороге', 'Победи пять диких покемонов на дороге, чтобы проверить, как вода отзывается на грозу.', 'amira_storm_wins', 5, NULL, 1),
    (161, 3, 'Отдать набросок', 'Принеси Амире два энергетика и расскажи, что видел в отражении.', 'amira_lake_turnin', 20, '{"items":{"1":12000,"5":8,"9":2},"rank":3}', 1),
    (162, 1, 'Ставка на перо', 'Найди Билли и выслушай его условие — он торгуется только за храбрость.', 'billy_feather_start', 6, NULL, 1),
    (162, 2, 'Храброе сердце', 'Поймай покемона с характером «смелый» — Билли проверит его лично.', 'billy_brave_catch', 7, NULL, 1),
    (162, 3, 'Обмен у витрины', 'Принеси Билли десять сушёных клеверов и одну конфету в обмен на перо.', 'billy_feather_turnin', 20, '{"items":{"1":15000,"3":15,"9":3,"10":20},"rank":4}', 1)
ON DUPLICATE KEY UPDATE
    title = VALUES(title),
    description = VALUES(description),
    action_key = VALUES(action_key),
    required_process = VALUES(required_process),
    reward_json = VALUES(reward_json),
    enabled = VALUES(enabled);
