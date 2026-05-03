<?php 
$q3   = isset_qest(3);
$qx03 = quest_process(3, 2);
if(!empty($_GET['quest_npc']) && !empty($_GET['do']) && $_GET['quest_npc'] == 4){
   if($_GET['quest_npc'] == 4  && (!$q3 || $qx03)) include ("npc/npc_4.php");
    else die("<script>location.href='game.php?go=char';</script>"); 
}else{
  $name = 'Лес Вертании';
  $about = 'Это достаточно светлый и безопасный лес. 
            Многие начинающие тренеры проводят здесь большую часть свободного времени, набираясь опыта и отлавливая редких покемонов-жуков. 
            Однако есть и те, кто, прослышав легенду о старом самурайском поселке, все еще бродят в попытке отыскать его и узнать все секреты былой мощи самураев.';
  $pers = '...';
  if(!$q3 || $qx03) $pers = "<a href='/game.php?go=char&quest_npc=4&do=1'>Циркач Стив</a>";
  $move = '<a href="/game.php?go=charWork&loc=4" target="_chat_two">Дорога 1</a> | <a href="/game.php?go=charWork&loc=16" target="_chat_two">Вертания</a> | <a href="/game.php?go=charWork&loc=9" target="_chat_two">Озеро Вертании</a> | <a href="/game.php?go=charWork&loc=6" target="_chat_two">Старинные деревья</a>';
  $img_r = '<img src="img/room/005.png" width="290" height="150">';
}
?>

