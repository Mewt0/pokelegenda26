<?php
/* trenInfo.game.card.merge.pvp.fixed.php — карточка тренера (компакт) + PVP/PVE из $rangA/$rangB
 * PHP 5.4, cp1251. Имена/переменные НЕ меняю. Подключай свои include/коннект ДО этого файла.
 */
header('Content-Type: text/html; charset=windows-1251');

// ====== Блок получения данных (оставлен как у тебя, косметика от notice) ======
$bab = ''; $bac = ''; $bbb = ''; $bbc = ''; $x01 = -1; $x02 = -1;

if(!empty($_GET['id']) && preg_match("|^[\\d]+$|", $_GET['id'])){
    if($_GET['id'] == 3) die("<font color='brown'><b><center>Система</center></b></font>");
    $usersInfo = first('SELECT * FROM users WHERE id=%d', $_GET['id']);
    if(empty($usersInfo['id'])) die('Данного тренера не существует, либо он был удален.');

    $idTren = $usersInfo['id'];
    $textTeu = false;

    if(($myrow['id'] == 1 || $myrow['id'] == 2) && isset($myrow['id'])) {
        // Admin actions
    }

    $countNorm = first('SELECT COUNT(DISTINCT basenum) as count FROM pok_user WHERE users=%d AND tips="normal"', $idTren);
    $countShin = first('SELECT COUNT(DISTINCT basenum) as count FROM pok_user WHERE users=%d AND tips="shine"', $idTren);
    $couny_poke_hpO = first('SELECT COUNT(*) as count FROM pok_user WHERE users=%d AND active=1 AND hp_my>0', $idTren);
    $couny_poke_hp  = first('SELECT COUNT(*) as count FROM pok_user WHERE users=%d AND active=1 AND hp_my<=0', $idTren);
    $build = first('SELECT town,title FROM build WHERE id=%d', $usersInfo['buildmy']);
    if(!empty($build)) $town = first('SELECT townName FROM towns WHERE id=%d', $build['town']);
    $ipBn  = first('SELECT id,ip FROM banip WHERE ip=%d', $usersInfo['ip']);

    $normalP = !empty($countNorm['count']) ? $countNorm['count'] : 0;
    $shinyP  = !empty($countShin['count']) ? $countShin['count'] : 0;
    $names   = $usersInfo['login'];
    $groups  = $usersInfo['groups'];
    $rangA   = $usersInfo['rang_a']; // PVP
    $rangB   = $usersInfo['rang_b']; // PVE
    $onlineV = $usersInfo['online'];
    $buildTitle = !empty($build['title']) ? $build['title'] : 'Неизвестно';
    $towns   = !empty($town['townName']) ? $town['townName'] : 'Неизвестно';
    $rang    = $usersInfo['rang'];
    $clan    = $usersInfo['clanid'];
    $ava     = $usersInfo['avatars'];

    // формат номера аватара
    if($ava < 10) { $ava = '00'.$ava; }
    elseif($ava < 100 && $ava > 9) { $ava = '0'.$ava; }

    $info = $usersInfo['info'];
    $datereg = $usersInfo['datereg'];
    $posishenie = $usersInfo['onlinetime'];
    $clanPoint = $usersInfo['clan_point'];
    $status_klan = $usersInfo['status_klan'];
    $colorsMy = colorsUsers($groups);
    if($colorsMy == '#000' || $colorsMy == '#000000') $colorsMy = '#FFF';

    $arest = info_uses_dop($idTren, 'arest');
    $arestText = ($arest > time()) ? ' | Заключен до: '.date('Y-m-d, H:i:s', $arest) : '';
    $rangsText = rang_group($idTren, $groups, $rang, $colorsMy).$arestText;

    // очки клана
    $clan_point = '';
    if($clan > 0){
        if($clanPoint > 0)      $clan_point = '<font style="font-size: 11px; font-family: Tahoma; text-align:justify; color: green;"><b>+'.$clanPoint.'</b></font>';
        elseif($clanPoint < 0)  $clan_point = '<font style="font-size: 11px; font-family: Tahoma; text-align:justify; color: #B22222;"><b>'.$clanPoint.'</b></font>';
        else                    $clan_point = '<font style="font-size: 11px; font-family: Tahoma; text-align:justify; color: black;"><b>0</b></font>';
    }

    $hpYes = !empty($couny_poke_hpO['count']) ? $couny_poke_hpO['count'] : 0;
    $hpNou = !empty($couny_poke_hp['count']) ? $couny_poke_hp['count'] : 0;
    $imgPokeCool = '';
    for($i=0;$i<$hpYes;$i++) $imgPokeCool .= "<img src='img/info/pokeball.png' width='24' height='24'>";
    for($i=0;$i<$hpNou;$i++) $imgPokeCool .= "<img src='img/info/pokeball3.png' width='24' height='24'>";
    $emptySlots = 6-($hpYes+$hpNou);
    if($emptySlots>0){ for($i=0;$i<$emptySlots;$i++) $imgPokeCool .= "<img src='img/info/pokeball2.png' width='24' height='24'>"; }

    // картинка ранга
    $rang_img = "img/info/rang/0.png";
    $rankConditions = array(
        array($rangA > 1000000 && $rangB > 50000, "img/info/rang/10.png"),
        array($rangA > 500000  && $rangB > 45000, "img/info/rang/9.png"),
        array($rangA > 450000  && $rangB > 38000, "img/info/rang/8.png"),
        array($rangA > 380000  && $rangB > 30000, "img/info/rang/7.png"),
        array($rangA > 340000  && $rangB > 25000, "img/info/rang/6.png"),
        array($rangA > 250000  && $rangB > 18000, "img/info/rang/5.png"),
        array($rangA > 180000  && $rangB > 15000, "img/info/rang/4.png"),
        array($rangA > 110000  && $rangB > 11000, "img/info/rang/3.png"),
        array($rangA > 8000    && $rangB > 5000,  "img/info/rang/2.png"),
        array($rangA > 250     && $rangB > 250,   "img/info/rang/1.png")
    );
    foreach($rankConditions as $condition){ if($condition[0]){ $rang_img = $condition[1]; break; } }

    $nameColor = '<span style="color:'.$colorsMy.';">'.$names.'</span>';
    $online = ($onlineV == 1) ? '<font style="font-size: 13px; font-family: Tahoma; text-align:justify; color:#00ff00;"><b>Онлайн</b></font>'
                              : '<font style="font-size: 13px; font-family: Tahoma; text-align:justify; color: #ff1000;"><b>Оффлайн</b></font>';

    // Эмблема/клан
    $clanTitle = '...';
    if($clan > 0){
        $clanx = first('SELECT id_sozdatel,clan_name,clan_img FROM clans WHERE id_clan=%d', $clan);
        if(!empty($clanx)){
            $clanTitle = '<a href="javascript:;" onclick="window.open(\'game.php?go=clans&id='.$clan.'\', \'clan\', \'fullscreen=no,scrollbars=yes,width=650,height=550\'); return false;">
            <img src="/img/clan/'.$clanx['clan_img'].'.png" width="32" alt="'.htmlspecialchars($clanx['clan_name'], ENT_QUOTES).'"
            onMouseMove="tip(event,\'<b style=font-size:22px;>'.htmlspecialchars($clanx['clan_name'], ENT_QUOTES).'</b>\')" 
            onMouseOut="tip(event,0);"></a><br>'.(!empty($status_klan) ? htmlspecialchars($status_klan, ENT_QUOTES) : '...').', '.$clan_point;
        }
    }

    // Подарки/награды
    $presen = select('SELECT *, pru.id as myid FROM presents_users pru 
                      INNER JOIN presents_sistem prs ON pru.idpresent=prs.id  
                      WHERE usertoid=%d', $idTren);
    $presentA = '...'; // награды
    $presentB = '...'; // подарки
    if(!empty($presen)){
        // A: награды
        $awardsHtml = '';
        foreach($presen as $item){
            if($item['tippresent']==1){
                $deleteScript = ($myrow['id']==1 || $myrow['id']==2) ? 'onClick="if(confirm(\'Вы точно хотите удалить эту награду?\')) window.location.href=\'/game.php?go=trenInfo&id='.$_GET['id'].'&delpresent='.$item['myid'].'\';"' : '';
                $awardsHtml .= '<img src="img/present/'.$item['idpresent'].'.png" class="imgPresent" '.$deleteScript.' 
                    onMouseMove="tip(event,\'<b><span style=color:#fff;font-size:14px;>'.htmlspecialchars($item['name'], ENT_QUOTES).'</span></b><b id=titleb><br>'.htmlspecialchars($item['title'], ENT_QUOTES).''
                    .(!empty($item['coments']) ? '<br>Коментарий: '.htmlspecialchars($item['coments'], ENT_QUOTES) : '').'</b>\')" 
                    onMouseOut="tip(event,0);">';
            }
        }
        if($awardsHtml!='') $presentA = $awardsHtml;

        // B: подарки
        $giftsHtml = '';
        foreach($presen as $gift){
            if($gift['tippresent']==2){
                $userPres = first('SELECT login, groups FROM users WHERE id=%d', $gift['userid']);
                $userMy = $userPres ? "<span style=color:".colorsUsers($userPres['groups']).";font-weight:bold;>".htmlspecialchars($userPres['login'], ENT_QUOTES)."</span>" : '';
                $deleteScript = ($myrow['id']==1 || $myrow['id']==2) ? 'onClick="if(confirm(\'Вы точно хотите удалить эту награду?\')) window.location.href=\'/game.php?go=trenInfo&id='.$_GET['id'].'&delpresent='.$gift['myid'].'\';"' : '';
                $giftsHtml .= '<img src="img/present/'.$gift['idpresent'].'.png" class="imgPresent" '.$deleteScript.' 
                    onMouseMove="tip(event,\'<b><span style=color:#fff;font-size:14px;>'.htmlspecialchars($gift['name'], ENT_QUOTES).'</span></b><b id=titleb><br>'.htmlspecialchars($gift['title'], ENT_QUOTES)
                    .($userMy ? '<br>От: '.$userMy : '')
                    .(!empty($gift['coments']) ? '<br>Коментарий: '.htmlspecialchars($gift['coments'], ENT_QUOTES) : '').'</b>\')" 
                    onMouseOut="tip(event,0);">';
            }
        }
        if($giftsHtml!='') $presentB = $giftsHtml;
    }

    // полиция/мульты
    $policeDefens = '';
    if(isset($myrow['police']) && $myrow['police'] == 1){
        $xpolice = select('SELECT login,id,online,groups FROM users WHERE ip=%d AND id!=%d', $usersInfo['ip'], $usersInfo['id']);
        $txt0x2 = ''; $countMult = 0;
        foreach($xpolice as $x0x1){
            $countMult++;
            $punished    = ($x0x1['groups']==7 || $x0x1['groups']==10) ? '<font color="#000">- Наказанный</font>' : '';
            $onlineStatus= ($x0x1['online']==1) ? '<font color="#000"> - Online</font>' : '';
            $txt0x2 .= '<span id="trid" style="color:green;" onClick="window.location.href=\'/game.php?go=trenInfo&id='.$x0x1['id'].'\'">'.$x0x1['login'].$punished.$onlineStatus.'</span><br>';
        }
        $txt0x1 = 'IP данного пользователя: '.long2ip($usersInfo['ip']).'<br>';
        $banned = !empty($ipBn['id']) ? '<br><b style="color:brown;">Забанен по IP адресу!</b>' : '';
        $banLink= !empty($ipBn['id']) ? '<a href="/game.php?go=trenInfo&id='.$idTren.'&banip=false" style="color:#000;font-size:15px;">Снять бан по IP</a>'
                                      : '<a href="/game.php?go=trenInfo&id='.$idTren.'&banip=true"  style="color:#000;font-size:15px;">Дать бан по IP</a>';
        $policeDefens = '<div id="pldef" onClick="document.getElementById(\'iddef\').style.display=(document.getElementById(\'iddef\').style.display==\'block\'?\'none\':\'block\');">Возможные мульты ('.$countMult.') ->'.$banned.'<div id="iddef" style="display:none;">'.$txt0x1.$txt0x2.'<br>'.$banLink.'</div></div>';
    }
}

// Derived for layout
$status_text  = isset($onlineV) && $onlineV==1 ? 'Онлайн' : 'Оффлайн';
$rating_all   = isset($usersInfo['rating_all']) ? $usersInfo['rating_all'] : 0;
$map_region   = isset($towns) ? $towns : 'Локация';
$map_place    = isset($buildTitle) ? $buildTitle : '...';
$vip_badge    = isset($usersInfo['vip']) ? intval($usersInfo['vip']) : 0;
$img          = isset($ava) ? 'img/ava/'.$ava.'.png' : '';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
<title>Профиль игрока</title>
<link rel="stylesheet" type="text/css" href="css/prof.css">
<script src="fancybox/jquery-1.3.2.min.js" type="text/javascript"></script>
<script type="text/javascript">
function enter(tip){
  if(tip == 1){
    document.getElementById("messDivOver").style.display='block';
    document.getElementById("mess").style.display='block';
  } else {
    jQuery("#mess").animate({opacity: 'hide'}, "slow");
    jQuery("#messDivOver").animate({opacity: 'hide'}, "slow");
  }
}
function sendGo(){
  jQuery.post("game.php?postGo=sends", {
    act: "send",
    users: jQuery("#mess #sendPolucId").val(),
    text: jQuery("#mess #sendTexts").val(),
    subj: jQuery("#mess #sendSubj").val()
  }, function(){ enter(2); jQuery("#lsM").html('Сообщение отправлено.'); });
  jQuery("#mess #sendTexts").val("");
  return false;
}
</script>
<style>
:root{ --bg:#eef2f6; --panel:#f7f9fc; --panel2:#ffffff; --b:#d7dee8; --text:#2a3340; --muted:#6c7a8a; }
*{box-sizing:border-box}
body{margin:0;background:#e3e8ee;font:13px/1.45 Tahoma, Verdana, Arial, sans-serif;color:var(--text);}
#tc-wrap{--pad:10px;--headH:46px;--footH:0px;max-width:1000px;margin:8px auto;padding:var(--pad);background:var(--bg);border:1px solid var(--b);border-radius:14px;box-shadow:0 6px 22px rgba(0,0,0,.18);}
#tc-head{height:var(--headH);display:flex;align-items:center;justify-content:center;background:linear-gradient(#f9fbfe,#edf2f7);border:1px solid var(--b);border-radius:10px;font-weight:900;font-size:18px;letter-spacing:.4px;color:#7b8999;text-transform:uppercase;}
#tc-grid{margin-top:10px;display:grid;grid-template-columns:240px 1fr 240px;gap:10px;height:calc(100vh - (var(--pad)*2 + var(--headH) + var(--footH) + 22px));min-height:520px;}
.tc-col{background:var(--panel);border:1px solid var(--b);border-radius:10px;padding:8px;min-height:0;}
.tc-scroll{overflow:auto;}
.panel{background:var(--panel2);border:1px solid var(--b);border-radius:10px;margin:6px 0;}
.panel-h{padding:6px 10px;font-weight:700;color:#7b8897;background:linear-gradient(#ffffff,#f2f5f9);border-bottom:1px solid var(--b);border-radius:10px 10px 0 0;display:flex;align-items:center;justify-content:space-between;}
.panel-b{padding:8px 10px;}
.kv{display:grid;grid-template-columns:1fr auto;gap:4px 10px;}
.k{color:#7c8da0;} .v{color:#2c3a4b;font-weight:700;}
.badge{display:inline-block;padding:3px 8px;border-radius:999px;font-weight:700;font-size:11px;border:1px solid var(--b);background:#fff;}
.center-card{background:#fff;border:1px solid var(--b);border-radius:10px;padding:8px;display:flex;flex-direction:column;height:100%;}
.center-top{display:flex;align-items:center;justify-content:space-between;gap:8px;}
.nick{flex:1;text-align:center;font-weight:700;font-size:16px;color:#555;background:#eef2f6;border:1px solid var(--b);padding:6px 8px;border-radius:8px;}
.center-mid{text-align:center;}
.center-mid img{max-height:300px;width:auto;image-rendering:pixelated;}
.center-foot{margin-top:auto;text-align:center;padding-top:4px;color:#5b6a7a;}
.pokeballs{ text-align:center; margin:6px 0; }
.awards,.gifts{display:grid;grid-template-columns:repeat(4,1fr);gap:6px;}
.award,.gift{aspect-ratio:1/1;border:1px dashed #cfd6de;border-radius:8px;background:#fff;}
.footer-bar{background:#e6efe4;border:1px solid #cddacc;color:#3d6648;font-weight:700;padding:5px 8px;border-radius:8px;text-align:center;}
.icon-btn{border:1px solid var(--b);background:#fff;border-radius:8px;padding:3px 8px;font-weight:700;color:#5e6d7e;cursor:pointer;}
.icon-btn:hover{filter:brightness(1.05);}
.modal{position:fixed;left:0;top:0;right:0;bottom:0;background:rgba(0,0,0,.45);display:none;align-items:center;justify-content:center;z-index:9999;}
.modal.show{display:flex;}
.modal-card{width:820px;max-width:95vw;max-height:85vh;background:#fff;border:1px solid var(--b);border-radius:10px;box-shadow:0 10px 28px rgba(0,0,0,.35);display:flex;flex-direction:column;}
.modal-head{padding:8px 10px;border-bottom:1px solid var(--b);display:flex;align-items:center;justify-content:space-between;font-weight:700;color:#6c7a8a;}
.modal-body{padding:10px;overflow:auto;}
.avatar-ph{height:300px;display:flex;align-items:center;justify-content:center;color:#8a97a8;background:#f3f6fa;border:1px dashed #cfd6de;border-radius:8px;}
</style>
<script>
(function(){ var h=window.innerHeight,w=window.innerWidth; if(h<830||w<1280) document.documentElement.className+=' tc-compact'; })();
</script>
</head>
<body>

<?php if(isset($myrow['id'])){ ?>
<div id="myid" onClick="window.location.href='/game.php?go=trenInfo&id=<?php echo $myrow['id']; ?>'"
     style="position:fixed;top:10px;left:10px;background:#fff;border:1px solid #cfd6de;color:#5b6a7a;padding:6px 10px;border-radius:10px;font-size:12px;z-index:10000;">
  Ваш ID: <?php echo $myrow['id']; ?>
</div>
<?php } ?>

<?php echo isset($policeDefens)?$policeDefens:''; ?>

<div id="tc-wrap">
  <div id="tc-head">ПРОФИЛЬ ИГРОКА</div>

  <div id="tc-grid">
    <!-- ЛЕВАЯ КОЛОНКА -->
    <section class="tc-col tc-scroll">
      <div class="panel">
        <div class="panel-h">КЛАН</div>
        <div class="panel-b">
          <div class="kv">
            <div class="k">Название:</div><div class="v"><?php echo !empty($clanTitle)?$clanTitle:'—'; ?></div>
            <div class="k">Вклад рейтинга:</div><div class="v"><?php echo isset($clanPoint)?intval($clanPoint):0; ?></div>
          </div>
        </div>
      </div>

      <div class="panel">
        <div class="panel-h">Награды</div>
        <div class="panel-b">
          <?php echo $presentA; ?>
        </div>
      </div>

      <div class="panel">
        <div class="panel-h">Информация</div>
        <div class="panel-b"><?php echo nl2br(htmlspecialchars($info, ENT_QUOTES)); ?></div>
      </div>

      <div class="panel">
        <div class="panel-h">Статистика</div>
        <div class="panel-b">
          <div class="kv">
            <div class="k">Статус:</div><div class="v"><?php echo $online; ?></div>
            <div class="k">Город -> Локация:</div><div class="v"><?php echo htmlspecialchars($towns,ENT_QUOTES).' -> '.htmlspecialchars($buildTitle,ENT_QUOTES); ?></div>
            <div class="k">Ранг:</div><div class="v"><?php echo $rangsText; ?></div>
            <div class="k">Рейтинг PVP:</div><div class="v"><span style="color:#FFD700;"><?php echo isset($rangA)?$rangA:0; ?></span></div>
            <div class="k">Рейтинг PVE:</div><div class="v"><span style="color:#ADFF2F;"><?php echo isset($rangB)?$rangB:0; ?></span></div>
            <div class="k">Покедекс:</div><div class="v"><?php echo $normalP; ?>/649</div>
            <div class="k">Шайнидекс:</div><div class="v"><?php echo $shinyP; ?>/649</div>
            <div class="k">Зарегистрирован:</div><div class="v"><?php echo htmlspecialchars($datereg,ENT_QUOTES); ?></div>
            <div class="k">В игре был:</div><div class="v"><?php echo !empty($posishenie)?date('Y-m-d, H:i:s', $posishenie):'Никогда'; ?></div>
          </div>
        </div>
        <div class="panel-b">
          <div class="footer-bar"><?php echo $map_region; ?> -> <?php echo $map_place; ?></div>
        </div>
      </div>
    </section>

    <!-- ЦЕНТР -->
    <section class="tc-col">
      <div class="center-card">
        <div class="center-top">
          <div class="badge"><?php echo $status_text; ?></div>
          <div class="nick"><?php echo $nameColor; ?></div>
          <div class="badge"><?php echo $vip_badge ? 'VIP '.$vip_badge : 'VIP'; ?></div>
        </div>

        <div class="pokeballs"><?php echo $imgPokeCool; ?></div>

        <div class="center-mid">
          <?php if(!empty($img)){ ?>
              <div class="avatar-ph" id="avaPH">Загрузка изображения...</div>
              <img id="avaIMG" data-src="<?php echo htmlspecialchars($img,ENT_QUOTES); ?>" alt="" style="display:none;">
          <?php } else { ?>
              <div class="avatar-ph">(нет изображения)</div>
          <?php } ?>
        </div>

        <div class="center-foot">
          <img src="<?php echo htmlspecialchars($rang_img,ENT_QUOTES); ?>" width="220" height="20" alt="rank">
        </div>
      </div>
    </section>

    <!-- ПРАВАЯ КОЛОНКА -->
    <section class="tc-col tc-scroll">
      <div class="panel">
        <div class="panel-h">
          <span>АКТИВНОСТЬ ТРЕНЕРА</span>
          <span class="badge"><?php echo 'Рейтинг: '.intval($rating_all); ?></span>
        </div>
        <div class="panel-b">
          <div class="kv">
            <div class="k">Статус:</div><div class="v"><?php echo $status_text; ?></div>
            <div class="k">VIP:</div><div class="v"><?php echo intval($vip_badge); ?></div>
          </div>
        </div>
      </div>

      <div class="panel">
        <div class="panel-h">
          <span>Подарки</span>
          <button class="icon-btn" id="openGifts">Открыть</button>
        </div>
        <div class="panel-b">
          <div style="text-align:center;color:#7c8da0;">Нажмите "Открыть" чтобы посмотреть подарки</div>
        </div>
      </div>

      <div class="panel">
        <div class="panel-h">Список друзей</div>
        <div class="panel-b">
          <?php
          if(isset($idTren) && isset($_SESSION['id']) && $idTren == $_SESSION['id']){
              echo '<a href="javascript:" onClick="window.open(\'/game.php?go=friends&to_id='.$idTren.'\',\'friens\',\'width=580,height=350,scrollbars=yes\');return true;">[Мои друзья]</a>';
          } else if(isset($idTren) && isset($_SESSION['id'])) {
              $friends_yes = first('SELECT id_my_friend FROM friends WHERE id_user=%d AND id_my_friend=%d', $_SESSION['id'], $idTren);
              if(empty($friends_yes['id_my_friend'])) {
                  $text_frien = "<a href=\"javascript:\" onClick=\"window.open('/game.php?go=friends&tip=1&id=".$idTren."','NewFriens','width=580,height=350,scrollbars=yes');return true;\">[Добавить в друзья]</a>";
              } else {
                  $text_frien = "<a href=\"javascript:\" onClick=\"window.open('/game.php?go=friends&tip=2&id=".$idTren."','NewFriens','width=580,height=350,scrollbars=yes');return true;\">[Удалить из друзей]</a>";
              }
              echo "<a href=\"javascript:\" onClick=\"window.open('game.php?go=friends&to_id=".$idTren."','friens','width=580,height=350,scrollbars=yes');return true;\">[Друзья ".$names."]</a> | ".$text_frien;
          } else {
              echo '—';
          }
          ?>
        </div>
      </div>
    </section>
  </div>
</div>

<!-- Gifts Modal -->
<div class="modal" id="giftsModal">
  <div class="modal-card">
    <div class="modal-head">Подарки <button class="icon-btn" id="closeGifts">Закрыть</button></div>
    <div class="modal-body"><?php echo $presentB; ?></div>
  </div>
</div>

<script type="text/javascript">
// lazy avatar
(function(){
  var img = document.getElementById('avaIMG');
  var ph  = document.getElementById('avaPH');
  if (img && ph) {
    var src = img.getAttribute('data-src');
    var pic = new Image();
    pic.onload = function(){ img.src = src; img.style.display = ''; ph.style.display = 'none'; };
    setTimeout(function(){ pic.src = src; }, 50);
  }
})();
// gifts modal
(function(){
  var open = document.getElementById('openGifts');
  var close = document.getElementById('closeGifts');
  var modal = document.getElementById('giftsModal');
  if (open && modal) open.onclick = function(){ modal.className = 'modal show'; };
  if (close && modal) close.onclick = function(){ modal.className = 'modal'; };
  if (modal) modal.addEventListener('click', function(e){ if(e.target === modal) modal.className = 'modal'; }, false);
})();
</script>

</body>
</html>
