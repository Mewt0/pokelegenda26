<?php
declare(strict_types=1);

use Pokemon8\Controller\AuthController;
use Pokemon8\Controller\GameApiController;
use Pokemon8\Controller\GameController;
use Pokemon8\Controller\GameModuleController;
use Pokemon8\Controller\HomeController;
use Pokemon8\Database\Connection;
use Pokemon8\Game\GameRoutes;
use Pokemon8\Game\LocationGraph;
use Pokemon8\Game\LocationStateService;
use Pokemon8\Game\MapMoveService;
use Pokemon8\Http\Request;
use Pokemon8\Http\Router;
use Pokemon8\Repository\RankingRepository;
use Pokemon8\Repository\BanRepository;
use Pokemon8\Repository\LocationRepository;
use Pokemon8\Repository\UserRepository;
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
$locationGraph = LocationGraph::fromLegacyData(APP_ROOT . '/include/data.world.php');
$locationState = new LocationStateService($locations, $locationGraph, APP_ROOT);
$mapMoves = new MapMoveService($locations, $locationGraph, $locationState);
$passwords = new PasswordHasher();
$csrf = new Csrf($session);
$banGuard = new BanGuard($bans);

$home = new HomeController($rankings, $session, $csrf);
$auth = new AuthController($users, $passwords, $session, $csrf, ['techwork' => $appConfig['techwork']]);
$game = new GameController($session, $csrf);
$gameApi = new GameApiController($session, $csrf, $locationState, $mapMoves);
$gameModules = new GameModuleController($session);

$router = new Router();
$router->get('/', fn (Request $request) => $home->index($request));
$router->post('/login', fn (Request $request) => $auth->login($request));
$router->get('/logout', fn (Request $request) => $auth->logout($request));
$router->get('/game', fn (Request $request) => $game->start($request));
foreach (GameRoutes::MODULES as $slug => $_module) {
    $router->get('/game/' . $slug, function (Request $request) use ($gameModules, $slug) {
        return $gameModules->show($request, $slug);
    });
}
$router->get('/api/game/state', fn (Request $request) => $gameApi->state($request));
$router->post('/api/map/move', fn (Request $request) => $gameApi->move($request));

$request = Request::capture();
$banResponse = $banGuard->check($request);
if ($banResponse !== null) {
    $banResponse->send();
    exit;
}

$router->dispatch($request)->send();
