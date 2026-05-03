<?
if(!empty($_GET['exits'])){
   $a = $_GET['exits'];
   if($a == 1){
    $b = first('SELECT id,locid FROM users_locvoz WHERE userid=%d AND tip="arena"',$_SESSION['id']);
    if(!$b){
        update('users',array('buildmy'=>1),'id='.(int)$_SESSION['id']);  
    }else{
        delete('users_locvoz','id='.(int)$b['id']);
        update('users',array('buildmy'=>$b['locid']),'id='.(int)$_SESSION['id']);        
    }
    die("<script>parent._location.location.href='game.php?go=char';</script>");
   }else{
     die("<script>parent._location.location.href='game.php?go=char';</script>");
   }
}

$turs = first('SELECT * FROM kurator_npc WHERE id=1');
        if($turs['stoimost'] <= 0) $summ_turs = "Р вЂР ВµРЎРѓР С—Р В»Р В°РЎвЂљР Р…Р С•";
          else $summ_turs = formatnum($turs['stoimost'])." Р СљР С•Р Р…Р ВµРЎвЂљ";
        if($turs['nametur']) $name_turs = $turs['nametur'];
         else $name_turs = "--";
        if($turs['fond'] <= 0) $summ_fond = "<b>Р СњР В°Р С”Р С•Р С—Р В»Р ВµР Р…Р С‘Р в„– Р Р…Р ВµРЎвЂљ.</b>";
          else $summ_fond = "<b>Р РЋРЎС“Р СР СР В° Р Р…Р В°Р С”Р С•Р С—Р В»Р ВµР Р…Р С‘Р в„– РЎРѓР С•РЎРѓРЎвЂљР В°Р Р†Р В»РЎРЏР ВµРЎвЂљ: <b>".formatnum($turs['fond'])."</b> Р СљР С•Р Р…Р ВµРЎвЂљ.</b>";
        if($myrow['groups'] == 1) $admintur = $myrow['id']; 
          else $admintur = 1;

$pers = "<a href='/game.php?go=char&npc=1&do_npc=pc'>Р СџР С•Р С”Р ВµРЎвЂ Р ВµР Р…РЎвЂљРЎР‚</a>";
if($_SESSION['id'] == $turs['upr'] OR $_SESSION['id'] == $admintur){
  if(empty($_GET['npc'])) $pers .= " |  <a href='game.php?go=char&upr=1'>Р СџР В°Р Р…Р ВµР В»РЎРЉ РЎС“Р С—РЎР‚Р В°Р Р†Р В»Р ВµР Р…Р С‘РЎРЏ</a>"; 
  if(!empty($_POST['names']) AND !empty($_POST['cenatur'])){
    $names   = obr_txt($_POST['names']);
    $cenatur = obr_chis($_POST['cenatur']); 
    if($cenatur <= 0 OR !$names) die("<script>location.href='game.php?go=char&upr=1';</script>");
    if(update('kurator_npc',array('nametur'=>$names, 'stoimost'=>$cenatur),'id=1')) 
      $_SESSION['uzmena'] = "<center><font color=gold>Р вЂ™РЎвЂ№ РЎС“Р Т‘Р В°РЎвЂЎР Р…Р С• Р С‘Р В·Р СР ВµР Р…Р С‘Р В»Р С‘ Р С‘Р Р…РЎвЂћР С•РЎР‚Р СР В°РЎвЂ Р С‘РЎР‹.</font></center>";
    die("<script>parent._location.location.href='game.php?go=char&upr=1';</script>");
  }
  
if(!empty($_GET['upr'])){
   $a = $_GET['upr'];
   if($a == 1){
    $quest_isset_const = 1;
    $name = "Р СџР В°Р Р…Р ВµР В»РЎРЉ РЎС“Р С—РЎР‚Р В°Р Р†Р В»Р ВµР Р…Р С‘РЎРЏ";
    $about = "
    ".(empty($_SESSION['uzmena'])?'':'<b style="color:green">'.$_SESSION['uzmena'].'</b>')."
    <table>
    <form action='/game.php?go=char' method='POST' target=\"_chat_two\">
    <tr>
    <td>Р Р€РЎРѓРЎвЂљР В°Р Р…Р С•Р Р†Р С‘РЎвЂљРЎРЉ Р Р…Р В°Р В·Р Р†Р В°Р Р…Р С‘Р Вµ РЎвЂљРЎС“РЎР‚Р Р…Р С‘РЎР‚Р В°:<br>
    <input type=\"text\" name=\"names\" value='' size=43%></td>
    <td><br>Р СћР ВµР С”РЎС“РЎвЂ°Р ВµР Вµ Р Р…Р В°Р В·Р Р†Р В°Р Р…Р С‘Р Вµ: <b>".$name_turs.".</b></td>
    </tr>
    <tr>
    <td>Р Р€РЎРѓРЎвЂљР В°Р Р…Р С•Р Р†Р С‘РЎвЂљРЎРЉ РЎвЂ Р ВµР Р…РЎС“ Р Р…Р В° РЎвЂљРЎС“РЎР‚Р Р…Р С‘РЎР‚(Р В±Р ВµР В· РЎвЂљР С•РЎвЂЎР ВµР С”):<br>
    <input type=\"text\" name=\"cenatur\" value='' size=43%></td>
    <td><br>Р СћР ВµР С”РЎС“РЎвЂ°Р В°РЎРЏ РЎРѓРЎвЂљР С•Р С‘Р СР С•РЎРѓРЎвЂљРЎРЉ: <b>".$summ_turs.".</b></td>
    </tr>
    <tr>
    <td colspan=2 align=center>
    <input type=\"submit\" value=\"Р вЂњР С•РЎвЂљР С•Р Р†Р С•\" width=\"55\" height=\"15\" name=\"submit\" />
    <br><br>
    </td>
    </tr>
    </form>
    </table>";
    $pers = "
      <a href='game.php?go=char&upr=2'>Р СџР С•РЎРѓР СР С•РЎвЂљРЎР‚Р ВµРЎвЂљРЎРЉ Р Р…Р В°Р С”Р С•Р С—Р В»Р ВµР Р…Р С‘РЎРЏ.</a>
      <a href='game.php?go=char'>Р СџР ВµРЎР‚Р ВµР в„–РЎвЂљР С‘ Р Р…Р В° Р В»Р С•Р С”Р В°РЎвЂ Р С‘РЎР‹.</a>"; 
  }elseif($a == 2){
      $quest_isset_const = 1;
      $name = "Р СџР В°Р Р…Р ВµР В»РЎРЉ РЎС“Р С—РЎР‚Р В°Р Р†Р В»Р ВµР Р…Р С‘РЎРЏ";
      $about = $summ_fond;
        $pers = "
        <a href='game.php?go=char&upr=3'>Р вЂ”Р В°Р В±РЎР‚Р В°РЎвЂљРЎРЉ Р Т‘Р ВµР Р…РЎРЉР С–Р С‘.</a>
        <a href='game.php?go=char'>Р СџР ВµРЎР‚Р ВµР в„–РЎвЂљР С‘ Р Р…Р В° Р В»Р С•Р С”Р В°РЎвЂ Р С‘РЎР‹.</a>";
  }elseif($a == 3){
         $x11 = false;
      if($turs['fond'] > 0){
        if(update('kurator_npc',array('fond'=>0),'id=1')){
          plus_item($turs['fond'],1);        
          $t = "Р Р€Р Т‘Р В°РЎвЂЎР Р…Р С• Р В·Р В°Р В±Р В°РЎР‚Р В°Р Р…Р В° Р С•Р С—Р В»Р В°РЎвЂљР В° РЎРѓ Р В°РЎР‚Р ВµР Р…РЎвЂ№ РЎР‹Р В·Р ВµРЎР‚Р С•Р С: ".$_SESSION['login']."(".$_SESSION['id'].") Р Р…Р В° РЎРѓРЎС“Р СР СРЎС“: ".$turs['fond'];        
          writeLoges($t,'df','arenaZarplata');
          $x11 = true;
        }
      }
      $quest_isset_const = 1;
      $name = "Р СџР В°Р Р…Р ВµР В»РЎРЉ РЎС“Р С—РЎР‚Р В°Р Р†Р В»Р ВµР Р…Р С‘РЎРЏ";
      if($x11) $about = "Р вЂ™РЎвЂ№ РЎС“Р Т‘Р В°РЎвЂЎР Р…Р С• Р В·Р В°Р В±РЎР‚Р В°Р В»Р С‘ Р Р†РЎРѓР Вµ Р Р…Р В°Р С”Р С•Р С—Р В»Р ВµР Р…Р Р…РЎвЂ№Р Вµ Р Т‘Р ВµР Р…РЎРЉР С–Р С‘.";
        else $about = "Р Р€Р Р†РЎвЂ№, Р Р…Р С• Р Р…Р В°Р С”Р С•Р С—Р В»Р ВµР Р…Р С‘Р в„– Р Р…Р ВµРЎвЂљ.";
        $pers = "
        <a href='game.php?go=char&upr=1'>Р СџР ВµРЎР‚Р ВµР в„–РЎвЂљР С‘ Р Р† Р С‘Р В·Р Р…Р В°РЎвЂЎР В°Р В»РЎРЉР Р…РЎС“РЎР‹ Р С—Р В°Р Р…Р ВµР В»РЎРЉ РЎС“Р С—РЎР‚Р В°Р Р†Р В»Р ВµР Р…Р С‘РЎРЏ.</a>
        <a href='game.php?go=char'>Р СџР ВµРЎР‚Р ВµР в„–РЎвЂљР С‘ Р Р…Р В° Р В»Р С•Р С”Р В°РЎвЂ Р С‘РЎР‹.</a>";
  }else{
       die("<script>location.href='game.php?go=char';</script>");
  }
} 
}

if(!empty($_GET['npc']) && $_GET['npc'] == 1 && empty($_GET['upr'])){
        if ($_GET['npc'] == 1){
            include ("npc/1.php"); 
            $img_r = '<img src="img/room/1_1.png" width="250" height="150">';
        }else{
          die("<script>location.href='game.php?go=char';</script>");
        }
}else{
  if(!empty($_GET['upr']) && ($_SESSION['id'] == $turs['upr'] || $_SESSION['id'] == $admintur)){
   // not...
  }else{
    $name = "Р СћРЎС“РЎР‚Р Р…Р С‘РЎР‚Р Р…Р В°РЎРЏ Р С’РЎР‚Р ВµР Р…Р В°";
    $about = '
    Р СџР С•Р СР ВµРЎвЂ°Р ВµР Р…Р С‘Р Вµ РЎРѓ Р Р†Р Р…РЎС“РЎв‚¬Р В°РЎР‹РЎвЂ°Р С‘Р С РЎР‚Р В°Р В·Р СР ВµРЎР‚Р С•Р С Р Р†Р Р…РЎС“РЎвЂљРЎР‚Р С‘ Р С—Р С•РЎвЂ¦Р С•Р В¶Р Вµ Р Р…Р В° Р С”РЎС“Р С—Р С•Р В»Р С•Р С•Р В±РЎР‚Р В°Р В·Р Р…Р С•Р Вµ Р В·Р Т‘Р В°Р Р…Р С‘Р Вµ, Р Р† РЎвЂ Р ВµР Р…РЎвЂљРЎР‚Р Вµ Р С”Р С•РЎвЂљР С•РЎР‚Р С•Р С–Р С• РЎР‚Р В°РЎРѓР С—Р С•Р В»Р В°Р С–Р В°Р ВµРЎвЂљРЎРѓРЎРЏ Р С•Р С–РЎР‚Р С•Р СР Р…Р В°РЎРЏ Р В°РЎР‚Р ВµР Р…Р В°. 
    Р РЋРЎР‹Р Т‘Р В° РЎРѓРЎР‰Р ВµР В·Р В¶Р В°РЎР‹РЎвЂљРЎРѓРЎРЏ Р СР Р…Р С•Р С–Р С‘Р Вµ Р С‘Р В·Р Р†Р ВµРЎРѓРЎвЂљР Р…РЎвЂ№Р Вµ Р С‘ Р Р…Р В°РЎвЂЎР С‘Р Р…Р В°РЎР‹РЎвЂ°Р С‘Р Вµ РЎвЂљРЎР‚Р ВµР Р…Р ВµРЎР‚РЎвЂ№, РЎвЂЎРЎвЂљР С•Р В±РЎвЂ№ Р С—РЎР‚Р С•Р Р†Р ВµРЎР‚Р С‘РЎвЂљРЎРЉ РЎРѓР Р†Р С•Р С‘ РЎРѓР С‘Р В»РЎвЂ№ Р С‘ Р Р…Р В°РЎС“РЎвЂЎР С‘РЎвЂљРЎРЉ РЎРѓР Р†Р С•Р С‘РЎвЂ¦ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р С•Р Р† Р Р…Р С•Р Р†РЎвЂ№Р С Р С—Р С•РЎвЂљРЎР‚РЎРЏРЎРѓР В°РЎР‹РЎвЂ°Р С‘Р С РЎРѓРЎвЂљРЎР‚Р В°РЎвЂљР ВµР С–Р С‘РЎРЏР С Р С‘ Р СР В°Р Р…Р ВµР Р†РЎР‚Р В°Р С.
    ';
    $move = "<a href='game.php?go=char&exits=1' target=\"_chat_two\">Р вЂ™РЎвЂ№РЎвЂ¦Р С•Р Т‘</a>";
    $img_r = '';
  } 
}
unset($_SESSION['uzmena']);
?>

