<?php
    $pers = obr_chis($_GET['quest_npc']);
    $do   = obr_chis($_GET['do']);
    $quest_isset_const = 1;
    $name = "Кэрол";
    $href = '/game.php?go=char&quest_npc='.$pers.'&do=';
if(!$q4){ 
  switch ($do) {
    case 1:
        $about  = " *Вы заметили, как маленькая девочка очень громко плачет, и не могли пройти мимо этой милашки. Пытаясь понять, что она бормочет, Вы решили ее успокоить*";
        $pers   = '<a href="'.$href.'2">Эй, ты чего? Что случилось? Кто-то обидел или потерялась?</a>';
      break;
    case 2:
        $about = "<sup>[Cквозь слезы]</sup> Да! Это все Бидрил! Они стаей налетели и... и... <sup>[Cнова громко плачет]</sup>";
         $pers  = '<a href="'.$href.'3">Ну, пожалуйста, успокойся! Расскажи, что случилось?</a>';
      break;
    case 3:
        $about = "Они налетели на меня и украли игрушку! Она была моей самой любимой! Я ее больше не увижу! <sup>[Cнова плачет]</sup>";
         $pers  = '<a href="'.$href.'4"> Ну погоди! Давай я попробую ее найти? Скажи, куда Бидрил улетели после нападения?</a>';
      break;
    case 4:
        $about = "Они все еще тут! Просто улетели за те деревья! Верни, пожалуйста, мою игрушку... Она похожа на одного из покемонов!Но я не помню, как этот покемон называется...";
         $pers  = '<a href="'.$href.'5" target="_chat_two">Хорошо! Жди тут. А лучше - спрячься куда-нибудь! Тут всегда можно ожидать нападение диких покемонов. Я скоро вернусь!</a>';
      break;
    case 5:
         if(!$q4) insert('quest',array('quest_id'=>4, 'user_id'=>$_SESSION['id'], 'process'=>10));
        die("<script>parent._location.location.href='game.php?go=char';</script>");
      break;
    default:
       die("<script>location.href='game.php?go=char';</script>");
  }
}
if($q4 && $qx04){ 
  switch ($do) {
    case 1:
        $about  = "Ну что? Где моя игрушка? Ты принес ее?";
        $pers   = '<a href="'.$href.'2">Да! Вот она! Держи и не плачь больше.</a>';
        $pers  .= '<a href="/game.php?go=char">Прости, но пока что я ее не нашел...</a>';
      break;
    case 2:
        if(provitems(4,1)){
           minus_item(1,4);
           plus_item(10000,1);
           plus_item(5,3);
           quest_update(4, 11, 1);
           questRangUp(3);
            $about = "Ура! Ты вернул(а) ее мне! Спасибо! Удачи тебе! ";
            $pers  = '<a href="/game.php?go=char">Будь осторожна!</a>';
        }else{
          $about = "Игрушка? Ее нет? <sup>[Плачет]</sup>";
          $pers  = '<a href="/game.php?go=char">Я обязательно ее найду!</a>';        
        }
      break;
    default:
       die("<script>location.href='game.php?go=char';</script>");
  }
}
?>