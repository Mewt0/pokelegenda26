<?php
require_once('include/function/int.arhi.php');
// Проверки
  if($myrow['buildmy'] == 44){
    if(!provitems(69,1)){
       update('users',array('buildmy'=>18),'id='.(int)$_SESSION['id']);
    }
  }
$ivent_shiny = 1;
$dateServer = date('Y-m-d H:i:s');
$timeServer = date('H:i:s');
function users_zayv($id){
  $a = first('SELECT id_z FROM pvp_zayv WHERE user_1=%d AND user_2=%d',$id,$_SESSION['id']);
  if(!empty($a['id_z'])) $ret = 1;
  else $ret = 2;
 return $ret;
}
$buildmy  = $myrow['buildmy'];
$build    = first('SELECT title,pve,tipe FROM build WHERE id=%d',$buildmy);
$myLocPokemon =  $buildmy;
/** Function ivents **/

if($build['pve'] > 0 && $myrow['atack_poke'] <= time() && $myrow['pvp'] == 0 && $myrow['trade'] == 0 && $myrow['pve'] == 0 && $myrow['pve_button'] == 1){
 if(provitems(65,1)){ 
  $random_a_shine =  round(550000/$ivent_shiny);     
  $random_b_shine =  10;
  $fleit_nor = true;
 }else{
  $random_a_shine =  round(1000000/$ivent_shiny);     
  $random_b_shine =  10;
  $fleit_nor = false; 
 }
 
/* if($timeServer > '12:00:00' && $timeServer < '15:00:00'){
    $event01 = rand(1,10000);
    if($event01 < 5){
      $myLocPokemon = 2000000001;
    }
    if($event01 < 20 && $event01 > 5){
      $myLocPokemon = 2000000002;
    }
 }elseif($timeServer > '16:00:00' && $timeServer < '19:00:00'){
    $event01 = rand(1,10000);
    if($event01 < 5){
      $myLocPokemon = 2000000001;
    }
    if($event01 < 20 && $event01 > 5){
      $myLocPokemon = 2000000003;
    }
 }elseif($timeServer > '20:00:00' && $timeServer < '23:00:00'){
    $event01 = rand(1,10000);
    if($event01 < 5){
      $myLocPokemon = 2000000001;
    }
    if($event01 < 20 && $event01 > 5){
      $myLocPokemon = 2000000004;
    } 
 } */
}
// Удочки...
if($build['tipe'] > 0){
 //Старая удочка
  if(provitems(16,1)){
      $randOldRod = rand(1,10000);
      if($randOldRod == 555){
              $myLocPokemon = $buildmy;
              if($build['tipe'] == 1) $myLocPokemon = 2000000000;
              if($build['tipe'] == 1 && $buildmy == 20000) $myLocPokemon = 2000000001;
      }
  }
}

if($myLocPokemon >= 2000000000){
  $t = "На: ".$_SESSION['login']."(".$_SESSION['id']."), удачно сработал предмет и его перекинуло на локацию с покемонами: ".$myLocPokemon;        
  writeLoges($t,'df','pokeLoc');
}
/** Function ivents END **/



$usersLoc = select('SELECT id,login,groups FROM users WHERE buildmy=%d AND online=1 ORDER BY groups ASC, login ASC',$buildmy);
$countmes = first('SELECT COUNT(*) as countmes FROM sends WHERE users=%d AND active=1',$_SESSION['id']);
if($countmes['countmes'] > 0) $countmes = $countmes['countmes']; else $countmes = 'false';

$pokem_count  = first('SELECT COUNT(DISTINCT basenum) as count FROM pok_user WHERE users=%d AND tips="normal"',$_SESSION['id']);
 if($pokem_count['count'] != $myrow['count_poke'])   update('users',array('count_poke'=>$pokem_count['count']),'id='.(int)$_SESSION['id']); 
$pokem_count_s = first('SELECT COUNT(DISTINCT basenum) as count FROM pok_user WHERE users=%d AND tips="shine"',$_SESSION['id']);
 if($pokem_count['count'] != $myrow['count_poke_s']) update('users',array('count_poke_s'=>$pokem_count_s['count']),'id='.(int)$_SESSION['id']);

$pokemHp = first('SELECT COUNT(*) as countpok FROM pok_user WHERE users=%d AND active=1 AND hp_my>0',$_SESSION['id']); 




print '<script type="text/javascript">';
if($myrow['trade']>0){
   echo "parent._chat_two.location.href='/game.php?go=char&trade=true&tradeid=".$myrow['trade']."&refresh=true';";
}
if(!empty($_SESSION['tradeUsers']) && $myrow['trade'] <= 0){
   unset($_SESSION['tradeUsers']);
   echo "parent.loc('char');";
}
if($myrow['pvp'] > 0){
 function pokes_go_zap($id,$zap){
    $id = substr($id, 4);
    $q = first('SELECT '.$zap.' FROM pok_user WHERE id=%d',$id);
    $a = $q[$zap];
  return $a;
 }
 $a = first('SELECT id,user_1,user_2,attac_1,attac_2,poke_1,poke_2,hod_user_id,pobeda FROM battles WHERE id=%d',$myrow['battleid']);
  if(!empty($a['id'])){
    if($a['user_1'] == $_SESSION['id']){
      if(($a['attac_1'] == 0) AND ($a['attac_2'] != 0)) echo "parent.loc('fight_pvp');";
      if(pokes_go_zap($a['poke_2'],'hp_my')  <= 0 && $battle_pvp['atack_1'] == 0 &&  $a['hod_user_id'] == 3) echo "parent.loc('fight_pvp');";
    }
    if($a['user_2'] == $_SESSION['id']){
      if(($a['attac_2'] == 0) AND ($a['attac_1'] != 0)) echo "parent.loc('fight_pvp');";
      if(pokes_go_zap($a['poke_1'],'hp_my')  <= 0 && $battle_pvp['atack_2'] == 0 &&  $a['hod_user_id'] == 3) echo "parent.loc('fight_pvp');";
    }
    //if($a['poke_1'] == false OR $a['poke_2'] == false) echo "parent.loc('fight_pvp');";
    if($a['hod_user_id'] == 1 OR  $a['hod_user_id'] == 2) echo "parent.loc('fight_pvp');";
    if($a['pobeda'] > 0) echo "parent.loc('fight_pvp');";
  } 
}
$qu_proc = first('SELECT process FROM quest WHERE user_id=%d AND quest_id=666',$_SESSION['id']);     
$my_time = first('SELECT timepoke FROM users WHERE id=%d',$_SESSION['id']); 
$event = 0;
   
if($pokemHp['countpok'] > 0 && $build['pve'] > 0 && empty($_SESSION['QUEST_MY_ISSET'])){
   $timePoke = date('H:i:s');
   if(($myrow['atack_poke'] <= time()) && $myrow['pvp'] == 0 && $myrow['trade'] == 0 && $myrow['pve'] == 0 && $myrow['pve_button'] == 1){
   // EVENT


   if($buildmy == 14 and $qu_proc['process'] == 2) { $pkmid = 478; $lvlpkmn = 200; $event = 1;}
   if($buildmy == 8 and $qu_proc['process'] == 4) { $pkmid = 94; $lvlpkmn = 300; $event = 1;}
   if($buildmy == 13 and $qu_proc['process'] == 6) { $pkmid = 248; $lvlpkmn = 400; $event = 1;}
   if($buildmy == 7 and $qu_proc['process'] == 8) { $pkmid = 635; $lvlpkmn = 500; $event = 1;}
   if($buildmy == 80 and $qu_proc['process'] == 10) { $pkmid = 571; $lvlpkmn = 600; $event = 1;}
   if($buildmy == 17 and $qu_proc['process'] == 12) { $pkmid = 487; $lvlpkmn = 700; $event = 1;}
   if($event == 1 and $my_time['timepoke'] < time()) {
       $idNums  = 1;
       $questupdate = $pokDik['questupdate'];
       if($questupdate <= 0) $lvlPok  = $lvlpkmn;
       if($pokDik['sprz']) $razvandsparka = $pokDik['sprz']; else  $razvandsparka = '0';
       $baseNum =  $pkmid;
       $iv = rand(15,35);  $iv2 = rand(2,31);  $iv3 = rand(11,25);
       $ev = rand(30,100); $ev2  = rand(1,150);$ev3  = rand(50,80);
       $sex = ''.mt_rand(1,2);
       $hp = pokeStatZap(1,$lvlPok,$iv ,$ev3,'hp'   ,$baseNum);
       $at = pokeStatZap(1,$lvlPok,$iv2,$ev ,'atk'  ,$baseNum);
       $df = pokeStatZap(1,$lvlPok,$iv ,$ev3,'def'  ,$baseNum);
       $sa = pokeStatZap(1,$lvlPok,$iv2,$ev ,'satk' ,$baseNum);
       $sd = pokeStatZap(1,$lvlPok,$iv ,$ev3,'sdef' ,$baseNum);
       $sp = pokeStatZap(1,$lvlPok,$iv3,$ev2,'speed',$baseNum);
       $tipes = 'normal';
       $names = pokeStatZap(2,'title','','','',$baseNum);
       if(rand(1,$random_a_shine) <= $random_b_shine && $pokDik['poimka'] == 1){
         if(rand(1,100) <= 5 && $fleit_nor) minus_item(1,65);
         $tipes = 'shine';
         $names = '<span class="pokesShiny">'.$names.' - <b>Shiny</b></span>';
       }
         $idPok = insert('pok_pve',array(
                         'users'=>$_SESSION['id'],
                         'basenum'=>$baseNum,
                         'names'=>$names,
                         'lvl'=>$lvlPok,
                         'sex'=>$sex,
                         'hp_my'=>$hp,
                         'hp_max'=>$hp,
                         'atk'=>$at,
                         'def'=>$df,
                         'satk'=>$sa,
                         'sdef'=>$sd,
                         'speed'=>$sp,
                         'hp_ev'=>$ev3,
                         'atk_ev'=>$ev,
                         'def_ev'=>$ev3,
                         'satk_ev'=>$ev,
                         'sdef_ev'=>$ev3,
                         'speed_ev'=>$ev2,
                         'hp_iv'=>$iv,
                         'atk_iv'=>$iv2,
                         'def_iv'=>$iv,
                         'satk_iv'=>$iv2,
                         'sdef_iv'=>$iv,
                         'speed_iv'=>$iv3,
                         'tips'=>$tipes,
                         'startepoke'=>$idNums,
                         'reproduction'=>$questupdate,
                         'sprz'=>$razvandsparka,
                         'poimka'=>0));
         $pokemon_start = first('SELECT id FROM pok_user WHERE users=%d AND active=1 AND startepoke=1 AND hp_my>0',$_SESSION['id']);
         $p_users = $pokemon_start['id'];
         if(empty($pokemon_start['id'])){
            $pokemon_start = first('SELECT id, CEIL(RAND()*id) as chance FROM pok_user WHERE users=%d AND active=1 AND hp_my>0 ORDER BY chance DESC LIMIT 0,1',$_SESSION['id']);
            $p_users = $pokemon_start['id'];
         }
         $time_pve = time() + 3600;
          $battl = insert('battles',array(
                 'user_1'=>$_SESSION['id'],
                 'user_2'=>$idPok,
                 'poke_1'=>'pvp_'.$p_users,
                 'poke_2'=>'pve_'.$idPok,
                 'batl_tip'=>'pve', 
                 'times'=>$time_pve));
         update('users',array('pve'=>1, 'battleid'=>$battl),'id='.(int)$_SESSION['id']);
         print "parent.loc('fight_pve');"; 
    
   }else{
     $pokDik = first('SELECT p.id, p.baseid, p.poimka, p.questupdate, p.lvl, p.sprz, CEIL(RAND()*p.chance) as chance 
                       FROM pokebuild p 
                       LEFT JOIN quest_poke q 
                       ON q.questid=p.questupdate AND q.pokenum=p.baseid AND q.coolpokemin<q.coolpokemax AND q.userid=%d
                       WHERE p.building=%d AND "'.$timePoke.'" BETWEEN p.timeone AND p.timetwo  
                       AND ((p.questupdate=0)OR(q.coolpokemin<q.coolpokemax AND NOT ISNULL(q.id))) 
                       ORDER BY chance DESC LIMIT 0,1',$_SESSION['id'],$myLocPokemon);
    if(!empty($pokDik['id'])){
       $idNums  = $pokDik['id'];
       $questupdate = $pokDik['questupdate'];
       if($questupdate <= 0) $lvlPok  = rand($pokDik['lvl'],$pokDik['lvl']+4); else $lvlPok = $pokDik['lvl'];
       if($pokDik['sprz']) $razvandsparka = $pokDik['sprz']; else  $razvandsparka = '0';
       $baseNum =  $pokDik['baseid'];
       $iv = rand(15,35);  $iv2 = rand(2,31);  $iv3 = rand(11,25);
       $ev = rand(30,100); $ev2  = rand(1,150);$ev3  = rand(50,80);
       $sex = ''.mt_rand(1,2);
       $hp = pokeStatZap(1,$lvlPok,$iv ,$ev3,'hp'   ,$baseNum);
       $at = pokeStatZap(1,$lvlPok,$iv2,$ev ,'atk'  ,$baseNum);
       $df = pokeStatZap(1,$lvlPok,$iv ,$ev3,'def'  ,$baseNum);
       $sa = pokeStatZap(1,$lvlPok,$iv2,$ev ,'satk' ,$baseNum);
       $sd = pokeStatZap(1,$lvlPok,$iv ,$ev3,'sdef' ,$baseNum);
       $sp = pokeStatZap(1,$lvlPok,$iv3,$ev2,'speed',$baseNum);
       $tipes = 'normal';
       $names = pokeStatZap(2,'title','','','',$baseNum);
       if(rand(1,$random_a_shine) <= $random_b_shine && $pokDik['poimka'] == 1){
         if(rand(1,100) <= 5 && $fleit_nor) minus_item(1,65);
         $tipes = 'shine';
         $names = '<span class="pokesShiny">'.$names.' - <b>Shiny</b></span>';
       }
         $idPok = insert('pok_pve',array(
                         'users'=>$_SESSION['id'],
                         'basenum'=>$baseNum,
                         'names'=>$names,
                         'lvl'=>$lvlPok,
                         'sex'=>$sex,
                         'hp_my'=>$hp,
                         'hp_max'=>$hp,
                         'atk'=>$at,
                         'def'=>$df,
                         'satk'=>$sa,
                         'sdef'=>$sd,
                         'speed'=>$sp,
                         'hp_ev'=>$ev3,
                         'atk_ev'=>$ev,
                         'def_ev'=>$ev3,
                         'satk_ev'=>$ev,
                         'sdef_ev'=>$ev3,
                         'speed_ev'=>$ev2,
                         'hp_iv'=>$iv,
                         'atk_iv'=>$iv2,
                         'def_iv'=>$iv,
                         'satk_iv'=>$iv2,
                         'sdef_iv'=>$iv,
                         'speed_iv'=>$iv3,
                         'tips'=>$tipes,
                         'startepoke'=>$idNums,
                         'reproduction'=>$questupdate,
                         'sprz'=>$razvandsparka,
                         'poimka'=>$pokDik['poimka']));
         $pokemon_start = first('SELECT id FROM pok_user WHERE users=%d AND active=1 AND startepoke=1 AND hp_my>0',$_SESSION['id']);
         $p_users = $pokemon_start['id'];
         if(empty($pokemon_start['id'])){
            $pokemon_start = first('SELECT id, CEIL(RAND()*id) as chance FROM pok_user WHERE users=%d AND active=1 AND hp_my>0 ORDER BY chance DESC LIMIT 0,1',$_SESSION['id']);
            $p_users = $pokemon_start['id'];
         }
         $time_pve = time() + 3600;
          $battl = insert('battles',array(
                 'user_1'=>$_SESSION['id'],
                 'user_2'=>$idPok,
                 'poke_1'=>'pvp_'.$p_users,
                 'poke_2'=>'pve_'.$idPok,
                 'batl_tip'=>'pve', 
                 'times'=>$time_pve));
         update('users',array('pve'=>1, 'battleid'=>$battl),'id='.(int)$_SESSION['id']);
         print "parent.loc('fight_pve');"; 
    }
   }
   }
}
print "parent.deletusers();";

if(($myrow['atack_poke'] <= time()) && $myrow['pvp'] == 0 && $myrow['pve'] == 0 && $myrow['pve_button'] == 1 && $build['pve'] > 0 && $pokemHp['countpok'] <= 0)
  print "parent.mess_error('Ваши покемоны слишком слабы, что бы начать бой. Пожалуйста, обратитесь в покецентр к сестре Джой. Она полностью восстановит силы ваших покемонов и вы сможете продолжить свой путь.','block');";

$g = 1;
$i = 0;
foreach($usersLoc as $usersLocEcho){
  if($myrow['pvp'] == 0 && $myrow['pve'] == 0) $call  = users_zayv($usersLocEcho['id']); else $call = 2;
  $id    = $usersLocEcho['id'];
  $name  = $usersLocEcho['login'];
  $color = colorsUsers($usersLocEcho['groups']);
  print 'parent.idTren['.$i.']='.$id.';';
  print 'parent.nameTren['.$i.']=\''.$name.'\';';
  print 'parent.colorTren['.$i.']=\''.$color.'\';';
  print 'parent.battleImg['.$i.']='.$call.';';
  $i++;
}


print 'parent.refresh_message('.$countmes.');';
print 'parent.refresh_online();';
print 'parent.mess_chat("';             
/** Сообщения чата **/ 
  if($myrow['mychat']==1){ 
    $dopSelect = ' AND ((tipe=1 AND room='.$myrow['buildmy'].' AND private=0) OR (userto='.$_SESSION['id'].' AND private=1) OR (private=1 AND author="'.$_SESSION['login'].'") OR (tipe=4 AND private=0)) ';
  } else { 
    $dopSelect = ' AND ((tipe=2 AND private=0) OR (userto='.$_SESSION['id'].' AND private=1) OR (private=1 AND author="'.$_SESSION['login'].'") OR (tipe=4 AND private=0)) '; 
  }
  $timeOutSends = time()-100;
  delete('chats','time<='.(int)$timeOutSends);
  if(empty($_GET["ids"])) $_GET["ids"] = 0; 
    $timeer    =  $_GET["ids"];
    $ay = false; $prclose = false;
    $au = false; $propen  = false;

$message = select('SELECT * FROM chats WHERE id > %d '.$dopSelect.' ORDER BY id',$timeer);
if($message){
  foreach($message  as $messages){
    $_GET["ids"] = $messages['id'];
    $private = $messages['private'];
     if($private == 1){ $propen = '<b>[</b>'; $prclose = '<b>]</b>'; } else { $prclose = false; $propen  = false;}
     if($messages['userto'] == $_SESSION['id'] || $messages['author'] == $_SESSION['login']){  $ay = '<b>'; $au = '</b>'; } else { $ay = false; $au = false;}
      $a = first('SELECT id,login,groups FROM users WHERE login="%s"',$messages['author']);
        if(!empty($a['id'])){        
          $colorUser = colorsUsers($a['groups']);
          $logins    = '<span style=\"color:'.$colorUser.'\">'.$a['login'].'</span>';
          $text_chat = $messages['text'];
          $mess2 = '<tt style=\"color:gold;\">['.date('H:i:s',$messages['time']).']</tt> <a href=\'javascript://\' onclick=\'parent.privat(\"'.$messages['author'].'\")\'><font color=black> PR </font></a> '.$ay.$propen.'<a href=\'javascript://\' onclick=\'parent.to_tren(\"'.$messages['author'].'\")\'>'.$logins.'</a>:  '.$text_chat.$prclose.$au.'<br>';
          print $mess2;
        }
  }
}
print '");';
//echo "alert($ids);";

print 'parent.usersloc("'.$build['title'].'");';
print 'parent.timeRefresh_game('.$_GET["ids"].');';
print '</script>';
?>