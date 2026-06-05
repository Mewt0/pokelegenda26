<?php
declare(strict_types=1);

/**
 * In-game Bug Reporter HTTP smoke.
 *
 * Usage:
 *   php tools/bug_reporter_smoke.php --login=Tacos --password=... --forbidden-login=NIGA --forbidden-password=...
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Run from CLI.\n");
    exit(2);
}

if (!extension_loaded('curl')) {
    fwrite(STDERR, "PHP curl extension is required.\n");
    exit(2);
}

define('APP_ROOT', dirname(__DIR__));

require APP_ROOT . '/src/Support/Autoload.php';

Pokemon8\Support\Env::load(APP_ROOT . '/.env');

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

$db = Pokemon8\Database\Connection::make(require APP_ROOT . '/config/database.php');
$results = [];
$createdReportId = 0;
$smokeTitle = 'SMOKE bug reporter ' . date('YmdHis');

try {
    $admin = new SmokeClient($baseUrl);
    login($admin, $login, $password);
    $gamePage = $admin->request('GET', '/game');
    $csrf = extractCsrf($gamePage['body']);
    assertTrue($results, 'bug_reporter.csrf', $csrf !== '', 'csrf=' . ($csrf !== '' ? 'yes' : 'no'));

    $csrfFail = $admin->request('POST', '/api/bug-reports', [
        'title' => $smokeTitle . ' csrf',
        'description' => 'negative csrf smoke',
    ]);
    assertTrue($results, 'bug_reporter.csrf.negative', $csrfFail['status'] === 419, 'status=' . $csrfFail['status']);

    $create = $admin->request('POST', '/api/bug-reports', [
        '_csrf' => $csrf,
        'title' => $smokeTitle,
        'description' => 'Smoke report with state, battle id and logs.',
        'severity' => 'bug',
        'page_url' => $baseUrl . '/game',
        'route' => '/game',
        'location_id' => 1,
        'battle_id' => 0,
        'client_state_json' => json_encode(['locationId' => 1, 'battleState' => ['mode' => 'smoke']], JSON_UNESCAPED_UNICODE),
        'battle_state_json' => json_encode(['mode' => 'smoke'], JSON_UNESCAPED_UNICODE),
        'client_logs_json' => json_encode([['level' => 'smoke', 'message' => 'bug reporter smoke']], JSON_UNESCAPED_UNICODE),
        'viewport_json' => json_encode(['width' => 1280, 'height' => 720], JSON_UNESCAPED_UNICODE),
    ]);
    $createJson = json_decode($create['body'], true);
    $createdReportId = (int) ($createJson['reportId'] ?? 0);
    assertTrue($results, 'bug_reporter.create', $create['status'] === 200 && ($createJson['ok'] ?? false) === true && $createdReportId > 0, 'status=' . $create['status'] . ', id=' . $createdReportId);

    [$adminList] = jsonCheck($admin, $results, 'admin.bug_reports.list', '/api/admin/bug-reports?q=' . rawurlencode($smokeTitle));
    $found = false;
    foreach (($adminList['reports'] ?? []) as $row) {
        if ((int) ($row['id'] ?? 0) === $createdReportId && isset($row['game_state'], $row['client_logs'], $row['server_logs'])) {
            $found = true;
            break;
        }
    }
    assertTrue($results, 'admin.bug_reports.attachments', $found, 'report=' . $createdReportId . ', rows=' . count($adminList['reports'] ?? []));

    $status = $admin->request('POST', '/api/admin/bug-reports/status', [
        '_csrf' => $csrf,
        'report_id' => $createdReportId,
        'status' => 'fixed',
        'note' => 'Smoke fixed.',
    ]);
    $statusJson = json_decode($status['body'], true);
    assertTrue($results, 'admin.bug_reports.status', $status['status'] === 200 && ($statusJson['ok'] ?? false) === true, 'status=' . $status['status']);

    [$gm] = jsonCheck($admin, $results, 'admin.gm_center.bug_reports', '/api/admin/gm-center');
    assertTrue($results, 'admin.gm_center.bug_reports.shape', isset($gm['gmCenter']['bugReports']['open'], $gm['gmCenter']['bugReports']['recent']), 'bug report summary');

    if ($forbiddenLogin !== '' && $forbiddenPassword !== '') {
        $player = new SmokeClient($baseUrl);
        login($player, $forbiddenLogin, $forbiddenPassword);
        $forbidden = $player->request('GET', '/api/admin/bug-reports');
        assertTrue($results, 'admin.bug_reports.forbidden.player', $forbidden['status'] === 403, 'status=' . $forbidden['status']);
    }
} catch (Throwable $e) {
    assertTrue($results, 'exception', false, $e->getMessage());
} finally {
    cleanupSmokeReport($db, $smokeTitle, $createdReportId);
}

printResults($results);
exit(count(array_filter($results, static fn (array $row): bool => !$row['ok'])) > 0 ? 1 : 0);

function cleanupSmokeReport(PDO $db, string $title, int $reportId): void
{
    $ids = [];
    if ($reportId > 0) {
        $ids[] = $reportId;
    }
    $stmt = $db->prepare('SELECT id FROM bug_reports WHERE title LIKE :title');
    $stmt->execute(['title' => $title . '%']);
    foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) ?: [] as $id) {
        $ids[] = (int) $id;
    }
    $ids = array_values(array_unique(array_filter($ids)));
    if ($ids === []) {
        return;
    }
    $in = implode(',', array_fill(0, count($ids), '?'));
    $db->prepare('DELETE FROM bug_report_events WHERE report_id IN (' . $in . ')')->execute($ids);
    $db->prepare('DELETE FROM bug_reports WHERE id IN (' . $in . ')')->execute($ids);
    if (tableHasColumn($db, 'game_notifications', 'source_type')) {
        $db->prepare('DELETE FROM game_notifications WHERE source_type = "bug_report" AND source_id IN (' . $in . ')')->execute(array_map('strval', $ids));
    }
}

function tableHasColumn(PDO $db, string $table, string $column): bool
{
    $stmt = $db->prepare(
        'SELECT COUNT(*)
           FROM information_schema.COLUMNS
          WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = :table
            AND COLUMN_NAME = :column'
    );
    $stmt->execute(['table' => $table, 'column' => $column]);
    return (int) ($stmt->fetchColumn() ?: 0) > 0;
}

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
    assertTrue($results, $name, $ok, 'status=' . $response['status'] . (!$ok ? ', body=' . mb_substr($response['body'], 0, 220) : ''));
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
    if (preg_match('/data-csrf="([^"]+)"/', $html, $m)) {
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
    echo sprintf("Bug Reporter smoke: %d/%d passed.\n", $passed, count($results));
}

final class SmokeClient
{
    private string $cookieFile;

    public function __construct(private string $baseUrl)
    {
        $this->cookieFile = tempnam(sys_get_temp_dir(), 'bug_reporter_cookie_') ?: (sys_get_temp_dir() . '/bug_reporter_cookie.txt');
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
