<?php
declare(strict_types=1);

/** Read-only: what the battle data tables look like (moves, abilities, weather). */
require __DIR__ . '/../src/Support/Autoload.php';

Pokemon8\Support\Env::load(dirname(__DIR__) . '/.env');
$db = Pokemon8\Database\Connection::make(require dirname(__DIR__) . '/config/database.php');

$tables = $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
$interesting = array_values(array_filter(
    array_map('strval', $tables),
    static fn (string $name): bool => (bool) preg_match('/move|attack|abilit|weath/i', $name)
));
echo 'tables: ' . implode(', ', $interesting) . PHP_EOL;

foreach ($interesting as $table) {
    $columns = array_map(
        static fn (array $row): string => (string) $row['Field'],
        $db->query("SHOW COLUMNS FROM `$table`")->fetchAll(PDO::FETCH_ASSOC)
    );
    $count = (int) $db->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
    echo "\n-- $table ($count rows)\n" . implode(', ', $columns) . PHP_EOL;

    $priorityColumn = in_array('priority', $columns, true) ? 'priority' : null;
    if ($priorityColumn !== null) {
        foreach ($db->query("SELECT `$priorityColumn` p, COUNT(*) c FROM `$table` GROUP BY p ORDER BY p") as $row) {
            echo "   priority={$row['p']} rows={$row['c']}\n";
        }
    }
}
