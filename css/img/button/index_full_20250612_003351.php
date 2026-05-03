<?php
session_start();
include ("include/function/config.php");
include ("include/function/db3.php");
include ("include/function/globfanction.php");
include ("include/function/functionusers.php");
include ("include/class/index.class.php");
include ("ban.php");

$db = db($config); // ????????????? ??????????? ? ??

$autorize = false;
$myrow = [];

if (!empty($_SESSION['login']) && !empty($_SESSION['password'])) {
    $myrow = first('SELECT * FROM users WHERE login="%s" AND password="%s"', $_SESSION['login'], $_SESSION['password']);
    if ($myrow) $autorize = true;
}

$rang = select('SELECT id FROM users ORDER BY battles DESC LIMIT 5');
$maney = select('SELECT id, count FROM users ORDER BY count DESC LIMIT 5');
$dex_norm = select('SELECT id, count_poke FROM users ORDER BY count_poke DESC LIMIT 5');
$dex_shiny = select('SELECT id, count_poke_s FROM users ORDER BY count_poke_s DESC LIMIT 5');
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>League of Pokemons - Главная</title>
    <link rel="stylesheet" href="css/style0.css" media="all">
</head>
<body>
    <div id='bar'>
        <div id='autorizeDiv'>
        <?php if (!$autorize): ?>
            <form method="post" action="/autoriz.php">
                Логин: <input name="LOGIN" type="text">
                Пароль: <input name="PASSWORD" type="password">
                <input name="AUTO" type="checkbox"> Автовход
                <input type="submit" value="Вход">
                <a href="index.php?go=reg">Регистрация</a>
                <a href="index.php?go=sendpass">Восстановление пароля</a>
            </form>
        <?php else: ?>
            ???????????? ???, ??????: <?= htmlspecialchars($_SESSION['login']) ?> |
            <a href="/game" target="mir">? ???</a> |
            <a href="/game.php?go=start" target="game">В игру</a> |
            <a href="index.php?go=exits">Выход</a>
        <?php endif; ?>
        </div>
    </div>

    <main>
        <h2>Новости</h2>
        <ul>
            <li>12 ??? 2014 - ?????????? ?????????</li>
            <li>12 ??? 2014 - ????? (???? +250%, ??????? +250%)</li>
            <li>12 ??? 2014 - ???????? ???? (700+ ?????????)</li>
        </ul>

        <aside>
            <h4>??? ??????</h4>
            <ul><?php foreach ($rang as $i => $r) echo "<li>" . ($i+1) . ". ".color_group_users($r['id'],2)."</li>"; ?></ul>

            <h4>Миллионеры</h4>
            <ul><?php foreach ($maney as $i => $m) echo "<li>" . ($i+1) . ". ".color_group_users($m['id'],2)." ({$m['count']})</li>"; ?></ul>

            <h4>Top - Pokedex</h4>
            <ul><?php foreach ($dex_norm as $i => $d) echo "<li>" . ($i+1) . ". ".color_group_users($d['id'],2)." ({$d['count_poke']}/649)</li>"; ?></ul>

            <h4>Top - Shiny-dex</h4>
            <ul><?php foreach ($dex_shiny as $i => $s) echo "<li>" . ($i+1) . ". ".color_group_users($s['id'],2)." ({$s['count_poke_s']}/649)</li>"; ?></ul>
        </aside>
    </main>
</body>
</html>
