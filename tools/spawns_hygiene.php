<?php
declare(strict_types=1);

/**
 * Data hygiene after a partially applied migration: find duplicate spawn rows and
 * leftovers, so the table state is explainable rather than merely non-failing.
 *
 * Usage: php tools/spawns_hygiene.php [--clean]
 */
require __DIR__ . '/../src/Support/Autoload.php';

Pokemon8\Support\Env::load(dirname(__DIR__) . '/.env');
$db = Pokemon8\Database\Connection::make(require dirname(__DIR__) . '/config/database.php');

echo 'rows: ' . (int) $db->query('SELECT COUNT(*) FROM pokebuild')->fetchColumn() . "\n";
echo 'distinct (building, baseid, lvl): '
    . (int) $db->query('SELECT COUNT(*) FROM (SELECT DISTINCT building, baseid, lvl FROM pokebuild) t')->fetchColumn() . "\n";

echo "\n-- дубли (building, baseid, lvl) --\n";
$dupes = $db->query(
    'SELECT building, baseid, lvl, COUNT(*) c, GROUP_CONCAT(id ORDER BY id) ids
       FROM pokebuild GROUP BY building, baseid, lvl HAVING c > 1 ORDER BY c DESC LIMIT 12'
)->fetchAll(PDO::FETCH_ASSOC);
foreach ($dupes as $row) {
    echo "loc={$row['building']} base={$row['baseid']} lvl={$row['lvl']} x{$row['c']} ids={$row['ids']}\n";
}
if ($dupes === []) {
    echo "(нет)\n";
}

echo "\n-- строк на локацию (top 12) --\n";
foreach ($db->query('SELECT building, COUNT(*) c FROM pokebuild GROUP BY building ORDER BY c DESC LIMIT 12') as $row) {
    echo "loc={$row['building']} => {$row['c']}\n";
}

if (in_array('--clean', $argv, true)) {
    $deleted = 0;
    foreach ($dupes as $row) {
        $ids = array_map('intval', explode(',', (string) $row['ids']));
        array_shift($ids);
        if ($ids === []) {
            continue;
        }
        $db->exec('DELETE FROM pokebuild WHERE id IN (' . implode(',', $ids) . ')');
        $deleted += count($ids);
    }
    echo "\nудалено дублей: $deleted\n";
    echo 'rows now: ' . (int) $db->query('SELECT COUNT(*) FROM pokebuild')->fetchColumn() . "\n";
}
