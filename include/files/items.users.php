<?php
include ("include/function/function.items.php");
include ("include/function/function.post.items.php");
//include ("include/function/function.minus.item.php");
$itemsCount = first("SELECT COUNT(*) as count FROM items_users WHERE user_id=%d",$_SESSION['id']);
$pos = "0";
$arrayScript = false;
if(isset($_GET['sendZapStr']) && ($_GET['sendZapStr'] > 0)){
header('Content-Type: text/css;charset=Windows-1251');
$_GET['page'] = $_GET['sendZapStr']-1;
include('include/function/itemsinpage.items.php');
$itemsinpage = new Itemsinpage($itemsCount['count']);
$itmesSelect = select('SELECT i.id, i.count, i.item_id, i.dattimer, i.timers, il.name, il.tittle, il.category, il.delet,  il.dress, il.uses, il.elementary 
                  FROM items_users i 
                  Inner Join items il on il.id=i.item_id 
                  WHERE i.user_id=%d ORDER BY i.item_id ASC LIMIT %d,%d',$_SESSION['id'],$itemsinpage->get('Start'),$itemsinpage->get('Limit'));
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
    $id = $itemsEcho["id"];
    $tipItems = $itemsEcho["item_id"];
    $tittle = $itemsEcho["tittle"];
    $name = $itemsEcho["name"];
    $count = formatnum($itemsEcho["count"]);
    $use = $itemsEcho["uses"];
    $dress = $itemsEcho["dress"];
    $elementary = $itemsEcho["elementary"];
    $delete = $itemsEcho["delet"];
    $tt_text = false; 
    if($itemsEcho["dattimer"] != "not"){
      $tt_text .= timersOtshet($itemsEcho["dattimer"],"<br>Срок действия истекет через: ", "Срок годности истек");
    }
    if($itemsEcho["timers"] != "not"){
      $tt_text .= timersOtshet($itemsEcho["timers"],"<br>Использовать можно через: ", "Готово к использованию");
    }                                                                                                                                                                                                                               
$txr = $name.': <b><small>x</small>'.$count.'</b>';
if ($tittle) $txr .='<br><span class=itemdescr>'.$tittle.$tt_text.'</span>';

echo "<div class=\"item\"><img class=\"item\" ID=\"pic".$pos."\" src=\"img/items/".$tipItems.".png\" onClick=\"pic(".$pos.",".$id.",'".$count."',".$use.",".$dress.",".$elementary.",".$delete.")\" onMouseMove=\"tip(event,'".$txr."');\" onMouseOut=\"tip(event,0); \"></div>";
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
<TITLE>League-Of-Pokemons -> Мой инвентарь</TITLE>
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
H1 {color:#8FBC8F; text-align:center}
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
if($itemsCount['count'] <= 0) die('<h1>На данный момент у вас нету ничего из инвентаря.<h1>');
?>
<script type='text/javascript' src='/script/jquery.js'></script>
<script type='text/javascript'>
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
function str_replace(search, replace, subject) {
  return subject.split(search).join(replace);
}
function pic(ID,sitID,am,uw,dr,rs,del) {
  for (s=0;s<document.images.length;s++) document.images[s].style.border='1px';
  am = str_replace('.','',am);
  document.getElementById("pic"+ID).style.border='1px solid #ffffff';
  document.getElementById("pic"+ID).style.padding='2px';
  document.getElementById('formit')['itID'].value=sitID;
  document.getElementById('formit')['amount'].value=am;
  document.getElementById('formit')['amount2'].value=am;
  document.getElementById('formit')['amount'].style.display=(del?'none':'inline');
  document.getElementById('formit')['amount2'].style.display=(del?'none':'inline');
  document.getElementById('formit')['but0'].style.display=(del?'none':'inline');
  document.getElementById('formit')['but01'].style.display=(del?'none':'inline');
   if(rs == '1'){                                                                   
      document.getElementById('formit')['but1'].style.display=(uw?'inline':'none');
      document.getElementById('formit')['but2'].style.display=(dr?'inline':'none');
      document.getElementById('formit')['pokes'].style.display=(uw||dr?'inline':'none');
      document.getElementById('formit')['but3'].style.display='none';
    }else{       
      document.getElementById('formit')['but2'].style.display='none';
      document.getElementById('formit')['but1'].style.display='none';
      document.getElementById('formit')['pokes'].style.display='none';
      document.getElementById('formit')['but3'].style.display=(rs?'inline':'none');
    }     
  eval("CURpic.src=pic"+ID+".src");
  CURname.innerHTML=document.getElementById('divTip').innerHTML;
}
function use_item(add) {
  document.getElementById('add').value=add;
  document.getElementById('formit').submit();
}
function loadItems(strn){
  if(strn == false) var s = 1; else var s = strn; 
  $.ajax({  
    type: "GET",  
    url: "game.php",          
    data: "go=items&sendZap=true&sendZapStr="+escape(s),   
    success: function(txt){
       $("#inv").html(txt);
    }
  });
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
<h2>Инвентарь</h2> 
<b><center><a href="game.php?go=eggs">В инкубаторную камеру</a> </center></b>
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
<form action="game.php?go=items" method="post" id="formit">
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
          <input name="amount" type="text" value="" size="12">
        </td>
        <td>
          <input name="but0" type="button" value="Выбросить" onclick="if (document.getElementById('formit')['amount'].value > 0) { if (confirm('Вы точно хотите выбросить этот предмет?')) use_item('drop');}">
        </td>
       </tr>
       <tr>
        <td>
          <input name="amount2" type="text" value="" size="12">
        </td>
        <td>
          <input name="but01" type="button" value="Отдать клану" onclick="if (document.getElementById('formit')['amount2'].value > 0) { if (confirm('Вы точно хотите отдать этот предмет в клан?')) use_item('clan');}"> 
        </td>
       </tr>
      </table>
    </TD>
  </tr>
  <tr>
    <td align="center">
      <?php
        $poke_zapros_item = select('SELECT id,names FROM pok_user WHERE users=%d and active=1',$_SESSION['id']);
      ?> 
      <select size="1" name="pokes" style='display:none'>
        <?php
        foreach($poke_zapros_item as $vivod_zaprosa){ 
          print '<option value="'.$vivod_zaprosa['id'].'">'.$vivod_zaprosa['names'].'</option>';
        }
        ?>
      </select>  
    </td>  
  </tr>
  <tr>
    <td valign="top" align="center"> 
      <input name="but1" style='display:none' type="button" value="Использовать" onclick="use_item('use');">  
      <input name="but2" style='display:none' type="button" value="Одеть" onclick="use_item('dress');">
      <input name="but3" style='display:none' type="button" value="Открыть" onclick="use_item('use');"> 
      <input name="itID" type="hidden" value="">
      <input name="add" id="add" type="hidden" value=""> 
    </td>
  </tr>
</form>
</TABLE>
</div>
<script type='text/javascript'>
  loadItems(1);
</script>
</body> 
</html>     