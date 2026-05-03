<?php
$qx01 = quest_process(1, 2);
$q2   = isset_qest(2);
$eventDay = true;

if (!empty($_GET['quest_npc']) && !empty($_GET['do']) && ($_GET['quest_npc'] == 1 || $_GET['quest_npc'] == 2 || $_GET['quest_npc'] == 3)) {
    if ($_GET['quest_npc'] == 2 && $qx01) include ("npc/npc_2.php");
    elseif ($_GET['quest_npc'] == 3 && !$q2) include ("npc/npc_3.php");
    elseif ($_GET['quest_npc'] == 1 && $eventDay) include ("npc/npc_event2.php");
    else die("<script>location.href='game.php?go=char';</script>");
} else {
    if (!empty($_GET['npc']) && ($_GET['npc'] == 1 || $_GET['npc'] == 2 || $_GET['npc'] == 3)) {
        if ($_GET['npc'] == 1) {
            include ("npc/1.php");
            $img_r = '<img src="img/room/1_1.png" width="250" height="150">';
        } elseif ($_GET['npc'] == 2) {
            include ("npc/shop.php");
        } elseif ($_GET['npc'] == 3) {
            include ("npc/kurator.php");
        }
    } else {
        $name = 'Р С’Р В»Р В°Р В±Р В°РЎРѓРЎвЂљР С‘РЎРЏ';
        $about = 'Р вЂњР С•РЎР‚Р С•Р Т‘ Р С—РЎР‚Р ВµР Т‘РЎРѓРЎвЂљР В°Р Р†Р В»РЎРЏР ВµРЎвЂљ РЎРѓР С•Р В±Р С•Р в„– Р С•Р С–РЎР‚Р С•Р СР Р…РЎвЂ№Р в„– РЎвЂЎР В°РЎРѓРЎвЂљР Р…РЎвЂ№Р в„– РЎРѓР ВµР С”РЎвЂљР С•РЎР‚ РЎРѓ Р С—РЎвЂ№РЎв‚¬Р Р…РЎвЂ№Р СР С‘ Р В·Р ВµР СР В»РЎРЏР СР С‘. 
                  Р вЂќР С•Р СР В° РЎР‚Р В°РЎРѓР С—Р С•Р В»Р С•Р В¶Р ВµР Р…РЎвЂ№ Р Р…Р В° Р Р†Р Р…РЎС“РЎв‚¬Р С‘РЎвЂљР ВµР В»РЎРЉР Р…Р С•Р С РЎР‚Р В°РЎРѓРЎРѓРЎвЂљР С•РЎРЏР Р…Р С‘Р С‘ Р С•Р Т‘Р С‘Р Р… Р С•РЎвЂљ Р С•Р Т‘Р Р…Р С•Р С–Р С•, Р С—Р С•РЎРЊРЎвЂљР С•Р СРЎС“ Р СР С•Р В¶Р ВµРЎвЂљ Р С—Р С•Р С”Р В°Р В·Р В°РЎвЂљРЎРЉРЎРѓРЎРЏ, РЎвЂЎРЎвЂљР С• Р В¶Р С‘Р Р†РЎС“РЎвЂљ Р Р† Р С’Р В»Р В°Р В±Р В°РЎРѓРЎвЂљР С‘Р С‘ Р С‘РЎРѓР С”Р В»РЎР‹РЎвЂЎР С‘РЎвЂљР ВµР В»РЎРЉР Р…Р С• Р В±Р С•Р С–Р В°РЎвЂЎР С‘. 
                  Р РЋР В°Р С Р С–Р С•РЎР‚Р С•Р Т‘ РЎРѓР В»Р В°Р Р†Р С‘РЎвЂљРЎРѓРЎРЏ Р С—Р С‘РЎвЂљР С•Р СР Р…Р С‘Р С”Р С•Р С Р С‘ Р В»Р В°Р В±Р С•РЎР‚Р В°РЎвЂљР С•РЎР‚Р С‘Р ВµР в„– Р В·Р Р…Р В°Р СР ВµР Р…Р С‘РЎвЂљР С•Р С–Р С• Р С—РЎР‚Р С•РЎвЂћР ВµРЎРѓРЎРѓР С•РЎР‚Р В° Р С›РЎС“Р С”Р В°. 
                  Р СљР Р…Р С•Р С–Р С‘Р Вµ Р Р…Р С•Р Р†Р С‘РЎвЂЎР С”Р С‘ Р Р…Р В°РЎвЂЎР С‘Р Р…Р В°РЎР‹РЎвЂљ Р В·Р Т‘Р ВµРЎРѓРЎРЉ РЎРѓР Р†Р С•Р в„– Р С—РЎС“РЎвЂљРЎРЉ, Р С—Р С•Р В»РЎС“РЎвЂЎР В°РЎРЏ РЎРѓРЎвЂљР В°РЎР‚РЎвЂљР С•Р Р†Р С•Р С–Р С• Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В°.';

        // РЎРѓРЎРѓРЎвЂ№Р В»Р С”Р С‘ Р Р…Р В° char РІР‚вЂќ Р В±Р ВµР В· target, РЎРѓ data-go/data-params (SPA), Р С‘ РЎРѓР С• РЎРѓР В»Р ВµРЎв‚¬Р ВµР С Р С—Р ВµРЎР‚Р ВµР Т‘ game.php
        $pers  = '<a href="/game.php?go=char&npc=1&do_npc=pc" data-go="char" data-params="npc=1&do_npc=pc">Р СџР С•Р С”Р ВµРЎвЂ Р ВµР Р…РЎвЂљРЎР‚</a> | ';
        $pers .= '<a href="/game.php?go=char&npc=2" data-go="char" data-params="npc=2">Р СџР С•Р С”Р ВµР СР В°РЎР‚Р С”Р ВµРЎвЂљ</a> | ';
        $pers .= '<a href="/game.php?go=char&npc=3" data-go="char" data-params="npc=3">Р С™РЎС“РЎР‚Р В°РЎвЂљР С•РЎР‚</a> ';
        if ($qx01) $pers .= '| <a href="/game.php?go=char&quest_npc=2&do=1" data-go="char" data-params="quest_npc=2&do=1">Р РЋР В»РЎС“РЎвЂЎР В°Р в„–Р Р…РЎвЂ№Р в„– Р С—РЎР‚Р С•РЎвЂ¦Р С•Р В¶Р С‘Р в„–</a> ';
        if (!$q2)  $pers .= '| <a href="/game.php?go=char&quest_npc=3&do=1" data-go="char" data-params="quest_npc=3&do=1">Р РЋРЎвЂљРЎР‚Р В°Р Р…Р Р…РЎвЂ№Р в„– Р РЋР С—Р В°Р в„–Р С”</a> ';

        // Р С—Р ВµРЎР‚Р ВµРЎвЂ¦Р С•Р Т‘РЎвЂ№ Р С—Р С• Р В»Р С•Р С”Р В°РЎвЂ Р С‘РЎРЏР С РІР‚вЂќ Р Р…Р В° charWork, Р В±Р ВµР В· target, РЎРѓ data-* Р Т‘Р В»РЎРЏ Р С—Р ВµРЎР‚Р ВµРЎвЂ¦Р Р†Р В°РЎвЂљРЎвЂЎР С‘Р С”Р В°
        $move  = '<a href="/game.php?go=charWork&loc=3"  data-go="charWork" data-params="loc=3">Р вЂєР В°Р В±Р С•РЎР‚Р В°РЎвЂљР С•РЎР‚Р С‘РЎРЏ</a> | ';
        $move .= '<a href="/game.php?go=charWork&loc=17" data-go="charWork" data-params="loc=17">Р вЂ”Р Т‘Р В°Р Р…Р С‘Р Вµ Р С’Р Т‘Р СР С‘Р Р…Р С‘РЎРѓРЎвЂљРЎР‚Р В°РЎвЂ Р С‘Р С‘</a> | ';
        $move .= '<a href="/game.php?go=charWork&loc=81" data-go="charWork" data-params="loc=81">Р РЋРЎвЂљР В°Р Т‘Р С‘Р С•Р Р… Р С›Р С–Р Р…Р ВµР Р…Р Р…РЎвЂ№РЎвЂ¦ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р С•Р Р†</a> | ';
        $move .= '<s>Р вЂ”Р С•Р Р…Р В° РЎвЂљРЎР‚Р ВµР Р…Р С‘РЎР‚Р С•Р Р†Р С”Р С‘</s> | <s>Р РЋР ВµР Р†Р ВµРЎР‚Р Р…Р В°РЎРЏ Р С’Р В»Р В°Р В±Р В°РЎРѓРЎвЂљР С‘РЎРЏ</s> | ';
        $move .= '<a href="/game.php?go=charWork&loc=4"  data-go="charWork" data-params="loc=4">Р вЂќР С•РЎР‚Р С•Р С–Р В° 1</a>';

        $img_r = '<img src="img/room/001.png" width="290" height="150">';
    }
}
?>
<script type="text/javascript"> (function (w, d) { function ajaxGET(url, cb){var x=new XMLHttpRequest();x.open('GET',url,true); try{x.setRequestHeader('X-Requested-With','XMLHttpRequest');}catch(e){} x.onreadystatechange=function(){if(x.readyState===4){var r=null;try{r=JSON.parse(x.responseText);}catch(e){}cb(r,x.status);}}; x.send(null); } function sameOrigin(url){var a=d.createElement('a');a.href=url;return a.host===w.location.host;} function quickGo(href){ w.location.replace(href); } d.addEventListener('click', function(e){ if (e.defaultPrevented || e.button!==0 || e.metaKey||e.ctrlKey||e.shiftKey||e.altKey) return; var a=e.target; while(a && a.tagName!=='A') a=a.parentNode; if(!a||!a.getAttribute) return; var href=a.getAttribute('href')||''; if(!href) return; if(!sameOrigin(href)) return; if (!/\/game\.php\?/.test(href)) return; if (/\bgo=charWork\b/i.test(href)) { e.preventDefault ? e.preventDefault() : (e.returnValue=false); var url = href + (href.indexOf('?')>-1?'&':'?') + 'ajax=1'; ajaxGET(url, function(r){ if (r && r.ok && r.redirect) quickGo(r.redirect); else quickGo('/game.php?go=map'); }); return; } if (/\bgo=map\b/i.test(href) && (/\bgets=/.test(href) || /\bnapadenie=/.test(href))) { e.preventDefault ? e.preventDefault() : (e.returnValue=false); var url2 = href + (href.indexOf('?')>-1?'&':'?') + 'ajax=1'; ajaxGET(url2, function(r){ quickGo('/game.php?go=map'); }); return; } }, true); var _orig_loc = w.loc; function _loc(u){ if (typeof u!=='string'){ if(_orig_loc) try{_orig_loc(u);}catch(e){} return; } if (/^charWork(\b|&)/i.test(u) || /\bgo=charWork\b/i.test(u)){ var full = /^go=/.test(u) ? ('/game.php?'+u) : ('/game.php?go='+u); var url = full + (full.indexOf('?')>-1?'&':'?') + 'ajax=1'; ajaxGET(url, function(r){ if(r&&r.ok&&r.redirect) quickGo(r.redirect); else quickGo('/game.php?go=map'); }); return; } quickGo(/^go=/.test(u)?('/game.php?'+u):('/game.php?go='+u)); } w.loc = _loc; try{ if(w.parent) w.parent.loc = _loc; }catch(e){} try{ if(w.top) w.top.loc=_loc; }catch(e){} })(window, document); </script>
