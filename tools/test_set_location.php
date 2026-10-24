<?php
declare(strict_types=1);

/**
 * Move the test character to a location, remembering the previous value so it can
 * be restored. Used only to make a dialogue reachable for a live check.
 *
 * Usage: php tools/test_set_location.php Tacos 7        (move and remember)
 *        php tools/test_set_location.php --restore      (put it back)
 */
require __DIR__ . '/../src/Support/Autoload.php';

Pokemon8\Support\Env::load(dirname(__DIR__) . '/.env');
$db = Pokemon8\Database\Connection::make(require dirname(__DIR__) . '/config/database.php');

$memoryFile = dirname(__DIR__) . '/storage/backups/test_location_memory.json';

$columns = array_map(
    static fn (array $row): string => (string) $row['Field'],
    $db->query('SHOW COLUMNS FROM users')->fetchAll(PDO::FETCH_ASSOC)
);
$locationColumn = null;
foreach (['buildmy', 'location', 'location_id', 'build', 'build_id', 'id_build', 'loc'] as $candidate) {
    if (in_array($candidate, $columns, true)) {
        $locationColumn = $candidate;
        break;
    }
}
if ($locationColumn === null) {
    echo "не нашёл колонку локации; колонки: " . implode(', ', array_slice($columns, 0, 30)) . PHP_EOL;
    exit(2);
}
echo "колонка локации: $locationColumn" . PHP_EOL;

if (($argv[1] ?? '') === '--restore') {
    if (!is_file($memoryFile)) {
        echo "нечего восстанавливать" . PHP_EOL;
        exit(1);
    }
    $memory = json_decode((string) file_get_contents($memoryFile), true);
    $stmt = $db->prepare("UPDATE users SET `$locationColumn` = :loc WHERE id = :id");
    $stmt->execute(['loc' => (int) $memory['before'], 'id' => (int) $memory['user_id']]);
    echo "восстановлено: user {$memory['user_id']} -> локация {$memory['before']}" . PHP_EOL;
    unlink($memoryFile);
    exit(0);
}

$login = (string) ($argv[1] ?? 'Tacos');
$target = (int) ($argv[2] ?? 7);

$stmt = $db->prepare("SELECT id, `$locationColumn` AS loc FROM users WHERE login = :login LIMIT 1");
$stmt->execute(['login' => $login]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if ($user === false) {
    echo "игрок $login не найден" . PHP_EOL;
    exit(2);
}

file_put_contents($memoryFile, json_encode(['user_id' => (int) $user['id'], 'before' => (int) $user['loc']]));
$db->prepare("UPDATE users SET `$locationColumn` = :loc WHERE id = :id")
   ->execute(['loc' => $target, 'id' => (int) $user['id']]);

echo "игрок {$user['id']} ($login): локация {$user['loc']} -> $target" . PHP_EOL;
