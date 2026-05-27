<?php
declare(strict_types=1);

namespace Pokemon8\Game;

final class BattleTransformationCatalog
{
    /** @return array<int,array{id:int,name:string,title:string,kind:string}> */
    public static function items(): array
    {
        return [
            90200 => ['id' => 90200, 'name' => 'Blue Orb', 'title' => 'Сфера Kyogre. В бою запускает Primal Reversion.', 'kind' => 'primal'],
            90201 => ['id' => 90201, 'name' => 'Red Orb', 'title' => 'Сфера Groudon. В бою запускает Primal Reversion.', 'kind' => 'primal'],
            90202 => ['id' => 90202, 'name' => 'Venusaurite', 'title' => 'Mega Stone для Venusaur.', 'kind' => 'mega'],
            90203 => ['id' => 90203, 'name' => 'Charizardite X', 'title' => 'Mega Stone для Charizard X.', 'kind' => 'mega'],
            90204 => ['id' => 90204, 'name' => 'Charizardite Y', 'title' => 'Mega Stone для Charizard Y.', 'kind' => 'mega'],
            90205 => ['id' => 90205, 'name' => 'Blastoisinite', 'title' => 'Mega Stone для Blastoise.', 'kind' => 'mega'],
            90206 => ['id' => 90206, 'name' => 'Beedrillite', 'title' => 'Mega Stone для Beedrill.', 'kind' => 'mega'],
            90207 => ['id' => 90207, 'name' => 'Pidgeotite', 'title' => 'Mega Stone для Pidgeot.', 'kind' => 'mega'],
            90208 => ['id' => 90208, 'name' => 'Alakazite', 'title' => 'Mega Stone для Alakazam.', 'kind' => 'mega'],
            90209 => ['id' => 90209, 'name' => 'Slowbronite', 'title' => 'Mega Stone для Slowbro.', 'kind' => 'mega'],
            90210 => ['id' => 90210, 'name' => 'Gengarite', 'title' => 'Mega Stone для Gengar.', 'kind' => 'mega'],
            90211 => ['id' => 90211, 'name' => 'Kangaskhanite', 'title' => 'Mega Stone для Kangaskhan.', 'kind' => 'mega'],
            90212 => ['id' => 90212, 'name' => 'Pinsirite', 'title' => 'Mega Stone для Pinsir.', 'kind' => 'mega'],
            90213 => ['id' => 90213, 'name' => 'Gyaradosite', 'title' => 'Mega Stone для Gyarados.', 'kind' => 'mega'],
            90214 => ['id' => 90214, 'name' => 'Aerodactylite', 'title' => 'Mega Stone для Aerodactyl.', 'kind' => 'mega'],
            90215 => ['id' => 90215, 'name' => 'Mewtwonite X', 'title' => 'Mega Stone для Mewtwo X.', 'kind' => 'mega'],
            90216 => ['id' => 90216, 'name' => 'Mewtwonite Y', 'title' => 'Mega Stone для Mewtwo Y.', 'kind' => 'mega'],
            90217 => ['id' => 90217, 'name' => 'Ampharosite', 'title' => 'Mega Stone для Ampharos.', 'kind' => 'mega'],
            90218 => ['id' => 90218, 'name' => 'Steelixite', 'title' => 'Mega Stone для Steelix.', 'kind' => 'mega'],
            90219 => ['id' => 90219, 'name' => 'Scizorite', 'title' => 'Mega Stone для Scizor.', 'kind' => 'mega'],
            90220 => ['id' => 90220, 'name' => 'Heracronite', 'title' => 'Mega Stone для Heracross.', 'kind' => 'mega'],
            90221 => ['id' => 90221, 'name' => 'Houndoominite', 'title' => 'Mega Stone для Houndoom.', 'kind' => 'mega'],
            90222 => ['id' => 90222, 'name' => 'Tyranitarite', 'title' => 'Mega Stone для Tyranitar.', 'kind' => 'mega'],
            90223 => ['id' => 90223, 'name' => 'Sceptilite', 'title' => 'Mega Stone для Sceptile.', 'kind' => 'mega'],
            90224 => ['id' => 90224, 'name' => 'Blazikenite', 'title' => 'Mega Stone для Blaziken.', 'kind' => 'mega'],
            90225 => ['id' => 90225, 'name' => 'Swampertite', 'title' => 'Mega Stone для Swampert.', 'kind' => 'mega'],
            90226 => ['id' => 90226, 'name' => 'Gardevoirite', 'title' => 'Mega Stone для Gardevoir.', 'kind' => 'mega'],
            90227 => ['id' => 90227, 'name' => 'Sablenite', 'title' => 'Mega Stone для Sableye.', 'kind' => 'mega'],
            90228 => ['id' => 90228, 'name' => 'Mawilite', 'title' => 'Mega Stone для Mawile.', 'kind' => 'mega'],
            90229 => ['id' => 90229, 'name' => 'Aggronite', 'title' => 'Mega Stone для Aggron.', 'kind' => 'mega'],
            90230 => ['id' => 90230, 'name' => 'Medichamite', 'title' => 'Mega Stone для Medicham.', 'kind' => 'mega'],
            90231 => ['id' => 90231, 'name' => 'Manectite', 'title' => 'Mega Stone для Manectric.', 'kind' => 'mega'],
            90232 => ['id' => 90232, 'name' => 'Sharpedonite', 'title' => 'Mega Stone для Sharpedo.', 'kind' => 'mega'],
            90233 => ['id' => 90233, 'name' => 'Cameruptite', 'title' => 'Mega Stone для Camerupt.', 'kind' => 'mega'],
            90234 => ['id' => 90234, 'name' => 'Altarianite', 'title' => 'Mega Stone для Altaria.', 'kind' => 'mega'],
            90235 => ['id' => 90235, 'name' => 'Banettite', 'title' => 'Mega Stone для Banette.', 'kind' => 'mega'],
            90236 => ['id' => 90236, 'name' => 'Absolite', 'title' => 'Mega Stone для Absol.', 'kind' => 'mega'],
            90237 => ['id' => 90237, 'name' => 'Glalitite', 'title' => 'Mega Stone для Glalie.', 'kind' => 'mega'],
            90238 => ['id' => 90238, 'name' => 'Salamencite', 'title' => 'Mega Stone для Salamence.', 'kind' => 'mega'],
            90239 => ['id' => 90239, 'name' => 'Metagrossite', 'title' => 'Mega Stone для Metagross.', 'kind' => 'mega'],
            90240 => ['id' => 90240, 'name' => 'Latiasite', 'title' => 'Mega Stone для Latias.', 'kind' => 'mega'],
            90241 => ['id' => 90241, 'name' => 'Latiosite', 'title' => 'Mega Stone для Latios.', 'kind' => 'mega'],
            90242 => ['id' => 90242, 'name' => 'Lopunnite', 'title' => 'Mega Stone для Lopunny.', 'kind' => 'mega'],
            90243 => ['id' => 90243, 'name' => 'Garchompite', 'title' => 'Mega Stone для Garchomp.', 'kind' => 'mega'],
            90244 => ['id' => 90244, 'name' => 'Lucarionite', 'title' => 'Mega Stone для Lucario.', 'kind' => 'mega'],
            90245 => ['id' => 90245, 'name' => 'Abomasite', 'title' => 'Mega Stone для Abomasnow.', 'kind' => 'mega'],
            90246 => ['id' => 90246, 'name' => 'Galladite', 'title' => 'Mega Stone для Gallade.', 'kind' => 'mega'],
            90247 => ['id' => 90247, 'name' => 'Audinite', 'title' => 'Mega Stone для Audino.', 'kind' => 'mega'],
            90248 => ['id' => 90248, 'name' => 'Diancite', 'title' => 'Mega Stone для Diancie.', 'kind' => 'mega'],
        ];
    }

    /** @return list<array{baseId:int,formId:int,type:string,itemId:int,itemKey:string,label:string,requiresMove:string}> */
    public static function rules(): array
    {
        return [
            ['baseId' => 382, 'formId' => 5017, 'type' => 'primal', 'itemId' => 90200, 'itemKey' => 'blue_orb', 'label' => 'Primal Kyogre', 'requiresMove' => ''],
            ['baseId' => 383, 'formId' => 5018, 'type' => 'primal', 'itemId' => 90201, 'itemKey' => 'red_orb', 'label' => 'Primal Groudon', 'requiresMove' => ''],
            ['baseId' => 384, 'formId' => 5019, 'type' => 'mega', 'itemId' => 0, 'itemKey' => '', 'label' => 'Mega Rayquaza', 'requiresMove' => 'dragon_ascent'],
            ['baseId' => 1, 'formId' => 5000, 'type' => 'mega', 'itemId' => 90202, 'itemKey' => 'venusaurite', 'label' => 'Mega Venusaur', 'requiresMove' => ''],
            ['baseId' => 6, 'formId' => 5002, 'type' => 'mega', 'itemId' => 90203, 'itemKey' => 'charizardite_x', 'label' => 'Mega Charizard X', 'requiresMove' => ''],
            ['baseId' => 6, 'formId' => 5001, 'type' => 'mega', 'itemId' => 90204, 'itemKey' => 'charizardite_y', 'label' => 'Mega Charizard Y', 'requiresMove' => ''],
            ['baseId' => 9, 'formId' => 5003, 'type' => 'mega', 'itemId' => 90205, 'itemKey' => 'blastoisinite', 'label' => 'Mega Blastoise', 'requiresMove' => ''],
            ['baseId' => 15, 'formId' => 5020, 'type' => 'mega', 'itemId' => 90206, 'itemKey' => 'beedrillite', 'label' => 'Mega Beedrill', 'requiresMove' => ''],
            ['baseId' => 18, 'formId' => 5021, 'type' => 'mega', 'itemId' => 90207, 'itemKey' => 'pidgeotite', 'label' => 'Mega Pidgeot', 'requiresMove' => ''],
            ['baseId' => 65, 'formId' => 5004, 'type' => 'mega', 'itemId' => 90208, 'itemKey' => 'alakazite', 'label' => 'Mega Alakazam', 'requiresMove' => ''],
            ['baseId' => 80, 'formId' => 5022, 'type' => 'mega', 'itemId' => 90209, 'itemKey' => 'slowbronite', 'label' => 'Mega Slowbro', 'requiresMove' => ''],
            ['baseId' => 94, 'formId' => 5005, 'type' => 'mega', 'itemId' => 90210, 'itemKey' => 'gengarite', 'label' => 'Mega Gengar', 'requiresMove' => ''],
            ['baseId' => 115, 'formId' => 5006, 'type' => 'mega', 'itemId' => 90211, 'itemKey' => 'kangaskhanite', 'label' => 'Mega Kangaskhan', 'requiresMove' => ''],
            ['baseId' => 127, 'formId' => 5007, 'type' => 'mega', 'itemId' => 90212, 'itemKey' => 'pinsirite', 'label' => 'Mega Pinsir', 'requiresMove' => ''],
            ['baseId' => 130, 'formId' => 5008, 'type' => 'mega', 'itemId' => 90213, 'itemKey' => 'gyaradosite', 'label' => 'Mega Gyarados', 'requiresMove' => ''],
            ['baseId' => 142, 'formId' => 5009, 'type' => 'mega', 'itemId' => 90214, 'itemKey' => 'aerodactylite', 'label' => 'Mega Aerodactyl', 'requiresMove' => ''],
            ['baseId' => 150, 'formId' => 5010, 'type' => 'mega', 'itemId' => 90215, 'itemKey' => 'mewtwonite_x', 'label' => 'Mega Mewtwo X', 'requiresMove' => ''],
            ['baseId' => 150, 'formId' => 5011, 'type' => 'mega', 'itemId' => 90216, 'itemKey' => 'mewtwonite_y', 'label' => 'Mega Mewtwo Y', 'requiresMove' => ''],
            ['baseId' => 181, 'formId' => 5012, 'type' => 'mega', 'itemId' => 90217, 'itemKey' => 'ampharosite', 'label' => 'Mega Ampharos', 'requiresMove' => ''],
            ['baseId' => 208, 'formId' => 5023, 'type' => 'mega', 'itemId' => 90218, 'itemKey' => 'steelixite', 'label' => 'Mega Steelix', 'requiresMove' => ''],
            ['baseId' => 212, 'formId' => 5013, 'type' => 'mega', 'itemId' => 90219, 'itemKey' => 'scizorite', 'label' => 'Mega Scizor', 'requiresMove' => ''],
            ['baseId' => 214, 'formId' => 5014, 'type' => 'mega', 'itemId' => 90220, 'itemKey' => 'heracronite', 'label' => 'Mega Heracross', 'requiresMove' => ''],
            ['baseId' => 229, 'formId' => 5015, 'type' => 'mega', 'itemId' => 90221, 'itemKey' => 'houndoominite', 'label' => 'Mega Houndoom', 'requiresMove' => ''],
            ['baseId' => 248, 'formId' => 5016, 'type' => 'mega', 'itemId' => 90222, 'itemKey' => 'tyranitarite', 'label' => 'Mega Tyranitar', 'requiresMove' => ''],
            ['baseId' => 254, 'formId' => 5024, 'type' => 'mega', 'itemId' => 90223, 'itemKey' => 'sceptilite', 'label' => 'Mega Sceptile', 'requiresMove' => ''],
            ['baseId' => 257, 'formId' => 5025, 'type' => 'mega', 'itemId' => 90224, 'itemKey' => 'blazikenite', 'label' => 'Mega Blaziken', 'requiresMove' => ''],
            ['baseId' => 260, 'formId' => 5026, 'type' => 'mega', 'itemId' => 90225, 'itemKey' => 'swampertite', 'label' => 'Mega Swampert', 'requiresMove' => ''],
            ['baseId' => 282, 'formId' => 5027, 'type' => 'mega', 'itemId' => 90226, 'itemKey' => 'gardevoirite', 'label' => 'Mega Gardevoir', 'requiresMove' => ''],
            ['baseId' => 302, 'formId' => 5028, 'type' => 'mega', 'itemId' => 90227, 'itemKey' => 'sablenite', 'label' => 'Mega Sableye', 'requiresMove' => ''],
            ['baseId' => 303, 'formId' => 5029, 'type' => 'mega', 'itemId' => 90228, 'itemKey' => 'mawilite', 'label' => 'Mega Mawile', 'requiresMove' => ''],
            ['baseId' => 306, 'formId' => 5030, 'type' => 'mega', 'itemId' => 90229, 'itemKey' => 'aggronite', 'label' => 'Mega Aggron', 'requiresMove' => ''],
            ['baseId' => 308, 'formId' => 5031, 'type' => 'mega', 'itemId' => 90230, 'itemKey' => 'medichamite', 'label' => 'Mega Medicham', 'requiresMove' => ''],
            ['baseId' => 310, 'formId' => 5032, 'type' => 'mega', 'itemId' => 90231, 'itemKey' => 'manectite', 'label' => 'Mega Manectric', 'requiresMove' => ''],
            ['baseId' => 319, 'formId' => 5033, 'type' => 'mega', 'itemId' => 90232, 'itemKey' => 'sharpedonite', 'label' => 'Mega Sharpedo', 'requiresMove' => ''],
            ['baseId' => 323, 'formId' => 5034, 'type' => 'mega', 'itemId' => 90233, 'itemKey' => 'cameruptite', 'label' => 'Mega Camerupt', 'requiresMove' => ''],
            ['baseId' => 334, 'formId' => 5035, 'type' => 'mega', 'itemId' => 90234, 'itemKey' => 'altarianite', 'label' => 'Mega Altaria', 'requiresMove' => ''],
            ['baseId' => 354, 'formId' => 5036, 'type' => 'mega', 'itemId' => 90235, 'itemKey' => 'banettite', 'label' => 'Mega Banette', 'requiresMove' => ''],
            ['baseId' => 359, 'formId' => 5037, 'type' => 'mega', 'itemId' => 90236, 'itemKey' => 'absolite', 'label' => 'Mega Absol', 'requiresMove' => ''],
            ['baseId' => 362, 'formId' => 5038, 'type' => 'mega', 'itemId' => 90237, 'itemKey' => 'glalitite', 'label' => 'Mega Glalie', 'requiresMove' => ''],
            ['baseId' => 373, 'formId' => 5039, 'type' => 'mega', 'itemId' => 90238, 'itemKey' => 'salamencite', 'label' => 'Mega Salamence', 'requiresMove' => ''],
            ['baseId' => 376, 'formId' => 5040, 'type' => 'mega', 'itemId' => 90239, 'itemKey' => 'metagrossite', 'label' => 'Mega Metagross', 'requiresMove' => ''],
            ['baseId' => 380, 'formId' => 5041, 'type' => 'mega', 'itemId' => 90240, 'itemKey' => 'latiasite', 'label' => 'Mega Latias', 'requiresMove' => ''],
            ['baseId' => 381, 'formId' => 5042, 'type' => 'mega', 'itemId' => 90241, 'itemKey' => 'latiosite', 'label' => 'Mega Latios', 'requiresMove' => ''],
            ['baseId' => 428, 'formId' => 5043, 'type' => 'mega', 'itemId' => 90242, 'itemKey' => 'lopunnite', 'label' => 'Mega Lopunny', 'requiresMove' => ''],
            ['baseId' => 445, 'formId' => 5044, 'type' => 'mega', 'itemId' => 90243, 'itemKey' => 'garchompite', 'label' => 'Mega Garchomp', 'requiresMove' => ''],
            ['baseId' => 448, 'formId' => 5045, 'type' => 'mega', 'itemId' => 90244, 'itemKey' => 'lucarionite', 'label' => 'Mega Lucario', 'requiresMove' => ''],
            ['baseId' => 460, 'formId' => 5046, 'type' => 'mega', 'itemId' => 90245, 'itemKey' => 'abomasite', 'label' => 'Mega Abomasnow', 'requiresMove' => ''],
            ['baseId' => 475, 'formId' => 5047, 'type' => 'mega', 'itemId' => 90246, 'itemKey' => 'galladite', 'label' => 'Mega Gallade', 'requiresMove' => ''],
            ['baseId' => 531, 'formId' => 5048, 'type' => 'mega', 'itemId' => 90247, 'itemKey' => 'audinite', 'label' => 'Mega Audino', 'requiresMove' => ''],
            ['baseId' => 719, 'formId' => 5049, 'type' => 'mega', 'itemId' => 90248, 'itemKey' => 'diancite', 'label' => 'Mega Diancie', 'requiresMove' => ''],
        ];
    }

    /** @return array{baseId:int,formId:int,type:string,itemId:int,itemKey:string,label:string,requiresMove:string}|null */
    public static function matchRule(int $baseId, int $heldItemId, string $heldItemName, bool $knowsDragonAscent): ?array
    {
        if (PokemonFormCatalog::isForm($baseId)) {
            return null;
        }

        foreach (self::rules() as $rule) {
            if ($rule['baseId'] !== $baseId) {
                continue;
            }

            if ($rule['requiresMove'] !== '') {
                if ($knowsDragonAscent) {
                    return $rule;
                }
                continue;
            }

            if ($rule['itemId'] > 0 && ($heldItemId === $rule['itemId'] || self::nameMatchesItemKey($heldItemName, $rule['itemKey']))) {
                return $rule;
            }
        }

        return null;
    }

    public static function transformationMessage(array $rule, string $name): string
    {
        $cleanName = trim(strip_tags($name)) ?: 'Покемон';
        if (($rule['type'] ?? '') === 'primal') {
            return sprintf('%s возвращается к древней форме: %s.', $cleanName, (string) $rule['label']);
        }
        return sprintf('%s мега-эволюционирует: %s.', $cleanName, (string) $rule['label']);
    }

    private static function nameMatchesItemKey(string $name, string $itemKey): bool
    {
        if ($itemKey === '' || trim($name) === '') {
            return false;
        }

        $compactName = self::compact($name);
        $compactKey = self::compact($itemKey);
        if ($compactKey !== '' && str_contains($compactName, $compactKey)) {
            return true;
        }

        $aliases = [
            'blueorb' => ['blueorb', 'синясфера', 'синийшар', 'голубаясфера'],
            'redorb' => ['redorb', 'краснаясфера', 'красныйшар'],
            'charizarditex' => ['charizarditex', 'чаризардитx'],
            'charizarditey' => ['charizarditey', 'чаризардитy'],
            'mewtwonitex' => ['mewtwonitex', 'мьютвонитx'],
            'mewtwonitey' => ['mewtwonitey', 'мьютвонитy'],
        ];
        foreach ($aliases[$compactKey] ?? [] as $alias) {
            if (str_contains($compactName, $alias)) {
                return true;
            }
        }

        return false;
    }

    private static function compact(string $value): string
    {
        $value = mb_strtolower($value, 'UTF-8');
        $value = str_replace('ё', 'е', $value);
        return preg_replace('/[^a-zа-я0-9]+/u', '', $value) ?? '';
    }
}
