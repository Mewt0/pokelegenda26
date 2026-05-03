<?
	require_once('include/files/pokemon.har.php');
$time = time();
$couny_poke = first('SELECT COUNT(*) as count FROM pok_user WHERE users=%d and active=1',$_SESSION['id']);
$cool_poke  = $couny_poke['count'];
function selectPokesBase($tip){
  $i = select('SELECT DISTINCT pb.id AS id, pb.title FROM poke_base pb
              INNER JOIN pok_user pu
              ON pu.basenum = pb.id AND pu.active=0
              WHERE users=%d ORDER BY id ASC',$_SESSION['id']);
  $option = false;
  if($i){
    foreach($i as $o){
          if($o['id'] == $tip) $selected = " selected "; else $selected = false; 
          $option .= '<option '.$selected.' value="'.$o['id'].'">#'.$o['title'].'</option>';
    }
  }
 return $option;
}
function selectlvl($q){
  $option = false;
  for($i=1; $i<101; $i++){
    if($i == $q) $selected = " selected "; else $selected = false;
    $option .= '<option '.$selected.' value="'.$i.'">'.$i.' - Lvl</option>';
  }
 return $option;
}
function selectgen($q){
  $option = false;
  for($i=1; $i<51; $i++){
    if($i == $q) $selected = " selected "; else $selected = false;
    $option .= '<option '.$selected.' value="'.$i.'">IV: '.$i.'</option>';
  }
 return $option;
}
function selecthar($q){
  $option = false;
  for($i=1; $i<27; $i++){
    if($i == $q) $selected = " selected "; else $selected = false;
    $option .= '<option '.$selected.' value="'.$i.'">'.haracter_pokes($i).'</option>';
  }
 return $option;
} 
if(!empty($_GET['id']) and !empty($_GET['do'])){
    if(!empty($_GET['zapStr'])) $st   = $_GET['zapStr'];else $st   = '0';
    if(!empty($_GET['base']))   $base = $_GET['base'];  else $base = '0';
    if(!empty($_GET['lvl']))    $lvl  = $_GET['lvl'];   else $lvl  = '0';
    if(!empty($_GET['spar']))   $spar = $_GET['spar'];  else $spar = '0';
    if(!empty($_GET['gen']))    $gen  = $_GET['gen'];   else $gen  = '0';
    if(!empty($_GET['pol']))    $pol  = $_GET['pol'];   else $pol  = '0';
    if(!empty($_GET['har']))    $har  = $_GET['har'];   else $har  = '0';
    $funct = $_GET['do'];
    $id = $_GET['id'];
      if($funct == "2"){
          if($cool_poke > 1){
              $poke = first('SELECT users,id FROM pok_user WHERE id=%d AND users=%d and active=1',$_GET['id'],$_SESSION['id']);
                if(!$poke || $poke['users'] != $_SESSION['id']) die("<script>location.href='game.php?go=char';</script>");
                    update('pok_user',array('active'=>0, 'startepoke'=>0),'users='.(int)$_SESSION['id'].' AND id='.(int)$poke['id']);
                    print '<script type="text/javascript">parent._location.loadPokemon('.$st.','.$base.','.$lvl.','.$spar.','.$gen.','.$pol.','.$har.',1);</script>';
                    print '<script type="text/javascript">parent._location.loadPokemon(1,0,0,0,0,0,0,2);</script>';
                    $zPok = $cool_poke-1;
                    print '<script type="text/javascript">parent._location.document.getElementById(\'coolpok\').innerHTML = '.$zPok.';</script>';
                    
          }
      }
      if($funct == "1"){
          if($cool_poke < 6){
               $poke = first('SELECT users,id FROM pok_user WHERE id=%d AND users=%d and active=0',$_GET['id'],$_SESSION['id']);
                if(!$poke || $poke['users'] != $_SESSION['id']) die("<script>location.href='game.php?go=char';</script>");
                    update('pok_user',array('active'=>1),'users='.(int)$_SESSION['id'].' AND id='.(int)$poke['id']);
                    print '<script type="text/javascript">parent._location.loadPokemon('.$st.','.$base.','.$lvl.','.$spar.','.$gen.','.$pol.','.$har.',1);</script>';
                    print '<script type="text/javascript">parent._location.loadPokemon(1,0,0,0,0,0,0,2);</script>';
                    $zPok = $cool_poke +1;
                    print '<script type="text/javascript">parent._location.document.getElementById(\'coolpok\').innerHTML='.$zPok.';</script>';
         }
     }
}
if(!empty($_GET['pokeActiv'])){  
  $poke_ac_one = select('SELECT id,names,lvl FROM pok_user WHERE users=%d and active=1 ORDER BY basenum ASC',$_SESSION['id']);
      echo "<table>";
  foreach($poke_ac_one as $poke){
      echo "<tr><td><a href='game.php?go=char&npc=1&do_npc=2&pitomnik=1&id=".$poke['id']."&do=2' target=\"_chat_two\">#".$poke['names']."</a> <span align=center style=\"font-size:12;color:Black;\">- ".$poke['lvl']."-lvl<br></span></td></tr>";
  }
      echo "</table>";
 die();
}
if(!empty($_GET['zapStr'])){    
    if(!empty($_GET['zapStr'])) $st   = obr_chis($_GET['zapStr']);else $st   = '0';
    if(!empty($_GET['base']))   $base = obr_chis($_GET['base']);  else $base = '0';
    if(!empty($_GET['lvl']))    $lvl  = obr_chis($_GET['lvl']);   else $lvl  = '0';
    if(!empty($_GET['spar']))   $spar = obr_chis($_GET['spar']);  else $spar = '0';
    if(!empty($_GET['gen']))    $gen  = obr_chis($_GET['gen']);   else $gen  = '0';
    if(!empty($_GET['pol']))    $pol  = obr_chis($_GET['pol']);   else $pol  = '0';
    if(!empty($_GET['har']))    $har  = obr_chis($_GET['har']);   else $har  = '0';
    if($base>0 || $lvl>0 || $spar>0 || $gen>0 || $pol>0 || $har>0) $serch = ' | <a href="javascript:" onclick="loadPokemon(1,0,0,0,0,0,0,1);" style="font-size:16px;color:gold;">СБРОСИТЬ ПОИСК</a></small>';
      else  $serch = false;
    function obPerm($chs){
      $chs = ceil($chs);
      $chs = abs($chs);
      $chs = stripslashes($chs);
      $chs = htmlspecialchars($chs);
      $chs = trim($chs);
      $chs = mysql_escape_string($chs);     					
      return $chs;
    }
    $sbornik = "&zapStr=".$st."&base=".$base."&lvl=".$lvl."&spar=".$spar."&gen=".$gen."&pol=".$pol."&har=".$har;
    $zapros = ' ';
    if($base > 0) $zapros .=  ' AND basenum='.obPerm($base).' ';
    if($lvl > 0 && $lvl < 101)  $zapros .=  ' AND lvl='.obPerm($lvl).' ';
    if($spar > 0) $zapros .=  ' AND reproduction='.($spar==1?'0':'1').' ';
    if($gen > 0)  { $gens = obPerm($gen); $zapros .=  ' AND hp_iv>='.$gens.' AND atk_iv>='.$gens.' AND def_iv>='.$gens.' AND satk_iv>='.$gens.' AND sdef_iv>='.$gens.' AND speed_iv>='.$gens.' ';}
    if($pol > 0)  $zapros .=  ' AND sex='.($pol==1?1:2).' ';
    if($har > 0 && $har < 27)  $zapros .=  ' AND har='.obPerm($har).' ';
    $_GET['page'] = $st-1;   
    include('itemsinpage.rinokpoke.php');
    $itemsCount = first("SELECT COUNT(*) as count FROM pok_user where users=%d AND active=0 ".$zapros." ",$_SESSION['id']);        
    $itemsinpage = new Itemsinpage($itemsCount['count']);
    $poke_ac_two = select('SELECT id,names,lvl,basenum,sex,hp_iv,atk_iv,def_iv,satk_iv,sdef_iv,speed_iv,har,startone,reproduction,happy FROM pok_user where users=%d AND active=0 '.$zapros.' ORDER BY basenum ASC, lvl ASC LIMIT %d,%d',$_SESSION['id'],$itemsinpage->get('Start'),$itemsinpage->get('Limit')); 
    $data = $itemsinpage->SmartyArr();
       $txtStran = false;
       $stran = false;
        for($i=0,$n=sizeof($data['Count']);$i<$n;$i++):
        if($data['Count'][$i][1]!=$_GET['page']){    
          $st = $data['Count'][$i][1]+1;	   
          if($data['Count'][$i][1] == 0)  $stran .= ' ';  
           else  
            $stran .= '-<button class="butStr" onclick="loadPokemon('.$st.','.$base.','.$lvl.','.$spar.','.$gen.','.$pol.','.$har.',1);">'.$st.'</button>';
           
        }else{
          $st2 = $data['Count'][$i][0];
          if($data['Count'][$i][1] == 0)  $onestr = '<button class="butStrYes" onclick="loadPokemon(1,'.$base.','.$lvl.','.$spar.','.$gen.','.$pol.','.$har.',1);">1</button>';
           else         
            $stran .= '-<button class="butStrYes" onclick="loadPokemon('.$st2.','.$base.','.$lvl.','.$spar.','.$gen.','.$pol.','.$har.',1);">'.$st2.'</button>';    
        }
        endfor; 
        if($stran != false) $onestr = ' <td align="center"><b style="color:#000;font-weight:bold;">Страница:</b></td></tr><tr>
                                         <td style="color:#000;font-weight:bold;" align="center">
                                        <button class="butStr" onclick="loadPokemon(1,'.$base.','.$lvl.','.$spar.','.$gen.','.$pol.','.$har.',1);">1</button>'; 
         else $onestr = '---';       
        $txtStran .= '<table width="500" style="font-weight:bold; font-size:15px;">
            <tr>
              <td align="center"><small>
                <a href="javascript:" onclick="loadPokemon(1,'.$base.','.$lvl.','.$spar.','.$gen.','.$pol.','.$har.',1);" style="font-size:16px;color:gold;">ОБНОВИТЬ</a></small> '.$serch.'
              </td>
              </tr>
              <tr>
               '.$onestr.$stran.'</td></tr></table>';
          
  echo "<br>
        <table>
          <tr>
            <td align='center' colspan='4' style='color:#000;font-weight:bold;font-size:25px;'>
              Поиск в питомнике(".$itemsCount['count']."):
            </td>
          </tr>
          <tr>
            <td style='color:#000;font-weight:bold;'>
              Базовый номер:
            </td>
            <td>
              <select size=\"1\" id=\"pokeBase\" onchange=\"loadPokemon(1,this.value,".$lvl.",".$spar.",".$gen.",".$pol.",".$har.",1);\">
                <option value=\"0\">Все</option>
                ".selectPokesBase($base)."
              </select>
            </td>
            <td style='color:#000;font-weight:bold;'>
              Пол:
            </td>
            <td>
              <select size=\"1\" id=\"pokeGen\" onchange=\"loadPokemon(1,".$base.",".$lvl.",".$spar.",".$gen.",this.value,".$har.",1);\">
                <option value=\"0\">Все</option>
                <option value=\"1\" ".($pol==1?' selected ':'').">Мужской</option>
                <option value=\"2\" ".($pol==2?' selected ':'').">Женский</option>
              </select>
            </td>
          </tr>
          <tr>
            <td style='color:#000;font-weight:bold;'>
              Характер:
            </td>
            <td>
              <select size=\"1\" id=\"pokeGen\" onchange=\"loadPokemon(1,".$base.",".$lvl.",".$spar.",".$gen.",".$pol.",this.value,1);\">
                <option value=\"0\">Все</option>
                ".selecthar($har)."
              </select>
            </td>
            <td style='color:#000;font-weight:bold;'>
              Уровень:
            </td>
            <td>
              <select size=\"1\" id=\"pokeLvl\" onchange=\"loadPokemon(1,".$base.",this.value,".$spar.",".$gen.",".$pol.",".$har.",1);\">
                <option value=\"0\">Все</option>
                ".selectlvl($lvl)."
              </select>
            </td>
          </tr>
          <tr>
            <td style='color:#000;font-weight:bold;'>
              Разведение:
            </td>
            <td>
              <select size=\"1\" id=\"pokeSpar\" onchange=\"loadPokemon(1,".$base.",".$lvl.",this.value,".$gen.",".$pol.",".$har.",1);\">
                <option value=\"0\">Все</option>
                <option value=\"1\" ".($spar==1?' selected ':'').">Доступно</option>
                <option value=\"2\" ".($spar==2?' selected ':'').">Недоступно</option>
              </select>
            </td>
            <td style='color:#000;font-weight:bold;'>
              Гены:
            </td>
            <td>
              <select size=\"1\" id=\"pokeGen\" onchange=\"loadPokemon(1,".$base.",".$lvl.",".$spar.",this.value,".$pol.",".$har.",1);\">
                <option value=\"0\">Все</option>
                ".selectgen($gen)."
              </select>
            </td>          
          </tr>
        </table>
        ".$txtStran."
        <br><center><b><span align=center style=\"font-size:16;color:Black;\">Покемоны в питомнике:</span></b></center><table>";
  if($itemsCount['count'] <=0 ) die('<span style="color:gold">Увы, но по данному критерию покемонов не найдено. Возможно, что Ваш питомник пуст.</span>');
  foreach($poke_ac_two as $poke){
      $img = ($poke['basenum'] >= 494?'.png':'.gif');
    $name = str_replace('"',"&quot;",$poke['names']);
    $name = str_replace('"',"&#39;",$name);
    $inf  = "<div align=left style=\'color:#67ff00\'>Имя: #".$name.".<br>Lvl: ".$poke['lvl'].".";
    $inf .= "<br>Разведение: ".($poke['reproduction']==0?'Доступно':'Недоступно').".";
    $inf .= "<br>Характер: ".haracter_pokes($poke['har']).".";
    $inf .= "<br>Счастье: ".$poke['happy']."%.";
    $inf .= "<br>Пол: ".($poke['sex']==1?'М':'Ж').".";
    $inf .= "<br>Генокод: HP-".$poke['hp_iv'].", AT-".$poke['atk_iv'].", DF-".$poke['def_iv'].", SA-".$poke['satk_iv'].", SD-".$poke['sdef_iv'].", SP-".$poke['speed_iv'].".<br>ID: ".$poke['id'].".</div>";
    $inf .= "<a href=\'game.php?go=char&npc=1&do_npc=2&pitomnik=1&id=".$poke['id']."&do=1".$sbornik."\' target=\'_chat_two\' style=\'color:gold;\' onclick=parent.mess_error(\'false\',\'none\');>->Забрать из питомника<-</a>";
    echo "
      <tr>
        <td>
          <a href='game.php?go=char&npc=1&do_npc=2&pitomnik=1&id=".$poke['id']."&do=1".$sbornik."' target=\"_chat_two\"><img src=pok/anim/".$poke['basenum'].$img." target='_chat_two'></a>
        </td>
        <td>
          <a href='game.php?go=char&npc=1&do_npc=2&pitomnik=1&id=".$poke['id']."&do=1".$sbornik."' target=\"_chat_two\"><b>#".$poke['names']."</b></a>
          <span align=center style=\"font-size:15;color:Black;\"> - ".$poke['lvl']."-lvl</span>
        </td>
        <td>ID: ".$poke['id']."</td>
        <td>          
          <a href='javascript:' onclick=\"parent.mess_error('".$inf."','block');\" style='color:gold;'>-инф-</a>
        </td>
      </tr>";
    }
  echo "</table></center>";
 die();
}
?>
<html>
<head>
<style>
INPUT,TEXTAREA {
        background-color: Ivory;
        font:8pt Tahoma;
        font-weight:bold;
        padding: 1px;
        BORDER: #000 2px solid;
        color: #000000;
}
SELECT {
        border-style:none;
        font:11pt Tahoma;
        color: #000000;
}
.butStr{
  font:10pt Tahoma;
  font-weight:bold;
  border: #000 1px solid;
  background-color: #FFF;
  color: #000;
}

.butStr:hover{
  font:10pt Tahoma;
  font-weight:bold;
  border: #000 1px solid;
  background-color: #cdbec0;
  color: #000;
}

.butStrYes{
 font:10pt Tahoma; 
 border: #fff 1px solid;
 background-color: #000;
 color: #FFF;
}
#fixedWindow{
 z-index: 1000;
 font-size:25;
 color:Black;
 padding:3px;
 width:230px;
 height:178px;
 position:fixed;
 background: #bbbbbb;
 filter:alpha(opacity=70); 
  opacity:0.7; 
 -moz-opacity:0.7;
 margin:5px; 
 top: 24%;
 border-radius: 15px;
  -webkit-border-radius: 15px;
  -moz--moz--webkit-border-radius: 15px;
  -webkit--moz--webkit-border-radius: 15px;
  -khtml--moz--webkit-border-radius: 15px;
  -moz--webkit-border-radius: 15px;
}
#pc{
  position: relative;
  z-index: 900;
}
#hrf{
  position:absolute;
  left:38px;
  bottom:5px;
}
</style>
<script type="text/javascript" src="fancybox/jquery-1.3.2.min.js"></script>
<script type="text/javascript">
function pdex(baseId){
  window.open('game.php?go=pokedex&id='+baseId,'pokedex','width=550,height=550,scrollbars=yes');
}
function loadPokemon(strn,valueBase,levels,spar,gen,pol,har,tip){
 $("#load").html('Состояние: <img src="/css/img/load.gif" alt="Загрузка" border="0"/>');
  if(tip == 1){
    if(strn       == false)  var strn = 1;
    if(valueBase  == false)  var valueBase = 0;
    if(levels     == false)  var levels = 0;
    if(spar       == false)  var spar = 0;
    if(gen        == false)  var gen  = 0;
    if(pol        == false)  var pol = 0;
    if(har        == false)  var har  = 0;
    $.ajax({  
      type: "GET",  
      url: "game.php",          
      data: "go=char&npc=1&do_npc=2&zapStr="+strn+"&base="+valueBase+"&lvl="+levels+"&spar="+spar+"&gen="+gen+"&pol="+pol+"&har="+har,   
      success: function(txt){
         $("#pitomnik").html(txt);
         $("#load").html("Состояние: OK.");
         document.getElementById('pitomnik').scrollIntoView(true);
      }
    });
  }else{
    $.ajax({  
      type: "GET",  
      url: "game.php",          
      data: "go=char&npc=1&do_npc=2&pokeActiv=true",   
      success: function(txt){
         $("#pitomnikActiv").html(txt);
         $("#load").html("Состояние: OK.");
      }
    });
  }
}                         
</script>
</head>
<body>
<div id="fixedWindow" align="center">
  <b><span align=center style="font-size:20;color:Black;">Питомник города: <br> 
  <?php 
  $nameBuildQ = buildInf($myrow['buildmy'],'title');
  $rest = substr($nameBuildQ, -1);
  if($rest === 'я') $nameBuild = substr($nameBuildQ, 0, -1).'и';
  else $nameBuild = $nameBuildQ.'а';
  if($nameBuildQ === 'Оливин Сити') $nameBuild = $nameBuildQ; 
  print $nameBuild;
  ?>.</span></b>
  <br>
  <div id="load">Состояние: OK.</div>
  <div id="hrf">
  <a href='game.php?go=char&npc=1&do_npc=pc'>
    <- Вернуться
  </a>
  </div>
</div>
<div id="fixedWindow" align="center" style="right:1px;">
  <b>
    <span align=center style="font-size:20;color:Black;">
      Покемоны с собой(<span id="coolpok"><?php print $cool_poke;?></span>):
   </span>
  </b>
  <br>
  <div id="pitomnikActiv"> </div>
</div>
<center>
<div id="pc">
  <div id="pitomnik"> </div>
</div>
<script type="text/javascript">
  loadPokemon(1,0,0,0,0,0,0,2);
  loadPokemon(1,0,0,0,0,0,0,1);
</script>
</center>
</body>
</html>
