<?php
declare(strict_types=1);

namespace Pokemon8\Http;

final class Request
{
    public function __construct(
        public string $method,
        public string $path,
        public array $query,
        public array $post,
        public array $server,
    ) {
    }

    public static function capture(): self
    {
        // Собираем запрос один раз, дальше контроллеры работают с объектом, а не с $_GET/$_POST напрямую.
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $post = $_POST;
        if ($post === [] && in_array($method, ['PUT', 'PATCH', 'DELETE'], true)) {
            parse_str((string) file_get_contents('php://input'), $post);
        }

        return new self(
            $method,
            '/' . trim($path, '/'),
            $_GET,
            $post,
            $_SERVER,
        );
    }

    public function input(string $key, string $default = ''): string
    {
        // POST важнее query-параметров: формы должны переопределять значения из URL.
        $value = $this->post[$key] ?? $this->query[$key] ?? $default;
        return trim((string) $value);
    }
}
