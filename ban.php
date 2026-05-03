<?php
declare(strict_types=1);

require_once __DIR__ . '/include/function/config.php';
require_once __DIR__ . '/include/function/db3.php';

app_start_session();
db($config);

function ban_current_ip(): string
{
    return app_client_ip();
}

$now = time();
$_SESSION['ipBan'] = isset($_SESSION['ipBan']) ? (int) $_SESSION['ipBan'] : 0;

if ($_SESSION['ipBan'] > $now) {
    http_response_code(403);
    echo '<center><b>Доступ к игре временно ограничен.</b></center>';
    exit;
}

$ip = ban_current_ip();
$ipRes = first('SELECT ip FROM banip WHERE ip="%s"', $ip);

if (!empty($ipRes['ip'])) {
    http_response_code(403);
    echo '<center><b>Ваш IP адрес был заблокирован. В базу IP попала хотя бы одна учетная запись, которая нарушала правила проекта или занималась мошенничеством.</b></center>';
    exit;
}