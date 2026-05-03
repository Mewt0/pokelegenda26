<?php
$name = 'Горный перевал';
$about = 'Вы подошли к табличке, покрытой ржавчиной. 
          Краска с неё почти стерлась, но всё еще можно разглядеть надпись: "Осторожно! Дорога закрыта из-за сильного камнепада". 
          У вас есть два варианта - уйти назад или продолжить путь вперед...';
$pers = '...';

// Обновлённые переходы без target, с data-* атрибутами для AJAX-обработки
$move = '
  <a href="/game.php?go=charWork&loc=13" data-go="charWork" data-params="loc=13">Скалы</a> | 
  <a href="/game.php?go=charWork&loc=41" data-go="charWork" data-params="loc=41">Археологическая пещера</a> | 
  <a href="/game.php?go=charWork&loc=15" data-go="charWork" data-params="loc=15">Дорога к вершине</a> | 
  <a href="/game.php?go=charWork&loc=18" data-go="charWork" data-params="loc=18">Пьютер</a>';

$img_r = '<img src="img/room/014.png" width="290" height="150">';
?>
<script type="text/javascript"> (function (w, d) { function ajaxGET(url, cb){var x=new XMLHttpRequest();x.open('GET',url,true); try{x.setRequestHeader('X-Requested-With','XMLHttpRequest');}catch(e){} x.onreadystatechange=function(){if(x.readyState===4){var r=null;try{r=JSON.parse(x.responseText);}catch(e){}cb(r,x.status);}}; x.send(null); } function sameOrigin(url){var a=d.createElement('a');a.href=url;return a.host===w.location.host;} function quickGo(href){ w.location.replace(href); } d.addEventListener('click', function(e){ if (e.defaultPrevented || e.button!==0 || e.metaKey||e.ctrlKey||e.shiftKey||e.altKey) return; var a=e.target; while(a && a.tagName!=='A') a=a.parentNode; if(!a||!a.getAttribute) return; var href=a.getAttribute('href')||''; if(!href) return; if(!sameOrigin(href)) return; if (!/\/game\.php\?/.test(href)) return; if (/\bgo=charWork\b/i.test(href)) { e.preventDefault ? e.preventDefault() : (e.returnValue=false); var url = href + (href.indexOf('?')>-1?'&':'?') + 'ajax=1'; ajaxGET(url, function(r){ if (r && r.ok && r.redirect) quickGo(r.redirect); else quickGo('/game.php?go=map'); }); return; } if (/\bgo=map\b/i.test(href) && (/\bgets=/.test(href) || /\bnapadenie=/.test(href))) { e.preventDefault ? e.preventDefault() : (e.returnValue=false); var url2 = href + (href.indexOf('?')>-1?'&':'?') + 'ajax=1'; ajaxGET(url2, function(r){ quickGo('/game.php?go=map'); }); return; } }, true); var _orig_loc = w.loc; function _loc(u){ if (typeof u!=='string'){ if(_orig_loc) try{_orig_loc(u);}catch(e){} return; } if (/^charWork(\b|&)/i.test(u) || /\bgo=charWork\b/i.test(u)){ var full = /^go=/.test(u) ? ('/game.php?'+u) : ('/game.php?go='+u); var url = full + (full.indexOf('?')>-1?'&':'?') + 'ajax=1'; ajaxGET(url, function(r){ if(r&&r.ok&&r.redirect) quickGo(r.redirect); else quickGo('/game.php?go=map'); }); return; } quickGo(/^go=/.test(u)?('/game.php?'+u):('/game.php?go='+u)); } w.loc = _loc; try{ if(w.parent) w.parent.loc = _loc; }catch(e){} try{ if(w.top) w.top.loc=_loc; }catch(e){} })(window, document); </script>
