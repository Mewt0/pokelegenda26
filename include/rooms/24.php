<?php  
$y = date('Y');
$m = date('m'); 
$d = date('d');
  if(!empty($_GET['do'])){
    $xt = false;
    $tim = date('Y-m-d H:i:s');
    $a = first('SELECT * FROM users_locvoz WHERE userid=%d AND tip="tiket"',$_SESSION['id']);
    if(!empty($a)){
      if($a['dop'] <= $tim){
        update('users',array('buildmy'=>$a['locid']),'id='.(int)$_SESSION['id']);
        delete('users_locvoz','userid='.(int)$_SESSION['id'].' AND tip="tiket"');
        echo "<script>parent._location.location='game.php?go=char';</script>";      
      }else{
        echo "<script>parent.mess_error('Вы хотите выпрыгнуть прямо с идущего теплохода? Время вашего прибытия: ".$a['dop'].".<br>Может быть стоит дождаться?','block');</script>";
      }      
    }else{
        echo "<script>parent.mess_error('Походу, Вы тут застряли. <br> Возникла ошибка, обратитесь к Администратору. <br> А пока что помойте полы на палубе :D!','block');</script>";
    }
  }
    $name = 'Теплоход';
    $about = 'Огромный, белоснежный теплоход. 
              Он прекрасен. Множество палуб. И на каждой из них различные удобства для морского путешествия. 
              Бары, кафе, бассейны, тренерские площадки и каюты. 
              Этот теплоход обычно использовался одним из миллонеров Джотто для различных круизов.';
    $pers = '...';
    $move = '<a href="/game.php?go=char&do=1" target="_chat_two">Выход</a>';
?>
