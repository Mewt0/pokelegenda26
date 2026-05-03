<?php
declare(strict_types=1);
/**
 * pInf.php РІР‚вЂќ Р С’Р Т‘Р СР С‘Р Р…Р С‘РЎРѓРЎвЂљРЎР‚Р В°РЎвЂљР С‘Р Р†Р Р…РЎвЂ№Р в„– Р С‘Р Р…РЎРѓРЎвЂљРЎР‚РЎС“Р СР ВµР Р…РЎвЂљ: Р С—Р С•Р С‘РЎРѓР С” Р СРЎС“Р В»РЎРЉРЎвЂљР В°Р С”Р С”Р В°РЎС“Р Р…РЎвЂљР С•Р Р† Р С—Р С• IP
 * PHP 8.x: РЎС“Р В±РЎР‚Р В°Р Р…РЎвЂ№ mysql_*, РЎвЂљР С‘Р С—Р С‘Р В·Р В°РЎвЂ Р С‘РЎРЏ, Р В±Р ВµР В·Р С•Р С—Р В°РЎРѓР Р…РЎвЂ№Р в„– Р Р†РЎвЂ№Р Р†Р С•Р Т‘
 *
 * Р вЂ™Р ВµРЎРѓРЎРЉ Р В·Р В°Р С”Р С•Р СР СР ВµР Р…РЎвЂљР С‘РЎР‚Р С•Р Р†Р В°Р Р…Р Р…РЎвЂ№Р в„– Р С”Р С•Р Т‘ РЎС“Р Т‘Р В°Р В»РЎвЂР Р… РІР‚вЂќ Р С•Р Р… Р В±РЎвЂ№Р В» Р С•РЎвЂљР В»Р В°Р Т‘Р С•РЎвЂЎР Р…РЎвЂ№Р С Р С‘ Р Р…Р Вµ Р С‘РЎРѓР С—Р С•Р В»РЎРЉР В·РЎС“Р ВµРЎвЂљРЎРѓРЎРЏ.
 */

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Р СћР С•Р В»РЎРЉР С”Р С• Р В°Р Т‘Р СР С‘Р Р…Р С‘РЎРѓРЎвЂљРЎР‚Р В°РЎвЂљР С•РЎР‚
if ((int)($_SESSION['id'] ?? 0) !== 1) {
    http_response_code(403);
    exit('Р вЂќР С•РЎРѓРЎвЂљРЎС“Р С— Р В·Р В°Р С—РЎР‚Р ВµРЎвЂ°РЎвЂР Р….');
}

require_once __DIR__ . '/include/function/config.php';
require_once __DIR__ . '/include/function/db3.php';
$db = db($config);

// РІвЂќР‚РІвЂќР‚ Р СџР С•Р С‘РЎРѓР С” Р С—Р С•Р В»РЎРЉР В·Р С•Р Р†Р В°РЎвЂљР ВµР В»Р ВµР в„– РЎРѓ Р С•Р Т‘Р С‘Р Р…Р В°Р С”Р С•Р Р†РЎвЂ№Р С IP (Р СРЎС“Р В»РЎРЉРЎвЂљРЎвЂ№) РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚

$matches = select('
    SELECT
        u1.login AS u1_login,
        u1.id    AS u1_id,
        u2.id    AS u2_id,
        u2.login AS u2_login
    FROM users u1
    INNER JOIN users u2
        ON  u1.ip = u2.ip
        AND u1.id < u2.id
    WHERE u1.ip > 0
      AND u2.ip > 0
      AND (u2.groups <> 7 OR u1.groups <> 7)
    ORDER BY u1.ip DESC
    LIMIT 40
');

if (empty($matches)) {
    echo '<p>Р СљРЎС“Р В»РЎРЉРЎвЂљР В°Р С”Р С”Р В°РЎС“Р Р…РЎвЂљРЎвЂ№ Р Р…Р Вµ Р С•Р В±Р Р…Р В°РЎР‚РЎС“Р В¶Р ВµР Р…РЎвЂ№.</p>';
    exit;
}

foreach ($matches as $row) {
    $u1Id    = (int)$row['u1_id'];
    $u2Id    = (int)$row['u2_id'];
    $u1Login = htmlspecialchars($row['u1_login'], ENT_QUOTES, 'UTF-8');
    $u2Login = htmlspecialchars($row['u2_login'], ENT_QUOTES, 'UTF-8');

    printf('
        <table width="600" bgcolor="#363636" style="border-radius:10px; margin-bottom:10px;">
            <tr><td style="color:#ccc; padding:8px;">
                <b>1) Р СџР С•Р В»РЎРЉР В·Р С•Р Р†Р В°РЎвЂљР ВµР В»РЎРЉ: <a href="page.php?id=%d" style="color:#8B6914;">%s</a>
                   РІР‚вЂќ Р Р†Р С•Р В·Р СР С•Р В¶Р Р…РЎвЂ№Р в„– Р СРЎС“Р В»РЎРЉРЎвЂљ: <a href="page.php?id=%d" target="_blank" style="color:Cyan;">%s</a></b><br>
                <b>2) Р СџР С•Р В»РЎРЉР В·Р С•Р Р†Р В°РЎвЂљР ВµР В»РЎРЉ: <a href="page.php?id=%d" style="color:#8B6914;">%s</a>
                   РІР‚вЂќ Р Р†Р С•Р В·Р СР С•Р В¶Р Р…РЎвЂ№Р в„– Р СРЎС“Р В»РЎРЉРЎвЂљ: <a href="page.php?id=%d" target="_blank" style="color:Cyan;">%s</a></b>
            </td></tr>
        </table>
        <hr>
    ',
        $u1Id, $u1Login, $u2Id, $u2Login,
        $u2Id, $u2Login, $u1Id, $u1Login
    );
}
