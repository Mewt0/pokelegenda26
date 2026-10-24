<?php
declare(strict_types=1);

/** Validity of every spawn row: unknown species, absurd levels, weights out of range. */
require __DIR__ . '/../src/Support/Autoload.php';

Pokemon8\Support\Env::load(dirname(__DIR__) . '/.env');
$db = Pokemon8\Database\Connection::make(require dirname(__DIR__) . '/config/database.php');

$checks = [
    'покемон не из каталога' => 'SELECT COUNT(*) FROM pokebuild p LEFT JOIN pokemon k ON k.id = p.baseid WHERE k.id IS NULL',
    'локация не из build' => 'SELECT COUNT(*) FROM pokebuild p LEFT JOIN build b ON b.id = p.building WHERE b.id IS NULL',
    'уровень вне 1..100' => 'SELECT COUNT(*) FROM pokebuild WHERE lvl < 1 OR lvl > 100',
    'вес вне 1..999' => 'SELECT COUNT(*) FROM pokebuild WHERE chance < 1 OR chance > 999',
    'нулевой вес' => 'SELECT COUNT(*) FROM pokebuild WHERE chance = 0',
];
foreach ($checks as $label => $sql) {
    echo str_pad($label, 26) . (int) $db->query($sql)->fetchColumn() . "\n";
}

echo "\n-- уровень: распределение --\n";
foreach ($db->query('SELECT MIN(lvl) mn, MAX(lvl) mx, ROUND(AVG(lvl),1) avg FROM pokebuild') as $row) {
    echo "min={$row['mn']} max={$row['mx']} avg={$row['avg']}\n";
}

echo "\n-- самые сильные спавны --\n";
foreach ($db->query('SELECT p.building, b.title, k.Name, p.lvl FROM pokebuild p JOIN pokemon k ON k.id = p.baseid LEFT JOIN build b ON b.id = p.building ORDER BY p.lvl DESC LIMIT 8') as $row) {
    echo "{$row['title']} ({$row['building']}): {$row['Name']} {$row['lvl']} ур.\n";
}
