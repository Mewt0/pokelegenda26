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
  return 'В подарок вы получили: '.$r.'.';
 }else{
  return 'Вы уже получали подарок!';
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
  <center><b>С новым годом! Всем подарки!</b></center>
</div>
<br> <br>
<div class="shadowtext_nw">
  <center><b> <?=eventesNw();?></b></center>
</div>
<br>
<span style="color:#fff; font-size: 16px;">  
  <center><b>
    Уходит старый год,<br>
    Шуршит его последняя страница.<br>
    Пусть лучшее, что было, не уйдет,<br>
    А худшее - не сможет повториться.  <br>
  </b></center>
</span>
<br><br>
<center>
<img src="/img/event3.gif" title="С новым годом! Всем подарки!" alt="С новым годом! Всем подарки!">
</center>