<?php 
$qx01 = quest_process(1, 3);
$q7   = isset_qest(7);
$qx07 = quest_process(7, 10);
$pers = false;

if (!empty($_GET['quest_npc']) && !empty($_GET['do'])) {
    if ($_GET['quest_npc'] == 7 && (empty($q7) || !empty($qx07))) {
        include("npc/npc_7.php");
    }
} else {
    if (!empty($_GET['do']) && $qx01) {
        if ($qx01) include("npc/startpoke.php");
        else die("<script>location.href='game.php?go=char';</script>"); 
    } else {
        $name = 'Дорога 1';
        $about = 'Слегка неудобная извилистая тропа.
                  По бокам от неё находятся обширные поля, на которых невооруженным глазом можно заметить множество летающих покемонов. 
                  Где-то вдалеке виднеются старые деревья знаменитого Леса Вертании.';

        if ($qx01) {
            $pers = '<a href="/game.php?go=char&do=1" data-go="char" data-params="do=1">Осмотреться</a>';
        }
        if (!$qx01 && (!$q7 || $qx07)) {
            $pers .= ($pers ? ' | ' : '') . '<a href="/game.php?go=char&quest_npc=7&do=1" data-go="char" data-params="quest_npc=7&do=1">Коллекционер Билли</a>';
        }
        if (!$pers) $pers = '...';

        // переходы по локациям — без target, с data-* для SPA
        $move  = '<a href="/game.php?go=charWork&loc=1" target="_chat_two">Алабастия</a> | ';
        $move .= '<a href="/game.php?go=charWork&loc=5" target="_chat_two">Лес Вертании</a> | ';
        $move .= '<a href="/game.php?go=charWork&loc=10" target="_chat_two">Дорога 2</a>';

        $img_r = '<img src="img/room/004.png" width="290" height="150">';
    }
}
?>
