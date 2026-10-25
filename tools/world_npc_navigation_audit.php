<?php
declare(strict_types=1);

// Bounded local-QA navigation audit. All fixture/heartbeat writes are rolled back.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit(2); }
require dirname(__DIR__) . '/src/Support/Autoload.php';
use Pokemon8\Database\Connection;
use Pokemon8\Game\{LocationContentRepository, LocationGraph, LocationStateService, NpcDialogService};
use Pokemon8\Repository\{AdminGrantReceiptRepository, InventoryRepository, LocationRepository, PokemonEvolutionRepository, PokemonRepository, QuestRepository, RewardRepository, SafeStorageRepository, TransportRepository};
\Pokemon8\Support\Env::load(dirname(__DIR__) . '/.env');
if (!AdminGrantReceiptRepository::localQa()) { fwrite(STDERR, "Local QA only.\n"); exit(2); }
$root = dirname(__DIR__);
$db = Connection::make(require $root . '/config/database.php');
$locations = new LocationRepository($db);
$safe = new SafeStorageRepository($db);
$inventory = new InventoryRepository($db, new PokemonEvolutionRepository($db));
$rewards = new RewardRepository($db, $safe);
$inventory->setSafeStorageRepository($safe);
$inventory->setRewardRepository($rewards);
$quests = new QuestRepository($db);
$useWorldDb = in_array('--world-db', $argv, true);
$content = $useWorldDb ? LocationContentRepository::fromDatabase($db) : LocationContentRepository::fromFile($root . '/config/location_content.php');
$raw = $useWorldDb ? (new \Pokemon8\Game\WorldDataRepository($db))->locationContent() : require $root . '/config/location_content.php';
$transport = new TransportRepository($db, $inventory);
$transport->setQuestRepository($quests);
$transport->setRewardRepository($rewards);
$graph = $useWorldDb ? LocationGraph::fromDatabase($db) : LocationGraph::fromLegacyData($root . '/include/data.world.php');
$state = new LocationStateService($locations, $graph, $root, $content, null, $transport);
$npcs = new NpcDialogService($db, $locations, $quests, $inventory, new PokemonRepository($db), $content, $rewards);
$userId = (int) $db->query('SELECT id FROM users WHERE login = "Tacos" LIMIT 1')->fetchColumn();
if ($userId <= 0) { throw new RuntimeException('QA user missing.'); }
$rows = $db->query('SELECT id, title FROM build ORDER BY id')->fetchAll();
$dbIds = array_map(static fn ($row) => (int) $row['id'], $rows);
$result = ['mode'=>'local_qa_rollback', 'location_states'=>0, 'npc_entries'=>0, 'dialog_nodes'=>0, 'navigation_edges'=>0,
    'state_failures'=>[], 'empty_titles'=>[], 'orphan_content_ids'=>[], 'dialog_failures'=>[],
    'same_dialog_candidates'=>[], 'route_choices'=>[], 'action_choices'=>[], 'truncated_npc_entries'=>[]];
$prepare = $db->prepare('UPDATE users SET buildmy=:location,pve=0,pvp=0,trade=0,battleid=0,pve_button=0,atack_poke=0 WHERE id=:user');
$db->beginTransaction();
try {
    foreach ($rows as $row) {
        $id = (int) $row['id'];
        $prepare->execute(['location'=>$id,'user'=>$userId]);
        $view = $state->currentStateForUser($userId);
        $result['location_states']++;
        if (empty($view['ok'])) { $result['state_failures'][]=['id'=>$id,'error'=>$view['error'] ?? 'unknown']; }
        if (trim((string) ($view['location']['title'] ?? '')) === '') { $result['empty_titles'][]=$id; }
    }
    foreach ($raw as $locationId => $location) {
        if (!in_array((int) $locationId, $dbIds, true)) { $result['orphan_content_ids'][]=(int)$locationId; }
        foreach ($content->find((int)$locationId)['npcs'] ?? [] as $npc) {
            $result['npc_entries']++;
            $prepare->execute(['location'=>(int)$locationId,'user'=>$userId]);
            $queue = [[$npc['params'],0]];
            $seen = [];
            while ($queue && count($seen) < 30) {
                [$params,$depth] = array_shift($queue);
                ksort($params);
                $key = json_encode($params, JSON_THROW_ON_ERROR);
                if (isset($seen[$key])) { continue; }
                $seen[$key] = true;
                $dialog = $npcs->open($userId, (int)$locationId, $params);
                $result['dialog_nodes']++;
                $label = $locationId . ':' . $npc['title'];
                if (empty($dialog['ok'])) { $result['dialog_failures'][]=['npc'=>$label,'params'=>$params,'error'=>$dialog['message'] ?? 'unknown']; continue; }
                foreach ($dialog['npc']['choices'] ?? [] as $choice) {
                    if (!empty($choice['disabled']) || !empty($choice['close'])) { continue; }
                    if (!empty($choice['action'])) { $result['action_choices'][]=['npc'=>$label,'action'=>$choice['action']]; continue; }
                    if (!empty($choice['route'])) { $result['route_choices'][]=['npc'=>$label,'route'=>$choice['route']]; continue; }
                    if (!is_array($choice['params'] ?? null)) { continue; }
                    $result['navigation_edges']++;
                    $next = $npcs->open($userId, (int)$locationId, $choice['params']);
                    if (($next['npc'] ?? null) === ($dialog['npc'] ?? null)) {
                        $result['same_dialog_candidates'][]=['npc'=>$label,'button'=>$choice['label'] ?? '', 'from'=>$params,'to'=>$choice['params']];
                    }
                    if ($depth < 5) { $queue[]=[$choice['params'],$depth+1]; }
                }
            }
            if ($queue) { $result['truncated_npc_entries'][]=$label; }
        }
    }
} finally {
    if ($db->inTransaction()) { $db->rollBack(); }
}
$result['warning'] = 'No action/route was executed. Same-dialog edges are candidates, not proof of broken rewards. Existing QA quest progress only; not every quest state or player gate. Location-state enumeration is not a walk through directed routes.';
$result['world_source'] = $useWorldDb ? 'db' : 'legacy';
$result['legacy_world_inputs_loaded'] = array_values(array_filter(get_included_files(), static fn(string $path): bool => in_array(str_replace('\\','/',$path), [$root . '/include/data.world.php',$root . '/config/location_content.php'],true)));
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
exit($result['state_failures'] || $result['dialog_failures'] ? 1 : 0);
