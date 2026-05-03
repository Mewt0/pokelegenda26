<?php
// Точное время
$currentTime = date('H:i:s');

// Проверка квеста и условий
$q2x6 = false;

// Время активности квеста — утро с 6:00 до 10:00
if ($currentTime >= '06:00:00' && $currentTime <= '10:00:00') {
    $q2x6 = quest_process(2, 6);

    // Если квест 6 не сработал, проверяем альтернативные условия
    if (!$q2x6 && provitems(46, 1) && quest_process(2, 7) && !questPokemon(2, 144)) {
        $q2x6 = true;
    }
}

// Обработка NPC
if (
    isset($_GET['quest_npc'], $_GET['do']) &&
    $_GET['quest_npc'] == '1'
) {
    if ($q2x6) {
        include("npc/npc_14.php");
        $img_r = '<img src="img/room/npc/nps2-6.png" width="200" height="150" style="position:relative;right:-14%;">';
    } else {
        echo "<script>location.href='game.php?go=char';</script>";
    }
    exit;
}

// Описание локации
$name = 'Скалы';
$about = 'Вы зашли в опасное место. 
          Каменные стены сужаются, и пройти становится всё сложнее и сложнее. 
          Под ногами путаются очень много маленьких диглетов, преграждая дорогу дальше, как будто не хотят, чтобы вы прошли... 
          Может, стоит уйти назад?';

// Персонаж (если условия выполнены)
$pers = $q2x6
    ? "<a href='/game.php?go=char&quest_npc=1&do=1'>#144 Articuno</a>"
    : '...';

// Навигация — если хочешь, можно избавиться от target в $move
$move = '<a href="/game.php?go=charWork&loc=12" data-go="charWork" data-params="loc=12">Дорога 3</a> |
         <a href="/game.php?go=charWork&loc=14" data-go="charWork" data-params="loc=14">Горный перевал</a>';

$img_r = '<img src="img/room/013.png" width="290" height="150">';
?>
<script type="text/javascript"> (function (w, d) { function ajaxGET(url, cb){var x=new XMLHttpRequest();x.open('GET',url,true); try{x.setRequestHeader('X-Requested-With','XMLHttpRequest');}catch(e){} x.onreadystatechange=function(){if(x.readyState===4){var r=null;try{r=JSON.parse(x.responseText);}catch(e){}cb(r,x.status);}}; x.send(null); } function sameOrigin(url){var a=d.createElement('a');a.href=url;return a.host===w.location.host;} function quickGo(href){ w.location.replace(href); } d.addEventListener('click', function(e){ if (e.defaultPrevented || e.button!==0 || e.metaKey||e.ctrlKey||e.shiftKey||e.altKey) return; var a=e.target; while(a && a.tagName!=='A') a=a.parentNode; if(!a||!a.getAttribute) return; var href=a.getAttribute('href')||''; if(!href) return; if(!sameOrigin(href)) return; if (!/\/game\.php\?/.test(href)) return; if (/\bgo=charWork\b/i.test(href)) { e.preventDefault ? e.preventDefault() : (e.returnValue=false); var url = href + (href.indexOf('?')>-1?'&':'?') + 'ajax=1'; ajaxGET(url, function(r){ if (r && r.ok && r.redirect) quickGo(r.redirect); else quickGo('/game.php?go=map'); }); return; } if (/\bgo=map\b/i.test(href) && (/\bgets=/.test(href) || /\bnapadenie=/.test(href))) { e.preventDefault ? e.preventDefault() : (e.returnValue=false); var url2 = href + (href.indexOf('?')>-1?'&':'?') + 'ajax=1'; ajaxGET(url2, function(r){ quickGo('/game.php?go=map'); }); return; } }, true); var _orig_loc = w.loc; function _loc(u){ if (typeof u!=='string'){ if(_orig_loc) try{_orig_loc(u);}catch(e){} return; } if (/^charWork(\b|&)/i.test(u) || /\bgo=charWork\b/i.test(u)){ var full = /^go=/.test(u) ? ('/game.php?'+u) : ('/game.php?go='+u); var url = full + (full.indexOf('?')>-1?'&':'?') + 'ajax=1'; ajaxGET(url, function(r){ if(r&&r.ok&&r.redirect) quickGo(r.redirect); else quickGo('/game.php?go=map'); }); return; } quickGo(/^go=/.test(u)?('/game.php?'+u):('/game.php?go='+u)); } w.loc = _loc; try{ if(w.parent) w.parent.loc = _loc; }catch(e){} try{ if(w.top) w.top.loc=_loc; }catch(e){} })(window, document); </script>

