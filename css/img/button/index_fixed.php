<?php
session_start();
include ("include/function/config.php");
include ("include/function/db3.php");
include ("include/function/globfanction.php");
include ("include/function/functionusers.php");
include ("include/class/index.class.php");
include ("ban.php");

$rang = select('SELECT id FROM users WHERE rang_b > 10 AND groups != 1 AND groups != 7 ORDER BY rang_b DESC limit 10');

$maney = select('SELECT iu.count,u.id 
                 FROM items_users iu 
                 INNER JOIN users u ON u.id=iu.user_id 
                 WHERE iu.item_id = 1 AND iu.count > 1000000
                 AND u.groups != 1 AND u.groups != 7
                 ORDER BY iu.count DESC limit 10');

$dex_norm = select('SELECT count_poke,id
                    FROM users
                    WHERE activation=1 AND id != 3 AND groups != 1 AND groups != 7
                    AND count_poke > 1
                    ORDER BY count_poke DESC limit 10');

$dex_shiny = select('SELECT count_poke_s,id 
                     FROM users 
                     WHERE activation=1 AND id != 3 AND groups != 1 AND groups != 7 
                     AND count_poke_s > 0
                     ORDER BY count_poke_s DESC limit 10');

$myrow = false;
if(!empty($_SESSION['login']) && !empty($_SESSION['password'])){
  if(!empty($_SESSION['browse']) && $_SESSION['browse'] == getBrowserSign()){
    $myrow = first('SELECT id,login,activation,password,groups FROM users WHERE login="%s" AND password="%s" AND activation=1', $_SESSION['login'], $_SESSION['password']);
    $_SESSION['browse'] = getBrowserSign();
  }
}
?>