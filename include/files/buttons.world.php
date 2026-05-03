<?php
// include/files/buttons.world.php — панель действий (сохраняет старый стиль)
// PHP 5.4, без переименования переменных/полей — только исправление битых символов

@session_start();

// Тянем конфиг/БД как в game.php (без дублей)
if (!function_exists('db')) {
    require_once $_SERVER['DOCUMENT_ROOT'].'/include/function/config.php';
    require_once $_SERVER['DOCUMENT_ROOT'].'/include/function/db3.php';
}
if (!function_exists('first')) {
    $glob = $_SERVER['DOCUMENT_ROOT'].'/include/function/globfanction.php';
    if (file_exists($glob)) require_once $glob;
}
if (!isset($db) && isset($config)) { $db = db($config); }

// Текущий пользователь (если $myrow не передан)
if (empty($myrow) || empty($myrow['id'])) {
    if (!empty($_SESSION['login']) && !empty($_SESSION['password'])) {
        $login = mysql_real_escape_string($_SESSION['login']);
        $pass  = mysql_real_escape_string($_SESSION['password']);
        $qr = @mysql_query("SELECT * FROM users WHERE login='".$login."' AND password='".$pass."' AND activation=1 LIMIT 1");
        if ($qr) $myrow = mysql_fetch_assoc($qr);
    }
}
$uid = !empty($myrow['id']) ? (int)$myrow['id'] : 0;

// Вспомогательная редирект-функция (работает даже если заголовки уже отосланы)
function __redir($url) {
    if (!headers_sent()) {
        header('Location: '.$url);
    } else {
        echo '<script>location.href='.json_encode($url).';</script>';
    }
    exit;
}

// ===== Обработка переключения режима чата и нападения =====
if ($uid > 0 && !empty($_GET['gochat'])) {
    $val = ($_GET['gochat'] === 'on') ? 2 : 1; // 2 — локалка, 1 — общий
    update('users', array('mychat'=>$val), 'id='.$uid);
    __redir('/game');
}

if ($uid > 0 && !empty($_GET['napadenie'])) {
    if ($_GET['napadenie'] === 'on') {
        $b_time = time() + 15;
        update('users', array('pve_button'=>1,'atack_poke'=>$b_time), 'id='.$uid);
    } else {
        update('users', array('pve_button'=>0), 'id='.$uid);
    }
    __redir('/game');
}

// ===== Текущие значения для статуса кнопок =====
$IS_PVE            = (!empty($myrow['pve_button']) && (int)$myrow['pve_button'] === 1);
$CHAT_SCOPE_IS_ROOM= (!empty($myrow['mychat']) && (int)$myrow['mychat'] === 2);
$IS_GROUP_123      = (!empty($myrow['groups']) && in_array((int)$myrow['groups'], array(1,2,3)));
$IS_GROUP_14       = (!empty($myrow['groups']) && in_array((int)$myrow['groups'], array(1,4)));

?>
<!-- ======= Панель действий — редактируется вручную с чатом ======= -->
<style type="text/css">
  .ui-bar {
    position: fixed; left:0; right:0; bottom:0;
    min-height: 44px; background:#f5f7fb; border-top:1px solid #cfd6df;
    display:flex; align-items:center; gap:8px; padding:6px 12px; z-index:1100;
    box-shadow:0 -2px 10px rgba(0,0,0,.08);
  }
  .ui-btn {
    height: 30px; padding:0 12px; border:1px solid #cfd6df; border-radius:6px;
    background:#fff; font:600 13px/30px Tahoma,Arial; color:#2a5db0; cursor:pointer;
    box-shadow:inset 0 1px 0 rgba(255,255,255,.85); text-decoration:none; display:inline-block;
  }
  .ui-btn:hover { background:#f8fbff; border-color:#9fc9f8; color:#0d4d8a; }
  .ui-input {
    height: 30px; padding:0 8px; border:1px solid #bfc9d6; border-radius:6px; background:#fff;
    font:13px Tahoma; color:#222; width:160px;
  }
  .ui-sep { width:1px; height:20px; background:#dfe6ef; margin:0 2px; }
  .ui-spacer { flex:1; }
  @media (max-width: 980px){
    .ui-bar { height:auto; flex-wrap:wrap; padding:6px; gap:6px; }
  }
</style>

<div class="ui-bar" id="buttonsBar">
  <!-- Переключатель «Нападение» -->
  <?php if ($IS_PVE): ?>
    <a class="ui-btn" href="/game.php?go=buttons&napadenie=off" title="Отключить нападение">Нападение: Вкл</a>
  <?php else: ?>
    <a class="ui-btn" href="/game.php?go=buttons&napadenie=on"  title="Включить нападение">Нападение: Выкл</a>
  <?php endif; ?>

  <!-- Режим чата: общий/локалка -->
  <?php if ($CHAT_SCOPE_IS_ROOM): ?>
    <a class="ui-btn" href="/game.php?go=buttons&gochat=off" title="Переключить на общий чат">Режим: Локалка</a>
  <?php else: ?>
    <a class="ui-btn" href="/game.php?go=buttons&gochat=on"  title="Переключить на чат локалки">Режим: Общий</a>
  <?php endif; ?>

  <div class="ui-sep"></div>

  <!-- Ник для действий -->
  <input type="text" class="ui-input" id="actNick" placeholder="Ник">

  <!-- Кнопки действий с игроком -->
  <button class="ui-btn" onclick="btnTrade()"   title="Предложение обмена">Обмен</button>
  <button class="ui-btn" onclick="btnBreed()"   title="Предложить разведение">Разведение</button>
  <button class="ui-btn" onclick="btnBattles()" title="Просмотреть бои на локации">Бои</button>

  <?php if ($IS_GROUP_123): ?>
    <div class="ui-sep"></div>
    <!-- Кнопки модера (как было раньше) -->
    <button class="ui-btn" onclick="parent.bbJs()"   title="Чёрный">Ч</button>
    <button class="ui-btn" onclick="parent.redJs()"  title="Красный">Кр</button>
    <button class="ui-btn" onclick="parent.blueJs()" title="Синий">Син</button>
  <?php endif; ?>

  <?php if ($IS_GROUP_14): ?>
    <button class="ui-btn" onclick="parent.nastJs()"  title="Цвет наставника">Наст</button>
    <button class="ui-btn" onclick="parent.yelowJs()" title="Жёлтый">Желт</button>
  <?php endif; ?>

  <div class="ui-spacer"></div>

  <!-- Быстрые переходы 1–6 (пример; подписи можно поменять) -->
  <a class="ui-btn" href="/game">1</a>
  <a class="ui-btn" href="/game.php?go=sends">2</a>
  <a class="ui-btn" href="/game.php?go=pokemon">3</a>
  <a class="ui-btn" href="/game.php?go=diamond_shop">4</a>
  <a class="ui-btn" href="/game.php?go=quest_list" target="_blank">5</a>
  <a class="ui-btn" href="/index.php?go=exits">6</a>
</div>

<script type="text/javascript">
function g(id){return document.getElementById(id)||null;}
function trim(s){return (s||'').replace(/^\s+|\s+$/g,'');}
function nick(){var v=g('actNick'); return v?trim(v.value):'';}

function btnTrade(){
  var n = nick(); if(!n){ alert('Укажите ник'); return; }
  // Старое поведение: map&gets=true&trade=true&to=...
  window.location.href = '/game&gets=true&trade=true&to='+encodeURIComponent(n);
}
function btnBreed(){
  var n = nick(); if(!n){ alert('Укажите ник'); return; }
  // Старое поведение: char&newpok=1&to_tren=...
  window.location.href = '/game.php?go=char&newpok=1&to_tren='+encodeURIComponent(n);
}
function btnBattles(){
  window.location.href = '/game&gets=true&view_battle=true';
}
</script>