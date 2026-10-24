# Independent Release UI Review — 2026-10-24

**Scope:** only `public/js/{mail-overlay,trainer-profile-window,profile-settings-modal,ui-overlay,ui-preferences}.js` and `public/css/{mail-overlay,trainer-profile-window,profile-settings-modal,tokens}.css`.
**Mode:** static code/CSS review only. No product file was edited, reverted, committed or moved.
**Test boundary:** no browser, Playwright/CDP, viewport resize, keyboard traversal, network-race reproduction, JS/PHP lint, HTTP smoke, DB access or long-session test was executed. The failing shell was not retried. Every finding below is static; none is a reproduced runtime defect. Read-only context: `PROJECT_CONTEXT.md`, `AGENTS.md`, `views/components/mail-panel.php`, `views/game-start.php:486`, `public/css/base.css:7-11`.
**Severity:** P1 = normal-flow data loss or false state; P2 = keyboard/mobile/accessibility defect with a workaround; P3 = consistency/polish.

## Findings

### P1-1 — Unsaved profile-settings edits are silently discarded
**Evidence:** `public/js/profile-settings-modal.js:95-99` rebuilds the section on every nav click; `:144-157` replaces `content.innerHTML`; `:177-196` holds the 500-char bio; `:109-116` reloads on every open. There is no dirty flag or confirmation.
**Impact:** edit bio/account fields, click another section (or close/reopen), and the edits are gone. Saving only the current section makes silent loss easy.
**Fix:** track dirty fields, preserve a per-section draft, and confirm before section switch/close; clear drafts only after a successful save of that section.

### P1-2 — Settings GET/POST responses can overwrite newer edits or saved state
**Evidence:** `public/js/profile-settings-modal.js:124-142` has no `AbortController`/sequence; `:109-116` leaves the previous form editable while loading; `:247-284` applies a save response without a revision guard.
**Impact:** on a slow reopen, typing during the GET is wiped by `renderSection()`; a GET that started before a save can resolve after it and replace `settings`, re-rendering stale values. A load failure leaves old `settings` and an enabled Save button.
**Fix:** abort/ignore stale loads with a revision id; render a loading state and disable fields/Save until the GET settles; tag save results and apply only the newest response.

### P1-3 — Mobile settings window can clip its own Save button
**Evidence:** `public/css/profile-settings-modal.css:20-21` uses `100vh` and a `430px` min-height; `:237-244` is a non-wrapping flex footer; `:263-268` gives each action `min-width:128px`; `:319-323` does not change the footer or the fixed `74px` header row.
**Impact:** at 320px viewport the window is `288px` wide; with 2px borders and 32px footer padding the content is `252px`, while two 128px buttons plus two 8px gaps need `272px`. The Save button clips under the window's `overflow:hidden`. `100vh` also overshoots the visible viewport on mobile browsers with dynamic chrome.
**Fix:** use `100dvh` with a `100vh` fallback; at ≤380px stack/wrap the footer and drop the 128px minimum; let the header row grow and reduce its horizontal padding.

### P2-1 — Dark theme is applied too late to prevent a light first paint
**Evidence:** `public/js/ui-preferences.js:108-114` applies the localStorage cache only when the script executes; `views/game-start.php:486` loads that script at the bottom of `<body>`, and no server-rendered `data-theme` attribute was found in views.
**Impact:** a dark-theme player can see the light palette for the first paint on every navigation, contradicting the no-flash intent in `ui-preferences.js:4-6`. Static paint-timing risk; not observed in a browser here.
**Fix:** bootstrap the cached theme from a tiny inline `<head>` script (or render `data-theme` server-side), then let `ui-preferences.js` reconcile with the API.

### P2-2 — Shift+Tab can escape the trainer-profile focus trap
**Evidence:** `public/js/trainer-profile-window.js:28` gives the dialog `tabindex="-1"`; `:215` focuses that dialog. `public/js/ui-overlay.js:29-36` excludes `tabindex="-1"` from focusables; `:138-143` only checks `current.contains(active)`, so focus on the dialog is considered “inside” and is not redirected.
**Impact:** immediately after opening a profile, Shift+Tab moves to the previous focusable element outside the modal. Keyboard users can leave the dialog while `aria-modal` is active.
**Fix:** treat `dialogOf(current)` as the backward/forward boundary, or include the dialog sentinel in the trap list.

### P2-3 — Saving from the nested settings modal loses focus on close
**Evidence:** `public/js/trainer-profile-window.js:532-536` passes `onSaved: () => loadProfile(...)`; `:518-540` then empties and rebuilds `[data-trainer-social]`, replacing the “Настройки” button that `ui-overlay` saved as the return target. `public/js/ui-overlay.js:94` skips restoration when `document.contains(restore)` is false.
**Impact:** after save + close, focus falls to `<body>` instead of the profile trigger; keyboard/screen-reader position is lost.
**Fix:** restore to a stable element (the profile dialog or a non-rebuilt trigger), or have `renderSocialActions` preserve the trigger node; add a fallback to focus the current dialog.

### P2-4 — Full re-renders destroy focus in mail and settings
**Evidence:** `public/js/mail-overlay.js:230-244` rebuilds the list via `innerHTML` on each selection; `:477-483` does not restore focus. `public/js/profile-settings-modal.js:80-101` rebuilds all nav buttons on every tab click; `:95-99` then rerenders the section.
**Impact:** mouse users are unaffected, but keyboard users lose their position after selecting a mail or switching a settings tab; screen readers must re-discover the widget.
**Fix:** preserve focused keys and refocus the equivalent new node after render, or update existing nodes/classes instead of replacing them.

### P2-5 — Existing mail draft can be silently re-addressed
**Evidence:** `public/js/mail-overlay.js:375-386` (reply) overwrites recipient and subject but keeps the current body; `:409-413` (`open({recipient})`) overwrites only the recipient; `:443-446` (“К письмам”) keeps the draft instead of discarding it.
**Impact:** a body written for one recipient can be sent to another after clicking a mail link or replying; “cancel” can later resurface stale content.
**Fix:** detect a non-empty draft and require an explicit discard/keep choice before changing recipient or subject; make cancel semantics explicit (clear or “minimize” with a visible draft indicator).

### P3-1 — Trainer social actions allow duplicate requests and reload after close
**Evidence:** `public/js/trainer-profile-window.js:553-572` creates enabled action buttons; `:599-621` has no in-flight guard and calls `loadProfile(currentProfileOptions)` after the await; `:218-233` aborts profile loads but not the social action.
**Impact:** double-click can emit duplicate friend/battle/trade requests; an action completing after close still starts a hidden profile GET and DOM render.
**Fix:** keep a pending action key, disable all action buttons until it settles, and guard the reload with `overlay.classList.contains('is-open')`.

### P3-2 — Overlay z-indexes bypass the token scale
**Evidence:** `public/css/mail-overlay.css:9` uses `96000`; `public/css/trainer-profile-window.css:4` uses `120000`; `public/css/profile-settings-modal.css:8` uses `130000`. `public/css/tokens.css:91-98` says arbitrary z-index is forbidden and defines only 0–60.
**Impact:** stacking is undocumented and will drift as new overlays/toasts are added; the profile/settings pair depends on three unrelated magic numbers.
**Fix:** add named layer tokens with a documented order (e.g. mail < profile < nested settings < toast) and consume them here.

## Focus-area conclusion

- **Dark theme:** static color audit found no hard-coded light colors in the four component CSS files; all surfaces/text/border/accent states used here resolve through `tokens.css`, and the dark block (`tokens.css:108-150`) defines every role used. Contrast was not measured. The remaining dark-theme issue is the first-paint timing in P2-1.
- **Mobile overflow:** mail and trainer-profile use `100dvh` plus internal scroll containers and show no obvious static 390px horizontal overflow in their reviewed rules. The concrete defect is the settings footer/`100vh` case in P1-3; actual rendering at 320/390 was not executed. `tokens.css:152-155` (`zoom:1.12` at `ui-size=large`) also deserves a runtime check against these `100vw`/`100dvh` overlays.
- **Request races:** mail’s load path is versioned and aborts the previous request (`mail-overlay.js:280-283`, `:294-320`); trainer’s card load is sequence-guarded (`trainer-profile-window.js:242-288`). The defects are settings (P1-2) and social actions (P3-1), not the mail card GET.
- **Nested modal keyboard/focus:** Escape/stack handling is coherent, but P2-2, P2-3 and P2-4 break focus containment or restoration.
- **Draft loss:** P1-1 (settings) and P2-5 (mail) are the two paths that silently destroy or misapply user-entered content.

**Executed tests:** none. No browser or long-session claim is made; no runtime, visual, keyboard, mobile, network, DB or server result is asserted in this report.
