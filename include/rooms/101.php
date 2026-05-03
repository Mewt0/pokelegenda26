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
        elseif ($_GET['npc'] == 3) include ("npc/kurator.php");
  }else{ 
    $name = 'Литтлруд';
    $about = 'Литтлруд - один из крупнейших городов Хоэнн. ';
    $pers = '<a href="/game.php?go=char&npc=1&do_npc=pc">Покецентр</a> | 
             <a href="/game.php?go=char&npc=2">Покемаркет</a> | 
             <a href="game.php?go=char&npc=3">Куратор</a> ';
    $move = '<a href="/game.php?go=charWork&loc=98" target="_chat_two">Лаборатория</a> | <a href="/game.php?go=charWork&loc=99" target="_chat_two">Дорога в порт</a> | <a href="/game.php?go=charWork&loc=102" target="_chat_two">Лес Литтлруда</a> ';
    $img_r = '<img src="img/room/101.png" width="290" height="150">';
  } 
}                                                                                          
?>