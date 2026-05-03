<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/log_php_errors.txt');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Р В±Р ВµР В·Р С•Р С—Р В°РЎРѓР Р…РЎвЂ№Р в„– Р Р†РЎвЂ№Р Р†Р С•Р Т‘
function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Р Р†РЎвЂ№РЎвЂ¦Р С•Р Т‘
if (isset($_GET['go']) && $_GET['go'] === 'exits') {

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'] ?? '/',
            $params['domain'] ?? '',
            (bool)($params['secure'] ?? false),
            (bool)($params['httponly'] ?? true)
        );
    }

    session_destroy();

    header('Location: /');
    exit;
}

// Р С—Р С•Р Т‘Р С”Р В»РЎР‹РЎвЂЎР ВµР Р…Р С‘Р Вµ
require_once __DIR__ . '/include/function/config.php';
require_once __DIR__ . '/include/function/db3.php';
require_once __DIR__ . '/include/function/globfanction.php';
require_once __DIR__ . '/include/function/functionusers.php';
require_once __DIR__ . '/include/class/index.class.php';
require_once __DIR__ . '/ban.php';

// Р Т‘Р В°Р Р…Р Р…РЎвЂ№Р Вµ
$ranking = new RankingsIndex();

$rang = $ranking->getTopRang();
$maney = $ranking->getTopMoney();
$dex_norm = $ranking->getTopDex();
$dex_shiny = $ranking->getTopShinyDex();

// Р С—Р С•Р В»РЎРЉР В·Р С•Р Р†Р В°РЎвЂљР ВµР В»РЎРЉ
$myrow = false;

if (!empty($_SESSION['login']) && !empty($_SESSION['password'])) {
    if (!empty($_SESSION['browse']) && $_SESSION['browse'] === getBrowserSign()) {

        $myrow = first(
            'SELECT id,login,activation,password,groups FROM users WHERE login="%s" AND password="%s" AND activation=1',
            (string)$_SESSION['login'],
            (string)$_SESSION['password']
        );

        $_SESSION['browse'] = getBrowserSign();
    }
}

// Р С”Р В°Р Р…Р С•Р Р…Р С‘РЎвЂЎР ВµРЎРѓР С”Р С‘Р в„– URL
$request_uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

if ($request_uri === '/index.php') {
    header('Location: /', true, 301);
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>League of Pokemons - Р вЂњР В»Р В°Р Р†Р Р…Р В°РЎРЏ</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link rel="stylesheet" href="/css/style0.css">
<link rel="stylesheet" href="/css/skin.css">

<script src="/script/jquery.js"></script>

<style>
body { min-width:1200px; overflow-x:auto; }
.wrapper { max-width:1200px; margin:auto; display:flex; }
#posts-list { flex:3; margin-right:20px; }
#sidebar { flex:1; min-width:300px; }
</style>

</head>
<body>

<div id="bar">
<div id="autorizeDiv">

<?php if (!$myrow): ?>

<form action="/autoriz.php" method="post">
Р вЂєР С•Р С–Р С‘Р Р…: <input name="LOGIN">
Р СџР В°РЎР‚Р С•Р В»РЎРЉ: <input type="password" name="PASSWORD">
<input type="submit" value="Р вЂ™РЎвЂ¦Р С•Р Т‘">
</form>

<?php else: ?>

Р СџРЎР‚Р С‘Р Р†Р ВµРЎвЂљ, <?= e((string)$_SESSION['login']) ?> |

<button onclick="window.open('/game.php?go=start')">Р вЂ™ Р С‘Р С–РЎР‚РЎС“</button>
<button onclick="location.href='/?go=exits'">Р вЂ™РЎвЂ№РЎвЂ¦Р С•Р Т‘</button>

<?php endif; ?>

</div>
</div>

<header>
<div class="wrapper">
<a href="/"><img src="/img/lop.png"></a>
</div>
</header>

<div class="wrapper">

<div id="posts-list">
<h2>Р СњР С•Р Р†Р С•РЎРѓРЎвЂљР С‘</h2>

<div>Р вЂќР С•Р В±РЎР‚Р С• Р С—Р С•Р В¶Р В°Р В»Р С•Р Р†Р В°РЎвЂљРЎРЉ Р Р† Р С‘Р С–РЎР‚РЎС“!</div>

</div>

<div id="sidebar">

<h3>Р СћР С•Р С— Р С‘Р С–РЎР‚Р С•Р С”Р С‘</h3>

<?php if (!empty($rang)): ?>
<?php foreach ($rang as $i => $r): ?>
<div>
<?= $i+1 ?>.
<?= color_group_users($r['id'],2) ?>
(<?= number_format((int)$r['rang_b']) ?>)
</div>
<?php endforeach; ?>
<?php endif; ?>

</div>

</div>

<footer>
<center>League of Pokemons Р’В© 2014</center>
</footer>

</body>
</html>