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
     * Проверяет IP до роутинга.
     * Р•СЃР»Рё IP Р·Р°Р±Р°РЅРµРЅ, РІРѕР·РІСЂР°С‰Р°РµС‚ РіРѕС‚РѕРІС‹Р№ 403 Response; РµСЃР»Рё РЅРµС‚ - РїСЂРѕРїСѓСЃРєР°РµС‚ Р·Р°РїСЂРѕСЃ РґР°Р»СЊС€Рµ.
     */
    public function check(Request $request): ?Response
    {
        $ip = ClientIp::fromServer($request->server);
        if (!$this->bans->isIpBanned($ip)) {
            return null;
        }

        return new Response(
            '<h1>Доступ закрыт</h1><p>Ваш IP адрес был заблокирован за многократные нарушения правил.</p>',
            403
        );
    }
}
