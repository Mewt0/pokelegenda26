<?php
declare(strict_types=1);

/**
 * Admin/GM Center HTTP smoke.
 *
 * Usage:
 *   php tools/admin_gm_center_smoke.php --login=Tacos --password=...
 *   php tools/admin_gm_center_smoke.php --login=Tacos --password=... --forbidden-login=NIGA --forbidden-password=...
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Run from CLI.\n");
    exit(2);
}

if (!extension_loaded('curl')) {
    fwrite(STDERR, "PHP curl extension is required.\n");
    exit(2);
}

$options = getopt('', ['base::', 'login:', 'password:', 'forbidden-login::', 'forbidden-password::']);
$baseUrl = rtrim((string) ($options['base'] ?? getenv('SMOKE_BASE_URL') ?: 'http://pokemonchic.com'), '/');
$login = (string) ($options['login'] ?? getenv('SMOKE_LOGIN') ?: '');
$password = (string) ($options['password'] ?? getenv('SMOKE_PASSWORD') ?: '');
$forbiddenLogin = (string) ($options['forbidden-login'] ?? '');
$forbiddenPassword = (string) ($options['forbidden-password'] ?? '');

if ($login === '' || $password === '') {
    fwrite(STDERR, "Missing --login/--password.\n");
    exit(2);
}

$results = [];

try {
    $admin = new SmokeClient($baseUrl);
    login($admin, $login, $password);

    $adminPage = $admin->request('GET', '/game/admin');
    assertTrue($results, 'admin.page', $adminPage['status'] === 200 && str_contains($adminPage['body'], 'admin-shell'), 'status=' . $adminPage['status']);
    assertTrue($results, 'admin.tabs', str_contains($adminPage['body'], 'data-admin-tab="commission"') && str_contains($adminPage['body'], 'data-admin-tab="battle_replays"'), 'GM tabs present');

    [$gm] = jsonCheck($admin, $results, 'admin.gm_center', '/api/admin/gm-center');
    assertTrue($results, 'admin.gm_center.shape', isset($gm['gmCenter']['health']['cards'], $gm['gmCenter']['activeBattles'], $gm['gmCenter']['marketModeration'], $gm['gmCenter']['logs']), 'cards=' . count($gm['gmCenter']['health']['cards'] ?? []));
    assertTrue($results, 'admin.gm_center.cards', count($gm['gmCenter']['health']['cards'] ?? []) >= 8, 'cards=' . count($gm['gmCenter']['health']['cards'] ?? []));

    [$dashboard] = jsonCheck($admin, $results, 'admin.dashboard', '/api/admin/dashboard');
    assertTrue($results, 'admin.dashboard.gm', isset($dashboard['dashboard']['gmCenter']['health']['status']), 'health=' . (string) ($dashboard['dashboard']['gmCenter']['health']['status'] ?? 'missing'));

    [$commission] = jsonCheck($admin, $results, 'admin.commission.dashboard', '/api/admin/commission/dashboard');
    assertTrue($results, 'admin.commission.shape', isset($commission['dashboard']['overview']), 'overview');

    [$replays] = jsonCheck($admin, $results, 'admin.replays', '/api/admin/battle-replays');
    assertTrue($results, 'admin.replays.shape', isset($replays['rows'], $replays['dashboard']), 'rows=' . count($replays['rows'] ?? []));

    [$moderation] = jsonCheck($admin, $results, 'admin.moderation', '/api/admin/moderation');
    assertTrue($results, 'admin.moderation.shape', isset($moderation['moderation']['punishments'], $moderation['moderation']['chat']), 'moderation rows');

    [$audit] = jsonCheck($admin, $results, 'admin.audit', '/api/admin/audit');
    assertTrue($results, 'admin.audit.shape', isset($audit['audit']) && is_array($audit['audit']), 'rows=' . count($audit['audit'] ?? []));

    $csrfFail = $admin->request('POST', '/api/admin/moderation/action', ['target' => 'NIGA', 'action' => 'warn', 'reason' => 'csrf smoke']);
    assertTrue($results, 'admin.csrf.negative', $csrfFail['status'] === 419, 'status=' . $csrfFail['status']);

    if ($forbiddenLogin !== '' && $forbiddenPassword !== '') {
        $player = new SmokeClient($baseUrl);
        login($player, $forbiddenLogin, $forbiddenPassword);
        $forbidden = $player->request('GET', '/api/admin/gm-center');
        assertTrue($results, 'admin.forbidden.player', $forbidden['status'] === 403, 'status=' . $forbidden['status']);
    }
} catch (Throwable $e) {
    assertTrue($results, 'exception', false, $e->getMessage());
}

printResults($results);
exit(count(array_filter($results, static fn (array $row): bool => !$row['ok'])) > 0 ? 1 : 0);

function login(SmokeClient $client, string $login, string $password): void
{
    $home = $client->request('GET', '/');
    $csrf = extractCsrf($home['body']);
    if ($csrf === '') {
        throw new RuntimeException('Login CSRF token not found.');
    }
    $client->request('POST', '/login', [
        '_csrf' => $csrf,
        'LOGIN' => $login,
        'PASSWORD' => $password,
    ]);
}

function jsonCheck(SmokeClient $client, array &$results, string $name, string $path): array
{
    $response = $client->request('GET', $path);
    $json = json_decode($response['body'], true);
    $ok = $response['status'] >= 200 && $response['status'] < 300 && is_array($json) && ($json['ok'] ?? false) === true;
    assertTrue($results, $name, $ok, 'status=' . $response['status']);
    return [is_array($json) ? $json : [], $response];
}

function extractCsrf(string $html): string
{
    if (preg_match('/name="_csrf"\s+value="([^"]+)"/', $html, $m)) {
        return html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
    }
    if (preg_match('/<meta\s+name="csrf-token"\s+content="([^"]+)"/', $html, $m)) {
        return html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
    }
    return '';
}

function assertTrue(array &$results, string $name, bool $ok, string $details = ''): void
{
    $results[] = ['name' => $name, 'ok' => $ok, 'details' => $details];
}

function printResults(array $results): void
{
    $passed = 0;
    foreach ($results as $row) {
        if ($row['ok']) {
            $passed++;
        }
        echo sprintf("[%s] %s%s\n", $row['ok'] ? 'OK ' : 'FAIL', $row['name'], $row['details'] !== '' ? ' - ' . $row['details'] : '');
    }
    echo sprintf("Admin GM Center smoke: %d/%d passed.\n", $passed, count($results));
}

final class SmokeClient
{
    private string $cookieFile;

    public function __construct(private string $baseUrl)
    {
        $this->cookieFile = tempnam(sys_get_temp_dir(), 'gm_smoke_cookie_') ?: (sys_get_temp_dir() . '/gm_smoke_cookie.txt');
    }

    /** @param array<string,string|int|float|bool> $data */
    public function request(string $method, string $path, array $data = []): array
    {
        $ch = curl_init($this->baseUrl . $path);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_COOKIEJAR => $this->cookieFile,
            CURLOPT_COOKIEFILE => $this->cookieFile,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HEADER => false,
            CURLOPT_HTTPHEADER => ['Accept: application/json, text/html;q=0.9,*/*;q=0.8'],
        ]);
        if (strtoupper($method) === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }
        $body = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($body === false) {
            throw new RuntimeException('HTTP request failed: ' . $error);
        }

        return ['status' => $status, 'body' => (string) $body];
    }
}
