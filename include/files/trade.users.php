<?php
$trade = first('SELECT * FROM tradesusers WHERE id=%d AND (userone=%d OR usertwo=%d)',$myrow['trade'],$_SESSION['id'],$_SESSION['id']);
if(empty($trade['id'])){
 die("<script>parent.loc('char');</script>Вы не ведете обмена.");
}
if(empty($_SESSION['tradeUsers'])) $_SESSION['tradeUsers'] = true;
$tradeId = $trade['id'];
if($trade['userone'] == $_SESSION['id']){
  $myStstus = $trade['useroneok'];
  $noStstus = $trade['usertwook'];
  $myUsers  = $trade['userone'];
  $noUsers  = $trade['usertwo'];
  $myOk =  'useroneok';
  $imTo = false;
}elseif($trade['usertwo'] == $_SESSION['id']){
  $myStstus = $trade['usertwook'];
  $noStstus = $trade['useroneok'];
  $myUsers  = $trade['usertwo'];
  $noUsers  = $trade['userone'];
  $myOk =  'usertwook';
  if($trade['time'] != 1) $imTo = true; else $imTo = false; 
}else{
  die("<script>parent.loc('char');</script>Вы не ведете обмена.");
}
$errorPost = false;
$okPost = false;

function tradetru(){
  global $tradeId,$myStstus,$noStstus,$myUsers,$noUsers;
  if($myStstus == 1 && $noStstus == 1){
    $tradYes = select('SELECT * FROM tradesobject WHERE tradeid=%d',$tradeId); // возвращает все
    if($tradYes){
      foreach($tradYes  as $tradYesrow){ 
        delete('tradesobject','id='.(int)$tradYesrow['id']);
        if($tradYesrow['object'] === 'item') plus_item($tradYesrow['objectcount'],$tradYesrow['objectid'],$tradYesrow['userto']);
        if($tradYesrow['object'] === 'eggs') update('eggs',array('users_egg'=>$tradYesrow['userto']),'id_egg='.(int)$tradYesrow['objectid']);
        if($tradYesrow['object'] === 'poke') update('pok_user',array('users'=>$tradYesrow['userto'], 'startepoke'=>0),'id='.(int)$tradYesrow['objectid']);        
        $arrObm = array('users'=>$tradYesrow['userid'], 'usersto'=>$tradYesrow['userto'], 'id'=>$tradYesrow['objectid'], 'cool'=>($tradYesrow['objectcount']>0?$tradYesrow['objectcount']:1), 'tipes'=>$tradYesrow['object']); 
        logGames('obmen',$arrObm);
      }    
    }else{
      // not...
    }  
  }
  unset($_SESSION['tradeUsers']);
  delete('tradesusers','id='.(int)$tradeId);
  update('users',array('trade'=>0),'id='.(int)$myUsers);
  update('users',array('trade'=>0),'id='.(int)$noUsers);
 die("<script>parent.loc('char');</script>");
}

if(!empty($_GET['torg'])){
   if($myStstus == 0){
     update('tradesusers',array($myOk=>1),'id='.(int)$tradeId);
     $myStstus = 1;
     print "
     <script language=\"JavaScript\">
      parent._location.document.getElementById('tradeststusmy').innerHTML='Согласен.';
      parent._location.document.getElementById('userone').className='okusers';
      parent._location.document.getElementById('buttonok').style.display='none';
     </script>"; 
   }
   if($myStstus == 1 && $noStstus == 1) tradetru();  
 exit;
}
  if($myStstus == 1 && $noStstus == 1) tradetru();
if(!empty($_GET['refresh'])){
    if($imTo) {
      echo "<script>parent.loc('char&trade=true&tradeid=".$myrow['trade']."');</script>";
      update('tradesusers',array('time'=>1),'id='.(int)$tradeId);
    }
    $itemNo = false;  
    $ino = select('SELECT id,object,objectid,objectcount,text FROM tradesobject WHERE tradeid=%d AND userid=%d AND userto=%d ORDER BY objectid ASC',$tradeId,$noUsers,$myUsers); 
    if(!empty($ino)){
      foreach($ino  as $inorow){
        $itemNo  .= $inorow['text'].($inorow['object'] === 'item'?' x'.$inorow['objectcount'].' ':false).'<br>';
      }
    }
  echo "
        <script language=\"JavaScript\">parent._location.document.getElementById('tradeno').innerHTML='".$itemNo."';</script>
        <script language=\"JavaScript\">
          parent._location.document.getElementById('tradeststusno').innerHTML='".($noStstus>0?'Согласен.':'')."';
          parent._location.document.getElementById('usertwo').className='".($noStstus>0?"okusers":"nousers")."';
          parent._location.document.getElementById('tradeststusmy').innerHTML='".($myStstus>0?'Согласен.':'')."';
          parent._location.document.getElementById('userone').className='".($myStstus>0?"okusers":"nousers")."';
          parent._location.document.getElementById('buttonok').style.display='".($myStstus>0?'none':'block')."';</script>
       ";
  exit;
}

if(!empty($_GET['drop']) && $_GET['drop'] == 'true'){
  $tradoff = select('SELECT * FROM tradesobject WHERE tradeid=%d',$tradeId); // возвращает все
  foreach($tradoff  as $tradoffrow){ 
    delete('tradesobject','id='.(int)$tradoffrow['id']);
    if($tradoffrow['object'] === 'item') plus_item($tradoffrow['objectcount'],$tradoffrow['objectid'],$tradoffrow['userid']);
    if($tradoffrow['object'] === 'eggs') update('eggs',array('users_egg'=>$tradoffrow['userid']),'id_egg='.(int)$tradoffrow['objectid']);
    if($tradoffrow['object'] === 'poke') update('pok_user',array('users'=>$tradoffrow['userid'], 'startepoke'=>0),'id='.(int)$tradoffrow['objectid']);
  }

  delete('tradesusers','id='.(int)$tradeId);
  update('users',array('trade'=>0),'id='.(int)$myUsers);
  update('users',array('trade'=>0),'id='.(int)$noUsers);
 die("<script>parent.loc('char');</script>");
}
if(!empty($_GET['pokeId']) && empty($_POST)){
  $pokemon = obr_chis($_GET['pokeId']);
  if($pokemon > 0){
   $xPok = first('SELECT id,users,names,lvl FROM pok_user WHERE id=%d AND users=%d AND startone=0',$pokemon,$_SESSION['id']);
   if(!empty($xPok['id'])){
     if($xPok['users'] == $myrow['id']){
      $pcount = first('SELECT COUNT(*) as count FROM pok_user WHERE users=%d AND active=1',$noUsers);
      $pcountMy = first('SELECT COUNT(*) as count FROM pok_user WHERE users=%d AND active=1',$myUsers);
      $tcount = first('SELECT COUNT(*) as count FROM tradesobject WHERE userto=%d AND object="poke"',$noUsers);
      $resCount = $pcount['count'] + (isset($tcount['count'])?$tcount['count']:0); 
      if($resCount < 6){
        if($pcountMy['count'] > 1){
         if(update('pok_user',array('users'=>3, 'startepoke'=>0),'id='.(int)$xPok['id'])){
                  insert('tradesobject',array('tradeid'=>$tradeId, 
                                              'userid'=>$_SESSION['id'], 
                                              'userto'=>$noUsers, 
                                              'object'=>'poke', 
                                              'objectid'=>$xPok['id'],
                                              'objectcount'=>1,
                                              'text'=>'<img class="item" src="img/pokeball.png"> '.$xPok['names'].' '.$xPok['lvl'].'-lvl'));
                   update('tradesusers',array('useroneok'=>0, 'usertwook'=>0,),'id='.(int)$tradeId);
                   $myStstus = 0; 
                   $noStstus = 0;  
                   $okPost = 'Покемон: '.$xPok['names'].' удачно зарегистрирован в слот обмена.';
         }else{
          $errorPost = 'Системная ошибка, попробуйте снова.';
         }
        }else{
         $errorPost = 'У Вас с собой должен остаться хотя бы один покемон.';
        }
      }else{
        $errorPost = 'У данного тренера не достаточно места для покемонов.';
      }
     }else{
       $errorPost = 'Системная ошибка, попробуйте снова.';
     }
   }else{
    $errorPost = 'Информация по этому покемону не найдена, возможно он принадлежит не Вам.';
   }
  }else{
    $errorPost = 'Не верное значение.';
  }
}
if(!empty($_GET['post']) && $_POST && empty($_GET['pokeId'])){
  if(!empty($_POST['amount']) && !empty($_POST['iditem'])){
     if($_POST['tip']>0) $egg = 1; else $egg = 0;
     $item = obr_chis($_POST['iditem']);
     $cool = obr_chis($_POST['amount']);
      if($item > 0 && $cool > 0 && $egg == 0){
         $invent = first('SELECT item_id,user_id,count FROM items_users WHERE id=%d AND user_id=%d',$item,$_SESSION['id']);
        if(!empty($invent)){
         if($invent['count'] >= $cool){
            $xitem = first('SELECT * FROM tradesobject WHERE tradeid=%d AND userid=%d AND objectid=%d',$tradeId,$_SESSION['id'],$invent['item_id']);
            $x = first('SELECT id,name FROM items WHERE id=%d AND torg=0',$invent['item_id']);
            if(!$xitem){
              if(!empty($x)){
                insert('tradesobject',array('tradeid'=>$tradeId, 
                                            'userid'=>$_SESSION['id'], 
                                            'userto'=>$noUsers, 
                                            'object'=>'item', 
                                            'objectid'=>$x['id'],
                                            'objectcount'=>$cool,
                                            'text'=>'<img class="item" src="img/items/'.$x['id'].'.png"> '.$x['name']));
                minus_item($cool,$x['id'],'',$item);
                $okPost = 'Предмет: '.$x['name'].' х'.$cool.', удачно поставлен в слот обмена.';
                update('tradesusers',array('useroneok'=>0, 'usertwook'=>0),'id='.(int)$tradeId);
                $myStstus = 0; 
                $noStstus = 0; 
              }else{
                $errorPost = 'Системная ошибка, пожалуйста, попробуйте снова.';
              }
            }else{
              if(query('UPDATE tradesobject SET objectcount=objectcount+%d WHERE id=%d AND objectid=%d',$cool,$xitem['id'],$invent['item_id'])){
                minus_item($cool,$x['id'],'',$item);
                update('tradesusers',array('useroneok'=>0, 'usertwook'=>0,),'id='.(int)$tradeId);
                $myStstus = 0; 
                $noStstus = 0;  
                $okPost = 'Предмет: '.$x['name'].' уже был зарегистрирован в слоте обмена. Теперь он обновлен.';
              }else{
                $errorPost = 'Системная ошибка, пожалуйста, попробуйте снова.';
              }              
            }
         }else{
           $errorPost = 'Количество передачи не соответствует, либо больше, количеству данного предмета.';
         }
        }else{
          $errorPost = 'Информация по этому предмету не найдена, возможно он принадлежит не Вам.';
        }
      }elseif($egg>0 && $item > 0){
        $eggsId = first('SELECT * FROM eggs WHERE id_egg=%d AND users_egg=%d',$item,$_SESSION['id']);
        if(!empty($eggsId['id_egg'])){
         if($eggsId['users_egg'] == $_SESSION['id']){
           $base = first('SELECT title FROM poke_base WHERE id=%d',$eggsId['base_id_egg']);
            if(!empty($base)){
               $tipEgg = $eggsId['base_id_egg'];
               $fileImg = "img/items/egg/".$tipEgg.".png"; 
               if(!file_exists($fileImg)) $tipEgg = 999;
                   update('eggs',array('users_egg'=>3),'id_egg='.(int)$eggsId['id_egg']);
                   insert('tradesobject',array('tradeid'=>$tradeId, 
                                              'userid'=>$_SESSION['id'], 
                                              'userto'=>$noUsers, 
                                              'object'=>'eggs', 
                                              'objectid'=>$eggsId['id_egg'],
                                              'objectcount'=>1,
                                              'text'=>'<img class="item" src="img/items/egg/'.$tipEgg.'.png"> Яйцо: #'.$base['title']));
                   update('tradesusers',array('useroneok'=>0, 'usertwook'=>0,),'id='.(int)$tradeId);
                   $myStstus = 0; 
                   $noStstus = 0;   
                $okPost = 'Яйцо: #'.$base['title'].', удачно поставлено в слот обмена.';         
            }else{
              $errorPost = 'Системная ошибка, пожалуйста, попробуйте снова. Яйцо не найдено в базе данных.';
            }
         }else{
           $errorPost = 'Системная ошибка, пожалуйста, попробуйте снова.';
         }               
        }else{
          $errorPost = 'Информация по этому яйцу не найдена, возможно он принадлежит не Вам.';
        }
      }else{
         $errorPost = 'Вы ввели не корректные данные.';
      }
  }
}
unset($_POST['iditem']);
unset($_POST['amount']);
if(!empty($_GET['tip'])  && !empty($_GET['sendZapStr'])){
  $str = obr_chis($_GET['sendZapStr']);
  $str = ($str<=0?1:$str);
  header('Content-Type: text/css;charset=Windows-1251');
  if($_GET['tip'] === 'item'){
    $_GET['page'] = $str-1;
    include('include/function/itemsinpage.trade.php');
    $itemsCount = first("SELECT COUNT(*) as count FROM items_users i Inner Join items il ON il.id=i.item_id WHERE i.user_id=%d AND il.torg=0",$_SESSION['id']);
    if($itemsCount['count'] <= 0) die('<b>На данный момент у Вас нет предметов.</b>');
    $itemsinpage = new Itemsinpage($itemsCount['count']);
    $itmesSelect = select('SELECT i.id, i.count, i.item_id, i.dattimer, i.timers, il.name, il.tittle
                           FROM items_users i 
                           Inner Join items il on il.id=i.item_id 
                           WHERE i.user_id=%d AND il.torg=0
                           ORDER BY i.item_id ASC 
                           LIMIT %d,%d',$_SESSION['id'],$itemsinpage->get('Start'),$itemsinpage->get('Limit'));
    $data = $itemsinpage->SmartyArr();
    print '<table width="335" style="font-weight:bold; font-size:12px;">
            <tr>
              <td align="center">
                <b style="font-size:18px;">Инвентарь:</b>
              <td>
            <tr>
            <tr>
              <td align="center" >
                <small><a href="javascript:" onclick="load(1,\'item\');">Обновить</a></small>
              <td>
            </tr>
      	   <tr>
      		  <td style="color:#000;font-weight:bold;">Страница: ';
             for($i=0,$n=sizeof($data['Count']);$i<$n;$i++):
        		 if($data['Count'][$i][1]!=$_GET['page']){
        		  $st = $data['Count'][$i][1]+1;	   
        			print '-<button class="butStr" onclick="load('.$st.',\'item\');">'.$st.'</button>';
        		 }else{
              $st2 = $data['Count'][$i][0];      
        		  print '-<button class="butStrYes" onclick="load('.$st2.',\'item\');">'.$st2.'</button>';    
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
          $tt_text = false; 
          if($itemsEcho["dattimer"] != "not"){
            $tt_text .= timersOtshet($itemsEcho["dattimer"],"<br>Срок действия истекет через: ", "Срок годности истек");
          }
          if($itemsEcho["timers"] != "not"){
            $tt_text .= timersOtshet($itemsEcho["timers"],"<br>Использовать можно через: ", "Готово к использованию");
          }                                                                                                                                                                                                                               
      $txr = $name.': <b><small>x</small>'.$count.'</b>';
      if ($tittle) $txr .='<br><span class=itemdescr>'.$tittle.$tt_text.'</span>';
      
      echo "<div class=\"item\"><img class=\"item\" ID=\"pic".$pos."\" src=\"img/items/".$tipItems.".png\" onClick=\"pic(".$pos.",".$id.",'".$count."',0)\" onMouseMove=\"tip(event,'".$txr."');\" onMouseOut=\"tip(event,0); \"></div>";
      $posa ++;
      $pos ++;
      $print ++;
    }
    for ($k=$print; $k<=45; $k++) echo "<div class=\"item\"><img class=\"item\" src='img/blank.gif'></div>";
    exit;  
  }elseif($_GET['tip'] === 'eggs'){
    $_GET['page'] = $str-1;
    include('include/function/itemsinpage.trade.php');
    $eggCount = first("SELECT COUNT(*) as count FROM eggs WHERE users_egg=%d",$_SESSION['id']);
    if($eggCount['count'] <= 0) die('<b>На данный момент у Вас нет ни одного яйца.</b>');
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
                    <small><a href="javascript:" onclick="load(1,\'eggs\');">Обновить</a></small>
                  <td>
                </tr>
          	   <tr>
          		  <td style="color:#000;font-weight:bold;">Страница: ';
               for($i=0,$n=sizeof($data['Count']);$i<$n;$i++):
          		 if($data['Count'][$i][1]!=$_GET['page']){
          		  $st = $data['Count'][$i][1]+1;	             
          			print '-<button class="butStr" onclick="load('.$st.',\'eggs\');">'.$st.'</button>';
          		 }else{
                $st2 = $data['Count'][$i][0];              
          		  print '-<button class="butStrYes" onclick="load('.$st2.',\'eggs\');">'.$st2.'</button>';    
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
          $fileImg = "img/items/egg/".$tipEgg.".png"; 
          if(!file_exists($fileImg)) $tipEgg = 999;
      echo "<div class=\"item\"><img class=\"item\" ID=\"pic".$pos."\" src=\"img/items/egg/".$tipEgg.".png\" onClick=\"pic(".$pos.",".$id.",'1',1)\" onMouseMove=\"tip(event,'".$txr."');\" onMouseOut=\"tip(event,0); \"></div>";
      $posa ++;
      $pos ++;
      $print ++;
    }
    for ($k=$print; $k<=45; $k++) echo "<div class=\"item\"><img src='img/blank.gif'></div>";
   exit;
  }else{
     $poke_ac_one = select('SELECT id,names,lvl FROM pok_user WHERE users=%d AND active=1 AND startone=0 ORDER BY basenum ASC',$_SESSION['id']);
      if($poke_ac_one){
        echo "<table>";
        foreach($poke_ac_one as $poke){
            echo "<tr>
                    <td>                     
                        <a style=\"font-weight:bold;\" href='/game.php?go=char&trade=true&tradeid=".$_GET['tradeid']."&pokeId=".$poke['id']."'>
                        <img class=\"item\" src='img/pokeball.png' width='24'>  #".$poke['names']."
                        </a> 
                        <span align=center style=\"font-size:12;color:Black;font-weight:bold;\">- ".$poke['lvl']."-lvl<br></span>
                    </td>
                  </tr>";
        }
        echo "</table>";
      }else{
        die('<b>На данный момент у Вас нет покемонов доступных для обмена.</b>');
      }
     die();
  }
}
if(!empty($_GET['itemOff'])){
 $ax = first('SELECT id,userid,object,objectcount,objectid,text FROM tradesobject WHERE id=%d AND userid=%d AND tradeid=%d',obr_chis($_GET['itemOff']),$_SESSION['id'],$tradeId);
 if(!empty($ax)){
  delete('tradesobject','id='.(int)$ax['id']);
  if($ax['object'] === 'item'){
    plus_item($ax['objectcount'],$ax['objectid']);
    $okPost = 'Предмет: '.$ax['text'].', удачно возвращен в количестве: '.$ax['objectcount'].' шт.';
  }
  if($ax['object'] === 'eggs'){
   update('eggs',array('users_egg'=>$ax['userid']),'id_egg='.(int)$ax['objectid']);
   $okPost = 'Предмет: '.$ax['text'].', удачно возвращено.';
  }
  if($ax['object'] === 'poke'){
   update('pok_user',array('users'=>$ax['userid'], 'startepoke'=>0),'id='.(int)$ax['objectid']);
   $okPost = 'Покемон: '.$ax['text'].', удачно возвращен.';
  }
  update('tradesusers',array('useroneok'=>0, 'usertwook'=>0,),'id='.(int)$tradeId);
  $myStstus = 0; 
  $noStstus = 0;   
 }else{
    $errorPost = 'Ошибка при удалении. Попробуйте снова.';
 }
}

$itemMy = false;
$itemNo = false;

$imy = select('SELECT id,object,objectid,objectcount,text FROM tradesobject WHERE tradeid=%d AND userid=%d AND userto=%d ORDER BY objectid ASC',$tradeId,$myUsers,$noUsers);
$ino = select('SELECT id,object,objectid,objectcount,text FROM tradesobject WHERE tradeid=%d AND userid=%d AND userto=%d ORDER BY objectid ASC',$tradeId,$noUsers,$myUsers);

if(!empty($imy)){
  foreach($imy  as $imyrow){
    $itemMy  .= '<a href="/game.php?go=char&trade=true&tradeid='.$trade['id'].'&itemOff='.$imyrow['id'].'" target="_chat_two" onclick="window.location.href=\'/game.php?go=char&trade=true&tradeid='.$trade['id'].'&itemOff='.$imyrow['id'].'\'; return false;">'.$imyrow['text'].($imyrow['object'] === 'item'?' x'.$imyrow['objectcount'].' ':false).'</a><br>';
  }
}
if(!empty($ino)){
  foreach($ino  as $inorow){
    $itemNo  .= $inorow['text'].($inorow['object'] === 'item'?' x'.$inorow['objectcount'].' ':false).'<br>';
  }
}
  
?>
<html>
<head>
<title>Pokelegenda -> Обмен</title>
<script type="text/javascript" src="fancybox/jquery-1.3.2.min.js"></script>
<script type="text/javascript">
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
function str_replace(search, replace, subject){
  return subject.split(search).join(replace);
}
function pic(ID,sitID,am,uw) {
  for (s=0;s<document.images.length;s++) document.images[s].style.border='1px';
  am = str_replace('.','',am);
  document.getElementById('formit')['amount'].value=am;
  document.getElementById('val').type=(uw?'hidden':'text');
  document.getElementById('formit')['tip'].value=uw;
  document.getElementById('formit')['iditem'].value=sitID; 
  document.getElementById("pic"+ID).style.border='1px solid #ffffff';
  document.getElementById("pic"+ID).style.padding='2px';
  document.getElementById("CURpic").src=document.getElementById("pic"+ID).src;
  document.getElementById("CURpic").style.border='2px solid #000';
  document.getElementById("CURpic").style.padding='2px';
  document.getElementById("formB").style.display='block';  
  contentB.innerHTML=document.getElementById('divTip').innerHTML;
}
function load(strn,tip){       
  if(strn == false)  var strn = 1;
  //if(tip  == false)  var tip = 'item';
  $.ajax({  
    type: "GET",  
    url: "game.php",          
    data: "go=char&trade=true&tradeid=<?=$trade['id'];?>&tip="+tip+"&sendZapStr="+strn,   
    success: function(txt){
       $("#inv").html(txt);
       if(tip == 'item') $("#itemTrad").html('<img src="/img/other/itradeon.png"  width="32">');
        else  $("#itemTrad").html('<img src="/img/other/itradeoff.png"  width="32">');
       if(tip == 'eggs') $("#eggsTrad").html('<img src="/img/other/etradeon.png"  width="32">');
        else  $("#eggsTrad").html('<img src="/img/other/etradeoff.png"  width="32">');
       if(tip == 'poke') $("#pokeTrad").html('<img src="/img/other/ptradeon.png"  width="32">');
        else  $("#pokeTrad").html('<img src="/img/other/ptradeoff.png"  width="32">');
    }
  });
}
function use() {
  document.getElementById('formit').submit();
}
</script>
<style>
body, html {
 background-color: #696969;
 position: relative;
 margin:1px; 
}
IMG.item {width:24px; height:24px; visibility:visible; margin:3px; CURSOR:POINTER;}
div.item {
		background-image: url('/css/img/tactile_noise.png');
		margin:1px;
		float:left;
		width: 35px;
		height: 35px;
}
div.trades{
  background-image: url('/css/img/vichy.png');
  position: absolute;
  top: 2px;
  left: 10px;
  width: 97%;
  height: 96%;
  min-height: 320px;
  overflow: auto;
  border: 2px solid #000;
  padding: 8px;
  z-index: 10;
  -moz-border-radius: 10px;
  -webkit-border-radius: 10px;
  border-radius: 10px;
  text-align:center;
  -moz-box-shadow: 0 0 30px #000;
  -webkit-box-shadow: 0 0 30px #000; 
  box-shadow: 0 0 30px #000; 
}
.spans{
 position: relative;
 color: #000;
 font-size: 23px;
 font-family: Geneva, 'Comic Sans', cursive;
 text-shadow: #fff 1px 2px 3px;                            
}
#itTrade{

}
.tradeButt{
   padding-top: 20px;
   font-weight: bold;
   cursor: pointer;
}
#nametrad{
  position: absolute;
  top: 2px;
  left: 13px;
  color: #000000;
  font-weight: bold;
  font-size: 20px;
  text-shadow: #808080 2px 3px 3px; 
}
#tradeImg{
  position: absolute;
  top: 40px;
  left:3px;
}
#b{
  border: 3px solid #000;
  -moz-border-radius: 10px;
  -webkit-border-radius: 10px;
  border-radius: 10px;
  padding: 5px;
  margin-top: 5px;
  width: 94%; 
}
#b{
  position: relative;
  top: -2px;
  height: 99%;
}
#userone, #usertwo{
  border: 3px solid #000;
  -moz-border-radius: 10px;
  -webkit-border-radius: 10px;
  border-radius: 10px;
  padding: 5px;
  margin-top: 5px;
  overflow: auto;
  width: 93%;
  height: 99%;
  position: relative;
  top: -2px;
  z-index: 900;
}
.okusers{
 background: #34c924;
 filter:alpha(opacity=60); 
 opacity:0.6; 
 -moz-opacity:0.6;
}
.nousers{
  z-index: 900;
}
#userone hr, #usertwo hr {
	position: relative; 
  width: 99%;
  top: 5px;
  left: 0px;
  color: #afeeee;
	background-color:#afeeee;
	height: 1px;
	border: 1px solid #000;
	z-index: 1200;
}
#trademy, #tradeno{
  position: relative;
  top: 5px;
  left: 5px;
  font-weight: bold;
  z-index: 1200;  
}
#tradeButton{
  position: relative;
  top: -18px;
  left:3px;
}
IMG { vertical-align: middle; }
#tradeststusmy, #tradeststusno{
  position: absolute;
  right: 10px;
  top: 3px;
  font-weight: bold;
  color: #ffff00;
  z-index: 2000; 
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
  z-index:1000;
  visibility:hidden;
}
#inv{
   position: relative;
  top: -28px;
  left:3px;
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
INPUT,TEXTAREA,SELECT {
        background-color: Ivory;
        font: 12pt Tahoma;
        font-weight: bold;
        BORDER: #000 2px solid;
        color: #000000;
}
A:link, A:visited {color: #000000; text-decoration:none}
A:hover, A:active {color: #000000; text-decoration:underline}
#contentB{
  font-weight: bold;
}
#errPost{
 background: #ffc0cb;
 position: absolute;
 padding: 5px;
 font-weight: bold;
 padding-right: 10px;
 padding-bottom: 8px;
 top:0px;
 left:0px;
 -moz-border-radius: 0px 0px 5px 0px; 
 -webkit-border-radius: 0px 0px 5px 0px; 
 border-radius: 0px 0px 5px 0px;
 filter: alpha(opacity=90); 
 opacity: 0.9;
 -webkit-opacity: 0.9; 
 -moz-opacity: 0.9; 
 z-index: 700;
}
.postError:hover:after{
   content: ' X';
   position: relative;
   color: brown;
   right: -6px;
}
#okPost{
 background: #03c03c;
 position: absolute;
 padding: 5px;
 font-weight: bold;
 padding-right: 10px;
 padding-bottom: 8px;
 top:0px;
 left:0px;
 -moz-border-radius: 0px 0px 5px 0px; 
 -webkit-border-radius: 0px 0px 5px 0px; 
 border-radius: 0px 0px 5px 0px;
 filter: alpha(opacity=90); 
 opacity: 0.9;
 -webkit-opacity: 0.9; 
 -moz-opacity: 0.9; 
 z-index: 700;
}
#okPost:hover:after{
   content: ' X';
   position: absolute;
   color: brown;
   right: 2px;
   top: 30%
}
#username{
 z-index: 1900;
}
</style>
</head>
<body>
<?php
print '<div id="errPost" class="postError" style="display:'.(empty($errorPost)?"none":"block").';" onclick="window.document.getElementById(\'errPost\').style.display = \'none\'">'.(empty($errorPost)?" ":$errorPost).'</div>';
print '<div id="okPost" style="display:'.(empty($okPost)?"none":"block").';" onclick="window.document.getElementById(\'okPost\').style.display = \'none\'">'.(empty($okPost)?" ":$okPost).'</div>';
?>
<div id="divTip"></div>
<div class="trades" id="trades">
  <b class="spans">Обмен:</b>
<table style="width: 99%; min-width: 1200px; position:relative; left:5px;   height: 89%; min-height: 320px;">
 <tr>
  <td width="38">
   <div id="tradeImg"> 
    <div class="tradeButt" id="itemTrad" onclick="load(1,'item');" title="Список мои айтемов"><img src="/img/other/itradeon.png"  alt="Список мои айтемов"  width="32"></div>
    <div class="tradeButt" id="eggsTrad" onclick="load(1,'eggs');" title="Список яйц покемонов"><img src="/img/other/etradeoff.png" alt="Список яйц покемонов"  width="32"></div>
    <div class="tradeButt" id="pokeTrad" onclick="load(1,'poke');" title="Мои покемоны"><img src="/img/other/ptradeoff.png" alt="Мои покемоны"  width="34"></div>
   </div>
  </td>
  <td align="center" width="330">
    <div id="inv"><script type="text/javascript"><?=(empty($_GET['pokeId']))?"load(1,'item');":"load(1,'poke');";?></script></div>
  </td>
  <td width="120">
     <div id="tradeButton">
      <div class="tradeButt" onclick="window.location.href='/game.php?go=char&trade=true&tradeid=<?=$trade['id'];?>';" title="Обновить окно обмена"><img src="/img/other/tradereload.png"  width="32" > Обновить</div>
      <div class="tradeButt" onclick="window.location.href='/game.php?go=char&trade=true&tradeid=<?=$trade['id'];?>&drop=true';" title="Отменить сделку"><img src="/img/other/tradeno.png"  width="32"> Отмена</div>
      <div class="tradeButt" id="buttonok" style='display:<?=($myStstus>0?'none':'block');?>;' onclick="parent._chat_two.location.href='/game.php?go=char&trade=true&tradeid=<?=$trade['id'];?>&torg=true';"><img src="/img/other/tradeok.png" width="32" title="Согласиться на обмен"> OK</div> 
     </div>
  </td>
  <td>                        
   <div id="b">
    <div id="imgB" align="center"><img class=\"item\" id="CURpic" src='img/blank.gif' align="center"></div>
    <div id="contentB"></div>
    <div id="formB" align="center" style='display:none;'>
      <br>
      <form action="/game.php?go=char&trade=true&tradeid=<?=$trade['id'];?>&post=true" method="post" id="formit">
        <input id="val" name="amount" type="text" value="" size="20"><br> 
        <input name="iditem" type="hidden" value="">
        <input name="tip"  type="hidden" value="">
        <input name="but"  type="button" value="Положить" onclick="if(document.getElementById('formit')['amount'].value > 0) use();">         
      </form>
    </div>
   </div>
  </td>
  <td width="280">
    <div id="userone" lass="<?=($myStstus>0?"okusers":"nousers");?>">
     <span id="username"><?=color_group_users($myUsers);?> <span id="tradeststusmy"><?php if($myStstus > 0): ?>Согласен.<?php endif; ?></span></span>
     
     <hr>
     <div id="trademy">
        <?=$itemMy;?>
     </div>
    </div>
  </td> 
  <td width="280">
    <div id="usertwo" class="<?=($noStstus>0?"okusers":"nousers");?>">
     <span id="username"><?=color_group_users($noUsers);?> <span id="tradeststusno"><?php if($noStstus > 0): ?>Согласен.<?php endif; ?></span></span><hr>
     
     <div id="tradeno">
       <?=$itemNo;?>
     </div>
    </div>
  </td> 
 </tr>
</table>
</div>
</body>
</html>
<?php die();?>    
