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
        // Редирект представлен как обычный Response, чтобы контроллеры ничего не echo'ли напрямую.
        return new self('', $status, ['Location' => $url]);
    }

    public function send(): void
    {
        // Единственное место, где ответ реально отправляется в браузер.
        http_response_code($this->status);
        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }
        echo $this->body;
    }
}
