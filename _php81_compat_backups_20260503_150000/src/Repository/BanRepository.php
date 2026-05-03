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
     * Проверяет наличие IP в таблице banip.
     */
    public function isIpBanned(string $ip): bool
    {
        $stmt = $this->db->prepare('SELECT ip FROM banip WHERE ip = :ip LIMIT 1');
        $stmt->execute(['ip' => $ip]);

        return (bool) $stmt->fetch();
    }
}
