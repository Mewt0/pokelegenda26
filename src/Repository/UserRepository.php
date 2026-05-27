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
            'SELECT id, login, password, groups, activation, email, email_verified_at FROM users WHERE login = :login AND activation = 1 LIMIT 1'
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

    public function loginExists(string $login): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM users WHERE LOWER(login) = LOWER(:login) LIMIT 1');
        $stmt->execute(['login' => $login]);
        return (bool) $stmt->fetchColumn();
    }

    public function emailExists(string $email, int $exceptUserId = 0): bool
    {
        $email = $this->normalizeEmail($email);
        if ($email === null) {
            return false;
        }

        $sql = 'SELECT 1 FROM users WHERE LOWER(email) = LOWER(:email)';
        $params = ['email' => $email];
        if ($exceptUserId > 0) {
            $sql .= ' AND id != :except';
            $params['except'] = $exceptUserId;
        }
        $sql .= ' LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (bool) $stmt->fetchColumn();
    }

    public function findActiveByEmail(string $email): ?array
    {
        $email = $this->normalizeEmail($email);
        if ($email === null) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT id, login, password, groups, activation, email, email_verified_at
               FROM users
              WHERE LOWER(email) = LOWER(:email)
                AND activation = 1
              ORDER BY email_verified_at DESC, id ASC
              LIMIT 1'
        );
        $stmt->execute(['email' => $email]);
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

    public function createUser(string $login, string $passwordHash, ?string $email, string $ip): array
    {
        $email = $this->normalizeEmail($email ?? '');
        $now = time();
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO users
                    (login, password, email, email_verified_at, online, onlinetime, activation, groups, moderation, police,
                     datereg, avatars, clanid, clan_point, clan_adm, pve, pvp, trade, info, gender, ip, rang,
                     rang_a, rang_b, rang_c, karma_score, count_poke, count_poke_s, buildmy, mychat, newuser,
                     battleid, pve_button, atack_poke, status_klan, youtuber, prefics, timepoke)
                 VALUES
                    (:login, :password, :email, 0, 0, :online_time, 1, 6, 0, 0,
                     :datereg, 1, 0, 0, 0, 0, 0, 0, :info, 1, :ip, :rang,
                     0, 0, 0, 0, 0, 0, 1, 1, :newuser,
                     0, 0, 0, "", 0, 0, 0)'
            );
            $stmt->execute([
                'login' => $login,
                'password' => $passwordHash,
                'email' => $email,
                'online_time' => $now,
                'datereg' => date('Y-m-d H:i:s', $now),
                'info' => '...',
                'ip' => ip2long($ip) ?: 0,
                'rang' => 'Новичок',
                'newuser' => $now + 86400,
            ]);

            $userId = (int) $this->db->lastInsertId();
            $this->ensureUserUniqueRow($userId);
            $this->ensureInformationUser($userId);
            $this->db->commit();
            return ['id' => $userId, 'login' => $login, 'email' => $email];
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function updateEmail(int $userId, ?string $email, bool $verified = false): void
    {
        $stmt = $this->db->prepare(
            'UPDATE users
                SET email = :email, email_verified_at = :verified
              WHERE id = :id
              LIMIT 1'
        );
        $stmt->execute([
            'email' => $this->normalizeEmail($email ?? ''),
            'verified' => $verified ? time() : 0,
            'id' => $userId,
        ]);
    }

    public function createPasswordResetToken(int $userId, string $email, string $tokenHash, int $expiresAt, string $ip): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO password_reset_tokens (user_id, email, token_hash, expires_at, used_at, request_ip, created_at)
             VALUES (:user, :email, :token, :expires, 0, :ip, :created)'
        );
        $stmt->execute([
            'user' => $userId,
            'email' => $email,
            'token' => $tokenHash,
            'expires' => $expiresAt,
            'ip' => ip2long($ip) ?: 0,
            'created' => time(),
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function findPasswordResetToken(string $tokenHash): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT prt.*, u.login, u.activation
               FROM password_reset_tokens prt
               INNER JOIN users u ON u.id = prt.user_id
              WHERE prt.token_hash = :token
              LIMIT 1'
        );
        $stmt->execute(['token' => $tokenHash]);
        return $stmt->fetch() ?: null;
    }

    public function markPasswordResetTokenUsed(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE password_reset_tokens SET used_at = :used WHERE id = :id LIMIT 1');
        $stmt->execute(['used' => time(), 'id' => $id]);
    }

    public function createEmailVerificationToken(int $userId, string $email, string $tokenHash, int $expiresAt, string $ip): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO email_verification_tokens (user_id, email, token_hash, expires_at, used_at, request_ip, created_at)
             VALUES (:user, :email, :token, :expires, 0, :ip, :created)'
        );
        $stmt->execute([
            'user' => $userId,
            'email' => $email,
            'token' => $tokenHash,
            'expires' => $expiresAt,
            'ip' => ip2long($ip) ?: 0,
            'created' => time(),
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function findEmailVerificationToken(string $tokenHash): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT evt.*, u.login, u.activation
               FROM email_verification_tokens evt
               INNER JOIN users u ON u.id = evt.user_id
              WHERE evt.token_hash = :token
              LIMIT 1'
        );
        $stmt->execute(['token' => $tokenHash]);
        return $stmt->fetch() ?: null;
    }

    public function markEmailVerificationTokenUsed(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE email_verification_tokens SET used_at = :used WHERE id = :id LIMIT 1');
        $stmt->execute(['used' => time(), 'id' => $id]);
    }

    private function normalizeEmail(string $email): ?string
    {
        $email = trim(mb_strtolower($email, 'UTF-8'));
        return $email !== '' && $email !== 'none@mail.ru' ? $email : null;
    }

    private function ensureUserUniqueRow(int $userId): void
    {
        $exists = $this->db->prepare('SELECT id FROM usersunictable WHERE id = :id LIMIT 1');
        $exists->execute(['id' => $userId]);
        if ($exists->fetchColumn()) {
            return;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO usersunictable
                (id, almaz, vsegoalmaz, anserid, ansverotv, birthday, energi_opit, energi_cool,
                 smile, smiletime, molcha, arest, myday, coolday)
             VALUES
                (:id, 0, 0, 1, "", :birthday, 0, 0, 0, "not", 0, 0, :myday, 0)'
        );
        $stmt->execute([
            'id' => $userId,
            'birthday' => date('Y-m-d'),
            'myday' => date('Y-m-d'),
        ]);
    }

    private function ensureInformationUser(int $userId): void
    {
        $exists = $this->db->prepare('SELECT id FROM information_users WHERE users_id = :user LIMIT 1');
        $exists->execute(['user' => $userId]);
        if ($exists->fetchColumn()) {
            return;
        }

        $nextId = (int) $this->db->query('SELECT COALESCE(MAX(id), 0) + 1 FROM information_users')->fetchColumn();
        $stmt = $this->db->prepare('INSERT INTO information_users (id, users_id, admins_panels) VALUES (:id, :user, 0)');
        $stmt->execute(['id' => $nextId, 'user' => $userId]);
    }
}
