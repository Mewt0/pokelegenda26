-- Extended Pokemarket catalogue from plan.md.
-- Idempotent: reruns update names/prices and insert only missing items.

INSERT INTO items (
    id, cools, name, tittle, category, uses, dress, delet, torg,
    elementary, timesnapoke, times, battleuse, dopolnen
)
SELECT seed.id, 0, seed.name, seed.tittle, 0, 1, 0, 1, 1, 0, 1, '0', 0, 'market_item'
FROM (
    SELECT 25 AS id, 'Премиум бол' AS name, 'Улучшенный покебол с повышенным шансом ловли.' AS tittle UNION ALL
    SELECT 64, 'Камень рассвета', 'Эволюционный камень, пробуждающий светлую форму некоторых покемонов.' UNION ALL
    SELECT 66, 'Сумрачный камень', 'Эволюционный камень, связанный с темными и ночными эволюциями.' UNION ALL
    SELECT 67, 'Огненный камень', 'Эволюционный камень с силой огненного типа.' UNION ALL
    SELECT 68, 'Листовой камень', 'Эволюционный камень с силой травяного типа.' UNION ALL
    SELECT 74, 'Солнечный камень', 'Эволюционный камень, наполненный солнечной энергией.' UNION ALL
    SELECT 75, 'Громовой камень', 'Эволюционный камень с электрической энергией.' UNION ALL
    SELECT 76, 'Ледяной камень', 'Эволюционный камень с холодной энергией льда.' UNION ALL
    SELECT 77, 'Водяной камень', 'Эволюционный камень с энергией водного типа.' UNION ALL
    SELECT 78, 'Случайная TM-атака', 'Случайная техническая машина с атакой для обучения покемона.' UNION ALL
    SELECT 217, 'Витамины PP', 'Редкие витамины, используемые для восстановления или усиления PP.' UNION ALL
    SELECT 234, 'Черная флейта', 'Флейта с темным звуком для игровых эффектов и событий.' UNION ALL
    SELECT 235, 'Белая флейта', 'Флейта со светлым звуком для игровых эффектов и событий.' UNION ALL
    SELECT 325, 'Лист разума', 'Редкий лист, связанный с тренировкой разума покемона.' UNION ALL
    SELECT 371, 'Увеличитель линзы', 'Предмет, повышающий точность и внимательность в бою.' UNION ALL
    SELECT 651, 'Красная конфета', 'Редкая конфета красного цвета для развития покемона.' UNION ALL
    SELECT 652, 'Розовая конфета', 'Редкая конфета розового цвета для развития покемона.' UNION ALL
    SELECT 653, 'Шоколадная конфета', 'Редкая шоколадная конфета для развития покемона.' UNION ALL
    SELECT 654, 'Серая конфета', 'Редкая серая конфета для развития покемона.' UNION ALL
    SELECT 655, 'Необычная конфета', 'Необычная конфета с особым игровым эффектом.' UNION ALL
    SELECT 861, 'Кекс с ягодами Церуго', 'Кекс с ягодами Церуго для восстановления сил.' UNION ALL
    SELECT 1401, 'Золотая конфета', 'Очень редкая золотая конфета для ценного усиления покемона.' UNION ALL
    SELECT 90001, 'Ордер Команды R I', 'Ордер первого уровня для принудительного PvP-нападения по правилам кармы.' UNION ALL
    SELECT 90002, 'Ордер Команды R II', 'Ордер второго уровня для принудительного PvP-нападения по правилам кармы.' UNION ALL
    SELECT 90003, 'Ордер протекции', 'Ордер защиты локации. Эффект будет подключен отдельным блоком.' UNION ALL
    SELECT 90004, 'Мастербол', 'Редкий шар с максимальным шансом ловли дикого покемона.'
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM items existing WHERE existing.id = seed.id);

UPDATE items item
JOIN (
    SELECT 25 AS id, 'Премиум бол' AS name, 'Улучшенный покебол с повышенным шансом ловли.' AS tittle UNION ALL
    SELECT 64, 'Камень рассвета', 'Эволюционный камень, пробуждающий светлую форму некоторых покемонов.' UNION ALL
    SELECT 66, 'Сумрачный камень', 'Эволюционный камень, связанный с темными и ночными эволюциями.' UNION ALL
    SELECT 67, 'Огненный камень', 'Эволюционный камень с силой огненного типа.' UNION ALL
    SELECT 68, 'Листовой камень', 'Эволюционный камень с силой травяного типа.' UNION ALL
    SELECT 74, 'Солнечный камень', 'Эволюционный камень, наполненный солнечной энергией.' UNION ALL
    SELECT 75, 'Громовой камень', 'Эволюционный камень с электрической энергией.' UNION ALL
    SELECT 76, 'Ледяной камень', 'Эволюционный камень с холодной энергией льда.' UNION ALL
    SELECT 77, 'Водяной камень', 'Эволюционный камень с энергией водного типа.' UNION ALL
    SELECT 78, 'Случайная TM-атака', 'Случайная техническая машина с атакой для обучения покемона.' UNION ALL
    SELECT 217, 'Витамины PP', 'Редкие витамины, используемые для восстановления или усиления PP.' UNION ALL
    SELECT 234, 'Черная флейта', 'Флейта с темным звуком для игровых эффектов и событий.' UNION ALL
    SELECT 235, 'Белая флейта', 'Флейта со светлым звуком для игровых эффектов и событий.' UNION ALL
    SELECT 325, 'Лист разума', 'Редкий лист, связанный с тренировкой разума покемона.' UNION ALL
    SELECT 371, 'Увеличитель линзы', 'Предмет, повышающий точность и внимательность в бою.' UNION ALL
    SELECT 651, 'Красная конфета', 'Редкая конфета красного цвета для развития покемона.' UNION ALL
    SELECT 652, 'Розовая конфета', 'Редкая конфета розового цвета для развития покемона.' UNION ALL
    SELECT 653, 'Шоколадная конфета', 'Редкая шоколадная конфета для развития покемона.' UNION ALL
    SELECT 654, 'Серая конфета', 'Редкая серая конфета для развития покемона.' UNION ALL
    SELECT 655, 'Необычная конфета', 'Необычная конфета с особым игровым эффектом.' UNION ALL
    SELECT 861, 'Кекс с ягодами Церуго', 'Кекс с ягодами Церуго для восстановления сил.' UNION ALL
    SELECT 1401, 'Золотая конфета', 'Очень редкая золотая конфета для ценного усиления покемона.' UNION ALL
    SELECT 90001, 'Ордер Команды R I', 'Ордер первого уровня для принудительного PvP-нападения по правилам кармы.' UNION ALL
    SELECT 90002, 'Ордер Команды R II', 'Ордер второго уровня для принудительного PvP-нападения по правилам кармы.' UNION ALL
    SELECT 90003, 'Ордер протекции', 'Ордер защиты локации. Эффект будет подключен отдельным блоком.' UNION ALL
    SELECT 90004, 'Мастербол', 'Редкий шар с максимальным шансом ловли дикого покемона.'
) AS seed ON seed.id = item.id
SET item.name = seed.name,
    item.tittle = seed.tittle,
    item.uses = 1,
    item.delet = 1,
    item.torg = 1;

INSERT INTO market_shop_items (
    item_id, currency_item_id, price, min_count, max_count, max_owned,
    stock, enabled, sort_order, note, created_at, updated_at
)
SELECT seed.item_id, 1, seed.price, 1, seed.max_count, 0, -1, 1, seed.sort_order, seed.note, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
FROM (
    SELECT 25 AS item_id, 1200 AS price, 99 AS max_count, 300 AS sort_order, 'plan:pokeballs' AS note UNION ALL
    SELECT 90004, 500000, 10, 310, 'plan:pokeballs' UNION ALL
    SELECT 64, 80000, 99, 320, 'plan:evolution_stones' UNION ALL
    SELECT 66, 80000, 99, 330, 'plan:evolution_stones' UNION ALL
    SELECT 67, 80000, 99, 340, 'plan:evolution_stones' UNION ALL
    SELECT 68, 80000, 99, 350, 'plan:evolution_stones' UNION ALL
    SELECT 74, 80000, 99, 360, 'plan:evolution_stones' UNION ALL
    SELECT 75, 80000, 99, 370, 'plan:evolution_stones' UNION ALL
    SELECT 76, 80000, 99, 380, 'plan:evolution_stones' UNION ALL
    SELECT 77, 80000, 99, 390, 'plan:evolution_stones' UNION ALL
    SELECT 78, 150000, 99, 400, 'plan:tm' UNION ALL
    SELECT 217, 120000, 99, 410, 'plan:vitamins' UNION ALL
    SELECT 325, 200000, 99, 420, 'plan:battle_items' UNION ALL
    SELECT 371, 250000, 99, 430, 'plan:battle_items' UNION ALL
    SELECT 651, 100000, 99, 440, 'plan:candies' UNION ALL
    SELECT 652, 100000, 99, 450, 'plan:candies' UNION ALL
    SELECT 653, 150000, 99, 460, 'plan:candies' UNION ALL
    SELECT 654, 150000, 99, 470, 'plan:candies' UNION ALL
    SELECT 655, 150000, 99, 480, 'plan:candies' UNION ALL
    SELECT 1401, 500000, 99, 490, 'plan:candies' UNION ALL
    SELECT 235, 250000, 99, 500, 'plan:flutes' UNION ALL
    SELECT 234, 250000, 99, 510, 'plan:flutes' UNION ALL
    SELECT 861, 125000, 99, 520, 'plan:food' UNION ALL
    SELECT 90001, 400000, 10, 530, 'plan:karma_warrants' UNION ALL
    SELECT 90002, 750000, 10, 540, 'plan:karma_warrants' UNION ALL
    SELECT 90003, 600000, 10, 550, 'plan:karma_warrants'
) AS seed
ON DUPLICATE KEY UPDATE
    currency_item_id = VALUES(currency_item_id),
    price = VALUES(price),
    min_count = VALUES(min_count),
    max_count = VALUES(max_count),
    max_owned = VALUES(max_owned),
    stock = VALUES(stock),
    enabled = VALUES(enabled),
    sort_order = VALUES(sort_order),
    note = VALUES(note),
    updated_at = UNIX_TIMESTAMP();
