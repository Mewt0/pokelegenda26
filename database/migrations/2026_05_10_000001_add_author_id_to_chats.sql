-- Migration: Add author_id to chats table
ALTER TABLE `chats` ADD COLUMN `author_id` INT(11) DEFAULT 0 AFTER `id`;
ALTER TABLE `chats` ADD INDEX (`author_id`);
ALTER TABLE `chats` ADD INDEX (`userto`);
ALTER TABLE `chats` ADD INDEX (`id`);
