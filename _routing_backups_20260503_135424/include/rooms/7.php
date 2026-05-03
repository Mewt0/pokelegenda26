<?php 
$qx02 = quest_process(2, 2);
$qxx02 = quest_process(2, 3);
if(!empty($_GET['quest_npc']) && !empty($_GET['do']) && $_GET['quest_npc'] == 1){
   if($_GET['quest_npc'] == 1  && ($qx02 || $qxx02)) include ("npc/npc_9.php");
   else die("<script>location.href='game.php?go=char';</script>"); 
}else{
  $name = 'Тёмный лес';
  $about = 'Этот лес хранит в себе много загадок и слухов. 
            Говорят, что в доме неподалеку когда-то жил тренер покемонов. 
            Но он был не таким как все: он ловил исключительно призрачных покемонов, поймать которых под силу не каждому. 
            Время шло и тренер умер от старости.
            Но его покемоны остались жить. 
            Они будто охраняют этот лес и тот старый заброшенный дом, пытаясь напугать или даже покалечить проходивших здесь тренеров. 
            В этом месте стоит быть особо внимательным... 
            А, может, лучше вообще туда не заходить? '; 
  if($qx02 || $qxx02) $pers = "<a href='/game.php?go=char&quest_npc=1&do=1'>Старая женщина</a>";
  if(empty($pers)) $pers = '...';
  $move = '<a href="/game.php?go=charWork&loc=6" target="_chat_two">Старинные деревья</a> | <a href="/game.php?go=charWork&loc=8" target="_chat_two">Пещера</a> | <s>Старый дом</s>';
  $img_r = '<img src="img/room/007.png" width="290" height="150">';
}
?>

