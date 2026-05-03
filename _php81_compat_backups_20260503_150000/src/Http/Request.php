<?php
declare(strict_types=1);

namespace Pokemon8\Http;

final readonly class Request
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

        return new self(
            strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET'),
            '/' . trim($path, '/'),
            $_GET,
            $_POST,
            $_SERVER,
        );
    }

    public function input(string $key, string $default = ''): string
    {
        // POST важнее query-параметров, потому что формы должны переопределять значения из URL.
        $value = $this->post[$key] ?? $this->query[$key] ?? $default;
        return trim((string) $value);
    }
}
