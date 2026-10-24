# PokemonChic / Pokemon Legend 8.0

Актуальный релизный baseline (2026-10-24): PHP 8.5.11, миграции 87/87, full regression 29/29. Источник текущих проверок и оставшихся условий: [release candidate](docs/reports/RELEASE_CANDIDATE_2026-10-24.md). Старые числа ниже сохранены как история.

Modern rewrite of the legacy browser Pokemon MMORPG for PHP, MySQL and a JSON API driven frontend.

The project is being prepared for open testing. Legacy PHP files are still kept as compatibility/reference material, but the active game flow is moving to `public/index.php`, controllers, repositories, services and modern game overlays.

## Current Status

Last project audit: 2026-10-07. The current source of truth is [PROJECT_CONTEXT.md](PROJECT_CONTEXT.md); older reports are historical unless they match live code and current smoke results.

Current verification snapshot:

| Area | Status | Evidence |
| --- | --- | --- |
| Site availability | OK | `http://pokemonchic.com` returns 200 in the 2026-10-07 gate. |
| Quick regression | OK | `tools/open_test_regression.php --profile=quick` = 24/24 in the 2026-10-07 gate. |
| Read smoke | OK | `tools/http_smoke.php` = 52/52 in the 2026-10-07 gate. |
| Migrations | OK | `79/79`, `pending=0`, `dirty=0`, `failed=0` in `PROJECT_CONTEXT.md`. |
| Integrity | OK with accepted historical rows | `P0=0`, `P1=0`, `WARN=0`, `ACCEPTED=4` in `PROJECT_CONTEXT.md`. |
| Item gameplay metadata | Mixed | `implemented=42`, `visual_only=17`, `todo=11` in the 2026-10-07 gate. |

See [PROJECT_CONTEXT.md](PROJECT_CONTEXT.md) for the short current architecture summary and [REWRITE_STATUS.md](REWRITE_STATUS.md) for the migration journal.

## Implemented Systems

### Core

- Front controller: `public/index.php`.
- Lightweight router and request/response layer.
- Session auth, CSRF protection and maintenance mode.
- PDO database access with repository classes.
- Modern `/game` shell without legacy frameset navigation.
- New `/game/admin` Game Master Center.

### Auth And Account Flow

- Login with legacy/password-hash compatibility.
- Registration without mandatory email for the current transition period.
- Password reset scaffolding with SMTP config.
- Techwork/maintenance gate for non-admin users.
- Admin access checks through the new admin repository layer.

### Game World

- `/api/game/state` for player state, location, transitions, NPC and battle state.
- `/api/map/move` for map movement.
- New NPC dialog flow through JSON APIs.
- Transport support for ship routes and airplane flights.
- Flight ticket flow: route selection, onboard location, 15-minute flight, conductor status and exit after arrival.

### Battles

- PvE battle API: `/api/battle/pve/state`, `/action`, `/ack-end`, `/force`.
- PvP battle API: invite, reject, timeout, accept, force, status, refresh sync, surrender and history.
- PvP smoke covers invite/reject/timeout/accept/refresh/anti-double-click/switch/items/history.
- Pokeballs are blocked in PvP.
- PvE catch/finish/ack smoke tests are available.
- Battle transformations are supported for Primal/Mega forms through battle-only state.
- Primal Kyogre, Primal Groudon and Mega Rayquaza support is seeded with weather abilities.

### Pokemon, Eggs And Breeding

- `/game/pokemon` for active team, daycare/nursery and breeding UI.
- Active team is separated from daycare storage.
- Held item icons are displayed on Pokemon cards.
- `/api/pokemon/moves`, `/training`, `/nursery`.
- `/api/eggs`, `/incubate`, `/hatch`.
- Incubators reduce remaining hatch time.
- Breeding flow with compatibility letters, gender rules, Ditto and Ditto Extract.
- Egg parent metadata, inherited IV logic and egg move inheritance are implemented.

### Inventory And Items

- `/api/inventory/page` with categories, search and pagination.
- `/api/inventory/battle` for battle inventory.
- Target item use, held item equip/unequip and gift opening.
- Temporary items use legacy `items_users.dattimer`.
- Held/lore metadata is stored in `item_gameplay_metadata`.
- Blue Orb, Red Orb, Soul Dew, Thick Club and type-boost compatibility rules are enforced.
- Gift boxes open through server-side loot tables and reward flow.

### Markets And Economy

- Legacy compatible item market and Pokemon market routes remain available.
- New Commission Shop:
  - `/game/commission`;
  - embedded overlay on `/game`;
  - `/api/commission/lots`;
  - `/api/commission/sellable`;
  - `/api/commission/my`;
  - create, buy and cancel lot endpoints.
- Supports item, Pokemon and egg lots.
- Reserves objects before sale and returns them safely on cancel/expire.
- Blocks potions, berries, quest items, bound items, temporary items and equipped items.
- Uses 5% default commission.
- Market logs, return storage and risky deal review are available in admin.

### Trainer Card And Social

- `/api/profile/card` returns UID, avatar, rank, clan, active team, gifts, rewards and gym badges.
- Trainer Card opens as a modal on the current page.
- Gym badge reward type exists and badges persist.
- Friends, chat, private/system notifications and messages have modern API coverage.

### Admin / GM Center

- `/game/admin` is the current Game Master Center.
- Sections cover users, items, Pokemon, attacks, locations, drop rules, bosses, events, tournaments, medals, moderation, audit and commission market.
- Admin Pokemon granting supports user lookup, base Pokemon lookup, IV/EV, nature, level, shiny, stats, HP and starter move reporting.
- Commission admin shows lots, logs, returns, price history, suspicious deals, review status and seller/buyer details.
- Legacy map is available only as its own tab.

## Main Open-Test Risks

These are the highest-priority remaining tasks before open testing. Battle replay, background jobs, migration status and GM Center health views already exist in the current layer; remaining work is hardening and broader QA, not initial implementation.

1. Finish long real 6v6 PvP testing with two sessions.
2. Finish rare battle item/status/weather edge-case QA.
3. Finish first-player path validation across quests, NPCs, location transitions and rewards.
4. Harden economy safety through more market, commission and reward edge-case checks.
5. Run desktop and mobile browser regression before inviting open testers.
6. Keep production operations documented: backup cadence, smoke cadence, background job monitoring and incident handling.

## QA Accounts

The current project uses these permanent QA/system accounts:

- `Tacos` - main admin/QA player.
- `NIGA` - second player for two-session PvP/trading/breeding tests.
- `Система` - system account for market/system operations and future system notifications.

## Smoke Scripts

Run from the project root with the configured PHP binary:

```bash
php tools/http_smoke.php --login=Tacos --password=REDACTED_USE_SMOKE_PASSWORD
php tools/pvp_qa_smoke.php --login1=Tacos --login2=NIGA --password1=REDACTED_USE_SMOKE_PASSWORD --password2=REDACTED_USE_SMOKE_PASSWORD
php tools/legacy_core_qa_smoke.php
php tools/breeding_qa_smoke.php --password=REDACTED_USE_SMOKE_PASSWORD
php tools/commission_market_smoke.php
```

Useful optional scripts:

```bash
php tools/prepare_qa_teams.php
php tools/qa_drop_mode.php
```

## Local Setup

Requirements:

- PHP 8.3+ for the target runtime.
- MySQL/MariaDB with `pdo_mysql`.
- Apache or PHP built-in server.
- `mbstring`.

Create `.env` from `.env.example` and configure the database:

```bash
cp .env.example .env
```

Run with the PHP built-in server:

```bash
php -S 127.0.0.1:8000 -t public public/index.php
```

With OSPanel, point the domain root at this repository and route traffic through `public/index.php`.

## Project Structure

```text
public/                  Front controller, public assets, JS/CSS
src/Controller/          HTTP controllers
src/Repository/          Database access and domain persistence
src/Game/                Battle, NPC, routing and gameplay services
src/Http/                Request/Response/Router layer
src/Security/            Auth/session/CSRF helpers
src/Support/             Env, autoload, mailer and utilities
views/                   PHP views and reusable components
database/migrations/     Idempotent SQL migrations
tools/                   QA/smoke/seed scripts
REWRITE_STATUS.md        Detailed rewrite status journal
PROJECT_CONTEXT.md       Short current architecture map
```

## Development Rules

- Prefer the new JSON API and repository/service layer.
- Keep legacy files as reference/compatibility unless a migration explicitly removes them.
- Use CSRF for every mutating endpoint.
- Do not write direct random SQL mutations in game code when a repository flow exists.
- Keep migrations idempotent and apply them to the live dev database.
- After meaningful changes, run relevant PHP lint, JS syntax checks and smoke scripts.
- Do not commit local logs, browser screenshots, archives, debug dumps or temporary workspace folders.

## License

Private project during rewrite and test preparation.
