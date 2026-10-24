# TASK-047 — отчёт: движок разложен на пять сервисов

**Исполнитель:** DSH-агент (шаги 1–7, 2026-10-11) · **Статус:** `done`
**Зона:** `src/Game/**`, `tools/battle_transformation_weather_smoke.php`

## Итог в цифрах

| Что | Было | Стало |
|---|---|---|
| `attackLocked` | 260 строк | **39 строк** |
| `applyMove` | 157 строк | вынесен в сервис |
| `pvpState` | 92 строки | вынесен в сервис |
| `BattleEngineService.php` | 3 521 строка | **2 853** |
| Сервисов боя | 0 | **5** |
| Кодировка русского текста | цела | цела (страж `OK` после каждого шага) |

Сервисы: `BattleTurnStatusService` 228, `BattleMoveService` 186, `BattleDamageService` 180,
`BattleRewardService` 168, `BattlePvpService` 119 (плюс каталоги правил
`BattleAbilityCatalog` 127, `BattleMoveEffectCatalog` 254, `BattleTransformationCatalog` 192,
`BattleMathService` 164 — они были и раньше).

`attackLocked` читается как последовательность шагов: загрузка боя и проверки → выбор атак и
PP → `applyPveTurnMoves` (порядок хода, атаки, HP, лог) → `resolvePveTurn` (победа с наградой,
поражение, боссовый бой, следующий раунд и ответ клиенту).

## Что переехало куда

| Что | Куда | Строк метода |
|---|---|---|
| Блок награды за победу в PvE | `BattleRewardService::grantPveVictoryRewards()` | 138 |
| `damageMove` | `BattleDamageService::damageMove()` | 153 |
| `applyStartOfTurnStatus` | `BattleTurnStatusService::applyStartOfTurnStatus()` | 202 |
| `applyMove` | `BattleMoveService::applyMove()` | 157 |
| `pvpState` | `BattlePvpService::pvpState()` | 92 |

Приватные помощники движка, которые понадобились сервисам, стали `public` и вызываются через
`$this->engine->...` — это внутренний контракт движка, а не переписывание логики. Состояние
`lastDamageDealt` осталось в движке и меняется через `setLastDamageDealt()`/`lastDamageDealt()`.
`BattleMoveService` получает `BattleDamageService` и `BattleTurnStatusService` теми же именами,
поэтому вызовы внутри перенесённого тела не менялись.

## Как переносилось (почему без порчи кодировки)

Две предыдущие попытки Codex провалились: первая дала сервисы-обёртки по 13 строк, вторая
физически перенесла тело, но превратила русский текст в двойную кодировку (10 636 маркеров,
`Бой завершен` исчезла, часть текста не восстановилась).

Здесь тело каждого метода переносилось **программно**: границы метода находил токенизатор PHP
по байтовым смещениям, тело копировалось байт в байт, менялись только сигнатура и вызовы
помощников. Текст не перезаписывался вручную, поэтому перекодировать его было нечему.
После каждого шага запускались `php -l`, `php tools/encoding_guard.php` и боевые смоуки.

Ошибки, которые поймали именно проверки (и были исправлены до коммита):

1. перенесённый блок применения хода использовал `$battleId` — параметр `attackLocked`;
2. блок не возвращал `$messages`;
3. подстановка `lastDamageDealt()` затронула присваивание — разделили на сеттер и геттер
   (иначе `php -l` падал на «Can't use method return value in write context»).

## Проверки (после каждого шага и в финале)

* `php tools/encoding_guard.php` → `OK` (нет маркеров мусора, русский текст цел);
* `php -l` по всем изменённым и новым файлам — чисто;
* `php tools/open_test_regression.php --profile=quick --login=Tacos --password=...` → **25/25**;
* `php tools/pvp_qa_smoke.php --password=...` → **126 проверок, 0 падений**;
* `php tools/http_smoke.php --login=Tacos --password=...` → **52 проверки, 0 падений**;
* `tools/pve_reward_idempotency_smoke.php` 9/9, `tools/pve_action_lock_smoke.php` 14/14,
  `tools/battle_replay_smoke.php` 6/6, `tools/battle_transformation_weather_smoke.php` 26/26,
  `tools/battle_transformation_stats_smoke.php` OK.

Попутно поправлен `tools/battle_transformation_weather_smoke.php`: он ходил в `damageMove`
через рефлексию по движку, а метод переехал в сервис. Теперь помощник ищет метод и среди
внедрённых сервисов движка.

## Что осталось (следующие кандидаты, не блокеры)

Крупнейшие методы, оставшиеся в движке (замер `F:\work\method-sizes.php`):

| Метод | Строк |
|---|---|
| `applySpecialMoveEffect` | 152 |
| `pvpUseItem` | 120 |
| `formatMoveEffects` | 107 |
| `useBall` | 105 |
| `resolvePveEnemyResponseAfterPlayerAction` | 99 |
| `state` | 91 |
| `resolvePveTurn` | 86 |
| `useItem` | 86 |

Логичное продолжение: боевые предметы (`useItem`, `pvpUseItem`, `battleItemEffect`,
`applyGenericBattleItem`, `useBall` — вместе ~450 строк) в `BattleItemService`; `applySpecialMoveEffect`
логично дописать в уже существующий `BattleMoveService`; форматтеры (`formatPokemon`, `formatMoves`,
`formatLogRows`, `groupLogByRound`, `battleEnvironment`) — в отдельный презентер, тогда сервисы
перестанут ходить в движок за представлением.

## История попыток

1. **Codex, прогон 1** — три сервиса-обёртки по 13–14 строк, тела не переехали, движок вырос
   до 3 235 строк. Отклонено.
2. **Codex, прогон 2** — тела переехали, кодировка разрушена (10 636 маркеров), гейт красный
   из-за auth-окружения прогона. Откат по решению владельца.
3. **Codex, прогон 3** — `damageMove` перенесён, `php -l` и страж прошли, но расчёт урона
   поехал: гейт 22/25, награда 2/3, погода 14/15. Откат.
4. **DSH-агент, шаги 1–7** — то, что описано выше: перенос программный, каждый шаг проверен.

`attackLocked` теперь читается как последовательность шагов:

* загрузка боя, проверки «бой завершён/не найден», боевцы, статусы, погода входа, запись replay;
* выбор атак и проверка PP;
* `applyPveTurnMoves` — порядок хода, применение атак, запись HP и лога (25 строк);
* `resolvePveTurn` — победа с наградой, поражение, продолжение боссового боя, следующий раунд
  и сборка ответа (85 строк).

## Что переехало куда

| Что | Куда | Строк |
|---|---|---|
| Блок награды за победу в PvE | `BattleRewardService::grantPveVictoryRewards()` | 138 |
| `damageMove` | `BattleDamageService::damageMove()` | 153 |
| `applyStartOfTurnStatus` | `BattleTurnStatusService::applyStartOfTurnStatus()` | 202 |

Приватные помощники движка, которые понадобились сервисам, стали `public` и вызываются через
`$this->engine->...` — это внутренний контракт движка, а не переписывание логики. Состояние
`lastDamageDealt` осталось в движке и меняется через `setLastDamageDealt()`/`lastDamageDealt()`,
чтобы значение не разъезжалось между классами.

## Как переносилось (почему без порчи кодировки)

Две предыдущие попытки Codex провалились: первая дала сервисы-обёртки по 13 строк, вторая
физически перенесла тело, но превратила русский текст в двойную кодировку (10 636 маркеров,
`Бой завершен` исчезла, часть текста не восстановилась).

Здесь тело каждого метода переносилось **программно**: границы метода находил токенизатор PHP
по байтовым смещениям, тело копировалось байт в байт, менялись только сигнатура и вызовы
помощников. Текст не перезаписывался вручную, поэтому перекодировать его было нечему.
После каждого шага запускались `php -l`, `php tools/encoding_guard.php` (страж кодировки,
созданный после той порчи) и боевые смоуки.

Две ошибки по пути были пойманы именно проверками и исправлены до коммита:

1. перенесённый блок применения хода использовал `$battleId` — параметр `attackLocked`;
   переменную добавили в сигнатуру нового метода;
2. блок не возвращал `$messages` — возврат добавили (смоук `pve_reward_idempotency` это поймал).

## Проверки (после финального шага)

* `php tools/encoding_guard.php` → `OK` (нет маркеров мусора, русский текст цел);
* `php -l` по всем изменённым и новым файлам — чисто;
* `php tools/open_test_regression.php --profile=quick --login=Tacos --password=REDACTED_USE_SMOKE_PASSWORD` → **25/25**;
* `php tools/pvp_qa_smoke.php --password=...` → **126 проверок, 0 падений**;
* `php tools/http_smoke.php --login=Tacos --password=...` → **52 проверки, 0 падений**;
* `tools/pve_reward_idempotency_smoke.php` 9/9, `tools/pve_action_lock_smoke.php` 14/14,
  `tools/battle_replay_smoke.php` 6/6, `tools/battle_transformation_weather_smoke.php` 26/26,
  `tools/battle_transformation_stats_smoke.php` OK.

Попутно поправлен `tools/battle_transformation_weather_smoke.php`: он ходил в `damageMove`
через рефлексию по движку, а метод переехал в сервис. Теперь помощник ищет метод и среди
внедрённых сервисов движка — смоук перестал зависеть от того, в каком классе живёт логика.

## Что осталось (следующие шаги, не блокеры)

* `applyMove` — 157 строк, следующий очевидный кандидат на такой же распил;
* `pvpState` — 92 строки;
* `BattleEngineService` всё ещё 3 098 строк: движок перестал быть свалкой, но 4–5 сервисов
  по 300–500 строк из плана пока не набраны (сейчас три по 168–228).

## История попыток

1. **Codex, прогон 1** — три сервиса-обёртки по 13–14 строк, тела не переехали, движок вырос
   до 3 235 строк. Отклонено.
2. **Codex, прогон 2** — тела переехали, кодировка разрушена (10 636 маркеров), гейт красный
   из-за auth-окружения прогона. Откат по решению владельца.
3. **Codex, прогон 3** — `damageMove` перенесён, `php -l` и страж прошли, но расчёт урона
   поехал: гейт 22/25, награда 2/3, погода 14/15. Откат.
4. **DSH-агент, шаги 1–5** — то, что описано выше: перенос программный, каждый шаг проверен.
