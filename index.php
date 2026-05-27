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

if (($_GET['go'] ?? '') === 'reg') {
    app_redirect('/register', 301);
}

if (($_GET['go'] ?? '') === 'sendpass') {
    app_redirect('/password/forgot', 301);
}

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if ($requestPath === '/index.php') {
    app_redirect('/', 301);
}

$ranking = new RankingsIndex();
$rang = $ranking->getTopRang();
$money = $ranking->getTopMoney();
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
        'date' => '3 мая 2026',
        'title' => 'Новая навигация игрового мира',
        'text' => 'Главный вход в игру переведен на новый маршрут /game. Старые переходы game.php?go=... постепенно уходят из интерфейса, а логика переносится в сервисы и JSON API.',
    ],
    [
        'date' => '3 мая 2026',
        'title' => 'Обновление главной страницы',
        'text' => 'Главная страница теперь сохранена в нормальном UTF-8, без битых символов. Интерфейс стал чище: быстрый вход, новости проекта и рейтинги видны сразу.',
    ],
    [
        'date' => '2 мая 2026',
        'title' => 'Переписываем проект без костылей',
        'text' => 'Новая версия строится вокруг front controller, контроллеров, репозиториев, CSRF-защиты и отдельных игровых сервисов. Legacy-файлы остаются только как источник бизнес-логики на время переноса.',
    ],
];

if (empty($_SESSION['_csrf']) || !is_string($_SESSION['_csrf'])) {
    $_SESSION['_csrf'] = bin2hex(random_bytes(32));
}
$csrfToken = (string) $_SESSION['_csrf'];

function render_rank_list(array $items, string $scoreKey, string $emptyText = 'Нет данных'): void
{
    if ($items === []) {
        echo '<div class="empty-state">' . app_e($emptyText) . '</div>';
        return;
    }

    $position = 1;
    foreach ($items as $item) {
        if (!isset($item['id'], $item[$scoreKey])) {
            continue;
        }

        $score = number_format((int) $item[$scoreKey], 0, '.', ' ');
        echo '<div class="rank-row">';
        echo '<span class="rank-name"><b>' . $position . '.</b> ' . color_group_users((int) $item['id'], 2) . '</span>';
        echo '<span class="rank-score">' . app_e($score) . '</span>';
        echo '</div>';
        $position++;
    }

    if ($position === 1) {
        echo '<div class="empty-state">' . app_e($emptyText) . '</div>';
    }
}
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="League of Pokemons - браузерная онлайн-игра про тренеров, покемонов, бои и путешествия.">
    <title>League of Pokemons - Главная</title>
    <base href="/">
    <style>
        :root {
            --bg: #f3f6f8;
            --surface: #ffffff;
            --surface-2: #eef4fb;
            --line: #d6e0ea;
            --text: #182234;
            --muted: #637086;
            --blue: #1d66c2;
            --green: #16845f;
            --amber: #a86b05;
            --red: #b3261e;
            --shadow: 0 18px 50px rgba(28, 44, 68, .12);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            background:
                radial-gradient(circle at 12% 12%, rgba(44, 132, 207, .14), transparent 28%),
                linear-gradient(135deg, #f7fbff 0%, #edf3f4 48%, #f8f1e7 100%);
            color: var(--text);
            font: 15px/1.5 Arial, Tahoma, sans-serif;
        }

        a { color: var(--blue); text-decoration: none; }
        a:hover { text-decoration: underline; }

        .page {
            width: min(1180px, calc(100% - 32px));
            margin: 0 auto;
            padding: 24px 0 36px;
        }

        .topbar,
        .hero,
        .panel,
        .news-card {
            background: rgba(255, 255, 255, .92);
            border: 1px solid var(--line);
            border-radius: 8px;
            box-shadow: var(--shadow);
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            padding: 14px 16px;
            position: sticky;
            top: 0;
            z-index: 10;
            backdrop-filter: blur(14px);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 220px;
        }

        .brand img {
            width: 54px;
            height: 54px;
            object-fit: contain;
        }

        .brand-title {
            font-size: 19px;
            font-weight: 800;
            letter-spacing: 0;
        }

        .brand-subtitle {
            color: var(--muted);
            font-size: 13px;
        }

        .nav {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        .nav a,
        .button,
        .login button {
            min-height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--line);
            border-radius: 6px;
            background: #fff;
            color: #174f91;
            padding: 0 14px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            white-space: nowrap;
        }

        .button-primary {
            border-color: #1558ab;
            background: #1d66c2;
            color: #fff;
        }

        .button-green {
            border-color: #147552;
            background: #16845f;
            color: #fff;
        }

        .hero {
            margin-top: 18px;
            overflow: hidden;
        }

        .hero-inner {
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) minmax(340px, .8fr);
            gap: 24px;
            padding: 28px;
            align-items: stretch;
        }

        .hero-copy {
            min-height: 330px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        h1 {
            margin: 0;
            font-size: clamp(34px, 5vw, 60px);
            line-height: 1;
            letter-spacing: 0;
        }

        .lead {
            max-width: 680px;
            margin: 18px 0 0;
            color: #344154;
            font-size: 18px;
        }

        .hero-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 24px;
        }

        .login-panel {
            background:
                linear-gradient(180deg, rgba(255,255,255,.96), rgba(244,249,255,.96)),
                url('/img/room/001.png');
            background-size: cover;
            background-position: center bottom;
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 18px;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            min-height: 330px;
            position: relative;
            overflow: hidden;
        }

        .login-panel::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(255,255,255,.18), rgba(255,255,255,.94) 58%);
        }

        .login-content {
            position: relative;
            z-index: 1;
        }

        .login-title {
            margin: 0 0 12px;
            font-size: 22px;
            font-weight: 800;
        }

        .login {
            display: grid;
            gap: 10px;
        }

        .login input {
            width: 100%;
            height: 42px;
            border: 1px solid var(--line);
            border-radius: 6px;
            padding: 0 12px;
            font: inherit;
            background: #fff;
        }

        .login-extra {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 12px;
            font-size: 13px;
        }

        .content-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 360px;
            gap: 18px;
            margin-top: 18px;
            align-items: start;
        }

        .section-title {
            margin: 0 0 14px;
            font-size: 22px;
        }

        .news-list {
            display: grid;
            gap: 12px;
        }

        .news-card {
            padding: 18px;
            box-shadow: none;
        }

        .news-date {
            color: var(--amber);
            font-weight: 800;
            font-size: 13px;
            text-transform: uppercase;
        }

        .news-card h3 {
            margin: 6px 0 8px;
            font-size: 20px;
        }

        .news-card p {
            margin: 0;
            color: #3c4758;
        }

        .panel {
            padding: 18px;
            box-shadow: none;
        }

        .rank-block + .rank-block {
            margin-top: 18px;
            padding-top: 18px;
            border-top: 1px solid var(--line);
        }

        .rank-block h3 {
            margin: 0 0 10px;
            font-size: 17px;
        }

        .rank-row {
            min-height: 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            border-bottom: 1px solid #edf2f7;
            padding: 5px 0;
        }

        .rank-name {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .rank-score {
            color: var(--green);
            font-weight: 800;
            flex: 0 0 auto;
        }

        .empty-state {
            color: var(--muted);
            padding: 8px 0;
        }

        footer {
            margin-top: 18px;
            color: var(--muted);
            font-size: 13px;
            text-align: center;
        }

        @media (max-width: 980px) {
            .topbar,
            .hero-inner,
            .content-grid {
                grid-template-columns: 1fr;
            }

            .topbar {
                display: grid;
            }

            .hero-inner {
                padding: 18px;
            }

            .hero-copy,
            .login-panel {
                min-height: auto;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <header class="topbar">
            <a class="brand" href="/" aria-label="League of Pokemons">
                <img src="/img/lop.png" alt="">
                <span>
                    <span class="brand-title">League of Pokemons</span>
                    <span class="brand-subtitle">PokemonChic</span>
                </span>
            </a>

            <nav class="nav" aria-label="Главное меню">
                <a href="/">Главная</a>
                <a href="/game">Игра</a>
                <a href="/game/pokedex">Покедекс</a>
                <a href="/game/trainers">Тренеры</a>
                <a href="/game/quests">Квесты</a>
            </nav>
        </header>

        <main>
            <section class="hero">
                <div class="hero-inner">
                    <div class="hero-copy">
                        <h1>League of Pokemons</h1>
                        <p class="lead">
                            Браузерная онлайн-игра про тренеров, покемонов, путешествия, квесты и бои.
                            Сейчас проект переводится на новую архитектуру: быстрее, чище и без битых кодировок.
                        </p>
                        <div class="hero-actions">
                            <?php if ($myrow): ?>
                                <a class="button button-primary" href="/game">Продолжить игру</a>
                                <a class="button" href="/game/profile">Профиль</a>
                                <a class="button" href="/?go=exits">Выйти</a>
                            <?php else: ?>
                                <a class="button button-primary" href="#login">Войти</a>
                                <a class="button button-green" href="/register">Регистрация</a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <aside class="login-panel" id="login">
                        <div class="login-content">
                            <?php if ($myrow): ?>
                                <h2 class="login-title">С возвращением, <?= app_e((string) ($_SESSION['login'] ?? 'тренер')) ?></h2>
                                <a class="button button-primary" href="/game">Открыть игровой мир</a>
                            <?php else: ?>
                                <h2 class="login-title">Вход в игру</h2>
                                <form class="login" action="/login" method="post">
                                    <input type="hidden" name="_csrf" value="<?= app_e($csrfToken) ?>">
                                    <input name="LOGIN" maxlength="16" autocomplete="username" placeholder="Логин" required>
                                    <input name="PASSWORD" type="password" maxlength="72" autocomplete="current-password" placeholder="Пароль" required>
                                    <label><input name="AUTO" type="checkbox"> Запомнить меня</label>
                                    <button class="button-primary" type="submit">Войти</button>
                                </form>
                                <div class="login-extra">
                                    <a href="/register">Создать аккаунт</a>
                                    <a href="/password/forgot">Восстановить пароль</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </aside>
                </div>
            </section>

            <div class="content-grid">
                <section class="panel">
                    <h2 class="section-title">Новости проекта</h2>
                    <div class="news-list">
                        <?php foreach ($news as $item): ?>
                            <article class="news-card">
                                <div class="news-date"><?= app_e($item['date']) ?></div>
                                <h3><?= app_e($item['title']) ?></h3>
                                <p><?= app_e($item['text']) ?></p>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>

                <aside class="panel">
                    <h2 class="section-title">Рейтинги</h2>

                    <section class="rank-block">
                        <h3>Топ тренеров</h3>
                        <?php render_rank_list(is_array($rang) ? $rang : [], 'rang_b'); ?>
                    </section>

                    <section class="rank-block">
                        <h3>Топ богачей</h3>
                        <?php render_rank_list(is_array($money) ? $money : [], 'count'); ?>
                    </section>

                    <section class="rank-block">
                        <h3>Покедекс</h3>
                        <?php render_rank_list(is_array($dexNorm) ? $dexNorm : [], 'count_poke'); ?>
                    </section>

                    <section class="rank-block">
                        <h3>Shiny-dex</h3>
                        <?php render_rank_list(is_array($dexShiny) ? $dexShiny : [], 'count_poke_s'); ?>
                    </section>
                </aside>
            </div>
        </main>

        <footer>
            League of Pokemons © 2014-2026. Все названия и персонажи Pokémon являются товарными знаками Nintendo, Creatures Inc. и Game Freak.
        </footer>
    </div>
</body>
</html>
