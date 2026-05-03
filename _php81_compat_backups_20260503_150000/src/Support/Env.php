<?php
declare(strict_types=1);

namespace Pokemon8\Support;

final class Env
{
    public static function load(string $path): void
    {
        // Минимальный .env-loader без сторонних зависимостей: подходит до подключения Composer-пакетов.
        if (!is_file($path) || !is_readable($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = array_map('trim', explode('=', $line, 2));
            if ($key === '') {
                continue;
            }

            $value = trim($value, "\"'");
            $_ENV[$key] = $_ENV[$key] ?? $value;
            $_SERVER[$key] = $_SERVER[$key] ?? $value;

            if (getenv($key) === false) {
                putenv($key . '=' . $value);
            }
        }
    }
}
