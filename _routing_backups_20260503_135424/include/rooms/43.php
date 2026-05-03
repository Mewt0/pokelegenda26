<?php
$eventQuest = first('SELECT * FROM quest_events WHERE quest=1 AND users=%d',$_SESSION['id']);
if(!empty($_GET['to']) && $eventQuest['etapes'] == 2 && $_SESSION['PRIZE_LEGEND']){
  $name = 'Праздничный зал';
  $about = '<b>';
  $pers = '...';
  $move = '<a href="/game.php?go=char">Уйти...</a>';
  if(!empty($_SESSION['PRIZE_LEGEND'][0])){
      plus_item(10,67);
     $about .= 'За торт Вы получили: Сладкий кекс х10.<br>';
     $_SESSION['PRIZE_LEGEND'][0] = false;
  }
  if(!empty($_SESSION['PRIZE_LEGEND'][1])){
    $xz = $_SESSION['PRIZE_LEGEND'][1];
    if($xz == 58){
      plus_item(5,66);
      plus_item(3,20);
      $about .= 'За небольшой подарок Вы получили: Кекс х5 + Свежий клевер х3.<br>';
    }elseif($xz == 59){
      plus_item(10,66);
      plus_item(5,20);
      plus_item(2,63);
      $about .= 'За подарок Вы получили: Кекс х10 + Свежий клевер х5 + Большой мешочек с добавками: x2.<br>';
    }elseif($xz == 60){
      plus_item(5,67);
      plus_item(5,20);
      plus_item(5,63);
      plus_item(3,64);
      $about .= 'За Большой подарок Вы получили: Сладкий кекс х5 + Свежий клевер х5 + Большой мешочек с добавками: x5 + Огромный мешочек с добавками: x3.<br>';
    }
   $about .= '</b>';
   update('quest_events',array('etapes'=>3),'id='.(int)$eventQuest['id']);
  }
 unset($_SESSION['PRIZE_LEGEND']);
}else{
  if(!empty($_GET['quest_npc']) && !empty($_GET['do'])){
     if($_GET['quest_npc'] == 1  || $_GET['quest_npc'] == 2 || $_GET['quest_npc'] == 3 || $_GET['quest_npc'] == 4) include ("npc/npc_eventspers.php");
      else die("<script>location.href='game.php?go=char';</script>"); 
  }else{  
    $name = 'Праздничный зал';
    $about = 'Не большой зал с приятным оттенком стен. 
              Повсюду расставлены столы с угощениями для гостей. 
              В центре этого зала можно увидеть огромного Саикуна, который сделан из множества воздушных шариков. 
              Именно здесь проводят различные праздники и торжества.';  
    $pers = '<a href="/game.php?go=char&quest_npc=1&do=1"><span style=\'font-weight:bold;color:brown;\'>Makasimka</span></a>
           | <a href="/game.php?go=char&quest_npc=2&do=1"><span style=\'font-weight:bold;color:brown;\'>LEGENDA</span></a>
           | <a href="/game.php?go=char&quest_npc=3&do=1"><span style=\'font-weight:bold;color:brown;\'>MiladyMio</span></a>';
    if($eventQuest['count_event_1'] > 0 || $eventQuest['count_event_2'] > 0) $pers .= ' | <a href="/game.php?go=char&quest_npc=4&do=1">Роберт</a>  ';
    if(!empty($_SESSION['PRIZE_LEGEND'])) $pers .= ' | <a href="/game.php?go=char&to=true">*Праздничный сюрприз*</a>'; 
    $move = '<a href="/game.php?go=charWork&loc=1" target="_chat_two">Алабастия</a>';
    $img_r = '<img src="img/room/043.png" width="290" height="150">';
  }
}
?>
