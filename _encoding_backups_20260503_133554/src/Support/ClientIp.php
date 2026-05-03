<?php
declare(strict_types=1);

namespace Pokemon8\Support;

final class ClientIp
{
    public static function fromServer(array $server): string
    {
        // Р вЂР ВµРЎР‚Р ВµР С Р С—Р ВµРЎР‚Р Р†РЎвЂ№Р в„– Р Р†Р В°Р В»Р С‘Р Т‘Р Р…РЎвЂ№Р в„– IP Р С‘Р В· Р Т‘Р С•Р Р†Р ВµРЎР‚Р ВµР Р…Р Р…РЎвЂ№РЎвЂ¦ РЎРѓР ВµРЎР‚Р Р†Р ВµРЎР‚Р Р…РЎвЂ№РЎвЂ¦ Р В·Р В°Р С–Р С•Р В»Р С•Р Р†Р С”Р С•Р Р†.
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
