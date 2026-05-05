-- Extends legacy attack metadata for the rewritten battle engine.
-- The PHP catalog is authoritative for modern moves that are absent in this old dump.

UPDATE attac_power
   SET atac_not = 1
 WHERE atac_name IN ('Toxic', 'Poison Powder', 'Poison Gas', 'Toxic Thread', 'Mortal Spin', 'Baneful Bunker')
   AND atac_not = 0;

UPDATE attac_power
   SET atac_not = 3
 WHERE atac_name IN ('Will-O-Wisp')
   AND atac_not = 0;

UPDATE attac_power
   SET atac_not = 5
 WHERE atac_name IN ('Thunder Wave', 'Stun Spore', 'Glare')
   AND atac_not = 0;

UPDATE attac_power
   SET atac_not = 2
 WHERE atac_name IN ('Spore', 'Sleep Powder', 'Hypnosis', 'Dark Void', 'Lovely Kiss', 'Sing', 'Grass Whistle')
   AND atac_not = 0;

UPDATE attac_power
   SET atac_not = 8
 WHERE atac_name = 'Leech Seed'
   AND atac_not = 0;

CREATE TEMPORARY TABLE battle_move_status_seed (
    name varchar(80) NOT NULL PRIMARY KEY,
    status_id int NOT NULL,
    chance int NOT NULL
) ENGINE=MEMORY;

INSERT INTO battle_move_status_seed (name, status_id, chance) VALUES
('Sludge Bomb', 1, 30),
('Poison Jab', 1, 30),
('Gunk Shot', 1, 30),
('Barb Barrage', 1, 50),
('Sludge Wave', 1, 10),
('Cross Poison', 1, 10),
('Poison Fang', 1, 50),
('Poison Tail', 1, 10),
('Smog', 1, 40),
('Flamethrower', 3, 10),
('Fire Blast', 3, 10),
('Flare Blitz', 3, 10),
('Lava Plume', 3, 30),
('Burning Jealousy', 3, 100),
('Scald', 3, 30),
('Scorching Sands', 3, 30),
('Inferno', 3, 100),
('Heat Crash', 3, 30),
('Fire Punch', 3, 10),
('Sacred Fire', 3, 50),
('Blue Flare', 3, 20),
('Sizzly Slide', 3, 100),
('Matcha Gotcha', 3, 20),
('Nuzzle', 5, 100),
('Zap Cannon', 5, 100),
('Blizzard', 4, 10),
('Ice Beam', 4, 10),
('Freeze-Dry', 4, 10),
('Confuse Ray', 7, 100),
('Sweet Kiss', 7, 100),
('Teeter Dance', 7, 100),
('Swagger', 7, 100),
('Flatter', 7, 100),
('G-Max Befuddle', 7, 100);

UPDATE attac_power ap
JOIN battle_move_status_seed seed ON seed.name = ap.atac_name
   SET ap.chans_dop = seed.chance;

UPDATE attac_dop ad
JOIN attac_power ap ON ap.atac_id = ad.id_attc
JOIN battle_move_status_seed seed ON seed.name = ap.atac_name
   SET ad.setting = 1,
       ad.dop_effc = seed.status_id,
       ad.tip_s = '-',
       ad.def = 0,
       ad.atc = 0,
       ad.sdef = 0,
       ad.satc = 0,
       ad.speed = 0,
       ad.acc = 0,
       ad.accuracy = 0;

SET @next_attac_dop_id := (SELECT COALESCE(MAX(id), 0) FROM attac_dop);

INSERT INTO attac_dop (id, id_attc, dop_effc, setting, tip_s, def, atc, sdef, satc, speed, acc, accuracy)
SELECT @next_attac_dop_id := @next_attac_dop_id + 1,
       ap.atac_id,
       seed.status_id,
       1,
       '-',
       0, 0, 0, 0, 0, 0, 0
  FROM attac_power ap
  JOIN battle_move_status_seed seed ON seed.name = ap.atac_name
 WHERE NOT EXISTS (
       SELECT 1
         FROM attac_dop ad
        WHERE ad.id_attc = ap.atac_id
 );

DROP TEMPORARY TABLE battle_move_status_seed;
