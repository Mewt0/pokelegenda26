<?php
$qx01 = quest_process(1, 2);
$q2   = isset_qest(2);
$eventDay = true;

if (!empty($_GET['quest_npc']) && !empty($_GET['do']) && ($_GET['quest_npc'] == 1 || $_GET['quest_npc'] == 2 || $_GET['quest_npc'] == 3)) {
    if ($_GET['quest_npc'] == 2 && $qx01) include ("npc/npc_2.php");
    elseif ($_GET['quest_npc'] == 3 && !$q2) include ("npc/npc_3.php");
    elseif ($_GET['quest_npc'] == 1 && $eventDay) include ("npc/npc_event2.php");
    else die("<script>location.href='game.php?go=char';</script>");
} else {
    if (!empty($_GET['npc']) && ($_GET['npc'] == 1 || $_GET['npc'] == 2 || $_GET['npc'] == 3)) {
        if ($_GET['npc'] == 1) {
            include ("npc/1.php");
            $img_r = '<img src="img/room/1_1.png" width="250" height="150">';
        } elseif ($_GET['npc'] == 2) {
            include ("npc/shop.php");
        } elseif ($_GET['npc'] == 3) {
            include ("npc/kurator.php");
        }
    } else {
        $name = 'Алабастия';
        $about = 'Город представляет собой огромный частный сектор с пышными землями. 
                  Дома расположены на внушительном расстоянии один от одного, поэтому может показаться, что живут в Алабастии исключительно богачи. 
                  Сам город славится питомником и лабораторией знаменитого профессора Оука. 
                  Многие новички начинают здесь свой путь, получая стартового покемона.';

        // ссылки на char — без target, с data-go/data-params (SPA), и со слешем перед game.php
        $pers  = '<a href="/game.php?go=char&npc=1&do_npc=pc" data-go="char" data-params="npc=1&do_npc=pc">Покецентр</a> | ';
        $pers .= '<a href="/game.php?go=char&npc=2" data-go="char" data-params="npc=2">Покемаркет</a> | ';
        $pers .= '<a href="/game.php?go=char&npc=3" data-go="char" data-params="npc=3">Куратор</a> ';
        if ($qx01) $pers .= '| <a href="/game.php?go=char&quest_npc=2&do=1" data-go="char" data-params="quest_npc=2&do=1">Случайный прохожий</a> ';
        if (!$q2)  $pers .= '| <a href="/game.php?go=char&quest_npc=3&do=1" data-go="char" data-params="quest_npc=3&do=1">Странный Спайк</a> ';

        // переходы по локациям — на charWork, без target, с data-* для перехватчика
        $move  = '<a href="/game.php?go=charWork&loc=3" target="_chat_two">Лаборатория</a> | ';
        $move .= '<a href="/game.php?go=charWork&loc=17" target="_chat_two">Здание Администрации</a> | ';
        $move .= '<a href="/game.php?go=charWork&loc=81" target="_chat_two">Стадион Огненных покемонов</a> | ';
        $move .= '<s>Зона тренировки</s> | <s>Северная Алабастия</s> | ';
        $move .= '<a href="/game.php?go=charWork&loc=4" target="_chat_two">Дорога 1</a>';

        $img_r = '<img src="img/room/001.png" width="290" height="150">';
    }
}
?>
