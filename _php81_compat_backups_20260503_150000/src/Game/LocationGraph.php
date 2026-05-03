<?php
declare(strict_types=1);

namespace Pokemon8\Game;

final readonly class LocationGraph
{
    /** @param array<int, list<int>> $edges */
    public function __construct(private array $edges)
    {
    }

    public static function fromLegacyData(string $path): self
    {
        $dataLoc = [];
        if (is_file($path)) {
            require $path;
        }

        $edges = [];
        foreach ($dataLoc as $from => $targets) {
            $from = (int) $from;
            $edges[$from] = array_values(array_unique(array_map('intval', (array) $targets)));
        }

        return new self($edges);
    }

    /** @return list<int> */
    public function movesFrom(int $locationId): array
    {
        return $this->edges[$locationId] ?? [$locationId];
    }

    public function canMove(int $from, int $to): bool
    {
        return in_array($to, $this->movesFrom($from), true);
    }
}
