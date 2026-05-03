<?php
declare(strict_types=1);

namespace Pokemon8\Support;

final class Env
{
    public static function load(string $path): void
    {
        // Р СљР С‘Р Р…Р С‘Р СР В°Р В»РЎРЉР Р…РЎвЂ№Р в„– .env-loader Р В±Р ВµР В· РЎРѓРЎвЂљР С•РЎР‚Р С•Р Р…Р Р…Р С‘РЎвЂ¦ Р В·Р В°Р Р†Р С‘РЎРѓР С‘Р СР С•РЎРѓРЎвЂљР ВµР в„–: Р С—Р С•Р Т‘РЎвЂ¦Р С•Р Т‘Р С‘РЎвЂљ Р Т‘Р С• Р С—Р С•Р Т‘Р С”Р В»РЎР‹РЎвЂЎР ВµР Р…Р С‘РЎРЏ Composer-Р С—Р В°Р С”Р ВµРЎвЂљР С•Р Р†.
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
