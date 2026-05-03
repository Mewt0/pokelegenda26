<?php
function equip_item($pokemon_id, $item_id) {
    // Проверка принадлежности покемона
    $pokemon = first('SELECT * FROM pok_user WHERE id=%d AND users=%d', $pokemon_id, $_SESSION['id']);
    if(!$pokemon) {
        $_SESSION['TEXT_ITEMS_ERROR'] = 'Покемон не найден';
        return false;
    }
    
    // Если экипируем предмет (item_id > 0)
    if($item_id > 0) {
        // Проверка наличия предмета в инвентаре
        $item = first('SELECT id FROM items_users WHERE item_id=%d AND user_id=%d', $item_id, $_SESSION['id']);
        if(!$item) {
            $_SESSION['TEXT_ITEMS_ERROR'] = 'Предмет отсутствует в инвентаре';
            return false;
        }
        
        // Запрет на экипировку эволюционных камней
        $evolution_stones = [40, 41, 42, 43, 44];
        if(in_array($item_id, $evolution_stones)) {
            $_SESSION['TEXT_ITEMS_ERROR'] = 'Эволюционные камни нельзя экипировать';
            return false;
        }
        
        // Снимаем текущий предмет (если был)
        if($pokemon['item'] > 0) {
            plus_item(1, $pokemon['item']);
        }
        
        // Экипируем новый предмет
        minus_item(1, $item_id, $item['id']);
    } 
    // Если снимаем предмет (item_id = 0)
    else {
        if($pokemon['item'] == 0) return true;
        plus_item(1, $pokemon['item']);
    }
    
    // Обновляем запись покемона
    $update = update('pok_user', ['item' => $item_id], 'id='.(int)$pokemon_id);
    
    if($update) {
        $_SESSION['TEXT_ITEMS_USE'] = 'Экипировка обновлена!';
        return true;
    }
    
    $_SESSION['TEXT_ITEMS_ERROR'] = 'Ошибка экипировки';
    return false;
}

function timersOtshet($time, $prefix = "", $suffix = "Истекло") {
    if ($time <= time()) {
        return $suffix;
    }
    $diff = $time - time();
    $days = floor($diff / (60*60*24));
    $hours = floor(($diff % (60*60*24)) / (60*60));
    $minutes = floor(($diff % (60*60)) / 60);
    $seconds = $diff % 60;
    return $prefix . sprintf("%d дн. %02d:%02d:%02d", $days, $hours, $minutes, $seconds);
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
      $_SESSION['TEXT_ITEMS_ERROR'] = 'Ошибка, поробуйте снова';
     return false;
    }
   $_SESSION['TEXT_ITEMS_USE'] = 'Съев '.item_txt($item,'name').', #'.$poke['names'].' улыбнулся, а по его глазам было видно, как ему еще хотелось полакомится';
  if($she || $gen){
   $_SESSION['TEXT_ITEMS_USE'] = 'Съев '.item_txt($item,'name').', #'.$poke['names'].' ';
   $hpGen = $poke['hp_iv'];
   $atGen = $poke['atk_iv'];
   $dfGen = $poke['def_iv'];
   $saGen = $poke['satk_iv'];
   $sdGen = $poke['sdef_iv'];
   $spGen = $poke['speed_iv']; 
    if($she && $poke['tips'] != 'shine'){
      $dop_name = '<span class="pokesShiny">'.$poke['names'].' - <b>Shiny</b></span>';
      $tips     = 'shine';
      $_SESSION['TEXT_ITEMS_USE'] .= 'прямо на ваших глазах начал менять свой окрас';
    }else{
      $dop_name = $poke['names'];
      $tips     = $poke['tips'];
      if(!$gen) $_SESSION['TEXT_ITEMS_USE'] .= 'улыбнулся, а по его глазам было видно, как ему еще хотелось полакомится';
      if($gen) $_SESSION['TEXT_ITEMS_USE'] .= 'он ему очень понравился';
    }
    if($gen){
      $x01 = rand(1,6);
      $px = rand(1,2);
      if($x01 == 1){
        $hpGen = $poke['hp_iv']+$px;
        $_SESSION['TEXT_ITEMS_USE'] .= ', а так же повысил свой ген HP на '.$px.' пункт'.($px>1?'а':'');
      }elseif($x01 == 2){
        $atGen = $poke['atk_iv']+$px;
        $_SESSION['TEXT_ITEMS_USE'] .= ', а так же повысил свой ген Атаки на '.$px.' пункт'.($px>1?'а':'');
      }elseif($x01 == 3){
        $dfGen = $poke['def_iv']+$px;
        $_SESSION['TEXT_ITEMS_USE'] .= ', а так же повысил свой ген Защиты на '.$px.' пункт'.($px>1?'а':'');
      }elseif($x01 == 4){
        $saGen = $poke['satk_iv']+$px;
        $_SESSION['TEXT_ITEMS_USE'] .= ', а так же повысил свой ген Спец. Атаки на '.$px.' пункт'.($px>1?'а':'');
      }elseif($x01 == 5){
        $sdGen = $poke['sdef_iv']+$px;
        $_SESSION['TEXT_ITEMS_USE'] .= ', а так же повысил свой ген Спец. Защиты на '.$px.' пункт'.($px>1?'а':'');
      }elseif($x01 == 6){
        $spGen = $poke['speed_iv']+$px;
        $_SESSION['TEXT_ITEMS_USE'] .= ', а так же повысил свой ген Скорости на '.$px.' пункт'.($px>1?'а':'');
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
  $_SESSION['TEXT_ITEMS_USE'] = "Вы удачно активировали: ".item_txt($item,'name').' '.($times != 'not'?timersOtshet($times-1,"<br>На: ", ""):'');
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
      $_SESSION['TEXT_ITEMS_USE'] = "Открыв подарок, Вы получили: <br>".item_txt($plus_item_id,'name').' x'.$col_vo;
      $r = true;
   }
 return $r;
}
function podarokNewUser($id){
  if($id == 45){
      $random_items = rand(0,100);
          if($random_items > 99  AND $random_items < 101){$plus_item_id = 17;  $col_vo = 1; } // Шок. Конфета
      elseif($random_items > 95  AND $random_items < 99 ){$plus_item_id = 11;  $col_vo = 15;} // Красн. Конфета
      elseif($random_items > 90  AND $random_items < 95 ){$plus_item_id = 12;  $col_vo = 2; } // Черн. Конфета
      elseif($random_items > 88  AND $random_items < 90 ){$plus_item_id = 2;   $col_vo = 25;} // Алмазы
      elseif($random_items > 65  AND $random_items < 88 ){$plus_item_id = 40;  $col_vo = 2; } // Гром. камень
      elseif($random_items > 50  AND $random_items < 65 ){$plus_item_id = 41;  $col_vo = 2; } // Огн. камень
      elseif($random_items > 35  AND $random_items < 50 ){$plus_item_id = 42;  $col_vo = 2; } // Вод. камень
      elseif($random_items > 20  AND $random_items < 35 ){$plus_item_id = 43;  $col_vo = 2; } // Трав. камень
      elseif($random_items > 0   AND $random_items < 20 ){$plus_item_id = 44;  $col_vo = 2; } // Лун. камень
        else                                             {$plus_item_id = 12;  $col_vo = 2; } // Черн. Конфета
      
      plus_item($col_vo,$plus_item_id);
      $_SESSION['TEXT_ITEMS_USE'] = "Открыв подарок, Вы получили: <br>".item_txt($plus_item_id,'name').' x'.$col_vo;
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
  if($r) $_SESSION['TEXT_ITEMS_USE'] = 'Ваш покемон удачно повысил уровень'; else $_SESSION['TEXT_ITEMS_ERROR'] = 'Увы, произошла ошибка'; 
 return $r;
}
function itemHarPoke($a,$b,$id_poke){
  $r = false;
  if($a == 1) $b = rand(1,26);
  $r = update('pok_user',array('har'=>$b),'users='.(int)$_SESSION['id'].' AND id='.(int)$id_poke);
  if($r) $_SESSION['TEXT_ITEMS_USE'] = 'Ваш покемон удачно изменил характер'; else $_SESSION['TEXT_ITEMS_ERROR'] = 'Увы, произошла ошибка'; 
 return $r;
}
function itemLevelPoke($id_poke){
  $q = false;
  $q = update('pok_user',array('lvl'=>100, 'evcount'=>396, 'hp_ev'=>0, 'atk_ev'=>0,	'def_ev'=>0,	'satk_ev'=>0,	'sdef_ev'=>0,	'speed_ev'=>0),'users='.(int)$_SESSION['id'].' AND id='.(int)$id_poke);
  if($q) $_SESSION['TEXT_ITEMS_USE'] = 'Ваш покемон удачно повысил уровень до 100, а EV очки сброшены на 396'; else $_SESSION['TEXT_ITEMS_ERROR'] = 'Увы, произошла ошибка'; 
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
    if($z) $_SESSION['TEXT_ITEMS_USE'] = 'Операция произведена удачно';
      else $_SESSION['TEXT_ITEMS_ERROR'] = 'Ошибка, поробуйте снова'; 
  }else{
    $_SESSION['TEXT_ITEMS_ERROR'] = 'У этого покемона уже активировано увеличение из этой категории. '.timersOtshet($q["time"],"<br>До истечения осталось: ", "Срок действия увеличения истек");; 
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
      $_SESSION['TEXT_ITEMS_USE'] = 'Покемон #'.$p['names'].' удачно эволюционировал в: #'.$dop_name;
      return $zx; 
     }     
   }
  }
 $_SESSION['TEXT_ITEMS_ERROR'] = 'Ошибка, поробуйте снова';
 return false;
}
?>