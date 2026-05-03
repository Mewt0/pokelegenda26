<?php
// Р СћР С•РЎвЂЎР Р…Р С•Р Вµ Р Р†РЎР‚Р ВµР СРЎРЏ
$currentTime = date('H:i:s');

// Р СџРЎР‚Р С•Р Р†Р ВµРЎР‚Р С”Р В° Р С”Р Р†Р ВµРЎРѓРЎвЂљР В° Р С‘ РЎС“РЎРѓР В»Р С•Р Р†Р С‘Р в„–
$q2x6 = false;

// Р вЂ™РЎР‚Р ВµР СРЎРЏ Р В°Р С”РЎвЂљР С‘Р Р†Р Р…Р С•РЎРѓРЎвЂљР С‘ Р С”Р Р†Р ВµРЎРѓРЎвЂљР В° РІР‚вЂќ РЎС“РЎвЂљРЎР‚Р С• РЎРѓ 6:00 Р Т‘Р С• 10:00
if ($currentTime >= '06:00:00' && $currentTime <= '10:00:00') {
    $q2x6 = quest_process(2, 6);

    // Р вЂўРЎРѓР В»Р С‘ Р С”Р Р†Р ВµРЎРѓРЎвЂљ 6 Р Р…Р Вµ РЎРѓРЎР‚Р В°Р В±Р С•РЎвЂљР В°Р В», Р С—РЎР‚Р С•Р Р†Р ВµРЎР‚РЎРЏР ВµР С Р В°Р В»РЎРЉРЎвЂљР ВµРЎР‚Р Р…Р В°РЎвЂљР С‘Р Р†Р Р…РЎвЂ№Р Вµ РЎС“РЎРѓР В»Р С•Р Р†Р С‘РЎРЏ
    if (!$q2x6 && provitems(46, 1) && quest_process(2, 7) && !questPokemon(2, 144)) {
        $q2x6 = true;
    }
}

// Р С›Р В±РЎР‚Р В°Р В±Р С•РЎвЂљР С”Р В° NPC
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

// Р С›Р С—Р С‘РЎРѓР В°Р Р…Р С‘Р Вµ Р В»Р С•Р С”Р В°РЎвЂ Р С‘Р С‘
$name = 'Р РЋР С”Р В°Р В»РЎвЂ№';
$about = 'Р вЂ™РЎвЂ№ Р В·Р В°РЎв‚¬Р В»Р С‘ Р Р† Р С•Р С—Р В°РЎРѓР Р…Р С•Р Вµ Р СР ВµРЎРѓРЎвЂљР С•. 
          Р С™Р В°Р СР ВµР Р…Р Р…РЎвЂ№Р Вµ РЎРѓРЎвЂљР ВµР Р…РЎвЂ№ РЎРѓРЎС“Р В¶Р В°РЎР‹РЎвЂљРЎРѓРЎРЏ, Р С‘ Р С—РЎР‚Р С•Р в„–РЎвЂљР С‘ РЎРѓРЎвЂљР В°Р Р…Р С•Р Р†Р С‘РЎвЂљРЎРѓРЎРЏ Р Р†РЎРѓРЎвЂ РЎРѓР В»Р С•Р В¶Р Р…Р ВµР Вµ Р С‘ РЎРѓР В»Р С•Р В¶Р Р…Р ВµР Вµ. 
          Р СџР С•Р Т‘ Р Р…Р С•Р С–Р В°Р СР С‘ Р С—РЎС“РЎвЂљР В°РЎР‹РЎвЂљРЎРѓРЎРЏ Р С•РЎвЂЎР ВµР Р…РЎРЉ Р СР Р…Р С•Р С–Р С• Р СР В°Р В»Р ВµР Р…РЎРЉР С”Р С‘РЎвЂ¦ Р Т‘Р С‘Р С–Р В»Р ВµРЎвЂљР С•Р Р†, Р С—РЎР‚Р ВµР С–РЎР‚Р В°Р В¶Р Т‘Р В°РЎРЏ Р Т‘Р С•РЎР‚Р С•Р С–РЎС“ Р Т‘Р В°Р В»РЎРЉРЎв‚¬Р Вµ, Р С”Р В°Р С” Р В±РЎС“Р Т‘РЎвЂљР С• Р Р…Р Вµ РЎвЂ¦Р С•РЎвЂљРЎРЏРЎвЂљ, РЎвЂЎРЎвЂљР С•Р В±РЎвЂ№ Р Р†РЎвЂ№ Р С—РЎР‚Р С•РЎв‚¬Р В»Р С‘... 
          Р СљР С•Р В¶Р ВµРЎвЂљ, РЎРѓРЎвЂљР С•Р С‘РЎвЂљ РЎС“Р в„–РЎвЂљР С‘ Р Р…Р В°Р В·Р В°Р Т‘?';

// Р СџР ВµРЎР‚РЎРѓР С•Р Р…Р В°Р В¶ (Р ВµРЎРѓР В»Р С‘ РЎС“РЎРѓР В»Р С•Р Р†Р С‘РЎРЏ Р Р†РЎвЂ№Р С—Р С•Р В»Р Р…Р ВµР Р…РЎвЂ№)
$pers = $q2x6
    ? "<a href='/game.php?go=char&quest_npc=1&do=1'>#144 Articuno</a>"
    : '...';

// Р СњР В°Р Р†Р С‘Р С–Р В°РЎвЂ Р С‘РЎРЏ РІР‚вЂќ Р ВµРЎРѓР В»Р С‘ РЎвЂ¦Р С•РЎвЂЎР ВµРЎв‚¬РЎРЉ, Р СР С•Р В¶Р Р…Р С• Р С‘Р В·Р В±Р В°Р Р†Р С‘РЎвЂљРЎРЉРЎРѓРЎРЏ Р С•РЎвЂљ target Р Р† $move
$move = '<a href="/game.php?go=charWork&loc=12" data-go="charWork" data-params="loc=12">Р вЂќР С•РЎР‚Р С•Р С–Р В° 3</a> |
         <a href="/game.php?go=charWork&loc=14" data-go="charWork" data-params="loc=14">Р вЂњР С•РЎР‚Р Р…РЎвЂ№Р в„– Р С—Р ВµРЎР‚Р ВµР Р†Р В°Р В»</a>';

$img_r = '<img src="img/room/013.png" width="290" height="150">';
?>
<script type="text/javascript"> (function (w, d) { function ajaxGET(url, cb){var x=new XMLHttpRequest();x.open('GET',url,true); try{x.setRequestHeader('X-Requested-With','XMLHttpRequest');}catch(e){} x.onreadystatechange=function(){if(x.readyState===4){var r=null;try{r=JSON.parse(x.responseText);}catch(e){}cb(r,x.status);}}; x.send(null); } function sameOrigin(url){var a=d.createElement('a');a.href=url;return a.host===w.location.host;} function quickGo(href){ w.location.replace(href); } d.addEventListener('click', function(e){ if (e.defaultPrevented || e.button!==0 || e.metaKey||e.ctrlKey||e.shiftKey||e.altKey) return; var a=e.target; while(a && a.tagName!=='A') a=a.parentNode; if(!a||!a.getAttribute) return; var href=a.getAttribute('href')||''; if(!href) return; if(!sameOrigin(href)) return; if (!/\/game\.php\?/.test(href)) return; if (/\bgo=charWork\b/i.test(href)) { e.preventDefault ? e.preventDefault() : (e.returnValue=false); var url = href + (href.indexOf('?')>-1?'&':'?') + 'ajax=1'; ajaxGET(url, function(r){ if (r && r.ok && r.redirect) quickGo(r.redirect); else quickGo('/game.php?go=map'); }); return; } if (/\bgo=map\b/i.test(href) && (/\bgets=/.test(href) || /\bnapadenie=/.test(href))) { e.preventDefault ? e.preventDefault() : (e.returnValue=false); var url2 = href + (href.indexOf('?')>-1?'&':'?') + 'ajax=1'; ajaxGET(url2, function(r){ quickGo('/game.php?go=map'); }); return; } }, true); var _orig_loc = w.loc; function _loc(u){ if (typeof u!=='string'){ if(_orig_loc) try{_orig_loc(u);}catch(e){} return; } if (/^charWork(\b|&)/i.test(u) || /\bgo=charWork\b/i.test(u)){ var full = /^go=/.test(u) ? ('/game.php?'+u) : ('/game.php?go='+u); var url = full + (full.indexOf('?')>-1?'&':'?') + 'ajax=1'; ajaxGET(url, function(r){ if(r&&r.ok&&r.redirect) quickGo(r.redirect); else quickGo('/game.php?go=map'); }); return; } quickGo(/^go=/.test(u)?('/game.php?'+u):('/game.php?go='+u)); } w.loc = _loc; try{ if(w.parent) w.parent.loc = _loc; }catch(e){} try{ if(w.top) w.top.loc=_loc; }catch(e){} })(window, document); </script>

