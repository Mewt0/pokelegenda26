-- TASK-049: архивирование осиротевших записей и внешние ключи на критичные связи.
--
-- Что делает:
--   1) складывает в архивные таблицы строки, чей владелец уже не существует
--      (items_users.user_id, pok_user.users, eggs.users_egg). Ничего не удаляется
--      безвозвратно: строка сначала попадает в *_orphan_archive, потом исчезает из
--      рабочей таблицы, иначе внешний ключ не поставить;
--   2) приводит схему к требованиям внешнего ключа: индекс на items.id,
--      тип items_users.item_id под items.id, индекс на eggs.users_egg;
--   3) ставит пять внешних ключей: элементы игрока, покемоны игрока, яйца, продавец лота.
--
-- Чего не делает: не трогает battles.user_1/user_2 (ждёт решения по TASK-032),
-- не ставит ключи на market_lots.buyer_id/private_buyer_id (0 — это «покупателя нет»,
-- а не ссылка), не меняет ни одной строки, у которой владелец существует.
--
-- Перед применением снят бэкап: storage/backups/pokemonchic_beta_20261011_035103.sql.
-- Откат: см. комментарии в конце файла.

SET NAMES utf8mb4;

-- ── 1. Архивные таблицы ────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `items_users_orphan_archive` LIKE `items_users`;
CREATE TABLE IF NOT EXISTS `pok_user_orphan_archive` LIKE `pok_user`;
CREATE TABLE IF NOT EXISTS `eggs_orphan_archive` LIKE `eggs`;

SET @sql := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'items_users_orphan_archive' AND COLUMN_NAME = 'archived_at') = 0,
    'ALTER TABLE `items_users_orphan_archive` ADD COLUMN `archived_at` INT NOT NULL DEFAULT 0',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pok_user_orphan_archive' AND COLUMN_NAME = 'archived_at') = 0,
    'ALTER TABLE `pok_user_orphan_archive` ADD COLUMN `archived_at` INT NOT NULL DEFAULT 0',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'eggs_orphan_archive' AND COLUMN_NAME = 'archived_at') = 0,
    'ALTER TABLE `eggs_orphan_archive` ADD COLUMN `archived_at` INT NOT NULL DEFAULT 0',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ── 2. Перенос осиротевших строк в архив ───────────────────────────────────

INSERT INTO `items_users_orphan_archive`
SELECT c.*, UNIX_TIMESTAMP() FROM `items_users` c
LEFT JOIN `users` u ON u.id = c.user_id
WHERE u.id IS NULL
  AND NOT EXISTS (SELECT 1 FROM `items_users_orphan_archive` a WHERE a.id = c.id);

DELETE c FROM `items_users` c
LEFT JOIN `users` u ON u.id = c.user_id
WHERE u.id IS NULL;

INSERT INTO `pok_user_orphan_archive`
SELECT c.*, UNIX_TIMESTAMP() FROM `pok_user` c
LEFT JOIN `users` u ON u.id = c.users
WHERE u.id IS NULL
  AND NOT EXISTS (SELECT 1 FROM `pok_user_orphan_archive` a WHERE a.id = c.id);

DELETE c FROM `pok_user` c
LEFT JOIN `users` u ON u.id = c.users
WHERE u.id IS NULL;

INSERT INTO `eggs_orphan_archive`
SELECT c.*, UNIX_TIMESTAMP() FROM `eggs` c
LEFT JOIN `users` u ON u.id = c.users_egg
WHERE u.id IS NULL
  AND NOT EXISTS (SELECT 1 FROM `eggs_orphan_archive` a WHERE a.id_egg = c.id_egg);

DELETE c FROM `eggs` c
LEFT JOIN `users` u ON u.id = c.users_egg
WHERE u.id IS NULL;

-- ── 3. Схема под внешние ключи ─────────────────────────────────────────────

-- items.id не имеет ни ключа, ни индекса, поэтому на него нельзя сослаться.
SET @has := (SELECT COUNT(*) FROM information_schema.STATISTICS
              WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'items' AND COLUMN_NAME = 'id');
SET @sql := IF(@has = 0, 'ALTER TABLE `items` ADD KEY `idx_items_id` (`id`)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- items_users.item_id объявлен int(20), а items.id — int(11) unsigned; типы обязаны совпасть.
SET @needs := (SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'items_users'
                  AND COLUMN_NAME = 'item_id' AND COLUMN_TYPE <> 'int(11) unsigned');
SET @sql := IF(@needs > 0,
    'ALTER TABLE `items_users` MODIFY `item_id` INT(11) UNSIGNED NOT NULL',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- eggs.users_egg без индекса, внешнему ключу нужен индекс на дочерней колонке.
SET @has := (SELECT COUNT(*) FROM information_schema.STATISTICS
              WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'eggs' AND COLUMN_NAME = 'users_egg');
SET @sql := IF(@has = 0, 'ALTER TABLE `eggs` ADD KEY `idx_eggs_users_egg` (`users_egg`)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ── 4. Внешние ключи ───────────────────────────────────────────────────────

-- Инвентарь: строка обязана принадлежать живому игроку.
SET @fk := (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
             WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'items_users'
               AND CONSTRAINT_NAME = 'fk_items_users_user_id_users_id' AND CONSTRAINT_TYPE = 'FOREIGN KEY');
SET @sql := IF(@fk = 0,
    'ALTER TABLE `items_users` ADD CONSTRAINT `fk_items_users_user_id_users_id`
       FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Инвентарь: строка обязана ссылаться на существующий предмет.
SET @fk := (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
             WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'items_users'
               AND CONSTRAINT_NAME = 'fk_items_users_item_id_items_id' AND CONSTRAINT_TYPE = 'FOREIGN KEY');
SET @sql := IF(@fk = 0,
    'ALTER TABLE `items_users` ADD CONSTRAINT `fk_items_users_item_id_items_id`
       FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Покемон игрока (включая резервного владельца комиссионки) обязан существовать.
SET @fk := (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
             WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'pok_user'
               AND CONSTRAINT_NAME = 'fk_pok_user_users_users_id' AND CONSTRAINT_TYPE = 'FOREIGN KEY');
SET @sql := IF(@fk = 0,
    'ALTER TABLE `pok_user` ADD CONSTRAINT `fk_pok_user_users_users_id`
       FOREIGN KEY (`users`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Яйцо на разведении обязано принадлежать живому игроку.
SET @fk := (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
             WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'eggs'
               AND CONSTRAINT_NAME = 'fk_eggs_users_egg_users_id' AND CONSTRAINT_TYPE = 'FOREIGN KEY');
SET @sql := IF(@fk = 0,
    'ALTER TABLE `eggs` ADD CONSTRAINT `fk_eggs_users_egg_users_id`
       FOREIGN KEY (`users_egg`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Продавец лота комиссионной лавки.
SET @fk := (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
             WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'market_lots'
               AND CONSTRAINT_NAME = 'fk_market_lots_seller_id_users_id' AND CONSTRAINT_TYPE = 'FOREIGN KEY');
SET @sql := IF(@fk = 0,
    'ALTER TABLE `market_lots` ADD CONSTRAINT `fk_market_lots_seller_id_users_id`
       FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ── Откат ──────────────────────────────────────────────────────────────────
-- ALTER TABLE `market_lots` DROP FOREIGN KEY `fk_market_lots_seller_id_users_id`;
-- ALTER TABLE `eggs` DROP FOREIGN KEY `fk_eggs_users_egg_users_id`;
-- ALTER TABLE `pok_user` DROP FOREIGN KEY `fk_pok_user_users_users_id`;
-- ALTER TABLE `items_users` DROP FOREIGN KEY `fk_items_users_item_id_items_id`;
-- ALTER TABLE `items_users` DROP FOREIGN KEY `fk_items_users_user_id_users_id`;
-- ALTER TABLE `eggs` DROP KEY `idx_eggs_users_egg`;
-- ALTER TABLE `items` DROP KEY `idx_items_id`;
-- ALTER TABLE `items_users` MODIFY `item_id` INT(20) NOT NULL;
-- Восстановление строк из архива (выполнять осознанно, вручную):
--   INSERT INTO items_users (id,item_id,user_id,count,dattimer,timers)
--     SELECT id,item_id,user_id,count,dattimer,timers FROM items_users_orphan_archive;
--   INSERT INTO pok_user SELECT * FROM pok_user_orphan_archive;  -- без служебной колонки
--   INSERT INTO eggs SELECT * FROM eggs_orphan_archive;
