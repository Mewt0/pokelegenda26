<?php
declare(strict_types=1);
/**
 * ban.php РІР‚вЂќ Р СџРЎР‚Р С•Р Р†Р ВµРЎР‚Р С”Р В° IP-Р В±Р В°Р Р…Р В°
 * PHP 8.x: РЎС“Р В±РЎР‚Р В°Р Р…РЎвЂ№ РЎС“РЎРѓРЎвЂљР В°РЎР‚Р ВµР Р†РЎв‚¬Р С‘Р Вµ getenv(), Р Т‘Р С•Р В±Р В°Р Р†Р В»Р ВµР Р…Р В° РЎвЂљР С‘Р С—Р С‘Р В·Р В°РЎвЂ Р С‘РЎРЏ
 */

declare(strict_types=1);

if (!isset($_SESSION)) {
    if (session_status() === PHP_SESSION_NONE) { session_start(); }
}

require_once __DIR__ . '/include/function/config.php';
require_once __DIR__ . '/include/function/db3.php';
db($config);

$timeipban = time();
if (empty($_SESSION['ipBan'])) {
    $_SESSION['ipBan'] = 0;
}

// Р СџР С•Р В»РЎС“РЎвЂЎР В°Р ВµР С РЎР‚Р ВµР В°Р В»РЎРЉР Р…РЎвЂ№Р в„– IP РЎРѓ РЎС“РЎвЂЎРЎвЂРЎвЂљР С•Р С Р С—РЎР‚Р С•Р С”РЎРѓР С‘
function getRealIp(): string
{
    $headers = [
        'HTTP_X_FORWARDED_FOR',
        'HTTP_CLIENT_IP',
        'HTTP_X_REAL_IP',
        'REMOTE_ADDR',
    ];

    foreach ($headers as $header) {
        $ip = $_SERVER[$header] ?? '';
        if ($ip !== '' && $ip !== 'unknown') {
            // X-Forwarded-For Р СР С•Р В¶Р ВµРЎвЂљ РЎРѓР С•Р Т‘Р ВµРЎР‚Р В¶Р В°РЎвЂљРЎРЉ РЎРѓР С—Р С‘РЎРѓР С•Р С” IP РЎвЂЎР ВµРЎР‚Р ВµР В· Р В·Р В°Р С—РЎРЏРЎвЂљРЎС“РЎР‹ РІР‚вЂќ Р В±Р ВµРЎР‚РЎвЂР С Р С—Р ВµРЎР‚Р Р†РЎвЂ№Р в„–
            $ip = trim(explode(',', $ip)[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }

    return '0.0.0.0';
}

$ip = getRealIp();

$ipRes = first('SELECT ip FROM banip WHERE ip="%s"', $ip);
if (!empty($ipRes['ip'])) {
    echo '<center><b>Р вЂ™Р В°РЎв‚¬ IP Р В°Р Т‘РЎР‚Р ВµРЎРѓ Р В±РЎвЂ№Р В» Р В·Р В°Р В±Р В»Р С•Р С”Р С‘РЎР‚Р С•Р Р†Р В°Р Р…. '
        . 'Р вЂ™ Р В±Р В°Р Р… Р С—Р С• IP Р С—Р С•Р С—Р В°Р Т‘Р В°РЎР‹РЎвЂљ РЎвЂљР С•Р В»РЎРЉР С”Р С• РЎвЂљР Вµ Р С—Р С•Р В»РЎРЉР В·Р С•Р Р†Р В°РЎвЂљР ВµР В»Р С‘, Р С”Р С•РЎвЂљР С•РЎР‚РЎвЂ№Р Вµ Р СР Р…Р С•Р С–Р С•Р С”РЎР‚Р В°РЎвЂљР Р…Р С• Р Р…Р В°РЎР‚РЎС“РЎв‚¬Р В°Р В»Р С‘ Р С—РЎР‚Р В°Р Р†Р С‘Р В»Р В°.</b></center>';
    exit;
}
