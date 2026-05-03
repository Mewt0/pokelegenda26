<?php
declare(strict_types=1);

namespace Pokemon8\Repository;

use PDO;

final class PokemonRepository
{
    public function __construct(private PDO $db)
    {
    }

    public function healActivePokemon(int $userId): int
    {
        $stmt = $this->db->prepare(
            'UPDATE pok_user SET hp_my = hp_max WHERE users = :user AND active = 1'
        );
        $stmt->execute(['user' => $userId]);
        $healed = $stmt->rowCount();

        $ids = $this->activePokemonIds($userId);
        foreach ($ids as $pokemonId) {
            $this->db->prepare(
                'UPDATE attac_my_poke
                    SET a_pp_min = a_pp_max,
                        b_pp_min = b_pp_max,
                        c_pp_min = c_pp_max,
                        d_pp_min = d_pp_max
                  WHERE pok_id = :pokemon'
            )->execute(['pokemon' => $pokemonId]);
        }

        return $healed;
    }

    public function countActivePokemon(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM pok_user WHERE users = :user AND active = 1');
        $stmt->execute(['user' => $userId]);
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    public function listActivePokemon(int $userId, int $limit = 12): array
    {
        return $this->listPokemonByActive($userId, 1, $limit);
    }

    public function listNurseryPokemon(int $userId, int $limit = 12): array
    {
        return $this->listPokemonByActive($userId, 0, $limit);
    }

    public function moveToNursery(int $userId, int $pokemonId): bool
    {
        if ($this->countActivePokemon($userId) <= 1) {
            return false;
        }

        $stmt = $this->db->prepare(
            'UPDATE pok_user
                SET active = 0, startepoke = 0
              WHERE id = :pokemon AND users = :user AND active = 1
              LIMIT 1'
        );
        $stmt->execute([
            'pokemon' => $pokemonId,
            'user' => $userId,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function moveFromNursery(int $userId, int $pokemonId): bool
    {
        if ($this->countActivePokemon($userId) >= 6) {
            return false;
        }

        $stmt = $this->db->prepare(
            'UPDATE pok_user
                SET active = 1
              WHERE id = :pokemon AND users = :user AND active = 0
              LIMIT 1'
        );
        $stmt->execute([
            'pokemon' => $pokemonId,
            'user' => $userId,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function moveEditorData(int $userId): array
    {
        $pokemon = $this->listActivePokemon($userId, 20);
        foreach ($pokemon as &$row) {
            $row['moves'] = $this->selectedMoves((int) $row['id']);
            $row['learnableMoves'] = $this->learnableMoves((int) $row['baseNum'], (int) $row['level']);
        }
        unset($row);

        return $pokemon;
    }

    public function setMove(int $userId, int $pokemonId, string $slot, int $moveId): array
    {
        $slotMap = [
            'a' => ['id' => 'a_id', 'ppMin' => 'a_pp_min', 'ppMax' => 'a_pp_max'],
            'b' => ['id' => 'b_id', 'ppMin' => 'b_pp_min', 'ppMax' => 'b_pp_max'],
            'c' => ['id' => 'c_id', 'ppMin' => 'c_pp_min', 'ppMax' => 'c_pp_max'],
            'd' => ['id' => 'd_id', 'ppMin' => 'd_pp_min', 'ppMax' => 'd_pp_max'],
        ];
        if (!isset($slotMap[$slot])) {
            return ['ok' => false, 'message' => 'Неверный слот атаки.'];
        }

        $busy = $this->db->prepare('SELECT pve, pvp FROM users WHERE id = :user LIMIT 1');
        $busy->execute(['user' => $userId]);
        $user = $busy->fetch();
        if ($user && ((int) ($user['pve'] ?? 0) > 0 || (int) ($user['pvp'] ?? 0) > 0)) {
            return ['ok' => false, 'message' => 'Сначала закончите бой.'];
        }

        $pokeStmt = $this->db->prepare(
            'SELECT id, basenum, lvl FROM pok_user WHERE id = :pokemon AND users = :user AND active = 1 LIMIT 1'
        );
        $pokeStmt->execute(['pokemon' => $pokemonId, 'user' => $userId]);
        $pokemon = $pokeStmt->fetch();
        if (!$pokemon) {
            return ['ok' => false, 'message' => 'Покемон не найден в команде.'];
        }

        $current = $this->db->prepare('SELECT * FROM attac_my_poke WHERE pok_id = :pokemon LIMIT 1');
        $current->execute(['pokemon' => $pokemonId]);
        $row = $current->fetch();

        if ($moveId <= 0) {
            if ($row) {
                $this->db->prepare(
                    'UPDATE attac_my_poke
                        SET ' . $slotMap[$slot]['id'] . ' = 0,
                            ' . $slotMap[$slot]['ppMin'] . ' = 0,
                            ' . $slotMap[$slot]['ppMax'] . ' = 0
                      WHERE pok_id = :pokemon
                      LIMIT 1'
                )->execute(['pokemon' => $pokemonId]);
            }
            return ['ok' => true, 'message' => 'Слот атаки очищен.'];
        }

        $learn = $this->db->prepare(
            'SELECT ap.atac_id, apw.atac_pp
               FROM attac_poke ap
               INNER JOIN attac_power apw ON apw.atac_id = ap.atac_id
              WHERE ap.poke_base_id = :base AND ap.atac_id = :move AND ap.atc_lvl <= :lvl
              LIMIT 1'
        );
        $learn->execute([
            'base' => (int) $pokemon['basenum'],
            'move' => $moveId,
            'lvl' => (int) $pokemon['lvl'],
        ]);
        $move = $learn->fetch();
        if (!$move) {
            return ['ok' => false, 'message' => 'Этот покемон не может изучить выбранную атаку.'];
        }

        if ($row) {
            foreach (['a_id', 'b_id', 'c_id', 'd_id'] as $moveColumn) {
                if ($moveColumn !== $slotMap[$slot]['id'] && (int) ($row[$moveColumn] ?? 0) === $moveId) {
                    return ['ok' => false, 'message' => 'Эта атака уже стоит у покемона.'];
                }
            }
        }

        $pp = max(1, (int) ($move['atac_pp'] ?? 15));
        if (!$row) {
            $this->db->prepare(
                'INSERT INTO attac_my_poke (pok_id, ' . $slotMap[$slot]['id'] . ', ' . $slotMap[$slot]['ppMin'] . ', ' . $slotMap[$slot]['ppMax'] . ')
                 VALUES (:pokemon, :move, :pp_min, :pp_max)'
            )->execute([
                'pokemon' => $pokemonId,
                'move' => $moveId,
                'pp_min' => $pp,
                'pp_max' => $pp,
            ]);
        } else {
            $this->db->prepare(
                'UPDATE attac_my_poke
                    SET ' . $slotMap[$slot]['id'] . ' = :move,
                        ' . $slotMap[$slot]['ppMin'] . ' = :pp_min,
                        ' . $slotMap[$slot]['ppMax'] . ' = :pp_max
                  WHERE pok_id = :pokemon
                  LIMIT 1'
            )->execute([
                'move' => $moveId,
                'pp_min' => $pp,
                'pp_max' => $pp,
                'pokemon' => $pokemonId,
            ]);
        }

        return ['ok' => true, 'message' => 'Атака обновлена.'];
    }

    /**
     * @return list<int>
     */
    private function activePokemonIds(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT id FROM pok_user WHERE users = :user AND active = 1');
        $stmt->execute(['user' => $userId]);
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN) ?: []);
    }

    private function listPokemonByActive(int $userId, int $active, int $limit): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, names, basenum, lvl, hp_my, hp_max, startepoke
               FROM pok_user
              WHERE users = :user AND active = :active
              ORDER BY startepoke DESC, basenum ASC, id ASC
              LIMIT :limit'
        );
        $stmt->bindValue(':user', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':active', $active, PDO::PARAM_INT);
        $stmt->bindValue(':limit', max(1, min(50, $limit)), PDO::PARAM_INT);
        $stmt->execute();

        $rows = [];
        foreach ($stmt->fetchAll() as $row) {
            $rows[] = [
                'id' => (int) $row['id'],
                'name' => strip_tags((string) $row['names']),
                'baseNum' => (int) $row['basenum'],
                'level' => (int) $row['lvl'],
                'hp' => (int) $row['hp_my'],
                'hpMax' => (int) $row['hp_max'],
                'starter' => (int) $row['startepoke'] === 1,
            ];
        }

        return $rows;
    }

    private function selectedMoves(int $pokemonId): array
    {
        $stmt = $this->db->prepare(
            'SELECT amp.a_id, amp.b_id, amp.c_id, amp.d_id,
                    ap1.atac_name AS a_name, ap2.atac_name AS b_name, ap3.atac_name AS c_name, ap4.atac_name AS d_name
               FROM attac_my_poke amp
               LEFT JOIN attac_power ap1 ON ap1.atac_id = amp.a_id
               LEFT JOIN attac_power ap2 ON ap2.atac_id = amp.b_id
               LEFT JOIN attac_power ap3 ON ap3.atac_id = amp.c_id
               LEFT JOIN attac_power ap4 ON ap4.atac_id = amp.d_id
              WHERE amp.pok_id = :pokemon
              LIMIT 1'
        );
        $stmt->execute(['pokemon' => $pokemonId]);
        $row = $stmt->fetch() ?: [];
        $seen = [];
        $slot = function (string $idKey, string $nameKey) use ($row, &$seen): array {
            $id = (int) ($row[$idKey] ?? 0);
            if ($id <= 0 || isset($seen[$id])) {
                return ['id' => 0, 'name' => 'Нет атаки'];
            }
            $seen[$id] = true;
            return ['id' => $id, 'name' => (string) ($row[$nameKey] ?? 'Нет атаки')];
        };

        return [
            'a' => $slot('a_id', 'a_name'),
            'b' => $slot('b_id', 'b_name'),
            'c' => $slot('c_id', 'c_name'),
            'd' => $slot('d_id', 'd_name'),
        ];
    }

    private function learnableMoves(int $baseNum, int $level): array
    {
        $stmt = $this->db->prepare(
            'SELECT ap.atac_id AS id, ap.atc_lvl AS level, apw.atac_name AS name
               FROM attac_poke ap
               INNER JOIN attac_power apw ON apw.atac_id = ap.atac_id
              WHERE ap.poke_base_id = :base AND ap.atc_lvl <= :lvl
              ORDER BY ap.atc_lvl DESC, ap.atac_id DESC
              LIMIT 80'
        );
        $stmt->execute(['base' => $baseNum, 'lvl' => $level]);

        $moves = [];
        foreach ($stmt->fetchAll() as $row) {
            $moves[] = [
                'id' => (int) $row['id'],
                'name' => (string) $row['name'],
                'level' => (int) $row['level'],
            ];
        }

        return $moves;
    }
}
