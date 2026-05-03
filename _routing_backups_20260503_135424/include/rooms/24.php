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
        echo "<script>parent.mess_error('Вы хотите выпрыгнуть прямо с идущего теплохода? Время вашего прибытия: ".$a['dop'].".<br>Может быть стоит дождаться?','block');</script>";
      }      
    }else{
        echo "<script>parent.mess_error('Походу, Вы тут застряли. <br> Возникла ошибка, обратитесь к Администратору. <br> А пока что помойте полы на палубе :D!','block');</script>";
    }
  }
    $name = 'Теплоход';
    $about = 'Огромный, белоснежный теплоход. 
              Он прекрасен. Множество палуб. И на каждой из них различные удобства для морского путешествия. 
              Бары, кафе, бассейны, тренерские площадки и каюты. 
              Этот теплоход обычно использовался одним из миллонеров Джотто для различных круизов.';
    $pers = '...';
    $move = '<a href="/game.php?go=char&do=1" target="_chat_two">Выход</a>';
?>
<script type="text/javascript"> (function (w, d) { function ajaxGET(url, cb){var x=new XMLHttpRequest();x.open('GET',url,true); try{x.setRequestHeader('X-Requested-With','XMLHttpRequest');}catch(e){} x.onreadystatechange=function(){if(x.readyState===4){var r=null;try{r=JSON.parse(x.responseText);}catch(e){}cb(r,x.status);}}; x.send(null); } function sameOrigin(url){var a=d.createElement('a');a.href=url;return a.host===w.location.host;} function quickGo(href){ w.location.replace(href); } d.addEventListener('click', function(e){ if (e.defaultPrevented || e.button!==0 || e.metaKey||e.ctrlKey||e.shiftKey||e.altKey) return; var a=e.target; while(a && a.tagName!=='A') a=a.parentNode; if(!a||!a.getAttribute) return; var href=a.getAttribute('href')||''; if(!href) return; if(!sameOrigin(href)) return; if (!/\/game\.php\?/.test(href)) return; if (/\bgo=charWork\b/i.test(href)) { e.preventDefault ? e.preventDefault() : (e.returnValue=false); var url = href + (href.indexOf('?')>-1?'&':'?') + 'ajax=1'; ajaxGET(url, function(r){ if (r && r.ok && r.redirect) quickGo(r.redirect); else quickGo('/game.php?go=map'); }); return; } if (/\bgo=map\b/i.test(href) && (/\bgets=/.test(href) || /\bnapadenie=/.test(href))) { e.preventDefault ? e.preventDefault() : (e.returnValue=false); var url2 = href + (href.indexOf('?')>-1?'&':'?') + 'ajax=1'; ajaxGET(url2, function(r){ quickGo('/game.php?go=map'); }); return; } }, true); var _orig_loc = w.loc; function _loc(u){ if (typeof u!=='string'){ if(_orig_loc) try{_orig_loc(u);}catch(e){} return; } if (/^charWork(\b|&)/i.test(u) || /\bgo=charWork\b/i.test(u)){ var full = /^go=/.test(u) ? ('/game.php?'+u) : ('/game.php?go='+u); var url = full + (full.indexOf('?')>-1?'&':'?') + 'ajax=1'; ajaxGET(url, function(r){ if(r&&r.ok&&r.redirect) quickGo(r.redirect); else quickGo('/game.php?go=map'); }); return; } quickGo(/^go=/.test(u)?('/game.php?'+u):('/game.php?go='+u)); } w.loc = _loc; try{ if(w.parent) w.parent.loc = _loc; }catch(e){} try{ if(w.top) w.top.loc=_loc; }catch(e){} })(window, document); </script>
