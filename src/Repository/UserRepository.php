<?php
declare(strict_types=1);

namespace Pokemon8\Repository;

use PDO;

final class UserRepository
{
    public function __construct(private PDO $db)
    {
    }

    /**
     * Ищет активного пользователя по логину.
     * Пароль проверяется отдельно в PasswordHasher, чтобы репозиторий не знал про алгоритмы хэширования.
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
     * Берет только состояние аккаунта: группа и блокировка.
     */
    public function findStateByLogin(string $login): ?array
    {
        $stmt = $this->db->prepare('SELECT id, activation, groups FROM users WHERE login = :login LIMIT 1');
        $stmt->execute(['login' => $login]);

        return $stmt->fetch() ?: null;
    }

    /**
     * Берет состояние аккаунта по id, чтобы проверять права для уже открытой сессии.
     */
    public function findStateById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT id, activation, groups FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);

        return $stmt->fetch() ?: null;
    }

    /**
     * Помечает пользователя онлайн после успешного входа.
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
     * Пересохраняет пароль в современном формате после успешной проверки старого хэша.
     */
    public function updatePasswordHash(int $id, string $hash): void
    {
        $stmt = $this->db->prepare('UPDATE users SET password = :password WHERE id = :id');
        $stmt->execute(['password' => $hash, 'id' => $id]);
    }
}
