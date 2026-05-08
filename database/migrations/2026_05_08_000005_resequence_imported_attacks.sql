-- Move the XLSX-imported attacks from the temporary 1000..1374 range into
-- the contiguous catalog range 560..934. Legacy service rows 997..999 remain
-- at the end of the attack table.

UPDATE attac_power
   SET atac_id = atac_id - 440
 WHERE atac_id BETWEEN 1000 AND 1374
   AND titles LIKE 'Импортировано из pokemon_moves_by_gen.xlsx%';

UPDATE attac_poke
   SET atac_id = atac_id - 440
 WHERE atac_id BETWEEN 1000 AND 1374;

UPDATE attac_egg
   SET atac_id = atac_id - 440
 WHERE atac_id BETWEEN 1000 AND 1374;

UPDATE attac_my_poke
   SET a_id = a_id - 440
 WHERE a_id BETWEEN 1000 AND 1374;

UPDATE attac_my_poke
   SET b_id = b_id - 440
 WHERE b_id BETWEEN 1000 AND 1374;

UPDATE attac_my_poke
   SET c_id = c_id - 440
 WHERE c_id BETWEEN 1000 AND 1374;

UPDATE attac_my_poke
   SET d_id = d_id - 440
 WHERE d_id BETWEEN 1000 AND 1374;
