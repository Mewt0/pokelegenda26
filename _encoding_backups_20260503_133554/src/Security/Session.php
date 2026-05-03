<?php
declare(strict_types=1);

namespace Pokemon8\Security;

final class Session
{
    public function __construct(private readonly string $name)
    {
    }

    /**
     * Р вЂ”Р В°Р С—РЎС“РЎРѓР С”Р В°Р ВµРЎвЂљ РЎРѓР ВµРЎРѓРЎРѓР С‘РЎР‹ РЎРѓ Р В±Р ВµР В·Р С•Р С—Р В°РЎРѓР Р…РЎвЂ№Р СР С‘ cookie-Р Р…Р В°РЎРѓРЎвЂљРЎР‚Р С•Р в„–Р С”Р В°Р СР С‘.
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
     * Р СљР ВµР Р…РЎРЏР ВµРЎвЂљ ID РЎРѓР ВµРЎРѓРЎРѓР С‘Р С‘ Р С—Р С•РЎРѓР В»Р Вµ Р В»Р С•Р С–Р С‘Р Р…Р В°, РЎвЂЎРЎвЂљР С•Р В±РЎвЂ№ Р В·Р В°РЎвЂ°Р С‘РЎвЂљР С‘РЎвЂљРЎРЉРЎРѓРЎРЏ Р С•РЎвЂљ session fixation.
     */
    public function regenerate(): void
    {
        session_regenerate_id(true);
    }

    /**
     * Р СџР С•Р В»Р Р…Р С•РЎРѓРЎвЂљРЎРЉРЎР‹ Р С•РЎвЂЎР С‘РЎвЂ°Р В°Р ВµРЎвЂљ РЎРѓР ВµРЎРѓРЎРѓР С‘РЎР‹ Р С—Р С•Р В»РЎРЉР В·Р С•Р Р†Р В°РЎвЂљР ВµР В»РЎРЏ.
     */
    public function destroy(): void
    {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    /**
     * Р В§Р С‘РЎвЂљР В°Р ВµРЎвЂљ Р В·Р Р…Р В°РЎвЂЎР ВµР Р…Р С‘Р Вµ Р С‘Р В· РЎРѓР ВµРЎРѓРЎРѓР С‘Р С‘.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Р вЂ”Р В°Р С—Р С‘РЎРѓРЎвЂ№Р Р†Р В°Р ВµРЎвЂљ Р В·Р Р…Р В°РЎвЂЎР ВµР Р…Р С‘Р Вµ Р Р† РЎРѓР ВµРЎРѓРЎРѓР С‘РЎР‹.
     */
    public function put(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Р С›Р С—РЎР‚Р ВµР Т‘Р ВµР В»РЎРЏР ВµРЎвЂљ, Р С‘Р Т‘Р ВµРЎвЂљ Р В»Р С‘ Р В·Р В°Р С—РЎР‚Р С•РЎРѓ РЎвЂЎР ВµРЎР‚Р ВµР В· HTTPS.
     */
    private function isHttps(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    }
}
