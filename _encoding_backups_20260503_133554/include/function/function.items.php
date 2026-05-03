<?php
function equip_item($pokemon_id, $item_id) {
    // Р СџРЎР‚Р С•Р Р†Р ВµРЎР‚Р С”Р В° Р С—РЎР‚Р С‘Р Р…Р В°Р Т‘Р В»Р ВµР В¶Р Р…Р С•РЎРѓРЎвЂљР С‘ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В°
    $pokemon = first('SELECT * FROM pok_user WHERE id=%d AND users=%d', $pokemon_id, $_SESSION['id']);
    if(!$pokemon) {
        $_SESSION['TEXT_ITEMS_ERROR'] = 'Р СџР С•Р С”Р ВµР СР С•Р Р… Р Р…Р Вµ Р Р…Р В°Р в„–Р Т‘Р ВµР Р…';
        return false;
    }
    
    // Р вЂўРЎРѓР В»Р С‘ РЎРЊР С”Р С‘Р С—Р С‘РЎР‚РЎС“Р ВµР С Р С—РЎР‚Р ВµР Т‘Р СР ВµРЎвЂљ (item_id > 0)
    if($item_id > 0) {
        // Р СџРЎР‚Р С•Р Р†Р ВµРЎР‚Р С”Р В° Р Р…Р В°Р В»Р С‘РЎвЂЎР С‘РЎРЏ Р С—РЎР‚Р ВµР Т‘Р СР ВµРЎвЂљР В° Р Р† Р С‘Р Р…Р Р†Р ВµР Р…РЎвЂљР В°РЎР‚Р Вµ
        $item = first('SELECT id FROM items_users WHERE item_id=%d AND user_id=%d', $item_id, $_SESSION['id']);
        if(!$item) {
            $_SESSION['TEXT_ITEMS_ERROR'] = 'Р СџРЎР‚Р ВµР Т‘Р СР ВµРЎвЂљ Р С•РЎвЂљРЎРѓРЎС“РЎвЂљРЎРѓРЎвЂљР Р†РЎС“Р ВµРЎвЂљ Р Р† Р С‘Р Р…Р Р†Р ВµР Р…РЎвЂљР В°РЎР‚Р Вµ';
            return false;
        }
        
        // Р вЂ”Р В°Р С—РЎР‚Р ВµРЎвЂљ Р Р…Р В° РЎРЊР С”Р С‘Р С—Р С‘РЎР‚Р С•Р Р†Р С”РЎС“ РЎРЊР Р†Р С•Р В»РЎР‹РЎвЂ Р С‘Р С•Р Р…Р Р…РЎвЂ№РЎвЂ¦ Р С”Р В°Р СР Р…Р ВµР в„–
        $evolution_stones = [40, 41, 42, 43, 44];
        if(in_array($item_id, $evolution_stones)) {
            $_SESSION['TEXT_ITEMS_ERROR'] = 'Р В­Р Р†Р С•Р В»РЎР‹РЎвЂ Р С‘Р С•Р Р…Р Р…РЎвЂ№Р Вµ Р С”Р В°Р СР Р…Р С‘ Р Р…Р ВµР В»РЎРЉР В·РЎРЏ РЎРЊР С”Р С‘Р С—Р С‘РЎР‚Р С•Р Р†Р В°РЎвЂљРЎРЉ';
            return false;
        }
        
        // Р РЋР Р…Р С‘Р СР В°Р ВµР С РЎвЂљР ВµР С”РЎС“РЎвЂ°Р С‘Р в„– Р С—РЎР‚Р ВµР Т‘Р СР ВµРЎвЂљ (Р ВµРЎРѓР В»Р С‘ Р В±РЎвЂ№Р В»)
        if($pokemon['item'] > 0) {
            plus_item(1, $pokemon['item']);
        }
        
        // Р В­Р С”Р С‘Р С—Р С‘РЎР‚РЎС“Р ВµР С Р Р…Р С•Р Р†РЎвЂ№Р в„– Р С—РЎР‚Р ВµР Т‘Р СР ВµРЎвЂљ
        minus_item(1, $item_id, $item['id']);
    } 
    // Р вЂўРЎРѓР В»Р С‘ РЎРѓР Р…Р С‘Р СР В°Р ВµР С Р С—РЎР‚Р ВµР Т‘Р СР ВµРЎвЂљ (item_id = 0)
    else {
        if($pokemon['item'] == 0) return true;
        plus_item(1, $pokemon['item']);
    }
    
    // Р С›Р В±Р Р…Р С•Р Р†Р В»РЎРЏР ВµР С Р В·Р В°Р С—Р С‘РЎРѓРЎРЉ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В°
    $update = update('pok_user', ['item' => $item_id], 'id='.(int)$pokemon_id);
    
    if($update) {
        $_SESSION['TEXT_ITEMS_USE'] = 'Р В­Р С”Р С‘Р С—Р С‘РЎР‚Р С•Р Р†Р С”Р В° Р С•Р В±Р Р…Р С•Р Р†Р В»Р ВµР Р…Р В°!';
        return true;
    }
    
    $_SESSION['TEXT_ITEMS_ERROR'] = 'Р С›РЎв‚¬Р С‘Р В±Р С”Р В° РЎРЊР С”Р С‘Р С—Р С‘РЎР‚Р С•Р Р†Р С”Р С‘';
    return false;
}

function timersOtshet($time, $prefix = "", $suffix = "Р ВРЎРѓРЎвЂљР ВµР С”Р В»Р С•") {
    if ($time <= time()) {
        return $suffix;
    }
    $diff = $time - time();
    $days = floor($diff / (60*60*24));
    $hours = floor(($diff % (60*60*24)) / (60*60));
    $minutes = floor(($diff % (60*60)) / 60);
    $seconds = $diff % 60;
    return $prefix . sprintf("%d Р Т‘Р Р…. %02d:%02d:%02d", $days, $hours, $minutes, $seconds);
}

// 

function no_href_item(){
    die ("<script>location.href='game.php?go=items';</script>");
}
function item_txt($id,$zap){
  $q = first('SELECT '.$zap.' FROM items WHERE id=%d',$id);
  $a = $q[$zap];
  if(!$q) $a = false;
 return $a;
} 
function stimul($idPoke, $idInv, $hp, $hpMax){
      if($idInv == 2) $hpPlus = 20;
  elseif($idInv == 3) $hpPlus = 50;
  elseif($idInv == 4) $hpPlus = 150;
  $hp = $hp + $hpPlus;
  if($hp>$hpMax) $hp = $hpMax;
  $u = update('pok_user',array('hp_my'=>$hp),'users='.(int)$_SESSION['id'].' AND id='.(int)$idPoke);  
return $u;
}
function shynyPoke($poke,$dop,$item){
  $x = explode(",", $dop);
  $she = false;
  $gen = false;
    if($x[0] == 1){
      if(rand(0,1000) <= $x[1]){
        $she = true;
      }
    }elseif($x[0] == 2){
      if(rand(0,1000) <= $x[1]){
        $she = true;
        $gen = true;
      }
    }else{
      $_SESSION['TEXT_ITEMS_ERROR'] = 'Р С›РЎв‚¬Р С‘Р В±Р С”Р В°, Р С—Р С•РЎР‚Р С•Р В±РЎС“Р в„–РЎвЂљР Вµ РЎРѓР Р…Р С•Р Р†Р В°';
     return false;
    }
   $_SESSION['TEXT_ITEMS_USE'] = 'Р РЋРЎР‰Р ВµР Р† '.item_txt($item,'name').', #'.$poke['names'].' РЎС“Р В»РЎвЂ№Р В±Р Р…РЎС“Р В»РЎРѓРЎРЏ, Р В° Р С—Р С• Р ВµР С–Р С• Р С–Р В»Р В°Р В·Р В°Р С Р В±РЎвЂ№Р В»Р С• Р Р†Р С‘Р Т‘Р Р…Р С•, Р С”Р В°Р С” Р ВµР СРЎС“ Р ВµРЎвЂ°Р Вµ РЎвЂ¦Р С•РЎвЂљР ВµР В»Р С•РЎРѓРЎРЉ Р С—Р С•Р В»Р В°Р С”Р С•Р СР С‘РЎвЂљРЎРѓРЎРЏ';
  if($she || $gen){
   $_SESSION['TEXT_ITEMS_USE'] = 'Р РЋРЎР‰Р ВµР Р† '.item_txt($item,'name').', #'.$poke['names'].' ';
   $hpGen = $poke['hp_iv'];
   $atGen = $poke['atk_iv'];
   $dfGen = $poke['def_iv'];
   $saGen = $poke['satk_iv'];
   $sdGen = $poke['sdef_iv'];
   $spGen = $poke['speed_iv']; 
    if($she && $poke['tips'] != 'shine'){
      $dop_name = '<span class="pokesShiny">'.$poke['names'].' - <b>Shiny</b></span>';
      $tips     = 'shine';
      $_SESSION['TEXT_ITEMS_USE'] .= 'Р С—РЎР‚РЎРЏР СР С• Р Р…Р В° Р Р†Р В°РЎв‚¬Р С‘РЎвЂ¦ Р С–Р В»Р В°Р В·Р В°РЎвЂ¦ Р Р…Р В°РЎвЂЎР В°Р В» Р СР ВµР Р…РЎРЏРЎвЂљРЎРЉ РЎРѓР Р†Р С•Р в„– Р С•Р С”РЎР‚Р В°РЎРѓ';
    }else{
      $dop_name = $poke['names'];
      $tips     = $poke['tips'];
      if(!$gen) $_SESSION['TEXT_ITEMS_USE'] .= 'РЎС“Р В»РЎвЂ№Р В±Р Р…РЎС“Р В»РЎРѓРЎРЏ, Р В° Р С—Р С• Р ВµР С–Р С• Р С–Р В»Р В°Р В·Р В°Р С Р В±РЎвЂ№Р В»Р С• Р Р†Р С‘Р Т‘Р Р…Р С•, Р С”Р В°Р С” Р ВµР СРЎС“ Р ВµРЎвЂ°Р Вµ РЎвЂ¦Р С•РЎвЂљР ВµР В»Р С•РЎРѓРЎРЉ Р С—Р С•Р В»Р В°Р С”Р С•Р СР С‘РЎвЂљРЎРѓРЎРЏ';
      if($gen) $_SESSION['TEXT_ITEMS_USE'] .= 'Р С•Р Р… Р ВµР СРЎС“ Р С•РЎвЂЎР ВµР Р…РЎРЉ Р С—Р С•Р Р…РЎР‚Р В°Р Р†Р С‘Р В»РЎРѓРЎРЏ';
    }
    if($gen){
      $x01 = rand(1,6);
      $px = rand(1,2);
      if($x01 == 1){
        $hpGen = $poke['hp_iv']+$px;
        $_SESSION['TEXT_ITEMS_USE'] .= ', Р В° РЎвЂљР В°Р С” Р В¶Р Вµ Р С—Р С•Р Р†РЎвЂ№РЎРѓР С‘Р В» РЎРѓР Р†Р С•Р в„– Р С–Р ВµР Р… HP Р Р…Р В° '.$px.' Р С—РЎС“Р Р…Р С”РЎвЂљ'.($px>1?'Р В°':'');
      }elseif($x01 == 2){
        $atGen = $poke['atk_iv']+$px;
        $_SESSION['TEXT_ITEMS_USE'] .= ', Р В° РЎвЂљР В°Р С” Р В¶Р Вµ Р С—Р С•Р Р†РЎвЂ№РЎРѓР С‘Р В» РЎРѓР Р†Р С•Р в„– Р С–Р ВµР Р… Р С’РЎвЂљР В°Р С”Р С‘ Р Р…Р В° '.$px.' Р С—РЎС“Р Р…Р С”РЎвЂљ'.($px>1?'Р В°':'');
      }elseif($x01 == 3){
        $dfGen = $poke['def_iv']+$px;
        $_SESSION['TEXT_ITEMS_USE'] .= ', Р В° РЎвЂљР В°Р С” Р В¶Р Вµ Р С—Р С•Р Р†РЎвЂ№РЎРѓР С‘Р В» РЎРѓР Р†Р С•Р в„– Р С–Р ВµР Р… Р вЂ”Р В°РЎвЂ°Р С‘РЎвЂљРЎвЂ№ Р Р…Р В° '.$px.' Р С—РЎС“Р Р…Р С”РЎвЂљ'.($px>1?'Р В°':'');
      }elseif($x01 == 4){
        $saGen = $poke['satk_iv']+$px;
        $_SESSION['TEXT_ITEMS_USE'] .= ', Р В° РЎвЂљР В°Р С” Р В¶Р Вµ Р С—Р С•Р Р†РЎвЂ№РЎРѓР С‘Р В» РЎРѓР Р†Р С•Р в„– Р С–Р ВµР Р… Р РЋР С—Р ВµРЎвЂ . Р С’РЎвЂљР В°Р С”Р С‘ Р Р…Р В° '.$px.' Р С—РЎС“Р Р…Р С”РЎвЂљ'.($px>1?'Р В°':'');
      }elseif($x01 == 5){
        $sdGen = $poke['sdef_iv']+$px;
        $_SESSION['TEXT_ITEMS_USE'] .= ', Р В° РЎвЂљР В°Р С” Р В¶Р Вµ Р С—Р С•Р Р†РЎвЂ№РЎРѓР С‘Р В» РЎРѓР Р†Р С•Р в„– Р С–Р ВµР Р… Р РЋР С—Р ВµРЎвЂ . Р вЂ”Р В°РЎвЂ°Р С‘РЎвЂљРЎвЂ№ Р Р…Р В° '.$px.' Р С—РЎС“Р Р…Р С”РЎвЂљ'.($px>1?'Р В°':'');
      }elseif($x01 == 6){
        $spGen = $poke['speed_iv']+$px;
        $_SESSION['TEXT_ITEMS_USE'] .= ', Р В° РЎвЂљР В°Р С” Р В¶Р Вµ Р С—Р С•Р Р†РЎвЂ№РЎРѓР С‘Р В» РЎРѓР Р†Р С•Р в„– Р С–Р ВµР Р… Р РЋР С”Р С•РЎР‚Р С•РЎРѓРЎвЂљР С‘ Р Р…Р В° '.$px.' Р С—РЎС“Р Р…Р С”РЎвЂљ'.($px>1?'Р В°':'');
      }
    }
   return update('pok_user',array('names'=>$dop_name,
                           'hp_iv'=>$hpGen, 
                           'atk_iv'=>$atGen, 
                           'def_iv'=>$dfGen, 
                           'satk_iv'=>$saGen, 
                           'sdef_iv'=>$sdGen, 
                           'speed_iv'=>$spGen,
                           'tips'=>$tips),'id='.(int)$poke['id']); 
  }
 return true;
}

function smyleChat($item,$dop){
  $x = explode(",", $dop);
  if($x[0] == 1){
    $times = time()+(60*60*24*$x[1]);
  }else{
    $times = 'not';
  }
  $_SESSION['TEXT_ITEMS_USE'] = "Р вЂ™РЎвЂ№ РЎС“Р Т‘Р В°РЎвЂЎР Р…Р С• Р В°Р С”РЎвЂљР С‘Р Р†Р С‘РЎР‚Р С•Р Р†Р В°Р В»Р С‘: ".item_txt($item,'name').' '.($times != 'not'?timersOtshet($times-1,"<br>Р СњР В°: ", ""):'');
  return update('usersunictable',array('smile'=>$x[2], 'smiletime'=>$times),'id='.(int)$_SESSION['id']);
}

function podarok($id){
   $r = false;
   if($id == 5){
      $random_items = rand(1,100);
          if($random_items > 99  AND $random_items < 101){$plus_item_id = 1;  $col_vo = 200000;}
      elseif($random_items > 90  AND $random_items < 99 ){$plus_item_id = 12; $col_vo = 1;}
      elseif($random_items > 85  AND $random_items < 90 ){$plus_item_id = 11; $col_vo = rand(1,2);}
      elseif($random_items > 75  AND $random_items < 85 ){$plus_item_id = 10; $col_vo = rand(1,4);}
      elseif($random_items > 63  AND $random_items < 75 ){$plus_item_id = 9;  $col_vo = rand(1,5);}
      elseif($random_items > 45  AND $random_items < 63 ){$plus_item_id = 8;  $col_vo = rand(1,5);}
      elseif($random_items > 22  AND $random_items < 45 ){$plus_item_id = 7;  $col_vo = rand(1,10);}
      elseif($random_items > 1   AND $random_items  < 22){$plus_item_id = 6;  $col_vo = rand(5,15);}
      else {$plus_item_id = 6; $col_vo = 10;}
      plus_item($col_vo,$plus_item_id);
      $_SESSION['TEXT_ITEMS_USE'] = "Р С›РЎвЂљР С”РЎР‚РЎвЂ№Р Р† Р С—Р С•Р Т‘Р В°РЎР‚Р С•Р С”, Р вЂ™РЎвЂ№ Р С—Р С•Р В»РЎС“РЎвЂЎР С‘Р В»Р С‘: <br>".item_txt($plus_item_id,'name').' x'.$col_vo;
      $r = true;
   }
 return $r;
}
function podarokNewUser($id){
  if($id == 45){
      $random_items = rand(0,100);
          if($random_items > 99  AND $random_items < 101){$plus_item_id = 17;  $col_vo = 1; } // Р РЃР С•Р С”. Р С™Р С•Р Р…РЎвЂћР ВµРЎвЂљР В°
      elseif($random_items > 95  AND $random_items < 99 ){$plus_item_id = 11;  $col_vo = 15;} // Р С™РЎР‚Р В°РЎРѓР Р…. Р С™Р С•Р Р…РЎвЂћР ВµРЎвЂљР В°
      elseif($random_items > 90  AND $random_items < 95 ){$plus_item_id = 12;  $col_vo = 2; } // Р В§Р ВµРЎР‚Р Р…. Р С™Р С•Р Р…РЎвЂћР ВµРЎвЂљР В°
      elseif($random_items > 88  AND $random_items < 90 ){$plus_item_id = 2;   $col_vo = 25;} // Р С’Р В»Р СР В°Р В·РЎвЂ№
      elseif($random_items > 65  AND $random_items < 88 ){$plus_item_id = 40;  $col_vo = 2; } // Р вЂњРЎР‚Р С•Р С. Р С”Р В°Р СР ВµР Р…РЎРЉ
      elseif($random_items > 50  AND $random_items < 65 ){$plus_item_id = 41;  $col_vo = 2; } // Р С›Р С–Р Р…. Р С”Р В°Р СР ВµР Р…РЎРЉ
      elseif($random_items > 35  AND $random_items < 50 ){$plus_item_id = 42;  $col_vo = 2; } // Р вЂ™Р С•Р Т‘. Р С”Р В°Р СР ВµР Р…РЎРЉ
      elseif($random_items > 20  AND $random_items < 35 ){$plus_item_id = 43;  $col_vo = 2; } // Р СћРЎР‚Р В°Р Р†. Р С”Р В°Р СР ВµР Р…РЎРЉ
      elseif($random_items > 0   AND $random_items < 20 ){$plus_item_id = 44;  $col_vo = 2; } // Р вЂєРЎС“Р Р…. Р С”Р В°Р СР ВµР Р…РЎРЉ
        else                                             {$plus_item_id = 12;  $col_vo = 2; } // Р В§Р ВµРЎР‚Р Р…. Р С™Р С•Р Р…РЎвЂћР ВµРЎвЂљР В°
      
      plus_item($col_vo,$plus_item_id);
      $_SESSION['TEXT_ITEMS_USE'] = "Р С›РЎвЂљР С”РЎР‚РЎвЂ№Р Р† Р С—Р С•Р Т‘Р В°РЎР‚Р С•Р С”, Р вЂ™РЎвЂ№ Р С—Р С•Р В»РЎС“РЎвЂЎР С‘Р В»Р С‘: <br>".item_txt($plus_item_id,'name').' x'.$col_vo;
    return true;
  }
 return false;
}

function confetka($a,$b,$id_poke){
  $r = false;
  if($b){
     $ev = $a; 
     if($ev == 0) { $ev = 'not';} 
     $r = levels_my_go($id_poke,$ev);
  }
  if($r) $_SESSION['TEXT_ITEMS_USE'] = 'Р вЂ™Р В°РЎв‚¬ Р С—Р С•Р С”Р ВµР СР С•Р Р… РЎС“Р Т‘Р В°РЎвЂЎР Р…Р С• Р С—Р С•Р Р†РЎвЂ№РЎРѓР С‘Р В» РЎС“РЎР‚Р С•Р Р†Р ВµР Р…РЎРЉ'; else $_SESSION['TEXT_ITEMS_ERROR'] = 'Р Р€Р Р†РЎвЂ№, Р С—РЎР‚Р С•Р С‘Р В·Р С•РЎв‚¬Р В»Р В° Р С•РЎв‚¬Р С‘Р В±Р С”Р В°'; 
 return $r;
}
function itemHarPoke($a,$b,$id_poke){
  $r = false;
  if($a == 1) $b = rand(1,26);
  $r = update('pok_user',array('har'=>$b),'users='.(int)$_SESSION['id'].' AND id='.(int)$id_poke);
  if($r) $_SESSION['TEXT_ITEMS_USE'] = 'Р вЂ™Р В°РЎв‚¬ Р С—Р С•Р С”Р ВµР СР С•Р Р… РЎС“Р Т‘Р В°РЎвЂЎР Р…Р С• Р С‘Р В·Р СР ВµР Р…Р С‘Р В» РЎвЂ¦Р В°РЎР‚Р В°Р С”РЎвЂљР ВµРЎР‚'; else $_SESSION['TEXT_ITEMS_ERROR'] = 'Р Р€Р Р†РЎвЂ№, Р С—РЎР‚Р С•Р С‘Р В·Р С•РЎв‚¬Р В»Р В° Р С•РЎв‚¬Р С‘Р В±Р С”Р В°'; 
 return $r;
}
function itemLevelPoke($id_poke){
  $q = false;
  $q = update('pok_user',array('lvl'=>100, 'evcount'=>396, 'hp_ev'=>0, 'atk_ev'=>0,	'def_ev'=>0,	'satk_ev'=>0,	'sdef_ev'=>0,	'speed_ev'=>0),'users='.(int)$_SESSION['id'].' AND id='.(int)$id_poke);
  if($q) $_SESSION['TEXT_ITEMS_USE'] = 'Р вЂ™Р В°РЎв‚¬ Р С—Р С•Р С”Р ВµР СР С•Р Р… РЎС“Р Т‘Р В°РЎвЂЎР Р…Р С• Р С—Р С•Р Р†РЎвЂ№РЎРѓР С‘Р В» РЎС“РЎР‚Р С•Р Р†Р ВµР Р…РЎРЉ Р Т‘Р С• 100, Р В° EV Р С•РЎвЂЎР С”Р С‘ РЎРѓР В±РЎР‚Р С•РЎв‚¬Р ВµР Р…РЎвЂ№ Р Р…Р В° 396'; else $_SESSION['TEXT_ITEMS_ERROR'] = 'Р Р€Р Р†РЎвЂ№, Р С—РЎР‚Р С•Р С‘Р В·Р С•РЎв‚¬Р В»Р В° Р С•РЎв‚¬Р С‘Р В±Р С”Р В°'; 
 return $q;
}
function timeStatus($id){
  switch ($id){
    case 1:
    case 2:
    case 3:
      return 'rasdasd';
    break;
    case 4:
      return 'rasdasd';
    break;
    default:
			return false;
		break;
  }
 return false;
}

function itemNormalStatus($pok,$dop){
  delete(POKE_EVENT,'pokeid='.(int)$pok.' AND  time<='.(int)time());
  $q = first('SELECT id,time FROM events_pokemon WHERE pokeid=%d AND uniq=0',$pok);
  if(empty($q['id'])){
    $tet = explode(",", $dop);
      if($tet[0] == 1){
        $times = time()+(60*60*$tet[1]);
      }elseif($tet[0] == 2){
        $times = time()+(60*$tet[1]);
      }
    $z = insert('events_pokemon',array('pokeid'=>$pok,'status'=>$tet[2],'time'=>$times,'uniq'=>0));
    if($z) $_SESSION['TEXT_ITEMS_USE'] = 'Р С›Р С—Р ВµРЎР‚Р В°РЎвЂ Р С‘РЎРЏ Р С—РЎР‚Р С•Р С‘Р В·Р Р†Р ВµР Т‘Р ВµР Р…Р В° РЎС“Р Т‘Р В°РЎвЂЎР Р…Р С•';
      else $_SESSION['TEXT_ITEMS_ERROR'] = 'Р С›РЎв‚¬Р С‘Р В±Р С”Р В°, Р С—Р С•РЎР‚Р С•Р В±РЎС“Р в„–РЎвЂљР Вµ РЎРѓР Р…Р С•Р Р†Р В°'; 
  }else{
    $_SESSION['TEXT_ITEMS_ERROR'] = 'Р Р€ РЎРЊРЎвЂљР С•Р С–Р С• Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В° РЎС“Р В¶Р Вµ Р В°Р С”РЎвЂљР С‘Р Р†Р С‘РЎР‚Р С•Р Р†Р В°Р Р…Р С• РЎС“Р Р†Р ВµР В»Р С‘РЎвЂЎР ВµР Р…Р С‘Р Вµ Р С‘Р В· РЎРЊРЎвЂљР С•Р в„– Р С”Р В°РЎвЂљР ВµР С–Р С•РЎР‚Р С‘Р С‘. '.timersOtshet($q["time"],"<br>Р вЂќР С• Р С‘РЎРѓРЎвЂљР ВµРЎвЂЎР ВµР Р…Р С‘РЎРЏ Р С•РЎРѓРЎвЂљР В°Р В»Р С•РЎРѓРЎРЉ: ", "Р РЋРЎР‚Р С•Р С” Р Т‘Р ВµР в„–РЎРѓРЎвЂљР Р†Р С‘РЎРЏ РЎС“Р Р†Р ВµР В»Р С‘РЎвЂЎР ВµР Р…Р С‘РЎРЏ Р С‘РЎРѓРЎвЂљР ВµР С”");; 
    return false;
  }
 return $z;
}

function evolutionItems($pok,$id){
  $go_evol = false;
  $p = first('SELECT * FROM pok_user WHERE id=%d AND users=%d AND active=1',$pok,$_SESSION['id']); 
  if($p){
    if($id == 40){
          if($p['basenum'] == 25)   $go_evol = 26;  
      elseif($p['basenum'] == 133)  $go_evol = 135; 
      elseif($p['basenum'] == 603)  $go_evol = 604; 
    }elseif($id == 41){
          if($p['basenum'] == 37)   $go_evol = 38;  
      elseif($p['basenum'] == 58)   $go_evol = 59; 
      elseif($p['basenum'] == 133)  $go_evol = 136;
      elseif($p['basenum'] == 513)  $go_evol = 514;
    }elseif($id == 42){
          if($p['basenum'] == 61)   $go_evol = 62;  
      elseif($p['basenum'] == 90)   $go_evol = 91; 
      elseif($p['basenum'] == 120)  $go_evol = 121;
      elseif($p['basenum'] == 133)  $go_evol = 134;
      elseif($p['basenum'] == 271)  $go_evol = 272;
      elseif($p['basenum'] == 515)  $go_evol = 516;
    }elseif($id == 43){
          if($p['basenum'] == 44)   $go_evol = 45;  
      elseif($p['basenum'] == 70)   $go_evol = 71; 
      elseif($p['basenum'] == 102)  $go_evol = 103;
      elseif($p['basenum'] == 274)  $go_evol = 275;
      elseif($p['basenum'] == 511)  $go_evol = 512;
    }elseif($id == 44){
          if($p['basenum'] == 30)   $go_evol = 31;  
      elseif($p['basenum'] == 33)   $go_evol = 34; 
      elseif($p['basenum'] == 35)   $go_evol = 36;
      elseif($p['basenum'] == 39)   $go_evol = 40;
      elseif($p['basenum'] == 300)  $go_evol = 301;
      elseif($p['basenum'] == 517)  $go_evol = 518;
    }
   if($go_evol){
     $id    = $p['id'];
     $level = $p['lvl'];
     $p['basenum'] = $go_evol;    
     
     $evo_poke = first('SELECT id,title FROM poke_base WHERE id=%d',$go_evol);
     if($p['tips'] == 'shine'){
      $dop_name = '<span class="pokesShiny">'.$evo_poke['title'].' - <b>Shiny</b></span>';
     }else{
      $dop_name = $evo_poke['title'];
     }   
      $zx = update('pok_user',array(
                  'names'=>$dop_name, 
                  'basenum'=>$go_evol, 
                  'hp_my'=>my_stat_go($id,'hp',$level,$p),
                  'hp_max'=>my_stat_go($id,'hp',$level,$p),
                  'atk'=>my_stat_go($id,'atk',$level,$p),
                  'def'=>my_stat_go($id,'def',$level,$p),
                  'satk'=>my_stat_go($id,'satk',$level,$p),
                  'sdef'=>my_stat_go($id,'sdef',$level,$p),
                  'speed'=>my_stat_go($id,'speed',$level,$p)),'users='.(int)$_SESSION['id'].' AND id='.(int)$id);
     if($zx){
      $_SESSION['TEXT_ITEMS_USE'] = 'Р СџР С•Р С”Р ВµР СР С•Р Р… #'.$p['names'].' РЎС“Р Т‘Р В°РЎвЂЎР Р…Р С• РЎРЊР Р†Р С•Р В»РЎР‹РЎвЂ Р С‘Р С•Р Р…Р С‘РЎР‚Р С•Р Р†Р В°Р В» Р Р†: #'.$dop_name;
      return $zx; 
     }     
   }
  }
 $_SESSION['TEXT_ITEMS_ERROR'] = 'Р С›РЎв‚¬Р С‘Р В±Р С”Р В°, Р С—Р С•РЎР‚Р С•Р В±РЎС“Р в„–РЎвЂљР Вµ РЎРѓР Р…Р С•Р Р†Р В°';
 return false;
}
?>