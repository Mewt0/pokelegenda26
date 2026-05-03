<?php
declare(strict_types=1);

namespace Pokemon8\Database;

use PDO;

final class Connection
{
    public static function make(array $config): PDO
    {
        // Р вЂ™РЎРѓР Вµ Р С—Р С•Р Т‘Р С”Р В»РЎР‹РЎвЂЎР ВµР Р…Р С‘РЎРЏ Р С” Р В±Р В°Р В·Р Вµ Р С—РЎР‚Р С•РЎвЂ¦Р С•Р Т‘РЎРЏРЎвЂљ РЎвЂЎР ВµРЎР‚Р ВµР В· PDO Р С‘ Р В±Р ВµРЎР‚РЎС“РЎвЂљ Р Т‘Р С•РЎРѓРЎвЂљРЎС“Р С—РЎвЂ№ РЎвЂљР С•Р В»РЎРЉР С”Р С• Р С‘Р В· config/database.php.
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['database'],
            $config['charset'],
        );

        return new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
}
