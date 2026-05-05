<?php
declare(strict_types=1);

namespace Pokemon8\Game;

final class BattleAbilityCatalog
{
    public static function key(array $pokemon): string
    {
        $raw = (string) (
            ($pokemon['ability_key'] ?? '')
            ?: ($pokemon['base_ability_key'] ?? '')
            ?: ($pokemon['ability'] ?? '')
            ?: ($pokemon['base_ability'] ?? '')
        );
        return self::normalize($raw);
    }

    public static function normalize(string $ability): string
    {
        $key = mb_strtolower(trim($ability), 'UTF-8');
        $key = str_replace('ё', 'е', $key);
        $key = preg_replace('/[^a-zа-я0-9]+/u', '_', $key) ?? $key;
        $key = trim($key, '_');

        return match ($key) {
            'sand_rush', 'широкие_лапы', 'песчаный_рывок' => 'sand_rush',
            'slush_rush', 'скольжение', 'снежное_скольжение' => 'slush_rush',
            'chlorophyll', 'хлорофилл' => 'chlorophyll',
            'swift_swim', 'водоплавающий', 'плавание' => 'swift_swim',
            'sand_force', 'песочник', 'сила_песка' => 'sand_force',
            'rain_dish', 'дождефаг', 'дождевая_чаша' => 'rain_dish',
            'ice_body', 'ледяное_тело' => 'ice_body',
            'dry_skin', 'сухая_кожа' => 'dry_skin',
            'solar_power', 'солнечная_батарея' => 'solar_power',
            'leaf_guard', 'лиственный_щит' => 'leaf_guard',
            'forecast', 'метеочувствительность', 'прогноз' => 'forecast',
            'air_lock', 'cloud_nine', 'штиль', 'облако_девять' => 'weather_lock',
            default => $key,
        };
    }

    public static function startsWeather(string $ability): ?string
    {
        return match (self::normalize($ability)) {
            'drizzle', 'морось' => 'rain',
            'drought', 'засуха' => 'sun',
            'sand_stream', 'песчаный_поток' => 'sandstorm',
            'snow_warning', 'snow_warn', 'hail_warning', 'снежное_предупреждение', 'град' => 'hail',
            default => null,
        };
    }

    public static function speedMultiplier(string $ability, string $weather): float
    {
        return match (self::normalize($ability)) {
            'sand_rush' => $weather === 'sandstorm' ? 2.0 : 1.0,
            'slush_rush' => $weather === 'hail' ? 2.0 : 1.0,
            'chlorophyll' => $weather === 'sun' ? 2.0 : 1.0,
            'swift_swim' => $weather === 'rain' ? 2.0 : 1.0,
            default => 1.0,
        };
    }

    public static function weatherDamageImmune(string $ability, string $weather): bool
    {
        return match (self::normalize($ability)) {
            'sand_rush' => $weather === 'sandstorm',
            'slush_rush' => $weather === 'hail',
            default => false,
        };
    }

    public static function weatherAttackMultiplier(string $ability, string $weather, string $moveType): float
    {
        $type = strtolower($moveType);
        if (self::normalize($ability) === 'sand_force' && $weather === 'sandstorm' && in_array($type, ['steel', 'rock', 'ground'], true)) {
            return 1.3;
        }
        return 1.0;
    }

    public static function forecastType(string $ability, string $weather): ?string
    {
        if (self::normalize($ability) !== 'forecast') {
            return null;
        }
        return match ($weather) {
            'sun' => 'fire',
            'rain' => 'water',
            'hail' => 'ice',
            default => 'normal',
        };
    }
}
