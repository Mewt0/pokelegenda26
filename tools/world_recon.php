<?php
declare(strict_types=1);

/** Read-only recon: locations, spawn catalogues, attacks table — the facts a brief needs. */
require __DIR__ . '/../src/Support/Autoload.php';

Pokemon8\Support\Env::load(dirname(__DIR__) . '/.env');
$db = Pokemon8\Database\Connection::make(require dirname(__DIR__) . '/config/database.php');

$tables = array_map('strval', $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN));

echo "== candidate tables ==\n";
foreach ($tables as $table) {
    if (preg_match('/^(build|pokebuild|atac|atack|attack|move|spawn|wild|drop)/i', $table)) {
        $count = (int) $db->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
        echo str_pad($table, 30) . $count . "\n";
    }
}

foreach (['build', 'pokebuild'] as $table) {
    if (!in_array($table, $tables, true)) {
        continue;
    }
    $columns = array_map(
        static fn (array $row): string => (string) $row['Field'],
        $db->query("SHOW COLUMNS FROM `$table`")->fetchAll(PDO::FETCH_ASSOC)
    );
    echo "\n== $table columns ==\n" . implode(', ', $columns) . "\n";
}

// Spawn format: existing rows are the template the model must match.
if (in_array('pokebuild', $tables, true)) {
    echo "\n== pokebuild samples (existing style) ==\n";
    foreach ($db->query('SELECT * FROM pokebuild ORDER BY building, baseid LIMIT 8') as $row) {
        echo json_encode($row, JSON_UNESCAPED_UNICODE) . "\n";
    }
    echo "\n== spawns per location (top 15) ==\n";
    foreach ($db->query('SELECT building, COUNT(*) c FROM pokebuild GROUP BY building ORDER BY c DESC LIMIT 15') as $row) {
        $title = '';
        foreach ($db->query('SELECT title FROM build WHERE id = ' . (int) $row['building']) as $b) {
            $title = (string) $b['title'];
        }
        echo $row['building'] . ' (' . $title . ') => ' . $row['c'] . "\n";
    }
}

// Location names: pick the first text column that looks like a name.
if (in_array('build', $tables, true)) {
    $columns = array_map(
        static fn (array $row): string => (string) $row['Field'],
        $db->query('SHOW COLUMNS FROM build')->fetchAll(PDO::FETCH_ASSOC)
    );
    $nameColumn = null;
    foreach (['name', 'names', 'title', 'nazvanie', 'buildname'] as $candidate) {
        if (in_array($candidate, $columns, true)) {
            $nameColumn = $candidate;
            break;
        }
    }
    echo "\n== locations ==\n";
    if ($nameColumn !== null) {
        foreach ($db->query("SELECT id, `$nameColumn` AS name FROM build ORDER BY id") as $row) {
            echo $row['id'] . ' | ' . $row['name'] . "\n";
        }
    } else {
        foreach ($db->query('SELECT * FROM build ORDER BY id LIMIT 20') as $row) {
            echo json_encode($row, JSON_UNESCAPED_UNICODE) . "\n";
        }
    }
}
