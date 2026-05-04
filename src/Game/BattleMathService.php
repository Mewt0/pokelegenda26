<?php
declare(strict_types=1);

namespace Pokemon8\Game;

/**
 * Pure battle math. No DB, no HTML.
 * Stages in this project are stored separately as plus/minus rows in statpokemonbatle.
 */
final class BattleMathService
{
    /** @var array<string, array<string, float>> */
    private array $typeChart = [
        'normal' => ['rock' => 0.5, 'ghost' => 0.0, 'steel' => 0.5],
        'fire' => ['fire' => 0.5, 'water' => 0.5, 'grass' => 2.0, 'ice' => 2.0, 'bug' => 2.0, 'rock' => 0.5, 'dragon' => 0.5, 'steel' => 2.0],
        'water' => ['fire' => 2.0, 'water' => 0.5, 'grass' => 0.5, 'ground' => 2.0, 'rock' => 2.0, 'dragon' => 0.5],
        'electric' => ['water' => 2.0, 'electric' => 0.5, 'grass' => 0.5, 'ground' => 0.0, 'flying' => 2.0, 'dragon' => 0.5],
        'grass' => ['fire' => 0.5, 'water' => 2.0, 'grass' => 0.5, 'poison' => 0.5, 'ground' => 2.0, 'flying' => 0.5, 'bug' => 0.5, 'rock' => 2.0, 'dragon' => 0.5, 'steel' => 0.5],
        'ice' => ['water' => 0.5, 'grass' => 2.0, 'ground' => 2.0, 'flying' => 2.0, 'dragon' => 2.0, 'steel' => 0.5, 'fire' => 0.5, 'ice' => 0.5],
        'fighting' => ['normal' => 2.0, 'ice' => 2.0, 'rock' => 2.0, 'dark' => 2.0, 'steel' => 2.0, 'poison' => 0.5, 'flying' => 0.5, 'psychic' => 0.5, 'bug' => 0.5, 'fairy' => 0.5, 'ghost' => 0.0],
        'poison' => ['grass' => 2.0, 'fairy' => 2.0, 'poison' => 0.5, 'ground' => 0.5, 'rock' => 0.5, 'ghost' => 0.5, 'steel' => 0.0],
        'ground' => ['fire' => 2.0, 'electric' => 2.0, 'poison' => 2.0, 'rock' => 2.0, 'steel' => 2.0, 'grass' => 0.5, 'bug' => 0.5, 'flying' => 0.0],
        'flying' => ['grass' => 2.0, 'fighting' => 2.0, 'bug' => 2.0, 'electric' => 0.5, 'rock' => 0.5, 'steel' => 0.5],
        'psychic' => ['fighting' => 2.0, 'poison' => 2.0, 'psychic' => 0.5, 'steel' => 0.5, 'dark' => 0.0],
        'bug' => ['grass' => 2.0, 'psychic' => 2.0, 'dark' => 2.0, 'fire' => 0.5, 'fighting' => 0.5, 'poison' => 0.5, 'flying' => 0.5, 'ghost' => 0.5, 'steel' => 0.5, 'fairy' => 0.5],
        'rock' => ['fire' => 2.0, 'ice' => 2.0, 'flying' => 2.0, 'bug' => 2.0, 'fighting' => 0.5, 'ground' => 0.5, 'steel' => 0.5],
        'ghost' => ['psychic' => 2.0, 'ghost' => 2.0, 'dark' => 0.5, 'normal' => 0.0],
        'dragon' => ['dragon' => 2.0, 'steel' => 0.5, 'fairy' => 0.0],
        'dark' => ['psychic' => 2.0, 'ghost' => 2.0, 'fighting' => 0.5, 'dark' => 0.5, 'fairy' => 0.5],
        'steel' => ['rock' => 2.0, 'ice' => 2.0, 'fairy' => 2.0, 'fire' => 0.5, 'water' => 0.5, 'electric' => 0.5, 'steel' => 0.5],
        'fairy' => ['fighting' => 2.0, 'dragon' => 2.0, 'dark' => 2.0, 'fire' => 0.5, 'poison' => 0.5, 'steel' => 0.5],
    ];

    public function clampStage(int|float $value): int
    {
        return max(0, min(6, (int) round((float) $value)));
    }

    public function statMultiplier(int $plus, int $minus): float
    {
        $plus = $this->clampStage($plus);
        $minus = $this->clampStage($minus);
        return (2.0 + $plus) / (2.0 + $minus);
    }

    public function effectiveStat(int|float $base, int $plus, int $minus): int
    {
        return max(1, (int) round(max(1.0, (float) $base) * $this->statMultiplier($plus, $minus)));
    }

    public function accuracyChance(int|float $baseAccuracy, array $attackerStages, array $defenderStages): int
    {
        // Legacy DB rule: atac_accuracy = 0 means the move never misses.
        if ((float) $baseAccuracy <= 0.0) {
            return 100;
        }
        $base = max(1.0, min(100.0, (float) $baseAccuracy));
        $attPlus = $this->clampStage($attackerStages['plus']['accuracy'] ?? 0);
        $attMinus = $this->clampStage($attackerStages['minus']['accuracy'] ?? 0);
        $defAccPlus = $this->clampStage($defenderStages['plus']['acc'] ?? 0);
        $defAccMinus = $this->clampStage($defenderStages['minus']['acc'] ?? 0);

        // Pokemon-style accuracy/evasion uses 3 as the base, not 2.
        $chance = $base * ((3.0 + $attPlus + $defAccMinus) / (3.0 + $attMinus + $defAccPlus));
        return max(1, min(100, (int) round($chance)));
    }

    /** @param list<string> $defenderTypes */
    public function typeEffectiveness(string $attackType, array $defenderTypes): float
    {
        $attack = $this->normalizeType($attackType);
        if ($attack === '') {
            return 1.0;
        }

        $multi = 1.0;
        foreach ($defenderTypes as $type) {
            $def = $this->normalizeType($type);
            if ($def === '' || $def === 'none') {
                continue;
            }
            $multi *= $this->typeChart[$attack][$def] ?? 1.0;
        }
        return $multi;
    }

    /** @param list<string> $attackerTypes */
    public function stab(string $attackType, array $attackerTypes): float
    {
        $attack = $this->normalizeType($attackType);
        foreach ($attackerTypes as $type) {
            if ($attack !== '' && $attack === $this->normalizeType($type)) {
                return 1.5;
            }
        }
        return 1.0;
    }

    /** @return list<string> */
    public function typeLabelsFromPokemon(array $pokemon): array
    {
        $types = [];
        foreach (['Element', 'SubElement', 'element', 'subelement', 'tips', 'tip', 'type'] as $field) {
            $value = trim((string) ($pokemon[$field] ?? ''));
            if ($value !== '' && strtolower($value) !== 'normal') {
                $types[] = $value;
            }
        }
        if ($types === []) {
            $tips = trim((string) ($pokemon['tips'] ?? ''));
            $types[] = $tips !== '' ? $tips : 'Normal';
        }

        $clean = [];
        foreach ($types as $type) {
            $norm = $this->normalizeType($type);
            if ($norm !== '' && $norm !== 'none' && !in_array($norm, $clean, true)) {
                $clean[] = $norm;
            }
        }
        return $clean === [] ? ['normal'] : $clean;
    }

    public function normalizeType(string $type): string
    {
        $type = strtolower(trim($type));
        $type = str_replace([' ', '_'], '', $type);
        return match ($type) {
            'fight', 'fighting' => 'fighting',
            'psy', 'psychic' => 'psychic',
            'electr', 'electric' => 'electric',
            'ghosts', 'ghost' => 'ghost',
            'none', 'null', '0', '-' => 'none',
            default => $type,
        };
    }

    public function typeMessage(float $effectiveness): string
    {
        if ($effectiveness <= 0.0) {
            return 'Нет эффекта.';
        }
        if ($effectiveness >= 4.0) {
            return 'Это крайне эффективно!';
        }
        if ($effectiveness >= 2.0) {
            return 'Это эффективно!';
        }
        if ($effectiveness <= 0.25) {
            return 'Это почти неэффективно.';
        }
        if ($effectiveness < 1.0) {
            return 'Это не очень эффективно.';
        }
        return '';
    }
}
