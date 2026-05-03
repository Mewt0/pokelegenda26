<?php
declare(strict_types=1);

namespace Pokemon8\Repository;

use PDO;

final readonly class BanRepository
{
    public function __construct(private PDO $db)
    {
    }

    /**
     * Р СџРЎР‚Р С•Р Р†Р ВµРЎР‚РЎРЏР ВµРЎвЂљ Р Р…Р В°Р В»Р С‘РЎвЂЎР С‘Р Вµ IP Р Р† РЎвЂљР В°Р В±Р В»Р С‘РЎвЂ Р Вµ banip.
     */
    public function isIpBanned(string $ip): bool
    {
        $stmt = $this->db->prepare('SELECT ip FROM banip WHERE ip = :ip LIMIT 1');
        $stmt->execute(['ip' => $ip]);

        return (bool) $stmt->fetch();
    }
}
