<?php
function level_clan($id, $ener, $rep, $lvl){
  $lvl_res = false;
  if ( $ener>=0)                       $lvl_res = 1;
  if ( $ener>=60)                      $lvl_res = 2;
  if ( $ener>=250    && $rep >=100)    $lvl_res = 3;
  if ( $ener>=1000   && $rep >=500)    $lvl_res = 4;
  if ( $ener>=5000   && $rep >=1000)   $lvl_res = 5;
  if ( $ener>=10000  && $rep >=5000)   $lvl_res = 6;
  if ( $ener>=20000  && $rep >=10000)  $lvl_res = 7;
  if ( $ener>=35000  && $rep >=15000)  $lvl_res = 8;
  if ( $ener>=80000  && $rep >=30000)  $lvl_res = 9;
  if ( $ener>=500000 && $rep >=100000) $lvl_res = 10;
 return  ($lvl_res?update('clans',array('clan_lvl'=>$lvl_res),'id_clan='.(int)$id):false);
}
if(empty($_GET['id'])){
?>
<center><h1>Топ - кланы:</h1></center>
<div align="center">
<table cellspacing='0' cellpadding='2' width='75%' align='center'>
	<tr>
	    <td style='color:#000;' width='45'><b>Место:</b></td>
	    <td colspan='2' style='color:#000;' align="center"><b>Спрайт, название:</b></td>
	    <td align="center" style='color:#000;'><b>Уровень:</b></td>
	</tr>
<?php
$clans = select('SELECT * FROM clans ORDER BY clan_lvl DESC, clan_reputation DESC, fond_energi DESC');
  $noom = 0;
  foreach($clans as $clansrow){
    $noom++;
    $color = ($noom == 1?"#ffd700":(($noom == 2)?'#c0c0c0':($noom == 3?'#cd7f32':"#fff")));
    update('clans',array('clan_mesto'=>$noom),'id_clan='.(int)$clansrow['id_clan']);
    level_clan($clansrow['id_clan'], $clansrow['fond_energi'], $clansrow['clan_reputation'], $clansrow['clan_lvl']);
?>
	<tr style='cursor:pointer' onmouseover='this.bgColor="#000"' onmouseout='this.bgColor=""' onclick="window.open('game.php?go=clans&id=<?echo $clansrow['id_clan'];?>', 'clan', 'fullscreen=no,scrollbars=yes,width=650,height=550'); return false;">
	    <td style='color:<?=$color;?>; padding:3px;' align='center'><?=$noom;?></td>
	    <td width=37 style='color:<?=$color;?>; padding:3px;'><img src=img/clan/<?=$clansrow['clan_img'];?>.png width=32 height=32></td>
	    <td style='color:<?=$color;?>; padding:3px;'><?=$clansrow['clan_name'];?></td>
	    <td align=center style='color:<?=$color;?>; padding:3px;'><?=$clansrow['clan_lvl'];?></td>
	</tr>
<?php
  }
?>
</table>
</div>
<?php
}else{
if($_GET['id'] <= 0) die();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; Charset=Windows-1251" />
<style>
body, html {
 background-image: url('/css/img/brushed_alu_dark.jpg');
 position: relative; 
 margin:0 0 0 0;
 padding:0 0 0 0;
 color: #000000; 
}
div.clan{
  background: #727272;
  background: -moz-linear-gradient(top,  #727272 1%, #bcbcbc 100%);
  background: -webkit-gradient(linear, left top, left bottom, color-stop(1%,#727272), color-stop(100%,#bcbcbc));
  background: -webkit-linear-gradient(top,  #727272 1%,#bcbcbc 100%);
  background: -o-linear-gradient(top,  #727272 1%,#bcbcbc 100%);
  background: -ms-linear-gradient(top,  #727272 1%,#bcbcbc 100%);
  background: linear-gradient(to bottom,  #727272 1%,#bcbcbc 100%);
  filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#727272', endColorstr='#bcbcbc',GradientType=0 );
  position: relative;
  top: 10px;
  left: 10px;
  overflow: auto;
  width: 95%;
  height: 520px;
  border: 2px solid #000;
  z-index: 650;
  -moz-border-radius: 10px;
  -webkit-border-radius: 10px;
  border-radius: 10px;
  text-align:center;
  -moz-box-shadow: 0 0 30px #fff;
  -webkit-box-shadow: 0 0 30px #fff; 
  box-shadow: 0 0 30px #fff; 
}
div.clan .nameClan{
 font-size: 40px;
 font-family: Geneva, 'Comic Sans', cursive ;
 text-shadow: #000 1px 2px 3px; 
  
}
div.clan .otherInf{
 text-align:center;
 font-size: 19px;
 color: #000;
}
div.clan .countUsers{
  border: 2px solid #000;
  overflow: auto;
  width: 78%;
  height: 250px;
  padding: 6px;
  padding-top: 18px;
  padding-left: 10px;
  -moz-border-radius: 10px;
  -webkit-border-radius: 10px;
  border-radius: 10px;
}
div.clan .inf{
  border: 2px solid #000;
  overflow: auto;
  margin-top: 8px;
  width: 78%;
  height: 85px;
  padding: 6px;
  padding-top: 3px;
  padding-left: 10px;
  -moz-border-radius: 10px;
  -webkit-border-radius: 10px;
  border-radius: 10px;
}
A:link, A:visited {color: #fff; text-decoration:none}
A:hover, A:active {color: #000000; text-decoration:underline}

INPUT,TEXTAREA,SELECT {
        background-color: Ivory;
        font: 12pt Tahoma;
        font-weight: bold;
        BORDER: #000 2px solid;
        color: #000000;
}
#errPost{
 background: #ffc0cb;
 position: absolute;
 padding: 5px;
 font-weight: bold;
 padding-right: 10px;
 padding-bottom: 8px;
 top:0px;
 left:0px;
 -moz-border-radius: 0px 0px 5px 0px; 
 -webkit-border-radius: 0px 0px 5px 0px; 
 border-radius: 0px 0px 5px 0px;
 filter: alpha(opacity=90); 
 opacity: 0.9;
 -webkit-opacity: 0.9; 
 -moz-opacity: 0.9; 
 z-index: 700;
}
.postError:hover:after{
   content: ' X';
   position: relative;
   color: brown;
   right: -6px;
}
#okPost{
 background: #03c03c;
 position: absolute;
 padding: 5px;
 font-weight: bold;
 padding-right: 10px;
 padding-bottom: 8px;
 top:0px;
 left:0px;
 -moz-border-radius: 0px 0px 5px 0px; 
 -webkit-border-radius: 0px 0px 5px 0px; 
 border-radius: 0px 0px 5px 0px;
 filter: alpha(opacity=90); 
 opacity: 0.9;
 -webkit-opacity: 0.9; 
 -moz-opacity: 0.9; 
 z-index: 700;
}
#okPost:hover:after{
   content: ' X';
   position: absolute;
   color: brown;
   right: 2px;
   top: 30%
}  
</style>
<SCRIPT language="JavaScript">
function info(id) {
  window.document.getElementById('inf').style.display = "none";
  window.document.getElementById('adm').style.display = "none";
  window.document.getElementById(id).style.display = "block";
}
</SCRIPT>
</head>
<body>
<?php
$errorPost = false;
$okPost = false;

$energ = users_conect_dop('energi_cool');
if($myrow['clanid'] == $_GET['id'] && $energ > 0 && !empty($_GET['enr']) && $_GET['enr'] > 0){
   $gets = obr_chis($_GET['enr']);
   $upEnerg = false;
   if($energ >= $gets) $upEnerg = true; 
   if($upEnerg == true){
      $ccxx = first('SELECT fond_energi FROM clans WHERE id_clan=%d',$myrow['clanid']);
      $clanpoint  = $ccxx['fond_energi'] + $gets;
      $usersPoint = $energ - $gets;
      $energ =  $usersPoint;
      $a = update('clans',array('fond_energi'=>$clanpoint),'id_clan='.(int)$myrow['clanid']);
      $b = update('usersunictable',array('energi_cool'=>$usersPoint),'id='.(int)$_SESSION['id']);
      if($a*$b == true) $okPost = 'Вы удачно повысили количество очков энергии в клане. <br> У вас осталось: '.$usersPoint.' шт. Энергии.';
      else  $errorPost .= 'Системная ошибка, попробуйте снова.';
   }else{
      $errorPost .= 'Недостаточно очков энергии.';
   }
}
if($energ > 0 && $myrow['clanid'] == $_GET['id']){
 $plus = "<input id='energy' name='energy' type='text' value='0' style='width:35px; height: 18px; font-size: 12px;' maxlength='4'> <a href='javascript:'  onclick=\"if(document.getElementById('energy').value > 0 && document.getElementById('energy').value <= ".$energ.") location.href='/game.php?go=clans&id=".$_GET['id']."&enr='+document.getElementById('energy').value;\"><img src='img/plus.gif' border=0 width=14/></a>";
}
if($myrow['clanid'] == $_GET['id'] && $myrow['clan_adm'] > 0){
  if(!empty($_POST['new_user'])){
    $rang = false;
    if(!empty($_POST['rang_user']) && $_POST['rang_user'] != 'FALSE') $rang = obr_txt(substr($_POST['rang_user'], 0, 40));
    $login = obr_txt(translitRus(substr($_POST['new_user'], 0, 16)));
    $x = first('SELECT id,clanid FROM users WHERE login="%s"',$login);
    $return = true;
    if(empty($x)){
      $errorPost .= 'Пользователя: <span style="color:#ff0031;">'.$login.'</span> не существует.';
      $return = false;
    }                                        
    if($return && $x['clanid'] > 0){
      $errorPost .= 'Пользователь: <span style="color:#ff0031;">'.$login.'</span> уже состоит в клане.';
      $return = false;    
    }
    if($return){
      update('users', array('clanid'=>$myrow['clanid'], 'status_klan'=>$rang, 'clan_point'=>0, 'clan_adm'=>0), 'id='.(int)$x['id']);
      $okPost .=  'Пользователь: <span style="color:#fff;">'.$login.'</span> удачно принят в клан.';
    }
  
  }
  if(!empty($_POST['textClan'])){
     $txt = obr_txt(substr($_POST['textClan'], 0, 350));
     if(update('clans', array('clan_title'=>$txt), 'id_clan='.(int)$myrow['clanid']))
       $okPost .=  'Описание клана удачно изменено.';
      else $errorPost .= 'Системная ошибка, попробуйте снова.';  
  }   
  if(!empty($_POST['clan_user'])){
     $login = obr_txt(translitRus(substr($_POST['clan_user'], 0, 16)));
     $x = first('SELECT id,clanid,clan_point,clan_adm FROM users WHERE login="%s"',$login);
     $return = true;
     if(empty($x)){
        $errorPost .= 'Пользователя: <span style="color:#ff0031;">'.$login.'</span> не существует.';
        $return = false;
     }
     if($return && $x['clanid'] != $myrow['clanid']){
        $errorPost .= 'Пользователя: <span style="color:#ff0031;">'.$login.'</span> не числится в Вашем клане.';
        $return = false;     
     }
     if(!empty($_POST['rang_user']) && $return){
       if($_POST['rang_user'] === 'FALSE') $rang = false; else $rang = obr_txt(substr($_POST['rang_user'], 0, 40));
       if($return){
        if(update('users', array('status_klan'=>$rang), 'id='.(int)$x['id'].' AND clanid='.(int)$myrow['clanid'])) 
          $okPost .=  'У пользователя: <span style="color:#fff;">'.$login.'</span> удачно изменен клановый ранг.';
         else $errorPost .= 'Системная ошибка, попробуйте снова.';       
       }     
     }  
     if(!empty($_POST['del']) && $return){
       if($return && $x['id'] == $_SESSION['id']){
          $errorPost .= 'Пользователя: <span style="color:#ff0031;">'.$login.'</span> нельзя исключить из клана, так как им являетесь Вы.';
          $return = false;     
       }
       if($return && $x['clan_adm']>0 && $myrow['clan_adm'] != 2){
          $errorPost .= 'Пользователя: <span style="color:#ff0031;">'.$login.'</span> нельзя исключить из клана, так как вышестоящие лица может исключать только создатель клана.';
          $return = false;     
       }
       if($return){
        if(update('users', array('clanid'=>0, 'status_klan'=>false, 'clan_point'=>0, 'clan_adm'=>0), 'id='.(int)$x['id'].' AND clanid='.(int)$myrow['clanid'])) 
          $okPost .=  'Пользователь: <span style="color:#fff;">'.$login.'</span> удачно исключен из клана.';
         else $errorPost .= 'Системная ошибка, попробуйте снова.';       
       }     
     
     }
     if(!empty($_POST['adm']) && $return){
       $adm = ($_POST['adm']==2?1:0);
       if($return && $x['id'] == $_SESSION['id']){
          $errorPost .= 'Пользователя: <span style="color:#ff0031;">'.$login.'</span> нельзя понизить/повысить в должности, так как им являетесь Вы.';
          $return = false;     
       }
       if($return && $x['clan_adm'] == $adm){
          $errorPost .= 'Пользователя: <span style="color:#ff0031;">'.$login.'</span> нельзя понизить/повысить в должности, так как эта должность у него на данный момент.';
          $return = false;     
       }
       if($return && $x['clan_adm'] == 2){
          $errorPost .= 'Пользователя: <span style="color:#ff0031;">'.$login.'</span> нельзя понизить/повысить в должности, так как он является создателем клана.';
          $return = false;     
       }     
       if($return == true){
        if(update('users', array('clan_adm'=>$adm), 'id='.(int)$x['id'].' AND clanid='.(int)$myrow['clanid'])) 
          $okPost .=  'У пользователя: <span style="color:#fff;">'.$login.'</span> удачно изменена должность в клане.';
         else $errorPost .= 'Системная ошибка, попробуйте снова.';
       }
     }
  }
}

if($errorPost){               
  print '<div id="errPost" class="postError" onclick="window.document.getElementById(\'errPost\').style.display = \'none\'">'.$errorPost.'</div>';
}
if($okPost){               
  print '<div id="okPost" onclick="window.document.getElementById(\'okPost\').style.display = \'none\'">'.$okPost.'</div>';
}

  if(!empty($_GET['id']) && $_GET['id'] > 0){
    $clans = first('SELECT * FROM clans WHERE id_clan=%d',$_GET['id']);
    if(!empty($clans)){
      print '<title>League-Of-Pokemons -> Клан: '.$clans['clan_name'].'</title>';
      level_clan($clans['id_clan'], $clans['fond_energi'], $clans['clan_reputation'], $clans['clan_lvl']);
      $usersClanCount = first('SELECT COUNT(*) as count FROM users WHERE clanid=%d',$clans['id_clan']); 
      $coolUsers = $usersClanCount['count'];
      $usersClan  = select('SELECT id,clan_adm,login,groups,online,clan_point,status_klan FROM users WHERE clanid=%d ORDER BY clan_adm DESC, clan_point DESC, id ASC',$clans['id_clan']);
      $user = false;
      $upr = false; 
      function pointclan($point){
       if($point > 0) $txt = '<font style="font-size: 11px; font-family: Tahoma; text-align:justify; color: #7fff00;"><b>+'.$point.'</b></font>';
        else $txt = ' ';
       if($point < 0) 
          $txt = '<font style="font-size: 11px; font-family: Tahoma; text-align:justify; color: #B22222;"><b>'.$point.'</b></font>';
       return $txt;
      }
       foreach($usersClan  as $usersClanrow){
          if($usersClanrow['clan_adm'] > 0) $upr = ' <b style="color:gold;font-size:22px; cursor:pointer;" title="Управляющий">*</b>'; else $upr = false;
          if($clans['id_sozdatel'] == $usersClanrow['id']) $upr = ' <b style="color:brown;font-size:22px;cursor:pointer;" title="Создатель клана">*</b>';
          $user .= color_group_users($usersClanrow['id'],false).' '.pointclan($usersClanrow['clan_point']).' '.($usersClanrow['status_klan']?' -> '.$usersClanrow['status_klan'].'.':'').$upr.'<br>';
       }
    $noom = $clans['clan_mesto'];
    $lvl  = $clans['clan_lvl'];
    $rep  = $clans['clan_reputation'];
    $enr  = $clans['fond_energi'];
    $color = ($noom == 1?"#ffd700":(($noom == 2)?'#c0c0c0':($noom == 3?'#cd7f32':"#fff")));
    $my_clan  = ($myrow['clanid'] == $clans['id_clan']?true:false);
    print '<div class="clan">
            <span class="nameClan" style="color: '.$color.';">'.$clans['clan_name'].'</span>
            <br>
            <span class="otherInf">Место в топе: <b>'.$noom.'</b> | Уровень: <b>'.$lvl.'</b> | Репутация: <b>'.$rep.'</b> | Энергия: <b>'.$enr.'</b> '.$plus.'</span>
            <br>
            '.(($my_clan == true && $myrow['clan_adm'] > 0)?"<a href='javascript:' onclick=\"info('adm')\">Администрирование</a> | <a href='javascript:' onclick=\"info('inf')\">Информация</a>":'<br>').'
            <div id="inf" align="center">
             <b class="otherInf">Состав('.$coolUsers.'): </b>
             <br>
             <div class="countUsers" align="left">'.$user.'</div>
             <div class="inf" align="left">
              <center><b>Дополнительная информация:</b></center>
               '.$clans['clan_title'].'
             </div>
            </div>';
            if($my_clan == true && $myrow['clan_adm'] > 0){
            print '<div id="adm" style="display:none;" align="center">
                    <b style="color:gold;">Управление кланом:</b>
                    <table>
                      <tr>
                        <td colspan="3" align="center"><b style="color:#000;">Принять в клан:</b></td>
                      <tr>
                      <tr>
                        <form action="" method="post">
                        <td width="300">
                          <b style="color:#000;">Логин: <input name="new_user" type="text" maxlength="16"></b>
                        </td> 
                        <td width="250">
                          <b style="color:#000;">Ранг: <input name="rang_user" type="text" maxlength="40" value="FALSE" onclick="if(this.value==\'FALSE\')this.value=\'\';" onblur="if(this.value==\'\')this.value=\'FALSE\';"></b>
                        </td>
                        <td width="150">
                          <input type="submit" name="submit" value="Принять">
                        </td>
                        </form>
                      <tr>
                    </table>
                    <table>
                      <tr>
                        <td colspan="3" align="center"><b style="color:#000;">Назначить/Снять администратором клана:</b></td>
                      <tr>
                      <tr>
                        <form action="" method="post">
                        <td width="300">
                          <b style="color:#000;">Логин: <input name="clan_user" type="text" maxlength="16"></b>
                        </td> 
                        <td width="250">
                          <select size="1" name="adm" style="width:235px"><option value="1">Участник</option><option value="2">Администратор</option></select>
                        </td>
                        <td width="150">
                          <input type="submit" name="submit" value="Назначить">
                        </td>
                        </form>
                      <tr>
                    </table>
                    <table>
                      <tr>
                        <td colspan="3" align="center"><b style="color:#000;">Исключить:</b></td>
                      <tr>
                      <tr>
                        <form action="" method="post">
                        <td colspan="2" width="553" align="center">
                          <b style="color:#000;">Логин: <input name="clan_user" type="text" maxlength="16"></b>
                          <input name="del" type="hidden" value="true" maxlength="16">
                        </td>
                        <td width="150">
                          <input type="submit" name="submit" value="Исключить">
                        </td> 
                        </form>
                      <tr>
                    </table>
                    <table>
                      <tr>
                        <td colspan="3" align="center"><b style="color:#000;">Изменить ранг в клане:</b></td>
                      <tr>
                      <tr>
                        <form action="" method="post">
                        <td width="300">
                          <b style="color:#000;">Логин: <input name="clan_user" type="text" maxlength="16"></b>
                        </td> 
                        <td width="250">
                          <b style="color:#000;">Ранг: <input name="rang_user" type="text" maxlength="40" value="FALSE" onclick="if(this.value==\'FALSE\')this.value=\'\';" onblur="if(this.value==\'\')this.value=\'FALSE\';"></b>
                        </td>
                        <td width="150">
                          <input type="submit" name="submit" value="Изменить">
                        </td>
                        </form>
                      <tr>
                    </table>
                    <table>
                      <tr>
                        <td colspan="3" align="center"><b style="color:#000;">Изменить описание клана:</b></td>
                      <tr>
                      <tr>
                        <form action="" method="post">
                        <td width="553" colspan="2">
                           <textarea name="textClan" style="width:97%; height:80px;"></textarea>
                        </td>
                        <td width="150">
                          <input type="submit" name="submit" value="Изменить">
                        </td>
                        </form>
                      <tr>
                    </table>
                   </div>';
            }
           print '</div>';
    
    }else{
      die('<b style="color:#000;">Данного клана не существует, возможно он был распущен.</b>');
    }
  }else{
    die('<b style="color:#000;">Введен неверный параметр.</b>');
  }
}
?>
</body>
</html>