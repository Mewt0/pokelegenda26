-- Optional but strongly recommended before testing.
-- Make a DB backup first.

-- battles.id must generate a fresh battle id. If PRIMARY already exists, skip ADD PRIMARY KEY.
ALTER TABLE `battles` MODIFY `id` INT(11) NOT NULL AUTO_INCREMENT;
-- ALTER TABLE `battles` ADD PRIMARY KEY (`id`);

-- statpokemonbatle stores battle stat stages. Start values must be 0, max stage is handled by code as 6.
ALTER TABLE `statpokemonbatle`
  MODIFY `id` INT(11) NOT NULL AUTO_INCREMENT,
  MODIFY `battleid` INT(11) NOT NULL DEFAULT 0,
  MODIFY `pokeid` CHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  MODIFY `attac` FLOAT NOT NULL DEFAULT 0,
  MODIFY `spattac` FLOAT NOT NULL DEFAULT 0,
  MODIFY `defend` FLOAT NOT NULL DEFAULT 0,
  MODIFY `spdefend` FLOAT NOT NULL DEFAULT 0,
  MODIFY `speed` FLOAT NOT NULL DEFAULT 0,
  MODIFY `acc` FLOAT NOT NULL DEFAULT 0,
  MODIFY `accuracy` FLOAT NOT NULL DEFAULT 0,
  MODIFY `tip` CHAR(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'plus',
  MODIFY `raundends` INT(11) NOT NULL DEFAULT 0;
-- ALTER TABLE `statpokemonbatle` ADD PRIMARY KEY (`id`);
-- ALTER TABLE `statpokemonbatle` ADD UNIQUE KEY `uniq_battle_poke_tip` (`battleid`, `pokeid`, `tip`);

-- bttle_status stores poison/sleep/burn/freeze/paralysis/etc.
ALTER TABLE `bttle_status`
  MODIFY `id_sts` INT(11) NOT NULL AUTO_INCREMENT,
  MODIFY `namber_st` INT(11) NOT NULL DEFAULT 0,
  MODIFY `buttleid` INT(11) NOT NULL DEFAULT 0,
  MODIFY `pokeid` CHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  MODIFY `raund_end` INT(11) NOT NULL DEFAULT 0,
  MODIFY `tip_poke` INT(11) NOT NULL DEFAULT 0;
-- ALTER TABLE `bttle_status` ADD PRIMARY KEY (`id_sts`);

-- Helpful indexes. If index already exists, skip that line.
-- CREATE INDEX `idx_stat_battle_poke_tip` ON `statpokemonbatle` (`battleid`, `pokeid`, `tip`);
-- CREATE INDEX `idx_status_battle_poke` ON `bttle_status` (`buttleid`, `pokeid`, `namber_st`);
-- CREATE INDEX `idx_attac_poke_base_lvl` ON `attac_poke` (`poke_base_id`, `atc_lvl`, `atac_id`);
-- CREATE INDEX `idx_attac_power_name` ON `attac_power` (`atac_id`);

-- Clean only known broken fake start buffs if they exist.
UPDATE `statpokemonbatle`
SET `acc` = 0, `accuracy` = 0
WHERE `tip` = 'plus'
  AND `attac` = 0 AND `spattac` = 0 AND `defend` = 0 AND `spdefend` = 0 AND `speed` = 0
  AND (`acc` > 0 OR `accuracy` > 0);
