<?php
function loces_go($ties, $coment, $cools){
$user_base = first('SELECT rang_a FROM users WHERE id=%d',$_SESSION['id']);
 $mes = $coment;
  if($ties == 1)
    $resultstiks = ($user_base['rang_a'] > $cools?true:false);
  elseif($ties == 2)
    $resultstiks = (provitems($cools['item_id'],$cools['cool'])?true:false);
  elseif($ties == 3){
    $user_quest = first('SELECT gotov,process FROM quest WHERE quest_id=%d AND user_id=%d',$cools['qid'],$_SESSION['id']);
     $resultstiks = ($user_quest['process'] >= $cools['process']?true:false);
  }
  elseif($ties == 4){
    $po_lvl = first('SELECT COUNT(*) as count FROM pok_user WHERE users=%d and active=1 and lvl>%d',$_SESSION['id'],$cools['lvl']);
     if(rangs_pr($cools['rang'],1))
        $resultstiks = ($po_lvl['count'] >= $cools['cool']?true:false);
      else
        $resultstiks = false;   
  }
 return array('res'=>$resultstiks, 'mes'=>$mes);;
}
?>