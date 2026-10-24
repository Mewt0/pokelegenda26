<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit(2); }
$root = dirname(__DIR__);
$log = tempnam(sys_get_temp_dir(), 'pokemonchic_error_');
$checks = 0;
try {
    foreach ([
        ['exception', '/api/test', 'echo "partial-body"; throw new RuntimeException("private-test-detail");', true],
        ['fatal', '/api/test', 'echo "partial-body"; trigger_error("private-test-detail", E_USER_ERROR);', true],
        ['html', '/game/test', 'throw new RuntimeException("private-test-detail");', false],
    ] as [$name, $uri, $failure, $json]) {
        $code = 'require ' . var_export($root . '/src/Http/ErrorHandler.php', true) . '; $_SERVER["REQUEST_URI"]=' . var_export($uri, true) . '; Pokemon8\\Http\\ErrorHandler::register(' . var_export($log, true) . '); ' . $failure;
        $process = proc_open([PHP_BINARY, '-d', 'display_errors=0', '-d', 'log_errors=0', '-r', $code], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['redirect', 1]], $pipes);
        if (!is_resource($process)) { throw new RuntimeException('Cannot start fixture.'); }
        fclose($pipes[0]); $output = stream_get_contents($pipes[1]); fclose($pipes[1]); proc_close($process);
        $payload = json_decode($output, true);
        $ok = $json ? is_array($payload) && ($payload['ok'] ?? true) === false && !empty($payload['request_id']) : str_contains($output, '<!doctype html>');
        if (!$ok || str_contains($output, 'private-test-detail') || str_contains($output, 'partial-body')) { throw new RuntimeException($name . ' response not sanitized'); }
        echo 'PASS ' . $name . PHP_EOL; $checks++;
    }
    if (substr_count((string) file_get_contents($log), '[request:') !== 3) { throw new RuntimeException('Error events not logged exactly once.'); }
    echo 'PASS exactly-once logging' . PHP_EOL; $checks++;
    require $root . '/src/Support/Autoload.php';
    $router = new Pokemon8\Http\Router();
    foreach (['/api/unknown' => 'application/json', '/game/unknown' => 'text/html'] as $path => $type) {
        $response = $router->dispatch(new Pokemon8\Http\Request('GET', $path, [], [], []));
        if ($response->status !== 404 || !str_contains($response->headers['Content-Type'], $type)) { throw new RuntimeException('Wrong 404 response.'); }
        echo 'PASS 404 ' . $type . PHP_EOL; $checks++;
    }
    $_ENV['APP_ENV'] = 'production';
    $_ENV['QA_TOOLS_ENABLED'] = '1';
    $admin = (new ReflectionClass(Pokemon8\Repository\AdminRepository::class))->newInstanceWithoutConstructor();
    if (($admin->qaSeedRun(1, ['action' => 'setup_accounts'])['ok'] ?? true) !== false) {
        throw new RuntimeException('Production QA writes were enabled.');
    }
    echo 'PASS production QA writes denied before DB access' . PHP_EOL; $checks++;
    echo 'API error regression: ' . $checks . '/' . $checks . ' passed.' . PHP_EOL;
} finally {
    if (is_file($log)) { unlink($log); }
}
