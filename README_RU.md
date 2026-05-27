# PokemonChic / Pokemon Legend 8.0

Короткое актуальное описание проекта на русском. Подробный журнал переноса: [REWRITE_STATUS.md](REWRITE_STATUS.md). Краткая карта архитектуры: [PROJECT_CONTEXT.md](PROJECT_CONTEXT.md).

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

1. Production battle replay/audit для PvE/PvP.
2. Cron/background jobs: истечение лотов, рейсов, ивентов, временных предметов и зависших боёв.
3. Migration status table и health dashboard.
4. Длинные ручные 6x6 PvP-тесты двумя сессиями.
5. Полная проверка редких предметов, статусов, погоды и атак.
6. Первый путь нового игрока: квесты, NPC, локации, награды.
7. Защита экономики от дюпов и потери предметов/валюты/покемонов/яиц.

## QA-команды

```bash
php tools/http_smoke.php --login=Tacos --password=jungheinrick
php tools/pvp_qa_smoke.php --login1=Tacos --login2=NIGA --password1=jungheinrick --password2=jungheinrick
php tools/legacy_core_qa_smoke.php
php tools/breeding_qa_smoke.php --password=jungheinrick
php tools/commission_market_smoke.php
```

## Тестовые аккаунты

- `Tacos` - основной QA/admin.
- `NIGA` - второй игрок для PvP/торговли/breeding.
- `Система` - системные операции и будущие системные уведомления.
