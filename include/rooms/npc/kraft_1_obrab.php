<?php
if($items == 1){
  if (provitems(39,1) && provitems(24,5) && provitems(29,3) && provitems(34,1)){
      minus_item(1,39);
      minus_item(5,24);
      minus_item(3,29);
      minus_item(1,34);      
      $s = rand(0,10);
      if($s > 5){
        plus_item(1,40);
        $_SESSION['mess_kraft'] = "Вы удачно изготовили: <img src='img/items/40.png' width='24' alt='Громовой камень' title='Громовой камень'> Громовой камень.";
        writeLoges('Юзер: '.$_SESSION['login'].' удачно изготовил: Громовой камень, в колличестве: 1 шт.','df','kraft_nps');
      }else{
        $_SESSION['mess_kraft'] = "Произошла неудача при изготовлении: <img src='img/items/40.png' width='24'> Громовой камень.";
      }      
  }else{
      $_SESSION['mess_kraft'] = "У Вас недостаточно ресурсов для изготовления: <img src='img/items/40.png' width='24'> Громовой камень."; 
  }
}elseif($items == 2){
  if (provitems(39,1) && provitems(25,5) && provitems(30,3) && provitems(35,1)){
      minus_item(1,39);
      minus_item(5,25);
      minus_item(3,30);
      minus_item(1,35);      
      $s = rand(0,10);
      if($s > 5){
        plus_item(1,41);
        $_SESSION['mess_kraft'] = "Вы удачно изготовили: <img src='img/items/41.png' width='24' alt='Огненный камень' title='Огненный камень'> Огненный камень.";
        writeLoges('Юзер: '.$_SESSION['login'].' удачно изготовил: Огненный камень, в колличестве: 1 шт.','df','kraft_nps');
      }else{
        $_SESSION['mess_kraft'] = "Произошла неудача при изготовлении: <img src='img/items/41.png' width='24'> Огненный камень.";
      }      
  }else{
      $_SESSION['mess_kraft'] = "У Вас недостаточно ресурсов для изготовления: <img src='img/items/41.png' width='24'> Огненный камень."; 
  }
}elseif($items == 3){
  if (provitems(39,1) && provitems(26,5) && provitems(31,3) && provitems(36,1)){
      minus_item(1,39);
      minus_item(5,26);
      minus_item(3,31);
      minus_item(1,36);      
      $s = rand(0,10);
      if($s > 5){
        plus_item(1,42);
        $_SESSION['mess_kraft'] = "Вы удачно изготовили: <img src='img/items/42.png' width='24' alt='Водный камень' title='Водный камень'> Водный камень.";
        writeLoges('Юзер: '.$_SESSION['login'].' удачно изготовил: Водный камень, в колличестве: 1 шт.','df','kraft_nps');
      }else{
        $_SESSION['mess_kraft'] = "Произошла неудача при изготовлении: <img src='img/items/42.png' width='24'> Водный камень.";
      }      
  }else{
      $_SESSION['mess_kraft'] = "У Вас недостаточно ресурсов для изготовления: <img src='img/items/42.png' width='24'> Водный камень."; 
  }
}elseif($items == 4){
  if (provitems(39,1) && provitems(27,5) && provitems(32,3) && provitems(37,1)){
      minus_item(1,39);
      minus_item(5,27);
      minus_item(3,32);
      minus_item(1,37);      
      $s = rand(0,10);
      if($s > 5){
        plus_item(1,43);
        $_SESSION['mess_kraft'] = "Вы удачно изготовили: <img src='img/items/43.png' width='24' alt='Лиственный камень' title='Лиственный камень'> Лиственный камень.";
        writeLoges('Юзер: '.$_SESSION['login'].' удачно изготовил: Лиственный камень, в колличестве: 1 шт.','df','kraft_nps');
      }else{
        $_SESSION['mess_kraft'] = "Произошла неудача при изготовлении: <img src='img/items/43.png' width='24'> Лиственный камень.";
      }      
  }else{
      $_SESSION['mess_kraft'] = "У Вас недостаточно ресурсов для изготовления: <img src='img/items/43.png' width='24'> Лиственный камень."; 
  }
}elseif($items == 5){
  if (provitems(39,1) && provitems(28,5) && provitems(33,3) && provitems(38,1)){
      minus_item(1,39);
      minus_item(5,28);
      minus_item(3,33);
      minus_item(1,38);      
      $s = rand(0,10);
      if($s > 5){
        plus_item(1,44);
        $_SESSION['mess_kraft'] = "Вы удачно изготовили: <img src='img/items/44.png' width='24' alt='Лунный камень' title='Лунный камень'> Лунный камень.";
        writeLoges('Юзер: '.$_SESSION['login'].' удачно изготовил: Лунный камень, в колличестве: 1 шт.','df','kraft_nps');
      }else{
        $_SESSION['mess_kraft'] = "Произошла неудача при изготовлении: <img src='img/items/44.png' width='24'> Лунный камень.";
      }      
  }else{
      $_SESSION['mess_kraft'] = "У Вас недостаточно ресурсов для изготовления: <img src='img/items/44.png' width='24'> Лунный камень."; 
  }
}else{
 $_SESSION['mess_kraft'] = "Ошибка!";
 die ("<script>window.location.href='game.php?go=char&quest_npc=3&do=3';</script>");
}
?>