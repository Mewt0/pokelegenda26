# Release audit collaboration — 2026-10-24

The owner explicitly requested Codex to close the identified release blockers together with the locally installed DeepSeek Harness.

Your ownership: create ONLY docs/reports/RELEASE_HARNESS_REVIEW_2026-10-24.md. Read PROJECT_CONTEXT.md and AGENTS.md first. You are not alone: Codex owns quest security fixes, environment startup and all DB/testing operations. Do not edit existing dirty files, do not revert anything, do not run DB mutations, smoke suites, services, migrations, git commits or external communications. Never read or print credentials.

Perform a bounded read-only release review of: composer/runtime version mismatch, public temporary QA endpoints and deploy packaging, stale PvE reaper safety and its existing regression, docs/agent-tasks/BOARD.md discrepancies, and pending release-critical vs optional design tasks. Read the live code; old reports are not proof. Produce actionable findings with file:line, priority, precise suggested fixes, and clearly distinguish tested facts from suggestions. Existing environment was down at initial audit. PHP 8.1 and PHP 8.5.11 are installed; composer requires >=8.3; active branch php85-migration. Current quest_update API is known insecure and owned by Codex; do not change it.

Finish your bounded review and report. Do not expand into unrelated projects or use Codex CLI/Hybrid Engine. Return a concise summary and report path.
