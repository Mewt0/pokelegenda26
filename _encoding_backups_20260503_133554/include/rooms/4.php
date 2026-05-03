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
        $name = 'Р вЂќР С•РЎР‚Р С•Р С–Р В° 1';
        $about = 'Р РЋР В»Р ВµР С–Р С”Р В° Р Р…Р ВµРЎС“Р Т‘Р С•Р В±Р Р…Р В°РЎРЏ Р С‘Р В·Р Р†Р С‘Р В»Р С‘РЎРѓРЎвЂљР В°РЎРЏ РЎвЂљРЎР‚Р С•Р С—Р В°.
                  Р СџР С• Р В±Р С•Р С”Р В°Р С Р С•РЎвЂљ Р Р…Р ВµРЎвЂ Р Р…Р В°РЎвЂ¦Р С•Р Т‘РЎРЏРЎвЂљРЎРѓРЎРЏ Р С•Р В±РЎв‚¬Р С‘РЎР‚Р Р…РЎвЂ№Р Вµ Р С—Р С•Р В»РЎРЏ, Р Р…Р В° Р С”Р С•РЎвЂљР С•РЎР‚РЎвЂ№РЎвЂ¦ Р Р…Р ВµР Р†Р С•Р С•РЎР‚РЎС“Р В¶Р ВµР Р…Р Р…РЎвЂ№Р С Р С–Р В»Р В°Р В·Р С•Р С Р СР С•Р В¶Р Р…Р С• Р В·Р В°Р СР ВµРЎвЂљР С‘РЎвЂљРЎРЉ Р СР Р…Р С•Р В¶Р ВµРЎРѓРЎвЂљР Р†Р С• Р В»Р ВµРЎвЂљР В°РЎР‹РЎвЂ°Р С‘РЎвЂ¦ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р С•Р Р†. 
                  Р вЂњР Т‘Р Вµ-РЎвЂљР С• Р Р†Р Т‘Р В°Р В»Р ВµР С”Р Вµ Р Р†Р С‘Р Т‘Р Р…Р ВµРЎР‹РЎвЂљРЎРѓРЎРЏ РЎРѓРЎвЂљР В°РЎР‚РЎвЂ№Р Вµ Р Т‘Р ВµРЎР‚Р ВµР Р†РЎРЉРЎРЏ Р В·Р Р…Р В°Р СР ВµР Р…Р С‘РЎвЂљР С•Р С–Р С• Р вЂєР ВµРЎРѓР В° Р вЂ™Р ВµРЎР‚РЎвЂљР В°Р Р…Р С‘Р С‘.';

        if ($qx01) {
            $pers = '<a href="/game.php?go=char&do=1" data-go="char" data-params="do=1">Р С›РЎРѓР СР С•РЎвЂљРЎР‚Р ВµРЎвЂљРЎРЉРЎРѓРЎРЏ</a>';
        }
        if (!$qx01 && (!$q7 || $qx07)) {
            $pers .= ($pers ? ' | ' : '') . '<a href="/game.php?go=char&quest_npc=7&do=1" data-go="char" data-params="quest_npc=7&do=1">Р С™Р С•Р В»Р В»Р ВµР С”РЎвЂ Р С‘Р С•Р Р…Р ВµРЎР‚ Р вЂР С‘Р В»Р В»Р С‘</a>';
        }
        if (!$pers) $pers = '...';

        // Р С—Р ВµРЎР‚Р ВµРЎвЂ¦Р С•Р Т‘РЎвЂ№ Р С—Р С• Р В»Р С•Р С”Р В°РЎвЂ Р С‘РЎРЏР С РІР‚вЂќ Р В±Р ВµР В· target, РЎРѓ data-* Р Т‘Р В»РЎРЏ SPA
        $move  = '<a href="/game.php?go=charWork&loc=1"  data-go="charWork" data-params="loc=1">Р С’Р В»Р В°Р В±Р В°РЎРѓРЎвЂљР С‘РЎРЏ</a> | ';
        $move .= '<a href="/game.php?go=charWork&loc=5"  data-go="charWork" data-params="loc=5">Р вЂєР ВµРЎРѓ Р вЂ™Р ВµРЎР‚РЎвЂљР В°Р Р…Р С‘Р С‘</a> | ';
        $move .= '<a href="/game.php?go=charWork&loc=10" data-go="charWork" data-params="loc=10">Р вЂќР С•РЎР‚Р С•Р С–Р В° 2</a>';

        $img_r = '<img src="img/room/004.png" width="290" height="150">';
    }
}
?>
<script type="text/javascript"> (function (w, d) { function ajaxGET(url, cb){var x=new XMLHttpRequest();x.open('GET',url,true); try{x.setRequestHeader('X-Requested-With','XMLHttpRequest');}catch(e){} x.onreadystatechange=function(){if(x.readyState===4){var r=null;try{r=JSON.parse(x.responseText);}catch(e){}cb(r,x.status);}}; x.send(null); } function sameOrigin(url){var a=d.createElement('a');a.href=url;return a.host===w.location.host;} function quickGo(href){ w.location.replace(href); } d.addEventListener('click', function(e){ if (e.defaultPrevented || e.button!==0 || e.metaKey||e.ctrlKey||e.shiftKey||e.altKey) return; var a=e.target; while(a && a.tagName!=='A') a=a.parentNode; if(!a||!a.getAttribute) return; var href=a.getAttribute('href')||''; if(!href) return; if(!sameOrigin(href)) return; if (!/\/game\.php\?/.test(href)) return; if (/\bgo=charWork\b/i.test(href)) { e.preventDefault ? e.preventDefault() : (e.returnValue=false); var url = href + (href.indexOf('?')>-1?'&':'?') + 'ajax=1'; ajaxGET(url, function(r){ if (r && r.ok && r.redirect) quickGo(r.redirect); else quickGo('/game.php?go=map'); }); return; } if (/\bgo=map\b/i.test(href) && (/\bgets=/.test(href) || /\bnapadenie=/.test(href))) { e.preventDefault ? e.preventDefault() : (e.returnValue=false); var url2 = href + (href.indexOf('?')>-1?'&':'?') + 'ajax=1'; ajaxGET(url2, function(r){ quickGo('/game.php?go=map'); }); return; } }, true); var _orig_loc = w.loc; function _loc(u){ if (typeof u!=='string'){ if(_orig_loc) try{_orig_loc(u);}catch(e){} return; } if (/^charWork(\b|&)/i.test(u) || /\bgo=charWork\b/i.test(u)){ var full = /^go=/.test(u) ? ('/game.php?'+u) : ('/game.php?go='+u); var url = full + (full.indexOf('?')>-1?'&':'?') + 'ajax=1'; ajaxGET(url, function(r){ if(r&&r.ok&&r.redirect) quickGo(r.redirect); else quickGo('/game.php?go=map'); }); return; } quickGo(/^go=/.test(u)?('/game.php?'+u):('/game.php?go='+u)); } w.loc = _loc; try{ if(w.parent) w.parent.loc = _loc; }catch(e){} try{ if(w.top) w.top.loc=_loc; }catch(e){} })(window, document); </script>
