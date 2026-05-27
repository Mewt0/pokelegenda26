# Open Test Regression Report - 2026-05-27

Цель: комплексный QA + UX + gameplay regression перед open-test. Проверка выполнялась как backend/API smoke, браузерный UI-pass и targeted fixes по найденным критичным хвостам.

## Итог

- Автоматический full regression: `24/24` блоков passed.
- Миграции: `73/73`, pending `0`, dirty `0`, failed `0`.
- DB integrity: `P0=0`, `P1=0`, `WARN=4`, `OK=16`.
- PvE: catch cycle `58/58`, finish cycle `59/59`.
- PvP Tacos/NIGA: `126/126`.
- Browser QA: `/game`, quest overlay, commission overlay, inventory overlay, `/game/tournaments`, `/game/admin` проверены на desktop/mobile-width; horizontal overflow не найден.

## Что Было Исправлено Во Время Прогона

### BUG-OPEN-001

Система: Commission / Notifications / QA stability  
Описание: комиссионка создавала `game_notifications.id` через `MAX(id)+1`, что при параллельных QA-прогонах могло дать duplicate primary key.  
Факт: при одновременном запуске market-smoke и hardening-smoke один из сценариев падал на duplicate key.  
Ожидание: уведомления должны создаваться через штатный `AUTO_INCREMENT`.  
Статус: fixed, retested.

### BUG-OPEN-002

Система: Reward pipeline / Inventory  
Описание: reward pipeline и часть smoke-скриптов создавали `items_users.id` через `MAX(id)+1`.  
Факт: при параллельных smoke возникали duplicate key на `items_users`.  
Ожидание: item rows создаются через `AUTO_INCREMENT`, smoke можно запускать повторно и безопасно.  
Статус: fixed, retested.

### BUG-OPEN-003

Система: Admin / GM Center UX  
Описание: legacy-карта с `game.php?go=...` была видна на дашборде админки из-за CSS override для `[hidden]`.  
Факт: legacy-блок отображался вне вкладки `Legacy-карта`.  
Ожидание: legacy-карта видна только в отдельной вкладке переноса.  
Статус: fixed, browser retested.

## Automated Regression Matrix

Запуск:

```bash
php tools/open_test_regression.php --profile=full --password=...
```

Результат: `Passed: 24`, `failed: 0`, `skipped: 0`.

| Блок | Результат |
|---|---:|
| Migrations | OK |
| DB integrity | OK, `P0=0`, `P1=0`, `WARN=4` |
| HTTP API read smoke | OK, `52/52` |
| Legacy core migration smoke | OK, `30/30` |
| First player experience | OK, `25/25` |
| Quest chains minimum | OK, `29/29` |
| Locations/NPC/transport | OK, `24/24` |
| Inventory/Held items | OK, `16/16` |
| Reward pipeline | OK, `13/13` |
| Safe storage | OK, `7/7` |
| Background jobs | OK, `10/10` |
| Commission market | OK, `24/24` |
| Commission hardening | OK, `36/36` |
| Economy guard | OK, `11/11` |
| Battle replay | OK, `6/6` |
| Notifications/mail | OK, `11/11` |
| Dex/AttackDex | OK, `27/27` |
| Tournament flow | OK, `33/33` |
| Admin/GM center | OK, `26/26` |
| Bug reporter | OK, `9/9` |
| PvE catch cycle | OK, `58/58` |
| PvE finish cycle | OK, `59/59` |
| Breeding Tacos/NIGA | OK, `55/55` |
| PvP Tacos/NIGA | OK, `126/126` |

## Browser QA

Проверено:

- `/game`: основной shell, нижнее меню, quick controls.
- Quest overlay: список квестов, статусы, rewards/objectives, tracked flow, mobile-width no horizontal overflow.
- Commission overlay: категории, `Мои лоты`, sellable list, no `Зелья/Ягоды` category, desktop/mobile-width no horizontal overflow.
- Inventory overlay: категории, большой список предметов, mobile-width no horizontal overflow.
- `/game/tournaments`: расписание/взнос/арена/reward hints, empty-state без поломки.
- `/game/admin`: GM Center, grouped menu, commission/admin tabs, fixed hidden legacy map.

Скриншоты:

- `tmp/open-test-commission-overlay.png`
- `tmp/open-test-commission-desktop.png`
- `tmp/open-test-quest-desktop.png`
- `tmp/open-test-admin-gm-fixed.png`

## Статус По Большим Зонам Из ТЗ

| Зона | Статус | Остаток |
|---|---|---|
| UX/UI polish | PARTIAL OK | Основные overlays без overflow; нужен 1-3h manual pass по всем popup/tooltip/scroll states. |
| Турниры | PARTIAL OK | Player-flow закрыт; нужны bracket UI, matchmaking, winner display, replay/history в турнире. |
| Кланы | TODO | В `GameRoutes` остаётся `todo`; нужна новая API/UI архитектура. |
| Редкие NPC | PARTIAL OK | Standard NPC/transport smoke OK; редкие event/hidden/stadium/craft NPC нужны отдельной матрицей. |
| Event systems | PARTIAL OK | Active events/notifications/jobs OK; нужны stacking/cleanup/timer/reconnect long tests. |
| Long-session stability | NOT FULLY RUN | В этом проходе сделан smoke + browser QA, но не 1-3h uninterrupted play. |
| Balancing | PARTIAL OK | Economy Guard работает; thresholds/abuse loops/drop rates требуют ручного баланса. |
| Full quest chains | PARTIAL OK | FPE/minimum/repeatable/cooldown OK; полные сюжетные цепочки ещё расширять. |
| Mobile UX | PARTIAL OK | Проверены ключевые overlays на mobile-width; нужны touch/keyboard/background реальные устройства. |
| Production cleanup | PARTIAL OK | Нет P0/P1; остаются integrity WARN и old API/PHP log tail в GM Center. |

## Оставшиеся Риски Перед Open Test

- `DB integrity WARN=4`: orphan legacy item owners, orphan legacy pokemon owners, один invalid egg owner, исторические unfinished battles. Это не P0/P1, но перед публичным тестом нужен cleanup policy.
- В GM Center виден `API/PHP errors: 12`; нужно разобрать актуальность tail логов, не трогая локальный `log_php_errors.txt` без отдельной задачи.
- Кланы пока не готовы как новая система.
- Турниры готовы как регистрация/арена/reward-flow, но не как полноценная сетка боёв.
- Полный long-session test 1-3 часа не был выполнен в этом коротком проходе; нужен отдельный manual QA слот.

## Следующий Рекомендуемый Этап

1. Cleanup policy для integrity WARN: что архивировать, что переносить в safe storage, что оставить как legacy bucket.
2. 60 минут manual session: FPE -> PvE -> inventory -> commission -> quest -> tournament -> PvP reconnect.
3. Отдельный clan discovery/implementation plan.
4. Tournament bracket/matchmaking v1.
5. Rare NPC/event NPC matrix и перенос самых опасных legacy interactions.
