<?php  
  if(!empty($_GET['npc']) && ($_GET['npc'] == 1 || $_GET['npc'] == 2)){
        if ($_GET['npc'] == 1){
            include ("npc/1.php"); 
            $img_r = '<img src="img/room/1_1.png" width="250" height="150">';
        }
        elseif ($_GET['npc'] == 2) include ("npc/shop.php");
  }else{ 
    $name = 'Лавандер';
    $about = 'Вот Вы и пришли в самый красивый водный город Канто. 
              По бокам стоят красивые фонтаны, с большими статуями водных покемонов. 
              На зданиях висят рекламы водного цирка, в который Вам не помешало бы сходить.';
    $pers = '<a href="/game.php?go=char&npc=1&do_npc=pc">Покецентр</a> | <a href="/game.php?go=char&npc=2">Покемаркет</a>';
    $move = '<a href="/game.php?go=charWork&loc=51" target="_chat_two">Дорога 6</a> | <a href="/game.php?go=charWork&loc=57" target="_chat_two">Дорога 12</a>';
  }
?>