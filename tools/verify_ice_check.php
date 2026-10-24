<?php
declare(strict_types=1);

/** Does the generated ice-check SQL match real tables/columns? */
require __DIR__ . '/../src/Support/Autoload.php';

Pokemon8\Support\Env::load(dirname(__DIR__) . '/.env');
$db = Pokemon8\Database\Connection::make(require dirname(__DIR__) . '/config/database.php');

$tables = array_map('strval', $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN));
foreach (['pok_user', 'pokemon', 'users'] as $table) {
    echo str_pad($table, 12) . (in_array($table, $tables, true) ? 'есть' : 'НЕТ') . "\n";
}

if (in_array('pok_user', $tables, true)) {
    $columns = array_map(
        static fn (array $row): string => (string) $row['Field'],
        $db->query('SHOW COLUMNS FROM pok_user')->fetchAll(PDO::FETCH_ASSOC)
    );
    echo "pok_user columns: " . implode(', ', array_slice($columns, 0, 24)) . "\n";
    foreach (['users', 'basenum', 'lvl', 'user_id', 'baseid'] as $needle) {
        echo "  содержит $needle: " . (in_array($needle, $columns, true) ? 'да' : 'нет') . "\n";
    }
}

echo "\n-- элементы покемонов, содержащие Ice --\n";
foreach ($db->query("SELECT Element, COUNT(*) c FROM pokemon WHERE Element LIKE '%Ice%' GROUP BY Element") as $row) {
    echo str_pad((string) $row['Element'], 24) . $row['c'] . "\n";
}

echo "\n-- пример: игрок и его покемоны (2 строки) --\n";
foreach ($db->query('SELECT * FROM pok_user LIMIT 2') as $row) {
    echo json_encode(array_slice($row, 0, 14, true), JSON_UNESCAPED_UNICODE) . "\n";
}
