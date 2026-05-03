<?php
declare(strict_types=1);

namespace Pokemon8\Security;

final class PasswordHasher
{
    /**
     * Создает современный хэш пароля.
     * Алгоритм выбирает PHP через PASSWORD_DEFAULT, поэтому проект спокойно переживет будущие обновления PHP.
     */
    public function hash(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Старый формат пароля из текущей базы.
     * Оставлен только для плавного входа старых игроков и последующей миграции.
     */
    public function legacyHash(string $password): string
    {
        return strrev(md5($password)) . 'b3p6f';
    }

    /**
     * Проверяет и новый password_hash(), и старый md5+salt формат.
     */
    public function verify(string $password, string $storedHash): bool
    {
        if (str_starts_with($storedHash, '$')) {
            return password_verify($password, $storedHash);
        }

        return $this->verifyLegacy($password, $storedHash);
    }

    /**
     * Говорит, нужно ли пересохранить пароль в новом формате.
     */
    public function needsRehash(string $storedHash): bool
    {
        if (!str_starts_with($storedHash, '$')) {
            return true;
        }

        return password_needs_rehash($storedHash, PASSWORD_DEFAULT);
    }

    /**
     * Совместимость со старой базой.
     */
    public function verifyLegacy(string $password, string $hash): bool
    {
        return hash_equals($hash, $this->legacyHash($password));
    }
}
