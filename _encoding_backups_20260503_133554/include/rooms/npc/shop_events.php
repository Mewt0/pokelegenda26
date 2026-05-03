<?
$time = time();
unset ($_SESSION['pokupka']);
unset ($_SESSION['NoMoney']);
$_SESSION['pokupka'] = false;
$okPokupka = false;
$kirka = false;
if (!empty($_POST['items'])) { 
    $items = obr_chis($_POST['items']);
     if(!provitems(58,1) && !provitems(59,1) && !provitems(60,1)){
        if ($_POST['items'] == 1){       
              $res = 200000;
              $alm = false;
              $itemPokup = 58;
              $_SESSION['pokupka'] = "<font color=gold>Р вЂ™РЎвЂ№ РЎС“Р Т‘Р В°РЎвЂЎР Р…Р С• Р С—РЎР‚Р ВµР С•Р В±РЎР‚Р ВµР В»Р С‘: Р СњР ВµР В±Р С•Р В»РЎРЉРЎв‚¬Р С•Р в„– Р С—Р С•Р Т‘Р В°РЎР‚Р С•Р С”.</font>";
              $okPokupka = true;
        }elseif($_POST['items'] == 2){
              $res = 500000;
              $alm = 3;
              $itemPokup = 59;
              $_SESSION['pokupka'] = "<font color=gold>Р вЂ™РЎвЂ№ РЎС“Р Т‘Р В°РЎвЂЎР Р…Р С• Р С—РЎР‚Р ВµР С•Р В±РЎР‚Р ВµР В»Р С‘: Р СџР С•Р Т‘Р В°РЎР‚Р С•Р С”.</font>";
              $okPokupka = true;
        }elseif($_POST['items'] == 3){
              $res = 1000000;
              $alm = 5;
              $itemPokup = 60;
              $_SESSION['pokupka'] = "<font color=gold>Р вЂ™РЎвЂ№ РЎС“Р Т‘Р В°РЎвЂЎР Р…Р С• Р С—РЎР‚Р ВµР С•Р В±РЎР‚Р ВµР В»Р С‘: Р вЂР С•Р В»РЎРЉРЎв‚¬Р С•Р в„– Р С—Р С•Р Т‘Р В°РЎР‚Р С•Р С”.</font>";
              $okPokupka = true;
        }
      
      if($alm > 0 && $okPokupka){
         if(!provitems(2,$alm)) $okPokupka = false;
      }    
      if(provitems(1,$res) && $okPokupka){
        minus_item($res,1);
        if($alm > 0) minus_item($alm,2); 
        plus_item(1,$itemPokup);
      }else{ 
        $okPokupka = false;
      }
    if($okPokupka == false && $kirka == false){
      $_SESSION['NoMoney'] = true;  
      $name = "Р СћР С•РЎР‚Р С–Р С•Р Р†Р ВµРЎвЂ ";
      $about = 'Р Р€ Р вЂ™Р В°РЎРѓ Р Р…Р ВµР Т‘Р С•РЎРѓРЎвЂљР В°РЎвЂљР С•РЎвЂЎР Р…Р С• Р Т‘Р ВµР Р…Р ВµР С–, Р В»Р С‘Р В±Р С• Р В°Р В»Р СР В°Р В·Р С•Р Р† Р Т‘Р В»РЎРЏ РЎРЊРЎвЂљР С•Р в„– Р С—Р С•Р С”РЎС“Р С—Р С”Р С‘! Р вЂ”Р В°РЎвЂ¦Р С•Р Т‘Р С‘РЎвЂљР Вµ Р С”Р В°Р С” Р Р…Р С‘Р В±РЎС“Р Т‘РЎРЉ Р Р† РЎРѓР В»Р ВµР Т‘РЎС“РЎР‹РЎвЂ°Р С‘Р в„– РЎР‚Р В°Р В·. Р Р€Р Т‘Р В°РЎвЂЎР Р…Р С•Р С–Р С• Р вЂ™Р В°Р С Р Т‘Р Р…РЎРЏ.';
      $pers = "<a href='game.php?go=char'>Р СџРЎР‚Р С•РЎРѓРЎвЂљР С‘РЎвЂљР Вµ, Р Р†РЎРѓР ВµР С–Р С• Р Т‘Р С•Р В±РЎР‚Р С•Р С–Р С•!</a>"; 
    }
  }else{
     $_SESSION['pokupka'] = "<font color=brown>Р Р€ Р вЂ™Р В°РЎРѓ РЎС“Р В¶Р Вµ Р ВµРЎРѓРЎвЂљРЎРЉ Р С•Р Т‘Р С‘Р Р… Р С‘Р В· РЎвЂљРЎР‚Р ВµРЎвЂ¦ Р С—Р С•Р Т‘Р В°РЎР‚Р С”Р С•Р Р†.</font>";
  }
}
if (isset($_GET['npc'])){
  $quest_isset_const = 1; 
  $pers_b = $_GET['npc'];
  if ($pers_b=="3" AND empty($_SESSION['NoMoney'])){
      $name = "Р СћР С•РЎР‚Р С–Р С•Р Р†Р ВµРЎвЂ "; 
      $about =" 
      <div style='width:100%; height:100%; overflow: scroll;'> 
      ".($_SESSION['pokupka']?$_SESSION['pokupka']:'')."
      <br>
      <table>
        <tr>
          <td>
            <img src='img/items/58.png' width='24' height='24'>
          </td>
          <td> 
           <b> Р СњР ВµР В±Р С•Р В»РЎРЉРЎв‚¬Р С•Р в„– Р С—Р С•Р Т‘Р В°РЎР‚Р С•Р С” <b>x1</b><br> Р В¦Р ВµР Р…Р В°: 200.000 Р СР С•Р Р…Р ВµРЎвЂљ. </b>
          <form action='' method='POST'>
          </td>
          <td colspan=2 align=left>
            <input type=\"hidden\" name=\"items\" value='1'>
            <input type=\"submit\" value=\"Р С™РЎС“Р С—Р С‘РЎвЂљРЎРЉ\" width=\"15\" height=\"15\" name=\"submit\" style='color:#000;font-weight:bold;border: 2px solid #000;position:relative;top:3px;'/>
            </form>
            <br>
            <br>
          </td>
        </tr>
        <tr>
          <td>
            <img src='img/items/59.png' width='24' height='24'>
          </td>
          <td> 
           <b> Р СџР С•Р Т‘Р В°РЎР‚Р С•Р С” <b>x1</b><br> Р В¦Р ВµР Р…Р В°: 500.000 Р СР С•Р Р…Р ВµРЎвЂљ Р С‘ 3 Р В°Р В»Р СР В°Р В·. </b>
          <form action='' method='POST'>
          </td>
          <td colspan=2 align=left>
            <input type=\"hidden\" name=\"items\" value='2'>
            <input type=\"submit\" value=\"Р С™РЎС“Р С—Р С‘РЎвЂљРЎРЉ\" width=\"15\" height=\"15\" name=\"submit\" style='color:#000;font-weight:bold;border: 2px solid #000;position:relative;top:3px;'/>
            </form>
            <br>
            <br>
          </td>
        </tr>
        <tr>
          <td>
            <img src='img/items/60.png' width='24' height='24'>
          </td>
          <td> 
            <b>Р вЂР С•Р В»РЎРЉРЎв‚¬Р С•Р в„– Р С—Р С•Р Т‘Р В°РЎР‚Р С•Р С” <b>x1</b><br> Р В¦Р ВµР Р…Р В°: 1.000.000 Р СР С•Р Р…Р ВµРЎвЂљ Р С‘ 5 Р В°Р В»Р СР В°Р В·Р С•Р Р†.</b>
          <form action='' method='POST'>
          </td>
          <td colspan=2 align=left>
            <input type=\"hidden\" name=\"items\" value='3'>
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
