<?php
/**
 * attak_pokes.php РІР‚вЂќ Р В Р ВµР Т‘Р В°Р С”РЎвЂљР С‘РЎР‚Р С•Р Р†Р В°Р Р…Р С‘Р Вµ Р С•Р С—Р С‘РЎРѓР В°Р Р…Р С‘Р в„– Р В°РЎвЂљР В°Р С” (РЎвЂљР С•Р В»РЎРЉР С”Р С• Р Т‘Р В»РЎРЏ Р В°Р Т‘Р СР С‘Р Р…Р С‘РЎРѓРЎвЂљРЎР‚Р В°РЎвЂљР С•РЎР‚Р В°)
 * PHP 8.x: РЎС“Р В±РЎР‚Р В°Р Р…РЎвЂ№ mysql_escape_string, РЎС“РЎРѓРЎвЂљР В°РЎР‚Р ВµР Р†РЎв‚¬Р С‘Р Вµ Р С”Р С•Р Р…РЎРѓРЎвЂљРЎР‚РЎС“Р С”РЎвЂ Р С‘Р С‘, Р Т‘Р С•Р В±Р В°Р Р†Р В»Р ВµР Р…Р В° РЎвЂљР С‘Р С—Р С‘Р В·Р В°РЎвЂ Р С‘РЎРЏ
 */

declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) { session_start(); }
// Р СћР С•Р В»РЎРЉР С”Р С• Р В°Р Т‘Р СР С‘Р Р…Р С‘РЎРѓРЎвЂљРЎР‚Р В°РЎвЂљР С•РЎР‚ (id=1)
if ((int)($_SESSION['id'] ?? 0) !== 1) {
    die('Р РЋРЎвЂљРЎР‚Р В°Р Р…Р С‘РЎвЂ Р В° Р С‘РЎРѓРЎвЂЎР ВµРЎР‚Р С—Р В°Р В»Р В° РЎРѓР Р†Р С•РЎР‹ Р В°Р С”РЎвЂљРЎС“Р В°Р В»РЎРЉР Р…Р С•РЎРѓРЎвЂљРЎРЉ.');
}

// Р СџРЎР‚Р С•РЎРѓРЎвЂљР С•Р в„– Р С—Р В°РЎР‚Р С•Р В»РЎРЉ Р Т‘Р С•РЎРѓРЎвЂљРЎС“Р С—Р В° (РЎР‚Р ВµР С”Р С•Р СР ВµР Р…Р Т‘РЎС“Р ВµРЎвЂљРЎРѓРЎРЏ РЎС“Р В±РЎР‚Р В°РЎвЂљРЎРЉ Р С‘ Р С‘РЎРѓР С—Р С•Р В»РЎРЉР В·Р С•Р Р†Р В°РЎвЂљРЎРЉ Р С—РЎР‚Р В°Р Р†Р В° Р С–РЎР‚РЎС“Р С—Р С—)
const ACCESS_PASS = '/*-/*-/*-';

if (empty($_SESSION['sessionAttac'])) {
    $submitted = (string)filter_input(INPUT_POST, 'password', FILTER_DEFAULT) ?: '';
    if ($submitted === ACCESS_PASS) {
        $_SESSION['sessionAttac'] = true;
        die("<script>location.href=location.href;</script>");
    }

    echo '
    <p style="margin-top:100px; font-weight:bold; color:#000;" align="center">Р вЂ™Р Р†Р ВµР Т‘Р С‘РЎвЂљР Вµ Р С—Р В°РЎР‚Р С•Р В»РЎРЉ:</p>
    <table align="center">
        <form action="" method="POST">
            <tr><td>
                <input type="password" name="password" value=""
                       style="color:#000; font-size:15px; font-weight:bold; border:2px solid #000;">
                <input type="submit" value="OK"
                       style="color:#000; font-weight:bold; border:2px solid #000; font-size:15px;">
            </td></tr>
        </form>
    </table>';
    exit;
}

// РІвЂќР‚РІвЂќР‚ Р СџР С•Р Т‘Р С”Р В»РЎР‹РЎвЂЎР ВµР Р…Р С‘Р Вµ Р вЂР вЂќ РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚

require_once __DIR__ . '/include/function/config.php';
require_once __DIR__ . '/include/function/db3.php';
$db = db($config);
require_once __DIR__ . '/include/function/globfanction.php';

// РІвЂќР‚РІвЂќР‚ Р вЂєР С•Р С– Р С‘Р В·Р СР ВµР Р…Р ВµР Р…Р С‘Р в„– РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚

function logAttak(string $text): void
{
    $logFile = __DIR__ . '/log/attak_loges.txt';
    $line    = date('Y-m-d, H:i:s') . ' | ' . $text . "\r\n";
    file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
}

// РІвЂќР‚РІвЂќР‚ Р С›Р В±РЎР‚Р В°Р В±Р С•РЎвЂљР С”Р В° РЎвЂћР С•РЎР‚Р СРЎвЂ№ Р С‘Р В·Р СР ВµР Р…Р ВµР Р…Р С‘РЎРЏ Р С•Р С—Р С‘РЎРѓР В°Р Р…Р С‘РЎРЏ Р В°РЎвЂљР В°Р С”Р С‘ РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚

if (
    !empty($_POST['attaktittle']) &&
    !empty($_POST['names']) &&
    !empty($_POST['effect'])
) {
    $name  = obr_txt((string)$_POST['names']);
    $title = obr_txt((string)$_POST['attaktittle']);
    $eff   = obr_txt((string)$_POST['effect']);

    if ($name === '' || $title === '' || $eff === '') {
        $_SESSION['atc'] = "<span style='color:#000; font-weight:bold; border:2px solid #000; background:#ffcece;'>Р В§Р ВµР С–Р С•-РЎвЂљР С• Р Р…Р Вµ РЎвЂ¦Р Р†Р В°РЎвЂљР В°Р ВµРЎвЂљ!</span>";
        die("<script>location.href=location.href;</script>");
    }

    // Р ВРЎвЂ°Р ВµР С Р В°РЎвЂљР В°Р С”РЎС“ Р С—Р С• РЎвЂљР С•РЎвЂЎР Р…Р С•Р СРЎС“ РЎРѓР С•Р Р†Р С—Р В°Р Т‘Р ВµР Р…Р С‘РЎР‹
    $found = first('SELECT atac_id FROM attac_power WHERE atac_name="%s"', $name);
    $atId  = (int)($found['atac_id'] ?? 0);

    if (!$found) {
        // Р СџРЎР‚Р С•Р В±РЎС“Р ВµР С Р С—Р С•Р В»Р Р…Р С•РЎвЂљР ВµР С”РЎРѓРЎвЂљР С•Р Р†РЎвЂ№Р в„– Р С—Р С•Р С‘РЎРѓР С”
        $found2 = first('SELECT atac_id FROM attac_power WHERE MATCH(atac_name) AGAINST("%s")', $name);
        $atId   = (int)($found2['atac_id'] ?? 0);

        if (!$found2) {
            $_SESSION['atc'] = "<span style='color:#000; font-weight:bold; border:2px solid #000; background:#ffcece;'>Р С’РЎвЂљР В°Р С”Р В°: "
                . htmlspecialchars($name) . " Р Р…Р Вµ Р Р…Р В°Р в„–Р Т‘Р ВµР Р…Р В° Р Р† Р В±Р В°Р В·Р Вµ!</span>";
            die("<script>location.href=location.href;</script>");
        }
    }

    $ok = update('attac_power', ['atac_tittle' => $title, 'tittle_effect' => $eff], 'atac_id=' . $atId);

    if ($ok) {
        logAttak("Р Р€ Р В°РЎвЂљР В°Р С”Р С‘: {$name} Р С‘Р В·Р СР ВµР Р…Р ВµР Р…Р С• Р С•Р С—Р С‘РЎРѓР В°Р Р…Р С‘Р Вµ Р Р…Р В°: {$title}, РЎРЊРЎвЂћРЎвЂћР ВµР С”РЎвЂљ Р Р…Р В°: {$eff}");
        $_SESSION['atc'] = "<span style='color:green; font-weight:bold;'>"
            . "Р Р€ Р В°РЎвЂљР В°Р С”Р С‘: " . htmlspecialchars($name) . ", РЎС“РЎРѓР С—Р ВµРЎв‚¬Р Р…Р С• Р С‘Р В·Р СР ВµР Р…Р ВµР Р…Р С• Р С•Р С—Р С‘РЎРѓР В°Р Р…Р С‘Р Вµ Р С‘ РЎРЊРЎвЂћРЎвЂћР ВµР С”РЎвЂљ. "
            . "Р СџРЎР‚Р С•Р Р†Р ВµРЎР‚Р С”Р В°: <a href=\"javascript:void(0)\" "
            . "onclick=\"window.open('/game.php?go=atk&id={$atId}','atk','width=726,height=260,scrollbars=yes')\">"
            . "<img src='/img/other/inf.png'></a></span>";
    } else {
        $_SESSION['atc'] = "<span style='color:#000; font-weight:bold; border:2px solid #000; background:#ffcece;'>Р вЂ™Р С•Р В·Р Р…Р С‘Р С”Р В»Р В° Р С•РЎв‚¬Р С‘Р В±Р С”Р В°.</span>";
    }

    die("<script>location.href=location.href;</script>");
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Р вЂќР С•Р В±Р В°Р Р†Р В»Р ВµР Р…Р С‘Р Вµ Р В°РЎвЂљР В°Р С”</title>
    <style>
        body, html { margin:5px; color:#000; background:#696969; }
        input, textarea { background:#FFFFF0; padding:2px; font:14pt Tahoma; border:2px solid #000; color:#000; }
        select { border:none; font:14pt Tahoma; color:#000; }
        #txt2 { color:green; font:12pt Tahoma; font-weight:bold; text-align:center; }
    </style>
</head>
<body>

<?php if (!empty($_GET['attac'])): ?>

    <?php
    $attacks = select('SELECT atac_id, atac_name, atac_tittle, tittle_effect FROM attac_power');
    echo "<table cellspacing='2' border='1' cellpadding='4'>
            <tr>
                <td>ID</td>
                <td>Р СњР В°Р В·Р Р†Р В°Р Р…Р С‘Р Вµ</td>
                <td width='42%'>Р С›Р С—Р С‘РЎРѓР В°Р Р…Р С‘Р Вµ</td>
                <td width='42%'>Р В­РЎвЂћРЎвЂћР ВµР С”РЎвЂљ</td>
            </tr>";
    foreach ($attacks as $row) {
        printf(
            "<tr>
                <td>%d</td>
                <td><span style='color:#000; padding:2px; font-weight:bold; border:2px solid #000; background:#e8ffce;'>%s</span></td>
                <td><span style='color:#fff0f5; font-weight:bold;'>%s</span></td>
                <td><span style='color:#fff0f5; font-weight:bold;'>%s</span></td>
             </tr>",
            (int)$row['atac_id'],
            htmlspecialchars($row['atac_name']),
            htmlspecialchars($row['atac_tittle']),
            htmlspecialchars($row['tittle_effect'])
        );
    }
    echo '</table>';
    ?>

<?php else: ?>

    <center><h1>Р ВР В·Р СР ВµР Р…Р С‘РЎвЂљРЎРЉ Р С•Р С—Р С‘РЎРѓР В°Р Р…Р С‘Р Вµ Р В°РЎвЂљР В°Р С”Р С‘</h1></center>
    <center>
        <div style="background:#4F4F4F; width:70%;" align="center">

            <?php
            if (!empty($_SESSION['atc'])) {
                echo $_SESSION['atc'];
                unset($_SESSION['atc']);
            }
            ?>

            <form method="POST" action="">
                <table align="center" width="900">
                    <tr>
                        <td>
                            <div id="txt2">Р СњР В°Р В·Р Р†Р В°Р Р…Р С‘Р Вµ Р В°РЎвЂљР В°Р С”Р С‘:</div>
                            <input type="text" name="names" style="width:100%;">
                        </td>
                    </tr>
                    <tr>
                        <td align="center" colspan="2">
                            <div id="txt2">Р С™Р С•РЎР‚РЎР‚Р ВµР С”РЎвЂљР Р…Р С•Р Вµ Р С•Р С—Р С‘РЎРѓР В°Р Р…Р С‘Р Вµ:</div>
                            <textarea name="attaktittle" style="width:100%; height:85px;" maxlength="5000"></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" colspan="2">
                            <div id="txt2">Р В­РЎвЂћРЎвЂћР ВµР С”РЎвЂљ:</div>
                            <textarea name="effect" style="width:100%; height:85px;" maxlength="5000"></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" colspan="2"><br>
                            <input type="submit" name="Submit" value="Р СџРЎР‚Р С‘Р СР ВµР Р…Р С‘РЎвЂљРЎРЉ">
                            <input type="reset" value="Р РЋР В±РЎР‚Р С•РЎРѓ">
                        </td>
                    </tr>
                </table>
            </form>
        </div>
    </center>

<?php endif; ?>
</body>
</html>
