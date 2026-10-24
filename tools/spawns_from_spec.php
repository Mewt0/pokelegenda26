<?php
declare(strict_types=1);

/**
 * Turn a designer's spawn/drop spec (JSON) into a safe, idempotent migration.
 *
 * Why this exists: two delegated attempts to write this SQL failed — one resolved
 * location ids dynamically with REGEXP over mojibake, the other used
 * `INSERT ... SELECT ... WHERE NOT EXISTS` and died on "Illegal mix of collations".
 * Here the model only supplies names and numbers; ids, SQL shape and idempotency
 * are ours.
 *
 * Usage: php tools/spawns_from_spec.php <spec.json> <out.sql>
 */

require __DIR__ . '/../src/Support/Autoload.php';

Pokemon8\Support\Env::load(dirname(__DIR__) . '/.env');
$db = Pokemon8\Database\Connection::make(require dirname(__DIR__) . '/config/database.php');

$specPath = $argv[1] ?? '';
$outPath = $argv[2] ?? '';
if ($specPath === '' || $outPath === '' || !is_file($specPath)) {
    fwrite(STDERR, "Usage: php tools/spawns_from_spec.php <spec.json> <out.sql>\n");
    exit(2);
}

$spec = json_decode((string) file_get_contents($specPath), true);
if (!is_array($spec)) {
    fwrite(STDERR, "Spec is not valid JSON.\n");
    exit(2);
}

// name -> base id, straight from the game data.
$byName = [];
foreach ($db->query('SELECT id, Name FROM pokemon') as $row) {
    $byName[strtolower((string) $row['Name'])] = (int) $row['id'];
}

// Which pokemon do the live quests require? Those must end up spawnable.
$questSpecies = [];
foreach ($db->query(
    "SELECT s.quest_id, s.description FROM quest_steps s WHERE s.description LIKE '%#%' OR s.description LIKE '%уровня%'"
) as $row) {
    if (preg_match_all('/\b([A-Z][a-z]{3,})/', (string) $row['description'], $matches)) {
        foreach ($matches[1] as $word) {
            $key = strtolower($word);
            if (isset($byName[$key])) {
                $questSpecies[$key] = true;
            }
        }
    }
}
// Species the quest chain explicitly names, even if the step text is phrased oddly.
foreach (['oddish', 'butterfree', 'arbok', 'venomoth', 'beedrill', 'primeape', 'horsea', 'metapod', 'pidgeotto', 'spearow', 'venonat', 'poliwhirl', 'weepinbell'] as $name) {
    $questSpecies[$name] = true;
}

$lines = [];
$lines[] = '-- Автоматически собрано tools/spawns_from_spec.php из дизайн-спеки.';
$lines[] = '-- Только явные числовые id локаций и покемонов: без SELECT внутри INSERT,';
$lines[] = '-- без сравнения строк — значит без коллаций и без динамического SQL.';
$lines[] = '';
$lines[] = 'SET NAMES utf8mb4;';
$lines[] = '';

// Идемпотентность без опоры на уникальный индекс: сначала снимаем ровно те пары
// (локация, покемон), которые собираемся вставить, потом вставляем заново.
$pairs = [];
foreach (($spec['spawns'] ?? []) as $block) {
    $location = (int) ($block['location'] ?? 0);
    if ($location <= 0) {
        continue;
    }
    foreach (($block['pokemon'] ?? []) as $entry) {
        $name = strtolower((string) ($entry['name'] ?? ''));
        if (isset($byName[$name])) {
            $pairs[] = sprintf('(%d, %d)', $location, $byName[$name]);
        }
    }
}
$pairs = array_values(array_unique($pairs));
if ($pairs !== []) {
    $lines[] = '-- Повторный прогон не плодит дубликаты.';
    $lines[] = 'DELETE FROM pokebuild WHERE (building, baseid) IN (' . implode(', ', $pairs) . ');';
    $lines[] = '';
}

$spawnCount = 0;
$timeWindows = [];
foreach (($spec['spawns'] ?? []) as $block) {
    $location = (int) ($block['location'] ?? 0);
    if ($location <= 0) {
        continue;
    }
    foreach (($block['pokemon'] ?? []) as $entry) {
        $name = strtolower((string) ($entry['name'] ?? ''));
        if (!isset($byName[$name])) {
            fwrite(STDERR, "Unknown pokemon in spec: {$name}\n");
            continue;
        }
        $baseId = $byName[$name];
        $level = max(1, (int) ($entry['level'] ?? 1));
        $weight = max(1, min(999, (int) ($entry['weight'] ?? 100)));
        $time = $entry['time'] ?? null;
        $from = '00:00:00';
        $to = '23:59:59';
        if (is_array($time) && count($time) === 2) {
            $from = (string) $time[0];
            $to = (string) $time[1];
            $timeWindows[$name] = true;
        }
        $lines[] = sprintf(
            "INSERT IGNORE INTO pokebuild (building, baseid, lvl, chance, poimka, quest_id, q_process, questupdate, timeone, timetwo, sprz) VALUES (%d, %d, %d, %d, 1, 0, 0, 0, '%s', '%s', 0);",
            $location,
            $baseId,
            $level,
            $weight,
            $from,
            $to
        );
        $spawnCount++;
    }
}

$dropCount = 0;
foreach (($spec['drops'] ?? []) as $block) {
    $location = (int) ($block['location'] ?? 0);
    if ($location <= 0) {
        continue;
    }
    foreach (($block['items'] ?? []) as $entry) {
        $itemId = (int) ($entry['item_id'] ?? 0);
        if ($itemId <= 0) {
            continue;
        }
        $chance = (float) ($entry['chance'] ?? 0);
        $min = max(1, (int) ($entry['min'] ?? 1));
        $max = max($min, (int) ($entry['max'] ?? 1));
        $note = str_replace("'", '', (string) ($entry['name'] ?? 'drop'));
        $lines[] = sprintf(
            "INSERT IGNORE INTO admin_drop_rules (item_id, location_id, pokebuild_id, pokemon_base_id, source_type, chance_percent, min_count, max_count, time_start, time_end, quest_id, quest_process, quest_complete, enabled, note, created_by, created_at, updated_at) VALUES (%d, %d, 0, 0, 'wild', %.4f, %d, %d, '00:00:00', '23:59:59', 0, 0, 0, 1, '%s', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());",
            $itemId,
            $location,
            $chance,
            $min,
            $max,
            $note
        );
        $dropCount++;
    }
}

// Coverage report: a quest species that never appears makes its quest unsolvable.
$spawnedNames = [];
foreach (($spec['spawns'] ?? []) as $block) {
    foreach (($block['pokemon'] ?? []) as $entry) {
        $spawnedNames[strtolower((string) ($entry['name'] ?? ''))] = true;
    }
}
$missing = array_values(array_diff(array_keys($questSpecies), array_keys($spawnedNames)));

file_put_contents($outPath, implode("\n", $lines) . "\n");
echo "spawns: $spawnCount, drops: $dropCount, night windows: " . count($timeWindows) . PHP_EOL;
echo 'quest species covered: ' . (count($questSpecies) - count($missing)) . '/' . count($questSpecies) . PHP_EOL;
if ($missing !== []) {
    echo 'NOT SPAWNABLE (quests would be unsolvable): ' . implode(', ', $missing) . PHP_EOL;
}
echo "written: $outPath" . PHP_EOL;
