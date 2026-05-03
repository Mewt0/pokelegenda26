<?php
// chat.world.php   :  +   ( )
// PHP 5.4, mysql_*, HTML CP1251, JSON UTF-8,  : chats (author, userto, private, time, text, room, tipe)
if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
  header("Content-Type: text/html; charset=windows-1251");
}
@session_start();
@header('Content-Type: text/html; charset=cp1251');

/* ===    === */
$cfg = $_SERVER['DOCUMENT_ROOT'].'/include/config.php';
if (!file_exists($cfg)) { $cfg = dirname(__DIR__).'/config.php'; }
if (!file_exists($cfg)) { echo 'Config not found'; exit; }
require_once $cfg;

$link = @mysql_connect($config['server'], $config['user'], $config['pass']);
if (!$link) { echo '    : '.mysql_error(); exit; }
if (!@mysql_select_db($config['db'], $link)) { echo '   : '.mysql_error(); exit; }
@mysql_query("SET NAMES 'cp1251'", $link);

/* ===   === */
$CURRENT_USER_ID    = isset($_SESSION['id']) ? (int)$_SESSION['id'] : 0;
$CURRENT_USER_LOGIN = isset($_SESSION['login']) ? (string)$_SESSION['login'] : 'Guest';

/* ===  === */
$CHAT_TABLE  = 'chats';
$USERS_TABLE = 'users';
$ONLINE_TTL  = 300;
$MAX_MSG_LEN = 2000;
$FETCH_LIMIT = 120;
$ALLOWED_CHANNEL = array('all','trade','battle','private','clan');
$CHANNEL2TIPE   = array('all'=>1,'trade'=>2,'battle'=>3,'clan'=>4);
$TIPE2CHANNEL   = array(1=>'all',2=>'trade',3=>'battle',4=>'clan');
$DEFAULT_TIPE   = 1;

/* ===  === */
function str_clean($s){ $s=str_replace(array("\r\n","\r"),"\n",$s); return trim(preg_replace('~[ \t]+~',' ',$s)); }
function __u2c($s){ if ($s===''||$s===null) return $s; $r=@iconv('utf-8','cp1251//IGNORE',$s); if($r!==false&&$r!=='')return $r; $r=@utf8_decode($s); return ($r!==false)?$r:$s; }
function __to_utf8($v){ if(is_array($v)){foreach($v as $k=>$val){$v[$k]=__to_utf8($val);}return $v;} if(is_string($v))return iconv('cp1251','utf-8//IGNORE',$v); return $v; }
function jexit_json($arr){ $arr=__to_utf8($arr); header('Content-Type: application/json; charset=UTF-8'); echo json_encode($arr); exit; }

/* === room + mychat === */
$USER_MYCHAT = 1; $PVE_BUTTON=0; $CURRENT_ROOM = 1;
if ($CURRENT_USER_ID>0){
  $q = mysql_query("SELECT mychat,pve_button,buildmy FROM `users` WHERE id=".$CURRENT_USER_ID." LIMIT 1");
  if ($q && ($r=mysql_fetch_assoc($q))){
    $USER_MYCHAT=(int)$r['mychat'];
    $PVE_BUTTON=(int)$r['pve_button'];
    $CURRENT_ROOM=!empty($r['buildmy']) ? (int)$r['buildmy'] : 1;
  }
}

/* ================== AJAX ================== */

/*  / */
if (!empty($_POST['__chat_action']) && $_POST['__chat_action']==='scope'){
  if ($CURRENT_USER_ID<=0) jexit_json(array('ok'=>false,'error'=>'auth','msg'=>' .'));
  $scope = isset($_POST['scope'])?$_POST['scope']:'all';
  $val = ($scope==='room')?2:1;
  $ok = mysql_query("UPDATE `".$USERS_TABLE."` SET mychat=".$val." WHERE id=".$CURRENT_USER_ID." LIMIT 1");
  if(!$ok) jexit_json(array('ok'=>false,'error'=>'db','msg'=>mysql_error()));
  jexit_json(array('ok'=>true,'mychat'=>$val));
}

/* /  (  ) */
if (!empty($_POST['__chat_action']) && $_POST['__chat_action']==='pve'){
  if ($CURRENT_USER_ID<=0) jexit_json(array('ok'=>false,'error'=>'auth','msg'=>' .'));
  $mode = isset($_POST['mode'])?$_POST['mode']:'off';
  if ($mode==='on'){
    $b_time = time()+15;
    $ok = mysql_query("UPDATE `".$USERS_TABLE."` SET pve_button=1, atack_poke=".$b_time." WHERE id=".$CURRENT_USER_ID." LIMIT 1");
  } else {
    $ok = mysql_query("UPDATE `".$USERS_TABLE."` SET pve_button=0 WHERE id=".$CURRENT_USER_ID." LIMIT 1");
  }
  if(!$ok) jexit_json(array('ok'=>false,'error'=>'db','msg'=>mysql_error()));
  jexit_json(array('ok'=>true));
}

/*   */
if (!empty($_POST['__chat_action']) && $_POST['__chat_action']==='send'){
  if ($CURRENT_USER_ID<=0) jexit_json(array('ok'=>false,'error'=>'auth','msg'=>' .'));
  $text = isset($_POST['text'])?(string)$_POST['text']:'';
  $text = str_clean($text); if(strlen($text)>$MAX_MSG_LEN)$text=substr($text,0,$MAX_MSG_LEN);
  if ($text==='') jexit_json(array('ok'=>false,'error'=>'empty','msg'=>' .'));
  $text_c = __u2c($text);

  $channel = isset($_POST['chat_channel'])?(string)$_POST['chat_channel']:'all';
  if(!in_array($channel,$ALLOWED_CHANNEL,true)) $channel='all';
  $pm_to_id = isset($_POST['pm_to_id'])?(int)$_POST['pm_to_id']:0;

  $isPrivate = ($channel==='private');
  if ($isPrivate){
    if ($pm_to_id<=0) jexit_json(array('ok'=>false,'error'=>'no_recipient','msg'=>'   .'));
    if ($pm_to_id===$CURRENT_USER_ID) jexit_json(array('ok'=>false,'error'=>'self_pm','msg'=>'  .'));
    $q=mysql_query("SELECT id,login,online,onlinetime FROM `".$USERS_TABLE."` WHERE id=".$pm_to_id." LIMIT 1");
    $u=$q?mysql_fetch_assoc($q):null;
    if(!$u) jexit_json(array('ok'=>false,'error'=>'no_user','msg'=>'  .'));
    $isOn=((int)$u['online']===1)&&(time()-(int)$u['onlinetime'] <= $ONLINE_TTL);
    if(!$isOn) jexit_json(array('ok'=>false,'error'=>'offline','msg'=>'  .'));
  }

  $author  = $CURRENT_USER_LOGIN;
  $userto  = $isPrivate ? $pm_to_id : 0;
  $private = $isPrivate ? 1 : 0;
  $tipe    = $isPrivate ? $DEFAULT_TIPE : (isset($CHANNEL2TIPE[$channel])?(int)$CHANNEL2TIPE[$channel]:$DEFAULT_TIPE);
  $ts      = time();
  $room    = $CURRENT_ROOM;

  $sql="INSERT INTO `".$CHAT_TABLE."` (`author`,`userto`,`private`,`time`,`text`,`tipe`,`room`)
        VALUES ('".addslashes($author)."',".$userto.",".$private.",".$ts.",'".addslashes($text_c)."',".$tipe.",".$room.")";
  $ok=mysql_query($sql);
  if(!$ok) jexit_json(array('ok'=>false,'error'=>'db','msg'=>mysql_error()));
  jexit_json(array('ok'=>true));
}

/*   */
if (isset($_GET['fetch']) && (int)$_GET['fetch']===1){
  $roomFilter = ($USER_MYCHAT==2) ? (" AND c.`room`=".(int)$CURRENT_ROOM." ") : "";
  $res = mysql_query("
    SELECT c.id,c.author,c.userto,c.private,c.time AS ts,c.text AS ctext,c.tipe,c.room,
           u1.id AS from_user_id,u1.login AS from_name, u2.login AS to_name
      FROM `".$CHAT_TABLE."` c
      LEFT JOIN `".$USERS_TABLE."` u1 ON u1.login=c.author
      LEFT JOIN `".$USERS_TABLE."` u2 ON u2.id=c.userto
     WHERE 1 ".$roomFilter."
     ORDER BY c.id DESC
     LIMIT ".$FETCH_LIMIT);
  if(!$res) jexit_json(array('ok'=>false,'error'=>'db','msg'=>mysql_error()));

  $rows=array();
  while($r=mysql_fetch_assoc($res)){
    $chan = ($r['private'] ? 'private' : (isset($TIPE2CHANNEL[(int)$r['tipe']])?$TIPE2CHANNEL[(int)$r['tipe']]:'all'));
    $rows[] = array(
      'id'=>(int)$r['id'],
      'from_user_id'=> isset($r['from_user_id'])?(int)$r['from_user_id']:0,
      'to_user_id'=> (int)$r['userto'],
      'channel'=> $chan,
      'text'=> $r['ctext'],
      'created_at'=> date('Y-m-d H:i:s',(int)$r['ts']),
      'from_name'=> ($r['from_name']?$r['from_name']:$r['author']),
      'to_name'=> ($r['to_name']?$r['to_name']:'')
    );
  }
  jexit_json(array('ok'=>true,'rows'=>$rows));
}
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=cp1251" />
<title></title>
<style>
html,body{margin:0;padding:0}
body{font:14px/1.4 Tahoma,Arial,sans-serif;background:#f5f5f5;color:#333}

/*  */
#chatDock{position:relative;height:100%;box-sizing:border-box;background:#f5f7fb;border-top:1px solid #cfd6df;box-shadow:inset 0 1px 0 rgba(255,255,255,.75);z-index:1}
#chatDock .inner{height:100%;box-sizing:border-box;margin:0;padding:10px;display:grid;grid-template-rows:auto 1fr auto;gap:8px}

/*  :  +   */
.topbar{display:flex;gap:10px;align-items:center;margin-bottom:6px}
.chat-tabs{display:flex;gap:6px;align-items:center;padding:6px 8px;background:linear-gradient(#fdfefe,#eef2f7);border:1px solid #cfd6df;border-radius:8px}
.chat-tabs .tab{display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border:1px solid #cfd6df;border-radius:6px;background:#fff;box-shadow:inset 0 1px 0 rgba(255,255,255,.85);color:#2a5db0;font-weight:700;text-transform:uppercase;font-size:12px;cursor:pointer;user-select:none}
.chat-tabs .tab.active{background:linear-gradient(#e9f4ff,#cfe7ff);border-color:#9fc9f8;color:#0d4d8a;box-shadow:0 0 0 1px rgba(137,195,255,.35)}

.actions{display:none}
.actions .btn{height:30px;padding:0 10px;border:1px solid #cfd6df;border-radius:6px;background:#fff;cursor:pointer}
.actions .btn.small{height:30px;width:30px;display:flex;align-items:center;justify-content:center}

/*  */
.chat-body{display:grid;grid-template-columns:1fr;gap:8px;min-height:0}

/*  */
#mess{background:#fff;border:1px solid #cfd6df;border-radius:8px;height:100%;min-height:140px;overflow:auto;padding:8px;box-sizing:border-box}
#mess .row{padding:6px 8px;border-bottom:1px solid #e6ebf3}
#mess .row:nth-child(odd){background:#fafcff}
#mess .pm{background:#fff7f7}
#mess .sys{color:#54657e;font-style:italic}
#mess .from{font-weight:bold;color:#203a66;cursor:pointer}
#mess .to{font-weight:bold;color:#7d2030}
#mess time{color:#678;font-size:12px;margin-left:6px}

/*  */
.chat-side{display:none}
.chat-side .box{background:#f5f7fb;border:1px solid #cfd6df;border-radius:8px;padding:8px}

/*  */
#chform{background:linear-gradient(#fdfefe,#eef2f7);border:1px solid #cfd6df;border-radius:8px;padding:6px;margin-top:0;display:grid;grid-template-columns:150px 1fr auto;gap:6px;align-items:center}
#chform input[type=text]{height:28px;border:1px solid #bfc9d6;background:#fff;border-radius:6px;padding:0 8px;color:#222}
#chform .ico{height:30px;width:36px;border:1px solid #cfd6df;border-radius:6px;background:#fff;cursor:pointer}

/*  */
#pmTarget{display:none;margin-top:4px;font:12px Tahoma}
#pmTarget input{height:20px;padding:0 6px;border:1px solid #bfc9d6;border-radius:4px;background:#f5f7fb}
#pmTarget button{height:22px;padding:0 6px;border:1px solid #cfd6df;border-radius:4px;background:#fff;cursor:pointer}

/*  */
#error{position:fixed;left:50%;top:25%;transform:translate(-50%,-50%);background:linear-gradient(to bottom,#ffdddd,#ffffff);border:1px solid #e74c3c;color:#c0392b;padding:12px;border-radius:6px;box-shadow:0 0 10px rgba(0,0,0,.05);display:none;width:380px;max-width:calc(100% - 24px);z-index:2000}
#error .exit{position:absolute;top:5px;right:5px;cursor:pointer;color:#999}
</style>
</head>
<body>

<div id="chatDock">
  <div class="inner">

    <!--   -->
    <div class="topbar">
      <!--  -->
      <div class="chat-tabs" id="chatTabs">
        <button type="button" class="tab active" data-tab="all">&#1054;&#1073;&#1097;&#1080;&#1081;</button>
        <button type="button" class="tab" data-tab="trade">&#1058;&#1086;&#1088;&#1075;</button>
        <button type="button" class="tab" data-tab="battle">&#1041;&#1086;&#1081;</button>
        <button type="button" class="tab" data-tab="private">&#1051;&#1080;&#1095;&#1085;&#1099;&#1081;</button>
        <button type="button" class="tab" data-tab="clan">&#1050;&#1083;&#1072;&#1085;</button>
        <button type="button" id="btnScope" class="tab" data-scope="<?php echo ($USER_MYCHAT==2?'room':'all');?>"><?php echo ($USER_MYCHAT==2?'&#1050;&#1086;&#1084;&#1085;&#1072;&#1090;&#1072;':'&#1042;&#1089;&#1077;');?></button>
      </div>

      <!--   ( ) -->
      <div class="actions">
        <!--  -->
        <button type="button" id="btnPve" class="btn"><?php echo ($PVE_BUTTON? '&#1053;&#1072;&#1087;&#1072;&#1076;&#1077;&#1085;&#1080;&#1077;: &#1042;&#1050;&#1051;':'&#1053;&#1072;&#1087;&#1072;&#1076;&#1077;&#1085;&#1080;&#1077;: &#1042;&#1067;&#1050;&#1051;'); ?></button>

        <!--  ""  / -->
        <input type="text" id="actToName" placeholder="&#1053;&#1080;&#1082;" style="height:30px;border:1px solid #cfd6df;border-radius:6px;padding:0 8px">

        <!--  -->
        <button type="button" id="btnTrade" class="btn">&#1054;&#1073;&#1084;&#1077;&#1085;</button>
        <!--  -->
        <button type="button" id="btnBreed" class="btn">&#1056;&#1072;&#1079;&#1074;&#1077;&#1076;&#1077;&#1085;&#1080;&#1077;</button>
        <!--  -->
        <button type="button" id="btnQuests" class="btn small" title="&#1050;&#1074;&#1077;&#1089;&#1090;&#1099;">&#1050;&#1074;</button>
        <!--    -->
        <button type="button" id="btnBattles" class="btn small" title="&#1041;&#1086;&#1080;">&#1041;&#1086;&#1080;</button>
      </div>
    </div>

    <div class="chat-body">
      <div id="mess"></div>
      <div class="chat-side">
        <div class="box">
          <div style="font-weight:bold;margin-bottom:6px;">&#1063;&#1072;&#1090;</div>
          <label style="display:flex;align-items:center;gap:6px;">
            <input type="checkbox" id="autoscroll" checked> &#1040;&#1074;&#1090;&#1086;&#1087;&#1088;&#1086;&#1082;&#1088;&#1091;&#1090;&#1082;&#1072;
          </label>
          <div style="margin-top:8px;font-size:12px;color:#555;">
              <b style="color:#203a66;"></b>     .
          </div>
        </div>
      </div>
    </div>

    <!--  -->
    <form id="chform" method="post" action="" onsubmit="return sendChat(event)">
      <input type="hidden" name="__chat_action" value="send">
      <input type="hidden" name="chat_channel" id="chat_channel" value="all">
      <input type="hidden" name="pm_to_id" id="pm_to_id" value="0">
      <input type="text" id="fromName" value="<?php echo htmlspecialchars($CURRENT_USER_LOGIN); ?>" readonly>
      <input type="text" id="inp" name="text" placeholder="&#1057;&#1086;&#1086;&#1073;&#1097;&#1077;&#1085;&#1080;&#1077;...">
      <button type="submit" class="ico" title="&#1054;&#1090;&#1087;&#1088;&#1072;&#1074;&#1080;&#1090;&#1100;">&#9658;</button>
    </form>

    <!--  -->
    <div id="pmTarget">
      : <input type="text" id="pm_to_name" value="" readonly>
      <button type="button" id="pm_clear" title="">?</button>
    </div>

  </div>
</div>

<div id="error"><div class="exit" onclick="this.parentNode.style.display='none'">?</div></div>

<script type="text/javascript">
function $(id){return document.getElementById(id)}
function qs(o){var s=[],k;for(k in o)if(o.hasOwnProperty(k))s.push(encodeURIComponent(k)+'='+encodeURIComponent(o[k]));return s.join('&')}
function showError(msg){var box=$('error'); if(!box){alert(msg);return;} box.innerHTML='<div class="exit" onclick="this.parentNode.style.display=\\'none\\'">?</div>'+String(msg||''); box.style.display='block'}

/*  */
(function(){
  var tabs=$('chatTabs'); if(!tabs) return;
  tabs.addEventListener('click', function(e){
    var t=e.target||e.srcElement; if(!t||!t.className||t.className.indexOf('tab')===-1||t.id==='btnScope')return false;
    if(e.preventDefault)e.preventDefault(); e.returnValue=false;
    var tab=t.getAttribute('data-tab')||'all';
    var btns=tabs.getElementsByClassName('tab');
    for(var i=0;i<btns.length;i++){var b=btns[i]; if((b.getAttribute('data-tab')||'')===tab){ if((' '+b.className+' ').indexOf(' active ')===-1)b.className+=' active'; } else { b.className=b.className.replace(/\s?active\s?/g,' ');} }
    $('chat_channel').value=tab;
    togglePmField();
    fetchMessages(true);
    return false;
  }, false);
})();

/*  / */
(function(){
  var b=$('btnScope'); if(!b) return;
  b.onclick=function(){
    var scope=(b.getAttribute('data-scope')==='room')?'all':'room';
    var xhr=new XMLHttpRequest();
    xhr.open('POST', window.location.pathname, true);
    xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded; charset=UTF-8');
    xhr.onreadystatechange=function(){
      if(xhr.readyState===4){
        var res=null; try{res=JSON.parse(xhr.responseText);}catch(e){}
        if(!res||res.ok!==true){showError((res&&res.msg)?res.msg:' .');return;}
        b.setAttribute('data-scope',scope); b.textContent=(scope==='room')?'\u041a\u043e\u043c\u043d\u0430\u0442\u0430':'\u0412\u0441\u0435';
        fetchMessages(true);
      }
    };
    xhr.send(qs({'__chat_action':'scope','scope':scope}));
  };
})();

/*    */
(function(){
  var b=$('btnPve'); if(!b) return;
  b.onclick=function(){
    var turnOn=(b.innerHTML.indexOf('')!==-1);
    var xhr=new XMLHttpRequest();
    xhr.open('POST', window.location.pathname, true);
    xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded; charset=UTF-8');
    xhr.onreadystatechange=function(){
      if(xhr.readyState===4){
        var res=null; try{res=JSON.parse(xhr.responseText);}catch(e){}
        if(!res||res.ok!==true){showError((res&&res.msg)?res.msg:'.');return;}
        b.innerHTML = turnOn ? ': ' : ': ';
      }
    };
    xhr.send(qs({'__chat_action':'pve','mode':(turnOn?'on':'off')}));
  };
})();

/*  /  /  /  */
(function(){
  var to=$('actToName');
  $('btnTrade').onclick=function(){ var v=(to.value||'').trim(); if(!v){showError('   .');return;} window.location.href='/game&gets=true&trade=true&to='+encodeURIComponent(v); };
  $('btnBreed').onclick=function(){ var v=(to.value||'').trim(); if(!v){showError('   .');return;} window.location.href='/game.php?go=char&newpok=1&to_tren='+encodeURIComponent(v); };
  $('btnQuests').onclick=function(){ window.open('game.php?go=quest_list','QuestInfo','width=500,height=500,scrollbars=yes'); };
  $('btnBattles').onclick=function(){ window.location.href='/game&gets=true&view_battle=true'; };
})();

/*     */
function togglePmField(){ var isPrivate=($('chat_channel').value==='private'); $('pmTarget').style.display=isPrivate?'':'none'; if(!isPrivate){$('pm_to_id').value=0;$('pm_to_name').value='';} }
$('pm_clear').onclick=function(){ $('pm_to_id').value=0; $('pm_to_name').value=''; };

/*      */
document.addEventListener('click',function(e){
  var t=e.target||e.srcElement; if(!t) return;
  if((' '+t.className+' ').indexOf(' from ')!==-1 && t.getAttribute('data-id')){
    var id=parseInt(t.getAttribute('data-id'),10)||0;
    var name=t.getAttribute('data-name')||t.textContent||t.innerText||'';
    $('chat_channel').value='private'; $('pm_to_id').value=id; $('pm_to_name').value=name;
    var tabs=$('chatTabs').getElementsByClassName('tab');
    for(var i=0;i<tabs.length;i++){var b=tabs[i],tb=b.getAttribute('data-tab')||''; if(tb==='private'){ if((' '+b.className+' ').indexOf(' active ')===-1) b.className+=' active'; } else { b.className=b.className.replace(/\s?active\s?/g,' ');} }
    togglePmField();
  }
}, false);

/*  */
var __lastTxt='', __lastAt=0;
function sendChat(ev){
  if(ev&&ev.preventDefault)ev.preventDefault();
  var txt=($('inp').value||'').replace(/\s+/g,' ').trim(); if(!txt) return false;
  var now=+new Date(); if(txt===__lastTxt&&(now-__lastAt)<2000) return false;
  var payload={'__chat_action':'send','chat_channel':$('chat_channel').value,'pm_to_id':$('pm_to_id').value,'text':txt};
  var xhr=new XMLHttpRequest();
  xhr.open('POST', window.location.pathname, true);
  xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded; charset=UTF-8');
  xhr.onreadystatechange=function(){
    if(xhr.readyState===4){
      var res=null; try{res=JSON.parse(xhr.responseText);}catch(e){}
      if(!res||res.ok!==true){showError((res&&res.msg)?res.msg:' .');return;}
      __lastTxt=txt; __lastAt=now; $('inp').value=''; fetchMessages(true);
    }
  };
  xhr.send(qs(payload));
  return false;
}

/*   */
var _fetchBusy=false;
function fetchMessages(forceScroll){
  if(_fetchBusy) return; _fetchBusy=true;
  var xhr=new XMLHttpRequest();
  xhr.open('GET', window.location.pathname+'?fetch=1', true);
  xhr.onreadystatechange=function(){
    if(xhr.readyState===4){
      _fetchBusy=false;
      var data=null; try{data=JSON.parse(xhr.responseText);}catch(e){}
      if(!data||data.ok!==true) return;
      var rows=data.rows||[], active=$('chat_channel').value, html=[],i,r,timeStr,isPM;
      for(i=0;i<rows.length;i++){
        r=rows[i]; if(active!=='all' && r.channel!==active) continue;
        isPM=(r.channel==='private' && parseInt(r.to_user_id,10)>0);
        var fromName=r.from_name||('ID'+r.from_user_id);
        var toName=r.to_name||(r.to_user_id?('ID'+r.to_user_id):'');
        var fromAttr=' class="from" data-id="'+parseInt(r.from_user_id,10)+'" data-name="'+(fromName||'')+'"';
        timeStr=r.created_at?String(r.created_at).replace(' ','&nbsp;'):'';
        if(isPM){
          if(parseInt(r.from_user_id,10)===<?php echo (int)$CURRENT_USER_ID;?>){
            html.push('<div class="row pm"><span'+fromAttr+'>'+fromName+'</span> <span class="sys"> ></span> <span class="to">'+toName+'</span>: '+r.text+' <time>'+timeStr+'</time></div>');
          }else{
            html.push('<div class="row pm"><span class="sys"> </span> <span'+fromAttr+'>'+fromName+'</span>: '+r.text+' <time>'+timeStr+'</time></div>');
          }
        }else{
          html.push('<div class="row"><span'+fromAttr+'>'+fromName+'</span>: '+r.text+' <time>'+timeStr+'</time></div>');
        }
      }
      $('mess').innerHTML=html.join('');
      if($('autoscroll').checked||forceScroll){ var el=$('mess'); el.scrollTop=el.scrollHeight; }
    }
  };
  xhr.send(null);
}

setInterval(function(){ fetchMessages(false); }, 5000);
window.onload=function(){ togglePmField(); fetchMessages(true); };
</script>


<script type="text/javascript">
(function(){
  function ajaxGET(url, cb){
    var x=new XMLHttpRequest();
    x.open('GET', url, true);
    x.setRequestHeader('X-Requested-With','XMLHttpRequest');
    x.onreadystatechange=function(){ if(x.readyState===4){ var r=null; try{r=JSON.parse(x.responseText);}catch(e){} cb(r, x); } };
    x.send(null);
  }
  function ajaxPOST(url, dataObj, cb){
    var x=new XMLHttpRequest();
    x.open('POST', url, true);
    x.setRequestHeader('X-Requested-With','XMLHttpRequest');
    x.setRequestHeader('Content-Type','application/x-www-form-urlencoded; charset=UTF-8');
    x.onreadystatechange=function(){ if(x.readyState===4){ var r=null; try{r=JSON.parse(x.responseText);}catch(e){} cb(r, x); } };
    var s=[],k; for(k in dataObj){ if(dataObj.hasOwnProperty(k)) s.push(encodeURIComponent(k)+'='+encodeURIComponent(dataObj[k])); }
    x.send(s.join('&'));
  }
  function navigateGameUrl(href){
    try{ if (window.loadPart) { window.loadPart('map','app-main'); return; } }catch(e){}
    window.location.href = href;
  }
  document.addEventListener('click', function(e){
    var t=e.target; while(t && t.tagName!=='A') t=t.parentNode;
    if(!t||!t.href) return;
    var href=t.getAttribute('href')||'';
    if(href.indexOf('/game.php?go=')===0 || href.indexOf('game.php?go=')===0){
      if (e.preventDefault) e.preventDefault(); e.returnValue=false;
      navigateGameUrl(href);
      return false;
    }
    if (href.indexOf('/game&')===0 && (href.indexOf('gets=')>0 || href.indexOf('napadenie=')>0)){
      if (e.preventDefault) e.preventDefault(); e.returnValue=false;
      var url=href + (href.indexOf('?')>-1?'&':'?') + 'ajax=1';
      ajaxGET(url, function(r){
        if (r && r.ok && r.redirect){ navigateGameUrl(r.redirect); }
        else if (r && r.ok){ navigateGameUrl('/game'); }
      });
      return false;
    }
  }, false);

  (function bindChatForm(){
    var f = document.getElementById('formchat');
    if (!f || f.__bound) return; f.__bound = true;

    f.addEventListener('submit', function(e){
      if (e.preventDefault) e.preventDefault(); e.returnValue=false;
      var tochat = (f.tochat && f.tochat.value) ? f.tochat.value : '';
      var text   = (f.textchat && f.textchat.value) ? f.textchat.value : '';
      var tipe   = (f.tipe && f.tipe.value) ? f.tipe.value : '';
      if (!text.replace(/\s+/g,'').length) return false;

      ajaxPOST('/include/function/post.map.php?ajax=1', { tochat: tochat, textchat: text, tipe: tipe }, function(r){
        if (r && r.ok){
          if (f.textchat) f.textchat.value='';
          try{ if (window.loadPart) window.loadPart('chat', 'chatDock'); }catch(e){}
        }
      });
      return false;
    }, false);
  })();

  var btnBattles = document.getElementById('btnBattles');
  if (btnBattles){
    btnBattles.onclick = function(ev){
      if (ev && ev.preventDefault) ev.preventDefault();
      ajaxGET('/include/function/get.map.php?gets=true&view_battle=true&ajax=1', function(r){
        if (r && r.ok && r.redirect){ navigateGameUrl(r.redirect); }
      });
      return false;
    };
  }
})();
</script>

</body>
</html>
