<?php
// post.map.php РІР‚вЂќ Р В°Р Т‘Р В°Р С—РЎвЂљР С‘РЎР‚Р С•Р Р†Р В°Р Р… Р С—Р С•Р Т‘ AJAX/SPA, PHP 5.4 + mysql_*

@session_start();

require_once __DIR__."/config.php";
require_once __DIR__."/db3.php";
require_once __DIR__."/ajax.helpers.php"; // is_ajax(), jexit(), resp_ok(), resp_err()

// Р вЂўРЎРѓР В»Р С‘ Р Р…РЎС“Р В¶Р Р…Р С•, Р С—Р С•Р Т‘Р С”Р В»РЎР‹РЎвЂЎР С‘ РЎС“РЎвЂљР С‘Р В»Р С”Р С‘/Р С”Р В»Р В°РЎРѓРЎРѓРЎвЂ№
// require_once __DIR__."/globfanction.php";
// require_once __DIR__."/fanction.games.php";

$db = db($config);
$isAjax = is_ajax();

// ===== Р В°Р Р†РЎвЂљР С•РЎР‚Р С‘Р В·Р В°РЎвЂ Р С‘РЎРЏ (Р С”Р В°Р С” Р Р† game.php) =====
if (empty($_SESSION['login']) || empty($_SESSION['password'])) {
    resp_err($isAjax, 'Р СњР ВµР В°Р Р†РЎвЂљР С•РЎР‚Р С‘Р В·Р С•Р Р†Р В°Р Р…');
}
$login = mysql_real_escape_string($_SESSION['login']);
$pass  = mysql_real_escape_string($_SESSION['password']);
$q = @mysql_query("SELECT * FROM users WHERE login='".$login."' AND password='".$pass."' AND activation=1 LIMIT 1");
if (!$q) resp_err($isAjax, 'DB error');
$myrow = mysql_fetch_assoc($q);
if (!$myrow || empty($myrow['id'])) resp_err($isAjax, 'Р СџР С•Р В»РЎРЉР В·Р С•Р Р†Р В°РЎвЂљР ВµР В»РЎРЉ Р Р…Р Вµ Р Р…Р В°Р в„–Р Т‘Р ВµР Р…');

$uid = (int)$myrow['id'];

// ===== 1) Р С›РЎвЂљР С—РЎР‚Р В°Р Р†Р С”Р В° РЎРѓР С•Р С•Р В±РЎвЂ°Р ВµР Р…Р С‘РЎРЏ Р Р† РЎвЂЎР В°РЎвЂљ (legacy: textchat / tochat / tipe / room / private) =====
if (isset($_POST['textchat'])) {
    $text = trim((string)$_POST['textchat']);
    if ($text === '') resp_err($isAjax, 'Р СџРЎС“РЎРѓРЎвЂљР С•Р Вµ РЎРѓР С•Р С•Р В±РЎвЂ°Р ВµР Р…Р С‘Р Вµ');

    // Р С›Р С—РЎР‚Р ВµР Т‘Р ВµР В»РЎРЏР ВµР С Р С—Р С•Р В»РЎС“РЎвЂЎР В°РЎвЂљР ВµР В»РЎРЏ (Р С—Р С• Р Р…Р С‘Р С”РЎС“), Р ВµРЎРѓР В»Р С‘ Р В·Р В°Р Т‘Р В°Р Р… РІР‚вЂќ РЎРЊРЎвЂљР С• Р С—РЎР‚Р С‘Р Р†Р В°РЎвЂљ
    $toLogin = isset($_POST['tochat']) ? trim((string)$_POST['tochat']) : '';
    $toId = 0; $isPrivate = 0;

    if ($toLogin !== '') {
        $toLoginEsc = mysql_real_escape_string($toLogin);
        $rq = @mysql_query("SELECT id FROM users WHERE login='".$toLoginEsc."' LIMIT 1");
        $ru = $rq ? mysql_fetch_assoc($rq) : null;
        if (!$ru) resp_err($isAjax, 'Р СџР С•Р В»РЎС“РЎвЂЎР В°РЎвЂљР ВµР В»РЎРЉ Р Р…Р Вµ Р Р…Р В°Р в„–Р Т‘Р ВµР Р…');
        $toId = (int)$ru['id'];
        if ($toId === $uid) resp_err($isAjax, 'Р СњР ВµР В»РЎРЉР В·РЎРЏ Р С—Р С‘РЎРѓР В°РЎвЂљРЎРЉ РЎРѓР ВµР В±Р Вµ');
        $isPrivate = 1;
    }

    // Р СџР С•Р В»РЎРЏ Р С—Р С• РЎвЂљР Р†Р С•Р ВµР в„– РЎвЂљР В°Р В±Р В»Р С‘РЎвЂ Р Вµ chats (Р С‘Р В· РЎвЂљР Р†Р С•Р ВµР С–Р С• Р С•Р С—Р С‘РЎРѓР В°Р Р…Р С‘РЎРЏ):
    // id, author (varchar), userto (int), private (int), time (int), text (blob), room (int), tipe (int)
    $author = mysql_real_escape_string($login);
    $room   = isset($myrow['buildmy']) ? (int)$myrow['buildmy'] : 1; // РЎвЂљР ВµР С”РЎС“РЎвЂ°Р В°РЎРЏ Р В»Р С•Р С”Р В°РЎвЂ Р С‘РЎРЏ Р С‘Р С–РЎР‚Р С•Р С”Р В°
    $tipe   = isset($_POST['tipe']) ? (int)$_POST['tipe'] : ( (int)$myrow['mychat']==2 ? 2 : 1 );

    // Р РЋР В°Р Р…Р С‘РЎвЂљР В°Р в„–Р В· РЎвЂљР ВµР С”РЎРѓРЎвЂљР В° Р С—РЎР‚Р С•РЎРѓРЎвЂљРЎвЂ№Р С addslashes (Р С—Р С•Р Т‘ РЎвЂљР Р†Р С•Р в„– php 5.4 + mysql_*)
    $textDB = addslashes($text);
    $sql = "INSERT INTO `chats` (author, userto, private, time, text, room, tipe)
            VALUES ('".$author."', ".$toId.", ".$isPrivate.", ".time().", '".$textDB."', ".$room.", ".$tipe.")";
    $ok = @mysql_query($sql);
    if (!$ok) resp_err($isAjax, 'Р С›РЎв‚¬Р С‘Р В±Р С”Р В° Р В·Р В°Р С—Р С‘РЎРѓР С‘ Р Р† РЎвЂЎР В°РЎвЂљ');

    // Р Р€РЎРѓР С—Р ВµРЎвЂ¦ РІР‚вЂќ Р Р† AJAX Р Р†Р ВµРЎР‚Р Р…РЎвЂР С ok, Р Р…Р В° РЎРѓРЎвЂљР В°РЎР‚Р С•Р С Р С—Р С•Р Р†Р ВµР Т‘Р ВµР Р…Р С‘Р С‘ Р С—РЎР‚Р С•РЎРѓРЎвЂљР С• Р’В«РЎвЂљР С‘РЎвЂ¦Р С•Р’В»
    if ($isAjax) jexit(array('ok'=>true));
    exit;
}

// ===== 2) Р СџРЎР‚Р С•РЎвЂЎР С‘Р Вµ POST-Р Т‘Р ВµР в„–РЎРѓРЎвЂљР Р†Р С‘РЎРЏ Р СР С‘РЎР‚Р В°, Р С”Р С•РЎвЂљР С•РЎР‚РЎвЂ№Р Вµ РЎР‚Р В°Р Р…РЎРЉРЎв‚¬Р Вµ Р С—Р С‘РЎРѓР В°Р В»Р С‘ Р Р† РЎвЂћРЎР‚Р ВµР в„–Р СРЎвЂ№ =====
// Р С›РЎРѓРЎвЂљР В°Р Р†РЎРЉ/Р Т‘Р С•Р В±Р В°Р Р†РЎРЉ РЎРѓР Р†Р С•Р С‘ Р В±Р В»Р С•Р С”Р С‘ Р Р…Р С‘Р В¶Р Вµ Р С—Р С• Р С•Р В±РЎР‚Р В°Р В·РЎвЂ РЎС“. Р вЂњР В»Р В°Р Р†Р Р…Р С•Р Вµ РІР‚вЂќ Р Р†Р СР ВµРЎРѓРЎвЂљР С• die("<script>...>")
// Р С‘РЎРѓР С—Р С•Р В»РЎРЉР В·РЎС“Р в„– resp_ok($isAjax,'/game.php?go=...') Р С‘Р В»Р С‘ resp_err($isAjax,'Р СћР ВµР С”РЎРѓРЎвЂљ').

// Р СџРЎР‚Р С‘Р СР ВµРЎР‚: Р С—Р ВµРЎР‚Р ВµР С”Р В»РЎР‹РЎвЂЎР ВµР Р…Р С‘Р Вµ PVE Р С”Р Р…Р С•Р С—Р С”Р С‘ РЎвЂЎР ВµРЎР‚Р ВµР В· POST (Р ВµРЎРѓР В»Р С‘ Р Р†Р Т‘РЎР‚РЎС“Р С– Р ВµРЎРѓРЎвЂљРЎРЉ POST-Р Р†Р ВµРЎвЂљР С”Р В°)
if (isset($_POST['pve_button'])) {
    $v = (int)$_POST['pve_button'] ? 1 : 0;
    $b_time = $v ? (time()+15) : 0;
    @mysql_query("UPDATE users SET pve_button=".$v.", atack_poke=".$b_time." WHERE id=".$uid." LIMIT 1");
    resp_ok($isAjax, null, 'OK');
}

// Р вЂўРЎРѓР В»Р С‘ Р Т‘Р ВµР в„–РЎРѓРЎвЂљР Р†Р С‘Р Вµ Р Р…Р Вµ РЎР‚Р В°РЎРѓР С—Р С•Р В·Р Р…Р В°Р Р…Р С•:
resp_err($isAjax, 'Р СњР ВµР С‘Р В·Р Р†Р ВµРЎРѓРЎвЂљР Р…Р С•Р Вµ Р Т‘Р ВµР в„–РЎРѓРЎвЂљР Р†Р С‘Р Вµ');
