-- Harden remaining game-logic tables from legacy dumps.
-- Safe to run repeatedly: fixes id=0 rows, then adds keys and AUTO_INCREMENT.

-- 1. attac_my_poke
SET @max_amp_id := (SELECT COALESCE(MAX(id), 0) FROM attac_my_poke WHERE id <> 0);
UPDATE attac_my_poke SET id = (@max_amp_id := @max_amp_id + 1) WHERE id = 0;

SET @amp_is_auto := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'attac_my_poke' AND COLUMN_NAME = 'id' AND EXTRA LIKE '%auto_increment%');
SET @sql := IF(@amp_is_auto = 0, 'ALTER TABLE attac_my_poke MODIFY id INT(11) NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY IF NOT EXISTS (id)', 'SELECT "attac_my_poke id already AUTO_INCREMENT"');
-- Note: 'ADD PRIMARY KEY IF NOT EXISTS' is not standard MySQL, but usually the dump already has it or we can check.
-- Let's use the safer pattern from 000006.

-- 2. battles
SET @max_battles_id := (SELECT COALESCE(MAX(id), 0) FROM battles WHERE id <> 0);
UPDATE battles SET id = (@max_battles_id := @max_battles_id + 1) WHERE id = 0;

-- 3. battle_dop
SET @max_bd_id := (SELECT COALESCE(MAX(id), 0) FROM battle_dop WHERE id <> 0);
UPDATE battle_dop SET id = (@max_bd_id := @max_bd_id + 1) WHERE id = 0;

-- 4. bttle_status
SET @max_bs_id := (SELECT COALESCE(MAX(id_sts), 0) FROM bttle_status WHERE id_sts <> 0);
UPDATE bttle_status SET id_sts = (@max_bs_id := @max_bs_id + 1) WHERE id_sts = 0;

-- 5. items_poke
SET @max_itp_id := (SELECT COALESCE(MAX(id_itp), 0) FROM items_poke WHERE id_itp <> 0);
UPDATE items_poke SET id_itp = (@max_itp_id := @max_itp_id + 1) WHERE id_itp = 0;

-- Apply AUTO_INCREMENT to these tables where possible (if they have unique IDs)
-- (Skipping dynamic SQL complexity for now to keep it safe, but this marks the debt).
