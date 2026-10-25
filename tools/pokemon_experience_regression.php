<?php
declare(strict_types=1);

/**
 * Facade contract for the group-aware XP curve (curve v2). No database access.
 *
 * The default facade group is medium_fast; species-aware callers must use
 * progressForBaseId()/levelThreshold($level, $group). The legacy formula stays
 * reachable as legacyLevelThreshold() for the one-time data migration only.
 */

require dirname(__DIR__) . '/src/Support/Autoload.php';

use Pokemon8\Game\PokemonExperience as XP;
use Pokemon8\Game\PokemonGrowthCurve as Curve;
use Pokemon8\Game\PokemonGrowthGroup as Group;

$checks = 0;
$assert = static function (bool $condition, string $label) use (&$checks): void {
    $checks++;
    if (!$condition) {
        throw new RuntimeException($label);
    }
};

$zero = XP::progress(1, 0);
$assert($zero['expCurrentLevel'] === 0 && $zero['expProgressPercent'] === 0.0, 'Level 1 zero');
$assert($zero['expNextLevel'] === Curve::cumulative(2, Group::MEDIUM_FAST), 'Medium-fast default threshold');
$assert($zero['growthGroup'] === Group::MEDIUM_FAST, 'Default group is medium_fast');
$assert($zero['growthCurveVersion'] === Curve::VERSION, 'Curve version exposed');

foreach ($groups = [Group::FAST, Group::MEDIUM_FAST, Group::MEDIUM_SLOW, Group::SLOW, Group::ERRATIC, Group::FLUCTUATING] as $group) {
    $assert(XP::levelThreshold(1, $group) === Curve::cumulative(2, $group), 'Facade threshold by group ' . $group);
    $assert(XP::levelThreshold(1, $group) === XP::progress(1, 0, $group)['expNextLevel'], 'Threshold matches progress ' . $group);
    for ($level = 1; $level < 100; $level++) {
        $lower = Curve::cumulative($level, $group);
        $next = Curve::cumulative($level + 1, $group);
        $start = XP::progress($level, $lower, $group);
        $assert($start['expProgressPercent'] === 0.0 && $start['expNextLevel'] === $next, 'Level start ' . $group . ' L' . $level);
        $before = XP::progress($level, $next - 1, $group);
        $assert($before['expRemaining'] === 1 && $before['expProgressPercent'] < 100, 'Before threshold ' . $group . ' L' . $level);
        $at = XP::progress($level, $next, $group);
        $assert($at['expRemaining'] === 0 && $at['expProgressPercent'] === 100.0, 'Exact threshold ' . $group . ' L' . $level);
        $after = XP::progress($level + 1, $next, $group);
        $assert($after['expProgressPercent'] === ($level === 99 ? 100.0 : 0.0), 'After level up ' . $group . ' L' . $level);
    }
    $cap = XP::progress(100, 0, $group);
    $assert($cap['expAtLevelCap'] && $cap['expRemaining'] === 0 && $cap['expProgressPercent'] === 100.0, 'Cap ' . $group);
    $assert($cap['expNextLevel'] === Curve::cumulative(100, $group), 'Cap threshold ' . $group);
}

foreach ([0, 100, PHP_INT_MAX] as $exp) {
    $cap = XP::progress(100, $exp);
    $assert($cap['expRemaining'] === 0 && $cap['expProgressPercent'] === 100.0 && $cap['expAtLevelCap'], 'Level cap');
}
$assert(XP::progress(5, 0)['expProgressPercent'] === 0.0, 'Underfilled seeded Pokemon');
$assert(XP::progress(5, PHP_INT_MAX)['expProgressPercent'] === 100.0, 'Overflow clamped');
$assert(XP::progress(-1, -20) === XP::progress(1, 0), 'Invalid inputs clamped');

$unknown = XP::progress(5, 0, 'not-a-group');
$assert($unknown['growthGroup'] === Group::MEDIUM_FAST, 'Unknown group normalized');

$baseAware = XP::progressForBaseId(1, 5, 0);
$assert($baseAware['growthGroup'] === Group::MEDIUM_SLOW && $baseAware['growthGroupKnown'] === true, 'Base-aware group');
$assert($baseAware['expNextLevel'] === Curve::cumulative(6, Group::MEDIUM_SLOW), 'Base-aware threshold');
$formAware = XP::progressForBaseId(5000, 5, 0);
$assert($formAware['growthGroup'] === Group::MEDIUM_SLOW && $formAware['growthBaseId'] === 1, 'Form inherits base group');

$assert(XP::legacyLevelThreshold(1) === Curve::legacyLevelThreshold(1), 'Legacy facade preserved');
$assert(XP::legacyLevelThreshold(50) === Curve::legacyCumulative(51), 'Legacy threshold formula');
$assert(XP::legacyLevelThreshold(1) !== XP::levelThreshold(1), 'Legacy and v2 curves are distinct');

echo 'PASS ' . $checks . " XP facade checks (no database access)\n";
