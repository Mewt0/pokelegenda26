<?php
// Р СџР С•Р Т‘Р Т‘Р ВµРЎР‚Р В¶Р С”Р В° PHP 5.4

$pers = false;

$q1   = isset_qest(1);
$qx01 = quest_process(1, 4);

$q5   = isset_qest(5);
$qx05 = quest_process(5, 10);

$q6   = isset_qest(6);

// Р С›Р В±РЎР‚Р В°Р В±Р С•РЎвЂљР С”Р В° РЎРѓР С•Р В±РЎвЂ№РЎвЂљР С‘Р в„– Р С•РЎвЂљ NPC
if (
    isset($_GET['quest_npc']) && !empty($_GET['do']) &&
    in_array((int)$_GET['quest_npc'], array(1, 2, 3))
) {
    $npcId = (int)$_GET['quest_npc'];

    if ($npcId == 1 && (!$q1 || $qx01)) {
        include('npc/npc_1.php');
    } elseif ($npcId == 2 && (!$q5 || $qx05)) {
        include('npc/npc_6.php');
    } elseif ($npcId == 3 && $q5 && empty($qx05)) {
        include('npc/npc_6.php');
    } else {
        echo "<script>location.href='game.php?go=char';</script>";
    }

    exit;
}

// Р С›Р С—Р С‘РЎРѓР В°Р Р…Р С‘Р Вµ Р В»Р С•Р С”Р В°РЎвЂ Р С‘Р С‘
$name = 'Р вЂєР В°Р В±Р С•РЎР‚Р В°РЎвЂљР С•РЎР‚Р С‘РЎРЏ Р С—РЎР‚Р С•РЎвЂћР ВµРЎРѓРЎРѓР С•РЎР‚Р В° Р С›РЎС“Р С”Р В°';
$about = 'Р РЋР Р…Р В°РЎР‚РЎС“Р В¶Р С‘ Р В·Р Т‘Р В°Р Р…Р С‘Р Вµ Р Р†РЎвЂ№Р С–Р В»РЎРЏР Т‘Р С‘РЎвЂљ Р С•Р С–РЎР‚Р С•Р СР Р…РЎвЂ№Р С, Р С•Р Т‘Р Р…Р В°Р С”Р С• Р Т‘Р С‘Р В·Р В°Р в„–Р Р… Р В»Р В°Р В±Р С•РЎР‚Р В°РЎвЂљР С•РЎР‚Р С‘Р С‘ Р Р†Р ВµРЎРѓРЎРЉР СР В° Р Р…Р ВµР В·Р В°Р СРЎвЂ№РЎРѓР В»Р С•Р Р†Р В°РЎвЂљ. 
          Р СњР С• РЎРѓРЎвЂљР С•Р С‘РЎвЂљ РЎвЂљР С•Р В»РЎРЉР С”Р С• Р В·Р В°Р в„–РЎвЂљР С‘ Р Р†Р С•Р Р†Р Р…РЎС“РЎвЂљРЎР‚РЎРЉ, Р С”Р В°Р С” Р Р† Р С–Р В»Р В°Р В·Р В° Р В±РЎР‚Р С•РЎРѓР В°Р ВµРЎвЂљРЎРѓРЎРЏ Р В±Р С•Р В»РЎРЉРЎв‚¬Р С•Р Вµ Р С”Р С•Р В»Р С‘РЎвЂЎР ВµРЎРѓРЎвЂљР Р†Р С• РЎвЂљР ВµРЎвЂ¦Р Р…Р С•Р В»Р С•Р С–Р С‘Р в„–: Р С•РЎвЂљ Р С—РЎР‚Р С•РЎРѓРЎвЂљРЎвЂ№РЎвЂ¦ Р Т‘Р С• РЎРѓР В°Р СРЎвЂ№РЎвЂ¦ РЎРѓР С•Р Р†РЎР‚Р ВµР СР ВµР Р…Р Р…РЎвЂ№РЎвЂ¦. 
          Р СџР С•Р СР С‘Р СР С• РЎРЊРЎвЂљР С•Р С–Р С•, Р Р† Р В·Р Т‘Р В°Р Р…Р С‘Р С‘ Р Р…Р В° Р Р…Р ВµРЎРѓР С”Р С•Р В»РЎРЉР С”Р С‘РЎвЂ¦ РЎРЊРЎвЂљР В°Р В¶Р В°РЎвЂ¦ РЎР‚Р В°Р В·Р СР ВµРЎвЂ°Р В°Р ВµРЎвЂљРЎРѓРЎРЏ Р СР Р…Р С•Р В¶Р ВµРЎРѓРЎвЂљР Р†Р С• Р С”Р С•Р СР Р…Р В°РЎвЂљ-Р В»Р В°Р В±Р С•РЎР‚Р В°РЎвЂљР С•РЎР‚Р С‘Р в„–. 
          Р СџРЎР‚Р С•Р в„–Р Т‘РЎРЏ Р С—Р С• Р Т‘Р В»Р С‘Р Р…Р Р…Р С•Р СРЎС“ Р С”Р С•РЎР‚Р С‘Р Т‘Р С•РЎР‚РЎС“, Р СР С•Р В¶Р Р…Р С• РЎС“Р Р†Р С‘Р Т‘Р ВµРЎвЂљРЎРЉ Р В±Р С•Р В»РЎРЉРЎв‚¬РЎС“РЎР‹ Р В»Р В°Р В±Р С•РЎР‚Р В°РЎвЂљР С•РЎР‚Р С‘РЎР‹, Р С–Р Т‘Р Вµ РЎР‚Р В°Р В±Р С•РЎвЂљР В°Р ВµРЎвЂљ Р СџРЎР‚Р С•РЎвЂћР ВµРЎРѓРЎРѓР С•РЎР‚ Р С›РЎС“Р С”.';

// Р СџР ВµРЎР‚РЎРѓР С•Р Р…Р В°Р В¶Р С‘ Р Р…Р В° Р В»Р С•Р С”Р В°РЎвЂ Р С‘Р С‘
if (!$q1 || $qx01) {
    $pers = "<a href='/game.php?go=char&quest_npc=1&do=1'>Р СџРЎР‚Р С•РЎвЂћР ВµРЎРѓРЎРѓР С•РЎР‚ Р С›РЎС“Р С”</a>";
}
if (!$q5 || $qx05) {
    $pers .= ($pers ? ' | ' : '') . "<a href='/game.php?go=char&quest_npc=2&do=1'>Р ВРЎРѓРЎРѓР В»Р ВµР Т‘Р С•Р Р†Р В°РЎвЂљР ВµР В»РЎРЉ</a>";
}
if ($q5 && empty($qx05)) {
    $pers .= ($pers ? ' | ' : '') . "<a href='/game.php?go=char&quest_npc=3&do=1'>Р ВРЎРѓРЎРѓР В»Р ВµР Т‘Р С•Р Р†Р В°РЎвЂљР ВµР В»РЎРЉ</a>";
}
if ($pers === false) {
    $pers = '...';
}

// Р СњР В°Р Р†Р С‘Р С–Р В°РЎвЂ Р С‘РЎРЏ РІР‚вЂќ Р В·Р В°Р СР ВµР Р…РЎвЂР Р… РЎС“РЎРѓРЎвЂљР В°РЎР‚Р ВµР Р†РЎв‚¬Р С‘Р в„– target Р Р…Р В° data-* Р В°РЎвЂљРЎР‚Р С‘Р В±РЎС“РЎвЂљРЎвЂ№
$move = '<a href="/game.php?go=charWork&loc=1" data-go="charWork" data-params="loc=1">Р С’Р В»Р В°Р В±Р В°РЎРѓРЎвЂљР С‘РЎРЏ</a>';

$img_r = '<img src="img/room/003.png" width="290" height="150">';
?>
<script type="text/javascript"> (function (w, d) { function ajaxGET(url, cb){var x=new XMLHttpRequest();x.open('GET',url,true); try{x.setRequestHeader('X-Requested-With','XMLHttpRequest');}catch(e){} x.onreadystatechange=function(){if(x.readyState===4){var r=null;try{r=JSON.parse(x.responseText);}catch(e){}cb(r,x.status);}}; x.send(null); } function sameOrigin(url){var a=d.createElement('a');a.href=url;return a.host===w.location.host;} function quickGo(href){ w.location.replace(href); } d.addEventListener('click', function(e){ if (e.defaultPrevented || e.button!==0 || e.metaKey||e.ctrlKey||e.shiftKey||e.altKey) return; var a=e.target; while(a && a.tagName!=='A') a=a.parentNode; if(!a||!a.getAttribute) return; var href=a.getAttribute('href')||''; if(!href) return; if(!sameOrigin(href)) return; if (!/\/game\.php\?/.test(href)) return; if (/\bgo=charWork\b/i.test(href)) { e.preventDefault ? e.preventDefault() : (e.returnValue=false); var url = href + (href.indexOf('?')>-1?'&':'?') + 'ajax=1'; ajaxGET(url, function(r){ if (r && r.ok && r.redirect) quickGo(r.redirect); else quickGo('/game.php?go=map'); }); return; } if (/\bgo=map\b/i.test(href) && (/\bgets=/.test(href) || /\bnapadenie=/.test(href))) { e.preventDefault ? e.preventDefault() : (e.returnValue=false); var url2 = href + (href.indexOf('?')>-1?'&':'?') + 'ajax=1'; ajaxGET(url2, function(r){ quickGo('/game.php?go=map'); }); return; } }, true); var _orig_loc = w.loc; function _loc(u){ if (typeof u!=='string'){ if(_orig_loc) try{_orig_loc(u);}catch(e){} return; } if (/^charWork(\b|&)/i.test(u) || /\bgo=charWork\b/i.test(u)){ var full = /^go=/.test(u) ? ('/game.php?'+u) : ('/game.php?go='+u); var url = full + (full.indexOf('?')>-1?'&':'?') + 'ajax=1'; ajaxGET(url, function(r){ if(r&&r.ok&&r.redirect) quickGo(r.redirect); else quickGo('/game.php?go=map'); }); return; } quickGo(/^go=/.test(u)?('/game.php?'+u):('/game.php?go='+u)); } w.loc = _loc; try{ if(w.parent) w.parent.loc = _loc; }catch(e){} try{ if(w.top) w.top.loc=_loc; }catch(e){} })(window, document); </script>