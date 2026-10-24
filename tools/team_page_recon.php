<?php
declare(strict_types=1);

/** Сколько покемонов реально у игрока и как страница команды разбивает их по секциям. */
require __DIR__ . '/../src/Support/Autoload.php';

Pokemon8\Support\Env::load(dirname(__DIR__) . '/.env');
$db = Pokemon8\Database\Connection::make(require dirname(__DIR__) . '/config/database.php');

$stmt = $db->prepare('SELECT COUNT(*) FROM pok_user WHERE users = :user');
$stmt->execute(['user' => 1]);
echo 'покемонов у игрока 1 (Tacos): ' . (int) $stmt->fetchColumn() . PHP_EOL;

$stmt = $db->prepare('SELECT active, COUNT(*) c FROM pok_user WHERE users = :user GROUP BY active');
$stmt->execute(['user' => 1]);
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    echo "  active={$row['active']}: {$row['c']}\n";
}

$stmt = $db->prepare('SELECT COUNT(*) FROM pok_user WHERE users = :user AND active = 1');
$stmt->execute(['user' => 1]);
echo 'в активной команде: ' . (int) $stmt->fetchColumn() . PHP_EOL;

$view = dirname(__DIR__) . '/views/game-pokemon.php';
$raw = (string) file_get_contents($view);
foreach (['team-section-active', 'team-section-nursery', 'team-list', 'is-overview'] as $needle) {
    echo str_pad($needle, 24) . substr_count($raw, $needle) . " вхождений в разметке\n";
}

echo PHP_EOL . "-- как страница получает список покемонов --\n";
foreach (file($view) ?: [] as $number => $line) {
    if (preg_match('/pokemon[s]?\s*=|->pokemons|team|nursery/i', $line) && preg_match('/fetch|\$pokemon|foreach|json/i', $line)) {
        echo ($number + 1) . ': ' . trim(mb_substr($line, 0, 130)) . PHP_EOL;
    }
}
