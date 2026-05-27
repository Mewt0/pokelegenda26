<?php
declare(strict_types=1);

namespace Pokemon8\Repository;

use PDO;
use Throwable;

final class CommissionMarketRepository
{
    private const COIN_ITEM_ID = 1;
    private const RESERVE_USER_ID = 3;
    private const SYSTEM_LOGIN = 'Система';

    private const CATEGORY_LABELS = [
        'all' => 'Все',
        'pokemon' => 'Покемоны',
        'egg' => 'Яйца покемонов',
        'currency' => 'Валюта',
        'evolution' => 'Предметы эволюции',
        'held_item' => 'Предметы снаряжения',
        'craft' => 'Предметы крафта',
        'ticket' => 'Билеты',
        'mega_primal' => 'Праймал/Мега предметы',
        'tm' => 'TM-атаки',
        'gift_box' => 'Подарочные ящики',
        'other' => 'Прочие предметы',
    ];

    public function __construct(private PDO $db, private InventoryRepository $inventory)
    {
    }

    public function dashboard(int $userId): array
    {
        $this->importLegacyLots();
        $this->expireDueLots();

        return [
            'ok' => true,
            'categories' => $this->categories(),
            'settings' => $this->settings(),
            'lots' => $this->lots($userId)['lots'],
            'my' => $this->myLots($userId),
            'sellable' => $this->sellable($userId),
        ];
    }

    public function lots(int $userId, array $filters = []): array
    {
        $this->importLegacyLots();
        $this->expireDueLots();

        $category = (string) ($filters['category'] ?? 'all');
        $q = trim((string) ($filters['q'] ?? ''));
        $sort = (string) ($filters['sort'] ?? 'new');
        $page = max(1, (int) ($filters['page'] ?? 1));
        $perPage = max(10, min(80, (int) ($filters['per_page'] ?? 40)));
        $offset = ($page - 1) * $perPage;

        $where = ['status = "active"', 'expires_at > :now', '(private_buyer_id = 0 OR private_buyer_id = :user_private OR seller_id = :user_seller)'];
        $params = ['now' => time(), 'user_private' => $userId, 'user_seller' => $userId];
        if ($category !== '' && $category !== 'all') {
            $where[] = 'category = :category';
            $params['category'] = $category;
        }
        if ($q !== '') {
            if (ctype_digit($q)) {
                $where[] = '(id = :qid_lot OR object_id = :qid_object OR object_name LIKE :q_name)';
                $params['qid_lot'] = (int) $q;
                $params['qid_object'] = (int) $q;
                $params['q_name'] = '%' . $q . '%';
            } else {
                $where[] = '(object_name LIKE :q_name OR seller_name LIKE :q_seller OR object_snapshot_json LIKE :q_snapshot)';
                $params['q_name'] = '%' . $q . '%';
                $params['q_seller'] = '%' . $q . '%';
                $params['q_snapshot'] = '%' . $q . '%';
            }
        }

        $whereSql = implode(' AND ', $where);
        $order = match ($sort) {
            'price_asc' => 'total_price ASC, id DESC',
            'price_desc' => 'total_price DESC, id DESC',
            'unit_asc' => 'price_per_unit ASC, id DESC',
            'unit_desc' => 'price_per_unit DESC, id DESC',
            'old' => 'created_at ASC, id ASC',
            'ending' => 'expires_at ASC, id ASC',
            'name' => 'object_name ASC, id DESC',
            default => 'created_at DESC, id DESC',
        };

        $total = $this->countWhere('market_lots', $whereSql, $params);
        $stmt = $this->db->prepare('SELECT * FROM market_lots WHERE ' . $whereSql . ' ORDER BY ' . $order . ' LIMIT :limit OFFSET :offset');
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'ok' => true,
            'categories' => $this->categories(),
            'sort' => $sort,
            'category' => $category,
            'q' => $q,
            'page' => $page,
            'pages' => max(1, (int) ceil($total / $perPage)),
            'total' => $total,
            'lots' => array_map(fn (array $row): array => $this->formatLot($row, $userId), $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []),
        ];
    }

    public function sellable(int $userId): array
    {
        return [
            'ok' => true,
            'items' => $this->sellableItems($userId),
            'pokemon' => $this->sellablePokemon($userId),
            'eggs' => $this->sellableEggs($userId),
        ];
    }

    public function myLots(int $userId): array
    {
        $this->expireDueLots();
        $stmt = $this->db->prepare(
            'SELECT *
               FROM market_lots
              WHERE seller_id = :seller OR buyer_id = :buyer
           ORDER BY created_at DESC, id DESC
              LIMIT 160'
        );
        $stmt->execute(['seller' => $userId, 'buyer' => $userId]);
        $rows = array_map(fn (array $row): array => $this->formatLot($row, $userId), $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);

        return [
            'ok' => true,
            'active' => array_values(array_filter($rows, static fn (array $row): bool => $row['status'] === 'active')),
            'history' => array_values(array_filter($rows, static fn (array $row): bool => $row['status'] !== 'active')),
            'lots' => $rows,
        ];
    }

    public function createLot(int $userId, array $payload): array
    {
        $settings = $this->settings();
        if (!$settings['enabled']) {
            return ['ok' => false, 'message' => 'Комиссионная лавка временно выключена.'];
        }
        if ($this->userBusy($userId)) {
            return ['ok' => false, 'message' => 'Сначала закончите бой или обмен.'];
        }

        $type = (string) ($payload['object_type'] ?? $payload['type'] ?? '');
        $objectId = max(0, (int) ($payload['object_id'] ?? 0));
        $quantity = max(1, (int) ($payload['quantity'] ?? 1));
        $pricePerUnit = max(0, (int) ($payload['price_per_unit'] ?? $payload['price'] ?? 0));
        $durationHours = max(1, (int) ($payload['duration_hours'] ?? 24));
        $privateBuyerId = max(0, (int) ($payload['private_buyer_id'] ?? 0));

        if (!in_array($type, ['item', 'pokemon', 'egg'], true)) {
            return ['ok' => false, 'message' => 'Выберите предмет, покемона или яйцо.'];
        }
        if ($type === 'pokemon' && !$settings['allow_pokemon']) {
            return ['ok' => false, 'message' => 'Продажа покемонов сейчас запрещена.'];
        }
        if ($type === 'egg' && !$settings['allow_eggs']) {
            return ['ok' => false, 'message' => 'Продажа яиц сейчас запрещена.'];
        }
        if ($pricePerUnit < $settings['min_price'] || $pricePerUnit > $settings['max_price']) {
            return ['ok' => false, 'message' => 'Цена выходит за разрешённый диапазон.'];
        }
        if ($durationHours < $settings['min_hours'] || $durationHours > $settings['max_hours']) {
            return ['ok' => false, 'message' => 'Срок размещения выходит за настройки лавки.'];
        }
        if ($this->activeLotCount($userId) >= $settings['max_active_lots']) {
            return ['ok' => false, 'message' => 'Достигнут лимит активных лотов.'];
        }

        $seller = $this->userLogin($userId);
        $now = time();
        $expiresAt = $now + ($durationHours * 3600);
        $total = $pricePerUnit * $quantity;

        $startedTransaction = !$this->db->inTransaction();
        if ($startedTransaction) {
            $this->db->beginTransaction();
        }
        try {
            $prepared = match ($type) {
                'item' => $this->prepareItemLot($userId, $objectId, $quantity),
                'pokemon' => $this->preparePokemonLot($userId, $objectId),
                'egg' => $this->prepareEggLot($userId, $objectId),
            };
            if (!$prepared['ok']) {
                if ($startedTransaction) {
                    $this->db->rollBack();
                }
                return $prepared;
            }

            $stmt = $this->db->prepare(
                'INSERT INTO market_lots
                    (seller_id, seller_name, object_type, object_id, object_name, object_icon, category,
                     object_snapshot_json, reserve_payload_json, quantity, price_per_unit, total_price, status,
                     created_at, expires_at, private_buyer_id)
                 VALUES
                    (:seller, :seller_name, :type, :object_id, :name, :icon, :category,
                     :snapshot, :reserve, :quantity, :unit, :total, "active",
                     :created, :expires, :private_buyer)'
            );
            $stmt->execute([
                'seller' => $userId,
                'seller_name' => $seller,
                'type' => $type,
                'object_id' => $objectId,
                'name' => (string) $prepared['name'],
                'icon' => (string) $prepared['icon'],
                'category' => (string) $prepared['category'],
                'snapshot' => $this->jsonEncode($prepared['snapshot']),
                'reserve' => $this->jsonEncode($prepared['reserve']),
                'quantity' => $quantity,
                'unit' => $pricePerUnit,
                'total' => $total,
                'created' => $now,
                'expires' => $expiresAt,
                'private_buyer' => $privateBuyerId,
            ]);
            $lotId = (int) $this->db->lastInsertId();
            $this->log('create', $userId, $lotId, ['type' => $type, 'object_id' => $objectId, 'quantity' => $quantity, 'total' => $total]);
            $risk = $this->dealRisk([
                'object_type' => $type,
                'object_id' => $objectId,
                'object_name' => (string) $prepared['name'],
                'category' => (string) $prepared['category'],
                'quantity' => $quantity,
                'price_per_unit' => $pricePerUnit,
                'total_price' => $total,
            ]);
            if ($risk['is_risky']) {
                $this->log('risk.flagged', $userId, $lotId, $risk + [
                    'seller_id' => $userId,
                    'buyer_id' => 0,
                    'total' => $total,
                    'commission' => 0,
                ]);
            }
            $this->notify($userId, 'Лот выставлен', sprintf('Ваш лот "%s" выставлен в комиссионной лавке.', (string) $prepared['name']), ['lot_id' => $lotId]);
            if ($startedTransaction) {
                $this->db->commit();
            }
        } catch (Throwable $e) {
            if ($startedTransaction && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['ok' => false, 'message' => 'Лот не создан: ' . $e->getMessage()];
        }

        return ['ok' => true, 'message' => 'Лот успешно выставлен.', 'lot_id' => $lotId];
    }

    public function buy(int $buyerId, int $lotId): array
    {
        if ($this->userBusy($buyerId)) {
            return ['ok' => false, 'message' => 'Сначала закончите бой или обмен.'];
        }

        $startedTransaction = !$this->db->inTransaction();
        if ($startedTransaction) {
            $this->db->beginTransaction();
        }
        try {
            $stmt = $this->db->prepare('SELECT * FROM market_lots WHERE id = :id LIMIT 1 FOR UPDATE');
            $stmt->execute(['id' => $lotId]);
            $lot = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$lot || (string) ($lot['status'] ?? '') !== 'active') {
                if ($startedTransaction) {
                    $this->db->rollBack();
                }
                return ['ok' => false, 'message' => 'Лот уже продан, снят или истёк.'];
            }
            if ((int) ($lot['expires_at'] ?? 0) <= time()) {
                $this->expireLockedLot($lot, $buyerId);
                if ($startedTransaction) {
                    $this->db->commit();
                }
                return ['ok' => false, 'message' => 'Срок действия лота истёк.'];
            }
            $sellerId = (int) ($lot['seller_id'] ?? 0);
            if ($sellerId === $buyerId) {
                if ($startedTransaction) {
                    $this->db->rollBack();
                }
                return ['ok' => false, 'message' => 'Вы не можете купить собственный лот.'];
            }
            $privateBuyerId = (int) ($lot['private_buyer_id'] ?? 0);
            if ($privateBuyerId > 0 && $privateBuyerId !== $buyerId) {
                if ($startedTransaction) {
                    $this->db->rollBack();
                }
                return ['ok' => false, 'message' => 'Этот приватный лот предназначен другому игроку.'];
            }

            $total = max(0, (int) ($lot['total_price'] ?? 0));
            if (!$this->inventory->removeItem($buyerId, self::COIN_ITEM_ID, $total)) {
                if ($startedTransaction) {
                    $this->db->rollBack();
                }
                return ['ok' => false, 'message' => 'Недостаточно монет.'];
            }

            $this->transferLotToBuyer($lot, $buyerId);
            $commission = (int) floor($total * $this->settings()['commission_percent'] / 100);
            $sellerIncome = max(0, $total - $commission);
            if ($sellerIncome > 0) {
                $this->inventory->addItem($sellerId, self::COIN_ITEM_ID, $sellerIncome);
            }

            $this->db->prepare(
                'UPDATE market_lots
                    SET status = "sold", sold_at = :time, buyer_id = :buyer, commission_amount = :commission
                  WHERE id = :id LIMIT 1'
            )->execute(['time' => time(), 'buyer' => $buyerId, 'commission' => $commission, 'id' => $lotId]);

            $this->syncLegacyAfterFinalStatus($lot);
            $this->log('buy', $buyerId, $lotId, ['seller_id' => $sellerId, 'total' => $total, 'commission' => $commission]);
            $risk = $this->dealRisk($lot);
            if ($risk['is_risky'] && !$this->riskLogExists($lotId)) {
                $this->log('risk.flagged', $buyerId, $lotId, $risk + [
                    'seller_id' => $sellerId,
                    'buyer_id' => $buyerId,
                    'total' => $total,
                    'commission' => $commission,
                ]);
            }
            $this->notify($buyerId, 'Покупка завершена', sprintf('Вы купили "%s".', (string) ($lot['object_name'] ?? 'лот')), ['lot_id' => $lotId]);
            $this->notify($sellerId, 'Лот продан', sprintf('Ваш лот "%s" продан за %s монет.', (string) ($lot['object_name'] ?? 'лот'), number_format($sellerIncome, 0, ',', ' ')), ['lot_id' => $lotId]);
            if ($startedTransaction) {
                $this->db->commit();
            }
        } catch (Throwable $e) {
            if ($startedTransaction && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['ok' => false, 'message' => 'Покупка не выполнена: ' . $e->getMessage()];
        }

        return ['ok' => true, 'message' => 'Покупка успешно завершена.'];
    }

    public function cancel(int $userId, int $lotId): array
    {
        $startedTransaction = !$this->db->inTransaction();
        if ($startedTransaction) {
            $this->db->beginTransaction();
        }
        try {
            $stmt = $this->db->prepare('SELECT * FROM market_lots WHERE id = :id AND seller_id = :user LIMIT 1 FOR UPDATE');
            $stmt->execute(['id' => $lotId, 'user' => $userId]);
            $lot = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$lot || (string) ($lot['status'] ?? '') !== 'active') {
                if ($startedTransaction) {
                    $this->db->rollBack();
                }
                return ['ok' => false, 'message' => 'Активный лот не найден.'];
            }
            $this->returnLotToSeller($lot, 'cancelled');
            $this->db->prepare('UPDATE market_lots SET status = "cancelled" WHERE id = :id LIMIT 1')->execute(['id' => $lotId]);
            $this->syncLegacyAfterFinalStatus($lot);
            $this->log('cancel', $userId, $lotId, []);
            $this->notify($userId, 'Лот снят', sprintf('Лот "%s" снят с продажи.', (string) ($lot['object_name'] ?? 'лот')), ['lot_id' => $lotId]);
            if ($startedTransaction) {
                $this->db->commit();
            }
        } catch (Throwable $e) {
            if ($startedTransaction && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['ok' => false, 'message' => 'Не удалось снять лот: ' . $e->getMessage()];
        }

        return ['ok' => true, 'message' => 'Лот снят с продажи.'];
    }

    public function adminSettings(): array
    {
        return ['ok' => true, 'settings' => $this->settings()];
    }

    public function saveAdminSettings(int $adminId, array $payload): array
    {
        $allowed = [
            'commission.enabled',
            'commission.commission_percent',
            'commission.min_price',
            'commission.max_price',
            'commission.min_hours',
            'commission.max_hours',
            'commission.max_active_lots',
            'commission.allow_pokemon',
            'commission.allow_eggs',
            'commission.allow_currency',
            'commission.hide_egg_species',
        ];
        foreach ($allowed as $key) {
            if (!array_key_exists($key, $payload)) {
                continue;
            }
            $stmt = $this->db->prepare(
                'INSERT INTO site_settings (name, value, updated_by, updated_at)
                 VALUES (:name, :value, :admin, :time)
                 ON DUPLICATE KEY UPDATE value = VALUES(value), updated_by = VALUES(updated_by), updated_at = VALUES(updated_at)'
            );
            $stmt->execute([
                'name' => $key,
                'value' => (string) $payload[$key],
                'admin' => $adminId,
                'time' => time(),
            ]);
        }
        $this->log('settings.update', $adminId, 0, ['keys' => array_values(array_intersect(array_keys($payload), $allowed))]);
        return ['ok' => true, 'message' => 'Настройки комиссионной лавки сохранены.', 'settings' => $this->settings()];
    }

    public function categories(): array
    {
        $rows = [];
        foreach (self::CATEGORY_LABELS as $key => $label) {
            $rows[] = ['key' => $key, 'label' => $label];
        }
        return $rows;
    }

    private function prepareItemLot(int $userId, int $itemId, int $quantity): array
    {
        $sellable = $this->sellableItems($userId);
        $item = null;
        foreach ($sellable as $row) {
            if ((int) $row['id'] === $itemId) {
                $item = $row;
                break;
            }
        }
        if ($item === null) {
            return ['ok' => false, 'message' => 'Этот предмет нельзя выставить в лавку.'];
        }
        if ($quantity > (int) $item['count']) {
            return ['ok' => false, 'message' => 'Недостаточно предметов для выставления.'];
        }
        if (!$this->inventory->removeItem($userId, $itemId, $quantity)) {
            return ['ok' => false, 'message' => 'Предмет не удалось зарезервировать.'];
        }

        return [
            'ok' => true,
            'name' => (string) $item['name'],
            'icon' => (string) $item['icon'],
            'category' => (string) $item['category'],
            'snapshot' => $item + ['preview' => $item['preview']],
            'reserve' => ['item_id' => $itemId, 'quantity' => $quantity],
        ];
    }

    private function preparePokemonLot(int $userId, int $pokemonId): array
    {
        $stmt = $this->db->prepare(
            'SELECT pu.*, pb.title AS base_title, ip.id_items AS held_item_id, hi.name AS held_item_name
               FROM pok_user pu
          LEFT JOIN poke_base pb ON pb.id = pu.basenum
          LEFT JOIN items_poke ip ON ip.id_poke = pu.id
          LEFT JOIN items hi ON hi.id = ip.id_items
              WHERE pu.id = :pokemon AND pu.users = :user
              LIMIT 1
              FOR UPDATE'
        );
        $stmt->execute(['pokemon' => $pokemonId, 'user' => $userId]);
        $pokemon = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$pokemon) {
            return ['ok' => false, 'message' => 'Покемон не найден.'];
        }
        if ((int) ($pokemon['startone'] ?? 0) > 0) {
            return ['ok' => false, 'message' => 'Этот покемон запрещён к продаже.'];
        }
        if ((int) ($pokemon['active'] ?? 0) === 1 && $this->activePokemonCount($userId) <= 1) {
            return ['ok' => false, 'message' => 'Нельзя выставить последнего активного покемона.'];
        }

        $this->db->prepare('UPDATE pok_user SET users = :reserve, active = 0, startepoke = 0 WHERE id = :pokemon AND users = :user LIMIT 1')
            ->execute(['reserve' => self::RESERVE_USER_ID, 'pokemon' => $pokemonId, 'user' => $userId]);

        $baseId = (int) ($pokemon['basenum'] ?? 0);
        $name = strip_tags((string) ($pokemon['names'] ?? $this->cleanBaseName((string) ($pokemon['base_title'] ?? ''), $baseId)));
        $snapshot = $this->pokemonSnapshot($pokemon);
        return [
            'ok' => true,
            'name' => $name,
            'icon' => $this->pokemonIcon($baseId, $name),
            'category' => 'pokemon',
            'snapshot' => $snapshot,
            'reserve' => ['pokemon_id' => $pokemonId],
        ];
    }

    private function prepareEggLot(int $userId, int $eggId): array
    {
        $stmt = $this->db->prepare(
            'SELECT e.*, pb.title AS base_title, ap.atac_name AS egg_attack_name
               FROM eggs e
          LEFT JOIN poke_base pb ON pb.id = e.base_id_egg
          LEFT JOIN attac_power ap ON ap.atac_id = e.attac_one
              WHERE e.id_egg = :egg AND e.users_egg = :user
              LIMIT 1
              FOR UPDATE'
        );
        $stmt->execute(['egg' => $eggId, 'user' => $userId]);
        $egg = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$egg) {
            return ['ok' => false, 'message' => 'Яйцо не найдено.'];
        }
        $this->db->prepare('UPDATE eggs SET users_egg = :reserve WHERE id_egg = :egg AND users_egg = :user LIMIT 1')
            ->execute(['reserve' => self::RESERVE_USER_ID, 'egg' => $eggId, 'user' => $userId]);
        $baseId = (int) ($egg['base_id_egg'] ?? 0);
        $name = 'Яйцо #' . $baseId . ' ' . $this->cleanBaseName((string) ($egg['base_title'] ?? ''), $baseId);
        return [
            'ok' => true,
            'name' => $name,
            'icon' => '/public/img/ui/chatgpt-pokeball.png',
            'category' => 'egg',
            'snapshot' => $this->eggSnapshot($egg),
            'reserve' => ['egg_id' => $eggId],
        ];
    }

    private function transferLotToBuyer(array $lot, int $buyerId): void
    {
        $type = (string) ($lot['object_type'] ?? '');
        $objectId = (int) ($lot['object_id'] ?? 0);
        if ($type === 'item') {
            $this->inventory->addItem($buyerId, $objectId, max(1, (int) ($lot['quantity'] ?? 1)));
            return;
        }
        if ($type === 'pokemon') {
            $active = $this->activePokemonCount($buyerId) < 6 ? 1 : 0;
            $stmt = $this->db->prepare('UPDATE pok_user SET users = :buyer, active = :active, startepoke = 0 WHERE id = :pokemon AND users = :reserve LIMIT 1');
            $stmt->execute(['buyer' => $buyerId, 'active' => $active, 'pokemon' => $objectId, 'reserve' => self::RESERVE_USER_ID]);
            if ($stmt->rowCount() !== 1) {
                throw new \RuntimeException('Покемон в резерве не найден.');
            }
            return;
        }
        if ($type === 'egg') {
            $stmt = $this->db->prepare('UPDATE eggs SET users_egg = :buyer WHERE id_egg = :egg AND users_egg = :reserve LIMIT 1');
            $stmt->execute(['buyer' => $buyerId, 'egg' => $objectId, 'reserve' => self::RESERVE_USER_ID]);
            if ($stmt->rowCount() !== 1) {
                throw new \RuntimeException('Яйцо в резерве не найдено.');
            }
            return;
        }
        throw new \RuntimeException('Неизвестный тип лота.');
    }

    private function returnLotToSeller(array $lot, string $reason): void
    {
        $sellerId = (int) ($lot['seller_id'] ?? 0);
        try {
            $type = (string) ($lot['object_type'] ?? '');
            $objectId = (int) ($lot['object_id'] ?? 0);
            if ($type === 'item') {
                $this->inventory->addItem($sellerId, $objectId, max(1, (int) ($lot['quantity'] ?? 1)));
            } elseif ($type === 'pokemon') {
                $active = $this->activePokemonCount($sellerId) < 6 ? 1 : 0;
                $stmt = $this->db->prepare('UPDATE pok_user SET users = :seller, active = :active, startepoke = 0 WHERE id = :pokemon AND users = :reserve LIMIT 1');
                $stmt->execute(['seller' => $sellerId, 'active' => $active, 'pokemon' => $objectId, 'reserve' => self::RESERVE_USER_ID]);
                if ($stmt->rowCount() !== 1) {
                    throw new \RuntimeException('Покемон в резерве не найден.');
                }
            } elseif ($type === 'egg') {
                $stmt = $this->db->prepare('UPDATE eggs SET users_egg = :seller WHERE id_egg = :egg AND users_egg = :reserve LIMIT 1');
                $stmt->execute(['seller' => $sellerId, 'egg' => $objectId, 'reserve' => self::RESERVE_USER_ID]);
                if ($stmt->rowCount() !== 1) {
                    throw new \RuntimeException('Яйцо в резерве не найдено.');
                }
            }
        } catch (Throwable $e) {
            $this->storeReturn($sellerId, (int) ($lot['id'] ?? 0), (string) ($lot['object_type'] ?? ''), (int) ($lot['object_id'] ?? 0), (int) ($lot['quantity'] ?? 1), $reason, $e->getMessage());
        }
    }

    private function expireDueLots(): void
    {
        $stmt = $this->db->prepare('SELECT * FROM market_lots WHERE status = "active" AND expires_at <= :time ORDER BY expires_at ASC LIMIT 50');
        $stmt->execute(['time' => time()]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $lot) {
            $this->db->beginTransaction();
            try {
                $lock = $this->db->prepare('SELECT * FROM market_lots WHERE id = :id AND status = "active" LIMIT 1 FOR UPDATE');
                $lock->execute(['id' => (int) $lot['id']]);
                $locked = $lock->fetch(PDO::FETCH_ASSOC);
                if ($locked && (int) ($locked['expires_at'] ?? 0) <= time()) {
                    $this->expireLockedLot($locked, 0);
                }
                $this->db->commit();
            } catch (Throwable) {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
            }
        }
    }

    private function expireLockedLot(array $lot, int $actorId): void
    {
        $this->returnLotToSeller($lot, 'expired');
        $this->db->prepare('UPDATE market_lots SET status = "expired" WHERE id = :id LIMIT 1')->execute(['id' => (int) $lot['id']]);
        $this->syncLegacyAfterFinalStatus($lot);
        $this->log('expire', $actorId, (int) $lot['id'], []);
        $this->notify((int) $lot['seller_id'], 'Срок лота истёк', sprintf('Лот "%s" возвращён.', (string) ($lot['object_name'] ?? 'лот')), ['lot_id' => (int) $lot['id']]);
    }

    private function sellableItems(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT iu.item_id, SUM(iu.count) AS count, i.name, i.tittle, i.category, i.uses, i.dress, i.torg, i.battleuse, i.dopolnen,
                    m.category_key, m.description, m.target_use_rule, m.effect_key, m.effect_status, m.compatibility_rule, m.safe_to_equip
               FROM items_users iu
               JOIN items i ON i.id = iu.item_id
          LEFT JOIN item_gameplay_metadata m ON m.item_id = iu.item_id
              WHERE iu.user_id = :user
                AND iu.count > 0
                AND iu.dattimer = "not"
           GROUP BY iu.item_id, i.name, i.tittle, i.category, i.uses, i.dress, i.torg, i.battleuse, i.dopolnen,
                    m.category_key, m.description, m.target_use_rule, m.effect_key, m.effect_status, m.compatibility_rule, m.safe_to_equip
           ORDER BY i.id ASC'
        );
        $stmt->execute(['user' => $userId]);
        $equipped = $this->equippedItemCounts($userId);
        $rows = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $itemId = (int) ($row['item_id'] ?? 0);
            $count = max(0, (int) ($row['count'] ?? 0) - (int) ($equipped[$itemId] ?? 0));
            if ($count <= 0 || !$this->itemAllowedForSale($row)) {
                continue;
            }
            $category = $this->itemCommissionCategory($row);
            $rows[] = [
                'type' => 'item',
                'id' => $itemId,
                'name' => $this->cleanItemName($row),
                'count' => $count,
                'category' => $category,
                'category_label' => self::CATEGORY_LABELS[$category] ?? self::CATEGORY_LABELS['other'],
                'icon' => $this->itemIcon($itemId),
                'preview' => [
                    'description' => (string) ($row['description'] ?? ''),
                    'effect_status' => (string) ($row['effect_status'] ?? 'unknown'),
                    'target_use_rule' => (string) ($row['target_use_rule'] ?? ''),
                    'compatibility' => (string) ($row['compatibility_rule'] ?? 'none'),
                    'equippable' => (int) ($row['dress'] ?? 0) > 0 || (int) ($row['safe_to_equip'] ?? 0) > 0,
                ],
            ];
        }
        return $rows;
    }

    private function sellablePokemon(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT pu.id, pu.names, pu.basenum, pu.lvl, pu.sex, pu.tips, pu.active, pu.har,
                    pu.hp_iv, pu.atk_iv, pu.def_iv, pu.satk_iv, pu.sdef_iv, pu.speed_iv,
                    pu.hp_ev, pu.atk_ev, pu.def_ev, pu.satk_ev, pu.sdef_ev, pu.speed_ev,
                    pb.title AS base_title, ip.id_items AS held_item_id, i.name AS held_item_name
               FROM pok_user pu
          LEFT JOIN poke_base pb ON pb.id = pu.basenum
          LEFT JOIN items_poke ip ON ip.id_poke = pu.id
          LEFT JOIN items i ON i.id = ip.id_items
              WHERE pu.users = :user AND pu.startone = 0
           ORDER BY pu.active DESC, pu.startepoke DESC, pu.id ASC'
        );
        $stmt->execute(['user' => $userId]);
        $activeCount = $this->activePokemonCount($userId);
        $rows = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $baseId = (int) ($row['basenum'] ?? 0);
            $name = strip_tags((string) ($row['names'] ?? $this->cleanBaseName((string) ($row['base_title'] ?? ''), $baseId)));
            $isActive = (int) ($row['active'] ?? 0) === 1;
            $rows[] = [
                'type' => 'pokemon',
                'id' => (int) ($row['id'] ?? 0),
                'name' => $name,
                'base_id' => $baseId,
                'level' => (int) ($row['lvl'] ?? 0),
                'active' => $isActive,
                'blocked' => $isActive && $activeCount <= 1,
                'blocked_reason' => $isActive && $activeCount <= 1 ? 'Нельзя продать последнего активного покемона.' : '',
                'icon' => $this->pokemonIcon($baseId, $name),
                'preview' => $this->pokemonSnapshot($row),
            ];
        }
        return $rows;
    }

    private function sellableEggs(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT e.*, pb.title AS base_title, ap.atac_name AS egg_attack_name
               FROM eggs e
          LEFT JOIN poke_base pb ON pb.id = e.base_id_egg
          LEFT JOIN attac_power ap ON ap.atac_id = e.attac_one
              WHERE e.users_egg = :user
           ORDER BY e.dtime ASC, e.id_egg ASC'
        );
        $stmt->execute(['user' => $userId]);
        $rows = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $baseId = (int) ($row['base_id_egg'] ?? 0);
            $rows[] = [
                'type' => 'egg',
                'id' => (int) ($row['id_egg'] ?? 0),
                'name' => 'Яйцо #' . $baseId . ' ' . $this->cleanBaseName((string) ($row['base_title'] ?? ''), $baseId),
                'base_id' => $baseId,
                'icon' => '/public/img/ui/chatgpt-pokeball.png',
                'preview' => $this->eggSnapshot($row),
            ];
        }
        return $rows;
    }

    private function itemAllowedForSale(array $row): bool
    {
        $itemId = (int) ($row['item_id'] ?? 0);
        if (in_array($itemId, [self::COIN_ITEM_ID, 2], true)) {
            return false;
        }
        $name = mb_strtolower($this->cleanItemName($row), 'UTF-8');
        foreach (['ягод', 'berry', 'зель', 'potion', 'энергетик', 'напиток', 'молоко'] as $banned) {
            if (str_contains($name, $banned)) {
                return false;
            }
        }
        $lockText = mb_strtolower(implode(' ', [
            (string) ($row['dopolnen'] ?? ''),
            (string) ($row['category_key'] ?? ''),
            (string) ($row['target_use_rule'] ?? ''),
            (string) ($row['effect_key'] ?? ''),
            (string) ($row['compatibility_rule'] ?? ''),
        ]), 'UTF-8');
        foreach (['quest', 'bound', 'blocked', 'personal', 'soulbound', 'no_trade', 'notrade', 'account_bound', 'квест', 'привяз', 'нельзя перед'] as $marker) {
            if (str_contains($lockText, $marker)) {
                return false;
            }
        }
        if ((int) ($row['safe_to_trade'] ?? 1) === 0) {
            return false;
        }
        $metadataCategory = (string) ($row['category_key'] ?? '');
        if (in_array($metadataCategory, ['held_item', 'gift_box', 'evolution', 'ticket', 'vitamin', 'utility'], true)) {
            return true;
        }
        return (int) ($row['torg'] ?? 0) === 1 || preg_match('/tm|mega|orb|ticket|билет|камень|осколок|подар/u', $name) === 1;
    }

    private function itemCommissionCategory(array $row): string
    {
        $itemId = (int) ($row['item_id'] ?? 0);
        if (in_array($itemId, [90200, 90201], true) || ($itemId >= 91000 && $itemId < 91200)) {
            return 'mega_primal';
        }
        $metadata = (string) ($row['category_key'] ?? '');
        if ($metadata === 'held_item') {
            return 'held_item';
        }
        if ($metadata === 'gift_box') {
            return 'gift_box';
        }
        if ($metadata === 'ticket') {
            return 'ticket';
        }
        if ($metadata === 'evolution') {
            return 'evolution';
        }
        $name = mb_strtolower($this->cleanItemName($row), 'UTF-8');
        if (str_contains($name, 'tm') || str_contains($name, 'атака')) {
            return 'tm';
        }
        if (str_contains($name, 'билет') || str_contains($name, 'ticket')) {
            return 'ticket';
        }
        if (str_contains($name, 'камень') || str_contains($name, 'stone') || str_contains((string) ($row['dopolnen'] ?? ''), 'evolution')) {
            return 'evolution';
        }
        if (str_contains($name, 'осколок') || str_contains($name, 'руда') || str_contains($name, 'кристалл')) {
            return 'craft';
        }
        return 'other';
    }

    private function formatLot(array $row, int $userId): array
    {
        $snapshot = $this->jsonDecode((string) ($row['object_snapshot_json'] ?? ''));
        return [
            'id' => (int) ($row['id'] ?? 0),
            'seller_id' => (int) ($row['seller_id'] ?? 0),
            'seller_name' => (string) ($row['seller_name'] ?? ''),
            'object_type' => (string) ($row['object_type'] ?? ''),
            'object_id' => (int) ($row['object_id'] ?? 0),
            'object_name' => (string) ($row['object_name'] ?? ''),
            'object_icon' => (string) ($row['object_icon'] ?? ''),
            'category' => (string) ($row['category'] ?? 'other'),
            'category_label' => self::CATEGORY_LABELS[(string) ($row['category'] ?? 'other')] ?? self::CATEGORY_LABELS['other'],
            'quantity' => (int) ($row['quantity'] ?? 0),
            'price_per_unit' => (int) ($row['price_per_unit'] ?? 0),
            'total_price' => (int) ($row['total_price'] ?? 0),
            'status' => (string) ($row['status'] ?? ''),
            'created_at' => (int) ($row['created_at'] ?? 0),
            'expires_at' => (int) ($row['expires_at'] ?? 0),
            'sold_at' => (int) ($row['sold_at'] ?? 0),
            'buyer_id' => (int) ($row['buyer_id'] ?? 0),
            'commission_amount' => (int) ($row['commission_amount'] ?? 0),
            'is_own' => (int) ($row['seller_id'] ?? 0) === $userId,
            'can_buy' => (int) ($row['seller_id'] ?? 0) !== $userId && (string) ($row['status'] ?? '') === 'active',
            'preview' => $snapshot,
            'legacy' => [
                'type' => (string) ($row['legacy_source_type'] ?? ''),
                'id' => (int) ($row['legacy_source_id'] ?? 0),
            ],
        ];
    }

    private function pokemonSnapshot(array $row): array
    {
        $pokemonId = (int) ($row['id'] ?? 0);
        return [
            'base_id' => (int) ($row['basenum'] ?? 0),
            'level' => (int) ($row['lvl'] ?? 0),
            'sex' => (int) ($row['sex'] ?? 0),
            'shiny' => (string) ($row['tips'] ?? 'normal') === 'shine',
            'nature' => !empty($row['nature_name']) ? (string) $row['nature_name'] : ('Характер #' . (int) ($row['har'] ?? 0)),
            'active' => (int) ($row['active'] ?? 0) === 1,
            'held_item' => [
                'id' => (int) ($row['held_item_id'] ?? 0),
                'name' => (string) ($row['held_item_name'] ?? ''),
            ],
            'iv' => [
                'hp' => (int) ($row['hp_iv'] ?? 0),
                'atk' => (int) ($row['atk_iv'] ?? 0),
                'def' => (int) ($row['def_iv'] ?? 0),
                'satk' => (int) ($row['satk_iv'] ?? 0),
                'sdef' => (int) ($row['sdef_iv'] ?? 0),
                'speed' => (int) ($row['speed_iv'] ?? 0),
            ],
            'ev' => [
                'hp' => (int) ($row['hp_ev'] ?? 0),
                'atk' => (int) ($row['atk_ev'] ?? 0),
                'def' => (int) ($row['def_ev'] ?? 0),
                'satk' => (int) ($row['satk_ev'] ?? 0),
                'sdef' => (int) ($row['sdef_ev'] ?? 0),
                'speed' => (int) ($row['speed_ev'] ?? 0),
            ],
            'moves' => $pokemonId > 0 ? $this->pokemonMoves($pokemonId) : [],
        ];
    }

    private function eggSnapshot(array $row): array
    {
        $readyAt = (int) ($row['dtime'] ?? 0);
        return [
            'base_id' => (int) ($row['base_id_egg'] ?? 0),
            'name' => $this->cleanBaseName((string) ($row['base_title'] ?? ''), (int) ($row['base_id_egg'] ?? 0)),
            'ready_at' => $readyAt,
            'remaining_seconds' => max(0, $readyAt - time()),
            'egg_attack_id' => (int) ($row['attac_one'] ?? 0),
            'egg_attack_name' => (string) ($row['egg_attack_name'] ?? ''),
            'tips' => (string) ($row['tips'] ?? 'normal'),
            'iv' => [
                'hp' => (int) ($row['hp_iv'] ?? 0),
                'atk' => (int) ($row['atk_iv'] ?? 0),
                'def' => (int) ($row['def_iv'] ?? 0),
                'satk' => (int) ($row['satk_iv'] ?? 0),
                'sdef' => (int) ($row['sdef_iv'] ?? 0),
                'speed' => (int) ($row['speed_iv'] ?? 0),
            ],
        ];
    }

    private function pokemonMoves(int $pokemonId): array
    {
        $stmt = $this->db->prepare(
            'SELECT a.a_id, ap1.atac_name AS a_name, a.b_id, ap2.atac_name AS b_name, a.c_id, ap3.atac_name AS c_name, a.d_id, ap4.atac_name AS d_name
               FROM attac_my_poke a
          LEFT JOIN attac_power ap1 ON ap1.atac_id = a.a_id
          LEFT JOIN attac_power ap2 ON ap2.atac_id = a.b_id
          LEFT JOIN attac_power ap3 ON ap3.atac_id = a.c_id
          LEFT JOIN attac_power ap4 ON ap4.atac_id = a.d_id
              WHERE a.pok_id = :pokemon
              LIMIT 1'
        );
        $stmt->execute(['pokemon' => $pokemonId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        $moves = [];
        foreach (['a', 'b', 'c', 'd'] as $slot) {
            $id = (int) ($row[$slot . '_id'] ?? 0);
            if ($id > 0) {
                $moves[] = ['id' => $id, 'name' => (string) ($row[$slot . '_name'] ?? ('Attack #' . $id))];
            }
        }
        return $moves;
    }

    private function importLegacyLots(): void
    {
        $now = time();
        $pokemonRows = $this->db->query(
            'SELECT rp.*, u.login AS seller_name, pu.names, pu.basenum, pu.lvl, pu.tips, pb.title AS base_title
               FROM rinok_poke rp
          LEFT JOIN users u ON u.id = rp.user_pok
          LEFT JOIN pok_user pu ON pu.id = rp.id_poke
          LEFT JOIN poke_base pb ON pb.id = pu.basenum
              WHERE rp.dateend > ' . $now . '
              LIMIT 200'
        )->fetchAll(PDO::FETCH_ASSOC) ?: [];
        foreach ($pokemonRows as $row) {
            $exists = $this->legacyExists('rinok_poke', (int) ($row['id_lot'] ?? 0));
            if ($exists) {
                continue;
            }
            $baseId = (int) ($row['basenum'] ?? 0);
            $name = strip_tags((string) ($row['names'] ?? $this->cleanBaseName((string) ($row['base_title'] ?? ''), $baseId)));
            $this->insertLegacyLot([
                'seller_id' => (int) ($row['user_pok'] ?? 0),
                'seller_name' => (string) ($row['seller_name'] ?? ''),
                'object_type' => 'pokemon',
                'object_id' => (int) ($row['id_poke'] ?? 0),
                'object_name' => $name,
                'object_icon' => $this->pokemonIcon($baseId, $name),
                'category' => 'pokemon',
                'quantity' => 1,
                'price_per_unit' => (int) ($row['maney'] ?? 0),
                'total_price' => (int) ($row['maney'] ?? 0),
                'created_at' => max(1, $now - 3600),
                'expires_at' => (int) ($row['dateend'] ?? $now + 86400),
                'private_buyer_id' => ctype_digit((string) ($row['user_id_to'] ?? '')) ? (int) $row['user_id_to'] : 0,
                'legacy_source_type' => 'rinok_poke',
                'legacy_source_id' => (int) ($row['id_lot'] ?? 0),
                'object_snapshot_json' => $this->jsonEncode(['base_id' => $baseId, 'level' => (int) ($row['lvl'] ?? 0), 'shiny' => (string) ($row['tips'] ?? '') === 'shine']),
            ]);
        }

        $itemRows = $this->db->query(
            'SELECT a.*, u.login AS seller_name, i.name, i.tittle, i.category, i.dopolnen, m.category_key, m.effect_status
               FROM auction_items a
          LEFT JOIN users u ON u.id = a.user_id
          LEFT JOIN items i ON i.id = a.item_id
          LEFT JOIN item_gameplay_metadata m ON m.item_id = a.item_id
              WHERE a.time_rinok > ' . $now . '
              LIMIT 200'
        )->fetchAll(PDO::FETCH_ASSOC) ?: [];
        foreach ($itemRows as $row) {
            $exists = $this->legacyExists('auction_items', (int) ($row['id_lot'] ?? 0));
            if ($exists || !$this->itemAllowedForSale($row + ['item_id' => (int) ($row['item_id'] ?? 0)])) {
                continue;
            }
            $category = $this->itemCommissionCategory($row + ['item_id' => (int) ($row['item_id'] ?? 0)]);
            $unit = max(1, (int) ($row['cena'] ?? 0));
            $qty = max(1, (int) ($row['count'] ?? 1));
            $itemId = (int) ($row['item_id'] ?? 0);
            $this->insertLegacyLot([
                'seller_id' => (int) ($row['user_id'] ?? 0),
                'seller_name' => (string) ($row['seller_name'] ?? ''),
                'object_type' => 'item',
                'object_id' => $itemId,
                'object_name' => $this->cleanItemName($row),
                'object_icon' => $this->itemIcon($itemId),
                'category' => $category,
                'quantity' => $qty,
                'price_per_unit' => $unit,
                'total_price' => $unit * $qty,
                'created_at' => max(1, strtotime((string) ($row['created'] ?? '')) ?: $now),
                'expires_at' => (int) ($row['time_rinok'] ?? $now + 86400),
                'private_buyer_id' => ctype_digit((string) ($row['user_id_to'] ?? '')) ? (int) $row['user_id_to'] : 0,
                'legacy_source_type' => 'auction_items',
                'legacy_source_id' => (int) ($row['id_lot'] ?? 0),
                'object_snapshot_json' => $this->jsonEncode(['effect_status' => (string) ($row['effect_status'] ?? ''), 'category' => $category]),
            ]);
        }
    }

    private function insertLegacyLot(array $row): void
    {
        $stmt = $this->db->prepare(
            'INSERT IGNORE INTO market_lots
                (seller_id, seller_name, object_type, object_id, object_name, object_icon, category,
                 object_snapshot_json, quantity, price_per_unit, total_price, status, created_at, expires_at,
                 private_buyer_id, legacy_source_type, legacy_source_id)
             VALUES
                (:seller_id, :seller_name, :object_type, :object_id, :object_name, :object_icon, :category,
                 :snapshot, :quantity, :unit, :total, "active", :created_at, :expires_at,
                 :private_buyer_id, :legacy_type, :legacy_id)'
        );
        $stmt->execute([
            'seller_id' => $row['seller_id'],
            'seller_name' => $row['seller_name'],
            'object_type' => $row['object_type'],
            'object_id' => $row['object_id'],
            'object_name' => $row['object_name'],
            'object_icon' => $row['object_icon'],
            'category' => $row['category'],
            'snapshot' => $row['object_snapshot_json'] ?? '{}',
            'quantity' => $row['quantity'],
            'unit' => $row['price_per_unit'],
            'total' => $row['total_price'],
            'created_at' => $row['created_at'],
            'expires_at' => $row['expires_at'],
            'private_buyer_id' => $row['private_buyer_id'],
            'legacy_type' => $row['legacy_source_type'],
            'legacy_id' => $row['legacy_source_id'],
        ]);
    }

    private function legacyExists(string $type, int $id): bool
    {
        if ($id <= 0) {
            return true;
        }
        $stmt = $this->db->prepare('SELECT 1 FROM market_lots WHERE legacy_source_type = :type AND legacy_source_id = :id LIMIT 1');
        $stmt->execute(['type' => $type, 'id' => $id]);
        return (bool) $stmt->fetchColumn();
    }

    private function syncLegacyAfterFinalStatus(array $lot): void
    {
        $type = (string) ($lot['legacy_source_type'] ?? '');
        $id = (int) ($lot['legacy_source_id'] ?? 0);
        if ($type === 'rinok_poke' && $id > 0) {
            $this->db->prepare('DELETE FROM rinok_poke WHERE id_lot = :id LIMIT 1')->execute(['id' => $id]);
        }
        if ($type === 'auction_items' && $id > 0) {
            $this->db->prepare('DELETE FROM auction_items WHERE id_lot = :id LIMIT 1')->execute(['id' => $id]);
        }
    }

    private function settings(): array
    {
        $defaults = [
            'enabled' => true,
            'commission_percent' => 5,
            'min_price' => 1,
            'max_price' => 999999999,
            'min_hours' => 24,
            'max_hours' => 72,
            'max_active_lots' => 20,
            'allow_pokemon' => true,
            'allow_eggs' => true,
            'allow_currency' => false,
            'hide_egg_species' => false,
        ];
        $stmt = $this->db->query('SELECT name, value FROM site_settings WHERE name LIKE "commission.%"');
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $key = str_replace('commission.', '', (string) ($row['name'] ?? ''));
            if (!array_key_exists($key, $defaults)) {
                continue;
            }
            $value = (string) ($row['value'] ?? '');
            if (is_bool($defaults[$key])) {
                $defaults[$key] = $value === '1' || mb_strtolower($value) === 'true';
            } else {
                $defaults[$key] = max(0, (int) $value);
            }
        }
        return $defaults;
    }

    private function activeLotCount(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM market_lots WHERE seller_id = :user AND status = "active" AND expires_at > :time');
        $stmt->execute(['user' => $userId, 'time' => time()]);
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    private function countWhere(string $table, string $whereSql, array $params): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM ' . $table . ' WHERE ' . $whereSql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    private function equippedItemCounts(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT ip.id_items, COUNT(*) AS qty
               FROM items_poke ip
               JOIN pok_user pu ON pu.id = ip.id_poke
              WHERE pu.users = :user
           GROUP BY ip.id_items'
        );
        $stmt->execute(['user' => $userId]);
        $map = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $map[(int) $row['id_items']] = (int) $row['qty'];
        }
        return $map;
    }

    private function activePokemonCount(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM pok_user WHERE users = :user AND active = 1');
        $stmt->execute(['user' => $userId]);
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    private function userBusy(int $userId): bool
    {
        $stmt = $this->db->prepare('SELECT pve, pvp, trade FROM users WHERE id = :user LIMIT 1');
        $stmt->execute(['user' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        return (int) ($row['pve'] ?? 0) > 0 || (int) ($row['pvp'] ?? 0) > 0 || (int) ($row['trade'] ?? 0) > 0;
    }

    private function userLogin(int $userId): string
    {
        $stmt = $this->db->prepare('SELECT login FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $userId]);
        return (string) ($stmt->fetchColumn() ?: ('#' . $userId));
    }

    private function notify(int $userId, string $title, string $message, array $payload = []): void
    {
        if ($userId <= 0) {
            return;
        }
        $systemId = $this->systemUserId();
        $payload = ['actor_id' => $systemId, 'actor_login' => self::SYSTEM_LOGIN] + $payload;
        $message = $this->messageWithActor($message);

        $this->db->prepare(
            'INSERT INTO game_notifications (id, user_id, title, message, variant, payload_json, source, created_at, read_at)
             VALUES (:id, :user, :title, :message, "info", :payload, "Система", :time, 0)'
        )->execute([
            'id' => $this->nextTableId('game_notifications', 'id'),
            'user' => $userId,
            'title' => $title,
            'message' => $message,
            'payload' => $this->jsonEncode($payload),
            'time' => time(),
        ]);
    }

    private function messageWithActor(string $message): string
    {
        $message = trim($message);
        $normalized = mb_strtolower($message, 'UTF-8');
        $actor = mb_strtolower(self::SYSTEM_LOGIN, 'UTF-8');
        if (str_starts_with($normalized, $actor . ':') || str_starts_with($normalized, $actor . ' :')) {
            return $message;
        }

        return self::SYSTEM_LOGIN . ': ' . $message;
    }

    private function systemUserId(): int
    {
        $stmt = $this->db->prepare('SELECT id FROM users WHERE login = :login ORDER BY id ASC LIMIT 1');
        $stmt->execute(['login' => self::SYSTEM_LOGIN]);
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    private function dealRisk(array $lot): array
    {
        $flags = [];
        $score = 0;
        $objectType = (string) ($lot['object_type'] ?? '');
        $category = (string) ($lot['category'] ?? '');
        $name = mb_strtolower((string) ($lot['object_name'] ?? ''), 'UTF-8');
        $quantity = max(1, (int) ($lot['quantity'] ?? 1));
        $unit = max(0, (int) ($lot['price_per_unit'] ?? 0));
        $total = max(0, (int) ($lot['total_price'] ?? ($unit * $quantity)));

        if ($total >= 50_000_000) {
            $flags[] = 'Сумма сделки 50 млн+';
            $score += 60;
        }
        if ($unit >= 10_000_000) {
            $flags[] = 'Цена за штуку 10 млн+';
            $score += 35;
        }
        $isEasyItem = $objectType === 'item'
            && ($this->isPokeballName($name) || in_array((int) ($lot['object_id'] ?? 0), [3, 25, 90004, 90005], true));
        if ($isEasyItem && ($total >= 1_000_000 || $unit >= 500_000)) {
            $flags[] = 'Легкодоступный предмет выставлен/куплен слишком дорого';
            $score += 55;
        }
        if ($objectType === 'item' && in_array($category, ['other', 'craft', 'ticket'], true) && $unit >= 5_000_000) {
            $flags[] = 'Утилитарный предмет с высокой ценой';
            $score += 25;
        }
        if ($quantity >= 100 && $unit >= 250_000) {
            $flags[] = 'Большой стак с высокой ценой за штуку';
            $score += 30;
        }

        return [
            'is_risky' => $flags !== [],
            'risk_score' => min(100, $score),
            'risk_flags' => $flags,
            'risk_label' => $flags === [] ? 'ОК' : implode('; ', $flags),
        ];
    }

    private function isPokeballName(string $name): bool
    {
        return (bool) preg_match('/поке.?бол|мастер.?бол|ультра.?бол|премиум.?бол|грит.?бол|great.?ball|ultra.?ball|master.?ball|ball|шар/ui', $name);
    }

    private function riskLogExists(int $lotId): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM market_logs WHERE lot_id = :lot AND action = "risk.flagged" LIMIT 1');
        $stmt->execute(['lot' => $lotId]);
        return (bool) $stmt->fetchColumn();
    }

    private function log(string $action, int $actorId, int $lotId, array $data): void
    {
        $this->db->prepare(
            'INSERT INTO market_logs (action, actor_id, lot_id, data_json, created_at)
             VALUES (:action, :actor, :lot, :data, :time)'
        )->execute([
            'action' => $action,
            'actor' => $actorId,
            'lot' => $lotId,
            'data' => $this->jsonEncode($data),
            'time' => time(),
        ]);
    }

    private function storeReturn(int $userId, int $lotId, string $type, int $objectId, int $quantity, string $reason, string $error): void
    {
        $this->db->prepare(
            'INSERT INTO market_return_storage (user_id, lot_id, object_type, object_id, quantity, payload_json, status, created_at, resolved_at)
             VALUES (:user, :lot, :type, :object, :qty, :payload, "pending", :time, 0)'
        )->execute([
            'user' => $userId,
            'lot' => $lotId,
            'type' => $type,
            'object' => $objectId,
            'qty' => max(1, $quantity),
            'payload' => $this->jsonEncode(['reason' => $reason, 'error' => $error]),
            'time' => time(),
        ]);
    }

    private function nextTableId(string $table, string $column): int
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table) || !preg_match('/^[a-zA-Z0-9_]+$/', $column)) {
            return 1;
        }
        return (int) ($this->db->query('SELECT COALESCE(MAX(`' . $column . '`), 0) + 1 FROM `' . $table . '`')->fetchColumn() ?: 1);
    }

    private function cleanItemName(array $row): string
    {
        $name = trim(strip_tags((string) ($row['name'] ?? $row['tittle'] ?? '')));
        return $name !== '' ? $name : ('Предмет #' . (int) ($row['item_id'] ?? 0));
    }

    private function cleanBaseName(string $title, int $baseId): string
    {
        $title = trim(html_entity_decode(strip_tags($title), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $title = preg_replace('/^#?0*' . $baseId . '\s*/u', '', $title) ?: $title;
        $title = preg_replace('/^#?0*\d+\s*/u', '', $title) ?: $title;
        return trim($title) !== '' ? trim($title) : ('Pokemon #' . $baseId);
    }

    private function itemIcon(int $itemId): string
    {
        return '/public/img/items/' . $itemId . '.png';
    }

    private function pokemonIcon(int $baseId, string $name = ''): string
    {
        return '/Pok/spriteanim/' . max(1, $baseId) . '.gif';
    }

    private function jsonEncode(array $data): string
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
    }

    private function jsonDecode(string $json): array
    {
        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : [];
    }
}
