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
    echo "<center><b>Ваш ип адрес был заблокирован. В бан по ип адресу попадают только те пользователи, которые многократно нарушали правила Покелегенды.</b></center>"; exit;
  }
?>