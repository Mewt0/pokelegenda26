<?php
// Р СџР С•Р Т‘Р Т‘Р ВµРЎР‚Р В¶Р С”Р В° PHP 5.4: Р Р…Р ВµРЎвЂљ short arrays, Р Р…Р ВµРЎвЂљ ??

if (!empty($_GET['npc']) && !empty($_GET['do'])) {
    if ($_GET['npc'] == '1') {
        include('npc/npc_8.php');
    } else {
        echo "<script>location.href='game.php?go=char';</script>";
    }
    exit;
}

// Р С›Р С—Р С‘РЎРѓР В°Р Р…Р С‘Р Вµ Р В»Р С•Р С”Р В°РЎвЂ Р С‘Р С‘
$name = 'Р вЂ”Р Т‘Р В°Р Р…Р С‘Р Вµ Р С’Р Т‘Р СР С‘Р Р…Р С‘РЎРѓРЎвЂљРЎР‚Р В°РЎвЂ Р С‘Р С‘';
$about = 'Р вЂ™РЎвЂ№ Р Р†Р С•РЎв‚¬Р В»Р С‘ Р Р† Р В·Р Т‘Р В°Р Р…Р С‘Р Вµ, Р С–Р Т‘Р Вµ Р Т‘Р С•Р Р†Р С•Р В»РЎРЉР Р…Р С• РЎвЂЎР В°РЎРѓРЎвЂљР С• Р СР С•Р В¶Р Р…Р С• Р В·Р В°РЎРѓРЎвЂљР В°РЎвЂљРЎРЉ Р С’Р Т‘Р СР С‘Р Р…Р С‘РЎРѓРЎвЂљРЎР‚Р В°РЎвЂљР С•РЎР‚Р С•Р Р†. 
          Р ВР В·РЎвЂ№РЎРѓР С”Р В°Р Р…Р Р…Р С• РЎС“Р С”РЎР‚Р В°РЎв‚¬Р ВµР Р…Р Р…РЎвЂ№Р Вµ Р С”Р С•РЎР‚Р С‘Р Т‘Р С•РЎР‚РЎвЂ№ РЎвЂљРЎвЂ°Р В°РЎвЂљР ВµР В»РЎРЉР Р…Р С• Р С•РЎвЂ¦РЎР‚Р В°Р Р…РЎРЏРЎР‹РЎвЂљРЎРѓРЎРЏ РЎРѓР С—Р ВµРЎвЂ Р С‘Р В°Р В»РЎРЉР Р…Р С• Р С•Р В±РЎС“РЎвЂЎР ВµР Р…Р Р…РЎвЂ№Р СР С‘ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В°Р СР С‘, 
          Р ВµРЎРѓР В»Р С‘ Р С—РЎР‚Р С•Р в„–РЎвЂљР С‘ РЎвЂЎРЎС“РЎвЂљРЎРЉ Р Р†Р С—Р ВµРЎР‚Р ВµР Т‘, Р Р†РЎвЂ№ РЎС“Р Р†Р С‘Р Т‘Р С‘РЎвЂљР Вµ Р Т‘Р ВµР Р†РЎС“РЎв‚¬Р С”РЎС“, РЎС“Р Р†Р В»Р ВµРЎвЂЎР ВµР Р…Р Р…РЎС“РЎР‹ РЎРѓР Р†Р С•Р ВµР в„– Р С—Р С•Р Р†РЎРѓР ВµР Т‘Р Р…Р ВµР Р†Р Р…Р С•Р в„– РЎР‚Р В°Р В±Р С•РЎвЂљР С•Р в„–.';

$pers = '<a href="/game.php?go=char&npc=1&do=1">Р РЋР ВµР С”РЎР‚Р ВµРЎвЂљР В°РЎР‚РЎРЉ</a>';

// Р СњР В°Р Р†Р С‘Р С–Р В°РЎвЂ Р С‘РЎРЏ РІР‚вЂќ Р В±Р ВµР В· target="_chat_two", Р СР С•Р В¶Р Р…Р С• Р Т‘Р С•Р В±Р В°Р Р†Р С‘РЎвЂљРЎРЉ data-go Р Т‘Р В»РЎРЏ JS-Р С—Р ВµРЎР‚Р ВµРЎвЂ¦Р Р†Р В°РЎвЂљР В°
$move = '<a href="/game.php?go=charWork&loc=1" data-go="charWork" data-params="loc=1">Р С’Р В»Р В°Р В±Р В°РЎРѓРЎвЂљР С‘РЎРЏ</a>';

$img_r = '<img src="img/room/27.png" width="250" height="150">';
?>
<script type="text/javascript"> (function (w, d) { function ajaxGET(url, cb){var x=new XMLHttpRequest();x.open('GET',url,true); try{x.setRequestHeader('X-Requested-With','XMLHttpRequest');}catch(e){} x.onreadystatechange=function(){if(x.readyState===4){var r=null;try{r=JSON.parse(x.responseText);}catch(e){}cb(r,x.status);}}; x.send(null); } function sameOrigin(url){var a=d.createElement('a');a.href=url;return a.host===w.location.host;} function quickGo(href){ w.location.replace(href); } d.addEventListener('click', function(e){ if (e.defaultPrevented || e.button!==0 || e.metaKey||e.ctrlKey||e.shiftKey||e.altKey) return; var a=e.target; while(a && a.tagName!=='A') a=a.parentNode; if(!a||!a.getAttribute) return; var href=a.getAttribute('href')||''; if(!href) return; if(!sameOrigin(href)) return; if (!/\/game\.php\?/.test(href)) return; if (/\bgo=charWork\b/i.test(href)) { e.preventDefault ? e.preventDefault() : (e.returnValue=false); var url = href + (href.indexOf('?')>-1?'&':'?') + 'ajax=1'; ajaxGET(url, function(r){ if (r && r.ok && r.redirect) quickGo(r.redirect); else quickGo('/game.php?go=map'); }); return; } if (/\bgo=map\b/i.test(href) && (/\bgets=/.test(href) || /\bnapadenie=/.test(href))) { e.preventDefault ? e.preventDefault() : (e.returnValue=false); var url2 = href + (href.indexOf('?')>-1?'&':'?') + 'ajax=1'; ajaxGET(url2, function(r){ quickGo('/game.php?go=map'); }); return; } }, true); var _orig_loc = w.loc; function _loc(u){ if (typeof u!=='string'){ if(_orig_loc) try{_orig_loc(u);}catch(e){} return; } if (/^charWork(\b|&)/i.test(u) || /\bgo=charWork\b/i.test(u)){ var full = /^go=/.test(u) ? ('/game.php?'+u) : ('/game.php?go='+u); var url = full + (full.indexOf('?')>-1?'&':'?') + 'ajax=1'; ajaxGET(url, function(r){ if(r&&r.ok&&r.redirect) quickGo(r.redirect); else quickGo('/game.php?go=map'); }); return; } quickGo(/^go=/.test(u)?('/game.php?'+u):('/game.php?go='+u)); } w.loc = _loc; try{ if(w.parent) w.parent.loc = _loc; }catch(e){} try{ if(w.top) w.top.loc=_loc; }catch(e){} })(window, document); </script>
