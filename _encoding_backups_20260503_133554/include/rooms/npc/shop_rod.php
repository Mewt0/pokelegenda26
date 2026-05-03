<?
$time = time();
unset ($_SESSION['pokupka']);
unset ($_SESSION['NoMoney']);
$_SESSION['pokupka'] = false;
$okPokupka = false;
if (!empty($_POST['items'])) { 
    $items = obr_chis($_POST['items']);
    $cool  = 1;
    $res   = 999999999999;
    $resQ  = 999999999999;
      if ($_POST['items'] == 1){       
            $res = $cool*250000;
            $itemPokup = 16;
            $resQ = 10;
            $_SESSION['pokupka'] = "<font color=gold>Р вЂ™РЎвЂ№ РЎС“Р Т‘Р В°РЎвЂЎР Р…Р С• Р С—РЎР‚Р ВµР С•Р В±РЎР‚Р ВµР В»Р С‘: Р РЋРЎвЂљР В°РЎР‚Р В°РЎРЏ РЎС“Р Т‘Р С•РЎвЂЎР С”Р В°, Р Р† Р С”Р С•Р В»Р С‘РЎвЂЎР ВµРЎРѓРЎвЂљР Р†Р Вµ: ".$cool." РЎв‚¬РЎвЂљ.</font>";
            $okPokupka = true;
      }elseif($_POST['items'] == 2){
            /*$res = $cool*500;
            $itemPokup = 15;
            $_SESSION['pokupka'] = "<font color=gold>Р вЂ™РЎвЂ№ РЎС“Р Т‘Р В°РЎвЂЎР Р…Р С• Р С—РЎР‚Р ВµР С•Р В±РЎР‚Р ВµР В»Р С‘: Р В­Р Р…Р ВµРЎР‚Р С–Р ВµРЎвЂљР С‘Р С”, Р Р† Р С”Р С•Р В»Р С‘РЎвЂЎР ВµРЎРѓРЎвЂљР Р†Р Вµ: ".$cool." РЎв‚¬РЎвЂљ.</font>";
            $okPokupka = true;  */
      }
    if(provitems(1,$res) && provQuestPoint($resQ) && $okPokupka){
      minus_item($res,1);
      updateQuestPoint($resQ);
      plus_item($cool,$itemPokup);
    }else{ 
      $okPokupka = false;
    }
  if($okPokupka == false){
    $_SESSION['NoMoney'] = true;  
    $name = "Р СџРЎР‚Р С•Р Т‘Р В°Р Р†Р ВµРЎвЂ  РЎС“Р Т‘Р С•РЎвЂЎР ВµР С”";
    $about = 'Р Р€ Р вЂ™Р В°РЎРѓ Р Р…Р ВµР Т‘Р С•РЎРѓРЎвЂљР В°РЎвЂљР С•РЎвЂЎР Р…Р С• Р Т‘Р ВµР Р…Р ВµР С–, Р В»Р С‘Р В±Р С• Р С”Р Р†Р ВµРЎРѓРЎвЂљР С•Р Р†РЎвЂ№РЎвЂ¦ Р С•РЎвЂЎР С”Р С•Р Р† Р Т‘Р В»РЎРЏ РЎРЊРЎвЂљР С•Р в„– Р С—Р С•Р С”РЎС“Р С—Р С”Р С‘! Р вЂ”Р В°РЎвЂ¦Р С•Р Т‘Р С‘РЎвЂљР Вµ Р С”Р В°Р С” Р Р…Р С‘Р В±РЎС“Р Т‘РЎРЉ Р Р† РЎРѓР В»Р ВµР Т‘РЎС“РЎР‹РЎвЂ°Р С‘Р в„– РЎР‚Р В°Р В·. Р Р€Р Т‘Р В°РЎвЂЎР Р…Р С•Р С–Р С• Р вЂ™Р В°Р С Р Т‘Р Р…РЎРЏ.';
    $pers = "<a href='game.php?go=char'>Р СџРЎР‚Р С•РЎРѓРЎвЂљР С‘РЎвЂљР Вµ, Р Р†РЎРѓР ВµР С–Р С• Р Т‘Р С•Р В±РЎР‚Р С•Р С–Р С•!</a>"; 
  }
}
if (isset($_GET['npc'])){
  $quest_isset_const = 1; 
  $pers_b = $_GET['npc'];
  $name = "Р СџРЎР‚Р С•Р Т‘Р В°Р Р†Р ВµРЎвЂ  РЎС“Р Т‘Р С•РЎвЂЎР ВµР С”";
  if ($pers_b=="3" AND empty($_SESSION['NoMoney'])){
      $about =" 
      <div style='width:100%; height:100%; overflow: scroll;'> 
      ".($_SESSION['pokupka']?$_SESSION['pokupka']:'')."
      <br>
      <table>
        <tr>
          <td>
            Р РЋР С—РЎР‚Р В°Р в„–РЎвЂљ
          </td>
          <td> 
            Р В¦Р ВµР Р…Р В°
          </td>
          <td  align=left width='310px'>
            Р С›Р С—Р С‘РЎРѓР В°Р Р…Р С‘Р Вµ Р С‘ Р Р…Р В°Р В·Р Р†Р В°Р Р…Р С‘Р Вµ
          </td>
          <td  align=left> 
            Р СџР С•Р С”РЎС“Р С—Р С”Р В°
          </td>
        </tr>
        <tr>
          <td>
            <img src='img/items/16.png' width='24' height='24'>
          </td>
          <td> 
            <b>250.000</b> Р СР С•Р Р…Р ВµРЎвЂљ; <br>
            <b>10</b> Р С”Р Р†Р ВµРЎРѓРЎвЂљР С•Р Р†РЎвЂ№РЎвЂ¦ Р С•РЎвЂЎР С”Р С•Р Р†.
            <br>
          </td>
          <td  align=left width='310px'>
            <form action='' method='POST'>
            <br>
          <b>Р РЋРЎвЂљР В°РЎР‚Р В°РЎРЏ РЎС“Р Т‘Р С•РЎвЂЎР С”Р В° - Р РЋР В°Р СР С•Р Т‘Р ВµР В»РЎРЉР Р…Р В°РЎРЏ РЎС“Р Т‘Р С•РЎвЂЎР С”Р В° Р С—Р С•Р СР С•Р С–Р В°РЎР‹РЎвЂ°Р В°РЎРЏ Р В»Р С•Р Р†Р С‘РЎвЂљРЎРЉ Р Р†Р С•Р Т‘Р Р…РЎвЂ№РЎвЂ¦ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р С•Р Р†.</b>  
          </td>
          <td  align=left> <br>
            <input type=\"hidden\" name=\"items\" value='1'>
            <input type=\"submit\" value=\"Р С™РЎС“Р С—Р С‘РЎвЂљРЎРЉ\" width=\"15\" height=\"15\" name=\"submit\" style='color:#000;font-weight:bold;border: 2px solid #000;position:relative;top:3px;'/>
            </form>
            <br>
            <br>
          </td>
        </tr>
      </table>
      </div>";
      $pers = "<a href='game.php?go=char'>Р Р€Р в„–РЎвЂљР С‘</a>";
    }
}
?>
