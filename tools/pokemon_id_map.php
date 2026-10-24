<?php
declare(strict_types=1);

/**
 * Dump pokemon name -> base id (the id pokebuild.baseid uses) to a TSV file, so a
 * designer's spawn table can be turned into SQL without guessing ids.
 *
 * Usage: php tools/pokemon_id_map.php [output.tsv]
 */
require __DIR__ . '/../src/Support/Autoload.php';

Pokemon8\Support\Env::load(dirname(__DIR__) . '/.env');
$db = Pokemon8\Database\Connection::make(require dirname(__DIR__) . '/config/database.php');

$outPath = $argv[1] ?? dirname(__DIR__) . '/storage/pokemon_id_map.tsv';
$rows = $db->query('SELECT id, Name, Element, SubElement, Base FROM pokemon ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);

$handle = fopen($outPath, 'wb');
fwrite($handle, "id\tname\telement\tsubelement\tbase\n");
foreach ($rows as $row) {
    fwrite($handle, implode("\t", [
        (string) $row['id'],
        (string) $row['Name'],
        (string) $row['Element'],
        (string) $row['SubElement'],
        (string) $row['Base'],
    ]) . "\n");
}
fclose($handle);

echo 'written: ' . $outPath . ' rows=' . count($rows) . PHP_EOL;

// Sanity check against the ids the spawn table already uses.
$used = $db->query('SELECT DISTINCT baseid FROM pokebuild ORDER BY baseid')->fetchAll(PDO::FETCH_COLUMN);
$known = array_map(static fn (array $row): int => (int) $row['id'], $rows);
$missing = array_values(array_diff(array_map('intval', $used), $known));
echo 'baseids used in pokebuild: ' . implode(',', array_map('strval', $used)) . PHP_EOL;
echo 'baseids with no match in pokemon: ' . ($missing === [] ? 'none' : implode(',', $missing)) . PHP_EOL;

foreach (['Oddish', 'Butterfree', 'Arbok', 'Venomoth', 'Beedrill', 'Primeape', 'Horsea', 'Metapod', 'Pidgeotto', 'Spearow', 'Venonat', 'Poliwhirl', 'Weepinbell'] as $name) {
    foreach ($rows as $row) {
        if (strcasecmp((string) $row['Name'], $name) === 0) {
            echo sprintf("%-12s id=%-4d %s/%s\n", $name, (int) $row['id'], (string) $row['Element'], (string) $row['SubElement']);
            break;
        }
    }
}
