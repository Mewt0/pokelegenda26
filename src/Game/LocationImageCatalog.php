<?php
declare(strict_types=1);

namespace Pokemon8\Game;

/** Generated environment art, independent of quest/NPC content and DB readers. */
final class LocationImageCatalog
{
    private array $images;

    public function __construct(private string $appRoot, ?array $images = null)
    {
        $path = $this->appRoot . '/config/location_image_overrides.php';
        $loaded = $images ?? (is_file($path) ? require $path : []);
        $this->images = is_array($loaded) ? $loaded : [];
    }

    public function pathFor(int $locationId): ?string
    {
        $filename = $this->images[$locationId] ?? null;
        if (!is_string($filename) || !preg_match('/\A[a-z0-9-]+\.webp\z/D', $filename)) {
            return null;
        }
        $path = '/img/room/generated-20261025/' . $filename;
        return is_file($this->appRoot . $path) ? $path : null;
    }
}
