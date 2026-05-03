<?php
// get.map.php — адаптирован под AJAX/SPA, PHP 5.4 + mysql_*

@session_start();

require_once __DIR__."/config.php";
require_once __DIR__."/db3.php";
require_once __DIR__."/ajax.helpers.php"; // is_ajax(), jexit(), resp_ok(), resp_err()

// require_once __DIR__."/globfanction.php";
// require_once __DIR__."/fanction.games.php";

$db = db($config);
$isAjax = is_ajax();

// ===== авторизация =====
if (empty($_SESSION['login']) || empty($_SESSION['password'])) {
    resp_err($isAjax, 'Неавторизован');
}
$login = mysql_real_escape_string($_SESSION['login']);
$pass  = mysql_real_escape_string($_SESSION['password']);
$q = @mysql_query("SELECT * FROM users WHERE login='".$login."' AND password='".$pass."' AND activation=1 LIMIT 1");
if (!$q) resp_err($isAjax, 'DB error');
$myrow = mysql_fetch_assoc($q);
if (!$myrow || empty($myrow['id'])) resp_err($isAjax, 'Пользователь не найден');

$uid = (int)$myrow['id'];

// ===== 1) Переход на локацию (в одном окне) =====
// Подстрой условие под твой реальный параметр (я не меняю названия, просто пример):
if (isset($_GET['location'])) {
    $loc = (int)$_GET['location'];
    // TODO: проверки доступности локации, стоимости, дистанции и т.д.
    @mysql_query("UPDATE users SET buildmy=".$loc." WHERE id=".$uid." LIMIT 1");
    // Возвращаем редирект БЕЗ новых окон/фреймов — в том же окне
    resp_ok($isAjax, '/game');
}

// ===== 2) Предложение обмена (legacy: ?gets=true&trade=true&to=НИК) =====
if (!empty($_GET['gets']) && !empty($_GET['trade']) && !empty($_GET['to'])) {
    $toLogin = trim((string)$_GET['to']);
    if ($toLogin==='') resp_err($isAjax, 'Кому обмен?');

    // Здесь твои проверки (онлайн, локация и пр.), создание заявки/сделки если надо…

    // А дальше — единый возврат без фреймов:
    resp_ok($isAjax, '/game.php?go=char&trade=true&to='.urlencode($toLogin));
}

// ===== 3) Просмотр боёв на локации (legacy: ?gets=true&view_battle=true) =====
if (!empty($_GET['gets']) && !empty($_GET['view_battle'])) {
    // Подготовка списка боёв (если есть) — у тебя это внутри map.world.php/отдельной страницы
    resp_ok($isAjax, '/game&view_battle=1');
}

// ===== 4) Любые другие «быстрые» GET-команды перенаправляй в один окно =====
// Пример: переключение PVE через GET (если у тебя было ?napadenie=on|off)
if (isset($_GET['napadenie'])) {
    $go2 = $_GET['napadenie'];
    if ($go2 === 'on') {
        $b_time = time() + 15;
        @mysql_query("UPDATE users SET pve_button=1, atack_poke=".$b_time." WHERE id=".$uid." LIMIT 1");
    } else {
        @mysql_query("UPDATE users SET pve_button=0, atack_poke=0 WHERE id=".$uid." LIMIT 1");
    }
    resp_ok($isAjax, '/game');
}

// Если ничего не подошло — ответ по умолчанию
resp_err($isAjax, 'Неизвестная команда');
