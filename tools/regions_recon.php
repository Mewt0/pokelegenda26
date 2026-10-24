<?php
declare(strict_types=1);

/** Регионы мира: как устроена таблица build и есть ли в каталоге покемоны новых регионов. */
require __DIR__ . '/../src/Support/Autoload.php';

Pokemon8\Support\Env::load(dirname(__DIR__) . '/.env');
$db = Pokemon8\Database\Connection::make(require dirname(__DIR__) . '/config/database.php');

echo "-- build: колонки --\n";
$columns = array_map(
    static fn (array $r): string => (string) $r['Field'],
    $db->query('SHOW COLUMNS FROM build')->fetchAll(PDO::FETCH_ASSOC)
);
echo implode(', ', $columns) . "\n";

echo "\n-- build: значения town (регион/город) --\n";
foreach ($db->query('SELECT town, COUNT(*) c, MIN(id) min_id, MAX(id) max_id FROM build GROUP BY town ORDER BY min_id') as $row) {
    echo str_pad((string) $row['town'], 6) . 'x' . str_pad((string) $row['c'], 4) . "id {$row['min_id']}–{$row['max_id']}\n";
}

echo "\n-- build: значения tipe --\n";
foreach ($db->query('SELECT tipe, COUNT(*) c FROM build GROUP BY tipe ORDER BY c DESC') as $row) {
    echo str_pad((string) $row['tipe'], 10) . $row['c'] . "\n";
}

echo "\n-- максимальный id локации и сколько всего --\n";
foreach ($db->query('SELECT MAX(id) mx, COUNT(*) c FROM build') as $row) {
    echo "max_id={$row['mx']} total={$row['c']}\n";
}

echo "\n-- покемоны по «поколениям» (номера каталога) --\n";
$bands = [1 => [1, 151], 2 => [152, 251], 3 => [252, 386], 4 => [387, 493], 5 => [494, 649]];
foreach ($bands as $gen => [$from, $to]) {
    $stmt = $db->prepare('SELECT COUNT(*) FROM pokemon WHERE id BETWEEN :a AND :b');
    $stmt->execute(['a' => $from, 'b' => $to]);
    echo "поколение $gen ($from–$to): " . (int) $stmt->fetchColumn() . "\n";
}

echo "\n-- примеры покемонов Хоэнна/Синно/Юновы --\n";
foreach (['Treecko', 'Mudkip', 'Ralts', 'Turtwig', 'Chimchar', 'Piplup', 'Snivy', 'Tepig', 'Oshawott', 'Zorua'] as $name) {
    $stmt = $db->prepare('SELECT id, Name, Element, SubElement FROM pokemon WHERE Name = :n LIMIT 1');
    $stmt->execute(['n' => $name]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo str_pad($name, 12) . ($row ? "id={$row['id']} {$row['Element']}/{$row['SubElement']}" : 'НЕТ В КАТАЛОГЕ') . "\n";
}

echo "\n-- связи локаций: есть ли таблица смежности --\n";
$tables = array_map('strval', $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN));
foreach ($tables as $table) {
    if (preg_match('/world|map|link|path|move|route/i', $table)) {
        echo "$table: " . (int) $db->query("SELECT COUNT(*) FROM `$table`")->fetchColumn() . " строк\n";
    }
}
