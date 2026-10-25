<?php
declare(strict_types=1);

// Dedicated disposable schema: never grants items to players in the live database.
use Pokemon8\Controller\AdminGrantApiController;
use Pokemon8\Database\Connection;
use Pokemon8\Http\Request;
use Pokemon8\Repository\AdminGrantReceiptRepository;
use Pokemon8\Repository\AdminRepository;
use Pokemon8\Security\Csrf;
use Pokemon8\Security\Session;
use Pokemon8\Service\AdminItemGrantV2Service;

if (PHP_SAPI !== 'cli') { http_response_code(404); exit(2); }
require dirname(__DIR__) . '/src/Support/Autoload.php';
\Pokemon8\Support\Env::load(dirname(__DIR__) . '/.env');
if (!AdminGrantReceiptRepository::localQa()) { fwrite(STDERR, "Local QA only.\n"); exit(2); }
$config = require dirname(__DIR__) . '/config/database.php';

function controller(PDO $db, ?Closure $fail = null, int $ttl = 600, ?Closure $clock = null): AdminGrantApiController {
    $session = new Session('admin_grant_qa');
    $admin = new AdminRepository($db);
    return new AdminGrantApiController($session, new Csrf($session), $admin,
        new AdminItemGrantV2Service($admin, new AdminGrantReceiptRepository($db, $admin, $fail), $session, $ttl, $clock));
}
function callApi(AdminGrantApiController $api, string $action, array $post, bool $csrf = true): array {
    if ($csrf) { $post['_csrf'] = $_SESSION['_csrf']; }
    $response = $api->handle(new Request('POST', '/api/admin/grants/items/' . $action, [], $post, []), $action);
    return [$response->status, json_decode($response->body, true, 512, JSON_THROW_ON_ERROR)];
}
function check(bool $ok, string $name): void {
    if (!$ok) { throw new RuntimeException('FAIL ' . $name); }
    $GLOBALS['passed'] = ($GLOBALS['passed'] ?? 0) + 1;
    echo "PASS $name\n";
}
function counts(PDO $db): array {
    $result = [];
    foreach (['items_users', 'pok_user', 'admin_audit_log', 'admin_grant_receipts'] as $table) {
        $result[$table] = (int) $db->query('SELECT COUNT(*) FROM ' . $table)->fetchColumn();
    }
    $result['quantity'] = (int) $db->query('SELECT COALESCE(SUM(count),0) FROM items_users WHERE user_id = 1 AND item_id = 90 AND dattimer = "not"')->fetchColumn();
    return $result;
}
function confirmation(array $preview, array $payload, ?string $key = null): array {
    return ['ticket' => $preview['data']['ticket'], 'payload' => $payload, 'reason' => 'Local isolated QA grant',
        'confirm' => true, 'idempotency_key' => $key ?? bin2hex(random_bytes(16))];
}

if (in_array('--worker', $argv, true)) {
    $input = json_decode(stream_get_contents(STDIN), true, 512, JSON_THROW_ON_ERROR);
    if (!preg_match('/\Apokemonchic_admin_grant_qa_[a-f0-9]{12}\z/D', $input['database'] ?? '')) { exit(2); }
    $config['database'] = $input['database'];
    $_SESSION = $input['session'];
    echo json_encode(callApi(controller(Connection::make($config)), 'confirm', $input['request']), JSON_THROW_ON_ERROR);
    exit;
}

$source = Connection::make($config);
$qaName = 'pokemonchic_admin_grant_qa_' . bin2hex(random_bytes(6));
$created = false;
try {
    $source->exec('CREATE DATABASE `' . $qaName . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $created = true;
    $qaConfig = $config;
    $qaConfig['database'] = $qaName;
    $db = Connection::make($qaConfig);
    foreach (['users', 'information_users', 'items', 'items_users', 'pok_user', 'admin_audit_log', 'admin_grant_receipts', 'site_settings'] as $table) {
        $db->exec('CREATE TABLE `' . $table . '` LIKE `' . str_replace('`', '``', $config['database']) . '`.`' . $table . '`');
    }
    foreach (['users' => 'id IN (1,29)', 'information_users' => 'users_id IN (1,29)', 'items' => 'id IN (1,90)',
        'items_users' => 'user_id = 1 AND item_id = 90'] as $table => $where) {
        $db->exec('INSERT INTO `' . $table . '` SELECT * FROM `' . str_replace('`', '``', $config['database']) . '`.`' . $table . '` WHERE ' . $where);
    }
    $db->exec('INSERT INTO site_settings (name,value,updated_by,updated_at) VALUES ("admin.grant.v2","0",0,0)');
    $_SESSION = ['id' => 1, '_csrf' => bin2hex(random_bytes(32)), '_csrf_issued_at' => time()];
    $api = controller($db);
    $payload = ['target_user_id' => 1, 'item_id' => 90, 'quantity' => 2];
    check(callApi($api, 'preview', $payload)[0] === 404, 'flag_off.preview');
    check(callApi($api, 'confirm', [])[0] === 404, 'flag_off.confirm');
    $db->exec('UPDATE site_settings SET value = "1" WHERE name = "admin.grant.v2"');
    foreach (['preview','confirm'] as $action) {
        $_SESSION['id'] = 29;
        check(callApi($api, $action, [])[0] === 403, 'non_admin.' . $action);
        $_SESSION['id'] = 1;
        check(callApi($api, $action, [], false)[0] === 419, 'csrf.' . $action);
    }
    check(callApi($api, 'preview', $payload + ['actor_id' => 29])[0] === 422, 'unknown_field');
    check(callApi($api, 'preview', array_replace($payload, ['quantity' => '1e3']))[0] === 422, 'quantity.strict');
    $baseline = counts($db);
    [$status, $preview] = callApi($api, 'preview', $payload);
    check($status === 200 && counts($db) === $baseline, 'preview.no_database_writes');
    $request = confirmation($preview, $payload);
    check(callApi($api, 'confirm', array_replace($request, ['reason' => '']))[0] === 422, 'reason.required');
    check(callApi($api, 'confirm', array_replace($request, ['confirm' => false]))[0] === 422, 'ack.required');
    check(callApi($api, 'confirm', array_replace($request, ['reason' => 'password=secret-value']))[0] === 422, 'reason.secret_rejected');
    $savedTickets = $_SESSION;
    unset($_SESSION['admin_item_grant_v2_tickets']);
    check(callApi($api, 'confirm', $request)[0] === 409, 'ticket.foreign_session');
    $_SESSION = $savedTickets;
    $tampered = $request;
    $tampered['payload']['quantity'] = 3;
    check(callApi($api, 'confirm', $tampered)[0] === 409, 'ticket.payload_changed');
    check(callApi($api, 'confirm', $request)[0] === 409, 'ticket.consumed');
    $now = time();
    $shortApi = controller($db, null, 1, static function () use (&$now): int { return $now; });
    [, $shortPreview] = callApi($shortApi, 'preview', $payload);
    $now += 2;
    check(callApi($shortApi, 'confirm', confirmation($shortPreview, $payload))[0] === 409, 'ticket.expired');
    [, $stalePreview] = callApi($api, 'preview', $payload);
    $db->beginTransaction();
    (new AdminRepository($db))->grantItem(1, ['user_id'=>1,'item_id'=>90,'count'=>1]);
    $db->commit();
    $staleCounts = counts($db);
    check(callApi($api, 'confirm', confirmation($stalePreview, $payload))[0] === 409 && counts($db) === $staleCounts, 'inventory.stale_no_partial_write');
    [, $goodPreview] = callApi($api, 'preview', $payload);
    $goodRequest = confirmation($goodPreview, $payload);
    $before = counts($db);
    [$status, $result] = callApi($api, 'confirm', $goodRequest);
    $after = counts($db);
    check($status === 200 && $after['quantity'] === $before['quantity'] + 2 && $after['admin_grant_receipts'] === $before['admin_grant_receipts'] + 1 && $after['admin_audit_log'] === $before['admin_audit_log'] + 1, 'confirm.exactly_one_grant_receipt_audit');
    [$status, $replay] = callApi($api, 'confirm', $goodRequest);
    check($status === 200 && $replay === $result && counts($db) === $after, 'replay.identical_no_duplicate');
    $conflict = $goodRequest;
    $conflict['payload']['quantity'] = 4;
    check(callApi($api, 'confirm', $conflict)[0] === 409 && counts($db) === $after, 'key.payload_conflict');
    $newKey = $goodRequest;
    $newKey['idempotency_key'] = bin2hex(random_bytes(16));
    check(callApi($api, 'confirm', $newKey)[0] === 409, 'ticket.new_key_reuse_rejected');
    [, $failPreview] = callApi($api, 'preview', $payload);
    $failureApi = controller($db, static function (): void { throw new RuntimeException('injected'); });
    $before = counts($db);
    check(callApi($failureApi, 'confirm', confirmation($failPreview, $payload))[0] === 503 && counts($db) === $before, 'rollback.after_credit_no_partial_rows');
    [, $concurrentPreview] = callApi($api, 'preview', $payload);
    $concurrentRequest = confirmation($concurrentPreview, $payload);
    $before = counts($db);
    $children = [];
    for ($i=0; $i<2; $i++) {
        $process = proc_open([PHP_BINARY, __FILE__, '--worker'], [['pipe','r'],['pipe','w'],['pipe','w']], $pipes);
        if (!is_resource($process)) { throw new RuntimeException('worker failed'); }
        fwrite($pipes[0], json_encode(['database'=>$qaName,'session'=>$_SESSION,'request'=>$concurrentRequest], JSON_THROW_ON_ERROR));
        fclose($pipes[0]);
        $children[] = [$process, $pipes];
    }
    $responses = [];
    foreach ($children as [$process,$pipes]) {
        $output = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[1]); fclose($pipes[2]);
        if (proc_close($process) !== 0) { throw new RuntimeException('worker error'); }
        $responses[] = json_decode($output, true, 512, JSON_THROW_ON_ERROR);
    }
    $after = counts($db);
    check($responses[0][0] === 200 && $responses[0] === $responses[1] && $after['quantity'] === $before['quantity'] + 2 && $after['admin_grant_receipts'] === $before['admin_grant_receipts'] + 1 && $after['admin_audit_log'] === $before['admin_audit_log'] + 1, 'concurrent.same_key_one_receipt');
    $stored = implode(' ', $db->query('SELECT payload FROM admin_audit_log')->fetchAll(PDO::FETCH_COLUMN)) . implode(' ', $db->query('SELECT response_json FROM admin_grant_receipts')->fetchAll(PDO::FETCH_COLUMN));
    check(!str_contains($stored, $_SESSION['_csrf']) && !str_contains($stored, $concurrentPreview['data']['ticket']) && !preg_match('/password|cookie|session_id|_csrf/i', $stored), 'receipt_audit.no_secrets');
    $db->exec('UPDATE site_settings SET value = "0" WHERE name = "admin.grant.v2"');
    $before = counts($db);
    $legacy = (new AdminRepository($db))->grantItem(1, ['user_id'=>1,'item_id'=>90,'count'=>1]);
    check($legacy['ok'] && counts($db)['quantity'] === $before['quantity'] + 1, 'flag_off.old_flow_unchanged');
    echo 'RESULT ' . $GLOBALS['passed'] . '/' . $GLOBALS['passed'] . " PASS; live player inventory untouched\n";
} finally {
    if ($created && preg_match('/\Apokemonchic_admin_grant_qa_[a-f0-9]{12}\z/D', $qaName)) {
        if (isset($db) && $db->inTransaction()) { $db->rollBack(); }
        $db = null;
        $source->exec('DROP DATABASE `' . $qaName . '`');
        echo "Disposable QA schema removed.\n";
    }
}
