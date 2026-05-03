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
      if(!empty($pr_fren['id_fr'])) die("<script>alert('Данный тренер уже является вашим другом'); window.close();</script>"); 
      if(!empty($isset_zay['id_fz'])) die("<script>alert('Вы уже подавали заявку на дружбу с этим тренером. Пожалуйста дождитесь ответа.'); window.close();</script>");
     $code = md5(rand(100000,999999));
     $tRes = time() +(60*60*24*1);
     $id_zap = insert('friends_zayv',array('id_user'=>$_SESSION['id'], 'id_user_to'=>$user_id, 'code'=>$code, 'time'=>$tRes));
     $text = "Здравствуйте, тренер: <b>".$_SESSION['login']."</b> хочет добавить Вас в друзья. Для того, чтобы принять дружбу, перейдите по <a href=\"game.php?go=friends&tip=3&id=".$id_zap."\" target=\"_blank\" style=\"color:brown;\">ЭТОЙ</a> ссылке. Для отказа просто проигнорируйте данное сообщение. Заявка удалится автоматически через 24 часа.";
     messSisyem($text,$user_id,'Заявка на дружбу');
   echo "<script>alert('Заявка на добавление в друзья успешно отправлена.'); window.close();</script>";
  } elseif($tip == 2) {
    $friends_yes = first('SELECT * FROM friends WHERE id_user=%d AND id_my_friend=%d',$_SESSION['id'],$user_id);
      if(!$friends_yes) die("<script>window.close();</script>");
    delete('friends','id_user='.(int)$_SESSION['id'].' AND  id_my_friend='.(int)$user_id);
    delete('friends','id_user='.(int)$user_id.' AND  id_my_friend='.(int)$_SESSION['id']);
    echo "<script>alert('Вы удачно расторгли дружбу с этим тренером.'); window.close();</script>";
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
   die("<script>alert('Вы удачно приняли дружбу.'); window.close();</script>");
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
      if($_GET['to_id'] == $_SESSION['id']) $txt_title = "Мои друзья(".$countfriends.")"; 
        else $txt_title = "Друзья ".$user_toid['login']."(".$countfriends.")";
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
        echo "У ".$user_toid['login']." на данный момент нет ни одного друга. 
              <br><a href=javascript: onClick=\"win1=window.open('game.php?go=friends&tip=1&id=".$user_toid['id']."','NewFriens','width=580,height=350,scrollbars=yes');return true;\" style=\"color:#afeeee\">[Добавить в друзья]</a>";
    }else{
        echo $echofriends;
    }
    
    ?>
  </div>
</center>
<?
}
?>