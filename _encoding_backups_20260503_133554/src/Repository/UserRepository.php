<?php
declare(strict_types=1);

namespace Pokemon8\Repository;

use PDO;

final readonly class UserRepository
{
    public function __construct(private PDO $db)
    {
    }

    /**
     * Р ВРЎвЂ°Р ВµРЎвЂљ Р В°Р С”РЎвЂљР С‘Р Р†Р Р…Р С•Р С–Р С• Р С—Р С•Р В»РЎРЉР В·Р С•Р Р†Р В°РЎвЂљР ВµР В»РЎРЏ Р С—Р С• Р В»Р С•Р С–Р С‘Р Р…РЎС“.
     * Р СџР В°РЎР‚Р С•Р В»РЎРЉ Р С—РЎР‚Р С•Р Р†Р ВµРЎР‚РЎРЏР ВµРЎвЂљРЎРѓРЎРЏ Р С•РЎвЂљР Т‘Р ВµР В»РЎРЉР Р…Р С• Р Р† PasswordHasher, РЎвЂЎРЎвЂљР С•Р В±РЎвЂ№ РЎР‚Р ВµР С—Р С•Р В·Р С‘РЎвЂљР С•РЎР‚Р С‘Р в„– Р Р…Р Вµ Р В·Р Р…Р В°Р В» Р С—РЎР‚Р С• Р В°Р В»Р С–Р С•РЎР‚Р С‘РЎвЂљР СРЎвЂ№ РЎвЂ¦РЎРЊРЎв‚¬Р С‘РЎР‚Р С•Р Р†Р В°Р Р…Р С‘РЎРЏ.
     */
    public function findActiveByLogin(string $login): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, login, password, groups, activation FROM users WHERE login = :login AND activation = 1 LIMIT 1'
        );
        $stmt->execute(['login' => $login]);

        return $stmt->fetch() ?: null;
    }

    /**
     * Р вЂР ВµРЎР‚Р ВµРЎвЂљ РЎвЂљР С•Р В»РЎРЉР С”Р С• РЎРѓР С•РЎРѓРЎвЂљР С•РЎРЏР Р…Р С‘Р Вµ Р В°Р С”Р С”Р В°РЎС“Р Р…РЎвЂљР В°: Р С–РЎР‚РЎС“Р С—Р С—Р В° Р С‘ Р В±Р В»Р С•Р С”Р С‘РЎР‚Р С•Р Р†Р С”Р В°.
     */
    public function findStateByLogin(string $login): ?array
    {
        $stmt = $this->db->prepare('SELECT id, activation, groups FROM users WHERE login = :login LIMIT 1');
        $stmt->execute(['login' => $login]);

        return $stmt->fetch() ?: null;
    }

    /**
     * Р СџР С•Р СР ВµРЎвЂЎР В°Р ВµРЎвЂљ Р С—Р С•Р В»РЎРЉР В·Р С•Р Р†Р В°РЎвЂљР ВµР В»РЎРЏ Р С•Р Р…Р В»Р В°Р в„–Р Р… Р С—Р С•РЎРѓР В»Р Вµ РЎС“РЎРѓР С—Р ВµРЎв‚¬Р Р…Р С•Р С–Р С• Р Р†РЎвЂ¦Р С•Р Т‘Р В°.
     */
    public function markOnline(int $id, string $ip): void
    {
        $stmt = $this->db->prepare('UPDATE users SET online = 1, onlinetime = :time, ip = :ip WHERE id = :id');
        $stmt->execute([
            'time' => time(),
            'ip' => ip2long($ip) ?: 0,
            'id' => $id,
        ]);
    }

    /**
     * Р СџР ВµРЎР‚Р ВµРЎРѓР С•РЎвЂ¦РЎР‚Р В°Р Р…РЎРЏР ВµРЎвЂљ Р С—Р В°РЎР‚Р С•Р В»РЎРЉ Р Р† РЎРѓР С•Р Р†РЎР‚Р ВµР СР ВµР Р…Р Р…Р С•Р С РЎвЂћР С•РЎР‚Р СР В°РЎвЂљР Вµ Р С—Р С•РЎРѓР В»Р Вµ РЎС“РЎРѓР С—Р ВµРЎв‚¬Р Р…Р С•Р в„– Р С—РЎР‚Р С•Р Р†Р ВµРЎР‚Р С”Р С‘ РЎРѓРЎвЂљР В°РЎР‚Р С•Р С–Р С• РЎвЂ¦РЎРЊРЎв‚¬Р В°.
     */
    public function updatePasswordHash(int $id, string $hash): void
    {
        $stmt = $this->db->prepare('UPDATE users SET password = :password WHERE id = :id');
        $stmt->execute(['password' => $hash, 'id' => $id]);
    }
}
