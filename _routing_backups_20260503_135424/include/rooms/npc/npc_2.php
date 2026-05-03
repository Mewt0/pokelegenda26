<?php
    $pers = obr_chis($_GET['quest_npc']);
    $do   = obr_chis($_GET['do']);
    $quest_isset_const = 1;
    $name = "Случайный прохожий";
    $href = '/game.php?go=char&quest_npc='.$pers.'&do=';
if($qx01){ 
  switch ($do) {
    case 1:
        $about = "Да-да? Что-то нужно?";
        $pers  = '<a href="'.$href.'2">Здравствуйте! Простите, но Вы не замечали тут покемонов?</a>';
      break;
    case 2:
        $about = "Покемонов? Да их тут много где пробегает. Но видел как трое покемонов вместе убежали на [Дорогу 1], если ты про них. 
                  Впервые вижу, что бы в дикой природе разные типы покемонов создавали стайки. А что случилось?";
         $pers  = '<a href="'.$href.'3"  target="_chat_two">Простите, но у меня очень мало времени, что бы все объяснять! Огромное спасибо за информацию! </a>';
      break;
    case 3:
        quest_update(1, 3, 0);
        die("<script>parent._location.location.href='game.php?go=char';</script>");
      break;
    default:
       die("<script>location.href='game.php?go=char';</script>");
  }
}

?>