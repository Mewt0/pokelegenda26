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

setInterval(updateGameData, 1000); // Р С•Р В±Р Р…Р С•Р Р†Р В»Р ВµР Р…Р С‘Р Вµ Р С”Р В°Р В¶Р Т‘РЎС“РЎР‹ РЎРѓР ВµР С”РЎС“Р Р…Р Т‘РЎС“
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
Р вЂєР С•Р С–Р С‘Р Р…: <input id="login" class="formAut" name="LOGIN" type="text" size="16" maxlength="16" value=""> 
Р СџР В°РЎР‚Р С•Р В»РЎРЉ: <input id="pass" class="formAut" name="PASSWORD" type="password" size="16" maxlength="16" value=""> 
</td>
<td>
  <input id="autoChec" class="checAut" name="AUTO" type="checkbox" style="cursor:hand"> 
  Р С’Р Р†РЎвЂљР С•Р Р†РЎвЂ¦Р С•Р Т‘
</form>
</td>
<td>
   <button id="confirmOk" type="button" class="btInp" onclick="autGo();" title="Р С’Р Р†РЎвЂљР С•РЎР‚Р С‘Р В·Р С•Р Р†Р В°РЎвЂљРЎРЉРЎРѓРЎРЏ">Р вЂ™РЎвЂ¦Р С•Р Т‘</button>
</td>
<td>
  | <button id="confirmOk" type="button" class="btInp" onclick="window.location.href='index.php?go=reg';" title="Р вЂ”Р В°РЎР‚Р ВµР С–Р С‘РЎРѓРЎвЂљРЎР‚Р С‘РЎР‚Р С•Р Р†Р В°РЎвЂљРЎРЉРЎРѓРЎРЏ">Р В Р ВµР С–Р С‘РЎРѓРЎвЂљРЎР‚Р В°РЎвЂ Р С‘РЎРЏ</button>
</td>
<td>
  | <button id="confirmOk" type="button" class="btInp" onclick="window.location.href='index.php?go=sendpass';" title="Р вЂ™Р С•РЎРѓРЎРѓРЎвЂљР В°Р Р…Р С•Р Р†Р С‘РЎвЂљРЎРЉ Р С—Р В°РЎР‚Р С•Р В»РЎРЉ">Р вЂ™Р С•РЎРѓРЎРѓРЎвЂљР В°Р Р…Р С•Р Р†Р В»Р ВµР Р…Р С‘Р Вµ Р С—Р В°РЎР‚Р С•Р В»РЎРЏ</button>
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

// Р ВµРЎРѓР В»Р С‘ РЎвЂљР ВµР С”РЎС“РЎвЂ°Р С‘Р в„– go РІР‚вЂќ Р С‘Р С–РЎР‚Р С•Р Р†Р С•Р в„–, РЎРѓРЎвЂљР В°Р Р†Р С‘Р С РЎвЂћР В»Р В°Р С– Р вЂќР С› Р С‘Р Р…Р С”Р В»РЎР‹Р Т‘Р С•Р Р†
if (isset($_GET['go']) && in_array($_GET['go'], $__GAME_UI_ROUTES, true)) {
    if (!defined('POKE_GAME_UI')) define('POKE_GAME_UI', 1);
} 
} else { 
  // Р СџР С•Р С”Р В°Р В·РЎвЂ№Р Р†Р В°Р ВµР С Р С—РЎР‚Р С‘Р Р†Р ВµРЎвЂљРЎРѓРЎвЂљР Р†Р С‘Р Вµ РЎвЂљР С•Р В»РЎРЉР С”Р С• Р ВµРЎРѓР В»Р С‘ Р СРЎвЂ№ Р Р…Р Вµ Р Р† Р С‘Р С–РЎР‚Р С•Р Р†Р С•Р С Р С‘Р Р…РЎвЂљР ВµРЎР‚РЎвЂћР ВµР в„–РЎРѓР Вµ
  if (!defined('POKE_GAME_UI')) {
    $alm = formatnum(coolseitems(2,$_SESSION['id']));
    print 'Р СџРЎР‚Р С‘Р Р†Р ВµРЎвЂљРЎРѓРЎвЂљР Р†РЎС“Р ВµР С Р вЂ™Р В°РЎРѓ, РЎвЂљРЎР‚Р ВµР Р…Р ВµРЎР‚: '.$_SESSION['login'].'!
    |  <button type="button" class="btInp" onclick="window.open(\'/game.php?go=map\',\'mir\');">Р вЂ™ Р СР С‘РЎР‚</button>
    | Р Р€ Р вЂ™Р В°РЎРѓ Р Р…Р В° РЎРѓРЎвЂЎР ВµРЎвЂљРЎС“:  <button type="button" class="btInp" onclick="location.href=\'/game.php?go=diamond_shop\';">'.($alm?$alm:'0').' -  Р С’Р В»Р СР В°Р В·Р С•Р Р†</button>
    | Р вЂ™РЎвЂ№ РЎРЏР Р†Р В»РЎРЏР ВµРЎвЂљР ВµРЎРѓРЎРЉ: '.textGroup(users_conect('groups')).'. 
    |  <button type="button" class="btInp" onclick="location.href=\'/index.php?go=exits\';">Р вЂ™РЎвЂ№РЎвЂ¦Р С•Р Т‘</button>';
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
                    <li class="top"><a href="/game.php?go=map" class="top_link" target="_blank"><span class="down" style="color:red">Р вЂ™ Р СР С‘РЎР‚!</span></a></li>
                    <li class="top"><a href="/game.php?go=pokemon" class="top_link"><span class="down">Р СџР С•Р С”Р ВµР СР С•Р Р…РЎвЂ№</span></a></li>
                    <li class="top"><a href="/game.php?go=sends" id="shop" class="top_link"><span class="down">Р СџР С•РЎвЂЎРЎвЂљР В°</span></a></li>                    
                    <li class="top"><a href="#" id="shop" class="top_link"><span class="down">Р СћРЎР‚Р ВµР Р…Р ВµРЎР‚РЎвЂ№</span></a>
                      <ul class="sub">
                        <li><a href="/game.php?go=users">Р РЋР С—Р С‘РЎРѓР С•Р С” Р СћРЎР‚Р ВµР Р…Р ВµРЎР‚Р С•Р Р†</a></li>
                        <li><a href="/game.php?go=clans">Р РЋР С—Р С‘РЎРѓР С•Р С” Р С™Р В»Р В°Р Р…Р С•Р Р†</a></li>
                      </ul>
                    </li>                   
                    <li class="top"><a href="javascript:" id="shop" class="top_link" onClick="window.open('/game.php?go=items','items','width=650,height=500,scrollbars=yes'); return true;"><span class="down">Р ВР Р…Р Р†Р ВµР Р…РЎвЂљР В°РЎР‚РЎРЉ</span></a></li>
                    <li class="top"><a href="/game.php?go=profile" id="shop" class="top_link"><span class="down">Р СџРЎР‚Р С•РЎвЂћР С‘Р В»РЎРЉ</span></a></li>
                    <li class="top"><a href="/game.php?go=diamond_shop" class="top_link"><span class="down" style="color:gold"><strong>Р В РЎвЂ№Р Р…Р С•Р С”</strong></span></a>
                     <ul class="sub">
                      <li><a href="game.php?go=diamond_shop">Р С’Р В»Р СР В°Р В·Р Р…РЎвЂ№Р в„– Р СљР В°Р С–Р В°Р В·Р С‘Р Р…</a></li>
                      <li><a href="game.php?go=pokerinok" target="_blank">Р В РЎвЂ№Р Р…Р С•Р С” Р СџР С•Р С”Р ВµР СР С•Р Р…Р С•Р Р†</a></li>
                      <li><a href="game.php?go=rinok" target="_blank">Р В РЎвЂ№Р Р…Р С•Р С” Р С’Р в„–РЎвЂљР ВµР СР С•Р Р†</a></li>
                     </ul>
                    </li>
                    <li class="top"><a href="" class="top_link" target="_blank"><span class="down"><strong>Р СџРЎР‚Р С•РЎвЂЎР ВµР Вµ</strong></span></a>
                      <ul class="sub">
                        <li><a href="http://forum.league-of-pokemons.ru/" target="_blank">Р В¤Р С•РЎР‚РЎС“Р С</a></li>
                        <li><a href=".." target="_blank">Р вЂњР В»Р В°Р Р†Р Р…Р В°РЎРЏ</a></li>
                        <li><a href="/index.php?go=exits">Р вЂ™РЎвЂ№РЎвЂ¦Р С•Р Т‘</a></li>
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
        <font color="brown"><b style="font-size:20;"> Р Р€Р Р†Р В°Р В¶Р В°Р ВµР СРЎвЂ№Р в„– Р С—Р С•Р В»РЎРЉР В·Р С•Р Р†Р В°РЎвЂљР ВµР В»РЎРЉ РЎРѓР В°Р в„–РЎвЂљР В°, Р Р…Р В° Р Р†Р В°РЎв‚¬Р ВµР С Р В±РЎР‚Р В°РЎС“Р В·Р ВµРЎР‚Р Вµ Р Р…Р Вµ Р Р†Р С”Р В»РЎР‹РЎвЂЎР ВµР Р…Р В° Р С•Р С—РЎвЂ Р С‘РЎРЏ РЎвЂЎРЎвЂљР ВµР Р…Р С‘РЎРЏ - "Javascript", Р Т‘Р В»РЎРЏ Р С”Р С•РЎР‚РЎР‚Р ВµР С”РЎвЂљР Р…Р С•Р в„– РЎР‚Р В°Р В±Р С•РЎвЂљРЎвЂ№ Р С‘ Р С•РЎвЂљР С•Р В±РЎР‚Р В°Р В¶Р ВµР Р…Р С‘РЎРЏ РЎРѓР В°Р в„–РЎвЂљР В°, Р Р†Р В°Р С Р С—Р С•РЎвЂљРЎР‚Р ВµР В±РЎС“Р ВµРЎвЂљРЎРѓРЎРЏ Р Р†Р С”Р В»РЎР‹РЎвЂЎР С‘РЎвЂљРЎРЉ РЎРЊРЎвЂљРЎС“ Р С•Р С—РЎвЂ Р С‘РЎР‹ Р Р† Р Р…Р В°РЎРѓРЎвЂљРЎР‚Р С•Р в„–Р С”Р В°РЎвЂ¦ РЎРѓР Р†Р С•Р ВµР С–Р С• Р В±РЎР‚Р В°РЎС“Р В·Р ВµРЎР‚Р В°, Р С‘Р В»Р С‘ Р С—Р ВµРЎР‚Р ВµРЎС“РЎРѓРЎвЂљР В°Р Р…Р С•Р Р†Р С‘РЎвЂљРЎРЉ Р ВµР С–Р С•. </b>
        </font><br>
      </noscript>
