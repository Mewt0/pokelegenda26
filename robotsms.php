<?php
session_start();
if(!empty($_GET['sms_id']) && !empty($_GET['short_number']) && !empty($_GET['msg'])){
require_once ("include/function/config.php");
require_once ("include/function/db3.php");
$db = db($config);
require_once('include/function/globfanction.php');
function log_sms($text){
    $GLOBALS['logs'] = fopen('dat/sms.dat', 'a');
    fwrite($GLOBALS['logs'], date("Y-m-d, H:i:s")." | ".$text." \r\n");
    fclose($GLOBALS['logs']);
}
function coolseitems($a,$b){
 $q = first('SELECT count FROM items_users WHERE user_id=%d AND item_id=%d',$a,$b);
  if($q['count'] > 0) return formatnum($q['count']);
   else return false;
}

#Примеры
$con['pref1'] = '79174';
$con['pref2'] = '79174'; // пока не нужно
#Cекретный ключ
$con['sign'] = '895371596';  // ключ

/* Присылаемые переменные */
$from = $_GET['from']; // Номер абонента
$date = $_GET['date']; // Data
$sms_id = $_GET['sms_id']; // Уникальный номер смс
$sign = $_GET['sign']; // Ключ
$country = $_GET['country']; // Страна
$number = $_GET['short_number']; // Короткий номер
$cost = $_GET['cost']; // Прибыль
/* Кончи присылаемые переменные */                             

    if($number == 7201 AND $country == 1 AND $cost>0) $donator_get = 2;  // 30 rub, rus
elseif($number == 7202 AND $country == 1 AND $cost>0) $donator_get = 5;  // 65 rub, rus
elseif($number == 3352 AND $country == 1 AND $cost>0) $donator_get = 8;  // 100 rub, rus
elseif($number == 8510 AND $country == 1 AND $cost>0) $donator_get = 11; // 254 rub, rus

elseif($number == 2855 AND $country == 3 AND $cost>0 AND $_GET['pay_status'] == "ok" AND $_GET['may_pay'] == "0") $donator_get = 5;  // 27 rub, 25 uah, ua
elseif($number == 3855 AND $country == 3 AND $cost>0 AND $_GET['pay_status'] == "ok" AND $_GET['may_pay'] == "0") $donator_get = 12;  // 62 rub, 50 uah, ua

elseif($number == 7122 AND $country == 8 AND $cost>0) $donator_get = 7; // 48 rub, 533.71 kzt, 
elseif($number == 7132 AND $country == 8 AND $cost>0) $donator_get = 4; // 22.91 rub, 265.18 kzt, 

elseif($number == 3339 AND $country == 12 AND $cost>0) $donator_get = 7; // 29900 byr.
elseif($number == 3336 AND $country == 12 AND $cost>0) $donator_get = 4; // 15900 byr

elseif($number == 5009 AND $country == 13 AND $cost>0) $donator_get = 2; // 1250 AMD.
elseif($number == 7122 AND $country == 13 AND $cost>0) $donator_get = 3; // 1666 amd.

else {    
  if ($country ==3 AND $_GET['sms_status'] == "mt"){ 
    echo("ok");
    echo("\n");
    echo("V blizhajshee vremja vash schet budet oplachen.");
    $txt_sms = '|'.$date.'|'.$sms_id.'| Страна'.$country.'| Сообщение'.$_GET['msg'].'|'.$from.'| Сообщение'.$_GET['msg'].'|'.$number.'|'.$cost.'|';
    log_sms($txt_sms);
    die(); 
  } 
  else die ('Hacking attempt!'); 
}

if ($sign != md5($sms_id.$con['sign'])) die ('Hacking attempt!'); 

$pos = strpos($_GET['msg'], $con['pref1']);
if($pos !== false){
    $pos = strpos($_GET['msg'], $con['pref1']);
    $ppos = $pos+strlen($con['pref1']); 
}
$user_id = substr($_GET['msg'], $ppos); //id пользователя 

$user_id = obr_chis($user_id);
$don = first('SELECT vsegoalmaz,id FROM usersunictable WHERE id=%d',$user_id);
  if(!empty($don) && $donator_get ){
    $login = first('SELECT login FROM users WHERE id=%d',$user_id);
    plus_item($donator_get,2,$user_id);
    query('UPDATE usersunictable SET vsegoalmaz=vsegoalmaz+'.$donator_get.' WHERE id=%d',$user_id);
    #Запись.
      $txt_sms = '|'.$date.'|'.$sms_id.'|'.$user_id.'|'.$don['login'].'|'.$from.'|'.$_GET['msg'].'|'.$number.'|'.$cost.'|';
      log_sms($txt_sms);
    #Конец
    echo("ok");
    echo("\n");
    echo("Vy udachno oplatili: ".$donator_get." almaz(a). Dlja pol'zovatelja: ".$login['login'].". Obshee kolichestvo almazov sostavljaet: ".coolseitems($user_id,2)." Na resurse: http://league-of-pokemons.ru");
    die();                                           
  }else{                        
    echo("ok");
    echo("\n");
    echo("Vi vveli nevernoe soobshenie");
    die();
  }
}else{
  echo("ok");
  echo("\n");
  echo("Vi vveli nevernoe soobshenie");
  die();
}
?>