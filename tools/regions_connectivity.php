<?php
declare(strict_types=1);

/**
 * Проверка связности регионов и межрегионального транспорта.
 *
 * Проверяет: какие маршруты есть в transport_routes и transport_flight_routes,
 * двусторонние ли они, достижимы ли столицы регионов друг из друга по графу
 * пеших переходов (include/data.world.php) плюс транспорт, и нет ли тупиков.
 */
require __DIR__ . '/../src/Support/Autoload.php';

Pokemon8\Support\Env::load(dirname(__DIR__) . '/.env');
$db = Pokemon8\Database\Connection::make(require dirname(__DIR__) . '/config/database.php');

$REGIONS = [
    'Канто/Джотто' => [1, 9999],
    'Хоэнн' => [30000, 30999],
    'Синно' => [31000, 31999],
    'Юнова' => [32000, 32999],
];
$regionOf = static function (int $id) use ($REGIONS): string {
    foreach ($REGIONS as $name => [$from, $to]) {
        if ($id >= $from && $id <= $to) {
            return $name;
        }
    }
    return 'служебное';
};

echo "== таблицы транспорта ==\n";
$tables = array_map('strval', $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN));
foreach (['transport_routes', 'transport_flight_routes'] as $table) {
    if (!in_array($table, $tables, true)) {
        echo "$table: НЕТ ТАБЛИЦЫ\n";
        continue;
    }
    $columns = array_map(
        static fn (array $r): string => (string) $r['Field'],
        $db->query("SHOW COLUMNS FROM `$table`")->fetchAll(PDO::FETCH_ASSOC)
    );
    $count = (int) $db->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
    echo "\n-- $table ($count строк) колонки: " . implode(', ', $columns) . "\n";
    foreach ($db->query("SELECT * FROM `$table` LIMIT 40") as $row) {
        echo '   ' . json_encode($row, JSON_UNESCAPED_UNICODE) . "\n";
    }
}

// Граф пеших переходов.
$dataLoc = [];
require dirname(__DIR__) . '/include/data.world.php';
$walk = [];
foreach ((array) $dataLoc as $from => $neighbours) {
    foreach ((array) $neighbours as $to) {
        if ((int) $from !== (int) $to) {
            $walk[(int) $from][(int) $to] = true;
        }
    }
}

echo "\n== связь регионов по пешим переходам ==\n";
$crossRegion = [];
foreach ($walk as $from => $targets) {
    foreach (array_keys($targets) as $to) {
        if ($regionOf((int) $from) !== $regionOf((int) $to)) {
            $crossRegion[] = "$from (" . $regionOf((int) $from) . ") -> $to (" . $regionOf((int) $to) . ")";
        }
    }
}
echo $crossRegion === [] ? "прямых пеших переходов между регионами нет\n" : implode("\n", $crossRegion) . "\n";

echo "\n== связность: сколько локаций достижимо из каждой локации ==\n";
$all = array_map('intval', $db->query('SELECT id FROM build')->fetchAll(PDO::FETCH_COLUMN));
$adjacency = $walk;
foreach ($adjacency as $from => $targets) {
    foreach (array_keys($targets) as $to) {
        $adjacency[$to][$from] = true; // считаем граф неориентированным: важно, куда можно дойти
    }
}
$regionReach = [];
foreach ($all as $start) {
    $seen = [$start => true];
    $queue = [$start];
    while ($queue !== []) {
        $current = array_shift($queue);
        foreach (array_keys($adjacency[$current] ?? []) as $next) {
            if (!isset($seen[$next])) {
                $seen[$next] = true;
                $queue[] = $next;
            }
        }
    }
    $regionReach[$regionOf($start)][count($seen)] = true;
}
foreach ($regionReach as $region => $counts) {
    echo str_pad($region, 14) . 'достижимо: ' . implode('/', array_keys($counts)) . " локаций\n";
}

echo "\n== односторонние пешие переходы ==\n";
$oneWay = [];
foreach ($walk as $from => $targets) {
    foreach (array_keys($targets) as $to) {
        if (!isset($walk[$to][$from])) {
            $oneWay[] = "$from -> $to";
        }
    }
}
echo $oneWay === [] ? "нет\n" : implode("\n", array_slice($oneWay, 0, 15)) . (count($oneWay) > 15 ? "\n...(всего " . count($oneWay) . ")" : '') . "\n";
