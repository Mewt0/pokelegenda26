<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') { http_response_code(404); exit(2); }
$root = dirname(__DIR__);
require $root . '/src/Support/Autoload.php';
\Pokemon8\Support\Env::load($root . '/.env');
if (!\Pokemon8\Repository\AdminGrantReceiptRepository::localQa()) { throw new RuntimeException('Local QA only'); }
$options = getopt('', ['login:', 'checkpoint:']);
$login = (string)($options['login']??'');
$checkpoint = (string)($options['checkpoint']??'');
if (!preg_match('/\AQAStart[a-z0-9]{1,9}\z/iD',$login) || !preg_match('/\A[a-z0-9_-]{1,40}\z/D',$checkpoint)) {
    throw new RuntimeException('Provide a QAStart account and safe checkpoint label');
}
$db = \Pokemon8\Database\Connection::make(require $root . '/config/database.php');
$user = $db->prepare('SELECT id,login,groups,buildmy,rang_c,pve,pvp,battleid FROM users WHERE login=?');
$user->execute([$login]);
$account = $user->fetch();
if (!$account || (int)$account['groups'] !== 6) { throw new RuntimeException('Expected normal non-admin test account'); }
$id = (int)$account['id'];
$snapshot = ['checkpoint'=>$checkpoint,'captured_at'=>date(DATE_ATOM),'account'=>$account];
foreach ([
    'inventory'=>'SELECT i.item_id,COALESCE(SUM(i.count),0) AS quantity,MAX(t.name) AS name FROM items_users i LEFT JOIN items t ON t.id=i.item_id WHERE i.user_id=? GROUP BY i.item_id ORDER BY i.item_id',
    'pokemon'=>'SELECT id,basenum,lvl,active,startepoke,hp_my,hp_max FROM pok_user WHERE users=? ORDER BY id',
    'quests'=>'SELECT quest_id,process,gotov,pers,time FROM quest WHERE user_id=? ORDER BY quest_id,id',
    'reward_transactions'=>'SELECT id,operation_key,source_type,source_id,title,status FROM reward_transactions WHERE user_id=? ORDER BY id',
] as $key=>$sql) {
    $stmt = $db->prepare($sql); $stmt->execute([$id]); $snapshot[$key]=$stmt->fetchAll();
}
$tables = $db->query("SHOW TABLES LIKE 'starter_quest_reward_claims'")->fetchColumn();
$snapshot['starter_claims'] = [];
if ($tables) {
    $stmt=$db->prepare('SELECT quest_id,operation_key,reward_json FROM starter_quest_reward_claims WHERE user_id=? ORDER BY quest_id');
    $stmt->execute([$id]); $snapshot['starter_claims']=$stmt->fetchAll();
}
$stmt=$db->prepare('SELECT e.transaction_id,e.reward_type,e.object_id,e.quantity,e.status FROM reward_transaction_entries e JOIN reward_transactions t ON t.id=e.transaction_id WHERE t.user_id=? ORDER BY e.id');
$stmt->execute([$id]); $snapshot['reward_entries']=$stmt->fetchAll();
$path=$root . '/docs/reports/NEW_PLAYER_REWARDS_2026-10-25_' . $login . '.json';
$history=is_file($path)?json_decode(file_get_contents($path),true,512,JSON_THROW_ON_ERROR):[];
$history[]=$snapshot;
file_put_contents($path,json_encode($history,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR).PHP_EOL);
echo json_encode($snapshot,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR).PHP_EOL;
