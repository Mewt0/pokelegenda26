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
    $name = 'Р СџР ВµРЎвЂљР В°Р В»Р В±РЎС“РЎР‚Р С–';
    $about = 'Р С™РЎР‚Р В°РЎРѓР С‘Р Р†РЎвЂ№Р в„– Р С–Р С•РЎР‚Р С•Р Т‘. Р СџР ВµРЎвЂљР В°Р В»Р В±РЎС“РЎР‚Р С– РЎРѓР В»Р В°Р Р†Р С‘РЎвЂљРЎРѓРЎРЏ РЎРѓР Р†Р С•Р С‘Р СР С‘ Р С—РЎР‚Р С‘Р С”РЎР‚Р В°РЎРѓР Р…РЎвЂ№Р СР С‘ РЎРѓР В°Р Т‘Р В°Р СР С‘.';
    $pers = '<a href="/game.php?go=char&npc=1&do_npc=pc">Р СџР С•Р С”Р ВµРЎвЂ Р ВµР Р…РЎвЂљРЎР‚</a> | 
             <a href="/game.php?go=char&npc=2">Р СџР С•Р С”Р ВµР СР В°РЎР‚Р С”Р ВµРЎвЂљ</a> ';
    $move = '<a href="/game.php?go=charWork&loc=105" target="_chat_two">Р вЂќР С•РЎР‚Р С•Р С–Р В° 102</a> ';
    $img_r = '<img src="img/room/108.png" width="290" height="150">';
  } 
}                                                                                          
?>