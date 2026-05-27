<?php
declare(strict_types=1);

use Pokemon8\Database\Connection;
use Pokemon8\Repository\SafeStorageRepository;
use Pokemon8\Support\Env;

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Run from CLI.\n");
    exit(2);
}

define('APP_ROOT', dirname(__DIR__));
require APP_ROOT . '/src/Support/Autoload.php';

Env::load(APP_ROOT . '/.env');
$db = Connection::make(require APP_ROOT . '/config/database.php');
$safe = new SafeStorageRepository($db);
$results = [];

$db->beginTransaction();
try {
    $tacos = userId($db, 'Tacos');
    assertTrue($results, 'user.tacos', $tacos > 0, 'id=' . $tacos);

    $itemEntry = $safe->storeItem($tacos, 1, 7, 'smoke', 'item', ['note' => 'safe item smoke'], 'smoke_item');
    assertTrue($results, 'store.item', $itemEntry > 0, 'entry=' . $itemEntry);

    $pokemonEntry = $safe->storePokemon($tacos, 123456, 'smoke', 'pokemon', ['note' => 'safe pokemon smoke'], 'smoke_pokemon');
    assertTrue($results, 'store.pokemon', $pokemonEntry > 0, 'entry=' . $pokemonEntry);

    $eggEntry = $safe->storeEgg($tacos, 654321, 'smoke', 'egg', ['note' => 'safe egg smoke'], 'smoke_egg');
    assertTrue($results, 'store.egg', $eggEntry > 0, 'entry=' . $eggEntry);

    $rollbackId = $safe->recordRollback(
        'smoke:' . time(),
        'smoke_operation',
        $tacos,
        'smoke',
        'rollback',
        ['before' => true],
        ['after' => true],
        ['undo' => true],
        'open'
    );
    assertTrue($results, 'rollback.record', $rollbackId > 0, 'rollback=' . $rollbackId);

    $safe->markStorageResolved($itemEntry, $tacos, 'resolved', ['smoke' => true]);
    assertTrue($results, 'store.resolve', storageStatus($db, $itemEntry) === 'resolved', 'status=' . storageStatus($db, $itemEntry));

    $counts = $safe->pendingCounts();
    assertTrue($results, 'pending.counts', ($counts['storage'] ?? -1) >= 2 && ($counts['rollbacks'] ?? -1) >= 1, json_encode($counts));

    $db->rollBack();
} catch (Throwable $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    $results[] = ['name' => 'exception', 'ok' => false, 'details' => $e->getMessage()];
}

printResults($results);
exit(count(array_filter($results, static fn (array $row): bool => !$row['ok'])) === 0 ? 0 : 1);

function userId(PDO $db, string $login): int
{
    $stmt = $db->prepare('SELECT id FROM users WHERE login = :login LIMIT 1');
    $stmt->execute(['login' => $login]);
    return (int) ($stmt->fetchColumn() ?: 0);
}

function storageStatus(PDO $db, int $entryId): string
{
    $stmt = $db->prepare('SELECT status FROM safe_storage_entries WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $entryId]);
    return (string) ($stmt->fetchColumn() ?: '');
}

function assertTrue(array &$results, string $name, bool $ok, string $details = ''): void
{
    $results[] = ['name' => $name, 'ok' => $ok, 'details' => $details];
}

function printResults(array $results): void
{
    $passed = 0;
    foreach ($results as $row) {
        if ($row['ok']) {
            $passed++;
        }
        echo sprintf("[%s] %s%s\n", $row['ok'] ? 'OK ' : 'FAIL', $row['name'], $row['details'] !== '' ? ' - ' . $row['details'] : '');
    }
    echo sprintf("Safe storage smoke: %d/%d passed.\n", $passed, count($results));
}
