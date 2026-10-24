# Release Harness Review — 2026-10-24

**Scope:** bounded, read-only release review requested by the owner. Only this report was created.
**Branch:** `php85-migration` (`.git/HEAD` → `refs/heads/php85-migration`; HEAD `be3373f8`).
**Reviewer constraints:** no edits to existing files, no DB mutations, no smoke suites, no services, no migrations, no commits, no external communications, no Codex CLI / Hybrid Engine. `.env` was not read; no credentials are reproduced here.

## 0. Method, evidence, and limits

**Evidence markers used below**

- **FACT (read)** — verified by reading the live file at the cited line. This is the strongest evidence in this report.
- **REPORT CLAIM** — taken from an old `TASK-*.report.md` or `BOARD.md`; treated as a claim, not proof.
- **NOT TESTED** — the runtime/DB/HTTP path could not be exercised in this review.
- **SUGGESTION** — a proposed fix; not applied.

**What was actually done**

- Read `PROJECT_CONTEXT.md`, `AGENTS.md`, live PHP/JS/config/`.htaccess`/`.gitignore` files, `docs/agent-tasks/BOARD.md`, the relevant task specs/reports, and the file tree.
- Verified the active branch from `.git/HEAD` and the last commit from `.git/logs/HEAD:99` (HEAD `be3373f8`, 2026-10-11).
- Confirmed by file search that there is **no** `composer.lock` and **no** `vendor/` directory.
- Confirmed the presence of a `2026-10-24` backup in `storage/backups/` and the absence of any deploy manifest/script at the repository root.

**What was not done**

- No shell command succeeded: `pwsh` exited `0xC0000142` (`STATUS_DLL_INIT_FAILED`) for every invocation, including `php -v` and `git status`. The environment was down at the start of the audit and remained unavailable.
- No PHP lint, Composer validation, HTTP probe, DB read, integrity run, or regression run was performed. All statements about runtime behavior below are static code analysis, not test results.
- No `git status` could be obtained. The working-tree cleanliness and the exact set of dirty files are therefore **unverified**. This report does not claim any existing file is clean or dirty.

---

## 1. P0 — release blockers

### R-01. The stale-PvE reaper will close an active **boss** battle

**Priority: P0 (stop-ship for any live `--apply`)**

**FACT (read):**

- `src/Game/StalePveBattleReaper.php:28-33` selects unfinished PvE battles with `b.times <= :now` (and `b.times <= 0`). There is no join or exclusion for boss sessions.
- `src/Repository/BossRepository.php:595-618` creates a boss battle with `batl_tip = "pve"`, `pobeda = 0`, and `times = :now` (the `:now` value is `time()` at `:598`, bound at `:617`). In other words, a boss battle is born already "expired" by the reaper's rule.
- `BossRepository::startForUser()` sets `users.pve = 1, battleid = <boss battle>` at `src/Repository/BossRepository.php:240-243`.
- `BossRepository::activeSessionForBattle()` identifies the live boss fight by `boss_battle_sessions.status = "active"` at `src/Repository/BossRepository.php:265-271`.
- `StalePveBattleReaper.php:70` sets `battles.pobeda = -1`; `:74` clears the matching `users.pve` / `users.battleid` lock.

**Impact:** any run of

```text
php tools/pve_stale_battle_reaper.php --apply
```

while a boss fight is active will mark that boss battle finished (`pobeda = -1`), clear the player's PvE lock, and leave `boss_battle_sessions` in `active` state. The player loses the boss encounter; the boss session becomes orphaned/inconsistent. Unlike ordinary PvE battles, which are created with `times = time() + 3600` (`src/Game/WildEncounterService.php:400,428`; legacy `include/files/gameload.world.php:199,276`), boss battles never get a future deadline.

**SUGGESTION (minimal, preferred):**

1. In `BossRepository::createBossBattle()` set a real deadline, e.g. `times = time() + <boss TTL>`, and bind that value at `:617`. Use the same TTL convention as PvE (3600 s) unless the boss design requires longer.
2. Additionally harden the reaper against any future `batl_tip = "pve"` rows that are managed by another subsystem:

```sql
AND NOT EXISTS (
    SELECT 1 FROM boss_battle_sessions s
    WHERE s.battle_id = b.id AND s.status = 'active'
)
```

Apply the same guard to the per-candidate `SELECT ... FOR UPDATE` at `StalePveBattleReaper.php:60`, not only to the initial candidate scan, so a boss fight started between the scan and the lock is still protected.
3. Add a regression case (see R-07) that creates an active boss battle + session and asserts that `reap(false)` leaves `pobeda = 0` and `users.pve = 1`.
4. Do not run `--apply` on the live DB until this is fixed and the regression is green.

**Status:** code is currently unsafe for live application. The Board already says "применение ждёт решения владельца"; this finding is the concrete reason that decision must not be made yet.

---

### R-02. `quest_update:*` remains in the live API; its status is contradictory

**Priority: P0 by the Board's own classification; owner is Codex; no change made in this review**

**FACT (read):**

- `src/Game/NpcDialogService.php:191-192` dispatches every action starting with `quest_update:` to `questUpdateAction()`.
- `src/Game/NpcDialogService.php:1540-1563` implements that handler with a five-entry whitelist and server-side checks (`locationId`, `quest_npc`, `advanceNarrativeStep`).
- `docs/agent-tasks/TASK-010-quest-integrity.report.md:5-6` claims the client-controlled `quest_update:<questId>:<process>:<completed>` handling and `questUpdateAction()` were removed.
- `docs/agent-tasks/TASK-013-auth-session-hygiene.report.md:20-21` says the `quest_update:*` routing/handler was restored because live dialogs and smoke depended on it.
- `docs/agent-tasks/BOARD.md:14` still lists TASK-010 as `todo`; the owner statement for this audit says this API is known insecure and is Codex-owned.

**Impact:** the repository cannot answer "is TASK-010 closed?" from its own status files. The live code does contain the action path; the report claiming removal is false for the current tree. This is release-critical by the Board's P0 label and must be resolved by Codex, not by this review.

**SUGGESTION:** Codex closes TASK-010 with a server-side state/nonce design (or an explicit owner acceptance), and the Board row is changed to reflect the real state (`reopened`/`todo`, not `done`). Do not treat the old TASK-010 report as evidence of closure.

---

## 2. P1 — release-critical security / packaging

### R-03. Production QA-seed endpoint contains a hard-coded account password and can run mutating smokes

**Priority: P1 (release-blocking security hygiene)**

**FACT (read):**

- Routes: `public/index.php:298-299` expose `GET /api/admin/qa-seed-tools` and `POST /api/admin/qa-seed-tools/run`.
- Authorization is admin-only plus CSRF: `src/Controller/AdminApiController.php:60-72` and `:773-783` (403 for non-admin, 419 for bad CSRF). It is **not** an anonymous endpoint.
- `src/Repository/AdminQaSeedRepositoryTrait.php:29-40` exposes actions `setup_accounts`, `give_teams`, `give_items`, `reset_market`, `run_smokes`.
- `src/Repository/AdminQaSeedRepositoryTrait.php:273` contains a hard-coded literal password for created QA accounts. The value is deliberately not reproduced in this report.
- `src/Repository/AdminQaSeedRepositoryTrait.php:293` makes the recreated `Tacos` account `groups = 1` (admin panel access).
- `src/Repository/AdminQaSeedRepositoryTrait.php:238-254` runs `db_integrity_smoke`, `background_jobs_smoke`, `inventory_held_items_smoke`, and `commission_hardening_smoke` through `proc_open` (`:475`) from a web request.

**Impact:** the endpoint is a production QA backdoor in practice:

- if the `Tacos` QA account is ever missing, any admin (or anyone who obtains an admin session) can recreate it as a group-1 admin with a password that is present in the source;
- `reset_market` and `run_smokes` execute mutating DB operations from a browser request, with no environment gate;
- a successful admin-session compromise escalates from "admin UI" to "known admin credential + arbitrary DB-changing QA actions".

**SUGGESTION:**

1. Remove the literal from `AdminQaSeedRepositoryTrait.php:273`. If seed accounts are still needed, read the initial password from an environment variable and fail closed when it is unset.
2. Gate the QA routes and the GM Center QA block behind an explicit non-production flag (e.g. `APP_ENV=qa` or `QA_SEED_TOOLS=1`), defaulting to off.
3. Remove `run_smokes` from the production route entirely; smokes must be run from CLI with the normal backup/lock discipline.
4. Rotate any account that may have been created/reset with the literal, and audit `admin_audit_log` for `qa_seed.*`.
5. Do not ship `docs/agent-tasks/*.report.md`: at least one report (`TASK-013-auth-session-hygiene.report.md:25`) contains a plaintext `--password=` value. The value is not reproduced here. This is a docs-packaging leak.

---

### R-04. Temporary QA scripts still live in the web root; `_incoming` is not denied

**Priority: P1 (release artifact / possible session-data exposure)**

**FACT (read):**

- `_qa-view.php:3` says "ВРЕМЕННЫЙ QA-инструмент ... удалить после проверки". It renders auth views with caller-supplied variables (`:17-32`).
- `_qa-viewport.php:3` says "удалить до деплоя". It accepts a caller-supplied `sess` (`:24`), performs a server-side authenticated fetch with `Cookie: PHPSESSID=...` (`:29-44`), and returns the page inside an iframe. If reachable, this is a session-data exfiltration gadget for anyone who knows a session id.
- Both files are blocked by the Apache rule at `.htaccess:9` (`^_qa-(?:view|viewport)[.]php$`), but they are still present in the web root. A rename, a non-Apache server, `AllowOverride None`, or a release built from the working tree would bypass the rule.
- `_incoming/README.md:59-66` explicitly lists `pokemonchic.com/testx.php` and `testind.php` under "Что не переносить в production".
- `_incoming/pokemonchic.com/testx.php` and `_incoming/pokemonchic.com/testind.php` still exist. `.htaccess:8` denies `.git`, `.hybrid`, `config`, `database`, `docs`, `src`, `storage`, `tools`, `vendor` — it does **not** deny `_incoming`.
- `_incoming/README.md:41-42` states that `_incoming/mapped/root_flat/db.php` contains old hard-coded DB access. The file was not opened. If `_incoming` is served as source (misconfigured PHP, or copied to a static host), that is a credential leak.
- `views/game-start.php:104-109` renders a "QA" button (`debugForceBattleBtn`) only inside `if (!empty($isAdmin))`, and `public/js/game-start-runtime.js:614-620` reveals it on Road 1 / location id 4. The server route `POST /api/battle/pve/force` is admin-checked and CSRF-checked at `src/Controller/GameApiController.php:100-113`. This is an admin-only QA artifact, not a public auth bypass; it should still be removed from the release build.

**SUGGESTION:**

1. Delete `_qa-view.php` and `_qa-viewport.php` from the release tree (move them under `tools/` if still needed locally).
2. Remove `_incoming/` from the web root entirely; at minimum add `RewriteRule ^_incoming(?:/|$) - [F,L,NC]` to `.htaccess` **and** a server-level deny for Apache/Nginx.
3. Remove the QA battle button and `/api/battle/pve/force` route from the production build, or render the button only when the server sends an admin flag (and keep the server-side 403/CSRF checks).
4. Add a release check that requests `/_qa-view.php`, `/_incoming/...`, `/.hybrid/...`, `/.env`, and `/storage/backups/...` and expects 403/404.

---

### R-05. No reproducible deploy package; secrets, backups, and snapshots live in the tree

**Priority: P1 (release packaging)**

**FACT (read):**

- There is no root-level `deploy/` directory, deploy script, manifest, or `.gitattributes`. The only deploy-looking file is the sample `_incoming/pokemonchic.com/deploy/pokemonchic.nginx.conf`.
- That sample (`_incoming/pokemonchic.com/deploy/pokemonchic.nginx.conf:17-31`) denies only dotfiles and extensions `log|sql|bak|old|rar|zip`; it does **not** deny `tools/`, `src/`, `config/`, `database/`, `storage/`, `docs/`, `_incoming/`, `_qa-*`, or `test*.php`. It also targets `php8.0-fpm`, not PHP 8.1/8.5. It is not a safe release config.
- Root `.htaccess:8` protects many dev directories only when Apache + `mod_rewrite` + `AllowOverride` are active. `.htaccess:19-26` denies `.env`, `*.sql`, `*.log`, and `log_php_errors.txt`, again only in Apache.
- `.gitignore:9` ignores `_*/` (so `_incoming` is ignored), but `.gitignore` has no entry for `.hybrid`. There are at least 11 full repository snapshots under `.hybrid/tasks/*/`, including copies of `_incoming/pokemonchic.com/db.php`. Root `.htaccess:8` denies `.hybrid` on Apache, but a zip/copy release includes it.
- `.gitignore:21` ignores `composer.lock`; `.gitignore:32` ignores `storage/backups/`.
- `storage/backups/` contains a dump dated **2026-10-24 14:02:05** plus earlier dumps. They are git-ignored and `.htaccess`-denied, but a whole-tree package includes them.
- `.env` exists in the project root (not read). `.env.example:3` sets `APP_DEBUG=1`. `public/index.php:101` turns `display_errors` on when debug is true.
- `docs/agent-tasks/*.report.md` are development artifacts; at least one contains a plaintext test password (see R-03).

**Impact:** if the release is produced by copying/zipping the domain root (the current tree has no alternative manifest), the package will contain `.env`, DB dumps, `.hybrid` snapshots, `_incoming` legacy code/config, QA scripts, and reports with credentials. Apache `.htaccess` rules do not travel to Nginx and do not protect files that are included in a downloaded archive.

**SUGGESTION:**

1. Create an explicit release manifest (files/directories to include) and build the artifact from it; do not zip the working tree.
2. Add `.gitattributes` `export-ignore` for `.hybrid/`, `_incoming/`, `_qa-*.php`, `docs/`, `tools/`, `storage/`, `.env`, `*.sql`, and local screenshots; add `.hybrid/` to `.gitignore`.
3. Ship a server config that denies everything except `public/`, the legacy entry points that must remain public, `css/`, `script/`, and `include/files/{registration,rulles,sendpass}.php` as applicable. The current Nginx sample is not that config.
4. Ensure production `.env` has `APP_DEBUG=0`; do not ship `.env.example` as `.env`.
5. Rotate the credential exposed in the report/source before any package is shared.

---

### R-06. Composer declares PHP `>=8.3`, while the live runtime is PHP 8.1

**Priority: P1 (release metadata / tooling), not an immediate runtime fatal**

**FACT (read):**

- `composer.json:6` requires `"php": ">=8.3"`.
- The site runtime is PHP 8.1.9 per `docs/agent-tasks/BOARD.md:147`; the owner context for this audit states PHP 8.1 and PHP 8.5.11 are installed, and the active branch is `php85-migration`.
- `PHP85_MIGRATION_PLAN.md:4` says "ветка создана, перепись не начата"; its phase checkboxes (`:135-166`) are unchecked. `PHP85_MIGRATION_PLAN.md:163` explicitly says the `>=8.3` declaration does not match either the environment or the checks.
- `BOARD.md:218` still lists "переключить PHP домена" as the next step; `BOARD.md:147` records that the domain was returned to PHP 8.1 after a failed 8.5 switch.
- No `composer.lock` and no `vendor/` were found by file search. The application uses its own autoloader: `public/index.php:90` requires `src/Support/Autoload.php`. Composer is therefore not used by the runtime, which is why the app still runs despite the declaration.

**Impact:** `composer install` / `composer check-platform-reqs` on the actual PHP 8.1 runtime fails or warns, so the release cannot be built/validated reproducibly from `composer.json`. If the project is actually released on 8.5, the current declaration is accidentally closer, but the 8.5 migration is not finished (short tags, php.ini, full gate). If it is released on 8.1, `composer.json` is simply false.

**SUGGESTION:**

1. Decide the minimum supported PHP for the release.
   - If 8.1 remains supported: change `composer.json:6` to `">=8.1"` and test on 8.1.
   - If 8.5 is the target: switch the domain first (`PHP85_MIGRATION_PLAN.md` Ф3), then set `">=8.5"` and complete the 8.5 gate.
2. Commit a `composer.lock` if Composer is part of the build (the current `.gitignore:21` prevents that).
3. Add `config.platform.php` only if the build must resolve for a different platform than the local CLI; do not use it to hide a wrong runtime.
4. If the release stays on 8.5, package/verify `short_open_tag=On` (22 files use `<?`) and the php.ini differences documented at `PHP85_MIGRATION_PLAN.md:214-216`.

---

### R-07. The reaper's existing regression cannot catch the boss/live-battle failure

**Priority: P1 (the safety test is weaker than its name suggests)**

**FACT (read):**

- `tools/pve_stale_battle_reaper_smoke.php:17-20` refuses to run unless the DB is `pokemon_test` or `REAPER_SMOKE_ALLOW_LIVE=1`. That is a reasonable safety barrier, but it means the test is not part of the normal release gate.
- `:49-54` creates only one synthetic **expired ordinary PvE** battle (`times = time() - 9000`) and sets `users.pve = 1`.
- `:64` is named `reap.preserves_pvp_flag` but asserts `pvp === 0` for a user whose `pvp` was already 0. No PvP battle is created and `pvp` is never set to 1. The check is vacuous.
- There is no case for:
  - a live, non-expired PvE battle (`times > now`), which the reaper must **not** close;
  - an active **boss** battle/session (R-01);
  - `times = 0` / invalid timestamp and the claimed fallback TTL policy;
  - a user with an active PvP battle while a stale PvE row exists.
- `src/Game/StalePveBattleReaper.php:13` declares `FALLBACK_TTL_SECONDS = 7200`, but it is never used. The candidate predicate at `:33` treats `times <= 0` as expired **immediately**, and the `times < :oldest` branch is subsumed by `times <= :now`. `isExpired()` at `:100-106` repeats the same policy.
- `src/Repository/IntegrityRepository.php:139` uses the same `times <= UNIX_TIMESTAMP()` predicate, so it also counts active boss battles as `battle.stale_pve_unfinished`.

**Impact:** a green `10/10` from this smoke does not prove live-battle safety. The TASK-034 report claims a PvP guard was added (`:pvp_user_1` / `:pvp_user_2`), but the current `StalePveBattleReaper.php` contains no PvP `NOT EXISTS` guard at all; either the report describes a different revision or the guard was lost. The current code's conditional `UPDATE users ... AND battleid = :battle AND pve = 1` does avoid clearing a PvP `battleid`, but that behavior is untested by `:64`.

**SUGGESTION:**

1. Add the missing smoke cases listed above; make the PvP case actually set `pvp = 1` and create a second PvP battle, then assert both flags and rows are untouched.
2. Implement the fallback policy explicitly or remove `FALLBACK_TTL_SECONDS`. If `times <= 0` is meant to mean "stale immediately", document that and stop claiming a 7200 s fallback.
3. Align `IntegrityRepository.php:139` with the reaper predicate and exclude active boss sessions.
4. Re-run the smoke only on `pokemon_test` or after a verified backup; do not make it a production endpoint (R-03).

---

### R-08. TASK-050 (global API error handler) is still not implemented

**Priority: P1 (release hardening; API correctness)**

**FACT (read):**

- `src/Http/GlobalErrorHandler.php` and `src/Http/DatabaseUnavailableException.php` do **not** exist (file search).
- No `set_exception_handler`, `set_error_handler`, or `register_shutdown_function` is present in `public/index.php`.
- `public/index.php:409` calls `$router->dispatch($request)->send()` without a top-level `try/catch`.
- `docs/agent-tasks/TASK-050-global-error-handler-json-api.answer.md:1-20` is a proposed patch (3 files, +221/−2) with local HTTP tests 11/11; it was never applied. `BOARD.md:30` still says `todo`.

**Impact:** a fatal error/PDO failure on an `/api/*` route can produce an HTML error page or a partially sent response instead of the promised JSON 500; with `APP_DEBUG=1` (`public/index.php:101`) it can also disclose paths/stack details. This is not a data-corruption blocker, but it is a release-quality blocker for a JSON API.

**SUGGESTION:** apply/port the TASK-050 patch, add a test that boots an API route with a forced DB failure and asserts `Content-Type: application/json` + 500/503, and set `APP_DEBUG=0` in production.

---

## 3. `docs/agent-tasks/BOARD.md` discrepancies

The board is not a reliable release status source. All rows below are live `BOARD.md` lines unless stated otherwise.

| Board line | What the board says | What the live tree / report says | Suggested correction |
|---|---|---|---|
| `BOARD.md:9` (032) | Status `rework`; text says "исправлено 2026-10-09; теперь реапер работоспособен"; mentions 153 candidates / 138 owners | `TASK-032-pve-stale-battle-reaper.report.md:12` says `candidates=164`; current reaper is unsafe for bosses (R-01); TASK-034 report claims 10/10 | Split: `blocked` on owner apply **and** `rework` for R-01/R-07; reconcile 153/138 vs 164 |
| `BOARD.md:10` (033) | Status `todo`; text says "Закрыто Codex 2026-10-09" and "24/24" | `TASK-033-http-smoke-battle-leak.report.md:1` says "выполнена"; board text says verified | `done` (or `review` until the gate is re-run) |
| `BOARD.md:14` (010) | Status `todo` | Live handler exists (`NpcDialogService.php:191-192,1540-1563`); TASK-010 report claims removal; TASK-013 report says restored | `reopened`/`todo` with the real reason; Codex-owned |
| `BOARD.md:15-17` (011-013) | All `todo` | Reports say done; live code contains the fixes (`ClientIp.php:10-20`, `AdminRepository.php:560-570`, `InventoryRepository.php:883-884`, `TrainingRepository.php:39,227`, `Csrf.php:17-56`) | `done`/`review` after the normal gate |
| `BOARD.md:19` (021) | Status `todo` | `BOARD.md:142` says "закрыта, файлы удалены"; live search finds no `market-items.js`, `game-market-items.php`, `game-pokemon-market.php`, or `battle-dock-prototype.*` | `done`; the older TASK-021 report's `blocked` text is historical |
| `BOARD.md:20-22` (001/002/003) | All `todo` | Reports say "Готово" and describe created tools/reports (`asset_registry_check.php`, `php_error_log_report.php`, docs reconciliation) | `done`/`review`, not `todo` |
| `BOARD.md:38` (034) | Status `todo`; text says "Закрыто 2026-10-09 ... 10/10" | `TASK-034-tools-backup-and-smoke.report.md:5` says "rework done"; Board journal `:196` says 10/10 | `done` (or `review` if the `:pvp_user` guard claim is re-checked) |
| `BOARD.md:40` (036) | Status `todo`; "0 из 34" | `TASK-036-icon-integration.report.md:1` says done; `views/components/head-styles.php:20` loads `icons.css`; `views/game-start.php` uses many `icon--*`; `BOARD.md:197` still says 22/34 | `partial`/`review`, with the remaining classes listed |
| `BOARD.md:41` (037) | Status `todo` | `TASK-037-pdo-parameter-duplication.report.md:1` says completed; `BOARD.md:141` says "доведена до review" | `review`/`done`; the audit tool is the deliverable |
| `BOARD.md:48` | Queue order `010 → ... → 003` | It omits 032-037 and the already-reported 038-045; 010 is the known-insecure exception | Replace with a current queue or mark the section historical |
| `BOARD.md:52` | "Блокер ... сброс 22:48 2026-10-07" | Today is 2026-10-24; the 8.5 migration and a 2026-10-24 backup exist | Remove/refresh the stale blocker |
| `BOARD.md:142` | 021 closed, files deleted | Live files are indeed absent; row `:19` still `todo` | Fix the row, keep the journal entry as history |
| `BOARD.md:194` | Gate 24/24 after 013 | Matches the 013 report, but the 013 row is still `todo` | Update the row |
| `BOARD.md:208` | php85 branch created "2026-07-10" | `PHP85_MIGRATION_PLAN.md:3` says 2026-10-07; `.git/logs/HEAD:84` shows checkout to `php85-migration` at epoch `1791506332` (2026-10-09) | Correct the date; one source of truth |
| `BOARD.md:218` | Next step: switch the domain to 8.5 | `BOARD.md:147` says the domain is back on 8.1; `PHP85_MIGRATION_PLAN.md:4` says migration not started | Mark the 8.5 work as not started/blocked, not "next step" |

**Missing rows:** reports exist for `TASK-038`, `TASK-040`, `TASK-041`, `TASK-042`, `TASK-043`, `TASK-044`, `TASK-045`, `TASK-regions`, and `TASK-quest161-162`, but the BOARD table has no rows for them (some appear only in the journal). This makes the board incomplete as a release checklist.

**Board journal staleness:** the last journal entry is 2026-10-11; a `storage/backups/pokemonchic_beta_20261024_140205.sql` exists, and this audit is dated 2026-10-24. The board has not been updated for the current audit cycle.

---

## 4. Pending work: release-critical vs optional design

This is a classification of the open/reported items, not a new task list.

### Release-critical (must be closed or explicitly accepted before release)

| Item | Actual state from live code / reports | Release call |
|---|---|---|
| **032 PvE reaper** | Code exists; unsafe for active boss battles (R-01); regression gaps (R-07); Board `rework` | **Block live apply**; fix R-01/R-07 first |
| **010 quest integrity** | Live `quest_update:*` handler exists; report says removed; Board `todo`; known insecure; Codex-owned | **Owner/Codex decision required**; do not ship without an explicit risk acceptance |
| **033 http_smoke leak** | Report says fixed and Board text says verified 24/24; row still `todo` | Verify once, then mark done; no new code expected |
| **011 IP ban / proxy trust** | Live `ClientIp` trusts headers only from `TRUSTED_PROXIES`; `AdminRepository` wraps ban+audit in a transaction | Verify with the gate; likely done |
| **012 economy atomicity** | Live `InventoryRepository:883-884` splits `:take`; `TrainingRepository` uses `withTransaction()` | Verify with the gate; likely done |
| **013 auth/session hygiene** | Live `Csrf` TTL/legacy handling and `ClientIp`/`Session` changes | Verify with the gate; likely done |
| **050 global API error handler** | Not implemented; only a proposed patch in `.answer.md` | Apply before release if the API is part of the release surface |
| **Composer/runtime decision** | `>=8.3` vs runtime 8.1; no lock/vendor; 8.5 migration not started | Must be decided before packaging (R-06) |
| **Deploy packaging / QA artifacts** | No manifest; `_incoming`, `_qa-*`, `.hybrid`, backups, `.env`, reports with credentials in tree | Must be closed before any package leaves the machine (R-03/R-04/R-05) |

### Optional / design-track (not release blockers by themselves)

These are UI/UX, refactoring, and documentation items. Several are already implemented; the action is mostly to update the board, not to redo the work.

- **001/002/003** — asset registry check, PHP health report, docs reconciliation. Reports say tools/reports were produced. Low risk; keep as review/close.
- **020** — frontend network-error handling. Live `mail-overlay.js` / `commission-market.js` contain `response.ok`/`finally` handling; report says done. UX hygiene, not a release blocker.
- **021** — dead frontend removal. Live target files are gone; report is historical. Close the board row.
- **023** — accessibility polish (`aria-*`, labels, theme). Partial; design-track.
- **036 / 038 / 040 / 042 / 043 / 044 / 045** — icon integration, type scale, sprite flicker, quest/Dex/mail UI. Reports claim done; board is stale/incomplete. These are design-refresh tasks, not release blockers, except where they affect a broken public page.
- **046** — umbrella code-quality spec; not a deliverable by itself.
- **052** — move direct SQL out of `src/Game`; technical debt, not a release blocker.
- **053** — DOM nodes instead of string concatenation; P2 design/frontend refactor.
- **Ф2/Ф3/Ф4/Ф5** — design-refresh phases. Ф2 is closed; Ф3/Ф4 are in progress; Ф5 is done but only partially connected. Optional for release unless a specific screen is broken.

**Not a release blocker but worth fixing during the same pass:** `README.md:18` says migrations `79/79`, while `PROJECT_CONTEXT.md:46` says `87/87`; `README.md:9` says last audit 2026-10-07, and `PROJECT_CONTEXT.md:3` still says "Обновлено: 2026-06-05" despite containing 2026-10-11 facts. This is documentation drift, not code risk.

---

## 5. Suggested release verification sequence (owner/Codex; not executed here)

1. **Fix R-01 first** (boss deadline and/or boss-session guard) and add the missing reaper smoke cases from R-07.
2. Remove/disable the production QA-seed endpoint and its hard-coded password (R-03); rotate the exposed credential.
3. Delete `_qa-*` and `_incoming` from the release tree; add server-level denies; build the artifact from a manifest (R-04/R-05).
4. Decide PHP target and align `composer.json`; run `composer validate` and `composer check-platform-reqs` on the target runtime (R-06). If 8.5: complete `PHP85_MIGRATION_PLAN.md` Ф3/Ф4, including `short_open_tag` and the full gate.
5. Apply TASK-050 or explicitly accept the API error-handling risk (R-08).
6. Re-run the normal gates **sequentially** after a fresh backup: `open_test_regression.php --profile=quick`, `http_smoke.php`, `db_integrity_smoke.php`, `pve_stale_battle_reaper_smoke.php` (on `pokemon_test` or with explicit consent), `pve_stale_battle_reaper.php --dry-run --json`.
7. Only after 1-6: update `BOARD.md` statuses and remove the stale rows/claims listed in section 3.
8. Do not run `pve_stale_battle_reaper.php --apply` until R-01 is fixed and the dry-run output has been reviewed against a fresh backup.

---

## 6. Bottom line

- **One concrete P0 product bug was found in live code:** the PvE reaper treats active boss battles as expired because `BossRepository` stores `times = now` for a `batl_tip = "pve"` row. The existing smoke does not cover this and its `preserves_pvp_flag` check is vacuous.
- **The release tree is not packageable as-is:** QA scripts, `_incoming` legacy code (including a file documented as having hard-coded DB access), `.hybrid` snapshots, `.env`, and backups are in the web root; the only deploy sample is an insecure PHP 8.0 Nginx config. The QA-seed endpoint also contains a hard-coded admin password and can run mutating smokes from the browser.
- **The Composer declaration is not aligned with the runtime:** `>=8.3` vs PHP 8.1; no `composer.lock`/`vendor`; the app uses its own autoloader, so this is a release/tooling blocker rather than an immediate fatal.
- **The Board cannot be used as the release status source:** at least 032/033/034/021/010/011-013 and the 038-045 reports contradict their rows, and several completed tasks have no row at all.
- **No runtime claims:** the environment was down (`pwsh` `0xC0000142`), so all findings are static code/read-only evidence. The next step is owner/Codex verification of R-01 and a deliberate packaging decision, not another report.

**Report path:** `docs/reports/RELEASE_HARNESS_REVIEW_2026-10-24.md`
