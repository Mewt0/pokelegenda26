<?php
  $pokemon = obr_chis($_GET['pok']);
  $value   = obr_chis($_GET['val']);
  $stat    = obr_txt($_GET['stat']);
  if(empty($pokemon) || empty($value) || empty($stat)) die("<script>window.location.href='/game.php?go=pokemon';</script>");
  $p = first('SELECT id,evcount,hp_ev,atk_ev,def_ev,satk_ev,sdef_ev,speed_ev FROM pok_user WHERE id=%d AND users=%d AND active=1',$pokemon,$_SESSION['id']);
  if(empty($p['id'])) die("<script>window.location.href='/game.php?go=pokemon';</script>");
  if($p['evcount'] <= 0) die("<script>alert('Недостаточно очков EV.');window.location.href='/game.php?go=pokemon';</script>");
  if($value > $p['evcount']) die("<script>alert('Недостаточно очков EV.');window.location.href='/game.php?go=pokemon';</script>");
      if($stat == 'hp') { $tb = 'hp_ev';    $chis = $p['hp_ev']    + $value; }
  elseif($stat == 'at') { $tb = 'atk_ev';   $chis = $p['atk_ev']   + $value; }
  elseif($stat == 'df') { $tb = 'def_ev';   $chis = $p['def_ev']   + $value; }
  elseif($stat == 'sa') { $tb = 'satk_ev';  $chis = $p['satk_ev']  + $value; }
  elseif($stat == 'sd') { $tb = 'sdef_ev';  $chis = $p['sdef_ev']  + $value; }
  elseif($stat == 'sp') { $tb = 'speed_ev'; $chis = $p['speed_ev'] + $value; }
  else die("<script>window.location.href='/game.php?go=pokemon';</script>");
  $podshet = $p['hp_ev']+$p['atk_ev']+$p['def_ev']+$p['satk_ev']+$p['sdef_ev']+$p['speed_ev']+$value;
  if($podshet > 580) die("<script>alert('Максимальное количество очков в покемоне не может превышать отметки 580.');window.location.href='/game.php?go=pokemon';</script>");
  if($chis > 255) die("<script>alert('Количество EV не может превышать отметки 255 в одном стате.');window.location.href='/game.php?go=pokemon';</script>");
  $evMyRes = $p['evcount'] - $value; 
  update('pok_user',array($tb=>$chis, 'evcount'=>$evMyRes),'users='.(int)$_SESSION['id'].' AND id='.(int)$p['id']);
  die("<script>alert('EV успешно расставлены.');window.location.href='/game.php?go=pokemon';</script>");   

?>