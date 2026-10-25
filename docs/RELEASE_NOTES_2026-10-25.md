# Development snapshot · 25 October 2026

## World and interface

- New illustrated backgrounds are bound to all 155 database locations. Similar biomes share suitable scenes.
- Updated 13 NPC portraits, 20 route markers, seven reference icons and the two encyclopedia menu images.
- Corrected portraits used as landscapes, generic-town fallbacks in caves/deserts/transport, and mismatched gym elements.
- Pokémon sprites, inventory artwork and player-uploaded avatars remain unchanged.
- The current tree also contains DB-backed world readers, regional services, starter reward work and an introductory guide. These features have separate gameplay QA evidence; this publication does not certify every story or rollout state.

## Repository

- Added English/Russian project overviews, visual examples, setup and QA guides, and contribution/issue/PR templates.
- Added source CI for PHP 8.5, JavaScript syntax, Russian-text encoding and the PDO auditor's self-tests.
- Removed local archives, saved pages and downloaded staging copies from the published tree. Runtime/agent state, secrets and operational backups stay local.
- Kept legacy runtime and reference directories while the active game still depends on them.

This is a source publication, not a production deployment. Database credentials, player data, SMTP configuration and production feature flags are operator-managed.
