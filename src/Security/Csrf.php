<?php
declare(strict_types=1);

namespace Pokemon8\Security;

final class Csrf
{
    private const TTL_SECONDS = 7200;

    public function __construct(private Session $session)
    {
    }

    /**
     * Возвращает токен формы. Если токена нет, создает криптостойкий новый.
     */
    public function token(): string
    {
        $token = $this->session->get('_csrf');
        $issuedAt = (int) $this->session->get('_csrf_issued_at', 0);
        if (
            is_string($token)
            && $token !== ''
            && ($issuedAt <= 0 || (time() - $issuedAt) < self::TTL_SECONDS)
        ) {
            return $token;
        }

        return $this->rotate();
    }

    /**
     * Replaces the token after a privilege or authentication boundary change.
     */
    public function rotate(): string
    {
        $token = bin2hex(random_bytes(32));
        $this->session->put('_csrf', $token);
        $this->session->put('_csrf_issued_at', time());

        return $token;
    }

    /**
     * Проверяет, что POST-запрос пришел из формы нашего сайта.
     */
    public function validate(?string $token): bool
    {
        $known = $this->session->get('_csrf');
        $issuedAt = (int) $this->session->get('_csrf_issued_at', 0);

        return is_string($known)
            && is_string($token)
            && ($issuedAt <= 0 || (time() - $issuedAt) < self::TTL_SECONDS)
            && hash_equals($known, $token);
    }
}
