CREATE TABLE IF NOT EXISTS `battle_id_sequence` (
  `id` TINYINT NOT NULL PRIMARY KEY,
  `next_id` INT(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `battle_id_sequence` (`id`, `next_id`)
VALUES (
  1,
  GREATEST(
    UNIX_TIMESTAMP(),
    (SELECT COALESCE(MAX(`id`), 0) + 1 FROM `battles`)
  )
)
ON DUPLICATE KEY UPDATE
  `next_id` = GREATEST(`next_id`, VALUES(`next_id`));
