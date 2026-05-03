<?php
/**
 * fix_tittle_effects.php  (Р С—Р С•Р В»Р С•Р В¶Р С‘ Р Р† /include/)
 * PHP 5.4, cp1251
 * Р В§Р С‘Р Р…Р С‘РЎвЂљ Р С”Р С•Р В»Р С•Р Р…Р С”РЎС“ attac_power.tittle_effect (Р В±Р С‘РЎвЂљРЎвЂ№Р Вµ UTF8 Р В±Р В°Р в„–РЎвЂљРЎвЂ№) -> Р Р…Р С•РЎР‚Р СР В°Р В»РЎРЉР Р…Р В°РЎРЏ cp1251.
 * Р СњР С‘РЎвЂЎР ВµР С–Р С• Р Р…Р Вµ Р С—Р ВµРЎР‚Р ВµР С‘Р СР ВµР Р…Р С•Р Р†РЎвЂ№Р Р†Р В°Р ВµРЎвЂљ. Р вЂќР ВµР В»Р В°Р ВµРЎвЂљ РЎР‚Р ВµР В·Р ВµРЎР‚Р Р†Р Р…РЎС“РЎР‹ Р С”Р С•Р С—Р С‘РЎР‹. Р СџР С‘РЎв‚¬Р ВµРЎвЂљ Р В»Р С•Р С–.
 */

header('Content-Type: text/plain; charset=windows-1251');

// Р СџР С•Р Т‘Р С”Р В»РЎР‹РЎвЂЎР ВµР Р…Р С‘Р Вµ Р С‘Р В· РЎРЊРЎвЂљР С•Р в„– Р В¶Р Вµ Р С—Р В°Р С—Р С”Р С‘ /include/
require_once __DIR__ . '/function/config.php';
require_once __DIR__ . '/function/db3.php';
require_once __DIR__ . '/function/globfanction.php';

function try_iconv($from, $to, $s) {
    $r = @iconv($from, $to . '//IGNORE', $s);
    return ($r === false) ? '' : $r;
}

// Р вЂР С•Р В»РЎРЉРЎв‚¬Р Вµ Р С”Р С‘РЎР‚Р С‘Р В»Р В»Р С‘РЎвЂ РЎвЂ№ Р С‘ Р СР ВµР Р…РЎРЉРЎв‚¬Р Вµ "Р В /Р РЋ" = Р В»РЎС“РЎвЂЎРЎв‚¬Р Вµ
function ru_score($s) {
    $u = @iconv('CP1251','UTF-8//IGNORE',$s);
    $ru = preg_match_all('/[Р С’-Р Р‡Р В°-РЎРЏР РѓРЎвЂ]/u', $u, $m);
    $pen = substr_count($s, 'Р В ') + substr_count($s, 'Р РЋ');
    return $ru * 5 - $pen;
}

function fix_cp1251($s) {
    $v1 = try_iconv('UTF-8','CP1251',$s);                   // utf8 -> cp1251
    $to_u = try_iconv('CP1251','UTF-8',$s);                 // cp1251 -> utf8
    $v2 = $to_u ? try_iconv('UTF-8','CP1251',$to_u) : '';   // Р С‘ Р С•Р В±РЎР‚Р В°РЎвЂљР Р…Р С•

    $best = $s; $bestScore = ru_score($s);
    foreach (array($v1, $v2) as $c) {
        if ($c !== '' && $c !== $s) {
            $sc = ru_score($c);
            if ($sc > $bestScore) { $best = $c; $bestScore = $sc; }
        }
    }
    return $best;
}

// 0) Р вЂРЎРЊР С”Р В°Р С—
mysql_query("CREATE TABLE IF NOT EXISTS attac_power_backup_fx LIKE attac_power");
mysql_query("INSERT IGNORE INTO attac_power_backup_fx SELECT * FROM attac_power");

$res = mysql_query("SELECT atac_id, tittle_effect FROM attac_power");
if (!$res) { echo "SQL error: ".mysql_error()."\n"; exit; }

$log = fopen(__DIR__ . '/fix_tittle_effects.log', 'a');
$updated = 0; $checked = 0;

while ($row = mysql_fetch_assoc($res)) {
    $checked++;
    $id = (int)$row['atac_id'];
    $raw = $row['tittle_effect'];
    $fixed = fix_cp1251($raw);

    if ($fixed !== '' && $fixed !== $raw) {
        $q = sprintf("UPDATE attac_power SET tittle_effect='%s' WHERE atac_id=%d",
            mysql_real_escape_string($fixed));
        if (!mysql_query($q)) {
            $msg = "FAIL id={$id} mysql_error=".mysql_error()."\n";
            fwrite($log, $msg); echo $msg;
        } else {
            $updated++;
            $msg = "OK   id={$id}\n";
            fwrite($log, $msg); echo $msg;
        }
    }
}

fclose($log);
echo "Checked: {$checked}, Updated: {$updated}\n";
echo "Log: ".__DIR__ . "/fix_tittle_effects.log\n";
?>
