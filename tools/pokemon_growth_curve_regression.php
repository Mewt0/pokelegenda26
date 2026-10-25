<?php
declare(strict_types=1);

/**
 * Pure regression for the per-growth-group XP model. No database access and no
 * writes. Run: php tools/pokemon_growth_curve_regression.php
 */

use Pokemon8\Game\PokemonExperience;
use Pokemon8\Game\PokemonGrowthCurve as Curve;
use Pokemon8\Game\PokemonGrowthData;
use Pokemon8\Game\PokemonGrowthGroup as Group;

define('APP_ROOT', dirname(__DIR__));
require APP_ROOT . '/src/Support/Autoload.php';

$checks = 0;
$assert = static function (bool $condition, string $label) use (&$checks): void {
    $checks++;
    if (!$condition) {
        throw new RuntimeException($label);
    }
};

// 1. Species/form mapping, including forms inheriting the base species.
$samples = [
    1 => Group::MEDIUM_SLOW,
    4 => Group::MEDIUM_SLOW,
    7 => Group::MEDIUM_SLOW,
    10 => Group::MEDIUM_FAST,
    25 => Group::MEDIUM_FAST,
    35 => Group::FAST,
    58 => Group::SLOW,
    92 => Group::MEDIUM_SLOW,
    113 => Group::FAST,
    129 => Group::SLOW,
    143 => Group::SLOW,
    150 => Group::SLOW,
    151 => Group::MEDIUM_SLOW,
    197 => Group::MEDIUM_FAST,
    285 => Group::FLUCTUATING,
    653 => Group::MEDIUM_SLOW,
    700 => Group::MEDIUM_FAST,
    703 => Group::SLOW,
];
foreach ($samples as $id => $expected) {
    $resolved = Group::resolve($id);
    $assert($resolved['group'] === $expected, 'sample species ' . $id . ' -> ' . $expected);
    $assert($resolved['known'] === true, 'sample species known ' . $id);
}

$form = Group::resolve(5000);
$assert($form['group'] === Group::MEDIUM_SLOW && $form['source'] === 'form_species', 'Mega Venusaur inherits base group');
$form = Group::resolve(5049);
$assert($form['group'] === Group::SLOW && $form['source'] === 'form_species', 'Mega Diancie inherits base group');
$fallback = Group::resolve(99999);
$assert($fallback['group'] === Group::DEFAULT && $fallback['known'] === false && $fallback['source'] === 'fallback_medium_fast', 'unknown id fallback');

$allSpecies = 0;
for ($id = 1; $id <= 1025; $id++) {
    if (!isset(PokemonGrowthData::SPECIES_GROUPS[$id])) {
        throw new RuntimeException('missing species mapping ' . $id);
    }
    $resolved = Group::resolve($id);
    $assert($resolved['known'] === true, 'known species ' . $id);
    $allSpecies++;
}
$assert($allSpecies === 1025, 'all PokeAPI species covered');
$assert(count(PokemonGrowthData::FORM_BASE) === 50, '50 battle forms covered');
foreach (PokemonGrowthData::FORM_BASE as $formId => $baseId) {
    $assert(is_int($baseId), 'form base is int ' . $formId);
    $assert(Group::resolve((int) $formId)['group'] === Group::resolve((int) $baseId)['group'], 'form inherits base group ' . $formId);
    $assert(Group::resolve((int) $formId)['known'] === true, 'form base known ' . $formId);
}

// 2. Official tables match the checked-in PokeAPI snapshot and its hashes.
$sourceDir = APP_ROOT . '/docs/reports/data/pokemon_growth_2026-10-25';
$assert(is_file($sourceDir . '/species-list.json'), 'species snapshot present');
$assert(hash_file('sha256', $sourceDir . '/species-list.json') === PokemonGrowthData::SOURCE['species_list_sha256'], 'species snapshot hash');
$files = glob($sourceDir . '/growth-*.json') ?: [];
$assert(count($files) === 6, 'six growth snapshots');
foreach ($files as $file) {
    $name = basename($file);
    $assert(isset(PokemonGrowthData::SOURCE['growth_rate_sha256'][$name]), 'hash recorded ' . $name);
    $assert(hash_file('sha256', $file) === PokemonGrowthData::SOURCE['growth_rate_sha256'][$name], 'hash matches ' . $name);
    $json = json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
    $map = [
        'slow' => Group::SLOW,
        'medium' => Group::MEDIUM_FAST,
        'fast' => Group::FAST,
        'medium-slow' => Group::MEDIUM_SLOW,
        'slow-then-very-fast' => Group::ERRATIC,
        'fast-then-very-slow' => Group::FLUCTUATING,
    ];
    $group = $map[(string) $json['name']] ?? null;
    $assert($group !== null, 'snapshot group recognized ' . $name);
    $table = [];
    foreach ($json['levels'] as $row) {
        $table[(int) $row['level']] = (int) $row['experience'];
    }
    $assert($table === PokemonGrowthData::OFFICIAL_CURVES[$group], 'official table matches snapshot ' . $group);
}

// 2a. The generated species map must be exactly the PokeAPI snapshot mapping.
$speciesList = json_decode((string) file_get_contents($sourceDir . '/species-list.json'), true, 512, JSON_THROW_ON_ERROR);
$idByName = [];
foreach (($speciesList['results'] ?? []) as $row) {
    if (preg_match('~/pokemon-species/(\d+)/?$~', (string) ($row['url'] ?? ''), $m)) {
        $idByName[(string) $row['name']] = (int) $m[1];
    }
}
$snapshotGroups = [];
$snapshotNames = [
    'slow' => Group::SLOW,
    'medium' => Group::MEDIUM_FAST,
    'fast' => Group::FAST,
    'medium-slow' => Group::MEDIUM_SLOW,
    'slow-then-very-fast' => Group::ERRATIC,
    'fast-then-very-slow' => Group::FLUCTUATING,
];
foreach ($files as $file) {
    $json = json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
    $snapshotGroup = $snapshotNames[(string) $json['name']] ?? null;
    $assert($snapshotGroup !== null, 'snapshot group key ' . basename($file));
    foreach (($json['pokemon_species'] ?? []) as $species) {
        $id = $idByName[(string) $species['name']] ?? 0;
        $assert($id > 0, 'snapshot species id ' . $species['name']);
        $snapshotGroups[$id] = $snapshotGroup;
    }
}
ksort($snapshotGroups);
$assert($snapshotGroups === PokemonGrowthData::SPECIES_GROUPS, 'generated species map equals PokeAPI snapshot');

// 2b. The saved Bulbapedia cross-check must agree with the generated mapping.
$crossFile = $sourceDir . '/bulbapedia-crosscheck-1-701.json';
$assert(is_file($crossFile), 'Bulbapedia cross-check present');
$cross = json_decode((string) file_get_contents($crossFile), true, 512, JSON_THROW_ON_ERROR);
$crossLabels = [
    'Medium Fast' => Group::MEDIUM_FAST,
    'Medium Slow' => Group::MEDIUM_SLOW,
    'Fast' => Group::FAST,
    'Slow' => Group::SLOW,
    'Erratic' => Group::ERRATIC,
    'Fluctuating' => Group::FLUCTUATING,
];
$crossChecked = 0;
foreach (($cross['mapping'] ?? []) as $id => $label) {
    $expected = $crossLabels[(string) $label] ?? null;
    $assert($expected !== null, 'Bulbapedia label recognized ' . $id);
    $assert(Group::resolve((int) $id)['group'] === $expected, 'Bulbapedia mapping ' . $id);
    $crossChecked++;
}
$assert($crossChecked === 701, 'Bulbapedia 701 rows checked');

$expectedTotals = [
    Group::FAST => 800000,
    Group::MEDIUM_FAST => 1000000,
    Group::MEDIUM_SLOW => 1059860,
    Group::SLOW => 1250000,
    Group::ERRATIC => 600000,
    Group::FLUCTUATING => 1640000,
];
foreach ($expectedTotals as $group => $total) {
    $assert((int) PokemonGrowthData::OFFICIAL_CURVES[$group][100] === $total, 'official total ' . $group);
}

// 3. Scaled curves: monotonic, exact target totals, no zero-width level.
foreach (Curve::targetTotals() as $group => $target) {
    $previous = 0;
    for ($level = 1; $level <= 100; $level++) {
        $current = Curve::cumulative($level, $group);
        $assert($current >= $previous, 'monotonic ' . $group . ' L' . $level);
        if ($level > 1) {
            $assert($current > $previous, 'strictly increasing ' . $group . ' L' . $level);
        }
        $previous = $current;
    }
    $assert(Curve::cumulative(1, $group) === 0, 'level 1 starts at 0 ' . $group);
    $assert(Curve::cumulative(100, $group) === $target, 'target total ' . $group);
    $assert(Curve::levelForExperience($target, $group) === 100, 'cap lookup ' . $group);
    $assert(Curve::levelForExperience($target - 1, $group) === 99, 'before cap lookup ' . $group);
    for ($level = 1; $level < 100; $level++) {
        $assert(Curve::levelForExperience(Curve::cumulative($level, $group), $group) === $level, 'threshold lookup ' . $group . ' L' . $level);
    }
}

// 4. UI contract is identical to the old one at the boundaries.
foreach ([Group::FAST, Group::MEDIUM_FAST, Group::SLOW] as $group) {
    $zero = Curve::progress(1, 0, $group);
    $assert($zero['expCurrentLevel'] === 0 && $zero['expProgressPercent'] === 0.0, 'level 1 zero ' . $group);
    for ($level = 1; $level < 100; $level++) {
        $lower = Curve::cumulative($level, $group);
        $next = Curve::cumulative($level + 1, $group);
        $start = Curve::progress($level, $lower, $group);
        $assert($start['expProgressPercent'] === 0.0 && $start['expNextLevel'] === $next, 'level start ' . $group . ' L' . $level);
        $before = Curve::progress($level, $next - 1, $group);
        $assert($before['expRemaining'] === 1 && $before['expProgressPercent'] < 100, 'before threshold ' . $group . ' L' . $level);
        $at = Curve::progress($level, $next, $group);
        $assert($at['expRemaining'] === 0 && $at['expProgressPercent'] === 100.0, 'exact threshold ' . $group . ' L' . $level);
        $after = Curve::progress($level + 1, $next, $group);
        $assert($after['expProgressPercent'] === ($level === 99 ? 100.0 : 0.0), 'after level up ' . $group . ' L' . $level);
    }
    $cap = Curve::progress(100, 0, $group);
    $assert($cap['expAtLevelCap'] && $cap['expRemaining'] === 0 && $cap['expProgressPercent'] === 100.0, 'cap ' . $group);
}

// 5. Legacy migration base matches the old production formula exactly.
for ($level = 1; $level <= 99; $level++) {
    $assert(Curve::legacyLevelThreshold($level) === PokemonExperience::legacyLevelThreshold($level), 'legacy threshold L' . $level);
    $assert(Curve::legacyCumulative($level) === ($level === 1 ? 0 : PokemonExperience::legacyLevelThreshold($level - 1)), 'legacy cumulative L' . $level);
}
$assert(Curve::legacyLevelThreshold(100) === PokemonExperience::legacyLevelThreshold(99), 'legacy cap threshold');
$assert(Curve::legacyCumulative(100) === PokemonExperience::legacyLevelThreshold(99), 'legacy cap cumulative');

// 6. Migration preserves level and relative progress; debt/overflow are clamped.
$cases = [
    [5, 324, Group::MEDIUM_SLOW],
    [50, 0, Group::MEDIUM_FAST],
    [50, 6262, Group::MEDIUM_FAST],
    [52, 156, Group::MEDIUM_FAST],
    [99, 8835948, Group::SLOW],
    [100, 0, Group::MEDIUM_FAST],
];
foreach ($cases as [$level, $exp, $group]) {
    $mapped = Curve::remapLegacyExperience($level, $exp, $group);
    $assert($mapped['level'] === $level, 'migration keeps level ' . $level);
    $assert($mapped['exp'] >= Curve::cumulative($level, $group), 'migration no debt L' . $level);
    if ($level < 100) {
        $assert($mapped['exp'] < Curve::cumulative($level + 1, $group), 'migration no auto-level L' . $level);
    }
}
$legacyMid = Curve::legacyCumulative(50) + 3000;
$mapped = Curve::remapLegacyExperience(50, $legacyMid, Group::MEDIUM_FAST);
$assert($mapped['fraction'] > 0.0 && $mapped['fraction'] < 1.0, 'migration preserves partial progress');
$mapped = Curve::remapLegacyExperience(50, 0, Group::MEDIUM_FAST);
$assert($mapped['debtClamped'] === true && $mapped['fraction'] === 0.0, 'migration detects debt');
$mapped = Curve::remapLegacyExperience(50, 999999, Group::MEDIUM_FAST);
$assert($mapped['overflowClamped'] === true && $mapped['fraction'] === 1.0, 'migration detects overflow');
$mapped = Curve::remapLegacyExperience(100, 0, Group::MEDIUM_FAST);
$assert($mapped['exp'] === Curve::cumulative(100, Group::MEDIUM_FAST), 'migration cap');

// 7. Candy grants never leave XP debt.
foreach (Curve::targetTotals() as $group => $target) {
    for ($newLevel = 1; $newLevel <= 100; $newLevel++) {
        $floor = Curve::cumulative($newLevel, $group);
        $assert(Curve::experienceAfterLevelGrant($newLevel, 0, $group) === $floor, 'candy floor ' . $group . ' L' . $newLevel);
        $assert(Curve::experienceAfterLevelGrant($newLevel, $floor + 1, $group) === $floor + 1, 'candy keeps progress ' . $group . ' L' . $newLevel);
        $assert(Curve::experienceAfterLevelGrant($newLevel, $floor, $group) >= $floor, 'candy no debt ' . $group . ' L' . $newLevel);
    }
}

// 7b. Pre-grant normalization: debt rises to the floor, valid XP is untouched,
//     pre-migration overflow never jumps to the cap.
foreach (Curve::targetTotals() as $group => $target) {
    for ($level = 1; $level <= 100; $level++) {
        $floor = Curve::cumulative($level, $group);
        $assert(Curve::normalizeStoredExperience($level, 0, $group) === $floor, 'normalize debt ' . $group . ' L' . $level);
        if ($level < 100) {
            $mid = $floor + intdiv(max(1, Curve::cumulative($level + 1, $group) - $floor), 2);
            $assert(Curve::normalizeStoredExperience($level, $mid, $group) === $mid, 'normalize keeps valid ' . $group . ' L' . $level);
        }
        $overflow = Curve::normalizeStoredExperience($level, 999999, $group);
        $assert($overflow >= $floor, 'normalize overflow floor ' . $group . ' L' . $level);
        if ($level < 100) {
            $assert($overflow < Curve::cumulative($level + 1, $group), 'normalize overflow no auto-level ' . $group . ' L' . $level);
        } else {
            $assert($overflow === $target, 'normalize cap ' . $group);
        }
    }
}

// 8. PokemonExperience facade is group-aware by default (medium_fast anchor)
//    and still exposes the legacy formula for the one-time migration only.
$groupProgress = PokemonExperience::progress(5, 0, Group::MEDIUM_FAST);
$assert($groupProgress['growthGroup'] === Group::MEDIUM_FAST, 'facade group');
$assert($groupProgress['expNextLevel'] === Curve::levelThreshold(5, Group::MEDIUM_FAST), 'facade next threshold');
$assert(PokemonExperience::levelThreshold(5, Group::MEDIUM_FAST) === Curve::levelThreshold(5, Group::MEDIUM_FAST), 'facade level threshold');
$defaultProgress = PokemonExperience::progress(5, 0);
$assert($defaultProgress['growthGroup'] === Group::DEFAULT, 'facade default group');
$assert($defaultProgress['expNextLevel'] === Curve::levelThreshold(5, Group::DEFAULT), 'facade default threshold');
$assert(PokemonExperience::levelThreshold(5) === Curve::levelThreshold(5, Group::DEFAULT), 'facade default level threshold');
$assert(PokemonExperience::legacyLevelThreshold(5) === Curve::legacyLevelThreshold(5), 'facade legacy helper');
$assert(PokemonExperience::legacyLevelThreshold(5) !== PokemonExperience::levelThreshold(5), 'legacy and v2 differ');
$assert(PokemonExperience::curveVersion() === Curve::VERSION, 'facade version');
$withBase = PokemonExperience::progressForBaseId(5000, 50, 0);
$assert($withBase['growthGroup'] === Group::MEDIUM_SLOW && $withBase['growthGroupKnown'] === true, 'base-aware progress form');
$assert($withBase['growthGroupSource'] === 'form_species' && $withBase['growthBaseId'] === 1, 'base-aware provenance');
$fallbackProgress = PokemonExperience::progressForBaseId(99999, 5, 0);
$assert($fallbackProgress['growthGroupKnown'] === false && $fallbackProgress['growthGroupSource'] === 'fallback_medium_fast', 'base-aware fallback');

echo 'PASS ' . $checks . " growth-curve checks (no database access)\n";
