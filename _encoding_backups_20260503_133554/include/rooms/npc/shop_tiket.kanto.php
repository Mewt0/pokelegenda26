<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/include/function/function.post.items.php';

$time = time();
unset($_SESSION['pokupka'], $_SESSION['NoMoney']);
$_SESSION['pokupka'] = false;

$okPokupka = false;
$itemPokup = 0;
$cool = 1; // Р С™Р С•Р В»-Р Р†Р С• Р Р†РЎвЂ№Р Т‘Р В°Р Р†Р В°Р ВµР СРЎвЂ№РЎвЂ¦ Р С—РЎР‚Р ВµР Т‘Р СР ВµРЎвЂљР С•Р Р†

if (!empty($_POST['items'])) {
    $items = (int)$_POST['items'];
    $res = 999999999999;

    if ($items == 1) {
        $res = $cool * 500000;
        $itemPokup = 21;
        $_SESSION['pokupka'] = "<b style='color:gold;'>Р вЂ™РЎвЂ№ РЎС“Р Т‘Р В°РЎвЂЎР Р…Р С• Р С—РЎР‚Р ВµР С•Р В±РЎР‚Р ВµР В»Р С‘: Р вЂР С‘Р В»Р ВµРЎвЂљ Р Р…Р В° РЎвЂљР ВµР С—Р В»Р С•РЎвЂ¦Р С•Р Т‘: Р С™Р В°Р Р…РЎвЂљР С• - Р вЂќР В¶Р С•РЎвЂљРЎвЂљР С•, Р Р† Р С”Р С•Р В»Р С‘РЎвЂЎР ВµРЎРѓРЎвЂљР Р†Р Вµ: {$cool} РЎв‚¬РЎвЂљ.</b>";
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
        $name = "Р С™Р В°РЎРѓРЎРѓР С‘РЎР‚";
        $about = 'Р Р€ Р вЂ™Р В°РЎРѓ Р Р…Р ВµР Т‘Р С•РЎРѓРЎвЂљР В°РЎвЂљР С•РЎвЂЎР Р…Р С• Р Т‘Р ВµР Р…Р ВµР С– Р Т‘Р В»РЎРЏ РЎРЊРЎвЂљР С•Р в„– Р С—Р С•Р С”РЎС“Р С—Р С”Р С‘! Р вЂ”Р В°РЎвЂ¦Р С•Р Т‘Р С‘РЎвЂљР Вµ Р С”Р В°Р С” Р Р…Р С‘Р В±РЎС“Р Т‘РЎРЉ Р Р† РЎРѓР В»Р ВµР Т‘РЎС“РЎР‹РЎвЂ°Р С‘Р в„– РЎР‚Р В°Р В·. Р Р€Р Т‘Р В°РЎвЂЎР Р…Р С•Р С–Р С• Р вЂ™Р В°Р С Р Т‘Р Р…РЎРЏ.';
        $pers = "<a href='game.php?go=char'>Р СџРЎР‚Р С•РЎРѓРЎвЂљР С‘РЎвЂљР Вµ, Р Р†РЎРѓР ВµР С–Р С• Р Т‘Р С•Р В±РЎР‚Р С•Р С–Р С•!</a>";
    }

    unset($_POST['items']);
}

if (isset($_GET['npc'])) {
    $quest_isset_const = 1;
    $pers_b = $_GET['npc'];
    $name = "Р С™Р В°РЎРѓРЎРѓР С‘РЎР‚";

    if ($pers_b == "1" && empty($_SESSION['NoMoney'])) {
        $about = "
        <div style='width:100%; overflow: auto;'>
        " . (!empty($_SESSION['pokupka']) ? $_SESSION['pokupka'] : '') . "
        <br>
        <table width='98%'>
            <tr>
                <td width='35'><b style='color:green;'>Р РЋР С—РЎР‚Р В°Р в„–РЎвЂљ</b></td>
                <td width='150' align='center'><b style='color:green;'>Р В¦Р ВµР Р…Р В°</b></td>
                <td align='left' width='500'><b style='color:green;'>Р С›Р С—Р С‘РЎРѓР В°Р Р…Р С‘Р Вµ Р С‘ Р Р…Р В°Р В·Р Р†Р В°Р Р…Р С‘Р Вµ</b></td>
                <td align='left'><b style='color:green;'>Р СџР С•Р С”РЎС“Р С—Р С”Р В°</b></td>
            </tr>
            <tr>
                <td align='center'><img src='img/items/21.png' width='24' height='24'></td>
                <td align='center'><b>500.000</b> Р СР С•Р Р…Р ВµРЎвЂљ.<br></td>
                <td align='left'>
                    <form action='' method='POST'>
                    <br>
                    <b>Р вЂР С‘Р В»Р ВµРЎвЂљ Р Р…Р В° РЎвЂљР ВµР С—Р В»Р С•РЎвЂ¦Р С•Р Т‘: Р С™Р В°Р Р…РЎвЂљР С• - Р вЂќР В¶Р С•РЎвЂљРЎвЂљР С•;<br>Р ВР СР ВµРЎРЏ Р С—РЎР‚Р С‘ РЎРѓР ВµР В±Р Вµ РЎРЊРЎвЂљР С•РЎвЂљ Р В±Р С‘Р В»Р ВµРЎвЂљ, Р вЂ™РЎвЂ№ РЎРѓР СР С•Р В¶Р ВµРЎвЂљР Вµ РЎРѓР ВµРЎРѓРЎвЂљРЎРЉ Р Р…Р В° РЎвЂљР ВµР С—Р В»Р С•РЎвЂ¦Р С•Р Т‘ Р С‘ Р С•РЎвЂљР С—РЎР‚Р В°Р Р†Р С‘РЎвЂљРЎРЉРЎРѓРЎРЏ, Р С”Р В°Р С” Р Р† Р вЂќР В¶Р С•РЎвЂљРЎвЂљР С•, РЎвЂљР В°Р С” Р С‘ Р Р† Р С•Р В±РЎР‚Р В°РЎвЂљР Р…Р С•Р С Р Р…Р В°Р С—РЎР‚Р В°Р Р†Р В»Р ВµР Р…Р С‘Р С‘.</b>
                </td>
                <td align='left'><br>
                    <input type='hidden' name='items' value='1'>
                    <input type='submit' value='Р С™РЎС“Р С—Р С‘РЎвЂљРЎРЉ' width='15' height='15' name='submit' style='color:#000;font-weight:bold;border: 2px solid #000;position:relative;top:3px;'/>
                    </form>
                    <br><br>
                </td>
            </tr>
        </table>
        </div>";
        $pers = "<a href='game.php?go=char'>Р Р€Р в„–РЎвЂљР С‘</a>";
    }
}
?>
