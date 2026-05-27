<?php
declare(strict_types=1);

namespace Pokemon8\Repository;

use PDO;
use Throwable;

final class TransportRepository
{
    public const SHIP_TICKET_ITEM_ID = 90110;
    public const PLANE_TICKET_ITEM_ID = 90111;
    public const COIN_ITEM_ID = 1;
    public const PLANE_LOCATION_ID = 95001;
    public const DEFAULT_FLIGHT_DURATION_SECONDS = 900;

    public function __construct(private PDO $db, private InventoryRepository $inventory)
    {
    }

    public function routesForUser(int $userId): array
    {
        $locationId = $this->currentLocationId($userId);
        if ($locationId <= 0) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT tr.*, src.title AS from_title, dst.title AS to_title, i.name AS item_name, i.tittle AS item_title
               FROM transport_routes tr
               INNER JOIN build src ON src.id = tr.from_location_id
               INNER JOIN build dst ON dst.id = tr.to_location_id
               LEFT JOIN items i ON i.id = tr.item_id
              WHERE tr.enabled = 1 AND tr.from_location_id = :location
              ORDER BY tr.kind ASC, tr.id ASC'
        );
        $stmt->execute(['location' => $locationId]);

        $routes = [];
        foreach ($stmt->fetchAll() ?: [] as $row) {
            $routes[] = $this->formatRoute($row);
        }
        return $routes;
    }

    public function travel(int $userId, int $routeId): array
    {
        if ($userId <= 0 || $routeId <= 0) {
            return ['ok' => false, 'message' => 'Маршрут не выбран.'];
        }
        if ($this->userIsBusy($userId)) {
            return ['ok' => false, 'message' => 'Сначала закончите бой или обмен.'];
        }

        $locationId = $this->currentLocationId($userId);
        $stmt = $this->db->prepare(
            'SELECT tr.*, src.title AS from_title, dst.title AS to_title, i.name AS item_name, i.tittle AS item_title
               FROM transport_routes tr
               INNER JOIN build src ON src.id = tr.from_location_id
               INNER JOIN build dst ON dst.id = tr.to_location_id
               LEFT JOIN items i ON i.id = tr.item_id
              WHERE tr.id = :id AND tr.enabled = 1
              LIMIT 1'
        );
        $stmt->execute(['id' => $routeId]);
        $route = $stmt->fetch();
        if (!$route || (int) ($route['from_location_id'] ?? 0) !== $locationId) {
            return ['ok' => false, 'message' => 'Этот рейс недоступен из текущей локации.'];
        }
        if ($this->isAdministrativeLocation((string) ($route['to_title'] ?? ''))) {
            return ['ok' => false, 'message' => 'В административную локацию транспорт не ходит.'];
        }

        $priceItemId = (int) ($route['price_item_id'] ?? self::COIN_ITEM_ID);
        $priceCount = max(0, (int) ($route['price_count'] ?? 0));
        if ($priceCount > 0 && !$this->inventory->removeItem($userId, $priceItemId, $priceCount)) {
            return ['ok' => false, 'message' => $priceItemId === self::COIN_ITEM_ID ? 'Не хватает монет на билет.' : 'Не хватает предметов для поездки.'];
        }

        $this->db->prepare('UPDATE users SET buildmy = :location WHERE id = :user LIMIT 1')
            ->execute(['location' => (int) $route['to_location_id'], 'user' => $userId]);

        return [
            'ok' => true,
            'message' => sprintf('Вы отправились: %s.', (string) ($route['title'] ?? 'рейс')),
            'route' => $this->formatRoute($route),
            'locationId' => (int) $route['to_location_id'],
        ];
    }

    public function planeDestinationsForUser(int $userId): array
    {
        if ($userId <= 0) {
            return [];
        }
        if ($this->activeFlight($userId) !== null) {
            return [];
        }

        $location = $this->currentLocationRow($userId);
        if ($location === null || (int) ($location['location_id'] ?? 0) === self::PLANE_LOCATION_ID) {
            return [];
        }

        $region = (int) ($location['region'] ?? 0);
        $stmt = $this->db->prepare(
            'SELECT r.*, b.title AS to_title
              FROM transport_flight_routes r
         INNER JOIN build b ON b.id = r.to_location_id
              WHERE r.enabled = 1
                AND (r.from_region = 0 OR r.from_region = :region)
                AND (:region_filter_min <= 0 OR r.to_region <> :region_filter_value)
              ORDER BY r.to_region ASC, r.id ASC'
        );
        $stmt->execute([
            'region' => $region,
            'region_filter_min' => $region,
            'region_filter_value' => $region,
        ]);

        $routes = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $routes[] = $this->formatFlightRoute($row);
        }

        return $routes;
    }

    public function startFlight(int $userId, int $itemUserId, int $routeId): array
    {
        if ($userId <= 0 || $itemUserId <= 0 || $routeId <= 0) {
            return ['ok' => false, 'message' => 'Выберите билет и рейс.'];
        }
        if ($this->userIsBusy($userId)) {
            return ['ok' => false, 'message' => 'Сначала закончите бой или обмен.'];
        }

        $location = $this->currentLocationRow($userId);
        if ($location === null) {
            return ['ok' => false, 'message' => 'Игрок не найден.'];
        }
        if ((int) ($location['location_id'] ?? 0) === self::PLANE_LOCATION_ID) {
            return ['ok' => false, 'message' => 'Вы уже на борту самолёта.'];
        }

        $this->db->beginTransaction();
        try {
            if ($this->activeFlight($userId, true) !== null) {
                $this->db->rollBack();
                return ['ok' => false, 'message' => 'У вас уже есть активный перелёт.'];
            }

            $route = $this->findFlightRoute($routeId, true);
            if ($route === null) {
                $this->db->rollBack();
                return ['ok' => false, 'message' => 'Рейс не найден или выключен.'];
            }

            $region = (int) ($location['region'] ?? 0);
            $fromRegion = (int) ($route['from_region'] ?? 0);
            if ($fromRegion > 0 && $region !== $fromRegion) {
                $this->db->rollBack();
                return ['ok' => false, 'message' => 'Этот рейс недоступен из текущего региона.'];
            }
            if ($region > 0 && (int) ($route['to_region'] ?? 0) === $region) {
                $this->db->rollBack();
                return ['ok' => false, 'message' => 'Вы уже находитесь в этом регионе.'];
            }
            if ($this->isAdministrativeLocation((string) ($route['to_title'] ?? ''))) {
                $this->db->rollBack();
                return ['ok' => false, 'message' => 'В административную локацию самолёт не летит.'];
            }

            $itemStmt = $this->db->prepare(
                'SELECT id, count
                   FROM items_users
                  WHERE id = :id
                    AND user_id = :user
                    AND item_id = :item
                    AND count > 0
                    AND (dattimer = "not" OR (dattimer REGEXP "^[0-9]+$" AND CAST(dattimer AS UNSIGNED) > :time))
                  LIMIT 1
                  FOR UPDATE'
            );
            $itemStmt->execute([
                'id' => $itemUserId,
                'user' => $userId,
                'item' => self::PLANE_TICKET_ITEM_ID,
                'time' => time(),
            ]);
            if (!$itemStmt->fetch(PDO::FETCH_ASSOC)) {
                $this->db->rollBack();
                return ['ok' => false, 'message' => 'Билет на самолёт не найден в инвентаре.'];
            }

            $now = time();
            $duration = max(60, (int) ($route['duration_seconds'] ?? self::DEFAULT_FLIGHT_DURATION_SECONDS));
            $insert = $this->db->prepare(
                'INSERT INTO transport_flights
                    (user_id, item_user_id, route_id, from_location_id, from_region, to_region,
                     destination_location_id, status, started_at, arrives_at, exited_at)
                 VALUES
                    (:user, :item_user, :route, :from_location, :from_region, :to_region,
                     :destination, "active", :started, :arrives, 0)'
            );
            $insert->execute([
                'user' => $userId,
                'item_user' => $itemUserId,
                'route' => $routeId,
                'from_location' => (int) ($location['location_id'] ?? 0),
                'from_region' => $region,
                'to_region' => (int) ($route['to_region'] ?? 0),
                'destination' => (int) ($route['to_location_id'] ?? 0),
                'started' => $now,
                'arrives' => $now + $duration,
            ]);

            $this->db->prepare('UPDATE users SET buildmy = :location WHERE id = :user LIMIT 1')
                ->execute(['location' => self::PLANE_LOCATION_ID, 'user' => $userId]);

            $flight = $this->activeFlight($userId, true);
            $this->db->commit();

            return [
                'ok' => true,
                'message' => sprintf('Посадка завершена. %s, полёт займёт %s.', (string) ($route['title'] ?? 'Рейс'), $this->formatDuration($duration)),
                'flight' => $flight !== null ? $this->formatFlight($flight) : null,
                'locationId' => self::PLANE_LOCATION_ID,
            ];
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['ok' => false, 'message' => 'Не удалось оформить посадку на самолёт.'];
        }
    }

    public function flightStatus(int $userId): array
    {
        $flight = $this->activeFlight($userId);
        if ($flight === null) {
            return ['ok' => false, 'message' => 'Активного перелёта нет.'];
        }

        return ['ok' => true, 'flight' => $this->formatFlight($flight)];
    }

    public function exitFlight(int $userId): array
    {
        if ($userId <= 0) {
            return ['ok' => false, 'message' => 'Нужно войти в игру.'];
        }

        $this->db->beginTransaction();
        try {
            $flight = $this->activeFlight($userId, true);
            if ($flight === null) {
                $this->db->rollBack();
                return ['ok' => false, 'message' => 'Активного перелёта нет.'];
            }

            $remaining = max(0, (int) ($flight['arrives_at'] ?? 0) - time());
            if ($remaining > 0) {
                $this->db->rollBack();
                return [
                    'ok' => false,
                    'message' => 'Мы ещё летим.',
                    'remainingSeconds' => $remaining,
                    'flight' => $this->formatFlight($flight),
                ];
            }

            if (!$this->inventory->removeItem($userId, self::PLANE_TICKET_ITEM_ID, 1)) {
                $this->db->rollBack();
                return ['ok' => false, 'message' => 'Билет не найден. Без билета выйти из самолёта нельзя.'];
            }

            $destination = (int) ($flight['destination_location_id'] ?? 0);
            $this->db->prepare('UPDATE users SET buildmy = :location WHERE id = :user LIMIT 1')
                ->execute(['location' => $destination, 'user' => $userId]);
            $this->db->prepare(
                'UPDATE transport_flights
                    SET status = "completed", exited_at = :time
                  WHERE id = :id AND user_id = :user
                  LIMIT 1'
            )->execute([
                'time' => time(),
                'id' => (int) $flight['id'],
                'user' => $userId,
            ]);

            $this->db->commit();

            return [
                'ok' => true,
                'message' => sprintf('Самолёт прибыл. Вы вышли в локацию: %s.', $this->toUtf8((string) ($flight['destination_title'] ?? 'регион'))),
                'locationId' => $destination,
            ];
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['ok' => false, 'message' => 'Не удалось выйти из самолёта.'];
        }
    }

    private function formatRoute(array $row): array
    {
        $itemId = (int) ($row['item_id'] ?? 0);
        return [
            'id' => (int) ($row['id'] ?? 0),
            'kind' => (string) ($row['kind'] ?? ''),
            'title' => (string) ($row['title'] ?? ''),
            'fromLocationId' => (int) ($row['from_location_id'] ?? 0),
            'fromTitle' => (string) ($row['from_title'] ?? ''),
            'toLocationId' => (int) ($row['to_location_id'] ?? 0),
            'toTitle' => (string) ($row['to_title'] ?? ''),
            'itemId' => $itemId,
            'itemName' => (string) (($row['item_name'] ?? '') ?: ($row['title'] ?? 'Билет')),
            'itemTitle' => (string) ($row['item_title'] ?? ''),
            'itemIcon' => $itemId > 0 ? '/public/img/items/' . $itemId . '.png' : '/public/img/ui/menu-map.png',
            'priceItemId' => (int) ($row['price_item_id'] ?? self::COIN_ITEM_ID),
            'priceCount' => (int) ($row['price_count'] ?? 0),
        ];
    }

    private function formatFlightRoute(array $row): array
    {
        $duration = max(60, (int) ($row['duration_seconds'] ?? self::DEFAULT_FLIGHT_DURATION_SECONDS));
        return [
            'id' => (int) ($row['id'] ?? 0),
            'title' => $this->toUtf8((string) ($row['title'] ?? 'Рейс')),
            'description' => $this->toUtf8((string) ($row['description'] ?? '')),
            'fromRegion' => (int) ($row['from_region'] ?? 0),
            'toRegion' => (int) ($row['to_region'] ?? 0),
            'toRegionLabel' => $this->regionLabel((int) ($row['to_region'] ?? 0)),
            'toLocationId' => (int) ($row['to_location_id'] ?? 0),
            'toTitle' => $this->toUtf8((string) ($row['to_title'] ?? '')),
            'durationSeconds' => $duration,
            'durationText' => $this->formatDuration($duration),
        ];
    }

    private function formatFlight(array $row): array
    {
        $remaining = max(0, (int) ($row['arrives_at'] ?? 0) - time());
        $routeTitle = $this->toUtf8((string) ($row['route_title'] ?? 'Рейс'));
        $destinationTitle = $this->toUtf8((string) ($row['destination_title'] ?? 'регион'));

        return [
            'id' => (int) ($row['id'] ?? 0),
            'routeId' => (int) ($row['route_id'] ?? 0),
            'title' => $routeTitle,
            'description' => $this->toUtf8((string) ($row['route_description'] ?? '')),
            'fromLocationId' => (int) ($row['from_location_id'] ?? 0),
            'fromRegion' => (int) ($row['from_region'] ?? 0),
            'toRegion' => (int) ($row['to_region'] ?? 0),
            'toRegionLabel' => $this->regionLabel((int) ($row['to_region'] ?? 0)),
            'destinationLocationId' => (int) ($row['destination_location_id'] ?? 0),
            'destinationTitle' => $destinationTitle,
            'startedAt' => (int) ($row['started_at'] ?? 0),
            'arrivesAt' => (int) ($row['arrives_at'] ?? 0),
            'remainingSeconds' => $remaining,
            'remainingText' => $this->formatDuration($remaining),
            'canExit' => $remaining <= 0,
            'statusText' => $remaining <= 0
                ? sprintf('Самолёт прибыл в %s. Можно выходить.', $destinationTitle)
                : sprintf('Самолёт летит в %s. До прибытия осталось %s.', $destinationTitle, $this->formatDuration($remaining)),
        ];
    }

    private function currentLocationId(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT buildmy FROM users WHERE id = :user LIMIT 1');
        $stmt->execute(['user' => $userId]);
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    private function currentLocationRow(int $userId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT u.buildmy AS location_id, COALESCE(b.town, 0) AS region, b.title AS location_title
               FROM users u
          LEFT JOIN build b ON b.id = u.buildmy
              WHERE u.id = :user
              LIMIT 1'
        );
        $stmt->execute(['user' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    private function findFlightRoute(int $routeId, bool $forUpdate = false): ?array
    {
        $sql = 'SELECT r.*, b.title AS to_title
                  FROM transport_flight_routes r
            INNER JOIN build b ON b.id = r.to_location_id
                 WHERE r.id = :id AND r.enabled = 1
                 LIMIT 1';
        if ($forUpdate) {
            $sql .= ' FOR UPDATE';
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $routeId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    private function activeFlight(int $userId, bool $forUpdate = false): ?array
    {
        $sql = 'SELECT f.*, r.title AS route_title, r.description AS route_description,
                       r.duration_seconds, b.title AS destination_title
                  FROM transport_flights f
             LEFT JOIN transport_flight_routes r ON r.id = f.route_id
             LEFT JOIN build b ON b.id = f.destination_location_id
                 WHERE f.user_id = :user AND f.status = "active"
                 ORDER BY f.id DESC
                 LIMIT 1';
        if ($forUpdate) {
            $sql .= ' FOR UPDATE';
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    private function userIsBusy(int $userId): bool
    {
        $stmt = $this->db->prepare('SELECT pve, pvp, trade FROM users WHERE id = :user LIMIT 1');
        $stmt->execute(['user' => $userId]);
        $user = $stmt->fetch();
        return !$user || (int) ($user['pve'] ?? 0) > 0 || (int) ($user['pvp'] ?? 0) > 0 || (int) ($user['trade'] ?? 0) > 0;
    }

    private function isAdministrativeLocation(string $title): bool
    {
        $name = mb_strtolower($title, 'UTF-8');
        foreach (['админист', 'зона админ', 'тюрьм'] as $word) {
            if (str_contains($name, $word)) {
                return true;
            }
        }
        return false;
    }

    private function regionLabel(int $region): string
    {
        return match ($region) {
            1 => 'Канто',
            3 => 'Джотто',
            4 => 'Хоэнн',
            default => 'Регион #' . $region,
        };
    }

    private function formatDuration(int $seconds): string
    {
        $seconds = max(0, $seconds);
        $minutes = intdiv($seconds, 60);
        $rest = $seconds % 60;
        if ($minutes <= 0) {
            return $rest . ' сек';
        }
        if ($rest === 0) {
            return $minutes . ' мин';
        }
        return $minutes . ' мин ' . $rest . ' сек';
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
