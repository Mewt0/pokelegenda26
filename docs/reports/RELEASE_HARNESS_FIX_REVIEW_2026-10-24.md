# Independent fix review — bounded (2026-10-24)

Scope: only `NpcDialogService::questStartAction()/questUpdateAction()`, `QuestRepository::advanceNarrativeStep()`, and `StalePveBattleReaper.php`. No shell, DB, HTTP, services, or credentials were used. Code inspection only.

Verdict: no P0/P1 or quest-state corruption path found in the named units. Two concrete defects and one unproven concurrency risk remain.

## 1. Low — completed/past narrative acknowledgements return a false failure

- Where: `NpcDialogService.php:1556`, `QuestRepository.php:88-93`.
- Repro: complete quest 1, then talk to the passerby at location 1 / `quest_npc=2`. The dialog still offers `quest_update:1:3:0` (`NpcDialogService.php:957`). The UPDATE affects 0 rows; the fallback requires `gotov=0 && process===3`, but the completed row has `gotov=1` (process 10), so the player gets "Сначала выполни предыдущий этап." The same false error occurs after starter selection sets process 4 while the passerby button remains, and on stale Amira/Airen buttons at process 6/7.
- Impact: state is safe, but a legitimate click is rejected with a false diagnostic; the acknowledgement path is not idempotent once the quest moves past that step.
- Fix: on 0 rows, distinguish "already recorded / quest completed" from "wrong step" (for example, return a tri-state, or treat `gotov=1` or `process >= next` as a benign no-op for narrative acknowledgements). Hiding stale choices is UI work and was not assessed.

## 2. Low–Medium — reaper's declared fallback TTL is never applied

- Where: `StalePveBattleReaper.php:12-14`, `:33`, `:96-103`, `:106-112`.
- Fact: `FALLBACK_TTL_SECONDS` (7200) is only copied to the result at line 102. No query predicate or `isExpired()` branch uses it. The candidate query treats `times <= 0` (and `times < now-10y`) as stale immediately; `isExpired()` does the same. The docblock calls 7200 the fallback lifetime for missing/zero/implausibly old timestamps.
- Impact: if a live PvE row can transiently have `times=0`, it is closed on the next pass with no two-hour grace; the result contract also advertises a fallback the reaper did not use. If zero-time rows are always genuinely stale, this is dead/misleading code rather than a live-data bug.
- Fix: apply an actual fallback (requires a creation timestamp, otherwise two-hour age cannot be computed) or remove/rename the constant and correct the comment/result contract.

## 3. Residual concurrency risk (not confirmed) — closing UPDATE does not re-assert boss exclusion

- Where: `StalePveBattleReaper.php:61-72`.
- Fact: the locked recheck excludes `boss_battle_sessions`, but the closing UPDATE filters only `id/user_1/batl_tip/pobeda`. If a boss session can be inserted after the recheck and before/around the UPDATE, the battle can still be closed and the user's PvE pointer cleared. Whether the `FOR UPDATE` row lock makes that interleaving impossible depends on the boss-session insert path, which is outside this bounded review.
- Fix: make the mutation self-guarding and enforce the invariant at session creation (lock/unique/FK); add a concurrent interleaving test.

## Verified by inspection (not runtime)

- Ten mappings: all location/NPC pairs match the intended NPC map (Oak 3/1, Spike 1/3, Steve 5/4, Carol 6/5, Jafit 10/8; passerby 1/2, old woman 7/1, Amira 11/1, Airen 11/2, Articuno 13/1). Quest ids and process targets match the action names.
- Expected-process update is atomic at row level: `... WHERE user_id=:user AND quest_id=:quest AND process=:expected AND gotov=0 LIMIT 1`; exact-repeat clicks are idempotent.
- Boss sessions are excluded at discovery (`:34`) and at the locked recheck (`:61`).
- Reaper clears `helping_hand` rows and active transformations; the PvP flag is preserved while the matching PvE pointer is cleared. Transaction guard and rollback/rethrow behavior are coherent.
- No client-supplied quest id/process reaches SQL in these units; action strings are allow-listed.

## Not run / not verified

- No PHP lint, smoke62, FPE25, smoke15, full29, integrity run, HTTP/API call, DB query, concurrency test, or PHP 8.5.11 check. Pass counts in the brief are context, not independently verified.
- `questStartAction` delegates to `createIfMissing()`/`nextId()`; the atomicity of that helper was not part of this bounded review, so this is not a clean bill of health for concurrent starts.
