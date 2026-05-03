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
        // Р РЋР С•Р В±Р С‘РЎР‚Р В°Р ВµР С Р В·Р В°Р С—РЎР‚Р С•РЎРѓ Р С•Р Т‘Р С‘Р Р… РЎР‚Р В°Р В·, Р Т‘Р В°Р В»РЎРЉРЎв‚¬Р Вµ Р С”Р С•Р Р…РЎвЂљРЎР‚Р С•Р В»Р В»Р ВµРЎР‚РЎвЂ№ РЎР‚Р В°Р В±Р С•РЎвЂљР В°РЎР‹РЎвЂљ РЎРѓ Р С•Р В±РЎР‰Р ВµР С”РЎвЂљР С•Р С, Р В° Р Р…Р Вµ РЎРѓ $_GET/$_POST Р Р…Р В°Р С—РЎР‚РЎРЏР СРЎС“РЎР‹.
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
        // POST Р Р†Р В°Р В¶Р Р…Р ВµР Вµ query-Р С—Р В°РЎР‚Р В°Р СР ВµРЎвЂљРЎР‚Р С•Р Р†, Р С—Р С•РЎвЂљР С•Р СРЎС“ РЎвЂЎРЎвЂљР С• РЎвЂћР С•РЎР‚Р СРЎвЂ№ Р Т‘Р С•Р В»Р В¶Р Р…РЎвЂ№ Р С—Р ВµРЎР‚Р ВµР С•Р С—РЎР‚Р ВµР Т‘Р ВµР В»РЎРЏРЎвЂљРЎРЉ Р В·Р Р…Р В°РЎвЂЎР ВµР Р…Р С‘РЎРЏ Р С‘Р В· URL.
        $value = $this->post[$key] ?? $this->query[$key] ?? $default;
        return trim((string) $value);
    }
}
