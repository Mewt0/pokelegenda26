# PHP health report

- Generated: `2026-10-09T07:50:29+03:00`
- Root: `E:/OSPanel/domains/pokemonchic.com`
- PHP: `E:/OSPanel/modules/php/PHP_8.1/php.exe`

## Summary

| Area | Checked | Failed |
|---|---:|---:|
| New layer | 174 | 0 |
| Legacy | 263 | 1 |

## New Layer Lint Failures

| File | Error |
|---|---|
| - | No failures. |

## Legacy Lint Failures

| File | Error |
|---|---|
| `include/function/Services_JSON.php` | Fatal error: Array and string offset access syntax with curly braces is no longer supported in E:\OSPanel\domains\pokemonchic.com\include\function\Services_JSON.php on line 156 Errors parsing E:\OSPanel\domains\pokemonchic.com\include\function\Services_JSON.php |

## Error Log Top Groups

- Log exists: `yes`
- Entries parsed: `41975`, unparsed lines: `103514`
- Layer split: new_layer=`0`, legacy=`41975`, unknown=`0`

| Count | First | Last | Type | File | Message |
|---:|---|---|---|---|---|
| 20693 | 03-May-2026 05:25:54 Europe/Moscow | 03-May-2026 05:51:13 Europe/Moscow | Warning | `include/function/fanction.games.post.php` | include(include/itemsinpage.class.php): Failed to open stream: No such file or directory |
| 20693 | 03-May-2026 05:25:54 Europe/Moscow | 03-May-2026 05:51:13 Europe/Moscow | Warning | `include/function/fanction.games.post.php` | include(): Failed opening '<value>' for inclusion (include_path='<value>') |
| 569 | 03-May-2026 05:14:11 Europe/Moscow | 03-May-2026 14:57:26 Europe/Moscow | Warning | `include/function/fanction.games.php` | Trying to access array offset on value of type bool |
| 14 | 03-May-2026 05:23:06 Europe/Moscow | 03-May-2026 15:14:17 Europe/Moscow | Warning | `include/config.room.php` | Trying to access array offset on value of type bool |
| 4 | 09-Oct-2026 06:40:10 Europe/Prague | 09-Oct-2026 06:40:10 Europe/Prague | Warning | `Command line code` | Array to string conversion |
| 1 | 26-May-2026 04:18:37 Europe/Prague | 26-May-2026 04:18:37 Europe/Prague | Warning | `Standard input code` | Pokemon8\Game\BattleEngineService::damageMove(): Argument #<N> ($defender) must be passed by reference, value given |
| 1 | 03-May-2026 04:34:31 Europe/Prague | 03-May-2026 04:34:31 Europe/Prague | Warning | `convert_db.php` | Undefined array key <N> |

## Error Log Top Files

| File | Count |
|---|---:|
| `include/function/fanction.games.post.php` | 41386 |
| `include/function/fanction.games.php` | 569 |
| `include/config.room.php` | 14 |
| `Command line code` | 4 |
| `convert_db.php` | 1 |
| `Standard input code` | 1 |

## Message Types Top 30

| Message | Count |
|---|---:|
| `Warning: include(include/itemsinpage.class.php): Failed to open stream: No such file or directory` | 20693 |
| `Warning: include(): Failed opening '<value>' for inclusion (include_path='<value>')` | 20693 |
| `Warning: Trying to access array offset on value of type bool` | 583 |
| `Warning: Array to string conversion` | 4 |
| `Warning: Undefined array key <N>` | 1 |
| `Warning: Pokemon8\Game\BattleEngineService::damageMove(): Argument #<N> ($defender) must be passed by reference, value given` | 1 |

## Focus Buckets

### Fatal error

| Count | First | Last | Type | File | Message |
|---:|---|---|---|---|---|
| 0 | - | - | - | - | - |

### Uncaught

| Count | First | Last | Type | File | Message |
|---:|---|---|---|---|---|
| 0 | - | - | - | - | - |

### Deprecated

| Count | First | Last | Type | File | Message |
|---:|---|---|---|---|---|
| 0 | - | - | - | - | - |

### Warning: Undefined

| Count | First | Last | Type | File | Message |
|---:|---|---|---|---|---|
| 1 | 03-May-2026 04:34:31 Europe/Prague | 03-May-2026 04:34:31 Europe/Prague | Warning | `convert_db.php` | Undefined array key <N> |

### Trying to access array offset

| Count | First | Last | Type | File | Message |
|---:|---|---|---|---|---|
| 569 | 03-May-2026 05:14:11 Europe/Moscow | 03-May-2026 14:57:26 Europe/Moscow | Warning | `include/function/fanction.games.php` | Trying to access array offset on value of type bool |
| 14 | 03-May-2026 05:23:06 Europe/Moscow | 03-May-2026 15:14:17 Europe/Moscow | Warning | `include/config.room.php` | Trying to access array offset on value of type bool |

## Candidates For Fixing

| File | Reason | Estimate |
|---|---|---|
| - | No new-layer candidates found in lint failures or top log groups. | - |

## Legacy - Do Not Touch

- Legacy lint failures: `1` / `263`.
- Legacy log entries: `41975`.
