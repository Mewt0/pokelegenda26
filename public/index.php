<?php
declare(strict_types=1);

use Pokemon8\Controller\AdminApiController;
use Pokemon8\Controller\AdminController;
use Pokemon8\Controller\AuthController;
use Pokemon8\Controller\ChatApiController;
use Pokemon8\Controller\DexApiController;
use Pokemon8\Controller\FriendApiController;
use Pokemon8\Controller\GameApiController;
use Pokemon8\Controller\GameController;
use Pokemon8\Controller\GameModuleController;
use Pokemon8\Controller\HomeController;
use Pokemon8\Controller\InventoryApiController;
use Pokemon8\Controller\InventoryController;
use Pokemon8\Controller\ItemMarketApiController;
use Pokemon8\Controller\NpcApiController;
use Pokemon8\Controller\PokemonApiController;
use Pokemon8\Controller\PokemonController;
use Pokemon8\Controller\ProfileController;
use Pokemon8\Controller\PveBattleApiController;
use Pokemon8\Controller\PvpBattleApiController;
use Pokemon8\Controller\ShopApiController;
use Pokemon8\Controller\TransportApiController;
use Pokemon8\Database\Connection;
use Pokemon8\Game\ChatService;
use Pokemon8\Game\GameRoutes;
use Pokemon8\Game\LocationContentRepository;
use Pokemon8\Game\LocationGraph;
use Pokemon8\Game\LocationStateService;
use Pokemon8\Game\MapMoveService;
use Pokemon8\Game\NpcDialogService;
use Pokemon8\Game\WildEncounterService;
use Pokemon8\Game\BattleEngineService;
use Pokemon8\Http\Request;
use Pokemon8\Http\Response;
use Pokemon8\Http\Router;
use Pokemon8\Repository\AdminRepository;
use Pokemon8\Repository\ChatRepository;
use Pokemon8\Repository\RankingRepository;
use Pokemon8\Repository\BanRepository;
use Pokemon8\Repository\InventoryRepository;
use Pokemon8\Repository\ItemMarketRepository;
use Pokemon8\Repository\LocationRepository;
use Pokemon8\Repository\MessageRepository;
use Pokemon8\Repository\PokemonRepository;
use Pokemon8\Repository\ProfileRepository;
use Pokemon8\Repository\QuestRepository;
use Pokemon8\Repository\UserRepository;
use Pokemon8\Repository\BattleRepository;
use Pokemon8\Repository\DexRepository;
use Pokemon8\Repository\FriendRepository;
use Pokemon8\Repository\TrainingRepository;
use Pokemon8\Repository\TransportRepository;
use Pokemon8\Security\BanGuard;
use Pokemon8\Security\Csrf;
use Pokemon8\Security\PasswordHasher;
use Pokemon8\Security\Session;
use Pokemon8\Support\Env;
use Pokemon8\View\View;

define('APP_ROOT', dirname(__DIR__));

require APP_ROOT . '/src/Support/Autoload.php';

// Загружаем окружение и конфиги до создания сервисов приложения.
Env::load(APP_ROOT . '/.env');

$appConfig = require APP_ROOT . '/config/app.php';
$dbConfig = require APP_ROOT . '/config/database.php';

date_default_timezone_set($appConfig['timezone']);
ini_set('default_charset', 'UTF-8');
ini_set('display_errors', $appConfig['debug'] ? '1' : '0');
error_reporting(E_ALL);

$session = new Session($appConfig['session_name']);
$session->start();

// Собираем зависимости вручную. Позже это можно заменить DI-контейнером.
$db = Connection::make($dbConfig);
$users = new UserRepository($db);
$bans = new BanRepository($db);
$rankings = new RankingRepository($db);
$locations = new LocationRepository($db);
$pokemonRepository = new PokemonRepository($db);
$quests = new QuestRepository($db);
$inventory = new InventoryRepository($db);
$itemMarketRepository = new ItemMarketRepository($db, $inventory);
$training = new TrainingRepository($db, $inventory);
$transportRepository = new TransportRepository($db, $inventory);
$adminRepository = new AdminRepository($db);
$appConfig['techwork'] = $adminRepository->setting('techwork', (string) ($appConfig['techwork'] ?? '0'));
$profiles = new ProfileRepository($db);
$battleRepository = new BattleRepository($db);
$dexRepository = new DexRepository($db);
$chatRepository = new ChatRepository($db);
$friendRepository = new FriendRepository($db);
$messageRepository = new MessageRepository($db);
$locationGraph = LocationGraph::fromLegacyData(APP_ROOT . '/include/data.world.php');
$locationContent = LocationContentRepository::fromFile(APP_ROOT . '/config/location_content.php');
$locationState = new LocationStateService($locations, $locationGraph, APP_ROOT, $locationContent);
$mapMoves = new MapMoveService($locations, $locationGraph, $locationState);
$wildEncounters = new WildEncounterService($db);
$battleEngine = new BattleEngineService($battleRepository);
$npcDialogs = new NpcDialogService($locations, $quests, $inventory, $pokemonRepository, $locationContent);
$chatService = new ChatService($chatRepository, $users, $session);
$passwords = new PasswordHasher();
$csrf = new Csrf($session);
$banGuard = new BanGuard($bans);

$home = new HomeController($rankings, $session, $csrf);
$adminPage = new AdminController($session, $csrf, $adminRepository);
$adminApi = new AdminApiController($session, $csrf, $adminRepository);
$auth = new AuthController($users, $passwords, $session, $csrf, $adminRepository, ['techwork' => $appConfig['techwork']]);
$game = new GameController($session, $csrf, $users, $adminRepository);
$inventoryPage = new InventoryController($session, $inventory, $csrf);
$inventoryApi = new InventoryApiController($session, $inventory, $csrf, $training);
$pokemonPage = new PokemonController($session, $csrf);
$pokemonApi = new PokemonApiController($session, $csrf, $pokemonRepository, $training);
$shopApi = new ShopApiController($session, $csrf, $training);
$itemMarketApi = new ItemMarketApiController($session, $csrf, $itemMarketRepository);
$profilePage = new ProfileController($session, $profiles);
$gameApi = new GameApiController($session, $csrf, $locationState, $mapMoves, $wildEncounters, $battleEngine, $locations, $users);
$gameModules = new GameModuleController($session, $csrf, $transportRepository, $messageRepository, $itemMarketRepository);
$npcApi = new NpcApiController($session, $csrf, $npcDialogs);
$chatApi = new ChatApiController($session, $csrf, $chatService, $locations);
$pveBattleApi = new PveBattleApiController($session, $csrf, $battleEngine);
$pvpBattleApi = new PvpBattleApiController($session, $csrf, $battleEngine);
$dexApi = new DexApiController($session, $dexRepository);
$friendApi = new FriendApiController($session, $csrf, $friendRepository);
$transportApi = new TransportApiController($session, $csrf, $transportRepository);

$router = new Router();
$router->get('/', fn (Request $request) => $home->index($request));
$router->post('/login', fn (Request $request) => $auth->login($request));
$router->get('/logout', fn (Request $request) => $auth->logout($request));
$router->get('/game', fn (Request $request) => $game->start($request));
$router->get('/game/admin', fn (Request $request) => $adminPage->index($request));
$router->get('/game/items', fn (Request $request) => $inventoryPage->index($request));
$router->get('/game/pokemon', fn (Request $request) => $pokemonPage->index($request));
$router->get('/game/profile', fn (Request $request) => $profilePage->show($request));
$router->get('/profile', fn (Request $request) => Response::redirect('/game/profile?' . http_build_query($request->query)));
foreach (GameRoutes::MODULES as $slug => $_module) {
    if ($slug === 'items' || $slug === 'pokemon' || $slug === 'profile' || $slug === 'admin') {
        continue;
    }
    $router->get('/game/' . $slug, function (Request $request) use ($gameModules, $slug) {
        return $gameModules->show($request, $slug);
    });
}
$router->get('/api/game/state', fn (Request $request) => $gameApi->state($request));
$router->post('/api/map/move', fn (Request $request) => $gameApi->move($request));
$router->post('/api/game/pve-mode', fn (Request $request) => $gameApi->setPveMode($request));
$router->post('/api/battle/pve/force', fn (Request $request) => $gameApi->forcePveBattleRoad2($request));
$router->get('/api/battle/pve/state', fn (Request $request) => $pveBattleApi->state($request));
$router->post('/api/battle/pve/action', fn (Request $request) => $pveBattleApi->action($request));
$router->post('/api/battle/pve/ack-end', fn (Request $request) => $pveBattleApi->ackEnd($request));
$router->get('/api/battle/pvp/status', fn (Request $request) => $pvpBattleApi->status($request));
$router->get('/api/battle/pvp/requests', fn (Request $request) => $pvpBattleApi->requests($request));
$router->get('/api/battle/pvp/pokemon-options', fn (Request $request) => $pvpBattleApi->pokemonOptions($request));
$router->post('/api/battle/pvp/request', fn (Request $request) => $pvpBattleApi->request($request));
$router->post('/api/battle/pvp/force', fn (Request $request) => $pvpBattleApi->force($request));
$router->post('/api/battle/pvp/accept', fn (Request $request) => $pvpBattleApi->accept($request));
$router->post('/api/battle/pvp/decline', fn (Request $request) => $pvpBattleApi->decline($request));
$router->get('/api/inventory/page', fn (Request $request) => $inventoryApi->page($request));
$router->get('/api/inventory/battle', fn (Request $request) => $inventoryApi->battle($request));
$router->post('/api/inventory/equip', fn (Request $request) => $inventoryApi->equip($request));
$router->post('/api/inventory/unequip', fn (Request $request) => $inventoryApi->unequip($request));
$router->post('/api/inventory/use-target', fn (Request $request) => $inventoryApi->useTarget($request));
$router->get('/api/pokemon/moves', fn (Request $request) => $pokemonApi->moves($request));
$router->post('/api/pokemon/training', fn (Request $request) => $pokemonApi->training($request));
$router->post('/api/shop/training/buy', fn (Request $request) => $shopApi->buyTrainingItem($request));
$router->get('/api/market/items', fn (Request $request) => $itemMarketApi->index($request));
$router->post('/api/market/items/buy', fn (Request $request) => $itemMarketApi->buy($request));
$router->get('/api/transport/routes', fn (Request $request) => $transportApi->routes($request));
$router->post('/api/transport/travel', fn (Request $request) => $transportApi->travel($request));
$router->get('/api/admin/overview', fn (Request $request) => $adminApi->overview($request));
$router->get('/api/admin/dashboard', fn (Request $request) => $adminApi->dashboard($request));
$router->get('/api/admin/lookups', fn (Request $request) => $adminApi->lookups($request));
$router->get('/api/admin/legacy-map', fn (Request $request) => $adminApi->legacyMap($request));
$router->get('/api/admin/items', fn (Request $request) => $adminApi->items($request));
$router->post('/api/admin/items/save', fn (Request $request) => $adminApi->saveItem($request));
$router->post('/api/admin/items/grant', fn (Request $request) => $adminApi->grantItem($request));
$router->delete('/api/admin/items', fn (Request $request) => $adminApi->deleteItem($request));
$router->get('/api/admin/drop-rules', fn (Request $request) => $adminApi->dropRules($request));
$router->post('/api/admin/drop-rules/save', fn (Request $request) => $adminApi->saveDropRule($request));
$router->post('/api/admin/drop-rules/delete', fn (Request $request) => $adminApi->deleteDropRule($request));
$router->get('/api/admin/users', fn (Request $request) => $adminApi->users($request));
$router->post('/api/admin/users/save', fn (Request $request) => $adminApi->saveUser($request));
$router->post('/api/admin/users/ban', fn (Request $request) => $adminApi->banUser($request));
$router->get('/api/admin/market-items', fn (Request $request) => $adminApi->marketItems($request));
$router->post('/api/admin/market-items', fn (Request $request) => $adminApi->saveMarketItem($request));
$router->delete('/api/admin/market-items', fn (Request $request) => $adminApi->deleteMarketItem($request));
$router->get('/api/admin/locations', fn (Request $request) => $adminApi->locations($request));
$router->post('/api/admin/locations', fn (Request $request) => $adminApi->saveLocation($request));
$router->delete('/api/admin/locations', fn (Request $request) => $adminApi->deleteLocation($request));
$router->get('/api/admin/pokemon', fn (Request $request) => $adminApi->pokemon($request));
$router->post('/api/admin/pokemon', fn (Request $request) => $adminApi->savePokemon($request));
$router->post('/api/admin/pokemon/grant', fn (Request $request) => $adminApi->grantPokemon($request));
$router->delete('/api/admin/pokemon', fn (Request $request) => $adminApi->deletePokemon($request));
$router->get('/api/admin/attacks', fn (Request $request) => $adminApi->attacks($request));
$router->post('/api/admin/attacks', fn (Request $request) => $adminApi->saveAttack($request));
$router->post('/api/admin/attacks/learn', fn (Request $request) => $adminApi->saveAttackLearn($request));
$router->delete('/api/admin/attacks', fn (Request $request) => $adminApi->deleteAttack($request));
$router->get('/api/admin/news', fn (Request $request) => $adminApi->news($request));
$router->post('/api/admin/news', fn (Request $request) => $adminApi->saveNews($request));
$router->delete('/api/admin/news', fn (Request $request) => $adminApi->deleteNews($request));
$router->get('/api/admin/tournaments', fn (Request $request) => $adminApi->tournaments($request));
$router->post('/api/admin/tournaments', fn (Request $request) => $adminApi->saveTournament($request));
$router->delete('/api/admin/tournaments', fn (Request $request) => $adminApi->deleteTournament($request));
$router->post('/api/admin/tournaments/participant', fn (Request $request) => $adminApi->saveTournamentParticipant($request));
$router->get('/api/admin/medals', fn (Request $request) => $adminApi->medals($request));
$router->post('/api/admin/medals', fn (Request $request) => $adminApi->saveMedal($request));
$router->delete('/api/admin/medals', fn (Request $request) => $adminApi->deleteMedal($request));
$router->post('/api/admin/medals/award', fn (Request $request) => $adminApi->awardMedal($request));
$router->get('/api/admin/moderation', fn (Request $request) => $adminApi->moderation($request));
$router->get('/api/admin/audit', fn (Request $request) => $adminApi->audit($request));
$router->get('/api/admin/settings', fn (Request $request) => $adminApi->settings($request));
$router->post('/api/admin/settings', fn (Request $request) => $adminApi->saveSettings($request));
$router->get('/api/dex/pokemon', fn (Request $request) => $dexApi->pokemonList($request));
$router->get('/api/dex/pokemon/show', fn (Request $request) => $dexApi->pokemon($request));
$router->get('/api/dex/attacks', fn (Request $request) => $dexApi->attackList($request));
$router->get('/api/dex/attack/show', fn (Request $request) => $dexApi->attack($request));
$router->post('/api/pokemon/move', fn (Request $request) => $pokemonApi->setMove($request));
$router->get('/api/location/npc', fn (Request $request) => $npcApi->show($request));
$router->post('/api/location/npc/action', fn (Request $request) => $npcApi->action($request));
$router->get('/api/chat/messages', fn (Request $request) => $chatApi->messages($request));
$router->post('/api/chat/messages', fn (Request $request) => $chatApi->send($request));
$router->get('/api/friends/status', fn (Request $request) => $friendApi->status($request));
$router->get('/api/friends/requests', fn (Request $request) => $friendApi->requests($request));
$router->post('/api/friends/request', fn (Request $request) => $friendApi->request($request));
$router->post('/api/friends/accept', fn (Request $request) => $friendApi->accept($request));
$router->post('/api/friends/decline', fn (Request $request) => $friendApi->decline($request));
$router->post('/api/friends/remove', fn (Request $request) => $friendApi->remove($request));

$request = Request::capture();
$banResponse = $banGuard->check($request);
if ($banResponse !== null) {
    $banResponse->send();
    exit;
}

$isTechwork = (int) $adminRepository->setting('techwork', (string) ($appConfig['techwork'] ?? '0')) === 1;
$path = '/' . trim($request->path, '/');
$path = $path === '//' ? '/' : $path;
$isGamePath = $path === '/game' || str_starts_with($path, '/game/');
$isGameApiPath = str_starts_with($path, '/api/');
$isAdminPath = $path === '/game/admin' || str_starts_with($path, '/api/admin/');
$currentUserId = (int) $session->get('id', 0);
$isCurrentAdmin = $currentUserId > 0 && $adminRepository->canAccess($currentUserId);

if ($isTechwork && !$isCurrentAdmin && !$isAdminPath && ($isGamePath || $isGameApiPath)) {
    $response = str_starts_with($path, '/api/')
        ? new Response(
            json_encode(['ok' => false, 'error' => 'techwork', 'message' => 'На сервере идут технические работы.'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}',
            503,
            ['Content-Type' => 'application/json; charset=UTF-8']
        )
        : new Response(View::render('error', ['message' => 'На сервере идут технические работы.']), 503);
    $response->send();
    exit;
}

$router->dispatch($request)->send();
