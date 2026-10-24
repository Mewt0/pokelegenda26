# PokemonChic / Pokemon Legend 8.0

Актуальный релизный baseline (2026-10-24): PHP 8.5.11, миграции 87/87, full regression 29/29. Текущие проверки и оставшиеся условия: [release candidate](docs/reports/RELEASE_CANDIDATE_2026-10-24.md). Старые числа ниже — исторические.

Короткое актуальное описание проекта на русском. Источник правды по текущей архитектуре и метрикам: [PROJECT_CONTEXT.md](PROJECT_CONTEXT.md). Подробный журнал переноса: [REWRITE_STATUS.md](REWRITE_STATUS.md).

## Текущий срез

Последний общий gate от 2026-10-07: сайт отвечает 200, quick-регрессия 24/24, read-smoke 52/52, миграции 79/79, integrity `P0=0`, `P1=0`, `WARN=0`, `ACCEPTED=4`. В `item_gameplay_metadata`: `implemented=42`, `visual_only=17`, `todo=11`.

## Что уже есть

- Новый вход через `public/index.php`.
- Игровой экран `/game` без legacy frameset.
- JSON API для карты, NPC, инвентаря, покемонов, боёв, яиц, breeding, квестов, рынка, комиссионной лавки и Trainer Card.
- PvE/PvP боёвка с smoke-проверками, историей боёв и запретом покеболов в PvP.
- Инвентарь, held items, подарочные ящики и временные предметы.
- Питомник, яйца, инкубаторы и разведение через буквы совместимости / Ditto / Экстракт Дитто.
- Комиссионная лавка `/game/commission` и overlay на `/game`: предметы, покемоны, яйца, лоты, покупка, снятие, комиссия, логи и risk-review в админке.
- Trainer Card как модальное окно с UID, аватаром, командой, подарками и gym badges.
- Game Master Center `/game/admin`: пользователи, предметы, выдача покемонов, атаки, локации, дроп, боссы, ивенты, модерация, медали, турниры и логи комиссионки.
- Служебный аккаунт `Система`.

## Главные хвосты до open test

Battle replay, background jobs, migration status и health-витрина в GM Center уже есть в текущем слое. Оставшиеся риски — не первичная реализация, а доведение и широкий QA-прогон:

1. Длинные ручные 6x6 PvP-тесты двумя сессиями.
2. Полная проверка редких предметов, статусов, погоды и атак.
3. Первый путь нового игрока: квесты, NPC, локации, награды.
4. Защита экономики от дюпов и потери предметов/валюты/покемонов/яиц.
5. Desktop/mobile browser regression перед приглашением open-тестеров.

## QA-команды

```bash
php tools/http_smoke.php --login=Tacos --password=REDACTED_USE_SMOKE_PASSWORD
php tools/pvp_qa_smoke.php --login1=Tacos --login2=NIGA --password1=REDACTED_USE_SMOKE_PASSWORD --password2=REDACTED_USE_SMOKE_PASSWORD
php tools/legacy_core_qa_smoke.php
php tools/breeding_qa_smoke.php --password=REDACTED_USE_SMOKE_PASSWORD
php tools/commission_market_smoke.php
```

## Тестовые аккаунты

- `Tacos` - основной QA/admin.
- `NIGA` - второй игрок для PvP/торговли/breeding.
- `Система` - системные операции и будущие системные уведомления.
