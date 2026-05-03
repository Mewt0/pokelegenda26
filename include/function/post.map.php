<?php
// post.map.php — адаптирован под AJAX/SPA, PHP 5.4 + mysql_*

@session_start();

require_once __DIR__."/config.php";
require_once __DIR__."/db3.php";
require_once __DIR__."/ajax.helpers.php"; // is_ajax(), jexit(), resp_ok(), resp_err()

// Если нужно, подключи утилки/классы
// require_once __DIR__."/globfanction.php";
// require_once __DIR__."/fanction.games.php";

$db = db($config);
$isAjax = is_ajax();

// ===== авторизация (как в game.php) =====
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

// ===== 1) Отправка сообщения в чат (legacy: textchat / tochat / tipe / room / private) =====
if (isset($_POST['textchat'])) {
    $text = trim((string)$_POST['textchat']);
    if ($text === '') resp_err($isAjax, 'Пустое сообщение');

    // Определяем получателя (по нику), если задан — это приват
    $toLogin = isset($_POST['tochat']) ? trim((string)$_POST['tochat']) : '';
    $toId = 0; $isPrivate = 0;

    if ($toLogin !== '') {
        $toLoginEsc = mysql_real_escape_string($toLogin);
        $rq = @mysql_query("SELECT id FROM users WHERE login='".$toLoginEsc."' LIMIT 1");
        $ru = $rq ? mysql_fetch_assoc($rq) : null;
        if (!$ru) resp_err($isAjax, 'Получатель не найден');
        $toId = (int)$ru['id'];
        if ($toId === $uid) resp_err($isAjax, 'Нельзя писать себе');
        $isPrivate = 1;
    }

    // Поля по твоей таблице chats (из твоего описания):
    // id, author (varchar), userto (int), private (int), time (int), text (blob), room (int), tipe (int)
    $author = mysql_real_escape_string($login);
    $room   = isset($myrow['buildmy']) ? (int)$myrow['buildmy'] : 1; // текущая локация игрока
    $tipe   = isset($_POST['tipe']) ? (int)$_POST['tipe'] : ( (int)$myrow['mychat']==2 ? 2 : 1 );

    // Санитайз текста простым addslashes (под твой php 5.4 + mysql_*)
    $textDB = addslashes($text);
    $sql = "INSERT INTO `chats` (author, userto, private, time, text, room, tipe)
            VALUES ('".$author."', ".$toId.", ".$isPrivate.", ".time().", '".$textDB."', ".$room.", ".$tipe.")";
    $ok = @mysql_query($sql);
    if (!$ok) resp_err($isAjax, 'Ошибка записи в чат');

    // Успех — в AJAX вернём ok, на старом поведении просто «тихо»
    if ($isAjax) jexit(array('ok'=>true));
    exit;
}

// ===== 2) Прочие POST-действия мира, которые раньше писали в фреймы =====
// Оставь/добавь свои блоки ниже по образцу. Главное — вместо die("<script>...>")
// используй resp_ok($isAjax,'/game.php?go=...') или resp_err($isAjax,'Текст').

// Пример: переключение PVE кнопки через POST (если вдруг есть POST-ветка)
if (isset($_POST['pve_button'])) {
    $v = (int)$_POST['pve_button'] ? 1 : 0;
    $b_time = $v ? (time()+15) : 0;
    @mysql_query("UPDATE users SET pve_button=".$v.", atack_poke=".$b_time." WHERE id=".$uid." LIMIT 1");
    resp_ok($isAjax, null, 'OK');
}

// Если действие не распознано:
resp_err($isAjax, 'Неизвестное действие');
