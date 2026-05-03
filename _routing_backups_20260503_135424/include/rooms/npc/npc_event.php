<?php
    $pers = obr_chis($_GET['quest_npc']);
    $do   = obr_chis($_GET['do']);
    $quest_isset_const = 1;
    $name = "Роберт";
    $href = '/game.php?go=char&quest_npc='.$pers.'&do=';
     $eventQuest = first('SELECT * FROM quest_events WHERE quest=1 AND users=%d',$_SESSION['id']);
     $dat  = date('Y-m-d, H:i:s');
if(!empty($do)){
  if(!empty($eventDay)){

    if(empty($eventQuest['id'])){
      switch ($do){                  
        case 1:
            $about   = "Добрый день. Ты наверное слышал о том, что скоро состоится большой праздник, на который приглашаются все желающие?";
            $pers    = '<a href="'.$href.'2">Здравствуй! Конечно же!</a>';
            $pers   .= '<a href="/game.php?go=char">Праздник? Извини, но мне не до них.<sup>[Уйти]</sup></a>';
        break;
        case 2:
            $about  = " Ну что же, тебе придется постараться, чтобы получить приглашение. 
                        В этот праздник наши дизайнеры предложили нам хорошую идею - построить в центре праздничного зала 5-ти метрового Саикуна, из воздушных шаров.
                        Мы с моими коллегами, собственно, не против, но вот проблема, как нам насобирать 5.000 шариков за 2 дня?
                        Если ты сможешь помочь нам в этом, то мы с удовольстивем выдадим тебе приглашение на праздник! 
                        А так же, не придешь же ты на праздник с пустыми руками?  
                        Дак вот, в Церулине есть кондитер, который готов испечь торт, если ты предоставишь ему нужные ингредиенты.  
                        Ну а праздничный подарок ты сможешь приобрести в Пьютере у торговца.";
            $pers   = '<a href="'.$href.'3">Хм... Хорошо, я возьмусь за это дело.</a>';
            $pers   .= '<a href="/game.php?go=char">Ох, я уж лучше куплю приглашение. Прости, но у меня нет времени на это.<sup>[Уйти]</sup></a>';
        break;
        case 3:
            $about  = "Вот и хорошо! Постарайся как можно больше насобирать воздушных шариков, а так же, если будет свободное время, то нам бы не помешало немного серпантиновой ленты. 
                       Конечно же вся твоя помощь не пройдет даром, чем больше ты сможешь помочь нам, тем лучше будет подарок. ";
            $pers   = '<a href="'.$href.'4" target="_chat_two">Хорошо, помогу чем смогу.</a>';
        break;
        case 4:
          if(empty($eventQuest['id'])) insert('quest_events',array('quest'=>1, 'users'=>$_SESSION['id'], 'etapes'=>1));
          die("<script>parent._location.location.href='game.php?go=char';</script>");
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