<?php
declare(strict_types=1);

// Read-only registry foundation: no content imports, progress updates or output files.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit(2); }
require dirname(__DIR__) . '/src/Support/Autoload.php';
use Pokemon8\Database\Connection;
use Pokemon8\Support\Env;
$root = dirname(__DIR__);
Env::load($root . '/.env');
$db = Connection::make(require $root . '/config/database.php');
$content = require $root . '/config/location_content.php';
$locations = $db->query('SELECT id, title FROM build ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
$definitions = $db->query('SELECT id, title, enabled, depends_on_quest_id FROM quest_definitions ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
$progress = $db->query('SELECT quest_id, COUNT(*) AS player_rows FROM quest GROUP BY quest_id ORDER BY quest_id')->fetchAll(PDO::FETCH_ASSOC);
$defined = array_map(static fn (array $row): int => (int) $row['id'], $definitions);
$dbLocationIds = array_map(static fn (array $row): int => (int) $row['id'], $locations);
$npcs = [];
foreach ($content as $locationId => $location) {
    foreach ($location['npcs'] ?? [] as $index => $npc) {
        $npcs[] = [
            'registry_key' => $locationId . ':' . $index,
            'location_id' => (int) $locationId,
            'title' => (string) ($npc['title'] ?? ''),
            'type' => (string) ($npc['type'] ?? 'npc'),
            'params' => $npc['params'] ?? [],
            'parity_status' => 'not_verified_by_inventory',
        ];
    }
}
$missingContent = array_values(array_filter($locations, static fn (array $row): bool => !isset($content[(int) $row['id']])));
$undefinedProgress = array_values(array_filter($progress, static fn (array $row): bool => !in_array((int) $row['quest_id'], $defined, true)));
$legacyFiles = array_map('basename', glob($root . '/include/rooms/npc/*.php') ?: []);
sort($legacyFiles);
$result = [
    'mode' => 'read_only',
    'warning' => 'Counts and references are not proof of migration or reachable gameplay. No player identifiers are exported.',
    'summary' => [
        'db_locations' => count($locations),
        'configured_locations' => count($content),
        'db_locations_without_content' => count($missingContent),
        'configured_npc_entries' => count($npcs),
        'legacy_npc_files' => count($legacyFiles),
        'quest_definitions' => count($definitions),
        'quest_steps' => (int) $db->query('SELECT COUNT(*) FROM quest_steps')->fetchColumn(),
        'quest_progress_rows' => array_sum(array_column($progress, 'player_rows')),
        'undefined_progress_quest_ids' => array_map(static fn (array $row): int => (int) $row['quest_id'], $undefinedProgress),
    ],
    'locations_without_content' => $missingContent,
    'content_without_db_location' => array_values(array_diff(array_map('intval', array_keys($content)), $dbLocationIds)),
    'undefined_progress' => $undefinedProgress,
    'quest_definitions' => $definitions,
    'legacy_quest_list' => $db->query('SELECT id_q, name_q FROM quest_list ORDER BY id_q')->fetchAll(PDO::FETCH_ASSOC),
    'npc_registry' => $npcs,
    'legacy_npc_files' => $legacyFiles,
];
if (!in_array('--json', $argv, true)) {
    $result = array_intersect_key($result, array_flip(['mode', 'warning', 'summary', 'locations_without_content', 'content_without_db_location', 'undefined_progress']));
}
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
