-- Fix id=0 entries in legacy tables
-- These often appear in legacy dumps due to incorrect primary key handling or bulk imports without AI.

-- Fix items_users: rows with id=0 are considered corrupt or duplicates of the first valid entry.
DELETE FROM items_users WHERE id = 0;

-- Fix inputusers: legacy registration sometimes inserted id=0
DELETE FROM inputusers WHERE id = 0;

-- Fix battles and bttle_status: update 0 to next available ID to avoid collision with PK requirements
UPDATE battles SET id = (SELECT COALESCE(MAX(id), 0) + 1 FROM (SELECT * FROM battles) AS b) WHERE id = 0;
UPDATE bttle_status SET id_sts = (SELECT COALESCE(MAX(id_sts), 0) + 1 FROM (SELECT * FROM bttle_status) AS s) WHERE id_sts = 0;
