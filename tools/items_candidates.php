<?php
declare(strict_types=1);

/** What items are actually usable as drops? Grouped by category, with ids. */
require __DIR__ . '/../src/Support/Autoload.php';

Pokemon8\Support\Env::load(dirname(__DIR__) . '/.env');
$db = Pokemon8\Database\Connection::make(require dirname(__DIR__) . '/config/database.php');

echo "-- категории предметов --\n";
foreach ($db->query('SELECT category, COUNT(*) c FROM items GROUP BY category ORDER BY c DESC') as $row) {
    echo str_pad((string) $row['category'], 24) . $row['c'] . "\n";
}

echo "\n-- лечебные и полезные кандидаты (по названию) --\n";
$sql = "SELECT id, name, tittle, category FROM items
         WHERE LOWER(name) REGEXP 'heal|potion|antidote|revive|ether|elixir|berry|repel|ball|конфет|перо|клевер|энерг'
            OR LOWER(tittle) REGEXP 'леч|аптеч|зель|восстан|ягод|конфет|перо|клевер|энерг'
         ORDER BY id LIMIT 24";
foreach ($db->query($sql) as $row) {
    echo str_pad((string) $row['id'], 6) . str_pad((string) $row['name'], 26) . str_pad((string) $row['tittle'], 26) . $row['category'] . "\n";
}

echo "\n-- предметы 1..20 (база) --\n";
foreach ($db->query('SELECT id, name, tittle, category FROM items ORDER BY id LIMIT 20') as $row) {
    echo str_pad((string) $row['id'], 6) . str_pad((string) $row['name'], 26) . str_pad((string) $row['tittle'], 24) . $row['category'] . "\n";
}
