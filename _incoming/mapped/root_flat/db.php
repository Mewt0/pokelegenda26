<?php
declare(strict_types=1);

/**
 * db.php РІР‚вЂќ PHP 8.0 / mysqli connection layer.
 * Р РЋРЎвЂљР В°РЎР‚РЎвЂ№Р Вµ Р С‘Р СР ВµР Р…Р В° Р С—Р ВµРЎР‚Р ВµР СР ВµР Р…Р Р…РЎвЂ№РЎвЂ¦ РЎРѓР С•РЎвЂ¦РЎР‚Р В°Р Р…Р ВµР Р…РЎвЂ№: $host, $user, $pass, $db, $link.
 */

$host = 'localhost';
$user = 'fh7904el_base';
$pass = '^&@^#A?]';
$db   = 'fh7904el_base';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $link = mysqli_connect($host, $user, $pass, $db);
    mysqli_set_charset($link, 'utf8mb4');
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    error_log('[DB] Connection error: ' . $e->getMessage());
    exit('Р С›РЎв‚¬Р С‘Р В±Р С”Р В° Р С—Р С•Р Т‘Р С”Р В»РЎР‹РЎвЂЎР ВµР Р…Р С‘РЎРЏ Р С” Р В±Р В°Р В·Р Вµ Р Т‘Р В°Р Р…Р Р…РЎвЂ№РЎвЂ¦');
}

// Р СљР С‘Р Р…Р С‘Р СР В°Р В»РЎРЉР Р…РЎвЂ№Р Вµ РЎРѓР С•Р Р†РЎР‚Р ВµР СР ВµР Р…Р Р…РЎвЂ№Р Вµ helper-РЎвЂћРЎС“Р Р…Р С”РЎвЂ Р С‘Р С‘ Р В±Р ВµР В· mysql_*.
if (!function_exists('db_escape')) {
    function db_escape(string $value): string
    {
        return mysqli_real_escape_string($GLOBALS['link'], $value);
    }
}

if (!function_exists('db_query')) {
    function db_query(string $sql): mysqli_result|bool
    {
        return mysqli_query($GLOBALS['link'], $sql);
    }
}

if (!function_exists('db_first')) {
    function db_first(string $sql): ?array
    {
        $result = db_query($sql);
        if ($result instanceof mysqli_result) {
            $row = mysqli_fetch_assoc($result);
            mysqli_free_result($result);
            return $row ?: null;
        }
        return null;
    }
}
