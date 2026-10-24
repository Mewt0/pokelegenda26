-- В локации 5 суммарный шанс дропа вышел 114%: перья (по 22%) легли поверх
-- обычного набора 70%. Снижаем перья до 12% каждое — итого 94%, и квест #7
-- всё ещё выполним (нужно 10+10 перьев, лес даёт их стабильно).
SET NAMES utf8mb4;

UPDATE admin_drop_rules SET chance_percent = 12.0000 WHERE item_id = 13 AND location_id = 5;
UPDATE admin_drop_rules SET chance_percent = 12.0000 WHERE item_id = 14 AND location_id = 5;
