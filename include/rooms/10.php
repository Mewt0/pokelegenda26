<?php
$q8 = first('SELECT * FROM quest WHERE user_id=%d AND quest_id=8', $_SESSION['id']);

if (!empty($_GET['quest_npc']) && !empty($_GET['do'])) {
    if ($_GET['quest_npc'] == 8 && (empty($q8) || $q8['gotov'] == 0)) include("npc/npc_13.php");
    else die("<script>location.href='game.php?go=char';</script>");
} else {
    $name = 'Дорога 2';
    $about = 'Большая дорога, которая, если присмотреться, ведет к озеру. 
              По бокам её видны зеленые луга, кое-где можно заметить пробегающих Понит. 
              Небольшие стайки Баттерфи летают под лучами солнца, а пыльца с их крылышек мягко падает на землю, отражая солнечные лучи. 
              Здесь нет той городской суматохи или какого-либо громкого шума. 
              Одним словом - райское местечко для отдыха.';  
    $pers = '...';
    if (!$q8 || $q8['gotov'] == 0) $pers = '<a href="/game.php?go=char&quest_npc=8&do=1">Цветочный прилавок</a>';
    $move = '<a href="/game.php?go=charWork&loc=4" target="_chat_two">Дорога 1</a> | <a href="/game.php?go=charWork&loc=11" target="_chat_two">Небольшое озеро</a>';
    $img_r = '<img src="img/room/010.png" width="290" height="150">';
}
?>
