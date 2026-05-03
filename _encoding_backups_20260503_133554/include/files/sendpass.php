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
      $mess = '<p class="regWindowOk">Р СџР В°РЎР‚Р С•Р В»РЎРЉ РЎС“РЎРѓР С—Р ВµРЎв‚¬Р Р…Р С• Р С‘Р В·Р СР ВµР Р…Р ВµР Р…. Р СћР ВµР С—Р ВµРЎР‚РЎРЉ Р вЂ™РЎвЂ№ Р СР С•Р В¶Р ВµРЎвЂљР Вµ Р Р†Р С•Р в„–РЎвЂљР С‘ Р С—Р С•Р Т‘ Р С—Р В°РЎР‚Р С•Р В»Р ВµР С, Р С”Р С•РЎвЂљР С•РЎР‚РЎвЂ№Р в„– Р С—РЎР‚Р С‘РЎв‚¬Р ВµР В» Р вЂ™Р В°Р С Р Р…Р В° E-mail Р Р† Р С—Р ВµРЎР‚Р Р†Р С•Р С Р С—Р С‘РЎРѓРЎРЉР СР Вµ.</p>';
    }else{
      $mess = '<p class="regWindowError">Р СњР ВµР Р†Р ВµРЎР‚Р Р…Р С•Р Вµ Р В·Р Р…Р В°РЎвЂЎР ВµР Р…Р С‘Р Вµ</p>';
    } 
  }
  if(!empty($_POST['login']) && !empty($_POST['email']) && preg_match("|^[a-z_-]+$|i", $_POST['login']) && preg_match("/^[a-z0-9._-]{1,20}@(([a-z0-9-]+\.)+(com|net|org|mil|edu|gov|ru|info|biz |inc|"."name|[a-z]{2})|[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})$/is",$_POST['email'])){
     $sends_pass = first('SELECT id,email,login FROM users WHERE login="%s" AND email="%s"',$_POST['login'],$_POST['email']);
     if(!empty($sends_pass['id'])){
       $code = date('His').rand(100000,999999).'s3a5';
       $code = md5($code);
       $pass = rand(100000,999999);
       $to = $sends_pass['email'];
       $subject = "Pokelegenda: Р вЂ™Р С•РЎРѓРЎРѓРЎвЂљР В°Р Р…Р С•Р Р†Р В»Р ВµР Р…Р С‘Р Вµ Р С—Р В°РЎР‚Р С•Р В»РЎРЏ";
       $message = '<html>
                  <head>
                   <title>Pokelegenda: Р вЂ™Р С•РЎРѓРЎРѓРЎвЂљР В°Р Р…Р С•Р Р†Р В»Р ВµР Р…Р С‘Р Вµ Р С—Р В°РЎР‚Р С•Р В»РЎРЏ</title>
                  </head>
                  <body>
                  <table border="6" cellpadding=7 cellspacing=10 width=80% align="center" bgcolor="#000" style="border-radius:10;" >
                  <tr>
                  <td>
                  <font color="ffffff">
                  <p>Р вЂ”Р Т‘РЎР‚Р В°РЎРѓРЎвЂљР Р†РЎС“Р в„–РЎвЂљР Вµ, <b>'.$_POST['login'].'</b>.</p>                  
                  Р вЂ™РЎвЂ№, Р С‘Р В»Р С‘ Р С”РЎвЂљР С•-РЎвЂљР С• Р Т‘РЎР‚РЎС“Р С–Р С•Р в„–, РЎРѓР С•Р В·Р Т‘Р В°Р В» Р В·Р В°Р С—РЎР‚Р С•РЎРѓ Р Р…Р В° Р С‘Р В·Р СР ВµР Р…Р ВµР Р…Р С‘Р Вµ Р С—Р В°РЎР‚Р С•Р В»РЎРЏ Р С” Р В°Р С”Р С”Р В°РЎС“Р Р…РЎвЂљРЎС“ Р В»Р С•Р С–Р С‘Р Р… Р С”Р С•РЎвЂљР С•РЎР‚Р С•Р С–Р С•: '.$sends_pass['login'].', Р Р…Р В° РЎР‚Р ВµРЎРѓРЎС“РЎР‚РЎРѓР Вµ: <a href="http://pokelegenda.ru" target="_blank" style="color:#FFF">Pokelegenda</a>.
                  Р вЂўРЎРѓР В»Р С‘ Р вЂ™РЎвЂ№ Р Р…Р Вµ РЎвЂ¦Р С•РЎвЂљР С‘РЎвЂљР Вµ Р С‘Р В·Р СР ВµР Р…РЎРЏРЎвЂљРЎРЉ Р С—Р В°РЎР‚Р С•Р В»РЎРЉ, РЎвЂљР С• Р С—РЎР‚Р С•Р С‘Р С–Р Р…Р С•РЎР‚Р С‘РЎР‚РЎС“Р в„–РЎвЂљР Вµ РЎРЊРЎвЂљР С• РЎРѓР С•Р С•Р В±РЎвЂ°Р ВµР Р…Р С‘Р Вµ.
                  Р вЂќР В»РЎРЏ Р В°Р С”РЎвЂљР С‘Р Р†Р В°РЎвЂ Р С‘Р С‘ Р Р…Р С•Р Р†Р С•Р С–Р С• Р С—Р В°РЎР‚Р С•Р В»РЎРЏ Р С” Р В°Р С”Р С”Р В°РЎС“Р Р…РЎвЂљРЎС“, Р вЂ™РЎвЂ№ Р Т‘Р С•Р В»Р В¶Р Р…РЎвЂ№ Р С—Р ВµРЎР‚Р ВµР в„–РЎвЂљР С‘ Р С—Р С• РЎРѓРЎРѓРЎвЂ№Р В»Р С”Р Вµ: <a href="http://pokelegenda.ru/index.php?go=sendpass&codes='.$code.'&users='.$sends_pass['id'].'" style="color:#FFF">pokelegenda.ru/index.php?go=sendpass&codes='.$code.'&users='.$sends_pass['id'].'</a>
                  Р вЂўРЎРѓР В»Р С‘ Р В°Р С”РЎвЂљР С‘Р Р†Р В°РЎвЂ Р С‘РЎРЏ Р С—РЎР‚Р С•РЎв‚¬Р В»Р В° РЎС“РЎРѓР С—Р ВµРЎв‚¬Р Р…Р С• Р Р†РЎвЂ№ РЎРѓР СР С•Р В¶Р ВµРЎвЂљР Вµ Р Р†Р С•Р в„–РЎвЂљР С‘ Р Р† Р С‘Р С–РЎР‚РЎС“, Р С—Р С•Р Т‘ <span style="color:brown"><b>Р СџР В°РЎР‚Р С•Р В»Р ВµР С: '.$pass.'</b></span>.
                  Р вЂ™Р Р…Р С‘Р СР В°Р Р…Р С‘Р Вµ! Р РЋРЎРѓРЎвЂ№Р В»Р С”Р В° Р Т‘Р ВµР в„–РЎРѓРЎвЂљР Р†РЎС“Р ВµРЎвЂљ 24 РЎвЂЎР В°РЎРѓР В° Р С—Р С•РЎРѓР В»Р Вµ Р Т‘Р С•РЎРѓРЎвЂљР В°Р Р†Р С”Р С‘ РЎРЊРЎвЂљР С•Р С–Р С• РЎРѓР С•Р С•Р В±РЎвЂ°Р ВµР Р…Р С‘РЎРЏ!
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
      $mess = '<p class="regWindowOk">Р СџР С•РЎР‚Р С•Р В»РЎРЉ РЎС“РЎРѓР С—Р ВµРЎв‚¬Р Р…Р С• РЎРѓР В±РЎР‚Р С•РЎв‚¬Р ВµР Р…. Р вЂ™Р В°Р С Р С•РЎвЂљР С—РЎР‚Р В°Р Р†Р В»Р ВµР Р…Р С• РЎРѓР С•Р С•Р В±РЎвЂ°Р ВµР Р…Р С‘Р Вµ Р Р…Р В° Р Р†Р В°РЎв‚¬ E-mail: '.$_POST['email'].' РЎРѓ РЎРѓРЎРѓРЎвЂ№Р В»Р С”Р С•Р в„– Р С• Р С—Р С•Р Т‘РЎвЂљР Р†Р ВµРЎР‚Р В¶Р Т‘Р ВµР Р…Р С‘Р С‘.</p>';
     }else{
      $mess = '<p class="regWindowError">Р вЂќР В°Р Р…Р Р…Р С•Р С–Р С• Р С—Р С•Р В»РЎРЉР В·Р С•Р Р†Р В°РЎвЂљР ВµР В»РЎРЏ Р Р…Р Вµ РЎРѓРЎС“РЎвЂ°Р ВµРЎРѓРЎвЂљР Р†РЎС“Р ВµРЎвЂљ, Р В»Р С‘Р В±Р С• Р С•Р Р… Р В±РЎвЂ№Р В» РЎС“Р Т‘Р В°Р В»Р ВµР Р…!</p>';
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
<h2> Р вЂ”Р В°Р В±РЎвЂ№Р В»Р С‘ Р С—Р В°РЎР‚Р С•Р В»РЎРЉ?</h2>
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
      <b><font color="gold"  size="4">Р вЂ™Р Р†Р ВµР Т‘Р С‘РЎвЂљР Вµ Р вЂ™Р В°РЎв‚¬ Р В»Р С•Р С–Р С‘Р Р…: </font></b> 
    </td> 
    <td align="left"> 
      <input type="text" name="login" class="inpPass">
    </td> 
  </tr>
  <tr>
    <td align="right">
      <b><font color="gold" size="4">Р вЂ™Р Р†Р ВµР Т‘Р С‘РЎвЂљР Вµ Р вЂ™Р В°РЎв‚¬ E-mail: </font></b>
    </td>
    <td align="left">
      <br><input type="text" name="email" class="inpPass"> 
   </td>                
  </tr>
  <tr>
    <td align="center" colspan=2><input type="submit" name="submit" value="Р С›РЎвЂљР С—РЎР‚Р В°Р Р†Р С‘РЎвЂљРЎРЉ" class="inpPass"></td>
  </tr>
  </form>                   
</table>
</CENTER>
