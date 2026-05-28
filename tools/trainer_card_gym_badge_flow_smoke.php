<?php
declare(strict_types=1);

use Pokemon8\Database\Connection;
use Pokemon8\Game\BattleEngineService;
use Pokemon8\Repository\BattleRepository;
use Pokemon8\Repository\ProfileRepository;
use Pokemon8\Repository\RewardRepository;
use Pokemon8\Support\Env;

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Run from CLI.\n");
    exit(2);
}

define('APP_ROOT', dirname(__DIR__));
require APP_ROOT . '/src/Support/Autoload.php';

Env::load(APP_ROOT . '/.env');
$db = Connection::make(require APP_ROOT . '/config/database.php');
$results = [];

$db->beginTransaction();
try {
    assertTrue($results, 'table.rules', tableExists($db, 'gym_badge_battle_rules'));
    assertTrue($results, 'table.badges', tableExists($db, 'gym_badges') && tableExists($db, 'user_gym_badges'));

    $userId = userId($db, 'Tacos');
    assertTrue($results, 'user.tacos', $userId > 0, 'id=' . $userId);

    $badgeKey = 'codex_smoke_' . time();
    $now = time();
    $db->prepare(
        'INSERT INTO gym_badges (badge_key, title, leader_name, location_id, icon_item_id, created_at, updated_at)
         VALUES (:key, "Smoke Gym Badge", "Smoke Leader", 0, 89, :created, :updated)'
    )->execute(['key' => $badgeKey, 'created' => $now, 'updated' => $now]);

    $locationId = currentLocation($db, $userId);
    $db->prepare(
        'INSERT INTO gym_badge_battle_rules
            (badge_key, location_id, enemy_base_id, enemy_pokemon_id, enemy_name_like, min_level, source_note, enabled, created_at, updated_at)
         VALUES
            (:badge, :location, 74, 0, "smoke leader", 5, "trainer-card smoke", 1, :created, :updated)'
    )->execute([
        'badge' => $badgeKey,
        'location' => $locationId,
        'created' => $now,
        'updated' => $now,
    ]);

    $battleRepo = new BattleRepository($db);
    $rewardRepo = new RewardRepository($db);
    $engine = new BattleEngineService($battleRepo, $rewardRepo);
    $grant = new ReflectionMethod($engine, 'grantGymBadgeForPveVictory');
    $grant->setAccessible(true);

    $battle = ['id' => 900000123];
    $enemy = ['id' => 777001, 'basenum' => 74, 'names' => 'Smoke Leader Geodude', 'lvl' => 12];
    $first = $grant->invoke($engine, $userId, $battle, $enemy, 1);
    assertTrue($results, 'badge.granted', is_array($first) && ($first['key'] ?? '') === $badgeKey, json_encode($first, JSON_UNESCAPED_UNICODE));

    $count = badgeCount($db, $userId, $badgeKey);
    assertTrue($results, 'badge.persisted.once', $count === 1, 'count=' . $count);

    $second = $grant->invoke($engine, $userId, $battle, $enemy, 1);
    assertTrue($results, 'badge.no.duplicate', $second === null && badgeCount($db, $userId, $badgeKey) === 1);

    $profile = (new ProfileRepository($db))->profile($userId, $userId);
    $keys = array_map(static fn (array $badge): string => (string) ($badge['key'] ?? ''), $profile['gymBadges'] ?? []);
    assertTrue($results, 'profile.card.includes.badge', in_array($badgeKey, $keys, true), implode(',', $keys));

    $source = badgeSource($db, $userId, $badgeKey);
    assertTrue(
        $results,
        'badge.source.gym_battle',
        ($source['source_type'] ?? '') === 'gym_battle' && (int) ($source['source_battle_id'] ?? 0) === 900000123,
        json_encode($source, JSON_UNESCAPED_UNICODE)
    );

    $db->rollBack();
} catch (Throwable $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    $results[] = ['name' => 'exception', 'ok' => false, 'details' => $e->getMessage()];
}

printResults($results);
exit(count(array_filter($results, static fn (array $row): bool => !$row['ok'])) === 0 ? 0 : 1);

function tableExists(PDO $db, string $table): bool
{
    $stmt = $db->prepare(
        'SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table LIMIT 1'
    );
    $stmt->execute(['table' => $table]);
    return (bool) $stmt->fetchColumn();
}

function userId(PDO $db, string $login): int
{
    $stmt = $db->prepare('SELECT id FROM users WHERE login = :login LIMIT 1');
    $stmt->execute(['login' => $login]);
    return (int) ($stmt->fetchColumn() ?: 0);
}

function currentLocation(PDO $db, int $userId): int
{
    $stmt = $db->prepare('SELECT buildmy FROM users WHERE id = :user LIMIT 1');
    $stmt->execute(['user' => $userId]);
    return (int) ($stmt->fetchColumn() ?: 0);
}

function badgeCount(PDO $db, int $userId, string $badgeKey): int
{
    $stmt = $db->prepare(
        'SELECT COUNT(*)
           FROM user_gym_badges ugb
     INNER JOIN gym_badges gb ON gb.id = ugb.badge_id
          WHERE ugb.user_id = :user AND gb.badge_key = :badge'
    );
    $stmt->execute(['user' => $userId, 'badge' => $badgeKey]);
    return (int) ($stmt->fetchColumn() ?: 0);
}

function badgeSource(PDO $db, int $userId, string $badgeKey): array
{
    $stmt = $db->prepare(
        'SELECT ugb.source_type, ugb.source_id, ugb.source_battle_id
           FROM user_gym_badges ugb
     INNER JOIN gym_badges gb ON gb.id = ugb.badge_id
          WHERE ugb.user_id = :user AND gb.badge_key = :badge
          LIMIT 1'
    );
    $stmt->execute(['user' => $userId, 'badge' => $badgeKey]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
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
    echo sprintf("Trainer Card gym badge flow smoke: %d/%d passed.\n", $passed, count($results));
}
