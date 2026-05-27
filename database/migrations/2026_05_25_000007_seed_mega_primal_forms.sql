-- Mega and Primal form seed.
-- Idempotent: stores official classic Mega Evolution forms plus Primal Kyogre/Groudon
-- as separate dex/base rows in the existing 5000+ form range.

CREATE TEMPORARY TABLE IF NOT EXISTS tmp_mega_primal_forms (
    form_id INT NOT NULL PRIMARY KEY,
    base_id INT NOT NULL,
    form_title VARCHAR(32) NOT NULL,
    type_a VARCHAR(24) NOT NULL,
    type_b VARCHAR(24) NOT NULL,
    hp INT NOT NULL,
    atk INT NOT NULL,
    def INT NOT NULL,
    satk INT NOT NULL,
    sdef INT NOT NULL,
    speed INT NOT NULL,
    exp INT NOT NULL,
    ability_key VARCHAR(64) NOT NULL
) ENGINE=Memory;

DELETE FROM tmp_mega_primal_forms;

INSERT INTO tmp_mega_primal_forms
    (form_id, base_id, form_title, type_a, type_b, hp, atk, def, satk, sdef, speed, exp, ability_key)
VALUES
    (5000,1,'Mega Venusaur','Grass','Poison',80,100,123,122,120,80,281,'thick_fat'),
    (5001,6,'Mega Charizard Y','Fire','Flying',78,104,78,159,115,100,285,'drought'),
    (5002,6,'Mega Charizard X','Fire','Dragon',78,130,111,130,85,100,285,'tough_claws'),
    (5003,9,'Mega Blastoise','Water','None',79,103,120,135,115,78,284,'mega_launcher'),
    (5004,65,'Mega Alakazam','Psychic','None',55,50,65,175,105,150,270,'trace'),
    (5005,94,'Mega Gengar','Ghost','Poison',60,65,80,170,95,130,270,'shadow_tag'),
    (5006,115,'Mega Kangaskhan','Normal','None',105,125,100,60,100,100,207,'parental_bond'),
    (5007,127,'Mega Pinsir','Bug','Flying',65,155,120,65,90,105,210,'aerilate'),
    (5008,130,'Mega Gyarados','Water','Dark',95,155,109,70,130,81,224,'mold_breaker'),
    (5009,142,'Mega Aerodactyl','Rock','Flying',80,135,85,70,95,150,215,'tough_claws'),
    (5010,150,'Mega Mewtwo X','Psychic','Fighting',106,190,100,154,100,130,351,'steadfast'),
    (5011,150,'Mega Mewtwo Y','Psychic','None',106,150,70,194,120,140,351,'insomnia'),
    (5012,181,'Mega Ampharos','Electric','Dragon',90,95,105,165,110,45,275,'mold_breaker'),
    (5013,212,'Mega Scizor','Bug','Steel',70,150,140,65,100,75,210,'technician'),
    (5014,214,'Mega Heracross','Bug','Fighting',80,185,115,40,105,75,210,'skill_link'),
    (5015,229,'Mega Houndoom','Dark','Fire',75,90,90,140,90,115,210,'solar_power'),
    (5016,248,'Mega Tyranitar','Rock','Dark',100,164,150,95,120,71,315,'sand_stream'),
    (5017,382,'Primal Kyogre','Water','None',100,150,90,180,160,90,347,'primordial_sea'),
    (5018,383,'Primal Groudon','Ground','Fire',100,180,160,150,90,90,347,'desolate_land'),
    (5019,384,'Mega Rayquaza','Dragon','Flying',105,180,100,180,100,115,351,'delta_stream'),
    (5020,15,'Mega Beedrill','Bug','Poison',65,150,40,15,80,145,223,'adaptability'),
    (5021,18,'Mega Pidgeot','Normal','Flying',83,80,80,135,80,121,261,'no_guard'),
    (5022,80,'Mega Slowbro','Water','Psychic',95,75,180,130,80,30,207,'shell_armor'),
    (5023,208,'Mega Steelix','Steel','Ground',75,125,230,55,95,30,214,'sand_force'),
    (5024,254,'Mega Sceptile','Grass','Dragon',70,110,75,145,85,145,284,'lightning_rod'),
    (5025,257,'Mega Blaziken','Fire','Fighting',80,160,80,130,80,100,284,'speed_boost'),
    (5026,260,'Mega Swampert','Water','Ground',100,150,110,95,110,70,286,'swift_swim'),
    (5027,282,'Mega Gardevoir','Psychic','Fairy',68,85,65,165,135,100,278,'pixilate'),
    (5028,302,'Mega Sableye','Dark','Ghost',50,85,125,85,115,20,168,'magic_bounce'),
    (5029,303,'Mega Mawile','Steel','Fairy',50,105,125,55,95,50,168,'huge_power'),
    (5030,306,'Mega Aggron','Steel','None',70,140,230,60,80,50,284,'filter'),
    (5031,308,'Mega Medicham','Fighting','Psychic',60,100,85,80,85,100,179,'pure_power'),
    (5032,310,'Mega Manectric','Electric','None',70,75,80,135,80,135,201,'intimidate'),
    (5033,319,'Mega Sharpedo','Water','Dark',70,140,70,110,65,105,196,'strong_jaw'),
    (5034,323,'Mega Camerupt','Fire','Ground',70,120,100,145,105,20,196,'sheer_force'),
    (5035,334,'Mega Altaria','Dragon','Fairy',75,110,110,110,105,80,207,'pixilate'),
    (5036,354,'Mega Banette','Ghost','None',64,165,75,93,83,75,194,'prankster'),
    (5037,359,'Mega Absol','Dark','None',65,150,60,115,60,115,198,'magic_bounce'),
    (5038,362,'Mega Glalie','Ice','None',80,120,80,120,80,100,203,'refrigerate'),
    (5039,373,'Mega Salamence','Dragon','Flying',95,145,130,120,90,120,315,'aerilate'),
    (5040,376,'Mega Metagross','Steel','Psychic',80,145,150,105,110,110,315,'tough_claws'),
    (5041,380,'Mega Latias','Dragon','Psychic',80,100,120,140,150,110,315,'levitate'),
    (5042,381,'Mega Latios','Dragon','Psychic',80,130,100,160,120,110,315,'levitate'),
    (5043,428,'Mega Lopunny','Normal','Fighting',65,136,94,54,96,135,203,'scrappy'),
    (5044,445,'Mega Garchomp','Dragon','Ground',108,170,115,120,95,92,315,'sand_force'),
    (5045,448,'Mega Lucario','Fighting','Steel',70,145,88,140,70,112,219,'adaptability'),
    (5046,460,'Mega Abomasnow','Grass','Ice',90,132,105,132,105,30,208,'snow_warning'),
    (5047,475,'Mega Gallade','Psychic','Fighting',68,165,95,65,115,110,278,'inner_focus'),
    (5048,531,'Mega Audino','Normal','Fairy',103,60,126,80,126,50,425,'healer'),
    (5049,719,'Mega Diancie','Rock','Fairy',50,160,110,160,110,110,315,'magic_bounce');

UPDATE pokemon p
INNER JOIN tmp_mega_primal_forms s ON s.form_id = p.id
   SET p.Name = s.form_title,
       p.Element = s.type_a,
       p.SubElement = s.type_b,
       p.HP = s.hp,
       p.Attack = s.atk,
       p.Defense = s.def,
       p.Speed = s.speed,
       p.Base = s.exp,
       p.spatk = s.satk,
       p.spdef = s.sdef,
       p.NextEvo = '',
       p.Evolve = 0,
       p.Code = LPAD(s.base_id, 3, '0'),
       p.Learn = '',
       p.LearnLV = '',
       p.Learnt = '',
       p.LearntPP = '';

INSERT INTO pokemon
    (id, Name, Element, SubElement, HP, Attack, Defense, Speed, Base, spatk, spdef, NextEvo, Evolve, Code, Learn, LearnLV, Learnt, LearntPP)
SELECT s.form_id, s.form_title, s.type_a, s.type_b, s.hp, s.atk, s.def, s.speed, s.exp, s.satk, s.sdef,
       '', 0, LPAD(s.base_id, 3, '0'), '', '', '', ''
  FROM tmp_mega_primal_forms s
 WHERE NOT EXISTS (SELECT 1 FROM pokemon p WHERE p.id = s.form_id);

UPDATE poke_base pb
INNER JOIN tmp_mega_primal_forms s ON s.form_id = pb.id
   SET pb.title = s.form_title,
       pb.img = CONCAT('pok/', s.form_id, '.jpg'),
       pb.hp = s.hp,
       pb.atk = s.atk,
       pb.def = s.def,
       pb.satk = s.satk,
       pb.sdef = s.sdef,
       pb.speed = s.speed,
       pb.exp = s.exp,
       pb.evolution_lvl = 0,
       pb.evolution_type = 0,
       pb.tip_a = 0,
       pb.tip_b = 0,
       pb.egg = 0,
       pb.evol_a = 0,
       pb.ability_key = s.ability_key;

INSERT INTO poke_base
    (id, title, img, hp, atk, def, satk, sdef, speed, exp, evolution_lvl, evolution_type, tip_a, tip_b, egg, evol_a, ability_key)
SELECT s.form_id, s.form_title, CONCAT('pok/', s.form_id, '.jpg'), s.hp, s.atk, s.def, s.satk, s.sdef, s.speed, s.exp,
       0, 0, 0, 0, 0, 0, s.ability_key
  FROM tmp_mega_primal_forms s
 WHERE NOT EXISTS (SELECT 1 FROM poke_base pb WHERE pb.id = s.form_id);

CREATE TABLE IF NOT EXISTS pokemon_description (
    poke_id INT NOT NULL,
    description TEXT NOT NULL,
    updated_at INT NOT NULL DEFAULT 0,
    PRIMARY KEY (poke_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO pokemon_description (poke_id, description, updated_at)
SELECT s.form_id,
       CASE s.form_id
           WHEN 5017 THEN 'Primal Kyogre — праймал-форма Kyogre. Способность «Первозданное море» вызывает Сильный ливень: действуют эффекты дождя, а атаки Огненного типа не выполняются. Погода держится, пока активна эта способность, либо пока её не заменит Сильный ветер или Жаркое солнце.'
           WHEN 5018 THEN 'Primal Groudon — праймал-форма Groudon. Способность «Выжженная земля» вызывает Жаркое солнце: действуют эффекты солнца, а атаки Водного типа не выполняются. Погода держится, пока активна эта способность, либо пока её не заменит Сильный ливень или Сильный ветер.'
           WHEN 5019 THEN 'Mega Rayquaza — мега-форма Rayquaza. Способность «Дельта-поток» вызывает Сильный ветер, перекрывает праймал-погоду и ослабляет атаки, которые особенно опасны для Летающего типа.'
           ELSE CONCAT(s.form_title, ' — особая мега-форма покемона #', LPAD(s.base_id, 3, '0'), '. Форма имеет собственные типы, базовые характеристики и способность ', REPLACE(s.ability_key, '_', ' '), '.')
       END,
       UNIX_TIMESTAMP()
  FROM tmp_mega_primal_forms s
ON DUPLICATE KEY UPDATE
    description = VALUES(description),
    updated_at = VALUES(updated_at);

SET @next_attac_poke_id := (SELECT COALESCE(MAX(id_structure), 0) FROM attac_poke);

INSERT INTO attac_poke (id_structure, atac_id, poke_base_id, atc_lvl)
SELECT @next_attac_poke_id := @next_attac_poke_id + 1,
       x.atac_id,
       x.form_id,
       x.atc_lvl
  FROM (
        SELECT s.form_id, ap.atac_id, ap.atc_lvl
          FROM tmp_mega_primal_forms s
    INNER JOIN attac_poke ap ON ap.poke_base_id = s.base_id
         WHERE NOT EXISTS (
               SELECT 1
                 FROM attac_poke existing
                WHERE existing.poke_base_id = s.form_id
                  AND existing.atac_id = ap.atac_id
                  AND existing.atc_lvl = ap.atc_lvl
         )
         ORDER BY s.form_id, ap.atc_lvl, ap.atac_id
       ) x
;

SET @next_attac_egg_id := (SELECT COALESCE(MAX(id), 0) FROM attac_egg);

INSERT INTO attac_egg (id, poke_base_id, atac_id)
SELECT @next_attac_egg_id := @next_attac_egg_id + 1,
       x.form_id,
       x.atac_id
  FROM (
        SELECT s.form_id, ae.atac_id
          FROM tmp_mega_primal_forms s
    INNER JOIN attac_egg ae ON ae.poke_base_id = s.base_id
         WHERE NOT EXISTS (
               SELECT 1
                 FROM attac_egg existing
                WHERE existing.poke_base_id = s.form_id
                  AND existing.atac_id = ae.atac_id
         )
         ORDER BY s.form_id, ae.atac_id
       ) x
;
