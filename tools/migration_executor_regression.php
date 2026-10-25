<?php
declare(strict_types=1);

use Pokemon8\Database\Connection;
use Pokemon8\Support\Env;

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Run from CLI.\n");
    exit(2);
}

// Regression for R1: use the real production executor, not a copy.
// migration_status.php skips its CLI entry point when this constant is defined.
define('POKEMON8_MIGRATION_STATUS_LIBRARY', true);
require dirname(__DIR__) . '/tools/migration_status.php';

exit(r1Main());

function r1ParseArgs(array $argv): array
{
    $options = [];
    foreach (array_slice($argv, 1) as $arg) {
        if (!str_starts_with($arg, '--')) {
            continue;
        }
        $arg = substr($arg, 2);
        $eq = strpos($arg, '=');
        if ($eq === false) {
            $options[$arg] = '1';
        } else {
            $options[substr($arg, 0, $eq)] = substr($arg, $eq + 1);
        }
    }
    return $options;
}

function r1AppsConfig(): array
{
    Env::load(APP_ROOT . '/.env');
    return require APP_ROOT . '/config/database.php';
}

function r1Connect(array $config, string $schema): PDO
{
    return new PDO(
        sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $config['host'], (int) $config['port'], $schema),
        (string) $config['username'],
        (string) $config['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_MULTI_STATEMENTS => false,
        ]
    );
}

function r1Assert(array &$results, string $name, bool $ok, string $details = ''): void
{
    $results[] = ['name' => $name, 'ok' => $ok, 'details' => $details];
}

function r1MigrationFiles(): array
{
    $names = [
        '2026_10_25_000004_mysql84_items_unique_id.sql',
        '2026_10_25_000005_mysql84_items_poke_id_hardening.sql',
        '2026_10_25_000006_mysql84_news_date_policy.sql',
        '2026_10_25_000007_mysql84_items_poke_id_sequence.sql',
    ];
    $files = [];
    foreach ($names as $name) {
        $path = APP_ROOT . '/database/migrations/' . $name;
        if (!is_file($path)) {
            throw new RuntimeException('Missing migration fixture: ' . $name);
        }
        $files[] = ['name' => $name, 'path' => $path, 'checksum' => hash_file('sha256', $path) ?: ''];
    }
    return $files;
}

function r1FixtureFresh(PDO $db): void
{
    $db->exec('CREATE TABLE `items` (
        `id` INT NOT NULL,
        `name` VARCHAR(100) NOT NULL,
        KEY `idx_items_id` (`id`)
    ) ENGINE=InnoDB');
    $db->exec("INSERT INTO `items` (`id`, `name`) VALUES (1, 'Potion'), (2, 'Antidote')");

    $db->exec('CREATE TABLE `items_poke` (
        `id_itp` INT NOT NULL,
        `id_poke` INT NOT NULL,
        `id_items` INT NOT NULL,
        `datetime` VARCHAR(15) NOT NULL DEFAULT "not"
    ) ENGINE=InnoDB');
    $db->exec("INSERT INTO `items_poke` (`id_itp`, `id_poke`, `id_items`, `datetime`) VALUES
        (0, 900, 1, 'not'),
        (0, 901, 2, 'not'),
        (5, 902, 1, 'not')");

    $db->exec("CREATE TABLE `news` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `date` DATE NOT NULL DEFAULT '2026-10-25',
        `title` VARCHAR(100) NOT NULL DEFAULT '',
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB");
}

function r1FixtureHardened(PDO $db): void
{
    $db->exec('CREATE TABLE `items` (
        `id` INT NOT NULL,
        `name` VARCHAR(100) NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB');
    $db->exec('CREATE TABLE `items_poke` (
        `id_itp` INT NOT NULL AUTO_INCREMENT,
        `id_poke` INT NOT NULL,
        `id_items` INT NOT NULL,
        `datetime` VARCHAR(15) NOT NULL DEFAULT "not",
        PRIMARY KEY (`id_itp`)
    ) ENGINE=InnoDB');
    $db->exec('CREATE TABLE `news` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `date` DATE NULL DEFAULT NULL,
        `title` VARCHAR(100) NOT NULL DEFAULT \'\',
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB');
    $db->exec('CREATE TABLE `items_poke_id_sequence` (
        `id` TINYINT NOT NULL PRIMARY KEY,
        `next_id` INT NOT NULL
    ) ENGINE=InnoDB');
    $db->exec('INSERT INTO `items_poke_id_sequence` (`id`, `next_id`) VALUES (1, 0)');
}

function r1FixtureDuplicate(PDO $db): void
{
    $db->exec('CREATE TABLE `items` (
        `id` INT NOT NULL,
        `name` VARCHAR(100) NOT NULL,
        KEY `idx_items_id` (`id`)
    ) ENGINE=InnoDB');
    $db->exec("INSERT INTO `items` (`id`, `name`) VALUES (1, 'Potion'), (1, 'Duplicate'), (2, 'Antidote')");
    $db->exec('CREATE TABLE `items_poke` (
        `id_itp` INT NOT NULL,
        `id_poke` INT NOT NULL,
        `id_items` INT NOT NULL,
        `datetime` VARCHAR(15) NOT NULL DEFAULT "not"
    ) ENGINE=InnoDB');
    $db->exec('CREATE TABLE `news` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `date` DATE NOT NULL DEFAULT \'2026-10-25\',
        `title` VARCHAR(100) NOT NULL DEFAULT \'\',
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB');
}

function r1RunApply(PDO $db, array $files): array
{
    try {
        applyPendingMigrations($db, $files);
        return ['ok' => true, 'error' => null, 'snapshot' => buildSnapshot($db, $files)];
    } catch (Throwable $e) {
        return ['ok' => false, 'error' => $e->getMessage(), 'snapshot' => buildSnapshot($db, $files)];
    }
}

function r1Registry(PDO $db): array
{
    return $db->query('SELECT `migration`, `status`, `note` FROM `schema_migrations` ORDER BY `migration`')->fetchAll() ?: [];
}

function r1Scalar(PDO $db, string $sql): mixed
{
    return $db->query($sql)->fetchColumn();
}

function r1ScenarioSchema(array $config, PDO $server): array
{
    $schema = 'pokemonchic84_r1_' . bin2hex(random_bytes(6));
    if (!preg_match('/\Apokemonchic84_r1_[a-f0-9]{12}\z/D', $schema)) {
        throw new RuntimeException('Unexpected disposable schema name.');
    }
    $server->exec('CREATE DATABASE `' . $schema . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    try {
        $db = r1Connect($config, $schema);
        bootstrapMigrationTables($db);
        return [$schema, $db];
    } catch (Throwable $e) {
        r1DropSchema($server, $schema);
        throw $e;
    }
}

function r1DropSchema(PDO $server, string $schema): void
{
    if (preg_match('/\Apokemonchic84_r1_[a-f0-9]{12}\z/D', $schema)) {
        $server->exec('DROP DATABASE `' . $schema . '`');
    }
}

function r1LiteralQuestionMarkScenario(array $config, PDO $server, array &$results): void
{
    // 2026_05_08_000004 contains literal '?' strings inside REPLACE(...). It is
    // already applied on the lab, so the four-file scenarios above never prepare
    // it. Exercising its first statement proves prepare() does not mistake the
    // quoted '?' for a bind placeholder.
    $schema = null;
    try {
        [$schema, $db] = r1ScenarioSchema($config, $server);
        $db->exec('CREATE TABLE `attac_power` (
            `atac_id` INT NOT NULL,
            `atac_name` VARCHAR(200) NOT NULL,
            `atac_tip` VARCHAR(200) NOT NULL DEFAULT \'\',
            `atac_categori` INT NOT NULL DEFAULT 0,
            `atac_pp` INT NOT NULL DEFAULT 0,
            `atac_power` INT NOT NULL DEFAULT 0,
            `atac_accuracy` INT NOT NULL DEFAULT 0,
            `atac_goal` INT NOT NULL DEFAULT 0,
            `atac_tittle` TEXT NULL,
            `tittle_effect` TEXT NULL,
            `atac_not` INT NOT NULL DEFAULT 0,
            `critic` INT NOT NULL DEFAULT 0,
            `chans_dop` INT NOT NULL DEFAULT 0,
            `chans_effect` INT NOT NULL DEFAULT 0,
            `cool_attak` INT NOT NULL DEFAULT 0,
            `cool_attak2` INT NOT NULL DEFAULT 0,
            `dop_effect` INT NOT NULL DEFAULT 0,
            `priorety` INT NOT NULL DEFAULT 0,
            `stati` INT NOT NULL DEFAULT 0,
            `attac_effecti` INT NOT NULL DEFAULT 0,
            `titles` TEXT NULL
        ) ENGINE=InnoDB');

        $file = APP_ROOT . '/database/migrations/2026_05_08_000004_import_missing_attacks.sql';
        $statements = splitSqlStatements((string) file_get_contents($file));
        $first = (string) ($statements[0] ?? '');
        r1Assert($results, 'literal_question.statement_found', $first !== '', 'statements=' . count($statements));
        if ($first === '') {
            return;
        }

        try {
            executeMigrationStatement($db, $first);
            r1Assert($results, 'literal_question.execute_succeeds', true, 'quoted ? did not become a placeholder');
        } catch (Throwable $e) {
            r1Assert($results, 'literal_question.execute_succeeds', false, $e->getMessage());
            return;
        }

        $inserted = (int) r1Scalar($db, "SELECT COUNT(*) FROM `attac_power` WHERE `atac_id` = 560 AND `atac_name` = 'Aromatic Mist'");
        r1Assert($results, 'literal_question.row_inserted', $inserted === 1, 'rows=' . $inserted);
    } catch (Throwable $e) {
        r1Assert($results, 'literal_question.exception', false, $e->getMessage());
    } finally {
        if ($schema !== null) {
            r1DropSchema($server, $schema);
        }
    }
}

function r1Main(): int
{
    global $argv;
    $options = r1ParseArgs($argv);
    $config = r1AppsConfig();
    if ((int) ($config['port'] ?? 0) !== 3384) {
        throw new RuntimeException('Refusing to run: expected the isolated lab on port 3384.');
    }
    $server = Connection::make($config);
    $serverInfo = $server->query('SELECT VERSION() AS version, @@port AS port')->fetch();
    if (!is_array($serverInfo)
        || !str_starts_with((string) ($serverInfo['version'] ?? ''), '8.4.')
        || (int) ($serverInfo['port'] ?? 0) !== 3384) {
        throw new RuntimeException('Refusing to run: expected MySQL 8.4.13 on port 3384.');
    }
    if (!preg_match('/\Apokemonchic84_\d{8}_\d{6}_[a-f0-9]{6}\z/D', (string) $config['database'])) {
        throw new RuntimeException('Refusing to run outside the isolated lab schema.');
    }

    $files = r1MigrationFiles();
    $results = [];
    $tempDir = dirname(__DIR__) . '/storage/tmp/r1_' . bin2hex(random_bytes(6));

    // A. First application on a legacy fixture, then the idempotent rerun.
    $schemaA = null;
    try {
        [$schemaA, $db] = r1ScenarioSchema($config, $server);
        r1FixtureFresh($db);
        $first = r1RunApply($db, $files);
        r1Assert($results, 'fresh.apply_succeeds', $first['ok'] === true, (string) $first['error']);
        if ($first['ok']) {
            $snapshot = $first['snapshot'];
            r1Assert(
                $results,
                'fresh.registry_4_applied',
                (int) $snapshot['applied_migrations'] === 4
                    && (int) $snapshot['pending_migrations'] === 0
                    && (int) $snapshot['failed_migrations'] === 0
                    && (int) $snapshot['dirty_migrations'] === 0,
                json_encode($snapshot)
            );
            $itemsUnique = (int) r1Scalar($db, "SELECT COUNT(*) FROM (SELECT INDEX_NAME FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'items' AND NON_UNIQUE = 0 GROUP BY INDEX_NAME HAVING COUNT(*) = 1 AND MAX(COLUMN_NAME) = 'id') i");
            r1Assert($results, 'fresh.items_unique_index', $itemsUnique > 0, 'single_column_unique_indexes=' . $itemsUnique);
            $pokeExtra = (string) r1Scalar($db, "SELECT EXTRA FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'items_poke' AND COLUMN_NAME = 'id_itp'");
            $zeroIds = (int) r1Scalar($db, 'SELECT COUNT(*) FROM `items_poke` WHERE `id_itp` = 0');
            $distinctIds = (int) r1Scalar($db, 'SELECT COUNT(DISTINCT `id_itp`) FROM `items_poke`');
            $rowCount = (int) r1Scalar($db, 'SELECT COUNT(*) FROM `items_poke`');
            r1Assert(
                $results,
                'fresh.items_poke_hardened',
                str_contains(strtolower($pokeExtra), 'auto_increment') && $zeroIds === 0 && $distinctIds === $rowCount && $rowCount === 3,
                'extra=' . $pokeExtra . ' zero=' . $zeroIds . ' distinct=' . $distinctIds . ' rows=' . $rowCount
            );
            $newsNullable = (string) r1Scalar($db, "SELECT IS_NULLABLE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'news' AND COLUMN_NAME = 'date'");
            $newsDefault = r1Scalar($db, "SELECT COLUMN_DEFAULT FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'news' AND COLUMN_NAME = 'date'");
            r1Assert($results, 'fresh.news_date_nullable', $newsNullable === 'YES' && $newsDefault === null, 'nullable=' . $newsNullable . ' default=' . var_export($newsDefault, true));
            $sequence = r1Scalar($db, 'SELECT `next_id` FROM `items_poke_id_sequence` WHERE `id` = 1');
            r1Assert($results, 'fresh.sequence_seeded_from_max', (int) $sequence === 7, 'next_id=' . var_export($sequence, true));

            $rerun = r1RunApply($db, $files);
            r1Assert($results, 'rerun.apply_succeeds', $rerun['ok'] === true, (string) $rerun['error']);
            $registry = r1Registry($db);
            r1Assert($results, 'rerun.no_duplicate_bookkeeping', count($registry) === 4, 'registry_rows=' . count($registry));
            r1Assert(
                $results,
                'rerun.still_4_applied',
                (int) $rerun['snapshot']['applied_migrations'] === 4 && (int) $rerun['snapshot']['pending_migrations'] === 0,
                json_encode($rerun['snapshot'])
            );
        }
    } catch (Throwable $e) {
        r1Assert($results, 'fresh.exception', false, $e->getMessage());
    } finally {
        if ($schemaA !== null) {
            r1DropSchema($server, $schemaA);
        }
    }

    // B. Already-hardened schema: every migration takes its no-op branch. This is
    // where the old PDO::exec executor failed with error 2014 on EXECUTE + SELECT.
    $schemaB = null;
    try {
        [$schemaB, $db] = r1ScenarioSchema($config, $server);
        r1FixtureHardened($db);
        $noop = r1RunApply($db, $files);
        r1Assert($results, 'noop.apply_succeeds', $noop['ok'] === true, (string) $noop['error']);
        if ($noop['ok']) {
            r1Assert(
                $results,
                'noop.registry_4_applied',
                (int) $noop['snapshot']['applied_migrations'] === 4
                    && (int) $noop['snapshot']['failed_migrations'] === 0
                    && (int) $noop['snapshot']['pending_migrations'] === 0,
                json_encode($noop['snapshot'])
            );
            $sequence = r1Scalar($db, 'SELECT `next_id` FROM `items_poke_id_sequence` WHERE `id` = 1');
            r1Assert($results, 'noop.sequence_not_lowered', (int) $sequence === 0, 'next_id=' . var_export($sequence, true));
        }
    } catch (Throwable $e) {
        r1Assert($results, 'noop.exception', false, $e->getMessage());
    } finally {
        if ($schemaB !== null) {
            r1DropSchema($server, $schemaB);
        }
    }

    // C. A genuinely broken migration must still be recorded as failed and abort.
    $schemaC = null;
    try {
        [$schemaC, $db] = r1ScenarioSchema($config, $server);
        r1FixtureDuplicate($db);
        $failed = r1RunApply($db, $files);
        r1Assert($results, 'failed.apply_raises', $failed['ok'] === false, (string) $failed['error']);
        $registry = r1Registry($db);
        $failedRow = null;
        foreach ($registry as $row) {
            if (str_contains((string) $row['migration'], '000004_mysql84_items_unique_id')) {
                $failedRow = $row;
            }
        }
        r1Assert(
            $results,
            'failed.bookkeeping_failed_row',
            is_array($failedRow) && (string) $failedRow['status'] === 'failed' && trim((string) $failedRow['note']) !== '',
            $failedRow === null ? 'no registry row' : json_encode($failedRow)
        );
        r1Assert(
            $results,
            'failed.no_silent_index',
            (int) r1Scalar($db, "SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'items' AND INDEX_NAME = 'uq_items_id'") === 0,
            'uq_items_id rows must be 0'
        );
        r1Assert($results, 'failed.snapshot_failed_1', (int) $failed['snapshot']['failed_migrations'] === 1 && (int) $failed['snapshot']['pending_migrations'] === 3, json_encode($failed['snapshot']));
    } catch (Throwable $e) {
        r1Assert($results, 'failed.exception', false, $e->getMessage());
    } finally {
        if ($schemaC !== null) {
            r1DropSchema($server, $schemaC);
        }
    }

    // D. An EXECUTE that returns more than one result set. No current migration
    // does this, but the executor must drain all sets for future guarded files.
    $schemaD = null;
    try {
        if (!is_dir($tempDir) && !mkdir($tempDir, 0775, true) && !is_dir($tempDir)) {
            throw new RuntimeException('Unable to create temp dir ' . $tempDir);
        }
        $fixture = $tempDir . '/000099_r1_multi_resultset.sql';
        file_put_contents($fixture, "CALL `r1_two_result_sets`();\nSET @r1_after_multi := 1;\nINSERT INTO `r1_marker` (`id`) VALUES (1);\n");
        $multiFiles = [['name' => basename($fixture), 'path' => $fixture, 'checksum' => hash_file('sha256', $fixture) ?: '']];

        [$schemaD, $db] = r1ScenarioSchema($config, $server);
        $db->exec('CREATE TABLE `r1_marker` (`id` INT NOT NULL PRIMARY KEY) ENGINE=InnoDB');
        $db->exec('DROP PROCEDURE IF EXISTS `r1_two_result_sets`');
        $db->exec('CREATE PROCEDURE `r1_two_result_sets`() BEGIN SELECT 1 AS `first_set`; SELECT 2 AS `second_set`; END');
        $multi = r1RunApply($db, $multiFiles);
        r1Assert($results, 'multi_resultset.apply_succeeds', $multi['ok'] === true, (string) $multi['error']);
        r1Assert(
            $results,
            'multi_resultset.marker_written',
            (int) r1Scalar($db, 'SELECT COUNT(*) FROM `r1_marker` WHERE `id` = 1') === 1,
            'rows=' . var_export(r1Scalar($db, 'SELECT COUNT(*) FROM `r1_marker`'), true)
        );
    } catch (Throwable $e) {
        r1Assert($results, 'multi_resultset.exception', false, $e->getMessage());
    } finally {
        if ($schemaD !== null) {
            r1DropSchema($server, $schemaD);
        }
        if (is_dir($tempDir)) {
            foreach (glob($tempDir . '/*') ?: [] as $path) {
                @unlink($path);
            }
            @rmdir($tempDir);
        }
    }

    // E. A real already-applied migration with literal '?' strings. This is the
    // fresh-install prepare risk that the four-file cases cannot cover.
    r1LiteralQuestionMarkScenario($config, $server, $results);

    $failed = array_values(array_filter($results, static fn (array $row): bool => !$row['ok']));
    if (isset($options['json'])) {
        echo json_encode([
            'test_server' => 'MySQL 8.4.13',
            'uses_repository_runner' => APP_ROOT . '/tools/migration_status.php',
            'checks' => $results,
            'passed' => count($results) - count($failed),
            'failed' => count($failed),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
    } else {
        foreach ($results as $row) {
            printf("%-52s %s %s\n", $row['name'], $row['ok'] ? 'PASS' : 'FAIL', $row['details']);
        }
        printf("Migration executor: %d/%d passed, %d failed\n", count($results) - count($failed), count($results), count($failed));
    }

    return $failed === [] ? 0 : 1;
}
