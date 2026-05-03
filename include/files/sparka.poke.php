<?php
if (!empty($_GET['newpok']) && !empty($_GET['to_tren'])){
  if($myrow['pvp'] == 1 || $myrow['pve'] == 1 || $myrow['trade'] > 0) die("<script>window.location.href='/game.php?go=char';</script>");
  if(!empty($_GET['exitspar'])){
    delete('sparka','user_1='.(int)$_SESSION['id'].' OR user_2='.(int)$_SESSION['id']);
    die("<script>parent._location.location.href='/game.php?go=char';</script>");
  }
  delete('sparka','time<='.(int)time());
  $treners = obr_txt($_GET['to_tren']);
  $nw      = obr_chis($_GET['newpok']);
  $oke     = true;
  $textWorld = false;
  $exits   = '<br><a href="/game.php?go=char&newpok='.$nw.'&to_tren='.$treners.'&exitspar=true" target="_chat_two"><- Уйти</a>';
  $exits1   = '<br><a href="/game.php?go=char&newpok='.$nw.'&to_tren='.$treners.'&exitspar=true" target="_chat_two"><- Отказаться от разведения</a>';
  $sp = first('SELECT * FROM sparka WHERE user_2=%d OR user_1=%d',$_SESSION['id'],$_SESSION['id']);
  if(!empty($sp['id']) && $nw == 1)  die("<script>window.location.href='/game.php?go=char&newpok=2&to_tren=true&id=".$sp['id']."';</script>");
  if(!empty($_GET['exitspark']) && !empty($sp['id'])){
    if($sp['no'] != 5){
      $upT = time()+12;
      update('sparka',array('time'=>$upT, 'no'=>5),'id='.(int)$sp['id']);
    }
    die("<script>parent._location.location.href='/game.php?go=char';</script>");
  }
  function noHref($to=false,$text=false){
    if($text != false) print "<script>parent.mess_chat(\"".sistemChat($text)."\");</script>";
    if($to == false) $to = 'char';
    die ("<script>parent._location.location.href='/game.php?go=".$to."';</script>");
  }
  function pokeInf($id,$zap,$user=false){
   if($user != false) $user = 'AND users='.$user; else $user = '';
    $q = first('SELECT '.$zap.' FROM pok_user WHERE id=%d AND active=1 AND reproduction=0 AND startone=0 '.$user,$id);
     if(empty($q[$zap])) $a = false; else $a = $q[$zap];
   return $a;
  }
  function isset_trener($id,$tip=false){
    global $treners, $nw;
    if($tip != false) $to = first('SELECT online,login,buildmy,id,pve,pvp,trade FROM users WHERE login="%s"',$id);
      else            $to = first('SELECT online,login,buildmy,id,pve,pvp,trade FROM users WHERE id=%d',$id); 
    $ya = first('SELECT online,login,buildmy,id,pve,pvp FROM users WHERE id=%d',$_SESSION['id']);
    if($tip != false) $sp = first('SELECT id FROM sparka WHERE user_2=%d OR user_1=%d',$id,$id);
    $ok = true;
    $t  = false;
    if(empty($to['id']))                                   { $t = 'Тренеру, которому предлагается разведение, не существует!'; $ok = false;}
    if($tip != false){if(!empty($sp['id']) && $ok == true) { $t = 'Тренеру, которому предлагается разведение, занят!'; $ok = false;} }
    if($to['id']       == $_SESSION['id']  && $ok == true) { $t = 'Вы не можите предлагать разведение самому себе.'; $ok = false;}
    if($to['online']   != 1                && $ok == true) { $t = 'Тренер: '.$to['login'].' в данный момент находится Оффлайн.'; $ok = false;}
    if($to['buildmy']  != $ya['buildmy']   && $ok == true) { $t = 'Выши локации с тренером: <b>'.$to['login'].'</b> не совпадают.'; $ok = false;}
    if($to['pve'] == 1 || $to['pvp'] == 1 || $to['trade']>0  && $ok == true) { $t = 'Тренер: '.$to['login'].' занят.'; $ok = false;} 
    if($ok == false) $t .= '<br><a href="/game.php?go=char&newpok='.$nw.'&to_tren='.$treners.'&exitspar=true" target="_chat_two"><- Уйти</a>'; 
    $x = array('txt'=> $t, 'isset'=> $ok, 'idtreners'=>$to['id']);
   return $x;                                                                             
  }                                                                                     
  if($nw == 1 && empty($sp['id'])){
    $x = isset_trener($treners,'true');       
    if($x['isset'] == false) { $textWorld = $x['txt'];  $oke = false;}
  }
  if($nw > 1 && !empty($sp['id'])){
    $treners = ($sp['user_1'] == $_SESSION['id']?$sp['user_2']:$sp['user_1']);    
    $x = isset_trener($treners);       
    if($x['isset'] == false) { $textWorld = $x['txt'];  $oke = false;}
  } 
  if($nw == 1 && $oke == true && empty($sp['id'])){    
    $textWorld .= '<font color = "#551A8B" size = "5"><b>Выберите покемона для разведения:</b></font><br>';
    $pokesMy = select('SELECT basenum,id,names,lvl FROM pok_user WHERE users=%d AND active=1 AND startone=0 AND reproduction=0 ORDER BY basenum ASC',$_SESSION['id']);
    if($pokesMy){
      foreach($pokesMy as $pokes_while){
        if($pokes_while['basenum'] > 493) { $a = 'png';} else { $a = 'gif'; }
        $textWorld .= '<img src="pok/anim/'.$pokes_while['basenum'].'.'.$a.'"><b><a href="game.php?go=char&newpok=1&to_tren='.$treners.'&pokes='.$pokes_while['id'].'" target="_chat_two">#'.$pokes_while['names'].' '.$pokes_while['lvl'].' - lvl</a></b><br>';
      }
      $textWorld .= $exits1;
    }else{
       $textWorld .= '<font color=brown><b>Доступных покемонов для разведения нет!</b></font>'.$exits;
    }
    if(!empty($_GET['pokes'])){
       $b = $_GET['pokes'];               
       $x01 = pokeInf($b,'id',$_SESSION['id']);
       if(empty($x01)) noHref('','Нет доступных покемонов для разведения.');
        $out_sp = time() +(60*3);
        $id_sp  = insert('sparka',array(
                         'time'=>$out_sp,
                         'poke_1'=>$x01,
                         'poke_2'=>0,
                         'user_1'=>$_SESSION['id'],
                         'user_2'=>$x['idtreners']));
       die("<script>parent._location.location.href='/game.php?go=char&newpok=2&to_tren=true&id=".$id_sp."';</script>");
    }
  }
  if($nw == 2 && $oke == true && !empty($sp['id'])){
    if($sp['user_1'] == $_SESSION['id'] && $sp['tip'] == false){
      $p01 = pokeInf($sp['poke_1'],'basenum');
      $p02 = pokeInf($sp['poke_1'],'names');
      $format = ($p01>493?'png':'gif');
      $textWorld .= 'Вы предложили тренеру: '.color_group_users($sp['user_2']).' разведение покемонов: <img src="pok/anim/'.$p01.'.'.$format.'"> <font color=#551A8B><b>#'.$p02.'</b></font>.';
      $textWorld .= '<br><br><b>Ожидание ответа...</b><br><a href="/game.php?go=char&newpok='.$nw.'&to_tren='.$treners.'&exitspark=true" target="_chat_two"><- Уйти</a>';
    }
    if($sp['user_2'] == $_SESSION['id'] && $oke == true && $sp['tip'] == false){
      $p01 = pokeInf($sp['poke_1'],'basenum');
      $p02 = pokeInf($sp['poke_1'],'names');
      $p03 = pokeInf($sp['poke_1'],'sex');
      $pol = ($p03 == 1?'М':'Д');
      $polNo = ($p03 == 1?2:1);
      $format = ($p01>493?'png':'gif');
      $textWorld .= 'Тренер: '.color_group_users($sp['user_1']).' предлагает Вам разведение покемонов: <img src="pok/anim/'.$p01.'.'.$format.'"> <b>№'.$p01.'</b>.';
      $textWorld .= '<br><b>Предлагаемый покемон: </b><img src="pok/anim/'.$p01.'.'.$format.'"> <font color=#551A8B><b>#'.$p02.'('.$pol.') - ID: '.$sp['poke_1'].'</b></font>.';
      
      $pokes_select = select('SELECT basenum,sex,id,names,lvl FROM pok_user WHERE users=%d AND active=1 AND startone=0 AND reproduction=0 AND basenum=%d AND sex=%d ORDER BY id ASC',$_SESSION['id'],$p01,$polNo);
      if(empty($pokes_select)){
        $textWorld .= '<br><br><font color="green"><b>У Вас нет покемонов подобного типа, либо противоположного пола.</b></font>';
      }else{
        $textWorld .= '<br><font color="green"><b>Выберите покемона:</b></font><br>';
         foreach($pokes_select as $pokes_select_s){
           $base = $pokes_select_s['basenum']; 
           $img = ($base > 493?'png':'gif');
           $pol = ($pokes_select_s['sex'] == 1?'М':'Д');
           $textWorld .= '<img src="pok/anim/'.$base.'.'.$img.'"><b><a href="/game.php?go=char&newpok=3&to_tren='.$treners.'&id='.$sp['id'].'&pokesnew='.$pokes_select_s['id'].'" target="_chat_two">'.$pokes_select_s['names'].' '.$pokes_select_s['lvl'].' - lvl</a> ('.$pol.')</b> ID: '.$pokes_select_s['id'].'<br>'; 
         }
      }
    }
    if($sp['tip'] > 0){
       if($sp['no'] != 5){
        $upT = time()+15;
        update('sparka',array('time'=>$upT, 'no'=>5),'id='.(int)$sp['id']);
       } 
       delete('sparka','time<='.(int)time());
       $textWorld .= '<h2>Результат о разведении:</h2>';
       $textWorld .= '<div style="color:green;font-weight:bold;">'.$sp['mess'].'</div>';
       $textWorld .= '<br><a href="/game.php?go=char"><- Уйти</a>';
    }
  }
  if(!$sp['id'] &&  $textWorld == false) $textWorld .= '<br><a href="/game.php?go=char"><- Уйти</a>';
  if($nw == 3 && $oke == true && !empty($sp['id']) && !empty($_GET['pokesnew']) && $sp['user_2'] == $_SESSION['id']){ 
    $pou = obr_chis($_GET['pokesnew']);
    $sparka  = first('SELECT * FROM sparka WHERE id=%d AND user_2=%d',$_GET['id'],$_SESSION['id']);
    if(empty($sparka['id'])){
     noHref('char&newpok=2&to_tren='.$treners.'&id='.$sparka['id'],'Систематическая ошибка. Попробуйте снова.');
     die();
    }
    $msSp = false;
    $sparkaId = $sparka['id'];
    $p01 = pokeInf($sparka['poke_1'],'basenum');
    $p02 = pokeInf($sparka['poke_1'],'sex');
    $sel = ($p02 == 1?'m_id='.$sparka['poke_1'].' AND d_id='.$pou.' ':'m_id='.$pou.' AND d_id='.$sparka['poke_1'].' ');    
    $pok_one = first('SELECT id,sex,hp_iv,atk_iv,def_iv,sdef_iv,satk_iv,speed_iv,users,tips,basenum,lvl,happy FROM pok_user WHERE id=%d AND users=%d AND active=1 AND startone=0 AND reproduction=0 AND basenum=%d',$sparka['poke_1'],$sparka['user_1'],$p01);
    $pok_two = first('SELECT id,sex,hp_iv,atk_iv,def_iv,sdef_iv,satk_iv,speed_iv,users,tips,basenum,lvl,happy FROM pok_user WHERE id=%d AND users=%d AND active=1 AND startone=0 AND reproduction=0 AND basenum=%d',$pou,$_SESSION['id'],$p01);
    if(empty($pok_one['id']) || empty($pok_two['id']) || empty($sparka['id'])){
     noHref('char&newpok=2&to_tren='.$treners.'&id='.$sparka['id'],'Систематическая ошибка. Попробуйте снова.');
     die();
    }
    $pSpark  = first('SELECT * FROM poke_spar WHERE '.$sel.' ');
    if(empty($pSpark['id'])){
      if($p02 == 1) { $pm = $sparka['poke_1']; $pd = $pou; } else { $pm = $pou; $pd = $sparka['poke_1']; }
       if(!empty($pm)) delete('poke_spar','m_id='.(int)$pm); 
       if(!empty($pd)) delete('poke_spar','d_id='.(int)$pd);
       $timSpark = time() +(60*60*24*1);
       insert('poke_spar',array('d_id'=>$pd, 'm_id'=>$pm, 'status'=>1, 'timer'=>$timSpark));
       $pSpark['status'] = 1;
       $pSpark['timer'] = $timSpark;
       $msSp = 'Ваши покемоны встертились впервые, и они удачно перешли на первую стадию отношений. <br> Через 24 часа Вы сможете попоробовать снова свести их вместе.';
       update('sparka',array('tip'=>1, 'mess'=>$msSp, 'no'=>4),'id='.(int)$sparkaId);
       die("<script>parent._location.location.href='/game.php?go=char&newpok=2&to_tren=true&id=".$sp['id']."';</script>");
    }
    if($pSpark['timer'] > time()){
       $msSp = 'Разведение не удалось! Ваши покемоны не готовы к этой встрече. '.timersOtshet($pSpark['timer']-1,"<br>Попробуйте снова через: ", "Готовы");
       update('sparka',array('tip'=>1, 'mess'=>$msSp, 'no'=>4),'id='.(int)$sparkaId);
       die("<script>parent._location.location.href='/game.php?go=char&newpok=2&to_tren=true&id=".$sp['id']."';</script>");
    }
    if($pSpark['status']  == 1){
       $timSpark = time() +(60*60*24*1);
       update('poke_spar',array('status'=>2, 'timer'=>$timSpark),'id='.(int)$pSpark['id']);
       $msSp = 'Ваши покемоны удачно перешли на вторую стадию отношений. '.timersOtshet($timSpark-1,"<br>Попробуйте свести вместе ваших покемонов через: ", "Готовы");
       update('sparka',array('tip'=>1, 'mess'=>$msSp, 'no'=>4),'id='.(int)$sparkaId);
       die("<script>parent._location.location.href='/game.php?go=char&newpok=2&to_tren=true&id=".$sp['id']."';</script>");
    }        
    if($pSpark['status']  == 2){
       $timSpark = time() +(60*60*24*1);
       $ra = rand(1,100);
       if($ra < 30){
         $pSpark['status'] = 3;
       }else{
         update('poke_spar',array('status'=>3, 'timer'=>$timSpark),'id='.(int)$pSpark['id']);
         $msSp = 'Ваши покемоны удачно перешли на третию стадию отношений. '.timersOtshet($timSpark-1,"<br>Попробуйте свести вместе ваших покемонов через: ", "Готовы");
         update('sparka',array('tip'=>1, 'mess'=>$msSp, 'no'=>4),'id='.(int)$sparkaId);
         die("<script>parent._location.location.href='/game.php?go=char&newpok=2&to_tren=true&id=".$sp['id']."';</script>");
       }
    }
    if($pSpark['status'] == 3){
       if($pok_one['sex'] == $pok_two['sex']){ 
         $msSp = 'Разведение не удалось! Пол покемонов должен быть разным!';
         update('sparka',array('tip'=>1, 'mess'=>$msSp, 'no'=>4),'id='.(int)$sparkaId);
         die("<script>parent._location.location.href='/game.php?go=char&newpok=2&to_tren=true&id=".$sp['id']."';</script>");
       }
       if($pok_one['happy'] < 15 || $pok_two['happy'] < 15){ 
         $msSp = 'Разведение не удалось! Счастье обноих покемонов, которых Вы желаете спарить, должно быть более 15%!';
         update('sparka',array('tip'=>1, 'mess'=>$msSp, 'no'=>4),'id='.(int)$sparkaId);
         die("<script>parent._location.location.href='/game.php?go=char&newpok=2&to_tren=true&id=".$sp['id']."';</script>");
       }
       if($pok_one['basenum'] != $pok_two['basenum']){
         $msSp = 'Разведение не удалось! Типы покемонов должны быть одинаковыми!';
         update('sparka',array('tip'=>1, 'mess'=>$msSp, 'no'=>4),'id='.(int)$sparkaId);
         die("<script>parent._location.location.href='/game.php?go=char&newpok=2&to_tren=true&id=".$sp['id']."';</script>");
       }
          if($pok_one['hp_iv']   >$pok_two['hp_iv'])    {$hp_iv    = $pok_one['hp_iv'];   } else {$hp_iv    = $pok_two['hp_iv'];   }
          if($pok_one['atk_iv']  >$pok_two['atk_iv'])   {$atk_iv   = $pok_one['atk_iv'];  } else {$atk_iv   = $pok_two['atk_iv'];  }
          if($pok_one['def_iv']  >$pok_two['def_iv'])   {$def_iv   = $pok_one['def_iv'];  } else {$def_iv   = $pok_two['def_iv'];  }
          if($pok_one['sdef_iv'] >$pok_two['sdef_iv'])  {$sdef_iv  = $pok_one['sdef_iv']; } else {$sdef_iv  = $pok_two['sdef_iv']; }
          if($pok_one['satk_iv'] >$pok_two['satk_iv'])  {$satk_iv  = $pok_one['satk_iv']; } else {$satk_iv  = $pok_two['satk_iv']; }
          if($pok_one['speed_iv']>$pok_two['speed_iv']) {$speed_iv = $pok_one['speed_iv'];} else {$speed_iv = $pok_two['speed_iv'];}
          if(rand(1,6) == 2){
              $hp_iv    = $hp_iv    + rand(0,2);
              $atk_iv   = $atk_iv   + rand(0,2);
              $def_iv   = $def_iv   + rand(0,2);
              $sdef_iv  = $sdef_iv  + rand(0,2);
              $satk_iv  = $satk_iv  + rand(0,2);
              $speed_iv = $speed_iv + rand(0,2);
          }
          $user_egg = ($pok_one['sex'] == 2?$pok_one['users']:$pok_two['users']);
          $tip_egg = "normal";
          $tip_egg_sms  = false;
          if($pok_one['tips']  == 'shine'  && $pok_two['tips'] == 'shine') $v = 600;
          if(($pok_one['tips'] == 'normal' && $pok_two['tips'] == 'shine') || ($pok_one['tips'] == 'shine' && $pok_two['tips'] == 'normal')) $v = 120;       
          if($pok_one['tips']  == 'normal' && $pok_two['tips'] == 'normal') $v = 1;
          if(rand(1,1000) <= $v) { $tip_egg = "shine"; $tip_egg_sms = " - <b>Shine<b>";}
          $days  = rand(8,14);
          $dtime = time() +(60*60*24*$days);
          $basaEgg = first('SELECT egg,id,title FROM poke_base WHERE id=%d',$pok_one['basenum']);
          $basaEggNam = first('SELECT title FROM poke_base WHERE id=%d',$basaEgg['egg']);
          if($basaEgg['egg'] <= 0){
            $msSp = 'Разведение не удалось! На данный момент разведение этих покемонов не возможно!';
            update('sparka',array('tip'=>1, 'mess'=>$msSp, 'no'=>4),'id='.(int)$sparkaId);
            die("<script>parent._location.location.href='/game.php?go=char&newpok=2&to_tren=true&id=".$sp['id']."';</script>");
          }
          $msSp = "Разведение прошло удачно, яйцо: #".$basaEggNam['title']." ".$tip_egg_sms." получает: ".color_group_users($user_egg);
          insert('eggs',array(
                 'base_id_egg'=>$basaEgg['egg'],
                 'hp_iv'=>$hp_iv,
                 'atk_iv'=>$atk_iv,
                 'def_iv'=>$def_iv,
                 'sdef_iv'=>$sdef_iv,
                 'satk_iv'=>$satk_iv,
                 'speed_iv'=>$speed_iv,
                 'users_egg'=>$user_egg,
                 'dtime'=>$dtime,
                 'tips'=>$tip_egg)); 
         $sparen_id = $pok_one['basenum'];
         if($sparen_id == '64' || $sparen_id == '67' || $sparen_id == '75' || $sparen_id == '93'){
                if($sparen_id == '64') { $sparen_id_evo = '65'; }
            elseif($sparen_id == '67') { $sparen_id_evo = '68'; }
            elseif($sparen_id == '75') { $sparen_id_evo = '76'; }
            elseif($sparen_id == '93') { $sparen_id_evo = '94'; }
            else $sparen_id_evo = false;    
            if($sparen_id_evo != false){
              $evo_poke = first('SELECT title,id FROM poke_base WHERE id=%d',$sparen_id_evo);
               if($pok_one['tips'] == 'shine'){
                  $dop_name_1 = '<span class="pokesShiny">'.$evo_poke['title'].' - <b>Shiny</b></font>';
               }else{
                  $dop_name_1 = $evo_poke['title'];
               }
               if($pok_two['tips'] == 'shine'){
                  $dop_name_2 = '<span class="pokesShiny">'.$evo_poke['title'].' - <b>Shiny</b></font>';
               }else{
                  $dop_name_2 = $evo_poke['title'];
               }
               update('pok_user',array('names'=>$dop_name_1, 'basenum'=>$evo_poke['id']),'id='.(int)$pok_one['id']);
               update('pok_user',array('names'=>$dop_name_2, 'basenum'=>$evo_poke['id']),'id='.(int)$pok_two['id']);
               stat_updates($pok_one['id'],$pok_one['lvl']);
               stat_updates($pok_two['id'],$pok_two['lvl']);
               $msSp .= ' <br>А так же, при разведении, ваши покемоны эволюционировали в #'.$evo_poke['title'];
            }  
         }
        update('pok_user',array('reproduction'=>1),'id='.(int)$pok_two['id']);
        update('pok_user',array('reproduction'=>1),'id='.(int)$pok_one['id']);
        delete('poke_spar','id='.(int)$pSpark['id']);
        update('sparka',array('tip'=>1, 'mess'=>$msSp, 'no'=>4),'id='.(int)$sparkaId);
        die("<script>parent._location.location.href='/game.php?go=char&newpok=2&to_tren=true&id=".$sp['id']."';</script>");
    }     
    die("<script>parent._location.location.href='/game.php?go=char&newpok=2&to_tren=true&id=".$sp['id']."';</script>");
  }

  print '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
          <html>
            <head>
              <meta HTTP-EQUIV="Content-Type" CONTENT="text/html; Charset=Windows-1251">
              <meta http-equiv="refresh" content="10"/>
              <link REL="Stylesheet" HREF="css/room.css" TYPE="text/css">
              <style>
                a:link    {text-decoration:none; color:#000000;}
                a:active  {text-decoration:none; color:#000000;}
                a:visited {text-decoration:none; color:#000000;}
                a:hover   {text-decoration:underline; color:#505050;}
              </style>
            </head>
            <body>
                <div class = "about" style="width:97%;height:90%;position:reletive;top:5px;left:6px;" align = "center">';

  print $textWorld;

  print    '    </div>
            </body>
          </html>';
 exit;
}
?>