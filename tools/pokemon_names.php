<?php
declare(strict_types=1);

/**
 * Read-only: pokemon name -> base id, so a designer's spawn table can be turned
 * into SQL mechanically instead of by guessing.
 */
require __DIR__ . '/../src/Support/Autoload.php';

Pokemon8\Support\Env::load(dirname(__DIR__) . '/.env');
$db = Pokemon8\Database\Connection::make(require dirname(__DIR__) . '/config/database.php');

$tables = array_map('strval', $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN));
$candidates = array_values(array_filter(
    $tables,
    static fn (string $t): bool => (bool) preg_match('/^poke_?base|^pokemon$|^pokedex|^base_poke/i', $t)
));
echo 'candidate tables: ' . implode(', ', $candidates) . PHP_EOL;

foreach ($candidates as $table) {
    $columns = array_map(
        static fn (array $row): string => (string) $row['Field'],
        $db->query("SHOW COLUMNS FROM `$table`")->fetchAll(PDO::FETCH_ASSOC)
    );
    echo "\n== $table\n" . implode(', ', $columns) . PHP_EOL;
    echo 'rows: ' . (int) $db->query("SELECT COUNT(*) FROM `$table`")->fetchColumn() . PHP_EOL;
    foreach ($db->query("SELECT * FROM `$table` ORDER BY 1 LIMIT 5") as $row) {
        echo json_encode($row, JSON_UNESCAPED_UNICODE) . PHP_EOL;
    }
}
