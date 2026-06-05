<?php
declare(strict_types=1);

namespace Pokemon8\Repository;

use PDO;
use Pokemon8\Game\PokemonFormCatalog;

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
        $this->normalizeActiveTeam($userId);
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
        $this->normalizeActiveTeam($userId);
        if ($this->countActivePokemon($userId) <= 1) {
            return false;
        }

        $stmt = $this->db->prepare(
            'UPDATE pok_user
                SET active = 0,
                    startepoke = 0,
                    happy = GREATEST(0, happy - 10)
              WHERE id = :pokemon AND users = :user AND active = 1 AND startepoke <> 1
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
        $this->normalizeActiveTeam($userId);
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

    public function nurseryAction(int $userId, int $pokemonId, string $action): array
    {
        if ($pokemonId <= 0) {
            return ['ok' => false, 'message' => 'Покемон не выбран.', 'pokemon' => $this->moveEditorData($userId)];
        }

        $busy = $this->db->prepare('SELECT pve, pvp FROM users WHERE id = :user LIMIT 1');
        $busy->execute(['user' => $userId]);
        $user = $busy->fetch();
        if ($user && ((int) ($user['pve'] ?? 0) > 0 || (int) ($user['pvp'] ?? 0) > 0)) {
            return ['ok' => false, 'message' => 'Сначала закончите бой.', 'pokemon' => $this->moveEditorData($userId)];
        }

        $pokemon = $this->ownedPokemon($userId, $pokemonId);
        if ($pokemon === null) {
            return ['ok' => false, 'message' => 'Покемон не найден.', 'pokemon' => $this->moveEditorData($userId)];
        }

        if ($action === 'store') {
            if ((int) ($pokemon['active'] ?? 0) !== 1) {
                return ['ok' => false, 'message' => 'Этот покемон уже находится в питомнике.', 'pokemon' => $this->moveEditorData($userId)];
            }
            if ((int) ($pokemon['startepoke'] ?? 0) === 1) {
                return ['ok' => false, 'message' => 'Стартового покемона нельзя отправить в питомник.', 'pokemon' => $this->moveEditorData($userId)];
            }
            if (!$this->moveToNursery($userId, $pokemonId)) {
                return ['ok' => false, 'message' => 'При себе должен остаться хотя бы один активный покемон.', 'pokemon' => $this->moveEditorData($userId)];
            }
            return ['ok' => true, 'message' => 'Покемон отправлен в питомник.', 'pokemon' => $this->moveEditorData($userId)];
        }

        if ($action === 'take') {
            if ((int) ($pokemon['active'] ?? 0) === 1) {
                return ['ok' => false, 'message' => 'Этот покемон уже находится в команде.', 'pokemon' => $this->moveEditorData($userId)];
            }
            if (!$this->moveFromNursery($userId, $pokemonId)) {
                return ['ok' => false, 'message' => 'В команде уже 6 покемонов. Сначала отправьте кого-нибудь в питомник.', 'pokemon' => $this->moveEditorData($userId)];
            }
            return ['ok' => true, 'message' => 'Покемон добавлен в команду.', 'pokemon' => $this->moveEditorData($userId)];
        }

        return ['ok' => false, 'message' => 'Неизвестное действие питомника.', 'pokemon' => $this->moveEditorData($userId)];
    }

    public function moveEditorData(int $userId): array
    {
        $this->normalizeActiveTeam($userId);
        $pokemon = array_merge(
            $this->listActivePokemon($userId, 6),
            $this->listNurseryPokemon($userId, 500)
        );
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
                'INSERT INTO attac_my_poke
                    (id, pok_id, a_id, a_pp_min, a_pp_max, b_id, b_pp_min, b_pp_max, c_id, c_pp_min, c_pp_max, d_id, d_pp_min, d_pp_max)
                 VALUES
                    (:id, :pokemon, :a_id, :a_pp_min, :a_pp_max, :b_id, :b_pp_min, :b_pp_max, :c_id, :c_pp_min, :c_pp_max, :d_id, :d_pp_min, :d_pp_max)'
            )->execute([
                'id' => $this->nextAttacMyPokeId(),
                'pokemon' => $pokemonId,
                'a_id' => $slot === 'a' ? $moveId : 0,
                'a_pp_min' => $slot === 'a' ? $pp : 0,
                'a_pp_max' => $slot === 'a' ? $pp : 0,
                'b_id' => $slot === 'b' ? $moveId : 0,
                'b_pp_min' => $slot === 'b' ? $pp : 0,
                'b_pp_max' => $slot === 'b' ? $pp : 0,
                'c_id' => $slot === 'c' ? $moveId : 0,
                'c_pp_min' => $slot === 'c' ? $pp : 0,
                'c_pp_max' => $slot === 'c' ? $pp : 0,
                'd_id' => $slot === 'd' ? $moveId : 0,
                'd_pp_min' => $slot === 'd' ? $pp : 0,
                'd_pp_max' => $slot === 'd' ? $pp : 0,
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

    private function nextAttacMyPokeId(): int
    {
        return (int) ($this->db->query('SELECT COALESCE(MAX(id), 0) + 1 FROM attac_my_poke')->fetchColumn() ?: 1);
    }

    /**
     * @return list<int>
     */
    private function activePokemonIds(int $userId): array
    {
        $this->normalizeActiveTeam($userId);
        $stmt = $this->db->prepare('SELECT id FROM pok_user WHERE users = :user AND active = 1');
        $stmt->execute(['user' => $userId]);
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN) ?: []);
    }

    private function normalizeActiveTeam(int $userId): int
    {
        $stmt = $this->db->prepare(
            'SELECT id
               FROM pok_user
              WHERE users = :user AND active = 1
              ORDER BY startepoke DESC, id ASC'
        );
        $stmt->execute(['user' => $userId]);
        $ids = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN) ?: []);
        if (count($ids) <= 6) {
            return 0;
        }

        $overflow = array_slice($ids, 6);
        $placeholders = implode(',', array_fill(0, count($overflow), '?'));
        $update = $this->db->prepare(
            'UPDATE pok_user
                SET active = 0, startepoke = 0
              WHERE users = ? AND id IN (' . $placeholders . ')'
        );
        $update->execute(array_merge([$userId], $overflow));
        return $update->rowCount();
    }

    private function ownedPokemon(int $userId, int $pokemonId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, users, active, startepoke
               FROM pok_user
              WHERE id = :pokemon AND users = :user
              LIMIT 1'
        );
        $stmt->execute(['pokemon' => $pokemonId, 'user' => $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    private function listPokemonByActive(int $userId, int $active, int $limit): array
    {
        $stmt = $this->db->prepare(
            'SELECT pu.id, pu.names, pu.basenum, pu.lvl, pu.hp_my, pu.hp_max, pu.happy, pu.startepoke,
                    pu.atk, pu.def, pu.satk, pu.sdef, pu.speed,
                    pu.hp_ev, pu.atk_ev, pu.def_ev, pu.satk_ev, pu.sdef_ev, pu.speed_ev,
                    pu.training_stage, pu.training_stat, pu.training_named_effect, pu.training_tamed,
                    COALESCE(ip.id_items, pu.item, 0) AS held_item_id,
                    held.name AS held_item_name,
                    held.tittle AS held_item_title
               FROM pok_user pu
          LEFT JOIN items_poke ip ON ip.id_poke = pu.id
          LEFT JOIN items held ON held.id = COALESCE(ip.id_items, pu.item, 0)
              WHERE pu.users = :user AND pu.active = :active
              ORDER BY pu.startepoke DESC, pu.basenum ASC, pu.id ASC
              LIMIT :limit'
        );
        $stmt->bindValue(':user', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':active', $active, PDO::PARAM_INT);
        $stmt->bindValue(':limit', max(1, min(50, $limit)), PDO::PARAM_INT);
        $stmt->execute();

        $rows = [];
        foreach ($stmt->fetchAll() as $row) {
            $ev = [
                'hp' => max(0, (int) ($row['hp_ev'] ?? 0)),
                'atk' => max(0, (int) ($row['atk_ev'] ?? 0)),
                'def' => max(0, (int) ($row['def_ev'] ?? 0)),
                'speed' => max(0, (int) ($row['speed_ev'] ?? 0)),
                'satk' => max(0, (int) ($row['satk_ev'] ?? 0)),
                'sdef' => max(0, (int) ($row['sdef_ev'] ?? 0)),
            ];
            $evTotal = array_sum($ev);
            $evMaxPerStat = 252;
            $evMaxTotal = 510;
            $evRemainingTotal = max(0, $evMaxTotal - $evTotal);
            $vitaminStep = 10;
            $vitaminByStat = [];
            foreach ($ev as $statKey => $value) {
                $vitaminByStat[$statKey] = intdiv(max(0, min($evMaxPerStat - $value, $evRemainingTotal)), $vitaminStep);
            }

            $rows[] = [
                'id' => (int) $row['id'],
                'name' => strip_tags((string) $row['names']),
                'baseNum' => (int) $row['basenum'],
                'formId' => (int) $row['basenum'],
                'dexNumber' => PokemonFormCatalog::displayBaseId((int) $row['basenum']),
                'displayBaseNum' => PokemonFormCatalog::displayBaseId((int) $row['basenum']),
                'formKey' => PokemonFormCatalog::formKey((int) $row['basenum'], (string) $row['names']),
                'isForm' => PokemonFormCatalog::isForm((int) $row['basenum']),
                'level' => (int) $row['lvl'],
                'hp' => (int) $row['hp_my'],
                'hpMax' => (int) $row['hp_max'],
                'happiness' => max(0, min(100, (int) ($row['happy'] ?? 0))),
                'stats' => [
                    'hp' => (int) $row['hp_max'],
                    'atk' => (int) $row['atk'],
                    'def' => (int) $row['def'],
                    'speed' => (int) $row['speed'],
                    'satk' => (int) $row['satk'],
                    'sdef' => (int) $row['sdef'],
                ],
                'ev' => $ev + [
                    'total' => $evTotal,
                    'maxPerStat' => $evMaxPerStat,
                    'maxTotal' => $evMaxTotal,
                    'remainingTotal' => $evRemainingTotal,
                ],
                'vitaminCapacity' => [
                    'step' => $vitaminStep,
                    'remainingTotalUses' => intdiv($evRemainingTotal, $vitaminStep),
                    'byStat' => $vitaminByStat,
                ],
                'active' => $active === 1,
                'starter' => (int) $row['startepoke'] === 1,
                'heldItem' => [
                    'id' => (int) ($row['held_item_id'] ?? 0),
                    'name' => strip_tags((string) ($row['held_item_name'] ?? '')),
                    'title' => strip_tags((string) ($row['held_item_title'] ?? '')),
                ],
                'training' => $this->trainingInfo($row),
            ];
        }

        return $rows;
    }

    private function trainingInfo(array $row): array
    {
        $stage = max(0, min(6, (int) ($row['training_stage'] ?? 0)));
        $stages = [
            0 => ['name' => 'Без тренировки', 'bonus' => 0, 'success' => 100.0, 'immuneSuccess' => 100.0, 'weaken' => 0.0, 'icon' => ''],
            1 => ['name' => 'Начальная', 'bonus' => 10, 'success' => 65.0, 'immuneSuccess' => 100.0, 'weaken' => 1.0, 'icon' => 'I'],
            2 => ['name' => 'Расширенная', 'bonus' => 18, 'success' => 43.0, 'immuneSuccess' => 45.0, 'weaken' => 3.0, 'icon' => 'II'],
            3 => ['name' => 'Мастерская', 'bonus' => 25, 'success' => 11.0, 'immuneSuccess' => 12.0, 'weaken' => 6.0, 'icon' => 'III'],
            4 => ['name' => 'Знаменитая', 'bonus' => 31, 'success' => 6.0, 'immuneSuccess' => 6.5, 'weaken' => 10.0, 'icon' => 'IV'],
            5 => ['name' => 'Легендарная', 'bonus' => 36, 'success' => 3.0, 'immuneSuccess' => 3.2, 'weaken' => 55.0, 'icon' => 'V'],
            6 => ['name' => 'Именная', 'bonus' => 40, 'success' => 1.9, 'immuneSuccess' => 2.3, 'weaken' => 90.0, 'icon' => 'MAX'],
        ];
        $stat = (string) ($row['training_stat'] ?? '');
        $meta = $stages[$stage];
        return [
            'stage' => $stage,
            'stageName' => $meta['name'],
            'bonus' => $meta['bonus'],
            'successChance' => $meta['success'],
            'boostedSuccessChance' => $meta['immuneSuccess'],
            'weakenChance' => $meta['weaken'],
            'icon' => $meta['icon'],
            'stat' => $stat,
            'statLabel' => $this->trainingStatLabel($stat),
            'namedEffect' => (string) ($row['training_named_effect'] ?? ''),
            'tamed' => (int) ($row['training_tamed'] ?? 0) === 1,
        ];
    }

    private function trainingStatLabel(string $stat): string
    {
        return match ($stat) {
            'atk' => 'Атака',
            'def' => 'Защита',
            'satk' => 'Спец. атака',
            'sdef' => 'Спец. защита',
            'speed' => 'Скорость',
            default => 'Не выбран',
        };
    }

    private function selectedMoves(int $pokemonId): array
    {
        $stmt = $this->db->prepare(
            'SELECT amp.a_id, amp.b_id, amp.c_id, amp.d_id,
                    amp.a_pp_min, amp.a_pp_max, amp.b_pp_min, amp.b_pp_max, amp.c_pp_min, amp.c_pp_max, amp.d_pp_min, amp.d_pp_max,
                    ap1.atac_name AS a_name, ap1.atac_tip AS a_type,
                    ap2.atac_name AS b_name, ap2.atac_tip AS b_type,
                    ap3.atac_name AS c_name, ap3.atac_tip AS c_type,
                    ap4.atac_name AS d_name, ap4.atac_tip AS d_type
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
        $emptyMove = ['id' => 0, 'name' => 'Нет атаки', 'type' => 'Normal', 'pp' => 0, 'ppMax' => 0];
        $slot = function (string $idKey, string $nameKey, string $typeKey, string $ppMinKey, string $ppMaxKey) use ($row, &$seen, $emptyMove): array {
            $id = (int) ($row[$idKey] ?? 0);
            if ($id <= 0 || isset($seen[$id])) {
                return $emptyMove;
            }
            $seen[$id] = true;
            return [
                'id' => $id,
                'name' => (string) ($row[$nameKey] ?? 'Нет атаки'),
                'type' => (string) ($row[$typeKey] ?? 'Normal'),
                'pp' => (int) ($row[$ppMinKey] ?? 0),
                'ppMax' => (int) ($row[$ppMaxKey] ?? 0),
            ];
        };

        return [
            'a' => $slot('a_id', 'a_name', 'a_type', 'a_pp_min', 'a_pp_max'),
            'b' => $slot('b_id', 'b_name', 'b_type', 'b_pp_min', 'b_pp_max'),
            'c' => $slot('c_id', 'c_name', 'c_type', 'c_pp_min', 'c_pp_max'),
            'd' => $slot('d_id', 'd_name', 'd_type', 'd_pp_min', 'd_pp_max'),
        ];
    }

    private function learnableMoves(int $baseNum, int $level): array
    {
        $moves = $this->learnableMovesForBase($baseNum, $level);
        if ($moves !== [] || !PokemonFormCatalog::isForm($baseNum)) {
            return $moves;
        }

        return $this->learnableMovesForBase(PokemonFormCatalog::displayBaseId($baseNum), $level);
    }

    private function learnableMovesForBase(int $baseNum, int $level): array
    {
        $stmt = $this->db->prepare(
            'SELECT ap.atac_id AS id, ap.atc_lvl AS level, apw.atac_name AS name, apw.atac_tip AS type, apw.atac_pp AS pp
               FROM attac_poke ap
               INNER JOIN attac_power apw ON apw.atac_id = ap.atac_id
              WHERE ap.poke_base_id = :base AND ap.atc_lvl <= :lvl
              ORDER BY ap.atc_lvl DESC, ap.atac_id DESC
              LIMIT 240'
        );
        $stmt->execute(['base' => $baseNum, 'lvl' => $level]);

        $moves = [];
        foreach ($stmt->fetchAll() as $row) {
            $moves[] = [
                'id' => (int) $row['id'],
                'name' => (string) $row['name'],
                'level' => (int) $row['level'],
                'type' => (string) ($row['type'] ?? 'Normal'),
                'pp' => (int) ($row['pp'] ?? 0),
            ];
        }

        return $moves;
    }
}
