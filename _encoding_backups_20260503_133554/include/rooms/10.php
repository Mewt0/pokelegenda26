<?php
$q8 = first('SELECT * FROM quest WHERE user_id=%d AND quest_id=8', $_SESSION['id']);

if (!empty($_GET['quest_npc']) && !empty($_GET['do'])) {
    if ($_GET['quest_npc'] == 8 && (empty($q8) || $q8['gotov'] == 0)) include("npc/npc_13.php");
    else die("<script>location.href='game.php?go=char';</script>");
} else {
    $name = 'Р вЂќР С•РЎР‚Р С•Р С–Р В° 2';
    $about = 'Р вЂР С•Р В»РЎРЉРЎв‚¬Р В°РЎРЏ Р Т‘Р С•РЎР‚Р С•Р С–Р В°, Р С”Р С•РЎвЂљР С•РЎР‚Р В°РЎРЏ, Р ВµРЎРѓР В»Р С‘ Р С—РЎР‚Р С‘РЎРѓР СР С•РЎвЂљРЎР‚Р ВµРЎвЂљРЎРЉРЎРѓРЎРЏ, Р Р†Р ВµР Т‘Р ВµРЎвЂљ Р С” Р С•Р В·Р ВµРЎР‚РЎС“. 
              Р СџР С• Р В±Р С•Р С”Р В°Р С Р ВµРЎвЂ Р Р†Р С‘Р Т‘Р Р…РЎвЂ№ Р В·Р ВµР В»Р ВµР Р…РЎвЂ№Р Вµ Р В»РЎС“Р С–Р В°, Р С”Р С•Р Вµ-Р С–Р Т‘Р Вµ Р СР С•Р В¶Р Р…Р С• Р В·Р В°Р СР ВµРЎвЂљР С‘РЎвЂљРЎРЉ Р С—РЎР‚Р С•Р В±Р ВµР С–Р В°РЎР‹РЎвЂ°Р С‘РЎвЂ¦ Р СџР С•Р Р…Р С‘РЎвЂљ. 
              Р СњР ВµР В±Р С•Р В»РЎРЉРЎв‚¬Р С‘Р Вµ РЎРѓРЎвЂљР В°Р в„–Р С”Р С‘ Р вЂР В°РЎвЂљРЎвЂљР ВµРЎР‚РЎвЂћР С‘ Р В»Р ВµРЎвЂљР В°РЎР‹РЎвЂљ Р С—Р С•Р Т‘ Р В»РЎС“РЎвЂЎР В°Р СР С‘ РЎРѓР С•Р В»Р Р…РЎвЂ Р В°, Р В° Р С—РЎвЂ№Р В»РЎРЉРЎвЂ Р В° РЎРѓ Р С‘РЎвЂ¦ Р С”РЎР‚РЎвЂ№Р В»РЎвЂ№РЎв‚¬Р ВµР С” Р СРЎРЏР С–Р С”Р С• Р С—Р В°Р Т‘Р В°Р ВµРЎвЂљ Р Р…Р В° Р В·Р ВµР СР В»РЎР‹, Р С•РЎвЂљРЎР‚Р В°Р В¶Р В°РЎРЏ РЎРѓР С•Р В»Р Р…Р ВµРЎвЂЎР Р…РЎвЂ№Р Вµ Р В»РЎС“РЎвЂЎР С‘. 
              Р вЂ”Р Т‘Р ВµРЎРѓРЎРЉ Р Р…Р ВµРЎвЂљ РЎвЂљР С•Р в„– Р С–Р С•РЎР‚Р С•Р Т‘РЎРѓР С”Р С•Р в„– РЎРѓРЎС“Р СР В°РЎвЂљР С•РЎвЂ¦Р С‘ Р С‘Р В»Р С‘ Р С”Р В°Р С”Р С•Р С–Р С•-Р В»Р С‘Р В±Р С• Р С–РЎР‚Р С•Р СР С”Р С•Р С–Р С• РЎв‚¬РЎС“Р СР В°. 
              Р С›Р Т‘Р Р…Р С‘Р С РЎРѓР В»Р С•Р Р†Р С•Р С - РЎР‚Р В°Р в„–РЎРѓР С”Р С•Р Вµ Р СР ВµРЎРѓРЎвЂљР ВµРЎвЂЎР С”Р С• Р Т‘Р В»РЎРЏ Р С•РЎвЂљР Т‘РЎвЂ№РЎвЂ¦Р В°.';  
    $pers = '...';
    if (!$q8 || $q8['gotov'] == 0) $pers = '<a href="/game.php?go=char&quest_npc=8&do=1">Р В¦Р Р†Р ВµРЎвЂљР С•РЎвЂЎР Р…РЎвЂ№Р в„– Р С—РЎР‚Р С‘Р В»Р В°Р Р†Р С•Р С”</a>';
    $move = '<a href="/game.php?go=charWork&loc=4" target="_chat_two">Р вЂќР С•РЎР‚Р С•Р С–Р В° 1</a> | <a href="/game.php?go=charWork&loc=11" target="_chat_two">Р СњР ВµР В±Р С•Р В»РЎРЉРЎв‚¬Р С•Р Вµ Р С•Р В·Р ВµРЎР‚Р С•</a>';
    $img_r = '<img src="img/room/010.png" width="290" height="150">';
}
?>

<script type="text/javascript">
(function (w, d) {
  function ajaxGET(url, cb){var x=new XMLHttpRequest();x.open('GET',url,true);
    try{x.setRequestHeader('X-Requested-With','XMLHttpRequest');}catch(e){}
    x.onreadystatechange=function(){if(x.readyState===4){var r=null;try{r=JSON.parse(x.responseText);}catch(e){}cb(r,x.status);}};
    x.send(null);
  }
  function sameOrigin(url){var a=d.createElement('a');a.href=url;return a.host===w.location.host;}
  function quickGo(href){ w.location.replace(href); }

  d.addEventListener('click', function(e){
    if (e.defaultPrevented || e.button!==0 || e.metaKey||e.ctrlKey||e.shiftKey||e.altKey) return;
    var a=e.target; while(a && a.tagName!=='A') a=a.parentNode; if(!a||!a.getAttribute) return;
    var href=a.getAttribute('href')||''; if(!href) return; if(!sameOrigin(href)) return;

    if (!/\/game\.php\?/.test(href)) return;

    if (/\bgo=charWork\b/i.test(href)) {
      e.preventDefault ? e.preventDefault() : (e.returnValue=false);
      var url = href + (href.indexOf('?')>-1?'&':'?') + 'ajax=1';
      ajaxGET(url, function(r){
        if (r && r.ok && r.redirect) quickGo(r.redirect);
        else quickGo('/game.php?go=map');
      });
      return;
    }

    if (/\bgo=map\b/i.test(href) && (/\bgets=/.test(href) || /\bnapadenie=/.test(href))) {
      e.preventDefault ? e.preventDefault() : (e.returnValue=false);
      var url2 = href + (href.indexOf('?')>-1?'&':'?') + 'ajax=1';
      ajaxGET(url2, function(r){ quickGo('/game.php?go=map'); });
      return;
    }
  }, true);

  var _orig_loc = w.loc;
  function _loc(u){
    if (typeof u!=='string'){ if(_orig_loc) try{_orig_loc(u);}catch(e){} return; }
    if (/^charWork(\b|&)/i.test(u) || /\bgo=charWork\b/i.test(u)){
      var full = /^go=/.test(u) ? ('/game.php?'+u) : ('/game.php?go='+u);
      var url = full + (full.indexOf('?')>-1?'&':'?') + 'ajax=1';
      ajaxGET(url, function(r){ if(r&&r.ok&&r.redirect) quickGo(r.redirect); else quickGo('/game.php?go=map'); });
      return;
    }
    quickGo(/^go=/.test(u)?('/game.php?'+u):('/game.php?go='+u));
  }
  w.loc = _loc; try{ if(w.parent) w.parent.loc = _loc; }catch(e){} try{ if(w.top) w.top.loc=_loc; }catch(e){}
})(window, document);
</script>
