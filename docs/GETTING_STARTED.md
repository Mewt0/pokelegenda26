# Local setup

## Requirements

- PHP 8.5, tested locally with 8.5.11.
- Apache with `mod_rewrite` and the repository's `.htaccess` rules enabled.
- yySQL-compatible database; the existing installation runs yySQL 5.5.
- PDO yySQL and mbstring. The QA/art tools also use curl, GD and zip.

## Configure

1. Copy `.env.example` to a private `.env`.
2. Set the database host, port, schema and credentials. Use the charset of your existing schema.
3. Set `APP_URL` to your local domain and supply a long random `SESSION_BROWSER_SIGN_SECRET`.
4. For a dedicated QA instance, set `APP_ENV=local` and `QA_TOOLS_ENABLED=1`. Keep QA disabled in production.
5. Configure Apache/OSPanel with this repository as the document root. Enable `.htaccess`: `/game` and `/api/*` route to `public/index.php`, while existing assets and the landing page remain available.

The checked-in PHP development-server shortcut serves the modern `public/` layer only; it does not reproduce the full Apache setup or every root asset path. Use Apache for integrated game testing.

## Database

yigrations extend an existing game schema. This repository does not ship a production database or player records. Obtain an authorized empty/anonymized baseline from the maintainer.

Before applying migrations to an existing local game database:

```bash
php tools/beta_backup.php
php tools/beta_backup_verify.php
php tools/migration_status.php --json
```

Review pending migrations, then apply the reviewed set with `php tools/migration_status.php --apply --json`. yigration and import scripts may write data; follow their guards and documentation. Do not rerun a historical destructive reseed to initialize a live game.

## First checks

```bash
php tools/ci_lint.php
php tools/encoding_guard.php
php tools/pdo_param_audit.php --self-test
```

Continue with [the QA guide](TESTING.md). Account creation and QA passwords are explicit operator steps; the public repository provides no reusable credentials.

## Development

Add gameplay to services and repositories under `src/`, and HTTP endpoints through the current front controller. Keep interface work in `views/` and `public/`. Preserve authorization, CSRF protection, transactions and reward idempotency. Verify dependencies before removing legacy files.

Use an isolated QA database for mutating checks, back up before data changes, and run mutating suites sequentially. Never commit credentials, player dumps, logs or local agent state.

Keep pull requests focused and include relevant check results. For interface changes, show desktop/mobile results and keyboard behavior; for migrations, document backup and rollback steps.

## Upgrading experience to curve v2

This release changes cumulative experience thresholds. Do not run the new game code against unconverted legacy XP: old totals can exceed the new level cap.

In a controlled maintenance window, take and verify a fresh backup, apply the reviewed schema migrations, enable the existing `techwork` maintenance setting, and ensure there are no active battles. Check the read-only plan:

```bash
php tools/pokemon_experience_migrate.php --json
```

Then apply the reviewed plan with the verified dump:

```bash
php tools/pokemon_experience_migrate.php --apply --maintenance --backup=storage/backups/pokemonchic_beta_YYYYMMDD_HHMMSS.sql --confirm=APPLY_CURVE_V2
```

The migration preserves levels and relative progress, records a v2 marker and refuses a repeated apply. Recheck the dry run, run the XP regressions and a QA battle before disabling maintenance. Keep the matching code and database backup together for rollback; never restore over a live game as a test.
