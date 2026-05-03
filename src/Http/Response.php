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
        // Р РµРґРёСЂРµРєС‚ РїСЂРµРґСЃС‚Р°РІР»РµРЅ РєР°Рє РѕР±С‹С‡РЅС‹Р№ Response, С‡С‚РѕР±С‹ РєРѕРЅС‚СЂРѕР»Р»РµСЂС‹ РЅРёС‡РµРіРѕ РЅРµ echo'Р»Рё РЅР°РїСЂСЏРјСѓСЋ.
        return new self('', $status, ['Location' => $url]);
    }

    public function send(): void
    {
        // Р•РґРёРЅСЃС‚РІРµРЅРЅРѕРµ РјРµСЃС‚Рѕ, РіРґРµ РѕС‚РІРµС‚ СЂРµР°Р»СЊРЅРѕ РѕС‚РїСЂР°РІР»СЏРµС‚СЃСЏ РІ Р±СЂР°СѓР·РµСЂ.
        http_response_code($this->status);
        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }
        echo $this->body;
    }
}
