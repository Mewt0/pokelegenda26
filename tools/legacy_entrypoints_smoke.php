<?php
declare(strict_types=1);

/** GET-only legacy exposure audit. Does not follow redirects or execute game state APIs.
 * php tools/legacy_entrypoints_smoke.php --anonymous-only --json
 * php tools/legacy_entrypoints_smoke.php --cookie-file=<existing QA Netscape cookie jar> --json
 * Without --anonymous-only, an authenticated QA cookie jar is required.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
$options = getopt('', ['base:', 'cookie-file:', 'anonymous-only', 'json', 'help']);
if (isset($options['help'])) {
    echo "Usage: php tools/legacy_entrypoints_smoke.php [--base=http://pokemonchic.com] (--anonymous-only | --cookie-file=PATH) [--json]\n";
    exit(0);
}
$base = rtrim((string) ($options['base'] ?? 'http://pokemonchic.com'), '/');
$cookieFile = (string) ($options['cookie-file'] ?? '');
$anonymousOnly = isset($options['anonymous-only']);
if (!extension_loaded('curl') || !filter_var($base, FILTER_VALIDATE_URL)
    || !in_array(parse_url($base, PHP_URL_SCHEME), ['http', 'https'], true)
    || (!$anonymousOnly && !is_readable($cookieFile))) {
    fwrite(STDERR, "Requires curl, an HTTP(S) base URL, and --anonymous-only or a readable QA cookie jar.\n");
    exit(2);
}

function legacyProbe(string $base, string $path, string $cookies): array
{
    $curl = curl_init($base . $path);
    curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_CONNECTTIMEOUT => 5, CURLOPT_TIMEOUT => 15]);
    if ($cookies !== '') {
        curl_setopt($curl, CURLOPT_COOKIEFILE, $cookies);
    }
    $body = curl_exec($curl);
    $result = ['status' => (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE),
        'redirect' => (string) curl_getinfo($curl, CURLINFO_REDIRECT_URL),
        'body' => is_string($body) ? $body : '', 'transport_error' => $body === false];
    return $result;
}

$paths = ['/admin/', '/admin/admin.php', '/admin/poke.php', '/admin/gitem.php',
    '/admin/bb_news_admin.php', '/include/data.world.php', '/include/function/Services_JSON.php',
    '/game.php?go=items', '/events.php', '/page.php?id=1'];
$modes = ['anonymous' => ''];
if (!$anonymousOnly) {
    // Auth identity endpoint is read-only; /game/state can start a battle and is excluded.
    $identity = legacyProbe($base, '/api/profile/settings', $cookieFile);
    $user = json_decode($identity['body'], true);
    if ($identity['status'] !== 200 || !is_array($user) || ($user['ok'] ?? false) !== true || !isset($user['settings'])) {
        fwrite(STDERR, "QA session could not be verified via /api/profile/settings; authenticated checks NOT RUN.\n");
        exit(2);
    }
    $modes['qa_session'] = $cookieFile;
}
$rows = [];
foreach ($modes as $mode => $cookies) {
    foreach ($paths as $path) {
        $response = legacyProbe($base, $path, $cookies);
        $leak = preg_match('~Fatal error|Stack trace|Parse error|Warning:|[A-Z]:[\\\\/]OSPanel|/(?:var/www|home/[^/]+|srv/www)/~i', $response['body']) === 1;
        $redirectOk = $path === '/page.php?id=1'
            && in_array($response['status'], [301, 302, 303, 307, 308], true)
            && $response['redirect'] === $base . '/game/profile?id=1';
        $ok = !$response['transport_error'] && !$leak
            && ($path === '/page.php?id=1' ? $redirectOk : in_array($response['status'], [403, 404, 410], true));
        $rows[] = ['mode' => $mode, 'path' => $path, 'status' => $response['status'],
            'error_leak' => $leak, 'transport_error' => $response['transport_error'], 'pass' => $ok];
    }
}
$failed = count(array_filter($rows, static fn(array $row): bool => !$row['pass']));
$report = ['scope' => 'explicit legacy URL sample; not exhaustive',
    'authenticated' => $anonymousOnly ? 'NOT RUN' : 'RUN',
    'checked' => count($rows), 'violations' => $failed, 'results' => $rows];
if (isset($options['json'])) {
    echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
} else {
    foreach ($rows as $row) {
        printf("%s %s %s HTTP %d leak=%s\n", $row['pass'] ? 'PASS' : 'FAIL', $row['mode'], $row['path'], $row['status'], $row['error_leak'] ? 'yes' : 'no');
    }
    printf("Checked %d; violations %d; authenticated: %s\n", count($rows), $failed, $report['authenticated']);
}
exit($failed > 0 ? 1 : 0);
