<?php
declare(strict_types=1);

/**
 * Read-only inventory: what quests exist in the new layer, and what the legacy
 * source still holds. Run with the project PHP:
 *
 *   & 'E:\OSPanel\modules\php\PHP_8.1\php.exe' tools\quest_inventory.php
 */

require __DIR__ . '/../src/Support/Autoload.php';

Pokemon8\Support\Env::load(dirname(__DIR__) . '/.env');
$db = Pokemon8\Database\Connection::make(require dirname(__DIR__) . '/config/database.php');

$tables = [];
foreach ($db->query("SHOW TABLES LIKE '%quest%'")->fetchAll(PDO::FETCH_COLUMN) as $name) {
    $tables[] = (string) $name;
}
echo 'tables: ' . implode(', ', $tables) . PHP_EOL;

foreach (['quest_definitions', 'quest_steps', 'quest', 'quest_rewards'] as $table) {
    if (!in_array($table, $tables, true)) {
        echo $table . ': MISSING' . PHP_EOL;
        continue;
    }
    echo $table . ': ' . (int) $db->query("SELECT COUNT(*) FROM `$table`")->fetchColumn() . " rows\n";
}

if (in_array('quest_definitions', $tables, true)) {
    echo "\n-- quest_definitions --\n";
    foreach ($db->query('SELECT id, code, title, enabled, depends_on_quest_id FROM quest_definitions ORDER BY id') as $row) {
        echo sprintf(
            "%4d  enabled=%s  dep=%-4s  %-18s %s\n",
            (int) $row['id'],
            (string) $row['enabled'],
            (string) ($row['depends_on_quest_id'] ?? '0'),
            (string) ($row['code'] ?? '-'),
            (string) ($row['title'] ?? '-')
        );
    }
}

if (in_array('quest_steps', $tables, true)) {
    echo "\n-- steps per quest --\n";
    foreach ($db->query('SELECT quest_id, COUNT(*) c FROM quest_steps GROUP BY quest_id ORDER BY quest_id') as $row) {
        echo sprintf("quest %4d: %d steps\n", (int) $row['quest_id'], (int) $row['c']);
    }
}

echo "\n-- legacy quest references in include/ and game.php --\n";
$needles = ['quest', 'kvest', 'задан'];
$root = dirname(__DIR__);
$hits = 0;
foreach (['include', 'game.php', 'attak_pokes.php'] as $target) {
    $path = $root . '/' . $target;
    $files = is_dir($path)
        ? new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path))
        : [new SplFileInfo($path)];
    foreach ($files as $file) {
        if (!$file->isFile() || !preg_match('/\.(php|js)$/', $file->getFilename())) {
            continue;
        }
        $content = (string) file_get_contents($file->getPathname());
        foreach ($needles as $needle) {
            if (stripos($content, $needle) !== false) {
                echo $file->getPathname() . "\n";
                $hits++;
                break;
            }
        }
    }
}
echo "legacy files mentioning quests: $hits\n";
