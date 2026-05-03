<?php
// Поддержка PHP 5.4: нет short arrays, нет ??

if (!empty($_GET['npc']) && !empty($_GET['do'])) {
    if ($_GET['npc'] == '1') {
        include('npc/npc_8.php');
    } else {
        echo "<script>location.href='game.php?go=char';</script>";
    }
    exit;
}

// Описание локации
$name = 'Здание Администрации';
$about = 'Вы вошли в здание, где довольно часто можно застать Администраторов. 
          Изысканно украшенные коридоры тщательно охраняются специально обученными покемонами, 
          если пройти чуть вперед, вы увидите девушку, увлеченную своей повседневной работой.';

$pers = '<a href="/game.php?go=char&npc=1&do=1">Секретарь</a>';

// Навигация — без target="_chat_two", можно добавить data-go для JS-перехвата
$move = '<a href="/game.php?go=charWork&loc=1" target="_chat_two">Алабастия</a>';

$img_r = '<img src="img/room/27.png" width="250" height="150">';
?>
