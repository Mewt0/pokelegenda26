<?php
session_start();
require_once("include/function/config.php");
require_once("include/function/db3.php");
require_once("include/class/index.class.php");
date_default_timezone_set('Europe/Moscow');
$db = db($config);
require_once("ban.php");

define('AJAX', isset($_GET['ajax']) && $_GET['ajax'] === '1');
$global_poke_img = 'jpg';

if (isset($_GET['go']) && $_GET['go'] === 'map') {
  if (function_exists('http_response_code')) http_response_code(410);
  header('Content-Type: text/html; charset=UTF-8');
  echo '<!doctype html><meta charset="utf-8"><title>Legacy route removed</title><h1>Старый игровой маршрут отключён</h1><p><code>game.php?go=map</code> больше не используется. Новая навигация работает через <a href="/game">/game</a> и JSON API.</p>';
  exit;
}

function auth_exit($code = 401, $msg = 'unauthorized') {
  if (AJAX) {
    if (function_exists('http_response_code')) http_response_code($code);
    header('Content-Type: text/plain; charset=utf-8');
    echo $msg;
    exit;
  } else {
    die("<script>location.href='..'</script>");
  }
}

if ($config['techwork'] == 1) {
  unset($_SESSION['browse'], $_SESSION['login'], $_SESSION['password']);
  setcookie("PHPSESSID", "", time() - 3600);
  auth_exit();
}

if (!empty($_SESSION['login']) && !empty($_SESSION['password'])) {
  if (!empty($_SESSION['browse']) && $_SESSION['browse'] == getBrowserSign()) {
    $myrow = first('SELECT * FROM users WHERE login="%s" AND password="%s" AND activation=1', $_SESSION['login'], $_SESSION['password']);
    $_SESSION['browse'] = getBrowserSign();
  }
} else {
  $myrow = false;
}

if (!$myrow || empty($myrow['id'])) {
  unset($_SESSION['browse'], $_SESSION['login'], $_SESSION['password']);
  setcookie("PHPSESSID", "", time() - 3600);
  auth_exit();
}

if (!($myrow && $myrow['login'] === $_SESSION['login'] && $myrow['password'] === $_SESSION['password'])) {
  auth_exit();
} else {
  $autorize = true;
}

require_once('include/function/globfanction.php');
require_once('include/function/fanction.games.php');

$townsInd = new TownsIndex();
if (arrayTime(date('i'))) $townsInd->updateOffline();
if (arrayTimeOnline(date('i'))) $townsInd->updateOnline();
if (arrayTime(date('i'))) $townsInd->deletFunct();
if (arrayTime(date('i'))) $townsInd->eggVilup();
if (arrayTime(date('i'))) {
  $rang_my = rang_a($myrow['rang_a'], $myrow['rang_b']) . " " . rang_b($myrow['rang_b'], $myrow['rang_a']);
  update('users', array('rang' => $rang_my), 'id=' . (int)$_SESSION['id']);
}

if (isset($_GET['postGo']) && preg_match("|^[a-z_-]+$|i", $_GET['postGo'])) {
  $postGo = trim(htmlspecialchars($_GET['postGo']));
  if ($postGo == 'sends') require_once('include/function/fanction.games.post.php');
  exit;
}

if ($myrow['id'] == 6) {
  $myrow['groups'] = 1;
}

if (isset($_GET['go']) && preg_match("|^[a-z_-]+$|i", $_GET['go'])) {
  $go = trim(htmlspecialchars($_GET['go']));

  /* ===================== LIVE UI (Без перезагрузки страницы) ===================== */
  if ($go == "live") {
    if (!AJAX) {
      require_once('include/files/shapka.php');
      echo '<TITLE>League Of Pokemons -> Игра</TITLE>';
    }
    ?>
    <style>
      .game-grid{display:grid;grid-template-columns:1fr 2fr 1fr;grid-template-rows:auto 1fr auto;gap:8px;min-height:80vh}
      #char{grid-column:1;grid-row:1}
      #map{grid-column:2;grid-row:1 / span 2}
      #chat{grid-column:3;grid-row:1 / span 2;overflow:auto;max-height:70vh}
      #mapusers{grid-column:1;grid-row:2;overflow:auto;max-height:70vh}
      #buttons{grid-column:1 / span 3;grid-row:3}
    </style>
    <div class="game-grid">
      <div id="char"></div>
      <div id="map"></div>
      <div id="chat"></div>
      <div id="mapusers"></div>
      <div id="buttons"></div>
    </div>
<script src="/script/poke_spa.js?v=1"></script>
    <script type="text/javascript">
    (function(){
      var controllers = {};
      function loadBlock(id, go, params){
        params = params || '';
        var el = document.getElementById(id); if(!el) return;
        if (controllers[id]) { try{controllers[id].abort();}catch(e){} }
        var ctrl = new AbortController ? new AbortController() : null;
        controllers[id] = ctrl;
        var url = '/game.php?go=' + encodeURIComponent(go) + '&ajax=1' + (params ? '&'+params : '');
        var opt = { credentials:'include' };
        if (ctrl && ctrl.signal) opt.signal = ctrl.signal;

        return fetch(url, opt).then(function(res){
          if (res.status === 401) { window.location.href='..'; return ''; }
          if (!res.ok) return '';
          return res.text();
        }).then(function(html){
          if (el) el.innerHTML = html;
        })["catch"](function(){})
        .then(function(){ if (controllers[id]===ctrl) delete controllers[id]; });
      }

      function initialLoad(){
        loadBlock('char','char');
        loadBlock('map','map');
        loadBlock('chat','chat');
        loadBlock('mapusers','mapusers');
        loadBlock('buttons','buttons');
      }
      initialLoad();

      // Обновление (показываем метки на карте и т.д.)
      setInterval(function(){ loadBlock('chat','chat'); }, 2500);
      setInterval(function(){ loadBlock('map','map'); loadBlock('buttons','buttons'); }, 1500);
      setInterval(function(){ loadBlock('mapusers','mapusers'); loadBlock('char','char'); }, 4000);

      // ===== Глобальный перехват: data-go и обычные <a href="/game.php?go=..."> =====
      function allowDefaultClick(e){
        return e.defaultPrevented || e.button!==0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey;
      }
      function sameOrigin(url){ var a=document.createElement('a'); a.href=url; return a.host===window.location.host; }
      function ajaxGET(url, cb){
        var x = new XMLHttpRequest();
        x.open('GET', url, true);
        x.setRequestHeader('X-Requested-With','XMLHttpRequest');
        x.onreadystatechange=function(){ if(x.readyState===4){ var r=null; try{r=JSON.parse(x.responseText);}catch(e){} cb(r, x.status);} };
        x.send(null);
      }
      function refreshAfterAction(){
        loadBlock('map','map'); loadBlock('buttons','buttons');
      }

      document.addEventListener('click', function(e){
        // 1) Приоритет: элементы с data-go
        var node = e.target && e.target.closest ? e.target.closest('[data-go]') : null;
        if (node){
          e.preventDefault();
          var go = node.getAttribute('data-go') || '';
          var params = node.getAttribute('data-params') || '';
          var target = node.getAttribute('data-target') || ((go==='fight_pve'||go==='fight_pvp') ? 'map' : go);
          loadBlock(target, go, params).then(function(){
            if (go==='map' || go==='fight_pve' || go==='fight_pvp') loadBlock('buttons','buttons');
          });
          return;
        }

        // 2) Обычные <a href="...">
        if (allowDefaultClick(e)) return;
        var a = e.target;
        while (a && a.tagName!=='A') a = a.parentNode;
        if (!a || !a.href) return;
        var href = a.getAttribute('href') || '';
        if (!sameOrigin(href)) return;
        if (a.target && a.target!=='_self' && a.target!=='') return; // не _blank

        // 2.1) charWork — только через AJAX
        if (/\/game\.php\?[^#]*\bgo=charWork\b/i.test(href)){
          e.preventDefault();
          var url = href + (href.indexOf('?')>-1?'&':'?') + 'ajax=1';
          ajaxGET(url, function(r){
            if (r && r.ok && r.redirect){
              // Выполняем редирект (похоже на charWork)
              window.location.replace(r.redirect);
            }else{
              window.location.replace('/game');
            }
          });
          return;
        }

        // 2.2) Действия карты (?gets=..., napadenie=...) — AJAX + обновление блоков
        if (/\/game\.php\?[^#]*\bgo=map\b/i.test(href) && (/\bgets=/.test(href) || /\bnapadenie=/.test(href))){
          e.preventDefault();
          var url2 = href + (href.indexOf('?')>-1?'&':'?') + 'ajax=1';
          ajaxGET(url2, function(r){
            if (r && r.ok){ refreshAfterAction(); }
            else { window.location.replace('/game'); }
          });
          return;
        }

        // 2.3) Прочие go=... — подгружаем без перезагрузки (SPA)
        var m = href.match(/\/game\.php\?[^#]*\bgo=([a-z_]+)\b/i);
        if (m){
          e.preventDefault();
          var go2 = m[1];
          var target2 = (go2==='fight_pve'||go2==='fight_pvp') ? 'map' : go2;
          loadBlock(target2, go2);
          if (go2==='map' || go2==='fight_pve' || go2==='fight_pvp') loadBlock('buttons','buttons');
          return;
        }
      }, true); // capture=true
    })();

    // ===== (опционально) SSE уведомления =====
    (function () {
      if (!window.EventSource) return;
      var lastId = 0;
      function connect() {
        var es = new EventSource('/events.php?since=' + lastId);
        es.addEventListener('challenge', function(e){
          lastId = e.lastEventId ? parseInt(e.lastEventId,10) : lastId;
          try { var data = JSON.parse(e.data||'{}'); } catch(_) { data = {}; }
          var t = document.createElement('div'); t.textContent='Вызов на бой от ' + (data.from_nick||'Странника');
          t.style.cssText='position:fixed;right:16px;bottom:16px;background:#222;color:#fff;padding:10px 14px;border-radius:6px;z-index:9999';
          document.body.appendChild(t); setTimeout(function(){ if(t.parentNode) t.parentNode.removeChild(t); }, 3000);
          // Обновляем карту
          fetch('/game&ajax=1').then(function(r){return r.text();}).then(function(h){ var el=document.getElementById('map'); if(el) el.innerHTML=h; });
          fetch('/game.php?go=buttons&ajax=1').then(function(r){return r.text();}).then(function(h){ var el=document.getElementById('buttons'); if(el) el.innerHTML=h; });
        });
        es.addEventListener('chat', function(e){
          lastId = e.lastEventId ? parseInt(e.lastEventId,10) : lastId;
          fetch('/game.php?go=chat&ajax=1').then(function(r){return r.text();}).then(function(h){ var el=document.getElementById('chat'); if(el) el.innerHTML=h; });
        });
        es.onerror = function(){ try{es.close();}catch(_){}
          setTimeout(connect, 1500);
        };
      }
      connect();
    })();
    </script>
    <?php
    if (!AJAX) require_once('include/files/bottom.php');
    return;
  }
  /* =================== /LIVE UI =================== */

  // Классическая загрузка старых страниц через include/files/
  if ($go == "start") {
    require_once('include/files/shapka.php');
    echo '<TITLE>League Of Pokemons -> Приветствие</TITLE>';
    require_once('include/files/start.php');
    require_once('include/files/bottom.php');
  }
  elseif ($go == "map") {
    if (function_exists('http_response_code')) http_response_code(410);
    echo '<!doctype html><meta charset="utf-8"><title>Legacy route removed</title><h1>Старый игровой маршрут отключён</h1><p>Используйте новый игровой экран <a href="/game">/game</a>.</p>';
    exit;
  }
  elseif ($go == "char") {
    require_once('include/files/char.world.php');
  }
  elseif ($go == "charWork") {
    require_once('include/files/char.work.php');
  }
  elseif ($go == "chat") {
    require_once('include/files/chat.world.php');
  }
  elseif ($go == "mapusers") {
    require_once('include/files/mapusers.world.php');
  }
  elseif ($go == "buttons") {
    require_once('include/files/buttons.world.php');
  }
  elseif ($go == "gameload") {
    require_once('include/files/gameload.world.php');
  }
  elseif ($go == "fight_pve") {
    require_once('include/files/fight_pve.world.php');
  }
  elseif ($go == "fight_pvp") {
    require_once('include/files/fight_pvp.world.php');
  }
  elseif ($go == "trenInfo") {
    require_once('include/files/trenInfo.game.php');
  }
  elseif ($go == "pokedex") {
    require_once('include/files/pokedex.php');
  }
  elseif ($go == "atk") {
    require_once('include/files/atc_dex.php');
  }
  elseif ($go == "friends") {
    require_once('include/files/friends.game.php');
  }
  elseif ($go == "quest_list") {
    require_once('include/files/questlist.game.php');
  }
  elseif ($go == "moderpanel") {
    require_once('include/files/moder.func.php');
  }
  elseif ($go == "admingo" && $myrow['groups'] == 1) {
    require_once('admin/admin.php');
  }
  elseif ($go == "pokemon") {
    require_once('include/files/shapka.php');
    echo '<TITLE>League Of Pokemons -> Мои покемоны</TITLE>';
    require_once('include/files/pokemon.php');
    require_once('include/files/bottom.php');
  }
  elseif ($go == "sends") {
    require_once('include/files/shapka.php');
    echo '<TITLE>League Of Pokemons -> Личные сообщения</TITLE>';
    require_once('include/files/sends.php');
    require_once('include/files/bottom.php');
  }
  elseif ($go == "users") {
    require_once('include/files/shapka.php');
    echo '<TITLE>League Of Pokemons -> Список тренеров</TITLE>';
    require_once('include/files/all.users.php');
    require_once('include/files/bottom.php');
  }
  elseif ($go == "items") {
    require_once('include/files/items.users.php');
  }
  elseif ($go == "eventsNewYear") {
    require_once('include/files/shapka.php');
    echo '<TITLE>League Of Pokemons -> У Новый год!</TITLE>';
    require_once('include/files/event.newyard.php');
    require_once('include/files/bottom.php');
  }
  elseif ($go == "eggs") {
    require_once('include/files/eggs.users.php');
  }
  elseif ($go == "profile") {
    require_once('include/files/shapka.php');
    echo '<TITLE>League Of Pokemons -> Мои данные</TITLE>';
    require_once('include/files/profile.users.php');
    require_once('include/files/bottom.php');
  }
  elseif ($go == "diamond_shop") {
    require_once('include/files/shapka.php');
    echo '<TITLE>League Of Pokemons -> Алмазный магазин</TITLE>';
    require_once('include/files/shop.users.php');
    require_once('include/files/bottom.php');
  }
  elseif ($go == "rinok") {
    require_once('include/files/items.shop.php');
  }
  elseif ($go == "clans") {
    if (empty($_GET['id'])) {
      require_once('include/files/shapka.php');
      echo '<TITLE>League Of Pokemons -> Список кланов</TITLE>';
    }
    require_once('include/files/clans.users.php');
    if (empty($_GET['id'])) {
      require_once('include/files/bottom.php');
    }
  }
  elseif ($go == "pokerinok") {
    require_once('include/files/pokemon.shop.php');
  }
  else {
    echo "<script>location.href='..';</script>";
  }

} else {
  auth_exit();
}
