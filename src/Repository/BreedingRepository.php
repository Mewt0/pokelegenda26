<?php
declare(strict_types=1);

namespace Pokemon8\Repository;

use PDO;
use Throwable;
use Pokemon8\Game\PokemonFormCatalog;

final class BreedingRepository
{
    public const DITTO_BASE_ID = 132;
    public const DITTO_EXTRACT_ITEM_ID = 90310;
    public const DITTO_ESSENCE_ITEM_ID = self::DITTO_EXTRACT_ITEM_ID;

    private const REQUEST_TTL_SECONDS = 600;
    private const EGG_READY_SECONDS = 24 * 24 * 3600;

    public function __construct(
        private PDO $db,
        private InventoryRepository $inventory,
        private ?SafeStorageRepository $safeStorage = null
    )
    {
    }

    public function state(int $userId): array
    {
        $this->expireOldRequests();

        $candidates = $this->breedingCandidates($userId);
        $incoming = [];
        foreach ($this->pendingIncomingRequests($userId) as $request) {
            $requesterPokemon = $this->findPokemonById((int) $request['requester_pokemon_id']);
            if ($requesterPokemon === null) {
                continue;
            }

            $options = [];
            foreach ($candidates as $candidate) {
                $check = $this->evaluatePair($requesterPokemon, $candidate, 'pair');
                $options[] = [
                    'pokemon_id' => (int) $candidate['id'],
                    'label' => $this->pokemonLabel($candidate),
                    'compatible' => (bool) $check['compatible'],
                    'reason' => (string) $check['reason'],
                    'egg_base_id' => (int) ($check['egg_base_id'] ?? 0),
                    'egg_owner_id' => (int) ($check['egg_owner_id'] ?? 0),
                ];
            }

            $incoming[] = [
                'id' => (int) $request['id'],
                'requester_id' => (int) $request['requester_id'],
                'requester_login' => (string) ($request['requester_login'] ?? ''),
                'requester_pokemon' => $this->formatPokemon($requesterPokemon),
                'expires_at' => (int) $request['expires_at'],
                'remaining_seconds' => max(0, (int) $request['expires_at'] - time()),
                'candidate_options' => $options,
            ];
        }

        return [
            'ok' => true,
            'candidates' => array_map(fn (array $row): array => $this->formatPokemon($row), $candidates),
            'incoming' => $incoming,
            'outgoing' => array_map(fn (array $row): array => $this->formatRequest($row), $this->outgoingRequests($userId)),
            'history' => array_map(fn (array $row): array => $this->formatRequest($row), $this->recentRequests($userId)),
            'ditto_essence' => [
                'item_id' => self::DITTO_EXTRACT_ITEM_ID,
                'name' => 'Экстракт Дитто',
                'count' => $this->inventory->countItem($userId, self::DITTO_EXTRACT_ITEM_ID),
                'valid_candidates' => array_values(array_filter(
                    array_map(function (array $candidate): ?array {
                        $check = $this->evaluatePair($candidate, null, 'ditto_essence');
                        if (!$check['compatible']) {
                            return null;
                        }

                        return [
                            'pokemon_id' => (int) $candidate['id'],
                            'label' => $this->pokemonLabel($candidate),
                            'reason' => (string) $check['reason'],
                            'egg_base_id' => (int) ($check['egg_base_id'] ?? 0),
                        ];
                    }, $candidates)
                )),
            ],
            'rules' => [
                'request_ttl_seconds' => self::REQUEST_TTL_SECONDS,
                'egg_ready_seconds' => self::EGG_READY_SECONDS,
                'ditto_essence_item_id' => self::DITTO_EXTRACT_ITEM_ID,
                'ditto_extract_item_id' => self::DITTO_EXTRACT_ITEM_ID,
            ],
        ];
    }

    public function createRequest(int $userId, string $target, int $pokemonId): array
    {
        $this->expireOldRequests();

        if ($this->userIsBusy($userId)) {
            return ['ok' => false, 'message' => 'Сначала закончите бой или обмен.'];
        }

        $targetUser = $this->findUser($target);
        if ($targetUser === null) {
            return ['ok' => false, 'message' => 'Игрок для спарки не найден.'];
        }
        $targetUserId = (int) $targetUser['id'];
        if ($targetUserId === $userId) {
            return ['ok' => false, 'message' => 'Для этой QA-спарки выберите второго игрока.'];
        }

        $pokemon = $this->findOwnedPokemon($userId, $pokemonId);
        if ($pokemon === null) {
            return ['ok' => false, 'message' => 'Выберите своего покемона.'];
        }

        $singleCheck = $this->checkSinglePokemon($pokemon);
        if (!$singleCheck['ok']) {
            return ['ok' => false, 'message' => $singleCheck['message']];
        }

        $now = time();
        $this->db->prepare(
            'INSERT INTO pokemon_breeding_requests
                (requester_id, target_user_id, requester_pokemon_id, status, result_message, method, created_at, expires_at)
             VALUES
                (:requester, :target, :pokemon, "pending", "", "pair", :created, :expires)'
        )->execute([
            'requester' => $userId,
            'target' => $targetUserId,
            'pokemon' => $pokemonId,
            'created' => $now,
            'expires' => $now + self::REQUEST_TTL_SECONDS,
        ]);

        return [
            'ok' => true,
            'message' => sprintf(
                'Заявка на спарку отправлена игроку %s. Он должен выбрать совместимого покемона.',
                (string) $targetUser['login']
            ),
            'request_id' => (int) $this->db->lastInsertId(),
            'state' => $this->state($userId),
        ];
    }

    public function respond(int $userId, int $requestId, string $action, int $pokemonId): array
    {
        $this->expireOldRequests();

        if (!in_array($action, ['accept', 'decline'], true)) {
            return ['ok' => false, 'message' => 'Неизвестное действие.'];
        }

        if ($action === 'decline') {
            $this->db->prepare(
                'UPDATE pokemon_breeding_requests
                    SET status = "declined", result_message = "Заявка отклонена.", responded_at = :time
                  WHERE id = :id AND target_user_id = :user AND status = "pending"
                  LIMIT 1'
            )->execute(['time' => time(), 'id' => $requestId, 'user' => $userId]);

            return [
                'ok' => true,
                'message' => 'Заявка отклонена.',
                'state' => $this->state($userId),
            ];
        }

        if ($this->userIsBusy($userId)) {
            return ['ok' => false, 'message' => 'Сначала закончите бой или обмен.'];
        }

        $startedTransaction = !$this->db->inTransaction();
        if ($startedTransaction) {
            $this->db->beginTransaction();
        }

        try {
            $stmt = $this->db->prepare(
                'SELECT *
                   FROM pokemon_breeding_requests
                  WHERE id = :id AND target_user_id = :user
                  LIMIT 1
                  FOR UPDATE'
            );
            $stmt->execute(['id' => $requestId, 'user' => $userId]);
            $request = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$request) {
                if ($startedTransaction) {
                    $this->db->rollBack();
                }
                return ['ok' => false, 'message' => 'Заявка не найдена.'];
            }

            if ((string) $request['status'] !== 'pending') {
                if ($startedTransaction) {
                    $this->db->rollBack();
                }
                return ['ok' => false, 'message' => 'Эта заявка уже обработана.'];
            }

            if ((int) $request['expires_at'] < time()) {
                $this->db->prepare('UPDATE pokemon_breeding_requests SET status = "expired", result_message = "Заявка истекла." WHERE id = :id LIMIT 1')
                    ->execute(['id' => $requestId]);
                if ($startedTransaction) {
                    $this->db->commit();
                }
                return ['ok' => false, 'message' => 'Заявка истекла.'];
            }

            $requesterPokemon = $this->findPokemonById((int) $request['requester_pokemon_id']);
            $targetPokemon = $this->findOwnedPokemon($userId, $pokemonId);
            if ($requesterPokemon === null || $targetPokemon === null) {
                if ($startedTransaction) {
                    $this->db->rollBack();
                }
                return ['ok' => false, 'message' => 'Один из родителей не найден.'];
            }

            $check = $this->evaluatePair($requesterPokemon, $targetPokemon, 'pair');
            if (!$check['compatible']) {
                if ($startedTransaction) {
                    $this->db->rollBack();
                }
                return ['ok' => false, 'message' => (string) $check['reason'], 'compatible' => false];
            }

            $eggId = $this->createEgg($check, $requesterPokemon, $targetPokemon, $requestId, 'pair');
            $message = sprintf(
                'Спарка прошла успешно: %s + %s. Яйцо #%d создано игроку #%d.',
                $this->pokemonLabel($requesterPokemon),
                $this->pokemonLabel($targetPokemon),
                $eggId,
                (int) $check['egg_owner_id']
            );
            $this->db->prepare(
                'UPDATE pokemon_breeding_requests
                    SET status = "accepted", target_pokemon_id = :target_pokemon, result_message = :message,
                        egg_id = :egg, responded_at = :time
                  WHERE id = :id
                  LIMIT 1'
            )->execute([
                'target_pokemon' => $pokemonId,
                'message' => $message,
                'egg' => $eggId,
                'time' => time(),
                'id' => $requestId,
            ]);

            if ($startedTransaction) {
                $this->db->commit();
            }

            return [
                'ok' => true,
                'message' => $message,
                'egg_id' => $eggId,
                'egg_owner_id' => (int) $check['egg_owner_id'],
                'state' => $this->state($userId),
            ];
        } catch (Throwable $e) {
            if ($startedTransaction && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $this->safeStorage?->recordRollback(
                'breeding_failed:' . $requestId . ':' . time(),
                'breeding_accept',
                $userId,
                'breeding',
                $requestId,
                ['request_id' => $requestId, 'target_pokemon_id' => $pokemonId],
                [],
                ['transaction_rolled_back' => true, 'egg_created' => false],
                'failed',
                $e->getMessage()
            );
            return ['ok' => false, 'message' => 'Не удалось завершить спарку: ' . $e->getMessage()];
        }
    }

    public function useDittoEssence(int $userId, int $pokemonId): array
    {
        $this->expireOldRequests();

        if ($this->userIsBusy($userId)) {
            return ['ok' => false, 'message' => 'Сначала закончите бой или обмен.'];
        }

        $pokemon = $this->findOwnedPokemon($userId, $pokemonId);
        if ($pokemon === null) {
            return ['ok' => false, 'message' => 'Покемон не найден.'];
        }

        $check = $this->evaluatePair($pokemon, null, 'ditto_essence');
        return [
            'ok' => false,
            'message' => (string) $check['reason'],
            'state' => $this->state($userId),
        ];
    }

    private function expireOldRequests(): void
    {
        $this->db->prepare(
            'UPDATE pokemon_breeding_requests
                SET status = "expired", result_message = "Заявка истекла."
              WHERE status = "pending" AND expires_at < :time'
        )->execute(['time' => time()]);
    }

    private function findUser(string $target): ?array
    {
        $target = trim($target);
        if ($target === '') {
            return null;
        }

        if (ctype_digit($target)) {
            $stmt = $this->db->prepare('SELECT id, login FROM users WHERE id = :id LIMIT 1');
            $stmt->execute(['id' => (int) $target]);
        } else {
            $stmt = $this->db->prepare('SELECT id, login FROM users WHERE LOWER(login) = LOWER(:login) LIMIT 1');
            $stmt->execute(['login' => $target]);
        }

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : null;
    }

    private function breedingCandidates(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT pu.*, pb.title AS base_title, pb.egg AS legacy_egg
               FROM pok_user pu
          LEFT JOIN poke_base pb ON pb.id = pu.basenum
              WHERE pu.users = :user
              ORDER BY pu.active DESC, pu.basenum ASC, pu.id ASC'
        );
        $stmt->execute(['user' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function findOwnedPokemon(int $userId, int $pokemonId): ?array
    {
        if ($pokemonId <= 0) {
            return null;
        }
        $stmt = $this->db->prepare(
            'SELECT pu.*, pb.title AS base_title, pb.egg AS legacy_egg
               FROM pok_user pu
          LEFT JOIN poke_base pb ON pb.id = pu.basenum
              WHERE pu.users = :user AND pu.id = :pokemon
              LIMIT 1'
        );
        $stmt->execute(['user' => $userId, 'pokemon' => $pokemonId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : null;
    }

    private function findPokemonById(int $pokemonId): ?array
    {
        if ($pokemonId <= 0) {
            return null;
        }
        $stmt = $this->db->prepare(
            'SELECT pu.*, pb.title AS base_title, pb.egg AS legacy_egg
               FROM pok_user pu
          LEFT JOIN poke_base pb ON pb.id = pu.basenum
              WHERE pu.id = :pokemon
              LIMIT 1'
        );
        $stmt->execute(['pokemon' => $pokemonId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : null;
    }

    private function pendingIncomingRequests(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT r.*, u.login AS requester_login
               FROM pokemon_breeding_requests r
               JOIN users u ON u.id = r.requester_id
              WHERE r.target_user_id = :user AND r.status = "pending"
              ORDER BY r.created_at DESC, r.id DESC'
        );
        $stmt->execute(['user' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function outgoingRequests(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT r.*, u.login AS target_login
               FROM pokemon_breeding_requests r
               JOIN users u ON u.id = r.target_user_id
              WHERE r.requester_id = :user AND r.status = "pending"
              ORDER BY r.created_at DESC, r.id DESC'
        );
        $stmt->execute(['user' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function recentRequests(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT r.*, tu.login AS target_login, ru.login AS requester_login
               FROM pokemon_breeding_requests r
          LEFT JOIN users tu ON tu.id = r.target_user_id
          LEFT JOIN users ru ON ru.id = r.requester_id
              WHERE r.requester_id = :requester_user OR r.target_user_id = :target_user
              ORDER BY r.created_at DESC, r.id DESC
              LIMIT 10'
        );
        $stmt->execute(['requester_user' => $userId, 'target_user' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * @return array{compatible:bool,reason:string,egg_base_id?:int,egg_owner_id?:int,chance_percent?:int}
     */
    private function evaluatePair(array $first, ?array $second, string $method): array
    {
        $firstRule = $this->ruleForPokemon($first);
        if (!$this->ruleAllowsBreeding($firstRule)) {
            return ['compatible' => false, 'reason' => 'Этот покемон не может размножаться.'];
        }

        if ($method === 'ditto_essence') {
            if ($this->displayBaseId($first) === self::DITTO_BASE_ID) {
                return ['compatible' => false, 'reason' => 'Экстракт Дитто не работает на Ditto.'];
            }
            if ((int) ($first['sex'] ?? 0) !== 0 && (string) ($firstRule['gender_mode'] ?? '') !== 'genderless') {
                return ['compatible' => false, 'reason' => 'Экстракт Дитто нужен только для бесполых покемонов.'];
            }

            return [
                'compatible' => false,
                'reason' => 'Экстракт Дитто теперь не используется напрямую: наденьте его на обоих бесполых родителей с одной буквой совместимости и отправьте обычную заявку.',
            ];
        }

        if ($second === null) {
            return ['compatible' => false, 'reason' => 'Нужен второй родитель.'];
        }

        $secondRule = $this->ruleForPokemon($second);
        if (!$this->ruleAllowsBreeding($secondRule)) {
            return ['compatible' => false, 'reason' => 'Второй покемон не может размножаться.'];
        }

        $firstBase = $this->displayBaseId($first);
        $secondBase = $this->displayBaseId($second);
        $firstIsDitto = $firstBase === self::DITTO_BASE_ID;
        $secondIsDitto = $secondBase === self::DITTO_BASE_ID;
        if ($firstIsDitto && $secondIsDitto) {
            return ['compatible' => false, 'reason' => 'Ditto + Ditto не дают яйцо.'];
        }

        if ($firstIsDitto || $secondIsDitto) {
            $speciesParent = $firstIsDitto ? $second : $first;
            $speciesRule = $this->ruleForPokemon($speciesParent);
            if (!$this->ruleAllowsBreeding($speciesRule)) {
                return ['compatible' => false, 'reason' => 'Вид второго родителя не допускает разведение.'];
            }

            return [
                'compatible' => true,
                'reason' => 'Совместимо через Ditto.',
                'egg_base_id' => (int) ($speciesRule['egg_base_id'] ?: $this->displayBaseId($speciesParent)),
                'egg_owner_id' => (int) $speciesParent['users'],
                'chance_percent' => 85,
            ];
        }

        $firstGenderless = (int) ($first['sex'] ?? 0) === 0 || (string) ($firstRule['gender_mode'] ?? '') === 'genderless';
        $secondGenderless = (int) ($second['sex'] ?? 0) === 0 || (string) ($secondRule['gender_mode'] ?? '') === 'genderless';
        if ($firstGenderless && $secondGenderless) {
            if (!$this->lettersMatch($firstRule, $secondRule)) {
                return ['compatible' => false, 'reason' => 'У бесполых родителей должна совпадать буква совместимости.'];
            }

            if (!$this->hasDittoExtractEquipped($first) || !$this->hasDittoExtractEquipped($second)) {
                return ['compatible' => false, 'reason' => 'Для бесполой спарки Экстракт Дитто должен быть надет на обоих покемонах.'];
            }

            return [
                'compatible' => true,
                'reason' => 'Бесполая пара совместима через Экстракт Дитто на обоих родителях.',
                'egg_base_id' => (int) ($firstRule['egg_base_id'] ?: $this->displayBaseId($first)),
                'egg_owner_id' => (int) $first['users'],
                'chance_percent' => 70,
            ];
        }

        if ($firstGenderless || $secondGenderless) {
            return ['compatible' => false, 'reason' => 'Бесполый покемон размножается только с Ditto или с другим бесполым через Экстракт Дитто на обоих.'];
        }

        if ((int) ($first['sex'] ?? 0) === (int) ($second['sex'] ?? 0)) {
            return ['compatible' => false, 'reason' => 'Нужны покемоны разных полов.'];
        }

        if (!$this->lettersMatch($firstRule, $secondRule)) {
            return ['compatible' => false, 'reason' => 'Буква совместимости не совпадает.'];
        }

        $female = (int) ($first['sex'] ?? 0) === 2 ? $first : $second;
        $femaleRule = $this->ruleForPokemon($female);

        return [
            'compatible' => true,
            'reason' => 'Пара совместима.',
            'egg_base_id' => (int) ($femaleRule['egg_base_id'] ?: $this->displayBaseId($female)),
            'egg_owner_id' => (int) $female['users'],
            'chance_percent' => 70,
        ];
    }

    private function checkSinglePokemon(array $pokemon): array
    {
        $rule = $this->ruleForPokemon($pokemon);
        if (!$this->ruleAllowsBreeding($rule)) {
            return ['ok' => false, 'message' => 'Этот покемон находится в группе No Eggs Discovered или запрещён к разведению.'];
        }

        return ['ok' => true, 'message' => 'OK'];
    }

    private function createEgg(array $evaluation, array $first, ?array $second, int $requestId, string $method): int
    {
        $eggId = $this->nextTableId('eggs', 'id_egg');
        $eggBaseId = (int) ($evaluation['egg_base_id'] ?? 0);
        $ownerId = (int) ($evaluation['egg_owner_id'] ?? 0);
        $iv = $this->breedIv($first, $second);
        $attackId = $this->eggMoveId($eggBaseId, $first, $second);
        $tips = $this->rollEggTips($first, $second);

        $this->db->prepare(
            'INSERT INTO eggs
                (id_egg, base_id_egg, dtime, users_egg, hp_iv, atk_iv, def_iv, satk_iv, sdef_iv, speed_iv,
                 tips, attac_one, spar, parent_one_id, parent_two_id, parent_one_user_id, parent_two_user_id,
                 breeding_request_id, breeding_method)
             VALUES
                (:egg_id, :base_id, :ready_at, :owner_id, :hp_iv, :atk_iv, :def_iv, :satk_iv, :sdef_iv, :speed_iv,
                 :tips, :attack_id, 1, :parent_one_id, :parent_two_id, :parent_one_user_id, :parent_two_user_id,
                 :request_id, :method_key)'
        )->execute([
            'egg_id' => $eggId,
            'base_id' => $eggBaseId,
            'ready_at' => time() + self::EGG_READY_SECONDS,
            'owner_id' => $ownerId,
            'hp_iv' => $iv['hp'],
            'atk_iv' => $iv['atk'],
            'def_iv' => $iv['def'],
            'satk_iv' => $iv['satk'],
            'sdef_iv' => $iv['sdef'],
            'speed_iv' => $iv['speed'],
            'tips' => $tips,
            'attack_id' => $attackId,
            'parent_one_id' => (int) $first['id'],
            'parent_two_id' => $second !== null ? (int) $second['id'] : 0,
            'parent_one_user_id' => (int) $first['users'],
            'parent_two_user_id' => $second !== null ? (int) $second['users'] : 0,
            'request_id' => $requestId,
            'method_key' => $method,
        ]);

        $this->safeStorage?->recordRollback(
            'breeding_egg:' . $eggId,
            'breeding_egg_create',
            $ownerId,
            'breeding',
            $requestId,
            [
                'parent_one_id' => (int) $first['id'],
                'parent_two_id' => $second !== null ? (int) $second['id'] : 0,
                'parent_one_user_id' => (int) $first['users'],
                'parent_two_user_id' => $second !== null ? (int) $second['users'] : 0,
            ],
            [
                'egg_id' => $eggId,
                'base_id' => $eggBaseId,
                'owner_id' => $ownerId,
                'ready_at' => time() + self::EGG_READY_SECONDS,
                'iv' => $iv,
                'attack_id' => $attackId,
                'method' => $method,
            ],
            [
                'action' => 'delete_egg',
                'egg_id' => $eggId,
                'owner_id' => $ownerId,
                'reason' => 'Rollback breeding egg creation',
            ],
            'recorded'
        );

        return $eggId;
    }

    private function breedIv(array $first, ?array $second): array
    {
        $fields = ['hp', 'atk', 'def', 'satk', 'sdef', 'speed'];
        $iv = [];
        foreach ($fields as $field) {
            $column = $field . '_iv';
            $a = max(0, min(31, (int) ($first[$column] ?? 0)));
            $b = $second !== null ? max(0, min(31, (int) ($second[$column] ?? 0))) : random_int(0, 31);
            $iv[$field] = max(0, min(31, max($a, $b) + $this->geneMutationDelta()));
        }

        return $iv;
    }

    private function geneMutationDelta(): int
    {
        $roll = random_int(1, 100);
        return match (true) {
            $roll <= 3 => 2,
            $roll <= 12 => 1,
            $roll >= 98 => -2,
            $roll >= 90 => -1,
            default => 0,
        };
    }

    private function rollEggTips(array $first, ?array $second): string
    {
        $firstShiny = (string) ($first['tips'] ?? 'normal') === 'shine';
        $secondShiny = $second !== null && (string) ($second['tips'] ?? 'normal') === 'shine';
        $chance = ($firstShiny || $secondShiny) ? 60 : 1000;
        return random_int(1, $chance) === 1 ? 'shine' : 'normal';
    }

    private function firstMoveId(int $baseId): int
    {
        $stmt = $this->db->prepare(
            'SELECT atac_id
               FROM attac_poke
              WHERE poke_base_id = :base
              ORDER BY atc_lvl ASC, id_structure ASC
              LIMIT 1'
        );
        $stmt->execute(['base' => $baseId]);
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    private function eggMoveId(int $baseId, array $first, ?array $second): int
    {
        $activeMoves = array_values(array_unique(array_filter(array_merge(
            $this->activeMoveIds((int) ($first['id'] ?? 0)),
            $second !== null ? $this->activeMoveIds((int) ($second['id'] ?? 0)) : []
        ))));
        if ($activeMoves === []) {
            return $this->firstMoveId($baseId);
        }

        $placeholders = implode(',', array_fill(0, count($activeMoves), '?'));
        $stmt = $this->db->prepare(
            'SELECT atac_id
               FROM attac_poke
              WHERE poke_base_id = ? AND atac_id IN (' . $placeholders . ')'
        );
        $stmt->execute(array_merge([$baseId], $activeMoves));
        $learnable = array_flip(array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN) ?: []));
        foreach ($activeMoves as $moveId) {
            if (isset($learnable[(int) $moveId])) {
                return (int) $moveId;
            }
        }

        return $this->firstMoveId($baseId);
    }

    /**
     * @return list<int>
     */
    private function activeMoveIds(int $pokemonId): array
    {
        if ($pokemonId <= 0) {
            return [];
        }

        $stmt = $this->db->prepare('SELECT a_id, b_id, c_id, d_id FROM attac_my_poke WHERE pok_id = :pokemon LIMIT 1');
        $stmt->execute(['pokemon' => $pokemonId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!is_array($row)) {
            return [];
        }

        $ids = [];
        foreach (['a_id', 'b_id', 'c_id', 'd_id'] as $key) {
            $id = (int) ($row[$key] ?? 0);
            if ($id > 0) {
                $ids[] = $id;
            }
        }

        return $ids;
    }

    private function ruleForPokemon(array $pokemon): array
    {
        $baseId = $this->displayBaseId($pokemon);
        $stmt = $this->db->prepare('SELECT * FROM pokemon_breeding_rules WHERE base_id = :base LIMIT 1');
        $stmt->execute(['base' => $baseId]);
        $rule = $stmt->fetch(PDO::FETCH_ASSOC);
        if (is_array($rule)) {
            return $rule;
        }

        $legacyEgg = (int) ($pokemon['legacy_egg'] ?? 0);
        if ($legacyEgg <= 0 || $legacyEgg === $baseId) {
            return [
                'base_id' => $baseId,
                'egg_base_id' => $baseId,
                'egg_group_1' => 'unknown',
                'egg_group_2' => '',
                'compatibility_letter' => '',
                'gender_mode' => (int) ($pokemon['sex'] ?? 0) === 0 ? 'genderless' : 'normal',
                'breedable' => 0,
            ];
        }

        return [
            'base_id' => $baseId,
            'egg_base_id' => $baseId,
            'egg_group_1' => 'legacy_' . $legacyEgg,
            'egg_group_2' => '',
            'compatibility_letter' => $this->legacyCompatibilityLetter($legacyEgg),
            'gender_mode' => (int) ($pokemon['sex'] ?? 0) === 0 ? 'genderless' : 'normal',
            'breedable' => 1,
        ];
    }

    private function ruleAllowsBreeding(array $rule): bool
    {
        if ((int) ($rule['breedable'] ?? 0) !== 1) {
            return false;
        }
        $groups = $this->eggGroups($rule);
        return $groups !== [] && !in_array('no_eggs', $groups, true);
    }

    private function groupsOverlap(array $a, array $b): bool
    {
        $left = array_diff($this->eggGroups($a), ['ditto']);
        $right = array_diff($this->eggGroups($b), ['ditto']);
        return array_values(array_intersect($left, $right)) !== [];
    }

    private function lettersMatch(array $a, array $b): bool
    {
        $left = $this->compatibilityLetter($a);
        $right = $this->compatibilityLetter($b);
        return $left !== '' && $left === $right;
    }

    private function compatibilityLetter(array $rule): string
    {
        $letter = strtoupper(trim((string) ($rule['compatibility_letter'] ?? '')));
        if ($letter !== '') {
            return substr($letter, 0, 1);
        }

        $groups = array_diff($this->eggGroups($rule), ['ditto', 'no_eggs']);
        if ($groups === []) {
            return '';
        }

        return strtoupper(substr((string) reset($groups), 0, 1));
    }

    private function legacyCompatibilityLetter(int $legacyEgg): string
    {
        if ($legacyEgg <= 0) {
            return '';
        }

        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return $alphabet[($legacyEgg - 1) % strlen($alphabet)] ?? '';
    }

    /**
     * @return list<string>
     */
    private function eggGroups(array $rule): array
    {
        $groups = [];
        foreach (['egg_group_1', 'egg_group_2'] as $key) {
            $group = trim((string) ($rule[$key] ?? ''));
            if ($group !== '') {
                $groups[] = $group;
            }
        }
        return array_values(array_unique($groups));
    }

    private function displayBaseId(array $pokemon): int
    {
        return PokemonFormCatalog::displayBaseId((int) ($pokemon['basenum'] ?? 0));
    }

    private function formatPokemon(array $pokemon): array
    {
        $rule = $this->ruleForPokemon($pokemon);
        return [
            'id' => (int) $pokemon['id'],
            'user_id' => (int) $pokemon['users'],
            'base_id' => $this->displayBaseId($pokemon),
            'name' => $this->cleanName((string) ($pokemon['names'] ?? $pokemon['base_title'] ?? ''), $this->displayBaseId($pokemon)),
            'label' => $this->pokemonLabel($pokemon),
            'level' => (int) ($pokemon['lvl'] ?? 0),
            'sex' => (int) ($pokemon['sex'] ?? 0),
            'sex_label' => $this->sexLabel((int) ($pokemon['sex'] ?? 0)),
            'active' => (int) ($pokemon['active'] ?? 0) === 1,
            'egg_groups' => $this->eggGroups($rule),
            'compatibility_letter' => $this->compatibilityLetter($rule),
            'breedable' => $this->ruleAllowsBreeding($rule),
            'gender_mode' => (string) ($rule['gender_mode'] ?? 'normal'),
            'egg_base_id' => (int) ($rule['egg_base_id'] ?? 0),
            'paired' => (int) ($pokemon['reproduction'] ?? 0) > 0,
            'held_item_id' => $this->heldItemId($pokemon),
            'has_ditto_extract' => $this->hasDittoExtractEquipped($pokemon),
        ];
    }

    private function formatRequest(array $row): array
    {
        return [
            'id' => (int) $row['id'],
            'requester_id' => (int) $row['requester_id'],
            'target_user_id' => (int) $row['target_user_id'],
            'requester_pokemon_id' => (int) $row['requester_pokemon_id'],
            'target_pokemon_id' => (int) $row['target_pokemon_id'],
            'status' => (string) $row['status'],
            'method' => (string) $row['method'],
            'message' => (string) ($row['result_message'] ?? ''),
            'egg_id' => (int) ($row['egg_id'] ?? 0),
            'created_at' => (int) $row['created_at'],
            'expires_at' => (int) $row['expires_at'],
            'remaining_seconds' => max(0, (int) $row['expires_at'] - time()),
            'target_login' => (string) ($row['target_login'] ?? ''),
            'requester_login' => (string) ($row['requester_login'] ?? ''),
        ];
    }

    private function pokemonLabel(array $pokemon): string
    {
        $baseId = $this->displayBaseId($pokemon);
        return sprintf(
            '#%d %s Lv.%d %s',
            (int) $pokemon['id'],
            $this->cleanName((string) ($pokemon['names'] ?? $pokemon['base_title'] ?? ''), $baseId),
            (int) ($pokemon['lvl'] ?? 0),
            $this->sexLabel((int) ($pokemon['sex'] ?? 0))
        );
    }

    private function sexLabel(int $sex): string
    {
        return match ($sex) {
            1 => '♂',
            2 => '♀',
            default => 'бесполый',
        };
    }

    private function heldItemId(array $pokemon): int
    {
        $direct = (int) ($pokemon['item'] ?? 0);
        if ($direct > 0) {
            return $direct;
        }

        $pokemonId = (int) ($pokemon['id'] ?? 0);
        if ($pokemonId <= 0) {
            return 0;
        }

        $stmt = $this->db->prepare(
            'SELECT id_items
               FROM items_poke
              WHERE id_poke = :pokemon
                AND (datetime = "not" OR (datetime REGEXP "^[0-9]+$" AND CAST(datetime AS UNSIGNED) > :time))
              LIMIT 1'
        );
        $stmt->execute(['pokemon' => $pokemonId, 'time' => time()]);
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    private function hasDittoExtractEquipped(array $pokemon): bool
    {
        return $this->heldItemId($pokemon) === self::DITTO_EXTRACT_ITEM_ID;
    }

    private function cleanName(string $name, int $baseId): string
    {
        $name = trim(html_entity_decode(strip_tags($name), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $name = preg_replace('/^#?0*' . $baseId . '\s*/u', '', $name) ?: $name;
        $name = preg_replace('/^#?0*\d+\s*/u', '', $name) ?: $name;
        return trim($name) !== '' ? trim($name) : ('Pokemon #' . $baseId);
    }

    private function userIsBusy(int $userId): bool
    {
        $stmt = $this->db->prepare('SELECT battleid, trade FROM users WHERE id = :user LIMIT 1');
        $stmt->execute(['user' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!is_array($row)) {
            return true;
        }

        return (int) ($row['battleid'] ?? 0) > 0 || (int) ($row['trade'] ?? 0) > 0;
    }

    private function nextTableId(string $table, string $column): int
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table) || !preg_match('/^[a-zA-Z0-9_]+$/', $column)) {
            return 1;
        }
        return (int) ($this->db->query('SELECT COALESCE(MAX(`' . $column . '`), 0) + 1 FROM `' . $table . '`')->fetchColumn() ?: 1);
    }
}
