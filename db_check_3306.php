<?php
declare(strict_types=1);

require __DIR__ . '/src/Support/Autoload.php';
use Pokemon8\Support\Env;
use Pokemon8\Database\Connection;

Env::load(__DIR__ . '/.env');
$dbConfig = require __DIR__ . '/config/database.php';
$dbConfig['port'] = 3306;

try {
    $db = Connection::make($dbConfig);
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables in database (port 3306):\n";
    foreach ($tables as $table) {
        echo "- $table\n";
    }
} catch (Exception $e) {
    echo "Error (port 3306): " . $e->getMessage() . "\n";
}
