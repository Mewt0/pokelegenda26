<?php
@session_start();
@header('Content-Type: text/html; charset=cp1251');

$cfg = $_SERVER['DOCUMENT_ROOT'].'/include/config.php';
if (!file_exists($cfg)) { $cfg = dirname(__DIR__).'/config.php'; }
if (file_exists($cfg)) { require_once $cfg; }

if (!isset($config) || empty($config['server'])) {
    echo '<div class="loc-users loc-users--error">Config not found</div>';
    return;
}

$link = @mysql_connect($config['server'], $config['user'], $config['pass']);
if (!$link || !@mysql_select_db($config['db'], $link)) {
    echo '<div class="loc-users loc-users--error">DB not available</div>';
    return;
}
@mysql_query("SET NAMES 'cp1251'", $link);

$currentUserId = isset($_SESSION['id']) ? (int)$_SESSION['id'] : 0;
$currentUser = null;
if ($currentUserId > 0) {
    $q = mysql_query("SELECT id, buildmy FROM users WHERE id=".$currentUserId." LIMIT 1");
    $currentUser = $q ? mysql_fetch_assoc($q) : null;
}

$buildId = !empty($currentUser['buildmy']) ? (int)$currentUser['buildmy'] : 0;
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
<style>
.loc-users{height:100%;box-sizing:border-box;padding:14px;background:#f7fafc;color:#172033;font:13px Tahoma,Arial,sans-serif}
.loc-users__title{font-weight:700;font-size:15px;margin:0 0 4px;text-align:left}
.loc-users__sub{color:#65758b;margin:0 0 12px}
.loc-users__list{display:grid;gap:8px;max-height:calc(100vh - 240px);overflow:auto;padding-right:4px}
.loc-user{display:grid;gap:5px;padding:9px 10px;background:#fff;border:1px solid #d8e1ee;border-radius:8px}
.loc-user__top{display:flex;align-items:center;justify-content:space-between;gap:8px}
.loc-user__name{font-weight:700;color:#174f91;text-decoration:none;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.loc-user__status{display:inline-flex;align-items:center;gap:5px;font-size:12px;color:#65758b}
.loc-user__dot{width:8px;height:8px;border-radius:50%;background:#a5b4c5}
.loc-user__dot--on{background:#24a35a}
.loc-user__badges{display:flex;gap:5px;flex-wrap:wrap}
.loc-user__badge{font-size:11px;line-height:18px;padding:0 6px;border-radius:999px;background:#eef3f8;color:#49627f;border:1px solid #d8e1ee}
.loc-user__badge--on{background:#e6f7ed;color:#1c7c43;border-color:#bde5cc}
.loc-users--error{color:#8a1f1f;background:#fff4f4}
</style>
<div class="loc-users">
  <h3 class="loc-users__title">&#1057;&#1077;&#1081;&#1095;&#1072;&#1089; &#1085;&#1072; &#1083;&#1086;&#1082;&#1072;&#1094;&#1080;&#1080; (<?php echo count($users); ?>)</h3>
  <p class="loc-users__sub">
    #<?php echo (int)$buildId; ?> <?php echo loc_h($locationTitle); ?>
  </p>
  <div class="loc-users__list" id="online">
    <?php if (empty($users)): ?>
      <div class="loc-user">&#1053;&#1072; &#1083;&#1086;&#1082;&#1072;&#1094;&#1080;&#1080; &#1085;&#1080;&#1082;&#1086;&#1075;&#1086; &#1085;&#1077;&#1090;.</div>
    <?php endif; ?>
    <?php foreach ($users as $user): ?>
      <?php $online = loc_is_online($user); ?>
      <div class="loc-user">
        <div class="loc-user__top">
          <a class="loc-user__name" href="/game.php?go=trenInfo&id=<?php echo (int)$user['id']; ?>">
            <?php echo loc_h($user['login']); ?>
          </a>
          <span class="loc-user__status">
            <span class="loc-user__dot <?php echo $online ? 'loc-user__dot--on' : ''; ?>"></span>
            <?php echo $online ? 'online' : 'offline'; ?>
          </span>
        </div>
        <div class="loc-user__badges">
          <span class="loc-user__badge">&#1043;&#1088;. <?php echo (int)$user['groups']; ?></span>
          <span class="loc-user__badge <?php echo !empty($user['pve_button']) ? 'loc-user__badge--on' : ''; ?>">PVE <?php echo !empty($user['pve_button']) ? 'ON' : 'OFF'; ?></span>
          <span class="loc-user__badge <?php echo !empty($user['pvp']) ? 'loc-user__badge--on' : ''; ?>">PVP <?php echo !empty($user['pvp']) ? 'ON' : 'OFF'; ?></span>
          <span class="loc-user__badge <?php echo !empty($user['trade']) ? 'loc-user__badge--on' : ''; ?>">Trade <?php echo !empty($user['trade']) ? 'ON' : 'OFF'; ?></span>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
