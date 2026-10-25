# Documentation

Start with the current source and [PROJECT_CONTEXT.md](../PROJECT_CONTEXT.md). Historical audits describe the tree and environment at the time they ran.

| Guide | What it covers |
| --- | --- |
| [Local setup](GETTING_STARTED.md) | PHP, Apache, private configuration and the database baseline |
| [Testing](TESTING.md) | Source checks, isolated QA, backups and result reporting |
| [October development snapshot](RELEASE_NOTES_2026-10-25.md) | Current publication scope and limitations |
| [Contribution notes](../CONTRIBUTING.md) | Code boundaries, data preservation and PR expectations |
| [Project map](../PROJECT_MAP_RU.md) | Where to edit features, assets and services |
| [Technical structure](../PROJECT_STRUCTURE_RU.md) | A deeper map of the PHP and frontend layers |
| [Project context](../PROJECT_CONTEXT.md) | Architecture and dated operational evidence |
| [Migration journal](../REWRITE_STATUS.md) | Historical implementation notes |

The public snapshot excludes runtime backups, raw QA output, browser state and the agent message bus. Operator task records remain in the local working project. A successful source check does not replace database-backed or browser gameplay tests.
