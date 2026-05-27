<?php
declare(strict_types=1);

namespace Pokemon8\Game;

use Pokemon8\Repository\BossRepository;
use Pokemon8\Repository\LocationRepository;
use Pokemon8\Repository\TransportRepository;

final class LocationStateService
{
    public function __construct(
        private LocationRepository $locations,
        private LocationGraph $graph,
        private string $appRoot,
        private LocationContentRepository $content,
        private ?BossRepository $bosses = null,
        private ?TransportRepository $transport = null,
    ) {
    }

    public function currentStateForUser(int $userId): array
    {
        $this->locations->touchOnlineHeartbeat($userId);
        $user = $this->locations->findUserState($userId);
        if ($user === null) {
            return ['ok' => false, 'error' => 'auth'];
        }

        $locationId = (int) $user['buildmy'];
        $location = $this->locations->findLocation($locationId);
        if ($location === null) {
            return ['ok' => false, 'error' => 'location_not_found'];
        }

        $formattedLocation = $locationId === TransportRepository::PLANE_LOCATION_ID
            ? $this->formatPlaneLocation($userId, $location)
            : $this->formatLocation($location);
        $flightStatus = $locationId === TransportRepository::PLANE_LOCATION_ID && $this->transport !== null
            ? $this->transport->flightStatus($userId)
            : null;

        return [
            'ok' => true,
            'user' => [
                'id' => (int) $user['id'],
                'login' => (string) $user['login'],
                'pveButton' => (int) ($user['pve_button'] ?? 0) === 1,
            ],
            'location' => $formattedLocation,
            'moves' => $locationId === TransportRepository::PLANE_LOCATION_ID ? [] : $this->movesFor($locationId),
            'users' => $this->locations->usersAtLocation($locationId),
            'bosses' => $locationId === TransportRepository::PLANE_LOCATION_ID ? [] : ($this->bosses?->activeForLocation($locationId) ?? []),
            'flight' => is_array($flightStatus) && is_array($flightStatus['flight'] ?? null) ? $flightStatus['flight'] : null,
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
        $room = $this->content->find($id);

        return [
            'id' => $id,
            'title' => $room['name'] ?: $this->toUtf8((string) $location['title']),
            'type' => (int) ($location['tipe'] ?? 0),
            'image' => $this->imageFor($id, $room['image']),
            'description' => $room['about'] ?: '',
            'npcs' => $room['npcs'],
        ];
    }

    private function formatPlaneLocation(int $userId, array $location): array
    {
        $status = $this->transport?->flightStatus($userId) ?? ['ok' => false];
        $flight = is_array($status['flight'] ?? null) ? $status['flight'] : null;
        $description = 'Вы находитесь на борту самолёта. Проводник подскажет, сколько осталось до прибытия.';
        if ($flight !== null) {
            $description = (string) ($flight['statusText'] ?? $description);
        }

        return [
            'id' => (int) $location['id'],
            'title' => $this->toUtf8((string) $location['title']),
            'type' => (int) ($location['tipe'] ?? 0),
            'image' => '/img/room/001.png',
            'description' => $description,
            'npcs' => [
                [
                    'title' => 'Проводник',
                    'icon' => 'person',
                    'params' => ['npc' => 'flight_status'],
                ],
                [
                    'title' => 'Выход',
                    'icon' => 'route',
                    'params' => ['npc' => 'flight_exit'],
                ],
            ],
        ];
    }

    private function imageFor(int $locationId, ?string $legacyImage): string
    {
        if ($legacyImage !== null && is_file($this->appRoot . '/' . ltrim($legacyImage, '/'))) {
            return $legacyImage;
        }

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
