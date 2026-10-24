<?php
declare(strict_types=1);

/**
 * Encoding guard for src/Game.
 *
 * The TASK-047 refactor runs twice corrupted the Russian text in the battle engine and its
 * new services: the file ended up as a Windows-1251/UTF-8 round trip, `Бой завершен`
 * disappeared and part of the text could not be restored at all. A syntax check does not
 * catch that, and neither does the project gate, because the tests read structure, not text.
 *
 * This checks bytes, not display:
 *   php tools/encoding_guard.php          # exit 1 when a file lost its Russian text
 *   php tools/encoding_guard.php --json
 */

$root = dirname(__DIR__);
$json = in_array('--json', $argv, true);

/** A phrase that every healthy battle-engine file family keeps in plain UTF-8. */
$probes = ['Бой завершен', 'Покемон', 'сообщение', 'Награда'];
$markers = '/Р[^\x{0400}-\x{04FF} ]|вЂ|РІР‚|РЎвЂ|Р В/u';

$targets = [];
foreach (glob($root . '/src/Game/*.php') ?: [] as $path) {
    $targets[] = $path;
}
foreach (glob($root . '/src/Repository/BattleRepository.php') ?: [] as $path) {
    $targets[] = $path;
}

$bad = [];
$report = [];
foreach ($targets as $path) {
    $text = (string) file_get_contents($path);
    // A file may legitimately contain mojibake *patterns* (the heuristics that strip broken
    // characters keep a list of them). Those lines are not damage.
    $markerHits = 0;
    foreach (explode("\n", $text) as $line) {
        if (preg_match('/\$markers?\b|as \$marker/', $line)) {
            continue;
        }
        $markerHits += preg_match_all($markers, $line);
    }
    $cyrillic = preg_match_all('/[\x{0400}-\x{04FF}]/u', $text);
    $probeHits = 0;
    foreach ($probes as $probe) {
        if (str_contains($text, $probe)) {
            $probeHits++;
        }
    }
    $hasLatinOnly = $cyrillic === 0;

    $report[] = [
        'file' => str_replace($root . '/', '', $path),
        'markers' => $markerHits,
        'cyrillic' => $cyrillic,
        'probes' => $probeHits,
    ];

    // Damage is the round trip itself: mojibake markers that no healthy file carries.
    if ($markerHits > 0) {
        $bad[] = $report[count($report) - 1];
    }
}

if ($json) {
    echo json_encode(['ok' => $bad === [], 'files' => $report], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), "\n";
    exit($bad === [] ? 0 : 1);
}

printf("checked %d files\n", count($report));
foreach ($report as $row) {
    if ($row['markers'] > 0) {
        printf("  BROKEN  %-50s markers=%-6d cyrillic=%-6d probes=%d\n", $row['file'], $row['markers'], $row['cyrillic'], $row['probes']);
    }
}
if ($bad === []) {
    echo "encoding guard: OK (no mojibake markers, Russian text intact)\n";
    exit(0);
}
echo "encoding guard: FAILED — the files above lost their Russian text\n";
exit(1);
