<?php
if((!empty($_GET['zap_login']) OR !empty($_GET['zap_email'])) && ($_GET['severeg'] == false)){
Header('Content-Type: text/css;charset=Windows-1251');
  if($_GET['zap_login'] != false && $_GET['zap_email'] == false){
      $aLogin = $_GET['zap_login'];
      if(!preg_match("|^[a-z_-]+$|i", $aLogin)){
        echo "NOР вЂєР С•Р С–Р С‘Р Р… Р СР С•Р В¶Р ВµРЎвЂљ РЎРѓР С•Р Т‘Р ВµРЎР‚Р В¶Р В°РЎвЂљРЎРЉ РЎвЂљР С•Р В»РЎРЉР С”Р С• Р В»Р В°РЎвЂљР С‘Р Р…РЎРѓР С”Р С‘Р Вµ Р В±РЎС“Р С”Р Р†РЎвЂ№ Р С‘ Р В·Р Р…Р В°Р С”Р С‘: _ , -. <br>Р СџРЎР‚Р С‘ РЎРЊРЎвЂљР С•Р С, Р В±РЎС“Р С”Р Р†РЎвЂ№ Р Р…Р Вµ Р Т‘Р С•Р В»Р В¶Р Р…РЎвЂ№ Р С—Р С•Р Р†РЎвЂљР С•РЎР‚РЎРЏРЎвЂљРЎРѓРЎРЏ Р В±Р С•Р В»Р ВµР Вµ 2-РЎвЂ¦ РЎР‚Р В°Р В·, Р В° РЎРѓР С‘Р СР Р†Р С•Р В»РЎвЂ№ Р В±Р С•Р В»Р ВµР Вµ Р С•Р Т‘Р Р…Р С•Р С–Р С• РЎР‚Р В°Р В·Р В°.";      
        exit;
      }
      if(strlen($aLogin) < 3 or strlen($aLogin) > 16){
        echo "NOР вЂєР С•Р С–Р С‘Р Р… Р Т‘Р С•Р В»Р В¶Р ВµР Р… РЎРѓР С•РЎРѓРЎвЂљР С•РЎРЏРЎвЂљРЎРЉ Р Р…Р Вµ Р СР ВµР Р…Р ВµР Вµ РЎвЂЎР ВµР С Р С‘Р В· 3-РЎвЂ¦ РЎРѓР С‘Р СР Р†Р С•Р В»Р С•Р Р† Р С‘ Р Р…Р Вµ Р В±Р С•Р В»Р ВµР Вµ РЎвЂЎР ВµР С Р С‘Р В· 16-РЎвЂљР С‘.";
        exit;
      }
      if (preg_match("/(-)\\1/",$aLogin) || preg_match("/(_)\\1/",$aLogin) || preg_match("/_-/i",$aLogin) || preg_match("/-_/i",$aLogin)){
        echo "NOCР С‘Р СР Р†Р С•Р В»РЎвЂ№ Р Р…Р Вµ Р Т‘Р С•Р В»Р В¶Р Р…РЎвЂ№ РЎРѓРЎвЂљР С•РЎРЏРЎвЂљРЎРЉ Р С—Р С•Р Т‘РЎР‚РЎРЏР Т‘ Р В±Р С•Р В»Р ВµР Вµ Р С•Р Т‘Р Р…Р С•Р С–Р С• РЎР‚Р В°Р В·Р В°.";      
        exit;
      }
      if (preg_match("/^-/",$aLogin) ||  preg_match("/-$/",$aLogin) || preg_match("/^_/",$aLogin) ||  preg_match("/_$/",$aLogin)){
        echo "NOР вЂєР С•Р С–Р С‘Р Р… Р Р…Р Вµ Р Т‘Р С•Р В»Р В¶Р ВµР Р… Р Р…Р В°РЎвЂЎР С‘Р Р…Р В°РЎвЂљРЎРЉРЎРѓРЎРЏ Р С‘Р В»Р С‘ Р В·Р В°Р С”Р В°Р Р…РЎвЂЎР С‘Р Р†Р В°РЎвЂљРЎРЉРЎРѓРЎРЏ Р Р…Р В° РЎРѓР С‘Р СР Р†Р С•Р В».";      
        exit;
      }
      for($n=0; $n!=strlen($aLogin)-2; $n++){ 
        if($aLogin[$n]==$aLogin[$n+1] && $aLogin[$n]==$aLogin[$n+2]){
            echo 'NOР вЂРЎС“Р С”Р Р†РЎвЂ№ Р Р…Р Вµ Р Т‘Р С•Р В»Р В¶Р Р…РЎвЂ№ РЎРѓРЎвЂљР С•РЎРЏРЎвЂљРЎРЉ Р С—Р С•Р Т‘РЎР‚РЎРЏР Т‘ Р В±Р С•Р В»Р ВµР Вµ Р Т‘Р Р†РЎС“РЎвЂ¦ РЎР‚Р В°Р В·.';
            exit;
        }
      }       
      $aLogin = trim($_GET['zap_login']);
      $aLogin = stripslashes($aLogin);
      $aLogin = htmlspecialchars($aLogin); 
      $logined = first('SELECT id FROM users where MATCH (login) AGAINST ("%s" IN BOOLEAN MODE)',$aLogin);
      if(!$logined['id']) echo "OK"; 
        else 
          echo "NOР вЂќР В°Р Р…Р Р…РЎвЂ№Р в„– Р В»Р С•Р С–Р С‘Р Р… РЎС“Р В¶Р Вµ Р В·Р В°Р Р…РЎРЏРЎвЂљ.";
     exit;
    }
  if($_GET['zap_email'] != false && $_GET['zap_login'] == false){
      $aMail = $_GET['zap_email'];
      if(strlen($aMail) < 5 or strlen($aMail) > 100){
        echo "NOE-mail Р Т‘Р С•Р В»Р В¶Р ВµР Р… РЎРѓР С•РЎРѓРЎвЂљР С•РЎРЏРЎвЂљРЎРЉ Р Р…Р Вµ Р СР ВµР Р…Р ВµР Вµ РЎвЂЎР ВµР С Р С‘Р В· 5-РЎвЂљР С‘ РЎРѓР С‘Р СР Р†Р С•Р В»Р С•Р Р† Р С‘ Р Р…Р Вµ Р В±Р С•Р В»Р ВµР Вµ РЎвЂЎР ВµР С Р С‘Р В· 100.";
        exit;
      }                     
      if(!preg_match("/^[a-z0-9._-]{1,20}@(([a-z0-9-]+\.)+(com|net|org|mil|edu|gov|ru|info|biz |inc|"."name|[a-z]{2})|[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})$/is",$aMail)){
        echo "NOE-mail Р Р†Р Р†Р ВµР Т‘Р ВµР Р… Р Р…Р Вµ Р Р†Р ВµРЎР‚Р Р…Р С•.";      
        exit;
      }
      $aMail = trim($aMail);
      $aMail = stripslashes($aMail);
      $aMail = htmlspecialchars($aMail);
      $mailed = first('SELECT id FROM users where email="%s"',$aMail);
      $ip = getenv("HTTP_X_FORWARDED_FOR");
      if(empty($ip) || $ip=='unknown'){ 
          $ip = getenv("REMOTE_ADDR"); 
      }
      $ipadress = first('SELECT id FROM users WHERE ip="%s"',ip2long($ip));
      if($ipadress){
        echo "NOР СњР В° Р Р†Р В°РЎв‚¬Р ВµР С IP Р В°Р Т‘РЎР‚Р ВµРЎРѓР Вµ РЎС“Р В¶Р Вµ Р С‘Р СР ВµР ВµРЎвЂљРЎРѓРЎРЏ Р В°Р С”Р С”Р В°РЎС“Р Р…РЎвЂљ.";      
        exit;      
      }
      if(!$mailed['id']) echo "OK"; 
        else 
          echo "NOР вЂќР В°Р Р…Р Р…РЎвЂ№Р в„– E-mail РЎС“Р В¶Р Вµ Р В·Р В°Р Р…РЎРЏРЎвЂљ.";
     exit;
    }
}
if(!empty($_GET['severeg']) && $_GET['severeg'] == 'seve'){

  $yes = true;
  $obTopEr  = '<p class="regWindowError" align="center" valign="middle">';
  $obTop    = '<p class="regWindowOk" align="center" valign="middle">';
  $obBattom = '</p>';
  if($_SESSION['severeg'] == true) 
  {
    $yes = false;
    $txt = 'Р ВР В·Р Р†Р С‘Р Р…Р С‘РЎвЂљР Вµ, Р Р…Р С• Р Р†РЎвЂ№ РЎС“Р В¶Р Вµ РЎР‚Р ВµР С–Р С‘РЎРѓРЎвЂљРЎР‚Р С‘РЎР‚Р С•Р Р†Р В°Р В»Р С‘РЎРѓРЎРЉ РЎРѓР ВµР С–Р С•Р Т‘Р Р…РЎРЏ!';
  }
  if (isset($_POST['login']) && isset($_POST['password']) && isset($_POST['email'])
  &&  isset($_POST['passwordTwo']) &&  isset($_POST['question']) &&  isset($_POST['answer'])
  &&  isset($_POST['gender']) &&  isset($_POST['digits'])
  &&  isset($_POST['check'])  &&  $yes == true)                                                   
  { 
    $login    = obr_txt($_POST['login']);       if($login    == '') unset($login);
    $pass     = obr_txt($_POST['password']);    if($pass     == '') unset($pass);
    $passt    = obr_txt($_POST['passwordTwo']); if($passt    == '') unset($passt);
    $email    = obr_txt($_POST['email']);       if($email    == '') unset($email);
    $question = obr_chis($_POST['question']);   if($question == '') unset($question);
    $answer   = obr_txt($_POST['answer']);      if($answer   == '') unset($answer);
    $gender   = obr_txt($_POST['gender']);      if($gender   == '') unset($gender);
    $pravila  = $_POST['law'];                  if($pravila  == '') unset($pravila);
    $codes    = obr_chis($_POST['digits']);     if($codes    == '') unset($codes);
    $trucodes = obr_txt($_POST['check']);       if($trucodes == '') unset($trucodes);
    
      if(empty($login) OR empty($pass) OR empty($passt) OR empty($email) OR empty($question) OR empty($answer) OR empty($gender) OR empty($codes) OR empty($trucodes)){
        $txtError = "Р В¤Р С•РЎР‚Р СР В° РЎР‚Р ВµР С–Р С‘РЎРѓРЎвЂљРЎР‚Р В°РЎвЂ Р С‘Р С‘ Р В·Р В°Р С—Р С•Р В»Р Р…Р ВµР Р…Р В° Р Р…Р Вµ Р Р†Р ВµРЎР‚Р Р…Р С•.";
        $yes = false;
      }
      if((md5($codes) != $trucodes) && ($yes == true)){
        $txtError = "Р вЂ™РЎвЂ№ Р Р†Р Р†Р ВµР В»Р С‘ Р Р…Р Вµ Р Р†Р ВµРЎР‚Р Р…РЎвЂ№Р Вµ РЎвЂ Р С‘РЎвЂћРЎР‚РЎвЂ№ РЎРѓ Р С”Р В°РЎР‚РЎвЂљР С‘Р Р…Р С”Р С‘.";
        $yes = false;
      }
      if($pravila == false || $pravila == '' && $yes == true){
        $txtError = "Р вЂ™РЎвЂ№ Р Р…Р Вµ РЎРѓР С•Р С–Р В»Р В°РЎРѓР С‘Р В»Р С‘РЎРѓРЎРЉ РЎРѓ Р С—РЎР‚Р В°Р Р†Р С‘Р В»Р В°Р СР С‘ Р С‘Р С–РЎР‚РЎвЂ№.";
        $yes = false;
      }    
      if(!preg_match("|^[a-z_-]+$|i", $login) && $yes == true){
        $txtError = "Р вЂєР С•Р С–Р С‘Р Р… Р СР С•Р В¶Р ВµРЎвЂљ РЎРѓР С•Р Т‘Р ВµРЎР‚Р В¶Р В°РЎвЂљРЎРЉ РЎвЂљР С•Р В»РЎРЉР С”Р С• Р В»Р В°РЎвЂљР С‘Р Р…РЎРѓР С”Р С‘Р Вµ Р В±РЎС“Р С”Р Р†РЎвЂ№ Р С‘ Р В·Р Р…Р В°Р С”Р С‘: _ , -. <br>Р СџРЎР‚Р С‘ РЎРЊРЎвЂљР С•Р С, Р В±РЎС“Р С”Р Р†РЎвЂ№ Р Р…Р Вµ Р Т‘Р С•Р В»Р В¶Р Р…РЎвЂ№ Р С—Р С•Р Р†РЎвЂљР С•РЎР‚РЎРЏРЎвЂљРЎРѓРЎРЏ Р В±Р С•Р В»Р ВµР Вµ 2-РЎвЂ¦ РЎР‚Р В°Р В·, Р В° РЎРѓР С‘Р СР Р†Р С•Р В»РЎвЂ№ Р В±Р С•Р В»Р ВµР Вµ Р С•Р Т‘Р Р…Р С•Р С–Р С• РЎР‚Р В°Р В·Р В°.";      
        $yes = false;
      }
      if((strlen($login) < 3 OR strlen($login) > 16) && $yes == true){
        $txtError =  "Р вЂєР С•Р С–Р С‘Р Р… Р Т‘Р С•Р В»Р В¶Р ВµР Р… РЎРѓР С•РЎРѓРЎвЂљР С•РЎРЏРЎвЂљРЎРЉ Р Р…Р Вµ Р СР ВµР Р…Р ВµР Вµ РЎвЂЎР ВµР С Р С‘Р В· 3-РЎвЂ¦ РЎРѓР С‘Р СР Р†Р С•Р В»Р С•Р Р† Р С‘ Р Р…Р Вµ Р В±Р С•Р В»Р ВµР Вµ РЎвЂЎР ВµР С Р С‘Р В· 16-РЎвЂљР С‘.";
        $yes = false;
      }
      if((preg_match("/(-)\\1/",$login) || preg_match("/(_)\\1/",$login) || preg_match("/_-/i",$login) || preg_match("/-_/i",$login)) && $yes == true){
        $txtError = "Р вЂєР С•Р С–Р С‘Р Р… РЎРѓР С•Р Т‘Р ВµРЎР‚Р В¶Р С‘РЎвЂљ РЎРѓР С‘Р СР Р†Р С•Р В»РЎвЂ№, Р С”Р С•РЎвЂљР С•РЎР‚РЎвЂ№Р Вµ Р С—Р С•Р Р†РЎвЂљР С•РЎР‚РЎРЏРЎР‹РЎвЂљРЎРѓРЎРЏ Р В±Р С•Р В»Р ВµР Вµ Р С•Р Т‘Р Р…Р С•Р С–Р С• РЎР‚Р В°Р В·Р В° Р С—Р С•Р Т‘РЎР‚РЎРЏР Т‘.";      
        $yes = false;
      }
      if((preg_match("/^-/",$login) ||  preg_match("/-$/",$login) || preg_match("/^_/",$login) ||  preg_match("/_$/",$login)) && $yes == true){
        $txtError = "Р вЂєР С•Р С–Р С‘Р Р… Р Р…Р Вµ Р Т‘Р С•Р В»Р В¶Р ВµР Р… Р Р…Р В°РЎвЂЎР С‘Р Р…Р В°РЎвЂљРЎРЉРЎРѓРЎРЏ Р С‘Р В»Р С‘ Р В·Р В°Р С”Р В°Р Р…РЎвЂЎР С‘Р Р†Р В°РЎвЂљРЎРЉРЎРѓРЎРЏ Р Р…Р В° РЎРѓР С‘Р СР Р†Р С•Р В».";      
        $yes = false;
      }
      if($yes == true){
        for($n=0; $n!=strlen($login)-2; $n++){ 
          if($login[$n]==$login[$n+1] && $login[$n]==$login[$n+2]){
            $txtError = 'Р вЂєР С•Р С–Р С‘Р Р… РЎРѓР С•Р Т‘Р ВµРЎР‚Р В¶Р С‘РЎвЂљ Р В±РЎС“Р С”Р Р†РЎвЂ№, Р С”Р С•РЎвЂљР С•РЎР‚РЎвЂ№Р Вµ Р С—Р С•Р Р†РЎвЂљР С•РЎР‚РЎРЏРЎР‹РЎвЂљРЎРѓРЎРЏ Р В±Р С•Р В»Р ВµР Вµ Р Т‘Р Р†РЎС“РЎвЂ¦ РЎР‚Р В°Р В· Р С—Р С•Р Т‘РЎР‚РЎРЏР Т‘.';
            $yes = false;
          }                     
        }
      }
      if($yes == true){
        $ip = getenv("HTTP_X_FORWARDED_FOR");
        if(empty($ip) || $ip=='unknown'){ 
          $ip = getenv("REMOTE_ADDR"); 
        }
        $ipadress = first('SELECT id FROM users WHERE ip="%s"',ip2long($ip));
        if($ipadress){    
             $txtError = 'Р СњР В° Р Р†Р В°РЎв‚¬Р ВµР С IP Р В°Р Т‘РЎР‚Р ВµРЎРѓР Вµ РЎС“Р В¶Р Вµ Р С‘Р СР ВµР ВµРЎвЂљРЎРѓРЎРЏ Р В°Р С”Р С”Р В°РЎС“Р Р…РЎвЂљ.';
             $yes = false;   
        }
      }
      if($yes == true){
        $logins_rows = first('SELECT id FROM users WHERE MATCH (login) AGAINST ("%s")',$login);
        $logins_rows_two = first('SELECT id FROM users WHERE login="%s"',$login);
          if($logins_rows['id'] != false || $logins_rows_two != false){
             $txtError = 'Р вЂќР В°Р Р…Р Р…РЎвЂ№Р в„– Р В»Р С•Р С–Р С‘Р Р… Р В·Р В°Р Р…РЎРЏРЎвЂљ Р Т‘РЎР‚РЎС“Р С–Р С‘Р С Р С—Р С•Р В»РЎРЉР В·Р С•Р Р†Р В°РЎвЂљР ВµР В»Р ВµР С.';
             $yes = false;
          }  
      }
      if((strlen($pass) < 6 OR strlen($pass) > 16) && $yes == true){
        $txtError = "Р СџР В°РЎР‚Р С•Р В»РЎРЉ Р Т‘Р С•Р В»Р В¶Р ВµР Р… РЎРѓР С•РЎРѓРЎвЂљР С•РЎРЏРЎвЂљРЎРЉ Р Р…Р Вµ Р СР ВµР Р…Р ВµР Вµ РЎвЂЎР ВµР С Р С‘Р В· 6-РЎвЂљР С‘ РЎРѓР С‘Р СР Р†Р С•Р В»Р С•Р Р† Р С‘ Р Р…Р Вµ Р В±Р С•Р В»Р ВµР Вµ РЎвЂЎР ВµР С Р С‘Р В· 16-РЎвЂљР С‘.";
        $yes = false;
      }
      if($pass != $passt && $yes == true){
        $txtError = "Р Р€Р С”Р В°Р В·Р В°Р Р…РЎвЂ№Р Вµ Р С—Р В°РЎР‚Р С•Р В»Р С‘ Р Р…Р Вµ РЎРѓР С•Р Р†Р С—Р В°Р Т‘Р В°РЎР‹РЎвЂљ.";
        $yes = false;
      }
      if((strlen($email) < 5 OR strlen($email) > 100) && $yes == true){
        $txtError = "E-mail Р Т‘Р С•Р В»Р В¶Р ВµР Р… РЎРѓР С•РЎРѓРЎвЂљР С•РЎРЏРЎвЂљРЎРЉ Р Р…Р Вµ Р СР ВµР Р…Р ВµР Вµ РЎвЂЎР ВµР С Р С‘Р В· 5-РЎвЂљР С‘ РЎРѓР С‘Р СР Р†Р С•Р В»Р С•Р Р† Р С‘ Р Р…Р Вµ Р В±Р С•Р В»Р ВµР Вµ РЎвЂЎР ВµР С Р С‘Р В· 100.";
        $yes = false;
      }
      if((!preg_match("/^[a-z0-9._-]{1,20}@(([a-z0-9-]+\.)+(com|net|org|mil|edu|gov|ru|info|biz |inc|"."name|[a-z]{2})|[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})$/is",$email)) && $yes == true){
        $txtError = "E-mail Р Р†Р Р†Р ВµР Т‘Р ВµР Р… Р Р…Р Вµ Р Р†Р ВµРЎР‚Р Р…Р С•. E-mail РЎРѓР С•Р Т‘Р ВµРЎР‚Р В¶Р С‘РЎвЂљ Р Р…Р ВµР Т‘Р С•Р С—РЎС“РЎРѓРЎвЂљР С‘Р СРЎвЂ№Р Вµ РЎРѓР С‘Р СР Р†Р С•Р В»РЎвЂ№.";      
        $yes = false;
      }
      if($yes == true){
        $email_rows_two = first('SELECT id FROM users WHERE email="%s"',$email);
          if($email_rows_two != false){
             $txtError = 'Р вЂќР В°Р Р…Р Р…РЎвЂ№Р в„– E-mail Р В·Р В°Р Р…РЎРЏРЎвЂљ Р Т‘РЎР‚РЎС“Р С–Р С‘Р С Р С—Р С•Р В»РЎРЉР В·Р С•Р Р†Р В°РЎвЂљР ВµР В»Р ВµР С.';
             $yes = false;
          }  
      }
      if((strlen($answer) < 5 OR strlen($answer) > 50) && $yes == true){
        $txtError = "Р РЋР ВµР С”РЎР‚Р ВµРЎвЂљР Р…РЎвЂ№Р в„– Р С•РЎвЂљР Р†Р ВµРЎвЂљ Р Т‘Р С•Р В»Р В¶Р ВµР Р… РЎРѓР С•РЎРѓРЎвЂљР С•РЎРЏРЎвЂљРЎРЉ Р Р…Р Вµ Р СР ВµР Р…Р ВµР Вµ РЎвЂЎР ВµР С Р С‘Р В· 5-РЎвЂљР С‘ РЎРѓР С‘Р СР Р†Р С•Р В»Р С•Р Р† Р С‘ Р Р…Р Вµ Р В±Р С•Р В»Р ВµР Вµ РЎвЂЎР ВµР С Р С‘Р В· 50-РЎвЂљР С‘.";
        $yes = false;
      }
        if($yes == true){
           if(isset($_POST['year']) && isset($_POST['month']) && isset($_POST['day'])){
              $year  = obr_chis($_POST['year']);
              $month = obr_chis($_POST['month']);
              $day   = obr_chis($_POST['day']);
              $dateYears = $year.'-'.$month.'-'.$day;
                if(empty($year) OR empty($month) OR empty($day)) $dateYears = date('Y-m-d');
           }else{
              $dateYears = date('Y-m-d');
           }
           if(isset($_POST['info']) && $_POST['info'] != ''){
              $info = obr_txt($_POST['info']);
           }else{
              $info = "...";
           }
           if($gender == 'Р СљРЎС“Р В¶РЎРѓР С”Р С•Р в„–') 
              $userGender = 1;
           else
              $userGender = 2;
           $time_newuser = time()+(60*60*24*1);
           $ip          = getenv("HTTP_X_FORWARDED_FOR"); 
           if(empty($ip) || $ip=='unknown') 
              $ip  = getenv("REMOTE_ADDR"); 
                 
           $pass   = md5($pass);//РЎв‚¬Р С‘РЎвЂћРЎР‚РЎС“Р ВµР С Р С—Р В°РЎР‚Р С•Р В»РЎРЉ
           $pass   = strrev($pass);//Р С—Р ВµРЎР‚Р ВµР Р†Р С•РЎР‚Р В°РЎвЂЎР С‘Р Р†Р В°Р ВµР С
           $pass   = $pass."b3p6f";//Р Т‘Р С•Р В±Р С•Р Р†Р В»РЎРЏР ВµР С
           $rangMy = "Р СњР С•Р Р†Р С‘РЎвЂЎР С•Р С”";
           $info   = substr($info, 0, 100);
           $answer = substr($answer, 0, 50);
           $datreg = date('Y-m-d, H:i:s');            
           $myid   = insert('users',array(
                     'login'=>$login,
                     'password'=>$pass,
                     'email'=>$email,
                     'groups'=>6,
                     'datereg'=>$datreg,
                     'avatars'=>'001',
                     'info'=>$info,
                     'gender'=>$userGender,
                     'rang'=>$rangMy,
                     'ip'=>ip2long($ip),
                     'buildmy'=>'3',
                     'newuser'=>$time_newuser,
                     'mychat'=>2));
           $provUnicuser  = first('SELECT id FROM usersunictable WHERE id=%d',$myid);
           if($provUnicuser['id'] != false) delete('usersunictable','id='.(int)$myid);           
                     insert('usersunictable',array(
                     'id'=>$myid,
                     'anserid'=>$question,
                     'ansverotv'=>$answer,
                     'birthday'=>$dateYears));
           $forum_s = "<a href='http://forum.league-of-pokemons.ru/viewforum.php?f=4' target='_blank'>Forum -> Р ВР С–РЎР‚Р С•Р Р†Р С•Р в„– Р СљР С‘РЎР‚</a>";
           $pravila_s = "<a href='/index.php?go=rule' target='_blank'>League Of Pokemons -> Р СџРЎР‚Р В°Р Р†Р С‘Р В»Р В° Р С‘Р С–РЎР‚РЎвЂ№</a>";
           $_SESSION['severeg'] = true;     
          print $obTop.'Р В Р В°Р Т‘РЎвЂ№ Р С—РЎР‚Р С‘Р Р†Р ВµРЎвЂљРЎРѓРЎвЂљР Р†Р С•Р Р†Р В°РЎвЂљРЎРЉ Р вЂ™Р В°РЎРѓ РЎвЂљРЎР‚Р ВµР Р…Р ВµРЎР‚, <b>'.$login.'</b> Р Р…Р В° Р Р…Р В°РЎв‚¬Р ВµР С РЎРѓР В°Р в„–РЎвЂљР Вµ!<br>
                        Р вЂ™РЎвЂ№ РЎС“Р Т‘Р В°РЎвЂЎР Р…Р С• Р С—РЎР‚Р С•РЎв‚¬Р В»Р С‘ РЎР‚Р ВµР С–Р С‘РЎРѓРЎвЂљРЎР‚Р В°РЎвЂ Р С‘РЎР‹!<br>
                        Р вЂўРЎРѓР В»Р С‘ Р вЂ™РЎвЂ№ Р С—Р ВµРЎР‚Р Р†РЎвЂ№Р в„– РЎР‚Р В°Р В· Р С‘Р С–РЎР‚Р В°Р ВµРЎвЂљР Вµ Р Р† Р С—Р С•Р Т‘Р С•Р В±Р Р…РЎвЂ№Р Вµ Р С‘Р С–РЎР‚РЎвЂ№, Р С—РЎР‚Р С•РЎРѓР С‘Р С Р С—Р С•РЎвЂЎР С‘РЎвЂљР В°РЎвЂљРЎРЉ Р С‘Р Р…РЎвЂљР ВµРЎР‚Р ВµРЎРѓР Р…РЎвЂ№Р Вµ РЎРѓРЎвЂљР В°РЎвЂљРЎРЉР С‘, Р С—РЎР‚Р С• Р С‘Р С–РЎР‚Р С•Р Р†Р С•Р в„– Р СР С‘РЎР‚: '.$forum_s.'<br>
                        Р С’ РЎвЂљР В°Р С” Р В¶Р Вµ, Р ВµРЎвЂ°Р Вµ РЎР‚Р В°Р В· Р С•Р В·Р Р…Р В°Р С”Р С•Р СР С‘РЎвЂљРЎРѓРЎРЏ РЎРѓ Р С—РЎР‚Р В°Р Р†Р С‘Р В»Р В°Р СР С‘ Р Р…Р В°РЎв‚¬Р ВµР в„– Р С‘Р С–РЎР‚РЎвЂ№: '.$pravila_s.'.<br>
                        <b>Р вЂ“Р ВµР В»Р В°Р ВµР С Р вЂ™Р В°Р С Р Р†РЎРѓР ВµР С–Р С• Р Р…Р В°Р С‘Р В»РЎС“РЎвЂЎРЎв‚¬Р ВµР С–Р С•, РЎС“Р Т‘Р В°РЎвЂЎР Р…Р С•Р в„– Р С‘Р С–РЎР‚РЎвЂ№!</b>
                       '.$obBattom;
          unset($_POST['login']);
          unset($_POST['password']);     
          unset($_POST['email']);    
        }else{
           print $obTopEr.$txtError.$obBattom;
        }
  }
  else
  {
    if($yes == true) print $obTopEr.'Р В Р ВµР С–Р С‘РЎРѓРЎвЂљРЎР‚Р В°РЎвЂ Р С‘РЎРЏ Р Р…Р Вµ Р СР С•Р В¶Р ВµРЎвЂљ Р В±РЎвЂ№РЎвЂљРЎРЉ Р С—РЎР‚Р С•Р Т‘Р С•Р В»Р В¶Р ВµР Р…Р В°, Р Р† Р Р†Р В°РЎв‚¬Р ВµР С Р В·Р В°Р С—РЎР‚Р С•РЎРѓР Вµ Р Р…Р Вµ РЎвЂ¦Р Р†Р В°РЎвЂљР В°Р ВµРЎвЂљ Р Т‘Р В°Р Р…Р Р…РЎвЂ№РЎвЂ¦!'.$obBattom;
      else print $obTopEr.$txt.$obBattom;
  }
}

?>