<?php
if(!empty($_POST['password_to']) && !empty($_POST['password_return']) && !empty($_POST['password_return_two'])){
  $pass = strrev(md5($_POST['password_to']));
  $pass = $pass.'b3p6f';
  $class = false;
  $okPass = true; 
  if($_POST['password_return_two'] != $_POST['password_return']) { $txt = "Р СњР С•Р Р†РЎвЂ№Р в„– Р С—Р В°РЎР‚Р С•Р В»РЎРЉ Р Р…Р Вµ РЎРѓР С•Р Р†Р С—Р В°Р Т‘Р В°Р ВµРЎвЂљ  РЎРѓ Р С—Р С•Р Р†РЎвЂљР С•РЎР‚Р Р…РЎвЂ№Р С."; $okPass = false; }
  if((strlen($_POST['password_return']) < 6 OR strlen($_POST['password_return']) > 16)){ $txt = "Р СџР В°РЎР‚Р С•Р В»РЎРЉ Р Т‘Р С•Р В»Р В¶Р ВµР Р… РЎРѓР С•РЎРѓРЎвЂљР С•РЎРЏРЎвЂљРЎРЉ Р Р…Р Вµ Р СР ВµР Р…Р ВµР Вµ РЎвЂЎР ВµР С Р С‘Р В· 6-РЎвЂљР С‘ РЎРѓР С‘Р СР Р†Р С•Р В»Р С•Р Р† Р С‘ Р Р…Р Вµ Р В±Р С•Р В»Р ВµР Вµ РЎвЂЎР ВµР С Р С‘Р В· 16-РЎвЂљР С‘."; $okPass = false; }
  if($pass != users_conect('password')) { $txt = "Р СњР Вµ Р Р†Р ВµРЎР‚Р Р…Р С• Р Р†Р Р†Р ВµР Т‘Р ВµР Р… Р С—Р В°РЎР‚Р С•Р В»РЎРЉ Р С•РЎвЂљ Р В°Р С”Р С”Р В°РЎС“Р Р…РЎвЂљР В°."; $okPass = false; }
  if($okPass == true) {
    $passRet = strrev(md5($_POST['password_return']));
    $passRet = $passRet.'b3p6f';
    $_SESSION['password']  = $passRet;
    update('users',array('password'=>$passRet),'id='.(int)$_SESSION['id']);
    $txt = "Р СџР В°РЎР‚Р С•Р В»РЎРЉ РЎС“РЎРѓР С—Р ВµРЎв‚¬Р Р…Р С• Р С‘Р В·Р СР ВµР Р…Р ВµР Р…."; $class = 'regWindowOk';
  }
print '<p class="'.($class?$class:'regWindowError').'" style="text-align: center;">'.$txt.'</p>';
} 
if(!empty($_POST['info'])){
 $okInfo = true;
 $class = false;
 if(users_conect('groups') != 1) $text = obr_txt($_POST['info']);  else $text = $_POST['info'];
 if(strlen($text) > 100){
   $txt = "Р С™Р С•Р В»Р С‘РЎвЂЎР ВµРЎРѓРЎвЂљР Р†Р С• РЎРѓР С‘Р СР Р†Р С•Р В»Р С•Р Р† Р Р† Р С‘Р Р…РЎвЂћР С•РЎР‚Р СР В°РЎвЂ Р С‘Р С‘ Р Р…Р Вµ Р СР С•Р В¶Р ВµРЎвЂљ Р С—РЎР‚Р ВµР Р†РЎвЂ№РЎв‚¬Р В°РЎвЂљРЎРЉ Р С•РЎвЂљР СР ВµРЎвЂљР С”Р С‘ 100."; $okInfo = false;
 }
 if($okInfo == true) {
    update('users',array('info'=>$text),'id='.(int)$_SESSION['id']);
    $txt = "Р ВР Р…РЎвЂћР С•РЎР‚Р СР В°РЎвЂ Р С‘РЎРЏ РЎС“РЎРѓР С—Р ВµРЎв‚¬Р Р…Р С• Р С‘Р В·Р СР ВµР Р…Р ВµР Р…Р В°."; $class = 'regWindowOk';
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
    $txt = "Р вЂ™Р В°РЎв‚¬ Р В°Р Р†Р В°РЎвЂљР В°РЎР‚ РЎС“РЎРѓР С—Р ВµРЎв‚¬Р Р…Р С• Р С‘Р В·Р СР ВµР Р…Р ВµР Р…."; $class = 'regWindowOk';
  }
print '<p class="'.($class?$class:'regWindowError').'" style="text-align: center;">'.$txt.'</p>';
}
$ava =  users_conect('avatars');
if($ava < 10) $ava = '00'.$ava; elseif($ava < 100 && $ava > 9) $ava = '0'.$ava; else $ava = $ava;
?>
<h1>Р вЂєР С‘РЎвЂЎР Р…РЎвЂ№Р Вµ Р Т‘Р В°Р Р…Р Р…РЎвЂ№Р Вµ РЎвЂљРЎР‚Р ВµР Р…Р ВµРЎР‚Р В°: <?php echo $_SESSION['login']; ?>.</h1>
<table width="100%">
  <tr>
    <td width="215">
      <h2>Р вЂ™Р В°РЎв‚¬ Р В°Р Р†Р В°РЎвЂљР В°РЎР‚:<h2>
    </td>
    <td>
      <h2>Р вЂ™Р В°Р В¶Р Р…РЎвЂ№Р Вµ Р Т‘Р В°Р Р…Р Р…РЎвЂ№Р Вµ:<h2>
    </td>
  </tr>
  <tr width="215">
    <td>                                                                            
      <img alt='Р С’Р Р†Р В°РЎвЂљР В°РЎР‚Р С”Р В°: <?php echo $_SESSION['login']; ?>' src='img/ava/<?php echo $ava;?>.png' width='215' height='410'>
    </td>
    <td>
      <h3>Р ВР В·Р СР ВµР Р…Р С‘РЎвЂљРЎРЉ Р С—Р В°РЎР‚Р С•Р В»РЎРЉ:<h3>
        <form action='' method='post'>
          <b><i><font color="gold">Р вЂ™Р Р†Р ВµР Т‘Р С‘РЎвЂљР Вµ РЎРѓРЎвЂљР В°РЎР‚РЎвЂ№Р в„– Р С—Р В°РЎР‚Р С•Р В»РЎРЉ:</i></b></font><br>
          <input style="border:2px solid #000;" name='password_to' type='password'>  <br>
          <b><i><font color="gold">Р вЂ™Р Р†Р ВµР Т‘Р С‘РЎвЂљР Вµ Р Р…Р С•Р Р†РЎвЂ№Р в„– Р С—Р В°РЎР‚Р С•Р В»РЎРЉ:</i></b></font> <br>
          <input style="border:2px solid #000;"name='password_return' type='password' maxlength="16"><br>
          <b><i><font color="gold">Р вЂ™Р Р†Р ВµР Т‘Р С‘РЎвЂљР Вµ Р Р…Р С•Р Р†РЎвЂ№Р в„– Р С—Р В°РЎР‚Р С•Р В»РЎРЉ Р ВµРЎвЂ°Р Вµ РЎР‚Р В°Р В·:</i></b></font> <br>
          <input style="border:2px solid #000;" name='password_return_two' type='password' maxlength="16"><br>
          <input style="border:2px solid #000;margin:3px;font-weight:bold;" type='submit' name='submit' value='Р ВР В·Р СР ВµР Р…Р С‘РЎвЂљРЎРЉ'>
        </form>
      <br>
      <h2>Р ВР Р…РЎвЂћР С•РЎР‚Р СР В°РЎвЂ Р С‘РЎРЏ Р С• РЎРѓР ВµР В±Р Вµ:</h2><br>                       
      <form action='' method='post'> 
        <input style="border:2px solid #000;margin:3px;padding:2px;font-weight:bold;width:80%;height:20px;" name='info' type='text' value='<?php echo users_conect('info');?>' maxlength="100">
        <input style="border:2px solid #000;margin:3px;padding:2px;font-weight:bold;" type='submit' name='submit' value='Р ВР В·Р СР ВµР Р…Р С‘РЎвЂљРЎРЉ'>
      </form>          
      <br>
      <?php 
        if(users_conect('moderation') == 1)  print '<a href="game.php?go=moderpanel" target="_blank"><h2>Р СџР В°Р Р…Р ВµР В»РЎРЉ Р СР С•Р Т‘Р ВµРЎР‚Р В°РЎвЂљР С•РЎР‚Р В°</h2></a><br>'; 
        if(users_conect('police') == 1) print '<a href="game.php?go=policepanel" target="_blank"><h2>Р СџР В°Р Р…Р ВµР В»РЎРЉ Р С—Р С•Р В»Р С‘РЎвЂ Р ВµР в„–РЎРѓР С”Р С•Р С–Р С•</h2></a><br>';  
      ?>
      <a href='javascript:' onclick='this.style.display="none"; document.getElementById("divAva").style.display="block"'><b><h2>Р РЋР СР ВµР Р…Р С‘РЎвЂљРЎРЉ Р В°Р Р†Р В°РЎвЂљР В°РЎР‚</h2></b></a> 
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
            <input style="border:2px solid #000;margin:3px;font-weight:bold;" type='submit' name='submit' value='Р ВР В·Р СР ВµР Р…Р С‘РЎвЂљРЎРЉ'>
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
            <input style="border:2px solid #000;margin:3px;font-weight:bold;" type='submit' name='submit' value='Р ВР В·Р СР ВµР Р…Р С‘РЎвЂљРЎРЉ'>
          </center>
        </td>
      </tr>
     </form>
    </table>
</div>
<?php
}
?>