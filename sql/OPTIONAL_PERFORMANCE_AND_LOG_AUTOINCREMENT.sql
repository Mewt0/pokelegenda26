-- OPTIONAL. Делать только после backup БД.
-- Код уже имеет fallback, но эти изменения делают battle_log / bttle_status безопаснее.

-- battle_log.id сейчас на серверной БД может быть без AUTO_INCREMENT.
-- Если PRIMARY KEY уже есть, выполнить только MODIFY.
-- ALTER TABLE `battle_log` ADD PRIMARY KEY (`id`);
-- ALTER TABLE `battle_log` MODIFY `id` INT(250) NOT NULL AUTO_INCREMENT;

-- bttle_status.id_sts сейчас на серверной БД может быть без AUTO_INCREMENT.
-- Если PRIMARY KEY уже есть, выполнить только MODIFY.
-- ALTER TABLE `bttle_status` ADD PRIMARY KEY (`id_sts`);
-- ALTER TABLE `bttle_status` MODIFY `id_sts` INT(11) NOT NULL AUTO_INCREMENT;

-- Проверка дублей боевых статов. Если вернёт строки — сначала чистить руками.
SELECT `battleid`, `pokeid`, `tip`, COUNT(*) AS c
FROM `statpokemonbatle`
GROUP BY `battleid`, `pokeid`, `tip`
HAVING c > 1;

-- Уникальный ключ включать только если запрос выше вернул 0 строк.
-- ALTER TABLE `statpokemonbatle` ADD UNIQUE KEY `uniq_battle_poke_tip` (`battleid`, `pokeid`, `tip`);
