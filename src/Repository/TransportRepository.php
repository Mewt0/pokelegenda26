<?php
declare(strict_types=1);

namespace Pokemon8\Repository;

use PDO;

final class TransportRepository
{
    public const SHIP_TICKET_ITEM_ID = 90110;
    public const PLANE_TICKET_ITEM_ID = 90111;
    public const COIN_ITEM_ID = 1;

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

    private function currentLocationId(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT buildmy FROM users WHERE id = :user LIMIT 1');
        $stmt->execute(['user' => $userId]);
        return (int) ($stmt->fetchColumn() ?: 0);
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
}
