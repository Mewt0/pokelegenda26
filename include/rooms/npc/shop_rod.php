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
            $_SESSION['pokupka'] = "<font color=gold>Вы удачно преобрели: Старая удочка, в количестве: ".$cool." шт.</font>";
            $okPokupka = true;
      }elseif($_POST['items'] == 2){
            /*$res = $cool*500;
            $itemPokup = 15;
            $_SESSION['pokupka'] = "<font color=gold>Вы удачно преобрели: Энергетик, в количестве: ".$cool." шт.</font>";
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
    $name = "Продавец удочек";
    $about = 'У Вас недостаточно денег, либо квестовых очков для этой покупки! Заходите как нибудь в следующий раз. Удачного Вам дня.';
    $pers = "<a href='game.php?go=char'>Простите, всего доброго!</a>"; 
  }
}
if (isset($_GET['npc'])){
  $quest_isset_const = 1; 
  $pers_b = $_GET['npc'];
  $name = "Продавец удочек";
  if ($pers_b=="3" AND empty($_SESSION['NoMoney'])){
      $about =" 
      <div style='width:100%; height:100%; overflow: scroll;'> 
      ".($_SESSION['pokupka']?$_SESSION['pokupka']:'')."
      <br>
      <table>
        <tr>
          <td>
            Спрайт
          </td>
          <td> 
            Цена
          </td>
          <td  align=left width='310px'>
            Описание и название
          </td>
          <td  align=left> 
            Покупка
          </td>
        </tr>
        <tr>
          <td>
            <img src='img/items/16.png' width='24' height='24'>
          </td>
          <td> 
            <b>250.000</b> монет; <br>
            <b>10</b> квестовых очков.
            <br>
          </td>
          <td  align=left width='310px'>
            <form action='' method='POST'>
            <br>
          <b>Старая удочка - Самодельная удочка помогающая ловить водных покемонов.</b>  
          </td>
          <td  align=left> <br>
            <input type=\"hidden\" name=\"items\" value='1'>
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
