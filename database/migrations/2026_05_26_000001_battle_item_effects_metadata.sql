-- Battle item effect metadata.
-- Idempotent: only marks existing items as usable in battle and appends parser-friendly effect keys.

UPDATE items
   SET battleuse = 1,
       dopolnen = TRIM(BOTH ';' FROM CONCAT_WS(';', NULLIF(NULLIF(dopolnen, '0'), ''), 'battle_cure:sleep'))
 WHERE id = 15
   AND dopolnen NOT LIKE '%battle_cure:sleep%';

UPDATE items
   SET battleuse = 1,
       dopolnen = TRIM(BOTH ';' FROM CONCAT_WS(';', NULLIF(NULLIF(dopolnen, '0'), ''), 'battle_pp_restore'))
 WHERE id = 217
   AND dopolnen NOT LIKE '%battle_pp_restore%';

UPDATE items
   SET battleuse = 1,
       dopolnen = TRIM(BOTH ';' FROM CONCAT_WS(';', NULLIF(NULLIF(dopolnen, '0'), ''), 'battle_heal_percent:50'))
 WHERE id = 861
   AND dopolnen NOT LIKE '%battle_heal_percent:%';

UPDATE items
   SET battleuse = 1,
       dopolnen = TRIM(BOTH ';' FROM CONCAT_WS(';', NULLIF(NULLIF(dopolnen, '0'), ''), 'battle_cure:poison'))
 WHERE (LOWER(name) LIKE '%antidote%' OR LOWER(name) LIKE '%антидот%' OR LOWER(name) LIKE '%противояд%')
   AND dopolnen NOT LIKE '%battle_cure:%';

UPDATE items
   SET battleuse = 1,
       dopolnen = TRIM(BOTH ';' FROM CONCAT_WS(';', NULLIF(NULLIF(dopolnen, '0'), ''), 'battle_cure:burn'))
 WHERE (LOWER(name) LIKE '%burn heal%' OR LOWER(name) LIKE '%антиожог%' OR LOWER(name) LIKE '%ожог%')
   AND dopolnen NOT LIKE '%battle_cure:%';

UPDATE items
   SET battleuse = 1,
       dopolnen = TRIM(BOTH ';' FROM CONCAT_WS(';', NULLIF(NULLIF(dopolnen, '0'), ''), 'battle_cure:paralyze'))
 WHERE (LOWER(name) LIKE '%paralyze heal%' OR LOWER(name) LIKE '%антипара%' OR LOWER(name) LIKE '%паралич%')
   AND dopolnen NOT LIKE '%battle_cure:%';

UPDATE items
   SET battleuse = 1,
       dopolnen = TRIM(BOTH ';' FROM CONCAT_WS(';', NULLIF(NULLIF(dopolnen, '0'), ''), 'battle_cure:freeze'))
 WHERE (LOWER(name) LIKE '%ice heal%' OR LOWER(name) LIKE '%размороз%' OR LOWER(name) LIKE '%замороз%')
   AND dopolnen NOT LIKE '%battle_cure:%';

UPDATE items
   SET battleuse = 1,
       dopolnen = TRIM(BOTH ';' FROM CONCAT_WS(';', NULLIF(NULLIF(dopolnen, '0'), ''), 'battle_cure:confuse'))
 WHERE (LOWER(name) LIKE '%confusion%' OR LOWER(name) LIKE '%спутан%')
   AND dopolnen NOT LIKE '%battle_cure:%';

UPDATE items
   SET battleuse = 1,
       dopolnen = TRIM(BOTH ';' FROM CONCAT_WS(';', NULLIF(NULLIF(dopolnen, '0'), ''), 'full_heal'))
 WHERE (LOWER(name) LIKE '%full heal%' OR LOWER(name) LIKE '%полное исцел%')
   AND dopolnen NOT LIKE '%full_heal%';

UPDATE items
   SET battleuse = 1,
       dopolnen = TRIM(BOTH ';' FROM CONCAT_WS(';', NULLIF(NULLIF(dopolnen, '0'), ''), 'full_restore'))
 WHERE (LOWER(name) LIKE '%full restore%' OR LOWER(name) LIKE '%полное восстанов%')
   AND dopolnen NOT LIKE '%full_restore%';

UPDATE items
   SET battleuse = 1,
       dopolnen = TRIM(BOTH ';' FROM CONCAT_WS(';', NULLIF(NULLIF(dopolnen, '0'), ''), 'battle_heal_percent:50'))
 WHERE (LOWER(name) LIKE '%potion%' OR LOWER(name) LIKE '%зелье%')
   AND LOWER(name) NOT LIKE '%full%'
   AND LOWER(name) NOT LIKE '%полное%'
   AND dopolnen NOT LIKE '%battle_heal_percent:%';
