<?php
$name = 'Горный перевал';
$about = 'Вы подошли к табличке, покрытой ржавчиной. 
          Краска с неё почти стерлась, но всё еще можно разглядеть надпись: "Осторожно! Дорога закрыта из-за сильного камнепада". 
          У вас есть два варианта - уйти назад или продолжить путь вперед...';
$pers = '...';

// Обновлённые переходы без target, с data-* атрибутами для AJAX-обработки
$move = '
  <a href="/game.php?go=charWork&loc=13" target="_chat_two">Скалы</a> | 
  <a href="/game.php?go=charWork&loc=41" target="_chat_two">Археологическая пещера</a> | 
  <a href="/game.php?go=charWork&loc=15" target="_chat_two">Дорога к вершине</a> | 
  <a href="/game.php?go=charWork&loc=18" target="_chat_two">Пьютер</a>';

$img_r = '<img src="img/room/014.png" width="290" height="150">';
?>
