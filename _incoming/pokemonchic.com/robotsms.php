<?php
declare(strict_types=1);
/**
 * robotsms.php РІР‚вЂќ Р С›Р В±РЎР‚Р В°Р В±Р С•РЎвЂљРЎвЂЎР С‘Р С” SMS-Р С—Р В»Р В°РЎвЂљР ВµР В¶Р ВµР в„– (Р Т‘Р С•Р Р…Р В°РЎвЂљ РЎвЂЎР ВµРЎР‚Р ВµР В· Р С”Р С•РЎР‚Р С•РЎвЂљР С”Р С‘Р Вµ Р Р…Р С•Р СР ВµРЎР‚Р В°)
 * PHP 8.x: РЎС“Р В±РЎР‚Р В°Р Р…РЎвЂ№ РЎС“РЎРѓРЎвЂљР В°РЎР‚Р ВµР Р†РЎв‚¬Р С‘Р Вµ Р С”Р С•Р Р…РЎРѓРЎвЂљРЎР‚РЎС“Р С”РЎвЂ Р С‘Р С‘, Р Т‘Р С•Р В±Р В°Р Р†Р В»Р ВµР Р…Р В° РЎвЂљР С‘Р С—Р С‘Р В·Р В°РЎвЂ Р С‘РЎРЏ Р С‘ Р Р†Р В°Р В»Р С‘Р Т‘Р В°РЎвЂ Р С‘РЎРЏ
 */

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Р СџРЎР‚Р С•Р Р†Р ВµРЎР‚РЎРЏР ВµР С Р С•Р В±РЎРЏР В·Р В°РЎвЂљР ВµР В»РЎРЉР Р…РЎвЂ№Р Вµ Р С—Р В°РЎР‚Р В°Р СР ВµРЎвЂљРЎР‚РЎвЂ№
$smsId      = (string)filter_input(INPUT_GET, 'sms_id',      FILTER_DEFAULT) ?: '';
$shortNum   = (string)filter_input(INPUT_GET, 'short_number', FILTER_DEFAULT) ?: '';
$msg        = (string)filter_input(INPUT_GET, 'msg',          FILTER_DEFAULT) ?: '';

if ($smsId === '' || $shortNum === '' || $msg === '') {
    smsReply('Vi vveli nevernoe soobshenie');
}

require_once __DIR__ . '/include/function/config.php';
require_once __DIR__ . '/include/function/db3.php';
$db = db($config);
require_once __DIR__ . '/include/function/globfanction.php';

// РІвЂќР‚РІвЂќР‚ Р С™Р С•Р Р…РЎвЂћР С‘Р С–РЎС“РЎР‚Р В°РЎвЂ Р С‘РЎРЏ РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚

const SMS_PREFIX = '79174';
const SMS_SECRET = '895371596';

// РІвЂќР‚РІвЂќР‚ Р вЂ™РЎРѓР С—Р С•Р СР С•Р С–Р В°РЎвЂљР ВµР В»РЎРЉР Р…РЎвЂ№Р Вµ РЎвЂћРЎС“Р Р…Р С”РЎвЂ Р С‘Р С‘ РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚

function logSms(string $text): void
{
    $line = date('Y-m-d, H:i:s') . ' | ' . $text . "\r\n";
    file_put_contents(__DIR__ . '/dat/sms.dat', $line, FILE_APPEND | LOCK_EX);
}

function smsReply(string $text): never
{
    echo "ok\n" . $text;
    exit;
}

function getItemCount(int $userId, int $itemId): string|false
{
    $row = first('SELECT count FROM items_users WHERE user_id=%d AND item_id=%d', $userId, $itemId);
    if (!empty($row['count']) && (int)$row['count'] > 0) {
        return formatnum((int)$row['count']);
    }
    return false;
}

// РІвЂќР‚РІвЂќР‚ Р вЂ™РЎвЂ¦Р С•Р Т‘Р Р…РЎвЂ№Р Вµ Р Т‘Р В°Р Р…Р Р…РЎвЂ№Р Вµ РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚

$from      = (string)filter_input(INPUT_GET, 'from',        FILTER_DEFAULT) ?: '';
$date      = (string)filter_input(INPUT_GET, 'date',        FILTER_DEFAULT) ?: '';
$sign      = (string)filter_input(INPUT_GET, 'sign',        FILTER_DEFAULT) ?: '';
$country   = (int)filter_input(INPUT_GET,   'country',      FILTER_VALIDATE_INT);
$cost      = (float)filter_input(INPUT_GET, 'cost',         FILTER_VALIDATE_FLOAT);
$number    = (string)$shortNum;
$payStatus = (string)filter_input(INPUT_GET, 'pay_status',  FILTER_DEFAULT) ?: '';
$mayPay    = (string)filter_input(INPUT_GET, 'may_pay',     FILTER_DEFAULT) ?: '';
$smsStatus = (string)filter_input(INPUT_GET, 'sms_status',  FILTER_DEFAULT) ?: '';

// РІвЂќР‚РІвЂќР‚ Р СћР В°Р В±Р В»Р С‘РЎвЂ Р В° РЎвЂљР В°РЎР‚Р С‘РЎвЂћР С•Р Р†: [short_number, country] => Р В°Р В»Р СР В°Р В·Р С•Р Р† РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚

$tariffs = [
    // Р В Р С•РЎРѓРЎРѓР С‘РЎРЏ
    ['num' => '7201', 'country' => 1, 'diamonds' => 2,  'extra' => []],
    ['num' => '7202', 'country' => 1, 'diamonds' => 5,  'extra' => []],
    ['num' => '3352', 'country' => 1, 'diamonds' => 8,  'extra' => []],
    ['num' => '8510', 'country' => 1, 'diamonds' => 11, 'extra' => []],
    // Р Р€Р С”РЎР‚Р В°Р С‘Р Р…Р В°
    ['num' => '2855', 'country' => 3, 'diamonds' => 5,  'extra' => ['pay_status' => 'ok', 'may_pay' => '0']],
    ['num' => '3855', 'country' => 3, 'diamonds' => 12, 'extra' => ['pay_status' => 'ok', 'may_pay' => '0']],
    // Р С™Р В°Р В·Р В°РЎвЂ¦РЎРѓРЎвЂљР В°Р Р…
    ['num' => '7122', 'country' => 8, 'diamonds' => 7,  'extra' => []],
    ['num' => '7132', 'country' => 8, 'diamonds' => 4,  'extra' => []],
    // Р вЂР ВµР В»Р В°РЎР‚РЎС“РЎРѓРЎРЉ
    ['num' => '3339', 'country' => 12, 'diamonds' => 7, 'extra' => []],
    ['num' => '3336', 'country' => 12, 'diamonds' => 4, 'extra' => []],
    // Р С’РЎР‚Р СР ВµР Р…Р С‘РЎРЏ
    ['num' => '5009', 'country' => 13, 'diamonds' => 2, 'extra' => []],
    ['num' => '7122', 'country' => 13, 'diamonds' => 3, 'extra' => []],
];

$donatorGet = 0;

foreach ($tariffs as $t) {
    if ($t['num'] === $number && $t['country'] === $country && $cost > 0) {
        // Р СџРЎР‚Р С•Р Р†Р ВµРЎР‚РЎРЏР ВµР С Р Т‘Р С•Р С—Р С•Р В»Р Р…Р С‘РЎвЂљР ВµР В»РЎРЉР Р…РЎвЂ№Р Вµ РЎС“РЎРѓР В»Р С•Р Р†Р С‘РЎРЏ (pay_status, may_pay)
        $extraOk = true;
        foreach ($t['extra'] as $key => $val) {
            if (($key === 'pay_status' && $payStatus !== $val) ||
                ($key === 'may_pay'    && $mayPay    !== $val)) {
                $extraOk = false;
                break;
            }
        }
        if ($extraOk) {
            $donatorGet = $t['diamonds'];
            break;
        }
    }
}

// РІвЂќР‚РІвЂќР‚ Р вЂўРЎРѓР В»Р С‘ РЎвЂљР В°РЎР‚Р С‘РЎвЂћ Р Р…Р Вµ Р Р…Р В°Р в„–Р Т‘Р ВµР Р… РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚

if ($donatorGet === 0) {
    // Р Р€Р С”РЎР‚Р В°Р С‘Р Р…Р В°: MT-РЎРѓРЎвЂљР В°РЎвЂљРЎС“РЎРѓ РІР‚вЂќ Р С—Р С•Р Т‘РЎвЂљР Р†Р ВµРЎР‚Р В¶Р Т‘Р В°РЎР‹РЎвЂ°Р ВµР Вµ SMS
    if ($country === 3 && $smsStatus === 'mt') {
        $logText = "|{$date}|{$smsId}| Р РЋРЎвЂљРЎР‚Р В°Р Р…Р В°:{$country}| Р РЋР С•Р С•Р В±РЎвЂ°Р ВµР Р…Р С‘Р Вµ:{$msg}|{$from}|{$number}|{$cost}|";
        logSms($logText);
        smsReply('V blizhajshee vremja vash schet budet oplachen.');
    }
    die('Hacking attempt!');
}

// РІвЂќР‚РІвЂќР‚ Р СџРЎР‚Р С•Р Р†Р ВµРЎР‚Р С”Р В° Р С—Р С•Р Т‘Р С—Р С‘РЎРѓР С‘ РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚

if ($sign !== md5($smsId . SMS_SECRET)) {
    die('Hacking attempt!');
}

// РІвЂќР‚РІвЂќР‚ Р ВР В·Р Р†Р В»Р ВµРЎвЂЎР ВµР Р…Р С‘Р Вµ ID Р С—Р С•Р В»РЎРЉР В·Р С•Р Р†Р В°РЎвЂљР ВµР В»РЎРЏ Р С‘Р В· РЎРѓР С•Р С•Р В±РЎвЂ°Р ВµР Р…Р С‘РЎРЏ РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚

$pos = strpos($msg, SMS_PREFIX);
if ($pos === false) {
    smsReply('Vi vveli nevernoe soobshenie');
}

$userId = (int)obr_chis(substr($msg, $pos + strlen(SMS_PREFIX)));

if ($userId <= 0) {
    smsReply('Vi vveli nevernoe soobshenie');
}

// РІвЂќР‚РІвЂќР‚ Р СњР В°РЎвЂЎР С‘РЎРѓР В»Р ВµР Р…Р С‘Р Вµ Р В°Р В»Р СР В°Р В·Р С•Р Р† РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚

$don = first('SELECT vsegoalmaz, id FROM usersunictable WHERE id=%d', $userId);

if (!empty($don) && $donatorGet > 0) {
    $loginRow = first('SELECT login FROM users WHERE id=%d', $userId);
    $login    = (string)($loginRow['login'] ?? '');

    plus_item($donatorGet, 2, $userId);
    query('UPDATE usersunictable SET vsegoalmaz=vsegoalmaz+%d WHERE id=%d', $donatorGet, $userId);

    $logText = "|{$date}|{$smsId}|{$userId}|{$login}|{$from}|{$msg}|{$number}|{$cost}|";
    logSms($logText);

    $total = getItemCount($userId, 2) ?: '0';
    smsReply(
        "Vy udachno oplatili: {$donatorGet} almaz(a). "
        . "Dlja pol'zovatelja: {$login}. "
        . "Obshee kolichestvo almazov: {$total}. "
        . "Na resurse: http://league-of-pokemons.ru"
    );
} else {
    smsReply('Vi vveli nevernoe soobshenie');
}
