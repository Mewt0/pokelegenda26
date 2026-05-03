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
<center><h1>Р СћР С•Р С— - Р С”Р В»Р В°Р Р…РЎвЂ№:</h1></center>
<div align="center">
<table cellspacing='0' cellpadding='2' width='75%' align='center'>
	<tr>
	    <td style='color:#000;' width='45'><b>Р СљР ВµРЎРѓРЎвЂљР С•:</b></td>
	    <td colspan='2' style='color:#000;' align="center"><b>Р РЋР С—РЎР‚Р В°Р в„–РЎвЂљ, Р Р…Р В°Р В·Р Р†Р В°Р Р…Р С‘Р Вµ:</b></td>
	    <td align="center" style='color:#000;'><b>Р Р€РЎР‚Р С•Р Р†Р ВµР Р…РЎРЉ:</b></td>
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
      if($a*$b == true) $okPost = 'Р вЂ™РЎвЂ№ РЎС“Р Т‘Р В°РЎвЂЎР Р…Р С• Р С—Р С•Р Р†РЎвЂ№РЎРѓР С‘Р В»Р С‘ Р С”Р С•Р В»Р С‘РЎвЂЎР ВµРЎРѓРЎвЂљР Р†Р С• Р С•РЎвЂЎР С”Р С•Р Р† РЎРЊР Р…Р ВµРЎР‚Р С–Р С‘Р С‘ Р Р† Р С”Р В»Р В°Р Р…Р Вµ. <br> Р Р€ Р Р†Р В°РЎРѓ Р С•РЎРѓРЎвЂљР В°Р В»Р С•РЎРѓРЎРЉ: '.$usersPoint.' РЎв‚¬РЎвЂљ. Р В­Р Р…Р ВµРЎР‚Р С–Р С‘Р С‘.';
      else  $errorPost .= 'Р РЋР С‘РЎРѓРЎвЂљР ВµР СР Р…Р В°РЎРЏ Р С•РЎв‚¬Р С‘Р В±Р С”Р В°, Р С—Р С•Р С—РЎР‚Р С•Р В±РЎС“Р в„–РЎвЂљР Вµ РЎРѓР Р…Р С•Р Р†Р В°.';
   }else{
      $errorPost .= 'Р СњР ВµР Т‘Р С•РЎРѓРЎвЂљР В°РЎвЂљР С•РЎвЂЎР Р…Р С• Р С•РЎвЂЎР С”Р С•Р Р† РЎРЊР Р…Р ВµРЎР‚Р С–Р С‘Р С‘.';
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
      $errorPost .= 'Р СџР С•Р В»РЎРЉР В·Р С•Р Р†Р В°РЎвЂљР ВµР В»РЎРЏ: <span style="color:#ff0031;">'.$login.'</span> Р Р…Р Вµ РЎРѓРЎС“РЎвЂ°Р ВµРЎРѓРЎвЂљР Р†РЎС“Р ВµРЎвЂљ.';
      $return = false;
    }                                        
    if($return && $x['clanid'] > 0){
      $errorPost .= 'Р СџР С•Р В»РЎРЉР В·Р С•Р Р†Р В°РЎвЂљР ВµР В»РЎРЉ: <span style="color:#ff0031;">'.$login.'</span> РЎС“Р В¶Р Вµ РЎРѓР С•РЎРѓРЎвЂљР С•Р С‘РЎвЂљ Р Р† Р С”Р В»Р В°Р Р…Р Вµ.';
      $return = false;    
    }
    if($return){
      update('users', array('clanid'=>$myrow['clanid'], 'status_klan'=>$rang, 'clan_point'=>0, 'clan_adm'=>0), 'id='.(int)$x['id']);
      $okPost .=  'Р СџР С•Р В»РЎРЉР В·Р С•Р Р†Р В°РЎвЂљР ВµР В»РЎРЉ: <span style="color:#fff;">'.$login.'</span> РЎС“Р Т‘Р В°РЎвЂЎР Р…Р С• Р С—РЎР‚Р С‘Р Р…РЎРЏРЎвЂљ Р Р† Р С”Р В»Р В°Р Р….';
    }
  
  }
  if(!empty($_POST['textClan'])){
     $txt = obr_txt(substr($_POST['textClan'], 0, 350));
     if(update('clans', array('clan_title'=>$txt), 'id_clan='.(int)$myrow['clanid']))
       $okPost .=  'Р С›Р С—Р С‘РЎРѓР В°Р Р…Р С‘Р Вµ Р С”Р В»Р В°Р Р…Р В° РЎС“Р Т‘Р В°РЎвЂЎР Р…Р С• Р С‘Р В·Р СР ВµР Р…Р ВµР Р…Р С•.';
      else $errorPost .= 'Р РЋР С‘РЎРѓРЎвЂљР ВµР СР Р…Р В°РЎРЏ Р С•РЎв‚¬Р С‘Р В±Р С”Р В°, Р С—Р С•Р С—РЎР‚Р С•Р В±РЎС“Р в„–РЎвЂљР Вµ РЎРѓР Р…Р С•Р Р†Р В°.';  
  }   
  if(!empty($_POST['clan_user'])){
     $login = obr_txt(translitRus(substr($_POST['clan_user'], 0, 16)));
     $x = first('SELECT id,clanid,clan_point,clan_adm FROM users WHERE login="%s"',$login);
     $return = true;
     if(empty($x)){
        $errorPost .= 'Р СџР С•Р В»РЎРЉР В·Р С•Р Р†Р В°РЎвЂљР ВµР В»РЎРЏ: <span style="color:#ff0031;">'.$login.'</span> Р Р…Р Вµ РЎРѓРЎС“РЎвЂ°Р ВµРЎРѓРЎвЂљР Р†РЎС“Р ВµРЎвЂљ.';
        $return = false;
     }
     if($return && $x['clanid'] != $myrow['clanid']){
        $errorPost .= 'Р СџР С•Р В»РЎРЉР В·Р С•Р Р†Р В°РЎвЂљР ВµР В»РЎРЏ: <span style="color:#ff0031;">'.$login.'</span> Р Р…Р Вµ РЎвЂЎР С‘РЎРѓР В»Р С‘РЎвЂљРЎРѓРЎРЏ Р Р† Р вЂ™Р В°РЎв‚¬Р ВµР С Р С”Р В»Р В°Р Р…Р Вµ.';
        $return = false;     
     }
     if(!empty($_POST['rang_user']) && $return){
       if($_POST['rang_user'] === 'FALSE') $rang = false; else $rang = obr_txt(substr($_POST['rang_user'], 0, 40));
       if($return){
        if(update('users', array('status_klan'=>$rang), 'id='.(int)$x['id'].' AND clanid='.(int)$myrow['clanid'])) 
          $okPost .=  'Р Р€ Р С—Р С•Р В»РЎРЉР В·Р С•Р Р†Р В°РЎвЂљР ВµР В»РЎРЏ: <span style="color:#fff;">'.$login.'</span> РЎС“Р Т‘Р В°РЎвЂЎР Р…Р С• Р С‘Р В·Р СР ВµР Р…Р ВµР Р… Р С”Р В»Р В°Р Р…Р С•Р Р†РЎвЂ№Р в„– РЎР‚Р В°Р Р…Р С–.';
         else $errorPost .= 'Р РЋР С‘РЎРѓРЎвЂљР ВµР СР Р…Р В°РЎРЏ Р С•РЎв‚¬Р С‘Р В±Р С”Р В°, Р С—Р С•Р С—РЎР‚Р С•Р В±РЎС“Р в„–РЎвЂљР Вµ РЎРѓР Р…Р С•Р Р†Р В°.';       
       }     
     }  
     if(!empty($_POST['del']) && $return){
       if($return && $x['id'] == $_SESSION['id']){
          $errorPost .= 'Р СџР С•Р В»РЎРЉР В·Р С•Р Р†Р В°РЎвЂљР ВµР В»РЎРЏ: <span style="color:#ff0031;">'.$login.'</span> Р Р…Р ВµР В»РЎРЉР В·РЎРЏ Р С‘РЎРѓР С”Р В»РЎР‹РЎвЂЎР С‘РЎвЂљРЎРЉ Р С‘Р В· Р С”Р В»Р В°Р Р…Р В°, РЎвЂљР В°Р С” Р С”Р В°Р С” Р С‘Р С РЎРЏР Р†Р В»РЎРЏР ВµРЎвЂљР ВµРЎРѓРЎРЉ Р вЂ™РЎвЂ№.';
          $return = false;     
       }
       if($return && $x['clan_adm']>0 && $myrow['clan_adm'] != 2){
          $errorPost .= 'Р СџР С•Р В»РЎРЉР В·Р С•Р Р†Р В°РЎвЂљР ВµР В»РЎРЏ: <span style="color:#ff0031;">'.$login.'</span> Р Р…Р ВµР В»РЎРЉР В·РЎРЏ Р С‘РЎРѓР С”Р В»РЎР‹РЎвЂЎР С‘РЎвЂљРЎРЉ Р С‘Р В· Р С”Р В»Р В°Р Р…Р В°, РЎвЂљР В°Р С” Р С”Р В°Р С” Р Р†РЎвЂ№РЎв‚¬Р ВµРЎРѓРЎвЂљР С•РЎРЏРЎвЂ°Р С‘Р Вµ Р В»Р С‘РЎвЂ Р В° Р СР С•Р В¶Р ВµРЎвЂљ Р С‘РЎРѓР С”Р В»РЎР‹РЎвЂЎР В°РЎвЂљРЎРЉ РЎвЂљР С•Р В»РЎРЉР С”Р С• РЎРѓР С•Р В·Р Т‘Р В°РЎвЂљР ВµР В»РЎРЉ Р С”Р В»Р В°Р Р…Р В°.';
          $return = false;     
       }
       if($return){
        if(update('users', array('clanid'=>0, 'status_klan'=>false, 'clan_point'=>0, 'clan_adm'=>0), 'id='.(int)$x['id'].' AND clanid='.(int)$myrow['clanid'])) 
          $okPost .=  'Р СџР С•Р В»РЎРЉР В·Р С•Р Р†Р В°РЎвЂљР ВµР В»РЎРЉ: <span style="color:#fff;">'.$login.'</span> РЎС“Р Т‘Р В°РЎвЂЎР Р…Р С• Р С‘РЎРѓР С”Р В»РЎР‹РЎвЂЎР ВµР Р… Р С‘Р В· Р С”Р В»Р В°Р Р…Р В°.';
         else $errorPost .= 'Р РЋР С‘РЎРѓРЎвЂљР ВµР СР Р…Р В°РЎРЏ Р С•РЎв‚¬Р С‘Р В±Р С”Р В°, Р С—Р С•Р С—РЎР‚Р С•Р В±РЎС“Р в„–РЎвЂљР Вµ РЎРѓР Р…Р С•Р Р†Р В°.';       
       }     
     
     }
     if(!empty($_POST['adm']) && $return){
       $adm = ($_POST['adm']==2?1:0);
       if($return && $x['id'] == $_SESSION['id']){
          $errorPost .= 'Р СџР С•Р В»РЎРЉР В·Р С•Р Р†Р В°РЎвЂљР ВµР В»РЎРЏ: <span style="color:#ff0031;">'.$login.'</span> Р Р…Р ВµР В»РЎРЉР В·РЎРЏ Р С—Р С•Р Р…Р С‘Р В·Р С‘РЎвЂљРЎРЉ/Р С—Р С•Р Р†РЎвЂ№РЎРѓР С‘РЎвЂљРЎРЉ Р Р† Р Т‘Р С•Р В»Р В¶Р Р…Р С•РЎРѓРЎвЂљР С‘, РЎвЂљР В°Р С” Р С”Р В°Р С” Р С‘Р С РЎРЏР Р†Р В»РЎРЏР ВµРЎвЂљР ВµРЎРѓРЎРЉ Р вЂ™РЎвЂ№.';
          $return = false;     
       }
       if($return && $x['clan_adm'] == $adm){
          $errorPost .= 'Р СџР С•Р В»РЎРЉР В·Р С•Р Р†Р В°РЎвЂљР ВµР В»РЎРЏ: <span style="color:#ff0031;">'.$login.'</span> Р Р…Р ВµР В»РЎРЉР В·РЎРЏ Р С—Р С•Р Р…Р С‘Р В·Р С‘РЎвЂљРЎРЉ/Р С—Р С•Р Р†РЎвЂ№РЎРѓР С‘РЎвЂљРЎРЉ Р Р† Р Т‘Р С•Р В»Р В¶Р Р…Р С•РЎРѓРЎвЂљР С‘, РЎвЂљР В°Р С” Р С”Р В°Р С” РЎРЊРЎвЂљР В° Р Т‘Р С•Р В»Р В¶Р Р…Р С•РЎРѓРЎвЂљРЎРЉ РЎС“ Р Р…Р ВµР С–Р С• Р Р…Р В° Р Т‘Р В°Р Р…Р Р…РЎвЂ№Р в„– Р СР С•Р СР ВµР Р…РЎвЂљ.';
          $return = false;     
       }
       if($return && $x['clan_adm'] == 2){
          $errorPost .= 'Р СџР С•Р В»РЎРЉР В·Р С•Р Р†Р В°РЎвЂљР ВµР В»РЎРЏ: <span style="color:#ff0031;">'.$login.'</span> Р Р…Р ВµР В»РЎРЉР В·РЎРЏ Р С—Р С•Р Р…Р С‘Р В·Р С‘РЎвЂљРЎРЉ/Р С—Р С•Р Р†РЎвЂ№РЎРѓР С‘РЎвЂљРЎРЉ Р Р† Р Т‘Р С•Р В»Р В¶Р Р…Р С•РЎРѓРЎвЂљР С‘, РЎвЂљР В°Р С” Р С”Р В°Р С” Р С•Р Р… РЎРЏР Р†Р В»РЎРЏР ВµРЎвЂљРЎРѓРЎРЏ РЎРѓР С•Р В·Р Т‘Р В°РЎвЂљР ВµР В»Р ВµР С Р С”Р В»Р В°Р Р…Р В°.';
          $return = false;     
       }     
       if($return == true){
        if(update('users', array('clan_adm'=>$adm), 'id='.(int)$x['id'].' AND clanid='.(int)$myrow['clanid'])) 
          $okPost .=  'Р Р€ Р С—Р С•Р В»РЎРЉР В·Р С•Р Р†Р В°РЎвЂљР ВµР В»РЎРЏ: <span style="color:#fff;">'.$login.'</span> РЎС“Р Т‘Р В°РЎвЂЎР Р…Р С• Р С‘Р В·Р СР ВµР Р…Р ВµР Р…Р В° Р Т‘Р С•Р В»Р В¶Р Р…Р С•РЎРѓРЎвЂљРЎРЉ Р Р† Р С”Р В»Р В°Р Р…Р Вµ.';
         else $errorPost .= 'Р РЋР С‘РЎРѓРЎвЂљР ВµР СР Р…Р В°РЎРЏ Р С•РЎв‚¬Р С‘Р В±Р С”Р В°, Р С—Р С•Р С—РЎР‚Р С•Р В±РЎС“Р в„–РЎвЂљР Вµ РЎРѓР Р…Р С•Р Р†Р В°.';
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
      print '<title>League-Of-Pokemons -> Р С™Р В»Р В°Р Р…: '.$clans['clan_name'].'</title>';
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
          if($usersClanrow['clan_adm'] > 0) $upr = ' <b style="color:gold;font-size:22px; cursor:pointer;" title="Р Р€Р С—РЎР‚Р В°Р Р†Р В»РЎРЏРЎР‹РЎвЂ°Р С‘Р в„–">*</b>'; else $upr = false;
          if($clans['id_sozdatel'] == $usersClanrow['id']) $upr = ' <b style="color:brown;font-size:22px;cursor:pointer;" title="Р РЋР С•Р В·Р Т‘Р В°РЎвЂљР ВµР В»РЎРЉ Р С”Р В»Р В°Р Р…Р В°">*</b>';
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
            <span class="otherInf">Р СљР ВµРЎРѓРЎвЂљР С• Р Р† РЎвЂљР С•Р С—Р Вµ: <b>'.$noom.'</b> | Р Р€РЎР‚Р С•Р Р†Р ВµР Р…РЎРЉ: <b>'.$lvl.'</b> | Р В Р ВµР С—РЎС“РЎвЂљР В°РЎвЂ Р С‘РЎРЏ: <b>'.$rep.'</b> | Р В­Р Р…Р ВµРЎР‚Р С–Р С‘РЎРЏ: <b>'.$enr.'</b> '.$plus.'</span>
            <br>
            '.(($my_clan == true && $myrow['clan_adm'] > 0)?"<a href='javascript:' onclick=\"info('adm')\">Р С’Р Т‘Р СР С‘Р Р…Р С‘РЎРѓРЎвЂљРЎР‚Р С‘РЎР‚Р С•Р Р†Р В°Р Р…Р С‘Р Вµ</a> | <a href='javascript:' onclick=\"info('inf')\">Р ВР Р…РЎвЂћР С•РЎР‚Р СР В°РЎвЂ Р С‘РЎРЏ</a>":'<br>').'
            <div id="inf" align="center">
             <b class="otherInf">Р РЋР С•РЎРѓРЎвЂљР В°Р Р†('.$coolUsers.'): </b>
             <br>
             <div class="countUsers" align="left">'.$user.'</div>
             <div class="inf" align="left">
              <center><b>Р вЂќР С•Р С—Р С•Р В»Р Р…Р С‘РЎвЂљР ВµР В»РЎРЉР Р…Р В°РЎРЏ Р С‘Р Р…РЎвЂћР С•РЎР‚Р СР В°РЎвЂ Р С‘РЎРЏ:</b></center>
               '.$clans['clan_title'].'
             </div>
            </div>';
            if($my_clan == true && $myrow['clan_adm'] > 0){
            print '<div id="adm" style="display:none;" align="center">
                    <b style="color:gold;">Р Р€Р С—РЎР‚Р В°Р Р†Р В»Р ВµР Р…Р С‘Р Вµ Р С”Р В»Р В°Р Р…Р С•Р С:</b>
                    <table>
                      <tr>
                        <td colspan="3" align="center"><b style="color:#000;">Р СџРЎР‚Р С‘Р Р…РЎРЏРЎвЂљРЎРЉ Р Р† Р С”Р В»Р В°Р Р…:</b></td>
                      <tr>
                      <tr>
                        <form action="" method="post">
                        <td width="300">
                          <b style="color:#000;">Р вЂєР С•Р С–Р С‘Р Р…: <input name="new_user" type="text" maxlength="16"></b>
                        </td> 
                        <td width="250">
                          <b style="color:#000;">Р В Р В°Р Р…Р С–: <input name="rang_user" type="text" maxlength="40" value="FALSE" onclick="if(this.value==\'FALSE\')this.value=\'\';" onblur="if(this.value==\'\')this.value=\'FALSE\';"></b>
                        </td>
                        <td width="150">
                          <input type="submit" name="submit" value="Р СџРЎР‚Р С‘Р Р…РЎРЏРЎвЂљРЎРЉ">
                        </td>
                        </form>
                      <tr>
                    </table>
                    <table>
                      <tr>
                        <td colspan="3" align="center"><b style="color:#000;">Р СњР В°Р В·Р Р…Р В°РЎвЂЎР С‘РЎвЂљРЎРЉ/Р РЋР Р…РЎРЏРЎвЂљРЎРЉ Р В°Р Т‘Р СР С‘Р Р…Р С‘РЎРѓРЎвЂљРЎР‚Р В°РЎвЂљР С•РЎР‚Р С•Р С Р С”Р В»Р В°Р Р…Р В°:</b></td>
                      <tr>
                      <tr>
                        <form action="" method="post">
                        <td width="300">
                          <b style="color:#000;">Р вЂєР С•Р С–Р С‘Р Р…: <input name="clan_user" type="text" maxlength="16"></b>
                        </td> 
                        <td width="250">
                          <select size="1" name="adm" style="width:235px"><option value="1">Р Р€РЎвЂЎР В°РЎРѓРЎвЂљР Р…Р С‘Р С”</option><option value="2">Р С’Р Т‘Р СР С‘Р Р…Р С‘РЎРѓРЎвЂљРЎР‚Р В°РЎвЂљР С•РЎР‚</option></select>
                        </td>
                        <td width="150">
                          <input type="submit" name="submit" value="Р СњР В°Р В·Р Р…Р В°РЎвЂЎР С‘РЎвЂљРЎРЉ">
                        </td>
                        </form>
                      <tr>
                    </table>
                    <table>
                      <tr>
                        <td colspan="3" align="center"><b style="color:#000;">Р ВРЎРѓР С”Р В»РЎР‹РЎвЂЎР С‘РЎвЂљРЎРЉ:</b></td>
                      <tr>
                      <tr>
                        <form action="" method="post">
                        <td colspan="2" width="553" align="center">
                          <b style="color:#000;">Р вЂєР С•Р С–Р С‘Р Р…: <input name="clan_user" type="text" maxlength="16"></b>
                          <input name="del" type="hidden" value="true" maxlength="16">
                        </td>
                        <td width="150">
                          <input type="submit" name="submit" value="Р ВРЎРѓР С”Р В»РЎР‹РЎвЂЎР С‘РЎвЂљРЎРЉ">
                        </td> 
                        </form>
                      <tr>
                    </table>
                    <table>
                      <tr>
                        <td colspan="3" align="center"><b style="color:#000;">Р ВР В·Р СР ВµР Р…Р С‘РЎвЂљРЎРЉ РЎР‚Р В°Р Р…Р С– Р Р† Р С”Р В»Р В°Р Р…Р Вµ:</b></td>
                      <tr>
                      <tr>
                        <form action="" method="post">
                        <td width="300">
                          <b style="color:#000;">Р вЂєР С•Р С–Р С‘Р Р…: <input name="clan_user" type="text" maxlength="16"></b>
                        </td> 
                        <td width="250">
                          <b style="color:#000;">Р В Р В°Р Р…Р С–: <input name="rang_user" type="text" maxlength="40" value="FALSE" onclick="if(this.value==\'FALSE\')this.value=\'\';" onblur="if(this.value==\'\')this.value=\'FALSE\';"></b>
                        </td>
                        <td width="150">
                          <input type="submit" name="submit" value="Р ВР В·Р СР ВµР Р…Р С‘РЎвЂљРЎРЉ">
                        </td>
                        </form>
                      <tr>
                    </table>
                    <table>
                      <tr>
                        <td colspan="3" align="center"><b style="color:#000;">Р ВР В·Р СР ВµР Р…Р С‘РЎвЂљРЎРЉ Р С•Р С—Р С‘РЎРѓР В°Р Р…Р С‘Р Вµ Р С”Р В»Р В°Р Р…Р В°:</b></td>
                      <tr>
                      <tr>
                        <form action="" method="post">
                        <td width="553" colspan="2">
                           <textarea name="textClan" style="width:97%; height:80px;"></textarea>
                        </td>
                        <td width="150">
                          <input type="submit" name="submit" value="Р ВР В·Р СР ВµР Р…Р С‘РЎвЂљРЎРЉ">
                        </td>
                        </form>
                      <tr>
                    </table>
                   </div>';
            }
           print '</div>';
    
    }else{
      die('<b style="color:#000;">Р вЂќР В°Р Р…Р Р…Р С•Р С–Р С• Р С”Р В»Р В°Р Р…Р В° Р Р…Р Вµ РЎРѓРЎС“РЎвЂ°Р ВµРЎРѓРЎвЂљР Р†РЎС“Р ВµРЎвЂљ, Р Р†Р С•Р В·Р СР С•Р В¶Р Р…Р С• Р С•Р Р… Р В±РЎвЂ№Р В» РЎР‚Р В°РЎРѓР С—РЎС“РЎвЂ°Р ВµР Р….</b>');
    }
  }else{
    die('<b style="color:#000;">Р вЂ™Р Р†Р ВµР Т‘Р ВµР Р… Р Р…Р ВµР Р†Р ВµРЎР‚Р Р…РЎвЂ№Р в„– Р С—Р В°РЎР‚Р В°Р СР ВµРЎвЂљРЎР‚.</b>');
  }
}
?>
</body>
</html>