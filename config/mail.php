<?php
declare(strict_types=1);

return [
    'enabled' => filter_var($_ENV['MAIL_ENABLED'] ?? 'true', FILTER_VALIDATE_BOOL),
    'transport' => strtolower((string) ($_ENV['MAIL_TRANSPORT'] ?? 'mail')),
    'from_address' => $_ENV['MAIL_FROM_ADDRESS'] ?? 'no-reply@pokemonchic.local',
    'from_name' => $_ENV['MAIL_FROM_NAME'] ?? 'Pokemon 8.0',
    'smtp_host' => $_ENV['SMTP_HOST'] ?? $_ENV['MAIL_HOST'] ?? '127.0.0.1',
    'smtp_port' => (int) ($_ENV['SMTP_PORT'] ?? $_ENV['MAIL_PORT'] ?? 1025),
    'smtp_username' => $_ENV['SMTP_USERNAME'] ?? $_ENV['MAIL_USERNAME'] ?? '',
    'smtp_password' => $_ENV['SMTP_PASSWORD'] ?? $_ENV['MAIL_PASSWORD'] ?? '',
    'smtp_encryption' => strtolower((string) ($_ENV['SMTP_ENCRYPTION'] ?? $_ENV['MAIL_ENCRYPTION'] ?? '')),
    'smtp_timeout' => (int) ($_ENV['SMTP_TIMEOUT'] ?? 10),
    'reset_ttl_minutes' => (int) ($_ENV['PASSWORD_RESET_TTL_MINUTES'] ?? 60),
    'verify_ttl_minutes' => (int) ($_ENV['EMAIL_VERIFY_TTL_MINUTES'] ?? 1440),
];
