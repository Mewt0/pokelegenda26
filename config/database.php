<?php
declare(strict_types=1);

return [
    'host' => $_ENV['DB_HOST'] ?? $_ENV['server'] ?? '127.0.0.1',
    'port' => (int) ($_ENV['DB_PORT'] ?? 3306),
    'database' => $_ENV['DB_DATABASE'] ?? $_ENV['db'] ?? 'pokemon',
    'username' => $_ENV['DB_USER'] ?? $_ENV['user'] ?? 'pokemon',
    'password' => $_ENV['DB_PASS'] ?? $_ENV['pass'] ?? 'pokemon',
    'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
];
