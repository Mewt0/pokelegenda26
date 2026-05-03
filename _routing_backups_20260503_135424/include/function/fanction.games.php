<?php
/** -- Работа с системой -- **/
 function itemsPokemon($id){
  $a = first('SELECT id_items FROM items_poke WHERE id_poke=%d',$id);
  if(!$a) {
    $images_pok = '<img src=img/blank.gif width=24 height=24 border=0>';
  } else {
    $images_pok = '<img src=img/items/'.$a['id_items'].'.png width=24 height=24 border=0>';
  }
  return $images_pok;
}

function text_chats($text,$tip,$build=false){
  $time = time();
  if($tip == 1) 
    insert('chats',array('author'=>$_SESSION['login'], 'time'=>$time, 'text'=>$text, 'tipe'=>4));
   else   
     insert('chats',array('author'=>$_SESSION['login'], 'time'=>$time, 'text'=>$text, 'room'=>$build, 'tipe'=>1));
}
function log_moder($text){
 return insert('mod_log',array('text'=>$text,'id_user'=>$_SESSION['id'],'date'=>date('Y-m-d, H:i:s'),'tip'=>1));
}
function buildInf($id,$zap){
 $q = first('SELECT * FROM build WHERE id=%d',$id);
 if(!$q) return false; 
    else return $q[$zap]; 
}
/** --- Работа с системой *Конец*--- **/
/** -- Работа с пользователями -- **/
function users_conect($zap){
    $q = first('SELECT '.$zap.' FROM users WHERE id=%d',$_SESSION['id']);
    if($q) $a = $q[$zap];
      else $a = false;
 return $a;     
}
function users_conect_dop($zap){
    $q = first('SELECT '.$zap.' FROM usersunictable WHERE id=%d',$_SESSION['id']);
    if($q) $a = $q[$zap];
      else $a = false;
 return $a;   
}
function info_uses($id,$zap){
    $q = first('SELECT '.$zap.' FROM users WHERE id=%d',$id);
    if($q) $a = $q[$zap];
      else $a = false;
 return $a;     
}
function info_uses_dop($id,$zap){
    $q = first('SELECT '.$zap.' FROM usersunictable WHERE id=%d',$id);
    if($q) $a = $q[$zap];
      else $a = false;
 return $a;   
}
function textGroup($a){
      if($a == 1) $txt = "Администратором";
  elseif($a == 2) $txt = "Полицейским";
  elseif($a == 3) $txt = "Модератором";
  elseif($a == 4) $txt = "Наставником";
  elseif($a == 5) $txt = "Гим-Лидером";
  elseif($a == 6) $txt = "Пользователем";
  elseif($a == 7) $txt = "Заключенным";
  elseif($a == 8)$txt = "Куратором-турниров";  
  elseif($a == 10)$txt = "Забаненым";
  else            $txt = "Пользователем";
 return $txt;
}

function colorsUsers($group){
        if ($group == 1)  $color_gr = "#B22222"; //Админы
    elseif ($group == 2)  $color_gr = "#ff7518"; //Полиция
    elseif ($group == 3)  $color_gr = "#afeeee"; //Мoдераторы
    elseif ($group == 4)  $color_gr = "#4b0082"; //Наставники
    elseif ($group == 5)  $color_gr = "#ffbf00"; //Гим-лидеры
    elseif ($group == 6)  $color_gr = "#000000"; //Пользователи
    elseif ($group == 7)  $color_gr = "#ffc0cb"; //Заключенные
    elseif ($group == 8) $color_gr = "#66FF33"; //Куратор-турниров
    elseif ($group == 10) $color_gr = "#7fc7ff"; //Забаненые
    else                  $color_gr = "#000000"; //Юзеры
 return $color_gr;
}

function rang_group($id,$group,$rang,$color=false) {
  if($color != false) $colors = $color; else $colors = '#000';
      if ($group == 1 AND ($id != 1 AND $id !=3)) $returnRang = "<span style=\"color:".$colors."\"><b>Администратор</b></span>";
  elseif ($group == 5 AND ($id != 2 AND $id !=473 AND $id !=385 AND $id !=570 AND $id !=191 AND $id !=704 AND $id !=366 AND $id !=753)) $returnRang = "<span style=\"color:".$colors."\"<b>Гим-лидер</span>, ".$rang."</b></span>";
  elseif ($group == 2) $returnRang = "<span style=\"color:".$colors."\"><b>Полицейский</span>, ".$rang."</b></span>";
  elseif ($group == 3) $returnRang = "<span style=\"color:".$colors."\"><b>Модератор</span>, ".$rang."</b></span>";
  elseif ($group == 4) $returnRang = "<span style=\"color:".$colors."\"><b>Наставник</span>, ".$rang."</b></span>";
  elseif ($group == 6) $returnRang = "<span style=\"color:".$colors."\"><b>".$rang."</b></span>";
  elseif ($group == 8) $returnRang = "<span style=\"color:".$colors."\"><b>Куратор-турниров</span>, ".$rang."</b></span>";
  elseif ($group == 7) $returnRang = "<span style=\"color:".$colors."\"><b>Заключенный</b></span>";
  elseif ($group == 10)$returnRang = "<span style=\"color:".$colors."\"><b>Забаненый</b></span>";
  elseif ($id == 2 AND $group == 5) $returnRang = "<span style=\"color:".$colors."\"><b>Стадион Водных покемонов, ГИМ-Лидер</b></span>";
  elseif ($id == 753 AND $group == 5) $returnRang = "<span style=\"color:".$colors."\"><b>Стадион Боевых покемонов, ГИМ-Лидер</b></span>";
  elseif ($id == 385 AND $group == 5) $returnRang = "<span style=\"color:".$colors."\"><b>Стадион Психических покемонов, ГИМ-Лидер</b></span>";
  elseif ($id == 570 AND $group == 5) $returnRang = "<span style=\"color:".$colors."\"><b>Стадион Земляных покемонов, ГИМ-Лидер</b></span>";
  elseif ($id == 191 AND $group == 5) $returnRang = "<span style=\"color:".$colors."\"><b>Стадион Травяных покемонов, ГИМ-Лидер</b></span>";
  elseif ($id == 704 AND $group == 5) $returnRang = "<span style=\"color:".$colors."\"><b>Стадион Каменных покемонов, ГИМ-Лидер</b></span>";
  elseif ($id == 366 AND $group == 5) $returnRang = "<span style=\"color:".$colors."\"><b>Стадион Огненных покемонов, ГИМ-Лидер</b></span>";
  elseif ($id == 473 AND $group == 5) $returnRang = "<span style=\"color:".$colors."\"><b>Стадион Электрических покемонов, ГИМ-Лидер</b></span>";
  elseif ($id == 1 AND $group == 1) $returnRang = "<span style=\"color:".$colors."\"><b>Главный Администратор</b></span>";
  elseif ($id == 3 AND $group == 1) $returnRang = "<span style=\"color:".$colors."\"><b>Система</b></span>";
  else  $returnRang = "<span style=\"color:".$colors."\"><b>".$rang."</b></span>"; 
 return $returnRang;
}

function color_group_users($id, $tipes = false){
  $login = info_uses($id,'login');
  if($login){          
    $group = info_uses($id,'groups');   
    $on    = info_uses($id,'online');
    $color_gr = colorsUsers($group);
    $obv = false;
    $onclik = false;
    if(!$tipes) $obv = '<a href=javascript: onClick="win1=window.open(\'page.php?id='.$id.'\',\'info\',\'width=900,height=580,scrollbars=yes\');return true;"><img src="/img/other/inf.png" ></a>';
      else $onclik = 'onClick=window.open(\'page.php?id='.$id.'\',\'info\',\'width=900,height=580,scrollbars=yes\');';
    if($on == 1) $obv .= " <span ".$onclik." style='color:".$color_gr.";font-weight:bold;'>".$login."</span>";
    else $obv .= " <span ".$onclik." style='color:".$color_gr.";'>".$login."</span>";
  }else{
    $obv = false;
  }
 return $obv;
}

function sistemChat($txt){
   return '<b><tt style=\"color:gold;\">['.date('H:i:s',time()).']</tt> <a href=\'javascript://\' onclick=\'parent.privat(\"League Of Pokemons\")\'><font color=\"black\"> PR </font></a><a href=\'javascript://\' onclick=\'parent.to_tr(\"League Of Pokemons\")\' style=\"color:'.colorsUsers(1).'\">League Of Pokemons</a>: '.$txt.'</b><br>';
}

function moderations($login,$minut,$pricina){
  $login   = obr_txt($login);
  $minut   = obr_chis($minut);               
  $pricina = obr_txt($pricina);
    if(empty($login) || empty($minut) || empty($pricina)) die("<script>parent.mess_chat(\"".sistemChat("Чего - то не хватает.")."\"); document.location.href = document.location.href;</script>");
  $a = first('SELECT id FROM users WHERE login = "%s"',$login);
    if(empty($a['id'])) die("<script>parent.mess_chat(\"".sistemChat("Пользователя: ".$login.", несуществует.")."\");document.location.href = document.location.href;</script>");
  $m = $minut*60;
  $min = time() + $m;                 
  $text = "<font color=#A52A2A>Пользователь: <b>".$login."</b>, за нарушения правил игрового чата: <i>".$pricina."</i>, будет молчать: ".$minut." мин.!</font>"; 
  update('usersunictable', array('molcha'=>$min), 'id='.(int)$a['id']);
  //query('UPDATE usersunictable SET coolday=coolday-1 WHERE id=%d AND coolday >= 1',$a['id']);
  text_chats($text,1);
  log_moder($text);     
  die("<script>parent.mess_chat(\"".sistemChat("Пользователь: ".$login.", наказан.")."\");document.location.href = document.location.href;</script>"); 
}

function police($login,$day,$pricina){
  $login   = obr_txt($login);
  $day     = obr_chis($day);               
  $pricina = obr_txt($pricina);
    if(empty($login) || empty($pricina)) die("<script>parent.mess_chat(\"".sistemChat("Чего - то не хватает.")."\"); document.location.href = document.location.href;</script>");
  $a = first('SELECT id FROM users WHERE login = "%s"',$login);
    if(empty($a['id'])) die("<script>parent.mess_chat(\"".sistemChat("Пользователя: ".$login.", несуществует.")."\");document.location.href = document.location.href;</script>");
  $d = time() +(60*60*24*$day);             
  if($day <= 0 ) { $t = ' освобожден из заключения('.$pricina.').'; $b = 1; $o = 'освобожден'; $g = 6; }else { $t = 'за нарушения правил игрового мира: <i>'.$pricina.'</i>, заключен на: '.$day.' дней!'; $b = 2; $o = 'наказан'; $g = 7; }
  $text = "<font color=#A52A2A>Пользователь: <b>".$login."</b>, ".$t."</font>"; 
  update('usersunictable', array('arest'=>$d, 'molcha'=>$d), 'id='.(int)$a['id']); 
  update('users', array('buildmy'=>$b, 'groups'=>$g, 'police'=>0, 'moderation'=>0),  'id='.(int)$a['id']);
  text_chats($text,1);
  //log_moder($text);     
  die("<script>parent.mess_chat(\"".sistemChat("Пользователь: ".$login.", ".$o.".")."\");document.location.href = document.location.href;</script>"); 
}

/** --- Работа с пользователями *Конец* --- **/


/** -- Работа с покемонами -- **/
function conect_pokes($id){
  return first('SELECT * FROM pok_user WHERE id=%d',$id);
}
function sexPoke($id,$pol){
  if($pol == 1) { $x = 'm_id'; $xNo = 'd_id'; $g = 'Готов'; }else{ $x = 'd_id'; $xNo = 'm_id'; $g = 'Готова';}
  $text = false;
  $x01  = false;
  $a = first('SELECT * FROM poke_spar WHERE '.$x.'=%d',$id);
  if(!empty($a['id'])){
    $xp = first('SELECT id, names FROM pok_user WHERE id=%d',$a[$xNo]);
     if($xp['id']){
      $names  = $xp['names'];
      $idNo   = $a[$xNo];
      if($a['status'] == 1) { $x01 = 'Проявляет интерес к: <b>#'.$names.'</b> (ID: '.$idNo.')'; }
      if($a['status'] == 2) { $x01 = 'Проявляет симпатию к: <b>#'.$names.'</b> (ID: '.$idNo.')'; }
      if($a['status'] == 3) { $x01 = $g.' к разведению с <b>#'.$names.'</b> (ID: '.$idNo.')'; }
      $text = '<b><span style="color:#000;">Готовность к разведению:</span></b> <b><i>'.$x01.'.</b></i><br>';
     }
  }
 return $text;
}
function conectActivePokesMyUsers($id,$zap){
    $q = first('SELECT '.$zap.' FROM pok_user WHERE id=%d AND users=%d AND active=1',$id,$_SESSION['id']);
    if($q) $a = $q[$zap];
      else $a = false;
 return $a; 
}
function skoba_poke($id_poks,$id_items){
  $pitem = first('SELECT id_items FROM items_poke WHERE id_poke=%d AND id_items=%d',$id_poks,$id_items);
    if($pitem && $id_items == 2) $effekt = '1';
      else $effekt = '0';
 return $effekt;
}

function bafs_pokemon($poke,$ids,$tip){
    $return = 0;
        if($ids == 'hp')    $ids = 1;
    elseif($ids == 'atk')   $ids = 2;
    elseif($ids == 'def')   $ids = 4;
    elseif($ids == 'satk')  $ids = 3;
    elseif($ids == 'sdef')  $ids = 5;
    elseif($ids == 'speed') $ids = 6;
    else                    $ids = 0;

    if($ids > 0){
        $a = first('SELECT * FROM baf_pokes WHERE id_poke=%d AND id_bafs=%d AND tip_bafs="%s"',$poke,$ids,$tip);
        $return = (!empty($a) && isset($a['cols']) ? $a['cols'] : 0);
    }
 return $return;
}


function hp_updates_pokemon($id){
    $p = first('SELECT hp_my,hp_max FROM pok_user WHERE users=%d AND id=%d',$_SESSION['id'],$id);
    if(!empty($p) && isset($p['hp_my'], $p['hp_max']) && $p['hp_my'] > $p['hp_max']){
        update('pok_user',array('hp_my'=>$p['hp_max']),'users='.(int)$_SESSION['id'].' AND id='.(int)$id);
        $ret = true;
    }
    else{
        $ret = false;
    }
 return $ret;
}

function my_stat_go($id,$tip,$levels,$p=false){
  hp_updates_pokemon($id);
  if(empty($p)) $p = first('SELECT evcount,exp,hp_max,users,lvl,id,basenum,har,hp_iv,hp_ev,atk_iv,atk_ev,def,def_ev,def_iv,satk_iv,satk_ev,sdef_ev,sdef_iv,speed_ev,speed_iv FROM pok_user WHERE users=%d AND id=%d',$_SESSION['id'],$id);
  $h = first('SELECT * FROM har WHERE id_har=%d',$p['har']);
  $p_base= first('SELECT hp,atk,def,satk,sdef,speed FROM poke_base WHERE id=%d',$p['basenum']);
  if($tip == 'hp'){
      $r = (($p['hp_iv']+($p_base['hp']*2)+($p['hp_ev']/4)+100)*($levels/100))+10;
        $r = $r+bafs_pokemon($id,'hp','plus');
    }
  elseif($tip == 'atk'){
      $r = ((($p['atk_iv']+($p_base['atk']*2)+($p['atk_ev']/4))*($levels/100))+5)*$h['atk'];
        $r = $r+bafs_pokemon($id,'atk','plus');
    }
  elseif($tip == 'def'){
      $r = ((($p['def_iv']+($p_base['def']*2)+($p['def_ev']/4))*($levels/100))+5)*$h['def'];
        $r = $r+bafs_pokemon($id,'def','plus');
    }
  elseif($tip == 'satk'){
      $r = ((($p['satk_iv']+($p_base['satk']*2)+($p['satk_ev']/4))*($levels/100))+5)*$h['satk'];
        $r = $r+bafs_pokemon($id,'satk','plus');
    }
  elseif($tip == 'sdef'){
      $r = ((($p['sdef_iv']+($p_base['sdef']*2)+($p['sdef_ev']/4))*($levels/100))+5)*$h['sdef'];
        $r = $r+bafs_pokemon($id,'sdef','plus');
    }
  elseif($tip == 'speed'){
      $r = ((($p['speed_iv']+($p_base['speed']*2)+($p['speed_ev']/4))*($levels/100))+5)*$h['speed'];
        $r = $r+bafs_pokemon($id,'speed','plus');
    }
  $r = round($r);    
 return $r;
}

function pokeStatZap($tip,$levels,$iv=false,$ev=false,$stat=false,$base=false){
  $p_base= first('SELECT hp,atk,def,satk,sdef,speed,title FROM poke_base WHERE id=%d',$base);
 if($tip == 1){
   $stat2 = $p_base[$stat];
  if($stat == 'hp')
    $r = (($iv+($stat2*2)+($ev/4)+100)*($levels/100))+10;   
   else
     $r = ((($iv+($stat2*2)+($ev/4))*($levels/100))+5)*1;  
  $r = round($r);
 }else{
   $r = $p_base[$levels];
 }    
 return $r;
}


function stat_updates($id,$lvl){
  update('pok_user',array(
        'hp_max'=>my_stat_go($id,'hp',$lvl),
        'atk'=>my_stat_go($id,'atk',$lvl),
        'def'=>my_stat_go($id,'def',$lvl),
        'satk'=>my_stat_go($id,'satk',$lvl),
        'sdef'=>my_stat_go($id,'sdef',$lvl),
        'speed'=>my_stat_go($id,'speed',$lvl)),'users='.(int)$_SESSION['id'].' AND id='.(int)$id);
}

function bafIssetPoke($poke,$ids,$tip){
        if($ids == 'hp')    {$ids = 1; $c = "НР";}
    elseif($ids == 'atc')   {$ids = 2; $c = "Атака";}
    elseif($ids == 'satc')  {$ids = 3; $c = "Спец. Атака";}
    elseif($ids == 'def')   {$ids = 4; $c = "Защита";}
    elseif($ids == 'sdef')  {$ids = 5; $c = "Спец. Защита";}
    elseif($ids == 'speed') {$ids = 6; $c = "Скорость";}
    else $ids = 0;
    if($ids > 0){
        $a = first('SELECT cols FROM baf_pokes WHERE id_poke=%d AND id_bafs=%d AND tip_bafs="%s"',$poke,$ids,$tip);
        if($a){
            $return = $a['cols'];
            if($tip == "plus") $txt = "<font color=#000000>Увеличено: $c, на: <b>$return</b> едениц. </font><br>";
            else  $txt = "<font color=#000000>Уменьшено: $c, на: <b>$return</b> едениц. </font><br>";
        }else{
            $txt = "";
        }
    }
 return $txt;
}



function unic_evol($id,$evol_tip,$base){
  $up = false;
  $sdata = date('H');
  $p = first('SELECT atk_ev,def_ev,lvl,tips,id,happy FROM pok_user WHERE id=%d AND users=%d AND active=1',$id,$_SESSION['id']);
  $c = $p['lvl'];
  $hep = $p['happy'];
  if($base == 236 && $c>=20 && $evol_tip == 1){
        if($p['atk_ev'] >  $p['def_ev']) $pok = 106;
    elseif($p['atk_ev'] <  $p['def_ev']) $pok = 107;
    elseif($p['atk_ev'] == $p['def_ev']) $pok = 237;
    $up = true; 
  }elseif($evol_tip == 2 && $hep >= 100){
    $up = true; 
        if($base == 172) $pok = 25;
    elseif($base == 42 ) $pok = 169;
    elseif($base == 113) $pok = 242;
    elseif($base == 298) $pok = 183;
    elseif($base == 133 && ($sdata > 12 && $sdata <=18)) $pok = 196;
    elseif($base == 133 && ($sdata > 18 && $sdata <=24)) $pok = 197;
    elseif($base == 349) $pok = 350;
    elseif($base == 174) $pok = 39;
    elseif($base == 173) $pok = 35;
    elseif($base == 175) $pok = 176;
    elseif($base == 406) $pok = 315;
    elseif($base == 527) $pok = 528;
    elseif($base == 108) $pok = 463;
    elseif($base == 114) $pok = 465;
    elseif($base == 439) $pok = 122;
    elseif($base == 349) $pok = 350;
    elseif($base == 190) $pok = 424;
    elseif($base == 438) $pok = 185;
    else $up = false;  
  }elseif($evol_tip == 3 && $hep >= 50){
    $at = first('SELECT a_id,b_id,d_id,c_id FROM attac_my_poke WHERE pok_id=%d',$p['id']);
    $up = true; 
        if($base == 193 && ($at['a_id']==246 || $at['b_id']==246 || $at['c_id']==246 || $at['d_id']==246)) $pok = 469;
        elseif($base == 221 && ($at['a_id']==246 || $at['b_id']==246 || $at['c_id']==246 || $at['d_id']==246)) $pok = 473;
    else $up = false;  
  }
  if($up == true){
      $p2 = first('SELECT title,img FROM poke_base WHERE id=%d',$pok);
      if($p['tips'] == 'shine'){
        $dop_name = '<span class="pokesShiny">'.$p2['title'].' - <b>Shiny</b></span>';
      }elseif($p['tips'] == 'nwy'){
        $dop_name = '<span class="pokesNwy">'.$p2['title'].' - <b>Christmas</b></span>';
      }else{
        $dop_name = $p2['title'];
      }
      $ups = update('pok_user',array(
             'names'=>$dop_name, 
             'basenum'=>$pok, 
             'hp_my'=>my_stat_go($id,'hp',$c),
             'hp_max'=>my_stat_go($id,'hp',$c),
             'atk'=>my_stat_go($id,'atk',$c),
             'def'=>my_stat_go($id,'def',$c),
             'satk'=>my_stat_go($id,'satk',$c),
             'sdef'=>my_stat_go($id,'sdef',$c)
              ),'users='.(int)$_SESSION['id'].' AND id='.(int)$p['id']);        
  }
 return $ups;
}
function evolution_pokes($id){
  $up = false;
  $p = first('SELECT id,basenum,lvl,tips FROM pok_user WHERE id=%d AND users=%d AND active=1',$id,$_SESSION['id']);
  $p3 = first('SELECT evolution_lvl,evolution_type,evol_a FROM poke_base WHERE id=%d',$p['basenum']);
  if($p && $p3){
    if($p3['evol_a']>0){
      unic_evol($id,$p3['evol_a'],$p['basenum']);
    }else{
      if($p3['evolution_lvl'] != 0){
        if($p){
          $a = $p3['evolution_lvl'];
          $b = $p3['evolution_type'];
          $c = $p['lvl']; 
          if($a == $c OR $a < $c){
            $p2 = first('SELECT title,img FROM poke_base WHERE id=%d',$b);
          if($p['tips'] == 'shine'){
            $dop_name = '<span class="pokesShiny">'.$p2['title'].' - <b>Shiny</b></span>';
          }elseif($p['tips'] == 'nwy'){
            $dop_name = '<span class="pokesNwy">'.$p2['title'].' - <b>Christmas</b></span>';
          }else{
            $dop_name = $p2['title'];
          }
          $up = update('pok_user',array(
                 'names'=>$dop_name, 
                 'basenum'=>$b, 
                 'hp_my'=>my_stat_go($id,'hp',$c),
                 'hp_max'=>my_stat_go($id,'hp',$c),
                 'atk'=>my_stat_go($id,'atk',$c),
                 'def'=>my_stat_go($id,'def',$c),
                 'satk'=>my_stat_go($id,'satk',$c),
                 'sdef'=>my_stat_go($id,'sdef',$c)
                  ),'users='.(int)$_SESSION['id'].' AND id='.(int)$p['id']);
          }
        }
      }
    }
  }  
return $up;
}

function levels_my_go($id,$ev_m){
  $a = false;
  $ev = 0;
  $up = false;
  $pokems = first('SELECT exp,hp_max,lvl,id,users,evcount FROM pok_user WHERE id=%d AND users=%d AND active=1',$id,$_SESSION['id']);
  if($pokems){
      if($ev_m != 'not'){
        if($ev_m > 0){
          $ev =  $pokems['evcount']+$ev_m;
        }else{
          $ev =  $pokems['evcount']+4;
        }
      } 
    if($pokems['lvl'] != 100 AND $pokems['lvl'] < 100){       
      if($ev == 0 || $ev < 0 || $ev == 'not'){
        $ev = $pokems['evcount'];
      }
        $level = $pokems['lvl']+1;
        $ev = $ev+skoba_poke($id,2);
        evolution_pokes($pokems['id']);
        $up = update('pok_user',array(
          'exp'=>level_exp($level-1)+1,  
          'evcount'=>$ev, 
          'lvl'=>$level, 
          'hp_my'=>my_stat_go($id,'hp',$level),
          'hp_max'=>my_stat_go($id,'hp',$level),
          'atk'=>my_stat_go($id,'atk',$level),
          'def'=>my_stat_go($id,'def',$level),
          'satk'=>my_stat_go($id,'satk',$level),
          'sdef'=>my_stat_go($id,'sdef',$level),
          'speed'=>my_stat_go($id,'speed',$level)),'users='.(int)$_SESSION['id'].' AND id='.(int)$id);
        
    }
 }
 return $up;
}

class pokes{
    private $info = array();
      function __construct($id_base_pok){
          $this->info = first('SELECT * FROM pok_user WHERE id=%d AND users=%d AND active=1',$id_base_pok,$_SESSION['id']); 
        }

      function __get($key){
          return isset($this->info[$key])?$this->info[$key]:'';
        }   

      function __set($key,$value){     
          //   $info =  $key,    array(); =  $value
          $this->info[$key] = $value;
        }
}

function levels_go($p){
  $inf = new pokes($p);
  $exp = $inf->exp;
  $level = $inf->lvl;
  $ev =  $inf->evcount;  
  $exp_max = level_exp($level);
  $changed = false;
  $c = $inf->hp_ev + $inf->atk_ev + $inf->def_ev + $inf->satk_ev + $inf->sdef_ev + $inf->speed_ev + $inf->evcount;
  $ch_ev = 4+skoba_poke($p,2);
while($exp>=$exp_max AND $level<100)
  {
    $ev+=$ch_ev;
    $level++;
    $changed = true;
    $exp_max = level_exp($level);
      
  } 
if($changed){
        $up = update('pok_user',array(
        'exp'=>level_exp($level-1),  
        'evcount'=>$ev, 
        'lvl'=>$level, 
        'hp_my'=>my_stat_go($p,'hp',$level),
        'hp_max'=>my_stat_go($p,'hp',$level),
        'atk'=>my_stat_go($p,'atk',$level),
        'def'=>my_stat_go($p,'def',$level),
        'satk'=>my_stat_go($p,'satk',$level),
        'sdef'=>my_stat_go($p,'sdef',$level),
        'speed'=>my_stat_go($p,'speed',$level)),'users='.(int)$_SESSION['id'].' AND id='.(int)$p);
        evolution_pokes($p);
} 
}

function pokemonDicPluse($p2,$battle,$active,$uses){
  $x = first('SELECT * FROM pok_pve WHERE id=%d AND poimka=1',$p2);
  $lvl = $x['lvl'];
  if($x['tips'] == 'normal'){
    $iv1 = rand(1,25);
    $iv2 = rand(1,22);
    $iv3 = rand(1,24);
    $iv4 = rand(1,21);
    $iv5 = rand(1,23);
    $iv6 = rand(1,20);
    $dop_name = '';
    $tag_a = '';
    $tag_b = '';
    $tip = 'normal'; 
  }else{
    $iv1 = rand(10,32);
    $iv2 = rand(10,32);
    $iv3 = rand(10,32);
    $iv4 = rand(10,32);
    $iv5 = rand(10,32);
    $iv6 = rand(10,32);
    $dop_name = ' - <b>Shiny</b>';
    $tag_a = '<span class="pokesShiny">';
    $tag_b = '</span>';
    $tip = 'shine';        
  }
   $ev = 0;
   if($x['sprz'] == 1) {$sparka = '1'; $prod = '0';}
   elseif($x['sprz'] == 2){$sparka = '1'; $prod = '1';}
   elseif($x['sprz'] == 3){$sparka = '0'; $prod = '1';}
   else {$sparka = '0'; $prod = '0';}
   $har_k = rand(1,26);
   $har   = first('SELECT * FROM har WHERE id_har=%d',$har_k);
   $sex   = rand(1,2);
   $pok   = first('SELECT * FROM poke_base WHERE id=%d LIMIT 1',$x['basenum']);
   $hp    = (($iv1+($pok['hp']*2)+($ev/4)+100)*($lvl/100))+10;
   $atk   = ((($iv2+($pok['atk']*2)+($ev/4))*($lvl/100))+5)*$har['atk'];
   $def   = ((($iv3+($pok['def']*2)+($ev/4))*($lvl/100))+5)*$har['def'];
   $satk  = ((($iv4+($pok['satk']*2)+($ev/4))*($lvl/100))+5)*$har['satk'];
   $sdef  = ((($iv5+($pok['sdef']*2)+($ev/4))*($lvl/100))+5)*$har['sdef'];
   $speed = ((($iv6+($pok['speed']*2)+($ev/4))*($lvl/100))+5)*$har['speed'];
   $poke_nam = $tag_a.$pok['title'].$dop_name.$tag_b;
   insert('pok_user',array(
         'users'=>$uses,
         'basenum'=>$pok['id'],
         'names'=>$poke_nam,
         'active'=>$active,
         'evcount'=>'0',
         'lvl'=>$lvl,
         'sex'=>$sex,
         'har'=>$har['id_har'],
         'hp_my'=>$hp,
         'hp_max'=>$hp,
         'exp'=>level_exp($lvl-1),
         'exp_b'=>level_exp($lvl+1),
         'atk'=>$atk,
         'def'=>$def,
         'satk'=>$satk,
         'sdef'=>$sdef,
         'speed'=>$speed,
         'hp_ev'=>'0',
         'atk_ev'=>'0',
         'def_ev'=>'0',
         'satk_ev'=>'0',
         'sdef_ev'=>'0',
         'speed_ev'=>'0',
         'hp_iv'=>$iv1,
         'atk_iv'=>$iv2,
         'def_iv'=>$iv3,
         'satk_iv'=>$iv4,
         'sdef_iv'=>$iv5,
         'speed_iv'=>$iv6,
         'tips'=>$tip,
         'startepoke'=>'0',
         'startone'=>$prod,
         'reproduction'=>$sparka,
         'happy'=>rand(1,10),
         'datemay'=>date('Y-m-d H:i:s'),
         'usersone'=>$uses));
}
function pokemonEggPluse($uses,$id){
  $x = first('SELECT * FROM eggs WHERE id_egg=%d AND users_egg=%d AND dtime<=%d',$id,$uses,time());
  $r = false;
  if(!empty($x['id_egg'])){
    if($x['tips'] == 'normal'){
      $dop_name = '';
      $tag_a = '';
      $tag_b = '';
      $tip = 'normal'; 
    }else{
      $dop_name = ' - <b>Shiny</b>';
      $tag_a = '<span class="pokesShiny">';
      $tag_b = '</span>';
      $tip = 'shine';        
    }
     $atc  = $x['attac_one'];
     $ivHp = $x['hp_iv'];
     $ivAt = $x['atk_iv'];
     $ivDf = $x['def_iv'];
     $ivSa = $x['satk_iv'];
     $ivSd = $x['sdef_iv'];
     $ivSp = $x['speed_iv'];
     $uses = $x['users_egg'];
     $reproduction = (isset($x['spar'])?$x['spar']:0);
     $ev = 0;
     $lvl = 1;
     $har_k = rand(1,26);
     $har   = first('SELECT * FROM har WHERE id_har=%d',$har_k);
     $sex   = rand(1,2);
     $pok   = first('SELECT * FROM poke_base WHERE id=%d LIMIT 1',$x['base_id_egg']);
     $hp    = (($ivHp+($pok['hp']*2)+($ev/4)+100)*($lvl/100))+10;
     $atk   = ((($ivAt+($pok['atk']*2)+($ev/4))*($lvl/100))+5)*$har['atk'];
     $def   = ((($ivDf+($pok['def']*2)+($ev/4))*($lvl/100))+5)*$har['def'];
     $satk  = ((($ivSa+($pok['satk']*2)+($ev/4))*($lvl/100))+5)*$har['satk'];
     $sdef  = ((($ivSd+($pok['sdef']*2)+($ev/4))*($lvl/100))+5)*$har['sdef'];
     $speed = ((($ivSp+($pok['speed']*2)+($ev/4))*($lvl/100))+5)*$har['speed'];
     $poke_nam = $tag_a.$pok['title'].$dop_name.$tag_b;
     $r = insert('pok_user',array(
           'users'=>$uses,
           'basenum'=>$pok['id'],
           'names'=>$poke_nam,
           'active'=>'0',
           'evcount'=>'0',
           'lvl'=>$lvl,
           'sex'=>$sex,
           'har'=>$har['id_har'],
           'hp_my'=>$hp,
           'hp_max'=>$hp,
           'exp'=>'0',
           'exp_b'=>'0',
           'atk'=>$atk,
           'def'=>$def,
           'satk'=>$satk,
           'sdef'=>$sdef,
           'speed'=>$speed,
           'hp_ev'=>'0',
           'atk_ev'=>'0',
           'def_ev'=>'0',
           'satk_ev'=>'0',
           'sdef_ev'=>'0',
           'speed_ev'=>'0',
           'hp_iv'=>$ivHp,
           'atk_iv'=>$ivAt,
           'def_iv'=>$ivDf,
           'satk_iv'=>$ivSa,
           'sdef_iv'=>$ivSd,
           'speed_iv'=>$ivSp,
           'tips'=>$tip,
           'startepoke'=>'0',
           'reproduction'=>$reproduction,
           'happy'=>'0',
           'datemay'=>date('Y-m-d H:i:s'),
           'usersone'=>$uses));
        if($atc > 0 && $r == true){
          $ap = first('SELECT atac_pp FROM attac_power WHERE atac_id=%d',$atc);    
          if(!empty($ap['atac_pp'])){
              $pp = $ap['atac_pp'];
              insert('attac_my_poke',array(
                     'pok_id'=>$r,
                     'a_id'=>$atc,
                     'a_pp_min'=>$pp,
                     'a_pp_max'=>$pp));
          }
       }
  }
return $r;
}
/** --- Работа с покемонами *Конец* --- **/

/** Работа с атаками **/
function name_atc($x,$id,$tip=false){
  $b = false;
  if($x == '1')  $b = 'a_id';
  if($x == '2')  $b = 'b_id';
  if($x == '3')  $b = 'c_id';
  if($x == '4')  $b = 'd_id';
    $a = first('SELECT atac_name, apw.atac_tip FROM attac_my_poke apu inner join attac_power apw on apu.'.$b.'=apw.atac_id where apu.pok_id=%d AND apu.'.$b.'>0',$id);
   if(!$a) $x01 = false;
   if($tip == false) { 
    $x01 = $a['atac_name']; 
   }else{ 
    $x01 = $a['atac_tip']; 
   }
 return $x01;
}
function name_atc_pve($x,$id,$zap){
  if($x == '1')  $b = 'a_id';
  if($x == '2')  $b = 'b_id';
  if($x == '3')  $b = 'c_id';
  if($x == '4')  $b = 'd_id';
    $a = first('SELECT '.$zap.' FROM attac_my_poke apu inner join attac_power apw on apu.'.$b.'=apw.atac_id where apu.pok_id=%d AND apu.'.$b.'>0',$id);
      if(!$a)$a[$zap] = false;
 return $a[$zap];
}
function atac_pp($x,$id){
  if($x == '1') { $b = 'a_pp_min'; $bb = 'a_pp_max';}
  if($x == '2') { $b = 'b_pp_min'; $bb = 'b_pp_max';}
  if($x == '3') { $b = 'c_pp_min'; $bb = 'c_pp_max';}
  if($x == '4') { $b = 'd_pp_min'; $bb = 'd_pp_max';}
   $a = first('SELECT '.$b.', '.$bb.' FROM attac_my_poke where pok_id=%d',$id);
   $art = $a[$b].'/'.$a[$bb]; 
    if(!$a)$art = false;
 return $art;
}
function atac_pp_isset($x,$id){
  if($x == '1') { $b = 'a_pp_min'; $bb = 'a_pp_max';}
  if($x == '2') { $b = 'b_pp_min'; $bb = 'b_pp_max';}
  if($x == '3') { $b = 'c_pp_min'; $bb = 'c_pp_max';}
  if($x == '4') { $b = 'd_pp_min'; $bb = 'd_pp_max';}
   $a = first('SELECT '.$b.', '.$bb.' FROM attac_my_poke where pok_id=%d',$id);
   $art = $a[$b]; 
    if(!$a)$art = false;
 return $art;
}
function update_pp($x,$id){
  if($x != 'not'){
    if($x == '1') { $b = 'a_pp_min'; $bb = 'a_pp_max';}
    if($x == '2') { $b = 'b_pp_min'; $bb = 'b_pp_max';}
    if($x == '3') { $b = 'c_pp_min'; $bb = 'c_pp_max';}
    if($x == '4') { $b = 'd_pp_min'; $bb = 'd_pp_max';}
     $a = first('SELECT '.$b.', '.$bb.' FROM attac_my_poke where pok_id=%d',$id);
      if(!$a)$a[$b] = '1';
      $r = $a[$b] - 1;
      if($r < 0) { $r = 0;} $art = update('attac_my_poke',array($b=>$r),'pok_id='.(int)$id);
  }else{
   $art = false;
  }
 return $art;
}
/** ---- Работа с атаками *конец* ---- **/


/** --- Работа с итемами --- **/
function questDro($q,$p,$g){
    if($p > 0) $p = ' process='.$p.' AND '; else $p = ' '; 
    $a = first('SELECT * FROM quest WHERE '.$p.' quest_id=%d  AND user_id=%d AND gotov=%d',$q,$_SESSION['id'],$g);
    if(!empty($a['id'])) $a = true; else $a = false;
  return $a;
}
function questPoke($q,$num){
  $a = first('SELECT id FROM quest_poke WHERE questid=%d AND userid=%d AND pokenum=%d AND coolpokemin<coolpokemax',$q,$_SESSION['id'],$num);
  if(!empty($a['id'])) $a = $a['id']; else $a = false;
 return $a;
}
function updateQustPoke($id,$tip,$idPoke=false){
  if($idPoke == 2000000000 && $tip == 144){
     quest_update(2, 8, 0);
  }
  query('UPDATE quest_poke SET coolpokemin=coolpokemin+1 WHERE id=%d AND pokenum=%d AND userid=%d',$id,$tip,$_SESSION['id']);
}
function updateQuestPoint($point,$user=false){
  if($user == false) $user = $_SESSION['id'];
  $z = info_uses($user,'rang_c');
  $c = $z - $point; 
  if($c <= 0) $c = 0;
  return update('users',array('rang_c'=>$c),'id='.(int)$user);
}
function provQuestPoint($point,$user=false){
  if($user == false) $user = $_SESSION['id'];
  $z = info_uses($user,'rang_c'); 
  $point = ($z >= $point?true:false);
  return  $point;
}
function coolseitems($it_id,$idusers){
  $items_result = first('SELECT count FROM items_users WHERE user_id=%d AND item_id=%d',$idusers,$it_id);
  if($items_result['count'] > 0){
    $res = $items_result['count'];
  }else{
    $res = false;
  }
return $res;
}
function provitems($it_id,$cool,$users=false){
  if(empty($users)) $users = $_SESSION['id']; 
  $i = first('SELECT count,dattimer FROM items_users WHERE user_id=%d AND item_id=%d',$users,$it_id);
  if($i['dattimer'] == 'not'){
    return ($i['count'] >= $cool?true:false);
  }else{
    if($i['dattimer'] > time()) return ($i['count'] >= $cool?true:false);
    else return false;
  }
 return false; 
}
function nalog_clanz($money){
  $a = first('SELECT cool_many FROM settings_zax WHERE id_struc=1');
  $b = $a['cool_many'] + $money;
  update('settings_zax',array('cool_many'=>$b),'id_struc=1');
}
function infoItems($id,$zap){
  $a = first('SELECT '.$zap.' FROM  items WHERE id=%d',$id);
   if(!$a) $art = false; else  $art = $a[$zap];
 return $art;
}
function infoPokeBase($id,$zap){
  $a = first('SELECT '.$zap.' FROM poke_base WHERE id=%d',$id);
   if(!$a) $art = false; else  $art = $a[$zap];
 return $art;
}
/** --- Работа с итемами *конец* --- **/



/** --- Работа с квестами --- **/
function quest_process($id, $process){
  if($process == 0)  $a = true;
   else{
      $q = first('SELECT process FROM quest WHERE user_id=%d AND quest_id=%d AND gotov=0',$_SESSION['id'],$id);
      $a = (!empty($q) && isset($q['process']) && (int)$q['process'] === (int)$process);
  }
 return $a;
}
function isset_qest($id){
  $qest = first('SELECT quest_id FROM quest WHERE user_id=%d AND quest_id=%d',$_SESSION['id'],$id);
  $a = (!empty($qest) && !empty($qest['quest_id']));
   return $a;
}
function info_qest($id,$tip){
  $qest = first('SELECT '.$tip.' FROM quest WHERE user_id=%d AND quest_id=%d',$_SESSION['id'],$id);
  $a = (!empty($qest) && isset($qest[$tip]) && $qest[$tip] ? $qest[$tip] : false);
 return $a;
}
function quest_update($id, $proc, $gotov){
  $a = query('UPDATE quest SET process=%d, gotov=%d WHERE user_id=%d AND quest_id=%d',$proc,$gotov,$_SESSION['id'],$id);
 return $a; 
}

 
/** --- Работа с квестами *КОНЕЦ* --- **/ 
/** --- Работа с дополнениямм --- **/
function get($a,$tip=false){
  $q = array();
  $q['false'] = array('exp'=>0, 'drop'=>0, 'happy'=>0, 'energy'=>0, 'maney'=>0);
  
  if($tip == 'normal'){
  // Нормальные статусы пользователя
    $q[1] = array('exp'=>0, 'drop'=>0, 'happy'=>0, 'energy'=>0, 'maney'=>0); // Нету...
  }
  elseif($tip == 'uniq'){
  // Уникальные статусы пользователя
    $q[1] = array('exp'=>0, 'drop'=>0, 'happy'=>0, 'energy'=>0, 'maney'=>0); // Min.  Мешочек с добавками
    $q[2] = array('exp'=>0, 'drop'=>0, 'happy'=>0, 'energy'=>0, 'maney'=>0); // Сред. Мешочек с добавками
    $q[3] = array('exp'=>0, 'drop'=>0, 'happy'=>0, 'energy'=>0, 'maney'=>0); // Макс. Мешочек с добавками
  }
  elseif($tip == 'pnormal'){
  // Нормальные статусы покемона
    $q[1] = array('exp'=>0.2, 'drop'=>0, 'happy'=>0, 'energy'=>0, 'maney'=>0); // Сушеный клевер
    $q[2] = array('exp'=>0.4, 'drop'=>0, 'happy'=>0.2, 'energy'=>0, 'maney'=>0); // Клевер
    $q[3] = array('exp'=>0.6, 'drop'=>0, 'happy'=>0.4, 'energy'=>0, 'maney'=>0); // Свежий клевер
    $q[4] = array('exp'=>1.5, 'drop'=>0, 'happy'=>0, 'energy'=>0, 'maney'=>0); // Небольшой мешочек с добавками
    $q[5] = array('exp'=>2, 'drop'=>0, 'happy'=>0, 'energy'=>0, 'maney'=>0); // Мешочек с добавками
    $q[6] = array('exp'=>2.5, 'drop'=>0, 'happy'=>0, 'energy'=>0, 'maney'=>0); // Большой мешочек с добавками
    $q[7] = array('exp'=>2.5, 'drop'=>0, 'happy'=>1.5, 'energy'=>0, 'maney'=>0); // Огромный мешочек с добавками
  }
  elseif($tip == 'puniq'){
  // Уникальные статусы покемона
    $q[1] = array('exp'=>0, 'drop'=>0, 'happy'=>0, 'energy'=>0, 'maney'=>0);
  }
  elseif($tip == 'item'){
  // Статусы итемов
    $q[1] = array('exp'=>0, 'drop'=>0, 'happy'=>0, 'energy'=>0, 'maney'=>0); // Амулет удачи.
  }
  elseif($tip == 'happy'){
    $q[1] = array('exp'=>0.1,  'drop'=>0, 'happy'=>0, 'energy'=>0, 'maney'=>0.1);
    $q[2] = array('exp'=>0.15, 'drop'=>0, 'happy'=>0, 'energy'=>0, 'maney'=>0.2);
    $q[3] = array('exp'=>0.2,  'drop'=>0, 'happy'=>0, 'energy'=>0, 'maney'=>0.3);
    $q[4] = array('exp'=>0.25, 'drop'=>0, 'happy'=>0, 'energy'=>0, 'maney'=>0.35);
    $q[5] = array('exp'=>0.3,  'drop'=>0, 'happy'=>0, 'energy'=>0, 'maney'=>0.4);
    $q[6] = array('exp'=>0.35, 'drop'=>0, 'happy'=>0, 'energy'=>0, 'maney'=>0.45);
    $q[7] = array('exp'=>0.4,  'drop'=>0, 'happy'=>0, 'energy'=>0, 'maney'=>0.5);
    $q[8] = array('exp'=>0.45, 'drop'=>0, 'happy'=>0, 'energy'=>0, 'maney'=>0.55);
    $q[9] = array('exp'=>0.5,  'drop'=>0, 'happy'=>0, 'energy'=>0, 'maney'=>0.60);
    $q[10] = array('exp'=>0.6, 'drop'=>0, 'happy'=>0, 'energy'=>0, 'maney'=>0.70);
  }     
 return (!empty($q[$a])?$q[$a]:$q['false']);
}

function statususers(){
 delete(USER_EVENT,'userid='.(int)$_SESSION['id'].' AND  time<='.(int)time());
 $q = first('SELECT status FROM '.USER_EVENT.' WHERE userid=%d AND time>%d AND uniq=0',$_SESSION['id'],time());
 $u = first('SELECT status FROM '.USER_EVENT.' WHERE userid=%d AND time>%d AND uniq=1',$_SESSION['id'],time());
 $x = ($q['status']?$q['status']:false);
 $z = ($u['status']?$u['status']:false);
 $s = array('exp'=>0, 'drop'=>0, 'happy'=>0, 'energy'=>0, 'maney'=>0);
  if($x){
     $x01 = get($x,'normal'); 
     $s = array('exp'   =>$s['exp']   +$x01['exp'], 
                'drop'  =>$s['drop']  +$x01['drop'], 
                'happy' =>$s['happy'] +$x01['happy'], 
                'energy'=>$s['energy']+$x01['energy'], 
                'maney' =>$s['maney'] +$x01['maney']);
  }
  if($z){
     $x01 = get($z,'uniq'); 
     $s = array('exp'   =>$s['exp']   +$x01['exp'], 
                'drop'  =>$s['drop']  +$x01['drop'], 
                'happy' =>$s['happy'] +$x01['happy'], 
                'energy'=>$s['energy']+$x01['energy'], 
                'maney' =>$s['maney'] +$x01['maney']);
  }
 return $s; 
}
function statuspokemon($id,$item=false){
 delete(POKE_EVENT,'pokeid='.(int)$id.' AND  time<='.(int)time());
 $q = first('SELECT status FROM '.POKE_EVENT.' WHERE pokeid=%d AND time>%d AND uniq=0',$id,time());
 $u = first('SELECT status FROM '.POKE_EVENT.' WHERE pokeid=%d AND time>%d AND uniq=1',$id,time());
 $x = ($q['status']?$q['status']:false);
 $z = ($u['status']?$u['status']:false);
 $w = ($item>0?$item:false);
 $s = array('exp'=>0, 'drop'=>0, 'happy'=>0, 'energy'=>0, 'maney'=>0);
  if($x){
     $x01 = get($x,'pnormal'); 
     $s = array('exp'   =>$s['exp']   +$x01['exp'], 
                'drop'  =>$s['drop']  +$x01['drop'], 
                'happy' =>$s['happy'] +$x01['happy'], 
                'energy'=>$s['energy']+$x01['energy'], 
                'maney' =>$s['maney'] +$x01['maney']);
  }
  if($z){
     $x01 = get($z,'puniq'); 
     $s = array('exp'   =>$s['exp']   +$x01['exp'], 
                'drop'  =>$s['drop']  +$x01['drop'], 
                'happy' =>$s['happy'] +$x01['happy'], 
                'energy'=>$s['energy']+$x01['energy'], 
                'maney' =>$s['maney'] +$x01['maney']);
  }
  if($w){
     $x01 = get($w,'item'); 
     $s = array('exp'   =>$s['exp']   +$x01['exp'], 
                'drop'  =>$s['drop']  +$x01['drop'], 
                'happy' =>$s['happy'] +$x01['happy'], 
                'energy'=>$s['energy']+$x01['energy'], 
                'maney' =>$s['maney'] +$x01['maney']);
  }
 return $s;
}
function eventhappy($happty){
     $s = array('exp'=>0, 'drop'=>0, 'happy'=>0, 'energy'=>0, 'maney'=>0);
     $x01 = get('false');
          if($happty >= 10 && $happty < 20) $x01 = get(1,'happy');
      elseif($happty >= 20 && $happty < 30) $x01 = get(2,'happy');
      elseif($happty >= 30 && $happty < 40) $x01 = get(3,'happy');
      elseif($happty >= 40 && $happty < 50) $x01 = get(4,'happy');
      elseif($happty >= 50 && $happty < 60) $x01 = get(5,'happy');
      elseif($happty >= 60 && $happty < 70) $x01 = get(6,'happy');
      elseif($happty >= 70 && $happty < 80) $x01 = get(7,'happy');
      elseif($happty >= 80 && $happty < 90) $x01 = get(8,'happy');
      elseif($happty >= 90 && $happty < 100) $x01 = get(9,'happy');
      elseif($happty >= 100) $x01 = get(10,'happy');     
     $s = array('exp'   =>$s['exp']   +$x01['exp'], 
                'drop'  =>$s['drop']  +$x01['drop'], 
                'happy' =>$s['happy'] +$x01['happy'], 
                'energy'=>$s['energy']+$x01['energy'], 
                'maney' =>$s['maney'] +$x01['maney']);
 return $s; 
}
function eventserver(){
 $date = date('Y-m-d H:i:s'); 
 $q = first('SELECT * FROM server_events WHERE id=1 AND dateone < "%s" AND dateend > "%s"',$date,$date);
 $x1 = array('exp'=>$q['exp'], 'drop'=>$q['drop'], 'happy'=>$q['happy'], 'energy'=>$q['energy'], 'maney'=>$q['maney']);
 $x2 = array('exp'=>1, 'drop'=>1, 'happy'=>1, 'energy'=>1, 'maney'=>1);
 return (!empty($q)?$x1:$x2); 
}
/** --- Работа с дополнениямм  *КОНЕЦ* --- **/

/** --- Прочее --- **/
function shtraf($login,$maney,$pricina){
  $login   = obr_txt($login);
  $maney   = obr_chis($maney);               
  $pricina = obr_txt($pricina);
  
  if(empty($login) || empty($pricina) || empty($maney)) die("<script>parent.mess_chat(\"".sistemChat("Чего - то не хватает.")."\"); document.location.href = document.location.href;</script>");
  $a = first('SELECT id FROM users WHERE login = "%s"',$login);
  if(empty($a['id'])) die("<script>parent.mess_chat(\"".sistemChat("Пользователя: ".$login.", несуществует.")."\");document.location.href = document.location.href;</script>");
  if(!provitems(1,$maney,$a['id'])) die("<script>parent.mess_chat(\"".sistemChat("У пользователя: ".$login.", не достаточно денег.")."\");document.location.href = document.location.href;</script>");         
   minus_item($maney,1,$a['id']);
   //query('UPDATE usersunictable SET coolday=coolday-1 WHERE id=%d AND coolday >= 1',$a['id']);
  $text = "<span style=color:7df9ff;>Пользователю: <b>".$login."</b>, выписан штраф на сумму: ".formatnum($maney)." монет. Причина штрафа: ".$pricina.".</span>"; 
  $o    = 'удачно оштрафован';
  text_chats($text,1);   
  die("<script>parent.mess_chat(\"".sistemChat("Пользователь: ".$login.", ".$o.".")."\");document.location.href = document.location.href;</script>"); 
}
?>
