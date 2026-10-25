<?php
declare(strict_types=1);

use Pokemon8\Database\Connection;
use Pokemon8\Game\LocationStateService;
use Pokemon8\Game\MapMoveService;
use Pokemon8\Game\NpcDialogService;
use Pokemon8\Game\WorldDataRepository;
use Pokemon8\Repository\InventoryRepository;
use Pokemon8\Repository\LocationRepository;
use Pokemon8\Repository\PokemonRepository;
use Pokemon8\Repository\QuestRepository;
use Pokemon8\Repository\TransportRepository;

if (PHP_SAPI !== 'cli') { http_response_code(404); exit(2); }
define('APP_ROOT',dirname(__DIR__));
require APP_ROOT . '/src/Support/Autoload.php';
\Pokemon8\Support\Env::load(APP_ROOT . '/.env');
if (!\Pokemon8\Repository\AdminGrantReceiptRepository::localQa()) { fwrite(STDERR,"Local QA only.\n"); exit(2); }
$config = require APP_ROOT . '/config/database.php';
$server = Connection::make($config);
$schema = 'pokemonchic_regions_qa_' . bin2hex(random_bytes(6));
$created = false;
$passed = 0;
function regionCheck(bool $ok,string $label): void {
    if (!$ok) { throw new RuntimeException('FAIL ' . $label); }
    $GLOBALS['passed']++;
    echo "PASS $label\n";
}
function directedRegionPath(array $edges,array $ids,int $from,int $to): array {
    $queue = [[$from]];
    $seen = [$from=>true];
    while ($queue) {
        $path = array_shift($queue);
        $last = end($path);
        if ($last === $to) { return $path; }
        foreach ($edges[$last] ?? [] as $next) {
            if (isset($ids[$next]) && !isset($seen[$next])) { $seen[$next]=true; $queue[]=[...$path,$next]; }
        }
    }
    return [];
}
function regionFly(PDO $db,InventoryRepository $inventory,TransportRepository $transport,int $routeId): array {
    $inventory->addItem(1,TransportRepository::PLANE_TICKET_ITEM_ID,1);
    $ticket = (int)$db->query('SELECT id FROM items_users WHERE user_id=1 AND item_id=' . TransportRepository::PLANE_TICKET_ITEM_ID . ' ORDER BY id LIMIT 1')->fetchColumn();
    $start = $transport->startFlight(1,$ticket,$routeId);
    regionCheck(!empty($start['ok']),'flight.start.' . $routeId);
    regionCheck(empty($transport->exitFlight(1)['ok']),'flight.early_exit_rejected.' . $routeId);
    $db->exec("UPDATE transport_flights SET arrives_at=" . (time()-1) . " WHERE user_id=1 AND status='active'");
    return $transport->exitFlight(1);
}
try {
    $server->exec('CREATE DATABASE `' . $schema . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $created = true;
    $qaConfig = $config;
    $qaConfig['database'] = $schema;
    $db = Connection::make($qaConfig);
    $sourceSchema = str_replace('`','``',$config['database']);
    foreach ($server->query("SHOW FULL TABLES WHERE Table_type='BASE TABLE'")->fetchAll(PDO::FETCH_NUM) as $row) {
        $table = str_replace('`','``',$row[0]);
        $db->exec('CREATE TABLE `' . $table . '` LIKE `' . $sourceSchema . '`.`' . $table . '`');
    }
    foreach (['build','items','pokebuild','transport_routes','transport_flight_routes','site_settings','world_graph_sources','world_graph_edges','world_location_content','poke_base','har'] as $table) {
        $db->exec('INSERT INTO `' . $table . '` SELECT * FROM `' . $sourceSchema . '`.`' . $table . '`');
    }
    $db->exec('INSERT INTO users SELECT * FROM `' . $sourceSchema . '`.users WHERE id=1');
    $db->exec('INSERT INTO pok_user SELECT * FROM `' . $sourceSchema . '`.pok_user WHERE users=1 AND active=1 LIMIT 1');
    $db->exec('INSERT INTO attac_my_poke SELECT * FROM `' . $sourceSchema . '`.attac_my_poke WHERE pok_id IN (SELECT id FROM pok_user)');
    $db->exec("UPDATE site_settings SET value='1' WHERE name IN ('world.graph.db','world.content.db')");
    $db->exec('UPDATE users SET activation=1,buildmy=1,pve=0,pvp=0,trade=0,battleid=0 WHERE id=1');
    $locations = new LocationRepository($db);
    $graph = WorldDataRepository::runtimeGraph($db,APP_ROOT.'/missing-root');
    $content = WorldDataRepository::runtimeContent($db,APP_ROOT.'/missing-root');
    $inventory = new InventoryRepository($db);
    $transport = new TransportRepository($db,$inventory);
    $state = new LocationStateService($locations,$graph,APP_ROOT,$content,null,$transport);
    $moves = new MapMoveService($locations,$graph,$state);
    $npcs = new NpcDialogService($db,$locations,new QuestRepository($db),$inventory,new PokemonRepository($db),$content);
    $ids = array_fill_keys(array_map('intval',$db->query('SELECT id FROM build')->fetchAll(PDO::FETCH_COLUMN)),true);
    $regional = [...range(30000,30011),...range(31000,31011),...range(32000,32011)];
    $edges = $graph->allEdges();
    $walked = 0;
    $heals = 0;
    foreach ($regional as $id) {
        $locations->updateUserLocation(1,$id);
        $room = $state->currentStateForUser(1);
        regionCheck(!empty($room['ok']) && !str_contains($room['location']['title'],'Локация #') && mb_strlen($room['location']['description'])>50,'content.' . $id);
        $guide = $npcs->open(1,$id,['npc'=>'world_guide']);
        regionCheck(!empty($guide['ok']) && ($guide['npc']['text'] ?? '') === $room['location']['description'],'configured_guide.' . $id);
        regionCheck(empty($npcs->open(1,$id,['npc'=>'world_guide','do'=>'1'])['ok']),'forged_guide_params.' . $id);
        $hasJoy = false;
        foreach ($room['location']['npcs'] as $npc) {
            $dialog = $npcs->open(1,$id,$npc['params']);
            regionCheck(!empty($dialog['ok']),'npc.' . $id . '.' . ($npc['params']['npc']??''));
            if (($npc['params']['npc']??'') === '1') {
                $hasJoy = true;
                $db->exec('UPDATE pok_user SET hp_my=1 WHERE users=1 AND active=1');
                $db->exec('UPDATE attac_my_poke SET a_pp_min=0,b_pp_min=0,c_pp_min=0,d_pp_min=0');
                $heal = $npcs->action(1,$id,$npc['params'],'joy_heal');
                regionCheck(!empty($heal['ok']) && (int)$db->query('SELECT COUNT(*) FROM pok_user WHERE users=1 AND active=1 AND hp_my<>hp_max')->fetchColumn()===0,'heal.' . $id);
                regionCheck((int)$db->query('SELECT COUNT(*) FROM attac_my_poke WHERE a_pp_min<>a_pp_max OR b_pp_min<>b_pp_max OR c_pp_min<>c_pp_max OR d_pp_min<>d_pp_max')->fetchColumn()===0,'heal_pp.' . $id);
                $heals++;
            }
        }
        if (!$hasJoy) {
            regionCheck(empty($npcs->open(1,$id,['npc'=>'1','do_npc'=>'pc'])['ok']),'unconfigured_heal_dialog.rejected.' . $id);
            regionCheck(empty($npcs->action(1,$id,['npc'=>'1','do_npc'=>'pc'],'joy_heal')['ok']),'unconfigured_heal.rejected.' . $id);
        }
        regionCheck((int)$db->query('SELECT COUNT(*) FROM pokebuild WHERE building=' . $id)->fetchColumn()>0,'spawn_entries.' . $id);
        foreach ($edges[$id] ?? [] as $target) {
            if ($target===$id || !isset($ids[$target])) { continue; }
            $locations->updateUserLocation(1,$id);
            $moved = $moves->move(1,$target);
            regionCheck(!empty($moved['ok']) && (int)$locations->findUserState(1)['buildmy']===$target,'directed_edge.' . $id . '.' . $target);
            $walked++;
        }
    }
    $locations->updateUserLocation(1,1);
    regionCheck(empty($npcs->open(1,1,['npc'=>'world_guide'])['ok']),'unconfigured_guide.other_region_rejected');
    // Include only actual enabled directed transport. A cross-region walk alone
    // is not promised by the game: tickets and fares are still required.
    $travelEdges = $edges;
    $ships = [];
    foreach ($db->query('SELECT * FROM transport_routes WHERE enabled=1')->fetchAll() as $route) {
        $from = (int)$route['from_location_id'];
        $to = (int)$route['to_location_id'];
        $travelEdges[$from][] = $to;
        $ships[$from][$to] = $route;
    }
    $planeEdges = [];
    $flightRoutes = $db->query('SELECT * FROM transport_flight_routes WHERE enabled=1')->fetchAll();
    foreach ($db->query('SELECT id,town FROM build WHERE id<>95001')->fetchAll() as $location) {
        foreach ($flightRoutes as $route) {
            $region = (int)$location['town'];
            if (((int)$route['from_region']===0 || (int)$route['from_region']===$region) && ($region<=0 || (int)$route['to_region']!==$region)) {
                $from = (int)$location['id'];
                $to = (int)$route['to_location_id'];
                $travelEdges[$from][] = $to;
                $planeEdges[$from][$to] = (int)$route['id'];
            }
        }
    }
    // Directional paths are independently searched, never symmetrized.
    foreach ([30000,31000,32000] as $capital) {
        foreach ([[1,$capital],[$capital,1]] as [$from,$to]) {
            $path = directedRegionPath($travelEdges,$ids,$from,$to);
            regionCheck($path!==[],'entry_return_path.' . $from . '.' . $to);
            $locations->updateUserLocation(1,$from);
            foreach (array_slice($path,1) as $next) {
                $current = (int)$locations->findUserState(1)['buildmy'];
                if ($graph->canMove($current,$next)) {
                    $transition = $moves->move(1,$next);
                } elseif (isset($ships[$current][$next])) {
                    $route = $ships[$current][$next];
                    $inventory->addItem(1,(int)$route['price_item_id'],max(1,(int)$route['price_count']));
                    $transition = $transport->travel(1,(int)$route['id']);
                } else {
                    $transition = regionFly($db,$inventory,$transport,$planeEdges[$current][$next]);
                }
                regionCheck(!empty($transition['ok']) && (int)$locations->findUserState(1)['buildmy']===$next,'path_step.' . $current . '.' . $next);
            }
        }
    }
    $flights = 0;
    foreach ($db->query('SELECT * FROM transport_flight_routes WHERE enabled=1')->fetchAll() as $route) {
        $fromRegion = (int)$route['from_region'];
        $from = $fromRegion>0 ? (int)$db->query('SELECT MIN(id) FROM build WHERE town=' . $fromRegion)->fetchColumn() : ((int)$route['to_region']===1 ? 30000 : 1);
        $locations->updateUserLocation(1,$from);
        $exit = regionFly($db,$inventory,$transport,(int)$route['id']);
        regionCheck(!empty($exit['ok']) && (int)$locations->findUserState(1)['buildmy']===(int)$route['to_location_id'],'flight.arrival.' . $route['id']);
        $flights++;
    }
    regionCheck($heals===15 && $walked>60 && $flights>0,'coverage.regions_36_cities_15_directed_edges_and_flights');
    echo "Regional world: $passed/$passed; locations=36 cities=$heals directed_edges=$walked flights=$flights\n";
} finally {
    if ($created && preg_match('/\Apokemonchic_regions_qa_[a-f0-9]{12}\z/D',$schema)) { $server->exec('DROP DATABASE `' . $schema . '`'); }
}
