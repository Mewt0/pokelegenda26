<?php
    $pers = obr_chis($_GET['quest_npc']);
    $do   = obr_chis($_GET['do']);
    $quest_isset_const = 1;
    $name = "Исследователь";
    $href = '/game.php?go=char&quest_npc='.$pers.'&do=';
    $qInfPr = qInfo(6,'process');
if(!$q5){ 
  switch ($do) {
    case 1:
        $about   = "Привет, тренер! Есть минутка? ";
        $pers    = '<a href="'.$href.'2">Да, в чём дело?</a>';
        $pers   .= '<a href="/game.php?go=char">Нет, сейчас я слишком занят.</a>';
      break;
    case 2:
        $about  = "Дело в том, что лаборатория получила грант на исследование озера Вертании. 
                   Есть основания полагать, что озеро сообщается с другими озёрами региона системой подземных рек. 
                   Спектральный анализ химического состава воды показал наличие свободных радикалов катионов... ";
         $pers   = '<a href="'.$href.'3">Я тренер, а не исследователь. Что требуется от меня?</a>';
         $pers  .= '<a href="/game.php?go=char">Ох, тоска смертная, пойду я лучше отсюда...</a>';
      break;
    case 3:
        $about   = "Ах да, мои извинения. 
                    От тебя требуется поместить 10 маячков на хорси, которые водятся в озере. 
                    Но есть ещё одно условие: все хорси должны быть 40 уровня. 
                    Для этого достаточно просто победить покемонов в бою, а затем прикрепить маячёк к ним на спину. 
                    Само собой, за выполнение задания полагается награда. 
                    Ну как, берёшься?";
         $pers   = '<a href="'.$href.'4" target="_chat_two">Конечно! Думаю, награда того стоит.</a>';
         $pers  .= '<a href="/game.php?go=char">Хмм... пожалуй, это задание не по мне.</a>';
      break;
    case 4:
         if(!$q5) { 
          insert('quest',array('quest_id'=>5, 'user_id'=>$_SESSION['id'], 'process'=>10));
          insQuePoke(5,116,10);
         }
        die("<script>parent._location.location.href='game.php?go=char';</script>");
      break;
    default:
       die("<script>location.href='game.php?go=char';</script>");
  }
}
if($q5 && $qx05){ 
  switch ($do) {
    case 1:
        $about  = "Ну как там хорси? Не терпится получить первые данные и приступить к их анализу.";
        $pers   = '<a href="'.$href.'2">Задание выполнено.</a>';
        $pers  .= '<a href="/game.php?go=char">Я пока не успел выполнить задание.</a>';
      break;
    case 2:
        $qp = questPokemon(5,116);
        if($qp){
          deletqPokemon($qp);
          plus_item(25000,1);
          quest_update(5, 20, 1);
          questRangUp(2);
            $about = "Замечательно! Возможно, результаты исследований окажутся весьма неожиданными. Вот твоя награда! <sup>(25.000 Монет)</sup>";
            $pers  = '<a href="/game.php?go=char">Ого, щедро. Большое спасибо!</a>';
        }else{
          $about = "Хмм... я получаю сигнал не от всех десяти датчиков. Похоже, твоё задание всё ещё не выполнено.";
          $pers  = '<a href="/game.php?go=char">Хорошо, хорошо, отправляюсь обратно на озеро!</a>';        
        }
      break;
    default:
       die("<script>location.href='game.php?go=char';</script>");
  }
}
if($q5 && $qx05 == false && ($qInfPr == 1 || $qInfPr == 0)){
    $timeQ = qInfo(6,'time');
  if(!empty($timeQ) && $timeQ > time()){                                      
    switch($do){
      case 1:
          $about  = "Увы, но сегодня для тебя нет заданий. ".timersOtshet($timeQ,"Приходи через: ", "...");
          $pers   = '<a href="/game.php?go=char">Хорошо, до свидания.<sup>[Уйти]</sup></a>';
        break;
      default:
         die("<script>location.href='game.php?go=char';</script>");
    }
  }else{
  switch($do){
      case 1:
          $about  = "О, привет, ты как раз вовремя. Не занят? А то у меня для тебя появилось ещё одно задание. ";
          $pers   = '<a href="'.$href.'2">Я весь во внимание.</a>';
          $pers  .= '<a href="/game.php?go=char">К сожалению, мне сейчас некогда.</a>';
        break;
      case 2:
          $about  = "Прекрасно! На этот раз мне понадобится помощь в ловле покемонов. 
                      Как тебе, скорее всего, уже известно, основная область исследований нашей лаборатории – не озёра и подземные реки, а эволюция покемонов. 
                      Не так давно нами была разработана методика исследований, основанная на расчётах констант квантово-полевых систем… 
                      Ах, опять заговорился. 
                      Короче говоря, от тебя требуется ежедневно приносить в лабораторию по 5 метаподов 9 уровня, которые являются идеальными объектами для наших исследований. 
                      Щедрая награда гарантирована. <br>Берёшься?";
          $pers   = '<a href="'.$href.'3">Награда? Ммм… я в деле.</a>';
          $pers  .= '<a href="/game.php?go=char">Нет, на этот раз не возьмусь.</a>';
        break;
      case 3:
          $about  = "Отлично! Возвращайся с пятью метаподами 9 уровня с собой.";
          $pers   = '<a href="/game.php?go=char">Хорошо.<sup>[Уйти]</sup></a>';
          if(empty($q6)) insert('quest',array('quest_id'=>6, 'user_id'=>$_SESSION['id'], 'process'=>10));
            else update('quest',array('process'=>10),'quest_id= 6 AND user_id='.(int)$_SESSION['id']); 
        break;
      default:
         die("<script>location.href='game.php?go=char';</script>");
    }
  }  
}
if($q5 && $qx05 == false && $qInfPr == 10 && !empty($q6)){
  switch ($do) {
    case 1:
        $about  = "Ну как? Наловил метаподов 9 уровня?";
        $pers   = '<a href="'.$href.'2">Да, вот они.</a>';
        $pers  .= '<a href="/game.php?go=char">Пока ещё нет.</a>';
      break;
    case 2:
        if(qCountPoke(5,'AND basenum=11 AND lvl=9')){
          if(qCountPoke(6)){ 
            if(delete('pok_user','basenum=11 AND lvl=9 AND active=1 AND users='.(int)$_SESSION['id'].' LIMIT 5')){
              plus_item(10000,1);
              plus_item(10,3);
              $z = time()+(60*60*24);
              update('quest',array('time'=>$z, 'process'=>1),'quest_id= 6 AND user_id='.(int)$_SESSION['id']);
              $about  = "Отлично! Большое тебе спасибо. Вот обещанная награда – 10000 кредитов и 10 покеболов. Приходи завтра! ";
              $pers   = '<a href="/game.php?go=char">Спасибо.<sup>[Уйти]</sup></a>';  
            }else{ 
              $about  = "Ошибка системы...";
              $pers   = '<a href="/game.php?go=char">...<sup>[Уйти]</sup></a>'; 
            }
          }else{
            $about  = "Я не могу забрать их. У тебя в команде должно быть более 5-ти покеомнов.";
            $pers   = '<a href="/game.php?go=char">Хорошо.<sup>[Уйти]</sup></a>';
          }
        }else{
          $about  = 'Вижу, с математикой у тебя похуже, чем у меня. Пятерых метаподов 9 уровня я у тебя не нашёл. Возвращайся, как наловишь больше!';
          $pers   = '<a href="/game.php?go=char">Хорошо.<sup>[Уйти]</sup></a>';
        }
      break;
    default:
       die("<script>location.href='game.php?go=char';</script>");
  }  
}
?>