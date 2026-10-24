<?php
declare(strict_types=1);

// Restore only into a new, isolated schema. Never overwrite a database.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit(2); }
require dirname(__DIR__) . '/src/Support/Autoload.php';
Pokemon8\Support\Env::load(dirname(__DIR__) . '/.env');
$config = require dirname(__DIR__) . '/config/database.php';
$options = getopt('', ['file:', 'mysql:']);
try {
    $file = realpath((string) ($options['file'] ?? ''));
    $backupRoot = realpath(dirname(__DIR__) . '/storage/backups');
    if (!$file || !$backupRoot || !str_starts_with(strtolower($file), strtolower($backupRoot . DIRECTORY_SEPARATOR)) || !is_file($file)) {
        throw new RuntimeException('Select a dump inside storage/backups using --file.');
    }
    $sql = file_get_contents($file);
    if ($sql === false || preg_match('/^\s*(?:CREATE\s+DATABASE|USE\s+)/mi', $sql)) {
        throw new RuntimeException('Dump must not select or create another database.');
    }
    preg_match_all('/^CREATE TABLE `([^`]+)`/m', $sql, $matches);
    $expected = array_values(array_unique($matches[1]));
    if (count($expected) < 14) { throw new RuntimeException('Incomplete dump.'); }
    unset($sql);
    $db = Pokemon8\Database\Connection::make($config);
    if (strtoupper((string) $db->query('SELECT @@event_scheduler')->fetchColumn()) === 'ON') {
        throw new RuntimeException('Restore requires event_scheduler OFF to prevent restored events from executing.');
    }
    $schema = 'pokemonchic_restore_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3));
    $db->exec('CREATE DATABASE `' . $schema . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $mysql = (string) ($options['mysql'] ?? 'E:/OSPanel/modules/database/MySQL-5.5/bin/mysql.exe');
    if (!is_file($mysql)) { throw new RuntimeException('mysql client not found; use --mysql.'); }
    $env = getenv();
    $env['MYSQL_PWD'] = (string) $config['password'];
    $process = proc_open([$mysql, '--host=' . $config['host'], '--port=' . $config['port'], '--user=' . $config['username'], '--default-character-set=utf8mb4', $schema],
        [0 => ['file', $file, 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, null, $env);
    unset($env);
    if (!is_resource($process)) { throw new RuntimeException('Unable to start mysql.'); }
    $stdout = stream_get_contents($pipes[1]); fclose($pipes[1]);
    $stderr = stream_get_contents($pipes[2]); fclose($pipes[2]);
    if (proc_close($process) !== 0) { throw new RuntimeException('Restore failed: ' . trim((string) $stderr)); }
    $tables = $db->query('SHOW TABLES FROM `' . $schema . '`')->fetchAll(PDO::FETCH_COLUMN);
    $missing = array_diff($expected, $tables);
    if ($missing !== []) { throw new RuntimeException('Missing restored tables: ' . implode(', ', $missing)); }
    $counts = [];
    foreach (['users', 'pok_user', 'items_users', 'eggs', 'schema_migrations'] as $table) {
        $counts[$table] = (int) $db->query('SELECT COUNT(*) FROM `' . $schema . '`.`' . $table . '`')->fetchColumn();
    }
    echo json_encode(['ok' => true, 'schema' => $schema, 'dump_sha256' => hash_file('sha256', $file), 'tables' => count($tables), 'expected_tables' => count($expected), 'counts' => $counts, 'retained_for_inspection' => true], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
} catch (Throwable $e) {
    fwrite(STDERR, 'Restore verify: ' . $e->getMessage() . PHP_EOL);
    if (isset($schema)) { fwrite(STDERR, 'Isolated schema retained: ' . $schema . PHP_EOL); }
    exit(1);
}
