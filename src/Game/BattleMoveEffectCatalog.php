<?php
declare(strict_types=1);

namespace Pokemon8\Game;

final class BattleMoveEffectCatalog
{
    public static function key(string $name): string
    {
        $key = strtolower(trim(preg_replace('/[^a-z0-9]+/i', ' ', $name) ?? $name));
        return preg_replace('/\s+/', ' ', $key) ?? $key;
    }

    public static function hazardKind(int $id, string $name): ?string
    {
        return match (self::key($name)) {
            'spikes' => 'spikes',
            'toxic spikes' => 'toxic_spikes',
            'stealth rock', 'stone axe' => 'stealth_rock',
            'sticky web' => 'sticky_web',
            'ceaseless edge' => 'spikes',
            'g max steelsurge' => 'steel_spikes',
            default => match ($id) {
                191 => 'spikes',
                390 => 'toxic_spikes',
                446 => 'stealth_rock',
                default => null,
            },
        };
    }

    /** @return array{kind:string,label:string}|null */
    public static function weather(int $id, string $name): ?array
    {
        return match (self::key($name)) {
            'sunny day' => ['kind' => 'sun', 'label' => 'Солнечная погода'],
            'rain dance' => ['kind' => 'rain', 'label' => 'Дождь'],
            'sandstorm' => ['kind' => 'sandstorm', 'label' => 'Песчаная буря'],
            'hail' => ['kind' => 'hail', 'label' => 'Град'],
            default => match ($id) {
                241 => ['kind' => 'sun', 'label' => 'Солнечная погода'],
                240 => ['kind' => 'rain', 'label' => 'Дождь'],
                201 => ['kind' => 'sandstorm', 'label' => 'Песчаная буря'],
                258 => ['kind' => 'hail', 'label' => 'Град'],
                default => null,
            },
        };
    }

    /** @return list<array{statusId:int,chance:int,target?:string}> */
    public static function secondaryStatuses(int $id, string $name): array
    {
        $key = self::key($name);
        $map = [
            'sludge bomb' => [1, 30],
            'poison jab' => [1, 30],
            'gunk shot' => [1, 30],
            'barb barrage' => [1, 50],
            'sludge wave' => [1, 10],
            'cross poison' => [1, 10],
            'poison fang' => [1, 50],
            'poison tail' => [1, 10],
            'smog' => [1, 40],
            'flamethrower' => [3, 10],
            'fire blast' => [3, 10],
            'flare blitz' => [3, 10],
            'lava plume' => [3, 30],
            'burning jealousy' => [3, 100],
            'scald' => [3, 30],
            'scorching sands' => [3, 30],
            'inferno' => [3, 100],
            'heat crash' => [3, 30],
            'fire punch' => [3, 10],
            'sacred fire' => [3, 50],
            'blue flare' => [3, 20],
            'sizzly slide' => [3, 100],
            'matcha gotcha' => [3, 20],
            'nuzzle' => [5, 100],
            'zap cannon' => [5, 100],
            'blizzard' => [4, 10],
            'ice beam' => [4, 10],
            'freeze dry' => [4, 10],
            'confuse ray' => [7, 100],
            'sweet kiss' => [7, 100],
            'teeter dance' => [7, 100],
            'swagger' => [7, 100],
            'flatter' => [7, 100],
            'g max befuddle' => [7, 100],
        ];
        if (!isset($map[$key])) {
            return [];
        }

        return [[
            'statusId' => $map[$key][0],
            'chance' => $map[$key][1],
            'target' => 'enemy',
        ]];
    }

    /** @return array{statusId:int,chance:int}|null */
    public static function primaryStatus(int $id, string $name): ?array
    {
        $map = [
            'toxic' => [1, 100],
            'poison powder' => [1, 100],
            'poison gas' => [1, 100],
            'toxic thread' => [1, 100],
            'mortal spin' => [1, 100],
            'baneful bunker' => [1, 100],
            'will o wisp' => [3, 100],
            'thunder wave' => [5, 100],
            'stun spore' => [5, 100],
            'glare' => [5, 100],
            'spore' => [2, 100],
            'sleep powder' => [2, 100],
            'hypnosis' => [2, 100],
            'dark void' => [2, 100],
            'lovely kiss' => [2, 100],
            'sing' => [2, 100],
            'grass whistle' => [2, 100],
            'leech seed' => [8, 100],
        ];
        $key = self::key($name);
        if (!isset($map[$key])) {
            return null;
        }

        return ['statusId' => $map[$key][0], 'chance' => $map[$key][1]];
    }

    /** @return list<array{target:string,kind:string,field:string,delta:int,label:string}> */
    public static function statEffects(string $name): array
    {
        $map = [
            'toxic thread' => [['target' => 'enemy', 'kind' => 'minus', 'field' => 'speed', 'delta' => 1, 'label' => 'Скорость']],
            'growl' => [['target' => 'enemy', 'kind' => 'minus', 'field' => 'attac', 'delta' => 1, 'label' => 'Атака']],
            'tail whip' => [['target' => 'enemy', 'kind' => 'minus', 'field' => 'defend', 'delta' => 1, 'label' => 'Защита']],
            'leer' => [['target' => 'enemy', 'kind' => 'minus', 'field' => 'defend', 'delta' => 1, 'label' => 'Защита']],
            'string shot' => [['target' => 'enemy', 'kind' => 'minus', 'field' => 'speed', 'delta' => 2, 'label' => 'Скорость']],
            'screech' => [['target' => 'enemy', 'kind' => 'minus', 'field' => 'defend', 'delta' => 2, 'label' => 'Защита']],
            'charm' => [['target' => 'enemy', 'kind' => 'minus', 'field' => 'attac', 'delta' => 2, 'label' => 'Атака']],
            'fake tears' => [['target' => 'enemy', 'kind' => 'minus', 'field' => 'spdefend', 'delta' => 2, 'label' => 'Спец. защита']],
            'metal sound' => [['target' => 'enemy', 'kind' => 'minus', 'field' => 'spdefend', 'delta' => 2, 'label' => 'Спец. защита']],
            'sand attack' => [['target' => 'enemy', 'kind' => 'minus', 'field' => 'accuracy', 'delta' => 1, 'label' => 'Точность']],
            'smokescreen' => [['target' => 'enemy', 'kind' => 'minus', 'field' => 'accuracy', 'delta' => 1, 'label' => 'Точность']],
            'sweet scent' => [['target' => 'enemy', 'kind' => 'minus', 'field' => 'acc', 'delta' => 2, 'label' => 'Ловкость']],
            'swagger' => [['target' => 'enemy', 'kind' => 'plus', 'field' => 'attac', 'delta' => 2, 'label' => 'Атака']],
            'flatter' => [['target' => 'enemy', 'kind' => 'plus', 'field' => 'spattac', 'delta' => 1, 'label' => 'Спец. атака']],
            'swords dance' => [['target' => 'self', 'kind' => 'plus', 'field' => 'attac', 'delta' => 2, 'label' => 'Атака']],
            'calm mind' => [
                ['target' => 'self', 'kind' => 'plus', 'field' => 'spattac', 'delta' => 1, 'label' => 'Спец. атака'],
                ['target' => 'self', 'kind' => 'plus', 'field' => 'spdefend', 'delta' => 1, 'label' => 'Спец. защита'],
            ],
            'nasty plot' => [['target' => 'self', 'kind' => 'plus', 'field' => 'spattac', 'delta' => 2, 'label' => 'Спец. атака']],
            'agility' => [['target' => 'self', 'kind' => 'plus', 'field' => 'speed', 'delta' => 2, 'label' => 'Скорость']],
            'iron defense' => [['target' => 'self', 'kind' => 'plus', 'field' => 'defend', 'delta' => 2, 'label' => 'Защита']],
            'harden' => [['target' => 'self', 'kind' => 'plus', 'field' => 'defend', 'delta' => 1, 'label' => 'Защита']],
            'defense curl' => [['target' => 'self', 'kind' => 'plus', 'field' => 'defend', 'delta' => 1, 'label' => 'Защита']],
            'bulk up' => [
                ['target' => 'self', 'kind' => 'plus', 'field' => 'attac', 'delta' => 1, 'label' => 'Атака'],
                ['target' => 'self', 'kind' => 'plus', 'field' => 'defend', 'delta' => 1, 'label' => 'Защита'],
            ],
            'dragon dance' => [
                ['target' => 'self', 'kind' => 'plus', 'field' => 'attac', 'delta' => 1, 'label' => 'Атака'],
                ['target' => 'self', 'kind' => 'plus', 'field' => 'speed', 'delta' => 1, 'label' => 'Скорость'],
            ],
            'double team' => [['target' => 'self', 'kind' => 'plus', 'field' => 'acc', 'delta' => 1, 'label' => 'Ловкость']],
            'growth' => [
                ['target' => 'self', 'kind' => 'plus', 'field' => 'attac', 'delta' => 1, 'label' => 'Атака'],
                ['target' => 'self', 'kind' => 'plus', 'field' => 'spattac', 'delta' => 1, 'label' => 'Спец. атака'],
            ],
        ];

        return $map[self::key($name)] ?? [];
    }

    public static function trapKind(int $id, string $name): ?string
    {
        return match (self::key($name)) {
            'mean look', 'spider web', 'block', 'anchor shot', 'spirit shackle', 'jaw lock', 'fairy lock' => 'trap',
            'fire spin', 'whirlpool', 'sand tomb', 'magma storm', 'infestation', 'bind', 'wrap', 'clamp' => 'partial_trap',
            default => null,
        };
    }

    public static function sideFieldKind(int $id, string $name): ?string
    {
        return match (self::key($name)) {
            'g max cannonade' => 'gmax_cannonade',
            'g max vine lash' => 'gmax_vine_lash',
            default => null,
        };
    }

    public static function volatileKind(int $id, string $name): ?string
    {
        return match (self::key($name)) {
            'nightmare' => 'nightmare',
            'perish song' => 'perish_song',
            'destiny bond' => 'destiny_bond',
            'taunt' => 'taunt',
            'encore' => 'encore',
            'torment' => 'torment',
            'disable' => 'disable',
            'knock off' => 'knock_off',
            default => null,
        };
    }

    public static function recoilKind(int $id, string $name): ?string
    {
        return match (self::key($name)) {
            'double edge', 'brave bird', 'wood hammer', 'wave crash', 'volt tackle', 'wild charge', 'flare blitz',
            'submission', 'take down' => 'third',
            'head smash' => 'half_damage',
            'steel beam', 'mind blown', 'chloroblast' => 'half_max',
            'jump kick', 'high jump kick' => 'crash_on_miss',
            default => null,
        };
    }
}
