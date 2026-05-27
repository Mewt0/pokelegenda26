<?php
declare(strict_types=1);

use Pokemon8\Database\Connection;
use Pokemon8\Game\BattleEngineService;
use Pokemon8\Game\LocationContentRepository;
use Pokemon8\Game\LocationGraph;
use Pokemon8\Game\LocationStateService;
use Pokemon8\Game\MapMoveService;
use Pokemon8\Game\NpcDialogService;
use Pokemon8\Game\WildEncounterService;
use Pokemon8\Repository\BattleRepository;
use Pokemon8\Repository\BattleReplayRepository;
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
$battleReplay = new BattleReplayRepository($db);
$battleRepository = new BattleRepository($db, $evolutions);
$battleRepository->setBattleReplayRepository($battleReplay);
$battle = new BattleEngineService($battleRepository, $rewards, null, $safeStorage, $battleReplay, $quests);

$results = [];
$userId = userId($db, 'Tacos');
assertTrue($results, 'user.tacos.exists', $userId > 0, 'id=' . $userId);

if ($userId <= 0) {
    printResults($results);
    exit(1);
}

$db->beginTransaction();
try {
    prepareUser($db, $userId);

    $moveLab = $moves->move($userId, 3);
    assertTrue($results, 'fpe.move.lab', ($moveLab['ok'] ?? false) === true && (int) ($moveLab['location']['id'] ?? 0) === 3);

    $start = $npcs->action($userId, 3, ['quest_npc' => '1', 'do' => '1'], 'quest_start:1:2');
    assertTrue($results, 'fpe.oak.start', ($start['ok'] ?? false) === true, (string) ($start['message'] ?? ''));

    $moveHome = $moves->move($userId, 1);
    assertTrue($results, 'fpe.move.home', ($moveHome['ok'] ?? false) === true && (int) ($moveHome['location']['id'] ?? 0) === 1);

    $hint = $npcs->action($userId, 1, ['quest_npc' => '2', 'do' => '1'], 'quest_update:1:3:0');
    assertTrue($results, 'fpe.passerby.hint', ($hint['ok'] ?? false) === true);

    $moves->move($userId, 3);
    $chosen = $npcs->action($userId, 3, ['quest_npc' => '1', 'do' => '1'], 'starter_select:1');
    assertTrue($results, 'fpe.starter.choose', ($chosen['ok'] ?? false) === true);

    $finish = $npcs->action($userId, 3, ['quest_npc' => '1', 'do' => '1'], 'oak_finish_starter');
    assertTrue($results, 'fpe.starter.finish', ($finish['ok'] ?? false) === true, (string) ($finish['message'] ?? ''));
    assertTrue($results, 'fpe.quest.1.completed', $quests->isCompleted($userId, 1));
    assertTrue($results, 'fpe.quest.101.started', activeQuest($quests, $userId, 101));
    assertTrue($results, 'fpe.starter.has_moves', starterHasMoves($db, $userId));

    $moves->move($userId, 1);
    $route = $moves->move($userId, 4);
    assertTrue($results, 'fpe.move.route1', ($route['ok'] ?? false) === true && (int) ($route['location']['id'] ?? 0) === 4);
    assertTrue($results, 'fpe.quest.101.route_progress', questProcess($quests, $userId, 101) >= 10);

    $encounter = $wild->forceStartForUserAtLocation($userId, 4);
    $battleId = (int) ($encounter['wildEncounter']['battleId'] ?? 0);
    assertTrue($results, 'fpe.pve.force_start', ($encounter['ok'] ?? false) === true && $battleId > 0, (string) ($encounter['wildEncounter']['message'] ?? ''));
    weakenEnemy($db, $battleId);
    strengthenStarter($db, $userId);

    $battleState = $battle->state($userId);
    $moveId = firstMoveId($battleState);
    assertTrue($results, 'fpe.pve.move.available', $moveId > 0, 'move=' . $moveId);
    if ($moveId > 0) {
        $attack = $battle->action($userId, 'attack', ['move_id' => $moveId]);
        assertTrue($results, 'fpe.pve.first_battle_finishes', ($attack['ok'] ?? false) === true && !empty($attack['finished']), (string) ($attack['result'] ?? ''));
        assertTrue($results, 'fpe.quest.101.completed', $quests->isCompleted($userId, 101));
        assertTrue($results, 'fpe.quest.102.started', activeQuest($quests, $userId, 102));
    }

    $moves->move($userId, 5);
    $viridian = $moves->move($userId, 16);
    assertTrue($results, 'fpe.move.viridian', ($viridian['ok'] ?? false) === true && (int) ($viridian['location']['id'] ?? 0) === 16);
    assertTrue($results, 'fpe.quest.102.completed', $quests->isCompleted($userId, 102));
    assertTrue($results, 'fpe.quest.103.started', activeQuest($quests, $userId, 103));

    $ticketRowId = latestItemRow($db, $userId, TransportRepository::PLANE_TICKET_ITEM_ID);
    $flightRoutes = $transport->planeDestinationsForUser($userId);
    assertTrue($results, 'fpe.transport.ticket', $ticketRowId > 0, 'item_row=' . $ticketRowId);
    assertTrue($results, 'fpe.transport.routes', count($flightRoutes) > 0, 'routes=' . count($flightRoutes));
    if ($ticketRowId > 0 && count($flightRoutes) > 0) {
        $db->prepare('UPDATE users SET buildmy = 23, pve = 0, pvp = 0, trade = 0 WHERE id = :user LIMIT 1')->execute(['user' => $userId]);
        $shipRoutes = $transport->routesForUser($userId);
        $routeId = (int) ($shipRoutes[0]['id'] ?? 0);
        assertTrue($results, 'fpe.transport.ship_route', $routeId > 0, 'route=' . $routeId);
        $travel = $routeId > 0 ? $transport->travel($userId, $routeId) : ['ok' => false, 'message' => 'ship route missing'];
        assertTrue($results, 'fpe.transport.route_travel', ($travel['ok'] ?? false) === true, (string) ($travel['message'] ?? ''));
        assertTrue($results, 'fpe.quest.103.completed', $quests->isCompleted($userId, 103));
    }
} catch (Throwable $e) {
    fail($results, 'fatal', $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
} finally {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
}

printResults($results);
exit(hasFailures($results) ? 1 : 0);

function prepareUser(PDO $db, int $userId): void
{
    $db->prepare('UPDATE users SET buildmy = 1, pve = 0, pvp = 0, trade = 0, battleid = 0, pve_button = 0, atack_poke = 0 WHERE id = :user LIMIT 1')
        ->execute(['user' => $userId]);
    $db->prepare('DELETE FROM quest WHERE user_id = :user AND quest_id IN (1, 101, 102, 103)')
        ->execute(['user' => $userId]);
    $db->prepare('UPDATE transport_flights SET status = "qa-paused" WHERE user_id = :user AND status = "active"')
        ->execute(['user' => $userId]);
}

function activeQuest(QuestRepository $quests, int $userId, int $questId): bool
{
    $state = $quests->findForUser($userId, $questId);
    return $state !== null && (int) ($state['gotov'] ?? 0) === 0;
}

function questProcess(QuestRepository $quests, int $userId, int $questId): int
{
    $state = $quests->findForUser($userId, $questId);
    return $state !== null ? (int) ($state['process'] ?? 0) : 0;
}

function starterHasMoves(PDO $db, int $userId): bool
{
    $stmt = $db->prepare(
        'SELECT COUNT(*)
           FROM pok_user p
           JOIN attac_my_poke a ON a.pok_id = p.id
          WHERE p.users = :user
            AND p.startepoke = 1
            AND (a.a_id > 0 OR a.b_id > 0 OR a.c_id > 0 OR a.d_id > 0)'
    );
    $stmt->execute(['user' => $userId]);
    return (int) ($stmt->fetchColumn() ?: 0) > 0;
}

function weakenEnemy(PDO $db, int $battleId): void
{
    $stmt = $db->prepare('SELECT poke_2 FROM battles WHERE id = :battle LIMIT 1');
    $stmt->execute(['battle' => $battleId]);
    $key = (string) ($stmt->fetchColumn() ?: '');
    if (preg_match('/^pve_(\d+)$/', $key, $m)) {
        $db->prepare('UPDATE pok_pve SET hp_my = 1, hp_max = GREATEST(hp_max, 1) WHERE id = :id LIMIT 1')
            ->execute(['id' => (int) $m[1]]);
    }
}

function strengthenStarter(PDO $db, int $userId): void
{
    $db->prepare('UPDATE pok_user SET atk = 999, satk = 999, speed = 999, hp_my = hp_max WHERE users = :user AND active = 1 ORDER BY startepoke DESC, id DESC LIMIT 1')
        ->execute(['user' => $userId]);
}

function firstMoveId(array $battleState): int
{
    $fallback = 0;
    foreach (($battleState['battle']['moves'] ?? []) as $move) {
        $id = (int) ($move['id'] ?? 0);
        if ($id > 0 && (int) ($move['pp'] ?? 1) > 0) {
            if ($fallback === 0) {
                $fallback = $id;
            }
            if ((int) ($move['power'] ?? 0) > 0) {
                return $id;
            }
        }
    }
    return $fallback;
}

function latestItemRow(PDO $db, int $userId, int $itemId): int
{
    $stmt = $db->prepare('SELECT id FROM items_users WHERE user_id = :user AND item_id = :item AND count > 0 ORDER BY id DESC LIMIT 1');
    $stmt->execute(['user' => $userId, 'item' => $itemId]);
    return (int) ($stmt->fetchColumn() ?: 0);
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
