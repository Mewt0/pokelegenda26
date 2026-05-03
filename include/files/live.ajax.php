<?php
@session_start();
@header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

require_once $_SERVER['DOCUMENT_ROOT'].'/include/function/config.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/include/function/db3.php';

$db = db($config);

// auth как в game.php
if (empty($_SESSION['login']) || empty($_SESSION['password'])) { echo json_encode(array('ok'=>false,'err'=>'auth')); exit; }
$login = mysql_real_escape_string($_SESSION['login']);
$pass  = mysql_real_escape_string($_SESSION['password']);
$q = @mysql_query("SELECT * FROM users WHERE login='".$login."' AND password='".$pass."' AND activation=1 LIMIT 1");
if (!$q) { echo json_encode(array('ok'=>false,'err'=>'db')); exit; }
$myrow = mysql_fetch_assoc($q);
if (!$myrow || empty($myrow['id'])) { echo json_encode(array('ok'=>false,'err'=>'nouser')); exit; }

function __state_hash($u){
  $b=(int)$u['buildmy']; $c=(int)$u['mychat']; $p=(int)$u['pve_button']; $bid=(int)$u['battleid'];
  return md5($b.'|'.$c.'|'.$p.'|'.$bid);
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action === 'state') {
  $hash = __state_hash($myrow);
  $bid  = !empty($myrow['battleid']) ? (int)$myrow['battleid'] : 0;
  echo json_encode(array(
    'ok'=>true,
    'hash'=>$hash,
    'battle'=>($bid>0?1:0),
    'redirect'=>($bid>0?'/game.php?go=fight_pve':null)
  ));
  exit;
}

if ($action === 'load') {
  if (!defined('AJAX_FRAGMENT')) define('AJAX_FRAGMENT', 1);
  $part = isset($_GET['part']) ? $_GET['part'] : '';
  ob_start();
  if ($part==='map') {
    include $_SERVER['DOCUMENT_ROOT'].'/include/files/map.world.php';
  } elseif ($part==='chat') {
    include $_SERVER['DOCUMENT_ROOT'].'/include/files/chat.world.php';
  } elseif ($part==='buttons') {
    include $_SERVER['DOCUMENT_ROOT'].'/include/files/buttons.world.php';
  } else {
    ob_end_clean(); echo json_encode(array('ok'=>false,'err'=>'bad_part')); exit;
  }
  $html = ob_get_clean();
  echo json_encode(array('ok'=>true,'html'=>$html)); exit;
}

echo json_encode(array('ok'=>false,'err'=>'bad_action'));
