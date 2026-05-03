<?php
declare(strict_types=1);

namespace Pokemon8\Security;

final class Csrf
{
    public function __construct(private Session $session)
    {
    }

    /**
     * Р’РѕР·РІСЂР°С‰Р°РµС‚ С‚РѕРєРµРЅ С„РѕСЂРјС‹. Р•СЃР»Рё С‚РѕРєРµРЅР° РЅРµС‚, СЃРѕР·РґР°РµС‚ РєСЂРёРїС‚РѕСЃС‚РѕР№РєРёР№ РЅРѕРІС‹Р№.
     */
    public function token(): string
    {
        $token = $this->session->get('_csrf');
        if (is_string($token) && $token !== '') {
            return $token;
        }

        $token = bin2hex(random_bytes(32));
        $this->session->put('_csrf', $token);

        return $token;
    }

    /**
     * РџСЂРѕРІРµСЂСЏРµС‚, С‡С‚Рѕ POST-Р·Р°РїСЂРѕСЃ РїСЂРёС€РµР» РёР· С„РѕСЂРјС‹ РЅР°С€РµРіРѕ СЃР°Р№С‚Р°.
     */
    public function validate(?string $token): bool
    {
        $known = $this->session->get('_csrf');
        return is_string($known) && is_string($token) && hash_equals($known, $token);
    }
}
