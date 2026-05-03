<?
if (!empty($_SESSION['login']) and !empty($_SESSION['password'])){
  $login = $_SESSION['login'];
  $password = $_SESSION['password'];
  $admin = first('SELECT * FROM users WHERE id=%d AND activation=1 AND groups=1',$_SESSION['id']);
  $info_user = first('SELECT admins_panels FROM information_users WHERE users_id=%d',$_SESSION['id']);
  if(empty($admin['id'])) die("<script>location.href='..';</script>"); 
  if($info_user['admins_panels'] != 1) die("<script>location.href='..';</script>"); 
}else{
  die("<script>location.href='..';</script>"); 
}
$pass = 'Liquid';
if (!isset($_SESSION['adminPanels'])) {
 if (isset($_POST['password']) && $_POST['password']==$pass) {
   $_SESSION['adminPanels'] = true;
   die("<script>location.href=location.href;</script>");
 }
 print '
       <style>
       body, html {
         background-image: url("/css/img/brushed_alu_dark.jpg");
         position: relative; 
         margin:0 0 0 0;
         padding:0 0 0 0;
         color: #000000; 
        }
       </style> 
       <p style="margin-top: 240px; font-weight:bold;" align=center valign=middle>Р вЂ™Р Р†Р ВµР Т‘Р С‘РЎвЂљР Вµ Р С—Р В°РЎР‚Р С•Р В»РЎРЉ:
       <table align=center>
       <form action="" method="POST">
       <input type="password" name="password" value="" style="color:#000;font-size:15px;font-weight:bold;border: 2px solid #000;">
       <input type="submit" value="OK" style="color:#000;font-weight:bold;border: 2px solid #000;font-size:15px;">
       </form></table></p>';
 exit;
}
require_once ("admin/shapka.php");
require_once ("admin/function.php");
?>
<style type="text/css">
.c1 {
	height:1px;
	margin:0 6px;
}
.c2 {
	border-width:0 2px;
	margin:0 4px;
	height:1px;
}
.c3 {
	border-width:0 1px;
	margin:0 3px;
	height:1px;
}
.c4 {
	border-width:0 1px;
	margin:0 2px;
	height:1px;
}
.c5 {
	border-width:0 1px;
	margin:0 1px;
	height:2px;
}
.c1, .c2, .c3, .c4, .c5 {
	overflow:hidden;
}

#name  {

	background:#363636;
}
#txt2 { color:#B8860B; }
.gran { 

    border-color: green; 
    border-style: solid; 
    padding: 5 5 5 5px; 
   }
   .gran_tw { 
    width: 80px;
    border-color: #B8860B; 
    border-style: groove;
    border-width: 7px;
    padding: 5 5 5 5px; 
   }
    .gran_tr { 
    color: black;
    height: 60px;
    border-color: #B8860B; 
    border-style: groove;
    border-width: 7px;
    padding: 5 5 5 5px; 
   }
    .gran_fo { 
    width: 50px;
    height: 50px;
    border-color: #2E8B57; 
    border-style: groove;
    border-width: 7px;
    padding: 5 10 5 10px; 
   }
    .gran_fi { 
    width: 80%;
    height: 50px;
    border-color: #2E8B57; 
    border-style: groove;
    border-width: 7px;
    padding: 2 2 2 2px; 
   }
   .shadowtext {
    text-shadow: Black 1px 1px 2px, #DAA520 0 0 1em; 
    color: gold; 
    font-size: 3em; 
    border-color: green; 
    border-style: solid; 
    padding: 5 5 5 5px;
   }
    .shadowtext_tw {
    text-shadow: Black 1px 1px 2px, #DAA520 0 0 1em; 
    color: gold; 
    font-size: 3em;  
    padding: 5 5 5 5px;
   }
   INPUT,TEXTAREA {
        background-color: Ivory;
        font:8pt Tahoma;
        BORDER: #b3d0c1 1px solid;
        color: #000000;
}

SELECT {
        border-style:none;
        font:8pt Tahoma;
        color: #000000;
}
.codeNapoff {

    font-family: sans-serif; 
    font-weight: bold; 
    font-size: 90%; 
    padding: 5px; 
    margin: 0; 
    color: white; 
    border-bottom: 2px solid white; 
    border-color: #1C1C1C;
} 
</style>
<script type="text/javascript">
function simpletagfont()
{ 
  cMes = document.getElementById('form1')['news'].value
  cAdd = '<font color=Brown>'+cMes+'</font>'
  document.getElementById('form1')['news'].value = cAdd;              }
  
function simpletagb()
{
  cMes = document.getElementById('form1')['news'].value
  cAdd = '<b>'+cMes+'</b>'
  document.getElementById('form1')['news'].value = cAdd;             }
  function simpletagi()
{
  cMes = document.getElementById('form1')['news'].value
  cAdd = '<i>'+cMes+'</i>'
  document.getElementById('form1')['news'].value = cAdd;             }
</script>
<?php
if (!empty($_GET['do'])) { 
    $do_adm = $_GET['do'];

    if     ($do_adm == "news")           require_once(__DIR__ . "/bb_news_admin.php");
    elseif ($do_adm == "pok")            require_once(__DIR__ . "/poke.php");
    elseif ($do_adm == "basepok")        require_once(__DIR__ . "/basepoke.php");
    elseif ($do_adm == "buildings")      require_once(__DIR__ . "/buildings.php");
    elseif ($do_adm == "pokeloc")        require_once(__DIR__ . "/locpoke.php");
    elseif ($do_adm == "item")           require_once(__DIR__ . "/ite.php");
    elseif ($do_adm == "atc")            require_once(__DIR__ . "/atc.php");
    elseif ($do_adm == "alm")            require_once(__DIR__ . "/alm.php");
    elseif ($do_adm == "gitem")          require_once(__DIR__ . "/gitem.php");
    elseif ($do_adm == "logs")           require_once(__DIR__ . "/logs.php");
    elseif ($do_adm == "bans")           require_once(__DIR__ . "/bans.php");
    elseif ($do_adm == "log_alm")        require_once(__DIR__ . "/logsam.php");
    elseif ($do_adm == "info_pokes")     require_once(__DIR__ . "/info_pokes.php");
    elseif ($do_adm == "attak_pokes")    require_once(__DIR__ . "/attak_pokes.php");
    elseif ($do_adm == "info_tur")       require_once(__DIR__ . "/info_tur.php");
    elseif ($do_adm == "info_tur_user")  require_once(__DIR__ . "/info_tur_user.php");
    elseif ($do_adm == "medal")          require_once(__DIR__ . "/medal.php");
} else {
    print '<h2>' . $login . ', Р Т‘Р С•Р В±РЎР‚Р С• Р С—Р С•Р В¶Р В°Р В»Р С•Р Р†Р В°РЎвЂљРЎРЉ Р Р† Р С—Р В°Р Р…Р ВµР В»РЎРЉ Р В°Р Т‘Р СР С‘Р Р…Р С‘РЎРѓРЎвЂљРЎР‚Р В°РЎвЂљР С•РЎР‚Р В°!</h2>';
}

require_once(__DIR__ . "/bottom.php");
?>


