<?php
$time_my = time();
require_once __DIR__ . '/../config.php';
$connect = mysql_connect($config['server'], $config['user'], $config['pass']) or die('   ');
mysql_select_db($config['db'], $connect);
mysql_query("SET NAMES 'cp1251'");
if (!function_exists('escape')) {
function escape($string) {
    global $connect;
    return mysql_real_escape_string($string, $connect);
}
}

if (!function_exists('logdel')) {
function logdel($id){
  delete('battles','id='.(int)$id);
  delete('battle_log','battle_id='.(int)$id);
  delete('statpokemonbatle','battleid='.(int)$id);
  delete('bttle_status','buttleid='.(int)$id); 
  delete('battle_dop','battleid='.(int)$id);
  delete('bettle_attac','battleid='.(int)$id); 
  delete('attacers_battle','battleid='.(int)$id);
  delete('pok_pve','users='.(int)$_SESSION['id']);
}
}
if (!function_exists('obrid')) {
function obrid($txt){
  $id = substr($txt, 4);
  $tip = substr($txt, 0, 3);
  $subtxt = array('id'=>$id,'tip'=>$tip); 
 return $subtxt; 
}
}

if (!function_exists('poke_info')) {
function poke_info($id,$zap){
  $ibrId = obrid($id);
  $id    = $ibrId['id'];
  $tip   = $ibrId['tip'];
  if($tip == 'pvp') $tip = 'user'; 
   if($tip == 'user' || $tip == 'pve' || $tip == 'nps') 
     $q = first('SELECT '.$zap.' FROM pok_'.$tip.' WHERE id=%d',$id);
   else 
     $q = false;
   if($q) $a = $q[$zap];
    else $a = false;
 return $a;
}
}

if (!function_exists('infoBattle')) {
function infoBattle($id,$zap){
  $q = first('SELECT '.$zap.' FROM battles WHERE id=%d',$id);
   if($q) $a = $q[$zap];
    else  $a = false;
 return $a;
}
}

if (!function_exists('battle_dop')) {
function battle_dop($id,$zap,$b){
  $q = first('SELECT '.$zap.' FROM battle_dop WHERE battleid=%d AND pokeid="%s"',$b,$id);
  if($q) $a = $q[$zap];
    else $a = false;
 return $a;
}
}

if (!function_exists('attac_battle')) {
function attac_battle($id,$zap){ 
  $q = first('SELECT '.$zap.' FROM attac_power WHERE atac_id=%d',$id);
   if($q) $a = $q[$zap];
    else  $a = false; 
 if($id == 999 && $zap == 'atac_name') $a = '   ';
 if($id == 998 && $zap == 'atac_name') $a = '   ';
 return $a;
}
}

if (!function_exists('poke_tipe')) {
function poke_tipe($id,$zap){
  $q = first('SELECT '.$zap.' FROM pokemon WHERE id=%d',$id);
   if($q) $a = $q[$zap];
    else  $a = false;
 return $a;
}
}

if (!function_exists('stat_struc')) {
function stat_struc($idPokemon,$zap,$tip,$battl){
  $q = first('SELECT '.$zap.' FROM statpokemonbatle WHERE battleid=%d AND pokeid="%s" AND tip="%s"',$battl,$idPokemon,$tip);
   if($q) $a = $q[$zap];
    else  $a = 0;
 return $a;
}
}

if (!function_exists('updateHp')) {
function updateHp($pok, $hp){
  $ibrId = obrid($pok);
  $id    = $ibrId['id'];
  $tip   = $ibrId['tip'];
  if($tip == 'pvp') $tip = 'user'; 
   if($tip == 'user' || $tip == 'pve' || $tip == 'nps'){ 
     $myHp = poke_info($pok,'hp_max');
     if($hp > $myHp) $hp = $myHp;
     update('pok_'.$tip,array('hp_my'=>$hp),'id='.(int)$id);
   }   
}
}

if (!function_exists('poimkaDik')) {
function poimkaDik($hp,$hpMax,$znac){
   $CatchValue = round((( 3*$hpMax-2*$hp)*(rand(0,255) * $znac)/(3*$hp))*1);
    if($CatchValue <= 0) $CatchValue = 1; 
      $CatchValue2 = sqrt(sqrt(996711660/$CatchValue));
    if($CatchValue2 <= 0) {$CatchValue2 = 1;} 
      $Catch = round(sqrt(918510/$CatchValue2));
    if($CatchValue>$Catch) $a = true; else $a = false;
 return $a;
}
}

if (!function_exists('tittleAttak')) {
function tittleAttak($id){
   $tittle = array();
   $tittle[13]  = '     ';
   $tittle[19]  = '         ';
   $tittle[76]  = '        ';
   $tittle[91]  = '        ';
   $tittle[143] = '     ';
   $tittle[248] = '      ';
   $tittle[291] = '        ';
   $tittle[340] = '      ';
   $tittle[353] = '      ';
   $tittle[467] = '        ';
   $tittle[553] = '       ';
   $tittle[554] = '       ';
 return $tittle[$id];
}
}

if (!function_exists('mess_logs')) {
function mess_logs($battle_id,$mess,$raund) { 
  insert('battle_log',array('battle_id'=>$battle_id, 'demage'=>$mess, 'raund'=>$raund));
}
}
  
if (!function_exists('hpmy_go')) {
function hpmy_go($id,$poke,$battle)
{
  switch ($id):  
    case 208:             
      $hp_my_pl = round(poke_info($poke,'hp_max')*0.5); 
      $dm_mess = " Milk Drink    HP #".poke_info($poke,'names'); 
    break;
    
    case 234:
            if(infoBattle($battle,'id_pogodi') == 1) { $h = "0.5";   $h_tittle = "1/2"; }
        elseif(infoBattle($battle,'id_pogodi') == 2) { $h = "0.66";  $h_tittle = "2/3"; }
        else { $h = "0.25";  $h_tittle = "1/4"; }
      $hp_my_pl = round(poke_info($poke,'hp_max')*$h);  
      $dm_mess = " Morning Sun  HP #".poke_info($poke,'names')."($h_tittle  )"; 
    break;
    
    case 235:     
            if(infoBattle($battle,'id_pogodi') == 1) { $h = "0.5";   $h_tittle = "1/2"; }
        elseif(infoBattle($battle,'id_pogodi') == 2) { $h = "0.66";  $h_tittle = "2/3"; }
        else { $h = "0.25";  $h_tittle = "1/4"; }
      $hp_my_pl = round(poke_info($poke,'hp_max')*$h);  
      $dm_mess = " Synthesis  HP #".poke_info($poke,'names')."($h_tittle  )"; 
    break;
    
    case 236:
            if(infoBattle($battle,'id_pogodi') == 1) { $h = "0.5";   $h_tittle = "1/2"; }
        elseif(infoBattle($battle,'id_pogodi') == 2) { $h = "0.66";  $h_tittle = "2/3"; }
        else { $h = "0.25";  $h_tittle = "1/4"; }
      $hp_my_pl = round(poke_info($poke,'hp_max')*$h);  
      $dm_mess = " Moonlight  HP #".poke_info($poke,'names')."($h_tittle  )"; 
    break;
    
    case 303:
      $hp_my_pl = round(poke_info($poke,'hp_max')*0.5);   
      $dm_mess = " Slack Off    HP #".poke_info($poke,'names'); 
    break;
  endswitch;
    
    $res_status = array('mess_status' => $dm_mess, 'hp_up'=>$hp_my_pl);
return $res_status; 
}
}
if (!function_exists('items_go_pokes')) {
function items_go_pokes($poke_xod, $battle) {
	
    $message = false;
    $p1 = infoBattle($battle, 'poke_1');
    $p2 = infoBattle($battle, 'poke_2');

    if ($p1 == $poke_xod) $c = "to_it";
    if ($p2 == $poke_xod) $c = "to_it2";

    $users = poke_info($poke_xod, 'users');
    $item = infoBattle($battle, $c);

    // 
    if ($item == 3 && infoBattle($battle, 'batl_tip') == 'pve' && poke_info($p2, 'poimka') == 1) {
        $pokeball = poimkaDik(poke_info($p2, 'hp_my'), poke_info($p2, 'hp_max'), '1');

        if ($pokeball == false) {
            $r = rand(1, 2);
            if ($r == 1) {
                $message = users($users, 'login') . ", : ,   ";
            } else {
                $message = users($users, 'login') . ", : ,  #" . poke_info($p2, 'names') . "      ";
            }
        } else {
            $countPoke = first('SELECT COUNT(*) as countpok FROM pok_user WHERE users=%d AND active=1', $_SESSION['id']);
            $active = ($countPoke['countpok'] >= 6) ? 0 : 1;

            $p2 = obrid($p2);
            pokemonDicPluse($p2['id'], $battle, $active, $_SESSION['id']);
            $a = time() + 30;

            query('UPDATE users SET pve=0, rang_a=rang_a+1, atack_poke=%d WHERE id=%d', $a, $_SESSION['id']);
            $_SESSION['klicers'] = $_SESSION['klicers'] + 1;
            logdel($battle);
            minus_item(1, $item, $users);
            die('<script>alert("  ");location.href=\'/game.php?go=char\';</script>');
        }
    }

    // 
    if ($item == 15) {
        delete('bttle_status', 'pokeid="' . addslashes($poke_xod) . '" AND namber_st=2');
        $message = users($users, 'login') . ", : , : #" . poke_info($poke_xod, 'names');
    }

    //  +20 HP
    if ($item == 91) {
        $h = poke_info($poke_xod, 'hp_my') + 20;
        if ($h > poke_info($poke_xod, 'hp_max')) $h = poke_info($poke_xod, 'hp_max');
        updateHp($poke_xod, $h);
        $message = users($users, 'login') . ", : , : #" . poke_info($poke_xod, 'names');
    }

    //  
    if ($item == 93) {
        $h = poke_info($poke_xod, 'hp_max');
        $a = "";

        if (rand(1, 100) <= 50) {
            delete('bttle_status', 'pokeid="' . addslashes($poke_xod) . '"');
            $a = ",      ";
        }

        updateHp($poke_xod, $h);
        $message = users($users, 'login') . ", :  , : #" . poke_info($poke_xod, 'names') . "     " . $a;
    }

    //    :
    // if ($message != false) minus_item(1, $item, $users);

    return $message;
}
}

if (!function_exists('attaks_stat')) {
function attaks_stat($p,$a,$battl){    
  if(attac_battle($a,'atac_categori') == 1) $atk = poke_info($p,'atk')*((2+stat_struc($p,'attac','plus',$battl))/(2+stat_struc($p,'attac','minus',$battl)));    
  if(attac_battle($a,'atac_categori') == 2) $atk = poke_info($p,'satk')*((2+stat_struc($p,'spattac','plus',$battl))/(2+stat_struc($p,'spattac','minus',$battl))); 
  if(attac_battle($a,'atac_categori') == 3) $atk = poke_info($p,'atk')*((2+stat_struc($p,'attac','plus',$battl))/(2+stat_struc($p,'attac','minus',$battl)));   
return $atk;
}
}
if (!function_exists('def_stat')) {
function def_stat($p,$a,$battl){
  if(attac_battle($a,'atac_categori') == 1) $def = poke_info($p,'def')*((2+stat_struc($p,'defend','plus',$battl))/(2+stat_struc($p,'defend','minus',$battl)));    
  if(attac_battle($a,'atac_categori') == 2) $def = poke_info($p,'sdef')*((2+stat_struc($p,'spdefend','plus',$battl))/(2+stat_struc($p,'spdefend','minus',$battl))); 
  if(attac_battle($a,'atac_categori') == 3) $def = poke_info($p,'def')*((2+stat_struc($p,'defend','plus',$battl))/(2+stat_struc($p,'defend','minus',$battl)));   
return $def;
}
}

if (!function_exists('stat_no_pok')) {
function stat_no_pok($id,$p,$battle) {
  switch ($id):  
    case 39:
      $def = "1"; 
      $atc = "0"; 
      $sdef = "0"; 
      $satc = "0";
      $speed = "0";
      $tip_s = "minus";
      $mess = ' [ #'.poke_info($p,'names').'   1 ]';
    break;    
    case 43:
      $def = "1"; 
      $atc = "0"; 
      $sdef = "0"; 
      $satc = "0";
      $speed = "0";
      $tip_s = "minus";
      $mess = ' [ #'.poke_info($p,'names').'   1 ]';
    break;    
    case 45:
      $def = "0"; 
      $atc = "1"; 
      $sdef = "0"; 
      $satc = "0";
      $speed = "0";
      $tip_s = "minus";
      $mess = ' [ #'.poke_info($p,'names').'   1 ]';
    break;    
    case 103:
      $def = "2"; 
      $atc = "0"; 
      $sdef = "0"; 
      $satc = "0";
      $speed = "0";
      $tip_s = "minus";
      $mess = ' [ #'.poke_info($p,'names').'   2 ]';
    break;
    case 297:
      $def = "0"; 
      $atc = "2"; 
      $sdef = "0"; 
      $satc = "0";
      $speed = "0";
      $tip_s = "minus";
      $mess = ' [ #'.poke_info($p,'names').'   2 ]';
    break;    
    case 313:
      $def = "0"; 
      $atc = "0"; 
      $sdef = "2"; 
      $satc = "0";
      $speed = "0";
      $tip_s = "minus";
      $mess = ' [.  #'.poke_info($p,'names').'   2 ]';
    break;
    case 319:
      $def = "0"; 
      $atc = "0"; 
      $sdef = "2"; 
      $satc = "0";
      $speed = "0";
      $tip_s = "minus";
      $mess = ' [.  #'.poke_info($p,'names').'   2 ]';
    break;
  endswitch;
  $yes_s = first('SELECT * FROM statpokemonbatle WHERE battleid=%d AND pokeid="%s" AND tip="%s"',$battle,$p,$tip_s);
    if(!$yes_s) insert('statpokemonbatle',array('battleid'=>$battle,'pokeid'=>$p,'defend' =>$def,'attac'=>$atc,'speed'=>$speed,'spattac'=>$satc,'spdefend'=>$sdef,'tip'=>$tip_s));
else {                           
    if ($yes_s['defend'] <= 6)    $d = $yes_s['defend'] + $def;
    if ($d > 6) $d = 6;   

    if ($yes_s['attac'] <= 6)     $a = $yes_s['attac'] + $atc;
    if ($a > 6) $a = 6;

    if ($yes_s['speed'] <= 6)     $s = $yes_s['speed'] + $speed;
    if ($s > 6) $s = 6;

    if ($yes_s['spattac'] <= 6)   $sa = $yes_s['spattac'] + $satc;
    if ($sa > 6) $sa = 6;

    if ($yes_s['spdefend'] <= 6)  $sd = $yes_s['spdefend'] + $sdef;
    if ($sd > 6) $sd = 6;

    $safe_p = mysql_real_escape_string($p, $connect);
    $safe_tip = mysql_real_escape_string($tip_s, $connect);

    update('statpokemonbatle', array(
        'tip'       => $tip_s,
        'defend'    => $d,
        'attac'     => $a,
        'speed'     => $s,
        'spattac'   => $sa,
        'spdefend'  => $sd
    ), 'battleid=' . (int)$battle . ' AND pokeid="' . $safe_p . '" AND tip="' . $safe_tip . '"');
}
 return $mess;
}
}

if (!function_exists('stats_insert')) {
function stats_insert($p, $battle, $def, $atc, $sdef, $satc, $speed, $acc, $accuracy, $tip_s){
  global $connect;

  $yes_s = first('SELECT * FROM statpokemonbatle WHERE battleid=%d AND pokeid="%s" AND tip="%s"', $battle, $p, $tip_s);
  if(!$yes_s) {
    insert('statpokemonbatle', array(
      'battleid'=>$battle, 'pokeid'=>$p, 'defend'=>$def, 'attac'=>$atc, 'speed'=>$speed,
      'spattac'=>$satc, 'spdefend'=>$sdef, 'acc'=>$acc, 'accuracy'=>$accuracy, 'tip'=>$tip_s
    ));
  } else {
    $d = min($yes_s['defend'] + $def, 6);
    $a = min($yes_s['attac'] + $atc, 6);
    $s = min($yes_s['speed'] + $speed, 6);
    $sa = min($yes_s['spattac'] + $satc, 6);
    $sd = min($yes_s['spdefend'] + $sdef, 6);
    $ac = min($yes_s['acc'] + $acc, 6);
    $ar = min($yes_s['accuracy'] + $accuracy, 6);

    update('statpokemonbatle', array(
      'tip'=>$tip_s, 'defend'=>$d, 'attac'=>$a, 'speed'=>$s,
      'spattac'=>$sa, 'spdefend'=>$sd, 'acc'=>$ac, 'accuracy'=>$ar
    ), 'battleid='.(int)$battle.
        ' AND pokeid="'.mysql_real_escape_string($p, $connect).'"'.
        ' AND tip="'.mysql_real_escape_string($tip_s, $connect).'"');
  }
}
}


if (!function_exists('stat_gous_pok')) {
function stat_gous_pok($id,$p,$battle){ 
  $ok  = false;
  $at  = "";
  $at2 = "";
  $bt  = "";
  $a   = first('SELECT * FROM stat_attak WHERE id_atk=%d',$id);
    if($a['tip'] == "plus"){
      $ok        = true;
      $def       = $a['def']; 
      $atc       = $a['atc'];
      $acc       = $a['acc']; 
      $sdef      = $a['sdef']; 
      $satc      = $a['satc']; 
      $speed     = $a['speed'];
      $accuracy  = $a['accuracy']; 
      $tip_s     = "plus";
        if($def > 0)        $at .= " [  +".$def."] ";
        if($atc > 0)        $at .= " [  +".$atc."] ";
        if($acc > 0)        $at .= " [  +".$acc."] ";
        if($sdef > 0)       $at .= " [.   +".$sdef."] ";
        if($satc > 0)       $at .= " [.   +".$satc."] ";
        if($speed > 0)      $at .= " [  +".$speed."] ";
        if($accuracy > 0)   $at .= " [  +".$accuracy."] ";
      $bt = "<br><font color='gold'> #".poke_info($p,'names')." : </font><br>".$at;
     stats_insert($p,$battle,$def,$atc,$sdef,$satc,$speed,$acc,$accuracy,$tip_s);
    }
    if($a['tip_b'] == "minus"){
      $ok         = true;
      $def        = $a['def_b']; 
      $atc        = $a['atc_b'];
      $acc        = $a['acc_b']; 
      $sdef       = $a['sdef_b']; 
      $satc       = $a['satc_b']; 
      $speed      = $a['speed_b'];
      $accuracy   = $a['accuracy_b'];
      $tip_s      = "minus";
        if($def > 0)        $at2 .= " [  -".$def."] ";
        if($atc > 0)        $at2 .= " [  -".$atc."] ";
        if($acc > 0)        $at2 .= " [  -".$acc."] ";
        if($sdef > 0)       $at2 .= " [.   -".$sdef."] ";
        if($satc > 0)       $at2 .= " [.   -".$satc."] ";
        if($speed > 0)      $at2 .= " [  -".$speed."] ";
        if($accuracy > 0)   $at2 .= " [  -".$accuracy."] ";
      $bt .= " <br><font color='gold'> #".poke_info($p,'names')." : </font><br>".$at2;
     stats_insert($p,$battle,$def,$atc,$sdef,$satc,$speed,$acc,$accuracy,$tip_s);
    }
  $mess = ". ".$bt;
 return $mess;
}
}

if (!function_exists('status_my')) {
function status_my($p,$b){
  $status_yes = first('SELECT bsp.id_sts, bsp.namber_st FROM bttle_status bsp INNER JOIN status sts ON bsp.namber_st=sts.id_status WHERE bsp.buttleid=%d AND bsp.pokeid="%s"',$b,$p); 
  $hp_up = false;
  $namber_st_yes = false;
    if($status_yes){
    $namber_st_yes = $status_yes['namber_st'];
      switch ($namber_st_yes):  
        case 1:
          $hp_up = round(poke_info($p,'hp_max')/8);        
          $mess = ',     #'.poke_info($p,'names');
          $resultatic = 1;
          $ok = 1; 
        break;
        case 2:
          $hp_up = false;
          $resultatic = 2;
          $mess = "";
          $ok = 0; 
        break;
        case 3:     
          $hp_up = round(poke_info($p,'hp_max')/8);
          $mess = ',   #'.poke_info($p,'names');
          $resultatic = 1;
          $ok = 1; 
        break;
        case 4:
          $hp_up = false;
          $resultatic = 3;
          $mess = "";
          $ok = 0;
        break;
        case 5:
          $hp_up = false;
            if(rand(1,100) <= 25){
              $resultatic = 4;
              $mess = "";
            }else{
              $resultatic = 1;
              $mess = "";     
            }
          $ok = 0;
        break;
        case 6:
          $hp_up = false;
          $resultatic = 5;
          $mess = "";
          $ok = 0;
        break;
        case 7:
          $hp_up = false;
            if(rand(1,100) <= 50){
              $resultatic = 6;
              $mess = " ";
            }else{ 
              $resultatic = 1;
              $mess = "";      
            }
          $ok = 0;
        break;
        case 8:
          $hp_up = round(poke_info($p,'hp_max')/8);        
          $mess = ', -    #'.poke_info($p,'names');
          $resultatic = 1;
          $ok = 1; 
        break;
        case 9:
          $hp_up = round(poke_info($p,'hp_max')/4);        
          $mess = ', #'.poke_info($p,'names').'    ,    ';
          $resultatic = 1;
          $ok = 1; 
        break;
      endswitch;
    }else{
      $mess = "";
      $resultatic = 1;
    }
  delete('bttle_status','raund_end<='.(int)infoBattle($b,'raund').' AND  buttleid='.(int)$b); 
  $res_status = array('resultatic'=>$resultatic, 'mess_status'=>$mess, 'hp_up'=>$hp_up, 'id_status'=>$namber_st_yes); 
 return $res_status;
}
}

if (!function_exists('status_go')) {
function status_go($namber_status, $pokemon, $battle){
    global $connect;

    $tip_pokes = 1;
    $ok_stat = 1;
    $st_stat = false;
    $update = false;

    switch ($namber_status):
        case 1:
            $ran = ''.mt_rand(9999,99999);
            $random = infoBattle($battle, 'raund') + $ran;
            $number_st = 1;
            $mess3 = ' [#'.poke_info($pokemon,'names').' <b></b>]';
            break;
        case 2:
            $ran = ''.mt_rand(2,5);
            $random = infoBattle($battle, 'raund') + $ran;
            $number_st = 2;
            $mess3 = ' [#'.poke_info($pokemon,'names').' <b></b>]';
            break;
        case 3:
            $ran = ''.mt_rand(9999,99999);
            $random = infoBattle($battle, 'raund') + $ran;
            $number_st = 3;
            $mess3 = ', [#'.poke_info($pokemon,'names').'  ,       2 ]';
            $st_stat = 1;
            $atc = 2;
            $tip_s = "minus";
            break;
        case 4:
            $ran = ''.mt_rand(9999,99999);
            $random = infoBattle($battle, 'raund') + $ran;
            $number_st = 4;
            $mess3 = ' [#'.poke_info($pokemon,'names').' <b></b>]';
            break;
        case 5:
            $ran = ''.mt_rand(9999,99999);
            $random = infoBattle($battle, 'raund') + $ran;
            $number_st = 5;
            $st_stat = 1;
            $speed = 4;
            $tip_s = "minus";
            $mess3 = ' [#'.poke_info($pokemon,'names').' <b></b>,       4 ]';
            break;
        case 6:
            $ran = 1;
            $random = infoBattle($battle, 'raund') + $ran;
            $number_st = 6;
            $mess3 = ' [#'.poke_info($pokemon,'names').' <b></b>]';
            break;
        case 7:
            $ran = ''.mt_rand(9999,99999);
            $random = infoBattle($battle, 'raund') + $ran;
            $number_st = 7;
            $mess3 = ' [#'.poke_info($pokemon,'names').' <b></b>]';
            break;
        case 8:
            $ran = ''.mt_rand(9999,99999);
            $random = infoBattle($battle, 'raund') + $ran;
            $number_st = 8;
            $mess3 = ' [ #'.poke_info($pokemon,'names').' <b> -</b>]';
            break;
        case 9:
            $ran = ''.mt_rand(9999,99999);
            $random = infoBattle($battle, 'raund') + $ran;
            $number_st = 9;
            $mess3 = ' [ #'.poke_info($pokemon,'names').' <b></b>]';
            $update = true;
            break;
        case 777:
            $ran = 2;
            $random = infoBattle($battle, 'raund') + $ran;
            $number_st = 2;
            $mess3 = ' [#'.poke_info($pokemon,'names').' <b></b>]';
            $update = true;
            break;
    endswitch;

    $status_yes = first('SELECT id_sts FROM bttle_status WHERE battleid=%d AND pokeid="%s"', $battle, $pokemon);

    if (!$status_yes) {
        insert('bttle_status', array(
            'namber_st' => $number_st,
            'battleid' => $battle,
            'pokeid' => $pokemon,
            'raund_end' => $random,
            'tip_poke' => $tip_pokes
        ));
    } elseif ($status_yes && $update === true) {
        update('bttle_status', array(
            'namber_st' => $number_st,
            'raund_end' => $random
        ), 'pokeid="' . mysql_real_escape_string($pokemon, $connect) . '" AND battleid=' . (int)$battle);
    } else {
        $mess3 = " [: #" . poke_info($pokemon, 'names') . "    ]";
        $ok_stat = 0;
    }

    if ($st_stat && $ok_stat) {
        if (empty($def)) $def = 0;
        if (empty($atc)) $atc = 0;
        if (empty($acc)) $acc = 0;
        if (empty($satc)) $satc = 0;
        if (empty($sdef)) $sdef = 0;
        if (empty($speed)) $speed = 0;
        if (empty($accuracy)) $accuracy = 0;

        stats_insert($pokemon, $battle, $def, $atc, $sdef, $satc, $speed, $acc, $accuracy, $tip_s);
    }

    return $mess3;
}
}


if (!function_exists('status_isset')) {
function status_isset($idst,$pokes,$battle){
  $status_yes = first('SELECT id_sts FROM bttle_status WHERE buttleid=%d AND pokeid="%s" AND namber_st=%d',$battle,$pokes,$idst);
    if($status_yes['id_sts'] == true) $ret = true; 
      else $ret = false;
 return $ret;
}
}

if (!function_exists('propusc_xod')) {
function propusc_xod($p, $battle) {
    global $connect;

    $mess = false;
    $a_dop = false;
    $xodov = '0';

    $z = first('SELECT propusk,at_dop,mess FROM battle_dop WHERE battleid=%d AND pokeid="%s"', $battle, $p);

    if ($z) {
        $c = false;
        if (infoBattle($battle, 'poke_1') == $p) $c = "1";
        if (infoBattle($battle, 'poke_2') == $p) $c = "2";

        if ($c !== false) {
            $xodov = $z['propusk'] - 1;

            $pokeid_safe = mysql_real_escape_string($p, $connect);

            if ($xodov <= 0) {
                if ($z['at_dop'] != 0) {
                    $a_dop = $z['at_dop'];
                    delete('battle_dop', 'battleid=' . (int)$battle . ' AND pokeid="' . $pokeid_safe . '"');
                    update('battles', array('attac_' . $c => $z['at_dop']), 'id=' . (int)$battle);
                } else {
                    delete('battle_dop', 'battleid=' . (int)$battle . ' AND pokeid="' . $pokeid_safe . '"');
                }
            } else {
                update('battle_dop', array('propusk' => $xodov), 'battleid=' . (int)$battle . ' AND pokeid="' . $pokeid_safe . '"');
            }

            $mess = $z['mess'];
        }
    }

    return array(
        'hod' => $xodov,
        'mess' => $mess,
        'attac' => $a_dop
    );
}
}


if (!function_exists('new_propusc')) {
function new_propusc($p, $battle, $atk){
  $mes_a = false;
  $id_atk = $atk;
  if(attac_battle($atk,'dop_effect') == "propusk"){
    if ($id_atk == 63 OR $id_atk == 307 OR $id_atk == 338 OR $id_atk == 416){                                                     
      $mes_a = "    : <a href=javascript: onClick=win1=window.open('/game.php?go=atk&id=".$id_atk."','atk','width=726,height=260,scrollbars=yes');return true;>".attac_battle($atk,'atac_name')."</a>";
      $cols = 2;
      insert('battle_dop',array('propusk'=>$cols, 'mess'=>$mes_a, 'pokeid'=>$p, 'battleid'=>$battle));
    } 
  }
  elseif(attac_battle($atk,'dop_effect') == "dopropusk"){
    $cols = 1;
      if ($id_atk == 76){
        $mes_a = tittleAttak($id_atk);
        $pr = 0;
        $cols = 1;
      }
  elseif ($id_atk == 13 OR $id_atk == 19 OR $id_atk == 91 OR $id_atk == 143 OR $id_atk == 248 OR $id_atk == 291 OR $id_atk == 340 OR $id_atk == 353 OR $id_atk == 467 OR $id_atk == 553 OR $id_atk == 554){
    $mes_a = tittleAttak($id_atk);
      if ($id_atk == 19 OR $id_atk == 91 OR $id_atk == 291 OR $id_atk == 340) $pr = 1; else  $pr = 0;
      if ($id_atk == 248 || $id_atk == 351) $cols = 2;
  }
  insert('battle_dop',array('propusk'=>$cols, 'mess'=>$mes_a, 'pokeid'=>$p, 'battleid'=>$battle, 'at_dop'=>$id_atk, 'vulnerability'=>$pr));
 }
return $mes_a;
} 
}

if (!function_exists('attacers')) {
function attacers($a,$p,$battle){
 $zap_a = first('SELECT * FROM attacers_battle WHERE battleid=%d AND pokeid="%s"',$battle,$p);
  if($zap_a['round_end'] > 0){
    $attac_return = $zap_a['id_attac'];
    if($attac_return <= 0) $attac_return = $a;  
    $resultat_zap = $zap_a['round_end'];
    if(($zap_a['status_isset'] > 0) && ($resultat_zap <= infoBattle($battle,'raund')) && ($zap_a['pokeid'] == $p)){                                                    
      if(infoBattle($battle,'poke_1') == $p) { update('battles',array('effect_go' =>1, 'effect' =>$zap_a['status_isset']),'id='.(int)$battle); }
      if(infoBattle($battle,'poke_2') == $p) { update('battles',array('effect_go2'=>1, 'effect2'=>$zap_a['status_isset']),'id='.(int)$battle); }
    }
    query('UPDATE attacers_battle SET round_end=round_end-1 WHERE id=%d',$zap_a['id']);
  }else{
    $attac_return = $a;
  }
return $attac_return;
}
}

if (!function_exists('attacers_on')) {
function attacers_on($a,$p,$battle){
  $zap_a = first('SELECT id FROM attacers_battle WHERE pokeid="%s" AND battleid=%d',$p,$battle);
      if($a == 37) { $b = 37;   $rounds = infoBattle($battle,'raund') + 1; $status = 7;}
  elseif($a == 80) { $b = 80;   $rounds = infoBattle($battle,'raund') + 1; $status = 7;}
  elseif($a == 200){ $b = 200;  $rounds = infoBattle($battle,'raund') + 1; $status = 7;}
  if(!$zap_a && $b == true) $return = insert('attacers_battle',array('id_attac'=>$b,'pokeid'=>$p,'battleid'=>$battle, 'status_isset'=>$status, 'round_end'=> $rounds));
    else $return = false;
   delete('attacers_battle','round_end<='.(int)$zap_a['id'].' AND battleid='.(int)$battle);
 return $return;
}
}

if (!function_exists('dmg')) {
function dmg($poke,$poke2,$attak,$attak2,$battlId){ 
  $pokemon_speed  = poke_info($poke, 'speed')*((2+stat_struc($poke ,'speed','plus', $battlId))/(2+stat_struc($poke ,'speed','minus', $battlId))); 
  $pokemon2_speed = poke_info($poke2,'speed')*((2+stat_struc($poke2,'speed','plus', $battlId))/(2+stat_struc($poke2,'speed','minus', $battlId)));
    function img($pokeBaseId){
      if($pokeBaseId > 493) $imgAnimOne  = "png"; else $imgAnimOne = "gif";
     return $imgAnimOne;
    }     
	$types_atk1 = attac_battle($attak,'atac_tip'); 
    $types_atk2 = attac_battle($attak2,'atac_tip'); 
	
   $attak  = attacers($attak ,$poke ,$battlId);
   $attak2 = attacers($attak2,$poke2,$battlId);      
 
      function demagePokes($pokemon,$pokemonAttak,$pokemonEnemy,$pokemonEnemyAttak,$idBattle,$numbersXod){
          $dop_attaci_udar = false;
          $array_propusk   = propusc_xod($pokemon,$idBattle);
          $otdacha_hp      = 0;
          $hp_ot_uron      = 0;
          $uronNoBase      = false;
          $status_my       = status_my($pokemon,$idBattle);
          $dm_mess_e       = false;
          $crit_udar       = 1;
          $goPokemon       = false;
          $crt_mess        = false;
          $udarTrue        = 'go';
          $tip_sum         = false;
          $tip_res         = false;
          $txtObr          = false;
          $zamena          = 0;
          $smena           = 1;
          $stabs           = false;
          $rs_hp           = false;
          $hp_no           = false;
          $pp_up           = 0;
          $stab            = 1;
          $t_ur            = false;
          $img             = "gif";
          $x10             = 1; 
          $ok              = true;
          $dm              = false;

          if($array_propusk['attac'] != false) $pokemonAttak = $array_propusk['attac']; 
          if(poke_info($pokemon,'hp_my') > 0){
            //die();
            /*** / -  ***/
              if(poke_info($pokemon,'basenum') > 493) $img  = "png";    
              if(attac_battle($pokemonAttak,'attac_effecti') == 1) attacers_on($pokemonAttak,$pokemon,$idBattle);  
              if($status_my['resultatic'] == 1 || $status_my['resultatic'] == 6){
               if($status_my['resultatic'] == 1){
                if(battle_dop($pokemonEnemy,'vulnerability',$idBattle) == 1 && attac_battle($pokemonAttak,'dop_effect') != 2){ 
                  $txtObr = "  #".poke_info($pokemonEnemy,'names')."     ";
                  $udarTrue = 'other'; 
                  $ok = false; 
                }
                if($array_propusk['hod'] == true){
                  $txtObr = $array_propusk['mess'];
                  $udarTrue = 'other';  
                  $ok = false; 
                  $smena = 0;
                }
                if($pokemonEnemyAttak == 182 || $pokemonEnemyAttak == 197){
                  $txtObr = "  #".poke_info($pokemonEnemy,'names')."     ";
                  $udarTrue = 'other';
                  $pp_up = 1;  
                  $ok = false;        
                } 
                if((attac_battle($pokemonAttak,'dop_effect') == "dopropusk") && ($array_propusk['hod'] == false) && ($array_propusk['attac'] == false)){  
                  $txtObr = new_propusc($pokemon, $idBattle, $pokemonAttak);
                  $smena = 0;
                  $udarTrue = 'other';
                  $pp_up = 1;  
                  $ok = false;
                }
               }
              }else{
                  $udarTrue = 'other';
                  $txtObr = "   ".$status_my['mess_status'];
              }
              if($pokemonAttak == "998" && $ok == true && $smena == 1){
                $udarTrue == 'item';
                $txtObr = items_go_pokes($pokemon,$idBattle);
                $ok = false;
                $goPokemon = true; 
                $pp_up = 0; 
              }
              if($pokemonAttak == "999" && $ok == true && $smena == 1){
                if(infoBattle($idBattle,'poke_1') == $pokemon) { $c = "to_p";  $po = "poke_1"; }
                if(infoBattle($idBattle,'poke_2') == $pokemon) { $c = "to_p2"; $po = "poke_2"; }
                $cb  = first('SELECT '.$c.' FROM battles WHERE id=%d',$idBattle);
                if($cb[$c]) update('battles',array($po => $cb[$c]),'id='.(int)$idBattle);
                $txtObr = "<b>".users(poke_info($cb[$c],'users'),'login')."</b>,   : #".poke_info($cb[$c],'names')."";
                $ok = false;
                $goPokemon = true; 
                $zamena = "1";
                $pp_up = 0;
                $udarTrue == 'item'; 
              }
              if($ok == 0 && $zamena != 1){
                if($status_my['hp_up'] != false) {
                  $status_hp = $status_my['hp_up'];
                  $rs_hp_my = round(poke_info($pokemon,'hp_my') - $status_hp);
                  $rs_hp_my = ($rs_hp_my<=0?0:$rs_hp_my);
                  if($rs_hp_my > poke_info($pokemon,'hp_max')) $rs_hp_my = poke_info($pokemon,'hp_max');
                  updateHp($pokemon, $rs_hp_my);
                  $txtObr .= $status_my['mess_status'];
                  if($rs_hp_my <= 0) $txtObr .= ",      !";
                }
              }
            if($ok == 1){        
              if($pokemonAttak == 389  AND ((attac_battle($pokemonEnemyAttak,'atac_categori') == 2) OR (attac_battle($pokemonEnemyAttak,'atac_categori') == 1))){  
              
              }else{
                if($pokemonAttak == 389){
                  $txtObr = " ";
                  $udarTrue = 'other';  
                  $ok = false; 
                  $pp_up = 0;
                }  
              }
            }
            if($ok == 1){        
                if($pokemonAttak == 173  AND status_isset(2,$pokemon,$idBattle) == true){
                  $status_my['resultatic'] = 1;
                  $x10 = 0;
                }else{
                  if($pokemonAttak == 173){
                    $txtObr = " ";
                    $udarTrue = 'other';  
                    $ok = false;
                    $pp_up = 0;
                  }
                }
                if($pokemonAttak == 264 AND $numbers == 2){
                    $a10 = status_my($pokemonEnemy,$idBattle);
                    if($a10['resultatic'] == 1 && attac_battle($pokemonEnemyAttak,'atac_categori') != 3){
                      $txtObr = " ";
                      $udarTrue = 'other';  
                      $ok = false;
                      $pp_up = 0;
                    }
                }
            }
            if($goPokemon == false){
            if($ok == true){ 
             /**   ,    ,  . **/
              $tip_atttac            = attac_battle($pokemonAttak,'atac_tip'); 
              $pokemonEnemy_tipe_one = poke_tipe(poke_info($pokemonEnemy,'basenum'),'Element');
              $pokemonEnemy_tipe_two = poke_tipe(poke_info($pokemonEnemy,'basenum'),'SubElement');
              $pokemonMy_tipe_one = poke_tipe(poke_info($pokemon,'basenum'),'Element');
              $pokemonMy_tipe_two = poke_tipe(poke_info($pokemon,'basenum'),'SubElement');
              $typesAttac['1']       = tip($tip_atttac,$pokemonEnemy_tipe_one);
                if($pokemonEnemy_tipe_two == "None" OR $pokemonEnemy_tipe_two == false) $typesAttac['2'] = 1; 
                  else $typesAttac['2'] = tip($tip_atttac,$pokemonEnemy_tipe_two); 
              $tip_res = $typesAttac['1']*$typesAttac['2']; //  .
             /** -END- **/
            }
            if($status_my['resultatic'] == 1 OR $status_my['resultatic'] == 6){
              $lupe = round(attac_battle($pokemonAttak,'atac_accuracy'));        
                if($lupe == 0 OR $lupe == false) $lupe_res = 9999;
                  else{
                    $pl_user    = stat_struc($pokemon,'accuracy','plus',  $idBattle); // 
                    $min_user   = stat_struc($pokemon,'accuracy','minus', $idBattle); // 
                    $pl_nouser  = stat_struc($pokemonEnemy, 'acc','plus', $idBattle); // 
                    $min_nouser = stat_struc($pokemonEnemy, 'acc','minus',$idBattle); // 
                    $item_my = "0";
                    $item_no = "0";
                    $lupe_res = floor($lupe*((3+$pl_user+$min_nouser)/(3+$min_user+$pl_nouser))*(1+$item_my-$item_no));
                  }
                if(rand(1,100) <= $lupe_res){
                  $pp_up = 1;
                    if(attac_battle($pokemonAttak,'atac_categori') == 1 OR attac_battle($pokemonAttak,'atac_categori') == 2){
                      $crit = 1*attac_battle($pokemonAttak,'critic'); /**   **/
                      $randomnumber = ''.mt_rand(85,100); /**   **/
                        if($tip_res > 0){
                          if((poke_tipe(poke_info($pokemon,'basenum'),'Element') == $tip_atttac) OR (poke_tipe(poke_info($pokemon,'basenum'),'SubElement') == $tip_atttac)){
                            $stab = 1.5; 
                            $stabs = ", (STAB)"; 
                          }else{
                            $stab = 1;
                            $stabs = ""; 
                          }
                          if(rand(1,100) <= $crit){
                            $crit_udar = 2; 
                            $crt_mess  = ", <font color=gold><i> !</i></font>"; 
                          }else{
                            $crit_udar = 1;
                            $crt_mess  = "";
                          }
                        }
                        $lvl_my = poke_info($pokemon,'lvl');
                        $atk = attaks_stat($pokemon,$pokemonAttak,$idBattle);
                        $def = def_stat($pokemonEnemy,$pokemonAttak,$idBattle);
                        $power_atak = attac_battle($pokemonAttak,'atac_power');
                        $ok_dm = 0;
                        $ok_demag = 1;
                        if($power_atak <= 0) $power_atak = 1;
                        
                         if($tip_res > 0){
                           if($pokemonAttak == 82){
                             $damage = 40;
                             $ok_dm = 1;
                           }
                           if($pokemonAttak == 101 || $pokemonAttak == 69){ 
                             $damage = poke_info($pokemon,'lvl');  
                             $ok_dm = 1;
                           }
                           elseif($pokemonAttak == 149){ 
                              if(poke_info($pokemonEnemy,'hp_my') != 1) $x = poke_info($pokemonEnemy,'hp_my')*0.5;
                                else $x = 1;
                             $damage = $x;  
                             $ok_dm = 1;
                           }
                           elseif($pokemonAttak == 175 OR $pokemonAttak == 179){
                              $hd = (poke_info($pokemon,'hp_my')/poke_info($pokemon,'hp_max'))*100;
                                if($hd > 100) $hd = 100;
                                    
                                    if($hd > 70 AND $hd <= 100) $power_atak = 20;
                                elseif($hd > 35 AND $hd <= 71 ) $power_atak = 40;
                                elseif($hd > 20 AND $hd <= 36 ) $power_atak = 80;
                                elseif($hd > 10 AND $hd <= 21 ) $power_atak = 100;
                                elseif($hd >  5 AND $hd <= 11 ) $power_atak = 150;
                                elseif($hd >  0 AND $hd <= 4  ) $power_atak = 200;
                                else $power_atak = 80;    
                                $ok_dm = 0;
                           }
                           elseif($pokemonAttak == 217){
                              $hd = rand(1,50);
                                    if($hd < 40 && $hd > 30) $power_atak = 20;
                                elseif($hd < 30 && $hd > 20) $power_atak = 80;
                                elseif($hd < 20 && $hd > 10) $power_atak = 120;
                                elseif($hd < 10 && $hd > 1) { 
                                  $hp_ot_uron = 80; 
                                  $ok_demag = 0; 
                                  $dm_mess_e = ",   #".poke_info($pokemon,'names')."  80  "; 
                                }
                                else $power_atak = 20;    
                              $ok_dm = 0;
                           }
                           elseif($pokemonAttak == 222){
                              $hd = rand(1,7);
                              $a_222 = rand(4,10);  
                                    if($hd == 1 && $a_222 == 4)  $power_atak = 10;
                                elseif($hd == 2 && $a_222 == 5)  $power_atak = 30;
                                elseif($hd == 3 && $a_222 == 6)  $power_atak = 50;
                                elseif($hd == 4 && $a_222 == 7)  $power_atak = 70;
                                elseif($hd == 5 && $a_222 == 8)  $power_atak = 90;
                                elseif($hd == 6 && $a_222 == 9)  $power_atak = 110;
                                elseif($hd == 7 && $a_222 == 10) $power_atak = 150;
                                else $power_atak = 35;    
                              $ok_dm = 0;
                           }
                           elseif($pokemonAttak == 251){
                              $couny_poke = first('SELECT COUNT(*) as count FROM pok_user WHERE users=%d and active=1',$_SESSION['id']);
                              $hd = $couny_poke['count'];
                                    if($hd == 1)  $power_atak = 10;
                                elseif($hd == 2)  $power_atak = 30;
                                elseif($hd == 3)  $power_atak = 50;
                                elseif($hd == 4)  $power_atak = 70;
                                elseif($hd == 5)  $power_atak = 90;
                                elseif($hd == 6)  $power_atak = 110;
                                else $power_atak = 35;    
                              $ok_dm = 0;
                           }
                          elseif($pokemonAttak == 283){
                              $x = (poke_info($pokemonEnemy,'hp_my') - poke_info($pokemon,'hp_my'));
                               if($x <= 0 ) $x = 1;
                              $damage = $x; 
                              $ok_dm = 1; 
                          }
                          elseif($pokemonAttak == 360 OR $pokemonAttak == 486){
                              $x = round(25*(poke_info($pokemonEnemy,'speed')/poke_info($pokemon,'speed')));
                               if($x > 150 ) $x = 150;
                              $damage = $x; 
                              $ok_dm = 0;
                          }
                          elseif($pokemonAttak == 378 OR $pokemonAttak == 462){
                              $ax = rand(1,120);                     
                              $x  = round($ax*(poke_info($pokemonEnemy,'hp_my')/poke_info($pokemon,'hp_max')));
                               if($x > 250 ) $x = 250;
                               if($x <= 0  ) $x = 1;
                              $power_atak = $x;
                              $ok_dm = 0;
                          }
                          elseif($pokemonAttak == 386){
                              $summa = (stat_struc($pokemonEnemy ,'speed','plus', $idBattle) + stat_struc($pokemonEnemy ,'defend','plus', $idBattle) + stat_struc($pokemonEnemy ,'attac','plus', $idBattle) + stat_struc($pokemonEnemy ,'spattac','plus', $idBattle) + stat_struc($pokemonEnemy ,'spdefend','plus', $idBattle));                                  
                              $x  = round(60 + (20*$summa));
                               if($x <= 0  ) $x = 1;
                              $power_atak = $x;
                              $ok_dm = 0;
                          }
                          elseif($pokemonAttak == 515){
                              $summa = poke_info($pokemon,'hp_my');                                  
                               if($summa <= 0  ) $summa = 1;
                              $damage = $summa;
                              $otdacha_hp = round(poke_info($pokemon,'hp_my')*2);
                              $ok_dm = 1;
                          }
                         }
                        if($ok_demag == 1){
                          if($ok_dm == 0){
                            $damage = round ((((((2*$lvl_my/5+2)*$atk*$power_atak/$def)/50)+2)*$stab*$tip_res*$randomnumber/100)*$crit_udar);
                            if((attac_battle($pokemonAttak,'cool_attak') > 0))
                               $damage = $damage*rand(attac_battle($pokemonAttak,'cool_attak'),attac_battle($pokemonAttak,'cool_attak2'));
                          }
                          if($damage == 0 AND $tip_sum > 0) $damage = 1;
                          $damage = round($damage); 
                          $dm = ($damage<=0?0:$damage);
                          $sput_udar = 0;
                          
                          if($status_my['resultatic'] != 6){
                                if ($tip_res == 0)    $dm_mess_e = ",   ";  
                            elseif ($tip_res == 2)    $dm_mess_e = ",  "; 
                            elseif ($tip_res == 4)    $dm_mess_e = ",   "; 
                            elseif ($tip_res == 0.5)  $dm_mess_e = ",    "; 
                            elseif ($tip_res == 0.25) $dm_mess_e = ",     ";
                            $rs_hp = round(poke_info($pokemonEnemy,'hp_my') - $dm);
                            $rs_hp = ($rs_hp<=0?0:$rs_hp);
                            if(($pokemonAttak == 12 OR $pokemonAttak == 32 OR $pokemonAttak == 90) && ($tip_res  >  0)) $rs_hp = 0;
                            if($pokemonAttak == 36)       $otdacha_hp = round($dm*0.25);
                            elseif($pokemonAttak == 66)   $otdacha_hp = round($dm*0.25);
                            elseif($pokemonAttak == 38)   $otdacha_hp = round($dm*(1/3));
                            elseif($pokemonAttak == 165)  $otdacha_hp = round($dm*0.5);
                            elseif($pokemonAttak == 344)  $otdacha_hp = round($dm*0.33);
                            elseif($pokemonAttak == 394)  $otdacha_hp = round($dm*(1/3));
                            elseif($pokemonAttak == 413)  $otdacha_hp = round($dm*(1/3));
                            elseif($pokemonAttak == 452)  $otdacha_hp = round($dm*(1/3));
                            elseif($pokemonAttak == 457)  $otdacha_hp = round($dm*0.5);
                            elseif($pokemonAttak == 528)  $otdacha_hp = round($dm*(1/4));
                            elseif($pokemonAttak == 543)  $otdacha_hp = round($dm*25);
                            
                            if($otdacha_hp > 0) $dm_mess_e .= " , #".poke_info($pokemon,'names')."      "; 
                              updateHp($pokemonEnemy, $rs_hp);
                            if($tip_res != 0) $t_ur = "  : ".$dm;
                            if($rs_hp <= 0) $hp_pr = ", #".poke_info($pokemonEnemy,'names')."     ";
                          }else{
                            $rs_hp = (round($dm/6));
                            $sput_udar = ($rs_hp<=0?0:$rs_hp);
                            $t_ur = "  ,    ";
                            $crt_mess = "";
                            $stabs = "";
                            $dm_mess_e = "";
                          } 
                        }
                        $hp_attacers = 0;
                        $hp_attacers_min = "0";
                          if($pokemonAttak == 71){
                             $hp_attacers = round($dm/2);
                             $stabs  .=  ", Absorb  HP: #".poke_info($pokemon,'names')."     ";
                          }
                          elseif($pokemonAttak == 72){
                             $hp_attacers = round($dm/2);
                             $stabs  .=  ", Mega Drain  HP: #".poke_info($pokemon,'names')."     ";
                          }
                          elseif($pokemonAttak == 72){
                             $hp_attacers = round($dm/2);
                             $stabs  .=  ", Mega Drain  HP: #".poke_info($pokemon,'names')."     ";
                          }
                          elseif($pokemonAttak == 141){
                             $hp_attacers = round($dm/2);
                             $stabs  .=  ", Leech Life  HP: #".poke_info($pokemon,'names')."     ";
                          }
                          elseif($pokemonAttak == 202){
                             $hp_attacers = round($dm/2);
                             $stabs  .=  ", Giga Drain  HP: #".poke_info($pokemon,'names')."     ";
                          }

                        if($rs_hp > 0){
                          if(attac_battle($pokemonAttak,'chans_dop') > 0){
                            $canse_dop = attac_battle($pokemonAttak,'chans_dop');
                              if(rand(1,100) <= $canse_dop){
                                $zap_a = first('SELECT * FROM attac_dop WHERE id_attc=%d',$pokemonAttak);
                                  if($zap_a['setting'] == 1){
                                    $zap_m = status_go($zap_a['dop_effc'],$pokemonEnemy,$idBattle);
                                  }
                                  if(($zap_a['setting'] == 2) OR ($zap_a['setting'] == 3)){                                                                                                                
                                      if($zap_a['setting'] == 2) $p = $pokemonEnemy;
                                      if($zap_a['setting'] == 3) $p = $pokemon;
                                      stats_insert($p,$idBattle,$zap_a['def'],$zap_a['atc'],$zap_a['sdef'],$zap_a['satc'],$zap_a['speed'],$zap_a['acc'],$zap_a['accuracy'],$zap_a['tip_s']); 
                                      if($zap_a['tip_s'] == "minus") $zap_m = ", : ";
                                        else  $zap_m = ", : #".poke_info($p,'names')." : ";
                                      if($zap_a['def']>0)        $zap_m .= " [] ";
                                      if($zap_a['atc']>0)        $zap_m .= " [] ";
                                      if($zap_a['acc']>0)        $zap_m .= " [] ";
                                      if($zap_a['sdef']>0)       $zap_m .= " [. ] ";
                                      if($zap_a['satc']>0)       $zap_m .= " [. ] ";
                                      if($zap_a['speed']>0)      $zap_m .= " [] ";
                                      if($zap_a['accuracy']>0)   $zap_m .= " [] ";
                                  }
                                $dop_attaci_udar = " ".$zap_m;
                              }    
                          } 
                        }
                          
                    }else{
                      if($tip_res > 0){ 
                        $attac_not = attac_battle($pokemonAttak,'atac_not');
                        if($attac_not > 0){ 
                          if($attac_not == 8 && ($pokemonEnemy_tipe_one == 'Grass' || $pokemonEnemy_tipe_two == 'Grass')) $t_ur = " ";
                           else
                            $t_ur = status_go(attac_battle($pokemonAttak,'atac_not'),$pokemonEnemy,$idBattle); 
                        } 
                      }else{ 
                        $t_ur = ",   "; 
                      }
                     
                      if(attac_battle($pokemonAttak,'atac_categori') == 5) $t_ur_s = hpmy_go(attac_battle($pokemonAttak,'atac_id'),$pokemonEnemy,$idBattle); else 
                      if(attac_battle($pokemonAttak,'atac_categori') == 6) $t_ur = stat_no_pok(attac_battle($pokemonAttak,'atac_id'),$pokemonEnemy,$idBattle);
                      if(attac_battle($pokemonAttak,'stati') == 1) $t_ur = stat_gous_pok($pokemonAttak,$pokemon,$idBattle);
                      if(attac_battle($pokemonAttak,'stati') == 2) $t_ur = stat_gous_pok($pokemonAttak,$pokemonEnemy ,$idBattle);
                      if(attac_battle($pokemonAttak,'attac_effecti') == 2){
                        if($pokemonAttak == 105){
                          $hp_attacers = poke_info($pokemon,'hp_max')*0.5;
                          $stabs  .=  ",  1/2   HP: #".poke_info($pokemon,'names')." ";
                        }
                        elseif($pokemonAttak == 135){
                          $hp_attacers = poke_info($pokemon,'hp_max')*0.5;
                          $stabs  .=  ",  1/2   HP: #".poke_info($pokemon,'names')." ";
                        }
                        elseif($pokemonAttak == 187){
                          $hp_attacers_min = poke_info($pokemon,'hp_my')*0.5;
                          $stabs  .=  "<br>[HP: #".poke_info($pokemon,'names')."   2 ] ";
                        } 
                        elseif($pokemonAttak == 208){
                          $hp_attacers = poke_info($pokemon,'hp_max')*0.5;
                          $stabs  .=  ",  1/2   HP: #".poke_info($pokemon,'names')." ";
                        }    
                        elseif($pokemonAttak == 505){
                          $hp_attacers = poke_info($pokemon,'hp_max')*0.5;
                          $stabs  .=  ",  1/2   HP: #".poke_info($pokemon,'names')." ";
                        }
                      }
                      //    
                      if(attac_battle($pokemonAttak,'chans_dop') > 0){
                        $canse_dop = attac_battle($pokemonAttak,'chans_dop');
                          if(rand(1,100) <= $canse_dop){
                              $zap_a = first('SELECT * FROM attac_dop WHERE id_attc=%d',$pokemonAttak);
                                if($zap_a['setting'] == 1){
                                  $zap_m = status_go($zap_a['dop_effc'],$pokemonEnemy,$idBattle);
                                }
                                if($zap_a['setting'] == 2){                                                                                                                
                                    stats_insert($pokemonEnemy,$idBattle,$zap_a['def'],$zap_a['atc'],$zap_a['sdef'],$zap_a['satc'],$zap_a['speed'],$zap_a['acc'],$zap_a['accuracy'],$zap_a['tip_s']); 
                                    if($zap_a['tip_s'] == "minus") $zap_m = ", : ";
                                      else  $zap_m = ", : ";
                                    if($zap_a['def']>0)        $zap_m .= " [] ";
                                    if($zap_a['atc']>0)        $zap_m .= " [] ";
                                    if($zap_a['acc']>0)        $zap_m .= " [] ";
                                    if($zap_a['sdef']>0)       $zap_m .= " [. ] ";
                                    if($zap_a['satc']>0)       $zap_m .= " [. ] ";
                                    if($zap_a['speed']>0)      $zap_m .= " [] ";
                                    if($zap_a['accuracy']>0)   $zap_m .= " [] ";
                                }
                             $dop_attaci_udar = $zap_m;
                          }    
                      }
                      //     **
                    }
                    $status_hp = 0;
                    $attac_hp = 0;       
                    if($status_my['hp_up'] != false){  
                      $poInf  = poke_info($pokemonEnemy,'hp_my');
                      if($status_my['id_status'] == 8 && $poInf > 0){
                         $poInf = $poInf+$status_my['hp_up'];
                         updateHp($pokemonEnemy, $poInf);
                         $status_my['mess_status'] = $status_my['mess_status'].'. [   HP: '.poke_info($pokemonEnemy,'names').']';
                      }
                      $status_hp = $status_my['hp_up'];
                    }
                    if(empty($t_ur_s)) $t_ur_s = array('hp_up'=>false);
                    if($t_ur_s['hp_up'] != false){
                        $attac_hp = $t_ur_s['hp_up'];
                        $t_ur = $t_ur_s['mess_status'];
                    }
                    if(empty($sput_udar)) $sput_udar = 0; 
                    if(empty($status_hp)) $status_hp = 0;
                    if(empty($hp_attacers_min)) $hp_attacers_min = 0; 
                    if(empty($otdacha_hp)) $otdacha_hp = 0;
                    if(empty($hp_attacers)) $hp_attacers = 0;
                    if(empty($hp_ot_uron)) $hp_ot_uron = 0;
                    if(empty($attac_hp)) $attac_hp = 0;
                    $rs_hp_my = round((poke_info($pokemon,'hp_my') - $status_hp - $sput_udar - $hp_attacers_min - $otdacha_hp) + $attac_hp + $hp_attacers + $hp_ot_uron);
                                         
                     if($pokemonAttak == 153 OR $pokemonAttak == 262 OR $pokemonAttak == 120) $rs_hp_my = 0;
                     if($pokemonAttak == 156){
                        $rs_hp_my = poke_info($pokemon,'hp_max');
                        $t_ur .= "   HP: #".poke_info($pokemon,'names').", ";
                        $t_ur .= status_go(777,$pokemon,$idBattle);
                     }
                     if($pokemonAttak == 18 || $pokemonAttak == 46){
                         if(infoBattle($idBattle,'batl_tip') == 'pvp'){  
                             $ok_demag = false;
                             if($pokemon == infoBattle($idBattle,'poke_1')) { $ompu = 'zamtru_2'; } else { $ompu = 'zamtru_1'; }
                             $uInf = poke_info($pokemonEnemy,'users');
                             $counPokeActiv  = first('SELECT COUNT(*) as countpok FROM pok_user WHERE users=%d AND active=1 AND hp_my>0',$uInf);
                             if($counPokeActiv['countpok'] > 1){
                              $users =  users($uInf,'login');    
                              update('battles',array($ompu=>1),'id='.(int)$idBattle);
                              $uronNoBase  = '   : '.$users.'   ';
                            }else{
                              $uronNoBase  = '  ';
                           }
                         }else{
                            $uronNoBase  = '  ';
                         }
                     }
                     if($pokemonAttak == 369 || $pokemonAttak == 521){
                       if(infoBattle($idBattle,'batl_tip') == 'pvp'){ 
                           if($pokemon == infoBattle($idBattle,'poke_1')) { $ompu = 'zamtru_1'; } else { $ompu = 'zamtru_2'; }
                           $uInf = poke_info($pokemon,'users');
                           $counPokeActiv  = first('SELECT COUNT(*) as countpok FROM pok_user WHERE users=%d AND active=1 AND hp_my>0',$uInf);
                           if($counPokeActiv['countpok'] > 1){
                            $users =  users($uInf,'login');    
                            update('battles',array($ompu=>1),'id='.(int)$idBattle);
                            $uronNoBase  = ',     : '.$users.'   ';
                          }else{
                            $uronNoBase  = false;
                          }
                       }else{
                            $uronNoBase  = '  ';
                       }
                     }
                    if($pokemonAttak == 174){
                        if($pokemonMy_tipe_one == 'Ghost' || $pokemonMy_tipe_two == 'Ghost'){
                         $uronNoBase = status_go(9,$pokemonEnemy,$idBattle).', #'.poke_info($pokemon,'names').'     '; 
                         $rs_hp_my = $rs_hp_my - poke_info($pokemon,'hp_max')*0.5;
                        }else{
                           stats_insert($pokemon,$idBattle,0,0,0,0,1,0,0,'minus');
                           stats_insert($pokemon,$idBattle,1,1,0,0,0,0,0,'plus');
                           $uronNoBase = '  #'.poke_info($pokemon,'names').' : [ +1], [ +1]  : [ -1] '; 
                        }
                    }
                    $rs_hp_my = ($rs_hp_my<=0?0:$rs_hp_my);
                    if($rs_hp_my > poke_info($pokemon,'hp_max')) $rs_hp_my = poke_info($pokemon,'hp_max');
                      updateHp($pokemon, $rs_hp_my);
                    if($rs_hp_my <= 0) $hp_no = ",     ";
                    if($x10 == 1) $x11 = $status_my['mess_status']; else $x11 = '';
                    if(empty($hp_pr)) $hp_pr = false;
                    if(empty($t_ur)) $t_ur = false;
                    if(empty($dm_mess_e)) $dm_mess_e = false;
                    if(empty($crt_mess)) $crt_mess = false;
                    if(empty($stabs)) $stabs = false;
                    if(empty($x11)) $x11 = false;
                    if(empty($dop_attaci_udar)) $dop_attaci_udar = false;
                    if(empty($hp_no)) $hp_no = false;
                    $uron_ms = $t_ur.$dm_mess_e.$uronNoBase.$crt_mess.$stabs.$x11.$dop_attaci_udar.$hp_no.$hp_pr;
                               
                }else{
                  $udarTrue = 'promax';
                }
            if(infoBattle($idBattle,'poke_1') == $pokemon && infoBattle($idBattle,'effect_go')  > 0) { $c_go = infoBattle($idBattle,'effect_go');   $d = infoBattle($idBattle,'effect');  update('battles',array('effect'=>0, 'effect_go'=>0),'id='.(int)$idBattle); }
            if(infoBattle($idBattle,'poke_2') == $pokemon && infoBattle($idBattle,'effect_go2') > 0) { $c_go = infoBattle($idBattle,'effect_go2');  $d = infoBattle($idBattle,'effect2'); update('battles',array('effect2'=>0, 'effect_go2'=>0),'id='.(int)$idBattle); }
            if(!empty($c_go) && $c_go == 1)  status_go($d,$pokemon,$idBattle);
          }else{
            $udarTrue = 'status';
          }
          }else{ 
             $udarTrue = 'item';
          }
          }else{
            $udarTrue = 'nohp';
          }  
            
            
            
            /**     **/
            $infoPokesBasenum = poke_info($pokemon,'basenum');
            $infoPokesName    = poke_info($pokemon,'names');
            $namePokeAtak     = attac_battle($pokemonAttak,'atac_name');
            $trxtPovtor       = "<img src='pok/anim/".$infoPokesBasenum.".".img($infoPokesBasenum)."' onClick=win1=window.open('/game.php?go=pokedex&id=".$infoPokesBasenum."','pokedex','width=550,height=550,scrollbars=yes');return true;> 
                                  #".$infoPokesName."  :  
                                    <a href=javascript: onClick=win1=window.open('/game.php?go=atk&id=".$pokemonAttak."','atk','width=726,height=260,scrollbars=yes');return true;>".$namePokeAtak."</a>
                                 , ";
            if($udarTrue == 'go'){
               $txtObr = $trxtPovtor.$uron_ms;
               if(attac_battle($pokemonAttak,'dop_effect') == "propusk") new_propusc($pokemon, $idBattle, $pokemonAttak);
            }
            elseif($udarTrue == 'promax'){
               $txtObr = $trxtPovtor."  ";
               $pp_up = "1";
               if(attac_battle($pokemonAttak,'dop_effect') == "propusk") new_propusc($pokemon, $idBattle, $pokemonAttak);
            }
            elseif($udarTrue == 'status'){
               $txtObr = $trxtPovtor."  ".$status_my['mess_status'];
            }
            elseif($udarTrue == 'nohp'){
               $txtObr = $trxtPovtor."     ";
            }
            elseif($udarTrue == 'item'){
               $txtObr = $txtObr;
            }else{
               $txtObr = $trxtPovtor.$txtObr;
            }
            $ms = "<font color='black'>
                    <b>
                      ".$txtObr.".
                    </b>
                   </font>";
            /** : *   * **/
          if($pp_up == 1 && ($pokemonAttak != 999 OR $pokemonAttak != 998 OR $pokemonAttak != 997)){
              $ibrId = obrid($pokemon);
              $pokemon    = $ibrId['id'];
              $tip        = $ibrId['tip'];
              if($tip == 'pvp'){
                     if($pokemonAttak == name_atc_pve(1,$pokemon,'atac_id')) $chis = 1;
                 elseif($pokemonAttak == name_atc_pve(2,$pokemon,'atac_id')) $chis = 2;
                 elseif($pokemonAttak == name_atc_pve(3,$pokemon,'atac_id')) $chis = 3;
                 elseif($pokemonAttak == name_atc_pve(4,$pokemon,'atac_id')) $chis = 4;
                 else $chis = 'not';
                 if($chis != 'not') update_pp($chis,$pokemon); 
                $chis = false;
              }
          }
      
       return $ms;
      }
    $battle = $battlId;
    if(attac_battle($attak,'priorety') > attac_battle($attak2,'priorety')){          
      $m  = demagePokes(infoBattle($battle,'poke_1'),$attak ,infoBattle($battle,'poke_2'),$attak2,$battle,1);
      $m2 = demagePokes(infoBattle($battle,'poke_2'),$attak2,infoBattle($battle,'poke_1'),$attak ,$battle,2);      
      $mess = " $m                 
               <br>            
                $m2";
	$xod = 1;
    }
    elseif(attac_battle($attak2,'priorety') > attac_battle($attak,'priorety')){
      $m2 = demagePokes(infoBattle($battle,'poke_2'),$attak2,infoBattle($battle,'poke_1'),$attak ,$battle,1); 
      $m  = demagePokes(infoBattle($battle,'poke_1'),$attak ,infoBattle($battle,'poke_2'),$attak2,$battle,2);  
      $mess = " $m2
               <br>
                $m";
	$xod = 2;
    }
    elseif(attac_battle($attak2,'priorety') == attac_battle($attak,'priorety')){
      
      if($pokemon_speed > $pokemon2_speed){
        $m  = demagePokes(infoBattle($battle,'poke_1'),$attak ,infoBattle($battle,'poke_2'),$attak2,$battle,1);
        $m2 = demagePokes(infoBattle($battle,'poke_2'),$attak2,infoBattle($battle,'poke_1'),$attak ,$battle,2); 
        $mess = " $m
                 <br>
                  $m2";  
	  $xod = 1;
      }else{
        $m2 = demagePokes(infoBattle($battle,'poke_2'),$attak2,infoBattle($battle,'poke_1'),$attak ,$battle,1); 
        $m  = demagePokes(infoBattle($battle,'poke_1'),$attak ,infoBattle($battle,'poke_2'),$attak2,$battle,2);
        $mess = " $m2
                 <br>
                  $m";
      $xod = 2;
      }
      
    }
	if($xod == 1){$xod2 = 2;}else{$xod2 = 1;}
  $ctg = attac_battle($attak,'atac_categori');
  $ctg2 = attac_battle($attak2,'atac_categori');
  $retJson = Array("ctg1" => $ctg,"ctg2" => $ctg2, "xod1" => $xod , "xod2" => $xod2, "tip1" => $types_atk1 , "tip2" => $types_atk2);
  require_once('Services_JSON.php');
  $oJson = new Services_JSON();
  $Data_new = $oJson->encode($retJson);
  
  update('battles',array('dates'=>$Data_new),'id='.(int)$battle);
  
  mess_logs($battle, $mess, my_battle($battle,'raund'));

    return $mess;  
}
}

?>
<?php


if (!function_exists('tip')) {
function tip($atkType, $defType){
    // Normalize to strings
    if ($atkType === null || $defType === null) return 1;
    $atk = trim((string)$atkType);
    $def = trim((string)$defType);

    // Treat common "None" / empty as neutral
    if ($def === '' || strtolower($def) === 'none') return 1;

    // Basic type chart (neutral default). Keys are lowercase English type names.
    $chart = array(
      'normal' => array('rock'=>0.5,'ghost'=>0,'steel'=>0.5),
      'fire'   => array('fire'=>0.5,'water'=>0.5,'grass'=>2,'ice'=>2,'bug'=>2,'rock'=>0.5,'dragon'=>0.5,'steel'=>2),
      'water'  => array('fire'=>2,'water'=>0.5,'grass'=>0.5,'ground'=>2,'rock'=>2,'dragon'=>0.5),
      'electric'=>array('water'=>2,'electric'=>0.5,'grass'=>0.5,'ground'=>0,'flying'=>2,'dragon'=>0.5),
      'grass'  => array('fire'=>0.5,'water'=>2,'grass'=>0.5,'poison'=>0.5,'ground'=>2,'flying'=>0.5,'bug'=>0.5,'rock'=>2,'dragon'=>0.5,'steel'=>0.5),
      'ice'    => array('water'=>0.5,'grass'=>2,'ground'=>2,'flying'=>2,'dragon'=>2,'steel'=>0.5,'fire'=>0.5,'ice'=>0.5),
      'fighting'=>array('normal'=>2,'ice'=>2,'rock'=>2,'dark'=>2,'steel'=>2,'poison'=>0.5,'flying'=>0.5,'psychic'=>0.5,'bug'=>0.5,'fairy'=>0.5,'ghost'=>0),
      'poison' => array('grass'=>2,'fairy'=>2,'poison'=>0.5,'ground'=>0.5,'rock'=>0.5,'ghost'=>0.5,'steel'=>0),
      'ground' => array('fire'=>2,'electric'=>2,'poison'=>2,'rock'=>2,'steel'=>2,'grass'=>0.5,'bug'=>0.5,'flying'=>0),
      'flying' => array('grass'=>2,'fighting'=>2,'bug'=>2,'electric'=>0.5,'rock'=>0.5,'steel'=>0.5),
      'psychic'=> array('fighting'=>2,'poison'=>2,'psychic'=>0.5,'steel'=>0.5,'dark'=>0),
      'bug'    => array('grass'=>2,'psychic'=>2,'dark'=>2,'fire'=>0.5,'fighting'=>0.5,'poison'=>0.5,'flying'=>0.5,'ghost'=>0.5,'steel'=>0.5,'fairy'=>0.5),
      'rock'   => array('fire'=>2,'ice'=>2,'flying'=>2,'bug'=>2,'fighting'=>0.5,'ground'=>0.5,'steel'=>0.5),
      'ghost'  => array('psychic'=>2,'ghost'=>2,'dark'=>0.5,'normal'=>0),
      'dragon' => array('dragon'=>2,'steel'=>0.5,'fairy'=>0),
      'dark'   => array('psychic'=>2,'ghost'=>2,'fighting'=>0.5,'dark'=>0.5,'fairy'=>0.5),
      'steel'  => array('rock'=>2,'ice'=>2,'fairy'=>2,'fire'=>0.5,'water'=>0.5,'electric'=>0.5,'steel'=>0.5),
      'fairy'  => array('fighting'=>2,'dragon'=>2,'dark'=>2,'fire'=>0.5,'poison'=>0.5,'steel'=>0.5),
    );

    $atk_l = strtolower($atk);
    $def_l = strtolower($def);

    if (isset($chart[$atk_l]) && isset($chart[$atk_l][$def_l])) {
        return $chart[$atk_l][$def_l];
    }
    // Neutral by default
    return 1;
}
}

?>