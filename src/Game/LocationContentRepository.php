<?php
declare(strict_types=1);

namespace Pokemon8\Game;

final class LocationContentRepository
{
    /** @param array<int, array<string, mixed>> $locations */
    public function __construct(private array $locations)
    {
    }

    public static function fromFile(string $path): self
    {
        $locations = is_file($path) ? require $path : [];
        return new self(is_array($locations) ? $locations : []);
    }

    /** @return array{name:?string,about:?string,image:?string,npcs:list<array<string,mixed>>} */
    public function find(int $locationId): array
    {
        $data = $this->locations[$locationId] ?? [];

        return [
            'name' => isset($data['name']) && $data['name'] !== '' ? (string) $data['name'] : null,
            'about' => isset($data['about']) && $data['about'] !== '' ? (string) $data['about'] : null,
            'image' => isset($data['image']) && $data['image'] !== '' ? (string) $data['image'] : null,
            'npcs' => $this->normalizeNpcs($data['npcs'] ?? []),
        ];
    }

    /** @param mixed $npcs */
    private function normalizeNpcs(mixed $npcs): array
    {
        if (!is_array($npcs)) {
            return [];
        }

        $normalized = [];
        foreach ($npcs as $npc) {
            if (!is_array($npc) || empty($npc['title'])) {
                continue;
            }

            $normalized[] = [
                'title' => (string) $npc['title'],
                'type' => (string) ($npc['type'] ?? 'npc'),
                'params' => is_array($npc['params'] ?? null) ? $npc['params'] : [],
                'icon' => (string) ($npc['icon'] ?? 'person'),
            ];
        }

        return $normalized;
    }
}
