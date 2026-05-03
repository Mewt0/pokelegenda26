<?php
    $pers = obr_chis($_GET['npc']);
    $do   = obr_chis($_GET['do']);
    $quest_isset_const = 1;
    $name = "Секретарь";
    $href = '/game.php?go=char&npc='.$pers.'&do=';
if(!empty($do)){
  switch ($do) {                  
    case 1:
        $about   = "Добрый день. Если у Вас возникли вопросы, я постараюсь ответить на них.";
        $pers    = '<a href="'.$href.'2">Здравствуйте! Могу ли я узнать свою репутацию?</a>';
        $pers   .= '<a href="'.$href.'4">Здравствуйте! Я хочу зарегистировать свой клан на захват.</a>';
        $pers   .= '<a href="'.$href.'5">Здравствуйте! Я бы хотел(-а) попробовать получить должность в игре.</a>';
        $pers   .= '<a href="/game.php?go=char">Здравствуйте! Я просто проходил(-а) мимо, у меня нет вопросов.<sup>[Уйти]</sup></a>';
    break;
    case 2:
        $about  = "Конечно, но это делается за определенную плату, в: 10.000 монет.";
         $pers   = '<a href="'.$href.'3">Да, конечно, я согласен(-на).</a>';
         $pers  .= '<a href="'.$href.'1">У меня другой вопрос.<sup>[Вернуться]</sup></a>';
         $pers  .= '<a href="/game.php?go=char">Простите, но это дорого.<sup>[Уйти]</sup></a>';
    break;
    case 3:
      if(provitems(1,10000)){
        minus_item(10000,1);
        nalog_clanz('5000');
        $plAdm = first('SELECT * FROM users WHERE id=%d',$_SESSION['id']);  
          $rangPve = $plAdm['rang_a'];
          $rangPvp = $plAdm['rang_b'];
          $rangQst = $plAdm['rang_c'];
          if($rangPve < 0) $rangPve .= ' (Отрицателен)';
          if($rangPvp < 0) $rangPvp .= ' (Отрицателен)';
          if($rangQst < 0) $rangQst .= ' (Отрицателен)';
          $about   = "<b>
                        Ваш ранг PVE сражений составляет: <span style='color:brown;'>".$rangPve."</span> очков.<br>
                        Ранг PVP сражений составляет: <span style='color:brown;'>".$rangPvp."</span> очков.<br>
                        Количество квестовых очков составляет: <span style='color:brown;'>".$rangQst."</span> шт.<br>
                      </b>
                      ";
          $pers   = '<a href="'.$href.'1">У меня другой вопрос.<sup>[Вернуться]</sup></a>';
          $pers  .= '<a href="/game.php?go=char"">Спасибо за помощь, я пошел.<sup>[Уйти]</sup></a>';
      }else{
          $about   = "Извените, но у Вас не достаточно монет.";
          $pers    = '<a href="'.$href.'1">У меня другой вопрос.<sup>[Вернуться]</sup></a>';
          $pers   .= '<a href="/game.php?go=char"">Простите за беспокойство, до свидания.<sup>[Уйти]</sup></a>';      
      }         
    break;
    case 4:
        $about  = "Извините, но на данный момент захваты не планируются.";
        $pers   = '<a href="'.$href.'1">У меня другой вопрос.<sup>[Вернуться]</sup></a>';
        $pers  .= '<a href="/game.php?go=char">Простите за беспокойство, до свидания.<sup>[Уйти]</sup></a>';
    break;
    case 5:
        $about  = "Извините, но на данный момент должностные лица в игру не набираются. Как только будет новый набор на должности мы сообщим Вам об этом.";
        $pers   = '<a href="'.$href.'1">У меня другой вопрос.<sup>[Вернуться]</sup></a>';
        $pers  .= '<a href="/game.php?go=char">Простите за беспокойство, до свидания.<sup>[Уйти]</sup></a>';
    break;
    default:
       die("<script>location.href='game.php?go=char';</script>");
 }
}else{
  die("<script>location.href='game.php?go=char';</script>");
}
?>