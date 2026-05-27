<?php
declare(strict_types=1);

namespace Pokemon8\Support;

final class Mailer
{
    public function __construct(private array $config)
    {
    }

    public function send(string $to, string $subject, string $text): bool
    {
        $to = trim($to);
        if (!filter_var($to, FILTER_VALIDATE_EMAIL) || !($this->config['enabled'] ?? true)) {
            return false;
        }

        $fromAddress = (string) ($this->config['from_address'] ?? 'no-reply@pokemonchic.local');
        $fromName = (string) ($this->config['from_name'] ?? 'Pokemon 8.0');
        $encodedSubject = mb_encode_mimeheader($subject, 'UTF-8', 'B');
        $encodedFromName = mb_encode_mimeheader($fromName, 'UTF-8', 'B');
        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
            'From: ' . $encodedFromName . ' <' . $fromAddress . '>',
            'Reply-To: ' . $fromAddress,
            'X-Mailer: Pokemon 8.0',
        ];

        return mail($to, $encodedSubject, $text, implode("\r\n", $headers));
    }
}
