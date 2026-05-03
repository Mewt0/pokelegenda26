<?php
if($_POST && isset($_GET['post'])){
if(!empty($_POST['to']) && !empty($_POST['itemlotid']) && $_POST['to'] == 1){ 
    $itemoff = obr_chis($_POST['itemlotid']);
    if (empty($itemoff) || $itemoff <= 0) die("<script>location.href='game.php?go=rinok';</script>"); 
    $lot = first('SELECT id_lot,count,tip_item,user_id FROM auction_items WHERE id_lot=%d AND user_id=%d AND egg=0 ',$itemoff,$_SESSION['id']);
    if(empty($lot)) die("<script> alert('Р СћР В°Р С”Р С•Р С–Р С• Р В»Р С•РЎвЂљР В° Р Р…Р ВµРЎРѓРЎС“РЎвЂ°Р ВµРЎРѓРЎвЂљР Р†РЎС“Р ВµРЎвЂљ, Р Р†Р С•Р В·Р СР С•Р В¶Р Р…Р С• Р С•Р Р… Р В±РЎвЂ№Р В» Р С”РЎС“Р С—Р В»Р ВµР Р…!'); location.href='game.php?go=rinok';</script>"); 
    $count    = $lot['count'];
    $item_tip = $lot['tip_item'];
    if($_SESSION['id'] != $lot['user_id']) die("<script>location.href='game.php?go=rinok';</script>");
    plus_item($count,$item_tip);
    delete('auction_items','id_lot='.(int)$lot['id_lot']);
    die("<script>alert('Р вЂ™РЎвЂ№ РЎС“Р Т‘Р В°РЎвЂЎР Р…Р С• Р В·Р В°Р В±РЎР‚Р В°Р В»Р С‘ Р С—РЎР‚Р ВµР Т‘Р СР ВµРЎвЂљ!');location.href='game.php?go=rinok';</script>");
}
elseif(!empty($_POST['itemlotid']) && !empty($_POST['toe']) && $_POST['toe'] == 1){
    $itemoff = obr_chis($_POST['itemlotid']);
    if (empty($itemoff) || $itemoff <= 0) die("<script>location.href='game.php?go=rinok';</script>");
    $lot = first('SELECT id_lot,item_id,user_id FROM auction_items WHERE id_lot=%d AND user_id=%d AND egg=1',$itemoff,$_SESSION['id']);
    if(empty($lot)) die("<script> alert('Р СћР В°Р С”Р С•Р С–Р С• Р В»Р С•РЎвЂљР В° Р Р…Р ВµРЎРѓРЎС“РЎвЂ°Р ВµРЎРѓРЎвЂљР Р†РЎС“Р ВµРЎвЂљ, Р Р†Р С•Р В·Р СР С•Р В¶Р Р…Р С• Р С•Р Р… Р В±РЎвЂ№Р В» Р С”РЎС“Р С—Р В»Р ВµР Р…!'); location.href='game.php?go=rinok';</script>");
    if($_SESSION['id'] != $lot['user_id']) die("<script>location.href='game.php?go=rinok';</script>");
    update('eggs',array('users_egg'=>$lot['user_id']),'id_egg='.(int)$lot['item_id']);
    delete('auction_items','id_lot='.(int)$lot['id_lot']);
    die("<script>alert('Р вЂ™РЎвЂ№ РЎС“Р Т‘Р В°РЎвЂЎР Р…Р С• Р В·Р В°Р В±РЎР‚Р В°Р В»Р С‘ РЎРЏР в„–РЎвЂ Р С•!');location.href='game.php?go=rinok';</script>");
}
elseif(!empty($_POST['itemlotid']) && !empty($_POST['toe']) && $_POST['toe'] == 2){
    $item = obr_chis($_POST['itemlotid']);
    if(empty($item) || $item <= 0) die("<script>location.href='game.php?go=rinok';</script>");
    $lot = first('SELECT * FROM auction_items WHERE id_lot=%d AND egg=1 AND regions=%d',$item,$my_Build);
    if(empty($lot)) die("<script>alert('Р СћР В°Р С”Р С•Р С–Р С• Р В»Р С•РЎвЂљР В° Р Р…Р ВµРЎРѓРЎС“РЎвЂ°Р ВµРЎРѓРЎвЂљР Р†РЎС“Р ВµРЎвЂљ, Р Р†Р С•Р В·Р СР С•Р В¶Р Р…Р С• Р С•Р Р… Р В±РЎвЂ№Р В» Р С”РЎС“Р С—Р В»Р ВµР Р…!'); location.href='game.php?go=rinok';</script>");
    if($_SESSION['id'] == $lot['user_id']) die("<script>location.href='game.php?go=rinok';</script>");
    if($lot['user_id_to'] != "no" && $lot['user_id_to'] != $_SESSION['id']) die("<script>alert('Р В­РЎвЂљР С• РЎРЏР в„–РЎвЂ Р С• Р В·Р В°Р В±РЎР‚Р В°РЎвЂљРЎРЉ Р Р…Р ВµР В»РЎРЉР В·РЎРЏ, Р С•Р Р…Р С• Р С—РЎР‚Р С•Р Т‘Р В°Р ВµРЎвЂљРЎРѓРЎРЏ Р Р…Р Вµ Р вЂ™Р В°Р С!');location.href='game.php?go=rinok';</script>");
    if(!provitems(1,$lot['cena'])) die("<script>alert('Р Р€ Р вЂ™Р В°РЎРѓ Р Р…Р ВµР Т‘Р С•РЎРѓРЎвЂљР В°РЎвЂљР С•РЎвЂЎР Р…Р С• Р СР С•Р Р…Р ВµРЎвЂљ Р Т‘Р В»РЎРЏ Р С—Р С•Р С”РЎС“Р С—Р С”Р С‘ РЎРЊРЎвЂљР С•Р С–Р С• РЎРЏР в„–РЎвЂ Р В°!');location.href='game.php?go=rinok';</script>");
      // Р вЂ”Р В°Р В±Р С‘РЎР‚Р В°Р ВµР С Р Т‘Р ВµР Р…РЎРЉР С–Р С‘ РЎС“ Р С—Р С•Р С”РЎС“Р С—Р В°РЎвЂљР ВµР В»РЎРЏ
       minus_item($lot['cena'],1);
      // Р вЂ™РЎвЂ№РЎвЂЎР С‘РЎвЂљР В°Р ВµР С Р Р…Р В°Р В»Р С•Р С– Р С•РЎвЂљ РЎРѓРЎС“Р СР СРЎвЂ№
       $nalog = round($lot['cena']*0.05);
       $res_chen = $lot['cena'] - $nalog;
       nalog_clanz($nalog);
      // Р СњР В°РЎвЂЎР С‘РЎРѓР В»РЎРЏР ВµР С Р Т‘Р ВµР Р…РЎРЉР С–Р С‘ Р С—РЎР‚Р С•Р Т‘Р С•Р Р†РЎвЂ РЎС“
       plus_item($res_chen,1,$lot['user_id']);
      // Р ВР В·Р СР ВµР Р…РЎРЏР ВµР С Р С—Р В°РЎР‚Р В°Р СР ВµРЎвЂљРЎР‚РЎвЂ№ Р Р† Р В±Р В°Р В·Р В°РЎвЂ¦
       update('eggs',array('users_egg'=>$_SESSION['id']),'id_egg='.(int)$lot['item_id']); 
       delete('auction_items','id_lot='.(int)$lot['id_lot']);
      // Р вЂєР С•Р С–Р С‘РЎР‚РЎС“Р ВµР С
         $x001 = first('SELECT base_id_egg FROM eggs WHERE id_egg=%d',$lot['item_id']);
         $arrEgg = array('pokup'=>$_SESSION['id'], 'prodav'=>$lot['user_id'], 'egg'=>$x001['base_id_egg'], 'eggid'=>$lot['item_id'], 'cena'=>$res_chen);
         logGames('egg',$arrEgg);
      // Р РЋР С•Р С•Р В±РЎвЂ°Р В°Р ВµР С Р С—РЎР‚Р С•Р Т‘Р С•Р Р†РЎвЂ РЎС“    
         $text_send = "Р вЂ”Р Т‘РЎР‚Р В°Р Р†РЎРѓРЎвЂљР Р†РЎС“Р в„–РЎвЂљР Вµ, Р вЂ™Р В°РЎв‚¬ РЎвЂљР С•Р Р†Р В°РЎР‚ Р С—Р С•Р Т‘ РІвЂћвЂ“".$item." (РЎРЏР в„–РЎвЂ Р С• #".$x001['base_id_egg']."), Р В±РЎвЂ№Р В»Р С• РЎС“РЎРѓР С—Р ВµРЎв‚¬Р Р…Р С• Р С—РЎР‚Р С•Р Т‘Р В°Р Р…Р С• Р С—Р С•Р В»РЎРЉР В·Р С•Р Р†Р В°РЎвЂљР ВµР В»РЎР‹: ".$_SESSION['login'].". Р вЂ™РЎвЂ№ Р С—Р С•Р В»РЎС“РЎвЂЎР С‘Р В»Р С‘ Р Т‘Р ВµР Р…Р С–Р С‘ Р В·Р В° Р вЂ™Р В°РЎв‚¬ РЎвЂљР С•Р Р†Р В°РЎР‚ Р Р† РЎРѓРЎС“Р СР СР Вµ: ".formatnum($res_chen)." Р СР С•Р Р…Р ВµРЎвЂљ, Р С‘Р В· Р Р…Р С‘РЎвЂ¦ Р Р†РЎвЂ№РЎвЂЎР В»Р С‘ Р Р…Р В°Р В»Р С•Р С– Р Р…Р В° РЎРѓРЎС“Р СР СРЎС“: ".formatnum($nalog)." Р СР С•Р Р…Р ВµРЎвЂљ.";
         messSisyem($text_send,$lot['user_id'],'Р СћР С•РЎР‚Р С–Р С•Р Р†РЎвЂ№Р Вµ Р С•Р С—Р ВµРЎР‚Р В°РЎвЂ Р С‘Р С‘');
      // Р вЂ”Р В°Р С”Р В°Р Р…РЎвЂЎР С‘Р Р†Р В°Р ВµР С РЎРѓР С”РЎР‚Р С‘Р С—РЎвЂљ
die("<script>alert('Р вЂ™РЎвЂ№ РЎС“Р Т‘Р В°РЎвЂЎР Р…Р С• Р С”РЎС“Р С—Р С‘Р В»Р С‘ РЎРЏР в„–РЎвЂ Р С•!'); location.href='game.php?go=rinok';</script>");
}
elseif(!empty($_POST['itemlotid']) && !empty($_POST['amount']) && !empty($_POST['to']) && $_POST['to'] == 2){
   $itemLot = obr_chis($_POST['itemlotid']);
   $am      = obr_chis($_POST['amount']);
   if(empty($itemLot) || $itemLot <= 0 || empty($am) || $am <= 0) die("<script>location.href='game.php?go=rinok';</script>");
   $lot = first('SELECT * FROM auction_items WHERE id_lot=%d AND egg=0 AND regions=%d',$itemLot,$my_Build);
   if(empty($lot)) die("<script> alert('Р СћР В°Р С”Р С•Р С–Р С• Р В»Р С•РЎвЂљР В° Р Р…Р ВµРЎРѓРЎС“РЎвЂ°Р ВµРЎРѓРЎвЂљР Р†РЎС“Р ВµРЎвЂљ, Р Р†Р С•Р В·Р СР С•Р В¶Р Р…Р С• Р С•Р Р… Р В±РЎвЂ№Р В» Р С”РЎС“Р С—Р В»Р ВµР Р…, Р В»Р С‘Р В±Р С• Р С‘Р В·РЎР‰РЎРЏРЎвЂљ РЎРѓ Р С—РЎР‚Р С•Р Т‘Р В°Р В¶Р С‘!'); location.href='game.php?go=rinok';</script>");
   if($lot['user_id_to'] != "no" && $lot['user_id_to'] != $_SESSION['id']) die("<script>alert('Р В­РЎвЂљР С•РЎвЂљ Р С—РЎР‚Р ВµР Т‘Р СР ВµРЎвЂљ Р В·Р В°Р В±РЎР‚Р В°РЎвЂљРЎРЉ Р Р…Р ВµР В»РЎРЉР В·РЎРЏ, Р С•Р Р… Р С—РЎР‚Р С•Р Т‘Р В°Р ВµРЎвЂљРЎРѓРЎРЏ Р Р…Р Вµ Р вЂ™Р В°Р С!');location.href='game.php?go=rinok';</script>");
   if($am > $lot['count']) die("<script> alert('Р вЂ”Р В°РЎвЂЎР ВµР С Р С•Р В±Р СР В°Р Р…РЎвЂ№Р Р†Р В°РЎвЂљРЎРЉ РЎРѓР С‘РЎРѓРЎвЂљР ВµР СРЎС“?!'); location.href='game.php?go=rinok';</script>");
    $itemLot     = $lot['id_lot'];
    $cena        = $lot['cena'];
    $count       = $lot['count'];
    $item_tip    = $lot['tip_item'];
    $prodavec    = $lot['user_id'];
    $cenaRes     = ceil($cena/$count);
    $cenaPokupki = $am*$cenaRes;
    $countRes    = $count-$am;
    $cenaRes     = $countRes*$cenaRes;
  if($countRes < 0) die("<script> alert('Р В§РЎвЂљР С•-РЎвЂљР С• Р Р…Р Вµ РЎвЂљР В°Р С”, Р С—Р С•Р С—РЎР‚Р С•Р В±РЎС“Р в„–РЎвЂљР Вµ РЎРѓР Р…Р С•Р Р†Р В°.'); location.href='game.php?go=rinok';</script>");
  if($_SESSION['id'] == $prodavec) die("<script>alert('Р вЂ”Р В°РЎвЂЎР ВµР С Р С—Р С•Р С”РЎС“Р С—Р В°РЎвЂљРЎРЉ Р С—РЎР‚Р ВµР Т‘Р СР ВµРЎвЂљ РЎС“ РЎРѓР В°Р СР С•Р С–Р С• РЎРѓР ВµР В±РЎРЏ? Р РЋР Р†Р С•Р в„– Р С—РЎР‚Р ВµР Т‘Р СР ВµРЎвЂљ Р С—Р С•Р С”РЎС“Р С—Р В°РЎвЂљРЎРЉ Р Р…Р ВµР В»РЎРЉР В·РЎРЏ!'); location.href='game.php?go=rinok';</script>");
  if(!provitems(1,$cenaPokupki)) die("<script>alert('Р Р€ Р вЂ™Р В°РЎРѓ Р Р…Р ВµР Т‘Р С•РЎРѓРЎвЂљР В°РЎвЂљР С•РЎвЂЎР Р…Р С• Р СР С•Р Р…Р ВµРЎвЂљ Р Т‘Р В»РЎРЏ Р С—Р С•Р С”РЎС“Р С—Р С”Р С‘ РЎРЊРЎвЂљР С•Р С–Р С• Р С—РЎР‚Р ВµР Т‘Р СР ВµРЎвЂљР В°!');location.href='game.php?go=rinok';</script>");
    // Р вЂ”Р В°Р В±Р С‘РЎР‚Р В°Р ВµР С Р Т‘Р ВµР Р…РЎРЉР С–Р С‘ РЎС“ Р С—Р С•Р С”РЎС“Р С—Р В°РЎвЂљР ВµР В»РЎРЏ, Р Р…Р В°РЎвЂЎР С‘РЎРѓР В»Р ВµР С Р С—Р С•Р С”РЎС“Р С—Р В°РЎвЂљР ВµР В»РЎР‹ Р В°Р в„–РЎвЂљР ВµР С.
     minus_item($cenaPokupki,1);
     plus_item($am,$item_tip);
    // Р вЂ™РЎвЂ№РЎвЂЎР С‘РЎвЂљР В°Р ВµР С Р Р…Р В°Р В»Р С•Р С– Р С•РЎвЂљ РЎРѓРЎС“Р СР СРЎвЂ№
     $nalog = round($cenaPokupki*0.05);
     $res_chen = $cenaPokupki - $nalog;
     nalog_clanz($nalog);
    // Р СњР В°РЎвЂЎР С‘РЎРѓР В»РЎРЏР ВµР С Р Т‘Р ВµР Р…РЎРЉР С–Р С‘ Р С—РЎР‚Р С•Р Т‘Р С•Р Р†РЎвЂ РЎС“
     plus_item($res_chen,1,$prodavec);
    // Р ВР В·Р СР ВµР Р…РЎРЏР ВµР С Р С—Р В°РЎР‚Р В°Р СР ВµРЎвЂљРЎР‚РЎвЂ№ Р В»Р С•РЎвЂљР В°
     if($countRes != 0) update('auction_items',array('count'=>$countRes, 'cena'=>$cenaRes),'id_lot='.(int)$itemLot);
     if($countRes == 0) delete('auction_items','id_lot='.(int)$itemLot);
    //Log
      $arrItem = array('pokup'=>$_SESSION['id'], 'prodav'=>$prodavec, 'item'=>$item_tip, 'cena'=>$res_chen, 'cool'=>$am);
      logGames('item',$arrItem);
    // Р РЋР С•Р С•Р В±РЎвЂ°Р В°Р ВµР С Р С—РЎР‚Р С•Р Т‘Р С•Р Р†РЎвЂ РЎС“                          
      $cc = first('SELECT name FROM items WHERE id=%d',$item_tip);
      $textSend = "Р вЂ”Р Т‘РЎР‚Р В°Р Р†РЎРѓРЎвЂљР Р†РЎС“Р в„–РЎвЂљР Вµ, Р вЂ™Р В°РЎв‚¬ РЎвЂљР С•Р Р†Р В°РЎР‚ Р С—Р С•Р Т‘ РІвЂћвЂ“".$itemLot."(".$cc['name']."), Р В±РЎвЂ№Р В» РЎС“РЎРѓР С—Р ВµРЎв‚¬Р Р…Р С• Р С—РЎР‚Р С•Р Т‘Р В°Р Р… Р С—Р С•Р В»РЎРЉР В·Р С•Р Р†Р В°РЎвЂљР ВµР В»РЎР‹: ".$_SESSION['login'].", Р Р† Р С”Р С•Р В»Р С‘РЎвЂЎР ВµРЎРѓРЎвЂљР Р†Р Вµ: ".$am." РЎв‚¬РЎвЂљ. Р вЂ™РЎвЂ№ Р С—Р С•Р В»РЎС“РЎвЂЎР С‘Р В»Р С‘ Р Т‘Р ВµР Р…Р С–Р С‘ Р В·Р В° Р вЂ™Р В°РЎв‚¬ РЎвЂљР С•Р Р†Р В°РЎР‚ Р Р† РЎРѓРЎС“Р СР СР Вµ: ".formatnum($cenaPokupki)." Р СР С•Р Р…Р ВµРЎвЂљ, Р С‘Р В· Р Р…Р С‘РЎвЂ¦ Р Р†РЎвЂ№РЎвЂЎР В»Р С‘ Р Р…Р В°Р В»Р С•Р С– Р Р…Р В° РЎРѓРЎС“Р СР СРЎС“: ".formatnum($nalog)." Р СР С•Р Р…Р ВµРЎвЂљ.";
      if($countRes != 0) $textSend .= " Р СћР С•Р Р†Р В°РЎР‚Р В°(".$cc['name'].") Р Р…Р В° РЎР‚РЎвЂ№Р Р…Р С”Р Вµ Р С•РЎРѓРЎвЂљР В°Р В»Р С•РЎРѓРЎРЉ: ".$countRes." РЎв‚¬РЎвЂљ.";
      messSisyem($textSend,$prodavec,'Р СћР С•РЎР‚Р С–Р С•Р Р†РЎвЂ№Р Вµ Р С•Р С—Р ВµРЎР‚Р В°РЎвЂ Р С‘Р С‘');
    //Р вЂ”Р В°Р С”Р В°Р Р…РЎвЂЎР С‘Р Р†Р В°Р ВµР С РЎРѓР С”РЎР‚Р С‘Р С—РЎвЂљ
die("<script>alert('Р СџРЎР‚Р ВµР Т‘Р СР ВµРЎвЂљ РЎС“Р Т‘Р В°РЎвЂЎР Р…Р С• Р С”РЎС“Р С—Р В»Р ВµР Р…. Р РЋР С—Р В°РЎРѓР С‘Р В±Р С• Р В·Р В° Р С—Р С•Р С”РЎС“Р С—Р С”РЎС“!');location.href='game.php?go=rinok';</script>");          
}
elseif(!empty($_POST['cools']) && !empty($_POST['itemsid']) && !empty($_POST['cena']) && empty($_POST['toe']) && empty($_POST['eggs']) && empty($_POST['to'])){
  $cools = obr_chis($_POST['cools']);
  $items = obr_chis($_POST['itemsid']); 
  $cena  = obr_chis($_POST['cena']);
  if(empty($cools) || $cools <= 0 || empty($items) || $items <= 0 || empty($cena) || $cena <=0)  die("<script>location.href='game.php?go=rinok&do';</script>"); 
  if(!empty($_POST['user_to'])) $user_to = obr_txt($_POST['user_to']); else $user_to = false;
  if($user_to != false && !preg_match("|^[a-z_-]+$|i", $user_to))die("<script>alert('Р СњР ВµР Т‘Р С•Р С—РЎС“РЎРѓРЎвЂљР С‘Р СРЎвЂ№Р Вµ РЎРѓР С‘Р СР Р†Р С•Р В»РЎвЂ№ Р Р† РЎРѓРЎвЂљРЎР‚Р С•Р С”Р Вµ Р С—Р С•Р В»РЎРЉР В·Р С•Р Р†Р В°РЎвЂљР ВµР В»РЎРЏ.');location.href='game.php?go=rinok&do';</script>"); 
  if($user_to){
    $proverka_usera = first('SELECT login,id FROM users WHERE login="%s"',$user_to);
    if(!$proverka_usera) die("<script>alert('Р СћР В°Р С”Р С•Р С–Р С• Р С—Р С•Р В»РЎРЉР В·Р С•Р Р†Р В°РЎвЂљР ВµР В»РЎРЏ: ".$user_to.", Р Р…Р Вµ РЎРѓРЎС“РЎвЂ°Р ВµРЎРѓРЎвЂљР Р†РЎС“Р ВµРЎвЂљ.'); location.href='game.php?go=rinok&do';</script>");                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   
    $user_to_id = $proverka_usera['id'];
  }else{
    $user_to_id = "no";
  }
  $invent = first('SELECT item_id,user_id FROM items_users WHERE user_id=%d AND id=%d',$_SESSION['id'],$items);
  $tip_item = $invent['item_id'];
  $baseItem = first('SELECT torg FROM items WHERE id=%d AND id != 1',$tip_item);
  if(empty($baseItem) || empty($invent)) die("<script>location.href='game.php?go=rinok&do';</script>");
  if($baseItem['torg'] != 0)  die("<script>alert('Р СњР ВµР В»РЎРЉР В·РЎРЏ Р С—РЎР‚Р С•Р Т‘Р В°РЎвЂљРЎРЉ РЎРЊРЎвЂљР С•РЎвЂљ Р С—РЎР‚Р ВµР Т‘Р СР ВµРЎвЂљ.');location.href='game.php?go=rinok&do';</script>");
  if($_SESSION['id'] != $invent['user_id'])  die("<script>location.href='game.php?go=rinok&do';</script>");
  if(!provitems($tip_item,$cools))  die("<script>alert('Р Р€ Р Р†Р В°РЎРѓ Р Р…Р Вµ РЎвЂ¦Р Р†Р В°РЎвЂљР В°Р ВµРЎвЂљ.');location.href='game.php?go=rinok&do';</script>");
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
 die("<script>alert('Р СџРЎР‚Р ВµР Т‘Р СР ВµРЎвЂљ РЎС“Р Т‘Р В°РЎвЂЎР Р…Р С• Р Р†РЎвЂ№РЎРѓРЎвЂљР В°Р Р†Р В»Р ВµР Р… Р Р…Р В° Р С—РЎР‚Р С•Р Т‘Р В°Р В¶РЎС“.'); location.href='game.php?go=rinok&do';</script>"); 
}
elseif(!empty($_POST['eggs']) AND !empty($_POST['cena']) AND !empty($_POST['eggid'])  AND empty($_POST['toe']) AND empty($_POST['to'])){
  $egg  = obr_chis($_POST['eggid']);
  $cena = obr_chis($_POST['cena']); 
  if(empty($egg) || empty($cena) || $egg <= 0 || $cena <= 0) die("<script>alert('Р СњР ВµРЎвЂљ Р С—Р В°РЎР‚Р В°Р СР ВµРЎвЂљРЎР‚Р В°.'); location.href='game.php?go=rinok&do';</script>");
  if(!empty($_POST['user_to'])) $user_to = $_POST['user_to']; else $user_to = false;
  if($user_to != false && !preg_match("|^[a-z_-]+$|i", $user_to))die("<script>alert('Р СњР ВµР Т‘Р С•Р С—РЎС“РЎРѓРЎвЂљР С‘Р СРЎвЂ№Р Вµ РЎРѓР С‘Р СР Р†Р С•Р В»РЎвЂ№ Р Р† РЎРѓРЎвЂљРЎР‚Р С•Р С”Р Вµ Р С—Р С•Р В»РЎРЉР В·Р С•Р Р†Р В°РЎвЂљР ВµР В»РЎРЏ.');location.href='game.php?go=rinok&do';</script>"); 
  if($user_to){
    $proverka_usera = first('SELECT login,id FROM users WHERE login="%s"',$user_to);
    if(!$proverka_usera) die("<script>alert('Р СћР В°Р С”Р С•Р С–Р С• Р С—Р С•Р В»РЎРЉР В·Р С•Р Р†Р В°РЎвЂљР ВµР В»РЎРЏ: ".$user_to.", Р Р…Р Вµ РЎРѓРЎС“РЎвЂ°Р ВµРЎРѓРЎвЂљР Р†РЎС“Р ВµРЎвЂљ.'); location.href='game.php?go=rinok&do';</script>");                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   
    $user_to_id = $proverka_usera['id'];
  }else{
    $user_to_id = "no";
  }
  $eggIsset = first('SELECT users_egg FROM eggs WHERE id_egg=%d AND users_egg=%d',$egg,$_SESSION['id']);
  if(empty($eggIsset)) die("<script>alert('Р Р‡Р в„–РЎвЂ Р С• Р С—РЎР‚Р С‘Р Р…Р В°Р Т‘Р В»Р ВµР В¶Р С‘РЎвЂљ Р Р…Р Вµ Р вЂ™Р В°Р С, Р В»Р С‘Р В±Р С• Р С•Р Р…Р С• РЎС“Р В¶Р Вµ Р Р†РЎвЂ№Р В»РЎС“Р С—Р С‘Р В»Р С•РЎРѓРЎРЉ!'); location.href='game.php?go=rinok&do';</script>");
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
  die("<script>alert('Р Р‡Р в„–РЎвЂ Р С• РЎС“Р Т‘Р В°РЎвЂЎР Р…Р С• Р Р†РЎвЂ№РЎРѓРЎвЂљР В°Р Р†Р В»Р ВµР Р…Р С• Р Р…Р В° Р С—РЎР‚Р С•Р Т‘Р В°Р В¶РЎС“.');location.href='game.php?go=rinok&do';</script>");
}else{
  die("<script>alert('Р СњР ВµРЎвЂљ Р С—Р В°РЎР‚Р В°Р СР ВµРЎвЂљРЎР‚Р В°.'); location.href='game.php?go=rinok&do';</script>");
   
} 
die();
}
?>

   


       

   



