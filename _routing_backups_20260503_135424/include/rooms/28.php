<?php  
if(!empty($_GET['quest_npc']) && $_GET['quest_npc'] == 3){ 
  if($_GET['quest_npc'] == 3) include ("npc/nps_14.php");
  else die("<script>location.href='game.php?go=char';</script>"); 
}else{

  if(!empty($_GET['npc']) && ($_GET['npc'] == 1 || $_GET['npc'] == 2)){
        if ($_GET['npc'] == 1){
            include ("npc/1.php"); 
            $img_r = '<img src="img/room/1_1.png" width="250" height="150">';
        }
        elseif ($_GET['npc'] == 2) include ("npc/shop.php");
  }else{ 
    $name = 'Центр Оливина';
    $about = 'Новый Район, центр города - Оливин. 
              Проходя по улице, Вы видите пару памятников, которые были возведены легендарными тренерами покемонов. 
              В городе есть большой рынок, где можно найти много интересного для себя.';
    $pers = '<a href="/game.php?go=char&npc=1&do_npc=pc">Покецентр</a> | <a href="/game.php?go=char&npc=2">Покемаркет</a> | <a href=\'/game.php?go=char&quest_npc=3&do=1\'>Мастеровой</a>';
    $move = '<a href="/game.php?go=charWork&loc=26" target="_chat_two">Оливин Сити</a> | 
             <a href="/game.php?go=charWork&loc=29" target="_chat_two">Пригород</a>
             ';
  }
}                                                                                          
?>