# Chat QA Static Review — 2026-10-24

## Scope and method
- Static-only review of `src/Game/ChatService.php`, `src/Repository/ChatRepository.php`, `src/Controller/ChatApiController.php`, and `public/js/chat.js`. No HTTP, browser, DB, shell, tests, credentials, or other files. No source edits.
- General/trade/battle/clan/private were traced through code only; none were executed, so findings are static hypotheses, not reproductions.
- The initial smoke mismatch (Tacos=room4, NIGA=room13) is a temporary QA-fixture alignment issue with `finally` restoration. It is not treated as product evidence or a product bug.
- One pass, five findings maximum.

## Findings
1. **High — clan history follows current membership, not send-time clan.** The repository selects each user's *current* `clanid` for author and viewer (`ChatRepository.php:29`), and the service filters type 5 by current-clan equality (`ChatService.php:31-37`). Therefore leaving a clan hides all old clan history, joining exposes pre-join messages, and an author switching clans makes their old messages appear to the new clan. Persist a `clan_id` snapshot on each clan message and filter by membership-at-send or a join epoch.
2. **Medium — room 0 can broadcast non-system chat into every room.** The non-private predicate accepts `c.room = 0` for all message types (`ChatRepository.php:36`), while sends take `buildmy` without clamping (`ChatApiController.php:38,141`; `ChatService.php:123-131`). If a user state has `buildmy=0`, ordinary messages are stored in room 0 and become visible in every room. Restrict room 0 to system messages or force `roomId >= 1` for player sends.
3. **Medium — private sends accept nonexistent recipients.** Validation checks only `userto > 0` and not-self (`ChatService.php:112-121`); there is no user lookup before insert (`:123`). The row persists and is returned to the sender through the author branch (`ChatRepository.php:37`); a null `to_name` renders as an empty `-> ` (`chat.js:1000-1004`). Resolve the recipient server-side and reject unknown or blocked IDs.
4. **Medium — no unread model, and the private tab mixes all conversations.** No unread count/state exists in the reviewed code. The initial `sync_only` response returns only a max ID (`ChatApiController.php:39-44`); the client advances `lastId` and discards the history (`chat.js:914-917`), so unseen private messages present at load trigger no notification. Private rendering filters only `msg.private` (`chat.js:955-958`), so choosing a recipient does not isolate that conversation from other peers. This is a QA gap in unread counts and private-channel isolation.
5. **Medium — clan chat is scoped to the current game room.** The non-private room predicate also applies to type 5 (`ChatRepository.php:36`), and the send path uses `buildmy` (`ChatApiController.php:141`). Clan members in different rooms cannot see each other's clan messages. If the clan channel is intended to be clan-wide, add an explicit clan scope; otherwise confirm this is intended.

## Safe rendering
- User-controlled author names, message text, mention labels, and card fields are written with `textContent` or created DOM nodes. Mention markers are tokenized, and invalid markers are replaced with a plain-text placeholder (`chat.js:1025-1066`); static UI strings are the only `innerHTML` uses. Sprite URLs are constrained to root-relative paths (`chat.js:880-882`).
- No static XSS path was found within these four files. Residual rendering risk would come from consumers outside this review, such as `PokemonSocial.notify`.

## Verification limits
- No runtime evidence exists for any channel, including all PM recipient cases, clan join/leave history, unread counts, or room switching.
- Each finding needs targeted HTTP/browser/DB verification under the separate QA owner; this review must not be read as a pass of the smoke suite.
- Only this report was written; stop.
