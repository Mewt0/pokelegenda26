<?php
if(!empty($_POST['password_to']) && !empty($_POST['password_return']) && !empty($_POST['password_return_two'])){
  $pass = strrev(md5($_POST['password_to']));
  $pass = $pass.'b3p6f';
  $class = false;
  $okPass = true; 
  if($_POST['password_return_two'] != $_POST['password_return']) { $txt = "Новый пароль не совпадает  с повторным."; $okPass = false; }
  if((strlen($_POST['password_return']) < 6 OR strlen($_POST['password_return']) > 16)){ $txt = "Пароль должен состоять не менее чем из 6-ти символов и не более чем из 16-ти."; $okPass = false; }
  if($pass != users_conect('password')) { $txt = "Не верно введен пароль от аккаунта."; $okPass = false; }
  if($okPass == true) {
    $passRet = strrev(md5($_POST['password_return']));
    $passRet = $passRet.'b3p6f';
    $_SESSION['password']  = $passRet;
    update('users',array('password'=>$passRet),'id='.(int)$_SESSION['id']);
    $txt = "Пароль успешно изменен."; $class = 'regWindowOk';
  }
print '<p class="'.($class?$class:'regWindowError').'" style="text-align: center;">'.$txt.'</p>';
} 
if(!empty($_POST['info'])){
 $okInfo = true;
 $class = false;
 if(users_conect('groups') != 1) $text = obr_txt($_POST['info']);  else $text = $_POST['info'];
 if(strlen($text) > 100){
   $txt = "Количество символов в информации не может превышать отметки 100."; $okInfo = false;
 }
 if($okInfo == true) {
    update('users',array('info'=>$text),'id='.(int)$_SESSION['id']);
    $txt = "Информация успешно изменена."; $class = 'regWindowOk';
  }
print '<p class="'.($class?$class:'regWindowError').'" style="text-align: center;">'.$txt.'</p>';
}
if(!empty($_POST['avatar']) && $_POST['avatar'] > 0){
   $okAva = true;
   $class = false;
   $info = $_POST['avatar']; 
    if(users_conect('gender') == 1){
      if ($info=='1') {$info = '001';} else 
      if ($info=='2') {$info = '002';} else
      if ($info=='3') {$info = '003';} else
      if ($info=='4') {$info = '004';} else 
      if ($info=='5') {$info = '005';} else
      if ($info=='6') {$info = '006';} else
      if ($info=='7') {$info = '007';} else 
      if ($info=='8') {$info = '008';} else
      if ($info=='9') {$info = '009';} else
      if ($info=='14'){$info = '014';} else
      if ($info=='16'){$info = '016';} else 
      if ($info=='64'){$info = '064';} else { $info = '001'; }      
    }else{
      if ($info=='15'){$info = '015';} else 
      if ($info=='20'){$info = '020';} else
      if ($info=='23'){$info = '023';} else
      if ($info=='24'){$info = '024';} else
      if ($info=='27'){$info = '027';} else
      if ($info=='28'){$info = '028';} else
      if ($info=='39'){$info = '039';} else
      if ($info=='40'){$info = '040';} else    
      if ($info=='50'){$info = '050';} else
      if ($info=='51'){$info = '051';} else
      if ($info=='52'){$info = '052';} else
      if ($info=='53'){$info = '053';} else
      if ($info=='54'){$info = '054';} else
      if ($info=='55'){$info = '055';} else
      if ($info=='56'){$info = '056';} else  
      if ($info=='62'){$info = '062';} else 
      if ($info=='77'){$info = '077';} else 
      if ($info=='78'){$info = '078';} else{ $info = '001'; } 
    }
 if($okAva == true) {
    update('users',array('avatars'=>$info),'id='.(int)$_SESSION['id']);
    $txt = "Ваш аватар успешно изменен."; $class = 'regWindowOk';
  }
print '<p class="'.($class?$class:'regWindowError').'" style="text-align: center;">'.$txt.'</p>';
}
$ava =  users_conect('avatars');
if($ava < 10) $ava = '00'.$ava; elseif($ava < 100 && $ava > 9) $ava = '0'.$ava; else $ava = $ava;
?>
<h1>Личные данные тренера: <?php echo $_SESSION['login']; ?>.</h1>
<table width="100%">
  <tr>
    <td width="215">
      <h2>Ваш аватар:<h2>
    </td>
    <td>
      <h2>Важные данные:<h2>
    </td>
  </tr>
  <tr width="215">
    <td>                                                                            
      <img alt='Аватарка: <?php echo $_SESSION['login']; ?>' src='img/ava/<?php echo $ava;?>.png' width='215' height='410'>
    </td>
    <td>
      <h3>Изменить пароль:<h3>
        <form action='' method='post'>
          <b><i><font color="gold">Введите старый пароль:</i></b></font><br>
          <input style="border:2px solid #000;" name='password_to' type='password'>  <br>
          <b><i><font color="gold">Введите новый пароль:</i></b></font> <br>
          <input style="border:2px solid #000;"name='password_return' type='password' maxlength="16"><br>
          <b><i><font color="gold">Введите новый пароль еще раз:</i></b></font> <br>
          <input style="border:2px solid #000;" name='password_return_two' type='password' maxlength="16"><br>
          <input style="border:2px solid #000;margin:3px;font-weight:bold;" type='submit' name='submit' value='Изменить'>
        </form>
      <br>
      <h2>Информация о себе:</h2><br>                       
      <form action='' method='post'> 
        <input style="border:2px solid #000;margin:3px;padding:2px;font-weight:bold;width:80%;height:20px;" name='info' type='text' value='<?php echo users_conect('info');?>' maxlength="100">
        <input style="border:2px solid #000;margin:3px;padding:2px;font-weight:bold;" type='submit' name='submit' value='Изменить'>
      </form>          
      <br>
      <?php 
        if(users_conect('moderation') == 1)  print '<a href="game.php?go=moderpanel" target="_blank"><h2>Панель модератора</h2></a><br>'; 
        if(users_conect('police') == 1) print '<a href="game.php?go=policepanel" target="_blank"><h2>Панель полицейского</h2></a><br>';  
      ?>
      <a href='javascript:' onclick='this.style.display="none"; document.getElementById("divAva").style.display="block"'><b><h2>Сменить аватар</h2></b></a> 
    </td>
  </tr>             
</table>
<?php 
if(users_conect('gender') == 1){  
?>   
<div id="divAva" style='display:none'>
  <hr>
    <table width='100%' border=1>
     <form name="avaup" action="" method="POST">
      <tr>
        <td width='33%'>
          <center>
            <input type="radio" name="avatar" value="001" style="cursor:hand" id="A1" checked>
          </center>
        </td>
        <td width='33%'>
          <center>
            <input type="radio" name="avatar" value="002" style="cursor:hand" id="A2">
          </center>
        </td>
        <td width='33%'>
          <center>
            <input type="radio" name="avatar" value="003" style="cursor:hand" id="A3">
          </center>
        </td>
      </tr>
      <tr>
        <td>
          <center>
            <LABEL FOR="A1">
              <img src='img/ava/001.png' width='215' height='410'> 
            </LABEL>
          </center>
        </td>
        <td>
          <center>
            <LABEL FOR="A2">
              <img src='img/ava/002.png' width='215' height='410'>  
            </LABEL>
          </center>
        </td>
        <td>
          <center>
            <LABEL FOR="A3">
              <img src='img/ava/003.png' width='215' height='410'> 
            </LABEL>
          </center>
        </td>
      </tr>
      <tr>
        <td>
          <center>
            <input type="radio" name="avatar" value="004" style="cursor:hand" id="A4">
          </center>
        </td>
        <td width='33%'>
          <center>
            <input type="radio" name="avatar" value="005" style="cursor:hand" id="A5">
          </center>
        </td>
        <td width='33%'>
          <center>
            <input type="radio" name="avatar" value="006" style="cursor:hand" id="A6">
          </center>
        </td>
      </tr>
      <tr>
        <td>
          <center>
            <LABEL FOR="A4">
              <img src='img/ava/004.png' width='215' height='410'>
            </LABEL>
          </center>
        </td>
        <td>
          <center>
            <LABEL FOR="A5">
              <img src='img/ava/005.png' width='215' height='410'>
            </LABEL>
          </center>
        </td>
        <td>
          <center>
            <LABEL FOR="A6">
              <img src='img/ava/006.png' width='215' height='410'>
            </LABEL>
          </center>
        </td>
      </tr>
      <tr>
        <td>
          <center>
            <input type="radio" name="avatar" value="007" style="cursor:hand" id="A7">
          </center>
        </td>
        <td width='33%'>
          <center>
            <input type="radio" name="avatar" value="008" style="cursor:hand" id="A8">
          </center>
        </td>
        <td width='33%'>
          <center>
            <input type="radio" name="avatar" value="009" style="cursor:hand" id="A9">
          </center>
        </td>
      </tr>
      <tr>
        <td>
          <center>
            <LABEL FOR="A7">
              <img src='img/ava/007.png' width='215' height='410'>
            </LABEL>
          </center>
        </td>
        <td>
          <center>
            <LABEL FOR="A8">
              <img src='img/ava/008.png' width='215' height='410'>
            </LABEL>
          </center>
        </td>
        <td>
          <center>
            <LABEL FOR="A9">
              <img src='img/ava/009.png' width='215' height='410'>
            </LABEL>
          </center>
        </td>
      </tr>
      <tr>
        <td>
          <center>
            <LABEL FOR="A10">
              <input type="radio" name="avatar" value="014" style="cursor:hand" id="A10"> 
            </LABEL>
          </center>
        </td>
        <td width='33%'>
          <center>
            <LABEL FOR="A11">
              <input type="radio" name="avatar" value="016" style="cursor:hand" id="A11">
            </LABEL>
          </center>
        </td>
        <td width='33%'>
          <center>
            <LABEL FOR="A12">
              <input type="radio" name="avatar" value="064" style="cursor:hand" id="A12"> 
            </LABEL>
          </center>
        </td>
      </tr>
      <tr>
        <td>
          <center>
            <LABEL FOR="A10"> 
              <img src='img/ava/014.png'  width='215' height='410'>  
            </LABEL>
          </center>
        </td>
        <td>
          <center>
            <LABEL FOR="A11"> 
              <img src='img/ava/016.png'  width='250' height='410'>  
            </LABEL>
          </center>
        </td>
        <td>
          <center>
            <LABEL FOR="A12"> 
              <img src='img/ava/064.png'  width='250' height='410'>  
            </LABEL>
          </center>
        </td>
      </tr>
      <tr align="center">
        <td colspan="3" align="center">
          <center>
            <input style="border:2px solid #000;margin:3px;font-weight:bold;" type='submit' name='submit' value='Изменить'>
          </center>
        </td>
      </tr>
     </form>
    </table>
  <br>
</div>
<?php
} else {
?>
<div id="divAva" style='display:none'>
  <hr>
    <table width='100%'>
     <form name="avaup" action="" method="POST">
      <tr>
        <td width='33%'>
          <center>
            <input type="radio" name="avatar" value="031" style="cursor:hand" id="A1" checked>
          </center>
        </td>
        <td width='33%'>
          <center>
            <input type="radio" name="avatar" value="012" style="cursor:hand" id="A2">
          </center>
        </td>
        <td width='33%'>
          <center>
            <input type="radio" name="avatar" value="015" style="cursor:hand" id="A3">
          </center>
        </td>
      </tr>
      <tr>
        <td>
          <center>
            <LABEL FOR="A1"> 
              <img src='img/ava/031.png' width='215' height='410'> 
            </LABEL>
          </center>
        </td>
        <td>
          <center>
            <LABEL FOR="A2"> 
              <img src='img/ava/012.png' width='215' height='410'> 
            </LABEL>
          </center>
        </td>
        <td>
          <center>
            <LABEL FOR="A3">
              <img src='img/ava/015.png' width='215' height='410'> 
            </LABEL>
          </center>
        </td>
      </tr>
      <tr>
        <td>
          <center>
            <input type="radio" name="avatar" value="020" style="cursor:hand" id="A4">
          </center>
        </td>
        <td width='33%'>
          <center>
            <input type="radio" name="avatar" value="023" style="cursor:hand" id="A5">
          </center>
        </td>
        <td width='33%'>
          <center>
            <input type="radio" name="avatar" value="024" style="cursor:hand" id="A6">
          </center>
        </td>
      </tr>
      <tr>
        <td>
          <center>
            <LABEL FOR="A4"> 
              <img src='img/ava/020.png' width='215' height='410'> 
            </LABEL>
          </center>
        </td>
        <td>
          <center>
            <LABEL FOR="A5">
              <img src='img/ava/023.png' width='215' height='410'> 
            </LABEL>
          </center>
        </td>
        <td>
          <center>
            <LABEL FOR="A6">
              <img src='img/ava/024.png' width='215' height='410'> 
            </LABEL>
          </center>
        </td>
      </tr>
      <tr>
        <td>
          <center>
            <input type="radio" name="avatar" value="027" style="cursor:hand" id="A7">
          </center>
        </td>
        <td width='33%'>
          <center>
            <input type="radio" name="avatar" value="028" style="cursor:hand" id="A8">
          </center>
        </td>
        <td width='33%'>
          <center>
            <input type="radio" name="avatar" value="039" style="cursor:hand" id="A9">
          </center>
        </td>
      </tr>
      <tr>
        <td>
          <center>
            <LABEL FOR="A7">
              <img src='img/ava/027.png' width='215' height='410'> 
            </LABEL>
          </center>
        </td>
        <td>
          <center>
            <LABEL FOR="A8">
              <img src='img/ava/028.png' width='250' height='410'> 
            </LABEL>
          </center>
        </td>
        <td>
          <center>
            <LABEL FOR="A9">
              <img src='img/ava/039.png' width='215' height='410'> 
            </LABEL>
          </center>
        </td>
      </tr>
      <tr>
        <td>
          <center>
            <input type="radio" name="avatar" value="040" style="cursor:hand" id="A10">
          </center>
        </td>
        <td>
          <center>
            <input type="radio" name="avatar" value="050" style="cursor:hand" id="A11">
          </center>
        </td>
        <td>
          <center>
            <input type="radio" name="avatar" value="051" style="cursor:hand" id="A12">
          </center>
        </td>
      </tr>
      <tr>
        <td>
          <center>
            <LABEL FOR="A10">
              <img src='img/ava/040.png' width='215' height='410'> 
            </LABEL>
          </center>
        </td>
        <td>
          <center>
            <LABEL FOR="A11"> 
              <img src='img/ava/050.png' width='215' height='410'> 
            </LABEL>
          </center>
        </td>
        <td>
          <center>
             <LABEL FOR="A12"> 
                <img src='img/ava/051.png' width='215' height='410'> 
             </LABEL>
          </center>
        </td>
      </tr>
      <tr>
        <td>
          <center>
            <input type="radio" name="avatar" value="052" style="cursor:hand" id="A13">
          </center>
        </td>
        <td>
          <center>
            <input type="radio" name="avatar" value="053" style="cursor:hand" id="A14">
          </center>
        </td>
        <td>
          <center>
            <input type="radio" name="avatar" value="054" style="cursor:hand" id="A15">
          </center>
        </td>
      </tr>
      <tr>
        <td>
          <center>
            <LABEL FOR="A13"> 
              <img src='img/ava/052.png' width='215' height='410'> 
            </LABEL>
          </center>
        </td>
        <td>
          <center>
            <LABEL FOR="A14"> 
              <img src='img/ava/053.png' width='215' height='410'> 
            </LABEL>
          </center>
        </td>
        <td>
          <center>
            <LABEL FOR="A15"> 
              <img src='img/ava/054.png' width='215' height='410'> 
            </LABEL>
          </center>
        </td>
      </tr>
      <tr>
        <td>
          <center>
            <input type="radio" name="avatar" value="055" style="cursor:hand" id="A16">
          </center>
        </td>
        <td>
          <center>
            <input type="radio" name="avatar" value="056" style="cursor:hand" id="A17">
          </center>
        </td>
        <td>
          <center>
            <input type="radio" name="avatar" value="061" style="cursor:hand" id="A18">
          </center>
        </td>
      </tr>
      <tr>
        <td>
          <center>
            <LABEL FOR="A16"> 
              <img src='img/ava/055.png' width='215' height='410'> 
            </LABEL>
          </center>
        </td>
        <td>
          <center>
            <LABEL FOR="A17"> 
              <img src='img/ava/056.png' width='215' height='410'> 
            </LABEL>
          </center>
        </td>
        <td>
          <center>
            <LABEL FOR="A18"> 
              <img src='img/ava/061.png' width='215' height='410'> 
            </LABEL>
          </center>
        </td>
      </tr>
      <tr>
        <td>
          <center>
            <input type="radio" name="avatar" value="062" style="cursor:hand" id="A19">
          </center>
        </td>
        <td>
          <center>
            <input type="radio" name="avatar" value="077" style="cursor:hand" id="A20">
          </center>
        </td>
        <td>
          <center>
            <input type="radio" name="avatar" value="078" style="cursor:hand" id="A21">
          </center>
        </td>
      </tr>
      <tr>
        <td>
          <center>
           <LABEL FOR="A19">  
              <img src='img/ava/062.png' width='215' height='410'> 
           </LABEL>
          </center>
        </td>
        <td>
          <center>
           <LABEL FOR="A20">  
            <img src='img/ava/077.png' width='215' height='410'> 
           </LABEL>
          </center>
        </td>
        <td>
          <center>
           <LABEL FOR="A21">  
            <img src='img/ava/078.png' width='215' height='410'> 
           </LABEL>
          </center>
        </td>
      </tr>
      <tr>
        <td colspan="3">
          <center>
            <input style="border:2px solid #000;margin:3px;font-weight:bold;" type='submit' name='submit' value='Изменить'>
          </center>
        </td>
      </tr>
     </form>
    </table>
</div>
<?php
}
?>