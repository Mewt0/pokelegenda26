<?php
declare(strict_types=1);

namespace Pokemon8\Controller;

use Pokemon8\Http\Request;
use Pokemon8\Http\Response;
use Pokemon8\Security\Csrf;
use Pokemon8\Security\Session;
use Pokemon8\View\View;

final class GameController
{
    public function __construct(
        private Session $session,
        private Csrf $csrf,
    ) {
    }

    public function start(Request $request): Response
    {
        // РџРѕРєР° СЌС‚Рѕ Р±РµР·РѕРїР°СЃРЅР°СЏ СЃС‚Р°СЂС‚РѕРІР°СЏ Р·Р°РіР»СѓС€РєР°. РљР°СЂС‚Р°, С‡Р°С‚ Рё Р±РѕР№ Р±СѓРґСѓС‚ РѕС‚РґРµР»СЊРЅС‹РјРё РєРѕРЅС‚СЂРѕР»Р»РµСЂР°РјРё.
        if (!$this->session->get('id')) {
            return Response::redirect('/');
        }

        return new Response(View::render('game-start', [
            'login' => $this->session->get('login'),
            'csrf' => $this->csrf->token(),
        ]));
    }
}
