<?php
declare(strict_types=1);

use Pokemon8\Database\Connection;
use Pokemon8\Repository\InventoryRepository;
use Pokemon8\Support\Env;

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Run from CLI.\n");
    exit(2);
}

require dirname(__DIR__) . '/src/Support/Autoload.php';

$options = parseArgs($argv);
if (isset($options['worker'])) {
    exit(workerMain($options));
}

exit(parentMain($options));

/** @return array<string,string> */
function parseArgs(array $argv): array
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

function appConfig(): array
{
    Env::load(dirname(__DIR__) . '/.env');
    return require dirname(__DIR__) . '/config/database.php';
}

function assertLabServer(PDO $db): void
{
    $server = $db->query('SELECT VERSION() AS version, @@port AS port')->fetch();
    if (!is_array($server)
        || !str_starts_with((string) ($server['version'] ?? ''), '8.4.')
        || (int) ($server['port'] ?? 0) !== 3384) {
        throw new RuntimeException('Refusing to run: expected MySQL 8.4.13 on port 3384 (isolated lab).');
    }
}

function connectSchema(array $config, string $schema): PDO
{
    $config['database'] = $schema;
    return Connection::make($config);
}

function writeJson(string $path, array $data): void
{
    $tmp = $path . '.tmp';
    file_put_contents($tmp, json_encode($data, JSON_UNESCAPED_SLASHES) . PHP_EOL);
    rename($tmp, $path);
}

function readJson(string $path): ?array
{
    if (!is_file($path)) {
        return null;
    }
    $raw = (string) file_get_contents($path);
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : null;
}

function waitForJson(string $path, int $timeoutMs): ?array
{
    $deadline = microtime(true) + ($timeoutMs / 1000);
    while (microtime(true) < $deadline) {
        $data = readJson($path);
        if ($data !== null) {
            return $data;
        }
        usleep(50000);
    }
    return null;
}

function waitForFile(string $path, int $timeoutMs): ?string
{
    $deadline = microtime(true) + ($timeoutMs / 1000);
    while (microtime(true) < $deadline) {
        if (is_file($path)) {
            return trim((string) file_get_contents($path));
        }
        usleep(50000);
    }
    return null;
}

function clearIpc(string $dir): void
{
    foreach (glob($dir . '/*') ?: [] as $path) {
        if (is_file($path)) {
            @unlink($path);
        }
    }
}

function resetLegacyFixture(PDO $db): void
{
    $db->exec('DROP TABLE IF EXISTS `items_poke`');
    $db->exec('DROP TABLE IF EXISTS `items_poke_id_sequence`');
    $db->exec(
        'CREATE TABLE `items_poke` (
            `id_itp` INT NOT NULL,
            `id_poke` INT NOT NULL,
            `id_items` INT NOT NULL,
            `datetime` VARCHAR(15) NOT NULL DEFAULT "not"
        ) ENGINE=InnoDB'
    );
    $db->exec(
        'CREATE TABLE `items_poke_id_sequence` (
            `id` TINYINT NOT NULL PRIMARY KEY,
            `next_id` INT NOT NULL
        ) ENGINE=InnoDB'
    );
    $db->exec('INSERT INTO `items_poke_id_sequence` (`id`, `next_id`) VALUES (1, 0)');
}

function resetAutoFixture(PDO $db): void
{
    $db->exec('DROP TABLE IF EXISTS `items_poke`');
    $db->exec(
        'CREATE TABLE `items_poke` (
            `id_itp` INT NOT NULL AUTO_INCREMENT,
            `id_poke` INT NOT NULL,
            `id_items` INT NOT NULL,
            `datetime` VARCHAR(15) NOT NULL DEFAULT "not",
            PRIMARY KEY (`id_itp`)
        ) ENGINE=InnoDB'
    );
}

/** @return array{process:resource,log:string,role:string} */
function startWorker(string $schema, string $ipcDir, string $logDir, string $role): array
{
    $nullDevice = PHP_OS_FAMILY === 'Windows' ? 'NUL' : '/dev/null';
    $log = $logDir . '/' . $role . '.log';
    $command = [
        PHP_BINARY,
        __FILE__,
        '--worker=' . $role,
        '--schema=' . $schema,
        '--dir=' . $ipcDir,
    ];
    $process = proc_open(
        $command,
        [0 => ['file', $nullDevice, 'r'], 1 => ['file', $log, 'a'], 2 => ['file', $log, 'a']],
        $pipes,
        dirname(__DIR__),
        getenv()
    );
    if (!is_resource($process)) {
        throw new RuntimeException('Unable to start worker ' . $role);
    }
    return ['process' => $process, 'log' => $log, 'role' => $role];
}

/** @return array{exit:int,timeout:bool,log:string} */
function finishWorker(array $worker, int $timeoutMs): array
{
    $deadline = microtime(true) + ($timeoutMs / 1000);
    $exit = -1;
    while (microtime(true) < $deadline) {
        $status = proc_get_status($worker['process']);
        if (!$status['running']) {
            $exit = (int) $status['exitcode'];
            proc_close($worker['process']);
            return ['exit' => $exit, 'timeout' => false, 'log' => trim((string) @file_get_contents($worker['log']))];
        }
        usleep(50000);
    }
    proc_terminate($worker['process'], 9);
    $status = proc_get_status($worker['process']);
    $exit = (int) ($status['exitcode'] ?? -1);
    proc_close($worker['process']);
    return ['exit' => $exit, 'timeout' => true, 'log' => trim((string) @file_get_contents($worker['log']))];
}

function stopWorkerIfRunning(array $worker): void
{
    if (!isset($worker['process']) || !is_resource($worker['process'])) {
        return;
    }
    $status = proc_get_status($worker['process']);
    if ($status['running']) {
        proc_terminate($worker['process'], 9);
    }
    proc_close($worker['process']);
}

function assertion(array &$results, string $name, bool $ok, string $details = ''): void
{
    $results[] = ['name' => $name, 'ok' => $ok, 'details' => $details];
}

function heldRows(PDO $db): array
{
    return $db->query('SELECT `id_itp`, `id_poke` FROM `items_poke` ORDER BY `id_poke` ASC')->fetchAll() ?: [];
}

function sequenceValue(PDO $db): ?int
{
    $value = $db->query('SELECT `next_id` FROM `items_poke_id_sequence` WHERE `id` = 1')->fetchColumn();
    return $value === false ? null : (int) $value;
}

function runOverlapScenario(
    PDO $db,
    array $config,
    string $schema,
    string $ipcDir,
    string $logDir,
    string $scenario,
    bool $auto,
    string $aAction,
    array &$results
): void {
    clearIpc($ipcDir);
    if ($auto) {
        resetAutoFixture($db);
    } else {
        resetLegacyFixture($db);
    }

    $workerA = null;
    $workerB = null;
    try {
        $workerA = startWorker($schema, $ipcDir, $logDir, $scenario . '-a');
        $aInserted = waitForJson($ipcDir . '/a_inserted.json', 15000);
        assertion($results, $scenario . '.a_inserted', $aInserted !== null, $aInserted === null ? 'worker A did not insert' : 'id=' . (int) $aInserted['id']);
        if ($aInserted === null) {
            return;
        }

        $workerB = startWorker($schema, $ipcDir, $logDir, $scenario . '-b');
        $bAllocating = waitForJson($ipcDir . '/b_allocating.json', 15000);
        assertion($results, $scenario . '.b_started', $bAllocating !== null, $bAllocating === null ? 'worker B did not start' : 'started');
        if ($bAllocating === null) {
            return;
        }

        if ($auto) {
            // AUTO_INCREMENT does not need to see A's uncommitted row, so B must
            // finish while A is still open. That proves the server path is intact.
            $bDoneWhileAOpen = waitForJson($ipcDir . '/b_done.json', 10000);
            assertion(
                $results,
                $scenario . '.auto_does_not_wait_for_commit',
                $bDoneWhileAOpen !== null && ($bDoneWhileAOpen['ok'] ?? false) === true,
                $bDoneWhileAOpen === null ? 'worker B blocked unexpectedly' : 'id=' . (int) ($bDoneWhileAOpen['id'] ?? 0)
            );
        } else {
            // The legacy allocator legitimately waits on A's row lock. Give B time
            // to reach the blocking UPDATE, then prove it has not completed.
            usleep(600000);
            $bFinishedEarly = readJson($ipcDir . '/b_done.json');
            assertion(
                $results,
                $scenario . '.waits_for_uncommitted_a',
                $bFinishedEarly === null,
                $bFinishedEarly === null ? 'B blocked on A row lock' : 'B completed before A ' . $aAction . ' (id=' . (int) ($bFinishedEarly['id'] ?? 0) . ')'
            );
        }

        writeJson($ipcDir . '/a_release.json', ['action' => $aAction]);
        $aDone = waitForJson($ipcDir . '/a_done.json', 40000);
        $bDone = waitForJson($ipcDir . '/b_done.json', 40000);
        $aResult = finishWorker($workerA, 5000);
        $workerA = null;
        $bResult = finishWorker($workerB, 5000);
        $workerB = null;
        assertion($results, $scenario . '.a_done', $aDone !== null && ($aDone['ok'] ?? false) === true, $aDone === null ? 'no a_done' : (string) json_encode($aDone));
        assertion($results, $scenario . '.b_done', $bDone !== null && ($bDone['ok'] ?? false) === true, $bDone === null ? 'no b_done' : (string) json_encode($bDone));
        assertion($results, $scenario . '.worker_a_exit', !$aResult['timeout'] && $aResult['exit'] === 0, 'exit=' . $aResult['exit'] . ' log=' . $aResult['log']);
        assertion($results, $scenario . '.worker_b_exit', !$bResult['timeout'] && $bResult['exit'] === 0, 'exit=' . $bResult['exit'] . ' log=' . $bResult['log']);

        if ($aDone === null || $bDone === null) {
            return;
        }

        $rows = heldRows($db);
        $ids = array_map(static fn (array $row): int => (int) $row['id_itp'], $rows);
        $unique = count($ids) === count(array_unique($ids));
        if ($aAction === 'rollback') {
            assertion($results, $scenario . '.rollback_leaves_one_row', count($rows) === 1, 'rows=' . json_encode($rows));
            assertion($results, $scenario . '.rollback_reuses_id_1', $rows !== [] && (int) $rows[0]['id_itp'] === 1 && (int) $rows[0]['id_poke'] === 1002, 'rows=' . json_encode($rows));
            assertion($results, $scenario . '.rollback_sequence', sequenceValue($db) === 1, 'next_id=' . var_export(sequenceValue($db), true));
        } else {
            assertion($results, $scenario . '.two_rows', count($rows) === 2, 'rows=' . json_encode($rows));
            assertion($results, $scenario . '.unique_ids', $unique && count($ids) === 2, 'ids=' . implode(',', $ids));
            assertion($results, $scenario . '.monotonic_ids', $ids === [1, 2], 'ids=' . implode(',', $ids));
            if (!$auto) {
                assertion($results, $scenario . '.sequence_after_commit', sequenceValue($db) === 2, 'next_id=' . var_export(sequenceValue($db), true));
            }
        }
    } finally {
        if ($workerA !== null) {
            stopWorkerIfRunning($workerA);
        }
        if ($workerB !== null) {
            stopWorkerIfRunning($workerB);
        }
    }
}

function missingAllocatorScenario(PDO $db, array &$results): void
{
    $db->exec('DROP TABLE IF EXISTS `items_poke`');
    $db->exec('DROP TABLE IF EXISTS `items_poke_id_sequence`');
    $db->exec(
        'CREATE TABLE `items_poke` (
            `id_itp` INT NOT NULL,
            `id_poke` INT NOT NULL,
            `id_items` INT NOT NULL,
            `datetime` VARCHAR(15) NOT NULL DEFAULT "not"
        ) ENGINE=InnoDB'
    );

    $repo = new InventoryRepository($db);
    // PHP 8.1+ reflection can invoke a private method without setAccessible().
    $method = new ReflectionMethod(InventoryRepository::class, 'insertHeldItemRow');
    $error = null;

    $db->beginTransaction();
    try {
        $method->invoke($repo, 1003, 13, 'not');
    } catch (Throwable $e) {
        $error = $e;
    } finally {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
    }

    $message = $error instanceof Throwable ? $error->getMessage() : '';
    assertion(
        $results,
        'missing_allocator.fails_loudly',
        $error instanceof RuntimeException
            && str_contains($message, 'items_poke_id_sequence')
            && str_contains($message, '000007'),
        $error === null ? 'no exception' : $message
    );
    assertion($results, 'missing_allocator.no_row_written', heldRows($db) === [], 'rows=' . json_encode(heldRows($db)));
}

function detachedTransactionScenario(PDO $db, array &$results): void
{
    resetLegacyFixture($db);
    $repo = new InventoryRepository($db);
    $method = new ReflectionMethod(InventoryRepository::class, 'insertHeldItemRow');

    $method->invoke($repo, 1004, 14, 'not');
    $method->invoke($repo, 1005, 15, 'not');

    $rows = heldRows($db);
    $ids = array_map(static fn (array $row): int => (int) $row['id_itp'], $rows);
    assertion($results, 'detached.opens_and_commits', $db->inTransaction() === false && count($rows) === 2, 'in_transaction=' . var_export($db->inTransaction(), true) . ' rows=' . json_encode($rows));
    assertion($results, 'detached.unique_ids', $ids === [1, 2], 'ids=' . implode(',', $ids));
    assertion($results, 'detached.sequence_after_commit', sequenceValue($db) === 2, 'next_id=' . var_export(sequenceValue($db), true));
}

function workerMain(array $options): int
{
    $role = (string) ($options['worker'] ?? '');
    $schema = (string) ($options['schema'] ?? '');
    $dir = (string) ($options['dir'] ?? '');
    if (!preg_match('/\Apokemonchic84_hid_[a-f0-9]{12}\z/D', $schema) || !is_dir($dir) || $role === '') {
        fwrite(STDERR, "[held-id worker] invalid arguments\n");
        return 2;
    }

    $config = appConfig();
    if ((int) ($config['port'] ?? 0) !== 3384) {
        fwrite(STDERR, "[held-id worker] refusing non-lab port\n");
        return 2;
    }

    try {
        $db = connectSchema($config, $schema);
        assertLabServer($db);
        $repo = new InventoryRepository($db);
        $method = new ReflectionMethod(InventoryRepository::class, 'insertHeldItemRow');

        $isA = str_ends_with($role, '-a');
        $pokemonId = $isA ? 1001 : 1002;
        $itemId = $isA ? 11 : 12;
        $prefix = $isA ? 'a' : 'b';

        $db->beginTransaction();
        if (!$isA) {
            writeJson($dir . '/b_allocating.json', ['at' => microtime(true)]);
        }

        $method->invoke($repo, $pokemonId, $itemId, 'not');
        $id = (int) ($db->query('SELECT `id_itp` FROM `items_poke` WHERE `id_poke` = ' . $pokemonId . ' LIMIT 1')->fetchColumn() ?: 0);
        writeJson($dir . '/' . $prefix . '_inserted.json', ['id' => $id, 'at' => microtime(true)]);

        if ($isA) {
            $rawRelease = waitForFile($dir . '/a_release.json', 30000);
            if ($rawRelease === null) {
                $db->rollBack();
                writeJson($dir . '/a_done.json', ['ok' => false, 'error' => 'release timeout', 'id' => $id]);
                return 1;
            }
            $release = json_decode($rawRelease, true);
            $action = is_array($release) ? (string) ($release['action'] ?? 'commit') : 'commit';
            if ($action === 'rollback') {
                $db->rollBack();
            } else {
                $db->commit();
            }
            writeJson($dir . '/a_done.json', ['ok' => true, 'action' => $action, 'id' => $id]);
            return 0;
        }

        $db->commit();
        writeJson($dir . '/b_done.json', ['ok' => true, 'id' => $id]);
        return 0;
    } catch (Throwable $e) {
        if (isset($db) && $db instanceof PDO && $db->inTransaction()) {
            $db->rollBack();
        }
        $prefix = str_ends_with($role, '-a') ? 'a' : 'b';
        writeJson($dir . '/' . $prefix . '_done.json', ['ok' => false, 'error' => $e->getMessage()]);
        fwrite(STDERR, '[held-id worker] ' . $e->getMessage() . PHP_EOL);
        return 1;
    }
}

function parentMain(array $options): int
{
    $config = appConfig();
    // Refuse before opening any connection, so a stray live .env is never touched.
    if ((int) ($config['port'] ?? 0) !== 3384
        || !preg_match('/\Apokemonchic84_\d{8}_\d{6}_[a-f0-9]{6}\z/D', (string) $config['database'])) {
        throw new RuntimeException('Refusing to run outside the isolated lab on port 3384.');
    }
    $server = Connection::make(['host' => $config['host'], 'port' => (int) $config['port'], 'database' => $config['database'], 'username' => $config['username'], 'password' => $config['password'], 'charset' => $config['charset']]);
    assertLabServer($server);

    $schema = 'pokemonchic84_hid_' . bin2hex(random_bytes(6));
    $ipcDir = dirname(__DIR__) . '/storage/tmp/' . $schema;
    $logDir = $ipcDir . '/logs';
    if (!is_dir($logDir) && !mkdir($logDir, 0775, true) && !is_dir($logDir)) {
        throw new RuntimeException('Unable to create temp directory ' . $logDir);
    }

    $results = [];
    $db = null;
    $created = false;
    try {
        $server->exec('CREATE DATABASE `' . $schema . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $created = true;
        $db = connectSchema($config, $schema);
        assertLabServer($db);

        runOverlapScenario($db, $config, $schema, $ipcDir, $logDir, 'legacy_commit', false, 'commit', $results);
        runOverlapScenario($db, $config, $schema, $ipcDir, $logDir, 'legacy_rollback', false, 'rollback', $results);
        runOverlapScenario($db, $config, $schema, $ipcDir, $logDir, 'auto_increment', true, 'commit', $results);
        detachedTransactionScenario($db, $results);
        missingAllocatorScenario($db, $results);
    } finally {
        if ($db instanceof PDO && $db->inTransaction()) {
            $db->rollBack();
        }
        $db = null;
        if ($created && preg_match('/\Apokemonchic84_hid_[a-f0-9]{12}\z/D', $schema)) {
            $server->exec('DROP DATABASE `' . $schema . '`');
        }
        if (preg_match('/\Apokemonchic84_hid_[a-f0-9]{12}\z/D', basename($ipcDir))) {
            foreach (glob($ipcDir . '/*') ?: [] as $path) {
                if (is_file($path)) {
                    @unlink($path);
                }
            }
            foreach (glob($ipcDir . '/logs/*') ?: [] as $path) {
                @unlink($path);
            }
            @rmdir($ipcDir . '/logs');
            @rmdir($ipcDir);
        }
    }

    $failed = array_values(array_filter($results, static fn (array $row): bool => !$row['ok']));
    if (isset($options['json'])) {
        echo json_encode([
            'test_server' => 'MySQL 8.4.13',
            'isolated_schema' => $schema,
            'scenarios' => ['legacy_commit', 'legacy_rollback', 'auto_increment', 'detached_transaction', 'missing_allocator'],
            'checks' => $results,
            'passed' => count($results) - count($failed),
            'failed' => count($failed),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
    } else {
        foreach ($results as $row) {
            printf("%-52s %s %s\n", $row['name'], $row['ok'] ? 'PASS' : 'FAIL', $row['details']);
        }
        printf("Held-id concurrency: %d/%d passed, %d failed\n", count($results) - count($failed), count($results), count($failed));
    }

    return $failed === [] ? 0 : 1;
}
