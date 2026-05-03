<?php
declare(strict_types=1);

namespace Pokemon8\Repository;

use PDO;

final class LocationRepository
{
    public function __construct(private PDO $db)
    {
    }

    public function findUserState(int $userId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, login, buildmy, pvp, pve, trade, pve_button FROM users WHERE id = :id AND activation = 1 LIMIT 1'
        );
        $stmt->execute(['id' => $userId]);

        return $stmt->fetch() ?: null;
    }

    public function touchOnlineHeartbeat(int $userId): void
    {
        $stmt = $this->db->prepare(
            'UPDATE users SET online = 1, onlinetime = :time WHERE id = :id LIMIT 1'
        );
        $stmt->execute([
            'time' => time(),
            'id' => $userId,
        ]);
    }

    public function updateUserLocation(int $userId, int $locationId): void
    {
        $stmt = $this->db->prepare('UPDATE users SET buildmy = :location WHERE id = :id LIMIT 1');
        $stmt->execute([
            'location' => $locationId,
            'id' => $userId,
        ]);
    }

    public function findLocation(int $locationId): ?array
    {
        $stmt = $this->db->prepare('SELECT id, title, tipe FROM build WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $locationId]);

        return $stmt->fetch() ?: null;
    }

    /**
     * @param list<int> $ids
     * @return array<int, array{id:int,title:string,tipe:int}>
     */
    public function findLocationsByIds(array $ids): array
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if ($ids === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare('SELECT id, title, tipe FROM build WHERE id IN (' . $placeholders . ')');
        $stmt->execute($ids);

        $locations = [];
        foreach ($stmt->fetchAll() as $row) {
            $locations[(int) $row['id']] = [
                'id' => (int) $row['id'],
                'title' => (string) $row['title'],
                'tipe' => (int) ($row['tipe'] ?? 0),
            ];
        }

        return $locations;
    }

    /**
     * @return list<array{id:int,login:string,online:bool,pveButton:bool,pvp:bool,trade:bool}>
     */
    public function usersAtLocation(int $locationId): array
    {
        $onlineSince = time() - 300;
        $stmt = $this->db->prepare(
            'SELECT id, login, online, onlinetime, pve_button, pvp, trade
               FROM users
              WHERE buildmy = :location
                AND activation = 1
                AND online = 1
                AND onlinetime >= :online_since
              ORDER BY login ASC
              LIMIT 80'
        );
        $stmt->execute([
            'location' => $locationId,
            'online_since' => $onlineSince,
        ]);

        $users = [];
        foreach ($stmt->fetchAll() as $row) {
            $users[] = [
                'id' => (int) $row['id'],
                'login' => (string) $row['login'],
                'online' => true,
                'pveButton' => (int) $row['pve_button'] === 1,
                'pvp' => (int) $row['pvp'] === 1,
                'trade' => (int) $row['trade'] > 0,
            ];
        }

        return $users;
    }

    public function setPveButton(int $userId, bool $enabled): void
    {
        if ($enabled) {
            $stmt = $this->db->prepare(
                'UPDATE users SET pve_button = 1, atack_poke = :attack_time WHERE id = :id LIMIT 1'
            );
            $stmt->execute([
                'attack_time' => time() + 15,
                'id' => $userId,
            ]);
            return;
        }

        $stmt = $this->db->prepare(
            'UPDATE users SET pve_button = 0 WHERE id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $userId]);
    }
}
