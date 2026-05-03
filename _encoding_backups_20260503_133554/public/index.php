<?php
declare(strict_types=1);

use Pokemon8\Controller\AuthController;
use Pokemon8\Controller\GameController;
use Pokemon8\Controller\HomeController;
use Pokemon8\Database\Connection;
use Pokemon8\Http\Request;
use Pokemon8\Http\Router;
use Pokemon8\Repository\RankingRepository;
use Pokemon8\Repository\BanRepository;
use Pokemon8\Repository\UserRepository;
use Pokemon8\Security\BanGuard;
use Pokemon8\Security\Csrf;
use Pokemon8\Security\PasswordHasher;
use Pokemon8\Security\Session;
use Pokemon8\Support\Env;

define('APP_ROOT', dirname(__DIR__));

require APP_ROOT . '/src/Support/Autoload.php';

// Р вЂ”Р В°Р С–РЎР‚РЎС“Р В¶Р В°Р ВµР С Р С•Р С”РЎР‚РЎС“Р В¶Р ВµР Р…Р С‘Р Вµ Р С‘ Р С”Р С•Р Р…РЎвЂћР С‘Р С–Р С‘ Р Т‘Р С• РЎРѓР С•Р В·Р Т‘Р В°Р Р…Р С‘РЎРЏ РЎРѓР ВµРЎР‚Р Р†Р С‘РЎРѓР С•Р Р† Р С—РЎР‚Р С‘Р В»Р С•Р В¶Р ВµР Р…Р С‘РЎРЏ.
Env::load(APP_ROOT . '/.env');

$appConfig = require APP_ROOT . '/config/app.php';
$dbConfig = require APP_ROOT . '/config/database.php';

date_default_timezone_set($appConfig['timezone']);
ini_set('default_charset', 'UTF-8');
ini_set('display_errors', $appConfig['debug'] ? '1' : '0');
error_reporting(E_ALL);

$session = new Session($appConfig['session_name']);
$session->start();

// Р РЋР С•Р В±Р С‘РЎР‚Р В°Р ВµР С Р В·Р В°Р Р†Р С‘РЎРѓР С‘Р СР С•РЎРѓРЎвЂљР С‘ Р Р†РЎР‚РЎС“РЎвЂЎР Р…РЎС“РЎР‹. Р СџР С•Р В·Р В¶Р Вµ РЎРЊРЎвЂљР С• Р СР С•Р В¶Р Р…Р С• Р В·Р В°Р СР ВµР Р…Р С‘РЎвЂљРЎРЉ DI-Р С”Р С•Р Р…РЎвЂљР ВµР в„–Р Р…Р ВµРЎР‚Р С•Р С.
$db = Connection::make($dbConfig);
$users = new UserRepository($db);
$bans = new BanRepository($db);
$rankings = new RankingRepository($db);
$passwords = new PasswordHasher();
$csrf = new Csrf($session);
$banGuard = new BanGuard($bans);

$home = new HomeController($rankings, $session, $csrf);
$auth = new AuthController($users, $passwords, $session, $csrf, ['techwork' => $appConfig['techwork']]);
$game = new GameController($session);

$router = new Router();
$router->get('/', fn (Request $request) => $home->index($request));
$router->post('/login', fn (Request $request) => $auth->login($request));
$router->get('/logout', fn (Request $request) => $auth->logout($request));
$router->get('/game', fn (Request $request) => $game->start($request));

$request = Request::capture();
$banResponse = $banGuard->check($request);
if ($banResponse !== null) {
    $banResponse->send();
    exit;
}

$router->dispatch($request)->send();
