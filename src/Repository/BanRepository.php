<?php
declare(strict_types=1);

namespace Pokemon8\Repository;

use PDO;

final class BanRepository
{
    public function __construct(private PDO $db)
    {
    }

    /**
     * РџСЂРѕРІРµСЂСЏРµС‚ РЅР°Р»РёС‡РёРµ IP РІ С‚Р°Р±Р»РёС†Рµ banip.
     */
    public function isIpBanned(string $ip): bool
    {
        $stmt = $this->db->prepare('SELECT ip FROM banip WHERE ip = :ip LIMIT 1');
        $stmt->execute(['ip' => $ip]);

        return (bool) $stmt->fetch();
    }
}
