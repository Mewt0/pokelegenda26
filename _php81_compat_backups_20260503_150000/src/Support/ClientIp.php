<?php
declare(strict_types=1);

namespace Pokemon8\Support;

final class ClientIp
{
    public static function fromServer(array $server): string
    {
        // Берем первый валидный IP из доверенных серверных заголовков.
        foreach (['HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'] as $header) {
            $raw = $server[$header] ?? '';
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
}
