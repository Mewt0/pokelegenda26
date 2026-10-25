<?php
declare(strict_types=1);

use Pokemon8\Database\Connection;
use Pokemon8\Game\StalePveBattleReaper;
use Pokemon8\Support\Env;

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Run from CLI.\n");
    exit(2);
}

define('APP_ROOT', dirname(__DIR__));
require APP_ROOT . '/src/Support/Autoload.php';
Env::load(APP_ROOT . '/.env');
$config = require APP_ROOT . '/config/database.php';
if (strtolower((string) $config['database']) !== 'pokemon_test' && getenv('REAPER_SMOKE_ALLOW_LIVE') !== '1') {
    fwrite(STDERR, "Refusing to run smoke unless DB_DATABASE=pokemon_test or REAPER_SMOKE_ALLOW_LIVE=1 is explicitly set.\n");
    exit(2);
}
$db = Connection::make($config);
$db->exec('SET SESSION TRANSACTION ISOLATION LEVEL SERIALIZABLE');
$results = [];
$userId = 0;
$battleId = 0;
$originalUserState = null;
$transactionStarted = false;

try {
    $userId = (int) ($db->query('SELECT id FROM users ORDER BY id LIMIT 1')->fetchColumn() ?: 0);
    if ($userId <= 0) {
        throw new RuntimeException('Test database must contain at least one user.');
    }
    $stateStmt = $db->prepare('SELECT pve, pvp, trade, battleid FROM users WHERE id = :id');
    $stateStmt->execute(['id' => $userId]);
    $userState = $stateStmt->fetch(PDO::FETCH_ASSOC);
    if (!$userState) {
        throw new RuntimeException('Unable to load smoke user state.');
    }
    $originalUserState = $userState;
    if ((int) $userState['pve'] !== 0 || (int) $userState['pvp'] !== 0 || (int) $userState['trade'] !== 0) {
        throw new RuntimeException('Smoke user must be idle before the test starts.');
    }
    $reaper = new StalePveBattleReaper($db);

    $db->beginTransaction();
    $transactionStarted = true;

    $battleId = (int) $db->query('SELECT COALESCE(MAX(id), 0) + 100000 FROM battles')->fetchColumn();
    $db->prepare(
        'INSERT INTO battles (id, user_1, user_2, poke_1, poke_2, attac_1, attac_2, item_1, item_2, batl_tip, time, pobeda, raund, times, dates)
         VALUES (:id, :user, 0, "smoke", "smoke", 0, 0, 0, 0, "pve", :now, 0, 1, :stale, "")'
    )->execute(['id' => $battleId, 'user' => $userId, 'now' => time(), 'stale' => time() - 9000]);
    $fixture = $db->prepare('INSERT INTO battles (id, user_1, user_2, poke_1, poke_2, attac_1, attac_2, item_1, item_2, batl_tip, time, pobeda, raund, times, dates) VALUES (:id, :user, 0, "smoke", "smoke", 0, 0, 0, 0, "pve", :created, 0, 1, :deadline, "")');
    $fixture->execute(['id' => $battleId + 1, 'user' => $userId, 'created' => time(), 'deadline' => time() + 3600]);
    $fixture->execute(['id' => $battleId + 2, 'user' => $userId, 'created' => time(), 'deadline' => time()]);
    $fixture->execute(['id' => $battleId + 3, 'user' => $userId, 'created' => time(), 'deadline' => 0]);
    $fixture->execute(['id' => $battleId + 4, 'user' => $userId, 'created' => time() - 9000, 'deadline' => 0]);
    $fixture->execute(['id' => $battleId + 5, 'user' => $userId, 'created' => 0, 'deadline' => 0]);
    $db->prepare('INSERT INTO boss_battle_sessions (boss_id, user_id, battle_id, boss_team_json, rewards_json) VALUES (0, :user, :battle, "[]", "[]")')
        ->execute(['user' => $userId, 'battle' => $battleId + 2]);
    $repo = new Pokemon8\Repository\BattleRepository($db);
    $repo->addBattleVolatile($battleId, 'smoke', 'helping_hand', 999999);
    $db->prepare('UPDATE users SET pve = 1, battleid = :battle WHERE id = :user')->execute(['battle' => $battleId, 'user' => $userId]);

    $dry = $reaper->reap(true);
    check($results, 'dry_run.detects_synthetic_battle', in_array($battleId, $dry['battleIds'], true) && $dry['changed'] === 0);
    check($results, 'busy.before_reap', isBusy($db, $userId));
    check($results, 'dry_run.excludes_live_pve', !in_array($battleId + 1, $dry['battleIds'], true));
    check($results, 'dry_run.excludes_boss', !in_array($battleId + 2, $dry['battleIds'], true));
    check($results, 'dry_run.fallback_grace', !in_array($battleId + 3, $dry['battleIds'], true));
    check($results, 'dry_run.fallback_expired', in_array($battleId + 4, $dry['battleIds'], true));
    check($results, 'dry_run.unknown_age_preserved', !in_array($battleId + 5, $dry['battleIds'], true));

    $applied = $reaper->reap(false, 0, false);
    check($results, 'reap.uses_real_reaper', $applied['dryRun'] === false && in_array($battleId, $applied['battleIds'], true));
    check($results, 'reap.closes_battle', $applied['changed'] >= 1 && battleWinner($db, $battleId) === -1);
    check($results, 'reap.preserves_live_pve', battleWinner($db, $battleId + 1) === 0);
    check($results, 'reap.preserves_boss', battleWinner($db, $battleId + 2) === 0);
    check($results, 'reap.fallback_grace', battleWinner($db, $battleId + 3) === 0);
    check($results, 'reap.fallback_expired', battleWinner($db, $battleId + 4) === -1);
    check($results, 'reap.unknown_age_preserved', battleWinner($db, $battleId + 5) === 0);
    check($results, 'reap.clears_helping_hand', !$repo->hasBattleVolatile($battleId, 'smoke', 'helping_hand'));
    check($results, 'reap.clears_matching_pve_lock', !isBusy($db, $userId));
    check($results, 'reap.preserves_pvp_flag', (int) scalar($db, 'SELECT pvp FROM users WHERE id = :id', ['id' => $userId]) === 0);

    $secondDry = $reaper->reap(true);
    check($results, 'repeat_is_idempotent', !in_array($battleId, $secondDry['battleIds'], true) && $secondDry['changed'] === 0);
} catch (Throwable $e) {
    $results[] = ['name' => 'exception', 'ok' => false, 'details' => $e->getMessage()];
} finally {
    if ($transactionStarted && $db->inTransaction()) {
        try {
            $db->rollBack();
            check($results, 'cleanup.rolls_back_transaction', true);
        } catch (Throwable $cleanupError) {
            check($results, 'cleanup.rolls_back_transaction', false);
            $results[] = ['name' => 'cleanup.error', 'ok' => false, 'details' => $cleanupError->getMessage()];
        }
    }
    if ($userId > 0 && $originalUserState !== null) {
        try {
            $restored = fetchUserState($db, $userId);
            check($results, 'cleanup.restores_user_state', $restored == $originalUserState);
        } catch (Throwable $cleanupError) {
            check($results, 'cleanup.restores_user_state', false);
            $results[] = ['name' => 'cleanup.error', 'ok' => false, 'details' => $cleanupError->getMessage()];
        }
    }
    if ($battleId > 0) {
        try {
            check($results, 'cleanup.removes_synthetic_battle', (int) scalar($db, 'SELECT COUNT(*) FROM battles WHERE id = :id', ['id' => $battleId]) === 0);
        } catch (Throwable $cleanupError) {
            check($results, 'cleanup.removes_synthetic_battle', false);
            $results[] = ['name' => 'cleanup.error', 'ok' => false, 'details' => $cleanupError->getMessage()];
        }
    }
}

$passed = count(array_filter($results, static fn (array $row): bool => $row['ok']));
foreach ($results as $row) {
    echo sprintf('[%s] %s%s' . PHP_EOL, $row['ok'] ? 'OK' : 'FAIL', $row['name'], isset($row['details']) ? ' - ' . $row['details'] : '');
}
echo sprintf("PvE stale battle reaper smoke: %d/%d passed.%s", $passed, count($results), PHP_EOL);
exit($passed === count($results) ? 0 : 1);

function check(array &$results, string $name, bool $ok): void
{
    $results[] = ['name' => $name, 'ok' => $ok];
}

function isBusy(PDO $db, int $userId): bool
{
    $stmt = $db->prepare('SELECT (pve > 0 OR pvp > 0 OR trade > 0) FROM users WHERE id = :id');
    $stmt->execute(['id' => $userId]);
    return (bool) $stmt->fetchColumn();
}

function battleWinner(PDO $db, int $battleId): int
{
    return (int) scalar($db, 'SELECT pobeda FROM battles WHERE id = :id', ['id' => $battleId]);
}

function fetchUserState(PDO $db, int $userId): array
{
    $stmt = $db->prepare('SELECT pve, pvp, trade, battleid FROM users WHERE id = :id');
    $stmt->execute(['id' => $userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

function scalar(PDO $db, string $sql, array $params = []): mixed
{
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn();
}
