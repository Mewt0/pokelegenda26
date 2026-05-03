<?php
session_start();
include('error.php');
require_once ("include/function/config.php");
require_once ("include/function/db3.php");
require_once ("include/class/index.class.php");
$db = db($config);
require_once('include/function/globfanction.php');
delete('eventusers','time<='.(int)time());
if(!empty($_GET['code']) && !empty($_GET['user'])){
 $code =  obr_txt($_GET['code']);
 $user = obr_chis($_GET['user']);
 $x = first('SELECT id,items,users FROM eventusers WHERE code="%s" AND users=%d',$code,$user);
 if(!empty($x['id'])){
    plus_item(1,$x['items'],$x['users']);
    delete('eventusers','code="'.mysql_escape_string($code).'" AND users='.(int)$user);
  die("<script>alert('Подарок успешно получен! Скоро он появится у вас в инвентаре!'); window.close();</script>"); 
 }else{
    die('Error');
 }
}else{
  $a = time()+(60*60*24*7);
  die('Error: '.$a.' ');
}
// Код для массовой рассылки (закомментирован)
/*mail("000_06@list.ru","League-Of-Pokemons, Поздравляем с подарками!", "
        Здравствуйте, ".$login."
        ---------------------------------
          С уважением, Tacos
          Администратор League-Of-Pokemons.ru
          000_01@list.ru
      ", "From:000_06@list.ru");       */
      
      /* Получатели */
 /**
$users = select('SELECT login,email,id FROM users'); // выбираем всех
function ssilka($user){
  $a = rand(111111, 999999).date('His'); 
  $cod = md5($a);
  $time  = time()+(60*60*24*1);
  insert('eventusers',array('users'=>$user, 'code'=>$cod, 'items'=>5, 'time'=>$time));
  return  $cod;
}
$subject = "League-Of-Pokemons, Поздравляем с подарками!";
foreach($users  as $usersrow){ 
  $to   = $usersrow['login']." <".$usersrow['email'].">";
  $ssil = ssilka($usersrow['id']);
  $sx   = 'http://League-Of-Pokemons.ru/mailTo.php?code='.$ssil.'&user='.$usersrow['id'];  
$message = '
<html>
<head>
 <title>Поздравляем с подарками для всех!</title>
</head>
<body>
<p>Здравствуйте, '. $usersrow['login'].'!<br>
  Вы (или кто-то другой) зарегистрировались на проекте: League-Of-Pokemons под этим E-mail адресом и подписались получать важные новости!</p>
<table>
  <tr>
   <td>
    К сожалению, в проекте: http://League-Of-Pokemons.ru все пользователи были поощрены подарками! Чтобы их получить, Вам необходимо перейти по этой ссылке: '.$sx.'. Внимание! Ссылка действует 24 часа после получения. Для отката подарка достаточно пройти по этой ссылке повторно это сообщение!
   </td>
  </tr>
  <tr>
   <td>Спасибо за то, что вы играете с нами! С Уважением, команда League-Of-Pokemons.ru</td>
  </tr>
</table>
</body>
</html>
';
/* Для отправки HTML-почты вы должны установить заголовок Content-type. 
$headers= "MIME-Version: 1.0\r\n";
$headers .= "Content-type: text/html; Charset=Windows-1251\r\n";

$headers .= "From: League-Of-Pokemons Events Day <events@League-Of-Pokemons.ru>\r\n";
$headers .= "Cc: events@League-Of-Pokemons.ru/\r\n";
$headers .= "Bcc: events@League-Of-Pokemons.ru/\r\n";
mail($to, $subject, $message, $headers);
}                 */

?>