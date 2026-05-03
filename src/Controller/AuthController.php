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

final class AuthController
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
        // CSRF РЅСѓР¶РµРЅ РґР°Р¶Рµ РґР»СЏ Р»РѕРіРёРЅР°: РёРЅР°С‡Рµ С„РѕСЂРјСѓ РјРѕР¶РЅРѕ РґРµСЂРіР°С‚СЊ СЃ С‡СѓР¶РѕРіРѕ СЃР°Р№С‚Р°.
        if (!$this->csrf->validate($request->input('_csrf'))) {
            return $this->loginError('РЎРµСЃСЃРёСЏ С„РѕСЂРјС‹ СѓСЃС‚Р°СЂРµР»Р°. РћР±РЅРѕРІРё СЃС‚СЂР°РЅРёС†Сѓ Рё РїРѕРїСЂРѕР±СѓР№ СЃРЅРѕРІР°.');
        }

        $login = $request->input('LOGIN');
        $password = $request->input('PASSWORD');

        if ($login === '' || $password === '') {
            return $this->loginError('Р—Р°РїРѕР»РЅРё Р»РѕРіРёРЅ Рё РїР°СЂРѕР»СЊ.');
        }

        if (!preg_match('/^[a-z_-]{3,16}$/i', $login)) {
            return $this->loginError('Р›РѕРіРёРЅ РґРѕР»Р¶РµРЅ Р±С‹С‚СЊ 3-16 СЃРёРјРІРѕР»РѕРІ: Р»Р°С‚РёРЅРёС†Р°, "_" РёР»Рё "-".');
        }

        $passwordLength = mb_strlen($password, 'UTF-8');
        if ($passwordLength < 6 || $passwordLength > 72) {
            return $this->loginError('РџР°СЂРѕР»СЊ РґРѕР»Р¶РµРЅ Р±С‹С‚СЊ РѕС‚ 6 РґРѕ 72 СЃРёРјРІРѕР»РѕРІ.');
        }

        $state = $this->users->findStateByLogin($login);
        if ((int) ($this->config['techwork'] ?? 0) === 1 && (int) ($state['groups'] ?? 0) !== 1) {
            return $this->loginError('РќР° СЃРµСЂРІРµСЂРµ РёРґСѓС‚ С‚РµС…РЅРёС‡РµСЃРєРёРµ СЂР°Р±РѕС‚С‹.');
        }

        if (($state['activation'] ?? null) !== null && (int) $state['activation'] === 0) {
            return $this->loginError('РђРєРєР°СѓРЅС‚ Р·Р°Р±Р»РѕРєРёСЂРѕРІР°РЅ Р°РґРјРёРЅРёСЃС‚СЂР°С†РёРµР№ РїСЂРѕРµРєС‚Р°.');
        }

        $user = $this->users->findActiveByLogin($login);
        if ($user === null || !$this->passwords->verify($password, (string) $user['password'])) {
            return $this->loginError('Р›РѕРіРёРЅ РёР»Рё РїР°СЂРѕР»СЊ РЅРµРІРµСЂРЅС‹Р№.');
        }

        // Р•СЃР»Рё РїРѕР»СЊР·РѕРІР°С‚РµР»СЊ РІРѕС€РµР» СЃРѕ СЃС‚Р°СЂС‹Рј С…СЌС€РµРј, СЃСЂР°Р·Сѓ РјРёРіСЂРёСЂСѓРµРј РїР°СЂРѕР»СЊ РЅР° СЃРѕРІСЂРµРјРµРЅРЅС‹Р№ С„РѕСЂРјР°С‚.
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
