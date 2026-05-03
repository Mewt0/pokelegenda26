<?php
$eggCount = first("SELECT COUNT(*) as count FROM eggs WHERE users_egg=%d",$_SESSION['id']);
$pos = "0";
$arrayScript = false;
if(isset($_GET['sendZapStr']) && ($_GET['sendZapStr'] > 0)){
header('Content-Type: text/css;charset=Windows-1251');
$_GET['page'] = $_GET['sendZapStr']-1;
include('include/function/itemsinpage.items.php');
$itemsinpage = new Itemsinpage($eggCount['count']);
$itmesSelect = select('SELECT e.id_egg, e.base_id_egg, e.dtime, e.attac_one, pb.title 
                      FROM eggs e 
                      Inner Join poke_base pb 
                      ON pb.id=e.base_id_egg 
                      WHERE e.users_egg=%d ORDER BY e.base_id_egg ASC LIMIT %d,%d',$_SESSION['id'],$itemsinpage->get('Start'),$itemsinpage->get('Limit'));
$data = $itemsinpage->SmartyArr();
  print '<table width="335" style="font-weight:bold; font-size:12px;">
            <tr>
              <td align="center" >
                <small><a href="javascript:" onclick="loadItems(1);">Обновить</a></small>
              <td>
            </tr>
      	   <tr>
      		  <td style="color:#000;font-weight:bold;">Страница: ';
           for($i=0,$n=sizeof($data['Count']);$i<$n;$i++):
      		 if($data['Count'][$i][1]!=$_GET['page']){
      		  $st = $data['Count'][$i][1]+1;	   
      			print '-<button class="butStr" onclick="loadItems('.$st.')">'.$st.'</button>';
      		 }else{
            $st2 = $data['Count'][$i][0];      
      		  print '-<button class="butStrYes" onclick="loadItems('.$st2.')">'.$st2.'</button>';    
      		 }
      		endfor;
  print '</td></tr></table>';
$posa = 0;
$print = 1;
$pos = $posa*45;
foreach($itmesSelect as $itemsEcho){
    $id = $itemsEcho["id_egg"];
    $tipEgg = $itemsEcho["base_id_egg"];
    $name = 'Яйцо: #'.$itemsEcho["title"];
    $count = 1;
    $use = $itemsEcho['attac_one'];
    $dress = 1;
    $elementary = 1; 
    $tt_text = 'Яйцевая атака: '.($use>0?'Изучена':'Не изучена').'.';
    $tt_text .= timersOtshet($itemsEcho["dtime"],"<br>До вылупления осталось: ", "<br>Готово к вылуплению");                                                                                                                                                                                                                          
    $txr = $name.': <b><small>x</small>'.$count.'</b>.';
    if ($tt_text) $txr .='<br><span class=itemdescr>'.$tt_text.'</span>';
    $fileImg = "img/items/egg/".$tipEgg.".png"; if(!file_exists($fileImg)) $tipEgg = 999;
echo "<div class=\"item\"><img class=\"item\" ID=\"pic".$pos."\" src=\"img/items/egg/".$tipEgg.".png\" onClick=\"pic(".$pos.",".$id.",'".$count."',".$use.")\" onMouseMove=\"tip(event,'".$txr."');\" onMouseOut=\"tip(event,0); \"></div>";
$posa ++;
$pos ++;
$print ++;
}
for ($k=$print; $k<=45; $k++) echo "<div class=\"item\"><img src='img/blank.gif'></div>";
exit;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
<TITLE>League-Of-Pokemons -> Инкубаторная камера</TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; Charset=Windows-1251">
<style>
BODY {
  background-image: url('/css/img/brushed_alu_dark.png');
  margin:0 0 0 0;
  padding:0 0 0 0;
  color: #000000;
}
A:link, A:visited {color: #000000; text-decoration:none}
A:hover, A:active {color: #000000; text-decoration:underline}
TABLE, TD, TR {
  BORDER-COLOR: #000000;
  font-family: Verdana, Arial, Helvetica, sans-serif;
  font-size: 11px;
}
H2{
  text-align:center;
  color: #fff;
}
INPUT,TEXTAREA {
  background-color: Ivory;
  font:8pt Tahoma;
  font-weight:bold;
  BORDER: #000 1px solid;
  color: #000;
}
SELECT{
  border-style:none;
  font:8pt Tahoma;
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
H1 {color:#fff; text-align:center}
#divTip {
  position:absolute;
  background: #505050;
  background: -moz-linear-gradient(top, #505050 0%, #808080 50%, #505050 100%);
  background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#505050), color-stop(50%,#808080), color-stop(100%,#505050));
  background: -webkit-linear-gradient(top, #505050 0%, #808080 50%, #505050 100%);
  background: -o-linear-gradient(top, #505050 0%, #808080 50%, #505050 100%);
  background: -ms-linear-gradient(top, #505050 0%, #808080 50%, #505050 100%);
  background: linear-gradient(top, #505050 0%, #808080 50%, #505050 100%);
  border: solid 2px #c0c0c0;
  font-weight:bold;
  text-align:justify;
  color: #afeeee;
  padding: 4px;
  FONT-SIZE: 11px; FONT-FAMILY: Tahoma; 
  z-index:10;
  visibility:hidden;
}
</style>
<?php
if(!empty($_GET['atkEG'])){
print '</HEAD><body>';
 $eggGet = obr_chis($_GET['atkEG']);
 $eggIsset = first('SELECT pb.title,e.base_id_egg,e.attac_one FROM eggs e INNER JOIN poke_base pb on pb.id=e.base_id_egg WHERE e.users_egg=%d AND e.id_egg=%d',$_SESSION['id'],$eggGet);
 if(!empty($eggIsset['base_id_egg'])){
   if($eggIsset['attac_one'] == 0){
     $eggs_atk  = select('SELECT ap.atac_name, ae.atac_id  
                          FROM attac_egg ae
                          INNER JOIN attac_power ap 
                          ON ap.atac_id=ae.atac_id 
                          WHERE ae.poke_base_id=%d',$eggIsset['base_id_egg']);
     if(!empty($eggs_atk)){ 
      if(!empty($_POST['atc'])){
        $pos = obr_chis($_POST['atc']);
        $b = first('SELECT id,atac_id FROM attac_egg WHERE poke_base_id=%d AND atac_id=%d',$eggIsset['base_id_egg'],$pos);
        if(!empty($b['id'])){
          update('eggs',array('attac_one'=>$b['atac_id']),'id_egg='.(int)$eggGet);
          die("<script>alert('Ататка успешно выучена.');window.close();</script>");
        }else{
          print '<b style="color:#000;padding:5px;">ERROR ATTAK POST!</b>';
        }
      }
      print '<center><h2>Изуение атак для яйца: #'.$eggIsset['title'].' (ID: '.$eggGet.').<br>Базовый номер яйца: #'.$eggIsset['base_id_egg'].'.</h2></center>';
      print '<font color="gold"><b>* Изучить можно только одну атаку, переучить атаку нельзя.</b></font>';
      print '<table style="padding: 5px; margin: 15px;"> 
              <form action="" method="post" >
              <tr>
                <td colspan=2>
                  <b style="color:#000;padding:5px;">Выбирите атаку:</b>
                </td>
              </tr>';
      foreach($eggs_atk as $eggs_atc_go){
        print '<tr>                              
                <td>
                  <input type="radio" name="atc" value="'.$eggs_atc_go['atac_id'].'" style="cursor:hand">
                </td>
                <td>
                  <a href="javascript:" onClick="window.open(\'/game.php?go=atk&id='.$eggs_atc_go['atac_id'].'\',\'atc\',\'width=800,height=250,scrollbars=yes\');return true;"><img src="/img/other/inf.png"></a>
                  <b>'.$eggs_atc_go['atac_name'].'</b>
                </td>
               </tr>';
      }
      print '<tr><td colspan=2><b><input name="submit" type="submit" value="Изучить"> </b></td></tr></form></table>';
     }else{
      print '<b style="color:#000;padding:5px;">Данный покемон не может изучать яйцевые атаки!</b>';
     }
   }else{
      print '<b style="color:#000;padding:5px;">У яйца: #'.$eggIsset['title'].', яйцевая атака уже была изучена!</b>';
   }
 }else{
   print '<b style="color:#000;padding:5px;">ERROR POKEMON EGG!</b>';
 }
print '</body></html>';
exit;
}
if($eggCount['count'] <= 0) die('<h2>На данный момент Ваша инкубаторная камера пуста!<h2>');
?>
<script type='text/javascript' src='/script/jquery.js'></script>
<script>
function defPosition(event) { // координаты мыши
    var x = y = 0;
    if (document.attachEvent != null) {
        x = window.event.clientX + (document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft);
        y = window.event.clientY + (document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop);
    } else if (!document.attachEvent && document.addEventListener) {
        x = event.clientX + window.scrollX;
        y = event.clientY + window.scrollY;
    } else {
        // Do nothing
    }
    return {x:x, y:y};
}
function tip(event, txt) {
   if (txt) {
      document.getElementById('divTip').style.left=defPosition(event).x+15;
      document.getElementById('divTip').style.top=defPosition(event).y+10;
      document.getElementById('divTip').innerHTML=txt;
      document.getElementById('divTip').style.visibility='visible';
    } 
  else 
      document.getElementById('divTip').style.visibility='hidden';
}
function pic(ID,sitID,am,at) {
  for (s=0;s<document.images.length;s++) document.images[s].style.border='1px';
  document.getElementById("pic"+ID).style.border='1px solid #ffffff';
  document.getElementById("pic"+ID).style.padding='2px';
  document.getElementById('formit')['eggID'].value=sitID;
  document.getElementById('formit')['attak'].value=at;    
  eval("CURpic.src=pic"+ID+".src");
  CURname.innerHTML=document.getElementById('divTip').innerHTML;
}
function useAttak(add) {
  document.getElementById('add').value=add;
  document.getElementById('formit').submit();
}
function loadItems(strn){
  if(strn == false) var s = 1; else var s = strn; 
  $.ajax({  
    type: "GET",  
    url: "game.php",          
    data: "go=eggs&sendZap=true&sendZapStr="+escape(s),   
    success: function(txt){
       $("#inv").html(txt);
    }
  });
}
function useAttak(add){
  window.open('/game.php?go=eggs&atkEG='+add,'atkEG','location=no,width=550,height=480,toolbar=1');
}
</script>
  
 <STYLE>
IMG {width:24; height:24; visibility:visible; margin:3px}
IMG.item{CURSOR:POINTER;}
div.item {
		background-image: url('/css/img/tactile_noise.png');
		margin:1px;
		float:left;
		width: 35px;
		height: 35px;
}
.block { 
    width: 320px; 
    background-image: url('/css/img/tactile_noise.png'); 
    padding: 5px; 
    border: solid 2px black; 
    font-family:Georgia;
    font-size:11pt;
    color: #ffffff;
    border-radius:1;
} p.regAutWindow{
  background-color: #000;
  margin-top: 20px;
  border:2px solid #FFF;
  font-weight:bold;
  color:#f7f21a;
  padding:4px;
  -moz-box-shadow: 0 0 30px #c0c0c0;
  -webkit-box-shadow: 0 0 30px #c0c0c0; 
  box-shadow: 0 0 30px #c0c0c0; 
  -moz-border-radius: 7px;
  -webkit-border-radius: 7px;
  border-radius: 7px;
  width:500px;
}
</STYLE>
</HEAD>
<BODY>
<?php 
if(isset($_SESSION['TEXT_ITEMS_ERROR'])){
  echo "<p class='regAutWindow'><b><span style=\"color:#edafc9;\">".$_SESSION['TEXT_ITEMS_ERROR'].".</span></b></p>";
  unset ($_SESSION['TEXT_ITEMS_ERROR']);
}
if(isset($_SESSION['TEXT_ITEMS_USE'])){
  echo "<p class='regAutWindow'><b><span style=\"color:gold;\">".$_SESSION['TEXT_ITEMS_USE'].".</span></b></p>";
  unset ($_SESSION['TEXT_ITEMS_USE']);
}
?>
<div style="width:350; height:500;">
<h2>Инкубаторная камера</h2> 
<b><center><a href="game.php?go=items">В инвентарь</a> </center></b>
<div id="divTip"></div>
<TABLE align="left" width="600">
  <TR>
    <TD width="345" valign=top>
      <DIV ID="inv"></DIV>
    </TD>
    <TD valign=top> </TD>
  </TR>
</TABLE>
<TABLE  width="320"  style="border-radius:15; background-image: url('/css/img/random_grey_variations.png');">
<form action="game.php?go=eggs" method="post" id="formit">
  <tr> 
    <td width="25%"" align="center">
      <img ID="CURpic" src="img/blank.gif" width="24" height="24" border="0" style="CURSOR:DEFAULT"> 
    </td>
  </tr>
  <tr> 
    <td width="25%">
      <div id="CURname" class="block">&nbsp;</div>
    </td>
  </tr>
  <tr>
    <td align="center">
      <table>
       <tr>
        <td>
          <input name="attak" type="hidden" value="">
          <input name="eggID" type="hidden" value="">
          <input name="but0" type="button" value="Изучить атаку" onclick="if (document.getElementById('formit')['attak'].value == 0 && document.getElementById('formit')['eggID'].value > 0) {  useAttak(document.getElementById('formit')['eggID'].value); }">
        </td>
       </tr>
      </table>
    </TD>
  </tr>
</form>
</TABLE>
</div>
<script>
  loadItems(1);
</script>
</body> 
</html>     