<?php
declare(strict_types=1);

namespace Pokemon8\Controller;

use Pokemon8\Http\Request;
use Pokemon8\Http\Response;
use Pokemon8\Repository\RankingRepository;
use Pokemon8\Security\Csrf;
use Pokemon8\Security\Session;
use Pokemon8\View\View;

final class HomeController
{
    public function __construct(
        private RankingRepository $rankings,
        private Session $session,
        private Csrf $csrf,
    ) {
    }

    public function index(Request $request): Response
    {
        // Р“Р»Р°РІРЅР°СЏ РїРѕРєР° РїРѕРєР°Р·С‹РІР°РµС‚ СЃС‚Р°СЂС‚РѕРІС‹Р№ СЌРєСЂР°РЅ РЅРѕРІРѕРіРѕ СЏРґСЂР° Рё СЂРµР№С‚РёРЅРіРё РёР· СЃС‚Р°СЂРѕР№ Р‘Р”.
        $body = View::render('home', [
            'userLogin' => $this->session->get('login'),
            'csrfToken' => $this->csrf->token(),
            'fighters' => $this->rankings->fighters(),
            'pokedex' => $this->rankings->pokedex(),
        ]);

        return new Response($body);
    }
}
