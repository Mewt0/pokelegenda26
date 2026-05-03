<?php
declare(strict_types=1);

/**
 * fix_tittle_effects.include.php РІР‚вЂќ PHP 8.0 / UTF-8.
 * Р В§Р С‘Р Р…Р С‘РЎвЂљ Р С”Р С•Р В»Р С•Р Р…Р С”РЎС“ attac_power.tittle_effect Р С‘ Р С—Р С‘РЎв‚¬Р ВµРЎвЂљ Р В»Р С•Р С–.
 * Р РЋРЎвЂљР В°РЎР‚РЎвЂ№Р Вµ Р С‘Р СР ВµР Р…Р В° РЎвЂћРЎС“Р Р…Р С”РЎвЂ Р С‘Р в„– РЎРѓР С•РЎвЂ¦РЎР‚Р В°Р Р…Р ВµР Р…РЎвЂ№: try_iconv(), ru_score(), fix_cp1251().
 */

header('Content-Type: text/plain; charset=UTF-8');

require_once __DIR__ . '/function/config.php';
require_once __DIR__ . '/function/db3.php';
require_once __DIR__ . '/function/globfanction.php';

function try_iconv(string $from, string $to, string $s): string
{
    $r = @iconv($from, $to . '//IGNORE', $s);
    return ($r === false) ? '' : $r;
}

function ru_score(string $s): int
{
    $u = @iconv('CP1251', 'UTF-8//IGNORE', $s);
    if ($u === false) {
        $u = $s;
    }
    $ru = preg_match_all('/[Р С’-Р Р‡Р В°-РЎРЏР РѓРЎвЂ]/u', $u, $m);
    $pen = substr_count($s, 'Р“С’') + substr_count($s, 'Р“вЂ');
    return ((int)$ru) * 5 - $pen;
}

function fix_cp1251(string $s): string
{
    $v1 = try_iconv('UTF-8', 'CP1251', $s);
    $to_u = try_iconv('CP1251', 'UTF-8', $s);
    $v2 = $to_u !== '' ? try_iconv('UTF-8', 'CP1251', $to_u) : '';

    $best = $s;
    $bestScore = ru_score($s);
    foreach ([$v1, $v2] as $c) {
        if ($c !== '' && $c !== $s) {
            $sc = ru_score($c);
            if ($sc > $bestScore) {
                $best = $c;
                $bestScore = $sc;
            }
        }
    }
    return $best;
}

// Р СџР С•Р Т‘Р Т‘Р ВµРЎР‚Р В¶Р С”Р В° Р Т‘Р Р†РЎС“РЎвЂ¦ Р Р†Р В°РЎР‚Р С‘Р В°Р Р…РЎвЂљР С•Р Р† РЎРЏР Т‘РЎР‚Р В°: РЎРѓРЎвЂљР В°РЎР‚РЎвЂ№Р Вµ query()/first() Р С‘Р В· db3.php Р С‘Р В»Р С‘ mysqli $link.
function fx_query(string $sql)
{
    if (function_exists('query')) {
        return query($sql);
    }
    if (isset($GLOBALS['link']) && $GLOBALS['link'] instanceof mysqli) {
        return mysqli_query($GLOBALS['link'], $sql);
    }
    throw new RuntimeException('Р СњР ВµРЎвЂљ Р Т‘Р С•РЎРѓРЎвЂљРЎС“Р С—Р Р…Р С•Р С–Р С• DB-РЎРѓР В»Р С•РЎРЏ: query() Р С‘Р В»Р С‘ mysqli $link');
}

function fx_escape(string $value): string
{
    if (function_exists('obr_txt')) {
        return obr_txt($value);
    }
    if (isset($GLOBALS['link']) && $GLOBALS['link'] instanceof mysqli) {
        return mysqli_real_escape_string($GLOBALS['link'], $value);
    }
    return addslashes($value);
}

fx_query('CREATE TABLE IF NOT EXISTS attac_power_backup_fx LIKE attac_power');
fx_query('INSERT IGNORE INTO attac_power_backup_fx SELECT * FROM attac_power');

$res = fx_query('SELECT atac_id, tittle_effect FROM attac_power');
if (!$res) {
    echo "SQL error
";
    exit;
}

$logPath = __DIR__ . '/fix_tittle_effects.log';
$updated = 0;
$checked = 0;

while ($row = ($res instanceof mysqli_result ? mysqli_fetch_assoc($res) : (is_object($res) && method_exists($res, 'fetch_assoc') ? $res->fetch_assoc() : null))) {
    $checked++;
    $id = (int)($row['atac_id'] ?? 0);
    $raw = (string)($row['tittle_effect'] ?? '');
    $fixed = fix_cp1251($raw);

    if ($fixed !== '' && $fixed !== $raw) {
        $q = sprintf("UPDATE attac_power SET tittle_effect='%s' WHERE atac_id=%d", fx_escape($fixed), $id);
        try {
            fx_query($q);
            $updated++;
            $msg = "OK   id={$id}
";
        } catch (Throwable $e) {
            $msg = "FAIL id={$id} error=" . $e->getMessage() . "
";
        }
        file_put_contents($logPath, $msg, FILE_APPEND | LOCK_EX);
        echo $msg;
    }
}

if ($res instanceof mysqli_result) {
    mysqli_free_result($res);
}

echo "Checked: {$checked}, Updated: {$updated}
";
echo "Log: {$logPath}
";
