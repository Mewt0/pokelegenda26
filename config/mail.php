<?php
declare(strict_types=1);

return [
    'enabled' => filter_var($_ENV['MAIL_ENABLED'] ?? 'true', FILTER_VALIDATE_BOOL),
    'from_address' => $_ENV['MAIL_FROM_ADDRESS'] ?? 'no-reply@pokemonchic.local',
    'from_name' => $_ENV['MAIL_FROM_NAME'] ?? 'Pokemon 8.0',
    'reset_ttl_minutes' => (int) ($_ENV['PASSWORD_RESET_TTL_MINUTES'] ?? 60),
    'verify_ttl_minutes' => (int) ($_ENV['EMAIL_VERIFY_TTL_MINUTES'] ?? 1440),
];
