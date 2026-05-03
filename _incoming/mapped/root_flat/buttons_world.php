<?php
declare(strict_types=1);
/**
 * buttons_world.php РІР‚вЂќ Р СџР В°Р Р…Р ВµР В»РЎРЉ РЎС“Р С—РЎР‚Р В°Р Р†Р В»Р ВµР Р…Р С‘РЎРЏ (Р С”Р Р…Р С•Р С—Р С”Р С‘)
 * PHP 8.x: РЎРѓРЎвЂљРЎР‚Р С•Р С–Р В°РЎРЏ РЎвЂљР С‘Р С—Р С‘Р В·Р В°РЎвЂ Р С‘РЎРЏ, РЎС“Р В±РЎР‚Р В°Р Р…РЎвЂ№ РЎС“РЎРѓРЎвЂљР В°РЎР‚Р ВµР Р†РЎв‚¬Р С‘Р Вµ Р С”Р С•Р Р…РЎРѓРЎвЂљРЎР‚РЎС“Р С”РЎвЂ Р С‘Р С‘
 */

declare(strict_types=1);

header('Content-Type: text/html; charset=UTF-8');

// РІвЂќР‚РІвЂќР‚ Р СџР ВµРЎР‚Р ВµР С”Р В»РЎР‹РЎвЂЎР ВµР Р…Р С‘Р Вµ РЎвЂЎР В°РЎвЂљР В° РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚

if (!empty($_GET['gochat'])) {
    $gochat = $_GET['gochat'];
    if ($gochat === 'on') {
        update('users', ['mychat' => 2], 'id=' . (int)$_SESSION['id']);
    } elseif ($gochat === 'off') {
        update('users', ['mychat' => 1], 'id=' . (int)$_SESSION['id']);
    }
    die("<script>location.href='game.php?go=buttons';</script>");
}

// РІвЂќР‚РІвЂќР‚ Р СџР ВµРЎР‚Р ВµР С”Р В»РЎР‹РЎвЂЎР ВµР Р…Р С‘Р Вµ Р Р…Р В°Р С—Р В°Р Т‘Р ВµР Р…Р С‘Р в„– Р Т‘Р С‘Р С”Р С‘РЎвЂ¦ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р С•Р Р† РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚

if (!empty($_GET['napadenie'])) {
    $napadenie = $_GET['napadenie'];
    if ($napadenie === 'on') {
        $bTime = time() + 15;
        update('users', ['pve_button' => 1, 'atack_poke' => $bTime], 'id=' . (int)$_SESSION['id']);
    } elseif ($napadenie === 'off') {
        update('users', ['pve_button' => 0], 'id=' . (int)$_SESSION['id']);
    }
    die("<script>location.href='game.php?go=buttons';</script>");
}

// РІвЂќР‚РІвЂќР‚ Р вЂ™РЎРѓР С—Р С•Р СР С•Р С–Р В°РЎвЂљР ВµР В»РЎРЉР Р…РЎвЂ№Р Вµ РЎвЂћРЎС“Р Р…Р С”РЎвЂ Р С‘Р С‘ РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚

function imgButton(string $src, string $title, string $onclick, string $style = ''): string
{
    $style = $style ?: 'border:1px solid black;';
    return sprintf(
        '<input type="image" src="%s" title="%s" style="%s" name="button" onclick="%s">',
        htmlspecialchars($src, ENT_QUOTES),
        htmlspecialchars($title, ENT_QUOTES),
        htmlspecialchars($style, ENT_QUOTES),
        $onclick
    );
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="/css/bottons.css">
    <style>body { background-color: #696969; }</style>
</head>
<body>

<!-- Р В¤Р С•РЎР‚Р СР В° РЎвЂЎР В°РЎвЂљР В° -->
<div style="position:absolute; top:0; left:0; z-index:12;">
    <form action="/game&post" target="_location_two" method="POST" name="formchat" id="formchat">
        <table>
            <tr>
                <td width="100px">
                    <div class="inputCHAT">
                        <input name="tochat" type="text" value=""
                               style="border:none; width:100px; font-weight:bold;">
                    </div>
                </td>
                <td width="560px" height="15px">
                    <div class="inputCHAT">
                        <input type="text" name="textchat" value="" maxlength="2000"
                               style="display:inline; width:100%;">
                    </div>
                </td>
                <td width="39px">
                    <input type="image" src="/css/img/goSend.png" name="submit" value="">
                </td>
            </tr>
        </table>
    </form>
</div>

<!-- Р С™Р Р…Р С•Р С—Р С”Р С‘ РЎС“Р С—РЎР‚Р В°Р Р†Р В»Р ВµР Р…Р С‘РЎРЏ -->
<div style="position:absolute; top:0; right:0; z-index:1;">
    <table>
        <tr>
            <td align="right" height="15px">

                <?php
                // Р С™Р Р…Р С•Р С—Р С”Р В° РЎвЂЎР В°РЎвЂљР В°
                if (($myrow['mychat'] ?? 0) == 1) {
                    echo imgButton(
                        '/css/img/button/7.png',
                        'Р В§Р В°РЎвЂљ Р Р…Р В° Р В»Р С•Р С”Р В°РЎвЂ Р С‘Р С‘',
                        "location.href='/game.php?go=buttons&gochat=on';"
                    );
                } else {
                    echo imgButton(
                        '/css/img/button/8.png',
                        'Р С›Р В±РЎвЂ°Р С‘Р в„– РЎвЂЎР В°РЎвЂљ',
                        "location.href='/game.php?go=buttons&gochat=off';"
                    );
                }
                ?>

                <?php echo  imgButton('/css/img/button/1.png',  'Р РЋР СР В°Р в„–Р В»Р С‘Р С”Р С‘',         "parent.smile('block');") ?>
                <input type="image" src="/css/img/button/13.png"
                       style="border:1px solid black;" width="35" height="35"
                       title="Р РЋР С—Р С‘РЎРѓР С•Р С” Р С”Р Р†Р ВµРЎРѓРЎвЂљР С•Р Р†"
                       onclick="window.open('game.php?go=quest_list','QuestInfo','width=500,height=500,scrollbars=yes'); return true;">
                <?php echo  imgButton('/css/img/button/6.png', 'Р С›РЎвЂЎР С‘РЎРѓРЎвЂљР С‘РЎвЂљРЎРЉ РЎвЂЎР В°РЎвЂљ', 'parent.drop_chat()') ?>

                <?php
                // Р С™Р Р…Р С•Р С—Р С”Р В° Р Р…Р В°Р С—Р В°Р Т‘Р ВµР Р…Р С‘Р в„–
                if (($myrow['pve_button'] ?? 0) == 1) {
                    echo imgButton(
                        '/css/img/button/3.png',
                        'Р СњР В°Р С—Р В°Р Т‘Р ВµР Р…Р С‘Р Вµ Р Т‘Р С‘Р С”Р С‘РЎвЂ¦ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р С•Р Р† Р Р†Р С”Р В»РЎР‹РЎвЂЎР ВµР Р…Р С•!',
                        'location.href="game.php?go=buttons&napadenie=off"'
                    );
                } else {
                    echo imgButton(
                        '/css/img/button/2.png',
                        'Р СњР В°Р С—Р В°Р Т‘Р ВµР Р…Р С‘Р Вµ Р Т‘Р С‘Р С”Р С‘РЎвЂ¦ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р С•Р Р† Р Р†РЎвЂ№Р С”Р В»РЎР‹РЎвЂЎР ВµР Р…Р С•!',
                        'location.href="game.php?go=buttons&napadenie=on"'
                    );
                }
                ?>

                <?php echo  imgButton(
                    '/css/img/button/4.png',
                    'Р СџРЎР‚Р ВµР Т‘Р В»Р С•Р В¶Р ВµР Р…Р С‘Р Вµ Р С•Р В±Р СР ВµР Р…Р В°',
                    "if(document.getElementById('formchat')['tochat'].value) "
                    . "parent._location_work.location.href='/game&gets=true&trade=true&to='"
                    . "+document.getElementById('formchat')['tochat'].value;"
                ) ?>

                <?php echo  imgButton(
                    '/css/img/button/5.png',
                    'Р СџРЎР‚Р ВµР Т‘Р В»Р С•Р В¶Р С‘РЎвЂљРЎРЉ РЎР‚Р В°Р В·Р Р†Р ВµР Т‘Р ВµР Р…Р С‘Р Вµ',
                    "if(document.getElementById('formchat')['tochat'].value) "
                    . "parent._location.location.href='/game.php?go=char&newpok=1&to_tren='"
                    . "+document.getElementById('formchat')['tochat'].value;"
                ) ?>

                <?php echo  imgButton(
                    '/css/img/button/15.png',
                    'Р СџРЎР‚Р С•РЎРѓР СР С•РЎвЂљРЎР‚Р ВµРЎвЂљРЎРЉ Р В±Р С•Р С‘ Р Р…Р В° Р В»Р С•Р С”Р В°РЎвЂ Р С‘Р С‘',
                    "parent._location_work.location.href='/game&gets=true&view_battle=true'",
                    'border:1px solid black; width:35px;'
                ) ?>

                <?php
                $groups = (int)($myrow['groups'] ?? 0);

                // Р С™Р Р…Р С•Р С—Р С”Р С‘ Р СР С•Р Т‘Р ВµРЎР‚Р В°РЎвЂљР С•РЎР‚Р В°
                if (in_array($groups, [1, 2, 3], true)):
                ?>
                    <input src="/css/img/button/9.png"  title="Р вЂ“Р С‘РЎР‚Р Р…РЎвЂ№Р С" style="border:1px solid black;" name="button" onclick="parent.bbJs()"   type="image">
                    <input src="/css/img/button/10.png" title="Р С™РЎР‚Р В°РЎРѓР Р…РЎвЂ№Р С" style="border:1px solid black;" name="button" onclick="parent.redJs()"  type="image">
                    <input src="/css/img/button/11.png" title="Р РЋР С‘Р Р…Р С‘Р С"   style="border:1px solid black;" name="button" onclick="parent.blueJs()" type="image">
                <?php endif; ?>

                <?php if (in_array($groups, [1, 4], true)): ?>
                    <input src="/css/img/button/12.png" title="Р В¦Р Р†Р ВµРЎвЂљ Р Р…Р В°РЎРѓРЎвЂљР В°Р Р†Р Р…Р С‘Р С”Р В°" style="border:1px solid black;" name="button" onclick="parent.nastJs()"  type="image">
                    <input src="/css/img/button/14.png" title="Р В¦Р Р†Р ВµРЎвЂљ Р Р…Р В°РЎРѓРЎвЂљР В°Р Р†Р Р…Р С‘Р С”Р В°" style="border:1px solid black; width:35px; height:35px;" name="button" onclick="parent.yelowJs()" type="image">
                <?php endif; ?>

            </td>
        </tr>
    </table>
</div>

</body>
</html>
