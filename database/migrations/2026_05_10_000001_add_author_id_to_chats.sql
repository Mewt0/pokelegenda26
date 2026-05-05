-- Migration: Add author_id to chats table and backfill data
ALTER TABLE `chats` ADD COLUMN `author_id` INT(11) DEFAULT 0 AFTER `id`;

-- Backfill author_id from users table where possible
UPDATE `chats` c
JOIN `users` u ON u.login = c.author
SET c.author_id = u.id
WHERE c.author_id = 0;

-- Add indexes for performance
ALTER TABLE `chats` ADD INDEX `idx_author_id` (`author_id`);
ALTER TABLE `chats` ADD INDEX `idx_userto` (`userto`);
ALTER TABLE `chats` ADD INDEX `idx_id_room_private` (`id`, `room`, `private`);
