<?php
declare(strict_types=1);

require_once __DIR__ . '/include/function/config.php';
require_once __DIR__ . '/app/security_headers.php';
app_send_security_headers();
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
            'SELECT id,login,activation,password,`groups` FROM users WHERE login="%s" AND password="%s" AND activation=1',
            (string) $_SESSION['login'],
            (string) $_SESSION['password']
        );
        $_SESSION['browse'] = getBrowserSign();
    }
}

$news = [];
$newsMonths = [1 => 'января', 2 => 'февраля', 3 => 'марта', 4 => 'апреля', 5 => 'мая', 6 => 'июня', 7 => 'июля', 8 => 'августа', 9 => 'сентября', 10 => 'октября', 11 => 'ноября', 12 => 'декабря'];
try {
    $newsRows = select('SELECT subject, news, date FROM news ORDER BY date DESC, id ASC LIMIT 6') ?: [];
    foreach ($newsRows as $row) {
        $text = trim((string) ($row['news'] ?? ''));
        if ($text === '') {
            continue;
        }
        $stamp = !empty($row['date']) ? strtotime((string) $row['date']) : false;
        $dateText = $stamp
            ? date('j', $stamp) . ' ' . $newsMonths[(int) date('n', $stamp)] . ' ' . date('Y', $stamp)
            : 'Без даты';
        $title = trim((string) ($row['subject'] ?? ''));
        $news[] = [
            'date' => $dateText,
            'title' => $title !== '' ? $title : 'Новость проекта',
            'text' => $text,
        ];
    }
} catch (Throwable $e) {
    $news = [];
}
if ($news === []) {
    $news = [[
        'date' => '25 октября 2026',
        'title' => 'Новости уже здесь',
        'text' => 'Свежие новости проекта появятся на этой странице совсем скоро.',
    ]];
}

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
    <?php /* Лендинг — первая страница, которую видит игрок: подключаем общий базовый слой
             (токены, сброс, self-hosted Inter), чтобы он не жил в отдельной палитре. */ ?>
    <link rel="stylesheet" href="/public/css/tokens.css?v=<?= (int) @filemtime(__DIR__ . '/public/css/tokens.css') ?>">
    <link rel="stylesheet" href="/public/css/base.css?v=<?= (int) @filemtime(__DIR__ . '/public/css/base.css') ?>">
    <link rel="stylesheet" href="/public/css/fonts.css?v=<?= (int) @filemtime(__DIR__ . '/public/css/fonts.css') ?>">
    <link rel="stylesheet" href="/public/css/landing.css?v=<?= (int) @filemtime(__DIR__ . '/public/css/landing.css') ?>">
</head>
<body class="landing">
    <div class="page">
        <header class="topbar">
            <?php /* Бренд один: «League of Pokemons» (план §9, F-29). Подзаголовок
                     «PokemonChic» дублировал название рядом с логотипом-вордмарком. */ ?>
            <a class="brand" href="/" aria-label="League of Pokemons — на главную">
                <img src="/img/lop.png" alt="League of Pokemons">
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
                                    <input name="LOGIN" maxlength="16" autocomplete="username" placeholder="Логин" aria-label="Логин" required>
                                    <input name="PASSWORD" type="password" maxlength="72" autocomplete="current-password" placeholder="Пароль" aria-label="Пароль" required>
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
