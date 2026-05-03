<?php
@session_start();
@header('Content-Type: text/html; charset=windows-1251');

$cfg = $_SERVER['DOCUMENT_ROOT'].'/include/config.php';
if (!file_exists($cfg)) { $cfg = dirname(__DIR__).'/config.php'; }
if (file_exists($cfg)) { require_once $cfg; }

if (!isset($config) || empty($config['server'])) {
    echo '<html><body>Config not found</body></html>';
    return;
}

$link = @mysql_connect($config['server'], $config['user'], $config['pass']);
if (!$link || !@mysql_select_db($config['db'], $link)) {
    echo '<html><body>DB not available</body></html>';
    return;
}
@mysql_query("SET NAMES 'cp1251'", $link);

$currentUserId = isset($_SESSION['id']) ? (int)$_SESSION['id'] : 0;
$buildId = 0;
if ($currentUserId > 0) {
    $q = mysql_query("SELECT buildmy FROM users WHERE id=".$currentUserId." LIMIT 1");
    $currentUser = $q ? mysql_fetch_assoc($q) : null;
    $buildId = !empty($currentUser['buildmy']) ? (int)$currentUser['buildmy'] : 0;
}

$locationTitle = '';
if ($buildId > 0) {
    $qLoc = mysql_query("SELECT title FROM build WHERE id=".$buildId." LIMIT 1");
    $loc = $qLoc ? mysql_fetch_assoc($qLoc) : null;
    $locationTitle = !empty($loc['title']) ? $loc['title'] : '';
}

$users = array();
if ($buildId > 0) {
    $sql = "SELECT id, login, groups, online, onlinetime, pve, pvp, trade, pve_button
            FROM users
            WHERE buildmy=".$buildId." AND activation=1
            ORDER BY online DESC, login ASC
            LIMIT 80";
    $res = mysql_query($sql);
    if ($res) {
        while ($row = mysql_fetch_assoc($res)) {
            $users[] = $row;
        }
    }
}

function loc_h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'windows-1251');
}

function loc_is_online($user) {
    return ((int)$user['online'] === 1) && (time() - (int)$user['onlinetime'] <= 300);
}
?>
<html>
<head>
<meta HTTP-EQUIV="Content-Type" CONTENT="text/html;Charset=Windows-1251">
<style>
BODY {margin:10px; background-color:#696969; color:#000; font:12px Tahoma,Arial,sans-serif; overflow:auto;}
a:link, a:active, a:visited {text-decoration:none; color:#000000;}
a:hover {text-decoration:none; color:#505050;}
#onlineusers{font-size:12px; line-height:18px; word-wrap:break-word;}
.loc-head{margin-bottom:8px; text-align:center;}
.loc-title{font-weight:bold; font-size:13px;}
.loc-sub{font-size:12px; color:#111; margin-top:3px;}
.user-row{white-space:nowrap; overflow:hidden; text-overflow:ellipsis; margin:2px 0;}
.user-row img{vertical-align:middle;}
.dot{display:inline-block; width:7px; height:7px; border-radius:50%; background:#777; margin-right:4px;}
.dot-on{background:#21a642;}
.badge{font-size:10px; color:#333;}
#battlego {
  background:#505050; left:3px; top:0; width:170px; height:95px; overflow:auto;
  z-index:2; padding:3px; position:absolute; border:solid 1px #baa4a8; color:#fff;
  text-align:center; font-size:12px; font-family:Tahoma,serif; display:none; opacity:.9;
  box-shadow:0 0 18px #c0c0c0; border-radius:7px;
}
#battle_view {
  background:#505050; left:3px; top:0; width:85%; min-height:70px; overflow:auto;
  z-index:2; padding:5px; position:absolute; border:solid 1px #baa4a8; color:#fff;
  text-align:left; font-size:12px; font-family:Tahoma,serif; display:none;
  box-shadow:0 0 18px #c0c0c0; border-radius:7px;
}
#battl{display:none;}
#mess{
  background:#000; display:none; padding:4px 3px; font-size:12px; font-weight:bold;
  left:2px; top:5px; width:94%; position:fixed; border:solid 1px #000; color:#fff;
  border-radius:3px; box-shadow:0 0 10px #c0c0c0;
}
.close_battle_view{
  background:url("../../css/img/close_n.png") no-repeat; width:16px; height:16px;
  position:absolute; top:3px; right:3px; cursor:pointer; z-index:2;
}
</style>
</head>
<body>
<div class="mess" id="mess"></div>
<div class="battlego" id="battlego">
  <div id="text"></div>
  <div id="button" align="center"></div>
</div>
<div class="battle_view" id="battle_view">
  <div class="close_battle_view" onclick="document.getElementById('battle_view').style.display='none'"></div>
  <b>Бои на локации:</b><br><br>
  <div id="battle_view_txt"></div>
</div>
<div class="loc-head">
  <b><span id="locname"><?php echo loc_h($locationTitle); ?></span>.</b><br>
  <span class="loc-title">Сейчас на локации (<span id="userscount"><?php echo count($users); ?></span>).</span>
  <div class="loc-sub">#<?php echo (int)$buildId; ?> <?php echo loc_h($locationTitle); ?></div>
</div>
<span id="battl">Вас вызывают на бой: <span id="trenBattle"></span></span>
<div id="online"><span id="onlineusers">
<?php if (empty($users)): ?>
  На локации никого нет.
<?php endif; ?>
<?php foreach ($users as $user): ?>
  <?php $online = loc_is_online($user); ?>
  <div class="user-row">
    <span class="dot <?php echo $online ? 'dot-on' : ''; ?>"></span>
    <a href="javascript:" onclick="window.open('/game.php?go=trenInfo&id=<?php echo (int)$user['id']; ?>', 'info', 'fullscreen=no,scrollbars=yes,width=800,height=530'); return false;"><b><?php echo loc_h($user['login']); ?></b></a>
    <span class="badge"><?php echo $online ? 'on' : 'off'; ?><?php echo !empty($user['pve_button']) ? ' / pve' : ''; ?></span>
  </div>
<?php endforeach; ?>
</span></div>
</body>
</html>
