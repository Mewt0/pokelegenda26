<?php  
    $name = 'Мост';
    $about = 'Совсем новый мост, который, по-видимому, поставили совсем недавно. 
              Вы оглядываетесь вниз и смотрите на кристально чистую воду. 
              Рядом с Вами сидит рыбак, с большим количеством покеболов около него.';
    $pers = '<a href="/game.php?go=char&person=1">Рыбак</a>';
    $move = '<a href="/game.php?go=charWork&loc=19" target="_chat_two">Дорога 4</a> | 
             <a href="/game.php?go=charWork&loc=22" target="_chat_two">Церулин</a>';
if(!empty($_GET['person']) && $_GET['person'] == 1){
  $quest_isset_const = 1; 
  $name  = 'Рыбак';
  $about = 'Иди отсюда! Не мешай мне рыбачить.';
  $pers  = '<a href="/game.php?go=char">Уйти.</a>';
  $move  = false;
}
?>
