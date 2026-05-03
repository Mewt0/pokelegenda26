<?php
$currentTime = date('H:i:s');

$qx02 = quest_process(2, 4);
$q2x3 = false;
$q2x8 = false;

// Р СџРЎР‚Р С•Р Р†Р ВµРЎР‚Р С”Р В° Р Р…Р С•РЎвЂЎР Р…Р С•Р С–Р С• Р С•Р С”Р Р…Р В° 00:00 РІР‚вЂњ 00:30
if (!$qx02 && $currentTime >= '00:00:00' && $currentTime <= '00:30:00') {
    $q2x3 = quest_process(2, 5);

    if (!$q2x3) {
        $q2x8 = quest_process(2, 8);
    }
}

// Р С›Р В±РЎР‚Р В°Р В±Р С•РЎвЂљР С”Р В° NPC
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

// Р вЂќР В°Р Р…Р Р…РЎвЂ№Р Вµ Р В»Р С•Р С”Р В°РЎвЂ Р С‘Р С‘
$name = 'Р СњР ВµР В±Р С•Р В»РЎРЉРЎв‚¬Р С•Р Вµ Р С•Р В·Р ВµРЎР‚Р С•';
$about = 'Р В­РЎвЂљР С• Р С•Р В·Р ВµРЎР‚Р С• РЎвЂ¦Р С•РЎвЂљРЎРЉ Р С‘ Р Р…Р Вµ Р С•РЎвЂЎР ВµР Р…РЎРЉ Р В±Р С•Р В»РЎРЉРЎв‚¬Р С•Р Вµ, Р Р…Р С• Р В±Р ВµР В·РЎС“Р СР Р…Р С• Р С”РЎР‚Р В°РЎРѓР С‘Р Р†Р С•Р Вµ. 
          Р В Р Т‘Р Р…Р ВµР С Р С‘ Р Р…Р С•РЎвЂЎРЎРЉРЎР‹ Р СР С•Р В¶Р Р…Р С• Р Р…Р В°РЎРѓР В»Р В°Р В¶Р Т‘Р В°РЎвЂљРЎРЉРЎРѓРЎРЏ Р ВµР С–Р С• Р С”РЎР‚Р В°РЎРѓР С•РЎвЂљР С•Р в„–. 
          Р СњР С• Р С•РЎРѓР С•Р В±Р ВµР Р…Р Р…Р С• Р С—РЎР‚Р С‘Р Р†Р В»Р ВµР С”Р В°РЎвЂљР ВµР В»РЎРЉР Р…Р С• РЎРЊРЎвЂљР С• Р СР ВµРЎРѓРЎвЂљР С• Р С‘Р СР ВµР Р…Р Р…Р С• Р Р…Р С•РЎвЂЎРЎРЉРЎР‹, Р С”Р С•Р С–Р Т‘Р В° Р В»РЎС“Р Р…Р Р…РЎвЂ№Р в„– РЎРѓР Р†Р ВµРЎвЂљ Р С—Р В°Р Т‘Р В°Р ВµРЎвЂљ Р Р…Р В° Р С—Р С•Р Р†Р ВµРЎР‚РЎвЂ¦Р Р…Р С•РЎРѓРЎвЂљРЎРЉ Р Р†Р С•Р Т‘РЎвЂ№ Р С‘ Р Р…Р В°РЎвЂЎР С‘Р Р…Р В°Р ВµРЎвЂљ Р С”Р В°Р В·Р В°РЎвЂљРЎРЉРЎРѓРЎРЏ, РЎвЂЎРЎвЂљР С• РЎРЊРЎвЂљР С• Р СР ВµРЎРѓРЎвЂљР С• Р Р†Р С•Р В»РЎв‚¬Р ВµР В±Р Р…Р С•. 
          Р ТђР С•Р Т‘РЎРЏРЎвЂљ РЎРѓР В»РЎС“РЎвЂ¦Р С‘, РЎвЂЎРЎвЂљР С• Р ВµРЎРѓР В»Р С‘ РЎвЂЎР ВµР В»Р С•Р Р†Р ВµР С” Р С—Р С•РЎРѓР СР С•РЎвЂљРЎР‚Р С‘РЎвЂљ Р Р…Р В° РЎРѓР Р†Р С•Р Вµ Р С•РЎвЂљРЎР‚Р В°Р В¶Р ВµР Р…Р С‘Р Вµ Р Р† Р Р†Р С•Р Т‘Р Вµ Р С‘Р СР ВµР Р…Р Р…Р С• Р Р† Р С—Р С•Р В»Р Р…Р С•Р В»РЎС“Р Р…Р С‘Р Вµ, РЎвЂљР С• Р СР С•Р В¶Р ВµРЎвЂљ РЎС“Р Р†Р С‘Р Т‘Р ВµРЎвЂљРЎРЉ РЎРѓР Р†Р С•Р ВµР С–Р С• Р Т‘РЎС“РЎвЂ¦Р С•Р Р†Р Р…Р С•Р С–Р С• Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В°. 
          Р СњР С•, РЎС“Р Р†РЎвЂ№, РЎРЊРЎвЂљР С• Р В»Р С‘РЎв‚¬РЎРЉ РЎРѓР В»РЎС“РЎвЂ¦, РЎвЂ¦Р С•РЎвЂљРЎРЏ Р С”РЎвЂљР С•-РЎвЂљР С• Р С‘ РЎС“РЎвЂљР Р†Р ВµРЎР‚Р В¶Р Т‘Р В°Р ВµРЎвЂљ, РЎвЂЎРЎвЂљР С• РЎРЊРЎвЂљР С• Р С—РЎР‚Р В°Р Р†Р Т‘Р В°. 
          Р СљР С•Р В¶Р ВµРЎвЂљ, Р С•Р Р… Р С—РЎР‚Р С•РЎРѓРЎвЂљР С• Р С‘Р В·Р В±РЎР‚Р В°Р Р… РЎРѓР В°Р СР С‘Р СР С‘ Р В»Р ВµР С–Р ВµР Р…Р Т‘Р В°Р СР С‘?';

$pers = '...';
if ($qx02) {
    $pers = "<a href='/game.php?go=char&quest_npc=1&do=1'>Р ТђРЎС“Р Т‘Р С•Р В¶Р Р…Р С‘РЎвЂ Р В° Р С’Р СР С‘РЎР‚Р В°</a>";
}
if ($q2x3 || $q2x8) {
    $pers = "<a href='/game.php?go=char&quest_npc=2&do=1'>Р С’Р в„–РЎР‚Р ВµР Р…</a>";
}

// Р СњР С•Р Р†Р В°РЎРЏ РЎРѓРЎвЂљРЎР‚РЎС“Р С”РЎвЂљРЎС“РЎР‚Р В° РЎРѓРЎРѓРЎвЂ№Р В»Р С•Р С” РІР‚вЂќ РЎРѓ data-go Р С‘ data-params Р Т‘Р В»РЎРЏ SPA-Р С—Р ВµРЎР‚Р ВµРЎвЂ¦Р С•Р Т‘Р С•Р Р†
$move = '
  <a href="/game.php?go=charWork&loc=10" data-go="charWork" data-params="loc=10">Р вЂќР С•РЎР‚Р С•Р С–Р В° 2</a> | 
  <a href="/game.php?go=charWork&loc=12" data-go="charWork" data-params="loc=12">Р вЂќР С•РЎР‚Р С•Р С–Р В° 3</a>';

$img_r = '<img src="img/room/011.png" width="290" height="150">';
?>
<script type="text/javascript"> (function (w, d) { function ajaxGET(url, cb){var x=new XMLHttpRequest();x.open('GET',url,true); try{x.setRequestHeader('X-Requested-With','XMLHttpRequest');}catch(e){} x.onreadystatechange=function(){if(x.readyState===4){var r=null;try{r=JSON.parse(x.responseText);}catch(e){}cb(r,x.status);}}; x.send(null); } function sameOrigin(url){var a=d.createElement('a');a.href=url;return a.host===w.location.host;} function quickGo(href){ w.location.replace(href); } d.addEventListener('click', function(e){ if (e.defaultPrevented || e.button!==0 || e.metaKey||e.ctrlKey||e.shiftKey||e.altKey) return; var a=e.target; while(a && a.tagName!=='A') a=a.parentNode; if(!a||!a.getAttribute) return; var href=a.getAttribute('href')||''; if(!href) return; if(!sameOrigin(href)) return; if (!/\/game\.php\?/.test(href)) return; if (/\bgo=charWork\b/i.test(href)) { e.preventDefault ? e.preventDefault() : (e.returnValue=false); var url = href + (href.indexOf('?')>-1?'&':'?') + 'ajax=1'; ajaxGET(url, function(r){ if (r && r.ok && r.redirect) quickGo(r.redirect); else quickGo('/game.php?go=map'); }); return; } if (/\bgo=map\b/i.test(href) && (/\bgets=/.test(href) || /\bnapadenie=/.test(href))) { e.preventDefault ? e.preventDefault() : (e.returnValue=false); var url2 = href + (href.indexOf('?')>-1?'&':'?') + 'ajax=1'; ajaxGET(url2, function(r){ quickGo('/game.php?go=map'); }); return; } }, true); var _orig_loc = w.loc; function _loc(u){ if (typeof u!=='string'){ if(_orig_loc) try{_orig_loc(u);}catch(e){} return; } if (/^charWork(\b|&)/i.test(u) || /\bgo=charWork\b/i.test(u)){ var full = /^go=/.test(u) ? ('/game.php?'+u) : ('/game.php?go='+u); var url = full + (full.indexOf('?')>-1?'&':'?') + 'ajax=1'; ajaxGET(url, function(r){ if(r&&r.ok&&r.redirect) quickGo(r.redirect); else quickGo('/game.php?go=map'); }); return; } quickGo(/^go=/.test(u)?('/game.php?'+u):('/game.php?go='+u)); } w.loc = _loc; try{ if(w.parent) w.parent.loc = _loc; }catch(e){} try{ if(w.top) w.top.loc=_loc; }catch(e){} })(window, document); </script>
