<?
if($_GET['npc'] == 3){    
    function dels_locs(){
      delete('users_locvoz','userid='.(int)$_SESSION['id'].' AND tip="arena"'); 
      die("<script>parent._location.location.href='/game.php?go=char';</script>");         
    }
    if($myrow['groups'] == 1) $admintur = $myrow['id']; else $admintur = 1;
    $quest_isset_const = 1;
    $turs = first('SELECT * FROM kurator_npc WHERE id=1');    
      $summ_turs = $turs['stoimost'];
        if($summ_turs <= 0) $summ_turs = "Р вЂР ВµРЎРѓР С—Р В»Р В°РЎвЂљР Р…Р С•";
          else $summ_turs = formatnum($summ_turs)." Р СљР С•Р Р…Р ВµРЎвЂљ";
        if($turs['nametur'])$name_turs = $turs['nametur'];
         else $name_turs = "--";
        if($turs['upr'] > 0)$kuratorid = $turs['upr']; 
          else $kuratorid = 1;
    if($_GET['do_npc'] == 1){ 
      if($_SESSION['id'] == $kuratorid OR $_SESSION['id'] == $admintur){
        $about = "Р вЂќР С•Р В±РЎР‚Р С• Р С—Р С•Р В¶Р В°Р В»Р С•Р Р†Р В°РЎвЂљРЎРЉ, РЎС“Р С—РЎР‚Р В°Р Р†Р В»РЎРЏРЎР‹РЎвЂ°Р С‘Р в„–! Р вЂќР В»РЎРЏ Р вЂ™Р В°РЎРѓ Р С—РЎР‚Р С•РЎвЂ¦Р С•Р Т‘ Р Р…Р В° Р В°РЎР‚Р ВµР Р…РЎС“ РЎРѓР Р†Р С•Р В±Р С•Р Т‘Р Р…РЎвЂ№Р в„–, Р В·Р В°РЎвЂ¦Р С•Р Т‘Р С‘РЎвЂљР Вµ.";
        $name = "Р С™РЎС“РЎР‚Р В°РЎвЂљР С•РЎР‚";
        $pers = "
         <a href='game.php?go=char&npc=3&do_npc=2' target=\"_chat_two\">
          Р СџРЎР‚Р С•Р в„–РЎвЂљР С‘ Р Р…Р В° Р С’РЎР‚Р ВµР Р…РЎС“.
         </a> 
         <a href='game.php?go=char'>
          Р Р€Р в„–РЎвЂљР С‘.
         </a> ";      
      
      }else{ 
        $about = "
          Р СџРЎР‚Р ВµР В¶Р Т‘Р Вµ РЎвЂЎР ВµР С Р С—Р С•Р в„–РЎвЂљР С‘ Р Р…Р В° РЎвЂљРЎС“РЎР‚Р Р…Р С‘РЎР‚, Р С•Р В·Р Р…Р В°Р С”Р С•Р СРЎРЉРЎРѓРЎРЏ РЎРѓ Р С—РЎР‚Р В°Р Р†Р С‘Р В»Р В°Р СР С‘:<br>
          Р СџРЎР‚Р ВµР Т‘РЎРѓРЎвЂљР С•РЎРЏРЎвЂ°Р С‘Р в„– РЎвЂљРЎС“РЎР‚Р Р…Р С‘РЎР‚(Р Р…Р В°Р В·Р Р†Р В°Р Р…Р С‘Р Вµ): <b>".$name_turs."</b>.<br> 
          Р РЋРЎС“Р СР СР В° Р Р†РЎвЂ¦Р С•Р Т‘Р В° РЎРѓР С•РЎРѓРЎвЂљР В°Р Р†Р В»РЎРЏР ВµРЎвЂљ: <b>".$summ_turs."</b>.<br>
          Р С›РЎвЂћР С‘РЎвЂ Р С‘Р В°Р В»РЎРЉР Р…РЎвЂ№Р в„– Р С”РЎС“РЎР‚Р В°РЎвЂљР С•РЎР‚ РЎвЂљРЎС“РЎР‚Р Р…Р С‘РЎР‚Р В°: ".color_group_users($kuratorid).".";
        $name = "Р С™РЎС“РЎР‚Р В°РЎвЂљР С•РЎР‚";
        $pers = "
         <a href='game.php?go=char&npc=3&do_npc=2' target=\"_chat_two\">
          Р РЋР С—Р В°РЎРѓР С‘Р В±Р С•, РЎРЏ Р С—Р С•Р В»Р Р…Р С•РЎРѓРЎвЂљРЎР‹ РЎРѓР С•Р С–Р В»Р В°РЎв‚¬Р В°РЎР‹РЎРѓРЎРЉ, Р С”Р В°Р С” РЎРѓ Р С—РЎР‚Р В°Р Р†Р С‘Р В»Р В°Р СР С‘ РЎРѓР В°Р СР С•Р С–Р С• РЎвЂљРЎС“РЎР‚Р Р…Р С‘РЎР‚Р В°, РЎвЂљР В°Р С” Р С‘ РЎРѓ Р вЂ™Р В°РЎв‚¬Р С‘Р СР С‘ Р С—РЎР‚Р В°Р Р†Р С‘Р В»Р В°Р СР С‘. 
          Р СћР В°Р С”Р В¶Р Вµ РЎРЏ Р С–Р С•РЎвЂљР С•Р Р†(-Р В°) Р С•Р С—Р В»Р В°РЎвЂљР С‘РЎвЂљРЎРЉ РЎРѓРЎвЂљР С•Р С‘Р СР С•РЎРѓРЎвЂљРЎРЉ РЎвЂљРЎС“РЎР‚Р Р…Р С‘РЎР‚Р В°. <sup>[Р СџР ВµРЎР‚Р ВµРЎвЂ¦Р С•Р Т‘ Р Р…Р В° Р В°РЎР‚Р ВµР Р…РЎС“]</sup>
         </a> 
         <a href='game.php?go=char'>
          Р РЋР С—Р В°РЎРѓР С‘Р В±Р С•, Р Р…Р С• РЎРЊРЎвЂљР С•РЎвЂљ РЎвЂљРЎС“РЎР‚Р Р…Р С‘РЎР‚ Р Р…Р Вµ Р Т‘Р В»РЎРЏ Р СР ВµР Р…РЎРЏ.
         </a> ";
      }
    }elseif($_GET['do_npc'] == 2){  
          $b = first('SELECT * FROM users_locvoz WHERE userid=%d AND tip="arena"',$_SESSION['id']);
          if($_SESSION['id'] == $kuratorid OR $_SESSION['id'] == $admintur){    
              if(!$b){ 
                  insert('users_locvoz',array('userid'=>$_SESSION['id'], 'locid'=>$myrow['buildmy'], 'tip'=>'arena'));
                  update('users',array('buildmy'=>40),'id='.(int)$_SESSION['id']);
                  die("<script>parent._location.location.href='/game.php?go=char';</script>"); 
              }else  dels_locs();
          }else{ 
              if(!$b){
               if(provitems(1,$turs['stoimost'])){
                  $tnal = $turs['stoimost'];
                  minus_item($tnal,1);
                  
                  $tnalon = $tnal*0.05;
                  nalog_clanz($tnalon);
                  
                  $tnal = $tnal - $tnalon;
                  $tnal = $tnal*0.5; 
                  $xtnal = $turs['fond']+$tnal;                                       
                  update('kurator_npc',array('fond'=>$xtnal),'id=1');
                  insert('users_locvoz',array('userid'=>$_SESSION['id'], 'locid'=>$myrow['buildmy'], 'tip'=>'arena'));
                  update('users',array('buildmy'=>40),'id='.(int)$_SESSION['id']);
                  die("<script>parent._location.location.href='/game.php?go=char';</script>");
               }else echo "<script>parent._location.location.href='/game.php?go=char&npc=3&do_npc=3';</script>";  
              }else
                dels_locs();
          }
    }elseif($_GET['do_npc'] == 3){  
        $about = "
          Р ВР В·Р Р†Р С‘Р Р…Р С‘РЎвЂљР Вµ, Р Р…Р С• РЎС“ Р Р†Р В°РЎРѓ Р Р…Р ВµР Т‘Р С•РЎРѓРЎвЂљР В°РЎвЂљР С•РЎвЂЎР Р…Р С• РЎРѓРЎР‚Р ВµР Т‘РЎРѓРЎвЂљР Р†, РЎвЂЎРЎвЂљР С•Р В±РЎвЂ№ Р С—РЎР‚Р С•Р в„–РЎвЂљР С‘ Р Р…Р В° РЎРЊРЎвЂљР С•РЎвЂљ РЎвЂљРЎС“РЎР‚Р Р…Р С‘РЎР‚.
          <br> 
          Р вЂќР В»РЎРЏ Р С—РЎР‚Р С•РЎвЂ¦Р С•Р Т‘Р В° Р Р†Р В°Р С Р Р…РЎС“Р В¶Р Р…Р С•: <b>".$summ_turs."</b>.
          ";
        $name = "Р С™РЎС“РЎР‚Р В°РЎвЂљР С•РЎР‚";
        $pers = "<a href='game.php?go=char'>Р ТђР С•РЎР‚Р С•РЎв‚¬Р С•, Р С—РЎР‚Р С•РЎРѓРЎвЂљР С‘РЎвЂљР Вµ.</a>" ;
    }else{
      if($_SESSION['id'] == $kuratorid OR $_SESSION['id'] == $admintur){
        $about = "Р вЂќР С•Р В±РЎР‚Р С• Р С—Р С•Р В¶Р В°Р В»Р С•Р Р†Р В°РЎвЂљРЎРЉ, РЎС“Р С—РЎР‚Р В°Р Р†Р В»РЎРЏРЎР‹РЎвЂ°Р С‘Р в„–! Р вЂќР В»РЎРЏ Р вЂ™Р В°РЎРѓ Р С—РЎР‚Р С•РЎвЂ¦Р С•Р Т‘ Р Р…Р В° Р В°РЎР‚Р ВµР Р…РЎС“ РЎРѓР Р†Р С•Р В±Р С•Р Т‘Р Р…РЎвЂ№Р в„–, Р В·Р В°РЎвЂ¦Р С•Р Т‘Р С‘РЎвЂљР Вµ.";
        $name = "Р С™РЎС“РЎР‚Р В°РЎвЂљР С•РЎР‚";
        $pers = "
         <a href='game.php?go=char&npc=3&do_npc=2' target=\"_chat_two\">
          Р СџРЎР‚Р С•Р в„–РЎвЂљР С‘ Р Р…Р В° Р С’РЎР‚Р ВµР Р…РЎС“.
         </a> 
         <a href='game.php?go=char'>
          Р Р€Р в„–РЎвЂљР С‘.
         </a> ";       
      }else{ 
        $name = "Р С™РЎС“РЎР‚Р В°РЎвЂљР С•РЎР‚";
        $about = "Р вЂ”Р Т‘РЎР‚Р В°Р Р†РЎРѓРЎвЂљР Р†РЎС“Р в„– РЎвЂљРЎР‚Р ВµР Р…Р ВµРЎР‚, РЎвЂЎР ВµР С РЎРЏ Р СР С•Р С–РЎС“ РЎвЂљР ВµР В±Р Вµ Р С—Р С•Р СР С•РЎвЂЎРЎРЉ?";
        $pers = "
         <a href='game.php?go=char&npc=3&do_npc=1'>
          Р вЂ”Р Т‘РЎР‚Р В°Р Р†РЎРѓРЎвЂљР Р†РЎС“Р в„–РЎвЂљР Вµ, РЎРЏ РЎвЂ¦Р С•РЎвЂљР ВµР В»(-Р В°) Р В±РЎвЂ№ Р С—РЎР‚Р С•Р в„–РЎвЂљР С‘ Р Р…Р В° РЎвЂљРЎС“РЎР‚Р Р…Р С‘РЎР‚Р Р…РЎС“РЎР‹ Р В°РЎР‚Р ВµР Р…РЎС“.
         </a> 
         <a href='game.php?go=char'>
          Р вЂ”Р Т‘РЎР‚Р В°Р Р†РЎРѓРЎвЂљР Р†РЎС“Р в„–РЎвЂљР Вµ, РЎРЏ Р С—РЎР‚Р С•РЎРѓРЎвЂљР С• Р С—РЎР‚Р С•РЎвЂ¦Р С•Р Т‘Р С‘Р В»(-Р В°) Р СР С‘Р СР С•.
         </a> ";
      }
  }
}else{
    die("<script>location.href='game.php?go=char';</script>"); 
}

?>