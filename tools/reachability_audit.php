<?php
declare(strict_types=1);

/**
 * Релизная проверка достижимости: из стартового города должны быть достижимы все
 * локации — по пешим переходам (include/data.world.php) с учётом транспорта.
 *
 * Транспорт считаем двусторонним: иначе игрок уедет и не вернётся, а это тупик.
 * Usage: php tools/reachability_audit.php
 */
require __DIR__ . '/../src/Support/Autoload.php';

Pokemon8\Support\Env::load(dirname(__DIR__) . '/.env');
$db = Pokemon8\Database\Connection::make(require dirname(__DIR__) . '/config/database.php');

$titles = [];
$all = [];
foreach ($db->query('SELECT id, title FROM build') as $row) {
    $titles[(int) $row['id']] = (string) $row['title'];
    $all[] = (int) $row['id'];
}

$dataLoc = [];
require dirname(__DIR__) . '/include/data.world.php';

$adjacency = [];
$link = static function (int $from, int $to) use (&$adjacency): void {
    if ($from > 0 && $to > 0 && $from !== $to) {
        $adjacency[$from][$to] = true;
        $adjacency[$to][$from] = true;
    }
};

$walkEdges = 0;
foreach ((array) $dataLoc as $from => $neighbours) {
    foreach ((array) $neighbours as $to) {
        $link((int) $from, (int) $to);
        $walkEdges++;
    }
}

$transportEdges = 0;
foreach (['transport_routes' => ['from_location_id', 'to_location_id']] as $table => $columns) {
    try {
        foreach ($db->query("SELECT {$columns[0]} a, {$columns[1]} b, enabled FROM `$table`") as $row) {
            if ((int) $row['enabled'] !== 1) {
                continue;
            }
            $link((int) $row['a'], (int) $row['b']);
            $transportEdges++;
        }
    } catch (Throwable $error) {
        echo "таблица $table недоступна: {$error->getMessage()}\n";
    }
}
foreach ($db->query('SELECT to_location_id t, to_region r, enabled FROM transport_flight_routes') as $row) {
    if ((int) $row['enabled'] === 1) {
        $link(1, (int) $row['t']);
        $transportEdges++;
    }
}

echo "рёбер пеших переходов: $walkEdges, рёбер транспорта: $transportEdges\n";

$start = 1; // Алабастия — стартовый город
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

$unreachable = array_values(array_diff($all, array_keys($seen)));
echo 'всего локаций: ' . count($all) . ', достижимо из Алабастии: ' . count($seen) . "\n";
if ($unreachable === []) {
    echo "недостижимых нет\n";
} else {
    echo 'НЕДОСТИЖИМЫЕ (' . count($unreachable) . "):\n";
    foreach (array_slice($unreachable, 0, 30) as $id) {
        echo "  $id  {$titles[$id]}\n";
    }
    if (count($unreachable) > 30) {
        echo '  … и ещё ' . (count($unreachable) - 30) . "\n";
    }
}

// Локации без единой связи вообще.
$isolated = [];
foreach ($all as $id) {
    if (!isset($adjacency[$id])) {
        $isolated[] = $id;
    }
}
echo 'без связей вообще: ' . count($isolated) . ($isolated === [] ? "\n" : ' — ' . implode(', ', array_slice($isolated, 0, 20)) . "\n");
