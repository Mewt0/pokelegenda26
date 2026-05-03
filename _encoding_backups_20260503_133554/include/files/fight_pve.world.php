<?php
$battlPve  = first('SELECT * FROM battles WHERE user_1=%d AND batl_tip="pve" AND id=%d',$_SESSION['id'],$myrow['battleid']);
if(empty($battlPve['id'])) die("<script>window.location.href='game.php?go=char';</script>");
function no_href_locs(){
  die("<script>location.href='/game.php?go=fight_pve';</script>");
}
require_once ('include/function/battle.functions.tip.php');
require_once('include/function/globfanction.php');
require_once ('include/function/battle.functions.php');

if (!function_exists('atktip')) {
function atktip($type){
    if ($type === null || $type === false || $type === '') return '';
    if (is_numeric($type)) {
        return '<span class="atk-tip">Type: #'.(int)$type.'</span>';
    }
    return '<span class="atk-tip">'.htmlspecialchars($type, ENT_QUOTES, 'UTF-8').'</span>';
}
}

if(empty($_SESSION['klicers'])) $_SESSION['klicers'] = 1;

if(!empty($_POST['code']) && !empty($_SESSION['klikercode'])){
    if($_POST['code'] == $_SESSION['klikercode']){
        $_SESSION['klicers'] = 0;
        $_SESSION['klikercode'] = false;
        die("<script>
              parent._location.document.getElementById('errorCapth').style.display = 'none';
              parent._location.location.href='game.php?go=fight_pve';</script>");
    }else{
      die("<script>
            parent._location.document.getElementById('capthcode')['code'].value='';
            parent._location.document.getElementById('errorCapth').style.display = 'block';
            parent._location.document.getElementById('errorCapth').innerHTML='Р СњР ВµР Р†Р ВµРЎР‚Р Р…РЎвЂ№Р в„– Р С”Р С•Р Т‘, Р С—Р С•Р Р†РЎвЂљР С•РЎР‚Р С‘РЎвЂљР Вµ!';
          </script>");
    }
}
if($_SESSION['klicers'] >= 25){
  function Generate_Chars(){
    $a = array('1','2','3','4','5','6','7','8','9','f','b','v','d','e','f','g','h','i','j','k','l','m','n','p','q','r','s','t','u','v','w','x','y','z');
    $v = array_rand($a,1);
    $_SESSION["captcha"]=$_SESSION["captcha"].$a[$v];
    return $a[$v];
  }
  if(empty($_SESSION['klikercode'])){
    $_SESSION['klikercode'] = false;
    $cc = mt_rand(4,6);
    for($i=0; $i<$cc; $i++) $_SESSION["klikercode"] .= Generate_Chars();
  }  
  if(!empty($_GET['code_new'])){
    $_SESSION['klikercode'] = false;
    $cc = mt_rand(4,6);
    for($i=0; $i<$cc; $i++) $_SESSION["klikercode"] .= Generate_Chars();
    die("<script>parent._location.location.href='game.php?go=fight_pve';</script>");
  }
}
 
$pve_id  = $battlPve['id'];
$pveRound  = $battlPve['raund'];
$usersbattlone = $battlPve['user_1'];
$usersbattltwo = $battlPve['user_2'];
$attacOne = $battlPve['attac_1'];
$attacTwo = $battlPve['attac_2'];
$pokeOne  = $battlPve['poke_1'];
$pokeTwo  = $battlPve['poke_2'];

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

if(!empty($_GET['udar']) && $_GET['udar'] == 'sur'){
    $a = time()+30;    
    if($myrow['rang_b'] > -125) query('UPDATE users SET pve=0, rang_a=rang_a+1, rang_b=rang_b-1, atack_poke=%d WHERE id=%d',$a,$_SESSION['id']);
      else
        query('UPDATE users SET pve=0, atack_poke=%d WHERE id=%d',$a,$_SESSION['id']);
    $_SESSION['klicers'] = $_SESSION['klicers']+1;
    logdel($pve_id);
	$onlTime = time() + 30*60;
	$tipPoke    =  poke_info($battlPve['poke_2'],'basenum');
	//EVENT
	if($tipPoke == 478 OR $tipPoke == 94 OR $tipPoke == 248 OR $tipPoke == 645 OR $tipPoke == 571 OR $tipPoke == 487) {
	query('UPDATE users SET timepoke=%d WHERE id=%d',$onlTime,$_SESSION['id']);
	}
    die("<script>location.href='/game.php?go=char';</script>");
} 

if($_SESSION['klicers'] < 25){
// Р В®Р В·Р В°Р ВµР С Р В°РЎвЂљР В°Р С”РЎС“
if(!empty($_GET['atk']) && (($_GET['atk'] == 1) OR ($_GET['atk'] == 2) OR ($_GET['atk'] == 3) OR ($_GET['atk'] == 4))){
  if(poke_info($pokeTwo,'hp_my') > 0) include('include/function/atk.php');
}
// Р С™Р С•Р Р…Р ВµРЎвЂ  РЎР‹Р В·Р В°Р Р…Р С‘Р Вµ Р В°РЎвЂљР В°Р С”Р С‘  
}
/** Р РЋР СР ВµР Р…Р В° Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В° РЎвЂЎР ВµРЎР‚Р ВµР В· Р С—Р С•РЎРѓРЎвЂљ **/
if (!empty($_POST['smena']) && !empty($_POST['rows'])){
  $poke     = obr_chis($_POST['smena']);
  $poke     = 'pvp_'.$poke;  
  $rows_res = obr_chis($_POST['rows']);
  if(poke_info($poke,'users') == $_SESSION['id']){
    if(($rows_res == 1) AND (poke_info($pokeOne,'hp_my') <= 0)){ 
        update('battles',array('poke_1'=>$poke, 'attac_1'=>'0', 'attac_1'=>'0'),'user_1='.(int)$_SESSION['id'].' AND id='.(int)$pve_id);
        $mess_go = "<b>".$_SESSION['login']."</b>, Р Р†РЎвЂ№Р В±Р С‘РЎР‚Р В°Р ВµРЎвЂљ: #".poke_info($poke,'names');
        insert('battle_log',array('battle_id'=>$pve_id, 'demage'=>$mess_go, 'raund'=>$pveRound+1));
        die("<script>location.href='game.php?go=fight_pve';</script>"); 
    }
    elseif($rows_res == 2){
       $po_p = "999";
       update('battles',array('attac_1'=>$po_p, 'time_2'=>0, 'to_p'=>$poke),'id='.(int)$pve_id);
       die("<script>location.href='game.php?go=fight_pve';</script>");
    }
  }
}
/** Р РЋР СР ВµР Р…Р В° Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В° РЎвЂЎР ВµРЎР‚Р ВµР В· Р С—Р С•РЎРѓРЎвЂљ *Р С™Р С›Р СњР вЂўР В¦* **/

/** Р В®Р В·Р В°Р ВµР С Р С‘РЎвЂљР ВµР С РЎвЂЎР ВµРЎР‚Р ВµР В· Р С—Р С•РЎРѓРЎвЂљ **/
if (!empty($_POST['itemgo'])){
  if(poke_info($pokeOne,'hp_my') > 0){ 
    $itemUser = first('SELECT item_id FROM items_users WHERE user_id=%d AND id=%d',$_SESSION['id'],$_POST['itemgo']);
    if(!empty($itemUser['item_id'])){
      if(provitems($itemUser['item_id'],1) && infoItems($itemUser['item_id'],'battleuse') == 1){
          if($itemUser['item_id'] == 3 && poke_info($pokeTwo,'poimka') == 0) { print "<script>alert('Р В­РЎвЂљР С•Р С–Р С• Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В° Р В»Р С•Р Р†Р С‘РЎвЂљРЎРЉ Р Р…Р ВµР В»РЎРЉР В·РЎРЏ!');</script>"; unset($_POST['itemgo']);} else {
            $po_p = "998";
            update('battles',array('attac_1'=>$po_p, 'time_2'=>0, 'to_it'=>$itemUser['item_id']),'id='.(int)$pve_id);
          }        
      }
    } 
  }
}
/** Р В®Р В·Р В°Р ВµР С Р С‘РЎвЂљР ВµР С РЎвЂЎР ВµРЎР‚Р ВµР В· Р С—Р С•РЎРѓРЎвЂљ *Р С™Р С›Р СњР вЂўР В¦* **/

if(my_battle($pve_id,'attac_1')>0){
  $a = attacPokeDic(poke_info($pokeTwo,'basenum'),poke_info($pokeTwo,'lvl'));
  update('battles',array('attac_2'=>$a),'id='.(int)$pve_id);
  dmg(my_battle($pve_id,'poke_1'),my_battle($pve_id,'poke_2'),my_battle($pve_id,'attac_1'),my_battle($pve_id,'attac_2'),$pve_id);
  update('battles',array('attac_1'=>0, 'attac_2'=>0, 'raund'=>$pveRound+1),'id='.(int)$pve_id);
  $battlPve  = first('SELECT * FROM battles WHERE user_1=%d AND batl_tip="pve" AND id=%d',$_SESSION['id'],$pve_id);
}                    

/** Р Р€РЎРѓР С‘Р В»Р ВµР Р…Р С‘РЎРЏ **/
$mdbatl = md5($battlPve['poke_1'].$pve_id);
if(empty($_SESSION['battleStatus'])) $_SESSION['battleStatus'] = array();
if($_SESSION['battleStatus'][1] != $mdbatl){
  $myX = obrid($battlPve['poke_1']);
  $i = first('SELECT id_items FROM items_poke WHERE id_poke=%d',$myX['id']);
  if(empty($i['id_items'])) $i['id_items'] = false;
  $item_battle = $_SESSION['battleStatus'][0] = $i['id_items']; 
  $_SESSION['battleStatus'][1] = $mdbatl;
  $stabUsers   = $_SESSION['battleStatus'][2] = statususers(); 
  $eventSrsver = $_SESSION['battleStatus'][3] = eventserver();
  $stabPokemon = $_SESSION['battleStatus'][4] = statuspokemon($myX['id'],$item_battle);
  $stabHappy   = $_SESSION['battleStatus'][5] = eventhappy(poke_info($battlPve['poke_1'],'happy'));
}elseif($_SESSION['battleStatus'][1] == $mdbatl){
  $item_battle = $_SESSION['battleStatus'][0];
  $stabUsers   = $_SESSION['battleStatus'][2];
  $eventSrsver = $_SESSION['battleStatus'][3];
  $stabPokemon = $_SESSION['battleStatus'][4];
  $stabHappy   = $_SESSION['battleStatus'][5];
}else{
  $item_battle = false;
  $stabUsers   = array();
  $eventSrsver = array();
  $stabPokemon = array();
  $stabHappy   = array();
}

$stabEnergy = 0;
$dropsEvent = $eventSrsver['drop']   + ($stabPokemon['drop']   + $stabUsers['drop']  + $stabHappy['drop']);
$expmyEvent = $eventSrsver['exp']    + ($stabPokemon['exp']    + $stabUsers['exp']   + $stabHappy['exp']);
$maneyEvent = $eventSrsver['maney']  + ($stabPokemon['maney']  + $stabUsers['maney'] + $stabHappy['maney']);
$happyEvent = $eventSrsver['happy']  + ($stabPokemon['happy']  + $stabUsers['happy'] + $stabHappy['happy']);
$energEvent = $eventSrsver['energy'] + ($stabPokemon['energy'] + $stabUsers['energy']+ $stabHappy['energy']);

$eventsText = '<span style="color:#fff;font-size:15px;font-weight:bold;cursor:pointer;" onClick="eventhtml();" title="Р Р€Р Р†Р ВµР В»Р С‘РЎвЂЎР ВµР Р…РЎвЂ№Р Вµ РЎР‚Р ВµР в„–РЎвЂљРЎвЂ№">Р СџРЎР‚Р С‘РЎРѓРЎС“РЎвЂљРЎРѓРЎвЂљР Р†РЎС“РЎР‹РЎвЂљ РЎС“Р Р†Р ВµР В»Р С‘РЎвЂЎР ВµР Р…Р С‘РЎРЏ  >></span>';
$eventsMess = false;
if($dropsEvent > 1) $eventsMess .= '<b style="color:#f754e1;">Р вЂќРЎР‚Р С•Р С—:</b> +<b>'.(($dropsEvent-1)*100).'%</b>.<br>';
if($expmyEvent > 1) $eventsMess .= '<b style="color:#7df9ff;">Р С›Р С—РЎвЂ№РЎвЂљ:</b> +<b>'.(($expmyEvent-1)*100).'%</b>.<br>';
if($maneyEvent > 1) $eventsMess .= '<b style="color:#ff7518;">Р СљР С•Р Р…Р ВµРЎвЂљРЎвЂ№:</b> +<b>'.(($maneyEvent-1)*100).'%</b>.<br>';
if($happyEvent > 1) $eventsMess .= '<b style="color:gold;">Р РЋРЎвЂЎР В°РЎРѓРЎвЂљРЎРЉР Вµ:</b> +<b>'.(($happyEvent-1)*100).'%</b>.<br>';
if($energEvent > 1) $eventsMess .= '<b style="color:#66ff00;">Р В­Р Р…Р ВµРЎР‚Р С–Р С‘РЎРЏ:</b> +<b>'.(($energEvent-1)*100).'%</b>.<br>';
if($eventsMess) $eventsMesages = $eventsMess; 
/** Р Р€РЎРѓР С‘Р В»Р ВµР Р…Р С‘РЎРЏ *Р С™Р С•Р Р…Р ВµРЎвЂ * **/                     


$ms = false;
$log_selekt = select('SELECT raund,demage FROM battle_log WHERE battle_id=%d ORDER BY id DESC',$pve_id);    
foreach($log_selekt as $log_go){
  $ms .=  '<table style=" padding: 5px; margin: 20px;"><tr>';
  $ms .=  '<td><font color="gold" size = "6"><b>'.$log_go['raund'].'</b></font><td>';
  $ms .=  '<td>'.$log_go['demage'].'<td>';
  $ms .=  '</tr></table><hr>';
}

$pogoda_name = first('SELECT name_pogod FROM pogoda WHERE id_pog=%d',$battlPve['id_pogodi']); 
if(isset($pogoda_name['name_pogod'])) $pogoda_battle = "<font color='DarkGreen'><b> $pogoda_name[name_pogod].</b></font>";
  else $pogoda_battle = "<font color='DarkGreen'><b> Р С›Р В±РЎвЂ№РЎвЂЎР Р…Р В°РЎРЏ.</b></font>";

$pokem_one = $battlPve['poke_1'];
$pokem_two = $battlPve['poke_2'];
$at  = $battlPve['attac_1'];
$at2 = $battlPve['attac_2'];


if(poke_info($pokem_two,'hp_my') <= 0 && !empty($pve_id)){
      $myLvl      =  poke_info($pokem_one,'lvl');
      $noLvl      =  poke_info($pokem_two,'lvl');
      if($noLvl > 100) $noLvl = 100;
      $myOpit     =  poke_info($pokem_one,'exp');
      $myHappy    =  poke_info($pokem_one,'happy');
      $questPoke  =  poke_info($pokem_two,'reproduction');
      $pokesDrop  =  poke_info($pokem_two,'startepoke');
      $tipPoke    =  poke_info($pokem_two,'basenum');
      $enerone    = 0;
      $myPokemonUpRes = obrid($pokem_one);   
      
      if($questPoke > 0){
         $a = questPoke($questPoke,$tipPoke);
          if($a != false){
            updateQustPoke($a,$tipPoke,$pokesDrop);
          }
      }     
      if($noLvl > 25){
        $energy     =  users_conect_dop('energi_opit');
        $energyCool =  users_conect_dop('energi_cool');
         if($energy >= 100){$energyCool = $energyCool+1; $energy = 0;}
        $enerone = 0.5 * $energEvent;
        $ener    = $energy + $enerone;
        $myEnerg = ($ener>=100?100:$ener);
        update('usersunictable',array('energi_opit'=>$myEnerg, 'energi_cool'=>$energyCool),'id='.(int)$_SESSION['id']);
      }
      if($myLvl < 100) $exp = ceil(rand(round((($myLvl*$noLvl)*1.8)/7),round((($noLvl*67)*1.5)/7))*$expmyEvent*30);
        else $exp = 0;
        
      if($myHappy < 100) $happyone = (0.2*$noLvl)*$happyEvent;
        else $happyone = 0;
        
      $happy = $myHappy + $happyone; 
      $myHappy = ($happy>100?100:$happy);
	  $maney = abs(ceil(round(rand(((60*$noLvl)*1*1.5)/5,((16*($noLvl+6))*1*1.5)/4)*$maneyEvent)));
      //EVENT
	  if($tipPoke == 478) {
      $maney = abs(ceil(round(rand(((60*$noLvl)*1*1.5)/5,((16*($noLvl+6))*1*1.5)/4)*$maneyEvent)))+100;
      }
	  if($tipPoke == 248) {
	  $rnd = rand(1,5);
	  if($rnd == 1){ plus_item(1,40); }
	  if($rnd == 2){ plus_item(1,41); }
	  if($rnd == 3){ plus_item(1,42); }
	  if($rnd == 4){ plus_item(1,43); }
	  if($rnd == 5){ plus_item(1,44); }
	  }
	  if($tipPoke == 635) {
	  $rnd = rand(1,3);
	  if($rnd == 1){ plus_item(1,67); }
	  if($rnd == 2){ plus_item(1,85); }
	  if($rnd == 3){ plus_item(1,12); }
	  }
	  if($tipPoke == 571) {
	  $rnd = rand(1,2);
	  if($rnd == 1){ plus_item(1,17); }
	  if($rnd == 2){ plus_item(1,45); }
	  }	  
	  if($tipPoke == 487) {
	  $days  = rand(8,14);
      $dtime = time() +(60*60*24*$days);
	  $tip_egg = "normal";
	  $rnd = rand(1,3);
	  if($rnd == 1){
	  insert('eggs',array(
                 'base_id_egg'=>1,
                 'hp_iv'=>30,
                 'atk_iv'=>30,
                 'def_iv'=>30,
                 'sdef_iv'=>30,
                 'satk_iv'=>30,
                 'speed_iv'=>30,
                 'users_egg'=>$_SESSION['id'],
                 'dtime'=>$dtime,
                 'tips'=>$tip_egg)); 
	  }
	  if($rnd == 2){ 
	  insert('eggs',array(
                 'base_id_egg'=>228,
                 'hp_iv'=>30,
                 'atk_iv'=>30,
                 'def_iv'=>30,
                 'sdef_iv'=>30,
                 'satk_iv'=>30,
                 'speed_iv'=>30,
                 'users_egg'=>$_SESSION['id'],
                 'dtime'=>$dtime,
                 'tips'=>$tip_egg)); 	  
	  }
	  if($rnd == 3){
	  insert('eggs',array(
                 'base_id_egg'=>592,
                 'hp_iv'=>30,
                 'atk_iv'=>30,
                 'def_iv'=>30,
                 'sdef_iv'=>30,
                 'satk_iv'=>30,
                 'speed_iv'=>30,
                 'users_egg'=>$_SESSION['id'],
                 'dtime'=>$dtime,
                 'tips'=>$tip_egg)); 
	  }
	  }	  	  
	  if($tipPoke == 94) {
	  plus_item(10,10);
	  }	  
	  if($tipPoke == 15) {
	  $q_4  = first('SELECT process FROM quest WHERE user_id=%d AND quest_id=4',$_SESSION['id']);
	  if($q_4['process'] == 10){
	  $rnd = rand(1,4);
      if($rnd == 1){	  
	  plus_item(1,4);
	  }
	  }	  
	  }
	  $myOpit  = $myOpit + $exp;
      
      update('pok_user',array('exp'=>$myOpit, 'happy'=>$myHappy),'id='.(int)$myPokemonUpRes['id']);
      
      levels_go($myPokemonUpRes['id']);
      evolution_pokes($myPokemonUpRes['id']);      
      plus_item($maney,1);
	  
	  
          
      $dropMess = false;
      $dropes   = select('SELECT t1.id,t1.item_id,t1.quest_id,t1.processq,t1.qgotov,FLOOR(value/t1.chance) as nums,CEIL(RAND()*t1.cools)  as cool
                       FROM (SELECT d.id,d.item_id,d.chance,d.nums,d.cools,d.quest_id,d.processq,d.qgotov,CEIL(RAND()*d.chance*d.nums/'.$dropsEvent.') as value
                             FROM items_drop d
                             WHERE (d.buildings_pokemons='.$pokesDrop.' OR (d.top_poke='.$tipPoke.' AND d.top_poke != 0 AND d.buildings_pokemons = 0)) OR d.ball=1
                            ) t1 
                       WHERE t1.value<=t1.nums');
      if($dropes){          
        foreach($dropes  as $poke_drop){ 
          $id      = $poke_drop['item_id'];
          $nums    = $poke_drop['cool'];
          $q       = true;
          if($poke_drop['quest_id'] > 0) $q = questDro($poke_drop['quest_id'],$poke_drop['processq'],$poke_drop['qgotov']); else $q = true;
          if($nums > 0 && $q == true){
            $names = infoItems($id,'name');
            $dropMess .= ', '.$names.'  x'.$nums; 
            plus_item($nums,$id);
            $arrDrop = array('itemid'=>$id, 'cool'=>$nums);
            logGames('drop',$arrDrop);
          }
        }
      } 
	  //EVENT
if($tipPoke == 478) {
update('quest',array('process'=>3),'user_id='.(int)$_SESSION['id'],'quest_id=666');
}	  
if($tipPoke == 94) {
update('quest',array('process'=>5),'user_id='.(int)$_SESSION['id'],'quest_id=666');
}
if($tipPoke == 248) {
update('quest',array('process'=>7),'user_id='.(int)$_SESSION['id'],'quest_id=666');
}
if($tipPoke == 635) {
update('quest',array('process'=>9),'user_id='.(int)$_SESSION['id'],'quest_id=666');
}
if($tipPoke == 571) {
update('quest',array('process'=>11),'user_id='.(int)$_SESSION['id'],'quest_id=666');
}
if($tipPoke == 487) {
update('quest',array('process'=>13),'user_id='.(int)$_SESSION['id'],'quest_id=666');
}
}

             
$color_stst = "red";         
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
$happy    = poke_info($pokem_one,'happy');                                                                                
if($expPolos>100) $expPolos = 100; if($expPolos <= 0) $expPolos = 0;
$ind = 1; $invs = 2; 
$hp_ind = poke_info($pokem_one,'hp_my');
$hp_invs = poke_info($pokem_two,'hp_my');
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<HEAD>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; Charset=Windows-1251">
<LINK REL = "Stylesheet" HREF = "css/battle.css" TYPE = "text/css">
<script>
function eventhtml(){
  if(document.getElementById('events').style.display == 'none')
    document.getElementById('events').style.display = 'block';
    else document.getElementById('events').style.display = 'none';
}
</script>
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
<body>
<div id="MovesBlock">
<?php
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
$oncOne = 'onclick="location.href=\'/game.php?go=fight_pve&atk=1\';"';
$oncTwo = 'onclick="location.href=\'/game.php?go=fight_pve&atk=2\';"';
$oncThr = 'onclick="location.href=\'/game.php?go=fight_pve&atk=3\';"';
$oncFou = 'onclick="location.href=\'/game.php?go=fight_pve&atk=4\';"';
if(!atac_pp_isset(1,$pokeAt) && $one)  { $one  = '<s>'.$one.'</s>';  $pp1 = '<s>'.$pp1.'</s>'; $oncOne = "";}
if(!atac_pp_isset(2,$pokeAt) && $two)  { $two  = '<s>'.$two.'</s>';  $pp2 = '<s>'.$pp2.'</s>'; $oncTwo = "";}
if(!atac_pp_isset(3,$pokeAt) && $thre) { $thre = '<s>'.$thre.'</s>'; $pp3 = '<s>'.$pp3.'</s>'; $oncThr = "";}
if(!atac_pp_isset(4,$pokeAt) && $four) { $four = '<s>'.$four.'</s>'; $pp4 = '<s>'.$pp4.'</s>'; $oncFou = "";}
?>
<td width="100%" align="left" valign="top">
<center>
<?php
if($_SESSION['klicers'] < 25){
if($hp_my > 0){
  if($hp_no > 0){
    if($at == 0){
    ?>
                     
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
            </div><br>
          <?php
          }
          ?>
          </td>
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
            </div><br>
          <?php
           }
          ?>
          </td>
        </tr>
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
            </div><br>
          <?php
           }
          ?>
          </td>
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
          </div><br>
        <?php
        }
        ?>
        </td>
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
              $my_pokes_yes    .= "</select><input type='hidden' name='rows' value='2'> "; 
              $my_pokes_yes    .= "<input type='submit' name='submit' value='Р вЂ”Р В°Р СР ВµР Р…Р С‘РЎвЂљРЎРЉ'> </form></td>";
          }
          if($result_item_use){
              $my_pokes_yes    .= "<td>";
              $my_pokes_yes    .= "<form action='' method='POST'>";
              $my_pokes_yes    .= "<select size='1' name='itemgo'>";
            foreach($result_item_use as $use_item_id){
              $my_pokes_yes    .= "<option value='".$use_item_id['id']."'>".$use_item_id['name']." x".$use_item_id['count']."</option>";
            }
              $my_pokes_yes    .= "</select> ";
              $my_pokes_yes    .= "<input type='submit' name='submit' value='Р ВРЎРѓР С—Р С•Р В»РЎРЉР В·Р С•Р Р†Р В°РЎвЂљРЎРЉ'> </form></td>";    
          }
      echo "<tr>$my_pokes_yes</tr>";
      ?>
        <tr>
          <td colspan=2 align="center">
            <INPUT type="button" title="Р РЋР Т‘Р В°РЎвЂљРЎРЉРЎРѓРЎРЏ" value="Р РЋР Т‘Р В°РЎвЂљРЎРЉРЎРѓРЎРЏ" onclick="window.location.href='/game.php?go=fight_pve&udar=sur';" style="width:120px;" >
          </td>
        </tr>
      </table>
    <?php
    }else{
      $tt = false;
      echo 
      '
      <br><br><br>
      <font style="font-size: 19px;">Р С›Р В¶Р С‘Р Т‘Р В°Р ВµР С Р С•РЎвЂљР Р†Р ВµРЎвЂљ РЎРѓР С•Р С—Р ВµРЎР‚Р Р…Р С‘Р С”Р В°...</font>
      <br><br>
      <DIV ID=timer style=\'text-align:center; width:20%; font-weight:bold; font-size:24px;\'>'.
      $tt
      .'</DIV>
      <br>
      <INPUT type="button" title="Р РЋР Т‘Р В°РЎвЂљРЎРЉРЎРѓРЎРЏ" value="Р РЋР Т‘Р В°РЎвЂљРЎРЉРЎРѓРЎРЏ" onclick="window.location.href=\'/game.php?go=fight_pve&udar=sur\';" style="width:120px;" >
      <br>
      ';
    }
  }else{
    /** Р вЂќРЎР‚Р С•Р С—, Р С•Р С—РЎвЂ№РЎвЂљ, Р С”Р Р†Р ВµРЎРѓРЎвЂљРЎвЂ№, РЎРѓРЎвЂЎР В°РЎРѓРЎвЂљРЎРЉР Вµ **/
    if($hp_my > 0 && $pve_id > 0){
    print '<table width="60%"><tr><td><td><div id="atc" style="padding:10px;width:100%;text-align:left;font-weight:bold;">
          Р вЂ™РЎвЂ№Р В±Р С‘РЎвЂљР С•: Р СљР С•Р Р…Р ВµРЎвЂљ: x'.$maney.$dropMess.'.
          <br>
          Р СџР С•Р В»РЎС“РЎвЂЎР ВµР Р…Р С•: Р С›Р С—РЎвЂ№РЎвЂљР В°: x'.$exp.', Р В­Р Р…Р ВµРЎР‚Р С–Р С‘Р С‘: x'.$enerone.', Р РЋРЎвЂЎР В°РЎРѓРЎвЂљРЎРЉРЎРЏ: x'.$happyone.'.
          </div></tr></table>';
    print '<table width="61%"><tr><td><td><div id="atc" onClick="window.location.href=\'/game.php?go=char\';" style="padding:10px;width:100%;text-align:left;font-size:20px;font-weight:bold;">
          <- Р Р€Р в„–РЎвЂљР С‘
          </div></tr></table>';
   }
    /** Р вЂќРЎР‚Р С•Р С—, Р С•Р С—РЎвЂ№РЎвЂљ, Р С”Р Р†Р ВµРЎРѓРЎвЂљРЎвЂ№, РЎРѓРЎвЂЎР В°РЎРѓРЎвЂљРЎРЉР Вµ  *Р С™Р С›Р СњР вЂўР В¦*  **/
 }
}else{
$my_pokes_yes = "";
    if($hp_one_user_poke){
      $my_pokes_yes .= "<br><br><b>Р вЂ™РЎвЂ№Р В±Р ВµРЎР‚Р С‘РЎвЂљР Вµ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В°:</b><br>";
      $my_pokes_yes .= "<form action='' method='POST'>";
      $my_pokes_yes    .= "<select size='1' name='smena'>";
    foreach($hp_one_user_poke as $smena_pok_id){
          $my_pokes_yes    .= "<option value='".$smena_pok_id['id']."'>".$smena_pok_id['names']."</option>";
    }  
      $my_pokes_yes    .= "</select><input type='hidden' name='rows' value='1'>"; 
      $my_pokes_yes    .= "<input type='submit' name='submit' value='Р РЋР СР ВµР Р…Р С‘РЎвЂљРЎРЉ'> </form><br>";
      $out =  '<INPUT type="button" title="Р РЋР Т‘Р В°РЎвЂљРЎРЉРЎРѓРЎРЏ" value="Р РЋР Т‘Р В°РЎвЂљРЎРЉРЎРѓРЎРЏ" onclick="location.href=\'/game.php?go=fight_pve&udar=sur\';" style="width:120px;" >';
    }else{
      $my_pokes_yes = "<br><h2>Р СџР С•РЎР‚Р В°Р В¶Р ВµР Р…Р С‘Р Вµ!</h2>";
      $out = '<INPUT type="button" title="Р Р€Р в„–РЎвЂљР С‘" value="Р Р€Р в„–РЎвЂљР С‘" onclick="window.location.href=\'/game.php?go=char\';" style="width:120px;" >'; 
   $tipPoke    =  poke_info($pokem_two,'basenum');
   if($tipPoke == 478 OR $tipPoke == 94 OR $tipPoke == 248 OR $tipPoke == 645 OR $tipPoke == 571 OR $tipPoke == 487) {
	$onlTime = time() + 30*60;
	query('UPDATE users SET timepoke=%d WHERE id=%d',$onlTime,$_SESSION['id']);
	}
   }
    echo 
    '<br><br><br>
    <div style="background:#CFCFCF;width:60%;" >
      <font style="font-size: 19px;" color = "brown"><b>Р Р€ #'.poke_info($pokem_one,'names').' Р Р…Р Вµ Р Т‘Р С•РЎРѓРЎвЂљР В°РЎвЂљР С•РЎвЂЎР Р…Р С• HP Р Т‘Р В»РЎРЏ Р С—РЎР‚Р С•Р Т‘Р С•Р В»Р В¶Р ВµР Р…Р С‘РЎРЏ Р В±Р С•РЎРЏ.</b></font>
      '.$my_pokes_yes.'
    </div>
    <br>'.$out.'<br>';
}
}else{
    echo "<center><img src='/img_gen.php?let=".$_SESSION['klikercode']."'><br></center>";
    echo "<div id=\"atc\" style='width:350px;'>
    <center><b>
    <a href='/game.php?go=fight_pve&code_new=1' target=\"_chat_two\" style='color:#afeeee; font-size: 15px;'>Р С›Р В±Р Р…Р С•Р Р†Р С‘РЎвЂљРЎРЉ Р С”Р В°РЎР‚РЎвЂљР С‘Р Р…Р С”РЎС“</a><br>
    Р вЂ™Р Р†Р ВµР Т‘Р С‘РЎвЂљР Вµ, Р С—Р С•Р В¶Р В°Р В»РЎС“Р в„–РЎРѓРЎвЂљР В°, РЎвЂ Р С‘РЎвЂћРЎР‚РЎвЂ№ Р С‘ Р В±РЎС“Р С”Р Р†РЎвЂ№ РЎРѓ Р С”Р В°РЎР‚РЎвЂљР С‘Р Р…Р С”Р С‘:<br>
    <div id='errorCapth' style='width: 65%; font-weight:bold; font-size: 15px; color: #000; display:none; background: #ffcece; border:2px solid #000; padding:4px;'></div> 
    <form action='/game.php?go=fight_pve' id='capthcode' method='POST' target=\"_chat_two\">
    <input type='text' name='code' style='font-weight:bold; font-size: 15px; color: #000; border:2px solid #000;'>
    <input type='submit' value='Р С›Р С™' name='OK' style='font-weight:bold; font-size: 15px;  color: #000; border:2px solid #000;'>
    </form>
    </center></div>";
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
		  <div style="width:100%; text-align: right; height:14; border: 2px solid black; border-radius: 4px;"><div style='width:<?php echo $h2?>%;background:<?php echo $color2?>height:14;  border-radius: 4px;' title="Р С™Р С•Р В»Р С‘РЎвЂЎР ВµРЎРѓРЎвЂљР Р†Р С• Р В·Р Т‘Р С•РЎР‚Р С•Р Р†РЎРЉРЎРЏ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В°">
          <font style='font:11px Tahoma; color: #1C1C1C;'>
          <b><?php echo $hp_no."/".poke_info($pokem_two,'hp_max'); ?></b>
          </font>
          </div>
		  </div>
		  </center>
		  <a class="tooltip" href="#">Р Р€РЎРѓР С‘Р В»Р ВµР Р…Р С‘РЎРЏ<span class="classic"> 
		      <?php                         
    echo tittle_status($pokem_two,$battlPve['id']);
    $def_st_no_pl  = status_na_pokemone($pokem_two,$battlPve['id'],'plus','defend');
    $sdef_st_no_pl = status_na_pokemone($pokem_two,$battlPve['id'],'plus','spdefend');
    $atk_st_no_pl  = status_na_pokemone($pokem_two,$battlPve['id'],'plus','attac');
    $satk_st_no_pl = status_na_pokemone($pokem_two,$battlPve['id'],'plus','spattac');
    $acc_st_no_pl  = status_na_pokemone($pokem_two,$battlPve['id'],'plus','acc');
    $spd_st_no_pl  = status_na_pokemone($pokem_two,$battlPve['id'],'plus','speed');
    $acr_st_no_pl  = status_na_pokemone($pokem_two,$battlPve['id'],'plus','accuracy');
             if ($def_st_no_pl>0) echo "<b>Р вЂ”Р В°РЎвЂ°Р С‘РЎвЂљР В° + ".$def_st_no_pl."</b> <br>";
             if ($sdef_st_no_pl>0)echo "<b>Р РЋР С—Р ВµРЎвЂ . Р вЂ”Р В°РЎвЂ°Р С‘РЎвЂљР В° + ".$sdef_st_no_pl."</b> <br>";
             if ($atk_st_no_pl>0) echo "<b>Р С’РЎвЂљР В°Р С”Р В° + ".$atk_st_no_pl."</b><br> ";
             if ($satk_st_no_pl>0)echo "<b>Р РЋР С—Р ВµРЎвЂ . Р С’РЎвЂљР В°Р С”Р В° + ".$satk_st_no_pl."</b><br> ";
             if ($acc_st_no_pl>0) echo "<b>Р вЂєР С•Р Р†Р С”Р С•РЎРѓРЎвЂљРЎРЉ + ".$acc_st_no_pl."</b><br> ";
             if ($spd_st_no_pl>0) echo "<b>Р РЋР С”Р С•РЎР‚Р С•РЎРѓРЎвЂљРЎРЉ + ".$spd_st_no_pl."</b> <br>";
             if ($acr_st_no_pl>0) echo "<b>Р СћР С•РЎвЂЎР Р…Р С•РЎРѓРЎвЂљРЎРЉ + ".$acr_st_no_pl."</b> <br>";
    $def_st_no_min  = status_na_pokemone($pokem_two,$battlPve['id'],'minus','defend');
    $sdef_st_no_min = status_na_pokemone($pokem_two,$battlPve['id'],'minus','spdefend');
    $atk_st_no_min  = status_na_pokemone($pokem_two,$battlPve['id'],'minus','attac');
    $satk_st_no_min = status_na_pokemone($pokem_two,$battlPve['id'],'minus','spattac');
    $acc_st_no_min  = status_na_pokemone($pokem_two,$battlPve['id'],'minus','acc');
    $spd_st_no_min  = status_na_pokemone($pokem_two,$battlPve['id'],'minus','speed');
    $acr_st_no_min  = status_na_pokemone($pokem_two,$battlPve['id'],'minus','accuracy');         
             if ($def_st_no_min>0) echo "<font color='".$color_stst."'><b>Р вЂ”Р В°РЎвЂ°Р С‘РЎвЂљР В° - ".$def_st_no_min."</b></font><br> ";
             if ($sdef_st_no_min>0)echo "<font color='".$color_stst."'><b>Р РЋР С—Р ВµРЎвЂ . Р вЂ”Р В°РЎвЂ°Р С‘РЎвЂљР В° - ".$sdef_st_no_min."</b></font><br> ";
             if ($atk_st_no_min>0) echo "<font color='".$color_stst."'><b>Р С’РЎвЂљР В°Р С”Р В° - ".$atk_st_no_min."</b></font><br> ";
             if ($satk_st_no_min>0)echo "<font color='".$color_stst."'><b>Р РЋР С—Р ВµРЎвЂ . Р С’РЎвЂљР В°Р С”Р В° - ".$satk_st_no_min."</b></font> <br>";
             if ($acc_st_no_min>0) echo "<font color='".$color_stst."'><b>Р вЂєР С•Р Р†Р С”Р С•РЎРѓРЎвЂљРЎРЉ - ".$acc_st_no_min."</b></font><br> ";
             if ($spd_st_no_min>0) echo "<font color='".$color_stst."'><b>Р РЋР С”Р С•РЎР‚Р С•РЎРѓРЎвЂљРЎРЉ - ".$spd_st_no_min."</b></font><br> ";
             if ($acr_st_no_min>0) echo "<font color='".$color_stst."'><b>Р СћР С•РЎвЂЎР Р…Р С•РЎРѓРЎвЂљРЎРЉ - ".$acr_st_no_min."</b></font> <br>";
    $img2 = "/pok/".poke_info($pokem_two,'tips')."/".poke_info($pokem_two,'basenum').".jpg";
    ?>  
</span></a>
</div>
<div id="pokemon1">
<b>
<?php
  $poke_name_str = poke_info($pokem_one, 'names');
  $isShiny = (strpos($poke_name_str, 'pokesShiny') !== false);
  $poke_sprite_path = $isShiny ? 'pok/sback/' : 'pok/back/';
  $poke_sprite_ext = 'gif';
?>
<div id="pkmnInf">
  <a href=javascript: onClick=win1=window.open('game.php?go=pokedex&id=<?php echo poke_info($pokem_one,'basenum');?>','pokedex','width=550,height=550,scrollbars=yes');return true;>
    <img src=img/other/pokedex.png>
  </a>
  <?php echo "#" . $poke_name_str; ?> <?php echo poke_info($pokem_one, 'lvl'); ?> - lvl
</div>
</b> <br>

<center>
  <img id="pMy" src="<?= $poke_sprite_path . NumPok($p_one_im) . '.' . $poke_sprite_ext ?>">
  <div id="moves_one"></div>
  <div style="width:100%; text-align: left; height:14; border: 2px solid black; border-radius: 4px;">
    <div style='width:<?php echo $h1 ?>%;background:<?php echo $color1 ?>height:14;  border-radius: 4px;' title="Р С™Р С•Р В»Р С‘РЎвЂЎР ВµРЎРѓРЎвЂљР Р†Р С• Р В·Р Т‘Р С•РЎР‚Р С•Р Р†РЎРЉРЎРЏ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В°">
      <font style='font:11px Tahoma; color: #1C1C1C;'>
        <b><?php echo $hp_my . "/" . poke_info($pokem_one, 'hp_max'); ?></b>
      </font>
    </div>
  </div>
  <?php print '<DIV align="left" style="width:' . $expPolos . '%;background:#4169e1;height:14px;font-size:9;color:Black;" title="Р С™Р С•Р В»Р С‘РЎвЂЎР ВµРЎРѓРЎвЂљР Р†Р С• Р С•Р С—РЎвЂ№РЎвЂљР В° Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В°"><b>' . round($expPolos) . '%</b></DIV>'; ?>
</center>
<a class="tooltip" href="#">Р Р€РЎРѓР С‘Р В»Р ВµР Р…Р С‘РЎРЏ<span class="classic">
<?php


echo tittle_status($pokem_one,$pve_id);
$def_st_my_pl  = status_na_pokemone($pokem_one,$pve_id,'plus','defend');
$sdef_st_my_pl = status_na_pokemone($pokem_one,$pve_id,'plus','spdefend');
$atk_st_my_pl  = status_na_pokemone($pokem_one,$pve_id,'plus','attac');
$satk_st_my_pl = status_na_pokemone($pokem_one,$pve_id,'plus','spattac');
$acc_st_my_pl  = status_na_pokemone($pokem_one,$pve_id,'plus','acc');
$spd_st_my_pl  = status_na_pokemone($pokem_one,$pve_id,'plus','speed');
$acr_st_my_pl  = status_na_pokemone($pokem_one,$pve_id,'plus','accuracy');
         if ($def_st_my_pl >0) echo "<b>Р вЂ”Р В°РЎвЂ°Р С‘РЎвЂљР В° + ".$def_st_my_pl."</b><br> ";
         if ($sdef_st_my_pl>0) echo "<b>Р РЋР С—Р ВµРЎвЂ . Р вЂ”Р В°РЎвЂ°Р С‘РЎвЂљР В° + ".$sdef_st_my_pl."</b><br> ";
         if ($atk_st_my_pl >0) echo "<b>Р С’РЎвЂљР В°Р С”Р В° + ".$atk_st_my_pl."</b><br> ";
         if ($satk_st_my_pl>0) echo "<b>Р РЋР С—Р ВµРЎвЂ . Р С’РЎвЂљР В°Р С”Р В° + ".$satk_st_my_pl."</b><br> ";
         if ($acc_st_my_pl >0) echo "<b>Р вЂєР С•Р Р†Р С”Р С•РЎРѓРЎвЂљРЎРЉ + ".$acc_st_my_pl."</b><br> ";
         if ($spd_st_my_pl >0) echo "<b>Р РЋР С”Р С•РЎР‚Р С•РЎРѓРЎвЂљРЎРЉ + ".$spd_st_my_pl."</b><br> ";
         if ($acr_st_my_pl >0) echo "<b>Р СћР С•РЎвЂЎР Р…Р С•РЎРѓРЎвЂљРЎРЉ + ".$acr_st_my_pl."</b> <br>";
$def_st_my_min  = status_na_pokemone($pokem_one,$pve_id,'minus','defend');
$sdef_st_my_min = status_na_pokemone($pokem_one,$pve_id,'minus','spdefend');
$atk_st_my_min  = status_na_pokemone($pokem_one,$pve_id,'minus','attac');
$satk_st_my_min = status_na_pokemone($pokem_one,$pve_id,'minus','spattac');
$acc_st_my_min  = status_na_pokemone($pokem_one,$pve_id,'minus','acc');
$spd_st_my_min  = status_na_pokemone($pokem_one,$pve_id,'minus','speed');
$acr_st_my_min  = status_na_pokemone($pokem_one,$pve_id,'minus','accuracy');         
         if ($def_st_my_min>0) echo "<font color='".$color_stst."'><b>Р вЂ”Р В°РЎвЂ°Р С‘РЎвЂљР В° - ".$def_st_my_min."</b></font> <br>";
         if ($sdef_st_my_min>0)echo "<font color='".$color_stst."'><b>Р РЋР С—Р ВµРЎвЂ . Р вЂ”Р В°РЎвЂ°Р С‘РЎвЂљР В° - ".$sdef_st_my_min."</b></font> <br>";
         if ($atk_st_my_min>0) echo "<font color='".$color_stst."'><b>Р С’РЎвЂљР В°Р С”Р В° - ".$atk_st_my_min."</b></font><br> ";
         if ($satk_st_my_min>0)echo "<font color='".$color_stst."'><b>Р РЋР С—Р ВµРЎвЂ . Р С’РЎвЂљР В°Р С”Р В° - ".$satk_st_my_min."</b></font><br> ";
         if ($acc_st_my_min>0)echo  "<font color='".$color_stst."'><b>Р вЂєР С•Р Р†Р С”Р С•РЎРѓРЎвЂљРЎРЉ - ".$acc_st_my_min."</b></font><br> ";
         if ($spd_st_my_min>0)echo  "<font color='".$color_stst."'><b>Р РЋР С”Р С•РЎР‚Р С•РЎРѓРЎвЂљРЎРЉ - ".$spd_st_my_min."</b></font><br> ";
         if ($acr_st_my_min>0)echo  "<font color='".$color_stst."'><b>Р СћР С•РЎвЂЎР Р…Р С•РЎРѓРЎвЂљРЎРЉ - ".$acr_st_my_min."</b></font> <br>";
$img1 = "/pok/".poke_info($pokem_one,'tips')."/".poke_info($pokem_one,'basenum').".jpg";
?></span></a>
		 
		  </div>
  
</div>
  <div id="logPvp">
<center>
    <b><font color = "#FFFFFF" size="2">Р В Р В°РЎС“Р Р…Р Т‘: <?php echo $battlPve['raund'];?></font></b><br>
    <b><font color = "#FFFFFF">Р СџР С•Р С–Р С•Р Т‘Р В°:</font> <?php echo $pogoda_battle; ?></b>
  </center>
  <hr>
  <?echo $ms;?> 
 
   </div>
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
<?php
if($hp_no <= 0){
  $a = time()+30;
  query('UPDATE users SET pve=0, rang_a=rang_a+2, atack_poke=%d WHERE id=%d',$a,$_SESSION['id']);
  
  if($myrow['rang_b'] < 125) query('UPDATE users SET pve=0, rang_a=rang_a+2, rang_b=rang_b+1, atack_poke=%d WHERE id=%d',$a,$_SESSION['id']);
      else
        query('UPDATE users SET pve=0, rang_a=rang_a+2, atack_poke=%d WHERE id=%d',$a,$_SESSION['id']); 
  $_SESSION['klicers'] = $_SESSION['klicers']+1;
  logdel($pve_id);
}
if($count_user_poke['countpok'] <= 0){
    $a = time()+30;
    if($myrow['rang_b'] > -125) query('UPDATE users SET pve=0, rang_a=rang_a+1, rang_b=rang_b-1, atack_poke=%d WHERE id=%d',$a,$_SESSION['id']);
      else
        query('UPDATE users SET pve=0, atack_poke=%d WHERE id=%d',$a,$_SESSION['id']); 
    $_SESSION['klicers'] = $_SESSION['klicers']+1;
    logdel($pve_id);
}
?>