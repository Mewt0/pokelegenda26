<?php
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
        echo "<script>parent.mess_error('Вы хотите выпрыгнуть из дирижабля? <br>Время вашего прибытия: ".$a['dop'].".<br>Может быть стоит дождаться? Дирижабль летит очень высоко, вы можете разбиться!','block');</script>";
      }      
    }else{
        echo "<script>parent.mess_error('Походу, Вы тут застряли. <br> Возникла ошибка, обратитесь к Администратору. <br> А пока что помойте полы в слоне :D!','block');</script>";
    }
  }
    $name = 'Дирижабль';
    $about = 'Вы плывете на большом дирижабле, перед вами расстилается красивый вид пятизвездочных апартаментов. Успокаивающие  тона, множество кресел, буфет с едой высшего качества. Капитан дирижабля приветствует пассажиров и поворачивает руль корабля. После чего, дирижабль начинает плавно передвигаться по небу...';  
    $pers = '...';
    $move = '<a href="/game.php?go=char&do=1" target="_chat_two">Выход</a>';

?>
