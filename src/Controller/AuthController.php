<?php
declare(strict_types=1);

namespace Pokemon8\Controller;

use Pokemon8\Http\Request;
use Pokemon8\Http\Response;
use Pokemon8\Repository\AdminRepository;
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
        private AdminRepository $admin,
        private array $config,
    ) {
    }

    public function login(Request $request): Response
    {
        // CSRF нужен даже для логина: иначе форму можно дергать с чужого сайта.
        if (!$this->csrf->validate($request->input('_csrf'))) {
            return $this->loginError('Сессия формы устарела. Обнови страницу и попробуй снова.');
        }

        $login = $request->input('LOGIN');
        $password = $request->input('PASSWORD');

        if ($login === '' || $password === '') {
            return $this->loginError('Заполни логин и пароль.');
        }

        if (!preg_match('/^[a-z_-]{3,16}$/i', $login)) {
            return $this->loginError('Логин должен быть 3-16 символов: латиница, "_" или "-".');
        }

        $passwordLength = mb_strlen($password, 'UTF-8');
        if ($passwordLength < 6 || $passwordLength > 72) {
            return $this->loginError('Пароль должен быть от 6 до 72 символов.');
        }

        $state = $this->users->findStateByLogin($login);
        if ((int) ($this->config['techwork'] ?? 0) === 1 && !$this->admin->canAccess((int) ($state['id'] ?? 0))) {
            return $this->loginError('На сервере идут технические работы.');
        }

        if (($state['activation'] ?? null) !== null && (int) $state['activation'] === 0) {
            return $this->loginError('Аккаунт заблокирован администрацией проекта.');
        }

        $user = $this->users->findActiveByLogin($login);
        if ($user === null || !$this->passwords->verify($password, (string) $user['password'])) {
            return $this->loginError('Логин или пароль неверный.');
        }

        // Если пользователь вошел со старым хэшем, сразу мигрируем пароль на современный формат.
        if ($this->passwords->needsRehash((string) $user['password'])) {
            $newHash = $this->passwords->hash($password);
            $this->users->updatePasswordHash((int) $user['id'], $newHash);
            $user['password'] = $newHash;
        }

        $this->session->regenerate();
        $this->session->put('id', (int) $user['id']);
        $this->session->put('login', (string) $user['login']);
        $this->session->put('password', (string) $user['password']);
        $this->session->put('groups', (int) $user['groups']);

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
