<?php
$date = date("Y-m-d");
if (!empty($_GET['tip']) AND !empty($_GET['id'])) { 
  if(!$_GET['tip'] OR !$_GET['id'])  die("<script>window.close();</script>"); 
  $tip = $_GET['tip']; 
  $user_id = $_GET['id'];
  delete('friends_zayv','time<='.(int)time()); 
  if($tip == 1){
    if($user_id == $_SESSION['id']) die("<script>window.close();</script>"); 
    $user_to_fri = first('SELECT id FROM users WHERE id=%d',$user_id);
    $pr_fren = first('SELECT id_fr FROM friends WHERE id_user=%d AND id_my_friend=%d',$_SESSION['id'],$user_id);
    $isset_zay = first('SELECT id_fz FROM friends_zayv WHERE id_user=%d AND id_user_to=%d',$_SESSION['id'],$user_id);
      if(!$user_to_fri) die("<script>window.close();</script>");
      if(!empty($pr_fren['id_fr'])) die("<script>alert('Р вЂќР В°Р Р…Р Р…РЎвЂ№Р в„– РЎвЂљРЎР‚Р ВµР Р…Р ВµРЎР‚ РЎС“Р В¶Р Вµ РЎРЏР Р†Р В»РЎРЏР ВµРЎвЂљРЎРѓРЎРЏ Р Р†Р В°РЎв‚¬Р С‘Р С Р Т‘РЎР‚РЎС“Р С–Р С•Р С'); window.close();</script>"); 
      if(!empty($isset_zay['id_fz'])) die("<script>alert('Р вЂ™РЎвЂ№ РЎС“Р В¶Р Вµ Р С—Р С•Р Т‘Р В°Р Р†Р В°Р В»Р С‘ Р В·Р В°РЎРЏР Р†Р С”РЎС“ Р Р…Р В° Р Т‘РЎР‚РЎС“Р В¶Р В±РЎС“ РЎРѓ РЎРЊРЎвЂљР С‘Р С РЎвЂљРЎР‚Р ВµР Р…Р ВµРЎР‚Р С•Р С. Р СџР С•Р В¶Р В°Р В»РЎС“Р в„–РЎРѓРЎвЂљР В° Р Т‘Р С•Р В¶Р Т‘Р С‘РЎвЂљР ВµРЎРѓРЎРЉ Р С•РЎвЂљР Р†Р ВµРЎвЂљР В°.'); window.close();</script>");
     $code = md5(rand(100000,999999));
     $tRes = time() +(60*60*24*1);
     $id_zap = insert('friends_zayv',array('id_user'=>$_SESSION['id'], 'id_user_to'=>$user_id, 'code'=>$code, 'time'=>$tRes));
     $text = "Р вЂ”Р Т‘РЎР‚Р В°Р Р†РЎРѓРЎвЂљР Р†РЎС“Р в„–РЎвЂљР Вµ, РЎвЂљРЎР‚Р ВµР Р…Р ВµРЎР‚: <b>".$_SESSION['login']."</b> РЎвЂ¦Р С•РЎвЂЎР ВµРЎвЂљ Р Т‘Р С•Р В±Р В°Р Р†Р С‘РЎвЂљРЎРЉ Р вЂ™Р В°РЎРѓ Р Р† Р Т‘РЎР‚РЎС“Р В·РЎРЉРЎРЏ. Р вЂќР В»РЎРЏ РЎвЂљР С•Р С–Р С•, РЎвЂЎРЎвЂљР С•Р В±РЎвЂ№ Р С—РЎР‚Р С‘Р Р…РЎРЏРЎвЂљРЎРЉ Р Т‘РЎР‚РЎС“Р В¶Р В±РЎС“, Р С—Р ВµРЎР‚Р ВµР в„–Р Т‘Р С‘РЎвЂљР Вµ Р С—Р С• <a href=\"game.php?go=friends&tip=3&id=".$id_zap."\" target=\"_blank\" style=\"color:brown;\">Р В­Р СћР С›Р в„ў</a> РЎРѓРЎРѓРЎвЂ№Р В»Р С”Р Вµ. Р вЂќР В»РЎРЏ Р С•РЎвЂљР С”Р В°Р В·Р В° Р С—РЎР‚Р С•РЎРѓРЎвЂљР С• Р С—РЎР‚Р С•Р С‘Р С–Р Р…Р С•РЎР‚Р С‘РЎР‚РЎС“Р в„–РЎвЂљР Вµ Р Т‘Р В°Р Р…Р Р…Р С•Р Вµ РЎРѓР С•Р С•Р В±РЎвЂ°Р ВµР Р…Р С‘Р Вµ. Р вЂ”Р В°РЎРЏР Р†Р С”Р В° РЎС“Р Т‘Р В°Р В»Р С‘РЎвЂљРЎРѓРЎРЏ Р В°Р Р†РЎвЂљР С•Р СР В°РЎвЂљР С‘РЎвЂЎР ВµРЎРѓР С”Р С‘ РЎвЂЎР ВµРЎР‚Р ВµР В· 24 РЎвЂЎР В°РЎРѓР В°.";
     messSisyem($text,$user_id,'Р вЂ”Р В°РЎРЏР Р†Р С”Р В° Р Р…Р В° Р Т‘РЎР‚РЎС“Р В¶Р В±РЎС“');
   echo "<script>alert('Р вЂ”Р В°РЎРЏР Р†Р С”Р В° Р Р…Р В° Р Т‘Р С•Р В±Р В°Р Р†Р В»Р ВµР Р…Р С‘Р Вµ Р Р† Р Т‘РЎР‚РЎС“Р В·РЎРЉРЎРЏ РЎС“РЎРѓР С—Р ВµРЎв‚¬Р Р…Р С• Р С•РЎвЂљР С—РЎР‚Р В°Р Р†Р В»Р ВµР Р…Р В°.'); window.close();</script>";
  } elseif($tip == 2) {
    $friends_yes = first('SELECT * FROM friends WHERE id_user=%d AND id_my_friend=%d',$_SESSION['id'],$user_id);
      if(!$friends_yes) die("<script>window.close();</script>");
    delete('friends','id_user='.(int)$_SESSION['id'].' AND  id_my_friend='.(int)$user_id);
    delete('friends','id_user='.(int)$user_id.' AND  id_my_friend='.(int)$_SESSION['id']);
    echo "<script>alert('Р вЂ™РЎвЂ№ РЎС“Р Т‘Р В°РЎвЂЎР Р…Р С• РЎР‚Р В°РЎРѓРЎвЂљР С•РЎР‚Р С–Р В»Р С‘ Р Т‘РЎР‚РЎС“Р В¶Р В±РЎС“ РЎРѓ РЎРЊРЎвЂљР С‘Р С РЎвЂљРЎР‚Р ВµР Р…Р ВµРЎР‚Р С•Р С.'); window.close();</script>";
  } elseif($tip == 3) {
    $user_to_zayv = first('SELECT * FROM friends_zayv WHERE id_fz=%d AND id_user_to=%d',$_GET['id'],$_SESSION['id']);
      if(empty($user_to_zayv['id_fz'])) die("<script>window.close();</script>");
        $f_my  =  first('SELECT * FROM friends WHERE id_user=%d AND id_my_friend=%d',$_SESSION['id'],$user_to_zayv['id_user']);
      if(!empty($f_my['id_fr'])){  
          delete('friends_zayv','id_fz='.(int)$_GET['id']); 
          die("<script>window.close();</script>");
      }
    insert('friends',array(
          'id_user'=>$_SESSION['id'],
          'id_my_friend'=>$user_to_zayv['id_user']));
    insert('friends',array(
          'id_user'=>$user_to_zayv['id_user'],
          'id_my_friend'=>$_SESSION['id']));
    delete('friends_zayv','id_fz='.(int)$_GET['id']);
   die("<script>alert('Р вЂ™РЎвЂ№ РЎС“Р Т‘Р В°РЎвЂЎР Р…Р С• Р С—РЎР‚Р С‘Р Р…РЎРЏР В»Р С‘ Р Т‘РЎР‚РЎС“Р В¶Р В±РЎС“.'); window.close();</script>");
  }else{ 
    die("<script>window.close();</script>"); 
  }
}
elseif(!empty($_GET['to_id'])){
  if(empty($_GET['to_id']) || $_GET['to_id'] <= 0) { die("<script>window.close();</script>"); }
  $user_toid = first('SELECT id,login FROM users WHERE id=%d',$_GET['to_id']);
    if(empty($user_toid['id'])){ die("<script>window.close();</script>"); }
  $result_friends = select('SELECT * FROM friends WHERE id_user=%d',$user_toid['id'],$user_toid['id']);


      $countfriends = 0;
      $echofriends  = '';
      foreach($result_friends as $friends_u){
        $user_frien = first('SELECT groups,login,id FROM users WHERE id=%d',$friends_u['id_my_friend']);
          if(!$user_frien){
             // not
          }else{                                                                                                                     
              $countfriends++;
              $echofriends .= color_group_users($user_frien["id"])."<br>"; 
          }
      }
      if($_GET['to_id'] == $_SESSION['id']) $txt_title = "Р СљР С•Р С‘ Р Т‘РЎР‚РЎС“Р В·РЎРЉРЎРЏ(".$countfriends.")"; 
        else $txt_title = "Р вЂќРЎР‚РЎС“Р В·РЎРЉРЎРЏ ".$user_toid['login']."(".$countfriends.")";
?>
<body style = "background-image: url('/css/img/micro_carbon.png');">
  <title><?echo $txt_title;?></title>
<center>
  <div style="width: 80%; background-image: url('/css/img/micro_carbon.png');  border-bottom: 2px solid black; padding:3px; border: solid 2px #afeeee; border-bottom: 0px;">
    <font style=" FONT-SIZE: 15px; FONT-FAMILY: Tahoma; text-align:justify; color: #fff; "><b><?echo $txt_title;?>:</b></font>
  </div>                                                              
  <div style="width: 80%; height:250; background-image: url('/css/img/vichy.png'); overflow: auto; padding:3px; color:#fff; border: solid 2px #afeeee;" align="left">
    <?
    if(!$result_friends){
        echo "Р Р€ ".$user_toid['login']." Р Р…Р В° Р Т‘Р В°Р Р…Р Р…РЎвЂ№Р в„– Р СР С•Р СР ВµР Р…РЎвЂљ Р Р…Р ВµРЎвЂљ Р Р…Р С‘ Р С•Р Т‘Р Р…Р С•Р С–Р С• Р Т‘РЎР‚РЎС“Р С–Р В°. 
              <br><a href=javascript: onClick=\"win1=window.open('game.php?go=friends&tip=1&id=".$user_toid['id']."','NewFriens','width=580,height=350,scrollbars=yes');return true;\" style=\"color:#afeeee\">[Р вЂќР С•Р В±Р В°Р Р†Р С‘РЎвЂљРЎРЉ Р Р† Р Т‘РЎР‚РЎС“Р В·РЎРЉРЎРЏ]</a>";
    }else{
        echo $echofriends;
    }
    
    ?>
  </div>
</center>
<?
}
?>