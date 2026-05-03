<?php
if($myrow['groups'] == 7 || $myrow['groups'] == 10) die("<script>alert('Правонарушителям вход запрещен!'); location.href='..';</script>");
$mess_error = false;

$mBuild = first('SELECT b.town,t.townName FROM build b INNER JOIN towns t ON b.town=t.id WHERE b.id=%d',$myrow['buildmy']);
$my_Build = ($mBuild['town']?$mBuild['town']:1);
$nameTown = $mBuild['townName'];

if($my_Build == 2) die("<script>alert('Во время перелета/поездки невозможно пользоваться рынком.');</script><script>window.close();</script>");

function selectedDelet($id){
  $p = first('SELECT id_lot FROM auction_items WHERE id_lot=%d',$id);
  if(!empty($p['id_lot'])) $s = true; else $s = false;
 return $s;
}

$deleted = select('SELECT count,time_rinok,tip_item,id_lot,egg,user_id FROM auction_items WHERE time_rinok<=%d AND egg=0 LIMIT 0,20',time());
if($deleted){
  foreach($deleted as $deletedgo){ 
    $time = time();
    $fal  = false;
    if($deletedgo["time_rinok"] <= $time && $deletedgo["time_rinok"] != 'not' && $deletedgo["egg"] == 0){
      if(selectedDelet($deletedgo["id_lot"]) == true){
        $count    = $deletedgo["count"];
        $tipItem  = $deletedgo["tip_item"];
        $userItem = $deletedgo['user_id'];
        $fal = true;
      }
      delete('auction_items','id_lot='.(int)$deletedgo["id_lot"].' AND time_rinok<='.(int)$time);
      if($fal == true) {
        plus_item($count,$tipItem,$userItem);
        $textSend = "Предмет: <span style='color:brown'>".infoItems($tipItem,'name')."</span> не был продан и был возвращен в количестве: <span style='color:brown'>".$count."</span> шт.";
        messSisyem($textSend,$userItem,'Возвращение предмета');
      }
    } 
  }
}
$deletedEgg = select('SELECT time_rinok,item_id,id_lot,egg,user_id FROM auction_items WHERE time_rinok<=%d AND egg=1 LIMIT 0,20',time());
if($deletedEgg){
  foreach($deletedEgg as $deletedgo){ 
    $time = time();
    $fal  = false;
    if($deletedgo["time_rinok"] <= $time && $deletedgo["time_rinok"] != 'not' && $deletedgo["egg"] == 1){
      if(selectedDelet($deletedgo["id_lot"]) == true){
        $egg      = $deletedgo["item_id"];
        $userItem = $deletedgo['user_id'];
        $fal = true;
      }     
      delete('auction_items','id_lot='.(int)$deletedgo["id_lot"].' AND time_rinok<='.(int)$time);
      if($fal == true){
        update('eggs',array('users_egg'=>$userItem),'users_egg=3 AND id_egg='.(int)$egg);
         $ps = first('SELECT base_id_egg FROM eggs WHERE id_egg=%d',$egg); 
         $textSend = "Яйцо: <span style='color:brown'>#".infoPokeBase($ps['base_id_egg'],'title')."</span> не было продано и было возвращено.";
          messSisyem($textSend,$userItem,'Возвращение яйца');
      }
    } 
  }
}

if($_POST && isset($_GET['post'])){
  if ($myrow['pve']>0  OR $myrow['pvp']>0  OR $myrow['trade']>0) die("<script>alert('Прежде чем совершить какое-либо действие с покупкой/продажей, Вам необходимо закончить бой/обмен.'); location.href='/game.php?go=rinok'</script>");
  require_once ('include/files/items.shop.post.php');
  die();
}
if(isset($_GET['do'])){
 require_once ('include/files/items.shop.do.php');
die();
}

function selectItems($tip){
  global $my_Build;
  $i = select('SELECT DISTINCT i.id AS id,i.name FROM items i
              INNER JOIN  auction_items au
              ON au.tip_item = i.id
              WHERE i.torg !=2 AND i.id !=1 AND au.regions=%d ORDER BY id ASC',$my_Build);
  $option = false;
   if($i){
    foreach($i as $o){
          if($o['id'] == $tip) $selected = " selected "; else $selected = false; 
          $option .= '<option '.$selected.' value="'.$o['id'].'">'.$o['name'].'</option>';
    }
  }
 return '<select size="1" id="invType" onchange="loadItems(1,this.value,1);"><option value="0">Все</option>'.$option.'</select>';
}
function selectPokesEgg($tip){
  global $my_Build;
  $i = select('SELECT DISTINCT pb.id AS id, pb.title
               FROM eggs e INNER JOIN poke_base pb 
               ON e.base_id_egg=pb.id
               INNER JOIN auction_items au
               ON au.item_id=e.id_egg AND au.egg=1 
               WHERE e.users_egg=3 AND au.regions=%d ORDER BY pb.id ASC',$my_Build);
  $option = false;
   if($i){
    foreach($i as $o){     
          if($o['id'] == $tip) $selected = " selected "; else $selected = false; 
          $option .= '<option '.$selected.' value="'.$o['id'].'">#'.$o['title'].'</option>';
    }
  }
 return '<select size="1" id="invType" onchange="loadItems(1,this.value,2);"><option value="0">Все</option>'.$option.'</select>';
}


if(isset($_GET['sendZapStr']) && ($_GET['sendZapStr'] > 0) && isset($_GET['itemtip']) && !empty($_GET['tiprinok'])){
   Header('Content-Type: text/css;charset=Windows-1251');
   if(!$_GET['sendZapStr'] || !$_GET['tiprinok'] || !preg_match("|^[0-9]+$|i", $_GET['tiprinok']))  die("ERROR");
   include('include/function/itemsinpage.rinok.php');
   $_GET['page'] = $_GET['sendZapStr']-1;
   $posa = 0;
   $print = 1;
   if($_GET['tiprinok'] == 1){
      /** Итемы без типажа **/
      if($_GET['itemtip'] == 0){
        $itemsCount  = first("SELECT COUNT(*) as count FROM auction_items WHERE egg=0 AND regions=%d",$my_Build);
        $itemsinpage = new Itemsinpage($itemsCount['count']);
        $resultItems = select('SELECT a.id_lot, il.id, il.name, il.tittle, a.count, a.cena, a.created, a.user_id, a.user_id_to, a.time_rinok
                              FROM auction_items a 
                              Inner Join items il 
                              ON il.id=a.tip_item
                              WHERE a.egg=0 AND a.regions=%d  
                              ORDER BY a.tip_item ASC, a.cena ASC 
                              LIMIT %d,%d',$my_Build,$itemsinpage->get('Start'),$itemsinpage->get('Limit'));
       }
       if($_GET['itemtip']>0){
         $itemsCount  = first("SELECT COUNT(*) as count FROM auction_items WHERE egg=0 AND tip_item=%d AND regions=%d",$_GET['itemtip'],$my_Build);
         $itemsinpage = new Itemsinpage($itemsCount['count']);
         $resultItems = select('SELECT a.id_lot, il.id, il.name, il.tittle, a.count, a.cena, a.created, a.user_id, a.user_id_to, a.time_rinok
                              FROM auction_items a 
                              Inner Join items il 
                              ON il.id=a.tip_item
                              WHERE a.egg=0 AND a.tip_item=%d AND a.regions=%d  
                              ORDER BY a.tip_item ASC, a.cena ASC 
                              LIMIT %d,%d',$_GET['itemtip'],$my_Build,$itemsinpage->get('Start'),$itemsinpage->get('Limit'));     
       }
        if(empty($resultItems)) die("<b style='font-size:15px;color:gold;'>Предметы не найдены.</b>");
        $data = $itemsinpage->SmartyArr();
        $txtStran = false;
        $txtStran .= '<table width="335" style="font-weight:bold; font-size:12px;"><tr><td align="center"><small><a href="javascript:" onclick="loadItems('.($_GET['itemtip']>0?"1,".$_GET['itemtip'].",1":"1,0,1").');">Обновить</a></small><br>'.selectItems($_GET['itemtip']).'<td></tr><tr><td style="color:#000;font-weight:bold;">Страница: ';
                 for($i=0,$n=sizeof($data['Count']);$i<$n;$i++):
            		 if($data['Count'][$i][1]!=$_GET['page']){    
            		  $st = $data['Count'][$i][1]+1;	   
            			$txtStran .= '-<button class="butStr" onclick="loadItems('.($_GET['itemtip']>0?$st.",".$_GET['itemtip'].",1":$st.",0,1").')">'.$st.'</button>';
            		 }else{
                  $st2 = $data['Count'][$i][0];      
            		  $txtStran .= '-<button class="butStrYes" onclick="loadItems('.($_GET['itemtip']>0?$st2.",".$_GET['itemtip'].",1":$st2.",0,1").')">'.$st2.'</button>';    
            		 }
            		endfor;
       $txtStran .='</td></tr></table>';
       print $txtStran;
        $pos = $posa*154;
        foreach($resultItems as $itemsEcho){          
          $idLot     = $itemsEcho["id_lot"];
          $itemType  = $itemsEcho["id"];
          $names     = $itemsEcho["name"];
          $tittle    = $itemsEcho["tittle"];
          $count     = formatnum($itemsEcho["count"]);
          $cena      = formatnum($itemsEcho["cena"]);
          $date      = $itemsEcho["created"];
          $prodavec  = color_group_users($itemsEcho['user_id'],1);
          $prodavec = str_replace('"','\'',$prodavec);
          $prodavec = str_replace("'","\'",$prodavec);        
          $cenaOne   = round($itemsEcho["cena"]/$itemsEcho["count"]);
          $cenaOne   = formatnum($cenaOne);
          $pokupatel = false;
          if($itemsEcho['user_id_to'] != "no"){
            $pokup = color_group_users($itemsEcho['user_id_to'],1);
            $pokup = str_replace('"','\'',$pokup);
            $pokup = str_replace("'","\'",$pokup);
            $pokupatel =  "<b>Покупателю: </b><i>".$pokup."</i><br>";
          }
          $lotEnd = false;                                                                                                                                  
          if($itemsEcho["time_rinok"] != "not")  $lotEnd = timersOtshet($itemsEcho["time_rinok"],"<br>До окончания лота осталось: ", "Срок действия лота истек");
          $tittle = "<font color=gold><b>Цена:</b> ".$cena."</font><br><b>Цена за 1 шт:</b> ".$cenaOne."<br><b>Количество:</b> ".$count."<br><b>Предмет:</b> <i>".$names."</i><br><b>Продавец:</b> <i>".$prodavec."</i><br>".$pokupatel."<b>Дата поставки:</b> <i>".$date."</i><br><b>Описание:</b> ".$tittle."<br>".$lotEnd;
          $txr = '<b>'.$names.'</b>: <b><small>x</small>'.$count.'</b>';
          if ($tittle) $txr .='<br><span class=itemdescr>'.$tittle.'</span>'; 
          echo "<div class=\"item\"><img class=\"item\" ID=\"pic".$pos."\" src=\"img/items/".$itemType.".png\" onClick=\"pic(".$pos.",".$idLot.",".$itemType.",".$itemsEcho["count"].",'".$txr."',".$itemsEcho['user_id'].",0)\" onMouseMove=\"tip(event,'".$txr."');\" onMouseOut=\"tip(event,0); \"></div>";
          $posa  ++;
          $pos   ++;
          $print ++;
        }
   }
   elseif($_GET['tiprinok'] == 2){
      if($_GET['itemtip']==0){
        $itemsCount  = first("SELECT COUNT(*) as count FROM auction_items WHERE egg=1 AND regions=%d",$my_Build);
        $itemsinpage = new Itemsinpage($itemsCount['count']);
        $resultItems = select('SELECT a.id_lot, a.count, a.cena, a.created, a.user_id, a.user_id_to, a.time_rinok, e.base_id_egg, e.dtime, p.title
                              FROM auction_items a 
                              INNER JOIN eggs e 
                              ON e.id_egg=a.item_id AND a.egg=1
                              INNER JOIN poke_base p 
                              ON p.id=e.base_id_egg  
                              WHERE a.egg=1 AND e.users_egg=3 AND a.regions=%d 
                              ORDER BY a.egg ASC, a.cena ASC
                              LIMIT %d,%d',$my_Build,$itemsinpage->get('Start'),$itemsinpage->get('Limit'));
      }
      if($_GET['itemtip']>0){
        $itemsCount  = first("SELECT COUNT(*) as count 
                              FROM auction_items a
                              INNER JOIN eggs e
                              ON e.id_egg=a.item_id 
                              WHERE a.egg=1 AND e.base_id_egg=%d AND e.users_egg=3 AND a.regions=%d",$_GET['itemtip'],$my_Build);
        $itemsinpage = new Itemsinpage($itemsCount['count']);
        $resultItems = select('SELECT a.id_lot, a.count, a.cena, a.created, a.user_id, a.user_id_to, a.time_rinok, e.base_id_egg, e.dtime, p.title
                              FROM auction_items a 
                              INNER JOIN eggs e 
                              ON e.id_egg=a.item_id AND a.egg=1
                              INNER JOIN poke_base p 
                              ON p.id=e.base_id_egg  
                              WHERE a.egg=1 AND e.base_id_egg=%d AND e.users_egg=3 AND a.regions=%d 
                              ORDER BY a.egg ASC, a.cena ASC
                              LIMIT %d,%d',$_GET['itemtip'],$my_Build,$itemsinpage->get('Start'),$itemsinpage->get('Limit'));
      }
      if(empty($resultItems)) die("<b style='font-size:15px;color:gold;'>Яйца покемонов на рынке не найдены.</b>");
      $data = $itemsinpage->SmartyArr();
      $txtStran = false;                                                                                                                                                                                                                            
      $pos = $posa*154;
      $txtStran .= '<table width="335" style="font-weight:bold; font-size:12px;"><tr><td align="center"><small><a href="javascript:" onclick="loadItems('.($_GET['itemtip']>0?"1,".$_GET['itemtip'].",2":"1,0,2").');">Обновить</a></small><br>'.selectPokesEgg($_GET['itemtip']).'<td></tr><tr><td style="color:#000;font-weight:bold;">Страница: ';
        for($i=0,$n=sizeof($data['Count']);$i<$n;$i++):
        if($data['Count'][$i][1]!=$_GET['page']){    
          $st = $data['Count'][$i][1]+1;	   
          $txtStran .= '-<button class="butStr" onclick="loadItems('.($_GET['itemtip']>0?$st.",".$_GET['itemtip'].",2":$st.",0,2").')">'.$st.'</button>';
        }else{
          $st2 = $data['Count'][$i][0];      
          $txtStran .= '-<button class="butStrYes" onclick="loadItems('.($_GET['itemtip']>0?$st2.",".$_GET['itemtip'].",2":$st2.",0,2").')">'.$st2.'</button>';    
            		 }
        endfor;
      $txtStran .='</td></tr></table>';
      print $txtStran;
        foreach($resultItems as $itemsEcho){          
          $idLot     = $itemsEcho["id_lot"];
          $itemType  = $itemsEcho["base_id_egg"];
          $names     = $itemsEcho["title"];
          //$tittle    = $itemsEcho["title"];
          $count     = formatnum($itemsEcho["count"]);
          $cena      = formatnum($itemsEcho["cena"]);
          $date      = $itemsEcho["created"];
          $prodavec  = color_group_users($itemsEcho['user_id'],1);
          $prodavec = str_replace('"','\'',$prodavec);
          $prodavec = str_replace("'","\'",$prodavec);        
          $pokupatel = false;
          if($itemsEcho['user_id_to'] != "no"){
            $pokup = color_group_users($itemsEcho['user_id_to'],1);
            $pokup = str_replace('"','\'',$pokup);
            $pokup = str_replace("'","\'",$pokup);
            $pokupatel =  "<b>Покупателю: </b><i>".$pokup."</i><br>";
          }
          $lotEnd = false;
          $content_vilup = timersOtshet($itemsEcho['dtime'],"<br>До вылупления осталось: ", "<b>Готово к вылуплению</b>");
          if($itemsEcho["time_rinok"] != "not")  $lotEnd = timersOtshet($itemsEcho["time_rinok"],"<br>До окончания лота осталось: ", "Срок действия лота истек");
          $tittle = "<font color=gold><b>Цена:</b> ".$cena."</font><br><b>Яйцо:</b> <i>#".$names."</i><br><b>Продавец:</b> <i>".$prodavec."</i><br>".$pokupatel."<b>Дата поставки:</b> <i>".$date."</i>".$content_vilup."<br>".$lotEnd;
          $txr = '<b>#'.$names.'</b>: <b><small>x</small>'.$count.'</b>';
          if ($tittle) $txr .='<br><span class=itemdescr>'.$tittle.'</span>'; 
          $fileImg = "img/items/egg/".$itemType.".png"; if(!file_exists($fileImg)) $itemType = 999;
          echo "<div class=\"item\"><img class=\"item\" ID=\"pic".$pos."\" src=\"img/items/egg/".$itemType.".png\" onClick=\"pic(".$pos.",".$idLot.",".$itemType.",1,'".$txr."',".$itemsEcho['user_id'].",1)\" onMouseMove=\"tip(event,'".$txr."');\" onMouseOut=\"tip(event,0); \"></div>";
          $posa  ++;
          $pos   ++;
          $print ++;
        }
   }else{ die("Произошла ошибка при выборе торговой операции.");}   
for ($k=$print; $k<=154; $k++) echo "<div class=\"item\"><img src='img/blank.gif'></div>";
exit;
}
?>
<html>
<head>
<TITLE>Pokelegenda -> Список продаваемых предметов</TITLE>
<style>
BODY {
    background-image: url('/css/img/brushed_alu_dark.png');
	 position: relative;
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

INPUT,TEXTAREA {
        background-color: Ivory;
        font:8pt Tahoma;
        BORDER: #b3d0c1 1px solid;
        color: #000000;
}

SELECT {
        border-style:none;
        font:8pt Tahoma;
        color: #000000;
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
#container{
	
}


INPUT,TEXTAREA {
        background-color: Ivory;
        font:8pt Tahoma;
        BORDER: #b3d0c1 1px solid;
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
    padding-left: 15px;  
    border: solid 2px black; 
    font-size:12pt;
    color: #ffffff;
    -moz-border-radius: 7px;
    -webkit-border-radius: 7px;
    border-radius: 7px;
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
</style> 
<script type='text/javascript' src='/script/jquery.js'></script>
<script language="JavaScript">
function defPosition(event) { // координаты мыши
    var x = 0
    var y = 0;
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
function tip(event, txt){
   if (txt != 0) 
    {
      document.getElementById('divTip').style.left=defPosition(event).x+15;
      document.getElementById('divTip').style.top=defPosition(event).y+10;
      document.getElementById('divTip').innerHTML=txt;
      document.getElementById('divTip').style.visibility='visible';
    } 
  else 
      document.getElementById('divTip').style.visibility='hidden';
}

function pic(ID,idLot,itTipe,count,txt,users,egg,eggImg) {
 var user = <?php echo $_SESSION['id']; ?>;   
   for (s=0;s<document.images.length;s++)
   document.images[s].style.border='0px';
   CURname.innerHTML='';
   document.getElementById("pic"+ID).style.border='1px solid #ffffff';
   document.getElementById('formit')['toe'].value='';
   document.getElementById('formit')['to'].value='';
   if(user == users){
      document.getElementById('formit')['itemlotid'].value=idLot;
      document.getElementById('tableIT').style.display='block';
      document.getElementById('CURpost').style.display='block';
      document.getElementById('CURname').style.display='block';
      document.getElementById('formit')['itemoff'].style.display=(idLot?'inline':'none');
      document.getElementById('formit')['amount'].style.display='none';
      document.getElementById('formit')['but0'].style.display='none';
       if(egg)document.getElementById('formit')['toe'].value='1';
       if(!egg)document.getElementById('formit')['to'].value='1';
   }else{
     document.getElementById('formit')['itemlotid'].value=idLot;
     document.getElementById('formit')['amount'].value=count;
     document.getElementById('tableIT').style.display='block';
     document.getElementById('CURpost').style.display='block';
     document.getElementById('CURname').style.display='block';
     document.getElementById('formit')['itemoff'].style.display='none';
     document.getElementById('formit')['amount'].style.display=(idLot?'inline':'none');
     document.getElementById('formit')['but0'].style.display=(idLot?'inline':'none');
       if(egg)document.getElementById('formit')['toe'].value='2';
       if(!egg)document.getElementById('formit')['to'].value='2';
   }
  if(egg == 1){
    CURname.innerHTML='<center><img ID="CURpic" src="img/items/egg/'+itTipe+'.png" width="24" height="24" border="0"><br></center>'+document.getElementById('divTip').innerHTML;
  }else{
    CURname.innerHTML='<center><img ID="CURpic" src="img/items/'+itTipe+'.png" width="24" height="24" border="0"><br></center>'+document.getElementById('divTip').innerHTML;
  }
 }
function item_go() {
    var user = <?php echo $_SESSION['id'];?>;
    var a = document.getElementById('formit')['to'].value;
    var b = document.getElementById('formit')['toe'].value;
    document.getElementById('formit').submit();
}
function loadItems(strn,valueItem,tipes){
  if(strn      == false) var strn = 1;
  if(valueItem == false) var valueItem = 0;
  if(tipes     == false) var tipes = 1; 
  $.ajax({  
    type: "GET",  
    url: "game.php",          
    data: "go=rinok&sendZapStr="+strn+"&itemtip="+valueItem+"&tiprinok="+tipes,   
    success: function(txt){
       $("#inv").html(txt);
       $("#tableIT").animate({opacity: 'hide' }, "slow");
    }
  });
}   
</script>
</head>
<BODY>
<div id="divTip"></div>
<?php echo $mess_error;?>
<TABLE width="810" style="font-weight:bold;font-size:12px;"> 
  <tr>
    <td align="center">
      <b style="color:gold">Рынок региона: <?=$nameTown;?>.</b>
    </td>
  </tr>
  <tr>
    <TD align=center>
     <a href=javascript: onClick=win1=window.open('/game.php?go=rinok&do','rinok','width=530,height=500,scrollbars=yes');return true;>Выставить предмет на продажу.</a> 
    </TD>
  </tr>
    <tr>
    <TD align=center>
     <a href=javascript: onClick="loadItems(1,0,1);">Переключиться на рынок предметов.</a> | <a href=javascript: onClick="loadItems(1,0,2);">Переключиться на рынок яйц покемонов.</a> 
    </TD>
  </tr>
</TABLE>
<TABLE align="left" width="850">
  <TR>
    <TD  width="850"  valign=top>
      <DIV ID="inv">
      </DIV>
    </TD>
  </TR>
</TABLE>
<br>
<center>
<TABLE id="tableIT" class="tableTextItems" style="display:none;" align=center>
  <tr align=center id="tableIT">
    <td id="tableIT" align=center>
      <DIV id="CURname" style='display:none; border-radius: 10px;' class="block" align=left>&nbsp;</DIV>
      <DIV id="CURpost" style='display:none; border-radius: 10px;' class="block" align=center>
      <br>
        <form action="game.php?go=rinok&post" method="post" id="formit">
          <input name="amount"  style='display:none' type="text" value="" SIZE=10>
          <input name="but0"    style='display:none' type="button" value="Купить" onclick="if (document.getElementById('formit')['amount'].value > 0) { if (confirm('Вы точно хотите купить этот предмет?')) item_go();}"> 
          <input name="itemoff" style='display:none' type="button" value="Снять" onclick="if (confirm('Вы точно хотите снять этот предмет?')) item_go();"> 
          <input id='it' name="itemlotid" type="hidden" value="">
          <input name="to"  type="hidden">
          <input name="toe" type="hidden">
        </form>
       </DIV>
    </td>
  </tr>
</TABLE>
<script>
loadItems(1,0,1);
</script>
</center>
 





























   


       

   



