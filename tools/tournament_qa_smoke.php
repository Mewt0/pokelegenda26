<?php
declare(strict_types=1);

/**
 * Player tournament QA smoke: schedule, curator, fee, arena, participants and rewards.
 *
 * Usage:
 *   php tools/tournament_qa_smoke.php --password=...
 *   php tools/tournament_qa_smoke.php --login=Tacos --password=... --second-login=NIGA --second-password=...
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

$options = getopt('', ['base::', 'login::', 'password:', 'second-login::', 'second-password::']);
$baseUrl = rtrim((string) ($options['base'] ?? getenv('SMOKE_BASE_URL') ?: 'http://pokemonchic.com'), '/');
$login = (string) ($options['login'] ?? 'Tacos');
$password = (string) ($options['password'] ?? getenv('SMOKE_PASSWORD') ?: '');
$secondLogin = (string) ($options['second-login'] ?? 'NIGA');
$secondPassword = (string) ($options['second-password'] ?? $password);

if ($password === '' || $secondPassword === '') {
    fwrite(STDERR, "Missing --password.\n");
    exit(2);
}

$db = Pokemon8\Database\Connection::make(require APP_ROOT . '/config/database.php');
$results = [];
$prefix = 'SMOKE tournament ' . date('YmdHis') . ' ';
$feeItemId = 990018;
$rewardItemId = 990019;
$createdTournamentIds = [];
$createdMedalIds = [];
$createdPokemonIds = [];
$originalLocations = [];

try {
    ensureSmokeItem($db, $feeItemId, 'Smoke Tournament Ticket');
    ensureSmokeItem($db, $rewardItemId, 'Smoke Tournament Prize');
    $db->prepare('DELETE FROM items_users WHERE item_id IN (:fee, :reward)')->execute(['fee' => $feeItemId, 'reward' => $rewardItemId]);

    $tacosId = userId($db, $login);
    $nigaId = userId($db, $secondLogin);
    assertTrue($results, 'users.ready', $tacosId > 0 && $nigaId > 0, $login . '=' . $tacosId . ', ' . $secondLogin . '=' . $nigaId);
    $originalLocations[$tacosId] = userLocation($db, $tacosId);
    $originalLocations[$nigaId] = userLocation($db, $nigaId);
    ensurePokemon($db, $tacosId, $createdPokemonIds, 'SMOKE Tournament Tacos');
    ensurePokemon($db, $nigaId, $createdPokemonIds, 'SMOKE Tournament NIGA');
    grantItem($db, $tacosId, $feeItemId, 20);
    grantItem($db, $nigaId, $feeItemId, 20);

    $tacos = new SmokeClient($baseUrl);
    loginClient($tacos, $login, $password);
    $gamePage = $tacos->request('GET', '/game');
    $csrf = extractCsrf($gamePage['body']);
    assertTrue($results, 'csrf.present', $csrf !== '', 'csrf=' . ($csrf !== '' ? 'yes' : 'no'));
    $page = $tacos->request('GET', '/game/tournaments');
    assertTrue($results, 'frontend.page', $page['status'] === 200 && str_contains($page['body'], 'Турниры'), 'status=' . $page['status']);

    $niga = new SmokeClient($baseUrl);
    loginClient($niga, $secondLogin, $secondPassword);
    $nigaPage = $niga->request('GET', '/game');
    $nigaCsrf = extractCsrf($nigaPage['body']);
    assertTrue($results, 'csrf.second.present', $nigaCsrf !== '', 'csrf=' . ($nigaCsrf !== '' ? 'yes' : 'no'));

    $now = time();
    $registrationId = createTournament($db, $createdTournamentIds, [
        'title' => $prefix . 'registration',
        'status' => 'registration',
        'starts_at' => $now + 3600,
        'ends_at' => $now + 7200,
        'registration_deadline_at' => $now + 1800,
        'entry_fee_item_id' => $feeItemId,
        'entry_fee_amount' => 5,
        'location_id' => 40,
        'arena_exit_location_id' => 1,
        'curator_user_id' => $tacosId,
        'max_participants' => 4,
        'rules' => 'Smoke rules: registration, fee and cancellation.',
        'reward_note' => 'Smoke reward note',
        'reward_json' => '{"items":{"' . $rewardItemId . '":2}}',
    ]);

    [$dashboard] = jsonGet($tacos, $results, 'api.tournaments.list', '/api/tournaments');
    $registration = findTournament($dashboard, $registrationId);
    assertTrue($results, 'schedule.deadline.visible', $registration !== null && ($registration['registrationDeadlineAt'] ?? 0) === $now + 1800, 'deadline=' . (string) ($registration['registrationDeadlineAt'] ?? 'missing'));
    assertTrue($results, 'curator.visible', $registration !== null && ($registration['curator']['id'] ?? 0) === $tacosId && ($registration['curator']['login'] ?? '') === $login, 'curator=' . json_encode($registration['curator'] ?? []));
    assertTrue($results, 'fee.visible', $registration !== null && ($registration['entryFee']['itemId'] ?? 0) === $feeItemId && ($registration['entryFee']['amount'] ?? 0) === 5, 'fee=' . json_encode($registration['entryFee'] ?? []));
    assertTrue($results, 'arena.visible', $registration !== null && ($registration['arena']['locationId'] ?? 0) === 40, 'arena=' . json_encode($registration['arena'] ?? []));

    $csrfFail = $tacos->request('POST', '/api/tournaments/register', ['tournament_id' => $registrationId]);
    assertTrue($results, 'csrf.negative', $csrfFail['status'] === 419, 'status=' . $csrfFail['status']);

    $beforeFee = itemCount($db, $tacosId, $feeItemId);
    [$register] = jsonPost($tacos, $results, 'register.success', '/api/tournaments/register', ['_csrf' => $csrf, 'tournament_id' => $registrationId]);
    $afterFee = itemCount($db, $tacosId, $feeItemId);
    assertTrue($results, 'fee.deducted', ($register['ok'] ?? false) === true && $beforeFee - $afterFee === 5, 'before=' . $beforeFee . ', after=' . $afterFee);

    [$duplicate] = jsonPost($tacos, $results, 'register.duplicate.request', '/api/tournaments/register', ['_csrf' => $csrf, 'tournament_id' => $registrationId], false);
    assertTrue($results, 'register.duplicate.blocked', ($duplicate['ok'] ?? true) === false && itemCount($db, $tacosId, $feeItemId) === $afterFee, 'message=' . ($duplicate['message'] ?? ''));

    [$cancel] = jsonPost($tacos, $results, 'cancel.success', '/api/tournaments/cancel', ['_csrf' => $csrf, 'tournament_id' => $registrationId]);
    assertTrue($results, 'fee.refunded', ($cancel['ok'] ?? false) === true && itemCount($db, $tacosId, $feeItemId) === $beforeFee, 'count=' . itemCount($db, $tacosId, $feeItemId));

    $pastId = createTournament($db, $createdTournamentIds, [
        'title' => $prefix . 'past deadline',
        'status' => 'registration',
        'starts_at' => $now + 3600,
        'ends_at' => $now + 7200,
        'registration_deadline_at' => $now - 60,
        'entry_fee_item_id' => $feeItemId,
        'entry_fee_amount' => 1,
        'location_id' => 40,
        'curator_user_id' => $tacosId,
        'max_participants' => 4,
    ]);
    [$past] = jsonPost($tacos, $results, 'register.past_deadline.request', '/api/tournaments/register', ['_csrf' => $csrf, 'tournament_id' => $pastId], false);
    assertTrue($results, 'register.past_deadline.blocked', ($past['ok'] ?? true) === false, 'message=' . ($past['message'] ?? ''));

    $poorId = createTournament($db, $createdTournamentIds, [
        'title' => $prefix . 'no money',
        'status' => 'registration',
        'starts_at' => $now + 3600,
        'ends_at' => $now + 7200,
        'registration_deadline_at' => $now + 1800,
        'entry_fee_item_id' => $feeItemId,
        'entry_fee_amount' => 999999,
        'location_id' => 40,
        'curator_user_id' => $tacosId,
        'max_participants' => 4,
    ]);
    [$poor] = jsonPost($tacos, $results, 'register.no_money.request', '/api/tournaments/register', ['_csrf' => $csrf, 'tournament_id' => $poorId], false);
    assertTrue($results, 'register.no_money.blocked', ($poor['ok'] ?? true) === false && participantStatus($db, $poorId, $tacosId) === '', 'message=' . ($poor['message'] ?? ''));

    $maxId = createTournament($db, $createdTournamentIds, [
        'title' => $prefix . 'max participants',
        'status' => 'registration',
        'starts_at' => $now + 3600,
        'ends_at' => $now + 7200,
        'registration_deadline_at' => $now + 1800,
        'entry_fee_item_id' => $feeItemId,
        'entry_fee_amount' => 1,
        'location_id' => 40,
        'curator_user_id' => $tacosId,
        'max_participants' => 1,
    ]);
    jsonPost($tacos, $results, 'register.max.first', '/api/tournaments/register', ['_csrf' => $csrf, 'tournament_id' => $maxId]);
    [$maxFail] = jsonPost($niga, $results, 'register.max.second.request', '/api/tournaments/register', ['_csrf' => $nigaCsrf, 'tournament_id' => $maxId], false);
    assertTrue($results, 'register.max.blocked', ($maxFail['ok'] ?? true) === false, 'message=' . ($maxFail['message'] ?? ''));

    $activeId = createTournament($db, $createdTournamentIds, [
        'title' => $prefix . 'arena',
        'status' => 'active',
        'starts_at' => $now - 60,
        'ends_at' => $now + 3600,
        'registration_deadline_at' => $now - 120,
        'entry_fee_item_id' => $feeItemId,
        'entry_fee_amount' => 0,
        'location_id' => 40,
        'arena_exit_location_id' => 1,
        'curator_user_id' => $tacosId,
        'max_participants' => 4,
    ]);
    addParticipant($db, $activeId, $tacosId, 'registered', 0, 0, $originalLocations[$tacosId] ?: 1);
    [$enter] = jsonPost($tacos, $results, 'arena.enter', '/api/tournaments/arena-enter', ['_csrf' => $csrf, 'tournament_id' => $activeId]);
    assertTrue($results, 'arena.location', ($enter['ok'] ?? false) === true && userLocation($db, $tacosId) === 40 && participantStatus($db, $activeId, $tacosId) === 'checked_in', 'location=' . userLocation($db, $tacosId));
    [$leave] = jsonPost($tacos, $results, 'arena.leave', '/api/tournaments/arena-leave', ['_csrf' => $csrf, 'tournament_id' => $activeId]);
    assertTrue($results, 'arena.return', ($leave['ok'] ?? false) === true && userLocation($db, $tacosId) === ($originalLocations[$tacosId] ?: 1), 'location=' . userLocation($db, $tacosId));

    $rewardId = createTournament($db, $createdTournamentIds, [
        'title' => $prefix . 'reward',
        'status' => 'finished',
        'starts_at' => $now - 7200,
        'ends_at' => $now - 3600,
        'registration_deadline_at' => $now - 8000,
        'entry_fee_item_id' => $feeItemId,
        'entry_fee_amount' => 0,
        'location_id' => 40,
        'curator_user_id' => $tacosId,
        'max_participants' => 4,
        'reward_note' => '2 smoke prizes + medal',
        'reward_json' => '{"items":{"' . $rewardItemId . '":2}}',
    ]);
    $medalId = createMedal($db, $createdMedalIds, $rewardId, $prefix . 'winner medal');
    addParticipant($db, $rewardId, $tacosId, 'winner', 10, 1, $originalLocations[$tacosId] ?: 1);
    $beforeReward = itemCount($db, $tacosId, $rewardItemId);
    [$claim] = jsonPost($tacos, $results, 'reward.claim', '/api/tournaments/claim-reward', ['_csrf' => $csrf, 'tournament_id' => $rewardId]);
    assertTrue($results, 'reward.items.granted', ($claim['ok'] ?? false) === true && itemCount($db, $tacosId, $rewardItemId) === $beforeReward + 2, 'before=' . $beforeReward . ', after=' . itemCount($db, $tacosId, $rewardItemId));
    assertTrue($results, 'reward.medal.granted_once', userMedalCount($db, $tacosId, $medalId, $rewardId) === 1, 'medals=' . userMedalCount($db, $tacosId, $medalId, $rewardId));
    [$claimAgain] = jsonPost($tacos, $results, 'reward.duplicate.request', '/api/tournaments/claim-reward', ['_csrf' => $csrf, 'tournament_id' => $rewardId], false);
    assertTrue($results, 'reward.duplicate.blocked', ($claimAgain['ok'] ?? true) === false && itemCount($db, $tacosId, $rewardItemId) === $beforeReward + 2, 'message=' . ($claimAgain['message'] ?? ''));

    assertTrue($results, 'logs.created', tournamentLogCount($db, $createdTournamentIds) >= 5, 'logs=' . tournamentLogCount($db, $createdTournamentIds));
} catch (Throwable $e) {
    assertTrue($results, 'exception', false, $e->getMessage());
} finally {
    cleanup($db, $createdTournamentIds, $createdMedalIds, $createdPokemonIds, $feeItemId, $rewardItemId, $originalLocations);
}

printResults($results);
exit(count(array_filter($results, static fn (array $row): bool => !$row['ok'])) > 0 ? 1 : 0);

function createTournament(PDO $db, array &$ids, array $data): int
{
    $now = time();
    $defaults = [
        'legacy_id' => 0,
        'title' => 'SMOKE tournament',
        'status' => 'registration',
        'starts_at' => $now + 3600,
        'ends_at' => $now + 7200,
        'registration_deadline_at' => $now + 1800,
        'entry_fee_item_id' => 1,
        'entry_fee_amount' => 0,
        'location_id' => 40,
        'arena_exit_location_id' => 1,
        'curator_user_id' => 0,
        'min_level' => 1,
        'max_level' => 100,
        'max_participants' => 0,
        'rules' => '',
        'reward_note' => '',
        'reward_json' => '{}',
        'created_by' => 0,
        'updated_by' => 0,
        'created_at' => $now,
        'updated_at' => $now,
    ];
    $row = array_merge($defaults, $data);
    $db->prepare(
        'INSERT INTO admin_tournaments
            (legacy_id, title, status, starts_at, ends_at, registration_deadline_at, entry_fee_item_id, entry_fee_amount,
             location_id, arena_exit_location_id, curator_user_id, min_level, max_level, max_participants,
             rules, reward_note, reward_json, created_by, updated_by, created_at, updated_at)
         VALUES
            (:legacy_id, :title, :status, :starts_at, :ends_at, :registration_deadline_at, :entry_fee_item_id, :entry_fee_amount,
             :location_id, :arena_exit_location_id, :curator_user_id, :min_level, :max_level, :max_participants,
             :rules, :reward_note, :reward_json, :created_by, :updated_by, :created_at, :updated_at)'
    )->execute($row);
    $id = (int) $db->lastInsertId();
    $ids[] = $id;
    return $id;
}

function addParticipant(PDO $db, int $tournamentId, int $userId, string $status, int $score, int $place, int $returnLocation): void
{
    $now = time();
    $db->prepare(
        'INSERT INTO admin_tournament_participants
            (tournament_id, user_id, pokemon_id, status, score, place_num, joined_at, fee_paid_at, fee_refunded_at,
             checked_in_at, left_at, return_location_id, reward_claimed_at, updated_at)
         VALUES
            (:tournament, :user, 0, :status, :score, :place, :joined_at, 0, 0, 0, 0, :return_location, 0, :updated_at)
         ON DUPLICATE KEY UPDATE status = VALUES(status), score = VALUES(score), place_num = VALUES(place_num),
             return_location_id = VALUES(return_location_id), reward_claimed_at = 0, updated_at = VALUES(updated_at)'
    )->execute([
        'tournament' => $tournamentId,
        'user' => $userId,
        'status' => $status,
        'score' => $score,
        'place' => $place,
        'joined_at' => $now,
        'return_location' => $returnLocation,
        'updated_at' => $now,
    ]);
}

function createMedal(PDO $db, array &$ids, int $tournamentId, string $title): int
{
    $now = time();
    $db->prepare(
        'INSERT INTO admin_medals
            (title, description, icon_file, medal_type, tournament_id, sort_order, enabled, created_by, updated_by, created_at, updated_at)
         VALUES
            (:title, "Smoke medal", "", "tournament", :tournament, 0, 1, 0, 0, :created_at, :updated_at)'
    )->execute([
        'title' => $title,
        'tournament' => $tournamentId,
        'created_at' => $now,
        'updated_at' => $now,
    ]);
    $id = (int) $db->lastInsertId();
    $ids[] = $id;
    return $id;
}

function ensureSmokeItem(PDO $db, int $itemId, string $name): void
{
    $db->prepare('DELETE FROM items WHERE id = :id AND name LIKE "Smoke Tournament%"')->execute(['id' => $itemId]);
    $db->prepare(
        'INSERT INTO items (id, cools, name, tittle, category, uses, dress, delet, torg, elementary, timesnapoke, times, battleuse, dopolnen)
         VALUES (:id, 0, :name, :title, 0, 0, 0, 1, 1, 0, 1, "0", 0, "smoke")'
    )->execute(['id' => $itemId, 'name' => $name, 'title' => $name]);
}

function ensurePokemon(PDO $db, int $userId, array &$createdIds, string $name): void
{
    $stmt = $db->prepare('SELECT COUNT(*) FROM pok_user WHERE users = :user AND lvl BETWEEN 1 AND 100 AND hp_max > 0');
    $stmt->execute(['user' => $userId]);
    if ((int) $stmt->fetchColumn() > 0) {
        return;
    }

    $db->prepare(
        'INSERT INTO pok_user
            (users, basenum, names, active, lvl, sex, har, hp_my, hp_max, exp, exp_b,
             atk, def, satk, sdef, speed, hp_ev, atk_ev, def_ev, satk_ev, sdef_ev, speed_ev,
             hp_iv, atk_iv, def_iv, satk_iv, sdef_iv, speed_iv, tips, startone, startepoke, happy)
         VALUES
            (:user, 16, :name, 1, 10, 1, 1, 40, 40, 0, 100,
             20, 20, 20, 20, 20, 0, 0, 0, 0, 0, 0,
             10, 10, 10, 10, 10, 10, "normal", 0, 0, 100)'
    )->execute(['user' => $userId, 'name' => $name]);
    $createdIds[] = (int) $db->lastInsertId();
}

function grantItem(PDO $db, int $userId, int $itemId, int $count): void
{
    $db->prepare('INSERT INTO items_users (item_id, user_id, count, dattimer, timers) VALUES (:item, :user, :count, "smoke_tournament", "not")')
        ->execute(['item' => $itemId, 'user' => $userId, 'count' => $count]);
}

function itemCount(PDO $db, int $userId, int $itemId): int
{
    $stmt = $db->prepare('SELECT COALESCE(SUM(count), 0) FROM items_users WHERE user_id = :user AND item_id = :item');
    $stmt->execute(['user' => $userId, 'item' => $itemId]);
    return (int) $stmt->fetchColumn();
}

function userId(PDO $db, string $login): int
{
    $stmt = $db->prepare('SELECT id FROM users WHERE login = :login LIMIT 1');
    $stmt->execute(['login' => $login]);
    return (int) ($stmt->fetchColumn() ?: 0);
}

function userLocation(PDO $db, int $userId): int
{
    $stmt = $db->prepare('SELECT buildmy FROM users WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $userId]);
    return (int) ($stmt->fetchColumn() ?: 0);
}

function participantStatus(PDO $db, int $tournamentId, int $userId): string
{
    $stmt = $db->prepare('SELECT status FROM admin_tournament_participants WHERE tournament_id = :tournament AND user_id = :user LIMIT 1');
    $stmt->execute(['tournament' => $tournamentId, 'user' => $userId]);
    return (string) ($stmt->fetchColumn() ?: '');
}

function userMedalCount(PDO $db, int $userId, int $medalId, int $tournamentId): int
{
    $stmt = $db->prepare('SELECT COUNT(*) FROM admin_user_medals WHERE user_id = :user AND medal_id = :medal AND tournament_id = :tournament');
    $stmt->execute(['user' => $userId, 'medal' => $medalId, 'tournament' => $tournamentId]);
    return (int) $stmt->fetchColumn();
}

function tournamentLogCount(PDO $db, array $tournamentIds): int
{
    $tournamentIds = array_values(array_filter(array_map('intval', $tournamentIds)));
    if ($tournamentIds === []) {
        return 0;
    }
    $in = implode(',', array_fill(0, count($tournamentIds), '?'));
    $stmt = $db->prepare('SELECT COUNT(*) FROM admin_tournament_logs WHERE tournament_id IN (' . $in . ')');
    $stmt->execute($tournamentIds);
    return (int) $stmt->fetchColumn();
}

function findTournament(array $dashboard, int $tournamentId): ?array
{
    foreach (($dashboard['tournaments'] ?? []) as $row) {
        if ((int) ($row['id'] ?? 0) === $tournamentId) {
            return $row;
        }
    }
    return null;
}

function cleanup(PDO $db, array $tournamentIds, array $medalIds, array $pokemonIds, int $feeItemId, int $rewardItemId, array $locations): void
{
    foreach ($locations as $userId => $location) {
        if ((int) $userId > 0 && (int) $location > 0) {
            $db->prepare('UPDATE users SET buildmy = :location WHERE id = :id LIMIT 1')->execute(['location' => (int) $location, 'id' => (int) $userId]);
        }
    }
    $tournamentIds = array_values(array_filter(array_map('intval', $tournamentIds)));
    $medalIds = array_values(array_filter(array_map('intval', $medalIds)));
    $pokemonIds = array_values(array_filter(array_map('intval', $pokemonIds)));
    if ($tournamentIds !== []) {
        $in = implode(',', array_fill(0, count($tournamentIds), '?'));
        if (tableExists($db, 'reward_transactions') && tableExists($db, 'reward_transaction_entries')) {
            $stmt = $db->prepare('SELECT id FROM reward_transactions WHERE source_type = "tournament" AND source_id IN (' . $in . ')');
            $stmt->execute(array_map('strval', $tournamentIds));
            $rewardTransactionIds = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN) ?: []);
            if ($rewardTransactionIds !== []) {
                $rtIn = implode(',', array_fill(0, count($rewardTransactionIds), '?'));
                $db->prepare('DELETE FROM reward_transaction_entries WHERE transaction_id IN (' . $rtIn . ')')->execute($rewardTransactionIds);
            }
        }
        $db->prepare('DELETE FROM admin_tournament_logs WHERE tournament_id IN (' . $in . ')')->execute($tournamentIds);
        $db->prepare('DELETE FROM admin_tournament_participants WHERE tournament_id IN (' . $in . ')')->execute($tournamentIds);
        $db->prepare('DELETE FROM admin_user_medals WHERE tournament_id IN (' . $in . ')')->execute($tournamentIds);
        $db->prepare('DELETE FROM admin_medals WHERE tournament_id IN (' . $in . ')')->execute($tournamentIds);
        $db->prepare('DELETE FROM admin_tournaments WHERE id IN (' . $in . ')')->execute($tournamentIds);
        if (tableHasColumn($db, 'game_notifications', 'source_type')) {
            $db->prepare('DELETE FROM game_notifications WHERE source_type = "tournament" AND source_id IN (' . $in . ')')->execute(array_map('strval', $tournamentIds));
        }
        if (tableExists($db, 'reward_transactions')) {
            $db->prepare('DELETE FROM reward_transactions WHERE source_type = "tournament" AND source_id IN (' . $in . ')')->execute(array_map('strval', $tournamentIds));
        }
    }
    if ($medalIds !== []) {
        $in = implode(',', array_fill(0, count($medalIds), '?'));
        $db->prepare('DELETE FROM admin_user_medals WHERE medal_id IN (' . $in . ')')->execute($medalIds);
        $db->prepare('DELETE FROM admin_medals WHERE id IN (' . $in . ')')->execute($medalIds);
    }
    if ($pokemonIds !== []) {
        $in = implode(',', array_fill(0, count($pokemonIds), '?'));
        $db->prepare('DELETE FROM pok_user WHERE id IN (' . $in . ')')->execute($pokemonIds);
    }
    $db->prepare('DELETE FROM items_users WHERE item_id IN (:fee, :reward)')
        ->execute(['fee' => $feeItemId, 'reward' => $rewardItemId]);
    $db->prepare('DELETE FROM items WHERE id IN (:fee, :reward) AND name LIKE "Smoke Tournament%"')
        ->execute(['fee' => $feeItemId, 'reward' => $rewardItemId]);
}

function tableExists(PDO $db, string $table): bool
{
    $stmt = $db->prepare('SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table');
    $stmt->execute(['table' => $table]);
    return (int) $stmt->fetchColumn() > 0;
}

function tableHasColumn(PDO $db, string $table, string $column): bool
{
    $stmt = $db->prepare('SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table AND COLUMN_NAME = :column');
    $stmt->execute(['table' => $table, 'column' => $column]);
    return (int) $stmt->fetchColumn() > 0;
}

function loginClient(SmokeClient $client, string $login, string $password): void
{
    $home = $client->request('GET', '/');
    $csrf = extractCsrf($home['body']);
    if ($csrf === '') {
        throw new RuntimeException('Login CSRF token not found.');
    }
    $client->request('POST', '/login', ['_csrf' => $csrf, 'LOGIN' => $login, 'PASSWORD' => $password]);
}

function jsonGet(SmokeClient $client, array &$results, string $name, string $path): array
{
    $response = $client->request('GET', $path);
    $json = json_decode($response['body'], true);
    $ok = $response['status'] >= 200 && $response['status'] < 300 && is_array($json) && ($json['ok'] ?? false) === true;
    assertTrue($results, $name, $ok, 'status=' . $response['status'] . (!$ok ? ', body=' . mb_substr($response['body'], 0, 220) : ''));
    return [is_array($json) ? $json : [], $response];
}

function jsonPost(SmokeClient $client, array &$results, string $name, string $path, array $data, bool $expectOk = true): array
{
    $response = $client->request('POST', $path, $data);
    $json = json_decode($response['body'], true);
    $ok = $response['status'] >= 200 && $response['status'] < 300 && is_array($json) && (($json['ok'] ?? false) === $expectOk || !$expectOk);
    assertTrue($results, $name, $ok, 'status=' . $response['status'] . (!$ok ? ', body=' . mb_substr($response['body'], 0, 220) : ''));
    return [is_array($json) ? $json : [], $response];
}

function extractCsrf(string $html): string
{
    if (preg_match('/name="_csrf"\s+value="([^"]+)"/', $html, $m)) {
        return html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
    }
    if (preg_match('/<meta\s+name="csrf-token"\s+content="([^"]+)"/', $html, $m)) {
        return html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
    }
    if (preg_match('/data-csrf="([^"]+)"/', $html, $m)) {
        return html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
    }
    return '';
}

function assertTrue(array &$results, string $name, bool $ok, string $details = ''): void
{
    $results[] = ['name' => $name, 'ok' => $ok, 'details' => $details];
}

function printResults(array $results): void
{
    $passed = 0;
    foreach ($results as $row) {
        if ($row['ok']) {
            $passed++;
        }
        echo sprintf("[%s] %s%s\n", $row['ok'] ? 'OK ' : 'FAIL', $row['name'], $row['details'] !== '' ? ' - ' . $row['details'] : '');
    }
    echo sprintf("Tournament QA smoke: %d/%d passed.\n", $passed, count($results));
}

final class SmokeClient
{
    private string $cookieFile;

    public function __construct(private string $baseUrl)
    {
        $this->cookieFile = tempnam(sys_get_temp_dir(), 'tournament_cookie_') ?: (sys_get_temp_dir() . '/tournament_cookie.txt');
    }

    /** @param array<string,string|int|float|bool> $data */
    public function request(string $method, string $path, array $data = []): array
    {
        $ch = curl_init($this->baseUrl . $path);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_COOKIEJAR => $this->cookieFile,
            CURLOPT_COOKIEFILE => $this->cookieFile,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HEADER => false,
        ]);
        if (strtoupper($method) === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }
        $body = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($body === false) {
            $body = curl_error($ch);
        }
        curl_close($ch);
        return ['status' => $status, 'body' => (string) $body];
    }
}
