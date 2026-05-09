-- Fix beta capture flow.
-- Pokeballs must be usable in PvE battles and caught pokemon must be visible
-- through the pokemon screen even when they go to the nursery.

UPDATE items
   SET battleuse = 1,
       uses = 1
 WHERE id IN (3, 25, 90004);

UPDATE market_shop_items
   SET enabled = 1,
       updated_at = UNIX_TIMESTAMP()
 WHERE item_id IN (3, 25, 90004);
