<?php
declare(strict_types=1);

namespace Pokemon8\Security;

final readonly class Csrf
{
    public function __construct(private Session $session)
    {
    }

    /**
     * Возвращает токен формы. Если токена нет, создает криптостойкий новый.
     */
    public function token(): string
    {
        $token = $this->session->get('_csrf');
        if (is_string($token) && $token !== '') {
            return $token;
        }

        $token = bin2hex(random_bytes(32));
        $this->session->put('_csrf', $token);

        return $token;
    }

    /**
     * Проверяет, что POST-запрос пришел из формы нашего сайта.
     */
    public function validate(?string $token): bool
    {
        $known = $this->session->get('_csrf');
        return is_string($known) && is_string($token) && hash_equals($known, $token);
    }
}
