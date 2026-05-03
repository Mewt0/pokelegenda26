<?php
declare(strict_types=1);

namespace Pokemon8\Game;

use Pokemon8\Repository\LocationRepository;

final readonly class LocationStateService
{
    public function __construct(
        private LocationRepository $locations,
        private LocationGraph $graph,
        private string $appRoot,
    ) {
    }

    public function currentStateForUser(int $userId): array
    {
        $user = $this->locations->findUserState($userId);
        if ($user === null) {
            return ['ok' => false, 'error' => 'auth'];
        }

        $locationId = (int) $user['buildmy'];
        $location = $this->locations->findLocation($locationId);
        if ($location === null) {
            return ['ok' => false, 'error' => 'location_not_found'];
        }

        return [
            'ok' => true,
            'user' => [
                'id' => (int) $user['id'],
                'login' => (string) $user['login'],
            ],
            'location' => $this->formatLocation($location),
            'moves' => $this->movesFor($locationId),
            'users' => $this->locations->usersAtLocation($locationId),
        ];
    }

    public function movesFor(int $locationId): array
    {
        $ids = array_values(array_filter(
            $this->graph->movesFrom($locationId),
            static fn (int $id): bool => $id !== $locationId
        ));
        $locations = $this->locations->findLocationsByIds($ids);

        $moves = [];
        foreach ($ids as $id) {
            if (!isset($locations[$id])) {
                continue;
            }
            $moves[] = [
                'id' => $id,
                'title' => $this->toUtf8($locations[$id]['title']),
            ];
        }

        return $moves;
    }

    private function formatLocation(array $location): array
    {
        $id = (int) $location['id'];

        return [
            'id' => $id,
            'title' => $this->toUtf8((string) $location['title']),
            'type' => (int) ($location['tipe'] ?? 0),
            'image' => $this->imageFor($id),
        ];
    }

    private function imageFor(int $locationId): string
    {
        $relative = sprintf('img/room/%03d.png', $locationId);
        if (is_file($this->appRoot . '/' . $relative)) {
            return '/' . $relative;
        }

        return '/img/room/001.png';
    }

    private function toUtf8(string $value): string
    {
        if (mb_check_encoding($value, 'UTF-8')) {
            return $value;
        }

        $converted = @iconv('Windows-1251', 'UTF-8//IGNORE', $value);
        return $converted !== false ? $converted : $value;
    }
}
