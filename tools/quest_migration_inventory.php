<?php
declare(strict_types=1);

/**
 * Read-only comparison: legacy quest tables vs the new quest layer.
 *
 *   & 'E:\OSPanel\modules\php\PHP_8.1\php.exe' tools\quest_migration_inventory.php
 */

require __DIR__ . '/../src/Support/Autoload.php';

Pokemon8\Support\Env::load(dirname(__DIR__) . '/.env');
$db = Pokemon8\Database\Connection::make(require dirname(__DIR__) . '/config/database.php');

function columns(PDO $db, string $table): array
{
    try {
        return array_map(
            static fn (array $row): string => (string) $row['Field'],
            $db->query("SHOW COLUMNS FROM `$table`")->fetchAll(PDO::FETCH_ASSOC)
        );
    } catch (Throwable $e) {
        return [];
    }
}

function countRows(PDO $db, string $table): int
{
    try {
        return (int) $db->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
    } catch (Throwable $e) {
        return -1;
    }
}

$legacy = ['quest_list', 'quest_process', 'quest_poke', 'quest_events', 'quest'];
$new = ['quest_definitions', 'quest_steps', 'user_quest_tracking'];

foreach ([['LEGACY', $legacy], ['NEW', $new]] as [$label, $tables]) {
    echo "== $label ==\n";
    foreach ($tables as $table) {
        $cols = columns($db, $table);
        if ($cols === []) {
            echo "$table: MISSING\n";
            continue;
        }
        echo sprintf("%-22s rows=%-7d cols: %s\n", $table, countRows($db, $table), implode(', ', $cols));
    }
    echo "\n";
}

echo "== legacy quest_list content ==\n";
try {
    $rows = $db->query('SELECT * FROM quest_list ORDER BY 1')->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        $flat = [];
        foreach ($row as $key => $value) {
            $value = (string) $value;
            if (mb_strlen($value) > 60) {
                $value = mb_substr($value, 0, 60) . '…';
            }
            $flat[] = $key . '=' . $value;
        }
        echo implode(' | ', $flat) . "\n";
    }
} catch (Throwable $e) {
    echo 'quest_list read failed: ' . $e->getMessage() . "\n";
}

echo "\n== new quest_definitions ==\n";
try {
    foreach ($db->query('SELECT * FROM quest_definitions ORDER BY id') as $row) {
        echo sprintf("#%s %s\n", (string) $row['id'], (string) ($row['title'] ?? $row['code'] ?? '-'));
    }
} catch (Throwable $e) {
    echo 'read failed: ' . $e->getMessage() . "\n";
}
