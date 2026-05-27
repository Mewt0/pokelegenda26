<?php
declare(strict_types=1);

namespace Pokemon8\Game;

final class PokemonFormCatalog
{
    /**
     * Internal 5000+ ids are form/base rows. Their public dex number remains
     * the original Pokemon number, while battle/dex logic can still distinguish
     * the exact form by id and key.
     *
     * @return array<int,array{baseId:int,key:string,label:string}>
     */
    public static function forms(): array
    {
        return [
            5000 => ['baseId' => 1, 'key' => 'mega', 'label' => 'Mega Venusaur'],
            5001 => ['baseId' => 6, 'key' => 'mega_y', 'label' => 'Mega Charizard Y'],
            5002 => ['baseId' => 6, 'key' => 'mega_x', 'label' => 'Mega Charizard X'],
            5003 => ['baseId' => 9, 'key' => 'mega', 'label' => 'Mega Blastoise'],
            5004 => ['baseId' => 65, 'key' => 'mega', 'label' => 'Mega Alakazam'],
            5005 => ['baseId' => 94, 'key' => 'mega', 'label' => 'Mega Gengar'],
            5006 => ['baseId' => 115, 'key' => 'mega', 'label' => 'Mega Kangaskhan'],
            5007 => ['baseId' => 127, 'key' => 'mega', 'label' => 'Mega Pinsir'],
            5008 => ['baseId' => 130, 'key' => 'mega', 'label' => 'Mega Gyarados'],
            5009 => ['baseId' => 142, 'key' => 'mega', 'label' => 'Mega Aerodactyl'],
            5010 => ['baseId' => 150, 'key' => 'mega_x', 'label' => 'Mega Mewtwo X'],
            5011 => ['baseId' => 150, 'key' => 'mega_y', 'label' => 'Mega Mewtwo Y'],
            5012 => ['baseId' => 181, 'key' => 'mega', 'label' => 'Mega Ampharos'],
            5013 => ['baseId' => 212, 'key' => 'mega', 'label' => 'Mega Scizor'],
            5014 => ['baseId' => 214, 'key' => 'mega', 'label' => 'Mega Heracross'],
            5015 => ['baseId' => 229, 'key' => 'mega', 'label' => 'Mega Houndoom'],
            5016 => ['baseId' => 248, 'key' => 'mega', 'label' => 'Mega Tyranitar'],
            5017 => ['baseId' => 382, 'key' => 'primal', 'label' => 'Primal Kyogre'],
            5018 => ['baseId' => 383, 'key' => 'primal', 'label' => 'Primal Groudon'],
            5019 => ['baseId' => 384, 'key' => 'mega', 'label' => 'Mega Rayquaza'],
            5020 => ['baseId' => 15, 'key' => 'mega', 'label' => 'Mega Beedrill'],
            5021 => ['baseId' => 18, 'key' => 'mega', 'label' => 'Mega Pidgeot'],
            5022 => ['baseId' => 80, 'key' => 'mega', 'label' => 'Mega Slowbro'],
            5023 => ['baseId' => 208, 'key' => 'mega', 'label' => 'Mega Steelix'],
            5024 => ['baseId' => 254, 'key' => 'mega', 'label' => 'Mega Sceptile'],
            5025 => ['baseId' => 257, 'key' => 'mega', 'label' => 'Mega Blaziken'],
            5026 => ['baseId' => 260, 'key' => 'mega', 'label' => 'Mega Swampert'],
            5027 => ['baseId' => 282, 'key' => 'mega', 'label' => 'Mega Gardevoir'],
            5028 => ['baseId' => 302, 'key' => 'mega', 'label' => 'Mega Sableye'],
            5029 => ['baseId' => 303, 'key' => 'mega', 'label' => 'Mega Mawile'],
            5030 => ['baseId' => 306, 'key' => 'mega', 'label' => 'Mega Aggron'],
            5031 => ['baseId' => 308, 'key' => 'mega', 'label' => 'Mega Medicham'],
            5032 => ['baseId' => 310, 'key' => 'mega', 'label' => 'Mega Manectric'],
            5033 => ['baseId' => 319, 'key' => 'mega', 'label' => 'Mega Sharpedo'],
            5034 => ['baseId' => 323, 'key' => 'mega', 'label' => 'Mega Camerupt'],
            5035 => ['baseId' => 334, 'key' => 'mega', 'label' => 'Mega Altaria'],
            5036 => ['baseId' => 354, 'key' => 'mega', 'label' => 'Mega Banette'],
            5037 => ['baseId' => 359, 'key' => 'mega', 'label' => 'Mega Absol'],
            5038 => ['baseId' => 362, 'key' => 'mega', 'label' => 'Mega Glalie'],
            5039 => ['baseId' => 373, 'key' => 'mega', 'label' => 'Mega Salamence'],
            5040 => ['baseId' => 376, 'key' => 'mega', 'label' => 'Mega Metagross'],
            5041 => ['baseId' => 380, 'key' => 'mega', 'label' => 'Mega Latias'],
            5042 => ['baseId' => 381, 'key' => 'mega', 'label' => 'Mega Latios'],
            5043 => ['baseId' => 428, 'key' => 'mega', 'label' => 'Mega Lopunny'],
            5044 => ['baseId' => 445, 'key' => 'mega', 'label' => 'Mega Garchomp'],
            5045 => ['baseId' => 448, 'key' => 'mega', 'label' => 'Mega Lucario'],
            5046 => ['baseId' => 460, 'key' => 'mega', 'label' => 'Mega Abomasnow'],
            5047 => ['baseId' => 475, 'key' => 'mega', 'label' => 'Mega Gallade'],
            5048 => ['baseId' => 531, 'key' => 'mega', 'label' => 'Mega Audino'],
            5049 => ['baseId' => 719, 'key' => 'mega', 'label' => 'Mega Diancie'],
        ];
    }

    /** @return array{baseId:int,key:string,label:string}|null */
    public static function form(int $formId): ?array
    {
        return self::forms()[$formId] ?? null;
    }

    public static function isForm(int $formId): bool
    {
        return isset(self::forms()[$formId]);
    }

    public static function displayBaseId(int $id, string $rawCode = ''): int
    {
        $form = self::form($id);
        if ($form !== null) {
            return $form['baseId'];
        }

        $code = (int) preg_replace('/\D+/', '', $rawCode);
        return $code > 0 ? $code : $id;
    }

    public static function formKey(int $id, string $name = ''): string
    {
        $form = self::form($id);
        if ($form !== null) {
            return $form['key'];
        }

        $lower = strtolower($name);
        if (str_contains($lower, 'primal') || str_contains($lower, 'праймал')) {
            return 'primal';
        }
        if (str_contains($lower, 'mega') || str_contains($lower, 'мега')) {
            return 'mega';
        }
        return 'normal';
    }
}
