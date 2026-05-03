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
    $name = 'Р С›Р В»Р С‘Р Р†Р С‘Р Р… Р РЋР С‘РЎвЂљР С‘';
    $about = 'Р С›Р В»Р С‘Р Р†Р С‘Р Р… РЎРѓР С‘РЎвЂљР С‘ - Р С•Р Т‘Р С‘Р Р… Р С‘Р В· Р С”РЎР‚РЎС“Р С—Р Р…Р ВµР в„–РЎв‚¬Р С‘РЎвЂ¦ Р С–Р С•РЎР‚Р С•Р Т‘Р С•Р Р† Р вЂќР В¶Р С•РЎвЂљРЎвЂљР С•. 
              Р СћР В°Р С” Р В¶Р Вµ Р С•Р Р… РЎРЏР Р†Р В»РЎРЏР ВµРЎвЂљРЎРѓРЎРЏ РЎРѓР В°Р СРЎвЂ№Р С Р С—РЎР‚Р С‘Р Р†Р В»Р ВµР С”Р В°РЎвЂљР ВµР В»РЎРЉР Р…РЎвЂ№Р С Р С–Р С•РЎР‚Р С•Р Т‘Р С•Р С Р С‘Р В·-Р В·Р В° РЎвЂљР С•Р С–Р С•, РЎвЂЎРЎвЂљР С• Р С•Р Р… Р Р…Р В°РЎвЂ¦Р С•Р Т‘Р С‘РЎвЂљРЎРѓРЎРЏ РЎР‚РЎРЏР Т‘Р С•Р С РЎРѓ Р СР С•РЎР‚Р ВµР С Р С‘ РЎРѓР В°Р СР С•Р в„– Р В±Р С•Р В»РЎРЉРЎв‚¬Р С•Р в„– РЎР‚Р В°РЎРѓРЎвЂљР С‘РЎвЂљР ВµР В»РЎРЉР Р…Р С•РЎРѓРЎвЂљРЎРЉРЎР‹ Р Р† РЎР‚Р ВµР С–Р С‘Р С•Р Р…Р Вµ.';
    $pers = '<a href="/game.php?go=char&npc=1&do_npc=pc">Р СџР С•Р С”Р ВµРЎвЂ Р ВµР Р…РЎвЂљРЎР‚</a> | 
             <a href="/game.php?go=char&npc=2">Р СџР С•Р С”Р ВµР СР В°РЎР‚Р С”Р ВµРЎвЂљ</a> | 
             <a href="game.php?go=char&npc=3">Р С™РЎС“РЎР‚Р В°РЎвЂљР С•РЎР‚</a> ';
    $move = '<a href="/game.php?go=charWork&loc=25" target="_chat_two">Р СџР С•РЎР‚РЎвЂљ Р вЂќР В¶Р С•РЎвЂљРЎвЂљР С•</a> | 
             <a href="/game.php?go=charWork&loc=28" target="_chat_two">Р В¦Р ВµР Р…РЎвЂљРЎР‚ Р С›Р В»Р С‘Р Р†Р С‘Р Р…Р В°</a> | 
             <a href="/game.php?go=charWork&loc=84" target="_chat_two">Р РЋРЎвЂљР В°Р Т‘Р С‘Р С•Р Р… Р вЂ™Р С•Р Т‘Р Р…РЎвЂ№РЎвЂ¦ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р С•Р Р†</a> | 
             <a href="/game.php?go=charWork&loc=45" target="_chat_two">Р СњР В°РЎвЂ Р С‘Р С•Р Р…Р В°Р В»РЎРЉР Р…РЎвЂ№Р в„– Р С—Р В°РЎР‚Р С”</a> | 
             <a href="/game.php?go=charWork&loc=27" target="_chat_two">Р СџР В»РЎРЏР В¶</a>';
  } 
}                                                                                          
?>