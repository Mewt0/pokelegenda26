<?php
declare(strict_types=1);

use Pokemon8\Database\Connection;
use Pokemon8\Game\LocationContentRepository;
use Pokemon8\Game\LocationGraph;
use Pokemon8\Game\LocationStateService;
use Pokemon8\Game\NpcDialogService;
use Pokemon8\Repository\EggRepository;
use Pokemon8\Repository\InventoryRepository;
use Pokemon8\Repository\LocationRepository;
use Pokemon8\Repository\PokemonEvolutionRepository;
use Pokemon8\Repository\PokemonMarketRepository;
use Pokemon8\Repository\PokemonRepository;
use Pokemon8\Repository\QuestRepository;
use Pokemon8\Repository\RewardRepository;
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
$rewards = new RewardRepository($db);
$inventory->setRewardRepository($rewards);
$quests = new QuestRepository($db);
$locations = new LocationRepository($db);
$pokemon = new PokemonRepository($db);
$eggs = new EggRepository($db);
$market = new PokemonMarketRepository($db, $inventory);
$graph = LocationGraph::fromLegacyData(APP_ROOT . '/include/data.world.php');
$content = LocationContentRepository::fromFile(APP_ROOT . '/config/location_content.php');
$locationState = new LocationStateService($locations, $graph, APP_ROOT, $content);
$npcs = new NpcDialogService($db, $locations, $quests, $inventory, $pokemon, $content, $rewards);

$results = [];
$tacos = userId($db, 'Tacos');
$niga = userId($db, 'NIGA');
assertTrue($results, 'users.tacos', $tacos > 0, 'id=' . $tacos);
assertTrue($results, 'users.niga', $niga > 0, 'id=' . $niga);

if ($tacos <= 0 || $niga <= 0) {
    printResults($results);
    exit(1);
}

$db->beginTransaction();
try {
    smokeQuests($db, $results, $quests, $rewards, $inventory, $tacos);
    smokeLocationsAndNpcs($db, $results, $locationState, $graph, $npcs, $tacos);
    smokeEggs($db, $results, $eggs, $inventory, $tacos);
    smokePokemonMarket($db, $results, $market, $inventory, $tacos, $niga);
} catch (Throwable $e) {
    fail($results, 'fatal', $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
} finally {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
}

printResults($results);
exit(hasFailures($results) ? 1 : 0);

function smokeQuests(PDO $db, array &$results, QuestRepository $quests, RewardRepository $rewards, InventoryRepository $inventory, int $userId): void
{
    assertTrue($results, 'quests.definitions.seeded', countRows($db, 'quest_definitions') >= 7, 'definitions=' . countRows($db, 'quest_definitions'));
    assertTrue($results, 'quests.steps.seeded', countRows($db, 'quest_steps') >= 15, 'steps=' . countRows($db, 'quest_steps'));

    $journal = $quests->journalForUser($userId);
    assertTrue($results, 'quests.journal.new_layer', ($journal['ok'] ?? false) === true && count($journal['quests'] ?? []) >= 7, 'quests=' . count($journal['quests'] ?? []));

    $questId = 990101;
    $dependentId = 990102;
    $now = time();
    $db->prepare(
        'INSERT INTO quest_definitions (id, title, description, depends_on_quest_id, repeatable, reward_json, enabled, updated_at)
         VALUES
         (:quest, "QA temp quest", "Smoke quest for start/progress/completion.", 0, 0, :reward, 1, :now),
         (:dependent, "QA dependent quest", "Smoke dependency quest.", :quest_dep, 0, :reward_dep, 1, :now_dep)
         ON DUPLICATE KEY UPDATE title = VALUES(title), description = VALUES(description), depends_on_quest_id = VALUES(depends_on_quest_id), reward_json = VALUES(reward_json), enabled = 1, updated_at = VALUES(updated_at)'
    )->execute([
        'quest' => $questId,
        'dependent' => $dependentId,
        'quest_dep' => $questId,
        'reward' => '{"items":{"1":1},"rank":1}',
        'reward_dep' => '{"items":{"1":2},"rank":1}',
        'now' => $now,
        'now_dep' => $now,
    ]);
    $db->prepare(
        'INSERT INTO quest_steps (quest_id, step_no, title, description, action_key, required_process, reward_json, enabled)
         VALUES
         (:quest, 1, "Start", "Start temp quest.", "qa_start", 10, NULL, 1),
         (:quest2, 2, "Finish", "Finish temp quest.", "qa_finish", 20, :reward, 1),
         (:dependent, 1, "Dependent start", "Start dependent quest.", "qa_dep_start", 5, NULL, 1)
         ON DUPLICATE KEY UPDATE title = VALUES(title), description = VALUES(description), action_key = VALUES(action_key), required_process = VALUES(required_process), reward_json = VALUES(reward_json), enabled = 1'
    )->execute([
        'quest' => $questId,
        'quest2' => $questId,
        'dependent' => $dependentId,
        'reward' => '{"items":{"1":1},"rank":1}',
    ]);

    $blocked = $quests->startFromDefinition($userId, $dependentId);
    assertTrue($results, 'quests.dependency.blocks', ($blocked['ok'] ?? true) === false, (string) ($blocked['message'] ?? 'blocked'));

    $started = $quests->startFromDefinition($userId, $questId);
    assertTrue($results, 'quests.start', ($started['ok'] ?? false) === true, (string) ($started['message'] ?? ''));
    $state = $quests->findForUser($userId, $questId);
    assertTrue($results, 'quests.progress.persisted', $state !== null && (int) ($state['process'] ?? 0) === 10, 'process=' . (int) ($state['process'] ?? 0));

    $quests->updateState($userId, $questId, 20, 1);
    $completed = $quests->findForUser($userId, $questId);
    assertTrue($results, 'quests.completion.persisted', $completed !== null && (int) ($completed['gotov'] ?? 0) === 1, 'gotov=' . (int) ($completed['gotov'] ?? 0));

    $dependent = $quests->startFromDefinition($userId, $dependentId);
    assertTrue($results, 'quests.dependency.unlocked', ($dependent['ok'] ?? false) === true, (string) ($dependent['message'] ?? ''));

    $coinsBefore = $inventory->countItem($userId, 1);
    $notificationsBefore = countRowsWhere($db, 'game_notifications', 'user_id = ' . $userId);
    $rewards->grantItems($userId, [1 => 1], 'Квест: QA smoke');
    $coinsAfter = $inventory->countItem($userId, 1);
    $notificationsAfter = countRowsWhere($db, 'game_notifications', 'user_id = ' . $userId);
    assertTrue($results, 'quests.reward_flow.items', $coinsAfter >= $coinsBefore + 1, 'coins ' . $coinsBefore . ' -> ' . $coinsAfter);
    assertTrue($results, 'quests.reward_flow.push', $notificationsAfter >= $notificationsBefore + 1, 'notifications ' . $notificationsBefore . ' -> ' . $notificationsAfter);
}

function smokeLocationsAndNpcs(PDO $db, array &$results, LocationStateService $locationState, LocationGraph $graph, NpcDialogService $npcs, int $userId): void
{
    $db->prepare('UPDATE users SET buildmy = 1, pve = 0, pvp = 0, trade = 0 WHERE id = :user LIMIT 1')
        ->execute(['user' => $userId]);

    $state = $locationState->currentStateForUser($userId);
    assertTrue($results, 'location.state', ($state['ok'] ?? false) === true && (int) ($state['location']['id'] ?? 0) === 1, 'location=' . (int) ($state['location']['id'] ?? 0));
    assertTrue($results, 'location.transitions', count($state['moves'] ?? []) > 0, 'moves=' . count($state['moves'] ?? []));
    assertTrue($results, 'location.blocked_route', !$graph->canMove(1, 999999), '1 -> 999999 blocked');
    assertTrue($results, 'location.npcs.loaded', count($state['location']['npcs'] ?? []) >= 3, 'npcs=' . count($state['location']['npcs'] ?? []));

    $marketDialog = $npcs->open($userId, 1, ['npc' => '2']);
    $marketChoices = $marketDialog['npc']['choices'] ?? [];
    $hasMarketRoute = false;
    foreach ($marketChoices as $choice) {
        $hasMarketRoute = $hasMarketRoute || (($choice['route'] ?? '') === '/game/market/items');
    }
    assertTrue($results, 'npc.market.route', ($marketDialog['ok'] ?? false) === true && $hasMarketRoute, 'route=/game/market/items');

    $questDialog = $npcs->open($userId, 1, ['quest_npc' => '2', 'do' => '1']);
    $hasQuestAction = false;
    foreach (($questDialog['npc']['choices'] ?? []) as $choice) {
        $hasQuestAction = $hasQuestAction || str_starts_with((string) ($choice['action'] ?? ''), 'quest_update:');
    }
    assertTrue($results, 'npc.quest.action', ($questDialog['ok'] ?? false) === true && $hasQuestAction, 'quest_update action present');
}

function smokeEggs(PDO $db, array &$results, EggRepository $eggs, InventoryRepository $inventory, int $userId): void
{
    $futureEggId = nextId($db, 'eggs', 'id_egg');
    insertEgg($db, $futureEggId, $userId, 1, time() + 86400);
    $inventory->addItem($userId, EggRepository::INCUBATOR_ITEM_ID, 1);
    $incubatorsBefore = $inventory->countItem($userId, EggRepository::INCUBATOR_ITEM_ID);
    $incubated = $eggs->incubate($userId, $futureEggId);
    $incubatorsAfter = $inventory->countItem($userId, EggRepository::INCUBATOR_ITEM_ID);
    $newReadyAt = (int) ($db->query('SELECT dtime FROM eggs WHERE id_egg = ' . $futureEggId)->fetchColumn() ?: 0);
    assertTrue($results, 'eggs.incubate', ($incubated['ok'] ?? false) === true && $newReadyAt < time() + 86400, 'ready_at=' . $newReadyAt);
    assertTrue($results, 'eggs.incubator.consume', $incubatorsAfter === $incubatorsBefore - 1, 'incubators ' . $incubatorsBefore . ' -> ' . $incubatorsAfter);

    $readyEggId = $futureEggId + 1;
    insertEgg($db, $readyEggId, $userId, 1, time() - 60);
    $listed = $eggs->listForUser($userId);
    $seenReady = false;
    foreach (($listed['eggs'] ?? []) as $egg) {
        $seenReady = $seenReady || (int) ($egg['id'] ?? 0) === $readyEggId;
    }
    assertTrue($results, 'eggs.list.persistence', $seenReady, 'egg_id=' . $readyEggId);

    $hatched = $eggs->hatch($userId, $readyEggId);
    $pokemonId = (int) ($hatched['pokemon_id'] ?? 0);
    $pokemonExists = $pokemonId > 0 && (int) ($db->query('SELECT COUNT(*) FROM pok_user WHERE id = ' . $pokemonId . ' AND users = ' . $userId)->fetchColumn() ?: 0) === 1;
    $eggDeleted = (int) ($db->query('SELECT COUNT(*) FROM eggs WHERE id_egg = ' . $readyEggId)->fetchColumn() ?: 0) === 0;
    assertTrue($results, 'eggs.hatch', ($hatched['ok'] ?? false) === true && $pokemonExists && $eggDeleted, 'pokemon_id=' . $pokemonId);
    assertTrue($results, 'eggs.move_generation', is_array($hatched['start_move'] ?? null) && (int) ($hatched['start_move']['id'] ?? 0) > 0, 'move=' . (string) ($hatched['start_move']['name'] ?? 'none'));
}

function smokePokemonMarket(PDO $db, array &$results, PokemonMarketRepository $market, InventoryRepository $inventory, int $sellerId, int $buyerId): void
{
    $db->prepare('UPDATE users SET pve = 0, pvp = 0, trade = 0 WHERE id IN (:seller, :buyer)')
        ->execute(['seller' => $sellerId, 'buyer' => $buyerId]);
    $db->prepare('UPDATE pok_user SET active = 0 WHERE users = :buyer AND startone = 0')
        ->execute(['buyer' => $buyerId]);

    $cancelPokemonId = createTempPokemon($db, $sellerId, 1, 10, 1);
    $listedCancel = $market->listPokemon($sellerId, $cancelPokemonId, 1000, (string) $buyerId);
    $cancelLotId = lotIdByPokemon($db, $cancelPokemonId);
    assertTrue($results, 'pokemon_market.list.private', ($listedCancel['ok'] ?? false) === true && $cancelLotId > 0, 'lot=' . $cancelLotId);

    $sellerIndex = $market->index($sellerId, (string) $cancelPokemonId);
    $buyerIndex = $market->index($buyerId, (string) $cancelPokemonId);
    assertTrue($results, 'pokemon_market.private.visible_to_seller', containsLot($sellerIndex, $cancelLotId), 'lot=' . $cancelLotId);
    assertTrue($results, 'pokemon_market.private.visible_to_buyer', containsLot($buyerIndex, $cancelLotId), 'lot=' . $cancelLotId);

    $cancelled = $market->cancel($sellerId, $cancelLotId);
    $returned = (int) ($db->query('SELECT users FROM pok_user WHERE id = ' . $cancelPokemonId)->fetchColumn() ?: 0) === $sellerId;
    assertTrue($results, 'pokemon_market.cancel', ($cancelled['ok'] ?? false) === true && $returned, (string) ($cancelled['message'] ?? ''));

    $buyPokemonId = createTempPokemon($db, $sellerId, 4, 10, 1);
    $listedBuy = $market->listPokemon($sellerId, $buyPokemonId, 1000, (string) $buyerId);
    $buyLotId = lotIdByPokemon($db, $buyPokemonId);
    $inventory->addItem($buyerId, 1, 5000);
    $bought = $market->buy($buyerId, $buyLotId);
    $owner = (int) ($db->query('SELECT users FROM pok_user WHERE id = ' . $buyPokemonId)->fetchColumn() ?: 0);
    $lotGone = (int) ($db->query('SELECT COUNT(*) FROM rinok_poke WHERE id_lot = ' . $buyLotId)->fetchColumn() ?: 0) === 0;
    assertTrue($results, 'pokemon_market.buy.transfer', ($listedBuy['ok'] ?? false) === true && ($bought['ok'] ?? false) === true && $owner === $buyerId && $lotGone, 'owner=' . $owner . ', message=' . (string) ($bought['message'] ?? ''));

    $db->prepare('UPDATE users SET pve = 1 WHERE id = :seller LIMIT 1')->execute(['seller' => $sellerId]);
    $busyPokemonId = createTempPokemon($db, $sellerId, 7, 10, 1);
    $busy = $market->listPokemon($sellerId, $busyPokemonId, 1000, '');
    assertTrue($results, 'pokemon_market.battle_lock', ($busy['ok'] ?? true) === false, (string) ($busy['message'] ?? ''));
    $db->prepare('UPDATE users SET pve = 0 WHERE id = :seller LIMIT 1')->execute(['seller' => $sellerId]);

    $db->prepare('UPDATE pok_user SET active = 0 WHERE users = :seller')->execute(['seller' => $sellerId]);
    $lastPokemonId = createTempPokemon($db, $sellerId, 7, 10, 1);
    $last = $market->listPokemon($sellerId, $lastPokemonId, 1000, '');
    assertTrue($results, 'pokemon_market.active_protection', ($last['ok'] ?? true) === false, (string) ($last['message'] ?? ''));
}

function insertEgg(PDO $db, int $eggId, int $userId, int $baseId, int $readyAt): void
{
    $db->prepare(
        'INSERT INTO eggs
            (id_egg, base_id_egg, dtime, users_egg, hp_iv, atk_iv, def_iv, sdef_iv, satk_iv, speed_iv,
             tips, attac_one, spar, parent_one_id, parent_two_id, parent_one_user_id, parent_two_user_id,
             breeding_request_id, breeding_method)
         VALUES
            (:id, :base, :dtime, :user, 10, 11, 12, 13, 14, 15,
             "normal", 0, 0, 0, 0, 0, 0, 0, "qa")'
    )->execute(['id' => $eggId, 'base' => $baseId, 'dtime' => $readyAt, 'user' => $userId]);
}

function createTempPokemon(PDO $db, int $userId, int $baseId, int $level, int $active): int
{
    $baseStmt = $db->prepare('SELECT title, hp, atk, def, satk, sdef, speed, ability_key FROM poke_base WHERE id = :base LIMIT 1');
    $baseStmt->execute(['base' => $baseId]);
    $base = $baseStmt->fetch(PDO::FETCH_ASSOC) ?: [
        'title' => 'Pokemon #' . $baseId,
        'hp' => 45,
        'atk' => 45,
        'def' => 45,
        'satk' => 45,
        'sdef' => 45,
        'speed' => 45,
        'ability_key' => null,
    ];
    $pokemonId = nextId($db, 'pok_user', 'id');
    $hp = max(1, (int) round((((1 + ((int) $base['hp'] * 2) + 100) * ($level / 100)) + 10)));
    $stat = static fn (int $value): int => max(1, (int) round((((1 + ($value * 2)) * ($level / 100)) + 5)));
    $db->prepare(
        'INSERT INTO pok_user
            (id, users, basenum, names, active, evcount, lvl, sex, har, hp_my, hp_max, exp, exp_b,
             atk, def, satk, sdef, speed, hp_ev, atk_ev, def_ev, satk_ev, sdef_ev, speed_ev,
             hp_iv, atk_iv, def_iv, satk_iv, sdef_iv, speed_iv, tips, startone, startepoke,
             reproduction, happy, datemay, usersone, sprz, item, ability_key)
         VALUES
            (:id, :user, :base, :name, :active, 0, :level, 1, 16, :hp_my, :hp_max, 0, 100,
             :atk, :def, :satk, :sdef, :speed, 0, 0, 0, 0, 0, 0,
             1, 1, 1, 1, 1, 1, "normal", 0, 0,
             0, 0, NOW(), :user_one, 0, 0, :ability)'
    )->execute([
        'id' => $pokemonId,
        'user' => $userId,
        'base' => $baseId,
        'name' => (string) $base['title'],
        'active' => $active,
        'level' => $level,
        'hp_my' => $hp,
        'hp_max' => $hp,
        'atk' => $stat((int) $base['atk']),
        'def' => $stat((int) $base['def']),
        'satk' => $stat((int) $base['satk']),
        'sdef' => $stat((int) $base['sdef']),
        'speed' => $stat((int) $base['speed']),
        'user_one' => $userId,
        'ability' => $base['ability_key'] ?? null,
    ]);

    return $pokemonId;
}

function lotIdByPokemon(PDO $db, int $pokemonId): int
{
    $stmt = $db->prepare('SELECT id_lot FROM rinok_poke WHERE id_poke = :pokemon ORDER BY id_lot DESC LIMIT 1');
    $stmt->execute(['pokemon' => $pokemonId]);
    return (int) ($stmt->fetchColumn() ?: 0);
}

function containsLot(array $payload, int $lotId): bool
{
    foreach (($payload['lots'] ?? []) as $lot) {
        if ((int) ($lot['id_lot'] ?? 0) === $lotId) {
            return true;
        }
    }
    return false;
}

function userId(PDO $db, string $login): int
{
    $stmt = $db->prepare('SELECT id FROM users WHERE LOWER(login) = LOWER(:login) LIMIT 1');
    $stmt->execute(['login' => $login]);
    return (int) ($stmt->fetchColumn() ?: 0);
}

function countRows(PDO $db, string $table): int
{
    return (int) ($db->query('SELECT COUNT(*) FROM `' . $table . '`')->fetchColumn() ?: 0);
}

function countRowsWhere(PDO $db, string $table, string $where): int
{
    return (int) ($db->query('SELECT COUNT(*) FROM `' . $table . '` WHERE ' . $where)->fetchColumn() ?: 0);
}

function nextId(PDO $db, string $table, string $column): int
{
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $table) || !preg_match('/^[a-zA-Z0-9_]+$/', $column)) {
        return 1;
    }
    return (int) ($db->query('SELECT COALESCE(MAX(`' . $column . '`), 0) + 1 FROM `' . $table . '`')->fetchColumn() ?: 1);
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
