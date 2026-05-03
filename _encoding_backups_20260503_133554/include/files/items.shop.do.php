<?php
$itemsMy = select('SELECT i.id, i.count, il.name, il.tittle, il.id AS ides
                  FROM items_users i 
                  INNER JOIN items il
                  ON il.id=i.item_id
                  WHERE i.user_id=%d AND il.id != 1 AND il.torg = 0 ORDER BY il.id ASC',$_SESSION['id']);
$eggMy = select('SELECT p.title, e.dtime, e.base_id_egg, e.id_egg
                 FROM eggs e
                 INNER JOIN poke_base p
                 ON e.base_id_egg=p.id 
                 WHERE e.users_egg=%d',$_SESSION['id']);
?>
<html>
<TITLE>League-Of-Pokemons -> Р вЂ™РЎвЂ№РЎРѓРЎвЂљР В°Р Р†Р С‘РЎвЂљРЎРЉ Р С—РЎР‚Р ВµР Т‘Р СР ВµРЎвЂљ Р Р…Р В° Р С—РЎР‚Р С•Р Т‘Р В°Р В¶РЎС“</TITLE>
<head>
<style>
BODY {
	 color: #000;
	 background-color: #000;
   background-image: url('/css/img/wood_1.png');
	 position: relative;
	 text-align: center;
}
A:link, A:visited {color: #000000; text-decoration:none}
A:hover, A:active {color: #000000; text-decoration:underline}
TABLE, TD, TR {
        BORDER-COLOR: #000000;
        font-family: Verdana, Arial, Helvetica, sans-serif;
        font-size: 11px;
}
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
        font:8pt Tahoma;
        color: #000000;
}
IMG {width:24; height:24; visibility:visible; margin:3px}
IMG.item{CURSOR:POINTER;}
BODY {margin:5 5 5 5;}
div.item {
		background-image: url('/css/img/tactile_noise.png');
		margin:1px;
		float:left;
		width: 35px;
		height: 35px;
}
.block { 
    width: 320px; 
    background-image: url('/css/img/green-fibers.png'); 
    padding: 5px; 
    border: solid 2px black; 
    font-family:Georgia;
    font-size:11pt;
    color: #ffffff;
    border-radius:1;
}
</style>
<script language="JavaScript">
function defPosition(event) { // Р С”Р С•Р С•РЎР‚Р Т‘Р С‘Р Р…Р В°РЎвЂљРЎвЂ№ Р СРЎвЂ№РЎв‚¬Р С‘
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
   if (txt != 0){
      document.getElementById('divTip').style.left=defPosition(event).x+15;
      document.getElementById('divTip').style.top=defPosition(event).y+10;
      document.getElementById('divTip').innerHTML=txt;
      document.getElementById('divTip').style.visibility='visible';
    } 
  else 
      document.getElementById('divTip').style.visibility='hidden';
}
</script>
</head>
<div id="divTip"></div>
<table align="center" width="500" style = "color:black; background-image: url('/css/img/vichy.png');" border="2" cellpadding="7">
<?php
foreach($itemsMy as $itemsMyRow){      
 $id       = $itemsMyRow["id"];
 $itemType = $itemsMyRow["ides"];
 $content  = $itemsMyRow["tittle"];
 $title    = $itemsMyRow["name"];
 $count    = $itemsMyRow["count"];             
 $re = '<b>'.$title.'</b> x '.$count.'<br><i>'.$content.'</i>';
 print '
<form action="game.php?go=rinok&post" method="post">
  <tr>
    <td>
      <img src="/img/items/'.$itemType.'.png" onMouseMove="tip(event, \''.$re.'\');" onMouseOut="tip(event, 0);" width="24" height="24">
    </td>
    <td align="center">
        Р С™Р С•Р В»Р С‘РЎвЂЎР ВµРЎРѓРЎвЂљР Р†Р С•<br>
          <input name="cools" type="text" size="12" maxlength="10">
    </td>
    <td width="450" align="center"> 
        Р В¦Р ВµР Р…Р В° Р В·Р В° 1 РЎв‚¬РЎвЂљ.
        <br>
      <input type="text"   name="cena" maxlength="10">
    </td>
    <td width=450> 
        Р СџР ВµРЎР‚Р ВµР Т‘Р В°РЎвЂљРЎРЉ Р С—Р С•Р В»РЎРЉР В·Р С•Р Р†Р В°РЎвЂљР ВµР В»РЎР‹
        <br>
      <input type="text"   name="user_to" value="" maxlength="15">
      <input type="hidden" name="itemsid"  value='.$id.'>   
    </td>
  </tr> 
  <tr align="center">
    <td COLSPAN=4 align=center>
      <input type="submit" name="submit" value="Р вЂ™РЎвЂ№РЎРѓРЎвЂљР В°Р Р†Р С‘РЎвЂљРЎРЉ Р Р…Р В° Р С—РЎР‚Р С•Р Т‘Р В°Р В¶РЎС“"> 
    </td>
  </tr>
</form>';
}
foreach($eggMy as $eggMyRow){
  $egg_name = "Р Р‡Р в„–РЎвЂ Р С•: #".$eggMyRow['title'];
  $content  = timersOtshet($eggMyRow['dtime'],"<br>Р вЂќР С• Р Р†РЎвЂ№Р В»РЎС“Р С—Р В»Р ВµР Р…Р С‘РЎРЏ Р С•РЎРѓРЎвЂљР В°Р В»Р С•РЎРѓРЎРЉ: ", "<b>Р вЂњР С•РЎвЂљР С•Р Р†Р С• Р С” Р Р†РЎвЂ№Р В»РЎС“Р С—Р В»Р ВµР Р…Р С‘РЎР‹</b>");
  $itemType = $eggMyRow['base_id_egg'];
  $id       = $eggMyRow['id_egg'];
  $re       = '<b>'.$egg_name.'</b><br><i>'.$content.'</i>';
print '
<form action="game.php?go=rinok&post" method="post">
  <tr>
    <td>
      <img src="/img/items/egg/'.$itemType.'.png" onMouseMove="tip(event, \''.$re.'\');" onMouseOut="tip(event, 0);" width="24" height="24">
    </td>
    <td align="center">
       <b>'.$egg_name.'</b><input name="eggs" type="hidden" value="1">
    </td>
    <td width="450" align="center"> 
        Р В¦Р ВµР Р…Р В° Р В·Р В° 1 РЎв‚¬РЎвЂљ.
        <br>
      <input type="text"   name="cena" maxlength="10">
    </td>
    <td width=450> 
        Р СџР ВµРЎР‚Р ВµР Т‘Р В°РЎвЂљРЎРЉ Р С—Р С•Р В»РЎРЉР В·Р С•Р Р†Р В°РЎвЂљР ВµР В»РЎР‹
        <br>
      <input type="text"   name="user_to" value="" maxlength="15">
      <input type="hidden" name="eggid"  value='.$id.'>   
    </td>
  </tr> 
  <tr align="center">
    <td COLSPAN=4 align=center>
      <input type="submit" name="submit" value="Р вЂ™РЎвЂ№РЎРѓРЎвЂљР В°Р Р†Р С‘РЎвЂљРЎРЉ Р Р…Р В° Р С—РЎР‚Р С•Р Т‘Р В°Р В¶РЎС“"> 
    </td>
  </tr>
</form>';
}
?>
</table>