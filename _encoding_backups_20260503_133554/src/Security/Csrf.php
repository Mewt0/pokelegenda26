<?php
declare(strict_types=1);

namespace Pokemon8\Security;

final readonly class Csrf
{
    public function __construct(private Session $session)
    {
    }

    /**
     * Р вЂ™Р С•Р В·Р Р†РЎР‚Р В°РЎвЂ°Р В°Р ВµРЎвЂљ РЎвЂљР С•Р С”Р ВµР Р… РЎвЂћР С•РЎР‚Р СРЎвЂ№. Р вЂўРЎРѓР В»Р С‘ РЎвЂљР С•Р С”Р ВµР Р…Р В° Р Р…Р ВµРЎвЂљ, РЎРѓР С•Р В·Р Т‘Р В°Р ВµРЎвЂљ Р С”РЎР‚Р С‘Р С—РЎвЂљР С•РЎРѓРЎвЂљР С•Р в„–Р С”Р С‘Р в„– Р Р…Р С•Р Р†РЎвЂ№Р в„–.
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
     * Р СџРЎР‚Р С•Р Р†Р ВµРЎР‚РЎРЏР ВµРЎвЂљ, РЎвЂЎРЎвЂљР С• POST-Р В·Р В°Р С—РЎР‚Р С•РЎРѓ Р С—РЎР‚Р С‘РЎв‚¬Р ВµР В» Р С‘Р В· РЎвЂћР С•РЎР‚Р СРЎвЂ№ Р Р…Р В°РЎв‚¬Р ВµР С–Р С• РЎРѓР В°Р в„–РЎвЂљР В°.
     */
    public function validate(?string $token): bool
    {
        $known = $this->session->get('_csrf');
        return is_string($known) && is_string($token) && hash_equals($known, $token);
    }
}
