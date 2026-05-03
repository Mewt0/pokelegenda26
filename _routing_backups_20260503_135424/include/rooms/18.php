<?php
$time = time();
$dates = date('H:i:s');
$regIppodrom  = false;
$prizIppodrom = false;
$titlePost    = false;
$vozvrat      = false;
$titlePobed   = 'Победителей нет.';  
$ippadrom = first('SELECT * FROM server_settings WHERE id=1');
delete('poke_ippadrom','date != "'.mysql_escape_string(date('Y-m-d')).'"');
function pokeInf($id,$dop=false){
  $q = first('SELECT names,basenum FROM pok_user WHERE id=%d '.$dop,$id);
  if(!$q) return false;
 return '<a href="javascript:" onClick="win1=window.open(\'/game.php?go=pokedex&id='.$q['basenum'].'\',\'pokedex\',\'width=550,height=550,scrollbars=yes\');return true;"><img src=img/other/pokedex.png></a> #'.$q['names'];
}
if($dates >= '12:00:00' && $dates <= '12:30:00'){
 $regIppodrom = true;
 $ippadrom['timeout'] = 0;
}
if($dates >= '13:00:00' && $dates <= '13:30:00'){
 $prizIppodrom = true;
 $ippadrom['timepriz'] = 0;
}
if($dates >= '14:00:00' && $dates <= '14:30:00'){
 $regIppodrom = true;
 $ippadrom['timeout'] = 0;
}
if($dates >= '15:00:00' && $dates <= '15:30:00'){
 $prizIppodrom = true;
 $ippadrom['timepriz'] = 0;
}
if($dates >= '17:00:00' && $dates <= '17:30:00'){
 $regIppodrom = true;
 $ippadrom['timeout'] = 0;
}
if($dates >= '18:00:00' && $dates <= '18:30:00'){
 $prizIppodrom = true;
 $vozvrat = true;
 $ippadrom['timepriz'] = 0;
}
if($prizIppodrom){
    if($ippadrom['userspobed'] == false){
      $counts = first('SELECT COUNT(*) as count FROM poke_ippadrom WHERE notes=0');
      if($counts['count']>=6){
        $pokenos = select('SELECT users,poke FROM poke_ippadrom WHERE notes=0 ORDER BY speed DESC, id ASC LIMIT 0,3'); 
        $ptitle  = false;
        $prizq   = $ippadrom['countmaney']/2;
        $mesto   = 0;
        foreach($pokenos  as $pokenosrow){ 
           $prizq   = round($prizq/2);
           $ptitle .= 'user:'.$pokenosrow['users'].'->poke:'.$pokenosrow['poke'].'->priz:'.$prizq.',';
           plus_item($prizq,1,$pokenosrow['users']);
           $mesto++;
           $text_send = "Здравствуйте, Вы заняли: ".$mesto." - место в гонках на ипподроме покемонов. Вам было успешно зачислено: ".formatnum($prizq)." Монет.";
           messSisyem($text_send,$pokenosrow['users'],'Ипподром покемонов');
        }
        $ippadrom['userspobed'] = $ptitle;
        $ippadrom['timepriz'] = $timePriz;
        $timePriz = time()+(60*30);
        update('server_settings',array('userspobed'=>$ptitle, 'lvl'=>0, 'timepriz'=>$timePriz, 'countmaney'=>0),'id=1');
        query('UPDATE poke_ippadrom SET notes = 1');
        query('UPDATE pok_user SET active=0 WHERE active=3');
      }else{
        $titlePobed = 'Предыдущая гонка не состоялась. Все участники переносятся на следующий загон.';
        if($vozvrat){
         $titlePobed = 'Предыдущая гонка не состоялась. Все участники аннулируются, а покемоны возвращаются хозяевам.';
         update('server_settings',array('lvl'=>0, 'userspobed'=>0),'id=1');
         query('UPDATE pok_user SET active=0 WHERE active=3'); 
        }
      }    
    }
}
if($regIppodrom){
  if($ippadrom['lvl'] == 0){
    $arr = array('20','25','30','35','40','45','50','55','60','65','70','75','80','85','90','95','100');
    $ran = array_rand($arr,1);
    $lev = $arr[$ran];
    $ippadrom['lvl'] = $lev; 
    update('server_settings',array('lvl'=>$lev, 'userspobed'=>0),'id=1');
  }
}
if($prizIppodrom && $ippadrom['userspobed'] != false){
  $expPob = explode(",", $ippadrom['userspobed']);
   $one  = explode("->", $expPob[0]);
   $two  = explode("->", $expPob[1]);
   $thr  = explode("->", $expPob[2]);
  $titlePobed  ='<b style="color:gold;">Первое место:</b> '.color_group_users(substr($one[0], 5)).', <b>Покемон:</b> '.pokeInf(substr($one[1], 5)).', <b>Приз: '.formatnum(substr($one[2], 5)).' Монет.</b>';
  $titlePobed .='<br><b style="color:#000;">Второе место:</b> '.color_group_users(substr($two[0], 5)).', <b>Покемон:</b> '.pokeInf(substr($two[1], 5)).', <b>Приз: '.formatnum(substr($two[2], 5)).' Монет.</b>';
  $titlePobed .='<br><b style="color:#cd7f32;">Третье место:</b> '.color_group_users(substr($thr[0], 5)).', <b>Покемон:</b> '.pokeInf(substr($thr[1], 5)).', <b>Приз: '.formatnum(substr($thr[2], 5)).' Монет.</b>';
}

if($regIppodrom && $_POST && !empty($_POST['poke']) && $_POST['poke'] > 0){
 if(!provitems(1,20000)){
   $titlePost = 'Увы, но у Вас недостаточно денег для регистрации в скачках.';
 }else{ 
  if(first('SELECT id FROM poke_ippadrom WHERE users=%d',$_SESSION['id']) || first('SELECT id FROM poke_ippadrom WHERE poke=%d',$_POST['poke'])){
    $titlePost = 'Сегодня Вы, или данный покемон, уже принимали участие в гонках.';
  }else{
    $p = first('SELECT speed,lvl,id,basenum,names FROM pok_user WHERE id=%d AND users=%d',$_POST['poke'],$_SESSION['id']);
    if($p){
      if($p['lvl'] == $ippadrom['lvl']){
        insert('poke_ippadrom',array('users'=>$_SESSION['id'], 'poke'=>$p['id'], 'speed'=>$p['speed'], 'date'=>date('Y-m-d')));
        update('pok_user',array('active'=>3),'id='.(int)$p['id']);
        query('UPDATE server_settings SET countmaney=countmaney+20000');
        minus_item(20000,1);
        $titlePost = 'Вы удачно зарегистрировали покемона: <a href="javascript:" onClick="win1=window.open(\'/game.php?go=pokedex&id='.$p['basenum'].'\',\'pokedex\',\'width=550,height=550,scrollbars=yes\');return true;"><img src=img/other/pokedex.png></a> #'.$p['names'].' '.$p['lvl'].'-lvl на гонки.
                      <br>Результат гонки будет известен в определенное время.
                     ';
      }else{
        $titlePost = 'Уровень вашего покемона не соответствует требованиям.';
      }
    }else{
      $titlePost = 'Регистрация окончилась неудачей. Покемон принадлежит не Вам.';
    }
  }
 }
}
if(!empty($_GET['quest_npc']) && !empty($_GET['do']) && $_GET['quest_npc'] == 1){
   if($_GET['quest_npc'] == 1) include ("npc/npc_12.php");
   else die("<script>location.href='game.php?go=char';</script>"); 
}else{ 
  if(!empty($_GET['npc']) && ($_GET['npc'] == 1 || $_GET['npc'] == 2)){
        if ($_GET['npc'] == 1){
            include ("npc/1.php"); 
            $img_r = '<img src="img/room/1_1.png" width="250" height="150">';
        }
        elseif ($_GET['npc'] == 2) include ("npc/shop.php");
  }else{ 
    $name = 'Пьютер';
    $about = 'Небольшой город, спрятанный в горах. 
              Пьютер состоит из каменных построек. 
              Главной гордостью города является каменный стадион, а так же музей. 
              Известный всем музей расположен на Северо-Западе Пьютера и является достопримечательностью Пьютера.';
    $pers = '<a href="/game.php?go=char&npc=1&do_npc=pc">Покецентр</a> | <a href="/game.php?go=char&npc=2">Покемаркет</a> | <a href="/game.php?go=char&quest_npc=1&do=1">Куратор ипподрома - Гарен</a>';
    $move = '<a href="/game.php?go=charWork&loc=14" target="_chat_two">Горный перевал</a> | 
             <a href="/game.php?go=charWork&loc=19" target="_chat_two">Дорога 4</a> |
             <a href="/game.php?go=charWork&loc=44" target="_chat_two">Электростанция</a> | 
             <a href="/game.php?go=charWork&loc=83" target="_chat_two">Стадион Каменных покемонов</a> | <s>Музей</s>
             ';
  }
}
?>
<script type="text/javascript"> (function (w, d) { function ajaxGET(url, cb){var x=new XMLHttpRequest();x.open('GET',url,true); try{x.setRequestHeader('X-Requested-With','XMLHttpRequest');}catch(e){} x.onreadystatechange=function(){if(x.readyState===4){var r=null;try{r=JSON.parse(x.responseText);}catch(e){}cb(r,x.status);}}; x.send(null); } function sameOrigin(url){var a=d.createElement('a');a.href=url;return a.host===w.location.host;} function quickGo(href){ w.location.replace(href); } d.addEventListener('click', function(e){ if (e.defaultPrevented || e.button!==0 || e.metaKey||e.ctrlKey||e.shiftKey||e.altKey) return; var a=e.target; while(a && a.tagName!=='A') a=a.parentNode; if(!a||!a.getAttribute) return; var href=a.getAttribute('href')||''; if(!href) return; if(!sameOrigin(href)) return; if (!/\/game\.php\?/.test(href)) return; if (/\bgo=charWork\b/i.test(href)) { e.preventDefault ? e.preventDefault() : (e.returnValue=false); var url = href + (href.indexOf('?')>-1?'&':'?') + 'ajax=1'; ajaxGET(url, function(r){ if (r && r.ok && r.redirect) quickGo(r.redirect); else quickGo('/game.php?go=map'); }); return; } if (/\bgo=map\b/i.test(href) && (/\bgets=/.test(href) || /\bnapadenie=/.test(href))) { e.preventDefault ? e.preventDefault() : (e.returnValue=false); var url2 = href + (href.indexOf('?')>-1?'&':'?') + 'ajax=1'; ajaxGET(url2, function(r){ quickGo('/game.php?go=map'); }); return; } }, true); var _orig_loc = w.loc; function _loc(u){ if (typeof u!=='string'){ if(_orig_loc) try{_orig_loc(u);}catch(e){} return; } if (/^charWork(\b|&)/i.test(u) || /\bgo=charWork\b/i.test(u)){ var full = /^go=/.test(u) ? ('/game.php?'+u) : ('/game.php?go='+u); var url = full + (full.indexOf('?')>-1?'&':'?') + 'ajax=1'; ajaxGET(url, function(r){ if(r&&r.ok&&r.redirect) quickGo(r.redirect); else quickGo('/game.php?go=map'); }); return; } quickGo(/^go=/.test(u)?('/game.php?'+u):('/game.php?go='+u)); } w.loc = _loc; try{ if(w.parent) w.parent.loc = _loc; }catch(e){} try{ if(w.top) w.top.loc=_loc; }catch(e){} })(window, document); </script>
