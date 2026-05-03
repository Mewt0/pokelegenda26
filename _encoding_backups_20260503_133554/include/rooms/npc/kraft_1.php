<?
$quest_isset_const = 1;
$kraft_isset_const = 1;

if(!empty($_POST['items'])){ 
 $items = obr_chis($_POST['items']);
  if($items <= 0 OR !$items){   
    $_SESSION['mess_kraft'] = "Р С›РЎв‚¬Р С‘Р В±Р С”Р В°!";
    die ("<script>window.location.href='/game.php?go=char&quest_npc=3&do=3';</script>");  
  }
 include ("kraft_1_obrab.php");
}

$name  = "Р ВР В·Р С–Р С•РЎвЂљР С•Р Р†Р В»Р ВµР Р…Р С‘Р Вµ Р С—РЎР‚Р ВµР Т‘Р СР ВµРЎвЂљР С•Р Р†";
$about = "
<div style=\"width: 100%; height:100%; overflow: auto;\" align=\"left\"> 
 ".(!empty($_SESSION['mess_kraft'])?'<b style="color:green;"><center>'.$_SESSION['mess_kraft'].'</center></b>':'')."
  <table width='100%' border='1'>
   <tr>       			
    <td width='65px' align='center'>
     <span style='font-weight:bold;color:black;'>Р РЋР С—РЎР‚Р В°Р в„–РЎвЂљ</span>
    </td>
    <td width='250px' align='center'>
     <span style='font-weight:bold;color:black;'>Р СњР В°Р В·Р Р†Р В°Р Р…Р С‘Р Вµ</span>
    </td>
    <td align='center'>
     <span style='font-weight:bold;color:black;'>Р СћРЎР‚Р ВµР В±РЎС“Р ВµР СРЎвЂ№Р Вµ Р С—РЎР‚Р ВµР Т‘Р СР ВµРЎвЂљРЎвЂ№ Р С‘ Р С‘РЎвЂ¦ Р С”Р С•Р В» - Р Р†Р С•</span>
    </td>
    <td align='center'>
     <span style='font-weight:bold;color:black;'>---</span>
    </td>
   </tr>
   <tr>
    <td width='65px' align='center'> 
      <img src='img/items/40.png' width='24' alt='Р вЂњРЎР‚Р С•Р СР С•Р Р†Р С•Р в„– Р С”Р В°Р СР ВµР Р…РЎРЉ' title='Р вЂњРЎР‚Р С•Р СР С•Р Р†Р С•Р в„– Р С”Р В°Р СР ВµР Р…РЎРЉ'>
    </td>
    <td width='250px'>
     <span style='font-weight:bold;color:black;'> 
        Р вЂњРЎР‚Р С•Р СР С•Р Р†Р С•Р в„– Р С”Р В°Р СР ВµР Р…РЎРЉ. <br> Р РЃР В°Р Р…РЎРѓ: <i style='color:#ffdb58;'>Р РЋРЎР‚Р ВµР Т‘Р Р…Р С‘Р в„–</i>.
     </span>
    </td>
    <td>
     <span style='font-weight:bold;color:black;'> 
       Р В Р В°РЎРѓРЎвЂљР Р†Р С•РЎР‚Р С‘РЎвЂљР ВµР В»РЎРЉ: <b style='color:#551A8B;'>РЎвЂ¦1</b>.<br>
       Р СљР В°Р В»Р ВµР Р…РЎРЉР С”Р С‘Р в„– Р С•РЎРѓР С”Р С•Р В»Р С•Р С” Р вЂњРЎР‚Р С•Р СР С•Р Р†Р С•Р С–Р С• Р С”Р В°Р СР Р…РЎРЏ: <b style='color:#551A8B;'>x5</b>.<br>
       Р РЋРЎР‚Р ВµР Т‘Р Р…Р С‘Р в„– Р С•РЎРѓР С”Р С•Р В»Р С•Р С” Р вЂњРЎР‚Р С•Р СР С•Р Р†Р С•Р С–Р С• Р С”Р В°Р СР Р…РЎРЏ: <b style='color:#551A8B;'>x3</b>.<br>
       Р С›Р С–РЎР‚Р С•Р СР Р…РЎвЂ№Р в„– Р С•РЎРѓР С”Р С•Р В»Р С•Р С” Р вЂњРЎР‚Р С•Р СР С•Р Р†Р С•Р С–Р С• Р С”Р В°Р СР Р…РЎРЏ: <b style='color:#551A8B;'>x1</b>.<br>
     </span>
    </td>
    <td align='center'>
      <br>
      <form action='' method='POST'>
         <input type=\"hidden\" name=\"items\" value='1'>
         <input type=\"submit\" value=\"Р ВР В·Р С–Р С•РЎвЂљР С•Р Р†Р С‘РЎвЂљРЎРЉ\" width=\"15\" height=\"15\" name=\"submit\"/ style=\"font-size:13px;font-weight:bold;color:#fff;border: 4px double #fff;background: #000;\">
      </form>
    </td>
   </tr>
   
   <tr>
    <td width='65px' align='center'> 
      <img src='img/items/41.png' width='24' alt='Р С›Р С–Р Р…Р ВµР Р…Р Р…РЎвЂ№Р в„– Р С”Р В°Р СР ВµР Р…РЎРЉ' title='Р С›Р С–Р Р…Р ВµР Р…Р Р…РЎвЂ№Р в„– Р С”Р В°Р СР ВµР Р…РЎРЉ'>
    </td>
    <td width='250px'>
     <span style='font-weight:bold;color:black;'> 
        Р С›Р С–Р Р…Р ВµР Р…Р Р…РЎвЂ№Р в„– Р С”Р В°Р СР ВµР Р…РЎРЉ. <br> Р РЃР В°Р Р…РЎРѓ: <i style='color:#ffdb58;'>Р РЋРЎР‚Р ВµР Т‘Р Р…Р С‘Р в„–</i>.
     </span>
    </td>
    <td>
     <span style='font-weight:bold;color:black;'> 
       Р В Р В°РЎРѓРЎвЂљР Р†Р С•РЎР‚Р С‘РЎвЂљР ВµР В»РЎРЉ: <b style='color:#551A8B;'>РЎвЂ¦1</b>.<br>
       Р СљР В°Р В»Р ВµР Р…РЎРЉР С”Р С‘Р в„– Р С•РЎРѓР С”Р С•Р В»Р С•Р С” Р С›Р С–Р Р…Р ВµР Р…Р Р…Р С•Р С–Р С• Р С”Р В°Р СР Р…РЎРЏ: <b style='color:#551A8B;'>x5</b>.<br>
       Р РЋРЎР‚Р ВµР Т‘Р Р…Р С‘Р в„– Р С•РЎРѓР С”Р С•Р В»Р С•Р С” Р С›Р С–Р Р…Р ВµР Р…Р Р…Р С•Р С–Р С• Р С”Р В°Р СР Р…РЎРЏ: <b style='color:#551A8B;'>x3</b>.<br>
       Р С›Р С–РЎР‚Р С•Р СР Р…РЎвЂ№Р в„– Р С•РЎРѓР С”Р С•Р В»Р С•Р С” Р С›Р С–Р Р…Р ВµР Р…Р Р…Р С•Р С–Р С• Р С”Р В°Р СР Р…РЎРЏ: <b style='color:#551A8B;'>x1</b>.<br>
     </span>
    </td>
    <td align='center'>
      <br>
      <form action='' method='POST'>
         <input type=\"hidden\" name=\"items\" value='2'>
         <input type=\"submit\" value=\"Р ВР В·Р С–Р С•РЎвЂљР С•Р Р†Р С‘РЎвЂљРЎРЉ\" width=\"15\" height=\"15\" name=\"submit\"/ style=\"font-size:13px;font-weight:bold;color:#fff;border: 4px double #fff;background: #000;\">
      </form>
    </td>
   </tr>
   
   <tr>
    <td width='65px' align='center'> 
      <img src='img/items/42.png' width='24' alt='Р вЂ™Р С•Р Т‘Р Р…РЎвЂ№Р в„– Р С”Р В°Р СР ВµР Р…РЎРЉ' title='Р вЂ™Р С•Р Т‘Р Р…РЎвЂ№Р в„– Р С”Р В°Р СР ВµР Р…РЎРЉ'>
    </td>
    <td width='250px'>
     <span style='font-weight:bold;color:black;'> 
        Р вЂ™Р С•Р Т‘Р Р…РЎвЂ№Р в„– Р С”Р В°Р СР ВµР Р…РЎРЉ. <br> Р РЃР В°Р Р…РЎРѓ: <i style='color:#ffdb58;'>Р РЋРЎР‚Р ВµР Т‘Р Р…Р С‘Р в„–</i>.
     </span>
    </td>
    <td>
     <span style='font-weight:bold;color:black;'> 
       Р В Р В°РЎРѓРЎвЂљР Р†Р С•РЎР‚Р С‘РЎвЂљР ВµР В»РЎРЉ: <b style='color:#551A8B;'>РЎвЂ¦1</b>.<br>
       Р СљР В°Р В»Р ВµР Р…РЎРЉР С”Р С‘Р в„– Р С•РЎРѓР С”Р С•Р В»Р С•Р С” Р вЂ™Р С•Р Т‘Р Р…Р С•Р С–Р С• Р С”Р В°Р СР Р…РЎРЏ: <b style='color:#551A8B;'>x5</b>.<br>
       Р РЋРЎР‚Р ВµР Т‘Р Р…Р С‘Р в„– Р С•РЎРѓР С”Р С•Р В»Р С•Р С” Р вЂ™Р С•Р Т‘Р Р…Р С•Р С–Р С• Р С”Р В°Р СР Р…РЎРЏ: <b style='color:#551A8B;'>x3</b>.<br>
       Р С›Р С–РЎР‚Р С•Р СР Р…РЎвЂ№Р в„– Р С•РЎРѓР С”Р С•Р В»Р С•Р С” Р вЂ™Р С•Р Т‘Р Р…Р С•Р С–Р С• Р С”Р В°Р СР Р…РЎРЏ: <b style='color:#551A8B;'>x1</b>.<br>
     </span>
    </td>
    <td align='center'>
      <br>
      <form action='' method='POST'>
         <input type=\"hidden\" name=\"items\" value='3'>
         <input type=\"submit\" value=\"Р ВР В·Р С–Р С•РЎвЂљР С•Р Р†Р С‘РЎвЂљРЎРЉ\" width=\"15\" height=\"15\" name=\"submit\"/ style=\"font-size:13px;font-weight:bold;color:#fff;border: 4px double #fff;background: #000;\">
      </form>
    </td>
   </tr>
   
   <tr>
    <td width='65px' align='center'> 
      <img src='img/items/43.png' width='24' alt='Р вЂєР С‘РЎРѓРЎвЂљР Р†Р ВµР Р…Р Р…РЎвЂ№Р в„– Р С”Р В°Р СР ВµР Р…РЎРЉ' title='Р вЂєР С‘РЎРѓРЎвЂљР Р†Р ВµР Р…Р Р…РЎвЂ№Р в„– Р С”Р В°Р СР ВµР Р…РЎРЉ'>
    </td>
    <td width='250px'>
     <span style='font-weight:bold;color:black;'> 
        Р вЂєР С‘РЎРѓРЎвЂљР Р†Р ВµР Р…Р Р…РЎвЂ№Р в„– Р С”Р В°Р СР ВµР Р…РЎРЉ. <br> Р РЃР В°Р Р…РЎРѓ: <i style='color:#ffdb58;'>Р РЋРЎР‚Р ВµР Т‘Р Р…Р С‘Р в„–</i>.
     </span>
    </td>
    <td>
     <span style='font-weight:bold;color:black;'> 
       Р В Р В°РЎРѓРЎвЂљР Р†Р С•РЎР‚Р С‘РЎвЂљР ВµР В»РЎРЉ: <b style='color:#551A8B;'>РЎвЂ¦1</b>.<br>
       Р СљР В°Р В»Р ВµР Р…РЎРЉР С”Р С‘Р в„– Р С•РЎРѓР С”Р С•Р В»Р С•Р С” Р вЂєР С‘РЎРѓРЎвЂљР Р†Р ВµР Р…Р Р…Р С•Р С–Р С• Р С”Р В°Р СР Р…РЎРЏ: <b style='color:#551A8B;'>x5</b>.<br>
       Р РЋРЎР‚Р ВµР Т‘Р Р…Р С‘Р в„– Р С•РЎРѓР С”Р С•Р В»Р С•Р С” Р вЂєР С‘РЎРѓРЎвЂљР Р†Р ВµР Р…Р Р…Р С•Р С–Р С• Р С”Р В°Р СР Р…РЎРЏ: <b style='color:#551A8B;'>x3</b>.<br>
       Р С›Р С–РЎР‚Р С•Р СР Р…РЎвЂ№Р в„– Р С•РЎРѓР С”Р С•Р В»Р С•Р С” Р вЂєР С‘РЎРѓРЎвЂљР Р†Р ВµР Р…Р Р…Р С•Р С–Р С• Р С”Р В°Р СР Р…РЎРЏ: <b style='color:#551A8B;'>x1</b>.<br>
     </span>
    </td>
    <td align='center'>
      <br>
      <form action='' method='POST'>
         <input type=\"hidden\" name=\"items\" value='4'>
         <input type=\"submit\" value=\"Р ВР В·Р С–Р С•РЎвЂљР С•Р Р†Р С‘РЎвЂљРЎРЉ\" width=\"15\" height=\"15\" name=\"submit\"/ style=\"font-size:13px;font-weight:bold;color:#fff;border: 4px double #fff;background: #000;\">
      </form>
    </td>
   </tr>
   
   <tr>
    <td width='65px' align='center'> 
      <img src='img/items/44.png' width='24' alt='Р вЂєРЎС“Р Р…Р Р…РЎвЂ№Р в„– Р С”Р В°Р СР ВµР Р…РЎРЉ' title='Р вЂєРЎС“Р Р…Р Р…РЎвЂ№Р в„– Р С”Р В°Р СР ВµР Р…РЎРЉ'>
    </td>
    <td width='250px'>
     <span style='font-weight:bold;color:black;'> 
        Р вЂєРЎС“Р Р…Р Р…РЎвЂ№Р в„– Р С”Р В°Р СР ВµР Р…РЎРЉ. <br> Р РЃР В°Р Р…РЎРѓ: <i style='color:#ffdb58;'>Р РЋРЎР‚Р ВµР Т‘Р Р…Р С‘Р в„–</i>.
     </span>
    </td>
    <td>
     <span style='font-weight:bold;color:black;'> 
       Р В Р В°РЎРѓРЎвЂљР Р†Р С•РЎР‚Р С‘РЎвЂљР ВµР В»РЎРЉ: <b style='color:#551A8B;'>РЎвЂ¦1</b>.<br>
       Р СљР В°Р В»Р ВµР Р…РЎРЉР С”Р С‘Р в„– Р С•РЎРѓР С”Р С•Р В»Р С•Р С” Р вЂєРЎС“Р Р…Р Р…Р С•Р С–Р С• Р С”Р В°Р СР Р…РЎРЏ: <b style='color:#551A8B;'>x5</b>.<br>
       Р РЋРЎР‚Р ВµР Т‘Р Р…Р С‘Р в„– Р С•РЎРѓР С”Р С•Р В»Р С•Р С” Р вЂєРЎС“Р Р…Р Р…Р С•Р С–Р С• Р С”Р В°Р СР Р…РЎРЏ: <b style='color:#551A8B;'>x3</b>.<br>
       Р С›Р С–РЎР‚Р С•Р СР Р…РЎвЂ№Р в„– Р С•РЎРѓР С”Р С•Р В»Р С•Р С” Р вЂєРЎС“Р Р…Р Р…Р С•Р С–Р С• Р С”Р В°Р СР Р…РЎРЏ: <b style='color:#551A8B;'>x1</b>.<br>
     </span>
    </td>
    <td align='center'>
      <br>
      <form action='' method='POST'>
         <input type=\"hidden\" name=\"items\" value='5'>
         <input type=\"submit\" value=\"Р ВР В·Р С–Р С•РЎвЂљР С•Р Р†Р С‘РЎвЂљРЎРЉ\" width=\"15\" height=\"15\" name=\"submit\"/ style=\"font-size:13px;font-weight:bold;color:#fff;border: 4px double #fff;background: #000;\">
      </form>
    </td>
   </tr>
  </table>
</div>";
$pers = "
    <a href='game.php?go=char&quest_npc=3&do=1'>Р СњР В°Р В·Р В°Р Т‘</a> 
    <a href='game.php?go=char'>Р Р€Р в„–РЎвЂљР С‘</a>";
if(!empty($_SESSION['mess_kraft'])) unset($_SESSION['mess_kraft']);
?>