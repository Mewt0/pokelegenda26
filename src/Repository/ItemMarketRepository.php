<?php
declare(strict_types=1);

namespace Pokemon8\Repository;

use PDO;
use Throwable;

final class ItemMarketRepository
{
    private const COIN_ITEM_ID = 1;

    public function __construct(
        private PDO $db,
        private InventoryRepository $inventory,
    ) {
    }

    public function catalog(int $userId, string $search = '', int $limit = 80): array
    {
        $limit = max(1, min(200, $limit));
        $sql = 'SELECT ms.id, ms.item_id, ms.currency_item_id, ms.price, ms.min_count, ms.max_count,
                       ms.stock, ms.enabled, ms.note, i.name, i.tittle, i.category,
                       ci.name AS currency_name
                  FROM market_shop_items ms
            INNER JOIN items i ON i.id = ms.item_id
             LEFT JOIN items ci ON ci.id = ms.currency_item_id
                 WHERE ms.enabled = 1';
        $params = [];

        if ($search !== '') {
            $sql .= ' AND (i.id = :id_search OR i.name LIKE :search OR i.tittle LIKE :search)';
            $params['id_search'] = ctype_digit($search) ? (int) $search : -1;
            $params['search'] = '%' . $search . '%';
        }

        $sql .= ' ORDER BY ms.sort_order ASC, i.id ASC LIMIT ' . $limit;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $rows = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $rows[] = $this->formatCatalogRow($row, $userId);
        }

        return $rows;
    }

    public function lots(int $userId, string $search = '', int $limit = 80): array
    {
        $limit = max(1, min(200, $limit));
        $region = $this->regionForUser($userId);
        $sql = 'SELECT a.id_lot, a.tip_item, a.cena, a.count, a.user_id, a.user_id_to, a.created,
                       a.time_rinok, i.name, i.tittle, u.login AS seller_login
                  FROM auction_items a
            INNER JOIN items i ON i.id = a.tip_item
             LEFT JOIN users u ON u.id = a.user_id
                 WHERE a.egg = 0
                   AND a.regions = :region
                   AND a.count > 0
                   AND a.time_rinok > :now
                   AND (a.user_id_to = "no" OR a.user_id_to = :user_id_text)';
        $params = [
            'region' => $region,
            'now' => time(),
            'user_id_text' => (string) $userId,
        ];

        if ($search !== '') {
            $sql .= ' AND (i.id = :id_search OR i.name LIKE :search OR i.tittle LIKE :search)';
            $params['id_search'] = ctype_digit($search) ? (int) $search : -1;
            $params['search'] = '%' . $search . '%';
        }

        $sql .= ' ORDER BY a.tip_item ASC, a.cena ASC LIMIT ' . $limit;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $rows = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $rows[] = $this->formatLotRow($row, $userId);
        }

        return $rows;
    }

    public function buyCatalogItem(int $userId, int $shopItemId, int $count): array
    {
        $count = max(1, min(999, $count));
        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare(
                'SELECT ms.*, i.name, ci.name AS currency_name
                   FROM market_shop_items ms
             INNER JOIN items i ON i.id = ms.item_id
              LEFT JOIN items ci ON ci.id = ms.currency_item_id
                  WHERE ms.id = :id AND ms.enabled = 1
                  LIMIT 1
                  FOR UPDATE'
            );
            $stmt->execute(['id' => $shopItemId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                $this->db->rollBack();
                return ['ok' => false, 'message' => 'Товар не найден или снят с продажи.'];
            }

            $min = max(1, (int) ($row['min_count'] ?? 1));
            $max = max($min, (int) ($row['max_count'] ?? $min));
            if ($count < $min || $count > $max) {
                $this->db->rollBack();
                return ['ok' => false, 'message' => sprintf('Можно купить от %d до %d шт.', $min, $max)];
            }

            $stock = (int) ($row['stock'] ?? -1);
            if ($stock >= 0 && $stock < $count) {
                $this->db->rollBack();
                return ['ok' => false, 'message' => 'На складе нет такого количества.'];
            }

            $currencyItemId = (int) ($row['currency_item_id'] ?? self::COIN_ITEM_ID);
            $total = max(1, (int) ($row['price'] ?? 0)) * $count;
            if (!$this->inventory->removeItem($userId, $currencyItemId, $total)) {
                $this->db->rollBack();
                return ['ok' => false, 'message' => 'Не хватает валюты для покупки.'];
            }

            $this->inventory->addItem($userId, (int) $row['item_id'], $count);
            if ($stock >= 0) {
                $update = $this->db->prepare('UPDATE market_shop_items SET stock = stock - :count, updated_at = :time WHERE id = :id');
                $update->execute(['count' => $count, 'time' => time(), 'id' => $shopItemId]);
            }

            $this->db->commit();
            return [
                'ok' => true,
                'message' => sprintf('Куплено: %s x%d за %s %s.', (string) $row['name'], $count, number_format($total, 0, ',', ' '), (string) ($row['currency_name'] ?? 'монет')),
                'item' => $this->formatCatalogRow($row, $userId),
                'wallet' => [
                    'currency_item_id' => $currencyItemId,
                    'count' => $this->inventory->countItem($userId, $currencyItemId),
                ],
            ];
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['ok' => false, 'message' => 'Не удалось завершить покупку.'];
        }
    }

    public function buyLot(int $userId, int $lotId, int $amount): array
    {
        $amount = max(1, min(999, $amount));
        $region = $this->regionForUser($userId);
        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare(
                'SELECT a.*, i.name
                   FROM auction_items a
             INNER JOIN items i ON i.id = a.tip_item
                  WHERE a.id_lot = :lot AND a.egg = 0
                  LIMIT 1
                  FOR UPDATE'
            );
            $stmt->execute(['lot' => $lotId]);
            $lot = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$lot || (int) $lot['regions'] !== $region || (int) $lot['count'] <= 0 || (int) $lot['time_rinok'] <= time()) {
                $this->db->rollBack();
                return ['ok' => false, 'message' => 'Лот не найден, истек или недоступен в этом регионе.'];
            }

            if ((int) $lot['user_id'] === $userId) {
                $this->db->rollBack();
                return ['ok' => false, 'message' => 'Свой лот покупать нельзя.'];
            }

            $targetBuyer = (string) ($lot['user_id_to'] ?? 'no');
            if ($targetBuyer !== 'no' && $targetBuyer !== (string) $userId) {
                $this->db->rollBack();
                return ['ok' => false, 'message' => 'Этот лот выставлен для другого игрока.'];
            }

            $available = (int) $lot['count'];
            if ($amount > $available) {
                $this->db->rollBack();
                return ['ok' => false, 'message' => 'В лоте нет такого количества предметов.'];
            }

            $unitPrice = (int) ceil(((int) $lot['cena']) / max(1, $available));
            $total = $unitPrice * $amount;
            if (!$this->inventory->removeItem($userId, self::COIN_ITEM_ID, $total)) {
                $this->db->rollBack();
                return ['ok' => false, 'message' => 'Не хватает монет для покупки лота.'];
            }

            $this->inventory->addItem($userId, (int) $lot['tip_item'], $amount);
            $tax = (int) round($total * 0.05);
            $sellerIncome = max(0, $total - $tax);
            if ((int) $lot['user_id'] > 0 && $sellerIncome > 0) {
                $this->inventory->addItem((int) $lot['user_id'], self::COIN_ITEM_ID, $sellerIncome);
            }

            $remaining = $available - $amount;
            if ($remaining > 0) {
                $update = $this->db->prepare('UPDATE auction_items SET count = :count, cena = :price WHERE id_lot = :lot');
                $update->execute([
                    'count' => $remaining,
                    'price' => $remaining * $unitPrice,
                    'lot' => $lotId,
                ]);
            } else {
                $delete = $this->db->prepare('DELETE FROM auction_items WHERE id_lot = :lot LIMIT 1');
                $delete->execute(['lot' => $lotId]);
            }

            $this->db->commit();
            return [
                'ok' => true,
                'message' => sprintf('Лот куплен: %s x%d за %s монет.', (string) $lot['name'], $amount, number_format($total, 0, ',', ' ')),
                'wallet' => [
                    'currency_item_id' => self::COIN_ITEM_ID,
                    'count' => $this->inventory->countItem($userId, self::COIN_ITEM_ID),
                ],
            ];
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['ok' => false, 'message' => 'Не удалось купить лот.'];
        }
    }

    public function wallet(int $userId): array
    {
        return [
            'coins' => $this->inventory->countItem($userId, self::COIN_ITEM_ID),
            'diamonds' => $this->inventory->countItem($userId, 2),
        ];
    }

    private function formatCatalogRow(array $row, int $userId): array
    {
        $price = max(0, (int) ($row['price'] ?? 0));
        return [
            'id' => (int) ($row['id'] ?? 0),
            'item_id' => (int) ($row['item_id'] ?? 0),
            'name' => (string) ($row['name'] ?? 'Предмет'),
            'description' => $this->plain((string) ($row['tittle'] ?? $row['note'] ?? '')),
            'price' => $price,
            'currency_item_id' => (int) ($row['currency_item_id'] ?? self::COIN_ITEM_ID),
            'currency_name' => (string) ($row['currency_name'] ?? 'Монета'),
            'min_count' => max(1, (int) ($row['min_count'] ?? 1)),
            'max_count' => max(1, (int) ($row['max_count'] ?? 99)),
            'stock' => (int) ($row['stock'] ?? -1),
            'owned' => $this->inventory->countItem($userId, (int) ($row['item_id'] ?? 0)),
            'icon' => $this->itemIconPath((int) ($row['item_id'] ?? 0)),
        ];
    }

    private function formatLotRow(array $row, int $userId): array
    {
        $count = max(1, (int) ($row['count'] ?? 1));
        $price = max(1, (int) ($row['cena'] ?? 0));
        return [
            'id_lot' => (int) ($row['id_lot'] ?? 0),
            'item_id' => (int) ($row['tip_item'] ?? 0),
            'name' => (string) ($row['name'] ?? 'Предмет'),
            'description' => $this->plain((string) ($row['tittle'] ?? '')),
            'count' => $count,
            'price' => $price,
            'unit_price' => (int) ceil($price / $count),
            'seller_id' => (int) ($row['user_id'] ?? 0),
            'seller_login' => (string) ($row['seller_login'] ?? 'Игрок'),
            'is_own' => (int) ($row['user_id'] ?? 0) === $userId,
            'expires_at' => (int) ($row['time_rinok'] ?? 0),
            'icon' => $this->itemIconPath((int) ($row['tip_item'] ?? 0)),
        ];
    }

    private function regionForUser(int $userId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COALESCE(NULLIF(b.town, 0), 1) AS region
               FROM users u
          LEFT JOIN build b ON b.id = u.buildmy
              WHERE u.id = :id
              LIMIT 1'
        );
        $stmt->execute(['id' => $userId]);

        return max(1, (int) ($stmt->fetchColumn() ?: 1));
    }

    private function itemIconPath(int $itemId): string
    {
        $path = dirname(__DIR__, 2) . '/public/img/items/' . $itemId . '.png';
        if ($itemId > 0 && is_file($path)) {
            return '/public/img/items/' . $itemId . '.png';
        }

        return '/public/img/items/3.png';
    }

    private function plain(string $value): string
    {
        $value = trim(strip_tags($value));
        return $value === '' ? 'Описание пока не заполнено.' : $value;
    }
}
