<?php
declare(strict_types=1);

namespace Pokemon8\Repository;

use PDO;
use Throwable;

final class EggRepository
{
    public const INCUBATOR_ITEM_ID = 90311;

    public function __construct(private PDO $db)
    {
    }

    public function listForUser(int $userId, int $page = 1, int $perPage = 45): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(90, $perPage));
        $offset = ($page - 1) * $perPage;

        $totalStmt = $this->db->prepare('SELECT COUNT(*) FROM eggs WHERE users_egg = :user');
        $totalStmt->execute(['user' => $userId]);
        $total = (int) ($totalStmt->fetchColumn() ?: 0);

        $stmt = $this->db->prepare(
            'SELECT e.id_egg, e.base_id_egg, e.dtime, e.attac_one, e.tips,
                    e.hp_iv, e.atk_iv, e.def_iv, e.satk_iv, e.sdef_iv, e.speed_iv,
                    e.parent_one_id, e.parent_two_id, e.parent_one_user_id, e.parent_two_user_id,
                    e.breeding_request_id, e.breeding_method,
                    pb.title AS base_title,
                    ap.atac_name AS egg_attack_name
               FROM eggs e
          LEFT JOIN poke_base pb ON pb.id = e.base_id_egg
          LEFT JOIN attac_power ap ON ap.atac_id = e.attac_one
              WHERE e.users_egg = :user
           ORDER BY e.base_id_egg ASC, e.id_egg ASC
              LIMIT :limit OFFSET :offset'
        );
        $stmt->bindValue(':user', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $now = time();
        $rows = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $baseId = (int) ($row['base_id_egg'] ?? 0);
            $readyAt = (int) ($row['dtime'] ?? 0);
            $rows[] = [
                'id' => (int) ($row['id_egg'] ?? 0),
                'base_id' => $baseId,
                'name' => $this->cleanBaseName((string) ($row['base_title'] ?? ''), $baseId),
                'ready_at' => $readyAt,
                'remaining_seconds' => max(0, $readyAt - $now),
                'ready' => $readyAt <= $now,
                'tips' => (string) ($row['tips'] ?? 'normal'),
                'egg_attack_id' => (int) ($row['attac_one'] ?? 0),
                'egg_attack_name' => (string) ($row['egg_attack_name'] ?? ''),
                'breeding' => [
                    'request_id' => (int) ($row['breeding_request_id'] ?? 0),
                    'method' => (string) ($row['breeding_method'] ?? ''),
                    'parent_one_id' => (int) ($row['parent_one_id'] ?? 0),
                    'parent_two_id' => (int) ($row['parent_two_id'] ?? 0),
                    'parent_one_user_id' => (int) ($row['parent_one_user_id'] ?? 0),
                    'parent_two_user_id' => (int) ($row['parent_two_user_id'] ?? 0),
                ],
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

        return [
            'ok' => true,
            'page' => $page,
            'pages' => max(1, (int) ceil($total / $perPage)),
            'total' => $total,
            'eggs' => $rows,
        ];
    }

    public function incubate(int $userId, int $eggId): array
    {
        $stmt = $this->db->prepare('SELECT id_egg, dtime FROM eggs WHERE id_egg = :egg AND users_egg = :user LIMIT 1');
        $stmt->execute(['egg' => $eggId, 'user' => $userId]);
        $egg = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$egg) {
            return ['ok' => false, 'message' => 'Яйцо не найдено.'];
        }

        $now = time();
        $readyAt = (int) ($egg['dtime'] ?? 0);
        if ($readyAt <= $now) {
            return ['ok' => true, 'message' => 'Яйцо уже готово к вылуплению.', 'ready_at' => $readyAt];
        }

        $startedTransaction = !$this->db->inTransaction();
        if ($startedTransaction) {
            $this->db->beginTransaction();
        }
        try {
            if (!$this->consumeItem($userId, self::INCUBATOR_ITEM_ID, 1)) {
                if ($startedTransaction) {
                    $this->db->rollBack();
                }
                return ['ok' => false, 'message' => 'Нужен Инкубатор для ускорения яйца.'];
            }

            $remaining = max(60, $readyAt - $now);
            $newReadyAt = $now + max(60, (int) ceil($remaining / 2));
            $this->db->prepare('UPDATE eggs SET dtime = :time WHERE id_egg = :egg AND users_egg = :user LIMIT 1')
                ->execute(['time' => $newReadyAt, 'egg' => $eggId, 'user' => $userId]);

            if ($startedTransaction) {
                $this->db->commit();
            }
        } catch (Throwable $e) {
            if ($startedTransaction && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['ok' => false, 'message' => 'Инкубация не выполнена: ' . $e->getMessage()];
        }

        return ['ok' => true, 'message' => 'Инкубатор сократил оставшееся время в 2 раза.', 'ready_at' => $newReadyAt];
    }

    public function hatch(int $userId, int $eggId): array
    {
        $stmt = $this->db->prepare(
            'SELECT e.*, pb.title, pb.hp, pb.atk, pb.def, pb.satk, pb.sdef, pb.speed
               FROM eggs e
          LEFT JOIN poke_base pb ON pb.id = e.base_id_egg
              WHERE e.id_egg = :egg AND e.users_egg = :user
              LIMIT 1'
        );
        $stmt->execute(['egg' => $eggId, 'user' => $userId]);
        $egg = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$egg) {
            return ['ok' => false, 'message' => 'Яйцо не найдено.'];
        }
        if ((int) ($egg['dtime'] ?? 0) > time()) {
            return ['ok' => false, 'message' => 'Яйцо ещё не готово к вылуплению.'];
        }

        $baseId = (int) ($egg['base_id_egg'] ?? 0);
        if ($baseId <= 0 || empty($egg['title'])) {
            return ['ok' => false, 'message' => 'У яйца не найден базовый покемон.'];
        }

        $level = 1;
        $har = random_int(1, 26);
        $natureStmt = $this->db->prepare('SELECT atk, def, satk, sdef, speed FROM har WHERE id_har = :har LIMIT 1');
        $natureStmt->execute(['har' => $har]);
        $nature = $natureStmt->fetch(PDO::FETCH_ASSOC) ?: ['atk' => 1, 'def' => 1, 'satk' => 1, 'sdef' => 1, 'speed' => 1];
        $iv = [
            'hp' => max(0, (int) ($egg['hp_iv'] ?? 0)),
            'atk' => max(0, (int) ($egg['atk_iv'] ?? 0)),
            'def' => max(0, (int) ($egg['def_iv'] ?? 0)),
            'satk' => max(0, (int) ($egg['satk_iv'] ?? 0)),
            'sdef' => max(0, (int) ($egg['sdef_iv'] ?? 0)),
            'speed' => max(0, (int) ($egg['speed_iv'] ?? 0)),
        ];
        $calcStat = static function (int $baseValue, int $ivValue, float $natureValue): int {
            return max(1, (int) round(((($ivValue + $baseValue * 2) / 100) + 5) * max(0.1, $natureValue)));
        };
        $stats = [
            'hp' => max(1, (int) round(($iv['hp'] + (int) $egg['hp'] * 2 + 100) / 100 + 10)),
            'atk' => $calcStat((int) $egg['atk'], $iv['atk'], (float) $nature['atk']),
            'def' => $calcStat((int) $egg['def'], $iv['def'], (float) $nature['def']),
            'satk' => $calcStat((int) $egg['satk'], $iv['satk'], (float) $nature['satk']),
            'sdef' => $calcStat((int) $egg['sdef'], $iv['sdef'], (float) $nature['sdef']),
            'speed' => $calcStat((int) $egg['speed'], $iv['speed'], (float) $nature['speed']),
        ];
        $name = $this->cleanBaseName((string) $egg['title'], $baseId) . ((string) ($egg['tips'] ?? 'normal') === 'shine' ? ' - Shiny' : '');

        $startedTransaction = !$this->db->inTransaction();
        if ($startedTransaction) {
            $this->db->beginTransaction();
        }
        try {
            $pokemonId = $this->nextTableId('pok_user', 'id');
            $this->db->prepare(
                'INSERT INTO pok_user
                    (id, users, basenum, names, active, evcount, lvl, sex, har, hp_my, hp_max, exp, exp_b,
                     atk, def, satk, sdef, speed, hp_ev, atk_ev, def_ev, satk_ev, sdef_ev, speed_ev,
                     hp_iv, atk_iv, def_iv, satk_iv, sdef_iv, speed_iv, tips, startone, startepoke,
                     reproduction, happy, datemay, usersone, sprz, item)
                 VALUES
                    (:id, :user, :base, :name, 0, 0, :level, :sex, :har, :hp_my, :hp_max, 0, 0,
                     :atk, :def, :satk, :sdef, :speed, 0, 0, 0, 0, 0, 0,
                     :hp_iv, :atk_iv, :def_iv, :satk_iv, :sdef_iv, :speed_iv, :tips, 0, 0,
                     :reproduction, 0, NOW(), :usersone, 0, 0)'
            )->execute([
                'id' => $pokemonId,
                'user' => $userId,
                'base' => $baseId,
                'name' => $name,
                'level' => $level,
                'sex' => random_int(1, 2),
                'har' => $har,
                'hp_my' => $stats['hp'],
                'hp_max' => $stats['hp'],
                'atk' => $stats['atk'],
                'def' => $stats['def'],
                'satk' => $stats['satk'],
                'sdef' => $stats['sdef'],
                'speed' => $stats['speed'],
                'hp_iv' => $iv['hp'],
                'atk_iv' => $iv['atk'],
                'def_iv' => $iv['def'],
                'satk_iv' => $iv['satk'],
                'sdef_iv' => $iv['sdef'],
                'speed_iv' => $iv['speed'],
                'tips' => (string) ($egg['tips'] ?? 'normal') === 'shine' ? 'shine' : 'normal',
                'reproduction' => (int) ($egg['spar'] ?? 0),
                'usersone' => $userId,
            ]);

            $move = $this->seedHatchedPokemonMove($pokemonId, $baseId, (int) ($egg['attac_one'] ?? 0));
            $this->db->prepare('DELETE FROM eggs WHERE id_egg = :egg AND users_egg = :user LIMIT 1')
                ->execute(['egg' => $eggId, 'user' => $userId]);
            if ($startedTransaction) {
                $this->db->commit();
            }
        } catch (Throwable $e) {
            if ($startedTransaction && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['ok' => false, 'message' => 'Не удалось вылупить яйцо: ' . $e->getMessage()];
        }

        return [
            'ok' => true,
            'message' => sprintf('Из яйца вылупился %s. Покемон отправлен в питомник.', $name),
            'pokemon_id' => $pokemonId,
            'start_move' => $move,
        ];
    }

    private function seedHatchedPokemonMove(int $pokemonId, int $baseId, int $preferredMoveId): ?array
    {
        if ($preferredMoveId > 0) {
            $stmt = $this->db->prepare('SELECT atac_id, atac_name, atac_pp FROM attac_power WHERE atac_id = :id LIMIT 1');
            $stmt->execute(['id' => $preferredMoveId]);
            $move = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $stmt = $this->db->prepare(
                'SELECT ap.atac_id, p.atac_name, p.atac_pp
                   FROM attac_poke ap
                   JOIN attac_power p ON p.atac_id = ap.atac_id
                  WHERE ap.poke_base_id = :base
               ORDER BY ap.atc_lvl ASC, ap.id_structure ASC
                  LIMIT 1'
            );
            $stmt->execute(['base' => $baseId]);
            $move = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        if (!$move) {
            return null;
        }

        $pp = max(0, (int) ($move['atac_pp'] ?? 0));
        $this->db->prepare(
            'INSERT INTO attac_my_poke
                (id, a_id, a_pp_min, a_pp_max, b_id, b_pp_min, b_pp_max, c_id, c_pp_min, c_pp_max, d_id, d_pp_min, d_pp_max, pok_id)
             VALUES
                (:id, :move, :pp_min, :pp_max, 0, 0, 0, 0, 0, 0, 0, 0, 0, :pokemon)'
        )->execute([
            'id' => $this->nextTableId('attac_my_poke', 'id'),
            'pokemon' => $pokemonId,
            'move' => (int) $move['atac_id'],
            'pp_min' => $pp,
            'pp_max' => $pp,
        ]);

        return ['id' => (int) $move['atac_id'], 'name' => (string) $move['atac_name'], 'pp' => $pp];
    }

    private function nextTableId(string $table, string $column): int
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table) || !preg_match('/^[a-zA-Z0-9_]+$/', $column)) {
            return 1;
        }
        return (int) ($this->db->query('SELECT COALESCE(MAX(`' . $column . '`), 0) + 1 FROM `' . $table . '`')->fetchColumn() ?: 1);
    }

    private function consumeItem(int $userId, int $itemId, int $count): bool
    {
        if ($count <= 0) {
            return true;
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
        $stmt->execute(['user' => $userId, 'item' => $itemId, 'time' => time()]);

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            if ($remaining <= 0) {
                break;
            }

            $rowId = (int) ($row['id'] ?? 0);
            $rowCount = (int) ($row['count'] ?? 0);
            $take = min($rowCount, $remaining);
            $newCount = $rowCount - $take;
            if ($newCount > 0) {
                $this->db->prepare('UPDATE items_users SET count = :count WHERE id = :id LIMIT 1')
                    ->execute(['count' => $newCount, 'id' => $rowId]);
            } else {
                $this->db->prepare('DELETE FROM items_users WHERE id = :id LIMIT 1')
                    ->execute(['id' => $rowId]);
            }
            $remaining -= $take;
        }

        return $remaining <= 0;
    }

    private function cleanBaseName(string $title, int $baseId): string
    {
        $title = trim(html_entity_decode(strip_tags($title), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $title = preg_replace('/^#?0*' . $baseId . '\s*/u', '', $title) ?: $title;
        $title = preg_replace('/^#?0*\d+\s*/u', '', $title) ?: $title;
        return trim($title) !== '' ? trim($title) : ('Pokemon #' . $baseId);
    }
}
