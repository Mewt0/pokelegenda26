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
        // Главная пока показывает стартовый экран нового ядра и рейтинги из старой БД.
        $body = View::render('home', [
            'userLogin' => $this->session->get('login'),
            'csrfToken' => $this->csrf->token(),
            'fighters' => $this->rankings->fighters(),
            'pokedex' => $this->rankings->pokedex(),
        ]);

        return new Response($body);
    }
}
