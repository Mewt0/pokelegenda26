<?php
if($_POST && isset($_GET['post'])){
if(!empty($_POST['to']) && !empty($_POST['itemlotid']) && $_POST['to'] == 1){ 
    $itemoff = obr_chis($_POST['itemlotid']);
    if (empty($itemoff) || $itemoff <= 0) die("<script>location.href='game.php?go=rinok';</script>"); 
    $lot = first('SELECT id_lot,count,tip_item,user_id FROM auction_items WHERE id_lot=%d AND user_id=%d AND egg=0 ',$itemoff,$_SESSION['id']);
    if(empty($lot)) die("<script> alert('Такого лота несуществует, возможно он был куплен!'); location.href='game.php?go=rinok';</script>"); 
    $count    = $lot['count'];
    $item_tip = $lot['tip_item'];
    if($_SESSION['id'] != $lot['user_id']) die("<script>location.href='game.php?go=rinok';</script>");
    plus_item($count,$item_tip);
    delete('auction_items','id_lot='.(int)$lot['id_lot']);
    die("<script>alert('Вы удачно забрали предмет!');location.href='game.php?go=rinok';</script>");
}
elseif(!empty($_POST['itemlotid']) && !empty($_POST['toe']) && $_POST['toe'] == 1){
    $itemoff = obr_chis($_POST['itemlotid']);
    if (empty($itemoff) || $itemoff <= 0) die("<script>location.href='game.php?go=rinok';</script>");
    $lot = first('SELECT id_lot,item_id,user_id FROM auction_items WHERE id_lot=%d AND user_id=%d AND egg=1',$itemoff,$_SESSION['id']);
    if(empty($lot)) die("<script> alert('Такого лота несуществует, возможно он был куплен!'); location.href='game.php?go=rinok';</script>");
    if($_SESSION['id'] != $lot['user_id']) die("<script>location.href='game.php?go=rinok';</script>");
    update('eggs',array('users_egg'=>$lot['user_id']),'id_egg='.(int)$lot['item_id']);
    delete('auction_items','id_lot='.(int)$lot['id_lot']);
    die("<script>alert('Вы удачно забрали яйцо!');location.href='game.php?go=rinok';</script>");
}
elseif(!empty($_POST['itemlotid']) && !empty($_POST['toe']) && $_POST['toe'] == 2){
    $item = obr_chis($_POST['itemlotid']);
    if(empty($item) || $item <= 0) die("<script>location.href='game.php?go=rinok';</script>");
    $lot = first('SELECT * FROM auction_items WHERE id_lot=%d AND egg=1 AND regions=%d',$item,$my_Build);
    if(empty($lot)) die("<script>alert('Такого лота несуществует, возможно он был куплен!'); location.href='game.php?go=rinok';</script>");
    if($_SESSION['id'] == $lot['user_id']) die("<script>location.href='game.php?go=rinok';</script>");
    if($lot['user_id_to'] != "no" && $lot['user_id_to'] != $_SESSION['id']) die("<script>alert('Это яйцо забрать нельзя, оно продается не Вам!');location.href='game.php?go=rinok';</script>");
    if(!provitems(1,$lot['cena'])) die("<script>alert('У Вас недостаточно монет для покупки этого яйца!');location.href='game.php?go=rinok';</script>");
      // Забираем деньги у покупателя
       minus_item($lot['cena'],1);
      // Вычитаем налог от суммы
       $nalog = round($lot['cena']*0.05);
       $res_chen = $lot['cena'] - $nalog;
       nalog_clanz($nalog);
      // Начисляем деньги продовцу
       plus_item($res_chen,1,$lot['user_id']);
      // Изменяем параметры в базах
       update('eggs',array('users_egg'=>$_SESSION['id']),'id_egg='.(int)$lot['item_id']); 
       delete('auction_items','id_lot='.(int)$lot['id_lot']);
      // Логируем
         $x001 = first('SELECT base_id_egg FROM eggs WHERE id_egg=%d',$lot['item_id']);
         $arrEgg = array('pokup'=>$_SESSION['id'], 'prodav'=>$lot['user_id'], 'egg'=>$x001['base_id_egg'], 'eggid'=>$lot['item_id'], 'cena'=>$res_chen);
         logGames('egg',$arrEgg);
      // Сообщаем продовцу    
         $text_send = "Здравствуйте, Ваш товар под №".$item." (яйцо #".$x001['base_id_egg']."), было успешно продано пользователю: ".$_SESSION['login'].". Вы получили денги за Ваш товар в сумме: ".formatnum($res_chen)." монет, из них вычли налог на сумму: ".formatnum($nalog)." монет.";
         messSisyem($text_send,$lot['user_id'],'Торговые операции');
      // Заканчиваем скрипт
die("<script>alert('Вы удачно купили яйцо!'); location.href='game.php?go=rinok';</script>");
}
elseif(!empty($_POST['itemlotid']) && !empty($_POST['amount']) && !empty($_POST['to']) && $_POST['to'] == 2){
   $itemLot = obr_chis($_POST['itemlotid']);
   $am      = obr_chis($_POST['amount']);
   if(empty($itemLot) || $itemLot <= 0 || empty($am) || $am <= 0) die("<script>location.href='game.php?go=rinok';</script>");
   $lot = first('SELECT * FROM auction_items WHERE id_lot=%d AND egg=0 AND regions=%d',$itemLot,$my_Build);
   if(empty($lot)) die("<script> alert('Такого лота несуществует, возможно он был куплен, либо изъят с продажи!'); location.href='game.php?go=rinok';</script>");
   if($lot['user_id_to'] != "no" && $lot['user_id_to'] != $_SESSION['id']) die("<script>alert('Этот предмет забрать нельзя, он продается не Вам!');location.href='game.php?go=rinok';</script>");
   if($am > $lot['count']) die("<script> alert('Зачем обманывать систему?!'); location.href='game.php?go=rinok';</script>");
    $itemLot     = $lot['id_lot'];
    $cena        = $lot['cena'];
    $count       = $lot['count'];
    $item_tip    = $lot['tip_item'];
    $prodavec    = $lot['user_id'];
    $cenaRes     = ceil($cena/$count);
    $cenaPokupki = $am*$cenaRes;
    $countRes    = $count-$am;
    $cenaRes     = $countRes*$cenaRes;
  if($countRes < 0) die("<script> alert('Что-то не так, попробуйте снова.'); location.href='game.php?go=rinok';</script>");
  if($_SESSION['id'] == $prodavec) die("<script>alert('Зачем покупать предмет у самого себя? Свой предмет покупать нельзя!'); location.href='game.php?go=rinok';</script>");
  if(!provitems(1,$cenaPokupki)) die("<script>alert('У Вас недостаточно монет для покупки этого предмета!');location.href='game.php?go=rinok';</script>");
    // Забираем деньги у покупателя, начислем покупателю айтем.
     minus_item($cenaPokupki,1);
     plus_item($am,$item_tip);
    // Вычитаем налог от суммы
     $nalog = round($cenaPokupki*0.05);
     $res_chen = $cenaPokupki - $nalog;
     nalog_clanz($nalog);
    // Начисляем деньги продовцу
     plus_item($res_chen,1,$prodavec);
    // Изменяем параметры лота
     if($countRes != 0) update('auction_items',array('count'=>$countRes, 'cena'=>$cenaRes),'id_lot='.(int)$itemLot);
     if($countRes == 0) delete('auction_items','id_lot='.(int)$itemLot);
    //Log
      $arrItem = array('pokup'=>$_SESSION['id'], 'prodav'=>$prodavec, 'item'=>$item_tip, 'cena'=>$res_chen, 'cool'=>$am);
      logGames('item',$arrItem);
    // Сообщаем продовцу                          
      $cc = first('SELECT name FROM items WHERE id=%d',$item_tip);
      $textSend = "Здравствуйте, Ваш товар под №".$itemLot."(".$cc['name']."), был успешно продан пользователю: ".$_SESSION['login'].", в количестве: ".$am." шт. Вы получили денги за Ваш товар в сумме: ".formatnum($cenaPokupki)." монет, из них вычли налог на сумму: ".formatnum($nalog)." монет.";
      if($countRes != 0) $textSend .= " Товара(".$cc['name'].") на рынке осталось: ".$countRes." шт.";
      messSisyem($textSend,$prodavec,'Торговые операции');
    //Заканчиваем скрипт
die("<script>alert('Предмет удачно куплен. Спасибо за покупку!');location.href='game.php?go=rinok';</script>");          
}
elseif(!empty($_POST['cools']) && !empty($_POST['itemsid']) && !empty($_POST['cena']) && empty($_POST['toe']) && empty($_POST['eggs']) && empty($_POST['to'])){
  $cools = obr_chis($_POST['cools']);
  $items = obr_chis($_POST['itemsid']); 
  $cena  = obr_chis($_POST['cena']);
  if(empty($cools) || $cools <= 0 || empty($items) || $items <= 0 || empty($cena) || $cena <=0)  die("<script>location.href='game.php?go=rinok&do';</script>"); 
  if(!empty($_POST['user_to'])) $user_to = obr_txt($_POST['user_to']); else $user_to = false;
  if($user_to != false && !preg_match("|^[a-z_-]+$|i", $user_to))die("<script>alert('Недопустимые символы в строке пользователя.');location.href='game.php?go=rinok&do';</script>"); 
  if($user_to){
    $proverka_usera = first('SELECT login,id FROM users WHERE login="%s"',$user_to);
    if(!$proverka_usera) die("<script>alert('Такого пользователя: ".$user_to.", не существует.'); location.href='game.php?go=rinok&do';</script>");                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   
    $user_to_id = $proverka_usera['id'];
  }else{
    $user_to_id = "no";
  }
  $invent = first('SELECT item_id,user_id FROM items_users WHERE user_id=%d AND id=%d',$_SESSION['id'],$items);
  $tip_item = $invent['item_id'];
  $baseItem = first('SELECT torg FROM items WHERE id=%d AND id != 1',$tip_item);
  if(empty($baseItem) || empty($invent)) die("<script>location.href='game.php?go=rinok&do';</script>");
  if($baseItem['torg'] != 0)  die("<script>alert('Нельзя продать этот предмет.');location.href='game.php?go=rinok&do';</script>");
  if($_SESSION['id'] != $invent['user_id'])  die("<script>location.href='game.php?go=rinok&do';</script>");
  if(!provitems($tip_item,$cools))  die("<script>alert('У вас не хватает.');location.href='game.php?go=rinok&do';</script>");
  $cenaRes = round($cena*$cools);
  minus_item($cools,$tip_item);
  $my_time_rinok = time() + (60*60*24*1);
  insert('auction_items',array(
         'item_id'=>'0',
         'cena'=>$cenaRes,
         'count'=>$cools,
         'user_id'=>$_SESSION['id'],
         'created'=>date('Y-m-d'),
         'tip_item'=>$tip_item,
         'user_id_to'=>$user_to_id,
         'time_rinok'=>$my_time_rinok,
         'regions'=>$my_Build));
 die("<script>alert('Предмет удачно выставлен на продажу.'); location.href='game.php?go=rinok&do';</script>"); 
}
elseif(!empty($_POST['eggs']) AND !empty($_POST['cena']) AND !empty($_POST['eggid'])  AND empty($_POST['toe']) AND empty($_POST['to'])){
  $egg  = obr_chis($_POST['eggid']);
  $cena = obr_chis($_POST['cena']); 
  if(empty($egg) || empty($cena) || $egg <= 0 || $cena <= 0) die("<script>alert('Нет параметра.'); location.href='game.php?go=rinok&do';</script>");
  if(!empty($_POST['user_to'])) $user_to = $_POST['user_to']; else $user_to = false;
  if($user_to != false && !preg_match("|^[a-z_-]+$|i", $user_to))die("<script>alert('Недопустимые символы в строке пользователя.');location.href='game.php?go=rinok&do';</script>"); 
  if($user_to){
    $proverka_usera = first('SELECT login,id FROM users WHERE login="%s"',$user_to);
    if(!$proverka_usera) die("<script>alert('Такого пользователя: ".$user_to.", не существует.'); location.href='game.php?go=rinok&do';</script>");                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   
    $user_to_id = $proverka_usera['id'];
  }else{
    $user_to_id = "no";
  }
  $eggIsset = first('SELECT users_egg FROM eggs WHERE id_egg=%d AND users_egg=%d',$egg,$_SESSION['id']);
  if(empty($eggIsset)) die("<script>alert('Яйцо принадлежит не Вам, либо оно уже вылупилось!'); location.href='game.php?go=rinok&do';</script>");
  if($eggIsset['users_egg'] != $_SESSION['id']) die("<script>location.href='game.php?go=rinok&do';</script>");
  $my_time_rinok = time() + (60*60*24*2);
  insert('auction_items',array(
         'item_id'=>$egg,
         'cena'=>$cena,
         'user_id'=>$eggIsset['users_egg'],
         'created'=>date('Y-m-d'),
         'egg'=>1,
         'user_id_to'=>$user_to_id,
         'time_rinok'=>$my_time_rinok,
         'regions'=>$my_Build));
  update('eggs',array('users_egg'=>3),'users_egg='.(int)$_SESSION['id'].' AND id_egg='.(int)$egg);
  die("<script>alert('Яйцо удачно выставлено на продажу.');location.href='game.php?go=rinok&do';</script>");
}else{
  die("<script>alert('Нет параметра.'); location.href='game.php?go=rinok&do';</script>");
   
} 
die();
}
?>

   


       

   



