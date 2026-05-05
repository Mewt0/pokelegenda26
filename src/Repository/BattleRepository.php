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
        // Active fight: user.pve = 1. Finished-but-not-acked fight: pve = 0,
        // battleid still points to battles.pobeda != 0 so the client can show final log.
        $stmt = $this->db->prepare('SELECT battleid, pve FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch();
        if (!$user) {
            return 0;
        }

        $battleId = (int) ($user['battleid'] ?? 0);
        if ($battleId <= 0) {
            return 0;
        }
        if ((int) ($user['pve'] ?? 0) === 1) {
            return $battleId;
        }

        $battle = $this->db->prepare(
            'SELECT id FROM battles WHERE id = :id AND user_1 = :user AND batl_tip = "pve" AND pobeda <> 0 LIMIT 1'
        );
        $battle->execute(['id' => $battleId, 'user' => $userId]);
        return $battle->fetchColumn() !== false ? $battleId : 0;
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

    public function findActivePvpBattleIdForUser(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT battleid, pvp FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch();
        if (!$user) {
            return 0;
        }

        $battleId = (int) ($user['battleid'] ?? 0);
        if ($battleId <= 0) {
            return 0;
        }

        if ((int) ($user['pvp'] ?? 0) === 1) {
            return $battleId;
        }

        $battle = $this->db->prepare(
            'SELECT id FROM battles WHERE id = :id AND (user_1 = :user_1 OR user_2 = :user_2) AND batl_tip = "pvp" AND pobeda <> 0 LIMIT 1'
        );
        $battle->execute(['id' => $battleId, 'user_1' => $userId, 'user_2' => $userId]);
        return $battle->fetchColumn() !== false ? $battleId : 0;
    }

    public function findPvpBattleForUser(int $userId, int $battleId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, user_1, user_2, poke_1, poke_2, attac_1, attac_2, raund, hod_user_id, pobeda
               FROM battles
              WHERE id = :id AND (user_1 = :user_1 OR user_2 = :user_2) AND batl_tip = "pvp"
              LIMIT 1'
        );
        $stmt->execute(['id' => $battleId, 'user_1' => $userId, 'user_2' => $userId]);
        return $stmt->fetch() ?: null;
    }

    public function findUserLoginById(int $userId): string
    {
        $stmt = $this->db->prepare('SELECT login FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $userId]);
        return (string) ($stmt->fetchColumn() ?: ('Игрок #' . $userId));
    }

    public function findFirstBattlePokemonForUser(int $userId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id
               FROM pok_user
              WHERE users = :user AND active = 1 AND hp_my > 0
              ORDER BY startepoke DESC, id ASC
              LIMIT 1'
        );
        $stmt->execute(['user' => $userId]);
        $pokemonId = (int) ($stmt->fetchColumn() ?: 0);
        return $pokemonId > 0 ? $this->findPokemon('pvp_' . $pokemonId) : null;
    }

    public function findChosenBattlePokemonForUser(int $userId, int $pokemonId): ?array
    {
        if ($userId <= 0 || $pokemonId <= 0) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT id
               FROM pok_user
              WHERE id = :pokemon AND users = :user AND active = 1 AND hp_my > 0
              LIMIT 1'
        );
        $stmt->execute(['pokemon' => $pokemonId, 'user' => $userId]);
        return $stmt->fetchColumn() !== false ? $this->findPokemon('pvp_' . $pokemonId) : null;
    }

    public function pvpAttackPermission(int $attackerId, int $targetId): array
    {
        if ($attackerId <= 0 || $targetId <= 0 || $attackerId === $targetId) {
            return ['allowed' => false, 'message' => 'Нельзя вызвать этого игрока.'];
        }

        $users = $this->karmaUsers([$attackerId, $targetId]);
        if (!isset($users[$attackerId], $users[$targetId])) {
            return ['allowed' => false, 'message' => 'Игрок не найден.'];
        }

        $attacker = $users[$attackerId];
        $target = $users[$targetId];
        $attackerState = $this->karmaState($attacker);
        $targetState = $this->karmaState($target);
        $location = $this->karmaBattleLocation((int) ($attacker['buildmy'] ?? 0), (int) ($target['buildmy'] ?? 0));
        $base = [
            'attacker' => $this->karmaSummary($attacker),
            'target' => $this->karmaSummary($target),
            'location' => $location,
        ];

        if (!$location['sameLocation']) {
            return $base + ['allowed' => false, 'message' => 'Нападение возможно только на игрока в той же локации.'];
        }
        if ($location['type'] === 'forbidden') {
            return $base + ['allowed' => false, 'message' => 'В этой локации нападения полностью запрещены: стадионы, арены и аукционы охраняются стражей.'];
        }

        if ($attackerState === 'good' && $targetState === 'bad') {
            return [
                'allowed' => true,
                'rule' => 'protector_hunts_criminal',
                'requiresWarrant' => false,
                'message' => 'Защитник может охотиться на преступника в любой локации, кроме полностью запрещенных.',
            ] + $base;
        }
        if ($attackerState === 'bad' && $targetState === 'good' && $location['type'] === 'dangerous') {
            return [
                'allowed' => true,
                'rule' => 'criminal_attacks_protector',
                'requiresWarrant' => false,
                'message' => 'Преступник может нападать на защитников в опасных локациях без ордера.',
            ] + $base;
        }

        if ($location['type'] !== 'dangerous') {
            return $base + ['allowed' => false, 'message' => 'В безопасной локации можно нападать только Защитнику на Преступника.'];
        }

        $warrant = $this->bestAvailableWarrant($attackerId);
        if ($warrant === null) {
            return $base + ['allowed' => false, 'message' => 'Для нападения в опасной локации нужен ордер Команды R.'];
        }
        if ((int) ($attacker['rang_a'] ?? 0) < 400) {
            return $base + ['allowed' => false, 'message' => 'Для использования ордера нужно минимум 400 очков репутации.'];
        }
        if (!$this->targetAboveBeginner($target)) {
            return $base + ['allowed' => false, 'message' => 'Ордер не позволяет нападать на тренеров ранга Начинающий и ниже.'];
        }
        if ((int) $warrant['level'] === 1 && !$this->popularityAllowedByWarrant($attacker, $target)) {
            return $base + ['allowed' => false, 'message' => 'Ордер I уровня требует, чтобы популярность цели была не ниже 30% от вашей.'];
        }

        return [
            'allowed' => true,
            'rule' => $targetState === 'bad' ? 'warrant_attack_criminal' : 'warrant_attack_neutral',
            'requiresWarrant' => true,
            'warrant' => $warrant,
            'message' => $targetState === 'bad'
                ? 'Ордер Команды R позволяет напасть на преступника. Ордер будет потрачен.'
                : 'Ордер Команды R позволяет напасть в опасной локации. За нападение на нейтрального игрока карма снизится.',
        ] + $base;
    }

    public function usersCanStartPvp(int $firstUserId, int $secondUserId): bool
    {
        if ($firstUserId <= 0 || $secondUserId <= 0 || $firstUserId === $secondUserId) {
            return false;
        }

        $stmt = $this->db->prepare(
            'SELECT id, pve, pvp
               FROM users
              WHERE id IN (:first, :second) AND activation = 1'
        );
        $stmt->execute(['first' => $firstUserId, 'second' => $secondUserId]);
        $rows = $stmt->fetchAll();
        if (!is_array($rows) || count($rows) !== 2) {
            return false;
        }

        foreach ($rows as $row) {
            if ((int) ($row['pve'] ?? 0) !== 0 || (int) ($row['pvp'] ?? 0) !== 0) {
                return false;
            }
        }

        return $this->findFirstBattlePokemonForUser($firstUserId) !== null
            && $this->findFirstBattlePokemonForUser($secondUserId) !== null;
    }

    public function pvpRequestStatus(int $currentUserId, int $targetUserId): string
    {
        if ($currentUserId <= 0 || $targetUserId <= 0) {
            return 'none';
        }
        if ($currentUserId === $targetUserId) {
            return 'self';
        }

        if ($this->findActivePvpBattleIdForUser($currentUserId) > 0) {
            return 'active';
        }

        $stmt = $this->db->prepare(
            'SELECT from_user_id, to_user_id
               FROM pvp_requests
              WHERE status = "pending"
                AND ((from_user_id = :current_a AND to_user_id = :target_a)
                  OR (from_user_id = :target_b AND to_user_id = :current_b))
              ORDER BY id DESC
              LIMIT 1'
        );
        $stmt->execute([
            'current_a' => $currentUserId,
            'target_a' => $targetUserId,
            'target_b' => $targetUserId,
            'current_b' => $currentUserId,
        ]);
        $row = $stmt->fetch();
        if (!$row) {
            $permission = $this->pvpAttackPermission($currentUserId, $targetUserId);
            return !empty($permission['allowed']) ? 'none' : 'restricted';
        }

        return (int) ($row['to_user_id'] ?? 0) === $currentUserId ? 'incoming' : 'outgoing';
    }

    /** @return list<array{id:int,fromUserId:int,login:string,createdAt:int}> */
    public function incomingPvpRequests(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT pr.id, pr.from_user_id, pr.created_at, u.login
               FROM pvp_requests pr
               LEFT JOIN users u ON u.id = pr.from_user_id
              WHERE pr.to_user_id = :user AND pr.status = "pending"
              ORDER BY pr.id DESC
              LIMIT 20'
        );
        $stmt->execute(['user' => $userId]);

        $result = [];
        foreach ($stmt->fetchAll() ?: [] as $row) {
            $result[] = [
                'id' => (int) ($row['id'] ?? 0),
                'fromUserId' => (int) ($row['from_user_id'] ?? 0),
                'login' => (string) ($row['login'] ?? ('Игрок #' . (int) ($row['from_user_id'] ?? 0))),
                'createdAt' => (int) ($row['created_at'] ?? 0),
            ];
        }
        return $result;
    }

    public function requestOrAcceptPvp(int $fromUserId, int $toUserId, int $fromPokemonId = 0): array
    {
        if ($fromUserId <= 0 || $toUserId <= 0 || $fromUserId === $toUserId) {
            return ['ok' => false, 'message' => 'Нельзя вызвать этого игрока.'];
        }

        $chosenPokemon = $this->findChosenBattlePokemonForUser($fromUserId, $fromPokemonId);
        if ($chosenPokemon === null) {
            return ['ok' => false, 'message' => 'Выберите живого покемона из активной команды.'];
        }

        $incoming = $this->db->prepare(
            'SELECT id
               FROM pvp_requests
              WHERE from_user_id = :target AND to_user_id = :current AND status = "pending"
              ORDER BY id DESC
              LIMIT 1'
        );
        $incoming->execute(['target' => $toUserId, 'current' => $fromUserId]);
        $incomingId = (int) ($incoming->fetchColumn() ?: 0);
        if ($incomingId > 0) {
            return $this->acceptPvpRequest($fromUserId, $incomingId, $fromPokemonId);
        }

        $permission = $this->pvpAttackPermission($fromUserId, $toUserId);
        if (empty($permission['allowed'])) {
            return [
                'ok' => false,
                'status' => 'restricted',
                'message' => (string) ($permission['message'] ?? 'По карме нельзя вызвать этого игрока.'),
                'karma' => $permission,
            ];
        }

        if (!$this->usersCanStartPvp($fromUserId, $toUserId)) {
            return ['ok' => false, 'message' => 'Один из игроков уже занят или у него нет живого активного покемона.'];
        }

        $now = time();
        $existing = $this->db->prepare(
            'SELECT id
               FROM pvp_requests
              WHERE from_user_id = :from AND to_user_id = :to AND status = "pending"
              ORDER BY id DESC
              LIMIT 1'
        );
        $existing->execute(['from' => $fromUserId, 'to' => $toUserId]);
        $existingId = (int) ($existing->fetchColumn() ?: 0);
        if ($existingId > 0) {
            $this->db->prepare(
                'UPDATE pvp_requests
                    SET from_pokemon_id = :pokemon, updated_at = :now
                  WHERE id = :id
                  LIMIT 1'
            )->execute(['pokemon' => $fromPokemonId, 'now' => $now, 'id' => $existingId]);

            return ['ok' => true, 'status' => 'outgoing', 'message' => 'Вызов на бой уже отправлен.'];
        }

        $this->db->prepare(
            'INSERT INTO pvp_requests (from_user_id, to_user_id, from_pokemon_id, to_pokemon_id, status, battle_id, created_at, updated_at)
             VALUES (:from, :to, :pokemon, 0, "pending", 0, :created_at, :updated_at)'
        )->execute([
            'from' => $fromUserId,
            'to' => $toUserId,
            'pokemon' => $fromPokemonId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return ['ok' => true, 'status' => 'outgoing', 'message' => 'Вызов на бой отправлен.'];
    }

    public function acceptPvpRequest(int $userId, int $requestId, int $toPokemonId = 0): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, from_user_id, to_user_id, from_pokemon_id
               FROM pvp_requests
              WHERE id = :id AND to_user_id = :user AND status = "pending"
              LIMIT 1'
        );
        $stmt->execute(['id' => $requestId, 'user' => $userId]);
        $request = $stmt->fetch();
        if (!$request) {
            return ['ok' => false, 'message' => 'Заявка на бой не найдена.'];
        }

        $fromUserId = (int) ($request['from_user_id'] ?? 0);
        $toUserId = (int) ($request['to_user_id'] ?? 0);
        $fromPokemonId = (int) ($request['from_pokemon_id'] ?? 0);
        $permission = $this->pvpAttackPermission($fromUserId, $toUserId);
        if (empty($permission['allowed'])) {
            return [
                'ok' => false,
                'status' => 'restricted',
                'message' => (string) ($permission['message'] ?? 'По карме нельзя начать этот бой.'),
                'karma' => $permission,
            ];
        }

        if (!$this->usersCanStartPvp($fromUserId, $toUserId)) {
            return ['ok' => false, 'message' => 'Бой нельзя начать: один из игроков уже занят.'];
        }

        $firstPokemon = $this->findChosenBattlePokemonForUser($fromUserId, $fromPokemonId)
            ?? $this->findFirstBattlePokemonForUser($fromUserId);
        $secondPokemon = $this->findChosenBattlePokemonForUser($toUserId, $toPokemonId);
        if ($firstPokemon === null || $secondPokemon === null) {
            return ['ok' => false, 'message' => 'Выберите живого покемона из активной команды.'];
        }

        $battleId = $this->createPvpBattle($fromUserId, $toUserId, (int) $firstPokemon['id'], (int) $secondPokemon['id']);
        $this->applyPvpStartKarma($fromUserId, $toUserId, $battleId, $permission);
        $now = time();
        $this->db->prepare(
            'UPDATE pvp_requests
                SET status = "accepted", battle_id = :battle, to_pokemon_id = :pokemon, updated_at = :now
              WHERE id = :id
              LIMIT 1'
        )->execute(['battle' => $battleId, 'pokemon' => (int) $secondPokemon['id'], 'now' => $now, 'id' => $requestId]);
        $this->db->prepare(
            'UPDATE pvp_requests
                SET status = "expired", updated_at = :now
              WHERE status = "pending"
                AND (from_user_id IN (:from_a, :from_b) OR to_user_id IN (:to_a, :to_b))'
        )->execute([
            'now' => $now,
            'from_a' => $fromUserId,
            'from_b' => $toUserId,
            'to_a' => $fromUserId,
            'to_b' => $toUserId,
        ]);

        return [
            'ok' => true,
            'status' => 'active',
            'battleId' => $battleId,
            'message' => 'PvP бой начался.',
        ];
    }

    public function declinePvpRequest(int $userId, int $requestId): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE pvp_requests
                SET status = "declined", updated_at = :now
              WHERE id = :id AND to_user_id = :user AND status = "pending"
              LIMIT 1'
        );
        $stmt->execute(['now' => time(), 'id' => $requestId, 'user' => $userId]);
        return $stmt->rowCount() > 0;
    }

    public function createPvpBattle(int $firstUserId, int $secondUserId, int $firstPokemonId, int $secondPokemonId): int
    {
        $battleId = $this->nextTableId('battles', 'id');
        $now = time();
        $stmt = $this->db->prepare(
            'INSERT INTO battles
                (id, user_1, user_2, poke_1, poke_2, attac_1, attac_2, item_1, item_2,
                 to_p, to_p2, batl_tip, time, pobeda, raund, effect_go, effect_go2,
                 effect, effect2, id_pogodi, times, time_1, time_2, to_it, to_it2,
                 hod_user_id, tips_battle, zamtru_1, zamtru_2, room, dates)
             VALUES
                (:id, :user_1, :user_2, :poke_1, :poke_2, 0, 0, 0, 0,
                 0, 0, "pvp", 0, 0, 1, 0, 0,
                 0, 0, 1, :now, 0, 0, 0, 0,
                 0, 0, 0, 0, 0, "")'
        );
        $stmt->execute([
            'id' => $battleId,
            'user_1' => $firstUserId,
            'user_2' => $secondUserId,
            'poke_1' => 'pvp_' . $firstPokemonId,
            'poke_2' => 'pvp_' . $secondPokemonId,
            'now' => $now,
        ]);

        $this->db->prepare(
            'UPDATE users SET pvp = 1, pve = 0, battleid = :battle WHERE id IN (:first, :second)'
        )->execute(['battle' => $battleId, 'first' => $firstUserId, 'second' => $secondUserId]);

        return $battleId;
    }

    public function findPokemon(string $battlePokemon): ?array
    {
        $parsed = $this->parseBattlePokemon($battlePokemon);
        if ($parsed === null) {
            return null;
        }

        $table = $parsed['table'];
        $heldSelect = $table === 'pok_user' ? ', held.name AS held_item_name, held.tittle AS held_item_title' : ', NULL AS held_item_name, NULL AS held_item_title';
        $heldJoin = $table === 'pok_user' ? ' LEFT JOIN items held ON held.id = bp.item' : '';
        $stmt = $this->db->prepare(
            'SELECT bp.*, pk.Element, pk.SubElement, pk.Name AS dex_name,
                    pb.ability_key AS base_ability_key' . $heldSelect . '
               FROM ' . $table . ' bp
               LEFT JOIN pokemon pk ON pk.id = bp.basenum
               LEFT JOIN poke_base pb ON pb.id = bp.basenum' . $heldJoin . '
              WHERE bp.id = :id
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
        $this->applyTrainingBonus($row);
        return $row;
    }

    private function applyTrainingBonus(array &$pokemon): void
    {
        $stage = max(0, min(6, (int) ($pokemon['training_stage'] ?? 0)));
        $stat = (string) ($pokemon['training_stat'] ?? '');
        $bonus = match ($stage) {
            1 => 10,
            2 => 18,
            3 => 25,
            4 => 31,
            5 => 36,
            6 => 40,
            default => 0,
        };
        if ($bonus <= 0 || !in_array($stat, ['atk', 'def', 'satk', 'sdef', 'speed'], true)) {
            return;
        }

        $base = max(1, (int) ($pokemon[$stat] ?? 0));
        $pokemon[$stat] = max(1, (int) floor($base * (1 + $bonus / 100)));
        $pokemon['training_bonus_percent'] = $bonus;
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

    public function findBattleStatuses(int $battleId, string $battlePokemon): array
    {
        if ($battleId <= 0 || $battlePokemon === '') {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT attac, spattac, defend, spdefend, speed, acc, accuracy, tip
               FROM statpokemonbatle
              WHERE battleid = :battle AND pokeid = :pokemon'
        );
        $stmt->execute([
            'battle' => $battleId,
            'pokemon' => $battlePokemon,
        ]);

        $labels = [
            'attac' => 'Атака',
            'spattac' => 'С. Атака',
            'defend' => 'Защита',
            'spdefend' => 'С. Защита',
            'speed' => 'Скорость',
            'acc' => 'Ловкость',
            'accuracy' => 'Точность',
        ];

        // В старом коде могли появляться дубли строк plus/minus. Здесь агрегируем,
        // чтобы UI не показывал одно и то же усиление несколько раз.
        $sum = [
            'plus' => array_fill_keys(array_keys($labels), 0),
            'minus' => array_fill_keys(array_keys($labels), 0),
        ];

        foreach ($stmt->fetchAll() ?: [] as $row) {
            $kind = (string) ($row['tip'] ?? '') === 'minus' ? 'minus' : 'plus';
            foreach ($labels as $field => $_label) {
                $sum[$kind][$field] += max(0, (int) ($row[$field] ?? 0));
            }
        }

        $result = [];
        foreach (['plus', 'minus'] as $kind) {
            foreach ($labels as $field => $label) {
                $value = max(0, min(6, (int) $sum[$kind][$field]));
                if ($value <= 0) {
                    continue;
                }
                $result[] = [
                    'field' => $field,
                    'label' => $label,
                    'value' => $value,
                    'sign' => $kind === 'minus' ? '-' : '+',
                    'kind' => $kind,
                ];
            }
        }

        return $result;
    }

    public function findBattleStatStageMap(int $battleId, string $battlePokemon): array
    {
        $fields = ['attac', 'spattac', 'defend', 'spdefend', 'speed', 'acc', 'accuracy'];
        $map = array_fill_keys($fields, 0);

        if ($battleId <= 0 || $battlePokemon === '') {
            return $map;
        }

        $stmt = $this->db->prepare(
            'SELECT attac, spattac, defend, spdefend, speed, acc, accuracy, tip
               FROM statpokemonbatle
              WHERE battleid = :battle AND pokeid = :pokemon'
        );
        $stmt->execute([
            'battle' => $battleId,
            'pokemon' => $battlePokemon,
        ]);

        foreach ($stmt->fetchAll() ?: [] as $row) {
            $sign = (string) ($row['tip'] ?? '') === 'minus' ? -1 : 1;
            foreach ($fields as $field) {
                $map[$field] += $sign * max(0, (int) ($row[$field] ?? 0));
            }
        }

        foreach ($map as $field => $value) {
            $map[$field] = max(-6, min(6, (int) $value));
        }

        return $map;
    }

    public function applyBattleStatStage(int $battleId, string $battlePokemon, string $kind, string $field, int $delta): int
    {
        $allowedFields = ['attac', 'spattac', 'defend', 'spdefend', 'speed', 'acc', 'accuracy'];
        if ($battleId <= 0 || $battlePokemon === '' || !in_array($kind, ['plus', 'minus'], true) || !in_array($field, $allowedFields, true)) {
            return 0;
        }

        $delta = max(1, min(6, $delta));
        $this->ensureBattleStatRow($battleId, $battlePokemon, $kind);

        $read = $this->db->prepare(
            sprintf(
                'SELECT `%s` FROM statpokemonbatle WHERE battleid = :battle AND pokeid = :pokemon AND tip = :kind LIMIT 1',
                $field
            )
        );
        $read->execute(['battle' => $battleId, 'pokemon' => $battlePokemon, 'kind' => $kind]);
        $before = max(0, min(6, (int) ($read->fetchColumn() ?: 0)));
        $after = max(0, min(6, $before + $delta));
        $applied = max(0, $after - $before);
        if ($applied <= 0) {
            return 0;
        }

        $sql = sprintf(
            'UPDATE statpokemonbatle
                SET `%s` = :value
              WHERE battleid = :battle AND pokeid = :pokemon AND tip = :kind
              LIMIT 1',
            $field
        );

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'value' => $after,
            'battle' => $battleId,
            'pokemon' => $battlePokemon,
            'kind' => $kind,
        ]);

        return $applied;
    }

    public function ensureBattleStatRow(int $battleId, string $battlePokemon, string $kind): void
    {
        if ($battleId <= 0 || $battlePokemon === '' || !in_array($kind, ['plus', 'minus'], true)) {
            return;
        }

        $exists = $this->db->prepare(
            'SELECT id FROM statpokemonbatle WHERE battleid = :battle AND pokeid = :pokemon AND tip = :kind LIMIT 1'
        );
        $exists->execute([
            'battle' => $battleId,
            'pokemon' => $battlePokemon,
            'kind' => $kind,
        ]);
        if ($exists->fetchColumn() !== false) {
            return;
        }

        try {
            $stmt = $this->db->prepare(
                'INSERT INTO statpokemonbatle
                    (battleid, pokeid, attac, spattac, defend, spdefend, speed, acc, accuracy, tip, raundends)
                 VALUES
                    (:battle, :pokemon, 0, 0, 0, 0, 0, 0, 0, :kind, 0)'
            );
            $stmt->execute([
                'battle' => $battleId,
                'pokemon' => $battlePokemon,
                'kind' => $kind,
            ]);
            return;
        } catch (\Throwable) {
            // старый дамп без AUTO_INCREMENT на statpokemonbatle.id
        }

        try {
            $nextId = (int) ($this->db->query('SELECT COALESCE(MAX(id), 0) + 1 FROM statpokemonbatle')->fetchColumn() ?: 1);
            $stmt = $this->db->prepare(
                'INSERT INTO statpokemonbatle
                    (id, battleid, pokeid, attac, spattac, defend, spdefend, speed, acc, accuracy, tip, raundends)
                 VALUES
                    (:id, :battle, :pokemon, 0, 0, 0, 0, 0, 0, 0, :kind, 0)'
            );
            $stmt->execute([
                'id' => $nextId,
                'battle' => $battleId,
                'pokemon' => $battlePokemon,
                'kind' => $kind,
            ]);
        } catch (\Throwable) {
            // не валим бой из-за статов
        }
    }

    public function findAvailableMoves(int $baseId, int $level): array
    {
        $stmt = $this->db->prepare(
            'SELECT ap.atac_id AS id, ap.atc_lvl,
                    apw.atac_name, apw.atac_tip, apw.atac_power, apw.atac_accuracy, apw.atac_categori,
                    apw.critic, apw.priorety, apw.atac_pp, apw.chans_dop, apw.chans_effect, apw.atac_not,
                    apw.stati, apw.attac_effecti, apw.titles, apw.atac_tittle, apw.tittle_effect
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
                'atac_tip' => 'Normal',
                'atac_categori' => 1,
                'critic' => 1,
                'priorety' => 0,
                'atac_pp' => 35,
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
            'SELECT atac_id AS id, atac_name, atac_tip, atac_power, atac_accuracy, atac_categori,
                    critic, priorety, atac_pp, chans_dop, chans_effect, atac_not, stati, attac_effecti,
                    titles, atac_tittle, tittle_effect
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

    public function decrementSelectedMovePp(int $pokemonId, int $moveId): bool
    {
        if ($pokemonId <= 0 || $moveId <= 0) {
            return false;
        }

        $stmt = $this->db->prepare(
            'SELECT a_id, a_pp_min, b_id, b_pp_min, c_id, c_pp_min, d_id, d_pp_min
               FROM attac_my_poke
              WHERE pok_id = :pokemon
              LIMIT 1'
        );
        $stmt->execute(['pokemon' => $pokemonId]);
        $row = $stmt->fetch();
        if (!$row) {
            return false;
        }

        $slots = [
            ['id' => 'a_id', 'pp' => 'a_pp_min'],
            ['id' => 'b_id', 'pp' => 'b_pp_min'],
            ['id' => 'c_id', 'pp' => 'c_pp_min'],
            ['id' => 'd_id', 'pp' => 'd_pp_min'],
        ];
        foreach ($slots as $slot) {
            if ((int) ($row[$slot['id']] ?? 0) !== $moveId) {
                continue;
            }
            if ((int) ($row[$slot['pp']] ?? 0) <= 0) {
                return false;
            }
            $update = $this->db->prepare(
                sprintf(
                    'UPDATE attac_my_poke SET %s = GREATEST(%s - 1, 0) WHERE pok_id = :pokemon LIMIT 1',
                    $slot['pp'],
                    $slot['pp']
                )
            );
            $update->execute(['pokemon' => $pokemonId]);
            return $update->rowCount() > 0;
        }

        return false;
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

    public function setPvpBattleAction(int $battleId, int $side, int $moveId): void
    {
        $column = $side === 2 ? 'attac_2' : 'attac_1';
        $stmt = $this->db->prepare(
            sprintf('UPDATE battles SET %s = :move WHERE id = :id AND batl_tip = "pvp" AND %s = 0 LIMIT 1', $column, $column)
        );
        $stmt->execute(['move' => $moveId, 'id' => $battleId]);
    }

    public function resetPvpBattleActionsAndIncrementRound(int $battleId): void
    {
        $stmt = $this->db->prepare(
            'UPDATE battles SET attac_1 = 0, attac_2 = 0, raund = raund + 1 WHERE id = :id AND batl_tip = "pvp" LIMIT 1'
        );
        $stmt->execute(['id' => $battleId]);
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

    public function switchPvpPokemon(int $battleId, int $userId, int $pokemonId): bool
    {
        $battle = $this->findPvpBattleForUser($userId, $battleId);
        if ($battle === null) {
            return false;
        }

        $side = (int) ($battle['user_2'] ?? 0) === $userId ? 2 : 1;
        $check = $this->db->prepare(
            'SELECT id FROM pok_user WHERE id = :id AND users = :user AND active = 1 AND hp_my > 0 LIMIT 1'
        );
        $check->execute(['id' => $pokemonId, 'user' => $userId]);
        if (!$check->fetch()) {
            return false;
        }

        $stmt = $this->db->prepare(
            sprintf('UPDATE battles SET poke_%d = :poke WHERE id = :battle AND batl_tip = "pvp" LIMIT 1', $side)
        );
        $stmt->execute([
            'poke' => 'pvp_' . $pokemonId,
            'battle' => $battleId,
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
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO battle_log (battle_id, demage, raund) VALUES (:battle_id, :demage, :raund)'
            );
            $stmt->execute([
                'battle_id' => $battleId,
                'demage' => $message,
                'raund' => $round,
            ]);
            return;
        } catch (\Throwable) {
            // Server DB keeps battle_log.id without AUTO_INCREMENT. Use safe fallback.
        }

        $nextId = (int) ($this->db->query('SELECT COALESCE(MAX(id), 0) + 1 FROM battle_log')->fetchColumn() ?: 1);
        $stmt = $this->db->prepare(
            'INSERT INTO battle_log (id, battle_id, demage, raund) VALUES (:id, :battle_id, :demage, :raund)'
        );
        $stmt->execute([
            'id' => $nextId,
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

    public function addBattleHazard(int $battleId, int $side, string $kind): bool
    {
        $kind = $this->normalizeHazardKind($kind);
        $side = $side === 2 ? 2 : 1;
        if ($battleId <= 0 || $kind === '') {
            return false;
        }

        $pokeId = 'hazard:' . $side . ':' . $kind;
        $exists = $this->db->prepare(
            'SELECT id, at_dop FROM battle_dop WHERE battleid = :battle AND pokeid = :poke LIMIT 1'
        );
        $exists->execute(['battle' => $battleId, 'poke' => $pokeId]);
        $row = $exists->fetch();
        if ($row) {
            $maxLayers = match ($kind) {
                'spikes' => 3,
                'toxic_spikes' => 2,
                default => 1,
            };
            $layers = max(1, (int) ($row['at_dop'] ?? 1));
            if ($layers >= $maxLayers) {
                return false;
            }
            $this->db->prepare('UPDATE battle_dop SET at_dop = :layers WHERE id = :id')
                ->execute(['layers' => $layers + 1, 'id' => (int) ($row['id'] ?? 0)]);
            return true;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO battle_dop (id, battleid, pokeid, propusk, vulnerability, at_dop, mess)
             VALUES (:id, :battle, :poke, 0, 0, 1, :kind)'
        );
        $stmt->execute([
            'id' => $this->nextTableId('battle_dop', 'id'),
            'battle' => $battleId,
            'poke' => $pokeId,
            'kind' => $kind,
        ]);

        return true;
    }

    /** @return list<string> */
    public function findBattleHazards(int $battleId, int $side): array
    {
        $side = $side === 2 ? 2 : 1;
        if ($battleId <= 0) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT mess
               FROM battle_dop
              WHERE battleid = :battle AND pokeid LIKE :prefix
              ORDER BY id ASC'
        );
        $stmt->execute([
            'battle' => $battleId,
            'prefix' => 'hazard:' . $side . ':%',
        ]);

        $result = [];
        foreach ($stmt->fetchAll() ?: [] as $row) {
            $kind = $this->normalizeHazardKind((string) ($row['mess'] ?? ''));
            if ($kind !== '' && !in_array($kind, $result, true)) {
                $result[] = $kind;
            }
        }
        return $result;
    }

    /** @return array<string,int> */
    public function findBattleHazardLayers(int $battleId, int $side): array
    {
        $side = $side === 2 ? 2 : 1;
        if ($battleId <= 0) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT mess, at_dop
               FROM battle_dop
              WHERE battleid = :battle AND pokeid LIKE :prefix
              ORDER BY id ASC'
        );
        $stmt->execute([
            'battle' => $battleId,
            'prefix' => 'hazard:' . $side . ':%',
        ]);

        $result = [];
        foreach ($stmt->fetchAll() ?: [] as $row) {
            $kind = $this->normalizeHazardKind((string) ($row['mess'] ?? ''));
            if ($kind !== '') {
                $result[$kind] = max(1, (int) ($row['at_dop'] ?? 1));
            }
        }
        return $result;
    }

    public function setBattleWeather(int $battleId, string $kind, int $roundEnd): void
    {
        $kind = $this->normalizeWeatherKind($kind);
        if ($battleId <= 0 || $kind === '') {
            return;
        }

        $this->db->prepare('DELETE FROM battle_dop WHERE battleid = :battle AND pokeid = "field:weather"')
            ->execute(['battle' => $battleId]);

        $stmt = $this->db->prepare(
            'INSERT INTO battle_dop (id, battleid, pokeid, propusk, vulnerability, at_dop, mess)
             VALUES (:id, :battle, "field:weather", "field", :round_end, 0, :kind)'
        );
        $stmt->execute([
            'id' => $this->nextTableId('battle_dop', 'id'),
            'battle' => $battleId,
            'round_end' => max(1, $roundEnd),
            'kind' => $kind,
        ]);
    }

    /** @return array{kind:string,roundEnd:int}|null */
    public function findBattleWeather(int $battleId): ?array
    {
        if ($battleId <= 0) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT mess, vulnerability
               FROM battle_dop
              WHERE battleid = :battle AND pokeid = "field:weather"
              LIMIT 1'
        );
        $stmt->execute(['battle' => $battleId]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }

        $kind = $this->normalizeWeatherKind((string) ($row['mess'] ?? ''));
        return $kind === '' ? null : ['kind' => $kind, 'roundEnd' => (int) ($row['vulnerability'] ?? 0)];
    }

    public function clearBattleWeather(int $battleId): void
    {
        if ($battleId <= 0) {
            return;
        }
        $this->db->prepare('DELETE FROM battle_dop WHERE battleid = :battle AND pokeid = "field:weather"')
            ->execute(['battle' => $battleId]);
    }

    public function setBattleTerrain(int $battleId, string $kind, int $roundEnd): void
    {
        $kind = $this->normalizeTerrainKind($kind);
        if ($battleId <= 0 || $kind === '') {
            return;
        }

        $this->db->prepare('DELETE FROM battle_dop WHERE battleid = :battle AND pokeid = "field:terrain"')
            ->execute(['battle' => $battleId]);

        $stmt = $this->db->prepare(
            'INSERT INTO battle_dop (id, battleid, pokeid, propusk, vulnerability, at_dop, mess)
             VALUES (:id, :battle, "field:terrain", "field", :round_end, 0, :kind)'
        );
        $stmt->execute([
            'id' => $this->nextTableId('battle_dop', 'id'),
            'battle' => $battleId,
            'round_end' => max(1, $roundEnd),
            'kind' => $kind,
        ]);
    }

    /** @return array{kind:string,roundEnd:int}|null */
    public function findBattleTerrain(int $battleId): ?array
    {
        if ($battleId <= 0) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT mess, vulnerability
               FROM battle_dop
              WHERE battleid = :battle AND pokeid = "field:terrain"
              LIMIT 1'
        );
        $stmt->execute(['battle' => $battleId]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }

        $kind = $this->normalizeTerrainKind((string) ($row['mess'] ?? ''));
        return $kind === '' ? null : ['kind' => $kind, 'roundEnd' => (int) ($row['vulnerability'] ?? 0)];
    }

    public function addBattleRoom(int $battleId, string $kind, int $roundEnd): bool
    {
        $kind = $this->normalizeRoomKind($kind);
        if ($battleId <= 0 || $kind === '') {
            return false;
        }

        $pokeId = 'field:room:' . $kind;
        $exists = $this->db->prepare('SELECT id FROM battle_dop WHERE battleid = :battle AND pokeid = :poke LIMIT 1');
        $exists->execute(['battle' => $battleId, 'poke' => $pokeId]);
        if ($exists->fetchColumn() !== false) {
            return false;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO battle_dop (id, battleid, pokeid, propusk, vulnerability, at_dop, mess)
             VALUES (:id, :battle, :poke, "field", :round_end, 0, :kind)'
        );
        $stmt->execute([
            'id' => $this->nextTableId('battle_dop', 'id'),
            'battle' => $battleId,
            'poke' => $pokeId,
            'round_end' => max(1, $roundEnd),
            'kind' => $kind,
        ]);
        return true;
    }

    public function hasBattleRoom(int $battleId, string $kind): bool
    {
        $kind = $this->normalizeRoomKind($kind);
        if ($battleId <= 0 || $kind === '') {
            return false;
        }
        $stmt = $this->db->prepare('SELECT id FROM battle_dop WHERE battleid = :battle AND pokeid = :poke LIMIT 1');
        $stmt->execute(['battle' => $battleId, 'poke' => 'field:room:' . $kind]);
        return $stmt->fetchColumn() !== false;
    }

    public function addBattleVolatile(int $battleId, string $battlePokemon, string $kind, int $roundEnd): bool
    {
        $kind = $this->normalizeVolatileKind($kind);
        if ($battleId <= 0 || $battlePokemon === '' || $kind === '') {
            return false;
        }

        $pokeId = $this->volatileKey($battlePokemon, $kind);
        $exists = $this->db->prepare('SELECT id FROM battle_dop WHERE battleid = :battle AND pokeid = :poke LIMIT 1');
        $exists->execute(['battle' => $battleId, 'poke' => $pokeId]);
        if ($exists->fetchColumn() !== false) {
            return false;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO battle_dop (id, battleid, pokeid, propusk, vulnerability, at_dop, mess)
             VALUES (:id, :battle, :poke, :battle_pokemon, :round_end, 0, :kind)'
        );
        $stmt->execute([
            'id' => $this->nextTableId('battle_dop', 'id'),
            'battle' => $battleId,
            'poke' => $pokeId,
            'battle_pokemon' => $battlePokemon,
            'round_end' => max(1, $roundEnd),
            'kind' => $kind,
        ]);

        return true;
    }

    public function hasBattleVolatile(int $battleId, string $battlePokemon, string $kind): bool
    {
        $kind = $this->normalizeVolatileKind($kind);
        if ($battleId <= 0 || $battlePokemon === '' || $kind === '') {
            return false;
        }

        $stmt = $this->db->prepare('SELECT id FROM battle_dop WHERE battleid = :battle AND pokeid = :poke LIMIT 1');
        $stmt->execute(['battle' => $battleId, 'poke' => $this->volatileKey($battlePokemon, $kind)]);
        return $stmt->fetchColumn() !== false;
    }

    /** @return list<array{kind:string,roundEnd:int}> */
    public function findBattleVolatiles(int $battleId, string $battlePokemon): array
    {
        if ($battleId <= 0 || $battlePokemon === '') {
            return [];
        }

        $prefix = 'v:' . substr(sha1($battlePokemon), 0, 16) . ':%';
        $stmt = $this->db->prepare(
            'SELECT mess, vulnerability, at_dop
               FROM battle_dop
              WHERE battleid = :battle AND pokeid LIKE :prefix AND propusk = :battle_pokemon
              ORDER BY id ASC'
        );
        $stmt->execute([
            'battle' => $battleId,
            'prefix' => $prefix,
            'battle_pokemon' => $battlePokemon,
        ]);

        $rows = [];
        foreach ($stmt->fetchAll() ?: [] as $row) {
            $kind = $this->normalizeVolatileKind((string) ($row['mess'] ?? ''));
            if ($kind !== '') {
                $rows[] = ['kind' => $kind, 'roundEnd' => (int) ($row['vulnerability'] ?? 0), 'stacks' => (int) ($row['at_dop'] ?? 0)];
            }
        }
        return $rows;
    }

    public function incrementBattleVolatileStacks(int $battleId, string $battlePokemon, string $kind): int
    {
        $kind = $this->normalizeVolatileKind($kind);
        if ($battleId <= 0 || $battlePokemon === '' || $kind === '') {
            return 0;
        }
        $pokeId = $this->volatileKey($battlePokemon, $kind);
        $this->db->prepare('UPDATE battle_dop SET at_dop = at_dop + 1 WHERE battleid = :battle AND pokeid = :poke')
            ->execute(['battle' => $battleId, 'poke' => $pokeId]);

        $stmt = $this->db->prepare('SELECT at_dop FROM battle_dop WHERE battleid = :battle AND pokeid = :poke LIMIT 1');
        $stmt->execute(['battle' => $battleId, 'poke' => $pokeId]);
        return max(0, (int) ($stmt->fetchColumn() ?: 0));
    }

    public function clearSwitchVolatiles(int $battleId, string $battlePokemon): void
    {
        if ($battleId <= 0 || $battlePokemon === '') {
            return;
        }
        $prefix = 'v:' . substr(sha1($battlePokemon), 0, 16) . ':%';
        $this->db->prepare(
            'DELETE FROM battle_dop
              WHERE battleid = :battle
                AND pokeid LIKE :prefix
                AND propusk = :battle_pokemon
                AND mess IN ("trap", "partial_trap", "confusion", "leech_seed", "nightmare", "perish_song", "taunt", "heal_block", "encore", "torment", "disable", "knock_off", "weather_started")'
        )->execute([
            'battle' => $battleId,
            'prefix' => $prefix,
            'battle_pokemon' => $battlePokemon,
        ]);
    }

    public function addBattleSideField(int $battleId, int $side, string $kind, int $roundEnd): bool
    {
        $kind = $this->normalizeSideFieldKind($kind);
        $side = $side === 2 ? 2 : 1;
        if ($battleId <= 0 || $kind === '') {
            return false;
        }

        $pokeId = 'sidefield:' . $side . ':' . $kind;
        $exists = $this->db->prepare('SELECT id FROM battle_dop WHERE battleid = :battle AND pokeid = :poke LIMIT 1');
        $exists->execute(['battle' => $battleId, 'poke' => $pokeId]);
        if ($exists->fetchColumn() !== false) {
            return false;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO battle_dop (id, battleid, pokeid, propusk, vulnerability, at_dop, mess)
             VALUES (:id, :battle, :poke, :side, :round_end, 0, :kind)'
        );
        $stmt->execute([
            'id' => $this->nextTableId('battle_dop', 'id'),
            'battle' => $battleId,
            'poke' => $pokeId,
            'side' => (string) $side,
            'round_end' => max(1, $roundEnd),
            'kind' => $kind,
        ]);

        return true;
    }

    /** @return list<array{kind:string,roundEnd:int}> */
    public function findBattleSideFields(int $battleId, int $side): array
    {
        $side = $side === 2 ? 2 : 1;
        if ($battleId <= 0) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT mess, vulnerability
               FROM battle_dop
              WHERE battleid = :battle AND pokeid LIKE :prefix
              ORDER BY id ASC'
        );
        $stmt->execute([
            'battle' => $battleId,
            'prefix' => 'sidefield:' . $side . ':%',
        ]);

        $rows = [];
        foreach ($stmt->fetchAll() ?: [] as $row) {
            $kind = $this->normalizeSideFieldKind((string) ($row['mess'] ?? ''));
            if ($kind !== '') {
                $rows[] = ['kind' => $kind, 'roundEnd' => (int) ($row['vulnerability'] ?? 0)];
            }
        }
        return $rows;
    }

    public function deleteExpiredBattleEffects(int $battleId, int $round): void
    {
        if ($battleId <= 0) {
            return;
        }

        $this->db->prepare(
            'DELETE FROM battle_dop
              WHERE battleid = :battle
                AND vulnerability > 0
                AND vulnerability <= :round
                AND (pokeid LIKE "v:%" OR pokeid LIKE "sidefield:%" OR pokeid = "field:weather" OR pokeid = "field:terrain" OR pokeid LIKE "field:room:%")'
        )->execute(['battle' => $battleId, 'round' => $round]);
    }

    public function clearBattleHazards(int $battleId): void
    {
        if ($battleId <= 0) {
            return;
        }

        $this->db->prepare('DELETE FROM battle_dop WHERE battleid = :battle AND pokeid LIKE "hazard:%"')
            ->execute(['battle' => $battleId]);
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
        $pokemonId = $this->nextTableId('pok_user', 'id');

        $insert = $this->db->prepare(
            'INSERT INTO pok_user
                (id, users, basenum, names, active, evcount, lvl, sex, har, hp_my, hp_max, exp, exp_b,
                 atk, def, satk, sdef, speed, hp_ev, atk_ev, def_ev, satk_ev, sdef_ev, speed_ev,
                 hp_iv, atk_iv, def_iv, satk_iv, sdef_iv, speed_iv, tips, startone, startepoke,
                 reproduction, happy, datemay, usersone, sprz, item)
             VALUES
                (:id, :users, :basenum, :names, :active, :evcount, :lvl, :sex, :har, :hp_my, :hp_max, :exp, :exp_b,
                 :atk, :def, :satk, :sdef, :speed, :hp_ev, :atk_ev, :def_ev, :satk_ev, :sdef_ev, :speed_ev,
                 :hp_iv, :atk_iv, :def_iv, :satk_iv, :sdef_iv, :speed_iv, :tips, :startone, 0,
                 :reproduction, :happy, :datemay, :usersone, :sprz, 0)'
        );
        $insert->execute([
            'id' => $pokemonId,
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

        $this->ensureDefaultMovesForCaughtPokemon(
            $pokemonId,
            (int) ($wild['basenum'] ?? 0),
            (int) ($wild['lvl'] ?? 1)
        );

        return $pokemonId;
    }

    private function ensureDefaultMovesForCaughtPokemon(int $pokemonId, int $baseId, int $level): void
    {
        if ($pokemonId <= 0) {
            return;
        }

        $exists = $this->db->prepare('SELECT id FROM attac_my_poke WHERE pok_id = :pokemon LIMIT 1');
        $exists->execute(['pokemon' => $pokemonId]);
        if ($exists->fetchColumn() !== false) {
            return;
        }

        $moves = array_slice($this->findAvailableMoves($baseId, $level), 0, 4);
        $slots = [
            'a' => ['id' => 0, 'pp' => 0],
            'b' => ['id' => 0, 'pp' => 0],
            'c' => ['id' => 0, 'pp' => 0],
            'd' => ['id' => 0, 'pp' => 0],
        ];

        foreach (array_values($moves) as $index => $move) {
            $slotKey = ['a', 'b', 'c', 'd'][$index] ?? null;
            if ($slotKey === null) {
                break;
            }

            $moveId = (int) ($move['id'] ?? 0);
            if ($moveId <= 0) {
                continue;
            }

            $slots[$slotKey] = [
                'id' => $moveId,
                'pp' => max(1, (int) ($move['atac_pp'] ?? 15)),
            ];
        }

        $stmt = $this->db->prepare(
            'INSERT INTO attac_my_poke
                (id, pok_id, a_id, a_pp_min, a_pp_max, b_id, b_pp_min, b_pp_max, c_id, c_pp_min, c_pp_max, d_id, d_pp_min, d_pp_max)
             VALUES
                (:id, :pokemon, :a_id, :a_pp_min, :a_pp_max, :b_id, :b_pp_min, :b_pp_max, :c_id, :c_pp_min, :c_pp_max, :d_id, :d_pp_min, :d_pp_max)'
        );
        $stmt->execute([
            'id' => $this->nextTableId('attac_my_poke', 'id'),
            'pokemon' => $pokemonId,
            'a_id' => $slots['a']['id'],
            'a_pp_min' => $slots['a']['pp'],
            'a_pp_max' => $slots['a']['pp'],
            'b_id' => $slots['b']['id'],
            'b_pp_min' => $slots['b']['pp'],
            'b_pp_max' => $slots['b']['pp'],
            'c_id' => $slots['c']['id'],
            'c_pp_min' => $slots['c']['pp'],
            'c_pp_max' => $slots['c']['pp'],
            'd_id' => $slots['d']['id'],
            'd_pp_min' => $slots['d']['pp'],
            'd_pp_max' => $slots['d']['pp'],
        ]);
    }

    public function finishBattle(int $battleId, int $userId, int $winner): void
    {
        // Не удаляем бой/лог сразу: frontend должен успеть показать финальный экран.
        // Удаление делается только после /api/battle/pve/ack-end.
        $this->db->prepare(
            'UPDATE battles SET pobeda = :winner WHERE id = :id LIMIT 1'
        )->execute(['winner' => $winner, 'id' => $battleId]);

        $this->db->prepare(
            'UPDATE users SET pve = 0, battleid = :battle, atack_poke = :next_attack WHERE id = :id LIMIT 1'
        )->execute([
            'battle' => $battleId,
            'next_attack' => time() + 30,
            'id' => $userId,
        ]);
    }

    public function finishPvpBattle(int $battleId, int $winner): void
    {
        $this->db->prepare(
            'UPDATE battles SET pobeda = :winner WHERE id = :id AND batl_tip = "pvp" LIMIT 1'
        )->execute(['winner' => $winner, 'id' => $battleId]);

        $this->db->prepare(
            'UPDATE users
                SET pvp = 0, battleid = :set_battle
              WHERE battleid = :where_battle AND pvp = 1'
        )->execute(['set_battle' => $battleId, 'where_battle' => $battleId]);
    }

    public function acknowledgePvpBattleForUser(int $userId): void
    {
        if ($userId <= 0) {
            return;
        }

        $battleId = $this->findActivePvpBattleIdForUser($userId);
        if ($battleId <= 0) {
            $this->db->prepare('UPDATE users SET pvp = 0, battleid = 0 WHERE id = :id AND pvp = 0 LIMIT 1')
                ->execute(['id' => $userId]);
            return;
        }

        $battle = $this->findPvpBattleForUser($userId, $battleId);
        if ($battle !== null && (int) ($battle['pobeda'] ?? 0) !== 0) {
            $this->db->prepare('UPDATE users SET pvp = 0, battleid = 0 WHERE id = :id LIMIT 1')
                ->execute(['id' => $userId]);
        }
    }

    public function cleanupFinishedBattleForUser(int $userId): void
    {
        if ($userId <= 0) {
            return;
        }

        $stmt = $this->db->prepare('SELECT battleid FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $userId]);
        $battleId = (int) ($stmt->fetchColumn() ?: 0);
        if ($battleId <= 0) {
            return;
        }

        $battle = $this->db->prepare(
            'SELECT id, poke_2, pobeda
               FROM battles
              WHERE id = :id AND user_1 = :user AND batl_tip = "pve"
              LIMIT 1'
        );
        $battle->execute([
            'id' => $battleId,
            'user' => $userId,
        ]);
        $row = $battle->fetch();
        if (!$row) {
            $this->db->prepare('UPDATE users SET battleid = 0 WHERE id = :id LIMIT 1')->execute(['id' => $userId]);
            return;
        }

        if ((int) ($row['pobeda'] ?? 0) === 0) {
            // Бой еще не помечен как завершенный.
            return;
        }

        $enemyBattlePokemon = (string) ($row['poke_2'] ?? '');

        $this->db->prepare('DELETE FROM statpokemonbatle WHERE battleid = :id')->execute(['id' => $battleId]);
        $this->db->prepare('DELETE FROM battle_dop WHERE battleid = :id')->execute(['id' => $battleId]);
        $this->db->prepare('DELETE FROM battle_log WHERE battle_id = :id')->execute(['id' => $battleId]);
        $this->db->prepare('DELETE FROM battles WHERE id = :id LIMIT 1')->execute(['id' => $battleId]);

        $parsed = $this->parseBattlePokemon($enemyBattlePokemon);
        if ($parsed !== null && $parsed['table'] === 'pok_pve') {
            $this->db->prepare('DELETE FROM pok_pve WHERE id = :id LIMIT 1')->execute(['id' => $parsed['id']]);
        }

        $this->db->prepare('UPDATE users SET battleid = 0 WHERE id = :id LIMIT 1')->execute(['id' => $userId]);
    }


    /**
     * Returns separate plus/minus stage storage. Do not collapse to one net value:
     * legacy math uses (2 + plus) / (2 + minus).
     *
     * @return array{plus: array<string,int>, minus: array<string,int>}
     */
    public function findBattleStatStageParts(int $battleId, string $battlePokemon): array
    {
        $fields = ['attac', 'spattac', 'defend', 'spdefend', 'speed', 'acc', 'accuracy'];
        $result = [
            'plus' => array_fill_keys($fields, 0),
            'minus' => array_fill_keys($fields, 0),
        ];

        if ($battleId <= 0 || $battlePokemon === '') {
            return $result;
        }

        $stmt = $this->db->prepare(
            'SELECT attac, spattac, defend, spdefend, speed, acc, accuracy, tip
               FROM statpokemonbatle
              WHERE battleid = :battle AND pokeid = :pokemon'
        );
        $stmt->execute(['battle' => $battleId, 'pokemon' => $battlePokemon]);

        foreach ($stmt->fetchAll() ?: [] as $row) {
            $kind = (string) ($row['tip'] ?? '') === 'minus' ? 'minus' : 'plus';
            foreach ($fields as $field) {
                $result[$kind][$field] += max(0, (int) ($row[$field] ?? 0));
            }
        }

        foreach (['plus', 'minus'] as $kind) {
            foreach ($fields as $field) {
                $result[$kind][$field] = max(0, min(6, (int) $result[$kind][$field]));
            }
        }

        return $result;
    }

    /**
     * @return list<array{target:string,kind:string,field:string,delta:int,label:string}>
     */
    public function findMoveStatEffects(int $moveId): array
    {
        if ($moveId <= 0) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT tip, def, atc, sdef, satc, speed, acc, accuracy,
                    tip_b, def_b, atc_b, sdef_b, satc_b, speed_b, acc_b, accuracy_b
               FROM stat_attak
              WHERE id_atk = :move
              LIMIT 1'
        );
        $stmt->execute(['move' => $moveId]);
        $row = $stmt->fetch();
        if (!$row) {
            return [];
        }

        $effects = [];
        $this->appendStageEffectsFromRow($effects, $row, 'self', (string) ($row['tip'] ?? ''), false);
        $this->appendStageEffectsFromRow($effects, $row, 'enemy', (string) ($row['tip_b'] ?? ''), true);
        return $effects;
    }

    /** @return list<array{target:string,kind:string,field:string,delta:int,label:string,statusId?:int,chance?:int}> */
    public function findMoveSecondaryEffects(int $moveId): array
    {
        if ($moveId <= 0) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT ad.dop_effc, ad.setting, ad.tip_s, ad.def, ad.atc, ad.sdef, ad.satc, ad.speed, ad.acc, ad.accuracy,
                    ap.chans_dop, ap.chans_effect, ap.atac_not
               FROM attac_dop ad
               LEFT JOIN attac_power ap ON ap.atac_id = ad.id_attc
              WHERE ad.id_attc = :move
              LIMIT 1'
        );
        $stmt->execute(['move' => $moveId]);
        $row = $stmt->fetch();
        if (!$row) {
            return [];
        }

        $chance = (int) ($row['chans_dop'] ?? 0);
        if ($chance <= 0) {
            $chance = (int) ($row['chans_effect'] ?? 0);
        }
        $setting = (int) ($row['setting'] ?? 0);
        // chans_dop/chans_effect = 0 means no random secondary effect.
        // Do NOT convert 0 to 100, otherwise burn/paralyze/debuff becomes guaranteed.
        if ($chance <= 0) {
            return [];
        }

        if ($setting === 1 && (int) ($row['dop_effc'] ?? 0) > 0) {
            return [[
                'target' => 'enemy',
                'kind' => 'status',
                'field' => 'status',
                'delta' => 0,
                'label' => 'Статус',
                'statusId' => (int) $row['dop_effc'],
                'chance' => max(1, min(100, $chance)),
            ]];
        }

        if ($setting === 2 || $setting === 3) {
            $effects = [];
            $target = $setting === 3 ? 'self' : 'enemy';
            $kind = (string) ($row['tip_s'] ?? '') === 'plus' ? 'plus' : 'minus';
            $this->appendStageEffectsFromSecondaryRow($effects, $row, $target, $kind, max(1, min(100, $chance)));
            return $effects;
        }

        return [];
    }

    public function findMovePrimaryStatusId(int $moveId): int
    {
        if ($moveId <= 0) {
            return 0;
        }
        $stmt = $this->db->prepare('SELECT atac_not FROM attac_power WHERE atac_id = :move LIMIT 1');
        $stmt->execute(['move' => $moveId]);
        return max(0, (int) ($stmt->fetchColumn() ?: 0));
    }

    /** @return list<array{id:int,name:string,endsAt:int}> */
    public function findBattleMajorStatuses(int $battleId, string $battlePokemon): array
    {
        if ($battleId <= 0 || $battlePokemon === '') {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT bs.namber_st, bs.raund_end, st.tittle_status
               FROM bttle_status bs
               LEFT JOIN status st ON st.id_status = bs.namber_st
              WHERE bs.buttleid = :battle AND bs.pokeid = :pokemon'
        );
        $stmt->execute(['battle' => $battleId, 'pokemon' => $battlePokemon]);

        $rows = [];
        foreach ($stmt->fetchAll() ?: [] as $row) {
            $rows[] = [
                'id' => (int) ($row['namber_st'] ?? 0),
                'name' => (string) ($row['tittle_status'] ?? ('Статус #' . (int) ($row['namber_st'] ?? 0))),
                'endsAt' => (int) ($row['raund_end'] ?? 0),
            ];
        }
        return $rows;
    }

    public function hasBattleStatus(int $battleId, string $battlePokemon, int $statusId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT id_sts FROM bttle_status WHERE buttleid = :battle AND pokeid = :pokemon AND namber_st = :status LIMIT 1'
        );
        $stmt->execute(['battle' => $battleId, 'pokemon' => $battlePokemon, 'status' => $statusId]);
        return $stmt->fetchColumn() !== false;
    }

    public function applyBattleStatus(int $battleId, string $battlePokemon, int $statusId, int $currentRound, int $duration = 4): bool
    {
        if ($battleId <= 0 || $battlePokemon === '' || $statusId <= 0) {
            return false;
        }
        if ($this->hasBattleStatus($battleId, $battlePokemon, $statusId)) {
            return false;
        }
        if (in_array($statusId, [1, 2, 3, 4, 5], true)) {
            $stable = $this->db->prepare(
                'SELECT id_sts FROM bttle_status
                  WHERE buttleid = :battle AND pokeid = :pokemon AND namber_st IN (1, 2, 3, 4, 5)
                  LIMIT 1'
            );
            $stable->execute(['battle' => $battleId, 'pokemon' => $battlePokemon]);
            if ($stable->fetchColumn() !== false) {
                return false;
            }
        }

        $roundEnd = $statusId === 1 || $statusId === 3 ? 999999 : max($currentRound + 1, $currentRound + $duration);
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO bttle_status (namber_st, buttleid, pokeid, raund_end, tip_poke)
                 VALUES (:status, :battle, :pokemon, :round_end, 1)'
            );
            $stmt->execute([
                'status' => $statusId,
                'battle' => $battleId,
                'pokemon' => $battlePokemon,
                'round_end' => $roundEnd,
            ]);
            return true;
        } catch (\Throwable) {
            // old dump without auto increment
        }

        try {
            $nextId = (int) ($this->db->query('SELECT COALESCE(MAX(id_sts), 0) + 1 FROM bttle_status')->fetchColumn() ?: 1);
            $stmt = $this->db->prepare(
                'INSERT INTO bttle_status (id_sts, namber_st, buttleid, pokeid, raund_end, tip_poke)
                 VALUES (:id, :status, :battle, :pokemon, :round_end, 1)'
            );
            $stmt->execute([
                'id' => $nextId,
                'status' => $statusId,
                'battle' => $battleId,
                'pokemon' => $battlePokemon,
                'round_end' => $roundEnd,
            ]);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    public function deleteBattleStatus(int $battleId, string $battlePokemon, int $statusId): void
    {
        $stmt = $this->db->prepare('DELETE FROM bttle_status WHERE buttleid = :battle AND pokeid = :pokemon AND namber_st = :status');
        $stmt->execute(['battle' => $battleId, 'pokemon' => $battlePokemon, 'status' => $statusId]);
    }

    public function deleteExpiredBattleStatuses(int $battleId, int $round): void
    {
        if ($battleId <= 0) {
            return;
        }
        $stmt = $this->db->prepare('DELETE FROM bttle_status WHERE buttleid = :battle AND raund_end > 0 AND raund_end <= :round');
        $stmt->execute(['battle' => $battleId, 'round' => $round]);
    }

    /** @param list<array<string,mixed>> $effects */
    private function appendStageEffectsFromRow(array &$effects, array $row, string $target, string $kind, bool $suffixB): void
    {
        if (!in_array($kind, ['plus', 'minus'], true)) {
            return;
        }
        $fields = $this->stageFieldMap($suffixB);
        foreach ($fields as $column => [$field, $label]) {
            $delta = max(0, (int) ($row[$column] ?? 0));
            if ($delta <= 0) {
                continue;
            }
            $effects[] = ['target' => $target, 'kind' => $kind, 'field' => $field, 'delta' => $delta, 'label' => $label];
        }
    }

    /** @param list<array<string,mixed>> $effects */
    private function appendStageEffectsFromSecondaryRow(array &$effects, array $row, string $target, string $kind, int $chance): void
    {
        foreach ($this->stageFieldMap(false) as $column => [$field, $label]) {
            $delta = max(0, (int) ($row[$column] ?? 0));
            if ($delta <= 0) {
                continue;
            }
            $effects[] = ['target' => $target, 'kind' => $kind, 'field' => $field, 'delta' => $delta, 'label' => $label, 'chance' => $chance];
        }
    }

    /** @return array<string, array{string,string}> */
    private function stageFieldMap(bool $suffixB): array
    {
        $s = $suffixB ? '_b' : '';
        return [
            'atc' . $s => ['attac', 'Атака'],
            'satc' . $s => ['spattac', 'Спец. Атака'],
            'def' . $s => ['defend', 'Защита'],
            'sdef' . $s => ['spdefend', 'Спец. Защита'],
            'speed' . $s => ['speed', 'Скорость'],
            'acc' . $s => ['acc', 'Ловкость'],
            'accuracy' . $s => ['accuracy', 'Точность'],
        ];
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
        return $itemId === 2
            || str_contains($name, 'скоб')
            || str_contains($name, 'macho')
            || str_contains($name, 'brace');
    }

    /** @param list<int> $ids */
    private function karmaUsers(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if ($ids === []) {
            return [];
        }

        $placeholders = [];
        $params = [];
        foreach ($ids as $index => $id) {
            $key = 'id' . $index;
            $placeholders[] = ':' . $key;
            $params[$key] = $id;
        }

        $stmt = $this->db->prepare(
            'SELECT id, login, groups, karma_score, rang_a, rang_b, buildmy
               FROM users
              WHERE id IN (' . implode(',', $placeholders) . ') AND activation = 1'
        );
        $stmt->execute($params);

        $users = [];
        foreach ($stmt->fetchAll() ?: [] as $row) {
            $users[(int) $row['id']] = $row;
        }
        return $users;
    }

    private function karmaState(array $user): string
    {
        $group = (int) ($user['groups'] ?? 6);
        $score = (int) ($user['karma_score'] ?? 0);
        if (in_array($group, [7, 10], true) || $score <= -10) {
            return 'bad';
        }
        if ($score >= 10) {
            return 'good';
        }
        return 'neutral';
    }

    private function karmaSummary(array $user): array
    {
        $state = $this->karmaState($user);
        return [
            'id' => (int) ($user['id'] ?? 0),
            'login' => (string) ($user['login'] ?? ''),
            'score' => (int) ($user['karma_score'] ?? 0),
            'state' => $state,
            'title' => match ($state) {
                'bad' => 'Плохая репутация',
                'good' => 'Хорошая репутация',
                default => 'Нейтральная репутация',
            },
        ];
    }

    private function karmaBattleLocation(int $attackerLocationId, int $targetLocationId): array
    {
        if ($attackerLocationId <= 0 || $targetLocationId <= 0 || $attackerLocationId !== $targetLocationId) {
            return [
                'id' => $attackerLocationId,
                'sameLocation' => false,
                'type' => 'unknown',
                'title' => '',
            ];
        }

        $stmt = $this->db->prepare('SELECT id, title, pve, zax FROM build WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $attackerLocationId]);
        $location = $stmt->fetch() ?: [];
        $title = (string) ($location['title'] ?? '');
        $type = $this->karmaLocationType($title, (int) ($location['pve'] ?? 0), (int) ($location['zax'] ?? 0));

        return [
            'id' => $attackerLocationId,
            'sameLocation' => true,
            'type' => $type,
            'title' => $title,
        ];
    }

    private function karmaLocationType(string $title, int $pve, int $zax): string
    {
        $name = function_exists('mb_strtolower') ? mb_strtolower($title) : strtolower($title);
        foreach (['стадион', 'арена', 'аукцион', 'турнир'] as $word) {
            if (str_contains($name, $word)) {
                return 'forbidden';
            }
        }
        foreach (['дорога', 'лес', 'пещер', 'озеро', 'туннел', 'пустын', 'гора', 'подвал', 'шахт', 'путь'] as $word) {
            if (str_contains($name, $word)) {
                return 'dangerous';
            }
        }
        return 'safe';
    }

    private function bestAvailableWarrant(int $userId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT item_id, count
               FROM items_users
              WHERE user_id = :user AND item_id IN (90001, 90002) AND count > 0
              ORDER BY item_id DESC
              LIMIT 1'
        );
        $stmt->execute(['user' => $userId]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }

        $itemId = (int) ($row['item_id'] ?? 0);
        return [
            'itemId' => $itemId,
            'level' => $itemId === 90002 ? 2 : 1,
        ];
    }

    private function consumeWarrant(int $userId, int $itemId): bool
    {
        if (!in_array($itemId, [90001, 90002], true)) {
            return false;
        }

        $stmt = $this->db->prepare(
            'UPDATE items_users
                SET count = count - 1
              WHERE user_id = :user AND item_id = :item AND count > 0
              LIMIT 1'
        );
        $stmt->execute(['user' => $userId, 'item' => $itemId]);
        if ($stmt->rowCount() <= 0) {
            return false;
        }

        $this->db->prepare('DELETE FROM items_users WHERE user_id = :user AND item_id = :item AND count <= 0')
            ->execute(['user' => $userId, 'item' => $itemId]);
        return true;
    }

    private function targetAboveBeginner(array $target): bool
    {
        return (int) ($target['rang_a'] ?? 0) > 250 || (int) ($target['rang_b'] ?? 0) > 250;
    }

    private function popularityAllowedByWarrant(array $attacker, array $target): bool
    {
        $attackerPopularity = max(0, (int) ($attacker['rang_b'] ?? 0));
        $targetPopularity = max(0, (int) ($target['rang_b'] ?? 0));
        return $targetPopularity >= (int) ceil($attackerPopularity * 0.3);
    }

    private function applyPvpStartKarma(int $attackerId, int $targetId, int $battleId, array $permission): void
    {
        if (!empty($permission['requiresWarrant']) && !empty($permission['warrant']['itemId'])) {
            $this->consumeWarrant($attackerId, (int) $permission['warrant']['itemId']);
        }

        $rule = (string) ($permission['rule'] ?? '');
        if (in_array($rule, ['protector_hunts_criminal', 'warrant_attack_criminal'], true)) {
            $this->changeKarma($attackerId, $targetId, $battleId, 1, $rule);
            return;
        }
        if ($rule === 'warrant_attack_neutral') {
            $this->changeKarma($attackerId, $targetId, $battleId, -1, $rule);
        }
    }

    private function changeKarma(int $userId, int $targetUserId, int $battleId, int $delta, string $reason): void
    {
        if ($userId <= 0 || $delta === 0) {
            return;
        }

        $this->db->prepare('UPDATE users SET karma_score = karma_score + :delta WHERE id = :id LIMIT 1')
            ->execute(['delta' => $delta, 'id' => $userId]);

        $stmt = $this->db->prepare('SELECT karma_score FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $userId]);
        $scoreAfter = (int) ($stmt->fetchColumn() ?: 0);

        $this->db->prepare(
            'INSERT INTO karma_events (user_id, target_user_id, battle_id, delta, score_after, reason, created_at)
             VALUES (:user, :target, :battle, :delta, :score, :reason, :created)'
        )->execute([
            'user' => $userId,
            'target' => $targetUserId,
            'battle' => $battleId,
            'delta' => $delta,
            'score' => $scoreAfter,
            'reason' => $reason,
            'created' => time(),
        ]);
    }

    private function nextTableId(string $table, string $column): int
    {
        $allowed = [
            'pok_user' => ['id'],
            'attac_my_poke' => ['id'],
            'battle_dop' => ['id'],
            'battles' => ['id'],
        ];
        if (!isset($allowed[$table]) || !in_array($column, $allowed[$table], true)) {
            throw new \InvalidArgumentException('Unsupported sequence target.');
        }

        return (int) ($this->db
            ->query(sprintf('SELECT COALESCE(MAX(%s), 0) + 1 FROM %s', $column, $table))
            ->fetchColumn() ?: 1);
    }

    private function normalizeHazardKind(string $kind): string
    {
        $kind = strtolower(trim($kind));
        return match ($kind) {
            'spikes', 'toxic_spikes', 'stealth_rock', 'sticky_web', 'steel_spikes' => $kind,
            default => '',
        };
    }

    private function normalizeWeatherKind(string $kind): string
    {
        $kind = strtolower(trim($kind));
        return match ($kind) {
            'sun', 'rain', 'sandstorm', 'hail' => $kind,
            default => '',
        };
    }

    private function normalizeTerrainKind(string $kind): string
    {
        $kind = strtolower(trim($kind));
        return match ($kind) {
            'electric', 'misty', 'grassy' => $kind,
            default => '',
        };
    }

    private function normalizeRoomKind(string $kind): string
    {
        $kind = strtolower(trim($kind));
        return match ($kind) {
            'trick_room', 'wonder_room', 'magic_room' => $kind,
            default => '',
        };
    }

    private function normalizeVolatileKind(string $kind): string
    {
        $kind = strtolower(trim($kind));
        return match ($kind) {
            'trap', 'partial_trap', 'badly_poisoned', 'confusion', 'curse', 'leech_seed', 'nightmare', 'perish_song',
            'destiny_bond', 'taunt', 'heal_block', 'encore', 'torment', 'disable', 'knock_off' => $kind,
            'weather_started' => $kind,
            default => '',
        };
    }

    private function normalizeSideFieldKind(string $kind): string
    {
        $kind = strtolower(trim($kind));
        return match ($kind) {
            'gmax_cannonade', 'gmax_vine_lash', 'reflect', 'light_screen' => $kind,
            default => '',
        };
    }

    private function volatileKey(string $battlePokemon, string $kind): string
    {
        return 'v:' . substr(sha1($battlePokemon), 0, 16) . ':' . $kind;
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
