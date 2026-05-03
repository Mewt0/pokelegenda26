<?php
declare(strict_types=1);

return [
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? 'false', FILTER_VALIDATE_BOOL),
    'name' => $_ENV['APP_NAME'] ?? 'Pokemon 8.0',
    'url' => rtrim($_ENV['APP_URL'] ?? 'http://127.0.0.1:8000', '/'),
    'timezone' => $_ENV['APP_TIMEZONE'] ?? 'Europe/Prague',
    'run' => (int) ($_ENV['APP_RUN'] ?? $_ENV['run'] ?? 1),
    'techwork' => (string) ($_ENV['APP_TECHWORK'] ?? $_ENV['techwork'] ?? '0'),
    'session_name' => $_ENV['APP_SESSION_NAME'] ?? 'pokemon8_session',
];
