<?php
declare(strict_types=1);

use Pokemon8\Database\Connection;
use Pokemon8\Game\NpcDialogService;
use Pokemon8\Game\WorldDataRepository;
use Pokemon8\Repository\InventoryRepository;
use Pokemon8\Repository\LocationRepository;
use Pokemon8\Repository\PokemonRepository;
use Pokemon8\Repository\QuestRepository;
use Pokemon8\Repository\RewardRepository;
use Pokemon8\Repository\UserRepository;

if (PHP_SAPI !== 'cli') { http_response_code(404); exit(2); }
define('APP_ROOT',dirname(__DIR__));
require APP_ROOT . '/src/Support/Autoload.php';
\Pokemon8\Support\Env::load(APP_ROOT . '/.env');
if (!\Pokemon8\Repository\AdminGrantReceiptRepository::localQa()) { throw new RuntimeException('Local QA only'); }
function starterService(PDO $db): NpcDialogService {
    return new NpcDialogService($db,new LocationRepository($db),new QuestRepository($db),new InventoryRepository($db),new PokemonRepository($db),WorldDataRepository::runtimeContent($db,APP_ROOT),new RewardRepository($db));
}
if (in_array('--worker',$argv,true)) {
    $input=json_decode(stream_get_contents(STDIN),true,512,JSON_THROW_ON_ERROR);
    if (!preg_match('/\Apokemonchic_starter_qa_[a-f0-9]{12}\z/D',$input['config']['database']??'')) { throw new RuntimeException('Disposable schema required'); }
    $db=Connection::make($input['config']);
    $result=starterService($db)->action((int)$input['user'],3,['quest_npc'=>'1','do'=>'1'],'oak_finish_starter');
    echo json_encode(['ok'=>!empty($result['ok'])]); exit;
}
$config=require APP_ROOT . '/config/database.php';
$server=Connection::make($config);
$schema='pokemonchic_starter_qa_' . bin2hex(random_bytes(6));
$created=false;
$passed=0;
function starterCheck(bool $ok,string $name): void {
    if (!$ok) { throw new RuntimeException('FAIL ' . $name); }
    $GLOBALS['passed']++; echo "PASS $name\n";
}
function starterSnapshot(PDO $db,int $user): array {
    $result=[];
    foreach (['pok_user','attac_my_poke','items_users','quest','starter_quest_reward_claims','reward_transactions','reward_transaction_entries','game_notifications'] as $table) {
        $result[$table]=(int)$db->query('SELECT COUNT(*) FROM `' . $table . '`')->fetchColumn();
    }
    $result['rank']=(int)$db->query('SELECT rang_c FROM users WHERE id=' . $user)->fetchColumn();
    $result['items']=$db->query('SELECT item_id,count FROM items_users WHERE user_id=' . $user . ' ORDER BY item_id,id')->fetchAll();
    $result['quest']=$db->query('SELECT quest_id,process,gotov,pers FROM quest WHERE user_id=' . $user . ' ORDER BY id')->fetchAll();
    return $result;
}
function preparedStarter(PDO $db): int {
    $user=(new UserRepository($db))->createUser('Iso'.bin2hex(random_bytes(5)),password_hash(bin2hex(random_bytes(16)),PASSWORD_DEFAULT),null,'127.0.0.1');
    $id=(int)$user['id'];
    $db->exec('UPDATE users SET buildmy=3 WHERE id=' . $id);
    (new QuestRepository($db))->createIfMissing($id,1,3);
    $chosen=starterService($db)->action($id,3,['quest_npc'=>'1','do'=>'1'],'starter_select:1');
    if (empty($chosen['ok'])) { throw new RuntimeException('Starter fixture choice failed'); }
    return $id;
}
try {
    $server->exec('CREATE DATABASE `' . $schema . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $created=true;
    $qaConfig=$config; $qaConfig['database']=$schema;
    $db=Connection::make($qaConfig);
    $source=str_replace('`','``',$config['database']);
    foreach ($server->query("SHOW FULL TABLES WHERE Table_type='BASE TABLE'")->fetchAll(PDO::FETCH_NUM) as $row) {
        $table=str_replace('`','``',$row[0]);
        $db->exec('CREATE TABLE `' . $table . '` LIKE `' . $source . '`.`' . $table . '`');
    }
    foreach (['site_settings','items','build','quest_definitions','quest_steps','poke_base','har','attac_power','attac_poke','world_graph_sources','world_graph_edges','world_location_content'] as $table) {
        $db->exec('INSERT INTO `' . $table . '` SELECT * FROM `' . $source . '`.`' . $table . '`');
    }
    $db->exec('INSERT INTO users SELECT * FROM `' . $source . '`.users WHERE login="Система"');
    $quests=new QuestRepository($db);
    $service=$quests->starterRewards(new RewardRepository($db));
    foreach (\Pokemon8\Game\StarterQuestRewardService::PACKS as $quest=>$pack) {
        $stmt=$db->prepare('SELECT reward_json FROM quest_definitions WHERE id=?'); $stmt->execute([$quest]);
        $metadata=json_decode($stmt->fetchColumn(),true,512,JSON_THROW_ON_ERROR);
        starterCheck($metadata['items']===$pack['items'] && $metadata['rank']===$pack['rank'],'metadata_matches_pack.' . $quest);
    }
    $user=preparedStarter($db);
    $npc=starterService($db);
    $params=['quest_npc'=>'1','do'=>'1'];
    $before=starterSnapshot($db,$user);
    starterCheck(empty($npc->action($user,3,['quest_npc'=>'2'],'oak_finish_starter')['ok']),'wrong_npc.finish_rejected');
    starterCheck(empty($npc->action($user,3,['quest_npc'=>'2'],'starter_select:4')['ok']),'wrong_npc.choice_rejected');
    starterCheck(empty($npc->action($user,1,$params,'oak_finish_starter')['ok']),'wrong_location.rejected');
    starterCheck(empty($npc->action($user,3,$params,'starter_select:4')['ok']),'changed_choice.rejected');
    starterCheck(!empty($npc->action($user,3,$params,'starter_select:1')['ok']) && starterSnapshot($db,$user)===$before,'same_choice.idempotent');
    starterCheck(starterSnapshot($db,$user)===$before,'invalid_calls.zero_writes');
    starterCheck(empty($service->claim($user,102)['claimed']) && empty($service->claim($user,103)['claimed']),'future_stages.rejected');
    $db->exec('UPDATE quest SET process=2,pers=0 WHERE user_id=' . $user);
    starterCheck(empty($npc->action($user,3,$params,'starter_select:1')['ok']) && empty($npc->action($user,3,$params,'oak_finish_starter')['ok']),'early_choice_finish.rejected');
    $db->exec('UPDATE quest SET process=4,pers=1 WHERE user_id=' . $user);
    $before=starterSnapshot($db,$user);
    $db->exec("CREATE TRIGGER qa_fail_starter_receipt BEFORE INSERT ON starter_quest_reward_claims FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='isolated receipt failure'");
    starterCheck(empty($npc->action($user,3,$params,'oak_finish_starter')['ok']) && starterSnapshot($db,$user)===$before,'receipt_failure.rollback_starter_items_rank_progress_ledger');
    $db->beginTransaction();
    starterCheck(empty($npc->action($user,3,$params,'oak_finish_starter')['ok']) && $db->inTransaction() && starterSnapshot($db,$user)===$before,'receipt_failure.savepoint_preserves_outer');
    $db->exec('UPDATE users SET rang_c=rang_c WHERE id=' . $user);
    $db->rollBack();
    $db->exec('DROP TRIGGER qa_fail_starter_receipt');
    $db->beginTransaction();
    starterCheck(!empty($npc->action($user,3,$params,'oak_finish_starter')['ok']) && $db->inTransaction(),'caller_owned.success_does_not_commit');
    $db->rollBack();
    starterCheck(starterSnapshot($db,$user)===$before,'caller_owned.outer_rollback_removes_all');
    $db->beginTransaction();
    starterCheck(!empty($npc->action($user,3,$params,'oak_finish_starter')['ok']) && $db->inTransaction(),'caller_owned.success_pending_commit');
    $db->commit();
    $after=starterSnapshot($db,$user);
    starterCheck($after['starter_quest_reward_claims']===$before['starter_quest_reward_claims']+1 && $after['pok_user']===$before['pok_user']+1 && $after['rank']===$before['rank']+1,'starter.exactly_once');
    $inventory=new InventoryRepository($db);
    foreach ($service->pack(1)['items'] as $item=>$count) { starterCheck($inventory->countItem($user,$item)===$count,'oak.actual_item.' . $item); }
    starterCheck(empty($npc->action($user,3,$params,'oak_finish_starter')['ok']) && empty($npc->action($user,3,$params,'starter_select:1')['ok']) && starterSnapshot($db,$user)===$after,'replays.no_duplicate_starter_items_rank_receipt');
    $db->exec('UPDATE quest SET process=10 WHERE user_id=' . $user . ' AND quest_id=101');
    starterCheck(!empty($service->claim($user,101)['claimed']),'first_battle.pack_claim');
    $db->exec('UPDATE users SET buildmy=16 WHERE id=' . $user);
    starterCheck(!empty($service->claim($user,102)['claimed']),'town.pack_claim');
    starterCheck(!empty($service->claim($user,103)['claimed']),'transport.pack_claim');
    foreach ([1=>12000,3=>20,10=>5,15=>2,90111=>1] as $item=>$count) { starterCheck($inventory->countItem($user,$item)===$count,'chain.exact_total.' . $item); }
    $completed=starterSnapshot($db,$user);
    foreach ([1,101,102,103] as $quest) { starterCheck(empty($service->claim($user,$quest)['claimed']),'stage_replay.' . $quest); }
    starterCheck(starterSnapshot($db,$user)===$completed,'stage_replays.no_writes');
    $legacy=preparedStarter($db);
    $db->exec('UPDATE quest SET gotov=1,process=10 WHERE user_id=' . $legacy . ' AND quest_id=1');
    $before=starterSnapshot($db,$legacy);
    starterCheck(!empty($service->claim($legacy,1)['legacy_completed']) && starterSnapshot($db,$legacy)===$before,'legacy_completed.no_retroactive_grant_or_receipt');
    $concurrent=preparedStarter($db);
    $before=starterSnapshot($db,$concurrent);
    $processes=[];
    for ($i=0;$i<2;$i++) {
        $process=proc_open([PHP_BINARY,__FILE__,'--worker'],[['pipe','r'],['pipe','w'],['pipe','w']],$pipes,APP_ROOT);
        if (!is_resource($process)) { throw new RuntimeException('Unable to create isolated concurrency worker'); }
        fwrite($pipes[0],json_encode(['config'=>$qaConfig,'user'=>$concurrent],JSON_THROW_ON_ERROR)); fclose($pipes[0]);
        $processes[]=[$process,$pipes];
    }
    $wins=0;
    foreach ($processes as [$process,$pipes]) {
        $result=stream_get_contents($pipes[1]); $error=stream_get_contents($pipes[2]);
        fclose($pipes[1]); fclose($pipes[2]);
        starterCheck(proc_close($process)===0,'concurrent.worker_exit');
        $wins+=(int)!empty(json_decode($result,true,512,JSON_THROW_ON_ERROR)['ok']);
    }
    $after=starterSnapshot($db,$concurrent);
    starterCheck($wins===1 && $after['pok_user']===$before['pok_user']+1 && $after['starter_quest_reward_claims']===$before['starter_quest_reward_claims']+1 && $after['rank']===$before['rank']+1,'concurrent.two_requests_one_starter_receipt_reward');
    echo "Starter reward claims: $passed/$passed\n";
} finally {
    if (isset($db) && $db->inTransaction()) { $db->rollBack(); }
    if ($created && preg_match('/\Apokemonchic_starter_qa_[a-f0-9]{12}\z/D',$schema)) { $server->exec('DROP DATABASE `' . $schema . '`'); }
}
