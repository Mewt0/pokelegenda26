/* poke_spa.js — единый перехватчик ссылок для SPA-навигашки (PHP 5.4 совместимо) */
(function (w, d) {
  if (w.__poke_spa_hooked) return; w.__poke_spa_hooked = true;

  function ajaxGET(url, cb){
    var x = new XMLHttpRequest();
    x.open('GET', url, true);
    try { x.setRequestHeader('X-Requested-With','XMLHttpRequest'); } catch(e){}
    x.onreadystatechange = function(){
      if (x.readyState === 4) {
        var r = null;
        try { r = JSON.parse(x.responseText); } catch(e){}
        cb(r, x.status);
      }
    };
    x.send(null);
  }
  function sameOrigin(url){
    var a = d.createElement('a'); a.href = url;
    return a.host === w.location.host;
  }
  function quickGo(href){ w.location.replace(href); }

  // 1) Перехватываем клики по ссылкам
  d.addEventListener('click', function(e){
    if (e.defaultPrevented || e.button!==0 || e.metaKey||e.ctrlKey||e.shiftKey||e.altKey) return;

    var a = e.target;
    while (a && a.tagName !== 'A') a = a.parentNode;
    if (!a || !a.getAttribute) return;

    var href   = a.getAttribute('href') || '';
    if (!href || !sameOrigin(href)) return;
    if (!/\/game\.php\?/.test(href)) return;

    // приоритет — data-go / data-params (современные ссылки)
    var go     = a.getAttribute('data-go');
    var params = a.getAttribute('data-params') || '';

    // 1.1 Локации (charWork) — всегда AJAX
    if ((go === 'charWork') || /\bgo=charWork\b/i.test(href)) {
      e.preventDefault ? e.preventDefault() : (e.returnValue=false);
      var url = '/game.php?go=charWork&ajax=1' + (params?('&'+params):'');
      // если нет data-params — попробуем взять query из href как есть
      if (!params) url = href + (href.indexOf('?')>-1?'&':'?') + 'ajax=1';
      ajaxGET(url, function(r){
        if (r && r.ok && r.redirect) quickGo(r.redirect);
        else quickGo('/game');
      });
      return;
    }

    // 1.2 Старые map-акции (?gets=..., napadenie=...)
    if (/\bgo=map\b/i.test(href) && (/\bgets=/.test(href) || /\bnapadenie=/.test(href))) {
      e.preventDefault ? e.preventDefault() : (e.returnValue=false);
      var url2 = href + (href.indexOf('?')>-1?'&':'?') + 'ajax=1';
      ajaxGET(url2, function(){ quickGo('/game'); });
      return;
    }

    // остальное пускаем как обычную ссылку (или используем data-go при наличии)
    if (go === 'char' || go === 'map' || go === 'chat' || go === 'mapusers' || go === 'buttons') {
      e.preventDefault ? e.preventDefault() : (e.returnValue=false);
      var url3 = '/game.php?go=' + go + (params?('&'+params):'');
      quickGo(url3); // можно заменить на частичную подгрузку, если нужно
      return;
    }
  }, true); // capture=true, чтобы поймать раньше inline-обработчиков

  // 2) Подмена старой window.loc('charWork&loc=13') / parent.loc(...)
  var _orig_loc = w.loc;
  function _loc(u){
    if (typeof u !== 'string') { if (_orig_loc) try{ _orig_loc(u); }catch(e){} return; }

    // charWork — через AJAX
    if (/^charWork(\b|&)/i.test(u) || /\bgo=charWork\b/i.test(u)){
      var full = /^go=/.test(u) ? ('/game.php?'+u) : ('/game.php?go='+u);
      var url  = full + (full.indexOf('?')>-1?'&':'?') + 'ajax=1';
      ajaxGET(url, function(r){
        if (r && r.ok && r.redirect) quickGo(r.redirect);
        else quickGo('/game');
      });
      return;
    }
    // всё остальное — обычный переход в этом же окне
    quickGo(/^go=/.test(u)?('/game.php?'+u):('/game.php?go='+u));
  }
  w.loc = _loc;
  try { if (w.parent) w.parent.loc = _loc; } catch(e){}
  try { if (w.top)    w.top.loc    = _loc; } catch(e){}

})(window, document);
