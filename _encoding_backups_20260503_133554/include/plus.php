<?php
  $pokemon = obr_chis($_GET['pok']);
  $value   = obr_chis($_GET['val']);
  $stat    = obr_txt($_GET['stat']);
  if(empty($pokemon) || empty($value) || empty($stat)) die("<script>window.location.href='/game.php?go=pokemon';</script>");
  $p = first('SELECT id,evcount,hp_ev,atk_ev,def_ev,satk_ev,sdef_ev,speed_ev FROM pok_user WHERE id=%d AND users=%d AND active=1',$pokemon,$_SESSION['id']);
  if(empty($p['id'])) die("<script>window.location.href='/game.php?go=pokemon';</script>");
  if($p['evcount'] <= 0) die("<script>alert('Р СњР ВµР Т‘Р С•РЎРѓРЎвЂљР В°РЎвЂљР С•РЎвЂЎР Р…Р С• Р С•РЎвЂЎР С”Р С•Р Р† EV.');window.location.href='/game.php?go=pokemon';</script>");
  if($value > $p['evcount']) die("<script>alert('Р СњР ВµР Т‘Р С•РЎРѓРЎвЂљР В°РЎвЂљР С•РЎвЂЎР Р…Р С• Р С•РЎвЂЎР С”Р С•Р Р† EV.');window.location.href='/game.php?go=pokemon';</script>");
      if($stat == 'hp') { $tb = 'hp_ev';    $chis = $p['hp_ev']    + $value; }
  elseif($stat == 'at') { $tb = 'atk_ev';   $chis = $p['atk_ev']   + $value; }
  elseif($stat == 'df') { $tb = 'def_ev';   $chis = $p['def_ev']   + $value; }
  elseif($stat == 'sa') { $tb = 'satk_ev';  $chis = $p['satk_ev']  + $value; }
  elseif($stat == 'sd') { $tb = 'sdef_ev';  $chis = $p['sdef_ev']  + $value; }
  elseif($stat == 'sp') { $tb = 'speed_ev'; $chis = $p['speed_ev'] + $value; }
  else die("<script>window.location.href='/game.php?go=pokemon';</script>");
  $podshet = $p['hp_ev']+$p['atk_ev']+$p['def_ev']+$p['satk_ev']+$p['sdef_ev']+$p['speed_ev']+$value;
  if($podshet > 580) die("<script>alert('Р СљР В°Р С”РЎРѓР С‘Р СР В°Р В»РЎРЉР Р…Р С•Р Вµ Р С”Р С•Р В»Р С‘РЎвЂЎР ВµРЎРѓРЎвЂљР Р†Р С• Р С•РЎвЂЎР С”Р С•Р Р† Р Р† Р С—Р С•Р С”Р ВµР СР С•Р Р…Р Вµ Р Р…Р Вµ Р СР С•Р В¶Р ВµРЎвЂљ Р С—РЎР‚Р ВµР Р†РЎвЂ№РЎв‚¬Р В°РЎвЂљРЎРЉ Р С•РЎвЂљР СР ВµРЎвЂљР С”Р С‘ 580.');window.location.href='/game.php?go=pokemon';</script>");
  if($chis > 255) die("<script>alert('Р С™Р С•Р В»Р С‘РЎвЂЎР ВµРЎРѓРЎвЂљР Р†Р С• EV Р Р…Р Вµ Р СР С•Р В¶Р ВµРЎвЂљ Р С—РЎР‚Р ВµР Р†РЎвЂ№РЎв‚¬Р В°РЎвЂљРЎРЉ Р С•РЎвЂљР СР ВµРЎвЂљР С”Р С‘ 255 Р Р† Р С•Р Т‘Р Р…Р С•Р С РЎРѓРЎвЂљР В°РЎвЂљР Вµ.');window.location.href='/game.php?go=pokemon';</script>");
  $evMyRes = $p['evcount'] - $value; 
  update('pok_user',array($tb=>$chis, 'evcount'=>$evMyRes),'users='.(int)$_SESSION['id'].' AND id='.(int)$p['id']);
  die("<script>alert('EV РЎС“РЎРѓР С—Р ВµРЎв‚¬Р Р…Р С• РЎР‚Р В°РЎРѓРЎРѓРЎвЂљР В°Р Р†Р В»Р ВµР Р…РЎвЂ№.');window.location.href='/game.php?go=pokemon';</script>");   

?>