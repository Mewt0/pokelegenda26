<?php
declare(strict_types=1);

use Pokemon8\Database\Connection;
use Pokemon8\Repository\BattleRepository;
use Pokemon8\Support\Env;

require dirname(__DIR__) . '/src/Support/Autoload.php';

Env::load(dirname(__DIR__) . '/.env');

$db = Connection::make(require dirname(__DIR__) . '/config/database.php');
$repo = new BattleRepository($db);

$findBase = new ReflectionMethod($repo, 'findBasePokemonRow');
$findBase->setAccessible(true);
$scaleStats = new ReflectionMethod($repo, 'scaleTransformedStats');
$scaleStats->setAccessible(true);

$results = [];

assertTransformStats(
    $results,
    $repo,
    $findBase,
    $scaleStats,
    'primal_kyogre',
    382,
    5017,
    ['hp_max' => 342, 'hp_my' => 342, 'atk' => 236, 'def' => 216, 'satk' => 359, 'sdef' => 316, 'speed' => 307],
    ['hp_max' => 342, 'hp_my' => 342, 'atk' => 354, 'def' => 216, 'satk' => 431, 'sdef' => 361, 'speed' => 307]
);

assertTransformStats(
    $results,
    $repo,
    $findBase,
    $scaleStats,
    'primal_groudon',
    383,
    5018,
    ['hp_max' => 342, 'hp_my' => 342, 'atk' => 399, 'def' => 379, 'satk' => 236, 'sdef' => 216, 'speed' => 216],
    ['hp_max' => 342, 'hp_my' => 342, 'atk' => 479, 'def' => 433, 'satk' => 354, 'sdef' => 216, 'speed' => 216]
);

assertTransformStats(
    $results,
    $repo,
    $findBase,
    $scaleStats,
    'mega_rayquaza',
    384,
    5019,
    ['hp_max' => 352, 'hp_my' => 352, 'atk' => 399, 'def' => 216, 'satk' => 336, 'sdef' => 216, 'speed' => 289],
    ['hp_max' => 352, 'hp_my' => 352, 'atk' => 479, 'def' => 240, 'satk' => 403, 'sdef' => 240, 'speed' => 350]
);

foreach ($results as $row) {
    echo sprintf("[%s] %s %s\n", $row['ok'] ? 'OK ' : 'FAIL', $row['name'], $row['detail']);
}

exit(count(array_filter($results, static fn (array $row): bool => !$row['ok'])) > 0 ? 1 : 0);

/**
 * @param array<int,array{name:string,ok:bool,detail:string}> $results
 * @param array<string,int> $input
 * @param array<string,int> $expected
 */
function assertTransformStats(
    array &$results,
    BattleRepository $repo,
    ReflectionMethod $findBase,
    ReflectionMethod $scaleStats,
    string $name,
    int $originalId,
    int $formId,
    array $input,
    array $expected
): void {
    $original = $findBase->invoke($repo, $originalId);
    $form = $findBase->invoke($repo, $formId);
    $actual = $input;
    $scaleStats->invokeArgs($repo, [&$actual, $original, $form]);

    $ok = true;
    foreach ($expected as $field => $value) {
        if ((int) ($actual[$field] ?? 0) !== $value) {
            $ok = false;
            break;
        }
    }

    $results[] = [
        'name' => $name,
        'ok' => $ok,
        'detail' => $ok
            ? json_encode($actual, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            : 'expected=' . json_encode($expected, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . ' actual=' . json_encode($actual, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    ];
}
