<?php
declare(strict_types=1);

// Legacy inputs are read only by this offline importer, never copied as PHP runtime.
function worldImportSources(string $root): array
{
    $dataLoc = [];
    require $root . '/include/data.world.php';
    $edges = [];
    foreach ($dataLoc as $from => $targets) {
        $edges[(int)$from] = array_values(array_unique(array_map('intval', (array)$targets)));
    }
    $content = require $root . '/config/location_content.php';
    if (!is_array($content)) { throw new RuntimeException('Invalid world content source'); }
    return ['edges'=>$edges, 'content'=>$content];
}

function worldImportSnapshot(PDO $db): array
{
    $edges = [];
    foreach ($db->query('SELECT location_id FROM world_graph_sources ORDER BY sort_order')->fetchAll() as $row) {
        $edges[(int)$row['location_id']] = [];
    }
    foreach ($db->query('SELECT source_id,target_id FROM world_graph_edges ORDER BY source_id,sort_order')->fetchAll() as $row) {
        $from = (int)$row['source_id'];
        if (!array_key_exists($from,$edges)) { throw new RuntimeException('Edge without source registry'); }
        $edges[$from][] = (int)$row['target_id'];
    }
    $content = [];
    foreach ($db->query('SELECT location_id,content_json,source_hash FROM world_location_content ORDER BY sort_order')->fetchAll() as $row) {
        if (!hash_equals($row['source_hash'],hash('sha256',$row['content_json']))) {
            throw new RuntimeException('Content hash mismatch for location ' . (int)$row['location_id']);
        }
        $content[(int)$row['location_id']] = json_decode($row['content_json'],true,512,JSON_THROW_ON_ERROR);
    }
    return ['edges'=>$edges,'content'=>$content];
}

function worldImportSeed(PDO $db, array $source): array
{
    // Serialize importers. Never overwrite authored/live content, edges, flags or progress.
    if ((int)$db->query("SELECT GET_LOCK('pokemonchic_world_import',10)")->fetchColumn() !== 1) {
        throw new RuntimeException('World importer is busy');
    }
    try {
        foreach (['world.graph.db','world.content.db'] as $flag) {
            $stmt = $db->prepare('SELECT value FROM site_settings WHERE name=?');
            $stmt->execute([$flag]);
            if ((string)$stmt->fetchColumn() !== '0') { throw new RuntimeException('Import requires default-off world readers'); }
        }
        $db->beginTransaction();
        $sourceRow = $db->prepare('INSERT INTO world_graph_sources (location_id,sort_order) VALUES (?,?)');
        $edgeRow = $db->prepare('INSERT INTO world_graph_edges (source_id,target_id,sort_order) VALUES (?,?,?)');
        $existing = worldImportSnapshot($db);
        foreach (array_keys($existing['edges']) as $id) {
            if (!array_key_exists($id,$source['edges']) || $existing['edges'][$id] !== $source['edges'][$id]) {
                throw new RuntimeException('Existing graph differs at ' . $id . '; explicit reconciliation required');
            }
        }
        foreach ($existing['content'] as $id=>$content) {
            if (!array_key_exists($id,$source['content']) || $content !== $source['content'][$id]) {
                throw new RuntimeException('Existing content differs at ' . $id . '; no automatic overwrite');
            }
        }
        $inserted = ['sources'=>0,'edges'=>0,'content'=>0];
        $position = 0;
        foreach ($source['edges'] as $id=>$targets) {
            if (!array_key_exists($id,$existing['edges'])) {
                $sourceRow->execute([$id,$position]);
                $inserted['sources']++;
                foreach ($targets as $order=>$target) {
                    $edgeRow->execute([$id,$target,$order]);
                    $inserted['edges']++;
                }
            }
            $position++;
        }
        $contentRow = $db->prepare('INSERT INTO world_location_content (location_id,sort_order,content_json,source_hash) VALUES (?,?,?,?)');
        $position = 0;
        foreach ($source['content'] as $id=>$content) {
            if (!array_key_exists($id,$existing['content'])) {
                $json = json_encode($content,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);
                $contentRow->execute([$id,$position,$json,hash('sha256',$json)]);
                $inserted['content']++;
            }
            $position++;
        }
        if (worldImportSnapshot($db) !== $source) { throw new RuntimeException('Exact ordered world parity failed'); }
        $db->commit();
        return $inserted;
    } catch (Throwable $e) {
        if ($db->inTransaction()) { $db->rollBack(); }
        throw $e;
    } finally {
        $db->query("SELECT RELEASE_LOCK('pokemonchic_world_import')");
    }
}

function worldQuestFingerprint(PDO $db): string
{
    $hash = hash_init('sha256');
    foreach (['quest','user_quest_tracking'] as $table) {
        // Fingerprint values independently of the storage engine's incidental row order.
        $rows = $db->query('SELECT * FROM `' . $table . '`')->fetchAll();
        $encoded = array_map(static fn(array $row): string => json_encode($row,JSON_THROW_ON_ERROR),$rows);
        sort($encoded,SORT_STRING);
        hash_update($hash,$table . json_encode($encoded,JSON_THROW_ON_ERROR));
    }
    return hash_final($hash);
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') !== __FILE__) { return; }
if (PHP_SAPI !== 'cli') { http_response_code(404); exit(2); }
$root = dirname(__DIR__);
require $root . '/src/Support/Autoload.php';
\Pokemon8\Support\Env::load($root . '/.env');
if (!\Pokemon8\Repository\AdminGrantReceiptRepository::localQa()) { fwrite(STDERR,"Local QA only.\n"); exit(2); }
try {
    $db = \Pokemon8\Database\Connection::make(require $root . '/config/database.php');
    $source = worldImportSources($root);
    $before = worldQuestFingerprint($db);
    $inserted = in_array('--apply',$argv,true) ? worldImportSeed($db,$source) : null;
    $snapshot = worldImportSnapshot($db);
    $after = worldQuestFingerprint($db);
    $buildIds = array_map('intval',$db->query('SELECT id FROM build ORDER BY id')->fetchAll(PDO::FETCH_COLUMN));
    $contentIds = array_keys($source['content']);
    $flags = $db->query("SELECT name,value FROM site_settings WHERE name IN ('world.graph.db','world.content.db') ORDER BY name")->fetchAll();
    $report = ['ok'=>$snapshot === $source && $before === $after,'inserted'=>$inserted,
        'graph_sources'=>count($snapshot['edges']),'directed_edges'=>array_sum(array_map('count',$snapshot['edges'])),
        'content_locations'=>count($snapshot['content']),
        'npc_entries'=>array_sum(array_map(static fn(array $c): int=>count($c['npcs']??[]),$snapshot['content'])),
        'exact_ordered_graph_parity'=>$snapshot['edges'] === $source['edges'],
        'exact_raw_content_npc_params_parity'=>$snapshot['content'] === $source['content'],
        'quest_progress_unchanged'=>$before === $after,'quest_fingerprint'=>$after,'flags'=>$flags,
        'db_without_config'=>array_values(array_diff($buildIds,$contentIds)),
        'config_without_db'=>array_values(array_diff($contentIds,$buildIds))];
    echo json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR) . PHP_EOL;
    if (in_array('--save-report',$argv,true)) {
        file_put_contents($root . '/docs/reports/WORLD_DATA_PARITY_2026-10-25.json',json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR) . PHP_EOL);
    }
    exit($report['ok'] ? 0 : 1);
} catch (Throwable $e) { fwrite(STDERR,'[world_import] ' . $e->getMessage() . PHP_EOL); exit(1); }
