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
use Pokemon8\Support\Mailer;
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
        private Mailer $mailer,
        private array $mailConfig,
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

        if (!preg_match('/^[a-z0-9_-]{3,16}$/i', $login)) {
            return $this->loginError('Логин должен быть 3-16 символов: латиница, цифры, "_" или "-".');
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
        $this->session->put('browse', $this->browserSign($request));

        $this->users->markOnline((int) $user['id'], ClientIp::fromServer($request->server));

        return Response::redirect('/game');
    }

    public function logout(Request $request): Response
    {
        $this->session->destroy();
        return Response::redirect('/');
    }

    public function registerForm(Request $request): Response
    {
        if ($this->session->get('id')) {
            return Response::redirect('/game');
        }

        return new Response(View::render('auth-register', [
            'csrfToken' => $this->csrf->token(),
        ]));
    }

    public function register(Request $request): Response
    {
        if (!$this->csrf->validate($request->input('_csrf'))) {
            return $this->authMessage('Регистрация', 'Сессия формы устарела. Обнови страницу и попробуй снова.', true);
        }

        $login = $request->input('LOGIN');
        $password = $request->input('PASSWORD');
        $passwordRepeat = $request->input('PASSWORD_REPEAT');
        $email = $this->normalizeOptionalEmail($request->input('EMAIL'));

        if (!preg_match('/^[a-z0-9_-]{3,16}$/i', $login)) {
            return $this->authMessage('Регистрация', 'Логин должен быть 3-16 символов: латиница, цифры, "_" или "-".', true);
        }
        if ($this->users->loginExists($login)) {
            return $this->authMessage('Регистрация', 'Такой логин уже занят.', true);
        }

        $passwordLength = mb_strlen($password, 'UTF-8');
        if ($passwordLength < 6 || $passwordLength > 72) {
            return $this->authMessage('Регистрация', 'Пароль должен быть от 6 до 72 символов.', true);
        }
        if (!hash_equals($password, $passwordRepeat)) {
            return $this->authMessage('Регистрация', 'Пароли не совпадают.', true);
        }

        if ($email !== null) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->authMessage('Регистрация', 'Email указан неверно. Можно оставить поле пустым.', true);
            }
            if ($this->users->emailExists($email)) {
                return $this->authMessage('Регистрация', 'Этот email уже привязан к другому аккаунту.', true);
            }
        }

        $hash = $this->passwords->hash($password);
        $user = $this->users->createUser($login, $hash, $email, ClientIp::fromServer($request->server));

        $this->session->regenerate();
        $this->session->put('id', (int) $user['id']);
        $this->session->put('login', (string) $user['login']);
        $this->session->put('password', $hash);
        $this->session->put('groups', 6);
        $this->session->put('browse', $this->browserSign($request));
        $this->users->markOnline((int) $user['id'], ClientIp::fromServer($request->server));

        if ($email !== null) {
            $this->sendEmailVerification((int) $user['id'], $login, $email, $request);
        }

        return Response::redirect('/game');
    }

    public function forgotForm(Request $request): Response
    {
        return new Response(View::render('auth-forgot', [
            'csrfToken' => $this->csrf->token(),
        ]));
    }

    public function forgot(Request $request): Response
    {
        if (!$this->csrf->validate($request->input('_csrf'))) {
            return $this->authMessage('Восстановление пароля', 'Сессия формы устарела. Обнови страницу и попробуй снова.', true);
        }

        $email = $this->normalizeOptionalEmail($request->input('EMAIL'));
        if ($email === null || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->authMessage('Восстановление пароля', 'Укажи корректный email, привязанный к аккаунту.', true);
        }

        $user = $this->users->findActiveByEmail($email);
        if ($user !== null && $this->accountHasUsableEmail($user)) {
            $token = bin2hex(random_bytes(32));
            $hash = hash('sha256', $token);
            $ttl = max(10, (int) ($this->mailConfig['reset_ttl_minutes'] ?? 60));
            $expiresAt = time() + ($ttl * 60);
            $this->users->createPasswordResetToken((int) $user['id'], (string) $user['email'], $hash, $expiresAt, ClientIp::fromServer($request->server));
            $link = $this->absoluteUrl($request, '/password/reset?token=' . urlencode($token));
            $this->mailer->send(
                (string) $user['email'],
                'Восстановление пароля Pokemon 8.0',
                "Привет, {$user['login']}!\n\nСсылка для смены пароля действует {$ttl} минут:\n{$link}\n\nЕсли ты не запрашивал восстановление, просто проигнорируй это письмо."
            );
        }

        return $this->authMessage(
            'Восстановление пароля',
            'Если этот email привязан к активному аккаунту, мы отправили письмо со ссылкой для восстановления.'
        );
    }

    public function resetForm(Request $request): Response
    {
        $token = $request->input('token');
        $row = $this->validPasswordResetToken($token);
        if ($row === null) {
            return $this->authMessage('Новый пароль', 'Ссылка восстановления недействительна или уже истекла.', true);
        }

        return new Response(View::render('auth-reset', [
            'csrfToken' => $this->csrf->token(),
            'token' => $token,
            'login' => (string) $row['login'],
        ]));
    }

    public function reset(Request $request): Response
    {
        if (!$this->csrf->validate($request->input('_csrf'))) {
            return $this->authMessage('Новый пароль', 'Сессия формы устарела. Открой ссылку из письма ещё раз.', true);
        }

        $token = $request->input('token');
        $row = $this->validPasswordResetToken($token);
        if ($row === null) {
            return $this->authMessage('Новый пароль', 'Ссылка восстановления недействительна или уже истекла.', true);
        }

        $password = $request->input('PASSWORD');
        $passwordRepeat = $request->input('PASSWORD_REPEAT');
        $passwordLength = mb_strlen($password, 'UTF-8');
        if ($passwordLength < 6 || $passwordLength > 72) {
            return $this->authMessage('Новый пароль', 'Пароль должен быть от 6 до 72 символов.', true);
        }
        if (!hash_equals($password, $passwordRepeat)) {
            return $this->authMessage('Новый пароль', 'Пароли не совпадают.', true);
        }

        $this->users->updatePasswordHash((int) $row['user_id'], $this->passwords->hash($password));
        $this->users->markPasswordResetTokenUsed((int) $row['id']);

        return $this->authMessage('Новый пароль', 'Пароль изменён. Теперь можно войти с новым паролем.');
    }

    public function updateEmail(Request $request): Response
    {
        $userId = (int) $this->session->get('id', 0);
        if ($userId <= 0) {
            return Response::redirect('/');
        }
        if (!$this->csrf->validate($request->input('_csrf'))) {
            return $this->authMessage('Почта аккаунта', 'Сессия формы устарела. Вернись в профиль и попробуй снова.', true);
        }

        $email = $this->normalizeOptionalEmail($request->input('EMAIL'));
        if ($email !== null && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->authMessage('Почта аккаунта', 'Email указан неверно.', true);
        }
        if ($email !== null && $this->users->emailExists($email, $userId)) {
            return $this->authMessage('Почта аккаунта', 'Этот email уже привязан к другому аккаунту.', true);
        }

        $this->users->updateEmail($userId, $email, false);
        if ($email !== null) {
            $this->sendEmailVerification($userId, (string) $this->session->get('login', 'тренер'), $email, $request);
        }

        return $this->authMessage(
            'Почта аккаунта',
            $email === null ? 'Почта отвязана от аккаунта.' : 'Почта сохранена. Мы отправили письмо-подтверждение, логика подтверждения уже подготовлена.'
        );
    }

    public function verifyEmail(Request $request): Response
    {
        $token = $request->input('token');
        if (!preg_match('/^[a-f0-9]{64}$/i', $token)) {
            return $this->authMessage('Подтверждение почты', 'Ссылка подтверждения недействительна.', true);
        }

        $row = $this->users->findEmailVerificationToken(hash('sha256', $token));
        if ($row === null || (int) $row['used_at'] > 0 || (int) $row['expires_at'] < time() || (int) $row['activation'] !== 1) {
            return $this->authMessage('Подтверждение почты', 'Ссылка подтверждения истекла или уже использована.', true);
        }

        $this->users->updateEmail((int) $row['user_id'], (string) $row['email'], true);
        $this->users->markEmailVerificationTokenUsed((int) $row['id']);

        return $this->authMessage('Подтверждение почты', 'Почта подтверждена и может использоваться для восстановления пароля.');
    }

    private function loginError(string $message): Response
    {
        return new Response(View::render('error', ['message' => $message]), 422);
    }

    private function authMessage(string $title, string $message, bool $bad = false): Response
    {
        return new Response(View::render('auth-message', [
            'title' => $title,
            'message' => $message,
            'bad' => $bad,
        ]), $bad ? 422 : 200);
    }

    private function normalizeOptionalEmail(string $email): ?string
    {
        $email = trim(mb_strtolower($email, 'UTF-8'));
        return $email === '' ? null : $email;
    }

    private function accountHasUsableEmail(array $user): bool
    {
        $email = $this->normalizeOptionalEmail((string) ($user['email'] ?? ''));
        return $email !== null && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function validPasswordResetToken(string $token): ?array
    {
        if (!preg_match('/^[a-f0-9]{64}$/i', $token)) {
            return null;
        }

        $row = $this->users->findPasswordResetToken(hash('sha256', $token));
        if ($row === null || (int) $row['used_at'] > 0 || (int) $row['expires_at'] < time() || (int) $row['activation'] !== 1) {
            return null;
        }

        return $row;
    }

    private function sendEmailVerification(int $userId, string $login, string $email, Request $request): void
    {
        $token = bin2hex(random_bytes(32));
        $hash = hash('sha256', $token);
        $ttl = max(60, (int) ($this->mailConfig['verify_ttl_minutes'] ?? 1440));
        $expiresAt = time() + ($ttl * 60);
        $this->users->createEmailVerificationToken($userId, $email, $hash, $expiresAt, ClientIp::fromServer($request->server));
        $link = $this->absoluteUrl($request, '/email/verify?token=' . urlencode($token));
        $this->mailer->send(
            $email,
            'Подтверждение почты Pokemon 8.0',
            "Привет, {$login}!\n\nПочта привязана к аккаунту. Ссылка для будущего подтверждения:\n{$link}\n\nПока подтверждение не обязательно, но эта заготовка уже нужна для восстановления пароля."
        );
    }

    private function absoluteUrl(Request $request, string $path): string
    {
        $configured = rtrim((string) ($this->config['url'] ?? ''), '/');
        if ($configured !== '' && !str_contains($configured, '127.0.0.1:8000')) {
            return $configured . $path;
        }

        $scheme = (!empty($request->server['HTTPS']) && $request->server['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = (string) ($request->server['HTTP_HOST'] ?? 'pokemonchic.com');
        return $scheme . '://' . $host . $path;
    }

    private function browserSign(Request $request): string
    {
        $ip = ClientIp::fromServer($request->server);
        $parts = explode('.', $ip);
        $network = ($parts[0] ?? '0') . '.' . ($parts[1] ?? '0');
        $agent = (string) ($request->server['HTTP_USER_AGENT'] ?? 'none');
        $secret = (string) ($_ENV['SESSION_BROWSER_SIGN_SECRET'] ?? 'change-me-local-secret');

        return hash('sha256', $secret . '::' . $network . '::' . $agent);
    }
}
