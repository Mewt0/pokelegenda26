<?php
declare(strict_types=1);

namespace Pokemon8\Controller;

use Pokemon8\Http\Request;
use Pokemon8\Http\Response;
use Pokemon8\Repository\UserRepository;
use Pokemon8\Security\Csrf;
use Pokemon8\Security\PasswordHasher;
use Pokemon8\Security\Session;
use Pokemon8\Support\ClientIp;
use Pokemon8\View\View;

final readonly class AuthController
{
    public function __construct(
        private UserRepository $users,
        private PasswordHasher $passwords,
        private Session $session,
        private Csrf $csrf,
        private array $config,
    ) {
    }

    public function login(Request $request): Response
    {
        // CSRF Р Р…РЎС“Р В¶Р ВµР Р… Р Т‘Р В°Р В¶Р Вµ Р Т‘Р В»РЎРЏ Р В»Р С•Р С–Р С‘Р Р…Р В°: Р С‘Р Р…Р В°РЎвЂЎР Вµ РЎвЂћР С•РЎР‚Р СРЎС“ Р СР С•Р В¶Р Р…Р С• Р Т‘Р ВµРЎР‚Р С–Р В°РЎвЂљРЎРЉ РЎРѓ РЎвЂЎРЎС“Р В¶Р С•Р С–Р С• РЎРѓР В°Р в„–РЎвЂљР В°.
        if (!$this->csrf->validate($request->input('_csrf'))) {
            return $this->loginError('Р РЋР ВµРЎРѓРЎРѓР С‘РЎРЏ РЎвЂћР С•РЎР‚Р СРЎвЂ№ РЎС“РЎРѓРЎвЂљР В°РЎР‚Р ВµР В»Р В°. Р С›Р В±Р Р…Р С•Р Р†Р С‘ РЎРѓРЎвЂљРЎР‚Р В°Р Р…Р С‘РЎвЂ РЎС“ Р С‘ Р С—Р С•Р С—РЎР‚Р С•Р В±РЎС“Р в„– РЎРѓР Р…Р С•Р Р†Р В°.');
        }

        $login = $request->input('LOGIN');
        $password = $request->input('PASSWORD');

        if ($login === '' || $password === '') {
            return $this->loginError('Р вЂ”Р В°Р С—Р С•Р В»Р Р…Р С‘ Р В»Р С•Р С–Р С‘Р Р… Р С‘ Р С—Р В°РЎР‚Р С•Р В»РЎРЉ.');
        }

        if (!preg_match('/^[a-z_-]{3,16}$/i', $login)) {
            return $this->loginError('Р вЂєР С•Р С–Р С‘Р Р… Р Т‘Р С•Р В»Р В¶Р ВµР Р… Р В±РЎвЂ№РЎвЂљРЎРЉ 3-16 РЎРѓР С‘Р СР Р†Р С•Р В»Р С•Р Р†: Р В»Р В°РЎвЂљР С‘Р Р…Р С‘РЎвЂ Р В°, "_" Р С‘Р В»Р С‘ "-".');
        }

        $passwordLength = mb_strlen($password, 'UTF-8');
        if ($passwordLength < 6 || $passwordLength > 72) {
            return $this->loginError('Р СџР В°РЎР‚Р С•Р В»РЎРЉ Р Т‘Р С•Р В»Р В¶Р ВµР Р… Р В±РЎвЂ№РЎвЂљРЎРЉ Р С•РЎвЂљ 6 Р Т‘Р С• 72 РЎРѓР С‘Р СР Р†Р С•Р В»Р С•Р Р†.');
        }

        $state = $this->users->findStateByLogin($login);
        if ((int) ($this->config['techwork'] ?? 0) === 1 && (int) ($state['groups'] ?? 0) !== 1) {
            return $this->loginError('Р СњР В° РЎРѓР ВµРЎР‚Р Р†Р ВµРЎР‚Р Вµ Р С‘Р Т‘РЎС“РЎвЂљ РЎвЂљР ВµРЎвЂ¦Р Р…Р С‘РЎвЂЎР ВµРЎРѓР С”Р С‘Р Вµ РЎР‚Р В°Р В±Р С•РЎвЂљРЎвЂ№.');
        }

        if (($state['activation'] ?? null) !== null && (int) $state['activation'] === 0) {
            return $this->loginError('Р С’Р С”Р С”Р В°РЎС“Р Р…РЎвЂљ Р В·Р В°Р В±Р В»Р С•Р С”Р С‘РЎР‚Р С•Р Р†Р В°Р Р… Р В°Р Т‘Р СР С‘Р Р…Р С‘РЎРѓРЎвЂљРЎР‚Р В°РЎвЂ Р С‘Р ВµР в„– Р С—РЎР‚Р С•Р ВµР С”РЎвЂљР В°.');
        }

        $user = $this->users->findActiveByLogin($login);
        if ($user === null || !$this->passwords->verify($password, (string) $user['password'])) {
            return $this->loginError('Р вЂєР С•Р С–Р С‘Р Р… Р С‘Р В»Р С‘ Р С—Р В°РЎР‚Р С•Р В»РЎРЉ Р Р…Р ВµР Р†Р ВµРЎР‚Р Р…РЎвЂ№Р в„–.');
        }

        // Р вЂўРЎРѓР В»Р С‘ Р С—Р С•Р В»РЎРЉР В·Р С•Р Р†Р В°РЎвЂљР ВµР В»РЎРЉ Р Р†Р С•РЎв‚¬Р ВµР В» РЎРѓР С• РЎРѓРЎвЂљР В°РЎР‚РЎвЂ№Р С РЎвЂ¦РЎРЊРЎв‚¬Р ВµР С, РЎРѓРЎР‚Р В°Р В·РЎС“ Р СР С‘Р С–РЎР‚Р С‘РЎР‚РЎС“Р ВµР С Р С—Р В°РЎР‚Р С•Р В»РЎРЉ Р Р…Р В° РЎРѓР С•Р Р†РЎР‚Р ВµР СР ВµР Р…Р Р…РЎвЂ№Р в„– РЎвЂћР С•РЎР‚Р СР В°РЎвЂљ.
        if ($this->passwords->needsRehash((string) $user['password'])) {
            $newHash = $this->passwords->hash($password);
            $this->users->updatePasswordHash((int) $user['id'], $newHash);
            $user['password'] = $newHash;
        }

        $this->session->regenerate();
        $this->session->put('id', (int) $user['id']);
        $this->session->put('login', (string) $user['login']);
        $this->session->put('password', (string) $user['password']);

        $this->users->markOnline((int) $user['id'], ClientIp::fromServer($request->server));

        return Response::redirect('/game');
    }

    public function logout(Request $request): Response
    {
        $this->session->destroy();
        return Response::redirect('/');
    }

    private function loginError(string $message): Response
    {
        return new Response(View::render('error', ['message' => $message]), 422);
    }
}
