# Pokemon 8.0 Rewrite Status

Обновлено 2026-05-27: админка Комиссионной лавки доведена до рабочего GM Center. В `/game/admin` есть отдельная вкладка `Комиссионная лавка` в группе `Операции`: логи/лоты показывают время, действие, lot_id, объект, количество, цену за штуку, сумму, комиссию, продавца, покупателя и риск. Фильтры исправлены: select-значения теперь реально отправляют пустое значение для “любой”, а не строку `=любой`; добавлены дата-диапазон, фильтры `Система`/`Только риск`, сортировки по комиссии и количеству вверх/вниз. Инспектор сделки показывает аккуратный снимок предмета/покемона/яйца, кнопку истории цены, похожие активные лоты, последнюю продажу и кнопку `Проверено, сделка норм` для риск-сделок. Опасные сделки подсвечиваются через risk-флаги: дорогие лёгкие предметы вроде Pokeball, сделки 50 млн+, высокая цена за штуку и крупные дорогие стаки попадают в фильтр риска; проверенные сделки уходят из риск-фильтра и пишут `risk.approved` в `market_logs`. Legacy-карта не показывается как нижний блок на других вкладках, а доступна только через вкладку `Legacy-карта`. Проверено: таблицы `market_lots/market_logs/market_return_storage/market_deal_reviews` есть в БД; PHP lint `AdminRepository.php`, `AdminApiController.php`, `CommissionMarketRepository.php`, `views/game-admin.php`; JS syntax `public/js/admin-panel.js`; admin API smoke: Tacos получает dashboard/lots/logs/price-history/returns, NIGA получает `403`, POST risk-review без CSRF даёт `419`; transactional risk-review smoke прошёл без порчи БД; `tools/commission_market_smoke.php` прошёл `24/24`; `tools/http_smoke.php --login=Tacos --password=...` прошёл `51/51`. Browser QA: вкладка открывается, фильтры не обнуляют выдачу, инспектор/история цены работают, desktop без overflow, mobile без page horizontal overflow; широкая таблица скроллится внутри блока.

Обновлено 2026-05-27: Комиссионная лавка получила отдельный удобный режим `Мои лоты` в основном игровом overlay. Кнопка `Мои лоты` внизу левого блока переключает окно в режим продавца: слева показывается сетка объектов игрока с поиском, по центру таблица `Лоты выставленные на продажу` с колонкой `Снять с продажи`, снизу доступна форма выставления, а кнопка меняется на `Вернуться`. Версия JS/CSS лавки поднята до `20260527-my-lots`, чтобы браузер не держал старый режим. Серверная защита продажи усилена: `quest/bound/personal/no_trade/account_bound/привяз...` маркеры теперь блокируются не только из `items.dopolnen`, но и из metadata-полей предмета, поэтому привязанные/квестовые предметы не появляются в sellable-списке и не проходят прямой POST. Проверено: PHP lint `CommissionMarketRepository.php`, `views/components/commission-market-panel.php`, `views/game-start.php`, `views/game-commission.php`; JS syntax `public/js/commission-market.js`; `tools/commission_market_smoke.php` прошёл `24/24`, отдельный transactional bound-smoke прошёл `4/4`, `tools/http_smoke.php --login=Tacos --password=...` прошёл `51/51`. Browser QA: `/game` overlay открывается, `Мои лоты` показывает 77 sellable-объектов, UI-выставление и снятие лота Tacos записали `create/cancel` в `market_logs`; NIGA выкупил системный лот `Быстрый коготь` за 10000, лот стал `sold`, `buyer_id=29`, buy-лог с комиссией 500 записан. Desktop/mobile проверки без window/body horizontal overflow; mobile использует горизонтальный скролл сетки объектов.

Обновлено 2026-05-27: для сокращения рабочего контекста добавлен `PROJECT_CONTEXT.md` - короткий актуальный источник правды по архитектуре, текущим API, моделям данных, UI-системам, комиссионной лавке, QA-аккаунтам и smoke-скриптам. Старые промпты, дублирующиеся ТЗ, временные идеи, legacy UI-варианты, локальные скриншоты и исторические отчёты больше не считаются рабочим контекстом; `REWRITE_STATUS.md` остаётся журналом статусов, а `REWRITE.md` - историческим документом.

Обновлено 2026-05-27: добавлен постоянный служебный аккаунт `Система` для QA/рыночных/будущих системных операций. Миграция `2026_05_27_000001_system_account_notifications.sql` идемпотентно создаёт/обновляет пользователя `Система` (`system@pokemonchic.local`), фиксирует `system.account_id` и `system.account_login` в `site_settings`; в текущей БД это `id=10153`. Автоматическую подмену всех reward-push на `Система:` пока не включаем: отдельную механику “админ выдал → игрок получил системный пуш” надо спроектировать отдельно, чтобы не смешивать квестовые/боевые/рыночные уведомления. Для проверки сортировки лавки добавлены системные тестовые лоты по 1 предмету разных категорий от продавца `Система` через `legacy_source_type=system_sort_seed`. Проверено: миграция применена в БД, PHP lint `RewardRepository.php`, `CommissionMarketRepository.php`, `views/game-start.php`, `views/game-commission.php`, `views/components/commission-market-panel.php`, JS syntax `public/js/commission-market.js`, `tools/legacy_core_qa_smoke.php` прошёл `30/30`, `tools/commission_market_smoke.php` после отдельного запуска прошёл `24/24`, авторизованный `tools/http_smoke.php --login=Tacos --password=...` прошёл `51/51`.

Обновлено 2026-05-26: добавлена Комиссионная лавка v1. Новый маршрут `/game/commission` и API `/api/commission/*` работают как единый рынок предметов, покемонов и яиц: список лотов, поиск, категории, сортировка, вкладка `Мои лоты`, sellable-список, выставление, покупка и снятие. Добавлены таблицы `market_lots`, `market_logs`, `market_return_storage`, настройки `commission.*` в `site_settings`, резерв предметов через `items_users`, покемонов через `pok_user.users=3`, яиц через `eggs.users_egg=3`, комиссия 5%, safe-return storage и уведомления через `game_notifications`. Старые `/game/market/items` и `/game/market/pokemon` оставлены совместимыми входами в лавку; legacy `auction_items`/`rinok_poke` импортируются лениво в `market_lots`. Сервер запрещает зелья, ягоды, квестовые/временные/bound/экипированные и заблокированные объекты; Primal/Mega предметы вынесены отдельной категорией. Проверено: миграция применена в БД, PHP lint, `tools/commission_market_smoke.php` прошёл `24/24`, авторизованный `tools/http_smoke.php --login=Tacos --password=...` прошёл `51/51`, браузерный render `/game/commission` показывает категории без зелий/ягод, `Мои лоты`, форму выставления и без горизонтального overflow.

Обновлено 2026-05-26: проведён полный статус-аудит после свежих QA-smoke и browser-regression. Нижняя оценка готовности синхронизирована с верхними записями 2026-05-26: яйца, рынок покемонов, breeding-flow, gift-box, held items, Trainer Card/gym badges, Комиссионная лавка v1, PvP smoke и PvE catch/finish/ack больше не считаются ранними beta-заготовками. `GameRoutes::MODULES` на момент аудита: 26 модулей, из них `done=4`, `partial=18`, `todo=4`; это отражает маршруты, но не всю фактическую готовность отдельных подсистем, поэтому ниже добавлены уточнённые проценты и roadmap больших оставшихся систем.

Обновлено 2026-05-26: закрыт блок “Legacy → Новый слой / Core Systems / QA подготовка” для квестов, NPC, яиц и рынка покемонов. Добавлен новый `/api/quests` и страница `/game/quests`: журнал читает `quest_definitions`, `quest_steps` и состояние legacy `quest`, показывает статусы `available/active/completed/locked/cooldown`, reward JSON, зависимости и шаги; `POST /api/quests/start` запускает доступные квесты через CSRF. `/game/eggs` и `/api/eggs` подтверждены как основной новый слой для списка, инкубации и вылупления; `/game/market/pokemon` и `/api/market/pokemon` подтверждены как новый слой для list/buy/cancel поверх `rinok_poke`. Исправлена видимость приватных лотов рынка: теперь приватный лот видит и целевой покупатель, и продавец, чтобы его можно было снять. `PokemonMarketRepository::buy/cancel` стал корректно работать внутри внешней транзакции для QA-smoke без порчи боевых данных. Добавлен `tools/legacy_core_qa_smoke.php`: транзакционно проверяет quest start/progress/completion/dependency/reward notification, location transitions/blocked route/NPC routes/quest actions, egg incubate/hatch/start move/refresh persistence, pokemon market private lot/cancel/buy ownership transfer/battle lock/active pokemon protection. Проверено: PHP lint изменённых файлов, `tools/legacy_core_qa_smoke.php` прошёл `30/30`, авторизованный `tools/http_smoke.php --login=Tacos --password=...` прошёл `43/43` включая страницы `/game/quests`, `/game/eggs`, `/game/market/pokemon`, API `/api/quests`, `/api/eggs`, `/api/market/pokemon` и негативный CSRF `419`.

Обновлено 2026-05-26: breeding-flow приведён к новой игровой спецификации “буква совместимости + Экстракт Дитто”. Добавлена и применена идемпотентная миграция `2026_05_26_000010_breeding_letters_ditto_extract.sql`: `pokemon_breeding_rules.compatibility_letter`, индекс по букве, сиды A/B/C/M/W/X/Z для тестовых/ключевых линий, item `90310 Экстракт Дитто` стал held utility (`dress=1`, `target_use_rule=equip_held`). Дополнительно миграция `2026_05_26_000011_egg_incubator_item.sql` добавляет `90311 Инкубатор`, а `/api/eggs/incubate` теперь списывает инкубатор и уменьшает оставшееся время яйца в 2 раза; повторные ускорения работают последовательно. Серверная совместимость теперь требует разные полы и одинаковую букву, а бесполые пары работают только через Ditto или если Экстракт Дитто надет на обоих бесполых родителей с той же буквой; прямое “использовать предмет” больше не создаёт яйцо и не списывает предмет. IV яйца наследуются как максимум родителей по каждому стату с редкой мутацией `+1/+2/-1/-2`, яйцевая атака пытается наследоваться из активных атак родителей, если она есть в learnset ребёнка. Таймер новых breeding eggs выставлен на 24 дня. UI `/game/pokemon` показывает букву совместимости, статус `нс/спарен`, признак Extract, подсказку по held-логике и фильтр ПЦ вида `25 нс ж` / `pikachu м`; в меню игрока добавлен вход `❤️ Разведение`, который открывает `/game/pokemon?breed_with=<login>` и подставляет партнёра. QA подготовка обновлена: Tacos/NIGA получили genderless Magnemite-пару с надетым Extract и инкубаторы. Проверено: миграции повторно применяются, PHP lint изменённых файлов, inline JS syntax OK, `player-menu.js --check` OK, браузерный DOM показывает `Экстракт Дитто`, фильтр, буквы и target `NIGA`, `tools/breeding_qa_smoke.php --password=jungheinrick` прошёл `55/55`: normal-pair egg, wrong-letter block, genderless+normal block, genderless+Ditto egg, genderless+genderless+Extract egg, прямой Extract не списывается, легендарка заблокирована, `/api/eggs` видит яйца, инкубатор режет таймер и списывается.

Обновлено 2026-05-26: добавлен и проверен новый breeding-flow для питомника между игроками. Legacy `include/files/sparka.poke.php` больше не является единственной логикой для QA-разведения: добавлены таблицы `pokemon_breeding_rules`, `pokemon_breeding_requests`, parent-поля в `eggs`, API `/api/pokemon/breeding`, `/request`, `/respond`, `/ditto-essence` и UI-панель в `/game/pokemon`. Правила: normal-пары требуют разных полов и общей egg group; genderless запрещён без Ditto; Ditto+Ditto запрещён; `no_eggs`/легендарные не разводятся; кастомный предмет `90310 Эссенция Дитто` создаёт яйцо для breedable genderless как helper и списывается только при успехе. Подготовлены Tacos/NIGA: Bulbasaur male/female, несовместимый Pidgey, Magnemite genderless, Ditto и blocked Kyogre; предмет выдан обоим. Проверено: миграция повторно применяется, PHP lint новых файлов, JS syntax `/game/pokemon`, браузерный UI показывает панель разведения и не выбирает disabled No Eggs покемона по умолчанию, `tools/breeding_qa_smoke.php --password=jungheinrick` прошёл `43/43`: compatible egg, incompatible blocked, genderless+normal blocked, genderless+Ditto egg, Ditto Essence success, failed Essence не списывает предмет, `/api/eggs` видит созданные яйца.

Обновлено 2026-05-26: закрыт отдельный блок held/lore item metadata. Добавлена и применена идемпотентная миграция `2026_05_26_000008_held_lore_item_metadata.sql`: `items.dopolnen` расширен до `VARCHAR(128)`, создана таблица `item_gameplay_metadata` с русским названием, английским alias, категорией, описанием, target-use правилом, battle/equip флагами, статусом эффекта и совместимостью. Все 66 предметов из списка `78..520` заведены в metadata, missing item `327 Звёздная пыль` добавлен в `items`, runtime-иконки в `public/img/items/index.json` проверены без пропусков. Рабочие type-boost предметы получают `held_item:*` метки, Quick Claw/Scope Lens/Wide Lens/Choice Specs/Iron Ball/Assault Vest/Soul Dew/Thick Club связаны с уже реализованными боевыми эффектами, а неполные эффекты помечены `visual_only` или `todo`. Совместимость экипировки теперь читается из БД: Soul Dew только Latios/Latias, Thick Club только Cubone/Marowak, Blue Orb только Kyogre, Red Orb только Groudon; ID `78` сохранён как существующая `Случайная TM-атака`, чтобы не сломать TM/market flow, но asset-alias Pink Bow зафиксирован в metadata. Проверено: миграция повторно применяется без дублей, PHP lint `InventoryRepository.php` и `BattleEngineService.php`, reflection-smoke совместимости, авторизованный `tools/http_smoke.php --login=Tacos --password=jungheinrick` прошёл `38/38`.

Обновлено 2026-05-26: проведён финальный QA-регресс по плану `3.5h` в ускоренном комбинированном формате: setup, admin issue flow, PvE, PvP, held items, Trainer Card и общий regression. Авто-проверки: `tools/http_smoke.php --login=Tacos --password=jungheinrick` прошёл `38/38`, `tools/pvp_qa_smoke.php --login1=Tacos --login2=NIGA --password1=jungheinrick --password2=jungheinrick` прошёл `126/126`, mutating PvE `--battle=catch` прошёл `42/42`, mutating PvE finish/rewards/ack `--battle=finish` прошёл `45/45`, CSRF negative возвращает `419`. DB integrity: Tacos/NIGA имеют по 6 активных покемонов, питомник отделён (`Tacos=15`, `NIGA=1`), held-item references без сирот, `items_users.id=0` отсутствует, `pok_user.basenum` не содержит постоянных Mega/Primal `5000..5099`, пользователи не застряли в PvP после smoke. Admin issue flow проверен транзакционно с rollback: выдача `#382 Kyogre - Shiny Lv.100` возвращает чистый base label без дубля `#382 #382 382`, Pokemon ID созданной записи, Shiny, IV/EV, характер, HP/статы и стартовую атаку `Ice Beam #58`; UI формы `/game/admin -> Покемоны -> Создать` содержит поля игрока/покемона/IV/EV/статов и быстрые кнопки. Browser QA: desktop `/game/pokemon` показывает 6 активных и 15 в питомнике, held icons `6/3`; mobile viewport `390x844` без горизонтального overflow, held icons сохраняются; PvE UI показывает Primal Kyogre, Blue Orb, 4 атаки, strong weather, enemy sprite, вкладки Fight/Pokemon/Items/Balls, switch-list показывает Rayquaza/Lucario и held item names; бой завершён через escape + ack без stuck state. Trainer Card открывается кликом по `Тренеркарта` на текущей странице, отображает 6 party slots, 6 held icons, 2 gym badges, друзей и UID без console errors. Критичных P0/P1 по итогам regression не найдено: 500/fatal, потеря предметов/покемонов/валюты, permanent primal state и PvP desync не воспроизведены. Скриншоты QA сохранены в `tmp/final-qa-*.png`.

Обновлено 2026-05-26: закрыт UI-блок покемонов и held items. `/game/pokemon` снова показывает два отдельных блока в overview: `Активная команда` содержит строго `active=true` и до 6 слотов, `Питомник` вынесен отдельной секцией ниже и не смешивается с командой после refresh. Trainer Card теперь получает `heldItem` для активной команды через `/api/profile/card` и рисует маленькую иконку предмета на каждом покемоне; query-version JS/CSS обновлён, чтобы браузер не держал старый рендер. Проверено: PHP lint `ProfileRepository.php`, `views/game-pokemon.php`, `views/game-start.php`, `tools/http_smoke.php`; `node --check trainer-profile-window.js`; HTTP-smoke Tacos 38/38; браузерный QA: `/game/pokemon` после refresh показывает 6 активных, 15 в питомнике, held icons `6/3`; battle UI показывает held item у активного Primal Kyogre и в списке смены; Trainer Card показывает 6 held icons, console errors нет.

Обновлено 2026-05-26: блок Trainer Card + Gym Badges доведён до явного API-контракта и reward-flow. `/api/profile/card` теперь помимо старых `user/party/gymBadges` отдаёт верхнеуровневые `uid`, `avatar`, `rank`, `clan`, `activeTeam`, `gifts`, `badges`, чтобы фронт и QA не зависели от внутренней структуры `user`. Добавлена и применена миграция `2026_05_26_000007_gym_badges_reward_type.sql`: `user_gym_badges` хранит `reward_type='gym_badge'`, `issued_at`, `source_battle_id`, `source_quest_id`; старые выдачи backfill-нуты из `awarded_at/source_type/source_id`. `RewardRepository` получил `grantReward(..., 'gym_badge', ...)` и `grantGymBadge()`: повторная выдача не создаёт дубль из-за `UNIQUE(user_id,badge_id)` и возвращает `granted=false`, успешная выдача пишет push-уведомление. Trainer Card показывает location/source/issued date в tooltip значка. Проверено: PHP lint `ProfileRepository.php`, `RewardRepository.php`, `tools/http_smoke.php`; `node --check trainer-profile-window.js`; транзакционный DB-smoke выдачи/повтора/rollback; авторизованный HTTP-smoke Tacos 37/37; браузерный рендер модалки Tacos показывает UID, активную команду и 2 gym badges.

Обновлено 2026-05-26: ловля в PvP заблокирована на сервере и в боевом UI. Прямой `action=ball` во время активного PvP теперь возвращает JSON-ошибку `Покеболы нельзя использовать в PvP-бою`, а вкладка `Balls`, catch-блок и клики по покеболам скрываются/отклоняются на фронте при `battle.mode = pvp`. Это оставляет ловлю только для PvE/wild-сценариев.

Обновлено 2026-05-26: блок подарков доведён и перепроверен. `Уникальный подарок` теперь имеет гарантированный базовый дроп `Красная конфета`, чтобы открытие не превращалось в пустой результат; случайные бонусы остались в loot table. Серверное открытие подарка стало атомарным: начисление наград, списание коробки и запись push-лога `Подарок открыт` выполняются в одной транзакции, поэтому при ошибке логирования/выдачи подарок не теряется. Транзакционный smoke на Tacos открыл и откатил все подарки без мусора в БД: `Уникальный подарок`, `Подарок`, `Боевой набор`, `Набор held items`, `Набор эволюции`, `Праймал/Мега набор`, `Премиальный тестовый подарок`; у каждого проверены loot rows, rewards и `game_notifications`. Авторизованный `tools/http_smoke.php --login=Tacos` прошёл 37/37.

Обновлено 2026-05-26: усилены held items. Runtime-ассеты для `344`, `358`, `90200`, `90201` и базовых held/lore items проверены в `public/img/items` и `public/img/items/index.json`. Серверный equip теперь запрещает неверные пары: Blue Orb только Kyogre, Red Orb только Groudon, Soul Dew только Latios/Latias, Thick Club только Cubone/Marowak. В бою подключены безопасные эффекты held items: type-boost предметы вроде Metal Coat/Twisted Spoon/Charcoal/Dragon Fang дают x1.2 к своему типу, Soul Dew усиливает Dragon/Psychic атаки Latios/Latias, Thick Club удваивает Attack Cubone/Marowak, Scope/Razor Lens повышает крит, Wide Lens повышает точность, Quick Claw даёт шанс приоритета, Iron Ball режет Speed, Assault Vest повышает Sp.Def и блокирует статусные атаки. Визуальные/неполные предметы остаются экипируемыми без скрытого эффекта. Smoke на реальной БД подтвердил ограничения и модификаторы. `tools/http_smoke.php` расширен проверками `/api/eggs`, `/api/market/pokemon` и `/api/profile/card`; авторизованный smoke Tacos проходит 37/37.

Обновлено 2026-05-26: закрыт UX-баг открытия подарков на отдельной странице `/game/items`. Кнопка `Открыть` теперь доступна только для предметов с target-rule `open_gift`, вызывает `/api/inventory/open-gift`, сохраняет итоговое сообщение после перерисовки инвентаря и создаёт push-уведомление `Подарок открыт` через `game_notifications`. Проверено в браузере на Tacos: `Уникальный подарок` открылся, количество уменьшилось `x99 -> x98`, сообщение с наградой осталось на экране, уведомление записалось. Дополнительно проверены `/game/eggs`, `/game/market/pokemon`, `/game` без console errors; в активном PvE-бою вкладка `Pokémon` показывает Rayquaza/Lucario/Dialga/Zekrom/Xerneas со спрайтами, HP и held item.

Обновлено 2026-05-26: углублён smoke по legacy-переносу яиц и рынка покемонов. Транзакционный DB-smoke создал готовое яйцо Bulbasaur, `/api/eggs`-репозиторий увидел его, `hatch()` создал `pok_user` со стартовой атакой `Tackle` и затем всё откатилось. Для рынка покемонов найден и исправлен `SQLSTATE[HY093]` при поиске по ID: повторяющиеся PDO placeholders заменены на уникальные, а текстовый поиск продавца теперь использует единый alias `seller`. Транзакционный smoke подтвердил приватный лот Tacos -> NIGA и поиск лота по `pokemon_id` и продавцу без записи мусора в БД.

Обновлено 2026-05-26: добавлен блок QA-переноса для яиц, рынка покемонов, подарков и тестовых battle-команд. Появились новые API `/api/eggs` (`incubate`, `hatch`) и `/api/market/pokemon` (`list`, `buy`, `cancel`) поверх legacy-таблиц `eggs` и `rinok_poke`, без прямого исполнения старых PHP-разделов. Миграция `2026_05_26_000005_plan_items_gifts_learnsets.sql` применена в БД и повторно проходит без дублей: добавлены `item_gift_loot`, `gym_badges`, `user_gym_badges`, тестовые подарочные ящики, недостающие held/lore items, gift target-rule `open_gift` и learnset для Kyogre/Groudon/Rayquaza/Lucario/Charizard/Mewtwo. Миграция `2026_05_26_000006_seed_gym_badges_and_trainer_card.sql` добавила базовые gym badges для Trainer Card. `/api/profile/card` теперь отдаёт `gymBadges`, а модальная Trainer Card показывает их отдельной полосой над наградами. Картинки предметов скопированы из `downloaded_assets_sorted/items_60x60_numeric` в `public/img/items`, `public/img/items/index.json` обновлён. Уникальный подарок/ящики открываются через `/api/inventory/open-gift` с rollback-safe логикой: при ошибке предмет не списывается. Админская выдача покемона теперь чисто показывает `#382 Kyogre` без дубля `#382 #382 382 Kyogre`, возвращает `base_id`, созданный `Pokemon ID`, Shiny, IV/EV, характер, статы, HP и стартовую атаку; транзакционный smoke Kyogre для Tacos прошёл со стартовыми атаками и откатился без мусора в БД. Добавлен `tools/prepare_qa_teams.php`: он идемпотентно подготовил Tacos/NIGA для боевых QA-прогонов, выдал полные 6x6 команды Lv.100, Primal/Mega held items, подарочные наборы и тестовые gym badges Tacos. Балансные добавления: Tacos получил Dialga/Zekrom/Xerneas, NIGA получил Palkia/Reshiram/Yveltal. Дополнительно исправлен collation-баг рынка покемонов при чтении приватных лотов и проверка фактической передачи покемона при покупке/снятии. Боевой UI смены покемона увеличен по высоте, правая колонка расширена, список `Pokémon` теперь показывает спрайт, Lv, HP и held item каждого доступного покемона; в активной PvE-сессии Tacos список Rayquaza/Lucario/Dialga/Zekrom/Xerneas проверен в браузере.

Обновлено 2026-05-26: переработан боевой инвентарь. `/api/inventory/battle` теперь отдаёт все активные боевые предметы и покеболы с `battle_pocket`, `battle_category`, `catch_modifier`, `summary` и категориями; Poké Balls больше не теряются среди обычных предметов и не скрываются из-за `battleuse=0`. Battle UI получил отдельные вкладки `Fight / Pokémon / Items / Balls`, sticky-фильтры внутри Items, компактные строки, нативный scroll/touch-scroll без лимита `slice(0, 12)` и обновление количества после использования. В бою, списке смены, команде и питомнике показывается экипированный предмет покемона. Проверено: PHP lint изменённых backend/view файлов, rendered JS syntax smoke после авторизованного входа, read-only HTTP-smoke 32/32 OK, `--battle=catch` 37/37 OK; ловля списала Покебол `113 -> 112`, состояние боя после cleanup `battleid=0, pve=0, pvp=0`. Codex Browser-панель была недоступна (`No active Codex browser pane available`), поэтому живой 30-минутный клик-прогон в UI нужно повторить в открытом браузере вручную.

Обновлено 2026-05-26: проведён QA-hardening блока Trainer Card + Battle Transformations. Исправлены найденные баги: `finishPvpBattle()` падал при сбросе формы из-за повторного PDO-placeholder `:time`; обычные погодные способности могли перетереть Primal/Mega weather; strong weather могла не переактивироваться после смены, если уже стоял volatile `weather_started`; Primal/Mega предметы можно было попытаться надеть на неверного покемона. Теперь Blue Orb подходит только Kyogre, Red Orb только Groudon, Mega Rayquaza требует `Dragon Ascent`, формы не пишутся в `pok_user.basenum`, атаки сохраняются от обычного покемона, после завершения боя пишется `[REVERT]`, а в логах появились `[TRANSFORM]`, `[WEATHER]`, `[DAMAGE_MODIFIER]`, `[DAMAGE_BLOCKED]`. В БД применена миграция `2026_05_26_000004_harden_battle_transformation_abilities.sql` с точными AbilityDex-описаниями `Приморское море`, `Выжженная земля`, `Дельта-поток`. Проверено: PHP lint изменённых классов, JS syntax `trainer-profile-window.js`, `player-menu.js`, `chat.js`, транзакционный smoke для Kyogre/Groudon/Rayquaza, read-only HTTP-smoke `tools/http_smoke.php` 32/32 OK, ProfileRepository smoke для Tacos/NIGA.

Обновлено 2026-05-26: добавлен серверный слой Battle Transformations для временных форм в бою. В БД применена миграция `2026_05_26_000003_battle_transformations.sql`: появились `battle_transformations`, `pokemon_transformation_unlocks`, предметы `90200..90248` для Blue/Red Orb и Mega Stones, а также target-use правила `equip_held`. `BattleRepository` теперь видит held item из `items_poke`, автоматически активирует Primal/Mega форму при входе активного покемона в бой, не меняет `pok_user.basenum`, масштабирует боевые статы по форме, подменяет тип/способность/спрайт только в battle state и сбрасывает форму после завершения боя. Rayquaza получает Mega Rayquaza без камня только если знает `Dragon Ascent`. Иконки предметов скачаны в `public/img/items/90200..90248.png` и добавлены в `public/img/items/index.json`. Проверено: PHP lint `BattleTransformationCatalog.php`, `BattleRepository.php`, `InventoryRepository.php`, `BattleEngineService.php`; миграция применена к локальной БД; транзакционный smoke подтвердил `Charizard + Charizardite X -> Mega Charizard X` в бою без постоянного изменения покемона.

Обновлено 2026-05-26: Покедекс получил нормальные ассеты разновидностей для Primal Kyogre, Primal Groudon и Mega Rayquaza из `static.pokelegenda.ru`. Normal-арт лежит в `public/img/pokemon/art`, shiny-арт в `public/img/pokemon/art-shiny`, миниатюры для блока «Разновидности» в `public/img/pokemon/small` и `public/img/pokemon/small-shiny`. `DexRepository` больше не использует normal-small как shiny-замену и понимает алиасы имён `kyogreprimal/groudonprimal/rayquazamega`; frontend Покедекса рисует формы через компактные миниатюры и переключает normal/shiny корректно. Проверено: PHP lint `src/Repository/DexRepository.php`, `node --check public/js/dex-overlay.js`, repository-smoke для `382/5017`, `383/5018`, `384/5019`.

Обновлено 2026-05-26: закрыт smoke-долг после gameplay-проверок. Добавлен авторизованный HTTP-smoke `tools/http_smoke.php`: логинится через форму, достаёт CSRF, проверяет `/api/game/state`, `/api/events/active`, категории инвентаря, `/api/pokemon/moves`, `/api/inventory/battle`, `/api/battle/history`, негативный CSRF, питомник, battle state, ловлю и награды через опциональные режимы `--battle=catch|finish|force`. Исправлена отправка HTTP 419 в `Response`, чтобы CSRF-ошибки больше не маскировались как 500. NPC-питомник показывает до 24 покемонов и даёт кнопку на полный список `/game/pokemon`; новый API-питомник по-прежнему обновляет данные без full refresh и снимает -10 счастья. Проверено: PHP lint `tools/http_smoke.php`, `NpcDialogService.php`, `Request.php`, `Response.php`; read-only smoke 31/31 OK; `--mutate-daycare` 32/32 OK; `--battle=force` 35/35 OK; `--battle=catch` 37/37 OK; `--battle=finish` 38/38 OK; транзакционный DB-smoke подтвердил `game_event_boosts` happiness x3 -> multiplier 3 с rollback.

Обновлено 2026-05-25: добавлены классические Mega/Primal формы в диапазоне `5000..5049`. Существующие формы `5000..5016` обновлены способностями и кодом базового покемона, добавлены Primal Kyogre `5017`, Primal Groudon `5018`, Mega Rayquaza `5019` и недостающие Mega-формы до Mega Diancie `5049`; learnset и egg moves копируются с базовых покемонов. Обычные Kyogre/Groudon/Rayquaza теперь имеют обычные погодные способности (`Морось`, `Засуха`, `Штиль`), а Primal/Mega формы включают `Первозданное море`, `Выжженную землю`, `Дельта-поток`. Особая погода отображается в battle state, блокирует Fire/Water атаки для Primal-погоды и ослабляет Flying-слабости при `Сильном ветре`. Покедекс показывает блок «Формы» и описания способностей. Счастье покемона оставлено в текущем диапазоне `0..100`. Проверено: миграция `2026_05_25_000007_seed_mega_primal_forms.sql`, PHP lint изменённых классов, `node --check public/js/dex-overlay.js`, repository-smoke для Kyogre/Groudon/Rayquaza, Mewtwo, Charizard и Diancie.

Обновлено 2026-05-26: для Primal Kyogre `5017`, Primal Groudon `5018` и Mega Rayquaza `5019` добавлены боевые Showdown GIF-спрайты прямо в старую структуру `Pok`: `spriteanim`/`pok`/`anim` для передней стороны, `back` для задней, `shiny` и `sback` для shiny-вариантов. PvE/PvP battle state теперь дополнительно отдаёт `sprites.front/back/frontShiny/backShiny` с путями `/Pok/...`, а фронт боя предпочитает shiny front/back, когда покемон shiny.

Обновлено 2026-05-26: Mega/Primal формы получили явную серверную мету `pokemon_forms`: внутренний id формы остаётся `5000..5049`, публичный номер Покедекса остаётся номером базового покемона (`5017 -> #382`, `5018 -> #383`, `5019 -> #384`). API Покедекса и боёв отдают `formId`, `baseId`/`dexNumber`, `formKey`, `isForm`, поэтому фронт показывает Primal Kyogre под `#382`, а сервер всё равно понимает, что это `5017` с `primordial_sea`. Перевод покемона в форму той же species теперь наследует текущие атаки, IV/EV, HP и рассчитанные статы игрока без пересчёта, меняя только форму/имя; проверено транзакционным smoke-тестом Kyogre `382 -> 5017`. Добавлена таблица `pokemon_ability_descriptions` с русскими названиями и описаниями способностей Mega/Primal форм, Покедекс читает эти описания из БД. В локальную БД `3308` применены все миграции `2026_05_25_*` и `2026_05_26_*`.

Обновлено 2026-05-25: `/game/events` перестал быть заглушкой переноса. Добавлен новый `GameEventRepository`, `EventApiController`, публичные API `/api/events` и `/api/events/active`, отдельный экран `views/game-events.php` с активными событиями, будущими событиями, личными бустами, итоговыми множителями и QA-чеклистом. Раздел больше не исполняет `include/files/gameload.world.php`; legacy `game.php?go=gameload` остаётся только историческим источником логики. Проверено: PHP lint новых файлов, авторизованный API-smoke, создание временного `exp x2` события через админский API, отображение `pve.exp = 2`, удаление тестовой записи, открытие `/game/events` в браузере без console errors.

Обновлено 2026-05-25: проведён первый реальный gameplay-QA проход по основному циклу: PvE бой, смена покемона, Toxic/отравление, ловля на низком HP, боевые предметы, дроп, магазин, лечение, питомник, квестовые данные и игровые события. Исправлены: обновление счётчиков боевого кармана после покебола/предмета, пересечение кликабельных строк предметов в бою, категории инвентаря `Квестовые`/`Дроп`, сохранение истории PvE перед `ack-end`, буст `happiness`, буст `catch`, пустой `battle.moves` на финальном экране боя. Добавлены отчёт `QA_GAMEPLAY_REPORT_2026-05-25.md`, план переноса `/game/events` `GAME_EVENTS_MIGRATION_PLAN.md`, миграции `2026_05_25_000004_fix_quest_utf8_and_steps.sql`, `2026_05_25_000005_fix_capture_ball_aliases.sql`, `2026_05_25_000006_battle_history_archive.sql` и инструмент `tools/qa_drop_mode.php`. Тестовый QA-дроп после проверки выключен.

Обновлено 2026-05-25: доработан питомник/команда покемонов. `/api/pokemon/nursery` теперь отправляет покемона в питомник и возвращает из питомника без перезагрузки страницы, с CSRF, проверкой боя и защитой от поломки команды. Сервер нормализует старые данные: активная команда не может оставаться больше 6, лишние покемоны переводятся в питомник; стартового покемона нельзя убрать, и нельзя оставить игрока без активных покемонов. Окно `/game/pokemon` теперь при открытии всегда подтягивает свежий список, при закрытии сбрасывает выбранного покемона, показывает раздельные блоки `Активная команда` и `Питомник`, статусы карточек и кнопки `В питомник` / `В команду`. Проверено на Tacos: закрытие/повторное открытие не сохраняет старую выбранную карточку, Venusaur отправляется в питомник и возвращается в команду без full refresh, ошибок в консоли нет.

Обновлено 2026-05-25: выдача покемонов в админке проверена и усилена. Форма `/game/admin -> Покемоны -> Создать` теперь явно подписывает `Гены / IV`, показывает нормальные русские характеры, имеет быстрые кнопки `Гены 32 всем`, `EV 0 всем`, `Статы 60 всем` и подсказку по ограничениям. Сервер принимает гены вручную в диапазоне `0..9999`, EV держит в диапазоне `0..252` на стат и отклоняет сумму выше `510`. При выдаче автоматически ставится стартовая атака: первая доступная по уровню из `attac_poke`, а если по уровню ничего нет - первая атака покемона как fallback. Ответ API теперь возвращает полный текст: имя, уровень, игрок, гены, EV, стартовая атака, HP и итоговые статы. Прогнан browser-smoke через админку на NIGA/Bulbasaur и API/DB-сценарии с минимальными, максимальными, смешанными, одинаковыми, ручными статами и shiny-режимом; тестовые покемоны после проверки удалены.

Обновлено 2026-05-25: добавлена beta-версия перелётов по билету на самолёт. Миграция `2026_05_25_000003_plane_ticket_flights.sql` добавляет/обновляет предмет `90111 Билет на самолёт`, товар Покемаркета за `50` алмазов, `item_target_rules.target_type=flight`, таблицы `transport_flight_routes` и `transport_flights`, а также локацию `95001 На борту самолёта`. Билет применяется из инвентаря: вместо выбора покемона открывается выбор рейса в другой регион, после посадки игрок переносится на борт на 15 минут. В локации самолёта есть `Проводник` со статусом таймера и `Выход`; ранний выход возвращает ошибку `Мы ещё летим`, после прибытия списывает билет и переносит игрока на стартовую локацию региона. Старые `transport_routes` для парохода/моментальных рейсов сохранены. Проверено: повторный запуск миграции, `/api/transport/flight-routes`, `/api/transport/flight-start`, `/api/transport/flight-status`, `/api/transport/flight-exit`, `/api/game/state` на борту и браузерный UI инвентаря без console errors.

Обновлено 2026-05-25: добавлена первая рабочая версия боссов на локациях. В БД появились `location_bosses`, `location_boss_pokemon`, `location_boss_drops`, `boss_battle_sessions`; миграция `2026_05_25_000002_location_bosses.sql` проверена повторным запуском. В админке добавлен раздел `Боссы`: можно задать локацию, название, описание, event key, окно появления, команду до 6 покемонов с уровнем/IV/EV/атаками/предметом и boss-drop. На `/game` активные боссы показываются отдельными кнопками на локации, старт идёт через `/api/bosses/start`. Бой использует текущий PvE UI, но теперь поддерживает цепочку 6 на 6: следующий покемон босса выходит после нокаута, следующий живой покемон игрока подставляется после поражения активного. Награды босса выдаются только после победы над всей командой, обычный wild-drop финального покемона при boss-win не роллится. Предмет босса подключается как held item для боевого pve-покемона, поэтому существующие эффекты held item, например погодные камни, доступны и боссам. Статус модуля: `PARTIAL_NEW`, потому что отдельный `item_effects_json` для уникальных эффектов boss-предметов ещё надо расширять.

Обновлено 2026-05-09: проведена точечная оценка Покедекса, Атакадекса, Инвентаря и боевых эффектов. В БД сейчас `attac_power=937`, поэтому Атакадекс должен показывать полный список, а не первые 100/687. Покедекс и Атакадекс остаются `PARTIAL_NEW`, но ближе к рабочему справочнику: поиск и карточки есть, основной долг - полнота связей egg/level-up/эволюций и проверка missing assets. Инвентарь усилен: миграция `2026_05_09_000002_complete_inventory_item_effects.sql` перевела основные `item_target_rules` из `pending` в рабочие эффекты `exp_candy`, `pp_vitamin`, `evolution_item`, `tm_learn`, `equip_held`, `nature_neutral`, `boost_exp/drop/money`, `training_train`, `training_weaken`. В PvE/PvP бою дополнительно подключены боевые эффекты для PP-витамина и кекса с ягодами. Готовность после этого блока: Покедекс 70%, Атакадекс 75%, Инвентарь 68%, боевые статусы/погода/ловушки 70%. До `DONE_NEW` ещё нужен ручной прогон применения каждого класса предметов, ловли/побега/поражения/ack-end и выборочная проверка редких атак.

Обновлено 2026-05-09: открыт beta-блок стабилизации. PvP-заявки получили `expires_at/responded_at`, ленивое истечение через 120 секунд, идемпотентный accept и `/api/battle/history`; PvP-предметы теперь идут через общий action-flow и для beta разрешены только `battleuse=1` без покеболов. Инвентарь получил серверные категории, поиск по ID/названию, фильтрацию активных временных предметов и первые target-use эффекты: `exp_candy`, `pp_vitamin`, `boost_exp/drop/money`. Почта поверх legacy `sends` получила `mail_message_state`, read/unread и архив без физического удаления писем. Админка получила `/api/admin/lookups?type=&q=` для lookup-полей. Миграция `2026_05_09_000001_open_beta_foundation.sql` применена к локальной БД на `3308`.

Обновлено 2026-05-08: модерация получила единую таблицу `moderation_punishments`, форму действий в `/game/admin` и чат-команды для модераторов: `/mute`, `/unmute`, `/ban`, `/unban`, `/warn`. Мут проверяется перед отправкой обычного сообщения, бан через команду/админку дополнительно пишет IP в `banip`, все действия из админки попадают в `admin_audit_log`. Миграция `2026_05_08_000009_create_moderation_punishments.sql` проверена повторным запуском.

Обновлено 2026-05-08: добавлен общий слой наград и пуш-уведомлений `game_notifications`: квестовые награды, крафт и победы в PvE теперь создают игровые уведомления. Добавлены таблицы `quest_definitions`, `quest_steps` для многоэтапных и зависимых квестов, а также `game_event_boosts` и `player_boosts` для x2/x4 и предметных бустов. В админку добавлены разделы `Дикие слоты` для `pokebuild` и `Ивенты/бусты`; диких покемонов на локациях теперь можно задавать без ручного SQL.

Обновлено 2026-05-08: нормализована живая таблица `chats`: добавлен `author_id`, заполнены авторы старых сообщений, `id` стал `PRIMARY KEY AUTO_INCREMENT`, добавлены индексы для автора, получателя и polling по комнате. Миграция `2026_05_08_000006_fix_chats_author_and_id.sql` проверена повторным запуском. Чатовый UI теперь сбрасывает локальный `lastId` при смене локации через `window.PokemonGameState`, `data-location-id` и событие `pokemon:location-changed`.

Обновлено 2026-05-08: из `pokemon_moves_by_gen.xlsx` импортированы недостающие атаки по проверке имени, без изменения уже существующих атак. В живой `attac_power` добавлено 375 новых строк `atac_id=560..934`; существующая атака `559 Fusion Bolt` сохранена, служебные строки `997..999` оставлены в конце каталога. Общее количество атак стало 937. Описания и эффекты новых атак записаны на русском в UTF-8, миграции `2026_05_08_000004_import_missing_attacks.sql` и `2026_05_08_000005_resequence_imported_attacks.sql` идемпотентны и не создают дубли.

Обновлено 2026-05-08: клик по игрокам в панели локации получил валидацию авторизации, `user_id`, защиты от действий над самим собой и fallback-роут `/profile -> /game/profile`, чтобы старые ссылки на тренеркарты больше не уходили в Apache 404. Проверено smoke-запросами: `/profile?user=NIGA` отдает 302 на новый роут, `/game/profile?user=NIGA` отдает 200 для админ-сессии даже при включенных техработах.

Обновлено 2026-05-25: legacy-ссылка тренеркарты `/page.php?id=...`, которую открывал лидерборд на главной через `color_group_users()`, теперь совместимо редиректит на `/game/profile?id=...`; рейтинг на главной больше не генерирует `window.open('page.php...')`.

Обновлено 2026-05-08: legacy NPC переведены в новый JSON-flow без прямого исполнения старых PHP-файлов. Старые NPC теперь используются как справочник поведения: покецентр, Покемаркет, куратор, транспорт, стадион, секретарь, Билли и квестовые NPC открывают новые диалоги/маршруты. Дополнительно перенесены основные действия профессора Оука, прохожего, Спайка, Стива, Кэрол, исследователя, крафт эволюционных камней и проверка репутации у секретаря. Smoke по ключевым legacy-комбинациям NPC прошел без ошибок.

Обновлено 2026-05-08: старые турнирные файлы `admin/info_tur.php`, `admin/info_tur_user.php`, `admin/medal.php` отсутствуют в проекте, поэтому начат новый модуль с нуля. Добавлены таблицы `admin_tournaments`, `admin_tournament_participants`, `admin_medals`, `admin_user_medals`, API `/api/admin/tournaments`, `/api/admin/medals`, CRUD в админке, участники турниров и выдача медалей игрокам.

Обновлено 2026-05-08: `/game/admin` расширен до новой админпанели v1. Добавлены разделы дашборда, пользователей, предметов, Покемаркета, дропа, локаций, покемонов игроков, атак, новостей, модерации, системных настроек и legacy-карты. API работает через `/api/admin/*`, POST/DELETE защищены CSRF, опасные удаления пишут снимок строки в `admin_audit_log`.

Обновлено 2026-05-08: добавлена таблица `site_settings` и чтение `techwork` из БД. Добавлены индексы для поиска пользователей, предметов, магазина и аудита. Миграция `2026_05_08_000002_admin_panel_v1_foundation.sql` проверена повторным запуском.

Обновлено 2026-05-07: `/game/market/items` получил отдельный shop-дизайн в формате полок товаров и правой корзины "Покупки"; вход добавлен в нижнее меню игры как "Покемаркет". Каталог расширен базовыми конфетами и энергетиком через сид-миграцию.

Обновлено 2026-05-07: из legacy `include/rooms/npc/shop.php` перенесен старый список Покемаркета: Покебол 250, Энергетик 500, Фонарик 500000, Каменная Кирка 350000, Пропуск на Электростанцию 125000. Для кирки и пропуска сохранен старый лимит: нельзя купить второй экземпляр, пока один уже есть в инвентаре.

Обновлено 2026-05-06: `/game/market/items` переведен из заглушки магазина тренировок в отдельный Покемаркет. Добавлены `/api/market/items`, `/api/market/items/buy`, таблица `market_shop_items`, системный каталог товаров и чтение legacy-лотов `auction_items`. Покупка проверяет CSRF, авторизацию, баланс, остатки, регион, чужие приватные лоты и списывает/выдает предметы через `InventoryRepository`. Также исправлен учет дублей в `items_users`: баланс теперь считается суммой активных строк, а списание проходит по нескольким строкам.

Обновлено: 2026-05-26.

Этот файл фиксирует реальное состояние нового слоя игры. Старые файлы можно читать как источник правил, но новые функции пишем через контроллеры, репозитории, JSON API, CSRF, PDO и UTF-8.

## Оценка готовности

Оценка на 2026-05-26: проект уже ближе к рабочей закрытой alpha/ранней beta нового слоя. Главная игра открывается через `/game`, основные JSON API подключены, PvE/PvP имеют общий боевой слой, ловля/награды/ack-end покрыты smoke, PvP прогнан двумя сессиями, подарки и held items работают через серверные правила, Trainer Card показывает gym badges, яйца и breeding-flow вынесены в новый API, Комиссионная лавка получила рабочий v1, а админка стала рабочим Game Master Center. До открытой beta остаются не “первые включения”, а долгие edge cases, UX-долги, редкие legacy NPC/квесты, CI/regression, production logging и фоновая обработка истекающих сущностей.

Итоговая готовность:

| Уровень | Готовность | Почему |
|---|---:|---|
| Dev-сборка для активной разработки | 84% | Основные экраны/API живые, есть подготовленные QA-аккаунты, транзакционные smoke-скрипты, Комиссионная лавка v1 и browser-regression по ключевым боевым/инвентарным сценариям. |
| Закрытая alpha для 1-3 доверенных тестеров | 74% | Можно гонять карту, чат, NPC, инвентарь, PvE/PvP, яйца, breeding, рынок/лавку, подарки и Trainer Card; нужен контроль логов и быстрый retest багов. |
| Открытая beta | 62% | Закрыты PvP timeout/accept/history/anti-double-click, PvE catch/finish/ack, временные предметы, gift-box, held items, базовая админка и лавка v1; остаются редкие legacy-правила, UX и единый regression. |
| Публичный production-релиз | 37% | Нужны production replay/audit боёв, CI, migration status, cleanup jobs, права по ролям, фоновые jobs лавки/рейсов/ивентов и полный ручной прогон долгих edge cases. |

Готовность по крупным блокам:

| Блок | Оценка | Текущее состояние |
|---|---:|---|
| Авторизация, сессия, техработы | 72% | Регистрация без обязательной почты и password-reset подготовлены; `site_settings.techwork` работает, но нужен production SMTP/OSP smoke. |
| `/game`, карта, переходы, игроки на локации | 65% | Shell работает, popup игроков валидируется, Trainer Card открывается модально; сложные условия проходов и редкие legacy-правила ещё переносить. |
| Чат и модерация | 78% | Схема чата исправлена, каналы работают, мут/бан/warn и личные системные уведомления добавлены; UX, антиспам и журнал модерации ещё улучшать. |
| Друзья и социальные действия | 78% | Заявки, принятие, удаление и статусы работают; для beta нужен ещё один ручной прогон с двумя браузерами после долгой сессии. |
| PvE бой | 70% | Старт, атаки, статусы, погода, ловля, награды, finish/ack и Primal/Mega smoke закрыты; остаются редкие атаки, все предметы и долгие edge cases. |
| PvP бой | 72% | Invite/reject/timeout/accept/refresh/anti-double-click/history/items/surrender smoke прошёл `126/126`; до релиза нужны долгие реальные 6x6 бои и replay/audit. |
| Инвентарь и предметная логика | 72% | Категории, поиск, временные предметы, gift-box, held equip/unequip и основные battle effects работают; редкие consumables/evolution/TM ещё требуют полного QA. |
| Покемоны, питомник и breeding | 70% | Активная команда отделена от питомника, held icons видны, breeding-flow через буквы/Extract и `/api/eggs` проверен; нужен UX-полиш питомника. |
| Покемаркет и экономика | 75% | Магазин предметов, legacy-compat рынок покемонов и новая Комиссионная лавка v1 работают; нужен cron истечения лотов, админский UI настроек и длинный двухоконный market-regression. |
| Покедекс и атакадекс | 68% | Поиск и карточки есть, импорт атак расширен, формы/Primal/Mega добавлены; нужны полная проверка 937 атак, эволюций, hidden moves и missing assets. |
| NPC и квесты | 62% | Основные NPC переписаны, quest journal читает `quest_definitions/quest_steps`, rewards идут через общий flow; редкие event-NPC и сюжетные ветки ещё переносить. |
| Trainer Card и gym badges | 75% | `/api/profile/card` отдаёт UID/avatar/rank/clan/team/gifts/badges, gym badges рендерятся и выдаются один раз; автоматическую выдачу за gym-лидера ещё привязать. |
| Админка/Game Master Center | 60% | Большой рабочий каркас есть: пользователи, предметы, выдачи, дроп, локации, боссы, ивенты, модерация, турниры; UX/forms/roles/audit coverage ещё `PARTIAL_NEW`. |
| Дроп, ивенты, бусты | 58% | Глобальные/персональные бусты и admin drop rules есть, PvE учитывает множители; нужны тест-калькулятор, UI-видимость и массовый QA дропа. |
| Почта/сообщения | 62% | Отправка, входящие/исходящие, read/unread и архив поверх `sends` подключены; нужны диалоги/thread UX, restore и двухоконный прогон. |
| Транспорт | 60% | Самолёт/пароход/рейсы/билет/15-минутный полёт подключены; нужен редактор маршрутов, закрытые локации и полный региональный QA. |
| Турниры и медали | 45% | Таблицы и CRUD начаты с нуля; игровой сценарий турнира, сетка, награды и отображение игрокам ещё не закрыты. |
| Комиссионная лавка | 55% | `/game/commission` и `/api/commission/*` реализованы для item/pokemon/egg: резерв, покупка, отмена, история, комиссия, safe storage, логи, запрет зелий/ягод и smoke `24/24`; остались cron expire, полный админский UI и валютные объекты. |

Минимум до закрытой alpha:

- Держать обязательный smoke-набор: `tools/http_smoke.php`, `tools/pvp_qa_smoke.php`, `tools/legacy_core_qa_smoke.php`, `tools/breeding_qa_smoke.php`.
- Прогнать руками `/game`, карту, чат, NPC, инвентарь, PvE catch/finish/ack, PvP invite/accept/refresh, подарки, breeding, яйца и Trainer Card.
- Проверить техработы обычным игроком, модератором и админом.
- Пройти два-три квеста целиком с начислением наград и push-уведомлением.
- Проверить выдачу предмета, wild slot, правило дропа, мут/бан и аудит в админке.
- Убрать/не пушить временные картинки, прототипы и локальные скрины из рабочего дерева.

Минимум до открытой beta:

- Довести PvP до длинных реальных 6x6 боёв с replay/audit и ручным восстановлением после refresh.
- Довести инвентарь: все расходники, TM, evolution items, редкие held effects, квестовый дроп и массовый UX.
- Завершить почту: диалоги/thread UX, restore из архива и кнопка из popup игрока.
- Довести админку до стабильного Game Master Center: роли, валидации, формы редактирования, полное audit coverage.
- Довести Комиссионную лавку: cron истечения лотов, админский UI настроек, импорт/миграция старых активных лотов и длинный market-regression.
- Поднять CI/API-smoke/browser-regression для обычного игрока, модератора, админа и двух игроков одновременно.

## Оставшиеся большие системы

- `Комиссионная лавка` - `PARTIAL_NEW`: общий рынок предметов, покемонов и яиц уже работает через `market_lots`; дальше нужны cron expire, админский UI настроек, валютные объекты, модерация лотов и длинный regression на реальных лотах.
- `Unified market` - старые `auction_items` и `rinok_poke` импортируются лениво в `market_lots`, но нужен отдельный one-shot importer/отчёт и режим read-only для старых таблиц после стабилизации.
- `Редкие NPC и event-NPC` - перенести оставшиеся сюжетные ветки, сезонные действия и тонкие условия проходов из legacy PHP в сервисы/quest definitions.
- `Кланы` - сейчас маршрут в `GameRoutes` остаётся `todo`; нужны API, роли, чат/рейтинги/взносы и UI.
- `Турниры` - CRUD есть, но нужна игровая сетка, регистрация, матчмейкинг, награды и отображение результата в профиле.
- `Production battle logging/replay` - хранить полный state раундов PvE/PvP/Boss для восстановления, спорных ситуаций и QA-replay.
- `Health Dashboard` - админский мониторинг миграций, API-smoke, последних 500, очередей/cron, ошибок и состояния БД.
- `Cron/jobs` - истечение лотов, рейсов, событий, временных предметов, expired mails/storage, cleanup battle locks.
- `Permission matrix` - явная таблица прав игрок/модератор/admin/game master по каждому `/api/admin/*` и опасному действию.
- `QA matrix` - отдельный документ/скрипт сценариев Tacos/NIGA/admin, чтобы regression не жил только в чате.
- `Safe storage` - единый системный склад для возвратов предметов/яиц/покемонов при ошибке инвентаря, отмене лота, истечении или rollback.
- `Migration runner/status` - таблица применённых миграций и команда проверки drift между файлом и живой БД.
- `Asset registry checker` - проверка отсутствующих item/pokemon sprites до запуска QA.
- `Production dump checklist` - что чистить, что оставлять, какие тестовые аккаунты и публичные данные не удалять.

## Статусы

- `DONE_NEW` - новый слой написан и проверен на реальных сценариях.
- `PARTIAL_NEW` - новый слой есть, но поведение еще не закрыто полностью.
- `LEGACY_COMPAT` - старый код оставлен как совместимость или справочник.
- `TODO_REWRITE` - нужно переписать.
- `BROKEN` - найдено и пока не исправлено.

## Роуты и API

| Блок | Статус | Комментарий |
|---|---:|---|
| `public/index.php` | `DONE_NEW` | Front controller, основные страницы и API подключены. |
| `/game` | `PARTIAL_NEW` | Новый игровой shell работает без frameset, часть UI еще стабилизируется. |
| `/game/messages` | `PARTIAL_NEW` | Входящие/исходящие/архив работают поверх `sends`; отправка, read/unread и soft-archive подключены, диалоги еще улучшать. |
| `/api/game/state` | `PARTIAL_NEW` | Возвращает пользователя, локацию, переходы, NPC, игроков, PvE-состояние. |
| `/api/map/move` | `PARTIAL_NEW` | Базовые переходы работают, сложные legacy-условия еще переносить. |
| `/api/location/npc`, `/api/location/npc/action` | `PARTIAL_NEW` | Legacy NPC переписаны на новый JSON-flow без прямого include старых файлов; работают Покецентр/питомник, Покемаркет, транспорт, касса, куратор, стадион, секретарь, Билли, стартовый квест Оука, Спайк, Стив, Кэрол, исследователь и крафт камней. Остались редкие event-NPC и тонкие сюжетные ветки. |
| `/api/chat/messages` | `PARTIAL_NEW` | Общий/торг/бой/клан/приват работают через новый API, нужна дальнейшая чистка UX и модерации. |
| `/api/friends/*` | `DONE_NEW` | Заявки, принятие, удаление, статус отношений и входящие уведомления реализованы. |
| `/api/battle/pve/*` | `PARTIAL_NEW` | Бой работает; добавлены базовые статусы, ловушки и ловля, но предметы/редкие атаки еще расширять. |
| `/api/battle/pvp/*` | `PARTIAL_NEW` | Invite/reject/timeout/accept/refresh/anti-double-click/switch/items/history/surrender покрыты `tools/pvp_qa_smoke.php` `126/126`; до релиза нужны длинные реальные 6x6 и replay/audit. |
| `/api/battle/history` | `PARTIAL_NEW` | Отдает последние PvE/PvP бои игрока с результатом, соперником, датой и логом. |
| `/api/bosses/start` | `PARTIAL_NEW` | Запускает локационного босса 6 на 6 через текущий PvE UI; победа засчитывается только после всей команды босса. |
| `/api/inventory/*` | `PARTIAL_NEW` | Инвентарь, категории, поиск, временные предметы, target-use, held equip/unequip и боевые предметы подключены; полный набор редких расходников ещё требует QA. |
| `/api/inventory/open-gift` | `DONE_NEW` | Gift-box открывается сервером через loot table, начисляет reward-flow/push-log и не списывает предмет при ошибке. |
| `/api/eggs` | `DONE_NEW` | Новый JSON API для яиц: список, инкубация через `90311 Инкубатор`, вылупление в питомник со стартовой атакой; покрыт `legacy_core_qa_smoke` и `breeding_qa_smoke`. |
| `/api/pokemon/breeding/*` | `DONE_NEW` | Новый breeding-flow: буква совместимости, normal/genderless/Ditto/Extract правила, parent metadata, egg attack inheritance, UI в `/game/pokemon`. |
| `/api/market/pokemon` | `DONE_NEW` | Новый JSON API для рынка покемонов поверх `rinok_poke`: list/buy/cancel/private lot/ownership transfer/battle lock/active pokemon protection покрыты smoke; таблица остаётся `LEGACY_COMPAT`. |
| `/api/pokemon/*` | `PARTIAL_NEW` | Команда, атаки и лечение есть, UI/кодировку еще чистить. |
| `/api/pokemon/training` | `PARTIAL_NEW` | Набор тренировки/ослабления применяются к активному покемону, тратят предметы и обновляют стадии. |
| `/api/notifications` | `DONE_NEW` | Отдает новые игровые пуш-уведомления о наградах и помечает их прочитанными. |
| `/api/profile/card` | `DONE_NEW` | Trainer Card отдаёт UID/avatar/rank/clan/activeTeam/gifts/badges/gymBadges; модальное окно рендерит held item и gym badges на текущей странице. |
| `/api/dex/*` | `PARTIAL_NEW` | Покедекс/атакадекс есть, требуется финальная проверка полного списка атак и данных. |
| `/api/shop/training/buy` | `PARTIAL_NEW` | Покупка наборов тренировки/ослабления за алмазы или монеты. |
| `/api/transport/*` | `PARTIAL_NEW` | Старые transport_routes для парохода/моментальных рейсов сохранены; добавлены рейсы самолёта по билету из любой локации, активный полёт 15 минут, статус проводника и выход после прибытия. |
| `/game/admin`, `/api/admin/*` | `PARTIAL_NEW` | Новая админка v1: дашборд, пользователи, предметы, Покемаркет, дроп, локации, покемоны, атаки, новости, модерация, настройки и аудит. Нужна ручная UX-дошлифовка и расширение тонких legacy-правил. |
| `/game/commission`, `/api/commission/*` | `PARTIAL_NEW` | Комиссионная лавка v1: список/поиск/категории/сортировка, sellable, выставление item/pokemon/egg, покупка, снятие, история, safe-return storage, комиссия 5%, admin settings API; валютные объекты и cron expire ещё довести. |

## База данных

| Таблица | Статус | Комментарий |
|---|---:|---|
| `chats` | `DONE_NEW` | Есть `author_id`, `PRIMARY KEY AUTO_INCREMENT` на `id`, индексы автора/получателя/polling; новый чат API проверен на чтении реальных сообщений. |
| `sends` | `PARTIAL_NEW` | Legacy-источник писем сохранен; новая страница читает входящие/исходящие и пишет новые письма. Состояние read/archive вынесено в `mail_message_state`. |
| `friends` | `DONE_NEW` | Добавлены `AUTO_INCREMENT`, primary key, unique pair, reverse index. |
| `friends_zayv` | `DONE_NEW` | Добавлены `AUTO_INCREMENT`, primary key, unique request pair, incoming-request index. |
| `quest` | `DONE_NEW` | Добавлены `PRIMARY KEY AUTO_INCREMENT` на `id`, индексы `(user_id, quest_id)` и `(quest_id, gotov, process)`; новые NPC-квесты больше не вставляют строки с пустым id. |
| `quest_definitions`, `quest_steps` | `PARTIAL_NEW` | Новый слой описаний квестов: зависимости, многоэтапность, reward JSON и сиды для стартовых/Билли/ежедневных квестов. |
| `pok_user` | `PARTIAL_NEW` | Для ловли новый `id` задается явно, потому что в живой БД не было `AUTO_INCREMENT`. |
| `pok_user.training_*` | `PARTIAL_NEW` | Хранит стадию тренировки, выбранный стат, именной эффект и признак приручения. |
| `attac_my_poke` | `PARTIAL_NEW` | Новые вставки указывают `id`; пойманным покемонам создаются стартовые атаки. |
| `items` / `items_users` | `PARTIAL_NEW` | Инвентарь укреплён: временные предметы фильтруются, gift-box/held/evolution/vitamin metadata подключены, `items_users.id=0` отсутствует; редкие эффекты ещё требуют QA. |
| `item_gameplay_metadata` | `DONE_NEW` | 66 held/lore items `78..520` имеют ru/en alias, категорию, target-use, battle/equip flags, effect status и совместимость; `todo/visual_only` явно отмечают неполные эффекты. |
| `item_gift_loot` | `DONE_NEW` | Loot table для уникального подарка и тестовых ящиков: фиксированные/случайные награды, шанс, количество, транзакционный rollback и push-log проверены smoke. |
| `gym_badges`, `user_gym_badges` | `PARTIAL_NEW` | API/reward-flow готов: `gym_badge` выдаётся один раз, хранит `issued_at`, лидера, локацию, источник battle/quest и рендерится в Trainer Card. Автоматическую выдачу после gym-боя ещё надо привязать к конкретным лидерам. |
| `eggs` | `DONE_NEW` | Таблица яиц читается новым `/api/eggs`; breeding пишет parent metadata, инкубация режет таймер, вылупление создаёт `pok_user`, стартовую атаку и отправляет покемона в питомник. |
| `pokemon_breeding_rules`, `pokemon_breeding_requests` | `DONE_NEW` | Новый breeding-flow: compatibility letter, genderless/Ditto/Extract правила, request/response status, связь с созданным яйцом. |
| `rinok_poke` | `LEGACY_COMPAT` | Legacy-рынок покемонов обслуживается новым `/api/market/pokemon` с транзакциями покупки/снятия/выставления; в будущем заменяется комиссионкой. |
| `market_lots`, `market_logs`, `market_return_storage` | `PARTIAL_NEW` | Таблицы Комиссионной лавки v1 применены: безопасный резерв, статусы active/sold/cancelled/expired, комиссия, логи, legacy source links и склад возврата; нужен фоновый expire-job и UI просмотра склада. |
| `battles`, `battle_log` | `PARTIAL_NEW` | PvE использует новые репозитории, логирование еще нужно довести для PvP/истории. |
| `battle_dop` | `PARTIAL_NEW` | Добавлены primary key и индекс `(battleid, pokeid)`; используется для полевых ловушек, временных боевых эффектов, запрета смены, side-field и погоды. |
| `location_bosses`, `location_boss_pokemon`, `location_boss_drops`, `boss_battle_sessions` | `PARTIAL_NEW` | Локационные и ивентовые боссы: настройка команды до 6 покемонов, окна появления, дропа и активной boss-сессии боя. |
| `pvp_requests` | `DONE_NEW` | Новая таблица заявок на PvP с индексами входящих/исходящих вызовов, пары игроков, связанного боя, `expires_at` и `responded_at`. |
| `mail_message_state` | `DONE_NEW` | Soft-state для legacy-писем: папка, read/archive/delete timestamp без физического удаления из `sends`. |
| `users.karma_score`, `karma_events` | `DONE_NEW` | Новая система кармы: явный счет репутации, лог изменений и мост для старых групп 7/10 как плохой репутации. |
| `transport_routes` | `PARTIAL_NEW` | Таблица старых/моментальных рейсов между регионами: тип транспорта, откуда/куда, предмет-иконка, цена, включенность. |
| `transport_flight_routes`, `transport_flights` | `PARTIAL_NEW` | Рейсы самолёта из любой локации в 3 региона и активное состояние полёта с таймером, локацией борта, прибытием и выходом. |
| `admin_drop_rules` | `PARTIAL_NEW` | Новая таблица настраиваемого дропа: предмет, локация, покемон/слот, шанс, количество, время, квестовые условия. |
| `game_notifications` | `DONE_NEW` | Пуш-уведомления в игре для наград и системных событий; `/api/notifications` отдает новые записи и помечает их прочитанными. |
| `game_event_boosts`, `player_boosts` | `PARTIAL_NEW` | Основа глобальных x2/x4 и персональных предметных бустов; PvE уже учитывает множители опыта, монет и дропа. |
| `admin_tournaments`, `admin_tournament_participants` | `PARTIAL_NEW` | Новый модуль турниров с нуля: расписание, статус, взнос, арена, куратор, уровни и участники. |
| `admin_medals`, `admin_user_medals` | `PARTIAL_NEW` | Новый каталог медалей и выдача медалей игрокам с привязкой к турниру. |
| `admin_audit_log` | `PARTIAL_NEW` | Логирует действия новой админки: предметы и правила дропа. |
| `moderation_punishments` | `DONE_NEW` | Хранит муты, баны, предупреждения, сроки, причины, модератора и снятие наказаний; используется админкой и чат-командами. |
| `site_settings` | `DONE_NEW` | Хранит системные флаги, сейчас используется для `techwork`. |

## Уже сделано

### Чат

- Сообщения грузятся только с момента входа клиента.
- Есть очистка чата на клиенте и автопрокрутка.
- Есть общий, торговый, боевой, клановый и приватный каналы.
- Можно отвечать игроку в общий чат без приватного режима.
- Сообщения, адресованные текущему игроку, подсвечиваются.
- HTML в сообщениях выводится через `textContent`, XSS не должен исполняться.
- Схема `chats` нормализована: `author_id`, автоинкрементный `id`, индексы под polling и приватные сообщения.
- При смене локации клиент сбрасывает локальный `lastId`, очищает видимый лог и начинает синхронизацию новой комнаты без старого хвоста.
- Модераторы могут выдавать наказания командами `/mute`, `/unmute`, `/ban`, `/unban`, `/warn`; активный мут блокирует отправку обычных сообщений с понятной ошибкой.

Статус: `DONE_NEW`.

### Сообщения

- `/game/messages` больше не показывает только `todo`.
- Страница читает входящие, исходящие и архив из legacy-таблицы `sends`.
- Отправка письма работает через `/api/messages/send` по нику или ID.
- Read/unread работает через `/api/messages/read` и больше не зависит только от старого поля `sends.active`.
- Удаление в UI стало мягким архивом через `mail_message_state`; физически строки из `sends` не удаляются.
- Тема и текст очищаются от HTML и экранируются перед выводом.
- Не реализованы полностью: полноценные диалоги/thread UX, restore из архива и browser-regression двумя игроками.

Статус: `PARTIAL_NEW`.

### Игроки и друзья

- Меню игрока по клику в панели игроков работает.
- Меню проверяет авторизацию, наличие `player_id`, не показывает боевые/социальные действия при клике на самого себя и не открывает пустые/битые ссылки.
- Старые ссылки `/profile?user=...` перенаправляются на `/game/profile?user=...`, поэтому клик по нику больше не должен приводить к Apache 404.
- Кнопка дружбы меняется по состоянию: добавить, принять заявку, заявка отправлена, удалить из друзей.
- Входящие заявки опрашиваются клиентом и показываются toast-уведомлением.
- API защищен авторизацией и CSRF для POST.
- Проверен сценарий на реальной БД: заявка -> входящий статус -> принятие -> дружба -> удаление; профиль NIGA через новый роут отдает 200.

Статус: `DONE_NEW`.

### PvE бой

- Бой запускается и обновляется через JSON API.
- Лог раундов отдается в UI.
- Базовые атаки, PP, точность, урон, часть статусов и бафов подключены.
- Картинки покемонов в бою подключаются через новый UI.
- После завершения боя API больше не отдает пустой список атак, чтобы UI не показывал четыре `Нет атаки`.
- Ловля дикого покемона чинит `pok_user` и создает строку `attac_my_poke`.
- Проверены данные эффектов: Fire Punch дает шанс ожога, Leech Seed связан со статусом пиявок.
- Добавлены эффекты начала хода: яд, ожог, сон, заморозка, паралич, страх, спутанность, пиявки, проклятие.
- Добавлены специальные эффекты: Recover/Softboiled, Heal Pulse, Rest, Belly Drum, Selfdestruct/Explosion.
- Добавлено хранение и срабатывание ловушек: Spikes, Toxic Spikes, Stealth Rock.
- Добавлен каталог боевых эффектов атак: яд/тяжелый яд Toxic/ожог/сон/паралич/заморозка, шипы/паутина/камни/стальные шипы, удерживающие ловушки, отдача, crash-урон Jump Kick/High Jump Kick, погода, Nightmare/Perish Song/Destiny Bond/Taunt/Encore/Torment/Disable/Knock Off.
- Погода влияет на бой: солнце усиливает Fire и режет Water, дождь усиливает Water и режет Fire, Thunder/Hurricane получают точность в дождь/солнце, Blizzard получает точность в град, Weather Ball меняет тип/силу.
- Статусы разделены по боевой модели: stable-статусы взаимоисключающие, учитывают иммунитеты типов и misty/electric terrain; volatile-эффекты могут жить параллельно и часть из них снимается при смене покемона.
- Toxic теперь прогрессирует по раундам `1/16 -> 2/16 -> 3/16...`; ожог тикает `1/16`, режет физическую атаку, паралич режет скорость до `25%`.
- Entry hazards поддерживают слои Spikes `1/8 -> 1/6 -> 1/4`, два слоя Toxic Spikes дают сильное отравление, Stealth Rock считает урон от типа Rock.
- Добавлены battlefield-эффекты: Reflect, Light Screen, Electric/Misty/Grassy Terrain, Trick Room, Wonder Room, Magic Room; Grassy Terrain лечит, комнаты и экраны влияют на расчет боя.
- Добавлена динамическая погода и синергия способностей: авто-погода при входе (`Морось`, `Засуха`, песок/град), `Штиль` сбрасывает погоду, погодные камни продлевают эффект до 10 раундов.
- Способности в погоде учитываются в статах, уроне и тиках раунда: `Водоплавающий`, `Хлорофилл`, `Широкие лапы`, `Скольжение`, `Песочник`, `Дождефаг`, `Ледяное тело`, `Сухая кожа`, `Солнечная батарея`, `Лиственный щит`, `Метеочувствительность`.
- Для способностей добавлены nullable-колонки `ability_key` в `pok_user`, `poke_base`, `pok_pve`, `pok_nps`; старые покемоны без способности работают как раньше.
- В БД добавлена миграция `2026_05_06_000002_enrich_battle_move_effects.sql`, которая дополняет legacy-атаки статусами и шансами эффектов.
- В БД добавлена миграция `2026_05_06_000003_add_pokemon_abilities.sql` для хранения способностей.
- Проверено по списку эффектов: в живой `attac_power` есть 71 атака из переданного списка; отсутствующие современные атаки покрыты PHP-каталогом по имени и заработают после добавления самих атак в БД.

Статус: `PARTIAL_NEW`.

### PvP бой

- Игрок может вызвать другого игрока на бой из popup-меню в панели игроков.
- Добровольный вызов на бой через ник игрока не требует ордера и доступен везде, если оба игрока свободны и выбрали покемона.
- Карма и ордер Команды R оставлены для принудительного нападения отдельным правилом.
- Входящие PvP-вызовы опрашиваются клиентом и показываются toast-уведомлением.
- Если у текущего игрока есть входящий вызов от выбранного игрока, кнопка в меню меняется на `Принять бой`.
- При вызове/принятии PvP открывается модальное окно выбора живого активного покемона; выбор первого игрока сохраняется в `pvp_requests`.
- После принятия создается запись `battles` с `batl_tip = pvp`, обоим игрокам ставится `pvp = 1`, активные покемоны берутся из `pok_user`.
- PvP состояние отдается через боевой API: UI показывает `PvP бой против ...`, покемона соперника, ожидание второго игрока и лог раундов.
- Атаки, PP, порядок хода по скорости, урон, статусы/бафы и завершение боя используют общий `BattleEngineService`.
- Смена покемона в PvP занимает ход: соперник бьет уже нового активного покемона, если выбрал атаку.
- PvP-заявки имеют срок жизни 120 секунд; `status/requests` лениво помечают просроченные вызовы как `expired`.
- Отказ от заявки ставит `declined`, не создает бой и отдает понятный ответ клиенту.
- Повторный accept заявки идемпотентен: если бой уже создан, API возвращает существующий `battle_id`.
- Повторный выбор действия в раунде возвращает текущее состояние с сообщением `Ход уже выбран`.
- В PvP для beta разрешены только боевые предметы `battleuse=1`, покеболы заблокированы; предмет занимает ход.
- `/api/battle/history` отдает последние PvE/PvP бои игрока с результатом, соперником и логом.
- `ack-end` очищает PvP-состояние игрока после просмотра результата.
- Проверено транзакционным smoke на Tacos/NIGA: заявка -> incoming/outgoing статус -> принятие -> создание боя -> два выбранных хода -> переход на следующий раунд; изменения откатились rollback.

Статус: `PARTIAL_NEW`.

### Карма

- Добавлен `users.karma_score` и таблица `karma_events`.
- Карма стартует с `0`; статус `Защитник` начинается от `+10`, статус `Преступник` начинается от `-10`.
- Старые группы `7` и `10` считаются преступниками; миграция выставляет им `karma_score = -100`, если значение было нулевым.
- Добавлены служебные предметы: `90001` Ордер Команды R I, `90002` Ордер Команды R II, `90003` Ордер протекции.
- В опасной локации нейтральное нападение требует ордер Команды R и минимум `400` очков репутации (`rang_a`).
- Ордер I позволяет атаковать тренера выше ранга `Начинающий`, если его популярность (`rang_b`) не ниже `30%` от популярности атакующего.
- Ордер II позволяет атаковать любого тренера выше ранга `Начинающий`.
- Ордер списывается только для принудительного нападения Команды R. Добровольный PvP через заявку ордер не требует.
- Нападение с ордером на нейтрального игрока дает атакующему `-1` кармы.
- Нападение на преступника дает атакующему `+1` кармы.
- Защитник (`karma >= 10`) может нападать на преступника в любой безопасной/опасной локации без ордера.
- Преступник (`karma <= -10`) может нападать на защитника без ордера, кроме закрытых административных локаций.
- Стадионы, арены, турниры, аукционы, тюрьмы и административные зоны полностью запрещают принудительные нападения.
- Профиль показывает статус и счет кармы.

Статус: `DONE_NEW`.

### Инвентарь

- `/api/inventory/page` принимает `category`, `q`, `page` и фильтрует данные на сервере.
- В overlay добавлены категории: все, покеболы, ТМ, яйца, эволюция, расходники, квестовые, дроп.
- Поиск инвентаря ищет по ID строки, item_id, названию и описанию предмета.
- Просроченные временные предметы с `items_users.dattimer` больше не отображаются, не экипируются и не используются.
- Target-use на покемона читает `item_target_rules` и не списывает предмет, если эффект не реализован.
- Для beta подключены эффекты `exp_candy`, `pp_vitamin`, `boost_exp`, `boost_drop`, `boost_money`, а тренировка/ослабление идут через `TrainingRepository`.
- Боевые предметы в PvE/PvP читаются из активного инвентаря; покеболы в PvP заблокированы.
- Не закрыто полностью: ТМ-обучение, яйца, все редкие расходники и удобный UX применения больших пачек.

Статус: `PARTIAL_NEW`.

### Покемоны

- Плитка команды и детали покемона есть.
- Лечение активных покемонов восстанавливает HP и PP.
- Редактор атак работает через новый API.
- В редакторе атак добавлен поиск по доступным атакам покемона: название, тип, ID и уровень.
- Вставка первой строки `attac_my_poke` учитывает ручной `id`.
- Добавлена система тренировок: набор тренировки повышает стадию с шансами League-17, выбирает случайный стат кроме HP и переносит бонус на новый стат; набор ослабления понижает стадию на 1, сохраняет стат и приручает монстра.
- Бонус тренировки применяется в бою к выбранному стату без перезаписи базовых значений покемона.
- Именная тренировка хранит случайный доп. эффект и дает 5% шанс наложить его при damaging-атаке.
- В `/game/diamond-shop` и `/game/market/items` добавлена покупка наборов тренировки/ослабления: 10 алмазов или 500000 монет за предмет.

Статус: `PARTIAL_NEW`.

### Dex

- Покедекс и атакадекс открываются в overlay.
- Атакадекс использует иконки типов.
- Поиск атакадекса ищет по названию, типу, описанию, ID, категории и компактному названию без пробелов/дефисов.
- В живой БД проверено 562 атаки, максимум `atac_id = 999`; поиск не ограничен первыми 100 строками.
- В карточке покемона добавлены способы эволюции: уровень, предметы эволюции и особые условия. Иконки предметов берутся через `public/img/items/index.json`.

Статус: `PARTIAL_NEW`.

### Транспорт

- Добавлен новый раздел `/game/transport`.
- Добавлены JSON API `/api/transport/routes` и `/api/transport/travel`.
- Рейсы берутся из `transport_routes`, показывают название предмета и его фото.
- Поездка проверяет занятость игрока, текущую локацию, запрет административных локаций и списывает цену.
- Добавлены первые маршруты: пароход между портами и самолет между региональными точками.

Статус: `PARTIAL_NEW`.

### Админка

- `/game/admin` больше не ведет в legacy-include, а открывает новый визуальный shell в стиле текущей игры.
- Доступ закрыт проверкой `users.groups = 1` и `information_users.admins_panels = 1`.
- Добавлен `/api/admin/items`: поиск предметов, создание и обновление предметов без ручного SQL.
- Добавлен `/api/admin/drop-rules`: правила дропа предметов по локации, конкретному wild-слоту, базовому покемону, времени, шансу и количеству.
- Добавлен `/api/admin/dashboard`: обзор счетчиков, онлайн-игроков, последних боев, настроек и аудита.
- Добавлен `/api/admin/lookups?type=&q=`: быстрый lookup игроков, предметов, атак, базовых покемонов, локаций и wild-слотов для форм без ручного ввода ID.
- Добавлен `/api/admin/users`: поиск и правка группы, активации, админ-доступа, локации, кармы, репутации и PvE/PvP-состояния.
- Добавлены админские действия выдачи предметов, бан/разбан IP, управление товарами Покемаркета, локациями, покемонами игроков, атаками, привязками level-up/egg attacks и новостями.
- Добавлены разделы `Турниры` и `Медали`: старого исходника нет, поэтому модуль пишет новые таблицы и дает CRUD турниров, управление участниками и выдачу медалей.
- Добавлены `/api/admin/moderation`, `/api/admin/audit`, `/api/admin/settings`; `techwork` теперь читается из `site_settings`.
- Раздел `Модерация` теперь имеет форму наказаний: выбор игрока, действие, срок и причина; поддерживает мут/размут, бан/разбан и предупреждения.
- Добавлены разделы `Дикие слоты` и `Ивенты/бусты`: можно редактировать `pokebuild`, настраивать x2/x4 опыт/монеты/дроп/квестовые награды и хранить персональные бусты.
- Реальные удаления разрешены, но требуют CSRF, подтверждение `DELETE` для опасных сущностей и записывают payload удаляемой строки в `admin_audit_log`.
- PvE-награды читают включенные `admin_drop_rules` после победы и выдают предметы игроку, если выпал шанс.
- Добавлен `admin_audit_log` для фиксации действий администратора.
- Старые разделы админки отображаются как карта переноса; legacy-карта доступна через `/api/admin/legacy-map` и служит навигацией по тому, что уже перенесено и что еще остается источником правил.

Статус: `PARTIAL_NEW`.

## Критичные долги

- Запустить `Комиссионную лавку` как отдельный модуль: item/pokemon/egg/currency лоты, резерв объекта, комиссия, возвраты в safe storage, запрет зелий/ягод/quest/bound/temp объектов и защита от дюпа транзакциями.
- Объединить старые рынки в `Unified market`: `rinok_poke` и будущие item-lots оставить как `LEGACY_COMPAT` до миграции активных лотов.
- Дошлифовать админку до Game Master Center: lookup-select во всех формах, UX-валидации, роли moderator/admin/game master, Health Dashboard, audit по каждому опасному действию.
- Довести PvP/PvE до production-наблюдаемости: долгий 6x6 browser-regression, replay/audit state каждого раунда, восстановление спорных боёв и edge cases refresh.
- Завершить перенос редких NPC/event-NPC, квестовых веток, кланов и полноценных турниров.
- Закрыть редкие item effects: все evolution/TM/held/consumable эффекты, status-heal, weather-связки и type-boost предметы через QA-matrix.
- Поднять CI/regression: авторизованные API-smoke с CSRF для player/moderator/admin, browser-smoke desktop/mobile и двухсессионные Tacos/NIGA сценарии.
- Добавить `Migration runner/status`, `Asset registry checker`, `Safe storage` и production dump checklist.
- Вычистить оставшиеся mojibake-строки в legacy-комментариях и старых модулях.
- Разнести большой `views/game-start.php`, админский JS/CSS и остатки крупного frontend-кода на модули.

## Последние проверки

- 2026-05-26 commission-market-v1: добавлена Комиссионная лавка `/game/commission` и API `/api/commission/lots`, `/sellable`, `/my`, `/lots/buy`, `/lots/cancel`, `/api/admin/commission/settings`. Миграция `2026_05_26_000012_commission_market.sql` создала `market_lots`, `market_logs`, `market_return_storage` и настройки `commission.*` в `site_settings`. Проверено: PHP lint `CommissionMarketRepository.php`, `CommissionMarketApiController.php`, `GameModuleController.php`, `GameRoutes.php`, `views/game-commission.php`, `public/index.php`, `tools/http_smoke.php`, `tools/commission_market_smoke.php`; `tools/commission_market_smoke.php` — 24/24 OK; `tools/http_smoke.php --login=Tacos --password=jungheinrick` — 51/51 OK, включая `/api/commission/lots?q=Kyogre`, sellable/my и CSRF-negative `419`; browser render `/game/commission` показывает категории без зелий/ягод, вкладку `Мои лоты`, форму `Выставить`, no horizontal overflow.
- 2026-05-26 status-audit: `GameRoutes::MODULES` сверены как 26 модулей (`4 done`, `18 partial`, `4 todo`), таблицы API/БД в этом файле синхронизированы с текущими маршрутами `/api/eggs`, `/api/market/pokemon`, `/api/pokemon/breeding/*`, `/api/profile/card`, `/api/inventory/open-gift`, `/api/battle/pvp/*`, `/api/commission/*`; нижняя оценка готовности обновлена с состояния 2026-05-09 на 2026-05-26. Комиссионная лавка поднята до `PARTIAL_NEW`, а старые item/pokemon рынки помечены как legacy-compat слой до полного unified market.
- 2026-05-26 pvp-two-session-smoke: добавлен `tools/pvp_qa_smoke.php` для реального HTTP-прогона двух сессий `Tacos`/`NIGA`. Покрыто: invite, reject, timeout, accept, повторный accept, refresh-sync обеих сторон, anti-double-click для атаки и смены, смена покемона, прямой `action=ball` и покебол через `action=item` заблокированы в PvP, успешное применение временного PP-витамина в PvP без расхода запасов аккаунта, surrender/end-sync, повторная сдача после завершения без мутации лога, история боя и отсутствие PvE-наград в PvP history. Исправлены `HY093` в `BattleRepository` при decline/expire PvP-заявок и мутация завершенного PvP-боя в `BattleEngineService`: finished-state больше не переактивирует weather/log, switch/escape после конца возвращают текущее состояние. Проверки: `php -l` для `BattleEngineService.php`, `BattleRepository.php`, `tools/pvp_qa_smoke.php`; `tools/pvp_qa_smoke.php --password=jungheinrick` — 126/126 OK; `tools/http_smoke.php --login=Tacos --password=jungheinrick` — 37/37 OK.
- 2026-05-09 capture/report-fix: исправлена ловля PvE-покемонов. Шары `3`, `25`, `90004` разрешены в бою через `battleuse=1`; шар списывается сразу при попытке, Premium Ball получает повышенный шанс, Master Ball — максимальный шанс. Пойманный покемон создается в `pok_user`, получает атаки в `attac_my_poke`; если команда заполнена, резервные покемоны теперь тоже отдаются через `/api/pokemon/moves`, чтобы игрок видел питомник/резерв. PvE-предметы и смена покемона больше не дают бесплатный ход: дикий покемон отвечает после предмета/смены, если оба живы. `ack-end` чистит `bttle_status`; training train/weaken обернуты в транзакцию с `FOR UPDATE`.
- 2026-05-09 beta-block: PHP lint через `D:\OSPanel\modules\php\PHP_8.1\php.exe -l` для `InventoryRepository.php`, `MessageRepository.php`, `AdminRepository.php`, `BattleRepository.php`, `InventoryApiController.php`, `MessageApiController.php`, `AdminApiController.php`, `PvpBattleApiController.php`, `views/game-start.php`, `views/game-messages.php`, `public/index.php`; JS syntax `node --check public/js/admin-panel.js`; миграция `2026_05_09_000001_open_beta_foundation.sql` применена на MySQL `3308`, проверены поля `pvp_requests.expires_at/responded_at` и таблица `mail_message_state`; DB-smoke прошел для inventory count/category/search, mail inbox/sent/archive, admin lookup users/attacks; unauth web-smoke: `/api/admin/lookups` -> `403`, `/api/inventory/page` -> `401`, `/game/messages` -> `200` с редиректом/страницей доступа.
- PHP lint: `src/Game/BattleEngineService.php`, `src/Repository/BattleRepository.php`, `src/Repository/MessageRepository.php`, `src/Repository/TrainingRepository.php`, `src/Repository/InventoryRepository.php`, `src/Repository/PokemonRepository.php`, `src/Repository/DexRepository.php`, `src/Repository/AdminRepository.php`, `src/Controller/AdminApiController.php`, `src/Controller/GameModuleController.php`, `src/Controller/PokemonApiController.php`, `src/Controller/ShopApiController.php`, `src/Game/GameRoutes.php`, `src/Http/Request.php`, `src/Http/Router.php`, `views/game-module.php`, `views/game-messages.php`, `views/game-training-shop.php`, `views/game-pokemon.php`, `views/game-admin.php`, `public/index.php`.
- JS syntax: `public/js/player-menu.js`, `public/js/admin-panel.js`.
- DB schema check: `battle_dop` primary key и индекс `(battleid, pokeid)`, `pvp_requests` индексы входящих/исходящих/пары/боя, `site_settings`, `idx_users_login`, `idx_items_name`, `idx_admin_audit_created`, таблицы турниров и медалей.
- DB smoke: добавление ловушки, защита от дубля, чтение ловушек, Fire Punch -> burn, Leech Seed -> status 8, Toxic -> progressive poison, Spikes/Sticky Web hazards, Fire Spin -> partial trap, Sunny Day -> weather, Double-Edge -> recoil, Taunt blocks Thunder Wave, иммунитет Fire к Burn, запрет второго stable-статуса, 3 слоя Spikes, Trick Room order, Reflect damage reduction, weather ability synergy (`Морось`, `Водоплавающий`, `Дождефаг`, `Сухая кожа`, `Засуха`, `Солнечная батарея`, `Лиственный щит`, `Песочник`, `Метеочувствительность`), чтение inbox, PvP заявка/принятие/два хода в транзакции с rollback, карма/ордеры/безопасные и запрещенные локации в транзакции с rollback, поиск атак `Teleport/100/double slap/физическая/status`, покупка наборов за монеты/алмазы, тренировка и ослабление в транзакции с rollback.
- Render smoke: `game-module` и `game-messages` рендерятся через `View::render`.
