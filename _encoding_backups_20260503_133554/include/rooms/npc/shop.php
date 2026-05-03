<?
require_once($_SERVER['DOCUMENT_ROOT'].'/include/function/funtction.minus.item.php');
$time = time();
unset ($_SESSION['pokupka']);
unset ($_SESSION['NoMoney']);
$_SESSION['pokupka'] = false;
$okPokupka = false;
$kirka = false;
if (!empty($_POST['items']) AND !empty($_POST['cool'])) { 
    $items = obr_chis($_POST['items']);
    $cool  = obr_chis($_POST['cool']);
    $res   = 999999999999;
      if ($_POST['items'] == 1){       
            $res = $cool*250;
            $itemPokup = 3;
            $_SESSION['pokupka'] = "<font color=gold>Р вЂ™РЎвЂ№ РЎС“Р Т‘Р В°РЎвЂЎР Р…Р С• Р С—РЎР‚Р ВµР С•Р В±РЎР‚Р ВµР В»Р С‘: Р СџР С•Р С”Р ВµР В±Р С•Р В», Р Р† Р С”Р С•Р В»Р С‘РЎвЂЎР ВµРЎРѓРЎвЂљР Р†Р Вµ: ".$cool." РЎв‚¬РЎвЂљ.</font>";
            $okPokupka = true;
      }elseif($_POST['items'] == 2){
            $res = $cool*500;
            $itemPokup = 15;
            $_SESSION['pokupka'] = "<font color=gold>Р вЂ™РЎвЂ№ РЎС“Р Т‘Р В°РЎвЂЎР Р…Р С• Р С—РЎР‚Р ВµР С•Р В±РЎР‚Р ВµР В»Р С‘: Р В­Р Р…Р ВµРЎР‚Р С–Р ВµРЎвЂљР С‘Р С”, Р Р† Р С”Р С•Р В»Р С‘РЎвЂЎР ВµРЎРѓРЎвЂљР Р†Р Вµ: ".$cool." РЎв‚¬РЎвЂљ.</font>";
            $okPokupka = true;
      }elseif($_POST['items'] == 3){
            $res = $cool*500000;
            $itemPokup = 22;
            $_SESSION['pokupka'] = "<font color=gold>Р вЂ™РЎвЂ№ РЎС“Р Т‘Р В°РЎвЂЎР Р…Р С• Р С—РЎР‚Р ВµР С•Р В±РЎР‚Р ВµР В»Р С‘: Р В¤Р С•Р Р…Р В°РЎР‚Р С‘Р С”, Р Р† Р С”Р С•Р В»Р С‘РЎвЂЎР ВµРЎРѓРЎвЂљР Р†Р Вµ: ".$cool." РЎв‚¬РЎвЂљ.</font>";
            $okPokupka = true;
      }elseif($_POST['items'] == 4){
        if(!provitems(23,1)){
            $cool = 1;
            $res = $cool*350000;
            $itemPokup = 23;
            $_SESSION['pokupka'] = "<font color=gold>Р вЂ™РЎвЂ№ РЎС“Р Т‘Р В°РЎвЂЎР Р…Р С• Р С—РЎР‚Р ВµР С•Р В±РЎР‚Р ВµР В»Р С‘: Р С™Р В°Р СР ВµР Р…Р Р…Р В°РЎРЏ Р С™Р С‘РЎР‚Р С”Р В°, Р Р† Р С”Р С•Р В»Р С‘РЎвЂЎР ВµРЎРѓРЎвЂљР Р†Р Вµ: ".$cool." РЎв‚¬РЎвЂљ.</font>";
            $okPokupka = true;
        }else{
           $_SESSION['pokupka'] = "<font color=brown>Р Р€ Р вЂ™Р В°РЎРѓ РЎС“Р В¶Р Вµ Р ВµРЎРѓРЎвЂљРЎРЉ Р С™Р В°Р СР ВµР Р…Р Р…Р В°РЎРЏ Р С™Р С‘РЎР‚Р С”Р В°!</font>";
           $okPokupka = false;
           $kirka = true;
        }
      }elseif($_POST['items'] == 5){
        if(!provitems(69,1)){
            $cool = 1;
            $res = $cool*125000;
            $itemPokup = 69;
            $_SESSION['pokupka'] = "<font color=gold>Р вЂ™РЎвЂ№ РЎС“Р Т‘Р В°РЎвЂЎР Р…Р С• Р С—РЎР‚Р ВµР С•Р В±РЎР‚Р ВµР В»Р С‘: Р СџРЎР‚Р С•Р С—РЎС“РЎРѓР С” Р Р…Р В° Р В­Р В»Р ВµР С”РЎвЂљРЎР‚Р С•РЎРѓРЎвЂљР В°Р Р…РЎвЂ Р С‘РЎР‹, Р Р† Р С”Р С•Р В»Р С‘РЎвЂЎР ВµРЎРѓРЎвЂљР Р†Р Вµ: ".$cool." РЎв‚¬РЎвЂљ.</font>";
            $okPokupka = true;
        }else{
           $_SESSION['pokupka'] = "<font color=brown>Р Р€ Р вЂ™Р В°РЎРѓ РЎС“Р В¶Р Вµ Р ВµРЎРѓРЎвЂљРЎРЉ Р СџРЎР‚Р С•Р С—РЎС“РЎРѓР С” Р Р…Р В° Р В­Р В»Р ВµР С”РЎвЂљРЎР‚Р С•РЎРѓРЎвЂљР В°Р Р…РЎвЂ Р С‘РЎР‹!</font>";
           $okPokupka = false;
           $kirka = true;
        }
      }
    if(provitems(1,$res) && $okPokupka){
      minus_item($res,1);
      plus_item($cool,$itemPokup);
    }else{ 
      $okPokupka = false;
    }
  if($okPokupka == false && $kirka == false){
    $_SESSION['NoMoney'] = true;  
    $name = "Р СџРЎР‚Р С•Р Т‘Р В°Р Р†Р ВµРЎвЂ ";
    $about = 'Р Р€ Р вЂ™Р В°РЎРѓ Р Р…Р ВµР Т‘Р С•РЎРѓРЎвЂљР В°РЎвЂљР С•РЎвЂЎР Р…Р С• Р Т‘Р ВµР Р…Р ВµР С–, Р Т‘Р В»РЎРЏ РЎРЊРЎвЂљР С•Р в„– Р С—Р С•Р С”РЎС“Р С—Р С”Р С‘! Р вЂ”Р В°РЎвЂ¦Р С•Р Т‘Р С‘РЎвЂљР Вµ Р С”Р В°Р С” Р Р…Р С‘Р В±РЎС“Р Т‘РЎРЉ Р Р† РЎРѓР В»Р ВµР Т‘РЎС“РЎР‹РЎвЂ°Р С‘Р в„– РЎР‚Р В°Р В·. Р Р€Р Т‘Р В°РЎвЂЎР Р…Р С•Р С–Р С• Р вЂ™Р В°Р С Р Т‘Р Р…РЎРЏ.';
    $pers = "<a href='game.php?go=char'>Р СџРЎР‚Р С•РЎРѓРЎвЂљР С‘РЎвЂљР Вµ, Р Р†РЎРѓР ВµР С–Р С• Р Т‘Р С•Р В±РЎР‚Р С•Р С–Р С•!</a>"; 
  }
}
if (isset($_GET['npc'])){
  $quest_isset_const = 1; 
  $pers_b = $_GET['npc'];
  $name = "Р СџР С•Р С”Р ВµР СР В°РЎР‚Р С”Р ВµРЎвЂљ";
  if ($pers_b=="2" AND empty($_SESSION['NoMoney'])){
      $about =" 
      <div style='width:100%; height:100%; overflow: scroll;'> 
      ".($_SESSION['pokupka']?$_SESSION['pokupka']:'')."
      <br>
      <table>
        <tr>
          <td>
            <img src='img/items/3.png' width='24' height='24'>
          </td>
          <td> 
            <b>Р СџР С•Р С”Р ВµР В±Р С•Р В» x1</b><br> Р В¦Р ВµР Р…Р В°: 250 Р СР С•Р Р…Р ВµРЎвЂљ.
          </td>
          <td colspan=2 align=center>
            <form action='' method='POST'>
            <input type=\"text\" name=\"cool\" value='1' size='6' style='color:#000;font-weight:bold;border: 2px solid #000;position:relative;top:-6px;'>
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
            <img src='img/items/15.png' width='24' height='24'>
          </td>
          <td> 
            <b>Р В­Р Р…Р ВµРЎР‚Р С–Р ВµРЎвЂљР С‘Р С” x1</b><br> Р В¦Р ВµР Р…Р В°: 500 Р СР С•Р Р…Р ВµРЎвЂљ.
          <form action='' method='POST'>
          </td>
          <td colspan=2 align=center>
            <input type=\"text\" name=\"cool\" value='1' size='6' style='color:#000;font-weight:bold;border: 2px solid #000;position:relative;top:-6px;'>
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
            <img src='img/items/22.png' width='24' height='24'>
          </td>
          <td> 
            <b>Р В¤Р С•Р Р…Р В°РЎР‚Р С‘Р С” x1</b><br> Р В¦Р ВµР Р…Р В°: 500.000 Р СР С•Р Р…Р ВµРЎвЂљ.
          <form action='' method='POST'>
          </td>
          <td colspan=2 align=center>
            <input type=\"text\" name=\"cool\" value='1' size='6' style='color:#000;font-weight:bold;border: 2px solid #000;position:relative;top:-6px;'>
          </td>
          <td colspan=2 align=left>
            <input type=\"hidden\" name=\"items\" value='3'>
            <input type=\"submit\" value=\"Р С™РЎС“Р С—Р С‘РЎвЂљРЎРЉ\" width=\"15\" height=\"15\" name=\"submit\" style='color:#000;font-weight:bold;border: 2px solid #000;position:relative;top:3px;'/>
            </form>
            <br>
            <br>
          </td>
        </tr>
        <tr>
          <td>
            <img src='img/items/23.png' width='24' height='24'>
          </td>
          <td> 
            <b>Р С™Р В°Р СР ВµР Р…Р Р…Р В°РЎРЏ Р С™Р С‘РЎР‚Р С”Р В° x1</b><br> Р В¦Р ВµР Р…Р В°: 350.000 Р СР С•Р Р…Р ВµРЎвЂљ.
          <form action='' method='POST'>
          </td>
          <td colspan=2 align=center>
            <input type=\"text\" name=\"cool\" value='1' size='6' style='color:#000;font-weight:bold;border: 2px solid #000;position:relative;top:-6px;'>
          </td>
          <td colspan=2 align=left>
            <input type=\"hidden\" name=\"items\" value='4'>
            <input type=\"submit\" value=\"Р С™РЎС“Р С—Р С‘РЎвЂљРЎРЉ\" width=\"15\" height=\"15\" name=\"submit\" style='color:#000;font-weight:bold;border: 2px solid #000;position:relative;top:3px;'/>
            </form>
            <br>
            <br>
          </td>
        </tr>
        <tr>
          <td>
            <img src='img/items/69.png' width='24' height='24'>
          </td>
          <td> 
            <b>Р СџРЎР‚Р С•Р С—РЎС“РЎРѓР С” Р Р…Р В° Р В­Р В»Р ВµР С”РЎвЂљРЎР‚Р С•РЎРѓРЎвЂљР В°Р Р…РЎвЂ Р С‘РЎР‹ x1</b><br> Р В¦Р ВµР Р…Р В°: 125.000 Р СР С•Р Р…Р ВµРЎвЂљ.
          <form action='' method='POST'>
          </td>
          <td colspan=2 align=center>
            <input type=\"text\" name=\"cool\" value='1' size='6' style='color:#000;font-weight:bold;border: 2px solid #000;position:relative;top:-6px;'>
          </td>
          <td colspan=2 align=left>
            <input type=\"hidden\" name=\"items\" value='5'>
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
