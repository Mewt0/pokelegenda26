<?php
    $pers = obr_chis($_GET['quest_npc']);
    $do   = obr_chis($_GET['do']);
    $quest_isset_const = 1;
    $name = "Кондитер";
    $href = '/game.php?go=char&quest_npc='.$pers.'&do=';
     $dat  = date('Y-m-d, H:i:s');
if(!empty($do)){
  if(!empty($eventDay)){

    if(1==1){
      switch ($do){                  
        case 1:
            $about   = "Доброго времени суток! Вы что-то хотели?";
            $pers    = '<a href="'.$href.'2">Здравствуйте! Да, мне нужно что бы Вы, как можно быстрее испекли мне торт. </a>';
            if($eventDay['time'] > 0) $pers   .= '<a href="'.$href.'6">Здравствуйте! Я пришел забрать торт. </a>';
            $pers   .= '<a href="/game.php?go=char">Простите, я ошибся(-лась). <sup>[Уйти]</sup></a>';
        break;
        case 2:
            $about  = " Хм... \"Как можно быстрее\" - говоришь? Ну это зависит не от меня, а от того, как скоро ты сможешь найти ингредиенты для торта.";
            $pers   = '<a href="'.$href.'3">Я постараюсь найти их в кротчайшие сроки. Что именно требуется для торта?</a>';
            $pers  .= '<a href="'.$href.'5">У меня уже есть все ингредиенты, можно приступать к изготовлению торта.</a>';
            $pers  .= '<a href="/game.php?go=char">Ну уж нет, на это у меня совсем нет времени.<sup>[Уйти]</sup></a>';
        break;
        case 3:
            $about  = " Для изготовления торта тебе потребуется: Бутылочка с водой х3, Мешочек с мукой х2, Баночка меда х1, Банка с молоком х1, Ягода х15, Яйцо х5. 
                        Как только ты принесешь мне все ингредиенты я сразу же приступлю к изготовлению торта.";
            $pers   = '<a href="'.$href.'4">Хорошо, а где можно достать эти ингредиенты.</a>';
        break;
        case 4:
            $about  = " Бутылочка с водой выбивается из #120 Staryu<br>
                        Мешочек с мукой выбивается из #133 Eevee<br> 
                        Баночка меда выбивается из #217 Ursaring<br>
                        Банка с молоком выбивается из  #241 Miltank<br>
                        Ягода выбивается из #420 Cherubi<br> 
                        Яйцо выбивается из #242 Blissey<br>
                        Где именно находятся эти покемоны - я и сам не знаю, но вижу, что тренер ты опытный, так что, думаю, тебе это не составит тяжёлого труда.
                        Знаю лишь то, что все покемоны обитают в Канто.";
            $pers   = '<a href="/game.php?go=char">Постараюсь найти. Ждите, я скоро вернусь. <sup>[Уйти]</sup></a>';
        break;
        case 5:
          if(provitems(51,3) && provitems(52,2) && provitems(53,1) && provitems(54,1) && provitems(55,15) && provitems(56,5)){
            minus_item(3,51);
            minus_item(2,52);
            minus_item(1,53);
            minus_item(1,54);
            minus_item(15,55);
            minus_item(5,56);
            $about  = " Отлично! Торт будет готов через три часа.";
            $pers   = '<a href="/game.php?go=char">Хорошо, буду ждать. <sup>[Уйти]</sup></a>';
            update('quest_events',array('time'=>time()+(60*60*3)),'id='.(int)$eventDay['id']);         
          }else{
            $about  = " Увы, но тут не все ингредиенты, что я просил.";
            $pers   = '<a href="/game.php?go=char">Простите... <sup>[Уйти]</sup></a>';            
          }
        break;
        case 6:
          if($eventDay['time'] > 0){
            if($eventDay['time'] <= time()){
               plus_item(1,57);
              $about  = " Вот, возьми этот торт и будь с ним аккуратнее.";
              $pers   = '<a href="/game.php?go=char">Спасибо большое. <sup>[Уйти]</sup></a>';
              update('quest_events',array('time'=>0),'id='.(int)$eventDay['id']);             
            }else{
              $about  = " Торт еще не готов. ". timersOtshet($eventDay["time"],"<br>До приготовления осталось: ", "Торт готов!");
              $pers   = '<a href="/game.php?go=char">Хорошо, буду ждать. <sup>[Уйти]</sup></a>';
            }        
          }else{
            $about  = " Пошел прочь! Тебе нельзя сюда заходить, что ты здесь оставил?!";
            $pers   = '<a href="/game.php?go=char">Простите... <sup>[Уйти]</sup></a>';            
          }
        break;
        default:
           die("<script>location.href='game.php?go=char';</script>");
      }
    }elseif(!empty($eventQuest['id']) && $dat < '2012-10-20, 18:00:00'){
      switch ($do){                  
        case 1:
            $about   = "Ты уже принес, то что требовалось для украшения праздничного зала?";
            $pers    = '<a href="'.$href.'2">Да, конечно же, вот возьми.</a>';
            $pers   .= '<a href="/game.php?go=char">Пока что нет.<sup>[Уйти]</sup></a>';
        break;
        case 2:
          $shar = coolseitems(48,$_SESSION['id']);
          $sharCount = formatnum($shar);
          $sharEvent = formatnum($eventQuest['count_event_1']);
          $lent = coolseitems(49,$_SESSION['id']);
          $lentCount = formatnum($lent);
          $lentEvent = formatnum($eventQuest['count_event_2']);        
          if($shar > 0 || $lent > 0) {
             if($shar > 0) minus_item($shar,48);
             if($lent > 0) minus_item($lent,49);
             update('quest_events',array('count_event_1'=>($eventQuest['count_event_1']+$shar), 
                                         'count_event_2'=>($eventQuest['count_event_2']+$lent) ),'id='.(int)$eventQuest['id']);
          }
          
          if($shar > 0 && $lent <= 0){
            $about  = " Спасибо, что принес эти чудесные шарики! Если сможешь найти еще немного, то я с удовольствием приму их все. 
                        <br> 
                        <b>Отдано шариков: ".$sharEvent." + ".$sharCount." шт.</b>";
            $pers   = '<a  href="/game.php?go=char">Хорошо, я постараюсь. <sup>[Уйти]</sup></a>';
          }elseif($shar <= 0 && $lent > 0){
            $about  = " Отлично! Лента - это то что надо для украшения нашего праздничного зала. 
                        Спасибо, что помог нам с серпантиновой лентой! 
                        Если сможешь найти еще немного, то я с удовольствием возьму ее.
                        <br> 
                        <b>Отдано лент: ".$lentEvent." + ".$lentCount." шт.</b>";
            $pers   = '<a  href="/game.php?go=char">Хорошо, я постараюсь. <sup>[Уйти]</sup></a>';         
          }elseif($shar > 0 && $lent > 0){
            $about  = " Я просто счастлив! Ты принес шарики и ленты, теперь уж точно наш праздничный зал будет украшен по максимуму.
                        Спасибо тебе, что помогаешь в организации праздника! 
                        Если сможешь найти еще немного, то я с удовольствием приму их в любом количестве.
                        <br> 
                        <b>Отдано шариков: ".$sharEvent." + ".$sharCount." шт.</b>
                        <br> 
                        <b>Отдано лент: ".$lentEvent." + ".$lentCount." шт.</b>";
            $pers   = '<a  href="/game.php?go=char">Хорошо, я постараюсь принести еще. <sup>[Уйти]</sup></a>';        
          }else{
            $about  = " Эх... У меня и до этого полно забот, а ты еще и обманывашь меня. 
                        Только зря отвлекаешь от работы. 
                        Если не собираешься помогать, то не мешай работать!";
            $pers   = '<a  href="/game.php?go=char">Прости... <sup>[Уйти]</sup></a>';            
          }
        break;
        default:
           die("<script>location.href='game.php?go=char';</script>");
      }    
    }else{
      switch ($do){                  
        case 1:
           if($eventQuest['count_event_1'] > 0 || $eventQuest['count_event_2'] > 0){
            $about   = "Здравствуй, вот и наступило время пройти в праздничный зал! 
                        Там же ты получишь награду, за помощь в организации этого праздника.";
            $pers    = '<a href="'.$href.'2" target="_chat_two">Хорошо. <sup>[Пройти в зал]</sup></a>';
            $pers   .= '<a href="/game.php?go=char">Прости, но сегодня мне не до праздника.<sup>[Уйти]</sup></a>';
           }else{
            $about   = "Здравствуй, раз уж ты не принимал участие в организации праздника, то пройти ты сможешь только имея при себе специальный пропуск.";
            $pers    = '<a href="'.$href.'2" target="_chat_two">У меня есть пропуск. <sup>[Пройти в зал]</sup></a>';
            $pers   .= '<a href="/game.php?go=char">Оу, печально. Прости за беспокойство.<sup>[Уйти]</sup></a>';            
           }
        break;
        case 2:
         if($eventQuest['count_event_1'] > 0 || $eventQuest['count_event_2'] > 0){
          update('users',array('buildmy'=>43),'id='.(int)$_SESSION['id']);
          die("<script>parent._location.location.href='game.php?go=char';</script>");
         }else{
            if(provitems(50,1)){
               update('users',array('buildmy'=>43),'id='.(int)$_SESSION['id']);
               die("<script>parent._location.location.href='game.php?go=char';</script>");
            }else{
               die("<script>parent._location.location.href='".$href."3';</script>");
            }
         }
        break;
        case 3:
            $about   = "Увы, но пропуска у тебя нет. А без пропуска я не могу пропусть тебя на праздник. ";
            $pers    = '<a href="/game.php?go=char">Хорошо, прости за беспокойство. <sup>[Уйти]</sup></a>';
        break;
        default:
           die("<script>location.href='game.php?go=char';</script>");
      }    
    }
   
  }
}else{
  die("<script>location.href='game.php?go=char';</script>");
}
?>