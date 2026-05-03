<?php
declare(strict_types=1);
/**
 * mailTo.php РІР‚вЂќ Р СџР С•Р В»РЎС“РЎвЂЎР ВµР Р…Р С‘Р Вµ Р С—Р С•РЎвЂЎРЎвЂљР С•Р Р†Р С•Р С–Р С• Р С—Р С•Р Т‘Р В°РЎР‚Р С”Р В° Р С—Р С• РЎРѓРЎРѓРЎвЂ№Р В»Р С”Р Вµ
 * PHP 8.x: РЎС“Р В±РЎР‚Р В°Р Р…РЎвЂ№ mysql_escape_string, РЎвЂљР С‘Р С—Р С‘Р В·Р В°РЎвЂ Р С‘РЎРЏ, die РІвЂ вЂ™ exit
 */

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/error.php';
require_once __DIR__ . '/include/function/config.php';
require_once __DIR__ . '/include/function/db3.php';
require_once __DIR__ . '/include/class/index.class.php';
$db = db($config);
require_once __DIR__ . '/include/function/globfanction.php';

// Р Р€Р Т‘Р В°Р В»РЎРЏР ВµР С Р С—РЎР‚Р С•РЎРѓРЎР‚Р С•РЎвЂЎР ВµР Р…Р Р…РЎвЂ№Р Вµ РЎРѓР С•Р В±РЎвЂ№РЎвЂљР С‘РЎРЏ
delete('eventusers', 'time<=' . time());

$code = (string)filter_input(INPUT_GET, 'code', FILTER_DEFAULT) ?: '';
$user = (int)filter_input(INPUT_GET, 'user', FILTER_VALIDATE_INT) ?: 0;

if ($code !== '' && $user > 0) {
    $code = obr_txt($code);

    $event = first(
        'SELECT id, items, users FROM eventusers WHERE code="%s" AND users=%d',
        $code,
        $user
    );

    if (!empty($event['id'])) {
        plus_item(1, (int)$event['items'], (int)$event['users']);
        delete('eventusers', 'code="' . $code . '" AND users=' . $user);
        echo "<script>alert('Р СџР С•Р Т‘Р В°РЎР‚Р С•Р С” РЎС“РЎРѓР С—Р ВµРЎв‚¬Р Р…Р С• Р С—Р С•Р В»РЎС“РЎвЂЎР ВµР Р…! Р С›Р Р… Р В¶Р Т‘РЎвЂРЎвЂљ Р вЂ™Р В°РЎРѓ Р Р† Р С‘Р Р…Р Р†Р ВµР Р…РЎвЂљР В°РЎР‚Р Вµ!'); window.close();</script>";
    } else {
        http_response_code(404);
        echo 'Р РЋРЎРѓРЎвЂ№Р В»Р С”Р В° Р Р…Р ВµР Т‘Р ВµР в„–РЎРѓРЎвЂљР Р†Р С‘РЎвЂљР ВµР В»РЎРЉР Р…Р В° Р С‘Р В»Р С‘ РЎС“Р В¶Р Вµ Р В±РЎвЂ№Р В»Р В° Р С‘РЎРѓР С—Р С•Р В»РЎРЉР В·Р С•Р Р†Р В°Р Р…Р В°.';
    }
} else {
    http_response_code(400);
    $expires = time() + 60 * 60 * 24 * 7;
    echo 'Р С›РЎв‚¬Р С‘Р В±Р С”Р В°: Р Р…Р ВµР Р†Р ВµРЎР‚Р Р…РЎвЂ№Р Вµ Р С—Р В°РЎР‚Р В°Р СР ВµРЎвЂљРЎР‚РЎвЂ№. Р РЋРЎР‚Р С•Р С” Р Т‘Р ВµР в„–РЎРѓРЎвЂљР Р†Р С‘РЎРЏ РЎРѓРЎРѓРЎвЂ№Р В»Р С•Р С”: ' . date('Y-m-d H:i:s', $expires);
}
