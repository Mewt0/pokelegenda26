<?php  
$dates = date('H:i:s');
$pc_res = false;
$persX = '<b>Покецентр <sup><small>[Закрыто]</small></sup></b>';
if($dates >= '07:30:00' && $dates <= '17:00:00'){
 $pc_res = true;
 $persX = '<a href="/game.php?go=char&npc=1&do_npc=pc">Покецентр</a>';
}
  if(!empty($_GET['npc']) && $_GET['npc'] == 1 && $pc_res == true){
        if ($_GET['npc'] == 1){
            include ("npc/1.php"); 
            $img_r = '<img src="img/room/1_1.png" width="250" height="150">';
        }
  }else{ 
    $name = 'Город Азалия';
    $about = 'Вы добрались до самого "сухого" города Джотто. 
              По легенде, 100 лет назад в город пришёл слоупок, который зеванием вызвал дождь. 
              За последние 10 лет в городе не было ни одного осадка и облачка. 
              Продвигаясь по улицам, Вы повсюду видите слоупоков. 
              Жителей города, почти, не заметно, как будто весь город вымер. 
              Будьте осторожны с этими покемонами, они везде...';
    $pers = $persX;
    $move = '<a href="/game.php?go=charWork&loc=38" target="_chat_two">Маршрут 7</a> | 
			 <a href="/game.php?go=charWork&loc=86" target="_chat_two">Стадион Психических покемонов</a> ';
 }                                                                                            
?>