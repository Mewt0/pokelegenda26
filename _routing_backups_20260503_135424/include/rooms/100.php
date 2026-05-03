<?php  
    $name = 'Вертолет';
    $about = 'Военный вертолет';
    $pers = '<a href="/game.php?go=char&person=1101" target="_chat_two">Высадка</a> | ';
if(!empty($_GET['person']) && $_GET['person'] == 1){
  $quest_isset_const = 1; 
  $name  = 'Военный';
  $about = 'Эвакуация';
  $pers  = '<a href="/game.php?go=char">Хорошо, спасибо.*появилась улыбка на лице*</a>';
  $move  = false;
}
?>
