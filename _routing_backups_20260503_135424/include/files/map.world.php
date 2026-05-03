<?php
// === старые обработчики оставляем как есть ===
if(isset($_GET['post']) && !empty($_POST)){
  require_once ("include/function/post.map.php");
  die();
}
if(isset($_GET['gets']) && !empty($_GET)){
  require_once ("include/function/get.map.php");
  die();
}

// Шапки кэша как защита от залипания
@header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
@header('Pragma: no-cache');
@header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

// Если этот файл может грузиться как «фрагмент» через AJAX, пометим
$__isFragment = defined('AJAX_FRAGMENT');
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>League Of Pokemons -> Игровой мир</title>
  <meta name="description" content="Онлайн игра про покемонов">
  <meta name="keywords" content="Игра онлайн, покемоны онлайн, покемоны, игра про покемонов, онлайн игра про покемонов">
  <meta http-equiv="pragma" content="no-cache">
  <link rel="stylesheet" href="/css/style.css" type="text/css">
  <script type="text/javascript" src="/script/jquery.js"></script>
  <script type="text/javascript" src="/script/textJs.js"></script>
  <script type="text/javascript" src="/script/map.js"></script>
  <style>
    /* Каркас вместо фреймов:
       - верх: инфо/персонаж (54% высоты)
       - середина: слева чат-контейнер (если чат не fixed), справа онлайн (240px)
       - низ: 50px место под кнопки (если кнопки fixed — просто отступ)
       Примечание: мой новый chat.world.php и buttons.world.php рисуют fixed-доки снизу;
       поэтому тут оставляем им место, чтобы не перекрывали контент. */

    html, body { height:100%; margin:0; padding:0; }
    .world-wrap {
      position:relative; min-height:100vh; background:#f5f5f5;
      padding-bottom: 56px; /* запас под кнопочный док, если он фиксированный */
    }
    .row-top {
      height:54vh; overflow:auto; border-bottom:1px solid #cfd6df; background:#fff;
    }
    .row-mid {
      height:calc(100vh - 54vh - 56px); /* остаток минус док снизу */
      display:grid; grid-template-columns: 1fr 240px; gap:0;
    }
    .col-chat { overflow:auto; background:#fff; border-right:1px solid #cfd6df; }
    .col-users{ overflow:auto; background:#f9fbff; }
    /* если чат и кнопки fixed-снизу (из их файлов), резервируем визуально место */
    .reserve-bottom { height:50px; }
  </style>
</head>
<body>

<div class="world-wrap" id="worldWrap">
  <!-- Верхняя зона (54%): персонаж/локация -->
  <div class="row-top" id="rowTop">
    <?php
    // как и было во фреймах в первой строке – грузили /game.php?go=char
    // здесь просто инклюдим тот же файл
    require_once("include/files/char.world.php");
    ?>
  </div>

  <!-- Средняя зона: слева чат (если он не fixed), справа – список игроков на локации -->
  <div class="row-mid">
    <div class="col-chat" id="colChat">
      <?php
      // Если используешь новый чат с фиксированным доком снизу (chatDock),
      // его можно инклюдить здесь – он сам прижмётся вниз, а эта колонка остаётся пустой.
      // Если старый чат «в потоке», он отрисуется тут.
      require_once("include/files/chat.world.php");
      ?>
    </div>
    <div class="col-users" id="colUsers">
      <?php require_once("include/files/mapusers.world.php"); ?>
    </div>
  </div>

  <!-- Резерв под док-кнопки (если они fixed) -->
  <div class="reserve-bottom"></div>
</div>

<?php
// Нижняя панель действий (иконки). Если используешь мой новый buttons.world.php,
// он рисует fixed-док снизу (z-index), так что можно включить один раз тут:
require_once("include/files/buttons.world.php");
?>

<script type="text/javascript">
  // Небольшой помощник: если чат/кнопки у тебя fixed-снизу, никаких авто-скроллов тут не нужно.
  // Если же используешь чат «в потоке», можно слегка подправить высоты, когда окно меняется.

  (function(){
    function adjust(){
      // ничего сложного – высоты заданы в vh, хватит по дефолту
    }
    window.addEventListener('resize', adjust, false);
    adjust();
  })();

  (function(w, d){
    function sameOrigin(url){
      var a = d.createElement('a');
      a.href = url;
      return a.host === w.location.host;
    }

    function ajaxJson(url, cb){
      var x = new XMLHttpRequest();
      x.open('GET', url, true);
      try { x.setRequestHeader('X-Requested-With', 'XMLHttpRequest'); } catch(e) {}
      x.onreadystatechange = function(){
        if (x.readyState !== 4) return;
        var data = null;
        try { data = JSON.parse(x.responseText || '{}'); } catch(e) {}
        cb(data, x.status);
      };
      x.send(null);
    }

    function loadInto(id, url){
      var el = d.getElementById(id);
      if (!el) return;
      if (w.jQuery) {
        w.jQuery(el).load(url);
        return;
      }
      var x = new XMLHttpRequest();
      x.open('GET', url, true);
      x.onreadystatechange = function(){
        if (x.readyState === 4 && x.status >= 200 && x.status < 400) {
          el.innerHTML = x.responseText;
        }
      };
      x.send(null);
    }

    function refreshWorld(){
      loadInto('rowTop', '/game.php?go=char&ajax=1');
      loadInto('colUsers', '/game.php?go=mapusers&ajax=1');
      loadInto('colChat', '/game.php?go=chat&ajax=1');
    }

    function showError(message){
      if (!message) return;
      if (typeof w.mess_error === 'function') w.mess_error(message, 'block');
      else alert(message);
    }

    function goRoute(route){
      if (!route) return;
      if (route.indexOf('game.php?') === 0 || route.indexOf('/game.php?') === 0) {
        w.location.href = route;
        return;
      }
      if (route.indexOf('char') === 0) {
        loadInto('rowTop', '/game.php?go=' + route);
        return;
      }
      w.location.href = '/game.php?go=' + route;
    }

    function handleCharWork(href){
      var url = href + (href.indexOf('?') > -1 ? '&' : '?') + 'ajax=1';
      ajaxJson(url, function(r){
        if (r && r.ok) {
          refreshWorld();
          if (r.chat && typeof w.mess_chat === 'function') w.mess_chat(r.chat);
          return;
        }
        showError(r && r.message ? r.message : 'Переход не выполнен.');
        refreshWorld();
      });
    }

    d.addEventListener('click', function(e){
      if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
      var a = e.target;
      while (a && a.tagName !== 'A') a = a.parentNode;
      if (!a || !a.getAttribute) return;
      var href = a.getAttribute('href') || '';
      if (!href || !sameOrigin(href)) return;
      if (!/\bgo=charWork\b/i.test(href)) return;
      e.preventDefault ? e.preventDefault() : (e.returnValue = false);
      handleCharWork(href);
    }, true);

    w.loc = function(route){
      if (typeof route !== 'string') return;
      if (/^charWork(\b|&)/i.test(route)) {
        handleCharWork('/game.php?go=' + route);
        return;
      }
      goRoute(route);
    };
    w._location = {
      location: {
        set href(url){ loadInto('rowTop', url); },
        get href(){ return ''; }
      }
    };
    w._chat_two = {
      location: {
        set href(url){
          if (/\bgo=charWork\b/i.test(url)) handleCharWork(url);
          else loadInto('rowTop', url);
        },
        get href(){ return ''; }
      }
    };
  })(window, document);
</script>

</body>
</html>
