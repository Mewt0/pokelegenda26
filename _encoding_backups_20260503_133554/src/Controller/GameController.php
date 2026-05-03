<?php
declare(strict_types=1);

namespace Pokemon8\Controller;

use Pokemon8\Http\Request;
use Pokemon8\Http\Response;
use Pokemon8\Security\Session;
use Pokemon8\View\View;

final readonly class GameController
{
    public function __construct(private Session $session)
    {
    }

    public function start(Request $request): Response
    {
        // Р СџР С•Р С”Р В° РЎРЊРЎвЂљР С• Р В±Р ВµР В·Р С•Р С—Р В°РЎРѓР Р…Р В°РЎРЏ РЎРѓРЎвЂљР В°РЎР‚РЎвЂљР С•Р Р†Р В°РЎРЏ Р В·Р В°Р С–Р В»РЎС“РЎв‚¬Р С”Р В°. Р С™Р В°РЎР‚РЎвЂљР В°, РЎвЂЎР В°РЎвЂљ Р С‘ Р В±Р С•Р в„– Р В±РЎС“Р Т‘РЎС“РЎвЂљ Р С•РЎвЂљР Т‘Р ВµР В»РЎРЉР Р…РЎвЂ№Р СР С‘ Р С”Р С•Р Р…РЎвЂљРЎР‚Р С•Р В»Р В»Р ВµРЎР‚Р В°Р СР С‘.
        if (!$this->session->get('id')) {
            return Response::redirect('/');
        }

        return new Response(View::render('game-start', [
            'login' => $this->session->get('login'),
        ]));
    }
}
