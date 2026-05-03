<?php  
  if(!empty($_GET['npc']) && ($_GET['npc'] == 1 || $_GET['npc'] == 2)){
        if ($_GET['npc'] == 1){
            include ("npc/1.php"); 
            $img_r = '<img src="img/room/1_1.png" width="250" height="150">';
        }
        elseif ($_GET['npc'] == 2) include ("npc/shop.php");
  }else{ 
    $name = 'Р В¤Р С•РЎР‚РЎвЂљРЎР‚Р С‘ Р РЋР С‘РЎвЂљР С‘';
    $about = '...';
    $pers = '<a href="/game.php?go=char&npc=1&do_npc=pc">Р СџР С•Р С”Р ВµРЎвЂ Р ВµР Р…РЎвЂљРЎР‚</a> | <a href="/game.php?go=char&npc=2">Р СџР С•Р С”Р ВµР СР В°РЎР‚Р С”Р ВµРЎвЂљ</a>';
    $move = '<a href="/game.php?go=charWork&loc=152" target="_chat_two">Р вЂќР С•РЎР‚Р С•Р С–Р В° 119</a> | <a href="/game.php?go=charWork&loc=154" target="_chat_two">Р вЂќР С•РЎР‚Р С•Р С–Р В° 120</a>';
  }
?>