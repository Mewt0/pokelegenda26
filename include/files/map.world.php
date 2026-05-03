<?php
if(isset($_GET['post']) && !empty($_POST)){
  require_once ("include/function/post.map.php");
  die();
}
if(isset($_GET['gets']) && !empty($_GET)){
  require_once ("include/function/get.map.php");
  die();
}
?>
<html><head>
<title>League Of Pokemons -> Игровой мир</title>
<meta name="description" content="Онлайн игра про покемонов">
<meta name="keywords" content="Игра онлайн, покемоны онлайн, покемоны, игра про покемонов, онлайн игра про покемонов">
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
<meta http-equiv="pragma" content= "no-cache">
<link rel="Stylesheet" href="css/style.css" type="text/css">
<script type='text/javascript' src='/script/jquery.js'></script>
<script type='text/javascript' src='/script/textJs.js'></script>
<script type='text/javascript' src='/script/map.js'></script>
</head>
<frameset rows="54%,*,50,0" frameborder=0 framespacing=0 bordercolor=#000000>
  <frame src="/game.php?go=char" name="_location" scrolling="auto" frameborder=0>
  <frameset cols="*,240" frameborder=0 framespacing=0 bordercolor=#000000>
    <frame src="/game.php?go=chat" name="_chat" scrolling="auto" frameborder=0>
    <frame src="/gameusers" name="_usersonline" scrolling="auto" frameborder=0>
  </frameset>
  <frame src="game.php?go=buttons" name="_input" scrolling="NO" frameborder=0 noresize>
  <frameset rows="0,0,0,0">
    <frame src="" name="_chat_two" noresize>
    <frame src="" name="_location_two" noresize>
    <frame src="" name="_location_work" noresize>
    <frame src="" name="_chat_work" noresize>
  </frameset>
</frameset>
<noframes>Ваш браузер не поддерживает фреймы</noframes>
<body>
</body>
</html>
