-- Import attacks missing from pokemon_moves_by_gen.xlsx by normalized attack name.
-- Existing attacks are not updated: this migration only inserts rows whose name is absent.
-- Name aliases treated as existing during generation: High Jump Kick/Hi Jump Kick, Vise Grip/ViceGrip, Smelling Salts/SmellingSalt, Feint Attack/Faint Attack.

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1000, 'Aromatic Mist', 'Fairy', 3, 20, 0, 0, 1, 'Статусная атака волшебного типа. Повышает один или несколько параметров пользователя.', 'Повышает один или несколько параметров пользователя.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'aromaticmist')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1000);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1001, 'Baby-Doll Eyes', 'Fairy', 3, 30, 0, 100, 1, 'Статусная атака волшебного типа. Почти всегда ходит первой. Понижает один или несколько параметров цели.', 'Почти всегда ходит первой. Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'babydolleyes')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1001);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1002, 'Belch', 'Poison', 2, 10, 120, 90, 1, 'Специальная атака ядовитого типа с силой 120. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'belch')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1002);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1003, 'Boomburst', 'Normal', 2, 10, 140, 100, 1, 'Специальная атака обычного типа с силой 140. Бьет всех соседних покемонов.', 'Бьет всех соседних покемонов.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'boomburst')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1003);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1004, 'Celebrate', 'Normal', 3, 40, 0, 0, 1, 'Статусная атака обычного типа. Особый эффект поздних поколений: требует точной настройки в боевом движке. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'celebrate')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1004);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1005, 'Confide', 'Normal', 3, 20, 0, 0, 1, 'Статусная атака обычного типа. Понижает Спец. атаку цели.', 'Понижает Спец. атаку цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'confide')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1005);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1006, 'Crafty Shield', 'Fairy', 3, 10, 0, 0, 1, 'Статусная атака волшебного типа. Дает защитный эффект на этот ход.', 'Дает защитный эффект на этот ход.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'craftyshield')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1006);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1007, 'Dazzling Gleam', 'Fairy', 2, 10, 80, 100, 1, 'Специальная атака волшебного типа с силой 80. Бьет всех соседних противников.', 'Бьет всех соседних противников.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'dazzlinggleam')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1007);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1008, 'Diamond Storm', 'Rock', 1, 5, 100, 95, 1, 'Физическая атака каменного типа с силой 100. Повышает один или несколько параметров пользователя.', 'Повышает один или несколько параметров пользователя.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'diamondstorm')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1008);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1009, 'Disarming Voice', 'Fairy', 2, 15, 40, 0, 1, 'Специальная атака волшебного типа с силой 40. Игнорирует точность и уклонение.', 'Игнорирует точность и уклонение.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'disarmingvoice')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1009);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1010, 'Dragon Ascent', 'Flying', 1, 5, 120, 100, 1, 'Физическая атака летающего типа с силой 120. Понижает один или несколько параметров цели.', 'Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'dragonascent')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1010);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1011, 'Draining Kiss', 'Fairy', 2, 10, 50, 100, 1, 'Специальная атака волшебного типа с силой 50. Пользователь восстанавливает большую часть нанесенного урона в HP.', 'Пользователь восстанавливает большую часть нанесенного урона в HP.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'drainingkiss')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1011);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1012, 'Eerie Impulse', 'Electric', 3, 15, 0, 100, 1, 'Статусная атака электрического типа. Понижает один или несколько параметров цели.', 'Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'eerieimpulse')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1012);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1013, 'Electric Terrain', 'Electric', 3, 10, 0, 0, 1, 'Статусная атака электрического типа. Может взаимодействовать со сном.', 'Может взаимодействовать со сном.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'electricterrain')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1013);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1014, 'Electrify', 'Electric', 3, 20, 0, 0, 1, 'Статусная атака электрического типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'electrify')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1014);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1015, 'Fairy Lock', 'Fairy', 3, 10, 0, 0, 1, 'Статусная атака волшебного типа. Влияет на смену покемона или возможность отступления.', 'Влияет на смену покемона или возможность отступления.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'fairylock')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1015);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1016, 'Fairy Wind', 'Fairy', 2, 30, 40, 100, 1, 'Специальная атака волшебного типа с силой 40. Наносит обычный урон без дополнительного эффекта.', 'Наносит обычный урон без дополнительного эффекта.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'fairywind')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1016);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1017, 'Fell Stinger', 'Bug', 1, 25, 50, 100, 1, 'Физическая атака насекомого типа с силой 50. Повышает один или несколько параметров пользователя.', 'Повышает один или несколько параметров пользователя.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'fellstinger')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1017);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1018, 'Flower Shield', 'Fairy', 3, 10, 0, 0, 1, 'Статусная атака волшебного типа. Повышает один или несколько параметров пользователя.', 'Повышает один или несколько параметров пользователя.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'flowershield')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1018);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1019, 'Flying Press', 'Fighting', 1, 10, 100, 95, 1, 'Физическая атака боевого типа с силой 100. Наносит урон с дополнительным особым эффектом.', 'Наносит урон с дополнительным особым эффектом.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'flyingpress')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1019);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1020, 'Forest''s Curse', 'Grass', 3, 20, 0, 100, 1, 'Статусная атака травяного типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'forestscurse')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1020);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1021, 'Freeze-Dry', 'Ice', 2, 20, 70, 100, 1, 'Специальная атака ледяного типа с силой 70. Может заморозить цель. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Может заморозить цель. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'freezedry')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1021);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1022, 'Geomancy', 'Fairy', 3, 10, 0, 0, 1, 'Статусная атака волшебного типа. Повышает один или несколько параметров пользователя. Особый эффект поздних поколений: требует точной настройки в боевом движке. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Повышает один или несколько параметров пользователя. Особый эффект поздних поколений: требует точной настройки в боевом движке. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'geomancy')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1022);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1023, 'Grassy Terrain', 'Grass', 3, 10, 0, 0, 1, 'Статусная атака травяного типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'grassyterrain')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1023);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1024, 'Happy Hour', 'Normal', 3, 30, 0, 0, 1, 'Статусная атака обычного типа. Влияет на погоду и связанные с ней бонусы.', 'Влияет на погоду и связанные с ней бонусы.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'happyhour')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1024);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1025, 'Hold Back', 'Normal', 1, 40, 40, 100, 1, 'Физическая атака обычного типа с силой 40. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'holdback')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1025);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1026, 'Hold Hands', 'Normal', 3, 40, 0, 0, 1, 'Статусная атака обычного типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'holdhands')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1026);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1027, 'Hyperspace Fury', 'Dark', 1, 5, 100, 0, 1, 'Физическая атака темного типа с силой 100. Понижает один или несколько параметров цели. Дает защитный эффект на этот ход.', 'Понижает один или несколько параметров цели. Дает защитный эффект на этот ход.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'hyperspacefury')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1027);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1028, 'Hyperspace Hole', 'Psychic', 2, 5, 80, 0, 1, 'Специальная атака психического типа с силой 80. Дает защитный эффект на этот ход.', 'Дает защитный эффект на этот ход.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'hyperspacehole')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1028);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1029, 'Infestation', 'Bug', 2, 20, 20, 100, 1, 'Специальная атака насекомого типа с силой 20. Удерживает цель и наносит ей урон 4-5 ходов.', 'Удерживает цель и наносит ей урон 4-5 ходов.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'infestation')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1029);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1030, 'Ion Deluge', 'Electric', 3, 25, 0, 0, 1, 'Статусная атака электрического типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'iondeluge')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1030);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1031, 'King''s Shield', 'Steel', 3, 10, 0, 0, 1, 'Статусная атака стального типа. Дает защитный эффект на этот ход.', 'Дает защитный эффект на этот ход.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'kingsshield')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1031);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1032, 'Land''s Wrath', 'Ground', 1, 10, 90, 100, 1, 'Физическая атака земляного типа с силой 90. Наносит обычный урон без дополнительного эффекта.', 'Наносит обычный урон без дополнительного эффекта.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'landswrath')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1032);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1033, 'Light of Ruin', 'Fairy', 2, 5, 140, 90, 1, 'Специальная атака волшебного типа с силой 140. Пользователь получает урон отдачи.', 'Пользователь получает урон отдачи.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'lightofruin')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1033);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1034, 'Magnetic Flux', 'Electric', 3, 20, 0, 0, 1, 'Статусная атака электрического типа. Повышает один или несколько параметров пользователя. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Повышает один или несколько параметров пользователя. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'magneticflux')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1034);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1035, 'Mat Block', 'Fighting', 3, 10, 0, 0, 1, 'Статусная атака боевого типа. Дает защитный эффект на этот ход.', 'Дает защитный эффект на этот ход.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'matblock')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1035);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1036, 'Misty Terrain', 'Fairy', 3, 10, 0, 0, 1, 'Статусная атака волшебного типа. Дает защитный эффект на этот ход.', 'Дает защитный эффект на этот ход.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'mistyterrain')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1036);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1037, 'Moonblast', 'Fairy', 2, 15, 95, 100, 1, 'Специальная атака волшебного типа с силой 95. Может понизить Спец. атаку цели.', 'Может понизить Спец. атаку цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'moonblast')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1037);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1038, 'Mystical Fire', 'Fire', 2, 10, 75, 100, 1, 'Специальная атака огненного типа с силой 75. Понижает Спец. атаку цели.', 'Понижает Спец. атаку цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'mysticalfire')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1038);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1039, 'Noble Roar', 'Normal', 3, 30, 0, 100, 1, 'Статусная атака обычного типа. Понижает один или несколько параметров цели.', 'Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'nobleroar')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1039);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1040, 'Nuzzle', 'Electric', 1, 20, 20, 100, 1, 'Физическая атака электрического типа с силой 20. Парализует цель.', 'Парализует цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'nuzzle')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1040);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1041, 'Oblivion Wing', 'Flying', 2, 10, 80, 100, 1, 'Специальная атака летающего типа с силой 80. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'oblivionwing')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1041);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1042, 'Origin Pulse', 'Water', 2, 10, 110, 85, 1, 'Специальная атака водного типа с силой 110. Бьет всех соседних противников.', 'Бьет всех соседних противников.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'originpulse')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1042);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1043, 'Parabolic Charge', 'Electric', 2, 20, 65, 100, 1, 'Специальная атака электрического типа с силой 65. Пользователь восстанавливает половину нанесенного урона в HP.', 'Пользователь восстанавливает половину нанесенного урона в HP.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'paraboliccharge')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1043);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1044, 'Parting Shot', 'Dark', 3, 20, 0, 100, 1, 'Статусная атака темного типа. Влияет на смену покемона или возможность отступления.', 'Влияет на смену покемона или возможность отступления.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'partingshot')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1044);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1045, 'Petal Blizzard', 'Grass', 1, 15, 90, 100, 1, 'Физическая атака травяного типа с силой 90. Бьет всех соседних покемонов.', 'Бьет всех соседних покемонов.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'petalblizzard')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1045);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1046, 'Phantom Force', 'Ghost', 1, 10, 90, 100, 1, 'Физическая атака призрачного типа с силой 90. Особый эффект поздних поколений: требует точной настройки в боевом движке. Дает защитный эффект на этот ход.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке. Дает защитный эффект на этот ход.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'phantomforce')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1046);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1047, 'Play Nice', 'Normal', 3, 20, 0, 0, 1, 'Статусная атака обычного типа. Понижает Атаку цели. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Понижает Атаку цели. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'playnice')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1047);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1048, 'Play Rough', 'Fairy', 1, 10, 90, 90, 1, 'Физическая атака волшебного типа с силой 90. Может понизить Атаку цели.', 'Может понизить Атаку цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'playrough')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1048);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1049, 'Powder', 'Bug', 3, 20, 0, 100, 1, 'Статусная атака насекомого типа. Наносит урон с дополнительным особым эффектом.', 'Наносит урон с дополнительным особым эффектом.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'powder')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1049);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1050, 'Power-Up Punch', 'Fighting', 1, 20, 40, 100, 1, 'Физическая атака боевого типа с силой 40. Повышает один или несколько параметров пользователя.', 'Повышает один или несколько параметров пользователя.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'poweruppunch')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1050);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1051, 'Precipice Blades', 'Ground', 1, 10, 120, 85, 1, 'Физическая атака земляного типа с силой 120. Бьет всех соседних противников.', 'Бьет всех соседних противников.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'precipiceblades')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1051);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1052, 'Rototiller', 'Ground', 3, 10, 0, 0, 1, 'Статусная атака земляного типа. Повышает один или несколько параметров пользователя.', 'Повышает один или несколько параметров пользователя.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'rototiller')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1052);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1053, 'Spiky Shield', 'Grass', 3, 10, 0, 0, 1, 'Статусная атака травяного типа. Дает защитный эффект на этот ход.', 'Дает защитный эффект на этот ход.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'spikyshield')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1053);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1054, 'Steam Eruption', 'Water', 2, 5, 110, 95, 1, 'Специальная атака водного типа с силой 110. Может поджечь цель.', 'Может поджечь цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'steameruption')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1054);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1055, 'Sticky Web', 'Bug', 3, 20, 0, 0, 1, 'Статусная атака насекомого типа. Снижает Скорость противника при выходе в бой.', 'Снижает Скорость противника при выходе в бой.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'stickyweb')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1055);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1056, 'Thousand Arrows', 'Ground', 1, 10, 90, 100, 1, 'Физическая атака земляного типа с силой 90. Делает летающих покемонов уязвимыми к земляным атакам.', 'Делает летающих покемонов уязвимыми к земляным атакам.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'thousandarrows')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1056);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1057, 'Thousand Waves', 'Ground', 1, 10, 90, 100, 1, 'Физическая атака земляного типа с силой 90. Цель не может сбежать или смениться.', 'Цель не может сбежать или смениться.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'thousandwaves')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1057);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1058, 'Topsy-Turvy', 'Dark', 3, 20, 0, 0, 1, 'Статусная атака темного типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'topsyturvy')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1058);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1059, 'Trick-or-Treat', 'Ghost', 3, 20, 0, 100, 1, 'Статусная атака призрачного типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'trickortreat')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1059);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1060, 'Venom Drench', 'Poison', 3, 20, 0, 100, 1, 'Статусная атака ядовитого типа. Может отравить цель.', 'Может отравить цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'venomdrench')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1060);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1061, 'Water Shuriken', 'Water', 2, 20, 15, 100, 1, 'Специальная атака водного типа с силой 15. Бьет 2-5 раз за один ход.', 'Бьет 2-5 раз за один ход.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'watershuriken')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1061);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1062, '10,000,000 Volt Thunderbolt', 'Electric', 2, 1, 195, 0, 1, 'Специальная атака электрического типа с силой 195. Особый эффект поздних поколений: требует точной настройки в боевом движке. Влияет на шанс критического удара.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке. Влияет на шанс критического удара.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = '10000000voltthunderbolt')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1062);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1063, 'Accelerock', 'Rock', 1, 20, 40, 100, 1, 'Физическая атака каменного типа с силой 40. Пользователь атакует первым.', 'Пользователь атакует первым.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'accelerock')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1063);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1064, 'Acid Downpour', 'Poison', 0, 1, 0, 0, 1, 'Особая атака ядовитого типа. Z-атака ядовитого типа.', 'Z-атака ядовитого типа.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'aciddownpour')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1064);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1065, 'All-Out Pummeling', 'Fighting', 0, 1, 0, 0, 1, 'Особая атака боевого типа. Z-атака боевого типа.', 'Z-атака боевого типа.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'alloutpummeling')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1065);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1066, 'Anchor Shot', 'Steel', 1, 20, 80, 100, 1, 'Физическая атака стального типа с силой 80. Особый эффект поздних поколений: требует точной настройки в боевом движке. Влияет на смену покемона или возможность отступления.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке. Влияет на смену покемона или возможность отступления.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'anchorshot')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1066);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1067, 'Aurora Veil', 'Ice', 3, 20, 0, 0, 1, 'Статусная атака ледяного типа. Наносит урон с дополнительным особым эффектом.', 'Наносит урон с дополнительным особым эффектом.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'auroraveil')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1067);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1068, 'Baddy Bad', 'Dark', 2, 15, 90, 100, 1, 'Специальная атака темного типа с силой 90. Наносит урон с дополнительным особым эффектом.', 'Наносит урон с дополнительным особым эффектом.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'baddybad')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1068);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1069, 'Baneful Bunker', 'Poison', 3, 10, 0, 0, 1, 'Статусная атака ядовитого типа. Защищает пользователя и отравляет противника при контакте.', 'Защищает пользователя и отравляет противника при контакте.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'banefulbunker')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1069);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1070, 'Beak Blast', 'Flying', 1, 15, 100, 100, 1, 'Физическая атака летающего типа с силой 100. Особый эффект поздних поколений: требует точной настройки в боевом движке. Может поджечь цель.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке. Может поджечь цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'beakblast')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1070);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1071, 'Black Hole Eclipse', 'Dark', 0, 1, 0, 0, 1, 'Особая атака темного типа. Z-атака темного типа.', 'Z-атака темного типа.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'blackholeeclipse')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1071);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1072, 'Bloom Doom', 'Grass', 0, 1, 0, 0, 1, 'Особая атака травяного типа. Z-атака травяного типа.', 'Z-атака травяного типа.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'bloomdoom')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1072);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1073, 'Bouncy Bubble', 'Water', 2, 15, 90, 100, 1, 'Специальная атака водного типа с силой 90. Пользователь восстанавливает половину нанесенного урона в HP.', 'Пользователь восстанавливает половину нанесенного урона в HP.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'bouncybubble')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1073);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1074, 'Breakneck Blitz', 'Normal', 0, 1, 0, 0, 1, 'Особая атака обычного типа. Z-атака обычного типа.', 'Z-атака обычного типа.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'breakneckblitz')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1074);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1075, 'Brutal Swing', 'Dark', 1, 20, 60, 100, 1, 'Физическая атака темного типа с силой 60. Наносит урон с дополнительным особым эффектом.', 'Наносит урон с дополнительным особым эффектом.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'brutalswing')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1075);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1076, 'Burn Up', 'Fire', 2, 5, 130, 100, 1, 'Специальная атака огненного типа с силой 130. Может поджечь цель. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Может поджечь цель. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'burnup')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1076);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1077, 'Buzzy Buzz', 'Electric', 2, 15, 90, 100, 1, 'Специальная атака электрического типа с силой 90. Может парализовать цель.', 'Может парализовать цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'buzzybuzz')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1077);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1078, 'Catastropika', 'Electric', 1, 1, 210, 0, 1, 'Физическая атака электрического типа с силой 210. Эксклюзивная Z-атака для Pikachu.', 'Эксклюзивная Z-атака для Pikachu.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'catastropika')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1078);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1079, 'Clanging Scales', 'Dragon', 2, 5, 110, 100, 1, 'Специальная атака драконьего типа с силой 110. Понижает один или несколько параметров цели.', 'Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'clangingscales')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1079);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1080, 'Clangorous Soulblaze', 'Dragon', 2, 1, 185, 0, 1, 'Специальная атака драконьего типа с силой 185. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'clangoroussoulblaze')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1080);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1081, 'Continental Crush', 'Rock', 0, 1, 0, 0, 1, 'Особая атака каменного типа. Z-атака каменного типа.', 'Z-атака каменного типа.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'continentalcrush')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1081);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1082, 'Core Enforcer', 'Dragon', 2, 10, 100, 100, 1, 'Специальная атака драконьего типа с силой 100. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'coreenforcer')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1082);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1083, 'Corkscrew Crash', 'Steel', 0, 1, 0, 0, 1, 'Особая атака стального типа. Z-атака стального типа.', 'Z-атака стального типа.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'corkscrewcrash')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1083);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1084, 'Darkest Lariat', 'Dark', 1, 10, 85, 100, 1, 'Физическая атака темного типа с силой 85. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'darkestlariat')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1084);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1085, 'Devastating Drake', 'Dragon', 0, 1, 0, 0, 1, 'Особая атака драконьего типа. Z-атака драконьего типа.', 'Z-атака драконьего типа.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'devastatingdrake')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1085);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1086, 'Double Iron Bash', 'Steel', 1, 5, 60, 100, 1, 'Физическая атака стального типа с силой 60. Может испугать цель.', 'Может испугать цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'doubleironbash')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1086);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1087, 'Dragon Hammer', 'Dragon', 1, 15, 90, 100, 1, 'Физическая атака драконьего типа с силой 90. Наносит урон с дополнительным особым эффектом.', 'Наносит урон с дополнительным особым эффектом.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'dragonhammer')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1087);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1088, 'Extreme Evoboost', 'Normal', 3, 1, 0, 0, 1, 'Статусная атака обычного типа. Особый эффект поздних поколений: требует точной настройки в боевом движке. Повышает один или несколько параметров пользователя.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке. Повышает один или несколько параметров пользователя.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'extremeevoboost')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1088);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1089, 'Fire Lash', 'Fire', 1, 15, 80, 100, 1, 'Физическая атака огненного типа с силой 80. Может поджечь цель. Понижает один или несколько параметров цели.', 'Может поджечь цель. Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'firelash')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1089);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1090, 'First Impression', 'Bug', 1, 10, 90, 100, 1, 'Физическая атака насекомого типа с силой 90. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'firstimpression')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1090);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1091, 'Fleur Cannon', 'Fairy', 2, 5, 130, 90, 1, 'Специальная атака волшебного типа с силой 130. Понижает один или несколько параметров цели.', 'Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'fleurcannon')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1091);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1092, 'Floaty Fall', 'Flying', 1, 15, 90, 95, 1, 'Физическая атака летающего типа с силой 90. Может испугать цель.', 'Может испугать цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'floatyfall')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1092);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1093, 'Floral Healing', 'Fairy', 3, 10, 0, 0, 1, 'Статусная атака волшебного типа. Особый эффект поздних поколений: требует точной настройки в боевом движке. Меняет поле боя и включает особый эффект местности.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке. Меняет поле боя и включает особый эффект местности.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'floralhealing')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1093);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1094, 'Freezy Frost', 'Ice', 2, 15, 90, 100, 1, 'Специальная атака ледяного типа с силой 90. Сбрасывает все изменения статов.', 'Сбрасывает все изменения статов.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'freezyfrost')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1094);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1095, 'Gear Up', 'Steel', 3, 20, 0, 0, 1, 'Статусная атака стального типа. Повышает один или несколько параметров пользователя. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Повышает один или несколько параметров пользователя. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gearup')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1095);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1096, 'Genesis Supernova', 'Psychic', 2, 1, 185, 0, 1, 'Специальная атака психического типа с силой 185. Эксклюзивная Z-атака для Mew.', 'Эксклюзивная Z-атака для Mew.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'genesissupernova')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1096);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1097, 'Gigavolt Havoc', 'Electric', 0, 1, 0, 0, 1, 'Особая атака электрического типа. Z-атака электрического типа.', 'Z-атака электрического типа.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gigavolthavoc')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1097);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1098, 'Glitzy Glow', 'Psychic', 2, 15, 90, 100, 1, 'Специальная атака психического типа с силой 90. Наносит урон с дополнительным особым эффектом.', 'Наносит урон с дополнительным особым эффектом.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'glitzyglow')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1098);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1099, 'Guardian of Alola', 'Fairy', 2, 1, 0, 0, 1, 'Специальная атака волшебного типа. Особый эффект поздних поколений: требует точной настройки в боевом движке. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'guardianofalola')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1099);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1100, 'High Horsepower', 'Ground', 1, 10, 95, 95, 1, 'Физическая атака земляного типа с силой 95. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'highhorsepower')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1100);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1101, 'Hydro Vortex', 'Water', 0, 1, 0, 0, 1, 'Особая атака водного типа. Z-атака водного типа.', 'Z-атака водного типа.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'hydrovortex')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1101);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1102, 'Ice Hammer', 'Ice', 1, 10, 100, 90, 1, 'Физическая атака ледяного типа с силой 100. Особый эффект поздних поколений: требует точной настройки в боевом движке. Понижает один или несколько параметров цели.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке. Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'icehammer')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1102);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1103, 'Inferno Overdrive', 'Fire', 0, 1, 0, 0, 1, 'Особая атака огненного типа. Z-атака огненного типа.', 'Z-атака огненного типа.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'infernooverdrive')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1103);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1104, 'Instruct', 'Psychic', 3, 15, 0, 0, 1, 'Статусная атака психического типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'instruct')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1104);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1105, 'Laser Focus', 'Normal', 3, 30, 0, 0, 1, 'Статусная атака обычного типа. Влияет на шанс критического удара.', 'Влияет на шанс критического удара.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'laserfocus')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1105);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1106, 'Leafage', 'Grass', 1, 40, 40, 100, 1, 'Физическая атака травяного типа с силой 40. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'leafage')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1106);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1107, 'Let''s Snuggle Forever', 'Fairy', 1, 1, 190, 0, 1, 'Физическая атака волшебного типа с силой 190. Эксклюзивная Z-атака для Mimikyu.', 'Эксклюзивная Z-атака для Mimikyu.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'letssnuggleforever')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1107);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1108, 'Light That Burns the Sky', 'Psychic', 2, 1, 200, 0, 1, 'Специальная атака психического типа с силой 200. Особый эффект поздних поколений: требует точной настройки в боевом движке. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'lightthatburnsthesky')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1108);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1109, 'Liquidation', 'Water', 1, 10, 85, 100, 1, 'Физическая атака водного типа с силой 85. Может понизить Защиту цели.', 'Может понизить Защиту цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'liquidation')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1109);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1110, 'Lunge', 'Bug', 1, 15, 80, 100, 1, 'Физическая атака насекомого типа с силой 80. Особый эффект поздних поколений: требует точной настройки в боевом движке. Понижает один или несколько параметров цели.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке. Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'lunge')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1110);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1111, 'Malicious Moonsault', 'Dark', 1, 1, 180, 0, 1, 'Физическая атака темного типа с силой 180. Эксклюзивная Z-атака для Incineroar.', 'Эксклюзивная Z-атака для Incineroar.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'maliciousmoonsault')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1111);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1112, 'Menacing Moonraze Maelstrom', 'Ghost', 2, 1, 200, 0, 1, 'Специальная атака призрачного типа с силой 200. Эксклюзивная Z-атака для Lunala.', 'Эксклюзивная Z-атака для Lunala.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'menacingmoonrazemaelstrom')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1112);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1113, 'Mind Blown', 'Fire', 2, 5, 150, 100, 1, 'Специальная атака огненного типа с силой 150. Пользователь получает урон отдачи.', 'Пользователь получает урон отдачи.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'mindblown')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1113);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1114, 'Moongeist Beam', 'Ghost', 2, 5, 100, 100, 1, 'Специальная атака призрачного типа с силой 100. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'moongeistbeam')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1114);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1115, 'Multi-Attack', 'Normal', 1, 10, 120, 100, 1, 'Физическая атака обычного типа с силой 120. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'multiattack')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1115);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1116, 'Nature''s Madness', 'Fairy', 2, 10, 0, 90, 1, 'Специальная атака волшебного типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'naturesmadness')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1116);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1117, 'Never-Ending Nightmare', 'Ghost', 0, 1, 0, 0, 1, 'Особая атака призрачного типа. Z-атака призрачного типа.', 'Z-атака призрачного типа.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'neverendingnightmare')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1117);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1118, 'Oceanic Operetta', 'Water', 2, 1, 195, 0, 1, 'Специальная атака водного типа с силой 195. Эксклюзивная Z-атака для Primarina.', 'Эксклюзивная Z-атака для Primarina.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'oceanicoperetta')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1118);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1119, 'Photon Geyser', 'Psychic', 2, 5, 100, 100, 1, 'Специальная атака психического типа с силой 100. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'photongeyser')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1119);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1120, 'Pika Papow', 'Electric', 2, 20, 0, 0, 1, 'Специальная атака электрического типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'pikapapow')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1120);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1121, 'Plasma Fists', 'Electric', 1, 15, 100, 100, 1, 'Физическая атака электрического типа с силой 100. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'plasmafists')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1121);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1122, 'Pollen Puff', 'Bug', 2, 15, 90, 100, 1, 'Специальная атака насекомого типа с силой 90. Наносит урон с дополнительным особым эффектом.', 'Наносит урон с дополнительным особым эффектом.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'pollenpuff')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1122);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1123, 'Power Trip', 'Dark', 1, 10, 20, 100, 1, 'Физическая атака темного типа с силой 20. Особый эффект поздних поколений: требует точной настройки в боевом движке. Повышает один или несколько параметров пользователя.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке. Повышает один или несколько параметров пользователя.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'powertrip')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1123);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1124, 'Prismatic Laser', 'Psychic', 2, 10, 160, 100, 1, 'Специальная атака психического типа с силой 160. Особый эффект поздних поколений: требует точной настройки в боевом движке. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'prismaticlaser')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1124);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1125, 'Psychic Fangs', 'Psychic', 1, 10, 85, 100, 1, 'Физическая атака психического типа с силой 85. Особый эффект поздних поколений: требует точной настройки в боевом движке. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'psychicfangs')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1125);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1126, 'Psychic Terrain', 'Psychic', 3, 10, 0, 0, 1, 'Статусная атака психического типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'psychicterrain')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1126);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1127, 'Pulverizing Pancake', 'Normal', 1, 1, 210, 0, 1, 'Физическая атака обычного типа с силой 210. Z-атака snorlax-exclusive normal типа.', 'Z-атака snorlax-exclusive normal типа.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'pulverizingpancake')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1127);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1128, 'Purify', 'Poison', 3, 20, 0, 0, 1, 'Статусная атака ядовитого типа. Особый эффект поздних поколений: требует точной настройки в боевом движке. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'purify')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1128);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1129, 'Revelation Dance', 'Normal', 2, 15, 90, 100, 1, 'Специальная атака обычного типа с силой 90. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'revelationdance')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1129);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1130, 'Sappy Seed', 'Grass', 1, 15, 90, 100, 1, 'Физическая атака травяного типа с силой 90. Влияет на погоду и связанные с ней бонусы.', 'Влияет на погоду и связанные с ней бонусы.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'sappyseed')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1130);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1131, 'Savage Spin-Out', 'Bug', 0, 1, 0, 0, 1, 'Особая атака насекомого типа. Z-атака насекомого типа.', 'Z-атака насекомого типа.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'savagespinout')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1131);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1132, 'Searing Sunraze Smash', 'Steel', 1, 1, 200, 0, 1, 'Физическая атака стального типа с силой 200. Эксклюзивная Z-атака для Solgaleo.', 'Эксклюзивная Z-атака для Solgaleo.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'searingsunrazesmash')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1132);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1133, 'Shadow Bone', 'Ghost', 1, 10, 85, 100, 1, 'Физическая атака призрачного типа с силой 85. Может понизить Защиту цели.', 'Может понизить Защиту цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'shadowbone')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1133);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1134, 'Shattered Psyche', 'Psychic', 0, 1, 0, 0, 1, 'Особая атака психического типа. Z-атака психического типа.', 'Z-атака психического типа.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'shatteredpsyche')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1134);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1135, 'Shell Trap', 'Fire', 2, 5, 150, 100, 1, 'Специальная атака огненного типа с силой 150. Наносит урон с дополнительным особым эффектом.', 'Наносит урон с дополнительным особым эффектом.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'shelltrap')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1135);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1136, 'Shore Up', 'Ground', 3, 5, 0, 0, 1, 'Статусная атака земляного типа. Особый эффект поздних поколений: требует точной настройки в боевом движке. Влияет на погоду и связанные с ней бонусы.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке. Влияет на погоду и связанные с ней бонусы.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'shoreup')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1136);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1137, 'Sinister Arrow Raid', 'Ghost', 1, 1, 180, 0, 1, 'Физическая атака призрачного типа с силой 180. Эксклюзивная Z-атака для Decidueye.', 'Эксклюзивная Z-атака для Decidueye.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'sinisterarrowraid')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1137);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1138, 'Sizzly Slide', 'Fire', 1, 15, 90, 100, 1, 'Физическая атака огненного типа с силой 90. Поджигает цель.', 'Поджигает цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'sizzlyslide')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1138);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1139, 'Smart Strike', 'Steel', 1, 10, 70, 0, 1, 'Физическая атака стального типа с силой 70. Особый эффект поздних поколений: требует точной настройки в боевом движке. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'smartstrike')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1139);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1140, 'Solar Blade', 'Grass', 1, 10, 125, 100, 1, 'Физическая атака травяного типа с силой 125. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'solarblade')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1140);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1141, 'Soul-Stealing 7-Star Strike', 'Ghost', 1, 1, 195, 0, 1, 'Физическая атака призрачного типа с силой 195. Эксклюзивная Z-атака для Marshadow.', 'Эксклюзивная Z-атака для Marshadow.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'soulstealing7starstrike')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1141);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1142, 'Sparkling Aria', 'Water', 2, 10, 90, 100, 1, 'Специальная атака водного типа с силой 90. Может поджечь цель.', 'Может поджечь цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'sparklingaria')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1142);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1143, 'Sparkly Swirl', 'Fairy', 2, 15, 90, 100, 1, 'Специальная атака волшебного типа с силой 90. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'sparklyswirl')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1143);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1144, 'Spectral Thief', 'Ghost', 1, 10, 90, 100, 1, 'Физическая атака призрачного типа с силой 90. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'spectralthief')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1144);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1145, 'Speed Swap', 'Psychic', 3, 10, 0, 0, 1, 'Статусная атака психического типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'speedswap')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1145);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1146, 'Spirit Shackle', 'Ghost', 1, 10, 80, 100, 1, 'Физическая атака призрачного типа с силой 80. Влияет на смену покемона или возможность отступления.', 'Влияет на смену покемона или возможность отступления.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'spiritshackle')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1146);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1147, 'Splintered Stormshards', 'Rock', 1, 1, 190, 0, 1, 'Физическая атака каменного типа с силой 190. Эксклюзивная Z-атака для Lycanroc.', 'Эксклюзивная Z-атака для Lycanroc.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'splinteredstormshards')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1147);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1148, 'Splishy Splash', 'Water', 2, 15, 90, 100, 1, 'Специальная атака водного типа с силой 90. Может парализовать цель.', 'Может парализовать цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'splishysplash')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1148);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1149, 'Spotlight', 'Normal', 3, 15, 0, 0, 1, 'Статусная атака обычного типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'spotlight')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1149);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1150, 'Stoked Sparksurfer', 'Electric', 2, 1, 175, 0, 1, 'Специальная атака электрического типа с силой 175. Z-атака alolan raichu-exclusive electric типа.', 'Z-атака alolan raichu-exclusive electric типа.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'stokedsparksurfer')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1150);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1151, 'Stomping Tantrum', 'Ground', 1, 10, 75, 100, 1, 'Физическая атака земляного типа с силой 75. Особый эффект поздних поколений: требует точной настройки в боевом движке. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'stompingtantrum')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1151);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1152, 'Strength Sap', 'Grass', 3, 10, 0, 100, 1, 'Статусная атака травяного типа. Особый эффект поздних поколений: требует точной настройки в боевом движке. Понижает один или несколько параметров цели.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке. Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'strengthsap')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1152);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1153, 'Subzero Slammer', 'Ice', 0, 1, 0, 0, 1, 'Особая атака ледяного типа. Z-атака ледяного типа.', 'Z-атака ледяного типа.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'subzeroslammer')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1153);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1154, 'Sunsteel Strike', 'Steel', 1, 5, 100, 100, 1, 'Физическая атака стального типа с силой 100. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'sunsteelstrike')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1154);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1155, 'Supersonic Skystrike', 'Flying', 0, 1, 0, 0, 1, 'Особая атака летающего типа. Z-атака летающего типа.', 'Z-атака летающего типа.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'supersonicskystrike')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1155);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1156, 'Tearful Look', 'Normal', 3, 20, 0, 0, 1, 'Статусная атака обычного типа. Особый эффект поздних поколений: требует точной настройки в боевом движке. Понижает один или несколько параметров цели. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке. Понижает один или несколько параметров цели. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'tearfullook')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1156);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1157, 'Tectonic Rage', 'Ground', 0, 1, 0, 0, 1, 'Особая атака земляного типа. Z-атака земляного типа.', 'Z-атака земляного типа.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'tectonicrage')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1157);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1158, 'Throat Chop', 'Dark', 1, 15, 80, 100, 1, 'Физическая атака темного типа с силой 80. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'throatchop')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1158);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1159, 'Toxic Thread', 'Poison', 3, 20, 0, 100, 1, 'Статусная атака ядовитого типа. Может отравить цель.', 'Может отравить цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'toxicthread')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1159);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1160, 'Trop Kick', 'Grass', 1, 15, 70, 100, 1, 'Физическая атака травяного типа с силой 70. Понижает Атаку цели.', 'Понижает Атаку цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'tropkick')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1160);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1161, 'Twinkle Tackle', 'Fairy', 0, 1, 0, 0, 1, 'Особая атака волшебного типа. Z-атака волшебного типа.', 'Z-атака волшебного типа.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'twinkletackle')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1161);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1162, 'Veevee Volley', 'Normal', 1, 20, 0, 0, 1, 'Физическая атака обычного типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'veeveevolley')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1162);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1163, 'Zing Zap', 'Electric', 1, 10, 80, 100, 1, 'Физическая атака электрического типа с силой 80. Может испугать цель.', 'Может испугать цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'zingzap')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1163);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1164, 'Zippy Zap', 'Electric', 1, 15, 50, 100, 1, 'Физическая атака электрического типа с силой 50. Влияет на шанс критического удара.', 'Влияет на шанс критического удара.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'zippyzap')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1164);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1165, 'Apple Acid', 'Grass', 2, 10, 80, 100, 1, 'Специальная атака травяного типа с силой 80. Понижает один или несколько параметров цели.', 'Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'appleacid')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1165);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1166, 'Astral Barrage', 'Ghost', 2, 5, 120, 100, 1, 'Специальная атака призрачного типа с силой 120. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'astralbarrage')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1166);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1167, 'Aura Wheel', 'Electric', 1, 10, 110, 100, 1, 'Физическая атака электрического типа с силой 110. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'aurawheel')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1167);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1168, 'Barb Barrage', 'Poison', 1, 10, 60, 100, 1, 'Физическая атака ядовитого типа с силой 60. Может отравить цель.', 'Может отравить цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'barbbarrage')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1168);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1169, 'Behemoth Bash', 'Steel', 1, 5, 100, 100, 1, 'Физическая атака стального типа с силой 100. Наносит урон с дополнительным особым эффектом.', 'Наносит урон с дополнительным особым эффектом.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'behemothbash')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1169);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1170, 'Behemoth Blade', 'Steel', 1, 5, 100, 100, 1, 'Физическая атака стального типа с силой 100. Наносит урон с дополнительным особым эффектом.', 'Наносит урон с дополнительным особым эффектом.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'behemothblade')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1170);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1171, 'Bitter Malice', 'Ghost', 2, 10, 75, 100, 1, 'Специальная атака призрачного типа с силой 75. Понижает Атаку цели.', 'Понижает Атаку цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'bittermalice')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1171);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1172, 'Bleakwind Storm', 'Flying', 2, 10, 100, 80, 1, 'Специальная атака летающего типа с силой 100. Может понизить Скорость цели.', 'Может понизить Скорость цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'bleakwindstorm')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1172);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1173, 'Body Press', 'Fighting', 1, 10, 80, 100, 1, 'Физическая атака боевого типа с силой 80. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'bodypress')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1173);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1174, 'Bolt Beak', 'Electric', 1, 10, 85, 100, 1, 'Физическая атака электрического типа с силой 85. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'boltbeak')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1174);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1175, 'Branch Poke', 'Grass', 1, 40, 40, 100, 1, 'Физическая атака травяного типа с силой 40. Наносит обычный урон без дополнительного эффекта.', 'Наносит обычный урон без дополнительного эффекта.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'branchpoke')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1175);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1176, 'Breaking Swipe', 'Dragon', 1, 15, 60, 100, 1, 'Физическая атака драконьего типа с силой 60. Понижает один или несколько параметров цели.', 'Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'breakingswipe')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1176);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1177, 'Burning Jealousy', 'Fire', 2, 5, 70, 100, 1, 'Специальная атака огненного типа с силой 70. Может поджечь цель.', 'Может поджечь цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'burningjealousy')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1177);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1178, 'Ceaseless Edge', 'Dark', 1, 15, 65, 90, 1, 'Физическая атака темного типа с силой 65. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'ceaselessedge')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1178);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1179, 'Chloroblast', 'Grass', 2, 5, 150, 95, 1, 'Специальная атака травяного типа с силой 150. Пользователь получает урон отдачи.', 'Пользователь получает урон отдачи.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'chloroblast')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1179);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1180, 'Clangorous Soul', 'Dragon', 3, 5, 0, 100, 1, 'Статусная атака драконьего типа. Повышает один или несколько параметров пользователя.', 'Повышает один или несколько параметров пользователя.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'clangoroussoul')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1180);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1181, 'Coaching', 'Fighting', 3, 10, 0, 0, 1, 'Статусная атака боевого типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'coaching')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1181);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1182, 'Corrosive Gas', 'Poison', 3, 40, 0, 100, 1, 'Статусная атака ядовитого типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'corrosivegas')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1182);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1183, 'Court Change', 'Normal', 3, 10, 0, 100, 1, 'Статусная атака обычного типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'courtchange')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1183);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1184, 'Decorate', 'Fairy', 3, 15, 0, 0, 1, 'Статусная атака волшебного типа. Повышает один или несколько параметров пользователя.', 'Повышает один или несколько параметров пользователя.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'decorate')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1184);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1185, 'Dire Claw', 'Poison', 1, 15, 80, 100, 1, 'Физическая атака ядовитого типа с силой 80. Может взаимодействовать со сном.', 'Может взаимодействовать со сном.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'direclaw')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1185);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1186, 'Dragon Darts', 'Dragon', 1, 10, 50, 100, 1, 'Физическая атака драконьего типа с силой 50. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'dragondarts')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1186);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1187, 'Dragon Energy', 'Dragon', 2, 5, 150, 100, 1, 'Специальная атака драконьего типа с силой 150. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'dragonenergy')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1187);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1188, 'Drum Beating', 'Grass', 1, 10, 80, 100, 1, 'Физическая атака травяного типа с силой 80. Понижает Скорость цели.', 'Понижает Скорость цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'drumbeating')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1188);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1189, 'Dual Wingbeat', 'Flying', 1, 10, 40, 90, 1, 'Физическая атака летающего типа с силой 40. Особый эффект поздних поколений: требует точной настройки в боевом движке. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'dualwingbeat')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1189);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1190, 'Dynamax Cannon', 'Dragon', 2, 5, 100, 100, 1, 'Специальная атака драконьего типа с силой 100. Наносит урон с дополнительным особым эффектом.', 'Наносит урон с дополнительным особым эффектом.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'dynamaxcannon')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1190);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1191, 'Eerie Spell', 'Psychic', 2, 5, 80, 100, 1, 'Специальная атака психического типа с силой 80. Наносит урон с дополнительным особым эффектом.', 'Наносит урон с дополнительным особым эффектом.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'eeriespell')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1191);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1192, 'Esper Wing', 'Psychic', 2, 10, 80, 100, 1, 'Специальная атака психического типа с силой 80. Повышенный шанс критического удара. Повышает Скорость пользователя.', 'Повышенный шанс критического удара. Повышает Скорость пользователя.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'esperwing')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1192);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1193, 'Eternabeam', 'Dragon', 2, 5, 160, 90, 1, 'Специальная атака драконьего типа с силой 160. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'eternabeam')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1193);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1194, 'Expanding Force', 'Psychic', 2, 10, 80, 100, 1, 'Специальная атака психического типа с силой 80. Меняет поле боя и включает особый эффект местности.', 'Меняет поле боя и включает особый эффект местности.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'expandingforce')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1194);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1195, 'False Surrender', 'Dark', 1, 10, 80, 0, 1, 'Физическая атака темного типа с силой 80. Игнорирует точность и уклонение.', 'Игнорирует точность и уклонение.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'falsesurrender')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1195);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1196, 'Fiery Wrath', 'Dark', 2, 10, 90, 100, 1, 'Специальная атака темного типа с силой 90. Может испугать цель.', 'Может испугать цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'fierywrath')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1196);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1197, 'Fishious Rend', 'Water', 1, 10, 85, 100, 1, 'Физическая атака водного типа с силой 85. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'fishiousrend')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1197);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1198, 'Flip Turn', 'Water', 1, 20, 60, 100, 1, 'Физическая атака водного типа с силой 60. Влияет на смену покемона или возможность отступления.', 'Влияет на смену покемона или возможность отступления.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'flipturn')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1198);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1199, 'Freezing Glare', 'Psychic', 2, 10, 90, 100, 1, 'Специальная атака психического типа с силой 90. Может заморозить цель.', 'Может заморозить цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'freezingglare')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1199);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1200, 'G-Max Befuddle', 'Bug', 0, 5, 0, 0, 1, 'Особая атака насекомого типа. Эксклюзивная G-Max атака для Butterfree. Может взаимодействовать со сном.', 'Эксклюзивная G-Max атака для Butterfree. Может взаимодействовать со сном.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxbefuddle')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1200);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1201, 'G-Max Cannonade', 'Water', 0, 10, 0, 0, 1, 'Особая атака водного типа. Эксклюзивная G-Max атака для Blastoise. Наносит урон с дополнительным особым эффектом.', 'Эксклюзивная G-Max атака для Blastoise. Наносит урон с дополнительным особым эффектом.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxcannonade')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1201);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1202, 'G-Max Centiferno', 'Fire', 0, 5, 0, 0, 1, 'Особая атака огненного типа. Эксклюзивная G-Max атака для Centiskorch. Мешает смене и может наносить периодический урон.', 'Эксклюзивная G-Max атака для Centiskorch. Мешает смене и может наносить периодический урон.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxcentiferno')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1202);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1203, 'G-Max Chi Strike', 'Fighting', 0, 5, 0, 0, 1, 'Особая атака боевого типа. Эксклюзивная G-Max атака для Machamp. Влияет на шанс критического удара.', 'Эксклюзивная G-Max атака для Machamp. Влияет на шанс критического удара.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxchistrike')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1203);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1204, 'G-Max Cuddle', 'Normal', 0, 5, 0, 0, 1, 'Особая атака обычного типа. Эксклюзивная G-Max атака для Eevee. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Эксклюзивная G-Max атака для Eevee. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxcuddle')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1204);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1205, 'G-Max Depletion', 'Dragon', 0, 5, 0, 0, 1, 'Особая атака драконьего типа. Эксклюзивная G-Max атака для Duraludon. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Эксклюзивная G-Max атака для Duraludon. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxdepletion')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1205);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1206, 'G-Max Drum Solo', 'Grass', 0, 5, 160, 0, 1, 'Особая атака травяного типа с силой 160. Эксклюзивная G-Max атака для Rillaboom. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Эксклюзивная G-Max атака для Rillaboom. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxdrumsolo')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1206);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1207, 'G-Max Finale', 'Fairy', 0, 5, 0, 0, 1, 'Особая атака волшебного типа. Эксклюзивная G-Max атака для Alcremie. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Эксклюзивная G-Max атака для Alcremie. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxfinale')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1207);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1208, 'G-Max Fireball', 'Fire', 0, 5, 160, 0, 1, 'Особая атака огненного типа с силой 160. Эксклюзивная G-Max атака для Cinderace. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Эксклюзивная G-Max атака для Cinderace. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxfireball')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1208);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1209, 'G-Max Foam Burst', 'Water', 0, 5, 0, 0, 1, 'Особая атака водного типа. Эксклюзивная G-Max атака для Kingler. Понижает один или несколько параметров цели.', 'Эксклюзивная G-Max атака для Kingler. Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxfoamburst')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1209);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1210, 'G-Max Gold Rush', 'Normal', 0, 5, 0, 0, 1, 'Особая атака обычного типа. Эксклюзивная G-Max атака для Meowth. Может спутать цель.', 'Эксклюзивная G-Max атака для Meowth. Может спутать цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxgoldrush')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1210);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1211, 'G-Max Gravitas', 'Psychic', 0, 5, 0, 0, 1, 'Особая атака психического типа. Эксклюзивная G-Max атака для Orbeetle. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Эксклюзивная G-Max атака для Orbeetle. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxgravitas')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1211);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1212, 'G-Max Hydrosnipe', 'Water', 0, 5, 160, 0, 1, 'Особая атака водного типа с силой 160. Эксклюзивная G-Max атака для Inteleon. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Эксклюзивная G-Max атака для Inteleon. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxhydrosnipe')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1212);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1213, 'G-Max Malodor', 'Poison', 0, 5, 0, 0, 1, 'Особая атака ядовитого типа. Эксклюзивная G-Max атака для Garbodor. Может отравить цель.', 'Эксклюзивная G-Max атака для Garbodor. Может отравить цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxmalodor')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1213);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1214, 'G-Max Meltdown', 'Steel', 0, 5, 0, 0, 1, 'Особая атака стального типа. Эксклюзивная G-Max атака для Melmetal. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Эксклюзивная G-Max атака для Melmetal. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxmeltdown')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1214);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1215, 'G-Max One Blow', 'Dark', 0, 5, 0, 0, 1, 'Особая атака темного типа. Эксклюзивная G-Max атака для Urshifu Single-Strike Style. Дает защитный эффект на этот ход.', 'Эксклюзивная G-Max атака для Urshifu Single-Strike Style. Дает защитный эффект на этот ход.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxoneblow')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1215);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1216, 'G-Max Rapid Flow', 'Water', 0, 5, 0, 0, 1, 'Особая атака водного типа. Эксклюзивная G-Max атака для Urshifu Rapid-Strike Style. Дает защитный эффект на этот ход.', 'Эксклюзивная G-Max атака для Urshifu Rapid-Strike Style. Дает защитный эффект на этот ход.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxrapidflow')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1216);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1217, 'G-Max Replenish', 'Normal', 0, 5, 0, 0, 1, 'Особая атака обычного типа. Эксклюзивная G-Max атака для Snorlax. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Эксклюзивная G-Max атака для Snorlax. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxreplenish')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1217);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1218, 'G-Max Resonance', 'Ice', 0, 5, 0, 0, 1, 'Особая атака ледяного типа. Эксклюзивная G-Max атака для Lapras. Наносит урон с дополнительным особым эффектом.', 'Эксклюзивная G-Max атака для Lapras. Наносит урон с дополнительным особым эффектом.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxresonance')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1218);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1219, 'G-Max Sandblast', 'Ground', 0, 5, 0, 0, 1, 'Особая атака земляного типа. Эксклюзивная G-Max атака для Sandaconda. Мешает смене и может наносить периодический урон.', 'Эксклюзивная G-Max атака для Sandaconda. Мешает смене и может наносить периодический урон.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxsandblast')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1219);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1220, 'G-Max Smite', 'Fairy', 0, 5, 0, 0, 1, 'Особая атака волшебного типа. Эксклюзивная G-Max атака для Hatterene. Может спутать цель.', 'Эксклюзивная G-Max атака для Hatterene. Может спутать цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxsmite')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1220);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1221, 'G-Max Snooze', 'Dark', 0, 5, 0, 0, 1, 'Особая атака темного типа. Эксклюзивная G-Max атака для Grimmsnarl. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Эксклюзивная G-Max атака для Grimmsnarl. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxsnooze')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1221);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1222, 'G-Max Steelsurge', 'Steel', 0, 5, 0, 0, 1, 'Особая атака стального типа. Эксклюзивная G-Max атака для Copperajah. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Эксклюзивная G-Max атака для Copperajah. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxsteelsurge')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1222);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1223, 'G-Max Stonesurge', 'Water', 0, 5, 0, 0, 1, 'Особая атака водного типа. Эксклюзивная G-Max атака для Drednaw. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Эксклюзивная G-Max атака для Drednaw. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxstonesurge')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1223);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1224, 'G-Max Stun Shock', 'Electric', 0, 10, 0, 0, 1, 'Особая атака электрического типа. Эксклюзивная G-Max атака для Toxtricity. Может отравить цель.', 'Эксклюзивная G-Max атака для Toxtricity. Может отравить цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxstunshock')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1224);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1225, 'G-Max Sweetness', 'Grass', 0, 10, 0, 0, 1, 'Особая атака травяного типа. Эксклюзивная G-Max атака для Appletun. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Эксклюзивная G-Max атака для Appletun. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxsweetness')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1225);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1226, 'G-Max Tartness', 'Grass', 0, 10, 0, 0, 1, 'Особая атака травяного типа. Эксклюзивная G-Max атака для Flapple. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Эксклюзивная G-Max атака для Flapple. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxtartness')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1226);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1227, 'G-Max Terror', 'Ghost', 0, 10, 0, 0, 1, 'Особая атака призрачного типа. Эксклюзивная G-Max атака для Gengar. Влияет на смену покемона или возможность отступления.', 'Эксклюзивная G-Max атака для Gengar. Влияет на смену покемона или возможность отступления.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxterror')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1227);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1228, 'G-Max Vine Lash', 'Grass', 0, 10, 0, 0, 1, 'Особая атака травяного типа. Эксклюзивная G-Max атака для Venusaur. Наносит урон с дополнительным особым эффектом.', 'Эксклюзивная G-Max атака для Venusaur. Наносит урон с дополнительным особым эффектом.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxvinelash')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1228);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1229, 'G-Max Volcalith', 'Rock', 0, 10, 0, 0, 1, 'Особая атака каменного типа. Эксклюзивная G-Max атака для Coalossal. Наносит урон с дополнительным особым эффектом.', 'Эксклюзивная G-Max атака для Coalossal. Наносит урон с дополнительным особым эффектом.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxvolcalith')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1229);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1230, 'G-Max Volt Crash', 'Electric', 0, 10, 0, 0, 1, 'Особая атака электрического типа. Эксклюзивная G-Max атака для Pikachu. Может парализовать цель.', 'Эксклюзивная G-Max атака для Pikachu. Может парализовать цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxvoltcrash')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1230);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1231, 'G-Max Wildfire', 'Fire', 0, 10, 0, 0, 1, 'Особая атака огненного типа. Эксклюзивная G-Max атака для Charizard. Наносит урон с дополнительным особым эффектом.', 'Эксклюзивная G-Max атака для Charizard. Наносит урон с дополнительным особым эффектом.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxwildfire')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1231);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1232, 'G-Max Wind Rage', 'Flying', 0, 10, 0, 0, 1, 'Особая атака летающего типа. Эксклюзивная G-Max атака для Corviknight. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Эксклюзивная G-Max атака для Corviknight. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxwindrage')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1232);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1233, 'Glacial Lance', 'Ice', 1, 5, 120, 100, 1, 'Физическая атака ледяного типа с силой 120. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'glaciallance')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1233);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1234, 'Grassy Glide', 'Grass', 1, 20, 55, 100, 1, 'Физическая атака травяного типа с силой 55. Меняет поле боя и включает особый эффект местности.', 'Меняет поле боя и включает особый эффект местности.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'grassyglide')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1234);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1235, 'Grav Apple', 'Grass', 1, 10, 80, 100, 1, 'Физическая атака травяного типа с силой 80. Понижает один или несколько параметров цели.', 'Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gravapple')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1235);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1236, 'Headlong Rush', 'Ground', 1, 5, 120, 100, 1, 'Физическая атака земляного типа с силой 120. Понижает один или несколько параметров цели.', 'Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'headlongrush')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1236);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1237, 'Infernal Parade', 'Ghost', 2, 15, 60, 100, 1, 'Специальная атака призрачного типа с силой 60. Наносит урон с дополнительным особым эффектом.', 'Наносит урон с дополнительным особым эффектом.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'infernalparade')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1237);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1238, 'Jaw Lock', 'Dark', 1, 10, 80, 100, 1, 'Физическая атака темного типа с силой 80. Влияет на смену покемона или возможность отступления.', 'Влияет на смену покемона или возможность отступления.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'jawlock')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1238);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1239, 'Jungle Healing', 'Grass', 3, 10, 0, 0, 1, 'Статусная атака травяного типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'junglehealing')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1239);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1240, 'Lash Out', 'Dark', 1, 5, 75, 100, 1, 'Физическая атака темного типа с силой 75. Понижает один или несколько параметров цели.', 'Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'lashout')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1240);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1241, 'Life Dew', 'Water', 3, 10, 0, 0, 1, 'Статусная атака водного типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'lifedew')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1241);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1242, 'Lunar Blessing', 'Psychic', 3, 5, 0, 0, 1, 'Статусная атака психического типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'lunarblessing')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1242);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1243, 'Magic Powder', 'Psychic', 3, 20, 0, 100, 1, 'Статусная атака психического типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'magicpowder')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1243);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1244, 'Max Airstream', 'Flying', 0, 0, 0, 0, 1, 'Особая атака летающего типа. Dynamax-атака летающего типа. Повышает один или несколько параметров пользователя.', 'Dynamax-атака летающего типа. Повышает один или несколько параметров пользователя.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'maxairstream')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1244);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1245, 'Max Darkness', 'Dark', 0, 0, 0, 0, 1, 'Особая атака темного типа. Dynamax-атака темного типа. Понижает один или несколько параметров цели.', 'Dynamax-атака темного типа. Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'maxdarkness')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1245);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1246, 'Max Flare', 'Fire', 0, 0, 0, 0, 1, 'Особая атака огненного типа. Dynamax-атака огненного типа. Влияет на погоду и связанные с ней бонусы.', 'Dynamax-атака огненного типа. Влияет на погоду и связанные с ней бонусы.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'maxflare')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1246);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1247, 'Max Flutterby', 'Bug', 0, 0, 0, 0, 1, 'Особая атака насекомого типа. Dynamax-атака насекомого типа. Понижает один или несколько параметров цели.', 'Dynamax-атака насекомого типа. Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'maxflutterby')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1247);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1248, 'Max Geyser', 'Water', 0, 0, 0, 0, 1, 'Особая атака водного типа. Dynamax-атака водного типа. Влияет на погоду и связанные с ней бонусы.', 'Dynamax-атака водного типа. Влияет на погоду и связанные с ней бонусы.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'maxgeyser')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1248);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1249, 'Max Guard', 'Normal', 0, 0, 0, 0, 1, 'Особая атака обычного типа. Особый эффект поздних поколений: требует точной настройки в боевом движке. Защищает пользователя.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке. Защищает пользователя.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'maxguard')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1249);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1250, 'Max Hailstorm', 'Ice', 0, 0, 0, 0, 1, 'Особая атака ледяного типа. Dynamax-атака ледяного типа. Влияет на погоду и связанные с ней бонусы.', 'Dynamax-атака ледяного типа. Влияет на погоду и связанные с ней бонусы.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'maxhailstorm')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1250);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1251, 'Max Knuckle', 'Fighting', 0, 0, 0, 0, 1, 'Особая атака боевого типа. Dynamax-атака боевого типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Dynamax-атака боевого типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'maxknuckle')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1251);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1252, 'Max Lightning', 'Electric', 0, 0, 0, 0, 1, 'Особая атака электрического типа. Dynamax-атака электрического типа. Меняет поле боя и включает особый эффект местности.', 'Dynamax-атака электрического типа. Меняет поле боя и включает особый эффект местности.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'maxlightning')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1252);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1253, 'Max Mindstorm', 'Psychic', 0, 0, 0, 0, 1, 'Особая атака психического типа. Dynamax-атака психического типа. Меняет поле боя и включает особый эффект местности.', 'Dynamax-атака психического типа. Меняет поле боя и включает особый эффект местности.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'maxmindstorm')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1253);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1254, 'Max Ooze', 'Poison', 0, 0, 0, 0, 1, 'Особая атака ядовитого типа. Dynamax-атака ядовитого типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Dynamax-атака ядовитого типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'maxooze')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1254);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1255, 'Max Overgrowth', 'Grass', 0, 0, 0, 0, 1, 'Особая атака травяного типа. Dynamax-атака травяного типа. Меняет поле боя и включает особый эффект местности.', 'Dynamax-атака травяного типа. Меняет поле боя и включает особый эффект местности.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'maxovergrowth')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1255);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1256, 'Max Phantasm', 'Ghost', 0, 0, 0, 0, 1, 'Особая атака призрачного типа. Dynamax-атака призрачного типа. Понижает один или несколько параметров цели.', 'Dynamax-атака призрачного типа. Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'maxphantasm')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1256);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1257, 'Max Quake', 'Ground', 0, 0, 0, 0, 1, 'Особая атака земляного типа. Dynamax-атака земляного типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Dynamax-атака земляного типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'maxquake')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1257);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1258, 'Max Rockfall', 'Rock', 0, 0, 0, 0, 1, 'Особая атака каменного типа. Dynamax-атака каменного типа. Влияет на погоду и связанные с ней бонусы.', 'Dynamax-атака каменного типа. Влияет на погоду и связанные с ней бонусы.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'maxrockfall')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1258);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1259, 'Max Starfall', 'Fairy', 0, 0, 0, 0, 1, 'Особая атака волшебного типа. Dynamax-атака волшебного типа. Меняет поле боя и включает особый эффект местности.', 'Dynamax-атака волшебного типа. Меняет поле боя и включает особый эффект местности.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'maxstarfall')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1259);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1260, 'Max Steelspike', 'Steel', 0, 0, 0, 0, 1, 'Особая атака стального типа. Dynamax-атака стального типа. Повышает один или несколько параметров пользователя.', 'Dynamax-атака стального типа. Повышает один или несколько параметров пользователя.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'maxsteelspike')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1260);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1261, 'Max Strike', 'Normal', 0, 0, 0, 0, 1, 'Особая атака обычного типа. Dynamax-атака обычного типа. Понижает один или несколько параметров цели.', 'Dynamax-атака обычного типа. Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'maxstrike')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1261);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1262, 'Max Wyrmwind', 'Dragon', 0, 0, 0, 0, 1, 'Особая атака драконьего типа. Dynamax-атака драконьего типа. Понижает один или несколько параметров цели.', 'Dynamax-атака драконьего типа. Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'maxwyrmwind')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1262);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1263, 'Meteor Assault', 'Fighting', 1, 5, 150, 100, 1, 'Физическая атака боевого типа с силой 150. Пользователь пропускает следующий ход для перезарядки.', 'Пользователь пропускает следующий ход для перезарядки.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'meteorassault')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1263);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1264, 'Meteor Beam', 'Rock', 2, 10, 120, 90, 1, 'Специальная атака каменного типа с силой 120. Особый эффект поздних поколений: требует точной настройки в боевом движке. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'meteorbeam')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1264);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1265, 'Misty Explosion', 'Fairy', 2, 5, 100, 100, 1, 'Специальная атака волшебного типа с силой 100. Меняет поле боя и включает особый эффект местности.', 'Меняет поле боя и включает особый эффект местности.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'mistyexplosion')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1265);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1266, 'Mountain Gale', 'Ice', 1, 10, 100, 85, 1, 'Физическая атака ледяного типа с силой 100. Понижает один или несколько параметров цели.', 'Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'mountaingale')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1266);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1267, 'Mystical Power', 'Psychic', 2, 10, 70, 90, 1, 'Специальная атака психического типа с силой 70. Повышает Спец. атаку пользователя.', 'Повышает Спец. атаку пользователя.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'mysticalpower')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1267);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1268, 'No Retreat', 'Fighting', 3, 5, 0, 0, 1, 'Статусная атака боевого типа. Влияет на смену покемона или возможность отступления.', 'Влияет на смену покемона или возможность отступления.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'noretreat')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1268);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1269, 'Obstruct', 'Dark', 3, 10, 0, 100, 1, 'Статусная атака темного типа. Дает защитный эффект на этот ход.', 'Дает защитный эффект на этот ход.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'obstruct')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1269);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1270, 'Octolock', 'Fighting', 3, 15, 0, 100, 1, 'Статусная атака боевого типа. Влияет на смену покемона или возможность отступления.', 'Влияет на смену покемона или возможность отступления.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'octolock')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1270);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1271, 'Overdrive', 'Electric', 2, 10, 80, 100, 1, 'Специальная атака электрического типа с силой 80. Бьет всех соседних противников.', 'Бьет всех соседних противников.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'overdrive')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1271);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1272, 'Poltergeist', 'Ghost', 1, 5, 110, 90, 1, 'Физическая атака призрачного типа с силой 110. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'poltergeist')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1272);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1273, 'Power Shift', 'Normal', 3, 10, 0, 0, 1, 'Статусная атака обычного типа. Влияет на смену покемона или возможность отступления.', 'Влияет на смену покемона или возможность отступления.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'powershift')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1273);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1274, 'Psyshield Bash', 'Psychic', 1, 10, 70, 90, 1, 'Физическая атака психического типа с силой 70. Повышает один или несколько параметров пользователя.', 'Повышает один или несколько параметров пользователя.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'psyshieldbash')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1274);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1275, 'Pyro Ball', 'Fire', 1, 5, 120, 90, 1, 'Физическая атака огненного типа с силой 120. Может поджечь цель.', 'Может поджечь цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'pyroball')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1275);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1276, 'Raging Fury', 'Fire', 1, 10, 120, 100, 1, 'Физическая атака огненного типа с силой 120. Может спутать цель.', 'Может спутать цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'ragingfury')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1276);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1277, 'Rising Voltage', 'Electric', 2, 20, 70, 100, 1, 'Специальная атака электрического типа с силой 70. Меняет поле боя и включает особый эффект местности.', 'Меняет поле боя и включает особый эффект местности.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'risingvoltage')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1277);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1278, 'Sandsear Storm', 'Ground', 2, 10, 100, 80, 1, 'Специальная атака земляного типа с силой 100. Может поджечь цель.', 'Может поджечь цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'sandsearstorm')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1278);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1279, 'Scale Shot', 'Dragon', 1, 20, 25, 90, 1, 'Физическая атака драконьего типа с силой 25. Бьет 2-5 раз за один ход. Понижает один или несколько параметров цели.', 'Бьет 2-5 раз за один ход. Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'scaleshot')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1279);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1280, 'Scorching Sands', 'Ground', 2, 10, 70, 100, 1, 'Специальная атака земляного типа с силой 70. Может поджечь цель.', 'Может поджечь цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'scorchingsands')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1280);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1281, 'Shell Side Arm', 'Poison', 2, 10, 90, 100, 1, 'Специальная атака ядовитого типа с силой 90. Может отравить цель. Наносит урон с дополнительным особым эффектом.', 'Может отравить цель. Наносит урон с дополнительным особым эффектом.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'shellsidearm')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1281);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1282, 'Shelter', 'Steel', 3, 10, 0, 0, 1, 'Статусная атака стального типа. Повышает Защиту пользователя.', 'Повышает Защиту пользователя.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'shelter')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1282);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1283, 'Skitter Smack', 'Bug', 1, 10, 70, 90, 1, 'Физическая атака насекомого типа с силой 70. Понижает один или несколько параметров цели. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Понижает один или несколько параметров цели. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'skittersmack')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1283);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1284, 'Snap Trap', 'Grass', 1, 15, 35, 100, 1, 'Физическая атака травяного типа с силой 35. Удерживает цель и наносит ей урон 4-5 ходов.', 'Удерживает цель и наносит ей урон 4-5 ходов.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'snaptrap')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1284);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1285, 'Snipe Shot', 'Water', 2, 15, 80, 100, 1, 'Специальная атака водного типа с силой 80. Особый эффект поздних поколений: требует точной настройки в боевом движке. Влияет на шанс критического удара.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке. Влияет на шанс критического удара.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'snipeshot')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1285);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1286, 'Spirit Break', 'Fairy', 1, 15, 75, 100, 1, 'Физическая атака волшебного типа с силой 75. Понижает Спец. атаку цели.', 'Понижает Спец. атаку цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'spiritbreak')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1286);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1287, 'Springtide Storm', 'Fairy', 2, 5, 100, 80, 1, 'Специальная атака волшебного типа с силой 100. Понижает один или несколько параметров цели.', 'Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'springtidestorm')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1287);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1288, 'Steel Beam', 'Steel', 2, 5, 140, 95, 1, 'Специальная атака стального типа с силой 140. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'steelbeam')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1288);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1289, 'Steel Roller', 'Steel', 1, 5, 130, 100, 1, 'Физическая атака стального типа с силой 130. Меняет поле боя и включает особый эффект местности.', 'Меняет поле боя и включает особый эффект местности.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'steelroller')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1289);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1290, 'Stone Axe', 'Rock', 1, 15, 65, 90, 1, 'Физическая атака каменного типа с силой 65. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'stoneaxe')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1290);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1291, 'Strange Steam', 'Fairy', 2, 10, 90, 95, 1, 'Специальная атака волшебного типа с силой 90. Может спутать цель.', 'Может спутать цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'strangesteam')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1291);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1292, 'Stuff Cheeks', 'Normal', 3, 10, 0, 0, 1, 'Статусная атака обычного типа. Повышает один или несколько параметров пользователя.', 'Повышает один или несколько параметров пользователя.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'stuffcheeks')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1292);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1293, 'Surging Strikes', 'Water', 1, 5, 25, 100, 1, 'Физическая атака водного типа с силой 25. Влияет на шанс критического удара.', 'Влияет на шанс критического удара.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'surgingstrikes')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1293);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1294, 'Take Heart', 'Psychic', 3, 10, 0, 0, 1, 'Статусная атака психического типа. Повышает один или несколько параметров пользователя.', 'Повышает один или несколько параметров пользователя.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'takeheart')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1294);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1295, 'Tar Shot', 'Rock', 3, 15, 0, 100, 1, 'Статусная атака каменного типа. Понижает один или несколько параметров цели.', 'Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'tarshot')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1295);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1296, 'Teatime', 'Normal', 3, 10, 0, 0, 1, 'Статусная атака обычного типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'teatime')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1296);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1297, 'Terrain Pulse', 'Normal', 2, 10, 50, 100, 1, 'Специальная атака обычного типа с силой 50. Меняет поле боя и включает особый эффект местности.', 'Меняет поле боя и включает особый эффект местности.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'terrainpulse')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1297);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1298, 'Thunder Cage', 'Electric', 2, 15, 80, 90, 1, 'Специальная атака электрического типа с силой 80. Мешает смене и может наносить периодический урон.', 'Мешает смене и может наносить периодический урон.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'thundercage')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1298);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1299, 'Thunderous Kick', 'Fighting', 1, 10, 90, 100, 1, 'Физическая атака боевого типа с силой 90. Понижает Защиту цели.', 'Понижает Защиту цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'thunderouskick')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1299);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1300, 'Triple Arrows', 'Fighting', 1, 10, 90, 100, 1, 'Физическая атака боевого типа с силой 90. Повышенный шанс критического удара. Может испугать цель.', 'Повышенный шанс критического удара. Может испугать цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'triplearrows')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1300);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1301, 'Triple Axel', 'Ice', 1, 10, 20, 90, 1, 'Физическая атака ледяного типа с силой 20. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'tripleaxel')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1301);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1302, 'Victory Dance', 'Fighting', 3, 10, 0, 0, 1, 'Статусная атака боевого типа. Повышает один или несколько параметров пользователя.', 'Повышает один или несколько параметров пользователя.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'victorydance')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1302);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1303, 'Wave Crash', 'Water', 1, 10, 120, 100, 1, 'Физическая атака водного типа с силой 120. Пользователь получает урон отдачи.', 'Пользователь получает урон отдачи.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'wavecrash')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1303);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1304, 'Wicked Blow', 'Dark', 1, 5, 75, 100, 1, 'Физическая атака темного типа с силой 75. Влияет на шанс критического удара.', 'Влияет на шанс критического удара.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'wickedblow')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1304);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1305, 'Wildbolt Storm', 'Electric', 2, 10, 100, 80, 1, 'Специальная атака электрического типа с силой 100. Может парализовать цель.', 'Может парализовать цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'wildboltstorm')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1305);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1306, 'Alluring Voice', 'Fairy', 2, 10, 80, 100, 1, 'Специальная атака волшебного типа с силой 80. Наносит обычный урон без дополнительного эффекта.', 'Наносит обычный урон без дополнительного эффекта.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'alluringvoice')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1306);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1307, 'Aqua Cutter', 'Water', 1, 20, 70, 100, 1, 'Физическая атака водного типа с силой 70. Влияет на шанс критического удара.', 'Влияет на шанс критического удара.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'aquacutter')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1307);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1308, 'Aqua Step', 'Water', 1, 10, 80, 100, 1, 'Физическая атака водного типа с силой 80. Повышает Скорость пользователя.', 'Повышает Скорость пользователя.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'aquastep')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1308);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1309, 'Armor Cannon', 'Fire', 2, 5, 120, 100, 1, 'Специальная атака огненного типа с силой 120. Понижает один или несколько параметров цели.', 'Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'armorcannon')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1309);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1310, 'Axe Kick', 'Fighting', 1, 10, 120, 90, 1, 'Физическая атака боевого типа с силой 120. Может спутать цель. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Может спутать цель. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'axekick')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1310);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1311, 'Bitter Blade', 'Fire', 1, 10, 90, 100, 1, 'Физическая атака огненного типа с силой 90. Пользователь восстанавливает половину нанесенного урона в HP.', 'Пользователь восстанавливает половину нанесенного урона в HP.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'bitterblade')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1311);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1312, 'Blazing Torque', 'Fire', 1, 10, 80, 100, 1, 'Физическая атака огненного типа с силой 80. Может поджечь цель.', 'Может поджечь цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'blazingtorque')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1312);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1313, 'Blood Moon', 'Normal', 2, 5, 140, 100, 1, 'Специальная атака обычного типа с силой 140. Нельзя использовать два раза подряд.', 'Нельзя использовать два раза подряд.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'bloodmoon')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1313);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1314, 'Burning Bulwark', 'Fire', 3, 10, 0, 0, 1, 'Статусная атака огненного типа. Статусная атака без отдельного описания эффекта в исходной таблице.', 'Статусная атака без отдельного описания эффекта в исходной таблице.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'burningbulwark')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1314);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1315, 'Chilling Water', 'Water', 2, 20, 50, 100, 1, 'Специальная атака водного типа с силой 50. Понижает Атаку цели.', 'Понижает Атаку цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'chillingwater')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1315);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1316, 'Chilly Reception', 'Ice', 3, 10, 0, 0, 1, 'Статусная атака ледяного типа. Влияет на погоду и связанные с ней бонусы.', 'Влияет на погоду и связанные с ней бонусы.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'chillyreception')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1316);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1317, 'Collision Course', 'Fighting', 1, 5, 100, 100, 1, 'Физическая атака боевого типа с силой 100. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'collisioncourse')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1317);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1318, 'Combat Torque', 'Fighting', 1, 10, 100, 100, 1, 'Физическая атака боевого типа с силой 100. Может парализовать цель.', 'Может парализовать цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'combattorque')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1318);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1319, 'Comeuppance', 'Dark', 1, 10, 0, 100, 1, 'Физическая атака темного типа. Наносит урон с дополнительным особым эффектом.', 'Наносит урон с дополнительным особым эффектом.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'comeuppance')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1319);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1320, 'Doodle', 'Normal', 3, 10, 0, 100, 1, 'Статусная атака обычного типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'doodle')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1320);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1321, 'Double Shock', 'Electric', 1, 5, 120, 100, 1, 'Физическая атака электрического типа с силой 120. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'doubleshock')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1321);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1322, 'Dragon Cheer', 'Dragon', 3, 15, 0, 0, 1, 'Статусная атака драконьего типа. Статусная атака без отдельного описания эффекта в исходной таблице.', 'Статусная атака без отдельного описания эффекта в исходной таблице.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'dragoncheer')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1322);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1323, 'Electro Drift', 'Electric', 2, 5, 100, 100, 1, 'Специальная атака электрического типа с силой 100. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'electrodrift')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1323);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1324, 'Electro Shot', 'Electric', 2, 10, 130, 100, 1, 'Специальная атака электрического типа с силой 130. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'electroshot')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1324);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1325, 'Fickle Beam', 'Dragon', 2, 5, 80, 100, 1, 'Специальная атака драконьего типа с силой 80. Наносит урон с дополнительным особым эффектом.', 'Наносит урон с дополнительным особым эффектом.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'ficklebeam')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1325);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1326, 'Fillet Away', 'Normal', 3, 10, 0, 0, 1, 'Статусная атака обычного типа. Понижает один или несколько параметров цели.', 'Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'filletaway')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1326);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1327, 'Flower Trick', 'Grass', 1, 10, 70, 0, 1, 'Физическая атака травяного типа с силой 70. Влияет на шанс критического удара.', 'Влияет на шанс критического удара.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'flowertrick')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1327);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1328, 'Gigaton Hammer', 'Steel', 1, 5, 160, 100, 1, 'Физическая атака стального типа с силой 160. Нельзя использовать два раза подряд.', 'Нельзя использовать два раза подряд.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gigatonhammer')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1328);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1329, 'Glaive Rush', 'Dragon', 1, 5, 120, 100, 1, 'Физическая атака драконьего типа с силой 120. Наносит урон с дополнительным особым эффектом.', 'Наносит урон с дополнительным особым эффектом.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'glaiverush')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1329);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1330, 'Hard Press', 'Steel', 1, 10, 0, 100, 1, 'Физическая атака стального типа. Наносит обычный урон без дополнительного эффекта.', 'Наносит обычный урон без дополнительного эффекта.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'hardpress')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1330);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1331, 'Hydro Steam', 'Water', 2, 15, 80, 100, 1, 'Специальная атака водного типа с силой 80. Влияет на погоду и связанные с ней бонусы.', 'Влияет на погоду и связанные с ней бонусы.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'hydrosteam')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1331);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1332, 'Hyper Drill', 'Normal', 1, 5, 100, 100, 1, 'Физическая атака обычного типа с силой 100. Дает защитный эффект на этот ход.', 'Дает защитный эффект на этот ход.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'hyperdrill')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1332);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1333, 'Ice Spinner', 'Ice', 1, 15, 80, 100, 1, 'Физическая атака ледяного типа с силой 80. Меняет поле боя и включает особый эффект местности.', 'Меняет поле боя и включает особый эффект местности.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'icespinner')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1333);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1334, 'Ivy Cudgel', 'Grass', 1, 0, 100, 0, 1, 'Физическая атака травяного типа с силой 100. Повышенный шанс критического удара. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Повышенный шанс критического удара. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'ivycudgel')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1334);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1335, 'Jet Punch', 'Water', 1, 15, 60, 100, 1, 'Физическая атака водного типа с силой 60. Почти всегда ходит первой.', 'Почти всегда ходит первой.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'jetpunch')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1335);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1336, 'Kowtow Cleave', 'Dark', 1, 10, 85, 0, 1, 'Физическая атака темного типа с силой 85. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'kowtowcleave')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1336);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1337, 'Last Respects', 'Ghost', 1, 10, 50, 100, 1, 'Физическая атака призрачного типа с силой 50. Наносит урон с дополнительным особым эффектом.', 'Наносит урон с дополнительным особым эффектом.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'lastrespects')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1337);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1338, 'Lumina Crash', 'Psychic', 2, 10, 80, 100, 1, 'Специальная атака психического типа с силой 80. Понижает один или несколько параметров цели.', 'Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'luminacrash')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1338);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1339, 'Magical Torque', 'Fairy', 1, 10, 100, 100, 1, 'Физическая атака волшебного типа с силой 100. Может спутать цель.', 'Может спутать цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'magicaltorque')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1339);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1340, 'Make It Rain', 'Steel', 2, 5, 120, 100, 1, 'Специальная атака стального типа с силой 120. Понижает один или несколько параметров цели. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Понижает один или несколько параметров цели. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'makeitrain')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1340);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1341, 'Malignant Chain', 'Poison', 2, 5, 100, 100, 1, 'Специальная атака ядовитого типа с силой 100. Может отравить цель.', 'Может отравить цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'malignantchain')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1341);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1342, 'Matcha Gotcha', 'Grass', 2, 15, 80, 90, 1, 'Специальная атака травяного типа с силой 80. Может поджечь цель.', 'Может поджечь цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'matchagotcha')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1342);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1343, 'Mighty Cleave', 'Rock', 1, 5, 95, 100, 1, 'Физическая атака каменного типа с силой 95. Наносит обычный урон без дополнительного эффекта.', 'Наносит обычный урон без дополнительного эффекта.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'mightycleave')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1343);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1344, 'Mortal Spin', 'Poison', 1, 15, 30, 100, 1, 'Физическая атака ядовитого типа с силой 30. Мешает смене и может наносить периодический урон.', 'Мешает смене и может наносить периодический урон.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'mortalspin')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1344);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1345, 'Noxious Torque', 'Poison', 1, 10, 100, 100, 1, 'Физическая атака ядовитого типа с силой 100. Может отравить цель.', 'Может отравить цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'noxioustorque')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1345);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1346, 'Order Up', 'Dragon', 1, 10, 80, 100, 1, 'Физическая атака драконьего типа с силой 80. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'orderup')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1346);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1347, 'Population Bomb', 'Normal', 1, 10, 20, 90, 1, 'Физическая атака обычного типа с силой 20. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'populationbomb')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1347);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1348, 'Pounce', 'Bug', 1, 20, 50, 100, 1, 'Физическая атака насекомого типа с силой 50. Понижает Скорость цели.', 'Понижает Скорость цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'pounce')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1348);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1349, 'Psyblade', 'Psychic', 1, 15, 80, 100, 1, 'Физическая атака психического типа с силой 80. Меняет поле боя и включает особый эффект местности.', 'Меняет поле боя и включает особый эффект местности.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'psyblade')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1349);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1350, 'Psychic Noise', 'Psychic', 2, 10, 75, 100, 1, 'Специальная атака психического типа с силой 75. Наносит урон с дополнительным особым эффектом.', 'Наносит урон с дополнительным особым эффектом.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'psychicnoise')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1350);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1351, 'Rage Fist', 'Ghost', 1, 10, 50, 100, 1, 'Физическая атака призрачного типа с силой 50. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'ragefist')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1351);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1352, 'Raging Bull', 'Normal', 1, 10, 90, 100, 1, 'Физическая атака обычного типа с силой 90. Особый эффект поздних поколений: требует точной настройки в боевом движке. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'ragingbull')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1352);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1353, 'Revival Blessing', 'Normal', 3, 1, 0, 0, 1, 'Статусная атака обычного типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'revivalblessing')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1353);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1354, 'Ruination', 'Dark', 2, 10, 0, 90, 1, 'Специальная атака темного типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'ruination')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1354);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1355, 'Salt Cure', 'Rock', 1, 15, 40, 100, 1, 'Физическая атака каменного типа с силой 40. Наносит урон с дополнительным особым эффектом.', 'Наносит урон с дополнительным особым эффектом.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'saltcure')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1355);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1356, 'Shed Tail', 'Normal', 3, 10, 0, 0, 1, 'Статусная атака обычного типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'shedtail')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1356);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1357, 'Silk Trap', 'Bug', 3, 10, 0, 0, 1, 'Статусная атака насекомого типа. Дает защитный эффект на этот ход.', 'Дает защитный эффект на этот ход.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'silktrap')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1357);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1358, 'Snowscape', 'Ice', 3, 10, 0, 0, 1, 'Статусная атака ледяного типа. Повышает один или несколько параметров пользователя.', 'Повышает один или несколько параметров пользователя.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'snowscape')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1358);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1359, 'Spicy Extract', 'Grass', 3, 15, 0, 0, 1, 'Статусная атака травяного типа. Понижает один или несколько параметров цели.', 'Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'spicyextract')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1359);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1360, 'Spin Out', 'Steel', 1, 5, 100, 100, 1, 'Физическая атака стального типа с силой 100. Понижает один или несколько параметров цели.', 'Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'spinout')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1360);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1361, 'Supercell Slam', 'Electric', 1, 15, 100, 95, 1, 'Физическая атака электрического типа с силой 100. Наносит обычный урон без дополнительного эффекта.', 'Наносит обычный урон без дополнительного эффекта.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'supercellslam')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1361);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1362, 'Syrup Bomb', 'Grass', 2, 10, 60, 85, 1, 'Специальная атака травяного типа с силой 60. Понижает один или несколько параметров цели.', 'Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'syrupbomb')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1362);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1363, 'Tachyon Cutter', 'Steel', 2, 10, 50, 0, 1, 'Специальная атака стального типа с силой 50. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'tachyoncutter')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1363);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1364, 'Temper Flare', 'Fire', 1, 10, 75, 100, 1, 'Физическая атака огненного типа с силой 75. Наносит обычный урон без дополнительного эффекта.', 'Наносит обычный урон без дополнительного эффекта.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'temperflare')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1364);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1365, 'Tera Blast', 'Normal', 2, 10, 80, 100, 1, 'Специальная атака обычного типа с силой 80. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'terablast')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1365);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1366, 'Tera Starstorm', 'Normal', 2, 5, 120, 100, 1, 'Специальная атака обычного типа с силой 120. Наносит обычный урон без дополнительного эффекта.', 'Наносит обычный урон без дополнительного эффекта.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'terastarstorm')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1366);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1367, 'Thunderclap', 'Electric', 2, 5, 70, 100, 1, 'Специальная атака электрического типа с силой 70. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'thunderclap')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1367);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1368, 'Tidy Up', 'Normal', 3, 10, 0, 0, 1, 'Статусная атака обычного типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'tidyup')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1368);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1369, 'Torch Song', 'Fire', 2, 10, 80, 100, 1, 'Специальная атака огненного типа с силой 80. Повышает Спец. атаку пользователя.', 'Повышает Спец. атаку пользователя.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'torchsong')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1369);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1370, 'Trailblaze', 'Grass', 1, 20, 50, 100, 1, 'Физическая атака травяного типа с силой 50. Повышает Скорость пользователя.', 'Повышает Скорость пользователя.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'trailblaze')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1370);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1371, 'Triple Dive', 'Water', 1, 10, 30, 95, 1, 'Физическая атака водного типа с силой 30. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'tripledive')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1371);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1372, 'Twin Beam', 'Psychic', 2, 10, 40, 100, 1, 'Специальная атака психического типа с силой 40. Бьет два раза за один ход.', 'Бьет два раза за один ход.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'twinbeam')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1372);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1373, 'Upper Hand', 'Fighting', 1, 15, 65, 100, 1, 'Физическая атака боевого типа с силой 65. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'upperhand')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1373);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 1374, 'Wicked Torque', 'Dark', 1, 10, 80, 100, 1, 'Физическая атака темного типа с силой 80. Может взаимодействовать со сном.', 'Может взаимодействовать со сном.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'wickedtorque')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 1374);
