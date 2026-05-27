<?php
declare(strict_types=1);

namespace Pokemon8\Http;

final class Response
{
    public function __construct(
        public string $body = '',
        public int $status = 200,
        public array $headers = ['Content-Type' => 'text/html; charset=UTF-8'],
    ) {
    }

    public static function redirect(string $url, int $status = 302): self
    {
        // Редирект представлен обычным Response, чтобы контроллеры не echo'ли напрямую.
        return new self('', $status, ['Location' => $url]);
    }

    public function send(): void
    {
        // Единственное место, где ответ реально отправляется в браузер.
        $this->sendStatus($this->status);
        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }
        echo $this->body;
    }

    private function sendStatus(int $status): void
    {
        if ($status === 419) {
            $protocol = $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1';
            header($protocol . ' 419 Page Expired', true, 419);
            return;
        }

        http_response_code($status);
    }
}
