<?php
declare(strict_types=1);

namespace Pokemon8\Support;

final class ClientIp
{
    public static function fromServer(array $server): string
    {
        $remoteAddress = self::firstValidIp((string) ($server['REMOTE_ADDR'] ?? ''));
        $trustedProxies = self::trustedProxies();
        $canTrustProxyHeaders = $remoteAddress !== null && in_array($remoteAddress, $trustedProxies, true);

        if ($canTrustProxyHeaders) {
            foreach (['HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'HTTP_X_REAL_IP'] as $header) {
                $ip = self::firstValidIp((string) ($server[$header] ?? ''));
                if ($ip !== null) {
                    return $ip;
                }
            }
        }

        return $remoteAddress ?? '127.0.0.1';
    }

    private static function firstValidIp(string $raw): ?string
    {
        if ($raw === '' || strtolower($raw) === 'unknown') {
            return null;
        }

        foreach (explode(',', $raw) as $candidate) {
            $ip = trim($candidate);
            if ($ip !== '' && strtolower($ip) !== 'unknown' && filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private static function trustedProxies(): array
    {
        $raw = (string) ($_ENV['TRUSTED_PROXIES'] ?? getenv('TRUSTED_PROXIES') ?: '');

        $proxies = [];
        foreach (explode(',', $raw) as $proxy) {
            $proxy = trim($proxy);
            if ($proxy !== '' && filter_var($proxy, FILTER_VALIDATE_IP)) {
                $proxies[] = $proxy;
            }
        }

        return $proxies;
    }
}
