-- Import attacks missing from pokemon_moves_by_gen.xlsx by normalized attack name.
-- Existing attacks are not updated: this migration only inserts rows whose name is absent.
-- Existing catalog ends at 559; imported attacks use 560..934, while service rows 997..999 stay at the end.
-- Name aliases treated as existing during generation: High Jump Kick/Hi Jump Kick, Vise Grip/ViceGrip, Smelling Salts/SmellingSalt, Feint Attack/Faint Attack.

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 560, 'Aromatic Mist', 'Fairy', 3, 20, 0, 0, 1, 'Статусная атака волшебного типа. Повышает один или несколько параметров пользователя.', 'Повышает один или несколько параметров пользователя.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'aromaticmist')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 560);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 561, 'Baby-Doll Eyes', 'Fairy', 3, 30, 0, 100, 1, 'Статусная атака волшебного типа. Почти всегда ходит первой. Понижает один или несколько параметров цели.', 'Почти всегда ходит первой. Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'babydolleyes')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 561);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 562, 'Belch', 'Poison', 2, 10, 120, 90, 1, 'Специальная атака ядовитого типа с силой 120. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'belch')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 562);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 563, 'Boomburst', 'Normal', 2, 10, 140, 100, 1, 'Специальная атака обычного типа с силой 140. Бьет всех соседних покемонов.', 'Бьет всех соседних покемонов.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'boomburst')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 563);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 564, 'Celebrate', 'Normal', 3, 40, 0, 0, 1, 'Статусная атака обычного типа. Особый эффект поздних поколений: требует точной настройки в боевом движке. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'celebrate')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 564);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 565, 'Confide', 'Normal', 3, 20, 0, 0, 1, 'Статусная атака обычного типа. Понижает Спец. атаку цели.', 'Понижает Спец. атаку цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'confide')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 565);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 566, 'Crafty Shield', 'Fairy', 3, 10, 0, 0, 1, 'Статусная атака волшебного типа. Дает защитный эффект на этот ход.', 'Дает защитный эффект на этот ход.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'craftyshield')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 566);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 567, 'Dazzling Gleam', 'Fairy', 2, 10, 80, 100, 1, 'Специальная атака волшебного типа с силой 80. Бьет всех соседних противников.', 'Бьет всех соседних противников.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'dazzlinggleam')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 567);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 568, 'Diamond Storm', 'Rock', 1, 5, 100, 95, 1, 'Физическая атака каменного типа с силой 100. Повышает один или несколько параметров пользователя.', 'Повышает один или несколько параметров пользователя.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'diamondstorm')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 568);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 569, 'Disarming Voice', 'Fairy', 2, 15, 40, 0, 1, 'Специальная атака волшебного типа с силой 40. Игнорирует точность и уклонение.', 'Игнорирует точность и уклонение.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'disarmingvoice')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 569);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 570, 'Dragon Ascent', 'Flying', 1, 5, 120, 100, 1, 'Физическая атака летающего типа с силой 120. Понижает один или несколько параметров цели.', 'Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'dragonascent')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 570);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 571, 'Draining Kiss', 'Fairy', 2, 10, 50, 100, 1, 'Специальная атака волшебного типа с силой 50. Пользователь восстанавливает большую часть нанесенного урона в HP.', 'Пользователь восстанавливает большую часть нанесенного урона в HP.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'drainingkiss')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 571);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 572, 'Eerie Impulse', 'Electric', 3, 15, 0, 100, 1, 'Статусная атака электрического типа. Понижает один или несколько параметров цели.', 'Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'eerieimpulse')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 572);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 573, 'Electric Terrain', 'Electric', 3, 10, 0, 0, 1, 'Статусная атака электрического типа. Может взаимодействовать со сном.', 'Может взаимодействовать со сном.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'electricterrain')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 573);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 574, 'Electrify', 'Electric', 3, 20, 0, 0, 1, 'Статусная атака электрического типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'electrify')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 574);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 575, 'Fairy Lock', 'Fairy', 3, 10, 0, 0, 1, 'Статусная атака волшебного типа. Влияет на смену покемона или возможность отступления.', 'Влияет на смену покемона или возможность отступления.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'fairylock')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 575);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 576, 'Fairy Wind', 'Fairy', 2, 30, 40, 100, 1, 'Специальная атака волшебного типа с силой 40. Наносит обычный урон без дополнительного эффекта.', 'Наносит обычный урон без дополнительного эффекта.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'fairywind')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 576);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 577, 'Fell Stinger', 'Bug', 1, 25, 50, 100, 1, 'Физическая атака насекомого типа с силой 50. Повышает один или несколько параметров пользователя.', 'Повышает один или несколько параметров пользователя.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'fellstinger')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 577);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 578, 'Flower Shield', 'Fairy', 3, 10, 0, 0, 1, 'Статусная атака волшебного типа. Повышает один или несколько параметров пользователя.', 'Повышает один или несколько параметров пользователя.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'flowershield')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 578);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 579, 'Flying Press', 'Fighting', 1, 10, 100, 95, 1, 'Физическая атака боевого типа с силой 100. Наносит урон с дополнительным особым эффектом.', 'Наносит урон с дополнительным особым эффектом.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'flyingpress')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 579);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 580, 'Forest''s Curse', 'Grass', 3, 20, 0, 100, 1, 'Статусная атака травяного типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'forestscurse')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 580);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 581, 'Freeze-Dry', 'Ice', 2, 20, 70, 100, 1, 'Специальная атака ледяного типа с силой 70. Может заморозить цель. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Может заморозить цель. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'freezedry')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 581);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 582, 'Geomancy', 'Fairy', 3, 10, 0, 0, 1, 'Статусная атака волшебного типа. Повышает один или несколько параметров пользователя. Особый эффект поздних поколений: требует точной настройки в боевом движке. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Повышает один или несколько параметров пользователя. Особый эффект поздних поколений: требует точной настройки в боевом движке. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'geomancy')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 582);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 583, 'Grassy Terrain', 'Grass', 3, 10, 0, 0, 1, 'Статусная атака травяного типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'grassyterrain')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 583);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 584, 'Happy Hour', 'Normal', 3, 30, 0, 0, 1, 'Статусная атака обычного типа. Влияет на погоду и связанные с ней бонусы.', 'Влияет на погоду и связанные с ней бонусы.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'happyhour')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 584);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 585, 'Hold Back', 'Normal', 1, 40, 40, 100, 1, 'Физическая атака обычного типа с силой 40. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'holdback')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 585);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 586, 'Hold Hands', 'Normal', 3, 40, 0, 0, 1, 'Статусная атака обычного типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'holdhands')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 586);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 587, 'Hyperspace Fury', 'Dark', 1, 5, 100, 0, 1, 'Физическая атака темного типа с силой 100. Понижает один или несколько параметров цели. Дает защитный эффект на этот ход.', 'Понижает один или несколько параметров цели. Дает защитный эффект на этот ход.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'hyperspacefury')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 587);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 588, 'Hyperspace Hole', 'Psychic', 2, 5, 80, 0, 1, 'Специальная атака психического типа с силой 80. Дает защитный эффект на этот ход.', 'Дает защитный эффект на этот ход.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'hyperspacehole')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 588);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 589, 'Infestation', 'Bug', 2, 20, 20, 100, 1, 'Специальная атака насекомого типа с силой 20. Удерживает цель и наносит ей урон 4-5 ходов.', 'Удерживает цель и наносит ей урон 4-5 ходов.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'infestation')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 589);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 590, 'Ion Deluge', 'Electric', 3, 25, 0, 0, 1, 'Статусная атака электрического типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'iondeluge')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 590);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 591, 'King''s Shield', 'Steel', 3, 10, 0, 0, 1, 'Статусная атака стального типа. Дает защитный эффект на этот ход.', 'Дает защитный эффект на этот ход.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'kingsshield')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 591);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 592, 'Land''s Wrath', 'Ground', 1, 10, 90, 100, 1, 'Физическая атака земляного типа с силой 90. Наносит обычный урон без дополнительного эффекта.', 'Наносит обычный урон без дополнительного эффекта.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'landswrath')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 592);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 593, 'Light of Ruin', 'Fairy', 2, 5, 140, 90, 1, 'Специальная атака волшебного типа с силой 140. Пользователь получает урон отдачи.', 'Пользователь получает урон отдачи.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'lightofruin')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 593);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 594, 'Magnetic Flux', 'Electric', 3, 20, 0, 0, 1, 'Статусная атака электрического типа. Повышает один или несколько параметров пользователя. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Повышает один или несколько параметров пользователя. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'magneticflux')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 594);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 595, 'Mat Block', 'Fighting', 3, 10, 0, 0, 1, 'Статусная атака боевого типа. Дает защитный эффект на этот ход.', 'Дает защитный эффект на этот ход.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'matblock')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 595);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 596, 'Misty Terrain', 'Fairy', 3, 10, 0, 0, 1, 'Статусная атака волшебного типа. Дает защитный эффект на этот ход.', 'Дает защитный эффект на этот ход.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'mistyterrain')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 596);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 597, 'Moonblast', 'Fairy', 2, 15, 95, 100, 1, 'Специальная атака волшебного типа с силой 95. Может понизить Спец. атаку цели.', 'Может понизить Спец. атаку цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'moonblast')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 597);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 598, 'Mystical Fire', 'Fire', 2, 10, 75, 100, 1, 'Специальная атака огненного типа с силой 75. Понижает Спец. атаку цели.', 'Понижает Спец. атаку цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'mysticalfire')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 598);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 599, 'Noble Roar', 'Normal', 3, 30, 0, 100, 1, 'Статусная атака обычного типа. Понижает один или несколько параметров цели.', 'Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'nobleroar')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 599);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 600, 'Nuzzle', 'Electric', 1, 20, 20, 100, 1, 'Физическая атака электрического типа с силой 20. Парализует цель.', 'Парализует цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'nuzzle')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 600);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 601, 'Oblivion Wing', 'Flying', 2, 10, 80, 100, 1, 'Специальная атака летающего типа с силой 80. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'oblivionwing')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 601);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 602, 'Origin Pulse', 'Water', 2, 10, 110, 85, 1, 'Специальная атака водного типа с силой 110. Бьет всех соседних противников.', 'Бьет всех соседних противников.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'originpulse')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 602);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 603, 'Parabolic Charge', 'Electric', 2, 20, 65, 100, 1, 'Специальная атака электрического типа с силой 65. Пользователь восстанавливает половину нанесенного урона в HP.', 'Пользователь восстанавливает половину нанесенного урона в HP.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'paraboliccharge')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 603);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 604, 'Parting Shot', 'Dark', 3, 20, 0, 100, 1, 'Статусная атака темного типа. Влияет на смену покемона или возможность отступления.', 'Влияет на смену покемона или возможность отступления.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'partingshot')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 604);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 605, 'Petal Blizzard', 'Grass', 1, 15, 90, 100, 1, 'Физическая атака травяного типа с силой 90. Бьет всех соседних покемонов.', 'Бьет всех соседних покемонов.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'petalblizzard')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 605);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 606, 'Phantom Force', 'Ghost', 1, 10, 90, 100, 1, 'Физическая атака призрачного типа с силой 90. Особый эффект поздних поколений: требует точной настройки в боевом движке. Дает защитный эффект на этот ход.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке. Дает защитный эффект на этот ход.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'phantomforce')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 606);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 607, 'Play Nice', 'Normal', 3, 20, 0, 0, 1, 'Статусная атака обычного типа. Понижает Атаку цели. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Понижает Атаку цели. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'playnice')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 607);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 608, 'Play Rough', 'Fairy', 1, 10, 90, 90, 1, 'Физическая атака волшебного типа с силой 90. Может понизить Атаку цели.', 'Может понизить Атаку цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'playrough')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 608);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 609, 'Powder', 'Bug', 3, 20, 0, 100, 1, 'Статусная атака насекомого типа. Наносит урон с дополнительным особым эффектом.', 'Наносит урон с дополнительным особым эффектом.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'powder')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 609);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 610, 'Power-Up Punch', 'Fighting', 1, 20, 40, 100, 1, 'Физическая атака боевого типа с силой 40. Повышает один или несколько параметров пользователя.', 'Повышает один или несколько параметров пользователя.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'poweruppunch')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 610);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 611, 'Precipice Blades', 'Ground', 1, 10, 120, 85, 1, 'Физическая атака земляного типа с силой 120. Бьет всех соседних противников.', 'Бьет всех соседних противников.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'precipiceblades')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 611);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 612, 'Rototiller', 'Ground', 3, 10, 0, 0, 1, 'Статусная атака земляного типа. Повышает один или несколько параметров пользователя.', 'Повышает один или несколько параметров пользователя.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'rototiller')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 612);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 613, 'Spiky Shield', 'Grass', 3, 10, 0, 0, 1, 'Статусная атака травяного типа. Дает защитный эффект на этот ход.', 'Дает защитный эффект на этот ход.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'spikyshield')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 613);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 614, 'Steam Eruption', 'Water', 2, 5, 110, 95, 1, 'Специальная атака водного типа с силой 110. Может поджечь цель.', 'Может поджечь цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'steameruption')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 614);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 615, 'Sticky Web', 'Bug', 3, 20, 0, 0, 1, 'Статусная атака насекомого типа. Снижает Скорость противника при выходе в бой.', 'Снижает Скорость противника при выходе в бой.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'stickyweb')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 615);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 616, 'Thousand Arrows', 'Ground', 1, 10, 90, 100, 1, 'Физическая атака земляного типа с силой 90. Делает летающих покемонов уязвимыми к земляным атакам.', 'Делает летающих покемонов уязвимыми к земляным атакам.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'thousandarrows')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 616);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 617, 'Thousand Waves', 'Ground', 1, 10, 90, 100, 1, 'Физическая атака земляного типа с силой 90. Цель не может сбежать или смениться.', 'Цель не может сбежать или смениться.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'thousandwaves')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 617);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 618, 'Topsy-Turvy', 'Dark', 3, 20, 0, 0, 1, 'Статусная атака темного типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'topsyturvy')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 618);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 619, 'Trick-or-Treat', 'Ghost', 3, 20, 0, 100, 1, 'Статусная атака призрачного типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'trickortreat')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 619);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 620, 'Venom Drench', 'Poison', 3, 20, 0, 100, 1, 'Статусная атака ядовитого типа. Может отравить цель.', 'Может отравить цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'venomdrench')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 620);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 621, 'Water Shuriken', 'Water', 2, 20, 15, 100, 1, 'Специальная атака водного типа с силой 15. Бьет 2-5 раз за один ход.', 'Бьет 2-5 раз за один ход.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 6. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'watershuriken')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 621);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 622, '10,000,000 Volt Thunderbolt', 'Electric', 2, 1, 195, 0, 1, 'Специальная атака электрического типа с силой 195. Особый эффект поздних поколений: требует точной настройки в боевом движке. Влияет на шанс критического удара.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке. Влияет на шанс критического удара.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = '10000000voltthunderbolt')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 622);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 623, 'Accelerock', 'Rock', 1, 20, 40, 100, 1, 'Физическая атака каменного типа с силой 40. Пользователь атакует первым.', 'Пользователь атакует первым.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'accelerock')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 623);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 624, 'Acid Downpour', 'Poison', 0, 1, 0, 0, 1, 'Особая атака ядовитого типа. Z-атака ядовитого типа.', 'Z-атака ядовитого типа.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'aciddownpour')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 624);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 625, 'All-Out Pummeling', 'Fighting', 0, 1, 0, 0, 1, 'Особая атака боевого типа. Z-атака боевого типа.', 'Z-атака боевого типа.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'alloutpummeling')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 625);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 626, 'Anchor Shot', 'Steel', 1, 20, 80, 100, 1, 'Физическая атака стального типа с силой 80. Особый эффект поздних поколений: требует точной настройки в боевом движке. Влияет на смену покемона или возможность отступления.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке. Влияет на смену покемона или возможность отступления.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'anchorshot')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 626);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 627, 'Aurora Veil', 'Ice', 3, 20, 0, 0, 1, 'Статусная атака ледяного типа. Наносит урон с дополнительным особым эффектом.', 'Наносит урон с дополнительным особым эффектом.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'auroraveil')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 627);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 628, 'Baddy Bad', 'Dark', 2, 15, 90, 100, 1, 'Специальная атака темного типа с силой 90. Наносит урон с дополнительным особым эффектом.', 'Наносит урон с дополнительным особым эффектом.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'baddybad')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 628);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 629, 'Baneful Bunker', 'Poison', 3, 10, 0, 0, 1, 'Статусная атака ядовитого типа. Защищает пользователя и отравляет противника при контакте.', 'Защищает пользователя и отравляет противника при контакте.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'banefulbunker')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 629);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 630, 'Beak Blast', 'Flying', 1, 15, 100, 100, 1, 'Физическая атака летающего типа с силой 100. Особый эффект поздних поколений: требует точной настройки в боевом движке. Может поджечь цель.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке. Может поджечь цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'beakblast')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 630);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 631, 'Black Hole Eclipse', 'Dark', 0, 1, 0, 0, 1, 'Особая атака темного типа. Z-атака темного типа.', 'Z-атака темного типа.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'blackholeeclipse')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 631);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 632, 'Bloom Doom', 'Grass', 0, 1, 0, 0, 1, 'Особая атака травяного типа. Z-атака травяного типа.', 'Z-атака травяного типа.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'bloomdoom')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 632);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 633, 'Bouncy Bubble', 'Water', 2, 15, 90, 100, 1, 'Специальная атака водного типа с силой 90. Пользователь восстанавливает половину нанесенного урона в HP.', 'Пользователь восстанавливает половину нанесенного урона в HP.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'bouncybubble')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 633);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 634, 'Breakneck Blitz', 'Normal', 0, 1, 0, 0, 1, 'Особая атака обычного типа. Z-атака обычного типа.', 'Z-атака обычного типа.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'breakneckblitz')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 634);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 635, 'Brutal Swing', 'Dark', 1, 20, 60, 100, 1, 'Физическая атака темного типа с силой 60. Наносит урон с дополнительным особым эффектом.', 'Наносит урон с дополнительным особым эффектом.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'brutalswing')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 635);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 636, 'Burn Up', 'Fire', 2, 5, 130, 100, 1, 'Специальная атака огненного типа с силой 130. Может поджечь цель. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Может поджечь цель. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'burnup')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 636);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 637, 'Buzzy Buzz', 'Electric', 2, 15, 90, 100, 1, 'Специальная атака электрического типа с силой 90. Может парализовать цель.', 'Может парализовать цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'buzzybuzz')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 637);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 638, 'Catastropika', 'Electric', 1, 1, 210, 0, 1, 'Физическая атака электрического типа с силой 210. Эксклюзивная Z-атака для Pikachu.', 'Эксклюзивная Z-атака для Pikachu.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'catastropika')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 638);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 639, 'Clanging Scales', 'Dragon', 2, 5, 110, 100, 1, 'Специальная атака драконьего типа с силой 110. Понижает один или несколько параметров цели.', 'Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'clangingscales')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 639);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 640, 'Clangorous Soulblaze', 'Dragon', 2, 1, 185, 0, 1, 'Специальная атака драконьего типа с силой 185. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'clangoroussoulblaze')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 640);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 641, 'Continental Crush', 'Rock', 0, 1, 0, 0, 1, 'Особая атака каменного типа. Z-атака каменного типа.', 'Z-атака каменного типа.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'continentalcrush')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 641);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 642, 'Core Enforcer', 'Dragon', 2, 10, 100, 100, 1, 'Специальная атака драконьего типа с силой 100. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'coreenforcer')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 642);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 643, 'Corkscrew Crash', 'Steel', 0, 1, 0, 0, 1, 'Особая атака стального типа. Z-атака стального типа.', 'Z-атака стального типа.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'corkscrewcrash')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 643);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 644, 'Darkest Lariat', 'Dark', 1, 10, 85, 100, 1, 'Физическая атака темного типа с силой 85. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'darkestlariat')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 644);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 645, 'Devastating Drake', 'Dragon', 0, 1, 0, 0, 1, 'Особая атака драконьего типа. Z-атака драконьего типа.', 'Z-атака драконьего типа.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'devastatingdrake')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 645);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 646, 'Double Iron Bash', 'Steel', 1, 5, 60, 100, 1, 'Физическая атака стального типа с силой 60. Может испугать цель.', 'Может испугать цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'doubleironbash')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 646);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 647, 'Dragon Hammer', 'Dragon', 1, 15, 90, 100, 1, 'Физическая атака драконьего типа с силой 90. Наносит урон с дополнительным особым эффектом.', 'Наносит урон с дополнительным особым эффектом.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'dragonhammer')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 647);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 648, 'Extreme Evoboost', 'Normal', 3, 1, 0, 0, 1, 'Статусная атака обычного типа. Особый эффект поздних поколений: требует точной настройки в боевом движке. Повышает один или несколько параметров пользователя.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке. Повышает один или несколько параметров пользователя.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'extremeevoboost')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 648);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 649, 'Fire Lash', 'Fire', 1, 15, 80, 100, 1, 'Физическая атака огненного типа с силой 80. Может поджечь цель. Понижает один или несколько параметров цели.', 'Может поджечь цель. Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'firelash')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 649);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 650, 'First Impression', 'Bug', 1, 10, 90, 100, 1, 'Физическая атака насекомого типа с силой 90. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'firstimpression')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 650);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 651, 'Fleur Cannon', 'Fairy', 2, 5, 130, 90, 1, 'Специальная атака волшебного типа с силой 130. Понижает один или несколько параметров цели.', 'Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'fleurcannon')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 651);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 652, 'Floaty Fall', 'Flying', 1, 15, 90, 95, 1, 'Физическая атака летающего типа с силой 90. Может испугать цель.', 'Может испугать цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'floatyfall')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 652);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 653, 'Floral Healing', 'Fairy', 3, 10, 0, 0, 1, 'Статусная атака волшебного типа. Особый эффект поздних поколений: требует точной настройки в боевом движке. Меняет поле боя и включает особый эффект местности.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке. Меняет поле боя и включает особый эффект местности.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'floralhealing')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 653);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 654, 'Freezy Frost', 'Ice', 2, 15, 90, 100, 1, 'Специальная атака ледяного типа с силой 90. Сбрасывает все изменения статов.', 'Сбрасывает все изменения статов.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'freezyfrost')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 654);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 655, 'Gear Up', 'Steel', 3, 20, 0, 0, 1, 'Статусная атака стального типа. Повышает один или несколько параметров пользователя. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Повышает один или несколько параметров пользователя. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gearup')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 655);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 656, 'Genesis Supernova', 'Psychic', 2, 1, 185, 0, 1, 'Специальная атака психического типа с силой 185. Эксклюзивная Z-атака для Mew.', 'Эксклюзивная Z-атака для Mew.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'genesissupernova')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 656);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 657, 'Gigavolt Havoc', 'Electric', 0, 1, 0, 0, 1, 'Особая атака электрического типа. Z-атака электрического типа.', 'Z-атака электрического типа.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gigavolthavoc')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 657);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 658, 'Glitzy Glow', 'Psychic', 2, 15, 90, 100, 1, 'Специальная атака психического типа с силой 90. Наносит урон с дополнительным особым эффектом.', 'Наносит урон с дополнительным особым эффектом.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'glitzyglow')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 658);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 659, 'Guardian of Alola', 'Fairy', 2, 1, 0, 0, 1, 'Специальная атака волшебного типа. Особый эффект поздних поколений: требует точной настройки в боевом движке. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'guardianofalola')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 659);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 660, 'High Horsepower', 'Ground', 1, 10, 95, 95, 1, 'Физическая атака земляного типа с силой 95. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'highhorsepower')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 660);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 661, 'Hydro Vortex', 'Water', 0, 1, 0, 0, 1, 'Особая атака водного типа. Z-атака водного типа.', 'Z-атака водного типа.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'hydrovortex')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 661);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 662, 'Ice Hammer', 'Ice', 1, 10, 100, 90, 1, 'Физическая атака ледяного типа с силой 100. Особый эффект поздних поколений: требует точной настройки в боевом движке. Понижает один или несколько параметров цели.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке. Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'icehammer')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 662);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 663, 'Inferno Overdrive', 'Fire', 0, 1, 0, 0, 1, 'Особая атака огненного типа. Z-атака огненного типа.', 'Z-атака огненного типа.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'infernooverdrive')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 663);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 664, 'Instruct', 'Psychic', 3, 15, 0, 0, 1, 'Статусная атака психического типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'instruct')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 664);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 665, 'Laser Focus', 'Normal', 3, 30, 0, 0, 1, 'Статусная атака обычного типа. Влияет на шанс критического удара.', 'Влияет на шанс критического удара.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'laserfocus')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 665);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 666, 'Leafage', 'Grass', 1, 40, 40, 100, 1, 'Физическая атака травяного типа с силой 40. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'leafage')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 666);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 667, 'Let''s Snuggle Forever', 'Fairy', 1, 1, 190, 0, 1, 'Физическая атака волшебного типа с силой 190. Эксклюзивная Z-атака для Mimikyu.', 'Эксклюзивная Z-атака для Mimikyu.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'letssnuggleforever')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 667);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 668, 'Light That Burns the Sky', 'Psychic', 2, 1, 200, 0, 1, 'Специальная атака психического типа с силой 200. Особый эффект поздних поколений: требует точной настройки в боевом движке. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'lightthatburnsthesky')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 668);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 669, 'Liquidation', 'Water', 1, 10, 85, 100, 1, 'Физическая атака водного типа с силой 85. Может понизить Защиту цели.', 'Может понизить Защиту цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'liquidation')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 669);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 670, 'Lunge', 'Bug', 1, 15, 80, 100, 1, 'Физическая атака насекомого типа с силой 80. Особый эффект поздних поколений: требует точной настройки в боевом движке. Понижает один или несколько параметров цели.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке. Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'lunge')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 670);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 671, 'Malicious Moonsault', 'Dark', 1, 1, 180, 0, 1, 'Физическая атака темного типа с силой 180. Эксклюзивная Z-атака для Incineroar.', 'Эксклюзивная Z-атака для Incineroar.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'maliciousmoonsault')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 671);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 672, 'Menacing Moonraze Maelstrom', 'Ghost', 2, 1, 200, 0, 1, 'Специальная атака призрачного типа с силой 200. Эксклюзивная Z-атака для Lunala.', 'Эксклюзивная Z-атака для Lunala.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'menacingmoonrazemaelstrom')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 672);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 673, 'Mind Blown', 'Fire', 2, 5, 150, 100, 1, 'Специальная атака огненного типа с силой 150. Пользователь получает урон отдачи.', 'Пользователь получает урон отдачи.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'mindblown')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 673);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 674, 'Moongeist Beam', 'Ghost', 2, 5, 100, 100, 1, 'Специальная атака призрачного типа с силой 100. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'moongeistbeam')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 674);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 675, 'Multi-Attack', 'Normal', 1, 10, 120, 100, 1, 'Физическая атака обычного типа с силой 120. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'multiattack')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 675);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 676, 'Nature''s Madness', 'Fairy', 2, 10, 0, 90, 1, 'Специальная атака волшебного типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'naturesmadness')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 676);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 677, 'Never-Ending Nightmare', 'Ghost', 0, 1, 0, 0, 1, 'Особая атака призрачного типа. Z-атака призрачного типа.', 'Z-атака призрачного типа.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'neverendingnightmare')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 677);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 678, 'Oceanic Operetta', 'Water', 2, 1, 195, 0, 1, 'Специальная атака водного типа с силой 195. Эксклюзивная Z-атака для Primarina.', 'Эксклюзивная Z-атака для Primarina.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'oceanicoperetta')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 678);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 679, 'Photon Geyser', 'Psychic', 2, 5, 100, 100, 1, 'Специальная атака психического типа с силой 100. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'photongeyser')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 679);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 680, 'Pika Papow', 'Electric', 2, 20, 0, 0, 1, 'Специальная атака электрического типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'pikapapow')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 680);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 681, 'Plasma Fists', 'Electric', 1, 15, 100, 100, 1, 'Физическая атака электрического типа с силой 100. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'plasmafists')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 681);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 682, 'Pollen Puff', 'Bug', 2, 15, 90, 100, 1, 'Специальная атака насекомого типа с силой 90. Наносит урон с дополнительным особым эффектом.', 'Наносит урон с дополнительным особым эффектом.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'pollenpuff')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 682);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 683, 'Power Trip', 'Dark', 1, 10, 20, 100, 1, 'Физическая атака темного типа с силой 20. Особый эффект поздних поколений: требует точной настройки в боевом движке. Повышает один или несколько параметров пользователя.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке. Повышает один или несколько параметров пользователя.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'powertrip')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 683);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 684, 'Prismatic Laser', 'Psychic', 2, 10, 160, 100, 1, 'Специальная атака психического типа с силой 160. Особый эффект поздних поколений: требует точной настройки в боевом движке. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'prismaticlaser')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 684);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 685, 'Psychic Fangs', 'Psychic', 1, 10, 85, 100, 1, 'Физическая атака психического типа с силой 85. Особый эффект поздних поколений: требует точной настройки в боевом движке. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'psychicfangs')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 685);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 686, 'Psychic Terrain', 'Psychic', 3, 10, 0, 0, 1, 'Статусная атака психического типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'psychicterrain')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 686);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 687, 'Pulverizing Pancake', 'Normal', 1, 1, 210, 0, 1, 'Физическая атака обычного типа с силой 210. Z-атака snorlax-exclusive normal типа.', 'Z-атака snorlax-exclusive normal типа.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'pulverizingpancake')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 687);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 688, 'Purify', 'Poison', 3, 20, 0, 0, 1, 'Статусная атака ядовитого типа. Особый эффект поздних поколений: требует точной настройки в боевом движке. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'purify')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 688);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 689, 'Revelation Dance', 'Normal', 2, 15, 90, 100, 1, 'Специальная атака обычного типа с силой 90. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'revelationdance')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 689);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 690, 'Sappy Seed', 'Grass', 1, 15, 90, 100, 1, 'Физическая атака травяного типа с силой 90. Влияет на погоду и связанные с ней бонусы.', 'Влияет на погоду и связанные с ней бонусы.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'sappyseed')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 690);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 691, 'Savage Spin-Out', 'Bug', 0, 1, 0, 0, 1, 'Особая атака насекомого типа. Z-атака насекомого типа.', 'Z-атака насекомого типа.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'savagespinout')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 691);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 692, 'Searing Sunraze Smash', 'Steel', 1, 1, 200, 0, 1, 'Физическая атака стального типа с силой 200. Эксклюзивная Z-атака для Solgaleo.', 'Эксклюзивная Z-атака для Solgaleo.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'searingsunrazesmash')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 692);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 693, 'Shadow Bone', 'Ghost', 1, 10, 85, 100, 1, 'Физическая атака призрачного типа с силой 85. Может понизить Защиту цели.', 'Может понизить Защиту цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'shadowbone')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 693);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 694, 'Shattered Psyche', 'Psychic', 0, 1, 0, 0, 1, 'Особая атака психического типа. Z-атака психического типа.', 'Z-атака психического типа.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'shatteredpsyche')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 694);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 695, 'Shell Trap', 'Fire', 2, 5, 150, 100, 1, 'Специальная атака огненного типа с силой 150. Наносит урон с дополнительным особым эффектом.', 'Наносит урон с дополнительным особым эффектом.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'shelltrap')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 695);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 696, 'Shore Up', 'Ground', 3, 5, 0, 0, 1, 'Статусная атака земляного типа. Особый эффект поздних поколений: требует точной настройки в боевом движке. Влияет на погоду и связанные с ней бонусы.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке. Влияет на погоду и связанные с ней бонусы.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'shoreup')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 696);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 697, 'Sinister Arrow Raid', 'Ghost', 1, 1, 180, 0, 1, 'Физическая атака призрачного типа с силой 180. Эксклюзивная Z-атака для Decidueye.', 'Эксклюзивная Z-атака для Decidueye.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'sinisterarrowraid')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 697);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 698, 'Sizzly Slide', 'Fire', 1, 15, 90, 100, 1, 'Физическая атака огненного типа с силой 90. Поджигает цель.', 'Поджигает цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'sizzlyslide')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 698);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 699, 'Smart Strike', 'Steel', 1, 10, 70, 0, 1, 'Физическая атака стального типа с силой 70. Особый эффект поздних поколений: требует точной настройки в боевом движке. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'smartstrike')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 699);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 700, 'Solar Blade', 'Grass', 1, 10, 125, 100, 1, 'Физическая атака травяного типа с силой 125. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'solarblade')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 700);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 701, 'Soul-Stealing 7-Star Strike', 'Ghost', 1, 1, 195, 0, 1, 'Физическая атака призрачного типа с силой 195. Эксклюзивная Z-атака для Marshadow.', 'Эксклюзивная Z-атака для Marshadow.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'soulstealing7starstrike')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 701);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 702, 'Sparkling Aria', 'Water', 2, 10, 90, 100, 1, 'Специальная атака водного типа с силой 90. Может поджечь цель.', 'Может поджечь цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'sparklingaria')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 702);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 703, 'Sparkly Swirl', 'Fairy', 2, 15, 90, 100, 1, 'Специальная атака волшебного типа с силой 90. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'sparklyswirl')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 703);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 704, 'Spectral Thief', 'Ghost', 1, 10, 90, 100, 1, 'Физическая атака призрачного типа с силой 90. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'spectralthief')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 704);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 705, 'Speed Swap', 'Psychic', 3, 10, 0, 0, 1, 'Статусная атака психического типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'speedswap')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 705);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 706, 'Spirit Shackle', 'Ghost', 1, 10, 80, 100, 1, 'Физическая атака призрачного типа с силой 80. Влияет на смену покемона или возможность отступления.', 'Влияет на смену покемона или возможность отступления.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'spiritshackle')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 706);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 707, 'Splintered Stormshards', 'Rock', 1, 1, 190, 0, 1, 'Физическая атака каменного типа с силой 190. Эксклюзивная Z-атака для Lycanroc.', 'Эксклюзивная Z-атака для Lycanroc.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'splinteredstormshards')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 707);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 708, 'Splishy Splash', 'Water', 2, 15, 90, 100, 1, 'Специальная атака водного типа с силой 90. Может парализовать цель.', 'Может парализовать цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'splishysplash')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 708);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 709, 'Spotlight', 'Normal', 3, 15, 0, 0, 1, 'Статусная атака обычного типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'spotlight')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 709);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 710, 'Stoked Sparksurfer', 'Electric', 2, 1, 175, 0, 1, 'Специальная атака электрического типа с силой 175. Z-атака alolan raichu-exclusive electric типа.', 'Z-атака alolan raichu-exclusive electric типа.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'stokedsparksurfer')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 710);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 711, 'Stomping Tantrum', 'Ground', 1, 10, 75, 100, 1, 'Физическая атака земляного типа с силой 75. Особый эффект поздних поколений: требует точной настройки в боевом движке. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'stompingtantrum')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 711);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 712, 'Strength Sap', 'Grass', 3, 10, 0, 100, 1, 'Статусная атака травяного типа. Особый эффект поздних поколений: требует точной настройки в боевом движке. Понижает один или несколько параметров цели.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке. Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'strengthsap')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 712);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 713, 'Subzero Slammer', 'Ice', 0, 1, 0, 0, 1, 'Особая атака ледяного типа. Z-атака ледяного типа.', 'Z-атака ледяного типа.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'subzeroslammer')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 713);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 714, 'Sunsteel Strike', 'Steel', 1, 5, 100, 100, 1, 'Физическая атака стального типа с силой 100. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'sunsteelstrike')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 714);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 715, 'Supersonic Skystrike', 'Flying', 0, 1, 0, 0, 1, 'Особая атака летающего типа. Z-атака летающего типа.', 'Z-атака летающего типа.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'supersonicskystrike')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 715);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 716, 'Tearful Look', 'Normal', 3, 20, 0, 0, 1, 'Статусная атака обычного типа. Особый эффект поздних поколений: требует точной настройки в боевом движке. Понижает один или несколько параметров цели. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке. Понижает один или несколько параметров цели. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'tearfullook')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 716);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 717, 'Tectonic Rage', 'Ground', 0, 1, 0, 0, 1, 'Особая атака земляного типа. Z-атака земляного типа.', 'Z-атака земляного типа.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'tectonicrage')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 717);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 718, 'Throat Chop', 'Dark', 1, 15, 80, 100, 1, 'Физическая атака темного типа с силой 80. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'throatchop')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 718);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 719, 'Toxic Thread', 'Poison', 3, 20, 0, 100, 1, 'Статусная атака ядовитого типа. Может отравить цель.', 'Может отравить цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'toxicthread')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 719);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 720, 'Trop Kick', 'Grass', 1, 15, 70, 100, 1, 'Физическая атака травяного типа с силой 70. Понижает Атаку цели.', 'Понижает Атаку цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'tropkick')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 720);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 721, 'Twinkle Tackle', 'Fairy', 0, 1, 0, 0, 1, 'Особая атака волшебного типа. Z-атака волшебного типа.', 'Z-атака волшебного типа.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'twinkletackle')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 721);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 722, 'Veevee Volley', 'Normal', 1, 20, 0, 0, 1, 'Физическая атака обычного типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'veeveevolley')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 722);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 723, 'Zing Zap', 'Electric', 1, 10, 80, 100, 1, 'Физическая атака электрического типа с силой 80. Может испугать цель.', 'Может испугать цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'zingzap')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 723);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 724, 'Zippy Zap', 'Electric', 1, 15, 50, 100, 1, 'Физическая атака электрического типа с силой 50. Влияет на шанс критического удара.', 'Влияет на шанс критического удара.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 7. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'zippyzap')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 724);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 725, 'Apple Acid', 'Grass', 2, 10, 80, 100, 1, 'Специальная атака травяного типа с силой 80. Понижает один или несколько параметров цели.', 'Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'appleacid')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 725);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 726, 'Astral Barrage', 'Ghost', 2, 5, 120, 100, 1, 'Специальная атака призрачного типа с силой 120. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'astralbarrage')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 726);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 727, 'Aura Wheel', 'Electric', 1, 10, 110, 100, 1, 'Физическая атака электрического типа с силой 110. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'aurawheel')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 727);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 728, 'Barb Barrage', 'Poison', 1, 10, 60, 100, 1, 'Физическая атака ядовитого типа с силой 60. Может отравить цель.', 'Может отравить цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'barbbarrage')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 728);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 729, 'Behemoth Bash', 'Steel', 1, 5, 100, 100, 1, 'Физическая атака стального типа с силой 100. Наносит урон с дополнительным особым эффектом.', 'Наносит урон с дополнительным особым эффектом.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'behemothbash')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 729);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 730, 'Behemoth Blade', 'Steel', 1, 5, 100, 100, 1, 'Физическая атака стального типа с силой 100. Наносит урон с дополнительным особым эффектом.', 'Наносит урон с дополнительным особым эффектом.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'behemothblade')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 730);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 731, 'Bitter Malice', 'Ghost', 2, 10, 75, 100, 1, 'Специальная атака призрачного типа с силой 75. Понижает Атаку цели.', 'Понижает Атаку цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'bittermalice')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 731);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 732, 'Bleakwind Storm', 'Flying', 2, 10, 100, 80, 1, 'Специальная атака летающего типа с силой 100. Может понизить Скорость цели.', 'Может понизить Скорость цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'bleakwindstorm')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 732);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 733, 'Body Press', 'Fighting', 1, 10, 80, 100, 1, 'Физическая атака боевого типа с силой 80. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'bodypress')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 733);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 734, 'Bolt Beak', 'Electric', 1, 10, 85, 100, 1, 'Физическая атака электрического типа с силой 85. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'boltbeak')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 734);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 735, 'Branch Poke', 'Grass', 1, 40, 40, 100, 1, 'Физическая атака травяного типа с силой 40. Наносит обычный урон без дополнительного эффекта.', 'Наносит обычный урон без дополнительного эффекта.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'branchpoke')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 735);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 736, 'Breaking Swipe', 'Dragon', 1, 15, 60, 100, 1, 'Физическая атака драконьего типа с силой 60. Понижает один или несколько параметров цели.', 'Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'breakingswipe')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 736);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 737, 'Burning Jealousy', 'Fire', 2, 5, 70, 100, 1, 'Специальная атака огненного типа с силой 70. Может поджечь цель.', 'Может поджечь цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'burningjealousy')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 737);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 738, 'Ceaseless Edge', 'Dark', 1, 15, 65, 90, 1, 'Физическая атака темного типа с силой 65. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'ceaselessedge')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 738);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 739, 'Chloroblast', 'Grass', 2, 5, 150, 95, 1, 'Специальная атака травяного типа с силой 150. Пользователь получает урон отдачи.', 'Пользователь получает урон отдачи.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'chloroblast')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 739);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 740, 'Clangorous Soul', 'Dragon', 3, 5, 0, 100, 1, 'Статусная атака драконьего типа. Повышает один или несколько параметров пользователя.', 'Повышает один или несколько параметров пользователя.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'clangoroussoul')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 740);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 741, 'Coaching', 'Fighting', 3, 10, 0, 0, 1, 'Статусная атака боевого типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'coaching')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 741);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 742, 'Corrosive Gas', 'Poison', 3, 40, 0, 100, 1, 'Статусная атака ядовитого типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'corrosivegas')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 742);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 743, 'Court Change', 'Normal', 3, 10, 0, 100, 1, 'Статусная атака обычного типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'courtchange')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 743);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 744, 'Decorate', 'Fairy', 3, 15, 0, 0, 1, 'Статусная атака волшебного типа. Повышает один или несколько параметров пользователя.', 'Повышает один или несколько параметров пользователя.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'decorate')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 744);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 745, 'Dire Claw', 'Poison', 1, 15, 80, 100, 1, 'Физическая атака ядовитого типа с силой 80. Может взаимодействовать со сном.', 'Может взаимодействовать со сном.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'direclaw')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 745);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 746, 'Dragon Darts', 'Dragon', 1, 10, 50, 100, 1, 'Физическая атака драконьего типа с силой 50. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'dragondarts')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 746);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 747, 'Dragon Energy', 'Dragon', 2, 5, 150, 100, 1, 'Специальная атака драконьего типа с силой 150. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'dragonenergy')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 747);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 748, 'Drum Beating', 'Grass', 1, 10, 80, 100, 1, 'Физическая атака травяного типа с силой 80. Понижает Скорость цели.', 'Понижает Скорость цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'drumbeating')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 748);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 749, 'Dual Wingbeat', 'Flying', 1, 10, 40, 90, 1, 'Физическая атака летающего типа с силой 40. Особый эффект поздних поколений: требует точной настройки в боевом движке. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'dualwingbeat')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 749);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 750, 'Dynamax Cannon', 'Dragon', 2, 5, 100, 100, 1, 'Специальная атака драконьего типа с силой 100. Наносит урон с дополнительным особым эффектом.', 'Наносит урон с дополнительным особым эффектом.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'dynamaxcannon')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 750);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 751, 'Eerie Spell', 'Psychic', 2, 5, 80, 100, 1, 'Специальная атака психического типа с силой 80. Наносит урон с дополнительным особым эффектом.', 'Наносит урон с дополнительным особым эффектом.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'eeriespell')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 751);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 752, 'Esper Wing', 'Psychic', 2, 10, 80, 100, 1, 'Специальная атака психического типа с силой 80. Повышенный шанс критического удара. Повышает Скорость пользователя.', 'Повышенный шанс критического удара. Повышает Скорость пользователя.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'esperwing')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 752);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 753, 'Eternabeam', 'Dragon', 2, 5, 160, 90, 1, 'Специальная атака драконьего типа с силой 160. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'eternabeam')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 753);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 754, 'Expanding Force', 'Psychic', 2, 10, 80, 100, 1, 'Специальная атака психического типа с силой 80. Меняет поле боя и включает особый эффект местности.', 'Меняет поле боя и включает особый эффект местности.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'expandingforce')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 754);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 755, 'False Surrender', 'Dark', 1, 10, 80, 0, 1, 'Физическая атака темного типа с силой 80. Игнорирует точность и уклонение.', 'Игнорирует точность и уклонение.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'falsesurrender')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 755);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 756, 'Fiery Wrath', 'Dark', 2, 10, 90, 100, 1, 'Специальная атака темного типа с силой 90. Может испугать цель.', 'Может испугать цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'fierywrath')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 756);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 757, 'Fishious Rend', 'Water', 1, 10, 85, 100, 1, 'Физическая атака водного типа с силой 85. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'fishiousrend')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 757);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 758, 'Flip Turn', 'Water', 1, 20, 60, 100, 1, 'Физическая атака водного типа с силой 60. Влияет на смену покемона или возможность отступления.', 'Влияет на смену покемона или возможность отступления.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'flipturn')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 758);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 759, 'Freezing Glare', 'Psychic', 2, 10, 90, 100, 1, 'Специальная атака психического типа с силой 90. Может заморозить цель.', 'Может заморозить цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'freezingglare')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 759);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 760, 'G-Max Befuddle', 'Bug', 0, 5, 0, 0, 1, 'Особая атака насекомого типа. Эксклюзивная G-Max атака для Butterfree. Может взаимодействовать со сном.', 'Эксклюзивная G-Max атака для Butterfree. Может взаимодействовать со сном.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxbefuddle')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 760);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 761, 'G-Max Cannonade', 'Water', 0, 10, 0, 0, 1, 'Особая атака водного типа. Эксклюзивная G-Max атака для Blastoise. Наносит урон с дополнительным особым эффектом.', 'Эксклюзивная G-Max атака для Blastoise. Наносит урон с дополнительным особым эффектом.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxcannonade')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 761);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 762, 'G-Max Centiferno', 'Fire', 0, 5, 0, 0, 1, 'Особая атака огненного типа. Эксклюзивная G-Max атака для Centiskorch. Мешает смене и может наносить периодический урон.', 'Эксклюзивная G-Max атака для Centiskorch. Мешает смене и может наносить периодический урон.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxcentiferno')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 762);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 763, 'G-Max Chi Strike', 'Fighting', 0, 5, 0, 0, 1, 'Особая атака боевого типа. Эксклюзивная G-Max атака для Machamp. Влияет на шанс критического удара.', 'Эксклюзивная G-Max атака для Machamp. Влияет на шанс критического удара.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxchistrike')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 763);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 764, 'G-Max Cuddle', 'Normal', 0, 5, 0, 0, 1, 'Особая атака обычного типа. Эксклюзивная G-Max атака для Eevee. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Эксклюзивная G-Max атака для Eevee. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxcuddle')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 764);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 765, 'G-Max Depletion', 'Dragon', 0, 5, 0, 0, 1, 'Особая атака драконьего типа. Эксклюзивная G-Max атака для Duraludon. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Эксклюзивная G-Max атака для Duraludon. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxdepletion')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 765);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 766, 'G-Max Drum Solo', 'Grass', 0, 5, 160, 0, 1, 'Особая атака травяного типа с силой 160. Эксклюзивная G-Max атака для Rillaboom. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Эксклюзивная G-Max атака для Rillaboom. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxdrumsolo')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 766);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 767, 'G-Max Finale', 'Fairy', 0, 5, 0, 0, 1, 'Особая атака волшебного типа. Эксклюзивная G-Max атака для Alcremie. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Эксклюзивная G-Max атака для Alcremie. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxfinale')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 767);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 768, 'G-Max Fireball', 'Fire', 0, 5, 160, 0, 1, 'Особая атака огненного типа с силой 160. Эксклюзивная G-Max атака для Cinderace. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Эксклюзивная G-Max атака для Cinderace. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxfireball')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 768);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 769, 'G-Max Foam Burst', 'Water', 0, 5, 0, 0, 1, 'Особая атака водного типа. Эксклюзивная G-Max атака для Kingler. Понижает один или несколько параметров цели.', 'Эксклюзивная G-Max атака для Kingler. Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxfoamburst')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 769);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 770, 'G-Max Gold Rush', 'Normal', 0, 5, 0, 0, 1, 'Особая атака обычного типа. Эксклюзивная G-Max атака для Meowth. Может спутать цель.', 'Эксклюзивная G-Max атака для Meowth. Может спутать цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxgoldrush')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 770);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 771, 'G-Max Gravitas', 'Psychic', 0, 5, 0, 0, 1, 'Особая атака психического типа. Эксклюзивная G-Max атака для Orbeetle. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Эксклюзивная G-Max атака для Orbeetle. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxgravitas')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 771);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 772, 'G-Max Hydrosnipe', 'Water', 0, 5, 160, 0, 1, 'Особая атака водного типа с силой 160. Эксклюзивная G-Max атака для Inteleon. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Эксклюзивная G-Max атака для Inteleon. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxhydrosnipe')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 772);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 773, 'G-Max Malodor', 'Poison', 0, 5, 0, 0, 1, 'Особая атака ядовитого типа. Эксклюзивная G-Max атака для Garbodor. Может отравить цель.', 'Эксклюзивная G-Max атака для Garbodor. Может отравить цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxmalodor')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 773);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 774, 'G-Max Meltdown', 'Steel', 0, 5, 0, 0, 1, 'Особая атака стального типа. Эксклюзивная G-Max атака для Melmetal. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Эксклюзивная G-Max атака для Melmetal. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxmeltdown')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 774);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 775, 'G-Max One Blow', 'Dark', 0, 5, 0, 0, 1, 'Особая атака темного типа. Эксклюзивная G-Max атака для Urshifu Single-Strike Style. Дает защитный эффект на этот ход.', 'Эксклюзивная G-Max атака для Urshifu Single-Strike Style. Дает защитный эффект на этот ход.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxoneblow')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 775);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 776, 'G-Max Rapid Flow', 'Water', 0, 5, 0, 0, 1, 'Особая атака водного типа. Эксклюзивная G-Max атака для Urshifu Rapid-Strike Style. Дает защитный эффект на этот ход.', 'Эксклюзивная G-Max атака для Urshifu Rapid-Strike Style. Дает защитный эффект на этот ход.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxrapidflow')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 776);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 777, 'G-Max Replenish', 'Normal', 0, 5, 0, 0, 1, 'Особая атака обычного типа. Эксклюзивная G-Max атака для Snorlax. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Эксклюзивная G-Max атака для Snorlax. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxreplenish')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 777);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 778, 'G-Max Resonance', 'Ice', 0, 5, 0, 0, 1, 'Особая атака ледяного типа. Эксклюзивная G-Max атака для Lapras. Наносит урон с дополнительным особым эффектом.', 'Эксклюзивная G-Max атака для Lapras. Наносит урон с дополнительным особым эффектом.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxresonance')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 778);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 779, 'G-Max Sandblast', 'Ground', 0, 5, 0, 0, 1, 'Особая атака земляного типа. Эксклюзивная G-Max атака для Sandaconda. Мешает смене и может наносить периодический урон.', 'Эксклюзивная G-Max атака для Sandaconda. Мешает смене и может наносить периодический урон.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxsandblast')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 779);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 780, 'G-Max Smite', 'Fairy', 0, 5, 0, 0, 1, 'Особая атака волшебного типа. Эксклюзивная G-Max атака для Hatterene. Может спутать цель.', 'Эксклюзивная G-Max атака для Hatterene. Может спутать цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxsmite')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 780);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 781, 'G-Max Snooze', 'Dark', 0, 5, 0, 0, 1, 'Особая атака темного типа. Эксклюзивная G-Max атака для Grimmsnarl. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Эксклюзивная G-Max атака для Grimmsnarl. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxsnooze')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 781);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 782, 'G-Max Steelsurge', 'Steel', 0, 5, 0, 0, 1, 'Особая атака стального типа. Эксклюзивная G-Max атака для Copperajah. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Эксклюзивная G-Max атака для Copperajah. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxsteelsurge')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 782);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 783, 'G-Max Stonesurge', 'Water', 0, 5, 0, 0, 1, 'Особая атака водного типа. Эксклюзивная G-Max атака для Drednaw. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Эксклюзивная G-Max атака для Drednaw. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxstonesurge')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 783);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 784, 'G-Max Stun Shock', 'Electric', 0, 10, 0, 0, 1, 'Особая атака электрического типа. Эксклюзивная G-Max атака для Toxtricity. Может отравить цель.', 'Эксклюзивная G-Max атака для Toxtricity. Может отравить цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxstunshock')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 784);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 785, 'G-Max Sweetness', 'Grass', 0, 10, 0, 0, 1, 'Особая атака травяного типа. Эксклюзивная G-Max атака для Appletun. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Эксклюзивная G-Max атака для Appletun. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxsweetness')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 785);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 786, 'G-Max Tartness', 'Grass', 0, 10, 0, 0, 1, 'Особая атака травяного типа. Эксклюзивная G-Max атака для Flapple. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Эксклюзивная G-Max атака для Flapple. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxtartness')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 786);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 787, 'G-Max Terror', 'Ghost', 0, 10, 0, 0, 1, 'Особая атака призрачного типа. Эксклюзивная G-Max атака для Gengar. Влияет на смену покемона или возможность отступления.', 'Эксклюзивная G-Max атака для Gengar. Влияет на смену покемона или возможность отступления.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxterror')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 787);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 788, 'G-Max Vine Lash', 'Grass', 0, 10, 0, 0, 1, 'Особая атака травяного типа. Эксклюзивная G-Max атака для Venusaur. Наносит урон с дополнительным особым эффектом.', 'Эксклюзивная G-Max атака для Venusaur. Наносит урон с дополнительным особым эффектом.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxvinelash')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 788);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 789, 'G-Max Volcalith', 'Rock', 0, 10, 0, 0, 1, 'Особая атака каменного типа. Эксклюзивная G-Max атака для Coalossal. Наносит урон с дополнительным особым эффектом.', 'Эксклюзивная G-Max атака для Coalossal. Наносит урон с дополнительным особым эффектом.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxvolcalith')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 789);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 790, 'G-Max Volt Crash', 'Electric', 0, 10, 0, 0, 1, 'Особая атака электрического типа. Эксклюзивная G-Max атака для Pikachu. Может парализовать цель.', 'Эксклюзивная G-Max атака для Pikachu. Может парализовать цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxvoltcrash')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 790);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 791, 'G-Max Wildfire', 'Fire', 0, 10, 0, 0, 1, 'Особая атака огненного типа. Эксклюзивная G-Max атака для Charizard. Наносит урон с дополнительным особым эффектом.', 'Эксклюзивная G-Max атака для Charizard. Наносит урон с дополнительным особым эффектом.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxwildfire')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 791);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 792, 'G-Max Wind Rage', 'Flying', 0, 10, 0, 0, 1, 'Особая атака летающего типа. Эксклюзивная G-Max атака для Corviknight. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Эксклюзивная G-Max атака для Corviknight. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gmaxwindrage')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 792);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 793, 'Glacial Lance', 'Ice', 1, 5, 120, 100, 1, 'Физическая атака ледяного типа с силой 120. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'glaciallance')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 793);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 794, 'Grassy Glide', 'Grass', 1, 20, 55, 100, 1, 'Физическая атака травяного типа с силой 55. Меняет поле боя и включает особый эффект местности.', 'Меняет поле боя и включает особый эффект местности.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'grassyglide')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 794);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 795, 'Grav Apple', 'Grass', 1, 10, 80, 100, 1, 'Физическая атака травяного типа с силой 80. Понижает один или несколько параметров цели.', 'Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gravapple')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 795);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 796, 'Headlong Rush', 'Ground', 1, 5, 120, 100, 1, 'Физическая атака земляного типа с силой 120. Понижает один или несколько параметров цели.', 'Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'headlongrush')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 796);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 797, 'Infernal Parade', 'Ghost', 2, 15, 60, 100, 1, 'Специальная атака призрачного типа с силой 60. Наносит урон с дополнительным особым эффектом.', 'Наносит урон с дополнительным особым эффектом.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'infernalparade')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 797);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 798, 'Jaw Lock', 'Dark', 1, 10, 80, 100, 1, 'Физическая атака темного типа с силой 80. Влияет на смену покемона или возможность отступления.', 'Влияет на смену покемона или возможность отступления.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'jawlock')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 798);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 799, 'Jungle Healing', 'Grass', 3, 10, 0, 0, 1, 'Статусная атака травяного типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'junglehealing')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 799);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 800, 'Lash Out', 'Dark', 1, 5, 75, 100, 1, 'Физическая атака темного типа с силой 75. Понижает один или несколько параметров цели.', 'Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'lashout')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 800);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 801, 'Life Dew', 'Water', 3, 10, 0, 0, 1, 'Статусная атака водного типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'lifedew')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 801);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 802, 'Lunar Blessing', 'Psychic', 3, 5, 0, 0, 1, 'Статусная атака психического типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'lunarblessing')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 802);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 803, 'Magic Powder', 'Psychic', 3, 20, 0, 100, 1, 'Статусная атака психического типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'magicpowder')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 803);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 804, 'Max Airstream', 'Flying', 0, 0, 0, 0, 1, 'Особая атака летающего типа. Dynamax-атака летающего типа. Повышает один или несколько параметров пользователя.', 'Dynamax-атака летающего типа. Повышает один или несколько параметров пользователя.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'maxairstream')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 804);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 805, 'Max Darkness', 'Dark', 0, 0, 0, 0, 1, 'Особая атака темного типа. Dynamax-атака темного типа. Понижает один или несколько параметров цели.', 'Dynamax-атака темного типа. Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'maxdarkness')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 805);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 806, 'Max Flare', 'Fire', 0, 0, 0, 0, 1, 'Особая атака огненного типа. Dynamax-атака огненного типа. Влияет на погоду и связанные с ней бонусы.', 'Dynamax-атака огненного типа. Влияет на погоду и связанные с ней бонусы.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'maxflare')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 806);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 807, 'Max Flutterby', 'Bug', 0, 0, 0, 0, 1, 'Особая атака насекомого типа. Dynamax-атака насекомого типа. Понижает один или несколько параметров цели.', 'Dynamax-атака насекомого типа. Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'maxflutterby')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 807);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 808, 'Max Geyser', 'Water', 0, 0, 0, 0, 1, 'Особая атака водного типа. Dynamax-атака водного типа. Влияет на погоду и связанные с ней бонусы.', 'Dynamax-атака водного типа. Влияет на погоду и связанные с ней бонусы.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'maxgeyser')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 808);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 809, 'Max Guard', 'Normal', 0, 0, 0, 0, 1, 'Особая атака обычного типа. Особый эффект поздних поколений: требует точной настройки в боевом движке. Защищает пользователя.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке. Защищает пользователя.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'maxguard')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 809);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 810, 'Max Hailstorm', 'Ice', 0, 0, 0, 0, 1, 'Особая атака ледяного типа. Dynamax-атака ледяного типа. Влияет на погоду и связанные с ней бонусы.', 'Dynamax-атака ледяного типа. Влияет на погоду и связанные с ней бонусы.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'maxhailstorm')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 810);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 811, 'Max Knuckle', 'Fighting', 0, 0, 0, 0, 1, 'Особая атака боевого типа. Dynamax-атака боевого типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Dynamax-атака боевого типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'maxknuckle')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 811);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 812, 'Max Lightning', 'Electric', 0, 0, 0, 0, 1, 'Особая атака электрического типа. Dynamax-атака электрического типа. Меняет поле боя и включает особый эффект местности.', 'Dynamax-атака электрического типа. Меняет поле боя и включает особый эффект местности.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'maxlightning')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 812);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 813, 'Max Mindstorm', 'Psychic', 0, 0, 0, 0, 1, 'Особая атака психического типа. Dynamax-атака психического типа. Меняет поле боя и включает особый эффект местности.', 'Dynamax-атака психического типа. Меняет поле боя и включает особый эффект местности.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'maxmindstorm')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 813);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 814, 'Max Ooze', 'Poison', 0, 0, 0, 0, 1, 'Особая атака ядовитого типа. Dynamax-атака ядовитого типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Dynamax-атака ядовитого типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'maxooze')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 814);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 815, 'Max Overgrowth', 'Grass', 0, 0, 0, 0, 1, 'Особая атака травяного типа. Dynamax-атака травяного типа. Меняет поле боя и включает особый эффект местности.', 'Dynamax-атака травяного типа. Меняет поле боя и включает особый эффект местности.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'maxovergrowth')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 815);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 816, 'Max Phantasm', 'Ghost', 0, 0, 0, 0, 1, 'Особая атака призрачного типа. Dynamax-атака призрачного типа. Понижает один или несколько параметров цели.', 'Dynamax-атака призрачного типа. Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'maxphantasm')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 816);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 817, 'Max Quake', 'Ground', 0, 0, 0, 0, 1, 'Особая атака земляного типа. Dynamax-атака земляного типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Dynamax-атака земляного типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'maxquake')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 817);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 818, 'Max Rockfall', 'Rock', 0, 0, 0, 0, 1, 'Особая атака каменного типа. Dynamax-атака каменного типа. Влияет на погоду и связанные с ней бонусы.', 'Dynamax-атака каменного типа. Влияет на погоду и связанные с ней бонусы.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'maxrockfall')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 818);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 819, 'Max Starfall', 'Fairy', 0, 0, 0, 0, 1, 'Особая атака волшебного типа. Dynamax-атака волшебного типа. Меняет поле боя и включает особый эффект местности.', 'Dynamax-атака волшебного типа. Меняет поле боя и включает особый эффект местности.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'maxstarfall')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 819);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 820, 'Max Steelspike', 'Steel', 0, 0, 0, 0, 1, 'Особая атака стального типа. Dynamax-атака стального типа. Повышает один или несколько параметров пользователя.', 'Dynamax-атака стального типа. Повышает один или несколько параметров пользователя.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'maxsteelspike')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 820);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 821, 'Max Strike', 'Normal', 0, 0, 0, 0, 1, 'Особая атака обычного типа. Dynamax-атака обычного типа. Понижает один или несколько параметров цели.', 'Dynamax-атака обычного типа. Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'maxstrike')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 821);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 822, 'Max Wyrmwind', 'Dragon', 0, 0, 0, 0, 1, 'Особая атака драконьего типа. Dynamax-атака драконьего типа. Понижает один или несколько параметров цели.', 'Dynamax-атака драконьего типа. Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'maxwyrmwind')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 822);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 823, 'Meteor Assault', 'Fighting', 1, 5, 150, 100, 1, 'Физическая атака боевого типа с силой 150. Пользователь пропускает следующий ход для перезарядки.', 'Пользователь пропускает следующий ход для перезарядки.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'meteorassault')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 823);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 824, 'Meteor Beam', 'Rock', 2, 10, 120, 90, 1, 'Специальная атака каменного типа с силой 120. Особый эффект поздних поколений: требует точной настройки в боевом движке. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'meteorbeam')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 824);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 825, 'Misty Explosion', 'Fairy', 2, 5, 100, 100, 1, 'Специальная атака волшебного типа с силой 100. Меняет поле боя и включает особый эффект местности.', 'Меняет поле боя и включает особый эффект местности.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'mistyexplosion')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 825);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 826, 'Mountain Gale', 'Ice', 1, 10, 100, 85, 1, 'Физическая атака ледяного типа с силой 100. Понижает один или несколько параметров цели.', 'Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'mountaingale')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 826);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 827, 'Mystical Power', 'Psychic', 2, 10, 70, 90, 1, 'Специальная атака психического типа с силой 70. Повышает Спец. атаку пользователя.', 'Повышает Спец. атаку пользователя.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'mysticalpower')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 827);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 828, 'No Retreat', 'Fighting', 3, 5, 0, 0, 1, 'Статусная атака боевого типа. Влияет на смену покемона или возможность отступления.', 'Влияет на смену покемона или возможность отступления.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'noretreat')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 828);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 829, 'Obstruct', 'Dark', 3, 10, 0, 100, 1, 'Статусная атака темного типа. Дает защитный эффект на этот ход.', 'Дает защитный эффект на этот ход.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'obstruct')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 829);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 830, 'Octolock', 'Fighting', 3, 15, 0, 100, 1, 'Статусная атака боевого типа. Влияет на смену покемона или возможность отступления.', 'Влияет на смену покемона или возможность отступления.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'octolock')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 830);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 831, 'Overdrive', 'Electric', 2, 10, 80, 100, 1, 'Специальная атака электрического типа с силой 80. Бьет всех соседних противников.', 'Бьет всех соседних противников.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'overdrive')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 831);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 832, 'Poltergeist', 'Ghost', 1, 5, 110, 90, 1, 'Физическая атака призрачного типа с силой 110. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'poltergeist')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 832);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 833, 'Power Shift', 'Normal', 3, 10, 0, 0, 1, 'Статусная атака обычного типа. Влияет на смену покемона или возможность отступления.', 'Влияет на смену покемона или возможность отступления.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'powershift')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 833);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 834, 'Psyshield Bash', 'Psychic', 1, 10, 70, 90, 1, 'Физическая атака психического типа с силой 70. Повышает один или несколько параметров пользователя.', 'Повышает один или несколько параметров пользователя.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'psyshieldbash')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 834);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 835, 'Pyro Ball', 'Fire', 1, 5, 120, 90, 1, 'Физическая атака огненного типа с силой 120. Может поджечь цель.', 'Может поджечь цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'pyroball')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 835);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 836, 'Raging Fury', 'Fire', 1, 10, 120, 100, 1, 'Физическая атака огненного типа с силой 120. Может спутать цель.', 'Может спутать цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'ragingfury')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 836);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 837, 'Rising Voltage', 'Electric', 2, 20, 70, 100, 1, 'Специальная атака электрического типа с силой 70. Меняет поле боя и включает особый эффект местности.', 'Меняет поле боя и включает особый эффект местности.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'risingvoltage')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 837);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 838, 'Sandsear Storm', 'Ground', 2, 10, 100, 80, 1, 'Специальная атака земляного типа с силой 100. Может поджечь цель.', 'Может поджечь цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'sandsearstorm')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 838);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 839, 'Scale Shot', 'Dragon', 1, 20, 25, 90, 1, 'Физическая атака драконьего типа с силой 25. Бьет 2-5 раз за один ход. Понижает один или несколько параметров цели.', 'Бьет 2-5 раз за один ход. Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'scaleshot')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 839);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 840, 'Scorching Sands', 'Ground', 2, 10, 70, 100, 1, 'Специальная атака земляного типа с силой 70. Может поджечь цель.', 'Может поджечь цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'scorchingsands')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 840);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 841, 'Shell Side Arm', 'Poison', 2, 10, 90, 100, 1, 'Специальная атака ядовитого типа с силой 90. Может отравить цель. Наносит урон с дополнительным особым эффектом.', 'Может отравить цель. Наносит урон с дополнительным особым эффектом.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'shellsidearm')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 841);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 842, 'Shelter', 'Steel', 3, 10, 0, 0, 1, 'Статусная атака стального типа. Повышает Защиту пользователя.', 'Повышает Защиту пользователя.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'shelter')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 842);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 843, 'Skitter Smack', 'Bug', 1, 10, 70, 90, 1, 'Физическая атака насекомого типа с силой 70. Понижает один или несколько параметров цели. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Понижает один или несколько параметров цели. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'skittersmack')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 843);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 844, 'Snap Trap', 'Grass', 1, 15, 35, 100, 1, 'Физическая атака травяного типа с силой 35. Удерживает цель и наносит ей урон 4-5 ходов.', 'Удерживает цель и наносит ей урон 4-5 ходов.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'snaptrap')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 844);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 845, 'Snipe Shot', 'Water', 2, 15, 80, 100, 1, 'Специальная атака водного типа с силой 80. Особый эффект поздних поколений: требует точной настройки в боевом движке. Влияет на шанс критического удара.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке. Влияет на шанс критического удара.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'snipeshot')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 845);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 846, 'Spirit Break', 'Fairy', 1, 15, 75, 100, 1, 'Физическая атака волшебного типа с силой 75. Понижает Спец. атаку цели.', 'Понижает Спец. атаку цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'spiritbreak')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 846);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 847, 'Springtide Storm', 'Fairy', 2, 5, 100, 80, 1, 'Специальная атака волшебного типа с силой 100. Понижает один или несколько параметров цели.', 'Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'springtidestorm')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 847);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 848, 'Steel Beam', 'Steel', 2, 5, 140, 95, 1, 'Специальная атака стального типа с силой 140. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'steelbeam')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 848);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 849, 'Steel Roller', 'Steel', 1, 5, 130, 100, 1, 'Физическая атака стального типа с силой 130. Меняет поле боя и включает особый эффект местности.', 'Меняет поле боя и включает особый эффект местности.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'steelroller')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 849);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 850, 'Stone Axe', 'Rock', 1, 15, 65, 90, 1, 'Физическая атака каменного типа с силой 65. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'stoneaxe')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 850);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 851, 'Strange Steam', 'Fairy', 2, 10, 90, 95, 1, 'Специальная атака волшебного типа с силой 90. Может спутать цель.', 'Может спутать цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'strangesteam')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 851);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 852, 'Stuff Cheeks', 'Normal', 3, 10, 0, 0, 1, 'Статусная атака обычного типа. Повышает один или несколько параметров пользователя.', 'Повышает один или несколько параметров пользователя.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'stuffcheeks')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 852);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 853, 'Surging Strikes', 'Water', 1, 5, 25, 100, 1, 'Физическая атака водного типа с силой 25. Влияет на шанс критического удара.', 'Влияет на шанс критического удара.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'surgingstrikes')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 853);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 854, 'Take Heart', 'Psychic', 3, 10, 0, 0, 1, 'Статусная атака психического типа. Повышает один или несколько параметров пользователя.', 'Повышает один или несколько параметров пользователя.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'takeheart')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 854);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 855, 'Tar Shot', 'Rock', 3, 15, 0, 100, 1, 'Статусная атака каменного типа. Понижает один или несколько параметров цели.', 'Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'tarshot')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 855);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 856, 'Teatime', 'Normal', 3, 10, 0, 0, 1, 'Статусная атака обычного типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'teatime')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 856);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 857, 'Terrain Pulse', 'Normal', 2, 10, 50, 100, 1, 'Специальная атака обычного типа с силой 50. Меняет поле боя и включает особый эффект местности.', 'Меняет поле боя и включает особый эффект местности.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'terrainpulse')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 857);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 858, 'Thunder Cage', 'Electric', 2, 15, 80, 90, 1, 'Специальная атака электрического типа с силой 80. Мешает смене и может наносить периодический урон.', 'Мешает смене и может наносить периодический урон.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'thundercage')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 858);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 859, 'Thunderous Kick', 'Fighting', 1, 10, 90, 100, 1, 'Физическая атака боевого типа с силой 90. Понижает Защиту цели.', 'Понижает Защиту цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'thunderouskick')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 859);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 860, 'Triple Arrows', 'Fighting', 1, 10, 90, 100, 1, 'Физическая атака боевого типа с силой 90. Повышенный шанс критического удара. Может испугать цель.', 'Повышенный шанс критического удара. Может испугать цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'triplearrows')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 860);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 861, 'Triple Axel', 'Ice', 1, 10, 20, 90, 1, 'Физическая атака ледяного типа с силой 20. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'tripleaxel')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 861);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 862, 'Victory Dance', 'Fighting', 3, 10, 0, 0, 1, 'Статусная атака боевого типа. Повышает один или несколько параметров пользователя.', 'Повышает один или несколько параметров пользователя.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'victorydance')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 862);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 863, 'Wave Crash', 'Water', 1, 10, 120, 100, 1, 'Физическая атака водного типа с силой 120. Пользователь получает урон отдачи.', 'Пользователь получает урон отдачи.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'wavecrash')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 863);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 864, 'Wicked Blow', 'Dark', 1, 5, 75, 100, 1, 'Физическая атака темного типа с силой 75. Влияет на шанс критического удара.', 'Влияет на шанс критического удара.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'wickedblow')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 864);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 865, 'Wildbolt Storm', 'Electric', 2, 10, 100, 80, 1, 'Специальная атака электрического типа с силой 100. Может парализовать цель.', 'Может парализовать цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 8. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'wildboltstorm')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 865);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 866, 'Alluring Voice', 'Fairy', 2, 10, 80, 100, 1, 'Специальная атака волшебного типа с силой 80. Наносит обычный урон без дополнительного эффекта.', 'Наносит обычный урон без дополнительного эффекта.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'alluringvoice')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 866);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 867, 'Aqua Cutter', 'Water', 1, 20, 70, 100, 1, 'Физическая атака водного типа с силой 70. Влияет на шанс критического удара.', 'Влияет на шанс критического удара.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'aquacutter')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 867);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 868, 'Aqua Step', 'Water', 1, 10, 80, 100, 1, 'Физическая атака водного типа с силой 80. Повышает Скорость пользователя.', 'Повышает Скорость пользователя.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'aquastep')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 868);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 869, 'Armor Cannon', 'Fire', 2, 5, 120, 100, 1, 'Специальная атака огненного типа с силой 120. Понижает один или несколько параметров цели.', 'Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'armorcannon')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 869);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 870, 'Axe Kick', 'Fighting', 1, 10, 120, 90, 1, 'Физическая атака боевого типа с силой 120. Может спутать цель. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Может спутать цель. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'axekick')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 870);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 871, 'Bitter Blade', 'Fire', 1, 10, 90, 100, 1, 'Физическая атака огненного типа с силой 90. Пользователь восстанавливает половину нанесенного урона в HP.', 'Пользователь восстанавливает половину нанесенного урона в HP.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'bitterblade')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 871);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 872, 'Blazing Torque', 'Fire', 1, 10, 80, 100, 1, 'Физическая атака огненного типа с силой 80. Может поджечь цель.', 'Может поджечь цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'blazingtorque')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 872);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 873, 'Blood Moon', 'Normal', 2, 5, 140, 100, 1, 'Специальная атака обычного типа с силой 140. Нельзя использовать два раза подряд.', 'Нельзя использовать два раза подряд.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'bloodmoon')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 873);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 874, 'Burning Bulwark', 'Fire', 3, 10, 0, 0, 1, 'Статусная атака огненного типа. Статусная атака без отдельного описания эффекта в исходной таблице.', 'Статусная атака без отдельного описания эффекта в исходной таблице.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'burningbulwark')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 874);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 875, 'Chilling Water', 'Water', 2, 20, 50, 100, 1, 'Специальная атака водного типа с силой 50. Понижает Атаку цели.', 'Понижает Атаку цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'chillingwater')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 875);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 876, 'Chilly Reception', 'Ice', 3, 10, 0, 0, 1, 'Статусная атака ледяного типа. Влияет на погоду и связанные с ней бонусы.', 'Влияет на погоду и связанные с ней бонусы.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'chillyreception')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 876);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 877, 'Collision Course', 'Fighting', 1, 5, 100, 100, 1, 'Физическая атака боевого типа с силой 100. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'collisioncourse')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 877);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 878, 'Combat Torque', 'Fighting', 1, 10, 100, 100, 1, 'Физическая атака боевого типа с силой 100. Может парализовать цель.', 'Может парализовать цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'combattorque')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 878);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 879, 'Comeuppance', 'Dark', 1, 10, 0, 100, 1, 'Физическая атака темного типа. Наносит урон с дополнительным особым эффектом.', 'Наносит урон с дополнительным особым эффектом.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'comeuppance')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 879);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 880, 'Doodle', 'Normal', 3, 10, 0, 100, 1, 'Статусная атака обычного типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'doodle')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 880);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 881, 'Double Shock', 'Electric', 1, 5, 120, 100, 1, 'Физическая атака электрического типа с силой 120. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'doubleshock')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 881);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 882, 'Dragon Cheer', 'Dragon', 3, 15, 0, 0, 1, 'Статусная атака драконьего типа. Статусная атака без отдельного описания эффекта в исходной таблице.', 'Статусная атака без отдельного описания эффекта в исходной таблице.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'dragoncheer')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 882);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 883, 'Electro Drift', 'Electric', 2, 5, 100, 100, 1, 'Специальная атака электрического типа с силой 100. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'electrodrift')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 883);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 884, 'Electro Shot', 'Electric', 2, 10, 130, 100, 1, 'Специальная атака электрического типа с силой 130. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'electroshot')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 884);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 885, 'Fickle Beam', 'Dragon', 2, 5, 80, 100, 1, 'Специальная атака драконьего типа с силой 80. Наносит урон с дополнительным особым эффектом.', 'Наносит урон с дополнительным особым эффектом.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'ficklebeam')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 885);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 886, 'Fillet Away', 'Normal', 3, 10, 0, 0, 1, 'Статусная атака обычного типа. Понижает один или несколько параметров цели.', 'Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'filletaway')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 886);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 887, 'Flower Trick', 'Grass', 1, 10, 70, 0, 1, 'Физическая атака травяного типа с силой 70. Влияет на шанс критического удара.', 'Влияет на шанс критического удара.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'flowertrick')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 887);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 888, 'Gigaton Hammer', 'Steel', 1, 5, 160, 100, 1, 'Физическая атака стального типа с силой 160. Нельзя использовать два раза подряд.', 'Нельзя использовать два раза подряд.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'gigatonhammer')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 888);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 889, 'Glaive Rush', 'Dragon', 1, 5, 120, 100, 1, 'Физическая атака драконьего типа с силой 120. Наносит урон с дополнительным особым эффектом.', 'Наносит урон с дополнительным особым эффектом.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'glaiverush')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 889);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 890, 'Hard Press', 'Steel', 1, 10, 0, 100, 1, 'Физическая атака стального типа. Наносит обычный урон без дополнительного эффекта.', 'Наносит обычный урон без дополнительного эффекта.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'hardpress')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 890);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 891, 'Hydro Steam', 'Water', 2, 15, 80, 100, 1, 'Специальная атака водного типа с силой 80. Влияет на погоду и связанные с ней бонусы.', 'Влияет на погоду и связанные с ней бонусы.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'hydrosteam')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 891);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 892, 'Hyper Drill', 'Normal', 1, 5, 100, 100, 1, 'Физическая атака обычного типа с силой 100. Дает защитный эффект на этот ход.', 'Дает защитный эффект на этот ход.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'hyperdrill')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 892);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 893, 'Ice Spinner', 'Ice', 1, 15, 80, 100, 1, 'Физическая атака ледяного типа с силой 80. Меняет поле боя и включает особый эффект местности.', 'Меняет поле боя и включает особый эффект местности.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'icespinner')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 893);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 894, 'Ivy Cudgel', 'Grass', 1, 0, 100, 0, 1, 'Физическая атака травяного типа с силой 100. Повышенный шанс критического удара. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Повышенный шанс критического удара. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'ivycudgel')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 894);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 895, 'Jet Punch', 'Water', 1, 15, 60, 100, 1, 'Физическая атака водного типа с силой 60. Почти всегда ходит первой.', 'Почти всегда ходит первой.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'jetpunch')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 895);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 896, 'Kowtow Cleave', 'Dark', 1, 10, 85, 0, 1, 'Физическая атака темного типа с силой 85. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'kowtowcleave')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 896);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 897, 'Last Respects', 'Ghost', 1, 10, 50, 100, 1, 'Физическая атака призрачного типа с силой 50. Наносит урон с дополнительным особым эффектом.', 'Наносит урон с дополнительным особым эффектом.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'lastrespects')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 897);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 898, 'Lumina Crash', 'Psychic', 2, 10, 80, 100, 1, 'Специальная атака психического типа с силой 80. Понижает один или несколько параметров цели.', 'Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'luminacrash')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 898);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 899, 'Magical Torque', 'Fairy', 1, 10, 100, 100, 1, 'Физическая атака волшебного типа с силой 100. Может спутать цель.', 'Может спутать цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'magicaltorque')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 899);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 900, 'Make It Rain', 'Steel', 2, 5, 120, 100, 1, 'Специальная атака стального типа с силой 120. Понижает один или несколько параметров цели. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Понижает один или несколько параметров цели. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'makeitrain')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 900);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 901, 'Malignant Chain', 'Poison', 2, 5, 100, 100, 1, 'Специальная атака ядовитого типа с силой 100. Может отравить цель.', 'Может отравить цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'malignantchain')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 901);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 902, 'Matcha Gotcha', 'Grass', 2, 15, 80, 90, 1, 'Специальная атака травяного типа с силой 80. Может поджечь цель.', 'Может поджечь цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'matchagotcha')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 902);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 903, 'Mighty Cleave', 'Rock', 1, 5, 95, 100, 1, 'Физическая атака каменного типа с силой 95. Наносит обычный урон без дополнительного эффекта.', 'Наносит обычный урон без дополнительного эффекта.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'mightycleave')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 903);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 904, 'Mortal Spin', 'Poison', 1, 15, 30, 100, 1, 'Физическая атака ядовитого типа с силой 30. Мешает смене и может наносить периодический урон.', 'Мешает смене и может наносить периодический урон.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'mortalspin')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 904);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 905, 'Noxious Torque', 'Poison', 1, 10, 100, 100, 1, 'Физическая атака ядовитого типа с силой 100. Может отравить цель.', 'Может отравить цель.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'noxioustorque')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 905);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 906, 'Order Up', 'Dragon', 1, 10, 80, 100, 1, 'Физическая атака драконьего типа с силой 80. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'orderup')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 906);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 907, 'Population Bomb', 'Normal', 1, 10, 20, 90, 1, 'Физическая атака обычного типа с силой 20. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'populationbomb')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 907);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 908, 'Pounce', 'Bug', 1, 20, 50, 100, 1, 'Физическая атака насекомого типа с силой 50. Понижает Скорость цели.', 'Понижает Скорость цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'pounce')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 908);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 909, 'Psyblade', 'Psychic', 1, 15, 80, 100, 1, 'Физическая атака психического типа с силой 80. Меняет поле боя и включает особый эффект местности.', 'Меняет поле боя и включает особый эффект местности.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'psyblade')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 909);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 910, 'Psychic Noise', 'Psychic', 2, 10, 75, 100, 1, 'Специальная атака психического типа с силой 75. Наносит урон с дополнительным особым эффектом.', 'Наносит урон с дополнительным особым эффектом.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'psychicnoise')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 910);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 911, 'Rage Fist', 'Ghost', 1, 10, 50, 100, 1, 'Физическая атака призрачного типа с силой 50. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'ragefist')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 911);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 912, 'Raging Bull', 'Normal', 1, 10, 90, 100, 1, 'Физическая атака обычного типа с силой 90. Особый эффект поздних поколений: требует точной настройки в боевом движке. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'ragingbull')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 912);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 913, 'Revival Blessing', 'Normal', 3, 1, 0, 0, 1, 'Статусная атака обычного типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'revivalblessing')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 913);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 914, 'Ruination', 'Dark', 2, 10, 0, 90, 1, 'Специальная атака темного типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'ruination')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 914);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 915, 'Salt Cure', 'Rock', 1, 15, 40, 100, 1, 'Физическая атака каменного типа с силой 40. Наносит урон с дополнительным особым эффектом.', 'Наносит урон с дополнительным особым эффектом.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'saltcure')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 915);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 916, 'Shed Tail', 'Normal', 3, 10, 0, 0, 1, 'Статусная атака обычного типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'shedtail')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 916);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 917, 'Silk Trap', 'Bug', 3, 10, 0, 0, 1, 'Статусная атака насекомого типа. Дает защитный эффект на этот ход.', 'Дает защитный эффект на этот ход.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'silktrap')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 917);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 918, 'Snowscape', 'Ice', 3, 10, 0, 0, 1, 'Статусная атака ледяного типа. Повышает один или несколько параметров пользователя.', 'Повышает один или несколько параметров пользователя.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'snowscape')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 918);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 919, 'Spicy Extract', 'Grass', 3, 15, 0, 0, 1, 'Статусная атака травяного типа. Понижает один или несколько параметров цели.', 'Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'spicyextract')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 919);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 920, 'Spin Out', 'Steel', 1, 5, 100, 100, 1, 'Физическая атака стального типа с силой 100. Понижает один или несколько параметров цели.', 'Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'spinout')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 920);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 921, 'Supercell Slam', 'Electric', 1, 15, 100, 95, 1, 'Физическая атака электрического типа с силой 100. Наносит обычный урон без дополнительного эффекта.', 'Наносит обычный урон без дополнительного эффекта.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'supercellslam')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 921);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 922, 'Syrup Bomb', 'Grass', 2, 10, 60, 85, 1, 'Специальная атака травяного типа с силой 60. Понижает один или несколько параметров цели.', 'Понижает один или несколько параметров цели.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'syrupbomb')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 922);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 923, 'Tachyon Cutter', 'Steel', 2, 10, 50, 0, 1, 'Специальная атака стального типа с силой 50. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'tachyoncutter')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 923);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 924, 'Temper Flare', 'Fire', 1, 10, 75, 100, 1, 'Физическая атака огненного типа с силой 75. Наносит обычный урон без дополнительного эффекта.', 'Наносит обычный урон без дополнительного эффекта.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'temperflare')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 924);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 925, 'Tera Blast', 'Normal', 2, 10, 80, 100, 1, 'Специальная атака обычного типа с силой 80. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'terablast')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 925);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 926, 'Tera Starstorm', 'Normal', 2, 5, 120, 100, 1, 'Специальная атака обычного типа с силой 120. Наносит обычный урон без дополнительного эффекта.', 'Наносит обычный урон без дополнительного эффекта.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'terastarstorm')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 926);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 927, 'Thunderclap', 'Electric', 2, 5, 70, 100, 1, 'Специальная атака электрического типа с силой 70. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'thunderclap')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 927);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 928, 'Tidy Up', 'Normal', 3, 10, 0, 0, 1, 'Статусная атака обычного типа. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'tidyup')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 928);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 929, 'Torch Song', 'Fire', 2, 10, 80, 100, 1, 'Специальная атака огненного типа с силой 80. Повышает Спец. атаку пользователя.', 'Повышает Спец. атаку пользователя.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'torchsong')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 929);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 930, 'Trailblaze', 'Grass', 1, 20, 50, 100, 1, 'Физическая атака травяного типа с силой 50. Повышает Скорость пользователя.', 'Повышает Скорость пользователя.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'trailblaze')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 930);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 931, 'Triple Dive', 'Water', 1, 10, 30, 95, 1, 'Физическая атака водного типа с силой 30. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'tripledive')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 931);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 932, 'Twin Beam', 'Psychic', 2, 10, 40, 100, 1, 'Специальная атака психического типа с силой 40. Бьет два раза за один ход.', 'Бьет два раза за один ход.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'twinbeam')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 932);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 933, 'Upper Hand', 'Fighting', 1, 15, 65, 100, 1, 'Физическая атака боевого типа с силой 65. Особый эффект поздних поколений: требует точной настройки в боевом движке.', 'Особый эффект поздних поколений: требует точной настройки в боевом движке.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'upperhand')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 933);

INSERT INTO attac_power (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
    atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
    cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
SELECT 934, 'Wicked Torque', 'Dark', 1, 10, 80, 100, 1, 'Физическая атака темного типа с силой 80. Может взаимодействовать со сном.', 'Может взаимодействовать со сном.', 0, '3', 0, 0, 0, 0, '0', 0, 0, 0, 'Импортировано из pokemon_moves_by_gen.xlsx. Поколение 9. Проверка дублей выполнена по имени атаки.'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM attac_power WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(atac_name), ' ', ''), '-', ''), '''', ''), '.', ''), ',', ''), ':', ''), '!', ''), '?', ''), '/', ''), '’', '') = 'wickedtorque')
  AND NOT EXISTS (SELECT 1 FROM attac_power WHERE atac_id = 934);
