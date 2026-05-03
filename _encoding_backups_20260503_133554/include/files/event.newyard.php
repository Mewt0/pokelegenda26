<?php
//...
function eventesNw(){
  $x = first('SELECT event FROM usersunictable WHERE id=%d',$_SESSION['id']);
  $r = false;
  if($x['event'] <= 0){
    $q = rand(1,3);
    if($q == 1){
      plus_pokes($_SESSION['id'],215,1,1,1,'nwy',0,32);
      $r = '<span class="pokesNwy">#215 Sneasel - <b>Christmas</b></span>'; 
    }elseif($q == 2){
      plus_pokes($_SESSION['id'],363,1,1,1,'nwy',0,32);
      $r = '<span class="pokesNwy">#363 Spheal - <b>Christmas</b></span>'; 
    }else{
      plus_pokes($_SESSION['id'],459,1,1,1,'nwy',0,32);
      $r = '<span class="pokesNwy">#459 Snover - <b>Christmas</b></span>';   
    }
    if($r)  update('usersunictable',array('event'=>1),'id='.(int)$_SESSION['id']); 
  return 'Р вЂ™ Р С—Р С•Р Т‘Р В°РЎР‚Р С•Р С” Р Р†РЎвЂ№ Р С—Р С•Р В»РЎС“РЎвЂЎР С‘Р В»Р С‘: '.$r.'.';
 }else{
  return 'Р вЂ™РЎвЂ№ РЎС“Р В¶Р Вµ Р С—Р С•Р В»РЎС“РЎвЂЎР В°Р В»Р С‘ Р С—Р С•Р Т‘Р В°РЎР‚Р С•Р С”!';
 }
}
?>
<style>
.shadowtext_tw {
    text-shadow: Black 1px 1px 2px, #DAA520 0 0 1em; 
    color: gold; 
    font-size: 3em;  
    padding: 5 5 5 5px;
   }
.shadowtext_nw {
    text-shadow: Black 1px 1px 2px, #000 0 0 1em; 
    color: #afeeee; 
    font-size: 25px;  
    padding: 5 5 5 5px;
   }
</style>
<div class="shadowtext_tw">
  <center><b>Р РЋ Р Р…Р С•Р Р†РЎвЂ№Р С Р С–Р С•Р Т‘Р С•Р С! Р вЂ™РЎРѓР ВµР С Р С—Р С•Р Т‘Р В°РЎР‚Р С”Р С‘!</b></center>
</div>
<br> <br>
<div class="shadowtext_nw">
  <center><b> <?=eventesNw();?></b></center>
</div>
<br>
<span style="color:#fff; font-size: 16px;">  
  <center><b>
    Р Р€РЎвЂ¦Р С•Р Т‘Р С‘РЎвЂљ РЎРѓРЎвЂљР В°РЎР‚РЎвЂ№Р в„– Р С–Р С•Р Т‘,<br>
    Р РЃРЎС“РЎР‚РЎв‚¬Р С‘РЎвЂљ Р ВµР С–Р С• Р С—Р С•РЎРѓР В»Р ВµР Т‘Р Р…РЎРЏРЎРЏ РЎРѓРЎвЂљРЎР‚Р В°Р Р…Р С‘РЎвЂ Р В°.<br>
    Р СџРЎС“РЎРѓРЎвЂљРЎРЉ Р В»РЎС“РЎвЂЎРЎв‚¬Р ВµР Вµ, РЎвЂЎРЎвЂљР С• Р В±РЎвЂ№Р В»Р С•, Р Р…Р Вµ РЎС“Р в„–Р Т‘Р ВµРЎвЂљ,<br>
    Р С’ РЎвЂ¦РЎС“Р Т‘РЎв‚¬Р ВµР Вµ - Р Р…Р Вµ РЎРѓР СР С•Р В¶Р ВµРЎвЂљ Р С—Р С•Р Р†РЎвЂљР С•РЎР‚Р С‘РЎвЂљРЎРЉРЎРѓРЎРЏ.  <br>
  </b></center>
</span>
<br><br>
<center>
<img src="/img/event3.gif" title="Р РЋ Р Р…Р С•Р Р†РЎвЂ№Р С Р С–Р С•Р Т‘Р С•Р С! Р вЂ™РЎРѓР ВµР С Р С—Р С•Р Т‘Р В°РЎР‚Р С”Р С‘!" alt="Р РЋ Р Р…Р С•Р Р†РЎвЂ№Р С Р С–Р С•Р Т‘Р С•Р С! Р вЂ™РЎРѓР ВµР С Р С—Р С•Р Т‘Р В°РЎР‚Р С”Р С‘!">
</center>