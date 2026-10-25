<?php
declare(strict_types=1);

/**
 * Security headers for the public front controllers.
 *
 * CSP is intentionally strict on scripts: the application loads external
 * self-hosted JS only and no inline event handlers are used. Inline styles are
 * still allowed because views and generated cards use style="" attributes.
 */
function app_send_security_headers(): void
{
    if (PHP_SAPI === 'cli' || headers_sent()) {
        return;
    }

    // Only the team panel is embedded by the same-origin game shell.
    $teamPanel = parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH) === '/game/pokemon';
    $frameAncestors = $teamPanel ? "'self'" : "'none'";
    header('X-Frame-Options: ' . ($teamPanel ? 'SAMEORIGIN' : 'DENY'));
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: same-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    header(
        "Content-Security-Policy: default-src 'self'; "
        . "base-uri 'self'; object-src 'none'; frame-ancestors {$frameAncestors}; form-action 'self'; "
        . "script-src 'self'; "
        . "style-src 'self' 'unsafe-inline'; "
        . "img-src 'self' data:; font-src 'self'; connect-src 'self'; "
        . "media-src 'self'; worker-src 'self'; manifest-src 'self'"
    );
}
