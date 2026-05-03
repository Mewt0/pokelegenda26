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
  $exits   = '<br><a href="/game.php?go=char&newpok='.$nw.'&to_tren='.$treners.'&exitspar=true" target="_chat_two"><- Р Р€Р в„–РЎвЂљР С‘</a>';
  $exits1   = '<br><a href="/game.php?go=char&newpok='.$nw.'&to_tren='.$treners.'&exitspar=true" target="_chat_two"><- Р С›РЎвЂљР С”Р В°Р В·Р В°РЎвЂљРЎРЉРЎРѓРЎРЏ Р С•РЎвЂљ РЎР‚Р В°Р В·Р Р†Р ВµР Т‘Р ВµР Р…Р С‘РЎРЏ</a>';
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
    if(empty($to['id']))                                   { $t = 'Р СћРЎР‚Р ВµР Р…Р ВµРЎР‚РЎС“, Р С”Р С•РЎвЂљР С•РЎР‚Р С•Р СРЎС“ Р С—РЎР‚Р ВµР Т‘Р В»Р В°Р С–Р В°Р ВµРЎвЂљРЎРѓРЎРЏ РЎР‚Р В°Р В·Р Р†Р ВµР Т‘Р ВµР Р…Р С‘Р Вµ, Р Р…Р Вµ РЎРѓРЎС“РЎвЂ°Р ВµРЎРѓРЎвЂљР Р†РЎС“Р ВµРЎвЂљ!'; $ok = false;}
    if($tip != false){if(!empty($sp['id']) && $ok == true) { $t = 'Р СћРЎР‚Р ВµР Р…Р ВµРЎР‚РЎС“, Р С”Р С•РЎвЂљР С•РЎР‚Р С•Р СРЎС“ Р С—РЎР‚Р ВµР Т‘Р В»Р В°Р С–Р В°Р ВµРЎвЂљРЎРѓРЎРЏ РЎР‚Р В°Р В·Р Р†Р ВµР Т‘Р ВµР Р…Р С‘Р Вµ, Р В·Р В°Р Р…РЎРЏРЎвЂљ!'; $ok = false;} }
    if($to['id']       == $_SESSION['id']  && $ok == true) { $t = 'Р вЂ™РЎвЂ№ Р Р…Р Вµ Р СР С•Р В¶Р С‘РЎвЂљР Вµ Р С—РЎР‚Р ВµР Т‘Р В»Р В°Р С–Р В°РЎвЂљРЎРЉ РЎР‚Р В°Р В·Р Р†Р ВµР Т‘Р ВµР Р…Р С‘Р Вµ РЎРѓР В°Р СР С•Р СРЎС“ РЎРѓР ВµР В±Р Вµ.'; $ok = false;}
    if($to['online']   != 1                && $ok == true) { $t = 'Р СћРЎР‚Р ВµР Р…Р ВµРЎР‚: '.$to['login'].' Р Р† Р Т‘Р В°Р Р…Р Р…РЎвЂ№Р в„– Р СР С•Р СР ВµР Р…РЎвЂљ Р Р…Р В°РЎвЂ¦Р С•Р Т‘Р С‘РЎвЂљРЎРѓРЎРЏ Р С›РЎвЂћРЎвЂћР В»Р В°Р в„–Р Р….'; $ok = false;}
    if($to['buildmy']  != $ya['buildmy']   && $ok == true) { $t = 'Р вЂ™РЎвЂ№РЎв‚¬Р С‘ Р В»Р С•Р С”Р В°РЎвЂ Р С‘Р С‘ РЎРѓ РЎвЂљРЎР‚Р ВµР Р…Р ВµРЎР‚Р С•Р С: <b>'.$to['login'].'</b> Р Р…Р Вµ РЎРѓР С•Р Р†Р С—Р В°Р Т‘Р В°РЎР‹РЎвЂљ.'; $ok = false;}
    if($to['pve'] == 1 || $to['pvp'] == 1 || $to['trade']>0  && $ok == true) { $t = 'Р СћРЎР‚Р ВµР Р…Р ВµРЎР‚: '.$to['login'].' Р В·Р В°Р Р…РЎРЏРЎвЂљ.'; $ok = false;} 
    if($ok == false) $t .= '<br><a href="/game.php?go=char&newpok='.$nw.'&to_tren='.$treners.'&exitspar=true" target="_chat_two"><- Р Р€Р в„–РЎвЂљР С‘</a>'; 
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
    $textWorld .= '<font color = "#551A8B" size = "5"><b>Р вЂ™РЎвЂ№Р В±Р ВµРЎР‚Р С‘РЎвЂљР Вµ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В° Р Т‘Р В»РЎРЏ РЎР‚Р В°Р В·Р Р†Р ВµР Т‘Р ВµР Р…Р С‘РЎРЏ:</b></font><br>';
    $pokesMy = select('SELECT basenum,id,names,lvl FROM pok_user WHERE users=%d AND active=1 AND startone=0 AND reproduction=0 ORDER BY basenum ASC',$_SESSION['id']);
    if($pokesMy){
      foreach($pokesMy as $pokes_while){
        if($pokes_while['basenum'] > 493) { $a = 'png';} else { $a = 'gif'; }
        $textWorld .= '<img src="pok/anim/'.$pokes_while['basenum'].'.'.$a.'"><b><a href="game.php?go=char&newpok=1&to_tren='.$treners.'&pokes='.$pokes_while['id'].'" target="_chat_two">#'.$pokes_while['names'].' '.$pokes_while['lvl'].' - lvl</a></b><br>';
      }
      $textWorld .= $exits1;
    }else{
       $textWorld .= '<font color=brown><b>Р вЂќР С•РЎРѓРЎвЂљРЎС“Р С—Р Р…РЎвЂ№РЎвЂ¦ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р С•Р Р† Р Т‘Р В»РЎРЏ РЎР‚Р В°Р В·Р Р†Р ВµР Т‘Р ВµР Р…Р С‘РЎРЏ Р Р…Р ВµРЎвЂљ!</b></font>'.$exits;
    }
    if(!empty($_GET['pokes'])){
       $b = $_GET['pokes'];               
       $x01 = pokeInf($b,'id',$_SESSION['id']);
       if(empty($x01)) noHref('','Р СњР ВµРЎвЂљ Р Т‘Р С•РЎРѓРЎвЂљРЎС“Р С—Р Р…РЎвЂ№РЎвЂ¦ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р С•Р Р† Р Т‘Р В»РЎРЏ РЎР‚Р В°Р В·Р Р†Р ВµР Т‘Р ВµР Р…Р С‘РЎРЏ.');
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
      $textWorld .= 'Р вЂ™РЎвЂ№ Р С—РЎР‚Р ВµР Т‘Р В»Р С•Р В¶Р С‘Р В»Р С‘ РЎвЂљРЎР‚Р ВµР Р…Р ВµРЎР‚РЎС“: '.color_group_users($sp['user_2']).' РЎР‚Р В°Р В·Р Р†Р ВµР Т‘Р ВµР Р…Р С‘Р Вµ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р С•Р Р†: <img src="pok/anim/'.$p01.'.'.$format.'"> <font color=#551A8B><b>#'.$p02.'</b></font>.';
      $textWorld .= '<br><br><b>Р С›Р В¶Р С‘Р Т‘Р В°Р Р…Р С‘Р Вµ Р С•РЎвЂљР Р†Р ВµРЎвЂљР В°...</b><br><a href="/game.php?go=char&newpok='.$nw.'&to_tren='.$treners.'&exitspark=true" target="_chat_two"><- Р Р€Р в„–РЎвЂљР С‘</a>';
    }
    if($sp['user_2'] == $_SESSION['id'] && $oke == true && $sp['tip'] == false){
      $p01 = pokeInf($sp['poke_1'],'basenum');
      $p02 = pokeInf($sp['poke_1'],'names');
      $p03 = pokeInf($sp['poke_1'],'sex');
      $pol = ($p03 == 1?'Р Сљ':'Р вЂќ');
      $polNo = ($p03 == 1?2:1);
      $format = ($p01>493?'png':'gif');
      $textWorld .= 'Р СћРЎР‚Р ВµР Р…Р ВµРЎР‚: '.color_group_users($sp['user_1']).' Р С—РЎР‚Р ВµР Т‘Р В»Р В°Р С–Р В°Р ВµРЎвЂљ Р вЂ™Р В°Р С РЎР‚Р В°Р В·Р Р†Р ВµР Т‘Р ВµР Р…Р С‘Р Вµ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р С•Р Р†: <img src="pok/anim/'.$p01.'.'.$format.'"> <b>РІвЂћвЂ“'.$p01.'</b>.';
      $textWorld .= '<br><b>Р СџРЎР‚Р ВµР Т‘Р В»Р В°Р С–Р В°Р ВµР СРЎвЂ№Р в„– Р С—Р С•Р С”Р ВµР СР С•Р Р…: </b><img src="pok/anim/'.$p01.'.'.$format.'"> <font color=#551A8B><b>#'.$p02.'('.$pol.') - ID: '.$sp['poke_1'].'</b></font>.';
      
      $pokes_select = select('SELECT basenum,sex,id,names,lvl FROM pok_user WHERE users=%d AND active=1 AND startone=0 AND reproduction=0 AND basenum=%d AND sex=%d ORDER BY id ASC',$_SESSION['id'],$p01,$polNo);
      if(empty($pokes_select)){
        $textWorld .= '<br><br><font color="green"><b>Р Р€ Р вЂ™Р В°РЎРѓ Р Р…Р ВµРЎвЂљ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р С•Р Р† Р С—Р С•Р Т‘Р С•Р В±Р Р…Р С•Р С–Р С• РЎвЂљР С‘Р С—Р В°, Р В»Р С‘Р В±Р С• Р С—РЎР‚Р С•РЎвЂљР С‘Р Р†Р С•Р С—Р С•Р В»Р С•Р В¶Р Р…Р С•Р С–Р С• Р С—Р С•Р В»Р В°.</b></font>';
      }else{
        $textWorld .= '<br><font color="green"><b>Р вЂ™РЎвЂ№Р В±Р ВµРЎР‚Р С‘РЎвЂљР Вµ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В°:</b></font><br>';
         foreach($pokes_select as $pokes_select_s){
           $base = $pokes_select_s['basenum']; 
           $img = ($base > 493?'png':'gif');
           $pol = ($pokes_select_s['sex'] == 1?'Р Сљ':'Р вЂќ');
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
       $textWorld .= '<h2>Р В Р ВµР В·РЎС“Р В»РЎРЉРЎвЂљР В°РЎвЂљ Р С• РЎР‚Р В°Р В·Р Р†Р ВµР Т‘Р ВµР Р…Р С‘Р С‘:</h2>';
       $textWorld .= '<div style="color:green;font-weight:bold;">'.$sp['mess'].'</div>';
       $textWorld .= '<br><a href="/game.php?go=char"><- Р Р€Р в„–РЎвЂљР С‘</a>';
    }
  }
  if(!$sp['id'] &&  $textWorld == false) $textWorld .= '<br><a href="/game.php?go=char"><- Р Р€Р в„–РЎвЂљР С‘</a>';
  if($nw == 3 && $oke == true && !empty($sp['id']) && !empty($_GET['pokesnew']) && $sp['user_2'] == $_SESSION['id']){ 
    $pou = obr_chis($_GET['pokesnew']);
    $sparka  = first('SELECT * FROM sparka WHERE id=%d AND user_2=%d',$_GET['id'],$_SESSION['id']);
    if(empty($sparka['id'])){
     noHref('char&newpok=2&to_tren='.$treners.'&id='.$sparka['id'],'Р РЋР С‘РЎРѓРЎвЂљР ВµР СР В°РЎвЂљР С‘РЎвЂЎР ВµРЎРѓР С”Р В°РЎРЏ Р С•РЎв‚¬Р С‘Р В±Р С”Р В°. Р СџР С•Р С—РЎР‚Р С•Р В±РЎС“Р в„–РЎвЂљР Вµ РЎРѓР Р…Р С•Р Р†Р В°.');
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
     noHref('char&newpok=2&to_tren='.$treners.'&id='.$sparka['id'],'Р РЋР С‘РЎРѓРЎвЂљР ВµР СР В°РЎвЂљР С‘РЎвЂЎР ВµРЎРѓР С”Р В°РЎРЏ Р С•РЎв‚¬Р С‘Р В±Р С”Р В°. Р СџР С•Р С—РЎР‚Р С•Р В±РЎС“Р в„–РЎвЂљР Вµ РЎРѓР Р…Р С•Р Р†Р В°.');
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
       $msSp = 'Р вЂ™Р В°РЎв‚¬Р С‘ Р С—Р С•Р С”Р ВµР СР С•Р Р…РЎвЂ№ Р Р†РЎРѓРЎвЂљР ВµРЎР‚РЎвЂљР С‘Р В»Р С‘РЎРѓРЎРЉ Р Р†Р С—Р ВµРЎР‚Р Р†РЎвЂ№Р Вµ, Р С‘ Р С•Р Р…Р С‘ РЎС“Р Т‘Р В°РЎвЂЎР Р…Р С• Р С—Р ВµРЎР‚Р ВµРЎв‚¬Р В»Р С‘ Р Р…Р В° Р С—Р ВµРЎР‚Р Р†РЎС“РЎР‹ РЎРѓРЎвЂљР В°Р Т‘Р С‘РЎР‹ Р С•РЎвЂљР Р…Р С•РЎв‚¬Р ВµР Р…Р С‘Р в„–. <br> Р В§Р ВµРЎР‚Р ВµР В· 24 РЎвЂЎР В°РЎРѓР В° Р вЂ™РЎвЂ№ РЎРѓР СР С•Р В¶Р ВµРЎвЂљР Вµ Р С—Р С•Р С—Р С•РЎР‚Р С•Р В±Р С•Р Р†Р В°РЎвЂљРЎРЉ РЎРѓР Р…Р С•Р Р†Р В° РЎРѓР Р†Р ВµРЎРѓРЎвЂљР С‘ Р С‘РЎвЂ¦ Р Р†Р СР ВµРЎРѓРЎвЂљР Вµ.';
       update('sparka',array('tip'=>1, 'mess'=>$msSp, 'no'=>4),'id='.(int)$sparkaId);
       die("<script>parent._location.location.href='/game.php?go=char&newpok=2&to_tren=true&id=".$sp['id']."';</script>");
    }
    if($pSpark['timer'] > time()){
       $msSp = 'Р В Р В°Р В·Р Р†Р ВµР Т‘Р ВµР Р…Р С‘Р Вµ Р Р…Р Вµ РЎС“Р Т‘Р В°Р В»Р С•РЎРѓРЎРЉ! Р вЂ™Р В°РЎв‚¬Р С‘ Р С—Р С•Р С”Р ВµР СР С•Р Р…РЎвЂ№ Р Р…Р Вµ Р С–Р С•РЎвЂљР С•Р Р†РЎвЂ№ Р С” РЎРЊРЎвЂљР С•Р в„– Р Р†РЎРѓРЎвЂљРЎР‚Р ВµРЎвЂЎР Вµ. '.timersOtshet($pSpark['timer']-1,"<br>Р СџР С•Р С—РЎР‚Р С•Р В±РЎС“Р в„–РЎвЂљР Вµ РЎРѓР Р…Р С•Р Р†Р В° РЎвЂЎР ВµРЎР‚Р ВµР В·: ", "Р вЂњР С•РЎвЂљР С•Р Р†РЎвЂ№");
       update('sparka',array('tip'=>1, 'mess'=>$msSp, 'no'=>4),'id='.(int)$sparkaId);
       die("<script>parent._location.location.href='/game.php?go=char&newpok=2&to_tren=true&id=".$sp['id']."';</script>");
    }
    if($pSpark['status']  == 1){
       $timSpark = time() +(60*60*24*1);
       update('poke_spar',array('status'=>2, 'timer'=>$timSpark),'id='.(int)$pSpark['id']);
       $msSp = 'Р вЂ™Р В°РЎв‚¬Р С‘ Р С—Р С•Р С”Р ВµР СР С•Р Р…РЎвЂ№ РЎС“Р Т‘Р В°РЎвЂЎР Р…Р С• Р С—Р ВµРЎР‚Р ВµРЎв‚¬Р В»Р С‘ Р Р…Р В° Р Р†РЎвЂљР С•РЎР‚РЎС“РЎР‹ РЎРѓРЎвЂљР В°Р Т‘Р С‘РЎР‹ Р С•РЎвЂљР Р…Р С•РЎв‚¬Р ВµР Р…Р С‘Р в„–. '.timersOtshet($timSpark-1,"<br>Р СџР С•Р С—РЎР‚Р С•Р В±РЎС“Р в„–РЎвЂљР Вµ РЎРѓР Р†Р ВµРЎРѓРЎвЂљР С‘ Р Р†Р СР ВµРЎРѓРЎвЂљР Вµ Р Р†Р В°РЎв‚¬Р С‘РЎвЂ¦ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р С•Р Р† РЎвЂЎР ВµРЎР‚Р ВµР В·: ", "Р вЂњР С•РЎвЂљР С•Р Р†РЎвЂ№");
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
         $msSp = 'Р вЂ™Р В°РЎв‚¬Р С‘ Р С—Р С•Р С”Р ВµР СР С•Р Р…РЎвЂ№ РЎС“Р Т‘Р В°РЎвЂЎР Р…Р С• Р С—Р ВµРЎР‚Р ВµРЎв‚¬Р В»Р С‘ Р Р…Р В° РЎвЂљРЎР‚Р ВµРЎвЂљР С‘РЎР‹ РЎРѓРЎвЂљР В°Р Т‘Р С‘РЎР‹ Р С•РЎвЂљР Р…Р С•РЎв‚¬Р ВµР Р…Р С‘Р в„–. '.timersOtshet($timSpark-1,"<br>Р СџР С•Р С—РЎР‚Р С•Р В±РЎС“Р в„–РЎвЂљР Вµ РЎРѓР Р†Р ВµРЎРѓРЎвЂљР С‘ Р Р†Р СР ВµРЎРѓРЎвЂљР Вµ Р Р†Р В°РЎв‚¬Р С‘РЎвЂ¦ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р С•Р Р† РЎвЂЎР ВµРЎР‚Р ВµР В·: ", "Р вЂњР С•РЎвЂљР С•Р Р†РЎвЂ№");
         update('sparka',array('tip'=>1, 'mess'=>$msSp, 'no'=>4),'id='.(int)$sparkaId);
         die("<script>parent._location.location.href='/game.php?go=char&newpok=2&to_tren=true&id=".$sp['id']."';</script>");
       }
    }
    if($pSpark['status'] == 3){
       if($pok_one['sex'] == $pok_two['sex']){ 
         $msSp = 'Р В Р В°Р В·Р Р†Р ВµР Т‘Р ВµР Р…Р С‘Р Вµ Р Р…Р Вµ РЎС“Р Т‘Р В°Р В»Р С•РЎРѓРЎРЉ! Р СџР С•Р В» Р С—Р С•Р С”Р ВµР СР С•Р Р…Р С•Р Р† Р Т‘Р С•Р В»Р В¶Р ВµР Р… Р В±РЎвЂ№РЎвЂљРЎРЉ РЎР‚Р В°Р В·Р Р…РЎвЂ№Р С!';
         update('sparka',array('tip'=>1, 'mess'=>$msSp, 'no'=>4),'id='.(int)$sparkaId);
         die("<script>parent._location.location.href='/game.php?go=char&newpok=2&to_tren=true&id=".$sp['id']."';</script>");
       }
       if($pok_one['happy'] < 15 || $pok_two['happy'] < 15){ 
         $msSp = 'Р В Р В°Р В·Р Р†Р ВµР Т‘Р ВµР Р…Р С‘Р Вµ Р Р…Р Вµ РЎС“Р Т‘Р В°Р В»Р С•РЎРѓРЎРЉ! Р РЋРЎвЂЎР В°РЎРѓРЎвЂљРЎРЉР Вµ Р С•Р В±Р Р…Р С•Р С‘РЎвЂ¦ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р С•Р Р†, Р С”Р С•РЎвЂљР С•РЎР‚РЎвЂ№РЎвЂ¦ Р вЂ™РЎвЂ№ Р В¶Р ВµР В»Р В°Р ВµРЎвЂљР Вµ РЎРѓР С—Р В°РЎР‚Р С‘РЎвЂљРЎРЉ, Р Т‘Р С•Р В»Р В¶Р Р…Р С• Р В±РЎвЂ№РЎвЂљРЎРЉ Р В±Р С•Р В»Р ВµР Вµ 15%!';
         update('sparka',array('tip'=>1, 'mess'=>$msSp, 'no'=>4),'id='.(int)$sparkaId);
         die("<script>parent._location.location.href='/game.php?go=char&newpok=2&to_tren=true&id=".$sp['id']."';</script>");
       }
       if($pok_one['basenum'] != $pok_two['basenum']){
         $msSp = 'Р В Р В°Р В·Р Р†Р ВµР Т‘Р ВµР Р…Р С‘Р Вµ Р Р…Р Вµ РЎС“Р Т‘Р В°Р В»Р С•РЎРѓРЎРЉ! Р СћР С‘Р С—РЎвЂ№ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р С•Р Р† Р Т‘Р С•Р В»Р В¶Р Р…РЎвЂ№ Р В±РЎвЂ№РЎвЂљРЎРЉ Р С•Р Т‘Р С‘Р Р…Р В°Р С”Р С•Р Р†РЎвЂ№Р СР С‘!';
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
            $msSp = 'Р В Р В°Р В·Р Р†Р ВµР Т‘Р ВµР Р…Р С‘Р Вµ Р Р…Р Вµ РЎС“Р Т‘Р В°Р В»Р С•РЎРѓРЎРЉ! Р СњР В° Р Т‘Р В°Р Р…Р Р…РЎвЂ№Р в„– Р СР С•Р СР ВµР Р…РЎвЂљ РЎР‚Р В°Р В·Р Р†Р ВµР Т‘Р ВµР Р…Р С‘Р Вµ РЎРЊРЎвЂљР С‘РЎвЂ¦ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р С•Р Р† Р Р…Р Вµ Р Р†Р С•Р В·Р СР С•Р В¶Р Р…Р С•!';
            update('sparka',array('tip'=>1, 'mess'=>$msSp, 'no'=>4),'id='.(int)$sparkaId);
            die("<script>parent._location.location.href='/game.php?go=char&newpok=2&to_tren=true&id=".$sp['id']."';</script>");
          }
          $msSp = "Р В Р В°Р В·Р Р†Р ВµР Т‘Р ВµР Р…Р С‘Р Вµ Р С—РЎР‚Р С•РЎв‚¬Р В»Р С• РЎС“Р Т‘Р В°РЎвЂЎР Р…Р С•, РЎРЏР в„–РЎвЂ Р С•: #".$basaEggNam['title']." ".$tip_egg_sms." Р С—Р С•Р В»РЎС“РЎвЂЎР В°Р ВµРЎвЂљ: ".color_group_users($user_egg);
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
               $msSp .= ' <br>Р С’ РЎвЂљР В°Р С” Р В¶Р Вµ, Р С—РЎР‚Р С‘ РЎР‚Р В°Р В·Р Р†Р ВµР Т‘Р ВµР Р…Р С‘Р С‘, Р Р†Р В°РЎв‚¬Р С‘ Р С—Р С•Р С”Р ВµР СР С•Р Р…РЎвЂ№ РЎРЊР Р†Р С•Р В»РЎР‹РЎвЂ Р С‘Р С•Р Р…Р С‘РЎР‚Р С•Р Р†Р В°Р В»Р С‘ Р Р† #'.$evo_poke['title'];
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