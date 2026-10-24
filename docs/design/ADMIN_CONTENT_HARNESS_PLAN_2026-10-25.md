# План: админ-награды, покемоны и перенос контента — 2026-10-25

## 1. Границы и подтверждённые факты
- Это только план: runtime, БД, shell, исходники, тесты, credentials и коммиты в этом проходе не трогаются.
- Прочитаны `PROJECT_CONTEXT.md`, `AGENTS.md` и указанные участки кода; ниже разделены «есть сейчас» и «предлагается».
- `views/game-admin.php:46-70` уже содержит вкладки «Мастер контента», «Пользователи», «Предметы», «Покемоны», «Локации»; `117-118` подключает мастер и `admin-panel.js`.
- `admin-panel.js:1435-1573`: выдача предмета/покемона идёт через поиск и одну кнопку «Выдать»; предпросмотра, причины, повторной сверки и квитанции нет.
- `AdminRepository::grantItem()` (`583-606`) валидирует игрока/предмет, начисляет и пишет audit; отдельного idempotency-key и квитанции нет, audit вынесен после начисления.
- `grantPokemon()` (`916-1049`) считает статы, пишет `pok_user`, атаки и audit внутри транзакции; идемпотентности и квитанции тоже нет.
- `audit()` (`2592-2606`) делает INSERT в `admin_audit_log`; correlation_id, причина, результат и маскирование чувствительных полей не видны.
- `admin-content-wizard.js`: предмет сохраняется сразу; квест (`178-251`) и NPC (`253-318`) собирают только черновик/карточку и ничего не публикуют.
- `LocationContentRepository.php:6-79` — read-only чтение `config/location_content.php`, санитайз `about` и нормализация NPC; это не хранилище редактируемых NPC.
- Legacy не объявляется мёртвым: он остаётся живым, пока URL/достижимость не доказали обратное; удаление вне плана.
- Числа миграций, строк и прогресса не выдумываются: только фактические отчёты инструментов на дату прогона.

## 2. Что переиспользуем сейчас
- `admin_audit_log` и `auditRows()` как основу журнала; расширяем метаданными, а не заменяем.
- `grantItem()`/`grantPokemon()`, `resolveUserForGrant()`, `resolvePokemonBaseForGrant()`, расчёт статов, `seedPokemonMoves()`.
- Существующие lookup-инпуты и серверные admin/CSRF-проверки оболочки `/game/admin`.
- `RewardRepository::grantPipeline()`, `reward_transactions` и Safe Storage для item-like наград и компенсаций.
- Мастер контента как UX-каркас: предмет — рабочая запись, квест/NPC — безопасный черновик.
- `LocationContentRepository`, `QuestRepository`, `NpcDialogService` и существующие смоуки как базу для миграции.

## 3. Что предлагается (сейчас этого нет)
- Таблица `admin_grant_receipts`: id, admin_id, idempotency_key, correlation_id, target, payload_hash, result, created_at.
- Preview-контракт: сервер возвращает нормализованный payload, остаток у получателя, предупреждения и подписанный preview-token.
- Confirm-контракт: причина, явное подтверждение, проверка роли/CSRF, транзакция, квитанция и audit в одном commit.
- Маскирование логов: e-mail, IP, токены, пароли; ID/ник — по политике, необходимой для разбора.
- Append-only audit на уровне БД (права/триггер), запрет UPDATE/DELETE; reversals — только новые записи.
- Реестр `content_migration_registry`: источник, цель, владелец, статус, parity-запрос, доказательство.
- Поэтапные флаги: `admin.grant.v2`, `content.quest.publish`, `content.npc.publish`, `locations.create` — по умолчанию off.
- Backup-гейт: `tools/beta_backup.php` + verify до любой миграции; миграции идемпотентны.

## 4. Целевой поток выдачи
1. Поиск получателя по точному ID/нику, подсказки и read-only показ текущего инвентаря/покемонов.
2. Выбор типа: предмет или покемон; визуальный builder с допустимыми полями, без ручного JSON.
3. Preview: получатель, before/after, лимиты, конфликты, предупреждения, срок временного предмета.
4. Явное подтверждение: чекбокс «понимаю последствия», причина, preview-token; кнопка не срабатывает одним кликом.
5. Транзакция: idempotency-key, блокировка цели, начисление + receipt + append-only audit; всё или ничего.
6. Квитанция: receipt ID, correlation_id, ссылка на запись; повторный запрос возвращает ту же квитанцию.
7. Трекер: успешные/неуспешные выдачи, pending-компенсации и фильтр по correlation_id.

## 5. Обязательные инварианты
- Идемпотентность: уникальный `(admin_id, idempotency_key)`; retry после таймаута не создаёт вторую награду.
- Транзакции: item row + receipt + audit (и reward entries, если используется pipeline) в одном commit; сбой не оставляет половину.
- Роли: проверка admin на сервере и CSRF на каждом write; скрытая кнопка — не защита.
- Логи: маскировать чувствительные поля, не писать пароли/токены; audit не должен содержать секретов.
- Аудит: append-only; исправление — новая запись `grant.reversal`/`grant.failed`, не редактирование старой.
- Компенсация: безопасное списание/возврат через Safe Storage и ledger; не DELETE и не destructive rollback.
- Если предмет использован/передан, а покемон изменён/продан — автореверс запрещён, только manual review с доказательствами.
- Correlation ID: от клика в UI до API, БД, audit, квитанции и логов; по нему собирается вся история.
- Read-only inventory: сначала снимок текущих наград/контента и источников; числа берутся из инструмента.
- Флаги и backup: staged rollout, старый путь доступен до приёмки; backup+verify и idempotent migration обязательны.

## 6. Квесты, NPC и реестр источников истины
- Текущее правило сохраняется: wizard квеста/NPC — только draft; публикация отдельным валидируемым действием.
- Воронка: draft → schema/action_key/reward validation → dry-run прогресса → staging-флаг → publish → smoke и мониторинг.
- NPC: сначала новый storage + admin save endpoint + чтение карты из него; только потом «опубликовать NPC».
- Реестр фиксирует текущий source of truth (файл/таблица/legacy), целевую модель, владельца, parity-запрос и evidence.
- Паритет прогресса: сохранять quest/step ID, сравнивать `user_quest_tracking` read-only, не сбрасывать игрокам прогресс.
- Cutover только при отсутствии необъяснённых расхождений; иначе dual-read и старый источник остаётся рабочим.

## 7. Локации
- Новая локация — только при доказанной нехватке: на графе нет нужного перехода, NPC/квеста/контент-таргета.
- Evidence: route graph, недостижимые цели, отсутствие подходящей существующей локации, подпись владельца.
- Сначала переиспользовать/связать существующие локации; произвольное расширение карты запрещено.

## 8. Фазы
0. Read-only inventory: источники, экраны, таблицы, смоуки, реестр; никаких write-миграций.
1. Item-grant v2 (первый исполнимый срез): preview/confirm/receipt/idempotency/audit для предметов, флаг off.
2. Pokémon-grant v2: тот же контракт, транзакция, компенсация, трекеры; выравнивание с item-путём.
3. NPC/quest: storage, publish-воронка, реестр, паритет существующего прогресса, staged flags.
4. Locations: evidence-gate и связи только там, где граф/контент доказали нехватку.

## 9. Первый исполнимый срез (Slice 1)
`Slice 1 = Phase 0 (read-only inventory) + item-only выдача v2.` Покемоны остаются на текущем пути до Slice 2.
- DSH: `public/js/admin-panel.js` (блок 1435-1484: поиск → builder → preview → confirm), `public/css/admin-panel.css`, `views/game-admin.php` только при необходимости mount-point.
- Codex: `src/Repository/AdminRepository.php`, `src/Controller/**` (preview/confirm), `database/migrations/**` (receipts/correlation), `tools/admin_grant_inventory.php`, `tools/admin_grant_flow_smoke.php`.
- Протокол: DSH пишет `docs/agent-tasks/TASK-*.md`; Codex — `.report.md`; `src/**` и миграции только по этой задаче.
- Scope: inventory-гейт, preview без записи, confirm с reason/token, idempotency, транзакция, audit, флаг `admin.grant.v2` off.
- Scope: квитанция и correlation_id; старый экран доступен при выключенном флаге.
- Out of scope: покемоны, публикация квестов/NPC, новые локации, автореверс, изменения legacy.

## 10. Приёмка Slice 1
- `php -l` на изменённые PHP-файлы и `node --check public/js/admin-panel.js` — без ошибок.
- Inventory-инструмент делает только SELECT, не пишет в БД и не содержит захардкоженных чисел миграций/строк.
- NIGA/не-админ получает 403; POST без CSRF — 419; проверка на сервере, не только в UI.
- Preview не меняет `items_users`, `pok_user` и `admin_audit_log`; показывает текущий остаток read-only.
- Confirm без preview-token/причины/чекбокса отклоняется; просроченный token отклоняется.
- Повтор с тем же idempotency_key возвращает ту же квитанцию и не создаёт вторую выдачу.
- Инъекция сбоя в транзакцию не оставляет частичной выдачи; фиксируется failed-receipt/audit.
- Audit содержит actor, target, item, qty, before/after, reason, result, correlation_id; секреты замаскированы.
- При выключенном флаге старый путь работает; проходят `admin_gm_center_smoke`, `reward_pipeline_smoke`, `db_integrity_smoke`.
- До миграции снят и проверен backup; повторный запуск миграции идемпотентен.
