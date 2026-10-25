# Testing

## Checks without a database

```bash
php tools/ci_lint.php
php tools/encoding_guard.php
php tools/pdo_param_audit.php --self-test
```

Check changed JavaScript with `node --check path/to/file.js`. GitHub Actions checks `public/js/` and maintained PHP. Legacy `include/` and `admin/` trees remain outside that syntax gate.

CI does not start the game, write a database or certify gameplay parity.

## QA environment

Use an isolated local database and dedicated QA accounts. Set `APP_ENV=local` and `QA_TOOLS_ENABLED=1` only there. Provide `SMOKE_LOGIN` and `SMOKE_PASSWORD` in the process environment; do not paste credentials into public reports.

Take and verify a backup before database mutations:

```bash
php tools/beta_backup.php
php tools/beta_backup_verify.php
```

Then run the relevant suites sequentially:

```bash
php tools/open_test_regression.php --profile=quick --login=Tacos
php tools/open_test_regression.php --profile=full --login=Tacos
```

The runner reads the password from `SMOKE_PASSWORD`. Confirm its help output and local guards before use. Avoid simultaneous mutating runs: battle locks and account state are shared.

## Read and asset checks

```bash
php tools/location_art_refresh_smoke.php
php tools/pdo_param_audit.php
```

The location art check formats live database locations without player movement or `/api/game/state` requests. Other tools with “read” in their name may still record sessions, notifications or state: inspect the tool before classifying it as mutation-free.

`tools/graphics_refresh_verify.php` is an operator check that also needs the local art-source manifest and protected-file baseline from the generation session. It is not a clean-clone CI gate.

## Reporting

Record the revision, environment, exact commands, passed/failed/skipped counts and remaining limitations. For UI changes, check narrow screens, image loading, keyboard controls and error fallback. For gameplay, include the actual action path; aggregate counts do not prove every quest story.

## Experience and gameplay regressions

The growth-group and XP contract checks need no database and run in CI:

```bash
php tools/pokemon_growth_curve_regression.php
php tools/pokemon_experience_regression.php
php tools/evolution_rules_regression.php
```

The 4–6 hour target is a model for one medium-growth Pokemon from level 5 to 100, assuming suitable encounters and 45–60 second battle cycles. It is not a timed full-playthrough result. Growth groups differ; no team-wide EXP share is enabled. Run database-backed catching, drops and concurrency checks sequentially on dedicated QA accounts after a verified backup.
