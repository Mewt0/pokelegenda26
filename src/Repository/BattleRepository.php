<?php
declare(strict_types=1);

namespace Pokemon8\Repository;

use PDO;

final class BattleRepository
{
    public function __construct(private PDO $db)
    {
    }

    public function findActivePveBattleIdForUser(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT battleid FROM users WHERE id = :id AND pve = 1 LIMIT 1');
        $stmt->execute(['id' => $userId]);
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    public function findPveBattleForUser(int $userId, int $battleId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, user_1, user_2, poke_1, poke_2, attac_1, attac_2, raund, hod_user_id, pobeda
               FROM battles
              WHERE id = :id AND user_1 = :user AND batl_tip = "pve"
              LIMIT 1'
        );
        $stmt->execute(['id' => $battleId, 'user' => $userId]);
        return $stmt->fetch() ?: null;
    }

    public function findPokemon(string $battlePokemon): ?array
    {
        $parsed = $this->parseBattlePokemon($battlePokemon);
        if ($parsed === null) {
            return null;
        }

        $table = $parsed['table'];
        $stmt = $this->db->prepare(
            'SELECT *
               FROM ' . $table . '
              WHERE id = :id
              LIMIT 1'
        );
        $ok = $stmt->execute(['id' => $parsed['id']]);
        if (!$ok) {
            return null;
        }

        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }

        $row['battle_pokemon'] = $battlePokemon;
        return $row;
    }

    public function findUserBattlePokemonOptions(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, names, hp_my, hp_max, active, basenum, lvl
               FROM pok_user
              WHERE users = :user AND active = 1 AND hp_my > 0
              ORDER BY (hp_my > 0) DESC, startepoke DESC, id ASC'
        );
        $stmt->execute(['user' => $userId]);
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function findAvailableMoves(int $baseId, int $level): array
    {
        $stmt = $this->db->prepare(
            'SELECT ap.atac_id AS id, ap.atc_lvl,
                    apw.atac_name, apw.atac_power, apw.atac_accuracy, apw.atac_categori, apw.critic, apw.priorety
               FROM attac_poke ap
               LEFT JOIN attac_power apw ON apw.atac_id = ap.atac_id
              WHERE ap.poke_base_id = :base AND ap.atc_lvl <= :lvl
              ORDER BY ap.atc_lvl DESC, ap.atac_id DESC
              LIMIT 16'
        );
        $stmt->execute(['base' => $baseId, 'lvl' => $level]);
        $rows = $stmt->fetchAll();

        if (!is_array($rows) || $rows === []) {
            return [[
                'id' => 33,
                'atac_name' => 'Tackle',
                'atac_power' => 40,
                'atac_accuracy' => 100,
                'atac_categori' => 1,
                'critic' => 1,
                'priorety' => 0,
            ]];
        }

        $byId = [];
        foreach ($rows as $row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id <= 0 || isset($byId[$id])) {
                continue;
            }
            $byId[$id] = $row;
        }

        return array_slice(array_values($byId), 0, 4);
    }

    public function findSelectedMovesForPokemon(int $pokemonId): array
    {
        if ($pokemonId <= 0) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT a_id, a_pp_min, a_pp_max, b_id, b_pp_min, b_pp_max, c_id, c_pp_min, c_pp_max, d_id, d_pp_min, d_pp_max
               FROM attac_my_poke
              WHERE pok_id = :pokemon
              LIMIT 1'
        );
        $stmt->execute(['pokemon' => $pokemonId]);
        $row = $stmt->fetch();
        if (!$row) {
            return [];
        }

        $slots = [
            ['id' => (int) ($row['a_id'] ?? 0), 'pp_min' => (int) ($row['a_pp_min'] ?? 0), 'pp_max' => (int) ($row['a_pp_max'] ?? 0)],
            ['id' => (int) ($row['b_id'] ?? 0), 'pp_min' => (int) ($row['b_pp_min'] ?? 0), 'pp_max' => (int) ($row['b_pp_max'] ?? 0)],
            ['id' => (int) ($row['c_id'] ?? 0), 'pp_min' => (int) ($row['c_pp_min'] ?? 0), 'pp_max' => (int) ($row['c_pp_max'] ?? 0)],
            ['id' => (int) ($row['d_id'] ?? 0), 'pp_min' => (int) ($row['d_pp_min'] ?? 0), 'pp_max' => (int) ($row['d_pp_max'] ?? 0)],
        ];

        $ids = array_values(array_unique(array_filter(array_column($slots, 'id'))));
        if ($ids === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $movesStmt = $this->db->prepare(
            'SELECT atac_id AS id, atac_name, atac_power, atac_accuracy, atac_categori, critic, priorety
               FROM attac_power
              WHERE atac_id IN (' . $placeholders . ')'
        );
        $movesStmt->execute($ids);

        $byId = [];
        foreach ($movesStmt->fetchAll() as $move) {
            $byId[(int) $move['id']] = $move;
        }

        $moves = [];
        $seen = [];
        foreach ($slots as $slot) {
            $id = (int) $slot['id'];
            if ($id <= 0 || isset($seen[$id]) || !isset($byId[$id])) {
                continue;
            }
            $seen[$id] = true;
            $move = $byId[$id];
            $move['pp_min'] = $slot['pp_min'];
            $move['pp_max'] = $slot['pp_max'];
            $moves[] = $move;
        }

        return $moves;
    }

    public function updateBattleAction(int $battleId, int $playerAttack, int $enemyAttack): void
    {
        $stmt = $this->db->prepare(
            'UPDATE battles SET attac_1 = :attac_1, attac_2 = :attac_2 WHERE id = :id LIMIT 1'
        );
        $stmt->execute([
            'attac_1' => $playerAttack,
            'attac_2' => $enemyAttack,
            'id' => $battleId,
        ]);
    }

    public function incrementRoundAndResetActions(int $battleId): void
    {
        $stmt = $this->db->prepare(
            'UPDATE battles SET attac_1 = 0, attac_2 = 0, raund = raund + 1 WHERE id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $battleId]);
    }

    public function updatePokemonHp(string $battlePokemon, int $newHp): void
    {
        $parsed = $this->parseBattlePokemon($battlePokemon);
        if ($parsed === null) {
            return;
        }

        $stmt = $this->db->prepare(
            'UPDATE ' . $parsed['table'] . ' SET hp_my = :hp WHERE id = :id LIMIT 1'
        );
        $stmt->execute(['hp' => max(0, $newHp), 'id' => $parsed['id']]);
    }

    public function switchPlayerPokemon(int $battleId, int $userId, int $pokemonId): bool
    {
        $check = $this->db->prepare(
            'SELECT id FROM pok_user WHERE id = :id AND users = :user AND active = 1 AND hp_my > 0 LIMIT 1'
        );
        $check->execute(['id' => $pokemonId, 'user' => $userId]);
        if (!$check->fetch()) {
            return false;
        }

        $stmt = $this->db->prepare(
            'UPDATE battles SET poke_1 = :poke WHERE id = :battle AND user_1 = :user LIMIT 1'
        );
        $stmt->execute([
            'poke' => 'pvp_' . $pokemonId,
            'battle' => $battleId,
            'user' => $userId,
        ]);
        return true;
    }

    public function findLatestPvePokemonIdForUser(int $userId): int
    {
        $stmt = $this->db->prepare(
            'SELECT id
               FROM pok_pve
              WHERE users = :user
              ORDER BY id DESC
              LIMIT 1'
        );
        $stmt->execute(['user' => $userId]);
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    public function patchBattleEnemy(int $battleId, int $pokPveId): void
    {
        if ($battleId <= 0 || $pokPveId <= 0) {
            return;
        }
        $stmt = $this->db->prepare(
            'UPDATE battles
                SET user_2 = :user_2, poke_2 = :poke_2
              WHERE id = :id
              LIMIT 1'
        );
        $stmt->execute([
            'user_2' => $pokPveId,
            'poke_2' => 'pve_' . $pokPveId,
            'id' => $battleId,
        ]);
    }

    public function insertBattleLog(int $battleId, int $round, string $message): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO battle_log (battle_id, demage, raund) VALUES (:battle_id, :demage, :raund)'
        );
        $stmt->execute([
            'battle_id' => $battleId,
            'demage' => $message,
            'raund' => $round,
        ]);
    }

    public function getBattleLog(int $battleId, int $limit = 40): array
    {
        $stmt = $this->db->prepare(
            'SELECT raund, demage
               FROM battle_log
              WHERE battle_id = :battle_id
              ORDER BY id DESC
              LIMIT :lim'
        );
        $stmt->bindValue(':battle_id', $battleId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', max(1, min(200, $limit)), PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        return array_reverse(is_array($rows) ? $rows : []);
    }

    public function addCoins(int $userId, int $coins): void
    {
        if ($userId <= 0 || $coins <= 0) {
            return;
        }

        $coinItemId = 1;
        $existing = $this->db->prepare(
            'SELECT id FROM items_users WHERE user_id = :user AND item_id = :item LIMIT 1'
        );
        $existing->execute([
            'user' => $userId,
            'item' => $coinItemId,
        ]);
        $rowId = (int) ($existing->fetchColumn() ?: 0);

        if ($rowId > 0) {
            $stmt = $this->db->prepare(
                'UPDATE items_users SET count = count + :coins WHERE id = :id LIMIT 1'
            );
            $stmt->execute([
                'coins' => $coins,
                'id' => $rowId,
            ]);
            return;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO items_users (item_id, user_id, count, dattimer, timers)
             VALUES (:item, :user, :coins, :dattimer, :timers)'
        );
        $stmt->execute([
            'item' => $coinItemId,
            'user' => $userId,
            'coins' => $coins,
            'dattimer' => 'not',
            'timers' => 'not',
        ]);
    }

    public function addExperienceAndEffort(int $userId, int $pokemonId, int $exp, int $baseEv): array
    {
        $stmt = $this->db->prepare(
            'SELECT *
               FROM pok_user
              WHERE id = :pokemon AND users = :user AND active = 1
              LIMIT 1'
        );
        $stmt->execute([
            'pokemon' => $pokemonId,
            'user' => $userId,
        ]);
        $pokemon = $stmt->fetch();
        if (!$pokemon) {
            return ['exp' => 0, 'ev' => 0, 'levelUps' => 0, 'level' => 0];
        }

        $evGain = max(0, $baseEv);
        if ($evGain > 0 && $this->pokemonHasMachoBrace($pokemonId)) {
            $evGain *= 2;
        }

        $level = max(1, (int) ($pokemon['lvl'] ?? 1));
        $newExp = max(0, (int) ($pokemon['exp'] ?? 0) + max(0, $exp));
        $newEv = max(0, (int) ($pokemon['evcount'] ?? 0) + $evGain);
        $levelUps = 0;

        while ($level < 100 && $newExp >= $this->levelExp($level)) {
            $level++;
            $levelUps++;
        }

        $stats = $this->calculateStats($pokemon, $level);
        $this->db->prepare(
            'UPDATE pok_user
                SET exp = :exp, exp_b = :exp_b, evcount = :evcount, lvl = :lvl,
                    hp_my = :hp_my, hp_max = :hp_max, atk = :atk, def = :def,
                    satk = :satk, sdef = :sdef, speed = :speed
              WHERE id = :pokemon AND users = :user
              LIMIT 1'
        )->execute([
            'exp' => $newExp,
            'exp_b' => $this->levelExp($level + 1),
            'evcount' => $newEv,
            'lvl' => $level,
            'hp_my' => $stats['hp'],
            'hp_max' => $stats['hp'],
            'atk' => $stats['atk'],
            'def' => $stats['def'],
            'satk' => $stats['satk'],
            'sdef' => $stats['sdef'],
            'speed' => $stats['speed'],
            'pokemon' => $pokemonId,
            'user' => $userId,
        ]);

        return ['exp' => max(0, $exp), 'ev' => $evGain, 'levelUps' => $levelUps, 'level' => $level];
    }

    public function findBattleInventoryItem(int $userId, int $itemUserId): ?array
    {
        if ($userId <= 0 || $itemUserId <= 0) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT iu.id, iu.item_id, iu.count, i.name, i.tittle, i.category, i.battleuse
               FROM items_users iu
               INNER JOIN items i ON i.id = iu.item_id
              WHERE iu.id = :id AND iu.user_id = :user AND iu.count > 0 AND i.battleuse = 1
              LIMIT 1'
        );
        $stmt->execute([
            'id' => $itemUserId,
            'user' => $userId,
        ]);

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function decrementInventoryItemRow(int $userId, int $itemUserId): void
    {
        if ($userId <= 0 || $itemUserId <= 0) {
            return;
        }

        $this->db->prepare(
            'UPDATE items_users
                SET count = GREATEST(count - 1, 0)
              WHERE id = :id AND user_id = :user AND count > 0
              LIMIT 1'
        )->execute([
            'id' => $itemUserId,
            'user' => $userId,
        ]);

        $this->db->prepare(
            'DELETE FROM items_users WHERE id = :id AND user_id = :user AND count <= 0 LIMIT 1'
        )->execute([
            'id' => $itemUserId,
            'user' => $userId,
        ]);
    }

    public function countActivePokemon(int $userId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM pok_user WHERE users = :user AND active = 1'
        );
        $stmt->execute(['user' => $userId]);
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    public function clearBattleStatus(string $battlePokemon, int $statusNumber): int
    {
        $stmt = $this->db->prepare(
            'DELETE FROM bttle_status WHERE pokeid = :poke AND namber_st = :status'
        );
        $stmt->execute([
            'poke' => $battlePokemon,
            'status' => $statusNumber,
        ]);
        return $stmt->rowCount();
    }

    public function catchWildPokemon(int $userId, string $enemyBattlePokemon, int $active): ?int
    {
        $parsed = $this->parseBattlePokemon($enemyBattlePokemon);
        if ($parsed === null || $parsed['table'] !== 'pok_pve') {
            return null;
        }

        $stmt = $this->db->prepare('SELECT * FROM pok_pve WHERE id = :id AND poimka = 1 LIMIT 1');
        $stmt->execute(['id' => $parsed['id']]);
        $wild = $stmt->fetch();
        if (!$wild) {
            return null;
        }

        $sprz = (int) ($wild['sprz'] ?? 0);
        $startone = in_array($sprz, [2, 3], true) ? 1 : 0;
        $reproduction = in_array($sprz, [1, 2], true) ? 1 : 0;

        $insert = $this->db->prepare(
            'INSERT INTO pok_user
                (users, basenum, names, active, evcount, lvl, sex, har, hp_my, hp_max, exp, exp_b,
                 atk, def, satk, sdef, speed, hp_ev, atk_ev, def_ev, satk_ev, sdef_ev, speed_ev,
                 hp_iv, atk_iv, def_iv, satk_iv, sdef_iv, speed_iv, tips, startone, startepoke,
                 reproduction, happy, datemay, usersone, sprz, item)
             VALUES
                (:users, :basenum, :names, :active, :evcount, :lvl, :sex, :har, :hp_my, :hp_max, :exp, :exp_b,
                 :atk, :def, :satk, :sdef, :speed, :hp_ev, :atk_ev, :def_ev, :satk_ev, :sdef_ev, :speed_ev,
                 :hp_iv, :atk_iv, :def_iv, :satk_iv, :sdef_iv, :speed_iv, :tips, :startone, 0,
                 :reproduction, :happy, :datemay, :usersone, :sprz, 0)'
        );
        $insert->execute([
            'users' => $userId,
            'basenum' => (int) ($wild['basenum'] ?? 0),
            'names' => substr(strip_tags((string) ($wild['names'] ?? 'Pokemon')), 0, 80),
            'active' => $active > 0 ? 1 : 0,
            'evcount' => (int) ($wild['evcount'] ?? 0),
            'lvl' => (int) ($wild['lvl'] ?? 1),
            'sex' => (int) ($wild['sex'] ?? 1),
            'har' => (int) ($wild['har'] ?? 1),
            'hp_my' => max(1, (int) ($wild['hp_my'] ?? 1)),
            'hp_max' => max(1, (int) ($wild['hp_max'] ?? 1)),
            'exp' => (int) ($wild['exp'] ?? $this->levelExp(max(1, (int) ($wild['lvl'] ?? 1)) - 1)),
            'exp_b' => (int) ($wild['exp_b'] ?? $this->levelExp(max(1, (int) ($wild['lvl'] ?? 1)) + 1)),
            'atk' => (int) ($wild['atk'] ?? 1),
            'def' => (int) ($wild['def'] ?? 1),
            'satk' => (int) ($wild['satk'] ?? 1),
            'sdef' => (int) ($wild['sdef'] ?? 1),
            'speed' => (int) ($wild['speed'] ?? 1),
            'hp_ev' => (int) ($wild['hp_ev'] ?? 0),
            'atk_ev' => (int) ($wild['atk_ev'] ?? 0),
            'def_ev' => (int) ($wild['def_ev'] ?? 0),
            'satk_ev' => (int) ($wild['satk_ev'] ?? 0),
            'sdef_ev' => (int) ($wild['sdef_ev'] ?? 0),
            'speed_ev' => (int) ($wild['speed_ev'] ?? 0),
            'hp_iv' => (int) ($wild['hp_iv'] ?? 1),
            'atk_iv' => (int) ($wild['atk_iv'] ?? 1),
            'def_iv' => (int) ($wild['def_iv'] ?? 1),
            'satk_iv' => (int) ($wild['satk_iv'] ?? 1),
            'sdef_iv' => (int) ($wild['sdef_iv'] ?? 1),
            'speed_iv' => (int) ($wild['speed_iv'] ?? 1),
            'tips' => (string) ($wild['tips'] ?? 'normal'),
            'startone' => $startone,
            'reproduction' => $reproduction,
            'happy' => (float) ($wild['happy'] ?? random_int(1, 10)),
            'datemay' => date('Y-m-d H:i:s'),
            'usersone' => $userId,
            'sprz' => $sprz,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function finishBattle(int $battleId, int $userId, int $winner): void
    {
        $this->db->prepare(
            'UPDATE battles SET pobeda = :winner WHERE id = :id LIMIT 1'
        )->execute(['winner' => $winner, 'id' => $battleId]);

        $enemy = $this->db->prepare('SELECT poke_2 FROM battles WHERE id = :id LIMIT 1');
        $enemy->execute(['id' => $battleId]);
        $enemyBattlePokemon = (string) ($enemy->fetchColumn() ?: '');

        $this->db->prepare(
            'UPDATE users SET pve = 0, battleid = 0, atack_poke = :next_attack WHERE id = :id LIMIT 1'
        )->execute(['next_attack' => time() + 30, 'id' => $userId]);

        $this->db->prepare('DELETE FROM battles WHERE id = :id LIMIT 1')->execute(['id' => $battleId]);
        $this->db->prepare('DELETE FROM battle_log WHERE battle_id = :id')->execute(['id' => $battleId]);

        $parsed = $this->parseBattlePokemon($enemyBattlePokemon);
        if ($parsed !== null && $parsed['table'] === 'pok_pve') {
            $this->db->prepare('DELETE FROM pok_pve WHERE id = :id LIMIT 1')->execute(['id' => $parsed['id']]);
        }
    }

    private function parseBattlePokemon(string $value): ?array
    {
        $value = trim($value);

        if (preg_match('/^(pvp|pve|nps|user|npc)_(\d+)$/', $value, $m)) {
            $prefix = $m[1];
            $id = (int) $m[2];
            if ($id <= 0) {
                return null;
            }

            $table = match ($prefix) {
                'pvp', 'user' => 'pok_user',
                'pve' => 'pok_pve',
                'nps', 'npc' => 'pok_nps',
                default => '',
            };
            if ($table === '') {
                return null;
            }

            return ['table' => $table, 'id' => $id];
        }

        if (ctype_digit($value)) {
            $id = (int) $value;
            if ($id <= 0) {
                return null;
            }

            foreach (['pok_user', 'pok_pve', 'pok_nps'] as $table) {
                $stmt = $this->db->prepare('SELECT id FROM ' . $table . ' WHERE id = :id LIMIT 1');
                $stmt->execute(['id' => $id]);
                if ($stmt->fetchColumn() !== false) {
                    return ['table' => $table, 'id' => $id];
                }
            }
        }

        return null;
    }

    private function levelExp(int $level): int
    {
        return (int) round(60 * exp(2 + max(0, $level) / 10) - 50);
    }

    private function pokemonHasMachoBrace(int $pokemonId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT ip.id_items, i.name
               FROM items_poke ip
               LEFT JOIN items i ON i.id = ip.id_items
              WHERE ip.id_poke = :pokemon
              LIMIT 1'
        );
        $stmt->execute(['pokemon' => $pokemonId]);
        $item = $stmt->fetch();
        if (!$item) {
            return false;
        }

        $itemId = (int) ($item['id_items'] ?? 0);
        $rawName = (string) ($item['name'] ?? '');
        $name = function_exists('mb_strtolower') ? mb_strtolower($rawName) : strtolower($rawName);
        return $itemId === 2 || str_contains($name, 'скоб') || str_contains($name, 'macho') || str_contains($name, 'brace');
    }

    private function calculateStats(array $pokemon, int $level): array
    {
        $baseStmt = $this->db->prepare('SELECT hp, atk, def, satk, sdef, speed FROM poke_base WHERE id = :base LIMIT 1');
        $baseStmt->execute(['base' => (int) ($pokemon['basenum'] ?? 0)]);
        $base = $baseStmt->fetch() ?: ['hp' => 25, 'atk' => 15, 'def' => 13, 'satk' => 15, 'sdef' => 13, 'speed' => 10];

        $harStmt = $this->db->prepare('SELECT atk, def, satk, sdef, speed FROM har WHERE id_har = :har LIMIT 1');
        $harStmt->execute(['har' => (int) ($pokemon['har'] ?? 1)]);
        $har = $harStmt->fetch() ?: ['atk' => 1, 'def' => 1, 'satk' => 1, 'sdef' => 1, 'speed' => 1];

        return [
            'hp' => (int) round((((int) ($pokemon['hp_iv'] ?? 1) + ((int) $base['hp'] * 2) + ((int) ($pokemon['hp_ev'] ?? 0) / 4) + 100) * ($level / 100)) + 10),
            'atk' => (int) round(((((int) ($pokemon['atk_iv'] ?? 1) + ((int) $base['atk'] * 2) + ((int) ($pokemon['atk_ev'] ?? 0) / 4)) * ($level / 100)) + 5) * (float) $har['atk']),
            'def' => (int) round(((((int) ($pokemon['def_iv'] ?? 1) + ((int) $base['def'] * 2) + ((int) ($pokemon['def_ev'] ?? 0) / 4)) * ($level / 100)) + 5) * (float) $har['def']),
            'satk' => (int) round(((((int) ($pokemon['satk_iv'] ?? 1) + ((int) $base['satk'] * 2) + ((int) ($pokemon['satk_ev'] ?? 0) / 4)) * ($level / 100)) + 5) * (float) $har['satk']),
            'sdef' => (int) round(((((int) ($pokemon['sdef_iv'] ?? 1) + ((int) $base['sdef'] * 2) + ((int) ($pokemon['sdef_ev'] ?? 0) / 4)) * ($level / 100)) + 5) * (float) $har['sdef']),
            'speed' => (int) round(((((int) ($pokemon['speed_iv'] ?? 1) + ((int) $base['speed'] * 2) + ((int) ($pokemon['speed_ev'] ?? 0) / 4)) * ($level / 100)) + 5) * (float) $har['speed']),
        ];
    }
}
__halt_compiler();
<?php
declare(strict_types=1);

namespace Pokemon8\Repository;

use PDO;

final class BattleRepository
{
    public function __construct(private PDO $db)
    {
    }

    public function findActivePveBattleIdForUser(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT battleid FROM users WHERE id = :id AND pve = 1 LIMIT 1');
        $stmt->execute(['id' => $userId]);
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    public function findPveBattleForUser(int $userId, int $battleId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, user_1, user_2, poke_1, poke_2, attac_1, attac_2, raund, hod_user_id, pobeda
               FROM battles
              WHERE id = :id AND user_1 = :user AND batl_tip = "pve"
              LIMIT 1'
        );
        $stmt->execute(['id' => $battleId, 'user' => $userId]);
        return $stmt->fetch() ?: null;
    }

    public function findPokemon(string $battlePokemon): ?array
    {
        $parsed = $this->parseBattlePokemon($battlePokemon);
        if ($parsed === null) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT id, users, names, basenum, lvl, hp_my, hp_max, atk, def, satk, sdef, speed
               FROM ' . $parsed['table'] . '
              WHERE id = :id
              LIMIT 1'
        );
        $stmt->execute(['id' => $parsed['id']]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }

        $row['battle_pokemon'] = $battlePokemon;
        return $row;
    }

    public function findUserBattlePokemonOptions(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, names, hp_my, hp_max, active, basenum, lvl
               FROM pok_user
              WHERE users = :user AND active = 1
              ORDER BY (hp_my > 0) DESC, startepoke DESC, id ASC'
        );
        $stmt->execute(['user' => $userId]);
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function findAvailableMoves(int $baseId, int $level): array
    {
        $stmt = $this->db->prepare(
            'SELECT ap.atac_id AS id, ap.atc_lvl,
                    apw.atac_name, apw.atac_power, apw.atac_accuracy, apw.atac_categori, apw.critic, apw.priorety
               FROM attac_poke ap
               LEFT JOIN attac_power apw ON apw.atac_id = ap.atac_id
              WHERE ap.poke_base_id = :base AND ap.atc_lvl <= :lvl
              ORDER BY ap.atc_lvl DESC, ap.atac_id DESC
              LIMIT 16'
        );
        $stmt->execute(['base' => $baseId, 'lvl' => $level]);
        $rows = $stmt->fetchAll();

        if (!is_array($rows) || $rows === []) {
            return [[
                'id' => 33,
                'atac_name' => 'Tackle',
                'atac_power' => 40,
                'atac_accuracy' => 100,
                'atac_categori' => 1,
                'critic' => 1,
                'priorety' => 0,
            ]];
        }

        $byId = [];
        foreach ($rows as $row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id <= 0 || isset($byId[$id])) {
                continue;
            }
            $byId[$id] = $row;
        }

        return array_slice(array_values($byId), 0, 4);
    }

    public function updateBattleAction(int $battleId, int $playerAttack, int $enemyAttack): void
    {
        $stmt = $this->db->prepare(
            'UPDATE battles SET attac_1 = :attac_1, attac_2 = :attac_2 WHERE id = :id LIMIT 1'
        );
        $stmt->execute([
            'attac_1' => $playerAttack,
            'attac_2' => $enemyAttack,
            'id' => $battleId,
        ]);
    }

    public function incrementRoundAndResetActions(int $battleId): void
    {
        $stmt = $this->db->prepare(
            'UPDATE battles SET attac_1 = 0, attac_2 = 0, raund = raund + 1 WHERE id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $battleId]);
    }

    public function updatePokemonHp(string $battlePokemon, int $newHp): void
    {
        $parsed = $this->parseBattlePokemon($battlePokemon);
        if ($parsed === null) {
            return;
        }

        $stmt = $this->db->prepare(
            'UPDATE ' . $parsed['table'] . ' SET hp_my = :hp WHERE id = :id LIMIT 1'
        );
        $stmt->execute(['hp' => max(0, $newHp), 'id' => $parsed['id']]);
    }

    public function switchPlayerPokemon(int $battleId, int $userId, int $pokemonId): bool
    {
        $check = $this->db->prepare(
            'SELECT id FROM pok_user WHERE id = :id AND users = :user AND active = 1 AND hp_my > 0 LIMIT 1'
        );
        $check->execute(['id' => $pokemonId, 'user' => $userId]);
        if (!$check->fetch()) {
            return false;
        }

        $stmt = $this->db->prepare(
            'UPDATE battles SET poke_1 = :poke WHERE id = :battle AND user_1 = :user LIMIT 1'
        );
        $stmt->execute([
            'poke' => 'pvp_' . $pokemonId,
            'battle' => $battleId,
            'user' => $userId,
        ]);
        return true;
    }

    public function insertBattleLog(int $battleId, int $round, string $message): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO battle_log (battle_id, demage, raund) VALUES (:battle_id, :demage, :raund)'
        );
        $stmt->execute([
            'battle_id' => $battleId,
            'demage' => $message,
            'raund' => $round,
        ]);
    }

    public function finishBattle(int $battleId, int $userId, int $winner): void
    {
        $this->db->prepare(
            'UPDATE battles SET pobeda = :winner WHERE id = :id LIMIT 1'
        )->execute(['winner' => $winner, 'id' => $battleId]);

        $enemy = $this->db->prepare('SELECT poke_2 FROM battles WHERE id = :id LIMIT 1');
        $enemy->execute(['id' => $battleId]);
        $enemyBattlePokemon = (string) ($enemy->fetchColumn() ?: '');

        $this->db->prepare(
            'UPDATE users SET pve = 0, battleid = 0, atack_poke = :next_attack WHERE id = :id LIMIT 1'
        )->execute(['next_attack' => time() + 30, 'id' => $userId]);

        $this->db->prepare('DELETE FROM battles WHERE id = :id LIMIT 1')->execute(['id' => $battleId]);
        $this->db->prepare('DELETE FROM battle_log WHERE battle_id = :id')->execute(['id' => $battleId]);

        $parsed = $this->parseBattlePokemon($enemyBattlePokemon);
        if ($parsed !== null && $parsed['table'] === 'pok_pve') {
            $this->db->prepare('DELETE FROM pok_pve WHERE id = :id LIMIT 1')->execute(['id' => $parsed['id']]);
        }
    }

    private function parseBattlePokemon(string $value): ?array
    {
        $value = trim($value);

        if (preg_match('/^(pvp|pve|nps|user|npc)_(\d+)$/', $value, $m)) {
            $prefix = $m[1];
            $id = (int) $m[2];
            if ($id <= 0) {
                return null;
            }

            $table = match ($prefix) {
                'pvp', 'user' => 'pok_user',
                'pve' => 'pok_pve',
                'nps', 'npc' => 'pok_nps',
                default => '',
            };
            if ($table === '') {
                return null;
            }

            return ['table' => $table, 'id' => $id];
        }

        if (ctype_digit($value)) {
            $id = (int) $value;
            if ($id <= 0) {
                return null;
            }

            foreach (['pok_user', 'pok_pve', 'pok_nps'] as $table) {
                $stmt = $this->db->prepare('SELECT id FROM ' . $table . ' WHERE id = :id LIMIT 1');
                $stmt->execute(['id' => $id]);
                if ($stmt->fetchColumn() !== false) {
                    return ['table' => $table, 'id' => $id];
                }
            }
        }

        return null;
    }
}
<?php
declare(strict_types=1);

namespace Pokemon8\Repository;

use PDO;

final class BattleRepository
{
    public function __construct(private PDO $db)
    {
    }

    public function findActivePveBattleIdForUser(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT battleid FROM users WHERE id = :id AND pve = 1 LIMIT 1');
        $stmt->execute(['id' => $userId]);
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    public function findPveBattleForUser(int $userId, int $battleId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, user_1, user_2, poke_1, poke_2, attac_1, attac_2, raund, hod_user_id, pobeda
               FROM battles
              WHERE id = :id AND user_1 = :user AND batl_tip = "pve"
              LIMIT 1'
        );
        $stmt->execute([
            'id' => $battleId,
            'user' => $userId,
        ]);

        return $stmt->fetch() ?: null;
    }

    public function findPokemon(string $battlePokemon): ?array
    {
        $parsed = $this->parseBattlePokemon($battlePokemon);
        if ($parsed === null) {
            return null;
        }

        $table = $parsed['table'];
        $stmt = $this->db->prepare(
            'SELECT id, users, names, basenum, lvl, hp_my, hp_max, atk, def, satk, sdef, speed
               FROM ' . $table . '
              WHERE id = :id
              LIMIT 1'
        );
        $stmt->execute(['id' => $parsed['id']]);

        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }

        $row['battle_pokemon'] = $battlePokemon;
        $row['table'] = $table;
        return $row;
    }

    public function findUserBattlePokemonOptions(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, names, hp_my, hp_max, active, basenum, lvl
               FROM pok_user
              WHERE users = :user AND active = 1
              ORDER BY (hp_my > 0) DESC, startepoke DESC, id ASC'
        );
        $stmt->execute(['user' => $userId]);

        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function findAvailableMoves(int $baseId, int $level): array
    {
        $stmt = $this->db->prepare(
            'SELECT ap.atac_id AS id, ap.atc_lvl,
                    apw.atac_name, apw.atac_power, apw.atac_accuracy, apw.atac_categori, apw.critic, apw.priorety
               FROM attac_poke ap
               LEFT JOIN attac_power apw ON apw.atac_id = ap.atac_id
              WHERE ap.poke_base_id = :base AND ap.atc_lvl <= :lvl
              ORDER BY ap.atc_lvl DESC, ap.atac_id DESC
              LIMIT 16'
        );
        $stmt->execute([
            'base' => $baseId,
            'lvl' => $level,
        ]);
        $rows = $stmt->fetchAll();
        if (!is_array($rows) || $rows === []) {
            return [[
                'id' => 33,
                'atac_name' => 'Tackle',
                'atac_power' => 40,
                'atac_accuracy' => 100,
                'atac_categori' => 1,
                'critic' => 1,
                'priorety' => 0,
            ]];
        }

        $byId = [];
        foreach ($rows as $row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id <= 0 || isset($byId[$id])) {
                continue;
            }
            $byId[$id] = $row;
        }

        return array_slice(array_values($byId), 0, 4);
    }

    public function updateBattleAction(int $battleId, int $playerAttack, int $enemyAttack): void
    {
        $stmt = $this->db->prepare(
            'UPDATE battles
                SET attac_1 = :attac_1, attac_2 = :attac_2
              WHERE id = :id
              LIMIT 1'
        );
        $stmt->execute([
            'attac_1' => $playerAttack,
            'attac_2' => $enemyAttack,
            'id' => $battleId,
        ]);
    }

    public function incrementRoundAndResetActions(int $battleId): void
    {
        $stmt = $this->db->prepare(
            'UPDATE battles
                SET attac_1 = 0, attac_2 = 0, raund = raund + 1
              WHERE id = :id
              LIMIT 1'
        );
        $stmt->execute(['id' => $battleId]);
    }

    public function updatePokemonHp(string $battlePokemon, int $newHp): void
    {
        $parsed = $this->parseBattlePokemon($battlePokemon);
        if ($parsed === null) {
            return;
        }

        $stmt = $this->db->prepare(
            'UPDATE ' . $parsed['table'] . ' SET hp_my = :hp WHERE id = :id LIMIT 1'
        );
        $stmt->execute([
            'hp' => max(0, $newHp),
            'id' => $parsed['id'],
        ]);
    }

    public function switchPlayerPokemon(int $battleId, int $userId, int $pokemonId): bool
    {
        $check = $this->db->prepare(
            'SELECT id FROM pok_user WHERE id = :id AND users = :user AND active = 1 AND hp_my > 0 LIMIT 1'
        );
        $check->execute([
            'id' => $pokemonId,
            'user' => $userId,
        ]);
        if (!$check->fetch()) {
            return false;
        }

        $stmt = $this->db->prepare(
            'UPDATE battles SET poke_1 = :poke WHERE id = :battle AND user_1 = :user LIMIT 1'
        );
        $stmt->execute([
            'poke' => 'pvp_' . $pokemonId,
            'battle' => $battleId,
            'user' => $userId,
        ]);

        return true;
    }

    public function insertBattleLog(int $battleId, int $round, string $message): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO battle_log (battle_id, demage, raund) VALUES (:battle_id, :demage, :raund)'
        );
        $stmt->execute([
            'battle_id' => $battleId,
            'demage' => $message,
            'raund' => $round,
        ]);
    }

    public function finishBattle(int $battleId, int $userId, int $winner): void
    {
        $stmt = $this->db->prepare(
            'UPDATE battles SET pobeda = :winner WHERE id = :id LIMIT 1'
        );
        $stmt->execute([
            'winner' => $winner,
            'id' => $battleId,
        ]);

        $enemy = $this->db->prepare('SELECT poke_2 FROM battles WHERE id = :id LIMIT 1');
        $enemy->execute(['id' => $battleId]);
        $enemyBattlePokemon = (string) ($enemy->fetchColumn() ?: '');

        $this->db->prepare(
            'UPDATE users SET pve = 0, battleid = 0, atack_poke = :next_attack WHERE id = :id LIMIT 1'
        )->execute([
            'next_attack' => time() + 30,
            'id' => $userId,
        ]);

        $this->db->prepare('DELETE FROM battles WHERE id = :id LIMIT 1')->execute(['id' => $battleId]);
        $this->db->prepare('DELETE FROM battle_log WHERE battle_id = :id')->execute(['id' => $battleId]);

        $parsed = $this->parseBattlePokemon($enemyBattlePokemon);
        if ($parsed !== null && $parsed['table'] === 'pok_pve') {
            $this->db->prepare('DELETE FROM pok_pve WHERE id = :id LIMIT 1')->execute(['id' => $parsed['id']]);
        }
    }

    private function parseBattlePokemon(string $value): ?array
    {
        $value = trim($value);

        if (preg_match('/^(pvp|pve|nps|user|npc)_(\d+)$/', $value, $m)) {
            $prefix = $m[1];
            $id = (int) $m[2];
            if ($id <= 0) {
                return null;
            }

            $table = match ($prefix) {
                'pvp', 'user' => 'pok_user',
                'pve' => 'pok_pve',
                'nps', 'npc' => 'pok_nps',
                default => '',
            };

            if ($table === '') {
                return null;
            }

            return ['table' => $table, 'id' => $id];
        }

        if (ctype_digit($value)) {
            $id = (int) $value;
            if ($id <= 0) {
                return null;
            }

            foreach (['pok_user', 'pok_pve', 'pok_nps'] as $table) {
                $stmt = $this->db->prepare('SELECT id FROM ' . $table . ' WHERE id = :id LIMIT 1');
                $stmt->execute(['id' => $id]);
                if ($stmt->fetchColumn() !== false) {
                    return ['table' => $table, 'id' => $id];
                }
            }
        }

        return null;
    }
}
<?php
declare(strict_types=1);

namespace Pokemon8\Repository;

use PDO;

final class BattleRepository
{
    public function __construct(private PDO $db)
    {
    }

    public function findActivePveBattleIdForUser(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT battleid FROM users WHERE id = :id AND pve = 1 LIMIT 1');
        $stmt->execute(['id' => $userId]);
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    public function findPveBattleForUser(int $userId, int $battleId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, user_1, user_2, poke_1, poke_2, attac_1, attac_2, raund, hod_user_id, pobeda
               FROM battles
              WHERE id = :id AND user_1 = :user AND batl_tip = "pve"
              LIMIT 1'
        );
        $stmt->execute([
            'id' => $battleId,
            'user' => $userId,
        ]);

        return $stmt->fetch() ?: null;
    }

    public function findPokemon(string $battlePokemon): ?array
    {
        $parsed = $this->parseBattlePokemon($battlePokemon);
        if ($parsed === null) {
            return null;
        }

        $table = $parsed['table'];
        $stmt = $this->db->prepare(
            'SELECT id, users, names, basenum, lvl, hp_my, hp_max, atk, def, satk, sdef, speed
               FROM ' . $table . '
              WHERE id = :id
              LIMIT 1'
        );
        $stmt->execute(['id' => $parsed['id']]);

        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }

        $row['battle_pokemon'] = $battlePokemon;
        $row['table'] = $table;
        return $row;
    }

    public function findUserBattlePokemonOptions(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, names, hp_my, hp_max, active, basenum, lvl
               FROM pok_user
              WHERE users = :user AND active = 1
              ORDER BY (hp_my > 0) DESC, startepoke DESC, id ASC'
        );
        $stmt->execute(['user' => $userId]);

        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function findAvailableMoves(int $baseId, int $level): array
    {
        $stmt = $this->db->prepare(
            'SELECT ap.atac_id AS id, ap.atc_lvl,
                    apw.atac_name, apw.atac_power, apw.atac_accuracy, apw.atac_categori, apw.critic, apw.priorety
               FROM attac_poke ap
               LEFT JOIN attac_power apw ON apw.atac_id = ap.atac_id
              WHERE ap.poke_base_id = :base AND ap.atc_lvl <= :lvl
              ORDER BY ap.atc_lvl DESC, ap.atac_id DESC
              LIMIT 16'
        );
        $stmt->execute([
            'base' => $baseId,
            'lvl' => $level,
        ]);
        $rows = $stmt->fetchAll();
        if (!is_array($rows) || $rows === []) {
            return [[
                'id' => 33,
                'atac_name' => 'Tackle',
                'atac_power' => 40,
                'atac_accuracy' => 100,
                'atac_categori' => 1,
                'critic' => 1,
                'priorety' => 0,
            ]];
        }

        $byId = [];
        foreach ($rows as $row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id <= 0 || isset($byId[$id])) {
                continue;
            }
            $byId[$id] = $row;
        }

        return array_slice(array_values($byId), 0, 4);
    }

    public function updateBattleAction(int $battleId, int $playerAttack, int $enemyAttack): void
    {
        $stmt = $this->db->prepare(
            'UPDATE battles
                SET attac_1 = :attac_1, attac_2 = :attac_2
              WHERE id = :id
              LIMIT 1'
        );
        $stmt->execute([
            'attac_1' => $playerAttack,
            'attac_2' => $enemyAttack,
            'id' => $battleId,
        ]);
    }

    public function incrementRoundAndResetActions(int $battleId): void
    {
        $stmt = $this->db->prepare(
            'UPDATE battles
                SET attac_1 = 0, attac_2 = 0, raund = raund + 1
              WHERE id = :id
              LIMIT 1'
        );
        $stmt->execute(['id' => $battleId]);
    }

    public function updatePokemonHp(string $battlePokemon, int $newHp): void
    {
        $parsed = $this->parseBattlePokemon($battlePokemon);
        if ($parsed === null) {
            return;
        }

        $stmt = $this->db->prepare(
            'UPDATE ' . $parsed['table'] . ' SET hp_my = :hp WHERE id = :id LIMIT 1'
        );
        $stmt->execute([
            'hp' => max(0, $newHp),
            'id' => $parsed['id'],
        ]);
    }

    public function switchPlayerPokemon(int $battleId, int $userId, int $pokemonId): bool
    {
        $check = $this->db->prepare(
            'SELECT id FROM pok_user WHERE id = :id AND users = :user AND active = 1 AND hp_my > 0 LIMIT 1'
        );
        $check->execute([
            'id' => $pokemonId,
            'user' => $userId,
        ]);
        if (!$check->fetch()) {
            return false;
        }

        $stmt = $this->db->prepare(
            'UPDATE battles SET poke_1 = :poke WHERE id = :battle AND user_1 = :user LIMIT 1'
        );
        $stmt->execute([
            'poke' => 'pvp_' . $pokemonId,
            'battle' => $battleId,
            'user' => $userId,
        ]);

        return true;
    }

    public function insertBattleLog(int $battleId, int $round, string $message): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO battle_log (battle_id, demage, raund) VALUES (:battle_id, :demage, :raund)'
        );
        $stmt->execute([
            'battle_id' => $battleId,
            'demage' => $message,
            'raund' => $round,
        ]);
    }

    public function finishBattle(int $battleId, int $userId, int $winner): void
    {
        $stmt = $this->db->prepare(
            'UPDATE battles SET pobeda = :winner WHERE id = :id LIMIT 1'
        );
        $stmt->execute([
            'winner' => $winner,
            'id' => $battleId,
        ]);

        $enemy = $this->db->prepare('SELECT poke_2 FROM battles WHERE id = :id LIMIT 1');
        $enemy->execute(['id' => $battleId]);
        $enemyBattlePokemon = (string) ($enemy->fetchColumn() ?: '');

        $this->db->prepare(
            'UPDATE users SET pve = 0, battleid = 0, atack_poke = :next_attack WHERE id = :id LIMIT 1'
        )->execute([
            'next_attack' => time() + 30,
            'id' => $userId,
        ]);

        $this->db->prepare('DELETE FROM battles WHERE id = :id LIMIT 1')->execute(['id' => $battleId]);
        $this->db->prepare('DELETE FROM battle_log WHERE battle_id = :id')->execute(['id' => $battleId]);

        $parsed = $this->parseBattlePokemon($enemyBattlePokemon);
        if ($parsed !== null && $parsed['table'] === 'pok_pve') {
            $this->db->prepare('DELETE FROM pok_pve WHERE id = :id LIMIT 1')->execute(['id' => $parsed['id']]);
        }
    }

    private function parseBattlePokemon(string $value): ?array
    {
        $value = trim($value);

        if (preg_match('/^(pvp|pve|nps|user|npc)_(\d+)$/', $value, $m)) {
            $prefix = $m[1];
            $id = (int) $m[2];
            if ($id <= 0) {
                return null;
            }

            $table = match ($prefix) {
                'pvp', 'user' => 'pok_user',
                'pve' => 'pok_pve',
                'nps', 'npc' => 'pok_nps',
                default => '',
            };

            if ($table === '') {
                return null;
            }

            return ['table' => $table, 'id' => $id];
        }

        if (ctype_digit($value)) {
            $id = (int) $value;
            if ($id <= 0) {
                return null;
            }

            foreach (['pok_user', 'pok_pve', 'pok_nps'] as $table) {
                $stmt = $this->db->prepare('SELECT id FROM ' . $table . ' WHERE id = :id LIMIT 1');
                $stmt->execute(['id' => $id]);
                if ($stmt->fetchColumn() !== false) {
                    return ['table' => $table, 'id' => $id];
                }
            }
        }

        return null;
    }
}
<?php
declare(strict_types=1);

namespace App\Repository;

use PDO;
use RuntimeException;

/**
 * BattleRepository
 *
 * ВАЖНО:
 * - Сохраняет legacy-таблицы: battles, pok_user, pok_pve, users.
 * - Нормализует типы на выходе.
 * - SQL-поля могут отличаться в твоей БД. Если имена отличаются — править только здесь.
 */
final class BattleRepository
{
    public function __construct(private PDO $pdo) {}

    public function getActivePveBattleForUser(int $userId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM battles WHERE user_id = :user_id AND type = :type AND status = :status ORDER BY id DESC LIMIT 1'
        );
        $stmt->execute([
            ':user_id' => $userId,
            ':type' => 'pve',
            ':status' => 'active',
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->normalizeBattle($row) : null;
    }

    public function getBattleById(int $battleId, int $userId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM battles WHERE id = :id AND user_id = :user_id LIMIT 1'
        );
        $stmt->execute([
            ':id' => $battleId,
            ':user_id' => $userId,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->normalizeBattle($row) : null;
    }

    public function getPlayerPokemon(int $pokUserId, int $userId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM pok_user WHERE id = :id AND user_id = :user_id LIMIT 1'
        );
        $stmt->execute([
            ':id' => $pokUserId,
            ':user_id' => $userId,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->normalizePokemon($row) : null;
    }

    public function getWildPokemon(int $pokPveId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM pok_pve WHERE id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $pokPveId]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->normalizePokemon($row) : null;
    }

    public function updatePlayerPokemonHp(int $pokUserId, int $userId, int $hp): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE pok_user SET hp = :hp WHERE id = :id AND user_id = :user_id LIMIT 1'
        );
        $stmt->execute([
            ':hp' => max(0, $hp),
            ':id' => $pokUserId,
            ':user_id' => $userId,
        ]);
    }

    public function updateWildPokemonHp(int $pokPveId, int $hp): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE pok_pve SET hp = :hp WHERE id = :id LIMIT 1'
        );
        $stmt->execute([
            ':hp' => max(0, $hp),
            ':id' => $pokPveId,
        ]);
    }

    public function updateBattleState(int $battleId, array $state): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE battles SET state_json = :state_json, updated_at = NOW() WHERE id = :id LIMIT 1'
        );
        $stmt->execute([
            ':state_json' => json_encode($state, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ':id' => $battleId,
        ]);
    }

    public function appendRoundLog(int $battleId, string $message, array $context = []): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO battle_logs (battle_id, message, context_json, created_at)
             VALUES (:battle_id, :message, :context_json, NOW())'
        );
        $stmt->execute([
            ':battle_id' => $battleId,
            ':message' => $message,
            ':context_json' => json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }

    public function getBattleLog(int $battleId, int $limit = 30): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT message, context_json, created_at FROM battle_logs
             WHERE battle_id = :battle_id ORDER BY id DESC LIMIT :limit'
        );
        $stmt->bindValue(':battle_id', $battleId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', max(1, min(100, $limit)), PDO::PARAM_INT);
        $stmt->execute();

        return array_reverse($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
    }

    public function finishBattle(int $battleId, int $userId, string $result): void
    {
        $this->pdo->beginTransaction();

        try {
            $stmt = $this->pdo->prepare(
                'UPDATE battles SET status = :status, result = :result, ended_at = NOW(), updated_at = NOW()
                 WHERE id = :id AND user_id = :user_id LIMIT 1'
            );
            $stmt->execute([
                ':status' => 'finished',
                ':result' => $result,
                ':id' => $battleId,
                ':user_id' => $userId,
            ]);

            $stmt = $this->pdo->prepare(
                'UPDATE users SET pve = 0, battleid = 0 WHERE id = :user_id LIMIT 1'
            );
            $stmt->execute([':user_id' => $userId]);

            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function cleanupPveState(int $userId): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE users SET pve = 0, battleid = 0 WHERE id = :user_id LIMIT 1'
        );
        $stmt->execute([':user_id' => $userId]);
    }

    private function normalizeBattle(array $row): array
    {
        $state = [];
        if (!empty($row['state_json'])) {
            $decoded = json_decode((string)$row['state_json'], true);
            $state = is_array($decoded) ? $decoded : [];
        }

        return [
            'id' => (int)($row['id'] ?? 0),
            'user_id' => (int)($row['user_id'] ?? 0),
            'player_pokemon_id' => (int)($row['player_pokemon_id'] ?? $row['pok_user_id'] ?? 0),
            'wild_pokemon_id' => (int)($row['wild_pokemon_id'] ?? $row['pok_pve_id'] ?? 0),
            'status' => (string)($row['status'] ?? 'active'),
            'result' => isset($row['result']) ? (string)$row['result'] : null,
            'state' => $state,
        ];
    }

    private function normalizePokemon(array $row): array
    {
        return [
            'id' => (int)($row['id'] ?? 0),
            'name' => (string)($row['name'] ?? $row['nickname'] ?? $row['pok_name'] ?? 'Pokemon'),
            'level' => (int)($row['level'] ?? $row['lvl'] ?? 1),
            'hp' => max(0, (int)($row['hp'] ?? 0)),
            'max_hp' => max(1, (int)($row['max_hp'] ?? $row['hp_max'] ?? $row['hp'] ?? 1)),
            'attack' => max(1, (int)($row['attack'] ?? $row['atk'] ?? 10)),
            'defense' => max(1, (int)($row['defense'] ?? $row['def'] ?? 10)),
            'speed' => max(1, (int)($row['speed'] ?? $row['spd'] ?? 10)),
            'status' => (string)($row['status'] ?? ''),
            'raw' => $row,
        ];
    }
}
