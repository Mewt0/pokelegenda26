<?php
declare(strict_types=1);

namespace Pokemon8\Game;

final class LegacyRoomDataExtractor
{
    /** @var array<int, array<string, mixed>> */
    private array $cache = [];

    public function __construct(private string $roomsPath)
    {
    }

    /** @return array{name:?string,about:?string,image:?string,npcs:list<array<string,mixed>>} */
    public function room(int $locationId): array
    {
        if (isset($this->cache[$locationId])) {
            return $this->cache[$locationId];
        }

        $path = rtrim($this->roomsPath, '/\\') . DIRECTORY_SEPARATOR . $locationId . '.php';
        if (!is_file($path)) {
            return $this->cache[$locationId] = [
                'name' => null,
                'about' => null,
                'image' => null,
                'npcs' => [],
            ];
        }

        $source = (string) file_get_contents($path);
        $name = $this->stringAssignment($source, 'name');
        $about = $this->stringAssignment($source, 'about');
        $image = $this->imageFromHtml($this->stringAssignment($source, 'img_r') ?? '');
        $npcs = $this->npcLinks($source);

        return $this->cache[$locationId] = [
            'name' => $name !== null ? $this->repairText($name) : null,
            'about' => $about !== null ? $this->cleanText($this->repairText($about)) : null,
            'image' => $image,
            'npcs' => $npcs,
        ];
    }

    private function stringAssignment(string $source, string $variable): ?string
    {
        $pattern = "/\\$" . preg_quote($variable, '/') . "\\s*=\\s*(['\"])(.*?)\\1\\s*;/s";
        if (!preg_match($pattern, $source, $match)) {
            return null;
        }

        return stripcslashes($match[2]);
    }

    private function imageFromHtml(string $html): ?string
    {
        if (!preg_match("/src\\s*=\\s*(['\"])(.*?)\\1/i", $html, $match)) {
            return null;
        }

        $src = trim($match[2]);
        if ($src === '') {
            return null;
        }

        return str_starts_with($src, '/') ? $src : '/' . ltrim($src, '/');
    }

    /** @return list<array<string,mixed>> */
    private function npcLinks(string $source): array
    {
        preg_match_all("/<a\\s+[^>]*href=(['\"])(.*?)\\1[^>]*>(.*?)<\\/a>/is", $source, $matches, PREG_SET_ORDER);

        $npcs = [];
        $seen = [];
        foreach ($matches as $match) {
            $href = html_entity_decode(strip_tags($match[2]), ENT_QUOTES, 'UTF-8');
            if (!str_contains($href, 'go=char')) {
                continue;
            }

            $query = parse_url($href, PHP_URL_QUERY);
            if ($query === null || $query === false) {
                continue;
            }

            parse_str($query, $params);
            if (!isset($params['npc']) && !isset($params['quest_npc']) && !isset($params['do'])) {
                continue;
            }

            $title = $this->cleanText($this->repairText(strip_tags($match[3])));
            if ($title === '') {
                continue;
            }

            $key = md5($href . '|' . $title);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;

            $npcs[] = [
                'title' => $title,
                'type' => isset($params['quest_npc']) ? 'quest' : 'npc',
                'params' => $this->safeParams($params),
                'icon' => $this->iconFor($title, isset($params['quest_npc']) ? 'quest' : 'npc'),
            ];
        }

        return $npcs;
    }

    /** @param array<string,mixed> $params */
    private function safeParams(array $params): array
    {
        $safe = [];
        foreach (['npc', 'quest_npc', 'do', 'do_npc'] as $key) {
            if (!isset($params[$key])) {
                continue;
            }
            $safe[$key] = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $params[$key]);
        }

        return $safe;
    }

    private function iconFor(string $title, string $type): string
    {
        $lower = mb_strtolower($title, 'UTF-8');
        if (str_contains($lower, 'покецентр')) {
            return 'cross';
        }
        if (str_contains($lower, 'маркет') || str_contains($lower, 'магазин')) {
            return 'shop';
        }
        if (str_contains($lower, 'куратор')) {
            return 'mentor';
        }

        return $type === 'quest' ? 'quest' : 'person';
    }

    private function cleanText(string $value): string
    {
        $value = preg_replace('/\\s+/u', ' ', $value) ?? $value;
        return trim(html_entity_decode($value, ENT_QUOTES, 'UTF-8'));
    }

    private function repairText(string $value): string
    {
        if ($value === '') {
            return '';
        }

        if (preg_match('/[РС][\\x{0400}-\\x{04FF}]/u', $value)) {
            $bytes = @iconv('UTF-8', 'Windows-1251//IGNORE', $value);
            if ($bytes !== false) {
                $fixed = @iconv('UTF-8', 'UTF-8//IGNORE', $bytes);
                if ($fixed !== false && $fixed !== '') {
                    return $fixed;
                }
            }
        }

        return $value;
    }
}
