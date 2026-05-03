<?php
// Поддержка PHP 5.4

$pers = false;

$q1   = isset_qest(1);
$qx01 = quest_process(1, 4);

$q5   = isset_qest(5);
$qx05 = quest_process(5, 10);

$q6   = isset_qest(6);

// Обработка событий от NPC
if (
    isset($_GET['quest_npc']) && !empty($_GET['do']) &&
    in_array((int)$_GET['quest_npc'], array(1, 2, 3))
) {
    $npcId = (int)$_GET['quest_npc'];

    if ($npcId == 1 && (!$q1 || $qx01)) {
        include('npc/npc_1.php');
    } elseif ($npcId == 2 && (!$q5 || $qx05)) {
        include('npc/npc_6.php');
    } elseif ($npcId == 3 && $q5 && empty($qx05)) {
        include('npc/npc_6.php');
    } else {
        echo "<script>location.href='game.php?go=char';</script>";
    }

    exit;
}

// Описание локации
$name = 'Лаборатория профессора Оука';
$about = 'Снаружи здание выглядит огромным, однако дизайн лаборатории весьма незамысловат. 
          Но стоит только зайти вовнутрь, как в глаза бросается большое количество технологий: от простых до самых современных. 
          Помимо этого, в здании на нескольких этажах размещается множество комнат-лабораторий. 
          Пройдя по длинному коридору, можно увидеть большую лабораторию, где работает Профессор Оук.';

// Персонажи на локации
if (!$q1 || $qx01) {
    $pers = "<a href='/game.php?go=char&quest_npc=1&do=1'>Профессор Оук</a>";
}
if (!$q5 || $qx05) {
    $pers .= ($pers ? ' | ' : '') . "<a href='/game.php?go=char&quest_npc=2&do=1'>Исследователь</a>";
}
if ($q5 && empty($qx05)) {
    $pers .= ($pers ? ' | ' : '') . "<a href='/game.php?go=char&quest_npc=3&do=1'>Исследователь</a>";
}
if ($pers === false) {
    $pers = '...';
}

// Навигация — заменён устаревший target на data-* атрибуты
$move = '<a href="/game.php?go=charWork&loc=1" target="_chat_two">Алабастия</a>';

$img_r = '<img src="img/room/003.png" width="290" height="150">';
?>
