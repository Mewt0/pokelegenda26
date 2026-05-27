-- Permanent service account used for QA, market seed lots and future system-side operations.
INSERT INTO users (
  login, password, email, email_verified_at, online, onlinetime, activation, `groups`,
  moderation, police, datereg, avatars, clanid, clan_point, clan_adm,
  pve, pvp, trade, info, gender, ip, rang, rang_a, rang_b, rang_c,
  karma_score, count_poke, count_poke_s, buildmy, mychat, newuser, battleid,
  pve_button, atack_poke, status_klan, youtuber, prefics, timepoke
)
SELECT
  'Система',
  '!system-account-no-login!',
  'system@pokemonchic.local',
  0, 0, UNIX_TIMESTAMP(), 1, 1,
  0, 0, NOW(), 1, 0, 0, 0,
  0, 0, 0,
  'Служебный аккаунт для системных и QA-операций.',
  1, 0, '', 0, 0, 0,
  0, 0, 0, 1, 1, 0, 0,
  0, 0, '', 0, 0, 0
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM users WHERE login = 'Система' LIMIT 1);

UPDATE users
   SET email = 'system@pokemonchic.local',
       activation = 1,
       online = 0,
       pve = 0,
       pvp = 0,
       trade = 0,
       info = 'Служебный аккаунт для системных и QA-операций.'
 WHERE login = 'Система';

INSERT INTO site_settings (name, value, updated_by, updated_at)
SELECT 'system.account_id', CAST(id AS CHAR), 0, UNIX_TIMESTAMP()
  FROM users
 WHERE login = 'Система'
 ORDER BY id ASC
 LIMIT 1
ON DUPLICATE KEY UPDATE value = VALUES(value), updated_at = VALUES(updated_at);

INSERT INTO site_settings (name, value, updated_by, updated_at)
VALUES ('system.account_login', 'Система', 0, UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE value = VALUES(value), updated_at = VALUES(updated_at);
