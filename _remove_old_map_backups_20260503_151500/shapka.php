<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml">
<script>
function updateGameData() {
    fetch('/live.ajax.php')
        .then(r => r.json())
        .then(data => {
            if (data.error) return;

            if (data.map) document.getElementById('gameMap').innerHTML = data.map;
            if (data.buttons) document.getElementById('gameButtons').innerHTML = data.buttons;
            if (data.chat) document.getElementById('gameChat').innerHTML = data.chat;

            if (data.battle) {
                window.location.href = '/game.php?go=fight_pve';
            }
        })
        .catch(e => console.error('AJAX error:', e));
}

setInterval(updateGameData, 1000); // обновление каждую секунду
</script>

<head>
<meta http-equiv="Content-Type" content="text/html; Charset=Windows-1251" />
<link Rel="stylesheet" Href="css/stylepl.css" Type="text/css">
<link rel="shortcut icon" href="favicon.ico">
<script type='text/javascript' src='/script/jquery.js'></script>
<script type='text/javascript' src='/script/md5.js'></script>
<script type='text/javascript' src='/script/textJs.js'></script>
<script type='text/javascript' src='/script/autr.js'></script>
<script type="text/javascript">
sitePath = "/";
sflakesMax = 64;
sflakesMaxActive = 64;
svMaxX = 3;
svMaxY = 3;
ssnowStick = 1;
sfollowMouse = 1;
</script>
<script type="text/javascript" src="/snow/snow.js"></script>
<script type='text/javascript'>
function hrefs(){
  window.location.href=window.location.href;
}
</script>
<div class='inside'>                                                                       
<div id='bar'>
<div id='autorizeDiv'>
<?php if(!$autorize){ ?>
<table style="">
<tr>
<td>
<form name="formAut" id="formAut"  action=""  method="post">
Логин: <input id="login" class="formAut" name="LOGIN" type="text" size="16" maxlength="16" value=""> 
Пароль: <input id="pass" class="formAut" name="PASSWORD" type="password" size="16" maxlength="16" value=""> 
</td>
<td>
  <input id="autoChec" class="checAut" name="AUTO" type="checkbox" style="cursor:hand"> 
  Автовход
</form>
</td>
<td>
   <button id="confirmOk" type="button" class="btInp" onclick="autGo();" title="Авторизоваться">Вход</button>
</td>
<td>
  | <button id="confirmOk" type="button" class="btInp" onclick="window.location.href='index.php?go=reg';" title="Зарегистрироваться">Регистрация</button>
</td>
<td>
  | <button id="confirmOk" type="button" class="btInp" onclick="window.location.href='index.php?go=sendpass';" title="Восстановить пароль">Восстановление пароля</button>
</td>
</tr>
</table>
<?php
$autorize = true;

$__GAME_UI_ROUTES = array(
  'map','char','charWork','fight_pve','fight_pvp','trenInfo',
  'pokedex','atk','friends','quest_list','moderpanel',
  'pokemon','sends','users','items','eventsNewYear','eggs',
  'profile','diamond_shop','rinok','clans','pokerinok','mapusers','chat'
);

// если текущий go — игровой, ставим флаг ДО инклюдов
if (isset($_GET['go']) && in_array($_GET['go'], $__GAME_UI_ROUTES, true)) {
    if (!defined('POKE_GAME_UI')) define('POKE_GAME_UI', 1);
} 
} else { 
  // Показываем приветствие только если мы не в игровом интерфейсе
  if (!defined('POKE_GAME_UI')) {
    $alm = formatnum(coolseitems(2,$_SESSION['id']));
    print 'Приветствуем Вас, тренер: '.$_SESSION['login'].'!
    |  <button type="button" class="btInp" onclick="window.open(\'/game.php?go=map\',\'mir\');">В мир</button>
    | У Вас на счету:  <button type="button" class="btInp" onclick="location.href=\'/game.php?go=diamond_shop\';">'.($alm?$alm:'0').' -  Алмазов</button>
    | Вы являетесь: '.textGroup(users_conect('groups')).'. 
    |  <button type="button" class="btInp" onclick="location.href=\'/index.php?go=exits\';">Выход</button>';
  }
}
?>
</div>
<div id="autMes" style="display:none;"></div>
<div id='autherror' style="display:none;"></div>
</div>
</head>
<body>
<div class="uptabl"></div>
<table class="uptabl" cellspacing="0">
  <tbody>
    <tr>
      <td class="shapka-body"></td>
    </tr>
    <tr class="menu-container">
      <td class="menu-container">
        <div class="menu" id="menu">
          <table class="hmenu">
            <tbody>
              <tr class="hmenu">
                <td class="hmenu-left"></td>
                <td class="hmenu">
                  <ul id="nav">
                    <li class="top"><a href="/game.php?go=map" class="top_link" target="_blank"><span class="down" style="color:red">В мир!</span></a></li>
                    <li class="top"><a href="/game.php?go=pokemon" class="top_link"><span class="down">Покемоны</span></a></li>
                    <li class="top"><a href="/game.php?go=sends" id="shop" class="top_link"><span class="down">Почта</span></a></li>                    
                    <li class="top"><a href="#" id="shop" class="top_link"><span class="down">Тренеры</span></a>
                      <ul class="sub">
                        <li><a href="/game.php?go=users">Список Тренеров</a></li>
                        <li><a href="/game.php?go=clans">Список Кланов</a></li>
                      </ul>
                    </li>                   
                    <li class="top"><a href="javascript:" id="shop" class="top_link" onClick="window.open('/game.php?go=items','items','width=650,height=500,scrollbars=yes'); return true;"><span class="down">Инвентарь</span></a></li>
                    <li class="top"><a href="/game.php?go=profile" id="shop" class="top_link"><span class="down">Профиль</span></a></li>
                    <li class="top"><a href="/game.php?go=diamond_shop" class="top_link"><span class="down" style="color:gold"><strong>Рынок</strong></span></a>
                     <ul class="sub">
                      <li><a href="game.php?go=diamond_shop">Алмазный Магазин</a></li>
                      <li><a href="game.php?go=pokerinok" target="_blank">Рынок Покемонов</a></li>
                      <li><a href="game.php?go=rinok" target="_blank">Рынок Айтемов</a></li>
                     </ul>
                    </li>
                    <li class="top"><a href="" class="top_link" target="_blank"><span class="down"><strong>Прочее</strong></span></a>
                      <ul class="sub">
                        <li><a href="http://forum.league-of-pokemons.ru/" target="_blank">Форум</a></li>
                        <li><a href=".." target="_blank">Главная</a></li>
                        <li><a href="/index.php?go=exits">Выход</a></li>
                      </ul>
                    </li>
                  </ul>
                  <td class="hmenu-right"></td>
                </td>
            </tbody>
          </table>
        </div>
      </td>
    </tr>
  </tbody>
</table>
<table class="midtabl" cellpadding="0" cellspacing="6">
  <tbody>
    <tr>
      <td class="news">
      <noscript>
        <font color="brown"><b style="font-size:20;"> Уважаемый пользователь сайта, на вашем браузере не включена опция чтения - "Javascript", для корректной работы и отображения сайта, вам потребуется включить эту опцию в настройках своего браузера, или переустановить его. </b>
        </font><br>
      </noscript>
