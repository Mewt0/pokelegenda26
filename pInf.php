<?php
session_start();
$s = mktime(18, 00, 00, 11  , 17, 2012);
echo $s;
die();
require_once ("include/function/config.php");
require_once ("include/function/db3.php");
$db = db($config);
if($_SESSION['id'] == 1){
$message = select('
SELECT   
u1.login as u1_login, 
u1.id as u1_id, 
u2.id as u2_id, 
u2.login as u2_login 
FROM users u1
INNER JOIN users u2
ON u1.ip=u2.ip
AND u1.id<u2.id
WHERE u1.ip>0 AND u2.ip>0 AND (u2.groups<>7 OR u1.groups<>7)
ORDER BY u1.ip DESC limit 40');
 $messages['u1_ban_off'] = false;
 $messages['u2_ban_off'] = false;
foreach($message as $messages){
if($messages['u1_ban_off']){ $mess_ban = "$messages[u1_ban_off]"; }  else { $mess_ban = "<font color=brown>Не зарегистрирован!</font>"; }
if($messages['u2_ban_off']){ $mess_ban2 = "<br>Заблокирован_2: $messages[u2_ban_off]"; } else { $mess_ban2 = "<br>Заблокирован_2: <font color=brown>Не зарегистрирован!</font>";}
print ' <table  width = 600 bgcolor="#363636" style="border-radius:10;" >';
echo " 
<tr><td>
<b>
1) Пользователь: <a href=\"page.php?id=$messages[u1_id]\" style=\"color:#8B6914;\">$messages[u1_login]</a>, 
<font color=Cyan2>игрок</font> <a href=\"page.php?id=$messages[u2_id]\"  target=\"_blank\">$messages[u2_login]</a>. 
</b>
<br>
<b>
2) Пользователь: <a href=\"page.php?id=$messages[u2_id]\" style=\"color:#8B6914;\">$messages[u2_login]</a>, 
<font color=Cyan2>игрок</font> <a href=\"page.php?id=$messages[u1_id]\"  target=\"_blank\">$messages[u1_login]</a>. 
</b>
<br>
<b>Заблокирован_1:  $mess_ban  $mess_ban2 </b> </td> </tr>";                                                                                        
print '</table><br><hr><br>';
}
}
if($_SESSION['id'] == 1){
/*$lgRin = select('
SELECT * 
FROM log_rinok
WHERE user_to=6
ORDER BY id ASC');                                                                                                                                                                                                                                                                           
foreach($lgRin as $lgRinec){
  print '<br>
  Покупатель: <a href="page.php?id='.$lgRinec['user_id'].'" style="color:red;"  target="_blank">'.$lgRinec['user_id'].'</a>. 
  Продавец: <a href="page.php?id='.$lgRinec['user_to'].'" style="color:red;"  target="_blank">'.$lgRinec['user_to'].'</a>. 
  Data('.$lgRinec['date'].'). 
  Предмет: '.$lgRinec['id_item'].' 
  Text: '.$lgRinec['text'].' ';
} */


/*$lgRin = select('
SELECT ap.id
FROM attac_my_poke ap
INNER JOIN attac_my_poke pa ON ap.id=pa.id
GROUP BY ap.pok_id
HAVING COUNT(ap.pok_id)>1 LIMIT 0 , 30');

foreach($lgRin as $lgRinec){
  $ii = $lgRinec['id']+1;
  delete('attac_my_poke','id='.(int)$ii);
} */
/*$lgRin = select('
SELECT id 
FROM users
WHERE groups=7
ORDER BY id ASC LIMIT 0 , 30');

foreach($lgRin as $lgRinec){
  delete('users','id='.(int)$lgRinec['id']);
  delete('usersunictable','id='.(int)$lgRinec['id']);
  delete('pok_user','users='.(int)$lgRinec['id']);
  delete('pok_pve','users='.(int)$lgRinec['id']);
  delete('quest','user_id='.(int)$lgRinec['id']); 
  delete('items_users','user_id='.(int)$lgRinec['id']); 
  delete('friends','id_my_friend='.(int)$lgRinec['id']); 
  delete('friends','id_user='.(int)$lgRinec['id']);
} */

}
die();
?>