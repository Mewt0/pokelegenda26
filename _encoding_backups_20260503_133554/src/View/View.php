<?php
declare(strict_types=1);

namespace Pokemon8\View;

final class View
{
    public static function render(string $template, array $data = []): string
    {
        // Р РЃР В°Р В±Р В»Р С•Р Р… Р С—Р С•Р В»РЎС“РЎвЂЎР В°Р ВµРЎвЂљ РЎвЂљР С•Р В»РЎРЉР С”Р С• РЎРЏР Р†Р Р…Р С• Р С—Р ВµРЎР‚Р ВµР Т‘Р В°Р Р…Р Р…РЎвЂ№Р Вµ Р Т‘Р В°Р Р…Р Р…РЎвЂ№Р Вµ Р С‘Р В· Р С”Р С•Р Р…РЎвЂљРЎР‚Р С•Р В»Р В»Р ВµРЎР‚Р В°.
        extract($data, EXTR_SKIP);
        ob_start();
        require dirname(__DIR__, 2) . '/views/' . $template . '.php';
        return (string) ob_get_clean();
    }

    public static function e(mixed $value): string
    {
        // Р вЂўР Т‘Р С‘Р Р…Р В°РЎРЏ РЎвЂћРЎС“Р Р…Р С”РЎвЂ Р С‘РЎРЏ Р В±Р ВµР В·Р С•Р С—Р В°РЎРѓР Р…Р С•Р С–Р С• Р Р†РЎвЂ№Р Р†Р С•Р Т‘Р В° РЎвЂљР ВµР С”РЎРѓРЎвЂљР В° Р Р† HTML.
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
