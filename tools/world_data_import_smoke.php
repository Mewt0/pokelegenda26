<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') { http_response_code(404); exit(2); }
$root = dirname(__DIR__);
require $root . '/src/Support/Autoload.php';
require __DIR__ . '/world_data_import.php';
\Pokemon8\Support\Env::load($root . '/.env');
if (!\Pokemon8\Repository\AdminGrantReceiptRepository::localQa()) { fwrite(STDERR,"Local QA only.\n"); exit(2); }
$config = require $root . '/config/database.php';
$server = \Pokemon8\Database\Connection::make($config);
$schema = 'pokemonchic_world_qa_' . bin2hex(random_bytes(6));
$created = false;
$passed = 0;
function checkWorld(bool $ok, string $label): void {
    if (!$ok) { throw new RuntimeException('FAIL ' . $label); }
    $GLOBALS['passed']++;
    echo "PASS $label\n";
}
function worldMustReject(PDO $db, array $source, string $label): void {
    $before = worldImportSnapshot($db);
    $rejected = false;
    try { worldImportSeed($db,$source); } catch (RuntimeException $e) { $rejected = true; }
    checkWorld($rejected && !$db->inTransaction() && worldImportSnapshot($db) === $before,$label);
}
try {
    $server->exec('CREATE DATABASE `' . $schema . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $created = true;
    $qaConfig = $config;
    $qaConfig['database'] = $schema;
    $db = \Pokemon8\Database\Connection::make($qaConfig);
    $db->exec('CREATE TABLE site_settings LIKE `' . str_replace('`','``',$config['database']) . '`.site_settings');
    $sql = file_get_contents($root . '/database/migrations/2026_10_25_000002_world_data.sql');
    foreach (explode(';',$sql) as $statement) { if (trim($statement) !== '') { $db->exec($statement); } }
    $source = worldImportSources($root);
    $seed = worldImportSeed($db,$source);
    checkWorld($seed['sources'] === count($source['edges']) && $seed['content'] === count($source['content']),'first_import.all_sources_content');
    checkWorld(worldImportSnapshot($db) === $source,'parity.exact_graph_content_order_types_params');
    $graph = \Pokemon8\Game\LocationGraph::fromDatabase($db);
    checkWorld($graph->allEdges() === $source['edges'],'db_reader.exact_graph_export');
    checkWorld($graph->movesFrom(999999) === [999999],'db_reader.unknown_location_fallback_preserved');
    $dbContent = \Pokemon8\Game\LocationContentRepository::fromDatabase($db);
    $legacyContent = \Pokemon8\Game\LocationContentRepository::fromFile($root . '/config/location_content.php');
    $equal = true;
    foreach (array_keys($source['content']) as $id) { $equal = $equal && $dbContent->find($id) === $legacyContent->find($id); }
    checkWorld($equal,'db_reader.normalized_content_npc_parity_all_locations');
    $reader = new \Pokemon8\Game\WorldDataRepository($db);
    checkWorld(!$reader->flagEnabled('world.graph.db') && !$reader->flagEnabled('world.content.db'),'flags.default_off');
    checkWorld(worldImportSeed($db,$source) === ['sources'=>0,'edges'=>0,'content'=>0],'second_import.zero_writes');
    $sourceSchema = str_replace('`','``',$config['database']);
    $db->exec('CREATE TABLE build LIKE `' . $sourceSchema . '`.build');
    $db->exec('INSERT INTO build SELECT * FROM `' . $sourceSchema . '`.build');
    $navigation = new ReflectionMethod(\Pokemon8\Repository\QuestRepository::class,'navigationForAction');
    $titles = new ReflectionMethod(\Pokemon8\Repository\QuestRepository::class,'locationTitle');
    $path = new ReflectionMethod(\Pokemon8\Repository\QuestRepository::class,'shortestLocationPath');
    $targets = new ReflectionMethod(\Pokemon8\Repository\QuestRepository::class,'questNavigationTargets');
    $legacyQuests = new \Pokemon8\Repository\QuestRepository($db);
    $expectedNavigation = [];
    foreach (array_keys($targets->invoke($legacyQuests)) as $action) {
        foreach ([1,7,11,30000,31000,32000] as $location) {
            $expectedNavigation[$action][$location] = $navigation->invoke($legacyQuests,$action,$location);
        }
    }
    $expectedTitles = [];
    foreach (array_keys($source['content']) as $id) { $expectedTitles[$id] = $titles->invoke($legacyQuests,$id); }
    checkWorld(\Pokemon8\Game\WorldDataRepository::runtimeGraph($db,$root)->allEdges() === $source['edges'],'flag_off.factory_reads_legacy_graph');
    checkWorld(\Pokemon8\Game\WorldDataRepository::runtimeContentData($db,$root) === $source['content'],'flag_off.factory_reads_legacy_content');
    $db->exec("UPDATE site_settings SET value='1' WHERE name IN ('world.graph.db','world.content.db')");
    checkWorld(\Pokemon8\Game\WorldDataRepository::runtimeGraph($db,$root.'/missing-root')->allEdges() === $source['edges'],'flag_on.graph_needs_no_legacy_root');
    checkWorld(\Pokemon8\Game\WorldDataRepository::runtimeContentData($db,$root.'/missing-root') === $source['content'],'flag_on.content_needs_no_legacy_root');
    $dbQuests = new \Pokemon8\Repository\QuestRepository($db);
    $equal = true;
    foreach ($expectedNavigation as $action=>$locations) {
        foreach ($locations as $id=>$expected) { $equal = $equal && $navigation->invoke($dbQuests,$action,$id) === $expected; }
    }
    checkWorld($equal,'all_known_quest_navigation_targets.six_start_locations_exact_parity');
    $equal = true;
    foreach ($expectedTitles as $id=>$expected) { $equal = $equal && $titles->invoke($dbQuests,$id) === $expected; }
    checkWorld($equal,'quest_location_titles.exact_parity');
    checkWorld($dbContent->find(6)['name'] === null && $titles->invoke($dbQuests,6) === (string)$db->query('SELECT title FROM build WHERE id=6')->fetchColumn(),'whitespace_title_6.uses_real_build_name');
    checkWorld($path->invoke($dbQuests,999999,1) === [],'quest_unknown_node.no_path_preserved');
    checkWorld($titles->invoke($dbQuests,30000) === (string)$db->query('SELECT title FROM build WHERE id=30000')->fetchColumn(),'uncovered_region_title.build_fallback_preserved');
    $db->exec("UPDATE site_settings SET value='0' WHERE name='world.content.db'");
    checkWorld(\Pokemon8\Game\WorldDataRepository::runtimeContentData($db,$root.'/missing-root') === [] && \Pokemon8\Game\WorldDataRepository::runtimeGraph($db,$root.'/missing-root')->allEdges() === $source['edges'],'mixed_flags.graph_db_content_legacy');
    $db->exec("UPDATE site_settings SET value=IF(name='world.content.db','1','0') WHERE name IN ('world.graph.db','world.content.db')");
    checkWorld(\Pokemon8\Game\WorldDataRepository::runtimeContentData($db,$root.'/missing-root') === $source['content'] && \Pokemon8\Game\WorldDataRepository::runtimeGraph($db,$root.'/missing-root')->allEdges() === [],'mixed_flags.graph_legacy_content_db');
    $db->exec("UPDATE site_settings SET value='0' WHERE name IN ('world.graph.db','world.content.db')");
    $db->exec("UPDATE site_settings SET value='1' WHERE name='world.graph.db'");
    worldMustReject($db,$source,'enabled_reader.reject_import');
    // Rerun migration preserves explicit operator flag values.
    foreach (explode(';',$sql) as $statement) { if (trim($statement) !== '') { $db->exec($statement); } }
    checkWorld((string)$db->query("SELECT value FROM site_settings WHERE name='world.graph.db'")->fetchColumn() === '1','migration.rerun_never_resets_flag');
    $db->exec("UPDATE site_settings SET value='0' WHERE name='world.graph.db'");
    $changed = $source;
    $first = array_key_first($changed['edges']);
    $changed['edges'][$first] = array_reverse($changed['edges'][$first]);
    worldMustReject($db,$changed,'graph_reorder.reject_no_overwrite');
    $changed = $source;
    $changed['content'][array_key_first($changed['content'])]['about'] = 'Authored content';
    worldMustReject($db,$changed,'content_change.reject_no_overwrite');
    $db->exec('DELETE FROM world_graph_edges');
    $db->exec('DELETE FROM world_graph_sources');
    $db->exec('DELETE FROM world_location_content');
    $fixture = ['edges'=>[5=>[5,9,7],9=>[],7=>[5]],'content'=>[5=>['npcs'=>[['title'=>'','params'=>['quest_npc'=>'03','do'=>'4']]]]]];
    worldImportSeed($db,$fixture);
    checkWorld(worldImportSnapshot($db) === $fixture,'empty_sources_directed_edges_invalid_titles_preserved');
    checkWorld(!in_array(5,worldImportSnapshot($db)['edges'][9],true),'no_reverse_edges_invented');
    $db->exec("UPDATE world_location_content SET content_json='{}' WHERE location_id=5");
    $rejected = false;
    try { worldImportSnapshot($db); } catch (RuntimeException $e) { $rejected = true; }
    checkWorld($rejected,'corrupt_content_hash.rejected');
    echo "World import: $passed/$passed\n";
} finally {
    if ($created && preg_match('/\Apokemonchic_world_qa_[a-f0-9]{12}\z/D',$schema)) {
        $server->exec('DROP DATABASE `' . $schema . '`');
    }
}
