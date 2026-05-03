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
        // РЎРѕР±РёСЂР°РµРј Р·Р°РїСЂРѕСЃ РѕРґРёРЅ СЂР°Р·, РґР°Р»СЊС€Рµ РєРѕРЅС‚СЂРѕР»Р»РµСЂС‹ СЂР°Р±РѕС‚Р°СЋС‚ СЃ РѕР±СЉРµРєС‚РѕРј, Р° РЅРµ СЃ $_GET/$_POST РЅР°РїСЂСЏРјСѓСЋ.
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
        // POST РІР°Р¶РЅРµРµ query-РїР°СЂР°РјРµС‚СЂРѕРІ, РїРѕС‚РѕРјСѓ С‡С‚Рѕ С„РѕСЂРјС‹ РґРѕР»Р¶РЅС‹ РїРµСЂРµРѕРїСЂРµРґРµР»СЏС‚СЊ Р·РЅР°С‡РµРЅРёСЏ РёР· URL.
        $value = $this->post[$key] ?? $this->query[$key] ?? $default;
        return trim((string) $value);
    }
}
