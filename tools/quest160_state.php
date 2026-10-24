<?php
declare(strict_types=1);

/** What did the live quest-160 start leave in the database? */
require __DIR__ . '/../src/Support/Autoload.php';

Pokemon8\Support\Env::load(dirname(__DIR__) . '/.env');
$db = Pokemon8\Database\Connection::make(require dirname(__DIR__) . '/config/database.php');

echo "-- колонки таблицы quest --\n";
$questColumns = array_map(
    static fn (array $r): string => (string) $r['Field'],
    $db->query('SHOW COLUMNS FROM quest')->fetchAll(PDO::FETCH_ASSOC)
);
echo implode(', ', $questColumns) . PHP_EOL;

$userColumn = null;
foreach (['users', 'user_id', 'user', 'uid'] as $candidate) {
    if (in_array($candidate, $questColumns, true)) {
        $userColumn = $candidate;
        break;
    }
}
// В таблице quest две колонки: id (строка записи) и quest_id (номер квеста).
// Фильтровать надо по quest_id, иначе проверка врёт.
$questIdColumn = in_array('quest_id', $questColumns, true) ? 'quest_id' : (in_array('id', $questColumns, true) ? 'id' : null);

echo "\n-- строки квестов 160..162 у игрока 1 --\n";
if ($userColumn !== null && $questIdColumn !== null) {
    $stmt = $db->prepare("SELECT * FROM quest WHERE `$userColumn` = :user AND `$questIdColumn` IN (160, 161, 162)");
    $stmt->execute(['user' => 1]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($rows === []) {
        echo "(нет записей)\n";
    }
    foreach ($rows as $row) {
        echo json_encode($row, JSON_UNESCAPED_UNICODE) . PHP_EOL;
    }
} else {
    echo "(не нашёл колонку игрока или квеста)\n";
}

echo "\n-- предметы игрока 1, которые нужны для сдачи (13, 14, 15) --\n";
$stmt = $db->prepare('SELECT item_id, COUNT(*) c FROM inventory WHERE users = :user AND item_id IN (13, 14, 15) GROUP BY item_id');
$stmt->execute(['user' => 1]);
$found = $stmt->fetchAll(PDO::FETCH_ASSOC);
if ($found === []) {
    echo "(нет)\n";
}
foreach ($found as $row) {
    echo "item {$row['item_id']}: {$row['c']}\n";
}

echo "\n-- ледяные покемоны игрока 1 --\n";
$stmt = $db->prepare(
    'SELECT COUNT(*) FROM pok_user pu INNER JOIN pokemon p ON p.id = pu.basenum
      WHERE pu.users = :user AND p.Element LIKE "%Ice%"'
);
$stmt->execute(['user' => 1]);
echo 'всего ледяных: ' . (int) $stmt->fetchColumn() . PHP_EOL;
$stmt->execute(['user' => 1]);
foreach ($db->query('SELECT pu.lvl, k.Name, p.Element FROM pok_user pu INNER JOIN pokemon p ON p.id = pu.basenum INNER JOIN pokemon k ON k.id = pu.basenum WHERE pu.users = 1 AND p.Element LIKE "%Ice%" LIMIT 5') as $row) {
    echo "  {$row['Name']} {$row['lvl']} ур. ({$row['Element']})\n";
}
