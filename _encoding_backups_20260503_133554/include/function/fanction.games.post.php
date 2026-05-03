<?php
$uses = $_SESSION['id'];
function obrabText($txt, $group){

  if($group != 1){
    $txt = stripslashes($txt);
    $txt = htmlspecialchars($txt);
    $txt = trim($txt);
  }else{
    $txt = stripslashes($txt);
    $txt = trim($txt);
  }
  $regex = "@(https?://)?(([a-zA-Z0-9.-]+)?[a-zA-Z0-9-]+(!?\.[a-zA-Z]{2,5}))+(/[^\s]*)?@";
  $txt = preg_replace_callback($regex, 'replace_link', $txt);
  return $txt;
}

function obrChis($chs){
    $chs = substr($chs, 0, 300); 
    $chs = ceil($chs);
    $chs = abs($chs);
    $chs = stripslashes($chs);
    $chs = htmlspecialchars($chs);
    $chs = trim($chs);
  return $chs;
}
  if($_POST || $_GET){
    if(isset($_POST['act']) && $_POST['act'] == 'send'){
      mysql_query("SET NAMES 'utf8'");
      $text  = obrabText($_POST['text'],2);
      $sub   = obrabText($_POST['subj'],2); 
      $user  = obrChis($_POST['users']);
      if(!empty($text) && !empty($user)){
        if(!$sub || $sub == '' || $sub == ' ') $sub = "Re...";
        $sub   = substr($sub,  0, 20);
        $user  = substr($user, 0, 20);
        $text  = substr($text, 0, 1200);
        $dat = date("Y-m-d");
        insert('sends',array('users'=>$user,'text'=>$text, 'tema'=>$sub, 'inputusers'=>$_SESSION['id'], 'date'=>$dat));
      }
    }
    if(isset($_GET['sendDel']) && ((int)$_GET['sendDel']>0)){
      header('Content-Type: text/css;charset=UTF-8');
      $smsDel = first('SELECT id FROM sends WHERE users=%d AND id=%d',$uses,$_GET['sendDel']);
      if($smsDel) delete('sends','users='.(int)$uses.' AND  id='.(int)$_GET['sendDel']);           
      if($smsDel) print 'OK'; else print 'NO';
     exit;
    }
    if(isset($_GET['sendUp']) && ((int)$_GET['sendUp']>0)){
      header('Content-Type: text/css;charset=UTF-8');
      $smsDel = first('SELECT id FROM sends WHERE users=%d AND id=%d AND active=1',$uses,$_GET['sendUp']);
      if($smsDel) update('sends',array('active'=>0),'users='.(int)$uses.' AND  id='.(int)$smsDel['id']);          
      if($smsDel) print 'OK'; else print 'NO';  
     exit;
    }               
    if(isset($_GET['sendZap']) && ((int)$_GET['sendZapStr']>0)){
      Header("Content-Type: text/css; charset=UTF-8");
      $_GET['page'] = $_GET['sendZapStr']-1;
      $acc = $_GET['page'];
      include('include/itemsinpage.class.php');
      $totalpages = first('SELECT COUNT(*) as Total FROM sends WHERE users=%d',$uses);
        $itemsinpage = new Itemsinpage($totalpages['Total']);
      $sms = select('SELECT * FROM sends WHERE users=%d ORDER BY id DESC LIMIT %d,%d',$uses,$itemsinpage->get('Start'),$itemsinpage->get('Limit'));
        if($sms) print 'OK';  else { print 'NO'; if($_GET['page']!=0) print $acc; else print 'NOT';}    
        if($sms){
          $cc = 1;
          $data = $itemsinpage->SmartyArr();
          print '
          <table>
      	   <tr>
      		  <td style="color:#000;font-weight:bold;">Р РЋРЎвЂљРЎР‚Р В°Р Р…Р С‘РЎвЂ Р В°: ';
           for($i=0,$n=sizeof($data['Count']);$i<$n;$i++):
      		 if($data['Count'][$i][1]!=$_GET['page']){
      		  $st = $data['Count'][$i][1]+1;	   
      			print '--<button class="butStr" onclick="loadSends('.$st.')">'.$st.'</button>';
      		 }else{
            $st2 = $data['Count'][$i][0];      
      		  print '--<button class="butStrYes" onclick="loadSends('.$st2.')">'.$st2.'</button>';    
      		 }
      		endfor;
      		print '</td></tr></table>';
        foreach($sms as $smsenter){ 
          if($smsenter['active'] == 1) $smsStatus = "Р СњР Вµ Р С—РЎР‚Р С•РЎвЂЎР С‘РЎвЂљР В°Р Р…Р С•"; else $smsStatus = "Р СџРЎР‚Р С•РЎвЂЎР С‘РЎвЂљР В°Р Р…Р С•";;
          print '                                                       
           <div id="send-'.$smsenter['id'].'" class="pane">
    	     <h3 onclick="effectSlow(\'#pid\','.$smsenter['id'].')">Р РЋР С•РЎРѓРЎвЂљР С•РЎРЏР Р…Р С‘Р Вµ: <span style="color:#fadadd" class="sendsStatus">'.$smsStatus.'</span>, Р С›РЎвЂљ: '.color_group_users($smsenter['inputusers']).', Р СћР ВµР СР В°: <span style="color:#fadadd">'.$smsenter['tema'].'.</span></h3>
    	     <div id="pid-'.$smsenter['id'].'" class="pid">
           '.$smsenter['text'].'
           <div class="otvets">
           <br>
           <hr style="background: #99958c;">
           Р СћР ВµР СР В°: <input id="sendSubj" value="Re..." maxlength="20"><br>
           Р С›РЎвЂљР Р†Р ВµРЎвЂљ:                                                   
                   <input id="sendPolucId" type="hidden" value="'.$smsenter['inputusers'].'">
                   <textarea id="sendTexts" name="text" style="width:100%; height:100px;" maxlength="500"></textarea>
                   <br />
                   <center>
                   <input class="subSend" type="submit"  title="Р С›РЎвЂљР С—РЎР‚Р В°Р Р†Р С‘РЎвЂљРЎРЉ" value="Р С›РЎвЂљР С—РЎР‚Р В°Р Р†Р С‘РЎвЂљРЎРЉ"  onclick="sendGo('.$smsenter['id'].')">
                   </center>
           </div>
           </div>
    	     <img src="/css/img/btn-delete.png" style = "border:0px;" alt="Р Р€Р Т‘Р В°Р В»Р С‘РЎвЂљРЎРЉ" title="Р Р€Р Т‘Р В°Р В»Р С‘РЎвЂљРЎРЉ" class="delete"  onclick="dels('.$smsenter['id'].','.$st2.')">
           <img src="/css/img/crayon.png" style = "border:0px; width: 18px; height: 18px;" alt="Р С›РЎвЂљР Р†Р ВµРЎвЂљР С‘РЎвЂљРЎРЉ" title="Р С›РЎвЂљР Р†Р ВµРЎвЂљР С‘РЎвЂљРЎРЉ" id="btOtv" class="btOtv"  onclick="otvet('.$smsenter['id'].')">
           </div>                                                                                                                                                       
          ';
        }
        }               
      exit;
    } 
  }else{
    die("<script>location.href='..'</script>");
  }
?>