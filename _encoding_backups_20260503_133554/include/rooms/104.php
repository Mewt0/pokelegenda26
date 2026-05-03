<?php
$eventDay = true;  
if(!empty($_GET['quest_npc']) && !empty($_GET['do']) && $_GET['quest_npc'] == 1){
   if($_GET['quest_npc'] == 1  && $eventDay) include ("npc/npc_event1.php");
    else die("<script>location.href='game.php?go=char';</script>"); 
}else{
  if(!empty($_GET['npc']) && ($_GET['npc'] == 1 || $_GET['npc'] == 2 || $_GET['npc'] == 3)){
        if ($_GET['npc'] == 1){
            include ("npc/1.php"); 
            $img_r = '<img src="img/room/1_1.png" width="250" height="150">';
        }
        elseif ($_GET['npc'] == 2) include ("npc/shop.php");
  }else{ 
    $name = 'Р С›Р В»Р Т‘РЎРЊР в„–Р В»';
    $about = 'Р С›Р В»Р Т‘РЎРЊР в„–Р В» - Р С•Р Т‘Р С‘Р Р… Р С‘Р В· Р С”РЎР‚РЎС“Р С—Р Р…Р ВµР в„–РЎв‚¬Р С‘РЎвЂ¦ Р С–Р С•РЎР‚Р С•Р Т‘Р С•Р Р† Р ТђР С•РЎРЊР Р…Р Р…. ';
    $pers = '<a href="/game.php?go=char&npc=1&do_npc=pc">Р СџР С•Р С”Р ВµРЎвЂ Р ВµР Р…РЎвЂљРЎР‚</a> | 
             <a href="/game.php?go=char&npc=2">Р СџР С•Р С”Р ВµР СР В°РЎР‚Р С”Р ВµРЎвЂљ</a> ';
    $move = '<a href="/game.php?go=charWork&loc=107" target="_chat_two">Р СџР С•Р В»Р С‘РЎвЂ Р ВµР в„–РЎРѓР С”Р С‘Р в„– РЎС“РЎвЂЎР В°РЎРѓРЎвЂљР С•Р С”</a> | <a href="/game.php?go=charWork&loc=103" target="_chat_two">Р вЂќР С•РЎР‚Р С•Р С–Р В° 101</a> | <a href="/game.php?go=charWork&loc=105" target="_chat_two">Р вЂќР С•РЎР‚Р С•Р С–Р В° 102</a> | <a href="/game.php?go=charWork&loc=106" target="_chat_two">Р вЂќР С•РЎР‚Р С•Р С–Р В° 103</a> ';
    $img_r = '<img src="img/room/101.png" width="290" height="150">';
  } 
}                                                                                          
?>