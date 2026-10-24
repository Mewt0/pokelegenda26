TASK-013 выполнена в разрешённой зоне.

Изменено:

- `AuthController`: устранена Host-инъекция, удалены хеш пароля и неиспользуемый `browse` из сессии, добавлена ротация CSRF.
- `Csrf`: TTL 2 часа и перевыпуск токена.
- `Session`: `X-Forwarded-Proto` принимается только от `TRUSTED_PROXIES`.
- Создан [отчёт TASK-013](E:/OSPanel/domains/pokemonchic.com/docs/agent-tasks/TASK-013-auth-session-hygiene.report.md).

Проверки:

- `php -l`: 3/3 без ошибок.
- CSRF TTL и ротация: успешно.
- Недоверенный proxy-заголовок игнорируется.
- При `Host: evil.example` ссылка остаётся `http://pokemonchic.com/...`.
- `git diff --check`: ошибок нет.

## Исправление регрессии

- `src/Security/Csrf.php`: токен без `_csrf_issued_at` теперь считается legacy-совместимым — он возвращается без неявной ротации и проходит `validate()`. TTL 2 часа применяется только к токенам с временной меткой, записанной `Csrf::rotate()`. Явная ротация после входа/смены пароля сохранена; после входа клиент загружает `/game` и читает актуальный токен.
- `src/Game/NpcDialogService.php`: восстановлены маршрутизация и обработчик `quest_update:*`, удалённые TASK-010 при том, что живые диалоги Spike и smoke продолжали посылать эти действия. Именно это останавливало прогресс и каскадно роняло завершение и обе награды.
- `src/Repository/QuestRepository.php`: ослаблять `canStart()` не потребовалось. В сценарии prerequisite-квест `1` завершён корректно; после восстановления обработчика строгая проверка проходит.
- Перед мутирующим гейтом выполнен `php tools/beta_backup.php`: создан `storage/backups/pokemonchic_beta_20261009_070703.sql` (`9.09 MB`).
- `php -l` для `Csrf.php`, `QuestRepository.php`, `NpcDialogService.php` — без ошибок; `git diff --check` — без ошибок.
- Полный обязательный гейт: `php tools/open_test_regression.php --profile=quick --login=Tacos --password=REDACTED_USE_SMOKE_PASSWORD` → `Passed: 24, failed: 0, skipped: 0` (`22297 ms`). Внутри: HTTP `52/52`, Quest chains minimum `53/53`, integrity `P0=0, P1=0, WARN=1, ACCEPTED=4`.

Итог: регрессия входа и цепочки Spike устранена, целевой gate восстановлен до `24/24`. Коммиты и push не выполнялись.
- Мутирующие smoke-тесты и операции с БД не запускались.

`GET /logout` оставлен без изменения: безопасное исправление требует правок `public/index.php` и `views/home.php`, находящихся вне зоны TASK-013. Риск и необходимое продолжение подробно записаны в отчёте. Коммиты и push не выполнялись.
