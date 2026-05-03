<?php  
if(!empty($_GET['npc']) AND ($_GET['npc'] == 1 OR $_GET['npc']==2 OR $_GET['npc']==3))
  {
        if ($_GET['npc'] == 1) 
          {
            include ("npc/1.php"); 
            $img_r = '<img src="img/room/1_1.png" width="250" height="150">';
          }
    elseif ($_GET['npc'] == 2) include ("npc/shop.php");
    elseif ($_GET['npc'] == 3) include ("npc/kurator.php");
      else die("<script>location.href='game.php?go=char';</script>");
     
  } 
else
  {
    $name = 'Зона Администраторов';
    $about = 'О_о Вы в зоне администраторов.';
    $pers = "<a href='game.php?go=char&npc=3'>Куратор</a> ";
    $move = '<a href="/game.php?go=char#" target="_chat_two">В никуда...</a> ';
  }

?>
