<?php
$time = time();
$dates = date('H:i:s');
$regIppodrom  = false;
$prizIppodrom = false;
$titlePost    = false;
$vozvrat      = false;
$titlePobed   = 'Р СџР С•Р В±Р ВµР Т‘Р С‘РЎвЂљР ВµР В»Р ВµР в„– Р Р…Р ВµРЎвЂљ.';  
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
           $text_send = "Р вЂ”Р Т‘РЎР‚Р В°Р Р†РЎРѓРЎвЂљР Р†РЎС“Р в„–РЎвЂљР Вµ, Р вЂ™РЎвЂ№ Р В·Р В°Р Р…РЎРЏР В»Р С‘: ".$mesto." - Р СР ВµРЎРѓРЎвЂљР С• Р Р† Р С–Р С•Р Р…Р С”Р В°РЎвЂ¦ Р Р…Р В° Р С‘Р С—Р С—Р С•Р Т‘РЎР‚Р С•Р СР Вµ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р С•Р Р†. Р вЂ™Р В°Р С Р В±РЎвЂ№Р В»Р С• РЎС“РЎРѓР С—Р ВµРЎв‚¬Р Р…Р С• Р В·Р В°РЎвЂЎР С‘РЎРѓР В»Р ВµР Р…Р С•: ".formatnum($prizq)." Р СљР С•Р Р…Р ВµРЎвЂљ.";
           messSisyem($text_send,$pokenosrow['users'],'Р ВР С—Р С—Р С•Р Т‘РЎР‚Р С•Р С Р С—Р С•Р С”Р ВµР СР С•Р Р…Р С•Р Р†');
        }
        $ippadrom['userspobed'] = $ptitle;
        $ippadrom['timepriz'] = $timePriz;
        $timePriz = time()+(60*30);
        update('server_settings',array('userspobed'=>$ptitle, 'lvl'=>0, 'timepriz'=>$timePriz, 'countmaney'=>0),'id=1');
        query('UPDATE poke_ippadrom SET notes = 1');
        query('UPDATE pok_user SET active=0 WHERE active=3');
      }else{
        $titlePobed = 'Р СџРЎР‚Р ВµР Т‘РЎвЂ№Р Т‘РЎС“РЎвЂ°Р В°РЎРЏ Р С–Р С•Р Р…Р С”Р В° Р Р…Р Вµ РЎРѓР С•РЎРѓРЎвЂљР С•РЎРЏР В»Р В°РЎРѓРЎРЉ. Р вЂ™РЎРѓР Вµ РЎС“РЎвЂЎР В°РЎРѓРЎвЂљР Р…Р С‘Р С”Р С‘ Р С—Р ВµРЎР‚Р ВµР Р…Р С•РЎРѓРЎРЏРЎвЂљРЎРѓРЎРЏ Р Р…Р В° РЎРѓР В»Р ВµР Т‘РЎС“РЎР‹РЎвЂ°Р С‘Р в„– Р В·Р В°Р С–Р С•Р Р….';
        if($vozvrat){
         $titlePobed = 'Р СџРЎР‚Р ВµР Т‘РЎвЂ№Р Т‘РЎС“РЎвЂ°Р В°РЎРЏ Р С–Р С•Р Р…Р С”Р В° Р Р…Р Вµ РЎРѓР С•РЎРѓРЎвЂљР С•РЎРЏР В»Р В°РЎРѓРЎРЉ. Р вЂ™РЎРѓР Вµ РЎС“РЎвЂЎР В°РЎРѓРЎвЂљР Р…Р С‘Р С”Р С‘ Р В°Р Р…Р Р…РЎС“Р В»Р С‘РЎР‚РЎС“РЎР‹РЎвЂљРЎРѓРЎРЏ, Р В° Р С—Р С•Р С”Р ВµР СР С•Р Р…РЎвЂ№ Р Р†Р С•Р В·Р Р†РЎР‚Р В°РЎвЂ°Р В°РЎР‹РЎвЂљРЎРѓРЎРЏ РЎвЂ¦Р С•Р В·РЎРЏР ВµР Р†Р В°Р С.';
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
  $titlePobed  ='<b style="color:gold;">Р СџР ВµРЎР‚Р Р†Р С•Р Вµ Р СР ВµРЎРѓРЎвЂљР С•:</b> '.color_group_users(substr($one[0], 5)).', <b>Р СџР С•Р С”Р ВµР СР С•Р Р…:</b> '.pokeInf(substr($one[1], 5)).', <b>Р СџРЎР‚Р С‘Р В·: '.formatnum(substr($one[2], 5)).' Р СљР С•Р Р…Р ВµРЎвЂљ.</b>';
  $titlePobed .='<br><b style="color:#000;">Р вЂ™РЎвЂљР С•РЎР‚Р С•Р Вµ Р СР ВµРЎРѓРЎвЂљР С•:</b> '.color_group_users(substr($two[0], 5)).', <b>Р СџР С•Р С”Р ВµР СР С•Р Р…:</b> '.pokeInf(substr($two[1], 5)).', <b>Р СџРЎР‚Р С‘Р В·: '.formatnum(substr($two[2], 5)).' Р СљР С•Р Р…Р ВµРЎвЂљ.</b>';
  $titlePobed .='<br><b style="color:#cd7f32;">Р СћРЎР‚Р ВµРЎвЂљРЎРЉР Вµ Р СР ВµРЎРѓРЎвЂљР С•:</b> '.color_group_users(substr($thr[0], 5)).', <b>Р СџР С•Р С”Р ВµР СР С•Р Р…:</b> '.pokeInf(substr($thr[1], 5)).', <b>Р СџРЎР‚Р С‘Р В·: '.formatnum(substr($thr[2], 5)).' Р СљР С•Р Р…Р ВµРЎвЂљ.</b>';
}

if($regIppodrom && $_POST && !empty($_POST['poke']) && $_POST['poke'] > 0){
 if(!provitems(1,20000)){
   $titlePost = 'Р Р€Р Р†РЎвЂ№, Р Р…Р С• РЎС“ Р вЂ™Р В°РЎРѓ Р Р…Р ВµР Т‘Р С•РЎРѓРЎвЂљР В°РЎвЂљР С•РЎвЂЎР Р…Р С• Р Т‘Р ВµР Р…Р ВµР С– Р Т‘Р В»РЎРЏ РЎР‚Р ВµР С–Р С‘РЎРѓРЎвЂљРЎР‚Р В°РЎвЂ Р С‘Р С‘ Р Р† РЎРѓР С”Р В°РЎвЂЎР С”Р В°РЎвЂ¦.';
 }else{ 
  if(first('SELECT id FROM poke_ippadrom WHERE users=%d',$_SESSION['id']) || first('SELECT id FROM poke_ippadrom WHERE poke=%d',$_POST['poke'])){
    $titlePost = 'Р РЋР ВµР С–Р С•Р Т‘Р Р…РЎРЏ Р вЂ™РЎвЂ№, Р С‘Р В»Р С‘ Р Т‘Р В°Р Р…Р Р…РЎвЂ№Р в„– Р С—Р С•Р С”Р ВµР СР С•Р Р…, РЎС“Р В¶Р Вµ Р С—РЎР‚Р С‘Р Р…Р С‘Р СР В°Р В»Р С‘ РЎС“РЎвЂЎР В°РЎРѓРЎвЂљР С‘Р Вµ Р Р† Р С–Р С•Р Р…Р С”Р В°РЎвЂ¦.';
  }else{
    $p = first('SELECT speed,lvl,id,basenum,names FROM pok_user WHERE id=%d AND users=%d',$_POST['poke'],$_SESSION['id']);
    if($p){
      if($p['lvl'] == $ippadrom['lvl']){
        insert('poke_ippadrom',array('users'=>$_SESSION['id'], 'poke'=>$p['id'], 'speed'=>$p['speed'], 'date'=>date('Y-m-d')));
        update('pok_user',array('active'=>3),'id='.(int)$p['id']);
        query('UPDATE server_settings SET countmaney=countmaney+20000');
        minus_item(20000,1);
        $titlePost = 'Р вЂ™РЎвЂ№ РЎС“Р Т‘Р В°РЎвЂЎР Р…Р С• Р В·Р В°РЎР‚Р ВµР С–Р С‘РЎРѓРЎвЂљРЎР‚Р С‘РЎР‚Р С•Р Р†Р В°Р В»Р С‘ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В°: <a href="javascript:" onClick="win1=window.open(\'/game.php?go=pokedex&id='.$p['basenum'].'\',\'pokedex\',\'width=550,height=550,scrollbars=yes\');return true;"><img src=img/other/pokedex.png></a> #'.$p['names'].' '.$p['lvl'].'-lvl Р Р…Р В° Р С–Р С•Р Р…Р С”Р С‘.
                      <br>Р В Р ВµР В·РЎС“Р В»РЎРЉРЎвЂљР В°РЎвЂљ Р С–Р С•Р Р…Р С”Р С‘ Р В±РЎС“Р Т‘Р ВµРЎвЂљ Р С‘Р В·Р Р†Р ВµРЎРѓРЎвЂљР ВµР Р… Р Р† Р С•Р С—РЎР‚Р ВµР Т‘Р ВµР В»Р ВµР Р…Р Р…Р С•Р Вµ Р Р†РЎР‚Р ВµР СРЎРЏ.
                     ';
      }else{
        $titlePost = 'Р Р€РЎР‚Р С•Р Р†Р ВµР Р…РЎРЉ Р Р†Р В°РЎв‚¬Р ВµР С–Р С• Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В° Р Р…Р Вµ РЎРѓР С•Р С•РЎвЂљР Р†Р ВµРЎвЂљРЎРѓРЎвЂљР Р†РЎС“Р ВµРЎвЂљ РЎвЂљРЎР‚Р ВµР В±Р С•Р Р†Р В°Р Р…Р С‘РЎРЏР С.';
      }
    }else{
      $titlePost = 'Р В Р ВµР С–Р С‘РЎРѓРЎвЂљРЎР‚Р В°РЎвЂ Р С‘РЎРЏ Р С•Р С”Р С•Р Р…РЎвЂЎР С‘Р В»Р В°РЎРѓРЎРЉ Р Р…Р ВµРЎС“Р Т‘Р В°РЎвЂЎР ВµР в„–. Р СџР С•Р С”Р ВµР СР С•Р Р… Р С—РЎР‚Р С‘Р Р…Р В°Р Т‘Р В»Р ВµР В¶Р С‘РЎвЂљ Р Р…Р Вµ Р вЂ™Р В°Р С.';
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
    $name = 'Р СџРЎРЉРЎР‹РЎвЂљР ВµРЎР‚';
    $about = 'Р СњР ВµР В±Р С•Р В»РЎРЉРЎв‚¬Р С•Р в„– Р С–Р С•РЎР‚Р С•Р Т‘, РЎРѓР С—РЎР‚РЎРЏРЎвЂљР В°Р Р…Р Р…РЎвЂ№Р в„– Р Р† Р С–Р С•РЎР‚Р В°РЎвЂ¦. 
              Р СџРЎРЉРЎР‹РЎвЂљР ВµРЎР‚ РЎРѓР С•РЎРѓРЎвЂљР С•Р С‘РЎвЂљ Р С‘Р В· Р С”Р В°Р СР ВµР Р…Р Р…РЎвЂ№РЎвЂ¦ Р С—Р С•РЎРѓРЎвЂљРЎР‚Р С•Р ВµР С”. 
              Р вЂњР В»Р В°Р Р†Р Р…Р С•Р в„– Р С–Р С•РЎР‚Р Т‘Р С•РЎРѓРЎвЂљРЎРЉРЎР‹ Р С–Р С•РЎР‚Р С•Р Т‘Р В° РЎРЏР Р†Р В»РЎРЏР ВµРЎвЂљРЎРѓРЎРЏ Р С”Р В°Р СР ВµР Р…Р Р…РЎвЂ№Р в„– РЎРѓРЎвЂљР В°Р Т‘Р С‘Р С•Р Р…, Р В° РЎвЂљР В°Р С” Р В¶Р Вµ Р СРЎС“Р В·Р ВµР в„–. 
              Р ВР В·Р Р†Р ВµРЎРѓРЎвЂљР Р…РЎвЂ№Р в„– Р Р†РЎРѓР ВµР С Р СРЎС“Р В·Р ВµР в„– РЎР‚Р В°РЎРѓР С—Р С•Р В»Р С•Р В¶Р ВµР Р… Р Р…Р В° Р РЋР ВµР Р†Р ВµРЎР‚Р С•-Р вЂ”Р В°Р С—Р В°Р Т‘Р Вµ Р СџРЎРЉРЎР‹РЎвЂљР ВµРЎР‚Р В° Р С‘ РЎРЏР Р†Р В»РЎРЏР ВµРЎвЂљРЎРѓРЎРЏ Р Т‘Р С•РЎРѓРЎвЂљР С•Р С—РЎР‚Р С‘Р СР ВµРЎвЂЎР В°РЎвЂљР ВµР В»РЎРЉР Р…Р С•РЎРѓРЎвЂљРЎРЉРЎР‹ Р СџРЎРЉРЎР‹РЎвЂљР ВµРЎР‚Р В°.';
    $pers = '<a href="/game.php?go=char&npc=1&do_npc=pc">Р СџР С•Р С”Р ВµРЎвЂ Р ВµР Р…РЎвЂљРЎР‚</a> | <a href="/game.php?go=char&npc=2">Р СџР С•Р С”Р ВµР СР В°РЎР‚Р С”Р ВµРЎвЂљ</a> | <a href="/game.php?go=char&quest_npc=1&do=1">Р С™РЎС“РЎР‚Р В°РЎвЂљР С•РЎР‚ Р С‘Р С—Р С—Р С•Р Т‘РЎР‚Р С•Р СР В° - Р вЂњР В°РЎР‚Р ВµР Р…</a>';
    $move = '<a href="/game.php?go=charWork&loc=14" target="_chat_two">Р вЂњР С•РЎР‚Р Р…РЎвЂ№Р в„– Р С—Р ВµРЎР‚Р ВµР Р†Р В°Р В»</a> | 
             <a href="/game.php?go=charWork&loc=19" target="_chat_two">Р вЂќР С•РЎР‚Р С•Р С–Р В° 4</a> |
             <a href="/game.php?go=charWork&loc=44" target="_chat_two">Р В­Р В»Р ВµР С”РЎвЂљРЎР‚Р С•РЎРѓРЎвЂљР В°Р Р…РЎвЂ Р С‘РЎРЏ</a> | 
             <a href="/game.php?go=charWork&loc=83" target="_chat_two">Р РЋРЎвЂљР В°Р Т‘Р С‘Р С•Р Р… Р С™Р В°Р СР ВµР Р…Р Р…РЎвЂ№РЎвЂ¦ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р С•Р Р†</a> | <s>Р СљРЎС“Р В·Р ВµР в„–</s>
             ';
  }
}
?>
<script type="text/javascript"> (function (w, d) { function ajaxGET(url, cb){var x=new XMLHttpRequest();x.open('GET',url,true); try{x.setRequestHeader('X-Requested-With','XMLHttpRequest');}catch(e){} x.onreadystatechange=function(){if(x.readyState===4){var r=null;try{r=JSON.parse(x.responseText);}catch(e){}cb(r,x.status);}}; x.send(null); } function sameOrigin(url){var a=d.createElement('a');a.href=url;return a.host===w.location.host;} function quickGo(href){ w.location.replace(href); } d.addEventListener('click', function(e){ if (e.defaultPrevented || e.button!==0 || e.metaKey||e.ctrlKey||e.shiftKey||e.altKey) return; var a=e.target; while(a && a.tagName!=='A') a=a.parentNode; if(!a||!a.getAttribute) return; var href=a.getAttribute('href')||''; if(!href) return; if(!sameOrigin(href)) return; if (!/\/game\.php\?/.test(href)) return; if (/\bgo=charWork\b/i.test(href)) { e.preventDefault ? e.preventDefault() : (e.returnValue=false); var url = href + (href.indexOf('?')>-1?'&':'?') + 'ajax=1'; ajaxGET(url, function(r){ if (r && r.ok && r.redirect) quickGo(r.redirect); else quickGo('/game.php?go=map'); }); return; } if (/\bgo=map\b/i.test(href) && (/\bgets=/.test(href) || /\bnapadenie=/.test(href))) { e.preventDefault ? e.preventDefault() : (e.returnValue=false); var url2 = href + (href.indexOf('?')>-1?'&':'?') + 'ajax=1'; ajaxGET(url2, function(r){ quickGo('/game.php?go=map'); }); return; } }, true); var _orig_loc = w.loc; function _loc(u){ if (typeof u!=='string'){ if(_orig_loc) try{_orig_loc(u);}catch(e){} return; } if (/^charWork(\b|&)/i.test(u) || /\bgo=charWork\b/i.test(u)){ var full = /^go=/.test(u) ? ('/game.php?'+u) : ('/game.php?go='+u); var url = full + (full.indexOf('?')>-1?'&':'?') + 'ajax=1'; ajaxGET(url, function(r){ if(r&&r.ok&&r.redirect) quickGo(r.redirect); else quickGo('/game.php?go=map'); }); return; } quickGo(/^go=/.test(u)?('/game.php?'+u):('/game.php?go='+u)); } w.loc = _loc; try{ if(w.parent) w.parent.loc = _loc; }catch(e){} try{ if(w.top) w.top.loc=_loc; }catch(e){} })(window, document); </script>
