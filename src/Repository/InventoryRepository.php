<?php
declare(strict_types=1);

namespace Pokemon8\Repository;

use PDO;

final class InventoryRepository
{
    public function __construct(private PDO $db)
    {
    }

    public function countItem(int $userId, int $itemId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COALESCE(SUM(count), 0)
               FROM items_users
              WHERE user_id = :user
                AND item_id = :item
                AND (dattimer = "not" OR (dattimer REGEXP "^[0-9]+$" AND CAST(dattimer AS UNSIGNED) > :time))'
        );
        $stmt->execute([
            'user' => $userId,
            'item' => $itemId,
            'time' => time(),
        ]);

        return max(0, (int) $stmt->fetchColumn());
    }

    public function hasItem(int $userId, int $itemId, int $count): bool
    {
        return $this->countItem($userId, $itemId) >= $count;
    }

    public function countForUser(int $userId, string $category = '', string $query = ''): int
    {
        [$where, $params] = $this->inventoryFilterSql($userId, $category, $query);
        $now = time();
        $stmt = $this->db->prepare(
            'SELECT COUNT(*)
               FROM items_users iu
               INNER JOIN items i ON i.id = iu.item_id
              WHERE iu.user_id = ' . $userId . '
                AND (iu.dattimer = "not" OR (iu.dattimer REGEXP "^[0-9]+$" AND CAST(iu.dattimer AS UNSIGNED) > ' . $now . '))' . $where
        );
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function listForUser(int $userId, int $limit, int $offset, string $category = '', string $query = ''): array
    {
        [$where, $params] = $this->inventoryFilterSql($userId, $category, $query);
        $now = time();
        $stmt = $this->db->prepare(
            'SELECT iu.id, iu.item_id, iu.count, iu.dattimer, iu.timers, i.name, i.tittle, i.category, i.delet, i.dress, i.uses, i.elementary, i.battleuse, i.dopolnen,
                    itr.enabled AS target_enabled, itr.target_type, itr.allow_quantity, itr.min_count AS target_min_count,
                    itr.max_count AS target_max_count, itr.effect_key, itr.ui_title, itr.ui_hint
             FROM items_users iu
             INNER JOIN items i ON i.id = iu.item_id
             LEFT JOIN item_target_rules itr ON itr.item_id = iu.item_id AND itr.enabled = 1
             WHERE iu.user_id = ' . $userId . '
               AND (iu.dattimer = "not" OR (iu.dattimer REGEXP "^[0-9]+$" AND CAST(iu.dattimer AS UNSIGNED) > ' . $now . '))' . $where . '
             ORDER BY iu.item_id ASC
             LIMIT :limit OFFSET :offset'
        );
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $offset), PDO::PARAM_INT);
        $stmt->execute();

        $items = $stmt->fetchAll();
        if (!is_array($items)) {
            return [];
        }

        return array_map(fn (array $row): array => $this->formatInventoryRow($row), $items);
    }

    public function listBattleItemsForUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT iu.id, iu.item_id, iu.count, iu.dattimer, iu.timers, i.name, i.tittle, i.category, i.delet, i.dress, i.uses, i.elementary, i.battleuse
             FROM items_users iu
             INNER JOIN items i ON i.id = iu.item_id
             WHERE iu.user_id = :user AND i.battleuse = 1
               AND (iu.dattimer = "not" OR (iu.dattimer REGEXP "^[0-9]+$" AND CAST(iu.dattimer AS UNSIGNED) > :time))
             ORDER BY i.id DESC'
        );
        $stmt->execute(['user' => $userId, 'time' => time()]);

        $items = $stmt->fetchAll();
        return is_array($items) ? $items : [];
    }

    public function itemIdForInventoryRow(int $userId, int $itemUserId): int
    {
        if ($userId <= 0 || $itemUserId <= 0) {
            return 0;
        }

        $stmt = $this->db->prepare(
            'SELECT item_id
               FROM items_users
              WHERE id = :id
                AND user_id = :user
                AND count > 0
                AND (dattimer = "not" OR (dattimer REGEXP "^[0-9]+$" AND CAST(dattimer AS UNSIGNED) > :time))
              LIMIT 1'
        );
        $stmt->execute([
            'id' => $itemUserId,
            'user' => $userId,
            'time' => time(),
        ]);

        return (int) ($stmt->fetchColumn() ?: 0);
    }

    public function useTargetedItem(int $userId, int $itemUserId, int $pokemonId, int $count): array
    {
        if ($itemUserId <= 0 || $pokemonId <= 0) {
            return ['ok' => false, 'message' => 'Выберите предмет и покемона.'];
        }
        $count = max(1, min(999, $count));

        if ($this->userIsBusy($userId)) {
            return ['ok' => false, 'message' => 'Сначала закончите бой или обмен.'];
        }

        $item = $this->findInventoryItemForTargetUse($userId, $itemUserId);
        if ($item === null) {
            return ['ok' => false, 'message' => 'Этот предмет нельзя применить на покемона.'];
        }

        $available = (int) ($item['count'] ?? 0);
        $min = max(1, (int) ($item['target_min_count'] ?? 1));
        $max = max($min, (int) ($item['target_max_count'] ?? $available));
        $max = min($max, $available);
        if ($count < $min || $count > $max) {
            return ['ok' => false, 'message' => sprintf('Можно применить от %d до %d шт.', $min, $max)];
        }
        if ((int) ($item['allow_quantity'] ?? 0) !== 1 && $count !== 1) {
            return ['ok' => false, 'message' => 'Этот предмет применяется только по одной штуке.'];
        }

        $pokemon = $this->findActivePokemon($userId, $pokemonId);
        if ($pokemon === null) {
            return ['ok' => false, 'message' => 'Покемон не найден в активной команде.'];
        }

        $effectKey = (string) ($item['effect_key'] ?? 'pending');
        $resultMessage = '';
        if (!in_array($effectKey, ['consume_only', 'exp_candy', 'pp_vitamin', 'boost_exp', 'boost_drop', 'boost_money'], true)) {
            return [
                'ok' => false,
                'error' => 'effect_not_implemented',
                'message' => sprintf(
                    'Меню применения для "%s" уже включено, но эффект "%s" ещё не подключён. Предмет не списан.',
                    (string) ($item['name'] ?? 'Предмет'),
                    $effectKey
                ),
            ];
        }

        $this->db->beginTransaction();
        try {
            if ($effectKey === 'exp_candy') {
                $levels = max(1, min(10, $count));
                $this->db->prepare('UPDATE pok_user SET lvl = lvl + :levels WHERE id = :pokemon AND users = :user LIMIT 1')
                    ->execute(['levels' => $levels, 'pokemon' => $pokemonId, 'user' => $userId]);
                $resultMessage = sprintf('%s получает +%d уров.', strip_tags((string) ($pokemon['names'] ?? 'Покемон')), $levels);
            } elseif ($effectKey === 'pp_vitamin') {
                $this->db->prepare(
                    'UPDATE attac_my_poke
                        SET a_pp_min = a_pp_max,
                            b_pp_min = b_pp_max,
                            c_pp_min = c_pp_max,
                            d_pp_min = d_pp_max
                      WHERE pok_id = :pokemon
                      LIMIT 1'
                )->execute(['pokemon' => $pokemonId]);
                $resultMessage = 'PP атак восстановлены.';
            } elseif (str_starts_with($effectKey, 'boost_')) {
                $boostKey = substr($effectKey, 6);
                $this->grantPlayerBoost($userId, $boostKey, 'all', 2.0, 3600 * max(1, $count));
                $resultMessage = 'Буст активирован.';
            }
            $this->decrementInventoryRowById($userId, $itemUserId, $count);
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            return ['ok' => false, 'message' => 'Не удалось применить предмет.'];
        }

        return [
            'ok' => true,
            'message' => sprintf(
                '%s применён на %s x%d.',
                (string) ($item['name'] ?? 'Предмет'),
                strip_tags((string) ($pokemon['names'] ?? 'покемона')),
                $count
            ) . ($resultMessage !== '' ? ' ' . $resultMessage : ''),
        ];
    }

    public function listActivePokemonForUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT pu.id, pu.names, ip.id_items AS equipped_item_id, i.name AS equipped_item_name
               FROM pok_user pu
               LEFT JOIN items_poke ip ON ip.id_poke = pu.id
               LEFT JOIN items i ON i.id = ip.id_items
              WHERE pu.users = :user AND pu.active = 1
              ORDER BY pu.startepoke DESC, pu.id ASC'
        );
        $stmt->execute(['user' => $userId]);

        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function addItem(int $userId, int $itemId, int $count): void
    {
        if ($count <= 0) {
            return;
        }

        $existing = $this->findRow($userId, $itemId);
        if ($existing !== null) {
            $stmt = $this->db->prepare('UPDATE items_users SET count = count + :count WHERE id = :id LIMIT 1');
            $stmt->execute([
                'count' => $count,
                'id' => (int) $existing['id'],
            ]);
            return;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO items_users (id, item_id, user_id, count, dattimer, timers) VALUES (:id, :item, :user, :count, :dattimer, :timers)'
        );
        $stmt->execute([
            'id' => $this->nextItemsUsersId(),
            'item' => $itemId,
            'user' => $userId,
            'count' => $count,
            'dattimer' => 'not',
            'timers' => 'not',
        ]);
    }

    private function nextItemsUsersId(): int
    {
        return (int) ($this->db->query('SELECT COALESCE(MAX(id), 0) + 1 FROM items_users')->fetchColumn() ?: 1);
    }

    public function removeItem(int $userId, int $itemId, int $count): bool
    {
        if ($count <= 0 || !$this->hasItem($userId, $itemId, $count)) {
            return false;
        }

        $remaining = $count;
        $stmt = $this->db->prepare(
            'SELECT id, count
               FROM items_users
              WHERE user_id = :user
                AND item_id = :item
                AND count > 0
                AND (dattimer = "not" OR (dattimer REGEXP "^[0-9]+$" AND CAST(dattimer AS UNSIGNED) > :time))
              ORDER BY count ASC, id ASC
              FOR UPDATE'
        );
        $stmt->execute([
            'user' => $userId,
            'item' => $itemId,
            'time' => time(),
        ]);

        foreach ($stmt->fetchAll() ?: [] as $row) {
            if ($remaining <= 0) {
                break;
            }

            $rowId = (int) $row['id'];
            $rowCount = (int) $row['count'];
            $take = min($rowCount, $remaining);
            $newCount = $rowCount - $take;

            if ($newCount > 0) {
                $update = $this->db->prepare('UPDATE items_users SET count = :count WHERE id = :id LIMIT 1');
                $update->execute(['count' => $newCount, 'id' => $rowId]);
            } else {
                $delete = $this->db->prepare('DELETE FROM items_users WHERE id = :id LIMIT 1');
                $delete->execute(['id' => $rowId]);
            }

            $remaining -= $take;
        }

        return $remaining <= 0;
    }

    public function equipItemToPokemon(int $userId, int $itemUserId, int $pokemonId): array
    {
        if ($itemUserId <= 0 || $pokemonId <= 0) {
            return ['ok' => false, 'message' => 'Выберите предмет и покемона.'];
        }

        if ($this->userIsBusy($userId)) {
            return ['ok' => false, 'message' => 'Сначала закончите бой или обмен.'];
        }

        $item = $this->findInventoryItemForEquip($userId, $itemUserId);
        if ($item === null) {
            return ['ok' => false, 'message' => 'Предмет не найден в инвентаре.'];
        }

        if ((int) ($item['dress'] ?? 0) <= 0 || (int) ($item['count'] ?? 0) <= 0) {
            return ['ok' => false, 'message' => 'Этот предмет нельзя надеть на покемона.'];
        }

        $pokemon = $this->findActivePokemon($userId, $pokemonId);
        if ($pokemon === null) {
            return ['ok' => false, 'message' => 'Покемон не найден в активной команде.'];
        }

        $itemId = (int) $item['item_id'];
        $expiresAt = 'not';
        $days = (int) ($item['timesnapoke'] ?? 0);
        if ($days > 0) {
            $expiresAt = (string) (time() + (60 * 60 * 24 * $days));
        }

        $this->db->beginTransaction();
        try {
            $current = $this->findEquippedPokemonItem($pokemonId);
            if ($current !== null) {
                $currentExpiresAt = (string) ($current['datetime'] ?? 'not');
                if ($currentExpiresAt === 'not' || (ctype_digit($currentExpiresAt) && (int) $currentExpiresAt > time())) {
                    $this->addItem($userId, (int) $current['id_items'], 1);
                }

                $stmt = $this->db->prepare(
                    'UPDATE items_poke SET id_items = :item, datetime = :datetime WHERE id_poke = :pokemon LIMIT 1'
                );
                $stmt->execute([
                    'item' => $itemId,
                    'datetime' => $expiresAt,
                    'pokemon' => $pokemonId,
                ]);
            } else {
                $stmt = $this->db->prepare(
                    'INSERT INTO items_poke (id_poke, id_items, datetime) VALUES (:pokemon, :item, :datetime)'
                );
                $stmt->execute([
                    'pokemon' => $pokemonId,
                    'item' => $itemId,
                    'datetime' => $expiresAt,
                ]);
            }

            $this->decrementInventoryRowById($userId, $itemUserId, 1);
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            return ['ok' => false, 'message' => 'Не удалось надеть предмет.'];
        }

        return [
            'ok' => true,
            'message' => sprintf('Предмет "%s" надет на %s.', (string) ($item['name'] ?? 'Предмет'), strip_tags((string) ($pokemon['names'] ?? 'покемона'))),
            'pokemon' => $this->listActivePokemonForUser($userId),
        ];
    }

    public function unequipPokemonItem(int $userId, int $pokemonId): array
    {
        if ($pokemonId <= 0) {
            return ['ok' => false, 'message' => 'Выберите покемона.'];
        }

        if ($this->userIsBusy($userId)) {
            return ['ok' => false, 'message' => 'Сначала закончите бой или обмен.'];
        }

        $pokemon = $this->findActivePokemon($userId, $pokemonId);
        if ($pokemon === null) {
            return ['ok' => false, 'message' => 'Покемон не найден в активной команде.'];
        }

        $current = $this->findEquippedPokemonItem($pokemonId);
        if ($current === null) {
            return ['ok' => false, 'message' => 'На этом покемоне нет предмета.'];
        }

        $this->db->beginTransaction();
        try {
            $currentExpiresAt = (string) ($current['datetime'] ?? 'not');
            if ($currentExpiresAt === 'not' || (ctype_digit($currentExpiresAt) && (int) $currentExpiresAt > time())) {
                $this->addItem($userId, (int) $current['id_items'], 1);
            }
            $stmt = $this->db->prepare('DELETE FROM items_poke WHERE id_poke = :pokemon LIMIT 1');
            $stmt->execute(['pokemon' => $pokemonId]);
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            return ['ok' => false, 'message' => 'Не удалось снять предмет.'];
        }

        return [
            'ok' => true,
            'message' => 'Предмет снят и возвращён в инвентарь.',
            'pokemon' => $this->listActivePokemonForUser($userId),
        ];
    }

    private function findRow(int $userId, int $itemId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, count
               FROM items_users
              WHERE user_id = :user AND item_id = :item
              ORDER BY count DESC, id ASC
              LIMIT 1'
        );
        $stmt->execute([
            'user' => $userId,
            'item' => $itemId,
        ]);

        return $stmt->fetch() ?: null;
    }

    private function findInventoryItemForEquip(int $userId, int $itemUserId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT iu.id, iu.item_id, iu.count, i.name, i.dress, i.timesnapoke
              FROM items_users iu
               INNER JOIN items i ON i.id = iu.item_id
              WHERE iu.id = :id AND iu.user_id = :user AND iu.count > 0
                AND (iu.dattimer = "not" OR (iu.dattimer REGEXP "^[0-9]+$" AND CAST(iu.dattimer AS UNSIGNED) > :time))
              LIMIT 1'
        );
        $stmt->execute(['id' => $itemUserId, 'user' => $userId, 'time' => time()]);

        return $stmt->fetch() ?: null;
    }

    private function findInventoryItemForTargetUse(int $userId, int $itemUserId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT iu.id, iu.item_id, iu.count, i.name,
                    itr.target_type, itr.allow_quantity, itr.min_count AS target_min_count,
                    itr.max_count AS target_max_count, itr.effect_key, itr.ui_title, itr.ui_hint
               FROM items_users iu
               INNER JOIN items i ON i.id = iu.item_id
               INNER JOIN item_target_rules itr ON itr.item_id = iu.item_id AND itr.enabled = 1
              WHERE iu.id = :id
                AND iu.user_id = :user
                AND iu.count > 0
                AND (iu.dattimer = "not" OR (iu.dattimer REGEXP "^[0-9]+$" AND CAST(iu.dattimer AS UNSIGNED) > :time))
                AND itr.target_type = "pokemon"
              LIMIT 1'
        );
        $stmt->execute(['id' => $itemUserId, 'user' => $userId, 'time' => time()]);

        return $stmt->fetch() ?: null;
    }

    private function formatInventoryRow(array $row): array
    {
        $row['category_key'] = $this->itemCategoryKey($row);
        $row['category_label'] = $this->itemCategoryLabel((string) $row['category_key']);
        $row['expires_at'] = ctype_digit((string) ($row['dattimer'] ?? '')) ? (int) $row['dattimer'] : 0;

        if ((int) ($row['target_enabled'] ?? 0) === 1) {
            $row['target_use'] = [
                'enabled' => true,
                'target_type' => (string) ($row['target_type'] ?? 'pokemon'),
                'allow_quantity' => (int) ($row['allow_quantity'] ?? 0) === 1,
                'min_count' => max(1, (int) ($row['target_min_count'] ?? 1)),
                'max_count' => max(1, (int) ($row['target_max_count'] ?? 1)),
                'effect_key' => (string) ($row['effect_key'] ?? 'pending'),
                'title' => (string) ($row['ui_title'] ?? 'Применить предмет'),
                'hint' => (string) ($row['ui_hint'] ?? ''),
            ];
        } else {
            $row['target_use'] = ['enabled' => false];
        }

        unset(
            $row['target_enabled'],
            $row['target_type'],
            $row['allow_quantity'],
            $row['target_min_count'],
            $row['target_max_count'],
            $row['effect_key'],
            $row['ui_title'],
            $row['ui_hint']
        );

        return $row;
    }

    /** @return array{0:string,1:array<string,int|string>} */
    private function inventoryFilterSql(int $userId, string $category, string $query): array
    {
        $where = '';
        $params = [];

        $category = $this->normalizeInventoryCategory($category);
        if ($category !== '') {
            $where .= ' AND ' . $this->categorySqlCondition($category);
        }

        $query = trim($query);
        if ($query !== '') {
            if (ctype_digit($query)) {
                $where .= ' AND (iu.item_id = :query_id_item OR iu.id = :query_id_row)';
                $params['query_id_item'] = (int) $query;
                $params['query_id_row'] = (int) $query;
            } else {
                $where .= ' AND (i.name LIKE :query_text_name OR i.tittle LIKE :query_text_title)';
                $params['query_text_name'] = '%' . $query . '%';
                $params['query_text_title'] = '%' . $query . '%';
            }
        }

        return [$where, $params];
    }

    private function normalizeInventoryCategory(string $category): string
    {
        $category = strtolower(trim($category));
        return in_array($category, ['balls', 'tm', 'eggs', 'evolution', 'consumables', 'quest', 'drop', 'other'], true) ? $category : '';
    }

    private function categorySqlCondition(string $category): string
    {
        return match ($category) {
            'balls' => '(iu.item_id IN (3, 25, 90004) OR i.name LIKE "%бол%" OR i.name LIKE "%ball%")',
            'tm' => '(iu.item_id = 78 OR i.name LIKE "%TM%" OR i.name LIKE "%ТМ%" OR i.name LIKE "%атака%")',
            'eggs' => '(i.name LIKE "%яйц%" OR i.category = 7)',
            'evolution' => '(iu.item_id IN (64,66,67,68,74,75,76,77) OR i.name LIKE "%камень%" OR i.name LIKE "%эвол%")',
            'consumables' => '(i.uses > 0 OR i.battleuse > 0 OR iu.item_id IN (15,217,330,678,651,652,653,654,655,861,1401))',
            'quest' => '(i.category IN (50, 99) OR i.dopolnen LIKE "%quest%" OR i.name LIKE "%квест%")',
            'drop' => '(i.dopolnen LIKE "%drop%" OR iu.item_id IN (13,14,20,21,22,23))',
            default => 'NOT (
                iu.item_id IN (3,25,64,66,67,68,74,75,76,77,78,217,330,678,651,652,653,654,655,861,90004,1401)
                OR i.name LIKE "%бол%" OR i.name LIKE "%камень%" OR i.name LIKE "%яйц%" OR i.name LIKE "%TM%" OR i.name LIKE "%ТМ%"
            )',
        };
    }

    private function itemCategoryKey(array $row): string
    {
        $id = (int) ($row['item_id'] ?? 0);
        $name = mb_strtolower((string) (($row['name'] ?? '') . ' ' . ($row['tittle'] ?? '')), 'UTF-8');
        if (in_array($id, [3, 25, 90004], true) || str_contains($name, 'бол') || str_contains($name, 'ball')) {
            return 'balls';
        }
        if ($id === 78 || str_contains($name, 'tm') || str_contains($name, 'тм')) {
            return 'tm';
        }
        if (str_contains($name, 'яйц')) {
            return 'eggs';
        }
        if (in_array($id, [64, 66, 67, 68, 74, 75, 76, 77], true) || str_contains($name, 'камень') || str_contains($name, 'эвол')) {
            return 'evolution';
        }
        if ((int) ($row['uses'] ?? 0) > 0 || (int) ($row['battleuse'] ?? 0) > 0 || in_array($id, [15, 217, 330, 678, 651, 652, 653, 654, 655, 861, 1401], true)) {
            return 'consumables';
        }
        if ((int) ($row['category'] ?? 0) === 99 || str_contains((string) ($row['dopolnen'] ?? ''), 'quest') || str_contains($name, 'квест')) {
            return 'quest';
        }
        if (str_contains((string) ($row['dopolnen'] ?? ''), 'drop') || in_array($id, [13, 14, 20, 21, 22, 23], true)) {
            return 'drop';
        }
        return 'other';
    }

    private function itemCategoryLabel(string $key): string
    {
        return match ($key) {
            'balls' => 'Покеболы',
            'tm' => 'ТМ',
            'eggs' => 'Яйца',
            'evolution' => 'Эволюция',
            'consumables' => 'Расходники',
            'quest' => 'Квестовые',
            'drop' => 'Дроп',
            default => 'Прочее',
        };
    }

    private function grantPlayerBoost(int $userId, string $boostKey, string $scope, float $multiplier, int $durationSeconds): void
    {
        $allowed = ['exp', 'drop', 'money'];
        if (!in_array($boostKey, $allowed, true)) {
            return;
        }
        $now = time();
        $stmt = $this->db->prepare(
            'INSERT INTO player_boosts (user_id, item_id, boost_key, multiplier, starts_at, expires_at, active, source, created_at)
             VALUES (:user, 0, :boost_key, :multiplier, :starts_at, :expires_at, 1, "inventory", :created_at)'
        );
        $stmt->execute([
            'user' => $userId,
            'boost_key' => $boostKey,
            'multiplier' => $multiplier,
            'starts_at' => $now,
            'expires_at' => $now + max(60, $durationSeconds),
            'created_at' => $now,
        ]);
    }

    private function findActivePokemon(int $userId, int $pokemonId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, names FROM pok_user WHERE id = :pokemon AND users = :user AND active = 1 LIMIT 1'
        );
        $stmt->execute(['pokemon' => $pokemonId, 'user' => $userId]);

        return $stmt->fetch() ?: null;
    }

    private function findEquippedPokemonItem(int $pokemonId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id_poke, id_items, datetime FROM items_poke WHERE id_poke = :pokemon LIMIT 1'
        );
        $stmt->execute(['pokemon' => $pokemonId]);

        return $stmt->fetch() ?: null;
    }

    private function decrementInventoryRowById(int $userId, int $itemUserId, int $count): void
    {
        $stmt = $this->db->prepare(
            'UPDATE items_users SET count = GREATEST(count - :count, 0) WHERE id = :id AND user_id = :user LIMIT 1'
        );
        $stmt->execute([
            'count' => max(1, $count),
            'id' => $itemUserId,
            'user' => $userId,
        ]);

        $stmt = $this->db->prepare('DELETE FROM items_users WHERE id = :id AND user_id = :user AND count <= 0 LIMIT 1');
        $stmt->execute(['id' => $itemUserId, 'user' => $userId]);
    }

    private function userIsBusy(int $userId): bool
    {
        $stmt = $this->db->prepare('SELECT pve, pvp, trade FROM users WHERE id = :user LIMIT 1');
        $stmt->execute(['user' => $userId]);
        $user = $stmt->fetch();
        if (!$user) {
            return true;
        }

        return (int) ($user['pve'] ?? 0) > 0 || (int) ($user['pvp'] ?? 0) > 0 || (int) ($user['trade'] ?? 0) > 0;
    }
}
