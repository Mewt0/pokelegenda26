<?php
declare(strict_types=1);

/** Откуда берутся картинки локаций: таблица, конфиг или легаси-комнаты. */
require __DIR__ . '/../src/Support/Autoload.php';

Pokemon8\Support\Env::load(dirname(__DIR__) . '/.env');
$db = Pokemon8\Database\Connection::make(require dirname(__DIR__) . '/config/database.php');

echo "-- таблицы/колонки, где может быть картинка локации --\n";
$tables = array_map('strval', $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN));
foreach ($tables as $table) {
    if (!preg_match('/build|location|room|town|world|img|image|photo|pic/i', $table)) {
        continue;
    }
    $columns = array_map(
        static fn (array $r): string => (string) $r['Field'],
        $db->query("SHOW COLUMNS FROM `$table`")->fetchAll(PDO::FETCH_ASSOC)
    );
    $imageCols = array_values(array_filter($columns, static fn (string $c): bool => (bool) preg_match('/img|image|photo|pic|sprz|sprite|foto|screen/i', $c)));
    echo str_pad($table, 28) . 'всего колонок ' . count($columns)
        . '; похожие на картинку: ' . ($imageCols === [] ? '(нет)' : implode(', ', $imageCols)) . "\n";
}

echo "\n-- конфиг контента локаций --\n";
$configPath = dirname(__DIR__) . '/config/location_content.php';
if (is_file($configPath)) {
    $raw = (string) file_get_contents($configPath);
    echo 'размер: ' . strlen($raw) . " байт\n";
    preg_match_all('/[\'"]([^\'"]*\.(?:jpg|jpeg|png|gif|webp))[\'"]/i', $raw, $matches);
    $images = array_values(array_unique($matches[1]));
    echo 'картинок в конфиге: ' . count($images) . "\n";
    foreach (array_slice($images, 0, 12) as $image) {
        echo "  $image\n";
    }
    foreach (['img', 'image', 'photo', 'picture', 'bg'] as $key) {
        $count = preg_match_all('/[\'"]' . $key . '[\'"]\s*=>/i', $raw);
        if ($count > 0) {
            echo "ключ '$key' встречается: $count\n";
        }
    }
} else {
    echo "нет файла $configPath\n";
}

echo "\n-- легаси-комнаты: картинки в include/rooms/*.php --\n";
$rooms = glob(dirname(__DIR__) . '/include/rooms/*.php') ?: [];
echo 'файлов комнат: ' . count($rooms) . "\n";
$withImage = 0;
$examples = [];
foreach ($rooms as $room) {
    $raw = (string) file_get_contents($room);
    if (preg_match_all('/[\'"]([^\'"]*\.(?:jpg|jpeg|png|gif|webp))[\'"]/i', $raw, $m)) {
        $withImage++;
        if (count($examples) < 8) {
            $examples[] = basename($room) . ' -> ' . implode(', ', array_slice(array_unique($m[1]), 0, 3));
        }
    }
}
echo "комнат с картинками: $withImage\n";
foreach ($examples as $example) {
    echo "  $example\n";
}
