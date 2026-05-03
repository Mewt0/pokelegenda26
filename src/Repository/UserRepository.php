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
     * РС‰РµС‚ Р°РєС‚РёРІРЅРѕРіРѕ РїРѕР»СЊР·РѕРІР°С‚РµР»СЏ РїРѕ Р»РѕРіРёРЅСѓ.
     * РџР°СЂРѕР»СЊ РїСЂРѕРІРµСЂСЏРµС‚СЃСЏ РѕС‚РґРµР»СЊРЅРѕ РІ PasswordHasher, С‡С‚РѕР±С‹ СЂРµРїРѕР·РёС‚РѕСЂРёР№ РЅРµ Р·РЅР°Р» РїСЂРѕ Р°Р»РіРѕСЂРёС‚РјС‹ С…СЌС€РёСЂРѕРІР°РЅРёСЏ.
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
     * Р‘РµСЂРµС‚ С‚РѕР»СЊРєРѕ СЃРѕСЃС‚РѕСЏРЅРёРµ Р°РєРєР°СѓРЅС‚Р°: РіСЂСѓРїРїР° Рё Р±Р»РѕРєРёСЂРѕРІРєР°.
     */
    public function findStateByLogin(string $login): ?array
    {
        $stmt = $this->db->prepare('SELECT id, activation, groups FROM users WHERE login = :login LIMIT 1');
        $stmt->execute(['login' => $login]);

        return $stmt->fetch() ?: null;
    }

    /**
     * РџРѕРјРµС‡Р°РµС‚ РїРѕР»СЊР·РѕРІР°С‚РµР»СЏ РѕРЅР»Р°Р№РЅ РїРѕСЃР»Рµ СѓСЃРїРµС€РЅРѕРіРѕ РІС…РѕРґР°.
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
     * РџРµСЂРµСЃРѕС…СЂР°РЅСЏРµС‚ РїР°СЂРѕР»СЊ РІ СЃРѕРІСЂРµРјРµРЅРЅРѕРј С„РѕСЂРјР°С‚Рµ РїРѕСЃР»Рµ СѓСЃРїРµС€РЅРѕР№ РїСЂРѕРІРµСЂРєРё СЃС‚Р°СЂРѕРіРѕ С…СЌС€Р°.
     */
    public function updatePasswordHash(int $id, string $hash): void
    {
        $stmt = $this->db->prepare('UPDATE users SET password = :password WHERE id = :id');
        $stmt->execute(['password' => $hash, 'id' => $id]);
    }
}
