<?php
declare(strict_types=1);

require_once __DIR__ . '/include/function/config.php';
require_once __DIR__ . '/include/function/db3.php';
require_once __DIR__ . '/include/function/globfanction.php';
require_once __DIR__ . '/include/function/functionusers.php';
require_once __DIR__ . '/include/class/index.class.php';
require_once __DIR__ . '/ban.php';

app_start_session();

if (($_GET['go'] ?? '') === 'exits') {
    app_destroy_session();
    app_redirect('/');
}

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if ($requestPath === '/index.php') {
    app_redirect('/', 301);
}

$ranking = new RankingsIndex();
$rang = $ranking->getTopRang();
$maney = $ranking->getTopMoney();
$dexNorm = $ranking->getTopDex();
$dexShiny = $ranking->getTopShinyDex();

$myrow = false;
if (!empty($_SESSION['login']) && !empty($_SESSION['password']) && !empty($_SESSION['browse'])) {
    if ($_SESSION['browse'] === getBrowserSign()) {
        $myrow = first(
            'SELECT id,login,activation,password,groups FROM users WHERE login="%s" AND password="%s" AND activation=1',
            (string) $_SESSION['login'],
            (string) $_SESSION['password']
        );
        $_SESSION['browse'] = getBrowserSign();
    }
}

$news = [
    [
        'date' => '12 декабря, 2014',
        'title' => 'Начало новой эры в мире покемонов!',
        'text' => 'Уважаемые тренеры! Мы рады приветствовать вас на просторах онлайн игры "League Of Pokemons"! Здесь вы сможете ловить покемонов, сражаться с дикими покемонами и другими тренерами, путешествовать по огромному миру, выполнять квесты, общаться, торговать, создавать кланы и многое другое. Игра постоянно развивается, и мы надеемся, что она станет вашим любимым развлечением!',
    ],
    [
        'date' => '12 декабря, 2014',
        'title' => 'Новые обновления',
        'text' => 'В игру добавлены новые покемоны, предметы, а также улучшена система боев. Теперь сражаться стало еще интереснее!',
    ],
    [
        'date' => '12 декабря, 2014',
        'title' => 'Добро пожаловать в мир League Of Pokemons!',
        'text' => 'League Of Pokemons - это уникальная браузерная онлайн игра, основанная на вселенной покемонов. Здесь вы можете ловить, тренировать и развивать своих покемонов, сражаться с другими игроками, исследовать огромный мир и выполнять увлекательные квесты. Присоединяйтесь к нам и станьте лучшим тренером!',
    ],
];

function render_rank_list(array $items, string $scoreKey, string $emptyText = 'Нет данных'): void
{
    if ($items === []) {
        echo '<div class="muted">' . app_e($emptyText) . '</div>';
        return;
    }

    $position = 1;
    foreach ($items as $item) {
        if (!isset($item['id'], $item[$scoreKey])) {
            continue;
        }

        $user = color_group_users((int) $item['id'], 2);
        $score = number_format((int) $item[$scoreKey], 0, '.', ' ');
        echo '<div><span class="rank-position">' . $position . '.</span> ' . $user . ' <span class="muted">(' . app_e($score) . ')</span></div>';
        $position++;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>League of Pokemons - Главная страница</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="/">
    <link rel="stylesheet" href="/css/style0.css">
    <link rel="stylesheet" href="/css/skin.css">
    <link rel="stylesheet" media="screen" href="/css/superfish.css">
    <script src="/script/jquery.js"></script>
    <style>
        body {
            min-width: 1200px;
            overflow-x: auto;
        }
        #main .wrapper {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
        }
        #posts-list {
            flex: 3;
            margin-right: 20px;
        }
        #sidebar {
            flex: 1;
            min-width: 300px;
        }
        #nav.sf-menu {
            margin: 0;
            padding: 0;
            list-style: none;
            display: flex;
            gap: 20px;
            align-items: center;
            flex-wrap: nowrap;
        }
        #nav.sf-menu > li {
            display: inline-block;
            margin: 0;
            padding: 0;
        }
        #nav.sf-menu > li > a {
            display: inline-block;
            white-space: nowrap;
            line-height: 32px;
        }
        header .wrapper {
            display: flex;
            align-items: center;
            gap: 18px;
        }
        header nav {
            flex: 1 1 auto;
        }
        #logo img {
            display: block;
            height: auto;
        }
        .rank-position {
            color: #cc66ff;
        }
        .muted {
            color: #aaa;
        }
    </style>
</head>
<body>
<div id="bar">
    <div id="autorizeDiv">
        <?php if (!$myrow): ?>
            <form name="formAut" id="formAut" action="/autoriz.php" method="post">
                Логин: <input id="login" class="formAut" name="LOGIN" type="text" size="16" maxlength="16" value="" autocomplete="username">
                Пароль: <input id="pass" class="formAut" name="PASSWORD" type="password" size="16" maxlength="40" value="" autocomplete="current-password">
                <input id="autoChec" class="checAut" name="AUTO" type="checkbox"> Запомнить меня
                <input type="submit" class="btInp" value="Войти">
                | <button type="button" class="btInp" onclick="location.href='index.php?go=reg';">Регистрация</button>
                | <button type="button" class="btInp" onclick="location.href='index.php?go=sendpass';">Восстановление пароля</button>
            </form>
        <?php else: ?>
            Приветствую, игрок: <?= app_e($_SESSION['login']) ?>!
            | <button type="button" class="btInp" onclick="window.open('/game','mir');">В игру</button>
            | <button type="button" class="btInp" onclick="window.open('/game','game');">В начало</button>
            | <button type="button" class="btInp" onclick="location.href='/?go=exits';">Выход</button>
        <?php endif; ?>
    </div>
    <div id="autMes" style="display:none;"></div>
    <div id="autherror" style="display:none;"></div>
</div>

<header class="clearfix">
    <div class="wrapper">
        <a href="/" id="logo"><img src="/img/lop.png" alt="League of Pokemons"></a>
        <nav>
            <ul id="nav" class="sf-menu">
                <li class="current-menu-item"><a href="/">Главная</a></li>
                <li><a href="/rules">Правила</a></li>
                <li><a href="/about">О нас</a></li>
                <li><a href="index.php?go=reg">Регистрация</a></li>
                <li><a href="http://forum.league-of-pokemons.ru/" target="_blank" rel="noopener">Форум</a></li>
                <li><a href="/vk-group">Группа ВКонтакте</a></li>
            </ul>
        </nav>
        <div id="combo-holder"></div>
    </div>
</header>

<div id="main">
    <div class="wrapper clearfix">
        <div id="posts-list">
            <h2 class="page-heading"><span>News</span></h2>

            <?php foreach ($news as $item): ?>
                <article class="format-standard">
                    <div class="entry-date">
                        <div class="number"><?= app_e(explode(' ', $item['date'])[0]) ?></div>
                        <div class="year"><?= app_e(substr($item['date'], 3)) ?></div>
                    </div>
                    <div class="feature-image"></div>
                    <h2 class="post-heading"><?= app_e($item['title']) ?></h2>
                    <div class="excerpt"><?= app_e($item['text']) ?></div>
                    <div class="meta">
                        <div class="user"><a href="/game/trainers?id=1" style="color:grey;">Tacos</a></div>
                    </div>
                </article>
            <?php endforeach; ?>

            <div class="page-navigation clearfix"></div>
        </div>

        <aside id="sidebar">
            <ul>
                <li class="block">
                    <h4><img src="/img/prize/1nagrada.png" style="vertical-align:middle;" alt=""> <span class="rank-position">Топ игроков:</span></h4>
                    <b><?php render_rank_list(is_array($rang) ? $rang : [], 'rang_b'); ?></b>
                </li>

                <li class="block">
                    <h4><span class="rank-position">Топ богачей:</span></h4>
                    <b><?php render_rank_list(is_array($maney) ? $maney : [], 'count'); ?></b>
                </li>

                <li class="block">
                    <h4><span class="rank-position">Top - Pokedex:</span></h4>
                    <b><?php render_rank_list(is_array($dexNorm) ? $dexNorm : [], 'count_poke'); ?></b>
                </li>

                <li class="block">
                    <h4><span class="rank-position">Top - Shiny-dex:</span></h4>
                    <b><?php render_rank_list(is_array($dexShiny) ? $dexShiny : [], 'count_poke_s'); ?></b>
                </li>
            </ul>
            <em id="corner"></em>
        </aside>
    </div>
</div>

<footer>
    <div class="wrapper">
        <ul class="widget-cols clearfix">
            <li>
                <div class="widget-block">
                    <h4>О нас:</h4>
                    <div class="recent-post">
                        <h4>Наши контакты:</h4>
                        <h4>Разработчики игры:</h4>
                    </div>
                </div>
            </li>
        </ul>

        <div class="footer-bottom">
            <center>League of Pokemons © 2014</center>
            <center><b>Все названия и персонажи являются торговыми марками компании «Nintendo» © 1996 - 2014</b></center>
            <div class="right">
                <ul id="social-bar"></ul>
            </div>
        </div>
    </div>
</footer>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[onclick*="exits"]').forEach(function(button) {
        button.onclick = function() {
            localStorage.clear();
            sessionStorage.clear();
            location.href = '/?go=exits';
        };
    });
});
</script>
</body>
</html>
