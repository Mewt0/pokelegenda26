<?php 
$q4  = isset_qest(4);
$qx04 = quest_process(4, 10);
$qx05 = quest_process(4, 11);
if(!empty($_GET['quest_npc']) && !empty($_GET['do']) && $_GET['quest_npc'] == 5){
   if($_GET['quest_npc'] == 5  && (!$q4 || $qx04)) include ("npc/npc_5.php");
    else die("<script>location.href='game.php?go=char';</script>"); 
}else{
  $name = 'Старинные деревья';
  $about = 'Этот участок леса довольно опасен для молодых тренеров. 
            Здесь находится улей Бидрилл, много разных и опасных покемонов, которые яростно охраняют свои границы. 
            Для метки своей территории эти покемоны оставляют на деревьях большие кресты, как бы предупреждая остальных, что тем лучше уйти с этой местности.';
  $pers = '...';
  if(!$q4 || $qx04 || $qx05) $pers = "<a href='/game.php?go=char&quest_npc=5&do=1'>Кэрол</a>";
  $move = '<a href="/game.php?go=charWork&loc=5" target="_chat_two">Лес Вертании</a> | <a href="/game.php?go=charWork&loc=7" target="_chat_two">Тёмный лес</a>';
  $img_r = '<img src="img/room/006.png" width="290" height="150">';
}
?>

