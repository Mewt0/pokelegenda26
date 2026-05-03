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
        elseif ($_GET['npc'] == 3) include ("npc/kurator.php");
  }else{ 
    $name = 'Р вЂєР С‘РЎвЂљРЎвЂљР В»РЎР‚РЎС“Р Т‘';
    $about = 'Р вЂєР С‘РЎвЂљРЎвЂљР В»РЎР‚РЎС“Р Т‘ - Р С•Р Т‘Р С‘Р Р… Р С‘Р В· Р С”РЎР‚РЎС“Р С—Р Р…Р ВµР в„–РЎв‚¬Р С‘РЎвЂ¦ Р С–Р С•РЎР‚Р С•Р Т‘Р С•Р Р† Р ТђР С•РЎРЊР Р…Р Р…. ';
    $pers = '<a href="/game.php?go=char&npc=1&do_npc=pc">Р СџР С•Р С”Р ВµРЎвЂ Р ВµР Р…РЎвЂљРЎР‚</a> | 
             <a href="/game.php?go=char&npc=2">Р СџР С•Р С”Р ВµР СР В°РЎР‚Р С”Р ВµРЎвЂљ</a> | 
             <a href="game.php?go=char&npc=3">Р С™РЎС“РЎР‚Р В°РЎвЂљР С•РЎР‚</a> ';
    $move = '<a href="/game.php?go=charWork&loc=98" target="_chat_two">Р вЂєР В°Р В±Р С•РЎР‚Р В°РЎвЂљР С•РЎР‚Р С‘РЎРЏ</a> | <a href="/game.php?go=charWork&loc=99" target="_chat_two">Р вЂќР С•РЎР‚Р С•Р С–Р В° Р Р† Р С—Р С•РЎР‚РЎвЂљ</a> | <a href="/game.php?go=charWork&loc=102" target="_chat_two">Р вЂєР ВµРЎРѓ Р вЂєР С‘РЎвЂљРЎвЂљР В»РЎР‚РЎС“Р Т‘Р В°</a> ';
    $img_r = '<img src="img/room/101.png" width="290" height="150">';
  } 
}                                                                                          
?>