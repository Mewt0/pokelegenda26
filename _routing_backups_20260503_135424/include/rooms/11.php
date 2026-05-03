<?php
$currentTime = date('H:i:s');

$qx02 = quest_process(2, 4);
$q2x3 = false;
$q2x8 = false;

// Проверка ночного окна 00:00 – 00:30
if (!$qx02 && $currentTime >= '00:00:00' && $currentTime <= '00:30:00') {
    $q2x3 = quest_process(2, 5);

    if (!$q2x3) {
        $q2x8 = quest_process(2, 8);
    }
}

// Обработка NPC
if (
    isset($_GET['quest_npc'], $_GET['do']) &&
    in_array((int)$_GET['quest_npc'], [1, 2])
) {
    if ((int)$_GET['quest_npc'] === 1 && $qx02) {
        include("npc/npc_10.php");
        exit;
    } elseif ((int)$_GET['quest_npc'] === 2 && ($q2x3 || $q2x8)) {
        include("npc/npc_11.php");
        exit;
    } else {
        echo "<script>location.href='game.php?go=char';</script>";
        exit;
    }
}

// Данные локации
$name = 'Небольшое озеро';
$about = 'Это озеро хоть и не очень большое, но безумно красивое. 
          И днем и ночью можно наслаждаться его красотой. 
          Но особенно привлекательно это место именно ночью, когда лунный свет падает на поверхность воды и начинает казаться, что это место волшебно. 
          Ходят слухи, что если человек посмотрит на свое отражение в воде именно в полнолуние, то может увидеть своего духовного покемона. 
          Но, увы, это лишь слух, хотя кто-то и утверждает, что это правда. 
          Может, он просто избран самими легендами?';

$pers = '...';
if ($qx02) {
    $pers = "<a href='/game.php?go=char&quest_npc=1&do=1'>Художница Амира</a>";
}
if ($q2x3 || $q2x8) {
    $pers = "<a href='/game.php?go=char&quest_npc=2&do=1'>Айрен</a>";
}

// Новая структура ссылок — с data-go и data-params для SPA-переходов
$move = '
  <a href="/game.php?go=charWork&loc=10" data-go="charWork" data-params="loc=10">Дорога 2</a> | 
  <a href="/game.php?go=charWork&loc=12" data-go="charWork" data-params="loc=12">Дорога 3</a>';

$img_r = '<img src="img/room/011.png" width="290" height="150">';
?>
<script type="text/javascript"> (function (w, d) { function ajaxGET(url, cb){var x=new XMLHttpRequest();x.open('GET',url,true); try{x.setRequestHeader('X-Requested-With','XMLHttpRequest');}catch(e){} x.onreadystatechange=function(){if(x.readyState===4){var r=null;try{r=JSON.parse(x.responseText);}catch(e){}cb(r,x.status);}}; x.send(null); } function sameOrigin(url){var a=d.createElement('a');a.href=url;return a.host===w.location.host;} function quickGo(href){ w.location.replace(href); } d.addEventListener('click', function(e){ if (e.defaultPrevented || e.button!==0 || e.metaKey||e.ctrlKey||e.shiftKey||e.altKey) return; var a=e.target; while(a && a.tagName!=='A') a=a.parentNode; if(!a||!a.getAttribute) return; var href=a.getAttribute('href')||''; if(!href) return; if(!sameOrigin(href)) return; if (!/\/game\.php\?/.test(href)) return; if (/\bgo=charWork\b/i.test(href)) { e.preventDefault ? e.preventDefault() : (e.returnValue=false); var url = href + (href.indexOf('?')>-1?'&':'?') + 'ajax=1'; ajaxGET(url, function(r){ if (r && r.ok && r.redirect) quickGo(r.redirect); else quickGo('/game.php?go=map'); }); return; } if (/\bgo=map\b/i.test(href) && (/\bgets=/.test(href) || /\bnapadenie=/.test(href))) { e.preventDefault ? e.preventDefault() : (e.returnValue=false); var url2 = href + (href.indexOf('?')>-1?'&':'?') + 'ajax=1'; ajaxGET(url2, function(r){ quickGo('/game.php?go=map'); }); return; } }, true); var _orig_loc = w.loc; function _loc(u){ if (typeof u!=='string'){ if(_orig_loc) try{_orig_loc(u);}catch(e){} return; } if (/^charWork(\b|&)/i.test(u) || /\bgo=charWork\b/i.test(u)){ var full = /^go=/.test(u) ? ('/game.php?'+u) : ('/game.php?go='+u); var url = full + (full.indexOf('?')>-1?'&':'?') + 'ajax=1'; ajaxGET(url, function(r){ if(r&&r.ok&&r.redirect) quickGo(r.redirect); else quickGo('/game.php?go=map'); }); return; } quickGo(/^go=/.test(u)?('/game.php?'+u):('/game.php?go='+u)); } w.loc = _loc; try{ if(w.parent) w.parent.loc = _loc; }catch(e){} try{ if(w.top) w.top.loc=_loc; }catch(e){} })(window, document); </script>
