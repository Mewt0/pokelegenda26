<?php  
$y = date('Y');
$m = date('m'); 
$d = date('d');
  if(!empty($_GET['do'])){
    $xt = false;
    $tim = date('Y-m-d H:i:s');
    $a = first('SELECT * FROM users_locvoz WHERE userid=%d AND tip="tiket"',$_SESSION['id']);
    if(!empty($a)){
      if($a['dop'] <= $tim){
        update('users',array('buildmy'=>$a['locid']),'id='.(int)$_SESSION['id']);
        delete('users_locvoz','userid='.(int)$_SESSION['id'].' AND tip="tiket"');
        echo "<script>parent._location.location='game.php?go=char';</script>";      
      }else{
        echo "<script>parent.mess_error('Р вЂ™РЎвЂ№ РЎвЂ¦Р С•РЎвЂљР С‘РЎвЂљР Вµ Р Р†РЎвЂ№Р С—РЎР‚РЎвЂ№Р С–Р Р…РЎС“РЎвЂљРЎРЉ Р С—РЎР‚РЎРЏР СР С• РЎРѓ Р С‘Р Т‘РЎС“РЎвЂ°Р ВµР С–Р С• РЎвЂљР ВµР С—Р В»Р С•РЎвЂ¦Р С•Р Т‘Р В°? Р вЂ™РЎР‚Р ВµР СРЎРЏ Р Р†Р В°РЎв‚¬Р ВµР С–Р С• Р С—РЎР‚Р С‘Р В±РЎвЂ№РЎвЂљР С‘РЎРЏ: ".$a['dop'].".<br>Р СљР С•Р В¶Р ВµРЎвЂљ Р В±РЎвЂ№РЎвЂљРЎРЉ РЎРѓРЎвЂљР С•Р С‘РЎвЂљ Р Т‘Р С•Р В¶Р Т‘Р В°РЎвЂљРЎРЉРЎРѓРЎРЏ?','block');</script>";
      }      
    }else{
        echo "<script>parent.mess_error('Р СџР С•РЎвЂ¦Р С•Р Т‘РЎС“, Р вЂ™РЎвЂ№ РЎвЂљРЎС“РЎвЂљ Р В·Р В°РЎРѓРЎвЂљРЎР‚РЎРЏР В»Р С‘. <br> Р вЂ™Р С•Р В·Р Р…Р С‘Р С”Р В»Р В° Р С•РЎв‚¬Р С‘Р В±Р С”Р В°, Р С•Р В±РЎР‚Р В°РЎвЂљР С‘РЎвЂљР ВµРЎРѓРЎРЉ Р С” Р С’Р Т‘Р СР С‘Р Р…Р С‘РЎРѓРЎвЂљРЎР‚Р В°РЎвЂљР С•РЎР‚РЎС“. <br> Р С’ Р С—Р С•Р С”Р В° РЎвЂЎРЎвЂљР С• Р С—Р С•Р СР С•Р в„–РЎвЂљР Вµ Р С—Р С•Р В»РЎвЂ№ Р Р…Р В° Р С—Р В°Р В»РЎС“Р В±Р Вµ :D!','block');</script>";
    }
  }
    $name = 'Р СћР ВµР С—Р В»Р С•РЎвЂ¦Р С•Р Т‘';
    $about = 'Р С›Р С–РЎР‚Р С•Р СР Р…РЎвЂ№Р в„–, Р В±Р ВµР В»Р С•РЎРѓР Р…Р ВµР В¶Р Р…РЎвЂ№Р в„– РЎвЂљР ВµР С—Р В»Р С•РЎвЂ¦Р С•Р Т‘. 
              Р С›Р Р… Р С—РЎР‚Р ВµР С”РЎР‚Р В°РЎРѓР ВµР Р…. Р СљР Р…Р С•Р В¶Р ВµРЎРѓРЎвЂљР Р†Р С• Р С—Р В°Р В»РЎС“Р В±. Р В Р Р…Р В° Р С”Р В°Р В¶Р Т‘Р С•Р в„– Р С‘Р В· Р Р…Р С‘РЎвЂ¦ РЎР‚Р В°Р В·Р В»Р С‘РЎвЂЎР Р…РЎвЂ№Р Вµ РЎС“Р Т‘Р С•Р В±РЎРѓРЎвЂљР Р†Р В° Р Т‘Р В»РЎРЏ Р СР С•РЎР‚РЎРѓР С”Р С•Р С–Р С• Р С—РЎС“РЎвЂљР ВµРЎв‚¬Р ВµРЎРѓРЎвЂљР Р†Р С‘РЎРЏ. 
              Р вЂР В°РЎР‚РЎвЂ№, Р С”Р В°РЎвЂћР Вµ, Р В±Р В°РЎРѓРЎРѓР ВµР в„–Р Р…РЎвЂ№, РЎвЂљРЎР‚Р ВµР Р…Р ВµРЎР‚РЎРѓР С”Р С‘Р Вµ Р С—Р В»Р С•РЎвЂ°Р В°Р Т‘Р С”Р С‘ Р С‘ Р С”Р В°РЎР‹РЎвЂљРЎвЂ№. 
              Р В­РЎвЂљР С•РЎвЂљ РЎвЂљР ВµР С—Р В»Р С•РЎвЂ¦Р С•Р Т‘ Р С•Р В±РЎвЂ№РЎвЂЎР Р…Р С• Р С‘РЎРѓР С—Р С•Р В»РЎРЉР В·Р С•Р Р†Р В°Р В»РЎРѓРЎРЏ Р С•Р Т‘Р Р…Р С‘Р С Р С‘Р В· Р СР С‘Р В»Р В»Р С•Р Р…Р ВµРЎР‚Р С•Р Р† Р вЂќР В¶Р С•РЎвЂљРЎвЂљР С• Р Т‘Р В»РЎРЏ РЎР‚Р В°Р В·Р В»Р С‘РЎвЂЎР Р…РЎвЂ№РЎвЂ¦ Р С”РЎР‚РЎС“Р С‘Р В·Р С•Р Р†.';
    $pers = '...';
    $move = '<a href="/game.php?go=char&do=1" target="_chat_two">Р вЂ™РЎвЂ№РЎвЂ¦Р С•Р Т‘</a>';
?>
<script type="text/javascript"> (function (w, d) { function ajaxGET(url, cb){var x=new XMLHttpRequest();x.open('GET',url,true); try{x.setRequestHeader('X-Requested-With','XMLHttpRequest');}catch(e){} x.onreadystatechange=function(){if(x.readyState===4){var r=null;try{r=JSON.parse(x.responseText);}catch(e){}cb(r,x.status);}}; x.send(null); } function sameOrigin(url){var a=d.createElement('a');a.href=url;return a.host===w.location.host;} function quickGo(href){ w.location.replace(href); } d.addEventListener('click', function(e){ if (e.defaultPrevented || e.button!==0 || e.metaKey||e.ctrlKey||e.shiftKey||e.altKey) return; var a=e.target; while(a && a.tagName!=='A') a=a.parentNode; if(!a||!a.getAttribute) return; var href=a.getAttribute('href')||''; if(!href) return; if(!sameOrigin(href)) return; if (!/\/game\.php\?/.test(href)) return; if (/\bgo=charWork\b/i.test(href)) { e.preventDefault ? e.preventDefault() : (e.returnValue=false); var url = href + (href.indexOf('?')>-1?'&':'?') + 'ajax=1'; ajaxGET(url, function(r){ if (r && r.ok && r.redirect) quickGo(r.redirect); else quickGo('/game.php?go=map'); }); return; } if (/\bgo=map\b/i.test(href) && (/\bgets=/.test(href) || /\bnapadenie=/.test(href))) { e.preventDefault ? e.preventDefault() : (e.returnValue=false); var url2 = href + (href.indexOf('?')>-1?'&':'?') + 'ajax=1'; ajaxGET(url2, function(r){ quickGo('/game.php?go=map'); }); return; } }, true); var _orig_loc = w.loc; function _loc(u){ if (typeof u!=='string'){ if(_orig_loc) try{_orig_loc(u);}catch(e){} return; } if (/^charWork(\b|&)/i.test(u) || /\bgo=charWork\b/i.test(u)){ var full = /^go=/.test(u) ? ('/game.php?'+u) : ('/game.php?go='+u); var url = full + (full.indexOf('?')>-1?'&':'?') + 'ajax=1'; ajaxGET(url, function(r){ if(r&&r.ok&&r.redirect) quickGo(r.redirect); else quickGo('/game.php?go=map'); }); return; } quickGo(/^go=/.test(u)?('/game.php?'+u):('/game.php?go='+u)); } w.loc = _loc; try{ if(w.parent) w.parent.loc = _loc; }catch(e){} try{ if(w.top) w.top.loc=_loc; }catch(e){} })(window, document); </script>
