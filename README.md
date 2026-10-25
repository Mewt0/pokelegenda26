<!-- Repository cover is local and renders without third-party image services. -->
<p align="center"><img src="docs/media/github-banner.svg" alt="PokemonChic — a browser RPG in the Pokémon world" width="100%"></p>

<p align="center">
  <a href="https://github.com/Mewt0/pokelegenda26/actions/workflows/ci.yml"><img src="https://github.com/Mewt0/pokelegenda26/actions/workflows/ci.yml/badge.svg?branch=main" alt="Source checks"></a>
  <img src="https://img.shields.io/badge/PHP-8.5-777BB4?style=flat-square&logo=php&logoColor=white" alt="PHP 8.5">
  <img src="https://img.shields.io/badge/MySQL-5.5_compatible-4479A1?style=flat-square&logo=mysql&logoColor=white" alt="MySQL 5.5 compatible">
  <img src="https://img.shields.io/badge/JavaScript-vanilla-F7DF1E?style=flat-square&logo=javascript&logoColor=222" alt="Vanilla JavaScript">
  <img src="https://img.shields.io/badge/status-active_development-537A4D?style=flat-square" alt="Active development">
</p>

<p align="center">
  <a href="README_RU.md">Русский</a> ·
  <a href="docs/GETTING_STARTED.md">Local setup</a> ·
  <a href="docs/TESTING.md">Testing</a> ·
  <a href="#under-the-hood">Architecture</a> ·
  <a href="https://github.com/Mewt0/pokelegenda26/issues">Issues</a>
</p>

PokemonChic is a browser RPG with Pokémon teams, PvE and PvP battles, quests, trading and a connected world. This repository contains the PHP/MySQL game and its ongoing migration from legacy pages to a JSON API and a responsive JavaScript interface.

## A look around the world

<table>
  <tr>
    <td width="50%"><img src="img/room/generated-20261025/alabastia.webp" alt="Alabastia, the starting town"><br><strong>A place to begin</strong><br>Alabastia and the professor’s laboratory.</td>
    <td width="50%"><img src="img/room/generated-20261025/forest.webp" alt="A wooded route"><br><strong>Beyond the town</strong><br>Forests, caves, coastal routes and regional towns.</td>
  </tr>
  <tr>
    <td><img src="img/room/generated-20261025/arena-water.webp" alt="A water-type battle arena"><br><strong>Battle arenas</strong><br>Distinct environments for eight gym types.</td>
    <td><img src="img/room/generated-20261025/port.webp" alt="A sunny harbor"><br><strong>Travel between regions</strong><br>Ports, ferries and flights connect the world.</td>
  </tr>
</table>

## What you can do

| System | In the game |
| --- | --- |
| Explore | Move between locations, meet NPCs, follow quests and travel by ship or plane. |
| Build a team | Catch Pokémon, manage the team and nursery, hatch eggs and use breeding systems. |
| Battle | Fight wild Pokémon or other trainers, use moves and items, and review battle history. |
| Trade | Use the inventory, shops and commission market for items, Pokémon and eggs. |
| Stay connected | Chat, mail, friends, trainer cards, tournaments and game events. |
| Run the world | A Game Master Center provides content, moderation, grants and QA tools. |

The latest art refresh connects **155 locations** to illustrated backgrounds and adds **42 NPC, route and reference icons**. Shared biomes reuse suitable art. Pokémon sprites and inventory artwork stay unchanged.

## Under the hood

```text
Browser /game
    │
    ├── PHP views + vanilla JavaScript / CSS
    │
    └── JSON API → Controllers → Game services → Repositories → MySQL
```

| Path | Purpose |
| --- | --- |
| `public/index.php` | API and game front controller |
| `src/Controller/` | HTTP endpoints |
| `src/Game/`, `src/Service/` | Battles, NPCs, rewards and gameplay services |
| `src/Repository/` | Database access |
| `views/`, `public/js/`, `public/css/` | Game interface |
| `database/migrations/` | Incremental schema and content migrations |
| `tools/` | Backup, migration, audit and regression commands |
| `img/`, `pok/`, `public/img/` | World, Pokémon and interface assets |

The root landing page and some compatibility paths still use legacy files. Keep `include/`, `admin/` and root PHP files until their runtime dependencies have been retired.

## Work on the project

Use **PHP 8.5**, Apache with `mod_rewrite`, and a MySQL-compatible database. The current local installation uses MySQL 5.5. PDO MySQL and mbstring are required; QA and art tooling also use curl, GD and zip.

```bash
git clone https://github.com/Mewt0/pokelegenda26.git
cd pokelegenda26
cp .env.example .env
```

Configure a private `.env` and an authorized game database, then follow [local setup](docs/GETTING_STARTED.md). Migrations extend the existing game schema; the repository does not distribute player databases or credentials.

For checks that need no database:

```bash
php tools/ci_lint.php
php tools/encoding_guard.php
php tools/pdo_param_audit.php --self-test
```

Database-backed suites, backups and QA accounts are covered in [Testing](docs/TESTING.md).

## Current scope

This is an active development snapshot. Source checks in GitHub Actions validate syntax and selected static safeguards. They do not certify a production deployment, all quest stories, economy balance or long PvP sessions.

See [the architecture overview](#under-the-hood), [local setup](docs/GETTING_STARTED.md) and [testing instructions](docs/TESTING.md) to work on the game.

## Contributing

Open an issue with the page or location, steps to reproduce, expected behavior and a screenshot when useful. Include the checks you ran in a pull request. [Development notes](docs/GETTING_STARTED.md#development) describe the code and data boundaries.

Third-party artwork, characters and bundled components retain their respective rights and licenses.
