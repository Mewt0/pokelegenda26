CREATE TABLE IF NOT EXISTS transport_routes (
  id INT NOT NULL AUTO_INCREMENT,
  kind VARCHAR(24) NOT NULL,
  from_location_id INT NOT NULL,
  to_location_id INT NOT NULL,
  title VARCHAR(120) NOT NULL,
  item_id INT NOT NULL DEFAULT 0,
  price_item_id INT NOT NULL DEFAULT 1,
  price_count INT NOT NULL DEFAULT 0,
  enabled TINYINT NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  KEY idx_transport_from (from_location_id, enabled),
  KEY idx_transport_to (to_location_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DELETE FROM transport_routes WHERE kind IN ('ship', 'plane') AND item_id IN (90110, 90111);
DELETE FROM items WHERE id IN (90110, 90111);

INSERT INTO items (id, cools, name, tittle, category, uses, dress, delet, torg, elementary, timesnapoke, times, battleuse, dopolnen)
VALUES
  (90110, 0, 'Билет на пароход', 'Билет для морского рейса между регионами.', 0, 0, 0, 0, 1, 0, 0, '0', 0, 'transport_ship'),
  (90111, 0, 'Билет на самолёт', 'Билет для воздушного рейса между регионами.', 0, 0, 0, 0, 1, 0, 0, '0', 0, 'transport_plane');

INSERT INTO transport_routes (kind, from_location_id, to_location_id, title, item_id, price_item_id, price_count, enabled)
VALUES
  ('ship', 23, 25, 'Пароход: Порт Церулина -> Порт Джотто', 90110, 1, 5000, 1),
  ('ship', 25, 23, 'Пароход: Порт Джотто -> Порт Церулина', 90110, 1, 5000, 1),
  ('plane', 100, 101, 'Самолёт: Канто -> Хоэнн', 90111, 1, 15000, 1),
  ('plane', 101, 100, 'Самолёт: Хоэнн -> Канто', 90111, 1, 15000, 1);
