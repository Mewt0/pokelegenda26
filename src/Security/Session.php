<?php
declare(strict_types=1);

namespace Pokemon8\Security;

final class Session
{
    public function __construct(private readonly string $name)
    {
    }

    /**
     * Запускает сессию с безопасными cookie-настройками.
     */
    public function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_name($this->name);
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => $this->isHttps(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }

    /**
     * Меняет ID сессии после логина, чтобы защититься от session fixation.
     */
    public function regenerate(): void
    {
        session_regenerate_id(true);
    }

    /**
     * Полностью очищает сессию пользователя.
     */
    public function destroy(): void
    {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    /**
     * Читает значение из сессии.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Записывает значение в сессию.
     */
    public function put(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Определяет, идет ли запрос через HTTPS.
     */
    private function isHttps(): bool
    {
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            return true;
        }

        $remoteAddress = trim((string) ($_SERVER['REMOTE_ADDR'] ?? ''));
        $trustedProxies = array_filter(array_map(
            static fn (string $proxy): string => trim($proxy),
            explode(',', (string) ($_ENV['TRUSTED_PROXIES'] ?? ''))
        ));

        return $remoteAddress !== ''
            && in_array($remoteAddress, $trustedProxies, true)
            && strtolower(trim(explode(',', (string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''))[0])) === 'https';
    }
}
