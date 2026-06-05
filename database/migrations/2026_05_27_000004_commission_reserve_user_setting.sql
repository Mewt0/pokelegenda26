INSERT INTO `site_settings` (`name`, `value`, `updated_by`, `updated_at`)
SELECT 'commission.reserve_user_id', CAST(COALESCE((SELECT `id` FROM `users` WHERE `login` = 'Система' ORDER BY `id` ASC LIMIT 1), 3) AS CHAR), 0, UNIX_TIMESTAMP()
ON DUPLICATE KEY UPDATE
  `value` = IF(`value` = '' OR `value` = '3', VALUES(`value`), `value`),
  `updated_at` = VALUES(`updated_at`);
