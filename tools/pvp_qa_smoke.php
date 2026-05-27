<?php
declare(strict_types=1);

/**
 * Two-session PvP QA smoke for Tacos/NIGA.
 *
 * Covers invite, reject, timeout, accept, refresh sync, switch, item use,
 * anti-double-click, history and final-state cleanup through the real HTTP API.
 *
 * Usage:
 *   php tools/pvp_qa_smoke.php --password=jungheinrick
 *   php tools/pvp_qa_smoke.php --login1=Tacos --login2=NIGA --password1=... --password2=...
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Run from CLI.\n");
    exit(2);
}

if (!extension_loaded('curl')) {
    fwrite(STDERR, "PHP curl extension is required.\n");
    exit(2);
}

define('APP_ROOT', dirname(__DIR__));

require APP_ROOT . '/src/Support/Autoload.php';

Pokemon8\Support\Env::load(APP_ROOT . '/.env');

$options = parseOptions($argv);
$baseUrl = rtrim((string) ($options['base'] ?? getenv('SMOKE_BASE_URL') ?: 'http://pokemonchic.com'), '/');
$login1 = (string) ($options['login1'] ?? 'Tacos');
$login2 = (string) ($options['login2'] ?? 'NIGA');
$password1 = (string) ($options['password1'] ?? $options['password'] ?? getenv('SMOKE_PASSWORD') ?: '');
$password2 = (string) ($options['password2'] ?? $options['password'] ?? getenv('SMOKE_PASSWORD') ?: '');

if ($password1 === '' || $password2 === '') {
    fwrite(STDERR, "Missing --password or --password1/--password2.\n");
    exit(2);
}

$db = Pokemon8\Database\Connection::make(require APP_ROOT . '/config/database.php');
$results = [];

try {
    $tacos = loginClient($baseUrl, $login1, $password1, $results, 'tacos');
    $niga = loginClient($baseUrl, $login2, $password2, $results, 'niga');

    $user1 = findUserId($db, $login1);
    $user2 = findUserId($db, $login2);
    assertTrue($results, 'users.resolve', $user1 > 0 && $user2 > 0, "#$user1 $login1 / #$user2 $login2");

    resetPvpQaState($db, $user1, $user2);

    $pokemon1 = firstPokemonId($tacos, $results, 'tacos');
    $pokemon2 = firstPokemonId($niga, $results, 'niga');

    runInviteReject($tacos, $niga, $db, $results, $user2, $pokemon1);
    runInviteTimeout($tacos, $niga, $db, $results, $user1, $user2, $pokemon1);

    $attackBattleId = runAttackDoubleClickFlow($tacos, $niga, $results, $user1, $user2, $pokemon1, $pokemon2);
    $switchBattleId = runSwitchFlow($tacos, $niga, $results, $user2, $pokemon1, $pokemon2);
    $itemBattleId = runItemFlow($tacos, $niga, $db, $results, $user1, $user2, $pokemon1, $pokemon2);
    runHistoryChecks($tacos, $niga, $results, [$attackBattleId, $switchBattleId, $itemBattleId]);

    resetPvpQaState($db, $user1, $user2);
} catch (Throwable $e) {
    $results[] = ['label' => 'fatal', 'ok' => false, 'detail' => $e->getMessage()];
} finally {
    printResults($results);
}

/**
 * @return array<string,mixed>
 */
function parseOptions(array $argv): array
{
    $options = [];
    foreach (array_slice($argv, 1) as $arg) {
        if (!str_starts_with($arg, '--')) {
            continue;
        }
        $arg = substr($arg, 2);
        if (str_contains($arg, '=')) {
            [$key, $value] = explode('=', $arg, 2);
            $options[$key] = $value;
        } else {
            $options[$arg] = true;
        }
    }
    return $options;
}

function loginClient(string $baseUrl, string $login, string $password, array &$results, string $label): QaClient
{
    $client = new QaClient($baseUrl);
    $home = $client->request('GET', '/');
    $loginCsrf = extractCsrf($home['body']);
    assertTrue($results, $label . '.csrf', $loginCsrf !== '', 'home csrf');
    $client->request('POST', '/login', [
        '_csrf' => $loginCsrf,
        'LOGIN' => $login,
        'PASSWORD' => $password,
    ]);
    $game = $client->request('GET', '/game');
    $client->csrf = extractCsrf($game['body']);
    assertTrue($results, $label . '.login', $game['status'] === 200 && $client->csrf !== '', 'game csrf');
    return $client;
}

function findUserId(PDO $db, string $login): int
{
    $stmt = $db->prepare('SELECT id FROM users WHERE LOWER(login) = LOWER(:login) LIMIT 1');
    $stmt->execute(['login' => $login]);
    return (int) ($stmt->fetchColumn() ?: 0);
}

function resetPvpQaState(PDO $db, int $user1, int $user2): void
{
    $now = time();
    $stmt = $db->prepare(
        'UPDATE pvp_requests
            SET status = "expired", updated_at = :now
          WHERE status = "pending"
            AND ((from_user_id = :u1a AND to_user_id = :u2a) OR (from_user_id = :u2b AND to_user_id = :u1b))'
    );
    $stmt->execute(['now' => $now, 'u1a' => $user1, 'u2a' => $user2, 'u2b' => $user2, 'u1b' => $user1]);

    $db->prepare('UPDATE users SET pvp = 0, pve = 0, battleid = 0 WHERE id IN (:u1, :u2)')
        ->execute(['u1' => $user1, 'u2' => $user2]);
}

function firstPokemonId(QaClient $client, array &$results, string $label): int
{
    $payload = jsonCheck($client, $results, $label . '.pokemon-options', 'GET', '/api/battle/pvp/pokemon-options');
    $pokemon = is_array($payload['pokemon'] ?? null) ? $payload['pokemon'] : [];
    foreach ($pokemon as $row) {
        if ((int) ($row['hp'] ?? 0) > 0 && empty($row['disabled'])) {
            $id = (int) ($row['id'] ?? 0);
            assertTrue($results, $label . '.pokemon.pick', $id > 0, 'pokemon_id=' . $id);
            return $id;
        }
    }
    assertTrue($results, $label . '.pokemon.pick', false, 'no alive pokemon');
    return 0;
}

function runInviteReject(QaClient $tacos, QaClient $niga, PDO $db, array &$results, int $targetUserId, int $pokemon1): void
{
    $request = jsonCheck($tacos, $results, 'pvp.invite.send', 'POST', '/api/battle/pvp/request', [
        '_csrf' => $tacos->csrf,
        'user_id' => (string) $targetUserId,
        'pokemon_id' => (string) $pokemon1,
    ]);
    assertTrue($results, 'pvp.invite.status', ($request['ok'] ?? false) === true && ($request['status'] ?? '') === 'outgoing', $request['message'] ?? '');

    $incoming = jsonCheck($niga, $results, 'pvp.invite.incoming', 'GET', '/api/battle/pvp/requests');
    $requestId = firstRequestId($incoming);
    assertTrue($results, 'pvp.invite.incoming.visible', $requestId > 0, 'request_id=' . $requestId);

    $decline = jsonCheck($niga, $results, 'pvp.invite.reject', 'POST', '/api/battle/pvp/decline', [
        '_csrf' => $niga->csrf,
        'request_id' => (string) $requestId,
    ]);
    assertTrue($results, 'pvp.invite.reject.ok', ($decline['ok'] ?? false) === true, $decline['message'] ?? '');

    $status = (string) pvpRequestRowStatus($db, $requestId);
    assertTrue($results, 'pvp.invite.reject.db', $status === 'declined', 'status=' . $status);
}

function runInviteTimeout(QaClient $tacos, QaClient $niga, PDO $db, array &$results, int $user1, int $user2, int $pokemon1): void
{
    $request = jsonCheck($tacos, $results, 'pvp.timeout.send', 'POST', '/api/battle/pvp/request', [
        '_csrf' => $tacos->csrf,
        'user_id' => (string) $user2,
        'pokemon_id' => (string) $pokemon1,
    ]);
    assertTrue($results, 'pvp.timeout.sent', ($request['ok'] ?? false) === true, $request['message'] ?? '');

    $requestId = latestRequestId($db, $user1, $user2);
    assertTrue($results, 'pvp.timeout.request-id', $requestId > 0, 'request_id=' . $requestId);

    $db->prepare('UPDATE pvp_requests SET expires_at = :expired WHERE id = :id LIMIT 1')
        ->execute(['expired' => time() - 1, 'id' => $requestId]);

    $incoming = jsonCheck($niga, $results, 'pvp.timeout.poll', 'GET', '/api/battle/pvp/requests');
    assertTrue($results, 'pvp.timeout.not-visible', firstRequestId($incoming) !== $requestId, 'expired request hidden');

    $status = (string) pvpRequestRowStatus($db, $requestId);
    assertTrue($results, 'pvp.timeout.db', $status === 'expired', 'status=' . $status);
}

function startAcceptedBattle(QaClient $tacos, QaClient $niga, array &$results, int $targetUserId, int $pokemon1, int $pokemon2, string $label): int
{
    $request = jsonCheck($tacos, $results, $label . '.invite', 'POST', '/api/battle/pvp/request', [
        '_csrf' => $tacos->csrf,
        'user_id' => (string) $targetUserId,
        'pokemon_id' => (string) $pokemon1,
    ]);
    assertTrue($results, $label . '.invite.ok', ($request['ok'] ?? false) === true, $request['message'] ?? '');

    $incoming = jsonCheck($niga, $results, $label . '.incoming', 'GET', '/api/battle/pvp/requests');
    $requestId = firstRequestId($incoming);
    assertTrue($results, $label . '.request-id', $requestId > 0, 'request_id=' . $requestId);

    $accept = jsonCheck($niga, $results, $label . '.accept', 'POST', '/api/battle/pvp/accept', [
        '_csrf' => $niga->csrf,
        'request_id' => (string) $requestId,
        'pokemon_id' => (string) $pokemon2,
    ]);
    $battleId = (int) ($accept['battleId'] ?? 0);
    assertTrue($results, $label . '.accept.ok', ($accept['ok'] ?? false) === true && $battleId > 0, 'battle_id=' . $battleId);

    $acceptAgain = jsonCheck($niga, $results, $label . '.accept.double', 'POST', '/api/battle/pvp/accept', [
        '_csrf' => $niga->csrf,
        'request_id' => (string) $requestId,
        'pokemon_id' => (string) $pokemon2,
    ]);
    assertTrue($results, $label . '.accept.double.safe', ($acceptAgain['ok'] ?? false) === true && (int) ($acceptAgain['battleId'] ?? 0) === $battleId, 'battle_id=' . (int) ($acceptAgain['battleId'] ?? 0));

    $state1 = jsonCheck($tacos, $results, $label . '.state.tacos', 'GET', '/api/battle/pve/state');
    $state2 = jsonCheck($niga, $results, $label . '.state.niga', 'GET', '/api/battle/pve/state');
    assertSameBattle($results, $label . '.refresh.initial-sync', $state1, $state2, $battleId);

    return $battleId;
}

function runAttackDoubleClickFlow(QaClient $tacos, QaClient $niga, array &$results, int $user1, int $user2, int $pokemon1, int $pokemon2): int
{
    $battleId = startAcceptedBattle($tacos, $niga, $results, $user2, $pokemon1, $pokemon2, 'pvp.accept');
    $state1 = jsonCheck($tacos, $results, 'pvp.attack.state', 'GET', '/api/battle/pve/state');

    $moveId = firstMoveId($state1);
    assertTrue($results, 'pvp.attack.move-id', $moveId > 0, 'move_id=' . $moveId);
    $attack = jsonCheck($tacos, $results, 'pvp.attack.tacos', 'POST', '/api/battle/pve/action', [
        '_csrf' => $tacos->csrf,
        'action' => 'attack',
        'move_id' => (string) $moveId,
    ]);
    assertTrue($results, 'pvp.attack.accepted', ($attack['ok'] ?? false) === true, 'waiting=' . (int) !empty($attack['battle']['waitingForOpponent']));

    $double = jsonCheck($tacos, $results, 'pvp.attack.double-click', 'POST', '/api/battle/pve/action', [
        '_csrf' => $tacos->csrf,
        'action' => 'attack',
        'move_id' => (string) $moveId,
    ]);
    $doubleMessage = implode(' ', array_map('strval', (array) ($double['messages'] ?? [$double['message'] ?? ''])));
    assertTrue($results, 'pvp.attack.double-click.safe', str_contains($doubleMessage, 'Ход уже выбран'), $doubleMessage);

    finishBattleByEscape($tacos, $niga, $results, $battleId, $user2, 'pvp.attack.finish');
    return $battleId;
}

function runSwitchFlow(QaClient $tacos, QaClient $niga, array &$results, int $targetUserId, int $pokemon1, int $pokemon2): int
{
    $battleId = startAcceptedBattle($tacos, $niga, $results, $targetUserId, $pokemon1, $pokemon2, 'pvp.switch');
    $switchTarget = firstSwitchId(jsonCheck($tacos, $results, 'pvp.switch.tacos-state', 'GET', '/api/battle/pve/state'));
    if ($switchTarget > 0) {
        $switch = jsonCheck($tacos, $results, 'pvp.switch.tacos', 'POST', '/api/battle/pve/action', [
            '_csrf' => $tacos->csrf,
            'action' => 'switch',
            'pokemon_id' => (string) $switchTarget,
        ]);
        assertTrue($results, 'pvp.switch.ok', ($switch['ok'] ?? false) === true, $switch['messages'][0] ?? $switch['message'] ?? '');

        $doubleSwitch = jsonCheck($tacos, $results, 'pvp.switch.double-click', 'POST', '/api/battle/pve/action', [
            '_csrf' => $tacos->csrf,
            'action' => 'switch',
            'pokemon_id' => (string) $switchTarget,
        ]);
        $doubleMessage = implode(' ', array_map('strval', (array) ($doubleSwitch['messages'] ?? [$doubleSwitch['message'] ?? ''])));
        assertTrue($results, 'pvp.switch.double-click.safe', str_contains($doubleMessage, 'Ход уже выбран'), $doubleMessage);
    } else {
        skip($results, 'pvp.switch.ok', 'no switch option');
    }

    $enemySwitchTarget = firstSwitchId(jsonCheck($niga, $results, 'pvp.switch.niga-state', 'GET', '/api/battle/pve/state'));
    if ($enemySwitchTarget > 0) {
        jsonCheck($niga, $results, 'pvp.switch.resolve', 'POST', '/api/battle/pve/action', [
            '_csrf' => $niga->csrf,
            'action' => 'switch',
            'pokemon_id' => (string) $enemySwitchTarget,
        ]);
    }

    $postSwitch1 = jsonCheck($tacos, $results, 'pvp.refresh.after-switch.tacos', 'GET', '/api/battle/pve/state');
    $postSwitch2 = jsonCheck($niga, $results, 'pvp.refresh.after-switch.niga', 'GET', '/api/battle/pve/state');
    assertSameBattle($results, 'pvp.refresh.after-switch-sync', $postSwitch1, $postSwitch2, $battleId);

    finishBattleByEscape($tacos, $niga, $results, $battleId, $targetUserId, 'pvp.switch.finish');
    return $battleId;
}

function finishBattleByEscape(QaClient $tacos, QaClient $niga, array &$results, int $battleId, int $expectedWinner, string $label): void
{
    $escape = jsonCheck($tacos, $results, $label . '.escape', 'POST', '/api/battle/pve/action', [
        '_csrf' => $tacos->csrf,
        'action' => 'escape',
    ]);
    $escapeResult = (string) ($escape['battle']['result'] ?? $escape['result'] ?? '');
    assertTrue($results, $label . '.escape.ok', ($escape['ok'] ?? false) === true && !empty($escape['finished']) && $escapeResult === 'lose', 'winner expected=' . $expectedWinner . ', result=' . $escapeResult);

    $finished1 = jsonCheck($tacos, $results, $label . '.state.tacos', 'GET', '/api/battle/pve/state');
    $finished2 = jsonCheck($niga, $results, $label . '.state.niga', 'GET', '/api/battle/pve/state');
    assertTrue($results, $label . '.both-see-end', !empty($finished1['finished']) && !empty($finished2['finished']), 'battle_id=' . $battleId . ', tacos=' . (int) !empty($finished1['finished']) . ', niga=' . (int) !empty($finished2['finished']));
    assertTrue($results, $label . '.result-sync', ($finished1['battle']['result'] ?? $finished1['result'] ?? '') === 'lose' && ($finished2['battle']['result'] ?? $finished2['result'] ?? '') === 'win', 'tacos=' . ($finished1['battle']['result'] ?? $finished1['result'] ?? '') . ', niga=' . ($finished2['battle']['result'] ?? $finished2['result'] ?? ''));

    $logCount = battleLogCount($finished1);
    $escapeAgain = jsonCheck($tacos, $results, $label . '.escape.after-finish', 'POST', '/api/battle/pve/action', [
        '_csrf' => $tacos->csrf,
        'action' => 'escape',
    ]);
    assertTrue($results, $label . '.escape.after-finish.idempotent', !empty($escapeAgain['finished']) && battleLogCount($escapeAgain) === $logCount, 'log_count=' . $logCount . '/' . battleLogCount($escapeAgain));

    jsonCheck($tacos, $results, $label . '.ack.tacos', 'POST', '/api/battle/pve/ack-end', ['_csrf' => $tacos->csrf]);
    jsonCheck($niga, $results, $label . '.ack.niga', 'POST', '/api/battle/pve/ack-end', ['_csrf' => $niga->csrf]);
}

function runItemFlow(QaClient $tacos, QaClient $niga, PDO $db, array &$results, int $userId, int $targetUserId, int $pokemon1, int $pokemon2): int
{
    $battleId = startAcceptedBattle($tacos, $niga, $results, $targetUserId, $pokemon1, $pokemon2, 'pvp.item');
    $temporaryItemRowId = createTemporaryItemRow($db, $userId, 217);
    $state = jsonCheck($tacos, $results, 'pvp.item.state', 'GET', '/api/battle/pve/state');
    try {
        if (!empty($state['battle']['waitingForOpponent'])) {
            skip($results, 'pvp.item.use', 'tacos is waiting for opponent');
            finishBattleByEscape($tacos, $niga, $results, $battleId, $targetUserId, 'pvp.item.finish');
            return $battleId;
        }

        $bag = jsonCheck($tacos, $results, 'pvp.item.bag', 'GET', '/api/inventory/battle');
        $ball = firstBattleBall($bag);
        if ($ball !== null) {
            $directBall = jsonCheck($tacos, $results, 'pvp.ball.direct', 'POST', '/api/battle/pve/action', [
                '_csrf' => $tacos->csrf,
                'action' => 'ball',
                'item_user_id' => (string) (int) ($ball['id'] ?? 0),
            ]);
            $directBallMessage = implode(' ', array_map('strval', (array) ($directBall['messages'] ?? [$directBall['message'] ?? ''])));
            assertTrue($results, 'pvp.ball.direct.blocked', ($directBall['ok'] ?? true) === false && str_contains($directBallMessage, 'PvP'), $directBallMessage);

            $ballAsItem = jsonCheck($tacos, $results, 'pvp.ball.as-item', 'POST', '/api/battle/pve/action', [
                '_csrf' => $tacos->csrf,
                'action' => 'item',
                'item_user_id' => (string) (int) ($ball['id'] ?? 0),
            ]);
            $ballAsItemMessage = implode(' ', array_map('strval', (array) ($ballAsItem['messages'] ?? [$ballAsItem['message'] ?? ''])));
            assertTrue($results, 'pvp.ball.as-item.blocked', ($ballAsItem['ok'] ?? true) === false && str_contains($ballAsItemMessage, 'PvP'), $ballAsItemMessage);
        } else {
            skip($results, 'pvp.ball.blocked', 'no battle ball in inventory');
        }

        assertTrue($results, 'pvp.item.temp-row', $temporaryItemRowId > 0, 'items_users.id=' . $temporaryItemRowId);
        $use = jsonCheck($tacos, $results, 'pvp.item.use', 'POST', '/api/battle/pve/action', [
            '_csrf' => $tacos->csrf,
            'action' => 'item',
            'item_user_id' => (string) $temporaryItemRowId,
        ]);
        $itemMessage = $use['message'] ?? implode(' ', (array) ($use['messages'] ?? []));
        assertTrue($results, 'pvp.item.response', ($use['ok'] ?? false) === true && str_contains((string) $itemMessage, 'витамин PP'), 'ok=' . json_encode($use['ok'] ?? null) . ', message=' . $itemMessage);

        $enemySwitchTarget = firstSwitchId(jsonCheck($niga, $results, 'pvp.item.niga-state', 'GET', '/api/battle/pve/state'));
        if ($enemySwitchTarget > 0) {
            jsonCheck($niga, $results, 'pvp.item.resolve', 'POST', '/api/battle/pve/action', [
                '_csrf' => $niga->csrf,
                'action' => 'switch',
                'pokemon_id' => (string) $enemySwitchTarget,
            ]);
        }
        $sync1 = jsonCheck($tacos, $results, 'pvp.item.refresh.tacos', 'GET', '/api/battle/pve/state');
        $sync2 = jsonCheck($niga, $results, 'pvp.item.refresh.niga', 'GET', '/api/battle/pve/state');
        assertSameBattle($results, 'pvp.item.refresh-sync', $sync1, $sync2, $battleId);
        finishBattleByEscape($tacos, $niga, $results, $battleId, $targetUserId, 'pvp.item.finish');
        return $battleId;
    } finally {
        cleanupTemporaryItemRow($db, $temporaryItemRowId);
    }
}

/**
 * @param list<int> $battleIds
 */
function runHistoryChecks(QaClient $tacos, QaClient $niga, array &$results, array $battleIds): void
{
    $h1 = jsonCheck($tacos, $results, 'pvp.history.tacos', 'GET', '/api/battle/history');
    $h2 = jsonCheck($niga, $results, 'pvp.history.niga', 'GET', '/api/battle/history');
    foreach ($battleIds as $battleId) {
        assertTrue($results, 'pvp.history.tacos.contains.' . $battleId, historyContains($h1, $battleId), 'battle_id=' . $battleId);
        assertTrue($results, 'pvp.history.niga.contains.' . $battleId, historyContains($h2, $battleId), 'battle_id=' . $battleId);
        assertTrue($results, 'pvp.rewards.no-pve-leak.' . $battleId, !historyHasPveRewardLeak($h1, $battleId) && !historyHasPveRewardLeak($h2, $battleId), 'PvP history has no PvE reward text');
    }
}

function firstRequestId(array $payload): int
{
    $requests = is_array($payload['requests'] ?? null) ? $payload['requests'] : [];
    return (int) ($requests[0]['id'] ?? 0);
}

function latestRequestId(PDO $db, int $from, int $to): int
{
    $stmt = $db->prepare('SELECT id FROM pvp_requests WHERE from_user_id = :from AND to_user_id = :to ORDER BY id DESC LIMIT 1');
    $stmt->execute(['from' => $from, 'to' => $to]);
    return (int) ($stmt->fetchColumn() ?: 0);
}

function pvpRequestRowStatus(PDO $db, int $requestId): string
{
    $stmt = $db->prepare('SELECT status FROM pvp_requests WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $requestId]);
    return (string) ($stmt->fetchColumn() ?: '');
}

function createTemporaryItemRow(PDO $db, int $userId, int $itemId): int
{
    $rowId = (int) ($db->query('SELECT COALESCE(MAX(id), 0) + 1 FROM items_users')->fetchColumn() ?: 1);
    $stmt = $db->prepare(
        'INSERT INTO items_users (id, item_id, user_id, count, dattimer, timers)
         VALUES (:id, :item, :user, 1, "not", 0)'
    );
    $stmt->execute([
        'id' => $rowId,
        'item' => $itemId,
        'user' => $userId,
    ]);
    return $rowId;
}

function cleanupTemporaryItemRow(PDO $db, int $rowId): void
{
    if ($rowId <= 0) {
        return;
    }
    $stmt = $db->prepare('DELETE FROM items_users WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $rowId]);
}

function firstMoveId(array $payload): int
{
    $moves = is_array($payload['battle']['moves'] ?? null) ? $payload['battle']['moves'] : (is_array($payload['moves'] ?? null) ? $payload['moves'] : []);
    foreach ($moves as $move) {
        $id = (int) ($move['id'] ?? 0);
        $ppMax = (int) ($move['ppMax'] ?? $move['pp_max'] ?? 1);
        $pp = (int) ($move['pp'] ?? $move['pp_min'] ?? 1);
        if ($id > 0 && ($ppMax <= 0 || $pp > 0)) {
            return (int) ($move['id'] ?? 0);
        }
    }
    return 0;
}

function firstSwitchId(array $payload): int
{
    $options = is_array($payload['battle']['switchOptions'] ?? null) ? $payload['battle']['switchOptions'] : [];
    foreach ($options as $option) {
        if (empty($option['disabled']) && (int) ($option['id'] ?? 0) > 0) {
            return (int) $option['id'];
        }
    }
    return 0;
}

function firstBattleItem(array $payload): ?array
{
    $items = is_array($payload['items'] ?? null) ? $payload['items'] : [];
    foreach ([217, 861, 15] as $preferredId) {
        foreach ($items as $item) {
            if (($item['battle_pocket'] ?? '') === 'items' && (int) ($item['item_id'] ?? 0) === $preferredId) {
                return $item;
            }
        }
    }
    foreach ($items as $item) {
        if (($item['battle_pocket'] ?? '') === 'items' && (int) ($item['battleuse'] ?? 0) === 1) {
            return $item;
        }
    }
    return null;
}

function firstBattleBall(array $payload): ?array
{
    $items = is_array($payload['items'] ?? null) ? $payload['items'] : [];
    foreach ($items as $item) {
        if (($item['battle_pocket'] ?? '') === 'balls' && (int) ($item['id'] ?? 0) > 0) {
            return $item;
        }
    }
    return null;
}

function assertSameBattle(array &$results, string $label, array $left, array $right, int $battleId): void
{
    $leftBattle = is_array($left['battle'] ?? null) ? $left['battle'] : $left;
    $rightBattle = is_array($right['battle'] ?? null) ? $right['battle'] : $right;
    $ok = (int) ($leftBattle['id'] ?? 0) === $battleId
        && (int) ($rightBattle['id'] ?? 0) === $battleId
        && (int) ($leftBattle['round'] ?? 0) === (int) ($rightBattle['round'] ?? 0)
        && (($leftBattle['mode'] ?? '') === 'pvp')
        && (($rightBattle['mode'] ?? '') === 'pvp');
    assertTrue($results, $label, $ok, 'battle=' . (int) ($leftBattle['id'] ?? 0) . '/' . (int) ($rightBattle['id'] ?? 0) . ', round=' . (int) ($leftBattle['round'] ?? 0) . '/' . (int) ($rightBattle['round'] ?? 0));
}

function historyContains(array $payload, int $battleId): bool
{
    foreach ((array) ($payload['history'] ?? []) as $row) {
        if ((int) ($row['id'] ?? 0) === $battleId && ($row['mode'] ?? '') === 'pvp') {
            return true;
        }
    }
    return false;
}

function historyHasPveRewardLeak(array $payload, int $battleId): bool
{
    foreach ((array) ($payload['history'] ?? []) as $row) {
        if ((int) ($row['id'] ?? 0) !== $battleId) {
            continue;
        }
        foreach ((array) ($row['log'] ?? []) as $log) {
            $text = (string) (is_array($log) ? ($log['text'] ?? '') : $log);
            if (str_contains($text, 'Награда:') || str_contains($text, 'Дроп:')) {
                return true;
            }
        }
    }
    return false;
}

function battleLogCount(array $payload): int
{
    if (is_array($payload['battle']['log'] ?? null)) {
        return count($payload['battle']['log']);
    }
    if (is_array($payload['log'] ?? null)) {
        return count($payload['log']);
    }
    return 0;
}

function extractCsrf(string $html): string
{
    if (preg_match('/name=["\']_csrf["\'][^>]*value=["\']([^"\']+)["\']/i', $html, $m)) {
        return html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    if (preg_match('/data-csrf=["\']([^"\']+)["\']/i', $html, $m)) {
        return html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    return '';
}

function jsonCheck(QaClient $client, array &$results, string $label, string $method, string $path, array $data = []): array
{
    $response = $client->request($method, $path, $data);
    $json = json_decode($response['body'], true);
    $json = is_array($json) ? $json : [];
    assertTrue($results, $label, $response['status'] >= 200 && $response['status'] < 300 && $json !== [], 'status=' . $response['status']);
    return $json;
}

function assertTrue(array &$results, string $label, bool $ok, string $detail = ''): void
{
    $results[] = ['label' => $label, 'ok' => $ok, 'detail' => $detail];
    echo '[' . ($ok ? 'OK' : 'FAIL') . '] ' . $label . ($detail !== '' ? ' - ' . $detail : '') . PHP_EOL;
}

function skip(array &$results, string $label, string $detail = ''): void
{
    $results[] = ['label' => $label, 'ok' => true, 'skip' => true, 'detail' => $detail];
    echo '[SKIP] ' . $label . ($detail !== '' ? ' - ' . $detail : '') . PHP_EOL;
}

function printResults(array $results): void
{
    $failures = 0;
    foreach ($results as $result) {
        if (empty($result['ok'])) {
            $failures++;
        }
    }
    printf("\nResult: %d checks, %d failures.\n", count($results), $failures);
    if ($failures > 0) {
        exit(1);
    }
}

final class QaClient
{
    public string $csrf = '';
    private string $cookieFile;

    public function __construct(private string $baseUrl)
    {
        $this->cookieFile = tempnam(sys_get_temp_dir(), 'pokemon8_pvp_cookie_') ?: '';
    }

    public function __destruct()
    {
        if ($this->cookieFile !== '' && is_file($this->cookieFile)) {
            @unlink($this->cookieFile);
        }
    }

    /**
     * @param array<string,string> $data
     * @return array{status:int,body:string,url:string}
     */
    public function request(string $method, string $path, array $data = []): array
    {
        $method = strtoupper($method);
        $url = $this->baseUrl . (str_starts_with($path, '/') ? $path : '/' . $path);
        $ch = curl_init($url);
        if ($ch === false) {
            throw new RuntimeException('Unable to init curl.');
        }

        $headers = ['User-Agent: Pokemon8PvpQa/1.0'];
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_COOKIEJAR => $this->cookieFile,
            CURLOPT_COOKIEFILE => $this->cookieFile,
            CURLOPT_HTTPHEADER => $headers,
        ]);

        if ($method !== 'GET') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($headers, ['Content-Type: application/x-www-form-urlencoded;charset=UTF-8']));
        }

        $raw = curl_exec($ch);
        if ($raw === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new RuntimeException('HTTP error: ' . $error);
        }

        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $effectiveUrl = (string) curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);

        return [
            'status' => $status,
            'body' => substr((string) $raw, $headerSize),
            'url' => $effectiveUrl,
        ];
    }
}
