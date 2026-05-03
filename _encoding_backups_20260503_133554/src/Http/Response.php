<?php
declare(strict_types=1);

namespace Pokemon8\Http;

final readonly class Response
{
    public function __construct(
        public string $body = '',
        public int $status = 200,
        public array $headers = ['Content-Type' => 'text/html; charset=UTF-8'],
    ) {
    }

    public static function redirect(string $url, int $status = 302): self
    {
        // Р В Р ВµР Т‘Р С‘РЎР‚Р ВµР С”РЎвЂљ Р С—РЎР‚Р ВµР Т‘РЎРѓРЎвЂљР В°Р Р†Р В»Р ВµР Р… Р С”Р В°Р С” Р С•Р В±РЎвЂ№РЎвЂЎР Р…РЎвЂ№Р в„– Response, РЎвЂЎРЎвЂљР С•Р В±РЎвЂ№ Р С”Р С•Р Р…РЎвЂљРЎР‚Р С•Р В»Р В»Р ВµРЎР‚РЎвЂ№ Р Р…Р С‘РЎвЂЎР ВµР С–Р С• Р Р…Р Вµ echo'Р В»Р С‘ Р Р…Р В°Р С—РЎР‚РЎРЏР СРЎС“РЎР‹.
        return new self('', $status, ['Location' => $url]);
    }

    public function send(): void
    {
        // Р вЂўР Т‘Р С‘Р Р…РЎРѓРЎвЂљР Р†Р ВµР Р…Р Р…Р С•Р Вµ Р СР ВµРЎРѓРЎвЂљР С•, Р С–Р Т‘Р Вµ Р С•РЎвЂљР Р†Р ВµРЎвЂљ РЎР‚Р ВµР В°Р В»РЎРЉР Р…Р С• Р С•РЎвЂљР С—РЎР‚Р В°Р Р†Р В»РЎРЏР ВµРЎвЂљРЎРѓРЎРЏ Р Р† Р В±РЎР‚Р В°РЎС“Р В·Р ВµРЎР‚.
        http_response_code($this->status);
        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }
        echo $this->body;
    }
}
