<?php
/**
 * fix_tittle_effects.php  (положи в /include/)
 * PHP 5.4, cp1251
 * Чинит колонку attac_power.tittle_effect (битые UTF8 байты) -> нормальная cp1251.
 * Ничего не переименовывает. Делает резервную копию. Пишет лог.
 */

header('Content-Type: text/plain; charset=windows-1251');

// Подключение из этой же папки /include/
require_once __DIR__ . '/function/config.php';
require_once __DIR__ . '/function/db3.php';
require_once __DIR__ . '/function/globfanction.php';

function try_iconv($from, $to, $s) {
    $r = @iconv($from, $to . '//IGNORE', $s);
    return ($r === false) ? '' : $r;
}

// Больше кириллицы и меньше "Р/С" = лучше
function ru_score($s) {
    $u = @iconv('CP1251','UTF-8//IGNORE',$s);
    $ru = preg_match_all('/[А-Яа-яЁё]/u', $u, $m);
    $pen = substr_count($s, 'Р') + substr_count($s, 'С');
    return $ru * 5 - $pen;
}

function fix_cp1251($s) {
    $v1 = try_iconv('UTF-8','CP1251',$s);                   // utf8 -> cp1251
    $to_u = try_iconv('CP1251','UTF-8',$s);                 // cp1251 -> utf8
    $v2 = $to_u ? try_iconv('UTF-8','CP1251',$to_u) : '';   // и обратно

    $best = $s; $bestScore = ru_score($s);
    foreach (array($v1, $v2) as $c) {
        if ($c !== '' && $c !== $s) {
            $sc = ru_score($c);
            if ($sc > $bestScore) { $best = $c; $bestScore = $sc; }
        }
    }
    return $best;
}

// 0) Бэкап
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
