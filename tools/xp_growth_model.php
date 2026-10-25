<?php
declare(strict_types=1);

/**
 * Read-only XP pacing model for the six growth groups.
 *
 * It uses the live pokebuild spawn table (SELECT only) and the production
 * encounter selection from WildEncounterService:
 *   ORDER BY (RAND() * GREATEST(chance, 1)) DESC LIMIT 1
 * plus enemy level = spawn level + random_int(0, 4).
 *
 * Reward/cooldown assumptions from BattleRewardService and the PvE finish path:
 *   XP per win = 12 * enemy level
 *   full cycle = 45..60 seconds (includes the 30 s cooldown)
 *   only the finishing Pokemon gets XP; no EXP share, candies or boosts.
 *
 * The special game-room location 20152 (levels 300..400) is excluded because it
 * is not a normal guaranteed leveling route.
 *
 * Run: php tools/xp_growth_model.php [--runs=1000] [--json]
 */

use Pokemon8\Database\Connection;
use Pokemon8\Game\PokemonGrowthCurve as Curve;
use Pokemon8\Game\PokemonGrowthGroup as Group;
use Pokemon8\Support\Env;

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Run from CLI.\n");
    exit(2);
}

define('APP_ROOT', dirname(__DIR__));
require APP_ROOT . '/src/Support/Autoload.php';

$runs = 1000;
foreach ($argv as $arg) {
    if (preg_match('/^--runs=(\d+)$/', $arg, $m)) {
        $runs = max(50, min(10000, (int) $m[1]));
    }
}
$jsonOut = in_array('--json', $argv, true);

$buildings = [];
try {
    Env::load(APP_ROOT . '/.env');
    $db = Connection::make(require APP_ROOT . '/config/database.php');
    $rows = $db->query(
        'SELECT building, lvl, chance
           FROM pokebuild
          WHERE building <> 20152 AND lvl BETWEEN 1 AND 51
          ORDER BY building, lvl'
    )->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        $building = (int) $row['building'];
        $buildings[$building][] = [
            'lvl' => max(1, (int) $row['lvl']),
            'chance' => max(1, (int) $row['chance']),
        ];
    }
} catch (Throwable $e) {
    // Fallback keeps the model reproducible when the DB is offline.
    $fallbackLevels = [1, 3, 5, 7, 9, 12, 14, 16, 18, 20, 22, 25, 28, 30, 32, 35, 38, 40, 42, 45, 47, 48, 50, 51];
    $buildings[1] = array_map(static fn (int $level): array => ['lvl' => $level, 'chance' => 99], $fallbackLevels);
}
if ($buildings === []) {
    $buildings[1] = [['lvl' => 5, 'chance' => 99]];
}

$buildingMeta = [];
foreach ($buildings as $id => $rows) {
    $max = 0;
    $weighted = 0;
    $weight = 0;
    foreach ($rows as $row) {
        $max = max($max, $row['lvl']);
        $weighted += $row['lvl'] * $row['chance'];
        $weight += $row['chance'];
    }
    $buildingMeta[$id] = [
        'max' => $max,
        'avg' => $weight > 0 ? $weighted / $weight : 0,
        'rows' => $rows,
    ];
}
$maxSpawn = 0;
foreach ($buildingMeta as $meta) {
    $maxSpawn = max($maxSpawn, $meta['max']);
}

/**
 * Policies describe which wild spawn levels a player can realistically use.
 * matched: enemies around the player's level; frontier: pushes ~8 levels ahead;
 * max: always farms the highest ordinary spawn (51 + 0..4 = up to 55).
 *
 * @return array<string,int>
 */
$policies = [
    'matched' => 2,
    'frontier' => 8,
    'max' => 999,
];

$groupOrder = [
    Group::ERRATIC,
    Group::FAST,
    Group::MEDIUM_FAST,
    Group::MEDIUM_SLOW,
    Group::SLOW,
    Group::FLUCTUATING,
];

$pickBuilding = static function (array $buildingMeta, int $cap): array {
    $best = null;
    foreach ($buildingMeta as $meta) {
        if ($meta['max'] > $cap) {
            continue;
        }
        if ($best === null || $meta['max'] > $best['max'] || ($meta['max'] === $best['max'] && $meta['avg'] > $best['avg'])) {
            $best = $meta;
        }
    }
    if ($best === null) {
        $best = reset($buildingMeta) ?: ['max' => 1, 'rows' => [['lvl' => 1, 'chance' => 1]]];
    }
    return $best;
};

$pickRow = static function (array $rows): array {
    $best = $rows[0];
    $bestScore = -1.0;
    foreach ($rows as $row) {
        $score = (mt_rand() / mt_getrandmax()) * $row['chance'];
        if ($score > $bestScore) {
            $bestScore = $score;
            $best = $row;
        }
    }
    return $best;
};

$simulate = static function (string $group, int $offset, int $runs, array $buildingMeta, int $startLevel) use ($pickBuilding, $pickRow): array {
    $curve = [];
    for ($level = 1; $level <= 100; $level++) {
        $curve[$level] = Curve::cumulative($level, $group);
    }
    $wins = [];
    $enemySum = 0;
    mt_srand(1234567);
    for ($run = 0; $run < $runs; $run++) {
        $level = $startLevel;
        $exp = $curve[$startLevel];
        $count = 0;
        while ($level < 100 && $count < 10000) {
            $cap = min(51, $level + $offset);
            $building = $pickBuilding($buildingMeta, $cap);
            $row = $pickRow($building['rows']);
            $enemy = min(55, $row['lvl'] + mt_rand(0, 4));
            $exp += 12 * $enemy;
            $enemySum += $enemy;
            $count++;
            while ($level < 100 && $exp >= $curve[$level + 1]) {
                $level++;
            }
        }
        $wins[] = $count;
    }
    sort($wins);
    $mean = array_sum($wins) / max(1, count($wins));
    $pick = static function (float $q) use ($wins): int {
        $index = (int) floor($q * (count($wins) - 1));
        return $wins[$index];
    };
    $totalXp = $curve[100] - $curve[$startLevel];
    return [
        'winsMean' => round($mean, 1),
        'winsP10' => $pick(0.10),
        'winsP50' => $pick(0.50),
        'winsP90' => $pick(0.90),
        'avgEnemyLevel' => round($enemySum / max(1, array_sum($wins)), 2),
        'hours45' => round($mean * 45 / 3600, 2),
        'hours60' => round($mean * 60 / 3600, 2),
        'hours45P90' => round($pick(0.90) * 45 / 3600, 2),
        'hours60P90' => round($pick(0.90) * 60 / 3600, 2),
        'team6Hours45' => round($mean * 6 * 45 / 3600, 1),
        'team6Hours60' => round($mean * 6 * 60 / 3600, 1),
        'totalXpFrom5' => $totalXp,
        'winsAtLevel50' => (int) ceil($totalXp / (12 * 50)),
        'targetTotal' => Curve::cumulative(100, $group),
    ];
};

$results = [];
foreach ($policies as $policy => $offset) {
    foreach ($groupOrder as $group) {
        $results[$policy][$group] = $simulate($group, $offset, $runs, $buildingMeta, 5)
            + ['group' => $group, 'label' => Group::label($group), 'policy' => $policy];
    }
}

$payload = [
    'generatedAt' => gmdate('c'),
    'model' => [
        'rewardPerWin' => '12 * enemyLevel',
        'cycleSeconds' => [45, 60],
        'startLevel' => 5,
        'targetLevel' => 100,
        'runs' => $runs,
        'seed' => 1234567,
        'buildings' => count($buildingMeta),
        'maxSpawnLevel' => $maxSpawn,
        'selection' => 'ORDER BY (RAND() * GREATEST(chance, 1)) DESC; enemy = spawn level + random_int(0, 4)',
        'notes' => 'No candies, boosts, EXP share, losses, healing or travel. Only the finishing Pokemon gains XP. Game room 20152 excluded.',
    ],
    'curves' => [
        'version' => Curve::VERSION,
        'anchorTotal' => Curve::ANCHOR_TOTAL,
        'targets' => Curve::targetTotals(),
        'source' => \Pokemon8\Game\PokemonGrowthData::SOURCE,
    ],
    'results' => $results,
];

if ($jsonOut) {
    echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
    exit(0);
}

echo "XP growth model (" . $runs . " runs, seed 1234567)\n";
echo "Reward: 12 * enemy level; cycle 45..60 s; start level 5; finishing Pokemon only.\n";
echo "Spawn table: " . count($buildingMeta) . " ordinary buildings, max base level " . $maxSpawn . "; game room 20152 excluded.\n\n";
foreach ($results as $policy => $rows) {
    echo "=== " . $policy . " ===\n";
    printf("%-14s %8s %8s %8s %8s %7s %11s %11s\n", 'group', 'wins', 'p10', 'p50', 'p90', 'enemy', 'hours45-60', 'team6 45-60');
    foreach ($rows as $row) {
        printf(
            "%-14s %8.1f %8d %8d %8d %7.2f %5.2f-%-5.2f %5.1f-%-5.1f\n",
            $row['label'],
            $row['winsMean'],
            $row['winsP10'],
            $row['winsP50'],
            $row['winsP90'],
            $row['avgEnemyLevel'],
            $row['hours45'],
            $row['hours60'],
            $row['team6Hours45'],
            $row['team6Hours60']
        );
    }
    echo "\n";
}
echo "Target totals: " . json_encode(Curve::targetTotals(), JSON_UNESCAPED_UNICODE) . "\n";
echo "Curve source: " . \Pokemon8\Game\PokemonGrowthData::SOURCE['cross_check'] . "\n";
