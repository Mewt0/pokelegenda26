UPDATE gym_badges
   SET title = CASE badge_key
     WHEN 'boulder' THEN 'Каменный значок'
     WHEN 'cascade' THEN 'Водный значок'
     WHEN 'thunder' THEN 'Электрический значок'
     WHEN 'rainbow' THEN 'Травяной значок'
     WHEN 'soul' THEN 'Ядовитый значок'
     WHEN 'marsh' THEN 'Психический значок'
     WHEN 'volcano' THEN 'Огненный значок'
     WHEN 'earth' THEN 'Земляной значок'
     ELSE title
   END,
   updated_at = UNIX_TIMESTAMP()
 WHERE badge_key IN ('boulder', 'cascade', 'thunder', 'rainbow', 'soul', 'marsh', 'volcano', 'earth');
