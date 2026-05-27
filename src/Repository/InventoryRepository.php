<?php
declare(strict_types=1);

namespace Pokemon8\Repository;

use PDO;
use Pokemon8\Game\BattleTransformationCatalog;
use Pokemon8\Game\PokemonFormCatalog;

final class InventoryRepository
{
    private PokemonEvolutionRepository $evolutions;
    private ?RewardRepository $rewards = null;
    private ?SafeStorageRepository $safeStorage = null;
    private ?bool $itemGameplayMetadataAvailable = null;

    public function __construct(private PDO $db, ?PokemonEvolutionRepository $evolutions = null)
    {
        $this->evolutions = $evolutions ?? new PokemonEvolutionRepository($db);
    }

    public function setRewardRepository(RewardRepository $rewards): void
    {
        $this->rewards = $rewards;
    }

    public function setSafeStorageRepository(SafeStorageRepository $safeStorage): void
    {
        $this->safeStorage = $safeStorage;
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
        $ballCondition = $this->battleBallSqlCondition();
        $stmt = $this->db->prepare(
            'SELECT iu.id, iu.item_id, iu.count, iu.dattimer, iu.timers, i.name, i.tittle, i.category, i.delet, i.dress, i.uses, i.elementary, i.battleuse, i.dopolnen
             FROM items_users iu
             INNER JOIN items i ON i.id = iu.item_id
             WHERE iu.user_id = :user
               AND iu.count > 0
               AND (i.battleuse = 1 OR ' . $ballCondition . ')
               AND (iu.dattimer = "not" OR (iu.dattimer REGEXP "^[0-9]+$" AND CAST(iu.dattimer AS UNSIGNED) > :time))
             ORDER BY
               CASE WHEN ' . $ballCondition . ' THEN 0 ELSE 1 END,
               i.id ASC'
        );
        $stmt->execute(['user' => $userId, 'time' => time()]);

        $items = $stmt->fetchAll();
        if (!is_array($items)) {
            return [];
        }

        return array_map(fn (array $row): array => $this->formatBattleInventoryRow($row), $items);
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
        $supportedEffects = [
            'consume_only',
            'exp_candy',
            'pp_vitamin',
            'boost_exp',
            'boost_drop',
            'boost_money',
            'boost_coins',
            'boost_happiness',
            'boost_catch',
            'evolution_item',
            'tm_learn',
            'equip_held',
            'nature_neutral',
        ];
        if (!in_array($effectKey, $supportedEffects, true)) {
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
            $happinessDelta = 0;
            if ($effectKey === 'consume_only') {
                $resultMessage = 'Предмет использован.';
            } elseif ($effectKey === 'exp_candy') {
                $levels = $this->candyLevelGain((int) ($item['item_id'] ?? 0), $pokemon, $count);
                if ($levels <= 0) {
                    $this->db->rollBack();
                    return ['ok' => false, 'message' => 'Покемон уже достиг максимального уровня. Предмет не списан.'];
                }
                $evolution = $this->levelPokemon($pokemonId, $userId, $levels);
                $resultMessage = sprintf('%s получает +%d уров.', strip_tags((string) ($pokemon['names'] ?? 'Покемон')), $levels);
                $happinessDelta += $levels * 2;
                if ((int) ($item['item_id'] ?? 0) === 861) {
                    $happinessDelta += 10;
                }
                if ($evolution !== null) {
                    $resultMessage .= sprintf(' %s эволюционирует в %s.', (string) $evolution['fromName'], (string) $evolution['toName']);
                }
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
                $happinessDelta += 2;
            } elseif ($effectKey === 'evolution_item') {
                $rule = $this->evolutions->itemEvolutionTarget(
                    (int) ($pokemon['basenum'] ?? 0),
                    (int) ($item['item_id'] ?? 0),
                    (int) ($pokemon['lvl'] ?? 0)
                );
                if ($rule === null) {
                    $this->db->rollBack();
                    return ['ok' => false, 'message' => 'Этот камень не подходит выбранному покемону. Предмет не списан.'];
                }
                $evolution = $this->evolutions->evolveOwnedPokemon($userId, $pokemonId, (int) $rule['toBaseId'], 'item', $rule);
                if ($evolution === null) {
                    $this->db->rollBack();
                    return ['ok' => false, 'message' => 'Не удалось выполнить эволюцию. Предмет не списан.'];
                }
                $resultMessage = sprintf('%s эволюционирует в %s.', (string) $evolution['fromName'], (string) $evolution['toName']);
            } elseif ($effectKey === 'tm_learn') {
                $learned = $this->learnRandomTmMove($pokemonId, $userId);
                if ($learned === null) {
                    $this->db->rollBack();
                    return ['ok' => false, 'message' => 'Для этого покемона не найдена доступная новая атака. Предмет не списан.'];
                }
                $resultMessage = sprintf('%s изучает %s.', strip_tags((string) ($pokemon['names'] ?? 'Покемон')), (string) ($learned['name'] ?? 'атаку'));
                $happinessDelta += 15;
            } elseif ($effectKey === 'equip_held') {
                if (!$this->canEquipHeldItem((int) ($pokemon['basenum'] ?? 0), (int) ($item['item_id'] ?? 0), (string) ($item['name'] ?? ''))) {
                    $this->db->rollBack();
                    return ['ok' => false, 'message' => 'Этот предмет не подходит выбранному покемону. Предмет не списан.'];
                }
                $this->equipHeldItemFromTargetUse($userId, $pokemonId, (int) ($item['item_id'] ?? 0));
                $resultMessage = 'Предмет закреплен за покемоном.';
            } elseif ($effectKey === 'nature_neutral') {
                $this->db->prepare('UPDATE pok_user SET har = 1 WHERE id = :pokemon AND users = :user LIMIT 1')
                    ->execute(['pokemon' => $pokemonId, 'user' => $userId]);
                $this->recalculatePokemonStats($pokemonId, $userId);
                $resultMessage = 'Характер приведен к обычному.';
            } elseif (str_starts_with($effectKey, 'boost_')) {
                $boostKey = substr($effectKey, 6);
                $this->grantPlayerBoost($userId, $boostKey, 'all', 2.0, 3600 * max(1, $count));
                $resultMessage = 'Буст активирован.';
            }
            if ($happinessDelta > 0) {
                $appliedHappiness = $this->adjustPokemonHappiness($userId, $pokemonId, $happinessDelta);
                if ($appliedHappiness > 0) {
                    $resultMessage .= ' Счастье +' . $appliedHappiness . '.';
                }
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

    public function openGiftBox(int $userId, int $itemUserId): array
    {
        if ($itemUserId <= 0) {
            return ['ok' => false, 'message' => 'Выберите подарок.'];
        }

        $stmt = $this->db->prepare(
            'SELECT iu.id, iu.item_id, iu.count, i.name
               FROM items_users iu
               INNER JOIN items i ON i.id = iu.item_id
               INNER JOIN item_target_rules itr ON itr.item_id = iu.item_id AND itr.enabled = 1
              WHERE iu.id = :id
                AND iu.user_id = :user
                AND iu.count > 0
                AND (iu.dattimer = "not" OR (iu.dattimer REGEXP "^[0-9]+$" AND CAST(iu.dattimer AS UNSIGNED) > :time))
                AND itr.target_type = "gift"
                AND itr.effect_key = "open_gift"
              LIMIT 1'
        );
        $stmt->execute(['id' => $itemUserId, 'user' => $userId, 'time' => time()]);
        $gift = $stmt->fetch();
        if (!$gift) {
            return ['ok' => false, 'message' => 'Этот предмет нельзя открыть как подарок.'];
        }

        try {
            $lootStmt = $this->db->prepare(
                'SELECT reward_item_id, min_count, max_count, chance_bps, guaranteed
                   FROM item_gift_loot
                  WHERE gift_item_id = :gift AND enabled = 1
               ORDER BY guaranteed DESC, sort_order ASC, reward_item_id ASC'
            );
            $lootStmt->execute(['gift' => (int) $gift['item_id']]);
            $loot = $lootStmt->fetchAll();
        } catch (\Throwable) {
            return ['ok' => false, 'message' => 'Таблица подарков еще не применена в БД.'];
        }

        if (!is_array($loot) || $loot === []) {
            return ['ok' => false, 'message' => 'У подарка пока нет таблицы наград. Предмет не списан.'];
        }

        $granted = [];
        $startedTransaction = !$this->db->inTransaction();
        if ($startedTransaction) {
            $this->db->beginTransaction();
        }
        try {
            foreach ($loot as $row) {
                $guaranteed = (int) ($row['guaranteed'] ?? 0) === 1;
                $chance = max(0, min(10000, (int) ($row['chance_bps'] ?? 0)));
                if (!$guaranteed && random_int(1, 10000) > $chance) {
                    continue;
                }

                $min = max(1, (int) ($row['min_count'] ?? 1));
                $max = max($min, (int) ($row['max_count'] ?? $min));
                $count = random_int($min, $max);
                $rewardItemId = (int) ($row['reward_item_id'] ?? 0);
                if ($rewardItemId <= 0 || $count <= 0) {
                    continue;
                }

                $this->addItem($userId, $rewardItemId, $count);
                $granted[] = ['item_id' => $rewardItemId, 'count' => $count, 'name' => $this->itemName($rewardItemId)];
            }

            if ($granted === []) {
                if ($startedTransaction && $this->db->inTransaction()) {
                    $this->db->rollBack();
                }
                return ['ok' => false, 'message' => 'Подарок ничего не выдал. Предмет не списан, проверь шансы loot table.'];
            }

            $parts = array_map(
                static fn (array $row): string => sprintf('%s x%d', (string) ($row['name'] ?? ('#' . $row['item_id'])), (int) $row['count']),
                $granted
            );

            $this->decrementInventoryRowById($userId, $itemUserId, 1);
            $this->rewards?->notify($userId, 'Подарок открыт', 'Получено: ' . implode(', ', $parts) . '.', 'reward', [
                'gift_item_id' => (int) $gift['item_id'],
                'inventory_row_id' => (int) $gift['id'],
                'rewards' => $granted,
            ]);
            if ($startedTransaction) {
                $this->db->commit();
            }
        } catch (\Throwable $e) {
            if ($startedTransaction && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $this->safeStorage?->recordRollback(
                'gift_open_failed:' . $userId . ':' . $itemUserId . ':' . time(),
                'inventory_gift_open',
                $userId,
                'inventory',
                $itemUserId,
                ['gift_item_user_id' => $itemUserId, 'gift_item_id' => (int) ($gift['item_id'] ?? 0)],
                ['granted' => $granted],
                ['gift_item_user_id' => $itemUserId, 'gift_spent' => false, 'granted_rolled_back' => true],
                'failed',
                $e->getMessage()
            );
            return ['ok' => false, 'message' => 'Не удалось открыть подарок. Предмет не списан.'];
        }

        $parts = array_map(
            static fn (array $row): string => sprintf('%s x%d', (string) ($row['name'] ?? ('#' . $row['item_id'])), (int) $row['count']),
            $granted
        );
        $message = sprintf('Подарок "%s" открыт: %s.', (string) ($gift['name'] ?? 'Подарок'), implode(', ', $parts));

        return [
            'ok' => true,
            'message' => $message,
            'rewards' => $granted,
        ];
    }

    private function candyLevelGain(int $itemId, array $pokemon, int $count): int
    {
        $level = (int) ($pokemon['lvl'] ?? 1);
        $maxGain = max(0, 100 - $level);
        if ($maxGain <= 0) {
            return 0;
        }

        $perItem = match ($itemId) {
            1401 => 5,
            655 => 3,
            653, 654, 861 => 2,
            default => 1,
        };

        return min($maxGain, max(1, $count) * $perItem);
    }

    private function levelPokemon(int $pokemonId, int $userId, int $levels): ?array
    {
        $this->db->prepare(
            'UPDATE pok_user
                SET lvl = LEAST(100, lvl + :levels)
              WHERE id = :pokemon AND users = :user
              LIMIT 1'
        )->execute(['levels' => max(1, $levels), 'pokemon' => $pokemonId, 'user' => $userId]);

        $this->recalculatePokemonStats($pokemonId, $userId);

        return $this->evolutions->applyLevelEvolution($userId, $pokemonId);
    }

    private function adjustPokemonHappiness(int $userId, int $pokemonId, int $delta): int
    {
        if ($userId <= 0 || $pokemonId <= 0 || $delta === 0) {
            return 0;
        }

        $stmt = $this->db->prepare('SELECT happy FROM pok_user WHERE id = :pokemon AND users = :user LIMIT 1 FOR UPDATE');
        $stmt->execute(['pokemon' => $pokemonId, 'user' => $userId]);
        $before = (float) ($stmt->fetchColumn() ?: 0);
        $after = max(0, min(100, $before + $delta));

        $this->db->prepare('UPDATE pok_user SET happy = :happy WHERE id = :pokemon AND users = :user LIMIT 1')
            ->execute(['happy' => $after, 'pokemon' => $pokemonId, 'user' => $userId]);

        return (int) max(0, round($after - $before));
    }

    private function evolvePokemon(int $pokemonId, int $userId, int $targetBase): void
    {
        $name = $this->basePokemonName($targetBase);
        $this->db->prepare(
            'UPDATE pok_user
                SET basenum = :base, names = :name
              WHERE id = :pokemon AND users = :user
              LIMIT 1'
        )->execute([
            'base' => $targetBase,
            'name' => $name,
            'pokemon' => $pokemonId,
            'user' => $userId,
        ]);

        $this->recalculatePokemonStats($pokemonId, $userId);
    }

    private function recalculatePokemonStats(int $pokemonId, int $userId): void
    {
        $stmt = $this->db->prepare(
            'SELECT id, basenum, lvl, har, hp_my, hp_max,
                    hp_ev, atk_ev, def_ev, satk_ev, sdef_ev, speed_ev,
                    hp_iv, atk_iv, def_iv, satk_iv, sdef_iv, speed_iv
               FROM pok_user
              WHERE id = :pokemon AND users = :user
              LIMIT 1'
        );
        $stmt->execute(['pokemon' => $pokemonId, 'user' => $userId]);
        $pokemon = $stmt->fetch();
        if (!$pokemon) {
            return;
        }

        $baseStmt = $this->db->prepare('SELECT hp, atk, def, satk, sdef, speed FROM poke_base WHERE id = :base LIMIT 1');
        $baseStmt->execute(['base' => (int) ($pokemon['basenum'] ?? 0)]);
        $base = $baseStmt->fetch();
        if (!$base) {
            return;
        }

        $harStmt = $this->db->prepare('SELECT atk, def, satk, sdef, speed FROM har WHERE id_har = :har LIMIT 1');
        $harStmt->execute(['har' => (int) ($pokemon['har'] ?? 1)]);
        $har = $harStmt->fetch() ?: ['atk' => 1, 'def' => 1, 'satk' => 1, 'sdef' => 1, 'speed' => 1];

        $level = max(1, min(100, (int) ($pokemon['lvl'] ?? 1)));
        $hpMaxBefore = max(1, (int) ($pokemon['hp_max'] ?? 1));
        $hpRatio = max(0.0, min(1.0, (int) ($pokemon['hp_my'] ?? $hpMaxBefore) / $hpMaxBefore));
        $calcStat = static function (int $baseValue, int $iv, int $ev, float $nature, int $level): int {
            return max(1, (int) round((((($iv + $baseValue * 2 + (int) floor($ev / 4)) * $level) / 100) + 5) * max(0.1, $nature)));
        };
        $hpMax = max(1, (int) round(((int) ($pokemon['hp_iv'] ?? 0) + (int) ($base['hp'] ?? 1) * 2 + (int) floor((int) ($pokemon['hp_ev'] ?? 0) / 4) + 100) * $level / 100 + 10));
        $hpMy = max(1, min($hpMax, (int) round($hpMax * $hpRatio)));

        $this->db->prepare(
            'UPDATE pok_user
                SET hp_my = :hp_my, hp_max = :hp_max,
                    atk = :atk, def = :def, satk = :satk, sdef = :sdef, speed = :speed
              WHERE id = :pokemon AND users = :user
              LIMIT 1'
        )->execute([
            'hp_my' => $hpMy,
            'hp_max' => $hpMax,
            'atk' => $calcStat((int) ($base['atk'] ?? 1), (int) ($pokemon['atk_iv'] ?? 0), (int) ($pokemon['atk_ev'] ?? 0), (float) ($har['atk'] ?? 1), $level),
            'def' => $calcStat((int) ($base['def'] ?? 1), (int) ($pokemon['def_iv'] ?? 0), (int) ($pokemon['def_ev'] ?? 0), (float) ($har['def'] ?? 1), $level),
            'satk' => $calcStat((int) ($base['satk'] ?? 1), (int) ($pokemon['satk_iv'] ?? 0), (int) ($pokemon['satk_ev'] ?? 0), (float) ($har['satk'] ?? 1), $level),
            'sdef' => $calcStat((int) ($base['sdef'] ?? 1), (int) ($pokemon['sdef_iv'] ?? 0), (int) ($pokemon['sdef_ev'] ?? 0), (float) ($har['sdef'] ?? 1), $level),
            'speed' => $calcStat((int) ($base['speed'] ?? 1), (int) ($pokemon['speed_iv'] ?? 0), (int) ($pokemon['speed_ev'] ?? 0), (float) ($har['speed'] ?? 1), $level),
            'pokemon' => $pokemonId,
            'user' => $userId,
        ]);
    }

    private function learnRandomTmMove(int $pokemonId, int $userId): ?array
    {
        $pokemon = $this->findActivePokemon($userId, $pokemonId);
        if ($pokemon === null) {
            return null;
        }

        $knownStmt = $this->db->prepare('SELECT a_id, b_id, c_id, d_id FROM attac_my_poke WHERE pok_id = :pokemon LIMIT 1');
        $knownStmt->execute(['pokemon' => $pokemonId]);
        $knownRow = $knownStmt->fetch() ?: [];
        $known = array_filter(array_map('intval', [
            $knownRow['a_id'] ?? 0,
            $knownRow['b_id'] ?? 0,
            $knownRow['c_id'] ?? 0,
            $knownRow['d_id'] ?? 0,
        ]));
        $notIn = count($known) > 0 ? (' AND ap.atac_id NOT IN (' . implode(',', array_map('intval', $known)) . ')') : '';

        $stmt = $this->db->prepare(
            'SELECT ap.atac_id AS id, ap.atac_name AS name, ap.atac_pp AS pp, ap.atac_tip AS tip
               FROM attac_poke pk
               INNER JOIN attac_power ap ON ap.atac_id = pk.atac_id
              WHERE pk.poke_base_id = :base
                AND (pk.atc_lvl IS NULL OR pk.atc_lvl <= :level)' . $notIn . '
              ORDER BY RAND()
              LIMIT 1'
        );
        $stmt->execute([
            'base' => (int) ($pokemon['basenum'] ?? 0),
            'level' => max(1, (int) ($pokemon['lvl'] ?? 1)),
        ]);
        $move = $stmt->fetch();
        if (!$move) {
            return null;
        }

        $slot = 'd';
        foreach (['a', 'b', 'c', 'd'] as $candidate) {
            if ((int) ($knownRow[$candidate . '_id'] ?? 0) <= 0) {
                $slot = $candidate;
                break;
            }
        }

        if ($knownRow) {
            $this->db->prepare(
                'UPDATE attac_my_poke
                    SET ' . $slot . '_id = :move,
                        ' . $slot . '_pp_min = :pp,
                        ' . $slot . '_pp_max = :pp
                  WHERE pok_id = :pokemon
                  LIMIT 1'
            )->execute([
                'move' => (int) ($move['id'] ?? 0),
                'pp' => (int) ($move['pp'] ?? 0),
                'pokemon' => $pokemonId,
            ]);
        } else {
            $this->db->prepare(
                'INSERT INTO attac_my_poke (
                    id, pok_id,
                    a_id, a_pp_min, a_pp_max,
                    b_id, b_pp_min, b_pp_max,
                    c_id, c_pp_min, c_pp_max,
                    d_id, d_pp_min, d_pp_max
                 )
                 VALUES (
                    :id, :pokemon,
                    :move, :pp_min, :pp_max,
                    0, 0, 0,
                    0, 0, 0,
                    0, 0, 0
                 )'
            )->execute([
                'id' => $this->nextAttacMyPokeId(),
                'pokemon' => $pokemonId,
                'move' => (int) ($move['id'] ?? 0),
                'pp_min' => (int) ($move['pp'] ?? 0),
                'pp_max' => (int) ($move['pp'] ?? 0),
            ]);
        }

        return $move;
    }

    private function nextAttacMyPokeId(): int
    {
        return (int) ($this->db->query('SELECT COALESCE(MAX(id), 0) + 1 FROM attac_my_poke')->fetchColumn() ?: 1);
    }

    private function equipHeldItemFromTargetUse(int $userId, int $pokemonId, int $itemId): void
    {
        $existing = $this->findEquippedPokemonItem($pokemonId);
        if ($existing !== null) {
            $currentExpiresAt = (string) ($existing['datetime'] ?? 'not');
            if ($currentExpiresAt === 'not' || (ctype_digit($currentExpiresAt) && (int) $currentExpiresAt > time())) {
                $this->addItem($userId, (int) $existing['id_items'], 1);
            }
            $this->db->prepare('UPDATE items_poke SET id_items = :item, datetime = :time WHERE id_poke = :pokemon LIMIT 1')
                ->execute(['item' => $itemId, 'time' => 'not', 'pokemon' => $pokemonId]);
            return;
        }

        $this->db->prepare('INSERT INTO items_poke (id_poke, id_items, datetime) VALUES (:pokemon, :item, :time)')
            ->execute(['pokemon' => $pokemonId, 'item' => $itemId, 'time' => 'not']);
    }

    private function basePokemonName(int $baseId): string
    {
        $stmt = $this->db->prepare('SELECT name_new FROM pokemon WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $baseId]);
        $name = trim((string) ($stmt->fetchColumn() ?: ''));
        return $name !== '' ? $name : ('Pokemon #' . $baseId);
    }

    private function itemName(int $itemId): string
    {
        $stmt = $this->db->prepare('SELECT name FROM items WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $itemId]);
        $name = trim(strip_tags((string) ($stmt->fetchColumn() ?: '')));
        return $name !== '' ? $name : ('Предмет #' . $itemId);
    }

    private function evolutionTargetFor(int $baseId, int $itemId): int
    {
        $canonicalItem = match ($itemId) {
            64 => 71,
            66 => 44,
            67 => 41,
            68 => 43,
            74 => 6,
            75 => 40,
            76, 77 => 42,
            default => $itemId,
        };
        $map = [
            25 => [40 => 26],
            30 => [44 => 31],
            33 => [44 => 34],
            35 => [44 => 36],
            37 => [41 => 38],
            39 => [44 => 40],
            44 => [43 => 45, 6 => 182],
            58 => [41 => 59],
            61 => [42 => 62],
            70 => [43 => 71],
            90 => [42 => 91],
            102 => [43 => 103],
            120 => [42 => 121],
            133 => [42 => 134, 40 => 135, 41 => 136, 6 => 196, 44 => 197],
            191 => [6 => 192],
            198 => [44 => 430],
            200 => [44 => 429],
            271 => [42 => 272],
            274 => [43 => 275],
            281 => [71 => 475],
            300 => [44 => 301],
            315 => [6 => 407],
            361 => [71 => 478],
            406 => [6 => 315],
            511 => [43 => 512],
            513 => [41 => 514],
            515 => [42 => 516],
            517 => [44 => 518],
            548 => [6 => 549],
            603 => [40 => 604],
            608 => [44 => 609],
        ];

        return (int) ($map[$baseId][$canonicalItem] ?? 0);
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

        try {
            $this->withItemsUsersLock(function () use ($userId, $itemId, $count): void {
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
            });
        } catch (\Throwable $e) {
            $this->safeStorage?->storeItem($userId, $itemId, $count, 'inventory.addItem', '', [
                'method' => 'InventoryRepository::addItem',
            ], 'inventory_add_failed', $e->getMessage());
            throw $e;
        }
    }

    private function nextItemsUsersId(): int
    {
        return (int) ($this->db->query('SELECT COALESCE(MAX(id), 0) + 1 FROM items_users')->fetchColumn() ?: 1);
    }

    private function withItemsUsersLock(callable $callback): void
    {
        $lock = $this->db->prepare('SELECT GET_LOCK(:name, 15)');
        $lock->execute(['name' => 'pokemon8_seq_items_users_id']);
        if ((int) ($lock->fetchColumn() ?: 0) !== 1) {
            throw new \RuntimeException('Unable to acquire items_users lock.');
        }

        try {
            $callback();
        } finally {
            $release = $this->db->prepare('SELECT RELEASE_LOCK(:name)');
            $release->execute(['name' => 'pokemon8_seq_items_users_id']);
        }
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
        if (!$this->canEquipHeldItem((int) ($pokemon['basenum'] ?? 0), $itemId, (string) ($item['name'] ?? ''))) {
            return ['ok' => false, 'message' => 'Этот предмет не подходит выбранному покемону.'];
        }
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

    private function canEquipHeldItem(int $baseId, int $itemId, string $itemName): bool
    {
        if ($itemId <= 0) {
            return false;
        }

        $displayBaseId = PokemonFormCatalog::displayBaseId($baseId);

        if (isset(BattleTransformationCatalog::items()[$itemId])) {
            return BattleTransformationCatalog::matchRule($displayBaseId, $itemId, $itemName, false) !== null;
        }

        $compatibility = $this->heldItemCompatibility($itemId);
        if ($compatibility !== null) {
            return $this->heldItemCompatibilityAllows($compatibility, $displayBaseId);
        }

        return match ($itemId) {
            344 => in_array($displayBaseId, [380, 381], true), // Soul Dew: Latias / Latios.
            358 => in_array($displayBaseId, [104, 105], true), // Thick Club: Cubone / Marowak line.
            default => true,
        };
    }

    private function heldItemCompatibility(int $itemId): ?array
    {
        if (!$this->itemGameplayMetadataTableAvailable()) {
            return null;
        }

        try {
            $stmt = $this->db->prepare(
                'SELECT compatibility_rule, compatibility_json, safe_to_equip, equipuse_flag
                   FROM item_gameplay_metadata
                  WHERE item_id = :item
                  LIMIT 1'
            );
            $stmt->execute(['item' => $itemId]);
            $row = $stmt->fetch();
        } catch (\Throwable) {
            $this->itemGameplayMetadataAvailable = false;
            return null;
        }

        return $row ?: null;
    }

    private function itemGameplayMetadataTableAvailable(): bool
    {
        if ($this->itemGameplayMetadataAvailable !== null) {
            return $this->itemGameplayMetadataAvailable;
        }

        try {
            $stmt = $this->db->query("SHOW TABLES LIKE 'item_gameplay_metadata'");
            $this->itemGameplayMetadataAvailable = (bool) $stmt->fetchColumn();
        } catch (\Throwable) {
            $this->itemGameplayMetadataAvailable = false;
        }

        return $this->itemGameplayMetadataAvailable;
    }

    private function heldItemCompatibilityAllows(array $compatibility, int $displayBaseId): bool
    {
        if ((int) ($compatibility['safe_to_equip'] ?? 0) <= 0 || (int) ($compatibility['equipuse_flag'] ?? 0) <= 0) {
            return false;
        }

        $rule = strtolower(trim((string) ($compatibility['compatibility_rule'] ?? 'universal')));
        return match ($rule) {
            '', 'universal' => true,
            'none' => false,
            'kyogre' => $displayBaseId === 382,
            'groudon' => $displayBaseId === 383,
            'latios_latias' => in_array($displayBaseId, [380, 381], true),
            'cubone_marowak' => in_array($displayBaseId, [104, 105], true),
            'base_ids' => $this->heldItemCompatibilityBaseIds($compatibility, $displayBaseId),
            default => true,
        };
    }

    private function heldItemCompatibilityBaseIds(array $compatibility, int $displayBaseId): bool
    {
        $raw = trim((string) ($compatibility['compatibility_json'] ?? ''));
        if ($raw === '') {
            return false;
        }

        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            $ids = $decoded['base_ids'] ?? $decoded;
            if (is_array($ids)) {
                return in_array($displayBaseId, array_map('intval', $ids), true);
            }
        }

        $ids = array_map('intval', preg_split('/[^0-9]+/', $raw, -1, PREG_SPLIT_NO_EMPTY) ?: []);
        return in_array($displayBaseId, $ids, true);
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

    private function formatBattleInventoryRow(array $row): array
    {
        $row['category_key'] = $this->itemCategoryKey($row);
        $row['category_label'] = $this->itemCategoryLabel((string) $row['category_key']);
        $row['battle_pocket'] = $this->isBattleBallRow($row) ? 'balls' : 'items';
        $row['battle_category'] = $row['battle_pocket'] === 'balls' ? 'balls' : $this->battleItemCategory($row);
        $row['battle_category_label'] = $this->battleCategoryLabel((string) $row['battle_category']);
        $row['catch_modifier'] = $this->battleCatchModifier($row);
        $row['expires_at'] = ctype_digit((string) ($row['dattimer'] ?? '')) ? (int) $row['dattimer'] : 0;
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
            'balls' => $this->battleBallSqlCondition(),
            'tm' => '(iu.item_id = 78 OR i.name LIKE "%TM%" OR i.name LIKE "%ТМ%" OR i.name LIKE "%атака%")',
            'eggs' => '(i.name LIKE "%яйц%" OR i.category = 7)',
            'evolution' => '(iu.item_id IN (64,66,67,68,69,74,75,76,77,80,81,82,83,86,87,89,332) OR i.name LIKE "%камень%" OR i.name LIKE "%эвол%")',
            'consumables' => '(i.uses > 0 OR i.battleuse > 0 OR iu.item_id IN (15,217,330,678,651,652,653,654,655,861,90111,1401))',
            'quest' => '(iu.item_id IN (4,13,14) OR i.category IN (50, 99) OR i.dopolnen LIKE "%quest%" OR i.name LIKE "%квест%" OR i.tittle LIKE "%Квест%")',
            'drop' => '(iu.item_id NOT IN (4,13,14) AND (i.dopolnen LIKE "%drop%" OR iu.item_id IN (20,21,22,23)))',
            default => 'NOT (
                iu.item_id IN (3,64,66,67,68,69,74,75,76,77,78,80,81,82,83,86,87,89,217,330,332,678,651,652,653,654,655,861,90004,90005,90111,1401)
                OR i.name LIKE "%бол%" OR i.name LIKE "%камень%" OR i.name LIKE "%яйц%" OR i.name LIKE "%TM%" OR i.name LIKE "%ТМ%"
            )',
        };
    }

    private function battleBallSqlCondition(): string
    {
        return '(iu.item_id IN (3, 90004, 90005)
            OR i.name REGEXP "поке.?бол|мастер.?бол|ультра.?бол|премиум.?бол|грит.?бол|ball|шар"
            OR i.name LIKE "%ball%"
            OR i.tittle REGEXP "поке.?бол|мастер.?бол|ультра.?бол|премиум.?бол|грит.?бол|ball|шар"
            OR i.tittle LIKE "%ball%"
            OR i.tittle LIKE "%шар%")';
    }

    private function itemCategoryKey(array $row): string
    {
        $id = (int) ($row['item_id'] ?? 0);
        $name = mb_strtolower((string) (($row['name'] ?? '') . ' ' . ($row['tittle'] ?? '')), 'UTF-8');
        if (in_array($id, [3, 90004, 90005], true) || $this->looksLikeCaptureBall($name)) {
            return 'balls';
        }
        if ($id === 78 || str_contains($name, 'tm') || str_contains($name, 'тм')) {
            return 'tm';
        }
        if (str_contains($name, 'яйц')) {
            return 'eggs';
        }
        if (in_array($id, [64, 66, 67, 68, 69, 74, 75, 76, 77, 80, 81, 82, 83, 86, 87, 89, 332], true) || str_contains($name, 'камень') || str_contains($name, 'эвол')) {
            return 'evolution';
        }
        if ((int) ($row['uses'] ?? 0) > 0 || (int) ($row['battleuse'] ?? 0) > 0 || in_array($id, [15, 217, 330, 678, 651, 652, 653, 654, 655, 861, 90111, 1401], true)) {
            return 'consumables';
        }
        if (in_array($id, [4, 13, 14], true) || (int) ($row['category'] ?? 0) === 99 || str_contains((string) ($row['dopolnen'] ?? ''), 'quest') || str_contains($name, 'квест')) {
            return 'quest';
        }
        if (str_contains((string) ($row['dopolnen'] ?? ''), 'drop') || in_array($id, [20, 21, 22, 23], true)) {
            return 'drop';
        }
        return 'other';
    }

    private function battleItemCategory(array $row): string
    {
        $id = (int) ($row['item_id'] ?? 0);
        $text = mb_strtolower((string) (($row['name'] ?? '') . ' ' . ($row['tittle'] ?? '') . ' ' . ($row['dopolnen'] ?? '')), 'UTF-8');

        if (str_contains($text, 'hp') || str_contains($text, 'здоров') || str_contains($text, 'леч') || str_contains($text, 'восстанов')) {
            return 'healing';
        }
        if (str_contains($text, 'ожив') || str_contains($text, 'revive') || str_contains($text, 'воскреш')) {
            return 'revive';
        }
        if (str_contains($text, 'яд') || str_contains($text, 'сон') || str_contains($text, 'паралич') || str_contains($text, 'замороз') || str_contains($text, 'ожог') || in_array($id, [15], true)) {
            return 'status';
        }
        if (str_contains($text, 'точност') || str_contains($text, 'атака') || str_contains($text, 'защит') || str_contains($text, 'скорост') || str_contains($text, 'баф') || str_contains($text, 'buff')) {
            return 'buffs';
        }

        return 'utility';
    }

    private function battleCategoryLabel(string $key): string
    {
        return match ($key) {
            'balls' => 'Покеболы',
            'healing' => 'Лечение',
            'revive' => 'Revive',
            'status' => 'Статусы',
            'buffs' => 'Баффы',
            default => 'Разное',
        };
    }

    private function isBattleBallRow(array $row): bool
    {
        $id = (int) ($row['item_id'] ?? 0);
        if (in_array($id, [3, 90004, 90005], true)) {
            return true;
        }

        $text = mb_strtolower((string) (($row['name'] ?? '') . ' ' . ($row['tittle'] ?? '')), 'UTF-8');
        return $this->looksLikeCaptureBall($text);
    }

    private function looksLikeCaptureBall(string $text): bool
    {
        return (bool) preg_match('/поке.?бол|мастер.?бол|ультра.?бол|премиум.?бол|грит.?бол|great.?ball|ultra.?ball|master.?ball|ball|шар/ui', $text);
    }

    private function battleCatchModifier(array $row): int
    {
        return match ((int) ($row['item_id'] ?? 0)) {
            90004 => 255,
            90005 => 2,
            default => 1,
        };
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
        $boostKey = $boostKey === 'money' ? 'coins' : $boostKey;
        $allowed = ['exp', 'drop', 'coins', 'happiness', 'catch', 'quest_rewards'];
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
            'SELECT id, names, basenum, lvl, har, hp_my, hp_max,
                    hp_ev, atk_ev, def_ev, satk_ev, sdef_ev, speed_ev,
                    hp_iv, atk_iv, def_iv, satk_iv, sdef_iv, speed_iv
               FROM pok_user
              WHERE id = :pokemon AND users = :user AND active = 1
              LIMIT 1'
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
