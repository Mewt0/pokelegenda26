<?php
declare(strict_types=1);

namespace Pokemon8\Security;

use Pokemon8\Http\Request;
use Pokemon8\Http\Response;
use Pokemon8\Repository\BanRepository;
use Pokemon8\Support\ClientIp;

final class BanGuard
{
    public function __construct(private BanRepository $bans)
    {
    }

    /**
     * РџСЂРѕРІРµСЂСЏРµС‚ IP РґРѕ СЂРѕСѓС‚РёРЅРіР°.
     * Р•СЃР»Рё IP Р·Р°Р±Р°РЅРµРЅ, РІРѕР·РІСЂР°С‰Р°РµС‚ РіРѕС‚РѕРІС‹Р№ 403 Response; РµСЃР»Рё РЅРµС‚ - РїСЂРѕРїСѓСЃРєР°РµС‚ Р·Р°РїСЂРѕСЃ РґР°Р»СЊС€Рµ.
     */
    public function check(Request $request): ?Response
    {
        $ip = ClientIp::fromServer($request->server);
        if (!$this->bans->isIpBanned($ip)) {
            return null;
        }

        return new Response(
            '<h1>Р”РѕСЃС‚СѓРї Р·Р°РєСЂС‹С‚</h1><p>Р’Р°С€ IP Р°РґСЂРµСЃ Р±С‹Р» Р·Р°Р±Р»РѕРєРёСЂРѕРІР°РЅ Р·Р° РјРЅРѕРіРѕРєСЂР°С‚РЅС‹Рµ РЅР°СЂСѓС€РµРЅРёСЏ РїСЂР°РІРёР».</p>',
            403
        );
    }
}
