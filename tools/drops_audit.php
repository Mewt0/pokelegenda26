<?php
declare(strict_types=1);

/** Are the drop rules real? Item ids must exist, chances must add up per location. */
require __DIR__ . '/../src/Support/Autoload.php';

Pokemon8\Support\Env::load(dirname(__DIR__) . '/.env');
$db = Pokemon8\Database\Connection::make(require dirname(__DIR__) . '/config/database.php');

$tables = array_map('strval', $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN));
$itemTables = array_values(array_filter($tables, static fn (string $t): bool => (bool) preg_match('/^items?$|^item_base|^items_base/i', $t)));
echo 'item tables: ' . implode(', ', $itemTables) . PHP_EOL;

$catalog = $itemTables[0] ?? null;
$idColumn = null;
$nameColumn = null;
if ($catalog !== null) {
    $columns = array_map(
        static fn (array $row): string => (string) $row['Field'],
        $db->query("SHOW COLUMNS FROM `$catalog`")->fetchAll(PDO::FETCH_ASSOC)
    );
    echo "$catalog columns: " . implode(', ', $columns) . PHP_EOL;
    foreach (['id', 'item_id'] as $candidate) {
        if (in_array($candidate, $columns, true)) { $idColumn = $candidate; break; }
    }
    foreach (['name', 'title', 'Name', 'names'] as $candidate) {
        if (in_array($candidate, $columns, true)) { $nameColumn = $candidate; break; }
    }
}

echo "\nвсего правил: " . (int) $db->query('SELECT COUNT(*) FROM admin_drop_rules')->fetchColumn() . PHP_EOL;

if ($catalog !== null && $idColumn !== null) {
    $sql = "SELECT COUNT(*) FROM admin_drop_rules d LEFT JOIN `$catalog` i ON i.`$idColumn` = d.item_id WHERE i.`$idColumn` IS NULL";
    echo 'правил с несуществующим предметом: ' . (int) $db->query($sql)->fetchColumn() . PHP_EOL;
}

echo "\n-- предметы в правилах --\n";
$selectName = ($catalog !== null && $nameColumn !== null) ? "LEFT JOIN `$catalog` i ON i.`$idColumn` = d.item_id" : '';
$selectField = ($selectName !== '') ? "COALESCE(i.`$nameColumn`, '???') AS item_name" : "'' AS item_name";
foreach ($db->query("SELECT d.item_id, $selectField, COUNT(*) c, ROUND(MIN(d.chance_percent),3) minc, ROUND(MAX(d.chance_percent),3) maxc FROM admin_drop_rules d $selectName GROUP BY d.item_id ORDER BY c DESC") as $row) {
    echo str_pad((string) $row['item_id'], 5) . str_pad((string) $row['item_name'], 22) . 'правил=' . str_pad((string) $row['c'], 4) . "шанс {$row['minc']}–{$row['maxc']}%\n";
}

echo "\n-- сумма шансов по локациям (должна быть <= 100) --\n";
foreach ($db->query('SELECT location_id, COUNT(*) c, ROUND(SUM(chance_percent),2) total FROM admin_drop_rules GROUP BY location_id ORDER BY total DESC LIMIT 10') as $row) {
    echo "loc={$row['location_id']} правил={$row['c']} сумма={$row['total']}%\n";
}

echo "\n-- джекпоты (>0.5%) --\n";
foreach ($db->query('SELECT COUNT(*) c FROM admin_drop_rules WHERE chance_percent > 0.5 AND chance_percent <= 3') as $row) {
    echo "редких (0.5–3%): {$row['c']}\n";
}
foreach ($db->query('SELECT COUNT(*) c FROM admin_drop_rules WHERE chance_percent > 3') as $row) {
    echo "частых (>3%): {$row['c']}\n";
}
