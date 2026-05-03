<?php
    $pers = obr_chis($_GET['quest_npc']);
    $do   = obr_chis($_GET['do']);
    $quest_isset_const = 1;
    $name = "Циркач Стив";
    $href = '/game.php?go=char&quest_npc='.$pers.'&do=';
if(!$q3){ 
  switch ($do) {
    case 1:
        $about  = "Здраствуй,тренер покемонов!";
        $pers   = '<a href="'.$href.'2">Здраствуйте!... Судя по вашему яркому виду одежды - вы работаете в цирке?</a>';
      break;
    case 2:
        $about = "Угадал! И я стою здесь не просто так... 
                  Мы открыли еще один цирк в регионе, но, к сожалению, у нас мало цирковых покемонов. 
                  Я стою здесь... Но у меня все никак не получается их поймать!";
         $pers  = '<a href="'.$href.'3">Как я понимаю, тебе нужна помощь?</a>';
      break;
    case 3:
        $about = "Снова угадал! Я был бы тебе очень признателен. 
                  Нужно поймать 5 разных покемонов! Но при этом есть одно условие. 
                  Они должны быть все 35 уровня. Смотри за этим!";
         $pers  = '<a href="'.$href.'4" target="_chat_two"> Ну что же, время у меня есть, попробую помочь тебе! Скоро вернусь!</a>';
      break;
    case 4:
         if(!$q3) insert('quest',array('quest_id'=>3, 'user_id'=>$_SESSION['id'], 'process'=>2));
        die("<script>parent._location.location.href='game.php?go=char';</script>");
      break;
    default:
       die("<script>location.href='game.php?go=char';</script>");
  }
}
if($q3 && $qx03){ 
  switch ($do) {
    case 1:
        $about  = "О! Ты уже вернулся? Ну как дела с поимкой?";
        $pers   = '<a href="'.$href.'2">Вот покеболлы с покемонами внутри. </a>';
        $pers  .= '<a href="/game.php?go=char">Пока что я не поймал нужное количество.</a>';
      break;
    case 2:
        if(qCountPoke(6)){
          $a = qPokeBase(12,35);
          $b = qPokeBase(24,35);
          $c = qPokeBase(49,35);
          $d = qPokeBase(15,35);
          $e = qPokeBase(57,35);
          $x = array(1=>'<br>#012 Butterfree 35-lvl',2=>'<br>#024 Arbok 35-lvl',3=>'<br>#049 Venomoth 35-lvl',4=>'<br>#015 Beedrill 35-lvl',5=>'<br>#057 Primeape 35-lvl');
          if($a && $b && $c && $d && $e){
           qDelPoke($a); qDelPoke($b); qDelPoke($c); qDelPoke($d); qDelPoke($e);
           plus_item(50000,1);
           plus_item(20,3);
           quest_update(3, 10, 1);
           questRangUp(2);
            $about = "Огромное тебе спасибо! Как ты выручил! Слушай, если решишь зайти в цирк, то тебе билет будет бесплатный! Я это тебе обещаю!";
            $pers  = '<a href="/game.php?go=char">Хорошо! До встречи!</a>';
          }else{
            $s = false;
            if(!$a) $s .= $x[1]; if(!$b) $s .= $x[2]; if(!$c) $s .= $x[3]; if(!$d) $s .= $x[4]; if(!$e) $s .= $x[5];
            $about  = "Зачем ты меня обманываешь? У тебя не хватает: ".$s;
            $pers  = '<a href="/game.php?go=char">Хорошо...</a>';  
          }
        }else{
          $about = "С собой у тебя должно быть не меньше 6-ти покемонов!";
          $pers  = '<a href="/game.php?go=char">Хорошо, простите.</a>';        
        }
      break;
    default:
       die("<script>location.href='game.php?go=char';</script>");
  }
}
?>