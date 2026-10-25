# Contributing

Read [PROJECT_CONTEXT.md](PROJECT_CONTEXT.md) and [the project map](PROJECT_MAP_RU.md) before changing gameplay. Older reports describe earlier snapshots.

## Code

- Add game logic to services and repositories under `src/`, with HTTP endpoints through the current front controller.
- Keep UI code in the current `views/` and `public/` layers. Reuse the existing design tokens and components.
- Preserve server-side authorization, CSRF, transaction boundaries and reward idempotency.
- Treat legacy PHP as compatibility/reference code. Prove that a file is unused before removing it.
- Keep unrelated changes out of a pull request. Preserve ongoing work in a shared checkout.

## Data

Use an isolated QA database for mutating tests. Back up and verify before schema or data mutations; run mutating suites sequentially. Keep quest progress and player assets intact.

Do not commit `.env`, database dumps, logs, browser profiles, credentials or local agent state. `.env.example` contains placeholders only.

## Checks

Run `php tools/ci_lint.php`, `php tools/encoding_guard.php`, and the relevant JavaScript syntax checks. Use [the QA guide](docs/TESTING.md) for behavior changes. Report skipped checks as skipped.

For UI changes, show the desktop/mobile result and keyboard behavior. For migrations, include backup/rollback notes. Link the issue and explain the resulting behavior in the PR description.
