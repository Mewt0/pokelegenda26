<?php
    $perse = obr_chis($_GET['quest_npc']);
    $do   = obr_chis($_GET['do']);
    $quest_isset_const = 1;
    $href = '/game.php?go=char&quest_npc='.$perse.'&do=';
     $eventDay   = true;
     $dat  = date('Y-m-d, H:i:s');
if(!empty($do)){
  if(!empty($eventDay)){

    if($perse == 1){
      $name = "<span style='font-weight:bold;color:brown;'>Makasimka</span>";
      switch ($do){                  
        case 1:
            $about   = "Добрый вечер. Что-то хотел(а)?";
            $pers    = '';
            $pers   .= '<a href="/game.php?go=char">Спасибо, что организовали такой праздник.</a>';
        break;
        default:
           die("<script>location.href='game.php?go=char';</script>");
      }
    }elseif($perse == 2){
      $name = "<span style='font-weight:bold;color:brown;'>LEGENDA</span>";
      switch ($do){                  
        case 1:
            $about   = "Привет. Я очень рад, что ты пришел(-шла) на мой день рождения! 
                        Угощайся и чувствуй себя как дома.";
            $pers    = '';
            if($eventQuest['etapes'] == 1) $pers   .= '<a href="'.$href.'2">Здравствуйте. Спасибо, я обязательно попробую. Я бы хотел(-а) поздравить тебя с днем рождения. Вот держи этот подарок и торт ^_^.</a>';
            $pers   .= '<a href="/game.php?go=char">Хорошо...<sup>[Уйти]</sup></a>';
        break;
        case 2:
         if($eventQuest['etapes'] == 1){
          $x01 = provitems(57,1);
          $x02 = provitems(58,1);
          $x03 = provitems(59,1);
          $x04 = provitems(60,1);          
          if($x01 || $x02 || $x03 || $x04){
             if($x01 > 0) { minus_item(1,57); $_SESSION['PRIZE_LEGEND'][0] = true;}
             if($x02 > 0) { minus_item(1,58); $_SESSION['PRIZE_LEGEND'][1] = 58;}          
             if($x03 > 0) { minus_item(1,59); $_SESSION['PRIZE_LEGEND'][1] = 59;}
             if($x04 > 0) { minus_item(1,60); $_SESSION['PRIZE_LEGEND'][1] = 60;}
            $about  = "  Очень приятно. Спасибо большое. ";
            $pers   = '<a  href="/game.php?go=char">Спасибо и тебе за праздник. <sup>[Уйти]</sup></a>';
            update('quest_events',array('etapes'=>2),'id='.(int)$eventQuest['id']);
          }else{
            $about  = " Если пришел без подарка, то так и скажи. Зачем обманывать-то? ";
            $pers   = '<a  href="/game.php?go=char">Хорошо, прости. <sup>[Уйти]</sup></a>';         
          }
         }else{
            $about  = " Спасибо, но ты уже дарил мне подарок. Второй я уже не принимаю. ";
            $pers   = '<a  href="/game.php?go=char">Хорошо, прости. <sup>[Уйти]</sup></a>';             
         }
        break;
        default:
           die("<script>location.href='game.php?go=char';</script>");
      }    
    }elseif($perse == 3){
      $name = "<span style='font-weight:bold;color:brown;'>MiladyMio</span>";
      switch ($do){                  
        case 1:
            $about   = "Добро пожаловать. Надеюсь ты здесь сможешь повеселиться как следует.";
            $pers    = '<a href="/game.php?go=char">Спасибо, я тоже не это надеюсь.<sup>[Уйти]</sup></a>';            
        break;
        default:
           die("<script>location.href='game.php?go=char';</script>");
      }    
    }elseif($perse == 4){
      $name = "Роберт";
      switch ($do){                  
        case 1:
          if($eventQuest['count_event_1'] > 0 || $eventQuest['count_event_2'] > 0){ 
            $obs = $eventQuest['count_event_1'] + $eventQuest['count_event_2'];
            update('quest_events',array('count_event_1'=>0, 'count_event_2'=>0),'id='.(int)$eventQuest['id']);
            if($obs >= 900){
             $a = array(65,238);
             $it = randArr($a);
             if($it == 65) $pax = '<br>#065 Alakazam 1 - lvl';
              if($it == 238) $pax = '<br>#238 Smoochum - lvl';
             plus_pokes($_SESSION['id'],$it,1,1,1,'normal',false,20);
             plus_item(1000000,1);
             plus_item(1,63);
             plus_item(1,64);
             plus_item(1,65);
             plus_item(3,67);
             plus_item(1,17);
             plus_item(10,10);
             plus_item(5,11);
             plus_item(1,40);
             plus_item(1,68);             
             $tx = ' Вы получили: '.$pax.'<br>Монет: x1.000.000.
                                  <br>Большой мешочек с добавками: x1.
                                  <br>Огромный мешочек с добавками: x1.
                                  <br>Флейта: x1.
                                  <br>Сладкий кекс: x3.
                                  <br>Шоколадная конфета: x1.
                                  <br>Розовая конфета: x10.
                                  <br>Красная конфета: x5.
                                  <br>Громовой камень: x1.
                                  <br>Коробок смайлов: x1.';
            }elseif($obs >= 701 && $obs <= 899){
             plus_item(700000,1);
             plus_item(1,63);
             plus_item(2,64);
             plus_item(1,65);
             plus_item(3,67);
             plus_item(5,66);
             plus_item(10,10);
             plus_item(1,68);          
             $tx = ' Вы получили: <br>Монет: x700.000.
                                  <br>Большой мешочек с добавками: x1.
                                  <br>Огромный мешочек с добавками: x2.
                                  <br>Флейта: x1.
                                  <br>Сладкий кекс: x3.
                                  <br>Кекс: x5.
                                  <br>Розовая конфета: x10.
                                  <br>Коробок смайлов: x1.';
            }elseif($obs >= 401 && $obs <= 700){
             plus_item(500000,1);
             plus_item(1,61);
             plus_item(2,62);
             plus_item(1,65);
             plus_item(10,66);
             plus_item(10,10);
             plus_item(1,68);          
             $tx = ' Вы получили: <br>Монет: x500.000.
                                  <br>Небольшой мешочек с добавками: x1.
                                  <br>Мешочек с добавками: x2.
                                  <br>Флейта: x1.
                                  <br>Кекс: x10.
                                  <br>Розовая конфета: x10.
                                  <br>Коробок смайлов: x1.';
            }elseif($obs >= 201 && $obs <= 400){
             plus_item(300000,1);
             plus_item(1,61);
             plus_item(1,62);
             plus_item(1,65);
             plus_item(5,66);
             plus_item(1,68);       
             $tx = ' Вы получили: <br>Монет: x300.000.
                                  <br>Небольшой мешочек с добавками: x1.
                                  <br>Мешочек с добавками: x1.
                                  <br>Флейта: x1.
                                  <br>Кекс: x5.
                                  <br>Коробок смайлов: x1.';
            }elseif($obs >= 101 && $obs <= 200){
             plus_item(250000,1);
             plus_item(2,61);
             plus_item(2,62);
             plus_item(1,65);
             plus_item(1,68);     
             $tx = ' Вы получили: <br>Монет: x250.000.
                                  <br>Небольшой мешочек с добавками: x2.
                                  <br>Мешочек с добавками: x2.
                                  <br>Флейта: x1.
                                  <br>Коробок смайлов: x1.';
            }elseif($obs >= 21 && $obs <= 100){
             plus_item(200000,1);
             plus_item(2,61);
             plus_item(1,65);      
             $tx = ' Вы получили: <br>Монет: x200.000.
                                  <br>Небольшой мешочек с добавками: x2.
                                  <br>Флейта: x1.';
            }elseif($obs >= 1 && $obs <= 20){
             plus_item(50000,1);
             plus_item(1,61);     
             $tx = ' Вы получили: <br>Монет: x50.000.
                                  <br>Небольшой мешочек с добавками: x1.';
            }          
            $about   = "Рад видеть тебя здесь! Чтож, пришло время отблагодарить тебя за помощь. <br><b>".$tx."</b>";
            $pers    = '<a href="/game.php?go=char">Спасибо большое, удачно Вам повеселиться.<sup>[Уйти]</sup></a>';
          }else{
            $about   = "Рад видеть тебя здесь! Веселись!";
            $pers    = '<a href="/game.php?go=char">Я тоже рад, спасибо.<sup>[Уйти]</sup></a>';         
          }            
        break;
        default:
           die("<script>location.href='game.php?go=char';</script>");
      }    
    }else{
      die("<script>location.href='game.php?go=char';</script>");
    }    
  }
}else{
  die("<script>location.href='game.php?go=char';</script>");
}
?>