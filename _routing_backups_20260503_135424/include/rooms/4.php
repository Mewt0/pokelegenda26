<?php 
$qx01 = quest_process(1, 3);
$q7   = isset_qest(7);
$qx07 = quest_process(7, 10);
$pers = false;

if (!empty($_GET['quest_npc']) && !empty($_GET['do'])) {
    if ($_GET['quest_npc'] == 7 && (empty($q7) || !empty($qx07))) {
        include("npc/npc_7.php");
    }
} else {
    if (!empty($_GET['do']) && $qx01) {
        if ($qx01) include("npc/startpoke.php");
        else die("<script>location.href='game.php?go=char';</script>"); 
    } else {
        $name = 'Дорога 1';
        $about = 'Слегка неудобная извилистая тропа.
                  По бокам от неё находятся обширные поля, на которых невооруженным глазом можно заметить множество летающих покемонов. 
                  Где-то вдалеке виднеются старые деревья знаменитого Леса Вертании.';

        if ($qx01) {
            $pers = '<a href="/game.php?go=char&do=1" data-go="char" data-params="do=1">Осмотреться</a>';
        }
        if (!$qx01 && (!$q7 || $qx07)) {
            $pers .= ($pers ? ' | ' : '') . '<a href="/game.php?go=char&quest_npc=7&do=1" data-go="char" data-params="quest_npc=7&do=1">Коллекционер Билли</a>';
        }
        if (!$pers) $pers = '...';

        // переходы по локациям — без target, с data-* для SPA
        $move  = '<a href="/game.php?go=charWork&loc=1"  data-go="charWork" data-params="loc=1">Алабастия</a> | ';
        $move .= '<a href="/game.php?go=charWork&loc=5"  data-go="charWork" data-params="loc=5">Лес Вертании</a> | ';
        $move .= '<a href="/game.php?go=charWork&loc=10" data-go="charWork" data-params="loc=10">Дорога 2</a>';

        $img_r = '<img src="img/room/004.png" width="290" height="150">';
    }
}
?>
<script type="text/javascript"> (function (w, d) { function ajaxGET(url, cb){var x=new XMLHttpRequest();x.open('GET',url,true); try{x.setRequestHeader('X-Requested-With','XMLHttpRequest');}catch(e){} x.onreadystatechange=function(){if(x.readyState===4){var r=null;try{r=JSON.parse(x.responseText);}catch(e){}cb(r,x.status);}}; x.send(null); } function sameOrigin(url){var a=d.createElement('a');a.href=url;return a.host===w.location.host;} function quickGo(href){ w.location.replace(href); } d.addEventListener('click', function(e){ if (e.defaultPrevented || e.button!==0 || e.metaKey||e.ctrlKey||e.shiftKey||e.altKey) return; var a=e.target; while(a && a.tagName!=='A') a=a.parentNode; if(!a||!a.getAttribute) return; var href=a.getAttribute('href')||''; if(!href) return; if(!sameOrigin(href)) return; if (!/\/game\.php\?/.test(href)) return; if (/\bgo=charWork\b/i.test(href)) { e.preventDefault ? e.preventDefault() : (e.returnValue=false); var url = href + (href.indexOf('?')>-1?'&':'?') + 'ajax=1'; ajaxGET(url, function(r){ if (r && r.ok && r.redirect) quickGo(r.redirect); else quickGo('/game.php?go=map'); }); return; } if (/\bgo=map\b/i.test(href) && (/\bgets=/.test(href) || /\bnapadenie=/.test(href))) { e.preventDefault ? e.preventDefault() : (e.returnValue=false); var url2 = href + (href.indexOf('?')>-1?'&':'?') + 'ajax=1'; ajaxGET(url2, function(r){ quickGo('/game.php?go=map'); }); return; } }, true); var _orig_loc = w.loc; function _loc(u){ if (typeof u!=='string'){ if(_orig_loc) try{_orig_loc(u);}catch(e){} return; } if (/^charWork(\b|&)/i.test(u) || /\bgo=charWork\b/i.test(u)){ var full = /^go=/.test(u) ? ('/game.php?'+u) : ('/game.php?go='+u); var url = full + (full.indexOf('?')>-1?'&':'?') + 'ajax=1'; ajaxGET(url, function(r){ if(r&&r.ok&&r.redirect) quickGo(r.redirect); else quickGo('/game.php?go=map'); }); return; } quickGo(/^go=/.test(u)?('/game.php?'+u):('/game.php?go='+u)); } w.loc = _loc; try{ if(w.parent) w.parent.loc = _loc; }catch(e){} try{ if(w.top) w.top.loc=_loc; }catch(e){} })(window, document); </script>
