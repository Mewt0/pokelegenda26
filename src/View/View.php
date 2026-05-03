<?php
declare(strict_types=1);

namespace Pokemon8\View;

final class View
{
    public static function render(string $template, array $data = []): string
    {
        // Шаблон получает только явно переданные данные из контроллера.
        extract($data, EXTR_SKIP);
        ob_start();
        require dirname(__DIR__, 2) . '/views/' . $template . '.php';
        return (string) ob_get_clean();
    }

    public static function e(mixed $value): string
    {
        // Единая функция безопасного вывода текста в HTML.
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
