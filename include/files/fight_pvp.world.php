<?php
function writeLoges($text, $type = 'system', $userId = 0) {
    $logfile = __DIR__ . "/../../log_{$type}.txt";
    $time = date("Y-m-d H:i:s");
    $entry = "[$time] [user:$userId] $text\n";
    file_put_contents($logfile, $entry, FILE_APPEND);
}

$battlPve  = first('SELECT * FROM battles WHERE (user_1=%d OR user_2=%d) AND batl_tip="pvp" AND id=%d',$_SESSION['id'],$_SESSION['id'],$myrow['battleid']);
if(empty($battlPve['id'])) {
  die("<script>window.location.href='game.php?go=char';</script>");
  update('users',array('pvp'=>0),'id='.(int)$_SESSION['id']);
}
function no_href_locs(){
  die("<script>window.location.href='/game.php?go=fight_pvp';</script>");
}
require_once ('include/function/battle.functions.tip.php');
require_once ('include/function/battle.functions.php');
$my_pokes_yes = false;
$hodUsers = $battlPve['hod_user_id'];
$pve_id  = $battlPve['id'];
$pobeda =  $battlPve['pobeda'];
$pveRound  = $battlPve['raund'];
$time_1   =   $battlPve['time_1'];
$time_2   =   $battlPve['time_2'];
$usersbattlone = $battlPve['user_1'];
$usersbattltwo = $battlPve['user_2'];
$сompulsionone = $battlPve['zamtru_1'];
$сompulsiontwo = $battlPve['zamtru_2'];
$attacOne = $battlPve['attac_1'];
$attacTwo = $battlPve['attac_2'];
$pokeOne  = $battlPve['poke_1'];
$pokeTwo  = $battlPve['poke_2'];
$pvp_id = $pve_id;
if($battlPve['user_1'] == $_SESSION['id']) {$t1 = $battlPve['time_1']; $t2 = $battlPve['time_2']; $a_my = $attacOne; $a_vs = $attacTwo; $ind = 1; $invs = 2; $hp_ind = poke_info($pokeOne,'hp_my'); $hp_invs = poke_info($pokeTwo,'hp_my');}else{$t2 = $battlPve['time_1']; $t1 = $battlPve['time_2']; $a_vs = $attacOne; $a_my = $attacTwo; $ind = 2; $invs = 1; $hp_ind = poke_info($pokeTwo,'hp_my'); $hp_invs = poke_info($pokeOne,'hp_my');}
function my_battle($id,$zap){
    $q = first('SELECT '.$zap.' FROM battles WHERE id=%d',$id);
    $a = $q[$zap];
 return $a;
}
function users($id,$zap){
  $q = first('SELECT '.$zap.' FROM users WHERE id=%d',$id);
  $a = $q[$zap];
return $a;
}
function attacPokeDic($base,$lvl){
  $c = first('SELECT atac_id FROM attac_poke WHERE poke_base_id=%d AND atc_lvl<=%d ORDER BY RAND() DESC LIMIT 0,1',$base,$lvl); 
  if(empty($c['atac_id'])) $a = 33; else $a = $c['atac_id'];
 return $a;
}
function status_na_pokemone($poke,$battle,$tip,$zap){
  $q = first('SELECT '.$zap.' FROM statpokemonbatle WHERE pokeid="%s" AND battleid=%d AND tip="%s"',$poke,$battle,$tip);
  $a = $q[$zap];
 return $a;
}
function tittle_status($p,$b){
    $q = first('SELECT sts.tittle_status FROM bttle_status bsp inner join status sts on bsp.namber_st=sts.id_status WHERE bsp.buttleid=%d AND bsp.pokeid="%s"',$b,$p);
    if(isset($q['tittle_status'])) $a = "<font color='Green'><b>".$q['tittle_status']."</b></font><br>";
      else  $a = "";
 return $a;
}

if(isset($_GET['udar']) AND  ($usersbattlone == $_SESSION['id'])){
  if($_GET['udar'] == 'sur'){
    if(info_uses($usersbattltwo,'pvp') > 0){
      update('users',array('pvp'=>0),'id='.(int)$_SESSION['id']);
      update('battles',array('pobeda'=>2),'id='.(int)$pvp_id);
      echo "<script>location.href='/game.php?go=char';</script>";
      exit; 
    }
  }  
  if(($_GET['udar'] == 'getHP') && (poke_info($pokeTwo,'hp_my') <= 0) && ($attacOne== 0) && (poke_info($pokeOne,'hp_my') > 0)){
    $timer = 120;
    update('battles',array('attac_1'=>997, 'time_2'=>$timer),'id='.(int)$pvp_id);
		  $Data_new = "";
update('battles',array('dates'=>$Data_new),'id='.(int)$pvp_id);
    echo "<script>parent._location.location.href='/game.php?go=fight_pvp';</script>";
    exit; 
  } 
  $Data_new = "";
update('battles',array('dates'=>$Data_new),'id='.(int)$pvp_id);
}
if(isset($_GET['udar']) AND  ($usersbattltwo == $_SESSION['id'])){
  if($_GET['udar'] == 'sur'){
    if(info_uses($usersbattlone,'pvp') > 0){
      update('users',array('pvp'=>0),'id='.(int)$_SESSION['id']);
      update('battles',array('pobeda'=>1),'id='.(int)$pve_id);
      echo "<script>location.href='/game.php?go=char';</script>";
      exit;
    } 
  }                                        
  if(($_GET['udar'] == 'getHP') && (poke_info($pokeOne,'hp_my') <= 0) && ($attacTwo == 0) && (poke_info($pokeTwo,'hp_my') > 0)){
    $timer = 120;
    update('battles',array('attac_2'=>997, 'time_1'=>$timer),'id='.(int)$pvp_id);
	  $Data_new = "";
update('battles',array('dates'=>$Data_new),'id='.(int)$pvp_id);
    echo "<script>parent._location.location.href='/game.php?go=fight_pvp';</script>";
    exit;
  } 
} 

// Юзаем батл
if((my_battle($pve_id,'attac_1') != 0) && (my_battle($pve_id,'attac_2') != 0) && (my_battle($pve_id,'poke_1') != false) && (my_battle($pve_id,'poke_2') != false)){
  $a1 = $attacOne;
  $a2 = $attacTwo;
  update('battles',array('attac_1'=>0, 'attac_2'=>0),'id='.(int)$pve_id);
  $ot = dmg($pokeOne,$pokeTwo,$a1,$a2,$pve_id);
  query('UPDATE battles SET raund=raund+1 WHERE id=%d',$pve_id);
}

// Смена покемона ************************************************************************************************************    
if (!empty($_POST['smena']) && !empty($_POST['rows'])){
  $poke = obr_chis($_POST['smena']);
  $poke = 'pvp_'.$poke;  
  $rows_res = obr_chis($_POST['rows']);
  if($pokeOne == "" OR $pokeTwo == ""){ $temer = time()+180; }else{$temer = 120;}
  	  $Data_new = "";
update('battles',array('dates'=>$Data_new),'id='.(int)$pvp_id);
  if(!$rows_res OR !$poke) die("<script>location.href='/game.php?go=fight_pvp';</script>"); 
  
 if($usersbattlone == $_SESSION['id'] && (poke_info($poke,'users') == $_SESSION['id'])){
      if(($rows_res == 1) AND (poke_info($pokeOne,'hp_my') <= 0)){ 
              update('battles',array('poke_1'=>$poke, 'attac_2'=>'0'),'user_1='.(int)$_SESSION['id'].' AND id='.(int)$pve_id);
              $mess_go = "<b>$_SESSION[login]</b>, выбирает: #".poke_info($poke,'names');
              insert('battle_log',array('battle_id'=>$pve_id, 'demage'=>$mess_go, 'raund'=>$pveRound));
              echo "<script>location.href='game.php?go=fight_pvp';</script>"; 
              exit;
      }
      elseif($rows_res == 2 AND $pokeOne == false){
              update('battles',array('poke_1'=>$poke, 'time_2'=>$temer),'user_1='.(int)$_SESSION['id'].' AND id='.(int)$pve_id);
              update('battles',array('hod_user_id'=>2),'id='.(int)$pve_id);
              echo "<script>location.href='game.php?go=fight_pvp';</script>";
              exit;
      }
      elseif($rows_res == 3){
              $po_p = "999";
              update('battles',array('attac_1'=>$po_p, 'time_2'=>$temer, 'to_p'=>$poke, 'zamtru_1'=>0),'id='.(int)$pve_id);
              echo "<script>location.href='game.php?go=fight_pvp';</script>";
              exit;
     }
 $battlPve  = first('SELECT * FROM battles WHERE (user_1=%d OR user_2=%d) AND batl_tip="pvp" AND id=%d',$_SESSION['id'],$_SESSION['id'],$myrow['battleid']);

 }
 if($usersbattltwo == $_SESSION['id'] && (poke_info($poke,'users') == $_SESSION['id'])){
      if(($rows_res == 1) AND (poke_info($pokeTwo,'hp_my') <= 0)){ 
              update('battles',array('poke_2'=>$poke, 'attac_1'=>'0'),'user_2='.(int)$_SESSION['id'].' AND id='.(int)$pve_id);
              $mess_go = "<b>".$_SESSION['login']."</b>, выбирает: #".poke_info($poke,'names');
              insert('battle_log',array('battle_id'=>$pve_id, 'demage'=>$mess_go, 'raund'=>$pveRound));
              echo "<script>location.href='game.php?go=fight_pvp';</script>"; 
              exit;
      }
      elseif($rows_res == 2 AND $pokeTwo == false){
              update('battles',array('poke_2'=>$poke, 'time_1'=>$temer),'user_2='.(int)$_SESSION['id'].' AND id='.(int)$pve_id);
              update('battles',array('hod_user_id'=>1),'id='.(int)$pve_id);
              echo "<script>location.href='game.php?go=fight_pvp';</script>";
              exit;
      }
      elseif($rows_res == 3){
              $po_p = "999";
              update('battles',array('attac_2'=>$po_p, 'time_1'=>$temer, 'to_p2'=>$poke, 'zamtru_2'=>0),'id='.(int)$pve_id);
              echo "<script>location.href='game.php?go=fight_pvp';</script>";
              exit;
     }
 } 
 $battlPve  = first('SELECT * FROM battles WHERE (user_1=%d OR user_2=%d) AND batl_tip="pvp" AND id=%d',$_SESSION['id'],$_SESSION['id'],$myrow['battleid']);

 }  
// Смена покемона *****КОНЕЦ******************************************************************************


// Победы *********************************************************************************************
if($usersbattlone == $_SESSION['id'] AND $pobeda == 1){
  $clan_user1 = first('SELECT u.clanid, c.clan_reputation, c.clan_lvl, c.id_clan FROM users u inner join clans c on u.clanid=c.id_clan WHERE u.id=%d',$_SESSION['id']);
  $clan_user2 = first('SELECT u.clanid, c.clan_reputation, c.clan_lvl, c.id_clan FROM users u inner join clans c on u.clanid=c.id_clan WHERE u.id=%d',$usersbattltwo);
  query('UPDATE users SET rang_a=rang_a+2, rang_b=rang_b+2  WHERE id=%d',$_SESSION['id']);
  query('UPDATE users SET rang_a=rang_a+1, rang_b=rang_b-2  WHERE id=%d',$usersbattltwo);
   
   $text_log = "<b>Пользователь: ".$_SESSION['login'].", <br> Выиграл бой у: ".users($usersbattltwo,'login')."</b>";
         writeLoges($text_log,'battle',$_SESSION['id']);
      if(!$clan_user2 OR !$clan_user1){ 
       // not...
      }else{
        if($clan_user1['id_clan'] == $clan_user2['id_clan']){
          // not...
        }else{
              if(my_battle($pvp_id,'tips_battle') == 1 AND clan_battle($clan_user1['id_clan']) AND $zahvat['yes'] == 1){
                  query('UPDATE clans_battle SET cools_num=cools_num+1 WHERE id_clan=%d',$clan_user1['id_clan']);
              }else{  
                  query('UPDATE users SET clan_point=clan_point+1 WHERE id=%d',$_SESSION['id']);
                  query('UPDATE clans SET clan_reputation=clan_reputation+1 WHERE id_clan=%d',$clan_user1['id_clan']);
                  query('UPDATE clans SET clan_reputation=clan_reputation-1 WHERE id_clan=%d',$clan_user2['id_clan']);
                  query('UPDATE users SET clan_point=clan_point-1 WHERE id=%d',$usersbattltwo);
              }
        }
      }
    $my_pokes_yes .= "<b><font color='brown'>Победа за вами!</font></b><br>";
    $my_pokes_yes .= '<INPUT type="button" title="Уйти" value="Уйти" onclick="location.href=\'/game.php?go=char\';" style="width:120px;" > ';
    
    echo '
    <LINK REL = "Stylesheet" HREF = "/css/battle.css" TYPE = "text/css">
   <center>
    <div style="background:#CFCFCF;width:60%;" >
    '.
     $my_pokes_yes
    .'
    </div>
     </center>
    ';
    logdel($pve_id);
    update('users',array('pvp'=>0),'id='.(int)$_SESSION['id']);
 exit;
}
if($usersbattltwo == $_SESSION['id'] AND $pobeda == 2){
  $clan_user1 = first('SELECT u.clanid, c.clan_reputation, c.clan_lvl, c.id_clan FROM users u inner join clans c on u.clanid=c.id_clan WHERE u.id=%d',$_SESSION['id']);
  $clan_user2 = first('SELECT u.clanid, c.clan_reputation, c.clan_lvl, c.id_clan FROM users u inner join clans c on u.clanid=c.id_clan WHERE u.id=%d',$usersbattlone);
  query('UPDATE users SET rang_a=rang_a+3, rang_b=rang_b+2 WHERE id=%d',$_SESSION['id']);
  query('UPDATE users SET rang_a=rang_a+1, rang_b=rang_b-2 WHERE id=%d',$usersbattlone);
   $text_log = "<b>Пользователь: ".$_SESSION['login'].", <br> Выиграл бой у: ".users($usersbattlone,'login')."</b>";
         writeLoges($text_log,'battle',$_SESSION['id']);
      if(!$clan_user2 OR !$clan_user1){ 
       // not...  
      }else {
        if($clan_user1['id_clan'] == $clan_user2['id_clan']){
          // not...
        }else{
            if(my_battle($pvp_id,'tips_battle') == 1 AND clan_battle($clan_user1['id_clan']) AND $zahvat['yes'] == 1){
                  query('UPDATE clans_battle SET cools_num=cools_num+1 WHERE id_clan=%d',$clan_user1['id_clan']);
            }else{  
                  query('UPDATE users SET clan_point=clan_point+1 WHERE id=%d',$_SESSION['id']);
                  query('UPDATE clans SET clan_reputation=clan_reputation+1 WHERE id_clan=%d',$clan_user1['id_clan']);
                  query('UPDATE clans SET clan_reputation=clan_reputation-1 WHERE id_clan=%d',$clan_user2['id_clan']);
                  query('UPDATE users SET clan_point=clan_point-1 WHERE id=%d',$usersbattlone);
            }
       }
     }
    $my_pokes_yes .= "<b><font color='brown'>Победа за вами!</font></b><br>";
    $my_pokes_yes .= '<INPUT type="button" title="Уйти" value="Уйти" onclick="location.href=\'/game.php?go=char\';" style="width:120px;" > ';    
    echo '
    <LINK REL = "Stylesheet" HREF = "/css/battle.css" TYPE = "text/css">
   <center>
    <div style="background:#CFCFCF;width:60%;" >
    '.
     $my_pokes_yes
    .'
    </div>
     </center>
    ';
    logdel($pve_id);
    update('users',array('pvp'=>0),'id='.(int)$_SESSION['id']);
 exit;
}
// Победы *конец*    

// Если окончен тайм-аут начисляем победу нужному юзеру 
  if($attacOne == "0" AND $attacTwo != "0" AND $time_1 <= 0){
      update('battles',array('pobeda'=>2),'id='.(int)$pve_id);
      update('users',array('pvp'=>0),'id='.(int)$usersbattlone);
      
  }
  if($attacTwo == "0" AND $attacOne != "0" AND $time_2 <= 0){
      update('battles',array('pobeda'=>1),'id='.(int)$pve_id);
      update('users',array('pvp'=>0),'id='.(int)$usersbattltwo);
  }   
// Если окончен тайм-аут начисляем победу нужному юзеру *конец*

// Апдейт когда оба покемона выбраны
if($pokeTwo != false AND $pokeOne != false AND $hodUsers != 3) 
      update('battles',array('hod_user_id'=>3),'id='.(int)$pve_id); 
// Апдейт когда оба покемона выбраны *конец*  

if($usersbattlone == $_SESSION['id'] && ($hodUsers == 1 || $hodUsers == 2)){
  $timer_off = $time_2;
  $timer_off = $timer_off - time();
  $tt = "Тайм: $timer_off сек.";
  if($time_2 < time() && $pokeTwo == false){
    update('battles',array('pobeda'=>1),'id='.(int)$pve_id);
  }
  if($pokeOne == false){
     $hp_one_user_poke = select('SELECT id,names,lvl FROM pok_user WHERE users=%d AND active=1 AND hp_my > 0',$_SESSION['id']);
      if($hp_one_user_poke){
          $my_pokes_yes .= "<b><font color='brown'>Выберете покемона для продолжения:</font></b>";
          $my_pokes_yes .= "<td>";
          $my_pokes_yes .= "<form action='' method='POST'>";
          $my_pokes_yes .= "<select size='1' name='smena'>";
          foreach($hp_one_user_poke as $smena_pok_id)
          {
          $my_pokes_yes    .= "<option value='".$smena_pok_id['id']."'>".$smena_pok_id['names']." ".$smena_pok_id['lvl']." - lvl</option>";
          }  
          $my_pokes_yes    .= "</select><input type='hidden' name='rows' value='2'><br>"; 
          $my_pokes_yes    .= "<input type='submit' name='submit' value='Выбрать'> </form></td>";
      }else{
          $my_pokes_yes    .= "<b><h1>Поражение!</h1></b>";
          update('battles',array('pobeda'=>2),'id='.(int)$pve_id); 
      }
  }else{
    $my_pokes_yes .= "<b><font color='brown'>Ожидаем ответ соперника...</font></b><br>".$tt."<br>";
    $my_pokes_yes .= '<INPUT type="button" title="Сдаться" value="Сдаться" onclick="location.href=\'/game.php?go=fight_pvp&udar=sur\';" style="width:120px;" > ';
  }    
    echo '
    <LINK REL = "Stylesheet" HREF = "/css/battle.css" TYPE = "text/css">
    <meta http-equiv="refresh" content="10" /> 
   <center>
    <div style="background:#CFCFCF;width:60%;" >
    '.
     $my_pokes_yes
    .'
    </div>
     </center>
    ';
 exit;
}
if($usersbattltwo == $_SESSION['id'] && ($hodUsers == 1 || $hodUsers == 2)){
  $timer_off = $time_1;
  $timer_off = $timer_off - time();
  $tt = "Тайм: $timer_off сек.";
  if($time_1 < time() && $pokeOne == false){
    update('battles',array('pobeda'=>2),'id='.(int)$pve_id);
  }
  if($pokeTwo == false){
     $hp_one_user_poke = select('SELECT id,names,lvl FROM pok_user WHERE users=%d AND active=1 AND hp_my > 0',$_SESSION['id']);
      if($hp_one_user_poke){
          $my_pokes_yes .= "<b><font color='brown'>Выберете покемона для продолжения:</font></b>";
          $my_pokes_yes .= "<td>";
          $my_pokes_yes .= "<form action='' method='POST'>";
          $my_pokes_yes .= "<select size='1' name='smena'>";
          foreach($hp_one_user_poke as $smena_pok_id)
          {
          $my_pokes_yes    .= "<option value='".$smena_pok_id['id']."'>".$smena_pok_id['names']." ".$smena_pok_id['lvl']." - lvl</option>";
          }  
          $my_pokes_yes    .= "</select><input type='hidden' name='rows' value='2'><br>"; 
          $my_pokes_yes    .= "<input type='submit' name='submit' value='Выбрать'> </form></td>";
      }else{
          $my_pokes_yes    .= "<b><h1>Поражение!</h1></b>";
          update('battles',array('pobeda'=>1),'id='.(int)$pve_id); 
      }
  }else{
    $my_pokes_yes .= "<b><font color='brown'>Ожидаем ответ соперника...</font></b><br>".$tt."<br>";
    $my_pokes_yes .= '<INPUT type="button" title="Сдаться" value="Сдаться" onclick="location.href=\'/game.php?go=fight_pvp&udar=sur\';" style="width:120px;" > ';
  }    
    echo '
    <LINK REL = "Stylesheet" HREF = "/css/battle.css" TYPE = "text/css">
    <meta http-equiv="refresh" content="10" /> 
   <center>
    <div style="background:#CFCFCF;width:60%;" >
    '.
     $my_pokes_yes
    .'
    </div>
     </center>
    ';
 exit;
}


// Юзаем атаку
if(!empty($_GET['atk']) && (($_GET['atk'] == 1) OR ($_GET['atk'] == 2) OR ($_GET['atk'] == 3) OR ($_GET['atk'] == 4))){
  if(poke_info($pokeTwo,'hp_my') > 0 && $usersbattltwo == $_SESSION['id'] && $сompulsiontwo == 0) include('include/function/atk.php');
  if(poke_info($pokeOne,'hp_my') > 0 && $usersbattlone == $_SESSION['id'] && $сompulsionone == 0) include('include/function/atk.php');
  
}
// Конец юзание атаки

  
/** Юзаем итем через пост **/
if (isset($_POST['itemgo'])){
  $temer = 120;
  $i = $_POST['itemgo']; 
  $itemUser = first('SELECT id,item_id FROM items_users WHERE user_id=%d AND id=%d',$_SESSION['id'],$i);
  if(empty($itemUser['id'])) no_href_locs();
  if($itemUser['item_id'] == 3) no_href_locs();
        if(($usersbattlone == $_SESSION['id']) && (poke_info($pokeOne,'hp_my') > 0) && $сompulsionone == 0){
         if(provitems($itemUser['item_id'],1) && infoItems($itemUser['item_id'],'battleuse') == 1){
              $po_p = "998";
              update('battles',array('attac_1'=>$po_p, 'time_2'=>$temer, 'to_it'=>$itemUser['item_id']),'id='.(int)$pvp_id);        
         }
        }
        if(($usersbattltwo == $_SESSION['id']) && (poke_info($pokeTwo,'hp_my') > 0) && $сompulsiontwo == 0){
         if(provitems($itemUser['item_id'],1) && infoItems($itemUser['item_id'],'battleuse') == 1){
              $po_p = "998";
              update('battles',array('attac_2'=>$po_p, 'time_1'=>$temer, 'to_it2'=>$itemUser['item_id']),'id='.(int)$pvp_id);        
         }
        }
  unset($_POST['itemgo']);
  die("<script>location.href='game.php?go=fight_pvp';</script>");
}
/** Юзаем итем через пост *КОНЕЦ* **/

/*if(my_battle($pve_id,'attac_1')>0){
  $a = attacPokeDic(poke_info($pokeTwo,'basenum'),poke_info($pokeTwo,'lvl'));
  update('battles',array('attac_2'=>$a),'id='.(int)$pve_id);
  dmg(my_battle($pve_id,'poke_1'),my_battle($pve_id,'poke_2'),my_battle($pve_id,'attac_1'),my_battle($pve_id,'attac_2'),$pve_id);
  update('battles',array('attac_1'=>0, 'attac_2'=>0, 'raund'=>$pveRound+1),'id='.(int)$pve_id);
  $battlPve  = first('SELECT * FROM battles WHERE user_1=%d AND batl_tip="pve" AND id=%d',$_SESSION['id'],$pve_id);
}     */               


$ms = false;
$log_selekt = select('SELECT raund,demage FROM battle_log WHERE battle_id=%d ORDER BY id DESC',$pve_id);    
foreach($log_selekt as $log_go){
  $ms .=  '<table style=" padding: 5px; margin: 20px;"><tr>';
  $ms .=  '<td><font color="gold" size = "6"><b>'.$log_go['raund'].'</b></font><td>';
  $ms .=  '<td>'.$log_go['demage'].'<td>';
  $ms .=  '</tr></table><hr>';
}

$pogoda_name = first('SELECT name_pogod FROM pogoda WHERE id_pog=%d',$battlPve['id_pogodi']); 
if(isset($pogoda_name['name_pogod'])) $pogoda_battle = "<font color='DarkGreen'><b> ".$pogoda_name['name_pogod'].".</b></font>";
  else $pogoda_battle = "<font color='DarkGreen'><b> Обычная.</b></font>";

$pokem_one = $battlPve['poke_1'];
$pokem_two = $battlPve['poke_2'];
$at  = $battlPve['attac_1'];
$at2 = $battlPve['attac_2'];

if($pokem_one != false && $pokem_two != false){

}else{
 die();
}

$battlPve  = first('SELECT * FROM battles WHERE id=%d',$myrow['battleid']);
if($usersbattlone == $_SESSION['id']){
  $pokem_one = $battlPve['poke_1']; 
  $pokem_two = $battlPve['poke_2'];
  $user      = $battlPve['user_1'];
  $userNo    = $battlPve['user_2'];
  $timer_off = $battlPve['time_2'];
  $сompulsion = $battlPve['zamtru_1'];
  $useText = 'user_1';
  $comText = 'zamtru_1';
  $pobeda    = 1;
  $pobedaNo  = 2;
  $at  = my_battle($pvp_id,'attac_1');
  $at2 = my_battle($pvp_id,'attac_2');
}else{
  $pokem_one = $battlPve['poke_2'];
  $pokem_two = $battlPve['poke_1'];
  $user      = $battlPve['user_2'];
  $userNo    = $battlPve['user_1'];
  $timer_off = $battlPve['time_1'];
  $сompulsion = $battlPve['zamtru_2'];
  $useText = 'user_2';
  $comText = 'zamtru_2';
  $pobeda    = 2;
  $pobedaNo  = 1;
  $at  = my_battle($pvp_id,'attac_2');
  $at2 = my_battle($pvp_id,'attac_1');
}

$color_stst = "red";
$timer_off = $timer_off;
$tt = $timer_off;         
$hp_my = poke_info($pokem_one,'hp_my');
$hp_no = poke_info($pokem_two,'hp_my');
$h1 = (poke_info($pokem_one,'hp_my')/poke_info($pokem_one,'hp_max'))*100;
$h1 = ($h1>100?100:$h1); 
if($h1 < 20) { $color1 = "#CD0000;"; } else { $color1 = "#008B45;"; }
$h2 = (poke_info($pokem_two,'hp_my')/poke_info($pokem_two,'hp_max'))*100;
$h2 = ($h2>100?100:$h2);
if($h2 < 20) { $color2 = "#CD0000;"; } else { $color2 = "#008B45;"; }
$pokeUsersOne = obrid($pokem_one);
$pokeUsersOne = $pokeUsersOne['id'];
$hp_one_user_poke = select('SELECT id,names FROM pok_user WHERE users=%d AND active=1 AND hp_my > 0 AND id != %d',$_SESSION['id'],$pokeUsersOne);
$count_user_poke  = first('SELECT COUNT(*) as countpok FROM pok_user WHERE users=%d AND active=1 AND hp_my>0',$_SESSION['id']);
$result_item_use  = select('SELECT i.id, i.count, il.name FROM items_users i inner join items il on il.id=i.item_id AND il.battleuse=1 WHERE user_id=%d ORDER BY il.id DESC',$_SESSION['id']);
$expPolos = lvl_polos(poke_info($pokem_one,'lvl'),poke_info($pokem_one,'exp'));                                                                                
if($expPolos>100) $expPolos = 100; if($expPolos <= 0) $expPolos = 0;
if(isset($_GET['timeout'])){
$tt2 = $t1 - 1;
if($ind == 1){
update('battles',array('time_1'=>$tt2),'id='.(int)$pvp_id);  
}else{
update('battles',array('time_2'=>$tt2),'id='.(int)$pvp_id); 
}
if($a_my == 0){
  die("<script>location.href='game.php?go=fight_pvp';</script>");
}
echo $tt2;
return;
}
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<HEAD>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; Charset=Windows-1251">
<LINK REL = "Stylesheet" HREF = "css/battle.css" TYPE = "text/css">
<script type="text/javascript" src="script/jquery.js"></script>
<script type="text/javascript" src="script/pkmnBattle.js"></script>
<style>
.tooltip {
			border-bottom: 1px dotted #000000; color: #000000; outline: none;
			cursor: help; text-decoration: none;
			position: relative;
		}
		.tooltip span {
			margin-left: -999em;
			position: absolute;
		}
		.tooltip:hover span {
			border-radius: 5px 5px; -moz-border-radius: 5px; -webkit-border-radius: 5px; 
			box-shadow: 5px 5px 5px rgba(0, 0, 0, 0.1); -webkit-box-shadow: 5px 5px rgba(0, 0, 0, 0.1); -moz-box-shadow: 5px 5px rgba(0, 0, 0, 0.1);
			font-family: Calibri, Tahoma, Geneva, sans-serif;
			position: absolute; left: 1em; top: -3em; z-index: 99;
			margin-left: 0; width: 250px;
		}
		.tooltip:hover img {
			border: 0; margin: -10px 0 0 -55px;
			float: left; position: absolute;
		}
		.tooltip:hover em {
			font-family: Candara, Tahoma, Geneva, sans-serif; font-size: 1.2em; font-weight: bold;
			display: block; padding: 0.2em 0 0.6em 0;
		}
		.classic { padding: 0.8em 1em; }
		.custom { padding: 0.5em 0.8em 0.8em 2em; }
		* html a:hover { background: transparent; }
		.classic {background: #FFFFAA; border: 1px solid #FFAD33; }
		.critical { background: #FFCCAA; border: 1px solid #FF3334;	}
		.help { background: #9FDAEE; border: 1px solid #2BB0D7;	}
		.info { background: #9FDAEE; border: 1px solid #2BB0D7;	}
		.warning { background: #FFFFAA; border: 1px solid #FFAD33; }

</style>
</HEAD>
<?php if($a_my > 0 and $a_vs < 1){ ?>
 <script language="JavaScript">
   function timeOut() {
	$("#timer").load("game.php?go=fight_pvp&timeout=1");
	     setTimeout("timeOut()",1000);
	  }    
 </script>
 <?php } ?>
<body onload="timeOut()">
<?php
$textGoodsno = false;
$okAtcFan = true;
$pokeAt = obrid($pokem_one);
$pokeAt = $pokeAt['id'];
$one  =  name_atc(1,$pokeAt);
$two  =  name_atc(2,$pokeAt);
$thre =  name_atc(3,$pokeAt);
$four =  name_atc(4,$pokeAt);
$pp1 =  atac_pp(1,$pokeAt);
$pp2 =  atac_pp(2,$pokeAt);
$pp3 =  atac_pp(3,$pokeAt);
$pp4 =  atac_pp(4,$pokeAt);
if(!empty($one)) $one   = $one.'<br>'.atktip(name_atc(1,$pokeAt,1));
if(!empty($two)) $two   = $two.'<br>'.atktip(name_atc(2,$pokeAt,1));
if(!empty($thre)) $thre = $thre.'<br>'.atktip(name_atc(3,$pokeAt,1));
if(!empty($four)) $four = $four.'<br>'.atktip(name_atc(4,$pokeAt,1));
$oncOne = 'onclick="window.location.href=\'/game.php?go=fight_pvp&atk=1\';"';
$oncTwo = 'onclick="window.location.href=\'/game.php?go=fight_pvp&atk=2\';"';
$oncThr = 'onclick="window.location.href=\'/game.php?go=fight_pvp&atk=3\';"';
$oncFou = 'onclick="window.location.href=\'/game.php?go=fight_pvp&atk=4\';"';
if(!atac_pp_isset(1,$pokeAt) && $one)  { $one  = '<s>'.$one.'</s>';  $pp1 = '<s>'.$pp1.'</s>'; $oncOne = "";}
if(!atac_pp_isset(2,$pokeAt) && $two)  { $two  = '<s>'.$two.'</s>';  $pp2 = '<s>'.$pp2.'</s>'; $oncTwo = "";}
if(!atac_pp_isset(3,$pokeAt) && $thre) { $thre = '<s>'.$thre.'</s>'; $pp3 = '<s>'.$pp3.'</s>'; $oncThr = "";}
if(!atac_pp_isset(4,$pokeAt) && $four) { $four = '<s>'.$four.'</s>'; $pp4 = '<s>'.$pp4.'</s>'; $oncFou = "";}
?>
	  <div id="MovesBlock">
		  <?php
if($сompulsion > 0){
 if($count_user_poke['countpok'] > 1){
   $okAtcFan = false;
   $textGoodsno = '<div id="atc" style="padding:10px;width:50%;text-align:center;font-weight:bold;cursor: default;"><table  align="center"><tr><td align="center" colspan=2> <b>Принудительная смена покемона.<br>Выберите покемона: </b><td></tr><tr>';
   if($hp_one_user_poke){
      $textGoodsno .= "<td  align=\"center\">";
      $textGoodsno .= "<form action='' method='POST'>";
      $textGoodsno .= "<select size='1' name='smena'>";
      foreach($hp_one_user_poke as $smena_pok_id){
        $textGoodsno    .= "<option value='".$smena_pok_id['id']."'>".$smena_pok_id['names']."</option>";
      }  
      $textGoodsno  .= "</select><input type='hidden' name='rows' value='3'> "; 
      $textGoodsno  .= "<input type='submit' name='submit' value='Заменить'> </form></td>";
   }
   $textGoodsno .= '
     </tr>
     <tr>
      <td colspan=2 align="center">
        <INPUT type="button" title="Сдаться" value="Сдаться" onclick="window.location.href=\'/game.php?go=fight_pvp&udar=sur\';" style="width:120px;" >
      </td>
    </tr>
   </table></div>
   ';
 }else{
   update('battles',array($comText=>0),' '.$useText.'='.(int)$_SESSION['id'].' AND id='.(int)$battlPve['id']);
 }
}

  if($hp_my > 0){
    if($hp_no > 0){
      if($okAtcFan == true){
        if($at == 0){
        ?>
            <tr>                  
              <td width="50%">
              <?php 
               if($one){
              ?>
                <div <?php print $oncOne;?> ID = "atc">
                  <center>
                    <b><?php echo $one;?></b>
                    <br />
                    <font style="font-size: 11px;">PP: <?php echo $pp1;?></font>
                  </center>
                </div>
              <?php
              }
              ?>
              </td>
			  <br>
              <td width="50%">
              <?php 
               if($two) {
              ?>
                <div <?php print $oncTwo;?> ID = "atc">
                  <center>
                    <b><?php echo $two;?></b>
                    <br />
                    <font style="font-size: 11px;">PP: <?php echo $pp2;?></font>
                  </center>
                </div>
              <?php
               }
              ?>
              </td>
            </tr>
			<br>
            <tr>
              <td width="50%">
              <?php 
               if($thre){
              ?>
                <div <?php print $oncThr;?> ID = "atc">
                  <center>
                    <b><?php echo $thre;?></b>
                    <br />
                    <font style="font-size: 11px;">PP: <?php echo $pp3;?></font>
                  </center>
                </div>
              <?php
               }
              ?>
              </td>
			  <br>
            <td width="50%">
            <?php 
            if($four){
            ?>
              <div <?php print $oncFou;?> ID = "atc">
                <center>
                  <b><?php echo $four;?></b>
                  <br />
                  <font style="font-size: 11px;">PP: <?php echo $pp4;?></font>
                </center>
              </div>
            <?php
            }
            ?>
            </td>
            </tr>
          <table>
          <?php
          $my_pokes_yes = "";
              if($hp_one_user_poke){
                  $my_pokes_yes .= "<td>";
                  $my_pokes_yes .= "<form action='' method='POST'>";
                  $my_pokes_yes .= "<select size='1' name='smena'>";
                foreach($hp_one_user_poke as $smena_pok_id){
                  $my_pokes_yes    .= "<option value='".$smena_pok_id['id']."'>".$smena_pok_id['names']."</option>";
                }  
                  $my_pokes_yes    .= "</select><input type='hidden' name='rows' value='3'> "; 
                  $my_pokes_yes    .= "<input type='submit' name='submit' value='Заменить'> </form></td>";
              }
              if($result_item_use){
                  $my_pokes_yes    .= "<td>";
                  $my_pokes_yes    .= "<form action='' method='POST'>";
                  $my_pokes_yes    .= "<select size='1' name='itemgo'>";
                foreach($result_item_use as $use_item_id){
                  $my_pokes_yes    .= "<option value='".$use_item_id['id']."'>".$use_item_id['name']." x".$use_item_id['count']."</option>";
                }
                  $my_pokes_yes    .= "</select> ";
                  $my_pokes_yes    .= "<input type='submit' name='submit' value='Использовать'> </form></td>";    
              }
           echo "<tr>".$my_pokes_yes."</tr>";
          ?>
            <tr>
              <td colspan=2 align="center">
                <INPUT type="button" title="Сдаться" value="Сдаться" onclick="window.location.href='/game.php?go=fight_pvp&udar=sur';" style="width:120px;" >
              </td>
            </tr>
          </table>
        <?php
        }else{
          echo 
          '
          <br><br><br>
          <font style="font-size: 19px;">Ожидаем ответ соперника...</font>
          <br><br>
          <DIV ID=timer style=\'text-align:center; width:20%; font-weight:bold; font-size:24px;\'>'.
          $tt
          .'</DIV>
          <br>
          <INPUT type="button" title="Сдаться" value="Сдаться" onclick="window.location.href=\'/game.php?go=fight_pvp&udar=sur\';" style="width:120px;" >
          <br>
          ';
        }
      }else{ 
          print $textGoodsno;
      }        
    }else{
      if(my_battle($pvp_id,'attac_1') == 0)  echo "<script>parent._location_two.location.href='/game.php?go=fight_pvp&udar=getHP';</script>";
      echo '<br><br><br>
      <font style="font-size: 19px;">Ожидаем выбор соперника...</font>
      <br><br>
      <DIV ID=timer style=\'text-align:center; width:20%; font-weight:bold; font-size:24px;\'>'.$tt.'</DIV>
      <br>
      <INPUT type="button" title="Сдаться" value="Сдаться" onclick="window.location.href=\'/game.php?go=fight_pvp&udar=sur\';" style="width:120px;" >
      <br>';
   }
  }else{
  $my_pokes_yes = "";
      if($hp_one_user_poke){
        $my_pokes_yes .= "<br><br><b>Выберите покемона:</b><br>";
        $my_pokes_yes .= "<form action='' method='POST'>";
        $my_pokes_yes    .= "<select size='1' name='smena'>";
      foreach($hp_one_user_poke as $smena_pok_id){
            $my_pokes_yes    .= "<option value='".$smena_pok_id['id']."'>".$smena_pok_id['names']."</option>";
      }  
        $my_pokes_yes    .= "</select><input type='hidden' name='rows' value='1'>"; 
        $my_pokes_yes    .= "<input type='submit' name='submit' value='Сменить'> </form><br>";
        $out =  '<INPUT type="button" title="Сдаться" value="Сдаться" onclick="location.href=\'/game.php?go=fight_pvp&udar=sur\';" style="width:120px;" >';
      }else{
        $my_pokes_yes = "<br><h2>Поражение!</h2>";
        $out = '<INPUT type="button" title="Уйти" value="Уйти" onclick="window.location.href=\'/game.php?go=char\';" style="width:120px;" >';
        update('battles',array('pobeda'=>$pobedaNo),'id='.(int)$pvp_id);
        update('users',array('pvp'=>0),'id='.(int)$_SESSION['id']);  
      }
      echo 
      '<br><br><br>
      <div style="background:#CFCFCF;width:60%;" >
        <font style="font-size: 19px;" color = "brown"><b>У #'.poke_info($pokem_one,'names').' не достаточно HP для продолжения боя.</b></font>
        '.$my_pokes_yes.'
      </div>
      <br>'.$out.'<br>';
  }
  function NumPok($bpid) {
if (1 == $bpid or 1 < $bpid and 10 > $bpid) return "00".$bpid;
if (10 == $bpid or 10 < $bpid and 100 > $bpid) return "0".$bpid;
if (100 == $bpid or 100 < $bpid and 700 > $bpid) return $bpid;
}
  $p_two_im = poke_info($pokem_two,'basenum');
  $p_one_im = poke_info($pokem_one,'basenum');
?>
		  </div>  
<div id="BattleRoom">

          <div id="pokemon2">
		  <b>
		   <div id="pkmnInf"> 
		  <a href=javascript: onClick=win1=window.open('/game.php?go=pokedex&id=<?php echo poke_info($pokem_two,'basenum');?>','pokedex','width=550,height=550,scrollbars=yes');return true;><img src=img/other/pokedex.png></a>
          <? echo "#".poke_info($pokem_two,'names'); ?> <? echo poke_info($pokem_two,'lvl'); ?>  - lvl </div>
		  </b> <br>
		  <center><img id="pVs" src="pok/pok/<?php echo NumPok($p_two_im); ?>.gif"><br>
		  <div id="moves_two"></div>
		  <div style="width:100%; text-align: right; height:14; border: 2px solid black; border-radius: 4px;"><div style='width:<?php echo $h2?>%;background:<?php echo $color2?>height:14;  border-radius: 4px;' title="Количество здоровья покемона">
          <font style='font:11px Tahoma; color: #1C1C1C;'>
          <b><?php echo $hp_no."/".poke_info($pokem_two,'hp_max'); ?></b>
          </font>
          </div>
		  </div>
		  </center>
		  <a class="tooltip" href="#">Усиления<span class="classic"> 
		      <?php                         
    echo tittle_status($pokem_two,$battlPve['id']);
    $def_st_no_pl  = status_na_pokemone($pokem_two,$battlPve['id'],'plus','defend');
    $sdef_st_no_pl = status_na_pokemone($pokem_two,$battlPve['id'],'plus','spdefend');
    $atk_st_no_pl  = status_na_pokemone($pokem_two,$battlPve['id'],'plus','attac');
    $satk_st_no_pl = status_na_pokemone($pokem_two,$battlPve['id'],'plus','spattac');
    $acc_st_no_pl  = status_na_pokemone($pokem_two,$battlPve['id'],'plus','acc');
    $spd_st_no_pl  = status_na_pokemone($pokem_two,$battlPve['id'],'plus','speed');
    $acr_st_no_pl  = status_na_pokemone($pokem_two,$battlPve['id'],'plus','accuracy');
             if ($def_st_no_pl>0) echo "<b>Защита + ".$def_st_no_pl."</b> <br>";
             if ($sdef_st_no_pl>0)echo "<b>Спец. Защита + ".$sdef_st_no_pl."</b> <br>";
             if ($atk_st_no_pl>0) echo "<b>Атака + ".$atk_st_no_pl."</b><br> ";
             if ($satk_st_no_pl>0)echo "<b>Спец. Атака + ".$satk_st_no_pl."</b><br> ";
             if ($acc_st_no_pl>0) echo "<b>Ловкость + ".$acc_st_no_pl."</b><br> ";
             if ($spd_st_no_pl>0) echo "<b>Скорость + ".$spd_st_no_pl."</b> <br>";
             if ($acr_st_no_pl>0) echo "<b>Точность + ".$acr_st_no_pl."</b> <br>";
    $def_st_no_min  = status_na_pokemone($pokem_two,$battlPve['id'],'minus','defend');
    $sdef_st_no_min = status_na_pokemone($pokem_two,$battlPve['id'],'minus','spdefend');
    $atk_st_no_min  = status_na_pokemone($pokem_two,$battlPve['id'],'minus','attac');
    $satk_st_no_min = status_na_pokemone($pokem_two,$battlPve['id'],'minus','spattac');
    $acc_st_no_min  = status_na_pokemone($pokem_two,$battlPve['id'],'minus','acc');
    $spd_st_no_min  = status_na_pokemone($pokem_two,$battlPve['id'],'minus','speed');
    $acr_st_no_min  = status_na_pokemone($pokem_two,$battlPve['id'],'minus','accuracy');         
             if ($def_st_no_min>0) echo "<font color='".$color_stst."'><b>Защита - ".$def_st_no_min."</b></font><br> ";
             if ($sdef_st_no_min>0)echo "<font color='".$color_stst."'><b>Спец. Защита - ".$sdef_st_no_min."</b></font><br> ";
             if ($atk_st_no_min>0) echo "<font color='".$color_stst."'><b>Атака - ".$atk_st_no_min."</b></font><br> ";
             if ($satk_st_no_min>0)echo "<font color='".$color_stst."'><b>Спец. Атака - ".$satk_st_no_min."</b></font> <br>";
             if ($acc_st_no_min>0) echo "<font color='".$color_stst."'><b>Ловкость - ".$acc_st_no_min."</b></font><br> ";
             if ($spd_st_no_min>0) echo "<font color='".$color_stst."'><b>Скорость - ".$spd_st_no_min."</b></font><br> ";
             if ($acr_st_no_min>0) echo "<font color='".$color_stst."'><b>Точность - ".$acr_st_no_min."</b></font> <br>";
    $img2 = "/pok/".poke_info($pokem_two,'tips')."/".poke_info($pokem_two,'basenum').".jpg";
    ?>  
	</span></a>
		  </div>
		  <div id="pokemon1">
		  <b>
          <div id="pkmnInf">  <a href=javascript: onClick=win1=window.open('game.php?go=pokedex&id=<?php echo poke_info($pokem_one,'basenum');?>','pokedex','width=550,height=550,scrollbars=yes');return true;><img src=img/other/pokedex.png></a>
          <? echo "#".poke_info($pokem_one,'names'); ?> <? echo poke_info($pokem_one,'lvl'); ?> - lvl </div>
		  </b> <br>
		  <center><img id="pMy" src="pok/back/<?php echo NumPok($p_one_im); ?>.gif">
		  <div id="moves_one"></div>
		  <div style="width:100%; text-align: left; height:14; border: 2px solid black; border-radius: 4px;"><div style='width:<?php echo $h1?>%;background:<?php echo $color1?>height:14;  border-radius: 4px;' title="Количество здоровья покемона">
          <font style='font:11px Tahoma; color: #1C1C1C;'>
          <b><?php echo $hp_my."/".poke_info($pokem_one,'hp_max'); ?></b>
          </font>
          </div>
		  </div>
		  <?php print '<DIV align="left" style="width:'.$expPolos.'%;background:#4169e1;height:14px;font-size:9;color:Black;" title="Количество опыта покемона"><b>'.round($expPolos).'%</b></DIV>';?>
		  </center>
		  <a class="tooltip" href="#">Усиления<span class="classic"> <?php
echo tittle_status($pokem_one,$pve_id);
$def_st_my_pl  = status_na_pokemone($pokem_one,$pve_id,'plus','defend');
$sdef_st_my_pl = status_na_pokemone($pokem_one,$pve_id,'plus','spdefend');
$atk_st_my_pl  = status_na_pokemone($pokem_one,$pve_id,'plus','attac');
$satk_st_my_pl = status_na_pokemone($pokem_one,$pve_id,'plus','spattac');
$acc_st_my_pl  = status_na_pokemone($pokem_one,$pve_id,'plus','acc');
$spd_st_my_pl  = status_na_pokemone($pokem_one,$pve_id,'plus','speed');
$acr_st_my_pl  = status_na_pokemone($pokem_one,$pve_id,'plus','accuracy');
         if ($def_st_my_pl >0) echo "<b>Защита + ".$def_st_my_pl."</b><br> ";
         if ($sdef_st_my_pl>0) echo "<b>Спец. Защита + ".$sdef_st_my_pl."</b><br> ";
         if ($atk_st_my_pl >0) echo "<b>Атака + ".$atk_st_my_pl."</b><br> ";
         if ($satk_st_my_pl>0) echo "<b>Спец. Атака + ".$satk_st_my_pl."</b><br> ";
         if ($acc_st_my_pl >0) echo "<b>Ловкость + ".$acc_st_my_pl."</b><br> ";
         if ($spd_st_my_pl >0) echo "<b>Скорость + ".$spd_st_my_pl."</b><br> ";
         if ($acr_st_my_pl >0) echo "<b>Точность + ".$acr_st_my_pl."</b> <br>";
$def_st_my_min  = status_na_pokemone($pokem_one,$pve_id,'minus','defend');
$sdef_st_my_min = status_na_pokemone($pokem_one,$pve_id,'minus','spdefend');
$atk_st_my_min  = status_na_pokemone($pokem_one,$pve_id,'minus','attac');
$satk_st_my_min = status_na_pokemone($pokem_one,$pve_id,'minus','spattac');
$acc_st_my_min  = status_na_pokemone($pokem_one,$pve_id,'minus','acc');
$spd_st_my_min  = status_na_pokemone($pokem_one,$pve_id,'minus','speed');
$acr_st_my_min  = status_na_pokemone($pokem_one,$pve_id,'minus','accuracy');         
         if ($def_st_my_min>0) echo "<font color='".$color_stst."'><b>Защита - ".$def_st_my_min."</b></font> <br>";
         if ($sdef_st_my_min>0)echo "<font color='".$color_stst."'><b>Спец. Защита - ".$sdef_st_my_min."</b></font> <br>";
         if ($atk_st_my_min>0) echo "<font color='".$color_stst."'><b>Атака - ".$atk_st_my_min."</b></font><br> ";
         if ($satk_st_my_min>0)echo "<font color='".$color_stst."'><b>Спец. Атака - ".$satk_st_my_min."</b></font><br> ";
         if ($acc_st_my_min>0)echo  "<font color='".$color_stst."'><b>Ловкость - ".$acc_st_my_min."</b></font><br> ";
         if ($spd_st_my_min>0)echo  "<font color='".$color_stst."'><b>Скорость - ".$spd_st_my_min."</b></font><br> ";
         if ($acr_st_my_min>0)echo  "<font color='".$color_stst."'><b>Точность - ".$acr_st_my_min."</b></font> <br>";
$img1 = "/pok/".poke_info($pokem_one,'tips')."/".poke_info($pokem_one,'basenum').".jpg";
?></span></a>
		 
		  </div>
  
</div>
  <div id="logPvp">
			  <center>
    <b><font color = "#FFFFFF" size="2">Раунд: <?php echo $battlPve['raund'];?></font></b><br>
    <b><font color = "#FFFFFF">Погода:</font> <?php echo $pogoda_battle; ?></b>
  </center>
  <hr>
  <?echo $ms;?> 	 
   </div>
</body>
<script>
$(document).ready(function() {
StartBattle(<?php echo $battlPve['dates']; ?>);
   });
function StartBattle(data) {
    if(data.xod1 == <?php echo $ind; ?>) {
	if(data.ctg<?php echo $ind; ?> == 1){
aPhis(1);
}
if(data.ctg<?php echo $ind; ?> == 2){
aSpec(1,data.tip<?php echo $ind; ?>);
}
if(data.ctg<?php echo $ind; ?> == 3 || data.ctg<?php echo $ind; ?> > 3){
aStat(1);
}
<?php if($hp_invs > 0){ ?>
if(data.ctg<?php echo $invs; ?> == 1){
setTimeout(function() { aPhis(2); }, 1200)
}
if(data.ctg<?php echo $invs; ?> == 2){
setTimeout(function() { aSpec(2,data.tip<?php echo $invs; ?>); }, 1200)
}
if(data.ctg<?php echo $invs; ?> == 3 || data.ctg<?php echo $invs; ?> > 3){
setTimeout(function() { aStat(2); }, 1200)
}
<?php
}
?>
}else{
if(data.ctg<?php echo $invs; ?> == 1){
aPhis(2);
}
if(data.ctg<?php echo $invs; ?> == 2){
aSpec(2,data.tip<?php echo $invs; ?>);
}
if(data.ctg<?php echo $invs; ?> == 3 || data.ctg<?php echo $invs; ?> > 3){
aStat(2);
}
<?php if($hp_ind > 0){ ?>
if(data.ctg<?php echo $ind; ?> == 1){
setTimeout(function() { aPhis(1); }, 1200)
}
if(data.ctg<?php echo $ind; ?> == 2){
setTimeout(function() { aSpec(1,data.tip<?php echo $ind; ?>); }, 1200)
}
if(data.ctg<?php echo $ind; ?> == 3 || data.ctg<?php echo $ind; ?> > 3){
setTimeout(function() { aStat(1); }, 1200)
}
<?php } ?>
}
}
</script>
</html>