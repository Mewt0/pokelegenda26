<?php
declare(strict_types=1);

namespace Pokemon8\Repository;

use PDO;
use Throwable;

final class PokemonEvolutionRepository
{
    public function __construct(private PDO $db)
    {
    }

    public function levelEvolutionTarget(int $baseId, int $level): ?array
    {
        if ($baseId <= 0 || $level <= 0) {
            return null;
        }

        $rule = $this->firstRule(
            'SELECT *
               FROM pokemon_evolution_rules
              WHERE enabled = 1
                AND trigger_type = "level"
                AND from_base_id = :base
                AND level_required > 0
                AND level_required <= :level
              ORDER BY level_required ASC, priority ASC, id ASC
              LIMIT 1',
            ['base' => $baseId, 'level' => $level]
        );
        if ($rule !== null) {
            return $this->formatRule($rule);
        }

        $stmt = $this->db->prepare(
            'SELECT pb.id AS from_base_id, pb.evolution_type AS to_base_id, "level" AS trigger_type,
                    pb.evolution_lvl AS level_required, 0 AS item_id,
                    CONCAT("Уровень ", pb.evolution_lvl) AS condition_text
               FROM poke_base pb
         INNER JOIN poke_base target ON target.id = pb.evolution_type
              WHERE pb.id = :base
                AND pb.evolution_type > 0
                AND pb.evolution_type <> pb.id
                AND pb.evolution_lvl > 0
                AND pb.evolution_lvl <= :level
              LIMIT 1'
        );
        $stmt->execute(['base' => $baseId, 'level' => $level]);
        $fallback = $stmt->fetch(PDO::FETCH_ASSOC);

        return $fallback ? $this->formatRule($fallback) : null;
    }

    public function itemEvolutionTarget(int $baseId, int $itemId, int $level = 0): ?array
    {
        if ($baseId <= 0 || $itemId <= 0) {
            return null;
        }

        $rule = $this->firstRule(
            'SELECT *
               FROM pokemon_evolution_rules
              WHERE enabled = 1
                AND trigger_type = "item"
                AND from_base_id = :base
                AND item_id = :item
                AND (level_required = 0 OR level_required <= :level)
              ORDER BY level_required DESC, priority ASC, id ASC
              LIMIT 1',
            ['base' => $baseId, 'item' => $itemId, 'level' => max(0, $level)]
        );
        if ($rule !== null) {
            return $this->formatRule($rule);
        }

        return null;
    }

    public function applyLevelEvolution(int $userId, int $pokemonId): ?array
    {
        $pokemon = $this->ownedPokemon($userId, $pokemonId);
        if ($pokemon === null) {
            return null;
        }

        $rule = $this->levelEvolutionTarget((int) $pokemon['basenum'], (int) $pokemon['lvl']);
        if ($rule === null) {
            return null;
        }

        return $this->evolveOwnedPokemon($userId, $pokemonId, (int) $rule['toBaseId'], 'level', $rule);
    }

    public function evolveOwnedPokemon(int $userId, int $pokemonId, int $targetBaseId, string $source = 'manual', array $rule = []): ?array
    {
        $pokemon = $this->ownedPokemon($userId, $pokemonId);
        if ($pokemon === null || $targetBaseId <= 0 || (int) $pokemon['basenum'] === $targetBaseId) {
            return null;
        }

        $targetName = $this->basePokemonName($targetBaseId);
        $oldBaseId = (int) $pokemon['basenum'];
        $oldName = trim((string) ($pokemon['names'] ?? '')) ?: $this->basePokemonName($oldBaseId);

        $this->db->prepare(
            'UPDATE pok_user
                SET basenum = :base, names = :name
              WHERE id = :pokemon AND users = :user
              LIMIT 1'
        )->execute([
            'base' => $targetBaseId,
            'name' => $targetName,
            'pokemon' => $pokemonId,
            'user' => $userId,
        ]);

        $this->recalculatePokemonStats($pokemonId, $userId);

        return [
            'pokemonId' => $pokemonId,
            'source' => $source,
            'fromBaseId' => $oldBaseId,
            'toBaseId' => $targetBaseId,
            'fromName' => $oldName,
            'toName' => $targetName,
            'condition' => (string) ($rule['condition'] ?? ''),
        ];
    }

    public function basePokemonName(int $baseId): string
    {
        if ($baseId <= 0) {
            return 'Pokemon';
        }

        $stmt = $this->db->prepare(
            'SELECT p.Name, p.Code, pb.title
               FROM pokemon p
          LEFT JOIN poke_base pb ON pb.id = p.id
              WHERE p.id = :id
              LIMIT 1'
        );
        $stmt->execute(['id' => $baseId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $title = trim((string) ($row['title'] ?? ''));
        $code = preg_replace('/\D+/', '', (string) ($row['Code'] ?? '')) ?: (string) $baseId;
        if ($title !== '') {
            $title = preg_replace('/^#?0*' . preg_quote($code, '/') . '\s*/', '', $title) ?: $title;
            $title = preg_replace('/^#?0*\d+\s*/', '', $title) ?: $title;
            return trim($title) !== '' ? trim($title) : ('Pokemon #' . $baseId);
        }

        $name = trim((string) ($row['Name'] ?? ''));
        return $name !== '' ? $name : ('Pokemon #' . $baseId);
    }

    private function ownedPokemon(int $userId, int $pokemonId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT *
               FROM pok_user
              WHERE id = :pokemon AND users = :user AND active = 1
              LIMIT 1'
        );
        $stmt->execute(['pokemon' => $pokemonId, 'user' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    private function firstRule(string $sql, array $params): ?array
    {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (Throwable) {
            return null;
        }
    }

    private function formatRule(array $row): array
    {
        return [
            'id' => (int) ($row['id'] ?? 0),
            'fromBaseId' => (int) ($row['from_base_id'] ?? 0),
            'toBaseId' => (int) ($row['to_base_id'] ?? 0),
            'triggerType' => (string) ($row['trigger_type'] ?? ''),
            'levelRequired' => (int) ($row['level_required'] ?? 0),
            'itemId' => (int) ($row['item_id'] ?? 0),
            'condition' => (string) ($row['condition_text'] ?? ''),
        ];
    }

    private function recalculatePokemonStats(int $pokemonId, int $userId): void
    {
        $pokemon = $this->ownedPokemon($userId, $pokemonId);
        if ($pokemon === null) {
            return;
        }

        $baseStmt = $this->db->prepare('SELECT hp, atk, def, satk, sdef, speed FROM poke_base WHERE id = :base LIMIT 1');
        $baseStmt->execute(['base' => (int) ($pokemon['basenum'] ?? 0)]);
        $base = $baseStmt->fetch(PDO::FETCH_ASSOC);
        if (!$base) {
            return;
        }

        $harStmt = $this->db->prepare('SELECT atk, def, satk, sdef, speed FROM har WHERE id_har = :har LIMIT 1');
        $harStmt->execute(['har' => (int) ($pokemon['har'] ?? 1)]);
        $har = $harStmt->fetch(PDO::FETCH_ASSOC) ?: ['atk' => 1, 'def' => 1, 'satk' => 1, 'sdef' => 1, 'speed' => 1];

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
}
