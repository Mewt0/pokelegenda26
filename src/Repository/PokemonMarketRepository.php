<?php
declare(strict_types=1);

namespace Pokemon8\Repository;

use PDO;
use Throwable;

final class PokemonMarketRepository
{
    public function __construct(private PDO $db, private InventoryRepository $inventory)
    {
    }

    public function index(int $userId, string $query = '', int $page = 1, int $perPage = 30): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(60, $perPage));
        $offset = ($page - 1) * $perPage;
        $where = ['(rp.user_pok = :current_user OR rp.user_id_to = "no" OR (rp.user_id_to REGEXP "^[0-9]+$" AND CAST(rp.user_id_to AS UNSIGNED) = :user_to))'];
        $params = ['current_user' => $userId, 'user_to' => $userId];
        $query = trim($query);
        if ($query !== '') {
            if (ctype_digit($query)) {
                $where[] = '(rp.id_lot = :q_id_lot OR pu.id = :q_id_pokemon OR pu.basenum = :q_id_base)';
                $params['q_id_lot'] = (int) $query;
                $params['q_id_pokemon'] = (int) $query;
                $params['q_id_base'] = (int) $query;
            } else {
                $where[] = '(pu.names LIKE :q_names OR pb.title LIKE :q_base OR seller.login LIKE :q_seller)';
                $params['q_names'] = '%' . $query . '%';
                $params['q_base'] = '%' . $query . '%';
                $params['q_seller'] = '%' . $query . '%';
            }
        }
        $whereSql = implode(' AND ', $where);

        $totalStmt = $this->db->prepare(
            'SELECT COUNT(*)
               FROM rinok_poke rp
          LEFT JOIN pok_user pu ON pu.id = rp.id_poke
          LEFT JOIN poke_base pb ON pb.id = pu.basenum
          LEFT JOIN users seller ON seller.id = rp.user_pok
              WHERE ' . $whereSql
        );
        $totalStmt->execute($params);
        $total = (int) ($totalStmt->fetchColumn() ?: 0);

        $stmt = $this->db->prepare(
            'SELECT rp.id_lot, rp.user_pok, rp.maney, rp.id_poke, rp.user_id_to, rp.dateend, rp.regions,
                    seller.login AS seller_login,
                    buyer.login AS private_buyer_login,
                    pu.names, pu.basenum, pu.lvl, pu.sex, pu.tips,
                    pu.hp_iv, pu.atk_iv, pu.def_iv, pu.satk_iv, pu.sdef_iv, pu.speed_iv,
                    pu.hp_ev, pu.atk_ev, pu.def_ev, pu.satk_ev, pu.sdef_ev, pu.speed_ev,
                    pb.title AS base_title
               FROM rinok_poke rp
          LEFT JOIN pok_user pu ON pu.id = rp.id_poke
          LEFT JOIN poke_base pb ON pb.id = pu.basenum
          LEFT JOIN users seller ON seller.id = rp.user_pok
          LEFT JOIN users buyer ON buyer.id = CASE
                WHEN rp.user_id_to REGEXP "^[0-9]+$" THEN CAST(rp.user_id_to AS UNSIGNED)
                ELSE 0
              END
              WHERE ' . $whereSql . '
           ORDER BY rp.id_lot DESC
              LIMIT :limit OFFSET :offset'
        );
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $lots = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $lots[] = $this->formatLot($row, $userId);
        }

        return [
            'ok' => true,
            'page' => $page,
            'pages' => max(1, (int) ceil($total / $perPage)),
            'total' => $total,
            'lots' => $lots,
            'own_pokemon' => $this->sellablePokemon($userId),
        ];
    }

    public function listPokemon(int $userId, int $pokemonId, int $price, string $privateTo = ''): array
    {
        if ($this->userBusy($userId)) {
            return ['ok' => false, 'message' => 'Сначала закончите бой или обмен.'];
        }
        if ($price <= 0) {
            return ['ok' => false, 'message' => 'Укажи цену больше нуля.'];
        }
        $activeCount = $this->activePokemonCount($userId);
        if ($activeCount <= 1) {
            return ['ok' => false, 'message' => 'В команде должен остаться хотя бы один активный покемон.'];
        }
        $lotCount = $this->db->prepare('SELECT COUNT(*) FROM rinok_poke WHERE user_pok = :user');
        $lotCount->execute(['user' => $userId]);
        if ((int) ($lotCount->fetchColumn() ?: 0) >= 3) {
            return ['ok' => false, 'message' => 'Нельзя выставлять больше 3 активных лотов.'];
        }

        $stmt = $this->db->prepare('SELECT id, users, names, startone FROM pok_user WHERE id = :pokemon AND users = :user AND active = 1 LIMIT 1');
        $stmt->execute(['pokemon' => $pokemonId, 'user' => $userId]);
        $pokemon = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$pokemon) {
            return ['ok' => false, 'message' => 'Покемон не найден в активной команде.'];
        }
        if ((int) ($pokemon['startone'] ?? 0) > 0) {
            return ['ok' => false, 'message' => 'Этот покемон запрещен к продаже.'];
        }

        $privateUserId = 'no';
        if (trim($privateTo) !== '') {
            $target = $this->findUser(trim($privateTo));
            if (!$target) {
                return ['ok' => false, 'message' => 'Получатель приватного лота не найден.'];
            }
            if ((int) $target['id'] === $userId) {
                return ['ok' => false, 'message' => 'Нельзя создать приватный лот самому себе.'];
            }
            $privateUserId = (string) $target['id'];
        }

        $this->withMarketLock(function () use ($userId, $pokemonId, $price, $privateUserId): void {
            $lotId = $this->nextMarketLotId();
            $this->db->prepare(
                'INSERT INTO rinok_poke (id_lot, user_pok, maney, id_poke, user_id_to, dateend, regions)
                 VALUES (:id, :user, :price, :pokemon, :private_to, :dateend, :region)'
            )->execute([
                'id' => $lotId,
                'user' => $userId,
                'price' => $price,
                'pokemon' => $pokemonId,
                'private_to' => $privateUserId,
                'dateend' => time() + 86400,
                'region' => $this->userLocation($userId),
            ]);
            $this->db->prepare('UPDATE pok_user SET users = 3, active = 0, startepoke = 0 WHERE id = :pokemon AND users = :user LIMIT 1')
                ->execute(['pokemon' => $pokemonId, 'user' => $userId]);
        });

        return ['ok' => true, 'message' => 'Покемон выставлен на рынок.'];
    }

    public function buy(int $userId, int $lotId): array
    {
        if ($this->userBusy($userId)) {
            return ['ok' => false, 'message' => 'Сначала закончите бой или обмен.'];
        }
        if ($this->activePokemonCount($userId) >= 6) {
            return ['ok' => false, 'message' => 'В команде уже 6 покемонов. Освободи слот.'];
        }

        $startedTransaction = !$this->db->inTransaction();
        if ($startedTransaction) {
            $this->db->beginTransaction();
        }
        try {
            $stmt = $this->db->prepare('SELECT * FROM rinok_poke WHERE id_lot = :lot LIMIT 1 FOR UPDATE');
            $stmt->execute(['lot' => $lotId]);
            $lot = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$lot) {
                if ($startedTransaction) {
                    $this->db->rollBack();
                }
                return ['ok' => false, 'message' => 'Лот уже продан или снят.'];
            }
            $sellerId = (int) ($lot['user_pok'] ?? 0);
            if ($sellerId === $userId) {
                if ($startedTransaction) {
                    $this->db->rollBack();
                }
                return ['ok' => false, 'message' => 'Нельзя купить своего покемона.'];
            }
            $privateTo = (string) ($lot['user_id_to'] ?? 'no');
            if ($privateTo !== 'no' && (int) $privateTo !== $userId) {
                if ($startedTransaction) {
                    $this->db->rollBack();
                }
                return ['ok' => false, 'message' => 'Этот приватный лот предназначен другому игроку.'];
            }
            $price = max(0, (int) ($lot['maney'] ?? 0));
            if (!$this->inventory->removeItem($userId, 1, $price)) {
                if ($startedTransaction) {
                    $this->db->rollBack();
                }
                return ['ok' => false, 'message' => 'Недостаточно монет.'];
            }
            $tax = (int) round($price * 0.05);
            $sellerAmount = max(0, $price - $tax);
            $this->inventory->addItem($sellerId, 1, $sellerAmount);
            $transfer = $this->db->prepare('UPDATE pok_user SET users = :user, active = 1, startepoke = 0 WHERE id = :pokemon AND users = 3 LIMIT 1');
            $transfer->execute(['user' => $userId, 'pokemon' => (int) $lot['id_poke']]);
            if ($transfer->rowCount() !== 1) {
                if ($startedTransaction) {
                    $this->db->rollBack();
                }
                return ['ok' => false, 'message' => 'Покемон в лоте не найден. Деньги не списаны.'];
            }
            $this->db->prepare('DELETE FROM rinok_poke WHERE id_lot = :lot LIMIT 1')->execute(['lot' => $lotId]);
            if ($startedTransaction) {
                $this->db->commit();
            }
        } catch (Throwable $e) {
            if ($startedTransaction && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['ok' => false, 'message' => 'Не удалось купить покемона.'];
        }

        return ['ok' => true, 'message' => 'Покемон куплен.'];
    }

    public function cancel(int $userId, int $lotId): array
    {
        $startedTransaction = !$this->db->inTransaction();
        if ($startedTransaction) {
            $this->db->beginTransaction();
        }
        try {
            $stmt = $this->db->prepare('SELECT * FROM rinok_poke WHERE id_lot = :lot AND user_pok = :user LIMIT 1 FOR UPDATE');
            $stmt->execute(['lot' => $lotId, 'user' => $userId]);
            $lot = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$lot) {
                if ($startedTransaction) {
                    $this->db->rollBack();
                }
                return ['ok' => false, 'message' => 'Лот не найден.'];
            }
            $active = $this->activePokemonCount($userId) < 6 ? 1 : 0;
            $returnPokemon = $this->db->prepare('UPDATE pok_user SET users = :user, active = :active, startepoke = 0 WHERE id = :pokemon AND users = 3 LIMIT 1');
            $returnPokemon->execute(['user' => $userId, 'active' => $active, 'pokemon' => (int) $lot['id_poke']]);
            if ($returnPokemon->rowCount() !== 1) {
                if ($startedTransaction) {
                    $this->db->rollBack();
                }
                return ['ok' => false, 'message' => 'Покемон в лоте не найден. Лот не снят.'];
            }
            $this->db->prepare('DELETE FROM rinok_poke WHERE id_lot = :lot LIMIT 1')->execute(['lot' => $lotId]);
            if ($startedTransaction) {
                $this->db->commit();
            }
        } catch (Throwable) {
            if ($startedTransaction && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['ok' => false, 'message' => 'Не удалось снять лот.'];
        }

        return ['ok' => true, 'message' => 'Лот снят с продажи.'];
    }

    private function sellablePokemon(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, names, basenum, lvl
               FROM pok_user
              WHERE users = :user AND active = 1 AND startone = 0
           ORDER BY startepoke DESC, id ASC'
        );
        $stmt->execute(['user' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function formatLot(array $row, int $currentUserId): array
    {
        $baseId = (int) ($row['basenum'] ?? 0);
        return [
            'id_lot' => (int) ($row['id_lot'] ?? 0),
            'pokemon_id' => (int) ($row['id_poke'] ?? 0),
            'base_id' => $baseId,
            'name' => strip_tags((string) ($row['names'] ?? $this->cleanBaseName((string) ($row['base_title'] ?? ''), $baseId))),
            'base_name' => $this->cleanBaseName((string) ($row['base_title'] ?? ''), $baseId),
            'level' => (int) ($row['lvl'] ?? 0),
            'price' => (int) ($row['maney'] ?? 0),
            'seller_id' => (int) ($row['user_pok'] ?? 0),
            'seller_login' => (string) ($row['seller_login'] ?? ''),
            'private_to' => (string) ($row['user_id_to'] ?? 'no'),
            'private_buyer_login' => (string) ($row['private_buyer_login'] ?? ''),
            'is_own' => (int) ($row['user_pok'] ?? 0) === $currentUserId,
            'date_end' => (int) ($row['dateend'] ?? 0),
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

    private function userBusy(int $userId): bool
    {
        $stmt = $this->db->prepare('SELECT pve, pvp, trade FROM users WHERE id = :user LIMIT 1');
        $stmt->execute(['user' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        return (int) ($row['pve'] ?? 0) > 0 || (int) ($row['pvp'] ?? 0) > 0 || (int) ($row['trade'] ?? 0) > 0;
    }

    private function activePokemonCount(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM pok_user WHERE users = :user AND active = 1');
        $stmt->execute(['user' => $userId]);
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    private function findUser(string $query): ?array
    {
        if (ctype_digit($query)) {
            $stmt = $this->db->prepare('SELECT id, login FROM users WHERE id = :id LIMIT 1');
            $stmt->execute(['id' => (int) $query]);
        } else {
            $stmt = $this->db->prepare('SELECT id, login FROM users WHERE login = :login LIMIT 1');
            $stmt->execute(['login' => $query]);
        }
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function userLocation(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT buildmy FROM users WHERE id = :user LIMIT 1');
        $stmt->execute(['user' => $userId]);
        return max(1, (int) ($stmt->fetchColumn() ?: 1));
    }

    private function withMarketLock(callable $callback): void
    {
        $lock = $this->db->prepare('SELECT GET_LOCK(:name, 15)');
        $lock->execute(['name' => 'pokemon8_seq_rinok_poke_id']);
        if ((int) ($lock->fetchColumn() ?: 0) !== 1) {
            throw new \RuntimeException('Unable to acquire rinok_poke lock.');
        }
        try {
            $callback();
        } finally {
            $release = $this->db->prepare('SELECT RELEASE_LOCK(:name)');
            $release->execute(['name' => 'pokemon8_seq_rinok_poke_id']);
        }
    }

    private function nextMarketLotId(): int
    {
        return (int) ($this->db->query('SELECT COALESCE(MAX(id_lot), 0) + 1 FROM rinok_poke')->fetchColumn() ?: 1);
    }

    private function cleanBaseName(string $title, int $baseId): string
    {
        $title = trim(html_entity_decode(strip_tags($title), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $title = preg_replace('/^#?0*' . $baseId . '\s*/u', '', $title) ?: $title;
        $title = preg_replace('/^#?0*\d+\s*/u', '', $title) ?: $title;
        return trim($title) !== '' ? trim($title) : ('Pokemon #' . $baseId);
    }
}
