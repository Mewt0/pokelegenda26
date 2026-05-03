<?php  
  if(!empty($_GET['npc']) && ($_GET['npc'] == 1 || $_GET['npc'] == 2)){
        if ($_GET['npc'] == 1){
            include ("npc/1.php"); 
            $img_r = '<img src="img/room/1_1.png" width="250" height="150">';
        }
        elseif ($_GET['npc'] == 2) include ("npc/shop.php");
  }else{ 
    $name = 'Маувайл';
    $about = 'Вот Вы и пришли в самый красивый водный город Маувайл. 
              По бокам стоят красивые фонтаны, с большими статуями водных покемонов. 
              На зданиях висят рекламы водного цирка, в который Вам не помешало бы сходить.';
    $pers = '<a href="/game.php?go=char&npc=1&do_npc=pc">Покецентр</a> | <a href="/game.php?go=char&npc=2">Покемаркет</a>';
    $move = '<a href="/game.php?go=charWork&loc=115" target="_chat_two">Дорога 117</a> | <a href="/game.php?go=charWork&loc=118" target="_chat_two">Дорога 111</a> | <a href="/game.php?go=charWork&loc=119" target="_chat_two">Дорога 118</a> | <a href="/game.php?go=charWork&loc=158" target="_chat_two">Дорога 110</a>';
  }
?>