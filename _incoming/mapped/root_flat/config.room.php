<?php
declare(strict_types=1);
function qPokeBase($base,$lvl=false){                                 
  if($lvl != false) $lvl = ' AND lvl='.$lvl.' '; else $lvl = '';
   $q = first('SELECT id FROM pok_user WHERE users=%d AND active=1 AND basenum=%d '.$lvl.' ',$_SESSION['id'],$base);
  $resultstiks = ($q['id']?$q['id']:false);
 return $resultstiks;
}
function qDelPoke($id){                                 
  delete('pok_user','id='.(int)$id.' AND  users='.(int)$_SESSION['id']);
}
function qInfo($q,$zap,$dop=false){                                 
  if(!empty($dop)) $z = $dop; else $z = ' ';
  $a = first('SELECT '.$zap.' FROM quest WHERE quest_id=%d AND user_id=%d '.$z,$q,$_SESSION['id']);
  return ($a[$zap]?$a[$zap]:false);
}
function qCountPoke($count,$dop=false){
  if(!empty($dop)) $z = $dop; else $z = ' ';
  $p = first('SELECT COUNT(*) as count FROM pok_user WHERE users=%d AND active=1 '.$z,$_SESSION['id']);
    $resultstiks = ($p['count'] >= $count?true:false);
 return $resultstiks;
}
function questRangUp($cols){
 query('UPDATE users SET rang_c=rang_c+%d WHERE id=%d',$cols,$_SESSION['id']);
}
function insQuePoke($q,$p,$cool){
  insert('quest_poke',array('userid'=>$_SESSION['id'], 'questid'=>$q, 'pokenum'=>$p, 'coolpokemin'=>0, 'coolpokemax'=>$cool));
}
function questPokemon($quest,$poke){
 $p = first('SELECT id FROM quest_poke WHERE userid=%d AND questid=%d AND pokenum=%d AND coolpokemin=coolpokemax',$_SESSION['id'],$quest,$poke);
 if(!empty($p['id'])) $a = $p['id']; else $a = false;
 return $a;
}
function deletqPokemon($id){
  delete('quest_poke','id='.(int)$id);
}
?>