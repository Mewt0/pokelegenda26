<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/include/function/function.post.items.php';

$time = time();
unset($_SESSION['pokupka'], $_SESSION['NoMoney']);
$_SESSION['pokupka'] = false;

$okPokupka = false;
$itemPokup = 0;
$cool = 1; // Кол-во выдаваемых предметов

if (!empty($_POST['items'])) {
    $items = (int)$_POST['items'];
    $res = 999999999999;

    if ($items == 1) {
        $res = $cool * 500000;
        $itemPokup = 21;
        $_SESSION['pokupka'] = "<b style='color:gold;'>Вы удачно преобрели: Билет на теплоход: Канто - Джотто, в количестве: {$cool} шт.</b>";
        $okPokupka = true;
    }

    if ($okPokupka && provitems(1, $res)) {
        minus_item($res, 1);
        plus_item($cool, $itemPokup);
    } else {
        $okPokupka = false;
    }

    if (!$okPokupka) {
        $_SESSION['NoMoney'] = true;
        $name = "Кассир";
        $about = 'У Вас недостаточно денег для этой покупки! Заходите как нибудь в следующий раз. Удачного Вам дня.';
        $pers = "<a href='game.php?go=char'>Простите, всего доброго!</a>";
    }

    unset($_POST['items']);
}

if (isset($_GET['npc'])) {
    $quest_isset_const = 1;
    $pers_b = $_GET['npc'];
    $name = "Кассир";

    if ($pers_b == "1" && empty($_SESSION['NoMoney'])) {
        $about = "
        <div style='width:100%; overflow: auto;'>
        " . (!empty($_SESSION['pokupka']) ? $_SESSION['pokupka'] : '') . "
        <br>
        <table width='98%'>
            <tr>
                <td width='35'><b style='color:green;'>Спрайт</b></td>
                <td width='150' align='center'><b style='color:green;'>Цена</b></td>
                <td align='left' width='500'><b style='color:green;'>Описание и название</b></td>
                <td align='left'><b style='color:green;'>Покупка</b></td>
            </tr>
            <tr>
                <td align='center'><img src='img/items/21.png' width='24' height='24'></td>
                <td align='center'><b>500.000</b> монет.<br></td>
                <td align='left'>
                    <form action='' method='POST'>
                    <br>
                    <b>Билет на теплоход: Канто - Джотто;<br>Имея при себе этот билет, Вы сможете сесть на теплоход и отправиться, как в Джотто, так и в обратном направлении.</b>
                </td>
                <td align='left'><br>
                    <input type='hidden' name='items' value='1'>
                    <input type='submit' value='Купить' width='15' height='15' name='submit' style='color:#000;font-weight:bold;border: 2px solid #000;position:relative;top:3px;'/>
                    </form>
                    <br><br>
                </td>
            </tr>
        </table>
        </div>";
        $pers = "<a href='game.php?go=char'>Уйти</a>";
    }
}
?>
