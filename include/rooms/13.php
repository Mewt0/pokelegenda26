<?php
// Точное время
$currentTime = date('H:i:s');

// Проверка квеста и условий
$q2x6 = false;

// Время активности квеста — утро с 6:00 до 10:00
if ($currentTime >= '06:00:00' && $currentTime <= '10:00:00') {
    $q2x6 = quest_process(2, 6);

    // Если квест 6 не сработал, проверяем альтернативные условия
    if (!$q2x6 && provitems(46, 1) && quest_process(2, 7) && !questPokemon(2, 144)) {
        $q2x6 = true;
    }
}

// Обработка NPC
if (
    isset($_GET['quest_npc'], $_GET['do']) &&
    $_GET['quest_npc'] == '1'
) {
    if ($q2x6) {
        include("npc/npc_14.php");
        $img_r = '<img src="img/room/npc/nps2-6.png" width="200" height="150" style="position:relative;right:-14%;">';
    } else {
        echo "<script>location.href='game.php?go=char';</script>";
    }
    exit;
}

// Описание локации
$name = 'Скалы';
$about = 'Вы зашли в опасное место. 
          Каменные стены сужаются, и пройти становится всё сложнее и сложнее. 
          Под ногами путаются очень много маленьких диглетов, преграждая дорогу дальше, как будто не хотят, чтобы вы прошли... 
          Может, стоит уйти назад?';

// Персонаж (если условия выполнены)
$pers = $q2x6
    ? "<a href='/game.php?go=char&quest_npc=1&do=1'>#144 Articuno</a>"
    : '...';

// Навигация — если хочешь, можно избавиться от target в $move
$move = '<a href="/game.php?go=charWork&loc=12" target="_chat_two">Дорога 3</a> |
         <a href="/game.php?go=charWork&loc=14" target="_chat_two">Горный перевал</a>';

$img_r = '<img src="img/room/013.png" width="290" height="150">';
?>
