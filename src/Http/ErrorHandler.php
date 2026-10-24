<?php
declare(strict_types=1);

namespace Pokemon8\Http;

use Throwable;

final class ErrorHandler
{
    public static function isApi(): bool
    {
        $path = (string) (parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH) ?? '');
        return $path === '/api' || str_starts_with($path, '/api/');
    }

    public static function register(string $logFile): void
    {
        $api = self::isApi();
        $bufferLevel = ob_get_level();
        if ($api) {
            ini_set('display_errors', '0');
            ob_start();
        }
        $reported = false;
        $report = static function (string $detail) use ($api, $bufferLevel, $logFile, &$reported): void {
            if ($reported) { return; }
            $reported = true;
            $id = bin2hex(random_bytes(8));
            error_log(sprintf('[%s] [request:%s] %s%s', date('c'), $id, str_replace(["\r", "\n"], ' ', $detail), PHP_EOL), 3, $logFile);
            // Once a response is on the wire, only log; never append a second body.
            if (headers_sent()) { return; }
            if ($api) {
                while (ob_get_level() > $bufferLevel) { ob_end_clean(); }
            }
            if (!headers_sent()) {
                http_response_code(500);
                header('Content-Type: ' . ($api ? 'application/json' : 'text/html') . '; charset=UTF-8');
                header('Cache-Control: no-store');
            }
            echo $api
                ? json_encode(['ok' => false, 'error' => 'internal_error', 'message' => 'Ошибка сервера. Попробуйте позже.', 'request_id' => $id], JSON_UNESCAPED_UNICODE)
                : '<!doctype html><html lang="ru"><meta charset="utf-8"><title>Ошибка сервера</title><h1>Ошибка сервера</h1><p>Попробуйте позже.</p></html>';
        };
        set_exception_handler(static function (Throwable $error) use ($report): void {
            $report(get_class($error) . ': ' . $error->getMessage() . ' in ' . $error->getFile() . ':' . $error->getLine());
        });
        register_shutdown_function(static function () use ($report, &$reported): void {
            if ($reported) { return; }
            $error = error_get_last();
            if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR], true)) {
                $report($error['message'] . ' in ' . $error['file'] . ':' . $error['line']);
            }
        });
    }
}
