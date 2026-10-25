<?php
declare(strict_types=1);

namespace Pokemon8\Game;

/**
 * Per-growth-group cumulative XP curve used by battle rewards, UI progress and
 * candy level changes.
 *
 * The official lookup tables are preserved in shape and rescaled to a
 * PokemonChic target total. Medium Fast is the balance anchor (4-6 active hours
 * from level 5 to 100 with the real encounter algorithm, whose average enemy is
 * ~32-34, not the hypothetical L50-55 spawn); the other groups keep their
 * official ordering and relative shape. Slow and fluctuating totals are
 * intentionally clamped so the hardest group stays below a ~7.5 hour
 * single-Pokemon journey instead of inheriting the official 1.64x outlier.
 *
 * This class does not change any existing call site by itself. The legacy
 * formula remains available through legacyCumulative() until the coordinated
 * migration is applied.
 */
final class PokemonGrowthCurve
{
    public const VERSION = 2;
    public const MIN_LEVEL = 1;
    public const MAX_LEVEL = 100;

    /** Balance anchor: cumulative XP for medium_fast from level 1 to 100. */
    public const ANCHOR_TOTAL = 128000;

    /** @var array<string,int> */
    private const TARGET_TOTALS = [
        PokemonGrowthGroup::FAST => 102400,
        PokemonGrowthGroup::MEDIUM_FAST => 128000,
        PokemonGrowthGroup::MEDIUM_SLOW => 135680,
        PokemonGrowthGroup::SLOW => 156160,
        PokemonGrowthGroup::ERRATIC => 83200,
        PokemonGrowthGroup::FLUCTUATING => 168960,
    ];

    /** @var array<string,array<int,int>> */
    private static array $scaled = [];

    /** @return array<string,int> */
    public static function targetTotals(): array
    {
        return self::TARGET_TOTALS;
    }

    /**
     * Total XP required to reach $level (level 1 = 0, level 100 = target total).
     */
    public static function cumulative(int $level, string $group = PokemonGrowthGroup::DEFAULT): int
    {
        $group = PokemonGrowthGroup::normalize($group);
        $level = max(self::MIN_LEVEL, min(self::MAX_LEVEL, $level));
        return self::scaledCurve($group)[$level];
    }

    /**
     * XP required to advance from $level to $level + 1. Mirrors the old
     * PokemonExperience::levelThreshold() contract; level 100 returns the cap.
     */
    public static function levelThreshold(int $level, string $group = PokemonGrowthGroup::DEFAULT): int
    {
        $level = max(self::MIN_LEVEL, min(self::MAX_LEVEL, $level));
        return self::cumulative($level >= self::MAX_LEVEL ? self::MAX_LEVEL : $level + 1, $group);
    }

    /**
     * Same response contract as PokemonExperience::progress(), plus the group
     * metadata needed by the UI to explain why species level at different rates.
     *
     * @return array<string,mixed>
     */
    public static function progress(int $level, int $experience, string $group = PokemonGrowthGroup::DEFAULT): array
    {
        $group = PokemonGrowthGroup::normalize($group);
        $level = max(self::MIN_LEVEL, min(self::MAX_LEVEL, $level));
        $experience = max(0, $experience);
        $current = self::cumulative($level, $group);
        $atCap = $level >= self::MAX_LEVEL;
        $next = $atCap ? $current : self::cumulative($level + 1, $group);
        $width = max(1, $next - $current);
        $percent = $atCap ? 100.0 : max(0.0, min(100.0, 100 * ($experience - $current) / $width));

        return [
            'exp' => $experience,
            'expCurrentLevel' => $current,
            'expNextLevel' => $next,
            'expProgressPercent' => !$atCap && $experience < $next ? min(99.99, round($percent, 2)) : round($percent, 2),
            'expRemaining' => $atCap ? 0 : max(0, $next - $experience),
            'expAtLevelCap' => $atCap,
            'growthGroup' => $group,
            'growthGroupLabel' => PokemonGrowthGroup::label($group),
            'growthCurveVersion' => self::VERSION,
        ];
    }

    /**
     * Convenience for repositories: resolve the base/form group and expose the
     * provenance fields next to the usual progress contract.
     *
     * @return array<string,mixed>
     */
    public static function progressForBaseId(int $baseId, int $level, int $experience): array
    {
        $resolved = PokemonGrowthGroup::resolve($baseId);
        return self::progress($level, $experience, $resolved['group']) + [
            'growthGroupKnown' => $resolved['known'],
            'growthGroupSource' => $resolved['source'],
            'growthBaseId' => $resolved['baseId'],
        ];
    }

    public static function levelForExperience(int $experience, string $group = PokemonGrowthGroup::DEFAULT): int
    {
        $group = PokemonGrowthGroup::normalize($group);
        $experience = max(0, $experience);
        $curve = self::scaledCurve($group);
        $level = 1;
        for ($candidate = 2; $candidate <= self::MAX_LEVEL; $candidate++) {
            if ($experience < $curve[$candidate]) {
                break;
            }
            $level = $candidate;
        }
        return $level;
    }

    /**
     * Cumulative threshold of the legacy round(60*exp(2+L/10)-50) curve.
     * Kept for one-time migration of existing rows.
     */
    public static function legacyCumulative(int $level): int
    {
        $level = max(self::MIN_LEVEL, min(self::MAX_LEVEL, $level));
        return $level === 1 ? 0 : (int) round(60 * exp(2 + ($level - 1) / 10) - 50);
    }

    public static function legacyLevelThreshold(int $level): int
    {
        $level = max(self::MIN_LEVEL, min(self::MAX_LEVEL, $level));
        return self::legacyCumulative($level >= self::MAX_LEVEL ? self::MAX_LEVEL : $level + 1);
    }

    /**
     * Candy rule: a level granted outside battle must not leave the Pokemon
     * below the XP floor of its new level. Existing XP above that floor is kept;
     * no extra progress is invented, and level 100 lands exactly on the cap.
     */
    public static function experienceAfterLevelGrant(int $newLevel, int $currentExperience, string $group = PokemonGrowthGroup::DEFAULT): int
    {
        $group = PokemonGrowthGroup::normalize($group);
        $newLevel = max(self::MIN_LEVEL, min(self::MAX_LEVEL, $newLevel));
        return max(max(0, $currentExperience), self::cumulative($newLevel, $group));
    }

    /**
     * Normalizes an experience value that may predate curve v2 (or come from a QA
     * fixture) before more XP is granted:
     *  - XP below the floor of the current level (candy/fixture debt) is raised to
     *    the floor, never lowering the level;
     *  - XP at/above the next-level border is clamped through the legacy remap so
     *    a not-yet-migrated row cannot jump straight to the cap. After the data
     *    migration this branch is unreachable for valid rows.
     */
    public static function normalizeStoredExperience(int $level, int $experience, string $group = PokemonGrowthGroup::DEFAULT): int
    {
        $group = PokemonGrowthGroup::normalize($group);
        $level = max(self::MIN_LEVEL, min(self::MAX_LEVEL, $level));
        $experience = max(0, $experience);
        if ($level >= self::MAX_LEVEL) {
            return self::cumulative(self::MAX_LEVEL, $group);
        }

        $current = self::cumulative($level, $group);
        $next = self::cumulative($level + 1, $group);
        if ($experience < $current) {
            return $current;
        }
        if ($experience >= $next) {
            return self::remapLegacyExperience($level, $experience, $group)['exp'];
        }

        return $experience;
    }

    /**
     * Converts an existing row without lowering its level. The relative position
     * inside the current level is preserved; a negative legacy position (candy
     * debt) becomes the start of the current level. Overflow above the old
     * next-level border is clamped to the end of the current level and reported
     * so the migration can list it for review; it never auto-levels or resets.
     *
     * @return array{level:int,exp:int,fraction:float,legacyFraction:float,debtClamped:bool,overflowClamped:bool,group:string}
     */
    public static function remapLegacyExperience(int $level, int $oldExperience, string $group = PokemonGrowthGroup::DEFAULT): array
    {
        $group = PokemonGrowthGroup::normalize($group);
        $level = max(self::MIN_LEVEL, min(self::MAX_LEVEL, $level));
        $oldExperience = max(0, $oldExperience);

        if ($level >= self::MAX_LEVEL) {
            return [
                'level' => self::MAX_LEVEL,
                'exp' => self::cumulative(self::MAX_LEVEL, $group),
                'fraction' => 1.0,
                'legacyFraction' => 1.0,
                'debtClamped' => false,
                'overflowClamped' => false,
                'group' => $group,
            ];
        }

        $legacyCurrent = self::legacyCumulative($level);
        $legacyNext = self::legacyCumulative($level + 1);
        $legacyWidth = max(1, $legacyNext - $legacyCurrent);
        $legacyFraction = ($oldExperience - $legacyCurrent) / $legacyWidth;
        $debtClamped = $legacyFraction < 0.0;
        $overflowClamped = $legacyFraction > 1.0;
        $fraction = max(0.0, min(1.0, $legacyFraction));

        $current = self::cumulative($level, $group);
        $next = self::cumulative($level + 1, $group);
        $newExp = $current + (int) round($fraction * max(1, $next - $current));
        if ($newExp >= $next) {
            $newExp = $next - 1;
        }

        return [
            'level' => $level,
            'exp' => max($current, $newExp),
            'fraction' => round($fraction, 6),
            'legacyFraction' => round($legacyFraction, 6),
            'debtClamped' => $debtClamped,
            'overflowClamped' => $overflowClamped,
            'group' => $group,
        ];
    }

    /** @return array<int,int> */
    private static function scaledCurve(string $group): array
    {
        if (isset(self::$scaled[$group])) {
            return self::$scaled[$group];
        }

        $official = PokemonGrowthData::OFFICIAL_CURVES[$group] ?? PokemonGrowthData::OFFICIAL_CURVES[PokemonGrowthGroup::DEFAULT];
        $officialTotal = max(1, (int) $official[self::MAX_LEVEL]);
        $target = self::TARGET_TOTALS[$group] ?? self::TARGET_TOTALS[PokemonGrowthGroup::DEFAULT];

        $curve = [self::MIN_LEVEL => 0];
        for ($level = 2; $level <= self::MAX_LEVEL; $level++) {
            $scaled = (int) round(((int) $official[$level]) * $target / $officialTotal);
            $curve[$level] = max($scaled, $curve[$level - 1] + 1);
        }
        // Keep the anchor exact after monotonic correction.
        $curve[self::MAX_LEVEL] = $target;
        if ($curve[self::MAX_LEVEL] <= $curve[self::MAX_LEVEL - 1]) {
            $curve[self::MAX_LEVEL - 1] = $target - 1;
        }

        return self::$scaled[$group] = $curve;
    }
}
