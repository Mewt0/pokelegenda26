-- Keep capture balls and crafting shards separated.
-- item_id 25 is an evolution/crafting shard in the current database, while
-- Premium Ball lives at 90005.

UPDATE items
   SET battleuse = 1,
       uses = 1
 WHERE id IN (3, 90004, 90005);

UPDATE items
   SET battleuse = 0
 WHERE id = 25
   AND LOWER(name) NOT LIKE '%ball%'
   AND name NOT LIKE '%бол%';

UPDATE market_shop_items
   SET enabled = 1,
       updated_at = UNIX_TIMESTAMP()
 WHERE item_id IN (3, 90004, 90005);
