<?php
declare(strict_types=1);

/**
 * Acceptance check: can every quest requirement actually be met in the wild?
 *
 * For each quest species, ask whether some location spawns it at (or above) the
 * level the quest asks for. This is the criterion that matters — an applied
 * migration is not proof of a solvable quest.
 */
require __DIR__ . '/../src/Support/Autoload.php';

Pokemon8\Support\Env::load(dirname(__DIR__) . '/.env');
$db = Pokemon8\Database\Connection::make(require dirname(__DIR__) . '/config/database.php');

// species => minimum level the quest needs (0 = any level)
$required = [
    'Oddish' => 0, 'Butterfree' => 35, 'Arbok' => 35, 'Venomoth' => 35,
    'Beedrill' => 35, 'Primeape' => 35, 'Horsea' => 40, 'Metapod' => 9,
    'Pidgeotto' => 0, 'Spearow' => 0, 'Venonat' => 25, 'Poliwhirl' => 0,
    'Weepinbell' => 45,
];

$idByName = [];
foreach ($db->query('SELECT id, Name FROM pokemon') as $row) {
    $idByName[strtolower((string) $row['Name'])] = (int) $row['id'];
}
$locationTitle = [];
foreach ($db->query('SELECT id, title FROM build') as $row) {
    $locationTitle[(int) $row['id']] = (string) $row['title'];
}

$ok = 0;
foreach ($required as $name => $minLevel) {
    $baseId = $idByName[strtolower($name)] ?? 0;
    if ($baseId === 0) {
        echo str_pad($name, 12) . "ОТСУТСТВУЕТ В КАТАЛОГЕ\n";
        continue;
    }
    $stmt = $db->prepare(
        'SELECT building, lvl, chance, timeone, timetwo FROM pokebuild
          WHERE baseid = :base AND lvl >= :lvl ORDER BY lvl DESC LIMIT 3'
    );
    $stmt->execute(['base' => $baseId, 'lvl' => $minLevel]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($rows === []) {
        echo str_pad($name, 12) . "НЕ НАЙДЕН в спавнах при уровне >= $minLevel\n";
        continue;
    }
    $ok++;
    $places = [];
    foreach ($rows as $row) {
        $places[] = $locationTitle[(int) $row['building']] . ' ' . $row['lvl'] . ' ур. (вес ' . $row['chance'] . ')';
    }
    echo str_pad($name, 12) . 'OK  ' . implode('; ', $places) . "\n";
}

echo "\nитог: $ok/" . count($required) . " квестовых видов доступны\n";
echo 'всего спавнов в базе: ' . (int) $db->query('SELECT COUNT(*) FROM pokebuild')->fetchColumn() . "\n";
echo 'правил дропа: ' . (int) $db->query('SELECT COUNT(*) FROM admin_drop_rules')->fetchColumn() . "\n";
echo 'локаций со спавнами: ' . (int) $db->query('SELECT COUNT(DISTINCT building) FROM pokebuild')->fetchColumn() . "\n";
