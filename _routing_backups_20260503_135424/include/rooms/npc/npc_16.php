<?php
    $pers = obr_chis($_GET['npc']);
    $do   = obr_chis($_GET['do']);
    $quest_isset_const = 1;
    $name = "Пророк";
    $href = '/game.php?go=char&npc='.$pers.'&do=';
    $qInfPr = qInfo(666,'process');
if(!$qInfPr){ 
  switch ($do) {
    case 1:
        $about   = "Здраствуйте тренер! Должно быть ты заметил,что в этот праздник слишком тихо? ";
        $pers    = '<a href="'.$href.'2">Да! Я бы хотел узнать в чем дело-то?</a>';
      break;
    case 2:
        $about  = "Я заметил шестерых демонов которые проникли в наш мир, вселились в покемонов и пугают местных граждан!.. ";
         $pers   = '<a href="'.$href.'3">Можно ли изгнать демонов из нашего мира?</a>';
      break;
    case 3:
        $about   = "Да, стоит только победить одержимого покемона и демон его покинет!";
         $pers   = '<a href="'.$href.'4">Отлично, можете сообщить где они?</a>';
      break;
    case 4:
	    $about   = "Первого демона видели возле Горного перевала ,отправляйся туда! Но помни, если ты потерпишь поражение, демон сразиться с тобой лишь через 30 минут после пройгрыша.";
         $pers   = '<a href="game.php?go=char">Спасибо</a>';
          insert('quest',array('quest_id'=>666, 'user_id'=>$_SESSION['id'], 'process'=>2));
      break;
    default:
       die("<script>location.href='game.php?go=char';</script>");
  }
}elseif($qInfPr == 2){
  switch ($do) {
    case 1:
        $about   = "Первый демон всё ещё возле Горного перевала... ";
        $pers    = '<a href="game.php?go=char">Я знаю.</a>';
      break;
    default:
       die("<script>location.href='game.php?go=char';</script>");
  }
}elseif($qInfPr == 3){
  switch ($do) {
    case 1:
        $about   = "Молодец ,первый демон повержен. Второй демон замечен был в пещере";
        $pers    = '<a href="game.php?go=char">Хорошо.</a>';
		update('quest',array('process'=>4),'user_id='.(int)$_SESSION['id'],'quest_id=666');
      break;
    default:
       die("<script>location.href='game.php?go=char';</script>");
  }
}elseif($qInfPr == 4){
  switch ($do) {
    case 1:
        $about   = "Второй демон всё ещё находится в пещере...";
        $pers    = '<a href="game.php?go=char">Я знаю.</a>';
      break;
    default:
       die("<script>location.href='game.php?go=char';</script>");
  }
}elseif($qInfPr == 5){
  switch ($do) {
    case 1:
        $about   = "Молодец это уже второй демон. Третий демон бродит в в окресностях Скал.";
        $pers    = '<a href="game.php?go=char">Хорошо.</a>';
		update('quest',array('process'=>6),'user_id='.(int)$_SESSION['id'],'quest_id=666');
      break;
    default:
       die("<script>location.href='game.php?go=char';</script>");
  }
}elseif($qInfPr == 6){
  switch ($do) {
    case 1:
        $about   = "Третий демон всё ещё на свободе...";
        $pers    = '<a href="game.php?go=char">Я знаю.</a>';
      break;
    default:
       die("<script>location.href='game.php?go=char';</script>");
  }
}elseif($qInfPr == 7){
  switch ($do) {
    case 1:
        $about   = "Отлично ,осталось всего три демона. Четвертый демон засел в темном лесу!";
        $pers    = '<a href="game.php?go=char">Хорошо.</a>';
		update('quest',array('process'=>8),'user_id='.(int)$_SESSION['id'],'quest_id=666');
      break;
    default:
       die("<script>location.href='game.php?go=char';</script>");
  }
}elseif($qInfPr == 8){
  switch ($do) {
    case 1:
        $about   = "Четвёртый демон всё ещё на свободе...";
        $pers    = '<a href="game.php?go=char">Я знаю.</a>';
      break;
    default:
       die("<script>location.href='game.php?go=char';</script>");
  }
}elseif($qInfPr == 9){
  switch ($do) {
    case 1:
        $about   = "Хорошая работа, ты станеш великим тренером! Пятый демон обустроился на стадионе электрического типа!";
        $pers    = '<a href="game.php?go=char">Хорошо.</a>';
		update('quest',array('process'=>10),'user_id='.(int)$_SESSION['id'],'quest_id=666');
      break;
    default:
       die("<script>location.href='game.php?go=char';</script>");
  }
}elseif($qInfPr == 10){
  switch ($do) {
    case 1:
        $about   = "Пятый демон всё ещё на свободе...";
        $pers    = '<a href="game.php?go=char">Я знаю.</a>';
      break;
    default:
       die("<script>location.href='game.php?go=char';</script>");
  }
}elseif($qInfPr == 11){
  switch ($do) {
    case 1:
        $about   = "Остался всего один, чего ты ждешь? Он в Здании Администрации! Одолей его и праздник востановлен!";
        $pers    = '<a href="game.php?go=char">Хорошо.</a>';
		update('quest',array('process'=>12),'user_id='.(int)$_SESSION['id'],'quest_id=666');
      break;
    default:
       die("<script>location.href='game.php?go=char';</script>");
  }
}elseif($qInfPr == 12){
  switch ($do) {
    case 1:
        $about   = "Я верю в тебя ты сможешь!";
        $pers    = '<a href="game.php?go=char">Да!</a>';
      break;
    default:
       die("<script>location.href='game.php?go=char';</script>");
  }
}elseif($qInfPr == 13){
  switch ($do) {
    case 1:
        $about   = "Вот и все! Можешь гулять спокойно, демоны вышли из несчастных покемонов! Наслаждайся праздником!";
        $pers    = '<a href="game.php?go=char">Ура.</a>';
      break;
    default:
       die("<script>location.href='game.php?go=char';</script>");
  }
}


?>