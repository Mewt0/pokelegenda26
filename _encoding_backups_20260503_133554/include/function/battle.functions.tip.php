<?php

if (!function_exists('tip')) {
function tip($atkType, $defType){
    if ($atkType === null || $defType === null) return 1;
    $atk = strtolower(trim((string)$atkType));
    $def = strtolower(trim((string)$defType));
    if ($def === '' || $def === 'none') return 1;
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
    if (isset($chart[$atk]) && isset($chart[$atk][$def])) return $chart[$atk][$def];
    return 1;
}
}
$time_my = time();
require_once __DIR__ . '/../config.php';
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
function obrid($txt){
  $id = substr($txt, 4);
  $tip = substr($txt, 0, 3);
  $subtxt = array('id'=>$id,'tip'=>$tip); 
 return $subtxt; 
}

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

function infoBattle($id,$zap){
  $q = first('SELECT '.$zap.' FROM battles WHERE id=%d',$id);
   if($q) $a = $q[$zap];
    else  $a = false;
 return $a;
}

function battle_dop($id,$zap,$b){
  $q = first('SELECT '.$zap.' FROM battle_dop WHERE battleid=%d AND pokeid="%s"',$b,$id);
  if($q) $a = $q[$zap];
    else $a = false;
 return $a;
}

function attac_battle($id,$zap){ 
  $q = first('SELECT '.$zap.' FROM attac_power WHERE atac_id=%d',$id);
   if($q) $a = $q[$zap];
    else  $a = false; 
 if($id == 999 && $zap == 'atac_name') $a = ' Р вЂ”Р В°Р СР ВµР Р…Р В° Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В° ';
 if($id == 998 && $zap == 'atac_name') $a = ' Р ВРЎРѓР С—Р С•Р В»РЎРЉР В·Р С•Р Р†Р В°Р Р…Р С‘Р Вµ Р С‘РЎвЂљР ВµР СР В° ';
 return $a;
}

function poke_tipe($id,$zap){
  $q = first('SELECT '.$zap.' FROM pokemon WHERE id=%d',$id);
   if($q) $a = $q[$zap];
    else  $a = false;
 return $a;
}

function stat_struc($idPokemon,$zap,$tip,$battl){
  $q = first('SELECT '.$zap.' FROM statpokemonbatle WHERE battleid=%d AND pokeid="%s" AND tip="%s"',$battl,$idPokemon,$tip);
   if($q) $a = $q[$zap];
    else  $a = 0;
 return $a;
}

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

function poimkaDik($hp,$hpMax,$znac){
   $CatchValue = round((( 3*$hpMax-2*$hp)*(rand(0,255) * $znac)/(3*$hp))*1);
    if($CatchValue <= 0) $CatchValue = 1; 
      $CatchValue2 = sqrt(sqrt(996711660/$CatchValue));
    if($CatchValue2 <= 0) {$CatchValue2 = 1;} 
      $Catch = round(sqrt(918510/$CatchValue2));
    if($CatchValue>$Catch) $a = true; else $a = false;
 return $a;
}

function tittleAttak($id){
   $tittle = array();
   $tittle[13]  = ' Р С‘ Р С—Р С•Р Т‘Р С–Р С•РЎвЂљР В°Р Р†Р В»Р С‘Р Р†Р В°Р ВµРЎвЂљРЎРѓРЎРЏ Р Р…Р В°Р Р…Р ВµРЎРѓРЎвЂљР С‘ Р В°РЎвЂљР В°Р С”РЎС“ ';
   $tittle[19]  = ' Р Р†Р В·Р В»Р ВµРЎвЂљР В°Р ВµРЎвЂљ Р Р†РЎвЂ№РЎРѓР С•Р С”Р С• Р Р† Р Р…Р ВµР В±Р С• Р С‘ Р С—Р С•Р Т‘Р С–Р С•РЎвЂљР В°Р Р†Р В»Р С‘Р Р†Р В°Р ВµРЎвЂљРЎРѓРЎРЏ Р Р…Р В°Р Р…Р ВµРЎРѓРЎвЂљР С‘ Р В°РЎвЂљР В°Р С”РЎС“ ';
   $tittle[76]  = ' Р Р…Р В°Р С”Р В°Р С—Р В»Р С‘Р Р†Р В°Р ВµРЎвЂљ РЎРѓР С•Р В»Р Р…Р ВµРЎвЂЎР Р…РЎС“РЎР‹ РЎРЊР Р…Р ВµРЎР‚Р С–Р С‘РЎР‹ Р С‘ Р С—Р С•Р Т‘Р С–Р С•РЎвЂљР В°Р Р†Р В»Р С‘Р Р†Р В°Р ВµРЎвЂљРЎРѓРЎРЏ Р Р…Р В°Р Р…Р ВµРЎРѓРЎвЂљР С‘ Р В°РЎвЂљР В°Р С”РЎС“ ';
   $tittle[91]  = ' Р В·Р В°РЎР‚РЎвЂ№Р Р†Р В°Р ВµРЎвЂљРЎРѓРЎРЏ Р С—Р С•Р Т‘ Р В·Р ВµР СР В»РЎР‹ Р С‘ Р С—Р С•Р Т‘Р С–Р С•РЎвЂљР В°Р Р†Р В»Р С‘Р Р†Р В°Р ВµРЎвЂљРЎРѓРЎРЏ Р Р…Р В°Р Р…Р ВµРЎРѓРЎвЂљР С‘ Р В°РЎвЂљР В°Р С”РЎС“ ';
   $tittle[143] = ' Р С‘ Р С—Р С•Р Т‘Р С–Р С•РЎвЂљР В°Р Р†Р В»Р С‘Р Р†Р В°Р ВµРЎвЂљРЎРѓРЎРЏ Р Р…Р В°Р Р…Р ВµРЎРѓРЎвЂљР С‘ Р В°РЎвЂљР В°Р С”РЎС“ ';
   $tittle[248] = ' Р С‘ Р Р…Р В°РЎвЂЎР С‘Р Р…Р В°Р ВµРЎвЂљ Р С—Р С•Р Т‘Р С–Р С•РЎвЂљР В°Р Р†Р В»Р С‘Р Р†Р В°РЎвЂљРЎРЉРЎРѓРЎРЏ Р Р…Р В°Р Р…Р ВµРЎРѓРЎвЂљР С‘ Р В°РЎвЂљР В°Р С”РЎС“ ';
   $tittle[291] = ' Р Р…РЎвЂ№РЎР‚РЎРЏР ВµРЎвЂљ Р С—Р С•Р Т‘ Р Р†Р С•Р Т‘РЎС“ Р С‘ Р С—Р С•Р Т‘Р С–Р С•РЎвЂљР В°Р Р†Р В»Р С‘Р Р†Р В°Р ВµРЎвЂљРЎРѓРЎРЏ Р Р…Р В°Р Р…Р ВµРЎРѓРЎвЂљР С‘ Р В°РЎвЂљР В°Р С”РЎС“ ';
   $tittle[340] = ' Р С‘ Р Р…Р В°РЎвЂЎР С‘Р Р…Р В°Р ВµРЎвЂљ Р С—Р С•Р Т‘Р С–Р С•РЎвЂљР В°Р Р†Р В»Р С‘Р Р†Р В°РЎвЂљРЎРЉРЎРѓРЎРЏ Р Р…Р В°Р Р…Р ВµРЎРѓРЎвЂљР С‘ Р В°РЎвЂљР В°Р С”РЎС“ ';
   $tittle[353] = ' Р С‘ Р Р…Р В°РЎвЂЎР С‘Р Р…Р В°Р ВµРЎвЂљ Р С—Р С•Р Т‘Р С–Р С•РЎвЂљР В°Р Р†Р В»Р С‘Р Р†Р В°РЎвЂљРЎРЉРЎРѓРЎРЏ Р Р…Р В°Р Р…Р ВµРЎРѓРЎвЂљР С‘ Р В°РЎвЂљР В°Р С”РЎС“ ';
   $tittle[467] = ' РЎРѓРЎвЂљР В°Р Р…Р С•Р Р†Р С‘РЎвЂљРЎРѓРЎРЏ Р Р…Р ВµР Р†Р С‘Р Т‘Р С‘Р СРЎвЂ№Р С Р С‘ Р Р…Р В°РЎвЂЎР С‘Р Р…Р В°Р ВµРЎвЂљ Р С—Р С•Р Т‘Р С–Р С•РЎвЂљР В°Р Р†Р В»Р С‘Р Р†Р В°РЎвЂљРЎРЉРЎРѓРЎРЏ Р Р…Р В°Р Р…Р ВµРЎРѓРЎвЂљР С‘ Р В°РЎвЂљР В°Р С”РЎС“ ';
   $tittle[553] = ' Р Р…Р В°Р С”Р В°Р С—Р В»Р С‘Р Р†Р В°Р ВµРЎвЂљ РЎРЊР Р…Р ВµРЎР‚Р С–Р С‘РЎР‹ Р С‘ Р С—Р С•Р Т‘Р С–Р С•РЎвЂљР В°Р Р†Р В»Р С‘Р Р†Р В°Р ВµРЎвЂљРЎРѓРЎРЏ Р Р…Р В°Р Р…Р ВµРЎРѓРЎвЂљР С‘ Р В°РЎвЂљР В°Р С”РЎС“ ';
   $tittle[554] = ' Р Р…Р В°Р С”Р В°Р С—Р В»Р С‘Р Р†Р В°Р ВµРЎвЂљ РЎРЊР Р…Р ВµРЎР‚Р С–Р С‘РЎР‹ Р С‘ Р С—Р С•Р Т‘Р С–Р С•РЎвЂљР В°Р Р†Р В»Р С‘Р Р†Р В°Р ВµРЎвЂљРЎРѓРЎРЏ Р Р…Р В°Р Р…Р ВµРЎРѓРЎвЂљР С‘ Р В°РЎвЂљР В°Р С”РЎС“ ';
 return $tittle[$id];
}

function mess_logs($battle_id,$mess,$raund) { 
  insert('battle_log',array('battle_id'=>$battle_id, 'demage'=>$mess, 'raund'=>$raund));
}
  
function hpmy_go($id,$poke,$battle)
{
  switch ($id):  
    case 208:             
      $hp_my_pl = round(poke_info($poke,'hp_max')*0.5); 
      $dm_mess = " Milk Drink Р Р†Р С•РЎРѓРЎРѓРЎвЂљР В°Р Р…Р В°Р Р†Р В»Р С‘Р Р†Р В°Р ВµРЎвЂљ Р С—Р С•Р В»Р С•Р Р†Р С‘Р Р…РЎС“ Р СР В°Р С”РЎРѓР С‘Р СР В°Р В»РЎРЉР Р…Р С•Р в„– HP #".poke_info($poke,'names'); 
    break;
    
    case 234:
            if(infoBattle($battle,'id_pogodi') == 1) { $h = "0.5";   $h_tittle = "1/2"; }
        elseif(infoBattle($battle,'id_pogodi') == 2) { $h = "0.66";  $h_tittle = "2/3"; }
        else { $h = "0.25";  $h_tittle = "1/4"; }
      $hp_my_pl = round(poke_info($poke,'hp_max')*$h);  
      $dm_mess = " Morning Sun Р Р†Р С•РЎРѓРЎРѓРЎвЂљР В°Р Р…Р В°Р Р†Р В»Р С‘Р Р†Р В°Р ВµРЎвЂљ HP #".poke_info($poke,'names')."($h_tittle Р С•РЎвЂљ Р СР В°Р С”РЎРѓР С‘Р СРЎС“Р СР В°)"; 
    break;
    
    case 235:     
            if(infoBattle($battle,'id_pogodi') == 1) { $h = "0.5";   $h_tittle = "1/2"; }
        elseif(infoBattle($battle,'id_pogodi') == 2) { $h = "0.66";  $h_tittle = "2/3"; }
        else { $h = "0.25";  $h_tittle = "1/4"; }
      $hp_my_pl = round(poke_info($poke,'hp_max')*$h);  
      $dm_mess = " Synthesis Р Р†Р С•РЎРѓРЎРѓРЎвЂљР В°Р Р…Р В°Р Р†Р В»Р С‘Р Р†Р В°Р ВµРЎвЂљ HP #".poke_info($poke,'names')."($h_tittle Р С•РЎвЂљ Р СР В°Р С”РЎРѓР С‘Р СРЎС“Р СР В°)"; 
    break;
    
    case 236:
            if(infoBattle($battle,'id_pogodi') == 1) { $h = "0.5";   $h_tittle = "1/2"; }
        elseif(infoBattle($battle,'id_pogodi') == 2) { $h = "0.66";  $h_tittle = "2/3"; }
        else { $h = "0.25";  $h_tittle = "1/4"; }
      $hp_my_pl = round(poke_info($poke,'hp_max')*$h);  
      $dm_mess = " Moonlight Р Р†Р С•РЎРѓРЎРѓРЎвЂљР В°Р Р…Р В°Р Р†Р В»Р С‘Р Р†Р В°Р ВµРЎвЂљ HP #".poke_info($poke,'names')."($h_tittle Р С•РЎвЂљ Р СР В°Р С”РЎРѓР С‘Р СРЎС“Р СР В°)"; 
    break;
    
    case 303:
      $hp_my_pl = round(poke_info($poke,'hp_max')*0.5);   
      $dm_mess = " Slack Off Р Р†Р С•РЎРѓРЎРѓРЎвЂљР В°Р Р…Р В°Р Р†Р В»Р С‘Р Р†Р В°Р ВµРЎвЂљ Р С—Р С•Р В»Р С•Р Р†Р С‘Р Р…РЎС“ Р СР В°Р С”РЎРѓР С‘Р СР В°Р В»РЎРЉР Р…Р С•Р в„– HP #".poke_info($poke,'names'); 
    break;
  endswitch;
    
    $res_status = array('mess_status' => $dm_mess, 'hp_up'=>$hp_my_pl);
return $res_status; 
}

function items_go_pokes($poke_xod,$battle){
      $message = false;
      $p1 = infoBattle($battle,'poke_1');
      $p2 = infoBattle($battle,'poke_2');
      if($p1 == $poke_xod) $c = "to_it";
      if($p2 == $poke_xod) $c = "to_it2";
      $users =  poke_info($poke_xod,'users');
      $item = infoBattle($battle,$c);
     if($item == 3 && infoBattle($battle,'batl_tip') == 'pve' && poke_info($p2,'poimka') == 1){     
         $pokeball = poimkaDik(poke_info($p2,'hp_my'),poke_info($p2,'hp_max'),'1');
         if($pokeball == false){
            $r = rand(1,2);
            if($r == 1) $message = users($users,'login').", Р С‘РЎРѓР С—Р С•Р В»РЎРЉР В·РЎС“Р ВµРЎвЂљ: Р СџР С•Р С”Р ВµР В±Р С•Р В», Р Р…Р С• Р С—РЎР‚Р С•Р СР В°РЎвЂ¦Р С‘Р Р†Р В°Р ВµРЎвЂљРЎРѓРЎРЏ ";
             else $message = users($users,'login').", Р С‘РЎРѓР С—Р С•Р В»РЎРЉР В·РЎС“Р ВµРЎвЂљ: Р СџР С•Р С”Р ВµР В±Р С•Р В», Р Р…Р С• #".poke_info($p2,'names')." Р Р…Р Вµ РЎвЂ¦Р С•РЎвЂЎР ВµРЎвЂљ Р В·Р В°Р В»Р В°Р В·Р С‘РЎвЂљРЎРЉ Р Р† Р Р…Р ВµР С–Р С• ";
          }else{
            $countPoke = first('SELECT COUNT(*) as countpok FROM pok_user WHERE users=%d AND active=1',$_SESSION['id']);
            if($countPoke['countpok']>=6) $active = 0; else $active = 1; 
              $p2 = obrid($p2);
              pokemonDicPluse($p2['id'],$battle,$active,$_SESSION['id']);
            $a = time()+30;
            query('UPDATE users SET pve=0, rang_a=rang_a+1, atack_poke=%d WHERE id=%d',$a,$_SESSION['id']);
            $_SESSION['klicers'] = $_SESSION['klicers']+1;
            logdel($battle);
            minus_item(1,$item,$users);
            die('<script>alert("Р СџР С•Р С”Р ВµР СР С•Р Р… РЎС“РЎРѓР С—Р ВµРЎв‚¬Р Р…Р С• Р С—Р С•Р в„–Р СР В°Р Р…");location.href=\'/game.php?go=char\';</script>');
          }
     }
   if ($item == 15) {                  
    delete('bttle_status', 'pokeid="' . mysql_real_escape_string($poke_xod, $connect) . '" AND namber_st=2');
    $message = users($users, 'login') . ", Р С‘РЎРѓР С—Р С•Р В»РЎРЉР В·РЎС“Р ВµРЎвЂљ: Р В­Р Р…Р ВµРЎР‚Р С–Р ВµРЎвЂљР С‘Р С”, Р Р…Р В°: #" . poke_info($poke_xod, 'names');
}
    if($item == 91){
       $h = poke_info($poke_xod,'hp_my') + 20; 
       if($h > poke_info($poke_xod,'hp_max')) $h = poke_info($poke_xod,'hp_max'); 
       updateHp($poke_xod, $h);
       $message = users($users,'login').", Р С‘РЎРѓР С—Р С•Р В»РЎРЉР В·РЎС“Р ВµРЎвЂљ: Р вЂ”Р ВµР В»РЎРЉР Вµ, Р Р…Р В°: #".poke_info($poke_xod,'names');
    }
if($item == 93){
    $h = poke_info($poke_xod, 'hp_max');
    $a = "";

    // Р вЂР ВµР В·Р С—Р ВµРЎвЂЎР Р…Р Вµ Р ВµР С”РЎР‚Р В°Р Р…РЎС“Р Р†Р В°Р Р…Р Р…РЎРЏ Р В±Р ВµР В· Р С—РЎвЂ“Р Т‘Р С”Р В»РЎР‹РЎвЂЎР ВµР Р…Р Р…РЎРЏ Р Т‘Р С• Р вЂР вЂќ
    $escaped_pokeid = addslashes($poke_xod);

    if(rand(1,100) <= 50) {
        delete('bttle_status', 'pokeid="' . $escaped_pokeid . '"');
        $a = ", Р В° РЎвЂљР В°Р С” Р В¶Р Вµ РЎРѓР Р…Р С‘Р СР В°Р ВµРЎвЂљ РЎРѓРЎвЂљР В°РЎвЂљРЎС“РЎРѓ Р В±Р С•Р В»Р ВµР В·Р Р…Р С‘";
    }

    updateHp($poke_xod, $h);
    $message = users($users, 'login') . ", Р С‘РЎРѓР С—Р С•Р В»РЎРЉР В·РЎС“Р ВµРЎвЂљ: Р вЂ”Р ВµР В»РЎРЉР Вµ Р Р†Р С•РЎРѓРЎРѓРЎвЂљР В°Р Р…Р С•Р Р†Р В»Р ВµР Р…Р С‘РЎРЏ, Р Р…Р В°: #" . poke_info($poke_xod, 'names') . " Р С‘ Р С—Р С•Р В»Р С•Р Р…Р С•РЎРѓРЎвЂљРЎРЉРЎР‹ Р Р†Р С•РЎРѓРЎРѓРЎвЂљР В°Р Р…Р В°Р Р†Р В»Р С‘Р Р†Р В°Р ВµРЎвЂљ Р ВµР С–Р С• Р В·Р Т‘Р С•РЎР‚Р С•Р Р†РЎРЉР Вµ" . $a;
}

// Р вЂ”Р В°Р С–Р В»РЎС“РЎв‚¬Р С”Р В°: Р Р†РЎР‚Р ВµР СР ВµР Р…Р Р…Р С• Р Р…Р Вµ РЎС“Р Т‘Р В°Р В»РЎРЏР ВµР С Р С—РЎР‚Р ВµР Т‘Р СР ВµРЎвЂљ
// if($message != false) minus_item(1, $item, $users);

return $message;

}
function attaks_stat($p,$a,$battl){    
  if(attac_battle($a,'atac_categori') == 1) $atk = poke_info($p,'atk')*((2+stat_struc($p,'attac','plus',$battl))/(2+stat_struc($p,'attac','minus',$battl)));    
  if(attac_battle($a,'atac_categori') == 2) $atk = poke_info($p,'satk')*((2+stat_struc($p,'spattac','plus',$battl))/(2+stat_struc($p,'spattac','minus',$battl))); 
  if(attac_battle($a,'atac_categori') == 3) $atk = poke_info($p,'atk')*((2+stat_struc($p,'attac','plus',$battl))/(2+stat_struc($p,'attac','minus',$battl)));   
return $atk;
}
function def_stat($p,$a,$battl){
  if(attac_battle($a,'atac_categori') == 1) $def = poke_info($p,'def')*((2+stat_struc($p,'defend','plus',$battl))/(2+stat_struc($p,'defend','minus',$battl)));    
  if(attac_battle($a,'atac_categori') == 2) $def = poke_info($p,'sdef')*((2+stat_struc($p,'spdefend','plus',$battl))/(2+stat_struc($p,'spdefend','minus',$battl))); 
  if(attac_battle($a,'atac_categori') == 3) $def = poke_info($p,'def')*((2+stat_struc($p,'defend','plus',$battl))/(2+stat_struc($p,'defend','minus',$battl)));   
return $def;
}

function stat_no_pok($id,$p,$battle) {
  switch ($id):  
    case 39:
      $def = "1"; 
      $atc = "0"; 
      $sdef = "0"; 
      $satc = "0";
      $speed = "0";
      $tip_s = "minus";
      $mess = ' [Р вЂ”Р В°РЎвЂ°Р С‘РЎвЂљР В° #'.poke_info($p,'names').' Р С—Р С•Р Р…Р С‘Р В¶Р ВµР Р…Р В° Р Р…Р В° 1 Р С—РЎС“Р Р…Р С”РЎвЂљ]';
    break;    
    case 43:
      $def = "1"; 
      $atc = "0"; 
      $sdef = "0"; 
      $satc = "0";
      $speed = "0";
      $tip_s = "minus";
      $mess = ' [Р вЂ”Р В°РЎвЂ°Р С‘РЎвЂљР В° #'.poke_info($p,'names').' Р С—Р С•Р Р…Р С‘Р В¶Р ВµР Р…Р В° Р Р…Р В° 1 Р С—РЎС“Р Р…Р С”РЎвЂљ]';
    break;    
    case 45:
      $def = "0"; 
      $atc = "1"; 
      $sdef = "0"; 
      $satc = "0";
      $speed = "0";
      $tip_s = "minus";
      $mess = ' [Р С’РЎвЂљР В°Р С”Р В° #'.poke_info($p,'names').' Р С—Р С•Р Р…Р С‘Р В¶Р ВµР Р…Р В° Р Р…Р В° 1 Р С—РЎС“Р Р…Р С”РЎвЂљ]';
    break;    
    case 103:
      $def = "2"; 
      $atc = "0"; 
      $sdef = "0"; 
      $satc = "0";
      $speed = "0";
      $tip_s = "minus";
      $mess = ' [Р вЂ”Р В°РЎвЂ°Р С‘РЎвЂљР В° #'.poke_info($p,'names').' Р С—Р С•Р Р…Р С‘Р В¶Р ВµР Р…Р В° Р Р…Р В° 2 Р С—РЎС“Р Р…Р С”РЎвЂљР В°]';
    break;
    case 297:
      $def = "0"; 
      $atc = "2"; 
      $sdef = "0"; 
      $satc = "0";
      $speed = "0";
      $tip_s = "minus";
      $mess = ' [Р С’РЎвЂљР В°Р С”Р В° #'.poke_info($p,'names').' Р С—Р С•Р Р…Р С‘Р В¶Р ВµР Р…Р В° Р Р…Р В° 2 Р С—РЎС“Р Р…Р С”РЎвЂљР В°]';
    break;    
    case 313:
      $def = "0"; 
      $atc = "0"; 
      $sdef = "2"; 
      $satc = "0";
      $speed = "0";
      $tip_s = "minus";
      $mess = ' [Р РЋР С—Р ВµРЎвЂ . Р вЂ”Р В°РЎвЂ°Р С‘РЎвЂљР В° #'.poke_info($p,'names').' Р С—Р С•Р Р…Р С‘Р В¶Р ВµР Р…Р В° Р Р…Р В° 2 Р С—РЎС“Р Р…Р С”РЎвЂљР В°]';
    break;
    case 319:
      $def = "0"; 
      $atc = "0"; 
      $sdef = "2"; 
      $satc = "0";
      $speed = "0";
      $tip_s = "minus";
      $mess = ' [Р РЋР С—Р ВµРЎвЂ . Р вЂ”Р В°РЎвЂ°Р С‘РЎвЂљР В° #'.poke_info($p,'names').' Р С—Р С•Р Р…Р С‘Р В¶Р ВµР Р…Р В° Р Р…Р В° 2 Р С—РЎС“Р Р…Р С”РЎвЂљР В°]';
    break;
  endswitch;
  $yes_s = first('SELECT * FROM statpokemonbatle WHERE battleid=%d AND pokeid="%s" AND tip="%s"',$battle,$p,$tip_s);
    if(!$yes_s) insert('statpokemonbatle',array('battleid'=>$battle,'pokeid'=>$p,'defend' =>$def,'attac'=>$atc,'speed'=>$speed,'spattac'=>$satc,'spdefend'=>$sdef,'tip'=>$tip_s));
    else {                           
      if($yes_s['defend'] <= 6)    $d = $yes_s['defend'] + $def;
        if($d > 6) $d = 6;   
      if($yes_s['attac'] <= 6)    $a = $yes_s['attac'] + $atc;
        if($a > 6) $a = 6;
      if($yes_s['speed'] <= 6)  $s = $yes_s['speed'] + $speed;
        if($s > 6) $s = 6;
      if($yes_s['spattac'] <= 6)   $sa = $yes_s['spattac'] + $satc;
        if($sa > 6) $sa = 6;
      if($yes_s['spdefend'] <= 6)   $sd = $yes_s['spdefend'] + $sdef;
        if($sd > 6) $sd = 6;     
      update('statpokemonbatle',array(
      'tip'=>$tip_s,
      'defend'=>$d,
      'attac'=>$a,
      'speed'=>$s,
      'spattac'=>$sa,
      'spdefend'=>$sd
      ),'battleid='.(int)$battle.' AND pokeid="'.mysql_escape_string($p).'" AND tip="'.mysql_escape_string($tip_s).'"'); 
    }
 return $mess;
}

function stats_insert($p,$battle,$def,$atc,$sdef,$satc,$speed,$acc,$accuracy,$tip_s){
  $yes_s = first('SELECT * FROM statpokemonbatle WHERE battleid=%d AND pokeid="%s" AND tip="%s"',$battle,$p,$tip_s);
    if(!$yes_s) 
        insert('statpokemonbatle',array('battleid'=>$battle,'pokeid'=>$p,'defend' =>$def,'attac'=>$atc,'speed'=>$speed,'spattac'=>$satc,'spdefend'=>$sdef, 'acc'=>$acc, 'accuracy' =>$accuracy, 'tip'=>$tip_s));
    else {                           
      if($yes_s['defend']    <=  6) $d = $yes_s['defend'] + $def;
      if($d > 6) $d = 6;   
      if($yes_s['attac']    <=  6) $a = $yes_s['attac'] + $atc;
      if($a > 6) $a = 6;
      if($yes_s['speed']  <=  6) $s = $yes_s['speed'] + $speed;
      if($s > 6) $s = 6;
      if($yes_s['spattac']   <=  6) $sa = $yes_s['spattac'] + $satc;
      if($sa > 6) $sa = 6;
      if($yes_s['spdefend']   <=  6) $sd = $yes_s['spdefend'] + $sdef;
      if($sd > 6) $sd = 6;
      if($yes_s['acc']    <=  6) $ac = $yes_s['acc'] + $acc;
      if($ac > 6) $ac = 6;
      if($yes_s['accuracy']<= 6) $ar = $yes_s['accuracy'] + $accuracy;
      if($ar > 6) $ar = 6;
      update('statpokemonbatle',array(
      'tip'=>$tip_s,
      'defend'=>$d,
      'attac'=>$a,
      'speed'=>$s,
      'spattac'=>$sa,
      'spdefend'=>$sd,
      'acc'=>$ac,
      'accuracy'=>$ar
      ),'battleid='.(int)$battle.' AND pokeid="'.mysql_escape_string($p).'" AND tip="'.mysql_escape_string($tip_s).'"'); 
    }
}

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
        if($def > 0)        $at .= " [Р вЂ”Р В°РЎвЂ°Р С‘РЎвЂљР В° Р Р…Р В° +".$def."] ";
        if($atc > 0)        $at .= " [Р С’РЎвЂљР В°Р С”Р В° Р Р…Р В° +".$atc."] ";
        if($acc > 0)        $at .= " [Р вЂєР С•Р Р†Р С”Р С•РЎРѓРЎвЂљРЎРЉ Р Р…Р В° +".$acc."] ";
        if($sdef > 0)       $at .= " [Р РЋР С—Р ВµРЎвЂ . Р вЂ”Р В°РЎвЂ°Р С‘РЎвЂљР В° Р Р…Р В° +".$sdef."] ";
        if($satc > 0)       $at .= " [Р РЋР С—Р ВµРЎвЂ . Р С’РЎвЂљР В°Р С”Р В° Р Р…Р В° +".$satc."] ";
        if($speed > 0)      $at .= " [Р РЋР С”Р С•РЎР‚Р С•РЎРѓРЎвЂљРЎРЉ Р Р…Р В° +".$speed."] ";
        if($accuracy > 0)   $at .= " [Р СћР С•РЎвЂЎР Р…Р С•РЎРѓРЎвЂљРЎРЉ Р Р…Р В° +".$accuracy."] ";
      $bt = "<br><font color='gold'>Р Р€ #".poke_info($p,'names')." РЎС“Р Р†Р ВµР В»Р С‘РЎвЂЎР ВµР Р…Р С•: </font><br>".$at;
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
        if($def > 0)        $at2 .= " [Р вЂ”Р В°РЎвЂ°Р С‘РЎвЂљР В° Р Р…Р В° -".$def."] ";
        if($atc > 0)        $at2 .= " [Р С’РЎвЂљР В°Р С”Р В° Р Р…Р В° -".$atc."] ";
        if($acc > 0)        $at2 .= " [Р вЂєР С•Р Р†Р С”Р С•РЎРѓРЎвЂљРЎРЉ Р Р…Р В° -".$acc."] ";
        if($sdef > 0)       $at2 .= " [Р РЋР С—Р ВµРЎвЂ . Р вЂ”Р В°РЎвЂ°Р С‘РЎвЂљР В° Р Р…Р В° -".$sdef."] ";
        if($satc > 0)       $at2 .= " [Р РЋР С—Р ВµРЎвЂ . Р С’РЎвЂљР В°Р С”Р В° Р Р…Р В° -".$satc."] ";
        if($speed > 0)      $at2 .= " [Р РЋР С”Р С•РЎР‚Р С•РЎРѓРЎвЂљРЎРЉ Р Р…Р В° -".$speed."] ";
        if($accuracy > 0)   $at2 .= " [Р СћР С•РЎвЂЎР Р…Р С•РЎРѓРЎвЂљРЎРЉ Р Р…Р В° -".$accuracy."] ";
      $bt .= " <br><font color='gold'>Р Р€ #".poke_info($p,'names')." РЎС“Р СР ВµР Р…РЎРЉРЎв‚¬Р ВµР Р…Р С•: </font><br>".$at2;
     stats_insert($p,$battle,$def,$atc,$sdef,$satc,$speed,$acc,$accuracy,$tip_s);
    }
  $mess = ". ".$bt;
 return $mess;
}

function status_my($p,$b){
  $status_yes = first('SELECT bsp.id_sts, bsp.namber_st FROM bttle_status bsp INNER JOIN status sts ON bsp.namber_st=sts.id_status WHERE bsp.buttleid=%d AND bsp.pokeid="%s"',$b,$p); 
  $hp_up = false;
  $namber_st_yes = false;
    if($status_yes){
    $namber_st_yes = $status_yes['namber_st'];
      switch ($namber_st_yes):  
        case 1:
          $hp_up = round(poke_info($p,'hp_max')/8);        
          $mess = ', РЎРЏР Т‘ Р С•РЎвЂљР Р…Р С‘Р СР В°Р ВµРЎвЂљ Р В·Р Т‘Р С•РЎР‚Р С•Р Р†РЎРЉР Вµ РЎС“ #'.poke_info($p,'names');
          $resultatic = 1;
          $ok = 1; 
        break;
        case 2:
          $hp_up = false;
          $resultatic = 2;
          $mess = "Р РЋР С—Р С‘РЎвЂљ";
          $ok = 0; 
        break;
        case 3:     
          $hp_up = round(poke_info($p,'hp_max')/8);
          $mess = ', Р С•Р С–Р С•Р Р…РЎРЉ Р С•РЎРѓР В»Р В°Р В±Р В»РЎРЏР ВµРЎвЂљ #'.poke_info($p,'names');
          $resultatic = 1;
          $ok = 1; 
        break;
        case 4:
          $hp_up = false;
          $resultatic = 3;
          $mess = "Р вЂ”Р В°Р СР С•РЎР‚Р С•Р В¶Р ВµР Р…";
          $ok = 0;
        break;
        case 5:
          $hp_up = false;
            if(rand(1,100) <= 25){
              $resultatic = 4;
              $mess = "Р СџР В°РЎР‚Р В°Р В»Р С‘Р В·Р С•Р Р†Р В°Р Р…";
            }else{
              $resultatic = 1;
              $mess = "";     
            }
          $ok = 0;
        break;
        case 6:
          $hp_up = false;
          $resultatic = 5;
          $mess = "Р СњР В°Р С—РЎС“Р С–Р В°Р Р…";
          $ok = 0;
        break;
        case 7:
          $hp_up = false;
            if(rand(1,100) <= 50){
              $resultatic = 6;
              $mess = " Р РЋР С—РЎС“РЎвЂљР В°Р Р…";
            }else{ 
              $resultatic = 1;
              $mess = "";      
            }
          $ok = 0;
        break;
        case 8:
          $hp_up = round(poke_info($p,'hp_max')/8);        
          $mess = ', РЎР‚Р В°РЎРѓРЎвЂљР ВµР Р…Р С‘РЎРЏ-Р С—Р С‘РЎРЏР Р†Р С”Р С‘ Р С•РЎвЂљР Р…Р С‘Р СР В°РЎР‹РЎвЂљ Р В·Р Т‘Р С•РЎР‚Р С•Р Р†РЎРЉР Вµ РЎС“ #'.poke_info($p,'names');
          $resultatic = 1;
          $ok = 1; 
        break;
        case 9:
          $hp_up = round(poke_info($p,'hp_max')/4);        
          $mess = ', #'.poke_info($p,'names').' РЎвЂљР ВµРЎР‚РЎРЏР ВµРЎвЂљ РЎвЂЎР В°РЎРѓРЎвЂљРЎРЉ РЎРѓР Р†Р С•Р ВµР С–Р С• Р В·Р Т‘Р С•РЎР‚Р С•Р Р†РЎРЉРЎРЏ, РЎвЂљР В°Р С” Р С”Р В°Р С” Р С•Р Р… Р С—РЎР‚Р С•Р С”Р В»РЎРЏРЎвЂљ';
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

function status_go($namber_status,$pokemon,$battle){
  $tip_pokes = 1;
  $ok_stat = 1;
  $st_stat = false;
  $update = false;
    switch ($namber_status):  
      case 1:
        $ran       = ''.mt_rand(9999,99999);
        $random    = infoBattle($battle,'raund') + $ran; 
        $number_st = 1;
        $mess3     = ' [#'.poke_info($pokemon,'names').' <b>Р С›РЎвЂљРЎР‚Р В°Р Р†Р В»Р ВµР Р…</b>]'; 
      break;
      case 2:
        $ran       = ''.mt_rand(2,5);
        $random    = infoBattle($battle,'raund') + $ran; 
        $number_st = 2;
        $mess3     = ' [#'.poke_info($pokemon,'names').' <b>Р Р€РЎРѓРЎвЂ№Р С—Р В»Р ВµР Р…</b>]';
      break;
      case 3:
        $ran       = ''.mt_rand(9999,99999);
        $random    = infoBattle($battle,'raund') + $ran;  
        $number_st = 3;
        $mess3     = ', [#'.poke_info($pokemon,'names').' Р Р† Р С•Р С–Р Р…Р Вµ, РЎвЂљР В°Р С” Р В¶Р Вµ Р ВµР С–Р С• Р В°РЎвЂљР В°Р С”Р В° Р С—Р С•Р Р…Р С‘Р В¶Р ВµР Р…Р В° Р Р…Р В° 2 Р С—РЎС“Р Р…Р С”РЎвЂљР В°]';
        $st_stat   = 1; 
        $atc       = "2"; 
        $tip_s     = "minus";
      break;
      case 4:
        $ran       = ''.mt_rand(9999,99999);
        $random    = infoBattle($battle,'raund') + $ran;  
        $number_st = 4;
        $mess3     = ' [#'.poke_info($pokemon,'names').' <b>Р вЂ”Р В°Р СР С•РЎР‚Р С•Р В¶Р ВµР Р…</b>]';
      break;
      case 5:
        $ran       = ''.mt_rand(9999,99999);
        $random    = infoBattle($battle,'raund') + $ran;  
        $number_st = 5;
        $st_stat   = 1;  
        $speed     = "4"; 
        $tip_s     = "minus";
        $mess3     = ' [#'.poke_info($pokemon,'names').' <b>Р СџР В°РЎР‚Р В°Р В»Р С‘Р В·Р С•Р Р†Р В°Р Р…</b>, РЎвЂљР В°Р С” Р В¶Р Вµ Р ВµР С–Р С• РЎРѓР С”Р С•РЎР‚Р С•РЎРѓРЎвЂљРЎРЉ Р С—Р С•Р Р…Р С‘Р В¶Р ВµР Р…Р В° Р Р…Р В° 4 Р С—РЎС“Р Р…Р С”РЎвЂљР В°]';
      break;
      case 6:
        $ran       = 1;
        $random    = infoBattle($battle,'raund') + $ran; 
        $number_st = 6;
        $mess3     = ' [#'.poke_info($pokemon,'names').' <b>Р СњР В°Р С—РЎС“Р С–Р В°Р Р…</b>]'; 
      break;
      case 7:
        $ran       = ''.mt_rand(9999,99999);
        $random    = infoBattle($battle,'raund') + $ran; 
        $number_st = 7;
        $mess3     = ' [#'.poke_info($pokemon,'names').' <b>Р РЋР С—РЎС“РЎвЂљР В°Р Р…</b>]';
      break;
      case 8:
        $ran       = ''.mt_rand(9999,99999);
        $random    = infoBattle($battle,'raund') + $ran; 
        $number_st = 8;
        $mess3     = ' [Р СњР В° #'.poke_info($pokemon,'names').' <b>Р СњР В°Р В»Р С•Р В¶Р ВµР Р…РЎвЂ№ РЎР‚Р В°РЎРѓРЎвЂљР ВµР Р…Р С‘РЎРЏ-Р С—Р С‘РЎРЏР Р†Р С”Р С‘</b>]';
      break;
      case 9:
        $ran       = ''.mt_rand(9999,99999);
        $random    = infoBattle($battle,'raund') + $ran; 
        $number_st = 9;
        $mess3     = ' [Р СњР В° #'.poke_info($pokemon,'names').' <b>Р СџРЎР‚Р С•Р С”Р В»РЎРЏРЎвЂљ</b>]';
        $update    = true;
      break;
      case 777:
        $ran       = ''.mt_rand(2,2);
        $random    = infoBattle($battle,'raund') + $ran; 
        $number_st = 2;
        $mess3     = ' [#'.poke_info($pokemon,'names').' <b>Р Р€РЎРѓРЎвЂ№Р С—Р В»Р ВµР Р…</b>]';
        $update    = true;
      break;
    endswitch;
  $status_yes = first('SELECT id_sts FROM bttle_status WHERE buttleid=%d AND pokeid="%s"',$battle,$pokemon);  
    if(!$status_yes) 
      insert('bttle_status',array('namber_st'=>$number_st, 'buttleid'=>$battle, 'pokeid'=>$pokemon, 'raund_end'=>$random, 'tip_poke'=>$tip_pokes));
    elseif($status_yes AND $update == true)
      update('bttle_status',array('namber_st'=>$number_st, 'raund_end'=>$random),'pokeid="'.mysql_escape_string($pokemon).'" AND buttleid='.(int)$battle);
    else{ 
      $mess3 = " [CР С•РЎРѓРЎвЂљР С•РЎРЏР Р…Р С‘Р Вµ: #".poke_info($pokemon,'names')." Р Р…Р Вµ Р СР С•Р В¶Р ВµРЎвЂљ Р В±РЎвЂ№РЎвЂљРЎРЉ Р С‘Р В·Р СР ВµР Р…Р ВµР Р…Р С•]";  $ok_stat = "0";
    }
    if(isset($st_stat) && ($st_stat == 1) && ($ok_stat == 1)) {
        if (empty($def))      $def      = 0; 
        if (empty($atc))      $atc      = 0;
        if (empty($acc))      $acc      = 0;  
        if (empty($satc))     $satc     = 0;
        if (empty($sdef))     $sdef     = 0;
        if (empty($speed))    $speed    = 0;
        if (empty($accuracy)) $accuracy = 0;
     stats_insert($pokemon,$battle,$def,$atc,$sdef,$satc,$speed,$acc,$accuracy,$tip_s); 
    }
return $mess3;
}

function status_isset($idst,$pokes,$battle){
  $status_yes = first('SELECT id_sts FROM bttle_status WHERE buttleid=%d AND pokeid="%s" AND namber_st=%d',$battle,$pokes,$idst);
    if($status_yes['id_sts'] == true) $ret = true; 
      else $ret = false;
 return $ret;
}

function propusc_xod($p,$battle){
  $mess = false;
  $a_dop = false;
  $xodov = '0';
    $z = first('SELECT propusk,at_dop,mess FROM battle_dop WHERE battleid=%d AND pokeid="%s"',$battle,$p);
  if($z){
      if(infoBattle($battle,'poke_1') == $p) $c = "1";
      if(infoBattle($battle,'poke_2') == $p) $c = "2";
      if($c != false){
        $xodov = $z['propusk'] - 1;
        if(($xodov == 0) OR ($xodov < 0) OR ($xodov == false)){
          if($z['at_dop'] != 0){
            $a_dop = $z['at_dop'];
            delete('battle_dop','battleid='.(int)$battle.' AND pokeid="'.mysql_escape_string($p).'"');
            update('battles',array('attac_'.$c=>$z['at_dop']),'id='.(int)$battle); 
          }else{
            delete('battle_dop','battleid='.(int)$battle.' AND pokeid="'.mysql_escape_string($p).'"');
          }
        }else{
          update('battle_dop',array('propusk'=>$xodov),'battleid='.(int)$battle.' AND pokeid="'.mysql_escape_string($p).'"');
        }
       $mess = $z['mess'];
      }
  }else{
    $mess = false;
    $a_dop = false;
    $xodov = false;
  }
 $resultat = array('hod' => $xodov, 'mess' => $mess, 'attac'=>$a_dop); 
return $resultat;
}

function new_propusc($p, $battle, $atk){
  $mes_a = false;
  $id_atk = $atk;
  if(attac_battle($atk,'dop_effect') == "propusk"){
    if ($id_atk == 63 OR $id_atk == 307 OR $id_atk == 338 OR $id_atk == 416){                                                     
      $mes_a = " Р Р…Р С• Р Р†Р С•РЎРѓРЎРѓРЎвЂљР В°Р Р…Р В°Р Р†Р В»Р С‘Р Р†Р В°Р ВµРЎвЂљ РЎРѓР С‘Р В»РЎвЂ№ Р С—Р С•РЎРѓР В»Р Вµ: <a href=javascript: onClick=win1=window.open('/game.php?go=atk&id=".$id_atk."','atk','width=726,height=260,scrollbars=yes');return true;>".attac_battle($atk,'atac_name')."</a>";
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
            /*** Р вЂњР ВµР Р…Р ВµРЎР‚Р В°РЎвЂ Р С‘РЎРЏ/Р С›Р В±РЎР‚Р В°Р В±Р С•РЎвЂљР С”Р В° - РЎС“Р Т‘Р В°РЎР‚Р В° ***/
              if(poke_info($pokemon,'basenum') > 493) $img  = "png";    
              if(attac_battle($pokemonAttak,'attac_effecti') == 1) attacers_on($pokemonAttak,$pokemon,$idBattle);  
              if($status_my['resultatic'] == 1 || $status_my['resultatic'] == 6){
               if($status_my['resultatic'] == 1){
                if(battle_dop($pokemonEnemy,'vulnerability',$idBattle) == 1 && attac_battle($pokemonAttak,'dop_effect') != 2){ 
                  $txtObr = " Р Р…Р С• #".poke_info($pokemonEnemy,'names')." Р Р…Р Вµ Р Т‘Р С•РЎРѓРЎвЂљРЎС“Р С—Р ВµР Р… Р Т‘Р В»РЎРЏ Р В°РЎвЂљР В°Р С” ";
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
                  $txtObr = " Р Р…Р С• #".poke_info($pokemonEnemy,'names')." Р В·Р В°РЎвЂ°Р С‘РЎвЂљР С‘Р В» РЎРѓР ВµР В±РЎРЏ Р С•РЎвЂљ Р В°РЎвЂљР В°Р С”Р С‘ ";
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
                  $txtObr = " Р Р…Р С• Р С•Р Р… ".$status_my['mess_status'];
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
                $txtObr = "<b>".users(poke_info($cb[$c],'users'),'login')."</b>, Р В·Р В°Р СР ВµР Р…РЎРЏР ВµРЎвЂљ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В° Р Р…Р В°: #".poke_info($cb[$c],'names')."";
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
                  if($rs_hp_my <= 0) $txtObr .= ", Р С•Р Р… Р В±Р С•Р В»РЎРЉРЎв‚¬Р Вµ Р Р…Р Вµ Р СР С•Р В¶Р ВµРЎвЂљ Р С—РЎР‚Р С•Р Т‘Р С•Р В»Р В¶Р В°РЎвЂљРЎРЉ Р В±Р С•Р в„–!";
                }
              }
            if($ok == 1){        
              if($pokemonAttak == 389  AND ((attac_battle($pokemonEnemyAttak,'atac_categori') == 2) OR (attac_battle($pokemonEnemyAttak,'atac_categori') == 1))){  
              
              }else{
                if($pokemonAttak == 389){
                  $txtObr = " Р СџРЎР‚Р С•Р Р†Р В°Р В»";
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
                    $txtObr = " Р СџРЎР‚Р С•Р Р†Р В°Р В»";
                    $udarTrue = 'other';  
                    $ok = false;
                    $pp_up = 0;
                  }
                }
                if($pokemonAttak == 264 AND $numbers == 2){
                    $a10 = status_my($pokemonEnemy,$idBattle);
                    if($a10['resultatic'] == 1 && attac_battle($pokemonEnemyAttak,'atac_categori') != 3){
                      $txtObr = " Р СџРЎР‚Р С•Р Р†Р В°Р В»";
                      $udarTrue = 'other';  
                      $ok = false;
                      $pp_up = 0;
                    }
                }
            }
            if($goPokemon == false){
            if($ok == true){ 
             /** Р Р€Р В·Р Р…Р В°Р ВµР С РЎвЂљР С‘Р С— Р В°РЎвЂљР В°Р С”Р С‘, РЎРѓРЎР‚Р В°Р Р†Р Р…Р С‘Р Р†Р В°Р С РЎРѓ РЎвЂљР С‘Р С—Р С•Р С Р С—Р С•Р С”Р ВµР С•Р СР Р…Р С•Р Р†, Р Р†РЎвЂ№Р Т‘Р В°Р ВµР С РЎР‚Р ВµР В·РЎС“Р В»РЎРЉРЎвЂљР В°РЎвЂљ. **/
              $tip_atttac            = attac_battle($pokemonAttak,'atac_tip'); 
              $pokemonEnemy_tipe_one = poke_tipe(poke_info($pokemonEnemy,'basenum'),'Element');
              $pokemonEnemy_tipe_two = poke_tipe(poke_info($pokemonEnemy,'basenum'),'SubElement');
              $pokemonMy_tipe_one = poke_tipe(poke_info($pokemon,'basenum'),'Element');
              $pokemonMy_tipe_two = poke_tipe(poke_info($pokemon,'basenum'),'SubElement');
              $typesAttac['1']       = tip($tip_atttac,$pokemonEnemy_tipe_one);
                if($pokemonEnemy_tipe_two == "None" OR $pokemonEnemy_tipe_two == false) $typesAttac['2'] = 1; 
                  else $typesAttac['2'] = tip($tip_atttac,$pokemonEnemy_tipe_two); 
              $tip_res = $typesAttac['1']*$typesAttac['2']; // Р Р€Р СР Р…Р С•Р Р…Р С•Р В¶Р В°Р ВµР С РЎвЂљР С‘Р С—РЎвЂ№.
             /** -END- **/
            }
            if($status_my['resultatic'] == 1 OR $status_my['resultatic'] == 6){
              $lupe = round(attac_battle($pokemonAttak,'atac_accuracy'));        
                if($lupe == 0 OR $lupe == false) $lupe_res = 9999;
                  else{
                    $pl_user    = stat_struc($pokemon,'accuracy','plus',  $idBattle); // Р СћР С•РЎвЂЎР Р…Р С•РЎРѓРЎвЂљРЎРЉ
                    $min_user   = stat_struc($pokemon,'accuracy','minus', $idBattle); // Р СћР С•РЎвЂЎР Р…Р С•РЎРѓРЎвЂљРЎРЉ
                    $pl_nouser  = stat_struc($pokemonEnemy, 'acc','plus', $idBattle); // Р вЂєР С•Р Р†Р С•Р С”РЎРѓРЎвЂљРЎРЉ
                    $min_nouser = stat_struc($pokemonEnemy, 'acc','minus',$idBattle); // Р вЂєР С•Р Р†Р С•Р С”РЎРѓРЎвЂљРЎРЉ
                    $item_my = "0";
                    $item_no = "0";
                    $lupe_res = floor($lupe*((3+$pl_user+$min_nouser)/(3+$min_user+$pl_nouser))*(1+$item_my-$item_no));
                  }
                if(rand(1,100) <= $lupe_res){
                  $pp_up = 1;
                    if(attac_battle($pokemonAttak,'atac_categori') == 1 OR attac_battle($pokemonAttak,'atac_categori') == 2){
                      $crit = 1*attac_battle($pokemonAttak,'critic'); /** Р С™РЎР‚Р С‘РЎвЂљ РЎС“Р Т‘РЎР‚Р В°РЎР‚ **/
                      $randomnumber = ''.mt_rand(85,100); /** Р В Р В°Р Р…Р Т‘Р С•Р СР Р…РЎвЂ№Р в„– Р Р…Р С•Р СР ВµРЎР‚ **/
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
                            $crt_mess  = ", <font color=gold><i>Р С™Р В Р ВР СћР ВР В§Р вЂўР РЋР С™Р ВР в„ў Р Р€Р вЂќР С’Р В !</i></font>"; 
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
                                  $dm_mess_e = ", Р Р†Р С•РЎРѓРЎРѓРЎвЂљР В°Р Р…Р В°Р Р†Р В»Р С‘Р Р†Р В°Р ВµРЎвЂљ Р СњР В  #".poke_info($pokemon,'names')." Р Р…Р В° 80 Р ВµР Т‘Р ВµР Р…Р С‘РЎвЂ  "; 
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
                                if ($tip_res == 0)    $dm_mess_e = ", Р Р…Р С• РЎРЊРЎвЂћРЎвЂћР ВµР С”РЎвЂљР В° Р Р…Р ВµРЎвЂљ";  
                            elseif ($tip_res == 2)    $dm_mess_e = ", Р В­РЎвЂћРЎвЂћР ВµР С”РЎвЂљР С‘Р Р†Р Р…Р В°РЎРЏ Р В°РЎвЂљР В°Р С”Р В°"; 
                            elseif ($tip_res == 4)    $dm_mess_e = ", Р С›РЎвЂЎР ВµР Р…РЎРЉ РЎРЊРЎвЂћРЎвЂћР ВµР С”РЎвЂљР С‘Р Р†Р Р…Р В°РЎРЏ Р В°РЎвЂљР В°Р С”Р В°"; 
                            elseif ($tip_res == 0.5)  $dm_mess_e = ", Р РЋР В»Р В°Р В±РЎвЂ№Р в„– РЎРЊРЎвЂћРЎвЂћР ВµР С”РЎвЂљ Р С•РЎвЂљ Р В°РЎвЂљР В°Р С”Р С‘"; 
                            elseif ($tip_res == 0.25) $dm_mess_e = ", Р С›РЎвЂЎР ВµР Р…РЎРЉ РЎРѓР В»Р В°Р В±РЎвЂ№Р в„– РЎРЊРЎвЂћРЎвЂћР ВµР С”РЎвЂљ Р С•РЎвЂљ Р В°РЎвЂљР В°Р С”Р С‘";
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
                            
                            if($otdacha_hp > 0) $dm_mess_e .= " , #".poke_info($pokemon,'names')." Р С—Р С•Р В»РЎС“РЎвЂЎР С‘Р В» Р С•РЎвЂљР Т‘Р В°РЎвЂЎРЎС“ Р С•РЎвЂљ Р Р…Р В°Р Р…Р ВµРЎРѓР ВµР Р…Р Р…Р С•Р С–Р С• РЎС“РЎР‚Р С•Р Р…Р В° "; 
                              updateHp($pokemonEnemy, $rs_hp);
                            if($tip_res != 0) $t_ur = "Р С‘ Р Р…Р В°Р Р…Р С•РЎРѓР С‘РЎвЂљ РЎС“РЎР‚Р С•Р Р…: ".$dm;
                            if($rs_hp <= 0) $hp_pr = ", #".poke_info($pokemonEnemy,'names')." Р В±Р С•Р В»РЎРЉРЎв‚¬Р Вµ Р Р…Р Вµ Р СР С•Р В¶Р ВµРЎвЂљ Р С—РЎР‚Р С•Р Т‘Р С•Р В»Р В¶Р В°РЎвЂљРЎРЉ Р В±Р С‘РЎвЂљР Р†РЎС“";
                          }else{
                            $rs_hp = (round($dm/6));
                            $sput_udar = ($rs_hp<=0?0:$rs_hp);
                            $t_ur = " РЎС“Р Т‘Р В°РЎР‚РЎРЏР ВµРЎвЂљ РЎРѓР ВµР В±РЎРЏ, РЎвЂљР В°Р С” Р С”Р В°Р С” Р С•Р Р… ";
                            $crt_mess = "";
                            $stabs = "";
                            $dm_mess_e = "";
                          } 
                        }
                        $hp_attacers = 0;
                        $hp_attacers_min = "0";
                          if($pokemonAttak == 71){
                             $hp_attacers = round($dm/2);
                             $stabs  .=  ", Absorb Р Р†Р С•РЎРѓРЎРѓРЎвЂљР В°Р Р…Р В°Р Р†Р В»Р С‘Р Р†Р В°Р ВµРЎвЂљ HP: #".poke_info($pokemon,'names')." Р Р…Р В° Р С—Р С•Р В»Р С•Р Р†Р С‘Р Р…РЎС“ Р С•РЎвЂљ Р С—Р С•РЎвЂљР ВµРЎР‚РЎРЏР Р…Р Р…Р С•Р С–Р С• ";
                          }
                          elseif($pokemonAttak == 72){
                             $hp_attacers = round($dm/2);
                             $stabs  .=  ", Mega Drain Р Р†Р С•РЎРѓРЎРѓРЎвЂљР В°Р Р…Р В°Р Р†Р В»Р С‘Р Р†Р В°Р ВµРЎвЂљ HP: #".poke_info($pokemon,'names')." Р Р…Р В° Р С—Р С•Р В»Р С•Р Р†Р С‘Р Р…РЎС“ Р С•РЎвЂљ Р С—Р С•РЎвЂљР ВµРЎР‚РЎРЏР Р…Р Р…Р С•Р С–Р С• ";
                          }
                          elseif($pokemonAttak == 72){
                             $hp_attacers = round($dm/2);
                             $stabs  .=  ", Mega Drain Р Р†Р С•РЎРѓРЎРѓРЎвЂљР В°Р Р…Р В°Р Р†Р В»Р С‘Р Р†Р В°Р ВµРЎвЂљ HP: #".poke_info($pokemon,'names')." Р Р…Р В° Р С—Р С•Р В»Р С•Р Р†Р С‘Р Р…РЎС“ Р С•РЎвЂљ Р С—Р С•РЎвЂљР ВµРЎР‚РЎРЏР Р…Р Р…Р С•Р С–Р С• ";
                          }
                          elseif($pokemonAttak == 141){
                             $hp_attacers = round($dm/2);
                             $stabs  .=  ", Leech Life Р Р†Р С•РЎРѓРЎРѓРЎвЂљР В°Р Р…Р В°Р Р†Р В»Р С‘Р Р†Р В°Р ВµРЎвЂљ HP: #".poke_info($pokemon,'names')." Р Р…Р В° Р С—Р С•Р В»Р С•Р Р†Р С‘Р Р…РЎС“ Р С•РЎвЂљ Р С—Р С•РЎвЂљР ВµРЎР‚РЎРЏР Р…Р Р…Р С•Р С–Р С• ";
                          }
                          elseif($pokemonAttak == 202){
                             $hp_attacers = round($dm/2);
                             $stabs  .=  ", Giga Drain Р Р†Р С•РЎРѓРЎРѓРЎвЂљР В°Р Р…Р В°Р Р†Р В»Р С‘Р Р†Р В°Р ВµРЎвЂљ HP: #".poke_info($pokemon,'names')." Р Р…Р В° Р С—Р С•Р В»Р С•Р Р†Р С‘Р Р…РЎС“ Р С•РЎвЂљ Р С—Р С•РЎвЂљР ВµРЎР‚РЎРЏР Р…Р Р…Р С•Р С–Р С• ";
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
                                      if($zap_a['tip_s'] == "minus") $zap_m = ", РЎС“Р СР ВµР Р…РЎРЉРЎв‚¬Р ВµР Р…Р С•: ";
                                        else  $zap_m = ", РЎС“: #".poke_info($p,'names')." РЎС“Р Р†Р ВµР В»Р С‘РЎвЂЎР ВµР Р…Р С•: ";
                                      if($zap_a['def']>0)        $zap_m .= " [Р вЂ”Р В°РЎвЂ°Р С‘РЎвЂљР В°] ";
                                      if($zap_a['atc']>0)        $zap_m .= " [Р С’РЎвЂљР В°Р С”Р В°] ";
                                      if($zap_a['acc']>0)        $zap_m .= " [Р вЂєР С•Р Р†Р С”Р С•РЎРѓРЎвЂљРЎРЉ] ";
                                      if($zap_a['sdef']>0)       $zap_m .= " [Р РЋР С—Р ВµРЎвЂ . Р вЂ”Р В°РЎвЂ°РЎвЂљР В°] ";
                                      if($zap_a['satc']>0)       $zap_m .= " [Р РЋР С—Р ВµРЎвЂ . Р С’РЎвЂљР В°Р С”Р В°] ";
                                      if($zap_a['speed']>0)      $zap_m .= " [Р РЋР С”Р С•РЎР‚Р С•РЎРѓРЎвЂљРЎРЉ] ";
                                      if($zap_a['accuracy']>0)   $zap_m .= " [Р СћР С•РЎвЂЎР Р…Р С•РЎРѓРЎвЂљРЎРЉ] ";
                                  }
                                $dop_attaci_udar = " ".$zap_m;
                              }    
                          } 
                        }
                          
                    }else{
                      if($tip_res > 0){ 
                        $attac_not = attac_battle($pokemonAttak,'atac_not');
                        if($attac_not > 0){ 
                          if($attac_not == 8 && ($pokemonEnemy_tipe_one == 'Grass' || $pokemonEnemy_tipe_two == 'Grass')) $t_ur = " Р С—РЎР‚Р С•Р Р†Р В°Р В»";
                           else
                            $t_ur = status_go(attac_battle($pokemonAttak,'atac_not'),$pokemonEnemy,$idBattle); 
                        } 
                      }else{ 
                        $t_ur = ", Р Р…Р С• РЎРЊРЎвЂћРЎвЂћР ВµР С”РЎвЂљР В° Р Р…Р ВµРЎвЂљ"; 
                      }
                     
                      if(attac_battle($pokemonAttak,'atac_categori') == 5) $t_ur_s = hpmy_go(attac_battle($pokemonAttak,'atac_id'),$pokemonEnemy,$idBattle); else 
                      if(attac_battle($pokemonAttak,'atac_categori') == 6) $t_ur = stat_no_pok(attac_battle($pokemonAttak,'atac_id'),$pokemonEnemy,$idBattle);
                      if(attac_battle($pokemonAttak,'stati') == 1) $t_ur = stat_gous_pok($pokemonAttak,$pokemon,$idBattle);
                      if(attac_battle($pokemonAttak,'stati') == 2) $t_ur = stat_gous_pok($pokemonAttak,$pokemonEnemy ,$idBattle);
                      if(attac_battle($pokemonAttak,'attac_effecti') == 2){
                        if($pokemonAttak == 105){
                          $hp_attacers = poke_info($pokemon,'hp_max')*0.5;
                          $stabs  .=  ", Р Р†Р С•РЎРѓРЎРѓРЎвЂљР В°Р Р…Р В°Р Р†Р В»Р С‘Р Р†Р В°Р ВµРЎвЂљ 1/2 Р С•РЎвЂљ Р СР В°РЎРѓР С‘Р СР В°Р В»РЎРЉР Р…Р С•Р С–Р С• HP: #".poke_info($pokemon,'names')." ";
                        }
                        elseif($pokemonAttak == 135){
                          $hp_attacers = poke_info($pokemon,'hp_max')*0.5;
                          $stabs  .=  ", Р Р†Р С•РЎРѓРЎРѓРЎвЂљР В°Р Р…Р В°Р Р†Р В»Р С‘Р Р†Р В°Р ВµРЎвЂљ 1/2 Р С•РЎвЂљ Р СР В°РЎРѓР С‘Р СР В°Р В»РЎРЉР Р…Р С•Р С–Р С• HP: #".poke_info($pokemon,'names')." ";
                        }
                        elseif($pokemonAttak == 187){
                          $hp_attacers_min = poke_info($pokemon,'hp_my')*0.5;
                          $stabs  .=  "<br>[HP: #".poke_info($pokemon,'names')." РЎС“Р СР ВµР Р…РЎРЉРЎв‚¬Р ВµР Р…Р С• Р Р† 2 РЎР‚Р В°Р В·Р В°] ";
                        } 
                        elseif($pokemonAttak == 208){
                          $hp_attacers = poke_info($pokemon,'hp_max')*0.5;
                          $stabs  .=  ", Р Р†Р С•РЎРѓРЎРѓРЎвЂљР В°Р Р…Р В°Р Р†Р В»Р С‘Р Р†Р В°Р ВµРЎвЂљ 1/2 Р С•РЎвЂљ Р СР В°РЎРѓР С‘Р СР В°Р В»РЎРЉР Р…Р С•Р С–Р С• HP: #".poke_info($pokemon,'names')." ";
                        }    
                        elseif($pokemonAttak == 505){
                          $hp_attacers = poke_info($pokemon,'hp_max')*0.5;
                          $stabs  .=  ", Р Р†Р С•РЎРѓРЎРѓРЎвЂљР В°Р Р…Р В°Р Р†Р В»Р С‘Р Р†Р В°Р ВµРЎвЂљ 1/2 Р С•РЎвЂљ Р СР В°РЎРѓР С‘Р СР В°Р В»РЎРЉР Р…Р С•Р С–Р С• HP: #".poke_info($pokemon,'names')." ";
                        }
                      }
                      // Р вЂќР С•Р С—Р С•Р В»Р Р…Р ВµР Р…Р С‘Р Вµ РЎС“ РЎРѓРЎвЂљР В°РЎвЂљРЎС“РЎРѓР Р…Р С•Р в„– Р В°РЎвЂљР В°Р С”Р С‘
// Р вЂќР С•Р С—Р С•Р В»Р Р…Р ВµР Р…Р С‘Р Вµ РЎС“ РЎРѓРЎвЂљР В°РЎвЂљРЎС“РЎРѓР Р…Р С•Р в„– Р В°РЎвЂљР В°Р С”Р С‘
if (attac_battle($pokemonAttak, 'chans_dop') > 0) {
    $canse_dop = attac_battle($pokemonAttak, 'chans_dop');
    $zap_m = ''; // < Р Т‘Р С•Р В±Р В°Р Р†Р В»Р ВµР Р…Р С•

    if (rand(1, 100) <= $canse_dop) {
        $zap_a = first('SELECT * FROM attac_dop WHERE id_attc=%d', $pokemonAttak);

        if ($zap_a) {
            if ($zap_a['setting'] == 1) {
                $zap_m = status_go($zap_a['dop_effc'], $pokemonEnemy, $idBattle);
            }

            if ($zap_a['setting'] == 2) {
                stats_insert($pokemonEnemy, $idBattle,
                    $zap_a['def'], $zap_a['atc'], $zap_a['sdef'],
                    $zap_a['satc'], $zap_a['speed'], $zap_a['acc'],
                    $zap_a['accuracy'], $zap_a['tip_s']
                );

                $zap_m = ($zap_a['tip_s'] == "minus") ? ", РЎС“Р СР ВµР Р…РЎРЉРЎв‚¬Р ВµР Р…Р С•: " : ", РЎС“Р Р†Р ВµР В»Р С‘РЎвЂЎР ВµР Р…Р С•: ";

                if ($zap_a['def'] > 0)      $zap_m .= " [Р вЂ”Р В°РЎвЂ°Р С‘РЎвЂљР В°] ";
                if ($zap_a['atc'] > 0)      $zap_m .= " [Р С’РЎвЂљР В°Р С”Р В°] ";
                if ($zap_a['acc'] > 0)      $zap_m .= " [Р вЂєР С•Р Р†Р С”Р С•РЎРѓРЎвЂљРЎРЉ] ";
                if ($zap_a['sdef'] > 0)     $zap_m .= " [Р РЋР С—Р ВµРЎвЂ . Р вЂ”Р В°РЎвЂ°Р С‘РЎвЂљР В°] ";
                if ($zap_a['satc'] > 0)     $zap_m .= " [Р РЋР С—Р ВµРЎвЂ . Р С’РЎвЂљР В°Р С”Р В°] ";
                if ($zap_a['speed'] > 0)    $zap_m .= " [Р РЋР С”Р С•РЎР‚Р С•РЎРѓРЎвЂљРЎРЉ] ";
                if ($zap_a['accuracy'] > 0) $zap_m .= " [Р СћР С•РЎвЂЎР Р…Р С•РЎРѓРЎвЂљРЎРЉ] ";
            }
        }

        $dop_attaci_udar = $zap_m;
    }
}
// Р вЂќР С•Р С—Р С•Р В»Р Р…Р ВµР Р…Р С‘Р Вµ РЎС“ РЎРѓРЎвЂљР В°РЎвЂљРЎС“РЎРѓР Р…Р С•Р в„– Р В°РЎвЂљР В°Р С”Р С‘ *Р С”Р С•Р Р…Р ВµРЎвЂ *
                      // Р вЂќР С•Р С—Р С•Р В»Р Р…Р ВµР Р…Р С‘Р Вµ РЎС“ РЎРѓРЎвЂљР В°РЎвЂљРЎС“РЎРѓР Р…Р С•Р в„– Р В°РЎвЂљР В°Р С”Р С‘ *Р С”Р С•Р Р…Р ВµРЎвЂ *
                    }
                    $status_hp = 0;
                    $attac_hp = 0;       
                    if($status_my['hp_up'] != false){  
                      $poInf  = poke_info($pokemonEnemy,'hp_my');
                      if($status_my['id_status'] == 8 && $poInf > 0){
                         $poInf = $poInf+$status_my['hp_up'];
                         updateHp($pokemonEnemy, $poInf);
                         $status_my['mess_status'] = $status_my['mess_status'].'. [Р В Р В°РЎРѓРЎвЂљР ВµР Р…Р С‘РЎРЏ Р С—Р С‘РЎРЏР Р†Р С”Р С‘ Р Р†Р С•РЎРѓРЎРѓРЎвЂљР В°Р Р…Р В°Р Р†Р В»Р С‘Р Р†Р В°РЎР‹РЎвЂљ HP: '.poke_info($pokemonEnemy,'names').']';
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
                        $t_ur .= " Р СџР С•Р В»Р Р…Р С•РЎРѓРЎвЂљРЎРЉРЎР‹ Р Р†Р С•РЎРѓРЎРѓРЎвЂљР В°Р Р…Р В°Р Р†Р В»Р С‘Р Р†Р В°Р ВµРЎвЂљ HP: #".poke_info($pokemon,'names').", ";
                        $t_ur .= status_go(777,$pokemon,$idBattle);
                     }
                     if($pokemonAttak == 18 || $pokemonAttak == 46){
                         if(infoBattle($idBattle,'batl_tip') == 'pvp'){  
                             $ok_demag = false;
                             if($pokemon == infoBattle($idBattle,'poke_1')) { $РЎРѓompu = 'zamtru_2'; } else { $РЎРѓompu = 'zamtru_1'; }
                             $uInf = poke_info($pokemonEnemy,'users');
                             $counPokeActiv  = first('SELECT COUNT(*) as countpok FROM pok_user WHERE users=%d AND active=1 AND hp_my>0',$uInf);
                             if($counPokeActiv['countpok'] > 1){
                              $users =  users($uInf,'login');    
                              update('battles',array($РЎРѓompu=>1),'id='.(int)$idBattle);
                              $uronNoBase  = ' Р С‘ Р Р†РЎвЂ№Р Р…РЎС“Р В¶Р Т‘Р В°Р ВµРЎвЂљ РЎвЂљРЎР‚Р ВµР Р…Р ВµРЎР‚Р В°: '.$users.' РЎРѓР СР ВµР Р…Р С‘РЎвЂљРЎРЉ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В° ';
                            }else{
                              $uronNoBase  = ' Р С—РЎР‚Р С•Р Р†Р В°Р В» ';
                           }
                         }else{
                            $uronNoBase  = ' Р С—РЎР‚Р С•Р Р†Р В°Р В» ';
                         }
                     }
                     if($pokemonAttak == 369 || $pokemonAttak == 521){
                       if(infoBattle($idBattle,'batl_tip') == 'pvp'){ 
                           if($pokemon == infoBattle($idBattle,'poke_1')) { $РЎРѓompu = 'zamtru_1'; } else { $РЎРѓompu = 'zamtru_2'; }
                           $uInf = poke_info($pokemon,'users');
                           $counPokeActiv  = first('SELECT COUNT(*) as countpok FROM pok_user WHERE users=%d AND active=1 AND hp_my>0',$uInf);
                           if($counPokeActiv['countpok'] > 1){
                            $users =  users($uInf,'login');    
                            update('battles',array($РЎРѓompu=>1),'id='.(int)$idBattle);
                            $uronNoBase  = ', Р В° РЎвЂљР В°Р С” Р В¶Р Вµ Р Р†РЎвЂ№Р Р…РЎС“Р В¶Р Т‘Р В°Р ВµРЎвЂљ РЎвЂљРЎР‚Р ВµР Р…Р ВµРЎР‚Р В°: '.$users.' РЎРѓР СР ВµР Р…Р С‘РЎвЂљРЎРЉ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В° ';
                          }else{
                            $uronNoBase  = false;
                          }
                       }else{
                            $uronNoBase  = ' Р С—РЎР‚Р С•Р Р†Р В°Р В» ';
                       }
                     }
                    if($pokemonAttak == 174){
                        if($pokemonMy_tipe_one == 'Ghost' || $pokemonMy_tipe_two == 'Ghost'){
                         $uronNoBase = status_go(9,$pokemonEnemy,$idBattle).', #'.poke_info($pokemon,'names').' РЎвЂљР ВµРЎР‚РЎРЏР ВµРЎвЂљ РЎвЂЎР В°РЎРѓРЎвЂљРЎРЉ РЎРѓР Р†Р С•Р ВµР С–Р С• Р В·Р Т‘Р С•РЎР‚Р С•Р Р†РЎРЉРЎРЏ '; 
                         $rs_hp_my = $rs_hp_my - poke_info($pokemon,'hp_max')*0.5;
                        }else{
                           stats_insert($pokemon,$idBattle,0,0,0,0,1,0,0,'minus');
                           stats_insert($pokemon,$idBattle,1,1,0,0,0,0,0,'plus');
                           $uronNoBase = ' РЎС“ #'.poke_info($pokemon,'names').' РЎС“Р Р†Р ВµР В»Р С‘РЎвЂЎР ВµР Р…Р С•: [Р С’РЎвЂљР В°Р С”Р В° +1], [Р вЂ”Р В°РЎвЂ°Р С‘РЎвЂљР В° +1] Р С‘ РЎС“Р СР ВµР Р…РЎРЉРЎв‚¬Р ВµР Р…Р С•: [Р РЋР С”Р С•РЎР‚Р С•РЎРѓРЎвЂљРЎРЉ -1] '; 
                        }
                    }
                    $rs_hp_my = ($rs_hp_my<=0?0:$rs_hp_my);
                    if($rs_hp_my > poke_info($pokemon,'hp_max')) $rs_hp_my = poke_info($pokemon,'hp_max');
                      updateHp($pokemon, $rs_hp_my);
                    if($rs_hp_my <= 0) $hp_no = ", Р В±Р С•Р В»РЎРЉРЎв‚¬Р Вµ Р Р…Р Вµ Р СР С•Р В¶Р ВµРЎвЂљ Р С—РЎР‚Р С•Р Т‘Р С•Р В»Р В¶Р В°РЎвЂљРЎРЉ Р В±Р С•Р в„–";
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
            
            
            
            /** Р вЂ™РЎвЂ№Р Р†Р С•Р Т‘Р С”Р В° Р Р† Р В»Р С•Р С– Р Т‘Р ВµР в„–РЎРѓРЎвЂљР Р†Р С‘Р в„– **/
            $infoPokesBasenum = poke_info($pokemon,'basenum');
            $infoPokesName    = poke_info($pokemon,'names');
            $namePokeAtak     = attac_battle($pokemonAttak,'atac_name');
            $trxtPovtor       = "<img src='pok/anim/".$infoPokesBasenum.".".img($infoPokesBasenum)."' onClick=win1=window.open('/game.php?go=pokedex&id=".$infoPokesBasenum."','pokedex','width=550,height=550,scrollbars=yes');return true;> 
                                  #".$infoPokesName." Р С‘РЎРѓР С—Р С•Р В»РЎРЉР В·РЎС“Р ВµРЎвЂљ Р В°РЎвЂљР В°Р С”РЎС“:  
                                    <a href=javascript: onClick=win1=window.open('/game.php?go=atk&id=".$pokemonAttak."','atk','width=726,height=260,scrollbars=yes');return true;>".$namePokeAtak."</a>
                                 , ";
            if($udarTrue == 'go'){
               $txtObr = $trxtPovtor.$uron_ms;
               if(attac_battle($pokemonAttak,'dop_effect') == "propusk") new_propusc($pokemon, $idBattle, $pokemonAttak);
            }
            elseif($udarTrue == 'promax'){
               $txtObr = $trxtPovtor." Р Р…Р С• Р С—РЎР‚Р С•Р СР В°РЎвЂ¦Р С‘Р Р†Р В°Р ВµРЎвЂљРЎРѓРЎРЏ";
               $pp_up = "1";
               if(attac_battle($pokemonAttak,'dop_effect') == "propusk") new_propusc($pokemon, $idBattle, $pokemonAttak);
            }
            elseif($udarTrue == 'status'){
               $txtObr = $trxtPovtor." Р Р…Р С• ".$status_my['mess_status'];
            }
            elseif($udarTrue == 'nohp'){
               $txtObr = $trxtPovtor." Р Р…Р С• Р Р…Р Вµ Р СР С•Р В¶Р ВµРЎвЂљ Р С—РЎР‚Р С•Р Т‘Р С•Р В»Р В¶Р В°РЎвЂљРЎРЉ Р В±Р С•Р в„–";
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
            /** Р С™Р С•Р Р…Р ВµРЎвЂ : *Р вЂ™РЎвЂ№Р Р†Р С•Р Т‘Р С”Р В° Р Р† Р В»Р С•Р С– Р Т‘Р ВµР в„–РЎРѓРЎвЂљР Р†Р С‘Р в„–* **/
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
?>