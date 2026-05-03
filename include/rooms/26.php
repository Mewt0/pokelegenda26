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
    $name = 'Оливин Сити';
    $about = 'Оливин сити - один из крупнейших городов Джотто. 
              Так же он является самым привлекательным городом из-за того, что он находится рядом с морем и самой большой растительностью в регионе.';
    $pers = '<a href="/game.php?go=char&npc=1&do_npc=pc">Покецентр</a> | 
             <a href="/game.php?go=char&npc=2">Покемаркет</a> | 
             <a href="game.php?go=char&npc=3">Куратор</a> ';
    $move = '<a href="/game.php?go=charWork&loc=25" target="_chat_two">Порт Джотто</a> | 
             <a href="/game.php?go=charWork&loc=28" target="_chat_two">Центр Оливина</a> | 
             <a href="/game.php?go=charWork&loc=84" target="_chat_two">Стадион Водных покемонов</a> | 
             <a href="/game.php?go=charWork&loc=45" target="_chat_two">Национальный парк</a> | 
             <a href="/game.php?go=charWork&loc=27" target="_chat_two">Пляж</a>';
  } 
}                                                                                          
?>