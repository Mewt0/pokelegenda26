<?php
declare(strict_types=1);

/**
 * CLI-only Helping Hand regression. Uses the real move/damage/repository code,
 * with a minimal in-memory PDO double: never connects to or changes a live DB.
 * Run: php tools/helping_hand_regression.php
 */

namespace Pokemon8\Repository {
    final class PokemonEvolutionRepository
    {
        public function __construct(\PDO $db) {}
    }

    final class BattleReplayRepository
    {
        public array $damages = [];
        public function recordDamage(int $battleId, int $round, array $payload): void
        {
            $this->damages[] = $payload;
        }
        public function recordRandomRoll(int $battleId, int $round, string $name, int $roll, int $min, int $max, ?int $chance, ?bool $success, array $context): void {}
    }
}

namespace Pokemon8\Game {
    final class BattleTurnStatusService
    {
        public bool $canAct = true;
        public function applyStartOfTurnStatus(int $battleId, int $round, array $attacker, array $defender, array $move): array
        {
            return ['canAct' => $this->canAct, 'messages' => []];
        }
    }

    final class BattleMoveEffectCatalog
    {
        public static function key(string $name): string { return strtolower($name); }
        public static function primaryStatus(int $id, string $name): ?array { return null; }
        public static function secondaryStatuses(int $id, string $name): array { return []; }
    }

    final class BattleAbilityCatalog
    {
        public static function weatherFamily(string $kind): string { return ''; }
        public static function weatherAttackMultiplier(string $ability, string $weather, string $type): float { return 1.0; }
        public static function key(array $pokemon): string { return ''; }
    }

    final class BattleMathService
    {
        public function stab(string $type, array $types): float { return 1.0; }
        public function typeEffectiveness(string $type, array $types): float { return $type === 'Ghost' ? 0.0 : 1.0; }
        public function typeMessage(float $effect): string { return ''; }
    }

    final class BattleEngineService
    {
        public bool $miss = false;
        public bool $weatherBlock = false;
        private int $lastDamage = 0;
        public function setLastDamageDealt(int $damage): void { $this->lastDamage = $damage; }
        public function lastDamageDealt(): int { return $this->lastDamage; }
        public function heldItemHasMeta(array $pokemon, string $key): bool { return false; }
        public function weatherBlockedMoveText(int $battle, string $attacker, string $move, string $type): string
        {
            return $this->weatherBlock ? 'blocked by weather' : '';
        }
        public function effectiveAccuracy(int $battle, array $attacker, array $defender, array $move): int { return $this->miss ? 50 : 100; }
        public function replayRoll(int $battle, int $round, string $label, int $min, int $max, ?int $threshold = null, ?bool $success = null, array $context = []): int
        {
            return $label === 'damage_variance' ? 100 : $max;
        }
        public function applyMissMoveEffect(array &$attacker, array $move): string { return ''; }
        public function effectiveStatFor(int $battle, array $pokemon, string $stat, string $stage): int { return (int) ($pokemon[$stat] ?? 1); }
        public function hasMajorStatus(int $battle, array $pokemon, int $status): bool { return false; }
        public function effectiveTypeLabels(int $battle, array $pokemon): array { return ['Normal']; }
        public function criticChance(string $kind): int { return 0; }
        public function heldItemCriticalBonus(array $pokemon): int { return 0; }
        public function weatherPowerModifier(string $weather, string $type): float { return 1.0; }
        public function terrainPowerModifier(string $terrain, string $type): float { return 1.0; }
        public function screenDamageModifier(int $battle, array $defender, bool $special): float { return 1.0; }
        public function heldItemPowerModifier(array $attacker, string $type, int $category): float { return 1.0; }
        public function weatherLogKey(string $weather): string { return $weather; }
        public function statMoveEffects(string $name): array { return []; }
        public function applyStageEffect(int $battle, array $attacker, array $defender, array $effect): string { return ''; }
        public function tryApplyStatus(int $battle, int $round, array $target, int $status, int $chance, string $type, string $move): string { return ''; }
        public function applyNamedTrainingEffect(int $battle, int $round, array $attacker, array $defender, array $move): string { return ''; }
        public function applySpecialMoveEffect(int $battle, int $round, array &$attacker, array &$defender, array $move): string { return ''; }
    }
}

namespace {
    use Pokemon8\Game\BattleDamageService;
    use Pokemon8\Game\BattleEngineService;
    use Pokemon8\Game\BattleMathService;
    use Pokemon8\Game\BattleMoveService;
    use Pokemon8\Game\BattleTurnStatusService;
    use Pokemon8\Repository\BattleRepository;
    use Pokemon8\Repository\BattleReplayRepository;

    /** Very small battle_dop SQL simulation for the real BattleRepository methods. */
    final class MemoryPDO extends PDO
    {
        public array $volatiles = [];
        public function __construct() {}
        public function prepare(string $query, array $options = []): PDOStatement|false
        {
            return new MemoryStatement($this, $query);
        }
        public function query(string $query, ?int $fetchMode = null, mixed ...$fetchModeArgs): PDOStatement|false
        {
            $stmt = $this->prepare($query);
            $stmt->execute();
            return $stmt;
        }
        public function perform(string $sql, array $params): array
        {
            $battle = (int) ($params['battle'] ?? 0);
            $poke = (string) ($params['poke'] ?? '');
            if (str_contains($sql, 'GET_LOCK') || str_contains($sql, 'RELEASE_LOCK')) {
                return [[1], 0];
            }
            if (str_contains($sql, 'COALESCE(MAX(id)')) {
                $ids = array_column($this->volatiles, 'id');
                return [[[($ids === [] ? 0 : max($ids)) + 1]], 0];
            }
            if (str_contains($sql, 'INSERT INTO battle_dop')) {
                $this->volatiles[] = [
                    'id' => (int) $params['id'], 'battleid' => $battle,
                    'pokeid' => $poke, 'propusk' => (string) $params['battle_pokemon'],
                    'mess' => (string) $params['kind'], 'vulnerability' => (int) $params['round_end'], 'at_dop' => 0,
                ];
                return [[], 1];
            }
            if (str_contains($sql, 'DELETE FROM battle_dop')) {
                $old = count($this->volatiles);
                $this->volatiles = array_values(array_filter($this->volatiles, static function (array $v) use ($sql, $params, $battle, $poke): bool {
                    if ($v['battleid'] !== $battle) { return true; }
                    if (str_contains($sql, 'mess = "helping_hand"')) { return $v['mess'] !== 'helping_hand'; }
                    return $v['pokeid'] !== $poke || $v['propusk'] !== ($params['battle_pokemon'] ?? '') || $v['mess'] !== ($params['kind'] ?? '');
                }));
                return [[], $old - count($this->volatiles)];
            }
            if (str_contains($sql, 'SELECT id FROM battle_dop')) {
                foreach ($this->volatiles as $v) {
                    if ($v['battleid'] === $battle && $v['pokeid'] === $poke) {
                        return [[['id' => $v['id']]], 0];
                    }
                }
            }
            if (str_contains($sql, 'FROM stat_attak')) {
                if ((int) ($params['move'] ?? 0) === 111) {
                    return [[['tip' => 'minus', 'atc' => 1, 'stati' => 2]], 0];
                }
            }
            if (str_contains($sql, 'UPDATE battles')) { return [[], 1]; }
            return [[], 0];
        }
    }

    final class MemoryStatement extends PDOStatement
    {
        private array $rows = [];
        private int $pos = 0;
        private int $changed = 0;
        public function __construct(private MemoryPDO $db, private string $sql) {}
        public function execute(?array $params = null): bool
        {
            [$this->rows, $this->changed] = $this->db->perform($this->sql, $params ?? []);
            $this->pos = 0;
            return true;
        }
        public function fetch(int $mode = PDO::FETCH_DEFAULT, int $cursorOrientation = PDO::FETCH_ORI_NEXT, int $cursorOffset = 0): mixed
        {
            return $this->rows[$this->pos++] ?? false;
        }
        public function fetchColumn(int $column = 0): mixed
        {
            $row = $this->fetch();
            return $row === false ? false : array_values($row)[$column];
        }
        public function fetchAll(int $mode = PDO::FETCH_DEFAULT, mixed ...$args): array { return $this->rows; }
        public function rowCount(): int { return $this->changed; }
    }

    require __DIR__ . '/../src/Repository/BattleRepository.php';
    require __DIR__ . '/../src/Game/BattleDamageService.php';
    require __DIR__ . '/../src/Game/BattleMoveService.php';

    $checks = 0;
    function check(bool $ok, string $name): void
    {
        global $checks;
        $checks++;
        if (!$ok) { throw new RuntimeException('FAIL ' . $name); }
        echo "PASS $name\n";
    }
    function pokemon(string $id): array
    {
        return ['battle_pokemon' => $id, 'names' => $id, 'lvl' => 50, 'hp_my' => 1000, 'hp_max' => 1000,
            'atk' => 120, 'def' => 100, 'satk' => 120, 'sdef' => 100];
    }
    function move(int $id, int $category, int $power, string $name = 'Attack', string $type = 'Normal'): array
    {
        return ['id' => $id, 'atac_id' => $id, 'atac_name' => $name, 'atac_power' => $power,
            'atac_categori' => $category, 'atac_tip' => $type, 'atac_accuracy' => 0, 'critic' => '3'];
    }

    if (in_array('--encoding-guard', $argv, true)) {
        foreach (['src/Game/BattleMoveService.php', 'src/Game/BattleDamageService.php',
            'src/Repository/BattleRepository.php', 'tools/helping_hand_regression.php'] as $path) {
            $bytes = file_get_contents(__DIR__ . '/../' . $path);
            check($bytes !== false && preg_match('//u', $bytes) === 1
                && !str_starts_with($bytes, "\xEF\xBB\xBF"), "UTF-8 without BOM: $path");
        }
        echo "encoding_guard OK\n";
        exit(0);
    }

    try {
        $db = new MemoryPDO();
        $repo = new BattleRepository($db);
        $replay = new BattleReplayRepository();
        $engine = new BattleEngineService();
        $status = new BattleTurnStatusService();
        $damage = new BattleDamageService($repo, new BattleMathService(), $replay, $engine);
        $service = new BattleMoveService($repo, $replay, $damage, $status, $engine);
        $help = move(270, 3, 0, 'Helping Hand');
        $attack = move(10, 1, 60);
        $statusMove = move(20, 3, 0, 'Status');

        // Check both shared entry paths, using distinct battle IDs and the same combatants.
        foreach (['PvE' => 101, 'PvP' => 202] as $mode => $battleId) {
            $user = pokemon('pvp_17');
            $enemy = pokemon('pve_28');
            $service->applyMove($battleId, 1, $user, $enemy, $attack);
            $base = (int) ($replay->damages[array_key_last($replay->damages)]['damage'] ?? 0);
            $rawDamage = (((2 * 50 / 5 + 2) * 60 * 120 / 100) / 50) + 2;
            check($base === (int) floor($rawDamage), "$mode unboosted damage");

            $text = $service->applyMove($battleId, 2, $user, $enemy, $help);
            check(str_contains($text, 'усилена') && $repo->hasBattleVolatile($battleId, $user['battle_pokemon'], 'helping_hand'), "$mode activates on user");
            check(!$repo->hasBattleVolatile($battleId, $enemy['battle_pokemon'], 'helping_hand'), "$mode does not buff opponent");
            $service->applyMove($battleId, 3, $user, $enemy, $statusMove);
            check($repo->hasBattleVolatile($battleId, $user['battle_pokemon'], 'helping_hand'), "$mode status move preserves");

            $engine->miss = true;
            $service->applyMove($battleId, 4, $user, $enemy, $attack);
            $engine->miss = false;
            check($repo->hasBattleVolatile($battleId, $user['battle_pokemon'], 'helping_hand'), "$mode miss preserves");

            $engine->weatherBlock = true;
            $service->applyMove($battleId, 5, $user, $enemy, $attack);
            $engine->weatherBlock = false;
            check($repo->hasBattleVolatile($battleId, $user['battle_pokemon'], 'helping_hand'), "$mode weather block preserves");

            $service->applyMove($battleId, 6, $user, $enemy, move(11, 2, 60, 'Immune', 'Ghost'));
            check($repo->hasBattleVolatile($battleId, $user['battle_pokemon'], 'helping_hand'), "$mode immunity preserves");

            $otherAttacker = $enemy;
            $otherDefender = $user;
            $service->applyMove($battleId, 7, $otherAttacker, $otherDefender, $attack);
            check($repo->hasBattleVolatile($battleId, $user['battle_pokemon'], 'helping_hand'), "$mode opponent cannot consume");
            $service->applyMove($battleId, 7, $user, $enemy, $help);
            check(count(array_filter($db->volatiles, static fn (array $v): bool => $v['battleid'] === $battleId && $v['mess'] === 'helping_hand')) === 1, "$mode cannot stack");
            $service->applyMove($battleId, 8, $user, $enemy, $attack);
            $payload = $replay->damages[array_key_last($replay->damages)];
            check((int) $payload['damage'] === (int) floor($rawDamage * 1.5), "$mode x1.5 with final rounding");
            check((float) ($payload['modifiers']['helping_hand'] ?? 0) === 1.5 && !$repo->hasBattleVolatile($battleId, $user['battle_pokemon'], 'helping_hand'), "$mode consumed after damage");
            $service->applyMove($battleId, 9, $user, $enemy, $attack);
            $payload = $replay->damages[array_key_last($replay->damages)];
            check((int) $payload['damage'] === $base && (float) $payload['modifiers']['helping_hand'] === 1.0, "$mode never reapplies");
        }

        $user = pokemon('pvp_17');
        $enemy = pokemon('pve_28');
        $service->applyMove(303, 1, $user, $enemy, $help);
        check(!$repo->hasBattleVolatile(304, $user['battle_pokemon'], 'helping_hand'), 'battle-scoped, no leakage');
        $status->canAct = false;
        $service->applyMove(304, 1, $user, $enemy, $help);
        $status->canAct = true;
        check(!$repo->hasBattleVolatile(304, $user['battle_pokemon'], 'helping_hand'), 'blocked Helping Hand does not activate');
        $repo->addBattleVolatile(304, $user['battle_pokemon'], 'taunt', 999999);
        $service->applyMove(304, 2, $user, $enemy, $help);
        check(!$repo->hasBattleVolatile(304, $user['battle_pokemon'], 'helping_hand'), 'taunt blocks Helping Hand');

        $repo->finishPvpBattle(303, 42);
        check(!$repo->hasBattleVolatile(303, $user['battle_pokemon'], 'helping_hand'), 'PvP finish clears pending charge');
        $service->applyMove(404, 1, $user, $enemy, $help);
        check($repo->claimPveBattleFinish(404, 42, -1), 'PvE finish claimed');
        check(!$repo->hasBattleVolatile(404, $user['battle_pokemon'], 'helping_hand'), 'PvE finish clears pending charge');

        $service->applyMove(505, 1, $user, $enemy, $help);
        $repo->finishBattle(505, 42, -1);
        check(!$repo->hasBattleVolatile(505, $user['battle_pokemon'], 'helping_hand'), 'other PvE finish clears pending charge');

        // A synthetic stati=2 row verifies the already-fixed Swagger/Flatter target rule.
        // The real move IDs/data are not needed or modified by this regression.
        $effects = $repo->findMoveStatEffects(111);
        check(($effects[0]['target'] ?? '') === 'enemy', 'existing stati=2 targets enemy');

        echo "OK: $checks checks; in-memory PDO only; no DB connection.\n";
    } catch (Throwable $e) {
        fwrite(STDERR, $e->getMessage() . "\n" . $e->getTraceAsString() . "\n");
        exit(1);
    }
}
