<?php
declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));

require_once __DIR__ . '/env.php';
app_load_env(APP_ROOT . '/.env');

$appDebug = filter_var(app_env('APP_DEBUG', '1'), FILTER_VALIDATE_BOOL);

error_reporting((int) app_env('APP_ERROR_REPORTING', (string) E_ALL));
ini_set('display_errors', $appDebug ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', (string) app_env('APP_ERROR_LOG', APP_ROOT . '/log_php_errors.txt'));
ini_set('default_charset', 'UTF-8');

$timezone = (string) app_env('APP_TIMEZONE', 'Europe/Prague');
if ($timezone !== '') {
    date_default_timezone_set($timezone);
}

function app_is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
}

function app_start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => app_is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

function app_destroy_session(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return;
    }

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'] ?? '/',
            $params['domain'] ?? '',
            (bool) ($params['secure'] ?? app_is_https()),
            (bool) ($params['httponly'] ?? true)
        );
    }

    session_destroy();
}

function app_redirect(string $url, int $statusCode = 302): never
{
    header('Location: ' . $url, true, $statusCode);
    exit;
}

function app_e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function app_client_ip(): string
{
    foreach (['HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'] as $header) {
        $raw = $_SERVER[$header] ?? '';
        if ($raw === '' || strtolower((string) $raw) === 'unknown') {
            continue;
        }

        $ip = trim(explode(',', (string) $raw)[0]);
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }
    }

    return '127.0.0.1';
}

function app_legacy_password_hash(string $password): string
{
    return strrev(md5($password)) . 'b3p6f';
}

$config = [
    'base' => (string) app_env('APP_URL', 'http://127.0.0.1:8000/'),
    'server' => (string) app_env('DB_HOST', '127.0.0.1'),
    'port' => (int) app_env('DB_PORT', '3306'),
    'user' => (string) app_env('DB_USER', 'pokemon'),
    'pass' => (string) app_env('DB_PASS', 'pokemon'),
    'db' => (string) app_env('DB_DATABASE', 'pokemon'),
    'charset' => (string) app_env('DB_CHARSET', 'utf8mb4'),
    'run' => (int) app_env('APP_RUN', '1'),
    'techwork' => (string) app_env('APP_TECHWORK', '0'),
    'time_techwork' => (string) app_env(
        'APP_TECHWORK_MESSAGE',
        '<b>Приблизительное время:</b> <i>20 - 30 минут</i>.'
    ),
];

if (!defined('SESSION_BROWSER_SIGN_SECRET')) {
    define('SESSION_BROWSER_SIGN_SECRET', (string) app_env('SESSION_BROWSER_SIGN_SECRET', 'change-me-local-secret'));
}

if (!function_exists('getBrowserSign')) {
    function getBrowserSign(): string
    {
        $ip = app_client_ip();
        $parts = explode('.', $ip);
        $network = ($parts[0] ?? '0') . '.' . ($parts[1] ?? '0');
        $agent = $_SERVER['HTTP_USER_AGENT'] ?? 'none';

        return hash('sha256', SESSION_BROWSER_SIGN_SECRET . '::' . $network . '::' . $agent);
    }
}

if (isset($_SESSION['id']) && in_array((int) $_SESSION['id'], [1, 2, 6], true)) {
    $config['techwork'] = '0';
}
