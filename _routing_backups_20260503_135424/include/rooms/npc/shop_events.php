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
              $_SESSION['pokupka'] = "<font color=gold>Вы удачно преобрели: Небольшой подарок.</font>";
              $okPokupka = true;
        }elseif($_POST['items'] == 2){
              $res = 500000;
              $alm = 3;
              $itemPokup = 59;
              $_SESSION['pokupka'] = "<font color=gold>Вы удачно преобрели: Подарок.</font>";
              $okPokupka = true;
        }elseif($_POST['items'] == 3){
              $res = 1000000;
              $alm = 5;
              $itemPokup = 60;
              $_SESSION['pokupka'] = "<font color=gold>Вы удачно преобрели: Большой подарок.</font>";
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
      $name = "Торговец";
      $about = 'У Вас недостаточно денег, либо алмазов для этой покупки! Заходите как нибудь в следующий раз. Удачного Вам дня.';
      $pers = "<a href='game.php?go=char'>Простите, всего доброго!</a>"; 
    }
  }else{
     $_SESSION['pokupka'] = "<font color=brown>У Вас уже есть один из трех подарков.</font>";
  }
}
if (isset($_GET['npc'])){
  $quest_isset_const = 1; 
  $pers_b = $_GET['npc'];
  if ($pers_b=="3" AND empty($_SESSION['NoMoney'])){
      $name = "Торговец"; 
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
           <b> Небольшой подарок <b>x1</b><br> Цена: 200.000 монет. </b>
          <form action='' method='POST'>
          </td>
          <td colspan=2 align=left>
            <input type=\"hidden\" name=\"items\" value='1'>
            <input type=\"submit\" value=\"Купить\" width=\"15\" height=\"15\" name=\"submit\" style='color:#000;font-weight:bold;border: 2px solid #000;position:relative;top:3px;'/>
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
           <b> Подарок <b>x1</b><br> Цена: 500.000 монет и 3 алмаз. </b>
          <form action='' method='POST'>
          </td>
          <td colspan=2 align=left>
            <input type=\"hidden\" name=\"items\" value='2'>
            <input type=\"submit\" value=\"Купить\" width=\"15\" height=\"15\" name=\"submit\" style='color:#000;font-weight:bold;border: 2px solid #000;position:relative;top:3px;'/>
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
            <b>Большой подарок <b>x1</b><br> Цена: 1.000.000 монет и 5 алмазов.</b>
          <form action='' method='POST'>
          </td>
          <td colspan=2 align=left>
            <input type=\"hidden\" name=\"items\" value='3'>
            <input type=\"submit\" value=\"Купить\" width=\"15\" height=\"15\" name=\"submit\" style='color:#000;font-weight:bold;border: 2px solid #000;position:relative;top:3px;'/>
            </form>
            <br>
            <br>
          </td>
        </tr>
      </table>
      </div>";
      $pers = "<a href='game.php?go=char'>Уйти</a>";
    }
}
?>
