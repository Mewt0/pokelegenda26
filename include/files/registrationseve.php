<?php
if((!empty($_GET['zap_login']) OR !empty($_GET['zap_email'])) && ($_GET['severeg'] == false)){
Header('Content-Type: text/css;charset=Windows-1251');
  if($_GET['zap_login'] != false && $_GET['zap_email'] == false){
      $aLogin = $_GET['zap_login'];
      if(!preg_match("|^[a-z_-]+$|i", $aLogin)){
        echo "NOЛогин может содержать только латинские буквы и знаки: _ , -. <br>При этом, буквы не должны повторятся более 2-х раз, а символы более одного раза.";      
        exit;
      }
      if(strlen($aLogin) < 3 or strlen($aLogin) > 16){
        echo "NOЛогин должен состоять не менее чем из 3-х символов и не более чем из 16-ти.";
        exit;
      }
      if (preg_match("/(-)\\1/",$aLogin) || preg_match("/(_)\\1/",$aLogin) || preg_match("/_-/i",$aLogin) || preg_match("/-_/i",$aLogin)){
        echo "NOCимволы не должны стоять подряд более одного раза.";      
        exit;
      }
      if (preg_match("/^-/",$aLogin) ||  preg_match("/-$/",$aLogin) || preg_match("/^_/",$aLogin) ||  preg_match("/_$/",$aLogin)){
        echo "NOЛогин не должен начинаться или заканчиваться на символ.";      
        exit;
      }
      for($n=0; $n!=strlen($aLogin)-2; $n++){ 
        if($aLogin[$n]==$aLogin[$n+1] && $aLogin[$n]==$aLogin[$n+2]){
            echo 'NOБуквы не должны стоять подряд более двух раз.';
            exit;
        }
      }       
      $aLogin = trim($_GET['zap_login']);
      $aLogin = stripslashes($aLogin);
      $aLogin = htmlspecialchars($aLogin); 
      $logined = first('SELECT id FROM users where MATCH (login) AGAINST ("%s" IN BOOLEAN MODE)',$aLogin);
      if(!$logined['id']) echo "OK"; 
        else 
          echo "NOДанный логин уже занят.";
     exit;
    }
  if($_GET['zap_email'] != false && $_GET['zap_login'] == false){
      $aMail = $_GET['zap_email'];
      if(strlen($aMail) < 5 or strlen($aMail) > 100){
        echo "NOE-mail должен состоять не менее чем из 5-ти символов и не более чем из 100.";
        exit;
      }                     
      if(!preg_match("/^[a-z0-9._-]{1,20}@(([a-z0-9-]+\.)+(com|net|org|mil|edu|gov|ru|info|biz |inc|"."name|[a-z]{2})|[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})$/is",$aMail)){
        echo "NOE-mail введен не верно.";      
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
        echo "NOНа вашем IP адресе уже имеется аккаунт.";      
        exit;      
      }
      if(!$mailed['id']) echo "OK"; 
        else 
          echo "NOДанный E-mail уже занят.";
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
    $txt = 'Извините, но вы уже регистрировались сегодня!';
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
        $txtError = "Форма регистрации заполнена не верно.";
        $yes = false;
      }
      if((md5($codes) != $trucodes) && ($yes == true)){
        $txtError = "Вы ввели не верные цифры с картинки.";
        $yes = false;
      }
      if($pravila == false || $pravila == '' && $yes == true){
        $txtError = "Вы не согласились с правилами игры.";
        $yes = false;
      }    
      if(!preg_match("|^[a-z_-]+$|i", $login) && $yes == true){
        $txtError = "Логин может содержать только латинские буквы и знаки: _ , -. <br>При этом, буквы не должны повторятся более 2-х раз, а символы более одного раза.";      
        $yes = false;
      }
      if((strlen($login) < 3 OR strlen($login) > 16) && $yes == true){
        $txtError =  "Логин должен состоять не менее чем из 3-х символов и не более чем из 16-ти.";
        $yes = false;
      }
      if((preg_match("/(-)\\1/",$login) || preg_match("/(_)\\1/",$login) || preg_match("/_-/i",$login) || preg_match("/-_/i",$login)) && $yes == true){
        $txtError = "Логин содержит символы, которые повторяются более одного раза подряд.";      
        $yes = false;
      }
      if((preg_match("/^-/",$login) ||  preg_match("/-$/",$login) || preg_match("/^_/",$login) ||  preg_match("/_$/",$login)) && $yes == true){
        $txtError = "Логин не должен начинаться или заканчиваться на символ.";      
        $yes = false;
      }
      if($yes == true){
        for($n=0; $n!=strlen($login)-2; $n++){ 
          if($login[$n]==$login[$n+1] && $login[$n]==$login[$n+2]){
            $txtError = 'Логин содержит буквы, которые повторяются более двух раз подряд.';
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
             $txtError = 'На вашем IP адресе уже имеется аккаунт.';
             $yes = false;   
        }
      }
      if($yes == true){
        $logins_rows = first('SELECT id FROM users WHERE MATCH (login) AGAINST ("%s")',$login);
        $logins_rows_two = first('SELECT id FROM users WHERE login="%s"',$login);
          if($logins_rows['id'] != false || $logins_rows_two != false){
             $txtError = 'Данный логин занят другим пользователем.';
             $yes = false;
          }  
      }
      if((strlen($pass) < 6 OR strlen($pass) > 16) && $yes == true){
        $txtError = "Пароль должен состоять не менее чем из 6-ти символов и не более чем из 16-ти.";
        $yes = false;
      }
      if($pass != $passt && $yes == true){
        $txtError = "Указаные пароли не совпадают.";
        $yes = false;
      }
      if((strlen($email) < 5 OR strlen($email) > 100) && $yes == true){
        $txtError = "E-mail должен состоять не менее чем из 5-ти символов и не более чем из 100.";
        $yes = false;
      }
      if((!preg_match("/^[a-z0-9._-]{1,20}@(([a-z0-9-]+\.)+(com|net|org|mil|edu|gov|ru|info|biz |inc|"."name|[a-z]{2})|[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})$/is",$email)) && $yes == true){
        $txtError = "E-mail введен не верно. E-mail содержит недопустимые символы.";      
        $yes = false;
      }
      if($yes == true){
        $email_rows_two = first('SELECT id FROM users WHERE email="%s"',$email);
          if($email_rows_two != false){
             $txtError = 'Данный E-mail занят другим пользователем.';
             $yes = false;
          }  
      }
      if((strlen($answer) < 5 OR strlen($answer) > 50) && $yes == true){
        $txtError = "Секретный ответ должен состоять не менее чем из 5-ти символов и не более чем из 50-ти.";
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
           if($gender == 'Мужской') 
              $userGender = 1;
           else
              $userGender = 2;
           $time_newuser = time()+(60*60*24*1);
           $ip          = getenv("HTTP_X_FORWARDED_FOR"); 
           if(empty($ip) || $ip=='unknown') 
              $ip  = getenv("REMOTE_ADDR"); 
                 
           $pass   = md5($pass);//шифруем пароль
           $pass   = strrev($pass);//переворачиваем
           $pass   = $pass."b3p6f";//добовляем
           $rangMy = "Новичок";
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
           $forum_s = "<a href='http://forum.league-of-pokemons.ru/viewforum.php?f=4' target='_blank'>Forum -> Игровой Мир</a>";
           $pravila_s = "<a href='/index.php?go=rule' target='_blank'>League Of Pokemons -> Правила игры</a>";
           $_SESSION['severeg'] = true;     
          print $obTop.'Рады приветствовать Вас тренер, <b>'.$login.'</b> на нашем сайте!<br>
                        Вы удачно прошли регистрацию!<br>
                        Если Вы первый раз играете в подобные игры, просим почитать интересные статьи, про игровой мир: '.$forum_s.'<br>
                        А так же, еще раз ознакомится с правилами нашей игры: '.$pravila_s.'.<br>
                        <b>Желаем Вам всего наилучшего, удачной игры!</b>
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
    if($yes == true) print $obTopEr.'Регистрация не может быть продолжена, в вашем запросе не хватает данных!'.$obBattom;
      else print $obTopEr.$txt.$obBattom;
  }
}

?>