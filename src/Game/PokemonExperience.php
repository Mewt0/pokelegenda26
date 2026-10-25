<?php
declare(strict_types=1);

namespace Pokemon8\Game;

/**
 * Cumulative XP thresholds shared by rewards and API presentation.
 *
 * Since curve v2 (2026-10-25) the per-growth-group contract is the default:
 * without an explicit group the medium_fast anchor is used, while callers that
 * know the species must pass its group (or use progressForBaseId()). The old
 * single curve remains available as legacyLevelThreshold()/legacyCumulative()
 * for the one-time data migration only.
 */
final class PokemonExperience
{
    public const VERSION = PokemonGrowthCurve::VERSION;

    public static function levelThreshold(int $level, ?string $group = null): int
    {
        return PokemonGrowthCurve::levelThreshold($level, $group ?? PokemonGrowthGroup::DEFAULT);
    }

    public static function legacyLevelThreshold(int $level): int
    {
        return (int) round(60 * exp(2 + max(0, $level) / 10) - 50);
    }

    /**
     * @return array<string,mixed>
     */
    public static function progress(int $level, int $experience, ?string $group = null): array
    {
        return PokemonGrowthCurve::progress($level, $experience, $group ?? PokemonGrowthGroup::DEFAULT);
    }

    /**
     * @return array<string,mixed>
     */
    public static function progressForBaseId(int $baseId, int $level, int $experience): array
    {
        return PokemonGrowthCurve::progressForBaseId($baseId, $level, $experience);
    }

    public static function curveVersion(): int
    {
        return PokemonGrowthCurve::VERSION;
    }

    public static function groupForBaseId(int $baseId): string
    {
        return PokemonGrowthGroup::forBaseId($baseId);
    }
}
