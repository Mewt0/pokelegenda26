<?php
$eventDay = true;  
if(!empty($_GET['quest_npc']) && !empty($_GET['do']) && $_GET['quest_npc'] == 1){
   if($_GET['quest_npc'] == 1  && $eventDay) include ("npc/npc_event1.php");
    else die("<script>location.href='game.php?go=char';</script>"); 
}else{
  if(!empty($_GET['npc']) && ($_GET['npc'] == 1 || $_GET['npc'] == 2 || $_GET['npc'] == 3)){
        if ($_GET['npc'] == 1){
            include ("npc/1.php"); 
            $img_r = '<img src="img/room/1_1.png" width="250" height="150">';
        }
        elseif ($_GET['npc'] == 2) include ("npc/shop.php");
  }else{ 
    $name = 'Петалбург';
    $about = 'Красивый город. Петалбург славится своими прикрасными садами.';
    $pers = '<a href="/game.php?go=char&npc=1&do_npc=pc">Покецентр</a> | 
             <a href="/game.php?go=char&npc=2">Покемаркет</a> ';
    $move = '<a href="/game.php?go=charWork&loc=105" target="_chat_two">Дорога 102</a> ';
    $img_r = '<img src="img/room/108.png" width="290" height="150">';
  } 
}                                                                                          
?>