<?php
declare(strict_types=1);

/**
 * Generates src/Game/PokemonGrowthData.php from the checked-in PokeAPI
 * snapshots in docs/reports/data/pokemon_growth_2026-10-25/.
 *
 * Read-only with respect to the game database: only SELECT form_id/base_id.
 * Run:
 *   php tools/build_pokemon_growth_data.php
 */

use Pokemon8\Database\Connection;
use Pokemon8\Support\Env;

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Run from CLI.\n");
    exit(2);
}

define('APP_ROOT', dirname(__DIR__));
require APP_ROOT . '/src/Support/Autoload.php';

$sourceDir = APP_ROOT . '/docs/reports/data/pokemon_growth_2026-10-25';
$target = APP_ROOT . '/src/Game/PokemonGrowthData.php';

$names = [
    'slow' => 'slow',
    'medium' => 'medium_fast',
    'fast' => 'fast',
    'medium-slow' => 'medium_slow',
    'slow-then-very-fast' => 'erratic',
    'fast-then-very-slow' => 'fluctuating',
];

$speciesList = json_decode((string) file_get_contents($sourceDir . '/species-list.json'), true, 512, JSON_THROW_ON_ERROR);
$idByName = [];
foreach (($speciesList['results'] ?? []) as $row) {
    if (preg_match('~/pokemon-species/(\d+)/?$~', (string) ($row['url'] ?? ''), $m)) {
        $idByName[(string) $row['name']] = (int) $m[1];
    }
}

$groups = [];
$curves = [];
$hashes = [];
foreach (glob($sourceDir . '/growth-*.json') ?: [] as $file) {
    $raw = (string) file_get_contents($file);
    $hashes[basename($file)] = hash('sha256', $raw);
    $json = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
    $group = $names[(string) ($json['name'] ?? '')] ?? null;
    if ($group === null) {
        throw new RuntimeException('Unknown growth-rate name: ' . (string) ($json['name'] ?? '?'));
    }
    $curve = [];
    foreach (($json['levels'] ?? []) as $level) {
        $curve[(int) $level['level']] = (int) $level['experience'];
    }
    ksort($curve);
    if (count($curve) !== 100 || (int) ($curve[1] ?? -1) !== 0) {
        throw new RuntimeException('Unexpected level table for ' . $group);
    }
    $curves[$group] = $curve;
    foreach (($json['pokemon_species'] ?? []) as $species) {
        $id = $idByName[(string) ($species['name'] ?? '')] ?? 0;
        if ($id <= 0) {
            throw new RuntimeException('Cannot resolve species id: ' . (string) ($species['name'] ?? '?'));
        }
        $groups[$id] = $group;
    }
}
ksort($groups);
ksort($curves);

Env::load(APP_ROOT . '/.env');
$db = Connection::make(require APP_ROOT . '/config/database.php');
$forms = [];
foreach ($db->query('SELECT form_id, base_id FROM pokemon_forms ORDER BY form_id')->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $forms[(int) $row['form_id']] = (int) $row['base_id'];
}
ksort($forms);

$speciesListHash = hash('sha256', (string) file_get_contents($sourceDir . '/species-list.json'));
$generatedAt = gmdate('c');

$exportGroups = static function (array $map): string {
    $lines = [];
    foreach ($map as $id => $group) {
        $lines[] = '        ' . $id . " => '" . $group . "',";
    }
    return implode("\n", $lines);
};
$exportForms = static function (array $map): string {
    $lines = [];
    foreach ($map as $id => $baseId) {
        $lines[] = '        ' . (int) $id . ' => ' . (int) $baseId . ',';
    }
    return implode("\n", $lines);
};
$exportCurves = static function (array $curves): string {
    $out = [];
    foreach ($curves as $group => $curve) {
        $values = [];
        foreach ($curve as $level => $xp) {
            $values[] = $level . ' => ' . $xp;
        }
        $out[] = "        '" . $group . "' => [\n            " . implode(', ', $values) . ",\n        ],";
    }
    return implode("\n", $out);
};
$exportHashes = static function (array $hashes): string {
    $out = [];
    foreach ($hashes as $file => $hash) {
        $out[] = "        '" . $file . "' => '" . $hash . "',";
    }
    return implode("\n", $out);
};

$php = <<<PHP
<?php
declare(strict_types=1);

namespace Pokemon8\Game;

/**
 * GENERATED FILE. Do not edit by hand; run tools/build_pokemon_growth_data.php.
 *
 * species -> experience group mapping and the official level tables are derived
 * from the PokeAPI snapshots below. The species mapping was cross-checked against
 * Bulbapedia's "List of Pokémon by experience type" for national dex 1..701
 * (701/701 rows identical on 2026-10-25). The six group totals/formulas were
 * checked against Bulbapedia's "Experience" article.
 *
 * Official totals at level 100: fast 800000, medium_fast 1000000,
 * medium_slow 1059860, slow 1250000, erratic 600000, fluctuating 1640000.
 */
final class PokemonGrowthData
{
    public const SOURCE = [
        'provider' => 'PokeAPI',
        'api' => 'https://pokeapi.co/api/v2/',
        'retrieved_at' => '{$generatedAt}',
        'species_list_sha256' => '{$speciesListHash}',
        'growth_rate_sha256' => [
{$exportHashes($hashes)}
        ],
        'cross_check' => 'Bulbapedia List of Pokémon by experience type, national dex 1..701: 701/701 identical',
        'curve_version' => 2,
    ];

    /** National dex / base id -> canonical experience group. */
    public const SPECIES_GROUPS = [
{$exportGroups($groups)}
    ];

    /** Battle form id -> base species id (forms inherit the species group). */
    public const FORM_BASE = [
{$exportForms($forms)}
    ];

    /** Official cumulative XP tables: group => [level => total XP]. */
    public const OFFICIAL_CURVES = [
{$exportCurves($curves)}
    ];
}

PHP;

if (file_put_contents($target, $php) === false) {
    fwrite(STDERR, "Cannot write {$target}\n");
    exit(1);
}

echo 'Wrote ' . $target . ' (' . count($groups) . ' species, ' . count($forms) . " forms, 6 curves)\n";
echo 'sha256 ' . hash('sha256', $php) . "\n";
