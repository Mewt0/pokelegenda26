<?php
declare(strict_types=1);

/**
 * Security smoke: legacy admin must stay unreachable and security headers must
 * be present. Read-only: the only POST targets a blocked URL with a zero grant.
 *
 * Usage: php tools/security_xss_smoke.php [--json]
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit(2);
}

define('APP_ROOT', dirname(__DIR__));
$base = rtrim((string) (getenv('SMOKE_BASE_URL') ?: 'http://pokemonchic.com'), '/');
$results = [];
$failed = 0;

function smokeRequest(string $url, string $method = 'GET', string $body = ''): array
{
    $options = [
        'http' => [
            'method' => $method,
            'ignore_errors' => true,
            'timeout' => 15,
            'header' => "User-Agent: PokemonChic security smoke\r\nAccept: text/html,*/*\r\n",
        ],
    ];
    if ($method === 'POST') {
        $options['http']['header'] .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $options['http']['content'] = $body;
    }
    $response = @file_get_contents($url, false, stream_context_create($options));
    $headers = $http_response_header ?? [];
    $status = 0;
    foreach ($headers as $line) {
        if (preg_match('#^HTTP/\S+\s+(\d{3})#', $line, $m)) {
            $status = (int) $m[1];
            break;
        }
    }
    $headerMap = [];
    foreach ($headers as $line) {
        $pos = strpos($line, ':');
        if ($pos !== false) {
            $headerMap[strtolower(trim(substr($line, 0, $pos)))] = trim(substr($line, $pos + 1));
        }
    }
    return ['status' => $status, 'headers' => $headerMap, 'body' => (string) $response];
}

function page(array $page, int $status, string $name): array
{
    return ['name' => $name, 'ok' => $page['status'] === $status, 'status' => $page['status']];
}

$blocked = [
    'admin/gitem.php',
    'admin/alm.php',
    'admin/bb_news_admin.php',
    'include/data.world.php',
    'storage/legacy_archive/admin/gitem.php',
];
foreach ($blocked as $path) {
    $page = smokeRequest($base . '/' . $path);
    $results[] = [
        'name' => 'blocked GET /' . $path,
        'ok' => in_array($page['status'], [403, 404], true),
        'status' => $page['status'],
    ];
}

$post = smokeRequest($base . '/admin/gitem.php', 'POST', 'user_id=1&item_id=1&count=0');
$results[] = [
    'name' => 'blocked POST /admin/gitem.php',
    'ok' => in_array($post['status'], [403, 404], true),
    'status' => $post['status'],
];

$home = smokeRequest($base . '/');
$headers = $home['headers'];
$csp = (string) ($headers['content-security-policy'] ?? '');
$results[] = ['name' => 'GET / returns 200', 'ok' => $home['status'] === 200, 'status' => $home['status']];
$results[] = ['name' => 'CSP script-src self', 'ok' => str_contains($csp, "script-src 'self'")];
$results[] = ['name' => 'CSP frame-ancestors none', 'ok' => str_contains($csp, "frame-ancestors 'none'")];
$results[] = ['name' => 'CSP default-src self', 'ok' => str_contains($csp, "default-src 'self'")];
$results[] = ['name' => 'X-Frame-Options DENY', 'ok' => (($headers['x-frame-options'] ?? '') === 'DENY')];
$results[] = ['name' => 'Referrer-Policy same-origin', 'ok' => (($headers['referrer-policy'] ?? '') === 'same-origin')];
$results[] = ['name' => 'Permissions-Policy present', 'ok' => isset($headers['permissions-policy'])];
$results[] = [
    'name' => 'legacy admin directory archived',
    'ok' => !is_dir(APP_ROOT . '/admin') && is_dir(APP_ROOT . '/storage/legacy_archive/admin'),
];
$results[] = [
    'name' => 'hardcoded legacy password removed from archived admin.php',
    'ok' => is_file(APP_ROOT . '/storage/legacy_archive/admin/admin.php')
        && !preg_match('/\$pass\s*=\s*[\'"][^\'"]+[\'"]\s*;/', (string) file_get_contents(APP_ROOT . '/storage/legacy_archive/admin/admin.php')),
];

foreach ($results as $result) {
    if (!$result['ok']) {
        $failed++;
    }
}

$payload = [
    'ok' => $failed === 0,
    'base' => $base,
    'checks' => $results,
    'passed' => count($results) - $failed,
    'failed' => $failed,
];
if (in_array('--json', $argv, true)) {
    echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
} else {
    foreach ($results as $result) {
        echo ($result['ok'] ? '[OK] ' : '[FAIL] ') . $result['name']
            . (isset($result['status']) ? ' (status ' . $result['status'] . ')' : '') . PHP_EOL;
    }
    echo sprintf("Security smoke: %d/%d passed.\n", $payload['passed'], count($results));
}
exit($failed === 0 ? 0 : 1);
