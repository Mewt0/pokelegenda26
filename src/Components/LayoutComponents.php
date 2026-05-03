<?php
declare(strict_types=1);

namespace Pokemon8\Components;

/**
 * Small HTML components for legacy-free pages.
 *
 * These helpers are intentionally simple: they escape text, return markup, and
 * do not touch global state. Keep all visible strings in UTF-8.
 */
final class NewsItem
{
    public static function render(array $data): string
    {
        $date = htmlspecialchars((string) ($data['date'] ?? ''), ENT_QUOTES, 'UTF-8');
        $title = htmlspecialchars((string) ($data['title'] ?? ''), ENT_QUOTES, 'UTF-8');
        $text = htmlspecialchars((string) ($data['text'] ?? ''), ENT_QUOTES, 'UTF-8');

        return <<<HTML
<article class="news-item">
    <div class="news-item-date">$date</div>
    <h3 class="news-item-title">$title</h3>
    <p class="news-item-text">$text</p>
</article>
HTML;
    }
}

final class RankingItem
{
    public static function render(array $data): string
    {
        $position = htmlspecialchars((string) ($data['position'] ?? ''), ENT_QUOTES, 'UTF-8');
        $name = htmlspecialchars((string) ($data['name'] ?? ''), ENT_QUOTES, 'UTF-8');
        $score = htmlspecialchars(number_format((int) ($data['score'] ?? 0), 0, '.', ' '), ENT_QUOTES, 'UTF-8');

        return <<<HTML
<div class="ranking-item">
    <span class="ranking-position">$position</span>
    <span class="ranking-name">$name</span>
    <span class="ranking-score">$score</span>
</div>
HTML;
    }
}

final class RankingSection
{
    public static function render(string $title, array $rankings): string
    {
        $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $items = '';

        foreach ($rankings as $ranking) {
            $items .= RankingItem::render($ranking) . PHP_EOL;
        }

        return <<<HTML
<section class="ranking-section">
    <h2 class="ranking-title">$title</h2>
    $items
</section>
HTML;
    }
}

final class Menu
{
    public static function render(array $items): string
    {
        $html = '<ul id="menu">' . PHP_EOL;

        foreach ($items as $item) {
            $title = htmlspecialchars((string) ($item['title'] ?? ''), ENT_QUOTES, 'UTF-8');
            $url = htmlspecialchars((string) ($item['url'] ?? '#'), ENT_QUOTES, 'UTF-8');
            $active = !empty($item['active']) ? ' class="active"' : '';
            $html .= "  <li$active><a href=\"$url\">$title</a></li>" . PHP_EOL;
        }

        return $html . '</ul>' . PHP_EOL;
    }
}

final class TopMenu
{
    public static function render(array $items): string
    {
        $html = '<nav id="t-menu"><ul>' . PHP_EOL;

        foreach ($items as $item) {
            $title = htmlspecialchars((string) ($item['title'] ?? ''), ENT_QUOTES, 'UTF-8');
            $url = htmlspecialchars((string) ($item['url'] ?? '#'), ENT_QUOTES, 'UTF-8');
            $html .= "  <li><a href=\"$url\">$title</a></li>" . PHP_EOL;
        }

        return $html . '</ul></nav>' . PHP_EOL;
    }
}

final class Button
{
    public static function render(
        string $text,
        string $url = '#',
        string $type = 'link',
        array $attributes = []
    ): string {
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        $url = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');

        $attrs = '';
        foreach ($attributes as $key => $value) {
            $key = htmlspecialchars((string) $key, ENT_QUOTES, 'UTF-8');
            $value = htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
            $attrs .= " $key=\"$value\"";
        }

        if ($type === 'link') {
            return "<a href=\"$url\" class=\"button\"$attrs>$text</a>";
        }

        $type = htmlspecialchars($type, ENT_QUOTES, 'UTF-8');
        return "<button type=\"$type\"$attrs>$text</button>";
    }
}

final class LoginForm
{
    public static function render(string $action = '/login', string $submitText = 'Войти'): string
    {
        $action = htmlspecialchars($action, ENT_QUOTES, 'UTF-8');
        $submitText = htmlspecialchars($submitText, ENT_QUOTES, 'UTF-8');

        return <<<HTML
<form action="$action" method="post" id="login_form">
    <input type="text" name="LOGIN" placeholder="Логин" required autocomplete="username">
    <input type="password" name="PASSWORD" placeholder="Пароль" required autocomplete="current-password">
    <button type="submit">$submitText</button>
</form>
HTML;
    }
}

final class Message
{
    public static function render(string $text, string $type = 'info'): string
    {
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        $type = htmlspecialchars($type, ENT_QUOTES, 'UTF-8');

        return <<<HTML
<div class="message message-$type" role="alert">
    $text
</div>
HTML;
    }
}

final class Card
{
    public static function render(string $title, string $content, string $footer = ''): string
    {
        $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $footerHtml = '';

        if ($footer !== '') {
            $footer = htmlspecialchars($footer, ENT_QUOTES, 'UTF-8');
            $footerHtml = "    <div class=\"card-footer\">$footer</div>" . PHP_EOL;
        }

        return <<<HTML
<section class="card">
    <div class="card-header">
        <h3>$title</h3>
    </div>
    <div class="card-body">
        $content
    </div>
$footerHtml</section>
HTML;
    }
}

final class Pagination
{
    public static function render(int $currentPage, int $totalPages, string $baseUrl = '/'): string
    {
        $html = '<nav class="pagination"><ul>' . PHP_EOL;

        if ($currentPage > 1) {
            $prevUrl = htmlspecialchars($baseUrl . '?page=' . ($currentPage - 1), ENT_QUOTES, 'UTF-8');
            $html .= "  <li><a href=\"$prevUrl\">Назад</a></li>" . PHP_EOL;
        }

        $start = max(1, $currentPage - 2);
        $end = min($totalPages, $currentPage + 2);

        for ($page = $start; $page <= $end; $page++) {
            $pageUrl = htmlspecialchars($baseUrl . '?page=' . $page, ENT_QUOTES, 'UTF-8');
            $active = $page === $currentPage ? ' class="active"' : '';
            $html .= "  <li><a href=\"$pageUrl\"$active>$page</a></li>" . PHP_EOL;
        }

        if ($currentPage < $totalPages) {
            $nextUrl = htmlspecialchars($baseUrl . '?page=' . ($currentPage + 1), ENT_QUOTES, 'UTF-8');
            $html .= "  <li><a href=\"$nextUrl\">Вперёд</a></li>" . PHP_EOL;
        }

        return $html . '</ul></nav>' . PHP_EOL;
    }
}
