<?php
declare(strict_types=1);

namespace Pokemon8\Http;

use Closure;

final class Router
{
    /** @var array<string, Closure> */
    private array $routes = [];

    public function get(string $path, Closure $handler): void
    {
        $this->routes['GET ' . $this->normalize($path)] = $handler;
    }

    public function post(string $path, Closure $handler): void
    {
        $this->routes['POST ' . $this->normalize($path)] = $handler;
    }

    public function dispatch(Request $request): Response
    {
        // Р В Р С•РЎС“РЎвЂљ Р С‘РЎвЂ°Р ВµРЎвЂљРЎРѓРЎРЏ Р С—Р С• Р С—Р В°РЎР‚Р Вµ "HTTP-Р СР ВµРЎвЂљР С•Р Т‘ + Р С—РЎС“РЎвЂљРЎРЉ".
        $key = $request->method . ' ' . $this->normalize($request->path);
        $handler = $this->routes[$key] ?? null;

        if ($handler === null) {
            return new Response('<h1>404</h1>', 404);
        }

        return $handler($request);
    }

    private function normalize(string $path): string
    {
        $path = '/' . trim($path, '/');
        return $path === '//' ? '/' : $path;
    }
}
