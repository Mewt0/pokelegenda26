<?php
declare(strict_types=1);

namespace Pokemon8\Security;

use Pokemon8\Http\Request;
use Pokemon8\Http\Response;
use Pokemon8\Repository\BanRepository;
use Pokemon8\Support\ClientIp;

final readonly class BanGuard
{
    public function __construct(private BanRepository $bans)
    {
    }

    /**
     * Р СџРЎР‚Р С•Р Р†Р ВµРЎР‚РЎРЏР ВµРЎвЂљ IP Р Т‘Р С• РЎР‚Р С•РЎС“РЎвЂљР С‘Р Р…Р С–Р В°.
     * Р вЂўРЎРѓР В»Р С‘ IP Р В·Р В°Р В±Р В°Р Р…Р ВµР Р…, Р Р†Р С•Р В·Р Р†РЎР‚Р В°РЎвЂ°Р В°Р ВµРЎвЂљ Р С–Р С•РЎвЂљР С•Р Р†РЎвЂ№Р в„– 403 Response; Р ВµРЎРѓР В»Р С‘ Р Р…Р ВµРЎвЂљ - Р С—РЎР‚Р С•Р С—РЎС“РЎРѓР С”Р В°Р ВµРЎвЂљ Р В·Р В°Р С—РЎР‚Р С•РЎРѓ Р Т‘Р В°Р В»РЎРЉРЎв‚¬Р Вµ.
     */
    public function check(Request $request): ?Response
    {
        $ip = ClientIp::fromServer($request->server);
        if (!$this->bans->isIpBanned($ip)) {
            return null;
        }

        return new Response(
            '<h1>Р вЂќР С•РЎРѓРЎвЂљРЎС“Р С— Р В·Р В°Р С”РЎР‚РЎвЂ№РЎвЂљ</h1><p>Р вЂ™Р В°РЎв‚¬ IP Р В°Р Т‘РЎР‚Р ВµРЎРѓ Р В±РЎвЂ№Р В» Р В·Р В°Р В±Р В»Р С•Р С”Р С‘РЎР‚Р С•Р Р†Р В°Р Р… Р В·Р В° Р СР Р…Р С•Р С–Р С•Р С”РЎР‚Р В°РЎвЂљР Р…РЎвЂ№Р Вµ Р Р…Р В°РЎР‚РЎС“РЎв‚¬Р ВµР Р…Р С‘РЎРЏ Р С—РЎР‚Р В°Р Р†Р С‘Р В».</p>',
            403
        );
    }
}
