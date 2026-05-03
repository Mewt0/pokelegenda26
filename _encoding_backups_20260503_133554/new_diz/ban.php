<?php
$timeipban = time();
if(empty($_SESSION['ipBan'])) $_SESSION['ipBan'] = 0;
if($_SESSION['ipBan'] <= $timeipban){

}
else
{

}
  $ip = getenv("HTTP_X_FORWARDED_FOR");
  if(empty($ip) || $ip=='unknown'){ 
    $ip = getenv("REMOTE_ADDR"); 
  }
  $ip_res = first('SELECT ip FROM banip WHERE ip="%s"',$ip);
  if(!empty($ip_res['ip'])){ 
    echo "<center><b>Р’Р°С€ РёРї Р°РґСЂРµСЃ Р±С‹Р» Р·Р°Р±Р»РѕРєРёСЂРѕРІР°РЅ. Р’ Р±Р°РЅ РїРѕ РёРї Р°РґСЂРµСЃСѓ РїРѕРїР°РґР°СЋС‚ С‚РѕР»СЊРєРѕ С‚Рµ РїРѕР»СЊР·РѕРІР°С‚РµР»Рё, РєРѕС‚РѕСЂС‹Рµ РјРЅРѕРіРѕРєСЂР°С‚РЅРѕ РЅР°СЂСѓС€Р°Р»Рё РїСЂР°РІРёР»Р° РџРѕРєРµР»РµРіРµРЅРґС‹.</b></center>"; exit;
  }
?>