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
     * Проверяет наличие IP в таблице banip.
     *
     * Колонка `banip.ip` целочисленная: `ChatRepository::banIpForUser()` пишет туда
     * `(int) $target['ip']`, а legacy сравнивает через `WHERE ip=%d`. Прежняя версия
     * подставляла точечную запись ('1.2.3.4'), MySQL приводил её к 0, поэтому бан по IP
     * не срабатывал никогда. Принимаем и точечную, и уже числовую форму.
     */
    public function isIpBanned(string $ip): bool
    {
        $ip = trim($ip);
        if ($ip === '') {
            return false;
        }

        $numericIp = str_contains($ip, '.') ? (int) sprintf('%u', ip2long($ip)) : (int) $ip;

        $stmt = $this->db->prepare('SELECT ip FROM banip WHERE ip = :ip LIMIT 1');
        $stmt->execute(['ip' => $numericIp]);

        return (bool) $stmt->fetch();
    }
}
