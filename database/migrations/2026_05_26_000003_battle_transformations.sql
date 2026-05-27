-- Battle Transformations: temporary Mega Evolution / Primal Reversion state.
-- Idempotent: does not delete player data and can be re-run safely.

CREATE TABLE IF NOT EXISTS battle_transformations (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    battle_id INT NOT NULL,
    battle_pokemon VARCHAR(32) NOT NULL,
    user_pokemon_id INT NOT NULL DEFAULT 0,
    original_base_id INT NOT NULL,
    form_base_id INT NOT NULL,
    transformation_type VARCHAR(32) NOT NULL,
    required_item_id INT NOT NULL DEFAULT 0,
    source_key VARCHAR(64) NOT NULL DEFAULT '',
    active TINYINT NOT NULL DEFAULT 1,
    activated_round INT NOT NULL DEFAULT 1,
    reverted_at INT NOT NULL DEFAULT 0,
    created_at INT NOT NULL DEFAULT 0,
    updated_at INT NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE KEY uq_battle_transformations_slot (battle_id, battle_pokemon),
    KEY idx_battle_transformations_active (battle_id, active),
    KEY idx_battle_transformations_pokemon (user_pokemon_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pokemon_transformation_unlocks (
    user_id INT NOT NULL,
    base_id INT NOT NULL,
    form_id INT NOT NULL,
    unlocked TINYINT NOT NULL DEFAULT 1,
    source VARCHAR(64) NOT NULL DEFAULT 'manual',
    unlocked_at INT NOT NULL DEFAULT 0,
    updated_at INT NOT NULL DEFAULT 0,
    PRIMARY KEY (user_id, base_id, form_id),
    KEY idx_pokemon_transformation_unlocks_form (form_id, unlocked)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TEMPORARY TABLE IF NOT EXISTS tmp_battle_transformation_items (
    id INT NOT NULL PRIMARY KEY,
    name VARCHAR(96) NOT NULL,
    title VARCHAR(512) NOT NULL,
    kind VARCHAR(24) NOT NULL
) ENGINE=Memory;

DELETE FROM tmp_battle_transformation_items;

INSERT INTO tmp_battle_transformation_items (id, name, title, kind)
VALUES
    (90200,'Blue Orb','Сфера Kyogre. Если Kyogre держит её в бою, он принимает Primal Kyogre. Предмет не расходуется.','primal'),
    (90201,'Red Orb','Сфера Groudon. Если Groudon держит её в бою, он принимает Primal Groudon. Предмет не расходуется.','primal'),
    (90202,'Venusaurite','Mega Stone для Venusaur. Предмет должен быть надет на покемона и не расходуется.','mega'),
    (90203,'Charizardite X','Mega Stone для Charizard X. Предмет должен быть надет на покемона и не расходуется.','mega'),
    (90204,'Charizardite Y','Mega Stone для Charizard Y. Предмет должен быть надет на покемона и не расходуется.','mega'),
    (90205,'Blastoisinite','Mega Stone для Blastoise. Предмет должен быть надет на покемона и не расходуется.','mega'),
    (90206,'Beedrillite','Mega Stone для Beedrill. Предмет должен быть надет на покемона и не расходуется.','mega'),
    (90207,'Pidgeotite','Mega Stone для Pidgeot. Предмет должен быть надет на покемона и не расходуется.','mega'),
    (90208,'Alakazite','Mega Stone для Alakazam. Предмет должен быть надет на покемона и не расходуется.','mega'),
    (90209,'Slowbronite','Mega Stone для Slowbro. Предмет должен быть надет на покемона и не расходуется.','mega'),
    (90210,'Gengarite','Mega Stone для Gengar. Предмет должен быть надет на покемона и не расходуется.','mega'),
    (90211,'Kangaskhanite','Mega Stone для Kangaskhan. Предмет должен быть надет на покемона и не расходуется.','mega'),
    (90212,'Pinsirite','Mega Stone для Pinsir. Предмет должен быть надет на покемона и не расходуется.','mega'),
    (90213,'Gyaradosite','Mega Stone для Gyarados. Предмет должен быть надет на покемона и не расходуется.','mega'),
    (90214,'Aerodactylite','Mega Stone для Aerodactyl. Предмет должен быть надет на покемона и не расходуется.','mega'),
    (90215,'Mewtwonite X','Mega Stone для Mewtwo X. Предмет должен быть надет на покемона и не расходуется.','mega'),
    (90216,'Mewtwonite Y','Mega Stone для Mewtwo Y. Предмет должен быть надет на покемона и не расходуется.','mega'),
    (90217,'Ampharosite','Mega Stone для Ampharos. Предмет должен быть надет на покемона и не расходуется.','mega'),
    (90218,'Steelixite','Mega Stone для Steelix. Предмет должен быть надет на покемона и не расходуется.','mega'),
    (90219,'Scizorite','Mega Stone для Scizor. Предмет должен быть надет на покемона и не расходуется.','mega'),
    (90220,'Heracronite','Mega Stone для Heracross. Предмет должен быть надет на покемона и не расходуется.','mega'),
    (90221,'Houndoominite','Mega Stone для Houndoom. Предмет должен быть надет на покемона и не расходуется.','mega'),
    (90222,'Tyranitarite','Mega Stone для Tyranitar. Предмет должен быть надет на покемона и не расходуется.','mega'),
    (90223,'Sceptilite','Mega Stone для Sceptile. Предмет должен быть надет на покемона и не расходуется.','mega'),
    (90224,'Blazikenite','Mega Stone для Blaziken. Предмет должен быть надет на покемона и не расходуется.','mega'),
    (90225,'Swampertite','Mega Stone для Swampert. Предмет должен быть надет на покемона и не расходуется.','mega'),
    (90226,'Gardevoirite','Mega Stone для Gardevoir. Предмет должен быть надет на покемона и не расходуется.','mega'),
    (90227,'Sablenite','Mega Stone для Sableye. Предмет должен быть надет на покемона и не расходуется.','mega'),
    (90228,'Mawilite','Mega Stone для Mawile. Предмет должен быть надет на покемона и не расходуется.','mega'),
    (90229,'Aggronite','Mega Stone для Aggron. Предмет должен быть надет на покемона и не расходуется.','mega'),
    (90230,'Medichamite','Mega Stone для Medicham. Предмет должен быть надет на покемона и не расходуется.','mega'),
    (90231,'Manectite','Mega Stone для Manectric. Предмет должен быть надет на покемона и не расходуется.','mega'),
    (90232,'Sharpedonite','Mega Stone для Sharpedo. Предмет должен быть надет на покемона и не расходуется.','mega'),
    (90233,'Cameruptite','Mega Stone для Camerupt. Предмет должен быть надет на покемона и не расходуется.','mega'),
    (90234,'Altarianite','Mega Stone для Altaria. Предмет должен быть надет на покемона и не расходуется.','mega'),
    (90235,'Banettite','Mega Stone для Banette. Предмет должен быть надет на покемона и не расходуется.','mega'),
    (90236,'Absolite','Mega Stone для Absol. Предмет должен быть надет на покемона и не расходуется.','mega'),
    (90237,'Glalitite','Mega Stone для Glalie. Предмет должен быть надет на покемона и не расходуется.','mega'),
    (90238,'Salamencite','Mega Stone для Salamence. Предмет должен быть надет на покемона и не расходуется.','mega'),
    (90239,'Metagrossite','Mega Stone для Metagross. Предмет должен быть надет на покемона и не расходуется.','mega'),
    (90240,'Latiasite','Mega Stone для Latias. Предмет должен быть надет на покемона и не расходуется.','mega'),
    (90241,'Latiosite','Mega Stone для Latios. Предмет должен быть надет на покемона и не расходуется.','mega'),
    (90242,'Lopunnite','Mega Stone для Lopunny. Предмет должен быть надет на покемона и не расходуется.','mega'),
    (90243,'Garchompite','Mega Stone для Garchomp. Предмет должен быть надет на покемона и не расходуется.','mega'),
    (90244,'Lucarionite','Mega Stone для Lucario. Предмет должен быть надет на покемона и не расходуется.','mega'),
    (90245,'Abomasite','Mega Stone для Abomasnow. Предмет должен быть надет на покемона и не расходуется.','mega'),
    (90246,'Galladite','Mega Stone для Gallade. Предмет должен быть надет на покемона и не расходуется.','mega'),
    (90247,'Audinite','Mega Stone для Audino. Предмет должен быть надет на покемона и не расходуется.','mega'),
    (90248,'Diancite','Mega Stone для Diancie. Предмет должен быть надет на покемона и не расходуется.','mega');

INSERT INTO items (id, cools, name, tittle, category, uses, dress, delet, torg, elementary, timesnapoke, times, battleuse, dopolnen)
SELECT id, 0, name, title, 8, 0, 1, 1, 1, 0, 0, 0, 0, kind
  FROM tmp_battle_transformation_items
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    tittle = VALUES(tittle),
    category = VALUES(category),
    uses = VALUES(uses),
    dress = VALUES(dress),
    battleuse = VALUES(battleuse),
    dopolnen = VALUES(dopolnen);

INSERT INTO item_target_rules
    (item_id, enabled, target_type, allow_quantity, min_count, max_count, effect_key, consume_on_success, ui_title, ui_hint, created_at, updated_at)
SELECT id, 1, 'pokemon', 0, 1, 1, 'equip_held', 1,
       'Дать предмет',
       'Закрепляет предмет за выбранным покемоном. В бою он может открыть Mega/Primal форму и не расходуется.',
       UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
  FROM tmp_battle_transformation_items
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

UPDATE pokemon_ability_descriptions
   SET name_ru = 'Приморское море',
       description_ru = 'При выходе вызывает Сильный ливень: действуют эффекты дождя, а атаки Fire-типа не выполняются, пока активна эта способность.',
       updated_at = UNIX_TIMESTAMP()
 WHERE ability_key = 'primordial_sea';

UPDATE pokemon_description
   SET description = 'Primal Kyogre — праймал-форма Kyogre. Способность «Приморское море» вызывает Сильный ливень: действуют эффекты дождя, а атаки Огненного типа не выполняются. Погода держится, пока активна эта способность, либо пока её не заменит Сильный ветер или Жаркое солнце.',
       updated_at = UNIX_TIMESTAMP()
 WHERE poke_id = 5017;
