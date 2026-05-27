<?php
declare(strict_types=1);

namespace Pokemon8\Repository;

use Pokemon8\Game\BattleTransformationCatalog;
use Pokemon8\Game\PokemonFormCatalog;
use PDO;

final class BattleRepository
{
    private PokemonEvolutionRepository $evolutions;

    public function __construct(private PDO $db, ?PokemonEvolutionRepository $evolutions = null)
    {
        $this->evolutions = $evolutions ?? new PokemonEvolutionRepository($db);
    }

    public function findActivePveBattleIdForUser(int $userId): int
    {
        // Active fight: user.pve = 1. Finished-but-not-acked fight: pve = 0,
        // battleid still points to battles.pobeda != 0 so the client can show final log.
        $stmt = $this->db->prepare('SELECT battleid, pve, pvp FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch();
        if (!$user) {
            return 0;
        }
        if ((int) ($user['pvp'] ?? 0) === 1) {
            return 0;
        }

        $battleId = (int) ($user['battleid'] ?? 0);
        if ($battleId <= 0) {
            return 0;
        }
        if ((int) ($user['pve'] ?? 0) === 1) {
            $battle = $this->db->prepare(
                'SELECT id FROM battles WHERE id = :id AND user_1 = :user AND batl_tip = "pve" AND pobeda = 0 LIMIT 1'
            );
            $battle->execute(['id' => $battleId, 'user' => $userId]);
            return $battle->fetchColumn() !== false ? $battleId : 0;
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
        $stmt = $this->db->prepare('SELECT battleid, pve, pvp FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch();
        if (!$user) {
            return 0;
        }
        if ((int) ($user['pve'] ?? 0) === 1) {
            return 0;
        }

        $battleId = (int) ($user['battleid'] ?? 0);
        if ($battleId <= 0) {
            return 0;
        }

        if ((int) ($user['pvp'] ?? 0) === 1) {
            $battle = $this->db->prepare(
                'SELECT id FROM battles WHERE id = :id AND (user_1 = :user_1 OR user_2 = :user_2) AND batl_tip = "pvp" AND pobeda = 0 LIMIT 1'
            );
            $battle->execute(['id' => $battleId, 'user_1' => $userId, 'user_2' => $userId]);
            return $battle->fetchColumn() !== false ? $battleId : 0;
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
        if ($attackerState === 'bad' && $targetState === 'good') {
            return [
                'allowed' => true,
                'rule' => 'criminal_attacks_protector',
                'requiresWarrant' => false,
                'message' => 'Преступник может нападать на защитников без ордера, кроме закрытых административных локаций.',
            ] + $base;
        }

        $warrant = $this->bestAvailableWarrant($attackerId);
        if ($warrant === null) {
            return $base + ['allowed' => false, 'message' => 'Для принудительного нападения нужен ордер Команды R. Добровольный бой через запрос ордер не требует.'];
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
                : 'Ордер Команды R позволяет принудительно напасть почти в любой локации. За нападение на нейтрального игрока карма снизится.',
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
        $this->expirePvpRequests();

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
                AND (expires_at = 0 OR expires_at > :now)
                AND ((from_user_id = :current_a AND to_user_id = :target_a)
                  OR (from_user_id = :target_b AND to_user_id = :current_b))
              ORDER BY id DESC
              LIMIT 1'
        );
        $stmt->execute([
            'now' => time(),
            'current_a' => $currentUserId,
            'target_a' => $targetUserId,
            'target_b' => $targetUserId,
            'current_b' => $currentUserId,
        ]);
        $row = $stmt->fetch();
        if (!$row) {
            return 'none';
        }

        return (int) ($row['to_user_id'] ?? 0) === $currentUserId ? 'incoming' : 'outgoing';
    }

    /** @return list<array{id:int,fromUserId:int,login:string,createdAt:int}> */
    public function incomingPvpRequests(int $userId): array
    {
        $this->expirePvpRequests();

        $stmt = $this->db->prepare(
            'SELECT pr.id, pr.from_user_id, pr.created_at, pr.expires_at, u.login
               FROM pvp_requests pr
               LEFT JOIN users u ON u.id = pr.from_user_id
              WHERE pr.to_user_id = :user
                AND pr.status = "pending"
                AND (pr.expires_at = 0 OR pr.expires_at > :now)
              ORDER BY pr.id DESC
              LIMIT 20'
        );
        $stmt->execute(['user' => $userId, 'now' => time()]);

        $result = [];
        foreach ($stmt->fetchAll() ?: [] as $row) {
            $result[] = [
                'id' => (int) ($row['id'] ?? 0),
                'fromUserId' => (int) ($row['from_user_id'] ?? 0),
                'login' => (string) ($row['login'] ?? ('Игрок #' . (int) ($row['from_user_id'] ?? 0))),
                'createdAt' => (int) ($row['created_at'] ?? 0),
                'expiresAt' => (int) ($row['expires_at'] ?? 0),
            ];
        }
        return $result;
    }

    public function requestOrAcceptPvp(int $fromUserId, int $toUserId, int $fromPokemonId = 0): array
    {
        $this->expirePvpRequests();

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
              WHERE from_user_id = :target
                AND to_user_id = :current
                AND status = "pending"
                AND (expires_at = 0 OR expires_at > :now)
              ORDER BY id DESC
              LIMIT 1'
        );
        $incoming->execute(['target' => $toUserId, 'current' => $fromUserId, 'now' => time()]);
        $incomingId = (int) ($incoming->fetchColumn() ?: 0);
        if ($incomingId > 0) {
            return $this->acceptPvpRequest($fromUserId, $incomingId, $fromPokemonId);
        }

        if (!$this->usersCanStartPvp($fromUserId, $toUserId)) {
            return ['ok' => false, 'message' => 'Один из игроков уже занят или у него нет живого активного покемона.'];
        }

        $now = time();
        $existing = $this->db->prepare(
            'SELECT id
               FROM pvp_requests
              WHERE from_user_id = :from
                AND to_user_id = :to
                AND status = "pending"
                AND (expires_at = 0 OR expires_at > :now)
              ORDER BY id DESC
              LIMIT 1'
        );
        $existing->execute(['from' => $fromUserId, 'to' => $toUserId, 'now' => $now]);
        $existingId = (int) ($existing->fetchColumn() ?: 0);
        if ($existingId > 0) {
            $this->db->prepare(
                'UPDATE pvp_requests
                    SET from_pokemon_id = :pokemon, expires_at = :expires_at, updated_at = :now
                  WHERE id = :id
                  LIMIT 1'
            )->execute(['pokemon' => $fromPokemonId, 'expires_at' => $now + 120, 'now' => $now, 'id' => $existingId]);

            return ['ok' => true, 'status' => 'outgoing', 'expiresAt' => $now + 120, 'message' => 'Вызов на бой уже отправлен.'];
        }

        $this->db->prepare(
            'INSERT INTO pvp_requests (from_user_id, to_user_id, from_pokemon_id, to_pokemon_id, status, battle_id, expires_at, responded_at, created_at, updated_at)
             VALUES (:from, :to, :pokemon, 0, "pending", 0, :expires_at, 0, :created_at, :updated_at)'
        )->execute([
            'from' => $fromUserId,
            'to' => $toUserId,
            'pokemon' => $fromPokemonId,
            'expires_at' => $now + 120,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return ['ok' => true, 'status' => 'outgoing', 'expiresAt' => $now + 120, 'message' => 'Вызов на бой отправлен.'];
    }

    public function forcePvpAttack(int $attackerId, int $targetId, int $attackerPokemonId = 0): array
    {
        if ($attackerId <= 0 || $targetId <= 0 || $attackerId === $targetId) {
            return ['ok' => false, 'message' => 'Нельзя напасть на этого игрока.'];
        }

        $permission = $this->pvpAttackPermission($attackerId, $targetId);
        if (empty($permission['allowed'])) {
            return [
                'ok' => false,
                'message' => (string) ($permission['message'] ?? 'Принудительное нападение сейчас недоступно.'),
                'permission' => $permission,
            ];
        }

        if (!$this->usersCanStartPvp($attackerId, $targetId)) {
            return [
                'ok' => false,
                'message' => 'Бой нельзя начать: один из игроков уже занят или у него нет живого активного покемона.',
                'permission' => $permission,
            ];
        }

        $attackerPokemon = $this->findChosenBattlePokemonForUser($attackerId, $attackerPokemonId);
        $targetPokemon = $this->findFirstBattlePokemonForUser($targetId);
        if ($attackerPokemon === null) {
            return [
                'ok' => false,
                'message' => 'Выберите живого покемона из активной команды.',
                'permission' => $permission,
            ];
        }
        if ($targetPokemon === null) {
            return [
                'ok' => false,
                'message' => 'У цели нет живого активного покемона.',
                'permission' => $permission,
            ];
        }

        $this->db->beginTransaction();
        try {
            $battleId = $this->createPvpBattle(
                $attackerId,
                $targetId,
                (int) ($attackerPokemon['id'] ?? 0),
                (int) ($targetPokemon['id'] ?? 0)
            );
            $this->applyPvpStartKarma($attackerId, $targetId, $battleId, $permission);
            $this->expirePendingPvpRequestsForUsers($attackerId, $targetId);
            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['ok' => false, 'message' => 'Не удалось начать принудительный бой.'];
        }

        return [
            'ok' => true,
            'status' => 'active',
            'battleId' => $battleId,
            'permission' => $permission,
            'message' => !empty($permission['requiresWarrant'])
                ? 'Ордер использован. Принудительный PvP бой начался.'
                : 'Принудительный PvP бой начался.',
        ];
    }

    public function acceptPvpRequest(int $userId, int $requestId, int $toPokemonId = 0): array
    {
        $this->expirePvpRequests();

        $stmt = $this->db->prepare(
            'SELECT id, from_user_id, to_user_id, from_pokemon_id, to_pokemon_id, status, battle_id, expires_at
               FROM pvp_requests
              WHERE id = :id AND to_user_id = :user
              LIMIT 1'
        );
        $stmt->execute(['id' => $requestId, 'user' => $userId]);
        $request = $stmt->fetch();
        if (!$request) {
            return ['ok' => false, 'message' => 'Заявка на бой не найдена.'];
        }
        if ((string) ($request['status'] ?? '') === 'accepted' && (int) ($request['battle_id'] ?? 0) > 0) {
            return [
                'ok' => true,
                'status' => 'active',
                'battleId' => (int) $request['battle_id'],
                'message' => 'PvP бой уже начался.',
            ];
        }
        if ((string) ($request['status'] ?? '') !== 'pending') {
            return ['ok' => false, 'status' => (string) ($request['status'] ?? 'unknown'), 'message' => 'Эта заявка уже не активна.'];
        }
        if ((int) ($request['expires_at'] ?? 0) > 0 && (int) $request['expires_at'] <= time()) {
            $this->expirePvpRequests();
            return ['ok' => false, 'status' => 'expired', 'message' => 'Вызов на бой истек.'];
        }

        $fromUserId = (int) ($request['from_user_id'] ?? 0);
        $toUserId = (int) ($request['to_user_id'] ?? 0);
        $fromPokemonId = (int) ($request['from_pokemon_id'] ?? 0);
        if (!$this->usersCanStartPvp($fromUserId, $toUserId)) {
            return ['ok' => false, 'message' => 'Бой нельзя начать: один из игроков уже занят.'];
        }

        $firstPokemon = $this->findChosenBattlePokemonForUser($fromUserId, $fromPokemonId)
            ?? $this->findFirstBattlePokemonForUser($fromUserId);
        $secondPokemon = $this->findChosenBattlePokemonForUser($toUserId, $toPokemonId);
        if ($firstPokemon === null || $secondPokemon === null) {
            return ['ok' => false, 'message' => 'Выберите живого покемона из активной команды.'];
        }

        $now = time();
        $this->db->beginTransaction();
        try {
            $locked = $this->db->prepare(
                'SELECT status, battle_id
                   FROM pvp_requests
                  WHERE id = :id AND to_user_id = :user
                  FOR UPDATE'
            );
            $locked->execute(['id' => $requestId, 'user' => $userId]);
            $lockedRow = $locked->fetch();
            if ($lockedRow && (string) ($lockedRow['status'] ?? '') === 'accepted' && (int) ($lockedRow['battle_id'] ?? 0) > 0) {
                $battleId = (int) $lockedRow['battle_id'];
            } else {
                if (!$lockedRow || (string) ($lockedRow['status'] ?? '') !== 'pending') {
                    $this->db->rollBack();
                    return ['ok' => false, 'message' => 'Эта заявка уже не активна.'];
                }
                $battleId = $this->createPvpBattle($fromUserId, $toUserId, (int) $firstPokemon['id'], (int) $secondPokemon['id']);
                $this->db->prepare(
                    'UPDATE pvp_requests
                        SET status = "accepted", battle_id = :battle, to_pokemon_id = :pokemon, responded_at = :responded_at, updated_at = :now
                      WHERE id = :id
                      LIMIT 1'
                )->execute(['battle' => $battleId, 'pokemon' => (int) $secondPokemon['id'], 'responded_at' => $now, 'now' => $now, 'id' => $requestId]);
                $this->expirePendingPvpRequestsForUsers($fromUserId, $toUserId);
            }
            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['ok' => false, 'message' => 'Не удалось принять вызов.'];
        }

        return [
            'ok' => true,
            'status' => 'active',
            'battleId' => $battleId,
            'message' => 'PvP бой начался.',
        ];
    }

    public function declinePvpRequest(int $userId, int $requestId): bool
    {
        $this->expirePvpRequests();

        $stmt = $this->db->prepare(
            'UPDATE pvp_requests
                SET status = "declined", responded_at = :responded_at, updated_at = :updated_at
              WHERE id = :id AND to_user_id = :user AND status = "pending"
                AND (expires_at = 0 OR expires_at > :now_check)
              LIMIT 1'
        );
        $now = time();
        $stmt->execute([
            'responded_at' => $now,
            'updated_at' => $now,
            'now_check' => $now,
            'id' => $requestId,
            'user' => $userId,
        ]);
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

    public function findPokemon(string $battlePokemon, int $battleId = 0): ?array
    {
        $parsed = $this->parseBattlePokemon($battlePokemon);
        if ($parsed === null) {
            return null;
        }

        $table = $parsed['table'];
        $heldSelect = ', NULL AS held_item_id, NULL AS held_item_name, NULL AS held_item_title, NULL AS held_item_meta';
        $heldJoin = '';
        if ($table === 'pok_user') {
            $heldSelect = ', COALESCE(ip.id_items, bp.item, 0) AS held_item_id, held.name AS held_item_name, held.tittle AS held_item_title, held.dopolnen AS held_item_meta';
            $heldJoin = ' LEFT JOIN items_poke ip ON ip.id_poke = bp.id
                          AND (ip.datetime = "not" OR ip.datetime = "" OR ip.datetime IS NULL
                            OR (ip.datetime REGEXP "^[0-9]+$" AND CAST(ip.datetime AS UNSIGNED) > UNIX_TIMESTAMP()))
                         LEFT JOIN items held ON held.id = COALESCE(ip.id_items, bp.item, 0)';
        } elseif ($table === 'pok_pve') {
            $heldSelect = ', bp.sprz AS held_item_id, held.name AS held_item_name, held.tittle AS held_item_title, held.dopolnen AS held_item_meta';
            $heldJoin = ' LEFT JOIN items held ON held.id = bp.sprz';
        }
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
        $battleId = $battleId > 0 ? $battleId : $this->findActiveBattleIdForBattlePokemon($battlePokemon);
        if ($battleId > 0) {
            $this->activateBattleTransformationIfEligible($battleId, $battlePokemon, $row);
            $this->applyBattleTransformation($battleId, $battlePokemon, $row);
        }
        return $row;
    }

    private function findActiveBattleIdForBattlePokemon(string $battlePokemon): int
    {
        if ($battlePokemon === '') {
            return 0;
        }

        $stmt = $this->db->prepare(
            'SELECT id
               FROM battles
              WHERE pobeda = 0
                AND (poke_1 = :pokemon_a OR poke_2 = :pokemon_b)
              ORDER BY id DESC
              LIMIT 1'
        );
        $stmt->execute(['pokemon_a' => $battlePokemon, 'pokemon_b' => $battlePokemon]);
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    private function activateBattleTransformationIfEligible(int $battleId, string $battlePokemon, array $pokemon): void
    {
        if ($battleId <= 0 || $battlePokemon === '' || !str_starts_with($battlePokemon, 'pvp_')) {
            return;
        }
        if (!$this->tableExists('battle_transformations')) {
            return;
        }
        if ($this->findBattleTransformation($battleId, $battlePokemon) !== null || $this->battleHasTransformation($battleId)) {
            return;
        }

        $baseId = (int) ($pokemon['basenum'] ?? 0);
        if ($baseId <= 0 || PokemonFormCatalog::isForm($baseId)) {
            return;
        }

        $pokemonId = (int) ($pokemon['id'] ?? 0);
        $rule = BattleTransformationCatalog::matchRule(
            $baseId,
            (int) ($pokemon['held_item_id'] ?? 0),
            (string) (($pokemon['held_item_name'] ?? '') . ' ' . ($pokemon['held_item_title'] ?? '')),
            $this->pokemonKnowsMove($pokemonId, 'Dragon Ascent')
        );
        if ($rule === null || !$this->transformationUnlocked((int) ($pokemon['users'] ?? 0), $baseId, (int) $rule['formId'])) {
            return;
        }

        $round = $this->battleRound($battleId);
        $now = time();
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO battle_transformations
                    (battle_id, battle_pokemon, user_pokemon_id, original_base_id, form_base_id,
                     transformation_type, required_item_id, source_key, active, activated_round,
                     reverted_at, created_at, updated_at)
                 VALUES
                    (:battle, :battle_pokemon, :pokemon, :original, :form,
                     :type, :item, :source, 1, :round, 0, :created, :updated)
                 ON DUPLICATE KEY UPDATE
                    active = 1,
                    form_base_id = VALUES(form_base_id),
                    transformation_type = VALUES(transformation_type),
                    required_item_id = VALUES(required_item_id),
                    source_key = VALUES(source_key),
                    updated_at = VALUES(updated_at)'
            );
            $stmt->execute([
                'battle' => $battleId,
                'battle_pokemon' => $battlePokemon,
                'pokemon' => $pokemonId,
                'original' => $baseId,
                'form' => (int) $rule['formId'],
                'type' => (string) $rule['type'],
                'item' => (int) $rule['itemId'],
                'source' => (string) ($rule['itemKey'] ?: $rule['requiresMove']),
                'round' => $round,
                'created' => $now,
                'updated' => $now,
            ]);
            $this->insertBattleLog($battleId, $round, BattleTransformationCatalog::transformationMessage($rule, (string) ($pokemon['names'] ?? 'Покемон')));
            $this->insertBattleLog(
                $battleId,
                $round,
                sprintf(
                    '[TRANSFORM] %s -> %s by %s.',
                    trim(strip_tags((string) ($pokemon['names'] ?? 'Pokemon'))) ?: 'Pokemon',
                    (string) $rule['label'],
                    (string) ($rule['itemKey'] !== '' ? $rule['itemKey'] : $rule['requiresMove'])
                )
            );
        } catch (\Throwable) {
            // Трансформация не должна ломать бой целиком.
        }
    }

    private function applyBattleTransformation(int $battleId, string $battlePokemon, array &$pokemon): void
    {
        if (!$this->tableExists('battle_transformations')) {
            return;
        }

        $row = $this->findBattleTransformation($battleId, $battlePokemon);
        if ($row === null) {
            return;
        }

        $formId = (int) ($row['form_base_id'] ?? 0);
        $originalBaseId = (int) ($row['original_base_id'] ?? ($pokemon['basenum'] ?? 0));
        if ($formId <= 0 || $originalBaseId <= 0) {
            return;
        }

        $form = $this->findBasePokemonRow($formId);
        $original = $this->findBasePokemonRow($originalBaseId);
        if ($form === null) {
            return;
        }

        $pokemon['original_basenum'] = $originalBaseId;
        $pokemon['battle_form_id'] = $formId;
        $pokemon['battle_form_type'] = (string) ($row['transformation_type'] ?? '');
        $pokemon['battle_form_key'] = PokemonFormCatalog::formKey($formId, (string) ($form['Name'] ?? $form['title'] ?? ''));
        $pokemon['battle_form_label'] = (string) ($form['Name'] ?? $form['title'] ?? ('Form #' . $formId));
        $pokemon['original_name'] = (string) ($pokemon['names'] ?? '');
        $pokemon['basenum'] = $formId;
        $pokemon['names'] = $pokemon['battle_form_label'];
        $pokemon['dex_name'] = $pokemon['battle_form_label'];
        $pokemon['Element'] = (string) ($form['Element'] ?? $pokemon['Element'] ?? '');
        $pokemon['SubElement'] = (string) ($form['SubElement'] ?? $pokemon['SubElement'] ?? '');
        $pokemon['base_ability_key'] = (string) ($form['ability_key'] ?? $pokemon['base_ability_key'] ?? '');
        $pokemon['ability_key'] = (string) ($form['ability_key'] ?? $pokemon['ability_key'] ?? '');

        if ($original !== null) {
            $this->scaleTransformedStats($pokemon, $original, $form);
        }
    }

    private function findBattleTransformation(int $battleId, string $battlePokemon): ?array
    {
        if ($battleId <= 0 || $battlePokemon === '' || !$this->tableExists('battle_transformations')) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT *
               FROM battle_transformations
              WHERE battle_id = :battle AND battle_pokemon = :pokemon AND active = 1
              LIMIT 1'
        );
        $stmt->execute(['battle' => $battleId, 'pokemon' => $battlePokemon]);
        return $stmt->fetch() ?: null;
    }

    private function battleHasTransformation(int $battleId): bool
    {
        if ($battleId <= 0 || !$this->tableExists('battle_transformations')) {
            return false;
        }

        $stmt = $this->db->prepare('SELECT 1 FROM battle_transformations WHERE battle_id = :battle AND active = 1 LIMIT 1');
        $stmt->execute(['battle' => $battleId]);
        return $stmt->fetchColumn() !== false;
    }

    private function battleRound(int $battleId): int
    {
        $stmt = $this->db->prepare('SELECT raund FROM battles WHERE id = :battle LIMIT 1');
        $stmt->execute(['battle' => $battleId]);
        return max(1, (int) ($stmt->fetchColumn() ?: 1));
    }

    private function transformationUnlocked(int $userId, int $baseId, int $formId): bool
    {
        if ($userId <= 0 || $formId <= 0 || !$this->tableExists('pokemon_transformation_unlocks')) {
            return true;
        }

        $stmt = $this->db->prepare(
            'SELECT unlocked
               FROM pokemon_transformation_unlocks
              WHERE user_id = :user AND base_id = :base AND form_id = :form
              LIMIT 1'
        );
        $stmt->execute(['user' => $userId, 'base' => $baseId, 'form' => $formId]);
        $value = $stmt->fetchColumn();
        return $value === false || (int) $value === 1;
    }

    private function pokemonKnowsMove(int $pokemonId, string $moveName): bool
    {
        if ($pokemonId <= 0 || trim($moveName) === '') {
            return false;
        }

        $stmt = $this->db->prepare(
            'SELECT 1
               FROM attac_my_poke amp
         INNER JOIN attac_power ap ON ap.atac_id IN (amp.a_id, amp.b_id, amp.c_id, amp.d_id)
              WHERE amp.pok_id = :pokemon
                AND LOWER(REPLACE(REPLACE(REPLACE(ap.atac_name, " ", ""), "-", ""), "_", "")) = :move
              LIMIT 1'
        );
        $stmt->execute([
            'pokemon' => $pokemonId,
            'move' => strtolower(str_replace([' ', '-', '_'], '', $moveName)),
        ]);
        return $stmt->fetchColumn() !== false;
    }

    private function findBasePokemonRow(int $baseId): ?array
    {
        if ($baseId <= 0) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT p.id, p.Name, p.Element, p.SubElement, p.HP, p.Attack, p.Defense, p.spatk, p.spdef, p.Speed,
                    pb.title, pb.hp AS base_hp, pb.atk AS base_atk, pb.def AS base_def,
                    pb.satk AS base_satk, pb.sdef AS base_sdef, pb.speed AS base_speed, pb.ability_key
               FROM pokemon p
          LEFT JOIN poke_base pb ON pb.id = p.id
              WHERE p.id = :id
              LIMIT 1'
        );
        $stmt->execute(['id' => $baseId]);
        return $stmt->fetch() ?: null;
    }

    private function scaleTransformedStats(array &$pokemon, array $original, array $form): void
    {
        $map = [
            'atk' => ['old' => 'Attack', 'new' => 'Attack'],
            'def' => ['old' => 'Defense', 'new' => 'Defense'],
            'satk' => ['old' => 'spatk', 'new' => 'spatk'],
            'sdef' => ['old' => 'spdef', 'new' => 'spdef'],
            'speed' => ['old' => 'Speed', 'new' => 'Speed'],
        ];

        foreach ($map as $field => $keys) {
            $oldBase = max(1, (int) ($original[$keys['old']] ?? $original['base_' . $field] ?? 1));
            $newBase = max(1, (int) ($form[$keys['new']] ?? $form['base_' . $field] ?? $oldBase));
            $current = max(1, (int) ($pokemon[$field] ?? 1));
            $pokemon[$field] = max(1, (int) round($current * ($newBase / $oldBase)));
        }

        $oldHpBase = max(1, (int) ($original['HP'] ?? $original['base_hp'] ?? 1));
        $newHpBase = max(1, (int) ($form['HP'] ?? $form['base_hp'] ?? $oldHpBase));
        if ($newHpBase !== $oldHpBase) {
            $oldMax = max(1, (int) ($pokemon['hp_max'] ?? 1));
            $newMax = max(1, (int) round($oldMax * ($newHpBase / $oldHpBase)));
            $ratio = max(0.0, min(1.0, ((int) ($pokemon['hp_my'] ?? 0)) / $oldMax));
            $pokemon['hp_max'] = $newMax;
            $pokemon['hp_my'] = max(0, (int) round($newMax * $ratio));
        }
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
            'SELECT pu.id, pu.names, pu.hp_my, pu.hp_max, pu.active, pu.basenum, pu.lvl, pu.sex, pu.tips,
                    COALESCE(ip.id_items, pu.item, 0) AS held_item_id,
                    held.name AS held_item_name,
                    held.tittle AS held_item_title,
                    held.dopolnen AS held_item_meta
               FROM pok_user pu
          LEFT JOIN items_poke ip ON ip.id_poke = pu.id
          LEFT JOIN items held ON held.id = COALESCE(ip.id_items, pu.item, 0)
              WHERE pu.users = :user AND pu.active = 1 AND pu.hp_my > 0
              ORDER BY (pu.hp_my > 0) DESC, pu.startepoke DESC, pu.id ASC'
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

    public function restorePokemonMovePp(int $pokemonId): void
    {
        if ($pokemonId <= 0) {
            return;
        }

        $this->db->prepare(
            'UPDATE attac_my_poke
                SET a_pp_min = a_pp_max,
                    b_pp_min = b_pp_max,
                    c_pp_min = c_pp_max,
                    d_pp_min = d_pp_max
              WHERE pok_id = :pokemon
              LIMIT 1'
        )->execute(['pokemon' => $pokemonId]);
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

    public function initializeBattlePokemonStats(int $battleId, string $battlePokemon): void
    {
        if ($battleId <= 0 || $battlePokemon === '') {
            return;
        }

        foreach (['plus', 'minus'] as $tip) {
            try {
                $stmt = $this->db->prepare(
                    'INSERT INTO statpokemonbatle
                        (battleid, pokeid, attac, spattac, defend, spdefend, speed, acc, accuracy, tip, raundends)
                     VALUES
                        (:battleid, :pokeid, 0, 0, 0, 0, 0, 0, 0, :tip, 0)'
                );
                $stmt->execute([
                    'battleid' => $battleId,
                    'pokeid' => $battlePokemon,
                    'tip' => $tip,
                ]);
                continue;
            } catch (\Throwable) {
                // Legacy dumps may have statpokemonbatle.id without AUTO_INCREMENT.
            }

            try {
                $nextId = (int) ($this->db->query('SELECT COALESCE(MAX(id), 0) + 1 FROM statpokemonbatle')->fetchColumn() ?: 1);
                $stmt = $this->db->prepare(
                    'INSERT INTO statpokemonbatle
                        (id, battleid, pokeid, attac, spattac, defend, spdefend, speed, acc, accuracy, tip, raundends)
                     VALUES
                        (:id, :battleid, :pokeid, 0, 0, 0, 0, 0, 0, 0, :tip, 0)'
                );
                $stmt->execute([
                    'id' => $nextId,
                    'battleid' => $battleId,
                    'pokeid' => $battlePokemon,
                    'tip' => $tip,
                ]);
            } catch (\Throwable $e) {
                error_log('[BOSS] cannot initialize statpokemonbatle: ' . $e->getMessage());
            }
        }
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

    /** @return list<array<string,mixed>> */
    public function battleHistoryForUser(int $userId, int $limit = 30): array
    {
        if ($userId <= 0) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT b.id, b.user_1, b.user_2, b.batl_tip, b.pobeda, b.raund, b.times,
                    u1.login AS user_1_login, u2.login AS user_2_login
               FROM battles b
               LEFT JOIN users u1 ON u1.id = b.user_1
               LEFT JOIN users u2 ON u2.id = b.user_2
              WHERE (b.user_1 = :user_a OR b.user_2 = :user_b)
                AND b.pobeda <> 0
              ORDER BY b.id DESC
              LIMIT :limit'
        );
        $stmt->bindValue(':user_a', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':user_b', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', max(1, min(100, $limit)), PDO::PARAM_INT);
        $stmt->execute();

        $rows = [];
        foreach ($stmt->fetchAll() ?: [] as $row) {
            $battleId = (int) ($row['id'] ?? 0);
            $opponentId = (int) ($row['user_1'] ?? 0) === $userId ? (int) ($row['user_2'] ?? 0) : (int) ($row['user_1'] ?? 0);
            $opponentLogin = (int) ($row['user_1'] ?? 0) === $userId
                ? (string) ($row['user_2_login'] ?? '')
                : (string) ($row['user_1_login'] ?? '');
            $winner = (int) ($row['pobeda'] ?? 0);
            $rows[] = [
                'id' => $battleId,
                'mode' => (string) ($row['batl_tip'] ?? ''),
                'round' => (int) ($row['raund'] ?? 0),
                'finishedAt' => (int) ($row['times'] ?? 0),
                'winnerId' => $winner,
                'result' => $winner === $userId ? 'win' : ($winner === -2 ? 'escape' : 'lose'),
                'opponent' => [
                    'id' => $opponentId,
                    'login' => $opponentLogin !== '' ? $opponentLogin : ($opponentId > 0 ? ('Игрок #' . $opponentId) : 'Дикий покемон'),
                ],
                'log' => $this->getBattleLog($battleId, 80),
            ];
        }

        foreach ($this->archivedBattleHistoryForUser($userId, $limit) as $row) {
            $rows[] = $row;
        }

        usort($rows, static fn (array $a, array $b): int => ((int) ($b['finishedAt'] ?? 0) <=> (int) ($a['finishedAt'] ?? 0)) ?: ((int) ($b['id'] ?? 0) <=> (int) ($a['id'] ?? 0)));
        return array_slice($rows, 0, max(1, min(100, $limit)));
    }

    /** @return list<array<string,mixed>> */
    private function archivedBattleHistoryForUser(int $userId, int $limit): array
    {
        if (!$this->tableExists('battle_history_archive')) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT battle_id, opponent_id, mode, result, winner_id, rounds, started_at, finished_at, log_json
               FROM battle_history_archive
              WHERE user_id = :user
              ORDER BY finished_at DESC, battle_id DESC
              LIMIT :limit'
        );
        $stmt->bindValue(':user', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', max(1, min(100, $limit)), PDO::PARAM_INT);
        $stmt->execute();

        $rows = [];
        foreach ($stmt->fetchAll() ?: [] as $row) {
            $log = json_decode((string) ($row['log_json'] ?? '[]'), true);
            $rows[] = [
                'id' => (int) ($row['battle_id'] ?? 0),
                'mode' => (string) ($row['mode'] ?? 'pve'),
                'round' => (int) ($row['rounds'] ?? 0),
                'finishedAt' => (int) ($row['finished_at'] ?? 0),
                'winnerId' => (int) ($row['winner_id'] ?? 0),
                'result' => (string) ($row['result'] ?? ''),
                'opponent' => [
                    'id' => (int) ($row['opponent_id'] ?? 0),
                    'login' => ((int) ($row['opponent_id'] ?? 0)) > 0 ? ('Игрок #' . (int) ($row['opponent_id'] ?? 0)) : 'Дикий покемон',
                ],
                'log' => is_array($log) ? $log : [],
            ];
        }

        return $rows;
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
        $this->addItemReward($userId, 1, $coins);
    }

    public function rollAdminDropRewards(int $userId, array $enemy, float $chanceMultiplier = 1.0): array
    {
        if ($userId <= 0 || (int) ($enemy['basenum'] ?? 0) <= 0) {
            return [];
        }

        $locationId = $this->currentLocationId($userId);
        $baseId = (int) ($enemy['basenum'] ?? 0);
        $now = date('H:i:s');
        $stmt = $this->db->prepare(
            'SELECT r.*, i.name AS item_name
               FROM admin_drop_rules r
               INNER JOIN items i ON i.id = r.item_id
          LEFT JOIN pokebuild pb ON pb.id = r.pokebuild_id
              WHERE r.enabled = 1
                AND r.source_type = "wild"
                AND (r.location_id = 0 OR r.location_id = :location)
                AND (r.pokemon_base_id = 0 OR r.pokemon_base_id = :base)
                AND (r.pokebuild_id = 0 OR (pb.building = :location_pb AND pb.baseid = :base_pb))
                AND (
                    (r.time_start <= r.time_end AND :now_a BETWEEN r.time_start AND r.time_end)
                    OR
                    (r.time_start > r.time_end AND (:now_b >= r.time_start OR :now_c <= r.time_end))
                )
              ORDER BY r.id ASC'
        );
        $stmt->execute([
            'location' => $locationId,
            'base' => $baseId,
            'location_pb' => $locationId,
            'base_pb' => $baseId,
            'now_a' => $now,
            'now_b' => $now,
            'now_c' => $now,
        ]);

        $drops = [];
        foreach ($stmt->fetchAll() ?: [] as $row) {
            $chance = (float) ($row['chance_percent'] ?? 0) * max(1.0, $chanceMultiplier);
            if ($chance <= 0) {
                continue;
            }
            if (random_int(1, 1000000) > (int) round(min(100.0, $chance) * 10000)) {
                continue;
            }

            $min = max(1, (int) ($row['min_count'] ?? 1));
            $max = max($min, (int) ($row['max_count'] ?? $min));
            $count = random_int($min, $max);
            $itemId = (int) ($row['item_id'] ?? 0);
            $this->addItemReward($userId, $itemId, $count);
            $drops[] = [
                'itemId' => $itemId,
                'name' => (string) ($row['item_name'] ?? ('Предмет #' . $itemId)),
                'count' => $count,
                'ruleId' => (int) ($row['id'] ?? 0),
            ];
        }

        return $drops;
    }

    private function addItemReward(int $userId, int $itemId, int $count): void
    {
        if ($userId <= 0 || $itemId <= 0 || $count <= 0) {
            return;
        }

        $this->withItemsUsersLock(function () use ($userId, $itemId, $count): void {
            $existing = $this->db->prepare('SELECT id FROM items_users WHERE user_id = :user AND item_id = :item LIMIT 1');
            $existing->execute(['user' => $userId, 'item' => $itemId]);
            $rowId = (int) ($existing->fetchColumn() ?: 0);
            if ($rowId > 0) {
                $stmt = $this->db->prepare('UPDATE items_users SET count = count + :count WHERE id = :id LIMIT 1');
                $stmt->execute(['count' => $count, 'id' => $rowId]);
                return;
            }

            $stmt = $this->db->prepare(
                'INSERT INTO items_users (id, item_id, user_id, count, dattimer, timers)
                 VALUES (:id, :item, :user, :count, :dattimer, :timers)'
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

    private function currentLocationId(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT buildmy FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $userId]);
        return (int) ($stmt->fetchColumn() ?: 0);
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
            return ['exp' => 0, 'ev' => 0, 'levelUps' => 0, 'level' => 0, 'evolution' => null];
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

        $evolution = $levelUps > 0 ? $this->evolutions->applyLevelEvolution($userId, $pokemonId) : null;

        return ['exp' => max(0, $exp), 'ev' => $evGain, 'levelUps' => $levelUps, 'level' => $level, 'evolution' => $evolution];
    }

    public function addPokemonHappiness(int $userId, int $pokemonId, int $amount): int
    {
        if ($userId <= 0 || $pokemonId <= 0 || $amount <= 0) {
            return 0;
        }

        $stmt = $this->db->prepare(
            'UPDATE pok_user
                SET happy = LEAST(100, GREATEST(0, happy + :amount))
              WHERE id = :pokemon AND users = :user AND active = 1
              LIMIT 1'
        );
        $stmt->execute([
            'amount' => $amount,
            'pokemon' => $pokemonId,
            'user' => $userId,
        ]);

        return $stmt->rowCount() > 0 ? $amount : 0;
    }

    public function findBattleInventoryItem(int $userId, int $itemUserId): ?array
    {
        if ($userId <= 0 || $itemUserId <= 0) {
            return null;
        }

        $ballCondition = '(iu.item_id IN (3, 90004, 90005)
            OR i.name REGEXP "поке.?бол|мастер.?бол|ультра.?бол|премиум.?бол|грит.?бол|ball|шар"
            OR i.name LIKE "%ball%"
            OR i.tittle REGEXP "поке.?бол|мастер.?бол|ультра.?бол|премиум.?бол|грит.?бол|ball|шар"
            OR i.tittle LIKE "%ball%"
            OR i.tittle LIKE "%шар%")';
        $stmt = $this->db->prepare(
            'SELECT iu.id, iu.item_id, iu.count, i.name, i.tittle, i.category, i.uses, i.elementary, i.battleuse, i.dopolnen
               FROM items_users iu
               INNER JOIN items i ON i.id = iu.item_id
              WHERE iu.id = :id
                AND iu.user_id = :user
                AND iu.count > 0
                AND (i.battleuse = 1 OR ' . $ballCondition . ')
                AND (iu.dattimer = "not" OR (iu.dattimer REGEXP "^[0-9]+$" AND CAST(iu.dattimer AS UNSIGNED) > :time))
              LIMIT 1'
        );
        $stmt->execute([
            'id' => $itemUserId,
            'user' => $userId,
            'time' => time(),
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

    public function clearBattleVolatile(int $battleId, string $battlePokemon, string $kind): int
    {
        $kind = $this->normalizeVolatileKind($kind);
        if ($battleId <= 0 || $battlePokemon === '' || $kind === '') {
            return 0;
        }

        $stmt = $this->db->prepare(
            'DELETE FROM battle_dop
              WHERE battleid = :battle
                AND pokeid = :poke
                AND propusk = :battle_pokemon
                AND mess = :kind'
        );
        $stmt->execute([
            'battle' => $battleId,
            'poke' => $this->volatileKey($battlePokemon, $kind),
            'battle_pokemon' => $battlePokemon,
            'kind' => $kind,
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
            'UPDATE battles
                SET pobeda = :winner
              WHERE id = :id AND user_1 = :user AND batl_tip = "pve"
              LIMIT 1'
        )->execute(['winner' => $winner, 'id' => $battleId, 'user' => $userId]);
        $this->markBattleTransformationsReverted($battleId);

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
        $this->markBattleTransformationsReverted($battleId);

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
            'SELECT *
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
        $this->archiveFinishedBattleForUser($row, $userId);

        $this->db->prepare('DELETE FROM statpokemonbatle WHERE battleid = :id')->execute(['id' => $battleId]);
        $this->db->prepare('DELETE FROM battle_dop WHERE battleid = :id')->execute(['id' => $battleId]);
        $this->db->prepare('DELETE FROM battle_log WHERE battle_id = :id')->execute(['id' => $battleId]);
        $this->db->prepare('DELETE FROM bttle_status WHERE buttleid = :id')->execute(['id' => $battleId]);
        if ($this->tableExists('battle_transformations')) {
            $this->db->prepare('DELETE FROM battle_transformations WHERE battle_id = :id')->execute(['id' => $battleId]);
        }
        $this->db->prepare('DELETE FROM battles WHERE id = :id LIMIT 1')->execute(['id' => $battleId]);

        $parsed = $this->parseBattlePokemon($enemyBattlePokemon);
        if ($parsed !== null && $parsed['table'] === 'pok_pve') {
            $this->db->prepare('DELETE FROM pok_pve WHERE id = :id LIMIT 1')->execute(['id' => $parsed['id']]);
        }

        $this->db->prepare('UPDATE users SET battleid = 0 WHERE id = :id LIMIT 1')->execute(['id' => $userId]);
    }

    private function markBattleTransformationsReverted(int $battleId): void
    {
        if ($battleId <= 0 || !$this->tableExists('battle_transformations')) {
            return;
        }

        foreach ($this->activeBattleTransformations($battleId) as $row) {
            $this->insertBattleLog(
                $battleId,
                $this->battleRound($battleId),
                sprintf(
                    '[REVERT] %s -> %s after battle.',
                    (string) ($row['form_name'] ?? $row['form_base_id'] ?? 'form'),
                    (string) ($row['original_name'] ?? $row['original_base_id'] ?? 'base')
                )
            );
        }

        $this->db->prepare(
            'UPDATE battle_transformations
                SET active = 0, reverted_at = :reverted_at, updated_at = :updated_at
              WHERE battle_id = :battle AND active = 1'
        )->execute(['reverted_at' => time(), 'updated_at' => time(), 'battle' => $battleId]);
    }

    /** @return list<array<string,mixed>> */
    private function activeBattleTransformations(int $battleId): array
    {
        if ($battleId <= 0 || !$this->tableExists('battle_transformations')) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT bt.original_base_id,
                    bt.form_base_id,
                    COALESCE(form_p.Name, form_pb.title, CONCAT("#", bt.form_base_id)) AS form_name,
                    COALESCE(original_p.Name, original_pb.title, CONCAT("#", bt.original_base_id)) AS original_name
               FROM battle_transformations bt
          LEFT JOIN pokemon form_p ON form_p.id = bt.form_base_id
          LEFT JOIN poke_base form_pb ON form_pb.id = bt.form_base_id
          LEFT JOIN pokemon original_p ON original_p.id = bt.original_base_id
          LEFT JOIN poke_base original_pb ON original_pb.id = bt.original_base_id
              WHERE bt.battle_id = :battle AND bt.active = 1
              ORDER BY bt.id ASC'
        );
        $stmt->execute(['battle' => $battleId]);
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    private function archiveFinishedBattleForUser(array $battle, int $userId): void
    {
        if (!$this->tableExists('battle_history_archive')) {
            return;
        }

        $battleId = (int) ($battle['id'] ?? 0);
        if ($battleId <= 0 || $userId <= 0) {
            return;
        }

        $winner = (int) ($battle['pobeda'] ?? 0);
        $result = $winner === $userId ? 'win' : ($winner === -1 ? 'escape' : 'lose');
        $logs = $this->getBattleLog($battleId, 200);
        $stmt = $this->db->prepare(
            'INSERT INTO battle_history_archive
                (battle_id, user_id, opponent_id, mode, result, winner_id, rounds, started_at, finished_at, log_json, snapshot_json, created_at)
             VALUES
                (:battle_id, :user_id, :opponent_id, :mode, :result, :winner_id, :rounds, :started_at, :finished_at, :log_json, :snapshot_json, :created_at)
             ON DUPLICATE KEY UPDATE
                result = VALUES(result),
                winner_id = VALUES(winner_id),
                rounds = VALUES(rounds),
                finished_at = VALUES(finished_at),
                log_json = VALUES(log_json),
                snapshot_json = VALUES(snapshot_json)'
        );
        $now = time();
        $stmt->execute([
            'battle_id' => $battleId,
            'user_id' => $userId,
            'opponent_id' => (int) ($battle['user_2'] ?? 0),
            'mode' => (string) ($battle['batl_tip'] ?? 'pve'),
            'result' => $result,
            'winner_id' => $winner,
            'rounds' => (int) ($battle['raund'] ?? 0),
            'started_at' => (int) ($battle['time'] ?? 0),
            'finished_at' => $now,
            'log_json' => json_encode($logs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'snapshot_json' => json_encode($battle, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'created_at' => $now,
        ]);
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
        return in_array($itemId, [2, 92], true)
            || str_contains($name, 'скоб')
            || str_contains($name, 'браслет')
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
        foreach (['админист', 'зона админ', 'тюрьм', 'стадион', 'арена', 'аукцион', 'турнир'] as $word) {
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
            if (!$this->consumeWarrant($attackerId, (int) $permission['warrant']['itemId'])) {
                throw new \RuntimeException('Warrant item was not consumed.');
            }
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

    private function expirePendingPvpRequestsForUsers(int $firstUserId, int $secondUserId): void
    {
        $this->db->prepare(
            'UPDATE pvp_requests
                SET status = "expired", responded_at = :responded_at, updated_at = :updated_at
              WHERE status = "pending"
                AND (from_user_id IN (:from_a, :from_b) OR to_user_id IN (:to_a, :to_b))'
        )->execute([
            'responded_at' => time(),
            'updated_at' => time(),
            'from_a' => $firstUserId,
            'from_b' => $secondUserId,
            'to_a' => $firstUserId,
            'to_b' => $secondUserId,
        ]);
    }

    private function expirePvpRequests(): void
    {
        $now = time();
        try {
            $this->db->prepare(
                'UPDATE pvp_requests
                    SET status = "expired", responded_at = :responded_at, updated_at = :updated_at
                  WHERE status = "pending"
                    AND expires_at > 0
                    AND expires_at <= :now_check'
            )->execute(['responded_at' => $now, 'updated_at' => $now, 'now_check' => $now]);
        } catch (\Throwable) {
            // Migrations may not be applied yet in a dev copy; the explicit migration fixes this.
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
        if ($table === 'battles' && $column === 'id') {
            return $this->nextBattleId();
        }

        $allowed = [
            'pok_user' => ['id'],
            'attac_my_poke' => ['id'],
            'battle_dop' => ['id'],
            'battles' => ['id'],
        ];
        if (!isset($allowed[$table]) || !in_array($column, $allowed[$table], true)) {
            throw new \InvalidArgumentException('Unsupported sequence target.');
        }

        $lockName = sprintf('pokemon8_seq_%s_%s', $table, $column);
        $lock = $this->db->prepare('SELECT GET_LOCK(:name, 15)');
        $lock->execute(['name' => $lockName]);

        try {
            return (int) ($this->db
                ->query(sprintf('SELECT COALESCE(MAX(%s), 0) + 1 FROM %s', $column, $table))
                ->fetchColumn() ?: 1);
        } finally {
            $release = $this->db->prepare('SELECT RELEASE_LOCK(:name)');
            $release->execute(['name' => $lockName]);
        }
    }

    private function nextBattleId(): int
    {
        $lockAcquired = false;
        try {
            if (!$this->tableExists('battle_id_sequence')) {
                if ($this->db->inTransaction()) {
                    throw new \RuntimeException('battle_id_sequence is not available inside transaction.');
                }
                $this->db->exec(
                    'CREATE TABLE IF NOT EXISTS battle_id_sequence (
                        id TINYINT NOT NULL PRIMARY KEY,
                        next_id INT(11) NOT NULL
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
                );
            }

            $lockAcquired = ((int) ($this->db->query('SELECT GET_LOCK("pokemon8_seq_battles_id", 15)')->fetchColumn() ?: 0)) === 1;
            $maxId = (int) ($this->db->query('SELECT COALESCE(MAX(id), 0) + 1 FROM battles')->fetchColumn() ?: 1);
            $seed = max(time(), $maxId, 1);
            $insert = $this->db->prepare(
                'INSERT INTO battle_id_sequence (id, next_id)
                 VALUES (1, :seed)
                 ON DUPLICATE KEY UPDATE next_id = GREATEST(next_id, VALUES(next_id))'
            );
            $insert->execute(['seed' => $seed]);

            $current = (int) ($this->db->query('SELECT next_id FROM battle_id_sequence WHERE id = 1')->fetchColumn() ?: 0);
            $candidate = max($current, $seed, $maxId);
            for ($attempt = 0; $attempt < 20; $attempt++) {
                $exists = $this->db->prepare('SELECT 1 FROM battles WHERE id = :id LIMIT 1');
                $exists->execute(['id' => $candidate]);
                if ($exists->fetchColumn() === false) {
                    $update = $this->db->prepare('UPDATE battle_id_sequence SET next_id = :next WHERE id = 1');
                    $update->execute(['next' => $candidate + 1]);
                    return $candidate;
                }
                $candidate++;
            }
        } catch (\Throwable) {
            // fallback below
        } finally {
            if ($lockAcquired) {
                try {
                    $this->db->query('SELECT RELEASE_LOCK("pokemon8_seq_battles_id")');
                } catch (\Throwable) {
                    // no-op
                }
            }
        }

        return max(time(), (int) ($this->db->query('SELECT COALESCE(MAX(id), 0) + 1 FROM battles')->fetchColumn() ?: 1));
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
            'sun', 'rain', 'sandstorm', 'hail', 'heavy_rain', 'harsh_sun', 'strong_winds' => $kind,
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

    private function tableExists(string $table): bool
    {
        static $cache = [];
        if (isset($cache[$table])) {
            return $cache[$table];
        }

        $stmt = $this->db->prepare(
            'SELECT 1
               FROM INFORMATION_SCHEMA.TABLES
              WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = :table
              LIMIT 1'
        );
        $stmt->execute(['table' => $table]);
        $cache[$table] = (bool) $stmt->fetchColumn();
        return $cache[$table];
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
