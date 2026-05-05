<?php
declare(strict_types=1);

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
use Pokemon8\Controller\NpcApiController;
use Pokemon8\Controller\PokemonApiController;
use Pokemon8\Controller\PokemonController;
use Pokemon8\Controller\ProfileController;
use Pokemon8\Controller\PveBattleApiController;
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
use Pokemon8\Http\Router;
use Pokemon8\Repository\ChatRepository;
use Pokemon8\Repository\RankingRepository;
use Pokemon8\Repository\BanRepository;
use Pokemon8\Repository\InventoryRepository;
use Pokemon8\Repository\LocationRepository;
use Pokemon8\Repository\PokemonRepository;
use Pokemon8\Repository\ProfileRepository;
use Pokemon8\Repository\QuestRepository;
use Pokemon8\Repository\UserRepository;
use Pokemon8\Repository\BattleRepository;
use Pokemon8\Repository\DexRepository;
use Pokemon8\Repository\FriendRepository;
use Pokemon8\Security\BanGuard;
use Pokemon8\Security\Csrf;
use Pokemon8\Security\PasswordHasher;
use Pokemon8\Security\Session;
use Pokemon8\Support\Env;

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
$profiles = new ProfileRepository($db);
$battleRepository = new BattleRepository($db);
$dexRepository = new DexRepository($db);
$chatRepository = new ChatRepository($db);
$friendRepository = new FriendRepository($db);
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
$auth = new AuthController($users, $passwords, $session, $csrf, ['techwork' => $appConfig['techwork']]);
$game = new GameController($session, $csrf, $users);
$inventoryPage = new InventoryController($session, $inventory, $csrf);
$inventoryApi = new InventoryApiController($session, $inventory, $csrf);
$pokemonPage = new PokemonController($session, $csrf);
$pokemonApi = new PokemonApiController($session, $csrf, $pokemonRepository);
$profilePage = new ProfileController($session, $profiles);
$gameApi = new GameApiController($session, $csrf, $locationState, $mapMoves, $wildEncounters, $battleEngine, $locations, $users);
$gameModules = new GameModuleController($session);
$npcApi = new NpcApiController($session, $csrf, $npcDialogs);
$chatApi = new ChatApiController($session, $csrf, $chatService, $locations);
$pveBattleApi = new PveBattleApiController($session, $csrf, $battleEngine);
$dexApi = new DexApiController($session, $dexRepository);
$friendApi = new FriendApiController($session, $csrf, $friendRepository);

$router = new Router();
$router->get('/', fn (Request $request) => $home->index($request));
$router->post('/login', fn (Request $request) => $auth->login($request));
$router->get('/logout', fn (Request $request) => $auth->logout($request));
$router->get('/game', fn (Request $request) => $game->start($request));
$router->get('/game/items', fn (Request $request) => $inventoryPage->index($request));
$router->get('/game/pokemon', fn (Request $request) => $pokemonPage->index($request));
$router->get('/game/profile', fn (Request $request) => $profilePage->show($request));
foreach (GameRoutes::MODULES as $slug => $_module) {
    if ($slug === 'items' || $slug === 'pokemon' || $slug === 'profile') {
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
$router->get('/api/inventory/page', fn (Request $request) => $inventoryApi->page($request));
$router->get('/api/inventory/battle', fn (Request $request) => $inventoryApi->battle($request));
$router->post('/api/inventory/equip', fn (Request $request) => $inventoryApi->equip($request));
$router->post('/api/inventory/unequip', fn (Request $request) => $inventoryApi->unequip($request));
$router->get('/api/pokemon/moves', fn (Request $request) => $pokemonApi->moves($request));
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

$router->dispatch($request)->send();
