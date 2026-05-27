-- Plane ticket flights.
-- Idempotent: adds premium plane ticket, regional flight routes and active flight state.

UPDATE items
   SET name = 'Билет на самолёт',
       tittle = 'Премиальный билет для перелёта между регионами. Откройте инвентарь, выберите рейс и отправляйтесь в путь.',
       uses = 1,
       delet = 1,
       torg = 1,
       battleuse = 0,
       dopolnen = 'plane'
 WHERE id = 90111;

INSERT INTO items (id, cools, name, tittle, category, uses, dress, delet, torg, elementary, timesnapoke, times, battleuse, dopolnen)
SELECT 90111, 0, 'Билет на самолёт', 'Премиальный билет для перелёта между регионами. Откройте инвентарь, выберите рейс и отправляйтесь в путь.', 0, 1, 0, 1, 1, 0, 1, '0', 0, 'plane'
  FROM DUAL
 WHERE NOT EXISTS (SELECT 1 FROM items WHERE id = 90111);

INSERT INTO market_shop_items (
    item_id, currency_item_id, price, min_count, max_count, max_owned,
    stock, enabled, sort_order, note, created_at, updated_at
)
SELECT 90111, 2, 50, 1, 20, 0, -1, 1, 20, 'premium:plane_ticket', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
  FROM DUAL
 WHERE EXISTS (SELECT 1 FROM items WHERE id = 90111)
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

INSERT INTO item_target_rules (
    item_id, enabled, target_type, allow_quantity, min_count, max_count,
    effect_key, consume_on_success, ui_title, ui_hint, created_at, updated_at
)
VALUES (
    90111, 1, 'flight', 0, 1, 1,
    'plane_ticket', 0, 'Выбрать рейс', 'Выберите регион назначения. Полёт длится 15 минут.',
    UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
)
ON DUPLICATE KEY UPDATE
    enabled = VALUES(enabled),
    target_type = VALUES(target_type),
    allow_quantity = VALUES(allow_quantity),
    min_count = VALUES(min_count),
    max_count = VALUES(max_count),
    effect_key = VALUES(effect_key),
    consume_on_success = VALUES(consume_on_success),
    ui_title = VALUES(ui_title),
    ui_hint = VALUES(ui_hint),
    updated_at = UNIX_TIMESTAMP();

CREATE TABLE IF NOT EXISTS transport_flight_routes (
    id INT NOT NULL AUTO_INCREMENT,
    from_region INT NOT NULL DEFAULT 0,
    to_region INT NOT NULL,
    to_location_id INT NOT NULL,
    title VARCHAR(120) NOT NULL,
    description TEXT NOT NULL,
    duration_seconds INT NOT NULL DEFAULT 900,
    enabled TINYINT NOT NULL DEFAULT 1,
    created_at INT NOT NULL DEFAULT 0,
    updated_at INT NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_transport_flight_destination (to_region, to_location_id),
    KEY idx_transport_flight_enabled (enabled, from_region, to_region)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS transport_flights (
    id INT NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    item_user_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
    route_id INT NOT NULL,
    from_location_id INT NOT NULL DEFAULT 0,
    from_region INT NOT NULL DEFAULT 0,
    to_region INT NOT NULL DEFAULT 0,
    destination_location_id INT NOT NULL DEFAULT 0,
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    started_at INT NOT NULL DEFAULT 0,
    arrives_at INT NOT NULL DEFAULT 0,
    exited_at INT NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY idx_transport_flights_user_status (user_id, status),
    KEY idx_transport_flights_arrives (arrives_at),
    KEY idx_transport_flights_route (route_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO transport_flight_routes (from_region, to_region, to_location_id, title, description, duration_seconds, enabled, created_at, updated_at)
VALUES
    (0, 1, 1, 'Рейс в Канто', 'Самолёт доставит вас в Алабастию, стартовую локацию региона Канто.', 900, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    (0, 3, 25, 'Рейс в Джотто', 'Самолёт доставит вас в Порт Джотто, стартовую точку региона Джотто.', 900, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    (0, 4, 101, 'Рейс в Хоэнн', 'Самолёт доставит вас в Литтлруд, стартовую локацию региона Хоэнн.', 900, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
    from_region = VALUES(from_region),
    title = VALUES(title),
    description = VALUES(description),
    duration_seconds = VALUES(duration_seconds),
    enabled = VALUES(enabled),
    updated_at = UNIX_TIMESTAMP();

INSERT INTO build (id, town, title, tipe, pve, zax)
SELECT 95001, 0, 'На борту самолёта', 0, 0, 0
  FROM DUAL
 WHERE NOT EXISTS (SELECT 1 FROM build WHERE id = 95001);
