<?php
declare(strict_types=1);

use Pokemon8\Database\Connection;
use Pokemon8\Game\LocationContentRepository;
use Pokemon8\Game\LocationGraph;
use Pokemon8\Game\LocationStateService;
use Pokemon8\Game\MapMoveService;
use Pokemon8\Game\NpcDialogService;
use Pokemon8\Game\WildEncounterService;
use Pokemon8\Repository\InventoryRepository;
use Pokemon8\Repository\LocationRepository;
use Pokemon8\Repository\PokemonEvolutionRepository;
use Pokemon8\Repository\PokemonRepository;
use Pokemon8\Repository\QuestRepository;
use Pokemon8\Repository\RewardRepository;
use Pokemon8\Repository\SafeStorageRepository;
use Pokemon8\Repository\TransportRepository;
use Pokemon8\Support\Env;

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Run from CLI.\n");
    exit(2);
}

define('APP_ROOT', dirname(__DIR__));
require APP_ROOT . '/src/Support/Autoload.php';

Env::load(APP_ROOT . '/.env');
$db = Connection::make(require APP_ROOT . '/config/database.php');

$evolutions = new PokemonEvolutionRepository($db);
$inventory = new InventoryRepository($db, $evolutions);
$safeStorage = new SafeStorageRepository($db);
$inventory->setSafeStorageRepository($safeStorage);
$rewards = new RewardRepository($db, $safeStorage);
$inventory->setRewardRepository($rewards);
$quests = new QuestRepository($db);
$locations = new LocationRepository($db);
$pokemon = new PokemonRepository($db);
$content = LocationContentRepository::fromFile(APP_ROOT . '/config/location_content.php');
$graph = LocationGraph::fromLegacyData(APP_ROOT . '/include/data.world.php');
$transport = new TransportRepository($db, $inventory);
$transport->setQuestRepository($quests);
$transport->setRewardRepository($rewards);
$state = new LocationStateService($locations, $graph, APP_ROOT, $content, null, $transport);
$moves = new MapMoveService($locations, $graph, $state, $quests, $rewards);
$npcs = new NpcDialogService($db, $locations, $quests, $inventory, $pokemon, $content, $rewards);
$wild = new WildEncounterService($db);

$results = [];
$userId = userId($db, 'Tacos');
assertTrue($results, 'user.tacos.exists', $userId > 0, 'id=' . $userId);

if ($userId <= 0) {
    printResults($results);
    exit(1);
}

runTransactionalWorldSmoke($db, $results, $userId, $graph, $state, $moves, $wild, $npcs, $transport, $inventory);
runFlightSmoke($db, $results, $userId, $state, $transport);

printResults($results);
exit(hasFailures($results) ? 1 : 0);

function runTransactionalWorldSmoke(
    PDO $db,
    array &$results,
    int $userId,
    LocationGraph $graph,
    LocationStateService $state,
    MapMoveService $moves,
    WildEncounterService $wild,
    NpcDialogService $npcs,
    TransportRepository $transport,
    InventoryRepository $inventory
): void {
    $db->beginTransaction();
    try {
        prepareUser($db, $userId, 1);
        ensureAlivePokemon($db, $userId);

        $routeIds = $graph->movesFrom(1);
        assertTrue($results, 'routes.graph.from_pallet', in_array(4, $routeIds, true), 'moves=' . implode(',', $routeIds));

        $home = $state->currentStateForUser($userId);
        assertTrue($results, 'map.state.home', ($home['ok'] ?? false) === true && (int) ($home['location']['id'] ?? 0) === 1);
        assertTrue($results, 'map.state.moves.visible', count($home['moves'] ?? []) >= 2, 'moves=' . count($home['moves'] ?? []));
        assertTrue($results, 'map.state.users.includes_self', containsUser($home['users'] ?? [], $userId));

        $blocked = $moves->move($userId, 999999);
        assertTrue($results, 'map.blocked.invalid_route', ($blocked['ok'] ?? true) === false && ($blocked['error'] ?? '') === 'forbidden', (string) ($blocked['message'] ?? ''));

        $toRoute = $moves->move($userId, 4);
        assertTrue($results, 'map.transition.1_to_4', ($toRoute['ok'] ?? false) === true && (int) ($toRoute['location']['id'] ?? 0) === 4);
        assertTrue($results, 'map.transition.chat_event', (string) ($toRoute['chatEvent']['type'] ?? '') === 'move');

        $db->prepare('UPDATE users SET pve = 1 WHERE id = :user LIMIT 1')->execute(['user' => $userId]);
        $busyMove = $moves->move($userId, 5);
        assertTrue($results, 'map.blocked.in_pve', ($busyMove['ok'] ?? true) === false && ($busyMove['error'] ?? '') === 'in_pve', (string) ($busyMove['message'] ?? ''));
        $db->prepare('UPDATE users SET pve = 0 WHERE id = :user LIMIT 1')->execute(['user' => $userId]);

        $encounter = $wild->forceStartForUserAtLocation($userId, 4);
        assertTrue($results, 'wild.force.location_4', ($encounter['ok'] ?? false) === true && !empty($encounter['wildEncounter']['started']), (string) ($encounter['wildEncounter']['message'] ?? ''));
        assertTrue($results, 'wild.force.battle_id', (int) ($encounter['wildEncounter']['battleId'] ?? 0) > 0, 'battle=' . (int) ($encounter['wildEncounter']['battleId'] ?? 0));

        prepareUser($db, $userId, 3);
        $oak = $npcs->open($userId, 3, ['quest_npc' => '1', 'do' => '1']);
        assertTrue($results, 'npc.oak.dialog', ($oak['ok'] ?? false) === true && trim(dialogTitle($oak)) !== '', dialogTitle($oak));

        prepareUser($db, $userId, 23);
        $ticketOffice = $npcs->open($userId, 23, ['npc' => '1']);
        assertTrue($results, 'npc.transport.ticket_office', ($ticketOffice['ok'] ?? false) === true && hasChoiceRoute($ticketOffice, '/game/transport'), dialogText($ticketOffice));
        $shipNpc = $npcs->open($userId, 23, ['npc' => '3']);
        assertTrue($results, 'npc.transport.ship_dialog', ($shipNpc['ok'] ?? false) === true && hasChoiceRoute($shipNpc, '/game/transport'), dialogText($shipNpc));

        $coinsBefore = $inventory->countItem($userId, TransportRepository::COIN_ITEM_ID);
        if ($coinsBefore < 5000) {
            $inventory->addItem($userId, TransportRepository::COIN_ITEM_ID, 5000 - $coinsBefore);
        }
        $shipRoutes = $transport->routesForUser($userId);
        $shipRouteId = (int) ($shipRoutes[0]['id'] ?? 0);
        assertTrue($results, 'transport.ship.routes', $shipRouteId > 0, 'routes=' . count($shipRoutes));
        $shipTravel = $shipRouteId > 0 ? $transport->travel($userId, $shipRouteId) : ['ok' => false, 'message' => 'missing route'];
        assertTrue($results, 'transport.ship.travel', ($shipTravel['ok'] ?? false) === true && (int) ($shipTravel['locationId'] ?? 0) > 0, (string) ($shipTravel['message'] ?? ''));
    } catch (Throwable $e) {
        fail($results, 'world.fatal', $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
    } finally {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
    }
}

function runFlightSmoke(PDO $db, array &$results, int $userId, LocationStateService $state, TransportRepository $transport): void
{
    $savedUser = fetchUserState($db, $userId);
    $pausedFlights = activeFlights($db, $userId);
    $ticketRowId = 0;
    $testFlightIds = [];

    try {
        foreach ($pausedFlights as $flight) {
            $db->prepare('UPDATE transport_flights SET status = "qa-paused" WHERE id = :id LIMIT 1')
                ->execute(['id' => (int) $flight['id']]);
        }
        prepareUser($db, $userId, 1);
        $db->prepare(
            'INSERT INTO items_users (item_id, user_id, count, dattimer, timers)
             VALUES (:item, :user, 1, "not", "not")'
        )->execute([
            'item' => TransportRepository::PLANE_TICKET_ITEM_ID,
            'user' => $userId,
        ]);
        $ticketRowId = (int) $db->lastInsertId();

        $flightRoutes = $transport->planeDestinationsForUser($userId);
        $routeId = (int) ($flightRoutes[0]['id'] ?? 0);
        assertTrue($results, 'transport.flight.routes', $routeId > 0, 'routes=' . count($flightRoutes));

        $start = $routeId > 0 ? $transport->startFlight($userId, $ticketRowId, $routeId) : ['ok' => false, 'message' => 'missing flight route'];
        assertTrue($results, 'transport.flight.start', ($start['ok'] ?? false) === true && (int) ($start['locationId'] ?? 0) === TransportRepository::PLANE_LOCATION_ID, (string) ($start['message'] ?? ''));

        $activeFlight = latestActiveFlight($db, $userId, $ticketRowId);
        if ($activeFlight !== null) {
            $testFlightIds[] = (int) $activeFlight['id'];
        }
        assertTrue($results, 'transport.flight.active_row', $activeFlight !== null, 'flight=' . (int) ($activeFlight['id'] ?? 0));

        $flightState = $state->currentStateForUser($userId);
        assertTrue($results, 'transport.flight.location_state', ($flightState['ok'] ?? false) === true && (int) ($flightState['location']['id'] ?? 0) === TransportRepository::PLANE_LOCATION_ID);
        assertTrue($results, 'transport.flight.no_map_moves', count($flightState['moves'] ?? []) === 0, 'moves=' . count($flightState['moves'] ?? []));
        assertTrue($results, 'transport.flight.npcs', hasNpcTitle($flightState['location']['npcs'] ?? [], 'Проводник') && hasNpcTitle($flightState['location']['npcs'] ?? [], 'Выход'));

        $status = $transport->flightStatus($userId);
        assertTrue($results, 'transport.flight.status', ($status['ok'] ?? false) === true && (int) ($status['flight']['remainingSeconds'] ?? 0) > 0);

        $earlyExit = $transport->exitFlight($userId);
        assertTrue($results, 'transport.flight.early_exit_blocked', ($earlyExit['ok'] ?? true) === false && str_contains((string) ($earlyExit['message'] ?? ''), 'летим'), (string) ($earlyExit['message'] ?? ''));
    } catch (Throwable $e) {
        fail($results, 'flight.fatal', $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
    } finally {
        foreach ($testFlightIds as $flightId) {
            $db->prepare('DELETE FROM transport_flights WHERE id = :id AND user_id = :user LIMIT 1')
                ->execute(['id' => $flightId, 'user' => $userId]);
        }
        if ($ticketRowId > 0) {
            $db->prepare('DELETE FROM items_users WHERE id = :id AND user_id = :user AND item_id = :item LIMIT 1')
                ->execute(['id' => $ticketRowId, 'user' => $userId, 'item' => TransportRepository::PLANE_TICKET_ITEM_ID]);
        }
        foreach ($pausedFlights as $flight) {
            $db->prepare('UPDATE transport_flights SET status = :status WHERE id = :id LIMIT 1')
                ->execute(['status' => (string) $flight['status'], 'id' => (int) $flight['id']]);
        }
        if ($savedUser !== null) {
            restoreUserState($db, $userId, $savedUser);
        }
    }
}

function prepareUser(PDO $db, int $userId, int $locationId): void
{
    $db->prepare(
        'UPDATE users
            SET buildmy = :location, pve = 0, pvp = 0, trade = 0, battleid = 0, pve_button = 0, atack_poke = 0
          WHERE id = :user
          LIMIT 1'
    )->execute(['location' => $locationId, 'user' => $userId]);
}

function ensureAlivePokemon(PDO $db, int $userId): void
{
    $stmt = $db->prepare('SELECT COUNT(*) FROM pok_user WHERE users = :user AND active = 1 AND hp_my > 0');
    $stmt->execute(['user' => $userId]);
    if ((int) ($stmt->fetchColumn() ?: 0) > 0) {
        return;
    }

    $db->prepare('UPDATE pok_user SET active = 1, hp_my = GREATEST(hp_max, 1) WHERE users = :user ORDER BY id DESC LIMIT 1')
        ->execute(['user' => $userId]);
}

function containsUser(array $users, int $userId): bool
{
    foreach ($users as $user) {
        if ((int) ($user['id'] ?? 0) === $userId) {
            return true;
        }
    }

    return false;
}

function hasChoiceRoute(array $dialog, string $route): bool
{
    foreach (($dialog['npc']['choices'] ?? []) as $choice) {
        if ((string) ($choice['route'] ?? '') === $route) {
            return true;
        }
    }

    return false;
}

function hasNpcTitle(array $npcs, string $title): bool
{
    foreach ($npcs as $npc) {
        if ((string) ($npc['title'] ?? '') === $title) {
            return true;
        }
    }

    return false;
}

function dialogTitle(array $payload): string
{
    return (string) ($payload['npc']['title'] ?? '');
}

function dialogText(array $payload): string
{
    return (string) ($payload['npc']['text'] ?? $payload['message'] ?? '');
}

function fetchUserState(PDO $db, int $userId): ?array
{
    $stmt = $db->prepare('SELECT buildmy, pve, pvp, trade, battleid, pve_button, atack_poke FROM users WHERE id = :user LIMIT 1');
    $stmt->execute(['user' => $userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return is_array($row) ? $row : null;
}

function restoreUserState(PDO $db, int $userId, array $state): void
{
    $db->prepare(
        'UPDATE users
            SET buildmy = :buildmy, pve = :pve, pvp = :pvp, trade = :trade, battleid = :battleid,
                pve_button = :pve_button, atack_poke = :atack_poke
          WHERE id = :user
          LIMIT 1'
    )->execute([
        'buildmy' => (int) ($state['buildmy'] ?? 1),
        'pve' => (int) ($state['pve'] ?? 0),
        'pvp' => (int) ($state['pvp'] ?? 0),
        'trade' => (int) ($state['trade'] ?? 0),
        'battleid' => (int) ($state['battleid'] ?? 0),
        'pve_button' => (int) ($state['pve_button'] ?? 0),
        'atack_poke' => (int) ($state['atack_poke'] ?? 0),
        'user' => $userId,
    ]);
}

function activeFlights(PDO $db, int $userId): array
{
    $stmt = $db->prepare('SELECT id, status FROM transport_flights WHERE user_id = :user AND status = "active"');
    $stmt->execute(['user' => $userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

function latestActiveFlight(PDO $db, int $userId, int $itemUserId): ?array
{
    $stmt = $db->prepare(
        'SELECT * FROM transport_flights
          WHERE user_id = :user AND item_user_id = :item_user AND status = "active"
          ORDER BY id DESC
          LIMIT 1'
    );
    $stmt->execute(['user' => $userId, 'item_user' => $itemUserId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return is_array($row) ? $row : null;
}

function userId(PDO $db, string $login): int
{
    $stmt = $db->prepare('SELECT id FROM users WHERE LOWER(login) = LOWER(:login) LIMIT 1');
    $stmt->execute(['login' => $login]);
    return (int) ($stmt->fetchColumn() ?: 0);
}

function assertTrue(array &$results, string $label, bool $ok, string $detail = ''): void
{
    $results[] = ['status' => $ok ? 'OK' : 'FAIL', 'label' => $label, 'detail' => $detail];
}

function fail(array &$results, string $label, string $detail): void
{
    $results[] = ['status' => 'FAIL', 'label' => $label, 'detail' => $detail];
}

function hasFailures(array $results): bool
{
    foreach ($results as $row) {
        if (($row['status'] ?? '') === 'FAIL') {
            return true;
        }
    }
    return false;
}

function printResults(array $results): void
{
    $failures = 0;
    foreach ($results as $row) {
        if ($row['status'] === 'FAIL') {
            $failures++;
        }
        printf("[%s] %s%s\n", $row['status'], $row['label'], $row['detail'] !== '' ? ' - ' . $row['detail'] : '');
    }
    printf("\nResult: %d checks, %d failures.\n", count($results), $failures);
}
