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
        if($summ_turs <= 0) $summ_turs = "Бесплатно";
          else $summ_turs = formatnum($summ_turs)." Монет";
        if($turs['nametur'])$name_turs = $turs['nametur'];
         else $name_turs = "--";
        if($turs['upr'] > 0)$kuratorid = $turs['upr']; 
          else $kuratorid = 1;
    if($_GET['do_npc'] == 1){ 
      if($_SESSION['id'] == $kuratorid OR $_SESSION['id'] == $admintur){
        $about = "Добро пожаловать, управляющий! Для Вас проход на арену свободный, заходите.";
        $name = "Куратор";
        $pers = "
         <a href='game.php?go=char&npc=3&do_npc=2' target=\"_chat_two\">
          Пройти на Арену.
         </a> 
         <a href='game.php?go=char'>
          Уйти.
         </a> ";      
      
      }else{ 
        $about = "
          Прежде чем пойти на турнир, ознакомься с правилами:<br>
          Предстоящий турнир(название): <b>".$name_turs."</b>.<br> 
          Сумма входа составляет: <b>".$summ_turs."</b>.<br>
          Официальный куратор турнира: ".color_group_users($kuratorid).".";
        $name = "Куратор";
        $pers = "
         <a href='game.php?go=char&npc=3&do_npc=2' target=\"_chat_two\">
          Спасибо, я полностю соглашаюсь, как с правилами самого турнира, так и с Вашими правилами. 
          Также я готов(-а) оплатить стоимость турнира. <sup>[Переход на арену]</sup>
         </a> 
         <a href='game.php?go=char'>
          Спасибо, но этот турнир не для меня.
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
          Извините, но у вас недостаточно средств, чтобы пройти на этот турнир.
          <br> 
          Для прохода вам нужно: <b>".$summ_turs."</b>.
          ";
        $name = "Куратор";
        $pers = "<a href='game.php?go=char'>Хорошо, простите.</a>" ;
    }else{
      if($_SESSION['id'] == $kuratorid OR $_SESSION['id'] == $admintur){
        $about = "Добро пожаловать, управляющий! Для Вас проход на арену свободный, заходите.";
        $name = "Куратор";
        $pers = "
         <a href='game.php?go=char&npc=3&do_npc=2' target=\"_chat_two\">
          Пройти на Арену.
         </a> 
         <a href='game.php?go=char'>
          Уйти.
         </a> ";       
      }else{ 
        $name = "Куратор";
        $about = "Здравствуй тренер, чем я могу тебе помочь?";
        $pers = "
         <a href='game.php?go=char&npc=3&do_npc=1'>
          Здравствуйте, я хотел(-а) бы пройти на турнирную арену.
         </a> 
         <a href='game.php?go=char'>
          Здравствуйте, я просто проходил(-а) мимо.
         </a> ";
      }
  }
}else{
    die("<script>location.href='game.php?go=char';</script>"); 
}

?>