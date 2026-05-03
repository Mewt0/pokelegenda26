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
            $_SESSION['pokupka'] = "<font color=gold>Вы удачно преобрели: Покебол, в количестве: ".$cool." шт.</font>";
            $okPokupka = true;
      }elseif($_POST['items'] == 2){
            $res = $cool*500;
            $itemPokup = 15;
            $_SESSION['pokupka'] = "<font color=gold>Вы удачно преобрели: Энергетик, в количестве: ".$cool." шт.</font>";
            $okPokupka = true;
      }elseif($_POST['items'] == 3){
            $res = $cool*500000;
            $itemPokup = 22;
            $_SESSION['pokupka'] = "<font color=gold>Вы удачно преобрели: Фонарик, в количестве: ".$cool." шт.</font>";
            $okPokupka = true;
      }elseif($_POST['items'] == 4){
        if(!provitems(23,1)){
            $cool = 1;
            $res = $cool*350000;
            $itemPokup = 23;
            $_SESSION['pokupka'] = "<font color=gold>Вы удачно преобрели: Каменная Кирка, в количестве: ".$cool." шт.</font>";
            $okPokupka = true;
        }else{
           $_SESSION['pokupka'] = "<font color=brown>У Вас уже есть Каменная Кирка!</font>";
           $okPokupka = false;
           $kirka = true;
        }
      }elseif($_POST['items'] == 5){
        if(!provitems(69,1)){
            $cool = 1;
            $res = $cool*125000;
            $itemPokup = 69;
            $_SESSION['pokupka'] = "<font color=gold>Вы удачно преобрели: Пропуск на Электростанцию, в количестве: ".$cool." шт.</font>";
            $okPokupka = true;
        }else{
           $_SESSION['pokupka'] = "<font color=brown>У Вас уже есть Пропуск на Электростанцию!</font>";
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
    $name = "Продавец";
    $about = 'У Вас недостаточно денег, для этой покупки! Заходите как нибудь в следующий раз. Удачного Вам дня.';
    $pers = "<a href='game.php?go=char'>Простите, всего доброго!</a>"; 
  }
}
if (isset($_GET['npc'])){
  $quest_isset_const = 1; 
  $pers_b = $_GET['npc'];
  $name = "Покемаркет";
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
            <b>Покебол x1</b><br> Цена: 250 монет.
          </td>
          <td colspan=2 align=center>
            <form action='' method='POST'>
            <input type=\"text\" name=\"cool\" value='1' size='6' style='color:#000;font-weight:bold;border: 2px solid #000;position:relative;top:-6px;'>
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
            <img src='img/items/15.png' width='24' height='24'>
          </td>
          <td> 
            <b>Энергетик x1</b><br> Цена: 500 монет.
          <form action='' method='POST'>
          </td>
          <td colspan=2 align=center>
            <input type=\"text\" name=\"cool\" value='1' size='6' style='color:#000;font-weight:bold;border: 2px solid #000;position:relative;top:-6px;'>
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
            <img src='img/items/22.png' width='24' height='24'>
          </td>
          <td> 
            <b>Фонарик x1</b><br> Цена: 500.000 монет.
          <form action='' method='POST'>
          </td>
          <td colspan=2 align=center>
            <input type=\"text\" name=\"cool\" value='1' size='6' style='color:#000;font-weight:bold;border: 2px solid #000;position:relative;top:-6px;'>
          </td>
          <td colspan=2 align=left>
            <input type=\"hidden\" name=\"items\" value='3'>
            <input type=\"submit\" value=\"Купить\" width=\"15\" height=\"15\" name=\"submit\" style='color:#000;font-weight:bold;border: 2px solid #000;position:relative;top:3px;'/>
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
            <b>Каменная Кирка x1</b><br> Цена: 350.000 монет.
          <form action='' method='POST'>
          </td>
          <td colspan=2 align=center>
            <input type=\"text\" name=\"cool\" value='1' size='6' style='color:#000;font-weight:bold;border: 2px solid #000;position:relative;top:-6px;'>
          </td>
          <td colspan=2 align=left>
            <input type=\"hidden\" name=\"items\" value='4'>
            <input type=\"submit\" value=\"Купить\" width=\"15\" height=\"15\" name=\"submit\" style='color:#000;font-weight:bold;border: 2px solid #000;position:relative;top:3px;'/>
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
            <b>Пропуск на Электростанцию x1</b><br> Цена: 125.000 монет.
          <form action='' method='POST'>
          </td>
          <td colspan=2 align=center>
            <input type=\"text\" name=\"cool\" value='1' size='6' style='color:#000;font-weight:bold;border: 2px solid #000;position:relative;top:-6px;'>
          </td>
          <td colspan=2 align=left>
            <input type=\"hidden\" name=\"items\" value='5'>
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
