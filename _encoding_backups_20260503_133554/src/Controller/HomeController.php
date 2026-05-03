<?php
declare(strict_types=1);

namespace Pokemon8\Controller;

use Pokemon8\Http\Request;
use Pokemon8\Http\Response;
use Pokemon8\Repository\RankingRepository;
use Pokemon8\Security\Csrf;
use Pokemon8\Security\Session;
use Pokemon8\View\View;

final readonly class HomeController
{
    public function __construct(
        private RankingRepository $rankings,
        private Session $session,
        private Csrf $csrf,
    ) {
    }

    public function index(Request $request): Response
    {
        // Р вЂњР В»Р В°Р Р†Р Р…Р В°РЎРЏ Р С—Р С•Р С”Р В° Р С—Р С•Р С”Р В°Р В·РЎвЂ№Р Р†Р В°Р ВµРЎвЂљ РЎРѓРЎвЂљР В°РЎР‚РЎвЂљР С•Р Р†РЎвЂ№Р в„– РЎРЊР С”РЎР‚Р В°Р Р… Р Р…Р С•Р Р†Р С•Р С–Р С• РЎРЏР Т‘РЎР‚Р В° Р С‘ РЎР‚Р ВµР в„–РЎвЂљР С‘Р Р…Р С–Р С‘ Р С‘Р В· РЎРѓРЎвЂљР В°РЎР‚Р С•Р в„– Р вЂР вЂќ.
        $body = View::render('home', [
            'userLogin' => $this->session->get('login'),
            'csrfToken' => $this->csrf->token(),
            'fighters' => $this->rankings->fighters(),
            'pokedex' => $this->rankings->pokedex(),
        ]);

        return new Response($body);
    }
}
