<?php
$currentTime = date('H:i:s');

$qx02 = quest_process(2, 4);
$q2x3 = false;
$q2x8 = false;

// Проверка ночного окна 00:00 – 00:30
if (!$qx02 && $currentTime >= '00:00:00' && $currentTime <= '00:30:00') {
    $q2x3 = quest_process(2, 5);

    if (!$q2x3) {
        $q2x8 = quest_process(2, 8);
    }
}

// Обработка NPC
if (
    isset($_GET['quest_npc'], $_GET['do']) &&
    in_array((int)$_GET['quest_npc'], [1, 2])
) {
    if ((int)$_GET['quest_npc'] === 1 && $qx02) {
        include("npc/npc_10.php");
        exit;
    } elseif ((int)$_GET['quest_npc'] === 2 && ($q2x3 || $q2x8)) {
        include("npc/npc_11.php");
        exit;
    } else {
        echo "<script>location.href='game.php?go=char';</script>";
        exit;
    }
}

// Данные локации
$name = 'Небольшое озеро';
$about = 'Это озеро хоть и не очень большое, но безумно красивое. 
          И днем и ночью можно наслаждаться его красотой. 
          Но особенно привлекательно это место именно ночью, когда лунный свет падает на поверхность воды и начинает казаться, что это место волшебно. 
          Ходят слухи, что если человек посмотрит на свое отражение в воде именно в полнолуние, то может увидеть своего духовного покемона. 
          Но, увы, это лишь слух, хотя кто-то и утверждает, что это правда. 
          Может, он просто избран самими легендами?';

$pers = '...';
if ($qx02) {
    $pers = "<a href='/game.php?go=char&quest_npc=1&do=1'>Художница Амира</a>";
}
if ($q2x3 || $q2x8) {
    $pers = "<a href='/game.php?go=char&quest_npc=2&do=1'>Айрен</a>";
}

// Новая структура ссылок — с data-go и data-params для SPA-переходов
$move = '
  <a href="/game.php?go=charWork&loc=10" target="_chat_two">Дорога 2</a> | 
  <a href="/game.php?go=charWork&loc=12" target="_chat_two">Дорога 3</a>';

$img_r = '<img src="img/room/011.png" width="290" height="150">';
?>
