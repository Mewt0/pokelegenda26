<?php
  delete('sendpass','time<='.(int)time());
  $mess = false;
  $class = 'regAutError';
  if(!empty($_GET['codes']) && !empty($_GET['users'])){
    $x01 = first('SELECT * FROM sendpass WHERE code="%s" AND users=%d',obr_txt($_GET['codes']),obr_chis($_GET['users']));
    if(!empty($x01['id'])){
      $new_password_sh = strrev(md5($x01["pass"]))."b3p6f";
      update('users',array('password'=>$new_password_sh),'id='.(int)$x01["users"]);
      delete('sendpass','id='.(int)$x01['id']);
      $mess = '<p class="regWindowOk">Пароль успешно изменен. Теперь Вы можете войти под паролем, который пришел Вам на E-mail в первом письме.</p>';
    }else{
      $mess = '<p class="regWindowError">Неверное значение</p>';
    } 
  }
  if(!empty($_POST['login']) && !empty($_POST['email']) && preg_match("|^[a-z_-]+$|i", $_POST['login']) && preg_match("/^[a-z0-9._-]{1,20}@(([a-z0-9-]+\.)+(com|net|org|mil|edu|gov|ru|info|biz |inc|"."name|[a-z]{2})|[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})$/is",$_POST['email'])){
     $sends_pass = first('SELECT id,email,login FROM users WHERE login="%s" AND email="%s"',$_POST['login'],$_POST['email']);
     if(!empty($sends_pass['id'])){
       $code = date('His').rand(100000,999999).'s3a5';
       $code = md5($code);
       $pass = rand(100000,999999);
       $to = $sends_pass['email'];
       $subject = "Pokelegenda: Восстановление пароля";
       $message = '<html>
                  <head>
                   <title>Pokelegenda: Восстановление пароля</title>
                  </head>
                  <body>
                  <table border="6" cellpadding=7 cellspacing=10 width=80% align="center" bgcolor="#000" style="border-radius:10;" >
                  <tr>
                  <td>
                  <font color="ffffff">
                  <p>Здраствуйте, <b>'.$_POST['login'].'</b>.</p>                  
                  Вы, или кто-то другой, создал запрос на изменение пароля к аккаунту логин которого: '.$sends_pass['login'].', на ресурсе: <a href="http://pokelegenda.ru" target="_blank" style="color:#FFF">Pokelegenda</a>.
                  Если Вы не хотите изменять пароль, то проигнорируйте это сообщение.
                  Для активации нового пароля к аккаунту, Вы должны перейти по ссылке: <a href="http://pokelegenda.ru/index.php?go=sendpass&codes='.$code.'&users='.$sends_pass['id'].'" style="color:#FFF">pokelegenda.ru/index.php?go=sendpass&codes='.$code.'&users='.$sends_pass['id'].'</a>
                  Если активация прошла успешно вы сможете войти в игру, под <span style="color:brown"><b>Паролем: '.$pass.'</b></span>.
                  Внимание! Ссылка действует 24 часа после доставки этого сообщения!
                  </font> 
                  </td>
                  </tr>
                  </table>
                  </body>
                  </html>
                  ';
      $headers= "MIME-Version: 1.0\r\n";
      $headers .= "Content-type: text/html; Charset=windows-1251\r\n";
      $headers .= "From: Pokelegenda <support@pokelegenda.ru>\r\n";
      $headers .= "Cc:  support@pokelegenda.ru\r\n";
      $headers .= "Bcc: support@pokelegenda.ru\r\n";
      $time  = time()+(60*60*24*1);
      insert('sendpass',array(
                        'code'=>$code,
                        'users'=>$sends_pass['id'],
                        'pass'=>$pass,
                        'time'=>$time));
      mail($to, $subject, $message, $headers);
      $mess = '<p class="regWindowOk">Пороль успешно сброшен. Вам отправлено сообщение на ваш E-mail: '.$_POST['email'].' с ссылкой о подтверждении.</p>';
     }else{
      $mess = '<p class="regWindowError">Данного пользователя не существует, либо он был удален!</p>';
     }
  }
?>
<style>
.inpPass{
  font-weight:bold;
  border: 2px solid #000;
  padding:  3px;
}
</style>
<h2> Забыли пароль?</h2>
<CENTER>
<table width=515 cellPadding=0 border=0>
  <form action="#" method="post">
  <tr>
    <td align="center" colspan=2>
      <?php echo $mess; ?>
    </td>
  </tr>
  <tr>
    <td align="right">
      <b><font color="gold"  size="4">Введите Ваш логин: </font></b> 
    </td> 
    <td align="left"> 
      <input type="text" name="login" class="inpPass">
    </td> 
  </tr>
  <tr>
    <td align="right">
      <b><font color="gold" size="4">Введите Ваш E-mail: </font></b>
    </td>
    <td align="left">
      <br><input type="text" name="email" class="inpPass"> 
   </td>                
  </tr>
  <tr>
    <td align="center" colspan=2><input type="submit" name="submit" value="Отправить" class="inpPass"></td>
  </tr>
  </form>                   
</table>
</CENTER>
