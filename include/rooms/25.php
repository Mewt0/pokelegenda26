<?php  
  if(!empty($_GET['npc']) && ($_GET['npc'] == 1 || $_GET['npc'] == 2 || $_GET['npc'] == 3)){
        if ($_GET['npc'] == 1) include ("npc/shop_tiket.kanto.php");
        elseif ($_GET['npc'] == 2) include ("npc/shop.php");
        elseif ($_GET['npc'] == 3) include ("npc/npc_tiket.dhotto.php");
  }else{ 
    $name = 'Порт Джотто';
    $about = 'Вот Вы и прибыли в Порт Джотто. 
              Первое, что вам попадается на глаза - это несколько кораблей и теплоходов, которые стоят друг с другом около причала. 
              От сюда можно отправится в любой другой регион. 
              Так же, именно отсюда начинается новое путешествие покетренеров, которые прыбыли из других регионов.';
    $pers = '<a href="/game.php?go=char&npc=2">Покемаркет</a> | 
             <a href="/game.php?go=char&npc=1">Касса</a>';
    $move = '<a href="/game.php?go=char&npc=3&do=1">Теплоход</a> | <a href="/game.php?go=charWork&loc=26" target="_chat_two">Оливин Сити</a>' ;
  }
?>
