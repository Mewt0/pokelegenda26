<?php
declare(strict_types=1);

namespace Pokemon8\Game;

/**
 * Resolves a base species (or battle form) to its canonical experience group.
 *
 * The mapping is generated from PokeAPI data; see PokemonGrowthData::SOURCE for
 * provenance and the checked-in snapshot hashes. Unknown ids deliberately fall
 * back to medium_fast and are marked known=false so callers/UI can disclose it.
 */
final class PokemonGrowthGroup
{
    public const FAST = 'fast';
    public const MEDIUM_FAST = 'medium_fast';
    public const MEDIUM_SLOW = 'medium_slow';
    public const SLOW = 'slow';
    public const ERRATIC = 'erratic';
    public const FLUCTUATING = 'fluctuating';

    public const DEFAULT = self::MEDIUM_FAST;

    /** @var array<string,string> */
    private const LABELS = [
        self::FAST => 'Быстрая',
        self::MEDIUM_FAST => 'Средняя',
        self::MEDIUM_SLOW => 'Средне-медленная',
        self::SLOW => 'Медленная',
        self::ERRATIC => 'Хаотичная',
        self::FLUCTUATING => 'Колеблющаяся',
    ];

    /**
     * @return array{group:string,label:string,known:bool,source:string,baseId:int,formId:int}
     */
    public static function resolve(int $baseId): array
    {
        $formId = $baseId;
        $resolvedBaseId = $baseId;
        $source = 'species';

        if (isset(PokemonGrowthData::FORM_BASE[$baseId])) {
            $resolvedBaseId = (int) PokemonGrowthData::FORM_BASE[$baseId];
            $source = 'form_species';
        }

        $group = PokemonGrowthData::SPECIES_GROUPS[$resolvedBaseId] ?? null;
        $known = is_string($group) && isset(self::LABELS[$group]);
        if (!$known) {
            $group = self::DEFAULT;
            $source = 'fallback_medium_fast';
        }

        return [
            'group' => $group,
            'label' => self::LABELS[$group],
            'known' => $known,
            'source' => $source,
            'baseId' => $resolvedBaseId,
            'formId' => $formId,
        ];
    }

    public static function forBaseId(int $baseId): string
    {
        return self::resolve($baseId)['group'];
    }

    public static function label(string $group): string
    {
        return self::LABELS[$group] ?? self::LABELS[self::DEFAULT];
    }

    /** @return array<string,string> */
    public static function labels(): array
    {
        return self::LABELS;
    }

    public static function isKnown(string $group): bool
    {
        return isset(self::LABELS[$group]);
    }

    public static function normalize(string $group): string
    {
        return self::isKnown($group) ? $group : self::DEFAULT;
    }
}
