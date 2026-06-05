<?php
declare(strict_types=1);

use Pokemon8\Database\Connection;
use Pokemon8\Repository\BattleRepository;
use Pokemon8\Support\Env;

require dirname(__DIR__) . '/src/Support/Autoload.php';

Env::load(dirname(__DIR__) . '/.env');

$db = Connection::make(require dirname(__DIR__) . '/config/database.php');
$repo = new BattleRepository($db);
$results = [];

$db->beginTransaction();
try {
    $pokemonId = (int) ($db->query('SELECT COALESCE(MAX(id), 0) + 1 FROM pok_user')->fetchColumn() ?: 1);
    $db->prepare(
        'INSERT INTO pok_user
            (id, users, basenum, names, active, evcount, lvl, sex, har, hp_my, hp_max, exp, exp_b,
             atk, def, satk, sdef, speed, hp_ev, atk_ev, def_ev, satk_ev, sdef_ev, speed_ev,
             hp_iv, atk_iv, def_iv, satk_iv, sdef_iv, speed_iv, tips, startone, startepoke,
             reproduction, happy, datemay, usersone, sprz, item)
         VALUES
            (:id, 1, 382, "Smoke Impossible Kyogre", 1, 0, 10000, 1, 1, 50000, 50000, 0, 0,
             50000, 50000, 50000, 50000, 50000, 9999, 9999, 9999, 9999, 9999, 9999,
             9999, 9999, 9999, 9999, 9999, 9999, "normal", 0, 0,
             0, 0, NOW(), 1, 0, 0)'
    )->execute(['id' => $pokemonId]);

    $pokemon = $repo->findPokemon('pvp_' . $pokemonId);
    assertSmoke($results, $pokemon !== null, 'temporary pokemon can be loaded');
    assertSmoke($results, (int) ($pokemon['lvl'] ?? 0) === 100, 'level is clamped to 100');
    assertSmoke($results, (int) ($pokemon['hp_my'] ?? 0) <= (int) ($pokemon['hp_max'] ?? 0), 'current HP does not exceed max HP');
    assertSmoke($results, (int) ($pokemon['hp_max'] ?? 0) < 1000, 'max HP is recalculated below impossible ceiling');
    foreach (['atk', 'def', 'satk', 'sdef', 'speed'] as $field) {
        assertSmoke($results, (int) ($pokemon[$field] ?? 0) < 1000, $field . ' is recalculated below impossible ceiling');
    }
} finally {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
}

foreach ($results as $row) {
    echo sprintf("[%s] %s\n", $row['ok'] ? 'OK ' : 'FAIL', $row['name']);
}

exit(count(array_filter($results, static fn (array $row): bool => !$row['ok'])) > 0 ? 1 : 0);

/** @param list<array{name:string,ok:bool}> $results */
function assertSmoke(array &$results, bool $ok, string $name): void
{
    $results[] = ['name' => $name, 'ok' => $ok];
}
